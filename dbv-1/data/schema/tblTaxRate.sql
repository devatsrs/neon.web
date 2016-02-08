CREATE TABLE `tblTaxRate` (
  `TaxRateId` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyId` int(11) DEFAULT NULL,
  `Title` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Amount` decimal(18,2) NOT NULL,
  `TaxType` tinyint(3) unsigned DEFAULT NULL,
  `FlatStatus` tinyint(3) unsigned DEFAULT NULL,
  `Status` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`TaxRateId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci