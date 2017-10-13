Use Ratemanagement3;

ALTER TABLE `tblTicketGroups`
	ADD COLUMN `LastEmailReadDateTime` DATETIME NULL AFTER `updated_by`;

CREATE TABLE IF NOT EXISTS `tblJunkTicketEmail` (
	`JunkTicketEmailID` INT(11) NOT NULL AUTO_INCREMENT,
	`CompanyID` INT(11) NOT NULL DEFAULT '0',
	`From` VARCHAR(500) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`FromName` VARCHAR(500) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`EmailTo` VARCHAR(500) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`Cc` VARCHAR(500) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`Subject` VARCHAR(500) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`Message` LONGTEXT NULL COLLATE 'utf8_unicode_ci',
	`MessageID` VARCHAR(500) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`EmailParent` INT(11) NULL DEFAULT NULL,
	`AttachmentPaths` VARCHAR(500) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`Extra` VARCHAR(500) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`TicketID` VARCHAR(500) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`created_by` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	PRIMARY KEY (`JunkTicketEmailID`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;
