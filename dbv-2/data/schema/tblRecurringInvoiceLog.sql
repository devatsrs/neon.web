CREATE TABLE `tblRecurringInvoiceLog` (
  `RecurringInvoicesLogID` int(11) NOT NULL AUTO_INCREMENT,
  `RecurringInvoiceID` int(11) DEFAULT NULL,
  `Note` longtext COLLATE utf8_unicode_ci,
  `RecurringInvoiceLogStatus` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`RecurringInvoicesLogID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci