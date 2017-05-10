CREATE TABLE `tblProcessID` (
  `ProcessID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Process` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ProcessID`),
  UNIQUE KEY `Process` (`Process`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci