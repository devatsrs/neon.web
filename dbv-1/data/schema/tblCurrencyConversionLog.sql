CREATE TABLE `tblCurrencyConversionLog` (
  `ConversionLogID` int(11) NOT NULL AUTO_INCREMENT,
  `CurrencyID` int(11) NOT NULL,
  `CompanyID` int(11) NOT NULL,
  `EffectiveDate` datetime NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `Value` decimal(18,6) DEFAULT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ConversionLogID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci