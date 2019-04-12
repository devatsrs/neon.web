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
	ADD COLUMN `IsGlobal` TINYINT NOT NULL DEFAULT '0' AFTER `ZeroBalanceWarningSettings`;
	
ALTER TABLE `tblBillingClass`
	ADD COLUMN `ParentBillingClassID` TINYINT NOT NULL DEFAULT '0' AFTER `IsGlobal`;

DROP PROCEDURE IF EXISTS `prc_getBillingClass`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getBillingClass`(
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
			(b1.CompanyID=10 OR b1.IsGlobal=1) AND
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