CREATE TABLE `tblReportSchedule` (
  `ReportScheduleID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `Name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ReportID` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Settings` longtext COLLATE utf8_unicode_ci,
  `Status` tinyint(4) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `UpdatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ReportScheduleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci