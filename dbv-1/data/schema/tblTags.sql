CREATE TABLE `tblTags` (
  `TagID` int(11) NOT NULL AUTO_INCREMENT,
  `TagName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `CompanyID` int(11) DEFAULT NULL,
  `TagType` int(11) DEFAULT NULL,
  PRIMARY KEY (`TagID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci