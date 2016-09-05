CREATE TABLE `tblAccountDiscountPlanHistory` (
  `AccountDiscountPlanHistoryID` int(11) NOT NULL AUTO_INCREMENT,
  `AccountID` int(11) NOT NULL,
  `AccountDiscountPlanID` int(11) NOT NULL,
  `DiscountPlanID` int(11) NOT NULL,
  `Type` tinyint(4) DEFAULT '1',
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Applied` datetime DEFAULT NULL,
  `Changed` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`AccountDiscountPlanHistoryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci