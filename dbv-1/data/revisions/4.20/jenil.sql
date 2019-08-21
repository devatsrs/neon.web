USE Ratemanagement3;

/* Lock wait timeout - 1wordtec and other env */

DROP PROCEDURE IF EXISTS `prc_UpdateMysqlPID`;
DELIMITER //
CREATE PROCEDURE `prc_UpdateMysqlPID`(
	IN `p_processId` VARCHAR(200)
)
BEGIN
	DECLARE MysqlPID VARCHAR(200);
--	  SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
		
		SELECT CONNECTION_ID() into MysqlPID;
		
		UPDATE tblCronJob
			SET MysqlPID=MysqlPID
		WHERE ProcessID=p_processId;
		
		COMMIT;

--	  SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

/* END- Lock wait timeout - 1wordtec and other env */



	
/* Tickets Changes */
		
DROP PROCEDURE prc_GetSystemTicket;
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
	IN `p_isExport` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_is_StartDate` INT,
	IN `p_is_EndDate` INT,
	IN `p_Requester` VARCHAR(255),
	IN `p_LastReply` INT

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
			AND (p_Search = '' OR ( p_Search != '' AND (T.Subject like Concat('%',p_Search,'%') OR  T.Description like Concat('%',p_Search,'%') OR  T.Requester like Concat('%',p_Search,'%') OR  T.RequesterName like Concat('%',p_Search,'%') ) OR (T.TicketID in  ( select ael.TicketID from AccountEmailLog ael where  ael.CompanyID = p_CompanyID AND (ael.Subject like Concat('%',p_Search,'%') OR  ael.Emailfrom like Concat('%',p_Search,'%') OR  ael.EmailfromName like Concat('%',p_Search,'%') OR  ael.Message like Concat('%',p_Search,'%') )   ) ) ) )
			AND (P_Status = '' OR find_in_set(T.`Status`,P_Status))
			AND (P_Priority = '' OR find_in_set(T.`Priority`,P_Priority))
			AND (P_Group = '' OR find_in_set(T.`Group`,P_Group))
			AND (P_Agent = '' OR find_in_set(T.`Agent`,P_Agent))
			AND (p_Requester = '' OR find_in_set(T.`Requester`,p_Requester))
			AND (p_is_StartDate = 0 OR ( p_is_StartDate != 0 AND DATE(T.created_at) >= p_StartDate))
			AND (p_is_EndDate = 0 OR ( p_is_EndDate != 0 AND DATE(T.created_at) <= p_EndDate))
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
				
			AND 
			(
				p_LastReply = 0 OR
				
				CASE 
					WHEN T.AgentRepliedDate is not null AND T.CustomerRepliedDate is not null				   
				THEN
				
					CASE 
						WHEN p_LastReply = 7 THEN
							DATE(T.AgentRepliedDate) <=  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.AgentRepliedDate > T.CustomerRepliedDate THEN 
							DATE(T.AgentRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.AgentRepliedDate < T.CustomerRepliedDate THEN 
							DATE(T.CustomerRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
					END
				
			 WHEN T.AgentRepliedDate is null OR T.CustomerRepliedDate is null					   
			  THEN	
					CASE 
						WHEN p_LastReply = 7 THEN
							DATE(T.AgentRepliedDate) <=  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.CustomerRepliedDate is null AND (T.AgentRepliedDate is not null) THEN 
							DATE(T.AgentRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.AgentRepliedDate is null AND (T.CustomerRepliedDate is not null) THEN  
							DATE(T.CustomerRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
					END	
			
				END 
				
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
			AND (p_Search = '' OR ( p_Search != '' AND (T.Subject like Concat('%',p_Search,'%') OR  T.Description like Concat('%',p_Search,'%') OR  T.Requester like Concat('%',p_Search,'%') OR  T.RequesterName like Concat('%',p_Search,'%') ) OR (T.TicketID in  ( select ael.TicketID from AccountEmailLog ael where  ael.CompanyID = p_CompanyID AND (ael.Subject like Concat('%',p_Search,'%') OR  ael.Emailfrom like Concat('%',p_Search,'%') OR  ael.EmailfromName like Concat('%',p_Search,'%') OR  ael.Message like Concat('%',p_Search,'%') )   ) ) ) )
			AND (P_Status = '' OR find_in_set(T.`Status`,P_Status))
			AND (P_Priority = '' OR find_in_set(T.`Priority`,P_Priority))
			AND (P_Group = '' OR find_in_set(T.`Group`,P_Group))
			AND (P_Agent = '' OR find_in_set(T.`Agent`,P_Agent))
			AND (p_Requester = '' OR find_in_set(T.`Requester`,p_Requester))
			AND (p_is_StartDate = 0 OR ( p_is_StartDate != 0 AND DATE(T.created_at) >= p_StartDate))
			AND (p_is_EndDate = 0 OR ( p_is_EndDate != 0 AND DATE(T.created_at) <= p_EndDate))
			AND ((P_DueBy = ''
			OR (find_in_set('Today',P_DueBy) AND DATE(T.DueDate) = DATE(P_CurrentDate)))
			OR (find_in_set('Tomorrow',P_DueBy) AND DATE(T.DueDate) =  DATE(DATE_ADD(P_CurrentDate, INTERVAL 1 Day)))
			OR (find_in_set('Next_8_hours',P_DueBy) AND T.DueDate BETWEEN P_CurrentDate AND DATE_ADD(P_CurrentDate, INTERVAL 8 Hour))
			OR (find_in_set('Overdue',P_DueBy) AND P_CurrentDate >=  T.DueDate))
			
			AND 
			(
				p_LastReply = 0 OR
				
				CASE 
					WHEN T.AgentRepliedDate is not null AND T.CustomerRepliedDate is not null				   
				THEN
				
					CASE 
						WHEN p_LastReply = 7 THEN
							DATE(T.AgentRepliedDate) <=  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.AgentRepliedDate > T.CustomerRepliedDate THEN 
							DATE(T.AgentRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.AgentRepliedDate < T.CustomerRepliedDate THEN 
							DATE(T.CustomerRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
					END
				
			 WHEN T.AgentRepliedDate is null OR T.CustomerRepliedDate is null					   
			  THEN	
					CASE 
						WHEN p_LastReply = 7 THEN
							DATE(T.AgentRepliedDate) <=  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.CustomerRepliedDate is null AND (T.AgentRepliedDate is not null) THEN 
							DATE(T.AgentRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.AgentRepliedDate is null AND (T.CustomerRepliedDate is not null) THEN  
							DATE(T.CustomerRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
					END	
			
				END 
				
			);

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
			AND (p_Search = '' OR ( p_Search != '' AND (T.Subject like Concat('%',p_Search,'%') OR  T.Description like Concat('%',p_Search,'%') OR  T.Requester like Concat('%',p_Search,'%') OR  T.RequesterName like Concat('%',p_Search,'%') ) OR (T.TicketID in  ( select ael.TicketID from AccountEmailLog ael where  ael.CompanyID = p_CompanyID AND (ael.Subject like Concat('%',p_Search,'%') OR  ael.Emailfrom like Concat('%',p_Search,'%') OR  ael.EmailfromName like Concat('%',p_Search,'%') OR  ael.Message like Concat('%',p_Search,'%') )   ) ) ) )
			AND (P_Status = '' OR find_in_set(T.`Status`,P_Status))
			AND (P_Priority = '' OR find_in_set(T.`Priority`,P_Priority))
			AND (P_Group = '' OR find_in_set(T.`Group`,P_Group))
			AND (P_Agent = '' OR find_in_set(T.`Agent`,P_Agent))
			AND (p_Requester = '' OR find_in_set(T.`Requester`,p_Requester))
			AND (p_is_StartDate = 0 OR ( p_is_StartDate != 0 AND DATE(T.created_at) >= p_StartDate))
			AND (p_is_EndDate = 0 OR ( p_is_EndDate != 0 AND DATE(T.created_at) <= p_EndDate))
			AND ((P_DueBy = ''
			OR (find_in_set('Today',P_DueBy) AND DATE(T.DueDate) = DATE(P_CurrentDate)))
			OR (find_in_set('Tomorrow',P_DueBy) AND DATE(T.DueDate) =  DATE(DATE_ADD(P_CurrentDate, INTERVAL 1 Day)))
			OR (find_in_set('Next_8_hours',P_DueBy) AND T.DueDate BETWEEN P_CurrentDate AND DATE_ADD(P_CurrentDate, INTERVAL 8 Hour))
			OR (find_in_set('Overdue',P_DueBy) AND P_CurrentDate >=  T.DueDate ) )
			
			AND 
			(
				p_LastReply = 0 OR
				
				CASE 
					WHEN T.AgentRepliedDate is not null AND T.CustomerRepliedDate is not null				   
				THEN
				
					CASE 
						WHEN p_LastReply = 7 THEN
							DATE(T.AgentRepliedDate) <=  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.AgentRepliedDate > T.CustomerRepliedDate THEN 
							DATE(T.AgentRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.AgentRepliedDate < T.CustomerRepliedDate THEN 
							DATE(T.CustomerRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
					END
				
			 WHEN T.AgentRepliedDate is null OR T.CustomerRepliedDate is null					   
			  THEN	
					CASE 
						WHEN p_LastReply = 7 THEN
							DATE(T.AgentRepliedDate) <=  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.CustomerRepliedDate is null AND (T.AgentRepliedDate is not null) THEN 
							DATE(T.AgentRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.AgentRepliedDate is null AND (T.CustomerRepliedDate is not null) THEN  
							DATE(T.CustomerRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
					END	
			
				END
			);

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
			AND (p_Search = '' OR ( p_Search != '' AND (T.Subject like Concat('%',p_Search,'%') OR  T.Description like Concat('%',p_Search,'%') OR  T.Requester like Concat('%',p_Search,'%') OR  T.RequesterName like Concat('%',p_Search,'%') ) OR (T.TicketID in  ( select ael.TicketID from AccountEmailLog ael where  ael.CompanyID = p_CompanyID AND (ael.Subject like Concat('%',p_Search,'%') OR  ael.Emailfrom like Concat('%',p_Search,'%') OR  ael.EmailfromName like Concat('%',p_Search,'%') OR  ael.Message like Concat('%',p_Search,'%') )   ) ) ) )
			AND (P_Status = '' OR find_in_set(T.`Status`,P_Status))
			AND (P_Priority = '' OR find_in_set(T.`Priority`,P_Priority))
			AND (P_Group = '' OR find_in_set(T.`Group`,P_Group))
			AND (P_Agent = '' OR find_in_set(T.`Agent`,P_Agent))
			AND (p_Requester = '' OR find_in_set(T.`Requester`,p_Requester))
			AND (p_is_StartDate = 0 OR ( p_is_StartDate != 0 AND DATE(T.created_at) >= p_StartDate))
			AND (p_is_EndDate = 0 OR ( p_is_EndDate != 0 AND DATE(T.created_at) <= p_EndDate))
			AND ((P_DueBy = ''
			OR (find_in_set('Today',P_DueBy) AND DATE(T.DueDate) = DATE(P_CurrentDate)))
			OR (find_in_set('Tomorrow',P_DueBy) AND DATE(T.DueDate) =  DATE(DATE_ADD(P_CurrentDate, INTERVAL 1 Day)))
			OR (find_in_set('Next_8_hours',P_DueBy) AND T.DueDate BETWEEN P_CurrentDate AND DATE_ADD(P_CurrentDate, INTERVAL 8 Hour))
			OR (find_in_set('Overdue',P_DueBy) AND P_CurrentDate >=  T.DueDate ))
			
			AND 
			(
				p_LastReply = 0 OR
				
				CASE 
					WHEN T.AgentRepliedDate is not null AND T.CustomerRepliedDate is not null				   
				THEN
				
					CASE 
						WHEN p_LastReply = 7 THEN
							DATE(T.AgentRepliedDate) <=  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.AgentRepliedDate > T.CustomerRepliedDate THEN 
							DATE(T.AgentRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.AgentRepliedDate < T.CustomerRepliedDate THEN 
							DATE(T.CustomerRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
					END
				
			 WHEN T.AgentRepliedDate is null OR T.CustomerRepliedDate is null					   
			  THEN	
					CASE 
						WHEN p_LastReply = 7 THEN
							DATE(T.AgentRepliedDate) <=  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.CustomerRepliedDate is null AND (T.AgentRepliedDate is not null) THEN 
							DATE(T.AgentRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
						WHEN T.AgentRepliedDate is null AND (T.CustomerRepliedDate is not null) THEN  
							DATE(T.CustomerRepliedDate) =  DATE_ADD(CURDATE(), INTERVAL CONCAT("-",p_LastReply) DAY)
					END	
			
				END	
			);
	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




/* ----------- Billing --------- */

USE NeonBillingDev;

DROP PROCEDURE IF EXISTS `prc_getInvoiceUsage`;
DELIMITER //
CREATE PROCEDURE `prc_getInvoiceUsage`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_ServiceID` INT,
	IN `p_GatewayID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_ShowZeroCall` INT

)
BEGIN

	DECLARE v_InvoiceCount_ INT;
	DECLARE v_BillingTime_ INT;
	DECLARE v_CDRType_ INT;
	DECLARE v_AvgRound_ INT;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetBillingTime(p_GatewayID,p_AccountID) INTO v_BillingTime_;

	CALL fnServiceUsageDetail(p_CompanyID,p_AccountID,p_GatewayID,p_ServiceID,p_StartDate,p_EndDate,v_BillingTime_);

	SELECT
		it.CDRType,b.RoundChargesCDR  INTO v_CDRType_, v_AvgRound_
	FROM NeonRMDev.tblAccountBilling ab
	INNER JOIN  NeonRMDev.tblBillingClass b
		ON b.BillingClassID = ab.BillingClassID
	INNER JOIN tblInvoiceTemplate it
		ON it.InvoiceTemplateID = b.InvoiceTemplateID
	WHERE ab.AccountID = p_AccountID
		AND ab.ServiceID = p_ServiceID
	LIMIT 1;

	IF( v_CDRType_ = 2)
	THEN

		DROP TEMPORARY TABLE IF EXISTS tmp_tblSummaryUsageDetails_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblSummaryUsageDetails_(
			AccountID int,
			area_prefix varchar(50),
			trunk varchar(50),
			UsageDetailID int,
			duration int,
			billed_duration int,
			cost decimal(18,6),
			ServiceID INT,
			AvgRate decimal(18,6)
		);

		INSERT INTO tmp_tblSummaryUsageDetails_
		SELECT
			AccountID,
			area_prefix,
			trunk,
			UsageDetailID,
			duration,
			billed_duration,
			cost,
			ServiceID,
			ROUND((uh.cost/uh.billed_duration)*60.0,v_AvgRound_) as AvgRate
		FROM tmp_tblUsageDetails_ uh;

		SELECT
					area_prefix AS AreaPrefix,
					Trunk,
					(SELECT
					Country
					FROM NeonRMDev.tblRate r
					INNER JOIN NeonRMDev.tblCountry c
					ON c.CountryID = r.CountryID
					WHERE  r.Code = ud.area_prefix limit 1)
					AS Country,
					(SELECT Description
					FROM NeonRMDev.tblRate r
					WHERE  r.Code = ud.area_prefix limit 1 )
					AS Description,
					COUNT(UsageDetailID) AS NoOfCalls,
					CONCAT( FLOOR(SUM(duration ) / 60), ':' , SUM(duration ) % 60) AS Duration,
					CONCAT( FLOOR(SUM(billed_duration ) / 60),':' , SUM(billed_duration ) % 60) AS BillDuration,
					SUM(cost) AS ChargedAmount,
					SUM(duration ) as DurationInSec,
					SUM(billed_duration ) as BillDurationInSec,
					ud.ServiceID,
					ud.AvgRate as AvgRatePerMin
				FROM tmp_tblSummaryUsageDetails_ ud
				GROUP BY ud.area_prefix,ud.Trunk,ud.AccountID,ud.ServiceID,ud.AvgRate;

	ELSE

		SELECT
			trunk AS Trunk,
			area_prefix AS Prefix,
			CONCAT("'",cli) AS CLI,
			CONCAT("'",cld) AS CLD,
			(SELECT
			Country
			FROM NeonRMDev.tblRate r
			INNER JOIN NeonRMDev.tblCountry c
			ON c.CountryID = r.CountryID
			WHERE  r.Code = ud.area_prefix limit 1)
			AS Country,
			(SELECT
			Description
			FROM NeonRMDev.tblRate r
			INNER JOIN NeonRMDev.tblCountry c
			ON c.CountryID = r.CountryID
			WHERE  r.Code = ud.area_prefix limit 1)
			AS Description,
			CASE
			WHEN is_inbound=1 then 'Incoming'
				ELSE 'Outgoing'
			END
			as CallType,
			connect_time AS ConnectTime,
			disconnect_time AS DisconnectTime,
			billed_duration AS BillDuration,
			SEC_TO_TIME(billed_duration) AS BillDurationMinutes,
			cost AS ChargedAmount,
			ServiceID
		FROM tmp_tblUsageDetails_ ud
		WHERE ((p_ShowZeroCall =0 AND ud.cost >0 ) OR (p_ShowZeroCall =1 AND ud.cost >= 0))
		ORDER BY connect_time ASC;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


/*New Add RatePrefix in Account- Bulk Ratesheet Email - VOS Change*/
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES (1, 'VOS_RATEPREFIX_RATESHEET', '0');

DROP PROCEDURE IF EXISTS `prc_WSGenerateRateSheet`;
DELIMITER //
CREATE PROCEDURE `prc_WSGenerateRateSheet`(
	IN `p_CustomerID` INT,
	IN `p_Trunk` VARCHAR(100),
	IN `p_TimezonesID` INT,
	IN `p_RatePrefix` VARCHAR(255)
)
BEGIN
		DECLARE v_trunkDescription_ VARCHAR(50);
		DECLARE v_lastRateSheetID_ INT ;
		DECLARE v_IncludePrefix_ TINYINT;
		DECLARE v_Prefix_ VARCHAR(50);
		DECLARE v_codedeckid_  INT;
		DECLARE v_ratetableid_ INT;
		DECLARE v_RateTableAssignDate_  DATETIME;
		DECLARE v_NewA2ZAssign_ INT;

		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


		SELECT trunk INTO v_trunkDescription_
		FROM   tblTrunk
		WHERE  TrunkID = p_Trunk;

		DROP TEMPORARY TABLE IF EXISTS tmp_RateSheetRate_;
		CREATE TEMPORARY TABLE tmp_RateSheetRate_(
			RateID        INT,
			Destination   VARCHAR(200),
			Codes         VARCHAR(50),
			Interval1     INT,
			IntervalN     INT,
			Rate          DECIMAL(18, 6),
			`level`         VARCHAR(50),
			`change`        VARCHAR(50),
			EffectiveDate  DATE,
			EndDate  DATE,
			INDEX tmp_RateSheetRate_RateID (`RateID`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRates_;
		CREATE TEMPORARY TABLE tmp_CustomerRates_ (
			RateID INT,
			Interval1 INT,
			IntervalN  INT,
			Rate DECIMAL(18, 6),
			EffectiveDate DATE,
			EndDate DATE,
			LastModifiedDate DATETIME,
			INDEX tmp_CustomerRates_RateId (`RateID`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
		CREATE TEMPORARY TABLE tmp_RateTableRate_ (
			RateID INT,
			Interval1 INT,
			IntervalN  INT,
			Rate DECIMAL(18, 6),
			EffectiveDate DATE,
			EndDate DATE,
			updated_at DATETIME,
			INDEX tmp_RateTableRate_RateId (`RateID`)
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_RateSheetDetail_;
		CREATE TEMPORARY TABLE tmp_RateSheetDetail_ (
			ratesheetdetailsid int,
			RateID int ,
			RateSheetID int,
			Destination varchar(200),
			Code varchar(50),
			Rate DECIMAL(18, 6),
			`change` varchar(50),
			EffectiveDate Date,
			EndDate DATE,
			INDEX tmp_RateSheetDetail_RateId (`RateID`,`RateSheetID`)
		);
		SELECT RateSheetID INTO v_lastRateSheetID_
		FROM   tblRateSheet
		WHERE  CustomerID = p_CustomerID
					 AND level = v_trunkDescription_
					 AND TimezonesID = p_TimezonesID
		ORDER  BY RateSheetID DESC LIMIT 1 ;

		SELECT includeprefix INTO v_IncludePrefix_
		FROM   tblCustomerTrunk
		WHERE  AccountID = p_CustomerID
					 AND TrunkID = p_Trunk;

		SELECT prefix INTO v_Prefix_
		FROM   tblCustomerTrunk
		WHERE  AccountID = p_CustomerID
					 AND TrunkID = p_Trunk;


		SELECT
			CodeDeckId,
			RateTableID,
			RateTableAssignDate
		INTO v_codedeckid_, v_ratetableid_, v_RateTableAssignDate_
		FROM tblCustomerTrunk
		WHERE tblCustomerTrunk.TrunkID = p_Trunk
					AND tblCustomerTrunk.AccountID = p_CustomerID
					AND tblCustomerTrunk.Status = 1;

		INSERT INTO tmp_CustomerRates_
			SELECT  tblCustomerRate.RateID,
				tblCustomerRate.Interval1,
				tblCustomerRate.IntervalN,
				tblCustomerRate.Rate,
				effectivedate,
				tblCustomerRate.EndDate,
				lastmodifieddate
			FROM   tblAccount
				JOIN tblCustomerRate
					ON tblAccount.AccountID = tblCustomerRate.CustomerID
				JOIN tblRate
					ON tblRate.RateId = tblCustomerRate.RateId
						 AND tblRate.CodeDeckId = v_codedeckid_
			WHERE  tblAccount.AccountID = p_CustomerID
						 AND tblCustomerRate.TrunkID = p_Trunk
						 AND tblCustomerRate.TimezonesID = p_TimezonesID
						 AND (p_RatePrefix='' OR FIND_IN_SET(tblCustomerRate.RatePrefix,p_RatePrefix))
			ORDER BY tblCustomerRate.CustomerID,tblCustomerRate.TrunkID,tblCustomerRate.TimezonesID,tblCustomerRate.RateID,tblCustomerRate.EffectiveDate DESC;

		DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRates4_;
		DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRates2_;

		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_CustomerRates4_ as (select * from tmp_CustomerRates_);
		DELETE n1 FROM tmp_CustomerRates_ n1, tmp_CustomerRates4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
																																			 AND  n1.RateId = n2.RateId;

		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_CustomerRates2_ as (select * from tmp_CustomerRates_);

		INSERT INTO tmp_RateTableRate_
			SELECT
				tblRateTableRate.RateID,
				tblRateTableRate.Interval1,
				tblRateTableRate.IntervalN,
				tblRateTableRate.Rate,
				tblRateTableRate.EffectiveDate,
				tblRateTableRate.EndDate,
				tblRateTableRate.updated_at
			FROM tblAccount
				JOIN tblCustomerTrunk
					ON tblCustomerTrunk.AccountID = tblAccount.AccountID
				JOIN tblRateTable
					ON tblCustomerTrunk.ratetableid = tblRateTable.ratetableid
				JOIN tblRateTableRate
					ON tblRateTableRate.ratetableid = tblRateTable.ratetableid
				LEFT JOIN tmp_CustomerRates_ trc1
					ON trc1.RateID = tblRateTableRate.RateID
			WHERE  tblAccount.AccountID = p_CustomerID
						 AND tblRateTableRate.TimezonesID = p_TimezonesID
						 AND tblCustomerTrunk.TrunkID = p_Trunk
						 AND (( tblRateTableRate.EffectiveDate <= Now()
										AND ( ( trc1.RateID IS NULL )
													OR ( trc1.RateID IS NOT NULL
															 AND tblRateTableRate.ratetablerateid IS NULL )
										) )
									OR ( tblRateTableRate.EffectiveDate > Now()
											 AND ( ( trc1.RateID IS NULL )
														 OR ( trc1.RateID IS NOT NULL
																	AND tblRateTableRate.EffectiveDate < (SELECT
																																					IFNULL(MIN(crr.EffectiveDate),
																																								 tblRateTableRate.EffectiveDate)
																																				FROM   tmp_CustomerRates2_ crr
																																				WHERE  crr.RateID =
																																							 tblRateTableRate.RateID
			) ) ) ) )
			ORDER BY tblRateTableRate.RateID,tblRateTableRate.EffectiveDate desc;

		DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate4_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate4_ as (select * from tmp_RateTableRate_);
		DELETE n1 FROM tmp_RateTableRate_ n1, tmp_RateTableRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
																																			 AND  n1.RateId = n2.RateId;

		INSERt INTO tmp_RateSheetDetail_
			SELECT ratesheetdetailsid,
				RateID,
				RateSheetID,
				Destination,
				Code,
				Rate,
				`change`,
				effectivedate,
				EndDate
			FROM   tblRateSheetDetails
			WHERE  RateSheetID = v_lastRateSheetID_
			ORDER BY RateID,effectivedate desc;

		DROP TEMPORARY TABLE IF EXISTS tmp_RateSheetDetail4_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateSheetDetail4_ as (select * from tmp_RateSheetDetail_);
		DELETE n1 FROM tmp_RateSheetDetail_ n1, tmp_RateSheetDetail4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
																																					 AND  n1.RateId = n2.RateId;


		DROP TABLE IF EXISTS tmp_CloneRateSheetDetail_ ;
		CREATE TEMPORARY TABLE tmp_CloneRateSheetDetail_ LIKE tmp_RateSheetDetail_;
		INSERT tmp_CloneRateSheetDetail_ SELECT * FROM tmp_RateSheetDetail_;


		INSERT INTO tmp_RateSheetRate_
			SELECT tbl.RateID,
				description,
				Code,
				tbl.Interval1,
				tbl.IntervalN,
				tbl.Rate,
				v_trunkDescription_,
				'NEW' as `change`,
				tbl.EffectiveDate,
				tbl.EndDate
			FROM   (
							 SELECT
								 rt.RateID,
								 rt.Interval1,
								 rt.IntervalN,
								 rt.Rate,
								 rt.EffectiveDate,
								 rt.EndDate
							 FROM   tmp_RateTableRate_ rt
								 LEFT JOIN tblRateSheet
									 ON tblRateSheet.RateSheetID =
											v_lastRateSheetID_
								 LEFT JOIN tmp_RateSheetDetail_ as  rsd
									 ON rsd.RateID = rt.RateID AND rsd.RateSheetID = v_lastRateSheetID_
							 WHERE  rsd.ratesheetdetailsid IS NULL

							 UNION

							 SELECT
								 trc2.RateID,
								 trc2.Interval1,
								 trc2.IntervalN,
								 trc2.Rate,
								 trc2.EffectiveDate,
								 trc2.EndDate
							 FROM   tmp_CustomerRates_ trc2
								 LEFT JOIN tblRateSheet
									 ON tblRateSheet.RateSheetID =
											v_lastRateSheetID_
								 LEFT JOIN tmp_CloneRateSheetDetail_ as  rsd2
									 ON rsd2.RateID = trc2.RateID AND rsd2.RateSheetID = v_lastRateSheetID_
							 WHERE  rsd2.ratesheetdetailsid IS NULL

						 ) AS tbl
				INNER JOIN tblRate
					ON tbl.RateID = tblRate.RateID;

		INSERT INTO tmp_RateSheetRate_
			SELECT tbl.RateID,
				description,
				Code,
				tbl.Interval1,
				tbl.IntervalN,
				tbl.Rate,
				v_trunkDescription_,
				tbl.`change`,
				tbl.EffectiveDate,
				tbl.EndDate
			FROM   (SELECT rt.RateID,
								description,
								tblRate.Code,
								rt.Interval1,
								rt.IntervalN,
								rt.Rate,
								rsd5.Rate AS rate2,
								rt.EffectiveDate,
								rt.EndDate,
								CASE
								WHEN rsd5.Rate > rt.Rate
										 AND rsd5.Destination !=
												 description THEN
									'NAME CHANGE & DECREASE'
								ELSE
									CASE
									WHEN rsd5.Rate > rt.Rate
											 AND rt.EffectiveDate <= Now() THEN
										'DECREASE'
									ELSE
										CASE
										WHEN ( rsd5.Rate >
													 rt.Rate
													 AND rt.EffectiveDate > Now()
										)
											THEN
												'PENDING DECREASE'
										ELSE
											CASE
											WHEN ( rsd5.Rate <
														 rt.Rate
														 AND rt.EffectiveDate <=
																 Now() )
												THEN
													'INCREASE'
											ELSE
												CASE
												WHEN ( rsd5.Rate
															 <
															 rt.Rate
															 AND rt.EffectiveDate >
																	 Now() )
													THEN
														'PENDING INCREASE'
												ELSE
													CASE
													WHEN
														rsd5.Destination !=
														description THEN
														'NAME CHANGE'
													ELSE 'NO CHANGE'
													END
												END
											END
										END
									END
								END as `Change`
							FROM   tblRate
								INNER JOIN tmp_RateTableRate_ rt
									ON rt.RateID = tblRate.RateID
								INNER JOIN tblRateSheet
									ON tblRateSheet.RateSheetID = v_lastRateSheetID_
								INNER JOIN tmp_RateSheetDetail_ as  rsd5
									ON rsd5.RateID = rt.RateID
										 AND rsd5.RateSheetID =
												 v_lastRateSheetID_
							UNION
							SELECT trc4.RateID,
								description,
								tblRate.Code,
								trc4.Interval1,
								trc4.IntervalN,
								trc4.Rate,
								rsd6.Rate AS rate2,
								trc4.EffectiveDate,
								trc4.EndDate,
								CASE
								WHEN rsd6.Rate > trc4.Rate
										 AND rsd6.Destination !=
												 description THEN
									'NAME CHANGE & DECREASE'
								ELSE
									CASE
									WHEN rsd6.Rate > trc4.Rate
											 AND trc4.EffectiveDate <= Now() THEN
										'DECREASE'
									ELSE
										CASE
										WHEN ( rsd6.Rate >
													 trc4.Rate
													 AND trc4.EffectiveDate > Now()
										)
											THEN
												'PENDING DECREASE'
										ELSE
											CASE
											WHEN ( rsd6.Rate <
														 trc4.Rate
														 AND trc4.EffectiveDate <=
																 Now() )
												THEN
													'INCREASE'
											ELSE
												CASE
												WHEN ( rsd6.Rate
															 <
															 trc4.Rate
															 AND trc4.EffectiveDate >
																	 Now() )
													THEN
														'PENDING INCREASE'
												ELSE
													CASE
													WHEN
														rsd6.Destination !=
														description THEN
														'NAME CHANGE'
													ELSE 'NO CHANGE'
													END
												END
											END
										END
									END
								END as  `Change`
							FROM   tblRate
								INNER JOIN tmp_CustomerRates_ trc4
									ON trc4.RateID = tblRate.RateID
								INNER JOIN tblRateSheet
									ON tblRateSheet.RateSheetID = v_lastRateSheetID_
								INNER JOIN tmp_CloneRateSheetDetail_ as rsd6
									ON rsd6.RateID = trc4.RateID
										 AND rsd6.RateSheetID =
												 v_lastRateSheetID_
						 ) AS tbl ;

		INSERT INTO tmp_RateSheetRate_
			SELECT tblRateSheetDetails.RateID,
				tblRateSheetDetails.Destination,
				tblRateSheetDetails.Code,
				tblRateSheetDetails.Interval1,
				tblRateSheetDetails.IntervalN,
				tblRateSheetDetails.Rate,
				v_trunkDescription_,
				'DELETE',
				tblRateSheetDetails.EffectiveDate,
				tblRateSheetDetails.EndDate
			FROM   tblRate
				INNER JOIN tblRateSheetDetails
					ON tblRate.RateID = tblRateSheetDetails.RateID
						 AND tblRateSheetDetails.RateSheetID = v_lastRateSheetID_
				LEFT JOIN (SELECT DISTINCT RateID,
										 effectivedate
									 FROM   tmp_RateTableRate_
									 UNION
									 SELECT DISTINCT RateID,
										 effectivedate
									 FROM tmp_CustomerRates_) AS TBL
					ON TBL.RateID = tblRateSheetDetails.RateID
			WHERE  `change` != 'DELETE'
						 AND TBL.RateID IS NULL ;

		DROP TEMPORARY TABLE IF EXISTS tmp_RateSheetRate4_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateSheetRate4_ as (select * from tmp_RateSheetRate_);
		DELETE n1 FROM tmp_RateSheetRate_ n1, tmp_RateSheetRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
																																			 AND  n1.RateId = n2.RateId;

		IF v_IncludePrefix_ = 1
		THEN
			SELECT rsr.RateID AS rateid,
						 rsr.Interval1 AS interval1,
						 rsr.IntervalN AS intervaln,
						 rsr.Destination AS destination,
						 rsr.Codes AS codes,
						 v_Prefix_ AS `tech prefix`,
						 CONCAT(rsr.Interval1,'/',rsr.IntervalN) AS `interval`,
						 FORMAT(rsr.Rate,6) AS `rate per minute (usd)`,
				rsr.`level`,
				rsr.`change`,
						 rsr.EffectiveDate AS `effective date`,
						 rsr.EndDate AS `end date`
			FROM   tmp_RateSheetRate_ rsr

			ORDER BY rsr.Destination,rsr.Codes desc;
		ELSE
			SELECT rsr.RateID AS rateid ,
						 rsr.Interval1 AS interval1,
						 rsr.IntervalN AS intervaln,
						 rsr.Destination AS destination,
						 rsr.Codes AS codes,
						 CONCAT(rsr.Interval1,'/',rsr.IntervalN) AS  `interval`,
						 FORMAT(rsr.Rate, 6) AS `rate per minute (usd)`,
				rsr.`level`,
				rsr.`change`,
						 rsr.EffectiveDate AS `effective date`,
						 rsr.EndDate AS `end date`
			FROM   tmp_RateSheetRate_ rsr

			ORDER BY rsr.Destination,rsr.Codes DESC;
		END IF;

		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	END//
DELIMITER ;



	