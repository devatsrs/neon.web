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
use speakintelligentRM;
DROP PROCEDURE IF EXISTS `prc_GetDIDLCR`;
DELIMITER //
CREATE PROCEDURE `prc_GetDIDLCR`(
	IN `p_companyid` INT,
	IN `p_CountryID` varchar(100),
	IN `p_AccessType` varchar(100),
	IN `p_City` varchar(100),
	IN `p_Tariff` VARCHAR(50),
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
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_SortOrder` VARCHAR(50),

	IN `p_isExport` INT





























)
		ThisSP:BEGIN



		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		SET @v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

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
			AccessType varchar(200),
			CountryID int,
			City varchar(50),
			Tariff varchar(50),
			EffectiveDate DATE,
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
			AccessType varchar(200),
			CountryID int,
			City varchar(50),
			Tariff varchar(50),
			EffectiveDate DATE,

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
			AccessType varchar(200),
			CountryID int,
			City varchar(50),
			Tariff varchar(50),
			EffectiveDate DATE,


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


		DROP TEMPORARY TABLE IF EXISTS tmp_table_output_1;
		CREATE TEMPORARY TABLE tmp_table_output_1 (

			AccessType varchar(200),
			CountryID int,
			City varchar(50),
			Tariff varchar(50),
			Code varchar(100),
			VendorID int,
			VendorName varchar(200),
			EffectiveDate DATE,
			Total double(18,4)

		);


		DROP TEMPORARY TABLE IF EXISTS tmp_table_output_2;
		CREATE TEMPORARY TABLE tmp_table_output_2 (

			AccessType varchar(200),
			CountryID int,
			City varchar(50),
			Tariff varchar(50),
			Code varchar(100),
			VendorID int,
			VendorName varchar(200),
			EffectiveDate DATE,
			Total double(18,4),
			vPosition int


		);


		DROP TEMPORARY TABLE IF EXISTS tmp_final_table_output;
		CREATE TEMPORARY TABLE tmp_final_table_output (

			AccessType varchar(200),
			Country varchar(100),
			City varchar(50),
			Tariff varchar(50),
			Code varchar(100),
			VendorID int,
			VendorName varchar(200),
			EffectiveDate DATE,
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


		SET @p_Calls	 							 = p_Calls;
		SET @p_Minutes	 							 = p_Minutes;

		set @p_CurrencyID = p_CurrencyID;

		SET @p_StartDate	= p_StartDate;
		SET @p_EndDate		= p_EndDate;


		SET @p_Position = p_Position;
		SET @v_CallerRate = 1;

		SET @p_DIDCategoryID  		= p_DIDCategoryID;

		SET @p_CountryID = p_CountryID;
		SET @p_AccessType = p_AccessType;
		SET @p_City = p_City;
		SET @p_Tariff = p_Tariff;
		SET @p_Prefix = TRIM(LEADING '0' FROM p_Prefix);


		IF @p_Calls = 0 AND @p_Minutes = 0 THEN



			select count(UsageDetailID)  into @p_Calls

			from speakintelligentCDR.tblUsageDetails  d

				inner join speakintelligentCDR.tblUsageHeader h on d.UsageHeaderID = h.UsageHeaderID

				inner join speakintelligentRM.tblCountry c  on   d.area_prefix  like concat(c.Prefix,'%')

			where CompanyID = p_companyid AND StartDate >= @p_StartDate AND StartDate <= @p_EndDate and d.is_inbound = 1

						AND (@p_CountryID = '' OR  c.CountryID = @p_CountryID )

						AND (@p_AccessType = '' OR d.NoType = @p_AccessType)

						AND (@p_City = '' OR d.City  = @p_City)

						AND ( @p_Tariff = '' OR d.Tariff  = @p_Tariff )

						AND ( @p_Prefix = '' OR ( d.area_prefix   = concat(c.Prefix,  @p_Prefix )  ) );



			insert into tmp_timezone_minutes (TimezonesID, minutes)

				select TimezonesID  , (sum(billed_duration) / 60) as minutes

				from speakintelligentCDR.tblUsageDetails  d

					inner join speakintelligentCDR.tblUsageHeader h on d.UsageHeaderID = h.UsageHeaderID

					inner join speakintelligentRM.tblCountry c  on   d.area_prefix  like concat(c.Prefix,'%')

				where CompanyID = p_companyid AND StartDate >= @p_StartDate AND StartDate <= @p_EndDate and d.is_inbound = 1 and TimezonesID is not null

							AND (@p_CountryID = '' OR  c.CountryID = @p_CountryID )

							AND (@p_AccessType = '' OR d.NoType = @p_AccessType)

							AND (@p_City = '' OR d.City  = @p_City)

							AND ( @p_Tariff = '' OR d.Tariff  = @p_Tariff )

							AND ( @p_Prefix = '' OR ( d.area_prefix   = concat(c.Prefix,  @p_Prefix )  ) )

				group by TimezonesID;


			insert into tmp_origination_minutes ( OriginationCode, minutes )

				select CLIPrefix  , (sum(billed_duration) / 60) as minutes

				from speakintelligentCDR.tblUsageDetails  d

					inner join speakintelligentCDR.tblUsageHeader h on d.UsageHeaderID = h.UsageHeaderID

					inner join speakintelligentRM.tblCountry c  on   d.area_prefix  like concat(c.Prefix,'%')

				where CompanyID = p_companyid AND StartDate >= @p_StartDate AND StartDate <= @p_EndDate and d.is_inbound = 1 and CLIPrefix is not null

							AND (@p_CountryID = '' OR  c.CountryID = @p_CountryID )

							AND (@p_AccessType = '' OR d.NoType = @p_AccessType)

							AND (@p_City = '' OR d.City  = @p_City)

							AND ( @p_Tariff = '' OR d.Tariff  = @p_Tariff )

							AND ( @p_Prefix = '' OR ( d.area_prefix   = concat(c.Prefix,  @p_Prefix )  ) )

				group by CLIPrefix;


		ELSE



			SET @p_PeakTimeZonePercentage	 		 = p_TimezonePercentage;
			SET @p_MobileOrigination				 = p_Origination ;
			SET @p_MobileOriginationPercentage	 	 = p_OriginationPercentage ;



			SET @v_PeakTimeZoneMinutes				 =  ( (@p_Minutes/ 100) * @p_PeakTimeZonePercentage ) 	;
			SET @v_MinutesFromMobileOrigination  =  ( (@p_Minutes/ 100) * @p_MobileOriginationPercentage ) 	;


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



		insert into tmp_table_without_origination (

			TimezonesID,
			TimezoneTitle,
			AccessType,
			CountryID ,
			City ,
			Tariff ,
			EffectiveDate,

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
				drtr.AccessType,
				r.CountryID,
				drtr.City,
				drtr.Tariff ,
				drtr.EffectiveDate,

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

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid  )
								* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = MonthlyCostCurrency and  CompanyID = p_companyid ))
							)
						END

												WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
													drtr.MonthlyCost
												ELSE
													(

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

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
								* (drtr.CostPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CostPerCallCurrency and  CompanyID = p_companyid ))
							)
						END

												WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
													drtr.CostPerCall
												ELSE
													(

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

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
								* (drtr.CostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CostPerMinuteCurrency and  CompanyID = p_companyid ))
							)
						END

													WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
														drtr.CostPerMinute
													ELSE
														(

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

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
								* (drtr.SurchargePerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = SurchargePerCallCurrency and  CompanyID = p_companyid ))
							)
						END

														 WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
															 drtr.SurchargePerCall
														 ELSE
															 (

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

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
								* (drtr.SurchargePerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = SurchargePerMinuteCurrency and  CompanyID = p_companyid ))
							)
						END

															 WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
																 drtr.SurchargePerMinute
															 ELSE
																 (

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

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
								* (drtr.OutpaymentPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = OutpaymentPerCallCurrency and  CompanyID = p_companyid ))
							)
						END

															WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
																drtr.OutpaymentPerCall
															ELSE
																(

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

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
								* (drtr.OutpaymentPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = OutpaymentPerMinuteCurrency and  CompanyID = p_companyid ))
							)
						END

																WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
																	drtr.OutpaymentPerMinute
																ELSE
																	(

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

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
								* (drtr.Surcharges  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = SurchargesCurrency and  CompanyID = p_companyid ))
							)
						END

											 WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
												 drtr.Surcharges
											 ELSE
												 (

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

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
								* (drtr.Chargeback  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = ChargebackCurrency and  CompanyID = p_companyid ))
							)
						END

											 WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
												 drtr.Chargeback
											 ELSE
												 (

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

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
								* (drtr.CollectionCostAmount  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CollectionCostAmountCurrency and  CompanyID = p_companyid ))
							)
						END

																 WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
																	 drtr.CollectionCostAmount
																 ELSE
																	 (

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

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
								* (drtr.CollectionCostPercentage  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CollectionCostAmountCurrency and  CompanyID = p_companyid ))
							)
						END

																		 WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
																			 drtr.CollectionCostPercentage
																		 ELSE
																			 (

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

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
								* (drtr.RegistrationCostPerNumber  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = RegistrationCostPerNumberCurrency and  CompanyID = p_companyid ))
							)
						END

																			WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
																				drtr.RegistrationCostPerNumber
																			ELSE
																				(

																					(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
																					* (drtr.RegistrationCostPerNumber  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = p_companyid ))
																				)
																			END as RegistrationCostPerNumber,




				@Total1 := (

					(	IFNULL(@MonthlyCost,0) 				)				+
					(IFNULL(@CostPerMinute,0) * IFNULL((select minutes from tmp_timezone_minutes tm where tm.TimezonesID = t.TimezonesID ),0))	+
					(IFNULL(@CostPerCall,0) * @p_Calls)		+
					(IFNULL(@SurchargePerCall,0) * IFNULL(tom.minutes,0)) +
					(IFNULL(@OutpaymentPerMinute,0) *  IFNULL((select minutes from tmp_timezone_minutes_2 tm2 where tm2.TimezonesID = t.TimezonesID ),0))	+
					(IFNULL(@OutpaymentPerCall,0) * 	@p_Calls) +

					(IFNULL(@CollectionCostAmount,0) * IFNULL((select minutes from tmp_timezone_minutes_3 tm3 where tm3.TimezonesID = t.TimezonesID ),0) )


				)
					as Total1,
				@Total := (
					@Total1 + @Total1 * (select sum( IF(FlatStatus = 0 ,(Amount/100), Amount ) * IFNULL(@CollectionCostPercentage,0))  from tblTaxRate where CompanyID = p_companyid  AND `Status` = 1 AND  TaxType in  (1,2)   )
				) as Total





			from tblRateTableDIDRate  drtr
				inner join tblRateTable  rt on rt.RateTableId = drtr.RateTableId
				inner join tblVendorConnection vc on vc.RateTableID = rt.RateTableId and ((vc.DIDCategoryID IS NOT NULL AND rt.DIDCategoryID IS NOT NULL) AND vc.DIDCategoryID = rt.DIDCategoryID) and vc.CompanyID = rt.CompanyId  and vc.Active=1
				inner join tblAccount a on vc.AccountId = a.AccountID and rt.CompanyId = a.CompanyId
				inner join tblRate r on drtr.RateID = r.RateID and r.CompanyID = vc.CompanyID
				left join tblRate r2 on drtr.OriginationRateID = r2.RateID and r.CompanyID = vc.CompanyID
				inner join tblCountry c on c.CountryID = r.CountryID

																	 AND ( @p_CountryID = '' OR  c.CountryID = @p_CountryID )
																	 AND ( @p_City = '' OR drtr.City = @p_City )
																	 AND ( @p_Tariff = '' OR drtr.Tariff  = @p_Tariff )
																	 AND ( @p_Prefix = '' OR (r.Code  = concat(c.Prefix ,@p_Prefix) ) )
																	 AND ( @p_AccessType = '' OR drtr.AccessType = @p_AccessType )

				inner join tblTimezones t on t.TimezonesID =  drtr.TimezonesID
				left join tmp_origination_minutes tom  on r2.Code = tom.OriginationCode

			where

				rt.CompanyId =  p_companyid

				and vc.DIDCategoryID = @p_DIDCategoryID

				and drtr.ApprovedStatus = 1

				and rt.Type = 2

				and rt.AppliedTo = 2

				AND EffectiveDate <= DATE(p_SelectedEffectiveDate)

				AND (EndDate is NULL OR EndDate > now() )
		;


		insert into tmp_table_with_origination
		(

			TimezonesID,
			TimezoneTitle,
			AccessType,
			CountryID,
			City ,
			Tariff ,

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
				drtr.AccessType,
				r.CountryID,
				drtr.City,
				drtr.Tariff,

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

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
								* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = MonthlyCostCurrency and  CompanyID = p_companyid ))
							)
						END

												WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
													drtr.MonthlyCost
												ELSE
													(

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

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
								* (drtr.CostPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CostPerCallCurrency and  CompanyID = p_companyid ))
							)
						END

												WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
													drtr.CostPerCall
												ELSE
													(

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

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
								* (drtr.CostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CostPerMinuteCurrency and  CompanyID = p_companyid ))
							)
						END

													WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
														drtr.CostPerMinute
													ELSE
														(

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

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
								* (drtr.SurchargePerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = SurchargePerCallCurrency and  CompanyID = p_companyid ))
							)
						END

														 WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
															 drtr.SurchargePerCall
														 ELSE
															 (

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

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
								* (drtr.SurchargePerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = SurchargePerMinuteCurrency and  CompanyID = p_companyid ))
							)
						END

															 WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
																 drtr.SurchargePerMinute
															 ELSE
																 (

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

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
								* (drtr.OutpaymentPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = OutpaymentPerCallCurrency and  CompanyID = p_companyid ))
							)
						END

															WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
																drtr.OutpaymentPerCall
															ELSE
																(

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

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
								* (drtr.OutpaymentPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = OutpaymentPerMinuteCurrency and  CompanyID = p_companyid ))
							)
						END

																WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
																	drtr.OutpaymentPerMinute
																ELSE
																	(

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

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
								* (drtr.Surcharges  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = SurchargesCurrency and  CompanyID = p_companyid ))
							)
						END

											 WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
												 drtr.Surcharges
											 ELSE
												 (

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

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
								* (drtr.Chargeback  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = ChargebackCurrency and  CompanyID = p_companyid ))
							)
						END

											 WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
												 drtr.Chargeback
											 ELSE
												 (

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

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
								* (drtr.CollectionCostAmount  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CollectionCostAmountCurrency and  CompanyID = p_companyid ))
							)
						END

																 WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
																	 drtr.CollectionCostAmount
																 ELSE
																	 (

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

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
								* (drtr.CollectionCostPercentage  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CollectionCostAmountCurrency and  CompanyID = p_companyid ))
							)
						END

																		 WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
																			 drtr.CollectionCostPercentage
																		 ELSE
																			 (

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

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
								* (drtr.RegistrationCostPerNumber  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = RegistrationCostPerNumberCurrency and  CompanyID = p_companyid ))
							)
						END

																			WHEN  ( @p_CurrencyID = rt.CurrencyID ) THEN
																				drtr.RegistrationCostPerNumber
																			ELSE
																				(

																					(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @p_CurrencyID  and  CompanyID = p_companyid )
																					* (drtr.RegistrationCostPerNumber  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = p_companyid ))
																				)
																			END as RegistrationCostPerNumber,






				@Total1 := (
					(	IFNULL(@MonthlyCost,0) 				)				+
					(IFNULL(@CostPerMinute,0) * IFNULL(tom.minutes,0))	+
					(IFNULL(@CostPerCall,0) * @p_Calls)		+
					(IFNULL(@SurchargePerCall,0) * IFNULL(tom.minutes,0)) +
					(IFNULL(@OutpaymentPerMinute,0) * 	IFNULL(tom.minutes,0))	+
					(IFNULL(@OutpaymentPerCall,0) * 	@p_Calls) +

					(IFNULL(@CollectionCostAmount,0) * IFNULL(tom.minutes,0))


				) as Total1,

				@Total := (
					@Total1 + @Total1 * (select sum( IF(FlatStatus = 0 ,(Amount/100), Amount ) * IFNULL(@CollectionCostPercentage,0))  from tblTaxRate where CompanyID = p_companyid AND `Status` = 1 AND  TaxType in  (1,2)   )
				) as Total





			from tblRateTableDIDRate  drtr
				inner join tblRateTable  rt on rt.RateTableId = drtr.RateTableId
				inner join tblVendorConnection vc on vc.RateTableID = rt.RateTableId and vc.DIDCategoryID = rt.DIDCategoryID and vc.CompanyID = rt.CompanyId and vc.Active=1
				inner join tblAccount a on vc.AccountId = a.AccountID and rt.CompanyId = a.CompanyId
				inner join tblRate r on drtr.RateID = r.RateID and r.CompanyID = vc.CompanyID
				inner join tblRate r2 on drtr.OriginationRateID = r2.RateID and r.CompanyID = vc.CompanyID
				inner join tblCountry c on c.CountryID = r.CountryID

																	 AND ( @p_CountryID = '' OR  c.CountryID = @p_CountryID )
																	 AND ( @p_City = '' OR drtr.City = @p_City )
																	 AND ( @p_Tariff = '' OR drtr.Tariff  = @p_Tariff )
																	 AND ( @p_Prefix = '' OR (r.Code  = concat(c.Prefix ,@p_Prefix) ) )
																	 AND ( @p_AccessType = '' OR drtr.AccessType = @p_AccessType )



				inner join tblTimezones t on t.TimezonesID =  drtr.TimezonesID
				inner join tmp_origination_minutes tom  on r2.Code = tom.OriginationCode
			where

				rt.CompanyId = p_companyid

				and vc.DIDCategoryID = @p_DIDCategoryID

				and drtr.ApprovedStatus = 1

				and rt.Type = 2

				and rt.AppliedTo = 2


				AND EffectiveDate <= DATE(p_SelectedEffectiveDate)

				AND (EndDate is NULL OR EndDate > now() )

		;




		delete t1 from tmp_table_without_origination t1 inner join tmp_table_with_origination t2 on t1.VendorID = t2.VendorID and t1.TimezonesID = t2.TimezonesID and t1.Code = t2.Code;



		insert into tmp_table1_ (

			TimezonesID,
			TimezoneTitle,
			AccessType,
			CountryID,
			City,
			Tariff,
			EffectiveDate,
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
				TimezonesID,
				TimezoneTitle,
				AccessType,
				CountryID,
				City,
				Tariff,
				EffectiveDate,
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
			from (
						 select
							 TimezonesID,
							 TimezoneTitle,
							 AccessType,
							 CountryID,
							 City,
							 Tariff,
							 EffectiveDate,
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
						 from
							 tmp_table_without_origination

						 union all

						 select
							 TimezonesID,
							 TimezoneTitle,
							 AccessType,
							 CountryID,
							 City,
							 Tariff,
							 EffectiveDate,
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
						 from
							 tmp_table_with_origination

					 ) tmp
			where Total is not null;


		insert into tmp_table_output_1
		(AccessType ,CountryID ,City ,Tariff,Code ,VendorID ,VendorName,EffectiveDate,Total)
			select AccessType ,CountryID ,City ,Tariff,Code ,VendorID ,VendorName,max(EffectiveDate),sum(Total) as Total
			from tmp_table1_
			group by AccessType ,CountryID ,City ,Tariff,Code ,VendorID ,VendorName;


		insert into tmp_table_output_2   ( AccessType ,CountryID ,City ,Tariff,Code ,VendorID ,VendorName,EffectiveDate,Total,vPosition )

			SELECT AccessType ,CountryID ,City ,Tariff,Code ,VendorID ,VendorName,EffectiveDate,Total,vPosition
			FROM (
						 select AccessType ,CountryID ,City ,Tariff,Code ,VendorID ,VendorName,EffectiveDate,Total,
							 @vPosition := (
								 CASE WHEN (@prev_Code = Code AND  @prev_AccessType    = AccessType AND  @prev_CountryID = CountryID
														AND  @prev_City    = City AND  @prev_Tariff = Tariff /*AND  @prev_VendorID = VendorID */ AND @prev_Total <=  Total
								 )
									 THEN
										 @vPosition + 1
								 ELSE
									 1
								 END) as  vPosition,
							 @prev_AccessType := AccessType ,
							 @prev_CountryID  := CountryID  ,
							 @prev_City  := City  ,
							 @prev_Tariff := Tariff ,
							 @prev_Code  := Code  ,
							 @prev_VendorID  := VendorID,
							 @prev_Total := Total

						 from tmp_table_output_1
							 ,(SELECT  @prev_AccessType := '' ,@prev_CountryID  := '' ,@prev_City  := '' ,@prev_Tariff := '' ,@prev_Code  := ''  , @prev_VendorID  := '', @prev_Total := 0 ) t

						 ORDER BY Code,AccessType,CountryID,City,Tariff,Total,VendorID
					 ) tmp;


		insert into tmp_final_table_output
		(AccessType ,Country ,City ,Tariff,Code ,VendorID ,VendorName,EffectiveDate,Total,vPosition)
			select AccessType ,Country ,City ,Tariff,Code ,VendorID ,VendorName,EffectiveDate, Total,vPosition
			from tmp_table_output_2 t
				LEFT JOIN tblCountry  c on t.CountryID = c.CountryID
			where vPosition  < @p_Position ;


		SET @stm_columns = "";

		IF p_isExport = 0 AND p_Position > 10 THEN
			SET p_Position = 10;
		END IF;



		SET @v_pointer_ = 1;
		WHILE @v_pointer_ <= @p_Position
		DO

			SET @stm_columns = CONCAT(@stm_columns, "GROUP_CONCAT(if(ANY_VALUE(vPosition) = ",@v_pointer_,", CONCAT(ANY_VALUE(Total), '<br>', ANY_VALUE(VendorName), '<br>', DATE_FORMAT (ANY_VALUE(EffectiveDate), '%d/%m/%Y'),'' ), NULL)) AS `POSITION ",@v_pointer_,"`,");

			SET @v_pointer_ = @v_pointer_ + 1;

		END WHILE;

		SET @stm_columns = TRIM(TRAILING ',' FROM @stm_columns);

		IF (p_isExport = 0)
		THEN

			SET @stm_query = CONCAT("SELECT AccessType ,Country ,Code,City ,Tariff, ", @stm_columns," FROM tmp_final_table_output GROUP BY Code, AccessType ,Country ,City ,Tariff ORDER BY Code, AccessType ,Country ,City ,Tariff  LIMIT ",p_RowspPage," OFFSET ",@v_OffSet_," ;");

		ELSE

			SET @stm_query = CONCAT("SELECT AccessType ,Country ,Code,City ,Tariff,  ", @stm_columns," FROM tmp_final_table_output GROUP BY Code, AccessType ,Country ,City ,Tariff ORDER BY Code, AccessType ,Country ,City ,Tariff  ;");


		END IF;

		select count(Code) as totalcount from tmp_final_table_output;


		PREPARE stm_query FROM @stm_query;
		EXECUTE stm_query;
		DEALLOCATE PREPARE stm_query;


		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

	END//
DELIMITER ;

-- Dumping structure for procedure speakintelligentRM.prc_GetLCR
DROP PROCEDURE IF EXISTS `prc_GetLCR`;
DELIMITER //
CREATE  PROCEDURE `prc_GetLCR`(
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

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage_1;
		CREATE TEMPORARY TABLE tmp_VendorRate_stage_1 (
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
			;
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
			;

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
			;

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
												INNER JOIN tblRate as tr on  tr.CodeDeckId = p_codedeckID AND f.Code=tr.Code
												INNER JOIN tblRate as tr1 on tr1.CodeDeckId = p_codedeckID AND LEFT(f.Code, x.RowNo) = tr1.Code

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

												INNER JOIN tblRate as tr on  tr.CodeDeckId = p_codedeckID AND f.Code=tr.Code
												INNER JOIN tblRate as tr1 on tr1.CodeDeckId = p_codedeckID AND LEFT(f.Code, x.RowNo) = tr1.Code

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
			;

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
			;

		END IF;





		-- DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage_1;
		-- CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRate_stage_1 as (select * from tmp_VendorRate_stage_);


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

								 inner join  tmp_all_code_ 		SplitCode   on v.Code = SplitCode.Code
								 left join  tmp_all_code_dup 	SplitCode2  on v.OriginationCode != '' AND v.OriginationCode = SplitCode2.Code

								 inner JOIN (	select Code,Description from tblRate where CodeDeckId=p_codedeckID
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


							 where  SplitCode.Code is not null AND  ( p_isExport = 1 OR p_isExport = 2  OR ( p_isExport = 0 AND rankname <= p_Position ) )

						 ) tmp

			-- order by AccountId ,OriginationCode,RowCode desc
			;

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

					inner join  tmp_all_code_ SplitCode   on v.Code = SplitCode.Code
					inner join tblRate tr  on  SplitCode.RowCode = tr.Code AND  tr.CodeDeckId = p_codedeckID

				where  SplitCode.Code is not null and ((p_isExport = 1  OR p_isExport = 2) OR (p_isExport = 0 AND rankname <= p_Position))
			-- order by v.AccountId ,v.OriginationCode,SplitCode.RowCode desc
			;

		END IF;

		insert ignore into tmp_VendorRate_stage_
			SELECT
				distinct
				v.RateTableRateID,

				v.RowCode,
				v.AccountId ,
				v.Blocked,
				v.AccountName ,
				v.OriginationCode ,
				v.Code ,
				v.Rate ,
				v.ConnectionFee,
				v.EffectiveDate ,
				v.OriginationDescription,
				v.Description,
				v.Preference,
				@rank := ( CASE WHEN(@prev_AccountID = v.AccountId  AND @prev_OriginationCode = v.OriginationCode and @prev_RowCode = v.RowCode )
					THEN  @rank + 1
									 ELSE 1
									 END
				) AS MaxMatchRank,
				@prev_OriginationCode := v.OriginationCode,
				@prev_RowCode := v.RowCode	 ,
				@prev_AccountID := v.AccountId

			FROM tmp_VendorRate_stage_1 v
				inner join  tmp_all_code_ SplitCode   on v.Code = SplitCode.Code
				inner join tblRate tr  on  SplitCode.RowCode = tr.Code AND  tr.CodeDeckId = p_codedeckID

				, (SELECT  @prev_OriginationCode := NUll , @prev_RowCode := '',  @rank := 0 , @prev_Code := '' , @prev_AccountID := Null) f
			order by v.AccountID,v.OriginationCode,v.RowCode desc;




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
			;

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
			;
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
					OriginationDescription as OriginationDescription,
					Description as Description,
					ANY_VALUE(Preference) as Preference,
					ANY_VALUE(RowCode) as RowCode,
					ANY_VALUE(FinalRankNumber) as FinalRankNumber
				from tmp_final_VendorRate_
				GROUP BY  OriginationDescription,Description ORDER BY OriginationDescription,Description ASC;


			ELSE


				SELECT
					distinct
					ANY_VALUE(RateTableRateID) as RateTableRateID,
					ANY_VALUE(AccountId) as AccountId,
					ANY_VALUE(Blocked) as Blocked,
					ANY_VALUE(AccountName) as AccountName,
					OriginationCode as OriginationCode,
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
				GROUP BY  OriginationCode,RowCode ORDER BY OriginationCode,RowCode ASC;


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
					SET @stm_columns = CONCAT(@stm_columns, "GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = ",v_pointer_,", CONCAT(ANY_VALUE(OriginationCode), '<br>', ANY_VALUE(OriginationDescription), '<br>',ANY_VALUE(Code), '<br>', ANY_VALUE(Description), '<br>', ANY_VALUE(Rate), '<br>', ANY_VALUE(AccountName), '<br>', DATE_FORMAT (ANY_VALUE(EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(RateTableRateID), '-', ANY_VALUE(AccountId), '-', ANY_VALUE(Code), '-', ANY_VALUE(Blocked) , '-', ANY_VALUE(Preference)  ), NULL)) AS `POSITION ",v_pointer_,"`,");
				ELSE
					SET @stm_columns = CONCAT(@stm_columns, "GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = ",v_pointer_,", CONCAT(ANY_VALUE(Code), '<br>', ANY_VALUE(Description), '<br>', ANY_VALUE(Rate), '<br>', ANY_VALUE(AccountName), '<br>', DATE_FORMAT (ANY_VALUE(EffectiveDate), '%d/%m/%Y')), NULL))  AS `POSITION ",v_pointer_,"`,");
				END IF;

				SET v_pointer_ = v_pointer_ + 1;

			END WHILE;

			SET @stm_columns = TRIM(TRAILING ',' FROM @stm_columns);



			IF (p_isExport = 0)
			THEN




				IF p_groupby = 'description' THEN

					SET @stm_query = CONCAT("SELECT CONCAT(OriginationDescription , ' <br> => ' , Description) as Destination,",@stm_columns," FROM tmp_final_VendorRate_  GROUP BY  OriginationDescription,Description ORDER BY OriginationDescription, Description ASC LIMIT ",p_RowspPage," OFFSET ",v_OffSet_," ;");

					select count(Description) as totalcount from ( SELECT Description as totalcount from tmp_final_VendorRate_  GROUP BY OriginationDescription,Description ) tmp ;

				ELSE

					SET @stm_query = CONCAT("SELECT CONCAT(OriginationCode , ' : ' , ANY_VALUE(OriginationDescription), ' <br> => '  , RowCode , ' : ' , ANY_VALUE(Description)) as Destination,", @stm_columns," FROM tmp_final_VendorRate_  GROUP BY  OriginationCode,RowCode ORDER BY OriginationCode,RowCode ASC LIMIT ",p_RowspPage," OFFSET ",v_OffSet_," ;");

					select count(RowCode) as totalcount from ( SELECT RowCode from tmp_final_VendorRate_ GROUP BY OriginationCode, RowCode) tmp;

				END IF;


			END IF;

			IF p_isExport = 1
			THEN

				IF p_groupby = 'description' THEN

					SET @stm_query = CONCAT("SELECT CONCAT(OriginationDescription , '  => ' , Description) as Destination,",@stm_columns," FROM tmp_final_VendorRate_   GROUP BY  OriginationDescription,Description ORDER BY Description ASC ;");


				ELSE

					SET @stm_query = CONCAT("SELECT CONCAT(OriginationCode , ' : ' , ANY_VALUE(OriginationDescription), '  => '  , RowCode , ' : ' , ANY_VALUE(Description)) as Destination,", @stm_columns," FROM tmp_final_VendorRate_   GROUP BY  OriginationCode,RowCode ORDER BY RowCode ASC;");


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
CREATE  PROCEDURE `prc_GetLCRwithPrefix`(
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
			;');




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
			;


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
			;

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


			;

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
			;

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

			;

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
			;

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
					SET @stm_columns = CONCAT(@stm_columns, "GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = ",v_pointer_,", CONCAT(  ANY_VALUE(Rate), '<br>', ANY_VALUE(AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(RateTableRateID), '-', ANY_VALUE(AccountId), '-', ANY_VALUE(RowCode), '-', ANY_VALUE(Blocked) , '-', ANY_VALUE(Preference)  ), NULL))AS `POSITION ",v_pointer_,"`,");
				ELSE
					SET @stm_columns = CONCAT(@stm_columns, "GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = ",v_pointer_,", CONCAT(  ANY_VALUE(Rate), '<br>', ANY_VALUE(AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(EffectiveDate), '%d/%m/%Y') ), NULL))AS `POSITION ",v_pointer_,"`,");
				END IF;

				SET v_pointer_ = v_pointer_ + 1;

			END WHILE;

			SET @stm_columns = TRIM(TRAILING ',' FROM @stm_columns);

			IF (p_isExport = 0)
			THEN

				IF p_groupby = 'description' THEN

					SET @stm_query = CONCAT("SELECT	CONCAT(OriginationDescription , ' <br> => ' , Description) as Destination,",@stm_columns," FROM tmp_final_VendorRate_  GROUP BY OriginationDescription,Description ORDER BY OriginationDescription , Description ASC LIMIT ",p_RowspPage," OFFSET ",v_OffSet_," ;");


					select count(Description) as totalcount from ( SELECT Description as totalcount from tmp_final_VendorRate_  GROUP BY OriginationDescription,Description ) tmp ;

				ELSE

					SET @stm_query = CONCAT("SELECT	CONCAT(OriginationCode , ' : ' , ANY_VALUE(OriginationDescription), ' <br> => '  , RowCode , ' : ' , ANY_VALUE(Description)) as Destination,",@stm_columns," FROM tmp_final_VendorRate_  GROUP BY OriginationCode, RowCode ORDER BY RowCode ASC LIMIT ",p_RowspPage," OFFSET ",v_OffSet_," ;");


					select count(RowCode) as totalcount from ( SELECT RowCode from tmp_final_VendorRate_ GROUP BY OriginationCode, RowCode) tmp;


				END IF;


			ELSE

				IF p_groupby = 'description' THEN

					SET @stm_query = CONCAT("SELECT CONCAT(OriginationDescription , ' => ' , Description)as Destination,",@stm_columns," FROM tmp_final_VendorRate_  GROUP BY  OriginationDescription,Description ORDER BY Description ASC;");

				ELSE

					SET @stm_query = CONCAT("SELECT CONCAT(OriginationCode , ' : ' , ANY_VALUE(OriginationDescription), ' => '  , RowCode , ' : ' , ANY_VALUE(Description)) as Destination,",@stm_columns," FROM tmp_final_VendorRate_  GROUP BY  OriginationCode, RowCode  ORDER BY RowCode ASC;");


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
