CREATE TABLE `tblUsageHourly` (
  `UsageHourlyID` bigint(20) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `CompanyGatewayID` int(11) DEFAULT NULL,
  `GatewayAccountID` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `Trunk` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AreaPrefix` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TotalCharges` double DEFAULT '0',
  `TotalDuration` int(11) DEFAULT NULL,
  `NoOfCalls` int(11) DEFAULT NULL,
  `PeriodFrom` datetime DEFAULT NULL,
  `PeriodTo` datetime DEFAULT NULL,
  `Duration` int(11) DEFAULT NULL,
  PRIMARY KEY (`UsageHourlyID`),
  KEY `IX_tblUsageHourly_CompanyID_EC544` (`CompanyID`,`CompanyGatewayID`,`GatewayAccountID`,`AreaPrefix`,`PeriodFrom`,`PeriodTo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci