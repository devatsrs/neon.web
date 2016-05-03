CREATE TABLE `tblDispute` (
  `DisputeID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `InvoiceID` int(11) NOT NULL,
  `DisputeTotal` decimal(18,6) NOT NULL,
  `DisputeDifference` decimal(18,6) NOT NULL,
  `DisputeDifferencePer` decimal(18,6) NOT NULL,
  `DisputeMinutes` bigint(20) NOT NULL,
  `MinutesDifference` bigint(20) NOT NULL,
  `MinutesDifferencePer` double(18,6) NOT NULL,
  `Status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0 - Pending, 1- Setteled , 2 - Canceled',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `CreatedBy` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `ModifiedBy` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`DisputeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci