CREATE TABLE `tblOpportunityComments` (
  `OpportunityCommentID` int(11) NOT NULL AUTO_INCREMENT,
  `OpportunityID` int(11) NOT NULL,
  `CommentText` longtext COLLATE utf8_unicode_ci,
  `AttachmentPaths` longtext COLLATE utf8_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`OpportunityCommentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci