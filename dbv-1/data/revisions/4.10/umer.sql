USE `Ratemanagement3`;
-- ##############################################################
update tblEmailTemplate set EmailFrom = (select EmailFrom from tblCompany limit 1)
-- ##############################################################
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'WEB_PATH', '/var/www/html/rm.umer');
-- ##############################################################

/*
UPDATE `tblEmailTemplate` SET `TicketTemplate`='1' WHERE  SystemType = 'CCNoteaddedtoticket';
UPDATE `tblEmailTemplate` SET `TicketTemplate`='1' WHERE  SystemType = 'AgentEscalationRule';
UPDATE `tblEmailTemplate` SET `TicketTemplate`='1' WHERE  SystemType = 'AgentTicketReopened';
UPDATE `tblEmailTemplate` SET `TicketTemplate`='1' WHERE  SystemType = 'AgentAssignedGroup';
UPDATE `tblEmailTemplate` SET `TicketTemplate`='1' WHERE  SystemType = 'CCNewTicketCreated';
UPDATE `tblEmailTemplate` SET `TicketTemplate`='1' WHERE  SystemType = 'RequesterNewTicketCreated';
UPDATE `tblEmailTemplate` SET `TicketTemplate`='1' WHERE  SystemType = 'AgentClosestheTicket';
UPDATE `tblEmailTemplate` SET `TicketTemplate`='1' WHERE  SystemType = 'AgentSolvestheTicket';
UPDATE `tblEmailTemplate` SET `TicketTemplate`='1' WHERE  SystemType = 'Noteaddedtoticket';
UPDATE `tblEmailTemplate` SET `TicketTemplate`='1' WHERE  SystemType = 'RequesterRepliestoTicket';
UPDATE `tblEmailTemplate` SET `TicketTemplate`='1' WHERE  SystemType = 'TicketAssignedtoAgent';
UPDATE `tblEmailTemplate` SET `TicketTemplate`='1' WHERE  SystemType = 'AgentNewTicketCreated';
*/
-- ##############################################################
/*ALTER TABLE `AccountEmailLog`	ADD COLUMN `TicketID` INT(11) NOT NULL DEFAULT '0' AFTER `EmailCall`;*/
-- ##############################################################
/*ALTER TABLE `tblTickets`
	ADD COLUMN `RequesterCC` VARCHAR(300) NULL DEFAULT NULL AFTER `RequesterName`,
	ADD COLUMN `RequesterBCC` VARCHAR(300) NULL DEFAULT NULL AFTER `RequesterCC`,
	ADD COLUMN `AccountID` INT NOT NULL DEFAULT '0' AFTER `RequesterBCC`,
	ADD COLUMN `ContactID` INT NOT NULL DEFAULT '0' AFTER `AccountID`,
	ADD COLUMN `UserID` INT NOT NULL DEFAULT '0' AFTER `ContactID`;*/
-- ##############################################################	
/*DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetSystemTicket`(
	IN `p_CompanyID` int,
	IN `p_Search` VARCHAR(100),
	IN `P_Status` VARCHAR(100),
	IN `P_Priority` VARCHAR(100),
	IN `P_Group` VARCHAR(100),
	IN `P_Agent` VARCHAR(100),
	IN `p_EmailCall` INT,
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
			(select tc.Emailfrom from AccountEmailLog tc where 	tc.EmailParent= T.AccountEmailLogID and tc.EmailCall =p_EmailCall order by tc.AccountEmailLogID desc limit 1) as CustomerResponse,			
			(select TAC.AccountID from tblAccount TAC where 	TAC.Email = T.Requester or TAC.BillingEmail =T.Requester limit 1) as ACCOUNTID,
			T.`Read` as `Read`
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
			AND (P_Agent = '' OR find_in_set(T.`Agent`,P_Agent));

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
			AND (P_Agent = '' OR find_in_set(T.`Agent`,P_Agent));			
		
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
			AND (P_Agent = '' OR find_in_set(T.`Agent`,P_Agent));
	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;*/
-- ##############################################################
/*DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetSystemTicketCustomer`(
	IN `p_CompanyID` int,
	IN `p_Search` VARCHAR(100),
	IN `P_Status` VARCHAR(100),
	IN `P_Priority` VARCHAR(100),
	IN `P_Group` VARCHAR(100),
	IN `P_Agent` VARCHAR(100),
	IN `P_EmailAddresses` VARCHAR(200),
	IN `p_EmailCall` INT,
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
			(select tc.Emailfrom from AccountEmailLog tc where 	tc.EmailParent= T.AccountEmailLogID and tc.EmailCall =p_EmailCall order by tc.AccountEmailLogID desc limit 1) as CustomerResponse	
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
DELIMITER ;*/
-- ##############################################################
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetFromEmailAddress`(
	IN `p_CompanyID` int,
	IN `p_userID` int ,
	IN `p_Ticket` INT,
	IN `p_Admin` INT
)
BEGIN
	DECLARE V_Ticket_Permission int;
	DECLARE V_Ticket_Permission_level int;
	DECLARE V_User_Groups varchar(100);
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;	
	SELECT 0 INTO V_Ticket_Permission;
	SELECT 0 into V_Ticket_Permission_level;			
	IF p_Ticket = 1
	THEN	
		IF p_Admin > 0
		THEN
			SELECT 1 INTO V_Ticket_Permission;
		END IF;
		
		IF p_Admin < 1 
		THEN		
			SELECT 
				count(*) into V_Ticket_Permission
			FROM 
				tblUser u
			inner join 
				tblUserPermission up on u.UserID = up.UserID
			inner join 
				tblResourceCategories tc on up.resourceID = tc.ResourceCategoryID
			WHERE 
				tc.ResourceCategoryName = 'Tickets.View.GlobalAccess'  and u.UserID = p_userID;
		END IF;
		
		IF V_Ticket_Permission > 0 
		THEN
			SELECT 1 into V_Ticket_Permission_level;			
			IF p_Admin > 0
			THEN
				SELECT DISTINCT GroupEmailAddress as EmailFrom FROM tblTicketGroups where CompanyID = p_CompanyID and GroupEmailStatus = 1 and GroupEmailAddress IS NOT NULL
					UNION ALL			
				SELECT  tc.EmailFrom as EmailFrom FROM tblCompany tc where tc.EmailFrom IS NOT NULL AND tc.EmailFrom <> ''
					UNION ALL
				SELECT DISTINCT(tu.EmailAddress) as EmailFrom from tblUser tu where tu.Status=1;
			END IF;
			IF p_Admin < 1	
			THEN
				SELECT DISTINCT GroupEmailAddress as EmailFrom FROM tblTicketGroups where CompanyID = p_CompanyID and GroupEmailStatus = 1 and GroupEmailAddress IS NOT NULL
					UNION ALL	
				SELECT  tc.EmailFrom as EmailFrom FROM tblCompany tc where tc.EmailFrom IS NOT NULL AND tc.EmailFrom <> ''
					UNION ALL
				SELECT tu.EmailAddress as EmailFrom from tblUser tu where tu.UserID = p_userID and tu.Status=1;							
			END IF;
		END IF;
		
		IF V_Ticket_Permission_level = 0
			THEN
				SELECT 0 into V_Ticket_Permission;
				SELECT 
				/*distinct u.userid, up.AddRemove,tc.ResourceCategoryName as permname*/
				count(*) into V_Ticket_Permission
			FROM 
				tblUser u
			inner join 
				tblUserPermission up on u.UserID = up.UserID
			inner join 
				tblResourceCategories tc on up.resourceID = tc.ResourceCategoryID
			WHERE 
				tc.ResourceCategoryName = 'Tickets.View.GroupAccess'  and u.UserID = p_userID;
		END IF;

		IF V_Ticket_Permission > 0 and V_Ticket_Permission_level = 0 
		THEN
			SELECT 2 into V_Ticket_Permission_level;
			
			SELECT GROUP_CONCAT(GroupID SEPARATOR ',') into V_User_Groups FROM tblTicketGroupAgents TGA where TGA.UserID = p_userID;
			
			IF p_Admin > 0
			THEN
				SELECT DISTINCT GroupEmailAddress as EmailFrom FROM tblTicketGroups where CompanyID = p_CompanyID and GroupEmailStatus = 1 and GroupEmailAddress IS NOT NULL AND FIND_IN_SET(GroupID,V_User_Groups)
					UNION ALL			
				SELECT  tc.EmailFrom as EmailFrom FROM tblCompany tc where tc.EmailFrom IS NOT NULL AND tc.EmailFrom <> ''
					UNION ALL
				SELECT DISTINCT(tu.EmailAddress) as EmailFrom from tblUser tu where tu.Status=1;
			END IF;
			IF p_Admin < 1	
			THEN
				SELECT DISTINCT GroupEmailAddress as EmailFrom FROM tblTicketGroups where CompanyID = p_CompanyID and GroupEmailStatus = 1 and GroupEmailAddress IS NOT NULL AND FIND_IN_SET(GroupID,V_User_Groups)
					UNION ALL	
				SELECT  tc.EmailFrom as EmailFrom FROM tblCompany tc where tc.EmailFrom IS NOT NULL AND tc.EmailFrom <> ''
					UNION ALL
				SELECT tu.EmailAddress as EmailFrom from tblUser tu where tu.UserID = p_userID and tu.Status=1;							
			END IF;
			
		END IF;

		IF V_Ticket_Permission_level = 0 
		THEN
			SELECT 0 into V_Ticket_Permission;
			SELECT 3 into V_Ticket_Permission_level;
			IF p_Admin > 0
			THEN
				SELECT DISTINCT TG.GroupEmailAddress as EmailFrom FROM tblTicketGroups TG INNER JOIN tblTickets TT ON TT.Group = TG.GroupID where TG.CompanyID = p_CompanyID and GroupEmailStatus = 1 and TG.GroupEmailAddress IS NOT NULL AND TT.Agent = p_userID					
					UNION ALL			
				SELECT  tc.EmailFrom as EmailFrom FROM tblCompany tc where tc.EmailFrom IS NOT NULL AND tc.EmailFrom <> ''
					UNION ALL
				SELECT DISTINCT(tu.EmailAddress) as EmailFrom from tblUser tu where tu.Status=1;
			END IF;
			IF p_Admin < 1	
			THEN
				SELECT DISTINCT TG.GroupEmailAddress as EmailFrom FROM tblTicketGroups TG INNER JOIN tblTickets TT ON TT.Group = TG.GroupID where TG.CompanyID = p_CompanyID and GroupEmailStatus = 1 and TG.GroupEmailAddress IS NOT NULL AND TT.Agent = p_userID
					UNION ALL	
				SELECT  tc.EmailFrom as EmailFrom FROM tblCompany tc where tc.EmailFrom IS NOT NULL AND tc.EmailFrom <> ''
					UNION ALL
				SELECT tu.EmailAddress as EmailFrom from tblUser tu where tu.UserID = p_userID and tu.Status=1;							
			END IF;
			
		END IF;		
	END IF;
	
	IF p_Ticket = 0
	THEN
		IF p_Admin > 0
		THEN
			SELECT  tc.EmailFrom as EmailFrom FROM tblCompany tc where tc.EmailFrom IS NOT NULL AND tc.EmailFrom <> ''
				UNION ALL
			SELECT DISTINCT(tu.EmailAddress) as EmailFrom from tblUser tu where tu.Status=1;
		END IF;
		IF p_Admin < 1	
		THEN
			SELECT  tc.EmailFrom as EmailFrom FROM tblCompany tc where tc.EmailFrom IS NOT NULL AND tc.EmailFrom <> ''
				UNION ALL
			SELECT tu.EmailAddress as EmailFrom from tblUser tu where tu.UserID = p_userID and tu.Status=1;							
		END IF;
	END IF;
	/*SELECT V_Ticket_Permission_level;*/
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;
-- ##############################################################
/*code to update ssl according to port*/

/*company email from in email templates*/
/*

DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getTicketTimeline`(
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
	ael.TicketID = p_TicketID and
	ael.CompanyID = p_CompanyID 
	and not Exists (SELECT 1 FROM tblTickets t where EmailCall = 1 order by ael.AccountEmailLogID limit 1)	
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
	select * from tmp_ticket_timeline_  order by created_at desc;		
END//
DELIMITER ;*/
-- ##############################################################
