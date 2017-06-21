USE `Ratemanagement3`;


-- Sage pay Integration - Not Tested yet
-- INSERT INTO `tblIntegration` (`CompanyId`, `Title`, `Slug`, `ParentID`) VALUES ('1', 'SagePay', 'sagepay', '4');

Delimiter ;;
DROP PROCEDURE IF EXISTS `prc_ManualImportAccount`;
CREATE PROCEDURE `prc_ManualImportAccount`(
	IN `p_ProcessID` VARCHAR(50)
)
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN

	Declare v_IsVendor  int ;

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_Accounts;
	CREATE TEMPORARY TABLE tmp_Accounts (
		AccountID int,
		AccountName varchar(100),
		IP varchar(250)
	);



	SET v_IsVendor = 1;

	-- select accounts from tblTempAccount which Account Name match with tblAccount
	insert into tmp_Accounts (AccountID,AccountName,IP)
	select a.AccountID, a.AccountName, tmp.IP
	from tblTempAccount tmp
	inner join tblAccount a on tmp.AccountName like concat (a.AccountName ,'%')
	where  tmp.ProcessID = p_ProcessID
			and a.CompanyID=1
			and tmp.IP is not null
			and a.AccountID is not null
	group by a.AccountID,tmp.IP;


	if (v_IsVendor = 1) THEN

			-- account match with comma seperated IPs , ip not exists
			/*select tmpa.AccountID, tmpa.AccountName, GROUP_CONCAT(tmpa.IP) as IPs
			from tmp_Accounts tmpa
			left join tblAccountAuthenticate auth on auth.CustomerAuthRule='IP' and FIND_IN_SET(tmpa.IP,auth.CustomerAuthValue)>0
			where auth.AccountID is null
			group by tmpa.AccountID,tmpa.AccountName;
			*/

			Update tblAccountAuthenticate autha
			inner join
			(
				select tmpa.AccountID, GROUP_CONCAT(tmpa.IP) as IPs
				from tmp_Accounts tmpa
				left join tblAccountAuthenticate auth on auth.CustomerAuthRule='IP' and FIND_IN_SET(tmpa.IP,auth.CustomerAuthValue)>0
				where auth.AccountID is null
				group by tmpa.AccountID
			) tmp on autha.AccountID = tmp.AccountID
			SET
			VendorAuthRule = 'IP',
			VendorAuthValue = tmp.IPs
			;

			-- Updat Account Vendor = 1
			update tblAccount a
			inner join
			(
				select tmpa.AccountID, GROUP_CONCAT(tmpa.IP) as IPs
				from tmp_Accounts tmpa
				left join tblAccountAuthenticate auth on auth.CustomerAuthRule='IP' and FIND_IN_SET(tmpa.IP,auth.CustomerAuthValue)>0
				where auth.AccountID is null
				group by tmpa.AccountID
			) tmp on a.AccountID = tmp.AccountID
			SET
			IsVendor = 1;



	END IF;


		-- select accounts which IPs already exists
			select tmpa.AccountID, tmpa.AccountName, GROUP_CONCAT(tmpa.IP) as IPs, 'IP Already Exists' as Skipped
			from tmp_Accounts tmpa
			left join tblAccountAuthenticate auth on auth.CustomerAuthRule='IP' and FIND_IN_SET(tmpa.IP,auth.CustomerAuthValue)>0
			where auth.AccountID is not null
			group by tmpa.AccountID,tmpa.AccountName;


END;;
Delimiter ;


Delimiter ;;
DROP PROCEDURE IF EXISTS  `prc_AssignSlaToTicket`;
CREATE PROCEDURE `prc_AssignSlaToTicket`(
	IN `p_CompanyID` INT,
	IN `p_TicketID` INT,
	IN `p_CompanyID` INT,
	IN `p_TicketID` INT)
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN


DECLARE v_HasCompanyFilter int;

DECLARE v_HasGroupFilter int;

DECLARE v_HasTypeFilter int;


DECLARE v_Group int;

DECLARE v_Type int;

DECLARE v_AccountID int;

DECLARE v_SlaPolicyID int;

DECLARE v_TicketSlaID int;

DECLARE v_DueDate datetime;

DECLARE v_resolve_in_min int;

DECLARE v_created_at datetime;

DECLARE v_OperationalHrs tinyint;

DECLARE v_GroupBusinessHours int;

DECLARE v_HoursType tinyint;

DECLARE v_PriorityID int;


SET SESSION TRANSACTION ISOLATION LEVEL READ  COMMITTED;

SELECT
`Priority` ,
`created_at` ,
`Group` ,
`Type` ,
`AccountID` into

 v_PriorityID ,
 v_created_at ,
 v_Group ,
 v_Type ,
 v_AccountID
from tblTickets where TicketID = p_TicketID;


-- if there is only one sla
IF ((select count(*) from tblTicketSla where CompanyID=p_CompanyID) = 1 ) THEN


	-- check for any match
	select sla.TicketSlaID  into v_TicketSlaID
	from tblTicketSla sla
	inner join tblTicketSlaPolicyApplyTo pol on pol.TicketSlaID = sla.TicketSlaID
		where sla.CompanyID=p_CompanyID
			/*and
			(
				(pol.CompanyFilter = ''  OR (pol.CompanyFilter is not null and FIND_IN_SET(v_AccountID,pol.CompanyFilter) > 0 ))
				OR
				(pol.GroupFilter = ''  OR (pol.GroupFilter is not null and FIND_IN_SET(v_Group,pol.GroupFilter) > 0) )
				OR
				(pol.TypeFilter = '' OR (pol.TypeFilter is not null and FIND_IN_SET(v_Type,pol.TypeFilter) > 0) )
			)*/
		limit 1	;



ELSE -- if there is many slas


		DROP TEMPORARY TABLE IF EXISTS `tmp_tblTicketSla`;
		CREATE TEMPORARY TABLE `tmp_tblTicketSla` (
			`TicketSlaID` INT NOT NULL,
			numMatches  INT NOT NULL
		);

		-- check max exact matches

		INSERT INTO tmp_tblTicketSla
		SELECT TicketSlaID , numMatches
	   FROM (SELECT (CASE WHEN (pol.CompanyFilter is not null and FIND_IN_SET(v_AccountID,pol.CompanyFilter) > 0 OR pol.CompanyFilter = '' ) THEN 1 ELSE 0 END +
		                CASE WHEN (pol.GroupFilter is not null and FIND_IN_SET(v_Group,pol.GroupFilter) > 0 OR pol.GroupFilter = '' ) THEN 1 ELSE 0 END +
							CASE WHEN (pol.TypeFilter is not null and FIND_IN_SET(v_Type,pol.TypeFilter) > 0 OR pol.TypeFilter = '' ) THEN 1 ELSE 0 END
		               ) AS numMatches,
		               sla.TicketSlaID
			          from tblTicketSla sla
						inner join tblTicketSlaPolicyApplyTo pol on pol.TicketSlaID = sla.TicketSlaID
						where sla.CompanyID=p_CompanyID
		       ) tmptable
		 WHERE numMatches > 0
		 ORDER BY numMatches DESC ;


		IF ( ( SELECT count(*) FROM tmp_tblTicketSla where  numMatches = 3 limit 1 ) > 0 ) THEN

			select TicketSlaID into v_TicketSlaID from tmp_tblTicketSla where  numMatches = 3 limit 1;

		ELSEIF ( ( SELECT count(*) FROM tmp_tblTicketSla where  numMatches = 2 limit 1 ) > 0 ) THEN

			select TicketSlaID into v_TicketSlaID from tmp_tblTicketSla where  numMatches = 2 limit 1;

		ELSEIF ( ( SELECT count(*) FROM tmp_tblTicketSla where  numMatches = 1 limit 1 ) > 0 ) THEN

			select TicketSlaID into v_TicketSlaID from tmp_tblTicketSla where  numMatches = 1 limit 1;

		END IF;


			-- if  no exact match found
		IF ( v_TicketSlaID is null ) THEN

				-- check for any match

					select sla.TicketSlaID  into v_TicketSlaID
					from tblTicketSla sla
					inner join tblTicketSlaPolicyApplyTo pol on pol.TicketSlaID = sla.TicketSlaID
					where sla.CompanyID=p_CompanyID
					and
					(
						(pol.CompanyFilter = ''  OR (pol.CompanyFilter != '' and FIND_IN_SET(v_AccountID,pol.CompanyFilter) > 0 ))
						OR
						(pol.GroupFilter = ''  OR (pol.GroupFilter != '' and FIND_IN_SET(v_Group,pol.GroupFilter) > 0) )
						OR
						(pol.TypeFilter = '' OR (pol.TypeFilter != '' and FIND_IN_SET(v_Type,pol.TypeFilter) > 0) )
					) limit 1;

	  END IF;



END IF;

	--  select v_TicketSlaID;


	-- update v_TicketSlaID;

	/*UPDATE tblTickets
	SET  TicketSlaID = v_TicketSlaID
	WHERE TicketID = p_TicketID;
	*/

	IF ( v_TicketSlaID > 0 ) THEN


			-- ###########################################################

			SELECT
				(	CASE WHEN (tat.ResolveType = 'Minute') THEN
						DATE_ADD(t.created_at, INTERVAL tat.ResolveValue Minute)
					WHEN (ResolveType = 'Hour') THEN
						DATE_ADD(t.created_at, INTERVAL tat.ResolveValue Hour)
					WHEN (tat.ResolveType = 'Day') THEN
						DATE_ADD(t.created_at, INTERVAL tat.ResolveValue Day)
					WHEN (tat.ResolveType = 'Month') THEN
						DATE_ADD(t.created_at, INTERVAL tat.ResolveValue Month)
					END
			  ) as DueDate   into v_DueDate
			FROM
				tblTickets t
			INNER join tblTicketSlaTarget tat on tat.TicketSlaID  = v_TicketSlaID AND tat.PriorityID = t.Priority
			WHERE
			t.TicketID = p_TicketID ;

			-- select v_DueDate;

			--	Resolve_in_min
			SELECT
			   	CASE WHEN (tat.ResolveType = 'Minute') THEN
						(tat.ResolveValue)
					WHEN (ResolveType = 'Hour') THEN
						 (tat.ResolveValue * 60)
					WHEN (tat.ResolveType = 'Day') THEN
						tat.ResolveValue * 60 * 24
					WHEN (tat.ResolveType = 'Month') THEN
						 (tat.ResolveValue*30*24*60)
					END
			  as Resolve_in_min into v_Resolve_in_min
			FROM
				tblTickets t
			INNER join tblTicketSlaTarget tat on tat.TicketSlaID  = v_TicketSlaID AND tat.PriorityID = t.Priority
			WHERE
			t.TicketID = p_TicketID ;

			-- select v_Resolve_in_min;

			select OperationalHrs into v_OperationalHrs from tblTicketSlaTarget where TicketSlaID = v_TicketSlaID and PriorityID = v_PriorityID limit 1;

				-- OperationalHrs
				-- BusinessHours		  =		1;
				-- CalendarHours		  =		0;


		      -- CalendarHours  -- do nothing
				-- 24x7

			-- select v_OperationalHrs;

			IF (v_OperationalHrs = 1) THEN 	-- BusinessHours

					SELECT  GroupBusinessHours into v_GroupBusinessHours from  tblTicketGroups	where GroupID=v_Group limit 1;

					-- select v_GroupBusinessHours;


					IF v_GroupBusinessHours is null THEN
							-- take default business hours
						SELECT  ID into v_GroupBusinessHours  from tblTicketBusinessHours where CompanyID = p_CompanyID and  IsDefault = 1 limit 1;

					END IF;




					SELECT  HoursType into v_HoursType from  tblTicketBusinessHours	where ID=v_GroupBusinessHours;

				-- select v_HoursType;

					-- HoursType
					-- HelpdeskHours247				=	1;
					-- HelpdeskHoursCustom				=	2;

					-- 24x7 do nothing

					-- not 24x7
					IF v_HoursType = 2 THEN

							-- if due day is off day or dueday timing is not betweek working days or (created or duedate is on holiday)
							IF (
									( SELECT count(*) FROM tblTicketsWorkingDays WHERE BusinessHoursID = v_GroupBusinessHours and `Status` = 1 and `Day` = DAYOFWEEK(v_DueDate) ) = 0
									OR
									(SELECT count(*) FROM tblTicketsWorkingDays WHERE BusinessHoursID = v_GroupBusinessHours and `Status` = 1 and `Day` = DAYOFWEEK(v_DueDate) and TIME(v_DueDate) BETWEEN StartTime and EndTime) = 0
  							  	   OR
									(select count(*) from tblTicketBusinessHolidays where BusinessHoursID = v_GroupBusinessHours and ( (`HolidayDay` = DAY(v_created_at) and  HolidayMonth = Month(v_created_at) ) OR ( `HolidayDay` = DAY(v_DueDate) and  HolidayMonth = Month(v_DueDate) )  )  limit 1 ) = 1

								) THEN

 								-- 	select "logic ";

								--  logic here only ....

								   SET @WorkingDays = (SELECT count(*) from tblTicketsWorkingDays WHERE BusinessHoursID = v_GroupBusinessHours and `Status` = 1 );

								   SET @v_date = DATE_FORMAT(v_created_at, '%Y-%m-%d %H:%i:00') ;

								   SET @v_end_date = DATE_FORMAT(DATE_ADD(v_DueDate, INTERVAL 8 Day) , '%Y-%m-%d %H:%i:00'); -- this can be changed if holidays are added ...



								-- insert date range with status of working hours.

								-- select   @v_date , @v_end_date;

								 	SET @v_min_counter = 0;

								 	-- SELECT v_GroupBusinessHours, @v_date , @v_end_date , v_DueDate, @v_min_counter , v_resolve_in_min ;

								   WHILE @v_date <= @v_end_date AND @v_min_counter <= v_resolve_in_min DO


									   IF (select count(*) from tblTicketBusinessHolidays where BusinessHoursID = v_GroupBusinessHours and `HolidayDay` = DAY(@v_date) and  HolidayMonth = Month(@v_date)  limit 1) > 0  THEN

										   SET @v_Status  = 0;

										   SET @v_date = DATE_ADD( date(@v_date), INTERVAL 1 Day );

										  --  SELECT @v_date  ,  @v_min_counter;


										ELSEIF (select count(*) from tblTicketsWorkingDays where BusinessHoursID = v_GroupBusinessHours and `Status` = 1 and `Day` = DAYOFWEEK(@v_date) and  Time(@v_date) between StartTime and EndTime limit 1) > 0 THEN

											SET @v_Status  = 1;

										ELSEIF v_resolve_in_min >= 1440  and ( select count(*) from tblTicketsWorkingDays where BusinessHoursID = v_GroupBusinessHours and `Status` = 1 and `Day` = DAYOFWEEK(@v_date) limit 1) > 0 THEN
											-- when >= 1 day due time then take off time to consider
											-- i.e. due in 2 day ,  creation date 2017-04-25 06:00:00  => due date =>2017-04-27 06:01:00
											SET @v_Status  = 1;

										ELSE

											SET @v_Status  = 0;


										   -- SELECT @v_date;

										END IF;


										IF ( @v_Status = 1) THEN

											-- only add time with enabled status


											SET @v_min_counter =  @v_min_counter + 1;

										END IF ;

										SET @v_date = DATE_ADD(@v_date, INTERVAL 1 Minute);

								 END WHILE;


								-- select * from tmp_tblDimDate_;

								-- select v_resolve_in_min;
								SET  v_DueDate = DATE_ADD(@v_date, INTERVAL -1 Minute); -- remove last min

 								-- select v_DueDate;

								-- ####################


						END IF;



					END IF;


			END IF;


			-- ###########################################################
			-- update v_TicketSlaID and v_DueDate;


			-- update due dates which are not customized yet
			UPDATE tblTickets
 			SET   TicketSlaID = v_TicketSlaID,
					 DueDate =  v_DueDate
			WHERE
			 TicketID = p_TicketID AND
			 CustomDueDate = 0;




	END IF;



SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END;;
Delimiter ;


-- Ticket full subject seach issue fixed
Delimiter ;;
DROP PROCEDURE IF EXISTS  `prc_GetSystemTicketCustomer`;
CREATE PROCEDURE `prc_GetSystemTicketCustomer`(
	IN `p_CompanyID` int,
	IN `p_Search` VARCHAR(100),
	IN `P_Status` VARCHAR(100),
	IN `P_Priority` VARCHAR(100),
	IN `P_Group` VARCHAR(100),
	IN `P_Agent` VARCHAR(100),
	IN `P_EmailAddresses` VARCHAR(200),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT

)
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN
	DECLARE v_OffSet_ int;
	DECLARE v_Round_ int;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	IF p_isExport = 0
	THEN
		SELECT
			T.TicketID,
			T.Subject,
			CASE WHEN (ISNULL(T.RequesterName) OR T.RequesterName='')  THEN T.Requester ElSE concat(T.RequesterName," (",T.Requester,")") END as Requester,
			T.Requester as RequesterEmail,
			TFV.FieldValueCustomer  as TicketStatus,
			TP.PriorityValue,
			concat(TU.FirstName,' ',TU.LastName) as Agent,
			T.created_at,
			(select tc.created_at from AccountEmailLog tc where tc.TicketID = T.TicketID  and tc.EmailCall =1 and tc.EmailParent>0 order by tc.AccountEmailLogID desc limit 1) as CustomerResponse,
		    (select tc.created_at from AccountEmailLog tc where tc.TicketID = T.TicketID  and tc.EmailCall =0  order by tc.AccountEmailLogID desc limit 1) as AgentResponse
		FROM
			tblTickets T
		LEFT JOIN tblTicketfieldsValues TFV
			ON TFV.ValuesID = T.Status
		LEFT JOIN tblTicketPriority TP
			ON TP.PriorityID = T.Priority
		LEFT JOIN tblUser TU
			ON TU.UserID = T.Agent
		LEFT JOIN tblTicketGroups TG
			ON TG.GroupID = T.`Group`

		WHERE
			T.CompanyID = p_CompanyID
			AND (p_Search = '' OR ( p_Search != '' AND (T.Subject like Concat('%',p_Search,'%') OR  T.Description like Concat('%',p_Search,'%') OR  T.Requester like Concat('%',p_Search,'%') OR  T.RequesterName like Concat('%',p_Search,'%') ) OR (T.TicketID in  ( select ael.TicketID from AccountEmailLog ael where (ael.Subject like Concat('%',p_Search,'%') OR  ael.Emailfrom like Concat('%',p_Search,'%') OR  ael.EmailfromName like Concat('%',p_Search,'%') OR  ael.Message like Concat('%',p_Search,'%') )   ) ) ) )
			AND (P_Status = '' OR find_in_set(T.`Status`,P_Status))
			AND (P_Priority = '' OR find_in_set(T.`Priority`,P_Priority))
			AND (P_Group = '' OR find_in_set(T.`Group`,P_Group))
			AND (P_Agent = '' OR find_in_set(T.`Agent`,P_Agent))
			AND (P_EmailAddresses = '' OR find_in_set(T.`Requester`,P_EmailAddresses))
			ORDER BY
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SubjectASC') THEN T.Subject
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SubjectDESC') THEN T.Subject
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusASC') THEN TicketStatus
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusDESC') THEN TicketStatus
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AgentASC') THEN TU.FirstName
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AgentDESC') THEN TU.FirstName
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN T.created_at
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN T.created_at
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN T.updated_at
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN T.updated_at
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RequesterASC') THEN T.Requester
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RequesterDESC') THEN T.Requester
			END DESC
			LIMIT
				p_RowspPage OFFSET v_OffSet_;

		SELECT
			COUNT(*) AS totalcount
				FROM
			tblTickets T
		LEFT JOIN tblTicketfieldsValues TFV
			ON TFV.ValuesID = T.Status
		LEFT JOIN tblTicketPriority TP
			ON TP.PriorityID = T.Priority
		LEFT JOIN tblUser TU
			ON TU.UserID = T.Agent
		LEFT JOIN tblTicketGroups TG
			ON TG.GroupID = T.`Group`
		WHERE
			T.CompanyID = p_CompanyID
			AND (p_Search = '' OR ( p_Search != '' AND (T.Subject like Concat('%',p_Search,'%') OR  T.Description like Concat('%',p_Search,'%') OR  T.Requester like Concat('%',p_Search,'%') OR  T.RequesterName like Concat('%',p_Search,'%') ) OR (T.TicketID in  ( select ael.TicketID from AccountEmailLog ael where (ael.Subject like Concat('%',p_Search,'%') OR  ael.Emailfrom like Concat('%',p_Search,'%') OR  ael.EmailfromName like Concat('%',p_Search,'%') OR  ael.Message like Concat('%',p_Search,'%') )   ) ) ) )
			AND (P_Status = '' OR find_in_set(T.`Status`,P_Status))
			AND (P_Priority = '' OR find_in_set(T.`Priority`,P_Priority))
			AND (P_Group = '' OR find_in_set(T.`Group`,P_Group))
			AND (P_Agent = '' OR find_in_set(T.`Agent`,P_Agent))
			AND (P_EmailAddresses = '' OR find_in_set(T.`Requester`,P_EmailAddresses));

		SELECT
				DISTINCT(TG.GroupID),
				TG.GroupName
		FROM
			tblTickets T
		LEFT JOIN tblTicketfieldsValues TFV
			ON TFV.ValuesID = T.Status
		LEFT JOIN tblTicketPriority TP
			ON TP.PriorityID = T.Priority
		LEFT JOIN tblUser TU
			ON TU.UserID = T.Agent
		LEFT JOIN tblTicketGroups TG
			ON TG.GroupID = T.`Group`
		WHERE
			T.CompanyID = p_CompanyID
			AND (p_Search = '' OR ( p_Search != '' AND (T.Subject like Concat('%',p_Search,'%') OR  T.Description like Concat('%',p_Search,'%') OR  T.Requester like Concat('%',p_Search,'%') OR  T.RequesterName like Concat('%',p_Search,'%') ) OR (T.TicketID in  ( select ael.TicketID from AccountEmailLog ael where (ael.Subject like Concat('%',p_Search,'%') OR  ael.Emailfrom like Concat('%',p_Search,'%') OR  ael.EmailfromName like Concat('%',p_Search,'%') OR  ael.Message like Concat('%',p_Search,'%') )   ) ) ) )
			AND (P_Status = '' OR find_in_set(T.`Status`,P_Status))
			AND (P_Priority = '' OR find_in_set(T.`Priority`,P_Priority))
			AND (P_Group = '' OR find_in_set(T.`Group`,P_Group))
			AND (P_Agent = '' OR find_in_set(T.`Agent`,P_Agent))
			AND (P_EmailAddresses = '' OR find_in_set(T.`Requester`,P_EmailAddresses))
			AND TG.GroupName IS NOT NULL;

	END IF;
	IF p_isExport = 1
	THEN
	SELECT
			T.TicketID,
			T.Subject,
			T.Requester,
			T.RequesterCC as 'CC',
			TFV.FieldValueCustomer  as 'Status',
			TP.PriorityValue as 'Priority',

			T.created_at as 'Date Created'
		FROM
			tblTickets T
		LEFT JOIN tblTicketfieldsValues TFV
			ON TFV.ValuesID = T.Status
		LEFT JOIN tblTicketPriority TP
			ON TP.PriorityID = T.Priority
		LEFT JOIN tblUser TU
			ON TU.UserID = T.Agent
		LEFT JOIN tblTicketGroups TG
			ON TG.GroupID = T.`Group`
		WHERE
			T.CompanyID = p_CompanyID
			AND (p_Search = '' OR ( p_Search != '' AND (T.Subject like Concat('%',p_Search,'%') OR  T.Description like Concat('%',p_Search,'%') OR  T.Requester like Concat('%',p_Search,'%') OR  T.RequesterName like Concat('%',p_Search,'%') ) OR (T.TicketID in  ( select ael.TicketID from AccountEmailLog ael where (ael.Subject like Concat('%',p_Search,'%') OR  ael.Emailfrom like Concat('%',p_Search,'%') OR  ael.EmailfromName like Concat('%',p_Search,'%') OR  ael.Message like Concat('%',p_Search,'%') )   ) ) ) )
			AND (P_Status = '' OR find_in_set(T.`Status`,P_Status))
			AND (P_Priority = '' OR find_in_set(T.`Priority`,P_Priority))
			AND (P_Group = '' OR find_in_set(T.`Group`,P_Group))
			AND (P_Agent = '' OR find_in_set(T.`Agent`,P_Agent))
			AND (P_EmailAddresses = '' OR find_in_set(T.`Requester`,P_EmailAddresses));
	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END;;
Delimiter ;


Delimiter ;;
DROP PROCEDURE IF EXISTS  `prc_GetSystemTicket`;
CREATE PROCEDURE `prc_GetSystemTicket`(
	IN `p_CompanyID` int,
	IN `p_Search` VARCHAR(100),
	IN `P_Status` VARCHAR(100),
	IN `P_Priority` VARCHAR(100),
	IN `P_Group` VARCHAR(100),
	IN `P_Agent` VARCHAR(100),
	IN `P_DueBy` VARCHAR(50),
	IN `P_CurrentDate` DATETIME,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT


)
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN
	DECLARE v_OffSet_ int;
	DECLARE v_Round_ int;
	DECLARE v_Groups_ varchar(200);

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	IF p_isExport = 0
	THEN
		SELECT
			T.TicketID,
			T.Subject,


  CASE
 	 WHEN T.AccountID>0   THEN     (SELECT CONCAT(IFNULL(TAA.AccountName,''),' (',T.Requester,')') FROM tblAccount TAA WHERE TAA.AccountID = T.AccountID  )
  	 WHEN T.ContactID>0   THEN     (select CONCAT(IFNULL(TCCC.FirstName,''),' ',IFNULL(TCCC.LastName,''),' (',T.Requester,')') FROM tblContact TCCC WHERE TCCC.ContactID = T.ContactID)
     WHEN T.UserID>0      THEN  	 (select CONCAT(IFNULL(TUU.FirstName,''),' ',IFNULL(TUU.LastName,''),' (',T.Requester,')') FROM tblUser TUU WHERE TUU.UserID = T.UserID )
    ELSE CONCAT(T.RequesterName,' (',T.Requester,')')
  END AS Requester,
			T.Requester as RequesterEmail,
			TFV.FieldValueAgent  as TicketStatus,
			TP.PriorityValue,
			concat(TU.FirstName,' ',TU.LastName) as Agent,
			TG.GroupName,
			T.created_at,

			(select tc.created_at from AccountEmailLog tc where tc.TicketID = T.TicketID  and tc.EmailCall =1 and tc.EmailParent>0 order by tc.AccountEmailLogID desc limit 1) as CustomerResponse,
		    (select tc.created_at from AccountEmailLog tc where tc.TicketID = T.TicketID  and tc.EmailCall =0  order by tc.AccountEmailLogID desc limit 1) as AgentResponse,
			(select TAC.AccountID from tblAccount TAC where 	TAC.Email = T.Requester or TAC.BillingEmail =T.Requester limit 1) as ACCOUNTID,
			T.`Read` as `Read`,
			T.TicketSlaID,
			T.DueDate,
			T.updated_at,
         T.Status,
         T.AgentRepliedDate,
         T.CustomerRepliedDate
		FROM
			tblTickets T
		LEFT JOIN tblTicketfieldsValues TFV
			ON TFV.ValuesID = T.Status
		LEFT JOIN tblTicketPriority TP
			ON TP.PriorityID = T.Priority
		LEFT JOIN tblUser TU
			ON TU.UserID = T.Agent
		LEFT JOIN tblTicketGroups TG
			ON TG.GroupID = T.`Group`

		WHERE
			T.CompanyID = p_CompanyID
			AND (p_Search = '' OR ( p_Search != '' AND (T.Subject like Concat('%',p_Search,'%') OR  T.Description like Concat('%',p_Search,'%') OR  T.Requester like Concat('%',p_Search,'%') OR  T.RequesterName like Concat('%',p_Search,'%') ) OR (T.TicketID in  ( select ael.TicketID from AccountEmailLog ael where (ael.Subject like Concat('%',p_Search,'%') OR  ael.Emailfrom like Concat('%',p_Search,'%') OR  ael.EmailfromName like Concat('%',p_Search,'%') OR  ael.Message like Concat('%',p_Search,'%') )   ) ) ) )
			AND (P_Status = '' OR find_in_set(T.`Status`,P_Status))
			AND (P_Priority = '' OR find_in_set(T.`Priority`,P_Priority))
			AND (P_Group = '' OR find_in_set(T.`Group`,P_Group))
			AND (P_Agent = '' OR find_in_set(T.`Agent`,P_Agent))
			AND (
					P_DueBy = '' OR
					(  P_DueBy != '' AND
						(
							   (find_in_set('Today',P_DueBy) AND DATE(T.DueDate) = DATE(P_CurrentDate))
							OR (find_in_set('Tomorrow',P_DueBy) AND DATE(T.DueDate) =  DATE(DATE_ADD(P_CurrentDate, INTERVAL 1 Day)))
							OR (find_in_set('Next_8_hours',P_DueBy) AND T.DueDate BETWEEN P_CurrentDate AND DATE_ADD(P_CurrentDate, INTERVAL 8 Hour))
							OR (find_in_set('Overdue',P_DueBy) AND P_CurrentDate >=  T.DueDate )
						)
					)
				)


			ORDER BY
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SubjectASC') THEN T.Subject
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SubjectDESC') THEN T.Subject
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusASC') THEN TicketStatus
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusDESC') THEN TicketStatus
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AgentASC') THEN TU.FirstName
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AgentDESC') THEN TU.FirstName
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN T.created_at
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN T.created_at
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN T.updated_at
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN T.updated_at
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RequesterASC') THEN T.Requester
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RequesterDESC') THEN T.Requester
			END DESC
			LIMIT
				p_RowspPage OFFSET v_OffSet_;

		SELECT
			COUNT(*) AS totalcount
				FROM
			tblTickets T
		LEFT JOIN tblTicketfieldsValues TFV
			ON TFV.ValuesID = T.Status
		LEFT JOIN tblTicketPriority TP
			ON TP.PriorityID = T.Priority
		LEFT JOIN tblUser TU
			ON TU.UserID = T.Agent
		LEFT JOIN tblTicketGroups TG
			ON TG.GroupID = T.`Group`
		WHERE
			T.CompanyID = p_CompanyID
			AND (p_Search = '' OR ( p_Search != '' AND (T.Subject like Concat('%',p_Search,'%') OR  T.Description like Concat('%',p_Search,'%') OR  T.Requester like Concat('%',p_Search,'%') OR  T.RequesterName like Concat('%',p_Search,'%') ) OR (T.TicketID in  ( select ael.TicketID from AccountEmailLog ael where (ael.Subject like Concat('%',p_Search,'%') OR  ael.Emailfrom like Concat('%',p_Search,'%') OR  ael.EmailfromName like Concat('%',p_Search,'%') OR  ael.Message like Concat('%',p_Search,'%') )   ) ) ) )
			AND (P_Status = '' OR find_in_set(T.`Status`,P_Status))
			AND (P_Priority = '' OR find_in_set(T.`Priority`,P_Priority))
			AND (P_Group = '' OR find_in_set(T.`Group`,P_Group))
			AND (P_Agent = '' OR find_in_set(T.`Agent`,P_Agent))
			AND ((P_DueBy = ''
			OR (find_in_set('Today',P_DueBy) AND DATE(T.DueDate) = DATE(P_CurrentDate)))
			OR (find_in_set('Tomorrow',P_DueBy) AND DATE(T.DueDate) =  DATE(DATE_ADD(P_CurrentDate, INTERVAL 1 Day)))
			OR (find_in_set('Next_8_hours',P_DueBy) AND T.DueDate BETWEEN P_CurrentDate AND DATE_ADD(P_CurrentDate, INTERVAL 8 Hour))
			OR (find_in_set('Overdue',P_DueBy) AND P_CurrentDate >=  T.DueDate));

	SELECT
			DISTINCT(TG.GroupID),
			TG.GroupName
	FROM
			tblTickets T
		LEFT JOIN tblTicketfieldsValues TFV
			ON TFV.ValuesID = T.Status
		LEFT JOIN tblTicketPriority TP
			ON TP.PriorityID = T.Priority
		LEFT JOIN tblUser TU
			ON TU.UserID = T.Agent
		LEFT JOIN tblTicketGroups TG
			ON TG.GroupID = T.`Group`
		WHERE
			T.CompanyID = p_CompanyID
			AND (p_Search = '' OR ( p_Search != '' AND (T.Subject like Concat('%',p_Search,'%') OR  T.Description like Concat('%',p_Search,'%') OR  T.Requester like Concat('%',p_Search,'%') OR  T.RequesterName like Concat('%',p_Search,'%') ) OR (T.TicketID in  ( select ael.TicketID from AccountEmailLog ael where (ael.Subject like Concat('%',p_Search,'%') OR  ael.Emailfrom like Concat('%',p_Search,'%') OR  ael.EmailfromName like Concat('%',p_Search,'%') OR  ael.Message like Concat('%',p_Search,'%') )   ) ) ) )
			AND (P_Status = '' OR find_in_set(T.`Status`,P_Status))
			AND (P_Priority = '' OR find_in_set(T.`Priority`,P_Priority))
			AND (P_Group = '' OR find_in_set(T.`Group`,P_Group))
			AND (P_Agent = '' OR find_in_set(T.`Agent`,P_Agent))
			AND ((P_DueBy = ''
			OR (find_in_set('Today',P_DueBy) AND DATE(T.DueDate) = DATE(P_CurrentDate)))
			OR (find_in_set('Tomorrow',P_DueBy) AND DATE(T.DueDate) =  DATE(DATE_ADD(P_CurrentDate, INTERVAL 1 Day)))
			OR (find_in_set('Next_8_hours',P_DueBy) AND T.DueDate BETWEEN P_CurrentDate AND DATE_ADD(P_CurrentDate, INTERVAL 8 Hour))
			OR (find_in_set('Overdue',P_DueBy) AND P_CurrentDate >=  T.DueDate ) );

	END IF;
	IF p_isExport = 1
	THEN
	SELECT
			T.TicketID,
			T.Subject,
			T.Requester,
			T.RequesterCC as 'CC',
			TFV.FieldValueAgent  as 'Status',
			TP.PriorityValue as 'Priority',
			concat(TU.FirstName,' ',TU.LastName) as Agent,
			T.created_at as 'Date Created',
			TG.GroupName as 'Group'
		FROM
			tblTickets T
		LEFT JOIN tblTicketfieldsValues TFV
			ON TFV.ValuesID = T.Status
		LEFT JOIN tblTicketPriority TP
			ON TP.PriorityID = T.Priority
		LEFT JOIN tblUser TU
			ON TU.UserID = T.Agent
		LEFT JOIN tblTicketGroups TG
			ON TG.GroupID = T.`Group`
		LEFT JOIN tblContact TCC
			ON TCC.Email = T.`Requester`

		WHERE
			T.CompanyID = p_CompanyID
			AND (p_Search = '' OR ( p_Search != '' AND (T.Subject like Concat('%',p_Search,'%') OR  T.Description like Concat('%',p_Search,'%') OR  T.Requester like Concat('%',p_Search,'%') OR  T.RequesterName like Concat('%',p_Search,'%') ) OR (T.TicketID in  ( select ael.TicketID from AccountEmailLog ael where (ael.Subject like Concat('%',p_Search,'%') OR  ael.Emailfrom like Concat('%',p_Search,'%') OR  ael.EmailfromName like Concat('%',p_Search,'%') OR  ael.Message like Concat('%',p_Search,'%') )   ) ) ) )
			AND (P_Status = '' OR find_in_set(T.`Status`,P_Status))
			AND (P_Priority = '' OR find_in_set(T.`Priority`,P_Priority))
			AND (P_Group = '' OR find_in_set(T.`Group`,P_Group))
			AND (P_Agent = '' OR find_in_set(T.`Agent`,P_Agent))
			AND ((P_DueBy = ''
			OR (find_in_set('Today',P_DueBy) AND DATE(T.DueDate) = DATE(P_CurrentDate)))
			OR (find_in_set('Tomorrow',P_DueBy) AND DATE(T.DueDate) =  DATE(DATE_ADD(P_CurrentDate, INTERVAL 1 Day)))
			OR (find_in_set('Next_8_hours',P_DueBy) AND T.DueDate BETWEEN P_CurrentDate AND DATE_ADD(P_CurrentDate, INTERVAL 8 Hour))
			OR (find_in_set('Overdue',P_DueBy) AND P_CurrentDate >=  T.DueDate ));
	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END;;
Delimiter ;


