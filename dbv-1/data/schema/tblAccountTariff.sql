CREATE TABLE `tblAccountTariff` (
  `AccountTariffID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL DEFAULT '0',
  `AccountID` int(11) NOT NULL,
  `ServiceID` int(11) NOT NULL,
  `RateTableID` int(11) NOT NULL,
  `Type` tinyint(4) DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`AccountTariffID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci