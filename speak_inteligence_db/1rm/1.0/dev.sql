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


DROP PROCEDURE IF EXISTS `prc_GetDIDLCR`;
DELIMITER //
CREATE PROCEDURE `prc_GetDIDLCR`(
	IN `p_companyid` INT,
	IN `p_CountryID` varchar(100),
	IN `p_Type` varchar(100),
	IN `p_CityTariff` varchar(100),
	IN `p_Prefix` varchar(100),
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
			Total1 double(18,4),
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
			Total1 double(18,4),
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


		DROP TEMPORARY TABLE IF EXISTS tmp_origination_minutes;
		CREATE TEMPORARY TABLE tmp_origination_minutes (
			OriginationCode varchar(50),
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

		set @p_CurrencyID = p_CurrencyID;

		SET @p_StartDate	= p_StartDate;
		SET @p_EndDate		= p_EndDate;



		SET @v_CallerRate = 1; -- temp set as 1
		-- SET @p_ServiceTemplateID  = p_ServiceTemplateID;
		SET @p_DIDCategoryID  		= p_DIDCategoryID;

		SET @p_CountryID = p_CountryID;
		SET @p_Type = p_Type;
		SET @p_CityTariff = p_CityTariff;
		SET @p_Prefix = TRIM(LEADING '0' FROM p_Prefix);


		IF @p_Calls = 0 AND @p_Minutes = 0 THEN



			select count(UsageDetailID)  into @p_Calls

			from speakintelligentCDR.tblUsageDetails  d

				inner join speakintelligentCDR.tblUsageHeader h on d.UsageHeaderID = h.UsageHeaderID

				inner join speakintelligentRM.tblCountry c  on   d.area_prefix  like concat(c.Prefix,'%')

			where CompanyID = p_companyid AND StartDate >= @p_StartDate AND StartDate <= @p_EndDate and d.is_inbound = 1

						AND (@p_CountryID = '' OR  c.CountryID = @p_CountryID )

						AND (@p_Type = '' OR d.NoType = @p_Type)

						AND (@p_CityTariff = '' OR d.CityTariff  = @p_CityTariff)

						AND ( @p_Prefix = '' OR ( d.area_prefix   = concat(c.Prefix,  @p_Prefix )  ) );



			insert into tmp_timezone_minutes (TimezonesID, minutes)

				select TimezonesID  , (sum(billed_duration) / 60) as minutes

				from speakintelligentCDR.tblUsageDetails  d

					inner join speakintelligentCDR.tblUsageHeader h on d.UsageHeaderID = h.UsageHeaderID

					inner join speakintelligentRM.tblCountry c  on   d.area_prefix  like concat(c.Prefix,'%')

				where CompanyID = p_companyid AND StartDate >= @p_StartDate AND StartDate <= @p_EndDate and d.is_inbound = 1 and TimezonesID is not null

							AND (@p_CountryID = '' OR  c.CountryID = @p_CountryID )

							AND (@p_Type = '' OR d.NoType = @p_Type)

							AND (@p_CityTariff = '' OR d.CityTariff  = @p_CityTariff)

							AND ( @p_Prefix = '' OR ( d.area_prefix   = concat(c.Prefix,  @p_Prefix )  ) )

				group by TimezonesID;


			insert into tmp_origination_minutes ( OriginationCode, minutes )

				select CLIPrefix  , (sum(billed_duration) / 60) as minutes

				from speakintelligentCDR.tblUsageDetails  d

					inner join speakintelligentCDR.tblUsageHeader h on d.UsageHeaderID = h.UsageHeaderID

					inner join speakintelligentRM.tblCountry c  on   d.area_prefix  like concat(c.Prefix,'%')

				where CompanyID = p_companyid AND StartDate >= @p_StartDate AND StartDate <= @p_EndDate and d.is_inbound = 1 and CLIPrefix is not null

							AND (@p_CountryID = '' OR  c.CountryID = @p_CountryID )

							AND (@p_Type = '' OR d.NoType = @p_Type)

							AND (@p_CityTariff = '' OR d.CityTariff  = @p_CityTariff)

							AND ( @p_Prefix = '' OR ( d.area_prefix   = concat(c.Prefix,  @p_Prefix )  ) )

				group by CLIPrefix;


		ELSE


			-- SET @v_PeakTimeZoneID	 				 = p_Timezone;
			SET @p_PeakTimeZonePercentage	 		 = p_TimezonePercentage;		-- peak percentage
			SET @p_MobileOrigination				 = p_Origination ; -- 'Mobile';	--
			SET @p_MobileOriginationPercentage	 	 = p_OriginationPercentage ;	-- mobile percentage

			-- Helper calculations...

			SET @v_PeakTimeZoneMinutes				 =  ( (@p_Minutes/ 100) * @p_PeakTimeZonePercentage ) 	; -- Peak minutes:
			SET @v_MinutesFromMobileOrigination  =  ( (@p_Minutes/ 100) * @p_MobileOriginationPercentage ) 	; -- Minutes from mobile:

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


insert into tmp_origination_minutes ( OriginationCode, minutes )
	select @p_MobileOrigination  , @v_MinutesFromMobileOrigination ;



END IF;


SET @v_days =    TIMESTAMPDIFF(DAY, (SELECT @p_StartDate), (SELECT @p_EndDate)) + 1 ;
SET @v_period1 =      IF(MONTH((SELECT @p_StartDate)) = MONTH((SELECT @p_EndDate)), 0, (TIMESTAMPDIFF(DAY, (SELECT @p_StartDate), LAST_DAY((SELECT @p_StartDate)) + INTERVAL 1 DAY)) / DAY(LAST_DAY((SELECT @p_StartDate))));
SET @v_period2 =      TIMESTAMPDIFF(MONTH, LAST_DAY((SELECT @p_StartDate)) + INTERVAL 1 DAY, LAST_DAY((SELECT @p_EndDate))) ;
SET @v_period3 =      IF(MONTH((SELECT @p_StartDate)) = MONTH((SELECT @p_EndDate)), (SELECT @v_days), DAY((SELECT @p_EndDate))) / DAY(LAST_DAY((SELECT @p_EndDate)));
SET @p_months =     (SELECT @v_period1) + (SELECT @v_period2) + (SELECT @v_period3);



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
Total1,
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
					(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid  )
					* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = MonthlyCostCurrency and  CompanyID = p_companyid ))
				)
			END

									WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
										drtr.MonthlyCost
									ELSE
										(
											-- Convert to base currrncy and x by RateGenerator Exhange
											(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
											* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = p_companyid ))
										)
									END * @p_months as MonthlyCost,

	@CostPerCall := CASE WHEN ( CostPerCallCurrency is not null)
		THEN

			CASE WHEN  @p_CurrencyID = CostPerCallCurrency THEN
				drtr.CostPerCall
			ELSE
				(
					-- Convert to base currrncy and x by RateGenerator Exhange
					(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
					* (drtr.CostPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CostPerCallCurrency and  CompanyID = p_companyid ))
				)
			END

									WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
										drtr.CostPerCall
									ELSE
										(
											-- Convert to base currrncy and x by RateGenerator Exhange
											(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
											* (drtr.CostPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = p_companyid ))
										)
									END as CostPerCall,

	@CostPerMinute := CASE WHEN ( CostPerMinuteCurrency is not null)
		THEN

			CASE WHEN  @p_CurrencyID = CostPerMinuteCurrency THEN
				drtr.CostPerMinute
			ELSE
				(
					-- Convert to base currrncy and x by RateGenerator Exhange
					(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
					* (drtr.CostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CostPerMinuteCurrency and  CompanyID = p_companyid ))
				)
			END

										WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
											drtr.CostPerMinute
										ELSE
											(
												-- Convert to base currrncy and x by RateGenerator Exhange
												(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
												* (drtr.CostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = p_companyid ))
											)
										END as CostPerMinute,


	@SurchargePerCall := CASE WHEN ( SurchargePerCallCurrency is not null)
		THEN

			CASE WHEN  @p_CurrencyID = SurchargePerCallCurrency THEN
				drtr.SurchargePerCall
			ELSE
				(
					-- Convert to base currrncy and x by RateGenerator Exhange
					(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
					* (drtr.SurchargePerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = SurchargePerCallCurrency and  CompanyID = p_companyid ))
				)
			END

											 WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
												 drtr.SurchargePerCall
											 ELSE
												 (
													 -- Convert to base currrncy and x by RateGenerator Exhange
													 (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
													 * (drtr.SurchargePerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = p_companyid ))
												 )
											 END as SurchargePerCall,


	@SurchargePerMinute := CASE WHEN ( SurchargePerMinuteCurrency is not null)
		THEN

			CASE WHEN  @p_CurrencyID = SurchargePerMinuteCurrency THEN
				drtr.SurchargePerMinute
			ELSE
				(
					-- Convert to base currrncy and x by RateGenerator Exhange
					(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
					* (drtr.SurchargePerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = SurchargePerMinuteCurrency and  CompanyID = p_companyid ))
				)
			END

												 WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
													 drtr.SurchargePerMinute
												 ELSE
													 (
														 -- Convert to base currrncy and x by RateGenerator Exhange
														 (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
														 * (drtr.SurchargePerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = p_companyid ))
													 )
												 END as SurchargePerMinute,

	@OutpaymentPerCall := CASE WHEN ( OutpaymentPerCallCurrency is not null)
		THEN

			CASE WHEN  @p_CurrencyID = OutpaymentPerCallCurrency THEN
				drtr.OutpaymentPerCall
			ELSE
				(
					-- Convert to base currrncy and x by RateGenerator Exhange
					(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
					* (drtr.OutpaymentPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = OutpaymentPerCallCurrency and  CompanyID = p_companyid ))
				)
			END

												WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
													drtr.OutpaymentPerCall
												ELSE
													(
														-- Convert to base currrncy and x by RateGenerator Exhange
														(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
														* (drtr.OutpaymentPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = p_companyid ))
													)
												END as OutpaymentPerCall,

	@OutpaymentPerMinute := CASE WHEN ( OutpaymentPerMinuteCurrency is not null)
		THEN

			CASE WHEN  @p_CurrencyID = OutpaymentPerMinuteCurrency THEN
				drtr.OutpaymentPerMinute
			ELSE
				(
					-- Convert to base currrncy and x by RateGenerator Exhange
					(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
					* (drtr.OutpaymentPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = OutpaymentPerMinuteCurrency and  CompanyID = p_companyid ))
				)
			END

													WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
														drtr.OutpaymentPerMinute
													ELSE
														(
															-- Convert to base currrncy and x by RateGenerator Exhange
															(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
															* (drtr.OutpaymentPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = p_companyid ))
														)
													END as OutpaymentPerMinute,

	@Surcharges := CASE WHEN ( SurchargesCurrency is not null)
		THEN

			CASE WHEN  @p_CurrencyID = SurchargesCurrency THEN
				drtr.Surcharges
			ELSE
				(
					-- Convert to base currrncy and x by RateGenerator Exhange
					(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
					* (drtr.Surcharges  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = SurchargesCurrency and  CompanyID = p_companyid ))
				)
			END

								 WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
									 drtr.Surcharges
								 ELSE
									 (
										 -- Convert to base currrncy and x by RateGenerator Exhange
										 (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
										 * (drtr.Surcharges  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = p_companyid ))
									 )
								 END as Surcharges,

	@Chargeback := CASE WHEN ( ChargebackCurrency is not null)
		THEN

			CASE WHEN  @p_CurrencyID = ChargebackCurrency THEN
				drtr.Chargeback
			ELSE
				(
					-- Convert to base currrncy and x by RateGenerator Exhange
					(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
					* (drtr.Chargeback  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = ChargebackCurrency and  CompanyID = p_companyid ))
				)
			END

								 WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
									 drtr.Chargeback
								 ELSE
									 (
										 -- Convert to base currrncy and x by RateGenerator Exhange
										 (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
										 * (drtr.Chargeback  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = p_companyid ))
									 )
								 END as Chargeback,

	@CollectionCostAmount := CASE WHEN ( CollectionCostAmountCurrency is not null)
		THEN

			CASE WHEN  @p_CurrencyID = CollectionCostAmountCurrency THEN
				drtr.CollectionCostAmount
			ELSE
				(
					-- Convert to base currrncy and x by RateGenerator Exhange
					(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
					* (drtr.CollectionCostAmount  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CollectionCostAmountCurrency and  CompanyID = p_companyid ))
				)
			END

													 WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
														 drtr.CollectionCostAmount
													 ELSE
														 (
															 -- Convert to base currrncy and x by RateGenerator Exhange
															 (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
															 * (drtr.CollectionCostAmount  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = p_companyid ))
														 )
													 END as CollectionCostAmount,


	@CollectionCostPercentage := CASE WHEN ( CollectionCostAmountCurrency is not null)
		THEN

			CASE WHEN  @p_CurrencyID = CollectionCostAmountCurrency THEN
				drtr.CollectionCostPercentage
			ELSE
				(
					-- Convert to base currrncy and x by RateGenerator Exhange
					(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
					* (drtr.CollectionCostPercentage  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CollectionCostAmountCurrency and  CompanyID = p_companyid ))
				)
			END

															 WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
																 drtr.CollectionCostPercentage
															 ELSE
																 (
																	 -- Convert to base currrncy and x by RateGenerator Exhange
																	 (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
																	 * (drtr.CollectionCostPercentage  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = p_companyid ))
																 )
															 END as CollectionCostPercentage,

	@RegistrationCostPerNumber := CASE WHEN ( RegistrationCostPerNumberCurrency is not null)
		THEN

			CASE WHEN  @p_CurrencyID = RegistrationCostPerNumberCurrency THEN
				drtr.RegistrationCostPerNumber
			ELSE
				(
					-- Convert to base currrncy and x by RateGenerator Exhange
					(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
					* (drtr.RegistrationCostPerNumber  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = RegistrationCostPerNumberCurrency and  CompanyID = p_companyid ))
				)
			END

																WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
																	drtr.RegistrationCostPerNumber
																ELSE
																	(
																		-- Convert to base currrncy and x by RateGenerator Exhange
																		(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
																		* (drtr.RegistrationCostPerNumber  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = p_companyid ))
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

	@Total1 := (

		(	IFNULL(@MonthlyCost,0) 				)				+
		(IFNULL(@CostPerMinute,0) * (select minutes from tmp_timezone_minutes tm where tm.TimezonesID = t.TimezonesID ))	+
		(IFNULL(@CostPerCall,0) * @p_Calls)		+
		(IFNULL(@SurchargePerCall,0) * IFNULL(tom.minutes,0)) +
		(IFNULL(@OutpaymentPerMinute,0) *  (select minutes from tmp_timezone_minutes_2 tm2 where tm2.TimezonesID = t.TimezonesID ))	+
		(IFNULL(@OutpaymentPerCall,0) * 	@p_Calls) +
		-- (IFNULL(@CollectionCostPercentage,0) * @v_CallerRate) +
		(IFNULL(@CollectionCostAmount,0) * (select minutes from tmp_timezone_minutes_3 tm3 where tm3.TimezonesID = t.TimezonesID ) )


	)
		as Total1,
	@Total := (
		@Total1 + @Total1 * (select sum( IF(FlatStatus = 0 ,(Amount/100), Amount ) * IFNULL(@CollectionCostPercentage,0))  from tblTaxRate where CompanyID = p_companyid AND TaxType in  (1,2)  /* 1 OVerall 2 Usage	*/)
	) as Total





from tblRateTableDIDRate  drtr
	inner join tblRateTable  rt on rt.RateTableId = drtr.RateTableId -- and rt.DIDCategoryID = 2
	inner join tblVendorConnection vc on vc.RateTableID = rt.RateTableId and vc.DIDCategoryID = rt.DIDCategoryID and vc.CompanyID = rt.CompanyId  and vc.Active=1
	inner join tblAccount a on vc.AccountId = a.AccountID and rt.CompanyId = a.CompanyId
	inner join tblRate r on drtr.RateID = r.RateID and r.CompanyID = vc.CompanyID
	left join tblRate r2 on drtr.OriginationRateID = r2.RateID and r.CompanyID = vc.CompanyID
	inner join tblCountry c on c.CountryID = r.CountryID
	inner join tblServiceTemplate st on
																		 ( @p_CountryID = '' OR  c.CountryID = @p_CountryID AND c.Country = st.country )
																		 AND ( @p_CityTariff = '' OR drtr.CityTariff  = @p_CityTariff AND st.city_tariff  =  drtr.CityTariff )
																		 AND ( @p_Prefix = '' OR (r.Code  = concat(c.Prefix ,@p_Prefix) AND r.Code = concat(c.Prefix ,  TRIM(LEADING '0' FROM st.prefixName) )) )

	inner join tblTimezones t on t.TimezonesID =  drtr.TimezonesID
	left join tmp_origination_minutes tom  on r2.Code = tom.OriginationCode

where

	rt.CompanyId =  p_companyid

	and vc.DIDCategoryID = @p_DIDCategoryID

	and drtr.ApprovedStatus = 1

	and rt.Type = 2 -- did

	and rt.AppliedTo = 2 -- vendor

	AND EffectiveDate <= DATE(p_SelectedEffectiveDate)

	AND (EndDate is NULL OR EndDate > now() )    -- rate should not end Today
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
	Total1,
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
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
						* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = MonthlyCostCurrency and  CompanyID = p_companyid ))
					)
				END

										WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
											drtr.MonthlyCost
										ELSE
											(
												-- Convert to base currrncy and x by RateGenerator Exhange
												(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
												* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = p_companyid ))
											)
										END  * @p_months  as MonthlyCost,

		@CostPerCall := CASE WHEN ( CostPerCallCurrency is not null)
			THEN

				CASE WHEN  @p_CurrencyID = CostPerCallCurrency THEN
					drtr.CostPerCall
				ELSE
					(
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
						* (drtr.CostPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CostPerCallCurrency and  CompanyID = p_companyid ))
					)
				END

										WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
											drtr.CostPerCall
										ELSE
											(
												-- Convert to base currrncy and x by RateGenerator Exhange
												(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
												* (drtr.CostPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = p_companyid ))
											)
										END as CostPerCall,

		@CostPerMinute := CASE WHEN ( CostPerMinuteCurrency is not null)
			THEN

				CASE WHEN  @p_CurrencyID = CostPerMinuteCurrency THEN
					drtr.CostPerMinute
				ELSE
					(
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
						* (drtr.CostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CostPerMinuteCurrency and  CompanyID = p_companyid ))
					)
				END

											WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
												drtr.CostPerMinute
											ELSE
												(
													-- Convert to base currrncy and x by RateGenerator Exhange
													(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
													* (drtr.CostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = p_companyid ))
												)
											END as CostPerMinute,


		@SurchargePerCall := CASE WHEN ( SurchargePerCallCurrency is not null)
			THEN

				CASE WHEN  @p_CurrencyID = SurchargePerCallCurrency THEN
					drtr.SurchargePerCall
				ELSE
					(
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
						* (drtr.SurchargePerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = SurchargePerCallCurrency and  CompanyID = p_companyid ))
					)
				END

												 WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
													 drtr.SurchargePerCall
												 ELSE
													 (
														 -- Convert to base currrncy and x by RateGenerator Exhange
														 (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
														 * (drtr.SurchargePerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = p_companyid ))
													 )
												 END as SurchargePerCall,


		@SurchargePerMinute := CASE WHEN ( SurchargePerMinuteCurrency is not null)
			THEN

				CASE WHEN  @p_CurrencyID = SurchargePerMinuteCurrency THEN
					drtr.SurchargePerMinute
				ELSE
					(
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
						* (drtr.SurchargePerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = SurchargePerMinuteCurrency and  CompanyID = p_companyid ))
					)
				END

													 WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
														 drtr.SurchargePerMinute
													 ELSE
														 (
															 -- Convert to base currrncy and x by RateGenerator Exhange
															 (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
															 * (drtr.SurchargePerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = p_companyid ))
														 )
													 END as SurchargePerMinute,

		@OutpaymentPerCall := CASE WHEN ( OutpaymentPerCallCurrency is not null)
			THEN

				CASE WHEN  @p_CurrencyID = OutpaymentPerCallCurrency THEN
					drtr.OutpaymentPerCall
				ELSE
					(
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
						* (drtr.OutpaymentPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = OutpaymentPerCallCurrency and  CompanyID = p_companyid ))
					)
				END

													WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
														drtr.OutpaymentPerCall
													ELSE
														(
															-- Convert to base currrncy and x by RateGenerator Exhange
															(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
															* (drtr.OutpaymentPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = p_companyid ))
														)
													END as OutpaymentPerCall,

		@OutpaymentPerMinute := CASE WHEN ( OutpaymentPerMinuteCurrency is not null)
			THEN

				CASE WHEN  @p_CurrencyID = OutpaymentPerMinuteCurrency THEN
					drtr.OutpaymentPerMinute
				ELSE
					(
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
						* (drtr.OutpaymentPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = OutpaymentPerMinuteCurrency and  CompanyID = p_companyid ))
					)
				END

														WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
															drtr.OutpaymentPerMinute
														ELSE
															(
																-- Convert to base currrncy and x by RateGenerator Exhange
																(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
																* (drtr.OutpaymentPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = p_companyid ))
															)
														END as OutpaymentPerMinute,

		@Surcharges := CASE WHEN ( SurchargesCurrency is not null)
			THEN

				CASE WHEN  @p_CurrencyID = SurchargesCurrency THEN
					drtr.Surcharges
				ELSE
					(
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
						* (drtr.Surcharges  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = SurchargesCurrency and  CompanyID = p_companyid ))
					)
				END

									 WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
										 drtr.Surcharges
									 ELSE
										 (
											 -- Convert to base currrncy and x by RateGenerator Exhange
											 (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
											 * (drtr.Surcharges  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = p_companyid ))
										 )
									 END as Surcharges,

		@Chargeback := CASE WHEN ( ChargebackCurrency is not null)
			THEN

				CASE WHEN  @p_CurrencyID = ChargebackCurrency THEN
					drtr.Chargeback
				ELSE
					(
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
						* (drtr.Chargeback  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = ChargebackCurrency and  CompanyID = p_companyid ))
					)
				END

									 WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
										 drtr.Chargeback
									 ELSE
										 (
											 -- Convert to base currrncy and x by RateGenerator Exhange
											 (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
											 * (drtr.Chargeback  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = p_companyid ))
										 )
									 END as Chargeback,

		@CollectionCostAmount := CASE WHEN ( CollectionCostAmountCurrency is not null)
			THEN

				CASE WHEN  @p_CurrencyID = CollectionCostAmountCurrency THEN
					drtr.CollectionCostAmount
				ELSE
					(
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
						* (drtr.CollectionCostAmount  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CollectionCostAmountCurrency and  CompanyID = p_companyid ))
					)
				END

														 WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
															 drtr.CollectionCostAmount
														 ELSE
															 (
																 -- Convert to base currrncy and x by RateGenerator Exhange
																 (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
																 * (drtr.CollectionCostAmount  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = p_companyid ))
															 )
														 END as CollectionCostAmount,


		@CollectionCostPercentage := CASE WHEN ( CollectionCostAmountCurrency is not null)
			THEN

				CASE WHEN  @p_CurrencyID = CollectionCostAmountCurrency THEN
					drtr.CollectionCostPercentage
				ELSE
					(
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
						* (drtr.CollectionCostPercentage  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CollectionCostAmountCurrency and  CompanyID = p_companyid ))
					)
				END

																 WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
																	 drtr.CollectionCostPercentage
																 ELSE
																	 (
																		 -- Convert to base currrncy and x by RateGenerator Exhange
																		 (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
																		 * (drtr.CollectionCostPercentage  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = p_companyid ))
																	 )
																 END as CollectionCostPercentage,

		@RegistrationCostPerNumber := CASE WHEN ( RegistrationCostPerNumberCurrency is not null)
			THEN

				CASE WHEN  @p_CurrencyID = RegistrationCostPerNumberCurrency THEN
					drtr.RegistrationCostPerNumber
				ELSE
					(
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
						* (drtr.RegistrationCostPerNumber  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = RegistrationCostPerNumberCurrency and  CompanyID = p_companyid ))
					)
				END

																	WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
																		drtr.RegistrationCostPerNumber
																	ELSE
																		(
																			-- Convert to base currrncy and x by RateGenerator Exhange
																			(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
																			* (drtr.RegistrationCostPerNumber  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = p_companyid ))
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
		@Total1 := (
			(	IFNULL(@MonthlyCost,0) 				)				+
			(IFNULL(@CostPerMinute,0) * IFNULL(tom.minutes,0))	+
			(IFNULL(@CostPerCall,0) * @p_Calls)		+
			(IFNULL(@SurchargePerCall,0) * IFNULL(tom.minutes,0)) +
			(IFNULL(@OutpaymentPerMinute,0) * 	IFNULL(tom.minutes,0))	+
			(IFNULL(@OutpaymentPerCall,0) * 	@p_Calls) +
			-- (IFNULL(@CollectionCostPercentage,0) * @v_CallerRate) +
			(IFNULL(@CollectionCostAmount,0) * IFNULL(tom.minutes,0))


		) as Total1,

		@Total := (
			@Total1 + @Total1 * (select sum( IF(FlatStatus = 0 ,(Amount/100), Amount ) * IFNULL(@CollectionCostPercentage,0))  from tblTaxRate where CompanyID = p_companyid AND TaxType in  (1,2)  /* 1 OVerall 2 Usage	*/)
		) as Total





	from tblRateTableDIDRate  drtr
		inner join tblRateTable  rt on rt.RateTableId = drtr.RateTableId -- and rt.DIDCategoryID = 2
		inner join tblVendorConnection vc on vc.RateTableID = rt.RateTableId and vc.DIDCategoryID = rt.DIDCategoryID and vc.CompanyID = rt.CompanyId and vc.Active=1
		inner join tblAccount a on vc.AccountId = a.AccountID and rt.CompanyId = a.CompanyId
		inner join tblRate r on drtr.RateID = r.RateID and r.CompanyID = vc.CompanyID
		inner join tblRate r2 on drtr.OriginationRateID = r2.RateID and r.CompanyID = vc.CompanyID
		inner join tblCountry c on c.CountryID = r.CountryID
		inner join tblServiceTemplate st on
																			 ( @p_CountryID = '' OR  c.CountryID = @p_CountryID AND c.Country = st.country )
																			 AND ( @p_CityTariff = '' OR drtr.CityTariff  = @p_CityTariff AND st.city_tariff  =  drtr.CityTariff )
																			 AND ( @p_Prefix = '' OR ( r.Code  = concat(c.Prefix ,@p_Prefix) AND r.Code = concat(c.Prefix ,  TRIM(LEADING '0' FROM st.prefixName) ) ) )



		inner join tblTimezones t on t.TimezonesID =  drtr.TimezonesID
		inner join tmp_origination_minutes tom  on r2.Code = tom.OriginationCode
	where

		rt.CompanyId = p_companyid

		and vc.DIDCategoryID = @p_DIDCategoryID

		and drtr.ApprovedStatus = 1

		and rt.Type = 2 -- did

		and rt.AppliedTo = 2 -- vendor


		AND EffectiveDate <= DATE(p_SelectedEffectiveDate)

		AND (EndDate is NULL OR EndDate > now() )    -- rate should not end Today

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
	select TimezoneTitle,	concat('CollectionCostPercentage' , ' ', IF(TimezoneTitle = 'Default','',TimezoneTitle)  , IF(OriginationCode is null ,'', concat(' From ',OriginationCode))) as Component,			IF(CollectionCostPercentage=0,NULL,concat(CollectionCostPercentage,'%')),			VendorName, Total, vPosition
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

-- SET @stm1 = CONCAT('ALTER   TABLE `tmp_final_result` ADD COLUMN `', @ColumnName , '` double(16,4) NULL DEFAULT NULL');
SET @stm1 = CONCAT('ALTER   TABLE `tmp_final_result` ADD COLUMN `', @ColumnName , '` varchar(50) NULL DEFAULT NULL');

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



set @Total = (select round(sum(Total),4) from tmp_component_output_ where Component = 'zCost' and VendorName = @ColumnName) ;

SET @stm3 = CONCAT('update tmp_final_result set  `', @ColumnName , '` = ', ROUND(@Total,4) , ' where Component = "zCost"');

PREPARE stm3 FROM @stm3;
EXECUTE stm3;
DEALLOCATE PREPARE stm3;


SET @v_pointer_ = @v_pointer_ + 1;

END WHILE;

select * from tmp_final_result;
select count(*) as totalcount from tmp_final_result;
-- select count(*) as totalcount from ( SELECT Component as totalcount from tmp_component_output_ GROUP BY  Component) tmp;



-- select distinct VendorName,ROUND(Total,4) from tmp_component_output_ order by vPosition,VendorName;
ELSE

select "" as Component	,"" as ComponentValue ;


END IF;



SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

-- Dumping structure for procedure speakintelligentRM.prc_GetLCR
DROP PROCEDURE IF EXISTS `prc_GetLCR`;
DELIMITER //
CREATE PROCEDURE `prc_GetLCR`(
	IN `p_companyid` INT,
	IN `p_trunkID` INT,
	IN `p_TimezonesID` VARCHAR(50),
	IN `p_codedeckID` INT,
	IN `p_CurrencyID` INT,
	IN `p_Originationcode` VARCHAR(50),
	IN `p_OriginationDescription` VARCHAR(250),
	IN `p_code` VARCHAR(50),
	IN `p_Description` VARCHAR(250),
	IN `p_AccountIds` TEXT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_SortOrder` VARCHAR(50),
	IN `p_Preference` INT,
	IN `p_Position` INT,
	IN `p_vendor_block` INT,
	IN `p_groupby` VARCHAR(50),
	IN `p_SelectedEffectiveDate` DATE,
	IN `p_ShowAllVendorCodes` INT,
	IN `p_merge_timezones` INT,
	IN `p_TakePrice` INT,
	IN `p_isExport` INT




























)
		ThisSP:BEGIN

		DECLARE v_OffSet_ int;

		DECLARE v_Code VARCHAR(50) ;
		DECLARE v_pointer_ int;
		DECLARE v_rowCount_ int;
		DECLARE v_p_code VARCHAR(50);
		DECLARE v_Codlen_ int;
		DECLARE v_position int;
		DECLARE v_p_code__ VARCHAR(50);
		DECLARE v_has_null_position int ;
		DECLARE v_next_position1 VARCHAR(200) ;
		DECLARE v_CompanyCurrencyID_ INT;

		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		SET @@session.collation_connection='utf8_unicode_ci';
		SET @@session.character_set_results='utf8';



		SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage_;
		CREATE TEMPORARY TABLE tmp_VendorRate_stage_ (
			RateTableRateID int,
			-- VendorConnectionID int,
			RowCode VARCHAR(50) ,
			AccountId INT ,
			Blocked INT DEFAULT 0,
			AccountName VARCHAR(100) ,
			OriginationCode VARCHAR(50) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			OriginationDescription VARCHAR(255),
			Description VARCHAR(255),
			Preference INT,
			MaxMatchRank int ,
			prev_prev_RowCode VARCHAR(50),
			prev_AccountID int,
			prev_VendorConnectionID int
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage2_;
		CREATE TEMPORARY TABLE tmp_VendorRate_stage2_ (
			RateTableRateID int,
			-- VendorConnectionID int,
			RowCode VARCHAR(50) ,
			AccountId INT ,
			Blocked INT DEFAULT 0,
			AccountName VARCHAR(100) ,
			OriginationCode VARCHAR(50) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			OriginationDescription VARCHAR(255),
			Description VARCHAR(255),
			Preference INT,
			MaxMatchRank int ,
			prev_prev_RowCode VARCHAR(50),
			prev_AccountID int
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
		CREATE TEMPORARY TABLE tmp_VendorRate_ (
			RateTableRateID int,
			-- VendorConnectionID int,
			AccountId INT ,
			Blocked INT DEFAULT 0,
			AccountName VARCHAR(100) ,
			OriginationCode VARCHAR(50) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			OriginationDescription VARCHAR(255),
			Description VARCHAR(255),
			Preference INT,
			RowCode VARCHAR(50)
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_final_VendorRate_;
		CREATE TEMPORARY TABLE tmp_final_VendorRate_ (
			RateTableRateID int,
			-- VendorConnectionID int,
			AccountId INT ,
			Blocked INT DEFAULT 0,
			AccountName VARCHAR(100) ,
			OriginationCode VARCHAR(50) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			OriginationDescription VARCHAR(255),
			Description VARCHAR(255),
			Preference INT,
			RowCode VARCHAR(50),
			FinalRankNumber int
		)
		;



		DROP TEMPORARY TABLE IF EXISTS tmp_search_code_;
		CREATE TEMPORARY TABLE tmp_search_code_ (
			Code  varchar(50),
			INDEX Index1 (Code)
		);



		DROP TEMPORARY TABLE IF EXISTS tmp_search_code_dup;
		CREATE TEMPORARY TABLE tmp_search_code_dup (
			Code  varchar(50),
			INDEX Index1 (Code)
		);



		DROP TEMPORARY TABLE IF EXISTS tmp_code_;
		CREATE TEMPORARY TABLE tmp_code_ (
			RowCode  varchar(50),
			Code  varchar(50),
			RowNo int,
			INDEX Index1 (Code)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_all_code_;
		CREATE TEMPORARY TABLE tmp_all_code_ (
			RowCode  varchar(50),
			Code  varchar(50),
			RowNo int,
			INDEX Index2 (Code)
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_all_code_dup;
		CREATE TEMPORARY TABLE tmp_all_code_dup (
			RowCode  varchar(50),
			Code  varchar(50),
			RowNo int,
			INDEX Index2 (Code)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates_(
			RateTableRateID int,
			-- VendorConnectionID int,
			AccountId int,
			Blocked INT DEFAULT 0,
			AccountName varchar(200),
			OriginationCode varchar(50),
			Code varchar(50),
			OriginationDescription varchar(200),
			Description varchar(200),
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate date,
			TrunkID int,
			CountryID int,
			RateID int,
			Preference int,
			INDEX IX_Code (Code)
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates1_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates1_(
			RateTableRateID int,
			-- VendorConnectionID int,
			AccountId int,
			Blocked INT DEFAULT 0,
			AccountName varchar(200),
			OriginationCode varchar(50),
			Code varchar(50),
			OriginationDescription varchar(200),
			Description varchar(200),
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate date,
			TrunkID int,
			CountryID int,
			OriginationRateID int,
			RateID int,
			Preference int,
			INDEX IX_Code (Code),
			INDEX tmp_VendorCurrentRates_AccountId (`AccountId`,`TrunkID`,`RateId`,`EffectiveDate`)
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateByRank_;
		CREATE TEMPORARY TABLE tmp_VendorRateByRank_ (
			RateTableRateID int,
			-- VendorConnectionID int,
			AccountId INT ,
			Blocked INT DEFAULT 0,
			AccountName VARCHAR(100) ,
			OriginationCode VARCHAR(50) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			OriginationDescription VARCHAR(255),
			Description VARCHAR(255),
			Preference INT,
			rankname INT,
			INDEX IX_Code (Code,rankname)
		)
		;

		SELECT CurrencyId INTO v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = p_companyid;


		IF (p_ShowAllVendorCodes = 1) THEN

			insert into tmp_search_code_
				SELECT  DISTINCT LEFT(f.Code, x.RowNo) as loopCode FROM (
																																	SELECT @RowNo  := @RowNo + 1 as RowNo
																																	FROM mysql.help_category
																																		,(SELECT @RowNo := 0 ) x
																																	limit 15
																																) x

					INNER JOIN (
											 SELECT distinct Code , Description from tblRate
											 WHERE CompanyID = p_companyid
														 AND
														 (
															 (
																 ( CHAR_LENGTH(RTRIM(p_code)) = 0  OR Code LIKE REPLACE(p_code,'*', '%') )
																 AND ( p_Description = ''  OR Description LIKE REPLACE(p_Description,'*', '%') )
															 )
															 /* OR
                                (

                                   ( CHAR_LENGTH(RTRIM(p_Originationcode)) = 0  OR Code LIKE REPLACE(p_Originationcode,'*', '%') )
                                 AND ( p_OriginationDescription = ''  OR Description LIKE REPLACE(p_OriginationDescription,'*', '%') )

                                ) */
														 )
										 ) f
						ON x.RowNo   <= LENGTH(f.Code)
				order by loopCode   desc;


		ELSE

			insert into tmp_search_code_
				SELECT  DISTINCT LEFT(f.Code, x.RowNo) as loopCode FROM (
																																	SELECT @RowNo  := @RowNo + 1 as RowNo
																																	FROM mysql.help_category
																																		,(SELECT @RowNo := 0 ) x
																																	limit 15
																																) x
					INNER JOIN tblRate AS f
						ON f.CompanyID = p_companyid  AND f.CodeDeckId = p_codedeckID

							 AND
							 (
								 (
									 ( CHAR_LENGTH(RTRIM(p_code)) = 0  OR f.Code LIKE REPLACE(p_code,'*', '%') )
									 AND ( p_Description = ''  OR f.Description LIKE REPLACE(p_Description,'*', '%') )
								 )
								 /* OR
                  (

                     ( CHAR_LENGTH(RTRIM(p_Originationcode)) = 0  OR f.Code LIKE REPLACE(p_Originationcode,'*', '%') )
                   AND ( p_OriginationDescription = ''  OR f.Description LIKE REPLACE(p_OriginationDescription,'*', '%') )

                  ) */
							 )
							 AND x.RowNo   <= LENGTH(f.Code)

				order by loopCode   desc;

		END IF;


		insert into tmp_search_code_dup select * from tmp_search_code_;

		SET @num := 0, @AccountID := '', @TrunkID := '', @RateID := '';

		SET @stm_show_all_vendor_codes1 = CONCAT("INNER JOIN tmp_search_code_ SplitCode ON tblRate.Code = SplitCode.Code");
		SET @stm_show_all_vendor_codes2 = CONCAT('( CHAR_LENGTH(RTRIM("',p_code,'")) = 0 OR tblRate.Code LIKE REPLACE("',p_code,'","*", "%") )
													AND ("',p_Description,'"="" OR tblRate.Description LIKE REPLACE("',p_Description,'","*","%"))
													AND ');




		SET @stm_filter_oringation_code = CONCAT('INNER JOIN tblRate r2 ON r2.CompanyID = ',p_companyid,' AND tblRateTableRate.OriginationRateID = r2.RateID
		INNER JOIN tmp_search_code_dup SplitCode2 ON r2.Code = SplitCode2.Code
						AND ( CHAR_LENGTH(RTRIM("',p_Originationcode,'")) = 0 OR r2.Code LIKE REPLACE("',p_Originationcode,'","*", "%") )
						AND ( "',p_OriginationDescription,'"=""  OR r2.Description LIKE REPLACE("',p_OriginationDescription,'","*", "%") )
				');




		SET @stm = CONCAT('
			INSERT INTO tmp_VendorCurrentRates1_
			',
											IF (p_merge_timezones = 1,"
				Select DISTINCT
					RateTableRateID,

					AccountId,
					Blocked,
					AccountName,
					OriginationCode,
					Code,
					OriginationDescription,
					Description,
					Rate,
					ConnectionFee,
					EffectiveDate,
					TrunkID,
					CountryID,
					OriginationRateID,
					RateID,
					Preference
				FROM (
			",""),'

				Select DISTINCT
					RateTableRateID,

					AccountId,
					Blocked,
					AccountName,
					OriginationCode,
					Code,
					OriginationDescription,
					Description,
					Rate,
					ConnectionFee,
					EffectiveDate,
					TrunkID,
					CountryID,
					OriginationRateID,
					RateID,
					Preference
					',IF (p_merge_timezones = 1,",
						@num := if(@AccountID = AccountID AND @TrunkID = TrunkID AND @RateID = RateID, @num + 1, 1) as row_number,
						@AccountID := AccountID,
						@TrunkID := TrunkID,
						@RateID := RateID
					",""),'
				FROM (
					 SELECT distinct
						RateTableRateID,

						tblAccount.AccountId,
						tblRateTableRate.Blocked,
						vt.Name as AccountName,
						IFNULL(r2.Code,"") as OriginationCode,
						tblRate.Code,
						IFNULL(r2.Description,"") as OriginationDescription ,
						tblRate.Description,
						CASE WHEN  tblAccount.CurrencyId = ',p_CurrencyID,'
						THEN
							tblRateTableRate.Rate
						WHEN  ',v_CompanyCurrencyID_,' = ',p_CurrencyID,'
						THEN
						(
							( tblRateTableRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = ',p_companyid,' ) )
						)
						ELSE
						(
							-- Convert to base currrncy and x by RateGenerator Exhange
							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' and  CompanyID = ',p_companyid,' )
							* (tblRateTableRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = ',p_companyid,' ))
						)
						END as Rate,
						ConnectionFee,
						DATE_FORMAT (tblRateTableRate.EffectiveDate, "%Y-%m-%d") AS EffectiveDate, vt.TrunkID, tblRate.CountryID,
						r2.RateID as OriginationRateID,
						tblRate.RateID,IFNULL(Preference, 5) AS Preference
					FROM
						tblRateTableRate
					INNER JOIN tblVendorConnection vt ON vt.CompanyID = ',p_companyid,' and vt.RateTableID = tblRateTableRate.RateTableID  and vt.RateTypeID = 1   and vt.Active = 1 and vt.TrunkID = ',p_trunkID,'
					INNER JOIN tblAccount ON tblAccount.AccountID = vt.AccountId AND  tblAccount.CompanyID = ',p_companyid,' AND vt.AccountId = tblAccount.AccountID
					INNER JOIN tblRate ON tblRate.CompanyID = ',p_companyid,' AND tblRateTableRate.RateId = tblRate.RateID -- AND vt.CodeDeckId = tblRate.CodeDeckId


					',
											IF (p_ShowAllVendorCodes = 1,"",@stm_show_all_vendor_codes1)
		,'

						LEFT JOIN tblRate r2 ON r2.CompanyID = ',p_companyid,' AND tblRateTableRate.OriginationRateID = r2.RateID
						LEFT JOIN tmp_search_code_dup SplitCode2 ON r2.Code = SplitCode2.Code

					WHERE
						',
											IF (p_ShowAllVendorCodes = 1,@stm_show_all_vendor_codes2,"")
		,'
						( EffectiveDate <= DATE("',p_SelectedEffectiveDate,'") )

						AND ( CHAR_LENGTH(RTRIM("',p_Originationcode,'")) = 0 OR r2.Code LIKE REPLACE("',p_Originationcode,'","*", "%") )
						AND ( "',p_OriginationDescription,'"=""  OR r2.Description LIKE REPLACE("',p_OriginationDescription,'","*", "%") )


						AND ( "',p_Originationcode,'" = ""  OR  ( r2.RateID IS NOT NULL  ) )
						AND ( "',p_OriginationDescription,'" = ""  OR  ( r2.RateID IS NOT NULL  ) )


						AND ( tblRateTableRate.EndDate IS NULL OR  tblRateTableRate.EndDate > Now() )   -- rate should not end Today
						AND ("',p_AccountIds,'"="" OR FIND_IN_SET(tblAccount.AccountID,"',p_AccountIds,'") != 0 )
						AND tblAccount.IsVendor = 1
						AND tblAccount.Status = 1
						AND tblAccount.CurrencyId is not NULL
						AND (
							(',p_merge_timezones,' = 0 AND tblRateTableRate.TimezonesID = "',p_TimezonesID,'") OR
							(',p_merge_timezones,' = 1 AND FIND_IN_SET(tblRateTableRate.TimezonesID, "',p_TimezonesID,'"))
						)
						-- AND blockCode.RateId IS NULL
						-- AND blockCountry.CountryId IS NULL
					',
											IF (p_merge_timezones = 1,CONCAT("ORDER BY AccountID, TrunkID, RateID, Rate ",IF(p_TakePrice=1,"DESC","ASC")),"")
		,'
				) tbl
			',
											IF (p_merge_timezones = 1,") AS x WHERE x.row_number <= 1","")
		,'
			order by Code asc;
		');




		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;






		IF p_groupby = 'description' THEN

			INSERT INTO tmp_VendorCurrentRates_
				Select max(RateTableRateID),max(AccountId),max(Blocked),max(AccountName),max(OriginationCode),max(Code),OriginationDescription,Description, MAX(Rate),max(ConnectionFee),max(EffectiveDate),max(TrunkID),max(CountryID),max(RateID),max(Preference)
				FROM (

							 SELECT * ,
								 @row_num := IF(@prev_RateTableRateID = RateTableRateID AND @prev_AccountId = AccountID AND @prev_TrunkID = TrunkID AND @prev_OriginationDescription = OriginationDescription AND @prev_Description = Description AND @prev_EffectiveDate >= EffectiveDate, @row_num + 1, 1) AS RowID,
								 @prev_RateTableRateID := RateTableRateID,
								 @prev_AccountId := AccountID,
								 @prev_TrunkID := TrunkID,
								 @prev_OriginationDescription := OriginationDescription,
								 @prev_Description := Description,
								 @prev_EffectiveDate := EffectiveDate
							 FROM tmp_VendorCurrentRates1_
								 ,(SELECT @row_num := 1, @prev_RateTableRateID := '', @prev_AccountId := '',@prev_TrunkID := '', @prev_OriginationDescription := '', @prev_Description := '', @prev_OriginationRateID := '',@prev_RateId := '', @prev_EffectiveDate := '') x

							 ORDER BY AccountId, TrunkID, OriginationDescription,Description, EffectiveDate DESC
						 ) tbl
				WHERE RowID = 1
				group BY AccountName, TrunkID, Description, OriginationDescription
				order by Description, OriginationDescription asc;

		ELSE

			INSERT INTO tmp_VendorCurrentRates_
				Select RateTableRateID,AccountId,Blocked,AccountName,OriginationCode,Code,OriginationDescription,Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference
				FROM (
							 SELECT * ,
								 @row_num := IF(@prev_RateTableRateID = RateTableRateID AND @prev_AccountId = AccountID AND @prev_TrunkID = TrunkID AND @prev_OriginationRateID = OriginationRateID AND  @prev_RateId = RateID AND @prev_EffectiveDate >= EffectiveDate, @row_num + 1, 1) AS RowID,
								 @prev_RateTableRateID := RateTableRateID,
								 @prev_AccountId := AccountID,
								 @prev_TrunkID := TrunkID,
								 @prev_OriginationRateID := OriginationRateID,
								 @prev_RateId := RateID,
								 @prev_EffectiveDate := EffectiveDate
							 FROM tmp_VendorCurrentRates1_
								 ,(SELECT @row_num := 1, @prev_RateTableRateID := '',  @prev_AccountId := '',@prev_TrunkID := '', @prev_OriginationRateID := '', @prev_RateId := '', @prev_EffectiveDate := '') x
							 ORDER BY AccountId, TrunkID, OriginationRateID,RateId, EffectiveDate , RateTableRateID DESC
						 ) tbl
				WHERE RowID = 1
				order by OriginationCode,Code asc;

		END IF;




		IF p_ShowAllVendorCodes = 1 THEN

			insert into tmp_all_code_ (RowCode,Code,RowNo)
				select RowCode , loopCode,RowNo
				from (
							 select   RowCode , loopCode,
								 @RowNo := ( CASE WHEN ( @prev_Code = tbl1.RowCode  ) THEN @RowNo + 1
														 ELSE 1
														 END

								 )      as RowNo,
								 @prev_Code := tbl1.RowCode
							 from (
											SELECT distinct f.Code as RowCode, LEFT(f.Code, x.RowNo) as loopCode FROM (
																																																	SELECT @RowNo  := @RowNo + 1 as RowNo
																																																	FROM mysql.help_category
																																																		,(SELECT @RowNo := 0 ) x
																																																	limit 15
																																																) x
												INNER JOIN tmp_search_code_ AS f
													ON  x.RowNo   <= LENGTH(f.Code)
															AND
															(
																(
																	( CHAR_LENGTH(RTRIM(p_code)) = 0  OR f.Code LIKE REPLACE(p_code,'*', '%') )
																)

															)



												INNER JOIN tblRate as tr on f.Code=tr.Code
											order by RowCode desc,  LENGTH(loopCode) DESC
										) tbl1
								 , ( Select @RowNo := 0 ) x
						 ) tbl order by RowCode desc,  LENGTH(loopCode) DESC ;


		ELSE

			insert into tmp_all_code_ (RowCode,Code,RowNo)
				select RowCode , loopCode,RowNo
				from (
							 select   RowCode , loopCode,
								 @RowNo := ( CASE WHEN ( @prev_Code = tbl1.RowCode  ) THEN @RowNo + 1
														 ELSE 1
														 END

								 )      as RowNo,
								 @prev_Code := tbl1.RowCode
							 from (
											SELECT distinct f.Code as RowCode, LEFT(f.Code, x.RowNo) as loopCode FROM (
																																																	SELECT @RowNo  := @RowNo + 1 as RowNo
																																																	FROM mysql.help_category
																																																		,(SELECT @RowNo := 0 ) x
																																																	limit 15
																																																) x
												INNER JOIN tmp_search_code_ AS f
													ON  x.RowNo   <= LENGTH(f.Code)
															AND
															(
																(
																	( CHAR_LENGTH(RTRIM(p_code)) = 0  OR f.Code LIKE REPLACE(p_code,'*', '%') )
																)

															)
												INNER JOIN tblRate as tr on f.Code=tr.Code AND tr.CodeDeckId=p_codedeckID
											order by RowCode desc,  LENGTH(loopCode) DESC
										) tbl1
								 , ( Select @RowNo := 0 ) x
						 ) tbl order by RowCode desc,  LENGTH(loopCode) DESC ;

		END IF;





		IF p_Preference = 1 THEN



			INSERT IGNORE INTO tmp_VendorRateByRank_
				SELECT
					RateTableRateID,

					AccountID,
					Blocked,
					AccountName,
					OriginationCode,
					Code,
					Rate,
					ConnectionFee,
					EffectiveDate,
					OriginationDescription,
					Description,
					Preference,
					preference_rank
				FROM (SELECT
								RateTableRateID,

								AccountID,
								Blocked,
								AccountName,
								OriginationCode,
								Code,
								Rate,
								ConnectionFee,
								EffectiveDate,
								OriginationDescription,
								Description,
								Preference,
								CASE WHEN p_groupby = 'description' THEN
									@preference_rank := 			CASE WHEN (@prev_OriginationDescription = OriginationDescription AND @prev_Description     = Description AND @prev_Preference > Preference  ) THEN @preference_rank + 1
																					 WHEN (@prev_OriginationDescription = OriginationDescription AND @prev_Description     = Description AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1
																					 WHEN (@prev_OriginationDescription = OriginationDescription AND @prev_Description    = Description AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank
																					 ELSE 1
																					 END
								ELSE
									@preference_rank := 		   CASE WHEN (@prev_OriginationCode    = OriginationCode AND @prev_Code     = Code AND @prev_Preference > Preference  ) THEN @preference_rank + 1
																						WHEN (@prev_OriginationCode    = OriginationCode AND @prev_Code     = Code AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1
																						WHEN (@prev_OriginationCode    = OriginationCode AND @prev_Code    = Code AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank
																						ELSE 1
																						END
								END AS preference_rank,

								@prev_OriginationCode := OriginationCode,
								@prev_Code := Code,
								@prev_OriginationDescription := OriginationDescription ,
								@prev_Description := Description,
								@prev_Preference := IFNULL(Preference, 5),
								@prev_Rate := Rate
							FROM tmp_VendorCurrentRates_ AS preference,
								(SELECT @preference_rank := 0 , @prev_OriginationCode := '', @prev_Code := ''  , @prev_OriginationDescription := '' , @prev_Description := ''  , @prev_Preference := 5,  @prev_Rate := 0) x
							ORDER BY
								CASE WHEN p_groupby = 'description' THEN
									preference.OriginationDescription
								ELSE
									preference.OriginationCode
								END ,

								CASE WHEN p_groupby = 'description' THEN
									preference.Description
								ELSE
									preference.Code
								END ASC ,
								preference.Preference DESC, preference.Rate ASC,preference.AccountId ASC
						 ) tbl
				WHERE (p_isExport = 1 OR p_isExport = 2) OR (p_isExport = 0 AND preference_rank <= p_Position)
				ORDER BY OriginationCode, Code, preference_rank;

		ELSE



			INSERT IGNORE INTO tmp_VendorRateByRank_
				SELECT
					RateTableRateID,

					AccountID,
					Blocked,
					AccountName,
					OriginationCode,
					Code,
					Rate,
					ConnectionFee,
					EffectiveDate,
					OriginationDescription,
					Description,
					Preference,
					RateRank
				FROM (SELECT
								RateTableRateID,

								AccountID,
								Blocked,
								AccountName,
								OriginationCode,
								Code,
								Rate,
								ConnectionFee,
								EffectiveDate,
								OriginationDescription,
								Description,
								Preference,
								CASE WHEN p_groupby = 'description' THEN
									@rank := 	CASE WHEN (@prev_OriginationDescription = OriginationDescription AND  @prev_Description    = Description AND @prev_Rate < Rate) THEN @rank + 1
														WHEN (@prev_OriginationDescription = OriginationDescription AND  @prev_Description    = Description AND @prev_Rate = Rate) THEN @rank
														ELSE 1
														END
								ELSE
									@rank :=    CASE WHEN (@prev_OriginationCode    = OriginationCode AND  @prev_Code    = Code AND @prev_Rate < Rate) THEN @rank + 1
															WHEN (@prev_OriginationCode    = OriginationCode AND  @prev_Code    = Code AND @prev_Rate = Rate) THEN @rank
															ELSE 1
															END
								END
									AS RateRank,
								@prev_OriginationCode := OriginationCode,
								@prev_Code := Code,
								@prev_OriginationDescription := OriginationDescription ,
								@prev_Description := Description,
								@prev_Rate := Rate
							FROM tmp_VendorCurrentRates_ AS rank,
								(SELECT @rank := 0 , @prev_Code := '' ,  @prev_OriginationDescription := ''  ,@prev_OriginationCode := ''  , @prev_Description := '' , @prev_Rate := 0) f
							ORDER BY
								CASE WHEN p_groupby = 'description' THEN
									rank.OriginationDescription
								ELSE
									rank.OriginationCode
								END ,
								CASE WHEN p_groupby = 'description' THEN
									rank.Description
								ELSE
									rank.Code
								END ,
								rank.Rate,rank.AccountId

						 ) tbl
				WHERE (p_isExport = 1 OR p_isExport = 2) OR (p_isExport = 0 AND RateRank <= p_Position)
				ORDER BY OriginationCode, Code, RateRank;

		END IF;





		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage_1;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRate_stage_1 as (select * from tmp_VendorRate_stage_);


		IF p_ShowAllVendorCodes = 1 THEN

			insert ignore into tmp_VendorRate_stage_1 (
				RateTableRateID,

				RowCode,
				OriginationDescription ,
				Description ,
				AccountId ,
				Blocked,
				AccountName ,
				OriginationCode ,
				Code ,
				Rate ,
				ConnectionFee,
				EffectiveDate ,
				Preference
			)
				SELECT
					distinct
					RateTableRateID,

					RowCode,
					OriginationDescription ,
					Description ,
					AccountId ,
					Blocked,
					AccountName ,
					OriginationCode ,
					Code ,
					Rate ,
					ConnectionFee,
					EffectiveDate ,
					Preference

				from (
							 select
								 RateTableRateID,

								 CASE WHEN (tr.Code is not null OR tr.Code like concat(v.Code,'%')) THEN
									 tr.Code
								 ELSE
									 v.Code
								 END 	as RowCode,
								 CASE WHEN (tr1.Code is not null OR tr1.Code like concat(v.OriginationCode,'%')) THEN
									 tr1.Description
								 ELSE
									 concat(v.OriginationDescription,'*')
								 END
											as OriginationDescription,
								 CASE WHEN (tr.Code is not null OR tr.Code like concat(v.Code,'%')) THEN
									 tr.Description
								 ELSE
									 concat(v.Description,'*')
								 END
											as Description,
								 v.AccountId ,
								 v.Blocked,
								 v.AccountName ,
								 v.OriginationCode ,
								 v.Code ,
								 v.Rate ,
								 v.ConnectionFee,
								 v.EffectiveDate ,
								 v.Preference
							 FROM tmp_VendorRateByRank_ v

								 left join  tmp_all_code_ 		SplitCode   on v.Code = SplitCode.Code
								 left join  tmp_all_code_dup 	SplitCode2  on v.OriginationCode != '' AND v.OriginationCode = SplitCode2.Code

								 LEFT JOIN (	select Code,Description from tblRate where CodeDeckId=p_codedeckID
																																				 AND
																																				 (
																																					 (
																																						 ( CHAR_LENGTH(RTRIM(p_code)) = 0  OR Code LIKE REPLACE(p_code,'*', '%') )
																																						 AND ( p_Description = ''  OR Description LIKE REPLACE(p_Description,'*', '%') )
																																					 )

																																				 )

													 ) tr on tr.Code=SplitCode.Code

								 LEFT JOIN (	select Code,Description from tblRate where CodeDeckId=p_codedeckID
																																				 AND
																																				 (
																																					 (

																																						 ( CHAR_LENGTH(RTRIM(p_Originationcode)) = 0  OR Code LIKE REPLACE(p_Originationcode,'*', '%') )
																																						 AND ( p_OriginationDescription = ''  OR Description LIKE REPLACE(p_OriginationDescription,'*', '%') )

																																					 )
																																				 )

													 ) tr1 on tr1.Code=SplitCode2.Code


							 where  SplitCode.Code is not null AND  (p_isExport = 1 OR p_isExport = 2  OR (p_isExport = 0 AND rankname <= p_Position))

						 ) tmp
				order by AccountID,RowCode desc ,LENGTH(RowCode), OriginationCode, Code desc, LENGTH(OriginationCode), LENGTH(Code)  desc;

		ELSE

			insert ignore into tmp_VendorRate_stage_1 (
				RateTableRateID,

				RowCode,
				AccountId ,
				Blocked,
				AccountName ,
				OriginationCode ,
				Code ,
				Rate ,
				ConnectionFee,
				EffectiveDate ,
				OriginationDescription ,
				Description ,
				Preference
			)
				SELECT
					distinct
					RateTableRateID,

					SplitCode.RowCode,
					v.AccountId ,
					Blocked,
					v.AccountName ,
					v.OriginationCode ,
					v.Code ,
					v.Rate ,
					v.ConnectionFee,
					v.EffectiveDate ,
					tr.Description as OriginationDescription,
					tr.Description,

					v.Preference
				FROM tmp_VendorRateByRank_ v

					left join  tmp_all_code_ SplitCode   on v.Code = SplitCode.Code
					inner join tblRate tr  on  SplitCode.RowCode = tr.Code AND  tr.CodeDeckId = p_codedeckID

				where  SplitCode.Code is not null and ((p_isExport = 1  OR p_isExport = 2) OR (p_isExport = 0 AND rankname <= p_Position))

				order by AccountID,SplitCode.RowCode desc ,LENGTH(SplitCode.RowCode), v.Code desc, LENGTH(v.Code)  desc;

		END IF;

		insert ignore into tmp_VendorRate_stage_
			SELECT
				distinct
				RateTableRateID,

				RowCode,
				v.AccountId ,
				Blocked,
				v.AccountName ,
				v.OriginationCode ,
				v.Code ,
				v.Rate ,
				v.ConnectionFee,
				v.EffectiveDate ,
				v.OriginationDescription,
				v.Description,
				v.Preference,
				@rank := ( CASE WHEN(@prev_OriginationCode = OriginationCode and @prev_RowCode = RowCode  AND @prev_AccountID = v.AccountId     )
					THEN  @rank + 1
									 ELSE 1
									 END
				) AS MaxMatchRank,
				@prev_OriginationCode := v.OriginationCode,
				@prev_RowCode := RowCode	 ,
				@prev_AccountID := v.AccountId

			FROM tmp_VendorRate_stage_1 v
				, (SELECT  @prev_OriginationCode := NUll , @prev_RowCode := '',  @rank := 0 , @prev_Code := '' , @prev_AccountID := Null) f
			order by AccountID,OriginationCode,RowCode desc ;



		IF p_groupby = 'description' THEN

			insert ignore into tmp_VendorRate_
				select
					distinct
					max(RateTableRateID),

					AccountId ,
					max(Blocked) ,
					max(AccountName) ,
					max(OriginationCode) ,
					max(Code) ,
					max(Rate) ,
					max(ConnectionFee),
					max(EffectiveDate) ,
					OriginationDescription ,
					Description ,
					max(Preference),
					max(RowCode)
				from tmp_VendorRate_stage_
				where MaxMatchRank = 1
				group by AccountId,OriginationDescription,Description
				order by AccountId,OriginationDescription, Description asc;

		ELSE

			insert ignore into tmp_VendorRate_
				select
					distinct
					RateTableRateID,

					AccountId ,
					Blocked,
					AccountName ,
					OriginationCode ,
					Code ,
					Rate ,
					ConnectionFee,
					EffectiveDate ,
					OriginationDescription ,
					Description ,
					Preference,
					RowCode
				from tmp_VendorRate_stage_
				where MaxMatchRank = 1
				order by RowCode desc;
		END IF;






		IF( p_Preference = 0 )
		THEN

			IF p_groupby = 'description' THEN


				insert into tmp_final_VendorRate_
					SELECT
						RateTableRateID,

						AccountId ,
						Blocked,
						AccountName ,
						OriginationCode ,
						Code ,
						Rate ,
						ConnectionFee,
						EffectiveDate ,
						OriginationDescription ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								RateTableRateID,

								AccountId ,
								Blocked,
								AccountName ,
								OriginationCode ,
								Code ,
								Rate ,
								ConnectionFee,
								EffectiveDate ,
								OriginationDescription ,
								Description ,
								Preference,
								RowCode,
								@rank := CASE WHEN (@prev_OriginationDescription = OriginationDescription AND @prev_Description    = Description AND  @prev_Rate <  Rate ) THEN @rank+1
												 WHEN (@prev_OriginationDescription = OriginationDescription AND @prev_Description    = Description AND  @prev_Rate = Rate ) THEN @rank
												 ELSE
													 1
												 END
									AS FinalRankNumber,
								@prev_OriginationDescription := OriginationDescription ,
								@prev_Description  := Description,
								@prev_Rate  := Rate
							from tmp_VendorRate_
								,( SELECT @rank := 0 , @prev_OriginationDescription := ''  , @prev_Description := '' , @prev_Rate := 0 ) x
							order by OriginationDescription,Description,Rate,AccountId ASC

						) tbl1
					where
						(p_isExport = 1  OR p_isExport = 2) OR (p_isExport = 0 AND FinalRankNumber <= p_Position);
			ELSE


				insert into tmp_final_VendorRate_
					SELECT
						RateTableRateID,

						AccountId ,
						Blocked,
						AccountName ,
						OriginationCode ,
						Code ,
						Rate ,
						ConnectionFee,
						EffectiveDate ,
						OriginationDescription ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								RateTableRateID,

								AccountId ,
								Blocked,
								AccountName ,
								OriginationCode ,
								Code ,
								Rate ,
								ConnectionFee,
								EffectiveDate ,
								OriginationDescription ,
								Description ,
								Preference,
								RowCode,
								@rank := CASE WHEN ( @prev_OriginationCode    = OriginationCode AND  @prev_RowCode     = RowCode AND @prev_Rate <  Rate ) THEN @rank+1
												 WHEN (@prev_OriginationCode    = OriginationCode AND   @prev_RowCode    = RowCode AND @prev_Rate = Rate ) THEN @rank
												 ELSE
													 1
												 END
									AS FinalRankNumber,
								@prev_OriginationCode  := OriginationCode,
								@prev_RowCode  := RowCode,
								@prev_Rate  := Rate
							from tmp_VendorRate_
								,( SELECT @rank := 0 , @prev_RowCode := '' , @prev_Rate := 0 ) x
							order by OriginationCode,RowCode,Rate,AccountId ASC

						) tbl1
					where
						(p_isExport = 1  OR p_isExport = 2) OR (p_isExport = 0 AND FinalRankNumber <= p_Position);

			END IF;

		ELSE

			IF p_groupby = 'description' THEN

				insert into tmp_final_VendorRate_
					SELECT
						RateTableRateID,

						AccountId ,
						Blocked,
						AccountName ,
						OriginationCode ,
						Code ,
						Rate ,
						ConnectionFee,
						EffectiveDate ,
						OriginationDescription ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								RateTableRateID,

								AccountId ,
								Blocked,
								AccountName ,
								OriginationCode ,
								Code ,
								Rate ,
								ConnectionFee,
								EffectiveDate ,
								OriginationDescription ,
								Description ,
								Preference,
								RowCode,
								@preference_rank := CASE WHEN (@prev_OriginationDescription    = OriginationDescription AND @prev_Description     = Description AND @prev_Preference > Preference  )   THEN @preference_rank + 1
																		WHEN (@prev_OriginationDescription    = OriginationDescription AND @prev_Description     = Description AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1
																		WHEN (@prev_OriginationDescription    = OriginationDescription AND @prev_Description    = Description AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank
																		ELSE 1 END AS FinalRankNumber,
								@prev_OriginationDescription := OriginationDescription,
								@prev_Description := Description,
								@prev_Preference := Preference,
								@prev_Rate := Rate
							from tmp_VendorRate_
								,(SELECT @preference_rank := 0 , @prev_OriginationDescription := ''  ,  @prev_Description := ''  , @prev_Preference := 5,  @prev_Rate := 0) x
							order by OriginationDescription,Description ASC ,Preference DESC ,Rate ASC ,AccountId ASC

						) tbl1
					where
						(p_isExport = 1  OR p_isExport = 2 ) OR (p_isExport = 0 AND FinalRankNumber <= p_Position);
			ELSE

				insert into tmp_final_VendorRate_
					SELECT
						RateTableRateID,

						AccountId ,
						Blocked,
						AccountName ,
						OriginationCode ,
						Code ,
						Rate ,
						ConnectionFee,
						EffectiveDate ,
						OriginationDescription ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								RateTableRateID,

								AccountId ,
								Blocked,
								AccountName ,
								OriginationCode ,
								Code ,
								Rate ,
								ConnectionFee,
								EffectiveDate ,
								OriginationDescription ,
								Description ,
								Preference,
								RowCode,
								@preference_rank := CASE WHEN (@prev_OriginationCode    = OriginationCode AND  @prev_Code     = RowCode AND @prev_Preference > Preference  )   THEN @preference_rank + 1
																		WHEN (@prev_OriginationCode    = OriginationCode AND  @prev_Code     = RowCode AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1
																		WHEN (@prev_OriginationCode    = OriginationCode AND  @prev_Code    = RowCode AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank
																		ELSE 1 END AS FinalRankNumber,
								@prev_OriginationCode := OriginationCode,
								@prev_Code := RowCode,
								@prev_Preference := Preference,
								@prev_Rate := Rate
							from tmp_VendorRate_
								,(SELECT @preference_rank := 0 , @prev_OriginationCode := ''  , @prev_Code := ''  , @prev_Preference := 5,  @prev_Rate := 0) x
							order by OriginationCode,RowCode ASC ,Preference DESC ,Rate ASC ,AccountId  ASC

						) tbl1
					where
						(p_isExport = 1  OR p_isExport = 2 ) OR (p_isExport = 0 AND FinalRankNumber <= p_Position);
			END IF;

		END IF;


		SET @stm_columns = "";


		IF p_isExport = 0 AND p_Position > 10 THEN
			SET p_Position = 10;
		END IF;



		-- for routing engine only
		IF p_isExport = 2
		THEN

			IF p_groupby = 'description' THEN

				SELECT
					distinct
					ANY_VALUE(RateTableRateID) as RateTableRateID,
					ANY_VALUE(AccountId) as AccountId,
					ANY_VALUE(Blocked) as Blocked,
					ANY_VALUE(AccountName) as AccountName,
					ANY_VALUE(OriginationCode) as OriginationCode,
					ANY_VALUE(Code) as Code,
					ANY_VALUE(Rate) as Rate,
					ANY_VALUE(ConnectionFee) as ConnectionFee,
					ANY_VALUE(EffectiveDate ) as EffectiveDate,
					ANY_VALUE(OriginationDescription) as OriginationDescription,
					Description as Description,
					ANY_VALUE(Preference) as Preference,
					ANY_VALUE(RowCode) as RowCode,
					ANY_VALUE(FinalRankNumber) as FinalRankNumber
				from tmp_final_VendorRate_
				GROUP BY  OriginationDescription,Description ORDER BY RowCode ASC;

			ELSE


				SELECT
					distinct
					ANY_VALUE(RateTableRateID) as RateTableRateID,
					ANY_VALUE(AccountId) as AccountId,
					ANY_VALUE(Blocked) as Blocked,
					ANY_VALUE(AccountName) as AccountName,
					ANY_VALUE(OriginationCode) as OriginationCode,
					ANY_VALUE(Code) as Code,
					ANY_VALUE(Rate) as Rate,
					ANY_VALUE(ConnectionFee) as ConnectionFee,
					ANY_VALUE(EffectiveDate ) as EffectiveDate,
					ANY_VALUE(OriginationDescription) as OriginationDescription,
					ANY_VALUE(Description) as Description,
					ANY_VALUE(Preference) as Preference,
					RowCode as RowCode,
					ANY_VALUE(FinalRankNumber) as FinalRankNumber
				from tmp_final_VendorRate_
				GROUP BY  OriginationCode,RowCode ORDER BY RowCode ASC;


			END IF;


		ELSE

			-- for export and normanl lcr screen.


			IF p_isExport = 1  OR p_isExport = 2 THEN
				SELECT MAX(FinalRankNumber) INTO p_Position FROM tmp_final_VendorRate_;
			END IF;



			SET v_pointer_=1;
			WHILE v_pointer_ <= p_Position
			DO

				IF (p_isExport = 0)
				THEN
					SET @stm_columns = CONCAT(@stm_columns, "GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = ",v_pointer_,", CONCAT(ANY_VALUE(t.OriginationCode), '<br>', ANY_VALUE(t.OriginationDescription), '<br>',ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Description), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.RateTableRateID), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.Blocked) , '-', ANY_VALUE(t.Preference)  ), NULL)) AS `POSITION ",v_pointer_,"`,");
				ELSE
					SET @stm_columns = CONCAT(@stm_columns, "GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = ",v_pointer_,", CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Description), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y')), NULL))  AS `POSITION ",v_pointer_,"`,");
				END IF;

				SET v_pointer_ = v_pointer_ + 1;

			END WHILE;

			SET @stm_columns = TRIM(TRAILING ',' FROM @stm_columns);



			IF (p_isExport = 0)
			THEN


				IF p_groupby = 'description' THEN

					SET @stm_query = CONCAT("SELECT CONCAT(max(t.OriginationDescription) , ' <br> => ' , max(t.Description)) as Destination,",@stm_columns," FROM tmp_final_VendorRate_  t GROUP BY  t.Description ORDER BY t.OriginationDescription, t.Description ASC LIMIT ",p_RowspPage," OFFSET ",v_OffSet_," ;");

					SELECT count(Description) as totalcount from tmp_final_VendorRate_  ;

				ELSE

					SET @stm_query = CONCAT("SELECT CONCAT(ANY_VALUE(t.OriginationCode) , ' : ' , ANY_VALUE(t.OriginationDescription), ' <br> => '  ,ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,", @stm_columns," FROM tmp_final_VendorRate_  t GROUP BY  OriginationCode,RowCode ORDER BY RowCode ASC LIMIT ",p_RowspPage," OFFSET ",v_OffSet_," ;");

					SELECT count(RowCode) as totalcount from tmp_final_VendorRate_ ;

				END IF;


			END IF;

			IF p_isExport = 1
			THEN

				IF p_groupby = 'description' THEN

					SET @stm_query = CONCAT("SELECT CONCAT(max(t.OriginationDescription) , '  => ' , max(t.Description)) as Destination,",@stm_columns," FROM tmp_final_VendorRate_  t GROUP BY  t.Description ORDER BY t.Description ASC ;");


				ELSE

					SET @stm_query = CONCAT("SELECT CONCAT(ANY_VALUE(t.OriginationCode) , ' : ' , ANY_VALUE(t.OriginationDescription), '  => '  ,ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,", @stm_columns," FROM tmp_final_VendorRate_  t GROUP BY  RowCode ORDER BY RowCode ASC;");


				END IF;


			END IF;




			PREPARE stm_query FROM @stm_query;
			EXECUTE stm_query;
			DEALLOCATE PREPARE stm_query;

		END IF;


		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

	END//
DELIMITER ;

-- Dumping structure for procedure speakintelligentRM.prc_GetLCRwithPrefix
DROP PROCEDURE IF EXISTS `prc_GetLCRwithPrefix`;
DELIMITER //
CREATE DEFINER=`root`@`%` PROCEDURE `prc_GetLCRwithPrefix`(
	IN `p_companyid` INT,
	IN `p_trunkID` INT,
	IN `p_TimezonesID` VARCHAR(50),
	IN `p_codedeckID` INT,
	IN `p_CurrencyID` INT,
	IN `p_Originationcode` VARCHAR(50),
	IN `p_OriginationDescription` VARCHAR(250),
	IN `p_code` VARCHAR(50),
	IN `p_Description` VARCHAR(250),
	IN `p_AccountIds` TEXT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_SortOrder` VARCHAR(50),
	IN `p_Preference` INT,
	IN `p_Position` INT,
	IN `p_vendor_block` INT,
	IN `p_groupby` VARCHAR(50),
	IN `p_SelectedEffectiveDate` DATE,
	IN `p_ShowAllVendorCodes` INT,
	IN `p_merge_timezones` INT,
	IN `p_TakePrice` INT,
	IN `p_isExport` INT



























)
		ThisSP:BEGIN

		DECLARE v_OffSet_ int;
		DECLARE v_Code VARCHAR(50) ;
		DECLARE v_pointer_ int;
		DECLARE v_rowCount_ int;
		DECLARE v_p_code VARCHAR(50);
		DECLARE v_Codlen_ int;
		DECLARE v_position int;
		DECLARE v_p_code__ VARCHAR(50);
		DECLARE v_has_null_position int ;
		DECLARE v_next_position1 VARCHAR(200) ;
		DECLARE v_CompanyCurrencyID_ INT;


		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		SET @@session.collation_connection='utf8_unicode_ci';
		SET @@session.character_set_results='utf8';

		SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage_;
		CREATE TEMPORARY TABLE tmp_VendorRate_stage_ (
			RateTableRateID int,
			-- VendorConnectionID int,
			RowCode VARCHAR(50) ,
			AccountId INT ,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) ,
			OriginationCode VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			OriginationDescription VARCHAR(255),
			Description VARCHAR(255),
			Preference INT,
			MaxMatchRank int ,
			prev_prev_RowCode VARCHAR(50),
			prev_AccountID int
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage2_;
		CREATE TEMPORARY TABLE tmp_VendorRate_stage2_ (
			RateTableRateID int,
			-- VendorConnectionID int,
			RowCode VARCHAR(50) ,
			AccountId INT ,
			AccountName VARCHAR(100) ,
			OriginationCode VARCHAR(50) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			OriginationDescription VARCHAR(255),
			Description VARCHAR(255),
			Preference INT,
			MaxMatchRank int ,
			prev_prev_RowCode VARCHAR(50),
			prev_AccountID int
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
		CREATE TEMPORARY TABLE tmp_VendorRate_ (
			RateTableRateID int,
			-- VendorConnectionID int,
			AccountId INT ,
			Blocked INT DEFAULT 0,
			AccountName VARCHAR(100) ,
			OriginationCode VARCHAR(50) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			RateID int,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			OriginationDescription VARCHAR(255),
			Description VARCHAR(255),
			Preference INT,
			RowCode VARCHAR(50)
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_final_VendorRate_;
		CREATE TEMPORARY TABLE tmp_final_VendorRate_ (
			RateTableRateID int,
			-- VendorConnectionID int,
			AccountId INT ,
			Blocked INT DEFAULT 0,
			AccountName VARCHAR(100) ,
			OriginationCode VARCHAR(50) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			RateID int,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			OriginationDescription VARCHAR(255),
			Description VARCHAR(255),
			Preference INT,
			RowCode VARCHAR(50),
			FinalRankNumber int
		)
		;


		DROP TEMPORARY TABLE IF EXISTS tmp_search_code_;
		CREATE TEMPORARY TABLE tmp_search_code_ (
			Code  varchar(50),
			INDEX Index1 (Code)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_code_;
		CREATE TEMPORARY TABLE tmp_code_ (
			RowCode  varchar(50),
			Code  varchar(50),
			RowNo int,
			INDEX Index1 (Code)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_all_code_;
		CREATE TEMPORARY TABLE tmp_all_code_ (
			RowCode  varchar(50),
			Code  varchar(50),

			INDEX Index2 (Code)
		)
		;


		DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates_(
			RateTableRateID int,
			-- VendorConnectionID int,
			AccountId int,
			Blocked INT DEFAULT 0,
			AccountName varchar(200),
			OriginationCode varchar(50),
			Code varchar(50),
			OriginationDescription varchar(200),
			Description varchar(200),
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate date,
			TrunkID int,
			CountryID int,
			RateID int,
			Preference int,
			INDEX IX_Code (Code)
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates1_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates1_(
			RateTableRateID int,
			-- VendorConnectionID int,
			AccountId int,
			Blocked INT DEFAULT 0,
			AccountName varchar(200),
			OriginationCode varchar(50),
			Code varchar(50),
			OriginationDescription varchar(200),
			Description varchar(200),
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate date,
			TrunkID int,
			CountryID int,
			OriginationRateID int,
			RateID int,
			Preference int,
			INDEX IX_Code (Code),
			INDEX tmp_VendorCurrentRates_AccountId (`AccountId`,`TrunkID`,`RateId`,`EffectiveDate`)
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateByRank_;
		CREATE TEMPORARY TABLE tmp_VendorRateByRank_ (
			RateTableRateID int,
			-- VendorConnectionID int,
			AccountId INT ,
			Blocked INT DEFAULT 0,
			AccountName VARCHAR(100) ,
			OriginationCode VARCHAR(50) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			RateID int,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			OriginationDescription VARCHAR(255),
			Description VARCHAR(255),
			Preference INT,
			rankname INT,
			INDEX IX_Code (Code,rankname)
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_block0;
		CREATE TEMPORARY TABLE tmp_block0(
			AccountId INT,
			AccountName VARCHAR(200),
			des VARCHAR(200),
			RateId INT
		);

		SELECT CurrencyId INTO v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = p_companyid;



		SET @num := 0, @AccountID := '', @TrunkID := '', @RateID := '';

		SET @stm_show_all_vendor_codes = CONCAT("INNER JOIN (SELECT Code,Description FROM tblRate WHERE CodeDeckId=",p_codedeckID,") tmpselectedcd ON tmpselectedcd.Code=tblRate.Code");


		SET @stm_filter_oringation_code = CONCAT('INNER JOIN tblRate r2 ON r2.CompanyID = ',p_companyid,' AND tblRateTableRate.OriginationRateID = r2.RateID
						AND ( CHAR_LENGTH(RTRIM("',p_Originationcode,'")) = 0 OR r2.Code LIKE REPLACE("',p_Originationcode,'","*", "%") )
						AND ( "',p_OriginationDescription,'"=""  OR r2.Description LIKE REPLACE("',p_OriginationDescription,'","*", "%") )
				');


		SET @stm_origination_code_filter = 		CONCAT('CASE WHEN  ');

		SET @stm = CONCAT('
			INSERT INTO tmp_VendorCurrentRates1_
			',
											IF (p_merge_timezones = 1,"
				SELECT DISTINCT
					RateTableRateID,

					AccountId,
					Blocked,
					AccountName,
					OriginationCode,
					Code,
					OriginationDescription,
					Description,
					Rate,
					ConnectionFee,
					EffectiveDate,
					TrunkID,
					CountryID,
					OriginationRateID,
					RateID,
					Preference
				FROM (
			",""),'

				Select DISTINCT
					RateTableRateID,

					AccountId,
					Blocked,
					AccountName,
					OriginationCode,
					Code,
					OriginationDescription,
					Description,
					Rate,
					ConnectionFee,
					EffectiveDate,
					TrunkID,
					CountryID,
					OriginationRateID,
					RateID,
					Preference
					',IF (p_merge_timezones = 1,",
						@num := if(@AccountID = AccountID AND @TrunkID = TrunkID AND @RateID = RateID, @num + 1, 1) as row_number,
						@AccountID := AccountID,
						@TrunkID := TrunkID,
						@RateID := RateID
					",""),'
				FROM (
					SELECT distinct
						RateTableRateID,

						tblAccount.AccountId,
						tblRateTableRate.Blocked,
						vt.Name as AccountName,
						IFNULL(r2.Code,"") as OriginationCode,
						tblRate.Code,
						IFNULL(r2.Description,"") as OriginationDescription ,
						tblRate.Description,
						CASE WHEN  tblAccount.CurrencyId = ',p_CurrencyID,'
						THEN
							tblRateTableRate.Rate
						WHEN  ',v_CompanyCurrencyID_,' = ',p_CurrencyID,'
						THEN
						(
							( tblRateTableRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = ',p_companyid,' ) )
						)
						ELSE
						(
							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' and  CompanyID = ',p_companyid,' )
							* (tblRateTableRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and CompanyID = ',p_companyid,' ))
						)
						END as Rate,
						ConnectionFee,
						DATE_FORMAT (tblRateTableRate.EffectiveDate, "%Y-%m-%d") AS EffectiveDate,
						vt.TrunkID, tblRate.CountryID,
						r2.RateID as OriginationRateID,
						tblRate.RateID,
						IFNULL(Preference, 5) AS Preference
					FROM
						tblRateTableRate
					INNER JOIN tblVendorConnection vt ON vt.CompanyID = ',p_companyid,' and vt.RateTableID = tblRateTableRate.RateTableID  and vt.RateTypeID = 1   and vt.Active = 1 and vt.TrunkID = ',p_trunkID,'
					INNER JOIN tblAccount ON tblAccount.AccountID = vt.AccountId AND tblAccount.CompanyID = ',p_companyid,' AND vt.AccountId = tblAccount.AccountID
					INNER JOIN tblRate ON tblRate.CompanyID = ',p_companyid,' AND tblRateTableRate.RateId = tblRate.RateID

						LEFT JOIN tblRate r2 ON r2.CompanyID = ',p_companyid,' AND tblRateTableRate.OriginationRateID = r2.RateID
						AND ( CHAR_LENGTH(RTRIM("',p_Originationcode,'")) = 0 OR r2.Code LIKE REPLACE("',p_Originationcode,'","*", "%") )
						AND ( "',p_OriginationDescription,'"=""  OR r2.Description LIKE REPLACE("',p_OriginationDescription,'","*", "%") )

					',
											IF (p_ShowAllVendorCodes = 1,"",@stm_show_all_vendor_codes)
		,'
					WHERE
						( CHAR_LENGTH(RTRIM("',p_code,'")) = 0 OR tblRate.Code LIKE REPLACE("',p_code,'","*", "%") )
						AND ("',p_Description,'"="" OR tblRate.Description LIKE REPLACE("',p_Description,'","*","%"))

						AND ( "',p_Originationcode,'" = ""  OR  ( r2.RateID IS NOT NULL ) )
						AND ( "',p_OriginationDescription,'" = ""  OR  ( r2.RateID IS NOT NULL ) )

						AND ("',p_AccountIds,'"="" OR FIND_IN_SET(tblAccount.AccountID,"',p_AccountIds,'") != 0 )
						-- AND EffectiveDate <= NOW()
						AND EffectiveDate <= DATE("',p_SelectedEffectiveDate,'")
						AND (tblRateTableRate.EndDate is NULL OR tblRateTableRate.EndDate > now() )    -- rate should not end Today
						AND tblAccount.IsVendor = 1
						AND tblAccount.Status = 1
						AND tblAccount.CurrencyId is not NULL
						AND (
							(',p_merge_timezones,' = 0 AND tblRateTableRate.TimezonesID = "',p_TimezonesID,'") OR
							(',p_merge_timezones,' = 1 AND FIND_IN_SET(tblRateTableRate.TimezonesID, "',p_TimezonesID,'"))
						)
						AND
						(
							(',p_vendor_block,' = 1 )
							OR
							(',p_vendor_block,' = 0 AND tblRateTableRate.Blocked = 0	)
						)
					',
											IF (p_merge_timezones = 1,CONCAT("ORDER BY AccountID, TrunkID, RateID, Rate ",IF(p_TakePrice=1,"DESC","ASC")),"")
		,'
				) tbl
			',
											IF (p_merge_timezones = 1,") AS x WHERE x.row_number <= 1","")
		,'
			ORDER BY Code ASC;');




		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;






		IF p_groupby = 'description' THEN

			INSERT INTO tmp_VendorCurrentRates_
				Select max(RateTableRateID),max(AccountId),max(Blocked),max(AccountName),max(OriginationCode),max(Code),OriginationDescription,Description, MAX(Rate),max(ConnectionFee),max(EffectiveDate),max(TrunkID),max(CountryID),max(RateID),max(Preference)
				FROM (

							 SELECT * ,
								 @row_num := IF(@prev_RateTableRateID = RateTableRateID AND @prev_AccountId = AccountID AND @prev_TrunkID = TrunkID AND @prev_OriginationDescription = OriginationDescription AND @prev_Description = Description AND @prev_EffectiveDate >= EffectiveDate, @row_num + 1, 1) AS RowID,
								 @prev_RateTableRateID := RateTableRateID,
								 @prev_AccountId := AccountID,
								 @prev_TrunkID := TrunkID,
								 @prev_OriginationDescription := OriginationDescription,
								 @prev_Description := Description,
								 @prev_EffectiveDate := EffectiveDate
							 FROM tmp_VendorCurrentRates1_
								 ,(SELECT @row_num := 1, @prev_RateTableRateID := '', @prev_AccountId := '',@prev_TrunkID := '', @prev_OriginationDescription := '', @prev_Description := '', @prev_OriginationRateID := '',@prev_RateId := '', @prev_EffectiveDate := '') x

							 ORDER BY AccountId, TrunkID, OriginationDescription,Description, EffectiveDate DESC
						 ) tbl
				WHERE RowID = 1
				group BY AccountName, TrunkID, Description, OriginationDescription
				order by Description, OriginationDescription asc;


		Else



			INSERT INTO tmp_VendorCurrentRates_
				Select RateTableRateID,AccountId,Blocked,AccountName,OriginationCode,Code,OriginationDescription,Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference
				FROM (
							 SELECT * ,
								 @row_num := IF(@prev_RateTableRateID = RateTableRateID AND @prev_AccountId = AccountID AND @prev_TrunkID = TrunkID AND @prev_OriginationRateID = OriginationRateID AND @prev_RateId = RateID AND @prev_EffectiveDate >= EffectiveDate, @row_num + 1, 1) AS RowID,
								 @prev_RateTableRateID := RateTableRateID,
								 @prev_AccountId := AccountID,
								 @prev_TrunkID := TrunkID,
								 @prev_OriginationRateID := OriginationRateID,
								 @prev_RateId := RateID,
								 @prev_EffectiveDate := EffectiveDate
							 FROM tmp_VendorCurrentRates1_
								 ,(SELECT @row_num := 1,@prev_RateTableRateID := '',  @prev_AccountId := '',@prev_TrunkID := '', @prev_OriginationRateID := '', @prev_RateId := '', @prev_EffectiveDate := '') x
							 ORDER BY AccountId, TrunkID, OriginationRateID,RateId, EffectiveDate,RateTableRateID DESC
						 ) tbl
				WHERE RowID = 1
				order by OriginationCode,Code asc;

		END IF;



		IF p_Preference = 1 THEN

			INSERT IGNORE INTO tmp_VendorRateByRank_
				SELECT
					RateTableRateID,
					AccountID,
					Blocked,
					AccountName,
					OriginationCode,
					Code,
					Rate,
					RateID,
					ConnectionFee,
					EffectiveDate,
					OriginationDescription,
					Description,
					Preference,
					preference_rank
				FROM (SELECT
								RateTableRateID,

								AccountID,
								Blocked,
								AccountName,
								OriginationCode,
								Code,
								Rate,
								RateID,
								ConnectionFee,
								EffectiveDate,
								OriginationDescription,
								Description,
								Preference,
								CASE WHEN p_groupby = 'description' THEN
									@preference_rank := 		  CASE WHEN ( @prev_OriginationDescription = OriginationDescription  AND @prev_Description     = Description AND @prev_Preference > Preference  					) THEN @preference_rank + 1
																					 WHEN ( @prev_OriginationDescription = OriginationDescription AND @prev_Description     = Description AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1
																					 WHEN ( @prev_OriginationDescription = OriginationDescription AND @prev_Description    = Description AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank
																					 ELSE 1
																					 END
								ELSE
									@preference_rank := CASE WHEN 			 (@prev_OriginationCode     = OriginationCode AND @prev_Code     = Code AND @prev_Preference > Preference  ) THEN @preference_rank + 1
																			WHEN (@prev_OriginationCode     = OriginationCode AND @prev_Code     = Code AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1
																			WHEN (@prev_OriginationCode     = OriginationCode AND @prev_Code    = Code AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank
																			ELSE 1
																			END
								END AS preference_rank,

								@prev_OriginationCode := OriginationCode,
								@prev_Code := Code,
								@prev_OriginationDescription := OriginationDescription,
								@prev_Description := Description,
								@prev_Preference := IFNULL(Preference, 5),
								@prev_Rate := Rate
							FROM tmp_VendorCurrentRates_ AS preference,
								(SELECT @preference_rank := 0 , @prev_Code := ''  , @prev_OriginationDescription := ''  , @prev_Description := ''  , @prev_Preference := 5,  @prev_Rate := 0) x
							ORDER BY
								CASE WHEN p_groupby = 'description' THEN
									preference.OriginationDescription
								ELSE
									preference.OriginationCode
								END ASC ,

								CASE WHEN p_groupby = 'description' THEN
									preference.Description
								ELSE
									preference.Code
								END ASC ,
								preference.Preference DESC, preference.Rate ASC,preference.AccountId ASC

						 ) tbl
				WHERE ( p_isExport = 1 OR p_isExport = 2 ) OR (p_isExport = 0 AND preference_rank <= p_Position)


				ORDER BY OriginationCode,Code, preference_rank;

		ELSE

			INSERT IGNORE INTO tmp_VendorRateByRank_
				SELECT
					RateTableRateID,

					AccountID,
					Blocked,
					AccountName,
					OriginationCode,
					Code,
					Rate,
					RateID,
					ConnectionFee,
					EffectiveDate,
					OriginationDescription,
					Description,
					Preference,
					RateRank
				FROM (
							 SELECT
								 RateTableRateID,

								 AccountID,
								 Blocked,
								 AccountName,
								 OriginationCode,
								 Code,
								 Rate,
								 RateID,
								 ConnectionFee,
								 EffectiveDate,
								 OriginationDescription,
								 Description,
								 Preference,
								 CASE WHEN p_groupby = 'description' THEN
									 @rank :=    CASE WHEN (@prev_OriginationDescription    = OriginationDescription AND @prev_Description    = Description AND @prev_Rate < Rate) THEN @rank + 1
															 WHEN (@prev_OriginationDescription    = OriginationDescription AND @prev_Description    = Description AND @prev_Description    = Description AND @prev_Rate = Rate) THEN @rank
															 ELSE 1
															 END
								 ELSE
									 @rank := 	CASE WHEN (@prev_OriginationCode    = OriginationCode AND @prev_Code    = Code AND @prev_Rate < Rate) THEN @rank + 1
														 WHEN (@prev_OriginationCode    = OriginationCode AND @prev_Code    = Code AND @prev_Rate = Rate) THEN @rank
														 ELSE 1
														 END
								 END
									 AS RateRank,
								 @prev_OriginationCode := OriginationCode,
								 @prev_Code := Code,
								 @prev_OriginationDescription = OriginationDescription AND
								 @prev_Description := Description,
								 @prev_Rate := Rate
							 FROM tmp_VendorCurrentRates_ AS rank,
								 (SELECT @rank := 0 , @prev_Code := '' ,@prev_OriginationCode := '',  @prev_OriginationDescription := ''  , @prev_Description := '' , @prev_Rate := 0) f
							 ORDER BY
								 CASE WHEN p_groupby = 'description' THEN
									 rank.OriginationDescription
								 ELSE
									 rank.OriginationCode
								 END ,
								 CASE WHEN p_groupby = 'description' THEN
									 rank.Description
								 ELSE
									 rank.Code
								 END ,
								 rank.Rate,rank.AccountId

						 ) tbl
				WHERE ( p_isExport = 1 OR p_isExport = 2 ) OR (p_isExport = 0 AND RateRank <= p_Position)
				ORDER BY OriginationCode,Code, RateRank;

		END IF;



		IF p_ShowAllVendorCodes = 1 THEN

			insert ignore into tmp_VendorRate_
				select
					distinct
					RateTableRateID,

					AccountId ,
					Blocked,
					AccountName ,
					v.OriginationCode ,
					v.Code,
					Rate,
					RateID,
					ConnectionFee,
					EffectiveDate,
					CASE WHEN (tr2.Code is not null) THEN
						tr2.Description
					ELSE
						concat(v.OriginationDescription,'*')
					END
								 as Description,
					CASE WHEN (tr.Code is not null) THEN
						tr.Description
					ELSE
						concat(v.Description,'*')
					END
								 as Description,
					Preference,
					v.Code as RowCode
				from tmp_VendorRateByRank_ v
					LEFT JOIN (
											select Code,Description from tblRate
											where CodeDeckId = p_codedeckID
														AND
														(
															( CHAR_LENGTH(RTRIM(p_code)) = 0  OR Code LIKE REPLACE(p_code,'*', '%') )
															AND ( p_Description = ''  OR Description LIKE REPLACE(p_Description,'*', '%') )
														)

										) tr on tr.Code=v.Code
					LEFT JOIN (
											select Code,Description from tblRate
											where CodeDeckId = p_codedeckID
														AND
														(
															( CHAR_LENGTH(RTRIM(p_Originationcode)) = 0  OR Code LIKE REPLACE(p_Originationcode,'*', '%') )
															AND ( p_OriginationDescription = ''  OR Description LIKE REPLACE(p_OriginationDescription,'*', '%') )

														)

										) tr2 on tr2.Code=v.OriginationCode

				order by RowCode desc;

		ELSE

			insert ignore into tmp_VendorRate_
				select
					distinct
					RateTableRateID,

					AccountId ,
					Blocked,
					AccountName ,
					OriginationCode ,
					Code ,
					Rate ,
					RateID,
					ConnectionFee,
					EffectiveDate ,
					OriginationDescription ,
					Description ,
					Preference,
					Code as RowCode
				from tmp_VendorRateByRank_
				order by RowCode desc;

		END IF;

		IF( p_Preference = 0 )
		THEN


			IF p_groupby = 'description' THEN


				insert into tmp_final_VendorRate_
					SELECT
						RateTableRateID,

						AccountId ,
						Blocked,
						AccountName ,
						OriginationCode ,
						Code ,
						Rate ,
						RateID,
						ConnectionFee,
						EffectiveDate ,
						OriginationDescription ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								RateTableRateID,

								AccountId,
								Blocked,
								AccountName ,
								OriginationCode ,
								Code ,
								Rate ,
								RateID,
								ConnectionFee,
								EffectiveDate ,
								OriginationDescription ,
								Description ,
								Preference,
								RowCode,
								@rank :=    CASE WHEN (@prev_OriginationDescription    = OriginationDescription AND  @prev_Description    = Description AND  @prev_Rate <  Rate ) THEN @rank+1
														WHEN (@prev_OriginationDescription    = OriginationDescription AND  @prev_Description    = Description AND  @prev_Rate = Rate ) THEN @rank
														ELSE
															1
														END
									AS FinalRankNumber,
								@prev_Rate  := Rate,
								@prev_OriginationDescription := OriginationDescription,
								@prev_Description := Description,
								@prev_RateID  := RateID

							from tmp_VendorRate_
								,( SELECT @rank := 0 , @prev_OriginationDescription := '' , @prev_Description := '' , @prev_Rate := 0 ) x
							order by Description,Rate,AccountId ASC

						) tbl1
					where
						( p_isExport = 1 OR p_isExport = 2 ) OR (p_isExport = 0 AND FinalRankNumber <= p_Position);

			ELSE

				insert into tmp_final_VendorRate_
					SELECT
						RateTableRateID,

						AccountId ,
						Blocked,
						AccountName ,
						OriginationCode ,
						Code ,
						Rate ,
						RateID,
						ConnectionFee,
						EffectiveDate ,
						OriginationDescription ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								RateTableRateID,

								AccountId ,
								Blocked,
								AccountName ,
								OriginationCode ,
								Code ,
								Rate ,
								RateID,
								ConnectionFee,
								EffectiveDate ,
								OriginationDescription ,
								Description ,
								Preference,
								RowCode,
								@rank := CASE WHEN (@prev_OriginationCode    = OriginationCode AND  @prev_RowCode     = RowCode AND @prev_Rate <  Rate ) THEN @rank+1
												 WHEN (@prev_OriginationCode    = OriginationCode AND  @prev_RowCode    = RowCode AND @prev_Rate = Rate ) THEN @rank
												 ELSE
													 1
												 END
									AS FinalRankNumber,
								@prev_OriginationCode  := OriginationCode,
								@prev_RowCode  := RowCode,
								@prev_Rate  := Rate
							from tmp_VendorRate_
								,( SELECT @rank := 0 , @prev_OriginationCode := '' , @prev_RowCode := '' , @prev_Rate := 0 ) x
							order by RowCode,Rate,AccountId ASC

						) tbl1
					where
						( p_isExport = 1 OR p_isExport = 2 ) OR (p_isExport = 0 AND FinalRankNumber <= p_Position);

			END IF;

		ELSE

			IF p_groupby = 'description' THEN

				insert into tmp_final_VendorRate_
					SELECT
						RateTableRateID,

						AccountId ,
						Blocked,
						AccountName ,
						OriginationCode ,
						Code ,
						Rate ,
						RateID,
						ConnectionFee,
						EffectiveDate ,
						OriginationDescription ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								RateTableRateID,

								AccountId ,
								Blocked
										 AccountName ,
								OriginationCode ,
								Code ,
								Rate ,
								RateID,
								ConnectionFee,
								EffectiveDate ,
								OriginationDescription ,
								Description ,
								Preference,
								RowCode,
								@preference_rank := 				CASE WHEN (@prev_OriginationDescription    = OriginationDescription AND @prev_Description    = Description AND @prev_Preference > Preference  )   THEN @preference_rank + 1
																					 WHEN (@prev_OriginationDescription    = OriginationDescription AND @prev_Description    = Description AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1
																					 WHEN (@prev_OriginationDescription    = OriginationDescription AND @prev_Description    = Description AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank
																					 ELSE 1 END
									AS FinalRankNumber,
								@prev_Preference := Preference,
								@prev_OriginationDescription := OriginationDescription,
								@prev_Description := Description,
								@prev_Rate := Rate
							from tmp_VendorRate_
								,(SELECT @preference_rank := 0 , @prev_Preference := 5, @prev_Description := '', @prev_OriginationDescription := '',  @prev_Rate := 0) x
							order by Description ASC ,Preference DESC ,Rate ASC ,AccountId ASC

						) tbl1
					where
						( p_isExport = 1 OR p_isExport = 2 ) OR (p_isExport = 0 AND FinalRankNumber <= p_Position);
			ELSE


				insert into tmp_final_VendorRate_
					SELECT
						RateTableRateID,

						AccountId ,
						Blocked,
						AccountName ,
						OriginationCode ,
						Code ,
						Rate ,
						RateID,
						ConnectionFee,
						EffectiveDate ,
						OriginationDescription ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								RateTableRateID,

								AccountId ,
								Blocked,
								AccountName ,
								OriginationCode ,
								Code ,
								Rate ,
								RateID,
								ConnectionFee,
								EffectiveDate ,
								OriginationDescription ,
								Description ,
								Preference,
								RowCode,
								@preference_rank := 				CASE WHEN (@prev_OriginationCode    = OriginationCode AND  @prev_Code     = RowCode AND @prev_Preference > Preference  )   THEN @preference_rank + 1
																					 WHEN (@prev_OriginationCode    = OriginationCode AND  @prev_Code     = RowCode AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1
																					 WHEN (@prev_OriginationCode    = OriginationCode AND @prev_Code    = RowCode AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank
																					 ELSE 1 END
									AS FinalRankNumber,
								@prev_OriginationCode := OriginationCode,
								@prev_Code := RowCode,
								@prev_Preference := Preference,
								@prev_Rate := Rate
							from tmp_VendorRate_
								,(SELECT @preference_rank := 0 , @prev_Code := ''  , @prev_OriginationCode := ''  , @prev_Preference := 5,  @prev_Rate := 0) x
							order by RowCode ASC ,Preference DESC ,Rate ASC ,AccountId ASC

						) tbl1
					where
						( p_isExport = 1 OR p_isExport = 2 ) OR (p_isExport = 0 AND FinalRankNumber <= p_Position);


			END IF;
		END IF;


		SET @stm_columns = "";


		IF p_isExport = 0 AND p_Position > 10 THEN
			SET p_Position = 10;
		END IF;


		-- for routing engine only
		IF p_isExport = 2
		THEN

			IF p_groupby = 'description' THEN

				SELECT
					distinct
					ANY_VALUE(RateTableRateID) as RateTableRateID,
					ANY_VALUE(AccountId) as AccountId,
					ANY_VALUE(Blocked) as Blocked,
					ANY_VALUE(AccountName) as AccountName,
					ANY_VALUE(OriginationCode) as OriginationCode,
					ANY_VALUE(Code) as Code,
					ANY_VALUE(Rate) as Rate,
					ANY_VALUE(ConnectionFee) as ConnectionFee,
					ANY_VALUE(EffectiveDate ) as EffectiveDate,
					ANY_VALUE(OriginationDescription) as OriginationDescription,
					Description as Description,
					ANY_VALUE(Preference) as Preference,
					ANY_VALUE(RowCode) as RowCode,
					ANY_VALUE(FinalRankNumber) as FinalRankNumber
				from tmp_final_VendorRate_
				GROUP BY  Description ORDER BY RowCode ASC;

			ELSE


				SELECT
					distinct
					ANY_VALUE(RateTableRateID) as RateTableRateID,
					ANY_VALUE(AccountId) as AccountId,
					ANY_VALUE(Blocked) as Blocked,
					ANY_VALUE(AccountName) as AccountName,
					ANY_VALUE(OriginationCode) as OriginationCode,
					ANY_VALUE(Code) as Code,
					ANY_VALUE(Rate) as Rate,
					ANY_VALUE(ConnectionFee) as ConnectionFee,
					ANY_VALUE(EffectiveDate ) as EffectiveDate,
					ANY_VALUE(OriginationDescription) as OriginationDescription,
					ANY_VALUE(Description) as Description,
					ANY_VALUE(Preference) as Preference,
					RowCode as RowCode,
					ANY_VALUE(FinalRankNumber) as FinalRankNumber
				from tmp_final_VendorRate_
				GROUP BY  OriginationCode,RowCode ORDER BY RowCode ASC;


			END IF;

		ELSE

			-- for export and normanl lcr screen.



			IF p_isExport = 1 THEN
				SELECT MAX(FinalRankNumber) INTO p_Position FROM tmp_final_VendorRate_;
			END IF;


			SET v_pointer_=1;
			WHILE v_pointer_ <= p_Position
			DO

				IF (p_isExport = 0)
				THEN
					SET @stm_columns = CONCAT(@stm_columns, "GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = ",v_pointer_,", CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.RateTableRateID), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.Blocked) , '-', ANY_VALUE(t.Preference)  ), NULL))AS `POSITION ",v_pointer_,"`,");
				ELSE
					SET @stm_columns = CONCAT(@stm_columns, "GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = ",v_pointer_,", CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y') ), NULL))AS `POSITION ",v_pointer_,"`,");
				END IF;

				SET v_pointer_ = v_pointer_ + 1;

			END WHILE;

			SET @stm_columns = TRIM(TRAILING ',' FROM @stm_columns);

			IF (p_isExport = 0)
			THEN

				IF p_groupby = 'description' THEN

					SET @stm_query = CONCAT("SELECT	CONCAT(max(t.OriginationDescription) , ' <br> => ' , max(t.Description)) as Destination,",@stm_columns," FROM tmp_final_VendorRate_  t GROUP BY t,OriginationDescription,t.Description ORDER BY t.OriginationDescription , t.Description ASC LIMIT ",p_RowspPage," OFFSET ",v_OffSet_," ;");


					SELECT count(Description) as totalcount from tmp_final_VendorRate_  ;

				ELSE

					SET @stm_query = CONCAT("SELECT	CONCAT(ANY_VALUE(t.OriginationCode) , ' : ' , ANY_VALUE(t.OriginationDescription), ' <br> => '  ,ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,",@stm_columns," FROM tmp_final_VendorRate_  t GROUP BY t.OriginationCode, t.RowCode ORDER BY RowCode ASC LIMIT ",p_RowspPage," OFFSET ",v_OffSet_," ;");



					SELECT count(RowCode) as totalcount from tmp_final_VendorRate_ ;

				END IF;


			ELSE

				IF p_groupby = 'description' THEN

					SET @stm_query = CONCAT("SELECT CONCAT(max(t.OriginationDescription) , ' => ' , max(t.Description))as Destination,",@stm_columns," FROM tmp_final_VendorRate_  t GROUP BY  t.OriginationDescription,t.Description ORDER BY t.Description ASC;");

				ELSE

					SET @stm_query = CONCAT("SELECT CONCAT(ANY_VALUE(t.OriginationCode) , ' : ' , ANY_VALUE(t.OriginationDescription), ' => '  ,ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,",@stm_columns," FROM tmp_final_VendorRate_  t GROUP BY  t.OriginationCode, t.RowCode  ORDER BY RowCode ASC;");


				END IF;


			END IF;

			PREPARE stm_query FROM @stm_query;
			EXECUTE stm_query;
			DEALLOCATE PREPARE stm_query;


		END IF;

		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

	END//
DELIMITER ;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
