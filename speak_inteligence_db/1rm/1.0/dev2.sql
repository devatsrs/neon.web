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
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
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
				Code varchar(100),
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


			DROP TEMPORARY TABLE IF EXISTS tmp_table_with_origination;
			CREATE TEMPORARY TABLE tmp_table_with_origination (

				TimezonesID  int,
				TimezoneTitle  varchar(100),
				Code varchar(100),
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



			DROP TEMPORARY TABLE IF EXISTS tmp_table_without_origination;
			CREATE TEMPORARY TABLE tmp_table_without_origination (

				TimezonesID  int,
				TimezoneTitle  varchar(100),
				Code varchar(100),
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
			vPosition int,
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

			DROP TEMPORARY TABLE IF EXISTS tmp_component_output_dup;
			CREATE TEMPORARY TABLE tmp_component_output_dup (

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

			DROP TEMPORARY TABLE IF EXISTS tmp_timezones;
			CREATE TEMPORARY TABLE tmp_timezones (
				ID int auto_increment,
				TimezonesID int,
				primary key (ID)
			);

			DROP TEMPORARY TABLE IF EXISTS tmp_timezone_minutes;
			CREATE TEMPORARY TABLE tmp_timezone_minutes (
				TimezonesID int,
				minutes int
			);
			DROP TEMPORARY TABLE IF EXISTS tmp_timezone_minutes_2;
			CREATE TEMPORARY TABLE tmp_timezone_minutes_2 (
				TimezonesID int,
				minutes int
			);
			DROP TEMPORARY TABLE IF EXISTS tmp_timezone_minutes_3;
			CREATE TEMPORARY TABLE tmp_timezone_minutes_3 (
				TimezonesID int,
				minutes int
			);

			-- arguments usage input
			SET @p_Calls	 							 = p_Calls;
			SET @p_Minutes	 							 = p_Minutes;
			SET @v_PeakTimeZoneID	 				 = p_Timezone;
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
			SET @p_DIDCategoryID  		= p_DIDCategoryID;

		set @p_CurrencyID = p_CurrencyID;

		SET @p_StartDate	= p_StartDate;
		SET @p_EndDate		= p_EndDate;




		SET @v_days =    TIMESTAMPDIFF(DAY, (SELECT @p_StartDate), (SELECT @p_EndDate)) ;
		SET @v_period1 =      IF(MONTH((SELECT @p_StartDate)) = MONTH((SELECT @p_EndDate)), 0, (TIMESTAMPDIFF(DAY, (SELECT @p_StartDate), LAST_DAY((SELECT @p_StartDate)) + INTERVAL 1 DAY)) / DAY(LAST_DAY((SELECT @p_StartDate))));
		SET @v_period2 =      TIMESTAMPDIFF(MONTH, LAST_DAY((SELECT @p_StartDate)) + INTERVAL 1 DAY, LAST_DAY((SELECT @p_EndDate))) ;
		SET @v_period3 =      IF(MONTH((SELECT @p_StartDate)) = MONTH((SELECT @p_EndDate)), (SELECT @v_days), DAY((SELECT @p_EndDate))) / DAY(LAST_DAY((SELECT @p_EndDate)));
		SET @p_months1 =     (SELECT @v_period1) + (SELECT @v_period2) + (SELECT @v_period3);


		SET @p_months = @p_months1;

		IF (day(LAST_DAY(@p_StartDate)) = @v_days ) THEN

			 SET @p_months = 1;

	 	END IF;




		 -- ///////////////////////////////////////////////////// Timezone minutes logic
		insert into tmp_timezones (TimezonesID) select TimezonesID from 	tblTimezones;

		insert into tmp_timezone_minutes (TimezonesID, minutes) select p_Timezone, @v_PeakTimeZoneMinutes as minutes;

		SET @v_RemainingTimezones = (select count(*) from tmp_timezones where TimezonesID != p_Timezone);
		SET @v_RemainingMinutes = (@p_Minutes - @v_PeakTimeZoneMinutes) / @v_RemainingTimezones ;

		SET @v_pointer_ = 1;
		SET @v_rowCount_ = ( SELECT COUNT(*) FROM tmp_timezones );

		WHILE @v_pointer_ <= @v_rowCount_
		DO

				SET @v_TimezonesID = (SELECT TimezonesID FROM tmp_timezones WHERE ID = @v_pointer_ AND TimezonesID != p_Timezone );

				if @v_TimezonesID > 0 THEN

					insert into tmp_timezone_minutes (TimezonesID, minutes)  select @v_TimezonesID, @v_RemainingMinutes as minutes;

				END IF ;

			SET @v_pointer_ = @v_pointer_ + 1;

		END WHILE;

		insert into tmp_timezone_minutes_2 (TimezonesID, minutes) select TimezonesID, minutes from tmp_timezone_minutes;
		insert into tmp_timezone_minutes_3 (TimezonesID, minutes) select TimezonesID, minutes from tmp_timezone_minutes;

		-- ///////////////////////////////////////////////////// Timezone minutes logic

								insert into tmp_table_without_origination (

																TimezonesID,
																TimezoneTitle,
																Code,
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
								drtr.TimezonesID,
								t.Title as TimezoneTitle,
								r.Code,
								r2.Code as OriginationCode,
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

								CASE WHEN  t.TimezonesID is null OR t.TimezonesID  = @v_DefaultTimeZoneID THEN
								(

									(	IFNULL(@MonthlyCost,0) 				)				+
									(IFNULL(@CostPerMinute,0) * @p_Minutes)	+
									(IFNULL(@CostPerCall,0) * @p_Calls)		+
									(IFNULL(@SurchargePerCall,0) * @v_MinutesFromMobileOrigination) +
									(IFNULL(@OutpaymentPerMinute,0) * 	@p_Minutes)	+
									(IFNULL(@OutpaymentPerCall,0) * 	@p_Calls) +
									(IFNULL(@CollectionCostPercentage,0) * @v_CallerRate) +
									(IFNULL(@CollectionCostAmount,0) * @p_Minutes)


								)
								WHEN  t.TimezonesID  = @v_PeakTimeZoneID THEN
								(

									(	IFNULL(@MonthlyCost,0) 				)				+
									(IFNULL(@CostPerMinute,0) * @v_PeakTimeZoneMinutes)	+
									(IFNULL(@CostPerCall,0) * @p_Calls)		+
									(IFNULL(@SurchargePerCall,0) * @v_MinutesFromMobileOrigination) +
									(IFNULL(@OutpaymentPerMinute,0) * 	@v_PeakTimeZoneMinutes)	+
									(IFNULL(@OutpaymentPerCall,0) * 	@p_Calls) +
									(IFNULL(@CollectionCostPercentage,0) * @v_CallerRate) +
									(IFNULL(@CollectionCostAmount,0) * @v_PeakTimeZoneMinutes)

								)
								WHEN  t.TimezonesID  = @v_OffPeakTimeZoneID THEN
								(

									(	IFNULL(@MonthlyCost,0) 				)				+
									(IFNULL(@CostPerMinute,0) * @v_OffpeakTimeZoneMinutes)	+
									(IFNULL(@CostPerCall,0) * @p_Calls)		+
									(IFNULL(@SurchargePerCall,0) * @v_MinutesFromMobileOrigination) +
									(IFNULL(@OutpaymentPerMinute,0) * 	@v_OffpeakTimeZoneMinutes)	+
									(IFNULL(@OutpaymentPerCall,0) * 	@p_Calls) +
									(IFNULL(@CollectionCostPercentage,0) * @v_CallerRate) +
									(IFNULL(@CollectionCostAmount,0) * @v_OffpeakTimeZoneMinutes)

								)
								END
								 as Total




				from tblRateTableDIDRate  drtr
				inner join tblRateTable  rt on rt.RateTableId = drtr.RateTableId -- and rt.DIDCategoryID = 2
				 inner join tblVendorConnection vc on vc.RateTableID = rt.RateTableId and vc.DIDCategoryID = rt.DIDCategoryID and vc.CompanyID = rt.CompanyId  and vc.Active=1
				inner join tblAccount a on vc.AccountId = a.AccountID and rt.CompanyId = a.CompanyId
				inner join tblRate r on drtr.RateID = r.RateID and r.CompanyID = vc.CompanyID
				left join tblRate r2 on drtr.OriginationRateID = r2.RateID and r.CompanyID = vc.CompanyID
		 		inner join tblCountry c on c.CountryID = r.CountryID
				inner join tblServiceTemplate st on st.ServiceTemplateId = @p_ServiceTemplateID
			--	 and  c.Country = st.country  AND r.Code = st.prefixName  -- for testing only
		and st.city_tariff  =  drtr.CityTariff and c.Country = st.country AND r.Code = concat(c.Prefix ,  TRIM(LEADING '0' FROM st.prefixName) ) --		for live only
				inner join tblTimezones t on t.TimezonesID =  drtr.TimezonesID
				where

				rt.CompanyId = 1

				and vc.DIDCategoryID = @p_DIDCategoryID

				and drtr.ApprovedStatus = 1

				and rt.Type = 2 -- did

			  	and rt.AppliedTo = 2 -- vendor

			--	and t.TimezonesID = @v_TimezonesID
			;


					insert into tmp_table_with_origination
					(

																TimezonesID,
																TimezoneTitle,
																Code,
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
								drtr.TimezonesID,
								t.Title as TimezoneTitle,
								r.Code,
								r2.Code as OriginationCode,
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


									-- @v_MinutesFromMobileOrigination used here instead of timezone or default minutes
								(
									(	IFNULL(@MonthlyCost,0) 				)				+
									(IFNULL(@CostPerMinute,0) * @v_MinutesFromMobileOrigination)	+
									(IFNULL(@CostPerCall,0) * @p_Calls)		+
									(IFNULL(@SurchargePerCall,0) * @v_MinutesFromMobileOrigination) +
									(IFNULL(@OutpaymentPerMinute,0) * 	@v_MinutesFromMobileOrigination)	+
									(IFNULL(@OutpaymentPerCall,0) * 	@p_Calls) +
									(IFNULL(@CollectionCostPercentage,0) * @v_CallerRate) +
									(IFNULL(@CollectionCostAmount,0) * @v_MinutesFromMobileOrigination)


								) as Total





				from tblRateTableDIDRate  drtr
				inner join tblRateTable  rt on rt.RateTableId = drtr.RateTableId -- and rt.DIDCategoryID = 2
				 inner join tblVendorConnection vc on vc.RateTableID = rt.RateTableId and vc.DIDCategoryID = rt.DIDCategoryID and vc.CompanyID = rt.CompanyId and vc.Active=1
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

				and vc.DIDCategoryID = @p_DIDCategoryID

				and drtr.ApprovedStatus = 1

				and rt.Type = 2 -- did

			  	and rt.AppliedTo = 2 -- vendor
				;
			--	and t.TimezonesID = @v_TimezonesID



				delete t1 from tmp_table_without_origination t1 inner join tmp_table_with_origination t2 on t1.VendorID = t2.VendorID and t1.TimezonesID = t2.TimezonesID and t1.Code = t2.Code;


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
																TimezonesID,
																TimezoneTitle,
																OriginationCode,
																VendorID,
																VendorName,
																(MonthlyCost * @p_months) as MonthlyCost,
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
																from (
																		select
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
																		from
																		tmp_table_without_origination

																		union all

																		select
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
																		from
																		tmp_table_with_origination

																) tmp
																where Total is not null;






			-- find rank
			insert into tmp_vendor_position (VendorID , vPosition)
			select
			VendorID , vPosition
			from (

				SELECT
					distinct
					v.VendorID,
					v.Total,
						@rank := ( CASE WHEN(@prev_VendorID != v.VendorID and @prev_Total <= v.Total  )
						THEN  @rank + 1
										 ELSE 1
										 END
					) AS vPosition,
					@prev_VendorID := v.VendorID,
					@prev_Total := v.Total

				FROM (

						select distinct  VendorID , sum(Total) as Total from tmp_table1_ group by VendorID
					) v
					, (SELECT  @prev_VendorID := NUll ,  @rank := 0 ,  @prev_Total := 0 ) f

				order by v.Total,v.VendorID asc
			) tmp
			where vPosition <= p_Position;



			--	limit 1;

			/*
			Default , MonthlyCost , 10.0	, VendorName
			Default , CostPerCall , 10.0 , VendorName
			*/


			insert into tmp_component_output_ (	TimezoneTitle,			Component,			ComponentValue,			VendorName			, Total, vPosition)
			select TimezoneTitle,	concat('MonthlyCost' , ' ', IF(TimezoneTitle = 'Default','', TimezoneTitle) , IF(OriginationCode is null ,'', concat(' From ',OriginationCode))   ) as Component,			IF(MonthlyCost=0,NULL,MonthlyCost),			VendorName, Total, vPosition
			from tmp_table1_
			inner join tmp_vendor_position on tmp_table1_.VendorID  = tmp_vendor_position.VendorID ;



			insert into tmp_component_output_ (	TimezoneTitle,			Component,			ComponentValue,			VendorName			, Total, vPosition)
			select TimezoneTitle,	concat('CostPerCall' , ' ', IF(TimezoneTitle = 'Default','',TimezoneTitle)  , IF(OriginationCode is null ,'', concat(' From ',OriginationCode))) as Component,			IF(CostPerCall=0,null,CostPerCall),			VendorName, Total, vPosition
			from tmp_table1_
			inner join tmp_vendor_position on tmp_table1_.VendorID  = tmp_vendor_position.VendorID ;

			insert into tmp_component_output_ (	TimezoneTitle,			Component,			ComponentValue,			VendorName			, Total, vPosition)
			select TimezoneTitle,	concat('CostPerMinute' , ' ', IF(TimezoneTitle = 'Default','',TimezoneTitle)  , IF(OriginationCode is null ,'', concat(' From ',OriginationCode)) ) as Component,			IF(CostPerMinute=0,NULL,CostPerMinute),			VendorName, Total, vPosition
			from tmp_table1_
			inner join tmp_vendor_position on tmp_table1_.VendorID  = tmp_vendor_position.VendorID ;

			insert into tmp_component_output_ (	TimezoneTitle,			Component,			ComponentValue,			VendorName			, Total, vPosition)
			select TimezoneTitle,	concat('SurchargePerCall' , ' ', IF(TimezoneTitle = 'Default','',TimezoneTitle)  , IF(OriginationCode is null ,'', concat(' From ',OriginationCode))) as Component,			IF(SurchargePerCall=0,NULL,SurchargePerCall),			VendorName, Total, vPosition
			from tmp_table1_
			inner join tmp_vendor_position on tmp_table1_.VendorID  = tmp_vendor_position.VendorID ;

			insert into tmp_component_output_ (	TimezoneTitle,			Component,			ComponentValue,			VendorName			, Total, vPosition)
			select TimezoneTitle,	concat('SurchargePerMinute' , ' ', IF(TimezoneTitle = 'Default','',TimezoneTitle)  , IF(OriginationCode is null ,'', concat(' From ',OriginationCode))) as Component,			IF(SurchargePerMinute=0,NULL,SurchargePerMinute),			VendorName, Total, vPosition
			from tmp_table1_
			inner join tmp_vendor_position on tmp_table1_.VendorID  = tmp_vendor_position.VendorID ;

			insert into tmp_component_output_ (	TimezoneTitle,			Component,			ComponentValue,			VendorName			, Total, vPosition)
			select TimezoneTitle,	concat('OutpaymentPerCall' , ' ', IF(TimezoneTitle = 'Default','',TimezoneTitle)  , IF(OriginationCode is null ,'', concat(' From ',OriginationCode))) as Component,			IF(OutpaymentPerCall=0,NULL,OutpaymentPerCall),			VendorName, Total, vPosition
			from tmp_table1_
			inner join tmp_vendor_position on tmp_table1_.VendorID  = tmp_vendor_position.VendorID ;

			insert into tmp_component_output_ (	TimezoneTitle,			Component,			ComponentValue,			VendorName			, Total, vPosition)
			select TimezoneTitle,	concat('OutpaymentPerMinute' , ' ', IF(TimezoneTitle = 'Default','',TimezoneTitle)  , IF(OriginationCode is null ,'', concat(' From ',OriginationCode))) as Component,			IF(OutpaymentPerMinute=0,NULL,OutpaymentPerMinute),			VendorName, Total, vPosition
			from tmp_table1_
			inner join tmp_vendor_position on tmp_table1_.VendorID  = tmp_vendor_position.VendorID ;

			insert into tmp_component_output_ (	TimezoneTitle,			Component,			ComponentValue,			VendorName			, Total, vPosition)
			select TimezoneTitle,	concat('Surcharges' , ' ', IF(TimezoneTitle = 'Default','',TimezoneTitle)  , IF(OriginationCode is null ,'', concat(' From ',OriginationCode))) as Component,			IF(Surcharges=0,NULL,Surcharges),			VendorName, Total, vPosition
			from tmp_table1_
			inner join tmp_vendor_position on tmp_table1_.VendorID  = tmp_vendor_position.VendorID ;

			insert into tmp_component_output_ (	TimezoneTitle,			Component,			ComponentValue,			VendorName	, 		Total, vPosition)
			select TimezoneTitle,	concat('Chargeback' , ' ', IF(TimezoneTitle = 'Default','',TimezoneTitle)  , IF(OriginationCode is null ,'', concat(' From ',OriginationCode))) as Component,			IF(Chargeback=0,NULL,Chargeback),			VendorName, Total, vPosition
			from tmp_table1_
			inner join tmp_vendor_position on tmp_table1_.VendorID  = tmp_vendor_position.VendorID ;

			insert into tmp_component_output_ (	TimezoneTitle,			Component,			ComponentValue,			VendorName	, Total, vPosition)
			select TimezoneTitle,	concat('CollectionCostAmount' , ' ', IF(TimezoneTitle = 'Default','',TimezoneTitle)  , IF(OriginationCode is null ,'', concat(' From ',OriginationCode))) as Component,			IF(CollectionCostAmount=0,NULL,CollectionCostAmount),			VendorName, Total, vPosition
			from tmp_table1_
			inner join tmp_vendor_position on tmp_table1_.VendorID  = tmp_vendor_position.VendorID ;

			insert into tmp_component_output_ (	TimezoneTitle,			Component,			ComponentValue,			VendorName , 	Total, vPosition)
			select TimezoneTitle,	concat('CollectionCostPercentage' , ' ', IF(TimezoneTitle = 'Default','',TimezoneTitle)  , IF(OriginationCode is null ,'', concat(' From ',OriginationCode))) as Component,			IF(CollectionCostPercentage=0,NULL,CollectionCostPercentage),			VendorName, Total, vPosition
			from tmp_table1_
			inner join tmp_vendor_position on tmp_table1_.VendorID  = tmp_vendor_position.VendorID ;


			insert into tmp_component_output_ (	TimezoneTitle,			Component,			ComponentValue,			VendorName , 	Total, vPosition)
			select TimezoneTitle,	'zCost' as Component,			ROUND(Total,4),			VendorName, Total, vPosition
			from tmp_table1_
			inner join tmp_vendor_position on tmp_table1_.VendorID  = tmp_vendor_position.VendorID ;



			DROP TEMPORARY TABLE IF EXISTS tmp_Components;
				CREATE TEMPORARY TABLE tmp_Components(
					ID int auto_increment,
					Component varchar(200),
					primary key (ID)
			);

			insert into tmp_Components(Component)
			select distinct Component from tmp_component_output_ where ComponentValue is not null;
			-- select distinct Component from tmp_component_output_  group by Component having sum(ifnull(ComponentValue,0)) = 0 ;

 		--	delete from tmp_component_output_ 		where Component in 		(select distinct Component from tmp_Components);


		insert into tmp_component_output_dup
			select * from tmp_component_output_;


		insert into tmp_vendors(VendorName,vPosition)
		select distinct VendorName,vPosition from tmp_component_output_ order by vPosition;

	 	SET @v_pointer_ = 1;
		SET @v_rowCount_ = ( SELECT COUNT(*) FROM tmp_vendors );

		IF @v_rowCount_ > 0 and (select count(*) from tmp_component_output_) > 0 THEN

			WHILE @v_pointer_ <= @v_rowCount_
			DO

				SET @v_VendorName = (SELECT VendorName FROM tmp_vendors WHERE ID = @v_pointer_);

					SET @ColumnName = concat('', @v_VendorName ,'');

					SET @stm1 = CONCAT('ALTER   TABLE `tmp_final_result` ADD COLUMN `', @ColumnName , '` double(16,4) NULL DEFAULT NULL');

					PREPARE stmt1 FROM @stm1;
					EXECUTE stmt1;
					DEALLOCATE PREPARE stmt1;




				SET @v_pointer_ = @v_pointer_ + 1;

			END WHILE;


		insert into tmp_final_result (Component)
		SELECT distinct Component FROM tmp_Components ;



		SET @v_pointer_ = 1;
		SET @v_rowCount_ = ( SELECT COUNT(*) FROM tmp_vendors );


			WHILE @v_pointer_ <= @v_rowCount_
			DO

				SET @v_VendorName = (SELECT VendorName FROM tmp_vendors WHERE ID = @v_pointer_);

					SET @ColumnName = concat('', @v_VendorName ,'');



					SET @stm2 = CONCAT('update tmp_final_result fr inner join tmp_component_output_ o on o.Component= fr.Component and VendorName =  "' ,  @ColumnName ,'" set  `', @ColumnName , '` = o.ComponentValue where o.Component != "zCost" ; ' );

					PREPARE stm2 FROM @stm2;
					EXECUTE stm2;
					DEALLOCATE PREPARE stm2;



					set @Total = (select sum(Total) from tmp_component_output_ where Component = 'zCost' and VendorName = @ColumnName) ;

					SET @stm3 = CONCAT('update tmp_final_result set  `', @ColumnName , '` = ', @Total , ' where Component = "zCost"');

					PREPARE stm3 FROM @stm3;
					EXECUTE stm3;
					DEALLOCATE PREPARE stm3;


				SET @v_pointer_ = @v_pointer_ + 1;

			END WHILE;

			select * from tmp_final_result;
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
