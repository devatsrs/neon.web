CREATE TABLE `tblRRate` (
  `RRateID` int(11) NOT NULL AUTO_INCREMENT,
  `CountryID` int(11) DEFAULT NULL,
  `CompanyID` int(11) DEFAULT NULL,
  `Code` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`RRateID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci