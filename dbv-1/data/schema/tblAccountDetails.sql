CREATE TABLE `tblAccountDetails` (
  `AccountDetailID` int(11) NOT NULL AUTO_INCREMENT,
  `AccountID` int(11) NOT NULL,
  `CustomerPaymentAdd` int(11) DEFAULT '0',
  `customerpanelpassword` longtext COLLATE utf8_unicode_ci,
  `DisplayRates` int(11) DEFAULT '0',
  `ResellerOwner` int(11) DEFAULT '0',
  PRIMARY KEY (`AccountDetailID`),
  UNIQUE KEY `UI_AccountID` (`AccountID`),
  KEY `IX_AccountID` (`AccountID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci