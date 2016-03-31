CREATE TABLE `tblBillingSettings` (
  `BillingSettingID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `InvoiceNumberSequence` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `InvoiceStartNumber` int(11) DEFAULT NULL,
  PRIMARY KEY (`BillingSettingID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci