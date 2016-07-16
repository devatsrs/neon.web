CREATE TABLE `tblAccountSubscription` (
  `AccountSubscriptionID` int(11) NOT NULL AUTO_INCREMENT,
  `AccountID` int(11) NOT NULL,
  `SubscriptionID` int(11) NOT NULL,
  `InvoiceDescription` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Qty` int(11) NOT NULL,
  `StartDate` date NOT NULL,
  `EndDate` date DEFAULT NULL,
  `ExemptTax` tinyint(3) unsigned DEFAULT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`AccountSubscriptionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci