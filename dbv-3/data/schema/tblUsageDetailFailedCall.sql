CREATE TABLE `tblUsageDetailFailedCall` (
  `UsageDetailFailedCallID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `UsageHeaderID` int(11) NOT NULL,
  `connect_time` datetime DEFAULT NULL,
  `disconnect_time` datetime DEFAULT NULL,
  `billed_duration` int(11) DEFAULT NULL,
  `area_prefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pincode` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `extension` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cli` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cld` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cost` double DEFAULT NULL,
  `remote_ip` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `trunk` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ProcessID` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ID` int(11) DEFAULT NULL,
  `is_inbound` tinyint(1) DEFAULT '0',
  `billed_second` int(11) DEFAULT NULL,
  `disposition` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `userfield` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`UsageDetailFailedCallID`),
  KEY `IX_ID` (`ID`),
  KEY `IX_UsageHeaderID` (`UsageHeaderID`),
  KEY `IX_ProcessID` (`ProcessID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci