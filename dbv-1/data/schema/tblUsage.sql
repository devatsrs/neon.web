CREATE TABLE `tblUsage` (
  `PKUsage` bigint(20) NOT NULL AUTO_INCREMENT,
  `AccountID` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AccountName` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AreaPrefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AreaName` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TotalCharges` decimal(18,3) DEFAULT '0.000',
  `TotalDuration` int(11) DEFAULT NULL,
  `NoOfCDR` int(11) DEFAULT NULL,
  `Minutes` decimal(18,3) DEFAULT NULL,
  `AVGRatePerMin` decimal(18,6) DEFAULT NULL,
  `PeriodFrom` datetime DEFAULT NULL,
  `PeriodTo` datetime DEFAULT NULL,
  `InvoiceCompanyID` int(11) DEFAULT NULL,
  PRIMARY KEY (`PKUsage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci