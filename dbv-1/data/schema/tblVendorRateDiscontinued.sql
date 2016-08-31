CREATE TABLE `tblVendorRateDiscontinued` (
  `DiscontinuedID` int(11) NOT NULL AUTO_INCREMENT,
  `VendorRateID` int(11) NOT NULL,
  `AccountId` int(11) NOT NULL,
  `TrunkID` int(11) NOT NULL,
  `RateId` int(11) NOT NULL,
  `Code` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Rate` decimal(18,6) NOT NULL DEFAULT '0.000000',
  `EffectiveDate` datetime DEFAULT NULL,
  `Interval1` int(11) DEFAULT NULL,
  `IntervalN` int(11) DEFAULT NULL,
  `ConnectionFee` decimal(18,6) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`DiscontinuedID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci