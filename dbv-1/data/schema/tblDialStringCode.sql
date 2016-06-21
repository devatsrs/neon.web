CREATE TABLE `tblDialStringCode` (
  `DialStringCodeID` int(11) NOT NULL AUTO_INCREMENT,
  `DialStringID` int(11) NOT NULL,
  `DialString` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `ChargeCode` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `Forbidden` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`DialStringCodeID`),
  UNIQUE KEY `IXUnique_DialStringID_DialString_ChargeCode` (`DialStringID`,`DialString`,`ChargeCode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci