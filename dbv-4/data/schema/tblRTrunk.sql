CREATE TABLE `tblRTrunk` (
  `RTrunkID` int(11) NOT NULL AUTO_INCREMENT,
  `Trunk` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `CompanyID` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`RTrunkID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci