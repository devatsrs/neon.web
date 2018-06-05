Use Ratemanagement3;

INSERT INTO `tblEmailTemplate` (`CompanyID`, `LanguageID`, `TemplateName`, `Subject`, `TemplateBody`, `created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`, `userID`, `Type`, `EmailFrom`, `StaticType`, `SystemType`, `Status`, `StatusDisabled`, `TicketTemplate`) VALUES (1, 43, 'AccountBalanceEmailReminder', '{{AccountName}} - Account Balance', '<p>Please find below your account balance details.</p><p>{{AccountBalance}}</p>', '2018-05-22 16:42:31', 'Vasim Seta', '2018-05-23 16:20:17', 'Vasim Seta', NULL, 0, '', 1, 'AccountBalanceEmailReminder', 1, 1, 0);

ALTER TABLE `tblBillingClass` ADD COLUMN `DeductCallChargeInAdvance` TINYINT(1) NULL DEFAULT '0' AFTER `SendInvoiceSetting`;
ALTER TABLE `tblBillingClass` ADD COLUMN `SuspendAccount` TINYINT(1) NULL DEFAULT '0' AFTER `DeductCallChargeInAdvance`;
ALTER TABLE `tblAccountBilling`	ADD COLUMN `AutoPayMethod` INT(11) NULL DEFAULT '0' AFTER `AutoPaymentSetting`;

ALTER TABLE `tblBillingClass` 
	ADD COLUMN `AutoPaymentSetting` VARCHAR(50) NULL DEFAULT NULL AFTER `SuspendAccount`,	
	ADD COLUMN `AutoPayMethod` INT(11) NULL DEFAULT '0' AFTER `AutoPaymentSetting`;

ALTER TABLE `tblBillingClass`
	ADD COLUMN `BalanceWarningStatus` TINYINT(4) NULL DEFAULT NULL AFTER `LowBalanceReminderSettings`,
	ADD COLUMN `BalanceWarningSettings` VARCHAR(5000) NULL DEFAULT NULL AFTER `BalanceWarningStatus`;
	
/* added on 29th may */
CREATE TABLE `tblSubscriptionDiscountPlan` (
	`SubscriptionDiscountPlanID` INT(11) NOT NULL AUTO_INCREMENT,
	`AccountID` INT(11) NULL DEFAULT '0',
	`ServiceID` INT(11) NULL DEFAULT '0',
	`AccountSubscriptionID` INT(11) NULL DEFAULT '0',
	`AccountName` VARCHAR(200) NULL DEFAULT '0' COLLATE 'utf8_unicode_ci',
	`AccountCLI` VARCHAR(300) NULL DEFAULT '0' COLLATE 'utf8_unicode_ci',
	`InboundDiscountPlans` VARCHAR(300) NULL DEFAULT '0' COLLATE 'utf8_unicode_ci',
	`OutboundDiscountPlans` VARCHAR(300) NULL DEFAULT '0' COLLATE 'utf8_unicode_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`updated_at` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`SubscriptionDiscountPlanID`),
	UNIQUE INDEX `IX_UNIQUE_ACCOUNTCLI` (`AccountCLI`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;
