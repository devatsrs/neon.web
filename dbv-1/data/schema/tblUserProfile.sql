CREATE TABLE `tblUserProfile` (
  `UserProfileID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `City` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `State` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PostCode` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Country` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Address1` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Address2` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Address3` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Picture` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Utc` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`UserProfileID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci