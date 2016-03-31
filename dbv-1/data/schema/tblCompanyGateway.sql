CREATE TABLE `tblCompanyGateway` (
  `CompanyGatewayID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `GatewayID` int(11) DEFAULT NULL,
  `Title` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `IP` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Settings` longtext COLLATE utf8_unicode_ci,
  `Status` tinyint(3) unsigned DEFAULT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `TimeZone` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `BillingTime` tinyint(3) unsigned DEFAULT NULL,
  `BillingTimeZone` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `UniqueID` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`CompanyGatewayID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci