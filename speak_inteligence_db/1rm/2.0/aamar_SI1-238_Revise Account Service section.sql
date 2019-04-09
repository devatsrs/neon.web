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
  where RateTableID = p_rateTableID and IFNULL(rate.Type,'')=p_Type and IFNULL(didRate.CityTariff,'') = p_City and  EffectiveDate >= NOW() order by RateTableDIDRateID desc;
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
	
	ALTER TABLE `tblCLIRateTable`
	ADD COLUMN `AccessDiscountPlanID` INT(11) NULL DEFAULT NULL AFTER `CLI`;
	
	-- --------------------------------------------------------
-- Host:                         78.129.140.6
-- Server version:               5.7.25 - MySQL Community Server (GPL)
-- Server OS:                    Linux
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
DECLARE v_TotalPackage int;
DECLARE v_TotalNumber int;


select count(Status) into v_ActivePackage from tblAccountServicePackage
 where status = 1 and AccountServiceID = p_AccountServiceID and AccountID = p_AccountID;
 
 select count(Status) into v_ActiveNumber from tblCLIRateTable
 where status = 1 and AccountServiceID = p_AccountServiceID and AccountID = p_AccountID;
 
 select count(Status) into v_TotalPackage from tblAccountServicePackage
 where AccountServiceID = p_AccountServiceID and AccountID = p_AccountID;
 
 select count(Status) into v_TotalNumber from tblCLIRateTable
 where AccountServiceID = p_AccountServiceID and AccountID = p_AccountID;
 
 IF (v_ActivePackage = v_TotalPackage and v_ActiveNumber = v_TotalNumber) THEN
 	return 1;
 END IF;
 
 return 0 ;
END//
DELIMITER ;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

  
 
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

-- Dumping structure for procedure speakintelligentRM.prcGetAccountServiceData
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prcGetAccountServiceData`(
	IN `p_AccountID` INT
,
	IN `p_Number` INT,
	IN `p_PackageID` INT,
	IN `p_AccountServiceOrderID` INT





,
	IN `p_RowspPage` INT
,
	IN `p_PageNumber` INT,
	IN `p_Export` INT
)
BEGIN

DECLARE v_OffSet_ int;
DECLARE v_totalcount int;
SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
DROP TEMPORARY TABLE IF EXISTS tmp_AccountServiceData;
		CREATE TEMPORARY TABLE tmp_AccountServiceData  (
			AccountServiceID INT,
			Numbers VARCHAR(500) COLLATE utf8_unicode_ci,
			Packages VARCHAR(500) COLLATE utf8_unicode_ci,
			AccountServiceOrder INT,
			AccountID INT
		);

DROP TEMPORARY TABLE IF EXISTS tmp_AccountServicePackage;
		CREATE TEMPORARY TABLE tmp_AccountServicePackage  (
			AccountServiceID INT,
			Packages VARCHAR(500) COLLATE utf8_unicode_ci,
			AccountServiceOrder INT,
			AccountID INT
		);
		
		DROP TEMPORARY TABLE IF EXISTS tmp_IAccountServicePackage;
		CREATE TEMPORARY TABLE tmp_IAccountServicePackage  (
			AccountServiceID INT,
			Packages VARCHAR(500) COLLATE utf8_unicode_ci,
			Numbers VARCHAR(500) COLLATE utf8_unicode_ci,
			AccountServiceOrder INT,
			AccountID INT
		);
insert into tmp_AccountServiceData (Numbers,AccountServiceID,AccountID)
select GROUP_CONCAT(distinct cliTable.CLI), tblAccountService.AccountServiceID,tblAccountService.AccountID from tblCLIRateTable cliTable,tblAccountService
where cliTable.AccountServiceID = tblAccountService.AccountServiceID and tblAccountService.AccountID = cliTable.AccountID and cliTable.AccountID = p_AccountID and IF(p_Number = 0,0,p_Number) = IF(p_Number = 0,0,cliTable.CLI)
group by tblAccountService.AccountServiceID,tblAccountService.AccountID;

insert into tmp_AccountServicePackage (Packages,AccountServiceID,AccountID)
select GROUP_CONCAT(distinct packName.Name), tblAccountService.AccountServiceID,tblAccountService.AccountID from tblAccountServicePackage packageTable,tblAccountService,tblPackage packName where
packageTable.AccountServiceID = tblAccountService.AccountServiceID
and packName.PackageId = packageTable.PackageId
 and packageTable.AccountID = p_AccountID and tblAccountService.AccountID = packageTable.AccountID and IF(p_PackageID = 0,0,p_PackageID) = IF(p_PackageID = 0,0,packageTable.PackageId)
 group by tblAccountService.AccountServiceID,tblAccountService.AccountID;
 


 update tmp_AccountServiceData 
 inner join tmp_AccountServicePackage on  tmp_AccountServiceData.AccountServiceID = tmp_AccountServicePackage.AccountServiceID
 
 SET tmp_AccountServiceData.Packages = tmp_AccountServicePackage.Packages;
 
 insert into tmp_IAccountServicePackage (Packages,AccountServiceID,AccountID)
 select Packages,AccountServiceID,AccountID from tmp_AccountServicePackage where AccountServiceID not in (select AccountServiceID from tmp_AccountServiceData);
 
 insert into tmp_AccountServiceData (Packages,AccountServiceID,AccountID)
 select Packages,AccountServiceID,AccountID from tmp_IAccountServicePackage;
 
 update tmp_AccountServiceData 
 inner join tblAccountService on  tmp_AccountServiceData.AccountServiceID = tblAccountService.AccountServiceID 
 
 SET tmp_AccountServiceData.AccountServiceOrder = tblAccountService.ServiceOrderID;
 
 delete from tmp_IAccountServicePackage;
 insert into tmp_IAccountServicePackage (AccountServiceID,AccountID,AccountServiceOrder)
 select AccountServiceID,AccountID,AccountServiceID from tblAccountService where AccountID = p_AccountID and AccountServiceID not in (select AccountServiceID from tmp_AccountServiceData);
 
 insert into tmp_AccountServiceData (Packages,AccountServiceID,AccountID)
 select Packages,AccountServiceID,AccountID from tmp_IAccountServicePackage;
-- select * from tmp_IAccountServicePackage;
SET v_totalcount = (SELECT COUNT(*)  FROM tmp_AccountServiceData);
IF p_Export = 0 THEN
 select AccountServiceID,Numbers,Packages,AccountServiceOrder,AccountID from tmp_AccountServiceData LIMIT p_RowspPage OFFSET v_OffSet_; 
 ELSE
 select AccountServiceID,Numbers,Packages,AccountServiceOrder,AccountID from tmp_AccountServiceData; 
END IF; 
 -- select * from tmp_AccountServicePackage;
 
 SELECT
			v_totalcount AS totalcount
		
		;
END//
DELIMITER ;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
