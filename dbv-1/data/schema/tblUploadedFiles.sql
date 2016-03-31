CREATE TABLE `tblUploadedFiles` (
  `UploadedFileID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `UserID` int(11) DEFAULT NULL,
  `UploadedFileName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `UploadedFilePath` longtext COLLATE utf8_unicode_ci,
  `UploadedFileHttpPath` tinyint(4) DEFAULT NULL,
  `created_at` datetime(3) DEFAULT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime(3) DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`UploadedFileID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci