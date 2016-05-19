CREATE TABLE `tblTask` (
  `TaskID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `UsersIDs` int(11) NOT NULL,
  `AccountIDs` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `BoardID` int(11) NOT NULL,
  `BoardColumnID` int(11) NOT NULL,
  `BackGroundColour` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `TextColour` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Subject` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `DueDate` datetime NOT NULL,
  `Priority` tinyint(4) NOT NULL DEFAULT '0',
  `Order` int(11) NOT NULL,
  `Tags` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `TaggedUsers` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `AttachmentPaths` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `Task_type` int(11) NOT NULL DEFAULT '0',
  `ParentID` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `updated_at` datetime NOT NULL,
  `updated_by` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`TaskID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci