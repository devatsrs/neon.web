CREATE TABLE `tblCurrencyConversion` (
  `ConversionID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `CurrencyID` int(11) NOT NULL,
  `Value` decimal(18,6) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `EffectiveDate` datetime DEFAULT NULL,
  PRIMARY KEY (`ConversionID`),
  KEY `IX_CurrencyID_CompanyID` (`CurrencyID`,`CompanyID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci