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

-- Dumping structure for procedure speakintelligentRM.prc_SetAccountServiceNumberAndPackage
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_SetAccountServiceNumberAndPackage`()
BEGIN

UPDATE tblCLIRateTable as CLIRateTable
inner join 
(
	select CLIRateTableID from tblCLIRateTable where NumberEndDate < date(sysdate()) 
	)	 serviceCLIRateTable
									 
SET CLIRateTable.Status = 0	
WHERE CLIRateTable.CLIRateTableID = serviceCLIRateTable.CLIRateTableID;

UPDATE tblAccountServicePackage as AccountServicePackage
inner join 
(
	select AccountServicePackageID from tblAccountServicePackage where PackageEndDate < date(sysdate()) 
	)	 serviceAccountServicePackage
									 
SET AccountServicePackage.Status = 0	
WHERE AccountServicePackage.AccountServicePackageID = serviceAccountServicePackage.AccountServicePackageID;



END//
DELIMITER ;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
