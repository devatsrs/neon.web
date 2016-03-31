CREATE TABLE `tblCompanySetting` (
  `CompanyID` int(11) NOT NULL,
  `Key` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Value` longtext COLLATE utf8_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci