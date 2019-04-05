/*
SQLyog Ultimate v11.42 (64 bit)
MySQL - 5.7.25 : Database - speakintelligentRM
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
USE `speakintelligentRM`;

/*Table structure for table `eng_tblTempAccount` */

CREATE TABLE `eng_tblTempAccount` (
  `AccountID` int(11) NOT NULL AUTO_INCREMENT,
  `AccountType` tinyint(3) unsigned DEFAULT NULL,
  `CompanyId` int(11) DEFAULT NULL,
  `CurrencyId` int(11) DEFAULT NULL,
  `Number` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AccountName` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `IsVendor` tinyint(1) DEFAULT NULL,
  `IsCustomer` tinyint(1) DEFAULT NULL,
  `IsReseller` tinyint(1) DEFAULT NULL,
  `Status` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Action` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Country` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CustomerID` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`AccountID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `eng_tblTempCLIRateTable` */

CREATE TABLE `eng_tblTempCLIRateTable` (
  `CLIRateTableID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `CLI` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AccessDiscountPlanID` int(11) DEFAULT NULL,
  `RateTableID` int(11) DEFAULT NULL,
  `TerminationRateTableID` int(11) DEFAULT NULL,
  `TerminationDiscountPlanID` int(11) DEFAULT NULL,
  `CountryID` int(11) DEFAULT NULL,
  `NumberStartDate` date DEFAULT NULL,
  `NumberEndDate` date DEFAULT NULL,
  `ServiceID` int(11) DEFAULT '0',
  `AccountServiceID` int(11) DEFAULT '0',
  `PackageID` int(11) DEFAULT '0',
  `PackageRateTableID` int(11) DEFAULT '0',
  `Status` tinyint(4) DEFAULT '1',
  `Prefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ContractID` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `City` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Tariff` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `DIDCategoryID` int(11) DEFAULT '0',
  `VendorID` int(11) DEFAULT '0',
  `NoType` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Action` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`CLIRateTableID`)
) ENGINE=InnoDB AUTO_INCREMENT=177 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `eng_tblTempCurrency` */

CREATE TABLE `eng_tblTempCurrency` (
  `CurrencyId` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyId` int(11) NOT NULL,
  `Code` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `Symbol` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Status` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Action` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`CurrencyId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `eng_tblTempCurrencyConversion` */

CREATE TABLE `eng_tblTempCurrencyConversion` (
  `ConversionID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `CurrencyID` int(11) NOT NULL,
  `Value` decimal(18,6) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ModifiedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `EffectiveDate` datetime DEFAULT NULL,
  `Action` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ConversionID`),
  KEY `IX_CurrencyID_CompanyID` (`CurrencyID`,`CompanyID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `eng_tblTempCustomerTrunk` */

CREATE TABLE `eng_tblTempCustomerTrunk` (
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
  `RoutinePlanStatus` tinyint(3) unsigned DEFAULT NULL,
  `RateTableAssignDate` datetime DEFAULT NULL,
  `UseInBilling` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`CustomerTrunkID`),
  UNIQUE KEY `IX_AccountIDTrunkID_Unique` (`AccountID`,`TrunkID`),
  KEY `Index_AccountID_TrunkID_Status` (`TrunkID`,`AccountID`,`Status`),
  KEY `temp_index` (`AccountID`,`Status`),
  KEY `FK_tblCustomerTrunk_tblRateTable` (`RateTableID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `eng_tblTempDynamicFields` */

CREATE TABLE `eng_tblTempDynamicFields` (
  `DynamicFieldsID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL DEFAULT '0',
  `Type` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'account, product etc',
  `FieldDomType` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `FieldName` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `FieldSlug` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `FieldDescription` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `FieldOrder` int(11) NOT NULL DEFAULT '0',
  `Status` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ItemTypeID` int(11) DEFAULT '0',
  `Minimum` int(11) DEFAULT '0',
  `Maximum` int(11) DEFAULT '0',
  `DefaultValue` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `SelectVal` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`DynamicFieldsID`),
  KEY `IX_Type` (`Type`),
  KEY `CompanyID_Type_Status` (`CompanyID`,`Type`,`Status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `eng_tblTempDynamicFieldsDetail` */

CREATE TABLE `eng_tblTempDynamicFieldsDetail` (
  `DynamicFieldsDetailID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) NOT NULL DEFAULT '0',
  `DynamicFieldsID` int(11) NOT NULL DEFAULT '0',
  `FieldType` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT 'gateway, item, account, users etc',
  `Options` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'json = numeric field , limit etc',
  `FieldOrder` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`DynamicFieldsDetailID`),
  KEY `IX_DynamicFieldsID` (`DynamicFieldsID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `eng_tblTempRateTable` */

CREATE TABLE `eng_tblTempRateTable` (
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
  `RoundChargedAmount` int(11) DEFAULT NULL,
  `DIDCategoryID` int(11) DEFAULT NULL,
  `Type` int(11) NOT NULL DEFAULT '1',
  `MinimumCallCharge` decimal(18,6) DEFAULT NULL,
  `AppliedTo` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`RateTableId`),
  KEY `CompanyCodedeck` (`CompanyId`,`CodeDeckId`),
  KEY `DIDCategoryID` (`DIDCategoryID`),
  KEY `CurrencyID_Type_AppliedTo` (`CurrencyID`,`Type`,`AppliedTo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `eng_tblTempRateTableRate` */

CREATE TABLE `eng_tblTempRateTableRate` (
  `RateTableRateID` bigint(20) NOT NULL AUTO_INCREMENT,
  `OriginationRateID` int(11) DEFAULT NULL,
  `RateID` int(11) NOT NULL,
  `RateTableId` bigint(20) NOT NULL,
  `TimezonesID` int(11) NOT NULL DEFAULT '1',
  `Rate` decimal(18,6) NOT NULL DEFAULT '0.000000',
  `RateN` decimal(18,6) NOT NULL DEFAULT '0.000000',
  `EffectiveDate` date NOT NULL,
  `EndDate` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PreviousRate` decimal(18,6) DEFAULT NULL,
  `Interval1` int(11) DEFAULT NULL,
  `IntervalN` int(11) DEFAULT NULL,
  `ConnectionFee` decimal(18,6) DEFAULT NULL,
  `RoutingCategoryID` int(11) DEFAULT NULL,
  `Preference` int(11) DEFAULT NULL,
  `Blocked` tinyint(4) NOT NULL DEFAULT '0',
  `ApprovedStatus` tinyint(4) NOT NULL DEFAULT '1',
  `ApprovedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ApprovedDate` datetime DEFAULT NULL,
  `RateCurrency` int(11) DEFAULT NULL,
  `ConnectionFeeCurrency` int(11) DEFAULT NULL,
  `VendorID` int(11) DEFAULT NULL,
  `OriginationCode` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `DestinationCode` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Action` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`RateTableRateID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `eng_tblTempReseller` */

CREATE TABLE `eng_tblTempReseller` (
  `ResellerID` int(11) NOT NULL AUTO_INCREMENT,
  `ResellerName` varchar(155) COLLATE utf8_unicode_ci NOT NULL,
  `CompanyID` int(11) NOT NULL,
  `ChildCompanyID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `FirstName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `LastName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Email` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Password` longtext COLLATE utf8_unicode_ci NOT NULL,
  `Status` tinyint(1) NOT NULL DEFAULT '1',
  `AllowWhiteLabel` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Action` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ResellerID`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `eng_tblTempRoutingCategory` */

CREATE TABLE `eng_tblTempRoutingCategory` (
  `RoutingCategoryID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Description` text COLLATE utf8_unicode_ci,
  `CompanyID` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `UpdatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Order` int(11) DEFAULT NULL,
  `Action` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`RoutingCategoryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `eng_tblTempRoutingProfile` */

CREATE TABLE `eng_tblTempRoutingProfile` (
  `RoutingProfileID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Description` text COLLATE utf8_unicode_ci,
  `SelectionCode` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `RoutingPolicy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CompanyID` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `UpdatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Status` int(11) DEFAULT NULL,
  `Action` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`RoutingProfileID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `eng_tblTempRoutingProfileCategory` */

CREATE TABLE `eng_tblTempRoutingProfileCategory` (
  `RoutingProfileCategoryID` int(11) NOT NULL AUTO_INCREMENT,
  `RoutingProfileID` int(11) DEFAULT NULL,
  `RoutingCategoryID` int(11) DEFAULT NULL,
  `Order` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `Action` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`RoutingProfileCategoryID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `eng_tblTempRoutingProfileToCustomer` */

CREATE TABLE `eng_tblTempRoutingProfileToCustomer` (
  `RoutingProfileToCustomerID` int(11) NOT NULL AUTO_INCREMENT,
  `RoutingProfileID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `TrunkID` int(11) DEFAULT '0',
  `ServiceID` int(11) DEFAULT '0',
  `AccountServiceID` int(11) DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `Action` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`RoutingProfileToCustomerID`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=latin1;

/*Table structure for table `eng_tblTempVendorConnection` */

CREATE TABLE `eng_tblTempVendorConnection` (
  `VendorConnectionID` int(11) NOT NULL AUTO_INCREMENT,
  `AccountId` int(11) NOT NULL,
  `RateTypeID` int(11) DEFAULT NULL,
  `CompanyID` int(11) NOT NULL,
  `Name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `DIDCategoryID` int(11) DEFAULT NULL,
  `Active` tinyint(1) DEFAULT '0',
  `RateTableID` int(11) DEFAULT NULL,
  `TrunkID` int(11) DEFAULT NULL,
  `CLIRule` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CLDRule` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CallPrefix` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `IP` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Port` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Password` text COLLATE utf8_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PrefixCDR` tinyint(1) DEFAULT '0',
  `SipHeader` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AuthenticationMode` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Location` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Action` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`VendorConnectionID`)
) ENGINE=InnoDB AUTO_INCREMENT=134 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `eng_tblTempVendorTimezone` */

CREATE TABLE `eng_tblTempVendorTimezone` (
  `VendorTimezoneID` int(11) NOT NULL AUTO_INCREMENT,
  `Type` int(11) NOT NULL,
  `Country` int(11) DEFAULT NULL,
  `TimeZoneID` int(11) NOT NULL,
  `VendorID` int(11) NOT NULL,
  `FromTime` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `ToTime` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `DaysOfWeek` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `DaysOfMonth` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `Months` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `ApplyIF` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `Status` tinyint(4) NOT NULL,
  `created_at` datetime NOT NULL,
  `created_by` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `updated_at` datetime NOT NULL,
  `updated_by` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Action` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`VendorTimezoneID`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
