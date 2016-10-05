CREATE TABLE `tblQuickBookLog` (
  `QuickBookLogID` int(11) NOT NULL AUTO_INCREMENT,
  `Note` longtext COLLATE utf8_unicode_ci,
  `Type` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`QuickBookLogID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci