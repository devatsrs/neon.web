CREATE TABLE `tblInvoiceHistory` (
  `InvoiceHistoryID` int(11) NOT NULL AUTO_INCREMENT,
  `InvoiceID` int(11) DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `BillingType` tinyint(3) unsigned DEFAULT NULL,
  `BillingTimezone` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `BillingCycleType` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `BillingCycleValue` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `BillingStartDate` date DEFAULT NULL,
  `LastInvoiceDate` date DEFAULT NULL,
  `NextInvoiceDate` date DEFAULT NULL,
  `LastChargeDate` date DEFAULT NULL,
  `NextChargeDate` date DEFAULT NULL,
  `ServiceID` int(11) DEFAULT '0',
  `IssueDate` date DEFAULT NULL,
  `FirstInvoice` int(11) DEFAULT '0',
  PRIMARY KEY (`InvoiceHistoryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci