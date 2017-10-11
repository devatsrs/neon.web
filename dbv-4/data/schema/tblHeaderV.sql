CREATE TABLE `tblHeaderV` (
  `HeaderVID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `DateID` bigint(20) NOT NULL,
  `CompanyID` int(11) DEFAULT NULL,
  `VAccountID` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `TotalCharges` double DEFAULT NULL,
  `TotalSales` double DEFAULT NULL,
  `TotalBilledDuration` int(11) DEFAULT NULL,
  `TotalDuration` int(11) DEFAULT NULL,
  `NoOfCalls` int(11) DEFAULT NULL,
  `NoOfFailCalls` int(11) DEFAULT NULL,
  PRIMARY KEY (`HeaderVID`),
  UNIQUE KEY `Unique_key` (`DateID`,`VAccountID`),
  KEY `FK_tblHeaderV_dim_date` (`DateID`),
  KEY `Index 4` (`CompanyID`),
  CONSTRAINT `tblHeader_ibfk_2` FOREIGN KEY (`DateID`) REFERENCES `tblDimDate` (`DateID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci