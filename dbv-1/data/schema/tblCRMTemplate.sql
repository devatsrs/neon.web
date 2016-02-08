CREATE TABLE `tblCRMTemplate` (
  `TemplateID` char(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TemplateName` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Subject` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TemplateBody` longtext COLLATE utf8_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci