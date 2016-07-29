CREATE TABLE `tblDestinationGroupSet` (
  `DestinationGroupSetID` int(11) NOT NULL AUTO_INCREMENT,
  `CodedeckID` int(11) NOT NULL,
  `Status` int(11) DEFAULT NULL,
  `CompanyID` int(11) NOT NULL,
  `Name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`DestinationGroupSetID`),
  UNIQUE KEY `GroupName` (`CompanyID`,`Name`),
  KEY `IX_CodedeckID` (`CodedeckID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci