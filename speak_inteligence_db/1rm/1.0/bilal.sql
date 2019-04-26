CREATE TABLE IF NOT EXISTS `tblApprovedOutPaymentLog` (
	`ApprovedOutPaymentLogID` INT(11) NOT NULL AUTO_INCREMENT,
	`AccountID` INT(11) NOT NULL,
	`VendorID` INT(11) NOT NULL,
	`InvoiceID` INT(11) NOT NULL,
	`StartDate` DATETIME NULL DEFAULT NULL,
	`EndDate` DATETIME NULL DEFAULT NULL,
	`Amount` DECIMAL(18,6) NOT NULL,
	`created_at` DATETIME NULL DEFAULT NULL,
	`updated_at` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`ApprovedOutPaymentLogID`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;


ALTER TABLE `tblAccountBalance`
	ADD COLUMN `OutPaymentAwaiting` DECIMAL(18,6) NULL DEFAULT 0 AFTER `OutPayment`,
	ADD COLUMN `OutPaymentAvailable` DECIMAL(18,6) NULL DEFAULT 0 AFTER `OutPaymentAwaiting`,
	ADD COLUMN `OutPaymentPaid` DECIMAL(18,6) NULL DEFAULT 0 AFTER `OutPaymentAvailable`;

ALTER TABLE `tblCLIRateTable`
ADD COLUMN `SpecialRateTableID` INT(11) NULL DEFAULT NULL AFTER `NoType`,
ADD COLUMN `SpecialTerminationRateTableID` INT(11) NULL DEFAULT NULL AFTER `SpecialRateTableID`;

ALTER TABLE `tblAccountServicePackage`
ADD COLUMN `SpecialPackageRateTableID` INT(11) NULL DEFAULT NULL AFTER `ServiceID`;