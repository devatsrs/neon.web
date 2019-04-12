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

-- Dumping structure for procedure speakintelligentRM.prc_WSGenerateRateTablePkg
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSGenerateRateTablePkg`(
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


		
		 SELECT CurrencyID INTO @v_RateGenatorCurrencyID_ FROM  tblRateGenerator WHERE RateGeneratorId = @p_RateGeneratorId;
		SET @p_EffectiveDate = p_EffectiveDate;

		
			
		
		

		SELECT
			rateposition,
			rateGen.companyid ,
			rateGen.RateGeneratorName,
			rateGen.RateGeneratorId,
			rateGen.CurrencyID,
		--	ProductID,
			rateGen.Calls,
			rateGen.Minutes,
			rateGen.DateFrom,
			rateGen.DateTo,
			rateGen.TimezonesID,
			rateGen.TimezonesPercentage,
			IFNULL(st.Name,''),
			IF( percentageRate = '' OR percentageRate is null	,0, percentageRate ),rateGen.SelectType
			INTO @v_RatePosition_, @v_CompanyId_,   @v_RateGeneratorName_,@p_RateGeneratorId, @v_CurrencyID_,
			-- @v_ProductID_,
			@v_Calls,
			@v_Minutes,
			@v_StartDate_ ,@v_EndDate_ ,@v_TimezonesID, @v_TimezonesPercentage,@v_PackageID,
			@v_percentageRate_,@v_PackageType
		FROM tblRateGenerator rateGen INNER JOIN tblPackage st ON st.PackageId = rateGen.PackageID
		WHERE RateGeneratorId = @p_RateGeneratorId;


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
				@v_PackageID,
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
			@v_PackageID,
			@row_num := @row_num+1 AS RowID
			FROM tblRateGeneratorCalculatedRate,(SELECT @row_num := 0) x
			WHERE RateGeneratorId = @p_RateGeneratorId
			ORDER BY CalculatedRateID ASC;  			



				set @v_ApprovedStatus = 1;

				set @v_PKGType = 3; -- package

			  	set @v_AppliedToCustomer = 1; -- customer
				set @v_AppliedToVendor = 2; -- vendor
				set @v_AppliedToReseller = 3; -- reseller
				
				


	-- arguments usage input
			SET @p_Calls	 							 = @v_Calls;
			SET @p_Minutes	 							 = @v_Minutes;
			SET @v_PeakTimeZoneID	 				 = @v_TimezonesID;
			SET @p_PeakTimeZonePercentage	 		 = @v_TimezonesPercentage;		-- peak percentage
			SET @p_MobileOriginationPercentage	 = @v_OriginationPercentage ;	-- mobile percentage

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
			
			
				
				-- Helper calculations...
				SET @p_MobileOrigination				 = @v_Origination ; -- 'Mobile';	--
				SET @v_PeakTimeZoneMinutes				 =  ( (@p_Minutes/ 100) * @p_PeakTimeZonePercentage ) 	; -- Peak minutes:
				SET @v_MinutesFromMobileOrigination  =  ( (@p_Minutes/ 100) * @p_MobileOriginationPercentage ) 	; -- Minutes from mobile:

		 
				 -- ///////////////////////////////////////////////////// Timezone minutes logic
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
		
	
		
/*		
SET @p_CountryID = '' ;
SET @p_CityTariff = '' ;
SET @p_Prefix = '' ;
SET @p_AccessType = '' ;
*/				
			
		-- ///////////////////////////////////////////////////// Timezone minutes logic			
 


																	
-- select @v_CompanyId_,@v_PackageType,@v_AppliedToVendor;

				

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
								rt.CodeDeckId,
								@v_PackageID,
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

								@PackageCostPerMinute := CASE WHEN ( PackageCostPerMinuteCurrency is not null)
								THEN

								CASE WHEN  @v_CurrencyID_ = PackageCostPerMinuteCurrency THEN
									drtr.PackageCostPerMinute
								ELSE
								(
									-- Convert to base currrncy and x by RateGenerator Exhange
									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.PackageCostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = PackageCostPerMinuteCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.PackageCostPerMinute
								ELSE
									(
										-- Convert to base currrncy and x by RateGenerator Exhange
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
									-- Convert to base currrncy and x by RateGenerator Exhange
									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
									* (drtr.RecordingCostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = RecordingCostPerMinuteCurrency and  CompanyID = @v_CompanyId_ ))
								)
								END

								WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
									drtr.RecordingCostPerMinute
								ELSE
									(
										-- Convert to base currrncy and x by RateGenerator Exhange
										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  @v_CurrencyID_  and  CompanyID = @v_CompanyId_ )
										* (drtr.RecordingCostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = rt.CurrencyID and  CompanyID = @v_CompanyId_ ))
									)
								END as RecordingCostPerMinute,


								
								
								OneOffCostCurrency,
								MonthlyCostCurrency,
								PackageCostPerMinuteCurrency,
								RecordingCostPerMinuteCurrency,
																
								
								
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
									(IFNULL(@PackageCostPerMinute,0) * IFNULL(tom.minutes,0))	+
											+
									(IFNULL(@RecordingCostPerMinute,0) * IFNULL(tom.minutes,0)) 


								) as Total1,

								@Total := @Total1 as Total
								
 
				from tblRateTablePKGRate  drtr
				inner join tblRateTable  rt on rt.RateTableId = drtr.RateTableId -- and rt.DIDCategoryID = 2
				 inner join tblVendorConnection vc on vc.RateTableID = rt.RateTableId and vc.CompanyID = rt.CompanyId  and vc.Active=1
				inner join tblAccount a on vc.AccountId = a.AccountID and rt.CompanyId = a.CompanyId
				inner join tblRate r on drtr.RateID = r.RateID and r.CompanyID = vc.CompanyID
				INNER JOIN tblPackage st ON st.CompanyID = r.CompanyID AND st.Name = @v_PackageID AND  r.Code = st.Name
				
				
				
				inner join tblTimezones t on t.TimezonesID =  drtr.TimezonesID
				left join tmp_timezone_minutes tom on tom.TimezonesID = t.TimezonesID
				WHERE
								rt.CompanyId =  @v_CompanyId_			
								AND rt.Type = @v_PackageType -- package
								AND rt.AppliedTo = @v_AppliedToVendor -- vendor  	
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
			--	and t.TimezonesID = @v_TimezonesID
																			
	
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
																		
 
 		-- testing output 
		--  select * from tmp_tblRateTablePKGRate;

		 
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
				
						select distinct  VendorID , sum(Total) as Total from tmp_tblRateTablePKGRate group by VendorID
					) v
					, (SELECT  @prev_VendorID := NUll ,  @rank := 0 ,  @prev_Total := 0 ) f

				order by v.Total,v.VendorID asc
			) tmp
			where vPosition <= @v_RatePosition_; 

			SET @v_SelectedVendor = ( select VendorID from tmp_vendor_position where vPosition <= @v_RatePosition_ order by vPosition , Total  limit 1 );
		
		-- testing output 
		 -- select * from tmp_vendor_position;
		 -- select @v_SelectedVendor;

	

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

			
		-- testing output 
		-- select * from tmp_SelectedVendortblRateTablePKGRate;

		
			DROP TEMPORARY TABLE IF EXISTS tmp_MergeComponents;
			CREATE TEMPORARY TABLE tmp_MergeComponents(
				ID int auto_increment,
				Component TEXT  ,
				TimezonesID INT(11)   ,
				ToTimezonesID INT(11)   ,
				Action CHAR(4)    ,
				MergeTo TEXT  ,
				
				primary key (ID)
			);
			
			insert into tmp_MergeComponents ( 
									Component,
									TimezonesID,
									ToTimezonesID,
									Action,
									MergeTo
									

			)
			select 
									Component,
									TimezonesID,
									ToTimezonesID,
									Action,
									MergeTo
									

			from tblRateGeneratorCostComponent 
			where RateGeneratorId = @p_RateGeneratorId 
			order by CostComponentID asc;
 
		 -- testing output 
		-- select * from tmp_MergeComponents;
		
 
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
						MergeTo
						
				
				INTO
				
						@v_Component,
						@v_TimezonesID,
						@v_ToTimezonesID,
						@v_Action,
						@v_MergeTo
					
				
				FROM tmp_MergeComponents WHERE ID = @v_pointer_;

				IF @v_Action = 'sum' THEN
				
					SET @ResultField = concat('(' ,  REPLACE(@v_Component,',',' + ') , ') ');
				--	select @ResultField,@v_MergeTo;

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
								tmp.Code = srt.Code
								AND (  @v_ToTimezonesID = "" OR  srt.TimezonesID = @v_ToTimezonesID)
								
						set 
						
						' , 'new_', @v_MergeTo , ' = tmp.componentValue;
				');
				-- @v_MergeToComponent
				
				if @stm1 is not null then
				PREPARE stm1 FROM @stm1;
				EXECUTE stm1;
				end if;
				-- dont use dealocate statement here...
				
			--	select * from tmp_SelectedVendortblRateTablePKGRate;
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
		
	
		 -- testing output 
		 
	

		update tmp_SelectedVendortblRateTablePKGRate	
		SET 
			OneOffCost  = IF(new_OneOffCost is null , OneOffCost ,new_OneOffCost)  	,
			MonthlyCost  = IF(new_MonthlyCost is null , MonthlyCost ,new_MonthlyCost)  	,
			PackageCostPerMinute  = IF(new_PackageCostPerMinute is null , PackageCostPerMinute ,new_PackageCostPerMinute)  	,
			RecordingCostPerMinute  = IF(new_RecordingCostPerMinute is null , RecordingCostPerMinute ,new_RecordingCostPerMinute)  	
			;



 -- select * from tmp_SelectedVendortblRateTablePKGRate;
		 -- testing output 
		-- select * from tmp_Raterules_;		 
		-- select * from tmp_SelectedVendortblRateTableDIDRate;

	 	SET @v_pointer_ = 1;
		SET @v_rowCount_ = ( SELECT COUNT(*) FROM tmp_Raterules_ );
	--	select @v_rowCount_;

		WHILE @v_pointer_ <= @v_rowCount_
		DO

			SET @v_rateRuleId_ = (SELECT rateruleid FROM tmp_Raterules_ rr WHERE rr.RowNo = @v_pointer_);
		--	select @v_rateRuleId_;
			
						update tmp_SelectedVendortblRateTablePKGRate rt
						inner join tmp_Raterules_ rr on rr.RowNo  = @v_pointer_  
						and  rr.TimezonesID  = rt.TimezonesID 
						and (rr.PackageID = '' OR rr.PackageID = rt.PackageID )
					
					

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

				
			
			
			SET @v_pointer_ = @v_pointer_ + 1;

		END WHILE;



	
	 -- testing output 
		-- select * from tmp_RateGeneratorCalculatedRate_;		 	 
	--	 select * from tmp_SelectedVendortblRateTablePKGRate;

	 	SET @v_pointer_ = 1;
		SET @v_rowCount_ = ( SELECT COUNT(*) FROM tmp_RateGeneratorCalculatedRate_ );

-- select @v_rowCount_;
		WHILE @v_pointer_ <= @v_rowCount_
		DO

						--	SET @v_rateRuleId_ = (SELECT rateruleid FROM tmp_RateGeneratorCalculatedRate_ rr WHERE rr.RowNo = @v_pointer_);
			
						-- Rate <  v_LessThenRate
						
						update tmp_SelectedVendortblRateTablePKGRate rt
						inner join tmp_RateGeneratorCalculatedRate_ rr on 
						rr.RowNo  = @v_pointer_  AND rr.TimezonesID  = rt.TimezonesID  
						
						AND (  rr.PackageID = ''  OR rt.PackageID = 	rr.PackageID )
						
						
						
						

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
 

			SET @v_pointer_ = @v_pointer_ + 1;

		END WHILE;



	 -- testing output 
	--	 select * from tmp_SelectedVendortblRateTablePKGRate;

		-- leave GenerateRateTable;
		
		
		SET @v_SelectedRateTableID = ( select RateTableID from tmp_SelectedVendortblRateTablePKGRate limit 1 );

		SET @v_AffectedRecords_ = 0;
					
		START TRANSACTION;
		

		
		IF p_RateTableId = -1
		THEN
		
			SET @v_codedeckid_ = ( select CodeDeckId from tmp_SelectedVendortblRateTablePKGRate limit 1 );
			
			INSERT INTO tblRateTable (Type, CompanyId, RateTableName, RateGeneratorID,DIDCategoryID, TrunkID, CodeDeckId,CurrencyID,Status, RoundChargedAmount,MinimumCallCharge,AppliedTo,created_at,updated_at, CreatedBy,ModifiedBy)
			select  @v_PKGType as Type, @v_CompanyId_, p_rateTableName , @p_RateGeneratorId,0 as DIDCategoryID, 0 as TrunkID,  @v_CodeDeckId as CodeDeckId , @v_RateGenatorCurrencyID_ as CurrencyID, Status, RoundChargedAmount,MinimumCallCharge, @v_AppliedToCustomer as AppliedTo , now() ,now() ,p_ModifiedBy,p_ModifiedBy 
			from tblRateTable where RateTableID = @v_SelectedRateTableID  limit 1;
			
			SET @p_RateTableId = LAST_INSERT_ID();
		
		ELSE 
		
			SET @p_RateTableId = p_RateTableId;
			
			IF p_delete_exiting_rate = 1
			THEN
			
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
				INNER JOIN tblPackage st ON st.CompanyID = r.CompanyID AND st.Name = @v_PackageID AND  r.Code = st.Name
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
				INNER JOIN tblPackage st ON st.CompanyID = r.CompanyID AND st.Name = @v_PackageID AND  r.Code = st.Name
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


	-- select * from tmp_SelectedVendortblRateTablePKGRate;	
	
	
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
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  drtr.OneOffCostCurrency  and  CompanyID = @v_CompanyId_  )
						* (drtr.OneOffCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
						)
						END

						WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
						drtr.OneOffCost
						ELSE
						(
							-- Convert to base currrncy and x by RateGenerator Exhange
							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  rt.CurrencyID  and  CompanyID = @v_CompanyId_ )
							* (drtr.OneOffCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
						)
						END as OneOffCost,
						
						( CASE WHEN ( drtr.MonthlyCostCurrency is not null)  -- (MonthlyCost * p_months) as MonthlyCost,
						THEN

						CASE WHEN  @v_CurrencyID_ = drtr.MonthlyCostCurrency THEN
						drtr.MonthlyCost
						ELSE
						(
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  drtr.MonthlyCostCurrency  and  CompanyID = @v_CompanyId_  )
						* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
						)
						END

						WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
						drtr.MonthlyCost
						ELSE
						(
							-- Convert to base currrncy and x by RateGenerator Exhange
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
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = drtr.PackageCostPerMinuteCurrency and  CompanyID = @v_CompanyId_ )
						* (drtr.PackageCostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_   and  CompanyID = @v_CompanyId_ ))
						)
						END

						WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
						drtr.PackageCostPerMinute
						ELSE
						(
							-- Convert to base currrncy and x by RateGenerator Exhange
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
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  drtr.RecordingCostPerMinuteCurrency  and  CompanyID = @v_CompanyId_ )
						* (drtr.RecordingCostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
						)
						END

						WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
						drtr.RecordingCostPerMinute
						ELSE
						(
							-- Convert to base currrncy and x by RateGenerator Exhange
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
						inner join tblRateTable  rt on rt.RateTableId = drtr.RateTableId -- and rt.DIDCategoryID = 2
						INNER JOIN tblRate r ON drtr.Code = r.Code and r.CodeDeckId = drtr.CodeDeckId
						INNER JOIN tblPackage st ON st.CompanyID = r.CompanyID AND st.Name = @v_PackageID AND  r.Code = st.Name
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
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  drtr.OneOffCostCurrency  and  CompanyID = @v_CompanyId_  )
						* (drtr.OneOffCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
						)
						END

						WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
						drtr.OneOffCost
						ELSE
						(
							-- Convert to base currrncy and x by RateGenerator Exhange
							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  rt.CurrencyID  and  CompanyID = @v_CompanyId_ )
							* (drtr.OneOffCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
						)
						END as OneOffCost,
						
						( CASE WHEN ( drtr.MonthlyCostCurrency is not null)  -- (MonthlyCost * p_months) as MonthlyCost,
						THEN

						CASE WHEN  @v_CurrencyID_ = drtr.MonthlyCostCurrency THEN
						drtr.MonthlyCost
						ELSE
						(
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  drtr.MonthlyCostCurrency  and  CompanyID = @v_CompanyId_  )
						* (drtr.MonthlyCost  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
						)
						END

						WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
						drtr.MonthlyCost
						ELSE
						(
							-- Convert to base currrncy and x by RateGenerator Exhange
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
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = drtr.PackageCostPerMinuteCurrency and  CompanyID = @v_CompanyId_ )
						* (drtr.PackageCostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_   and  CompanyID = @v_CompanyId_ ))
						)
						END

						WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
						drtr.PackageCostPerMinute
						ELSE
						(
							-- Convert to base currrncy and x by RateGenerator Exhange
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
						-- Convert to base currrncy and x by RateGenerator Exhange
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId =  drtr.RecordingCostPerMinuteCurrency  and  CompanyID = @v_CompanyId_ )
						* (drtr.RecordingCostPerMinute  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
						)
						END

						WHEN  ( @v_CurrencyID_ = rt.CurrencyID ) THEN
						drtr.RecordingCostPerMinute
						ELSE
						(
							-- Convert to base currrncy and x by RateGenerator Exhange
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
						inner join tblRateTable  rt on rt.RateTableId = drtr.RateTableId -- and rt.DIDCategoryID = 2
						INNER JOIN tblRate r ON drtr.Code = r.Code and r.CodeDeckId = drtr.CodeDeckId
						INNER JOIN tblPackage st ON st.CompanyID = r.CompanyID AND st.Name = @v_PackageID AND  r.Code = st.Name
						LEFT join tblRateTablePKGRateAA rtd  on rtd.RateID  = r.RateID 
						and  rtd.TimezonesID = drtr.TimezonesID 
						and rtd.RateTableID = @p_RateTableId
						and rtd.EffectiveDate = @p_EffectiveDate 
						WHERE rtd.RateTablePKGRateAAID is null;
	END IF;

							
							
					
 -- select * from tblRateTablePKGRateAA;		
 -- select * from tblRateTablePKGRate where RateTableId = @p_RateTableId;
		
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
		
		-- select * from tmp_EffectiveDates_;
		
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
		
		-- select @p_RateTableId,p_ModifiedBy;
		
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

-- Dumping structure for procedure speakintelligentRM.prc_GetRateTablePKGRate
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_GetRateTablePKGRate`(
	IN `p_companyid` INT,
	IN `p_RateTableId` INT,
	IN `p_TimezonesID` INT,
	IN `p_code` VARCHAR(50),
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

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTablePKGRate_;
	CREATE TEMPORARY TABLE tmp_RateTablePKGRate_ (
		ID INT,
		TimezoneTitle VARCHAR(50),
		Code VARCHAR(50),
		OneOffCost DECIMAL(18,6),
		MonthlyCost DECIMAL(18,6),
		PackageCostPerMinute DECIMAL(18,6),
		RecordingCostPerMinute DECIMAL(18,6),
		EffectiveDate DATE,
		EndDate DATE,
		updated_at DATETIME,
		ModifiedBy VARCHAR(50),
		RateTablePKGRateID INT,
		RateID INT,
		ApprovedStatus TINYINT,
		ApprovedBy VARCHAR(50),
		ApprovedDate DATETIME,
		OneOffCostCurrency INT(11),
		MonthlyCostCurrency INT(11),
		PackageCostPerMinuteCurrency INT(11),
		RecordingCostPerMinuteCurrency INT(11),
		OneOffCostCurrencySymbol VARCHAR(255),
		MonthlyCostCurrencySymbol VARCHAR(255),
		PackageCostPerMinuteCurrencySymbol VARCHAR(255),
		RecordingCostPerMinuteCurrencySymbol VARCHAR(255),
		TimezonesID INT(11),
		INDEX tmp_RateTablePKGRate_RateID (`RateID`)
    );


    INSERT INTO tmp_RateTablePKGRate_
    SELECT
		RateTablePKGRateID AS ID,
		tblTimezones.Title AS TimezoneTitle,
		tblRate.Code,
		OneOffCost,
		MonthlyCost,
		PackageCostPerMinute,
		RecordingCostPerMinute,
		IFNULL(tblRateTablePKGRate.EffectiveDate, NOW()) as EffectiveDate,
		tblRateTablePKGRate.EndDate,
		tblRateTablePKGRate.updated_at,
		tblRateTablePKGRate.ModifiedBy,
		RateTablePKGRateID,
		tblRate.RateID,
		tblRateTablePKGRate.ApprovedStatus,
		tblRateTablePKGRate.ApprovedBy,
		tblRateTablePKGRate.ApprovedDate,
		tblOneOffCostCurrency.CurrencyID AS OneOffCostCurrency,
		tblMonthlyCostCurrency.CurrencyID AS MonthlyCostCurrency,
		tblPackageCostPerMinuteCurrency.CurrencyID AS PackageCostPerMinuteCurrency,
		tblRecordingCostPerMinuteCurrency.CurrencyID AS RecordingCostPerMinuteCurrency,
		IFNULL(tblOneOffCostCurrency.Symbol,'') AS OneOffCostCurrencySymbol,
		IFNULL(tblMonthlyCostCurrency.Symbol,'') AS MonthlyCostCurrencySymbol,
		IFNULL(tblPackageCostPerMinuteCurrency.Symbol,'') AS PackageCostPerMinuteCurrencySymbol,
		IFNULL(tblRecordingCostPerMinuteCurrency.Symbol,'') AS RecordingCostPerMinuteCurrencySymbol,
		tblRateTablePKGRate.TimezonesID
    FROM tblRate
    LEFT JOIN tblRateTablePKGRate
        ON tblRateTablePKGRate.RateID = tblRate.RateID
        AND tblRateTablePKGRate.RateTableId = p_RateTableId
    INNER JOIN tblTimezones
    	  ON tblTimezones.TimezonesID = tblRateTablePKGRate.TimezonesID
    LEFT JOIN tblCurrency AS tblOneOffCostCurrency
        ON tblOneOffCostCurrency.CurrencyID = tblRateTablePKGRate.OneOffCostCurrency
    LEFT JOIN tblCurrency AS tblMonthlyCostCurrency
        ON tblMonthlyCostCurrency.CurrencyID = tblRateTablePKGRate.MonthlyCostCurrency
    LEFT JOIN tblCurrency AS tblPackageCostPerMinuteCurrency
        ON tblPackageCostPerMinuteCurrency.CurrencyID = tblRateTablePKGRate.PackageCostPerMinuteCurrency
    LEFT JOIN tblCurrency AS tblRecordingCostPerMinuteCurrency
        ON tblRecordingCostPerMinuteCurrency.CurrencyID = tblRateTablePKGRate.RecordingCostPerMinuteCurrency
    INNER JOIN tblRateTable
        ON tblRateTable.RateTableId = tblRateTablePKGRate.RateTableId
    WHERE
		(tblRate.CompanyID = p_companyid)
		AND (p_code is null OR tblRate.Code LIKE REPLACE(p_code, '*', '%'))
		AND (p_ApprovedStatus IS NULL OR tblRateTablePKGRate.ApprovedStatus = p_ApprovedStatus)
		AND (p_TimezonesID IS NULL OR tblRateTablePKGRate.TimezonesID = p_TimezonesID)
		AND (
				p_effective = 'All'
				OR (p_effective = 'Now' AND EffectiveDate <= NOW() )
				OR (p_effective = 'Future' AND EffectiveDate > NOW())
			);

	IF p_effective = 'Now'
	THEN
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTablePKGRate4_ as (select * from tmp_RateTablePKGRate_);
		DELETE n1 FROM tmp_RateTablePKGRate_ n1, tmp_RateTablePKGRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
			AND  n1.RateID = n2.RateID AND n1.TimezonesID = n2.TimezonesID;
	END IF;


    IF p_isExport = 0
    THEN

       	SELECT * FROM tmp_RateTablePKGRate_
			ORDER BY
					 CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleDESC') THEN TimezoneTitle
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleASC') THEN TimezoneTitle
                END ASC,
					 CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
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
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PackageCostPerMinuteDESC') THEN PackageCostPerMinute
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PackageCostPerMinuteASC') THEN PackageCostPerMinute
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RecordingCostPerMinuteDESC') THEN RecordingCostPerMinute
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RecordingCostPerMinuteASC') THEN RecordingCostPerMinute
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
        	FROM tmp_RateTablePKGRate_;

    END IF;

    IF p_isExport = 1
    THEN
        SELECT
        	TimezoneTitle AS `Time of Day`,
			Code AS PackageName,
			CONCAT(OneOffCostCurrencySymbol,OneOffCost) AS OneOffCost,
			CONCAT(MonthlyCostCurrencySymbol,MonthlyCost) AS MonthlyCost,
			CONCAT(PackageCostPerMinuteCurrencySymbol,PackageCostPerMinute) AS PackageCostPerMinute,
			CONCAT(RecordingCostPerMinuteCurrencySymbol,RecordingCostPerMinute) AS RecordingCostPerMinute,
			EffectiveDate,
			CONCAT(ModifiedBy,'\n',updated_at) AS `Modified By/Date`,
			CONCAT(ApprovedBy,'\n',ApprovedDate) AS `Approved By/Date`,
			ApprovedStatus
        FROM
		  		tmp_RateTablePKGRate_;
    END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

-- Dumping structure for procedure speakintelligentRM.prc_GetRateTablePKGRateAA
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_GetRateTablePKGRateAA`(
	IN `p_companyid` INT,
	IN `p_RateTableId` INT,
	IN `p_TimezonesID` INT,
	IN `p_code` VARCHAR(50),
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

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTablePKGRate_;
	CREATE TEMPORARY TABLE tmp_RateTablePKGRate_ (
		ID INT,
		TimezoneTitle VARCHAR(50),
		Code VARCHAR(50),
		OneOffCost DECIMAL(18,6),
		MonthlyCost DECIMAL(18,6),
		PackageCostPerMinute DECIMAL(18,6),
		RecordingCostPerMinute DECIMAL(18,6),
		EffectiveDate DATE,
		EndDate DATE,
		updated_at DATETIME,
		ModifiedBy VARCHAR(50),
		RateTablePKGRateID INT,
		RateID INT,
		ApprovedStatus TINYINT,
		ApprovedBy VARCHAR(50),
		ApprovedDate DATETIME,
		OneOffCostCurrency INT(11),
		MonthlyCostCurrency INT(11),
		PackageCostPerMinuteCurrency INT(11),
		RecordingCostPerMinuteCurrency INT(11),
		OneOffCostCurrencySymbol VARCHAR(255),
		MonthlyCostCurrencySymbol VARCHAR(255),
		PackageCostPerMinuteCurrencySymbol VARCHAR(255),
		RecordingCostPerMinuteCurrencySymbol VARCHAR(255),
		TimezonesID INT(11),
		INDEX tmp_RateTablePKGRate_RateID (`RateID`)
    );


    INSERT INTO tmp_RateTablePKGRate_
    SELECT
		RateTablePKGRateAAID AS ID,
		tblTimezones.Title AS TimezoneTitle,
		tblRate.Code,
		OneOffCost,
		MonthlyCost,
		PackageCostPerMinute,
		RecordingCostPerMinute,
		IFNULL(tblRateTablePKGRate.EffectiveDate, NOW()) as EffectiveDate,
		tblRateTablePKGRate.EndDate,
		tblRateTablePKGRate.updated_at,
		tblRateTablePKGRate.ModifiedBy,
		RateTablePKGRateAAID,
		tblRate.RateID,
		tblRateTablePKGRate.ApprovedStatus,
		tblRateTablePKGRate.ApprovedBy,
		tblRateTablePKGRate.ApprovedDate,
		tblOneOffCostCurrency.CurrencyID AS OneOffCostCurrency,
		tblMonthlyCostCurrency.CurrencyID AS MonthlyCostCurrency,
		tblPackageCostPerMinuteCurrency.CurrencyID AS PackageCostPerMinuteCurrency,
		tblRecordingCostPerMinuteCurrency.CurrencyID AS RecordingCostPerMinuteCurrency,
		IFNULL(tblOneOffCostCurrency.Symbol,'') AS OneOffCostCurrencySymbol,
		IFNULL(tblMonthlyCostCurrency.Symbol,'') AS MonthlyCostCurrencySymbol,
		IFNULL(tblPackageCostPerMinuteCurrency.Symbol,'') AS PackageCostPerMinuteCurrencySymbol,
		IFNULL(tblRecordingCostPerMinuteCurrency.Symbol,'') AS RecordingCostPerMinuteCurrencySymbol,
		tblRateTablePKGRate.TimezonesID
    FROM tblRate
    LEFT JOIN tblRateTablePKGRateAA AS tblRateTablePKGRate
        ON tblRateTablePKGRate.RateID = tblRate.RateID
        AND tblRateTablePKGRate.RateTableId = p_RateTableId
    INNER JOIN tblTimezones
    	  ON tblTimezones.TimezonesID = tblRateTablePKGRate.TimezonesID
    LEFT JOIN tblCurrency AS tblOneOffCostCurrency
        ON tblOneOffCostCurrency.CurrencyID = tblRateTablePKGRate.OneOffCostCurrency
    LEFT JOIN tblCurrency AS tblMonthlyCostCurrency
        ON tblMonthlyCostCurrency.CurrencyID = tblRateTablePKGRate.MonthlyCostCurrency
    LEFT JOIN tblCurrency AS tblPackageCostPerMinuteCurrency
        ON tblPackageCostPerMinuteCurrency.CurrencyID = tblRateTablePKGRate.PackageCostPerMinuteCurrency
    LEFT JOIN tblCurrency AS tblRecordingCostPerMinuteCurrency
        ON tblRecordingCostPerMinuteCurrency.CurrencyID = tblRateTablePKGRate.RecordingCostPerMinuteCurrency
    INNER JOIN tblRateTable
        ON tblRateTable.RateTableId = tblRateTablePKGRate.RateTableId
    WHERE
		(tblRate.CompanyID = p_companyid)
		AND (p_code is null OR tblRate.Code LIKE REPLACE(p_code, '*', '%'))
		AND (p_ApprovedStatus IS NULL OR tblRateTablePKGRate.ApprovedStatus = p_ApprovedStatus)
		AND (p_TimezonesID IS NULL OR tblRateTablePKGRate.TimezonesID = p_TimezonesID)
		AND (
				p_effective = 'All'
				OR (p_effective = 'Now' AND EffectiveDate <= NOW() )
				OR (p_effective = 'Future' AND EffectiveDate > NOW())
			);

	IF p_effective = 'Now'
	THEN
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTablePKGRate4_ as (select * from tmp_RateTablePKGRate_);
		DELETE n1 FROM tmp_RateTablePKGRate_ n1, tmp_RateTablePKGRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
			AND  n1.RateID = n2.RateID;
	END IF;


    IF p_isExport = 0
    THEN

       	SELECT * FROM tmp_RateTablePKGRate_
			ORDER BY
					 CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleDESC') THEN TimezoneTitle
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TimezoneTitleASC') THEN TimezoneTitle
                END ASC,
					 CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
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
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PackageCostPerMinuteDESC') THEN PackageCostPerMinute
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PackageCostPerMinuteASC') THEN PackageCostPerMinute
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RecordingCostPerMinuteDESC') THEN RecordingCostPerMinute
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RecordingCostPerMinuteASC') THEN RecordingCostPerMinute
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
        	FROM tmp_RateTablePKGRate_;

    END IF;

    IF p_isExport = 1
    THEN
        SELECT
        	TimezoneTitle AS `Time of Day`,
			Code AS PackageName,
			CONCAT(OneOffCostCurrencySymbol,OneOffCost) AS OneOffCost,
			CONCAT(MonthlyCostCurrencySymbol,MonthlyCost) AS MonthlyCost,
			CONCAT(PackageCostPerMinuteCurrencySymbol,PackageCostPerMinute) AS PackageCostPerMinute,
			CONCAT(RecordingCostPerMinuteCurrencySymbol,RecordingCostPerMinute) AS RecordingCostPerMinute,
			EffectiveDate,
			CONCAT(ModifiedBy,'\n',updated_at) AS `Modified By/Date`,
			CONCAT(ApprovedBy,'\n',ApprovedDate) AS `Approved By/Date`,
			ApprovedStatus
        FROM
		  		tmp_RateTablePKGRate_;
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

-- Dumping structure for procedure speakintelligentRM.prc_RateTablePKGRateUpdateDelete
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_RateTablePKGRateUpdateDelete`(
	IN `p_RateTableId` INT,
	IN `p_RateTablePKGRateId` LONGTEXT,
	IN `p_EffectiveDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_OneOffCost` VARCHAR(255),
	IN `p_MonthlyCost` VARCHAR(255),
	IN `p_PackageCostPerMinute` VARCHAR(255),
	IN `p_RecordingCostPerMinute` VARCHAR(255),
	IN `p_OneOffCostCurrency` DECIMAL(18,6),
	IN `p_MonthlyCostCurrency` DECIMAL(18,6),
	IN `p_PackageCostPerMinuteCurrency` DECIMAL(18,6),
	IN `p_RecordingCostPerMinuteCurrency` DECIMAL(18,6),
	IN `p_Critearea_Code` varchar(50),
	IN `p_Critearea_Effective` VARCHAR(50),
	IN `p_TimezonesID` INT,
	IN `p_Critearea_ApprovedStatus` TINYINT,
	IN `p_ModifiedBy` varchar(50),
	IN `p_Critearea` INT,
	IN `p_action` INT








)
ThisSP:BEGIN

	DECLARE v_RateApprovalProcess_ LONGTEXT;
	DECLARE v_RateTableAppliedTo_ INT(11);

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


	SELECT Value INTO v_RateApprovalProcess_ FROM tblCompanySetting WHERE CompanyID = (SELECT CompanyId FROM tblRateTable WHERE RateTableID = p_RateTableId) AND `Key`='RateApprovalProcess';
	SELECT AppliedTo into v_RateTableAppliedTo_ FROM tblRateTable WHERE RateTableID = p_RateTableId;

	DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTablePKGRate_;
	CREATE TEMPORARY TABLE tmp_TempRateTablePKGRate_ (
		`RateTablePKGRateId` int(11) NOT NULL,
		`RateId` int(11) NOT NULL,
		`RateTableId` int(11) NOT NULL,
		`TimezonesID` int(11) NOT NULL,
		`OneOffCost` decimal(18,6) NULL DEFAULT NULL,
		`MonthlyCost` decimal(18,6) NULL DEFAULT NULL,
		`PackageCostPerMinute` decimal(18,6) NULL DEFAULT NULL,
		`RecordingCostPerMinute` decimal(18,6) NULL DEFAULT NULL,
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`PackageCostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`RecordingCostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
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

	INSERT INTO tmp_TempRateTablePKGRate_
	SELECT
		rtr.RateTablePKGRateId,
		rtr.RateId,
		rtr.RateTableId,
		rtr.TimezonesID,
		IF(p_OneOffCost IS NOT NULL,IF(p_OneOffCost='NULL',NULL,p_OneOffCost),rtr.OneOffCost) AS OneOffCost,
		IF(p_MonthlyCost IS NOT NULL,IF(p_MonthlyCost='NULL',NULL,p_MonthlyCost),rtr.MonthlyCost) AS MonthlyCost,
		IF(p_PackageCostPerMinute IS NOT NULL,IF(p_PackageCostPerMinute='NULL',NULL,p_PackageCostPerMinute),rtr.PackageCostPerMinute) AS PackageCostPerMinute,
		IF(p_RecordingCostPerMinute IS NOT NULL,IF(p_RecordingCostPerMinute='NULL',NULL,p_RecordingCostPerMinute),rtr.RecordingCostPerMinute) AS RecordingCostPerMinute,
		IFNULL(p_OneOffCostCurrency,rtr.OneOffCostCurrency) AS OneOffCostCurrency,
		IFNULL(p_MonthlyCostCurrency,rtr.MonthlyCostCurrency) AS MonthlyCostCurrency,
		IFNULL(p_PackageCostPerMinuteCurrency,rtr.PackageCostPerMinuteCurrency) AS PackageCostPerMinuteCurrency,
		IFNULL(p_RecordingCostPerMinuteCurrency,rtr.RecordingCostPerMinuteCurrency) AS RecordingCostPerMinuteCurrency,
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
		tblRateTablePKGRate rtr
	INNER JOIN
		tblRate r ON r.RateID = rtr.RateId
	WHERE
		(
			p_EffectiveDate IS NULL OR (
				(rtr.RateID,rtr.TimezonesID) NOT IN (
						SELECT
							RateID,TimezonesID
						FROM
							tblRateTablePKGRate
						WHERE
							EffectiveDate=p_EffectiveDate AND (p_TimezonesID IS NULL OR TimezonesID = p_TimezonesID) AND
							((p_Critearea = 0 AND (FIND_IN_SET(RateTablePKGRateID,p_RateTablePKGRateID) = 0 )) OR p_Critearea = 1) AND
							RateTableId = p_RateTableId
				)
			)
		)
		AND
		(
			(p_Critearea = 0 AND (FIND_IN_SET(rtr.RateTablePKGRateID,p_RateTablePKGRateID) != 0 )) OR
			(
				p_Critearea = 1 AND
				(
					((p_Critearea_Code IS NULL) OR (p_Critearea_Code IS NOT NULL AND r.Code LIKE REPLACE(p_Critearea_Code,'*', '%'))) AND
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
		(p_TimezonesID IS NULL OR rtr.TimezonesID = p_TimezonesID);

IF p_action = 2 THEN
	CALL prc_ArchiveOldRateTablePKGRate(p_RateTableId,p_TimezonesID,p_ModifiedBy);
	delete from tblRateTablePKGRate where RateTablePKGRateID = p_RateTablePKGRateId;
END IF;	

	IF p_action = 1
	THEN

		IF p_EffectiveDate IS NOT NULL
		THEN
			CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempRateTablePKGRate_2 as (select * from tmp_TempRateTablePKGRate_);
			DELETE n1 FROM tmp_TempRateTablePKGRate_ n1, tmp_TempRateTablePKGRate_2 n2 WHERE n1.RateTablePKGRateID < n2.RateTablePKGRateID AND  n1.RateID = n2.RateID AND n1.TimezonesID = n2.TimezonesID;
		END IF;

		-- delete records which can be duplicates, we will not update them
		DELETE n1.* FROM tmp_TempRateTablePKGRate_ n1, tblRateTablePKGRate n2 WHERE n1.RateTablePKGRateID <> n2.RateTablePKGRateID AND n1.RateTableID = n2.RateTableID AND n1.TimezonesID = n2.TimezonesID AND n1.EffectiveDate = n2.EffectiveDate AND n1.RateID = n2.RateID AND n2.RateTableID=p_RateTableId;

		/*
			it was creating history evan if no columns are updating of row
			so, using this query we are removing rows which are not updating any columns
		*/
		DELETE
			temp
		FROM
			tmp_TempRateTablePKGRate_ temp
		JOIN
			tblRateTablePKGRate rtr ON rtr.RateTablePKGRateID = temp.RateTablePKGRateID
		WHERE
			(rtr.EffectiveDate = temp.EffectiveDate) AND
			(rtr.TimezonesID = temp.TimezonesID) AND
			((rtr.OneOffCost IS NULL && temp.OneOffCost IS NULL) || rtr.OneOffCost = temp.OneOffCost) AND
			((rtr.MonthlyCost IS NULL && temp.MonthlyCost IS NULL) || rtr.MonthlyCost = temp.MonthlyCost) AND
			((rtr.PackageCostPerMinute IS NULL && temp.PackageCostPerMinute IS NULL) || rtr.PackageCostPerMinute = temp.PackageCostPerMinute) AND
			((rtr.RecordingCostPerMinute IS NULL && temp.RecordingCostPerMinute IS NULL) || rtr.RecordingCostPerMinute = temp.RecordingCostPerMinute) AND
			((rtr.OneOffCostCurrency IS NULL && temp.OneOffCostCurrency IS NULL) || rtr.OneOffCostCurrency = temp.OneOffCostCurrency) AND
			((rtr.MonthlyCostCurrency IS NULL && temp.MonthlyCostCurrency IS NULL) || rtr.MonthlyCostCurrency = temp.MonthlyCostCurrency) AND
			((rtr.PackageCostPerMinuteCurrency IS NULL && temp.PackageCostPerMinuteCurrency IS NULL) || rtr.PackageCostPerMinuteCurrency = temp.PackageCostPerMinuteCurrency) AND
			((rtr.RecordingCostPerMinuteCurrency IS NULL && temp.RecordingCostPerMinuteCurrency IS NULL) || rtr.RecordingCostPerMinuteCurrency = temp.RecordingCostPerMinuteCurrency);

		-- if rate table is not vendor rate table and rate approval process is on then set approval status to awaiting approval while updating
		
		IF v_RateTableAppliedTo_!=2 AND v_RateApprovalProcess_=1
		THEN
			UPDATE
				tmp_TempRateTablePKGRate_
			SET
				ApprovedStatus = v_RateApprovalProcess_,
				ApprovedBy = NULL,
				ApprovedDate = NULL;

			INSERT INTO tblRateTablePKGRateAA (
				RateId,
				RateTableId,
				TimezonesID,
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
				created_at,
				updated_at,
				CreatedBy,
				ModifiedBy,
				ApprovedStatus,
				ApprovedBy,
				ApprovedDate
			)
			SELECT
				RateId,
				RateTableId,
				TimezonesID,
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
				created_at,
				updated_at,
				CreatedBy,
				ModifiedBy,
				ApprovedStatus,
				ApprovedBy,
				ApprovedDate
			FROM
				tmp_TempRateTablePKGRate_;

			-- LEAVE ThisSP;

		END IF;

	END IF;


	UPDATE
		tblRateTablePKGRate rtr
	INNER JOIN
		tmp_TempRateTablePKGRate_ temp ON temp.RateTablePKGRateID = rtr.RateTablePKGRateID
	SET
		rtr.EndDate = NOW()
	WHERE
		temp.RateTablePKGRateID = rtr.RateTablePKGRateID;

	CALL prc_ArchiveOldRateTablePKGRate(p_RateTableId,p_TimezonesID,p_ModifiedBy);
	
	

	IF p_action = 1
	THEN

		INSERT INTO tblRateTablePKGRate (
			RateId,
			RateTableId,
			TimezonesID,
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
			created_at,
			updated_at,
			CreatedBy,
			ModifiedBy,
			ApprovedStatus,
			ApprovedBy,
			ApprovedDate
		)
		SELECT
			RateId,
			RateTableId,
			TimezonesID,
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
			created_at,
			updated_at,
			CreatedBy,
			ModifiedBy,
			ApprovedStatus,
			ApprovedBy,
			ApprovedDate
		FROM
			tmp_TempRateTablePKGRate_;

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

-- Dumping structure for procedure speakintelligentRM.prc_RateTablePKGRateAAUpdateDelete
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_RateTablePKGRateAAUpdateDelete`(
	IN `p_RateTableId` INT,
	IN `p_RateTablePKGRateAAId` LONGTEXT,
	IN `p_EffectiveDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_OneOffCost` VARCHAR(255),
	IN `p_MonthlyCost` VARCHAR(255),
	IN `p_PackageCostPerMinute` VARCHAR(255),
	IN `p_RecordingCostPerMinute` VARCHAR(255),
	IN `p_OneOffCostCurrency` DECIMAL(18,6),
	IN `p_MonthlyCostCurrency` DECIMAL(18,6),
	IN `p_PackageCostPerMinuteCurrency` DECIMAL(18,6),
	IN `p_RecordingCostPerMinuteCurrency` DECIMAL(18,6),
	IN `p_Critearea_Code` varchar(50),
	IN `p_Critearea_Effective` VARCHAR(50),
	IN `p_TimezonesID` INT,
	IN `p_Critearea_ApprovedStatus` TINYINT,
	IN `p_ModifiedBy` varchar(50),
	IN `p_Critearea` INT,
	IN `p_action` INT





)
ThisSP:BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTablePKGRate_;
	CREATE TEMPORARY TABLE tmp_TempRateTablePKGRate_ (
		`RateTablePKGRateAAId` int(11) NOT NULL,
		`RateId` int(11) NOT NULL,
		`RateTableId` int(11) NOT NULL,
		`TimezonesID` int(11) NOT NULL,
		`OneOffCost` decimal(18,6) NULL DEFAULT NULL,
		`MonthlyCost` decimal(18,6) NULL DEFAULT NULL,
		`PackageCostPerMinute` decimal(18,6) NULL DEFAULT NULL,
		`RecordingCostPerMinute` decimal(18,6) NULL DEFAULT NULL,
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`PackageCostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`RecordingCostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
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

	INSERT INTO tmp_TempRateTablePKGRate_
	SELECT
		rtr.RateTablePKGRateAAId,
		rtr.RateId,
		rtr.RateTableId,
		rtr.TimezonesID,
		IF(p_OneOffCost IS NOT NULL,IF(p_OneOffCost='NULL',NULL,p_OneOffCost),rtr.OneOffCost) AS OneOffCost,
		IF(p_MonthlyCost IS NOT NULL,IF(p_MonthlyCost='NULL',NULL,p_MonthlyCost),rtr.MonthlyCost) AS MonthlyCost,
		IF(p_PackageCostPerMinute IS NOT NULL,IF(p_PackageCostPerMinute='NULL',NULL,p_PackageCostPerMinute),rtr.PackageCostPerMinute) AS PackageCostPerMinute,
		IF(p_RecordingCostPerMinute IS NOT NULL,IF(p_RecordingCostPerMinute='NULL',NULL,p_RecordingCostPerMinute),rtr.RecordingCostPerMinute) AS RecordingCostPerMinute,
		IFNULL(p_OneOffCostCurrency,rtr.OneOffCostCurrency) AS OneOffCostCurrency,
		IFNULL(p_MonthlyCostCurrency,rtr.MonthlyCostCurrency) AS MonthlyCostCurrency,
		IFNULL(p_PackageCostPerMinuteCurrency,rtr.PackageCostPerMinuteCurrency) AS PackageCostPerMinuteCurrency,
		IFNULL(p_RecordingCostPerMinuteCurrency,rtr.RecordingCostPerMinuteCurrency) AS RecordingCostPerMinuteCurrency,
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
		tblRateTablePKGRateAA rtr
	INNER JOIN
		tblRate r ON r.RateID = rtr.RateId
	WHERE
		(
			p_EffectiveDate IS NULL OR (
				(rtr.RateID,rtr.TimezonesID) NOT IN (
						SELECT
							RateID,TimezonesID
						FROM
							tblRateTablePKGRateAA
						WHERE
							EffectiveDate=p_EffectiveDate AND (p_TimezonesID IS NULL OR TimezonesID = p_TimezonesID) AND
							((p_Critearea = 0 AND (FIND_IN_SET(RateTablePKGRateAAID,p_RateTablePKGRateAAID) = 0 )) OR p_Critearea = 1) AND
							RateTableId = p_RateTableId
				)
			)
		)
		AND
		(
			(p_Critearea = 0 AND (FIND_IN_SET(rtr.RateTablePKGRateAAID,p_RateTablePKGRateAAID) != 0 )) OR
			(
				p_Critearea = 1 AND
				(
					((p_Critearea_Code IS NULL) OR (p_Critearea_Code IS NOT NULL AND r.Code LIKE REPLACE(p_Critearea_Code,'*', '%'))) AND
				--	(p_Critearea_ApprovedStatus IS NULL OR rtr.ApprovedStatus = p_Critearea_ApprovedStatus) AND
					(
						p_Critearea_Effective = 'All' OR
						(p_Critearea_Effective = 'Now' AND rtr.EffectiveDate <= NOW() ) OR
						(p_Critearea_Effective = 'Future' AND rtr.EffectiveDate > NOW() )
					)
				)
			)
		) AND
		rtr.RateTableId = p_RateTableId AND
		(p_TimezonesID IS NULL OR rtr.TimezonesID = p_TimezonesID);

IF p_action = 2 THEN
	CALL prc_ArchiveOldRateTablePKGRateAA(p_RateTableId,p_TimezonesID,p_ModifiedBy);
	delete from tblRateTablePKGRateAA where RateTablePKGRateAAID = p_RateTablePKGRateAAId;
END IF;	



	IF p_action = 1
	THEN

		IF p_EffectiveDate IS NOT NULL
		THEN
			CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempRateTablePKGRate_2 as (select * from tmp_TempRateTablePKGRate_);
			DELETE n1 FROM tmp_TempRateTablePKGRate_ n1, tmp_TempRateTablePKGRate_2 n2 WHERE n1.RateTablePKGRateAAID < n2.RateTablePKGRateAAID AND  n1.RateID = n2.RateID AND n1.TimezonesID = n2.TimezonesID;
		END IF;


		-- delete records which can be duplicates, we will not update them
		DELETE n1.* FROM tmp_TempRateTablePKGRate_ n1, tblRateTablePKGRateAA n2 WHERE n1.RateTablePKGRateAAID <> n2.RateTablePKGRateAAID AND n1.RateTableID = n2.RateTableID AND n1.TimezonesID = n2.TimezonesID AND n1.EffectiveDate = n2.EffectiveDate AND n1.RateID = n2.RateID AND n2.RateTableID=p_RateTableId;

		-- remove rejected rates from temp table while updating so, it can't be update and delete
		DELETE n1 FROM tmp_TempRateTablePKGRate_ n1 WHERE ApprovedStatus = 2;


		/*
			it was creating history evan if no columns are updating of row
			so, using this query we are removing rows which are not updating any columns
		*/
		DELETE
			temp
		FROM
			tmp_TempRateTablePKGRate_ temp
		JOIN
			tblRateTablePKGRateAA rtr ON rtr.RateTablePKGRateAAID = temp.RateTablePKGRateAAID
		WHERE
			(rtr.EffectiveDate = temp.EffectiveDate) AND
			(rtr.TimezonesID = temp.TimezonesID) AND
			((rtr.OneOffCost IS NULL && temp.OneOffCost IS NULL) || rtr.OneOffCost = temp.OneOffCost) AND
			((rtr.MonthlyCost IS NULL && temp.MonthlyCost IS NULL) || rtr.MonthlyCost = temp.MonthlyCost) AND
			((rtr.PackageCostPerMinute IS NULL && temp.PackageCostPerMinute IS NULL) || rtr.PackageCostPerMinute = temp.PackageCostPerMinute) AND
			((rtr.RecordingCostPerMinute IS NULL && temp.RecordingCostPerMinute IS NULL) || rtr.RecordingCostPerMinute = temp.RecordingCostPerMinute) AND
			((rtr.OneOffCostCurrency IS NULL && temp.OneOffCostCurrency IS NULL) || rtr.OneOffCostCurrency = temp.OneOffCostCurrency) AND
			((rtr.MonthlyCostCurrency IS NULL && temp.MonthlyCostCurrency IS NULL) || rtr.MonthlyCostCurrency = temp.MonthlyCostCurrency) AND
			((rtr.PackageCostPerMinuteCurrency IS NULL && temp.PackageCostPerMinuteCurrency IS NULL) || rtr.PackageCostPerMinuteCurrency = temp.PackageCostPerMinuteCurrency) AND
			((rtr.RecordingCostPerMinuteCurrency IS NULL && temp.RecordingCostPerMinuteCurrency IS NULL) || rtr.RecordingCostPerMinuteCurrency = temp.RecordingCostPerMinuteCurrency);

	END IF;


	UPDATE
		tblRateTablePKGRateAA rtr
	INNER JOIN
		tmp_TempRateTablePKGRate_ temp ON temp.RateTablePKGRateAAID = rtr.RateTablePKGRateAAID
	SET
		rtr.EndDate = NOW()
	WHERE
		temp.RateTablePKGRateAAID = rtr.RateTablePKGRateAAID;

	CALL prc_ArchiveOldRateTablePKGRateAA(p_RateTableId,p_TimezonesID,p_ModifiedBy);

	IF p_action = 1
	THEN


		INSERT INTO tblRateTablePKGRateAA (
			RateId,
			RateTableId,
			TimezonesID,
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
			created_at,
			updated_at,
			CreatedBy,
			ModifiedBy,
			ApprovedStatus,
			ApprovedBy,
			ApprovedDate
		)
		SELECT
			RateId,
			RateTableId,
			TimezonesID,
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
			created_at,
			updated_at,
			CreatedBy,
			ModifiedBy,
			ApprovedStatus,
			ApprovedBy,
			ApprovedDate
		FROM
			tmp_TempRateTablePKGRate_
		WHERE
			ApprovedStatus = 0; -- only allow awaiting approval rates to be updated

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

