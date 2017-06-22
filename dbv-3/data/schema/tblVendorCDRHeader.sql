CREATE TABLE `tblVendorCDRHeader` (
  `VendorCDRHeaderID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `CompanyGatewayID` int(11) DEFAULT NULL,
  `GatewayAccountID` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `StartDate` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `ServiceID` int(11) DEFAULT '0',
  `GatewayAccountPKID` int(11) DEFAULT NULL,
  PRIMARY KEY (`VendorCDRHeaderID`),
  KEY `Index_C_CG_A_GA` (`CompanyID`,`CompanyGatewayID`,`GatewayAccountID`,`AccountID`),
  KEY `Index_A_S_CG` (`AccountID`,`StartDate`,`CompanyGatewayID`),
  KEY `IX_GAID` (`GatewayAccountPKID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci