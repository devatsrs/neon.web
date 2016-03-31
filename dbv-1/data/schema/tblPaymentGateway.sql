CREATE TABLE `tblPaymentGateway` (
  `PaymentGatewayID` int(11) NOT NULL AUTO_INCREMENT,
  `Title` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CompanyID` int(11) DEFAULT NULL,
  `Status` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`PaymentGatewayID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci