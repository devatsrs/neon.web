CREATE TABLE `tblAccountAuthenticate` (
  `AccountAuthenticateID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `CustomerAuthRule` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CustomerAuthValue` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `VendorAuthRule` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `VendorAuthValue` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`AccountAuthenticateID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci