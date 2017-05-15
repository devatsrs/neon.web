USE Ratemanagement3;
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES	(1, 'TICKETING_SYSTEM', '1');

DROP PROCEDURE IF EXISTS `prc_AssignSlaToTicket`;
DELIMITER //
CREATE PROCEDURE `prc_AssignSlaToTicket`(
	IN `p_CompanyID` INT,
	IN `p_TicketID` INT
)
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
			and
			(
				(pol.CompanyFilter is null  OR (pol.CompanyFilter is not null and FIND_IN_SET(v_AccountID,pol.CompanyFilter) > 0 ))
				OR
				(pol.GroupFilter is null  OR (pol.GroupFilter is not null and FIND_IN_SET(v_Group,pol.GroupFilter) > 0) )
				OR
				(pol.TypeFilter is null OR (pol.TypeFilter is not null and FIND_IN_SET(v_Type,pol.TypeFilter) > 0) )
			)
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

END//
DELIMITER ;
-- ##########################
DROP PROCEDURE IF EXISTS `prc_CheckDueTickets`;
DELIMITER //
CREATE PROCEDURE `prc_CheckDueTickets`(
	IN `p_CompanyID` int,
	IN `p_currentDateTime` DATETIME,
	IN `P_Group` VARCHAR(50),
	IN `P_Agent` VARCHAR(50)
)
BEGIN
	DECLARE V_Status varchar(100);
	DECLARE V_OverDue int(11);
	DECLARE V_DueToday int(11);
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT 
		 group_concat(TFV.ValuesID separator ',') INTO V_Status FROM tblTicketfieldsValues TFV 
	LEFT JOIN tblTicketfields TF 
		ON TF.TicketFieldsID = TFV.FieldsID
	WHERE 
		TF.FieldType = 'default_status' AND TFV.FieldValueAgent ='Open';  --  TFV.FieldValueAgent!='Closed' AND TFV.FieldValueAgent!='Resolved';
				

	-- find overdue
		SELECT 
		
			count(*) into V_OverDue
		 
		FROM 
			tblTickets T			
		WHERE   
			T.CompanyID = p_CompanyID	
			AND (V_Status = '' OR find_in_set(T.`Status`,V_Status) )
			AND (P_Group = '' OR FIND_IN_SET(T.`Group`,P_Group) )
			AND (P_Agent = '' OR T.`Agent` = P_Agent )
			AND DueDate is not null
			AND DueDate < p_currentDateTime;	
			

		-- find due today		
		
		SELECT 
		
			count(*) into   V_DueToday
		 
		FROM 
			tblTickets T			
		WHERE   
			T.CompanyID = p_CompanyID	
			AND (V_Status = '' OR find_in_set(T.`Status`,V_Status) )
			AND (P_Group = '' OR FIND_IN_SET(T.`Group`,P_Group) )
			AND (P_Agent = '' OR T.`Agent` = P_Agent )
			AND DueDate is not null
			AND DATE(DueDate) = DATE(p_currentDateTime);	
			 
			SELECT V_OverDue as OverDue,V_DueToday as DueToday;
			
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;
-- ##########################
DROP PROCEDURE IF EXISTS `prc_CheckTicketsSlaVoilation`;
DELIMITER //
CREATE PROCEDURE `prc_CheckTicketsSlaVoilation`(
	IN `p_CompanyID` int,
	IN `p_currentDateTime` DATETIME
)
BEGIN
	DECLARE P_Status varchar(100);
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	SET  sql_mode='';
	
	SELECT 
		 group_concat(TFV.ValuesID separator ',') INTO P_Status FROM tblTicketfieldsValues TFV 
	LEFT JOIN tblTicketfields TF 
		ON TF.TicketFieldsID = TFV.FieldsID
	WHERE 
		TF.FieldType = 'default_status' AND TFV.FieldValueAgent!='Closed' AND TFV.FieldValueAgent!='Resolved';
				
	 DROP TEMPORARY TABLE IF EXISTS tmp_tickets_sla_voilation_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tickets_sla_voilation_(
		TicketID int,
		TicketSlaID int,
		CreatedDate datetime,
		RespondTime datetime,
		ResolveTime datetime,
		IsRespondedVoilation int,
		RespondEmailTime datetime,
		DueDate datetime,
		IsResolvedVoilation int,
		EscalationEmail int	
	);
		insert into tmp_tickets_sla_voilation_
		SELECT 
			T.TicketID,				
			T.TicketSlaID as TicketSlaID,
			T.created_at as CreatedDate,
		   CASE WHEN (TST.RespondType = 'Minute') THEN
		       	DATE_ADD(T.created_at, INTERVAL TST.RespondValue Minute)  
	 	  		  WHEN RespondType = 'Hour' THEN
	   	 		DATE_ADD(T.created_at, INTERVAL TST.RespondValue Hour) 			
			 	  WHEN (TST.RespondType = 'Day') THEN
		      	 DATE_ADD(T.created_at, INTERVAL TST.RespondValue Day)  
	 	  		  WHEN RespondType = 'Month' THEN
	   	 		DATE_ADD(T.created_at, INTERVAL TST.RespondValue Month)  	   	 
	  END AS RespondTime,
	  	CASE WHEN (TST.ResolveType = 'Minute') THEN
		       DATE_ADD(T.DueDate, INTERVAL TST.ResolveValue Minute)  
	 	  	  WHEN ResolveType = 'Hour' THEN
		   	 DATE_ADD(T.DueDate, INTERVAL TST.ResolveValue Hour) 			
		 	  WHEN (TST.ResolveType = 'Day') THEN
		       DATE_ADD(T.DueDate, INTERVAL TST.ResolveValue Day)  
	 	  	  WHEN ResolveType = 'Month' THEN
	   		 DATE_ADD(T.DueDate, INTERVAL TST.ResolveValue Month)  	   	 
	  END AS ResolveTime,
	  T.RespondSlaPolicyVoilationEmailStatus AS IsRespondedVoilation,
	  '0000-00-00 00:00' as RespondEmailTime,
	  T.DueDate,
	  T.ResolveSlaPolicyVoilationEmailStatus AS IsResolvedVoilation,
	  TST.EscalationEmail as EscalationEmail
			 		
		FROM 
			tblTickets T			
		LEFT JOIN tblTicketSlaTarget TST
			ON TST.TicketSlaID = T.TicketSlaID											
		WHERE   
			T.CompanyID = p_CompanyID	
			AND TST.PriorityID = T.Priority		
			AND (P_Status = '' OR find_in_set(T.`Status`,P_Status))
			AND (T.RespondSlaPolicyVoilationEmailStatus = 0 OR T.ResolveSlaPolicyVoilationEmailStatus = 0)
			AND T.TicketSlaID>0;		
	
	    	/*UPDATE tmp_tickets_sla_voilation_ TSV
	    	SET TSV.RespondEmailTime = 
			CASE WHEN
			exists
				 (SELECT AE.created_at FROM AccountEmailLog AE  
		    	WHERE AE.TicketID = TSV.TicketID AND AE.EmailParent >  0 AND 
				AE.AccountID is null AND AE.ContactID is null  AND AE.UserID > 0
				order by AE.AccountEmailLogID desc limit 1) 
		   THEN 
				(SELECT AE.created_at FROM AccountEmailLog AE  
		    	WHERE AE.TicketID = TSV.TicketID AND AE.EmailParent >  0 AND 
				AE.AccountID is null AND AE.ContactID is null  AND AE.UserID > 0
				order by AE.AccountEmailLogID desc limit 1)
			 ELSE p_currentDateTime
			end;*/
			
			UPDATE tmp_tickets_sla_voilation_ TSV SET
			TSV.IsRespondedVoilation = 
			CASE  
			  WHEN TSV.IsRespondedVoilation =1 THEN 0 
			  WHEN p_currentDateTime>=TSV.RespondTime THEN 1 ELSE 0			
			END,
			TSV.IsResolvedVoilation  =
			CASE  
				 WHEN TSV.IsResolvedVoilation =1 THEN 0 
				WHEN p_currentDateTime>=TSV.ResolveTime THEN 1 ELSE 0
			END;
		
			SELECT * FROM tmp_tickets_sla_voilation_;
		
			
			/*SELECT TicketID,IsResolvedVoilation,IsRespondedVoilation,EscalationEmail FROM tmp_tickets_sla_voilation_;*/
			
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;
-- ##########################
DROP PROCEDURE IF EXISTS `prc_GetTicketBusinessHours`;
DELIMITER //
CREATE PROCEDURE `prc_GetTicketBusinessHours`(
	IN `p_CompanyID` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(30),
	IN `p_SortOrder` VARCHAR(10),
	IN `p_isExport` INT
)
BEGIN

