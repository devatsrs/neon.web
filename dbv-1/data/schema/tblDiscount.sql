CREATE TABLE `tblDiscount` (
  `DiscountID` int(11) NOT NULL AUTO_INCREMENT,
  `DiscountPlanID` int(11) NOT NULL,
  `DestinationGroupID` int(11) NOT NULL,
  `Service` int(11) NOT NULL COMMENT '1=minutes;',
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `UpdatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`DiscountID`),
  KEY `FK_tblDiscount_tblDiscountPlan` (`DiscountPlanID`),
  KEY `FK_tblDiscount_tblDestinationGroup` (`DestinationGroupID`),
  CONSTRAINT `FK_tblDiscount_tblDestinationGroup` FOREIGN KEY (`DestinationGroupID`) REFERENCES `tblDestinationGroup` (`DestinationGroupID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_tblDiscount_tblDiscountPlan` FOREIGN KEY (`DiscountPlanID`) REFERENCES `tblDiscountPlan` (`DiscountPlanID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci