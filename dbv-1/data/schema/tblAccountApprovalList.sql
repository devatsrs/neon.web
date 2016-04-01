CREATE TABLE `tblAccountApprovalList` (
  `AccountApprovalListID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyId` int(11) NOT NULL,
  `AccountApprovalID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `FileName` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`AccountApprovalListID`),
  KEY `FK_tblAccountApprovalList_tblAccountApproval` (`AccountApprovalID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci