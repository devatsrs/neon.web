CREATE TABLE `tblDestinationGroup` (
  `DestinationGroupID` int(11) NOT NULL AUTO_INCREMENT,
  `DestinationGroupSetID` int(11) NOT NULL,
  `CompanyID` int(11) NOT NULL,
  `Name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`DestinationGroupID`),
  UNIQUE KEY `CompanyID` (`CompanyID`,`DestinationGroupSetID`,`Name`),
  KEY `FK_tblDestinationGroup_tblDestinationGroupSet` (`DestinationGroupSetID`),
  CONSTRAINT `tblDestinationGroup_ibfk_1` FOREIGN KEY (`DestinationGroupSetID`) REFERENCES `tblDestinationGroupSet` (`DestinationGroupSetID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci