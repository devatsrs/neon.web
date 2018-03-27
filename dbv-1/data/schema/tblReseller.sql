CREATE TABLE `tblReseller` (
  `ResellerID` int(11) NOT NULL AUTO_INCREMENT,
  `ResellerName` varchar(155) COLLATE utf8_unicode_ci NOT NULL,
  `CompanyID` int(11) NOT NULL,
  `ChildCompanyID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `FirstName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `LastName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Email` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Password` longtext COLLATE utf8_unicode_ci NOT NULL,
  `Status` tinyint(1) NOT NULL DEFAULT '1',
  `AllowWhiteLabel` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ResellerID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci