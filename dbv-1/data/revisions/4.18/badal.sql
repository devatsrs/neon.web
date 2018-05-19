Use Ratemanagement3;

ALTER TABLE `tblBillingClass` ADD COLUMN `DeductCallChargeInAdvance` TINYINT(1) NULL DEFAULT '0' AFTER `SendInvoiceSetting`;
ALTER TABLE `tblBillingClass` ADD COLUMN `SuspendAccount` TINYINT(1) NULL DEFAULT '0' AFTER `DeductCallChargeInAdvance`;
ALTER TABLE `tblAccountBilling`	ADD COLUMN `AutoPayMethod` INT(11) NULL DEFAULT '0' AFTER `AutoPaymentSetting`;

ALTER TABLE `tblBillingClass` 
	ADD COLUMN `AutoPaymentSetting` VARCHAR(50) NULL DEFAULT NULL AFTER `SuspendAccount`,	
	ADD COLUMN `AutoPayMethod` INT(11) NULL DEFAULT '0' AFTER `AutoPaymentSetting`;

ALTER TABLE `tblBillingClass`
	ADD COLUMN `BalanceWarningStatus` TINYINT(4) NULL DEFAULT NULL AFTER `LowBalanceReminderSettings`,
	ADD COLUMN `BalanceWarningSettings` VARCHAR(5000) NULL DEFAULT NULL AFTER `BalanceWarningStatus`;