CREATE TABLE `tblRateSheetDetails` (
  `RateSheetDetailsID` int(11) NOT NULL AUTO_INCREMENT,
  `RateID` int(11) NOT NULL,
  `RateSheetID` int(11) NOT NULL,
  `Destination` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Code` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Rate` decimal(18,6) NOT NULL DEFAULT '0.000000',
  `Change` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `EffectiveDate` datetime NOT NULL,
  `Interval1` int(11) DEFAULT NULL,
  `IntervalN` int(11) DEFAULT NULL,
  PRIMARY KEY (`RateSheetDetailsID`),
  KEY `IX_tblRateSheetDetails_RateSheetID_DBEE5` (`RateSheetID`,`RateSheetDetailsID`),
  KEY `IX_tblRateSheetDetails_RateSheetID_77B8B` (`RateSheetID`,`RateSheetDetailsID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci