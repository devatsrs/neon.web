CREATE TABLE `tblRateTableRateArchive` (
  `RateTableRateArchiveID` int(11) NOT NULL AUTO_INCREMENT,
  `RateTableRateID` int(11) NOT NULL,
  `RateTableId` int(11) NOT NULL,
  `RateId` int(11) NOT NULL,
  `Rate` decimal(18,6) NOT NULL,
  `EffectiveDate` datetime NOT NULL,
  `CreatedDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `Notes` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`RateTableRateArchiveID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci