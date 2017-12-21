CREATE TABLE `tblReport` (
  `ReportID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `Name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Settings` longtext COLLATE utf8_unicode_ci,
  `Type` tinyint(4) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `UpdatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Schedule` int(11) DEFAULT '0',
  `ScheduleSettings` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`ReportID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci