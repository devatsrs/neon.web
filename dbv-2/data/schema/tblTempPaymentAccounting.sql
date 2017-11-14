CREATE TABLE `tblTempPaymentAccounting` (
  `PaymentID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `ProcessID` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `AccountName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AccountNumber` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PaymentDate` datetime NOT NULL,
  `PaymentMethod` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PaymentType` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Notes` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Amount` decimal(18,8) NOT NULL,
  `Status` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TransactionID` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CurrencyID` int(11) DEFAULT NULL,
  `InvoiceID` int(11) DEFAULT NULL,
  `InvoiceNumber` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`PaymentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT