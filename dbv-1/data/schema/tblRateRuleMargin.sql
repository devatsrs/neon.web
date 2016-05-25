CREATE TABLE `tblRateRuleMargin` (
  `RateRuleMarginId` int(11) NOT NULL AUTO_INCREMENT,
  `RateRuleId` int(11) NOT NULL,
  `MinRate` decimal(18,6) DEFAULT '0.000000',
  `MaxRate` decimal(18,6) DEFAULT '0.000000',
  `AddMargin` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`RateRuleMarginId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci