CREATE TABLE `tblVendorCDRHeader` (
  `VendorCDRHeaderID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `CompanyGatewayID` int(11) DEFAULT NULL,
  `GatewayAccountID` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `StartDate` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`VendorCDRHeaderID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci