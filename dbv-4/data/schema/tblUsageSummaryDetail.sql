CREATE TABLE `tblUsageSummaryDetail` (
  `UsageSummaryDetailID` bigint(20) NOT NULL AUTO_INCREMENT,
  `SummaryHeaderID` bigint(20) NOT NULL,
  `TimeID` int(11) NOT NULL,
  `TotalCharges` double DEFAULT NULL,
  `TotalBilledDuration` int(11) DEFAULT NULL,
  `TotalDuration` int(11) DEFAULT NULL,
  `NoOfCalls` int(11) DEFAULT NULL,
  `ACD` int(11) DEFAULT NULL,
  `ASR` int(11) DEFAULT NULL,
  `FinalStatus` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`UsageSummaryDetailID`),
  KEY `FK_tblUsageSummaryDetail_dim_time` (`TimeID`),
  KEY `FK_tblUsageSummaryDetail_tblSummaryHeader` (`SummaryHeaderID`),
  CONSTRAINT `FK_tblUsageSummaryDetail_tblDimTime` FOREIGN KEY (`TimeID`) REFERENCES `tblDimTime` (`TimeID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_tblUsageSummaryDetail_tblSummaryHeader` FOREIGN KEY (`SummaryHeaderID`) REFERENCES `tblSummaryHeader` (`SummaryHeaderID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci