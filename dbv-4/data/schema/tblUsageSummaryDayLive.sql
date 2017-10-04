CREATE TABLE `tblUsageSummaryDayLive` (
  `UsageSummaryDayLiveID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `HeaderID` bigint(20) unsigned NOT NULL,
  `TotalCharges` double DEFAULT NULL,
  `TotalBilledDuration` int(11) DEFAULT NULL,
  `TotalDuration` int(11) DEFAULT NULL,
  `NoOfCalls` int(11) DEFAULT NULL,
  `NoOfFailCalls` int(11) DEFAULT NULL,
  `CompanyGatewayID` int(11) DEFAULT NULL,
  `ServiceID` int(11) DEFAULT NULL,
  `GatewayAccountPKID` int(11) DEFAULT NULL,
  `GatewayVAccountPKID` int(11) DEFAULT NULL,
  `VAccountID` int(11) DEFAULT NULL,
  `Trunk` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AreaPrefix` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CountryID` int(11) DEFAULT NULL,
  PRIMARY KEY (`UsageSummaryDayLiveID`),
  KEY `FK_tblUsageSummaryNew_dim_date` (`HeaderID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci