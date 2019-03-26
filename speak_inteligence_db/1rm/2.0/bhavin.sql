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