USE `RMBilling3`;

ALTER TABLE `tblTransactionLog`
	CHANGE COLUMN `Reposnse` `Response` LONGTEXT NULL COLLATE 'utf8_unicode_ci' AFTER `CreatedBy`;