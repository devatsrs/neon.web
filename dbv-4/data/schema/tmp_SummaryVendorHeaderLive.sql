CREATE TABLE `tmp_SummaryVendorHeaderLive` (
  `HeaderVID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `DateID` bigint(20) NOT NULL,
  `CompanyID` int(11) DEFAULT NULL,
  `VAccountID` int(11) DEFAULT NULL,
  PRIMARY KEY (`HeaderVID`),
  KEY `Unique_key` (`DateID`,`CompanyID`,`VAccountID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci