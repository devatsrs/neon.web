CREATE TABLE `tblAccountOneOffCharge` (
  `AccountOneOffChargeID` int(11) NOT NULL AUTO_INCREMENT,
  `AccountID` int(11) DEFAULT NULL,
  `ProductID` int(11) DEFAULT NULL,
  `Description` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Price` decimal(18,6) DEFAULT NULL,
  `Qty` int(11) DEFAULT NULL,
  `Discount` decimal(18,2) DEFAULT NULL,
  `TaxRateID` int(11) DEFAULT NULL,
  `TaxAmount` decimal(18,6) DEFAULT NULL,
  `Date` datetime DEFAULT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ServiceID` int(11) DEFAULT '0',
  PRIMARY KEY (`AccountOneOffChargeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci