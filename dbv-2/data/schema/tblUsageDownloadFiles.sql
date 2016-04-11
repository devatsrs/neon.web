CREATE TABLE `tblUsageDownloadFiles` (
  `UsageDownloadFilesID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyGatewayID` int(11) NOT NULL DEFAULT '0',
  `filename` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `UpdatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Status` int(11) DEFAULT '1' COMMENT '1=pending;2=progress;3=completed;4=error;',
  `processcount` int(11) DEFAULT '0',
  `process_at` datetime DEFAULT NULL,
  `message` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`UsageDownloadFilesID`),
  UNIQUE KEY `IX_gateway_filename` (`CompanyGatewayID`,`filename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci