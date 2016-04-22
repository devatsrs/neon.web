CREATE TABLE `AccountEmailLog` (
  `AccountEmailLogID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `UserID` int(11) DEFAULT NULL,
  `JobId` int(11) DEFAULT NULL,
  `ProcessID` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `Emailfrom` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `EmailTo` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Subject` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Message` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Cc` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Bcc` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AttachmentPaths` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`AccountEmailLogID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci