ALTER TABLE `tblCompany`
	ADD COLUMN `AccessComponents` VARCHAR(500) NULL DEFAULT NULL AFTER `Components`;
ALTER TABLE `tblCompany`
	ADD COLUMN `PackageComponents` VARCHAR(500) NULL AFTER `AccessComponents`;
		
ALTER TABLE `tblAccount`
	ADD COLUMN `IsAffiliateAccount` TINYINT(1) NULL AFTER `TaxRateID`,
	ADD COLUMN `CommissionPercentage` INT(11) NULL AFTER `IsAffiliateAccount`,
	ADD COLUMN `DurationMonths` INT(11) NULL AFTER `CommissionPercentage`;
ALTER TABLE `tblAccountServicePackage`
	ADD COLUMN `VendorID` INT(11) NULL DEFAULT '0' AFTER `SpecialPackageRateTableID`;

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
	IN `p_Number` VARCHAR(50),
	IN `p_PackageID` INT,
	IN `p_AccountServiceOrderID` INT





,
	IN `p_Affiliate` INT,
	IN `p_RowspPage` INT
,
	IN `p_PageNumber` INT,
	IN `p_Export` INT









)
BEGIN

DECLARE v_OffSet_ int;
DECLARE v_totalcount int;
DECLARE v_companyId int;
DECLARE v_PackageName VARCHAR(50);
SET v_OffSet_ = (p_PageNumber * p_RowspPage);
DROP TEMPORARY TABLE IF EXISTS tmp_AccountServiceData;
		CREATE TEMPORARY TABLE tmp_AccountServiceData  (
			AccountServiceID INT,
			Numbers VARCHAR(500) COLLATE utf8_unicode_ci,
			Packages VARCHAR(500) COLLATE utf8_unicode_ci,
			AccountServiceOrder VARCHAR(50),
			Affiliate VARCHAR(500) COLLATE utf8_unicode_ci,
			AffiliateAccount INT,
			AccountID INT
		);

DROP TEMPORARY TABLE IF EXISTS tmp_AccountServicePackage;
		CREATE TEMPORARY TABLE tmp_AccountServicePackage  (
			AccountServiceID INT,
			Packages VARCHAR(500) COLLATE utf8_unicode_ci,
			AccountServiceOrder VARCHAR(50),
			Affiliate VARCHAR(500) COLLATE utf8_unicode_ci,
			AffiliateAccount INT,
			AccountID INT
		);
		
		DROP TEMPORARY TABLE IF EXISTS tmp_IAccountServicePackage;
		CREATE TEMPORARY TABLE tmp_IAccountServicePackage  (
			AccountServiceID INT,
			Packages VARCHAR(500) COLLATE utf8_unicode_ci,
			Numbers VARCHAR(500) COLLATE utf8_unicode_ci,
			AccountServiceOrder VARCHAR(50),
			Affiliate VARCHAR(500) COLLATE utf8_unicode_ci,
			AffiliateAccount INT,
			AccountID INT
		);

SELECT CompanyId INTO v_companyId FROM  tblAccount WHERE AccountID = p_AccountID;
select Name into v_PackageName from tblPackage where PackageId = p_PackageID;		
insert into tmp_AccountServiceData (Numbers,AccountServiceID,AccountID,Affiliate,AffiliateAccount)
select GROUP_CONCAT(distinct cliTable.CLI), tblAccountService.AccountServiceID,tblAccountService.AccountID,(select AccountName from tblAccount where AccountID = IFNULL(tblAccountService.AffiliateAccount,0) ) as AccountName,IFNULL(tblAccountService.AffiliateAccount,0) as AffiliateAccount  from tblCLIRateTable cliTable,tblAccountService
where cliTable.AccountServiceID = tblAccountService.AccountServiceID and v_companyId = cliTable.CompanyID and tblAccountService.AccountID = cliTable.AccountID and cliTable.AccountID = p_AccountID and IF(p_Number = 0,0,p_Number) = IF(p_Number = 0,0,cliTable.CLI)
group by tblAccountService.AccountServiceID,tblAccountService.AccountID;

-- select "1",tmp_AccountServiceData.* from tmp_AccountServiceData;

insert into tmp_AccountServicePackage (Packages,AccountServiceID,AccountID,Affiliate,AffiliateAccount)
select GROUP_CONCAT(distinct packName.Name), tblAccountService.AccountServiceID,tblAccountService.AccountID,(select AccountName from tblAccount where AccountID = IFNULL(tblAccountService.AffiliateAccount,0) ) as AccountName,IFNULL(tblAccountService.AffiliateAccount,0) as AffiliateAccount from tblAccountServicePackage packageTable,tblAccountService,tblPackage packName where
packageTable.AccountServiceID = tblAccountService.AccountServiceID
and packName.PackageId = packageTable.PackageId and v_companyId = packName.CompanyID
 and packageTable.AccountID = p_AccountID and tblAccountService.AccountID = packageTable.AccountID and IF(p_PackageID = 0,0,p_PackageID) = IF(p_PackageID = 0,0,packageTable.PackageId)
 group by tblAccountService.AccountServiceID,tblAccountService.AccountID;
 
 -- select "2",tmp_AccountServicePackage.* from tmp_AccountServicePackage;

 update tmp_AccountServiceData 
 inner join tmp_AccountServicePackage on  tmp_AccountServiceData.AccountServiceID = tmp_AccountServicePackage.AccountServiceID
 SET tmp_AccountServiceData.Packages = tmp_AccountServicePackage.Packages;
 
 -- select "3",tmp_AccountServiceData.* from tmp_AccountServiceData;
 
 insert into tmp_IAccountServicePackage (Packages,AccountServiceID,AccountID,Affiliate,AffiliateAccount)
 select Packages,AccountServiceID,AccountID,Affiliate,AffiliateAccount from tmp_AccountServicePackage where AccountServiceID not in (select AccountServiceID from tmp_AccountServiceData);
 
 insert into tmp_AccountServiceData (Packages,AccountServiceID,AccountID,Affiliate,AffiliateAccount)
 select Packages,AccountServiceID,AccountID,Affiliate,AffiliateAccount from tmp_IAccountServicePackage;
 
 update tmp_AccountServiceData 
 inner join tblAccountService on  tmp_AccountServiceData.AccountServiceID = tblAccountService.AccountServiceID 
 
 SET tmp_AccountServiceData.AccountServiceOrder = tblAccountService.ServiceOrderID;
 
 delete from tmp_IAccountServicePackage;
 insert into tmp_IAccountServicePackage (AccountServiceID,AccountID,AccountServiceOrder,Affiliate,AffiliateAccount)
 select AccountServiceID,AccountID,AccountServiceID,(select AccountName from tblAccount where AccountID = IFNULL(tblAccountService.AffiliateAccount,0) ) as Affiliate,IFNULL(tblAccountService.AffiliateAccount,0) as AffiliateAccount from tblAccountService where AccountID = p_AccountID and AccountServiceID not in (select AccountServiceID from tmp_AccountServiceData);
 
 insert into tmp_AccountServiceData (Packages,AccountServiceID,AccountID,Affiliate,AffiliateAccount)
 select Packages,AccountServiceID,AccountID,Affiliate,AffiliateAccount from tmp_IAccountServicePackage;

 -- select "4",tmp_AccountServiceData.* from tmp_AccountServiceData;

IF p_Export = 0 THEN
 select AccountServiceID,Numbers,Packages,AccountServiceOrder,AccountID,Affiliate from tmp_AccountServiceData 
  where FIND_IN_SET(IF(p_PackageID = 0,0,v_PackageName), IF(p_PackageID = 0,0,Packages)) > 0 
  and IF(p_AccountServiceOrderID = 0,0,AccountServiceOrder) = IF(p_AccountServiceOrderID = 0,0,p_AccountServiceOrderID)
  and IF(p_Affiliate = 0,0,AffiliateAccount) = IF(p_Affiliate = 0,0,p_Affiliate)
  and FIND_IN_SET(IF(p_Number = 0,0,p_Number), IF(p_Number = 0,0,Numbers)) > 0
 LIMIT p_RowspPage OFFSET v_OffSet_; 

 ELSE
 select Numbers,Packages,AccountServiceOrder,Affiliate from tmp_AccountServiceData; 
 
 
END IF; 
 
 SELECT
			FOUND_ROWS() AS totalcount
		
		;

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

-- Dumping structure for procedure speakintelligentRM.prc_getRateTableVendor
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getRateTableVendor`(
	IN `p_rateTableID` INT
,
	IN `p_Type` VARCHAR(50)
,
	IN `p_City` VARCHAR(50)
,
	IN `p_Tariff` VARCHAR(50)
,
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
	select didRate.* from tblRateTableDIDRate didRate
  join tblRate rate on rate.RateID = didRate.RateID
  join tblTimezones ratetimeZone on ratetimeZone.TimezonesID = didRate.TimezonesID and rate.CountryID = p_Country
  where RateTableID = p_rateTableID and IFNULL(rate.Code,'') = p_Prefix and IFNULL(didRate.AccessType,'')=p_Type and IFNULL(didRate.City,'') = p_City and IFNULL(didRate.Tariff,'') = p_Tariff and didRate.VendorID is not null order by RateTableDIDRateID desc limit 1;
END IF; 

IF v_RateTableType = 3  THEN
	select name into @packageName from tblPackage where PackageId = p_Package;
	select pkgRate.* from tblRateTablePKGRate pkgRate join tblRate rate on rate.RateID = pkgRate.RateID
	  where rate.Code = @packageName and RateTableID = p_rateTableID order by RateTablePKGRateID desc ;
END IF;

END//
DELIMITER ;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
