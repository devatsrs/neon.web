CREATE TABLE `tblVendorPreference` (
  `VendorPreferenceID` int(11) NOT NULL AUTO_INCREMENT,
  `AccountId` int(11) NOT NULL,
  `Preference` int(11) NOT NULL,
  `RateId` int(11) DEFAULT NULL,
  `TrunkID` int(11) NOT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`VendorPreferenceID`),
  UNIQUE KEY `IX_UniqueAccountId_Pref_RateId_TrunkId` (`Preference`,`RateId`,`TrunkID`,`AccountId`),
  KEY `IX_AccountID_TrunkID_RateID` (`TrunkID`,`RateId`,`AccountId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci