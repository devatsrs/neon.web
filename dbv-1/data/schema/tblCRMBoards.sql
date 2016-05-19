CREATE TABLE `tblCRMBoards` (
  `BoardID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `BoardName` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Status` tinyint(4) NOT NULL DEFAULT '1',
  `BoardType` int(11) NOT NULL DEFAULT '1',
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`BoardID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci