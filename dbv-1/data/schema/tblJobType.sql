CREATE TABLE `tblJobType` (
  `JobTypeID` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `Code` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `Title` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CreatedDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `ModifiedDate` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`JobTypeID`),
  UNIQUE KEY `Code` (`Code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci