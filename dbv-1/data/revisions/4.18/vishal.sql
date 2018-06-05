use Ratemanagement3;

ALTER TABLE `tblTicketGroups`
	ADD COLUMN `GroupEmailPort` SMALLINT(4) NULL DEFAULT NULL AFTER `GroupEmailServer`;

ALTER TABLE `tblTicketGroups`
	ADD COLUMN `GroupEmailIsSSL` TINYINT(1) NULL DEFAULT '0' AFTER `GroupEmailPort`;