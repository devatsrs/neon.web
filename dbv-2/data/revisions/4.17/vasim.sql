USE `RMBilling3`;

set sql_mode = "";
ALTER TABLE `tblInvoiceDetail`
	CHANGE COLUMN `Qty` `Qty` FLOAT NULL DEFAULT NULL AFTER `Price`;