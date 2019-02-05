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

-- Dumping structure for procedure speakintelligentRM.prc_GetDIDLCR
DROP PROCEDURE IF EXISTS `prc_GetDIDLCR`;
DELIMITER //
CREATE PROCEDURE `prc_GetDIDLCR`(
	IN `p_companyid` INT,
	IN `p_ServiceTemplateID` INT,
	IN `p_CurrencyID` INT,
	IN `p_DIDCategoryID` INT,
	IN `p_Position` INT,
	IN `p_SelectedEffectiveDate` DATE,
	IN `p_Calls` INT,
	IN `p_Minutes` INT,
	IN `p_Timezone` INT,
	IN `p_TimezonePercentage` INT,
	IN `p_Origination` VARCHAR(100),
	IN `p_OriginationPercentage` INT,
	IN `p_isExport` INT








)
ThisSP:BEGIN



		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


		SELECT CurrencyId INTO @v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = p_companyid;


			DROP TEMPORARY TABLE IF EXISTS tmp_all_components;
				CREATE TEMPORARY TABLE tmp_all_components(
					ID int,
					component VARCHAR(200),
					component_title VARCHAR(200)
			);

			DROP TEMPORARY TABLE IF EXISTS tmp_Timezones;
				CREATE TEMPORARY TABLE tmp_Timezones(
					ID int AUTO_INCREMENT,
					TimezonesID int,
					Title varchar(200),
					Primary Key (ID )

			);

			DROP TEMPORARY TABLE IF EXISTS tmp_table1_;
			CREATE TEMPORARY TABLE tmp_table1_ (
				TimezonesID  int,
				TimezoneTitle  varchar(100),
				OriginationCode  varchar(100),
				VendorID int,
				VendorName varchar(200),
				MonthlyCost double(18,4),
				CostPerCall double(18,4),
				CostPerMinute double(18,4),
				SurchargePerCall double(18,4),
				SurchargePerMinute double(18,4),
				OutpaymentPerCall double(18,4),
				OutpaymentPerMinute double(18,4),
				Surcharges double(18,4),
				Chargeback double(18,4),
				CollectionCostAmount double(18,4),
				CollectionCostPercentage double(18,4),
				RegistrationCostPerNumber double(18,4),
				Total double(18,4)
			);

				DROP TEMPORARY TABLE IF EXISTS tmp_vendors;
		CREATE TEMPORARY TABLE tmp_vendors (
			ID  int AUTO_INCREMENT,
			VendorName varchar(100),
			PRIMARY KEY (ID)
		);


			insert into tmp_all_components (ID, component , component_title )
				VALUES
				(1, 'MonthlyCost', 			'Monthly cost'),
				(2, 'CostPerCall'  , 		'Cost per call'),
				(3, 'CostPerMinute',			'Cost per minute'),
				(4, 'SurchargePerCall', 	'Surcharge per call'),
				(5, 'SurchargePerMinute',	'Surcharge per minute'),
				(6, 'OutpaymentPerCall', 	'Out payment per call'),
				(7, 'OutpaymentPerMinute', 'Out payment per minute'),
				(8, 'Surcharges',				'Surcharges'),
				(9, 'Chargeback',			   'Charge back'),
				(10, 'CollectionCostAmount','Collection cost - amount'),
				(11, 'CollectionCostPercentage', 'Collection cost - percentage'),
				(12, 'RegistrationCostPerNumber', 'Registration cost - per number');


			DROP TEMPORARY TABLE IF EXISTS tmp_component_output_;
			CREATE TEMPORARY TABLE tmp_component_output_ (

				TimezoneTitle  varchar(100),
				Component	  varchar(200),
				ComponentValue varchar(200),
				VendorName varchar(200),
				VendorID int,
				Total double(18,4),
				vPosition int
			);


				DROP TEMPORARY TABLE IF EXISTS tmp_final_result;
			CREATE TEMPORARY TABLE tmp_final_result (
				Component  varchar(100)
			);


			DROP TEMPORARY TABLE IF EXISTS tmp_vendor_position;
			CREATE TEMPORARY TABLE tmp_vendor_position (
				VendorID int,
				vPosition int
			);


			-- arguments usage input
			SET @p_Calls	 							 = p_Calls;
			SET @p_Minutes	 							 = p_Minutes;
			SET @p_PeakTimeZoneID	 				 = p_Timezone;
			SET @p_PeakTimeZonePercentage	 		 = p_TimezonePercentage;		-- peak percentage
			SET @p_MobileOrigination				 = p_Origination ; -- 'Mobile';	--
			SET @p_MobileOriginationPercentage	 = p_OriginationPercentage ;	-- mobile percentage

			SELECT TimezonesID into @v_DefaultTimeZoneID from tblTimezones where Title = 'Default' limit 1;
			SELECT TimezonesID into @v_PeakTimeZoneID from tblTimezones where Title = 'Peak' limit 1;
			SELECT TimezonesID into @v_OffPeakTimeZoneID  from tblTimezones where Title = 'Off Peak'  limit 1;


			-- Helper calculations...

			SET @v_PeakTimeZoneMinutes				 =  ( (@p_Minutes/ 100) * @p_PeakTimeZonePercentage ) 	; -- Peak minutes:
			SET @v_OffpeakTimeZoneMinutes		 	 =  (@p_Minutes -  @v_PeakTimeZoneMinutes)	; -- off Peak minutes;
			SET @v_MinutesFromMobileOrigination  =  ( (@p_Minutes/ 100) * @p_MobileOriginationPercentage ) 	; -- Minutes from mobile:

			SET @v_CallerRate = 1; -- temp set as 1
			SET @p_ServiceTemplateID  = p_ServiceTemplateID;

		set @p_CurrencyID = p_CurrencyID;



								insert into tmp_table1_ (
																TimezonesID,
																TimezoneTitle,
																OriginationCode,
																VendorID,
																VendorName,
																MonthlyCost,
																CostPerCall,
																CostPerMinute,
																SurchargePerCall,
																SurchargePerMinute,
																OutpaymentPerCall,
																OutpaymentPerMinute,
																Surcharges,
																Chargeback,
																CollectionCostAmount,
																CollectionCostPercentage,
																RegistrationCostPerNumber,
																Total
																)

							select

															distinct
																TimezonesID,
																TimezoneTitle,
																OriginationCode,
																AccountID,
																AccountName,
																MonthlyCost,
																CostPerCall,
																CostPerMinute,
																SurchargePerCall,
																SurchargePerMinute,
																OutpaymentPerCall,
																OutpaymentPerMinute,
																Surcharges,
																Chargeback,
																CollectionCostAmount,
																CollectionCostPercentage,
																RegistrationCostPerNumber,
																Total
							from
							(


								select
								drtr.TimezonesID,
								t.Title as TimezoneTitle,
								null as OriginationCode,
								a.AccountID,
								a.AccountName,


@MonthlyCost := CASE WHEN ( MonthlyCostCurrency is not null)
THEN

CASE WHEN  @p_CurrencyID = MonthlyCostCurrency THEN
	drtr.MonthlyCost
ELSE
(
	-- Convert to base currrncy and x by RateGenerator Exhange
	(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
	* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = MonthlyCostCurrency and  CompanyID = 1 ))
)
END

WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
	drtr.MonthlyCost
ELSE
	(
		-- Convert to base currrncy and x by RateGenerator Exhange
		(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
		* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = 1 ))
	)
END as MonthlyCost,

@CostPerCall := CASE WHEN ( CostPerCallCurrency is not null)
THEN

CASE WHEN  @p_CurrencyID = CostPerCallCurrency THEN
	drtr.CostPerCall
ELSE
(
	-- Convert to base currrncy and x by RateGenerator Exhange
	(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
	* (drtr.CostPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CostPerCallCurrency and  CompanyID = 1 ))
)
END

WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
	drtr.CostPerCall
ELSE
	(
		-- Convert to base currrncy and x by RateGenerator Exhange
		(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
		* (drtr.CostPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = 1 ))
	)
END as CostPerCall,

@CostPerMinute := CASE WHEN ( CostPerMinuteCurrency is not null)
THEN

CASE WHEN  @p_CurrencyID = CostPerMinuteCurrency THEN
	drtr.CostPerMinute
ELSE
(
	-- Convert to base currrncy and x by RateGenerator Exhange
	(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
	* (drtr.CostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CostPerMinuteCurrency and  CompanyID = 1 ))
)
END

WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
	drtr.CostPerMinute
ELSE
	(
		-- Convert to base currrncy and x by RateGenerator Exhange
		(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
		* (drtr.CostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = 1 ))
	)
END as CostPerMinute,


@SurchargePerCall := CASE WHEN ( SurchargePerCallCurrency is not null)
THEN

CASE WHEN  @p_CurrencyID = SurchargePerCallCurrency THEN
	drtr.SurchargePerCall
ELSE
(
	-- Convert to base currrncy and x by RateGenerator Exhange
	(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
	* (drtr.SurchargePerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = SurchargePerCallCurrency and  CompanyID = 1 ))
)
END

WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
	drtr.SurchargePerCall
ELSE
	(
		-- Convert to base currrncy and x by RateGenerator Exhange
		(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
		* (drtr.SurchargePerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = 1 ))
	)
END as SurchargePerCall,


@SurchargePerMinute := CASE WHEN ( SurchargePerMinuteCurrency is not null)
THEN

CASE WHEN  @p_CurrencyID = SurchargePerMinuteCurrency THEN
	drtr.SurchargePerMinute
ELSE
(
	-- Convert to base currrncy and x by RateGenerator Exhange
	(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
	* (drtr.SurchargePerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = SurchargePerMinuteCurrency and  CompanyID = 1 ))
)
END

WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
	drtr.SurchargePerMinute
ELSE
	(
		-- Convert to base currrncy and x by RateGenerator Exhange
		(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
		* (drtr.SurchargePerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = 1 ))
	)
END as SurchargePerMinute,

@OutpaymentPerCall := CASE WHEN ( OutpaymentPerCallCurrency is not null)
THEN

CASE WHEN  @p_CurrencyID = OutpaymentPerCallCurrency THEN
	drtr.OutpaymentPerCall
ELSE
(
	-- Convert to base currrncy and x by RateGenerator Exhange
	(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
	* (drtr.OutpaymentPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = OutpaymentPerCallCurrency and  CompanyID = 1 ))
)
END

WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
	drtr.OutpaymentPerCall
ELSE
	(
		-- Convert to base currrncy and x by RateGenerator Exhange
		(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
		* (drtr.OutpaymentPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = 1 ))
	)
END as OutpaymentPerCall,

@OutpaymentPerMinute := CASE WHEN ( OutpaymentPerMinuteCurrency is not null)
THEN

CASE WHEN  @p_CurrencyID = OutpaymentPerMinuteCurrency THEN
	drtr.OutpaymentPerMinute
ELSE
(
	-- Convert to base currrncy and x by RateGenerator Exhange
	(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
	* (drtr.OutpaymentPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = OutpaymentPerMinuteCurrency and  CompanyID = 1 ))
)
END

WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
	drtr.OutpaymentPerMinute
ELSE
	(
		-- Convert to base currrncy and x by RateGenerator Exhange
		(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
		* (drtr.OutpaymentPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = 1 ))
	)
END as OutpaymentPerMinute,

@Surcharges := CASE WHEN ( SurchargesCurrency is not null)
THEN

CASE WHEN  @p_CurrencyID = SurchargesCurrency THEN
	drtr.Surcharges
ELSE
(
	-- Convert to base currrncy and x by RateGenerator Exhange
	(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
	* (drtr.Surcharges  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = SurchargesCurrency and  CompanyID = 1 ))
)
END

WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
	drtr.Surcharges
ELSE
	(
		-- Convert to base currrncy and x by RateGenerator Exhange
		(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
		* (drtr.Surcharges  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = 1 ))
	)
END as Surcharges,

@Chargeback := CASE WHEN ( ChargebackCurrency is not null)
THEN

CASE WHEN  @p_CurrencyID = ChargebackCurrency THEN
	drtr.Chargeback
ELSE
(
	-- Convert to base currrncy and x by RateGenerator Exhange
	(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
	* (drtr.Chargeback  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = ChargebackCurrency and  CompanyID = 1 ))
)
END

WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
	drtr.Chargeback
ELSE
	(
		-- Convert to base currrncy and x by RateGenerator Exhange
		(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
		* (drtr.Chargeback  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = 1 ))
	)
END as Chargeback,

@CollectionCostAmount := CASE WHEN ( CollectionCostAmountCurrency is not null)
THEN

CASE WHEN  @p_CurrencyID = CollectionCostAmountCurrency THEN
	drtr.CollectionCostAmount
ELSE
(
	-- Convert to base currrncy and x by RateGenerator Exhange
	(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
	* (drtr.CollectionCostAmount  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CollectionCostAmountCurrency and  CompanyID = 1 ))
)
END

WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
	drtr.CollectionCostAmount
ELSE
	(
		-- Convert to base currrncy and x by RateGenerator Exhange
		(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
		* (drtr.CollectionCostAmount  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = 1 ))
	)
END as CollectionCostAmount,


@CollectionCostPercentage := CASE WHEN ( CollectionCostAmountCurrency is not null)
THEN

CASE WHEN  @p_CurrencyID = CollectionCostAmountCurrency THEN
	drtr.CollectionCostPercentage
ELSE
(
	-- Convert to base currrncy and x by RateGenerator Exhange
	(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
	* (drtr.CollectionCostPercentage  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CollectionCostAmountCurrency and  CompanyID = 1 ))
)
END

WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
	drtr.CollectionCostPercentage
ELSE
	(
		-- Convert to base currrncy and x by RateGenerator Exhange
		(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
		* (drtr.CollectionCostPercentage  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = 1 ))
	)
END as CollectionCostPercentage,

@RegistrationCostPerNumber := CASE WHEN ( RegistrationCostPerNumberCurrency is not null)
THEN

CASE WHEN  @p_CurrencyID = RegistrationCostPerNumberCurrency THEN
	drtr.RegistrationCostPerNumber
ELSE
(
	-- Convert to base currrncy and x by RateGenerator Exhange
	(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
	* (drtr.RegistrationCostPerNumber  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = RegistrationCostPerNumberCurrency and  CompanyID = 1 ))
)
END

WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
	drtr.RegistrationCostPerNumber
ELSE
	(
		-- Convert to base currrncy and x by RateGenerator Exhange
		(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
		* (drtr.RegistrationCostPerNumber  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = 1 ))
	)
END as RegistrationCostPerNumber,


									/*
								Total =
								Cost per month +
								( Cost per min * Minutes ) +
								(Cost per minute peak(Tz) * Peak(Tz) minutes ) +
								(Cost per minute off-peak(Tz) * Off Peak (Tz)minutes)
								(Cost per call * Calls )+
								(Surcharge from mobile per min * Minutes from mobile (Origination) ) +
								(Outpayment per minute * Minutes) +
								(Out payment per call * Calls)+
								(Collection Cost *Caller Rate )+
								(Collection Cost amount *Minutes)
								*/


								(
									(	IFNULL(@MonthlyCost,0) 				)				+
									(IFNULL(@CostPerMinute,0) * @p_Minutes)	+
									(IFNULL(@CostPerCall,0) * @p_Calls)		+
									(IFNULL(@SurchargePerCall,0) * @v_MinutesFromMobileOrigination) +
									(IFNULL(@OutpaymentPerMinute,0) * 	@p_Minutes)	+
									(IFNULL(@OutpaymentPerCall,0) * 	@p_Calls) +
									(IFNULL(@CollectionCostPercentage,0) * @v_CallerRate) +
									(IFNULL(@CollectionCostAmount,0) * @p_Minutes)


								) as Total




				from tblRateTableDIDRate  drtr
				inner join tblRateTable  rt on rt.RateTableId = drtr.RateTableId -- and rt.DIDCategoryID = 2
				 inner join tblVendorConnection vc on vc.RateTableID = rt.RateTableId and vc.DIDCategoryID = rt.DIDCategoryID and vc.CompanyID = rt.CompanyId
				inner join tblAccount a on vc.AccountId = a.AccountID and rt.CompanyId = a.CompanyId
				inner join tblRate r on drtr.RateID = r.RateID and r.CompanyID = vc.CompanyID
		 		inner join tblCountry c on c.CountryID = r.CountryID
				inner join tblServiceTemplate st on st.ServiceTemplateId = @p_ServiceTemplateID
			--	 and  c.Country = st.country  AND r.Code = st.prefixName  -- for testing only
		and st.city_tariff  =  drtr.CityTariff and c.Country = st.country AND r.Code = concat(c.Prefix ,  TRIM(LEADING '0' FROM st.prefixName) ) --		for live only
				inner join tblTimezones t on t.TimezonesID =  drtr.TimezonesID
				where

				rt.CompanyId = 1

				and drtr.ApprovedStatus = 1

				and rt.Type = 2 -- did

			  	and rt.AppliedTo = 2 -- vendor

			--	and t.TimezonesID = @v_TimezonesID

						union all




								select
								drtr.TimezonesID,
								t.Title as TimezoneTitle,
								@p_MobileOrigination as OriginationCode,
								a.AccountID,
								a.AccountName,


@MonthlyCost := CASE WHEN ( MonthlyCostCurrency is not null)
THEN

CASE WHEN  @p_CurrencyID = MonthlyCostCurrency THEN
	drtr.MonthlyCost
ELSE
(
	-- Convert to base currrncy and x by RateGenerator Exhange
	(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
	* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = MonthlyCostCurrency and  CompanyID = 1 ))
)
END

WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
	drtr.MonthlyCost
ELSE
	(
		-- Convert to base currrncy and x by RateGenerator Exhange
		(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
		* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = 1 ))
	)
END as MonthlyCost,

@CostPerCall := CASE WHEN ( CostPerCallCurrency is not null)
THEN

CASE WHEN  @p_CurrencyID = CostPerCallCurrency THEN
	drtr.CostPerCall
ELSE
(
	-- Convert to base currrncy and x by RateGenerator Exhange
	(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
	* (drtr.CostPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CostPerCallCurrency and  CompanyID = 1 ))
)
END

WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
	drtr.CostPerCall
ELSE
	(
		-- Convert to base currrncy and x by RateGenerator Exhange
		(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
		* (drtr.CostPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = 1 ))
	)
END as CostPerCall,

@CostPerMinute := CASE WHEN ( CostPerMinuteCurrency is not null)
THEN

CASE WHEN  @p_CurrencyID = CostPerMinuteCurrency THEN
	drtr.CostPerMinute
ELSE
(
	-- Convert to base currrncy and x by RateGenerator Exhange
	(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
	* (drtr.CostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CostPerMinuteCurrency and  CompanyID = 1 ))
)
END

WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
	drtr.CostPerMinute
ELSE
	(
		-- Convert to base currrncy and x by RateGenerator Exhange
		(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
		* (drtr.CostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = 1 ))
	)
END as CostPerMinute,


@SurchargePerCall := CASE WHEN ( SurchargePerCallCurrency is not null)
THEN

CASE WHEN  @p_CurrencyID = SurchargePerCallCurrency THEN
	drtr.SurchargePerCall
ELSE
(
	-- Convert to base currrncy and x by RateGenerator Exhange
	(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
	* (drtr.SurchargePerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = SurchargePerCallCurrency and  CompanyID = 1 ))
)
END

WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
	drtr.SurchargePerCall
ELSE
	(
		-- Convert to base currrncy and x by RateGenerator Exhange
		(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
		* (drtr.SurchargePerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = 1 ))
	)
END as SurchargePerCall,


@SurchargePerMinute := CASE WHEN ( SurchargePerMinuteCurrency is not null)
THEN

CASE WHEN  @p_CurrencyID = SurchargePerMinuteCurrency THEN
	drtr.SurchargePerMinute
ELSE
(
	-- Convert to base currrncy and x by RateGenerator Exhange
	(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
	* (drtr.SurchargePerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = SurchargePerMinuteCurrency and  CompanyID = 1 ))
)
END

WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
	drtr.SurchargePerMinute
ELSE
	(
		-- Convert to base currrncy and x by RateGenerator Exhange
		(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
		* (drtr.SurchargePerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = 1 ))
	)
END as SurchargePerMinute,

@OutpaymentPerCall := CASE WHEN ( OutpaymentPerCallCurrency is not null)
THEN

CASE WHEN  @p_CurrencyID = OutpaymentPerCallCurrency THEN
	drtr.OutpaymentPerCall
ELSE
(
	-- Convert to base currrncy and x by RateGenerator Exhange
	(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
	* (drtr.OutpaymentPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = OutpaymentPerCallCurrency and  CompanyID = 1 ))
)
END

WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
	drtr.OutpaymentPerCall
ELSE
	(
		-- Convert to base currrncy and x by RateGenerator Exhange
		(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
		* (drtr.OutpaymentPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = 1 ))
	)
END as OutpaymentPerCall,

@OutpaymentPerMinute := CASE WHEN ( OutpaymentPerMinuteCurrency is not null)
THEN

CASE WHEN  @p_CurrencyID = OutpaymentPerMinuteCurrency THEN
	drtr.OutpaymentPerMinute
ELSE
(
	-- Convert to base currrncy and x by RateGenerator Exhange
	(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
	* (drtr.OutpaymentPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = OutpaymentPerMinuteCurrency and  CompanyID = 1 ))
)
END

WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
	drtr.OutpaymentPerMinute
ELSE
	(
		-- Convert to base currrncy and x by RateGenerator Exhange
		(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
		* (drtr.OutpaymentPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = 1 ))
	)
END as OutpaymentPerMinute,

@Surcharges := CASE WHEN ( SurchargesCurrency is not null)
THEN

CASE WHEN  @p_CurrencyID = SurchargesCurrency THEN
	drtr.Surcharges
ELSE
(
	-- Convert to base currrncy and x by RateGenerator Exhange
	(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
	* (drtr.Surcharges  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = SurchargesCurrency and  CompanyID = 1 ))
)
END

WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
	drtr.Surcharges
ELSE
	(
		-- Convert to base currrncy and x by RateGenerator Exhange
		(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
		* (drtr.Surcharges  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = 1 ))
	)
END as Surcharges,

@Chargeback := CASE WHEN ( ChargebackCurrency is not null)
THEN

CASE WHEN  @p_CurrencyID = ChargebackCurrency THEN
	drtr.Chargeback
ELSE
(
	-- Convert to base currrncy and x by RateGenerator Exhange
	(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
	* (drtr.Chargeback  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = ChargebackCurrency and  CompanyID = 1 ))
)
END

WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
	drtr.Chargeback
ELSE
	(
		-- Convert to base currrncy and x by RateGenerator Exhange
		(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
		* (drtr.Chargeback  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = 1 ))
	)
END as Chargeback,

@CollectionCostAmount := CASE WHEN ( CollectionCostAmountCurrency is not null)
THEN

CASE WHEN  @p_CurrencyID = CollectionCostAmountCurrency THEN
	drtr.CollectionCostAmount
ELSE
(
	-- Convert to base currrncy and x by RateGenerator Exhange
	(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
	* (drtr.CollectionCostAmount  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CollectionCostAmountCurrency and  CompanyID = 1 ))
)
END

WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
	drtr.CollectionCostAmount
ELSE
	(
		-- Convert to base currrncy and x by RateGenerator Exhange
		(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
		* (drtr.CollectionCostAmount  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = 1 ))
	)
END as CollectionCostAmount,


@CollectionCostPercentage := CASE WHEN ( CollectionCostAmountCurrency is not null)
THEN

CASE WHEN  @p_CurrencyID = CollectionCostAmountCurrency THEN
	drtr.CollectionCostPercentage
ELSE
(
	-- Convert to base currrncy and x by RateGenerator Exhange
	(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
	* (drtr.CollectionCostPercentage  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CollectionCostAmountCurrency and  CompanyID = 1 ))
)
END

WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
	drtr.CollectionCostPercentage
ELSE
	(
		-- Convert to base currrncy and x by RateGenerator Exhange
		(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
		* (drtr.CollectionCostPercentage  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = 1 ))
	)
END as CollectionCostPercentage,

@RegistrationCostPerNumber := CASE WHEN ( RegistrationCostPerNumberCurrency is not null)
THEN

CASE WHEN  @p_CurrencyID = RegistrationCostPerNumberCurrency THEN
	drtr.RegistrationCostPerNumber
ELSE
(
	-- Convert to base currrncy and x by RateGenerator Exhange
	(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
	* (drtr.RegistrationCostPerNumber  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = RegistrationCostPerNumberCurrency and  CompanyID = 1 ))
)
END

WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
	drtr.RegistrationCostPerNumber
ELSE
	(
		-- Convert to base currrncy and x by RateGenerator Exhange
		(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = 1 )
		* (drtr.RegistrationCostPerNumber  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = 1 ))
	)
END as RegistrationCostPerNumber,


									/*
								Total =
								Cost per month +
								( Cost per min * Minutes ) +
								(Cost per minute peak(Tz) * Peak(Tz) minutes ) +
								(Cost per minute off-peak(Tz) * Off Peak (Tz)minutes)
								(Cost per call * Calls )+
								(Surcharge from mobile per min * Minutes from mobile (Origination) ) +
								(Outpayment per minute * Minutes) +
								(Out payment per call * Calls)+
								(Collection Cost *Caller Rate )+
								(Collection Cost amount *Minutes)
								*/


								(
									(	IFNULL(@MonthlyCost,0) 				)				+
									(IFNULL(@CostPerMinute,0) * @p_Minutes)	+
									(IFNULL(@CostPerCall,0) * @p_Calls)		+
									(IFNULL(@SurchargePerCall,0) * @v_MinutesFromMobileOrigination) +
									(IFNULL(@OutpaymentPerMinute,0) * 	@p_Minutes)	+
									(IFNULL(@OutpaymentPerCall,0) * 	@p_Calls) +
									(IFNULL(@CollectionCostPercentage,0) * @v_CallerRate) +
									(IFNULL(@CollectionCostAmount,0) * @p_Minutes)


								) as Total





				from tblRateTableDIDRate  drtr
				inner join tblRateTable  rt on rt.RateTableId = drtr.RateTableId -- and rt.DIDCategoryID = 2
				 inner join tblVendorConnection vc on vc.RateTableID = rt.RateTableId and vc.DIDCategoryID = rt.DIDCategoryID and vc.CompanyID = rt.CompanyId
				inner join tblAccount a on vc.AccountId = a.AccountID and rt.CompanyId = a.CompanyId
				inner join tblRate r on drtr.RateID = r.RateID and r.CompanyID = vc.CompanyID
				inner join tblRate r2 on drtr.OriginationRateID = r2.RateID and r.CompanyID = vc.CompanyID
		 		inner join tblCountry c on c.CountryID = r.CountryID
				inner join tblServiceTemplate st on st.ServiceTemplateId = @p_ServiceTemplateID
--				 and  c.Country = st.country  AND r.Code = st.prefixName and  r2.Code = @p_MobileOrigination  -- for testing only
and st.city_tariff  =  drtr.CityTariff and c.Country = st.country AND r.Code = concat(c.Prefix ,  TRIM(LEADING '0' FROM st.prefixName) ) and  r2.Code = @p_MobileOrigination --		for live only
				inner join tblTimezones t on t.TimezonesID =  drtr.TimezonesID
				where

				rt.CompanyId = 1

				and drtr.ApprovedStatus = 1

				and rt.Type = 2 -- did

			  	and rt.AppliedTo = 2 -- vendor

			--	and t.TimezonesID = @v_TimezonesID
				) tmp
				where Total is not null;


			-- find rank
			insert into tmp_vendor_position (VendorID , vPosition)
			select
			VendorID , vPosition
			from (

				SELECT
					distinct
					VendorName,
					VendorID,
					Total,
					@rank := ( CASE WHEN(@prev_VendorName != VendorName and @prev_Total >= Total  )
						THEN  @rank + 1
										 ELSE 1
										 END
					) AS vPosition,
					@prev_VendorName := VendorName,
					@prev_Total := Total

				FROM tmp_table1_ v
					, (SELECT  @prev_VendorName := NUll ,  @rank := 0 ,  @prev_Total := 0 ) f
				order by VendorName,Total asc
			) tmp
			where vPosition <= p_Position;



			--	limit 1;

			/*
			Default , MonthlyCost , 10.0	, VendorName
			Default , CostPerCall , 10.0 , VendorName
			*/


			insert into tmp_component_output_ (	TimezoneTitle,			Component,			ComponentValue,			VendorName			, Total, vPosition)
			select TimezoneTitle,	concat('MonthlyCost' , IF(TimezoneTitle = 'Default','',TimezoneTitle) , IF(OriginationCode is null ,'', concat(' From ',OriginationCode))   ) as Component,			MonthlyCost,			VendorName, Total, vPosition
			from tmp_table1_
			inner join tmp_vendor_position on tmp_table1_.VendorID  = tmp_vendor_position.VendorID ;



			insert into tmp_component_output_ (	TimezoneTitle,			Component,			ComponentValue,			VendorName			, Total, vPosition)
			select TimezoneTitle,	concat('CostPerCall' , IF(TimezoneTitle = 'Default','',TimezoneTitle)  , IF(OriginationCode is null ,'', concat(' From ',OriginationCode))) as Component,			CostPerCall,			VendorName, Total, vPosition
			from tmp_table1_
			inner join tmp_vendor_position on tmp_table1_.VendorID  = tmp_vendor_position.VendorID ;

			insert into tmp_component_output_ (	TimezoneTitle,			Component,			ComponentValue,			VendorName			, Total, vPosition)
			select TimezoneTitle,	concat('CostPerMinute' , IF(TimezoneTitle = 'Default','',TimezoneTitle)  , IF(OriginationCode is null ,'', concat(' From ',OriginationCode)) ) as Component,			CostPerMinute,			VendorName, Total, vPosition
			from tmp_table1_
			inner join tmp_vendor_position on tmp_table1_.VendorID  = tmp_vendor_position.VendorID ;

			insert into tmp_component_output_ (	TimezoneTitle,			Component,			ComponentValue,			VendorName			, Total, vPosition)
			select TimezoneTitle,	concat('SurchargePerCall' , IF(TimezoneTitle = 'Default','',TimezoneTitle)  , IF(OriginationCode is null ,'', concat(' From ',OriginationCode))) as Component,			SurchargePerCall,			VendorName, Total, vPosition
			from tmp_table1_
			inner join tmp_vendor_position on tmp_table1_.VendorID  = tmp_vendor_position.VendorID ;

			insert into tmp_component_output_ (	TimezoneTitle,			Component,			ComponentValue,			VendorName			, Total, vPosition)
			select TimezoneTitle,	concat('SurchargePerMinute' , IF(TimezoneTitle = 'Default','',TimezoneTitle)  , IF(OriginationCode is null ,'', concat(' From ',OriginationCode))) as Component,			SurchargePerMinute,			VendorName, Total, vPosition
			from tmp_table1_
			inner join tmp_vendor_position on tmp_table1_.VendorID  = tmp_vendor_position.VendorID ;

			insert into tmp_component_output_ (	TimezoneTitle,			Component,			ComponentValue,			VendorName			, Total, vPosition)
			select TimezoneTitle,	concat('OutpaymentPerCall' , IF(TimezoneTitle = 'Default','',TimezoneTitle)  , IF(OriginationCode is null ,'', concat(' From ',OriginationCode))) as Component,			OutpaymentPerCall,			VendorName, Total, vPosition
			from tmp_table1_
			inner join tmp_vendor_position on tmp_table1_.VendorID  = tmp_vendor_position.VendorID ;

			insert into tmp_component_output_ (	TimezoneTitle,			Component,			ComponentValue,			VendorName			, Total, vPosition)
			select TimezoneTitle,	concat('OutpaymentPerMinute' , IF(TimezoneTitle = 'Default','',TimezoneTitle)  , IF(OriginationCode is null ,'', concat(' From ',OriginationCode))) as Component,			OutpaymentPerMinute,			VendorName, Total, vPosition
			from tmp_table1_
			inner join tmp_vendor_position on tmp_table1_.VendorID  = tmp_vendor_position.VendorID ;

			insert into tmp_component_output_ (	TimezoneTitle,			Component,			ComponentValue,			VendorName			, Total, vPosition)
			select TimezoneTitle,	concat('Surcharges' , IF(TimezoneTitle = 'Default','',TimezoneTitle)  , IF(OriginationCode is null ,'', concat(' From ',OriginationCode))) as Component,			Surcharges,			VendorName, Total, vPosition
			from tmp_table1_
			inner join tmp_vendor_position on tmp_table1_.VendorID  = tmp_vendor_position.VendorID ;

			insert into tmp_component_output_ (	TimezoneTitle,			Component,			ComponentValue,			VendorName	, 		Total, vPosition)
			select TimezoneTitle,	concat('Chargeback' , IF(TimezoneTitle = 'Default','',TimezoneTitle)  , IF(OriginationCode is null ,'', concat(' From ',OriginationCode))) as Component,			Chargeback,			VendorName, Total, vPosition
			from tmp_table1_
			inner join tmp_vendor_position on tmp_table1_.VendorID  = tmp_vendor_position.VendorID ;

			insert into tmp_component_output_ (	TimezoneTitle,			Component,			ComponentValue,			VendorName	, Total, vPosition)
			select TimezoneTitle,	concat('CollectionCostAmount' , IF(TimezoneTitle = 'Default','',TimezoneTitle)  , IF(OriginationCode is null ,'', concat(' From ',OriginationCode))) as Component,			CollectionCostAmount,			VendorName, Total, vPosition
			from tmp_table1_
			inner join tmp_vendor_position on tmp_table1_.VendorID  = tmp_vendor_position.VendorID ;

			insert into tmp_component_output_ (	TimezoneTitle,			Component,			ComponentValue,			VendorName , 	Total, vPosition)
			select TimezoneTitle,	concat('CollectionCostPercentage' , IF(TimezoneTitle = 'Default','',TimezoneTitle)  , IF(OriginationCode is null ,'', concat(' From ',OriginationCode))) as Component,			CollectionCostPercentage,			VendorName, Total, vPosition
			from tmp_table1_
			inner join tmp_vendor_position on tmp_table1_.VendorID  = tmp_vendor_position.VendorID ;


			insert into tmp_component_output_ (	TimezoneTitle,			Component,			ComponentValue,			VendorName , 	Total, vPosition)
			select TimezoneTitle,	'Cost' as Component,			ROUND(Total,4),			VendorName, Total, vPosition
			from tmp_table1_
			inner join tmp_vendor_position on tmp_table1_.VendorID  = tmp_vendor_position.VendorID ;



			DROP TEMPORARY TABLE IF EXISTS tmp_Compoents;
				CREATE TEMPORARY TABLE tmp_Compoents(
					Component varchar(200)
			);
			insert into tmp_Compoents
			select distinct Component from tmp_component_output_  group by Component having sum(ifnull(ComponentValue,0)) = 0;

 			delete from tmp_component_output_ 		where Component in 		(select distinct Component from tmp_Compoents);


		insert into tmp_vendors(VendorName)
		select distinct VendorName from tmp_component_output_;

	 	SET @v_pointer_ = 1;
		SET @v_rowCount_ = ( SELECT COUNT(*) FROM tmp_vendors );

		IF @v_rowCount_ > 0 and (select count(*) from tmp_component_output_) > 0 THEN

			/*WHILE @v_pointer_ <= @v_rowCount_
			DO

				SET @v_VendorName = (SELECT VendorName FROM tmp_vendors WHERE ID = @v_pointer_);

					SET @ColumnName = concat('`', @v_VendorName ,'`');

					SET @stm1 = CONCAT('ALTER   TABLE `tmp_final_result` ADD COLUMN ', @ColumnName , ' double(16,4) NULL DEFAULT NULL');

					PREPARE stmt1 FROM @stm1;
					EXECUTE stmt1;
					DEALLOCATE PREPARE stmt1;

				SET @v_pointer_ = @v_pointer_ + 1;

			END WHILE;
			*/

			SET @v_pointer_=1;
			SET @stm_columns= '';
			WHILE @v_pointer_ <= p_Position
			DO

				SET @v_VendorName = (SELECT VendorName FROM tmp_vendors WHERE ID = @v_pointer_);

				if @v_VendorName is not null then

					SET @stm_columns = CONCAT(@stm_columns, "GROUP_CONCAT( if(ANY_VALUE(vPosition) = ",@v_pointer_,", ANY_VALUE(ComponentValue) , NULL)) AS `",@v_VendorName,"`,");

				end if;

				SET @v_pointer_ = @v_pointer_ + 1;

			END WHILE;

			SET @stm_columns = TRIM(TRAILING ',' FROM @stm_columns);


			SET @stm_query = CONCAT("SELECT Component ,",@stm_columns," FROM tmp_component_output_    GROUP BY  Component;");

					PREPARE stm_query FROM @stm_query;
					EXECUTE stm_query;
					DEALLOCATE PREPARE stm_query;

			select count(*) as totalcount from ( SELECT Component as totalcount from tmp_component_output_ GROUP BY  Component) tmp;



	 			-- select distinct VendorName,ROUND(Total,4) from tmp_component_output_ order by vPosition,VendorName;
	ELSE

		select "" as Component	,"" as ComponentValue ;


	END IF;



	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
