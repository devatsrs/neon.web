USE `speakintelligentRM`;
ALTER TABLE `tblAccount`
	ADD COLUMN `PayoutMethod` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `PaymentDetail`;
	
	
CREATE TABLE `speakintelligentRM`.`tblAccountPayout` (
	`AccountPayoutID` INT(11) NOT NULL AUTO_INCREMENT,
	`CompanyID` INT(11) NOT NULL,
	`AccountID` INT(11) NOT NULL,
	`PaymentGatewayID` INT(11) NOT NULL,
	`Title` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`Options` TEXT NULL COLLATE 'utf8_unicode_ci',
	`Status` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
	`isDefault` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
	`Blocked` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
	`created_at` DATETIME NULL DEFAULT NULL,
	`created_by` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	`updated_by` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	PRIMARY KEY (`AccountPayoutID`)
)
 COLLATE 'utf8_unicode_ci' ENGINE=InnoDB ROW_FORMAT=Dynamic AUTO_INCREMENT=32;