CREATE TABLE `tblAccountDiscountScheme` (
  `AccountDiscountSchemeID` int(11) NOT NULL AUTO_INCREMENT,
  `AccountDiscountPlanID` int(11) NOT NULL,
  `DiscountID` int(11) NOT NULL,
  `Threshold` int(11) NOT NULL,
  `Discount` int(11) NOT NULL,
  `Unlimited` tinyint(1) NOT NULL DEFAULT '0',
  `MinutesUsed` int(11) DEFAULT NULL,
  PRIMARY KEY (`AccountDiscountSchemeID`),
  KEY `FK_tblAccountDiscountScheme_tblAccountDiscountPlan` (`AccountDiscountPlanID`),
  KEY `FK_tblAccountDiscountScheme_tblDiscount` (`DiscountID`),
  CONSTRAINT `FK_tblAccountDiscountScheme_tblAccountDiscountPlan` FOREIGN KEY (`AccountDiscountPlanID`) REFERENCES `tblAccountDiscountPlan` (`AccountDiscountPlanID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_tblAccountDiscountScheme_tblDiscount` FOREIGN KEY (`DiscountID`) REFERENCES `tblDiscount` (`DiscountID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci