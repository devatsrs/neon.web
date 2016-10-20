CREATE TABLE `tblAccountDiscountPlan` (
  `AccountDiscountPlanID` int(11) NOT NULL AUTO_INCREMENT,
  `AccountID` int(11) NOT NULL,
  `DiscountPlanID` int(11) NOT NULL,
  `Type` tinyint(4) DEFAULT '1',
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `StartDate` date DEFAULT NULL,
  `EndDate` date DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`AccountDiscountPlanID`),
  UNIQUE KEY `AccountID` (`Type`,`AccountID`),
  KEY `FK_tblAccountDiscountPlan_tblDiscountPlan` (`DiscountPlanID`),
  CONSTRAINT `tblAccountDiscountPlan_ibfk_1` FOREIGN KEY (`DiscountPlanID`) REFERENCES `tblDiscountPlan` (`DiscountPlanID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci