CREATE DEFINER=`root`@`localhost` PROCEDURE `fnGetCountry`()
BEGIN

DROP TEMPORARY TABLE IF EXISTS temptblCountry;
CREATE TEMPORARY TABLE IF NOT EXISTS `temptblCountry` (
	`CountryID` INT(11) NOT NULL AUTO_INCREMENT,
	`Prefix` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`Country` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	PRIMARY KEY (`CountryID`)
);
INSERT INTO temptblCountry(CountryID,Prefix,Country)
SELECT CountryID,Prefix,Country FROM LocalRatemanagement.tblCountry;
END