CREATE TABLE `tblSummeryData` (
  `SummeryDataID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `IP` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `GatewayAccountID` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `Gateway` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `AccountName` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `AreaPrefix` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `AreaName` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `Country` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TotalCalls` int(11) NOT NULL,
  `Duration` decimal(18,2) NOT NULL,
  `TotalCharge` decimal(18,6) NOT NULL,
  `ProcessID` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  PRIMARY KEY (`SummeryDataID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci