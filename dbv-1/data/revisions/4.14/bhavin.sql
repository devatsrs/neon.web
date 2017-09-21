Use Ratemanagement3;

INSERT INTO `tblIntegration` (`IntegrationID`, `CompanyId`, `Title`, `Slug`, `ParentID`, `MultiOption`) VALUES (21, 1, 'SagePay Direct Debit', 'sagepaydirectdebit', 4, 'N');

ALTER TABLE `tblAccountPaymentProfile`
	CHANGE COLUMN `Options` `Options` TEXT NULL DEFAULT NULL COLLATE 'utf8_unicode_ci';
