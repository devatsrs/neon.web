-- --------------------------------------------------------
-- Host:                         78.129.140.6
-- Server version:               5.7.25 - MySQL Community Server (GPL)
-- Server OS:                    Linux
-- HeidiSQL Version:             9.5.0.5196
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for speakintelligentRM
CREATE DATABASE IF NOT EXISTS `speakintelligentRM` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `speakintelligentRM`;

-- Dumping structure for procedure speakintelligentRM.prc_getEmailTemplate
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getEmailTemplate`(
	IN `p_CompanyID` INT,
	IN `p_Name` VARCHAR(50) ,
	IN `p_isReseller` INT,
	IN `p_SystemType` VARCHAR(50),
	IN `p_is_IsGlobal` INT,
	IN `p_LanguageID` VARCHAR(50),
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
				SystemType,
				(select language from tblLanguage where tblLanguage.LanguageID = tblEmailTemplate.LanguageID) as Language,				
				CASE WHEN IsGlobal = 1
				THEN
					'All'
				ELSE 
					IFNULL((SELECT ResellerName FROM tblReseller WHERE ChildCompanyID = tblEmailTemplate.CompanyID),'')
				END AS PartnerName,
				TemplateName,
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
				AND (p_LanguageID = 0 OR LanguageID = p_LanguageID)
				AND (p_CompanyID = 0 OR CompanyID = p_CompanyID)
				AND (p_Name = '' OR TemplateName LIKE CONCAT('%',p_Name,'%'))		
				AND (p_Type = 0 OR Type = p_Type)
				AND (p_userID = 0 OR userID = p_userID)
				AND (p_StaticType = 0 OR StaticType = p_StaticType)
				AND (p_SystemType = '' OR SystemType = p_SystemType)
				AND (p_is_IsGlobal = 0 OR IsGlobal = p_is_IsGlobal)
			ORDER BY
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TemplateNameDESC') THEN TemplateName
				END DESC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TemplateNameASC') THEN TemplateName
				END ASC,				
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ResellerNameDESC') THEN PartnerName
				END DESC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ResellerNameASC') THEN PartnerName
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
				AND (p_LanguageID = 0 OR LanguageID = p_LanguageID)
				AND (p_CompanyID = 0 OR CompanyID = p_CompanyID)
				AND (p_Name = '' OR TemplateName LIKE CONCAT('%',p_Name,'%'))		
				AND (p_Type = 0 OR Type = p_Type)
				AND (p_userID = 0 OR userID = p_userID)
				AND (p_StaticType = 0 OR StaticType = p_StaticType)
				AND (p_SystemType = '' OR SystemType = p_SystemType)
				AND (p_is_IsGlobal = 0 OR IsGlobal = p_is_IsGlobal);
		END IF;

		IF p_isExport = 1
		THEN

			SELECT
				SystemType,
				(select language from tblLanguage where tblLanguage.LanguageID = tblEmailTemplate.LanguageID) as Language,				
				CASE WHEN IsGlobal = 1
				THEN
					'All'
				ELSE 
					IFNULL((SELECT ResellerName FROM tblReseller WHERE ChildCompanyID = tblEmailTemplate.CompanyID),'')
				END AS PartnerName,
				TemplateName,
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
				AND (p_LanguageID = 0 OR LanguageID = p_LanguageID)
				AND (p_CompanyID = 0 OR CompanyID = p_CompanyID)
				AND (p_Name = '' OR TemplateName LIKE CONCAT('%',p_Name,'%'))		
				AND (p_Type = 0 OR Type = p_Type)
				AND (p_userID = 0 OR userID = p_userID)
				AND (p_StaticType = 0 OR StaticType = p_StaticType)
				AND (p_SystemType = '' OR SystemType = p_SystemType)
				AND (p_is_IsGlobal = 0 OR IsGlobal = p_is_IsGlobal);
			

		END IF;	
	
	END IF;

	IF p_isReseller = 1
	THEN
	
		DROP TEMPORARY TABLE IF EXISTS tmp_EmailTemplate_;
		CREATE TEMPORARY TABLE tmp_EmailTemplate_ (
			SystemType VARCHAR(100),
			LanguageID VARCHAR(250),		
			ResellerName VARCHAR(50),
			TemplateName VARCHAR(250),
			Subject VARCHAR(500),
			CreatedBy VARCHAR(200),
			updated_at DATETIME,
			Status TINYINT(4),
			TemplateID INT,
			StaticType TINYINT(4),
			IsGlobal INT,
			Type TINYINT(3),
			CompanyID INT
		
			);
			
		INSERT INTO tmp_EmailTemplate_
			SELECT
				SystemType,
				LanguageID,				
				CASE WHEN IsGlobal = 1
				THEN
					'All'
				ELSE 
					IFNULL((SELECT ResellerName FROM tblReseller WHERE ChildCompanyID = tblEmailTemplate.CompanyID),'')
				END AS PartnerName,
				TemplateName,
				Subject,
				CreatedBy,
				updated_at,
				Status,
				TemplateID,
				StaticType,
				IsGlobal,
				Type,
				CompanyID
			FROM tblEmailTemplate
			WHERE  
				Status = p_Status
				AND (p_LanguageID = 0 OR LanguageID = p_LanguageID)
				AND (p_userID = 0 OR userID = p_userID)
				AND (CompanyID = p_CompanyID OR IsGlobal=1)
				AND (p_Name = '' OR TemplateName LIKE CONCAT('%',p_Name,'%'))		
				AND (p_Type = 0 OR Type = p_Type)
				AND (p_userID = 0 OR userID = p_userID)
				AND (p_StaticType = 0 OR StaticType = p_StaticType)
				AND (p_SystemType = '' OR SystemType = p_SystemType)
				AND (p_is_IsGlobal = 0 OR IsGlobal = p_is_IsGlobal);	
				
		DELETE FROM tmp_EmailTemplate_ WHERE TemplateID in (SELECT min(TemplateID) AS TemplateID FROM tblEmailTemplate WHERE (CompanyID=p_CompanyID OR Isglobal=1) GROUP BY SystemType HAVING COUNT(*) > 1);		
	
		IF p_isExport = 0
		THEN

			SELECT
				SystemType,
				(select Language from tblLanguage where tblLanguage.LanguageID = tmp_EmailTemplate_.LanguageID) as Language,				
				CASE WHEN IsGlobal = 1
				THEN
					'All'
				ELSE 
					IFNULL((SELECT ResellerName FROM tblReseller WHERE ChildCompanyID = tmp_EmailTemplate_.CompanyID),'')
				END AS PartnerName,
				TemplateName,
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
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ResellerNameDESC') THEN PartnerName
				END DESC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ResellerNameASC') THEN PartnerName
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
			   SystemType,
				(select language from tblLanguage where tblLanguage.LanguageID = tmp_EmailTemplate_.LanguageID) as Language,				
				CASE WHEN IsGlobal = 1
				THEN
					'All'
				ELSE 
					IFNULL((SELECT ResellerName FROM tblReseller WHERE ChildCompanyID = tmp_EmailTemplate_.CompanyID),'')
				END AS PartnerName,
				TemplateName,
				Subject,
				CreatedBy,
				updated_at,
				Status,
				TemplateID,
				StaticType,
				IsGlobal,
				Type
			FROM tmp_EmailTemplate_;

		END IF;	
	
	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
