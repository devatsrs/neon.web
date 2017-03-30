USE `Ratemanagement3`;

-- #NEON-819##############################################################
DROP PROCEDURE IF EXISTS `prc_GetFromEmailAddress`;
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
-- ###############################################################
