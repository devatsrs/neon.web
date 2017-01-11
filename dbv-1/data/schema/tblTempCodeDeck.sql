CREATE TABLE `tblTempCodeDeck` (
  `TempCodeDeckRateID` int(11) NOT NULL AUTO_INCREMENT,
  `CountryId` int(11) DEFAULT NULL,
  `CompanyId` int(11) NOT NULL,
  `CodeDeckId` int(11) DEFAULT NULL,
  `ProcessId` bigint(20) unsigned NOT NULL,
  `Code` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Description` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Country` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Action` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Interval1` int(11) DEFAULT NULL,
  `IntervalN` int(11) DEFAULT NULL,
  PRIMARY KEY (`TempCodeDeckRateID`),
  KEY `PK_tblTempCodeDeck` (`ProcessId`,`Code`,`CompanyId`,`CodeDeckId`),
  KEY `PK_tblTempCdprocess` (`ProcessId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci