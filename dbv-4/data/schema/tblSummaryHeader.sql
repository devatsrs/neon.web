CREATE TABLE `tblSummaryHeader` (
  `SummaryHeaderID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `DateID` bigint(20) NOT NULL,
  `CompanyID` int(11) DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `GatewayAccountID` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CompanyGatewayID` int(11) DEFAULT NULL,
  `Trunk` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AreaPrefix` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CountryID` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `ServiceID` int(11) DEFAULT '0',
  PRIMARY KEY (`SummaryHeaderID`),
  KEY `FK_tblSummaryHeader_dim_date` (`DateID`),
  KEY `Unique_key` (`DateID`,`CompanyID`,`AccountID`,`GatewayAccountID`,`CompanyGatewayID`,`Trunk`,`AreaPrefix`),
  CONSTRAINT `tblSummaryHeader_ibfk_1` FOREIGN KEY (`DateID`) REFERENCES `tblDimDate` (`DateID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci