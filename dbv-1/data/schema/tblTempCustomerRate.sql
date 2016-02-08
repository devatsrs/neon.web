CREATE TABLE `tblTempCustomerRate` (
  `TempCustomerRateID` int(11) NOT NULL AUTO_INCREMENT,
  `Select` tinyint(1) DEFAULT '0',
  `RateID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `Rate` decimal(18,4) DEFAULT '0.0000',
  `EffectiveDate` date DEFAULT NULL,
  `PreviousRate` decimal(18,4) DEFAULT '0.0000',
  `Trunk` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`TempCustomerRateID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci