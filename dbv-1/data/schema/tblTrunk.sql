CREATE TABLE `tblTrunk` (
  `TrunkID` int(11) NOT NULL AUTO_INCREMENT,
  `Trunk` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `CompanyId` int(11) NOT NULL,
  `RatePrefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AreaPrefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Prefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Status` tinyint(1) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`TrunkID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci