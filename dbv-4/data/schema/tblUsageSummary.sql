CREATE TABLE `tblUsageSummary` (
  `UsageSummaryID` bigint(20) NOT NULL AUTO_INCREMENT,
  `SummaryHeaderID` bigint(20) NOT NULL,
  `TotalCharges` double DEFAULT NULL,
  `TotalBilledDuration` int(11) DEFAULT NULL,
  `TotalDuration` int(11) DEFAULT NULL,
  `NoOfCalls` int(11) DEFAULT NULL,
  `ACD` int(11) DEFAULT NULL,
  `ASR` int(11) DEFAULT NULL,
  `FinalStatus` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`UsageSummaryID`),
  KEY `FK_tblUsageSummary_dim_date` (`SummaryHeaderID`),
  CONSTRAINT `FK_tblUsageSummary_tblSummaryHeader` FOREIGN KEY (`SummaryHeaderID`) REFERENCES `tblSummaryHeader` (`SummaryHeaderID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci