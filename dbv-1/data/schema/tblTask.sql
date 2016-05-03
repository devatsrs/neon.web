CREATE TABLE `tblTask` (
  `TaskID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
<<<<<<< HEAD
  `UsersIDs` int(11) NOT NULL,
=======
  `UsersIDs` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
>>>>>>> 65d9c13b834d0f5e7b401d41381308301473264c
  `AccountIDs` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `BoardID` int(11) NOT NULL,
  `BoardColumnID` int(11) NOT NULL,
  `BackGroundColour` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `TextColour` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Subject` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `DueDate` datetime NOT NULL,
  `Priority` int(11) NOT NULL,
  `Order` int(11) NOT NULL,
  `Tags` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `TaggedUser` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `AttachmentPaths` mediumtext COLLATE utf8_unicode_ci NOT NULL,
<<<<<<< HEAD
  `Task_type` int(11) NOT NULL,
  `ParentID` int(11) NOT NULL,
=======
>>>>>>> 65d9c13b834d0f5e7b401d41381308301473264c
  `created_at` datetime NOT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `updated_at` datetime NOT NULL,
  `updated_by` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`TaskID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci