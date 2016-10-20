CREATE TABLE `tblAlert` (
  `AlertID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `Name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AlertType` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AlertGroup` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Status` tinyint(4) DEFAULT NULL,
  `LowValue` int(11) DEFAULT NULL,
  `HighValue` int(11) DEFAULT NULL,
  `Settings` text COLLATE utf8_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `UpdatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`AlertID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci