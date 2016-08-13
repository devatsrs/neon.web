CREATE TABLE `tblCompanyConfiguration` (
  `CompanyConfigurationID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `Key` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Value` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`CompanyConfigurationID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci