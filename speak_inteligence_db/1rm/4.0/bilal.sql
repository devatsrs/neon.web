ALTER TABLE `tblCompany`
	ADD COLUMN `LastInvoiceNumber` BIGINT(20) NOT NULL DEFAULT '0' AFTER `InvoiceStatus`,
	ADD COLUMN `InvoiceNumberPrefix` VARCHAR(50) NULL DEFAULT NULL AFTER `LastInvoiceNumber`

	CREATE TABLE `tblAccountRateTable` (
	`AccountRateTableID` INT NOT NULL AUTO_INCREMENT,
	`AccountID` INT NOT NULL DEFAULT '0',
	`AccessRateTableID` INT NOT NULL DEFAULT '0',
	`PackageRateTableID` INT NOT NULL DEFAULT '0',
	`TerminationRateTableID` INT NOT NULL DEFAULT '0',
	`created_at` DATETIME NULL DEFAULT NULL,
	`updated_at` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`AccountRateTableID`)
)
COLLATE='latin1_swedish_ci'
;

CREATE TABLE `tblSessions` (
	`id` VARCHAR(255) NOT NULL COLLATE 'utf8_unicode_ci',
	`payload` MEDIUMTEXT NOT NULL COLLATE 'utf8_unicode_ci',
	`last_activity` INT(11) NOT NULL,
	UNIQUE INDEX `id` (`id`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;
