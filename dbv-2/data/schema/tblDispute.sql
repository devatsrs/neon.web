CREATE TABLE `tblDispute` (
  `DisputeID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `InvoiceNo` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `AccountID` int(11) NOT NULL,
  `DisputeAmount` decimal(18,6) DEFAULT NULL,
  `Notes` text COLLATE utf8_unicode_ci NOT NULL,
  `Status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0 - Pending, 1- Setteled , 2 - Canceled',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `CreatedBy` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `ModifiedBy` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`DisputeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci