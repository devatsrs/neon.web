CREATE TABLE `tblCronJobLog` (
  `CronJobLogID` int(11) NOT NULL AUTO_INCREMENT,
  `CronJobID` int(11) NOT NULL,
  `CronJobStatus` tinyint(3) unsigned DEFAULT NULL,
  `Message` longtext COLLATE utf8_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`CronJobLogID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci