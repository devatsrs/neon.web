ALTER TABLE `tblRateGeneratorCalculatedRate`
	CHANGE COLUMN `TimeOfDay` `TimezonesID` INT(11) NULL DEFAULT NULL AFTER `Origination`;

ALTER TABLE `tblRateGeneratorCostComponent`
	CHANGE COLUMN `TimeOfDay` `TimezonesID` INT(11) NULL DEFAULT NULL AFTER `ToOrigination`,
	CHANGE COLUMN `ToTimeOfDay` `ToTimezonesID` INT(11) NULL DEFAULT NULL AFTER `TimezonesID`;

ALTER TABLE `tblRateGenerator`
	CHANGE COLUMN `TimeOfDay` `TimezonesID` INT(11) NULL DEFAULT NULL AFTER `Minutes`,
	CHANGE COLUMN `TimeOfDayPercentage` `TimezonesPercentage` FLOAT NULL DEFAULT NULL AFTER `TimezonesID`;

ALTER TABLE `tblRateGeneratorCalculatedRate`
	DROP COLUMN `RatePositionID`,
	DROP COLUMN `TrunkID`,
	DROP COLUMN `CurrencyID`;

ALTER TABLE `tblRateGeneratorCostComponent`
	DROP COLUMN `RatePositionID`,
	DROP COLUMN `TrunkID`,
	DROP COLUMN `CurrencyID`;

ALTER TABLE `tblRateRule`
	DROP COLUMN `Origination`;
