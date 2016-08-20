CREATE TABLE `tblEstimateTaxRate` (
  `EstimateTaxRateID` int(11) NOT NULL AUTO_INCREMENT,
  `EstimateID` int(11) NOT NULL,
  `TaxRateID` int(11) NOT NULL,
  `TaxAmount` decimal(18,6) NOT NULL,
  `Title` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`EstimateTaxRateID`),
  UNIQUE KEY `IX_EstimateTaxRateUnique` (`EstimateID`,`TaxRateID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci