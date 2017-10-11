CREATE TABLE `tblVendorSummaryHourLive` (
  `VendorSummaryHourLiveID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `HeaderVID` bigint(20) unsigned NOT NULL,
  `TimeID` int(11) NOT NULL,
  `TotalCharges` double DEFAULT NULL,
  `TotalSales` double DEFAULT NULL,
  `TotalBilledDuration` int(11) DEFAULT NULL,
  `TotalDuration` int(11) DEFAULT NULL,
  `NoOfCalls` int(11) DEFAULT NULL,
  `NoOfFailCalls` int(11) DEFAULT NULL,
  `CompanyGatewayID` int(11) DEFAULT NULL,
  `ServiceID` int(11) DEFAULT NULL,
  `GatewayAccountPKID` int(11) DEFAULT NULL,
  `GatewayVAccountPKID` int(11) DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `Trunk` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AreaPrefix` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CountryID` int(11) DEFAULT NULL,
  PRIMARY KEY (`VendorSummaryHourLiveID`),
  KEY `FK_tblVendorSummaryDetailNew_dim_time` (`TimeID`),
  KEY `FK_tblVendorSummaryDetailNew_tblSummaryHeader` (`HeaderVID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci