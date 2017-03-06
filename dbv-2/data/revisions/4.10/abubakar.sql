USE `RMBilling3`;

ALTER TABLE `tblBillingSubscription`
	ADD COLUMN `AnnuallyFee` DECIMAL(18,2) NULL DEFAULT NULL AFTER `CurrencyID`,
	ADD COLUMN `QuarterlyFee` DECIMAL(18,2) NULL DEFAULT NULL AFTER `AnuallyFee`;

ALTER TABLE `tblAccountSubscription`
	ADD COLUMN `AnnuallyFee` DECIMAL(18,2) NULL DEFAULT NULL AFTER `Discount`,
	ADD COLUMN `QuarterlyFee` DECIMAL(18,2) NULL DEFAULT NULL AFTER `AnnuallyFee`;