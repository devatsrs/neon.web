Use RMBilling3;

ALTER TABLE `tblAccountOneOffCharge`
	CHANGE COLUMN `Qty` `Qty` DECIMAL(18,6) NULL DEFAULT NULL AFTER `Price`;
