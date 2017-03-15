CREATE TABLE `tblUsageSummaryLive` (
  `UsageSummaryLiveID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `SummaryHeaderID` bigint(20) unsigned NOT NULL,
  `TotalCharges` double DEFAULT NULL,
  `TotalBilledDuration` int(11) DEFAULT NULL,
  `TotalDuration` int(11) DEFAULT NULL,
  `NoOfCalls` int(11) DEFAULT NULL,
  `NoOfFailCalls` int(11) DEFAULT NULL,
  `FinalStatus` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`UsageSummaryLiveID`),
  KEY `FK_tblUsageSummaryLive_dim_date` (`SummaryHeaderID`),
  CONSTRAINT `FK_tblUsageSummaryLive_tblSummaryHeader` FOREIGN KEY (`SummaryHeaderID`) REFERENCES `tblSummaryHeader` (`SummaryHeaderID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci