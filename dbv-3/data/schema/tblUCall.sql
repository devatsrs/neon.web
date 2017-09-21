CREATE TABLE `tblUCall` (
  `UID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `UUID` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`UID`),
  KEY `UUID` (`UUID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci