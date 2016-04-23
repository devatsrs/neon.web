CREATE TABLE `tblDimTtime` (
  `time_id` int(11) NOT NULL AUTO_INCREMENT,
  `fulltime` time DEFAULT NULL,
  `hour` int(11) DEFAULT NULL,
  `minute` int(11) DEFAULT NULL,
  `second` int(11) DEFAULT NULL,
  `ampm` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`time_id`),
  KEY `IX_fulltime` (`fulltime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci