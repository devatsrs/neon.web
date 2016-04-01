CREATE TABLE `tblAccountApproval` (
  `AccountApprovalID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyId` int(11) NOT NULL,
  `CountryId` int(11) DEFAULT NULL,
  `Key` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Required` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `AccountType` tinyint(3) unsigned DEFAULT NULL,
  `Status` tinyint(3) unsigned DEFAULT NULL,
  `DocumentFile` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Infomsg` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `BillingType` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`AccountApprovalID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci