CREATE TABLE `tblUsageDaily` (
  `UsageDailyID` bigint(20) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `CompanyGatewayID` int(11) DEFAULT NULL,
  `Trunk` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AreaPrefix` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TotalCharges` double DEFAULT NULL,
  `TotalBilledDuration` int(11) DEFAULT NULL,
  `TotalDuration` int(11) DEFAULT NULL,
  `NoOfCalls` int(11) DEFAULT NULL,
  `DailyDate` datetime DEFAULT NULL,
  PRIMARY KEY (`UsageDailyID`),
  KEY `IX_AccountID_ComapyGatewayID` (`AccountID`,`CompanyGatewayID`),
  KEY `IX_DailyDate` (`DailyDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci