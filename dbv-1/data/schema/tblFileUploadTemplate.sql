CREATE TABLE `tblFileUploadTemplate` (
  `FileUploadTemplateID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `Title` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Options` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TemplateFile` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Type` tinyint(3) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`FileUploadTemplateID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci