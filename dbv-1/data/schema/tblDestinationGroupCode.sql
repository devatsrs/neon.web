CREATE TABLE `tblDestinationGroupCode` (
  `DestinationGroupCodeID` int(11) NOT NULL AUTO_INCREMENT,
  `DestinationGroupID` int(11) NOT NULL,
  `RateID` int(11) NOT NULL,
  PRIMARY KEY (`DestinationGroupCodeID`),
  UNIQUE KEY `IX_DestinationGroupID_RateID` (`RateID`,`DestinationGroupID`),
  KEY `FK_tblDestinationGroupCode_tblDestinationGroup` (`DestinationGroupID`),
  CONSTRAINT `FK_tblDestinationGroupCode_tblDestinationGroup` FOREIGN KEY (`DestinationGroupID`) REFERENCES `tblDestinationGroup` (`DestinationGroupID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci