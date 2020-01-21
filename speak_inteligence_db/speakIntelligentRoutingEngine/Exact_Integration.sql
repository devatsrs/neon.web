use `speakintelligentRoutingEngine`;


ALTER TABLE `tblTaxRate`
	ADD COLUMN `VATCode` VARCHAR(50) NOT NULL DEFAULT '' AFTER `Status`;
