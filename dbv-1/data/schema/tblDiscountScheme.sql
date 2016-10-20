CREATE TABLE `tblDiscountScheme` (
  `DiscountSchemeID` int(11) NOT NULL AUTO_INCREMENT,
  `DiscountID` int(11) NOT NULL,
  `Threshold` int(11) NOT NULL,
  `Discount` int(11) NOT NULL,
  `Unlimited` tinyint(1) NOT NULL DEFAULT '0',
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `UpdatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`DiscountSchemeID`),
  KEY `FK_tblDiscountScheme_tblDiscount` (`DiscountID`),
  CONSTRAINT `FK_tblDiscountScheme_tblDiscount` FOREIGN KEY (`DiscountID`) REFERENCES `tblDiscount` (`DiscountID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci