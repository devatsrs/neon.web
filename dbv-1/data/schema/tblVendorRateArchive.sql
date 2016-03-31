CREATE TABLE `tblVendorRateArchive` (
  `VendorRateArchiveID` int(11) NOT NULL AUTO_INCREMENT,
  `VendorRateID` int(11) NOT NULL,
  `AccountId` int(11) NOT NULL,
  `TrunkId` int(11) NOT NULL,
  `RateId` int(11) NOT NULL,
  `Rate` decimal(18,6) NOT NULL,
  `EffectiveDate` datetime NOT NULL,
  `CreatedDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `Notes` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`VendorRateArchiveID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci