use `speakintelligentRM`;

INSERT INTO `tblIntegration` (`IntegrationID`, `CompanyId`, `Title`, `Slug`, `ParentID`, `MultiOption`) VALUES (29, 1, 'Ingenico', 'ingenico', 4, 'N');

ALTER TABLE `tblRateTableDIDRate`
	CHANGE COLUMN `CityTariff` `City` VARCHAR(50) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci' AFTER `EndDate`,
	ADD COLUMN `Tariff` VARCHAR(50) NOT NULL DEFAULT '' AFTER `City`,
	DROP INDEX `IX_Unique_RateID_ORateID_RateTableId_Timezone_Effective_CityT`,
	ADD UNIQUE INDEX `IX_Unique_RateID_ORateID_RateTableId_Timezone_Effective_CityT` (`RateID`, `OriginationRateID`, `RateTableId`, `TimezonesID`, `EffectiveDate`, `City`, `Tariff`);

ALTER TABLE `tblRateTableDIDRateAA`
	CHANGE COLUMN `CityTariff` `City` VARCHAR(50) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci' AFTER `EndDate`,
	ADD COLUMN `Tariff` VARCHAR(50) NOT NULL DEFAULT '' AFTER `City`;

ALTER TABLE `tblRateTableDIDRateArchive`
	CHANGE COLUMN `CityTariff` `City` VARCHAR(50) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci' AFTER `EndDate`,
	ADD COLUMN `Tariff` VARCHAR(50) NOT NULL DEFAULT '' AFTER `City`;

ALTER TABLE `tblRateTableDIDRateChangeLog`
	CHANGE COLUMN `CityTariff` `City` VARCHAR(50) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci' AFTER `Description`,
	ADD COLUMN `Tariff` VARCHAR(50) NOT NULL DEFAULT '' AFTER `City`;

ALTER TABLE `tblTempRateTableDIDRate`
	CHANGE COLUMN `CityTariff` `City` VARCHAR(50) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci' AFTER `Description`,
	ADD COLUMN `Tariff` VARCHAR(50) NOT NULL DEFAULT '' AFTER `City`;

ALTER TABLE `tblRateTable`
	ADD COLUMN `Reseller` INT NOT NULL DEFAULT '0' AFTER `AppliedTo`;

ALTER TABLE `tblRateGenerator`
	ADD COLUMN `AppliedTo` INT NOT NULL DEFAULT '1' AFTER `Tariff`,
	ADD COLUMN `Reseller` INT NOT NULL DEFAULT '0' AFTER `AppliedTo`;

ALTER TABLE `tblTempRateTableRate`
	ADD COLUMN `Type` VARCHAR(50) NULL DEFAULT NULL AFTER `Description`;




DROP PROCEDURE IF EXISTS `prc_WSGenerateRateTableDID`;
DELIMITER //
CREATE PROCEDURE `prc_WSGenerateRateTableDID`(
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
			CountryID int,
			AccessType varchar(100),
			Prefix varchar(100),
			City varchar(100),
			Tariff varchar(100),
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
			`CountryID` INT(11) NULL DEFAULT NULL,
			`AccessType` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
			`Prefix` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
			`City` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
			`Tariff` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
			RowNo INT
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_table_without_origination;
		CREATE TEMPORARY TABLE tmp_table_without_origination (
				RateTableID int,
				TimezonesID  int,
				TimezoneTitle  varchar(100),
				CodeDeckId int,
				CountryID int,
				AccessType varchar(100),
				CountryPrefix varchar(100),
				City varchar(100),
				Tariff varchar(100),
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


				Total1 double(18,4),
				Total double(18,4)
			);

		DROP TEMPORARY TABLE IF EXISTS tmp_table_with_origination;
		CREATE TEMPORARY TABLE tmp_table_with_origination (
				RateTableID int,
				TimezonesID  int,
				TimezoneTitle  varchar(100),
				CodeDeckId int,
				CountryID int,
				AccessType varchar(100),
				CountryPrefix varchar(100),
				City varchar(100),
				Tariff varchar(100),
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




				Total1 double(18,4),
				Total double(18,4)
			);



		DROP TEMPORARY TABLE IF EXISTS tmp_tblRateTableDIDRate;
		CREATE TEMPORARY TABLE tmp_tblRateTableDIDRate (
				RateTableID int,
				TimezonesID  int,
				TimezoneTitle  varchar(100),
				CodeDeckId int,
				CountryID int,
				AccessType varchar(100),
				CountryPrefix varchar(100),
				City varchar(100),
				Tariff varchar(100),
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
				CountryID int,
				AccessType varchar(100),
				CountryPrefix varchar(100),
				City varchar(100),
				Tariff varchar(100),
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

			DROP TEMPORARY TABLE IF EXISTS tmp_origination_minutes;
			CREATE TEMPORARY TABLE tmp_origination_minutes (
				OriginationCode varchar(50),
				minutes int
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



		SET @p_EffectiveDate = p_EffectiveDate;






		SELECT
			rateposition,
			companyid ,
			tblRateGenerator.RateGeneratorName,
			RateGeneratorId,
			CurrencyID,

			DIDCategoryID,
			Calls,
			Minutes,
			DateFrom,
			DateTo,
			TimezonesID,
			TimezonesPercentage,
			Origination,
			OriginationPercentage,

			IFNULL(CountryID,''),
			IFNULL(AccessType,''),
			IFNULL(City,''),
			IFNULL(Tariff,''),
			IFNULL(Prefix,''),
			IFNULL(AppliedTo,''),
			IFNULL(Reseller,''),



			IF( percentageRate = '' OR percentageRate is null	,0, percentageRate )

			INTO @v_RatePosition_, @v_CompanyId_,   @v_RateGeneratorName_,@p_RateGeneratorId, @v_CurrencyID_,

			@v_DIDCategoryID_,
			@v_Calls,
			@v_Minutes,
			@v_StartDate_ ,@v_EndDate_ ,@v_TimezonesID, @v_TimezonesPercentage, @v_Origination, @v_OriginationPercentage,


			@p_CountryID,
			@p_AccessType,
			@p_City,
			@p_Tariff,
			@p_Prefix,
			@p_AppliedTo,
			@p_Reseller,


			@v_percentageRate_
		FROM tblRateGenerator
		WHERE RateGeneratorId = @p_RateGeneratorId;


		SELECT CurrencyId INTO @v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = @v_CompanyId_;

		SELECT IFNULL(Value,0) INTO @v_RateApprovalProcess_ FROM tblCompanySetting WHERE CompanyID = @v_CompanyId_ AND `Key`='RateApprovalProcess';


		INSERT INTO tmp_Raterules_(
			rateruleid ,
			Component,
			Origination ,
			TimezonesID ,
			CountryID ,
			AccessType,
			Prefix ,
			City,
			Tariff,
			`Order` ,
			RowNo
		)
			SELECT
				rateruleid,
				Component,
				OriginationDescription as Origination ,
				TimeOfDay as TimezonesID,
				IF(CountryID ='',NULL,CountryID) as CountryID,
				AccessType,
				Prefix ,
				City,
				Tariff,
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
			CountryID ,
			AccessType,
			Prefix ,
			City,
			Tariff,
			RowNo )
			SELECT

			CalculatedRateID ,
			Component ,
			Origination ,
			TimezonesID ,
			RateLessThen	,
			ChangeRateTo ,
			IF(CountryID ='',NULL,CountryID) as CountryID,
			AccessType,
			Prefix ,
			City,
			Tariff,
			@row_num := @row_num+1 AS RowID
			FROM tblRateGeneratorCalculatedRate,(SELECT @row_num := 0) x
			WHERE RateGeneratorId = @p_RateGeneratorId
			ORDER BY CalculatedRateID ASC;


				set @v_ApprovedStatus = 1;

				set @v_DIDType = 2;

			  	set @v_AppliedToCustomer = 1;
				set @v_AppliedToVendor = 2;
				set @v_AppliedToReseller = 3;





			SET @p_Calls	 							 = @v_Calls;
			SET @p_Minutes	 							 = @v_Minutes;
			SET @v_PeakTimeZoneID	 				 = @v_TimezonesID;
			SET @p_PeakTimeZonePercentage	 		 = @v_TimezonesPercentage;
			SET @p_MobileOriginationPercentage	 = @v_OriginationPercentage ;

			SET @p_Prefix = TRIM(LEADING '0' FROM @p_Prefix);


			IF @p_Calls = 0 AND @p_Minutes = 0 THEN



				select count(UsageDetailID)  into @p_Calls

				from speakintelligentCDR.tblUsageDetails  d

				inner join speakintelligentCDR.tblUsageHeader h on d.UsageHeaderID = h.UsageHeaderID

				inner join speakintelligentRM.tblCountry c  on   d.area_prefix  like concat(c.Prefix,'%')

				where CompanyID = @v_CompanyId_ AND StartDate >= @v_StartDate_ AND StartDate <= @v_EndDate_ and d.is_inbound = 1

				AND (@p_CountryID = '' OR  c.CountryID = @p_CountryID )

				AND (@p_AccessType = '' OR d.NoType = @p_AccessType)

				AND (@p_City = '' OR d.City  = @p_City)

				AND (@p_Tariff = '' OR d.Tariff  = @p_Tariff)

				AND ( @p_Prefix = '' OR ( d.area_prefix   = concat(c.Prefix,  @p_Prefix )  ) );



				insert into tmp_timezone_minutes (TimezonesID, minutes)

				select TimezonesID  , (sum(billed_duration) / 60) as minutes

				from speakintelligentCDR.tblUsageDetails  d

				inner join speakintelligentCDR.tblUsageHeader h on d.UsageHeaderID = h.UsageHeaderID

				inner join speakintelligentRM.tblCountry c  on   d.area_prefix  like concat(c.Prefix,'%')

				where CompanyID = @v_CompanyId_ AND StartDate >= @v_StartDate_ AND StartDate <= @v_EndDate_ and d.is_inbound = 1 and TimezonesID is not null

				AND (@p_CountryID = '' OR  c.CountryID = @p_CountryID )

				AND (@p_AccessType = '' OR d.NoType = @p_AccessType)

				AND (@p_City = '' OR d.City  = @p_City)

				AND (@p_Tariff = '' OR d.Tariff  = @p_Tariff)

				AND ( @p_Prefix = '' OR ( d.area_prefix   = concat(c.Prefix,  @p_Prefix )  ) )

				group by TimezonesID;


				insert into tmp_origination_minutes ( OriginationCode, minutes )

				select CLIPrefix  , (sum(billed_duration) / 60) as minutes

				from speakintelligentCDR.tblUsageDetails  d

				inner join speakintelligentCDR.tblUsageHeader h on d.UsageHeaderID = h.UsageHeaderID

				inner join speakintelligentRM.tblCountry c  on   d.area_prefix  like concat(c.Prefix,'%')

				where CompanyID = @v_CompanyId_ AND StartDate >= @v_StartDate_ AND StartDate <= @v_EndDate_ and d.is_inbound = 1 and CLIPrefix is not null

				AND (@p_CountryID = '' OR  c.CountryID = @p_CountryID )

				AND (@p_AccessType = '' OR d.NoType = @p_AccessType)

				AND (@p_City = '' OR d.City  = @p_City)

				AND (@p_Tariff = '' OR d.Tariff  = @p_Tariff)

				AND ( @p_Prefix = '' OR ( d.area_prefix   = concat(c.Prefix,  @p_Prefix )  ) )

				group by CLIPrefix;



			ELSE




				SET @p_MobileOrigination				 = @v_Origination ;
				SET @v_PeakTimeZoneMinutes				 =  ( (@p_Minutes/ 100) * @p_PeakTimeZonePercentage ) 	;
				SET @v_MinutesFromMobileOrigination  =  ( (@p_Minutes/ 100) * @p_MobileOriginationPercentage ) 	;



				insert into tmp_timezones (TimezonesID) select TimezonesID from 	tblTimezones;

				insert into tmp_timezone_minutes (TimezonesID, minutes) select @v_TimezonesID, @v_PeakTimeZoneMinutes as minutes;

				SET @v_RemainingTimezones = (select count(*) from tmp_timezones where TimezonesID != @v_TimezonesID);
				SET @v_RemainingMinutes = (@p_Minutes - @v_PeakTimeZoneMinutes) / @v_RemainingTimezones ;

				SET @v_pointer_ = 1;
				SET @v_rowCount_ = ( SELECT COUNT(*) FROM tmp_timezones );

				WHILE @v_pointer_ <= @v_rowCount_
				DO

						SET @v_NewTimezonesID = (SELECT TimezonesID FROM tmp_timezones WHERE ID = @v_pointer_ AND TimezonesID != @v_TimezonesID );

						if @v_NewTimezonesID > 0  THEN

							insert into tmp_timezone_minutes (TimezonesID, minutes)  select @v_NewTimezonesID, @v_RemainingMinutes as minutes;

						END IF ;

					SET @v_pointer_ = @v_pointer_ + 1;

				END WHILE;




				insert into tmp_origination_minutes ( OriginationCode, minutes )
				select @p_MobileOrigination  , @v_MinutesFromMobileOrigination ;


		END IF;

		SET @v_days =    TIMESTAMPDIFF(DAY, (SELECT @v_StartDate_), (SELECT @v_EndDate_)) + 1 ;
		SET @v_period1 =      IF(MONTH((SELECT @v_StartDate_)) = MONTH((SELECT @v_EndDate_)), 0, (TIMESTAMPDIFF(DAY, (SELECT @v_StartDate_), LAST_DAY((SELECT @v_StartDate_)) + INTERVAL 1 DAY)) / DAY(LAST_DAY((SELECT @v_StartDate_))));
		SET @v_period2 =      TIMESTAMPDIFF(MONTH, LAST_DAY((SELECT @v_StartDate_)) + INTERVAL 1 DAY, LAST_DAY((SELECT @v_EndDate_))) ;
		SET @v_period3 =      IF(MONTH((SELECT @v_StartDate_)) = MONTH((SELECT @v_EndDate_)), (SELECT @v_days), DAY((SELECT @v_EndDate_))) / DAY(LAST_DAY((SELECT @v_EndDate_)));
		SET @v_months =     (SELECT @v_period1) + (SELECT @v_period2) + (SELECT @v_period3);


		insert into tmp_timezone_minutes_2 (TimezonesID, minutes) select TimezonesID, minutes from tmp_timezone_minutes;
		insert into tmp_timezone_minutes_3 (TimezonesID, minutes) select TimezonesID, minutes from tmp_timezone_minutes;








										insert into tmp_table_without_origination (
																RateTableID,
																TimezonesID,
																TimezoneTitle,
																CodeDeckId,
																CountryID,
																AccessType,
																CountryPrefix,
																City,
																Tariff,
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

																Total1,
																Total

																)

	select
								rt.RateTableID,
								drtr.TimezonesID,
								t.Title as TimezoneTitle,
								rt.CodeDeckId,
								c.CountryID,
								drtr.AccessType,
								c.Prefix,
								drtr.City,
								drtr.Tariff,
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

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_  )
									* (drtr.OneOffCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = OneOffCostCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.OneOffCost
								ELSE
									(

										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.OneOffCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as OneOffCost,
								@MonthlyCost := ( ( CASE WHEN ( MonthlyCostCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = MonthlyCostCurrency THEN
									drtr.MonthlyCost
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_  )
									* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = MonthlyCostCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.MonthlyCost
								ELSE
									(

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

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.CostPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CostPerCallCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.CostPerCall
								ELSE
									(

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

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.CostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CostPerMinuteCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.CostPerMinute
								ELSE
									(

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

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.SurchargePerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = SurchargePerCallCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.SurchargePerCall
								ELSE
									(

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

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.SurchargePerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = SurchargePerMinuteCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.SurchargePerMinute
								ELSE
									(

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

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.OutpaymentPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = OutpaymentPerCallCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.OutpaymentPerCall
								ELSE
									(

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

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.OutpaymentPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = OutpaymentPerMinuteCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.OutpaymentPerMinute
								ELSE
									(

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

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.Surcharges  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = SurchargesCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.Surcharges
								ELSE
									(

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

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.Chargeback  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = ChargebackCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.Chargeback
								ELSE
									(

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

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.CollectionCostAmount  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CollectionCostAmountCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.CollectionCostAmount
								ELSE
									(

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

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.CollectionCostPercentage  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CollectionCostAmountCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.CollectionCostPercentage
								ELSE
									(

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

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.RegistrationCostPerNumber  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = RegistrationCostPerNumberCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.RegistrationCostPerNumber
								ELSE
									(

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
								@Total1 + @Total1 * (select sum( IF(FlatStatus = 0 ,(Amount/100), Amount ) * IFNULL(@CollectionCostPercentage,0))  from tblTaxRate where CompanyID = @v_CompanyId_ AND TaxType in  (1,2)   )
									) as Total



				from tblRateTableDIDRate  drtr
				inner join tblRateTable  rt on rt.RateTableId = drtr.RateTableId
				 inner join tblVendorConnection vc on vc.RateTableID = rt.RateTableId and vc.DIDCategoryID = rt.DIDCategoryID and vc.CompanyID = rt.CompanyId  and vc.Active=1
				inner join tblAccount a on vc.AccountId = a.AccountID and rt.CompanyId = a.CompanyId
				inner join tblRate r on drtr.RateID = r.RateID and r.CompanyID = vc.CompanyID
				left join tblRate r2 on drtr.OriginationRateID = r2.RateID and r.CompanyID = vc.CompanyID
		 		inner join tblCountry c on c.CountryID = r.CountryID


				AND ( @p_CountryID = '' OR  c.CountryID = @p_CountryID )
				AND ( @p_City = '' OR drtr.City  = @p_City )
				AND ( @p_Tariff = '' OR drtr.Tariff  = @p_Tariff )
				AND ( @p_Prefix = '' OR (r.Code  = concat(c.Prefix ,@p_Prefix) ) )
				AND ( @p_AccessType = '' OR drtr.AccessType = @p_AccessType )


				inner join tblTimezones t on t.TimezonesID =  drtr.TimezonesID
				left join tmp_origination_minutes tom  on r2.Code = tom.OriginationCode

				where

				rt.CompanyId =  @v_CompanyId_

				and vc.DIDCategoryID = @v_DIDCategoryID_

				and drtr.ApprovedStatus = @v_ApprovedStatus

				and rt.Type = @v_DIDType

			  	and rt.AppliedTo = @v_AppliedToVendor

				and (
					 (p_EffectiveRate = 'now' AND EffectiveDate <= NOW())
					 OR
					 (p_EffectiveRate = 'future' AND EffectiveDate > NOW()  )
					 OR
					 (	 p_EffectiveRate = 'effective' AND EffectiveDate <= @p_EffectiveDate
							 AND ( drtr.EndDate IS NULL OR (drtr.EndDate > DATE(@p_EffectiveDate)) )
					 )
				)

			;




										insert into tmp_table_with_origination (
																RateTableID,
																TimezonesID,
																TimezoneTitle,
																CodeDeckId,
																CountryID,
																AccessType,
																CountryPrefix,
																City,
																Tariff,
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

																Total1,
																Total
																)

	select
								rt.RateTableID,
								drtr.TimezonesID,
								t.Title as TimezoneTitle,
								rt.CodeDeckId,
								c.CountryID,
								drtr.AccessType,
								c.Prefix,
								drtr.City,
								drtr.Tariff,
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

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_  )
									* (drtr.OneOffCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = OneOffCostCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.OneOffCost
								ELSE
									(

										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.OneOffCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as OneOffCost,
								@MonthlyCost := ( ( CASE WHEN ( MonthlyCostCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = MonthlyCostCurrency THEN
									drtr.MonthlyCost
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_  )
									* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = MonthlyCostCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.MonthlyCost
								ELSE
									(

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

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.CostPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CostPerCallCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.CostPerCall
								ELSE
									(

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

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.CostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CostPerMinuteCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.CostPerMinute
								ELSE
									(

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

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.SurchargePerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = SurchargePerCallCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.SurchargePerCall
								ELSE
									(

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

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.SurchargePerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = SurchargePerMinuteCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.SurchargePerMinute
								ELSE
									(

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

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.OutpaymentPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = OutpaymentPerCallCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.OutpaymentPerCall
								ELSE
									(

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

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.OutpaymentPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = OutpaymentPerMinuteCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.OutpaymentPerMinute
								ELSE
									(

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

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.Surcharges  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = SurchargesCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.Surcharges
								ELSE
									(

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

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.Chargeback  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = ChargebackCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.Chargeback
								ELSE
									(

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

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.CollectionCostAmount  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CollectionCostAmountCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.CollectionCostAmount
								ELSE
									(

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

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.CollectionCostPercentage  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = CollectionCostAmountCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.CollectionCostPercentage
								ELSE
									(

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

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.RegistrationCostPerNumber  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = RegistrationCostPerNumberCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.RegistrationCostPerNumber
								ELSE
									(

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
								@Total1 + @Total1 * (select sum( IF(FlatStatus = 0 ,(Amount/100), Amount ) * IFNULL(@CollectionCostPercentage,0))  from tblTaxRate where CompanyID = @v_CompanyId_ AND TaxType in  (1,2)   )
									) as Total


				from tblRateTableDIDRate  drtr
				inner join tblRateTable  rt on rt.RateTableId = drtr.RateTableId
				 inner join tblVendorConnection vc on vc.RateTableID = rt.RateTableId and vc.DIDCategoryID = rt.DIDCategoryID and vc.CompanyID = rt.CompanyId  and vc.Active=1
				inner join tblAccount a on vc.AccountId = a.AccountID and rt.CompanyId = a.CompanyId
				inner join tblRate r on drtr.RateID = r.RateID and r.CompanyID = vc.CompanyID
				left join tblRate r2 on drtr.OriginationRateID = r2.RateID and r.CompanyID = vc.CompanyID
		 		inner join tblCountry c on c.CountryID = r.CountryID


				AND ( @p_CountryID = '' OR  c.CountryID = @p_CountryID )
				AND ( @p_City = '' OR drtr.City  = @p_City )
				AND ( @p_Tariff = '' OR drtr.Tariff  = @p_Tariff )
				AND ( @p_Prefix = '' OR (r.Code  = concat(c.Prefix ,@p_Prefix) ) )
				AND ( @p_AccessType = '' OR drtr.AccessType = @p_AccessType )


				inner join tblTimezones t on t.TimezonesID =  drtr.TimezonesID
				inner join tmp_origination_minutes tom  on r2.Code = tom.OriginationCode
				where

				rt.CompanyId =  @v_CompanyId_

				and vc.DIDCategoryID = @v_DIDCategoryID_

				and drtr.ApprovedStatus = @v_ApprovedStatus

				and rt.Type = @v_DIDType

			  	and rt.AppliedTo = @v_AppliedToVendor

				and (
					 (p_EffectiveRate = 'now' AND EffectiveDate <= NOW())
					 OR
					 (p_EffectiveRate = 'future' AND EffectiveDate > NOW()  )
					 OR
					 (	 p_EffectiveRate = 'effective' AND EffectiveDate <= @p_EffectiveDate
							 AND ( drtr.EndDate IS NULL OR (drtr.EndDate > DATE(@p_EffectiveDate)) )
					 )
				)

			;


			delete t1 from tmp_table_without_origination t1 inner join tmp_table_with_origination t2 on t1.VendorID = t2.VendorID and t1.TimezonesID = t2.TimezonesID and t1.Code = t2.Code;

				insert into tmp_tblRateTableDIDRate (
										RateTableID,
										TimezonesID,
										TimezoneTitle,
										CodeDeckId,
										CountryID,
										AccessType,
										CountryPrefix,
										City,
										Tariff,
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
										RateTableID,
										TimezonesID,
										TimezoneTitle,
										CodeDeckId,
										CountryID,
										AccessType,
										CountryPrefix,
										City,
										Tariff,
										Code,
										OriginationCode,
										VendorID,
										VendorName,
										EndDate,
										OneOffCost,
										(MonthlyCost) as MonthlyCost,
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
												CountryID,
												AccessType,
												CountryPrefix,
												City,
												Tariff,
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
												from
												tmp_table_without_origination

												union all

												select
												RateTableID,
												TimezonesID,
												TimezoneTitle,
												CodeDeckId,
												CountryID,
												AccessType,
												CountryPrefix,
												City,
												Tariff,
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
												from
												tmp_table_with_origination

										) tmp
										where Total is not null;





			insert into tmp_vendor_position (VendorID , vPosition,Total)
			select
			VendorID , vPosition,Total
			from (

				SELECT
					distinct
					v.VendorID,
					v.Total,
					@rank := ( CASE WHEN(@prev_VendorID != v.VendorID and @prev_Total <= v.Total AND (@v_percentageRate_ = 0 OR  (IFNULL(@prev_Total,0) != 0 and  @v_percentageRate_ > 0 AND ROUND(((v.Total - @prev_Total) /( @prev_Total * 100)),2) > @v_percentageRate_) )   )
						THEN  @rank + 1
										 ELSE 1
										 END
					) AS vPosition,
					@prev_VendorID := v.VendorID,
					@prev_Total := v.Total

				FROM (

--						select distinct  VendorID , sum(Total) as Total from tmp_tblRateTableDIDRate group by VendorID
						select distinct  VendorID , sum(Total) as Total from tmp_tblRateTableDIDRate group by VendorID order by Total
					) v
					, (SELECT  @prev_VendorID := NUll ,  @rank := 0 ,  @prev_Total := 0 ) f

				order by v.Total,v.VendorID asc
			) tmp
			where vPosition <= @v_RatePosition_;

			SET @v_SelectedVendor = ( select VendorID from tmp_vendor_position where vPosition <= @v_RatePosition_ order by vPosition , Total  limit 1 );







			insert into tmp_SelectedVendortblRateTableDIDRate
			(
					RateTableID,
					TimezonesID,
					TimezoneTitle,
					Code,
					OriginationCode,
					VendorID,
					CodeDeckId,
					CountryID,
					AccessType,
					CountryPrefix,
					City,
					Tariff,
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

			)
			select
					RateTableID,
					TimezonesID,
					TimezoneTitle,
					Code,
					OriginationCode,
					VendorID,
					CodeDeckId,
					CountryID,
					AccessType,
					CountryPrefix,
					City,
					Tariff,
					VendorName,
					EndDate,

					IFNULL(OneOffCost,0),
					IFNULL(MonthlyCost,0),
					IFNULL(CostPerCall,0),
					IFNULL(CostPerMinute,0),
					IFNULL(SurchargePerCall,0),
					IFNULL(SurchargePerMinute,0),
					IFNULL(OutpaymentPerCall,0),
					IFNULL(OutpaymentPerMinute,0),
					IFNULL(Surcharges,0),
					IFNULL(Chargeback,0),
					IFNULL(CollectionCostAmount,0),
					IFNULL(CollectionCostPercentage,0),
					IFNULL(RegistrationCostPerNumber,0),

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





			DROP TEMPORARY TABLE IF EXISTS tmp_MergeComponents;
			CREATE TEMPORARY TABLE tmp_MergeComponents(
				ID int auto_increment,
				Component TEXT  ,
				Origination TEXT  ,
				ToOrigination TEXT  ,
				TimezonesID INT(11)   ,
				ToTimezonesID INT(11)   ,
				Action CHAR(4)    ,
				MergeTo TEXT  ,
				FromCountryID INT(11)   ,
				ToCountryID INT(11)   ,
				FromAccessType VARCHAR(50)    ,
				ToAccessType VARCHAR(50)    ,
				FromPrefix VARCHAR(50)    ,
				ToPrefix VARCHAR(50)    ,
				FromCity VARCHAR(50)    ,
				FromTariff VARCHAR(50)    ,
				ToCity VARCHAR(50)    ,
				ToTariff VARCHAR(50)    ,
				primary key (ID)
			);

			insert into tmp_MergeComponents (
									Component,
									Origination,
									ToOrigination,
									TimezonesID,
									ToTimezonesID,
									Action,
									MergeTo,
									FromCountryID,
									ToCountryID,
									FromAccessType,
									ToAccessType,
									FromPrefix,
									ToPrefix,
									FromCity,
									FromTariff,
									ToCity,
									ToTariff

			)
			select
									Component,
									Origination,
									ToOrigination,
									TimezonesID,
									ToTimezonesID,
									Action,
									MergeTo,
									IF(FromCountryID ='',NULL,FromCountryID) as FromCountryID,
									IF(ToCountryID ='',NULL,ToCountryID) as ToCountryID,
									FromAccessType,
									ToAccessType,
									FromPrefix,
									ToPrefix,
									FromCity,
									FromTariff,
									ToCity,
									ToTariff

			from tblRateGeneratorCostComponent
			where RateGeneratorId = @p_RateGeneratorId
			order by CostComponentID asc;





	 	SET @v_pointer_ = 1;
		SET @v_rowCount_ = ( SELECT COUNT(*) FROM tmp_MergeComponents );

		WHILE @v_pointer_ <= @v_rowCount_
		DO


				SELECT
						Component,
						Origination,
						ToOrigination,
						TimezonesID,
						ToTimezonesID,
						Action,
						MergeTo,
						FromCountryID,
						ToCountryID,
						FromAccessType,
						ToAccessType,
						FromPrefix,
						ToPrefix,
						FromCity,
						FromTariff,
						ToCity,
						ToTariff

				INTO

						@v_Component,
						@v_Origination,
						@v_ToOrigination,
						@v_TimezonesID,
						@v_ToTimezonesID,
						@v_Action,
						@v_MergeTo,
						@v_FromCountryID,
						@v_ToCountryID,
						@v_FromAccessType,
						@v_ToAccessType,
						@v_FromPrefix,
						@v_ToPrefix,
						@v_FromCity,
						@v_FromTariff,
						@v_ToCity,
						@v_ToTariff

				FROM tmp_MergeComponents WHERE ID = @v_pointer_;

				IF @v_Action = 'sum' THEN

					SET @ResultField = concat('(' ,  REPLACE(@v_Component,',',' + ') , ') ');

				ELSE

					SET @ResultField = concat('GREATEST(' ,  @v_Component, ') ');

				END IF;

				SET @stm1 = CONCAT('
						update tmp_SelectedVendortblRateTableDIDRate srt
						inner join (

								select

									TimezonesID,
									Code,
									OriginationCode,
									', @ResultField , ' as componentValue

									from tmp_tblRateTableDIDRate

								where
									VendorID = @v_SelectedVendor

								AND (  @v_TimezonesID = "" OR  TimezonesID = @v_TimezonesID)
								AND (  @v_Origination = "" OR  OriginationCode = @v_Origination)
								AND (  @v_FromCountryID =  ''  OR CountryID = 	@v_FromCountryID )
								AND (  @v_FromAccessType =  ''  OR AccessType = 	@v_FromAccessType )
								AND (  @v_FromPrefix =  '' OR Code = 	concat(CountryPrefix ,@v_FromPrefix) )
								AND (  @v_FromCity =  '' OR City = 	@v_FromCity )
								AND (  @v_FromTariff =  '' OR Tariff = 	@v_FromTariff )




						) tmp on
								tmp.Code = srt.Code
								AND (  @v_ToTimezonesID = "" OR  srt.TimezonesID = @v_ToTimezonesID)
								AND (  @v_ToOrigination = "" OR  srt.OriginationCode = @v_ToOrigination)
								AND (  @v_ToCountryID =  ''  OR srt.CountryID = 	@v_ToCountryID )
								AND (  @v_ToAccessType =  ''  OR srt.AccessType = 	@v_ToAccessType )
								AND (  @v_ToPrefix =  '' OR Code = 	concat(srt.CountryPrefix ,@v_ToPrefix) )
								AND (  @v_ToCity =  '' OR srt.City = 	@v_ToCity )
								AND (  @v_ToTariff =  '' OR srt.Tariff = 	@v_ToTariff )
						set

						' , 'new_', @v_MergeTo , ' = tmp.componentValue;
				');
				PREPARE stm1 FROM @stm1;
				EXECUTE stm1;


				IF ROW_COUNT()  = 0 THEN



						insert into tmp_SelectedVendortblRateTableDIDRate
						(
								TimezonesID,
								TimezoneTitle,
								Code,
								OriginationCode,
								VendorID,
								CodeDeckId,
								CountryID,
								AccessType,
								CountryPrefix,

								City,
								Tariff,
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
								IF(@v_ToTimezonesID = '',TimezonesID,@v_ToTimezonesID) as TimezonesID,
								TimezoneTitle,
								IF(@v_ToPrefix = '', Code, concat(CountryPrefix ,@v_ToPrefix)) as Code,
								IF(@v_ToOrigination = '',OriginationCode,@v_ToOrigination) as OriginationCode,
								VendorID,
								CodeDeckId,
								IF(@v_ToCountryID = '',CountryID,@v_ToCountryID) as CountryID,
								IF(@v_ToAccessType = '',AccessType,@v_ToAccessType) as AccessType,
								CountryPrefix,
								IF(@v_ToCity = '',City,@v_ToCity) as City,
								IF(@v_ToTariff = '',Tariff,@v_ToTariff) as Tariff,
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
							AND (  @v_TimezonesID = "" OR  TimezonesID = @v_TimezonesID)
							AND (  @v_Origination = "" OR  OriginationCode = @v_Origination)
							AND (  @v_FromCountryID =  ''  OR CountryID = 	@v_FromCountryID )
							AND (  @v_FromAccessType =  ''  OR AccessType = 	@v_FromAccessType )
							AND (  @v_FromPrefix =  '' OR Code = 	concat(CountryPrefix ,@v_FromPrefix) )
							AND (  @v_FromCity =  '' OR City = 	@v_FromCity )
							AND (  @v_FromTariff =  '' OR Tariff = 	@v_FromTariff );



				END IF;

				DEALLOCATE PREPARE stm1;



			SET @v_pointer_ = @v_pointer_ + 1;

		END WHILE;





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





	 	SET @v_pointer_ = 1;
		SET @v_rowCount_ = ( SELECT COUNT(*) FROM tmp_Raterules_ );

		WHILE @v_pointer_ <= @v_rowCount_
		DO

			SET @v_rateRuleId_ = (SELECT rateruleid FROM tmp_Raterules_ rr WHERE rr.RowNo = @v_pointer_);


						update tmp_SelectedVendortblRateTableDIDRate rt
						inner join tmp_Raterules_ rr on rr.RowNo  = @v_pointer_
						and  rr.TimezonesID  = rt.TimezonesID
						and (rr.Origination = '' OR rr.Origination = rt.OriginationCode )
						AND (  rr.CountryID = ''  OR rt.CountryID = 	rr.CountryID )
						AND (  rr.AccessType = '' OR rt.AccessType = 	rr.AccessType )
						AND (  rr.Prefix = ''  OR rt.Code = 	concat(rt.CountryPrefix ,rr.Prefix) )
						AND (  rr.City = '' OR rt.City = 	rr.City )
						AND (  rr.Tariff = '' OR rt.Tariff = 	rr.Tariff )

						LEFT join tblRateRuleMargin rule_mgn1 on  rule_mgn1.RateRuleId = @v_rateRuleId_
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






	 	SET @v_pointer_ = 1;
		SET @v_rowCount_ = ( SELECT COUNT(*) FROM tmp_RateGeneratorCalculatedRate_ );

		WHILE @v_pointer_ <= @v_rowCount_
		DO





						update tmp_SelectedVendortblRateTableDIDRate rt
						inner join tmp_RateGeneratorCalculatedRate_ rr on
						rr.RowNo  = @v_pointer_  AND rr.TimezonesID  = rt.TimezonesID  and   (rr.Origination = '' OR rr.Origination = rt.OriginationCode )

						AND (  rr.CountryID = ''  OR rt.CountryID = 	rr.CountryID )
						AND (  rr.AccessType = ''  OR rt.AccessType = 	rr.AccessType )
						AND (  rr.Prefix = ''  OR rt.Code = 	concat(rt.CountryPrefix ,rr.Prefix) )
						AND (  rr.City = ''  OR rt.City = 	rr.City )
						AND (  rr.Tariff = ''  OR rt.Tariff = 	rr.Tariff )



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








		SET @v_SelectedRateTableID = ( select RateTableID from tmp_SelectedVendortblRateTableDIDRate limit 1 );

		SET @v_AffectedRecords_ = 0;

		START TRANSACTION;

		SET @v_RATE_STATUS_AWAITING  = 0;
		SET @v_RATE_STATUS_APPROVED  = 1;
		SET @v_RATE_STATUS_REJECTED  = 2;
		SET @v_RATE_STATUS_DELETE    = 3;

		IF p_RateTableId = -1
		THEN

			SET @v_codedeckid_ = ( select CodeDeckId from tmp_SelectedVendortblRateTableDIDRate limit 1 );

			INSERT INTO tblRateTable (Type, CompanyId, RateTableName, RateGeneratorID,DIDCategoryID, TrunkID, CodeDeckId,CurrencyID,Status, RoundChargedAmount,MinimumCallCharge,AppliedTo,Reseller,created_at,updated_at, CreatedBy,ModifiedBy)
			select  @v_DIDType as Type, @v_CompanyId_, p_rateTableName , @p_RateGeneratorId,DIDCategoryID, 0 as TrunkID,  CodeDeckId , CurrencyID, Status, RoundChargedAmount,MinimumCallCharge, @p_AppliedTo as AppliedTo, @p_Reseller as Reseller, now() ,now() ,p_ModifiedBy,p_ModifiedBy
			from tblRateTable where RateTableID = @v_SelectedRateTableID  limit 1;

			SET @p_RateTableId = LAST_INSERT_ID();

		ELSE

			SET @p_RateTableId = p_RateTableId;

				IF p_delete_exiting_rate = 1
				THEN


					IF (@v_RateApprovalProcess_ = 1 ) THEN




							INSERT INTO tblRateTableDIDRateAA (
														OriginationRateID,
														RateID,
														RateTableId,
														TimezonesID,
														EffectiveDate,
														EndDate,
														City,
														Tariff,
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
														created_at,
														updated_at,
														CreatedBy,
														ModifiedBy,
														ApprovedStatus,
														ApprovedBy,
														ApprovedDate,
														VendorID

							)
							SELECT
														OriginationRateID,
														RateID,
														RateTableId,
														TimezonesID,
														EffectiveDate,
														NOW() as EndDate,
														City,
														Tariff,
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
														created_at,
														updated_at,
														CreatedBy,
														ModifiedBy,
														@v_RATE_STATUS_DELETE as ApprovedStatus,
														ApprovedBy,
														ApprovedDate,
														VendorID
											FROM tblRateTableDIDRate
							WHERE
							RateTableId = @p_RateTableId;

						call prc_ArchiveOldRateTableDIDRateAA(@p_RateTableId, NULL,p_ModifiedBy);



				ELSE




						UPDATE
							tblRateTableDIDRate
						SET
							EndDate = NOW()
						WHERE
							RateTableId = @p_RateTableId;


						call prc_ArchiveOldRateTableDIDRate(@p_RateTableId, NULL,p_ModifiedBy);
				END IF;

			END IF;



								IF (@v_RateApprovalProcess_ = 1 ) THEN




							INSERT INTO tblRateTableDIDRateAA (
														OriginationRateID,
														RateID,
														RateTableId,
														TimezonesID,
														EffectiveDate,
														EndDate,
														City,
														Tariff,
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
														created_at,
														updated_at,
														CreatedBy,
														ModifiedBy,
														ApprovedStatus,
														ApprovedBy,
														ApprovedDate,
														VendorID

							)
							SELECT
														rtd.OriginationRateID,
														rtd.RateID,
														rtd.RateTableId,
														rtd.TimezonesID,
														rtd.EffectiveDate,
														NOW() as EndDate,
														rtd.City,
														rtd.Tariff,
														rtd.OneOffCost,
														rtd.MonthlyCost,
														rtd.CostPerCall,
														rtd.CostPerMinute,
														rtd.SurchargePerCall,
														rtd.SurchargePerMinute,
														rtd.OutpaymentPerCall,
														rtd.OutpaymentPerMinute,
														rtd.Surcharges,
														rtd.Chargeback,
														rtd.CollectionCostAmount,
														rtd.CollectionCostPercentage,
														rtd.RegistrationCostPerNumber,
														rtd.OneOffCostCurrency,
														rtd.MonthlyCostCurrency,
														rtd.CostPerCallCurrency,
														rtd.CostPerMinuteCurrency,
														rtd.SurchargePerCallCurrency,
														rtd.SurchargePerMinuteCurrency,
														rtd.OutpaymentPerCallCurrency,
														rtd.OutpaymentPerMinuteCurrency,
														rtd.SurchargesCurrency,
														rtd.ChargebackCurrency,
														rtd.CollectionCostAmountCurrency,
														rtd.RegistrationCostPerNumberCurrency,
														rtd.created_at,
														rtd.updated_at,
														rtd.CreatedBy,
														rtd.ModifiedBy,
														@v_RATE_STATUS_DELETE as ApprovedStatus,
														rtd.ApprovedBy,
														rtd.ApprovedDate,
														rtd.VendorID

											FROM tblRateTableDIDRate rtd
											INNER JOIN tblRateTable rt  on rt.RateTableID = rtd.RateTableID
											INNER JOIN tblRate r
												ON rtd.RateID  = r.RateID
											LEFT JOIN tblRate rr
												ON rtd.OriginationRateID  = rr.RateID
											inner join tmp_SelectedVendortblRateTableDIDRate drtr on
											drtr.Code = r.Code and drtr.OriginationCode = rr.Code
											and rtd.TimezonesID = drtr.TimezonesID and rtd.City = drtr.City and rtd.Tariff = drtr.Tariff and  r.CodeDeckId = rr.CodeDeckId  AND  r.CodeDeckId = drtr.CodeDeckId



											where
											rtd.RateTableID = @p_RateTableId and rtd.EffectiveDate = @p_EffectiveDate;

											call prc_ArchiveOldRateTableDIDRateAA(@p_RateTableId, NULL,p_ModifiedBy);


						ELSE



							update tblRateTableDIDRate rtd
							INNER JOIN tblRateTable rt  on rt.RateTableID = rtd.RateTableID
							INNER JOIN tblRate r
								ON rtd.RateID  = r.RateID
							LEFT JOIN tblRate rr
								ON rtd.OriginationRateID  = rr.RateID
							inner join tmp_SelectedVendortblRateTableDIDRate drtr on
							drtr.Code = r.Code and drtr.OriginationCode = rr.Code
							and rtd.TimezonesID = drtr.TimezonesID and rtd.City = drtr.City and rtd.Tariff = drtr.Tariff and  r.CodeDeckId = rr.CodeDeckId  AND  r.CodeDeckId = drtr.CodeDeckId

							SET rtd.EndDate = NOW()

							where
							rtd.RateTableID = @p_RateTableId and rtd.EffectiveDate = @p_EffectiveDate;

							call prc_ArchiveOldRateTableDIDRate(@p_RateTableId, NULL,p_ModifiedBy);


					END IF;

					SET @v_AffectedRecords_ = @v_AffectedRecords_ + FOUND_ROWS();


		END IF;


		IF (@v_RateApprovalProcess_ = 1 ) THEN





					INSERT INTO tblRateTableDIDRateAA (
									VendorID,
									RateTableId,
									TimezonesID,
									OriginationRateID,
									RateId,
									City,
									Tariff,
									AccessType,
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
								IFNULL(rr.RateID,0) as OriginationRateID,
								r.RateId,
								drtr.City,
								drtr.Tariff,
								drtr.AccessType,


								CASE WHEN ( drtr.OneOffCostCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = drtr.OneOffCostCurrency THEN
								drtr.OneOffCost
								ELSE
								(

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  drtr.OneOffCostCurrency  and  CompanyID = @v_CompanyId_  )
								* (drtr.OneOffCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
								drtr.OneOffCost
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  rt.CurrencyID  and  CompanyID = @v_CompanyId_ )
									* (drtr.OneOffCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
								)
								END as OneOffCost,

								( CASE WHEN ( drtr.MonthlyCostCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = drtr.MonthlyCostCurrency THEN
								drtr.MonthlyCost
								ELSE
								(

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  drtr.MonthlyCostCurrency  and  CompanyID = @v_CompanyId_  )
								* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
								drtr.MonthlyCost
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID  and  CompanyID = @v_CompanyId_ )
									* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_  and  CompanyID = @v_CompanyId_ ))
								)
								END) as MonthlyCost,

								CASE WHEN ( drtr.CostPerCallCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = drtr.CostPerCallCurrency THEN
								drtr.CostPerCall
								ELSE
								(

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = drtr.CostPerCallCurrency and  CompanyID = @v_CompanyId_ )
								* (drtr.CostPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_   and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
								drtr.CostPerCall
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  rt.CurrencyID  and  CompanyID = @v_CompanyId_ )
									* (drtr.CostPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_  and  CompanyID = @v_CompanyId_ ))
								)
								END as CostPerCall,

								CASE WHEN ( drtr.CostPerMinuteCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = drtr.CostPerMinuteCurrency THEN
								drtr.CostPerMinute
								ELSE
								(

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  drtr.CostPerMinuteCurrency  and  CompanyID = @v_CompanyId_ )
								* (drtr.CostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
								drtr.CostPerMinute
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ )
									* (drtr.CostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_   and  CompanyID = @v_CompanyId_ ))
								)
								END as CostPerMinute,


								CASE WHEN ( drtr.SurchargePerCallCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = drtr.SurchargePerCallCurrency THEN
								drtr.SurchargePerCall
								ELSE
								(

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = drtr.SurchargePerCallCurrency and  CompanyID = @v_CompanyId_ )
								* (drtr.SurchargePerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_   and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
								drtr.SurchargePerCall
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  rt.CurrencyID  and  CompanyID = @v_CompanyId_ )
									* (drtr.SurchargePerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ ))
								)
								END as SurchargePerCall,


								CASE WHEN ( drtr.SurchargePerMinuteCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = drtr.SurchargePerMinuteCurrency THEN
								drtr.SurchargePerMinute
								ELSE
								(

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  drtr.SurchargePerMinuteCurrency  and  CompanyID = @v_CompanyId_ )
								* (drtr.SurchargePerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
								drtr.SurchargePerMinute
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  rt.CurrencyID and  CompanyID = @v_CompanyId_ )
									* (drtr.SurchargePerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
								)
								END as SurchargePerMinute,

								CASE WHEN ( drtr.OutpaymentPerCallCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = drtr.OutpaymentPerCallCurrency THEN
								drtr.OutpaymentPerCall
								ELSE
								(

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = drtr.OutpaymentPerCallCurrency and  CompanyID = @v_CompanyId_ )
								* (drtr.OutpaymentPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
								drtr.OutpaymentPerCall
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ )
									* (drtr.OutpaymentPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ ))
								)
								END as OutpaymentPerCall,

								CASE WHEN ( drtr.OutpaymentPerMinuteCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = drtr.OutpaymentPerMinuteCurrency THEN
								drtr.OutpaymentPerMinute
								ELSE
								(

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = drtr.OutpaymentPerMinuteCurrency and  CompanyID = @v_CompanyId_ )
								* (drtr.OutpaymentPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
								drtr.OutpaymentPerMinute
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID  and  CompanyID = @v_CompanyId_ )
									* (drtr.OutpaymentPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =   @v_CurrencyID_  and  CompanyID = @v_CompanyId_ ))
								)
								END as OutpaymentPerMinute,

								CASE WHEN ( drtr.SurchargesCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = drtr.SurchargesCurrency THEN
								drtr.Surcharges
								ELSE
								(

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = drtr.SurchargesCurrency and  CompanyID = @v_CompanyId_ )
								* (drtr.Surcharges  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_   and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
								drtr.Surcharges
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  rt.CurrencyID and  CompanyID = @v_CompanyId_ )
									* (drtr.Surcharges  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
								)
								END as Surcharges,

								 CASE WHEN ( drtr.ChargebackCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = drtr.ChargebackCurrency THEN
								drtr.Chargeback
								ELSE
								(

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = drtr.ChargebackCurrency and  CompanyID = @v_CompanyId_ )
								* (drtr.Chargeback  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =   @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
								drtr.Chargeback
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ )
									* (drtr.Chargeback  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ ))
								)
								END as Chargeback,

								CASE WHEN ( drtr.CollectionCostAmountCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = drtr.CollectionCostAmountCurrency THEN
								drtr.CollectionCostAmount
								ELSE
								(

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  drtr.CollectionCostAmountCurrency    and  CompanyID = @v_CompanyId_ )
								* (drtr.CollectionCostAmount  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
								drtr.CollectionCostAmount
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID  and  CompanyID = @v_CompanyId_ )
									* (drtr.CollectionCostAmount  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
								)
								END as CollectionCostAmount,


								CASE WHEN ( drtr.CollectionCostAmountCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = drtr.CollectionCostAmountCurrency THEN
								drtr.CollectionCostPercentage
								ELSE
								(

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = drtr.CollectionCostAmountCurrency and  CompanyID = @v_CompanyId_ )
								* (drtr.CollectionCostPercentage  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
								drtr.CollectionCostPercentage
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ )
									* (drtr.CollectionCostPercentage  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ ))
								)
								END as CollectionCostPercentage,

								CASE WHEN ( drtr.RegistrationCostPerNumberCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = drtr.RegistrationCostPerNumberCurrency THEN
								drtr.RegistrationCostPerNumber
								ELSE
								(

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =   drtr.RegistrationCostPerNumberCurrency  and  CompanyID = @v_CompanyId_ )
								* (drtr.RegistrationCostPerNumber  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
								drtr.RegistrationCostPerNumber
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ )
									* (drtr.RegistrationCostPerNumber  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_   and  CompanyID = @v_CompanyId_ ))
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
								date(drtr.EndDate) as EndDate,
								@v_RateApprovalProcess_ as ApprovedStatus,


									now() as  created_at ,
									now() as updated_at ,
									p_ModifiedBy as CreatedBy ,
									p_ModifiedBy as ModifiedBy



								from tmp_SelectedVendortblRateTableDIDRate drtr
								inner join tblRateTable  rt on rt.RateTableId = drtr.RateTableId
								INNER JOIN tblRate r ON drtr.Code = r.Code and r.CodeDeckId = drtr.CodeDeckId
								LEFT JOIN tblRate rr ON drtr.OriginationCode = rr.Code and r.CodeDeckId = rr.CodeDeckId
								LEFT join tblRateTableDIDRate rtd  on rtd.RateID  = r.RateID and rtd.OriginationRateID  = rr.RateID
								and  rtd.TimezonesID = drtr.TimezonesID and rtd.City = drtr.City and rtd.Tariff = drtr.Tariff
								and rtd.RateTableID = @p_RateTableId
								and rtd.EffectiveDate = @p_EffectiveDate
								WHERE rtd.RateTableDIDRateID is null;

		ELSE


				INSERT INTO tblRateTableDIDRate (
									VendorID,
									RateTableId,
									TimezonesID,
									OriginationRateID,
									RateId,
									City,
									Tariff,
									AccessType,
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
								IFNULL(rr.RateID,0) as OriginationRateID,
								r.RateId,
								drtr.City,
								drtr.Tariff,
								drtr.AccessType,


								CASE WHEN ( drtr.OneOffCostCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = drtr.OneOffCostCurrency THEN
								drtr.OneOffCost
								ELSE
								(

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  drtr.OneOffCostCurrency  and  CompanyID = @v_CompanyId_  )
								* (drtr.OneOffCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
								drtr.OneOffCost
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  rt.CurrencyID  and  CompanyID = @v_CompanyId_ )
									* (drtr.OneOffCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
								)
								END as OneOffCost,

								( CASE WHEN ( drtr.MonthlyCostCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = drtr.MonthlyCostCurrency THEN
								drtr.MonthlyCost
								ELSE
								(

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  drtr.MonthlyCostCurrency  and  CompanyID = @v_CompanyId_  )
								* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
								drtr.MonthlyCost
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID  and  CompanyID = @v_CompanyId_ )
									* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_  and  CompanyID = @v_CompanyId_ ))
								)
								END) as MonthlyCost,

								CASE WHEN ( drtr.CostPerCallCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = drtr.CostPerCallCurrency THEN
								drtr.CostPerCall
								ELSE
								(

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = drtr.CostPerCallCurrency and  CompanyID = @v_CompanyId_ )
								* (drtr.CostPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_   and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
								drtr.CostPerCall
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  rt.CurrencyID  and  CompanyID = @v_CompanyId_ )
									* (drtr.CostPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_  and  CompanyID = @v_CompanyId_ ))
								)
								END as CostPerCall,

								CASE WHEN ( drtr.CostPerMinuteCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = drtr.CostPerMinuteCurrency THEN
								drtr.CostPerMinute
								ELSE
								(

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  drtr.CostPerMinuteCurrency  and  CompanyID = @v_CompanyId_ )
								* (drtr.CostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
								drtr.CostPerMinute
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ )
									* (drtr.CostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_   and  CompanyID = @v_CompanyId_ ))
								)
								END as CostPerMinute,


								CASE WHEN ( drtr.SurchargePerCallCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = drtr.SurchargePerCallCurrency THEN
								drtr.SurchargePerCall
								ELSE
								(

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = drtr.SurchargePerCallCurrency and  CompanyID = @v_CompanyId_ )
								* (drtr.SurchargePerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_   and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
								drtr.SurchargePerCall
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  rt.CurrencyID  and  CompanyID = @v_CompanyId_ )
									* (drtr.SurchargePerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ ))
								)
								END as SurchargePerCall,


								CASE WHEN ( drtr.SurchargePerMinuteCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = drtr.SurchargePerMinuteCurrency THEN
								drtr.SurchargePerMinute
								ELSE
								(

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  drtr.SurchargePerMinuteCurrency  and  CompanyID = @v_CompanyId_ )
								* (drtr.SurchargePerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
								drtr.SurchargePerMinute
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  rt.CurrencyID and  CompanyID = @v_CompanyId_ )
									* (drtr.SurchargePerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
								)
								END as SurchargePerMinute,

								CASE WHEN ( drtr.OutpaymentPerCallCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = drtr.OutpaymentPerCallCurrency THEN
								drtr.OutpaymentPerCall
								ELSE
								(

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = drtr.OutpaymentPerCallCurrency and  CompanyID = @v_CompanyId_ )
								* (drtr.OutpaymentPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
								drtr.OutpaymentPerCall
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ )
									* (drtr.OutpaymentPerCall  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ ))
								)
								END as OutpaymentPerCall,

								CASE WHEN ( drtr.OutpaymentPerMinuteCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = drtr.OutpaymentPerMinuteCurrency THEN
								drtr.OutpaymentPerMinute
								ELSE
								(

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = drtr.OutpaymentPerMinuteCurrency and  CompanyID = @v_CompanyId_ )
								* (drtr.OutpaymentPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
								drtr.OutpaymentPerMinute
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID  and  CompanyID = @v_CompanyId_ )
									* (drtr.OutpaymentPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =   @v_CurrencyID_  and  CompanyID = @v_CompanyId_ ))
								)
								END as OutpaymentPerMinute,

								CASE WHEN ( drtr.SurchargesCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = drtr.SurchargesCurrency THEN
								drtr.Surcharges
								ELSE
								(

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = drtr.SurchargesCurrency and  CompanyID = @v_CompanyId_ )
								* (drtr.Surcharges  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_   and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
								drtr.Surcharges
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  rt.CurrencyID and  CompanyID = @v_CompanyId_ )
									* (drtr.Surcharges  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
								)
								END as Surcharges,

								 CASE WHEN ( drtr.ChargebackCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = drtr.ChargebackCurrency THEN
								drtr.Chargeback
								ELSE
								(

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = drtr.ChargebackCurrency and  CompanyID = @v_CompanyId_ )
								* (drtr.Chargeback  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =   @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
								drtr.Chargeback
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ )
									* (drtr.Chargeback  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ ))
								)
								END as Chargeback,

								CASE WHEN ( drtr.CollectionCostAmountCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = drtr.CollectionCostAmountCurrency THEN
								drtr.CollectionCostAmount
								ELSE
								(

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  drtr.CollectionCostAmountCurrency    and  CompanyID = @v_CompanyId_ )
								* (drtr.CollectionCostAmount  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
								drtr.CollectionCostAmount
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID  and  CompanyID = @v_CompanyId_ )
									* (drtr.CollectionCostAmount  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
								)
								END as CollectionCostAmount,


								CASE WHEN ( drtr.CollectionCostAmountCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = drtr.CollectionCostAmountCurrency THEN
								drtr.CollectionCostPercentage
								ELSE
								(

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = drtr.CollectionCostAmountCurrency and  CompanyID = @v_CompanyId_ )
								* (drtr.CollectionCostPercentage  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
								drtr.CollectionCostPercentage
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ )
									* (drtr.CollectionCostPercentage  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ ))
								)
								END as CollectionCostPercentage,

								CASE WHEN ( drtr.RegistrationCostPerNumberCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = drtr.RegistrationCostPerNumberCurrency THEN
								drtr.RegistrationCostPerNumber
								ELSE
								(

								(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =   drtr.RegistrationCostPerNumberCurrency  and  CompanyID = @v_CompanyId_ )
								* (drtr.RegistrationCostPerNumber  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
								drtr.RegistrationCostPerNumber
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ )
									* (drtr.RegistrationCostPerNumber  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_   and  CompanyID = @v_CompanyId_ ))
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
								date(drtr.EndDate) as EndDate,
								@v_RateApprovalProcess_ as ApprovedStatus,


									now() as  created_at ,
									now() as updated_at ,
									p_ModifiedBy as CreatedBy ,
									p_ModifiedBy as ModifiedBy



								from tmp_SelectedVendortblRateTableDIDRate drtr
								inner join tblRateTable  rt on rt.RateTableId = drtr.RateTableId
								INNER JOIN tblRate r ON drtr.Code = r.Code and r.CodeDeckId = drtr.CodeDeckId
								LEFT JOIN tblRate rr ON drtr.OriginationCode = rr.Code and r.CodeDeckId = rr.CodeDeckId
								LEFT join tblRateTableDIDRate rtd  on rtd.RateID  = r.RateID and rtd.OriginationRateID  = rr.RateID
								and  rtd.TimezonesID = drtr.TimezonesID and rtd.City = drtr.City and rtd.Tariff = drtr.Tariff
								and rtd.RateTableID = @p_RateTableId
								and rtd.EffectiveDate = @p_EffectiveDate
								WHERE rtd.RateTableDIDRateID is null;

		END IF;


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


				IF (@v_RateApprovalProcess_ = 1 ) THEN


						UPDATE  tblRateTableDIDRateAA vr1
						inner join
						(
							select
								RateTableId,
								OriginationRateID,
								RateID,
								EffectiveDate,
								TimezonesID,
								City,
								Tariff
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
							AND vr1.City = tmpvr.City
							AND vr1.Tariff = tmpvr.Tariff
							AND vr1.EffectiveDate < tmpvr.EffectiveDate
						SET
							vr1.EndDate = @EffectiveDate
						where
							vr1.RateTableId = @p_RateTableId

							AND vr1.EndDate is null;

				ELSE

						UPDATE  tblRateTableDIDRate vr1
						inner join
						(
							select
								RateTableId,
								OriginationRateID,
								RateID,
								EffectiveDate,
								TimezonesID,
								City,
								Tariff
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
							AND vr1.City = tmpvr.City
							AND vr1.Tariff = tmpvr.Tariff
							AND vr1.EffectiveDate < tmpvr.EffectiveDate
						SET
							vr1.EndDate = @EffectiveDate
						where
							vr1.RateTableId = @p_RateTableId

							AND vr1.EndDate is null;
				END IF;

				SET @v_pointer_ = @v_pointer_ + 1;

			END WHILE;

			SELECT RoundChargedAmount INTO @v_RoundChargedAmount from tblRateTable where RateTableID = @p_RateTableId  limit 1;



			IF (@v_RateApprovalProcess_ = 1 ) THEN



				update tblRateTableDIDRateAA
				SET

				OneOffCost = IF(OneOffCost = 0 , NULL, ROUND(OneOffCost,IFNULL(@v_RoundChargedAmount,@v_CompanyRoundChargesAmount))),
				MonthlyCost = IF(MonthlyCost = 0 , NULL, ROUND(MonthlyCost,IFNULL(@v_RoundChargedAmount,@v_CompanyRoundChargesAmount))),
				CostPerCall = IF(CostPerCall = 0 , NULL, ROUND(CostPerCall,IFNULL(@v_RoundChargedAmount,@v_CompanyRoundChargesAmount))),
				CostPerMinute = IF(CostPerMinute = 0 , NULL, ROUND(CostPerMinute,IFNULL(@v_RoundChargedAmount,@v_CompanyRoundChargesAmount))),
				SurchargePerCall = IF(SurchargePerCall = 0 , NULL, ROUND(SurchargePerCall,IFNULL(@v_RoundChargedAmount,@v_CompanyRoundChargesAmount))),
				SurchargePerMinute = IF(SurchargePerMinute = 0 , NULL, ROUND(SurchargePerMinute,IFNULL(@v_RoundChargedAmount,@v_CompanyRoundChargesAmount))),
				OutpaymentPerCall = IF(OutpaymentPerCall = 0 , NULL, ROUND(OutpaymentPerCall,IFNULL(@v_RoundChargedAmount,@v_CompanyRoundChargesAmount))),
				OutpaymentPerMinute = IF(OutpaymentPerMinute = 0 , NULL, ROUND(OutpaymentPerMinute,IFNULL(@v_RoundChargedAmount,@v_CompanyRoundChargesAmount))),
				Surcharges = IF(Surcharges = 0 , NULL, ROUND(Surcharges,IFNULL(@v_RoundChargedAmount,@v_CompanyRoundChargesAmount))),
				Chargeback = IF(Chargeback = 0 , NULL, ROUND(Chargeback,IFNULL(@v_RoundChargedAmount,@v_CompanyRoundChargesAmount))),
				CollectionCostAmount = IF(CollectionCostAmount = 0 , NULL, ROUND(CollectionCostAmount,IFNULL(@v_RoundChargedAmount,@v_CompanyRoundChargesAmount))),
				CollectionCostPercentage = IF(CollectionCostPercentage = 0 , NULL, ROUND(CollectionCostPercentage,IFNULL(@v_RoundChargedAmount,@v_CompanyRoundChargesAmount))),
				RegistrationCostPerNumber = IF(RegistrationCostPerNumber = 0 , NULL, ROUND(RegistrationCostPerNumber,IFNULL(@v_RoundChargedAmount,@v_CompanyRoundChargesAmount))),
				updated_at = now(),
				ModifiedBy = p_ModifiedBy

				where
				RateTableID = @p_RateTableId;



			ELSE


				update tblRateTableDIDRate
				SET

				OneOffCost = IF(OneOffCost = 0 , NULL, ROUND(OneOffCost,IFNULL(@v_RoundChargedAmount,@v_CompanyRoundChargesAmount))),
				MonthlyCost = IF(MonthlyCost = 0 , NULL, ROUND(MonthlyCost,IFNULL(@v_RoundChargedAmount,@v_CompanyRoundChargesAmount))),
				CostPerCall = IF(CostPerCall = 0 , NULL, ROUND(CostPerCall,IFNULL(@v_RoundChargedAmount,@v_CompanyRoundChargesAmount))),
				CostPerMinute = IF(CostPerMinute = 0 , NULL, ROUND(CostPerMinute,IFNULL(@v_RoundChargedAmount,@v_CompanyRoundChargesAmount))),
				SurchargePerCall = IF(SurchargePerCall = 0 , NULL, ROUND(SurchargePerCall,IFNULL(@v_RoundChargedAmount,@v_CompanyRoundChargesAmount))),
				SurchargePerMinute = IF(SurchargePerMinute = 0 , NULL, ROUND(SurchargePerMinute,IFNULL(@v_RoundChargedAmount,@v_CompanyRoundChargesAmount))),
				OutpaymentPerCall = IF(OutpaymentPerCall = 0 , NULL, ROUND(OutpaymentPerCall,IFNULL(@v_RoundChargedAmount,@v_CompanyRoundChargesAmount))),
				OutpaymentPerMinute = IF(OutpaymentPerMinute = 0 , NULL, ROUND(OutpaymentPerMinute,IFNULL(@v_RoundChargedAmount,@v_CompanyRoundChargesAmount))),
				Surcharges = IF(Surcharges = 0 , NULL, ROUND(Surcharges,IFNULL(@v_RoundChargedAmount,@v_CompanyRoundChargesAmount))),
				Chargeback = IF(Chargeback = 0 , NULL, ROUND(Chargeback,IFNULL(@v_RoundChargedAmount,@v_CompanyRoundChargesAmount))),
				CollectionCostAmount = IF(CollectionCostAmount = 0 , NULL, ROUND(CollectionCostAmount,IFNULL(@v_RoundChargedAmount,@v_CompanyRoundChargesAmount))),
				CollectionCostPercentage = IF(CollectionCostPercentage = 0 , NULL, ROUND(CollectionCostPercentage,IFNULL(@v_RoundChargedAmount,@v_CompanyRoundChargesAmount))),
				RegistrationCostPerNumber = IF(RegistrationCostPerNumber = 0 , NULL, ROUND(RegistrationCostPerNumber,IFNULL(@v_RoundChargedAmount,@v_CompanyRoundChargesAmount))),
				updated_at = now(),
				ModifiedBy = p_ModifiedBy

				where
				RateTableID = @p_RateTableId;

			END IF;


		END IF;

		commit;


		IF (@v_RateApprovalProcess_ = 1 ) THEN


			call prc_ArchiveOldRateTableDIDRateAA(@p_RateTableId, NULL,p_ModifiedBy);

		ELSE

			call prc_ArchiveOldRateTableDIDRate(@p_RateTableId, NULL,p_ModifiedBy);

		END IF;

		INSERT INTO tmp_JobLog_ (Message) VALUES (@p_RateTableId);

		SELECT * FROM tmp_JobLog_;


		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;



	END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_GetRateTableDIDRatesArchiveGrid`;
DELIMITER //
CREATE PROCEDURE `prc_GetRateTableDIDRatesArchiveGrid`(
	IN `p_CompanyID` INT,
	IN `p_RateTableID` INT,
	IN `p_TimezonesID` INT,
	IN `p_RateID` LONGTEXT,
	IN `p_OriginationRateID` LONGTEXT,
	IN `p_City` VARCHAR(50),
	IN `p_Tariff` VARCHAR(50),
	IN `p_View` INT
)
BEGIN

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
	CREATE TEMPORARY TABLE tmp_RateTableRate_ (
		AccessType varchar(200),
		Country VARCHAR(50),
		OriginationCode VARCHAR(50),
		Code VARCHAR(50),
		City VARCHAR(50),
		Tariff VARCHAR(50),
		OneOffCost DECIMAL(18,6),
		MonthlyCost DECIMAL(18,6),
		CostPerCall DECIMAL(18,6),
		CostPerMinute DECIMAL(18,6),
		SurchargePerCall DECIMAL(18,6),
		SurchargePerMinute DECIMAL(18,6),
		OutpaymentPerCall DECIMAL(18,6),
		OutpaymentPerMinute DECIMAL(18,6),
		Surcharges DECIMAL(18,6),
		Chargeback DECIMAL(18,6),
		CollectionCostAmount DECIMAL(18,6),
		CollectionCostPercentage DECIMAL(18,6),
		RegistrationCostPerNumber DECIMAL(18,6),
		EffectiveDate DATE,
		EndDate DATE,
		updated_at DATETIME,
		ModifiedBy VARCHAR(50),
		ApprovedStatus tinyint(4),
		ApprovedDate DATETIME,
		ApprovedBy VARCHAR(50),
		OneOffCostCurrency VARCHAR(255),
		MonthlyCostCurrency VARCHAR(255),
		CostPerCallCurrency VARCHAR(255),
		CostPerMinuteCurrency VARCHAR(255),
		SurchargePerCallCurrency VARCHAR(255),
		SurchargePerMinuteCurrency VARCHAR(255),
		OutpaymentPerCallCurrency VARCHAR(255),
		OutpaymentPerMinuteCurrency VARCHAR(255),
		SurchargesCurrency VARCHAR(255),
		ChargebackCurrency VARCHAR(255),
		CollectionCostAmountCurrency VARCHAR(255),
		RegistrationCostPerNumberCurrency VARCHAR(255)
	);

	INSERT INTO tmp_RateTableRate_ (
		AccessType,
		Country,
		OriginationCode,
		Code,
		City,
		Tariff,
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
		EffectiveDate,
		EndDate,
		updated_at,
		ModifiedBy,
		ApprovedStatus,
		ApprovedDate,
		ApprovedBy,
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
	SELECT
		vra.AccessType,
		tblCountry.Country,
		o_r.Code AS OriginationCode,
		r.Code,
		vra.City,
		vra.Tariff,
		vra.OneOffCost,
		vra.MonthlyCost,
		vra.CostPerCall,
		vra.CostPerMinute,
		vra.SurchargePerCall,
		vra.SurchargePerMinute,
		vra.OutpaymentPerCall,
		vra.OutpaymentPerMinute,
		vra.Surcharges,
		vra.Chargeback,
		vra.CollectionCostAmount,
		vra.CollectionCostPercentage,
		vra.RegistrationCostPerNumber,
		vra.EffectiveDate,
		IFNULL(vra.EndDate,'') AS EndDate,
		IFNULL(vra.created_at,'') AS ModifiedDate,
		IFNULL(vra.CreatedBy,'') AS ModifiedBy,
		vra.ApprovedStatus,
		vra.ApprovedDate,
		vra.ApprovedBy,
		IFNULL(tblOneOffCostCurrency.Symbol, '') AS OneOffCostCurrency,
		IFNULL(tblMonthlyCostCurrency.Symbol, '') AS MonthlyCostCurrency,
		IFNULL(tblCostPerCallCurrency.Symbol, '') AS CostPerCallCurrency,
		IFNULL(tblCostPerMinuteCurrency.Symbol, '') AS CostPerMinuteCurrency,
		IFNULL(tblSurchargePerCallCurrency.Symbol, '') AS SurchargePerCallCurrency,
		IFNULL(tblSurchargePerMinuteCurrency.Symbol, '') AS SurchargePerMinuteCurrency,
		IFNULL(tblOutpaymentPerCallCurrency.Symbol, '') AS OutpaymentPerCallCurrency,
		IFNULL(tblOutpaymentPerMinuteCurrency.Symbol, '') AS OutpaymentPerMinuteCurrency,
		IFNULL(tblSurchargesCurrency.Symbol, '') AS SurchargesCurrency,
		IFNULL(tblChargebackCurrency.Symbol, '') AS ChargebackCurrency,
		IFNULL(tblCollectionCostAmountCurrency.Symbol, '') AS CollectionCostAmountCurrency,
		IFNULL(tblRegistrationCostPerNumberCurrency.Symbol, '') AS RegistrationCostPerNumberCurrency
	FROM
		tblRateTableDIDRateArchive vra
	JOIN
		tblRate r ON r.RateID=vra.RateId
	LEFT JOIN
		tblRate o_r ON o_r.RateID=vra.OriginationRateID
	LEFT JOIN tblCurrency AS tblOneOffCostCurrency
		ON tblOneOffCostCurrency.CurrencyID = vra.OneOffCostCurrency
	LEFT JOIN tblCurrency AS tblMonthlyCostCurrency
		ON tblMonthlyCostCurrency.CurrencyID = vra.MonthlyCostCurrency
	LEFT JOIN tblCurrency AS tblCostPerCallCurrency
		ON tblCostPerCallCurrency.CurrencyID = vra.CostPerCallCurrency
	LEFT JOIN tblCurrency AS tblCostPerMinuteCurrency
		ON tblCostPerMinuteCurrency.CurrencyID = vra.CostPerMinuteCurrency
	LEFT JOIN tblCurrency AS tblSurchargePerCallCurrency
		ON tblSurchargePerCallCurrency.CurrencyID = vra.SurchargePerCallCurrency
	LEFT JOIN tblCurrency AS tblSurchargePerMinuteCurrency
		ON tblSurchargePerMinuteCurrency.CurrencyID = vra.SurchargePerMinuteCurrency
	LEFT JOIN tblCurrency AS tblOutpaymentPerCallCurrency
		ON tblOutpaymentPerCallCurrency.CurrencyID = vra.OutpaymentPerCallCurrency
	LEFT JOIN tblCurrency AS tblOutpaymentPerMinuteCurrency
		ON tblOutpaymentPerMinuteCurrency.CurrencyID = vra.OutpaymentPerMinuteCurrency
	LEFT JOIN tblCurrency AS tblSurchargesCurrency
		ON tblSurchargesCurrency.CurrencyID = vra.SurchargesCurrency
	LEFT JOIN tblCurrency AS tblChargebackCurrency
		ON tblChargebackCurrency.CurrencyID = vra.ChargebackCurrency
	LEFT JOIN tblCurrency AS tblCollectionCostAmountCurrency
		ON tblCollectionCostAmountCurrency.CurrencyID = vra.CollectionCostAmountCurrency
	LEFT JOIN tblCurrency AS tblRegistrationCostPerNumberCurrency
		ON tblRegistrationCostPerNumberCurrency.CurrencyID = vra.RegistrationCostPerNumberCurrency
	INNER JOIN tblRateTable
		ON tblRateTable.RateTableId = vra.RateTableId
	LEFT JOIN tblCountry
		ON tblCountry.CountryId = r.CountryId
	WHERE
		r.CompanyID = p_CompanyID AND
		vra.RateTableId = p_RateTableID AND
		vra.TimezonesID = p_TimezonesID AND
		vra.RateID = p_RateID AND
		vra.OriginationRateID = p_OriginationRateID AND
		vra.City = p_City AND
		vra.Tariff = p_Tariff
		/*(
		(vra.RateID, vra.OriginationRateID) IN (
		SELECT RateID,OriginationRateID FROM temp_rateids_
		)
		)*/
	ORDER BY
		vra.EffectiveDate DESC, vra.created_at DESC;

	SELECT
		AccessType,
		Country,
		OriginationCode,
		Code,
		City,
		Tariff,
		CONCAT(IFNULL(OneOffCostCurrency,''), OneOffCost) AS OneOffCost,
		CONCAT(IFNULL(MonthlyCostCurrency,''), MonthlyCost) AS MonthlyCost,
		CONCAT(IFNULL(CostPerCallCurrency,''), CostPerCall) AS CostPerCall,
		CONCAT(IFNULL(CostPerMinuteCurrency,''), CostPerMinute) AS CostPerMinute,
		CONCAT(IFNULL(SurchargePerCallCurrency,''), SurchargePerCall) AS SurchargePerCall,
		CONCAT(IFNULL(SurchargePerMinuteCurrency,''), SurchargePerMinute) AS SurchargePerMinute,
		CONCAT(IFNULL(OutpaymentPerCallCurrency,''), OutpaymentPerCall) AS OutpaymentPerCall,
		CONCAT(IFNULL(OutpaymentPerMinuteCurrency,''), OutpaymentPerMinute) AS OutpaymentPerMinute,
		CONCAT(IFNULL(SurchargesCurrency,''), Surcharges) AS Surcharges,
		CONCAT(IFNULL(ChargebackCurrency,''), Chargeback) AS Chargeback,
		CONCAT(IFNULL(CollectionCostAmountCurrency,''), CollectionCostAmount) AS CollectionCostAmount,
		CollectionCostPercentage,
		CONCAT(IFNULL(RegistrationCostPerNumberCurrency,''), RegistrationCostPerNumber) AS RegistrationCostPerNumber,
		EffectiveDate,
		EndDate,
		IFNULL(updated_at,'') AS ModifiedDate,
		IFNULL(ModifiedBy,'') AS ModifiedBy,
		ApprovedStatus,
		ApprovedDate,
		ApprovedBy
	FROM tmp_RateTableRate_;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_GetRateTableDIDRate`;
DELIMITER //
CREATE PROCEDURE `prc_GetRateTableDIDRate`(
	IN `p_companyid` INT,
	IN `p_RateTableId` INT,
	IN `p_trunkID` INT,
	IN `p_TimezonesID` INT,
	IN `p_contryID` INT,
	IN `p_origination_code` VARCHAR(50),
	IN `p_code` VARCHAR(50),
	IN `p_City` VARCHAR(50),
	IN `p_Tariff` VARCHAR(50),
	IN `p_AccessType` VARCHAR(50),
	IN `p_effective` VARCHAR(50),
	IN `p_ApprovedStatus` TINYINT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
BEGIN
	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableDIDRate_;
	CREATE TEMPORARY TABLE tmp_RateTableDIDRate_ (
		ID INT,
		AccessType varchar(200),
		Country VARCHAR(200),
		OriginationCode VARCHAR(50),
		Code VARCHAR(50),
		City VARCHAR(50),
		Tariff VARCHAR(50),
		TimezoneTitle VARCHAR(50),
		OneOffCost DECIMAL(18,6),
		MonthlyCost DECIMAL(18,6),
		CostPerCall DECIMAL(18,6),
		CostPerMinute DECIMAL(18,6),
		SurchargePerCall DECIMAL(18,6),
		SurchargePerMinute DECIMAL(18,6),
		OutpaymentPerCall DECIMAL(18,6),
		OutpaymentPerMinute DECIMAL(18,6),
		Surcharges DECIMAL(18,6),
		Chargeback DECIMAL(18,6),
		CollectionCostAmount DECIMAL(18,6),
		CollectionCostPercentage DECIMAL(18,6),
		RegistrationCostPerNumber DECIMAL(18,6),
		EffectiveDate DATE,
		EndDate DATE,
		updated_at DATETIME,
		ModifiedBy VARCHAR(50),
		RateTableDIDRateID INT,
		OriginationRateID INT,
		RateID INT,
		ApprovedStatus TINYINT,
		ApprovedBy VARCHAR(50),
		ApprovedDate DATETIME,
		OneOffCostCurrency INT(11),
		MonthlyCostCurrency INT(11),
		CostPerCallCurrency INT(11),
		CostPerMinuteCurrency INT(11),
		SurchargePerCallCurrency INT(11),
		SurchargePerMinuteCurrency INT(11),
		OutpaymentPerCallCurrency INT(11),
		OutpaymentPerMinuteCurrency INT(11),
		SurchargesCurrency INT(11),
		ChargebackCurrency INT(11),
		CollectionCostAmountCurrency INT(11),
		RegistrationCostPerNumberCurrency INT(11),
		OneOffCostCurrencySymbol VARCHAR(255),
		MonthlyCostCurrencySymbol VARCHAR(255),
		CostPerCallCurrencySymbol VARCHAR(255),
		CostPerMinuteCurrencySymbol VARCHAR(255),
		SurchargePerCallCurrencySymbol VARCHAR(255),
		SurchargePerMinuteCurrencySymbol VARCHAR(255),
		OutpaymentPerCallCurrencySymbol VARCHAR(255),
		OutpaymentPerMinuteCurrencySymbol VARCHAR(255),
		SurchargesCurrencySymbol VARCHAR(255),
		ChargebackCurrencySymbol VARCHAR(255),
		CollectionCostAmountCurrencySymbol VARCHAR(255),
		RegistrationCostPerNumberCurrencySymbol VARCHAR(255),
		TimezonesID INT(11),
		INDEX tmp_RateTableDIDRate_RateID (`RateID`)
    );


    INSERT INTO tmp_RateTableDIDRate_
    SELECT
		RateTableDIDRateID AS ID,
		AccessType,
		tblCountry.Country,
		OriginationRate.Code AS OriginationCode,
		tblRate.Code,
		City,
		Tariff,
		tblTimezones.Title AS TimezoneTitle,
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
		IFNULL(tblRateTableDIDRate.EffectiveDate, NOW()) as EffectiveDate,
		tblRateTableDIDRate.EndDate,
		tblRateTableDIDRate.updated_at,
		tblRateTableDIDRate.ModifiedBy,
		RateTableDIDRateID,
		OriginationRate.RateID AS OriginationRateID,
		tblRate.RateID,
		tblRateTableDIDRate.ApprovedStatus,
		tblRateTableDIDRate.ApprovedBy,
		tblRateTableDIDRate.ApprovedDate,
		tblOneOffCostCurrency.CurrencyID AS OneOffCostCurrency,
		tblMonthlyCostCurrency.CurrencyID AS MonthlyCostCurrency,
		tblCostPerCallCurrency.CurrencyID AS CostPerCallCurrency,
		tblCostPerMinuteCurrency.CurrencyID AS CostPerMinuteCurrency,
		tblSurchargePerCallCurrency.CurrencyID AS SurchargePerCallCurrency,
		tblSurchargePerMinuteCurrency.CurrencyID AS SurchargePerMinuteCurrency,
		tblOutpaymentPerCallCurrency.CurrencyID AS OutpaymentPerCallCurrency,
		tblOutpaymentPerMinuteCurrency.CurrencyID AS OutpaymentPerMinuteCurrency,
		tblSurchargesCurrency.CurrencyID AS SurchargesCurrency,
		tblChargebackCurrency.CurrencyID AS ChargebackCurrency,
		tblCollectionCostAmountCurrency.CurrencyID AS CollectionCostAmountCurrency,
		tblRegistrationCostPerNumberCurrency.CurrencyID AS RegistrationCostPerNumberCurrency,
		IFNULL(tblOneOffCostCurrency.Symbol,'') AS OneOffCostCurrencySymbol,
		IFNULL(tblMonthlyCostCurrency.Symbol,'') AS MonthlyCostCurrencySymbol,
		IFNULL(tblCostPerCallCurrency.Symbol,'') AS CostPerCallCurrencySymbol,
		IFNULL(tblCostPerMinuteCurrency.Symbol,'') AS CostPerMinuteCurrencySymbol,
		IFNULL(tblSurchargePerCallCurrency.Symbol,'') AS SurchargePerCallCurrencySymbol,
		IFNULL(tblSurchargePerMinuteCurrency.Symbol,'') AS SurchargePerMinuteCurrencySymbol,
		IFNULL(tblOutpaymentPerCallCurrency.Symbol,'') AS OutpaymentPerCallCurrencySymbol,
		IFNULL(tblOutpaymentPerMinuteCurrency.Symbol,'') AS OutpaymentPerMinuteCurrencySymbol,
		IFNULL(tblSurchargesCurrency.Symbol,'') AS SurchargesCurrencySymbol,
		IFNULL(tblChargebackCurrency.Symbol,'') AS ChargebackCurrencySymbol,
		IFNULL(tblCollectionCostAmountCurrency.Symbol,'') AS CollectionCostAmountCurrencySymbol,
		IFNULL(tblRegistrationCostPerNumberCurrency.Symbol,'') AS RegistrationCostPerNumberCurrencySymbol,
		tblRateTableDIDRate.TimezonesID
    FROM tblRate
    LEFT JOIN tblRateTableDIDRate
        ON tblRateTableDIDRate.RateID = tblRate.RateID
        AND tblRateTableDIDRate.RateTableId = p_RateTableId
    INNER JOIN tblTimezones
    	  ON tblTimezones.TimezonesID = tblRateTableDIDRate.TimezonesID
    LEFT JOIN tblRate AS OriginationRate
    	  ON OriginationRate.RateID = tblRateTableDIDRate.OriginationRateID
    LEFT JOIN tblCountry
    		ON tblCountry.CountryID = tblRate.CountryID
    LEFT JOIN tblCurrency AS tblOneOffCostCurrency
        ON tblOneOffCostCurrency.CurrencyID = tblRateTableDIDRate.OneOffCostCurrency
    LEFT JOIN tblCurrency AS tblMonthlyCostCurrency
        ON tblMonthlyCostCurrency.CurrencyID = tblRateTableDIDRate.MonthlyCostCurrency
    LEFT JOIN tblCurrency AS tblCostPerCallCurrency
        ON tblCostPerCallCurrency.CurrencyID = tblRateTableDIDRate.CostPerCallCurrency
    LEFT JOIN tblCurrency AS tblCostPerMinuteCurrency
        ON tblCostPerMinuteCurrency.CurrencyID = tblRateTableDIDRate.CostPerMinuteCurrency
    LEFT JOIN tblCurrency AS tblSurchargePerCallCurrency
        ON tblSurchargePerCallCurrency.CurrencyID = tblRateTableDIDRate.SurchargePerCallCurrency
    LEFT JOIN tblCurrency AS tblSurchargePerMinuteCurrency
        ON tblSurchargePerMinuteCurrency.CurrencyID = tblRateTableDIDRate.SurchargePerMinuteCurrency
    LEFT JOIN tblCurrency AS tblOutpaymentPerCallCurrency
        ON tblOutpaymentPerCallCurrency.CurrencyID = tblRateTableDIDRate.OutpaymentPerCallCurrency
    LEFT JOIN tblCurrency AS tblOutpaymentPerMinuteCurrency
        ON tblOutpaymentPerMinuteCurrency.CurrencyID = tblRateTableDIDRate.OutpaymentPerMinuteCurrency
    LEFT JOIN tblCurrency AS tblSurchargesCurrency
        ON tblSurchargesCurrency.CurrencyID = tblRateTableDIDRate.SurchargesCurrency
    LEFT JOIN tblCurrency AS tblChargebackCurrency
        ON tblChargebackCurrency.CurrencyID = tblRateTableDIDRate.ChargebackCurrency
    LEFT JOIN tblCurrency AS tblCollectionCostAmountCurrency
        ON tblCollectionCostAmountCurrency.CurrencyID = tblRateTableDIDRate.CollectionCostAmountCurrency
    LEFT JOIN tblCurrency AS tblRegistrationCostPerNumberCurrency
        ON tblRegistrationCostPerNumberCurrency.CurrencyID = tblRateTableDIDRate.RegistrationCostPerNumberCurrency
    INNER JOIN tblRateTable
        ON tblRateTable.RateTableId = tblRateTableDIDRate.RateTableId
    LEFT JOIN tblCurrency AS tblRateTableCurrency
    	  ON tblRateTableCurrency.CurrencyId = tblRateTable.CurrencyID
    WHERE		(tblRate.CompanyID = p_companyid)
		AND (p_contryID is null OR tblRate.CountryID = p_contryID)
		AND (p_origination_code is null OR OriginationRate.Code LIKE REPLACE(p_origination_code, '*', '%'))
		AND (p_code is null OR tblRate.Code LIKE REPLACE(p_code, '*', '%'))
		AND (p_City IS NULL OR tblRateTableDIDRate.City LIKE REPLACE(p_City, '*', '%'))
		AND (p_Tariff IS NULL OR tblRateTableDIDRate.Tariff LIKE REPLACE(p_Tariff, '*', '%'))
		AND (p_AccessType IS NULL OR tblRateTableDIDRate.AccessType LIKE REPLACE(p_AccessType, '*', '%'))
		AND (p_ApprovedStatus IS NULL OR tblRateTableDIDRate.ApprovedStatus = p_ApprovedStatus)
		AND TrunkID = p_trunkID
		AND (p_TimezonesID IS NULL OR tblRateTableDIDRate.TimezonesID = p_TimezonesID)
		AND (
				p_effective = 'All'
				OR (p_effective = 'Now' AND EffectiveDate <= NOW() )
				OR (p_effective = 'Future' AND EffectiveDate > NOW())
			);

	  IF p_effective = 'Now'
		THEN
		   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableDIDRate4_ as (select * from tmp_RateTableDIDRate_);
         DELETE n1 FROM tmp_RateTableDIDRate_ n1, tmp_RateTableDIDRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
		   AND  n1.RateID = n2.RateID AND n1.OriginationRateID = n2.OriginationRateID AND n1.TimezonesID = n2.TimezonesID AND n1.City = n2.City AND n1.Tariff = n2.Tariff;
		END IF;


    IF p_isExport = 0
    THEN

       	SELECT * FROM tmp_RateTableDIDRate_
					ORDER BY
					 CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryDESC') THEN Country
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryASC') THEN Country
                END ASC,
					 CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleDESC') THEN TimezoneTitle
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleASC') THEN TimezoneTitle
                END ASC,
					 CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeDESC') THEN OriginationCode
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeASC') THEN OriginationCode
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CityDESC') THEN City
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CityASC') THEN City
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TariffDESC') THEN Tariff
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TariffASC') THEN Tariff
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccessTypeDESC') THEN AccessType
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccessTypeASC') THEN AccessType
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OneOffCostDESC') THEN OneOffCost
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OneOffCostASC') THEN OneOffCost
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MonthlyCostDESC') THEN MonthlyCost
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MonthlyCostASC') THEN MonthlyCost
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerCallDESC') THEN CostPerCall
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerCallASC') THEN CostPerCall
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerMinuteDESC') THEN CostPerMinute
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerMinuteASC') THEN CostPerMinute
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerCallDESC') THEN SurchargePerCall
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerCallASC') THEN SurchargePerCall
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerMinuteDESC') THEN SurchargePerMinute
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerMinuteASC') THEN SurchargePerMinute
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerCallDESC') THEN OutpaymentPerCall
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerCallASC') THEN OutpaymentPerCall
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerMinuteDESC') THEN OutpaymentPerMinute
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerMinuteASC') THEN OutpaymentPerMinute
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargesDESC') THEN Surcharges
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargesASC') THEN Surcharges
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ChargebackDESC') THEN Chargeback
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ChargebackASC') THEN Chargeback
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostAmountDESC') THEN CollectionCostAmount
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostAmountASC') THEN CollectionCostAmount
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostPercentageDESC') THEN CollectionCostPercentage
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostPercentageASC') THEN CollectionCostPercentage
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RegistrationCostPerNumberDESC') THEN RegistrationCostPerNumber
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RegistrationCostPerNumberASC') THEN RegistrationCostPerNumber
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateDESC') THEN EffectiveDate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateASC') THEN EffectiveDate
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateDESC') THEN EndDate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateASC') THEN EndDate
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN updated_at
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN updated_at
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ModifiedByDESC') THEN ModifiedBy
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ModifiedByASC') THEN ModifiedBy
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ApprovedByDESC') THEN ApprovedBy
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ApprovedByASC') THEN ApprovedBy
                END ASC
			LIMIT p_RowspPage OFFSET v_OffSet_;

			SELECT
            COUNT(RateID) AS totalcount
        	FROM tmp_RateTableDIDRate_;

    END IF;

	 -- basic view
    IF p_isExport = 10
    THEN
        SELECT
			AccessType,
			Country,
			OriginationCode AS Origination,
			Code AS Prefix,
			City,
			Tariff,
        	TimezoneTitle AS `Time of Day`,
			CONCAT(OneOffCostCurrencySymbol,OneOffCost) AS OneOffCost,
			CONCAT(MonthlyCostCurrencySymbol,MonthlyCost) AS MonthlyCost,
			CONCAT(CostPerCallCurrencySymbol,CostPerCall) AS CostPerCall,
			CONCAT(CostPerMinuteCurrencySymbol,CostPerMinute) AS CostPerMinute,
			CONCAT(SurchargePerCallCurrencySymbol,SurchargePerCall) AS SurchargePerCall,
			CONCAT(SurchargePerMinuteCurrencySymbol,SurchargePerMinute) AS SurchargePerMinute,
			CONCAT(OutpaymentPerCallCurrencySymbol,OutpaymentPerCall) AS OutpaymentPerCall,
			CONCAT(OutpaymentPerMinuteCurrencySymbol,OutpaymentPerMinute) AS OutpaymentPerMinute,
			CONCAT(SurchargesCurrencySymbol,Surcharges) AS Surcharges,
			CONCAT(ChargebackCurrencySymbol,Chargeback) AS Chargeback,
			CONCAT(CollectionCostAmountCurrencySymbol,CollectionCostAmount) AS CollectionCostAmount,
			CollectionCostPercentage,
			CONCAT(RegistrationCostPerNumberCurrencySymbol,RegistrationCostPerNumber) AS RegistrationCostPerNumber,
			EffectiveDate,
			ApprovedStatus
        FROM
			tmp_RateTableDIDRate_;
    END IF;

	 -- advance view
    IF p_isExport = 11
    THEN
        SELECT
        	AccessType,
			Country,
			OriginationCode AS Origination,
			Code AS Prefix,
			City,
			Tariff,
        	TimezoneTitle AS `Time of Day`,
			CONCAT(OneOffCostCurrencySymbol,OneOffCost) AS OneOffCost,
			CONCAT(MonthlyCostCurrencySymbol,MonthlyCost) AS MonthlyCost,
			CONCAT(CostPerCallCurrencySymbol,CostPerCall) AS CostPerCall,
			CONCAT(CostPerMinuteCurrencySymbol,CostPerMinute) AS CostPerMinute,
			CONCAT(SurchargePerCallCurrencySymbol,SurchargePerCall) AS SurchargePerCall,
			CONCAT(SurchargePerMinuteCurrencySymbol,SurchargePerMinute) AS SurchargePerMinute,
			CONCAT(OutpaymentPerCallCurrencySymbol,OutpaymentPerCall) AS OutpaymentPerCall,
			CONCAT(OutpaymentPerMinuteCurrencySymbol,OutpaymentPerMinute) AS OutpaymentPerMinute,
			CONCAT(SurchargesCurrencySymbol,Surcharges) AS Surcharges,
			CONCAT(ChargebackCurrencySymbol,Chargeback) AS Chargeback,
			CONCAT(CollectionCostAmountCurrencySymbol,CollectionCostAmount) AS CollectionCostAmount,
			CollectionCostPercentage,
			CONCAT(RegistrationCostPerNumberCurrencySymbol,RegistrationCostPerNumber) AS RegistrationCostPerNumber,
			EffectiveDate,
			CONCAT(ModifiedBy,'\n',updated_at) AS `Modified By/Date`,
			CONCAT(ApprovedBy,'\n',ApprovedDate) AS `Approved By/Date`,
			ApprovedStatus
        FROM
			tmp_RateTableDIDRate_;
    END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_GetRateTableDIDRateAA`;
DELIMITER //
CREATE PROCEDURE `prc_GetRateTableDIDRateAA`(
	IN `p_companyid` INT,
	IN `p_RateTableId` INT,
	IN `p_trunkID` INT,
	IN `p_TimezonesID` INT,
	IN `p_contryID` INT,
	IN `p_origination_code` VARCHAR(50),
	IN `p_code` VARCHAR(50),
	IN `p_City` VARCHAR(50),
	IN `p_Tariff` VARCHAR(50),
	IN `p_AccessType` VARCHAR(50),
	IN `p_effective` VARCHAR(50),
	IN `p_ApprovedStatus` TINYINT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
BEGIN
	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableDIDRate_;
	CREATE TEMPORARY TABLE tmp_RateTableDIDRate_ (
		ID INT,
		AccessType varchar(200),
		Country VARCHAR(200),
		OriginationCode VARCHAR(50),
		Code VARCHAR(50),
		City VARCHAR(50),
		Tariff VARCHAR(50),
		TimezoneTitle VARCHAR(50),
		OneOffCost DECIMAL(18,6),
		MonthlyCost DECIMAL(18,6),
		CostPerCall DECIMAL(18,6),
		CostPerMinute DECIMAL(18,6),
		SurchargePerCall DECIMAL(18,6),
		SurchargePerMinute DECIMAL(18,6),
		OutpaymentPerCall DECIMAL(18,6),
		OutpaymentPerMinute DECIMAL(18,6),
		Surcharges DECIMAL(18,6),
		Chargeback DECIMAL(18,6),
		CollectionCostAmount DECIMAL(18,6),
		CollectionCostPercentage DECIMAL(18,6),
		RegistrationCostPerNumber DECIMAL(18,6),
		EffectiveDate DATE,
		EndDate DATE,
		updated_at DATETIME,
		ModifiedBy VARCHAR(50),
		RateTableDIDRateID INT,
		OriginationRateID INT,
		RateID INT,
		ApprovedStatus TINYINT,
		ApprovedBy VARCHAR(50),
		ApprovedDate DATETIME,
		OneOffCostCurrency INT(11),
		MonthlyCostCurrency INT(11),
		CostPerCallCurrency INT(11),
		CostPerMinuteCurrency INT(11),
		SurchargePerCallCurrency INT(11),
		SurchargePerMinuteCurrency INT(11),
		OutpaymentPerCallCurrency INT(11),
		OutpaymentPerMinuteCurrency INT(11),
		SurchargesCurrency INT(11),
		ChargebackCurrency INT(11),
		CollectionCostAmountCurrency INT(11),
		RegistrationCostPerNumberCurrency INT(11),
		OneOffCostCurrencySymbol VARCHAR(255),
		MonthlyCostCurrencySymbol VARCHAR(255),
		CostPerCallCurrencySymbol VARCHAR(255),
		CostPerMinuteCurrencySymbol VARCHAR(255),
		SurchargePerCallCurrencySymbol VARCHAR(255),
		SurchargePerMinuteCurrencySymbol VARCHAR(255),
		OutpaymentPerCallCurrencySymbol VARCHAR(255),
		OutpaymentPerMinuteCurrencySymbol VARCHAR(255),
		SurchargesCurrencySymbol VARCHAR(255),
		ChargebackCurrencySymbol VARCHAR(255),
		CollectionCostAmountCurrencySymbol VARCHAR(255),
		RegistrationCostPerNumberCurrencySymbol VARCHAR(255),
		TimezonesID INT(11),
		INDEX tmp_RateTableDIDRate_RateID (`RateID`)
    );


    INSERT INTO tmp_RateTableDIDRate_
    SELECT
		RateTableDIDRateAAID AS ID,
		AccessType,
		tblCountry.Country,
		OriginationRate.Code AS OriginationCode,
		tblRate.Code,
		City,
		Tariff,
		tblTimezones.Title AS TimezoneTitle,
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
		IFNULL(tblRateTableDIDRate.EffectiveDate, NOW()) as EffectiveDate,
		tblRateTableDIDRate.EndDate,
		tblRateTableDIDRate.updated_at,
		tblRateTableDIDRate.ModifiedBy,
		RateTableDIDRateAAID,
		OriginationRate.RateID AS OriginationRateID,
		tblRate.RateID,
		tblRateTableDIDRate.ApprovedStatus,
		tblRateTableDIDRate.ApprovedBy,
		tblRateTableDIDRate.ApprovedDate,
		tblOneOffCostCurrency.CurrencyID AS OneOffCostCurrency,
		tblMonthlyCostCurrency.CurrencyID AS MonthlyCostCurrency,
		tblCostPerCallCurrency.CurrencyID AS CostPerCallCurrency,
		tblCostPerMinuteCurrency.CurrencyID AS CostPerMinuteCurrency,
		tblSurchargePerCallCurrency.CurrencyID AS SurchargePerCallCurrency,
		tblSurchargePerMinuteCurrency.CurrencyID AS SurchargePerMinuteCurrency,
		tblOutpaymentPerCallCurrency.CurrencyID AS OutpaymentPerCallCurrency,
		tblOutpaymentPerMinuteCurrency.CurrencyID AS OutpaymentPerMinuteCurrency,
		tblSurchargesCurrency.CurrencyID AS SurchargesCurrency,
		tblChargebackCurrency.CurrencyID AS ChargebackCurrency,
		tblCollectionCostAmountCurrency.CurrencyID AS CollectionCostAmountCurrency,
		tblRegistrationCostPerNumberCurrency.CurrencyID AS RegistrationCostPerNumberCurrency,
		IFNULL(tblOneOffCostCurrency.Symbol,'') AS OneOffCostCurrencySymbol,
		IFNULL(tblMonthlyCostCurrency.Symbol,'') AS MonthlyCostCurrencySymbol,
		IFNULL(tblCostPerCallCurrency.Symbol,'') AS CostPerCallCurrencySymbol,
		IFNULL(tblCostPerMinuteCurrency.Symbol,'') AS CostPerMinuteCurrencySymbol,
		IFNULL(tblSurchargePerCallCurrency.Symbol,'') AS SurchargePerCallCurrencySymbol,
		IFNULL(tblSurchargePerMinuteCurrency.Symbol,'') AS SurchargePerMinuteCurrencySymbol,
		IFNULL(tblOutpaymentPerCallCurrency.Symbol,'') AS OutpaymentPerCallCurrencySymbol,
		IFNULL(tblOutpaymentPerMinuteCurrency.Symbol,'') AS OutpaymentPerMinuteCurrencySymbol,
		IFNULL(tblSurchargesCurrency.Symbol,'') AS SurchargesCurrencySymbol,
		IFNULL(tblChargebackCurrency.Symbol,'') AS ChargebackCurrencySymbol,
		IFNULL(tblCollectionCostAmountCurrency.Symbol,'') AS CollectionCostAmountCurrencySymbol,
		IFNULL(tblRegistrationCostPerNumberCurrency.Symbol,'') AS RegistrationCostPerNumberCurrencySymbol,
		tblRateTableDIDRate.TimezonesID
    FROM tblRate
    LEFT JOIN tblRateTableDIDRateAA AS tblRateTableDIDRate
        ON tblRateTableDIDRate.RateID = tblRate.RateID
        AND tblRateTableDIDRate.RateTableId = p_RateTableId
    INNER JOIN tblTimezones
    	  ON tblTimezones.TimezonesID = tblRateTableDIDRate.TimezonesID
    LEFT JOIN tblRate AS OriginationRate
    	  ON OriginationRate.RateID = tblRateTableDIDRate.OriginationRateID
    LEFT JOIN tblCountry
    		ON tblCountry.CountryID = tblRate.CountryID
    LEFT JOIN tblCurrency AS tblOneOffCostCurrency
        ON tblOneOffCostCurrency.CurrencyID = tblRateTableDIDRate.OneOffCostCurrency
    LEFT JOIN tblCurrency AS tblMonthlyCostCurrency
        ON tblMonthlyCostCurrency.CurrencyID = tblRateTableDIDRate.MonthlyCostCurrency
    LEFT JOIN tblCurrency AS tblCostPerCallCurrency
        ON tblCostPerCallCurrency.CurrencyID = tblRateTableDIDRate.CostPerCallCurrency
    LEFT JOIN tblCurrency AS tblCostPerMinuteCurrency
        ON tblCostPerMinuteCurrency.CurrencyID = tblRateTableDIDRate.CostPerMinuteCurrency
    LEFT JOIN tblCurrency AS tblSurchargePerCallCurrency
        ON tblSurchargePerCallCurrency.CurrencyID = tblRateTableDIDRate.SurchargePerCallCurrency
    LEFT JOIN tblCurrency AS tblSurchargePerMinuteCurrency
        ON tblSurchargePerMinuteCurrency.CurrencyID = tblRateTableDIDRate.SurchargePerMinuteCurrency
    LEFT JOIN tblCurrency AS tblOutpaymentPerCallCurrency
        ON tblOutpaymentPerCallCurrency.CurrencyID = tblRateTableDIDRate.OutpaymentPerCallCurrency
    LEFT JOIN tblCurrency AS tblOutpaymentPerMinuteCurrency
        ON tblOutpaymentPerMinuteCurrency.CurrencyID = tblRateTableDIDRate.OutpaymentPerMinuteCurrency
    LEFT JOIN tblCurrency AS tblSurchargesCurrency
        ON tblSurchargesCurrency.CurrencyID = tblRateTableDIDRate.SurchargesCurrency
    LEFT JOIN tblCurrency AS tblChargebackCurrency
        ON tblChargebackCurrency.CurrencyID = tblRateTableDIDRate.ChargebackCurrency
    LEFT JOIN tblCurrency AS tblCollectionCostAmountCurrency
        ON tblCollectionCostAmountCurrency.CurrencyID = tblRateTableDIDRate.CollectionCostAmountCurrency
    LEFT JOIN tblCurrency AS tblRegistrationCostPerNumberCurrency
        ON tblRegistrationCostPerNumberCurrency.CurrencyID = tblRateTableDIDRate.RegistrationCostPerNumberCurrency
    INNER JOIN tblRateTable
        ON tblRateTable.RateTableId = tblRateTableDIDRate.RateTableId
    LEFT JOIN tblCurrency AS tblRateTableCurrency
    	  ON tblRateTableCurrency.CurrencyId = tblRateTable.CurrencyID
    WHERE		(tblRate.CompanyID = p_companyid)
		AND (p_contryID is null OR tblRate.CountryID = p_contryID)
		AND (p_origination_code is null OR OriginationRate.Code LIKE REPLACE(p_origination_code, '*', '%'))
		AND (p_code is null OR tblRate.Code LIKE REPLACE(p_code, '*', '%'))
		AND (p_City IS NULL OR tblRateTableDIDRate.City LIKE REPLACE(p_City, '*', '%'))
		AND (p_Tariff IS NULL OR tblRateTableDIDRate.Tariff LIKE REPLACE(p_Tariff, '*', '%'))
		AND (p_AccessType IS NULL OR tblRateTableDIDRate.AccessType LIKE REPLACE(p_AccessType, '*', '%'))
		AND (p_ApprovedStatus IS NULL OR tblRateTableDIDRate.ApprovedStatus = p_ApprovedStatus)
		AND TrunkID = p_trunkID
		AND (p_TimezonesID IS NULL OR tblRateTableDIDRate.TimezonesID = p_TimezonesID)
		AND (
				p_effective = 'All'
				OR (p_effective = 'Now' AND EffectiveDate <= NOW() )
				OR (p_effective = 'Future' AND EffectiveDate > NOW())
			);

	  IF p_effective = 'Now'
		THEN
		   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableDIDRate4_ as (select * from tmp_RateTableDIDRate_);
         DELETE n1 FROM tmp_RateTableDIDRate_ n1, tmp_RateTableDIDRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
		   AND  n1.RateID = n2.RateID AND n1.OriginationRateID = n2.OriginationRateID;
		END IF;


    IF p_isExport = 0
    THEN

       	SELECT * FROM tmp_RateTableDIDRate_
					ORDER BY
					 CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryDESC') THEN Country
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryASC') THEN Country
                END ASC,
					 CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleDESC') THEN TimezoneTitle
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleASC') THEN TimezoneTitle
                END ASC,
					 CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeDESC') THEN OriginationCode
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeASC') THEN OriginationCode
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CityDESC') THEN City
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CityASC') THEN City
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TariffDESC') THEN Tariff
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TariffASC') THEN Tariff
                END ASC,
	             CASE
	                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccessTypeDESC') THEN AccessType
	             END DESC,
	             CASE
	                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccessTypeASC') THEN AccessType
	             END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OneOffCostDESC') THEN OneOffCost
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OneOffCostASC') THEN OneOffCost
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MonthlyCostDESC') THEN MonthlyCost
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MonthlyCostASC') THEN MonthlyCost
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerCallDESC') THEN CostPerCall
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerCallASC') THEN CostPerCall
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerMinuteDESC') THEN CostPerMinute
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerMinuteASC') THEN CostPerMinute
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerCallDESC') THEN SurchargePerCall
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerCallASC') THEN SurchargePerCall
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerMinuteDESC') THEN SurchargePerMinute
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerMinuteASC') THEN SurchargePerMinute
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerCallDESC') THEN OutpaymentPerCall
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerCallASC') THEN OutpaymentPerCall
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerMinuteDESC') THEN OutpaymentPerMinute
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerMinuteASC') THEN OutpaymentPerMinute
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargesDESC') THEN Surcharges
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargesASC') THEN Surcharges
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ChargebackDESC') THEN Chargeback
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ChargebackASC') THEN Chargeback
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostAmountDESC') THEN CollectionCostAmount
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostAmountASC') THEN CollectionCostAmount
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostPercentageDESC') THEN CollectionCostPercentage
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostPercentageASC') THEN CollectionCostPercentage
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RegistrationCostPerNumberDESC') THEN RegistrationCostPerNumber
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RegistrationCostPerNumberASC') THEN RegistrationCostPerNumber
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateDESC') THEN EffectiveDate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateASC') THEN EffectiveDate
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateDESC') THEN EndDate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateASC') THEN EndDate
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN updated_at
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN updated_at
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ModifiedByDESC') THEN ModifiedBy
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ModifiedByASC') THEN ModifiedBy
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ApprovedByDESC') THEN ApprovedBy
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ApprovedByASC') THEN ApprovedBy
                END ASC
			LIMIT p_RowspPage OFFSET v_OffSet_;

			SELECT
            COUNT(RateID) AS totalcount
        	FROM tmp_RateTableDIDRate_;

    END IF;

	 -- basic view
    IF p_isExport = 10
    THEN
        SELECT
			AccessType,
			Country,
			OriginationCode AS Origination,
			Code AS Prefix,
			City,
			Tariff,
        	TimezoneTitle AS `Time of Day`,
			CONCAT(OneOffCostCurrencySymbol,OneOffCost) AS OneOffCost,
			CONCAT(MonthlyCostCurrencySymbol,MonthlyCost) AS MonthlyCost,
			CONCAT(CostPerCallCurrencySymbol,CostPerCall) AS CostPerCall,
			CONCAT(CostPerMinuteCurrencySymbol,CostPerMinute) AS CostPerMinute,
			CONCAT(SurchargePerCallCurrencySymbol,SurchargePerCall) AS SurchargePerCall,
			CONCAT(SurchargePerMinuteCurrencySymbol,SurchargePerMinute) AS SurchargePerMinute,
			CONCAT(OutpaymentPerCallCurrencySymbol,OutpaymentPerCall) AS OutpaymentPerCall,
			CONCAT(OutpaymentPerMinuteCurrencySymbol,OutpaymentPerMinute) AS OutpaymentPerMinute,
			CONCAT(SurchargesCurrencySymbol,Surcharges) AS Surcharges,
			CONCAT(ChargebackCurrencySymbol,Chargeback) AS Chargeback,
			CONCAT(CollectionCostAmountCurrencySymbol,CollectionCostAmount) AS CollectionCostAmount,
			CollectionCostPercentage,
			CONCAT(RegistrationCostPerNumberCurrencySymbol,RegistrationCostPerNumber) AS RegistrationCostPerNumber,
			EffectiveDate,
			ApprovedStatus
        FROM
			tmp_RateTableDIDRate_;
    END IF;

	 -- advance view
    IF p_isExport = 11
    THEN
        SELECT
			AccessType,
			Country,
			OriginationCode AS Origination,
			Code AS Prefix,
			City,
			Tariff,
        	TimezoneTitle AS `Time of Day`,
			CONCAT(OneOffCostCurrencySymbol,OneOffCost) AS OneOffCost,
			CONCAT(MonthlyCostCurrencySymbol,MonthlyCost) AS MonthlyCost,
			CONCAT(CostPerCallCurrencySymbol,CostPerCall) AS CostPerCall,
			CONCAT(CostPerMinuteCurrencySymbol,CostPerMinute) AS CostPerMinute,
			CONCAT(SurchargePerCallCurrencySymbol,SurchargePerCall) AS SurchargePerCall,
			CONCAT(SurchargePerMinuteCurrencySymbol,SurchargePerMinute) AS SurchargePerMinute,
			CONCAT(OutpaymentPerCallCurrencySymbol,OutpaymentPerCall) AS OutpaymentPerCall,
			CONCAT(OutpaymentPerMinuteCurrencySymbol,OutpaymentPerMinute) AS OutpaymentPerMinute,
			CONCAT(SurchargesCurrencySymbol,Surcharges) AS Surcharges,
			CONCAT(ChargebackCurrencySymbol,Chargeback) AS Chargeback,
			CONCAT(CollectionCostAmountCurrencySymbol,CollectionCostAmount) AS CollectionCostAmount,
			CollectionCostPercentage,
			CONCAT(RegistrationCostPerNumberCurrencySymbol,RegistrationCostPerNumber) AS RegistrationCostPerNumber,
			EffectiveDate,
			CONCAT(ModifiedBy,'\n',updated_at) AS `Modified By/Date`,
			CONCAT(ApprovedBy,'\n',ApprovedDate) AS `Approved By/Date`,
			ApprovedStatus
        FROM
			tmp_RateTableDIDRate_;
    END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_getDiscontinuedRateTableDIDRateGrid`;
DELIMITER //
CREATE PROCEDURE `prc_getDiscontinuedRateTableDIDRateGrid`(
	IN `p_CompanyID` INT,
	IN `p_RateTableID` INT,
	IN `p_TimezonesID` INT,
	IN `p_CountryID` INT,
	IN `p_origination_code` VARCHAR(50),
	IN `p_Code` VARCHAR(50),
	IN `p_City` VARCHAR(50),
	IN `p_Tariff` VARCHAR(50),
	IN `p_AccessType` VARCHAR(50),
	IN `p_ApprovedStatus` TINYINT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
BEGIN
	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableDIDRate_;
	CREATE TEMPORARY TABLE tmp_RateTableDIDRate_ (
		ID INT,
		AccessType varchar(200),
		Country VARCHAR(200),
		OriginationCode VARCHAR(50),
		Code VARCHAR(50),
		City VARCHAR(50),
		Tariff VARCHAR(50),
		TimezoneTitle VARCHAR(50),
		OneOffCost DECIMAL(18,6),
		MonthlyCost DECIMAL(18,6),
		CostPerCall DECIMAL(18,6),
		CostPerMinute DECIMAL(18,6),
		SurchargePerCall DECIMAL(18,6),
		SurchargePerMinute DECIMAL(18,6),
		OutpaymentPerCall DECIMAL(18,6),
		OutpaymentPerMinute DECIMAL(18,6),
		Surcharges DECIMAL(18,6),
		Chargeback DECIMAL(18,6),
		CollectionCostAmount DECIMAL(18,6),
		CollectionCostPercentage DECIMAL(18,6),
		RegistrationCostPerNumber DECIMAL(18,6),
		EffectiveDate DATE,
		EndDate DATE,
		updated_at DATETIME,
		updated_by VARCHAR(50),
		RateTableDIDRateID INT,
		OriginationRateID INT,
		RateID INT,
		ApprovedStatus TINYINT,
		ApprovedBy VARCHAR(50),
		ApprovedDate DATETIME,
		OneOffCostCurrency INT(11),
		MonthlyCostCurrency INT(11),
		CostPerCallCurrency INT(11),
		CostPerMinuteCurrency INT(11),
		SurchargePerCallCurrency INT(11),
		SurchargePerMinuteCurrency INT(11),
		OutpaymentPerCallCurrency INT(11),
		OutpaymentPerMinuteCurrency INT(11),
		SurchargesCurrency INT(11),
		ChargebackCurrency INT(11),
		CollectionCostAmountCurrency INT(11),
		RegistrationCostPerNumberCurrency INT(11),
		OneOffCostCurrencySymbol VARCHAR(255),
		MonthlyCostCurrencySymbol VARCHAR(255),
		CostPerCallCurrencySymbol VARCHAR(255),
		CostPerMinuteCurrencySymbol VARCHAR(255),
		SurchargePerCallCurrencySymbol VARCHAR(255),
		SurchargePerMinuteCurrencySymbol VARCHAR(255),
		OutpaymentPerCallCurrencySymbol VARCHAR(255),
		OutpaymentPerMinuteCurrencySymbol VARCHAR(255),
		SurchargesCurrencySymbol VARCHAR(255),
		ChargebackCurrencySymbol VARCHAR(255),
		CollectionCostAmountCurrencySymbol VARCHAR(255),
		RegistrationCostPerNumberCurrencySymbol VARCHAR(255),
		TimezonesID INT(11),
		INDEX tmp_RateTableDIDRate_RateID (`Code`)
	);

	INSERT INTO tmp_RateTableDIDRate_
	SELECT
		vra.RateTableDIDRateID,
		vra.AccessType,
		tblCountry.Country,
		OriginationRate.Code AS OriginationCode,
		r.Code,
		vra.City,
		vra.Tariff,
		tblTimezones.Title AS TimezoneTitle,
		vra.OneOffCost,
		vra.MonthlyCost,
		vra.CostPerCall,
		vra.CostPerMinute,
		vra.SurchargePerCall,
		vra.SurchargePerMinute,
		vra.OutpaymentPerCall,
		vra.OutpaymentPerMinute,
		vra.Surcharges,
		vra.Chargeback,
		vra.CollectionCostAmount,
		vra.CollectionCostPercentage,
		vra.RegistrationCostPerNumber,
		vra.EffectiveDate,
		vra.EndDate,
		vra.created_at AS updated_at,
		vra.CreatedBy AS updated_by,
		vra.RateTableDIDRateID,
		vra.OriginationRateID,
		vra.RateID,
		vra.ApprovedStatus,
		vra.ApprovedBy,
		vra.ApprovedDate,
		tblOneOffCostCurrency.CurrencyID AS OneOffCostCurrency,
		tblMonthlyCostCurrency.CurrencyID AS MonthlyCostCurrency,
		tblCostPerCallCurrency.CurrencyID AS CostPerCallCurrency,
		tblCostPerMinuteCurrency.CurrencyID AS CostPerMinuteCurrency,
		tblSurchargePerCallCurrency.CurrencyID AS SurchargePerCallCurrency,
		tblSurchargePerMinuteCurrency.CurrencyID AS SurchargePerMinuteCurrency,
		tblOutpaymentPerCallCurrency.CurrencyID AS OutpaymentPerCallCurrency,
		tblOutpaymentPerMinuteCurrency.CurrencyID AS OutpaymentPerMinuteCurrency,
		tblSurchargesCurrency.CurrencyID AS SurchargesCurrency,
		tblChargebackCurrency.CurrencyID AS ChargebackCurrency,
		tblCollectionCostAmountCurrency.CurrencyID AS CollectionCostAmountCurrency,
		tblRegistrationCostPerNumberCurrency.CurrencyID AS RegistrationCostPerNumberCurrency,
		IFNULL(tblOneOffCostCurrency.Symbol,'') AS OneOffCostCurrencySymbol,
		IFNULL(tblMonthlyCostCurrency.Symbol,'') AS MonthlyCostCurrencySymbol,
		IFNULL(tblCostPerCallCurrency.Symbol,'') AS CostPerCallCurrencySymbol,
		IFNULL(tblCostPerMinuteCurrency.Symbol,'') AS CostPerMinuteCurrencySymbol,
		IFNULL(tblSurchargePerCallCurrency.Symbol,'') AS SurchargePerCallCurrencySymbol,
		IFNULL(tblSurchargePerMinuteCurrency.Symbol,'') AS SurchargePerMinuteCurrencySymbol,
		IFNULL(tblOutpaymentPerCallCurrency.Symbol,'') AS OutpaymentPerCallCurrencySymbol,
		IFNULL(tblOutpaymentPerMinuteCurrency.Symbol,'') AS OutpaymentPerMinuteCurrencySymbol,
		IFNULL(tblSurchargesCurrency.Symbol,'') AS SurchargesCurrencySymbol,
		IFNULL(tblChargebackCurrency.Symbol,'') AS ChargebackCurrencySymbol,
		IFNULL(tblCollectionCostAmountCurrency.Symbol,'') AS CollectionCostAmountCurrencySymbol,
		IFNULL(tblRegistrationCostPerNumberCurrency.Symbol,'') AS RegistrationCostPerNumberCurrencySymbol,
		vra.TimezonesID
	FROM
		tblRateTableDIDRateArchive vra
   INNER JOIN tblTimezones
    	  ON tblTimezones.TimezonesID = vra.TimezonesID
	JOIN
		tblRate r ON r.RateID=vra.RateId
	LEFT JOIN
		tblCountry ON tblCountry.CountryID = r.CountryID
   LEFT JOIN
		tblRate AS OriginationRate ON OriginationRate.RateID = vra.OriginationRateID
	LEFT JOIN
		tblRateTableDIDRate vr ON vr.RateTableId = vra.RateTableId AND vr.RateId = vra.RateId AND vr.OriginationRateID = vra.OriginationRateID AND vr.TimezonesID = vra.TimezonesID
	LEFT JOIN tblCurrency AS tblOneOffCostCurrency
		ON tblOneOffCostCurrency.CurrencyID = vra.OneOffCostCurrency
	LEFT JOIN tblCurrency AS tblMonthlyCostCurrency
		ON tblMonthlyCostCurrency.CurrencyID = vra.MonthlyCostCurrency
	LEFT JOIN tblCurrency AS tblCostPerCallCurrency
		ON tblCostPerCallCurrency.CurrencyID = vra.CostPerCallCurrency
	LEFT JOIN tblCurrency AS tblCostPerMinuteCurrency
		ON tblCostPerMinuteCurrency.CurrencyID = vra.CostPerMinuteCurrency
	LEFT JOIN tblCurrency AS tblSurchargePerCallCurrency
		ON tblSurchargePerCallCurrency.CurrencyID = vra.SurchargePerCallCurrency
	LEFT JOIN tblCurrency AS tblSurchargePerMinuteCurrency
		ON tblSurchargePerMinuteCurrency.CurrencyID = vra.SurchargePerMinuteCurrency
	LEFT JOIN tblCurrency AS tblOutpaymentPerCallCurrency
		ON tblOutpaymentPerCallCurrency.CurrencyID = vra.OutpaymentPerCallCurrency
	LEFT JOIN tblCurrency AS tblOutpaymentPerMinuteCurrency
		ON tblOutpaymentPerMinuteCurrency.CurrencyID = vra.OutpaymentPerMinuteCurrency
	LEFT JOIN tblCurrency AS tblSurchargesCurrency
		ON tblSurchargesCurrency.CurrencyID = vra.SurchargesCurrency
	LEFT JOIN tblCurrency AS tblChargebackCurrency
		ON tblChargebackCurrency.CurrencyID = vra.ChargebackCurrency
	LEFT JOIN tblCurrency AS tblCollectionCostAmountCurrency
		ON tblCollectionCostAmountCurrency.CurrencyID = vra.CollectionCostAmountCurrency
	LEFT JOIN tblCurrency AS tblRegistrationCostPerNumberCurrency
		ON tblRegistrationCostPerNumberCurrency.CurrencyID = vra.RegistrationCostPerNumberCurrency
	INNER JOIN tblRateTable
		ON tblRateTable.RateTableId = vra.RateTableId
	LEFT JOIN tblCurrency AS tblRateTableCurrency
		ON tblRateTableCurrency.CurrencyId = tblRateTable.CurrencyID
	WHERE
		r.CompanyID = p_CompanyID AND
		vra.RateTableId = p_RateTableID AND
		(p_TimezonesID IS NULL OR vra.TimezonesID = p_TimezonesID) AND
		(p_CountryID IS NULL OR r.CountryID = p_CountryID) AND
		(p_origination_code is null OR OriginationRate.Code LIKE REPLACE(p_origination_code, '*', '%')) AND
		(p_code IS NULL OR r.Code LIKE REPLACE(p_code, '*', '%')) AND
		(p_City IS NULL OR vra.City LIKE REPLACE(p_City, '*', '%')) AND
		(p_Tariff IS NULL OR vra.Tariff LIKE REPLACE(p_Tariff, '*', '%')) AND
		(p_AccessType IS NULL OR vra.AccessType LIKE REPLACE(p_AccessType, '*', '%')) AND
		(p_ApprovedStatus IS NULL OR vra.ApprovedStatus = p_ApprovedStatus) AND
		vr.RateTableDIDRateID is NULL;

	IF p_isExport = 0
	THEN
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableDIDRate2_ as (select * from tmp_RateTableDIDRate_);
		DELETE
			n1
		FROM
			tmp_RateTableDIDRate_ n1, tmp_RateTableDIDRate2_ n2
		WHERE
			n1.RateID = n2.RateID AND n1.OriginationRateID = n2.OriginationRateID AND n1.TimezonesID = n2.TimezonesID AND n1.City = n2.City AND n1.Tariff = n2.Tariff AND n1.RateTableDIDRateID < n2.RateTableDIDRateID;

		SELECT * FROM tmp_RateTableDIDRate_
		ORDER BY
				 CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryDESC') THEN Country
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryASC') THEN Country
             END ASC,
				 CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleDESC') THEN TimezoneTitle
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleASC') THEN TimezoneTitle
             END ASC,
				 CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeDESC') THEN OriginationCode
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeASC') THEN OriginationCode
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
             END ASC,
             CASE
					  WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CityDESC') THEN City
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CityASC') THEN City
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TariffDESC') THEN Tariff
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TariffASC') THEN Tariff
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccessTypeDESC') THEN AccessType
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccessTypeASC') THEN AccessType
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OneOffCostDESC') THEN OneOffCost
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OneOffCostASC') THEN OneOffCost
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MonthlyCostDESC') THEN MonthlyCost
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MonthlyCostASC') THEN MonthlyCost
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerCallDESC') THEN CostPerCall
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerCallASC') THEN CostPerCall
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerMinuteDESC') THEN CostPerMinute
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerMinuteASC') THEN CostPerMinute
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerCallDESC') THEN SurchargePerCall
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerCallASC') THEN SurchargePerCall
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerMinuteDESC') THEN SurchargePerMinute
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerMinuteASC') THEN SurchargePerMinute
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerCallDESC') THEN OutpaymentPerCall
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerCallASC') THEN OutpaymentPerCall
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerMinuteDESC') THEN OutpaymentPerMinute
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerMinuteASC') THEN OutpaymentPerMinute
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargesDESC') THEN Surcharges
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargesASC') THEN Surcharges
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ChargebackDESC') THEN Chargeback
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ChargebackASC') THEN Chargeback
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostAmountDESC') THEN CollectionCostAmount
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostAmountASC') THEN CollectionCostAmount
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostPercentageDESC') THEN CollectionCostPercentage
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostPercentageASC') THEN CollectionCostPercentage
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RegistrationCostPerNumberDESC') THEN RegistrationCostPerNumber
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RegistrationCostPerNumberASC') THEN RegistrationCostPerNumber
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateDESC') THEN EffectiveDate
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateASC') THEN EffectiveDate
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateDESC') THEN EndDate
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateASC') THEN EndDate
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN updated_at
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN updated_at
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ModifiedByDESC') THEN updated_by
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ModifiedByASC') THEN updated_by
             END ASC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ApprovedByDESC') THEN ApprovedBy
             END DESC,
             CASE
                 WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ApprovedByASC') THEN ApprovedBy
             END ASC
		LIMIT
			p_RowspPage
		OFFSET
			v_OffSet_;

		SELECT
			COUNT(code) AS totalcount
		FROM tmp_RateTableDIDRate_;

	END IF;

	-- basic view
	IF p_isExport = 10
	THEN
		SELECT
			AccessType,
			Country,
			OriginationCode AS Origination,
			Code AS Prefix,
			City,
			Tariff,
        	TimezoneTitle AS `Time of Day`,
			CONCAT(OneOffCostCurrencySymbol,OneOffCost) AS OneOffCost,
			CONCAT(MonthlyCostCurrencySymbol,MonthlyCost) AS MonthlyCost,
			CONCAT(CostPerCallCurrencySymbol,CostPerCall) AS CostPerCall,
			CONCAT(CostPerMinuteCurrencySymbol,CostPerMinute) AS CostPerMinute,
			CONCAT(SurchargePerCallCurrencySymbol,SurchargePerCall) AS SurchargePerCall,
			CONCAT(SurchargePerMinuteCurrencySymbol,SurchargePerMinute) AS SurchargePerMinute,
			CONCAT(OutpaymentPerCallCurrencySymbol,OutpaymentPerCall) AS OutpaymentPerCall,
			CONCAT(OutpaymentPerMinuteCurrencySymbol,OutpaymentPerMinute) AS OutpaymentPerMinute,
			CONCAT(SurchargesCurrencySymbol,Surcharges) AS Surcharges,
			CONCAT(ChargebackCurrencySymbol,Chargeback) AS Chargeback,
			CONCAT(CollectionCostAmountCurrencySymbol,CollectionCostAmount) AS CollectionCostAmount,
			CollectionCostPercentage,
			CONCAT(RegistrationCostPerNumberCurrencySymbol,RegistrationCostPerNumber) AS RegistrationCostPerNumber,
			ApprovedStatus
		FROM tmp_RateTableDIDRate_;
	END IF;

	-- advance view
	IF p_isExport = 11
	THEN
		SELECT
			AccessType,
			Country,
			OriginationCode AS Origination,
			Code AS Prefix,
			City,
			Tariff,
        	TimezoneTitle AS `Time of Day`,
			CONCAT(OneOffCostCurrencySymbol,OneOffCost) AS OneOffCost,
			CONCAT(MonthlyCostCurrencySymbol,MonthlyCost) AS MonthlyCost,
			CONCAT(CostPerCallCurrencySymbol,CostPerCall) AS CostPerCall,
			CONCAT(CostPerMinuteCurrencySymbol,CostPerMinute) AS CostPerMinute,
			CONCAT(SurchargePerCallCurrencySymbol,SurchargePerCall) AS SurchargePerCall,
			CONCAT(SurchargePerMinuteCurrencySymbol,SurchargePerMinute) AS SurchargePerMinute,
			CONCAT(OutpaymentPerCallCurrencySymbol,OutpaymentPerCall) AS OutpaymentPerCall,
			CONCAT(OutpaymentPerMinuteCurrencySymbol,OutpaymentPerMinute) AS OutpaymentPerMinute,
			CONCAT(SurchargesCurrencySymbol,Surcharges) AS Surcharges,
			CONCAT(ChargebackCurrencySymbol,Chargeback) AS Chargeback,
			CONCAT(CollectionCostAmountCurrencySymbol,CollectionCostAmount) AS CollectionCostAmount,
			CollectionCostPercentage,
			CONCAT(RegistrationCostPerNumberCurrencySymbol,RegistrationCostPerNumber) AS RegistrationCostPerNumber,
			CONCAT(updated_at,'\n',updated_by) AS `Modified Date/By`,
			CONCAT(ApprovedBy,'\n',ApprovedDate) AS `Approved By/Date`,
			ApprovedStatus
		FROM tmp_RateTableDIDRate_;
	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_ArchiveOldRateTableDIDRate`;
DELIMITER //
CREATE PROCEDURE `prc_ArchiveOldRateTableDIDRate`(
	IN `p_RateTableIds` LONGTEXT,
	IN `p_TimezonesIDs` LONGTEXT,
	IN `p_DeletedBy` TEXT
)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	UPDATE
		tblRateTableDIDRate rtr
	INNER JOIN tblRateTableDIDRate rtr2
		ON rtr2.RateTableId = rtr.RateTableId
		AND rtr2.RateID = rtr.RateID
		AND rtr2.OriginationRateID = rtr.OriginationRateID
		AND rtr2.TimezonesID = rtr.TimezonesID
		AND rtr2.City = rtr.City
		AND rtr2.Tariff = rtr.Tariff
	SET
		rtr.EndDate=NOW()
	WHERE
		(FIND_IN_SET(rtr.RateTableId,p_RateTableIds) != 0 AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(rtr.TimezonesID,p_TimezonesIDs) != 0) AND rtr.EffectiveDate <= NOW()) AND
		(FIND_IN_SET(rtr2.RateTableId,p_RateTableIds) != 0 AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(rtr2.TimezonesID,p_TimezonesIDs) != 0) AND rtr2.EffectiveDate <= NOW()) AND
		rtr.EffectiveDate < rtr2.EffectiveDate AND rtr.RateTableDIDRateID != rtr2.RateTableDIDRateID;


	INSERT INTO tblRateTableDIDRateArchive
	(
		RateTableDIDRateID,
		OriginationRateID,
		RateId,
		RateTableId,
		TimezonesID,
		EffectiveDate,
		EndDate,
		City,
		Tariff,
		AccessType,
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
		created_at,
		updated_at,
		CreatedBy,
		ModifiedBy,
		ApprovedStatus,
		ApprovedBy,
		ApprovedDate,
		Notes
	)
	SELECT DISTINCT -- null ,
		`RateTableDIDRateID`,
		`OriginationRateID`,
		`RateId`,
		`RateTableId`,
		`TimezonesID`,
		`EffectiveDate`,
		IFNULL(`EndDate`,date(now())) as EndDate,
		`City`,
		`Tariff`,
		`AccessType`,
		`OneOffCost`,
		`MonthlyCost`,
		`CostPerCall`,
		`CostPerMinute`,
		`SurchargePerCall`,
		`SurchargePerMinute`,
		`OutpaymentPerCall`,
		`OutpaymentPerMinute`,
		`Surcharges`,
		`Chargeback`,
		`CollectionCostAmount`,
		`CollectionCostPercentage`,
		`RegistrationCostPerNumber`,
		`OneOffCostCurrency`,
        `MonthlyCostCurrency`,
        `CostPerCallCurrency`,
        `CostPerMinuteCurrency`,
        `SurchargePerCallCurrency`,
        `SurchargePerMinuteCurrency`,
        `OutpaymentPerCallCurrency`,
        `OutpaymentPerMinuteCurrency`,
        `SurchargesCurrency`,
        `ChargebackCurrency`,
        `CollectionCostAmountCurrency`,
        `RegistrationCostPerNumberCurrency`,
		now() as `created_at`,
		`updated_at`,
		p_DeletedBy AS `CreatedBy`,
		`ModifiedBy`,
		`ApprovedStatus`,
		`ApprovedBy`,
		`ApprovedDate`,
		concat('Ends Today rates @ ' , now() ) as `Notes`
	FROM tblRateTableDIDRate
	WHERE FIND_IN_SET(RateTableId,p_RateTableIds) != 0 AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(TimezonesID,p_TimezonesIDs) != 0) AND EndDate <= NOW();



	DELETE  rtr
	FROM tblRateTableDIDRate rtr
	inner join tblRateTableDIDRateArchive rtra
		on rtr.RateTableDIDRateID = rtra.RateTableDIDRateID
	WHERE  FIND_IN_SET(rtr.RateTableId,p_RateTableIds) != 0 AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(rtr.TimezonesID,p_TimezonesIDs) != 0);



	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_ArchiveOldRateTableDIDRateAA`;
DELIMITER //
CREATE PROCEDURE `prc_ArchiveOldRateTableDIDRateAA`(
	IN `p_RateTableIds` LONGTEXT,
	IN `p_TimezonesIDs` LONGTEXT,
	IN `p_DeletedBy` TEXT
)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	UPDATE
		tblRateTableDIDRateAA rtr
	INNER JOIN tblRateTableDIDRateAA rtr2
		ON rtr2.RateTableId = rtr.RateTableId
		AND rtr2.RateID = rtr.RateID
		AND rtr2.OriginationRateID = rtr.OriginationRateID
		AND rtr2.City = rtr.City
		AND rtr2.Tariff = rtr.Tariff
		AND rtr2.TimezonesID = rtr.TimezonesID
	SET
		rtr.EndDate=NOW()
	WHERE
		(FIND_IN_SET(rtr.RateTableId,p_RateTableIds) != 0 AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(rtr.TimezonesID,p_TimezonesIDs) != 0) AND rtr.EffectiveDate <= NOW()) AND
		(FIND_IN_SET(rtr2.RateTableId,p_RateTableIds) != 0 AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(rtr2.TimezonesID,p_TimezonesIDs) != 0) AND rtr2.EffectiveDate <= NOW()) AND
		rtr.EffectiveDate < rtr2.EffectiveDate AND rtr.RateTableDIDRateAAID != rtr2.RateTableDIDRateAAID;


	INSERT INTO tblRateTableDIDRateArchive
	(
		RateTableDIDRateID,
		OriginationRateID,
		RateId,
		RateTableId,
		TimezonesID,
		EffectiveDate,
		EndDate,
		City,
		Tariff,
		AccessType,
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
		created_at,
		updated_at,
		CreatedBy,
		ModifiedBy,
		ApprovedStatus,
		ApprovedBy,
		ApprovedDate,
		Notes
	)
	SELECT DISTINCT -- null ,
		`RateTableDIDRateAAID`,
		`OriginationRateID`,
		`RateId`,
		`RateTableId`,
		`TimezonesID`,
		`EffectiveDate`,
		IFNULL(`EndDate`,date(now())) as EndDate,
		`City`,
		`Tariff`,
		`AccessType`,
		`OneOffCost`,
		`MonthlyCost`,
		`CostPerCall`,
		`CostPerMinute`,
		`SurchargePerCall`,
		`SurchargePerMinute`,
		`OutpaymentPerCall`,
		`OutpaymentPerMinute`,
		`Surcharges`,
		`Chargeback`,
		`CollectionCostAmount`,
		`CollectionCostPercentage`,
		`RegistrationCostPerNumber`,
		`OneOffCostCurrency`,
        `MonthlyCostCurrency`,
        `CostPerCallCurrency`,
        `CostPerMinuteCurrency`,
        `SurchargePerCallCurrency`,
        `SurchargePerMinuteCurrency`,
        `OutpaymentPerCallCurrency`,
        `OutpaymentPerMinuteCurrency`,
        `SurchargesCurrency`,
        `ChargebackCurrency`,
        `CollectionCostAmountCurrency`,
        `RegistrationCostPerNumberCurrency`,
		now() as `created_at`,
		`updated_at`,
		p_DeletedBy AS `CreatedBy`,
		`ModifiedBy`,
		`ApprovedStatus`,
		`ApprovedBy`,
		`ApprovedDate`,
		concat('Ends Today rates @ ' , now() ) as `Notes`
	FROM tblRateTableDIDRateAA
	WHERE
		FIND_IN_SET(RateTableId,p_RateTableIds) != 0
		AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(TimezonesID,p_TimezonesIDs) != 0)
		AND EndDate <= NOW()
		AND ApprovedStatus = 2; -- only rejected rates will be archive



	DELETE  rtr
	FROM tblRateTableDIDRateAA rtr
	WHERE
		FIND_IN_SET(rtr.RateTableId,p_RateTableIds) != 0
		AND (p_TimezonesIDs IS NULL OR FIND_IN_SET(rtr.TimezonesID,p_TimezonesIDs) != 0)
		AND EndDate <= NOW();



	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_RateTableDIDRateAAUpdateDelete`;
DELIMITER //
CREATE PROCEDURE `prc_RateTableDIDRateAAUpdateDelete`(
	IN `p_RateTableId` INT,
	IN `p_RateTableDIDRateAAId` LONGTEXT,
	IN `p_OriginationRateID` INT,
	IN `p_EffectiveDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_City` VARCHAR(50),
	IN `p_Tariff` VARCHAR(50),
	IN `p_AccessType` VARCHAR(200),
	IN `p_OneOffCost` VARCHAR(255),
	IN `p_MonthlyCost` VARCHAR(255),
	IN `p_CostPerCall` VARCHAR(255),
	IN `p_CostPerMinute` VARCHAR(255),
	IN `p_SurchargePerCall` VARCHAR(255),
	IN `p_SurchargePerMinute` VARCHAR(255),
	IN `p_OutpaymentPerCall` VARCHAR(255),
	IN `p_OutpaymentPerMinute` VARCHAR(255),
	IN `p_Surcharges` VARCHAR(255),
	IN `p_Chargeback` VARCHAR(255),
	IN `p_CollectionCostAmount` VARCHAR(255),
	IN `p_CollectionCostPercentage` VARCHAR(255),
	IN `p_RegistrationCostPerNumber` VARCHAR(255),
	IN `p_OneOffCostCurrency` DECIMAL(18,6),
	IN `p_MonthlyCostCurrency` DECIMAL(18,6),
	IN `p_CostPerCallCurrency` DECIMAL(18,6),
	IN `p_CostPerMinuteCurrency` DECIMAL(18,6),
	IN `p_SurchargePerCallCurrency` DECIMAL(18,6),
	IN `p_SurchargePerMinuteCurrency` DECIMAL(18,6),
	IN `p_OutpaymentPerCallCurrency` DECIMAL(18,6),
	IN `p_OutpaymentPerMinuteCurrency` DECIMAL(18,6),
	IN `p_SurchargesCurrency` DECIMAL(18,6),
	IN `p_ChargebackCurrency` DECIMAL(18,6),
	IN `p_CollectionCostAmountCurrency` DECIMAL(18,6),
	IN `p_RegistrationCostPerNumberCurrency` DECIMAL(18,6),
	IN `p_Critearea_CountryId` INT,
	IN `p_Critearea_Code` varchar(50),
	IN `p_Critearea_Description` varchar(200),
	IN `p_Critearea_City` VARCHAR(50),
	IN `p_Critearea_Tariff` VARCHAR(50),
	IN `p_Critearea_OriginationCode` VARCHAR(50),
	IN `p_Critearea_OriginationDescription` VARCHAR(200),
	IN `p_Critearea_Effective` VARCHAR(50),
	IN `p_TimezonesID` INT,
	IN `p_Critearea_ApprovedStatus` TINYINT,
	IN `p_ModifiedBy` varchar(50),
	IN `p_Critearea` INT,
	IN `p_action` INT
)
ThisSP:BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableDIDRate_;
	CREATE TEMPORARY TABLE tmp_TempRateTableDIDRate_ (
		`RateTableDIDRateAAId` int(11) NOT NULL,
		`OriginationRateID` int(11) NOT NULL DEFAULT '0',
		`RateId` int(11) NOT NULL,
		`RateTableId` int(11) NOT NULL,
		`TimezonesID` int(11) NOT NULL,
		`City` VARCHAR(50) NOT NULL DEFAULT '',
		`Tariff` VARCHAR(50) NOT NULL DEFAULT '',
		`AccessType` VARCHAR(200) NULL DEFAULT NULL,
		`OneOffCost` decimal(18,6) NULL DEFAULT NULL,
		`MonthlyCost` decimal(18,6) NULL DEFAULT NULL,
		`CostPerCall` decimal(18,6) NULL DEFAULT NULL,
		`CostPerMinute` decimal(18,6) NULL DEFAULT NULL,
		`SurchargePerCall` decimal(18,6) NULL DEFAULT NULL,
		`SurchargePerMinute` decimal(18,6) NULL DEFAULT NULL,
		`OutpaymentPerCall` decimal(18,6) NULL DEFAULT NULL,
		`OutpaymentPerMinute` decimal(18,6) NULL DEFAULT NULL,
		`Surcharges` decimal(18,6) NULL DEFAULT NULL,
		`Chargeback` decimal(18,6) NULL DEFAULT NULL,
		`CollectionCostAmount` decimal(18,6) NULL DEFAULT NULL,
		`CollectionCostPercentage` decimal(18,6) NULL DEFAULT NULL,
		`RegistrationCostPerNumber` decimal(18,6) NULL DEFAULT NULL,
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` datetime NOT NULL,
		`EndDate` datetime DEFAULT NULL,
		`created_at` datetime DEFAULT NULL,
		`updated_at` datetime DEFAULT NULL,
		`CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		`ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		`ApprovedStatus` tinyint,
		`ApprovedBy` varchar(50),
		`ApprovedDate` datetime
	);

	INSERT INTO tmp_TempRateTableDIDRate_
	SELECT
		rtr.RateTableDIDRateAAId,
		IF(rtr.OriginationRateID=0,IFNULL(p_OriginationRateID,rtr.OriginationRateID),rtr.OriginationRateID) AS OriginationRateID,
		rtr.RateId,
		rtr.RateTableId,
		rtr.TimezonesID,
		IFNULL(p_City,rtr.City) AS City,
		IFNULL(p_Tariff,rtr.Tariff) AS Tariff,
		IFNULL(p_AccessType,rtr.AccessType) AS AccessType,
		IF(p_OneOffCost IS NOT NULL,IF(p_OneOffCost='NULL',NULL,p_OneOffCost),rtr.OneOffCost) AS OneOffCost,
		IF(p_MonthlyCost IS NOT NULL,IF(p_MonthlyCost='NULL',NULL,p_MonthlyCost),rtr.MonthlyCost) AS MonthlyCost,
		IF(p_CostPerCall IS NOT NULL,IF(p_CostPerCall='NULL',NULL,p_CostPerCall),rtr.CostPerCall) AS CostPerCall,
		IF(p_CostPerMinute IS NOT NULL,IF(p_CostPerMinute='NULL',NULL,p_CostPerMinute),rtr.CostPerMinute) AS CostPerMinute,
		IF(p_SurchargePerCall IS NOT NULL,IF(p_SurchargePerCall='NULL',NULL,p_SurchargePerCall),rtr.SurchargePerCall) AS SurchargePerCall,
		IF(p_SurchargePerMinute IS NOT NULL,IF(p_SurchargePerMinute='NULL',NULL,p_SurchargePerMinute),rtr.SurchargePerMinute) AS SurchargePerMinute,
		IF(p_OutpaymentPerCall IS NOT NULL,IF(p_OutpaymentPerCall='NULL',NULL,p_OutpaymentPerCall),rtr.OutpaymentPerCall) AS OutpaymentPerCall,
		IF(p_OutpaymentPerMinute IS NOT NULL,IF(p_OutpaymentPerMinute='NULL',NULL,p_OutpaymentPerMinute),rtr.OutpaymentPerMinute) AS OutpaymentPerMinute,
		IF(p_Surcharges IS NOT NULL,IF(p_Surcharges='NULL',NULL,p_Surcharges),rtr.Surcharges) AS Surcharges,
		IF(p_Chargeback IS NOT NULL,IF(p_Chargeback='NULL',NULL,p_Chargeback),rtr.Chargeback) AS Chargeback,
		IF(p_CollectionCostAmount IS NOT NULL,IF(p_CollectionCostAmount='NULL',NULL,p_CollectionCostAmount),rtr.CollectionCostAmount) AS CollectionCostAmount,
		IF(p_CollectionCostPercentage IS NOT NULL,IF(p_CollectionCostPercentage='NULL',NULL,p_CollectionCostPercentage),rtr.CollectionCostPercentage) AS CollectionCostPercentage,
		IF(p_RegistrationCostPerNumber IS NOT NULL,IF(p_RegistrationCostPerNumber='NULL',NULL,p_RegistrationCostPerNumber),rtr.RegistrationCostPerNumber) AS RegistrationCostPerNumber,
		IFNULL(p_OneOffCostCurrency,rtr.OneOffCostCurrency) AS OneOffCostCurrency,
		IFNULL(p_MonthlyCostCurrency,rtr.MonthlyCostCurrency) AS MonthlyCostCurrency,
		IFNULL(p_CostPerCallCurrency,rtr.CostPerCallCurrency) AS CostPerCallCurrency,
		IFNULL(p_CostPerMinuteCurrency,rtr.CostPerMinuteCurrency) AS CostPerMinuteCurrency,
		IFNULL(p_SurchargePerCallCurrency,rtr.SurchargePerCallCurrency) AS SurchargePerCallCurrency,
		IFNULL(p_SurchargePerMinuteCurrency,rtr.SurchargePerMinuteCurrency) AS SurchargePerMinuteCurrency,
		IFNULL(p_OutpaymentPerCallCurrency,rtr.OutpaymentPerCallCurrency) AS OutpaymentPerCallCurrency,
		IFNULL(p_OutpaymentPerMinuteCurrency,rtr.OutpaymentPerMinuteCurrency) AS OutpaymentPerMinuteCurrency,
		IFNULL(p_SurchargesCurrency,rtr.SurchargesCurrency) AS SurchargesCurrency,
		IFNULL(p_ChargebackCurrency,rtr.ChargebackCurrency) AS ChargebackCurrency,
		IFNULL(p_CollectionCostAmountCurrency,rtr.CollectionCostAmountCurrency) AS CollectionCostAmountCurrency,
		IFNULL(p_RegistrationCostPerNumberCurrency,rtr.RegistrationCostPerNumberCurrency) AS RegistrationCostPerNumberCurrency,
		IFNULL(p_EffectiveDate,rtr.EffectiveDate) AS EffectiveDate,
		IFNULL(p_EndDate,rtr.EndDate) AS EndDate,
		rtr.created_at,
		NOW() AS updated_at,
		rtr.CreatedBy,
		p_ModifiedBy AS ModifiedBy,
		rtr.ApprovedStatus,
		NULL AS ApprovedBy,
		NULL AS ApprovedDate
	FROM
		tblRateTableDIDRateAA rtr
	INNER JOIN
		tblRate r ON r.RateID = rtr.RateId
	LEFT JOIN
		tblRate r2 ON r2.RateID = rtr.OriginationRateID
	WHERE
		(
			p_EffectiveDate IS NULL OR (
				(rtr.RateID,rtr.OriginationRateID,rtr.TimezonesID) NOT IN (
						SELECT
							RateID,OriginationRateID,TimezonesID
						FROM
							tblRateTableDIDRateAA
						WHERE
							EffectiveDate=p_EffectiveDate AND (p_TimezonesID IS NULL OR TimezonesID = p_TimezonesID) AND City=p_City AND Tariff=p_Tariff AND
							((p_Critearea = 0 AND (FIND_IN_SET(RateTableDIDRateAAID,p_RateTableDIDRateAAID) = 0 )) OR p_Critearea = 1) AND
							RateTableId = p_RateTableId
				)
			)
		)
		AND
		(
			(p_Critearea = 0 AND (FIND_IN_SET(rtr.RateTableDIDRateAAID,p_RateTableDIDRateAAID) != 0 )) OR
			(
				p_Critearea = 1 AND
				(
					((p_Critearea_CountryId IS NULL) OR (p_Critearea_CountryId IS NOT NULL AND r.CountryId = p_Critearea_CountryId)) AND
					((p_Critearea_OriginationCode IS NULL) OR (p_Critearea_OriginationCode IS NOT NULL AND r2.Code LIKE REPLACE(p_Critearea_OriginationCode,'*', '%'))) AND
					((p_Critearea_OriginationDescription IS NULL) OR (p_Critearea_OriginationDescription IS NOT NULL AND r2.Description LIKE REPLACE(p_Critearea_OriginationDescription,'*', '%'))) AND
					((p_Critearea_Code IS NULL) OR (p_Critearea_Code IS NOT NULL AND r.Code LIKE REPLACE(p_Critearea_Code,'*', '%'))) AND
					((p_Critearea_Description IS NULL) OR (p_Critearea_Description IS NOT NULL AND r.Description LIKE REPLACE(p_Critearea_Description,'*', '%'))) AND
					(p_Critearea_City IS NULL OR rtr.City LIKE REPLACE(p_Critearea_City, '*', '%')) AND
					(p_Critearea_Tariff IS NULL OR rtr.Tariff LIKE REPLACE(p_Critearea_Tariff, '*', '%')) AND
					(p_Critearea_ApprovedStatus IS NULL OR rtr.ApprovedStatus = p_Critearea_ApprovedStatus) AND
					(
						p_Critearea_Effective = 'All' OR
						(p_Critearea_Effective = 'Now' AND rtr.EffectiveDate <= NOW() ) OR
						(p_Critearea_Effective = 'Future' AND rtr.EffectiveDate > NOW() )
					)
				)
			)
		) AND
		rtr.RateTableId = p_RateTableId AND
		(p_TimezonesID IS NULL OR 	rtr.TimezonesID = p_TimezonesID);


	IF p_action = 1
	THEN

		IF p_EffectiveDate IS NOT NULL
		THEN
			CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempRateTableDIDRate_2 as (select * from tmp_TempRateTableDIDRate_);
			DELETE n1 FROM tmp_TempRateTableDIDRate_ n1, tmp_TempRateTableDIDRate_2 n2 WHERE n1.RateTableDIDRateAAID < n2.RateTableDIDRateAAID AND  n1.RateID = n2.RateID AND n1.OriginationRateID = n2.OriginationRateID AND n1.TimezonesID = n2.TimezonesID;
		END IF;

		-- delete records which can be duplicates, we will not update them
		DELETE n1.* FROM tmp_TempRateTableDIDRate_ n1, tblRateTableDIDRateAA n2 WHERE n1.RateTableDIDRateAAID <> n2.RateTableDIDRateAAID AND n1.RateTableID = n2.RateTableID AND n1.TimezonesID = n2.TimezonesID AND n1.EffectiveDate = n2.EffectiveDate AND n1.RateID = n2.RateID AND n1.OriginationRateID = n2.OriginationRateID AND n1.City=n2.City AND n1.Tariff=n2.Tariff AND n2.RateTableID=p_RateTableId;

		-- remove rejected rates from temp table while updating so, it can't be update and delete
		DELETE n1 FROM tmp_TempRateTableDIDRate_ n1 WHERE ApprovedStatus = 2;

		/*
			it was creating history evan if no columns are updating of row
			so, using this query we are removing rows which are not updating any columns
		*/
		DELETE
			temp
		FROM
			tmp_TempRateTableDIDRate_ temp
		JOIN
			tblRateTableDIDRateAA rtr ON rtr.RateTableDIDRateAAID = temp.RateTableDIDRateAAID
		WHERE
			(rtr.OriginationRateID = temp.OriginationRateID) AND
			(rtr.EffectiveDate = temp.EffectiveDate) AND
			(rtr.TimezonesID = temp.TimezonesID) AND
			(rtr.City = temp.City) AND
			(rtr.Tariff = temp.Tariff) AND
			((rtr.AccessType IS NULL && temp.AccessType IS NULL) || rtr.AccessType = temp.AccessType) AND
			((rtr.OneOffCost IS NULL && temp.OneOffCost IS NULL) || rtr.OneOffCost = temp.OneOffCost) AND
			((rtr.MonthlyCost IS NULL && temp.MonthlyCost IS NULL) || rtr.MonthlyCost = temp.MonthlyCost) AND
			((rtr.CostPerCall IS NULL && temp.CostPerCall IS NULL) || rtr.CostPerCall = temp.CostPerCall) AND
			((rtr.CostPerMinute IS NULL && temp.CostPerMinute IS NULL) || rtr.CostPerMinute = temp.CostPerMinute) AND
			((rtr.SurchargePerCall IS NULL && temp.SurchargePerCall IS NULL) || rtr.SurchargePerCall = temp.SurchargePerCall) AND
			((rtr.SurchargePerMinute IS NULL && temp.SurchargePerMinute IS NULL) || rtr.SurchargePerMinute = temp.SurchargePerMinute) AND
			((rtr.OutpaymentPerCall IS NULL && temp.OutpaymentPerCall IS NULL) || rtr.OutpaymentPerCall = temp.OutpaymentPerCall) AND
			((rtr.OutpaymentPerMinute IS NULL && temp.OutpaymentPerMinute IS NULL) || rtr.OutpaymentPerMinute = temp.OutpaymentPerMinute) AND
			((rtr.Surcharges IS NULL && temp.Surcharges IS NULL) || rtr.Surcharges = temp.Surcharges) AND
			((rtr.Chargeback IS NULL && temp.Chargeback IS NULL) || rtr.Chargeback = temp.Chargeback) AND
			((rtr.CollectionCostAmount IS NULL && temp.CollectionCostAmount IS NULL) || rtr.CollectionCostAmount = temp.CollectionCostAmount) AND
			((rtr.CollectionCostPercentage IS NULL && temp.CollectionCostPercentage IS NULL) || rtr.CollectionCostPercentage = temp.CollectionCostPercentage) AND
			((rtr.RegistrationCostPerNumber IS NULL && temp.RegistrationCostPerNumber IS NULL) || rtr.RegistrationCostPerNumber = temp.RegistrationCostPerNumber) AND
			((rtr.OneOffCostCurrency IS NULL && temp.OneOffCostCurrency IS NULL) || rtr.OneOffCostCurrency = temp.OneOffCostCurrency) AND
			((rtr.MonthlyCostCurrency IS NULL && temp.MonthlyCostCurrency IS NULL) || rtr.MonthlyCostCurrency = temp.MonthlyCostCurrency) AND
			((rtr.CostPerCallCurrency IS NULL && temp.CostPerCallCurrency IS NULL) || rtr.CostPerCallCurrency = temp.CostPerCallCurrency) AND
			((rtr.CostPerMinuteCurrency IS NULL && temp.CostPerMinuteCurrency IS NULL) || rtr.CostPerMinuteCurrency = temp.CostPerMinuteCurrency) AND
			((rtr.SurchargePerCallCurrency IS NULL && temp.SurchargePerCallCurrency IS NULL) || rtr.SurchargePerCallCurrency = temp.SurchargePerCallCurrency) AND
			((rtr.SurchargePerMinuteCurrency IS NULL && temp.SurchargePerMinuteCurrency IS NULL) || rtr.SurchargePerMinuteCurrency = temp.SurchargePerMinuteCurrency) AND
			((rtr.OutpaymentPerCallCurrency IS NULL && temp.OutpaymentPerCallCurrency IS NULL) || rtr.OutpaymentPerCallCurrency = temp.OutpaymentPerCallCurrency) AND
			((rtr.OutpaymentPerMinuteCurrency IS NULL && temp.OutpaymentPerMinuteCurrency IS NULL) || rtr.OutpaymentPerMinuteCurrency = temp.OutpaymentPerMinuteCurrency) AND
			((rtr.SurchargesCurrency IS NULL && temp.SurchargesCurrency IS NULL) || rtr.SurchargesCurrency = temp.SurchargesCurrency) AND
			((rtr.ChargebackCurrency IS NULL && temp.ChargebackCurrency IS NULL) || rtr.ChargebackCurrency = temp.ChargebackCurrency) AND
			((rtr.CollectionCostAmountCurrency IS NULL && temp.CollectionCostAmountCurrency IS NULL) || rtr.CollectionCostAmountCurrency = temp.CollectionCostAmountCurrency) AND
			((rtr.RegistrationCostPerNumberCurrency IS NULL && temp.RegistrationCostPerNumberCurrency IS NULL) || rtr.RegistrationCostPerNumberCurrency = temp.RegistrationCostPerNumberCurrency);

	END IF;


	UPDATE
		tblRateTableDIDRateAA rtr
	INNER JOIN
		tmp_TempRateTableDIDRate_ temp ON temp.RateTableDIDRateAAID = rtr.RateTableDIDRateAAID
	SET
		rtr.EndDate = NOW()
	WHERE
		temp.RateTableDIDRateAAID = rtr.RateTableDIDRateAAID;

	CALL prc_ArchiveOldRateTableDIDRateAA(p_RateTableId,p_TimezonesID,p_ModifiedBy);

	IF p_action = 1
	THEN

		INSERT INTO tblRateTableDIDRateAA (
			OriginationRateID,
			RateId,
			RateTableId,
			TimezonesID,
			City,
			Tariff,
			AccessType,
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
			created_at,
			updated_at,
			CreatedBy,
			ModifiedBy,
			ApprovedStatus,
			ApprovedBy,
			ApprovedDate
		)
		SELECT
			OriginationRateID,
			RateId,
			RateTableId,
			TimezonesID,
			City,
			Tariff,
			AccessType,
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
			created_at,
			updated_at,
			CreatedBy,
			ModifiedBy,
			ApprovedStatus,
			ApprovedBy,
			ApprovedDate
		FROM
			tmp_TempRateTableDIDRate_
		WHERE
			ApprovedStatus = 0; -- only allow awaiting approval rates to be updated

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_RateTableDIDRateUpdateDelete`;
DELIMITER //
CREATE PROCEDURE `prc_RateTableDIDRateUpdateDelete`(
	IN `p_RateTableId` INT,
	IN `p_RateTableDIDRateId` LONGTEXT,
	IN `p_OriginationRateID` INT,
	IN `p_EffectiveDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_City` VARCHAR(50),
	IN `p_Tariff` VARCHAR(50),
	IN `p_AccessType` VARCHAR(200),
	IN `p_OneOffCost` VARCHAR(255),
	IN `p_MonthlyCost` VARCHAR(255),
	IN `p_CostPerCall` VARCHAR(255),
	IN `p_CostPerMinute` VARCHAR(255),
	IN `p_SurchargePerCall` VARCHAR(255),
	IN `p_SurchargePerMinute` VARCHAR(255),
	IN `p_OutpaymentPerCall` VARCHAR(255),
	IN `p_OutpaymentPerMinute` VARCHAR(255),
	IN `p_Surcharges` VARCHAR(255),
	IN `p_Chargeback` VARCHAR(255),
	IN `p_CollectionCostAmount` VARCHAR(255),
	IN `p_CollectionCostPercentage` VARCHAR(255),
	IN `p_RegistrationCostPerNumber` VARCHAR(255),
	IN `p_OneOffCostCurrency` DECIMAL(18,6),
	IN `p_MonthlyCostCurrency` DECIMAL(18,6),
	IN `p_CostPerCallCurrency` DECIMAL(18,6),
	IN `p_CostPerMinuteCurrency` DECIMAL(18,6),
	IN `p_SurchargePerCallCurrency` DECIMAL(18,6),
	IN `p_SurchargePerMinuteCurrency` DECIMAL(18,6),
	IN `p_OutpaymentPerCallCurrency` DECIMAL(18,6),
	IN `p_OutpaymentPerMinuteCurrency` DECIMAL(18,6),
	IN `p_SurchargesCurrency` DECIMAL(18,6),
	IN `p_ChargebackCurrency` DECIMAL(18,6),
	IN `p_CollectionCostAmountCurrency` DECIMAL(18,6),
	IN `p_RegistrationCostPerNumberCurrency` DECIMAL(18,6),
	IN `p_Critearea_CountryId` INT,
	IN `p_Critearea_Code` varchar(50),
	IN `p_Critearea_Description` varchar(200),
	IN `p_Critearea_City` VARCHAR(50),
	IN `p_Critearea_Tariff` VARCHAR(50),
	IN `p_Critearea_OriginationCode` VARCHAR(50),
	IN `p_Critearea_OriginationDescription` VARCHAR(200),
	IN `p_Critearea_Effective` VARCHAR(50),
	IN `p_TimezonesID` INT,
	IN `p_Critearea_ApprovedStatus` TINYINT,
	IN `p_ModifiedBy` varchar(50),
	IN `p_Critearea` INT,
	IN `p_action` INT
)
ThisSP:BEGIN

	DECLARE v_RateApprovalProcess_ INT;
	DECLARE v_RateTableAppliedTo_ INT;

	DECLARE v_StatusAwaitingApproval_ INT(11) DEFAULT 0;
	DECLARE v_StatusApproved_ INT(11) DEFAULT 1;
	DECLARE v_StatusRejected_ INT(11) DEFAULT 2;
	DECLARE v_StatusDelete_ INT(11) DEFAULT 3;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT Value INTO v_RateApprovalProcess_ FROM tblCompanySetting WHERE CompanyID = (SELECT CompanyId FROM tblRateTable WHERE RateTableID = p_RateTableId) AND `Key`='RateApprovalProcess';
	SELECT AppliedTo INTO v_RateTableAppliedTo_ FROM tblRateTable WHERE RateTableID = p_RateTableId;

	DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableDIDRate_;
	CREATE TEMPORARY TABLE tmp_TempRateTableDIDRate_ (
		`RateTableDIDRateId` int(11) NOT NULL,
		`OriginationRateID` int(11) NOT NULL DEFAULT '0',
		`RateId` int(11) NOT NULL,
		`RateTableId` int(11) NOT NULL,
		`TimezonesID` int(11) NOT NULL,
		`City` VARCHAR(50) NOT NULL DEFAULT '',
		`Tariff` VARCHAR(50) NOT NULL DEFAULT '',
		`AccessType` VARCHAR(200) NULL DEFAULT NULL,
		`OneOffCost` decimal(18,6) NULL DEFAULT NULL,
		`MonthlyCost` decimal(18,6) NULL DEFAULT NULL,
		`CostPerCall` decimal(18,6) NULL DEFAULT NULL,
		`CostPerMinute` decimal(18,6) NULL DEFAULT NULL,
		`SurchargePerCall` decimal(18,6) NULL DEFAULT NULL,
		`SurchargePerMinute` decimal(18,6) NULL DEFAULT NULL,
		`OutpaymentPerCall` decimal(18,6) NULL DEFAULT NULL,
		`OutpaymentPerMinute` decimal(18,6) NULL DEFAULT NULL,
		`Surcharges` decimal(18,6) NULL DEFAULT NULL,
		`Chargeback` decimal(18,6) NULL DEFAULT NULL,
		`CollectionCostAmount` decimal(18,6) NULL DEFAULT NULL,
		`CollectionCostPercentage` decimal(18,6) NULL DEFAULT NULL,
		`RegistrationCostPerNumber` decimal(18,6) NULL DEFAULT NULL,
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` datetime NOT NULL,
		`EndDate` datetime DEFAULT NULL,
		`created_at` datetime DEFAULT NULL,
		`updated_at` datetime DEFAULT NULL,
		`CreatedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		`ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		`ApprovedStatus` tinyint,
		`ApprovedBy` varchar(50),
		`ApprovedDate` datetime
	);

	INSERT INTO tmp_TempRateTableDIDRate_
	SELECT
		rtr.RateTableDIDRateId,
		IF(rtr.OriginationRateID=0,IFNULL(p_OriginationRateID,rtr.OriginationRateID),rtr.OriginationRateID) AS OriginationRateID,
		rtr.RateId,
		rtr.RateTableId,
		rtr.TimezonesID,
		IFNULL(p_City,rtr.City) AS City,
		IFNULL(p_Tariff,rtr.Tariff) AS Tariff,
		IFNULL(p_AccessType,rtr.AccessType) AS AccessType,
		IF(p_OneOffCost IS NOT NULL,IF(p_OneOffCost='NULL',NULL,p_OneOffCost),rtr.OneOffCost) AS OneOffCost,
		IF(p_MonthlyCost IS NOT NULL,IF(p_MonthlyCost='NULL',NULL,p_MonthlyCost),rtr.MonthlyCost) AS MonthlyCost,
		IF(p_CostPerCall IS NOT NULL,IF(p_CostPerCall='NULL',NULL,p_CostPerCall),rtr.CostPerCall) AS CostPerCall,
		IF(p_CostPerMinute IS NOT NULL,IF(p_CostPerMinute='NULL',NULL,p_CostPerMinute),rtr.CostPerMinute) AS CostPerMinute,
		IF(p_SurchargePerCall IS NOT NULL,IF(p_SurchargePerCall='NULL',NULL,p_SurchargePerCall),rtr.SurchargePerCall) AS SurchargePerCall,
		IF(p_SurchargePerMinute IS NOT NULL,IF(p_SurchargePerMinute='NULL',NULL,p_SurchargePerMinute),rtr.SurchargePerMinute) AS SurchargePerMinute,
		IF(p_OutpaymentPerCall IS NOT NULL,IF(p_OutpaymentPerCall='NULL',NULL,p_OutpaymentPerCall),rtr.OutpaymentPerCall) AS OutpaymentPerCall,
		IF(p_OutpaymentPerMinute IS NOT NULL,IF(p_OutpaymentPerMinute='NULL',NULL,p_OutpaymentPerMinute),rtr.OutpaymentPerMinute) AS OutpaymentPerMinute,
		IF(p_Surcharges IS NOT NULL,IF(p_Surcharges='NULL',NULL,p_Surcharges),rtr.Surcharges) AS Surcharges,
		IF(p_Chargeback IS NOT NULL,IF(p_Chargeback='NULL',NULL,p_Chargeback),rtr.Chargeback) AS Chargeback,
		IF(p_CollectionCostAmount IS NOT NULL,IF(p_CollectionCostAmount='NULL',NULL,p_CollectionCostAmount),rtr.CollectionCostAmount) AS CollectionCostAmount,
		IF(p_CollectionCostPercentage IS NOT NULL,IF(p_CollectionCostPercentage='NULL',NULL,p_CollectionCostPercentage),rtr.CollectionCostPercentage) AS CollectionCostPercentage,
		IF(p_RegistrationCostPerNumber IS NOT NULL,IF(p_RegistrationCostPerNumber='NULL',NULL,p_RegistrationCostPerNumber),rtr.RegistrationCostPerNumber) AS RegistrationCostPerNumber,
		IFNULL(p_OneOffCostCurrency,rtr.OneOffCostCurrency) AS OneOffCostCurrency,
		IFNULL(p_MonthlyCostCurrency,rtr.MonthlyCostCurrency) AS MonthlyCostCurrency,
		IFNULL(p_CostPerCallCurrency,rtr.CostPerCallCurrency) AS CostPerCallCurrency,
		IFNULL(p_CostPerMinuteCurrency,rtr.CostPerMinuteCurrency) AS CostPerMinuteCurrency,
		IFNULL(p_SurchargePerCallCurrency,rtr.SurchargePerCallCurrency) AS SurchargePerCallCurrency,
		IFNULL(p_SurchargePerMinuteCurrency,rtr.SurchargePerMinuteCurrency) AS SurchargePerMinuteCurrency,
		IFNULL(p_OutpaymentPerCallCurrency,rtr.OutpaymentPerCallCurrency) AS OutpaymentPerCallCurrency,
		IFNULL(p_OutpaymentPerMinuteCurrency,rtr.OutpaymentPerMinuteCurrency) AS OutpaymentPerMinuteCurrency,
		IFNULL(p_SurchargesCurrency,rtr.SurchargesCurrency) AS SurchargesCurrency,
		IFNULL(p_ChargebackCurrency,rtr.ChargebackCurrency) AS ChargebackCurrency,
		IFNULL(p_CollectionCostAmountCurrency,rtr.CollectionCostAmountCurrency) AS CollectionCostAmountCurrency,
		IFNULL(p_RegistrationCostPerNumberCurrency,rtr.RegistrationCostPerNumberCurrency) AS RegistrationCostPerNumberCurrency,
		IFNULL(p_EffectiveDate,rtr.EffectiveDate) AS EffectiveDate,
		IFNULL(p_EndDate,rtr.EndDate) AS EndDate,
		rtr.created_at,
		NOW() AS updated_at,
		rtr.CreatedBy,
		p_ModifiedBy AS ModifiedBy,
		rtr.ApprovedStatus,
		rtr.ApprovedBy,
		rtr.ApprovedDate
	FROM
		tblRateTableDIDRate rtr
	INNER JOIN
		tblRate r ON r.RateID = rtr.RateId
	LEFT JOIN
		tblRate r2 ON r2.RateID = rtr.OriginationRateID
	WHERE
		(
			p_EffectiveDate IS NULL OR (
				(rtr.RateID,rtr.OriginationRateID,rtr.TimezonesID) NOT IN (
						SELECT
							RateID,OriginationRateID,TimezonesID
						FROM
							tblRateTableDIDRate
						WHERE
							EffectiveDate=p_EffectiveDate AND (p_TimezonesID IS NULL OR TimezonesID = p_TimezonesID) AND City=p_City AND Tariff=p_Tariff AND
							((p_Critearea = 0 AND (FIND_IN_SET(RateTableDIDRateID,p_RateTableDIDRateID) = 0 )) OR p_Critearea = 1) AND
							RateTableId = p_RateTableId
				)
			)
		)
		AND
		(
			(p_Critearea = 0 AND (FIND_IN_SET(rtr.RateTableDIDRateID,p_RateTableDIDRateID) != 0 )) OR
			(
				p_Critearea = 1 AND
				(
					((p_Critearea_CountryId IS NULL) OR (p_Critearea_CountryId IS NOT NULL AND r.CountryId = p_Critearea_CountryId)) AND
					((p_Critearea_OriginationCode IS NULL) OR (p_Critearea_OriginationCode IS NOT NULL AND r2.Code LIKE REPLACE(p_Critearea_OriginationCode,'*', '%'))) AND
					((p_Critearea_OriginationDescription IS NULL) OR (p_Critearea_OriginationDescription IS NOT NULL AND r2.Description LIKE REPLACE(p_Critearea_OriginationDescription,'*', '%'))) AND
					((p_Critearea_Code IS NULL) OR (p_Critearea_Code IS NOT NULL AND r.Code LIKE REPLACE(p_Critearea_Code,'*', '%'))) AND
					((p_Critearea_Description IS NULL) OR (p_Critearea_Description IS NOT NULL AND r.Description LIKE REPLACE(p_Critearea_Description,'*', '%'))) AND
					(p_Critearea_City IS NULL OR rtr.City LIKE REPLACE(p_Critearea_City, '*', '%')) AND
					(p_Critearea_Tariff IS NULL OR rtr.Tariff LIKE REPLACE(p_Critearea_Tariff, '*', '%')) AND
					(p_Critearea_ApprovedStatus IS NULL OR rtr.ApprovedStatus = p_Critearea_ApprovedStatus) AND
					(
						p_Critearea_Effective = 'All' OR
						(p_Critearea_Effective = 'Now' AND rtr.EffectiveDate <= NOW() ) OR
						(p_Critearea_Effective = 'Future' AND rtr.EffectiveDate > NOW() )
					)
				)
			)
		) AND
		rtr.RateTableId = p_RateTableId AND
		(p_TimezonesID IS NULL OR 	rtr.TimezonesID = p_TimezonesID);


	IF p_action = 1
	THEN

		IF p_EffectiveDate IS NOT NULL
		THEN
			CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempRateTableDIDRate_2 as (select * from tmp_TempRateTableDIDRate_);
			DELETE n1 FROM tmp_TempRateTableDIDRate_ n1, tmp_TempRateTableDIDRate_2 n2 WHERE n1.RateTableDIDRateID < n2.RateTableDIDRateID AND  n1.RateID = n2.RateID AND n1.OriginationRateID = n2.OriginationRateID AND n1.TimezonesID = n2.TimezonesID;
		END IF;

		-- delete records which can be duplicates, we will not update them
		DELETE n1.* FROM tmp_TempRateTableDIDRate_ n1, tblRateTableDIDRate n2 WHERE n1.RateTableDIDRateID <> n2.RateTableDIDRateID AND n1.RateTableID = n2.RateTableID AND n1.TimezonesID = n2.TimezonesID AND n1.EffectiveDate = n2.EffectiveDate AND n1.RateID = n2.RateID AND n1.OriginationRateID = n2.OriginationRateID AND n1.City=n2.City AND n1.Tariff=n2.Tariff AND n2.RateTableID=p_RateTableId;

		/*
			it was creating history evan if no columns are updating of row
			so, using this query we are removing rows which are not updating any columns
		*/
		DELETE
			temp
		FROM
			tmp_TempRateTableDIDRate_ temp
		JOIN
			tblRateTableDIDRate rtr ON rtr.RateTableDIDRateID = temp.RateTableDIDRateID
		WHERE
			(rtr.OriginationRateID = temp.OriginationRateID) AND
			(rtr.EffectiveDate = temp.EffectiveDate) AND
			(rtr.TimezonesID = temp.TimezonesID) AND
			(rtr.City = temp.City) AND
			(rtr.Tariff = temp.Tariff) AND
			((rtr.AccessType IS NULL && temp.AccessType IS NULL) || rtr.AccessType = temp.AccessType) AND
			((rtr.OneOffCost IS NULL && temp.OneOffCost IS NULL) || rtr.OneOffCost = temp.OneOffCost) AND
			((rtr.MonthlyCost IS NULL && temp.MonthlyCost IS NULL) || rtr.MonthlyCost = temp.MonthlyCost) AND
			((rtr.CostPerCall IS NULL && temp.CostPerCall IS NULL) || rtr.CostPerCall = temp.CostPerCall) AND
			((rtr.CostPerMinute IS NULL && temp.CostPerMinute IS NULL) || rtr.CostPerMinute = temp.CostPerMinute) AND
			((rtr.SurchargePerCall IS NULL && temp.SurchargePerCall IS NULL) || rtr.SurchargePerCall = temp.SurchargePerCall) AND
			((rtr.SurchargePerMinute IS NULL && temp.SurchargePerMinute IS NULL) || rtr.SurchargePerMinute = temp.SurchargePerMinute) AND
			((rtr.OutpaymentPerCall IS NULL && temp.OutpaymentPerCall IS NULL) || rtr.OutpaymentPerCall = temp.OutpaymentPerCall) AND
			((rtr.OutpaymentPerMinute IS NULL && temp.OutpaymentPerMinute IS NULL) || rtr.OutpaymentPerMinute = temp.OutpaymentPerMinute) AND
			((rtr.Surcharges IS NULL && temp.Surcharges IS NULL) || rtr.Surcharges = temp.Surcharges) AND
			((rtr.Chargeback IS NULL && temp.Chargeback IS NULL) || rtr.Chargeback = temp.Chargeback) AND
			((rtr.CollectionCostAmount IS NULL && temp.CollectionCostAmount IS NULL) || rtr.CollectionCostAmount = temp.CollectionCostAmount) AND
			((rtr.CollectionCostPercentage IS NULL && temp.CollectionCostPercentage IS NULL) || rtr.CollectionCostPercentage = temp.CollectionCostPercentage) AND
			((rtr.RegistrationCostPerNumber IS NULL && temp.RegistrationCostPerNumber IS NULL) || rtr.RegistrationCostPerNumber = temp.RegistrationCostPerNumber) AND
			((rtr.OneOffCostCurrency IS NULL && temp.OneOffCostCurrency IS NULL) || rtr.OneOffCostCurrency = temp.OneOffCostCurrency) AND
			((rtr.MonthlyCostCurrency IS NULL && temp.MonthlyCostCurrency IS NULL) || rtr.MonthlyCostCurrency = temp.MonthlyCostCurrency) AND
			((rtr.CostPerCallCurrency IS NULL && temp.CostPerCallCurrency IS NULL) || rtr.CostPerCallCurrency = temp.CostPerCallCurrency) AND
			((rtr.CostPerMinuteCurrency IS NULL && temp.CostPerMinuteCurrency IS NULL) || rtr.CostPerMinuteCurrency = temp.CostPerMinuteCurrency) AND
			((rtr.SurchargePerCallCurrency IS NULL && temp.SurchargePerCallCurrency IS NULL) || rtr.SurchargePerCallCurrency = temp.SurchargePerCallCurrency) AND
			((rtr.SurchargePerMinuteCurrency IS NULL && temp.SurchargePerMinuteCurrency IS NULL) || rtr.SurchargePerMinuteCurrency = temp.SurchargePerMinuteCurrency) AND
			((rtr.OutpaymentPerCallCurrency IS NULL && temp.OutpaymentPerCallCurrency IS NULL) || rtr.OutpaymentPerCallCurrency = temp.OutpaymentPerCallCurrency) AND
			((rtr.OutpaymentPerMinuteCurrency IS NULL && temp.OutpaymentPerMinuteCurrency IS NULL) || rtr.OutpaymentPerMinuteCurrency = temp.OutpaymentPerMinuteCurrency) AND
			((rtr.SurchargesCurrency IS NULL && temp.SurchargesCurrency IS NULL) || rtr.SurchargesCurrency = temp.SurchargesCurrency) AND
			((rtr.ChargebackCurrency IS NULL && temp.ChargebackCurrency IS NULL) || rtr.ChargebackCurrency = temp.ChargebackCurrency) AND
			((rtr.CollectionCostAmountCurrency IS NULL && temp.CollectionCostAmountCurrency IS NULL) || rtr.CollectionCostAmountCurrency = temp.CollectionCostAmountCurrency) AND
			((rtr.RegistrationCostPerNumberCurrency IS NULL && temp.RegistrationCostPerNumberCurrency IS NULL) || rtr.RegistrationCostPerNumberCurrency = temp.RegistrationCostPerNumberCurrency);

	END IF;


	-- if rate table is not vendor rate table and rate approval process is on then set approval status to awaiting approval while updating
	IF v_RateTableAppliedTo_!=2 AND v_RateApprovalProcess_=1
	THEN
		UPDATE
			tmp_TempRateTableDIDRate_
		SET
			ApprovedStatus = v_StatusAwaitingApproval_,
			ApprovedBy = NULL,
			ApprovedDate = NULL;


		INSERT INTO tblRateTableDIDRateAA (
			OriginationRateID,
			RateId,
			RateTableId,
			TimezonesID,
			City,
			Tariff,
			AccessType,
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
			created_at,
			updated_at,
			CreatedBy,
			ModifiedBy,
			ApprovedStatus,
			ApprovedBy,
			ApprovedDate
		)
		SELECT
			OriginationRateID,
			RateId,
			RateTableId,
			TimezonesID,
			City,
			Tariff,
			AccessType,
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
			created_at,
			updated_at,
			CreatedBy,
			ModifiedBy,
			IF(p_action=1,v_StatusAwaitingApproval_,v_StatusDelete_) AS ApprovedStatus, -- if action=update then status=aa else status=aadelete
			ApprovedBy,
			ApprovedDate
		FROM
			tmp_TempRateTableDIDRate_;

		LEAVE ThisSP;

	END IF;


	UPDATE
		tblRateTableDIDRate rtr
	INNER JOIN
		tmp_TempRateTableDIDRate_ temp ON temp.RateTableDIDRateID = rtr.RateTableDIDRateID
	SET
		rtr.EndDate = NOW()
	WHERE
		temp.RateTableDIDRateID = rtr.RateTableDIDRateID;

	CALL prc_ArchiveOldRateTableDIDRate(p_RateTableId,p_TimezonesID,p_ModifiedBy);

	IF p_action = 1
	THEN

		INSERT INTO tblRateTableDIDRate (
			OriginationRateID,
			RateId,
			RateTableId,
			TimezonesID,
			City,
			Tariff,
			AccessType,
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
			created_at,
			updated_at,
			CreatedBy,
			ModifiedBy,
			ApprovedStatus,
			ApprovedBy,
			ApprovedDate
		)
		SELECT
			OriginationRateID,
			RateId,
			RateTableId,
			TimezonesID,
			City,
			Tariff,
			AccessType,
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
			created_at,
			updated_at,
			CreatedBy,
			ModifiedBy,
			ApprovedStatus,
			ApprovedBy,
			ApprovedDate
		FROM
			tmp_TempRateTableDIDRate_;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_WSReviewRateTableDIDRate`;
DELIMITER //
CREATE PROCEDURE `prc_WSReviewRateTableDIDRate`(
	IN `p_RateTableId` INT,
	IN `p_replaceAllRates` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_processId` VARCHAR(200),
	IN `p_addNewCodesToCodeDeck` INT,
	IN `p_companyId` INT,
	IN `p_dialstringid` INT,
	IN `p_dialcodeSeparator` VARCHAR(50),
	IN `p_seperatecolumn` INT,
	IN `p_CurrencyID` INT,
	IN `p_list_option` INT
)
ThisSP:BEGIN


	DECLARE newstringcode INT(11) DEFAULT 0;
	DECLARE v_pointer_ INT;
	DECLARE v_rowCount_ INT;
	DECLARE v_RateTableCurrencyID_ INT;
	DECLARE v_CompanyCurrencyID_ INT;

    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
    CREATE TEMPORARY TABLE tmp_JobLog_ (
        Message longtext
    );

    DROP TEMPORARY TABLE IF EXISTS tmp_split_RateTableDIDRate_;
    CREATE TEMPORARY TABLE tmp_split_RateTableDIDRate_ (
		`TempRateTableDIDRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NULL DEFAULT NULL,
		`OneOffCost` decimal(18,6) DEFAULT NULL,
	  	`MonthlyCost` decimal(18,6) DEFAULT NULL,
	  	`CostPerCall` decimal(18,6) DEFAULT NULL,
	  	`CostPerMinute` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerCall` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerMinute` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerCall` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerMinute` decimal(18,6) DEFAULT NULL,
	  	`Surcharges` decimal(18,6) DEFAULT NULL,
	  	`Chargeback` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostAmount` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostPercentage` decimal(18,6) DEFAULT NULL,
	  	`RegistrationCostPerNumber` decimal(18,6) DEFAULT NULL,
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`DialStringPrefix` varchar(500) ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_OriginationCode (`OriginationCode`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
    );

    DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableDIDRate_;
    CREATE TEMPORARY TABLE tmp_TempRateTableDIDRate_ (
		`TempRateTableDIDRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NULL DEFAULT NULL,
		`OneOffCost` decimal(18,6) DEFAULT NULL,
	  	`MonthlyCost` decimal(18,6) DEFAULT NULL,
	  	`CostPerCall` decimal(18,6) DEFAULT NULL,
	  	`CostPerMinute` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerCall` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerMinute` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerCall` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerMinute` decimal(18,6) DEFAULT NULL,
	  	`Surcharges` decimal(18,6) DEFAULT NULL,
	  	`Chargeback` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostAmount` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostPercentage` decimal(18,6) DEFAULT NULL,
	  	`RegistrationCostPerNumber` decimal(18,6) DEFAULT NULL,
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`DialStringPrefix` varchar(500) ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_OriginationCode (`OriginationCode`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
    );

    CALL  prc_RateTableDIDRateCheckDialstringAndDupliacteCode(p_companyId,p_processId,p_dialstringid,p_effectiveImmediately,p_dialcodeSeparator,p_seperatecolumn);

	ALTER TABLE
		`tmp_TempRateTableDIDRate_`
	ADD Column `NewOneOffCost` decimal(18, 6),
	ADD Column `NewMonthlyCost` decimal(18, 6),
	ADD Column `NewCostPerCall` decimal(18, 6),
	ADD Column `NewCostPerMinute` decimal(18, 6),
	ADD Column `NewSurchargePerCall` decimal(18, 6),
	ADD Column `NewSurchargePerMinute` decimal(18, 6),
	ADD Column `NewOutpaymentPerCall` decimal(18, 6),
	ADD Column `NewOutpaymentPerMinute` decimal(18, 6),
	ADD Column `NewSurcharges` decimal(18, 6),
	ADD Column `NewChargeback` decimal(18, 6),
	ADD Column `NewCollectionCostAmount` decimal(18, 6),
	ADD Column `NewCollectionCostPercentage` decimal(18, 6),
	ADD Column `NewRegistrationCostPerNumber` decimal(18, 6) ;

    SELECT COUNT(*) AS COUNT INTO newstringcode FROM tmp_JobLog_;

    SELECT CurrencyID into v_RateTableCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyID FROM tblRateTable WHERE RateTableId=p_RateTableId);
    SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);

	IF p_CurrencyID > 0 AND p_CurrencyID != v_RateTableCurrencyID_
	THEN
		IF p_CurrencyID = v_CompanyCurrencyID_
		THEN
			UPDATE
				tmp_TempRateTableDIDRate_
			SET
				NewOneOffCost = ( OneOffCost  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewMonthlyCost = ( MonthlyCost  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewCostPerCall = ( CostPerCall  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewCostPerMinute = ( CostPerMinute  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewSurchargePerCall = ( SurchargePerCall  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewSurchargePerMinute = ( SurchargePerMinute  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewOutpaymentPerCall = ( OutpaymentPerCall  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewOutpaymentPerMinute = ( OutpaymentPerMinute  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewSurcharges = ( Surcharges  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewChargeback = ( Chargeback  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewCollectionCostAmount = ( CollectionCostAmount  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewCollectionCostPercentage = ( CollectionCostPercentage  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewRegistrationCostPerNumber = ( RegistrationCostPerNumber  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) )
			WHERE ProcessID=p_processId;
		ELSE
			UPDATE
				tmp_TempRateTableDIDRate_
			SET
				NewOneOffCost = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (OneOffCost  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewMonthlyCost = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (MonthlyCost  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewCostPerCall = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (CostPerCall  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewCostPerMinute = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (CostPerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewSurchargePerCall = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (SurchargePerCall  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewSurchargePerMinute = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (SurchargePerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewOutpaymentPerCall = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (OutpaymentPerCall  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewOutpaymentPerMinute = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (OutpaymentPerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewSurcharges = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (Surcharges  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewChargeback = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (Chargeback  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewCollectionCostAmount = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (CollectionCostAmount  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewCollectionCostPercentage = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (CollectionCostPercentage  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewRegistrationCostPerNumber = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (RegistrationCostPerNumber  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId )))
			WHERE ProcessID=p_processId;
		END IF;
	ELSE
		UPDATE
			tmp_TempRateTableDIDRate_
		SET
			NewOneOffCost = OneOffCost,
			NewMonthlyCost = MonthlyCost,
			NewCostPerCall = CostPerCall,
			NewCostPerMinute = CostPerMinute,
			NewSurchargePerCall = SurchargePerCall,
			NewSurchargePerMinute = SurchargePerMinute,
			NewOutpaymentPerCall = OutpaymentPerCall,
			NewOutpaymentPerMinute = OutpaymentPerMinute,
			NewSurcharges = Surcharges,
			NewChargeback = Chargeback,
			NewCollectionCostAmount = CollectionCostAmount,
			NewCollectionCostPercentage = CollectionCostPercentage,
			NewRegistrationCostPerNumber = RegistrationCostPerNumber
		WHERE
			ProcessID = p_processId;
	END IF;

    IF newstringcode = 0
    THEN

		INSERT INTO tblRateTableDIDRateChangeLog(
            TempRateTableDIDRateID,
            RateTableDIDRateID,
            RateTableId,
            TimezonesID,
            OriginationRateID,
            OriginationCode,
            OriginationDescription,
            RateId,
            Code,
            Description,
            City,
				Tariff,
            AccessType,
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
            `Action`,
            ProcessID,
            created_at
		)
		SELECT
			tblTempRateTableDIDRate.TempRateTableDIDRateID,
			tblRateTableDIDRate.RateTableDIDRateID,
			p_RateTableId AS RateTableId,
			tblTempRateTableDIDRate.TimezonesID,
			IFNULL(OriginationRate.RateID,0) AS OriginationRateID,
			tblTempRateTableDIDRate.OriginationCode,
			tblTempRateTableDIDRate.OriginationDescription,
			tblRate.RateId,
			tblTempRateTableDIDRate.Code,
			tblTempRateTableDIDRate.Description,
			tblTempRateTableDIDRate.City,
			tblTempRateTableDIDRate.Tariff,
			tblTempRateTableDIDRate.AccessType,
			tblTempRateTableDIDRate.NewOneOffCost,
			tblTempRateTableDIDRate.NewMonthlyCost,
			tblTempRateTableDIDRate.NewCostPerCall,
			tblTempRateTableDIDRate.NewCostPerMinute,
			tblTempRateTableDIDRate.NewSurchargePerCall,
			tblTempRateTableDIDRate.NewSurchargePerMinute,
			tblTempRateTableDIDRate.NewOutpaymentPerCall,
			tblTempRateTableDIDRate.NewOutpaymentPerMinute,
			tblTempRateTableDIDRate.NewSurcharges,
			tblTempRateTableDIDRate.NewChargeback,
			tblTempRateTableDIDRate.NewCollectionCostAmount,
			tblTempRateTableDIDRate.NewCollectionCostPercentage,
			tblTempRateTableDIDRate.NewRegistrationCostPerNumber,
			tblTempRateTableDIDRate.OneOffCostCurrency,
			tblTempRateTableDIDRate.MonthlyCostCurrency,
			tblTempRateTableDIDRate.CostPerCallCurrency,
			tblTempRateTableDIDRate.CostPerMinuteCurrency,
			tblTempRateTableDIDRate.SurchargePerCallCurrency,
			tblTempRateTableDIDRate.SurchargePerMinuteCurrency,
			tblTempRateTableDIDRate.OutpaymentPerCallCurrency,
			tblTempRateTableDIDRate.OutpaymentPerMinuteCurrency,
			tblTempRateTableDIDRate.SurchargesCurrency,
			tblTempRateTableDIDRate.ChargebackCurrency,
			tblTempRateTableDIDRate.CollectionCostAmountCurrency,
			tblTempRateTableDIDRate.RegistrationCostPerNumberCurrency,
			tblTempRateTableDIDRate.EffectiveDate,
			tblTempRateTableDIDRate.EndDate,
			'New' AS `Action`,
			p_processId AS ProcessID,
			now() AS created_at
		FROM tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
		LEFT JOIN tblRate
			ON tblTempRateTableDIDRate.Code = tblRate.Code AND tblTempRateTableDIDRate.CodeDeckId = tblRate.CodeDeckId  AND tblRate.CompanyID = p_companyId
		LEFT JOIN tblRate AS OriginationRate
			ON tblTempRateTableDIDRate.OriginationCode = OriginationRate.Code AND tblTempRateTableDIDRate.CodeDeckId = OriginationRate.CodeDeckId  AND OriginationRate.CompanyID = p_companyId
		LEFT JOIN tblRateTableDIDRate
			ON tblRate.RateID = tblRateTableDIDRate.RateId AND
			((IFNULL(tblRateTableDIDRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableDIDRate.OriginationRateID = OriginationRate.RateID)) AND
			tblRateTableDIDRate.RateTableId = p_RateTableId AND
			tblRateTableDIDRate.TimezonesID = tblTempRateTableDIDRate.TimezonesID AND
			tblRateTableDIDRate.City = tblTempRateTableDIDRate.City AND
			tblRateTableDIDRate.Tariff = tblTempRateTableDIDRate.Tariff AND
			tblRateTableDIDRate.EffectiveDate  <= date(now())
		WHERE tblTempRateTableDIDRate.ProcessID=p_processId AND tblRateTableDIDRate.RateTableDIDRateID IS NULL
			AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');


        DROP TEMPORARY TABLE IF EXISTS tmp_EffectiveDates_;
		CREATE TEMPORARY TABLE tmp_EffectiveDates_ (
			EffectiveDate  Date,
			RowID int,
			INDEX (RowID)
		);
        INSERT INTO tmp_EffectiveDates_
        SELECT DISTINCT
            EffectiveDate,
            @row_num := @row_num+1 AS RowID
        FROM tmp_TempRateTableDIDRate_
            ,(SELECT @row_num := 0) x
        WHERE  ProcessID = p_processId

        group by EffectiveDate
        ORDER BY EffectiveDate asc;

        SET v_pointer_ = 1;
        SET v_rowCount_ = ( SELECT COUNT(*) FROM tmp_EffectiveDates_ );

        IF v_rowCount_ > 0 THEN

            WHILE v_pointer_ <= v_rowCount_
            DO

                SET @EffectiveDate = ( SELECT EffectiveDate FROM tmp_EffectiveDates_ WHERE RowID = v_pointer_ );
                SET @row_num = 0;

                INSERT INTO tblRateTableDIDRateChangeLog(
					TempRateTableDIDRateID,
					RateTableDIDRateID,
					RateTableId,
					TimezonesID,
					OriginationRateID,
					OriginationCode,
					OriginationDescription,
					RateId,
					Code,
					Description,
					City,
					Tariff,
					AccessType,
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
					`Action`,
					ProcessID,
					created_at
                )
                SELECT
					DISTINCT
					tblTempRateTableDIDRate.TempRateTableDIDRateID,
					RateTableDIDRate.RateTableDIDRateID,
					p_RateTableId AS RateTableId,
					tblTempRateTableDIDRate.TimezonesID,
					IFNULL(OriginationRate.RateID,0) AS OriginationRateID,
					OriginationRate.Code AS OriginationCode,
					OriginationRate.Description AS OriginationDescription,
					tblRate.RateId,
					tblRate.Code,
					tblRate.Description,
					tblTempRateTableDIDRate.City,
					tblTempRateTableDIDRate.Tariff,
					tblTempRateTableDIDRate.AccessType,
					CONCAT(tblTempRateTableDIDRate.NewOneOffCost, IF(tblTempRateTableDIDRate.NewOneOffCost > RateTableDIDRate.OneOffCost, '<span style="color: green;" data-toggle="tooltip" data-title="OneOffCost Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewOneOffCost < RateTableDIDRate.OneOffCost, '<span style="color: red;" data-toggle="tooltip" data-title="OneOffCost Decrease" data-placement="top">&#9660;</span>',''))) AS `OneOffCost`,
					CONCAT(tblTempRateTableDIDRate.NewMonthlyCost, IF(tblTempRateTableDIDRate.NewMonthlyCost > RateTableDIDRate.MonthlyCost, '<span style="color: green;" data-toggle="tooltip" data-title="MonthlyCost Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewMonthlyCost < RateTableDIDRate.MonthlyCost, '<span style="color: red;" data-toggle="tooltip" data-title="MonthlyCost Decrease" data-placement="top">&#9660;</span>',''))) AS `MonthlyCost`,
					CONCAT(tblTempRateTableDIDRate.NewCostPerCall, IF(tblTempRateTableDIDRate.NewCostPerCall > RateTableDIDRate.CostPerCall, '<span style="color: green;" data-toggle="tooltip" data-title="CostPerCall Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewCostPerCall < RateTableDIDRate.CostPerCall, '<span style="color: red;" data-toggle="tooltip" data-title="CostPerCall Decrease" data-placement="top">&#9660;</span>',''))) AS `CostPerCall`,
					CONCAT(tblTempRateTableDIDRate.NewCostPerMinute, IF(tblTempRateTableDIDRate.NewCostPerMinute > RateTableDIDRate.CostPerMinute, '<span style="color: green;" data-toggle="tooltip" data-title="CostPerMinute Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewCostPerMinute < RateTableDIDRate.CostPerMinute, '<span style="color: red;" data-toggle="tooltip" data-title="CostPerMinute Decrease" data-placement="top">&#9660;</span>',''))) AS `CostPerMinute`,
					CONCAT(tblTempRateTableDIDRate.NewSurchargePerCall, IF(tblTempRateTableDIDRate.NewSurchargePerCall > RateTableDIDRate.SurchargePerCall, '<span style="color: green;" data-toggle="tooltip" data-title="SurchargePerCall Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewSurchargePerCall < RateTableDIDRate.SurchargePerCall, '<span style="color: red;" data-toggle="tooltip" data-title="SurchargePerCall Decrease" data-placement="top">&#9660;</span>',''))) AS `SurchargePerCall`,
					CONCAT(tblTempRateTableDIDRate.NewSurchargePerMinute, IF(tblTempRateTableDIDRate.NewSurchargePerMinute > RateTableDIDRate.SurchargePerMinute, '<span style="color: green;" data-toggle="tooltip" data-title="SurchargePerMinute Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewSurchargePerMinute < RateTableDIDRate.SurchargePerMinute, '<span style="color: red;" data-toggle="tooltip" data-title="SurchargePerMinute Decrease" data-placement="top">&#9660;</span>',''))) AS `SurchargePerMinute`,
					CONCAT(tblTempRateTableDIDRate.NewOutpaymentPerCall, IF(tblTempRateTableDIDRate.NewOutpaymentPerCall > RateTableDIDRate.OutpaymentPerCall, '<span style="color: green;" data-toggle="tooltip" data-title="OutpaymentPerCall Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewOutpaymentPerCall < RateTableDIDRate.OutpaymentPerCall, '<span style="color: red;" data-toggle="tooltip" data-title="OutpaymentPerCall Decrease" data-placement="top">&#9660;</span>',''))) AS `OutpaymentPerCall`,
					CONCAT(tblTempRateTableDIDRate.NewOutpaymentPerMinute, IF(tblTempRateTableDIDRate.NewOutpaymentPerMinute > RateTableDIDRate.OutpaymentPerMinute, '<span style="color: green;" data-toggle="tooltip" data-title="OutpaymentPerMinute Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewOutpaymentPerMinute < RateTableDIDRate.OutpaymentPerMinute, '<span style="color: red;" data-toggle="tooltip" data-title="OutpaymentPerMinute Decrease" data-placement="top">&#9660;</span>',''))) AS `OutpaymentPerMinute`,
					CONCAT(tblTempRateTableDIDRate.NewSurcharges, IF(tblTempRateTableDIDRate.NewSurcharges > RateTableDIDRate.Surcharges, '<span style="color: green;" data-toggle="tooltip" data-title="Surcharges Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewSurcharges < RateTableDIDRate.Surcharges, '<span style="color: red;" data-toggle="tooltip" data-title="Surcharges Decrease" data-placement="top">&#9660;</span>',''))) AS `Surcharges`,
					CONCAT(tblTempRateTableDIDRate.NewChargeback, IF(tblTempRateTableDIDRate.NewChargeback > RateTableDIDRate.Chargeback, '<span style="color: green;" data-toggle="tooltip" data-title="Chargeback Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewChargeback < RateTableDIDRate.Chargeback, '<span style="color: red;" data-toggle="tooltip" data-title="Chargeback Decrease" data-placement="top">&#9660;</span>',''))) AS `Chargeback`,
					CONCAT(tblTempRateTableDIDRate.NewCollectionCostAmount, IF(tblTempRateTableDIDRate.NewCollectionCostAmount > RateTableDIDRate.CollectionCostAmount, '<span style="color: green;" data-toggle="tooltip" data-title="CollectionCostAmount Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewCollectionCostAmount < RateTableDIDRate.CollectionCostAmount, '<span style="color: red;" data-toggle="tooltip" data-title="CollectionCostAmount Decrease" data-placement="top">&#9660;</span>',''))) AS `CollectionCostAmount`,
					CONCAT(tblTempRateTableDIDRate.NewCollectionCostPercentage, IF(tblTempRateTableDIDRate.NewCollectionCostPercentage > RateTableDIDRate.CollectionCostPercentage, '<span style="color: green;" data-toggle="tooltip" data-title="CollectionCostPercentage Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewCollectionCostPercentage < RateTableDIDRate.CollectionCostPercentage, '<span style="color: red;" data-toggle="tooltip" data-title="CollectionCostPercentage Decrease" data-placement="top">&#9660;</span>',''))) AS `CollectionCostPercentage`,
					CONCAT(tblTempRateTableDIDRate.NewRegistrationCostPerNumber, IF(tblTempRateTableDIDRate.NewRegistrationCostPerNumber > RateTableDIDRate.RegistrationCostPerNumber, '<span style="color: green;" data-toggle="tooltip" data-title="RegistrationCostPerNumber Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewRegistrationCostPerNumber < RateTableDIDRate.RegistrationCostPerNumber, '<span style="color: red;" data-toggle="tooltip" data-title="RegistrationCostPerNumber Decrease" data-placement="top">&#9660;</span>',''))) AS `RegistrationCostPerNumber`,
					tblTempRateTableDIDRate.OneOffCostCurrency,
					tblTempRateTableDIDRate.MonthlyCostCurrency,
					tblTempRateTableDIDRate.CostPerCallCurrency,
					tblTempRateTableDIDRate.CostPerMinuteCurrency,
					tblTempRateTableDIDRate.SurchargePerCallCurrency,
					tblTempRateTableDIDRate.SurchargePerMinuteCurrency,
					tblTempRateTableDIDRate.OutpaymentPerCallCurrency,
					tblTempRateTableDIDRate.OutpaymentPerMinuteCurrency,
					tblTempRateTableDIDRate.SurchargesCurrency,
					tblTempRateTableDIDRate.ChargebackCurrency,
					tblTempRateTableDIDRate.CollectionCostAmountCurrency,
					tblTempRateTableDIDRate.RegistrationCostPerNumberCurrency,
					tblTempRateTableDIDRate.EffectiveDate,
					tblTempRateTableDIDRate.EndDate ,
					'IncreasedDecreased' AS `Action`,
					p_processid AS ProcessID,
					now() AS created_at
                FROM
                (
                    SELECT DISTINCT tmp.* ,
                        @row_num := IF(@prev_RateId = tmp.RateID AND @prev_EffectiveDate >= tmp.EffectiveDate, (@row_num + 1), 1) AS RowID,
                        @prev_RateId := tmp.RateID,
                        @prev_EffectiveDate := tmp.EffectiveDate
                    FROM
                    (
                        SELECT DISTINCT vr1.*
                        FROM tblRateTableDIDRate vr1
                        LEFT OUTER JOIN tblRateTableDIDRate vr2
                            ON vr1.RateTableId = vr2.RateTableId
                            AND vr1.RateID = vr2.RateID
                            AND vr1.OriginationRateID = vr2.OriginationRateID
                            AND vr1.TimezonesID = vr2.TimezonesID
                            AND vr1.City = vr2.City
                            AND vr1.Tariff = vr2.Tariff
                            AND vr2.EffectiveDate  = @EffectiveDate
                        WHERE
                            vr1.RateTableId = p_RateTableId
                            AND vr1.EffectiveDate <= COALESCE(vr2.EffectiveDate,@EffectiveDate)
                        ORDER BY vr1.RateID DESC ,vr1.EffectiveDate DESC
                    ) tmp ,
                    ( SELECT @row_num := 0 , @prev_RateId := 0 , @prev_EffectiveDate := '' ) x
                      ORDER BY RateID DESC , EffectiveDate DESC
                ) RateTableDIDRate
                JOIN tblRate
                    ON tblRate.CompanyID = p_companyId
                    AND tblRate.RateID = RateTableDIDRate.RateId
                LEFT JOIN tblRate AS OriginationRate
                    ON OriginationRate.CompanyID = p_companyId
                    AND OriginationRate.RateID = RateTableDIDRate.OriginationRateID
                JOIN tmp_TempRateTableDIDRate_ tblTempRateTableDIDRate
                    ON tblTempRateTableDIDRate.Code = tblRate.Code
                    AND ((tblTempRateTableDIDRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableDIDRate.OriginationCode = OriginationRate.Code))
                    AND tblTempRateTableDIDRate.TimezonesID = RateTableDIDRate.TimezonesID
                    AND tblTempRateTableDIDRate.City = RateTableDIDRate.City
                    AND tblTempRateTableDIDRate.Tariff = RateTableDIDRate.Tariff
                    AND tblTempRateTableDIDRate.ProcessID=p_processId
                    AND RateTableDIDRate.EffectiveDate <= tblTempRateTableDIDRate.EffectiveDate
                    AND tblTempRateTableDIDRate.EffectiveDate =  @EffectiveDate
                    AND RateTableDIDRate.RowID = 1
                WHERE
                    RateTableDIDRate.RateTableId = p_RateTableId
                    AND tblTempRateTableDIDRate.Code IS NOT NULL
                    AND tblTempRateTableDIDRate.ProcessID=p_processId
                    AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

                SET v_pointer_ = v_pointer_ + 1;

            END WHILE;

        END IF;


        IF p_list_option = 1
        THEN

            INSERT INTO tblRateTableDIDRateChangeLog(
				RateTableDIDRateID,
				RateTableId,
				TimezonesID,
				OriginationRateID,
				OriginationCode,
				OriginationDescription,
				RateId,
				Code,
				Description,
				City,
				Tariff,
				AccessType,
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
				`Action`,
				ProcessID,
				created_at
            )
            SELECT DISTINCT
                tblRateTableDIDRate.RateTableDIDRateID,
                p_RateTableId AS RateTableId,
                tblRateTableDIDRate.TimezonesID,
                tblRateTableDIDRate.OriginationRateID,
                OriginationRate.Code,
                OriginationRate.Description,
                tblRateTableDIDRate.RateId,
                tblRate.Code,
                tblRate.Description,
                tblRateTableDIDRate.City,
					 tblRateTableDIDRate.Tariff,
                tblRateTableDIDRate.AccessType,
				tblRateTableDIDRate.OneOffCost,
				tblRateTableDIDRate.MonthlyCost,
				tblRateTableDIDRate.CostPerCall,
				tblRateTableDIDRate.CostPerMinute,
				tblRateTableDIDRate.SurchargePerCall,
				tblRateTableDIDRate.SurchargePerMinute,
				tblRateTableDIDRate.OutpaymentPerCall,
				tblRateTableDIDRate.OutpaymentPerMinute,
				tblRateTableDIDRate.Surcharges,
				tblRateTableDIDRate.Chargeback,
				tblRateTableDIDRate.CollectionCostAmount,
				tblRateTableDIDRate.CollectionCostPercentage,
				tblRateTableDIDRate.RegistrationCostPerNumber,
				tblRateTableDIDRate.OneOffCostCurrency,
				tblRateTableDIDRate.MonthlyCostCurrency,
				tblRateTableDIDRate.CostPerCallCurrency,
				tblRateTableDIDRate.CostPerMinuteCurrency,
				tblRateTableDIDRate.SurchargePerCallCurrency,
				tblRateTableDIDRate.SurchargePerMinuteCurrency,
				tblRateTableDIDRate.OutpaymentPerCallCurrency,
				tblRateTableDIDRate.OutpaymentPerMinuteCurrency,
				tblRateTableDIDRate.SurchargesCurrency,
				tblRateTableDIDRate.ChargebackCurrency,
				tblRateTableDIDRate.CollectionCostAmountCurrency,
				tblRateTableDIDRate.RegistrationCostPerNumberCurrency,
                tblRateTableDIDRate.EffectiveDate,
                tblRateTableDIDRate.EndDate ,
                'Deleted' AS `Action`,
                p_processId AS ProcessID,
                now() AS deleted_at
            FROM tblRateTableDIDRate
            JOIN tblRate
                ON tblRate.RateID = tblRateTableDIDRate.RateId AND tblRate.CompanyID = p_companyId
        		LEFT JOIN tblRate AS OriginationRate
             	 ON OriginationRate.RateID = tblRateTableDIDRate.OriginationRateID AND OriginationRate.CompanyID = p_companyId
            LEFT JOIN tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
                ON tblTempRateTableDIDRate.Code = tblRate.Code
                AND ((tblTempRateTableDIDRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableDIDRate.OriginationCode = OriginationRate.Code))
                AND tblTempRateTableDIDRate.TimezonesID = tblRateTableDIDRate.TimezonesID
                AND tblTempRateTableDIDRate.City = tblRateTableDIDRate.City
                AND tblTempRateTableDIDRate.Tariff = tblRateTableDIDRate.Tariff
                AND tblTempRateTableDIDRate.ProcessID=p_processId
                AND (
                    ( tblTempRateTableDIDRate.EndDate is null AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block') )
                    OR
                    ( tblTempRateTableDIDRate.EndDate is not null AND tblTempRateTableDIDRate.Change IN ('Delete', 'R', 'D', 'Blocked','Block') )
                )
            WHERE tblRateTableDIDRate.RateTableId = p_RateTableId
                AND ( tblRateTableDIDRate.EndDate is null OR tblRateTableDIDRate.EndDate <= date(now()) )
                AND tblTempRateTableDIDRate.Code IS NULL
            ORDER BY RateTableDIDRateID ASC;

        END IF;


        INSERT INTO tblRateTableDIDRateChangeLog(
            RateTableDIDRateID,
            RateTableId,
            TimezonesID,
            OriginationRateID,
            OriginationCode,
            OriginationDescription,
            RateId,
            Code,
            Description,
            City,
				Tariff,
            AccessType,
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
            `Action`,
            ProcessID,
            created_at
        )
        SELECT DISTINCT
            tblRateTableDIDRate.RateTableDIDRateID,
            p_RateTableId AS RateTableId,
            tblRateTableDIDRate.TimezonesID,
            tblRateTableDIDRate.OriginationRateID,
            OriginationRate.Code,
            OriginationRate.Description,
            tblRateTableDIDRate.RateId,
            tblRate.Code,
            tblRate.Description,
            tblRateTableDIDRate.City,
				tblRateTableDIDRate.Tariff,
            tblRateTableDIDRate.AccessType,
			tblRateTableDIDRate.OneOffCost,
			tblRateTableDIDRate.MonthlyCost,
			tblRateTableDIDRate.CostPerCall,
			tblRateTableDIDRate.CostPerMinute,
			tblRateTableDIDRate.SurchargePerCall,
			tblRateTableDIDRate.SurchargePerMinute,
			tblRateTableDIDRate.OutpaymentPerCall,
			tblRateTableDIDRate.OutpaymentPerMinute,
			tblRateTableDIDRate.Surcharges,
			tblRateTableDIDRate.Chargeback,
			tblRateTableDIDRate.CollectionCostAmount,
			tblRateTableDIDRate.CollectionCostPercentage,
			tblRateTableDIDRate.RegistrationCostPerNumber,
			tblRateTableDIDRate.OneOffCostCurrency,
			tblRateTableDIDRate.MonthlyCostCurrency,
			tblRateTableDIDRate.CostPerCallCurrency,
			tblRateTableDIDRate.CostPerMinuteCurrency,
			tblRateTableDIDRate.SurchargePerCallCurrency,
			tblRateTableDIDRate.SurchargePerMinuteCurrency,
			tblRateTableDIDRate.OutpaymentPerCallCurrency,
			tblRateTableDIDRate.OutpaymentPerMinuteCurrency,
			tblRateTableDIDRate.SurchargesCurrency,
			tblRateTableDIDRate.ChargebackCurrency,
			tblRateTableDIDRate.CollectionCostAmountCurrency,
			tblRateTableDIDRate.RegistrationCostPerNumberCurrency,
            tblRateTableDIDRate.EffectiveDate,
            IFNULL(tblTempRateTableDIDRate.EndDate,tblRateTableDIDRate.EndDate) as  EndDate ,
            'Deleted' AS `Action`,
            p_processId AS ProcessID,
            now() AS deleted_at
        FROM tblRateTableDIDRate
        JOIN tblRate
            ON tblRate.RateID = tblRateTableDIDRate.RateId AND tblRate.CompanyID = p_companyId
        LEFT JOIN tblRate AS OriginationRate
             ON OriginationRate.RateID = tblRateTableDIDRate.OriginationRateID AND OriginationRate.CompanyID = p_companyId
        LEFT JOIN tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
            ON tblRate.Code = tblTempRateTableDIDRate.Code
            AND ((tblTempRateTableDIDRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableDIDRate.OriginationCode = OriginationRate.Code))
            AND tblTempRateTableDIDRate.TimezonesID = tblRateTableDIDRate.TimezonesID
            AND tblTempRateTableDIDRate.City = tblRateTableDIDRate.City
            AND tblTempRateTableDIDRate.Tariff = tblRateTableDIDRate.Tariff
            AND tblTempRateTableDIDRate.Change IN ('Delete', 'R', 'D', 'Blocked','Block')
            AND tblTempRateTableDIDRate.ProcessID=p_processId
        WHERE
			tblRateTableDIDRate.RateTableId = p_RateTableId AND
			tblTempRateTableDIDRate.Code IS NOT NULL
        ORDER BY
		RateTableDIDRateID ASC;

    END IF;

    SELECT * FROM tmp_JobLog_;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_RateTableDIDRateCheckDialstringAndDupliacteCode`;
DELIMITER //
CREATE PROCEDURE `prc_RateTableDIDRateCheckDialstringAndDupliacteCode`(
	IN `p_companyId` INT,
	IN `p_processId` VARCHAR(200) ,
	IN `p_dialStringId` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_dialcodeSeparator` VARCHAR(50),
	IN `p_seperatecolumn` INT
)
ThisSP:BEGIN

	DECLARE totaldialstringcode INT(11) DEFAULT 0;
	DECLARE v_CodeDeckId_ INT ;
	DECLARE totalduplicatecode INT(11);
	DECLARE errormessage longtext;
	DECLARE errorheader longtext;

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableDIDRateDialString_ ;
	CREATE TEMPORARY TABLE `tmp_RateTableDIDRateDialString_` (
		`TempRateTableDIDRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NULL DEFAULT NULL,
		`OneOffCost` decimal(18,6) DEFAULT NULL,
	  	`MonthlyCost` decimal(18,6) DEFAULT NULL,
	  	`CostPerCall` decimal(18,6) DEFAULT NULL,
	  	`CostPerMinute` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerCall` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerMinute` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerCall` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerMinute` decimal(18,6) DEFAULT NULL,
	  	`Surcharges` decimal(18,6) DEFAULT NULL,
	  	`Chargeback` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostAmount` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostPercentage` decimal(18,6) DEFAULT NULL,
	  	`RegistrationCostPerNumber` decimal(18,6) DEFAULT NULL,
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`DialStringPrefix` varchar(500),
		INDEX IX_orogination_code (OriginationCode),
		INDEX IX_origination_description (OriginationDescription),
		INDEX IX_code (code),
		INDEX IX_CodeDeckId (CodeDeckId),
		INDEX IX_Description (Description),
		INDEX IX_EffectiveDate (EffectiveDate),
		INDEX IX_DialStringPrefix (DialStringPrefix)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableDIDRateDialString_2 ;
	CREATE TEMPORARY TABLE `tmp_RateTableDIDRateDialString_2` (
		`TempRateTableDIDRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NULL DEFAULT NULL,
		`OneOffCost` decimal(18,6) DEFAULT NULL,
	  	`MonthlyCost` decimal(18,6) DEFAULT NULL,
	  	`CostPerCall` decimal(18,6) DEFAULT NULL,
	  	`CostPerMinute` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerCall` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerMinute` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerCall` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerMinute` decimal(18,6) DEFAULT NULL,
	  	`Surcharges` decimal(18,6) DEFAULT NULL,
	  	`Chargeback` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostAmount` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostPercentage` decimal(18,6) DEFAULT NULL,
	  	`RegistrationCostPerNumber` decimal(18,6) DEFAULT NULL,
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`DialStringPrefix` varchar(500),
		INDEX IX_orogination_code (OriginationCode),
		INDEX IX_origination_description (OriginationDescription),
		INDEX IX_code (code),
		INDEX IX_CodeDeckId (CodeDeckId),
		INDEX IX_Description (Description),
		INDEX IX_EffectiveDate (EffectiveDate),
		INDEX IX_DialStringPrefix (DialStringPrefix)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableDIDRateDialString_3 ;
	CREATE TEMPORARY TABLE `tmp_RateTableDIDRateDialString_3` (
		`TempRateTableDIDRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NULL DEFAULT NULL,
		`OneOffCost` decimal(18,6) DEFAULT NULL,
	  	`MonthlyCost` decimal(18,6) DEFAULT NULL,
	  	`CostPerCall` decimal(18,6) DEFAULT NULL,
	  	`CostPerMinute` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerCall` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerMinute` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerCall` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerMinute` decimal(18,6) DEFAULT NULL,
	  	`Surcharges` decimal(18,6) DEFAULT NULL,
	  	`Chargeback` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostAmount` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostPercentage` decimal(18,6) DEFAULT NULL,
	  	`RegistrationCostPerNumber` decimal(18,6) DEFAULT NULL,
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`DialStringPrefix` varchar(500),
		INDEX IX_orogination_code (OriginationCode),
		INDEX IX_origination_description (OriginationDescription),
		INDEX IX_code (code),
		INDEX IX_CodeDeckId (CodeDeckId),
		INDEX IX_Description (Description),
		INDEX IX_EffectiveDate (EffectiveDate),
		INDEX IX_DialStringPrefix (DialStringPrefix)
	);

	CALL prc_SplitRateTableDIDRate(p_processId,p_dialcodeSeparator,p_seperatecolumn);

	IF  p_effectiveImmediately = 1
	THEN
		UPDATE tmp_split_RateTableDIDRate_
		SET EffectiveDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EffectiveDate < DATE_FORMAT (NOW(), '%Y-%m-%d');

		UPDATE tmp_split_RateTableDIDRate_
		SET EndDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EndDate < DATE_FORMAT (NOW(), '%Y-%m-%d');
	END IF;

	DROP TEMPORARY TABLE IF EXISTS tmp_split_RateTableDIDRate_2;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_split_RateTableDIDRate_2 as (SELECT * FROM tmp_split_RateTableDIDRate_);

	-- delete duplicate records
	DELETE n1 FROM tmp_split_RateTableDIDRate_ n1
	INNER JOIN
	(
		SELECT MAX(TempRateTableDIDRateID) AS TempRateTableDIDRateID,EffectiveDate,OriginationCode,Code,DialStringPrefix,TimezonesID,City,Tariff,AccessType,
			OneOffCost, MonthlyCost, CostPerCall, CostPerMinute, SurchargePerCall, SurchargePerMinute, OutpaymentPerCall,
			OutpaymentPerMinute, Surcharges, Chargeback, CollectionCostAmount, CollectionCostPercentage, RegistrationCostPerNumber
		FROM tmp_split_RateTableDIDRate_2 WHERE ProcessId = p_processId
		GROUP BY
			OriginationCode,Code,EffectiveDate,DialStringPrefix,TimezonesID,City,Tariff,AccessType,
			OneOffCost, MonthlyCost, CostPerCall, CostPerMinute, SurchargePerCall, SurchargePerMinute, OutpaymentPerCall,
			OutpaymentPerMinute, Surcharges, Chargeback, CollectionCostAmount, CollectionCostPercentage, RegistrationCostPerNumber
		HAVING COUNT(*)>1
	)n2
	ON n1.Code = n2.Code
		AND ((n1.OriginationCode IS NULL AND n2.OriginationCode IS NULL) OR (n1.OriginationCode = n2.OriginationCode))
		AND n2.EffectiveDate = n1.EffectiveDate
		AND ((n2.DialStringPrefix IS NULL AND n1.DialStringPrefix IS NULL) OR (n2.DialStringPrefix = n1.DialStringPrefix))
		AND n2.TimezonesID = n1.TimezonesID
		AND ((n2.City IS NULL AND n1.City IS NULL) OR n2.City = n1.City)
		AND ((n2.Tariff IS NULL AND n1.Tariff IS NULL) OR n2.Tariff = n1.Tariff)
		AND ((n2.AccessType IS NULL AND n1.AccessType IS NULL) OR n2.AccessType = n1.AccessType)
		AND ((n2.OneOffCost IS NULL AND n1.OneOffCost IS NULL) OR n2.OneOffCost = n1.OneOffCost)
		AND ((n2.MonthlyCost IS NULL AND n1.MonthlyCost IS NULL) OR n2.MonthlyCost = n1.MonthlyCost)
		AND ((n2.CostPerCall IS NULL AND n1.CostPerCall IS NULL) OR n2.CostPerCall = n1.CostPerCall)
		AND ((n2.CostPerMinute IS NULL AND n1.CostPerMinute IS NULL) OR n2.CostPerMinute = n1.CostPerMinute)
		AND ((n2.SurchargePerCall IS NULL AND n1.SurchargePerCall IS NULL) OR n2.SurchargePerCall = n1.SurchargePerCall)
		AND ((n2.SurchargePerMinute IS NULL AND n1.SurchargePerMinute IS NULL) OR n2.SurchargePerMinute = n1.SurchargePerMinute)
		AND ((n2.OutpaymentPerCall IS NULL AND n1.OutpaymentPerCall IS NULL) OR n2.OutpaymentPerCall = n1.OutpaymentPerCall)
		AND ((n2.OutpaymentPerMinute IS NULL AND n1.OutpaymentPerMinute IS NULL) OR n2.OutpaymentPerMinute = n1.OutpaymentPerMinute)
		AND ((n2.Surcharges IS NULL AND n1.Surcharges IS NULL) OR n2.Surcharges = n1.Surcharges)
		AND ((n2.Chargeback IS NULL AND n1.Chargeback IS NULL) OR n2.Chargeback = n1.Chargeback)
		AND ((n2.CollectionCostAmount IS NULL AND n1.CollectionCostAmount IS NULL) OR n2.CollectionCostAmount = n1.CollectionCostAmount)
		AND ((n2.CollectionCostPercentage IS NULL AND n1.CollectionCostPercentage IS NULL) OR n2.CollectionCostPercentage = n1.CollectionCostPercentage)
		AND ((n2.RegistrationCostPerNumber IS NULL AND n1.RegistrationCostPerNumber IS NULL) OR n2.RegistrationCostPerNumber = n1.RegistrationCostPerNumber)
		AND n1.TempRateTableDIDRateID < n2.TempRateTableDIDRateID
	WHERE
		n1.ProcessId = p_processId;

	INSERT INTO tmp_TempRateTableDIDRate_
	(
		`TempRateTableDIDRateID`,
		CodeDeckId,
		TimezonesID,
		OriginationCode,
		OriginationDescription,
		Code,
		Description,
		City,
		Tariff,
		AccessType,
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
		`Change`,
		ProcessId,
		DialStringPrefix
	)
	SELECT DISTINCT
		`TempRateTableDIDRateID`,
		`CodeDeckId`,
		`TimezonesID`,
		`OriginationCode`,
		`OriginationDescription`,
		`Code`,
		`Description`,
		`City`,
		`Tariff`,
		`AccessType`,
		`OneOffCost`,
		`MonthlyCost`,
		`CostPerCall`,
		`CostPerMinute`,
		`SurchargePerCall`,
		`SurchargePerMinute`,
		`OutpaymentPerCall`,
		`OutpaymentPerMinute`,
		`Surcharges`,
		`Chargeback`,
		`CollectionCostAmount`,
		`CollectionCostPercentage`,
		`RegistrationCostPerNumber`,
		`OneOffCostCurrency`,
		`MonthlyCostCurrency`,
		`CostPerCallCurrency`,
		`CostPerMinuteCurrency`,
		`SurchargePerCallCurrency`,
		`SurchargePerMinuteCurrency`,
		`OutpaymentPerCallCurrency`,
		`OutpaymentPerMinuteCurrency`,
		`SurchargesCurrency`,
		`ChargebackCurrency`,
		`CollectionCostAmountCurrency`,
		`RegistrationCostPerNumberCurrency`,
		`EffectiveDate`,
		`EndDate`,
		`Change`,
		`ProcessId`,
		`DialStringPrefix`
	FROM tmp_split_RateTableDIDRate_
	WHERE tmp_split_RateTableDIDRate_.ProcessId = p_processId;

	IF  p_effectiveImmediately = 1
	THEN
		UPDATE tmp_TempRateTableDIDRate_
		SET EffectiveDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EffectiveDate < DATE_FORMAT (NOW(), '%Y-%m-%d');

		UPDATE tmp_TempRateTableDIDRate_
		SET EndDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EndDate < DATE_FORMAT (NOW(), '%Y-%m-%d');
	END IF;

	SELECT COUNT(*) INTO totalduplicatecode FROM(
	SELECT COUNT(code) as c,code FROM tmp_TempRateTableDIDRate_  GROUP BY OriginationCode,Code,EffectiveDate,DialStringPrefix,TimezonesID,City,Tariff HAVING c>1) AS tbl;

	IF  totalduplicatecode > 0
	THEN

		SELECT GROUP_CONCAT(code) into errormessage FROM(
		SELECT DISTINCT OriginationCode,Code, 1 as a FROM(
		SELECT COUNT(TempRateTableDIDRateID) as c, OriginationCode, Code FROM tmp_TempRateTableDIDRate_  GROUP BY OriginationCode,Code,EffectiveDate,DialStringPrefix,TimezonesID,City,Tariff HAVING c>1) AS tbl) as tbl2 GROUP by a;

		INSERT INTO tmp_JobLog_ (Message)
		SELECT DISTINCT
			CONCAT(IF(OriginationCode IS NOT NULL,CONCAT(OriginationCode,'-'),''), Code, ' DUPLICATE CODE')
		FROM(
			SELECT COUNT(TempRateTableDIDRateID) as c, OriginationCode, Code FROM tmp_TempRateTableDIDRate_  GROUP BY OriginationCode,Code,EffectiveDate,DialStringPrefix,TimezonesID,City,Tariff HAVING c>1) AS tbl;
	END IF;

	-- this code is no longer in use as we have removed dialstring mapping from did and pkg rate upload
	IF	totalduplicatecode = 0
	THEN

		IF p_dialstringid >0
		THEN

			DROP TEMPORARY TABLE IF EXISTS tmp_DialString_;
			CREATE TEMPORARY TABLE tmp_DialString_ (
				`DialStringID` INT,
				`DialString` VARCHAR(250),
				`ChargeCode` VARCHAR(250),
				`Description` VARCHAR(250),
				`Forbidden` VARCHAR(50),
				INDEX tmp_DialStringID (`DialStringID`),
				INDEX tmp_DialStringID_ChargeCode (`DialStringID`,`ChargeCode`)
			);

			INSERT INTO tmp_DialString_
			SELECT DISTINCT
				`DialStringID`,
				`DialString`,
				`ChargeCode`,
				`Description`,
				`Forbidden`
			FROM tblDialStringCode
			WHERE DialStringID = p_dialstringid;

			SELECT  COUNT(*) as COUNT INTO totaldialstringcode
			FROM tmp_TempRateTableDIDRate_ vr
			LEFT JOIN tmp_DialString_ ds
				ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  ))
			WHERE vr.ProcessId = p_processId
				AND ds.DialStringID IS NULL
				AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

			IF totaldialstringcode > 0
			THEN

				INSERT INTO tblDialStringCode (DialStringID,DialString,ChargeCode,created_by)
				  SELECT DISTINCT p_dialStringId,vr.DialStringPrefix, Code, 'RMService'
					FROM tmp_TempRateTableDIDRate_ vr
						LEFT JOIN tmp_DialString_ ds
							ON vr.DialStringPrefix = ds.DialString AND ds.DialStringID = p_dialStringId
						WHERE vr.ProcessId = p_processId
							AND ds.DialStringID IS NULL
							AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

				TRUNCATE tmp_DialString_;
				INSERT INTO tmp_DialString_
					SELECT DISTINCT
						`DialStringID`,
						`DialString`,
						`ChargeCode`,
						`Description`,
						`Forbidden`
					FROM tblDialStringCode
						WHERE DialStringID = p_dialstringid;

				SELECT  COUNT(*) as COUNT INTO totaldialstringcode
				FROM tmp_TempRateTableDIDRate_ vr
					LEFT JOIN tmp_DialString_ ds
						ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  ))
					WHERE vr.ProcessId = p_processId
						AND ds.DialStringID IS NULL
						AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

				INSERT INTO tmp_JobLog_ (Message)
					  SELECT DISTINCT CONCAT(Code ,' ', vr.DialStringPrefix , ' No PREFIX FOUND')
					  	FROM tmp_TempRateTableDIDRate_ vr
							LEFT JOIN tmp_DialString_ ds
								ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  ))
							WHERE vr.ProcessId = p_processId
								AND ds.DialStringID IS NULL
								AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');
			END IF;

			IF totaldialstringcode = 0
			THEN

				INSERT INTO tmp_RateTableDIDRateDialString_
				SELECT DISTINCT
					`TempRateTableDIDRateID`,
					`CodeDeckId`,
					`TimezonesID`,
					`OriginationCode`,
					`OriginationDescription`,
					`DialString`,
					CASE WHEN ds.Description IS NULL OR ds.Description = ''
					THEN
						tblTempRateTableDIDRate.Description
					ELSE
						ds.Description
					END
					AS Description,
					`CityTariff`,
					`AccessType`,
					`OneOffCost`,
					`MonthlyCost`,
					`CostPerCall`,
					`CostPerMinute`,
					`SurchargePerCall`,
					`SurchargePerMinute`,
					`OutpaymentPerCall`,
					`OutpaymentPerMinute`,
					`Surcharges`,
					`Chargeback`,
					`CollectionCostAmount`,
					`CollectionCostPercentage`,
					`RegistrationCostPerNumber`,
					`OneOffCostCurrency`,
					`MonthlyCostCurrency`,
					`CostPerCallCurrency`,
					`CostPerMinuteCurrency`,
					`SurchargePerCallCurrency`,
					`SurchargePerMinuteCurrency`,
					`OutpaymentPerCallCurrency`,
					`OutpaymentPerMinuteCurrency`,
					`SurchargesCurrency`,
					`ChargebackCurrency`,
					`CollectionCostAmountCurrency`,
					`RegistrationCostPerNumberCurrency`,
					`EffectiveDate`,
					`EndDate`,
					`Change`,
					`ProcessId`,
					tblTempRateTableDIDRate.DialStringPrefix as DialStringPrefix
				FROM tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
				INNER JOIN tmp_DialString_ ds
					ON ( (tblTempRateTableDIDRate.Code = ds.ChargeCode AND tblTempRateTableDIDRate.DialStringPrefix = '') OR (tblTempRateTableDIDRate.DialStringPrefix != '' AND tblTempRateTableDIDRate.DialStringPrefix =  ds.DialString AND tblTempRateTableDIDRate.Code = ds.ChargeCode  ))
				WHERE tblTempRateTableDIDRate.ProcessId = p_processId
					AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');


				INSERT INTO tmp_RateTableDIDRateDialString_2
				SELECT *  FROM tmp_RateTableDIDRateDialString_ where DialStringPrefix!='';

				Delete From tmp_RateTableDIDRateDialString_
				Where DialStringPrefix = ''
				And Code IN (Select DialStringPrefix From tmp_RateTableDIDRateDialString_2);

				INSERT INTO tmp_RateTableDIDRateDialString_3
				SELECT * FROM tmp_RateTableDIDRateDialString_;


				DELETE  FROM tmp_TempRateTableDIDRate_ WHERE  ProcessId = p_processId;

				INSERT INTO tmp_TempRateTableDIDRate_(
					`TempRateTableDIDRateID`,
					CodeDeckId,
					TimezonesID,
					OriginationCode,
					OriginationDescription,
					Code,
					Description,
					CityTariff,
					AccessType,
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
					`Change`,
					ProcessId,
					DialStringPrefix
				)
				SELECT DISTINCT
					`TempRateTableDIDRateID`,
					`CodeDeckId`,
					`TimezonesID`,
					`OriginationCode`,
					`OriginationDescription`,
					`Code`,
					`Description`,
					`CityTariff`,
					`AccessType`,
					`OneOffCost`,
					`MonthlyCost`,
					`CostPerCall`,
					`CostPerMinute`,
					`SurchargePerCall`,
					`SurchargePerMinute`,
					`OutpaymentPerCall`,
					`OutpaymentPerMinute`,
					`Surcharges`,
					`Chargeback`,
					`CollectionCostAmount`,
					`CollectionCostPercentage`,
					`RegistrationCostPerNumber`,
					`OneOffCostCurrency`,
					`MonthlyCostCurrency`,
					`CostPerCallCurrency`,
					`CostPerMinuteCurrency`,
					`SurchargePerCallCurrency`,
					`SurchargePerMinuteCurrency`,
					`OutpaymentPerCallCurrency`,
					`OutpaymentPerMinuteCurrency`,
					`SurchargesCurrency`,
					`ChargebackCurrency`,
					`CollectionCostAmountCurrency`,
					`RegistrationCostPerNumberCurrency`,
					`EffectiveDate`,
					`EndDate`,
					`Change`,
					`ProcessId`,
					DialStringPrefix
				FROM tmp_RateTableDIDRateDialString_3;

			END IF;

		END IF;

	END IF;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_SplitRateTableDIDRate`;
DELIMITER //
CREATE PROCEDURE `prc_SplitRateTableDIDRate`(
	IN `p_processId` VARCHAR(200),
	IN `p_dialcodeSeparator` VARCHAR(50),
	IN `p_seperatecolumn` INT
)
ThisSP:BEGIN

	DECLARE i INTEGER;
	DECLARE v_rowCount_ INT;
	DECLARE v_pointer_ INT;
	DECLARE v_TempRateTableDIDRateID_ INT;
	DECLARE v_OriginationCode_ TEXT;
	DECLARE v_OriginationCountryCode_ VARCHAR(500);
	DECLARE v_Code_ TEXT;
	DECLARE v_CountryCode_ VARCHAR(500);
	DECLARE newcodecount INT(11) DEFAULT 0;

	IF p_dialcodeSeparator !='null'
	THEN

		DROP TEMPORARY TABLE IF EXISTS `my_splits`;
		CREATE TEMPORARY TABLE `my_splits` (
			`TempRateTableDIDRateID` INT(11) NULL DEFAULT NULL,
			`OriginationCode` Text NULL DEFAULT NULL,
			`OriginationCountryCode` Text NULL DEFAULT NULL,
			`Code` Text NULL DEFAULT NULL,
			`CountryCode` Text NULL DEFAULT NULL
		);

		SET i = 1;
		REPEAT
			/*
				p_seperatecolumn = 1 = Origination Code
				p_seperatecolumn = 2 = Destination Code
			*/
			IF(p_seperatecolumn = 1)
			THEN
				INSERT INTO my_splits (TempRateTableDIDRateID, OriginationCode, OriginationCountryCode, Code, CountryCode)
				SELECT TempRateTableDIDRateID , FnStringSplit(OriginationCode, p_dialcodeSeparator, i), OriginationCountryCode, Code, CountryCode  FROM tblTempRateTableDIDRate
				WHERE FnStringSplit(OriginationCode, p_dialcodeSeparator , i) IS NOT NULL
					AND ProcessId = p_processId;
			ELSE
				INSERT INTO my_splits (TempRateTableDIDRateID, OriginationCode, OriginationCountryCode, Code, CountryCode)
				SELECT TempRateTableDIDRateID , OriginationCode, OriginationCountryCode, FnStringSplit(Code, p_dialcodeSeparator, i), CountryCode  FROM tblTempRateTableDIDRate
				WHERE FnStringSplit(Code, p_dialcodeSeparator , i) IS NOT NULL
					AND ProcessId = p_processId;
			END IF;

			SET i = i + 1;
			UNTIL ROW_COUNT() = 0
		END REPEAT;

		UPDATE my_splits SET OriginationCode = trim(OriginationCode), Code = trim(Code);



		INSERT INTO my_splits (TempRateTableDIDRateID, OriginationCode, OriginationCountryCode, Code, CountryCode)
		SELECT TempRateTableDIDRateID, OriginationCode, OriginationCountryCode, Code, CountryCode  FROM tblTempRateTableDIDRate
		WHERE
			(
				(p_seperatecolumn = 1 AND (OriginationCountryCode IS NOT NULL AND OriginationCountryCode <> '') AND (OriginationCode IS NULL OR OriginationCode = '')) OR
				(p_seperatecolumn = 2 AND (CountryCode IS NOT NULL AND CountryCode <> '') AND (Code IS NULL OR Code = ''))
			)
		AND ProcessId = p_processId;


		DROP TEMPORARY TABLE IF EXISTS tmp_newratetable_splite_;
		CREATE TEMPORARY TABLE tmp_newratetable_splite_  (
			RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			TempRateTableDIDRateID INT(11) NULL DEFAULT NULL,
			OriginationCode VARCHAR(500) NULL DEFAULT NULL,
			OriginationCountryCode VARCHAR(500) NULL DEFAULT NULL,
			Code VARCHAR(500) NULL DEFAULT NULL,
			CountryCode VARCHAR(500) NULL DEFAULT NULL
		);

		INSERT INTO tmp_newratetable_splite_(TempRateTableDIDRateID,OriginationCode,OriginationCountryCode,Code,CountryCode)
		SELECT
			TempRateTableDIDRateID,
			OriginationCode,
			OriginationCountryCode,
			Code,
			CountryCode
		FROM my_splits
		WHERE
			((p_seperatecolumn = 1 AND OriginationCode like '%-%') OR (p_seperatecolumn = 2 AND Code like '%-%'))
			AND TempRateTableDIDRateID IS NOT NULL;

		SET v_pointer_ = 1;
		SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_newratetable_splite_);

		WHILE v_pointer_ <= v_rowCount_
		DO
			SET v_TempRateTableDIDRateID_ = (SELECT TempRateTableDIDRateID FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);
			SET v_OriginationCode_ = (SELECT OriginationCode FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);
			SET v_OriginationCountryCode_ = (SELECT OriginationCountryCode FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);
			SET v_Code_ = (SELECT Code FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);
			SET v_CountryCode_ = (SELECT CountryCode FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);

			Call prc_SplitAndInsertRateTableDIDRate(v_TempRateTableDIDRateID_,p_seperatecolumn,v_OriginationCode_,v_OriginationCountryCode_,v_Code_,v_CountryCode_);

			SET v_pointer_ = v_pointer_ + 1;
		END WHILE;

		DELETE FROM my_splits
		WHERE
			((p_seperatecolumn = 1 AND OriginationCode like '%-%') OR (p_seperatecolumn = 2 AND Code like '%-%'))
			AND TempRateTableDIDRateID IS NOT NULL;

		DELETE FROM my_splits
		WHERE (Code = '' OR Code IS NULL) AND (CountryCode = '' OR CountryCode IS NULL);

		INSERT INTO tmp_split_RateTableDIDRate_
		SELECT DISTINCT
			my_splits.TempRateTableDIDRateID as `TempRateTableDIDRateID`,
			`CodeDeckId`,
			`TimezonesID`,
			CONCAT(IFNULL(my_splits.OriginationCountryCode,''),my_splits.OriginationCode) as OriginationCode,
			`OriginationDescription`,
			CONCAT(IFNULL(my_splits.CountryCode,''),my_splits.Code) as Code,
			`Description`,
			`City`,
			`Tariff`,
			`OneOffCost`,
			`AccessType`,
			`MonthlyCost`,
			`CostPerCall`,
			`CostPerMinute`,
			`SurchargePerCall`,
			`SurchargePerMinute`,
			`OutpaymentPerCall`,
			`OutpaymentPerMinute`,
			`Surcharges`,
			`Chargeback`,
			`CollectionCostAmount`,
			`CollectionCostPercentage`,
			`RegistrationCostPerNumber`,
			`OneOffCostCurrency`,
			`MonthlyCostCurrency`,
			`CostPerCallCurrency`,
			`CostPerMinuteCurrency`,
			`SurchargePerCallCurrency`,
			`SurchargePerMinuteCurrency`,
			`OutpaymentPerCallCurrency`,
			`OutpaymentPerMinuteCurrency`,
			`SurchargesCurrency`,
			`ChargebackCurrency`,
			`CollectionCostAmountCurrency`,
			`RegistrationCostPerNumberCurrency`,
			`EffectiveDate`,
			`EndDate`,
			`Change`,
			`ProcessId`,
			`DialStringPrefix`
		FROM my_splits
		INNER JOIN tblTempRateTableDIDRate
			ON my_splits.TempRateTableDIDRateID = tblTempRateTableDIDRate.TempRateTableDIDRateID
		WHERE	tblTempRateTableDIDRate.ProcessId = p_processId;

	END IF;

	IF p_dialcodeSeparator = 'null'
	THEN

		INSERT INTO tmp_split_RateTableDIDRate_
		SELECT DISTINCT
			`TempRateTableDIDRateID`,
			`CodeDeckId`,
			`TimezonesID`,
			CONCAT(IFNULL(tblTempRateTableDIDRate.OriginationCountryCode,''),tblTempRateTableDIDRate.OriginationCode) as OriginationCode,
			`OriginationDescription`,
			CONCAT(IFNULL(tblTempRateTableDIDRate.CountryCode,''),tblTempRateTableDIDRate.Code) as Code,
			`Description`,
			`City`,
			`Tariff`,
			`AccessType`,
			`OneOffCost`,
			`MonthlyCost`,
			`CostPerCall`,
			`CostPerMinute`,
			`SurchargePerCall`,
			`SurchargePerMinute`,
			`OutpaymentPerCall`,
			`OutpaymentPerMinute`,
			`Surcharges`,
			`Chargeback`,
			`CollectionCostAmount`,
			`CollectionCostPercentage`,
			`RegistrationCostPerNumber`,
			`OneOffCostCurrency`,
			`MonthlyCostCurrency`,
			`CostPerCallCurrency`,
			`CostPerMinuteCurrency`,
			`SurchargePerCallCurrency`,
			`SurchargePerMinuteCurrency`,
			`OutpaymentPerCallCurrency`,
			`OutpaymentPerMinuteCurrency`,
			`SurchargesCurrency`,
			`ChargebackCurrency`,
			`CollectionCostAmountCurrency`,
			`RegistrationCostPerNumberCurrency`,
			`EffectiveDate`,
			`EndDate`,
			`Change`,
			`ProcessId`,
			`DialStringPrefix`
		FROM tblTempRateTableDIDRate
		WHERE ProcessId = p_processId;

	END IF;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_getReviewRateTableDIDRates`;
DELIMITER //
CREATE PROCEDURE `prc_getReviewRateTableDIDRates`(
	IN `p_ProcessID` VARCHAR(50),
	IN `p_Action` VARCHAR(50),
	IN `p_Origination_Code` VARCHAR(50),
	IN `p_Code` VARCHAR(50),
	IN `p_Timezone` INT,
	IN `p_City` VARCHAR(50),
	IN `p_Tariff` VARCHAR(50),
	IN `p_AccessType` VARCHAR(200),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
BEGIN
	DECLARE v_OffSet_ int;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	IF p_isExport = 0
	THEN
		SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

		SELECT
			IF(p_Action='Deleted',RateTableDIDRateID,TempRateTableDIDRateID) AS RateTableDIDRateID,
			RTCL.AccessType,
			OriginationCode,
			RTCL.Code,
			RTCL.City,
			RTCL.Tariff,
			tz.Title,
			CONCAT(IFNULL(tblOneOffCostCurrency.Symbol,''), IFNULL(OneOffCost,'')) AS OneOffCost,
			CONCAT(IFNULL(tblMonthlyCostCurrency.Symbol,''), IFNULL(MonthlyCost,'')) AS MonthlyCost,
			CONCAT(IFNULL(tblCostPerCallCurrency.Symbol,''), IFNULL(CostPerCall,'')) AS CostPerCall,
			CONCAT(IFNULL(tblCostPerMinuteCurrency.Symbol,''), IFNULL(CostPerMinute,'')) AS CostPerMinute,
			CONCAT(IFNULL(tblSurchargePerCallCurrency.Symbol,''), IFNULL(SurchargePerCall,'')) AS SurchargePerCall,
			CONCAT(IFNULL(tblSurchargePerMinuteCurrency.Symbol,''), IFNULL(SurchargePerMinute,'')) AS SurchargePerMinute,
			CONCAT(IFNULL(tblOutpaymentPerCallCurrency.Symbol,''), IFNULL(OutpaymentPerCall,'')) AS OutpaymentPerCall,
			CONCAT(IFNULL(tblOutpaymentPerMinuteCurrency.Symbol,''), IFNULL(OutpaymentPerMinute,'')) AS OutpaymentPerMinute,
			CONCAT(IFNULL(tblSurchargesCurrency.Symbol,''), IFNULL(Surcharges,'')) AS Surcharges,
			CONCAT(IFNULL(tblChargebackCurrency.Symbol,''), IFNULL(Chargeback,'')) AS Chargeback,
			CONCAT(IFNULL(tblCollectionCostAmountCurrency.Symbol,''), IFNULL(CollectionCostAmount,'')) AS CollectionCostAmount,
			CollectionCostPercentage,
			CONCAT(IFNULL(tblRegistrationCostPerNumberCurrency.Symbol,''), IFNULL(RegistrationCostPerNumber,'')) AS RegistrationCostPerNumber,
			EffectiveDate,
			EndDate
		FROM
			tblRateTableDIDRateChangeLog AS RTCL
		JOIN
			tblTimezones tz ON RTCL.TimezonesID = tz.TimezonesID
		LEFT JOIN tblCurrency AS tblOneOffCostCurrency
			ON tblOneOffCostCurrency.CurrencyID = RTCL.OneOffCostCurrency
		LEFT JOIN tblCurrency AS tblMonthlyCostCurrency
			ON tblMonthlyCostCurrency.CurrencyID = RTCL.MonthlyCostCurrency
		LEFT JOIN tblCurrency AS tblCostPerCallCurrency
			ON tblCostPerCallCurrency.CurrencyID = RTCL.CostPerCallCurrency
		LEFT JOIN tblCurrency AS tblCostPerMinuteCurrency
			ON tblCostPerMinuteCurrency.CurrencyID = RTCL.CostPerMinuteCurrency
		LEFT JOIN tblCurrency AS tblSurchargePerCallCurrency
			ON tblSurchargePerCallCurrency.CurrencyID = RTCL.SurchargePerCallCurrency
		LEFT JOIN tblCurrency AS tblSurchargePerMinuteCurrency
			ON tblSurchargePerMinuteCurrency.CurrencyID = RTCL.SurchargePerMinuteCurrency
		LEFT JOIN tblCurrency AS tblOutpaymentPerCallCurrency
			ON tblOutpaymentPerCallCurrency.CurrencyID = RTCL.OutpaymentPerCallCurrency
		LEFT JOIN tblCurrency AS tblOutpaymentPerMinuteCurrency
			ON tblOutpaymentPerMinuteCurrency.CurrencyID = RTCL.OutpaymentPerMinuteCurrency
		LEFT JOIN tblCurrency AS tblSurchargesCurrency
			ON tblSurchargesCurrency.CurrencyID = RTCL.SurchargesCurrency
		LEFT JOIN tblCurrency AS tblChargebackCurrency
			ON tblChargebackCurrency.CurrencyID = RTCL.ChargebackCurrency
		LEFT JOIN tblCurrency AS tblCollectionCostAmountCurrency
			ON tblCollectionCostAmountCurrency.CurrencyID = RTCL.CollectionCostAmountCurrency
		LEFT JOIN tblCurrency AS tblRegistrationCostPerNumberCurrency
			ON tblRegistrationCostPerNumberCurrency.CurrencyID = RTCL.RegistrationCostPerNumberCurrency
		WHERE
			ProcessID = p_ProcessID AND Action = p_Action AND
			RTCL.TimezonesID = p_Timezone AND
			(p_Origination_Code IS NULL OR OriginationCode LIKE REPLACE(p_Origination_Code, '*', '%')) AND
			(p_Code IS NULL OR p_Code = '' OR RTCL.Code LIKE REPLACE(p_Code, '*', '%')) AND
			(p_City IS NULL OR RTCL.City = p_City) AND
			(p_Tariff IS NULL OR RTCL.Tariff = p_Tariff) AND
			(p_AccessType IS NULL OR RTCL.AccessType = p_AccessType)
		ORDER BY
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeASC') THEN OriginationCode
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OriginationCodeDESC') THEN OriginationCode
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN RTCL.Code
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN RTCL.Code
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CityDESC') THEN RTCL.City
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CityASC') THEN RTCL.City
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TariffDESC') THEN RTCL.Tariff
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TariffASC') THEN RTCL.Tariff
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccessTypeDESC') THEN RTCL.AccessType
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccessTypeASC') THEN RTCL.AccessType
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OneOffCostDESC') THEN OneOffCost
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OneOffCostASC') THEN OneOffCost
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MonthlyCostDESC') THEN MonthlyCost
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MonthlyCostASC') THEN MonthlyCost
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerCallDESC') THEN CostPerCall
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerCallASC') THEN CostPerCall
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerMinuteDESC') THEN CostPerMinute
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CostPerMinuteASC') THEN CostPerMinute
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerCallDESC') THEN SurchargePerCall
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerCallASC') THEN SurchargePerCall
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerMinuteDESC') THEN SurchargePerMinute
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargePerMinuteASC') THEN SurchargePerMinute
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerCallDESC') THEN OutpaymentPerCall
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerCallASC') THEN OutpaymentPerCall
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerMinuteDESC') THEN OutpaymentPerMinute
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutpaymentPerMinuteASC') THEN OutpaymentPerMinute
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargesDESC') THEN Surcharges
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SurchargesASC') THEN Surcharges
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ChargebackDESC') THEN Chargeback
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ChargebackASC') THEN Chargeback
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostAmountDESC') THEN CollectionCostAmount
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostAmountASC') THEN CollectionCostAmount
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostPercentageDESC') THEN CollectionCostPercentage
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CollectionCostPercentageASC') THEN CollectionCostPercentage
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RegistrationCostPerNumberDESC') THEN RegistrationCostPerNumber
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RegistrationCostPerNumberASC') THEN RegistrationCostPerNumber
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateDESC') THEN EffectiveDate
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateASC') THEN EffectiveDate
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateDESC') THEN EndDate
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateASC') THEN EndDate
			END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT
			COUNT(*) AS totalcount
		FROM
			tblRateTableDIDRateChangeLog AS RTCL
		JOIN
			tblTimezones tz ON RTCL.TimezonesID = tz.TimezonesID
		WHERE
			ProcessID = p_ProcessID AND Action = p_Action AND
			RTCL.TimezonesID = p_Timezone AND
			(p_Origination_Code IS NULL OR OriginationCode LIKE REPLACE(p_Origination_Code, '*', '%')) AND
			(p_Code IS NULL OR p_Code = '' OR Code LIKE REPLACE(p_Code, '*', '%')) AND
			(p_City IS NULL OR RTCL.City = p_City) AND
			(p_Tariff IS NULL OR RTCL.Tariff = p_Tariff) AND
			(p_AccessType IS NULL OR RTCL.AccessType = p_AccessType);
	END IF;

	IF p_isExport = 1
	THEN
		SELECT
			distinct
			RTCL.AccessType AS `Access Type`,
			OriginationCode AS Origination,
			RTCL.Code AS Prefix,
			RTCL.City AS City,
			RTCL.Tariff AS Tariff,
			tz.Title AS `Time Of Day`,
			CONCAT(IFNULL(tblOneOffCostCurrency.Symbol,''), IFNULL(OneOffCost,'')) AS `One Off Cost`,
			CONCAT(IFNULL(tblMonthlyCostCurrency.Symbol,''), IFNULL(MonthlyCost,'')) AS `Monthly Cost`,
			CONCAT(IFNULL(tblCostPerCallCurrency.Symbol,''), IFNULL(CostPerCall,'')) AS `Cost Per Call`,
			CONCAT(IFNULL(tblCostPerMinuteCurrency.Symbol,''), IFNULL(CostPerMinute,'')) AS `Cost Per Minute`,
			CONCAT(IFNULL(tblSurchargePerCallCurrency.Symbol,''), IFNULL(SurchargePerCall,'')) AS `Surcharge Per Call`,
			CONCAT(IFNULL(tblSurchargePerMinuteCurrency.Symbol,''), IFNULL(SurchargePerMinute,'')) AS `Surcharge Per Minute`,
			CONCAT(IFNULL(tblOutpaymentPerCallCurrency.Symbol,''), IFNULL(OutpaymentPerCall,'')) AS `Outpayment Per Call`,
			CONCAT(IFNULL(tblOutpaymentPerMinuteCurrency.Symbol,''), IFNULL(OutpaymentPerMinute,'')) AS `Outpayment Per Minute`,
			CONCAT(IFNULL(tblSurchargesCurrency.Symbol,''), IFNULL(Surcharges,'')) AS Surcharges,
			CONCAT(IFNULL(tblChargebackCurrency.Symbol,''), IFNULL(Chargeback,'')) AS Chargeback,
			CONCAT(IFNULL(tblCollectionCostAmountCurrency.Symbol,''), IFNULL(CollectionCostAmount,'')) AS `Collection Cost Amount`,
			CollectionCostPercentage AS `Collection Cost Percentage`,
			CONCAT(IFNULL(tblRegistrationCostPerNumberCurrency.Symbol,''), IFNULL(RegistrationCostPerNumber,'')) AS `Registration Cost Per Number`,
			EffectiveDate AS `Effective Date`,
			EndDate AS `End Date`
		FROM
			tblRateTableDIDRateChangeLog AS RTCL
		JOIN
			tblTimezones tz ON RTCL.TimezonesID = tz.TimezonesID
		LEFT JOIN tblCurrency AS tblOneOffCostCurrency
			ON tblOneOffCostCurrency.CurrencyID = RTCL.OneOffCostCurrency
		LEFT JOIN tblCurrency AS tblMonthlyCostCurrency
			ON tblMonthlyCostCurrency.CurrencyID = RTCL.MonthlyCostCurrency
		LEFT JOIN tblCurrency AS tblCostPerCallCurrency
			ON tblCostPerCallCurrency.CurrencyID = RTCL.CostPerCallCurrency
		LEFT JOIN tblCurrency AS tblCostPerMinuteCurrency
			ON tblCostPerMinuteCurrency.CurrencyID = RTCL.CostPerMinuteCurrency
		LEFT JOIN tblCurrency AS tblSurchargePerCallCurrency
			ON tblSurchargePerCallCurrency.CurrencyID = RTCL.SurchargePerCallCurrency
		LEFT JOIN tblCurrency AS tblSurchargePerMinuteCurrency
			ON tblSurchargePerMinuteCurrency.CurrencyID = RTCL.SurchargePerMinuteCurrency
		LEFT JOIN tblCurrency AS tblOutpaymentPerCallCurrency
			ON tblOutpaymentPerCallCurrency.CurrencyID = RTCL.OutpaymentPerCallCurrency
		LEFT JOIN tblCurrency AS tblOutpaymentPerMinuteCurrency
			ON tblOutpaymentPerMinuteCurrency.CurrencyID = RTCL.OutpaymentPerMinuteCurrency
		LEFT JOIN tblCurrency AS tblSurchargesCurrency
			ON tblSurchargesCurrency.CurrencyID = RTCL.SurchargesCurrency
		LEFT JOIN tblCurrency AS tblChargebackCurrency
			ON tblChargebackCurrency.CurrencyID = RTCL.ChargebackCurrency
		LEFT JOIN tblCurrency AS tblCollectionCostAmountCurrency
			ON tblCollectionCostAmountCurrency.CurrencyID = RTCL.CollectionCostAmountCurrency
		LEFT JOIN tblCurrency AS tblRegistrationCostPerNumberCurrency
			ON tblRegistrationCostPerNumberCurrency.CurrencyID = RTCL.RegistrationCostPerNumberCurrency
		WHERE
			ProcessID = p_ProcessID AND Action = p_Action AND
			RTCL.TimezonesID = p_Timezone AND
			(p_Origination_Code IS NULL OR OriginationCode LIKE REPLACE(p_Origination_Code, '*', '%')) AND
			(p_Code IS NULL OR p_Code = '' OR RTCL.Code LIKE REPLACE(p_Code, '*', '%')) AND
			(p_City IS NULL OR RTCL.City = p_City) AND
			(p_Tariff IS NULL OR RTCL.Tariff = p_Tariff) AND
			(p_AccessType IS NULL OR RTCL.AccessType = p_AccessType);
	END IF;


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_WSProcessRateTableDIDRate`;
DELIMITER //
CREATE PROCEDURE `prc_WSProcessRateTableDIDRate`(
	IN `p_RateTableId` INT,
	IN `p_replaceAllRates` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_processId` VARCHAR(200),
	IN `p_addNewCodesToCodeDeck` INT,
	IN `p_companyId` INT,
	IN `p_dialstringid` INT,
	IN `p_dialcodeSeparator` VARCHAR(50),
	IN `p_seperatecolumn` INT,
	IN `p_CurrencyID` INT,
	IN `p_list_option` INT,
	IN `p_UserName` TEXT
)
ThisSP:BEGIN

	DECLARE v_AffectedRecords_ INT DEFAULT 0;
	DECLARE v_CodeDeckId_ INT ;
	DECLARE totaldialstringcode INT(11) DEFAULT 0;
	DECLARE newstringcode INT(11) DEFAULT 0;
	DECLARE totalduplicatecode INT(11);
	DECLARE errormessage longtext;
	DECLARE errorheader longtext;
	DECLARE v_RateTableCurrencyID_ INT;
	DECLARE v_CompanyCurrencyID_ INT;
	DECLARE v_RateApprovalProcess_ INT;
	DECLARE v_RateTableAppliedTo_ INT;

	DECLARE v_pointer_ INT;
	DECLARE v_rowCount_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT Value INTO v_RateApprovalProcess_ FROM tblCompanySetting WHERE CompanyID = p_companyId AND `Key`='RateApprovalProcess';
	SELECT AppliedTo INTO v_RateTableAppliedTo_ FROM tblRateTable WHERE RateTableID = p_RateTableId;

	DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
	CREATE TEMPORARY TABLE tmp_JobLog_ (
		Message longtext
	);

    DROP TEMPORARY TABLE IF EXISTS tmp_TempTimezones_;
    CREATE TEMPORARY TABLE tmp_TempTimezones_ (
        TimezonesID INT
    );

	DROP TEMPORARY TABLE IF EXISTS tmp_split_RateTableDIDRate_;
	CREATE TEMPORARY TABLE tmp_split_RateTableDIDRate_ (
		`TempRateTableDIDRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NULL DEFAULT NULL,
		`OneOffCost` decimal(18,6) DEFAULT NULL,
	  	`MonthlyCost` decimal(18,6) DEFAULT NULL,
	  	`CostPerCall` decimal(18,6) DEFAULT NULL,
	  	`CostPerMinute` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerCall` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerMinute` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerCall` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerMinute` decimal(18,6) DEFAULT NULL,
	  	`Surcharges` decimal(18,6) DEFAULT NULL,
	  	`Chargeback` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostAmount` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostPercentage` decimal(18,6) DEFAULT NULL,
	  	`RegistrationCostPerNumber` decimal(18,6) DEFAULT NULL,
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`DialStringPrefix` varchar(500) ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_OriginationCode (`OriginationCode`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableDIDRate_;
	CREATE TEMPORARY TABLE tmp_TempRateTableDIDRate_ (
		TempRateTableDIDRateID int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NULL DEFAULT NULL,
		`OneOffCost` decimal(18,6) DEFAULT NULL,
	  	`MonthlyCost` decimal(18,6) DEFAULT NULL,
	  	`CostPerCall` decimal(18,6) DEFAULT NULL,
	  	`CostPerMinute` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerCall` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerMinute` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerCall` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerMinute` decimal(18,6) DEFAULT NULL,
	  	`Surcharges` decimal(18,6) DEFAULT NULL,
	  	`Chargeback` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostAmount` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostPercentage` decimal(18,6) DEFAULT NULL,
	  	`RegistrationCostPerNumber` decimal(18,6) DEFAULT NULL,
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`DialStringPrefix` varchar(500) ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_OriginationCode (`OriginationCode`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_Delete_RateTableDIDRate;
	CREATE TEMPORARY TABLE tmp_Delete_RateTableDIDRate (
		RateTableDIDRateID INT,
		RateTableId INT,
		TimezonesID INT,
		OriginationRateID INT,
		OriginationCode VARCHAR(50),
		OriginationDescription VARCHAR(200),
		RateId INT,
		Code VARCHAR(50),
		Description VARCHAR(200),
		City varchar(50) NOT NULL DEFAULT '',
		Tariff varchar(50) NOT NULL DEFAULT '',
		AccessType varchar(200) NULL DEFAULT NULL,
		OneOffCost decimal(18,6) DEFAULT NULL,
	  	MonthlyCost decimal(18,6) DEFAULT NULL,
	  	CostPerCall decimal(18,6) DEFAULT NULL,
	  	CostPerMinute decimal(18,6) DEFAULT NULL,
	  	SurchargePerCall decimal(18,6) DEFAULT NULL,
	  	SurchargePerMinute decimal(18,6) DEFAULT NULL,
	  	OutpaymentPerCall decimal(18,6) DEFAULT NULL,
	  	OutpaymentPerMinute decimal(18,6) DEFAULT NULL,
	  	Surcharges decimal(18,6) DEFAULT NULL,
	  	Chargeback decimal(18,6) DEFAULT NULL,
	  	CollectionCostAmount decimal(18,6) DEFAULT NULL,
	  	CollectionCostPercentage decimal(18,6) DEFAULT NULL,
	  	RegistrationCostPerNumber decimal(18,6) DEFAULT NULL,
		OneOffCostCurrency INT(11) NULL DEFAULT NULL,
		MonthlyCostCurrency INT(11) NULL DEFAULT NULL,
		CostPerCallCurrency INT(11) NULL DEFAULT NULL,
		CostPerMinuteCurrency INT(11) NULL DEFAULT NULL,
		SurchargePerCallCurrency INT(11) NULL DEFAULT NULL,
		SurchargePerMinuteCurrency INT(11) NULL DEFAULT NULL,
		OutpaymentPerCallCurrency INT(11) NULL DEFAULT NULL,
		OutpaymentPerMinuteCurrency INT(11) NULL DEFAULT NULL,
		SurchargesCurrency INT(11) NULL DEFAULT NULL,
		ChargebackCurrency INT(11) NULL DEFAULT NULL,
		CollectionCostAmountCurrency INT(11) NULL DEFAULT NULL,
		RegistrationCostPerNumberCurrency INT(11) NULL DEFAULT NULL,
		EffectiveDate DATETIME,
		EndDate Datetime ,
		deleted_at DATETIME,
		INDEX tmp_RateTableDIDRateDiscontinued_RateTableDIDRateID (`RateTableDIDRateID`)
	);

	CALL  prc_RateTableDIDRateCheckDialstringAndDupliacteCode(p_companyId,p_processId,p_dialstringid,p_effectiveImmediately,p_dialcodeSeparator,p_seperatecolumn);

	SELECT COUNT(*) AS COUNT INTO newstringcode from tmp_JobLog_;

 	INSERT INTO tmp_TempTimezones_
 	SELECT DISTINCT TimezonesID from tmp_TempRateTableDIDRate_;

	IF newstringcode = 0
	THEN

		IF (SELECT count(*) FROM tblRateTableDIDRateChangeLog WHERE ProcessID = p_processId ) > 0
		THEN

			UPDATE
				tblRateTableDIDRate vr
			INNER JOIN tblRateTableDIDRateChangeLog  vrcl
			on vrcl.RateTableDIDRateID = vr.RateTableDIDRateID
			SET
				vr.EndDate = IFNULL(vrcl.EndDate,date(now()))
			WHERE vrcl.ProcessID = p_processId
				AND vrcl.`Action`  ='Deleted';


			UPDATE tmp_TempRateTableDIDRate_ tblTempRateTableDIDRate
			JOIN tblRateTableDIDRateChangeLog vrcl
				ON  vrcl.ProcessId = p_processId
				AND vrcl.Code = tblTempRateTableDIDRate.Code
				AND vrcl.OriginationCode = tblTempRateTableDIDRate.OriginationCode
			SET
				tblTempRateTableDIDRate.EndDate = vrcl.EndDate
			WHERE
				vrcl.`Action` = 'Deleted'
				AND vrcl.EndDate IS NOT NULL ;


		END IF;


		IF  p_replaceAllRates = 1
		THEN

			UPDATE tblRateTableDIDRate
				SET tblRateTableDIDRate.EndDate = date(now())
			WHERE RateTableId = p_RateTableId;

		END IF;



		IF p_list_option = 1
		THEN

			INSERT INTO tmp_Delete_RateTableDIDRate(
				RateTableDIDRateID,
				RateTableId,
				TimezonesID,
				OriginationRateID,
				OriginationCode,
				OriginationDescription,
				RateId,
				Code,
				Description,
				City,
				Tariff,
            AccessType,
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
				deleted_at
			)
			SELECT DISTINCT
				tblRateTableDIDRate.RateTableDIDRateID,
				p_RateTableId AS RateTableId,
				tblRateTableDIDRate.TimezonesID,
				tblRateTableDIDRate.OriginationRateID,
				OriginationRate.Code AS OriginationCode,
				OriginationRate.Description AS OriginationDescription,
				tblRateTableDIDRate.RateId,
				tblRate.Code,
				tblRate.Description,
				tblRateTableDIDRate.City,
				tblRateTableDIDRate.Tariff,
            tblRateTableDIDRate.AccessType,
				tblRateTableDIDRate.OneOffCost,
				tblRateTableDIDRate.MonthlyCost,
				tblRateTableDIDRate.CostPerCall,
				tblRateTableDIDRate.CostPerMinute,
				tblRateTableDIDRate.SurchargePerCall,
				tblRateTableDIDRate.SurchargePerMinute,
				tblRateTableDIDRate.OutpaymentPerCall,
				tblRateTableDIDRate.OutpaymentPerMinute,
				tblRateTableDIDRate.Surcharges,
				tblRateTableDIDRate.Chargeback,
				tblRateTableDIDRate.CollectionCostAmount,
				tblRateTableDIDRate.CollectionCostPercentage,
				tblRateTableDIDRate.RegistrationCostPerNumber,
				tblRateTableDIDRate.OneOffCostCurrency,
				tblRateTableDIDRate.MonthlyCostCurrency,
				tblRateTableDIDRate.CostPerCallCurrency,
				tblRateTableDIDRate.CostPerMinuteCurrency,
				tblRateTableDIDRate.SurchargePerCallCurrency,
				tblRateTableDIDRate.SurchargePerMinuteCurrency,
				tblRateTableDIDRate.OutpaymentPerCallCurrency,
				tblRateTableDIDRate.OutpaymentPerMinuteCurrency,
				tblRateTableDIDRate.SurchargesCurrency,
				tblRateTableDIDRate.ChargebackCurrency,
				tblRateTableDIDRate.CollectionCostAmountCurrency,
				tblRateTableDIDRate.RegistrationCostPerNumberCurrency,
				tblRateTableDIDRate.EffectiveDate,
				IFNULL(tblRateTableDIDRate.EndDate,date(now())) ,
				now() AS deleted_at
			FROM tblRateTableDIDRate
			JOIN tblRate
				ON tblRate.RateID = tblRateTableDIDRate.RateId
				AND tblRate.CompanyID = p_companyId
			LEFT JOIN tblRate AS OriginationRate
				ON OriginationRate.RateID = tblRateTableDIDRate.OriginationRateID
				AND OriginationRate.CompanyID = p_companyId
		  	/*JOIN tmp_TempTimezones_
		  		ON tmp_TempTimezones_.TimezonesID = tblRateTableDIDRate.TimezonesID*/
			LEFT JOIN tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
				ON tblTempRateTableDIDRate.Code = tblRate.Code
				AND ((tblTempRateTableDIDRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableDIDRate.OriginationCode = OriginationRate.Code))
				AND tblTempRateTableDIDRate.TimezonesID = tblRateTableDIDRate.TimezonesID
				AND tblTempRateTableDIDRate.City = tblRateTableDIDRate.City
				AND tblTempRateTableDIDRate.Tariff = tblRateTableDIDRate.Tariff
				AND  tblTempRateTableDIDRate.ProcessId = p_processId
				AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
			WHERE tblRateTableDIDRate.RateTableId = p_RateTableId
				AND tblTempRateTableDIDRate.Code IS NULL
				AND ( tblRateTableDIDRate.EndDate is NULL OR tblRateTableDIDRate.EndDate <= date(now()) )
			ORDER BY RateTableDIDRateID ASC;


			UPDATE tblRateTableDIDRate
			JOIN tmp_Delete_RateTableDIDRate ON tblRateTableDIDRate.RateTableDIDRateID = tmp_Delete_RateTableDIDRate.RateTableDIDRateID
				SET tblRateTableDIDRate.EndDate = date(now())
			WHERE
				tblRateTableDIDRate.RateTableId = p_RateTableId;

		END IF;


		IF ( (SELECT count(*) FROM tblRateTableDIDRate WHERE  RateTableId = p_RateTableId AND EndDate <= NOW() )  > 0  ) THEN

			call prc_ArchiveOldRateTableDIDRate(p_RateTableId, NULL,p_UserName);

		END IF;

		DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableDIDRate_2;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempRateTableDIDRate_2 AS (SELECT * FROM tmp_TempRateTableDIDRate_);

		IF  p_addNewCodesToCodeDeck = 1
		THEN
			-- Destination Code
			INSERT INTO tblRate (
				CompanyID,
				Code,
				Description,
				CreatedBy,
				CountryID,
				CodeDeckId,
				Interval1,
				IntervalN
			)
			SELECT DISTINCT
				p_companyId,
				vc.Code,
				vc.Description,
				'RMService',
				fnGetCountryIdByCodeAndCountry (vc.Code ,vc.Description) AS CountryID,
				CodeDeckId,
				Interval1,
				IntervalN
			FROM
			(
				SELECT DISTINCT
					tblTempRateTableDIDRate.Code,
					MAX(tblTempRateTableDIDRate.Description) AS Description,
					MAX(tblTempRateTableDIDRate.CodeDeckId) AS CodeDeckId,
					1 AS Interval1,
					1 AS IntervalN
				FROM tmp_TempRateTableDIDRate_  as tblTempRateTableDIDRate
				LEFT JOIN tblRate
					ON tblRate.Code = tblTempRateTableDIDRate.Code
					AND tblRate.CompanyID = p_companyId
					AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
				WHERE tblRate.RateID IS NULL
					AND tblTempRateTableDIDRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
				GROUP BY
					tblTempRateTableDIDRate.Code
			) vc;

			-- Origination Code
			INSERT INTO tblRate (
				CompanyID,
				Code,
				Description,
				CreatedBy,
				CountryID,
				CodeDeckId,
				Interval1,
				IntervalN
			)
			SELECT DISTINCT
				p_companyId,
				vc.Code,
				vc.Description,
				'RMService',
				fnGetCountryIdByCodeAndCountry (vc.Code ,vc.Description) AS CountryID,
				CodeDeckId,
				Interval1,
				IntervalN
			FROM
			(
				SELECT DISTINCT
					tblTempRateTableDIDRate.OriginationCode AS Code,
					MAX(tblTempRateTableDIDRate.OriginationDescription) AS Description,
					MAX(tblTempRateTableDIDRate.CodeDeckId) AS CodeDeckId,
					1 AS Interval1,
					1 AS IntervalN
				FROM tmp_TempRateTableDIDRate_  as tblTempRateTableDIDRate
				LEFT JOIN tblRate
					ON tblRate.Code = tblTempRateTableDIDRate.OriginationCode
					AND tblRate.CompanyID = p_companyId
					AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
				WHERE tblRate.RateID IS NULL
					AND tblTempRateTableDIDRate.OriginationCode IS NOT NULL AND tblTempRateTableDIDRate.OriginationCode != ''
					AND tblTempRateTableDIDRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
				GROUP BY
					tblTempRateTableDIDRate.OriginationCode
			) vc;

		ELSE
			SELECT GROUP_CONCAT(code) into errormessage FROM(
				SELECT DISTINCT
					c.Code as code, 1 as a
				FROM
				(
					SELECT DISTINCT
						temp.Code,
						MAX(temp.Description) AS Description
					FROM
					(
						SELECT DISTINCT
							tblTempRateTableDIDRate.Code,
							tblTempRateTableDIDRate.Description
						FROM tmp_TempRateTableDIDRate_  as tblTempRateTableDIDRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableDIDRate.Code
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')

						UNION ALL

						SELECT DISTINCT
							tblTempRateTableDIDRate.OriginationCode AS Code,
							tblTempRateTableDIDRate.OriginationDescription AS Description
						FROM tmp_TempRateTableDIDRate_2  as tblTempRateTableDIDRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableDIDRate.OriginationCode
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
					) temp
					GROUP BY Code
				) c
			) as tbl GROUP BY a;

			IF errormessage IS NOT NULL
			THEN
				INSERT INTO tmp_JobLog_ (Message)
				SELECT DISTINCT
					CONCAT(tbl.Code , ' CODE DOES NOT EXIST IN CODE DECK')
				FROM
				(
					SELECT DISTINCT
						temp.Code,
						MAX(temp.Description) AS Description
					FROM
					(
						SELECT DISTINCT
							tblTempRateTableDIDRate.Code,
							tblTempRateTableDIDRate.Description
						FROM tmp_TempRateTableDIDRate_  as tblTempRateTableDIDRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableDIDRate.Code
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')

						UNION ALL

						SELECT DISTINCT
							tblTempRateTableDIDRate.OriginationCode AS Code,
							tblTempRateTableDIDRate.OriginationDescription AS Description
						FROM tmp_TempRateTableDIDRate_2  as tblTempRateTableDIDRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableDIDRate.OriginationCode
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
					) temp
					GROUP BY Code
				) as tbl;
			END IF;
		END IF;


		UPDATE tblRateTableDIDRate
		INNER JOIN tblRate
			ON tblRate.RateID = tblRateTableDIDRate.RateId
			AND tblRate.CompanyID = p_companyId
		LEFT JOIN tblRate AS OriginationRate
			ON OriginationRate.RateID = tblRateTableDIDRate.OriginationRateID
			AND OriginationRate.CompanyID = p_companyId
		INNER JOIN tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
			ON tblRate.Code = tblTempRateTableDIDRate.Code
			AND ((tblTempRateTableDIDRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableDIDRate.OriginationCode = OriginationRate.Code))
			AND tblTempRateTableDIDRate.TimezonesID = tblRateTableDIDRate.TimezonesID
			AND tblTempRateTableDIDRate.City = tblRateTableDIDRate.City
			AND tblTempRateTableDIDRate.Tariff = tblRateTableDIDRate.Tariff
			AND tblTempRateTableDIDRate.Change IN ('Delete', 'R', 'D', 'Blocked', 'Block')
		SET tblRateTableDIDRate.EndDate = IFNULL(tblTempRateTableDIDRate.EndDate,date(now()))
		WHERE tblRateTableDIDRate.RateTableId = p_RateTableId;


		DELETE tblTempRateTableDIDRate
		FROM tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableDIDRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
		LEFT JOIN tblRate AS OriginationRate
			ON OriginationRate.Code = tblTempRateTableDIDRate.OriginationCode
			AND OriginationRate.CompanyID = p_companyId
			AND OriginationRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
		JOIN tblRateTableDIDRate
			ON tblRateTableDIDRate.RateId = tblRate.RateId
			AND ((IFNULL(tblRateTableDIDRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableDIDRate.OriginationRateID = OriginationRate.RateID))
			AND tblRateTableDIDRate.RateTableId = p_RateTableId
			AND tblRateTableDIDRate.TimezonesID = tblTempRateTableDIDRate.TimezonesID
			AND tblRateTableDIDRate.City = tblTempRateTableDIDRate.City
			AND tblRateTableDIDRate.Tariff = tblTempRateTableDIDRate.Tariff
			AND tblRateTableDIDRate.AccessType = tblTempRateTableDIDRate.AccessType
			AND IFNULL(tblTempRateTableDIDRate.OneOffCost,0) = IFNULL(tblRateTableDIDRate.OneOffCost,0)
        	AND IFNULL(tblTempRateTableDIDRate.MonthlyCost,0) = IFNULL(tblRateTableDIDRate.MonthlyCost,0)
        	AND IFNULL(tblTempRateTableDIDRate.CostPerCall,0) = IFNULL(tblRateTableDIDRate.CostPerCall,0)
        	AND IFNULL(tblTempRateTableDIDRate.CostPerMinute,0) = IFNULL(tblRateTableDIDRate.CostPerMinute,0)
        	AND IFNULL(tblTempRateTableDIDRate.SurchargePerCall,0) = IFNULL(tblRateTableDIDRate.SurchargePerCall,0)
        	AND IFNULL(tblTempRateTableDIDRate.SurchargePerMinute,0) = IFNULL(tblRateTableDIDRate.SurchargePerMinute,0)
        	AND IFNULL(tblTempRateTableDIDRate.OutpaymentPerCall,0) = IFNULL(tblRateTableDIDRate.OutpaymentPerCall,0)
        	AND IFNULL(tblTempRateTableDIDRate.OutpaymentPerMinute,0) = IFNULL(tblRateTableDIDRate.OutpaymentPerMinute,0)
        	AND IFNULL(tblTempRateTableDIDRate.Surcharges,0) = IFNULL(tblRateTableDIDRate.Surcharges,0)
        	AND IFNULL(tblTempRateTableDIDRate.Chargeback,0) = IFNULL(tblRateTableDIDRate.Chargeback,0)
        	AND IFNULL(tblTempRateTableDIDRate.CollectionCostAmount,0) = IFNULL(tblRateTableDIDRate.CollectionCostAmount,0)
        	AND IFNULL(tblTempRateTableDIDRate.CollectionCostPercentage,0) = IFNULL(tblRateTableDIDRate.CollectionCostPercentage,0)
        	AND IFNULL(tblTempRateTableDIDRate.RegistrationCostPerNumber,0) = IFNULL(tblRateTableDIDRate.RegistrationCostPerNumber,0)
		WHERE
			tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block');


		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

		SELECT CurrencyID into v_RateTableCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyID FROM tblRateTable WHERE RateTableId=p_RateTableId);
		SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);


		UPDATE tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableDIDRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
		LEFT JOIN tblRate AS OriginationRate
			ON OriginationRate.Code = tblTempRateTableDIDRate.OriginationCode
			AND OriginationRate.CompanyID = p_companyId
			AND OriginationRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
		JOIN tblRateTableDIDRate
			ON tblRateTableDIDRate.RateId = tblRate.RateId
			AND tblRateTableDIDRate.RateTableId = p_RateTableId
			AND tblRateTableDIDRate.TimezonesID = tblTempRateTableDIDRate.TimezonesID
			AND tblRateTableDIDRate.City = tblTempRateTableDIDRate.City
			AND tblRateTableDIDRate.Tariff = tblTempRateTableDIDRate.Tariff
		SET tblRateTableDIDRate.EndDate = NOW()
		WHERE
			tblRateTableDIDRate.RateId = tblRate.RateId
			AND ((IFNULL(tblRateTableDIDRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableDIDRate.OriginationRateID = OriginationRate.RateID))
			AND (
				tblTempRateTableDIDRate.City <> tblRateTableDIDRate.City
				OR tblTempRateTableDIDRate.Tariff <> tblRateTableDIDRate.Tariff
				OR tblTempRateTableDIDRate.AccessType <> tblRateTableDIDRate.AccessType
				OR tblTempRateTableDIDRate.OneOffCost <> tblRateTableDIDRate.OneOffCost
				OR tblTempRateTableDIDRate.MonthlyCost <> tblRateTableDIDRate.MonthlyCost
				OR tblTempRateTableDIDRate.CostPerCall <> tblRateTableDIDRate.CostPerCall
				OR tblTempRateTableDIDRate.CostPerMinute <> tblRateTableDIDRate.CostPerMinute
				OR tblTempRateTableDIDRate.SurchargePerCall <> tblRateTableDIDRate.SurchargePerCall
				OR tblTempRateTableDIDRate.SurchargePerMinute <> tblRateTableDIDRate.SurchargePerMinute
				OR tblTempRateTableDIDRate.OutpaymentPerCall <> tblRateTableDIDRate.OutpaymentPerCall
				OR tblTempRateTableDIDRate.OutpaymentPerMinute <> tblRateTableDIDRate.OutpaymentPerMinute
				OR tblTempRateTableDIDRate.Surcharges <> tblRateTableDIDRate.Surcharges
				OR tblTempRateTableDIDRate.Chargeback <> tblRateTableDIDRate.Chargeback
				OR tblTempRateTableDIDRate.CollectionCostAmount <> tblRateTableDIDRate.CollectionCostAmount
				OR tblTempRateTableDIDRate.CollectionCostPercentage <> tblRateTableDIDRate.CollectionCostPercentage
				OR tblTempRateTableDIDRate.RegistrationCostPerNumber <> tblRateTableDIDRate.RegistrationCostPerNumber
			)
			AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
			AND DATE_FORMAT (tblRateTableDIDRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTableDIDRate.EffectiveDate, '%Y-%m-%d');


		call prc_ArchiveOldRateTableDIDRate(p_RateTableId, NULL,p_UserName);

		SET @stm1 = CONCAT('
			INSERT INTO tblRateTableDIDRate (
				RateTableId,
				TimezonesID,
				OriginationRateID,
				RateId,
				City,
				Tariff,
				AccessType,
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
				ApprovedStatus
			)
			SELECT DISTINCT
				',p_RateTableId,' AS RateTableId,
				tblTempRateTableDIDRate.TimezonesID,
				IFNULL(OriginationRate.RateID,0) AS OriginationRateID,
				tblRate.RateID,
				tblTempRateTableDIDRate.City,
				tblTempRateTableDIDRate.Tariff,
				tblTempRateTableDIDRate.AccessType,
		');

		SET @stm2 = '';
		IF p_CurrencyID > 0 AND p_CurrencyID != v_RateTableCurrencyID_
        THEN
			IF p_CurrencyID = v_CompanyCurrencyID_
            THEN
				SET @stm2 = CONCAT('
				    ( tblTempRateTableDIDRate.OneOffCost  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS OneOffCost,
				    ( tblTempRateTableDIDRate.MonthlyCost  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS MonthlyCost,
				    ( tblTempRateTableDIDRate.CostPerCall  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS CostPerCall,
				    ( tblTempRateTableDIDRate.CostPerMinute  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS CostPerMinute,
				    ( tblTempRateTableDIDRate.SurchargePerCall  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS SurchargePerCall,
				    ( tblTempRateTableDIDRate.SurchargePerMinute  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS SurchargePerMinute,
				    ( tblTempRateTableDIDRate.OutpaymentPerCall  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS OutpaymentPerCall,
				    ( tblTempRateTableDIDRate.OutpaymentPerMinute  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS OutpaymentPerMinute,
				    ( tblTempRateTableDIDRate.Surcharges  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS Surcharges,
				    ( tblTempRateTableDIDRate.Chargeback  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS Chargeback,
				    ( tblTempRateTableDIDRate.CollectionCostAmount  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS CollectionCostAmount,
				    ( tblTempRateTableDIDRate.CollectionCostPercentage  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS CollectionCostPercentage,
				    ( tblTempRateTableDIDRate.RegistrationCostPerNumber  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS RegistrationCostPerNumber,
				');
			ELSE
				SET @stm2 = CONCAT('
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.OneOffCost  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS OneOffCost,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.MonthlyCost  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS MonthlyCost,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.CostPerCall  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS CostPerCall,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.CostPerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS CostPerMinute,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.SurchargePerCall  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS SurchargePerCall,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.SurchargePerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS SurchargePerMinute,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.OutpaymentPerCall  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS OutpaymentPerCall,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.OutpaymentPerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS OutpaymentPerMinute,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.Surcharges  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS Surcharges,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.Chargeback  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS Chargeback,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.CollectionCostAmount  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS CollectionCostAmount,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.CollectionCostPercentage  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS CollectionCostPercentage,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.RegistrationCostPerNumber  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS RegistrationCostPerNumber,
				');
			END IF;
        ELSE
            SET @stm2 = CONCAT('
                    tblTempRateTableDIDRate.OneOffCost AS OneOffCost,
                    tblTempRateTableDIDRate.MonthlyCost AS MonthlyCost,
                    tblTempRateTableDIDRate.CostPerCall AS CostPerCall,
                    tblTempRateTableDIDRate.CostPerMinute AS CostPerMinute,
                    tblTempRateTableDIDRate.SurchargePerCall AS SurchargePerCall,
                    tblTempRateTableDIDRate.SurchargePerMinute AS SurchargePerMinute,
                    tblTempRateTableDIDRate.OutpaymentPerCall AS OutpaymentPerCall,
                    tblTempRateTableDIDRate.OutpaymentPerMinute AS OutpaymentPerMinute,
                    tblTempRateTableDIDRate.Surcharges AS Surcharges,
                    tblTempRateTableDIDRate.Chargeback AS Chargeback,
                    tblTempRateTableDIDRate.CollectionCostAmount AS CollectionCostAmount,
                    tblTempRateTableDIDRate.CollectionCostPercentage AS CollectionCostPercentage,
                    tblTempRateTableDIDRate.RegistrationCostPerNumber AS RegistrationCostPerNumber,
                ');
		END IF;

		SET @stm3 = CONCAT('
				tblTempRateTableDIDRate.OneOffCostCurrency,
				tblTempRateTableDIDRate.MonthlyCostCurrency,
				tblTempRateTableDIDRate.CostPerCallCurrency,
				tblTempRateTableDIDRate.CostPerMinuteCurrency,
				tblTempRateTableDIDRate.SurchargePerCallCurrency,
				tblTempRateTableDIDRate.SurchargePerMinuteCurrency,
				tblTempRateTableDIDRate.OutpaymentPerCallCurrency,
				tblTempRateTableDIDRate.OutpaymentPerMinuteCurrency,
				tblTempRateTableDIDRate.SurchargesCurrency,
				tblTempRateTableDIDRate.ChargebackCurrency,
				tblTempRateTableDIDRate.CollectionCostAmountCurrency,
				tblTempRateTableDIDRate.RegistrationCostPerNumberCurrency,
				tblTempRateTableDIDRate.EffectiveDate,
				tblTempRateTableDIDRate.EndDate,
				 -- if rate table is not vendor rate table and Rate Approval Process is on then rate will be upload as not approved
				IF(',v_RateTableAppliedTo_,' !=2,IF(',v_RateApprovalProcess_,'=1,0,1),1) AS ApprovedStatus
			FROM tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
			JOIN tblRate
				ON tblRate.Code = tblTempRateTableDIDRate.Code
				AND tblRate.CompanyID = ',p_companyId,'
				AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
			LEFT JOIN tblRate AS OriginationRate
				ON OriginationRate.Code = tblTempRateTableDIDRate.OriginationCode
				AND OriginationRate.CompanyID = ',p_companyId,'
				AND OriginationRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
			LEFT JOIN tblRateTableDIDRate
				ON tblRate.RateID = tblRateTableDIDRate.RateId
				AND ((IFNULL(tblRateTableDIDRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableDIDRate.OriginationRateID = OriginationRate.RateID))
				AND tblRateTableDIDRate.RateTableId = ',p_RateTableId,'
				AND tblRateTableDIDRate.TimezonesID = tblTempRateTableDIDRate.TimezonesID
				AND tblRateTableDIDRate.City = tblTempRateTableDIDRate.City
				AND tblRateTableDIDRate.Tariff = tblTempRateTableDIDRate.Tariff
				AND tblTempRateTableDIDRate.EffectiveDate = tblRateTableDIDRate.EffectiveDate
			WHERE tblRateTableDIDRate.RateTableDIDRateID IS NULL
				AND tblTempRateTableDIDRate.Change NOT IN ("Delete", "R", "D", "Blocked","Block")
				AND tblTempRateTableDIDRate.EffectiveDate >= DATE_FORMAT (NOW(), "%Y-%m-%d");
		');

		SET @stm4 = CONCAT(@stm1,@stm2,@stm3);

		PREPARE stm4 FROM @stm4;
		EXECUTE stm4;
		DEALLOCATE PREPARE stm4;

		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();


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
				RateTableId = p_RateTableId
			Group By EffectiveDate
			order by EffectiveDate desc
		) tmp
		,(SELECT @row_num := 0) x;


		SET v_pointer_ = 1;
		SET v_rowCount_ = ( SELECT COUNT(*) FROM tmp_EffectiveDates_ );

		IF v_rowCount_ > 0 THEN

			WHILE v_pointer_ <= v_rowCount_
			DO
				SET @EffectiveDate = ( SELECT EffectiveDate FROM tmp_EffectiveDates_ WHERE RowID = v_pointer_ );
				SET @row_num = 0;

				UPDATE  tblRateTableDIDRate vr1
				inner join
				(
					select
						RateTableId,
						OriginationRateID,
						RateID,
						EffectiveDate,
						TimezonesID,
						City,
						Tariff
					FROM tblRateTableDIDRate
					WHERE RateTableId = p_RateTableId
						AND EffectiveDate =   @EffectiveDate
					order by EffectiveDate desc
				) tmpvr
				on
					vr1.RateTableId = tmpvr.RateTableId
					AND vr1.OriginationRateID = tmpvr.OriginationRateID
					AND vr1.RateID = tmpvr.RateID
					AND vr1.TimezonesID = tmpvr.TimezonesID
					AND vr1.City = tmpvr.City
					AND vr1.Tariff = tmpvr.Tariff
					AND vr1.EffectiveDate < tmpvr.EffectiveDate
				SET
					vr1.EndDate = @EffectiveDate
				where
					vr1.RateTableId = p_RateTableId

					AND vr1.EndDate is null;


				SET v_pointer_ = v_pointer_ + 1;

			END WHILE;

		END IF;

	END IF;

	INSERT INTO tmp_JobLog_ (Message) 	 	SELECT CONCAT(v_AffectedRecords_ , ' Records Uploaded ' );

	call prc_ArchiveOldRateTableDIDRate(p_RateTableId, NULL,p_UserName);

	DELETE  FROM tblTempRateTableDIDRate WHERE  ProcessId = p_processId;
	DELETE  FROM tblRateTableDIDRateChangeLog WHERE ProcessID = p_processId;
	SELECT * FROM tmp_JobLog_;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_WSProcessRateTableDIDRateAA`;
DELIMITER //
CREATE PROCEDURE `prc_WSProcessRateTableDIDRateAA`(
	IN `p_RateTableId` INT,
	IN `p_replaceAllRates` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_processId` VARCHAR(200),
	IN `p_addNewCodesToCodeDeck` INT,
	IN `p_companyId` INT,
	IN `p_dialstringid` INT,
	IN `p_dialcodeSeparator` VARCHAR(50),
	IN `p_seperatecolumn` INT,
	IN `p_CurrencyID` INT,
	IN `p_list_option` INT,
	IN `p_UserName` TEXT
)
ThisSP:BEGIN

	DECLARE v_AffectedRecords_ INT DEFAULT 0;
	DECLARE v_CodeDeckId_ INT ;
	DECLARE totaldialstringcode INT(11) DEFAULT 0;
	DECLARE newstringcode INT(11) DEFAULT 0;
	DECLARE totalduplicatecode INT(11);
	DECLARE errormessage longtext;
	DECLARE errorheader longtext;
	DECLARE v_RateTableCurrencyID_ INT;
	DECLARE v_CompanyCurrencyID_ INT;
	DECLARE v_pointer_ INT;
	DECLARE v_rowCount_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
	CREATE TEMPORARY TABLE tmp_JobLog_ (
		Message longtext
	);

    DROP TEMPORARY TABLE IF EXISTS tmp_TempTimezones_;
    CREATE TEMPORARY TABLE tmp_TempTimezones_ (
        TimezonesID INT
    );

	DROP TEMPORARY TABLE IF EXISTS tmp_split_RateTableDIDRate_;
	CREATE TEMPORARY TABLE tmp_split_RateTableDIDRate_ (
		`TempRateTableDIDRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NULL DEFAULT NULL,
		`OneOffCost` decimal(18,6) DEFAULT NULL,
	  	`MonthlyCost` decimal(18,6) DEFAULT NULL,
	  	`CostPerCall` decimal(18,6) DEFAULT NULL,
	  	`CostPerMinute` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerCall` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerMinute` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerCall` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerMinute` decimal(18,6) DEFAULT NULL,
	  	`Surcharges` decimal(18,6) DEFAULT NULL,
	  	`Chargeback` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostAmount` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostPercentage` decimal(18,6) DEFAULT NULL,
	  	`RegistrationCostPerNumber` decimal(18,6) DEFAULT NULL,
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`DialStringPrefix` varchar(500) ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_OriginationCode (`OriginationCode`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableDIDRate_;
	CREATE TEMPORARY TABLE tmp_TempRateTableDIDRate_ (
		TempRateTableDIDRateID INT,
		RateTableDIDRateID INT DEFAULT 0,
		`CodeDeckId` INT ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NULL DEFAULT NULL,
		`OneOffCost` decimal(18,6) DEFAULT NULL,
	  	`MonthlyCost` decimal(18,6) DEFAULT NULL,
	  	`CostPerCall` decimal(18,6) DEFAULT NULL,
	  	`CostPerMinute` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerCall` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerMinute` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerCall` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerMinute` decimal(18,6) DEFAULT NULL,
	  	`Surcharges` decimal(18,6) DEFAULT NULL,
	  	`Chargeback` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostAmount` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostPercentage` decimal(18,6) DEFAULT NULL,
	  	`RegistrationCostPerNumber` decimal(18,6) DEFAULT NULL,
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`DialStringPrefix` varchar(500) ,
		`ApprovedStatus` TINYINT(4) DEFAULT 0,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_OriginationCode (`OriginationCode`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_Delete_RateTableDIDRate;
	CREATE TEMPORARY TABLE tmp_Delete_RateTableDIDRate (
		TempRateTableDIDRateID INT DEFAULT 0,
		RateTableDIDRateID INT,
		`CodeDeckId` INT ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NULL DEFAULT NULL,
		`OneOffCost` decimal(18,6) DEFAULT NULL,
	  	`MonthlyCost` decimal(18,6) DEFAULT NULL,
	  	`CostPerCall` decimal(18,6) DEFAULT NULL,
	  	`CostPerMinute` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerCall` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerMinute` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerCall` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerMinute` decimal(18,6) DEFAULT NULL,
	  	`Surcharges` decimal(18,6) DEFAULT NULL,
	  	`Chargeback` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostAmount` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostPercentage` decimal(18,6) DEFAULT NULL,
	  	`RegistrationCostPerNumber` decimal(18,6) DEFAULT NULL,
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`DialStringPrefix` varchar(500) ,
		`ApprovedStatus` TINYINT(4) DEFAULT 0,
		INDEX tmp_RateTableDIDRateDiscontinued_RateTableDIDRateID (`RateTableDIDRateID`)
	);

	CALL  prc_RateTableDIDRateCheckDialstringAndDupliacteCode(p_companyId,p_processId,p_dialstringid,p_effectiveImmediately,p_dialcodeSeparator,p_seperatecolumn);

	SELECT COUNT(*) AS COUNT INTO newstringcode from tmp_JobLog_;

 	INSERT INTO tmp_TempTimezones_
 	SELECT DISTINCT TimezonesID from tmp_TempRateTableDIDRate_;

	IF newstringcode = 0
	THEN


		IF p_list_option = 1
		THEN

			INSERT INTO tmp_Delete_RateTableDIDRate(
				RateTableDIDRateID,
				CodeDeckId,
				TimezonesID,
				OriginationCode,
				OriginationDescription,
				Code,
				Description,
				City,
				Tariff,
				AccessType,
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
				`Change`,
				ProcessId,
				DialStringPrefix,
				ApprovedStatus
			)
			SELECT DISTINCT
				tblRateTableDIDRate.RateTableDIDRateID,
				tblRateTable.CodeDeckId AS CodeDeckId,
				tblRateTableDIDRate.TimezonesID,
				OriginationRate.Code AS OriginationCode,
				OriginationRate.Description AS OriginationDescription,
				tblRate.Code,
				tblRate.Description,
				tblRateTableDIDRate.City,
				tblRateTableDIDRate.Tariff,
				tblRateTableDIDRate.AccessType,
				tblRateTableDIDRate.OneOffCost,
				tblRateTableDIDRate.MonthlyCost,
				tblRateTableDIDRate.CostPerCall,
				tblRateTableDIDRate.CostPerMinute,
				tblRateTableDIDRate.SurchargePerCall,
				tblRateTableDIDRate.SurchargePerMinute,
				tblRateTableDIDRate.OutpaymentPerCall,
				tblRateTableDIDRate.OutpaymentPerMinute,
				tblRateTableDIDRate.Surcharges,
				tblRateTableDIDRate.Chargeback,
				tblRateTableDIDRate.CollectionCostAmount,
				tblRateTableDIDRate.CollectionCostPercentage,
				tblRateTableDIDRate.RegistrationCostPerNumber,
				tblRateTableDIDRate.OneOffCostCurrency,
				tblRateTableDIDRate.MonthlyCostCurrency,
				tblRateTableDIDRate.CostPerCallCurrency,
				tblRateTableDIDRate.CostPerMinuteCurrency,
				tblRateTableDIDRate.SurchargePerCallCurrency,
				tblRateTableDIDRate.SurchargePerMinuteCurrency,
				tblRateTableDIDRate.OutpaymentPerCallCurrency,
				tblRateTableDIDRate.OutpaymentPerMinuteCurrency,
				tblRateTableDIDRate.SurchargesCurrency,
				tblRateTableDIDRate.ChargebackCurrency,
				tblRateTableDIDRate.CollectionCostAmountCurrency,
				tblRateTableDIDRate.RegistrationCostPerNumberCurrency,
				tblRateTableDIDRate.EffectiveDate,
				NULL AS EndDate,
				'Delete' AS `Change`,
				p_processId AS ProcessId,
				'' AS DialStringPrefix,
				3 AS ApprovedStatus
			FROM tblRateTableDIDRate
			JOIN tblRateTable
				ON tblRateTable.RateTableId = tblRateTableDIDRate.RateTableId
			JOIN tblRate
				ON tblRate.RateID = tblRateTableDIDRate.RateId
				AND tblRate.CompanyID = p_companyId
			LEFT JOIN tblRate AS OriginationRate
				ON OriginationRate.RateID = tblRateTableDIDRate.OriginationRateID
				AND OriginationRate.CompanyID = p_companyId
		  	/*JOIN tmp_TempTimezones_
		  		ON tmp_TempTimezones_.TimezonesID = tblRateTableDIDRate.TimezonesID*/
			LEFT JOIN tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
				ON tblTempRateTableDIDRate.Code = tblRate.Code
				AND ((tblTempRateTableDIDRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableDIDRate.OriginationCode = OriginationRate.Code))
				AND tblTempRateTableDIDRate.TimezonesID = tblRateTableDIDRate.TimezonesID
				AND tblTempRateTableDIDRate.City = tblRateTableDIDRate.City
				AND tblTempRateTableDIDRate.Tariff = tblRateTableDIDRate.Tariff
				AND  tblTempRateTableDIDRate.ProcessId = p_processId
				AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
			WHERE tblRateTableDIDRate.RateTableId = p_RateTableId
				AND tblTempRateTableDIDRate.Code IS NULL
				AND ( tblRateTableDIDRate.EndDate is NULL OR tblRateTableDIDRate.EndDate <= date(now()) )
			ORDER BY RateTableDIDRateID ASC;


		END IF;




		DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableDIDRate_2;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempRateTableDIDRate_2 AS (SELECT * FROM tmp_TempRateTableDIDRate_);

		IF  p_addNewCodesToCodeDeck = 1
		THEN
			-- Destination Code
			INSERT INTO tblRate (
				CompanyID,
				Code,
				Description,
				CreatedBy,
				CountryID,
				CodeDeckId,
				Interval1,
				IntervalN
			)
			SELECT DISTINCT
				p_companyId,
				vc.Code,
				vc.Description,
				'RMService',
				fnGetCountryIdByCodeAndCountry (vc.Code ,vc.Description) AS CountryID,
				CodeDeckId,
				Interval1,
				IntervalN
			FROM
			(
				SELECT DISTINCT
					tblTempRateTableDIDRate.Code,
					MAX(tblTempRateTableDIDRate.Description) AS Description,
					MAX(tblTempRateTableDIDRate.CodeDeckId) AS CodeDeckId,
					1 AS Interval1,
					1 AS IntervalN
				FROM tmp_TempRateTableDIDRate_  as tblTempRateTableDIDRate
				LEFT JOIN tblRate
					ON tblRate.Code = tblTempRateTableDIDRate.Code
					AND tblRate.CompanyID = p_companyId
					AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
				WHERE tblRate.RateID IS NULL
					AND tblTempRateTableDIDRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
				GROUP BY
					tblTempRateTableDIDRate.Code
			) vc;

			-- Origination Code
			INSERT INTO tblRate (
				CompanyID,
				Code,
				Description,
				CreatedBy,
				CountryID,
				CodeDeckId,
				Interval1,
				IntervalN
			)
			SELECT DISTINCT
				p_companyId,
				vc.Code,
				vc.Description,
				'RMService',
				fnGetCountryIdByCodeAndCountry (vc.Code ,vc.Description) AS CountryID,
				CodeDeckId,
				Interval1,
				IntervalN
			FROM
			(
				SELECT DISTINCT
					tblTempRateTableDIDRate.OriginationCode AS Code,
					MAX(tblTempRateTableDIDRate.OriginationDescription) AS Description,
					MAX(tblTempRateTableDIDRate.CodeDeckId) AS CodeDeckId,
					1 AS Interval1,
					1 AS IntervalN
				FROM tmp_TempRateTableDIDRate_  as tblTempRateTableDIDRate
				LEFT JOIN tblRate
					ON tblRate.Code = tblTempRateTableDIDRate.OriginationCode
					AND tblRate.CompanyID = p_companyId
					AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
				WHERE tblRate.RateID IS NULL
					AND tblTempRateTableDIDRate.OriginationCode IS NOT NULL AND tblTempRateTableDIDRate.OriginationCode != ''
					AND tblTempRateTableDIDRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
				GROUP BY
					tblTempRateTableDIDRate.OriginationCode
			) vc;

		ELSE
			SELECT GROUP_CONCAT(code) into errormessage FROM(
				SELECT DISTINCT
					c.Code as code, 1 as a
				FROM
				(
					SELECT DISTINCT
						temp.Code,
						MAX(temp.Description) AS Description
					FROM
					(
						SELECT DISTINCT
							tblTempRateTableDIDRate.Code,
							tblTempRateTableDIDRate.Description
						FROM tmp_TempRateTableDIDRate_  as tblTempRateTableDIDRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableDIDRate.Code
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')

						UNION ALL

						SELECT DISTINCT
							tblTempRateTableDIDRate.OriginationCode AS Code,
							tblTempRateTableDIDRate.OriginationDescription AS Description
						FROM tmp_TempRateTableDIDRate_2  as tblTempRateTableDIDRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableDIDRate.OriginationCode
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
					) temp
					GROUP BY Code
				) c
			) as tbl GROUP BY a;

			IF errormessage IS NOT NULL
			THEN
				INSERT INTO tmp_JobLog_ (Message)
				SELECT DISTINCT
					CONCAT(tbl.Code , ' CODE DOES NOT EXIST IN CODE DECK')
				FROM
				(
					SELECT DISTINCT
						temp.Code,
						MAX(temp.Description) AS Description
					FROM
					(
						SELECT DISTINCT
							tblTempRateTableDIDRate.Code,
							tblTempRateTableDIDRate.Description
						FROM tmp_TempRateTableDIDRate_  as tblTempRateTableDIDRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableDIDRate.Code
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')

						UNION ALL

						SELECT DISTINCT
							tblTempRateTableDIDRate.OriginationCode AS Code,
							tblTempRateTableDIDRate.OriginationDescription AS Description
						FROM tmp_TempRateTableDIDRate_2  as tblTempRateTableDIDRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableDIDRate.OriginationCode
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
					) temp
					GROUP BY Code
				) as tbl;
			END IF;
		END IF;


		DELETE tblTempRateTableDIDRate
		FROM tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableDIDRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
		LEFT JOIN tblRate AS OriginationRate
			ON OriginationRate.Code = tblTempRateTableDIDRate.OriginationCode
			AND OriginationRate.CompanyID = p_companyId
			AND OriginationRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
		JOIN tblRateTableDIDRateAA AS tblRateTableDIDRate
			ON tblRateTableDIDRate.RateId = tblRate.RateId
			AND ((IFNULL(tblRateTableDIDRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableDIDRate.OriginationRateID = OriginationRate.RateID))
			AND tblRateTableDIDRate.RateTableId = p_RateTableId
			AND tblRateTableDIDRate.TimezonesID = tblTempRateTableDIDRate.TimezonesID
			AND tblRateTableDIDRate.City = tblTempRateTableDIDRate.City
			AND tblRateTableDIDRate.Tariff = tblTempRateTableDIDRate.Tariff
			AND tblRateTableDIDRate.AccessType = tblTempRateTableDIDRate.AccessType
			AND IFNULL(tblTempRateTableDIDRate.OneOffCost,0) = IFNULL(tblRateTableDIDRate.OneOffCost,0)
        	AND IFNULL(tblTempRateTableDIDRate.MonthlyCost,0) = IFNULL(tblRateTableDIDRate.MonthlyCost,0)
        	AND IFNULL(tblTempRateTableDIDRate.CostPerCall,0) = IFNULL(tblRateTableDIDRate.CostPerCall,0)
        	AND IFNULL(tblTempRateTableDIDRate.CostPerMinute,0) = IFNULL(tblRateTableDIDRate.CostPerMinute,0)
        	AND IFNULL(tblTempRateTableDIDRate.SurchargePerCall,0) = IFNULL(tblRateTableDIDRate.SurchargePerCall,0)
        	AND IFNULL(tblTempRateTableDIDRate.SurchargePerMinute,0) = IFNULL(tblRateTableDIDRate.SurchargePerMinute,0)
        	AND IFNULL(tblTempRateTableDIDRate.OutpaymentPerCall,0) = IFNULL(tblRateTableDIDRate.OutpaymentPerCall,0)
        	AND IFNULL(tblTempRateTableDIDRate.OutpaymentPerMinute,0) = IFNULL(tblRateTableDIDRate.OutpaymentPerMinute,0)
        	AND IFNULL(tblTempRateTableDIDRate.Surcharges,0) = IFNULL(tblRateTableDIDRate.Surcharges,0)
        	AND IFNULL(tblTempRateTableDIDRate.Chargeback,0) = IFNULL(tblRateTableDIDRate.Chargeback,0)
        	AND IFNULL(tblTempRateTableDIDRate.CollectionCostAmount,0) = IFNULL(tblRateTableDIDRate.CollectionCostAmount,0)
        	AND IFNULL(tblTempRateTableDIDRate.CollectionCostPercentage,0) = IFNULL(tblRateTableDIDRate.CollectionCostPercentage,0)
        	AND IFNULL(tblTempRateTableDIDRate.RegistrationCostPerNumber,0) = IFNULL(tblRateTableDIDRate.RegistrationCostPerNumber,0)
		WHERE
			tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block');


		-- SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

		SELECT CurrencyID into v_RateTableCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyID FROM tblRateTable WHERE RateTableId=p_RateTableId);
		SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);


		UPDATE tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableDIDRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
		LEFT JOIN tblRate AS OriginationRate
			ON OriginationRate.Code = tblTempRateTableDIDRate.OriginationCode
			AND OriginationRate.CompanyID = p_companyId
			AND OriginationRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
		JOIN tblRateTableDIDRateAA AS tblRateTableDIDRate
			ON tblRateTableDIDRate.RateId = tblRate.RateId
			AND tblRateTableDIDRate.RateTableId = p_RateTableId
			AND tblRateTableDIDRate.TimezonesID = tblTempRateTableDIDRate.TimezonesID
			AND tblRateTableDIDRate.City = tblTempRateTableDIDRate.City
			AND tblRateTableDIDRate.Tariff = tblTempRateTableDIDRate.Tariff
		SET tblRateTableDIDRate.EndDate = NOW()
		WHERE
			tblRateTableDIDRate.RateId = tblRate.RateId
			AND ((IFNULL(tblRateTableDIDRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableDIDRate.OriginationRateID = OriginationRate.RateID))
			AND (
				tblTempRateTableDIDRate.City <> tblRateTableDIDRate.City
				OR tblTempRateTableDIDRate.Tariff <> tblRateTableDIDRate.Tariff
				OR tblTempRateTableDIDRate.AccessType <> tblRateTableDIDRate.AccessType
				OR tblTempRateTableDIDRate.OneOffCost <> tblRateTableDIDRate.OneOffCost
				OR tblTempRateTableDIDRate.MonthlyCost <> tblRateTableDIDRate.MonthlyCost
				OR tblTempRateTableDIDRate.CostPerCall <> tblRateTableDIDRate.CostPerCall
				OR tblTempRateTableDIDRate.CostPerMinute <> tblRateTableDIDRate.CostPerMinute
				OR tblTempRateTableDIDRate.SurchargePerCall <> tblRateTableDIDRate.SurchargePerCall
				OR tblTempRateTableDIDRate.SurchargePerMinute <> tblRateTableDIDRate.SurchargePerMinute
				OR tblTempRateTableDIDRate.OutpaymentPerCall <> tblRateTableDIDRate.OutpaymentPerCall
				OR tblTempRateTableDIDRate.OutpaymentPerMinute <> tblRateTableDIDRate.OutpaymentPerMinute
				OR tblTempRateTableDIDRate.Surcharges <> tblRateTableDIDRate.Surcharges
				OR tblTempRateTableDIDRate.Chargeback <> tblRateTableDIDRate.Chargeback
				OR tblTempRateTableDIDRate.CollectionCostAmount <> tblRateTableDIDRate.CollectionCostAmount
				OR tblTempRateTableDIDRate.CollectionCostPercentage <> tblRateTableDIDRate.CollectionCostPercentage
				OR tblTempRateTableDIDRate.RegistrationCostPerNumber <> tblRateTableDIDRate.RegistrationCostPerNumber
			)
			AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
			AND DATE_FORMAT (tblRateTableDIDRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTableDIDRate.EffectiveDate, '%Y-%m-%d');


		call prc_ArchiveOldRateTableDIDRateAA(p_RateTableId, NULL,p_UserName);

		SET @stm1 = CONCAT('
			INSERT INTO tblRateTableDIDRateAA (
				RateTableId,
				TimezonesID,
				OriginationRateID,
				RateId,
				City,
				Tariff,
				AccessType,
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
				RateTableDIDRateID
			)
			SELECT DISTINCT
				',p_RateTableId,' AS RateTableId,
				tblTempRateTableDIDRate.TimezonesID,
				IFNULL(OriginationRate.RateID,0) AS OriginationRateID,
				tblRate.RateID,
				tblTempRateTableDIDRate.City,
				tblTempRateTableDIDRate.Tariff,
				tblTempRateTableDIDRate.AccessType,
		');

		SET @stm2 = '';
		IF p_CurrencyID > 0 AND p_CurrencyID != v_RateTableCurrencyID_
        THEN
			IF p_CurrencyID = v_CompanyCurrencyID_
            THEN
				SET @stm2 = CONCAT('
				    ( tblTempRateTableDIDRate.OneOffCost  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS OneOffCost,
				    ( tblTempRateTableDIDRate.MonthlyCost  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS MonthlyCost,
				    ( tblTempRateTableDIDRate.CostPerCall  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS CostPerCall,
				    ( tblTempRateTableDIDRate.CostPerMinute  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS CostPerMinute,
				    ( tblTempRateTableDIDRate.SurchargePerCall  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS SurchargePerCall,
				    ( tblTempRateTableDIDRate.SurchargePerMinute  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS SurchargePerMinute,
				    ( tblTempRateTableDIDRate.OutpaymentPerCall  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS OutpaymentPerCall,
				    ( tblTempRateTableDIDRate.OutpaymentPerMinute  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS OutpaymentPerMinute,
				    ( tblTempRateTableDIDRate.Surcharges  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS Surcharges,
				    ( tblTempRateTableDIDRate.Chargeback  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS Chargeback,
				    ( tblTempRateTableDIDRate.CollectionCostAmount  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS CollectionCostAmount,
				    ( tblTempRateTableDIDRate.CollectionCostPercentage  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS CollectionCostPercentage,
				    ( tblTempRateTableDIDRate.RegistrationCostPerNumber  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS RegistrationCostPerNumber,
				');
			ELSE
				SET @stm2 = CONCAT('
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.OneOffCost  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS OneOffCost,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.MonthlyCost  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS MonthlyCost,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.CostPerCall  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS CostPerCall,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.CostPerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS CostPerMinute,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.SurchargePerCall  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS SurchargePerCall,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.SurchargePerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS SurchargePerMinute,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.OutpaymentPerCall  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS OutpaymentPerCall,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.OutpaymentPerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS OutpaymentPerMinute,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.Surcharges  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS Surcharges,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.Chargeback  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS Chargeback,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.CollectionCostAmount  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS CollectionCostAmount,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.CollectionCostPercentage  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS CollectionCostPercentage,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.RegistrationCostPerNumber  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS RegistrationCostPerNumber,
				');
			END IF;
        ELSE
            SET @stm2 = CONCAT('
                    tblTempRateTableDIDRate.OneOffCost AS OneOffCost,
                    tblTempRateTableDIDRate.MonthlyCost AS MonthlyCost,
                    tblTempRateTableDIDRate.CostPerCall AS CostPerCall,
                    tblTempRateTableDIDRate.CostPerMinute AS CostPerMinute,
                    tblTempRateTableDIDRate.SurchargePerCall AS SurchargePerCall,
                    tblTempRateTableDIDRate.SurchargePerMinute AS SurchargePerMinute,
                    tblTempRateTableDIDRate.OutpaymentPerCall AS OutpaymentPerCall,
                    tblTempRateTableDIDRate.OutpaymentPerMinute AS OutpaymentPerMinute,
                    tblTempRateTableDIDRate.Surcharges AS Surcharges,
                    tblTempRateTableDIDRate.Chargeback AS Chargeback,
                    tblTempRateTableDIDRate.CollectionCostAmount AS CollectionCostAmount,
                    tblTempRateTableDIDRate.CollectionCostPercentage AS CollectionCostPercentage,
                    tblTempRateTableDIDRate.RegistrationCostPerNumber AS RegistrationCostPerNumber,
                ');
		END IF;

		SET @stm3 = CONCAT('
				tblTempRateTableDIDRate.OneOffCostCurrency,
				tblTempRateTableDIDRate.MonthlyCostCurrency,
				tblTempRateTableDIDRate.CostPerCallCurrency,
				tblTempRateTableDIDRate.CostPerMinuteCurrency,
				tblTempRateTableDIDRate.SurchargePerCallCurrency,
				tblTempRateTableDIDRate.SurchargePerMinuteCurrency,
				tblTempRateTableDIDRate.OutpaymentPerCallCurrency,
				tblTempRateTableDIDRate.OutpaymentPerMinuteCurrency,
				tblTempRateTableDIDRate.SurchargesCurrency,
				tblTempRateTableDIDRate.ChargebackCurrency,
				tblTempRateTableDIDRate.CollectionCostAmountCurrency,
				tblTempRateTableDIDRate.RegistrationCostPerNumberCurrency,
				tblTempRateTableDIDRate.EffectiveDate,
				tblTempRateTableDIDRate.EndDate,
				tblTempRateTableDIDRate.ApprovedStatus,
				tblTempRateTableDIDRate.RateTableDIDRateID
			FROM
			(
				SELECT * FROM tmp_TempRateTableDIDRate_
				WHERE tmp_TempRateTableDIDRate_.Change NOT IN ("Delete", "R", "D", "Blocked","Block")
				AND tmp_TempRateTableDIDRate_.EffectiveDate >= DATE_FORMAT (NOW(), "%Y-%m-%d")

				UNION

				SELECT * FROM tmp_Delete_RateTableDIDRate

			) as tblTempRateTableDIDRate
			JOIN tblRate
				ON tblRate.Code = tblTempRateTableDIDRate.Code
				AND tblRate.CompanyID = ',p_companyId,'
				AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
			LEFT JOIN tblRate AS OriginationRate
				ON OriginationRate.Code = tblTempRateTableDIDRate.OriginationCode
				AND OriginationRate.CompanyID = ',p_companyId,'
				AND OriginationRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId;
		');

		SET @stm4 = CONCAT(@stm1,@stm2,@stm3);

		PREPARE stm4 FROM @stm4;
		EXECUTE stm4;
		DEALLOCATE PREPARE stm4;

		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

	END IF;

	INSERT INTO tmp_JobLog_ (Message) 	 	SELECT CONCAT(v_AffectedRecords_ , ' Records Uploaded ' );

	call prc_ArchiveOldRateTableDIDRateAA(p_RateTableId, NULL,p_UserName);

	DELETE  FROM tblTempRateTableDIDRate WHERE  ProcessId = p_processId;
	DELETE  FROM tblRateTableDIDRateChangeLog WHERE ProcessID = p_processId;
	SELECT * FROM tmp_JobLog_;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_WSGenerateRateTable`;
DELIMITER //
CREATE PROCEDURE `prc_WSGenerateRateTable`(
	IN `p_jobId` INT,
	IN `p_RateGeneratorId` INT,
	IN `p_RateTableId` INT,
	IN `p_TimezonesID` VARCHAR(50),
	IN `p_rateTableName` VARCHAR(200),
	IN `p_EffectiveDate` VARCHAR(10),
	IN `p_delete_exiting_rate` INT,
	IN `p_EffectiveRate` VARCHAR(50),
	IN `p_GroupBy` VARCHAR(50),
	IN `p_ModifiedBy` VARCHAR(50),
	IN `p_IsMerge` INT,
	IN `p_TakePrice` INT,
	IN `p_MergeInto` INT
)
GenerateRateTable:BEGIN


		DECLARE i INTEGER;
		DECLARE v_RTRowCount_ INT;
		DECLARE v_RatePosition_ INT;
		DECLARE v_Use_Preference_ INT;
		DECLARE v_CurrencyID_ INT;
		DECLARE v_CompanyCurrencyID_ INT;
		DECLARE v_Average_ TINYINT;
		DECLARE v_CompanyId_ INT;
		DECLARE v_codedeckid_ INT;
		DECLARE v_trunk_ INT;
		DECLARE v_rateRuleId_ INT;
		DECLARE v_RateGeneratorName_ VARCHAR(200);
		DECLARE v_pointer_ INT ;
		DECLARE v_rowCount_ INT ;
		DECLARE v_percentageRate INT ;
		DECLARE v_AppliedTo INT ;
		DECLARE v_Reseller INT ;

		DECLARE v_LessThenRate  DECIMAL(18, 6);
		DECLARE v_ChargeRate  DECIMAL(18, 6);

		DECLARE v_IncreaseEffectiveDate_ DATETIME ;
		DECLARE v_DecreaseEffectiveDate_ DATETIME ;





		DECLARE v_tmp_code_cnt int ;
		DECLARE v_tmp_code_pointer int;
		DECLARE v_p_code varchar(50);
		DECLARE v_Codlen_ int;
		DECLARE v_p_code__ VARCHAR(50);
		DECLARE v_Commit int;
		DECLARE v_TimezonesID int;

		DECLARE EXIT HANDLER FOR SQLEXCEPTION
		BEGIN
			SHOW WARNINGS;
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



		SET p_EffectiveDate = CAST(p_EffectiveDate AS DATE);
		SET v_TimezonesID = IF(p_IsMerge=1,p_MergeInto,p_TimezonesID);

		IF p_rateTableName IS NOT NULL
		THEN


			SET v_RTRowCount_ = (SELECT
														 COUNT(*)
													 FROM tblRateTable
													 WHERE RateTableName = p_rateTableName
																 AND CompanyId = (SELECT
																										CompanyId
																									FROM tblRateGenerator
																									WHERE RateGeneratorID = p_RateGeneratorId));

			IF v_RTRowCount_ > 0
			THEN
				INSERT INTO tmp_JobLog_ (Message) VALUES ('RateTable Name is already exist, Please try using another RateTable Name');
				select * from tmp_JobLog_;
				LEAVE GenerateRateTable;
			END IF;
		END IF;

		DROP TEMPORARY TABLE IF EXISTS tmp_Rates_;
		CREATE TEMPORARY TABLE tmp_Rates_  (
			Originationcode VARCHAR(50) COLLATE utf8_unicode_ci,
			Originationdescription VARCHAR(200) COLLATE utf8_unicode_ci,
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			description VARCHAR(200) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			rateN DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			PreviousRate DECIMAL(18, 6),
			EffectiveDate DATE DEFAULT NULL,
			AccountID int,
			RateCurrency int,
			ConnectionFeeCurrency int,
			INDEX tmp_Rates_code (`code`),
			INDEX  tmp_Rates_description (`description`),
			UNIQUE KEY `unique_code` (`code`)

		);


		DROP TEMPORARY TABLE IF EXISTS tmp_Rates2_;
		CREATE TEMPORARY TABLE tmp_Rates2_  (
			OriginationCode VARCHAR(50) COLLATE utf8_unicode_ci,
			Originationdescription VARCHAR(200) COLLATE utf8_unicode_ci,
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			description VARCHAR(200) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			rateN DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			PreviousRate DECIMAL(18, 6),
			EffectiveDate DATE DEFAULT NULL,
			AccountID int,
			RateCurrency int,
			ConnectionFeeCurrency int,
			INDEX tmp_Rates2_code (`code`),
			INDEX  tmp_Rates_description (`description`)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_Rates3_;
		CREATE TEMPORARY TABLE tmp_Rates3_  (
			OriginationCode VARCHAR(50) COLLATE utf8_unicode_ci,
			Originationdescription VARCHAR(200) COLLATE utf8_unicode_ci,
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			description VARCHAR(200) COLLATE utf8_unicode_ci,
			UNIQUE KEY `unique_code` (`code`),
			INDEX  tmp_Rates_description (`description`)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_Codedecks_;
		CREATE TEMPORARY TABLE tmp_Codedecks_ (
			CodeDeckId INT
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Raterules_;

		CREATE TEMPORARY TABLE tmp_Raterules_  (
			rateruleid INT,
			Originationcode VARCHAR(50) COLLATE utf8_unicode_ci,
			Originationdescription VARCHAR(200) COLLATE utf8_unicode_ci,
			OriginationType VARCHAR(200) COLLATE utf8_unicode_ci,
			OriginationCountryID INT,
			DestinationType VARCHAR(200) COLLATE utf8_unicode_ci,
			DestinationCountryID INT,
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			description VARCHAR(200) COLLATE utf8_unicode_ci,
			RowNo INT,
			`Order` INT,
			INDEX tmp_Raterules_code (`code`),
			INDEX tmp_Raterules_rateruleid (`rateruleid`),
			INDEX tmp_Raterules_RowNo (`RowNo`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Raterules_dup;

		CREATE TEMPORARY TABLE tmp_Raterules_dup  (
			rateruleid INT,
			Originationcode VARCHAR(50) COLLATE utf8_unicode_ci,
			Originationdescription VARCHAR(200) COLLATE utf8_unicode_ci,
			OriginationType VARCHAR(200) COLLATE utf8_unicode_ci,
			OriginationCountryID INT,
			DestinationType VARCHAR(200) COLLATE utf8_unicode_ci,
			DestinationCountryID INT,
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			description VARCHAR(200) COLLATE utf8_unicode_ci,
			RowNo INT,
			`Order` INT,
			INDEX tmp_Raterules_code (`code`),
			INDEX tmp_Raterules_rateruleid (`rateruleid`),
			INDEX tmp_Raterules_RowNo (`RowNo`)
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_Raterules_dup2;
		CREATE TEMPORARY TABLE tmp_Raterules_dup2  (
			rateruleid INT,
			Originationcode VARCHAR(50) COLLATE utf8_unicode_ci,
			Originationdescription VARCHAR(200) COLLATE utf8_unicode_ci,
			OriginationType VARCHAR(200) COLLATE utf8_unicode_ci,
			OriginationCountryID INT,
			DestinationType VARCHAR(200) COLLATE utf8_unicode_ci,
			DestinationCountryID INT,
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			description VARCHAR(200) COLLATE utf8_unicode_ci,
			RowNo INT,
			`Order` INT,
			INDEX tmp_Raterules_code (`code`),
			INDEX tmp_Raterules_rateruleid (`rateruleid`),
			INDEX tmp_Raterules_RowNo (`RowNo`)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_Vendorrates_;
		CREATE TEMPORARY TABLE tmp_Vendorrates_  (
			OriginationCode varchar(50),
			OriginationDescription varchar(200),
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			rateN DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			AccountId INT,
			RowNo INT,
			PreferenceRank INT,
			RateCurrency int,
			ConnectionFeeCurrency int,

			INDEX tmp_Vendorrates_code (`code`),
			INDEX tmp_Vendorrates_rate (`rate`)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_VRatesstage2_;
		CREATE TEMPORARY TABLE tmp_VRatesstage2_  (
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci,
			OriginationCode varchar(50),
			OriginationDescription varchar(200),
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			description VARCHAR(200) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			rateN DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			FinalRankNumber int,
			AccountID int,
			RateCurrency int,
			ConnectionFeeCurrency int,
			INDEX tmp_Vendorrates_stage2__code (`RowCode`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_dupVRatesstage2_;
		CREATE TEMPORARY TABLE tmp_dupVRatesstage2_  (
			OriginationCode VARCHAR(50) COLLATE utf8_unicode_ci,
			OriginationDescription VARCHAR(200) COLLATE utf8_unicode_ci,
			RowCode VARCHAR(50)  COLLATE utf8_unicode_ci,
			description VARCHAR(200) COLLATE utf8_unicode_ci,
			FinalRankNumber int,
			INDEX tmp_dupVendorrates_stage2__code (`RowCode`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Vendorrates_stage3_;
		CREATE TEMPORARY TABLE tmp_Vendorrates_stage3_  (
			OriginationCode VARCHAR(50) COLLATE utf8_unicode_ci,
			OriginationDescription VARCHAR(200) COLLATE utf8_unicode_ci,
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci,
			description VARCHAR(200) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			rateN DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			AccountID int,
			RateCurrency int,
			ConnectionFeeCurrency int,
			INDEX tmp_Vendorrates_stage2__code (`RowCode`)
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_code_;
		CREATE TEMPORARY TABLE tmp_code_  (
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			INDEX tmp_code_code (`code`)
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_code_origination;
		CREATE TEMPORARY TABLE tmp_code_origination  (
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			INDEX tmp_code_code (`code`)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_all_code_;
		CREATE TEMPORARY TABLE tmp_all_code_ (
			RowCode  varchar(50) COLLATE utf8_unicode_ci,
			Code  varchar(50) COLLATE utf8_unicode_ci,
			RowNo int,
			INDEX Index2 (Code)
		);



		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage_;
		CREATE TEMPORARY TABLE tmp_VendorRate_stage_ (
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci,
			AccountId INT ,
			AccountName VARCHAR(100) ,
			OriginationCode varchar(50),
			OriginationDescription varchar(200),
			Code VARCHAR(50) COLLATE utf8_unicode_ci,
			Rate DECIMAL(18,6) ,
			RateN DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			RateCurrency int,
			ConnectionFeeCurrency int,
			MaxMatchRank int ,
			prev_prev_OriginationCode VARCHAR(50),
			prev_prev_RowCode VARCHAR(50),
			prev_AccountID int
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
		CREATE TEMPORARY TABLE tmp_VendorRate_ (
			AccountId INT ,
			AccountName VARCHAR(100) ,
			OriginationCode varchar(50),
			OriginationDescription varchar(200),
			Code VARCHAR(50) COLLATE utf8_unicode_ci,
			Rate DECIMAL(18,6) ,
			RateN DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			RateCurrency int,
			ConnectionFeeCurrency int,
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_final_VendorRate_;
		CREATE TEMPORARY TABLE tmp_final_VendorRate_ (
			AccountId INT ,
			AccountName VARCHAR(100) ,
			OriginationCode varchar(50),
			OriginationDescription varchar(200),
			Code VARCHAR(50) COLLATE utf8_unicode_ci,
			Rate DECIMAL(18,6) ,
			RateN DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			RateCurrency int,
			ConnectionFeeCurrency int,
			FinalRankNumber int,
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci,
			INDEX IX_CODE (RowCode)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates_GroupBy_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates_GroupBy_(
			AccountId int,
			AccountName varchar(200),
			OriginationCode varchar(50),
			OriginationDescription varchar(200),
			Code LONGTEXT,
			Description varchar(200) ,
			Rate DECIMAL(18,6) ,
			RateN DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate date,
			TrunkID int,
			TimezonesID int,
			CountryID int,
			RateID int,
			Preference int,
			RateCurrency int,
			ConnectionFeeCurrency int

		);

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates_(
			AccountId int,
			AccountName varchar(200),
			OriginationCode varchar(50),
			OriginationDescription varchar(200),
			Code varchar(50) COLLATE utf8_unicode_ci,
			Description varchar(200) ,
			Rate DECIMAL(18,6) ,
			RateN DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate date,
			TrunkID int,
			TimezonesID int,
			CountryID int,
			RateID int,
			Preference int,
			RateCurrency int,
			ConnectionFeeCurrency int,
			INDEX IX_CODE (Code)
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates1_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates1_(
			AccountId int,
			AccountName varchar(200),
			OriginationCode varchar(50),
			OriginationDescription varchar(200),
			Code varchar(50),
			Description varchar(200),
			Rate DECIMAL(18,6) ,
			RateN DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate date,
			TrunkID int,
			TimezonesID int,
			CountryID int,
			RateID int,
			Preference int,
			RateCurrency int,
			ConnectionFeeCurrency int,
			INDEX IX_Code (Code),
			INDEX tmp_VendorCurrentRates_AccountId (`AccountId`,`TrunkID`,`RateId`,`EffectiveDate`)
		);

		SELECT CurrencyID INTO v_CurrencyID_ FROM  tblRateGenerator WHERE RateGeneratorId = p_RateGeneratorId;



		SELECT IFNULL(REPLACE(JSON_EXTRACT(Options, '$.IncreaseEffectiveDate'),'"',''), p_EffectiveDate) , IFNULL(REPLACE(JSON_EXTRACT(Options, '$.DecreaseEffectiveDate'),'"',''), p_EffectiveDate)   INTO v_IncreaseEffectiveDate_ , v_DecreaseEffectiveDate_  FROM tblJob WHERE Jobid = p_jobId;


		IF v_IncreaseEffectiveDate_ is null OR v_IncreaseEffectiveDate_ = '' THEN

			SET v_IncreaseEffectiveDate_ = p_EffectiveDate;

		END IF;

		IF v_DecreaseEffectiveDate_ is null OR v_DecreaseEffectiveDate_ = '' THEN

			SET v_DecreaseEffectiveDate_ = p_EffectiveDate;

		END IF;


		SELECT
			UsePreference,
			rateposition,
			companyid ,
			CodeDeckId,
			tblRateGenerator.TrunkID,
			tblRateGenerator.UseAverage  ,
			tblRateGenerator.RateGeneratorName,
			IF( LessThenRate = '' OR LessThenRate is null 		,0, LessThenRate   ),
			IF( ChargeRate  = '' OR ChargeRate is null			,0, ChargeRate     ),
			IF( percentageRate = '' OR percentageRate is null	,0, percentageRate ),
			IFNULL(AppliedTo,''),
			IFNULL(Reseller,'')


			INTO v_Use_Preference_, v_RatePosition_, v_CompanyId_, v_codedeckid_, v_trunk_, v_Average_, v_RateGeneratorName_,v_LessThenRate,v_ChargeRate,v_percentageRate, v_AppliedTo, v_Reseller
		FROM tblRateGenerator
		WHERE RateGeneratorId = p_RateGeneratorId;


		SELECT CurrencyId INTO v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = v_CompanyId_;

		SELECT IFNULL(Value,0) INTO @v_RateApprovalProcess_ FROM tblCompanySetting WHERE CompanyID = v_CompanyId_ AND `Key`='RateApprovalProcess';

		SELECT IFNULL(Value,0) INTO @v_UseVendorCurrencyInRateGenerator_ FROM tblCompanySetting WHERE CompanyID = v_CompanyId_ AND `Key`='UseVendorCurrencyInRateGenerator';



		INSERT INTO tmp_Raterules_(
										rateruleid,
										Originationcode,
										Originationdescription,
										OriginationType,
										OriginationCountryID,
										DestinationType,
										DestinationCountryID,
										code,
										description,
										RowNo,
										`Order`
								)
			SELECT
				rateruleid,
				Originationcode,
				Originationdescription,
				OriginationType,
				OriginationCountryID,
				DestinationType,
				DestinationCountryID,
				code,
				description,
				@row_num := @row_num+1 AS RowID,
				`Order`
			FROM tblRateRule,(SELECT @row_num := 0) x
			WHERE rategeneratorid = p_RateGeneratorId
			ORDER BY `Order` ASC;


			insert into tmp_Raterules_dup (
										rateruleid,
										Originationcode,
										Originationdescription,
										OriginationType,
										OriginationCountryID,
										DestinationType,
										DestinationCountryID,
										code,
										description,
										RowNo,
										`Order`
									)
			select 							rateruleid,
										Originationcode,
										Originationdescription,
										OriginationType,
										OriginationCountryID,
										DestinationType,
										DestinationCountryID,
										code,
										description,
										RowNo,
										`Order`
			from tmp_Raterules_;

			INSERT INTO tmp_Codedecks_
			SELECT DISTINCT
				tblRateTable.CodeDeckId
			FROM tblRateRule
				INNER JOIN tblRateRuleSource
					ON tblRateRule.RateRuleId = tblRateRuleSource.RateRuleId
				INNER JOIN tblAccount
					ON tblAccount.AccountID = tblRateRuleSource.AccountId and tblAccount.IsVendor = 1
				JOIN tblVendorConnection
					ON tblAccount.AccountId = tblVendorConnection.AccountId
						 AND  tblVendorConnection.TrunkID = v_trunk_
						 AND tblVendorConnection.Active = 1
						 AND tblVendorConnection.RateTypeID = 1
				inner join tblRateTable on  tblRateTable.RateTableId = tblVendorConnection.RateTableID
			WHERE tblRateRule.RateGeneratorId = p_RateGeneratorId;


		SET v_pointer_ = 1;

		SET v_rowCount_ = (SELECT COUNT(rateruleid) FROM tmp_Raterules_);




		insert into tmp_code_
			SELECT  DISTINCT LEFT(f.Code, x.RowNo) as loopCode
			FROM (
						 SELECT @RowNo  := @RowNo + 1 as RowNo
						 FROM mysql.help_category
							 ,(SELECT @RowNo := 0 ) x
						 limit 15
					 ) x
				INNER JOIN
				(SELECT
					 distinct
					 tblRate.code
				 FROM tblRate
					 JOIN tmp_Raterules_ rr
						 ON   ( rr.code = '' OR (rr.code != '' AND tblRate.Code LIKE (REPLACE(rr.code,'*', '%%')) ))
									AND
						    ( rr.DestinationType = '' OR ( tblRate.`Type` = DestinationType ))
									AND
						    ( rr.DestinationCountryID = '' OR (tblRate.`CountryID` = DestinationCountryID ))


				 where  tblRate.CodeDeckId = v_codedeckid_
				 Order by tblRate.code
				) as f
					ON   x.RowNo   <= LENGTH(f.Code)
			order by loopCode   desc;


		insert into tmp_code_origination
			SELECT
				tblRate.code
			FROM tblRate
				JOIN tmp_Codedecks_ cd
					ON tblRate.CodeDeckId = cd.CodeDeckId
				JOIN tmp_Raterules_ rr
					ON ( rr.OriginationCode != '' AND tblRate.Code LIKE (REPLACE(rr.OriginationCode,'*', '%%')) )
							AND
						( rr.OriginationType = '' OR ( tblRate.`Type` = OriginationType ))
								AND
						( rr.OriginationCountryID = '' OR (tblRate.`CountryID` = OriginationCountryID ))




			Order by tblRate.code ;





		SELECT CurrencyId INTO v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = v_CompanyId_;
		SET @IncludeAccountIds = (SELECT GROUP_CONCAT(AccountId) from tblRateRule rr inner join  tblRateRuleSource rrs on rr.RateRuleId = rrs.RateRuleId where rr.RateGeneratorId = p_RateGeneratorId ) ;



		IF(p_IsMerge = 1)
		THEN






			INSERT INTO tmp_VendorCurrentRates1_
				Select DISTINCT AccountId,MAX(AccountName) AS AccountName,MAX(OriginationCode) AS OriginationCode,MAX(OriginationDescription) AS OriginationDescription,MAX(Code) AS Code,MAX(Description) AS Description, ROUND(IF(p_TakePrice=1,MAX(Rate),MIN(Rate)), 6) AS Rate, ROUND(IF(p_TakePrice=1,MAX(RateN),MIN(RateN)), 6) AS RateN,IF(p_TakePrice=1,MAX(ConnectionFee),MIN(ConnectionFee)) AS ConnectionFee,EffectiveDate,TrunkID,p_MergeInto AS TimezonesID,MAX(CountryID) AS CountryID,RateID,MAX(Preference) AS Preference, max(RateCurrency) as RateCurrency ,max(ConnectionFeeCurrency) as  ConnectionFeeCurrency
				FROM (
							 SELECT  vt.AccountId,tblAccount.AccountName, r2.Code as OriginationCode, r2.Description as OriginationDescription,tblRate.Code, tblRate.Description,IFNULL(RateCurrency,rt.CurrencyID) as RateCurrency ,IFNULL(ConnectionFeeCurrency,rt.CurrencyID) as ConnectionFeeCurrency,
									CASE WHEN  tblAccount.CurrencyId = v_CurrencyID_
										THEN
											tblRateTableRate.Rate
									WHEN  v_CompanyCurrencyID_ = v_CurrencyID_
										THEN
											(
												( tblRateTableRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ) )
											)
									ELSE
										(

											(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = v_CurrencyID_ and  CompanyID = v_CompanyId_ )
											* (tblRateTableRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ))
										)
									END as Rate,
									CASE WHEN  tblAccount.CurrencyId = v_CurrencyID_
										THEN
											tblRateTableRate.RateN
									WHEN  v_CompanyCurrencyID_ = v_CurrencyID_
										THEN
											(
												( tblRateTableRate.rateN  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ) )
											)
									ELSE
										(

											(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = v_CurrencyID_ and  CompanyID = v_CompanyId_ )
											* (tblRateTableRate.rateN  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ))
										)
									END as RateN,

									CASE WHEN  tblAccount.CurrencyId = v_CurrencyID_
										THEN
											ConnectionFee
									WHEN  v_CompanyCurrencyID_ = v_CurrencyID_
										THEN
											(
												( ConnectionFee  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ) )
											)
									ELSE
										(

											(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = v_CurrencyID_ and  CompanyID = v_CompanyId_ )
											* (ConnectionFee  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ))
										)
									END
									as ConnectionFee,


									DATE_FORMAT (tblRateTableRate.EffectiveDate, '%Y-%m-%d') AS EffectiveDate,
								 vt.TrunkID,
								 tblRateTableRate.TimezonesID, tblRate.CountryID, tblRate.RateID,IFNULL(Preference, 5) AS Preference,
									@row_num := IF(@prev_AccountId = vt.AccountID AND @prev_TrunkID = vt.TrunkID AND @prev_OriginationRateId = tblRateTableRate.OriginationRateId AND @prev_RateId = tblRateTableRate.RateID AND @prev_EffectiveDate >= tblRateTableRate.EffectiveDate, @row_num + 1, 1) AS RowID,
								 @prev_AccountId := vt.AccountID,
								 @prev_TrunkID := vt.TrunkID,
								 @prev_TimezonesID := tblRateTableRate.TimezonesID,
								 @prev_OriginationRateId := tblRateTableRate.OriginationRateId,
								 @prev_RateId := tblRateTableRate.RateID,
								 @prev_EffectiveDate := tblRateTableRate.EffectiveDate
							 FROM tblRateTableRate
								 Inner join tblRateTable rt on  rt.CompanyID = v_CompanyId_ and rt.RateTableID = tblRateTableRate.RateTableID
								Inner join tblVendorConnection vt on vt.CompanyID = v_CompanyId_ AND vt.RateTableID = tblRateTableRate.RateTableID  and vt.RateTypeID = 1  and vt.Active = 1  and vt.TrunkID =  v_trunk_

								 Inner join tblTimezones t on t.TimezonesID = tblRateTableRate.TimezonesID AND t.Status = 1
								 inner join tmp_Codedecks_ tcd on rt.CodeDeckId = tcd.CodeDeckId
								 INNER JOIN tblAccount   ON  tblAccount.AccountID = vt.AccountId AND tblAccount.CompanyID = v_CompanyId_ and tblAccount.IsVendor = 1
								 INNER JOIN tblRate ON tblRate.CompanyID = v_CompanyId_  AND tblRate.CodeDeckId = rt.CodeDeckId  AND    tblRateTableRate.RateId = tblRate.RateID
								 INNER JOIN tmp_code_ tcode ON tcode.Code  = tblRate.Code

 								 LEFT JOIN tblRate r2 ON r2.CompanyID = v_CompanyId_  AND r2.CodeDeckId = rt.CodeDeckId  AND    tblRateTableRate.OriginationRateId = r2.RateID
								 LEFT JOIN tmp_code_origination tcode2 ON tcode2.Code  = r2.Code


								 ,(SELECT @row_num := 1,  @prev_AccountId := '',@prev_TrunkID := '',@prev_TimezonesID := '', @prev_OriginationRateId := '',  @prev_RateId := '', @prev_EffectiveDate := '') x

							 WHERE
								 (
									 (p_EffectiveRate = 'now' AND EffectiveDate <= NOW())
									 OR
									 (p_EffectiveRate = 'future' AND EffectiveDate > NOW()  )
									 OR
									 (	 p_EffectiveRate = 'effective' AND EffectiveDate <= p_EffectiveDate
											 AND ( tblRateTableRate.EndDate IS NULL OR (tblRateTableRate.EndDate > DATE(p_EffectiveDate)) )
									 )
								 )

								 AND ( tblRateTableRate.EndDate IS NULL OR tblRateTableRate.EndDate > now() )
								 AND tblAccount.IsVendor = 1
								 AND tblAccount.Status = 1
								 AND tblAccount.CurrencyId is not NULL
								 AND vt.TrunkID = v_trunk_
								 AND FIND_IN_SET(tblRateTableRate.TimezonesID,p_TimezonesID) != 0
								 AND tblRateTableRate.Blocked = 0


								 AND ( @IncludeAccountIds = NULL
											 OR ( @IncludeAccountIds IS NOT NULL
														AND FIND_IN_SET(vt.AccountId,@IncludeAccountIds) > 0
											 )
								 )
							 ORDER BY vt.AccountId, vt.TrunkID, tblRateTableRate.TimezonesID, tblRateTableRate.RateId, tblRateTableRate.EffectiveDate DESC
						 ) tbl
				GROUP BY RateID, AccountId, TrunkID, EffectiveDate
				order by Code asc;

		ELSE

			INSERT INTO tmp_VendorCurrentRates1_
				Select DISTINCT AccountId,AccountName,OriginationCode,OriginationDescription,Code,Description, Rate, RateN,ConnectionFee,EffectiveDate,TrunkID,TimezonesID,CountryID,RateID,Preference,RateCurrency,ConnectionFeeCurrency
				FROM (
 							 SELECT  vt.AccountId,tblAccount.AccountName, r2.Code as OriginationCode, r2.Description as OriginationDescription,tblRate.Code, tblRate.Description,IFNULL(RateCurrency,rt.CurrencyID) as RateCurrency ,IFNULL(ConnectionFeeCurrency,rt.CurrencyID) as ConnectionFeeCurrency,

								CASE WHEN  tblAccount.CurrencyId = v_CurrencyID_
									THEN
										tblRateTableRate.Rate
								WHEN  v_CompanyCurrencyID_ = v_CurrencyID_
									THEN
										(
											( tblRateTableRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ) )
										)
								ELSE
									(

										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = v_CurrencyID_ and  CompanyID = v_CompanyId_ )
										* (tblRateTableRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ))
									)
								END as Rate,
								CASE WHEN  tblAccount.CurrencyId = v_CurrencyID_
									THEN
										tblRateTableRate.RateN
								WHEN  v_CompanyCurrencyID_ = v_CurrencyID_
									THEN
										(
											( tblRateTableRate.rateN  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ) )
										)
								ELSE
									(

										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = v_CurrencyID_ and  CompanyID = v_CompanyId_ )
										* (tblRateTableRate.rateN  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ))
									)
								END as RateN,
								CASE WHEN  tblAccount.CurrencyId = v_CurrencyID_
									THEN
										ConnectionFee
								WHEN  v_CompanyCurrencyID_ = v_CurrencyID_
									THEN
										(
											( ConnectionFee  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ) )
										)
								ELSE
									(

										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = v_CurrencyID_ and  CompanyID = v_CompanyId_ )
										* (ConnectionFee  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ))
									)
								END
								as ConnectionFee,

								DATE_FORMAT (tblRateTableRate.EffectiveDate, '%Y-%m-%d') AS EffectiveDate,
								 vt.TrunkID,
								 tblRateTableRate.TimezonesID,
								 tblRate.CountryID,
								 tblRate.RateID,
								 IFNULL(Preference, 5) AS Preference,
								 @row_num := IF(@prev_AccountId = vt.AccountID AND @prev_TrunkID = vt.TrunkID AND @prev_RateId = tblRateTableRate.RateID AND @prev_EffectiveDate >= tblRateTableRate.EffectiveDate, @row_num + 1, 1) AS RowID,
								 @prev_AccountId := vt.AccountID,
								 @prev_TrunkID := vt.TrunkID,
								 @prev_TimezonesID := tblRateTableRate.TimezonesID,
								 @prev_RateId := tblRateTableRate.RateID,
								 @prev_EffectiveDate := tblRateTableRate.EffectiveDate



							 FROM tblRateTableRate
								Inner join  tblRateTable rt on  rt.CompanyID = v_CompanyId_ and rt.RateTableID = tblRateTableRate.RateTableID
								Inner join tblVendorConnection vt on vt.CompanyID = v_CompanyId_ AND vt.RateTableID = tblRateTableRate.RateTableID  and vt.RateTypeID = 1  and vt.Active = 1  and vt.TrunkID =  v_trunk_
								 Inner join tblTimezones t on t.TimezonesID = tblRateTableRate.TimezonesID AND t.Status = 1
								 inner join tmp_Codedecks_ tcd on rt.CodeDeckId = tcd.CodeDeckId
								 INNER JOIN tblAccount   ON  tblAccount.AccountID = vt.AccountId AND tblAccount.CompanyID = v_CompanyId_ and tblAccount.IsVendor = 1
								 INNER JOIN tblRate ON tblRate.CompanyID = v_CompanyId_  AND tblRate.CodeDeckId = rt.CodeDeckId  AND    tblRateTableRate.RateId = tblRate.RateID
								 INNER JOIN tmp_code_ tcode ON tcode.Code  = tblRate.Code

 								 LEFT JOIN tblRate r2 ON r2.CompanyID = v_CompanyId_  AND r2.CodeDeckId = rt.CodeDeckId  AND    tblRateTableRate.OriginationRateId = r2.RateID
								 LEFT JOIN tmp_code_origination tcode2 ON tcode2.Code  = r2.Code

								 ,(SELECT @row_num := 1,  @prev_AccountId := '',@prev_TrunkID := '',@prev_TimezonesID := '', @prev_RateId := '', @prev_EffectiveDate := '') x

							 WHERE
								 (
									 (p_EffectiveRate = 'now' AND EffectiveDate <= NOW())
									 OR
									 (p_EffectiveRate = 'future' AND EffectiveDate > NOW()  )
									 OR
									 (	 p_EffectiveRate = 'effective' AND EffectiveDate <= p_EffectiveDate
											 AND ( tblRateTableRate.EndDate IS NULL OR (tblRateTableRate.EndDate > DATE(p_EffectiveDate)) )
									 )
								 )


								 AND ( tblRateTableRate.EndDate IS NULL OR tblRateTableRate.EndDate > now() )
								 AND tblAccount.IsVendor = 1
								 AND tblAccount.Status = 1
								 AND tblAccount.CurrencyId is not NULL
								 AND vt.TrunkID = v_trunk_
								 AND tblRateTableRate.TimezonesID = v_TimezonesID
								 AND tblRateTableRate.Blocked = 0
								 AND ( @IncludeAccountIds = NULL
											 OR ( @IncludeAccountIds IS NOT NULL
														AND FIND_IN_SET(vt.AccountId,@IncludeAccountIds) > 0
											 )
								 )
							 ORDER BY vt.AccountId, vt.TrunkID, tblRateTableRate.TimezonesID, tblRateTableRate.RateId, tblRateTableRate.EffectiveDate DESC
						 ) tbl
				order by Code asc;

		END IF;




		INSERT INTO tmp_VendorCurrentRates_
		Select AccountId,AccountName,OriginationCode,OriginationDescription,Code,Description, Rate, RateN,ConnectionFee,EffectiveDate,TrunkID,TimezonesID,CountryID,RateID,Preference,RateCurrency,ConnectionFeeCurrency
		FROM (
					 SELECT * ,
						 @row_num := IF(@prev_AccountId = AccountID AND @prev_TrunkID = TrunkID AND @prev_TimezonesID = TimezonesID AND @prev_RateId = RateID AND @prev_EffectiveDate >= EffectiveDate, @row_num + 1, 1) AS RowID,
						 @prev_AccountId := AccountID,
						 @prev_TrunkID := TrunkID,
						 @prev_TimezonesID := TimezonesID,
						 @prev_RateId := RateID,
						 @prev_EffectiveDate := EffectiveDate
					 FROM tmp_VendorCurrentRates1_
						 ,(SELECT @row_num := 1,  @prev_AccountId := 0 ,@prev_TrunkID := 0 ,@prev_TimezonesID := 0, @prev_RateId := 0, @prev_EffectiveDate := '') x
					 ORDER BY AccountId, TrunkID, TimezonesID, RateId, EffectiveDate DESC
				 ) tbl
		WHERE RowID = 1
		order by Code asc;

 		insert into tmp_all_code_ (RowCode,Code,RowNo)
			select RowCode , loopCode,RowNo
			from (
						 select   RowCode , loopCode,
							 @RowNo := ( CASE WHEN (@prev_Code  = tbl1.RowCode  ) THEN @RowNo + 1
													 ELSE 1
													 END

							 )      as RowNo,
							 @prev_Code := tbl1.RowCode

						 from (
										SELECT distinct f.Code as RowCode, LEFT(f.Code, x.RowNo) as loopCode
										FROM (
													 SELECT @RowNo  := @RowNo + 1 as RowNo
													 FROM mysql.help_category
														 ,(SELECT @RowNo := 0 ) x
													 limit 15
												 ) x
											INNER JOIN
											(
												select distinct Code from
													tmp_VendorCurrentRates_
											) AS f
												ON  x.RowNo   <= LENGTH(f.Code)
										order by RowCode desc,  LENGTH(loopCode) DESC
									) tbl1
							 , ( Select @RowNo := 0 ) x
					 ) tbl order by RowCode desc,  LENGTH(loopCode) DESC ;





		IF p_GroupBy = 'Desc'
		THEN

			INSERT INTO tmp_VendorCurrentRates_GroupBy_
				Select AccountId,max(AccountName),max(OriginationCode),OriginationDescription,max(Code),Description,max(Rate),max(RateN),max(ConnectionFee),max(EffectiveDate),TrunkID,TimezonesID,max(CountryID),max(RateID),max(Preference),max(RateCurrency) as RateCurrency ,max(ConnectionFeeCurrency) as  ConnectionFeeCurrency
				FROM
				(

					Select AccountId,AccountName,OriginationCode,OriginationDescription,r.Code,r.Description, Rate, RateN,ConnectionFee,EffectiveDate,TrunkID,TimezonesID,r.CountryID,r.RateID,Preference,RateCurrency,ConnectionFeeCurrency
					FROM tmp_VendorCurrentRates_ v
					Inner join  tmp_all_code_ SplitCode   on v.Code = SplitCode.Code
					Inner join  tblRate r   on r.CodeDeckId = v_codedeckid_ AND r.Code = SplitCode.RowCode


				) tmp
				GROUP BY AccountId, TrunkID, TimezonesID, Description,OriginationDescription
				order by Description asc;


				truncate table tmp_VendorCurrentRates_;

				INSERT INTO tmp_VendorCurrentRates_ (AccountId,AccountName,OriginationCode,OriginationDescription,Code,Description, Rate, RateN,ConnectionFee,EffectiveDate,TrunkID,TimezonesID,CountryID,RateID,Preference,RateCurrency,ConnectionFeeCurrency)
			  		SELECT AccountId,AccountName,OriginationCode,OriginationDescription,Code,Description, Rate, RateN,ConnectionFee,EffectiveDate,TrunkID,TimezonesID,CountryID,RateID,Preference,RateCurrency,ConnectionFeeCurrency
					FROM tmp_VendorCurrentRates_GroupBy_;


		END IF;




		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage_1;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRate_stage_1 as (select * from tmp_VendorRate_stage_);

		insert ignore into tmp_VendorRate_stage_1 (
			RowCode,
			AccountId ,
			AccountName ,
			OriginationCode,
			OriginationDescription,
			Code ,
			Rate ,
			RateN ,
			ConnectionFee,
			EffectiveDate ,
			Description ,
			Preference,
			RateCurrency,
			ConnectionFeeCurrency
		)
			SELECT
				distinct
				RowCode,
				v.AccountId ,
				v.AccountName ,
				v.OriginationCode,
				v.OriginationDescription,
				v.Code ,
				v.Rate ,
				v.RateN ,
				v.ConnectionFee,
				v.EffectiveDate ,
				v.Description ,
				v.Preference,
				v.RateCurrency,
				v.ConnectionFeeCurrency

			FROM tmp_VendorCurrentRates_ v
			Inner join  tmp_all_code_ SplitCode on v.Code = SplitCode.Code
			where  SplitCode.Code is not null
			order by AccountID,SplitCode.RowCode desc ,LENGTH(SplitCode.RowCode), v.Code desc, LENGTH(v.Code)  desc;



		insert into tmp_VendorRate_stage_
			SELECT
				RowCode,
				v.AccountId ,
				v.AccountName ,
				v.OriginationCode,
				v.OriginationDescription,
				v.Code ,
				v.Rate ,
				v.RateN ,
				v.ConnectionFee,
				v.EffectiveDate ,
				v.Description ,
				v.Preference,
				v.RateCurrency,
				v.ConnectionFeeCurrency,
				@rank := ( CASE WHEN ( @prev_OriginationCode = OriginationCode and  @prev_RowCode   = RowCode and   @prev_AccountID = v.AccountId   )
					THEN @rank + 1
					ELSE 1  END ) AS MaxMatchRank,
				@prev_OriginationCode := v.OriginationCode,
				@prev_RowCode := RowCode	 as prev_RowCode,
				@prev_AccountID := v.AccountId as prev_AccountID
			FROM tmp_VendorRate_stage_1 v
				, (SELECT  @prev_OriginationCode := NUll , @prev_RowCode := '',  @rank := 0 , @prev_Code := '' , @prev_AccountID := Null) f
			order by AccountID,OriginationCode,RowCode desc ;


		truncate tmp_VendorRate_;
		insert into tmp_VendorRate_
			select
				AccountId ,
				AccountName ,
				OriginationCode,
				OriginationDescription,
				Code ,
				Rate ,
				RateN ,
				ConnectionFee,
				EffectiveDate ,
				Description ,
				Preference,
				RateCurrency,
				ConnectionFeeCurrency,
				RowCode
			from tmp_VendorRate_stage_
			where MaxMatchRank = 1 order by OriginationCode,RowCode desc;







		WHILE v_pointer_ <= v_rowCount_
		DO

			SET v_rateRuleId_ = (SELECT rateruleid FROM tmp_Raterules_ rr WHERE rr.RowNo = v_pointer_);


				INSERT INTO tmp_Rates2_ (OriginationCode,OriginationDescription,code,description,rate,rateN,ConnectionFee,AccountID,RateCurrency,ConnectionFeeCurrency)
				select  OriginationCode,OriginationDescription,code,description,rate,rateN,ConnectionFee,AccountID,RateCurrency,ConnectionFeeCurrency from tmp_Rates_;



				IF p_GroupBy = 'Desc'
				THEN


						INSERT IGNORE INTO tmp_Rates3_ (OriginationCode,OriginationDescription,code,description)
						 select distinct tmpvr.OriginationCode,tmpvr.OriginationDescription,r.code,r.description
						from tmp_VendorCurrentRates1_  tmpvr
						Inner join  tblRate r   on r.CodeDeckId = v_codedeckid_ AND r.Code = tmpvr.Code
						left join  tblRate r2   on r2.CodeDeckId = v_codedeckid_ AND r2.Code = tmpvr.OriginationCode
						inner JOIN tmp_Raterules_ rr ON rr.RateRuleId = v_rateRuleId_ and

																 (
																	 ( rr.OriginationCode = ''  OR ( rr.OriginationCode != '' AND tmpvr.OriginationCode  LIKE (REPLACE(rr.OriginationCode,'*', '%%')) ) )
											 							AND
																	( rr.OriginationType = '' OR ( r2.`Type` = rr.OriginationType ))
																		AND
																	( rr.OriginationCountryID = '' OR (r2.`CountryID` = rr.OriginationCountryID ))




																 )
																 AND
																(
																	 ( rr.code = '' OR ( rr.code != '' AND tmpvr.Code LIKE (REPLACE(rr.code,'*', '%%')) ))

																		AND
																		( rr.DestinationType = '' OR ( r.`Type` = rr.DestinationType ))
																		AND
																		( rr.DestinationCountryID = '' OR (r.`CountryID` = rr.DestinationCountryID ))



																 )
																left JOIN tmp_Raterules_dup rr2 ON rr2.Order > rr.Order and
																(
																	 ( rr2.OriginationCode = ''  OR ( rr2.OriginationCode != '' AND tmpvr.OriginationCode  LIKE (REPLACE(rr2.OriginationCode,'*', '%%')) ) )

																		AND
																		( rr2.OriginationType = '' OR ( r2.`Type` = rr2.OriginationType ))
																		AND
																		( rr2.OriginationCountryID = '' OR (r2.`CountryID` = rr2.OriginationCountryID ))




																 )
																 AND
																(
																	 ( rr2.code = '' OR ( rr2.code != '' AND tmpvr.Code  LIKE (REPLACE(rr2.code,'*', '%%')) ))

																	AND
																	( rr2.DestinationType = '' OR ( r.`Type` = rr2.DestinationType ))
																	AND
																	( rr2.DestinationCountryID = '' OR (r.`CountryID` = rr2.DestinationCountryID ))



																 )
						inner JOIN tblRateRuleSource rrs ON  rrs.RateRuleId = rr.rateruleid  and rrs.AccountId = tmpvr.AccountId
						where rr2.code is null;

				END IF;



			truncate tmp_final_VendorRate_;

			IF( v_Use_Preference_ = 0 )
			THEN

				insert into tmp_final_VendorRate_
					SELECT
						AccountId ,
						AccountName ,
						OriginationCode ,
						OriginationDescription ,
						Code ,
						Rate ,
						RateN ,
						ConnectionFee,
						EffectiveDate ,
						Description ,
						Preference,
						RateCurrency,
						ConnectionFeeCurrency,
						FinalRankNumber,
						RowCode

					from
						(
							SELECT
								vr.AccountId ,
								vr.AccountName ,
								vr.OriginationCode ,
								vr.OriginationDescription ,
								vr.Code ,
								vr.Rate ,
								vr.RateN ,
								vr.ConnectionFee,
								vr.EffectiveDate ,
								vr.Description ,
								vr.Preference,
								vr.RowCode,
								vr.RateCurrency,
								vr.ConnectionFeeCurrency,

								CASE WHEN p_GroupBy = 'Desc'  THEN
													@rank := CASE WHEN (@prev_OriginationDescription = vr.OriginationDescription AND  @prev_Description = vr.Description  AND @prev_Rate <=  vr.Rate AND (v_percentageRate = 0 OR  (v_percentageRate > 0 AND ROUND(((vr.Rate - @prev_Rate) /( @prev_Rate * 100)),2) > v_percentageRate) )  ) THEN @rank+1
													 ELSE
														 1
													 END

								ELSE	@rank := CASE WHEN ( @prev_OriginationCode = vr.OriginationCode  AND  @prev_RowCode = vr.RowCode  AND @prev_Rate <=  vr.Rate  AND (v_percentageRate = 0 OR  (v_percentageRate > 0 AND ROUND(((vr.Rate - @prev_Rate) /( @prev_Rate * 100)),2) > v_percentageRate) ) ) THEN @rank+1

													 ELSE
														 1
													 END
								END
									AS FinalRankNumber,
								@prev_OriginationCode  := vr.OriginationCode,
								@prev_RowCode  := vr.RowCode,
								@prev_Description  := vr.Description,
								@prev_OriginationDescription  := vr.OriginationDescription,
								@prev_Rate  := vr.Rate
							from (
										 select distinct tmpvr.*
										 from tmp_VendorRate_  tmpvr
										inner JOIN tmp_Raterules_ rr ON rr.RateRuleId = v_rateRuleId_ and
										 (
											 ( rr.OriginationCode = ''  OR ( rr.OriginationCode != '' AND tmpvr.OriginationCode  LIKE (REPLACE(rr.OriginationCode,'*', '%%')) ) )
													AND
											( rr.OriginationType = '' OR ( r2.`Type` = rr.OriginationType ))
												AND
											( rr.OriginationCountryID = '' OR (r2.`CountryID` = rr.OriginationCountryID ))




										 )
										 AND
										(
											 ( rr.code = '' OR ( rr.code != '' AND tmpvr.RowCode LIKE (REPLACE(rr.code,'*', '%%')) ))

												AND
												( rr.DestinationType = '' OR ( r.`Type` = rr.DestinationType ))
												AND
												( rr.DestinationCountryID = '' OR (r.`CountryID` = rr.DestinationCountryID ))



										 )
										left JOIN tmp_Raterules_dup rr2 ON rr2.Order > rr.Order and
										(
											 ( rr2.OriginationCode = ''  OR ( rr2.OriginationCode != '' AND tmpvr.OriginationCode  LIKE (REPLACE(rr2.OriginationCode,'*', '%%')) ) )

												AND
												( rr2.OriginationType = '' OR ( r2.`Type` = rr2.OriginationType ))
												AND
												( rr2.OriginationCountryID = '' OR (r2.`CountryID` = rr2.OriginationCountryID ))




										 )
										 AND
										(
											 ( rr2.code = '' OR ( rr2.code != '' AND tmpvr.RowCode  LIKE (REPLACE(rr2.code,'*', '%%')) ))

											AND
											( rr2.DestinationType = '' OR ( r.`Type` = rr2.DestinationType ))
											AND
											( rr2.DestinationCountryID = '' OR (r.`CountryID` = rr2.DestinationCountryID ))



										 )
										 inner JOIN tblRateRuleSource rrs ON  rrs.RateRuleId = rr.rateruleid  and rrs.AccountId = tmpvr.AccountId

										 where rr2.code is null

									 ) vr
								,(SELECT @rank := 0 ,@prev_OriginationCode := ''  ,@prev_OriginationDescription := ''  , @prev_RowCode := '' , @prev_Rate := 0 , @prev_Description := ''  ) x
							order by
								CASE WHEN p_GroupBy = 'Desc'  THEN
									vr.OriginationDescription
								ELSE
									vr.OriginationCode
								END ,
								CASE WHEN p_GroupBy = 'Desc'  THEN
									vr.Description
								ELSE
									vr.RowCode
								END , vr.Rate,vr.AccountId

						) tbl1
					where FinalRankNumber <= v_RatePosition_;

			ELSE


				insert into tmp_final_VendorRate_
					SELECT
						AccountId ,
						AccountName ,
						OriginationCode ,
						OriginationDescription ,
						Code ,
						Rate ,
						RateN ,
						ConnectionFee,
						EffectiveDate ,
						Description ,
						Preference,
						RateCurrency,
						ConnectionFeeCurrency,
						FinalRankNumber,
						RowCode


					from
						(
							SELECT
								vr.AccountId ,
								vr.AccountName ,
								vr.OriginationCode ,
								vr.OriginationDescription ,
								vr.Code ,
								vr.Rate ,
								vr.RateN ,
								vr.ConnectionFee,
								vr.EffectiveDate ,
								vr.Description ,
								vr.Preference,
								vr.RowCode,
								vr.RateCurrency,
								vr.ConnectionFeeCurrency,
								CASE WHEN p_GroupBy = 'Desc'  THEN

										@preference_rank := CASE WHEN (@prev_Description  = vr.Description  AND @prev_Preference > vr.Preference  )   THEN @preference_rank + 1
																		WHEN (@prev_Description  = vr.Description  AND @prev_Preference = vr.Preference AND @prev_Rate <= vr.Rate) THEN @preference_rank + 1

																		ELSE 1 END
								ELSE
												@preference_rank := CASE WHEN (@prev_Code  = vr.RowCode  AND @prev_Preference > vr.Preference  )   THEN @preference_rank + 1
																		WHEN (@prev_Code  = vr.RowCode  AND @prev_Preference = vr.Preference AND @prev_Rate <= vr.Rate) THEN @preference_rank + 1

																		ELSE 1 END
								END

								AS FinalRankNumber,
								@prev_Code := vr.RowCode,
								@prev_Description  := vr.Description,
								@prev_Preference := vr.Preference,
								@prev_Rate := vr.Rate
							from (
										 select distinct tmpvr.*
										 from tmp_VendorRate_  tmpvr
										inner JOIN tmp_Raterules_ rr ON rr.RateRuleId = v_rateRuleId_ and

										 (
											 ( rr.OriginationCode = ''  OR ( rr.OriginationCode != '' AND tmpvr.OriginationCode  LIKE (REPLACE(rr.OriginationCode,'*', '%%')) ) )
													AND
											( rr.OriginationType = '' OR ( r2.`Type` = rr.OriginationType ))
												AND
											( rr.OriginationCountryID = '' OR (r2.`CountryID` = rr.OriginationCountryID ))




										 )
										 AND
										(
											 ( rr.code = '' OR ( rr.code != '' AND tmpvr.RowCode LIKE (REPLACE(rr.code,'*', '%%')) ))

												AND
												( rr.DestinationType = '' OR ( r.`Type` = rr.DestinationType ))
												AND
												( rr.DestinationCountryID = '' OR (r.`CountryID` = rr.DestinationCountryID ))



										 )
										left JOIN tmp_Raterules_dup rr2 ON rr2.Order > rr.Order and
										(
											 ( rr2.OriginationCode = ''  OR ( rr2.OriginationCode != '' AND tmpvr.OriginationCode  LIKE (REPLACE(rr2.OriginationCode,'*', '%%')) ) )

												AND
												( rr2.OriginationType = '' OR ( r2.`Type` = rr2.OriginationType ))
												AND
												( rr2.OriginationCountryID = '' OR (r2.`CountryID` = rr2.OriginationCountryID ))




										 )
										 AND
										(
											 ( rr2.code = '' OR ( rr2.code != '' AND tmpvr.RowCode  LIKE (REPLACE(rr2.code,'*', '%%')) ))

											AND
											( rr2.DestinationType = '' OR ( r.`Type` = rr2.DestinationType ))
											AND
											( rr2.DestinationCountryID = '' OR (r.`CountryID` = rr2.DestinationCountryID ))



										 )
											 inner JOIN tblRateRuleSource rrs ON  rrs.RateRuleId = rr.rateruleid  and rrs.AccountId = tmpvr.AccountId
										 where rr2.code is null

									 ) vr

								,(SELECT @preference_rank := 0 , @prev_Code := ''  , @prev_Preference := 5,  @prev_Rate := 0, @prev_Description := '') x
							order by
							CASE WHEN p_GroupBy = 'Desc'  THEN
									vr.OriginationDescription
								ELSE
									vr.OriginationCode
								END ,
							CASE WHEN p_GroupBy = 'Desc'  THEN
									vr.Description
								ELSE
									vr.RowCode
								END , vr.Preference DESC ,vr.Rate ASC ,vr.AccountId ASC
						) tbl1
					where FinalRankNumber <= v_RatePosition_;


			END IF;



			truncate   tmp_VRatesstage2_;

			INSERT INTO tmp_VRatesstage2_
				SELECT
					vr.RowCode,
					vr.OriginationCode ,
					vr.OriginationDescription ,
					vr.code,
					vr.description,
					vr.rate,
					vr.rateN,
					vr.ConnectionFee,
					vr.FinalRankNumber,
					vr.AccountID,
					vr.RateCurrency,
					vr.ConnectionFeeCurrency
				FROM tmp_final_VendorRate_ vr
					left join tmp_Rates2_ rate on rate.Code = vr.RowCode
				WHERE  rate.code is null
				order by vr.FinalRankNumber desc ;



			IF v_Average_ = 0
			THEN


				IF p_GroupBy = 'Desc'
				THEN

						insert into tmp_dupVRatesstage2_
						SELECT max(OriginationCode) , OriginationDescription, max(RowCode) , description,   MAX(FinalRankNumber) AS MaxFinalRankNumber
						FROM tmp_VRatesstage2_ GROUP BY OriginationDescription , description;

					truncate tmp_Vendorrates_stage3_;
					INSERT INTO tmp_Vendorrates_stage3_
						select  vr.OriginationCode ,vr.OriginationDescription , vr.RowCode as RowCode ,vr.description , vr.rate as rate , vr.rateN as rateN , vr.ConnectionFee as  ConnectionFee,vr.AccountID,vr.RateCurrency,vr.ConnectionFeeCurrency
						from tmp_VRatesstage2_ vr
							INNER JOIN tmp_dupVRatesstage2_ vr2
								ON (vr.description = vr2.description AND  vr.FinalRankNumber = vr2.FinalRankNumber);


				ELSE

					insert into tmp_dupVRatesstage2_
						SELECT OriginationCode , MAX(OriginationDescription), RowCode , MAX(description),   MAX(FinalRankNumber) AS MaxFinalRankNumber
						FROM tmp_VRatesstage2_ GROUP BY OriginationCode, RowCode;

					truncate tmp_Vendorrates_stage3_;
					INSERT INTO tmp_Vendorrates_stage3_
						select vr.OriginationCode ,vr.OriginationDescription , vr.RowCode as RowCode ,vr.description , vr.rate as rate , vr.rateN as rateN , vr.ConnectionFee as  ConnectionFee,vr.AccountID,vr.RateCurrency,vr.ConnectionFeeCurrency
						from tmp_VRatesstage2_ vr
							INNER JOIN tmp_dupVRatesstage2_ vr2
								ON (vr.RowCode = vr2.RowCode AND  vr.FinalRankNumber = vr2.FinalRankNumber);

				END IF;


				INSERT IGNORE INTO tmp_Rates_ (OriginationCode ,OriginationDescription,code,description,rate,rateN,ConnectionFee,PreviousRate,AccountID,RateCurrency,ConnectionFeeCurrency)
                SELECT 	OriginationCode,
						OriginationDescription,
						RowCode,
		                description,
                    CASE WHEN rule_mgn1.RateRuleId is not null
                        THEN
                            CASE WHEN trim(IFNULL(rule_mgn1.AddMargin,"")) != '' THEN
                                vRate.rate + (CASE WHEN rule_mgn1.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn1.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * vRate.rate) ELSE rule_mgn1.addmargin END)
                            WHEN trim(IFNULL(rule_mgn1.FixedValue,"")) != '' THEN
                                rule_mgn1.FixedValue
                            ELSE
                                vRate.rate
                            END
                    ELSE
                        vRate.rate
                    END as Rate,
                    CASE WHEN rule_mgn2.RateRuleId is not null
                        THEN
                            CASE WHEN trim(IFNULL(rule_mgn2.AddMargin,"")) != '' THEN
                                vRate.rateN + (CASE WHEN rule_mgn2.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn2.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * vRate.rateN) ELSE rule_mgn2.addmargin END)
                            WHEN trim(IFNULL(rule_mgn2.FixedValue,"")) != '' THEN
                                rule_mgn2.FixedValue
                            ELSE
                                vRate.rateN
                            END
                    ELSE
                        vRate.rateN
                    END as RateN,
                    ConnectionFee,
					null AS PreviousRate,
						AccountID,
						RateCurrency,
						ConnectionFeeCurrency

                FROM tmp_Vendorrates_stage3_ vRate
                LEFT join tblRateRuleMargin rule_mgn1 on  rule_mgn1.RateRuleId = v_rateRuleId_ and vRate.rate Between rule_mgn1.MinRate and rule_mgn1.MaxRate
                LEFT join tblRateRuleMargin rule_mgn2 on  rule_mgn2.RateRuleId = v_rateRuleId_ and vRate.rateN Between rule_mgn2.MinRate and rule_mgn2.MaxRate;




			ELSE

				INSERT IGNORE INTO tmp_Rates_ (OriginationCode ,OriginationDescription,code,description,rate,rateN,ConnectionFee,PreviousRate,AccountID,RateCurrency,ConnectionFeeCurrency)
                SELECT 	OriginationCode ,
						OriginationDescription,
						RowCode,
		                description,
                    CASE WHEN rule_mgn1.RateRuleId is not null
                        THEN
                            CASE WHEN trim(IFNULL(rule_mgn1.AddMargin,"")) != '' THEN
                                vRate.rate + (CASE WHEN rule_mgn1.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn1.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * vRate.rate) ELSE rule_mgn1.addmargin END)
                            WHEN trim(IFNULL(rule_mgn1.FixedValue,"")) != '' THEN
                                rule_mgn1.FixedValue
                            ELSE
                                vRate.rate
                            END
                    ELSE
                        vRate.rate
                    END as Rate,
                    CASE WHEN rule_mgn2.RateRuleId is not null
                        THEN
                            CASE WHEN trim(IFNULL(rule_mgn2.AddMargin,"")) != '' THEN
                                vRate.rateN + (CASE WHEN rule_mgn2.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn2.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * vRate.rateN) ELSE rule_mgn2.addmargin END)
                            WHEN trim(IFNULL(rule_mgn2.FixedValue,"")) != '' THEN
                                rule_mgn2.FixedValue
                            ELSE
                                vRate.rateN
                            END
                    ELSE
                        vRate.rateN
                    END as RateN,
                    ConnectionFee,
					null AS PreviousRate,
					AccountID,
					RateCurrency,
					ConnectionFeeCurrency

                FROM
                (
                     select
                        max(RowCode) AS RowCode,
                        max(OriginationCode) as OriginationCode,
						max(OriginationDescription) as OriginationDescription,
                        max(description) AS description,
                        AVG(Rate) as Rate,
                        AVG(RateN) as RateN,
                        AVG(ConnectionFee) as ConnectionFee,
						max(AccountID) as AccountID,
						max(RateCurrency) as RateCurrency,
						max(ConnectionFeeCurrency) as ConnectionFeeCurrency

                        from tmp_VRatesstage2_
                        group by
                        CASE WHEN p_GroupBy = 'Desc' THEN
                          OriginationDescription
                        ELSE
							OriginationCode
      					END,
						CASE WHEN p_GroupBy = 'Desc' THEN
                          description
                        ELSE  RowCode
      					END

                )  vRate
                LEFT join tblRateRuleMargin rule_mgn1 on  rule_mgn1.RateRuleId = v_rateRuleId_ and vRate.rate Between rule_mgn1.MinRate and rule_mgn1.MaxRate
                LEFT join tblRateRuleMargin rule_mgn2 on  rule_mgn2.RateRuleId = v_rateRuleId_ and vRate.rateN Between rule_mgn2.MinRate and rule_mgn2.MaxRate;

			END IF;


			SET v_pointer_ = v_pointer_ + 1;


		END WHILE;


		/*IF p_GroupBy = 'Desc'
		THEN

			truncate table tmp_Rates2_;
			insert into tmp_Rates2_ select * from tmp_Rates_;

				insert ignore into tmp_Rates_ (OriginationCode ,OriginationDescription,code,description,rate,rateN,ConnectionFee,PreviousRate,AccountID,RateCurrency,ConnectionFeeCurrency)
				select
				distinct
					vr.OriginationCode,
					vr.OriginationDescription,
					vr.Code,
					vr.Description,
					vd.rate,
					vd.rateN,
					vd.ConnectionFee,
					vd.PreviousRate,
					vd.AccountID,
					vd.RateCurrency,
					vd.ConnectionFeeCurrency
				from  tmp_Rates3_ vr
				inner JOIN tmp_Rates2_ vd on  vd.OriginationDescription = vr.OriginationDescription and  vd.Description = vr.Description and vd.OriginationCode != vr.OriginationCode and vd.Code != vr.Code
				where vd.Rate is not null;

		END IF;
		*/


		IF v_LessThenRate > 0 AND v_ChargeRate > 0 THEN

			update tmp_Rates_
			SET Rate = v_ChargeRate
			WHERE  Rate <  v_LessThenRate;

			update tmp_Rates_
			SET RateN = v_ChargeRate
			WHERE  RateN <  v_LessThenRate;

		END IF;


		IF @v_UseVendorCurrencyInRateGenerator_  = 1 THEN

			update tmp_Rates_

			SET
				Rate =
										CASE WHEN  RateCurrency = v_CurrencyID_ THEN
												Rate
										ELSE
											(

												(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = RateCurrency and  CompanyID = v_CompanyId_ )
												* (Rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = v_CurrencyID_ and  CompanyID = v_CompanyId_ ))
											)
										END,

				RateN =
									CASE WHEN  RateCurrency = v_CurrencyID_ THEN
												RateN
										ELSE
											(

												(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = RateCurrency and  CompanyID = v_CompanyId_ )
												* (RateN  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = v_CurrencyID_ and  CompanyID = v_CompanyId_ ))
											)
										END,
				ConnectionFee =
									CASE WHEN  ConnectionFeeCurrency = v_CurrencyID_ THEN
												ConnectionFee
										ELSE
											(

												(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = RateCurrency and  CompanyID = v_CompanyId_ )
												* (ConnectionFee  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = v_CurrencyID_ and  CompanyID = v_CompanyId_ ))
											)
										END
			;

		END IF;







		START TRANSACTION;

		SET @v_RATE_STATUS_AWAITING  = 0;
		SET @v_RATE_STATUS_APPROVED  = 1;
		SET @v_RATE_STATUS_REJECTED  = 2;
		SET @v_RATE_STATUS_DELETE    = 3;

		IF p_RateTableId = -1
		THEN

			INSERT INTO tblRateTable (CompanyId, RateTableName, RateGeneratorID, TrunkID, CodeDeckId,CurrencyID,AppliedTo,Reseller)
			VALUES (v_CompanyId_, p_rateTableName, p_RateGeneratorId, v_trunk_, v_codedeckid_,v_CurrencyID_,v_AppliedTo,v_Reseller);

			SET p_RateTableId = LAST_INSERT_ID();

			IF (@v_RateApprovalProcess_ = 1 ) THEN




							INSERT INTO tblRateTableRate (OriginationRateID,RateID,
																			RateTableId,
																			TimezonesID,
																			Rate,
																			RateN,
																			EffectiveDate,
																			PreviousRate,
																			Interval1,
																			IntervalN,
																			ConnectionFee,
																			ApprovedStatus,
																			VendorID,
																			RateCurrency,
																			ConnectionFeeCurrency
							)
								SELECT DISTINCT
									IFNULL(r.RateID,0) as OriginationRateID,
									tblRate.RateId,
									p_RateTableId,
									v_TimezonesID,
									rate.Rate,
									rate.RateN,
									p_EffectiveDate,
									rate.Rate,
									tblRate.Interval1,
									tblRate.IntervalN,
									rate.ConnectionFee,
									@v_RATE_STATUS_AWAITING as ApprovedStatus,
									rate.AccountID,
									rate.RateCurrency,
									rate.ConnectionFeeCurrency

								FROM tmp_Rates_ rate
									INNER JOIN tblRate
										ON rate.code  = tblRate.Code
									LEFT JOIN tblRate r
										ON rate.OriginationCode  = r.Code AND  r.CodeDeckId = tblRate.CodeDeckId

								WHERE tblRate.CodeDeckId = v_codedeckid_;


			ELSE



				INSERT INTO tblRateTableRate (OriginationRateID,RateID,
																			RateTableId,
																			TimezonesID,
																			Rate,
																			RateN,
																			EffectiveDate,
																			PreviousRate,
																			Interval1,
																			IntervalN,
																			ConnectionFee,
																			ApprovedStatus,
																			VendorID,
																			RateCurrency,
																			ConnectionFeeCurrency
				)
					SELECT DISTINCT
						IFNULL(r.RateID,0) as OriginationRateID,
						tblRate.RateId,
						p_RateTableId,
						v_TimezonesID,
						rate.Rate,
						rate.RateN,
						p_EffectiveDate,
						rate.Rate,
						tblRate.Interval1,
						tblRate.IntervalN,
						rate.ConnectionFee,
						@v_RATE_STATUS_APPROVED as ApprovedStatus,
						rate.AccountID,
						rate.RateCurrency,
						rate.ConnectionFeeCurrency

					FROM tmp_Rates_ rate
						INNER JOIN tblRate
							ON rate.code  = tblRate.Code
						LEFT JOIN tblRate r
							ON rate.OriginationCode  = r.Code AND  r.CodeDeckId = tblRate.CodeDeckId

					WHERE tblRate.CodeDeckId = v_codedeckid_;


			END IF;

		ELSE

			IF p_delete_exiting_rate = 1
			THEN

				IF (@v_RateApprovalProcess_ = 1 ) THEN



							insert into  tblRateTableRateAA (
													OriginationRateID,
													RateID,
													RateTableId,
													TimezonesID,
													Rate,
													RateN,
													EffectiveDate,
													EndDate,
													created_at,
													updated_at,
													CreatedBy,
													ModifiedBy,
													PreviousRate,
													Interval1,
													IntervalN,
													ConnectionFee,
													RoutingCategoryID,
													Preference,
													Blocked,
													ApprovedStatus,
													ApprovedBy,
													ApprovedDate,
													RateCurrency,
													ConnectionFeeCurrency,
													VendorID
												)
							SELECT

												OriginationRateID,
												RateID,
												RateTableId,
												TimezonesID,
												Rate,
												RateN,
												EffectiveDate,
												EndDate,
												created_at,
												updated_at,
												CreatedBy,
												ModifiedBy,
												PreviousRate,
												Interval1,
												IntervalN,
												ConnectionFee,
												RoutingCategoryID,
												Preference,
												Blocked,
												@v_RATE_STATUS_DELETE as ApprovedStatus,
												ApprovedBy,
												ApprovedDate,
												RateCurrency,
												ConnectionFeeCurrency,
												VendorID

							FROM tblRateTableRate

							WHERE RateTableId = p_RateTableId AND TimezonesID = v_TimezonesID;


							CALL prc_ArchiveOldRateTableRateAA(p_RateTableId,v_TimezonesID,CONCAT(p_ModifiedBy,'|RateGenerator'));

				ELSE

					UPDATE
						tblRateTableRate
					SET
						EndDate = NOW()
					WHERE
						tblRateTableRate.RateTableId = p_RateTableId AND tblRateTableRate.TimezonesID = v_TimezonesID;


					CALL prc_ArchiveOldRateTableRate(p_RateTableId,v_TimezonesID,CONCAT(p_ModifiedBy,'|RateGenerator'));


				END IF;


			END IF;


			UPDATE tmp_Rates_ SET EffectiveDate = p_EffectiveDate;


			UPDATE
				tmp_Rates_ tr
			SET
				PreviousRate = (SELECT rtr.Rate FROM tblRateTableRate rtr JOIN tblRate r ON r.RateID=rtr.RateID WHERE rtr.RateTableID=p_RateTableId AND rtr.TimezonesID=v_TimezonesID AND r.Code=tr.Code AND rtr.EffectiveDate<tr.EffectiveDate ORDER BY rtr.EffectiveDate DESC,rtr.RateTableRateID DESC LIMIT 1);

			UPDATE
				tmp_Rates_ tr
			SET
				PreviousRate = (SELECT rtr.Rate FROM tblRateTableRateArchive rtr JOIN tblRate r ON r.RateID=rtr.RateID WHERE rtr.RateTableID=p_RateTableId AND rtr.TimezonesID=v_TimezonesID AND r.Code=tr.Code AND rtr.EffectiveDate<tr.EffectiveDate ORDER BY rtr.EffectiveDate DESC,rtr.RateTableRateID DESC LIMIT 1)
			WHERE
				PreviousRate is null;



			IF v_IncreaseEffectiveDate_ != v_DecreaseEffectiveDate_ THEN

				UPDATE tmp_Rates_
				SET
					tmp_Rates_.EffectiveDate =
					CASE WHEN tmp_Rates_.PreviousRate < tmp_Rates_.Rate THEN
						v_IncreaseEffectiveDate_
					WHEN tmp_Rates_.PreviousRate > tmp_Rates_.Rate THEN
						v_DecreaseEffectiveDate_
					ELSE p_EffectiveDate
					END
				;

			END IF;


			IF (@v_RateApprovalProcess_ = 1 ) THEN





							insert into  tblRateTableRateAA (
													OriginationRateID,
													RateID,
													RateTableId,
													TimezonesID,
													Rate,
													RateN,
													EffectiveDate,
													EndDate,
													created_at,
													updated_at,
													CreatedBy,
													ModifiedBy,
													PreviousRate,
													Interval1,
													IntervalN,
													ConnectionFee,
													RoutingCategoryID,
													Preference,
													Blocked,
													ApprovedStatus,
													ApprovedBy,
													ApprovedDate,
													RateCurrency,
													ConnectionFeeCurrency,
													VendorID
												)
							SELECT

											rtr.OriginationRateID,
											rtr.RateID,
											rtr.RateTableId,
											rtr.TimezonesID,
											rtr.Rate,
											rtr.RateN,
											rtr.EffectiveDate,
											NOW() as EndDate,
											rtr.created_at,
											rtr.updated_at,
											rtr.CreatedBy,
											rtr.ModifiedBy,
											rtr.PreviousRate,
											rtr.Interval1,
											rtr.IntervalN,
											rtr.ConnectionFee,
											rtr.RoutingCategoryID,
											rtr.Preference,
											rtr.Blocked,
											@v_RATE_STATUS_DELETE as ApprovedStatus,
											rtr.ApprovedBy,
											rtr.ApprovedDate,
											rtr.RateCurrency,
											rtr.ConnectionFeeCurrency,
											rtr.VendorID

							FROM tblRateTableRate	rtr

							INNER JOIN
								tblRate ON tblRate.RateId = rtr.RateId
									AND rtr.RateTableId = p_RateTableId

							INNER JOIN
								tmp_Rates_ as rate ON


								rtr.EffectiveDate = p_EffectiveDate


							WHERE
								(
									(p_GroupBy != 'Desc'  AND rate.code = tblRate.Code )

									OR
									(p_GroupBy = 'Desc' AND rate.description = tblRate.description )
								)
								AND
								rtr.TimezonesID = v_TimezonesID AND
								rtr.RateTableId = p_RateTableId AND
								tblRate.CodeDeckId = v_codedeckid_ AND
								rate.rate != rtr.Rate;


				CALL prc_ArchiveOldRateTableRateAA(p_RateTableId,v_TimezonesID,CONCAT(p_ModifiedBy,'|RateGenerator'));


			ELSE



				UPDATE
					tblRateTableRate
				INNER JOIN
					tblRate ON tblRate.RateId = tblRateTableRate.RateId
						AND tblRateTableRate.RateTableId = p_RateTableId

				INNER JOIN
					tmp_Rates_ as rate ON


					tblRateTableRate.EffectiveDate = p_EffectiveDate
				SET
					tblRateTableRate.EndDate = NOW()
				WHERE
					(
						(p_GroupBy != 'Desc'  AND rate.code = tblRate.Code )

						OR
						(p_GroupBy = 'Desc' AND rate.description = tblRate.description )
					)
					AND
					tblRateTableRate.TimezonesID = v_TimezonesID AND
					tblRateTableRate.RateTableId = p_RateTableId AND
					tblRate.CodeDeckId = v_codedeckid_ AND
					rate.rate != tblRateTableRate.Rate;


				CALL prc_ArchiveOldRateTableRate(p_RateTableId,v_TimezonesID,CONCAT(p_ModifiedBy,'|RateGenerator'));

			END IF;


			IF (@v_RateApprovalProcess_ = 1 ) THEN



					INSERT INTO tblRateTableRateAA (
											OriginationRateID,
											RateID,
											RateTableId,
											TimezonesID,
											Rate,
											RateN,
											EffectiveDate,
											PreviousRate,
											Interval1,
											IntervalN,
											ConnectionFee,
											ApprovedStatus,
											VendorID,
											RateCurrency,
											ConnectionFeeCurrency

					)
						SELECT DISTINCT
							IFNULL(r.RateID,0) as OriginationRateID,
							tblRate.RateId,
							p_RateTableId AS RateTableId,
							v_TimezonesID AS TimezonesID,
							rate.Rate,
							rate.RateN,
							rate.EffectiveDate,
							rate.PreviousRate,
							tblRate.Interval1,
							tblRate.IntervalN,
							rate.ConnectionFee,
							@v_RATE_STATUS_AWAITING as ApprovedStatus,
							rate.AccountID,
							rate.RateCurrency,
							rate.ConnectionFeeCurrency

						FROM tmp_Rates_ rate
							INNER JOIN tblRate
								ON rate.code  = tblRate.Code
							LEFT JOIN tblRate r
								ON rate.OriginationCode  = r.Code AND  r.CodeDeckId = tblRate.CodeDeckId

							LEFT JOIN tblRateTableRate tbl1
								ON tblRate.RateId = tbl1.RateId
									 AND tbl1.RateTableId = p_RateTableId
									 AND tbl1.TimezonesID = v_TimezonesID
							LEFT JOIN tblRateTableRate tbl2
								ON tblRate.RateId = tbl2.RateId
									 and tbl2.EffectiveDate = rate.EffectiveDate
									 AND tbl2.RateTableId = p_RateTableId
									 AND tbl2.TimezonesID = v_TimezonesID
						WHERE  (    tbl1.RateTableRateID IS NULL
												OR
												(
													tbl2.RateTableRateID IS NULL
													AND  tbl1.EffectiveDate != rate.EffectiveDate

												)
									 )
									 AND tblRate.CodeDeckId = v_codedeckid_;





						insert into  tblRateTableRateAA (
													OriginationRateID,
													RateID,
													RateTableId,
													TimezonesID,
													Rate,
													RateN,
													EffectiveDate,
													EndDate,
													created_at,
													updated_at,
													CreatedBy,
													ModifiedBy,
													PreviousRate,
													Interval1,
													IntervalN,
													ConnectionFee,
													RoutingCategoryID,
													Preference,
													Blocked,
													ApprovedStatus,
													ApprovedBy,
													ApprovedDate,
													RateCurrency,
													ConnectionFeeCurrency,
													VendorID
												)
					SELECT

											IFNULL(rtr.OriginationRateID,0) as OriginationRateID,
											rtr.RateID,
											rtr.RateTableId,
											rtr.TimezonesID,
											rtr.Rate,
											rtr.RateN,
											rtr.EffectiveDate,
											NOW() as EndDate,
											rtr.created_at,
											rtr.updated_at,
											rtr.CreatedBy,
											rtr.ModifiedBy,
											rtr.PreviousRate,
											rtr.Interval1,
											rtr.IntervalN,
											rtr.ConnectionFee,
											rtr.RoutingCategoryID,
											rtr.Preference,
											rtr.Blocked,
											@v_RATE_STATUS_DELETE as ApprovedStatus,
											rtr.ApprovedBy,
											rtr.ApprovedDate,
											rtr.RateCurrency,
											rtr.ConnectionFeeCurrency,
											rtr.VendorID
					FROM
						tblRateTableRate rtr
					INNER JOIN
						tblRate ON rtr.RateId  = tblRate.RateId
					LEFT JOIN
						tmp_Rates_ rate ON rate.Code=tblRate.Code



					WHERE
						rate.Code is null AND rtr.RateTableId = p_RateTableId AND rtr.TimezonesID = v_TimezonesID AND rtr.EffectiveDate = rate.EffectiveDate AND tblRate.CodeDeckId = v_codedeckid_;



					CALL prc_ArchiveOldRateTableRateAA(p_RateTableId,v_TimezonesID,CONCAT(p_ModifiedBy,'|RateGenerator'));

			ELSE


					INSERT INTO tblRateTableRate (OriginationRateID, RateID,
																				RateTableId,
																				TimezonesID,
																				Rate,
																				RateN,
																				EffectiveDate,
																				PreviousRate,
																				Interval1,
																				IntervalN,
																				ConnectionFee,
																				ApprovedStatus,
																				VendorID,
																				RateCurrency,
																				ConnectionFeeCurrency

					)
						SELECT DISTINCT
							IFNULL(r.RateID,0) as OriginationRateID,
							tblRate.RateId,
							p_RateTableId AS RateTableId,
							v_TimezonesID AS TimezonesID,
							rate.Rate,
							rate.RateN,
							rate.EffectiveDate,
							rate.PreviousRate,
							tblRate.Interval1,
							tblRate.IntervalN,
							rate.ConnectionFee,
							@v_RATE_STATUS_APPROVED as ApprovedStatus,
							rate.AccountID,
							rate.RateCurrency,
							rate.ConnectionFeeCurrency

						FROM tmp_Rates_ rate
							INNER JOIN tblRate
								ON rate.code  = tblRate.Code
							LEFT JOIN tblRate r
								ON rate.OriginationCode  = r.Code AND  r.CodeDeckId = tblRate.CodeDeckId

							LEFT JOIN tblRateTableRate tbl1
								ON tblRate.RateId = tbl1.RateId
									 AND tbl1.RateTableId = p_RateTableId
									 AND tbl1.TimezonesID = v_TimezonesID
							LEFT JOIN tblRateTableRate tbl2
								ON tblRate.RateId = tbl2.RateId
									 and tbl2.EffectiveDate = rate.EffectiveDate
									 AND tbl2.RateTableId = p_RateTableId
									 AND tbl2.TimezonesID = v_TimezonesID
						WHERE  (    tbl1.RateTableRateID IS NULL
												OR
												(
													tbl2.RateTableRateID IS NULL
													AND  tbl1.EffectiveDate != rate.EffectiveDate

												)
									 )
									 AND tblRate.CodeDeckId = v_codedeckid_;


					UPDATE
						tblRateTableRate rtr
					INNER JOIN
						tblRate ON rtr.RateId  = tblRate.RateId
					LEFT JOIN
						tmp_Rates_ rate ON rate.Code=tblRate.Code
					SET
						rtr.EndDate = NOW()
					WHERE
						rate.Code is null AND rtr.RateTableId = p_RateTableId AND rtr.TimezonesID = v_TimezonesID AND rtr.EffectiveDate = rate.EffectiveDate AND tblRate.CodeDeckId = v_codedeckid_;









					CALL prc_ArchiveOldRateTableRate(p_RateTableId,v_TimezonesID,CONCAT(p_ModifiedBy,'|RateGenerator'));


			END IF;


		END IF;


		IF (@v_RateApprovalProcess_ = 1 ) THEN


				DROP TEMPORARY TABLE IF EXISTS tmp_ALL_RateTableRate_;
				CREATE TEMPORARY TABLE IF NOT EXISTS tmp_ALL_RateTableRate_ AS (SELECT * FROM tblRateTableRateAA WHERE RateTableID=p_RateTableId AND TimezonesID=v_TimezonesID);


				UPDATE
					tmp_ALL_RateTableRate_ temp
				SET
					EndDate = (SELECT EffectiveDate FROM tblRateTableRateAA rtr WHERE rtr.RateTableID=p_RateTableId AND rtr.TimezonesID=v_TimezonesID AND rtr.RateID=temp.RateID AND rtr.EffectiveDate>temp.EffectiveDate ORDER BY rtr.EffectiveDate ASC,rtr.RateTableRateID ASC LIMIT 1)
				WHERE
					temp.RateTableId = p_RateTableId AND temp.TimezonesID = v_TimezonesID;

				UPDATE
					tblRateTableRateAA rtr
				INNER JOIN
					tmp_ALL_RateTableRate_ temp ON rtr.RateTableRateID=temp.RateTableRateID AND rtr.TimezonesID=temp.TimezonesID
				SET
					rtr.EndDate=temp.EndDate,
					rtr.ApprovedStatus = IFNULL(@v_RateApprovalProcess_,1)
				WHERE
					rtr.RateTableId=p_RateTableId AND
					rtr.TimezonesID=v_TimezonesID;



				CALL prc_ArchiveOldRateTableRateAA(p_RateTableId,v_TimezonesID,CONCAT(p_ModifiedBy,'|RateGenerator'));



		ELSE

				DROP TEMPORARY TABLE IF EXISTS tmp_ALL_RateTableRate_;
				CREATE TEMPORARY TABLE IF NOT EXISTS tmp_ALL_RateTableRate_ AS (SELECT * FROM tblRateTableRate WHERE RateTableID=p_RateTableId AND TimezonesID=v_TimezonesID);


				UPDATE
					tmp_ALL_RateTableRate_ temp
				SET
					EndDate = (SELECT EffectiveDate FROM tblRateTableRate rtr WHERE rtr.RateTableID=p_RateTableId AND rtr.TimezonesID=v_TimezonesID AND rtr.RateID=temp.RateID AND rtr.EffectiveDate>temp.EffectiveDate ORDER BY rtr.EffectiveDate ASC,rtr.RateTableRateID ASC LIMIT 1)
				WHERE
					temp.RateTableId = p_RateTableId AND temp.TimezonesID = v_TimezonesID;

				UPDATE
					tblRateTableRate rtr
				INNER JOIN
					tmp_ALL_RateTableRate_ temp ON rtr.RateTableRateID=temp.RateTableRateID AND rtr.TimezonesID=temp.TimezonesID
				SET
					rtr.EndDate=temp.EndDate,
					rtr.ApprovedStatus = IFNULL(@v_RateApprovalProcess_,1)
				WHERE
					rtr.RateTableId=p_RateTableId AND
					rtr.TimezonesID=v_TimezonesID;



				CALL prc_ArchiveOldRateTableRate(p_RateTableId,v_TimezonesID,CONCAT(p_ModifiedBy,'|RateGenerator'));


		END IF;


		UPDATE tblRateTable
		SET RateGeneratorID = p_RateGeneratorId,
			TrunkID = v_trunk_,
			CodeDeckId = v_codedeckid_,
			updated_at = now()
		WHERE RateTableID = p_RateTableId;


		INSERT INTO tmp_JobLog_ (Message) VALUES (p_RateTableId);


		SELECT * FROM tmp_JobLog_;

		COMMIT;


		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;



	END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_WSGenerateRateTablePkg`;
DELIMITER //
CREATE PROCEDURE `prc_WSGenerateRateTablePkg`(
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
			TimezonesID int,
			PackageID VARCHAR(50),
			`Order` INT,
			RowNo INT
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_RateGeneratorCalculatedRate_;
		CREATE TEMPORARY TABLE tmp_RateGeneratorCalculatedRate_  (
			CalculatedRateID INT,
			Component VARCHAR(50),
			TimezonesID int,
			RateLessThen	double(18,4),
			ChangeRateTo double(18,4),
			PackageID VARCHAR(50),
			RowNo INT
		);



		DROP TEMPORARY TABLE IF EXISTS tmp_table_with_origination;
		CREATE TEMPORARY TABLE tmp_table_with_origination (
				RateTableID int,
				TimezonesID  int,
				TimezoneTitle  varchar(100),
				CodeDeckId int,
				Code varchar(100),
				OriginationCode  varchar(100),
				VendorID int,
				VendorName varchar(200),
				EndDate datetime,
				PackageID VARCHAR(50),
				OneOffCost double(18,4),
				MonthlyCost double(18,4),
				PackageCostPerMinute  double(18,4),
				RecordingCostPerMinute  double(18,4),


				OneOffCostCurrency int,
				MonthlyCostCurrency int,
				PackageCostPerMinuteCurrency int,
				RecordingCostPerMinuteCurrency int,


				Total1 double(18,4),
				Total double(18,4)
			);



		DROP TEMPORARY TABLE IF EXISTS tmp_tblRateTablePKGRate;
		CREATE TEMPORARY TABLE tmp_tblRateTablePKGRate (
				RateTableID int,
				TimezonesID  int,
				TimezoneTitle  varchar(100),
				CodeDeckId int,
				PackageID VARCHAR(50),
				Code  varchar(100),
				VendorID int,
				VendorName varchar(200),
				EndDate datetime,
				OneOffCost DECIMAL(18,6) NULL DEFAULT NULL,
	MonthlyCost DECIMAL(18,6) NULL DEFAULT NULL,
	PackageCostPerMinute DECIMAL(18,6) NULL DEFAULT NULL,
	RecordingCostPerMinute DECIMAL(18,6) NULL DEFAULT NULL,
	OneOffCostCurrency INT(11) NULL DEFAULT NULL,
	MonthlyCostCurrency INT(11) NULL DEFAULT NULL,
	PackageCostPerMinuteCurrency INT(11) NULL DEFAULT NULL,
	RecordingCostPerMinuteCurrency INT(11) NULL DEFAULT NULL,


				Total double(18,4)
			);

		DROP TEMPORARY TABLE IF EXISTS tmp_SelectedVendortblRateTablePKGRate;
		CREATE TEMPORARY TABLE tmp_SelectedVendortblRateTablePKGRate (
				RateTableID int,
				TimezonesID  int,
				TimezoneTitle  varchar(100),
				CodeDeckId int,
				PackageID VARCHAR(50),
				Code varchar(100),
				OriginationCode  varchar(100),
				VendorID int,
				VendorName varchar(200),
				EndDate datetime,
				OneOffCost DECIMAL(18,6) NULL DEFAULT NULL,
	MonthlyCost DECIMAL(18,6) NULL DEFAULT NULL,
	PackageCostPerMinute DECIMAL(18,6) NULL DEFAULT NULL,
	RecordingCostPerMinute DECIMAL(18,6) NULL DEFAULT NULL,
	OneOffCostCurrency INT(11) NULL DEFAULT NULL,
	MonthlyCostCurrency INT(11) NULL DEFAULT NULL,
	PackageCostPerMinuteCurrency INT(11) NULL DEFAULT NULL,
	RecordingCostPerMinuteCurrency INT(11) NULL DEFAULT NULL,



				new_OneOffCost DECIMAL(18,6) NULL DEFAULT NULL,
	new_MonthlyCost DECIMAL(18,6) NULL DEFAULT NULL,
	new_PackageCostPerMinute DECIMAL(18,6) NULL DEFAULT NULL,
	new_RecordingCostPerMinute DECIMAL(18,6) NULL DEFAULT NULL

			);

			DROP TEMPORARY TABLE IF EXISTS tmp_vendor_position;
			CREATE TEMPORARY TABLE tmp_vendor_position (
				VendorID int,
				vPosition int,
				Total double(18,4),
				Package int

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

		DROP TEMPORARY TABLE IF EXISTS tmp_tblVendorConnection;
			CREATE TEMPORARY TABLE tmp_tblVendorConnection (
				AccountID int,
				RateTableId int
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



		 SELECT CurrencyID,PackageID,CompanyID INTO @v_RateGenatorCurrencyID_,@v_RateGenartorPackageID,@v_RateGenatorCompanyID FROM  tblRateGenerator WHERE RateGeneratorId = @p_RateGeneratorId;
		SET @p_EffectiveDate = p_EffectiveDate;




		IF @v_RateGenartorPackageID = 0 THEN

		SELECT
			rateposition,
			rateGen.companyid ,
			rateGen.RateGeneratorName,
			rateGen.RateGeneratorId,
			rateGen.CurrencyID,

			rateGen.Calls,
			rateGen.Minutes,
			rateGen.DateFrom,
			rateGen.DateTo,
			rateGen.TimezonesID,
			rateGen.TimezonesPercentage,
			0,
			IF( percentageRate = '' OR percentageRate is null	,0, percentageRate ),rateGen.SelectType,
			IFNULL(AppliedTo,''),
			IFNULL(Reseller,'')

			INTO @v_RatePosition_, @v_CompanyId_,   @v_RateGeneratorName_,@p_RateGeneratorId, @v_CurrencyID_,

			@v_Calls,
			@v_Minutes,
			@v_StartDate_ ,@v_EndDate_ ,@v_TimezonesID, @v_TimezonesPercentage,@v_PackageID,
			@v_percentageRate_,@v_PackageType ,
			@p_AppliedTo,
			@p_Reseller
		FROM tblRateGenerator rateGen
		WHERE RateGeneratorId = @p_RateGeneratorId;

	ELSE

	SELECT
			rateposition,
			rateGen.companyid ,
			rateGen.RateGeneratorName,
			rateGen.RateGeneratorId,
			rateGen.CurrencyID,

			rateGen.Calls,
			rateGen.Minutes,
			rateGen.DateFrom,
			rateGen.DateTo,
			rateGen.TimezonesID,
			rateGen.TimezonesPercentage,
			IF(rateGen.PackageID = 0, "1", IFNULL(st.Name,'')),
			IF( percentageRate = '' OR percentageRate is null	,0, percentageRate ),rateGen.SelectType
			INTO @v_RatePosition_, @v_CompanyId_,   @v_RateGeneratorName_,@p_RateGeneratorId, @v_CurrencyID_,

			@v_Calls,
			@v_Minutes,
			@v_StartDate_ ,@v_EndDate_ ,@v_TimezonesID, @v_TimezonesPercentage,@v_PackageID,
			@v_percentageRate_,@v_PackageType
		FROM tblRateGenerator rateGen INNER JOIN tblPackage st ON  st.PackageId =  rateGen.PackageID
		WHERE RateGeneratorId = @p_RateGeneratorId;

	END IF;





		select CodeDeckId INTO @v_CodeDeckId from tblCodeDeck where CompanyId = @v_CompanyId_ and CodeDeckName = 'Default Codedeck';

		SELECT CurrencyId INTO @v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = @v_CompanyId_;

		SELECT IFNULL(Value,1) INTO @v_RateApprovalProcess_ FROM tblCompanySetting WHERE CompanyID = @v_CompanyId_ AND `Key`='RateApprovalProcess';

		SET @v_RateApprovalStatus_ = 0;
		IF @v_RateApprovalProcess_ = 1 THEN
			SET @v_RateApprovalStatus_ = 0;
		END IF;
		IF @v_RateApprovalProcess_ = 0 THEN
			SET @v_RateApprovalStatus_ = 1;
		END IF;

		INSERT INTO tmp_Raterules_(
			rateruleid ,
			Component,

			TimezonesID ,
			PackageID,
			`Order` ,
			RowNo
		)
			SELECT
				rateruleid,
				Component,

				TimeOfDay as TimezonesID,
				IF(Package = '' OR Package is null, "0", Package),
				`Order`,
				@row_num := @row_num+1 AS RowID
			FROM tblRateRule,(SELECT @row_num := 0) x
			WHERE rategeneratorid = @p_RateGeneratorId
			ORDER BY `Order` ASC;



		INSERT INTO tmp_RateGeneratorCalculatedRate_
			(
			CalculatedRateID ,
			Component ,

			TimezonesID ,
			RateLessThen,
			ChangeRateTo ,
			PackageID,
			RowNo )
			SELECT

			CalculatedRateID ,
			Component ,

			TimezonesID ,
			RateLessThen	,
			ChangeRateTo ,
			IF(Package = '', "0", Package),
			@row_num := @row_num+1 AS RowID
			FROM tblRateGeneratorCalculatedRate,(SELECT @row_num := 0) x
			WHERE RateGeneratorId = @p_RateGeneratorId
			ORDER BY CalculatedRateID ASC;



				set @v_ApprovedStatus = 1;

				set @v_PKGType = 3;

			  	set @v_AppliedToCustomer = 1;
				set @v_AppliedToVendor = 2;
				set @v_AppliedToReseller = 3;





			SET @p_Calls	 							 = @v_Calls;
			SET @p_Minutes	 							 = @v_Minutes;
			SET @v_PeakTimeZoneID	 				 = @v_TimezonesID;
			SET @p_PeakTimeZonePercentage	 		 = @v_TimezonesPercentage;
			SET @p_MobileOriginationPercentage	 = @v_OriginationPercentage ;

			SET @p_Prefix = TRIM(LEADING '0' FROM @p_Prefix);



			IF @p_Calls = 0 AND @p_Minutes = 0 THEN



				select count(UsageDetailID)  into @p_Calls

				from speakintelligentCDR.tblUsageDetails  d

				inner join speakintelligentCDR.tblUsageHeader h on d.UsageHeaderID = h.UsageHeaderID
				where CompanyID = @v_CompanyId_ AND StartDate >= @v_StartDate_ AND StartDate <= @v_EndDate_ and d.is_inbound = 1;



				insert into tmp_timezone_minutes (TimezonesID, minutes)

				select TimezonesID as TimezonesID , (sum(billed_duration) / 60) as minutes

				from speakintelligentCDR.tblUsageDetails  d
				inner join speakintelligentCDR.tblUsageHeader h on d.UsageHeaderID = h.UsageHeaderID
				where CompanyID = @v_CompanyId_ AND StartDate >= @v_StartDate_ AND StartDate <= @v_EndDate_ and d.is_inbound = 1 and TimezonesID is not null
				group by TimezonesID;






			SET @v_timeZoneCount = ( SELECT COUNT(*) FROM tmp_timezone_minutes );
			if @v_timeZoneCount = 0 THEN
				insert into tmp_timezone_minutes (TimezonesID, minutes) values (1,1);
			end if;


			ELSE




				SET @p_MobileOrigination				 = @v_Origination ;
				SET @v_PeakTimeZoneMinutes				 =  ( (@p_Minutes/ 100) * @p_PeakTimeZonePercentage ) 	;
				SET @v_MinutesFromMobileOrigination  =  ( (@p_Minutes/ 100) * @p_MobileOriginationPercentage ) 	;



				insert into tmp_timezones (TimezonesID) select TimezonesID from 	tblTimezones;

				insert into tmp_timezone_minutes (TimezonesID, minutes) select @v_TimezonesID, @v_PeakTimeZoneMinutes as minutes;

				SET @v_RemainingTimezones = (select count(*) from tmp_timezones where TimezonesID != @v_TimezonesID);
				SET @v_RemainingMinutes = (@p_Minutes - @v_PeakTimeZoneMinutes) / @v_RemainingTimezones ;

				SET @v_pointer_ = 1;
				SET @v_rowCount_ = ( SELECT COUNT(*) FROM tmp_timezones );

				WHILE @v_pointer_ <= @v_rowCount_
				DO

						SET @v_NewTimezonesID = (SELECT TimezonesID FROM tmp_timezones WHERE ID = @v_pointer_ AND TimezonesID != @v_TimezonesID );

						if @v_NewTimezonesID > 0  THEN

							insert into tmp_timezone_minutes (TimezonesID, minutes)  select @v_NewTimezonesID, @v_RemainingMinutes as minutes;

						END IF ;

					SET @v_pointer_ = @v_pointer_ + 1;

				END WHILE;







		END IF;



		SET @v_days =    TIMESTAMPDIFF(DAY, (SELECT @v_StartDate_), (SELECT @v_EndDate_)) + 1 ;
		SET @v_period1 =      IF(MONTH((SELECT @v_StartDate_)) = MONTH((SELECT @v_EndDate_)), 0, (TIMESTAMPDIFF(DAY, (SELECT @v_StartDate_), LAST_DAY((SELECT @v_StartDate_)) + INTERVAL 1 DAY)) / DAY(LAST_DAY((SELECT @v_StartDate_))));
		SET @v_period2 =      TIMESTAMPDIFF(MONTH, LAST_DAY((SELECT @v_StartDate_)) + INTERVAL 1 DAY, LAST_DAY((SELECT @v_EndDate_))) ;
		SET @v_period3 =      IF(MONTH((SELECT @v_StartDate_)) = MONTH((SELECT @v_EndDate_)), (SELECT @v_days), DAY((SELECT @v_EndDate_))) / DAY(LAST_DAY((SELECT @v_EndDate_)));
		SET @v_months =     (SELECT @v_period1) + (SELECT @v_period2) + (SELECT @v_period3);


		insert into tmp_timezone_minutes_2 (TimezonesID, minutes) select TimezonesID, minutes from tmp_timezone_minutes;
		insert into tmp_timezone_minutes_3 (TimezonesID, minutes) select TimezonesID, minutes from tmp_timezone_minutes;



	IF @v_RateGenartorPackageID = 0 THEN



										insert into tmp_table_with_origination (
																RateTableID,
																TimezonesID,
																TimezoneTitle,
																CodeDeckId,
																PackageID,
																Code,

																VendorID,
																VendorName,
																EndDate,
																OneOffCost,
																MonthlyCost,
																PackageCostPerMinute,
																RecordingCostPerMinute,

																OneOffCostCurrency,
																MonthlyCostCurrency,
																PackageCostPerMinuteCurrency,
																RecordingCostPerMinuteCurrency,

																Total1,
																Total
																)

	select
								rt.RateTableID,
								drtr.TimezonesID,
								t.Title as TimezoneTitle,
								r.CodeDeckId,
								(select PackageId from tblPackage where Name =r.Code and CompanyID = @v_RateGenatorCompanyID) ,
								r.Code,

								a.AccountID,
								a.AccountName,
								drtr.EndDate,
								@OneOffCost := CASE WHEN ( OneOffCostCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = OneOffCostCurrency THEN
									drtr.OneOffCost
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_  )
									* (drtr.OneOffCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = OneOffCostCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.OneOffCost
								ELSE
									(

										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.OneOffCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as OneOffCost,
								@MonthlyCost := ( ( CASE WHEN ( MonthlyCostCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = MonthlyCostCurrency THEN
									drtr.MonthlyCost
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_  )
									* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = MonthlyCostCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.MonthlyCost
								ELSE
									(

										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END) * @v_months) as MonthlyCost,

								@PackageCostPerMinute := CASE WHEN ( PackageCostPerMinuteCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = PackageCostPerMinuteCurrency THEN
									drtr.PackageCostPerMinute
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.PackageCostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = PackageCostPerMinuteCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.PackageCostPerMinute
								ELSE
									(

										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.PackageCostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as PackageCostPerMinute,

								@RecordingCostPerMinute := CASE WHEN ( RecordingCostPerMinuteCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = RecordingCostPerMinuteCurrency THEN
									drtr.RecordingCostPerMinute
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.RecordingCostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = RecordingCostPerMinuteCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.RecordingCostPerMinute
								ELSE
									(

										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.RecordingCostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as RecordingCostPerMinute,




								OneOffCostCurrency,
								MonthlyCostCurrency,
								PackageCostPerMinuteCurrency,
								RecordingCostPerMinuteCurrency,






							 @Total1 := (
									(	IFNULL(@MonthlyCost,0) 				)				+
									(IFNULL(@PackageCostPerMinute,0) * IFNULL(tom.minutes,0))	+
											+
									(IFNULL(@RecordingCostPerMinute,0) * IFNULL(tom.minutes,0))


								) as Total1,

								@Total := @Total1 as Total


				from tblRateTablePKGRate  drtr
				inner join tblRateTable  rt on rt.RateTableId = drtr.RateTableId
				 inner join tblVendorConnection vc on vc.RateTableID = rt.RateTableId and vc.CompanyID = rt.CompanyId  and vc.Active=1
				inner join tblAccount a on vc.AccountId = a.AccountID and rt.CompanyId = a.CompanyId
				inner join tblRate r on drtr.RateID = r.RateID and r.CompanyID = vc.CompanyID




				inner join tblTimezones t on t.TimezonesID =  drtr.TimezonesID
				left join tmp_timezone_minutes tom on tom.TimezonesID = t.TimezonesID
				WHERE
								rt.CompanyId =  @v_CompanyId_
								AND rt.Type = @v_PackageType
								AND rt.AppliedTo = @v_AppliedToVendor
								and (
							 (p_EffectiveRate = 'now' AND EffectiveDate <= NOW())
					 OR
					 (p_EffectiveRate = 'future' AND EffectiveDate > NOW()  )
					 OR
					 (	 p_EffectiveRate = 'effective' AND EffectiveDate <= @p_EffectiveDate
							 AND ( drtr.EndDate IS NULL OR (drtr.EndDate > DATE(@p_EffectiveDate)) )
					 )
				)
								 ;

	ELSE

			insert into tmp_table_with_origination (
																RateTableID,
																TimezonesID,
																TimezoneTitle,
																CodeDeckId,
																PackageID,
																Code,

																VendorID,
																VendorName,
																EndDate,
																OneOffCost,
																MonthlyCost,
																PackageCostPerMinute,
																RecordingCostPerMinute,

																OneOffCostCurrency,
																MonthlyCostCurrency,
																PackageCostPerMinuteCurrency,
																RecordingCostPerMinuteCurrency,

																Total1,
																Total
																)

	select
								rt.RateTableID,
								drtr.TimezonesID,
								t.Title as TimezoneTitle,
								r.CodeDeckId,
								st.PackageId,
								r.Code,

								a.AccountID,
								a.AccountName,
								drtr.EndDate,
								@OneOffCost := CASE WHEN ( OneOffCostCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = OneOffCostCurrency THEN
									drtr.OneOffCost
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_  )
									* (drtr.OneOffCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = OneOffCostCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.OneOffCost
								ELSE
									(

										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.OneOffCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as OneOffCost,
								@MonthlyCost := ( ( CASE WHEN ( MonthlyCostCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = MonthlyCostCurrency THEN
									drtr.MonthlyCost
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_  )
									* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = MonthlyCostCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.MonthlyCost
								ELSE
									(

										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END) * @v_months) as MonthlyCost,

								@PackageCostPerMinute := CASE WHEN ( PackageCostPerMinuteCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = PackageCostPerMinuteCurrency THEN
									drtr.PackageCostPerMinute
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.PackageCostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = PackageCostPerMinuteCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.PackageCostPerMinute
								ELSE
									(

										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.PackageCostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as PackageCostPerMinute,

								@RecordingCostPerMinute := CASE WHEN ( RecordingCostPerMinuteCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = RecordingCostPerMinuteCurrency THEN
									drtr.RecordingCostPerMinute
								ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.RecordingCostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = RecordingCostPerMinuteCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.RecordingCostPerMinute
								ELSE
									(

										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.RecordingCostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as RecordingCostPerMinute,




								OneOffCostCurrency,
								MonthlyCostCurrency,
								PackageCostPerMinuteCurrency,
								RecordingCostPerMinuteCurrency,






							 @Total1 := (
									(	IFNULL(@MonthlyCost,0) 				)				+
									(IFNULL(@PackageCostPerMinute,0) * IFNULL(tom.minutes,0))	+
											+
									(IFNULL(@RecordingCostPerMinute,0) * IFNULL(tom.minutes,0))


								) as Total1,

								@Total := @Total1 as Total


				from tblRateTablePKGRate  drtr
				inner join tblRateTable  rt on rt.RateTableId = drtr.RateTableId
				 inner join tblVendorConnection vc on vc.RateTableID = rt.RateTableId and vc.CompanyID = rt.CompanyId  and vc.Active=1
				inner join tblAccount a on vc.AccountId = a.AccountID and rt.CompanyId = a.CompanyId
				inner join tblRate r on drtr.RateID = r.RateID and r.CompanyID = vc.CompanyID
				INNER JOIN tblPackage st ON st.CompanyID = r.CompanyID AND st.Name = @v_PackageID AND  r.Code = st.Name



				inner join tblTimezones t on t.TimezonesID =  drtr.TimezonesID
				left join tmp_timezone_minutes tom on tom.TimezonesID = t.TimezonesID
				WHERE
								rt.CompanyId =  @v_CompanyId_
								AND rt.Type = @v_PackageType
								AND rt.AppliedTo = @v_AppliedToVendor
								and (
							 (p_EffectiveRate = 'now' AND EffectiveDate <= NOW())
					 OR
					 (p_EffectiveRate = 'future' AND EffectiveDate > NOW()  )
					 OR
					 (	 p_EffectiveRate = 'effective' AND EffectiveDate <= @p_EffectiveDate
							 AND ( drtr.EndDate IS NULL OR (drtr.EndDate > DATE(@p_EffectiveDate)) )
					 )
				)
								 ;

	END IF;



		-- select * from tmp_table_with_origination;

				insert into tmp_tblRateTablePKGRate (
										RateTableID,
										TimezonesID,
										TimezoneTitle,
										CodeDeckId,
										PackageID,
										Code,

										VendorID,
										VendorName,
										EndDate,
										OneOffCost,
										MonthlyCost,
										PackageCostPerMinute,
	RecordingCostPerMinute,
	OneOffCostCurrency,
	MonthlyCostCurrency,
	PackageCostPerMinuteCurrency,
	RecordingCostPerMinuteCurrency,

										Total
										)

										select
										RateTableID,
										TimezonesID,
										TimezoneTitle,
										CodeDeckId,
										PackageID,
										Code,

										VendorID,
										VendorName,
										EndDate,
										OneOffCost,
										MonthlyCost,
										PackageCostPerMinute,
	RecordingCostPerMinute,
	OneOffCostCurrency,
	MonthlyCostCurrency,
	PackageCostPerMinuteCurrency,
	RecordingCostPerMinuteCurrency,
										Total
										from
												tmp_table_with_origination


										where Total is not null;



		-- select * from tmp_tblRateTablePKGRate;


			insert into tmp_vendor_position (VendorID , vPosition,Total,Package)
			select
			VendorID , vPosition,Total,Package
			from (

				SELECT
					distinct
					v.VendorID,
					v.Total,
					v.Package,
					@rank := ( CASE WHEN(@prev_VendorID != v.VendorID and @prev_Total <= v.Total AND (@v_percentageRate_ = 0 OR  (IFNULL(@prev_Total,0) != 0 and  @v_percentageRate_ > 0 AND ROUND(((v.Total - @prev_Total) /( @prev_Total * 100)),2) > @v_percentageRate_) )   )
						THEN  @rank + 1
										 ELSE 1
										 END
					) AS vPosition,
					@prev_VendorID := v.VendorID,
					@prev_Total := v.Total

				FROM (

						select distinct  VendorID , sum(Total) as Total,PackageID as Package  from tmp_tblRateTablePKGRate group by VendorID,PackageID
					) v
					, (SELECT  @prev_VendorID := NUll ,  @rank := 0 ,  @prev_Total := 0 ) f

				order by v.Total,v.VendorID asc
			) tmp
			where vPosition <= @v_RatePosition_;

			insert into tmp_vendor_position (VendorID , vPosition,Total,Package)
			select
			VendorID , vPosition,Total,Package
			from (

				SELECT
					distinct
					v.VendorID,
					v.Total,
					v.Package,
					@rank := ( CASE WHEN(@prev_VendorID != v.VendorID and @prev_Total <= v.Total AND (@v_percentageRate_ = 0 OR  (IFNULL(@prev_Total,0) != 0 and  @v_percentageRate_ > 0 AND ROUND(((v.Total - @prev_Total) /( @prev_Total * 100)),2) > @v_percentageRate_) )   )
						THEN  @rank + 1
										 ELSE 1
										 END
					) AS vPosition,
					@prev_VendorID := v.VendorID,
					@prev_Total := v.Total

				FROM (

						select distinct  VendorID , sum(Total) as Total,0 as Package  from tmp_tblRateTablePKGRate group by VendorID
					) v
					, (SELECT  @prev_VendorID := NUll ,  @rank := 0 ,  @prev_Total := 0 ) f

				order by v.Total,v.VendorID asc
			) tmp
			where vPosition <= @v_RatePosition_;

			SET @v_SelectedVendor = ( select VendorID from tmp_vendor_position where vPosition <= @v_RatePosition_ order by vPosition , Total  limit 1 );


	--	 select * from tmp_vendor_position;
	--	 select @v_SelectedVendor;



	insert into tmp_SelectedVendortblRateTablePKGRate
			(
					RateTableID,
					TimezonesID,
					TimezoneTitle,
					Code,

					VendorID,
					CodeDeckId,
					PackageID,
					VendorName,
					EndDate,
					OneOffCost,
										MonthlyCost,
										PackageCostPerMinute,
	RecordingCostPerMinute,
	OneOffCostCurrency,
	MonthlyCostCurrency,
	PackageCostPerMinuteCurrency,
	RecordingCostPerMinuteCurrency

			)
			select
					RateTableID,
					TimezonesID,
					TimezoneTitle,
					Code,

					VendorID,
					CodeDeckId,
					PackageID,
					VendorName,
					EndDate,

					IFNULL(OneOffCost,0),
					IFNULL(MonthlyCost,0),
					IFNULL(PackageCostPerMinute,0),
					IFNULL(RecordingCostPerMinute,0),


					OneOffCostCurrency,
	MonthlyCostCurrency,
	PackageCostPerMinuteCurrency,
	RecordingCostPerMinuteCurrency

			from tmp_tblRateTablePKGRate

			where VendorID = @v_SelectedVendor ;



		-- select * from tmp_SelectedVendortblRateTablePKGRate;



			DROP TEMPORARY TABLE IF EXISTS tmp_MergeComponents;
			CREATE TEMPORARY TABLE tmp_MergeComponents(
				ID int auto_increment,
				Component TEXT  ,
				TimezonesID INT(11)   ,
				ToTimezonesID INT(11)   ,
				Action CHAR(4)    ,
				MergeTo TEXT  ,
				Package INT(11)   ,

				primary key (ID)
			);

			insert into tmp_MergeComponents (
									Component,
									TimezonesID,
									ToTimezonesID,
									Action,
									MergeTo,
									Package


			)
			select
									Component,
									TimezonesID,
									ToTimezonesID,
									Action,
									MergeTo,
									IF(Package = '' OR Package is null, "0", Package)


			from tblRateGeneratorCostComponent
			where RateGeneratorId = @p_RateGeneratorId
			order by CostComponentID asc;


	--	select * from tmp_MergeComponents;
	--	select * from tmp_SelectedVendortblRateTablePKGRate;


	 	SET @v_pointer_ = 1;
		SET @v_rowCount_ = ( SELECT COUNT(*) FROM tmp_MergeComponents );
		SET @v_MOneOffCost = '';
		SET @v_MMonthlyCost = '';
		SET @v_MPackageCostPerMinute = '';
		SET @v_MRecordingCostPerMinute = '';
		SET @v_costMergeCompnents = '';


		WHILE @v_pointer_ <= @v_rowCount_
		DO
		SET @v_MOneOffCost = '';
		SET @v_MMonthlyCost = '';
		SET @v_MPackageCostPerMinute = '';
		SET @v_MRecordingCostPerMinute = '';
		SET @v_costMergeCompnents = '';

				SELECT
						Component,
						TimezonesID,
						ToTimezonesID,
						Action,
						MergeTo,
						Package


				INTO

						@v_Component,
						@v_TimezonesID,
						@v_ToTimezonesID,
						@v_Action,
						@v_MergeTo,
						@v_MergePackage


				FROM tmp_MergeComponents WHERE ID = @v_pointer_;

				IF @v_Action = 'sum' THEN

					SET @ResultField = concat('(' ,  REPLACE(@v_Component,',',' + ') , ') ');


				ELSE


					if INSTR(@v_Component,',') > 0 then
					if INSTR(@v_Component,'OneOffCost') THEN
						SET @v_MOneOffCost = 'OneOffCost';

					END IF;
					if INSTR(@v_Component,'MonthlyCost') THEN
						SET @v_MMonthlyCost = 'MonthlyCost';

					END IF;
					if INSTR(@v_Component,'PackageCostPerMinute') THEN
						SET @v_MPackageCostPerMinute = 'PackageCostPerMinute';

					END IF;
					if INSTR(@v_Component,'RecordingCostPerMinute') THEN
						SET @v_MRecordingCostPerMinute = 'RecordingCostPerMinute';

					END IF;
					select GREATEST(@v_MOneOffCost,@v_MMonthlyCost,@v_MPackageCostPerMinute,@v_MRecordingCostPerMinute) into @ResultField;

					ELSE
						SET @ResultField = @v_Component;
					end if;

				END IF;



				IF @v_MergePackage = 0 THEN

				SET @v_SelectedVendor = ( select VendorID from tmp_vendor_position where vPosition <= @v_RatePosition_ and Package=@v_MergePackage order by vPosition , Total  limit 1 );

				SET @stm1 = CONCAT('
						update tmp_SelectedVendortblRateTablePKGRate srt
						inner join (

								select

									TimezonesID,
									Code,

									', @ResultField , ' as componentValue

									from tmp_tblRateTablePKGRate


								where
									VendorID = @v_SelectedVendor

								AND (  @v_TimezonesID = "" OR  TimezonesID = @v_TimezonesID)





						) tmp on
								  @v_ToTimezonesID = "" OR  srt.TimezonesID = @v_ToTimezonesID

						set

						' , 'new_', @v_MergeTo , ' = tmp.componentValue;
				');

			--	select @stm1;
					if @stm1 is not null then
						PREPARE stm1 FROM @stm1;
						EXECUTE stm1;
					end if;

			ELSE
			SET @v_SelectedVendor = ( select VendorID from tmp_vendor_position where vPosition <= @v_RatePosition_ and Package=@v_MergePackage order by vPosition , Total  limit 1 );
			SET @stm1 = CONCAT('
						update tmp_SelectedVendortblRateTablePKGRate srt
						inner join (

								select

									TimezonesID,
									Code,

									', @ResultField , ' as componentValue

									from tmp_tblRateTablePKGRate


								where
									VendorID = @v_SelectedVendor
									AND PackageID = @v_MergePackage
								AND (  @v_TimezonesID = "" OR  TimezonesID = @v_TimezonesID)





						) tmp on
								tmp.Code = srt.Code
								AND (  @v_ToTimezonesID = "" OR  srt.TimezonesID = @v_ToTimezonesID)

						set

						' , 'new_', @v_MergeTo , ' = tmp.componentValue;
				');

				--	select @stm1;
					if @stm1 is not null then
						PREPARE stm1 FROM @stm1;
						EXECUTE stm1;
					end if;

			END IF;




				IF ROW_COUNT()  = 0 THEN



						insert into tmp_SelectedVendortblRateTablePKGRate
						(
								TimezonesID,
								TimezoneTitle,
								Code,

								VendorID,
								CodeDeckId,
								PackageID,
								VendorName,
								EndDate,
								OneOffCost,
										MonthlyCost,
										PackageCostPerMinute,
	RecordingCostPerMinute,
	OneOffCostCurrency,
	MonthlyCostCurrency,
	PackageCostPerMinuteCurrency,
	RecordingCostPerMinuteCurrency
						)
						select
								IF(@v_ToTimezonesID = '',TimezonesID,@v_ToTimezonesID) as TimezonesID,
								TimezoneTitle,
								 Code,

								VendorID,
								CodeDeckId,
								PackageID,
								VendorName,
								EndDate,
								OneOffCost,
										MonthlyCost,
										PackageCostPerMinute,
	RecordingCostPerMinute,
	OneOffCostCurrency,
	MonthlyCostCurrency,
	PackageCostPerMinuteCurrency,
	RecordingCostPerMinuteCurrency

						from tmp_tblRateTablePKGRate

						where
							VendorID = @v_SelectedVendor
							AND (  @v_TimezonesID = "" OR  TimezonesID = @v_TimezonesID)

							;



				END IF;

		if @stm1 is not null then
				DEALLOCATE PREPARE stm1;
		end if;



			SET @v_pointer_ = @v_pointer_ + 1;

		END WHILE;



	--	 select * from tmp_SelectedVendortblRateTablePKGRate;


		update tmp_SelectedVendortblRateTablePKGRate
		SET
			OneOffCost  = IF(new_OneOffCost is null , OneOffCost ,new_OneOffCost)  	,
			MonthlyCost  = IF(new_MonthlyCost is null , MonthlyCost ,new_MonthlyCost)  	,
			PackageCostPerMinute  = IF(new_PackageCostPerMinute is null , PackageCostPerMinute ,new_PackageCostPerMinute)  	,
			RecordingCostPerMinute  = IF(new_RecordingCostPerMinute is null , RecordingCostPerMinute ,new_RecordingCostPerMinute)
			;








	 	SET @v_pointer_ = 1;
		SET @v_rowCount_ = ( SELECT COUNT(*) FROM tmp_Raterules_ );

	--	select * from tmp_Raterules_;

		WHILE @v_pointer_ <= @v_rowCount_
		DO

			SET @v_rateRuleId_ = (SELECT rateruleid FROM tmp_Raterules_ rr WHERE rr.RowNo = @v_pointer_);
			SET @v_rateRulePackageId = (SELECT PackageID FROM tmp_Raterules_ rr WHERE rr.RowNo = @v_pointer_);

				IF @v_rateRulePackageId = 0 THEN
						update tmp_SelectedVendortblRateTablePKGRate rt
						inner join tmp_Raterules_ rr on rr.RowNo  = @v_pointer_
						and  rr.TimezonesID  = rt.TimezonesID
						and rr.PackageID = 0



						LEFT join tblRateRuleMargin rule_mgn1 on  rule_mgn1.RateRuleId = @v_rateRuleId_
						AND
						(
							(rr.Component = 'OneOffCost' AND OneOffCost Between rule_mgn1.MinRate and rule_mgn1.MaxRate) OR
							(rr.Component = 'MonthlyCost' AND MonthlyCost Between rule_mgn1.MinRate and rule_mgn1.MaxRate) OR
							(rr.Component = 'PackageCostPerMinute' AND PackageCostPerMinute Between rule_mgn1.MinRate and rule_mgn1.MaxRate) OR
							(rr.Component = 'RecordingCostPerMinute' AND RecordingCostPerMinute Between rule_mgn1.MinRate and rule_mgn1.MaxRate)


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

						PackageCostPerMinute = CASE WHEN rr.Component = 'PackageCostPerMinute' AND rule_mgn1.RateRuleId is not null THEN
									CASE WHEN trim(IFNULL(rule_mgn1.AddMargin,"")) != '' THEN
										PackageCostPerMinute + (CASE WHEN rule_mgn1.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn1.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * PackageCostPerMinute) ELSE rule_mgn1.addmargin END)
									WHEN trim(IFNULL(rule_mgn1.FixedValue,"")) != '' THEN
										rule_mgn1.FixedValue
									ELSE
										PackageCostPerMinute
									END
							ELSE
							PackageCostPerMinute
							END,

						RecordingCostPerMinute = CASE WHEN rr.Component = 'RecordingCostPerMinute' AND rule_mgn1.RateRuleId is not null THEN
									CASE WHEN trim(IFNULL(rule_mgn1.AddMargin,"")) != '' THEN
										RecordingCostPerMinute + (CASE WHEN rule_mgn1.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn1.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * RecordingCostPerMinute) ELSE rule_mgn1.addmargin END)
									WHEN trim(IFNULL(rule_mgn1.FixedValue,"")) != '' THEN
										rule_mgn1.FixedValue
									ELSE
										RecordingCostPerMinute
									END
							ELSE
							RecordingCostPerMinute
							END


			;

			ELSE

			update tmp_SelectedVendortblRateTablePKGRate rt
						inner join tmp_Raterules_ rr on rr.RowNo  = @v_pointer_
						and  rr.TimezonesID  = rt.TimezonesID
						and (rr.PackageID = rt.PackageID )



						LEFT join tblRateRuleMargin rule_mgn1 on  rule_mgn1.RateRuleId = @v_rateRuleId_
						AND
						(
							(rr.Component = 'OneOffCost' AND OneOffCost Between rule_mgn1.MinRate and rule_mgn1.MaxRate) OR
							(rr.Component = 'MonthlyCost' AND MonthlyCost Between rule_mgn1.MinRate and rule_mgn1.MaxRate) OR
							(rr.Component = 'PackageCostPerMinute' AND PackageCostPerMinute Between rule_mgn1.MinRate and rule_mgn1.MaxRate) OR
							(rr.Component = 'RecordingCostPerMinute' AND RecordingCostPerMinute Between rule_mgn1.MinRate and rule_mgn1.MaxRate)


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

						PackageCostPerMinute = CASE WHEN rr.Component = 'PackageCostPerMinute' AND rule_mgn1.RateRuleId is not null THEN
									CASE WHEN trim(IFNULL(rule_mgn1.AddMargin,"")) != '' THEN
										PackageCostPerMinute + (CASE WHEN rule_mgn1.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn1.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * PackageCostPerMinute) ELSE rule_mgn1.addmargin END)
									WHEN trim(IFNULL(rule_mgn1.FixedValue,"")) != '' THEN
										rule_mgn1.FixedValue
									ELSE
										PackageCostPerMinute
									END
							ELSE
							PackageCostPerMinute
							END,

						RecordingCostPerMinute = CASE WHEN rr.Component = 'RecordingCostPerMinute' AND rule_mgn1.RateRuleId is not null THEN
									CASE WHEN trim(IFNULL(rule_mgn1.AddMargin,"")) != '' THEN
										RecordingCostPerMinute + (CASE WHEN rule_mgn1.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn1.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * RecordingCostPerMinute) ELSE rule_mgn1.addmargin END)
									WHEN trim(IFNULL(rule_mgn1.FixedValue,"")) != '' THEN
										rule_mgn1.FixedValue
									ELSE
										RecordingCostPerMinute
									END
							ELSE
							RecordingCostPerMinute
							END


			;


				END IF;


			SET @v_pointer_ = @v_pointer_ + 1;

		END WHILE;







	--	select * from tmp_RateGeneratorCalculatedRate_;
	 	SET @v_pointer_ = 1;
		SET @v_rowCount_ = ( SELECT COUNT(*) FROM tmp_RateGeneratorCalculatedRate_ );


		WHILE @v_pointer_ <= @v_rowCount_
		DO

					select PackageID into @CalculatedRatePackageID from	tmp_RateGeneratorCalculatedRate_ rr where rr.RowNo  = @v_pointer_;


						if @CalculatedRatePackageID = 0 THEN

						update tmp_SelectedVendortblRateTablePKGRate rt
						inner join tmp_RateGeneratorCalculatedRate_ rr on
						rr.RowNo  = @v_pointer_  AND rr.TimezonesID  = rt.TimezonesID	 and  rr.PackageID = 0





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
						PackageCostPerMinute = CASE WHEN FIND_IN_SET(rr.Component,'PackageCostPerMinute') != 0 AND PackageCostPerMinute < RateLessThen AND rr.CalculatedRateID is not null THEN
						ChangeRateTo
						ELSE
						PackageCostPerMinute
						END,
						RecordingCostPerMinute = CASE WHEN FIND_IN_SET(rr.Component,'RecordingCostPerMinute') != 0 AND RecordingCostPerMinute < RateLessThen AND rr.CalculatedRateID is not null THEN
						ChangeRateTo
						ELSE
						RecordingCostPerMinute
						END
						;

			ELSE

			update tmp_SelectedVendortblRateTablePKGRate rt
						inner join tmp_RateGeneratorCalculatedRate_ rr on
						rr.RowNo  = @v_pointer_  AND rr.TimezonesID  = rt.TimezonesID

						and rt.PackageID = 	rr.PackageID





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
						PackageCostPerMinute = CASE WHEN FIND_IN_SET(rr.Component,'PackageCostPerMinute') != 0 AND PackageCostPerMinute < RateLessThen AND rr.CalculatedRateID is not null THEN
						ChangeRateTo
						ELSE
						PackageCostPerMinute
						END,
						RecordingCostPerMinute = CASE WHEN FIND_IN_SET(rr.Component,'RecordingCostPerMinute') != 0 AND RecordingCostPerMinute < RateLessThen AND rr.CalculatedRateID is not null THEN
						ChangeRateTo
						ELSE
						RecordingCostPerMinute
						END
						;

			 END IF;

			SET @v_pointer_ = @v_pointer_ + 1;



		END WHILE;







	--	select * from tmp_SelectedVendortblRateTablePKGRate;

		SET @v_SelectedRateTableID = ( select RateTableID from tmp_SelectedVendortblRateTablePKGRate limit 1 );

		SET @v_AffectedRecords_ = 0;

		START TRANSACTION;



		IF p_RateTableId = -1 THEN

			SET @v_codedeckid_ = ( select CodeDeckId from tmp_SelectedVendortblRateTablePKGRate limit 1 );

			INSERT INTO tblRateTable (Type, CompanyId, RateTableName, RateGeneratorID,DIDCategoryID, TrunkID, CodeDeckId,CurrencyID,Status, RoundChargedAmount,MinimumCallCharge,AppliedTo,Reseller,created_at,updated_at, CreatedBy,ModifiedBy)
			select  @v_PKGType as Type, @v_CompanyId_, p_rateTableName , @p_RateGeneratorId,0 as DIDCategoryID, 0 as TrunkID,  @v_CodeDeckId as CodeDeckId , @v_RateGenatorCurrencyID_ as CurrencyID, Status, RoundChargedAmount,MinimumCallCharge, @p_AppliedTo as AppliedTo, @p_Reseller as Reseller , now() ,now() ,p_ModifiedBy,p_ModifiedBy
			from tblRateTable where RateTableID = @v_SelectedRateTableID  limit 1;

			SET @p_RateTableId = LAST_INSERT_ID();

			-- select  `AccountId`,  `RateTypeID`,  `ConnectionType`,  `CompanyID`,  `Name`,  `DIDCategoryID`,  `Active`,  `Tariff`,  `RateTableID`,  `TrunkID`,  `CLIRule`,  `CLDRule`,  `CallPrefix`,  `IP`,  `Port`,  `Username`,  Password,  now(),  `updated_at`,  `created_by`,  `updated_by` from tblVendorConnection where (AccountId,RateTableID) in (select RateTableID,VendorID from tmp_SelectedVendortblRateTablePKGRate group by VendorID);
		--	insert into tmp_tblVendorConnection	(AccountID,RateTableId)
		--	select VendorID,RateTableID from tmp_SelectedVendortblRateTablePKGRate group by VendorID;
		--	select * from tmp_tblVendorConnection;
		--	insert into tblVendorConnection(`AccountId`,  `RateTypeID`,  `ConnectionType`,  `CompanyID`,  `Name`,  `DIDCategoryID`,  `Active`,  `Tariff`,  RateTableID,  `TrunkID`,  `CLIRule`,  `CLDRule`,  `CallPrefix`,  `IP`,  `Port`,  `Username`,  Password,  created_at,  `updated_at`,  `created_by`,  `updated_by`)
		--	select  vConn.`AccountId`,  `RateTypeID`,  `ConnectionType`,  `CompanyID`,  `Name`,  `DIDCategoryID`,  `Active`,  `Tariff`,  @p_RateTableId,  `TrunkID`,  `CLIRule`,  `CLDRule`,  `CallPrefix`,  `IP`,  `Port`,  `Username`,  Password,  now(),  `updated_at`,  `created_by`,  `updated_by` from tblVendorConnection vConn,tmp_tblVendorConnection tvConn where vConn.AccountId = tvConn.AccountID and vConn.RateTableID = tvConn.RateTableId;


		ELSE

			SET @p_RateTableId = p_RateTableId;

			IF p_delete_exiting_rate = 1 THEN

				UPDATE
					tblRateTablePKGRate
				SET
					EndDate = NOW()
				WHERE
					RateTableId = @p_RateTableId;


			 call prc_ArchiveOldRateTablePKGRate(@p_RateTableId, NULL,p_ModifiedBy);

			END IF;



			IF @v_RateApprovalProcess_ = 0 THEN
				update tblRateTablePKGRate rtd
				INNER JOIN tblRateTable rt  on rt.RateTableID = rtd.RateTableID
				INNER JOIN tblRate r
					ON rtd.RateID  = r.RateID
				INNER JOIN tblPackage st ON st.CompanyID = r.CompanyID AND st.Name = drtr.Code AND  r.Code = st.Name
				inner join tmp_SelectedVendortblRateTablePKGRate drtr on
				drtr.Code = r.Code
				and rtd.TimezonesID = drtr.TimezonesID

				SET rtd.EndDate = NOW()

				where
				rtd.RateTableID = @p_RateTableId and rtd.EffectiveDate = @p_EffectiveDate;
			END IF;

			IF @v_RateApprovalProcess_ = 1 THEN
				update tblRateTablePKGRateAA rtd
				INNER JOIN tblRateTable rt  on rt.RateTableID = rtd.RateTableID
				INNER JOIN tblRate r
					ON rtd.RateID  = r.RateID
				INNER JOIN tblPackage st ON st.CompanyID = r.CompanyID AND st.Name = drtr.Code AND  r.Code = st.Name
				inner join tmp_SelectedVendortblRateTablePKGRate drtr on
				drtr.Code = r.Code
				and rtd.TimezonesID = drtr.TimezonesID

				SET rtd.EndDate = NOW()

				where
				rtd.RateTableID = @p_RateTableId and rtd.EffectiveDate = @p_EffectiveDate;
			END IF;


			IF @v_RateApprovalProcess_ = 0 THEN
		 call prc_ArchiveOldRateTablePKGRate(@p_RateTableId, NULL,p_ModifiedBy);
		END IF;
		IF @v_RateApprovalProcess_ = 1 THEN
		 call prc_ArchiveOldRateTablePKGRateAA(@p_RateTableId, NULL,p_ModifiedBy);
		END IF;



			SET @v_AffectedRecords_ = @v_AffectedRecords_ + FOUND_ROWS();


		END IF;


	--  select @v_RateApprovalProcess_;
	-- select * from tmp_SelectedVendortblRateTablePKGRate order by Code;

	IF @v_RateApprovalProcess_ = 0 THEN





		INSERT INTO tblRateTablePKGRate (
		RateTableId,
							TimezonesID,

							RateId,

							OneOffCost,
							MonthlyCost,
							PackageCostPerMinute,
							RecordingCostPerMinute,
							OneOffCostCurrency,
							MonthlyCostCurrency,
							PackageCostPerMinuteCurrency,
							RecordingCostPerMinuteCurrency,
							EffectiveDate,
							EndDate,
							ApprovedStatus,

							created_at ,
							updated_at ,
							CreatedBy ,
							ModifiedBy,
							VendorID


			)
			SELECT DISTINCT

						@p_RateTableId as RateTableId,
						drtr.TimezonesID,

						r.RateId,



						CASE WHEN ( drtr.OneOffCostCurrency is not null)
						THEN

						CASE WHEN  @v_CurrencyID_ = drtr.OneOffCostCurrency THEN
						drtr.OneOffCost
						ELSE
						(

						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  drtr.OneOffCostCurrency  and  CompanyID = @v_CompanyId_  )
						* (drtr.OneOffCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
						)
						END

						WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
						drtr.OneOffCost
						ELSE
						(

							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  rt.CurrencyID  and  CompanyID = @v_CompanyId_ )
							* (drtr.OneOffCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
						)
						END as OneOffCost,

						( CASE WHEN ( drtr.MonthlyCostCurrency is not null)
						THEN

						CASE WHEN  @v_CurrencyID_ = drtr.MonthlyCostCurrency THEN
						drtr.MonthlyCost
						ELSE
						(

						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  drtr.MonthlyCostCurrency  and  CompanyID = @v_CompanyId_  )
						* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
						)
						END

						WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
						drtr.MonthlyCost
						ELSE
						(

							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID  and  CompanyID = @v_CompanyId_ )
							* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_  and  CompanyID = @v_CompanyId_ ))
						)
						END) as MonthlyCost,

						CASE WHEN ( drtr.PackageCostPerMinuteCurrency is not null)
						THEN

						CASE WHEN  @v_CurrencyID_ = drtr.PackageCostPerMinuteCurrency THEN
						drtr.PackageCostPerMinute
						ELSE
						(

						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = drtr.PackageCostPerMinuteCurrency and  CompanyID = @v_CompanyId_ )
						* (drtr.PackageCostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_   and  CompanyID = @v_CompanyId_ ))
						)
						END

						WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
						drtr.PackageCostPerMinute
						ELSE
						(

							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  rt.CurrencyID  and  CompanyID = @v_CompanyId_ )
							* (drtr.PackageCostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_  and  CompanyID = @v_CompanyId_ ))
						)
						END as PackageCostPerMinute,

						CASE WHEN ( drtr.RecordingCostPerMinuteCurrency is not null)
						THEN

						CASE WHEN  @v_CurrencyID_ = drtr.RecordingCostPerMinuteCurrency THEN
						drtr.RecordingCostPerMinute
						ELSE
						(

						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  drtr.RecordingCostPerMinuteCurrency  and  CompanyID = @v_CompanyId_ )
						* (drtr.RecordingCostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
						)
						END

						WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
						drtr.RecordingCostPerMinute
						ELSE
						(

							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ )
							* (drtr.RecordingCostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_   and  CompanyID = @v_CompanyId_ ))
						)
						END as RecordingCostPerMinute,





						drtr.OneOffCostCurrency,
						drtr.MonthlyCostCurrency,
						drtr.PackageCostPerMinuteCurrency,
						drtr.RecordingCostPerMinuteCurrency,


						@p_EffectiveDate as EffectiveDate,
						date(drtr.EndDate) as EndDate,
						@v_RateApprovalStatus_ as ApprovedStatus,


							now() as  created_at ,
							now() as updated_at ,
							p_ModifiedBy as CreatedBy ,
							p_ModifiedBy as ModifiedBy,
							drtr.VendorID



						from tmp_SelectedVendortblRateTablePKGRate drtr
						inner join tblRateTable  rt on rt.RateTableId = drtr.RateTableId
						INNER JOIN tblRate r ON drtr.Code = r.Code and r.CodeDeckId = drtr.CodeDeckId
						INNER JOIN tblPackage st ON st.CompanyID = r.CompanyID AND st.Name = drtr.Code AND  r.Code = st.Name
						LEFT join tblRateTablePKGRate rtd  on rtd.RateID  = r.RateID
						and  rtd.TimezonesID = drtr.TimezonesID
						and rtd.RateTableID = @p_RateTableId
						and rtd.EffectiveDate = @p_EffectiveDate
						WHERE rtd.RateTablePKGRateID is null;
	END IF;


	IF @v_RateApprovalProcess_ = 1 THEN
		INSERT INTO tblRateTablePKGRateAA (
		RateTableId,
							TimezonesID,

							RateId,

							OneOffCost,
							MonthlyCost,
							PackageCostPerMinute,
							RecordingCostPerMinute,
							OneOffCostCurrency,
							MonthlyCostCurrency,
							PackageCostPerMinuteCurrency,
							RecordingCostPerMinuteCurrency,
							EffectiveDate,
							EndDate,
							ApprovedStatus,

							created_at ,
							updated_at ,
							CreatedBy ,
							ModifiedBy,
							VendorID


			)
			SELECT DISTINCT

						@p_RateTableId as RateTableId,
						drtr.TimezonesID,

						r.RateId,



						CASE WHEN ( drtr.OneOffCostCurrency is not null)
						THEN

						CASE WHEN  @v_CurrencyID_ = drtr.OneOffCostCurrency THEN
						drtr.OneOffCost
						ELSE
						(

						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  drtr.OneOffCostCurrency  and  CompanyID = @v_CompanyId_  )
						* (drtr.OneOffCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
						)
						END

						WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
						drtr.OneOffCost
						ELSE
						(

							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  rt.CurrencyID  and  CompanyID = @v_CompanyId_ )
							* (drtr.OneOffCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
						)
						END as OneOffCost,

						( CASE WHEN ( drtr.MonthlyCostCurrency is not null)
						THEN

						CASE WHEN  @v_CurrencyID_ = drtr.MonthlyCostCurrency THEN
						drtr.MonthlyCost
						ELSE
						(

						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  drtr.MonthlyCostCurrency  and  CompanyID = @v_CompanyId_  )
						* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
						)
						END

						WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
						drtr.MonthlyCost
						ELSE
						(

							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID  and  CompanyID = @v_CompanyId_ )
							* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_  and  CompanyID = @v_CompanyId_ ))
						)
						END) as MonthlyCost,

						CASE WHEN ( drtr.PackageCostPerMinuteCurrency is not null)
						THEN

						CASE WHEN  @v_CurrencyID_ = drtr.PackageCostPerMinuteCurrency THEN
						drtr.PackageCostPerMinute
						ELSE
						(

						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = drtr.PackageCostPerMinuteCurrency and  CompanyID = @v_CompanyId_ )
						* (drtr.PackageCostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_   and  CompanyID = @v_CompanyId_ ))
						)
						END

						WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
						drtr.PackageCostPerMinute
						ELSE
						(

							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  rt.CurrencyID  and  CompanyID = @v_CompanyId_ )
							* (drtr.PackageCostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_  and  CompanyID = @v_CompanyId_ ))
						)
						END as PackageCostPerMinute,

						CASE WHEN ( drtr.RecordingCostPerMinuteCurrency is not null)
						THEN

						CASE WHEN  @v_CurrencyID_ = drtr.RecordingCostPerMinuteCurrency THEN
						drtr.RecordingCostPerMinute
						ELSE
						(

						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  drtr.RecordingCostPerMinuteCurrency  and  CompanyID = @v_CompanyId_ )
						* (drtr.RecordingCostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
						)
						END

						WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
						drtr.RecordingCostPerMinute
						ELSE
						(

							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ )
							* (drtr.RecordingCostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_   and  CompanyID = @v_CompanyId_ ))
						)
						END as RecordingCostPerMinute,





						drtr.OneOffCostCurrency,
						drtr.MonthlyCostCurrency,
						drtr.PackageCostPerMinuteCurrency,
						drtr.RecordingCostPerMinuteCurrency,


						@p_EffectiveDate as EffectiveDate,
						date(drtr.EndDate) as EndDate,
						@v_RateApprovalStatus_ as ApprovedStatus,


							now() as  created_at ,
							now() as updated_at ,
							p_ModifiedBy as CreatedBy ,
							p_ModifiedBy as ModifiedBy,
							drtr.VendorID



						from tmp_SelectedVendortblRateTablePKGRate drtr
						inner join tblRateTable  rt on rt.RateTableId = drtr.RateTableId
						INNER JOIN tblRate r ON drtr.Code = r.Code and r.CodeDeckId = drtr.CodeDeckId
						INNER JOIN tblPackage st ON st.CompanyID = r.CompanyID AND st.Name = drtr.Code AND  r.Code = st.Name
						LEFT join tblRateTablePKGRateAA rtd  on rtd.RateID  = r.RateID
						and  rtd.TimezonesID = drtr.TimezonesID
						and rtd.RateTableID = @p_RateTableId
						and rtd.EffectiveDate = @p_EffectiveDate
						WHERE rtd.RateTablePKGRateAAID is null;
	END IF;







		SET @v_AffectedRecords_ = @v_AffectedRecords_ + FOUND_ROWS();



		DROP TEMPORARY TABLE IF EXISTS tmp_EffectiveDates_;
		CREATE TEMPORARY TABLE tmp_EffectiveDates_ (
			RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			EffectiveDate  Date
		);

	IF @v_RateApprovalProcess_ = 0 THEN
		INSERT INTO tmp_EffectiveDates_ (EffectiveDate)
		SELECT distinct
			EffectiveDate
		FROM
		(
			select distinct EffectiveDate
			from 	tblRateTablePKGRate
			WHERE
				RateTableId = @p_RateTableId
			Group By EffectiveDate
			order by EffectiveDate desc
		) tmp
		,(SELECT @row_num := 0) x;

	END IF;

	IF @v_RateApprovalProcess_ = 1 THEN
		INSERT INTO tmp_EffectiveDates_ (EffectiveDate)
		SELECT distinct
			EffectiveDate
		FROM
		(
			select distinct EffectiveDate
			from 	tblRateTablePKGRateAA
			WHERE
				RateTableId = @p_RateTableId
			Group By EffectiveDate
			order by EffectiveDate desc
		) tmp
		,(SELECT @row_num := 0) x;

	END IF;

		SET @v_pointer_ = 1;
		SET @v_rowCount_ = ( SELECT COUNT(*) FROM tmp_EffectiveDates_ );



		IF @v_rowCount_ > 0 THEN

			WHILE @v_pointer_ <= @v_rowCount_
			DO
				SET @EffectiveDate = ( SELECT EffectiveDate FROM tmp_EffectiveDates_ WHERE RowID = @v_pointer_ );

				IF @v_RateApprovalProcess_ = 0 THEN
					UPDATE  tblRateTablePKGRate vr1
					inner join
					(
						select
							RateTableId,

							RateID,
							EffectiveDate,
							TimezonesID
						FROM tblRateTablePKGRate
						WHERE RateTableId = @p_RateTableId
							AND EffectiveDate =   @EffectiveDate
						order by EffectiveDate desc
					) tmpvr
					on
						vr1.RateTableId = tmpvr.RateTableId
						AND vr1.RateID = tmpvr.RateID
						AND vr1.TimezonesID = tmpvr.TimezonesID

						AND vr1.EffectiveDate < tmpvr.EffectiveDate
					SET
						vr1.EndDate = @EffectiveDate
					where
						vr1.RateTableId = @p_RateTableId

						AND vr1.EndDate is null;
				END IF;

				IF @v_RateApprovalProcess_ = 1 THEN
					UPDATE  tblRateTablePKGRateAA vr1
					inner join
					(
						select
							RateTableId,

							RateID,
							EffectiveDate,
							TimezonesID
						FROM tblRateTablePKGRateAA
						WHERE RateTableId = @p_RateTableId
							AND EffectiveDate =   @EffectiveDate
						order by EffectiveDate desc
					) tmpvr
					on
						vr1.RateTableId = tmpvr.RateTableId
						AND vr1.RateID = tmpvr.RateID
						AND vr1.TimezonesID = tmpvr.TimezonesID

						AND vr1.EffectiveDate < tmpvr.EffectiveDate
					SET
						vr1.EndDate = @EffectiveDate
					where
						vr1.RateTableId = @p_RateTableId

						AND vr1.EndDate is null;
				END IF;


				SET @v_pointer_ = @v_pointer_ + 1;

			END WHILE;







		END IF;

		commit;



		IF @v_RateApprovalProcess_ = 0 THEN
		 call prc_ArchiveOldRateTablePKGRate(@p_RateTableId, NULL,p_ModifiedBy);
		END IF;
		IF @v_RateApprovalProcess_ = 1 THEN
		 call prc_ArchiveOldRateTablePKGRateAA(@p_RateTableId, NULL,p_ModifiedBy);
		END IF;

		INSERT INTO tmp_JobLog_ (Message) VALUES (@p_RateTableId);

		SELECT * FROM tmp_JobLog_;


		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;



	END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_WSProcessRateTableRate`;
DELIMITER //
CREATE PROCEDURE `prc_WSProcessRateTableRate`(
	IN `p_RateTableId` INT,
	IN `p_replaceAllRates` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_processId` VARCHAR(200),
	IN `p_addNewCodesToCodeDeck` INT,
	IN `p_companyId` INT,
	IN `p_forbidden` INT,
	IN `p_preference` INT,
	IN `p_dialstringid` INT,
	IN `p_dialcodeSeparator` VARCHAR(50),
	IN `p_seperatecolumn` INT,
	IN `p_CurrencyID` INT,
	IN `p_list_option` INT,
	IN `p_UserName` TEXT
)
ThisSP:BEGIN

	DECLARE v_AffectedRecords_ INT DEFAULT 0;
	DECLARE v_CodeDeckId_ INT ;
	DECLARE totaldialstringcode INT(11) DEFAULT 0;
	DECLARE newstringcode INT(11) DEFAULT 0;
	DECLARE totalduplicatecode INT(11);
	DECLARE errormessage longtext;
	DECLARE errorheader longtext;
	DECLARE v_RateTableCurrencyID_ INT;
	DECLARE v_CompanyCurrencyID_ INT;
	DECLARE v_RateApprovalProcess_ INT;
	DECLARE v_RateTableAppliedTo_ INT;

	DECLARE v_pointer_ INT;
	DECLARE v_rowCount_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT Value INTO v_RateApprovalProcess_ FROM tblCompanySetting WHERE CompanyID = p_companyId AND `Key`='RateApprovalProcess';
	SELECT AppliedTo INTO v_RateTableAppliedTo_ FROM tblRateTable WHERE RateTableID = p_RateTableId;

	DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
	CREATE TEMPORARY TABLE tmp_JobLog_ (
		Message longtext
	);

    DROP TEMPORARY TABLE IF EXISTS tmp_TempTimezones_;
    CREATE TEMPORARY TABLE tmp_TempTimezones_ (
        TimezonesID INT
    );

	DROP TEMPORARY TABLE IF EXISTS tmp_split_RateTableRate_;
	CREATE TEMPORARY TABLE tmp_split_RateTableRate_ (
		`TempRateTableRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Type` varchar(50) NULL DEFAULT NULL,
		`Rate` decimal(18, 6) ,
		`RateN` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Blocked` tinyint,
		`RoutingCategoryID` int,
		`RateCurrency` INT(11) NULL DEFAULT NULL,
		`ConnectionFeeCurrency` INT(11) NULL DEFAULT NULL,
		`DialStringPrefix` varchar(500) ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_OriginationCode (`OriginationCode`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableRate_;
	CREATE TEMPORARY TABLE tmp_TempRateTableRate_ (
		TempRateTableRateID int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Type` varchar(50) NULL DEFAULT NULL,
		`Rate` decimal(18, 6) ,
		`RateN` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Blocked` tinyint,
		`RoutingCategoryID` int,
		`RateCurrency` INT(11) NULL DEFAULT NULL,
		`ConnectionFeeCurrency` INT(11) NULL DEFAULT NULL,
		`DialStringPrefix` varchar(500) ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_OriginationCode (`OriginationCode`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_Delete_RateTableRate;
	CREATE TEMPORARY TABLE tmp_Delete_RateTableRate (
		RateTableRateID INT,
		RateTableId INT,
		TimezonesID INT,
		OriginationRateID INT,
		OriginationCode VARCHAR(50),
		OriginationDescription VARCHAR(200),
		RateId INT,
		Code VARCHAR(50),
		Description VARCHAR(200),
		`Type` varchar(50) NULL DEFAULT NULL,
		Rate DECIMAL(18, 6),
		RateN DECIMAL(18, 6),
		EffectiveDate DATETIME,
		EndDate Datetime ,
		Interval1 INT,
		IntervalN INT,
		ConnectionFee DECIMAL(18, 6),
		Preference varchar(100) ,
		Blocked tinyint,
		RoutingCategoryID int,
		RateCurrency INT(11) NULL DEFAULT NULL,
		ConnectionFeeCurrency INT(11) NULL DEFAULT NULL,
		deleted_at DATETIME,
		INDEX tmp_RateTableRateDiscontinued_RateTableRateID (`RateTableRateID`)
	);


	CALL  prc_RateTableCheckDialstringAndDupliacteCode(p_companyId,p_processId,p_dialstringid,p_effectiveImmediately,p_dialcodeSeparator,p_seperatecolumn);

	SELECT COUNT(*) AS COUNT INTO newstringcode from tmp_JobLog_;


 	INSERT INTO tmp_TempTimezones_
 	SELECT DISTINCT TimezonesID from tmp_TempRateTableRate_;


	IF newstringcode = 0
	THEN

		IF (SELECT count(*) FROM tblRateTableRateChangeLog WHERE ProcessID = p_processId ) > 0
		THEN

			UPDATE
				tblRateTableRate vr
			INNER JOIN tblRateTableRateChangeLog  vrcl
			on vrcl.RateTableRateID = vr.RateTableRateID
			SET
				vr.EndDate = IFNULL(vrcl.EndDate,date(now()))
			WHERE vrcl.ProcessID = p_processId
				AND vrcl.`Action`  ='Deleted';


			UPDATE tmp_TempRateTableRate_ tblTempRateTableRate
			JOIN tblRateTableRateChangeLog vrcl
				ON  vrcl.ProcessId = p_processId
				AND vrcl.Code = tblTempRateTableRate.Code
				AND vrcl.OriginationCode = tblTempRateTableRate.OriginationCode
			SET
				tblTempRateTableRate.EndDate = vrcl.EndDate
			WHERE
				vrcl.`Action` = 'Deleted'
				AND vrcl.EndDate IS NOT NULL ;


			UPDATE tmp_TempRateTableRate_ tblTempRateTableRate
			JOIN tblRateTableRateChangeLog vrcl
				ON  vrcl.ProcessId = p_processId
				AND vrcl.Code = tblTempRateTableRate.Code
				AND vrcl.OriginationCode = tblTempRateTableRate.OriginationCode
			SET
				tblTempRateTableRate.Interval1 = vrcl.Interval1 ,
				tblTempRateTableRate.IntervalN = vrcl.IntervalN
			WHERE
				vrcl.`Action` = 'New'
				AND vrcl.Interval1 IS NOT NULL
				AND vrcl.IntervalN IS NOT NULL ;



		END IF;


		IF  p_replaceAllRates = 1
		THEN
			UPDATE tblRateTableRate
				SET tblRateTableRate.EndDate = date(now())
			WHERE RateTableId = p_RateTableId;


		END IF;



		IF p_list_option = 1
		THEN

			INSERT INTO tmp_Delete_RateTableRate(
				RateTableRateID,
				RateTableId,
				TimezonesID,
				OriginationRateID,
				OriginationCode,
				OriginationDescription,
				RateId,
				Code,
				Description,
				Rate,
				RateN,
				EffectiveDate,
				EndDate,
				Interval1,
				IntervalN,
				ConnectionFee,
				Preference,
				Blocked,
				RoutingCategoryID,
				RateCurrency,
				ConnectionFeeCurrency,
				deleted_at
			)
			SELECT DISTINCT
				tblRateTableRate.RateTableRateID,
				p_RateTableId AS RateTableId,
				tblRateTableRate.TimezonesID,
				tblRateTableRate.OriginationRateID,
				OriginationRate.Code AS OriginationCode,
				OriginationRate.Description AS OriginationDescription,
				tblRateTableRate.RateId,
				tblRate.Code,
				tblRate.Description,
				tblRateTableRate.Rate,
				tblRateTableRate.RateN,
				tblRateTableRate.EffectiveDate,
				IFNULL(tblRateTableRate.EndDate,date(now())) ,
				tblRateTableRate.Interval1,
				tblRateTableRate.IntervalN,
				tblRateTableRate.ConnectionFee,
				tblRateTableRate.Preference,
				tblRateTableRate.Blocked,
				tblRateTableRate.RoutingCategoryID,
				tblRateTableRate.RateCurrency,
				tblRateTableRate.ConnectionFeeCurrency,
				now() AS deleted_at
			FROM tblRateTableRate
			JOIN tblRate
				ON tblRate.RateID = tblRateTableRate.RateId
				AND tblRate.CompanyID = p_companyId
			LEFT JOIN tblRate AS OriginationRate
				ON OriginationRate.RateID = tblRateTableRate.OriginationRateID
				AND OriginationRate.CompanyID = p_companyId
		  	/*JOIN tmp_TempTimezones_
		  		ON tmp_TempTimezones_.TimezonesID = tblRateTableRate.TimezonesID*/
			LEFT JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
				ON tblTempRateTableRate.Code = tblRate.Code
				AND ((tblTempRateTableRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableRate.OriginationCode = OriginationRate.Code))
				AND tblTempRateTableRate.TimezonesID = tblRateTableRate.TimezonesID
				AND  tblTempRateTableRate.ProcessId = p_processId
				AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
			WHERE tblRateTableRate.RateTableId = p_RateTableId
				AND tblTempRateTableRate.Code IS NULL
				AND ( tblRateTableRate.EndDate is NULL OR tblRateTableRate.EndDate <= date(now()) )
			ORDER BY RateTableRateID ASC;




			UPDATE tblRateTableRate
			JOIN tmp_Delete_RateTableRate ON tblRateTableRate.RateTableRateID = tmp_Delete_RateTableRate.RateTableRateID
				SET tblRateTableRate.EndDate = date(now())
			WHERE
				tblRateTableRate.RateTableId = p_RateTableId;

		END IF;



		IF ( (SELECT count(*) FROM tblRateTableRate WHERE  RateTableId = p_RateTableId AND EndDate <= NOW() )  > 0  ) THEN

			call prc_RateTableRateUpdatePreviousRate(p_RateTableId,'');
			call prc_ArchiveOldRateTableRate(p_RateTableId, NULL,p_UserName);

		END IF;

		DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableRate_2;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempRateTableRate_2 AS (SELECT * FROM tmp_TempRateTableRate_);

		IF  p_addNewCodesToCodeDeck = 1
		THEN
			-- Destination Code
			INSERT INTO tblRate (
				CompanyID,
				Code,
				Description,
				`Type`,
				CreatedBy,
				CountryID,
				CodeDeckId,
				Interval1,
				IntervalN
			)
			SELECT DISTINCT
				p_companyId,
				vc.Code,
				vc.Description,
				vc.`Type`,
				'RMService',
				fnGetCountryIdByCodeAndCountry (vc.Code ,vc.Description) AS CountryID,
				CodeDeckId,
				Interval1,
				IntervalN
			FROM
			(
				SELECT DISTINCT
					tblTempRateTableRate.Code,
					MAX(tblTempRateTableRate.Description) AS Description,
					MAX(tblTempRateTableRate.`Type`) AS `Type`,
					MAX(tblTempRateTableRate.CodeDeckId) AS CodeDeckId,
					MAX(tblTempRateTableRate.Interval1) AS Interval1,
					MAX(tblTempRateTableRate.IntervalN) AS IntervalN
				FROM tmp_TempRateTableRate_  as tblTempRateTableRate
				LEFT JOIN tblRate
					ON tblRate.Code = tblTempRateTableRate.Code
					AND tblRate.CompanyID = p_companyId
					AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
				WHERE tblRate.RateID IS NULL
					AND tblTempRateTableRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
				GROUP BY
					tblTempRateTableRate.Code
			) vc;

			-- Origination Code
			INSERT INTO tblRate (
				CompanyID,
				Code,
				Description,
				CreatedBy,
				CountryID,
				CodeDeckId,
				Interval1,
				IntervalN
			)
			SELECT DISTINCT
				p_companyId,
				vc.Code,
				vc.Description,
				'RMService',
				fnGetCountryIdByCodeAndCountry (vc.Code ,vc.Description) AS CountryID,
				CodeDeckId,
				Interval1,
				IntervalN
			FROM
			(
				SELECT DISTINCT
					tblTempRateTableRate.OriginationCode AS Code,
					MAX(tblTempRateTableRate.OriginationDescription) AS Description,
					MAX(tblTempRateTableRate.CodeDeckId) AS CodeDeckId,
					MAX(tblTempRateTableRate.Interval1) AS Interval1,
					MAX(tblTempRateTableRate.IntervalN) AS IntervalN
				FROM tmp_TempRateTableRate_  as tblTempRateTableRate
				LEFT JOIN tblRate
					ON tblRate.Code = tblTempRateTableRate.OriginationCode
					AND tblRate.CompanyID = p_companyId
					AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
				WHERE tblRate.RateID IS NULL
					AND tblTempRateTableRate.OriginationCode IS NOT NULL AND tblTempRateTableRate.OriginationCode != ''
					AND tblTempRateTableRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
				GROUP BY
					tblTempRateTableRate.OriginationCode
			) vc;

		ELSE
			SELECT GROUP_CONCAT(code) into errormessage FROM(
				SELECT DISTINCT
					c.Code as code, 1 as a
				FROM
				(
					SELECT DISTINCT
						temp.Code,
						MAX(temp.Description) AS Description
					FROM
					(
						SELECT DISTINCT
							tblTempRateTableRate.Code,
							tblTempRateTableRate.Description
						FROM tmp_TempRateTableRate_  as tblTempRateTableRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableRate.Code
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')

						UNION ALL

						SELECT DISTINCT
							tblTempRateTableRate.OriginationCode AS Code,
							tblTempRateTableRate.OriginationDescription AS Description
						FROM tmp_TempRateTableRate_2  as tblTempRateTableRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableRate.OriginationCode
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
					) temp
					GROUP BY Code
				) c
			) as tbl GROUP BY a;

			IF errormessage IS NOT NULL
			THEN
				INSERT INTO tmp_JobLog_ (Message)
				SELECT DISTINCT
					CONCAT(tbl.Code , ' CODE DOES NOT EXIST IN CODE DECK')
				FROM
				(
					SELECT DISTINCT
						temp.Code,
						MAX(temp.Description) AS Description
					FROM
					(
						SELECT DISTINCT
							tblTempRateTableRate.Code,
							tblTempRateTableRate.Description
						FROM tmp_TempRateTableRate_  as tblTempRateTableRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableRate.Code
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')

						UNION ALL

						SELECT DISTINCT
							tblTempRateTableRate.OriginationCode AS Code,
							tblTempRateTableRate.OriginationDescription AS Description
						FROM tmp_TempRateTableRate_2  as tblTempRateTableRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableRate.OriginationCode
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
					) temp
					GROUP BY Code
				) as tbl;
			END IF;
		END IF;




		UPDATE tblRateTableRate
		INNER JOIN tblRate
			ON tblRate.RateID = tblRateTableRate.RateId
			AND tblRate.CompanyID = p_companyId
		LEFT JOIN tblRate AS OriginationRate
			ON OriginationRate.RateID = tblRateTableRate.OriginationRateID
			AND OriginationRate.CompanyID = p_companyId
		INNER JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND ((tblTempRateTableRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableRate.OriginationCode = OriginationRate.Code))
			AND tblTempRateTableRate.TimezonesID = tblRateTableRate.TimezonesID
			AND tblTempRateTableRate.Change IN ('Delete', 'R', 'D', 'Blocked', 'Block')
		SET tblRateTableRate.EndDate = IFNULL(tblTempRateTableRate.EndDate,date(now()))
		WHERE tblRateTableRate.RateTableId = p_RateTableId
			AND tblRateTableRate.RateId = tblRate.RateId
			AND ((IFNULL(tblRateTableRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableRate.OriginationRateID = OriginationRate.RateID));



		UPDATE tblRate
		JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
			ON 	  tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
			AND tblTempRateTableRate.Code = tblRate.Code
			AND  tblTempRateTableRate.ProcessId = p_processId
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
		SET
			tblRate.Interval1 = tblTempRateTableRate.Interval1,
			tblRate.IntervalN = tblTempRateTableRate.IntervalN
		WHERE
			tblTempRateTableRate.Interval1 IS NOT NULL
			AND tblTempRateTableRate.IntervalN IS NOT NULL
			AND
			(
				tblRate.Interval1 != tblTempRateTableRate.Interval1
				OR
				tblRate.IntervalN != tblTempRateTableRate.IntervalN
			);




		UPDATE tblRateTableRate
		INNER JOIN tblRate
			ON tblRateTableRate.RateId = tblRate.RateId
			AND tblRateTableRate.RateTableId = p_RateTableId
			AND tblRate.CompanyID = p_companyId
		LEFT JOIN tblRate AS OriginationRate
			ON tblRateTableRate.OriginationRateID = OriginationRate.RateId
			AND tblRateTableRate.RateTableId = p_RateTableId
			AND OriginationRate.CompanyID = p_companyId
		INNER JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND ((tblTempRateTableRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableRate.OriginationCode = OriginationRate.Code))
			AND tblTempRateTableRate.TimezonesID = tblRateTableRate.TimezonesID
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
			AND tblRateTableRate.RateId = tblRate.RateId
		SET
			tblRateTableRate.ConnectionFee = tblTempRateTableRate.ConnectionFee,
			tblRateTableRate.Interval1 = tblTempRateTableRate.Interval1,
			tblRateTableRate.IntervalN = tblTempRateTableRate.IntervalN
		WHERE
			tblRateTableRate.RateTableId = p_RateTableId
			AND tblRateTableRate.RateId = tblRate.RateId
			AND ((IFNULL(tblRateTableRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableRate.OriginationRateID = OriginationRate.RateID));




		DELETE tblTempRateTableRate
		FROM tmp_TempRateTableRate_ as tblTempRateTableRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		LEFT JOIN tblRate AS OriginationRate
			ON OriginationRate.Code = tblTempRateTableRate.OriginationCode
			AND OriginationRate.CompanyID = p_companyId
			AND OriginationRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		JOIN tblRateTableRate
			ON tblRateTableRate.RateId = tblRate.RateId
			AND ((IFNULL(tblRateTableRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableRate.OriginationRateID = OriginationRate.RateID))
			AND tblRateTableRate.RateTableId = p_RateTableId
			AND tblRateTableRate.TimezonesID = tblTempRateTableRate.TimezonesID
			AND tblTempRateTableRate.Rate = tblRateTableRate.Rate
		WHERE
			tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
			AND tblRateTableRate.RateId = tblRate.RateId
			AND ((IFNULL(tblRateTableRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableRate.OriginationRateID = OriginationRate.RateID));



		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

		SELECT CurrencyID into v_RateTableCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyID FROM tblRateTable WHERE RateTableId=p_RateTableId);
		SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);



		DROP TEMPORARY TABLE IF EXISTS tmp_PreviousRate;
		CREATE TEMPORARY TABLE `tmp_PreviousRate` (
			`OriginationRateId` int,
			`RateId` int,
			`PreviousRate` decimal(18, 6),
			`EffectiveDate` Datetime
		);

		UPDATE tmp_TempRateTableRate_ as tblTempRateTableRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		LEFT JOIN tblRate AS OriginationRate
			ON OriginationRate.Code = tblTempRateTableRate.OriginationCode
			AND OriginationRate.CompanyID = p_companyId
			AND OriginationRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		JOIN tblRateTableRate
			ON tblRateTableRate.RateId = tblRate.RateId
			AND ((IFNULL(tblRateTableRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableRate.OriginationRateID = OriginationRate.RateID))
			AND tblRateTableRate.RateTableId = p_RateTableId
			AND tblRateTableRate.TimezonesID = tblTempRateTableRate.TimezonesID
		SET tblRateTableRate.EndDate = NOW()
		WHERE tblTempRateTableRate.Rate <> tblRateTableRate.Rate
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
			AND DATE_FORMAT (tblRateTableRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTableRate.EffectiveDate, '%Y-%m-%d');

		INSERT INTO
			tmp_PreviousRate (OriginationRateId,RateId,PreviousRate,EffectiveDate)
		SELECT
			tblRateTableRate.OriginationRateID,tblRateTableRate.RateId,tblRateTableRate.Rate,tblTempRateTableRate.EffectiveDate
		FROM
			tmp_TempRateTableRate_ as tblTempRateTableRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		LEFT JOIN tblRate AS OriginationRate
			ON OriginationRate.Code = tblTempRateTableRate.OriginationCode
			AND OriginationRate.CompanyID = p_companyId
			AND OriginationRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		JOIN tblRateTableRate
			ON tblRateTableRate.RateId = tblRate.RateId
			AND ((IFNULL(tblRateTableRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableRate.OriginationRateID = OriginationRate.RateID))
			AND tblRateTableRate.RateTableId = p_RateTableId
			AND tblRateTableRate.TimezonesID = tblTempRateTableRate.TimezonesID
		WHERE tblTempRateTableRate.Rate <> tblRateTableRate.Rate
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
			AND DATE_FORMAT (tblRateTableRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTableRate.EffectiveDate, '%Y-%m-%d');


		call prc_ArchiveOldRateTableRate(p_RateTableId, NULL,p_UserName);


		INSERT INTO tblRateTableRate (
			RateTableId,
			TimezonesID,
			OriginationRateID,
			RateId,
			Rate,
			RateN,
			EffectiveDate,
			EndDate,
			ConnectionFee,
			Interval1,
			IntervalN,
			Preference,
			Blocked,
			RoutingCategoryID,
			PreviousRate,
			ApprovedStatus,
			RateCurrency,
			ConnectionFeeCurrency
		)
		SELECT DISTINCT
			p_RateTableId,
			tblTempRateTableRate.TimezonesID,
			IFNULL(OriginationRate.RateID,0) AS OriginationRateID,
			tblRate.RateID,
			IF (
				p_CurrencyID > 0,
				CASE WHEN p_CurrencyID = v_RateTableCurrencyID_
				THEN
					tblTempRateTableRate.Rate
				WHEN  p_CurrencyID = v_CompanyCurrencyID_
				THEN
				(
					( tblTempRateTableRate.Rate  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ and CompanyID = p_companyId ) )
				)
				ELSE
				(
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId )
					*
					(tblTempRateTableRate.Rate  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))
				)
				END ,
				tblTempRateTableRate.Rate
			) AS Rate,
			IF (
				p_CurrencyID > 0,
				CASE WHEN p_CurrencyID = v_RateTableCurrencyID_
				THEN
					tblTempRateTableRate.RateN
				WHEN  p_CurrencyID = v_CompanyCurrencyID_
				THEN
				(
					( tblTempRateTableRate.RateN  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ and CompanyID = p_companyId ) )
				)
				ELSE
				(
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId )
					*
					(tblTempRateTableRate.RateN  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))
				)
				END ,
				tblTempRateTableRate.Rate
			) AS RateN,
			tblTempRateTableRate.EffectiveDate,
			tblTempRateTableRate.EndDate,
			tblTempRateTableRate.ConnectionFee,
			tblTempRateTableRate.Interval1,
			tblTempRateTableRate.IntervalN,
			tblTempRateTableRate.Preference,
			tblTempRateTableRate.Blocked,
			tblTempRateTableRate.RoutingCategoryID,
			IFNULL(tmp_PreviousRate.PreviousRate,0) AS PreviousRate,
			 -- if rate table is not vendor rate table and Rate Approval Process is on then rate will be upload as not approved
			IF(v_RateTableAppliedTo_!=2,IF(v_RateApprovalProcess_=1,0,1),1) AS ApprovedStatus,
			tblTempRateTableRate.RateCurrency,
			tblTempRateTableRate.ConnectionFeeCurrency
		FROM tmp_TempRateTableRate_ as tblTempRateTableRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		LEFT JOIN tblRate AS OriginationRate
			ON OriginationRate.Code = tblTempRateTableRate.OriginationCode
			AND OriginationRate.CompanyID = p_companyId
			AND OriginationRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		LEFT JOIN tblRateTableRate
			ON tblRate.RateID = tblRateTableRate.RateId
			AND ((IFNULL(tblRateTableRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableRate.OriginationRateID = OriginationRate.RateID))
			AND tblRateTableRate.RateTableId = p_RateTableId
			AND tblRateTableRate.TimezonesID = tblTempRateTableRate.TimezonesID
			AND tblTempRateTableRate.EffectiveDate = tblRateTableRate.EffectiveDate
		LEFT JOIN tmp_PreviousRate
			ON ((IFNULL(tmp_PreviousRate.OriginationRateID,0) = 0 AND OriginationRate.RateId IS NULL) OR (OriginationRate.RateId = tmp_PreviousRate.OriginationRateId))
			AND tblRate.RateId = tmp_PreviousRate.RateId AND tblTempRateTableRate.EffectiveDate = tmp_PreviousRate.EffectiveDate
		WHERE tblRateTableRate.RateTableRateID IS NULL
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
			AND tblTempRateTableRate.EffectiveDate >= DATE_FORMAT (NOW(), '%Y-%m-%d');

		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();



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
			from 	tblRateTableRate
			WHERE
				RateTableId = p_RateTableId
			Group By EffectiveDate
			order by EffectiveDate desc
		) tmp
		,(SELECT @row_num := 0) x;


		SET v_pointer_ = 1;
		SET v_rowCount_ = ( SELECT COUNT(*) FROM tmp_EffectiveDates_ );

		IF v_rowCount_ > 0 THEN

			WHILE v_pointer_ <= v_rowCount_
			DO
				SET @EffectiveDate = ( SELECT EffectiveDate FROM tmp_EffectiveDates_ WHERE RowID = v_pointer_ );
				SET @row_num = 0;

				UPDATE  tblRateTableRate vr1
				inner join
				(
					select
						RateTableId,
						OriginationRateID,
						RateID,
						EffectiveDate,
						TimezonesID
					FROM tblRateTableRate
					WHERE RateTableId = p_RateTableId
						AND EffectiveDate =   @EffectiveDate
					order by EffectiveDate desc
				) tmpvr
				on
					vr1.RateTableId = tmpvr.RateTableId
					AND vr1.OriginationRateID = tmpvr.OriginationRateID
					AND vr1.RateID = tmpvr.RateID
					AND vr1.TimezonesID = tmpvr.TimezonesID
					AND vr1.EffectiveDate < tmpvr.EffectiveDate
				SET
					vr1.EndDate = @EffectiveDate
				where
					vr1.RateTableId = p_RateTableId

					AND vr1.EndDate is null;


				SET v_pointer_ = v_pointer_ + 1;

			END WHILE;

		END IF;

	END IF;

	INSERT INTO tmp_JobLog_ (Message) 	 	SELECT CONCAT(v_AffectedRecords_ , ' Records Uploaded ' );


	call prc_RateTableRateUpdatePreviousRate(p_RateTableId,'');

	call prc_ArchiveOldRateTableRate(p_RateTableId, NULL,p_UserName);


	DELETE  FROM tblTempRateTableRate WHERE  ProcessId = p_processId;
	DELETE  FROM tblRateTableRateChangeLog WHERE ProcessID = p_processId;
	SELECT * FROM tmp_JobLog_;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_WSProcessRateTableRateAA`;
DELIMITER //
CREATE PROCEDURE `prc_WSProcessRateTableRateAA`(
	IN `p_RateTableId` INT,
	IN `p_replaceAllRates` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_processId` VARCHAR(200),
	IN `p_addNewCodesToCodeDeck` INT,
	IN `p_companyId` INT,
	IN `p_forbidden` INT,
	IN `p_preference` INT,
	IN `p_dialstringid` INT,
	IN `p_dialcodeSeparator` VARCHAR(50),
	IN `p_seperatecolumn` INT,
	IN `p_CurrencyID` INT,
	IN `p_list_option` INT,
	IN `p_UserName` TEXT
)
ThisSP:BEGIN

	DECLARE v_AffectedRecords_ INT DEFAULT 0;
	DECLARE v_CodeDeckId_ INT ;
	DECLARE totaldialstringcode INT(11) DEFAULT 0;
	DECLARE newstringcode INT(11) DEFAULT 0;
	DECLARE totalduplicatecode INT(11);
	DECLARE errormessage longtext;
	DECLARE errorheader longtext;
	DECLARE v_RateTableCurrencyID_ INT;
	DECLARE v_CompanyCurrencyID_ INT;

	DECLARE v_pointer_ INT;
	DECLARE v_rowCount_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
	CREATE TEMPORARY TABLE tmp_JobLog_ (
		Message longtext
	);

    DROP TEMPORARY TABLE IF EXISTS tmp_TempTimezones_;
    CREATE TEMPORARY TABLE tmp_TempTimezones_ (
        TimezonesID INT
    );

	DROP TEMPORARY TABLE IF EXISTS tmp_split_RateTableRate_;
	CREATE TEMPORARY TABLE tmp_split_RateTableRate_ (
		`TempRateTableRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Type` varchar(50) NULL DEFAULT NULL,
		`Rate` decimal(18, 6) ,
		`RateN` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Blocked` tinyint,
		`RoutingCategoryID` int,
		`RateCurrency` INT(11) NULL DEFAULT NULL,
		`ConnectionFeeCurrency` INT(11) NULL DEFAULT NULL,
		`DialStringPrefix` varchar(500) ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_OriginationCode (`OriginationCode`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableRate_;
	CREATE TEMPORARY TABLE tmp_TempRateTableRate_ (
		TempRateTableRateID int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Type` varchar(50) NULL DEFAULT NULL,
		`Rate` decimal(18, 6) ,
		`RateN` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Blocked` tinyint,
		`RoutingCategoryID` int,
		`RateCurrency` INT(11) NULL DEFAULT NULL,
		`ConnectionFeeCurrency` INT(11) NULL DEFAULT NULL,
		`DialStringPrefix` varchar(500) ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_OriginationCode (`OriginationCode`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_Delete_RateTableRate;
	CREATE TEMPORARY TABLE tmp_Delete_RateTableRate (
		RateTableRateID INT,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Type` varchar(50) NULL DEFAULT NULL,
		`Rate` decimal(18, 6) ,
		`RateN` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Blocked` tinyint,
		`RoutingCategoryID` int,
		`RateCurrency` INT(11) NULL DEFAULT NULL,
		`ConnectionFeeCurrency` INT(11) NULL DEFAULT NULL,
		`DialStringPrefix` varchar(500) ,
		INDEX tmp_RateTableRateDiscontinued_RateTableRateID (`RateTableRateID`)
	);


	CALL  prc_RateTableCheckDialstringAndDupliacteCode(p_companyId,p_processId,p_dialstringid,p_effectiveImmediately,p_dialcodeSeparator,p_seperatecolumn);

	SELECT COUNT(*) AS COUNT INTO newstringcode from tmp_JobLog_;


 	INSERT INTO tmp_TempTimezones_
 	SELECT DISTINCT TimezonesID from tmp_TempRateTableRate_;


	IF newstringcode = 0
	THEN

		IF (SELECT count(*) FROM tblRateTableRateChangeLog WHERE ProcessID = p_processId ) > 0
		THEN

			UPDATE tmp_TempRateTableRate_ tblTempRateTableRate
			JOIN tblRateTableRateChangeLog vrcl
				ON  vrcl.ProcessId = p_processId
				AND vrcl.Code = tblTempRateTableRate.Code
				AND vrcl.OriginationCode = tblTempRateTableRate.OriginationCode
			SET
				tblTempRateTableRate.Interval1 = vrcl.Interval1 ,
				tblTempRateTableRate.IntervalN = vrcl.IntervalN
			WHERE
				vrcl.`Action` = 'New'
				AND vrcl.Interval1 IS NOT NULL
				AND vrcl.IntervalN IS NOT NULL ;

		END IF;


		IF p_list_option = 1
		THEN

			INSERT INTO tmp_Delete_RateTableRate(
				RateTableRateID,
				CodeDeckId,
				TimezonesID,
				OriginationCode,
				OriginationDescription,
				Code,
				Description,
				Rate,
				RateN,
				EffectiveDate,
				EndDate,
				`Change`,
				ProcessId,
				Preference,
				ConnectionFee,
				Interval1,
				IntervalN,
				Blocked,
				RoutingCategoryID,
				RateCurrency,
				ConnectionFeeCurrency,
				DialStringPrefix
			)
			SELECT DISTINCT
				tblRateTableRate.RateTableRateID,
				tblRateTable.CodeDeckId,
				tblRateTableRate.TimezonesID,
				OriginationRate.Code AS OriginationCode,
				OriginationRate.Description AS OriginationDescription,
				tblRate.Code,
				tblRate.Description,
				tblRateTableRate.Rate,
				tblRateTableRate.RateN,
				tblRateTableRate.EffectiveDate,
				NULL AS EndDate,
				'Delete' AS `Change`,
				p_processId AS ProcessId,
				tblRateTableRate.Preference,
				tblRateTableRate.ConnectionFee,
				tblRateTableRate.Interval1,
				tblRateTableRate.IntervalN,
				tblRateTableRate.Blocked,
				tblRateTableRate.RoutingCategoryID,
				tblRateTableRate.RateCurrency,
				tblRateTableRate.ConnectionFeeCurrency,
				'' AS DialStringPrefix
			FROM tblRateTableRate
			JOIN tblRateTable
				ON tblRateTable.RateTableId = tblRateTableRate.RateTableId
			JOIN tblRate
				ON tblRate.RateID = tblRateTableRate.RateId
				AND tblRate.CompanyID = p_companyId
			LEFT JOIN tblRate AS OriginationRate
				ON OriginationRate.RateID = tblRateTableRate.OriginationRateID
				AND OriginationRate.CompanyID = p_companyId
		  	/*JOIN tmp_TempTimezones_
		  		ON tmp_TempTimezones_.TimezonesID = tblRateTableRate.TimezonesID*/
			LEFT JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
				ON tblTempRateTableRate.Code = tblRate.Code
				AND ((tblTempRateTableRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableRate.OriginationCode = OriginationRate.Code))
				AND tblTempRateTableRate.TimezonesID = tblRateTableRate.TimezonesID
				AND  tblTempRateTableRate.ProcessId = p_processId
				AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
			WHERE tblRateTableRate.RateTableId = p_RateTableId
				AND tblTempRateTableRate.Code IS NULL
				AND ( tblRateTableRate.EndDate is NULL OR tblRateTableRate.EndDate <= date(now()) )
			ORDER BY RateTableRateID ASC;



		END IF;



		DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableRate_2;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempRateTableRate_2 AS (SELECT * FROM tmp_TempRateTableRate_);

		IF  p_addNewCodesToCodeDeck = 1
		THEN
			-- Destination Code
			INSERT INTO tblRate (
				CompanyID,
				Code,
				Description,
				`Type`,
				CreatedBy,
				CountryID,
				CodeDeckId,
				Interval1,
				IntervalN
			)
			SELECT DISTINCT
				p_companyId,
				vc.Code,
				vc.Description,
				vc.`Type`,
				'RMService',
				fnGetCountryIdByCodeAndCountry (vc.Code ,vc.Description) AS CountryID,
				CodeDeckId,
				Interval1,
				IntervalN
			FROM
			(
				SELECT DISTINCT
					tblTempRateTableRate.Code,
					MAX(tblTempRateTableRate.Description) AS Description,
					MAX(tblTempRateTableRate.`Type`) AS `Type`,
					MAX(tblTempRateTableRate.CodeDeckId) AS CodeDeckId,
					MAX(tblTempRateTableRate.Interval1) AS Interval1,
					MAX(tblTempRateTableRate.IntervalN) AS IntervalN
				FROM tmp_TempRateTableRate_  as tblTempRateTableRate
				LEFT JOIN tblRate
					ON tblRate.Code = tblTempRateTableRate.Code
					AND tblRate.CompanyID = p_companyId
					AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
				WHERE tblRate.RateID IS NULL
					AND tblTempRateTableRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
				GROUP BY
					tblTempRateTableRate.Code
			) vc;

			-- Origination Code
			INSERT INTO tblRate (
				CompanyID,
				Code,
				Description,
				CreatedBy,
				CountryID,
				CodeDeckId,
				Interval1,
				IntervalN
			)
			SELECT DISTINCT
				p_companyId,
				vc.Code,
				vc.Description,
				'RMService',
				fnGetCountryIdByCodeAndCountry (vc.Code ,vc.Description) AS CountryID,
				CodeDeckId,
				Interval1,
				IntervalN
			FROM
			(
				SELECT DISTINCT
					tblTempRateTableRate.OriginationCode AS Code,
					MAX(tblTempRateTableRate.OriginationDescription) AS Description,
					MAX(tblTempRateTableRate.CodeDeckId) AS CodeDeckId,
					MAX(tblTempRateTableRate.Interval1) AS Interval1,
					MAX(tblTempRateTableRate.IntervalN) AS IntervalN
				FROM tmp_TempRateTableRate_  as tblTempRateTableRate
				LEFT JOIN tblRate
					ON tblRate.Code = tblTempRateTableRate.OriginationCode
					AND tblRate.CompanyID = p_companyId
					AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
				WHERE tblRate.RateID IS NULL
					AND tblTempRateTableRate.OriginationCode IS NOT NULL AND tblTempRateTableRate.OriginationCode != ''
					AND tblTempRateTableRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
				GROUP BY
					tblTempRateTableRate.OriginationCode
			) vc;

		ELSE
			SELECT GROUP_CONCAT(code) into errormessage FROM(
				SELECT DISTINCT
					c.Code as code, 1 as a
				FROM
				(
					SELECT DISTINCT
						temp.Code,
						MAX(temp.Description) AS Description
					FROM
					(
						SELECT DISTINCT
							tblTempRateTableRate.Code,
							tblTempRateTableRate.Description
						FROM tmp_TempRateTableRate_  as tblTempRateTableRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableRate.Code
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')

						UNION ALL

						SELECT DISTINCT
							tblTempRateTableRate.OriginationCode AS Code,
							tblTempRateTableRate.OriginationDescription AS Description
						FROM tmp_TempRateTableRate_2  as tblTempRateTableRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableRate.OriginationCode
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
					) temp
					GROUP BY Code
				) c
			) as tbl GROUP BY a;

			IF errormessage IS NOT NULL
			THEN
				INSERT INTO tmp_JobLog_ (Message)
				SELECT DISTINCT
					CONCAT(tbl.Code , ' CODE DOES NOT EXIST IN CODE DECK')
				FROM
				(
					SELECT DISTINCT
						temp.Code,
						MAX(temp.Description) AS Description
					FROM
					(
						SELECT DISTINCT
							tblTempRateTableRate.Code,
							tblTempRateTableRate.Description
						FROM tmp_TempRateTableRate_  as tblTempRateTableRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableRate.Code
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')

						UNION ALL

						SELECT DISTINCT
							tblTempRateTableRate.OriginationCode AS Code,
							tblTempRateTableRate.OriginationDescription AS Description
						FROM tmp_TempRateTableRate_2  as tblTempRateTableRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableRate.OriginationCode
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
					) temp
					GROUP BY Code
				) as tbl;
			END IF;
		END IF;



		UPDATE tblRate
		JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
			ON 	  tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
			AND tblTempRateTableRate.Code = tblRate.Code
			AND  tblTempRateTableRate.ProcessId = p_processId
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
		SET
			tblRate.Interval1 = tblTempRateTableRate.Interval1,
			tblRate.IntervalN = tblTempRateTableRate.IntervalN
		WHERE
			tblTempRateTableRate.Interval1 IS NOT NULL
			AND tblTempRateTableRate.IntervalN IS NOT NULL
			AND
			(
				tblRate.Interval1 != tblTempRateTableRate.Interval1
				OR
				tblRate.IntervalN != tblTempRateTableRate.IntervalN
			);


		DELETE tblTempRateTableRate
		FROM tmp_TempRateTableRate_ as tblTempRateTableRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		LEFT JOIN tblRate AS OriginationRate
			ON OriginationRate.Code = tblTempRateTableRate.OriginationCode
			AND OriginationRate.CompanyID = p_companyId
			AND OriginationRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		JOIN tblRateTableRateAA AS tblRateTableRate
			ON tblRateTableRate.RateId = tblRate.RateId
			AND ((IFNULL(tblRateTableRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableRate.OriginationRateID = OriginationRate.RateID))
			AND tblRateTableRate.RateTableId = p_RateTableId
			AND tblRateTableRate.TimezonesID = tblTempRateTableRate.TimezonesID
			AND tblTempRateTableRate.Rate = tblRateTableRate.Rate
		WHERE
			tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
			AND tblRateTableRate.RateId = tblRate.RateId
			AND ((IFNULL(tblRateTableRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableRate.OriginationRateID = OriginationRate.RateID));



	--	SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

		SELECT CurrencyID into v_RateTableCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyID FROM tblRateTable WHERE RateTableId=p_RateTableId);
		SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);


		-- delete from live table if code is already exist but rate is different
		/*UPDATE tmp_TempRateTableRate_ as tblTempRateTableRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		JOIN tblRateTableRateAA AS tblRateTableRate
			ON tblRateTableRate.RateId = tblRate.RateId
			AND tblRateTableRate.RateTableId = p_RateTableId
			AND tblRateTableRate.TimezonesID = tblTempRateTableRate.TimezonesID
		SET tblRateTableRate.EndDate = NOW()
		WHERE tblTempRateTableRate.Rate <> tblRateTableRate.Rate
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
			AND DATE_FORMAT (tblRateTableRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTableRate.EffectiveDate, '%Y-%m-%d');*/


		-- call prc_ArchiveOldRateTableRateAA(p_RateTableId, NULL,p_UserName);



		INSERT INTO tblRateTableRateAA (
			RateTableId,
			TimezonesID,
			OriginationRateID,
			RateId,
			Rate,
			RateN,
			EffectiveDate,
			EndDate,
			ConnectionFee,
			Interval1,
			IntervalN,
			Preference,
			Blocked,
			RoutingCategoryID,
			PreviousRate,
			ApprovedStatus,
			RateCurrency,
			ConnectionFeeCurrency
		)
		SELECT DISTINCT
			p_RateTableId,
			tblTempRateTableRate.TimezonesID,
			IFNULL(OriginationRate.RateID,0) AS OriginationRateID,
			tblRate.RateID,
			IF (
				p_CurrencyID > 0,
				CASE WHEN p_CurrencyID = v_RateTableCurrencyID_
				THEN
					tblTempRateTableRate.Rate
				WHEN  p_CurrencyID = v_CompanyCurrencyID_
				THEN
				(
					( tblTempRateTableRate.Rate  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ and CompanyID = p_companyId ) )
				)
				ELSE
				(
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId )
					*
					(tblTempRateTableRate.Rate  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))
				)
				END ,
				tblTempRateTableRate.Rate
			) AS Rate,
			IF (
				p_CurrencyID > 0,
				CASE WHEN p_CurrencyID = v_RateTableCurrencyID_
				THEN
					tblTempRateTableRate.RateN
				WHEN  p_CurrencyID = v_CompanyCurrencyID_
				THEN
				(
					( tblTempRateTableRate.RateN  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ and CompanyID = p_companyId ) )
				)
				ELSE
				(
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId )
					*
					(tblTempRateTableRate.RateN  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))
				)
				END ,
				tblTempRateTableRate.Rate
			) AS RateN,
			tblTempRateTableRate.EffectiveDate,
			tblTempRateTableRate.EndDate,
			tblTempRateTableRate.ConnectionFee,
			tblTempRateTableRate.Interval1,
			tblTempRateTableRate.IntervalN,
			tblTempRateTableRate.Preference,
			tblTempRateTableRate.Blocked,
			tblTempRateTableRate.RoutingCategoryID,
			0 AS PreviousRate,
			0 AS ApprovedStatus,
			tblTempRateTableRate.RateCurrency,
			tblTempRateTableRate.ConnectionFeeCurrency
		FROM tmp_TempRateTableRate_ as tblTempRateTableRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		LEFT JOIN tblRate AS OriginationRate
			ON OriginationRate.Code = tblTempRateTableRate.OriginationCode
			AND OriginationRate.CompanyID = p_companyId
			AND OriginationRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		/*LEFT JOIN tblRateTableRateAA AS tblRateTableRate
			ON tblRate.RateID = tblRateTableRate.RateId
			AND ((IFNULL(tblRateTableRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableRate.OriginationRateID = OriginationRate.RateID))
			AND tblRateTableRate.RateTableId = p_RateTableId
			AND tblRateTableRate.TimezonesID = tblTempRateTableRate.TimezonesID
			AND tblTempRateTableRate.EffectiveDate = tblRateTableRate.EffectiveDate*/
		WHERE /*tblRateTableRate.RateTableRateAAID IS NULL
			AND*/ tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
			AND tblTempRateTableRate.EffectiveDate >= DATE_FORMAT (NOW(), '%Y-%m-%d');

		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();


		IF((SELECT COUNT(*) FROM tmp_Delete_RateTableRate) > 0)
		THEN
			-- rates which needs to deleted
			INSERT INTO tblRateTableRateAA (
				RateTableRateID,
				RateTableId,
				TimezonesID,
				OriginationRateID,
				RateId,
				Rate,
				RateN,
				EffectiveDate,
				EndDate,
				ConnectionFee,
				Interval1,
				IntervalN,
				Preference,
				Blocked,
				RoutingCategoryID,
				PreviousRate,
				ApprovedStatus,
				RateCurrency,
				ConnectionFeeCurrency
			)
			SELECT DISTINCT
				tblTempRateTableRate.RateTableRateID,
				p_RateTableId,
				tblTempRateTableRate.TimezonesID,
				IFNULL(OriginationRate.RateID,0) AS OriginationRateID,
				tblRate.RateID,
				IF (
					p_CurrencyID > 0,
					CASE WHEN p_CurrencyID = v_RateTableCurrencyID_
					THEN
						tblTempRateTableRate.Rate
					WHEN  p_CurrencyID = v_CompanyCurrencyID_
					THEN
					(
						( tblTempRateTableRate.Rate  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ and CompanyID = p_companyId ) )
					)
					ELSE
					(
						(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId )
						*
						(tblTempRateTableRate.Rate  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))
					)
					END ,
					tblTempRateTableRate.Rate
				) AS Rate,
				IF (
					p_CurrencyID > 0,
					CASE WHEN p_CurrencyID = v_RateTableCurrencyID_
					THEN
						tblTempRateTableRate.RateN
					WHEN  p_CurrencyID = v_CompanyCurrencyID_
					THEN
					(
						( tblTempRateTableRate.RateN  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ and CompanyID = p_companyId ) )
					)
					ELSE
					(
						(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId )
						*
						(tblTempRateTableRate.RateN  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))
					)
					END ,
					tblTempRateTableRate.Rate
				) AS RateN,
				tblTempRateTableRate.EffectiveDate,
				tblTempRateTableRate.EndDate,
				tblTempRateTableRate.ConnectionFee,
				tblTempRateTableRate.Interval1,
				tblTempRateTableRate.IntervalN,
				tblTempRateTableRate.Preference,
				tblTempRateTableRate.Blocked,
				tblTempRateTableRate.RoutingCategoryID,
				0 AS PreviousRate,
				3 AS ApprovedStatus, -- delete status
				tblTempRateTableRate.RateCurrency,
				tblTempRateTableRate.ConnectionFeeCurrency
			FROM tmp_Delete_RateTableRate as tblTempRateTableRate
			JOIN tblRate
				ON tblRate.Code = tblTempRateTableRate.Code
				AND tblRate.CompanyID = p_companyId
				AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
			LEFT JOIN tblRate AS OriginationRate
				ON OriginationRate.Code = tblTempRateTableRate.OriginationCode
				AND OriginationRate.CompanyID = p_companyId
				AND OriginationRate.CodeDeckId = tblTempRateTableRate.CodeDeckId;


			SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

		END IF; -- END IF((SELECT COUNT(*) FROM tmp_Delete_RateTableRate) > 0)

	END IF; -- IF newstringcode = 0

	INSERT INTO tmp_JobLog_ (Message) 	 	SELECT CONCAT(v_AffectedRecords_ , ' Records Affected ' );

	call prc_ArchiveOldRateTableRateAA(p_RateTableId, NULL,p_UserName);


	DELETE  FROM tblTempRateTableRate WHERE  ProcessId = p_processId;
	DELETE  FROM tblRateTableRateChangeLog WHERE ProcessID = p_processId;
	SELECT * FROM tmp_JobLog_;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_RateTableCheckDialstringAndDupliacteCode`;
DELIMITER //
CREATE PROCEDURE `prc_RateTableCheckDialstringAndDupliacteCode`(
	IN `p_companyId` INT,
	IN `p_processId` VARCHAR(200) ,
	IN `p_dialStringId` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_dialcodeSeparator` VARCHAR(50),
	IN `p_seperatecolumn` INT
)
ThisSP:BEGIN

	DECLARE totaldialstringcode INT(11) DEFAULT 0;
	DECLARE v_CodeDeckId_ INT ;
	DECLARE totalduplicatecode INT(11);
	DECLARE errormessage longtext;
	DECLARE errorheader longtext;

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRateDialString_ ;
	CREATE TEMPORARY TABLE `tmp_RateTableRateDialString_` (
		`TempRateTableRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Type` varchar(50) NULL DEFAULT NULL,
		`Rate` decimal(18, 6) ,
		`RateN` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Blocked` tinyint,
		`RoutingCategoryID` int,
		`RateCurrency` INT(11) NULL DEFAULT NULL,
		`ConnectionFeeCurrency` INT(11) NULL DEFAULT NULL,
		`DialStringPrefix` varchar(500),
		INDEX IX_orogination_code (OriginationCode),
		INDEX IX_origination_description (OriginationDescription),
		INDEX IX_code (code),
		INDEX IX_CodeDeckId (CodeDeckId),
		INDEX IX_Description (Description),
		INDEX IX_EffectiveDate (EffectiveDate),
		INDEX IX_DialStringPrefix (DialStringPrefix)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRateDialString_2 ;
	CREATE TEMPORARY TABLE `tmp_RateTableRateDialString_2` (
		`TempRateTableRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Type` varchar(50) NULL DEFAULT NULL,
		`Rate` decimal(18, 6) ,
		`RateN` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Blocked` tinyint,
		`RoutingCategoryID` int,
		`RateCurrency` INT(11) NULL DEFAULT NULL,
		`ConnectionFeeCurrency` INT(11) NULL DEFAULT NULL,
		`DialStringPrefix` varchar(500),
		INDEX IX_orogination_code (OriginationCode),
		INDEX IX_origination_description (OriginationDescription),
		INDEX IX_code (code),
		INDEX IX_CodeDeckId (CodeDeckId),
		INDEX IX_Description (Description),
		INDEX IX_EffectiveDate (EffectiveDate),
		INDEX IX_DialStringPrefix (DialStringPrefix)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRateDialString_3 ;
	CREATE TEMPORARY TABLE `tmp_RateTableRateDialString_3` (
		`TempRateTableRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Type` varchar(50) NULL DEFAULT NULL,
		`Rate` decimal(18, 6) ,
		`RateN` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Forbidden` varchar(100) ,
		`RoutingCategoryID` int,
		`RateCurrency` INT(11) NULL DEFAULT NULL,
		`ConnectionFeeCurrency` INT(11) NULL DEFAULT NULL,
		`DialStringPrefix` varchar(500),
		INDEX IX_orogination_code (OriginationCode),
		INDEX IX_origination_description (OriginationDescription),
		INDEX IX_code (code),
		INDEX IX_CodeDeckId (CodeDeckId),
		INDEX IX_Description (Description),
		INDEX IX_EffectiveDate (EffectiveDate),
		INDEX IX_DialStringPrefix (DialStringPrefix)
	);

	CALL prc_SplitRateTableRate(p_processId,p_dialcodeSeparator,p_seperatecolumn);

	IF  p_effectiveImmediately = 1
	THEN
		UPDATE tmp_split_RateTableRate_
		SET EffectiveDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EffectiveDate < DATE_FORMAT (NOW(), '%Y-%m-%d');

		UPDATE tmp_split_RateTableRate_
		SET EndDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EndDate < DATE_FORMAT (NOW(), '%Y-%m-%d');
	END IF;

	DROP TEMPORARY TABLE IF EXISTS tmp_split_RateTableRate_2;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_split_RateTableRate_2 as (SELECT * FROM tmp_split_RateTableRate_);

	DELETE n1 FROM tmp_split_RateTableRate_ n1
	INNER JOIN
	(
		SELECT MAX(TempRateTableRateID) AS TempRateTableRateID,EffectiveDate,OriginationCode,Code,DialStringPrefix,TimezonesID,Rate
		FROM tmp_split_RateTableRate_2 WHERE ProcessId = p_processId
		GROUP BY OriginationCode,Code,EffectiveDate,DialStringPrefix,TimezonesID,Rate
		HAVING COUNT(*)>1
	)n2
	ON n1.Code = n2.Code
	AND ((n1.OriginationCode IS NULL AND n2.OriginationCode IS NULL) OR (n1.OriginationCode = n2.OriginationCode))
	AND n2.EffectiveDate = n1.EffectiveDate
	AND ((n2.DialStringPrefix IS NULL AND n1.DialStringPrefix IS NULL) OR (n2.DialStringPrefix = n1.DialStringPrefix))
	AND n2.TimezonesID = n1.TimezonesID
	AND n2.Rate = n1.Rate
	AND n1.TempRateTableRateID < n2.TempRateTableRateID
	WHERE n1.ProcessId = p_processId;

	INSERT INTO tmp_TempRateTableRate_
	SELECT DISTINCT
		`TempRateTableRateID`,
		`CodeDeckId`,
		`TimezonesID`,
		`OriginationCode`,
		`OriginationDescription`,
		`Code`,
		`Description`,
		`Type`,
		`Rate`,
		`RateN`,
		`EffectiveDate`,
		`EndDate`,
		`Change`,
		`ProcessId`,
		`Preference`,
		`ConnectionFee`,
		`Interval1`,
		`IntervalN`,
		`Blocked`,
		`RoutingCategoryID`,
		`RateCurrency`,
		`ConnectionFeeCurrency`,
		`DialStringPrefix`
	FROM tmp_split_RateTableRate_
	WHERE tmp_split_RateTableRate_.ProcessId = p_processId;

	SELECT CodeDeckId INTO v_CodeDeckId_
	FROM tmp_TempRateTableRate_
	WHERE ProcessId = p_processId  LIMIT 1;

	UPDATE tmp_TempRateTableRate_ as tblTempRateTableRate
	LEFT JOIN tblRate
		ON tblRate.Code = tblTempRateTableRate.Code
		AND tblRate.CompanyID = p_companyId
		AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		AND tblRate.CodeDeckId =  v_CodeDeckId_
	SET
		tblTempRateTableRate.Interval1 = CASE WHEN tblTempRateTableRate.Interval1 is not null  and tblTempRateTableRate.Interval1 > 0
		THEN
			tblTempRateTableRate.Interval1
		ELSE
			CASE WHEN tblRate.Interval1 is not null
			THEN
				tblRate.Interval1
			ELSE
				1
			END
		END,
		tblTempRateTableRate.IntervalN = CASE WHEN tblTempRateTableRate.IntervalN is not null  and tblTempRateTableRate.IntervalN > 0
		THEN
			tblTempRateTableRate.IntervalN
		ELSE
			CASE WHEN tblRate.IntervalN is not null
			THEN
				tblRate.IntervalN
			ElSE
				1
			END
		END;

	IF  p_effectiveImmediately = 1
	THEN
		UPDATE tmp_TempRateTableRate_
		SET EffectiveDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EffectiveDate < DATE_FORMAT (NOW(), '%Y-%m-%d');

		UPDATE tmp_TempRateTableRate_
		SET EndDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EndDate < DATE_FORMAT (NOW(), '%Y-%m-%d');
	END IF;

	SELECT count(*) INTO totalduplicatecode FROM(
	SELECT count(code) as c,code FROM tmp_TempRateTableRate_  GROUP BY OriginationCode,Code,EffectiveDate,DialStringPrefix,TimezonesID HAVING c>1) AS tbl;

	IF  totalduplicatecode > 0
	THEN

		SELECT GROUP_CONCAT(code) into errormessage FROM(
		SELECT DISTINCT OriginationCode,Code, 1 as a FROM(
		SELECT count(TempRateTableRateID) as c, OriginationCode, Code FROM tmp_TempRateTableRate_  GROUP BY OriginationCode,Code,EffectiveDate,DialStringPrefix,TimezonesID HAVING c>1) AS tbl) as tbl2 GROUP by a;

		INSERT INTO tmp_JobLog_ (Message)
		SELECT DISTINCT
			CONCAT(IF(OriginationCode IS NOT NULL,CONCAT(OriginationCode,'-'),''), Code, ' DUPLICATE CODE')
		FROM(
			SELECT count(TempRateTableRateID) as c, OriginationCode, Code FROM tmp_TempRateTableRate_  GROUP BY OriginationCode,Code,EffectiveDate,DialStringPrefix,TimezonesID HAVING c>1) AS tbl;
	END IF;

	IF	totalduplicatecode = 0
	THEN

		IF p_dialstringid >0
		THEN

			DROP TEMPORARY TABLE IF EXISTS tmp_DialString_;
			CREATE TEMPORARY TABLE tmp_DialString_ (
				`DialStringID` INT,
				`DialString` VARCHAR(250),
				`ChargeCode` VARCHAR(250),
				`Description` VARCHAR(250),
				`Forbidden` VARCHAR(50),
				INDEX tmp_DialStringID (`DialStringID`),
				INDEX tmp_DialStringID_ChargeCode (`DialStringID`,`ChargeCode`)
			);

			INSERT INTO tmp_DialString_
			SELECT DISTINCT
				`DialStringID`,
				`DialString`,
				`ChargeCode`,
				`Description`,
				`Forbidden`
			FROM tblDialStringCode
			WHERE DialStringID = p_dialstringid;

			SELECT  COUNT(*) as count INTO totaldialstringcode
			FROM tmp_TempRateTableRate_ vr
			LEFT JOIN tmp_DialString_ ds
				ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  ))
			WHERE vr.ProcessId = p_processId
				AND ds.DialStringID IS NULL
				AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

			IF totaldialstringcode > 0
			THEN
				INSERT INTO tblDialStringCode (DialStringID,DialString,ChargeCode,created_by)
				  SELECT DISTINCT p_dialStringId,vr.DialStringPrefix, Code, 'RMService'
					FROM tmp_TempRateTableRate_ vr
						LEFT JOIN tmp_DialString_ ds
							ON vr.DialStringPrefix = ds.DialString AND ds.DialStringID = p_dialStringId
						WHERE vr.ProcessId = p_processId
							AND ds.DialStringID IS NULL
							AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

				TRUNCATE tmp_DialString_;
				INSERT INTO tmp_DialString_
					SELECT DISTINCT
						`DialStringID`,
						`DialString`,
						`ChargeCode`,
						`Description`,
						`Forbidden`
					FROM tblDialStringCode
						WHERE DialStringID = p_dialstringid;

				SELECT  COUNT(*) as count INTO totaldialstringcode
				FROM tmp_TempRateTableRate_ vr
					LEFT JOIN tmp_DialString_ ds
						ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  ))
					WHERE vr.ProcessId = p_processId
						AND ds.DialStringID IS NULL
						AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

				INSERT INTO tmp_JobLog_ (Message)
					  SELECT DISTINCT CONCAT(Code ,' ', vr.DialStringPrefix , ' No PREFIX FOUND')
					  	FROM tmp_TempRateTableRate_ vr
							LEFT JOIN tmp_DialString_ ds
								ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  ))
							WHERE vr.ProcessId = p_processId
								AND ds.DialStringID IS NULL
								AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

			END IF;

			IF totaldialstringcode = 0
			THEN
				INSERT INTO tmp_RateTableRateDialString_
				SELECT DISTINCT
					`TempRateTableRateID`,
					`CodeDeckId`,
					`TimezonesID`,
					`OriginationCode`,
					`OriginationDescription`,
					`DialString`,
					CASE WHEN ds.Description IS NULL OR ds.Description = ''
					THEN
						tblTempRateTableRate.Description
					ELSE
						ds.Description
					END
					AS Description,
					`Type`,
					`Rate`,
					`RateN`,
					`EffectiveDate`,
					`EndDate`,
					`Change`,
					`ProcessId`,
					`Preference`,
					`ConnectionFee`,
					`Interval1`,
					`IntervalN`,
					tblTempRateTableRate.Forbidden as Forbidden,
					`RoutingCategoryID`,
					`RateCurrency`,
					`ConnectionFeeCurrency`,
					tblTempRateTableRate.DialStringPrefix as DialStringPrefix
				FROM tmp_TempRateTableRate_ as tblTempRateTableRate
				INNER JOIN tmp_DialString_ ds
					ON ( (tblTempRateTableRate.Code = ds.ChargeCode AND tblTempRateTableRate.DialStringPrefix = '') OR (tblTempRateTableRate.DialStringPrefix != '' AND tblTempRateTableRate.DialStringPrefix =  ds.DialString AND tblTempRateTableRate.Code = ds.ChargeCode  ))
				WHERE tblTempRateTableRate.ProcessId = p_processId
					AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');


				INSERT INTO tmp_RateTableRateDialString_2
				SELECT *  FROM tmp_RateTableRateDialString_ where DialStringPrefix!='';

				Delete From tmp_RateTableRateDialString_
				Where DialStringPrefix = ''
				And Code IN (Select DialStringPrefix From tmp_RateTableRateDialString_2);

				INSERT INTO tmp_RateTableRateDialString_3
				SELECT * FROM tmp_RateTableRateDialString_;


				DELETE  FROM tmp_TempRateTableRate_ WHERE  ProcessId = p_processId;

				INSERT INTO tmp_TempRateTableRate_(
					`TempRateTableRateID`,
					CodeDeckId,
					TimezonesID,
					OriginationCode,
					OriginationDescription,
					Code,
					Description,
					`Type`,
					Rate,
					RateN,
					EffectiveDate,
					EndDate,
					`Change`,
					ProcessId,
					Preference,
					ConnectionFee,
					Interval1,
					IntervalN,
					Forbidden,
					RoutingCategoryID,
					RateCurrency,
					ConnectionFeeCurrency,
					DialStringPrefix
				)
				SELECT DISTINCT
					`TempRateTableRateID`,
					`CodeDeckId`,
					`TimezonesID`,
					`OriginationCode`,
					`OriginationDescription`,
					`Code`,
					`Description`,
					`Type`,
					`Rate`,
					`RateN`,
					`EffectiveDate`,
					`EndDate`,
					`Change`,
					`ProcessId`,
					`Preference`,
					`ConnectionFee`,
					`Interval1`,
					`IntervalN`,
					`Forbidden`,
					`RoutingCategoryID`,
					`RateCurrency`,
					`ConnectionFeeCurrency`,
					`DialStringPrefix`
				FROM tmp_RateTableRateDialString_3;

				UPDATE tmp_TempRateTableRate_ as tblTempRateTableRate
				JOIN tmp_DialString_ ds
					ON ( (tblTempRateTableRate.Code = ds.ChargeCode and tblTempRateTableRate.DialStringPrefix = '') OR (tblTempRateTableRate.DialStringPrefix != '' and tblTempRateTableRate.DialStringPrefix =  ds.DialString and tblTempRateTableRate.Code = ds.ChargeCode  ))
					AND tblTempRateTableRate.ProcessId = p_processId
					AND ds.Forbidden = 1
				SET tblTempRateTableRate.Forbidden = 'B';

				UPDATE tmp_TempRateTableRate_ as  tblTempRateTableRate
				JOIN tmp_DialString_ ds
					ON ( (tblTempRateTableRate.Code = ds.ChargeCode and tblTempRateTableRate.DialStringPrefix = '') OR (tblTempRateTableRate.DialStringPrefix != '' and tblTempRateTableRate.DialStringPrefix =  ds.DialString and tblTempRateTableRate.Code = ds.ChargeCode  ))
					AND tblTempRateTableRate.ProcessId = p_processId
					AND ds.Forbidden = 0
				SET tblTempRateTableRate.Forbidden = 'UB';

			END IF;

		END IF;

	END IF;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_SplitRateTableRate`;
DELIMITER //
CREATE PROCEDURE `prc_SplitRateTableRate`(
	IN `p_processId` VARCHAR(200),
	IN `p_dialcodeSeparator` VARCHAR(50),
	IN `p_seperatecolumn` INT
)
ThisSP:BEGIN

	DECLARE i INTEGER;
	DECLARE v_rowCount_ INT;
	DECLARE v_pointer_ INT;
	DECLARE v_TempRateTableRateID_ INT;
	DECLARE v_OriginationCode_ TEXT;
	DECLARE v_OriginationCountryCode_ VARCHAR(500);
	DECLARE v_Code_ TEXT;
	DECLARE v_CountryCode_ VARCHAR(500);
	DECLARE newcodecount INT(11) DEFAULT 0;

	IF p_dialcodeSeparator !='null'
	THEN

		DROP TEMPORARY TABLE IF EXISTS `my_splits`;
		CREATE TEMPORARY TABLE `my_splits` (
			`TempRateTableRateID` INT(11) NULL DEFAULT NULL,
			`OriginationCode` Text NULL DEFAULT NULL,
			`OriginationCountryCode` Text NULL DEFAULT NULL,
			`Code` Text NULL DEFAULT NULL,
			`CountryCode` Text NULL DEFAULT NULL
		);

		SET i = 1;
		REPEAT
			/*
				p_seperatecolumn = 1 = Origination Code
				p_seperatecolumn = 2 = Destination Code
			*/
			IF(p_seperatecolumn = 1)
			THEN
				INSERT INTO my_splits (TempRateTableRateID, OriginationCode, OriginationCountryCode, Code, CountryCode)
				SELECT TempRateTableRateID , FnStringSplit(OriginationCode, p_dialcodeSeparator, i), OriginationCountryCode, Code, CountryCode  FROM tblTempRateTableRate
				WHERE FnStringSplit(OriginationCode, p_dialcodeSeparator , i) IS NOT NULL
					AND ProcessId = p_processId;
			ELSE
				INSERT INTO my_splits (TempRateTableRateID, OriginationCode, OriginationCountryCode, Code, CountryCode)
				SELECT TempRateTableRateID , OriginationCode, OriginationCountryCode, FnStringSplit(Code, p_dialcodeSeparator, i), CountryCode  FROM tblTempRateTableRate
				WHERE FnStringSplit(Code, p_dialcodeSeparator , i) IS NOT NULL
					AND ProcessId = p_processId;
			END IF;

			SET i = i + 1;
			UNTIL ROW_COUNT() = 0
		END REPEAT;

		UPDATE my_splits SET OriginationCode = trim(OriginationCode), Code = trim(Code);



		INSERT INTO my_splits (TempRateTableRateID, OriginationCode, OriginationCountryCode, Code, CountryCode)
		SELECT TempRateTableRateID, OriginationCode, OriginationCountryCode, Code, CountryCode  FROM tblTempRateTableRate
		WHERE
			(
				(p_seperatecolumn = 1 AND (OriginationCountryCode IS NOT NULL AND OriginationCountryCode <> '') AND (OriginationCode IS NULL OR OriginationCode = '')) OR
				(p_seperatecolumn = 2 AND (CountryCode IS NOT NULL AND CountryCode <> '') AND (Code IS NULL OR Code = ''))
			)
		AND ProcessId = p_processId;


		DROP TEMPORARY TABLE IF EXISTS tmp_newratetable_splite_;
		CREATE TEMPORARY TABLE tmp_newratetable_splite_  (
			RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			TempRateTableRateID INT(11) NULL DEFAULT NULL,
			OriginationCode VARCHAR(500) NULL DEFAULT NULL,
			OriginationCountryCode VARCHAR(500) NULL DEFAULT NULL,
			Code VARCHAR(500) NULL DEFAULT NULL,
			CountryCode VARCHAR(500) NULL DEFAULT NULL
		);

		INSERT INTO tmp_newratetable_splite_(TempRateTableRateID,OriginationCode,OriginationCountryCode,Code,CountryCode)
		SELECT
			TempRateTableRateID,
			OriginationCode,
			OriginationCountryCode,
			Code,
			CountryCode
		FROM my_splits
		WHERE
			((p_seperatecolumn = 1 AND OriginationCode like '%-%') OR (p_seperatecolumn = 2 AND Code like '%-%'))
			AND TempRateTableRateID IS NOT NULL;

		SET v_pointer_ = 1;
		SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_newratetable_splite_);

		WHILE v_pointer_ <= v_rowCount_
		DO
			SET v_TempRateTableRateID_ = (SELECT TempRateTableRateID FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);
			SET v_OriginationCode_ = (SELECT OriginationCode FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);
			SET v_OriginationCountryCode_ = (SELECT OriginationCountryCode FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);
			SET v_Code_ = (SELECT Code FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);
			SET v_CountryCode_ = (SELECT CountryCode FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);

			Call prc_SplitAndInsertRateTableRate(v_TempRateTableRateID_,p_seperatecolumn,v_OriginationCode_,v_OriginationCountryCode_,v_Code_,v_CountryCode_);

			SET v_pointer_ = v_pointer_ + 1;
		END WHILE;

		DELETE FROM my_splits
		WHERE
			((p_seperatecolumn = 1 AND OriginationCode like '%-%') OR (p_seperatecolumn = 2 AND Code like '%-%'))
			AND TempRateTableRateID IS NOT NULL;

		DELETE FROM my_splits
		WHERE (Code = '' OR Code IS NULL) AND (CountryCode = '' OR CountryCode IS NULL);

		INSERT INTO tmp_split_RateTableRate_
		SELECT DISTINCT
			my_splits.TempRateTableRateID as `TempRateTableRateID`,
			`CodeDeckId`,
			`TimezonesID`,
			CONCAT(IFNULL(my_splits.OriginationCountryCode,''),my_splits.OriginationCode) as OriginationCode,
			`OriginationDescription`,
			CONCAT(IFNULL(my_splits.CountryCode,''),my_splits.Code) as Code,
			`Description`,
			`Type`,
			`Rate`,
			`RateN`,
			`EffectiveDate`,
			`EndDate`,
			`Change`,
			`ProcessId`,
			`Preference`,
			`ConnectionFee`,
			`Interval1`,
			`IntervalN`,
			`Blocked`,
			`RoutingCategoryID`,
			`RateCurrency`,
			`ConnectionFeeCurrency`,
			`DialStringPrefix`
		FROM my_splits
		INNER JOIN tblTempRateTableRate
			ON my_splits.TempRateTableRateID = tblTempRateTableRate.TempRateTableRateID
		WHERE	tblTempRateTableRate.ProcessId = p_processId;

	END IF;

	IF p_dialcodeSeparator = 'null'
	THEN

		INSERT INTO tmp_split_RateTableRate_
		SELECT DISTINCT
			`TempRateTableRateID`,
			`CodeDeckId`,
			`TimezonesID`,
			CONCAT(IFNULL(tblTempRateTableRate.OriginationCountryCode,''),tblTempRateTableRate.OriginationCode) as OriginationCode,
			`OriginationDescription`,
			CONCAT(IFNULL(tblTempRateTableRate.CountryCode,''),tblTempRateTableRate.Code) as Code,
			`Description`,
			`Type`,
			`Rate`,
			`RateN`,
			`EffectiveDate`,
			`EndDate`,
			`Change`,
			`ProcessId`,
			`Preference`,
			`ConnectionFee`,
			`Interval1`,
			`IntervalN`,
			`Blocked`,
			`RoutingCategoryID`,
			`RateCurrency`,
			`ConnectionFeeCurrency`,
			`DialStringPrefix`
		FROM tblTempRateTableRate
		WHERE ProcessId = p_processId;

	END IF;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_WSReviewRateTableRate`;
DELIMITER //
CREATE PROCEDURE `prc_WSReviewRateTableRate`(
	IN `p_RateTableId` INT,
	IN `p_replaceAllRates` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_processId` VARCHAR(200),
	IN `p_addNewCodesToCodeDeck` INT,
	IN `p_companyId` INT,
	IN `p_forbidden` INT,
	IN `p_preference` INT,
	IN `p_dialstringid` INT,
	IN `p_dialcodeSeparator` VARCHAR(50),
	IN `p_seperatecolumn` INT,
	IN `p_CurrencyID` INT,
	IN `p_list_option` INT
)
ThisSP:BEGIN


	DECLARE newstringcode INT(11) DEFAULT 0;
	DECLARE v_pointer_ INT;
	DECLARE v_rowCount_ INT;
	DECLARE v_RateTableCurrencyID_ INT;
	DECLARE v_CompanyCurrencyID_ INT;

    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
    CREATE TEMPORARY TABLE tmp_JobLog_ (
        Message longtext
    );

    DROP TEMPORARY TABLE IF EXISTS tmp_split_RateTableRate_;
    CREATE TEMPORARY TABLE tmp_split_RateTableRate_ (
		`TempRateTableRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Type` varchar(50) NULL DEFAULT NULL,
		`Rate` decimal(18, 6) ,
		`RateN` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Blocked` tinyint,
		`RoutingCategoryID` int,
		`RateCurrency` INT(11) NULL DEFAULT NULL,
		`ConnectionFeeCurrency` INT(11) NULL DEFAULT NULL,
		`DialStringPrefix` varchar(500) ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_OriginationCode (`OriginationCode`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
    );

    DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableRate_;
    CREATE TEMPORARY TABLE tmp_TempRateTableRate_ (
		`TempRateTableRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Type` varchar(50) NULL DEFAULT NULL,
		`Rate` decimal(18, 6) ,
		`RateN` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Blocked` tinyint,
		`RoutingCategoryID` int,
		`RateCurrency` INT(11) NULL DEFAULT NULL,
		`ConnectionFeeCurrency` INT(11) NULL DEFAULT NULL,
		`DialStringPrefix` varchar(500) ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_OriginationCode (`OriginationCode`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
    );

    CALL  prc_RateTableCheckDialstringAndDupliacteCode(p_companyId,p_processId,p_dialstringid,p_effectiveImmediately,p_dialcodeSeparator,p_seperatecolumn);

	ALTER TABLE `tmp_TempRateTableRate_`	ADD Column `NewRate` decimal(18, 6) ;

    SELECT COUNT(*) AS COUNT INTO newstringcode from tmp_JobLog_;

    SELECT CurrencyID into v_RateTableCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyID FROM tblRateTable WHERE RateTableId=p_RateTableId);
    SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);


	update tmp_TempRateTableRate_
	SET
	NewRate = IF (
                    p_CurrencyID > 0,
                    CASE WHEN p_CurrencyID = v_RateTableCurrencyID_
                    THEN
                        Rate
                    WHEN  p_CurrencyID = v_CompanyCurrencyID_
                    THEN
                    (
                        ( Rate  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ and CompanyID = p_companyId ) )
                    )
                    ELSE
                    (
                        (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId )
                            *
                        (Rate  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))
                    )
                    END ,
                    Rate
                )
    WHERE ProcessID=p_processId;


    IF newstringcode = 0
    THEN

		INSERT INTO tblRateTableRateChangeLog(
            TempRateTableRateID,
            RateTableRateID,
            RateTableId,
            TimezonesID,
            OriginationRateID,
            OriginationCode,
            OriginationDescription,
            RateId,
            Code,
            Description,
            Rate,
            RateN,
            EffectiveDate,
            EndDate,
            Interval1,
            IntervalN,
            ConnectionFee,
            Preference,
            Blocked,
            RoutingCategoryID,
            RateCurrency,
            ConnectionFeeCurrency,
            `Action`,
            ProcessID,
            created_at
		)
		SELECT
			tblTempRateTableRate.TempRateTableRateID,
			tblRateTableRate.RateTableRateID,
			p_RateTableId AS RateTableId,
			tblTempRateTableRate.TimezonesID,
			IFNULL(OriginationRate.RateID,0) AS OriginationRateID,
			tblTempRateTableRate.OriginationCode,
			tblTempRateTableRate.OriginationDescription,
			tblRate.RateId,
			tblTempRateTableRate.Code,
			tblTempRateTableRate.Description,
			tblTempRateTableRate.Rate,
			tblTempRateTableRate.RateN,
			tblTempRateTableRate.EffectiveDate,
			tblTempRateTableRate.EndDate ,
			IFNULL(tblTempRateTableRate.Interval1,tblRate.Interval1 ) as Interval1,
			IFNULL(tblTempRateTableRate.IntervalN , tblRate.IntervalN ) as IntervalN,
			tblTempRateTableRate.ConnectionFee,
			tblTempRateTableRate.Preference,
			tblTempRateTableRate.Blocked,
			tblTempRateTableRate.RoutingCategoryID,
			tblTempRateTableRate.RateCurrency,
			tblTempRateTableRate.ConnectionFeeCurrency,
			'New' AS `Action`,
			p_processId AS ProcessID,
			now() AS created_at
		FROM tmp_TempRateTableRate_ as tblTempRateTableRate
		LEFT JOIN tblRate
			ON tblTempRateTableRate.Code = tblRate.Code AND tblTempRateTableRate.CodeDeckId = tblRate.CodeDeckId  AND tblRate.CompanyID = p_companyId
		LEFT JOIN tblRate AS OriginationRate
			ON tblTempRateTableRate.OriginationCode = OriginationRate.Code AND tblTempRateTableRate.CodeDeckId = OriginationRate.CodeDeckId  AND OriginationRate.CompanyID = p_companyId
		LEFT JOIN tblRateTableRate
			ON tblRate.RateID = tblRateTableRate.RateId AND
			((IFNULL(tblRateTableRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableRate.OriginationRateID = OriginationRate.RateID)) AND
			tblRateTableRate.RateTableId = p_RateTableId AND
			tblRateTableRate.TimezonesID = tblTempRateTableRate.TimezonesID
			AND tblRateTableRate.EffectiveDate  <= date(now())
		WHERE tblTempRateTableRate.ProcessID=p_processId AND tblRateTableRate.RateTableRateID IS NULL
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');



        DROP TEMPORARY TABLE IF EXISTS tmp_EffectiveDates_;
		CREATE TEMPORARY TABLE tmp_EffectiveDates_ (
			EffectiveDate  Date,
			RowID int,
			INDEX (RowID)
		);
        INSERT INTO tmp_EffectiveDates_
        SELECT distinct
            EffectiveDate,
            @row_num := @row_num+1 AS RowID
        FROM tmp_TempRateTableRate_
            ,(SELECT @row_num := 0) x
        WHERE  ProcessID = p_processId

        group by EffectiveDate
        order by EffectiveDate asc;

        SET v_pointer_ = 1;
        SET v_rowCount_ = ( SELECT COUNT(*) FROM tmp_EffectiveDates_ );

        IF v_rowCount_ > 0 THEN

            WHILE v_pointer_ <= v_rowCount_
            DO

                SET @EffectiveDate = ( SELECT EffectiveDate FROM tmp_EffectiveDates_ WHERE RowID = v_pointer_ );
                SET @row_num = 0;



                INSERT INTO tblRateTableRateChangeLog(
					TempRateTableRateID,
					RateTableRateID,
					RateTableId,
					TimezonesID,
					OriginationRateID,
					OriginationCode,
					OriginationDescription,
					RateId,
					Code,
					Description,
					Rate,
					RateN,
					EffectiveDate,
					EndDate,
					Interval1,
					IntervalN,
					ConnectionFee,
					Preference,
					Blocked,
					RoutingCategoryID,
					RateCurrency,
					ConnectionFeeCurrency,
					`Action`,
					ProcessID,
					created_at
                )
                SELECT
					distinct
					tblTempRateTableRate.TempRateTableRateID,
					RateTableRate.RateTableRateID,
					p_RateTableId AS RateTableId,
					tblTempRateTableRate.TimezonesID,
					IFNULL(OriginationRate.RateID,0) AS OriginationRateID,
					OriginationRate.Code AS OriginationCode,
					OriginationRate.Description AS OriginationDescription,
					tblRate.RateId,
					tblRate.Code,
					tblRate.Description,
					tblTempRateTableRate.Rate,
					tblTempRateTableRate.RateN,
					tblTempRateTableRate.EffectiveDate,
					tblTempRateTableRate.EndDate ,
					tblTempRateTableRate.Interval1,
					tblTempRateTableRate.IntervalN,
					tblTempRateTableRate.ConnectionFee,
					tblTempRateTableRate.Preference,
					tblTempRateTableRate.Blocked,
					tblTempRateTableRate.RoutingCategoryID,
					tblTempRateTableRate.RateCurrency,
					tblTempRateTableRate.ConnectionFeeCurrency,
					IF(tblTempRateTableRate.NewRate > RateTableRate.Rate, 'Increased', IF(tblTempRateTableRate.NewRate < RateTableRate.Rate, 'Decreased','')) AS `Action`,
					p_processid AS ProcessID,
					now() AS created_at
                FROM
                (

                    select distinct tmp.* ,
                        @row_num := IF(@prev_RateId = tmp.RateID AND @prev_EffectiveDate >= tmp.EffectiveDate, (@row_num + 1), 1) AS RowID,
                        @prev_RateId := tmp.RateID,
                        @prev_EffectiveDate := tmp.EffectiveDate
                    FROM
                    (
                        select distinct vr1.*
                        from tblRateTableRate vr1
                        LEFT outer join tblRateTableRate vr2
                            on vr1.RateTableId = vr2.RateTableId
                            and vr1.RateID = vr2.RateID
                            and vr1.OriginationRateID = vr2.OriginationRateID
                            AND vr1.TimezonesID = vr2.TimezonesID
                            AND vr2.EffectiveDate  = @EffectiveDate
                        where
                            vr1.RateTableId = p_RateTableId
                            and vr1.EffectiveDate <= COALESCE(vr2.EffectiveDate,@EffectiveDate)
                        order by vr1.RateID desc ,vr1.EffectiveDate desc
                    ) tmp ,
                    ( SELECT @row_num := 0 , @prev_RateId := 0 , @prev_EffectiveDate := '' ) x
                      order by RateID desc , EffectiveDate desc
                ) RateTableRate
                JOIN tblRate
                    ON tblRate.CompanyID = p_companyId
                    AND tblRate.RateID = RateTableRate.RateId
                LEFT JOIN tblRate AS OriginationRate
                    ON OriginationRate.CompanyID = p_companyId
                    AND OriginationRate.RateID = RateTableRate.OriginationRateID
                JOIN tmp_TempRateTableRate_ tblTempRateTableRate
                    ON tblTempRateTableRate.Code = tblRate.Code
                    AND ((tblTempRateTableRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableRate.OriginationCode = OriginationRate.Code))
                    AND tblTempRateTableRate.TimezonesID = RateTableRate.TimezonesID
                    AND tblTempRateTableRate.ProcessID=p_processId

                    AND  RateTableRate.EffectiveDate <= tblTempRateTableRate.EffectiveDate
                    AND tblTempRateTableRate.EffectiveDate =  @EffectiveDate
                    AND RateTableRate.RowID = 1
                WHERE
                    RateTableRate.RateTableId = p_RateTableId

                    AND tblTempRateTableRate.Code IS NOT NULL
                    AND tblTempRateTableRate.ProcessID=p_processId
                    AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

                SET v_pointer_ = v_pointer_ + 1;

            END WHILE;

        END IF;


        IF p_list_option = 1
        THEN

            INSERT INTO tblRateTableRateChangeLog(
				RateTableRateID,
				RateTableId,
				TimezonesID,
				OriginationRateID,
				OriginationCode,
				OriginationDescription,
				RateId,
				Code,
				Description,
				Rate,
				RateN,
				EffectiveDate,
				EndDate,
				Interval1,
				IntervalN,
				ConnectionFee,
				Preference,
				Blocked,
				RoutingCategoryID,
				RateCurrency,
				ConnectionFeeCurrency,
				`Action`,
				ProcessID,
				created_at
            )
            SELECT DISTINCT
				tblRateTableRate.RateTableRateID,
				p_RateTableId AS RateTableId,
				tblRateTableRate.TimezonesID,
				tblRateTableRate.OriginationRateID,
				OriginationRate.Code,
				OriginationRate.Description,
				tblRateTableRate.RateId,
				tblRate.Code,
				tblRate.Description,
				tblRateTableRate.Rate,
				tblRateTableRate.RateN,
				tblRateTableRate.EffectiveDate,
				tblRateTableRate.EndDate ,
				tblRateTableRate.Interval1,
				tblRateTableRate.IntervalN,
				tblRateTableRate.ConnectionFee,
				tblRateTableRate.Preference,
				tblRateTableRate.Blocked,
				tblRateTableRate.RoutingCategoryID,
				tblRateTableRate.RateCurrency,
				tblRateTableRate.ConnectionFeeCurrency,
				'Deleted' AS `Action`,
				p_processId AS ProcessID,
				now() AS deleted_at
            FROM tblRateTableRate
            JOIN tblRate
                ON tblRate.RateID = tblRateTableRate.RateId AND tblRate.CompanyID = p_companyId
        		LEFT JOIN tblRate AS OriginationRate
             	 ON OriginationRate.RateID = tblRateTableRate.OriginationRateID AND OriginationRate.CompanyID = p_companyId
            LEFT JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
                ON tblTempRateTableRate.Code = tblRate.Code
                AND ((tblTempRateTableRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableRate.OriginationCode = OriginationRate.Code))
                AND tblTempRateTableRate.TimezonesID = tblRateTableRate.TimezonesID
                AND tblTempRateTableRate.ProcessID=p_processId
                AND (

                    ( tblTempRateTableRate.EndDate is null AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block') )
                    OR

                    ( tblTempRateTableRate.EndDate is not null AND tblTempRateTableRate.Change IN ('Delete', 'R', 'D', 'Blocked','Block') )
                )
            WHERE tblRateTableRate.RateTableId = p_RateTableId
                AND ( tblRateTableRate.EndDate is null OR tblRateTableRate.EndDate <= date(now()) )
                AND tblTempRateTableRate.Code IS NULL
            ORDER BY RateTableRateID ASC;

        END IF;


        INSERT INTO tblRateTableRateChangeLog(
            RateTableRateID,
            RateTableId,
            TimezonesID,
            OriginationRateID,
            OriginationCode,
            OriginationDescription,
            RateId,
            Code,
            Description,
            Rate,
            RateN,
            EffectiveDate,
            EndDate,
            Interval1,
            IntervalN,
            ConnectionFee,
            Preference,
            Blocked,
            RoutingCategoryID,
            RateCurrency,
            ConnectionFeeCurrency,
            `Action`,
            ProcessID,
            created_at
        )
        SELECT DISTINCT
            tblRateTableRate.RateTableRateID,
            p_RateTableId AS RateTableId,
            tblRateTableRate.TimezonesID,
            tblRateTableRate.OriginationRateID,
            OriginationRate.Code,
            OriginationRate.Description,
            tblRateTableRate.RateId,
            tblRate.Code,
            tblRate.Description,
            tblRateTableRate.Rate,
            tblRateTableRate.RateN,
            tblRateTableRate.EffectiveDate,
            IFNULL(tblTempRateTableRate.EndDate,tblRateTableRate.EndDate) as  EndDate ,
            tblRateTableRate.Interval1,
            tblRateTableRate.IntervalN,
            tblRateTableRate.ConnectionFee,
            tblRateTableRate.Preference,
            tblRateTableRate.Blocked,
            tblRateTableRate.RoutingCategoryID,
			tblRateTableRate.RateCurrency,
			tblRateTableRate.ConnectionFeeCurrency,
            'Deleted' AS `Action`,
            p_processId AS ProcessID,
            now() AS deleted_at
        FROM tblRateTableRate
        JOIN tblRate
            ON tblRate.RateID = tblRateTableRate.RateId AND tblRate.CompanyID = p_companyId
        LEFT JOIN tblRate AS OriginationRate
             ON OriginationRate.RateID = tblRateTableRate.OriginationRateID AND OriginationRate.CompanyID = p_companyId
        LEFT JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
            ON tblRate.Code = tblTempRateTableRate.Code
            AND ((tblTempRateTableRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableRate.OriginationCode = OriginationRate.Code))
            AND tblTempRateTableRate.TimezonesID = tblRateTableRate.TimezonesID
            AND tblTempRateTableRate.Change IN ('Delete', 'R', 'D', 'Blocked','Block')
            AND tblTempRateTableRate.ProcessID=p_processId


        WHERE tblRateTableRate.RateTableId = p_RateTableId

            AND tblTempRateTableRate.Code IS NOT NULL
        ORDER BY RateTableRateID ASC;


    END IF;

    SELECT * FROM tmp_JobLog_;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_WSReviewRateTableDIDRateUpdate`;
DELIMITER //
CREATE PROCEDURE `prc_WSReviewRateTableDIDRateUpdate`(
	IN `p_RateTableID` INT,
	IN `p_TimezonesID` INT,
	IN `p_RateIds` TEXT,
	IN `p_ProcessID` VARCHAR(200),
	IN `p_criteria` INT,
	IN `p_Action` VARCHAR(20),
	IN `p_EndDate` DATETIME,
	IN `p_Code` VARCHAR(50),
	IN `p_OriginationCode` VARCHAR(50),
	IN `p_City` VARCHAR(50),
	IN `p_Tariff` VARCHAR(50),
	IN `p_AccessType` VARCHAR(200)
)
ThisSP:BEGIN

	DECLARE newstringcode INT(11) DEFAULT 0;
	DECLARE v_pointer_ INT;
	DECLARE v_rowCount_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
    CREATE TEMPORARY TABLE tmp_JobLog_ (
        Message longtext
    );

	SET @stm_and_code = '';
	IF p_Code != ''
	THEN
		SET @stm_and_code = CONCAT(' AND ("',p_Code,'" IS NULL OR "',p_Code,'" = "" OR tvr.Code LIKE "',REPLACE(p_Code, "*", "%"),'")');
	END IF;

	SET @stm_and_origination_code = '';
	IF p_OriginationCode != ''
	THEN
		SET @stm_and_origination_code = CONCAT(' AND ("',p_OriginationCode,'" IS NULL OR "',p_OriginationCode,'" = "" OR tvr.OriginationCode LIKE "',REPLACE(p_OriginationCode, "*", "%"),'")');
	END IF;

	SET @stm_and_city = '';
	IF p_City != ''
	THEN
		SET @stm_and_city = CONCAT(' AND ("',p_City,'" IS NULL OR "',p_City,'" = "" OR tvr.City = "',p_City,'")');
	END IF;

	SET @stm_and_tariff = '';
	IF p_Tariff != ''
	THEN
		SET @stm_and_tariff = CONCAT(' AND ("',p_Tariff,'" IS NULL OR "',p_Tariff,'" = "" OR tvr.Tariff = "',p_Tariff,'")');
	END IF;

	SET @stm_and_accesstype = '';
	IF p_AccessType != ''
	THEN
		SET @stm_and_accesstype = CONCAT(' AND ("',p_AccessType,'" IS NULL OR "',p_AccessType,'" = "" OR tvr.AccessType = "',p_AccessType,'")');
	END IF;

    CASE p_Action
		WHEN 'Deleted' THEN
			IF p_criteria = 1
			THEN
				SET @stm1 = CONCAT('UPDATE tblRateTableDIDRateChangeLog tvr SET EndDate="',p_EndDate,'" WHERE tvr.TimezonesID=',p_TimezonesID,' AND ProcessID="',p_ProcessID,'" AND `Action`="',p_Action,'" ',@stm_and_code,' ',@stm_and_origination_code,' ',@stm_and_city,' ',@stm_and_tariff,' ',@stm_and_accesstype,';');

				PREPARE stmt1 FROM @stm1;
				EXECUTE stmt1;
				DEALLOCATE PREPARE stmt1;
			ELSE
				SET @stm1 = CONCAT('UPDATE tblRateTableDIDRateChangeLog tvr SET EndDate="',p_EndDate,'" WHERE tvr.TimezonesID=',p_TimezonesID,' AND RateTableDIDRateID IN (',p_RateIds,')AND Action = "',p_Action,'" AND ProcessID = "',p_ProcessID,'" ',@stm_and_code,' ',@stm_and_origination_code,' ',@stm_and_city,' ',@stm_and_tariff,' ',@stm_and_accesstype,';');

				PREPARE stmt1 FROM @stm1;
				EXECUTE stmt1;
				DEALLOCATE PREPARE stmt1;
			END IF;
	END CASE;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_WSReviewRateTableRateUpdate`;
DELIMITER //
CREATE PROCEDURE `prc_WSReviewRateTableRateUpdate`(
	IN `p_RateTableID` INT,
	IN `p_TimezonesID` INT,
	IN `p_RateIds` TEXT,
	IN `p_ProcessID` VARCHAR(200),
	IN `p_criteria` INT,
	IN `p_Action` VARCHAR(20),
	IN `p_Interval1` INT,
	IN `p_IntervalN` INT,
	IN `p_EndDate` DATETIME,
	IN `p_Code` VARCHAR(50),
	IN `p_Description` VARCHAR(50),
	IN `p_OriginationCode` VARCHAR(50),
	IN `p_OriginationDescription` VARCHAR(50),
	IN `p_RoutingCategoryID` VARCHAR(50)
)
ThisSP:BEGIN

	DECLARE newstringcode INT(11) DEFAULT 0;
	DECLARE v_pointer_ INT;
	DECLARE v_rowCount_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
    CREATE TEMPORARY TABLE tmp_JobLog_ (
        Message longtext
    );

	SET @stm_and_code = '';
	IF p_Code != ''
	THEN
		SET @stm_and_code = CONCAT(' AND ("',p_Code,'" IS NULL OR "',p_Code,'" = "" OR tvr.Code LIKE "',REPLACE(p_Code, "*", "%"),'")');
	END IF;

	SET @stm_and_desc = '';
	IF p_Description != ''
	THEN
		SET @stm_and_desc = CONCAT(' AND ("',p_Description,'" IS NULL OR "',p_Description,'" = "" OR tvr.Description LIKE "',REPLACE(p_Description, "*", "%"),'")');
	END IF;

	SET @stm_and_origination_code = '';
	IF p_OriginationCode != ''
	THEN
		SET @stm_and_origination_code = CONCAT(' AND ("',p_OriginationCode,'" IS NULL OR "',p_OriginationCode,'" = "" OR tvr.OriginationCode LIKE "',REPLACE(p_OriginationCode, "*", "%"),'")');
	END IF;

	SET @stm_and_origination_desc = '';
	IF p_OriginationDescription != ''
	THEN
		SET @stm_and_origination_desc = CONCAT(' AND ("',p_OriginationDescription,'" IS NULL OR "',p_OriginationDescription,'" = "" OR tvr.OriginationDescription LIKE "',REPLACE(p_OriginationDescription, "*", "%"),'")');
	END IF;

	SET @stm_and_routing_category = '';
	IF p_RoutingCategoryID != ''
	THEN
		SET @stm_and_routing_category = CONCAT(' AND ("',p_RoutingCategoryID,'" IS NULL OR "',p_RoutingCategoryID,'" = "" OR tvr.RoutingCategoryID = "',p_RoutingCategoryID,'")');
	END IF;

    CASE p_Action
		WHEN 'New' THEN
			SET @stm = '';
			IF p_Interval1 > 0
			THEN
				SET @stm = CONCAT(@stm,'tvr.Interval1 = ',p_Interval1);
			END IF;

			IF p_IntervalN > 0
			THEN
				SET @stm = CONCAT(@stm,IF(@stm != '',',',''),'tvr.IntervalN = ',p_IntervalN);
			END IF;

			IF p_criteria = 1
			THEN
				IF @stm != ''
				THEN
					SET @stm1 = CONCAT('UPDATE tblTempRateTableRate tvr LEFT JOIN tblRateTableRateChangeLog vrcl ON tvr.TempRateTableRateID=vrcl.TempRateTableRateID SET ',@stm,' WHERE tvr.TimezonesID=',p_TimezonesID,' AND tvr.TempRateTableRateID=vrcl.TempRateTableRateID AND vrcl.Action = "',p_Action,'" AND tvr.ProcessID = "',p_ProcessID,'" ',@stm_and_code,' ',@stm_and_desc,' ',@stm_and_origination_code,' ',@stm_and_origination_desc,' ',@stm_and_routing_category,';');

					PREPARE stmt1 FROM @stm1;
					EXECUTE stmt1;
					DEALLOCATE PREPARE stmt1;

					SET @stm2 = CONCAT('UPDATE tblRateTableRateChangeLog tvr SET ',@stm,' WHERE tvr.TimezonesID=',p_TimezonesID,' AND ProcessID = "',p_ProcessID,'" AND Action = "',p_Action,'" ',@stm_and_code,' ',@stm_and_desc,' ',@stm_and_origination_code,' ',@stm_and_origination_desc,' ',@stm_and_routing_category,';');

					PREPARE stm2 FROM @stm2;
					EXECUTE stm2;
					DEALLOCATE PREPARE stm2;
				END IF;
			ELSE
				IF @stm != ''
				THEN
					SET @stm1 = CONCAT('UPDATE tblTempRateTableRate tvr LEFT JOIN tblRateTableRateChangeLog vrcl ON tvr.TempRateTableRateID=vrcl.TempRateTableRateID SET ',@stm,' WHERE tvr.TimezonesID=',p_TimezonesID,' AND tvr.TempRateTableRateID IN (',p_RateIds,') AND tvr.TempRateTableRateID=vrcl.TempRateTableRateID AND vrcl.Action = "',p_Action,'" AND tvr.ProcessID = "',p_ProcessID,'" ',@stm_and_code,' ',@stm_and_desc,' ',@stm_and_origination_code,' ',@stm_and_origination_desc,' ',@stm_and_routing_category,';');

					PREPARE stmt1 FROM @stm1;
					EXECUTE stmt1;
					DEALLOCATE PREPARE stmt1;

					SET @stm2 = CONCAT('UPDATE tblRateTableRateChangeLog tvr SET ',@stm,' WHERE tvr.TimezonesID=',p_TimezonesID,' AND TempRateTableRateID IN (',p_RateIds,') AND ProcessID = "',p_ProcessID,'" AND Action = "',p_Action,'" ',@stm_and_code,' ',@stm_and_desc,' ',@stm_and_origination_code,' ',@stm_and_origination_desc,' ',@stm_and_routing_category,';');

					PREPARE stm2 FROM @stm2;
					EXECUTE stm2;
					DEALLOCATE PREPARE stm2;
				END IF;
			END IF;

		WHEN 'Deleted' THEN
			IF p_criteria = 1
			THEN
				SET @stm1 = CONCAT('UPDATE tblRateTableRateChangeLog tvr SET EndDate="',p_EndDate,'" WHERE tvr.TimezonesID=',p_TimezonesID,' AND ProcessID="',p_ProcessID,'" AND `Action`="',p_Action,'" ',@stm_and_code,' ',@stm_and_desc,' ',@stm_and_origination_code,' ',@stm_and_origination_desc,' ',@stm_and_routing_category,';');

				PREPARE stmt1 FROM @stm1;
				EXECUTE stmt1;
				DEALLOCATE PREPARE stmt1;
			ELSE
				SET @stm1 = CONCAT('UPDATE tblRateTableRateChangeLog tvr SET EndDate="',p_EndDate,'" WHERE tvr.TimezonesID=',p_TimezonesID,' AND RateTableRateID IN (',p_RateIds,')AND Action = "',p_Action,'" AND ProcessID = "',p_ProcessID,'" ',@stm_and_code,' ',@stm_and_desc,' ',@stm_and_origination_code,' ',@stm_and_origination_desc,' ',@stm_and_routing_category,';');

				PREPARE stmt1 FROM @stm1;
				EXECUTE stmt1;
				DEALLOCATE PREPARE stmt1;
			END IF;
	END CASE;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_RateTableDIDRateApprove`;
DELIMITER //
CREATE PROCEDURE `prc_RateTableDIDRateApprove`(
	IN `p_RateTableId` INT,
	IN `p_RateTableDIDRateAAID` LONGTEXT,
	IN `p_ApprovedStatus` TINYINT,
	IN `p_Critearea_CountryId` INT,
	IN `p_Critearea_Code` VARCHAR(50),
	IN `p_Critearea_OriginationCode` VARCHAR(50),
	IN `p_Critearea_Effective` VARCHAR(50),
	IN `p_TimezonesID` INT,
	IN `p_Critearea_ApprovedStatus` TINYINT,
	IN `p_Critearea_City` VARCHAR(50),
	IN `p_Critearea_Tariff` VARCHAR(50),
	IN `p_ApprovedBy` VARCHAR(50),
	IN `p_Critearea` INT,
	IN `p_action` INT
)
ThisSP:BEGIN

	DECLARE v_StatusAwaitingApproval_ INT(11) DEFAULT 0;
	DECLARE v_StatusApproved_ INT(11) DEFAULT 1;
	DECLARE v_StatusRejected_ INT(11) DEFAULT 2;
	DECLARE v_StatusDelete_ INT(11) DEFAULT 3;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableDIDRate_;
	CREATE TEMPORARY TABLE tmp_RateTableDIDRate_ (
		`RateTableDIDRateAAID` BIGINT(20),
		`OriginationRateID` BIGINT(20),
		`RateID` INT(11),
		`RateTableId` BIGINT(20),
		`TimezonesID` BIGINT(20),
		`EffectiveDate` DATE,
		`EndDate` DATE,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NULL DEFAULT NULL,
		`OneOffCost` DECIMAL(18,6),
		`MonthlyCost` DECIMAL(18,6),
		`CostPerCall` DECIMAL(18,6),
		`CostPerMinute` DECIMAL(18,6),
		`SurchargePerCall` DECIMAL(18,6),
		`SurchargePerMinute` DECIMAL(18,6),
		`OutpaymentPerCall` DECIMAL(18,6),
		`OutpaymentPerMinute` DECIMAL(18,6),
		`Surcharges` DECIMAL(18,6),
		`Chargeback` DECIMAL(18,6),
		`CollectionCostAmount` DECIMAL(18,6),
		`CollectionCostPercentage` DECIMAL(18,6),
		`RegistrationCostPerNumber` DECIMAL(18,6),
		`OneOffCostCurrency` INT(11),
		`MonthlyCostCurrency` INT(11),
		`CostPerCallCurrency` INT(11),
		`CostPerMinuteCurrency` INT(11),
		`SurchargePerCallCurrency` INT(11),
		`SurchargePerMinuteCurrency` INT(11),
		`OutpaymentPerCallCurrency` INT(11),
		`OutpaymentPerMinuteCurrency` INT(11),
		`SurchargesCurrency` INT(11),
		`ChargebackCurrency` INT(11),
		`CollectionCostAmountCurrency` INT(11),
		`RegistrationCostPerNumberCurrency` INT(11),
		`created_at` DATETIME,
		`updated_at` DATETIME,
		`CreatedBy` VARCHAR(50),
		`ModifiedBy` VARCHAR(50),
		`ApprovedStatus` TINYINT(4),
		`ApprovedBy` VARCHAR(50),
		`ApprovedDate` DATETIME,
		`VendorID` INT(11),
		`RateTableDIDRateID` BIGINT(20),
		INDEX tmp_RateTableDIDRate_RateID (`RateID`,`OriginationRateID`,`TimezonesID`,`EffectiveDate`,`City`,`Tariff`)
	);

	INSERT INTO	tmp_RateTableDIDRate_
	SELECT
		rtr.RateTableDIDRateAAID,
		rtr.OriginationRateID,
		rtr.RateID,
		rtr.RateTableId,
		rtr.TimezonesID,
		IF(rtr.EffectiveDate < CURDATE(), CURDATE(), rtr.EffectiveDate) AS EffectiveDate,
		rtr.EndDate,
		rtr.City,
		rtr.Tariff,
		rtr.AccessType,
		rtr.OneOffCost,
		rtr.MonthlyCost,
		rtr.CostPerCall,
		rtr.CostPerMinute,
		rtr.SurchargePerCall,
		rtr.SurchargePerMinute,
		rtr.OutpaymentPerCall,
		rtr.OutpaymentPerMinute,
		rtr.Surcharges,
		rtr.Chargeback,
		rtr.CollectionCostAmount,
		rtr.CollectionCostPercentage,
		rtr.RegistrationCostPerNumber,
		rtr.OneOffCostCurrency,
		rtr.MonthlyCostCurrency,
		rtr.CostPerCallCurrency,
		rtr.CostPerMinuteCurrency,
		rtr.SurchargePerCallCurrency,
		rtr.SurchargePerMinuteCurrency,
		rtr.OutpaymentPerCallCurrency,
		rtr.OutpaymentPerMinuteCurrency,
		rtr.SurchargesCurrency,
		rtr.ChargebackCurrency,
		rtr.CollectionCostAmountCurrency,
		rtr.RegistrationCostPerNumberCurrency,
		rtr.created_at,
		rtr.updated_at,
		rtr.CreatedBy,
		rtr.ModifiedBy,
		rtr.ApprovedStatus AS ApprovedStatus,
		p_ApprovedBy AS ApprovedBy,
		NOW() AS ApprovedDate,
		rtr.VendorID,
		rtr.RateTableDIDRateID
	FROM
		tblRateTableDIDRateAA rtr
	INNER JOIN
		tblRate r ON r.RateID = rtr.RateId
	LEFT JOIN
		tblRate r2 ON r2.RateID = rtr.OriginationRateID
	WHERE
		(
			(p_Critearea = 0 AND (FIND_IN_SET(rtr.RateTableDIDRateAAID,p_RateTableDIDRateAAID) != 0 )) OR
			(
				p_Critearea = 1 AND
				(
					((p_Critearea_CountryId IS NULL) OR (p_Critearea_CountryId IS NOT NULL AND r.CountryId = p_Critearea_CountryId)) AND
					((p_Critearea_OriginationCode IS NULL) OR (p_Critearea_OriginationCode IS NOT NULL AND r2.Code LIKE REPLACE(p_Critearea_OriginationCode,'*', '%'))) AND
					((p_Critearea_Code IS NULL) OR (p_Critearea_Code IS NOT NULL AND r.Code LIKE REPLACE(p_Critearea_Code,'*', '%'))) AND
					(p_Critearea_City IS NULL OR rtr.City LIKE REPLACE(p_Critearea_City, '*', '%')) AND
					(p_Critearea_Tariff IS NULL OR rtr.Tariff LIKE REPLACE(p_Critearea_Tariff, '*', '%')) AND
					(p_Critearea_ApprovedStatus IS NULL OR rtr.ApprovedStatus = p_Critearea_ApprovedStatus) AND
					(
						p_Critearea_Effective = 'All' OR
						(p_Critearea_Effective = 'Now' AND rtr.EffectiveDate <= NOW() ) OR
						(p_Critearea_Effective = 'Future' AND rtr.EffectiveDate > NOW() )
					)
				)
			)
		) AND
		rtr.RateTableId = p_RateTableId AND
		(p_TimezonesID IS NULL OR rtr.TimezonesID = p_TimezonesID) AND
		rtr.ApprovedStatus IN (v_StatusAwaitingApproval_,v_StatusDelete_); -- only awaitng approval and awaitng approval delete rates

	IF p_ApprovedStatus = v_StatusApproved_ -- approve rates
	THEN

		DROP TEMPORARY TABLE IF EXISTS tmp_RateTableDIDRate2_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableDIDRate2_ AS (SELECT * FROM tmp_RateTableDIDRate_);

		-- delete all duplicate records, keep only one - only last aa rate will be approved and all other will be ignored
		DELETE temp2
		FROM
			tmp_RateTableDIDRate2_ temp2
		INNER JOIN
			tmp_RateTableDIDRate_ temp1 ON temp1.OriginationRateID = temp2.OriginationRateID
			AND temp1.RateID = temp2.RateID
			AND temp1.RateTableId = temp2.RateTableId
			AND temp1.TimezonesID = temp2.TimezonesID
			AND temp1.City = temp2.City
			AND temp1.Tariff = temp2.Tariff
			AND (
					temp1.EffectiveDate = temp2.EffectiveDate OR
					(temp1.EffectiveDate <= NOW() AND temp2.EffectiveDate <= NOW())
				)
		WHERE
			temp2.RateTableDIDRateAAID < temp1.RateTableDIDRateAAID;

		-- set EndDate to archive rates which needs to approve and exist with same effective date
		UPDATE
			tblRateTableDIDRate rtr
		INNER JOIN
			tmp_RateTableDIDRate2_ temp ON temp.RateId = rtr.RateId AND temp.OriginationRateID = rtr.OriginationRateID AND temp.TimezonesID = rtr.TimezonesID AND temp.EffectiveDate = rtr.EffectiveDate AND temp.City = rtr.City AND temp.Tariff = rtr.Tariff
		SET
			rtr.EndDate = NOW()
		WHERE
			temp.ApprovedStatus = v_StatusAwaitingApproval_;

		-- set EndDate to archive rates which needs to approve and exist with old effective date new rate is <=now() effective date
		UPDATE
			tblRateTableDIDRate rtr
		INNER JOIN
			tmp_RateTableDIDRate2_ temp ON temp.RateId = rtr.RateId AND
			temp.OriginationRateID = rtr.OriginationRateID AND
			temp.TimezonesID = rtr.TimezonesID AND
			(temp.EffectiveDate <= NOW() AND rtr.EffectiveDate <= temp.EffectiveDate) AND
			temp.City = rtr.City AND
			temp.Tariff = rtr.Tariff
		SET
			rtr.EndDate = NOW()
		WHERE
			temp.ApprovedStatus = v_StatusAwaitingApproval_;

		-- set EndDate to archive rates which rate's status is - awaiting approval delete
		UPDATE
			tblRateTableDIDRate rtr
		INNER JOIN
			tmp_RateTableDIDRate2_ temp ON temp.RateTableDIDRateID = rtr.RateTableDIDRateID
		SET
			rtr.EndDate = NOW()
		WHERE
			temp.ApprovedStatus = v_StatusDelete_;

		--	archive rates
		CALL prc_ArchiveOldRateTableDIDRate(p_RateTableId, NULL,p_ApprovedBy);

		-- insert approved rates to tblRateTableDIDRate
		INSERT INTO	tblRateTableDIDRate
		(
			OriginationRateID,
			RateID,
			RateTableId,
			TimezonesID,
			EffectiveDate,
			EndDate,
			City,
			Tariff,
			AccessType,
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
			created_at,
			updated_at,
			CreatedBy,
			ModifiedBy,
			ApprovedStatus,
			ApprovedBy,
			ApprovedDate,
			VendorID
		)
		SELECT
			OriginationRateID,
			RateID,
			RateTableId,
			TimezonesID,
			EffectiveDate,
			EndDate,
			City,
			Tariff,
			AccessType,
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
			created_at,
			updated_at,
			CreatedBy,
			ModifiedBy,
			v_StatusApproved_ AS ApprovedStatus,
			ApprovedBy,
			ApprovedDate,
			VendorID
		FROM
			tmp_RateTableDIDRate2_
		WHERE
			ApprovedStatus = v_StatusAwaitingApproval_;

		-- delete from Awaiting Approval table after inserting into tblRateTableDIDRate
		DELETE AA
		FROM
			tblRateTableDIDRateAA AS AA
		INNER JOIN
			tmp_RateTableDIDRate_ AS temp ON temp.RateTableDIDRateAAID = AA.RateTableDIDRateAAID;

		CALL prc_ArchiveOldRateTableDIDRate(p_RateTableId, NULL,p_ApprovedBy);

	ELSE -- reject/disapprove rates

		UPDATE
			tblRateTableDIDRateAA rtr
		INNER JOIN
			tmp_RateTableDIDRate_ temp ON temp.RateTableDIDRateAAID = rtr.RateTableDIDRateAAID
		SET
			rtr.ApprovedStatus = p_ApprovedStatus, rtr.ApprovedBy = temp.ApprovedBy, rtr.ApprovedDate = temp.ApprovedDate;

	END IF;


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;