CREATE TABLE `tblChargeCode` (
  `Prefix` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ChargeCode` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY `ChargeCode` (`ChargeCode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci