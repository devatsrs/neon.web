CREATE TABLE `tblAccountBalance` (
  `AccountBalanceID` int(11) NOT NULL AUTO_INCREMENT,
  `AccountID` int(11) NOT NULL DEFAULT '0',
  `PermanentCredit` decimal(18,6) NOT NULL,
  `CurrentCredit` decimal(18,6) NOT NULL,
  `BalanceAmount` decimal(18,6) NOT NULL,
  `TemporaryCredit` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `TemporaryCreditDateTime` datetime NOT NULL,
  `BalanceThreshold` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`AccountBalanceID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci