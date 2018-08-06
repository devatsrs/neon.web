use Ratemanagement3;

ALTER TABLE `tblTicketGroups`
	ADD COLUMN `GroupEmailPort` SMALLINT(4) NULL DEFAULT NULL AFTER `GroupEmailServer`;

ALTER TABLE `tblTicketGroups`
	ADD COLUMN `GroupEmailIsSSL` TINYINT(1) NULL DEFAULT '0' AFTER `GroupEmailPort`;

INSERT INTO `tblTicketImportRuleConditionType` (`TicketImportRuleConditionTypeID`, `Condition`, `ConditionText`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES (10, 'type', 'Type', NULL, NULL, NULL, NULL);