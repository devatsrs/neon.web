CREATE TABLE `tblRateRule` (
  `RateRuleId` int(11) NOT NULL AUTO_INCREMENT,
  `RateGeneratorId` int(11) NOT NULL,
  `Code` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`RateRuleId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci