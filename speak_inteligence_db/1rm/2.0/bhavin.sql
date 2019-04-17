USE `speakintelligentRM`;

CREATE TABLE IF NOT EXISTS `tblOutPaymentLog` (
	`OutPaymentLogID` INT(11) NOT NULL AUTO_INCREMENT,
	`CompanyID` INT(11) NOT NULL,
	`AccountID` INT(11) NOT NULL,
	`VendorID` INT(11) NOT NULL,
	`CLI` VARCHAR(50) NOT NULL COLLATE 'utf8_unicode_ci',
	`Date` DATETIME NOT NULL,
	`Amount` DECIMAL(18,6) NOT NULL,
	`Status` TINYINT(4) NULL DEFAULT '0',
	`created_at` DATETIME NULL DEFAULT NULL,
	`updated_at` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`OutPaymentLogID`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;

ALTER TABLE `tblBillingClass`
	DROP COLUMN `ResellerOwner`,
	DROP COLUMN `ResellerID`;

ALTER TABLE `tblBillingClass`
	ADD COLUMN `IsGlobal` TINYINT NOT NULL DEFAULT '0' AFTER `ZeroBalanceWarningSettings`;
	
ALTER TABLE `tblBillingClass`
	ADD COLUMN `ParentBillingClassID` TINYINT NOT NULL DEFAULT '0' AFTER `IsGlobal`;

ALTER TABLE `tblEmailTemplate`
	ADD COLUMN `IsGlobal` TINYINT NOT NULL DEFAULT '0' AFTER `TicketTemplate`;	

DROP PROCEDURE IF EXISTS `prc_getBillingClass`;
DELIMITER //
CREATE PROCEDURE `prc_getBillingClass`(
	IN `p_CompanyID` INT,
	IN `p_Name` VARCHAR(50) ,
	IN `p_isReseller` INT,
	IN `p_is_IsGlobal` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
BEGIN
	DECLARE v_OffSet_ INT;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	DROP TEMPORARY TABLE IF EXISTS tmp_BillingClass_;
	CREATE TEMPORARY TABLE tmp_BillingClass_ (
		NAME VARCHAR(50),
		ResellerName VARCHAR(50),
		UpdatedBy VARCHAR(200),
		updated_at DATETIME,
		BillingClassID INT,
		Applied INT,
		IsGlobal INT,
		ParentBillingClassID INT
		);
	IF p_isReseller = 0
	THEN

		INSERT INTO tmp_BillingClass_
		SELECT
			NAME,
			(SELECT ResellerName FROM tblReseller WHERE ChildCompanyID = tblBillingClass.CompanyID) AS ResellerName,
			UpdatedBy,
			updated_at,
			BillingClassID,
			(SELECT COUNT(*) FROM tblAccountBilling a WHERE a.BillingClassID = tblBillingClass.BillingClassID) AS Applied,
			IsGlobal,
			ParentBillingClassID
		FROM tblBillingClass
		WHERE  
			(p_CompanyID = 0 OR CompanyID = p_CompanyID) AND (p_is_IsGlobal = 0 OR IsGlobal = p_is_IsGlobal);


	END IF;

	IF p_isReseller = 1
	THEN
		
		INSERT INTO tmp_BillingClass_
		SELECT
			b1.NAME AS Name,
			(SELECT ResellerName FROM tblReseller WHERE ChildCompanyID = b1.CompanyID) AS ResellerName,
			b1.UpdatedBy AS UpdatedBy,
			b1.updated_at AS updated_at,
			b1.BillingClassID AS BillingClassID,
			(SELECT COUNT(*) FROM tblAccountBilling a WHERE a.BillingClassID = b1.BillingClassID) AS Applied,
			b1.IsGlobal AS IsGlobal,
			b1.ParentBillingClassID AS ParentBillingClassID
		FROM
			tblBillingClass b1
		LEFT JOIN	
			tblBillingClass b2 ON b1.BillingClassID = b2.ParentBillingClassID AND b1.IsGlobal=1
		WHERE
			(b1.CompanyID=p_CompanyID OR b1.IsGlobal=1) AND
			b2.BillingClassID IS NULL;


	END IF;

	IF p_isExport = 0
	THEN
		SELECT
			NAME,
			IFNULL(ResellerName,'') as ResellerName,
			IsGlobal,
			UpdatedBy,
			updated_at,
			BillingClassID,
			Applied,
			IsGlobal
		FROM tmp_BillingClass_
		WHERE  
			(p_Name = '' OR NAME LIKE REPLACE(p_Name, '*', '%'))
		ORDER BY
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameDESC') THEN NAME
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameASC') THEN NAME
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'UpdatedByDESC') THEN UpdatedBy
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'UpdatedByASC') THEN UpdatedBy
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN updated_at
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN updated_at
			END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;

	SELECT
		COUNT(BillingClassID) AS totalcount
		FROM tmp_BillingClass_
		WHERE (p_Name = '' OR NAME LIKE REPLACE(p_Name, '*', '%'));
	END IF;

	IF p_isExport = 1
	THEN

		SELECT
			NAME,
			IFNULL(ResellerName,'') as ResellerName,
			UpdatedBy,
			updated_at
		FROM tmp_BillingClass_
		WHERE  (p_Name = '' OR NAME LIKE REPLACE(p_Name, '*', '%'));

	END IF;	


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_GetFromEmailAddress`;
DELIMITER //
CREATE PROCEDURE `prc_GetFromEmailAddress`(
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
				tc.ResourceCategoryName = 'Tickets.View.GlobalAccess'  and u.UserID = p_userID and CompanyID = p_CompanyID;
		END IF;

		IF V_Ticket_Permission > 0
		THEN
			SELECT 1 into V_Ticket_Permission_level;
			IF p_Admin > 0
			THEN
				SELECT DISTINCT GroupReplyAddress as EmailFrom FROM tblTicketGroups where CompanyID = p_CompanyID and GroupEmailStatus = 1 and GroupReplyAddress IS NOT NULL
					UNION ALL
				SELECT  tc.EmailFrom as EmailFrom FROM tblCompany tc where CompanyID = p_CompanyID and tc.EmailFrom IS NOT NULL AND tc.EmailFrom <> ''
					UNION ALL
				SELECT DISTINCT(tu.EmailAddress) as EmailFrom from tblUser tu where CompanyID = p_CompanyID and tu.Status=1;
			END IF;
			IF p_Admin < 1
			THEN
				SELECT DISTINCT GroupReplyAddress as EmailFrom FROM tblTicketGroups where CompanyID = p_CompanyID and GroupEmailStatus = 1 and GroupReplyAddress IS NOT NULL
					UNION ALL
				SELECT  tc.EmailFrom as EmailFrom FROM tblCompany tc where CompanyID = p_CompanyID and  tc.EmailFrom IS NOT NULL AND tc.EmailFrom <> ''
					UNION ALL
				SELECT tu.EmailAddress as EmailFrom from tblUser tu where CompanyID = p_CompanyID and  tu.UserID = p_userID and tu.Status=1
					UNION ALL
				SELECT tu.EmailAddress as EmailFrom from tblUser tu where CompanyID = p_CompanyID and  tu.Status=1  and AdminUser = 0 and tu.UserID not in ( select UserID from tblUserRole );
			END IF;
		END IF;

		IF V_Ticket_Permission_level = 0
			THEN
				SELECT 0 into V_Ticket_Permission;
				SELECT

				count(*) into V_Ticket_Permission
			FROM
				tblUser u
			inner join
				tblUserPermission up on u.UserID = up.UserID
			inner join
				tblResourceCategories tc on up.resourceID = tc.ResourceCategoryID
			WHERE
				tc.ResourceCategoryName = 'Tickets.View.GroupAccess'  and u.UserID = p_userID and CompanyID = p_CompanyID;
		END IF;

		IF V_Ticket_Permission > 0 and V_Ticket_Permission_level = 0
		THEN
			SELECT 2 into V_Ticket_Permission_level;

			SELECT GROUP_CONCAT(GroupID SEPARATOR ',') into V_User_Groups FROM tblTicketGroupAgents TGA where TGA.UserID = p_userID;

			IF p_Admin > 0
			THEN
				SELECT DISTINCT GroupReplyAddress as EmailFrom FROM tblTicketGroups where CompanyID = p_CompanyID and GroupEmailStatus = 1 and GroupReplyAddress IS NOT NULL AND FIND_IN_SET(GroupID,V_User_Groups)
					UNION ALL
				SELECT  tc.EmailFrom as EmailFrom FROM tblCompany tc where CompanyID = p_CompanyID and tc.EmailFrom IS NOT NULL AND tc.EmailFrom <> ''
					UNION ALL
				SELECT DISTINCT(tu.EmailAddress) as EmailFrom from tblUser tu where CompanyID = p_CompanyID and tu.Status=1;
			END IF;
			IF p_Admin < 1
			THEN
				SELECT DISTINCT GroupReplyAddress as EmailFrom FROM tblTicketGroups where CompanyID = p_CompanyID and GroupEmailStatus = 1 and GroupReplyAddress IS NOT NULL AND FIND_IN_SET(GroupID,V_User_Groups)
					UNION ALL
				SELECT  tc.EmailFrom as EmailFrom FROM tblCompany tc where CompanyID = p_CompanyID and tc.EmailFrom IS NOT NULL AND tc.EmailFrom <> ''
					UNION ALL
				SELECT tu.EmailAddress as EmailFrom from tblUser tu where CompanyID = p_CompanyID and tu.UserID = p_userID and tu.Status=1
					UNION ALL
				SELECT tu.EmailAddress as EmailFrom from tblUser tu where CompanyID = p_CompanyID and tu.Status=1  and AdminUser = 0 and tu.UserID not in ( select UserID from tblUserRole );
			END IF;

		END IF;

		IF V_Ticket_Permission_level = 0
		THEN
			SELECT 0 into V_Ticket_Permission;
			SELECT 3 into V_Ticket_Permission_level;
			IF p_Admin > 0
			THEN
				SELECT DISTINCT TG.GroupReplyAddress as EmailFrom FROM tblTicketGroups TG INNER JOIN tblTickets TT ON TT.Group = TG.GroupID where TG.CompanyID = p_CompanyID and GroupEmailStatus = 1 and TG.GroupReplyAddress IS NOT NULL AND TT.Agent = p_userID
					UNION ALL
				SELECT  tc.EmailFrom as EmailFrom FROM tblCompany tc where CompanyID = p_CompanyID and tc.EmailFrom IS NOT NULL AND tc.EmailFrom <> ''
					UNION ALL
				SELECT DISTINCT(tu.EmailAddress) as EmailFrom from tblUser tu where CompanyID = p_CompanyID and tu.Status=1;
			END IF;
			IF p_Admin < 1
			THEN
				SELECT DISTINCT TG.GroupReplyAddress as EmailFrom FROM tblTicketGroups TG INNER JOIN tblTickets TT ON TT.Group = TG.GroupID where TG.CompanyID = p_CompanyID and GroupEmailStatus = 1 and TG.GroupReplyAddress IS NOT NULL AND TT.Agent = p_userID
					UNION ALL
				SELECT  tc.EmailFrom as EmailFrom FROM tblCompany tc where CompanyID = p_CompanyID and tc.EmailFrom IS NOT NULL AND tc.EmailFrom <> ''
					UNION ALL
				SELECT tu.EmailAddress as EmailFrom from tblUser tu where CompanyID = p_CompanyID and tu.UserID = p_userID and tu.Status=1
					UNION ALL
				SELECT tu.EmailAddress as EmailFrom from tblUser tu where CompanyID = p_CompanyID and tu.Status=1  and AdminUser = 0 and tu.UserID not in ( select UserID from tblUserRole );
			END IF;

		END IF;
	END IF;

	IF p_Ticket = 0
	THEN
		IF p_Admin > 0
		THEN
			SELECT  tc.EmailFrom as EmailFrom FROM tblCompany tc where tc.CompanyID=p_CompanyID AND tc.EmailFrom IS NOT NULL AND tc.EmailFrom <> ''
				UNION ALL
			SELECT DISTINCT(tu.EmailAddress) as EmailFrom from tblUser tu where CompanyID=p_CompanyID AND tu.Status=1;
			
		END IF;
		IF p_Admin < 1
		THEN
			SELECT  tc.EmailFrom as EmailFrom FROM tblCompany tc where CompanyID=p_CompanyID AND tc.EmailFrom IS NOT NULL AND tc.EmailFrom <> ''
				UNION ALL
			SELECT tu.EmailAddress as EmailFrom from tblUser tu where CompanyID=p_CompanyID AND tu.UserID = p_userID and tu.Status=1
				UNION ALL
			SELECT tu.EmailAddress as EmailFrom from tblUser tu where CompanyID=p_CompanyID AND tu.Status=1  and AdminUser = 0 and tu.UserID not in ( select UserID from tblUserRole );


		
		END IF;
	END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;	

DROP PROCEDURE IF EXISTS `prc_getEmailTemplate`;
DELIMITER //
CREATE PROCEDURE `prc_getEmailTemplate`(
	IN `p_CompanyID` INT,
	IN `p_Name` VARCHAR(50) ,
	IN `p_isReseller` INT,
	IN `p_is_IsGlobal` INT,
	IN `p_LanguageID` INT,
	IN `p_Type` INT,
	IN `p_userID` INT,
	IN `p_Status` INT,
	IN `p_StaticType` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
BEGIN
	DECLARE v_OffSet_ INT;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	IF p_isReseller = 0
	THEN
	
		IF p_isExport = 0
		THEN
			SELECT
				TemplateName,
				CASE WHEN IsGlobal = 1
				THEN
					'All'
				ELSE 
					IFNULL((SELECT ResellerName FROM tblReseller WHERE ChildCompanyID = tblEmailTemplate.CompanyID),'')
				END AS ResellerName,
				Subject,				
				CreatedBy,
				updated_at,
				Status,
				TemplateID,
				StaticType,
				IsGlobal,
				Type
			FROM tblEmailTemplate
			WHERE  
				Status = p_Status
				AND LanguageID = p_LanguageID
				AND (p_CompanyID = 0 OR CompanyID = p_CompanyID)
				AND (p_Name = '' OR TemplateName LIKE CONCAT('%',p_Name,'%'))		
				AND (p_Type = 0 OR Type = p_Type)
				AND (p_userID = 0 OR userID = p_userID)
				AND (p_StaticType = 0 OR StaticType = p_StaticType)
			ORDER BY
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TemplateNameDESC') THEN TemplateName
				END DESC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TemplateNameASC') THEN TemplateName
				END ASC,				
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ResellerNameDESC') THEN ResellerName
				END DESC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ResellerNameASC') THEN ResellerName
				END ASC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SubjectDESC') THEN Subject
				END DESC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SubjectASC') THEN Subject
				END ASC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TypeDESC') THEN Type
				END DESC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TypeASC') THEN Type
				END ASC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreatedByDESC') THEN CreatedBy
				END DESC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreatedByASC') THEN CreatedBy
				END ASC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN updated_at
				END DESC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN updated_at
				END ASC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusDESC') THEN Status
				END DESC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusASC') THEN Status
				END ASC
			LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT
			COUNT(TemplateID) AS totalcount
			FROM tblEmailTemplate
			WHERE  
				Status = p_Status
				AND LanguageID = p_LanguageID
				AND (p_CompanyID = 0 OR CompanyID = p_CompanyID)
				AND (p_Name = '' OR TemplateName LIKE CONCAT('%',p_Name,'%'))		
				AND (p_Type = 0 OR Type = p_Type)
				AND (p_userID = 0 OR userID = p_userID)
				AND (p_StaticType = 0 OR StaticType = p_StaticType);
		END IF;

		IF p_isExport = 1
		THEN

			SELECT
				TemplateName,
				CASE WHEN IsGlobal = 1
				THEN
					'All'
				ELSE 
					IFNULL((SELECT ResellerName FROM tblReseller WHERE ChildCompanyID = tblEmailTemplate.CompanyID),'')
				END AS ResellerName,
				Subject,
				IFNULL(SystemType,'') AS SystemType,
				CreatedBy,
				updated_at
			FROM tblEmailTemplate
			WHERE  
				Status = p_Status
				AND LanguageID = p_LanguageID
				AND (p_CompanyID = 0 OR CompanyID = p_CompanyID)
				AND (p_Name = '' OR TemplateName LIKE CONCAT('%',p_Name,'%'))		
				AND (p_Type = 0 OR Type = p_Type)
				AND (p_userID = 0 OR userID = p_userID)
				AND (p_StaticType = 0 OR StaticType = p_StaticType);

		END IF;	
	
	END IF;

	IF p_isReseller = 1
	THEN
	
		DROP TEMPORARY TABLE IF EXISTS tmp_EmailTemplate_;
		CREATE TEMPORARY TABLE tmp_EmailTemplate_ (
			TemplateName VARCHAR(250),
			ResellerName VARCHAR(50),
			Subject VARCHAR(500),
			CreatedBy VARCHAR(200),
			updated_at DATETIME,
			Status TINYINT(4),
			TemplateID INT,
			StaticType TINYINT(4),
			IsGlobal INT,
			Type TINYINT(3),
			SystemType VARCHAR(100)
			);
			
		INSERT INTO tmp_EmailTemplate_
			SELECT
				TemplateName,
				CASE WHEN IsGlobal = 1
				THEN
					'All'
				ELSE 
					IFNULL((SELECT ResellerName FROM tblReseller WHERE ChildCompanyID = tblEmailTemplate.CompanyID),'')
				END AS ResellerName,
				Subject,				
				CreatedBy,
				updated_at,
				Status,
				TemplateID,
				StaticType,
				IsGlobal,
				Type,
				SystemType
			FROM tblEmailTemplate
			WHERE  
				Status = p_Status
				AND LanguageID = p_LanguageID
				AND (p_userID = 0 OR userID = p_userID)
				AND (CompanyID = p_CompanyID OR IsGlobal=1)
				AND (p_Name = '' OR TemplateName LIKE CONCAT('%',p_Name,'%'))		
				AND (p_Type = 0 OR Type = p_Type)
				AND (p_userID = 0 OR userID = p_userID)
				AND (p_StaticType = 0 OR StaticType = p_StaticType)
				;	
				
		DELETE FROM tmp_EmailTemplate_ WHERE TemplateID in (SELECT min(TemplateID) AS TemplateID FROM tblEmailTemplate WHERE (CompanyID=p_CompanyID OR Isglobal=1) GROUP BY SystemType HAVING COUNT(*) > 1);		
	
		IF p_isExport = 0
		THEN

			SELECT
				TemplateName,
				ResellerName,
				Subject,				
				CreatedBy,
				updated_at,
				Status,
				TemplateID,
				StaticType,
				IsGlobal,
				Type
			FROM tmp_EmailTemplate_
			ORDER BY
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TemplateNameDESC') THEN TemplateName
				END DESC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TemplateNameASC') THEN TemplateName
				END ASC,				
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ResellerNameDESC') THEN ResellerName
				END DESC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ResellerNameASC') THEN ResellerName
				END ASC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SubjectDESC') THEN Subject
				END DESC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SubjectASC') THEN Subject
				END ASC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TypeDESC') THEN Type
				END DESC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TypeASC') THEN Type
				END ASC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreatedByDESC') THEN CreatedBy
				END DESC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreatedByASC') THEN CreatedBy
				END ASC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN updated_at
				END DESC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN updated_at
				END ASC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusDESC') THEN Status
				END DESC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusASC') THEN Status
				END ASC
			LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT
			COUNT(TemplateID) AS totalcount
			FROM tmp_EmailTemplate_;
		END IF;

		IF p_isExport = 1
		THEN

			SELECT
				TemplateName,
				ResellerName,
				Subject,
				IFNULL(SystemType,'') AS SystemType,
				CreatedBy,
				updated_at
			FROM tmp_EmailTemplate_;

		END IF;	
	
	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;