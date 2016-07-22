CREATE TABLE `tblAccountDiscountPlan` (
  `AccountDiscountPlanID` int(11) NOT NULL AUTO_INCREMENT,
  `AccountID` int(11) NOT NULL,
  `DiscountPlanID` int(11) NOT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`AccountDiscountPlanID`),
  KEY `FK_tblAccountDiscountPlan_tblDiscountPlan` (`DiscountPlanID`),
  CONSTRAINT `FK_tblAccountDiscountPlan_tblDiscountPlan` FOREIGN KEY (`DiscountPlanID`) REFERENCES `tblDiscountPlan` (`DiscountPlanID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci