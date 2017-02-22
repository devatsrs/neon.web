CREATE TABLE `tmp_tblUsageDetailsReport` (
  `UsageDetailsReportID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `UsageDetailID` bigint(20) unsigned DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `CompanyID` int(11) DEFAULT NULL,
  `CompanyGatewayID` int(11) DEFAULT NULL,
  `GatewayAccountID` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `trunk` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `area_prefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `billed_duration` int(11) DEFAULT NULL,
  `cost` decimal(18,6) DEFAULT NULL,
  `connect_time` time DEFAULT NULL,
  `connect_date` date DEFAULT NULL,
  `call_status` tinyint(4) DEFAULT NULL,
  `ServiceID` int(11) DEFAULT NULL,
  PRIMARY KEY (`UsageDetailsReportID`),
  KEY `temp_connect_time` (`connect_time`,`connect_date`),
  KEY `IX_CompanyID` (`CompanyID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci