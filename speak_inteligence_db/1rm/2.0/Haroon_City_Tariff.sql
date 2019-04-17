UPDATE `tblServiceTemplate` SET city_tariff= '' WHERE city_tariff IS NULL;
ALTER TABLE `tblServiceTemplate`
	CHANGE COLUMN `city_tariff` `City` VARCHAR(200) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci' AFTER `created_at`,
	ADD COLUMN `Tariff` VARCHAR(200) NOT NULL DEFAULT '' AFTER `City`;