CREATE TABLE `tblGlobalSetting` (
  `GlobalSettingID` int(11) NOT NULL AUTO_INCREMENT,
  `Key` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Value` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`GlobalSettingID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci