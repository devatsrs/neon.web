CREATE TABLE `tmp_tblVendorUsageDetailsReportLive` (
  `VendorCDRID` int(11) DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `CompanyID` int(11) DEFAULT NULL,
  `CompanyGatewayID` int(11) DEFAULT NULL,
  `GatewayAccountID` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `trunk` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `area_prefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `billed_duration` int(11) DEFAULT NULL,
  `buying_cost` decimal(18,6) DEFAULT NULL,
  `selling_cost` decimal(18,6) DEFAULT NULL,
  `connect_time` time DEFAULT NULL,
  `connect_date` date DEFAULT NULL,
  `call_status` tinyint(4) DEFAULT NULL,
  KEY `temp_connect_time` (`connect_time`,`connect_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci