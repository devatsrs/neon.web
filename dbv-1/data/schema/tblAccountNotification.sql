CREATE TABLE `tblAccountNotification` (
  `AccountNotificationID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `NotificationType` int(11) DEFAULT NULL,
  `EmailAddresses` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`AccountNotificationID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci