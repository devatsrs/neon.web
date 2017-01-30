CREATE TABLE `tblCLIRateTable` (
  `CLIRateTableID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `CLI` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `RateTableID` int(11) NOT NULL,
  PRIMARY KEY (`CLIRateTableID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci