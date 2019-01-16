-- --------------------------------------------------------
-- Host:                         188.227.186.98
-- Server version:               5.7.18 - MySQL Community Server (GPL)
-- Server OS:                    Linux
-- HeidiSQL Version:             9.5.0.5196
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for speakintelligentRM
CREATE DATABASE IF NOT EXISTS `speakintelligentRM` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `speakintelligentRM`;

-- Dumping structure for table speakintelligentRM.tblAccountServiceContract
CREATE TABLE IF NOT EXISTS `tblAccountServiceContract` (
  `AccountServiceContractID` int(11) NOT NULL AUTO_INCREMENT,
  `AccountServiceID` int(11) DEFAULT NULL,
  `ContractStartDate` date NOT NULL,
  `ContractEndDate` date NOT NULL,
  `Duration` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `ContractReason` varchar(50) COLLATE utf8_unicode_ci DEFAULT '0',
  `AutoRenewal` int(11) DEFAULT '0',
  `ContractTerm` decimal(10,0) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`AccountServiceContractID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
