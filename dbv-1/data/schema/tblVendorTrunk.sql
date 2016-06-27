CREATE TABLE `tblVendorTrunk` (
	`VendorTrunkID` INT(11) NOT NULL AUTO_INCREMENT,
	`CompanyID` INT(11) NOT NULL,
	`CodeDeckId` INT(11) NULL DEFAULT NULL,
	`AccountID` INT(11) NOT NULL,
	`TrunkID` INT(11) NOT NULL,
	`Prefix` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`Status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1',
	`created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	`CreatedBy` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	`ModifiedBy` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`UseInBilling` TINYINT(1) NULL DEFAULT '0',
	PRIMARY KEY (`VendorTrunkID`),
	UNIQUE INDEX `IX_Unique_TrunkId_AccountId` (`TrunkID`, `AccountID`),
	INDEX `IX_AccountID_TrunkID_Status` (`AccountID`, `TrunkID`, `Status`),
	INDEX `IX_AccountID_TrunkID_Codedeckid` (`TrunkID`, `CodeDeckId`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=2237
;
