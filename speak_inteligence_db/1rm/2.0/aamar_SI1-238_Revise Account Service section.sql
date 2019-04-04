-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.7.24-log - MySQL Community Server (GPL)
-- Server OS:                    Win64
-- HeidiSQL Version:             9.5.0.5196
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for procedure speakintelligentRM.prc_getRateTablesRateForAccountService
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getRateTablesRateForAccountService`(
	IN `p_rateTableID` INT






)
BEGIN

	DECLARE v_RateTableType int;


	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET @@session.collation_connection='utf8_unicode_ci';
	SET @@session.character_set_results='utf8';
	
SELECT Type INTO v_RateTableType FROM  tblRateTable WHERE RateTableID = p_rateTableID;




IF v_RateTableType = 2  THEN
	select rate.Code as OriginationCode, didRate.* from tblRateTableDIDRate didRate left join tblRate rate on rate.RateID = didRate.OriginationRateID  where RateTableID = p_rateTableID and EffectiveDate >= NOW() order by RateTableDIDRateID desc;
END IF;

IF v_RateTableType = 3  THEN
	select pkgRate.* from tblRateTablePKGRate pkgRate  where RateTableID = p_rateTableID order by RateTablePKGRateID and EffectiveDate >= NOW() desc ;
END IF;

END//
DELIMITER ;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;


ALTER TABLE `tblCLIRateTable`
	ADD COLUMN `TerminationRateTableID` INT(11) NULL DEFAULT NULL AFTER `RateTableID`;
ALTER TABLE `tblCLIRateTable`
	ADD COLUMN `TerminationDiscountPlanID` INT(11) NULL DEFAULT NULL AFTER `TerminationRateTableID`,
	ADD COLUMN `CountryID` INT(11) NULL DEFAULT NULL AFTER `TerminationDiscountPlanID`,
	ADD COLUMN `NumberStartDate` DATE NULL DEFAULT NULL AFTER `CountryID`,
	ADD COLUMN `NumberEndDate` DATE NULL DEFAULT NULL AFTER `NumberStartDate`;
ALTER TABLE `tblCLIRateTable`
	ADD COLUMN `ContractID` VARCHAR(50) NULL DEFAULT NULL AFTER `Prefix`,
	ADD COLUMN `City` VARCHAR(50) NULL DEFAULT NULL AFTER `ContractID`,
	ADD COLUMN `Tariff` VARCHAR(50) NULL DEFAULT NULL AFTER `City`;
	ALTER TABLE `tblCLIRateTable`
	DROP COLUMN `CityTariff`;

ALTER TABLE `tblAccountServicePackage`
	ADD COLUMN `PackageDiscountPlanID` INT(11),
	ADD COLUMN `PackageStartDate` DATE ,
	ADD COLUMN `PackageEndDate` DATE ;
	
	ALTER TABLE `tblAccountServicePackage`
	ADD COLUMN `ContractID` VARCHAR(50) ,
	ADD COLUMN `Status` INT(11) ;
	
	ALTER TABLE `tblAccountServicePackage`
	ADD COLUMN `ServiceID` INT(11) NULL DEFAULT NULL ;
	
	ALTER TABLE `tblAccountService`
	ADD COLUMN `ServiceOrderID` VARCHAR(50) NULL ;
	
	-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.7.24-log - MySQL Community Server (GPL)
-- Server OS:                    Win64
-- HeidiSQL Version:             9.5.0.5196
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for function speakintelligentRM.fnGetServiceStatusForAccountService
DELIMITER //
CREATE DEFINER=`root`@`localhost` FUNCTION `fnGetServiceStatusForAccountService`(
	`p_AccountID` INT,
	`p_AccountServiceID` INT,
	`p_status` INT
) RETURNS int(11)
BEGIN

DECLARE v_ActivePackage int;
DECLARE v_ActiveNumber int;


select count(Status) into v_ActivePackage from tblAccountServicePackage
 where status = 1 and AccountServiceID = p_AccountServiceID and AccountID = p_AccountID;
 
 select count(Status) into v_ActiveNumber from tblCLIRateTable
 where status = 1 and AccountServiceID = p_AccountServiceID and AccountID = p_AccountID;
 
 if (p_status = 1 AND (v_ActivePackage >= 1 OR v_ActiveNumber >= 1)) THEN
 		return 1 ;
 END IF;
 
 if (p_status = 0) THEN
 		return v_ActivePackage + v_ActiveNumber ;
 END IF;
 
 return 0 ;
END//
DELIMITER ;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
  
 