ALTER TABLE `tblRateTableDIDRate`
	CHANGE COLUMN `OriginationRateID` `OriginationRateID` BIGINT(20) NULL DEFAULT '0' AFTER `RateTableDIDRateID`;

ALTER TABLE `tblRateTableDIDRate`
	ADD COLUMN `VendorID` INT NULL DEFAULT NULL AFTER `ApprovedDate`;

ALTER TABLE `tblRateTableDIDRate`
	ADD COLUMN `AccessType` VARCHAR(200) NULL DEFAULT NULL AFTER `CityTariff`;


ALTER TABLE `tblRateTableRate`
	ADD COLUMN `VendorID` INT NULL DEFAULT NULL AFTER `ApprovedDate`;

ALTER TABLE `tblRateTableDIDRateArchive`
	ADD COLUMN `VendorID` INT NULL AFTER `Notes`;

ALTER TABLE `tblRateTableRateArchive`
	ADD COLUMN `VendorID` INT NULL AFTER `ApprovedDate`;

INSERT INTO `tblCompanySetting` (`CompanyID`, `Key`, `Value`) VALUES (1, 'UseVendorCurrencyInRateGenerator', '1');

ALTER TABLE `tblUsageDetails`
	ADD COLUMN `TimezonesID` INT NULL AFTER `disconnect_time`;
-- --------------------------------------------------------
-- Host:                         192.168.1.25
-- Server version:               5.7.23-log - MySQL Community Server (GPL)
-- Server OS:                    Win64
-- HeidiSQL Version:             9.5.0.5196
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for procedure speakintelligentRM.prc_editpreference
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_editpreference`(
	IN `p_groupby` VARCHAR(50),
	IN `p_preference` INT,
	IN `p_RateTableRateID` INT,
	IN `p_TimezonesID` INT,
	IN `p_OriginationDescription` VARCHAR(200),
	IN `p_description` VARCHAR(200),
	IN `p_username` VARCHAR(50)



)
BEGIN

 	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


	DROP TEMPORARY TABLE IF EXISTS tmp_pref0;
	CREATE TEMPORARY TABLE tmp_pref0(
		OriginationRateID INT,
		RateId INT
	);


		Select RateTableID into @RateTableID from tblRateTableRate where RateTableRateID =  p_RateTableRateID;

		IF p_groupby = 'description' THEN


					INSERT INTO tmp_pref0
						select DISTINCT OriginationRateID,RateId
						FROM (
								select vr.OriginationRateID, vr.RateId
								from tblRateTableRate vr
								inner join tblRate r  on vr.RateId=r.RateID
								where RateTableID = @RateTableID
										AND vr.TimezonesID = p_TimezonesID
										AND r.Description = p_description
										AND
										(


											p_OriginationDescription = ''
											OR r.Description = p_OriginationDescription
										)
							 ) tbl;


					update tblRateTableRate rtr
					inner join tmp_pref0  r on rtr.RateID = r.RateID AND ( r.OriginationRateId is null OR rtr.OriginationRateID = r.OriginationRateID )
					SET Preference = p_preference , updated_at=NOW() , ModifiedBy = p_username
					where RateTableID = @RateTableID
					AND TimezonesID = p_TimezonesID;


	 	ELSE


			update tblRateTableRate

				SET Preference = p_preference , updated_at=NOW() , ModifiedBy = p_username

			where RateTableRateID = p_RateTableRateID;





		END IF;




SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;


-- --------------------------------------------------------
-- Host:                         192.168.1.25
-- Server version:               5.7.23-log - MySQL Community Server (GPL)
-- Server OS:                    Win64
-- HeidiSQL Version:             9.5.0.5196
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for procedure speakintelligentRM.prc_lcrBlockUnblock
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_lcrBlockUnblock`(
	IN `p_companyId` INT,
	IN `p_groupby` VARCHAR(200),
	IN `p_RateTableRateID` INT,
	IN `p_TimezonesID` INT,
	IN `p_OriginationDescription` VARCHAR(200),
	IN `p_description` VARCHAR(200),
	IN `p_Blocked` TINYINT,
	IN `p_username` VARCHAR(50)
)
BEGIN

 	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


	DROP TEMPORARY TABLE IF EXISTS tmp_pref0;
	CREATE TEMPORARY TABLE tmp_pref0(
		OriginationRateID INT,
		RateId INT
	);


		Select RateTableID into @RateTableID from tblRateTableRate where RateTableRateID =  p_RateTableRateID;

		IF p_groupby = 'description' THEN


					INSERT INTO tmp_pref0
						select DISTINCT OriginationRateID,RateId
						FROM (
								select vr.OriginationRateID, vr.RateId
								from tblRateTableRate vr
								inner join tblRate r  on vr.RateId=r.RateID
								where RateTableID = @RateTableID
										AND vr.TimezonesID = p_TimezonesID
										AND r.Description = p_description
										AND
										(


											p_OriginationDescription = ''
											OR r.Description = p_OriginationDescription
										)
							 ) tbl;


					update tblRateTableRate rtr
					inner join tmp_pref0  r on rtr.RateID = r.RateID AND ( r.OriginationRateId is null OR rtr.OriginationRateID = r.OriginationRateID )
					SET Blocked = p_Blocked , updated_at=NOW() , ModifiedBy = p_username
					where RateTableID = @RateTableID
					AND TimezonesID = p_TimezonesID;


	 	ELSE


			update tblRateTableRate

				SET Blocked =  p_Blocked   , updated_at=NOW() , ModifiedBy = p_username

			where RateTableRateID = p_RateTableRateID;





		END IF;




SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

