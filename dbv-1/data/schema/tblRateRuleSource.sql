CREATE TABLE `tblRateRuleSource` (
  `RateRuleSourceId` int(11) NOT NULL AUTO_INCREMENT,
  `RateRuleId` int(11) NOT NULL,
  `AccountId` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`RateRuleSourceId`),
  KEY `IX_RateRuleId_AccountID` (`AccountId`,`RateRuleId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci