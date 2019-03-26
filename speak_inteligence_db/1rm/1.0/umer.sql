/* added three columns country, dutchprovider, dutchfoundation */
ALTER TABLE `tblTaxRate`
	ADD COLUMN `Country` VARCHAR(50) NULL DEFAULT NULL AFTER `FlatStatus`,
	ADD COLUMN `DutchProvider` TINYINT(3) NULL DEFAULT NULL AFTER `Country`,
	ADD COLUMN `DutchFoundation` TINYINT(3) NULL DEFAULT NULL AFTER `DutchProvider`;
SELECT `DEFAULT_COLLATION_NAME` FROM `information_schema`.`SCHEMATA` WHERE `SCHEMA_NAME`='speakintelligentRM';
SHOW TABLE STATUS FROM `speakintelligentRM`;
SHOW FUNCTION STATUS WHERE `Db`='speakintelligentRM';
SHOW PROCEDURE STATUS WHERE `Db`='speakintelligentRM';
SHOW TRIGGERS FROM `speakintelligentRM`;
SELECT *, EVENT_SCHEMA AS `Db`, EVENT_NAME AS `Name` FROM information_schema.`EVENTS` WHERE `EVENT_SCHEMA`='speakintelligentRM';
SHOW CREATE TABLE `speakintelligentRM`.`tblTaxRate`;
/* Entering session "staging" */
SHOW CREATE TABLE `speakintelligentRM`.`tblTaxRate`;

/* end changes */