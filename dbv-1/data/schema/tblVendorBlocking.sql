CREATE TABLE `tblVendorBlocking` (
  `VendorBlockingId` int(11) NOT NULL AUTO_INCREMENT,
  `AccountId` int(11) NOT NULL,
  `CountryId` int(11) DEFAULT NULL,
  `RateId` int(11) DEFAULT NULL,
  `TrunkID` int(11) NOT NULL,
  `BlockedBy` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `BlockedDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`VendorBlockingId`),
  KEY `IX_tblVendorBlocking_AccountId` (`AccountId`),
  KEY `IX_tblVendorBlocking_CountryId` (`CountryId`),
  KEY `IX_tblVendorBlocking_RateId` (`RateId`),
  KEY `IX_tblVendorBlocking_TrunkID` (`TrunkID`),
  KEY `IX_tblVendorBlocking_CountryId_TrunkID` (`AccountId`,`CountryId`,`TrunkID`),
  KEY `IX_tblVendorBlocking_TrunkID_4F42B` (`TrunkID`,`VendorBlockingId`,`RateId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci