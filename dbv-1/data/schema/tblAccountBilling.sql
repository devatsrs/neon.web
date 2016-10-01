CREATE TABLE `tblAccountBilling` (
  `AccountBillingID` int(11) NOT NULL AUTO_INCREMENT,
  `AccountID` int(11) NOT NULL DEFAULT '0',
  `BillingType` tinyint(3) unsigned DEFAULT NULL,
  `TaxRateId` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `BillingTimezone` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `BillingCycleType` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `BillingCycleValue` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `SendInvoiceSetting` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `BillingStartDate` date DEFAULT NULL,
  `LastInvoiceDate` date DEFAULT NULL,
  `NextInvoiceDate` date DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`AccountBillingID`),
  UNIQUE KEY `AccountID` (`AccountID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci