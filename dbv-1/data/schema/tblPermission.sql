CREATE TABLE `tblPermission` (
  `PermissionID` int(11) NOT NULL AUTO_INCREMENT,
  `role` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `action` longtext COLLATE utf8_unicode_ci,
  `resource` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`PermissionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci