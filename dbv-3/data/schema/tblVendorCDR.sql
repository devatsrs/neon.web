CREATE TABLE `tblVendorCDR` (
  `VendorCDRID` int(11) NOT NULL AUTO_INCREMENT,
  `VendorCDRHeaderID` int(11) NOT NULL,
  `connect_time` datetime DEFAULT NULL,
  `disconnect_time` datetime DEFAULT NULL,
  `billed_duration` int(11) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `ID` int(11) DEFAULT NULL,
  `selling_cost` double DEFAULT NULL,
  `buying_cost` double DEFAULT NULL,
  `cli` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cld` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `trunk` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `area_prefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `remote_ip` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ProcessID` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`VendorCDRID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci