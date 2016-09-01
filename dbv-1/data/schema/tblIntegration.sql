CREATE TABLE `tblIntegration` (
  `IntegrationID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyId` int(11) DEFAULT NULL,
  `Title` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Slug` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ParentID` int(11) DEFAULT '0',
  `MultiOption` enum('Y','N') COLLATE utf8_unicode_ci DEFAULT 'N',
  `ForeignID` int(11) DEFAULT '0',
  PRIMARY KEY (`IntegrationID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Integration categories'