CREATE TABLE `tblTempDialString` (
  `TempDialStringID` int(11) NOT NULL AUTO_INCREMENT,
  `DialStringID` int(11) NOT NULL,
  `DialString` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `ChargeCode` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `Forbidden` tinyint(1) NOT NULL DEFAULT '0',
  `ProcessId` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Action` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`TempDialStringID`),
  KEY `PK_tblTempDialPlanprocess` (`ProcessId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci