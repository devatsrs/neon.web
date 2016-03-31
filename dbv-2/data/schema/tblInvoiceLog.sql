CREATE TABLE `tblInvoiceLog` (
  `InvoiceLogID` int(11) NOT NULL AUTO_INCREMENT,
  `InvoiceID` int(11) DEFAULT NULL,
  `Note` longtext COLLATE utf8_unicode_ci,
  `InvoiceLogStatus` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`InvoiceLogID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci