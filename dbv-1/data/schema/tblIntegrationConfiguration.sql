CREATE TABLE `tblIntegrationConfiguration` (
  `IntegrationConfigurationID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyId` int(11) DEFAULT NULL,
  `IntegrationID` int(11) DEFAULT NULL,
  `ParentIntegrationID` int(11) DEFAULT NULL,
  `Settings` longtext COLLATE utf8_unicode_ci,
  `Status` tinyint(4) DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`IntegrationConfigurationID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='tblIntegration configiration details'