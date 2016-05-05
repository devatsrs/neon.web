CREATE TABLE `tblCRMComments` (
  `CommentID` int(11) NOT NULL AUTO_INCREMENT,
  `ParentID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `CommentText` longtext COLLATE utf8_unicode_ci,
  `AttachmentPaths` longtext COLLATE utf8_unicode_ci,
  `CommentType` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`CommentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci