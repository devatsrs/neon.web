CREATE TABLE `tblAccountBalance` (
  `AccountBalanceID` int(11) NOT NULL AUTO_INCREMENT,
  `AccountID` int(11) NOT NULL DEFAULT '0',
  `PermanentCredit` decimal(18,6) DEFAULT NULL,
  `CreditUsed` decimal(18,6) DEFAULT NULL,
  `TemporaryCredit` decimal(18,6) DEFAULT NULL,
  `TemporaryCreditDateTime` datetime DEFAULT NULL,
  `BalanceThreshold` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `BalanceAmount` decimal(18,6) DEFAULT NULL,
  `EmailToCustomer` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`AccountBalanceID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci