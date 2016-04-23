CREATE TABLE `tblUsageSummary` (
  `UsageSummaryID` bigint(20) NOT NULL AUTO_INCREMENT,
  `date_id` bigint(20) NOT NULL,
  `time_id` int(11) NOT NULL,
  `CompanyID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `GatewayAccountID` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CompanyGatewayID` int(11) NOT NULL,
  `Trunk` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AreaPrefix` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Country` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TotalCharges` double DEFAULT NULL,
  `TotalBilledDuration` int(11) DEFAULT NULL,
  `TotalDuration` int(11) DEFAULT NULL,
  `NoOfCalls` int(11) DEFAULT NULL,
  `FinalStatus` int(11) DEFAULT '0',
  PRIMARY KEY (`UsageSummaryID`),
  KEY `FK_tblUsageSummary_dim_date` (`date_id`),
  KEY `FK_tblUsageSummary_dim_time` (`time_id`),
  CONSTRAINT `FK_tblUsageSummary_dim_date` FOREIGN KEY (`date_id`) REFERENCES `tblDimDate` (`date_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_tblUsageSummary_dim_time` FOREIGN KEY (`time_id`) REFERENCES `tblDimTtime` (`time_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci