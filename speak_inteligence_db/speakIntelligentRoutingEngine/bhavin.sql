USE `speakIntelligentRoutingEngine`;

ALTER TABLE `tblActiveCall`
	CHANGE COLUMN `CityTariff` `City` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `PackageTimezonesID`;
	
UPDATE tblActiveCall SET City='' WHERE City IS NULL;

ALTER TABLE `tblActiveCall`
	CHANGE COLUMN `City` `City` VARCHAR(50) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci' AFTER `PackageTimezonesID`;	
	
ALTER TABLE `tblActiveCall`
	ADD COLUMN `Tariff` VARCHAR(50) NOT NULL DEFAULT '' AFTER `City`;