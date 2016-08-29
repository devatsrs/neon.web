CREATE TABLE `tblTempVendorRate` (
  `TempVendorRateID` int(11) NOT NULL AUTO_INCREMENT,
  `CodeDeckId` int(11) DEFAULT NULL,
  `Code` text COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Rate` decimal(18,6) NOT NULL DEFAULT '0.000000',
  `EffectiveDate` datetime NOT NULL,
  `Change` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ProcessId` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Preference` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ConnectionFee` decimal(18,6) DEFAULT NULL,
  `Interval1` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `IntervalN` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Forbidden` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`TempVendorRateID`),
  KEY `IX_tblTempVendorRateProcessID` (`ProcessId`),
  KEY `IX_tblTempVendorRate_Code_Change_ProcessId_5D43F` (`Change`,`ProcessId`,`Code`(100)),
  KEY `IX_tblTempVendorRateCodedeckCodeProcessID` (`CodeDeckId`,`ProcessId`,`Code`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci