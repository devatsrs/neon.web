CREATE TABLE IF NOT EXISTS `tblTask` (
  `TaskID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `UsersIDs` int(11) NOT NULL,
  `AccountIDs` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `BoardID` int(11) NOT NULL,
  `BoardColumnID` int(11) NOT NULL,
  `BackGroundColour` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TextColour` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Subject` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Description` longtext COLLATE utf8_unicode_ci,
  `DueDate` datetime DEFAULT NULL,
  `Priority` tinyint(4) DEFAULT '0',
  `ClosingDate` datetime DEFAULT NULL,
  `Order` int(11) NOT NULL DEFAULT '0',
  `taskClosed` tinyint(4) NOT NULL DEFAULT '0',
  `Tags` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TaggedUsers` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AttachmentPaths` mediumtext COLLATE utf8_unicode_ci,
  `Task_type` int(11) NOT NULL DEFAULT '0',
  `ParentID` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`TaskID`)
) ENGINE=InnoDB AUTO_INCREMENT=142 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
