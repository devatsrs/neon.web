CREATE TABLE `tblBillingSubscription` (
  `SubscriptionID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `Name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Description` longtext COLLATE utf8_unicode_ci,
  `InvoiceLineDescription` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `ActivationFee` decimal(18,2) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CurrencyID` int(11) DEFAULT NULL,
  `MonthlyFee` decimal(18,2) DEFAULT NULL,
  `WeeklyFee` decimal(18,2) DEFAULT NULL,
  `DailyFee` decimal(18,2) DEFAULT NULL,
  `Advance` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`SubscriptionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci