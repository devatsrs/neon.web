Use RMBilling3;

CREATE TABLE IF NOT EXISTS `tblProcessCallChargesLog` (
  `LogID` bigint(20) NOT NULL AUTO_INCREMENT,
  `AccountID` int(11) NOT NULL,
  `ServiceID` int(11) NOT NULL DEFAULT '0',
  `InvoiceDate` date NOT NULL,
  `Description` text COLLATE utf8_unicode_ci,
  `Amount` decimal(18,6) DEFAULT NULL,
  `PaymentStatus` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`LogID`),
  UNIQUE KEY `Unique_IX_AccountID_ServiceID_InvoiceDate` (`AccountID`,`ServiceID`,`InvoiceDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;