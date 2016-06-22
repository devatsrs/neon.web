CREATE TABLE `tblDialPlan` (
  `DialPlanID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `Name` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`DialPlanID`),
  UNIQUE KEY `CompanyID_Name` (`CompanyID`,`Name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci