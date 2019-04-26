ALTER TABLE `tblCLIRateTable` ADD COLUMN `PrefixWithoutCountry` VARCHAR(50) NULL DEFAULT NULL;
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






,
	IN `p_Type` VARCHAR(50),
	IN `p_City` VARCHAR(50),
	IN `p_Tariff` VARCHAR(50),
	IN `p_Country` INT


,
	IN `p_Package` INT

,
	IN `p_Prefix` VARCHAR(50)

)
BEGIN

	DECLARE v_RateTableType int;


	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET @@session.collation_connection='utf8_unicode_ci';
	SET @@session.character_set_results='utf8';
	
SELECT Type INTO v_RateTableType FROM  tblRateTable WHERE RateTableID = p_rateTableID;




IF v_RateTableType = 2  THEN
	select rate.Code as OriginationCode,ratetimeZone.Title as TimeTitle, didRate.* from tblRateTableDIDRate didRate
  join tblRate rate on rate.RateID = didRate.RateID
  join tblTimezones ratetimeZone on ratetimeZone.TimezonesID = didRate.TimezonesID and rate.CountryID = p_Country
  where RateTableID = p_rateTableID and IFNULL(rate.Code,'') = p_Prefix and IFNULL(rate.Type,'')=p_Type and IFNULL(didRate.City,'') = p_City and IFNULL(didRate.Tariff,'') = p_Tariff and  EffectiveDate >= NOW() order by RateTableDIDRateID desc;
END IF; 

IF v_RateTableType = 3  THEN
	select name into @packageName from tblPackage where PackageId = p_Package;
	select pkgRate.* from tblRateTablePKGRate pkgRate join tblRate rate on rate.RateID = pkgRate.RateID
	  where rate.Code = @packageName and RateTableID = p_rateTableID order by RateTablePKGRateID and EffectiveDate >= NOW() desc ;
END IF;

END//
DELIMITER ;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
