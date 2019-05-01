USE `speakintelligentCDR`;

UPDATE tblUsageDetailFailedCall SET CityTariff='' where CityTariff IS NULL;
ALTER TABLE `tblUsageDetailFailedCall`
	CHANGE COLUMN `CityTariff` `City` VARCHAR(50) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci' AFTER `PackageTimezonesID`,
	ADD COLUMN `Tariff` VARCHAR(50) NOT NULL DEFAULT '' AFTER `City`;

UPDATE tblUsageDetails SET CityTariff='' where CityTariff IS NULL;
ALTER TABLE `tblUsageDetails`
	CHANGE COLUMN `CityTariff` `City` VARCHAR(50) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci' AFTER `PackageTimezonesID`,
	ADD COLUMN `Tariff` VARCHAR(50) NOT NULL DEFAULT '' AFTER `City`;
