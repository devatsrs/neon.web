CREATE TABLE `tblRateGenerator` (
  `RateGeneratorId` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `CodeDeckId` int(11) DEFAULT NULL,
  `RateGeneratorName` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `TrunkID` int(11) DEFAULT NULL,
  `RatePosition` int(11) NOT NULL DEFAULT '1',
  `RateTableId` int(11) DEFAULT NULL,
  `UseAverage` tinyint(1) NOT NULL DEFAULT '0',
  `UsePreference` tinyint(1) DEFAULT NULL,
  `Sources` varchar(50) COLLATE utf8_unicode_ci DEFAULT 'All',
  `Status` tinyint(3) unsigned DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CurrencyID` int(11) DEFAULT NULL,
  PRIMARY KEY (`RateGeneratorId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci