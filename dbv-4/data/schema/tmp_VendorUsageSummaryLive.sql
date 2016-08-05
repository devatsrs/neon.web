CREATE TABLE `tmp_VendorUsageSummaryLive` (
  `DateID` bigint(20) NOT NULL,
  `TimeID` int(11) NOT NULL,
  `CompanyID` int(11) NOT NULL,
  `CompanyGatewayID` int(11) NOT NULL,
  `GatewayAccountID` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AccountID` int(11) NOT NULL,
  `Trunk` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AreaPrefix` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TotalCharges` double DEFAULT NULL,
  `TotalSales` double DEFAULT NULL,
  `TotalBilledDuration` int(11) DEFAULT NULL,
  `TotalDuration` int(11) DEFAULT NULL,
  `NoOfCalls` int(11) DEFAULT '0',
  `NoOfFailCalls` int(11) DEFAULT '0',
  `FinalStatus` int(11) DEFAULT '0',
  `CountryID` int(11) DEFAULT NULL,
  KEY `tmp_VendorUsageSummary_dim_date` (`DateID`),
  KEY `tmp_VendorUsageSummary_AreaPrefix` (`AreaPrefix`),
  KEY `Unique_key` (`DateID`,`CompanyID`,`AccountID`,`GatewayAccountID`,`CompanyGatewayID`,`Trunk`,`AreaPrefix`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci