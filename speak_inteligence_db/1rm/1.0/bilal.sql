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

INSERT INTO `tblCronJobCommand` (`CompanyID`, `GatewayID`, `Title`, `Command`, `Settings`, `Status`, `created_at`, `created_by`) VALUES ('1', NULL, 'Update PBX Customer Rate', 'updatepbxcustomerrate', '[[{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"}]]', '1', '2019-04-08 16:49:33', 'RateManagementSystem');