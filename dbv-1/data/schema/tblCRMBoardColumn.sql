CREATE TABLE `tblCRMBoardColumn` (
  `BoardColumnID` int(11) NOT NULL AUTO_INCREMENT,
  `BoardID` int(11) NOT NULL,
  `CompanyID` int(11) NOT NULL,
  `BoardColumnName` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Height` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `Width` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `Order` int(11) NOT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`BoardColumnID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci