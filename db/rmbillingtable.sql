-- --------------------------------------------------------
-- Host:                         188.227.186.98
-- Server version:               5.7.11 - MySQL Community Server (GPL)
-- Server OS:                    Linux
-- HeidiSQL Version:             9.1.0.4867
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping database structure for RMBilling4
CREATE DATABASE IF NOT EXISTS `RMBilling4` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `RMBilling4`;


-- Dumping structure for table RMBilling4.tblAccountOneOffCharge
CREATE TABLE IF NOT EXISTS `tblAccountOneOffCharge` (
  `AccountOneOffChargeID` int(11) NOT NULL AUTO_INCREMENT,
  `AccountID` int(11) DEFAULT NULL,
  `ProductID` int(11) DEFAULT NULL,
  `Description` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Price` decimal(18,6) DEFAULT NULL,
  `Qty` int(11) DEFAULT NULL,
  `Discount` decimal(18,2) DEFAULT NULL,
  `TaxRateID` int(11) DEFAULT NULL,
  `TaxAmount` decimal(18,6) DEFAULT NULL,
  `Date` datetime DEFAULT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`AccountOneOffChargeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RMBilling4.tblAccountSubscription
CREATE TABLE IF NOT EXISTS `tblAccountSubscription` (
  `AccountSubscriptionID` int(11) NOT NULL AUTO_INCREMENT,
  `AccountID` int(11) NOT NULL,
  `SubscriptionID` int(11) NOT NULL,
  `InvoiceDescription` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Qty` int(11) NOT NULL,
  `StartDate` date NOT NULL,
  `EndDate` date DEFAULT NULL,
  `ExemptTax` tinyint(3) unsigned DEFAULT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`AccountSubscriptionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RMBilling4.tblBillingSettings
CREATE TABLE IF NOT EXISTS `tblBillingSettings` (
  `BillingSettingID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `InvoiceNumberSequence` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `InvoiceStartNumber` int(11) DEFAULT NULL,
  PRIMARY KEY (`BillingSettingID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RMBilling4.tblBillingSubscription
CREATE TABLE IF NOT EXISTS `tblBillingSubscription` (
  `SubscriptionID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `Name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Description` longtext COLLATE utf8_unicode_ci,
  `InvoiceLineDescription` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `ActivationFee` decimal(18,2) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CurrencyID` int(11) DEFAULT NULL,
  `MonthlyFee` decimal(18,2) DEFAULT NULL,
  `WeeklyFee` decimal(18,2) DEFAULT NULL,
  `DailyFee` decimal(18,2) DEFAULT NULL,
  `Advance` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`SubscriptionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RMBilling4.tblCDRUploadHistory
CREATE TABLE IF NOT EXISTS `tblCDRUploadHistory` (
  `CDRUploadHistoryID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `CompanyGatewayID` int(11) NOT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `StartDate` datetime NOT NULL,
  `EndDate` datetime NOT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`CDRUploadHistoryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RMBilling4.tblGatewayAccount
CREATE TABLE IF NOT EXISTS `tblGatewayAccount` (
  `GatewayAccountPKID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `CompanyGatewayID` int(11) NOT NULL,
  `GatewayAccountID` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AccountName` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `AccountDetailInfo` longtext COLLATE utf8_unicode_ci,
  `IsVendor` tinyint(3) unsigned DEFAULT NULL,
  `GatewayVendorID` int(11) DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `AccountIP` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`GatewayAccountPKID`),
  KEY `IX_tblGatewayAccount_GatewayAccountID_AccountName_5F8A5` (`GatewayAccountID`,`AccountName`,`CompanyGatewayID`),
  KEY `IX_tblGatewayAccount_AccountID_63248` (`AccountID`,`GatewayAccountID`),
  KEY `IX_tblGatewayAccount_AccountID_CDCF2` (`AccountID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RMBilling4.tblInvoice
CREATE TABLE IF NOT EXISTS `tblInvoice` (
  `InvoiceID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `Address` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `InvoiceNumber` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `IssueDate` datetime DEFAULT NULL,
  `CurrencyID` int(11) DEFAULT NULL,
  `PONumber` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `InvoiceType` int(11) DEFAULT NULL,
  `SubTotal` decimal(18,6) DEFAULT NULL,
  `TotalDiscount` decimal(18,2) DEFAULT '0.00',
  `TaxRateID` int(11) DEFAULT NULL,
  `TotalTax` decimal(18,6) DEFAULT '0.000000',
  `InvoiceTotal` decimal(18,6) DEFAULT NULL,
  `GrandTotal` decimal(18,6) DEFAULT NULL,
  `Description` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Attachment` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Note` longtext COLLATE utf8_unicode_ci,
  `Terms` longtext COLLATE utf8_unicode_ci,
  `InvoiceStatus` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PDF` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `UsagePath` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PreviousBalance` decimal(18,6) DEFAULT NULL,
  `TotalDue` decimal(18,6) DEFAULT NULL,
  `Payment` decimal(18,6) DEFAULT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ItemInvoice` tinyint(3) unsigned DEFAULT NULL,
  `FooterTerm` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`InvoiceID`),
  KEY `IX_AccountID_Status_CompanyID` (`AccountID`,`InvoiceStatus`,`CompanyID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RMBilling4.tblInvoiceDetail
CREATE TABLE IF NOT EXISTS `tblInvoiceDetail` (
  `InvoiceDetailID` int(11) NOT NULL AUTO_INCREMENT,
  `InvoiceID` int(11) NOT NULL,
  `ProductID` int(11) DEFAULT NULL,
  `Description` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `StartDate` datetime DEFAULT NULL,
  `EndDate` datetime DEFAULT NULL,
  `Price` decimal(18,6) NOT NULL,
  `Qty` int(11) DEFAULT NULL,
  `Discount` decimal(18,2) DEFAULT NULL,
  `TaxRateID` int(11) DEFAULT NULL,
  `TaxAmount` decimal(18,6) NOT NULL DEFAULT '0.000000',
  `LineTotal` decimal(18,6) NOT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ProductType` int(11) DEFAULT NULL,
  PRIMARY KEY (`InvoiceDetailID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RMBilling4.tblInvoiceLog
CREATE TABLE IF NOT EXISTS `tblInvoiceLog` (
  `InvoiceLogID` int(11) NOT NULL AUTO_INCREMENT,
  `InvoiceID` int(11) DEFAULT NULL,
  `Note` longtext COLLATE utf8_unicode_ci,
  `InvoiceLogStatus` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`InvoiceLogID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RMBilling4.tblInvoiceReminder
CREATE TABLE IF NOT EXISTS `tblInvoiceReminder` (
  `InvoiceReminderID` int(11) NOT NULL AUTO_INCREMENT,
  `Title` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Days` int(11) DEFAULT NULL,
  `CompanyID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `TemplateID` int(11) NOT NULL,
  `Status` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`InvoiceReminderID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RMBilling4.tblInvoiceTaxRate
CREATE TABLE IF NOT EXISTS `tblInvoiceTaxRate` (
  `InvoiceTaxRateID` int(11) NOT NULL AUTO_INCREMENT,
  `InvoiceID` int(11) NOT NULL,
  `TaxRateID` int(11) NOT NULL,
  `TaxAmount` decimal(18,6) NOT NULL,
  `Title` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`InvoiceTaxRateID`),
  UNIQUE KEY `IX_InvoiceTaxRateUnique` (`InvoiceID`,`TaxRateID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RMBilling4.tblInvoiceTemplate
CREATE TABLE IF NOT EXISTS `tblInvoiceTemplate` (
  `InvoiceTemplateID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CompanyID` int(11) NOT NULL,
  `InvoiceNumberPrefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `InvoicePages` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `InvoiceStartNumber` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `LastInvoiceNumber` bigint(20) DEFAULT NULL,
  `CompanyLogoAS3Key` longtext COLLATE utf8_unicode_ci,
  `CompanyLogoUrl` longtext COLLATE utf8_unicode_ci,
  `Pages` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Header` longtext COLLATE utf8_unicode_ci,
  `Footer` longtext COLLATE utf8_unicode_ci,
  `ShowZeroCall` tinyint(3) unsigned DEFAULT '0',
  `Terms` longtext COLLATE utf8_unicode_ci,
  `Status` tinyint(3) unsigned DEFAULT '0',
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ShowPrevBal` tinyint(3) unsigned DEFAULT NULL,
  `DateFormat` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Type` int(11) DEFAULT NULL,
  `FooterTerm` longtext COLLATE utf8_unicode_ci,
  `ShowBillingPeriod` int(11) DEFAULT '0',
  PRIMARY KEY (`InvoiceTemplateID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RMBilling4.tblPayment
CREATE TABLE IF NOT EXISTS `tblPayment` (
  `PaymentID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `InvoiceNo` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PaymentDate` datetime NOT NULL,
  `PaymentMethod` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `PaymentType` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `Notes` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Amount` decimal(18,8) NOT NULL,
  `Status` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `ModifyBy` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `PaymentProof` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Recall` tinyint(4) NOT NULL DEFAULT '0',
  `RecallReasoan` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `RecallBy` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `CurrencyID` int(11) NOT NULL,
  `BulkUpload` bit(1) DEFAULT b'0',
  PRIMARY KEY (`PaymentID`),
  KEY `IX_AccountID_Status_CompanyID` (`AccountID`,`Status`,`CompanyID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RMBilling4.tblPaymentOLD
CREATE TABLE IF NOT EXISTS `tblPaymentOLD` (
  `PaymentID` int(11) NOT NULL,
  `CompanyID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `InvoiceNo` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PaymentDate` datetime NOT NULL,
  `PaymentMethod` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `PaymentType` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `Notes` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Amount` decimal(18,8) NOT NULL,
  `Status` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `ModifyBy` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `PaymentProof` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Recall` tinyint(4) NOT NULL DEFAULT '0',
  `RecallReasoan` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `RecallBy` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `CurrencyID` int(11) NOT NULL,
  `BulkUpload` bit(1) DEFAULT b'0',
  PRIMARY KEY (`PaymentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RMBilling4.tblProduct
CREATE TABLE IF NOT EXISTS `tblProduct` (
  `ProductID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyId` int(11) NOT NULL,
  `Name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Code` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Description` longtext COLLATE utf8_unicode_ci,
  `Amount` decimal(18,2) DEFAULT NULL,
  `Active` tinyint(3) unsigned DEFAULT '1',
  `Note` longtext COLLATE utf8_unicode_ci,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`ProductID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RMBilling4.tblSummeryData
CREATE TABLE IF NOT EXISTS `tblSummeryData` (
  `SummeryDataID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `IP` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `GatewayAccountID` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `Gateway` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `AccountName` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `AreaPrefix` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `AreaName` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `Country` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TotalCalls` int(11) NOT NULL,
  `Duration` decimal(18,2) NOT NULL,
  `TotalCharge` decimal(18,6) NOT NULL,
  `ProcessID` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  PRIMARY KEY (`SummeryDataID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RMBilling4.tblTempPayment
CREATE TABLE IF NOT EXISTS `tblTempPayment` (
  `PaymentID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `ProcessID` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `AccountID` int(11) NOT NULL,
  `InvoiceNo` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PaymentDate` datetime NOT NULL,
  `PaymentMethod` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `PaymentType` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `Notes` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Amount` decimal(18,8) NOT NULL,
  `Status` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`PaymentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT;

-- Data exporting was unselected.


-- Dumping structure for table RMBilling4.tblTempUsageDownloadLog
CREATE TABLE IF NOT EXISTS `tblTempUsageDownloadLog` (
  `TempUsageDownloadLogID` bigint(20) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `CompanyGatewayID` int(11) DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `DailySummaryStatus` int(11) DEFAULT '0',
  `ProcessID` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`TempUsageDownloadLogID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RMBilling4.tblTransactionLog
CREATE TABLE IF NOT EXISTS `tblTransactionLog` (
  `TransactionLogID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `InvoiceID` int(11) DEFAULT NULL,
  `Transaction` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Notes` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Amount` decimal(18,6) NOT NULL,
  `Status` tinyint(3) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `ModifyBy` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Reposnse` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`TransactionLogID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RMBilling4.tblUsageDaily
CREATE TABLE IF NOT EXISTS `tblUsageDaily` (
  `UsageDailyID` bigint(20) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `CompanyGatewayID` int(11) DEFAULT NULL,
  `Trunk` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AreaPrefix` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Pincode` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Extension` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TotalCharges` double DEFAULT NULL,
  `TotalBilledDuration` int(11) DEFAULT NULL,
  `TotalDuration` int(11) DEFAULT NULL,
  `NoOfCalls` int(11) DEFAULT NULL,
  `DailyDate` datetime DEFAULT NULL,
  PRIMARY KEY (`UsageDailyID`),
  KEY `IX_AccountID_ComapyGatewayID` (`AccountID`,`CompanyGatewayID`),
  KEY `IX_DailyDate` (`DailyDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RMBilling4.tblUsageDownloadFiles
CREATE TABLE IF NOT EXISTS `tblUsageDownloadFiles` (
  `UsageDownloadFilesID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyGatewayID` int(11) NOT NULL DEFAULT '0',
  `filename` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `UpdatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`UsageDownloadFilesID`),
  UNIQUE KEY `IX_gateway_filename` (`CompanyGatewayID`,`filename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RMBilling4.tblUsageHourly
CREATE TABLE IF NOT EXISTS `tblUsageHourly` (
  `UsageHourlyID` bigint(20) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `CompanyGatewayID` int(11) DEFAULT NULL,
  `GatewayAccountID` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `Trunk` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AreaPrefix` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TotalCharges` double DEFAULT '0',
  `TotalDuration` int(11) DEFAULT NULL,
  `NoOfCalls` int(11) DEFAULT NULL,
  `PeriodFrom` datetime DEFAULT NULL,
  `PeriodTo` datetime DEFAULT NULL,
  `Duration` int(11) DEFAULT NULL,
  PRIMARY KEY (`UsageHourlyID`),
  KEY `IX_tblUsageHourly_CompanyID_EC544` (`CompanyID`,`CompanyGatewayID`,`GatewayAccountID`,`AreaPrefix`,`PeriodFrom`,`PeriodTo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RMBilling4.tempsummery
CREATE TABLE IF NOT EXISTS `tempsummery` (
  `Customer Name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `Prefix` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `Country` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Description` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `rate1` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `rate2` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `Number of Calls` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Duration` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `Billed Duration` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Charged Amount` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Currency` varchar(100) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
