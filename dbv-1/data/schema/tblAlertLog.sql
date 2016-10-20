CREATE TABLE `tblAlertLog` (
  `AlertLogID` int(11) NOT NULL AUTO_INCREMENT,
  `AlertID` int(11) DEFAULT NULL,
  `AccountEmailLogID` int(11) DEFAULT NULL,
  `send_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `SendBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`AlertLogID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci