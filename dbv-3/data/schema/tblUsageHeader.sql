CREATE TABLE `tblUsageHeader` (
  `UsageHeaderID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `CompanyGatewayID` int(11) DEFAULT NULL,
  `GatewayAccountID` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `StartDate` datetime DEFAULT NULL,
  `DailySummaryStatus` tinyint(3) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `VendorCDRStatus` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`UsageHeaderID`),
  KEY `Index_Com_GA_CG_A` (`CompanyID`,`GatewayAccountID`,`CompanyGatewayID`,`AccountID`),
  KEY `Index_A_STD_CG` (`AccountID`,`StartDate`,`CompanyGatewayID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci