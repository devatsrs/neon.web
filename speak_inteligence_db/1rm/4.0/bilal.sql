ALTER TABLE `tblCompany`
	ADD COLUMN `LastInvoiceNumber` BIGINT(20) NOT NULL DEFAULT '0' AFTER `InvoiceStatus`,
	ADD COLUMN `InvoiceNumberPrefix` VARCHAR(50) NULL DEFAULT NULL AFTER `LastInvoiceNumber`;