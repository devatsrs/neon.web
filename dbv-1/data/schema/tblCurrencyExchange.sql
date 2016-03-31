CREATE TABLE `tblCurrencyExchange` (
  `CurrencyExchangeID` int(11) NOT NULL AUTO_INCREMENT,
  `FromCurrencyID` int(11) NOT NULL,
  `ToCurrencyID` int(11) NOT NULL,
  `Rate` decimal(18,6) DEFAULT NULL,
  `InverseRate` decimal(18,6) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `createdby` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updatedby` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`CurrencyExchangeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci