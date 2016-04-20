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

-- Dumping database structure for RateManagement4
CREATE DATABASE IF NOT EXISTS `RateManagement4` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `RateManagement4`;


-- Dumping structure for table RateManagement4.tblAccount
CREATE TABLE IF NOT EXISTS `tblAccount` (
  `AccountID` int(11) NOT NULL AUTO_INCREMENT,
  `AccountType` tinyint(3) unsigned DEFAULT NULL,
  `CompanyId` int(11) DEFAULT NULL,
  `CodeDeckId` int(11) DEFAULT NULL,
  `InvoiceTemplateID` int(11) DEFAULT NULL,
  `CurrencyId` int(11) DEFAULT NULL,
  `TaxRateId` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Title` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Owner` int(11) DEFAULT NULL,
  `Number` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AccountName` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `NamePrefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `FirstName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `LastName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `LeadStatus` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Rating` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `LeadSource` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Skype` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `EmailOptOut` tinyint(1) DEFAULT NULL,
  `Twitter` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `SecondaryEmail` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Email` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `IsVendor` tinyint(1) DEFAULT NULL,
  `IsCustomer` tinyint(1) DEFAULT NULL,
  `Ownership` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Website` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Mobile` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Phone` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Fax` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Employee` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Description` longtext COLLATE utf8_unicode_ci,
  `Address1` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Address2` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Address3` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `City` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `State` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PostCode` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Country` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `RateEmail` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `BillingEmail` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TechnicalEmail` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `VatNumber` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Status` int(11) DEFAULT NULL,
  `PaymentMethod` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PaymentDetail` longtext COLLATE utf8_unicode_ci,
  `Converted` tinyint(1) DEFAULT NULL,
  `ConvertedDate` datetime DEFAULT NULL,
  `ConvertedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TimeZone` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `VerificationStatus` tinyint(3) unsigned DEFAULT '0',
  `BillingType` tinyint(3) unsigned DEFAULT NULL,
  `BillingTimezone` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `SendInvoiceSetting` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PaymentDueInDays` int(11) DEFAULT NULL,
  `RoundChargesAmount` int(11) DEFAULT NULL,
  `BillingCycleType` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `BillingCycleValue` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Subscription` tinyint(1) DEFAULT '0',
  `SubscriptionQty` int(11) DEFAULT NULL,
  `CDRType` int(11) DEFAULT NULL,
  `InvoiceUsage` int(11) DEFAULT NULL,
  `AccountIP` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `BillingStartDate` datetime DEFAULT NULL,
  `LastInvoiceDate` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `NextInvoiceDate` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` longtext COLLATE utf8_unicode_ci,
  `Picture` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AutorizeProfileID` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tags` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Autopay` tinyint(3) unsigned DEFAULT NULL,
  `CustomerCLI` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `NominalAnalysisNominalAccountNumber` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `InboudRateTableID` int(11) DEFAULT NULL,
  PRIMARY KEY (`AccountID`),
  KEY `IX_tblAccount_AccountType_CompanyId_IsVendor_Status_Verificati10` (`AccountType`,`CompanyId`,`IsVendor`,`Status`,`VerificationStatus`,`AccountName`),
  KEY `CurrencyId` (`CurrencyId`),
  KEY `TaxRateId` (`TaxRateId`),
  KEY `InvoiceTemplateID` (`InvoiceTemplateID`),
  KEY `CodeDeckId` (`CodeDeckId`),
  KEY `IX_tblAccount_CompanyId_AccountName_AccountID_5E166` (`CompanyId`,`AccountName`),
  KEY `IX_tblAccount_AccountType_CompanyId_Status_738CD` (`AccountType`,`CompanyId`,`Status`,`AccountName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblAccountActivity
CREATE TABLE IF NOT EXISTS `tblAccountActivity` (
  `ActivityID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `Title` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Description` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Date` datetime DEFAULT NULL,
  `ActivityType` tinyint(3) unsigned NOT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ActivityID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblAccountApproval
CREATE TABLE IF NOT EXISTS `tblAccountApproval` (
  `AccountApprovalID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyId` int(11) NOT NULL,
  `CountryId` int(11) DEFAULT NULL,
  `Key` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Required` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `AccountType` tinyint(3) unsigned DEFAULT NULL,
  `Status` tinyint(3) unsigned DEFAULT NULL,
  `DocumentFile` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Infomsg` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `BillingType` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`AccountApprovalID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblAccountApprovalList
CREATE TABLE IF NOT EXISTS `tblAccountApprovalList` (
  `AccountApprovalListID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyId` int(11) NOT NULL,
  `AccountApprovalID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `FileName` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`AccountApprovalListID`),
  KEY `FK_tblAccountApprovalList_tblAccountApproval` (`AccountApprovalID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblAccountAuthenticate
CREATE TABLE IF NOT EXISTS `tblAccountAuthenticate` (
  `AccountAuthenticateID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `CustomerAuthRule` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CustomerAuthValue` varchar(8000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `VendorAuthRule` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `VendorAuthValue` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`AccountAuthenticateID`),
  KEY `IX_AccountID` (`AccountID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblAccountPaymentProfile
CREATE TABLE IF NOT EXISTS `tblAccountPaymentProfile` (
  `AccountPaymentProfileID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `PaymentGatewayID` int(11) NOT NULL,
  `Title` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Options` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Status` tinyint(3) unsigned DEFAULT NULL,
  `isDefault` tinyint(3) unsigned DEFAULT NULL,
  `Blocked` tinyint(3) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`AccountPaymentProfileID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblChargeCode
CREATE TABLE IF NOT EXISTS `tblChargeCode` (
  `Prefix` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ChargeCode` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblCodeDeck
CREATE TABLE IF NOT EXISTS `tblCodeDeck` (
  `CodeDeckId` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyId` int(11) NOT NULL,
  `CodeDeckName` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Type` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`CodeDeckId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblCompany
CREATE TABLE IF NOT EXISTS `tblCompany` (
  `CompanyID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyName` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `VAT` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CustomerAccountPrefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `FirstName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `LastName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Email` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Phone` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `SMTPServer` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `SMTPUsername` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `SMTPPassword` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Port` int(11) DEFAULT NULL,
  `IsSSL` tinyint(3) unsigned DEFAULT NULL,
  `EmailFrom` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `InvoiceBCCAddress` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AutoProcessResultEmailTo` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `FileLocation` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `VendorRateIncreaseWarningEmail` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `RateEmailFrom` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ServerUsername` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ServerPassword` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `NetworkFileLocation` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `City` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PostCode` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Country` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Address1` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Address2` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Address3` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Status` tinyint(3) unsigned DEFAULT '0',
  `TimeZone` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CurrencyId` int(11) DEFAULT NULL,
  `PaymentRequestEmail` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `DueSheetEmail` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `InvoiceGenerationEmail` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `RateSheetExcellNote` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `InvoiceStatus` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`CompanyID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblCompanyGateway
CREATE TABLE IF NOT EXISTS `tblCompanyGateway` (
  `CompanyGatewayID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `GatewayID` int(11) DEFAULT NULL,
  `Title` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `IP` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Settings` longtext COLLATE utf8_unicode_ci,
  `Status` tinyint(3) unsigned DEFAULT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `TimeZone` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `BillingTime` tinyint(3) unsigned DEFAULT NULL,
  `BillingTimeZone` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `UniqueID` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`CompanyGatewayID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblCompanySetting
CREATE TABLE IF NOT EXISTS `tblCompanySetting` (
  `CompanyID` int(11) NOT NULL,
  `Key` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Value` longtext COLLATE utf8_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblContact
CREATE TABLE IF NOT EXISTS `tblContact` (
  `ContactID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyId` int(11) DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `Title` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Owner` int(11) DEFAULT NULL,
  `Department` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `NamePrefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `FirstName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `LastName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `DateOfBirth` date DEFAULT NULL,
  `Skype` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `EmailOptOut` tinyint(1) DEFAULT NULL,
  `Twitter` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Email` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `SecondaryEmail` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Mobile` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `HomePhone` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Phone` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Fax` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `OtherPhone` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Description` longtext COLLATE utf8_unicode_ci,
  `Address1` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Address2` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Address3` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `City` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `State` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PostCode` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Country` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ContactID`),
  KEY `UserID` (`Owner`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblContactNote
CREATE TABLE IF NOT EXISTS `tblContactNote` (
  `NoteID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `ContactID` int(11) NOT NULL,
  `Title` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Note` longtext COLLATE utf8_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`NoteID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblCountry
CREATE TABLE IF NOT EXISTS `tblCountry` (
  `CountryID` int(11) NOT NULL AUTO_INCREMENT,
  `Prefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Country` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`CountryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblCRMTemplate
CREATE TABLE IF NOT EXISTS `tblCRMTemplate` (
  `TemplateID` char(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TemplateName` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Subject` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TemplateBody` longtext COLLATE utf8_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblCronJob
CREATE TABLE IF NOT EXISTS `tblCronJob` (
  `CronJobID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `CronJobCommandID` int(11) DEFAULT NULL,
  `Settings` longtext COLLATE utf8_unicode_ci,
  `Status` tinyint(3) unsigned NOT NULL,
  `LastRunTime` datetime DEFAULT NULL,
  `NextRunTime` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Active` int(11) DEFAULT '0',
  `JobTitle` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `DownloadActive` int(11) DEFAULT '0',
  `PID` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `EmailSendTime` datetime DEFAULT NULL,
  `CdrBehindEmailSendTime` datetime DEFAULT NULL,
  `CdrBehindDuration` int(11) DEFAULT NULL,
  PRIMARY KEY (`CronJobID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblCronJobCommand
CREATE TABLE IF NOT EXISTS `tblCronJobCommand` (
  `CronJobCommandID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `GatewayID` int(11) DEFAULT NULL,
  `Title` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Command` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Settings` longtext COLLATE utf8_unicode_ci,
  `Status` tinyint(3) unsigned NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`CronJobCommandID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblCronJobLog
CREATE TABLE IF NOT EXISTS `tblCronJobLog` (
  `CronJobLogID` int(11) NOT NULL AUTO_INCREMENT,
  `CronJobID` int(11) NOT NULL,
  `CronJobStatus` tinyint(3) unsigned DEFAULT NULL,
  `Message` longtext COLLATE utf8_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`CronJobLogID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblCurrency
CREATE TABLE IF NOT EXISTS `tblCurrency` (
  `CurrencyId` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyId` int(11) NOT NULL,
  `Code` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Status` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Symbol` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`CurrencyId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblCurrencyConversion
CREATE TABLE IF NOT EXISTS `tblCurrencyConversion` (
  `ConversionID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `CurrencyID` int(11) NOT NULL,
  `Value` decimal(18,6) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `EffectiveDate` datetime DEFAULT NULL,
  PRIMARY KEY (`ConversionID`),
  KEY `IX_CurrencyID_CompanyID` (`CurrencyID`,`CompanyID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblCurrencyConversionLog
CREATE TABLE IF NOT EXISTS `tblCurrencyConversionLog` (
  `ConversionLogID` int(11) NOT NULL AUTO_INCREMENT,
  `CurrencyID` int(11) NOT NULL,
  `CompanyID` int(11) NOT NULL,
  `EffectiveDate` datetime NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `Value` decimal(18,6) DEFAULT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ConversionLogID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblCurrencyExchange
CREATE TABLE IF NOT EXISTS `tblCurrencyExchange` (
  `CurrencyExchangeID` int(11) NOT NULL AUTO_INCREMENT,
  `FromCurrencyID` int(11) NOT NULL,
  `ToCurrencyID` int(11) NOT NULL,
  `Rate` decimal(18,6) DEFAULT NULL,
  `InverseRate` decimal(18,6) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `createdby` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updatedby` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`CurrencyExchangeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblCustomerRate
CREATE TABLE IF NOT EXISTS `tblCustomerRate` (
  `CustomerRateID` int(11) NOT NULL AUTO_INCREMENT,
  `RateID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `LastModifiedDate` datetime DEFAULT NULL,
  `LastModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CreatedDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `TrunkID` int(11) DEFAULT NULL,
  `Rate` decimal(18,6) NOT NULL DEFAULT '0.000000',
  `EffectiveDate` date DEFAULT NULL,
  `PreviousRate` decimal(18,6) DEFAULT '0.000000',
  `Interval1` int(11) DEFAULT NULL,
  `IntervalN` int(11) DEFAULT NULL,
  `RoutinePlan` int(11) DEFAULT NULL,
  `ConnectionFee` decimal(18,6) DEFAULT NULL,
  PRIMARY KEY (`CustomerRateID`),
  KEY `IX_tblCustomerRate_CustomerID_9494E` (`CustomerID`),
  KEY `IX_tblCustomerRate_CustomerID_EffectiveDate_61B1F` (`CustomerID`,`EffectiveDate`),
  KEY `IX_tblCustomerRate_CustomerID_TrunkID_Rate_FDB55` (`CustomerID`,`TrunkID`,`Rate`),
  KEY `IX_tblCustomerRate_RateID_CustomerID_effectivedate` (`CustomerID`,`TrunkID`,`RateID`),
  KEY `Index 6` (`RateID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblCustomerRateArchive
CREATE TABLE IF NOT EXISTS `tblCustomerRateArchive` (
  `CustomerRateArchiveID` int(11) NOT NULL AUTO_INCREMENT,
  `CustomerRateID` int(11) NOT NULL,
  `CustomerId` int(11) NOT NULL,
  `TrunkId` int(11) NOT NULL,
  `RateId` int(11) NOT NULL,
  `Rate` decimal(18,6) NOT NULL,
  `EffectiveDate` datetime NOT NULL,
  `CreatedDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `Notes` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`CustomerRateArchiveID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblCustomerRate_Backup
CREATE TABLE IF NOT EXISTS `tblCustomerRate_Backup` (
  `CustomerRateID` int(11) NOT NULL,
  `RateID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `LastModifiedDate` datetime DEFAULT NULL,
  `LastModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CreatedDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `TrunkID` int(11) DEFAULT NULL,
  `Rate` decimal(18,6) NOT NULL DEFAULT '0.000000',
  `EffectiveDate` date DEFAULT NULL,
  `PreviousRate` decimal(18,6) DEFAULT '0.000000',
  `Interval1` int(11) DEFAULT NULL,
  `IntervalN` int(11) DEFAULT NULL,
  `RoutinePlan` int(11) DEFAULT NULL,
  `ConnectionFee` decimal(18,6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblCustomerTrunk
CREATE TABLE IF NOT EXISTS `tblCustomerTrunk` (
  `CustomerTrunkID` int(11) NOT NULL AUTO_INCREMENT,
  `RateTableID` bigint(20) DEFAULT NULL,
  `CompanyID` int(11) NOT NULL,
  `CodeDeckId` int(11) DEFAULT NULL,
  `AccountID` int(11) NOT NULL,
  `TrunkID` int(11) NOT NULL,
  `Prefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `IncludePrefix` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `Status` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CompanyGatewayIDs` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `RoutinePlanStatus` tinyint(3) unsigned DEFAULT NULL,
  `RateTableAssignDate` datetime DEFAULT NULL,
  `UseInBilling` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`CustomerTrunkID`),
  UNIQUE KEY `IX_AccountIDTrunkID_Unique` (`AccountID`,`TrunkID`),
  KEY `Index_AccountID_TrunkID_Status` (`TrunkID`,`AccountID`,`Status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblEmailTemplate
CREATE TABLE IF NOT EXISTS `tblEmailTemplate` (
  `TemplateID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `TemplateName` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Subject` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TemplateBody` longtext COLLATE utf8_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `userID` int(11) DEFAULT NULL,
  `Type` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`TemplateID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblFileUploadTemplate
CREATE TABLE IF NOT EXISTS `tblFileUploadTemplate` (
  `FileUploadTemplateID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `Title` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Options` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TemplateFile` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Type` tinyint(3) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`FileUploadTemplateID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblGateway
CREATE TABLE IF NOT EXISTS `tblGateway` (
  `GatewayID` int(11) NOT NULL AUTO_INCREMENT,
  `Title` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Status` tinyint(3) unsigned DEFAULT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`GatewayID`),
  KEY `IX_tblGatewaySetting` (`GatewayID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblGatewayConfig
CREATE TABLE IF NOT EXISTS `tblGatewayConfig` (
  `GatewayConfigID` int(11) NOT NULL AUTO_INCREMENT,
  `GatewayID` int(11) NOT NULL,
  `Title` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Status` tinyint(3) unsigned NOT NULL,
  `Created_at` datetime DEFAULT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`GatewayConfigID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblGlobalAdmin
CREATE TABLE IF NOT EXISTS `tblGlobalAdmin` (
  `GlobalAdminID` int(11) NOT NULL AUTO_INCREMENT,
  `FirstName` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `LastName` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `EmailAddress` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `password` longtext COLLATE utf8_unicode_ci NOT NULL,
  `Status` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `remember_token` longtext COLLATE utf8_unicode_ci,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`GlobalAdminID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblGlobalSetting
CREATE TABLE IF NOT EXISTS `tblGlobalSetting` (
  `GlobalSettingID` int(11) NOT NULL AUTO_INCREMENT,
  `Key` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Value` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`GlobalSettingID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblIP
CREATE TABLE IF NOT EXISTS `tblIP` (
  `Account` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `IP` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblJob
CREATE TABLE IF NOT EXISTS `tblJob` (
  `JobID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `AccountID` int(11) DEFAULT '0',
  `ProcessID` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `JobTypeID` tinyint(3) unsigned NOT NULL,
  `JobStatusID` tinyint(3) unsigned NOT NULL,
  `JobLoggedUserID` int(11) NOT NULL,
  `TemplateID` int(11) DEFAULT NULL,
  `Title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Description` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Options` longtext COLLATE utf8_unicode_ci,
  `JobStatusMessage` longtext COLLATE utf8_unicode_ci,
  `EmailSentStatus` tinyint(3) unsigned DEFAULT '0',
  `EmailSentStatusMessage` longtext COLLATE utf8_unicode_ci,
  `OutputFilePath` longtext COLLATE utf8_unicode_ci,
  `HasRead` tinyint(3) unsigned DEFAULT '0',
  `ShowInCounter` tinyint(3) unsigned DEFAULT '1',
  `LogID` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PID` int(11) DEFAULT NULL,
  `LastRunTime` datetime DEFAULT NULL,
  PRIMARY KEY (`JobID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblJobFile
CREATE TABLE IF NOT EXISTS `tblJobFile` (
  `JobFileID` int(11) NOT NULL AUTO_INCREMENT,
  `JobID` int(11) NOT NULL,
  `FileName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `FilePath` longtext COLLATE utf8_unicode_ci,
  `HttpPath` tinyint(1) NOT NULL DEFAULT '0',
  `Options` longtext COLLATE utf8_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`JobFileID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblJobStatus
CREATE TABLE IF NOT EXISTS `tblJobStatus` (
  `JobStatusID` int(11) NOT NULL AUTO_INCREMENT,
  `Code` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `Title` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CreatedDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `ModifiedDate` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`JobStatusID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblJobType
CREATE TABLE IF NOT EXISTS `tblJobType` (
  `JobTypeID` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `Code` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `Title` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CreatedDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `ModifiedDate` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`JobTypeID`),
  UNIQUE KEY `UNIQUE_Code` (`Code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblLastPrefixNo
CREATE TABLE IF NOT EXISTS `tblLastPrefixNo` (
  `CompanyID` int(11) DEFAULT NULL,
  `LastPrefixNo` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblLog
CREATE TABLE IF NOT EXISTS `tblLog` (
  `LogID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `ProcessID` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Process` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Message` longtext COLLATE utf8_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`LogID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblNote
CREATE TABLE IF NOT EXISTS `tblNote` (
  `NoteID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `Title` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Note` longtext COLLATE utf8_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`NoteID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblPaymentGateway
CREATE TABLE IF NOT EXISTS `tblPaymentGateway` (
  `PaymentGatewayID` int(11) NOT NULL AUTO_INCREMENT,
  `Title` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CompanyID` int(11) DEFAULT NULL,
  `Status` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`PaymentGatewayID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblPaymentUploadTemplate
CREATE TABLE IF NOT EXISTS `tblPaymentUploadTemplate` (
  `PaymentUploadTemplateID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `Title` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Options` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TemplateFile` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`PaymentUploadTemplateID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblPermission
CREATE TABLE IF NOT EXISTS `tblPermission` (
  `PermissionID` int(11) NOT NULL AUTO_INCREMENT,
  `role` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `action` longtext COLLATE utf8_unicode_ci,
  `resource` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`PermissionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblRate
CREATE TABLE IF NOT EXISTS `tblRate` (
  `RateID` int(11) NOT NULL AUTO_INCREMENT,
  `CountryID` int(11) DEFAULT NULL,
  `CompanyID` int(11) DEFAULT NULL,
  `CodeDeckId` int(11) DEFAULT NULL,
  `Code` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Country__tobe_delete` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Interval1` int(11) DEFAULT '1',
  `IntervalN` int(11) DEFAULT '1',
  PRIMARY KEY (`RateID`),
  KEY `IX_tblRate_companyId_codedeckid` (`CompanyID`,`CodeDeckId`,`RateID`,`CountryID`,`Code`,`Description`),
  KEY `IX_country_company_codedeck` (`CountryID`,`CompanyID`,`CodeDeckId`),
  KEY `IX_tblrate_code` (`Code`),
  KEY `IX_tblrate_CodeDescription` (`RateID`,`CompanyID`,`CountryID`,`Code`,`Description`),
  KEY `IX_tblRate_CompanyCodeDeckIdCode` (`CompanyID`,`CodeDeckId`,`Code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblRateDevTmp
CREATE TABLE IF NOT EXISTS `tblRateDevTmp` (
  `RateID` int(11) NOT NULL AUTO_INCREMENT,
  `CountryID` int(11) DEFAULT NULL,
  `CompanyID` int(11) DEFAULT NULL,
  `CodeDeckId` int(11) DEFAULT NULL,
  `Code` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Interval1` int(11) DEFAULT '1',
  `IntervalN` int(11) DEFAULT '1',
  PRIMARY KEY (`RateID`),
  KEY `Index 3` (`Code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblRateGenerator
CREATE TABLE IF NOT EXISTS `tblRateGenerator` (
  `RateGeneratorId` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `CodeDeckId` int(11) DEFAULT NULL,
  `RateGeneratorName` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `TrunkID` int(11) DEFAULT NULL,
  `RatePosition` int(11) NOT NULL DEFAULT '1',
  `RateTableId` int(11) DEFAULT NULL,
  `UseAverage` tinyint(1) NOT NULL DEFAULT '0',
  `UsePreference` tinyint(1) DEFAULT NULL,
  `Sources` varchar(50) COLLATE utf8_unicode_ci DEFAULT 'All',
  `Status` tinyint(3) unsigned DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CurrencyID` int(11) DEFAULT NULL,
  PRIMARY KEY (`RateGeneratorId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblRateRule
CREATE TABLE IF NOT EXISTS `tblRateRule` (
  `RateRuleId` int(11) NOT NULL AUTO_INCREMENT,
  `RateGeneratorId` int(11) NOT NULL,
  `Code` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`RateRuleId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblRateRuleMargin
CREATE TABLE IF NOT EXISTS `tblRateRuleMargin` (
  `RateRuleMarginId` int(11) NOT NULL AUTO_INCREMENT,
  `RateRuleId` int(11) NOT NULL,
  `MinRate` decimal(18,2) DEFAULT '0.00',
  `MaxRate` decimal(18,2) DEFAULT '0.00',
  `AddMargin` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`RateRuleMarginId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblRateRuleSource
CREATE TABLE IF NOT EXISTS `tblRateRuleSource` (
  `RateRuleSourceId` int(11) NOT NULL AUTO_INCREMENT,
  `RateRuleId` int(11) NOT NULL,
  `AccountId` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`RateRuleSourceId`),
  KEY `IX_RateRuleId_AccountID` (`AccountId`,`RateRuleId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblRateSheet
CREATE TABLE IF NOT EXISTS `tblRateSheet` (
  `RateSheetID` int(11) NOT NULL AUTO_INCREMENT,
  `CustomerID` int(11) NOT NULL,
  `RateSheet` longblob,
  `DateGenerated` datetime NOT NULL,
  `FileName` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Level` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `GeneratedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`RateSheetID`),
  KEY `IX_tblRateSheet_CustomerID_Level_69B7F` (`CustomerID`,`Level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblRateSheetArchive
CREATE TABLE IF NOT EXISTS `tblRateSheetArchive` (
  `RateSheetID` int(11) NOT NULL AUTO_INCREMENT,
  `CustomerID` int(11) NOT NULL,
  `RateSheet` longblob,
  `DateGenerated` datetime NOT NULL,
  `FileName` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Level` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `GeneratedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`RateSheetID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblRateSheetDetails
CREATE TABLE IF NOT EXISTS `tblRateSheetDetails` (
  `RateSheetDetailsID` int(11) NOT NULL AUTO_INCREMENT,
  `RateID` int(11) NOT NULL,
  `RateSheetID` int(11) NOT NULL,
  `Destination` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Code` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Rate` decimal(18,6) NOT NULL DEFAULT '0.000000',
  `Change` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `EffectiveDate` datetime NOT NULL,
  `Interval1` int(11) DEFAULT NULL,
  `IntervalN` int(11) DEFAULT NULL,
  PRIMARY KEY (`RateSheetDetailsID`),
  KEY `IX_tblRateSheetDetails_RateSheetID_DBEE5` (`RateSheetID`,`RateSheetDetailsID`),
  KEY `IX_tblRateSheetDetails_RateSheetID_77B8B` (`RateSheetID`,`RateSheetDetailsID`),
  KEY `IX_tblRateSheetDetails_RateID` (`RateID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblRateSheetDetailsArchive
CREATE TABLE IF NOT EXISTS `tblRateSheetDetailsArchive` (
  `RateSheetDetailsID` int(11) NOT NULL AUTO_INCREMENT,
  `RateID` int(11) NOT NULL,
  `RateSheetID` int(11) NOT NULL,
  `Destination` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Code` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Rate` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `Change` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `EffectiveDate` datetime NOT NULL,
  PRIMARY KEY (`RateSheetDetailsID`),
  KEY `FK_tblRateSheetDetailsArchive_tblRateSheetArchive` (`RateSheetID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblRateSheetFormate
CREATE TABLE IF NOT EXISTS `tblRateSheetFormate` (
  `RateSheetFormateID` int(11) NOT NULL AUTO_INCREMENT,
  `Title` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Customer` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Vendor` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `Status` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `UpdatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`RateSheetFormateID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblRateSheetHistory
CREATE TABLE IF NOT EXISTS `tblRateSheetHistory` (
  `RateSheetHistoryID` int(11) NOT NULL AUTO_INCREMENT,
  `JobID` int(11) NOT NULL,
  `Title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Description` longtext COLLATE utf8_unicode_ci,
  `Type` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`RateSheetHistoryID`),
  KEY `IX_tblRateSheetHistory_JobID_Type_6D56E` (`JobID`,`Type`,`RateSheetHistoryID`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblRateTable
CREATE TABLE IF NOT EXISTS `tblRateTable` (
  `RateTableId` bigint(20) NOT NULL AUTO_INCREMENT,
  `CompanyId` int(11) NOT NULL,
  `CodeDeckId` int(11) NOT NULL,
  `RateTableName` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `RateGeneratorID` int(11) NOT NULL,
  `TrunkID` int(11) NOT NULL,
  `Status` tinyint(3) unsigned DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CurrencyID` int(11) DEFAULT NULL,
  PRIMARY KEY (`RateTableId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblRateTableRate
CREATE TABLE IF NOT EXISTS `tblRateTableRate` (
  `RateTableRateID` bigint(20) NOT NULL AUTO_INCREMENT,
  `RateID` int(11) NOT NULL,
  `RateTableId` bigint(20) NOT NULL,
  `Rate` decimal(18,6) NOT NULL DEFAULT '0.000000',
  `EffectiveDate` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PreviousRate` decimal(18,6) DEFAULT NULL,
  `Interval1` int(11) DEFAULT NULL,
  `IntervalN` int(11) DEFAULT NULL,
  `ConnectionFee` decimal(18,6) DEFAULT NULL,
  PRIMARY KEY (`RateTableRateID`),
  KEY `FK_tblRateTableRate_tblRate` (`RateID`),
  KEY `IX_RateTableRate_RateID` (`RateID`),
  KEY `XI_RateID_RatetableID` (`RateID`,`RateTableRateID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblRateTableRateArchive
CREATE TABLE IF NOT EXISTS `tblRateTableRateArchive` (
  `RateTableRateArchiveID` int(11) NOT NULL AUTO_INCREMENT,
  `RateTableRateID` int(11) NOT NULL,
  `RateTableId` int(11) NOT NULL,
  `RateId` int(11) NOT NULL,
  `Rate` decimal(18,6) NOT NULL,
  `EffectiveDate` datetime NOT NULL,
  `CreatedDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `Notes` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`RateTableRateArchiveID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblRate_Backup
CREATE TABLE IF NOT EXISTS `tblRate_Backup` (
  `RateID` int(11) NOT NULL,
  `CountryID` int(11) DEFAULT NULL,
  `CompanyID` int(11) DEFAULT NULL,
  `CodeDeckId` int(11) DEFAULT NULL,
  `Code` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Country__tobe_delete` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Interval1` int(11) DEFAULT '1',
  `IntervalN` int(11) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblResource
CREATE TABLE IF NOT EXISTS `tblResource` (
  `ResourceID` int(11) NOT NULL AUTO_INCREMENT,
  `ResourceName` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ResourceValue` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CompanyID` int(11) NOT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime(3) DEFAULT NULL,
  `updated_at` datetime(3) DEFAULT NULL,
  `CategoryID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ResourceID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblResourceCategories
CREATE TABLE IF NOT EXISTS `tblResourceCategories` (
  `ResourceCategoryID` int(11) NOT NULL AUTO_INCREMENT,
  `ResourceCategoryName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CompanyID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ResourceCategoryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblRole
CREATE TABLE IF NOT EXISTS `tblRole` (
  `RoleID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `RoleName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Active` tinyint(3) unsigned DEFAULT NULL,
  `CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`RoleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblRolePermission
CREATE TABLE IF NOT EXISTS `tblRolePermission` (
  `RolePermissionID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `roleID` int(11) NOT NULL,
  `resourceID` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`RolePermissionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblTags
CREATE TABLE IF NOT EXISTS `tblTags` (
  `TagID` int(11) NOT NULL AUTO_INCREMENT,
  `TagName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `CompanyID` int(11) DEFAULT NULL,
  `TagType` int(11) DEFAULT NULL,
  PRIMARY KEY (`TagID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblTaxRate
CREATE TABLE IF NOT EXISTS `tblTaxRate` (
  `TaxRateId` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyId` int(11) DEFAULT NULL,
  `Title` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Amount` decimal(18,2) NOT NULL,
  `TaxType` tinyint(3) unsigned DEFAULT NULL,
  `FlatStatus` tinyint(3) unsigned DEFAULT NULL,
  `Status` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`TaxRateId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblTempCodeDeck
CREATE TABLE IF NOT EXISTS `tblTempCodeDeck` (
  `TempCodeDeckRateID` int(11) NOT NULL AUTO_INCREMENT,
  `CountryId` int(11) DEFAULT NULL,
  `CompanyId` int(11) NOT NULL,
  `CodeDeckId` int(11) DEFAULT NULL,
  `ProcessId` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Code` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Description` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Country` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Action` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Interval1` int(11) DEFAULT NULL,
  `IntervalN` int(11) DEFAULT NULL,
  PRIMARY KEY (`TempCodeDeckRateID`),
  KEY `PK_tblTempCodeDeck` (`ProcessId`,`Code`,`CompanyId`,`CodeDeckId`),
  KEY `PK_tblTempCdprocess` (`ProcessId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblTempCustomerRate
CREATE TABLE IF NOT EXISTS `tblTempCustomerRate` (
  `TempCustomerRateID` int(11) NOT NULL AUTO_INCREMENT,
  `Select` tinyint(1) DEFAULT '0',
  `RateID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `Rate` decimal(18,4) DEFAULT '0.0000',
  `EffectiveDate` date DEFAULT NULL,
  `PreviousRate` decimal(18,4) DEFAULT '0.0000',
  `Trunk` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`TempCustomerRateID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblTemplate
CREATE TABLE IF NOT EXISTS `tblTemplate` (
  `TemplateID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `Title` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CreatedDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `ModifiedDate` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`TemplateID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblTempRateTableRate
CREATE TABLE IF NOT EXISTS `tblTempRateTableRate` (
  `TempRateTableRateID` int(11) NOT NULL AUTO_INCREMENT,
  `CodeDeckId` int(11) DEFAULT NULL,
  `Code` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Rate` decimal(18,6) NOT NULL DEFAULT '0.000000',
  `EffectiveDate` datetime NOT NULL,
  `Change` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ProcessId` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Preference` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ConnectionFee` decimal(18,6) DEFAULT NULL,
  `Interval1` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `IntervalN` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`TempRateTableRateID`),
  KEY `IX_tblTempRateTableRate_Code_Change_ProcessId_5D43F` (`Code`,`Change`,`ProcessId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblTempVendorRate
CREATE TABLE IF NOT EXISTS `tblTempVendorRate` (
  `TempVendorRateID` int(11) NOT NULL AUTO_INCREMENT,
  `CodeDeckId` int(11) DEFAULT NULL,
  `Code` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Rate` decimal(18,6) NOT NULL DEFAULT '0.000000',
  `EffectiveDate` datetime NOT NULL,
  `Change` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ProcessId` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Preference` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ConnectionFee` decimal(18,6) DEFAULT NULL,
  `Interval1` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `IntervalN` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`TempVendorRateID`),
  KEY `IX_tblTempVendorRate_Code_Change_ProcessId_5D43F` (`Code`,`Change`,`ProcessId`),
  KEY `IX_tblTempVendorRateCodedeckCodeProcessID` (`CodeDeckId`,`Code`,`ProcessId`),
  KEY `IX_tblTempVendorRateProcessID` (`ProcessId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblTrunk
CREATE TABLE IF NOT EXISTS `tblTrunk` (
  `TrunkID` int(11) NOT NULL AUTO_INCREMENT,
  `Trunk` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `CompanyId` int(11) NOT NULL,
  `RatePrefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AreaPrefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Prefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Status` tinyint(1) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`TrunkID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblUploadedFiles
CREATE TABLE IF NOT EXISTS `tblUploadedFiles` (
  `UploadedFileID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `UserID` int(11) DEFAULT NULL,
  `UploadedFileName` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `UploadedFilePath` longtext COLLATE utf8_unicode_ci,
  `UploadedFileHttpPath` tinyint(4) DEFAULT NULL,
  `created_at` datetime(3) DEFAULT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime(3) DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`UploadedFileID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblUsage
CREATE TABLE IF NOT EXISTS `tblUsage` (
  `PKUsage` bigint(20) NOT NULL AUTO_INCREMENT,
  `AccountID` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AccountName` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AreaPrefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AreaName` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TotalCharges` decimal(18,3) DEFAULT '0.000',
  `TotalDuration` int(11) DEFAULT NULL,
  `NoOfCDR` int(11) DEFAULT NULL,
  `Minutes` decimal(18,3) DEFAULT NULL,
  `AVGRatePerMin` decimal(18,6) DEFAULT NULL,
  `PeriodFrom` datetime DEFAULT NULL,
  `PeriodTo` datetime DEFAULT NULL,
  `InvoiceCompanyID` int(11) DEFAULT NULL,
  PRIMARY KEY (`PKUsage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblUser
CREATE TABLE IF NOT EXISTS `tblUser` (
  `UserID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `FirstName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `LastName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `EmailAddress` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` longtext COLLATE utf8_unicode_ci,
  `AdminUser` tinyint(1) DEFAULT '0',
  `AccountingUser` tinyint(1) DEFAULT NULL,
  `Status` int(11) DEFAULT '0',
  `Roles` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `EmailFooter` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblUserPermission
CREATE TABLE IF NOT EXISTS `tblUserPermission` (
  `UserPermissionID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `resourceID` int(11) NOT NULL,
  `AddRemove` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`UserPermissionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblUserProfile
CREATE TABLE IF NOT EXISTS `tblUserProfile` (
  `UserProfileID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `City` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `State` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PostCode` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Country` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Address1` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Address2` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Address3` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Picture` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Utc` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`UserProfileID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblUserRole
CREATE TABLE IF NOT EXISTS `tblUserRole` (
  `UserRoleID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) DEFAULT NULL,
  `RoleID` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`UserRoleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblVendorBlocking
CREATE TABLE IF NOT EXISTS `tblVendorBlocking` (
  `VendorBlockingId` int(11) NOT NULL AUTO_INCREMENT,
  `AccountId` int(11) NOT NULL,
  `CountryId` int(11) DEFAULT NULL,
  `RateId` int(11) DEFAULT NULL,
  `TrunkID` int(11) NOT NULL,
  `BlockedBy` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `BlockedDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`VendorBlockingId`),
  KEY `IX_tblVendorBlocking_AccountId` (`AccountId`),
  KEY `IX_tblVendorBlocking_CountryId` (`CountryId`),
  KEY `IX_tblVendorBlocking_RateId` (`RateId`),
  KEY `IX_tblVendorBlocking_TrunkID` (`TrunkID`),
  KEY `IX_tblVendorBlocking_CountryId_TrunkID` (`AccountId`,`CountryId`,`TrunkID`),
  KEY `IX_tblVendorBlocking_TrunkID_4F42B` (`TrunkID`,`VendorBlockingId`,`RateId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblVendorCSVMapping
CREATE TABLE IF NOT EXISTS `tblVendorCSVMapping` (
  `VendorCSVMappingId` int(11) NOT NULL AUTO_INCREMENT,
  `VendorId` int(11) DEFAULT NULL,
  `ExcelColumn` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `MapTo` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`VendorCSVMappingId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblVendorFileUploadTemplate
CREATE TABLE IF NOT EXISTS `tblVendorFileUploadTemplate` (
  `VendorFileUploadTemplateID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `Title` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Options` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TemplateFile` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`VendorFileUploadTemplateID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblVendorPreference
CREATE TABLE IF NOT EXISTS `tblVendorPreference` (
  `VendorPreferenceID` int(11) NOT NULL AUTO_INCREMENT,
  `AccountId` int(11) NOT NULL,
  `Preference` int(11) NOT NULL,
  `RateId` int(11) DEFAULT NULL,
  `TrunkID` int(11) NOT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`VendorPreferenceID`),
  KEY `IX_AccountID_TrunkID_RateID` (`TrunkID`,`RateId`,`AccountId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblVendorRate
CREATE TABLE IF NOT EXISTS `tblVendorRate` (
  `VendorRateID` int(11) NOT NULL AUTO_INCREMENT,
  `AccountId` int(11) NOT NULL,
  `TrunkID` int(11) NOT NULL,
  `RateId` int(11) NOT NULL,
  `Rate` decimal(18,6) NOT NULL DEFAULT '0.000000',
  `EffectiveDate` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Interval1` int(11) DEFAULT NULL,
  `IntervalN` int(11) DEFAULT NULL,
  `ConnectionFee` decimal(18,6) DEFAULT NULL,
  `MinimumCost` decimal(18,6) DEFAULT NULL,
  PRIMARY KEY (`VendorRateID`),
  KEY `IX_tblVendorRate_RateId_TrunkID_EffectiveDate` (`AccountId`,`Rate`,`TrunkID`,`RateId`,`EffectiveDate`),
  KEY `IX_tblVendorRate_AccountId_TrunkID_9BBE2` (`AccountId`,`TrunkID`,`VendorRateID`,`RateId`,`Rate`,`EffectiveDate`,`updated_at`,`created_at`,`created_by`,`updated_by`,`Interval1`,`IntervalN`),
  KEY `IX_VendorRate_RateID` (`RateId`),
  KEY `IX_VendorRate_Accountid_EffectiveDate_TrunkID_RateID` (`AccountId`,`TrunkID`,`RateId`,`EffectiveDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblVendorRateArchive
CREATE TABLE IF NOT EXISTS `tblVendorRateArchive` (
  `VendorRateArchiveID` int(11) NOT NULL AUTO_INCREMENT,
  `VendorRateID` int(11) NOT NULL,
  `AccountId` int(11) NOT NULL,
  `TrunkId` int(11) NOT NULL,
  `RateId` int(11) NOT NULL,
  `Rate` decimal(18,6) NOT NULL,
  `EffectiveDate` datetime NOT NULL,
  `CreatedDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `Notes` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`VendorRateArchiveID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblVendorTrunk
CREATE TABLE IF NOT EXISTS `tblVendorTrunk` (
  `VendorTrunkID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `CodeDeckId` int(11) DEFAULT NULL,
  `AccountID` int(11) NOT NULL,
  `TrunkID` int(11) NOT NULL,
  `Prefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CompanyGatewayIDs` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Status` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `UseInBilling` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`VendorTrunkID`),
  KEY `IX_AccountID_TrunkID_Status` (`AccountID`,`TrunkID`,`Status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tblVIP
CREATE TABLE IF NOT EXISTS `tblVIP` (
  `Account` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `IP` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.test
CREATE TABLE IF NOT EXISTS `test` (
  `AccountName` text COLLATE utf8_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.tmptblcountry
CREATE TABLE IF NOT EXISTS `tmptblcountry` (
  `id` int(11) NOT NULL,
  `iso` char(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `nicename` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `iso3` char(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `numcode` smallint(6) DEFAULT NULL,
  `phonecode` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RateManagement4.TMPtblRate
CREATE TABLE IF NOT EXISTS `TMPtblRate` (
  `RateID` int(11) NOT NULL AUTO_INCREMENT,
  `CountryID` int(11) DEFAULT NULL,
  `CompanyID` int(11) DEFAULT NULL,
  `CodeDeckId` int(11) DEFAULT NULL,
  `Code` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Country__tobe_delete` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Interval1` int(11) DEFAULT '1',
  `IntervalN` int(11) DEFAULT '1',
  PRIMARY KEY (`RateID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