DECLARE v_OffSet_ int;

   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
 
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	/*SELECT concat('myvar is ', v_OffSet_);*/ 
	
	IF p_isExport = 0
	THEN
		SELECT Name,Description,ID,IsDefault 
		FROM tblTicketBusinessHours  
		WHERE (CompanyID = p_CompanyID) 			
		ORDER BY
				CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameASC') THEN Name
            END ASC,
				CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameDESC') THEN Name
            END DESC,
            CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN Description
            END DESC,
				CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN Description
            END ASC				
							                    
			LIMIT p_RowspPage OFFSET v_OffSet_;
				
		SELECT COUNT(*) AS totalcount
		FROM tblTicketBusinessHours
		WHERE (CompanyID = p_CompanyID);
	END IF;
	
	IF p_isExport = 1
	THEN
	SELECT Name,Description,IsDefault as DefaultData
		FROM tblTicketBusinessHours  
		WHERE (CompanyID = p_CompanyID);
	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;
-- ##########################
DROP PROCEDURE IF EXISTS `prc_GetTicketSLA`;
DELIMITER //
CREATE PROCEDURE `prc_GetTicketSLA`(
	IN `p_CompanyID` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(30),
	IN `p_SortOrder` VARCHAR(10),
	IN `p_isExport` INT
)
BEGIN

DECLARE v_OffSet_ int;

   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
 
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	/*SELECT concat('myvar is ', v_OffSet_);*/ 
	
	IF p_isExport = 0
	THEN
		SELECT Name,Description,TicketSlaID,IsDefault 
		FROM tblTicketSla  
		WHERE (CompanyID = p_CompanyID) 			
		ORDER BY
				CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameASC') THEN Name
            END ASC,
				CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameDESC') THEN Name
            END DESC,
           CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN Description
            END DESC,
				CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN Description
            END ASC				
							                    
			LIMIT p_RowspPage OFFSET v_OffSet_;
				
		SELECT COUNT(*) AS totalcount
		FROM tblTicketSla
		WHERE (CompanyID = p_CompanyID);
	END IF;
	
	IF p_isExport = 1
	THEN
	SELECT Name,Description,IsDefault as DefaultData
		FROM tblTicketSla  
		WHERE (CompanyID = p_CompanyID);
	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;
-- ##########################
DELIMITER //
CREATE  PROCEDURE `prc_UpdateTicketSLADueDate`(
	IN `p_TicketID` INT,
	IN `p_PrevFieldValue` INT,
	IN `p_NewFieldValue` INT
)
BEGIN

	DECLARE 	v_created_at_from DATETIME;
	DECLARE 	v_created_at_to DATETIME;
	DECLARE 	v_DueDate DATETIME;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	-- update due date only when Status changed from SLATimer = 0 to 1 
	
	IF
	(
		(SELECT count(*) FROM tblTicketfieldsValues where FieldSlaTime = 0 and ValuesID=p_PrevFieldValue) > 0 AND
		(SELECT count(*) FROM tblTicketfieldsValues where FieldSlaTime = 1 and ValuesID=p_NewFieldValue) > 0
	) THEN


		SELECT DueDate into v_DueDate FROM tblTickets WHERE TicketID= p_TicketID;

          -- Previous entry - current entry
		SELECT created_at into v_created_at_from  FROM tblTicketLog where TicketID = p_TicketID order by TicketLogID desc limit 1,1;
          SELECT created_at into v_created_at_to FROM tblTicketLog where TicketID = p_TicketID  order by TicketLogID desc limit 1;

		-- Select DATE_ADD(v_DueDate, INTERVAL TIMESTAMPDIFF(Minute , v_created_at_from,v_created_at_to) Minute);

		UPDATE tblTickets
		SET DueDate = DATE_ADD(v_DueDate, INTERVAL TIMESTAMPDIFF(Minute , v_created_at_from,v_created_at_to) Minute)
		Where TicketID = p_TicketID;

		
	END IF;

 
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;
-- ##########################
CREATE TABLE IF NOT EXISTS `tblTicketBusinessHolidays` (
  `HolidayID` int(11) NOT NULL AUTO_INCREMENT,
  `BusinessHoursID` int(11) NOT NULL DEFAULT '0',
  `HolidayMonth` int(11) NOT NULL DEFAULT '0',
  `HolidayDay` int(11) NOT NULL DEFAULT '0',
  `HolidayName` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`HolidayID`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
-- ##########################
CREATE TABLE IF NOT EXISTS `tblTicketBusinessHours` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `IsDefault` tinyint(4) NOT NULL DEFAULT '0',
  `Name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Description` text COLLATE utf8_unicode_ci,
  `Timezone` varchar(100) COLLATE utf8_unicode_ci DEFAULT '0',
  `HoursType` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1 for default, 2 for change able',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
-- ##########################
CREATE TABLE IF NOT EXISTS `tblTicketSla` (
  `TicketSlaID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL DEFAULT '0',
  `IsDefault` tinyint(1) NOT NULL DEFAULT '0',
  `Name` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `Description` text COLLATE utf8_unicode_ci,
  `Status` tinyint(4) DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`TicketSlaID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `tblTicketSlaPolicyApplyTo` (
  `ApplyToID` int(11) NOT NULL AUTO_INCREMENT,
  `TicketSlaID` int(11) NOT NULL DEFAULT '0',
  `GroupFilter` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `TypeFilter` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `CompanyFilter` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ApplyToID`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `tblTicketSlaPolicyViolation` (
  `ViolationID` int(11) NOT NULL AUTO_INCREMENT,
  `TicketSlaID` int(11) NOT NULL DEFAULT '0',
  `Time` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `Value` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `VoilationType` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ViolationID`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `tblTicketSlaTarget` (
  `SlaTargetID` int(11) NOT NULL AUTO_INCREMENT,
  `TicketSlaID` int(11) NOT NULL DEFAULT '0',
  `PriorityID` tinyint(4) NOT NULL DEFAULT '0',
  `RespondValue` int(11) DEFAULT '0',
  `RespondType` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ResolveValue` int(11) DEFAULT '0',
  `ResolveType` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `OperationalHrs` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `EscalationEmail` tinyint(4) DEFAULT '1' COMMENT '0 for disable , 1 for enable',
  `created_at` datetime DEFAULT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`SlaTargetID`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `tblTicketsWorkingDays` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `BusinessHoursID` int(11) NOT NULL DEFAULT '0',
  `Day` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1 for monday....  7 for sunday',
  `Status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0 for disabled 1 for enabled',
  `StartTime` time NOT NULL DEFAULT '00:00:00',
  `EndTime` time NOT NULL DEFAULT '00:00:00',
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
-- ##########################
DROP PROCEDURE IF EXISTS `prc_getAccountTimeLine`;
DELIMITER //
CREATE PROCEDURE `prc_getAccountTimeLine`(
	IN `p_AccountID` INT,
	IN `p_CompanyID` INT,
	IN `p_TicketType` INT,
	IN `p_GUID` VARCHAR(100),
	IN `p_Time` DATE,
	IN `p_Start` INT,
	IN `p_RowspPage` INT
)
BEGIN
DECLARE v_OffSet_ int;
DECLARE v_ActiveSupportDesk int;
DECLARE v_contacts_ids varchar(200);
DECLARE v_contacts_emails varchar(200);
DECLARE v_account_emails varchar(200);
DECLARE v_account_contacts_emails varchar(300);

SET v_OffSet_ = p_Start;
SET sql_mode = 'ALLOW_INVALID_DATES';

/*delete 2 days old records*/
if(p_Start>0)
THEN
	DELETE FROM tbl_Account_Contacts_Activity  WHERE  `TableCreated_at` < DATE_SUB(p_Time, INTERVAL 2 DAY);
END IF;
if(p_Start= 0)
THEN
		/*storing contact ids and email addresses*/
		SELECT 
			group_concat(CC.ContactID separator ',')
		FROM
			tblContact CC
		WHERE 
			CC.Owner = p_AccountID	INTO v_contacts_ids;
			
		SELECT 
			group_concat(DISTINCT CC.Email separator ',')
		FROM
			tblContact CC
		WHERE 
			CC.Owner = p_AccountID	INTO v_contacts_emails;
			
		/*storing account emails*/	
		SELECT 
		(CASE WHEN tac.Email = tac.BillingEmail THEN tac.Email ELSE concat(tac.Email,',',tac.BillingEmail) END) 
		FROM
			tblAccount tac
		WHERE 
			tac.AccountID = p_AccountID	INTO v_contacts_emails;	
		/*combining account and contacts emails*/
		select concat(v_contacts_emails,',',v_contacts_emails) into v_account_contacts_emails;

		/*tasks start*/
			INSERT INTO tbl_Account_Contacts_Activity(  `Timeline_type`,  `TaskTitle`,  `TaskName`,  `TaskPriority`,  `DueDate`,  `TaskDescription`,  `TaskStatus`,  `followup_task`,  `TaskID`,  `Emailfrom`,  `EmailTo`,  `EmailToName`,  `EmailSubject`,  `EmailMessage`,  `EmailCc`, `EmailBcc`,  `EmailAttachments`,  `AccountEmailLogID`,  `NoteID`,  `Note`,  `TicketID`,  `TicketSubject`,  `TicketStatus`,  `RequestEmail`,  `TicketPriority`,  `TicketType`,  `TicketGroup`,  `TicketDescription`,  `CreatedBy`,  `created_at`,  `updated_at`,  `GUID`,`TableCreated_at`)
				SELECT 
				1 as Timeline_type,
				t.Subject,
				  concat(u.FirstName,' ',u.LastName) as Name,		
				 case when t.Priority =1
					  then 'High'
					  else
					   'Low' end  as Priority,
					DueDate,
				t.Description,
				bc.BoardColumnName as TaskStatus,
				t.Task_type,
				t.TaskID,
				'' as Emailfrom ,'' as EmailTo,'' as EmailToName,'' as EmailSubject,'' as Message,''as EmailCc,''as EmailBcc,''as EmailAttachments,0 as AccountEmailLogID,0 as NoteID,'' as Note ,
				0 as TicketID, '' as 	TicketSubject,	'' as	TicketStatus,	'' as 	RequestEmail,	'' as 	TicketPriority,	'' as 	TicketType,	'' as 	TicketGroup,	'' as TicketDescription,
				t.CreatedBy,t.created_at, t.updated_at,p_GUID as GUID,p_Time as TableCreated_at
			FROM tblTask t
			INNER JOIN tblCRMBoardColumn bc on  t.BoardColumnID = bc.BoardColumnID	
			JOIN tblUser u on  u.UserID = t.UsersIDs		
			WHERE t.AccountIDs = p_AccountID and t.CompanyID =p_CompanyID;
			/*tasks end*/
			
			
			/*emails start*/
			INSERT INTO tbl_Account_Contacts_Activity(  `Timeline_type`,  `TaskTitle`,  `TaskName`,  `TaskPriority`,  `DueDate`,  `TaskDescription`,  `TaskStatus`,  `followup_task`,  `TaskID`,  `Emailfrom`,  `EmailTo`,  `EmailToName`,  `EmailSubject`,  `EmailMessage`,  `EmailCc`, `EmailBcc`,  `EmailAttachments`,  `AccountEmailLogID`,  `NoteID`,  `Note`,  `TicketID`,  `TicketSubject`,  `TicketStatus`,  `RequestEmail`,  `TicketPriority`,  `TicketType`,  `TicketGroup`,  `TicketDescription`,  `CreatedBy`,  `created_at`,  `updated_at`,  `GUID`,`TableCreated_at`)
			select 2 as Timeline_type,'' as TaskTitle,'' as TaskName,'' as TaskPriority, '0000-00-00 00:00:00' as DueDate, '' as TaskDescription,'' as TaskStatus,0 as Task_type,0 as TaskID, Emailfrom, EmailTo,	
			case when concat(tu.FirstName,' ',tu.LastName) IS NULL or concat(tu.FirstName,' ',tu.LastName) = ''
					then ael.EmailTo
					else concat(tu.FirstName,' ',tu.LastName)
			   end as EmailToName, 
			Subject,Message,Cc,Bcc,AttachmentPaths as EmailAttachments,AccountEmailLogID,0 as NoteID,'' as Note ,0 as TicketID, '' as 	TicketSubject,	'' as	TicketStatus,	'' as 	RequestEmail,	'' as 	TicketPriority,	'' as 	TicketType,	'' as 	TicketGroup,	'' as TicketDescription,ael.CreatedBy,ael.created_at, ael.updated_at,p_GUID as GUID,p_Time as TableCreated_at
			from `AccountEmailLog` ael
			left JOIN tblUser tu
				ON tu.EmailAddress = ael.EmailTo
			where ael.CompanyID = p_CompanyID AND ((ael.AccountID = p_AccountID) OR (FIND_IN_SET(ael.ContactID,(v_contacts_ids)) ))			
			and ael.EmailParent=0 and ael.TicketID = 0
			order by ael.created_at desc;
			/*emails end*/
			
			/*nots start*/
			INSERT INTO tbl_Account_Contacts_Activity(  `Timeline_type`,  `TaskTitle`,  `TaskName`,  `TaskPriority`,  `DueDate`,  `TaskDescription`,  `TaskStatus`,  `followup_task`,  `TaskID`,  `Emailfrom`,  `EmailTo`,  `EmailToName`,  `EmailSubject`,  `EmailMessage`,  `EmailCc`, `EmailBcc`,  `EmailAttachments`,  `AccountEmailLogID`,  `NoteID`,  `Note`,  `TicketID`,  `TicketSubject`,  `TicketStatus`,  `RequestEmail`,  `TicketPriority`,  `TicketType`,  `TicketGroup`,  `TicketDescription`,  `CreatedBy`,  `created_at`,  `updated_at`,  `GUID`,`TableCreated_at`)
			select 3 as Timeline_type,'' as TaskTitle,'' as TaskName,'' as TaskPriority, '0000-00-00 00:00:00' as DueDate, '' as TaskDescription,'' as TaskStatus, 0 as Task_type,0 as TaskID, '' as Emailfrom ,'' as EmailTo,'' as EmailToName,'' as EmailSubject,'' as Message,''as EmailCc,''as EmailBcc,''as EmailAttachments,0 as AccountEmailLogID,NoteID,Note,0 as TicketID, '' as 	TicketSubject,	'' as	TicketStatus,	'' as 	RequestEmail,	'' as 	TicketPriority,	'' as 	TicketType,	'' as 	TicketGroup,	'' as TicketDescription,created_by,created_at,updated_at ,p_GUID as GUID,p_Time as TableCreated_at
			from `tblNote` 
			where (`CompanyID` = p_CompanyID and `AccountID` = p_AccountID and TicketID =0) order by created_at desc;
			
			INSERT INTO tbl_Account_Contacts_Activity(  `Timeline_type`,  `TaskTitle`,  `TaskName`,  `TaskPriority`,  `DueDate`,  `TaskDescription`,  `TaskStatus`,  `followup_task`,  `TaskID`,  `Emailfrom`,  `EmailTo`,  `EmailToName`,  `EmailSubject`,  `EmailMessage`,  `EmailCc`, `EmailBcc`,  `EmailAttachments`,  `AccountEmailLogID`,  `ContactNoteID`,  `Note`,  `TicketID`,  `TicketSubject`,  `TicketStatus`,  `RequestEmail`,  `TicketPriority`,  `TicketType`,  `TicketGroup`,  `TicketDescription`,  `CreatedBy`,  `created_at`,  `updated_at`,  `GUID`,`TableCreated_at`)
			select 3 as Timeline_type,'' as TaskTitle,'' as TaskName,'' as TaskPriority, '0000-00-00 00:00:00' as DueDate, '' as TaskDescription,'' as TaskStatus, 0 as Task_type,0 as TaskID, '' as Emailfrom ,'' as EmailTo,'' as EmailToName,'' as EmailSubject,'' as Message,''as EmailCc,''as EmailBcc,''as EmailAttachments,0 as AccountEmailLogID,NoteID,Note,0 as TicketID, '' as 	TicketSubject,	'' as	TicketStatus,	'' as 	RequestEmail,	'' as 	TicketPriority,	'' as 	TicketType,	'' as 	TicketGroup,	'' as TicketDescription,created_by,created_at,updated_at ,p_GUID as GUID,p_Time as TableCreated_at
			from `tblContactNote` where (`CompanyID` = p_CompanyID and FIND_IN_SET(ContactID,(v_contacts_ids)))  order by created_at desc;
			/*notes end*/
			
			/*tickets start*/
			/*IF v_ActiveSupportDesk=1*/
				
			IF p_TicketType=2 /*FRESH DESK TICKETS*/
			THEN
			INSERT INTO tbl_Account_Contacts_Activity(  `Timeline_type`,  `TaskTitle`,  `TaskName`,  `TaskPriority`,  `DueDate`,  `TaskDescription`,  `TaskStatus`,  `followup_task`,  `TaskID`,  `Emailfrom`,  `EmailTo`,  `EmailToName`,  `EmailSubject`,  `EmailMessage`,  `EmailCc`, `EmailBcc`,  `EmailAttachments`,  `AccountEmailLogID`,  `NoteID`,  `Note`,  `TicketID`,  `TicketSubject`,  `TicketStatus`,  `RequestEmail`,  `TicketPriority`,  `TicketType`,  `TicketGroup`,  `TicketDescription`,  `CreatedBy`,  `created_at`,  `updated_at`,  `GUID`,`TableCreated_at`)
			select 4 as Timeline_type,'' as TaskTitle,'' as TaskName,'' as TaskPriority, '0000-00-00 00:00:00' as DueDate, '' as TaskDescription,'' as TaskStatus, 0 as Task_type,0 as TaskID, '' as Emailfrom ,'' as EmailTo,'' as EmailToName,'' as EmailSubject,'' as Message,''as EmailCc,''as EmailBcc,''as EmailAttachments,0 as AccountEmailLogID, 0 as NoteID,'' as Note,
			TAT.TicketID,	TAT.Subject as 	TicketSubject,	TAT.Status as	TicketStatus,	TAT.RequestEmail as 	RequestEmail,	TAT.Priority as 	TicketPriority,	TAT.`Type` as 	TicketType,	TAT.`Group` as 	TicketGroup,	TAT.`Description` as TicketDescription,created_by,ApiCreatedDate as created_at,ApiUpdateDate as updated_at,p_GUID as GUID,p_Time as TableCreated_at 
			from 
				`tblHelpDeskTickets` TAT 
			where 
				(TAT.`CompanyID` = p_CompanyID and TAT.`AccountID` = p_AccountID and TAT.GUID = p_GUID);
			END IF;

			IF p_TicketType=1 /*SYSTEM TICKETS*/
			THEN
			INSERT INTO tbl_Account_Contacts_Activity(  `Timeline_type`,  `TaskTitle`,  `TaskName`,  `TaskPriority`,  `DueDate`,  `TaskDescription`,  `TaskStatus`,  `followup_task`,  `TaskID`,  `Emailfrom`,  `EmailTo`,  `EmailToName`,  `EmailSubject`,  `EmailMessage`,  `EmailCc`, `EmailBcc`,  `EmailAttachments`,  `AccountEmailLogID`,  `NoteID`,  `Note`,  `TicketID`,  `TicketSubject`,  `TicketStatus`,  `RequestEmail`,  `TicketPriority`,  `TicketType`,  `TicketGroup`,  `TicketDescription`,  `CreatedBy`,  `created_at`,  `updated_at`,  `GUID`,`TableCreated_at`)
				select 4 as Timeline_type,'' as TaskTitle,'' as TaskName,'' as TaskPriority, '0000-00-00 00:00:00' as DueDate, '' as TaskDescription,'' as TaskStatus, 0 as Task_type,0 as TaskID, '' as Emailfrom ,'' as EmailTo,'' as EmailToName,'' as EmailSubject,'' as Message,''as EmailCc,''as EmailBcc,''as EmailAttachments,0 as AccountEmailLogID, 0 as NoteID,'' as Note,
				TT.TicketID,TT.Subject as 	TicketSubject,	TFV.FieldValueAgent  as	TicketStatus,	TT.Requester as 	RequestEmail,TP.PriorityValue as TicketPriority,	TFVV.FieldValueAgent as 	TicketType,	TG.GroupName as TicketGroup,	TT.`Description` as TicketDescription,TT.created_by,TT.created_at,TT.updated_at,p_GUID as GUID,p_Time as TableCreated_at 
			from 
				`tblTickets` TT
			LEFT JOIN tblTicketfieldsValues TFV
				ON TFV.ValuesID = TT.Status		
			LEFT JOIN tblTicketPriority TP
				ON TP.PriorityID = TT.Priority
			LEFT JOIN tblTicketGroups TG
				ON TG.GroupID = TT.`Group`
			LEFT JOIN tblTicketfieldsValues TFVV
				ON TFVV.ValuesID = TT.Type
			where 			
				TT.`CompanyID` = p_CompanyID and 
			FIND_IN_SET(TT.Requester,(v_account_contacts_emails));	
			END IF;
			/*tickets end*/
END IF;
	select * from tbl_Account_Contacts_Activity where GUID = p_GUID order by created_at desc LIMIT p_RowspPage OFFSET v_OffSet_ ;
END//
DELIMITER ;
-- ##########################
DROP PROCEDURE IF EXISTS `prc_getContactTimeLine`;
DELIMITER //
CREATE PROCEDURE `prc_getContactTimeLine`(
	IN `p_ContactID` INT,
	IN `p_CompanyID` INT,
	IN `p_TicketType` INT,
	IN `p_GUID` VARCHAR(100),
	IN `p_Start` INT,
	IN `p_RowspPage` INT
)
BEGIN
DECLARE v_OffSet_ int;
DECLARE v_ActiveSupportDesk int;
SET v_OffSet_ = p_Start;
SET sql_mode = 'ALLOW_INVALID_DATES';

	DROP TEMPORARY TABLE IF EXISTS tmp_actvity_timeline_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_actvity_timeline_(
		`Timeline_type` int(11),		
		Emailfrom varchar(50),
		EmailTo varchar(50),
		EmailToName varchar(50),
		EmailSubject varchar(200),
		EmailMessage LONGTEXT,
		EmailCc varchar(500),
		EmailBcc varchar(500),
		EmailAttachments LONGTEXT,
		AccountEmailLogID int(11),
	    NoteID int(11),
		Note longtext,		
		TicketID int(11),
		TicketSubject varchar(200),
		TicketStatus varchar(100),
		RequestEmail varchar(100),
		TicketPriority varchar(100),
		TicketType varchar(100),
		TicketGroup varchar(100),
		TicketDescription LONGTEXT,		
		CreatedBy varchar(50),
		created_at datetime,
		updated_at datetime		
	);		
	
	
	INSERT INTO tmp_actvity_timeline_
	select 2 as Timeline_type, Emailfrom, EmailTo,	
	case when concat(tu.FirstName,' ',tu.LastName) IS NULL or concat(tu.FirstName,' ',tu.LastName) = ''
            then ael.EmailTo
            else concat(tu.FirstName,' ',tu.LastName)
       end as EmailToName, 
	Subject,Message,Cc,Bcc,AttachmentPaths as EmailAttachments,AccountEmailLogID,0 as NoteID,'' as Note ,0 as TicketID, '' as 	TicketSubject,	'' as	TicketStatus,	'' as 	RequestEmail,	'' as 	TicketPriority,	'' as 	TicketType,	'' as 	TicketGroup,	'' as TicketDescription,ael.CreatedBy,ael.created_at, ael.updated_at 
	from `AccountEmailLog` ael
	left JOIN tblUser tu
		ON tu.EmailAddress = ael.EmailTo
	where 
	ael.ContactID = p_ContactID and
	ael.CompanyID = p_CompanyID and 
	ael.UserType=1 and
	ael.EmailParent=0 and
	ael.TicketID = 0
	order by ael.created_at desc;
	
	INSERT INTO tmp_actvity_timeline_
	select 3 as Timeline_type, '' as Emailfrom ,'' as EmailTo,'' as EmailToName,'' as EmailSubject,'' as Message,''as EmailCc,''as EmailBcc,''as EmailAttachments,0 as AccountEmailLogID,NoteID,Note,0 as TicketID, '' as 	TicketSubject,	'' as	TicketStatus,	'' as 	RequestEmail,	'' as 	TicketPriority,	'' as 	TicketType,	'' as 	TicketGroup,	'' as TicketDescription,created_by,created_at,updated_at 
	from `tblContactNote` where (`CompanyID` = p_CompanyID and `ContactID` = p_ContactID) order by created_at desc;
	/*IF v_ActiveSupportDesk=1*/

	IF p_TicketType=2 /*FRESH DESK TICKETS*/
	THEN
	INSERT INTO tmp_actvity_timeline_
	select 4 as Timeline_type, '' as Emailfrom ,'' as EmailTo,'' as EmailToName,'' as EmailSubject,'' as Message,''as EmailCc,''as EmailBcc,''as EmailAttachments,0 as AccountEmailLogID, 0 as NoteID,'' as Note,
	TAT.TicketID,	TAT.Subject as 	TicketSubject,	TAT.Status as	TicketStatus,	TAT.RequestEmail as 	RequestEmail,	TAT.Priority as 	TicketPriority,	TAT.`Type` as 	TicketType,	TAT.`Group` as 	TicketGroup,	TAT.`Description` as TicketDescription,created_by,ApiCreatedDate as created_at,ApiUpdateDate as updated_at from `tblHelpDeskTickets` TAT where (TAT.`CompanyID` = p_CompanyID and TAT.`ContactID` = p_ContactID and TAT.GUID = p_GUID);
	END IF;

	IF p_TicketType=1 /*SYSTEM TICKETS*/
	THEN
	INSERT INTO tmp_actvity_timeline_
		select 4 as Timeline_type, '' as Emailfrom ,'' as EmailTo,'' as EmailToName,'' as EmailSubject,'' as Message,''as EmailCc,''as EmailBcc,''as EmailAttachments,0 as AccountEmailLogID, 0 as NoteID,'' as Note,
		TT.TicketID,TT.Subject as 	TicketSubject,	TFV.FieldValueAgent  as	TicketStatus,	TT.Requester as 	RequestEmail,TP.PriorityValue as TicketPriority,	TFVV.FieldValueAgent as 	TicketType,	TG.GroupName as TicketGroup,	TT.`Description` as TicketDescription,TT.created_by,TT.created_at,TT.updated_at 
	from 
		`tblTickets` TT
	LEFT JOIN tblTicketfieldsValues TFV
		ON TFV.ValuesID = TT.Status		
	LEFT JOIN tblTicketPriority TP
		ON TP.PriorityID = TT.Priority
	LEFT JOIN tblTicketGroups TG
		ON TG.GroupID = TT.`Group`
	LEFT JOIN tblTicketfieldsValues TFVV
		ON TFVV.ValuesID = TT.Type
	LEFT JOIN tblContact TC
		ON TC.Email = TT.Requester		
	where 			
		(TT.`CompanyID` = p_CompanyID and TC.`ContactID` = p_ContactID);	
	END IF;
	
	select * from tmp_actvity_timeline_ order by created_at desc LIMIT p_RowspPage OFFSET v_OffSet_ ;
END//
DELIMITER ;
-- ####################################
DROP PROCEDURE IF EXISTS `prc_GetSystemTicket`;
DELIMITER //
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
			/*CASE WHEN (ISNULL(T.RequesterName) OR T.RequesterName='')  THEN T.Requester ElSE concat(T.RequesterName," (",T.Requester,")") END as Requester,*/
			/*IFNULL(IFNULL((select AccountName from tblAccount TACC where 	TACC.Email = T.Requester or TACC.BillingEmail =T.Requester limit 1),(select CONCAT(TA.FirstName,' ',TA.LastName) from tblContact TA where 	TA.Email = T.Requester  limit 1)),CASE WHEN (ISNULL(T.RequesterName) OR T.RequesterName='')  THEN T.Requester ElSE concat(T.RequesterName," (",T.Requester,")") END) AS Requester,				*/
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
			/*(select tc.Emailfrom from AccountEmailLog tc where tc.TicketID = T.TicketID  and tc.EmailCall =1 order by tc.AccountEmailLogID desc limit 1) as CustomerResponse,			*/
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
			AND (p_Search = '' OR (T.Subject like Concat('%',p_Search,'%') OR  T.Description like Concat('%',p_Search,'%') OR  T.Requester like Concat('%',p_Search,'%') OR  T.RequesterName like Concat('%',p_Search,'%')   ))
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
			
			/*AND ((p_AccessPermission = 1) OR (p_AccessPermission=2 and find_in_set(T.`Group`,v_Groups_)) OR (p_AccessPermission=3 and find_in_set(T.`Agent`,p_User)))		*/
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
			AND (p_Search = '' OR (T.Subject like Concat('%',p_Search,'%') OR  T.Description like Concat('%',p_Search,'%') OR  T.Requester like Concat('%',p_Search,'%') OR  T.RequesterName like Concat('%',p_Search,'%')   ))
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
			AND (p_Search = '' OR (T.Subject like Concat('%',p_Search,'%') OR  T.Description like Concat('%',p_Search,'%') OR  T.Requester like Concat('%',p_Search,'%') OR  T.RequesterName like Concat('%',p_Search,'%')   ))
			AND (P_Status = '' OR find_in_set(T.`Status`,P_Status))	
			AND (P_Priority = '' OR find_in_set(T.`Priority`,P_Priority))
			AND (P_Group = '' OR find_in_set(T.`Group`,P_Group))
			AND (P_Agent = '' OR find_in_set(T.`Agent`,P_Agent))
			AND ((P_DueBy = '' 
			OR (find_in_set('Today',P_DueBy) AND DATE(T.DueDate) = DATE(P_CurrentDate)))
			OR (find_in_set('Tomorrow',P_DueBy) AND DATE(T.DueDate) =  DATE(DATE_ADD(P_CurrentDate, INTERVAL 1 Day)))
			OR (find_in_set('Next_8_hours',P_DueBy) AND T.DueDate BETWEEN P_CurrentDate AND DATE_ADD(P_CurrentDate, INTERVAL 8 Hour))
			OR (find_in_set('Overdue',P_DueBy) AND P_CurrentDate >=  T.DueDate ) );			
		/*select v_OffSet_,p_RowspPage;*/		
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
			AND (p_Search = '' OR (T.Subject like Concat('%',p_Search,'%') OR  T.Description like Concat('%',p_Search,'%') OR  T.Requester like Concat('%',p_Search,'%') OR  T.RequesterName like Concat('%',p_Search,'%')   ))
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
END//
DELIMITER ;
-- ####################################
DROP PROCEDURE IF EXISTS `prc_GetSystemTicketCustomer`;
DELIMITER //
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
			AND (p_Search = '' OR (T.Subject like Concat('%',p_Search,'%') OR  T.Description like Concat('%',p_Search,'%') OR  T.Requester like Concat('%',p_Search,'%') OR  T.RequesterName like Concat('%',p_Search,'%')   ))
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
			AND (p_Search = '' OR (T.Subject like Concat('%',p_Search,'%') OR  T.Description like Concat('%',p_Search,'%') OR  T.Requester like Concat('%',p_Search,'%') OR  T.RequesterName like Concat('%',p_Search,'%')   ))						
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
			AND (p_Search = '' OR (T.Subject like Concat('%',p_Search,'%') OR  T.Description like Concat('%',p_Search,'%') OR  T.Requester like Concat('%',p_Search,'%') OR  T.RequesterName like Concat('%',p_Search,'%')   ))						
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
			/*concat(TU.FirstName,' ',TU.LastName) as Agent,*/
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
			AND (p_Search = '' OR (T.Subject like Concat('%',p_Search,'%') OR  T.Description like Concat('%',p_Search,'%')))
			AND (P_Status = '' OR find_in_set(T.`Status`,P_Status))	
			AND (P_Priority = '' OR find_in_set(T.`Priority`,P_Priority))
			AND (P_Group = '' OR find_in_set(T.`Group`,P_Group))
			AND (P_Agent = '' OR find_in_set(T.`Agent`,P_Agent))
			AND (P_EmailAddresses = '' OR find_in_set(T.`Requester`,P_EmailAddresses));
	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;
-- #########################
DROP PROCEDURE IF EXISTS `prc_GetTicketDashboardTimeline`;
DELIMITER //
CREATE PROCEDURE `prc_GetTicketDashboardTimeline`(
	IN `p_CompanyID` INT,
	IN `P_Group` INT,
	IN `P_Agent` INT,
	IN `p_Time` DATETIME,
	IN `p_PageNumber` INT,
	IN `p_RowsPage` INT
)
BEGIN
	DECLARE v_MAXDATE DATETIME;

	SELECT MAX(created_at) as created_at INTO v_MAXDATE FROM tblTicketDashboardTimeline;
	IF(p_PageNumber=0)
	THEN
		DELETE FROM tblTicketDashboardTimeline WHERE created_at_table < DATE_SUB(p_Time, INTERVAL 1 MONTH);
		INSERT INTO tblTicketDashboardTimeline
		SELECT
			NULL,
			tc.CompanyID,
			1 as TimeLineType,
			CONCAT(IFNULL(u.FirstName,''),' ',IFNULL(u.LastName,'')) as UserName,
			u.UserID,
			CASE WHEN tc.AccountID != 0 THEN tc.AccountID
					WHEN tc.ContactID != 0 THEN tc.ContactID
					ELSE 0 END as CustomerID,
			CASE WHEN tc.AccountID != 0 THEN 1
					WHEN tc.ContactID != 0 THEN 2
					ELSE 0 END as CustomerType,
			acel.EmailCall,
			tc.TicketID,
			CASE WHEN tc.AccountEmailLogID != 0 AND acel.EmailParent=0 THEN 1 ELSE 0 END as TicketSubmit,
			tc.Subject,
			acel.AccountEmailLogID as ID,
			tc.Agent,
			tc.`Group`,
			0 as TicketFieldID,
			0 as TicketFieldValueFromID,
			0 as TicketFieldValueToID,
			acel.created_at,
			p_Time
		FROM AccountEmailLog acel
		INNER JOIN tblTickets tc ON tc.TicketID = acel.TicketID
		LEFT JOIN tblAccount ac ON ac.AccountID = acel.AccountID
		LEFT JOIN tblUser u ON u.UserID = acel.UserID
		WHERE acel.CompanyID = p_CompanyID
		AND (v_MAXDATE IS NULL OR acel.created_at > v_MAXDATE)
		AND acel.EmailCall = 0
		AND u.UserID IS NOT NULL;

		INSERT INTO tblTicketDashboardTimeline
		SELECT
		NULL,
		tc.CompanyID,
		2 as TimeLineType,
		CONCAT(IFNULL(u.FirstName,''),' ',IFNULL(u.LastName,''))  as UserName,
		TN.UserID as UserID,
		0 as CustomerID,
		0 as CustomerType,
		0 as EmailCall,
		tc.TicketID,
		IF(tc.AccountEmailLogID = 0,0,1) as TicketSubmit,
		tc.Subject,
		TN.NoteID as ID,
		tc.Agent,
		tc.`Group`,
		0 as TicketFieldID,
		0 as TicketFieldValueFromID,
		0 as TicketFieldValueToID,
		TN.created_at,
		p_Time
		FROM `tblNote` TN
		INNER JOIN tblTickets tc ON tc.TicketID = TN.TicketID
		INNER JOIN tblUser u ON u.UserID = TN.UserID
		WHERE TN.CompanyID = p_CompanyID
		AND (v_MAXDATE IS NULL OR TN.created_at > v_MAXDATE);

		INSERT INTO tblTicketDashboardTimeline
		SELECT
			NULL,
			tc.CompanyID,
			3 as TimeLineType,
			CASE WHEN tl.AccountID = 0 THEN CONCAT(IFNULL(u.FirstName,''),' ',IFNULL(u.LastName,'')) ELSE CONCAT(IFNULL(a.FirstName,''),' ',IFNULL(a.LastName,''))  END as UserName,
			tl.UserID as UserID,
			CASE WHEN tl.TicketFieldID = 0 THEN
				CASE WHEN tc.AccountID != 0 THEN tc.AccountID
						WHEN tc.ContactID != 0 THEN tc.ContactID
						ELSE 0 END
			ELSE
				tl.AccountID
			END as CustomerID,
			CASE WHEN tl.TicketFieldID = 0 THEN
				CASE WHEN tc.AccountID != 0 THEN 1
						WHEN tc.ContactID != 0 THEN 2
						ELSE 0 END
				ELSE 0 END as CustomerType,
			0 as EmailCall,
			tc.TicketID,
			IF(tc.AccountEmailLogID = 0,0,1) as TicketSubmit,
			tc.Subject,
			tl.TicketLogID as ID,
			tc.Agent,
			tc.`Group`,
			tl.TicketFieldID,
			tl.TicketFieldValueFromID,
			tl.TicketFieldValueToID,
			tl.created_at,
			p_Time
		FROM tblTicketLog tl
		INNER JOIN tblTickets tc ON tc.TicketID = tl.TicketID
		LEFT JOIN tblUser u ON u.UserID = tl.UserID
		LEFT JOIN tblAccount a ON a.AccountID = tl.AccountID
		WHERE tl.CompanyID = p_CompanyID
		AND (v_MAXDATE IS NULL OR tl.created_at > v_MAXDATE);
	END IF;
	SELECT * FROM tblTicketDashboardTimeline tl
	WHERE (P_Agent = 0 OR tl.AgentID = p_Agent)
	AND(P_Group = 0 OR tl.`GroupID` = p_Group)
	ORDER BY created_at DESC
	LIMIT p_RowsPage OFFSET p_PageNumber;
END//
DELIMITER ;
-- #########################
DROP PROCEDURE IF EXISTS `prc_getTicketTimeline`;
DELIMITER //
CREATE PROCEDURE `prc_getTicketTimeline`(
	IN `p_CompanyID` INT,
	IN `p_TicketID` INT,
	IN `p_isCustomer` INT
)
BEGIN

DECLARE v_EmailParent int;	
	select AccountEmailLogID into v_EmailParent from tblTickets where TicketID = p_TicketID;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_ticket_timeline_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_ticket_timeline_(
		`Timeline_type` int(11),		
		EmailCall int(11),
		EmailfromName varchar(200),
		EmailTo varchar(200),
		Emailfrom varchar(200),
		EmailMessage LONGTEXT,
		EmailCc varchar(500),
		EmailBcc varchar(500),
		AttachmentPaths LONGTEXT,
		AccountEmailLogID int(11),
	    NoteID int(11),
		Note longtext,			
		CreatedBy varchar(50),
		created_at datetime,
		updated_at datetime		
	);

	INSERT INTO tmp_ticket_timeline_
	select 1 as Timeline_type,EmailCall,EmailfromName,EmailTo,Emailfrom,Message,Cc,Bcc,IFNULL(AttachmentPaths,'a:0:{}'),AccountEmailLogID,0 as NoteID,'' as Note,ael.CreatedBy,ael.created_at, ael.updated_at 
	from `AccountEmailLog` ael	
	where 
	/*ael.EmailParent = v_EmailParent and*/
	ael.TicketID = p_TicketID and
	ael.CompanyID = p_CompanyID 
	and ael.EmailParent > 0
	/*and ael.AccountEmailLogID NOT IN (SELECT AEE.AccountEmailLogID FROM AccountEmailLog AEE WHERE AEE.EmailParent = 0 )	*/
	order by ael.created_at desc;
	
	IF p_isCustomer =0
	THEN
	
	INSERT INTO tmp_ticket_timeline_
	select 2 as Timeline_type,0 as EmailCall,'' as EmailfromName,'' as EmailTo,'' as Emailfrom,'' as Message,'' as Cc,'' as Bcc,'a:0:{}' as AttachmentPaths,0 as AccountEmailLogID,NoteID,Note,TN.created_by,TN.created_at, TN.updated_at 
	from `tblNote` TN	
	where 
	TN.TicketID = p_TicketID and
	TN.CompanyID = p_CompanyID  	
	order by TN.created_at desc;
END IF;
	select * from tmp_ticket_timeline_  order by created_at asc;		
END//
DELIMITER ;
-- ##############################
ALTER TABLE `tblTicketDashboardTimeline`
	ADD COLUMN `CustomerType` INT(11) NULL DEFAULT '0' AFTER `CustomerID`;

-- ############################
ALTER TABLE `tblTicketfieldsValues`
	CHANGE COLUMN `FieldSlaTime` `FieldSlaTime` TINYINT(1) NULL DEFAULT NULL COMMENT '0 for disable, 1 for active, 2 for inactive' COLLATE 'utf8_unicode_ci' AFTER `FieldValueCustomer`;
-- ###############################################
ALTER TABLE `tblTicketGroups`
	ADD COLUMN `GroupBusinessHours` INT(11) NOT NULL DEFAULT '0' AFTER `GroupDescription`;
-- ##############################
ALTER TABLE `tblTickets` 
ADD COLUMN `TicketSlaID` INT(11) NULL DEFAULT NULL AFTER `EscalationEmail`;

ALTER TABLE `tblTickets`
	ADD COLUMN `RespondSlaPolicyVoilationEmailStatus` TINYINT(4) NULL DEFAULT '0' AFTER `TicketSlaID`;
	
	ALTER TABLE `tblTickets`
	ADD COLUMN `ResolveSlaPolicyVoilationEmailStatus` TINYINT(4) NULL DEFAULT '0' AFTER `RespondSlaPolicyVoilationEmailStatus`;
	
ALTER TABLE `tblTickets`
	ADD COLUMN `DueDate` DATETIME NULL DEFAULT NULL AFTER `ResolveSlaPolicyVoilationEmailStatus`;
	
ALTER TABLE `tblTickets`
	ADD COLUMN `CustomDueDate` TINYINT(1) NOT NULL DEFAULT '0' AFTER `DueDate`;	
	
ALTER TABLE `tblTickets`
	ADD COLUMN `AgentRepliedDate` DATETIME NULL DEFAULT NULL AFTER `CustomDueDate`;
	
ALTER TABLE `tblTickets`
	ADD COLUMN `CustomerRepliedDate` DATETIME NULL DEFAULT NULL AFTER `AgentRepliedDate`;	
-- ##############################
/*INSERT INTO `tblResourceCategories` (`ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES
	('Tickets.All', 1, 4),
	('Tickets.View', 1, 4),
	('Tickets.Add', 1, 4),
	('Tickets.Edit', 1, 4),
	('Tickets.Delete', 1, 4),
	('TicketsFields.Edit', 1, 4),
	('TicketsGroups.All', 1, 4),
	('TicketsGroups.View', 1, 4),
	('TicketsGroups.Add', 1, 4),
	('TicketsGroups.Edit', 1, 4),
	('TicketsGroups.Delete', 1, 4)
	('Tickets.View.GlobalAccess', 1, 4),
	('Tickets.View.GroupAccess', 1, 4),
	('Tickets.View.RestrictedAccess', 1, 4),
	('TicketDashboardSummaryWidgets.View', 1, 4),
	('TicketDashboardTimeLineWidgets.View', 1, 4),
	('TicketsSla.Add', 1, 4),
	('TicketsSla.Edit', 1, 4),
	('TicketsSla.Delete', 1, 4),
	('TicketsSla.View', 1, 4),
	('TicketsSla.All', 1, 4),
	('TicketsBusinessHours.Add', 1, 4),
	('TicketsBusinessHours.Edit', 1, 4),
	('TicketsBusinessHours.Delete', 1, 4),
	('TicketsBusinessHours.View', 1, 4),
	('TicketsBusinessHours.All', 1, 4),
	('TicketDashboard.View', 1, 4);
-- ####################################
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='Tickets.View' limit 1) WHERE  `ResourceName`='Tickets.index';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='Tickets.All' limit 1) WHERE  `ResourceName`='Tickets.*';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsGroups.View' limit 1) WHERE  `ResourceName`='TicketsGroup.Activate_support_email';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsGroups.View' limit 1) WHERE  `ResourceName`='TicketsGroup.index';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsGroups.All' limit 1) WHERE  `ResourceName`='TicketsGroup.*';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsGroups.Add' limit 1) WHERE  `ResourceName`='TicketsGroup.add';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsGroups.Add' limit 1) WHERE  `ResourceName`='TicketsGroup.Store';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsGroups.View' limit 1) WHERE  `ResourceName`='TicketsGroup.ajax_datagrid';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsGroups.Edit' limit 1) WHERE  `ResourceName`='TicketsGroup.Edit';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsGroups.Edit' limit 1) WHERE  `ResourceName`='TicketsGroup.Update';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsGroups.Delete' limit 1) WHERE  `ResourceName`='TicketsGroup.delete';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsGroups.View' limit 1) WHERE  `ResourceName`='TicketsGroup.send_activation_single';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='Tickets.View' limit 1) WHERE  `ResourceName`='TicketsGroup.get_group_agents';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsFields.Edit' limit 1) WHERE  `ResourceName`='TicketsFields.index';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsFields.Edit' limit 1) WHERE  `ResourceName`='TicketsFields.*';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsFields.Edit' limit 1) WHERE  `ResourceName`='TicketsFields.iframe';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsFields.Edit' limit 1) WHERE  `ResourceName`='TicketsFields.iframeSubmit';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsFields.Edit' limit 1) WHERE  `ResourceName`='TicketsFields.ajax_ticketsfields';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsFields.Edit' limit 1) WHERE  `ResourceName`='TicketsFields.Ajax_Ticketsfields_Choices';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsFields.Edit' limit 1) WHERE  `ResourceName`='TicketsFields.Save_Single_Field';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsFields.Edit' limit 1) WHERE  `ResourceName`='TicketsFields.Update_Fields_Sorting';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='Tickets.View' limit 1) WHERE  `ResourceName`='Tickets.ajex_result';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='Tickets.Add' limit 1) WHERE  `ResourceName`='Tickets.add';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='Tickets.Add' limit 1) WHERE  `ResourceName`='Tickets.uploadFile';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='Tickets.Edit' limit 1) WHERE  `ResourceName`='Tickets.deleteUploadFile';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='Tickets.Add' limit 1) WHERE  `ResourceName`='Tickets.Store';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='Tickets.Edit' limit 1) WHERE  `ResourceName`='Tickets.edit';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='Tickets.Edit' limit 1) WHERE  `ResourceName`='Tickets.Update';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='Tickets.Edit' limit 1) WHERE  `ResourceName`='Tickets.UpdateDetailPage';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='Tickets.Delete' limit 1) WHERE  `ResourceName`='Tickets.delete';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='Tickets.View' limit 1) WHERE  `ResourceName`='Tickets.Detail';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='Tickets.Edit' limit 1) WHERE  `ResourceName`='Tickets.TicketAction';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='Tickets.Edit' limit 1) WHERE  `ResourceName`='Tickets.UpdateTicketAttributes';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='Tickets.Edit' limit 1) WHERE  `ResourceName`='Tickets.ActionSubmit';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='Tickets.View' limit 1) WHERE  `ResourceName`='Tickets.getConversationAttachment';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='Tickets.View' limit 1) WHERE  `ResourceName`='TicketsTickets.GetTicketAttachment';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='Tickets.Edit' limit 1) WHERE  `ResourceName`='Tickets.CloseTicket';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='Tickets.Add' limit 1) WHERE  `ResourceName`='Tickets.ComposeEmail';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='Tickets.Add' limit 1) WHERE  `ResourceName`='Tickets.SendMail';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='Tickets.Edit' limit 1) WHERE  `ResourceName`='Tickets.add_note';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsGroups.View' limit 1) WHERE  `ResourceName`='TicketsGroup.validatesmtp';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='Tickets.View' limit 1) WHERE  `ResourceName`='Dashboard.TicketDashboard';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsSla.View' limit 1) WHERE  `ResourceName`='TicketsSla.index';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsSla.All' limit 1) WHERE  `ResourceName`='TicketsSla.*';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsSla.Edit' limit 1) WHERE  `ResourceName`='TicketsSla.edit';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsSla.View' limit 1) WHERE  `ResourceName`='TicketsSla.ajax_datagrid';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsSla.Add' limit 1) WHERE  `ResourceName`='TicketsSla.add';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsSla.Add' limit 1) WHERE  `ResourceName`='TicketsSla.store';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsSla.Delete' limit 1) WHERE  `ResourceName`='TicketsSla.delete';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsSla.Edit' limit 1) WHERE  `ResourceName`='TicketsSla.update';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='Tickets.View' limit 1) WHERE  `ResourceName`='Tickets.ajex_result_export';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='Tickets.Edit' limit 1) WHERE  `ResourceName`='Tickets.UpdateTicketDueTime';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsBusinessHours.View' limit 1) WHERE  `ResourceName`='TicketsBusinessHours.index';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsBusinessHours.All' limit 1) WHERE  `ResourceName`='TicketsBusinessHours.*';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsBusinessHours.View' limit 1) WHERE  `ResourceName`='TicketsBusinessHours.ajax_datagrid';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsBusinessHours.Add' limit 1) WHERE  `ResourceName`='TicketsBusinessHours.create';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsBusinessHours.Add' limit 1) WHERE  `ResourceName`='TicketsBusinessHours.store';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsBusinessHours.Delete' limit 1) WHERE  `ResourceName`='TicketsBusinessHours.delete';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsBusinessHours.Edit' limit 1) WHERE  `ResourceName`='TicketsBusinessHours.edit';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketsBusinessHours.Edit' limit 1) WHERE  `ResourceName`='TicketsBusinessHours.update';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='Tickets.Edit' limit 1) WHERE  `ResourceName`='Tickets.BulkAction';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='Tickets.Delete' limit 1) WHERE  `ResourceName`='Tickets.BulkDelete';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketDashboardSummaryWidgets.View' limit 1) WHERE  `ResourceName`='TicketDashboard.ticketSummaryWidget';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='TicketDashboardTimeLineWidgets.View' limit 1) WHERE  `ResourceName`='TicketDashboard.ticketTimeLineWidget';
*/
-- ############################################
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1298, 'AccountService.All', 1, 3);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1297, 'AccountService.View', 1, 3);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1296, 'AccountService.Delete', 1, 3);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1295, 'AccountService.Edit', 1, 3);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1294, 'AccountService.Add', 1, 3);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1293, 'Service.Add', 1, 7);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1292, 'Service.Delete', 1, 7);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1291, 'Service.Edit', 1, 7);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1290, 'Service.View', 1, 7);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1289, 'Service.All', 1, 7);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1282, 'TicketDashboard.View', 1, 4);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1281, 'TicketsBusinessHours.All', 1, 4);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1280, 'TicketsBusinessHours.View', 1, 4);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1279, 'TicketsBusinessHours.Delete', 1, 4);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1278, 'TicketsBusinessHours.Edit', 1, 4);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1277, 'TicketsBusinessHours.Add', 1, 4);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1276, 'TicketsSla.All', 1, 4);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1275, 'TicketsSla.View', 1, 4);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1274, 'TicketsSla.Delete', 1, 4);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1273, 'TicketsSla.Edit', 1, 4);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1271, 'TicketsSla.Add', 1, 4);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1270, 'TicketDashboardTimeLineWidgets.View', 1, 4);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1269, 'TicketDashboardSummaryWidgets.View', 1, 4);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1268, 'RecurringProfile.All', 1, 0);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1267, 'RecurringProfile.View', 1, 0);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1266, 'RecurringProfile.Delete', 1, 0);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1265, 'RecurringProfile.Edit', 1, 0);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1264, 'RecurringProfile.Add', 1, 0);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1263, 'Tickets.View.RestrictedAccess', 1, 4);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1262, 'Tickets.View.GroupAccess', 1, 4);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1261, 'Tickets.View.GlobalAccess', 1, 4);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1260, 'TicketsGroups.Delete', 1, 4);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1259, 'TicketsGroups.Edit', 1, 4);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1258, 'TicketsGroups.Add', 1, 4);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1257, 'TicketsGroups.View', 1, 4);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1256, 'TicketsGroups.All', 1, 4);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1255, 'TicketsFields.Edit', 1, 4);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1254, 'Tickets.Delete', 1, 4);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1253, 'Tickets.Edit', 1, 4);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1252, 'Tickets.Add', 1, 4);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1251, 'Tickets.View', 1, 4);
INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1250, 'Tickets.All', 1, 4);
-- ############################################
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Tickets.index', 'TicketsController.index', 1, 1251);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Tickets.*', 'TicketsController.*', 1, 1250);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Tickets.ajax_datagrid_groups', 'TicketsController.ajax_datagrid_groups', 1, 1251);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsGroup.Activate_support_email', 'TicketsGroupController.Activate_support_email', 1, 1257);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('RecurringInvoice.index', 'RecurringInvoiceController.index', 1, 1267);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('RecurringInvoice.create', 'RecurringInvoiceController.create', 1, 1264);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('RecurringInvoice.store', 'RecurringInvoiceController.store', 1, 1264);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('RecurringInvoice.edit', 'RecurringInvoiceController.edit', 1, 1265);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('RecurringInvoice.delete', 'RecurringInvoiceController.delete', 1, 1266);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('RecurringInvoice.ajax_datagrid', 'RecurringInvoiceController.ajax_datagrid', 1, 1267);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('RecurringInvoice.calculate_total', 'RecurringInvoiceController.calculate_total', 1, 1267);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('RecurringInvoice.getAccountInfo', 'RecurringInvoiceController.getAccountInfo', 1, 1267);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('RecurringInvoice.getBillingClassInfo', 'RecurringInvoiceController.getBillingClassInfo', 1, 1267);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('RecurringInvoice.recurringinvoicelog', 'RecurringInvoiceController.recurringinvoicelog', 1, 1267);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('RecurringInvoice.ajax_recurringinvoicelog_datagrid', 'RecurringInvoiceController.ajax_recurringinvoicelog_datagrid', 1, 1267);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('RecurringInvoice.startstop', 'RecurringInvoiceController.startstop', 1, 1267);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('RecurringInvoice.generate', 'RecurringInvoiceController.generate', 1, 1267);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsGroup.index', 'TicketsGroupController.index', 1, 1257);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsGroup.*', 'TicketsGroupController.*', 1, 1256);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsGroup.add', 'TicketsGroupController.add', 1, 1258);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsGroup.Store', 'TicketsGroupController.Store', 1, 1258);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsGroup.ajax_datagrid', 'TicketsGroupController.ajax_datagrid', 1, 1257);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsGroup.Edit', 'TicketsGroupController.Edit', 1, 1259);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsGroup.Update', 'TicketsGroupController.Update', 1, 1259);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsGroup.delete', 'TicketsGroupController.delete', 1, 1260);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsGroup.send_activation_single', 'TicketsGroupController.send_activation_single', 1, 1257);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsGroup.get_group_agents', 'TicketsGroupController.get_group_agents', 1, 1251);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsFields.index', 'TicketsFieldsController.index', 1, 1255);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsFields.*', 'TicketsFieldsController.*', 1, 1255);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsFields.iframe', 'TicketsFieldsController.iframe', 1, 1255);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsFields.iframeSubmit', 'TicketsFieldsController.iframeSubmit', 1, 1255);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsFields.ajax_ticketsfields', 'TicketsFieldsController.ajax_ticketsfields', 1, 1255);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsFields.Ajax_Ticketsfields_Choices', 'TicketsFieldsController.Ajax_Ticketsfields_Choices', 1, 1255);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsFields.Save_Single_Field', 'TicketsFieldsController.Save_Single_Field', 1, 1255);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsFields.Update_Fields_Sorting', 'TicketsFieldsController.Update_Fields_Sorting', 1, 1255);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Tickets.ajex_result', 'TicketsController.ajex_result', 1, 1251);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Tickets.add', 'TicketsController.add', 1, 1252);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Tickets.uploadFile', 'TicketsController.uploadFile', 1, 1252);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Tickets.deleteUploadFile', 'TicketsController.deleteUploadFile', 1, 1253);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Tickets.Store', 'TicketsController.Store', 1, 1252);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Tickets.edit', 'TicketsController.edit', 1, 1253);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Tickets.Update', 'TicketsController.Update', 1, 1253);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Tickets.UpdateDetailPage', 'TicketsController.UpdateDetailPage', 1, 1253);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Tickets.delete', 'TicketsController.delete', 1, 1254);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Tickets.Detail', 'TicketsController.Detail', 1, 1251);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Tickets.TicketAction', 'TicketsController.TicketAction', 1, 1253);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Tickets.UpdateTicketAttributes', 'TicketsController.UpdateTicketAttributes', 1, 1253);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Tickets.ActionSubmit', 'TicketsController.ActionSubmit', 1, 1253);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Tickets.getConversationAttachment', 'TicketsController.getConversationAttachment', 1, 1251);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Tickets.GetTicketAttachment', 'TicketsController.GetTicketAttachment', 1, 1251);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Tickets.CloseTicket', 'TicketsController.CloseTicket', 1, 1253);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Tickets.ComposeEmail', 'TicketsController.ComposeEmail', 1, 1252);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Tickets.SendMail', 'TicketsController.SendMail', 1, 1252);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Tickets.add_note', 'TicketsController.add_note', 1, 1253);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsGroup.validatesmtp', 'TicketsGroupController.validatesmtp', 1, 1257);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Dashboard.TicketDashboard', 'DashboardController.TicketDashboard', 1, 1251);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsSla.index', 'TicketsSlaController.index', 1, 1275);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsSla.*', 'TicketsSlaController.*', 1, 1276);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsSla.edit', 'TicketsSlaController.edit', 1, 1273);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsSla.ajax_datagrid', 'TicketsSlaController.ajax_datagrid', 1, 1275);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsSla.add', 'TicketsSlaController.add', 1, 1271);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsSla.store', 'TicketsSlaController.store', 1, 1271);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsSla.delete', 'TicketsSlaController.delete', 1, 1274);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsSla.update', 'TicketsSlaController.update', 1, 1273);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Tickets.ajex_result_export', 'TicketsController.ajex_result_export', 1, 1251);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Tickets.UpdateTicketDueTime', 'TicketsController.UpdateTicketDueTime', 1, 1253);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsBusinessHours.index', 'TicketsBusinessHoursController.index', 1, 1280);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsBusinessHours.*', 'TicketsBusinessHoursController.*', 1, 1281);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsBusinessHours.ajax_datagrid', 'TicketsBusinessHoursController.ajax_datagrid', 1, 1280);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsBusinessHours.create', 'TicketsBusinessHoursController.create', 1, 1277);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsBusinessHours.store', 'TicketsBusinessHoursController.store', 1, 1277);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsBusinessHours.delete', 'TicketsBusinessHoursController.delete', 1, 1279);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsBusinessHours.edit', 'TicketsBusinessHoursController.edit', 1, 1278);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketsBusinessHours.update', 'TicketsBusinessHoursController.update', 1, 1278);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Tickets.BulkAction', 'TicketsController.BulkAction', 1, 1253);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Tickets.BulkDelete', 'TicketsController.BulkDelete', 1, 1254);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketDashboard.ticketSummaryWidget', 'TicketDashboardController.ticketSummaryWidget', 1, 1269);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('TicketDashboard.ticketTimeLineWidget', 'TicketDashboardController.ticketTimeLineWidget', 1, 1270);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Services.index', 'ServicesController.index', 1, 1290);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Services.*', 'ServicesController.*', 1, 1289);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Services.ajax_datagrid', 'ServicesController.ajax_datagrid', 1, 1290);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Services.store', 'ServicesController.store', 1, 1293);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Services.update', 'ServicesController.update', 1, 1291);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Services.delete', 'ServicesController.delete', 1, 1292);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('Services.exports', 'ServicesController.exports', 1, 1290);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('AccountService.addservices', 'AccountServiceController.addservices', 1, 1294);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('AccountService.edit', 'AccountServiceController.edit', 1, 1295);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('AccountService.ajax_datagrid', 'AccountServiceController.ajax_datagrid', 1, 1297);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('AccountService.update', 'AccountServiceController.update', 1, 1295);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('AccountService.changestatus', 'AccountServiceController.changestatus', 1, 1295);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('AccountService.delete', 'AccountServiceController.delete', 1, 1296);
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CategoryID`) VALUES ('AccountService.*', 'AccountServiceController.*', 1, 1298);
-- ###########################################################
INSERT INTO `tblEmailTemplate` (`CompanyID`, `TemplateName`, `Subject`, `TemplateBody`, `created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`, `userID`, `Type`, `EmailFrom`, `StaticType`, `SystemType`, `Status`, `StatusDisabled`, `TicketTemplate`) VALUES (1, 'Agent Response SlaVoilation', 'Response time SLA violated - [#{{TicketID}}] {{Subject}}', '<p style="margin-right: 0px; margin-bottom: 0px; margin-left: 0px; padding: 0px; outline: 0px; font-size: 13px; font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; vertical-align: baseline; border: 0px; line-height: 1.3; word-break: normal; word-wrap: break-word; color: rgb(51, 51, 51);">Hi,<br><br>There has been no response from the helpdesk for Ticket ID #{{TicketID}}.<br><br>Ticket Details:&nbsp;<br><br>Subject - {{Subject}}<br><br>Requestor - {{Requester}}<br><br>Regards,<br>{{CompanyName}}<br></p><p></p>', '2017-01-25 15:35:02', NULL, '2017-03-30 12:40:55', NULL, NULL, 4, '', 1, 'AgentResponseSlaVoilation', 1, 0, 1);
INSERT INTO `tblEmailTemplate` (`CompanyID`, `TemplateName`, `Subject`, `TemplateBody`, `created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`, `userID`, `Type`, `EmailFrom`, `StaticType`, `SystemType`, `Status`, `StatusDisabled`, `TicketTemplate`) VALUES (1, 'Agent Resolve SlaVoilation', 'Resolve time SLA violated - [#{{TicketID}}] {{Subject}}', '<p style="margin-right: 0px; margin-bottom: 0px; margin-left: 0px; padding: 0px; outline: 0px; font-size: 13px; font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; vertical-align: baseline; border: 0px; line-height: 1.3; word-break: normal; word-wrap: break-word; color: rgb(51, 51, 51);">Hi,<br><br>There has been no response from the helpdesk for Ticket ID #{{TicketID}}.<br><br>Ticket Details:&nbsp;<br><br>Subject - {{Subject}}<br><br>Requestor - {{Requester}}<br><br>Regards,<br>{{CompanyName}}&nbsp;&nbsp;&nbsp;&nbsp;<br></p><p></p>', '2017-01-25 15:35:02', NULL, '2017-03-30 12:40:55', NULL, NULL, 4, '', 1, 'AgentResolveSlaVoilation', 1, 0, 1);