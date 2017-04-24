CREATE TABLE `tblAccountService` (
  `AccountServiceID` int(11) NOT NULL AUTO_INCREMENT,
  `AccountID` int(11) NOT NULL DEFAULT '0',
  `ServiceID` int(3) NOT NULL DEFAULT '0',
  `CompanyID` int(3) NOT NULL DEFAULT '0',
  `Status` int(3) NOT NULL DEFAULT '1',
  `ServiceTitle` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`AccountServiceID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci