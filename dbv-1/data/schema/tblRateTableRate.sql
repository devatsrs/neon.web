CREATE TABLE `tblRateTableRate` (
  `RateTableRateID` bigint(20) NOT NULL AUTO_INCREMENT,
  `RateID` int(11) NOT NULL,
  `RateTableId` bigint(20) NOT NULL,
  `Rate` decimal(18,6) NOT NULL DEFAULT '0.000000',
  `EffectiveDate` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PreviousRate` decimal(18,6) DEFAULT NULL,
  `Interval1` int(11) DEFAULT NULL,
  `IntervalN` int(11) DEFAULT NULL,
  `ConnectionFee` decimal(18,6) DEFAULT NULL,
  PRIMARY KEY (`RateTableRateID`),
  KEY `FK_tblRateTableRate_tblRate` (`RateID`),
  KEY `XI_tblRateTableRate_RateID` (`RateID`),
  KEY `IX_RateTableRate_RateID` (`RateID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci