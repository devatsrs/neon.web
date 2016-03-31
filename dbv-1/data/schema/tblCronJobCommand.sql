CREATE TABLE `tblCronJobCommand` (
  `CronJobCommandID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `GatewayID` int(11) DEFAULT NULL,
  `Title` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Command` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Settings` longtext COLLATE utf8_unicode_ci,
  `Status` tinyint(3) unsigned NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`CronJobCommandID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci