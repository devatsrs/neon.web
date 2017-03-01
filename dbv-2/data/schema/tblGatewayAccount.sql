CREATE TABLE `tblGatewayAccount` (
  `GatewayAccountPKID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `CompanyGatewayID` int(11) NOT NULL,
  `GatewayAccountID` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AccountName` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `AccountDetailInfo` longtext COLLATE utf8_unicode_ci,
  `IsVendor` tinyint(3) unsigned DEFAULT NULL,
  `GatewayVendorID` int(11) DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `AccountIP` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`GatewayAccountPKID`),
  KEY `IX_tblGatewayAccount_GatewayAccountID_AccountName_5F8A5` (`GatewayAccountID`,`AccountName`,`CompanyGatewayID`),
  KEY `IX_tblGatewayAccount_AccountID_63248` (`AccountID`,`GatewayAccountID`),
  KEY `IX_tblGatewayAccount_AccountID_CDCF2` (`AccountID`),
  KEY `IX_CID_CGID_GAID_AID` (`CompanyID`,`CompanyGatewayID`,`GatewayAccountID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci