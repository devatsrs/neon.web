CREATE TABLE `tblTempAccountIP` (
  `tblTempAccountIPID` bigint(20) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `AccountName` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `IP` longtext COLLATE utf8_unicode_ci,
  `Type` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ProcessID` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ServiceID` int(11) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`tblTempAccountIPID`),
  KEY `IX_ProcessID` (`ProcessID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci