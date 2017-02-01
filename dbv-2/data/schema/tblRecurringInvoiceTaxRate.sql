CREATE TABLE `tblRecurringInvoiceTaxRate` (
  `RecurringInvoiceTaxRateID` int(11) NOT NULL AUTO_INCREMENT,
  `RecurringInvoiceID` int(11) NOT NULL,
  `TaxRateID` int(11) NOT NULL,
  `TaxAmount` decimal(18,6) NOT NULL,
  `Title` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `RecurringInvoiceTaxType` tinyint(4) NOT NULL DEFAULT '0',
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`RecurringInvoiceTaxRateID`),
  UNIQUE KEY `RecurringInvoiceTaxRateUnique` (`RecurringInvoiceID`,`TaxRateID`,`RecurringInvoiceTaxType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci