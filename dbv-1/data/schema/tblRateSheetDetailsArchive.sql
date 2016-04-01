CREATE TABLE `tblRateSheetDetailsArchive` (
  `RateSheetDetailsID` int(11) NOT NULL AUTO_INCREMENT,
  `RateID` int(11) NOT NULL,
  `RateSheetID` int(11) NOT NULL,
  `Destination` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Code` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Rate` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `Change` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `EffectiveDate` datetime NOT NULL,
  PRIMARY KEY (`RateSheetDetailsID`),
  KEY `FK_tblRateSheetDetailsArchive_tblRateSheetArchive` (`RateSheetID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci