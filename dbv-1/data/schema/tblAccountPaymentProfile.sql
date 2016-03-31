CREATE TABLE `tblAccountPaymentProfile` (
  `AccountPaymentProfileID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `PaymentGatewayID` int(11) NOT NULL,
  `Title` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Options` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Status` tinyint(3) unsigned DEFAULT NULL,
  `isDefault` tinyint(3) unsigned DEFAULT NULL,
  `Blocked` tinyint(3) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`AccountPaymentProfileID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci