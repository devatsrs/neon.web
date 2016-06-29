CREATE TABLE `tblDialPlanCode` (
  `DialPlanCodeID` int(11) NOT NULL AUTO_INCREMENT,
  `DialPlanID` int(11) NOT NULL,
  `DialString` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `ChargeCode` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `Forbidden` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`DialPlanCodeID`),
  UNIQUE KEY `IXUnique_DialPlanID_DialString_ChargeCode` (`DialPlanID`,`DialString`,`ChargeCode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci