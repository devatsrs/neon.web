CREATE TABLE `tblResourceCategoryMapping` (
  `MappingID` int(11) NOT NULL AUTO_INCREMENT,
  `ResourceID` int(11) DEFAULT NULL,
  `ResourceCategoryID` int(11) DEFAULT NULL,
  `CompanyID` int(11) DEFAULT NULL,
  PRIMARY KEY (`MappingID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci