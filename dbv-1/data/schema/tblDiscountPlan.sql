CREATE TABLE `tblDiscountPlan` (
  `DiscountPlanID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `DestinationGroupSetID` int(11) NOT NULL,
  `CurrencyID` int(11) NOT NULL,
  `Name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `UpdatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`DiscountPlanID`),
  KEY `FK_tblDiscountPlan_tblDestinationGroupSet` (`DestinationGroupSetID`),
  CONSTRAINT `FK_tblDiscountPlan_tblDestinationGroupSet` FOREIGN KEY (`DestinationGroupSetID`) REFERENCES `tblDestinationGroupSet` (`DestinationGroupSetID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci