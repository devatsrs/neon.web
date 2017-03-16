CREATE TABLE `tblUsageVendorSummary` (
  `UsageVendorSummaryID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `SummaryVendorHeaderID` bigint(20) unsigned NOT NULL,
  `TotalCharges` double DEFAULT NULL,
  `TotalSales` double DEFAULT NULL,
  `TotalBilledDuration` int(11) DEFAULT NULL,
  `TotalDuration` int(11) DEFAULT NULL,
  `NoOfCalls` int(11) DEFAULT NULL,
  `NoOfFailCalls` int(11) DEFAULT NULL,
  `FinalStatus` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`UsageVendorSummaryID`),
  KEY `FK_tblUsageVendorSummary_dim_date` (`SummaryVendorHeaderID`),
  CONSTRAINT `FK_tblUsageVendorSummary_tblSummaryVendorHeader` FOREIGN KEY (`SummaryVendorHeaderID`) REFERENCES `tblSummaryVendorHeader` (`SummaryVendorHeaderID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci