CREATE TABLE `tblCountry` (
  `CountryID` int(11) NOT NULL AUTO_INCREMENT,
  `Prefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Country` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`CountryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci