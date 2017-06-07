CREATE TABLE `tblNoticeBoardPost` (
  `NoticeBoardPostID` int(11) NOT NULL AUTO_INCREMENT,
  `Title` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Detail` text COLLATE utf8_unicode_ci NOT NULL,
  `Type` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CompanyID` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`NoticeBoardPostID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci