CREATE TABLE `tblResourceCategories` (
  `ResourceCategoryID` int(11) NOT NULL AUTO_INCREMENT,
  `ResourceCategoryName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CompanyID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ResourceCategoryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci