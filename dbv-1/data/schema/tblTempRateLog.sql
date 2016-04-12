CREATE TABLE `tblTempRateLog` (
  `TempRateLogID` bigint(20) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `CompanyGatewayID` int(11) DEFAULT NULL,
  `MessageType` int(11) NOT NULL,
  `Message` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `SentStatus` int(11) NOT NULL,
  `RateDate` date NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`TempRateLogID`),
  UNIQUE KEY `IX_DuplicateMessage` (`CompanyID`,`CompanyGatewayID`,`MessageType`,`Message`,`RateDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci