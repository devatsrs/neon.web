CREATE TABLE `tblDialStringCode` (
  `DialStringCodeID` int(11) NOT NULL AUTO_INCREMENT,
  `DialStringID` int(11) NOT NULL,
  `DialString` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `ChargeCode` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Forbidden` tinyint(1) NOT NULL DEFAULT '0',
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`DialStringCodeID`),
  UNIQUE KEY `IXUnique_DialStringID_DialString` (`DialStringID`,`DialString`),
  KEY `IX_tblDialStringCode_DialStringID` (`DialStringID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci