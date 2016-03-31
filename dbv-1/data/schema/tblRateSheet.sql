CREATE TABLE `tblRateSheet` (
  `RateSheetID` int(11) NOT NULL AUTO_INCREMENT,
  `CustomerID` int(11) NOT NULL,
  `RateSheet` longblob,
  `DateGenerated` datetime NOT NULL,
  `FileName` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Level` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `GeneratedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`RateSheetID`),
  KEY `IX_tblRateSheet_CustomerID_Level_69B7F` (`CustomerID`,`Level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci