USE `Ratemanagement3`;


CREATE TABLE `tblAccountImportExportLog` (
	`AccountImportExportLogID` INT(11) NOT NULL AUTO_INCREMENT,
	`CompanyID` INT(11) NOT NULL DEFAULT '0',
	`AccountID` INT(11) NOT NULL DEFAULT '0',
	`GatewayID` INT(11) NULL DEFAULT NULL,
	`OLDAccountName` INT(11) NOT NULL DEFAULT '0',
	`AccountName` INT(11) NOT NULL DEFAULT '0',
	`Address1` INT(11) NOT NULL DEFAULT '0',
	`Address2` INT(11) NOT NULL DEFAULT '0',
	`Address3` INT(11) NOT NULL DEFAULT '0',
	`City` INT(11) NOT NULL DEFAULT '0',
	`PostCode` INT(11) NOT NULL DEFAULT '0',
	`Country` INT(11) NOT NULL DEFAULT '0',
	`IsCustomer` INT(11) NOT NULL DEFAULT '0',
	`IsVendor` INT(11) NOT NULL DEFAULT '0',
	`Processed` INT(11) NOT NULL DEFAULT '0',
	`created_at` DATETIME NULL DEFAULT NULL,
	`CreatedBy` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`ModifiedBy` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`AccountImportExportLogID`),
	INDEX `CompanyID_Processed` (`CompanyID`, `Processed`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;


Delimiter ;;
CREATE PROCEDURE `prc_AccountImportExportLogMarkProcessed`(
	IN `p_CompanyID` INT,
	IN `p_GatewayID` INT,
	IN `p_AccountImportExportLogIDs` TEXT

)
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


	UPDATE
	tblAccountImportExportLog
	SET Processed = 1
	WHERE
	FIND_IN_SET(AccountImportExportLogID,p_AccountImportExportLogIDs) > 0
	AND
	CompanyID = p_CompanyID
	AND
	GatewayID = p_GatewayID
	;


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;


END;;
Delimiter ;

Delimiter ;;
CREATE PROCEDURE `prc_getAccountImportExportLog`(
	IN `p_CompanyID` INT


,
	IN `p_GatewayID` INT





)
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


 	/*
	 What to return ?
	 Seperate Entry for each gateway
	 		in tblAccountImportExportLog
			 so we can fetch GatewayID = p_GatewayID in tblAccountImportExportLog
	Seperate Entry for Customer / Vendor
			even if IsCustomer and IsVendor is on

	 CompanyID
	 AccountName

	 */

	SELECT
	AccountImportExportLogID,
	OLDAccountName,
	AccountName,
	Email,
	Address1,
	Address2,
	Address3,
	City,
	PostCode,
	Country,
	IsCustomer,
	IsVendor


	FROM tblAccountImportExportLog al
	WHERE
	al.CompanyID = p_CompanyID

	AND
	Processed = 0

	order by created_at ;


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;


END;;
Delimiter ;




