CREATE TABLE `tblVendorTrunk` (
  `VendorTrunkID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `CodeDeckId` int(11) DEFAULT NULL,
  `AccountID` int(11) NOT NULL,
  `TrunkID` int(11) NOT NULL,
  `Prefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CompanyGatewayIDs` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Status` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `UseInBilling` tinyint(1) unsigned zerofill DEFAULT '0',
  PRIMARY KEY (`VendorTrunkID`),
  KEY `IX_AccountID_TrunkID_Status` (`AccountID`,`TrunkID`,`Status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci