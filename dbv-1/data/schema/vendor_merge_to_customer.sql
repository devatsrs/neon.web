CREATE TABLE `vendor_merge_to_customer` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Customer` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Vendor` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Status` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci