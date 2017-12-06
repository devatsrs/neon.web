USE `RMBilling3`;

ALTER TABLE `tblInvoiceTemplate`
	ADD COLUMN `ItemDescription` VARCHAR(50) NULL AFTER `CDRType`;
ALTER TABLE `tblInvoiceTemplate`
	ADD COLUMN `VisibleColumns` VARCHAR(100) NULL DEFAULT NULL AFTER `ItemDescription`;
