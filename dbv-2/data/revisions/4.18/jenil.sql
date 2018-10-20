Use `RMBilling3`;

ALTER TABLE `tblProduct`
	ADD COLUMN `ItemTypeID` INT(11) NULL DEFAULT '0' AFTER `AppliedTo`,
	ADD COLUMN `Buying_price` DECIMAL(18,2) NULL DEFAULT '0.00' AFTER `ItemTypeID`,
	ADD COLUMN `Quantity` INT(11) NULL DEFAULT '0' AFTER `Buying_price`,
	ADD COLUMN `Low_stock_level` INT(11) NULL DEFAULT '0' AFTER `Quantity`,
	ADD COLUMN `Enable_stock` TINYINT(1) NULL DEFAULT '0' AFTER `Low_stock_level`;


ALTER TABLE tblProduct	ALTER Quantity DROP DEFAULT,
  ALTER Low_stock_level DROP DEFAULT;
ALTER TABLE tblProduct	CHANGE COLUMN Quantity Quantity INT(11) NULL AFTER Buying_price,
  CHANGE COLUMN Low_stock_level Low_stock_level INT(11) NULL AFTER Quantity;

prc_getLowStockItemsAlert();

/* Note :- only check with tblProduct table , quantity and lowstock */