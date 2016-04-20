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

-- Dumping database structure for RMCDR4
CREATE DATABASE IF NOT EXISTS `RMCDR4` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `RMCDR4`;


-- Dumping structure for table RMCDR4.tblTempUsageDetail
CREATE TABLE IF NOT EXISTS `tblTempUsageDetail` (
  `TempUsageDetailID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `CompanyGatewayID` int(11) DEFAULT NULL,
  `GatewayAccountID` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `connect_time` datetime DEFAULT NULL,
  `disconnect_time` datetime DEFAULT NULL,
  `billed_duration` int(11) DEFAULT NULL,
  `trunk` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `area_prefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cli` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cld` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cost` double DEFAULT NULL,
  `ProcessID` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ID` int(11) DEFAULT NULL,
  `remote_ip` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `pincode` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `extension` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`TempUsageDetailID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RMCDR4.tblTempVendorCDR
CREATE TABLE IF NOT EXISTS `tblTempVendorCDR` (
  `TempVendorCDRID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `CompanyGatewayID` int(11) DEFAULT NULL,
  `GatewayAccountID` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `billed_duration` int(11) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `ID` int(11) DEFAULT NULL,
  `selling_cost` double DEFAULT NULL,
  `buying_cost` double DEFAULT NULL,
  `connect_time` datetime DEFAULT NULL,
  `disconnect_time` datetime DEFAULT NULL,
  `cli` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cld` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `trunk` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `area_prefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `remote_ip` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ProcessID` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`TempVendorCDRID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RMCDR4.tblUsageDetailFailedCall
CREATE TABLE IF NOT EXISTS `tblUsageDetailFailedCall` (
  `UsageDetailFailedCallID` int(11) NOT NULL AUTO_INCREMENT,
  `UsageHeaderID` int(11) NOT NULL,
  `connect_time` datetime DEFAULT NULL,
  `disconnect_time` datetime DEFAULT NULL,
  `billed_duration` int(11) DEFAULT NULL,
  `area_prefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cli` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cld` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cost` double DEFAULT NULL,
  `remote_ip` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `trunk` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ProcessID` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ID` int(11) DEFAULT NULL,
  `extension` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pincode` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_inbound` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`UsageDetailFailedCallID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RMCDR4.tblUsageDetails
CREATE TABLE IF NOT EXISTS `tblUsageDetails` (
  `UsageDetailID` int(11) NOT NULL AUTO_INCREMENT,
  `UsageHeaderID` int(11) NOT NULL,
  `connect_time` datetime DEFAULT NULL,
  `disconnect_time` datetime DEFAULT NULL,
  `billed_duration` int(11) DEFAULT NULL,
  `area_prefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cli` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cld` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cost` decimal(18,6) DEFAULT NULL,
  `remote_ip` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `trunk` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ProcessID` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ID` int(11) DEFAULT NULL,
  `DailySummaryStatus` tinyint(1) DEFAULT NULL,
  `extension` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pincode` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_inbound` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`UsageDetailID`),
  KEY `IXUsageDetailCMP_GaTGatACPrID` (`UsageHeaderID`),
  KEY `Index_ID` (`ID`),
  KEY `Index_ProcessID` (`ProcessID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RMCDR4.tblUsageHeader
CREATE TABLE IF NOT EXISTS `tblUsageHeader` (
  `UsageHeaderID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `CompanyGatewayID` int(11) DEFAULT NULL,
  `GatewayAccountID` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `StartDate` datetime DEFAULT NULL,
  `DailySummaryStatus` tinyint(3) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `VendorCDRStatus` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`UsageHeaderID`),
  KEY `Index_Com_GA_CG_A` (`CompanyID`,`GatewayAccountID`,`CompanyGatewayID`,`AccountID`),
  KEY `Index_A_STD_CG` (`AccountID`,`StartDate`,`CompanyGatewayID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RMCDR4.tblVendorCDR
CREATE TABLE IF NOT EXISTS `tblVendorCDR` (
  `VendorCDRID` int(11) NOT NULL AUTO_INCREMENT,
  `VendorCDRHeaderID` int(11) NOT NULL,
  `connect_time` datetime DEFAULT NULL,
  `disconnect_time` datetime DEFAULT NULL,
  `billed_duration` int(11) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `ID` int(11) DEFAULT NULL,
  `selling_cost` decimal(18,6) DEFAULT NULL,
  `buying_cost` decimal(18,6) DEFAULT NULL,
  `cli` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cld` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `trunk` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `area_prefix` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `remote_ip` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ProcessID` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`VendorCDRID`),
  KEY `IX_VendorCDRHeaderID` (`VendorCDRHeaderID`),
  KEY `IX_ProcessID` (`ProcessID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RMCDR4.tblVendorCDRHeader
CREATE TABLE IF NOT EXISTS `tblVendorCDRHeader` (
  `VendorCDRHeaderID` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyID` int(11) DEFAULT NULL,
  `CompanyGatewayID` int(11) DEFAULT NULL,
  `GatewayAccountID` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `StartDate` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`VendorCDRHeaderID`),
  KEY `Index_Com_GA_CG_A` (`CompanyID`,`CompanyGatewayID`,`GatewayAccountID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.


-- Dumping structure for table RMCDR4.tempcdrs
CREATE TABLE IF NOT EXISTS `tempcdrs` (
  `Caller` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Original CLI` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CLI` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Original CLD` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CLD` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Billing Prefix` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Country` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Description` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Setup Time` datetime DEFAULT NULL,
  `Connect Time` datetime DEFAULT NULL,
  `Disconnect Time` datetime DEFAULT NULL,
  `Duration, sec` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Billed Duration, sec` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Cost` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Currency` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Result` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Remote IP` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `LRN Original CLD` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `LRN CLD` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Area Name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Error Message` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY `Index 1` (`CLD`),
  KEY `Index 2` (`Connect Time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
