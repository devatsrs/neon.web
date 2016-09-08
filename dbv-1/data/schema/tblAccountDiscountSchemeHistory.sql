CREATE TABLE `tblAccountDiscountSchemeHistory` (
  `AccountDiscountSchemeHistoryID` int(11) NOT NULL AUTO_INCREMENT,
  `AccountDiscountSchemeID` int(11) NOT NULL,
  `AccountDiscountPlanID` int(11) NOT NULL,
  `DiscountID` int(11) NOT NULL,
  `Threshold` int(11) NOT NULL,
  `Discount` int(11) NOT NULL,
  `Unlimited` tinyint(1) NOT NULL DEFAULT '0',
  `SecondsUsed` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`AccountDiscountSchemeHistoryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci