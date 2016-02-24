CREATE TABLE `tblOpportunityBoardColumn` (
  `OpportunityBoardColumnID` int(11) NOT NULL AUTO_INCREMENT,
  `OpportunityBoardID` int(11) NOT NULL,
  `CompanyID` int(11) NOT NULL,
  `OpportunityBoardColumnName` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Order` int(11) NOT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`OpportunityBoardColumnID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci