CREATE TABLE `tblProcessID` (
  `ProcessID` bigint(20) NOT NULL AUTO_INCREMENT,
  `Process` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`ProcessID`),
  UNIQUE KEY `Process` (`Process`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci