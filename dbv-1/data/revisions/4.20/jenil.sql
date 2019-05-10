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




	