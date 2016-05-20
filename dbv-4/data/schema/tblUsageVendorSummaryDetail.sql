CREATE TABLE `tblUsageVendorSummaryDetail` (
  `UsageVendorSummaryDetailID` bigint(20) NOT NULL AUTO_INCREMENT,
  `SummaryVendorHeaderID` bigint(20) NOT NULL,
  `TimeID` int(11) NOT NULL,
  `TotalCharges` double DEFAULT NULL,
  `TotalSales` double DEFAULT NULL,
  `TotalBilledDuration` int(11) DEFAULT NULL,
  `TotalDuration` int(11) DEFAULT NULL,
  `NoOfCalls` int(11) DEFAULT NULL,
  `NoOfFailCalls` int(11) DEFAULT NULL,
  `FinalStatus` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`UsageVendorSummaryDetailID`),
  KEY `FK_tblUsageVendorSummaryDetail_dim_time` (`TimeID`),
  KEY `FK_tblUsageVendorSummaryDetail_tblSummaryVendorHeader` (`SummaryVendorHeaderID`),
  CONSTRAINT `FK_tblUsageVendorSummaryDetail_tblDimTime` FOREIGN KEY (`TimeID`) REFERENCES `tblDimTime` (`TimeID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_tblUsageVendorSummaryDetail_tblSummaryVendorHeader` FOREIGN KEY (`SummaryVendorHeaderID`) REFERENCES `tblUsageVendorSummary` (`SummaryVendorHeaderID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci