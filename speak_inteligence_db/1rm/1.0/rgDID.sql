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

-- Dumping structure for procedure speakintelligentRM.prc_WSGenerateRateTableDID
drop procedure if exists prc_WSGenerateRateTableDID;
DELIMITER //
CREATE  PROCEDURE `prc_WSGenerateRateTableDID`(
	IN `p_jobId` INT,
	IN `p_RateGeneratorId` INT,
	IN `p_RateTableId` INT,
	IN `p_rateTableName` VARCHAR(200),
	IN `p_EffectiveDate` VARCHAR(10),
	IN `p_delete_exiting_rate` INT,
	IN `p_EffectiveRate` VARCHAR(50),
	IN `p_ModifiedBy` VARCHAR(50)
)
GenerateRateTable:BEGIN




		DECLARE EXIT HANDLER FOR SQLEXCEPTION
		BEGIN
			show warnings;
			ROLLBACK;
			INSERT INTO tmp_JobLog_ (Message) VALUES ('RateTable generation failed');


		END;

		DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
		CREATE TEMPORARY TABLE tmp_JobLog_ (
			Message longtext
		);

		SET @@session.collation_connection='utf8_unicode_ci';
		SET @@session.character_set_client='utf8';
		SET SESSION group_concat_max_len = 1000000;

		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;



		DROP TEMPORARY TABLE IF EXISTS tmp_Raterules_;
		CREATE TEMPORARY TABLE tmp_Raterules_  (
			rateruleid INT,
			Component VARCHAR(50) COLLATE utf8_unicode_ci,
			Origination VARCHAR(50) COLLATE utf8_unicode_ci,
			TimezonesID int,
			`Order` INT,
			RowNo INT
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_RateGeneratorCalculatedRate_;
		CREATE TEMPORARY TABLE tmp_RateGeneratorCalculatedRate_  (
			CalculatedRateID INT,
			Component VARCHAR(50),
			Origination VARCHAR(50) ,
			TimezonesID int,
			RateLessThen	double(18,4),
			ChangeRateTo double(18,4),
			RowNo INT
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_table_without_origination;
		CREATE TEMPORARY TABLE tmp_table_without_origination (
				RateTableID int,
				TimezonesID  int,
				TimezoneTitle  varchar(100),
				CodeDeckId int,
				CityTariff varchar(100),
				Code varchar(100),
				OriginationCode  varchar(100),
				VendorID int,
				VendorName varchar(200),
				EndDate datetime,
				OneOffCost double(18,4),
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

				OneOffCostCurrency int,
				MonthlyCostCurrency int,
				CostPerCallCurrency int,
				CostPerMinuteCurrency int,
				SurchargePerCallCurrency int,
				SurchargePerMinuteCurrency int,
				OutpaymentPerCallCurrency int,
				OutpaymentPerMinuteCurrency int,
				SurchargesCurrency int,
				ChargebackCurrency int,
				CollectionCostAmountCurrency int,
				RegistrationCostPerNumberCurrency int,


				Total double(18,4)
			);

		DROP TEMPORARY TABLE IF EXISTS tmp_table_with_origination;
		CREATE TEMPORARY TABLE tmp_table_with_origination (
				RateTableID int,
				TimezonesID  int,
				TimezoneTitle  varchar(100),
				CodeDeckId int,
				CityTariff varchar(100),
				Code varchar(100),
				OriginationCode  varchar(100),
				VendorID int,
				VendorName varchar(200),
				EndDate datetime,

				OneOffCost double(18,4),
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

				OneOffCostCurrency int,
				MonthlyCostCurrency int,
				CostPerCallCurrency int,
				CostPerMinuteCurrency int,
				SurchargePerCallCurrency int,
				SurchargePerMinuteCurrency int,
				OutpaymentPerCallCurrency int,
				OutpaymentPerMinuteCurrency int,
				SurchargesCurrency int,
				ChargebackCurrency int,
				CollectionCostAmountCurrency int,
				RegistrationCostPerNumberCurrency int,




				Total double(18,4)
			);

		DROP TEMPORARY TABLE IF EXISTS tmp_tblRateTableDIDRate;
		CREATE TEMPORARY TABLE tmp_tblRateTableDIDRate (
				RateTableID int,
				TimezonesID  int,
				TimezoneTitle  varchar(100),
				CodeDeckId int,
				CityTariff varchar(100),
				Code varchar(100),
				OriginationCode  varchar(100),
				VendorID int,
				VendorName varchar(200),
				EndDate datetime,
				OneOffCost double(18,4),
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

				OneOffCostCurrency int,
				MonthlyCostCurrency int,
				CostPerCallCurrency int,
				CostPerMinuteCurrency int,
				SurchargePerCallCurrency int,
				SurchargePerMinuteCurrency int,
				OutpaymentPerCallCurrency int,
				OutpaymentPerMinuteCurrency int,
				SurchargesCurrency int,
				ChargebackCurrency int,
				CollectionCostAmountCurrency int,
				RegistrationCostPerNumberCurrency int,


				Total double(18,4)
			);

		DROP TEMPORARY TABLE IF EXISTS tmp_SelectedVendortblRateTableDIDRate;
		CREATE TEMPORARY TABLE tmp_SelectedVendortblRateTableDIDRate (
				RateTableID int,
				TimezonesID  int,
				TimezoneTitle  varchar(100),
				CodeDeckId int,
				CityTariff varchar(100),
				Code varchar(100),
				OriginationCode  varchar(100),
				VendorID int,
				VendorName varchar(200),
				EndDate datetime,
				OneOffCost double(18,4),
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

				OneOffCostCurrency int,
				MonthlyCostCurrency int,
				CostPerCallCurrency int,
				CostPerMinuteCurrency int,
				SurchargePerCallCurrency int,
				SurchargePerMinuteCurrency int,
				OutpaymentPerCallCurrency int,
				OutpaymentPerMinuteCurrency int,
				SurchargesCurrency int,
				ChargebackCurrency int,
				CollectionCostAmountCurrency int,
				RegistrationCostPerNumberCurrency int,


				new_OneOffCost double(18,4),
				new_MonthlyCost double(18,4),
				new_CostPerCall double(18,4),
				new_CostPerMinute double(18,4),
				new_SurchargePerCall double(18,4),
				new_SurchargePerMinute double(18,4),
				new_OutpaymentPerCall double(18,4),
				new_OutpaymentPerMinute double(18,4),
				new_Surcharges double(18,4),
				new_Chargeback double(18,4),
				new_CollectionCostAmount double(18,4),
				new_CollectionCostPercentage double(18,4),
				new_RegistrationCostPerNumber double(18,4)

			);

			DROP TEMPORARY TABLE IF EXISTS tmp_vendor_position;
			CREATE TEMPORARY TABLE tmp_vendor_position (
				VendorID int,
				vPosition int,
				Total double(18,4)

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

	set @p_RateGeneratorId = p_RateGeneratorId;


		IF p_rateTableName IS NOT NULL
		THEN


			SET @v_RTRowCount_ = (SELECT COUNT(*)
													 FROM tblRateTable
													 WHERE RateTableName = p_rateTableName
																 AND CompanyId = (SELECT
																										CompanyId
																									FROM tblRateGenerator
																									WHERE RateGeneratorID = p_RateGeneratorId));

			IF @v_RTRowCount_ > 0
			THEN
				INSERT INTO tmp_JobLog_ (Message) VALUES ('RateTable Name is already exist, Please try using another RateTable Name');
				SELECT * FROM tmp_JobLog_;
				LEAVE GenerateRateTable;
			END IF;
		END IF;


		-- SELECT CurrencyID INTO @v_CurrencyID_ FROM  tblRateGenerator WHERE RateGeneratorId = @p_RateGeneratorId;
		SET @p_EffectiveDate = p_EffectiveDate;



		/*SELECT IFNULL(REPLACE(JSON_EXTRACT(Options, '$.IncreaseEffectiveDate'),'"',''), @p_EffectiveDate) , IFNULL(REPLACE(JSON_EXTRACT(Options, '$.DecreaseEffectiveDate'),'"',''), @p_EffectiveDate)
		INTO @v_IncreaseEffectiveDate_ , @v_DecreaseEffectiveDate_  FROM tblJob WHERE Jobid = p_jobId;


		IF v_IncreaseEffectiveDate_ is null OR v_IncreaseEffectiveDate_ = '' THEN

			SET @v_IncreaseEffectiveDate_ = @p_EffectiveDate;

		END IF;

		IF @v_DecreaseEffectiveDate_ is null OR @v_DecreaseEffectiveDate_ = '' THEN

			SET @v_DecreaseEffectiveDate_ = @p_EffectiveDate;

		END IF;
		*/


		SELECT
			rateposition,
			companyid ,
			tblRateGenerator.RateGeneratorName,
			RateGeneratorId,
			CurrencyID,
			ProductID,
			DIDCategoryID,
			Calls,
			Minutes,
			DateFrom,
			DateTo,
			TimezonesID,
			TimezonesPercentage,
			Origination,
			OriginationPercentage,
			IF( percentageRate = '' OR percentageRate is null	,0, percentageRate )

			INTO @v_RatePosition_, @v_CompanyId_,   @v_RateGeneratorName_,@p_RateGeneratorId, @v_CurrencyID_,@v_ProductID_,@v_DIDCategoryID_,
			@v_Calls,
			@v_Minutes,
			@v_StartDate_ ,@v_EndDate_ ,@v_TimezonesID, @v_TimezonesPercentage, @v_Origination, @v_OriginationPercentage, @v_percentageRate_
		FROM tblRateGenerator
		WHERE RateGeneratorId = @p_RateGeneratorId;


		SELECT CurrencyId INTO @v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = @v_CompanyId_;

		SELECT IF(IFNULL(Value,1) = 1,0,1) INTO @v_RateApprovalProcess_ FROM tblCompanySetting WHERE CompanyID = @v_CompanyId_ AND `Key`='RateApprovalProcess';


		INSERT INTO tmp_Raterules_(
			rateruleid ,
			Component,
			Origination ,
			TimezonesID ,
			`Order` ,
			RowNo
		)
			SELECT
				rateruleid,
				Component,
				OriginationDescription as Origination ,
				TimeOfDay as TimezonesID,
				`Order`,
				@row_num := @row_num+1 AS RowID
			FROM tblRateRule,(SELECT @row_num := 0) x
			WHERE rategeneratorid = @p_RateGeneratorId
			ORDER BY `Order` ASC;



		INSERT INTO tmp_RateGeneratorCalculatedRate_
			(
			CalculatedRateID ,
			Component ,
			Origination ,
			TimezonesID ,
			RateLessThen,
			ChangeRateTo ,
			RowNo )
			SELECT

			CalculatedRateID ,
			Component ,
			Origination ,
			TimezonesID ,
			RateLessThen	,
			ChangeRateTo ,
			@row_num := @row_num+1 AS RowID
			FROM tblRateGeneratorCalculatedRate,(SELECT @row_num := 0) x
			WHERE RateGeneratorId = @p_RateGeneratorId
			ORDER BY CalculatedRateID ASC;


				set @v_ApprovedStatus = 1;

				set @v_DIDType = 2; -- did

			  	set @v_AppliedToCustomer = 1; -- customer
				set @v_AppliedToVendor = 2; -- vendor
				set @v_AppliedToReseller = 3; -- reseller




	-- arguments usage input
			SET @p_Calls	 							 = @v_Calls;
			SET @p_Minutes	 							 = @v_Minutes;
			SET @v_PeakTimeZoneID	 				 = @v_TimezonesID;
			SET @p_PeakTimeZonePercentage	 		 = @v_TimezonesPercentage;		-- peak percentage
			SET @p_MobileOrigination				 = @v_Origination ; -- 'Mobile';	--
			SET @p_MobileOriginationPercentage	 = @v_OriginationPercentage ;	-- mobile percentage


			-- Helper calculations...

			SET @v_PeakTimeZoneMinutes				 =  ( (@p_Minutes/ 100) * @p_PeakTimeZonePercentage ) 	; -- Peak minutes:
			SET @v_OffpeakTimeZoneMinutes		 	 =  (@p_Minutes -  @v_PeakTimeZoneMinutes)	; -- off Peak minutes;
			SET @v_MinutesFromMobileOrigination  =  ( (@p_Minutes/ 100) * @p_MobileOriginationPercentage ) 	; -- Minutes from mobile:

			SET @v_CallerRate = 1; -- temp set as 1
--			SET @p_ServiceTemplateID  = @v_ProductID_;
			-- SET @p_DIDCategoryID  		= @v_DIDCategoryID_;

		-- set @p_CurrencyID = @v_CompanyId_;

		-- SET @p_StartDate	= p_StartDate;
		-- SET @p_EndDate		= p_EndDate;


		SET @v_days =    TIMESTAMPDIFF(DAY, (SELECT @v_StartDate_), (SELECT @v_EndDate_)) ;
		SET @v_period1 =      IF(MONTH((SELECT @v_StartDate_)) = MONTH((SELECT @v_EndDate_)), 0, (TIMESTAMPDIFF(DAY, (SELECT @v_StartDate_), LAST_DAY((SELECT @v_StartDate_)) + INTERVAL 1 DAY)) / DAY(LAST_DAY((SELECT @v_StartDate_))));
		SET @v_period2 =      TIMESTAMPDIFF(MONTH, LAST_DAY((SELECT @v_StartDate_)) + INTERVAL 1 DAY, LAST_DAY((SELECT @v_EndDate_))) ;
		SET @v_period3 =      IF(MONTH((SELECT @v_StartDate_)) = MONTH((SELECT @v_EndDate_)), (SELECT @v_days), DAY((SELECT @v_EndDate_))) / DAY(LAST_DAY((SELECT @v_EndDate_)));
		SET @v_months1 =     (SELECT @v_period1) + (SELECT @v_period2) + (SELECT @v_period3);


		SET @v_months = @v_months1;

		IF (day(LAST_DAY(@v_StartDate_)) = @v_days ) THEN

			 SET @v_months = 1;

	 	END IF;



		 -- ///////////////////////////////////////////////////// Timezone minutes logic
		insert into tmp_timezones (TimezonesID) select TimezonesID from 	tblTimezones;

		insert into tmp_timezone_minutes (TimezonesID, minutes) select @v_TimezonesID, @v_PeakTimeZoneMinutes as minutes;

		SET @v_RemainingTimezones = (select count(*) from tmp_timezones where TimezonesID != @v_TimezonesID);
		SET @v_RemainingMinutes = (@p_Minutes - @v_PeakTimeZoneMinutes) / @v_RemainingTimezones ;

		SET @v_pointer_ = 1;
		SET @v_rowCount_ = ( SELECT COUNT(*) FROM tmp_timezones );

		WHILE @v_pointer_ <= @v_rowCount_
		DO

				SET @v_TimezonesID = (SELECT TimezonesID FROM tmp_timezones WHERE ID = @v_pointer_ AND TimezonesID != @v_TimezonesID );

				if @v_TimezonesID > 0 THEN

					insert into tmp_timezone_minutes (TimezonesID, minutes)  select @v_TimezonesID, @v_RemainingMinutes as minutes;

				END IF ;

			SET @v_pointer_ = @v_pointer_ + 1;

		END WHILE;

		insert into tmp_timezone_minutes_2 (TimezonesID, minutes) select TimezonesID, minutes from tmp_timezone_minutes;
		insert into tmp_timezone_minutes_3 (TimezonesID, minutes) select TimezonesID, minutes from tmp_timezone_minutes;

		-- ///////////////////////////////////////////////////// Timezone minutes logic



										insert into tmp_table_without_origination (
																RateTableID,
																TimezonesID,
																TimezoneTitle,
																CodeDeckId,
																CityTariff,
																Code,
																OriginationCode,
																VendorID,
																VendorName,
																EndDate,
																OneOffCost,
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

																OneOffCostCurrency,
																MonthlyCostCurrency,
																CostPerCallCurrency,
																CostPerMinuteCurrency,
																SurchargePerCallCurrency,
																SurchargePerMinuteCurrency,
																OutpaymentPerCallCurrency,
																OutpaymentPerMinuteCurrency,
																SurchargesCurrency,
																ChargebackCurrency,
																CollectionCostAmountCurrency,
																RegistrationCostPerNumberCurrency,

																Total
																)

	select
								rt.RateTableID,
								drtr.TimezonesID,
								t.Title as TimezoneTitle,
								rt.CodeDeckId,
								drtr.CityTariff,
								r.Code,
								r2.Code as OriginationCode,
								a.AccountID,
								a.AccountName,
								drtr.EndDate,
								@OneOffCost := CASE WHEN ( OneOffCostCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = OneOffCostCurrency THEN
									drtr.OneOffCost
								ELSE
								(
									-- Convert to base currrncy and x by RateGenerator Exhange
									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_  )
									* (drtr.OneOffCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = OneOffCostCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.OneOffCost
								ELSE
									(
										-- Convert to base currrncy and x by RateGenerator Exhange
										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.OneOffCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as OneOffCost,
								@MonthlyCost := ( ( CASE WHEN ( MonthlyCostCurrency is not null)  -- (MonthlyCost * @p_months) as MonthlyCost,
								THEN

								CASE WHEN  @v_CurrencyID_ = MonthlyCostCurrency THEN
									drtr.MonthlyCost
								ELSE
								(
									-- Convert to base currrncy and x by RateGenerator Exhange
									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_  )
									* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = MonthlyCostCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.MonthlyCost
								ELSE
									(
										-- Convert to base currrncy and x by RateGenerator Exhange
										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END) * @v_months) as MonthlyCost,

								@CostPerCall := CASE WHEN ( CostPerCallCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = CostPerCallCurrency THEN
									drtr.CostPerCall
								ELSE
								(
									-- Convert to base currrncy and x by RateGenerator Exhange
									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.CostPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CostPerCallCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.CostPerCall
								ELSE
									(
										-- Convert to base currrncy and x by RateGenerator Exhange
										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.CostPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as CostPerCall,

								@CostPerMinute := CASE WHEN ( CostPerMinuteCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = CostPerMinuteCurrency THEN
									drtr.CostPerMinute
								ELSE
								(
									-- Convert to base currrncy and x by RateGenerator Exhange
									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.CostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CostPerMinuteCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.CostPerMinute
								ELSE
									(
										-- Convert to base currrncy and x by RateGenerator Exhange
										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.CostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as CostPerMinute,


								@SurchargePerCall := CASE WHEN ( SurchargePerCallCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = SurchargePerCallCurrency THEN
									drtr.SurchargePerCall
								ELSE
								(
									-- Convert to base currrncy and x by RateGenerator Exhange
									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.SurchargePerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = SurchargePerCallCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.SurchargePerCall
								ELSE
									(
										-- Convert to base currrncy and x by RateGenerator Exhange
										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.SurchargePerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as SurchargePerCall,


								@SurchargePerMinute := CASE WHEN ( SurchargePerMinuteCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = SurchargePerMinuteCurrency THEN
									drtr.SurchargePerMinute
								ELSE
								(
									-- Convert to base currrncy and x by RateGenerator Exhange
									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.SurchargePerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = SurchargePerMinuteCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.SurchargePerMinute
								ELSE
									(
										-- Convert to base currrncy and x by RateGenerator Exhange
										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.SurchargePerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as SurchargePerMinute,

								@OutpaymentPerCall := CASE WHEN ( OutpaymentPerCallCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = OutpaymentPerCallCurrency THEN
									drtr.OutpaymentPerCall
								ELSE
								(
									-- Convert to base currrncy and x by RateGenerator Exhange
									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.OutpaymentPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = OutpaymentPerCallCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.OutpaymentPerCall
								ELSE
									(
										-- Convert to base currrncy and x by RateGenerator Exhange
										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.OutpaymentPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as OutpaymentPerCall,

								@OutpaymentPerMinute := CASE WHEN ( OutpaymentPerMinuteCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = OutpaymentPerMinuteCurrency THEN
									drtr.OutpaymentPerMinute
								ELSE
								(
									-- Convert to base currrncy and x by RateGenerator Exhange
									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.OutpaymentPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = OutpaymentPerMinuteCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.OutpaymentPerMinute
								ELSE
									(
										-- Convert to base currrncy and x by RateGenerator Exhange
										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.OutpaymentPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as OutpaymentPerMinute,

								@Surcharges := CASE WHEN ( SurchargesCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = SurchargesCurrency THEN
									drtr.Surcharges
								ELSE
								(
									-- Convert to base currrncy and x by RateGenerator Exhange
									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.Surcharges  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = SurchargesCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.Surcharges
								ELSE
									(
										-- Convert to base currrncy and x by RateGenerator Exhange
										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.Surcharges  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as Surcharges,

								@Chargeback := CASE WHEN ( ChargebackCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = ChargebackCurrency THEN
									drtr.Chargeback
								ELSE
								(
									-- Convert to base currrncy and x by RateGenerator Exhange
									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.Chargeback  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = ChargebackCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.Chargeback
								ELSE
									(
										-- Convert to base currrncy and x by RateGenerator Exhange
										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.Chargeback  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as Chargeback,

								@CollectionCostAmount := CASE WHEN ( CollectionCostAmountCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = CollectionCostAmountCurrency THEN
									drtr.CollectionCostAmount
								ELSE
								(
									-- Convert to base currrncy and x by RateGenerator Exhange
									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.CollectionCostAmount  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CollectionCostAmountCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.CollectionCostAmount
								ELSE
									(
										-- Convert to base currrncy and x by RateGenerator Exhange
										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.CollectionCostAmount  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as CollectionCostAmount,


								@CollectionCostPercentage := CASE WHEN ( CollectionCostAmountCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = CollectionCostAmountCurrency THEN
									drtr.CollectionCostPercentage
								ELSE
								(
									-- Convert to base currrncy and x by RateGenerator Exhange
									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.CollectionCostPercentage  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CollectionCostAmountCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.CollectionCostPercentage
								ELSE
									(
										-- Convert to base currrncy and x by RateGenerator Exhange
										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.CollectionCostPercentage  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as CollectionCostPercentage,

								@RegistrationCostPerNumber := CASE WHEN ( RegistrationCostPerNumberCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = RegistrationCostPerNumberCurrency THEN
									drtr.RegistrationCostPerNumber
								ELSE
								(
									-- Convert to base currrncy and x by RateGenerator Exhange
									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.RegistrationCostPerNumber  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = RegistrationCostPerNumberCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.RegistrationCostPerNumber
								ELSE
									(
										-- Convert to base currrncy and x by RateGenerator Exhange
										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.RegistrationCostPerNumber  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as RegistrationCostPerNumber,


								OneOffCostCurrency,
								MonthlyCostCurrency,
								CostPerCallCurrency,
								CostPerMinuteCurrency,
								SurchargePerCallCurrency,
								SurchargePerMinuteCurrency,
								OutpaymentPerCallCurrency,
								OutpaymentPerMinuteCurrency,
								SurchargesCurrency,
								ChargebackCurrency,
								CollectionCostAmountCurrency,
								RegistrationCostPerNumberCurrency,

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
									(IFNULL(@CostPerMinute,0) * (select minutes from tmp_timezone_minutes tm where tm.TimezonesID = t.TimezonesID ))	+
									(IFNULL(@CostPerCall,0) * @p_Calls)		+
									(IFNULL(@SurchargePerCall,0) * @v_MinutesFromMobileOrigination) +
									(IFNULL(@OutpaymentPerMinute,0) *  (select minutes from tmp_timezone_minutes_2 tm2 where tm2.TimezonesID = t.TimezonesID ))	+
									(IFNULL(@OutpaymentPerCall,0) * 	@p_Calls) +
									(IFNULL(@CollectionCostPercentage,0) * @v_CallerRate) +
									(IFNULL(@CollectionCostAmount,0) * (select minutes from tmp_timezone_minutes_3 tm3 where tm3.TimezonesID = t.TimezonesID ) )


								) as Total

				from tblRateTableDIDRate  drtr
				inner join tblRateTable  rt on rt.RateTableId = drtr.RateTableId -- and rt.DIDCategoryID = 2
				 inner join tblVendorConnection vc on vc.RateTableID = rt.RateTableId and vc.DIDCategoryID = rt.DIDCategoryID and vc.CompanyID = rt.CompanyId  and vc.Active=1
				inner join tblAccount a on vc.AccountId = a.AccountID and rt.CompanyId = a.CompanyId
				inner join tblRate r on drtr.RateID = r.RateID and r.CompanyID = vc.CompanyID
				left join tblRate r2 on drtr.OriginationRateID = r2.RateID and r.CompanyID = vc.CompanyID
		 		inner join tblCountry c on c.CountryID = r.CountryID
				inner join tblServiceTemplate st on st.ServiceTemplateId = @v_ProductID_
			--	 and  c.Country = st.country  AND r.Code = st.prefixName  -- for testing only
				and st.city_tariff  =  drtr.CityTariff and c.Country = st.country AND r.Code = concat(c.Prefix ,  TRIM(LEADING '0' FROM st.prefixName) )  --		for live only
				inner join tblTimezones t on t.TimezonesID =  drtr.TimezonesID
				where

				rt.CompanyId =  @v_CompanyId_

				and vc.DIDCategoryID = @v_DIDCategoryID_

				and drtr.ApprovedStatus = @v_ApprovedStatus

				and rt.Type = @v_DIDType -- did

			  	and rt.AppliedTo = @v_AppliedToVendor -- vendor

				and (
					 (p_EffectiveRate = 'now' AND EffectiveDate <= NOW())
					 OR
					 (p_EffectiveRate = 'future' AND EffectiveDate > NOW()  )
					 OR
					 (	 p_EffectiveRate = 'effective' AND EffectiveDate <= @p_EffectiveDate
							 AND ( drtr.EndDate IS NULL OR (drtr.EndDate > DATE(@p_EffectiveDate)) )
					 )
				)
			--	and t.TimezonesID = @v_TimezonesID
			;




										insert into tmp_table_with_origination (
																RateTableID,
																TimezonesID,
																TimezoneTitle,
																CodeDeckId,
																CityTariff,
																Code,
																OriginationCode,
																VendorID,
																VendorName,
																EndDate,
																OneOffCost,
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

																OneOffCostCurrency,
																MonthlyCostCurrency,
																CostPerCallCurrency,
																CostPerMinuteCurrency,
																SurchargePerCallCurrency,
																SurchargePerMinuteCurrency,
																OutpaymentPerCallCurrency,
																OutpaymentPerMinuteCurrency,
																SurchargesCurrency,
																ChargebackCurrency,
																CollectionCostAmountCurrency,
																RegistrationCostPerNumberCurrency,

																Total
																)

	select
								rt.RateTableID,
								drtr.TimezonesID,
								t.Title as TimezoneTitle,
								rt.CodeDeckId,
								drtr.CityTariff,
								r.Code,
								r2.Code as OriginationCode,
								a.AccountID,
								a.AccountName,
								drtr.EndDate,
								@OneOffCost := CASE WHEN ( OneOffCostCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = OneOffCostCurrency THEN
									drtr.OneOffCost
								ELSE
								(
									-- Convert to base currrncy and x by RateGenerator Exhange
									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_  )
									* (drtr.OneOffCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = OneOffCostCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.OneOffCost
								ELSE
									(
										-- Convert to base currrncy and x by RateGenerator Exhange
										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.OneOffCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as OneOffCost,
								@MonthlyCost := ( ( CASE WHEN ( MonthlyCostCurrency is not null)  -- (MonthlyCost * @p_months) as MonthlyCost,
								THEN

								CASE WHEN  @v_CurrencyID_ = MonthlyCostCurrency THEN
									drtr.MonthlyCost
								ELSE
								(
									-- Convert to base currrncy and x by RateGenerator Exhange
									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_  )
									* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = MonthlyCostCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.MonthlyCost
								ELSE
									(
										-- Convert to base currrncy and x by RateGenerator Exhange
										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END) * @v_months) as MonthlyCost,

								@CostPerCall := CASE WHEN ( CostPerCallCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = CostPerCallCurrency THEN
									drtr.CostPerCall
								ELSE
								(
									-- Convert to base currrncy and x by RateGenerator Exhange
									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.CostPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CostPerCallCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.CostPerCall
								ELSE
									(
										-- Convert to base currrncy and x by RateGenerator Exhange
										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.CostPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as CostPerCall,

								@CostPerMinute := CASE WHEN ( CostPerMinuteCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = CostPerMinuteCurrency THEN
									drtr.CostPerMinute
								ELSE
								(
									-- Convert to base currrncy and x by RateGenerator Exhange
									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.CostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CostPerMinuteCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.CostPerMinute
								ELSE
									(
										-- Convert to base currrncy and x by RateGenerator Exhange
										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.CostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as CostPerMinute,


								@SurchargePerCall := CASE WHEN ( SurchargePerCallCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = SurchargePerCallCurrency THEN
									drtr.SurchargePerCall
								ELSE
								(
									-- Convert to base currrncy and x by RateGenerator Exhange
									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.SurchargePerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = SurchargePerCallCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.SurchargePerCall
								ELSE
									(
										-- Convert to base currrncy and x by RateGenerator Exhange
										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.SurchargePerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as SurchargePerCall,


								@SurchargePerMinute := CASE WHEN ( SurchargePerMinuteCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = SurchargePerMinuteCurrency THEN
									drtr.SurchargePerMinute
								ELSE
								(
									-- Convert to base currrncy and x by RateGenerator Exhange
									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.SurchargePerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = SurchargePerMinuteCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.SurchargePerMinute
								ELSE
									(
										-- Convert to base currrncy and x by RateGenerator Exhange
										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.SurchargePerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as SurchargePerMinute,

								@OutpaymentPerCall := CASE WHEN ( OutpaymentPerCallCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = OutpaymentPerCallCurrency THEN
									drtr.OutpaymentPerCall
								ELSE
								(
									-- Convert to base currrncy and x by RateGenerator Exhange
									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.OutpaymentPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = OutpaymentPerCallCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.OutpaymentPerCall
								ELSE
									(
										-- Convert to base currrncy and x by RateGenerator Exhange
										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.OutpaymentPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as OutpaymentPerCall,

								@OutpaymentPerMinute := CASE WHEN ( OutpaymentPerMinuteCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = OutpaymentPerMinuteCurrency THEN
									drtr.OutpaymentPerMinute
								ELSE
								(
									-- Convert to base currrncy and x by RateGenerator Exhange
									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.OutpaymentPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = OutpaymentPerMinuteCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.OutpaymentPerMinute
								ELSE
									(
										-- Convert to base currrncy and x by RateGenerator Exhange
										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.OutpaymentPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as OutpaymentPerMinute,

								@Surcharges := CASE WHEN ( SurchargesCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = SurchargesCurrency THEN
									drtr.Surcharges
								ELSE
								(
									-- Convert to base currrncy and x by RateGenerator Exhange
									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.Surcharges  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = SurchargesCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.Surcharges
								ELSE
									(
										-- Convert to base currrncy and x by RateGenerator Exhange
										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.Surcharges  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as Surcharges,

								@Chargeback := CASE WHEN ( ChargebackCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = ChargebackCurrency THEN
									drtr.Chargeback
								ELSE
								(
									-- Convert to base currrncy and x by RateGenerator Exhange
									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.Chargeback  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = ChargebackCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.Chargeback
								ELSE
									(
										-- Convert to base currrncy and x by RateGenerator Exhange
										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.Chargeback  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as Chargeback,

								@CollectionCostAmount := CASE WHEN ( CollectionCostAmountCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = CollectionCostAmountCurrency THEN
									drtr.CollectionCostAmount
								ELSE
								(
									-- Convert to base currrncy and x by RateGenerator Exhange
									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.CollectionCostAmount  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CollectionCostAmountCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.CollectionCostAmount
								ELSE
									(
										-- Convert to base currrncy and x by RateGenerator Exhange
										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.CollectionCostAmount  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as CollectionCostAmount,


								@CollectionCostPercentage := CASE WHEN ( CollectionCostAmountCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = CollectionCostAmountCurrency THEN
									drtr.CollectionCostPercentage
								ELSE
								(
									-- Convert to base currrncy and x by RateGenerator Exhange
									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.CollectionCostPercentage  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CollectionCostAmountCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.CollectionCostPercentage
								ELSE
									(
										-- Convert to base currrncy and x by RateGenerator Exhange
										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.CollectionCostPercentage  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as CollectionCostPercentage,

								@RegistrationCostPerNumber := CASE WHEN ( RegistrationCostPerNumberCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = RegistrationCostPerNumberCurrency THEN
									drtr.RegistrationCostPerNumber
								ELSE
								(
									-- Convert to base currrncy and x by RateGenerator Exhange
									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.RegistrationCostPerNumber  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = RegistrationCostPerNumberCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.RegistrationCostPerNumber
								ELSE
									(
										-- Convert to base currrncy and x by RateGenerator Exhange
										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.RegistrationCostPerNumber  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as RegistrationCostPerNumber,


								OneOffCostCurrency,
								MonthlyCostCurrency,
								CostPerCallCurrency,
								CostPerMinuteCurrency,
								SurchargePerCallCurrency,
								SurchargePerMinuteCurrency,
								OutpaymentPerCallCurrency,
								OutpaymentPerMinuteCurrency,
								SurchargesCurrency,
								ChargebackCurrency,
								CollectionCostAmountCurrency,
								RegistrationCostPerNumberCurrency,


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
				 inner join tblVendorConnection vc on vc.RateTableID = rt.RateTableId and vc.DIDCategoryID = rt.DIDCategoryID and vc.CompanyID = rt.CompanyId  and vc.Active=1
				inner join tblAccount a on vc.AccountId = a.AccountID and rt.CompanyId = a.CompanyId
				inner join tblRate r on drtr.RateID = r.RateID and r.CompanyID = vc.CompanyID
				left join tblRate r2 on drtr.OriginationRateID = r2.RateID and r.CompanyID = vc.CompanyID
		 		inner join tblCountry c on c.CountryID = r.CountryID
				inner join tblServiceTemplate st on st.ServiceTemplateId = @v_ProductID_
			--	 and  c.Country = st.country  AND r.Code = st.prefixName  -- for testing only
				and st.city_tariff  =  drtr.CityTariff and c.Country = st.country AND r.Code = concat(c.Prefix ,  TRIM(LEADING '0' FROM st.prefixName) ) and  r2.Code = @p_MobileOrigination --		for live only
				inner join tblTimezones t on t.TimezonesID =  drtr.TimezonesID
				where

				rt.CompanyId =  @v_CompanyId_

				and vc.DIDCategoryID = @v_DIDCategoryID_

				and drtr.ApprovedStatus = @v_ApprovedStatus

				and rt.Type = @v_DIDType -- did

			  	and rt.AppliedTo = @v_AppliedToVendor -- vendor

				and (
					 (p_EffectiveRate = 'now' AND EffectiveDate <= NOW())
					 OR
					 (p_EffectiveRate = 'future' AND EffectiveDate > NOW()  )
					 OR
					 (	 p_EffectiveRate = 'effective' AND EffectiveDate <= @p_EffectiveDate
							 AND ( drtr.EndDate IS NULL OR (drtr.EndDate > DATE(@p_EffectiveDate)) )
					 )
				)
			--	and t.TimezonesID = @v_TimezonesID
			;


			delete t1 from tmp_table_without_origination t1 inner join tmp_table_with_origination t2 on t1.VendorID = t2.VendorID and t1.TimezonesID = t2.TimezonesID and t1.Code = t2.Code;

				insert into tmp_tblRateTableDIDRate (
										RateTableID,
										TimezonesID,
										TimezoneTitle,
										CodeDeckId,
										Code,
										OriginationCode,
										VendorID,
										VendorName,
										OneOffCost,
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

										OneOffCostCurrency,
										MonthlyCostCurrency,
										CostPerCallCurrency,
										CostPerMinuteCurrency,
										SurchargePerCallCurrency,
										SurchargePerMinuteCurrency,
										OutpaymentPerCallCurrency,
										OutpaymentPerMinuteCurrency,
										SurchargesCurrency,
										ChargebackCurrency,
										CollectionCostAmountCurrency,
										RegistrationCostPerNumberCurrency,

										Total
										)

										select
										RateTableID,
										TimezonesID,
										TimezoneTitle,
										CodeDeckId,
										Code,
										OriginationCode,
										VendorID,
										VendorName,
										OneOffCost,
										(MonthlyCost * @v_months) as MonthlyCost,
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

										OneOffCostCurrency,
										MonthlyCostCurrency,
										CostPerCallCurrency,
										CostPerMinuteCurrency,
										SurchargePerCallCurrency,
										SurchargePerMinuteCurrency,
										OutpaymentPerCallCurrency,
										OutpaymentPerMinuteCurrency,
										SurchargesCurrency,
										ChargebackCurrency,
										CollectionCostAmountCurrency,
										RegistrationCostPerNumberCurrency,
										Total
										from (
												select
												RateTableID,
												TimezonesID,
												TimezoneTitle,
												CodeDeckId,
												Code,
												OriginationCode,
												VendorID,
												VendorName,
												OneOffCost,
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

												OneOffCostCurrency,
												MonthlyCostCurrency,
												CostPerCallCurrency,
												CostPerMinuteCurrency,
												SurchargePerCallCurrency,
												SurchargePerMinuteCurrency,
												OutpaymentPerCallCurrency,
												OutpaymentPerMinuteCurrency,
												SurchargesCurrency,
												ChargebackCurrency,
												CollectionCostAmountCurrency,
												RegistrationCostPerNumberCurrency,

												Total
												from
												tmp_table_without_origination

												union all

												select
												RateTableID,
												TimezonesID,
												TimezoneTitle,
												CodeDeckId,
												Code,
												OriginationCode,
												VendorID,
												VendorName,
												OneOffCost,
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

												OneOffCostCurrency,
												MonthlyCostCurrency,
												CostPerCallCurrency,
												CostPerMinuteCurrency,
												SurchargePerCallCurrency,
												SurchargePerMinuteCurrency,
												OutpaymentPerCallCurrency,
												OutpaymentPerMinuteCurrency,
												SurchargesCurrency,
												ChargebackCurrency,
												CollectionCostAmountCurrency,
												RegistrationCostPerNumberCurrency,
												Total
												from
												tmp_table_with_origination

										) tmp
										where Total is not null;


 		-- testing output
		-- select * from tmp_tblRateTableDIDRate;

			insert into tmp_vendor_position (VendorID , vPosition,Total)
			select
			VendorID , vPosition,Total
			from (

				SELECT
					distinct
					v.VendorID,
					v.Total,
						@rank := ( CASE WHEN(@prev_VendorID != v.VendorID and @prev_Total <= v.Total AND (@v_percentageRate_ = 0 OR  (@v_percentageRate_ > 0 AND ROUND(((v.Total - @prev_Total) /( @prev_Total * 100)),2) > @v_percentageRate_) )   )
						THEN  @rank + 1
										 ELSE 1
										 END
					) AS vPosition,
					@prev_VendorID := v.VendorID,
					@prev_Total := v.Total

				FROM (

						select distinct  VendorID , sum(Total) as Total from tmp_tblRateTableDIDRate group by VendorID
					) v
					, (SELECT  @prev_VendorID := NUll ,  @rank := 0 ,  @prev_Total := 0 ) f

				order by v.Total,v.VendorID asc
			) tmp
			where vPosition <= @v_RatePosition_;

			SET @v_SelectedVendor = ( select VendorID from tmp_vendor_position where vPosition <= @v_RatePosition_ order by vPosition , Total  limit 1 );

		-- testing output
		-- select * from tmp_vendor_position;
		-- select @v_SelectedVendor;



			insert into tmp_SelectedVendortblRateTableDIDRate
			(
					RateTableID,
					TimezonesID,
					TimezoneTitle,
					Code,
					OriginationCode,
					VendorID,
					CodeDeckId,
					CityTariff,
					EndDate,
					VendorName,
					OneOffCost,
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
					OneOffCostCurrency,
					MonthlyCostCurrency,
					CostPerCallCurrency,
					CostPerMinuteCurrency,
					SurchargePerCallCurrency,
					SurchargePerMinuteCurrency,
					OutpaymentPerCallCurrency,
					OutpaymentPerMinuteCurrency,
					SurchargesCurrency,
					ChargebackCurrency,
					CollectionCostAmountCurrency,
					RegistrationCostPerNumberCurrency

			)
			select
					RateTableID,
					TimezonesID,
					TimezoneTitle,
					Code,
					OriginationCode,
					VendorID,
					CodeDeckId,
					CityTariff,
					VendorName,
					EndDate,
					OneOffCost,
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

					OneOffCostCurrency,
					MonthlyCostCurrency,
					CostPerCallCurrency,
					CostPerMinuteCurrency,
					SurchargePerCallCurrency,
					SurchargePerMinuteCurrency,
					OutpaymentPerCallCurrency,
					OutpaymentPerMinuteCurrency,
					SurchargesCurrency,
					ChargebackCurrency,
					CollectionCostAmountCurrency,
					RegistrationCostPerNumberCurrency

			from tmp_tblRateTableDIDRate

			where VendorID = @v_SelectedVendor ;

		-- testing output
		-- select * from tmp_SelectedVendortblRateTableDIDRate;


			DROP TEMPORARY TABLE IF EXISTS tmp_MergeComponents;
			CREATE TEMPORARY TABLE tmp_MergeComponents(
				ID int auto_increment,
				Component varchar(500),
				Origination varchar(20),
				TimezonesID int,
				ComponentAction varchar(20),
				MergeToComponent varchar(20),
				MergeToTimezonesID int,
				MergeToOrigination varchar(20),
				primary key (ID)
			);

			insert into tmp_MergeComponents ( Component,Origination,TimezonesID,ComponentAction,MergeToComponent,MergeToTimezonesID,MergeToOrigination )
			select Component,Origination,TimezonesID,Action,MergeTo,ToTimezonesID,ToOrigination
			from tblRateGeneratorCostComponent
			where RateGeneratorId = @p_RateGeneratorId
			order by CostComponentID asc;

		 -- testing output
		-- select * from tmp_MergeComponents;


	 	SET @v_pointer_ = 1;
		SET @v_rowCount_ = ( SELECT COUNT(*) FROM tmp_MergeComponents );

		WHILE @v_pointer_ <= @v_rowCount_
		DO





				SELECT
				Component,Origination,TimezonesID,ComponentAction,MergeToComponent,MergeToTimezonesID,MergeToOrigination

				INTO

				@v_Component,@v_OriginationCode,@v_TimezonesID,@v_ComponentAction,@v_MergeToComponent,@v_MergeToTimezonesID,@v_MergeToOrigination

				FROM tmp_MergeComponents WHERE ID = @v_pointer_;

				IF @v_ComponentAction = 'sum' THEN

					SET @ResultField = concat('(' ,  REPLACE(@v_Component,',',' + ') , ') ');

				ELSE

					SET @ResultField = concat('GREATEST(' ,  @v_Component, ') ');

				END IF;

				SET @stm1 = CONCAT('
						update tmp_SelectedVendortblRateTableDIDRate srt
						inner join (

								select

									@v_MergeToTimezonesID as TimezonesID,
									Code,
									if (@v_MergeToOrigination = "",OriginationCode,@v_MergeToOrigination) as OriginationCode,
									', @ResultField , ' as componentValue

									from tmp_tblRateTableDIDRate

								where
									VendorID = @v_SelectedVendor

								AND TimezonesID = @v_TimezonesID

								AND (
										@v_OriginationCode = ""
										OR
										(@v_OriginationCode != "" AND OriginationCode = @v_OriginationCode)
									)


						) tmp on tmp.TimezonesID = srt.TimezonesID and tmp.Code = srt.Code and tmp.OriginationCode = srt.OriginationCode
						set

						' , 'new_', @v_MergeToComponent , ' = componentValue,
						srt.TimezonesID = tmp.TimezonesID;
				');
				PREPARE stm1 FROM @stm1;
				EXECUTE stm1;
				DEALLOCATE PREPARE stm1;

				select count(*) into @v_updated_rows
				from tmp_SelectedVendortblRateTableDIDRate
				where TimezonesID = @v_MergeToTimezonesID
				AND (
					@v_OriginationCode = ''
					OR
					(@v_OriginationCode != '' AND OriginationCode = @v_OriginationCode)
				);

				IF @v_updated_rows  = 0 THEN



						insert into tmp_SelectedVendortblRateTableDIDRate
						(
								TimezonesID,
								TimezoneTitle,
								Code,
								OriginationCode,
								VendorID,
								CodeDeckId,
								CityTariff,
								EndDate,
								VendorName,
								OneOffCost,
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
								OneOffCostCurrency,
								MonthlyCostCurrency,
								CostPerCallCurrency,
								CostPerMinuteCurrency,
								SurchargePerCallCurrency,
								SurchargePerMinuteCurrency,
								OutpaymentPerCallCurrency,
								OutpaymentPerMinuteCurrency,
								SurchargesCurrency,
								ChargebackCurrency,
								CollectionCostAmountCurrency,
								RegistrationCostPerNumberCurrency
						)
						select

								@v_MergeToTimezonesID as TimezonesID,
								TimezoneTitle,
								Code,
								if (@v_MergeToOrigination = '',OriginationCode,@v_MergeToOrigination) as OriginationCode,
								VendorID,
								CodeDeckId,
								CityTariff,
								VendorName,
								EndDate,
								OneOffCost,
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
								OneOffCostCurrency,
								MonthlyCostCurrency,
								CostPerCallCurrency,
								CostPerMinuteCurrency,
								SurchargePerCallCurrency,
								SurchargePerMinuteCurrency,
								OutpaymentPerCallCurrency,
								OutpaymentPerMinuteCurrency,
								SurchargesCurrency,
								ChargebackCurrency,
								CollectionCostAmountCurrency,
								RegistrationCostPerNumberCurrency

						from tmp_tblRateTableDIDRate

						where
							VendorID = @v_SelectedVendor

						AND TimezonesID = @v_TimezonesID

						AND (
								@v_OriginationCode = ''
								OR
								(@v_OriginationCode != '' AND OriginationCode = @v_OriginationCode)
							);

				END IF;




			SET @v_pointer_ = @v_pointer_ + 1;

		END WHILE;

		 -- testing output
		-- select * from tmp_SelectedVendortblRateTableDIDRate;


		update tmp_SelectedVendortblRateTableDIDRate
		SET
			OneOffCost  = IF(new_OneOffCost is null , OneOffCost ,new_OneOffCost)  	,
			MonthlyCost  = IF(new_MonthlyCost is null , MonthlyCost ,new_MonthlyCost)  	,
			CostPerCall  = IF(new_CostPerCall is null , CostPerCall ,new_CostPerCall)  	,
			CostPerMinute  = IF(new_CostPerMinute is null , CostPerMinute ,new_CostPerMinute)  	,
			SurchargePerCall  = IF(new_SurchargePerCall is null , SurchargePerCall ,new_SurchargePerCall)  	,
			SurchargePerMinute  = IF(new_SurchargePerMinute is null , SurchargePerMinute ,new_SurchargePerMinute)  	,
			OutpaymentPerCall  = IF(new_OutpaymentPerCall is null , OutpaymentPerCall ,new_OutpaymentPerCall)  	,
			OutpaymentPerMinute  = IF(new_OutpaymentPerMinute is null , OutpaymentPerMinute ,new_OutpaymentPerMinute)  	,
			Surcharges  = IF(new_Surcharges is null , Surcharges ,new_Surcharges)  	,
			Chargeback  = IF(new_Chargeback is null , Chargeback ,new_Chargeback)  	,
			CollectionCostAmount  = IF(new_CollectionCostAmount is null , CollectionCostAmount ,new_CollectionCostAmount)  	,
			CollectionCostPercentage  = IF(new_CollectionCostPercentage is null , CollectionCostPercentage ,new_CollectionCostPercentage)  	,
			RegistrationCostPerNumber  = IF(new_RegistrationCostPerNumber is null , RegistrationCostPerNumber ,new_RegistrationCostPerNumber) ;

		 -- testing output
		-- select * from tmp_Raterules_;
		-- select * from tmp_SelectedVendortblRateTableDIDRate;

	 	SET @v_pointer_ = 1;
		SET @v_rowCount_ = ( SELECT COUNT(*) FROM tmp_Raterules_ );

		WHILE @v_pointer_ <= @v_rowCount_
		DO

			SET @v_rateRuleId_ = (SELECT rateruleid FROM tmp_Raterules_ rr WHERE rr.RowNo = @v_pointer_);


						update tmp_SelectedVendortblRateTableDIDRate rt
						inner join tmp_Raterules_ rr on rr.TimezonesID  = rt.TimezonesID and (rr.Origination = '' OR rr.Origination = rt.OriginationCode )
						LEFT join tblRateRuleMargin rule_mgn1 on  rr.RowNo  = @v_pointer_
						AND
						(
							(rr.Component = 'OneOffCost' AND OneOffCost Between rule_mgn1.MinRate and rule_mgn1.MaxRate) OR
							(rr.Component = 'MonthlyCost' AND MonthlyCost Between rule_mgn1.MinRate and rule_mgn1.MaxRate) OR
							(rr.Component = 'CostPerCall' AND CostPerCall Between rule_mgn1.MinRate and rule_mgn1.MaxRate) OR
							(rr.Component = 'CostPerMinute' AND CostPerMinute Between rule_mgn1.MinRate and rule_mgn1.MaxRate) OR
							(rr.Component = 'SurchargePerCall' AND SurchargePerCall Between rule_mgn1.MinRate and rule_mgn1.MaxRate) OR
							(rr.Component = 'SurchargePerMinute' AND SurchargePerMinute Between rule_mgn1.MinRate and rule_mgn1.MaxRate) OR
							(rr.Component = 'OutpaymentPerCall' AND OutpaymentPerCall Between rule_mgn1.MinRate and rule_mgn1.MaxRate) OR
							(rr.Component = 'OutpaymentPerMinute' AND OutpaymentPerMinute Between rule_mgn1.MinRate and rule_mgn1.MaxRate) OR
							(rr.Component = 'Surcharges' AND Surcharges Between rule_mgn1.MinRate and rule_mgn1.MaxRate) OR
							(rr.Component = 'Chargeback' AND Chargeback Between rule_mgn1.MinRate and rule_mgn1.MaxRate) OR
							(rr.Component = 'CollectionCostAmount' AND CollectionCostAmount Between rule_mgn1.MinRate and rule_mgn1.MaxRate) OR
							(rr.Component = 'CollectionCostPercentage' AND CollectionCostPercentage Between rule_mgn1.MinRate and rule_mgn1.MaxRate) OR
							(rr.Component = 'RegistrationCostPerNumber' AND RegistrationCostPerNumber Between rule_mgn1.MinRate and rule_mgn1.MaxRate)

						)

						SET
						OneOffCost = CASE WHEN rr.Component = 'OneOffCost' AND rule_mgn1.RateRuleId is not null THEN
											CASE WHEN trim(IFNULL(rule_mgn1.AddMargin,"")) != '' THEN
												OneOffCost + (CASE WHEN rule_mgn1.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn1.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * OneOffCost) ELSE rule_mgn1.addmargin END)
											WHEN trim(IFNULL(rule_mgn1.FixedValue,"")) != '' THEN
												rule_mgn1.FixedValue
											ELSE
												OneOffCost
											END
									ELSE
									OneOffCost
									END,

						MonthlyCost = CASE WHEN rr.Component = 'MonthlyCost' AND rule_mgn1.RateRuleId is not null THEN
									CASE WHEN trim(IFNULL(rule_mgn1.AddMargin,"")) != '' THEN
										MonthlyCost + (CASE WHEN rule_mgn1.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn1.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * MonthlyCost) ELSE rule_mgn1.addmargin END)
									WHEN trim(IFNULL(rule_mgn1.FixedValue,"")) != '' THEN
										rule_mgn1.FixedValue
									ELSE
										MonthlyCost
									END
							ELSE
							MonthlyCost
							END,

						CostPerCall = CASE WHEN rr.Component = 'CostPerCall' AND rule_mgn1.RateRuleId is not null THEN
									CASE WHEN trim(IFNULL(rule_mgn1.AddMargin,"")) != '' THEN
										CostPerCall + (CASE WHEN rule_mgn1.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn1.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * CostPerCall) ELSE rule_mgn1.addmargin END)
									WHEN trim(IFNULL(rule_mgn1.FixedValue,"")) != '' THEN
										rule_mgn1.FixedValue
									ELSE
										CostPerCall
									END
							ELSE
							CostPerCall
							END,

						CostPerMinute = CASE WHEN rr.Component = 'CostPerMinute' AND rule_mgn1.RateRuleId is not null THEN
									CASE WHEN trim(IFNULL(rule_mgn1.AddMargin,"")) != '' THEN
										CostPerMinute + (CASE WHEN rule_mgn1.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn1.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * CostPerMinute) ELSE rule_mgn1.addmargin END)
									WHEN trim(IFNULL(rule_mgn1.FixedValue,"")) != '' THEN
										rule_mgn1.FixedValue
									ELSE
										CostPerMinute
									END
							ELSE
							CostPerMinute
							END,

						SurchargePerCall = CASE WHEN rr.Component = 'SurchargePerCall' AND rule_mgn1.RateRuleId is not null THEN
									CASE WHEN trim(IFNULL(rule_mgn1.AddMargin,"")) != '' THEN
										SurchargePerCall + (CASE WHEN rule_mgn1.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn1.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * SurchargePerCall) ELSE rule_mgn1.addmargin END)
									WHEN trim(IFNULL(rule_mgn1.FixedValue,"")) != '' THEN
										rule_mgn1.FixedValue
									ELSE
										SurchargePerCall
									END
							ELSE
							SurchargePerCall
							END,

						SurchargePerMinute = CASE WHEN rr.Component = 'SurchargePerMinute' AND rule_mgn1.RateRuleId is not null THEN
									CASE WHEN trim(IFNULL(rule_mgn1.AddMargin,"")) != '' THEN
										SurchargePerMinute + (CASE WHEN rule_mgn1.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn1.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * SurchargePerMinute) ELSE rule_mgn1.addmargin END)
									WHEN trim(IFNULL(rule_mgn1.FixedValue,"")) != '' THEN
										rule_mgn1.FixedValue
									ELSE
										SurchargePerMinute
									END
							ELSE
							SurchargePerMinute
							END,

						OutpaymentPerCall = CASE WHEN rr.Component = 'OutpaymentPerCall' AND rule_mgn1.RateRuleId is not null THEN
									CASE WHEN trim(IFNULL(rule_mgn1.AddMargin,"")) != '' THEN
										OutpaymentPerCall + (CASE WHEN rule_mgn1.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn1.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * OutpaymentPerCall) ELSE rule_mgn1.addmargin END)
									WHEN trim(IFNULL(rule_mgn1.FixedValue,"")) != '' THEN
										rule_mgn1.FixedValue
									ELSE
										OutpaymentPerCall
									END
							ELSE
							OutpaymentPerCall
							END,

						OutpaymentPerMinute = CASE WHEN rr.Component = 'OutpaymentPerMinute' AND rule_mgn1.RateRuleId is not null THEN
									CASE WHEN trim(IFNULL(rule_mgn1.AddMargin,"")) != '' THEN
										OutpaymentPerMinute + (CASE WHEN rule_mgn1.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn1.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * OutpaymentPerMinute) ELSE rule_mgn1.addmargin END)
									WHEN trim(IFNULL(rule_mgn1.FixedValue,"")) != '' THEN
										rule_mgn1.FixedValue
									ELSE
										OutpaymentPerMinute
									END
							ELSE
							OutpaymentPerMinute
							END,
						Surcharges = CASE WHEN rr.Component = 'Surcharges' AND rule_mgn1.RateRuleId is not null THEN
									CASE WHEN trim(IFNULL(rule_mgn1.AddMargin,"")) != '' THEN
										Surcharges + (CASE WHEN rule_mgn1.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn1.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * Surcharges) ELSE rule_mgn1.addmargin END)
									WHEN trim(IFNULL(rule_mgn1.FixedValue,"")) != '' THEN
										rule_mgn1.FixedValue
									ELSE
										Surcharges
									END
							ELSE
							Surcharges
							END,

						Chargeback = CASE WHEN rr.Component = 'Chargeback' AND rule_mgn1.RateRuleId is not null THEN
									CASE WHEN trim(IFNULL(rule_mgn1.AddMargin,"")) != '' THEN
										Chargeback + (CASE WHEN rule_mgn1.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn1.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * Chargeback) ELSE rule_mgn1.addmargin END)
									WHEN trim(IFNULL(rule_mgn1.FixedValue,"")) != '' THEN
										rule_mgn1.FixedValue
									ELSE
										Chargeback
									END
							ELSE
							Chargeback
							END,

						CollectionCostAmount = CASE WHEN rr.Component = 'CollectionCostAmount' AND rule_mgn1.RateRuleId is not null THEN
									CASE WHEN trim(IFNULL(rule_mgn1.AddMargin,"")) != '' THEN
										CollectionCostAmount + (CASE WHEN rule_mgn1.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn1.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * CollectionCostAmount) ELSE rule_mgn1.addmargin END)
									WHEN trim(IFNULL(rule_mgn1.FixedValue,"")) != '' THEN
										rule_mgn1.FixedValue
									ELSE
										CollectionCostAmount
									END
							ELSE
							CollectionCostAmount
							END,

						CollectionCostPercentage = CASE WHEN rr.Component = 'CollectionCostPercentage' AND rule_mgn1.RateRuleId is not null THEN
									CASE WHEN trim(IFNULL(rule_mgn1.AddMargin,"")) != '' THEN
										CollectionCostPercentage + (CASE WHEN rule_mgn1.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn1.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * CollectionCostPercentage) ELSE rule_mgn1.addmargin END)
									WHEN trim(IFNULL(rule_mgn1.FixedValue,"")) != '' THEN
										rule_mgn1.FixedValue
									ELSE
										CollectionCostPercentage
									END
							ELSE
							CollectionCostPercentage
							END,

						RegistrationCostPerNumber = CASE WHEN rr.Component = 'RegistrationCostPerNumber' AND rule_mgn1.RateRuleId is not null THEN
									CASE WHEN trim(IFNULL(rule_mgn1.AddMargin,"")) != '' THEN
										RegistrationCostPerNumber + (CASE WHEN rule_mgn1.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn1.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * RegistrationCostPerNumber) ELSE rule_mgn1.addmargin END)
									WHEN trim(IFNULL(rule_mgn1.FixedValue,"")) != '' THEN
										rule_mgn1.FixedValue
									ELSE
										RegistrationCostPerNumber
									END
							ELSE
							RegistrationCostPerNumber
							END
			;




			SET @v_pointer_ = @v_pointer_ + 1;

		END WHILE;


	 -- testing output
		-- select * from tmp_RateGeneratorCalculatedRate_;
		-- select * from tmp_SelectedVendortblRateTableDIDRate;

	 	SET @v_pointer_ = 1;
		SET @v_rowCount_ = ( SELECT COUNT(*) FROM tmp_RateGeneratorCalculatedRate_ );

		WHILE @v_pointer_ <= @v_rowCount_
		DO

						--	SET @v_rateRuleId_ = (SELECT rateruleid FROM tmp_RateGeneratorCalculatedRate_ rr WHERE rr.RowNo = @v_pointer_);

						-- Rate <  v_LessThenRate

						update tmp_SelectedVendortblRateTableDIDRate rt
						inner join tmp_RateGeneratorCalculatedRate_ rr on
						rr.RowNo  = @v_pointer_  AND rr.TimezonesID  = rt.TimezonesID  and   (rr.Origination = '' OR rr.Origination = rt.OriginationCode )


						SET
						OneOffCost = CASE WHEN FIND_IN_SET(rr.Component,'OneOffCost') != 0 AND OneOffCost < RateLessThen AND rr.CalculatedRateID is not null THEN
						ChangeRateTo
						ELSE
						OneOffCost
						END,
						MonthlyCost = CASE WHEN FIND_IN_SET(rr.Component,'MonthlyCost') != 0 AND MonthlyCost < RateLessThen AND rr.CalculatedRateID is not null THEN
						ChangeRateTo
						ELSE
						MonthlyCost
						END,
						CostPerCall = CASE WHEN FIND_IN_SET(rr.Component,'CostPerCall') != 0 AND CostPerCall < RateLessThen AND rr.CalculatedRateID is not null THEN
						ChangeRateTo
						ELSE
						CostPerCall
						END,
						CostPerMinute = CASE WHEN FIND_IN_SET(rr.Component,'CostPerMinute') != 0 AND CostPerMinute < RateLessThen AND rr.CalculatedRateID is not null THEN
						ChangeRateTo
						ELSE
						CostPerMinute
						END,
						SurchargePerCall = CASE WHEN FIND_IN_SET(rr.Component,'SurchargePerCall') != 0 AND SurchargePerCall < RateLessThen AND rr.CalculatedRateID is not null THEN
						ChangeRateTo
						ELSE
						SurchargePerCall
						END,
						SurchargePerMinute = CASE WHEN FIND_IN_SET(rr.Component,'SurchargePerMinute') != 0 AND SurchargePerMinute < RateLessThen AND rr.CalculatedRateID is not null THEN
						ChangeRateTo
						ELSE
						SurchargePerMinute
						END,
						OutpaymentPerCall = CASE WHEN FIND_IN_SET(rr.Component,'OutpaymentPerCall') != 0 AND OutpaymentPerCall < RateLessThen AND rr.CalculatedRateID is not null THEN
						ChangeRateTo
						ELSE
						OutpaymentPerCall
						END,
						OutpaymentPerMinute = CASE WHEN FIND_IN_SET(rr.Component,'OutpaymentPerMinute') != 0 AND OutpaymentPerMinute < RateLessThen AND rr.CalculatedRateID is not null THEN
						ChangeRateTo
						ELSE
						OutpaymentPerMinute
						END,
						Surcharges = CASE WHEN FIND_IN_SET(rr.Component,'Surcharges') != 0 AND Surcharges < RateLessThen AND rr.CalculatedRateID is not null THEN
						ChangeRateTo
						ELSE
						Surcharges
						END,
						Chargeback = CASE WHEN FIND_IN_SET(rr.Component,'Chargeback') != 0 AND Chargeback < RateLessThen AND rr.CalculatedRateID is not null THEN
						ChangeRateTo
						ELSE
						Chargeback
						END,
						CollectionCostAmount = CASE WHEN FIND_IN_SET(rr.Component,'CollectionCostAmount') != 0 AND CollectionCostAmount < RateLessThen AND rr.CalculatedRateID is not null THEN
						ChangeRateTo
						ELSE
						CollectionCostAmount
						END,
						CollectionCostPercentage = CASE WHEN FIND_IN_SET(rr.Component,'CollectionCostPercentage') != 0 AND CollectionCostPercentage < RateLessThen AND rr.CalculatedRateID is not null THEN
						ChangeRateTo
						ELSE
						CollectionCostPercentage
						END,
						RegistrationCostPerNumber = CASE WHEN FIND_IN_SET(rr.Component,'RegistrationCostPerNumber') != 0 AND RegistrationCostPerNumber < RateLessThen AND rr.CalculatedRateID is not null THEN
						ChangeRateTo
						ELSE
						RegistrationCostPerNumber
						END;


			SET @v_pointer_ = @v_pointer_ + 1;

		END WHILE;


	 -- testing output
		-- select * from tmp_SelectedVendortblRateTableDIDRate;

		-- leave GenerateRateTable;


		SET @v_SelectedRateTableID = ( select RateTableID from tmp_SelectedVendortblRateTableDIDRate limit 1 );

		SET @v_AffectedRecords_ = 0;

		START TRANSACTION;

		IF p_RateTableId = -1
		THEN

			SET @v_codedeckid_ = ( select CodeDeckId from tmp_SelectedVendortblRateTableDIDRate limit 1 );

			INSERT INTO tblRateTable (Type, CompanyId, RateTableName, RateGeneratorID,DIDCategoryID, TrunkID, CodeDeckId,CurrencyID,Status, RoundChargedAmount,MinimumCallCharge,AppliedTo,created_at,updated_at, CreatedBy,ModifiedBy)
			select  @v_DIDType as Type, @v_CompanyId_, p_rateTableName , @p_RateGeneratorId,DIDCategoryID, 0 as TrunkID,  CodeDeckId , CurrencyID, Status, RoundChargedAmount,MinimumCallCharge, @v_AppliedToCustomer as AppliedTo , now() ,now() ,p_ModifiedBy,p_ModifiedBy
			from tblRateTable where RateTableID = @v_SelectedRateTableID  limit 1;

			SET @p_RateTableId = LAST_INSERT_ID();

		ELSE

			SET @p_RateTableId = p_RateTableId;

			IF p_delete_exiting_rate = 1
			THEN

				UPDATE
					tblRateTableDIDRate
				SET
					EndDate = NOW()
				WHERE
					RateTableId = @p_RateTableId;


			call prc_ArchiveOldRateTableDIDRate(@p_RateTableId, NULL,p_ModifiedBy);

			END IF;

			update tblRateTableDIDRate rtd
			INNER JOIN tblRateTable rt  on rt.RateTableID = rtd.RateTableID
			INNER JOIN tblRate r
				ON rtd.RateID  = r.RateID
			LEFT JOIN tblRate rr
				ON rtd.OriginationRateID  = rr.RateID
			inner join tmp_SelectedVendortblRateTableDIDRate drtr on
			drtr.Code = r.Code and drtr.OriginationCode = rr.Code
			and rtd.TimezonesID = drtr.TimezonesID and rtd.CityTariff = drtr.CityTariff and  r.CodeDeckId = rr.CodeDeckId  AND  r.CodeDeckId = drtr.CodeDeckId

			SET rtd.EndDate = NOW()

			where
			rtd.RateTableID = @p_RateTableId and rtd.EffectiveDate = @p_EffectiveDate;

			call prc_ArchiveOldRateTableDIDRate(@p_RateTableId, NULL,p_ModifiedBy);



			SET @v_AffectedRecords_ = @v_AffectedRecords_ + FOUND_ROWS();


		END IF;

		INSERT INTO tblRateTableDIDRate (
							VendorID,
							RateTableId,
							TimezonesID,
							OriginationRateID,
							RateId,
							CityTariff,
							OneOffCost,
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
							OneOffCostCurrency,
							MonthlyCostCurrency,
							CostPerCallCurrency,
							CostPerMinuteCurrency,
							SurchargePerCallCurrency,
							SurchargePerMinuteCurrency,
							OutpaymentPerCallCurrency,
							OutpaymentPerMinuteCurrency,
							SurchargesCurrency,
							ChargebackCurrency,
							CollectionCostAmountCurrency,
							RegistrationCostPerNumberCurrency,
							EffectiveDate,
							EndDate,
							ApprovedStatus,

							created_at ,
							updated_at ,
							CreatedBy ,
							ModifiedBy


			)
			SELECT DISTINCT
						drtr.VendorID,
						@p_RateTableId as RateTableId,
						drtr.TimezonesID,
						rr.RateID as OriginationRateID,
						r.RateId,
						drtr.CityTariff,


						CASE WHEN ( rtd.OneOffCostCurrency is not null)
						THEN

						CASE WHEN  @v_CurrencyID_ = rtd.OneOffCostCurrency THEN
						drtr.OneOffCost
						ELSE
						(
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_  )
						* (drtr.OneOffCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rtd.OneOffCostCurrency and  CompanyID = @v_CompanyId_ ))
						)
						END

						WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
						drtr.OneOffCost
						ELSE
						(
							-- Convert to base currrncy and x by RateGenerator Exhange
							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
							* (drtr.OneOffCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
						)
						END as OneOffCost,

						( CASE WHEN ( rtd.MonthlyCostCurrency is not null)  -- (MonthlyCost * p_months) as MonthlyCost,
						THEN

						CASE WHEN  @v_CurrencyID_ = rtd.MonthlyCostCurrency THEN
						drtr.MonthlyCost
						ELSE
						(
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_  )
						* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rtd.MonthlyCostCurrency and  CompanyID = @v_CompanyId_ ))
						)
						END

						WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
						drtr.MonthlyCost
						ELSE
						(
							-- Convert to base currrncy and x by RateGenerator Exhange
							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
							* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
						)
						END) as MonthlyCost,

						CASE WHEN ( rtd.CostPerCallCurrency is not null)
						THEN

						CASE WHEN  @v_CurrencyID_ = rtd.CostPerCallCurrency THEN
						drtr.CostPerCall
						ELSE
						(
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
						* (drtr.CostPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rtd.CostPerCallCurrency and  CompanyID = @v_CompanyId_ ))
						)
						END

						WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
						drtr.CostPerCall
						ELSE
						(
							-- Convert to base currrncy and x by RateGenerator Exhange
							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
							* (drtr.CostPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
						)
						END as CostPerCall,

						CASE WHEN ( rtd.CostPerMinuteCurrency is not null)
						THEN

						CASE WHEN  @v_CurrencyID_ = rtd.CostPerMinuteCurrency THEN
						drtr.CostPerMinute
						ELSE
						(
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
						* (drtr.CostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rtd.CostPerMinuteCurrency and  CompanyID = @v_CompanyId_ ))
						)
						END

						WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
						drtr.CostPerMinute
						ELSE
						(
							-- Convert to base currrncy and x by RateGenerator Exhange
							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
							* (drtr.CostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
						)
						END as CostPerMinute,


						CASE WHEN ( rtd.SurchargePerCallCurrency is not null)
						THEN

						CASE WHEN  @v_CurrencyID_ = rtd.SurchargePerCallCurrency THEN
						drtr.SurchargePerCall
						ELSE
						(
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
						* (drtr.SurchargePerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rtd.SurchargePerCallCurrency and  CompanyID = @v_CompanyId_ ))
						)
						END

						WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
						drtr.SurchargePerCall
						ELSE
						(
							-- Convert to base currrncy and x by RateGenerator Exhange
							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
							* (drtr.SurchargePerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
						)
						END as SurchargePerCall,


						CASE WHEN ( rtd.SurchargePerMinuteCurrency is not null)
						THEN

						CASE WHEN  @v_CurrencyID_ = rtd.SurchargePerMinuteCurrency THEN
						drtr.SurchargePerMinute
						ELSE
						(
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
						* (drtr.SurchargePerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rtd.SurchargePerMinuteCurrency and  CompanyID = @v_CompanyId_ ))
						)
						END

						WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
						drtr.SurchargePerMinute
						ELSE
						(
							-- Convert to base currrncy and x by RateGenerator Exhange
							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
							* (drtr.SurchargePerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
						)
						END as SurchargePerMinute,

						CASE WHEN ( rtd.OutpaymentPerCallCurrency is not null)
						THEN

						CASE WHEN  @v_CurrencyID_ = rtd.OutpaymentPerCallCurrency THEN
						drtr.OutpaymentPerCall
						ELSE
						(
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
						* (drtr.OutpaymentPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rtd.OutpaymentPerCallCurrency and  CompanyID = @v_CompanyId_ ))
						)
						END

						WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
						drtr.OutpaymentPerCall
						ELSE
						(
							-- Convert to base currrncy and x by RateGenerator Exhange
							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
							* (drtr.OutpaymentPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
						)
						END as OutpaymentPerCall,

						CASE WHEN ( rtd.OutpaymentPerMinuteCurrency is not null)
						THEN

						CASE WHEN  @v_CurrencyID_ = rtd.OutpaymentPerMinuteCurrency THEN
						drtr.OutpaymentPerMinute
						ELSE
						(
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
						* (drtr.OutpaymentPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rtd.OutpaymentPerMinuteCurrency and  CompanyID = @v_CompanyId_ ))
						)
						END

						WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
						drtr.OutpaymentPerMinute
						ELSE
						(
							-- Convert to base currrncy and x by RateGenerator Exhange
							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
							* (drtr.OutpaymentPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
						)
						END as OutpaymentPerMinute,

						CASE WHEN ( rtd.SurchargesCurrency is not null)
						THEN

						CASE WHEN  @v_CurrencyID_ = rtd.SurchargesCurrency THEN
						drtr.Surcharges
						ELSE
						(
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
						* (drtr.Surcharges  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rtd.SurchargesCurrency and  CompanyID = @v_CompanyId_ ))
						)
						END

						WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
						drtr.Surcharges
						ELSE
						(
							-- Convert to base currrncy and x by RateGenerator Exhange
							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
							* (drtr.Surcharges  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
						)
						END as Surcharges,

						 CASE WHEN ( rtd.ChargebackCurrency is not null)
						THEN

						CASE WHEN  @v_CurrencyID_ = rtd.ChargebackCurrency THEN
						drtr.Chargeback
						ELSE
						(
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
						* (drtr.Chargeback  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rtd.ChargebackCurrency and  CompanyID = @v_CompanyId_ ))
						)
						END

						WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
						drtr.Chargeback
						ELSE
						(
							-- Convert to base currrncy and x by RateGenerator Exhange
							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
							* (drtr.Chargeback  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
						)
						END as Chargeback,

						CASE WHEN ( rtd.CollectionCostAmountCurrency is not null)
						THEN

						CASE WHEN  @v_CurrencyID_ = rtd.CollectionCostAmountCurrency THEN
						drtr.CollectionCostAmount
						ELSE
						(
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
						* (drtr.CollectionCostAmount  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rtd.CollectionCostAmountCurrency and  CompanyID = @v_CompanyId_ ))
						)
						END

						WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
						drtr.CollectionCostAmount
						ELSE
						(
							-- Convert to base currrncy and x by RateGenerator Exhange
							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
							* (drtr.CollectionCostAmount  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
						)
						END as CollectionCostAmount,


						CASE WHEN ( rtd.CollectionCostAmountCurrency is not null)
						THEN

						CASE WHEN  @v_CurrencyID_ = rtd.CollectionCostAmountCurrency THEN
						drtr.CollectionCostPercentage
						ELSE
						(
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
						* (drtr.CollectionCostPercentage  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rtd.CollectionCostAmountCurrency and  CompanyID = @v_CompanyId_ ))
						)
						END

						WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
						drtr.CollectionCostPercentage
						ELSE
						(
							-- Convert to base currrncy and x by RateGenerator Exhange
							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
							* (drtr.CollectionCostPercentage  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
						)
						END as CollectionCostPercentage,

						CASE WHEN ( rtd.RegistrationCostPerNumberCurrency is not null)
						THEN

						CASE WHEN  @v_CurrencyID_ = rtd.RegistrationCostPerNumberCurrency THEN
						drtr.RegistrationCostPerNumber
						ELSE
						(
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
						* (drtr.RegistrationCostPerNumber  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rtd.RegistrationCostPerNumberCurrency and  CompanyID = @v_CompanyId_ ))
						)
						END

						WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
						drtr.RegistrationCostPerNumber
						ELSE
						(
							-- Convert to base currrncy and x by RateGenerator Exhange
							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
							* (drtr.RegistrationCostPerNumber  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
						)
						END as RegistrationCostPerNumber,


						drtr.OneOffCostCurrency,
						drtr.MonthlyCostCurrency,
						drtr.CostPerCallCurrency,
						drtr.CostPerMinuteCurrency,
						drtr.SurchargePerCallCurrency,
						drtr.SurchargePerMinuteCurrency,
						drtr.OutpaymentPerCallCurrency,
						drtr.OutpaymentPerMinuteCurrency,
						drtr.SurchargesCurrency,
						drtr.ChargebackCurrency,
						drtr.CollectionCostAmountCurrency,
						drtr.RegistrationCostPerNumberCurrency,


						@p_EffectiveDate as EffectiveDate,
						IF(drtr.EndDate='0000-00-00 00:00:00',NULL,drtr.EndDate) as EndDate,
						@v_RateApprovalProcess_ as ApprovedStatus,


							now() as  created_at ,
							now() as updated_at ,
							p_ModifiedBy as CreatedBy ,
							p_ModifiedBy as ModifiedBy



						from tmp_SelectedVendortblRateTableDIDRate drtr
						inner join tblRateTable  rt on rt.RateTableId = drtr.RateTableId -- and rt.DIDCategoryID = 2
						INNER JOIN tblRate r ON drtr.Code = r.Code and r.CodeDeckId = drtr.CodeDeckId
						LEFT JOIN tblRate rr ON drtr.OriginationCode = rr.Code and r.CodeDeckId = rr.CodeDeckId
						LEFT join tblRateTableDIDRate rtd  on rtd.RateID  = r.RateID and rtd.OriginationRateID  = rr.RateID
						and  rtd.TimezonesID = drtr.TimezonesID and rtd.CityTariff = drtr.CityTariff
						and rtd.RateTableID = @p_RateTableId
						and rtd.EffectiveDate = @p_EffectiveDate
						WHERE rtd.RateTableDIDRateID is null;


		SET @v_AffectedRecords_ = @v_AffectedRecords_ + FOUND_ROWS();

		DROP TEMPORARY TABLE IF EXISTS tmp_EffectiveDates_;
		CREATE TEMPORARY TABLE tmp_EffectiveDates_ (
			RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			EffectiveDate  Date
		);
		INSERT INTO tmp_EffectiveDates_ (EffectiveDate)
		SELECT distinct
			EffectiveDate
		FROM
		(
			select distinct EffectiveDate
			from 	tblRateTableDIDRate
			WHERE
				RateTableId = @p_RateTableId
			Group By EffectiveDate
			order by EffectiveDate desc
		) tmp
		,(SELECT @row_num := 0) x;


		SET @v_pointer_ = 1;
		SET @v_rowCount_ = ( SELECT COUNT(*) FROM tmp_EffectiveDates_ );

		IF @v_rowCount_ > 0 THEN

			WHILE @v_pointer_ <= @v_rowCount_
			DO
				SET @EffectiveDate = ( SELECT EffectiveDate FROM tmp_EffectiveDates_ WHERE RowID = @v_pointer_ );


				UPDATE  tblRateTableDIDRate vr1
				inner join
				(
					select
						RateTableId,
						OriginationRateID,
						RateID,
						EffectiveDate,
						TimezonesID,
						CityTariff
					FROM tblRateTableDIDRate
					WHERE RateTableId = @p_RateTableId
						AND EffectiveDate =   @EffectiveDate
					order by EffectiveDate desc
				) tmpvr
				on
					vr1.RateTableId = tmpvr.RateTableId
					AND vr1.OriginationRateID = tmpvr.OriginationRateID
					AND vr1.RateID = tmpvr.RateID
					AND vr1.TimezonesID = tmpvr.TimezonesID
					AND vr1.CityTariff = tmpvr.CityTariff
					AND vr1.EffectiveDate < tmpvr.EffectiveDate
				SET
					vr1.EndDate = @EffectiveDate
				where
					vr1.RateTableId = @p_RateTableId

					AND vr1.EndDate is null;


				SET @v_pointer_ = @v_pointer_ + 1;

			END WHILE;

			update tblRateTableDIDRate
			SET
			OneOffCost = IF(OneOffCost = 0 , NULL, OneOffCost),
			MonthlyCost = IF(MonthlyCost = 0 , NULL, MonthlyCost),
			CostPerCall = IF(CostPerCall = 0 , NULL, CostPerCall),
			CostPerMinute = IF(CostPerMinute = 0 , NULL, CostPerMinute),
			SurchargePerCall = IF(SurchargePerCall = 0 , NULL, SurchargePerCall),
			SurchargePerMinute = IF(SurchargePerMinute = 0 , NULL, SurchargePerMinute),
			OutpaymentPerCall = IF(OutpaymentPerCall = 0 , NULL, OutpaymentPerCall),
			OutpaymentPerMinute = IF(OutpaymentPerMinute = 0 , NULL, OutpaymentPerMinute),
			Surcharges = IF(Surcharges = 0 , NULL, Surcharges),
			Chargeback = IF(Chargeback = 0 , NULL, Chargeback),
			CollectionCostAmount = IF(CollectionCostAmount = 0 , NULL, CollectionCostAmount),
			CollectionCostPercentage = IF(CollectionCostPercentage = 0 , NULL, CollectionCostPercentage),
			RegistrationCostPerNumber = IF(RegistrationCostPerNumber = 0 , NULL, RegistrationCostPerNumber),

			updated_at = now(),
			ModifiedBy = p_ModifiedBy

			where
			RateTableID = @p_RateTableId;

		END IF;

		commit;

		call prc_ArchiveOldRateTableDIDRate(@p_RateTableId, NULL,p_ModifiedBy);


		INSERT INTO tmp_JobLog_ (Message) VALUES (@p_RateTableId);

		SELECT * FROM tmp_JobLog_;


		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;



	END//
DELIMITER ;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
