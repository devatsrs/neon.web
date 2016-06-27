CREATE TABLE `tblCustomerTrunk` (
	`CustomerTrunkID` INT(11) NOT NULL AUTO_INCREMENT,
	`RateTableID` BIGINT(20) NULL DEFAULT NULL,
	`CompanyID` INT(11) NOT NULL,
	`CodeDeckId` INT(11) NULL DEFAULT NULL,
	`AccountID` INT(11) NOT NULL,
	`TrunkID` INT(11) NOT NULL,
	`Prefix` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`IncludePrefix` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1',
	`Status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1',
	`created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	`CreatedBy` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	`ModifiedBy` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`RoutinePlanStatus` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
	`RateTableAssignDate` DATETIME NULL DEFAULT NULL,
	`UseInBilling` TINYINT(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (`CustomerTrunkID`),
	UNIQUE INDEX `IX_AccountIDTrunkID_Unique` (`AccountID`, `TrunkID`),
	INDEX `Index_AccountID_TrunkID_Status` (`TrunkID`, `AccountID`, `Status`),
	INDEX `FK_tblCustomerTrunk_tblRateTable` (`RateTableID`),
	INDEX `temp_index` (`AccountID`, `Status`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=4042
;
