CREATE TABLE `tmp_UsageSummaryLive` (
  `UsageSummaryLiveID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `DateID` bigint(20) NOT NULL,
  `TimeID` int(11) NOT NULL,
  `CompanyID` int(11) NOT NULL,
  `CompanyGatewayID` int(11) NOT NULL,
  `GatewayAccountID` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AccountID` int(11) NOT NULL,
  `Trunk` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AreaPrefix` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TotalCharges` double DEFAULT NULL,
  `TotalBilledDuration` int(11) DEFAULT NULL,
  `TotalDuration` int(11) DEFAULT NULL,
  `NoOfCalls` int(11) DEFAULT '0',
  `NoOfFailCalls` int(11) DEFAULT '0',
  `FinalStatus` int(11) DEFAULT '0',
  `CountryID` int(11) DEFAULT NULL,
  `ServiceID` int(11) DEFAULT NULL,
  PRIMARY KEY (`UsageSummaryLiveID`),
  KEY `tblUsageSummary_dim_date` (`DateID`),
  KEY `tmp_UsageSummary_AreaPrefix` (`AreaPrefix`),
  KEY `Unique_key` (`DateID`,`CompanyID`,`AccountID`,`CompanyGatewayID`,`Trunk`,`AreaPrefix`),
  KEY `IX_CompanyID` (`CompanyID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci