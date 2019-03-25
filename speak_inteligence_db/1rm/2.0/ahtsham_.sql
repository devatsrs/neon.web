USE `speakintelligentRM`;

ALTER TABLE `tblBillingClass` DROP COLUMN `ResellerOwner`;
	
ALTER TABLE `tblBillingClass` ADD COLUMN `ResellerID` INT NULL AFTER `UpdatedBy`;
	
DELIMITER $$

DROP PROCEDURE IF EXISTS `prc_getBillingClass`$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getBillingClass`(IN `p_CompanyID` VARCHAR(50), IN `p_Name` VARCHAR(50) , IN `p_isReseller` VARCHAR(50) , IN `p_resellerComapyId` VARCHAR(50) , IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT)
BEGIN
	DECLARE v_OffSet_ INT;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	IF p_isExport = 0
	THEN
		SELECT
			NAME,
			(SELECT ResellerName FROM tblReseller WHERE ChildCompanyID =  tblBillingClass.CompanyID) AS ResellerName,
			UpdatedBy,
			updated_at,
			BillingClassID,
			(SELECT COUNT(*) FROM tblAccountBilling a WHERE a.BillingClassID =  tblBillingClass.BillingClassID) AS Applied
		FROM tblBillingClass
		WHERE  
			((p_isReseller = 1 AND tblBillingClass.CompanyID = p_CompanyID) 
			OR  ((p_isReseller = 0 AND p_resellerComapyId = '') OR ( p_resellerComapyId <> '' AND tblBillingClass.CompanyID = p_resellerComapyId  )))
			AND (p_Name = '' OR NAME LIKE REPLACE(p_Name, '*', '%'))
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
		FROM tblBillingClass
		WHERE  
			((p_isReseller = 1 AND tblBillingClass.CompanyID = p_CompanyID) 
			OR  ((p_isReseller = 0 AND p_resellerComapyId = '') OR ( p_resellerComapyId <> '' AND tblBillingClass.CompanyID = p_resellerComapyId  )))
			AND (p_Name = '' OR NAME LIKE REPLACE(p_Name, '*', '%'));
	END IF;
	IF p_isExport = 1
	THEN
	
		SELECT
			NAME,
			(SELECT ResellerName FROM tblReseller WHERE ChildCompanyID =  tblBillingClass.CompanyID) AS ResellerName,
			UpdatedBy,
			updated_at
		FROM tblBillingClass
		WHERE  
			((p_isReseller = 1 AND tblBillingClass.CompanyID = p_CompanyID) 
			OR  ((p_isReseller = 0 AND p_resellerComapyId = '') OR ( p_resellerComapyId <> '' AND tblBillingClass.CompanyID = p_resellerComapyId  )))
			AND (p_Name = '' OR NAME LIKE REPLACE(p_Name, '*', '%'));
	
	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END$$

DELIMITER ;