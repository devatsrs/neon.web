CREATE TABLE `tblAccountBalance` (
  `AccountBalanceID` int(11) NOT NULL AUTO_INCREMENT,
  `AccountID` int(11) NOT NULL DEFAULT '0',
  `PermanentCredit` decimal(18,6) NOT NULL,
  `CreditUsed` decimal(18,6) NOT NULL,
  `TemporaryCredit` decimal(18,6) DEFAULT NULL,
  `TemporaryCreditDateTime` datetime DEFAULT NULL,
  `BalanceThreshold` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `BalanceAmount` decimal(18,6) NOT NULL,
  PRIMARY KEY (`AccountBalanceID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci