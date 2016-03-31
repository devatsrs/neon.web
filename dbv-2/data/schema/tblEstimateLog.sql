CREATE TABLE `tblEstimateLog` (
  `EstimateLogID` int(11) NOT NULL AUTO_INCREMENT,
  `EstimateID` int(11) DEFAULT NULL,
  `Note` longtext COLLATE utf8_unicode_ci,
  `EstimateLogStatus` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`EstimateLogID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci