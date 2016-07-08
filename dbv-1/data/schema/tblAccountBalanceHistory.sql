CREATE TABLE `tblAccountBalanceHistory` (
  `AccountBalanceHistoryID` int(11) NOT NULL AUTO_INCREMENT,
  `AccountID` int(11) NOT NULL DEFAULT '0',
  `PermanentCredit` decimal(18,6) DEFAULT NULL,
  `TemporaryCredit` decimal(18,6) DEFAULT NULL,
  `BalanceThreshold` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`AccountBalanceHistoryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci