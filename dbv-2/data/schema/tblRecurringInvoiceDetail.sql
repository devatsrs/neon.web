CREATE TABLE `tblRecurringInvoiceDetail` (
  `RecurringInvoiceDetailID` int(11) NOT NULL AUTO_INCREMENT,
  `RecurringInvoiceID` int(11) NOT NULL,
  `ProductID` int(11) DEFAULT NULL,
  `Description` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `Price` decimal(18,6) NOT NULL,
  `Qty` int(11) DEFAULT NULL,
  `Discount` decimal(18,2) DEFAULT NULL,
  `TaxRateID` int(11) DEFAULT NULL,
  `TaxRateID2` int(11) DEFAULT NULL,
  `TaxAmount` decimal(18,6) NOT NULL DEFAULT '0.000000',
  `LineTotal` decimal(18,6) NOT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ProductType` int(11) DEFAULT NULL,
  PRIMARY KEY (`RecurringInvoiceDetailID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci