CREATE TABLE `tblRateTable` (
  `RateTableId` bigint(20) NOT NULL AUTO_INCREMENT,
  `CompanyId` int(11) NOT NULL,
  `CodeDeckId` int(11) NOT NULL,
  `RateTableName` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `RateGeneratorID` int(11) NOT NULL,
  `TrunkID` int(11) NOT NULL,
  `Status` tinyint(3) unsigned DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CurrencyID` int(11) DEFAULT NULL,
  PRIMARY KEY (`RateTableId`),
  KEY `Index 2` (`CompanyId`,`CodeDeckId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci