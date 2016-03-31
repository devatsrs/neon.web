CREATE TABLE `tblCompanyThemes` (
  `ThemeID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL DEFAULT '0',
  `DomainUrl` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `Logo` varchar(500) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `Favicon` varchar(500) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `Title` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `FooterText` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `FooterUrl` varchar(500) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `LoginMessage` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `CustomCss` text COLLATE utf8_unicode_ci,
  `ThemeStatus` enum('inactive','active') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active',
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`ThemeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci