CREATE TABLE `tblCallDetail` (
  `CallDetailID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `GCID` bigint(20) unsigned DEFAULT NULL,
  `CID` bigint(20) DEFAULT NULL,
  `VCID` bigint(20) DEFAULT NULL,
  `UsageHeaderID` int(11) DEFAULT NULL,
  `VendorCDRHeaderID` int(11) DEFAULT NULL,
  `CompanyGatewayID` int(11) DEFAULT NULL,
  `GatewayAccountPKID` int(11) DEFAULT NULL,
  `GatewayVAccountPKID` int(11) DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `VAccountID` int(11) DEFAULT NULL,
  `FailCall` tinyint(4) DEFAULT NULL,
  `FailCallV` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`CallDetailID`),
  KEY `IX_GCID` (`GCID`),
  KEY `IX_CID` (`CID`),
  KEY `IX_VCID` (`VCID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci