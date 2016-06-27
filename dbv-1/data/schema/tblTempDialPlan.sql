CREATE TABLE `tblTempDialPlan` (
  `TempDialPlanID` int(11) NOT NULL AUTO_INCREMENT,
  `DialPlanID` int(11) NOT NULL,
  `DialString` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `ChargeCode` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `Forbidden` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ProcessId` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Action` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`TempDialPlanID`),
  KEY `PK_tblTempDialPlanprocess` (`ProcessId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci