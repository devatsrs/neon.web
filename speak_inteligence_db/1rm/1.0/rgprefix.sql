use speakintelligentRM;
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

-- Dumping structure for procedure speakintelligentRM.prc_WSGenerateRateTableWithPrefix
DROP PROCEDURE IF EXISTS `prc_WSGenerateRateTableWithPrefix`;
DELIMITER //
CREATE  PROCEDURE `prc_WSGenerateRateTableWithPrefix`(
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
--		DECLARE @v_RTRowCount_ INT;
--		DECLARE @v_RatePosition_ INT;
	--	DECLARE @v_Use_Preference_ INT;
	--	DECLARE @v_CurrencyID_ INT;
	--	DECLARE @v_CompanyCurrencyID_ INT;
		-- DECLARE @v_Average_ TINYINT;
		-- DECLARE @v_CompanyId_ INT;
	--	DECLARE @v_codedeckid_ INT;
		-- DECLARE @v_trunk_ INT;
--		DECLARE @v_rateRuleId_ INT;
	--	DECLARE @v_RateGeneratorName_ VARCHAR(200);
--		DECLARE @v_pointer_ INT ;
--		DECLARE @v_rowCount_ INT ;
	--	DECLARE @v_percentageRate INT ;

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



		SET p_EffectiveDate = CAST(p_EffectiveDate AS DATE);
		SET v_TimezonesID = IF(p_IsMerge=1,p_MergeInto,p_TimezonesID);

		SET @v_RATE_STATUS_AWAITING  = 0;
		SET @v_RATE_STATUS_APPROVED  = 1;
		SET @v_RATE_STATUS_REJECTED  = 2;
		SET @v_RATE_STATUS_DELETE    = 3;


		IF p_rateTableName IS NOT NULL
		THEN


			SET @v_RTRowCount_ = (SELECT
														 COUNT(*)
													 FROM tblRateTable
													 WHERE RateTableName = p_rateTableName
																 AND CompanyId = (SELECT
																										CompanyId
																									FROM tblRateGenerator
																									WHERE RateGeneratorID = p_RateGeneratorId));

			IF @v_RTRowCount_ > 0
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
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci,
			FinalRankNumber int,
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

		SELECT CurrencyID INTO @v_CurrencyID_ FROM  tblRateGenerator WHERE RateGeneratorId = p_RateGeneratorId;



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


			INTO @v_Use_Preference_, @v_RatePosition_, @v_CompanyId_, @v_codedeckid_, @v_trunk_, @v_Average_, @v_RateGeneratorName_,v_LessThenRate,v_ChargeRate,@v_percentageRate, @v_AppliedTo, @v_Reseller
		FROM tblRateGenerator
		WHERE RateGeneratorId = p_RateGeneratorId;



		SELECT CurrencyId INTO @v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = @v_CompanyId_;

		SELECT IFNULL(Value,0)  INTO @v_RateApprovalProcess_ FROM tblCompanySetting WHERE CompanyID = @v_CompanyId_ AND `Key`='RateApprovalProcess';

		SELECT IFNULL(Value,0) INTO @v_UseVendorCurrencyInRateGenerator_ FROM tblCompanySetting WHERE CompanyID = @v_CompanyId_ AND `Key`='UseVendorCurrencyInRateGenerator';



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


			insert into tmp_Raterules_dup2 (
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
						 AND  tblVendorConnection.TrunkID = @v_trunk_
						 AND tblVendorConnection.Active = 1
						 AND tblVendorConnection.RateTypeID = 1
				inner join tblRateTable on  tblRateTable.RateTableId = tblVendorConnection.RateTableID
			WHERE tblRateRule.RateGeneratorId = p_RateGeneratorId;

		SET @v_pointer_ = 1;
		SET @v_rowCount_ = (SELECT COUNT(rateruleid) FROM tmp_Raterules_);







		insert into tmp_code_
			SELECT
				tblRate.code
			FROM tblRate
				JOIN tmp_Codedecks_ cd
					ON tblRate.CodeDeckId = cd.CodeDeckId
				JOIN tmp_Raterules_ rr
					ON ( rr.code != '' AND tblRate.Code LIKE (REPLACE(rr.code,'*', '%%')) )
								AND
						( rr.DestinationType = '' OR ( tblRate.`Type` = DestinationType ))
								AND
						( rr.DestinationCountryID is null  OR (tblRate.`CountryID` = DestinationCountryID ))




			Order by tblRate.code ;


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
						( rr.OriginationCountryID is null OR (tblRate.`CountryID` = OriginationCountryID ))



			Order by tblRate.code ;









		SELECT CurrencyId INTO @v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = @v_CompanyId_;
		SET @IncludeAccountIds = (SELECT GROUP_CONCAT(AccountId) from tblRateRule rr inner join  tblRateRuleSource rrs on rr.RateRuleId = rrs.RateRuleId where rr.RateGeneratorId = p_RateGeneratorId ) ;



		IF(p_IsMerge = 1)
		THEN




			INSERT INTO tmp_VendorCurrentRates1_
				Select DISTINCT AccountId,MAX(AccountName) AS AccountName,MAX(OriginationCode) AS OriginationCode,MAX(OriginationDescription) AS OriginationDescription,MAX(Code) AS Code,MAX(Description) AS Description, ROUND(IF(p_TakePrice=1,MAX(Rate),MIN(Rate)), 6) AS Rate, ROUND(IF(p_TakePrice=1,MAX(RateN),MIN(RateN)), 6) AS RateN,IF(p_TakePrice=1,MAX(ConnectionFee),MIN(ConnectionFee)) AS ConnectionFee,EffectiveDate,TrunkID,p_MergeInto AS TimezonesID,MAX(CountryID) AS CountryID,RateID,MAX(Preference) AS Preference, max(RateCurrency) as RateCurrency ,max(ConnectionFeeCurrency) as  ConnectionFeeCurrency
				FROM (
							 SELECT  vt.AccountId,tblAccount.AccountName, r2.Code as OriginationCode, r2.Description as OriginationDescription,tblRate.Code, tblRate.Description,IFNULL(RateCurrency,rt.CurrencyID) as RateCurrency ,IFNULL(ConnectionFeeCurrency,rt.CurrencyID) as ConnectionFeeCurrency,
									CASE WHEN  tblAccount.CurrencyId = @v_CurrencyID_
										THEN
											tblRateTableRate.Rate
									WHEN  @v_CompanyCurrencyID_ = @v_CurrencyID_
										THEN
											(
												( tblRateTableRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = @v_CompanyId_ ) )
											)
									ELSE
										(

											(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ )
											* (tblRateTableRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = @v_CompanyId_ ))
										)
									END as Rate,
									CASE WHEN  tblAccount.CurrencyId = @v_CurrencyID_
										THEN
											tblRateTableRate.RateN
									WHEN  @v_CompanyCurrencyID_ = @v_CurrencyID_
										THEN
											(
												( tblRateTableRate.rateN  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = @v_CompanyId_ ) )
											)
									ELSE
										(

											(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ )
											* (tblRateTableRate.rateN  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = @v_CompanyId_ ))
										)
									END as RateN,
									ConnectionFee,
									DATE_FORMAT (tblRateTableRate.EffectiveDate, '%Y-%m-%d') AS EffectiveDate,
								 vt.TrunkID,
								 tblRateTableRate.TimezonesID, tblRate.CountryID, tblRate.RateID,IFNULL(Preference, 5) AS Preference,
																																					@row_num := IF(@prev_AccountId = vt.AccountID AND @prev_TrunkID = vt.TrunkID AND @prev_RateId = tblRateTableRate.RateID AND @prev_EffectiveDate >= tblRateTableRate.EffectiveDate, @row_num + 1, 1) AS RowID,
								 @prev_AccountId := vt.AccountID,
								 @prev_TrunkID := vt.TrunkID,
								 @prev_TimezonesID := tblRateTableRate.TimezonesID,
								 @prev_RateId := tblRateTableRate.RateID,
								 @prev_EffectiveDate := tblRateTableRate.EffectiveDate
							 FROM tblRateTableRate
								 Inner join tblRateTable rt on  rt.CompanyID = @v_CompanyId_ and rt.RateTableID = tblRateTableRate.RateTableID
								Inner join tblVendorConnection vt on vt.CompanyID = @v_CompanyId_ AND vt.RateTableID = tblRateTableRate.RateTableID  and vt.RateTypeID = 1  and vt.Active = 1  and vt.TrunkID =  @v_trunk_

								 Inner join tblTimezones t on t.TimezonesID = tblRateTableRate.TimezonesID AND t.Status = 1
								 inner join tmp_Codedecks_ tcd on rt.CodeDeckId = tcd.CodeDeckId
								 INNER JOIN tblAccount   ON  tblAccount.AccountID = vt.AccountId AND tblAccount.CompanyID = @v_CompanyId_ and tblAccount.IsVendor = 1
								 INNER JOIN tblRate ON tblRate.CompanyID = @v_CompanyId_  AND tblRate.CodeDeckId = rt.CodeDeckId  AND    tblRateTableRate.RateId = tblRate.RateID
								 INNER JOIN tmp_code_ tcode ON tcode.Code  = tblRate.Code

 								 LEFT JOIN tblRate r2 ON r2.CompanyID = @v_CompanyId_  AND r2.CodeDeckId = rt.CodeDeckId  AND    tblRateTableRate.OriginationRateId = r2.RateID
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
								 AND vt.TrunkID = @v_trunk_
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
 							 SELECT  vt.AccountId,tblAccount.AccountName, r2.Code as OriginationCode, r2.Description as OriginationDescription,tblRate.Code, tblRate.Description,IFNULL(RateCurrency,rt.CurrencyID) as RateCurrency,IFNULL(ConnectionFeeCurrency,rt.CurrencyID) as ConnectionFeeCurrency,

								CASE WHEN  tblAccount.CurrencyId = @v_CurrencyID_
									THEN
										tblRateTableRate.Rate
								WHEN  @v_CompanyCurrencyID_ = @v_CurrencyID_
									THEN
										(
											( tblRateTableRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = @v_CompanyId_ ) )
										)
								ELSE
									(

										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ )
										* (tblRateTableRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = @v_CompanyId_ ))
									)
								END as Rate,
								CASE WHEN  tblAccount.CurrencyId = @v_CurrencyID_
									THEN
										tblRateTableRate.RateN
								WHEN  @v_CompanyCurrencyID_ = @v_CurrencyID_
									THEN
										(
											( tblRateTableRate.rateN  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = @v_CompanyId_ ) )
										)
								ELSE
									(

										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ )
										* (tblRateTableRate.rateN  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = @v_CompanyId_ ))
									)
								END as RateN,
								 ConnectionFee,
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
								Inner join  tblRateTable rt on  rt.CompanyID = @v_CompanyId_ and rt.RateTableID = tblRateTableRate.RateTableID
								Inner join tblVendorConnection vt on vt.CompanyID = @v_CompanyId_ AND vt.RateTableID = tblRateTableRate.RateTableID  and vt.RateTypeID = 1  and vt.Active = 1  and vt.TrunkID =  @v_trunk_
								 Inner join tblTimezones t on t.TimezonesID = tblRateTableRate.TimezonesID AND t.Status = 1
								 inner join tmp_Codedecks_ tcd on rt.CodeDeckId = tcd.CodeDeckId
								 INNER JOIN tblAccount   ON  tblAccount.AccountID = vt.AccountId AND tblAccount.CompanyID = @v_CompanyId_ and tblAccount.IsVendor = 1
								 INNER JOIN tblRate ON tblRate.CompanyID = @v_CompanyId_  AND tblRate.CodeDeckId = rt.CodeDeckId  AND    tblRateTableRate.RateId = tblRate.RateID
								 INNER JOIN tmp_code_ tcode ON tcode.Code  = tblRate.Code

 								 LEFT JOIN tblRate r2 ON r2.CompanyID = @v_CompanyId_  AND r2.CodeDeckId = rt.CodeDeckId  AND    tblRateTableRate.OriginationRateId = r2.RateID
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
								 AND vt.TrunkID = @v_trunk_
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



		IF p_GroupBy = 'Desc'
		THEN





			INSERT INTO tmp_VendorCurrentRates_GroupBy_
				Select AccountId,max(AccountName),max(OriginationCode),OriginationDescription,max(Code),Description,max(Rate),max(RateN),max(ConnectionFee),max(EffectiveDate),TrunkID,TimezonesID,max(CountryID),max(RateID),max(Preference),max(RateCurrency) as RateCurrency ,max(ConnectionFeeCurrency) as  ConnectionFeeCurrency
				FROM
				(
					Select AccountId,AccountName,OriginationCode,OriginationDescription,r.Code,r.Description, Rate, RateN,ConnectionFee,EffectiveDate,TrunkID,TimezonesID,r.CountryID,r.RateID,Preference,RateCurrency,ConnectionFeeCurrency
					FROM tmp_VendorCurrentRates_ v
					Inner join  tblRate r   on r.CodeDeckId = @v_codedeckid_ AND r.Code = v.Code
				) tmp
				GROUP BY AccountId, TrunkID, TimezonesID, OriginationDescription,Description
				order by OriginationDescription,Description asc;




				truncate table tmp_VendorCurrentRates_;

				INSERT INTO tmp_VendorCurrentRates_ (AccountId,AccountName,OriginationCode,OriginationDescription,Code,Description, Rate, RateN,ConnectionFee,EffectiveDate,TrunkID,TimezonesID,CountryID,RateID,Preference,RateCurrency,ConnectionFeeCurrency)
			  		SELECT AccountId,AccountName,OriginationCode,OriginationDescription,Code,Description, Rate, RateN,ConnectionFee,EffectiveDate,TrunkID,TimezonesID,CountryID,RateID,Preference,RateCurrency,ConnectionFeeCurrency
					FROM tmp_VendorCurrentRates_GroupBy_;


		END IF;


		insert into tmp_VendorRate_ (
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

		)
			select
				AccountId ,
				AccountName ,
				IFNULL(OriginationCode,''),
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

				Code as RowCode
			from tmp_VendorCurrentRates_;

		--	select * from tmp_VendorRate_;

		WHILE @v_pointer_ <= @v_rowCount_
		DO

			SET @v_rateRuleId_ = (SELECT rateruleid FROM tmp_Raterules_ rr WHERE rr.RowNo = @v_pointer_);


	--	SELECT @v_rateRuleId_;

			INSERT INTO tmp_Rates2_ (OriginationCode,OriginationDescription,code,description,rate,rateN,ConnectionFee,AccountID,RateCurrency,ConnectionFeeCurrency)
				select  OriginationCode,OriginationDescription,code,description,rate,rateN,ConnectionFee,AccountID,RateCurrency,ConnectionFeeCurrency from tmp_Rates_;

				IF p_GroupBy = 'Desc'
				THEN


						INSERT IGNORE INTO tmp_Rates3_ (OriginationCode,OriginationDescription,code,description)
						 select distinct tmpvr.OriginationCode,tmpvr.OriginationDescription,r.code,r.description
						from tmp_VendorCurrentRates1_  tmpvr
						Inner join  tblRate r   on r.CodeDeckId = @v_codedeckid_ AND r.Code = tmpvr.Code
						left join  tblRate r2   on r2.CodeDeckId = @v_codedeckid_ AND r2.Code = tmpvr.OriginationCode
						inner JOIN tmp_Raterules_ rr ON rr.RateRuleId = @v_rateRuleId_ and

																 (
																	 ( rr.OriginationCode = ''  OR ( rr.OriginationCode != '' AND tmpvr.OriginationCode  LIKE (REPLACE(rr.OriginationCode,'*', '%%')) ) )
											 							AND
																	( rr.OriginationType = '' OR ( r2.`Type` = rr.OriginationType ))
																		AND
																	( rr.OriginationCountryID is null OR (r2.`CountryID` = rr.OriginationCountryID ))




																 )
																 AND
																(
																	 ( rr.code = '' OR ( rr.code != '' AND tmpvr.Code LIKE (REPLACE(rr.code,'*', '%%')) ))

																		AND
																		( rr.DestinationType = '' OR ( r.`Type` = rr.DestinationType ))
																		AND
																		( rr.DestinationCountryID is null  OR (r.`CountryID` = rr.DestinationCountryID ))



																 )
																left JOIN tmp_Raterules_dup rr2 ON rr2.Order > rr.Order and
																(
																	 ( rr2.OriginationCode = ''  OR ( rr2.OriginationCode != '' AND tmpvr.OriginationCode  LIKE (REPLACE(rr2.OriginationCode,'*', '%%')) ) )

																		AND
																		( rr2.OriginationType = '' OR ( r2.`Type` = rr2.OriginationType ))
																		AND
																		( rr2.OriginationCountryID is null OR (r2.`CountryID` = rr2.OriginationCountryID ))




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

			IF( @v_Use_Preference_ = 0 ) THEN


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

						RowCode,
						FinalRankNumber
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
													@rank := CASE WHEN ( @prev_Description = vr.Description  AND @prev_Rate <=  vr.Rate AND (@v_percentageRate = 0 OR  (@v_percentageRate > 0 AND ROUND(((vr.Rate - @prev_Rate) /( @prev_Rate * 100)),2) > @v_percentageRate) )  ) THEN @rank+1
													 ELSE
														 1
													 END

								ELSE	@rank := CASE WHEN ( @prev_RowCode = vr.RowCode  AND @prev_Rate <=  vr.Rate  AND   (@v_percentageRate = 0 OR  (@v_percentageRate > 0 AND ROUND(((vr.Rate - @prev_Rate) /( @prev_Rate * 100)),2) > @v_percentageRate) ) ) THEN @rank+1

													 ELSE
														 1
													 END
								END
									AS FinalRankNumber,
								@prev_RowCode  := vr.RowCode,
								@prev_Description  := vr.Description,
								@prev_Rate  := vr.Rate
							from (
										 select distinct tmpvr.*
										 from tmp_VendorRate_  tmpvr
											Inner join  tblRate r   on r.CodeDeckId = @v_codedeckid_ AND r.Code = tmpvr.Code
											left join  tblRate r2   on r2.CodeDeckId = @v_codedeckid_ AND r2.Code = tmpvr.OriginationCode
										inner JOIN tmp_Raterules_ rr ON rr.RateRuleId = @v_rateRuleId_ and
										 (
											 ( rr.OriginationCode = ''  OR ( rr.OriginationCode != '' AND tmpvr.OriginationCode  LIKE (REPLACE(rr.OriginationCode,'*', '%%')) ) )
													AND
											( rr.OriginationType = '' OR ( r2.`Type` = rr.OriginationType ))
												AND
											( rr.OriginationCountryID is null OR (r2.`CountryID` = rr.OriginationCountryID ))




										 )
										 AND
										(
											 ( rr.code = '' OR ( rr.code != '' AND tmpvr.RowCode LIKE (REPLACE(rr.code,'*', '%%')) ))

												AND
												( rr.DestinationType = '' OR ( r.`Type` = rr.DestinationType ))
												AND
												( rr.DestinationCountryID is null  OR (r.`CountryID` = rr.DestinationCountryID ))



										 )
										left JOIN tmp_Raterules_dup rr2 ON rr2.Order > rr.Order and
										(
											 ( rr2.OriginationCode = ''  OR ( rr2.OriginationCode != '' AND tmpvr.OriginationCode  LIKE (REPLACE(rr2.OriginationCode,'*', '%%')) ) )

												AND
												( rr2.OriginationType = '' OR ( r2.`Type` = rr2.OriginationType ))
												AND
												( rr2.OriginationCountryID is null OR (r2.`CountryID` = rr2.OriginationCountryID ))




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
								,(SELECT @rank := 0 , @prev_RowCode := '' , @prev_Rate := 0 , @prev_Description := '' ) x
							order by
								CASE WHEN p_GroupBy = 'Desc'  THEN
									vr.Description
								ELSE
									vr.RowCode
								END , vr.Rate,vr.AccountId

						) tbl1
					where FinalRankNumber <= @v_RatePosition_;


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
						RowCode,
						FinalRankNumber
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
																		WHEN (@prev_Description  = vr.Description  AND @prev_Preference = vr.Preference AND @prev_Rate <= vr.Rate  AND  (@v_percentageRate = 0 OR  (@v_percentageRate > 0 AND ROUND(((vr.Rate - @prev_Rate) /( @prev_Rate * 100)),2) > @v_percentageRate) )  ) THEN @preference_rank + 1

																		ELSE 1 END
								ELSE
												@preference_rank := CASE WHEN (@prev_Code  = vr.RowCode  AND @prev_Preference > vr.Preference  )   THEN @preference_rank + 1
																		WHEN (@prev_Code  = vr.RowCode  AND @prev_Preference = vr.Preference AND @prev_Rate <= vr.Rate   AND  (@v_percentageRate = 0 OR  (@v_percentageRate > 0 AND ROUND(((vr.Rate - @prev_Rate) /( @prev_Rate * 100)),2) > @v_percentageRate) ) ) THEN @preference_rank + 1

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
										Inner join  tblRate r   on r.CodeDeckId = @v_codedeckid_ AND r.Code = tmpvr.Code
										left join  tblRate r2   on r2.CodeDeckId = @v_codedeckid_ AND r2.Code = tmpvr.OriginationCode
										inner JOIN tmp_Raterules_ rr ON rr.RateRuleId = @v_rateRuleId_ and

										 (
											 ( rr.OriginationCode = ''  OR ( rr.OriginationCode != '' AND tmpvr.OriginationCode  LIKE (REPLACE(rr.OriginationCode,'*', '%%')) ) )
													AND
											( rr.OriginationType = '' OR ( r2.`Type` = rr.OriginationType ))
												AND
											( rr.OriginationCountryID is null OR (r2.`CountryID` = rr.OriginationCountryID ))




										 )
										 AND
										(
											 ( rr.code = '' OR ( rr.code != '' AND tmpvr.RowCode LIKE (REPLACE(rr.code,'*', '%%')) ))

												AND
												( rr.DestinationType = '' OR ( r.`Type` = rr.DestinationType ))
												AND
												( rr.DestinationCountryID is null  OR (r.`CountryID` = rr.DestinationCountryID ))



										 )
										left JOIN tmp_Raterules_dup rr2 ON rr2.Order > rr.Order and
										(
											 ( rr2.OriginationCode = ''  OR ( rr2.OriginationCode != '' AND tmpvr.OriginationCode  LIKE (REPLACE(rr2.OriginationCode,'*', '%%')) ) )

												AND
												( rr2.OriginationType = '' OR ( r2.`Type` = rr2.OriginationType ))
												AND
												( rr2.OriginationCountryID is null OR (r2.`CountryID` = rr2.OriginationCountryID ))




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
									vr.Description
								ELSE
									vr.RowCode
								END , vr.Preference DESC ,vr.Rate ASC ,vr.AccountId ASC
						) tbl1
					where FinalRankNumber <= @v_RatePosition_;

			END IF;



			truncate   tmp_VRatesstage2_;

			INSERT INTO tmp_VRatesstage2_
			(
			RowCode,
			OriginationCode,
			OriginationDescription,
			code,
			description,
			rate,
			rateN,
			ConnectionFee,
			FinalRankNumber,
			AccountID,
			RateCurrency,
			ConnectionFeeCurrency
			)
				SELECT
					vr.RowCode,
					vr.OriginationCode,
					vr.OriginationDescription,
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



			IF @v_Average_ = 0
			THEN


				IF p_GroupBy = 'Desc'
				THEN

						insert into tmp_dupVRatesstage2_
						SELECT max(OriginationCode) , OriginationDescription,max(RowCode) , description,   MAX(FinalRankNumber) AS MaxFinalRankNumber
						FROM tmp_VRatesstage2_ GROUP BY OriginationDescription, description;

					truncate tmp_Vendorrates_stage3_;
					INSERT INTO tmp_Vendorrates_stage3_
						select  vr.OriginationCode ,vr.OriginationDescription ,  vr.RowCode as RowCode ,vr.description , vr.rate as rate , vr.rateN as rateN , vr.ConnectionFee as  ConnectionFee,vr.AccountID,vr.RateCurrency,vr.ConnectionFeeCurrency
						from tmp_VRatesstage2_ vr
							INNER JOIN tmp_dupVRatesstage2_ vr2
								ON (vr.description = vr2.description AND  vr.FinalRankNumber = vr2.FinalRankNumber);


				ELSE

					insert into tmp_dupVRatesstage2_
						SELECT OriginationCode , MAX(OriginationDescription), RowCode , MAX(description),   MAX(FinalRankNumber) AS MaxFinalRankNumber
						FROM tmp_VRatesstage2_ GROUP BY OriginationCode,RowCode;

					truncate tmp_Vendorrates_stage3_;
					INSERT INTO tmp_Vendorrates_stage3_
						select  vr.OriginationCode ,vr.OriginationDescription , vr.RowCode as RowCode ,vr.description , vr.rate as rate , vr.rateN as rateN , vr.ConnectionFee as  ConnectionFee,vr.AccountID,vr.RateCurrency,vr.ConnectionFeeCurrency
						from tmp_VRatesstage2_ vr
							INNER JOIN tmp_dupVRatesstage2_ vr2
								ON (vr.RowCode = vr2.RowCode AND  vr.FinalRankNumber = vr2.FinalRankNumber);

				END IF;

				INSERT IGNORE INTO tmp_Rates_ (OriginationCode ,OriginationDescription,code,description,rate,rateN,ConnectionFee,PreviousRate,AccountID,RateCurrency,ConnectionFeeCurrency)
                SELECT
				OriginationCode,
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
			    	 LEFT join tblRateRuleMargin rule_mgn1 on  rule_mgn1.RateRuleId = @v_rateRuleId_ and ( (rule_mgn1.MinRate is null AND  rule_mgn1.MaxRate is null)   OR (vRate.rate Between rule_mgn1.MinRate and rule_mgn1.MaxRate) )
                LEFT join tblRateRuleMargin rule_mgn2 on  rule_mgn2.RateRuleId = @v_rateRuleId_ and ( (rule_mgn2.MinRate is null AND  rule_mgn2.MaxRate is null)   OR (vRate.rateN Between rule_mgn2.MinRate and rule_mgn2.MaxRate) );




			ELSE

				INSERT IGNORE INTO tmp_Rates_ (OriginationCode ,OriginationDescription,code,description,rate,rateN,ConnectionFee,PreviousRate,AccountID,RateCurrency,ConnectionFeeCurrency)
                SELECT
				OriginationCode,
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
                        max(OriginationCode) as OriginationCode,
						max(OriginationDescription) as OriginationDescription,
						max(RowCode) AS RowCode,
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
                          description
                        ELSE  RowCode
      					END

                    )  vRate
			       LEFT join tblRateRuleMargin rule_mgn1 on  rule_mgn1.RateRuleId = @v_rateRuleId_ and ( (rule_mgn1.MinRate is null AND  rule_mgn1.MaxRate is null)   OR (vRate.rate Between rule_mgn1.MinRate and rule_mgn1.MaxRate) )
                LEFT join tblRateRuleMargin rule_mgn2 on  rule_mgn2.RateRuleId = @v_rateRuleId_ and ( (rule_mgn2.MinRate is null AND  rule_mgn2.MaxRate is null)   OR (vRate.rateN Between rule_mgn2.MinRate and rule_mgn2.MaxRate) );





			END IF;


			SET @v_pointer_ = @v_pointer_ + 1;


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

									CASE WHEN  RateCurrency = @v_CurrencyID_ THEN
											Rate
									ELSE
										(

											(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = RateCurrency and  CompanyID = @v_CompanyId_ )
											* (Rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
										)
									END,



			RateN =

								CASE WHEN  RateCurrency = @v_CurrencyID_ THEN
											RateN
									ELSE
										(

											(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = RateCurrency and  CompanyID = @v_CompanyId_ )
											* (RateN  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
										)
									END,
			ConnectionFee =
								CASE WHEN  ConnectionFeeCurrency = @v_CurrencyID_ THEN
											ConnectionFee
									ELSE
										(

											(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = RateCurrency and  CompanyID = @v_CompanyId_ )
											* (ConnectionFee  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = @v_CurrencyID_ and  CompanyID = @v_CompanyId_ ))
										)
									END
			;

		END IF;



		 -- leave GenerateRateTable;

		START TRANSACTION;


		IF p_RateTableId = -1
		THEN

			SET @v_RoundChargedAmount = 6;
			SET @v_TerminationType = 1;

			INSERT INTO tblRateTable (Type,CompanyId, RateTableName, RateGeneratorID, TrunkID, CodeDeckId,CurrencyID,RoundChargedAmount,AppliedTo,Reseller)
			VALUES (@v_TerminationType, @v_CompanyId_, p_rateTableName, p_RateGeneratorId, @v_trunk_, @v_codedeckid_,@v_CurrencyID_,@v_RoundChargedAmount,@v_AppliedTo,@v_Reseller);


			SET p_RateTableId = LAST_INSERT_ID();


			IF (@v_RateApprovalProcess_ = 1 ) THEN

							INSERT INTO tblRateTableRateAA (OriginationRateID,RateID,RateTableId,TimezonesID,Rate,RateN,EffectiveDate,PreviousRate,Interval1,IntervalN,ConnectionFee,ApprovedStatus,VendorID,RateCurrency,ConnectionFeeCurrency)
								SELECT DISTINCT
									IFNULL(r.RateID,0) as OriginationRateID,
									tblRate.RateId,p_RateTableId,v_TimezonesID,rate.Rate,rate.RateN,p_EffectiveDate,rate.Rate,tblRate.Interval1,tblRate.IntervalN,
									rate.ConnectionFee,@v_RATE_STATUS_AWAITING as ApprovedStatus,rate.AccountID,rate.RateCurrency,rate.ConnectionFeeCurrency

								FROM tmp_Rates_ rate
									INNER JOIN tblRate
										ON rate.code  = tblRate.Code
									LEFT JOIN tblRate r
										ON rate.OriginationCode  = r.Code AND  r.CodeDeckId = tblRate.CodeDeckId

								WHERE tblRate.CodeDeckId = @v_codedeckid_;


			ELSE


				INSERT INTO tblRateTableRate (OriginationRateID,RateID,RateTableId,TimezonesID,Rate,RateN,EffectiveDate,PreviousRate,Interval1,IntervalN,ConnectionFee,ApprovedStatus,VendorID,RateCurrency,ConnectionFeeCurrency)
					SELECT DISTINCT
						IFNULL(r.RateID,0) as OriginationRateID,
						tblRate.RateId,						p_RateTableId,						v_TimezonesID,						rate.Rate,						rate.RateN,
						p_EffectiveDate,						rate.Rate,						tblRate.Interval1,						tblRate.IntervalN,						rate.ConnectionFee,
						@v_RATE_STATUS_APPROVED as ApprovedStatus,						rate.AccountID,						rate.RateCurrency,						rate.ConnectionFeeCurrency

					FROM tmp_Rates_ rate
						INNER JOIN tblRate
							ON rate.code  = tblRate.Code
						LEFT JOIN tblRate r
							ON rate.OriginationCode  = r.Code AND  r.CodeDeckId = tblRate.CodeDeckId

					WHERE tblRate.CodeDeckId = @v_codedeckid_;


			END IF;

		ELSE

			IF p_delete_exiting_rate = 1
			THEN

				IF (@v_RateApprovalProcess_ = 1 ) THEN

							insert into  tblRateTableRateAA (OriginationRateID,RateID,RateTableId,TimezonesID,Rate,RateN,EffectiveDate,EndDate,created_at,updated_at,CreatedBy,ModifiedBy,PreviousRate,
							Interval1,IntervalN,ConnectionFee,RoutingCategoryID,Preference,Blocked,ApprovedStatus,ApprovedBy,ApprovedDate,RateCurrency,ConnectionFeeCurrency,VendorID)

							SELECT
									OriginationRateID,RateID,RateTableId,TimezonesID,Rate,RateN,EffectiveDate,EndDate,created_at,updated_at,CreatedBy,ModifiedBy,PreviousRate,
									Interval1,IntervalN,ConnectionFee,RoutingCategoryID,Preference,Blocked,@v_RATE_STATUS_DELETE as ApprovedStatus,ApprovedBy,ApprovedDate,RateCurrency,ConnectionFeeCurrency,VendorID

							FROM tblRateTableRate

							WHERE RateTableId = p_RateTableId AND TimezonesID = v_TimezonesID;

				ELSE

					UPDATE
						tblRateTableRate
					SET
						EndDate = NOW()
					WHERE
						tblRateTableRate.RateTableId = p_RateTableId AND tblRateTableRate.TimezonesID = v_TimezonesID;

				END IF;


			END IF;



         IF (@v_RateApprovalProcess_ = 1 ) THEN

				CALL prc_ArchiveOldRateTableRateAA(p_RateTableId,v_TimezonesID,CONCAT(p_ModifiedBy,'|RateGenerator'));

			ELSE

				CALL prc_ArchiveOldRateTableRate(p_RateTableId,v_TimezonesID,CONCAT(p_ModifiedBy,'|RateGenerator'));

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
													OriginationRateID,RateID,RateTableId,TimezonesID,Rate,RateN,EffectiveDate,EndDate,created_at,updated_at,CreatedBy,ModifiedBy,PreviousRate,Interval1,IntervalN,
													ConnectionFee,RoutingCategoryID,Preference,Blocked,ApprovedStatus,ApprovedBy,ApprovedDate,RateCurrency,ConnectionFeeCurrency,VendorID
												)
							SELECT

											IFNULL(rtr.OriginationRateID,0) as OriginationRateID,rtr.RateID,rtr.RateTableId,rtr.TimezonesID,rtr.Rate,rtr.RateN,rtr.EffectiveDate,NOW() as EndDate,rtr.created_at,rtr.updated_at,rtr.CreatedBy,rtr.ModifiedBy,rtr.PreviousRate,rtr.Interval1,rtr.IntervalN,
											rtr.ConnectionFee,rtr.RoutingCategoryID,rtr.Preference,rtr.Blocked,@v_RATE_STATUS_DELETE as ApprovedStatus,rtr.ApprovedBy,rtr.ApprovedDate,rtr.RateCurrency,rtr.ConnectionFeeCurrency,rtr.VendorID

							FROM tblRateTableRate	rtr

							INNER JOIN
								tblRate ON tblRate.RateId = rtr.RateId
									AND rtr.RateTableId = p_RateTableId

							INNER JOIN
								tmp_Rates_ as rate ON rtr.EffectiveDate = p_EffectiveDate

							WHERE
								(
									(p_GroupBy != 'Desc'  AND rate.code = tblRate.Code )

									OR
									(p_GroupBy = 'Desc' AND rate.description = tblRate.description )
								)
								AND
								rtr.TimezonesID = v_TimezonesID AND
								rtr.RateTableId = p_RateTableId AND
								tblRate.CodeDeckId = @v_codedeckid_ AND
								rate.rate != rtr.Rate;

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
					tblRate.CodeDeckId = @v_codedeckid_ AND
					rate.rate != tblRateTableRate.Rate;




			END IF;


			IF (@v_RateApprovalProcess_ = 1 ) THEN

				CALL prc_ArchiveOldRateTableRateAA(p_RateTableId,v_TimezonesID,CONCAT(p_ModifiedBy,'|RateGenerator'));

			ELSE

			CALL prc_ArchiveOldRateTableRate(p_RateTableId,v_TimezonesID,CONCAT(p_ModifiedBy,'|RateGenerator'));

			END IF;


			IF (@v_RateApprovalProcess_ = 1 ) THEN



					INSERT INTO tblRateTableRateAA (OriginationRateID, RateID,RateTableId,TimezonesID,Rate,RateN,EffectiveDate,
									PreviousRate,Interval1,IntervalN,ConnectionFee,ApprovedStatus,VendorID,RateCurrency,ConnectionFeeCurrency)
						SELECT DISTINCT

							IFNULL(r.RateID,0) as OriginationRateID,tblRate.RateId,p_RateTableId AS RateTableId,v_TimezonesID AS TimezonesID,rate.Rate,rate.RateN,rate.EffectiveDate,
							rate.PreviousRate,tblRate.Interval1,tblRate.IntervalN,rate.ConnectionFee,@v_RATE_STATUS_AWAITING as ApprovedStatus,rate.AccountID,rate.RateCurrency,rate.ConnectionFeeCurrency

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
									 AND tblRate.CodeDeckId = @v_codedeckid_;


					insert into  tblRateTableRateAA ( OriginationRateID,RateID,RateTableId,TimezonesID,Rate,RateN,EffectiveDate,EndDate,created_at,updated_at,CreatedBy,ModifiedBy,PreviousRate,Interval1,IntervalN,ConnectionFee,RoutingCategoryID,Preference,Blocked,ApprovedStatus,ApprovedBy,ApprovedDate,RateCurrency,ConnectionFeeCurrency,VendorID )
					SELECT
							IFNULL(rtr.OriginationRateID,0) as OriginationRateID,rtr.RateID,rtr.RateTableId,rtr.TimezonesID,rtr.Rate,rtr.RateN,rtr.EffectiveDate,NOW() as EndDate,rtr.created_at,rtr.updated_at,rtr.CreatedBy,rtr.ModifiedBy,rtr.PreviousRate,rtr.Interval1,rtr.IntervalN,rtr.ConnectionFee,rtr.RoutingCategoryID,rtr.Preference,rtr.Blocked,@v_RATE_STATUS_DELETE as ApprovedStatus,rtr.ApprovedBy,rtr.ApprovedDate,rtr.RateCurrency,rtr.ConnectionFeeCurrency,rtr.VendorID
					FROM
						tblRateTableRate rtr
					INNER JOIN
						tblRate ON rtr.RateId  = tblRate.RateId
					LEFT JOIN
						tmp_Rates_ rate ON rate.Code=tblRate.Code



					WHERE
						rate.Code is null AND rtr.RateTableId = p_RateTableId AND rtr.TimezonesID = v_TimezonesID AND rtr.EffectiveDate = rate.EffectiveDate AND tblRate.CodeDeckId = @v_codedeckid_;


			ELSE


					INSERT INTO tblRateTableRate ( OriginationRateID, RateID,RateTableId,TimezonesID,Rate,RateN,EffectiveDate,PreviousRate,
															Interval1,IntervalN,ConnectionFee,ApprovedStatus,VendorID,RateCurrency,ConnectionFeeCurrency )
						SELECT DISTINCT

									IFNULL(r.RateID,0) as OriginationRateID,tblRate.RateId,p_RateTableId AS RateTableId,v_TimezonesID AS TimezonesID,rate.Rate,rate.RateN,rate.EffectiveDate,rate.PreviousRate,
									tblRate.Interval1,tblRate.IntervalN,rate.ConnectionFee,@v_RATE_STATUS_APPROVED as ApprovedStatus,rate.AccountID,rate.RateCurrency,rate.ConnectionFeeCurrency

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
									 AND tblRate.CodeDeckId = @v_codedeckid_;


					UPDATE
						tblRateTableRate rtr
					INNER JOIN
						tblRate ON rtr.RateId  = tblRate.RateId
					LEFT JOIN
						tmp_Rates_ rate ON rate.Code=tblRate.Code
					SET
						rtr.EndDate = NOW()
					WHERE
						rate.Code is null AND rtr.RateTableId = p_RateTableId AND rtr.TimezonesID = v_TimezonesID AND rtr.EffectiveDate = rate.EffectiveDate AND tblRate.CodeDeckId = @v_codedeckid_;


			END IF;


		END IF;

		IF (@v_RateApprovalProcess_ = 1 ) THEN

				CALL prc_ArchiveOldRateTableRateAA(p_RateTableId,v_TimezonesID,CONCAT(p_ModifiedBy,'|RateGenerator'));

		ELSE

			CALL prc_ArchiveOldRateTableRate(p_RateTableId,v_TimezonesID,CONCAT(p_ModifiedBy,'|RateGenerator'));

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
					tmp_ALL_RateTableRate_ temp ON rtr.RateTableID=temp.RateTableID AND rtr.TimezonesID=temp.TimezonesID AND rtr.RateID = temp.RateID AND rtr.EffectiveDate = temp.EffectiveDate
				SET
					rtr.EndDate=temp.EndDate,
					rtr.ApprovedStatus = @v_RATE_STATUS_AWAITING
				WHERE
					rtr.RateTableId=p_RateTableId AND
					rtr.TimezonesID=v_TimezonesID;



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
					rtr.ApprovedStatus = @v_RATE_STATUS_APPROVED
				WHERE
					rtr.RateTableId=p_RateTableId AND
					rtr.TimezonesID=v_TimezonesID;


		END IF;


		UPDATE tblRateTable
		SET RateGeneratorID = p_RateGeneratorId,
			TrunkID = @v_trunk_,
			CodeDeckId = @v_codedeckid_,
			updated_at = now()
		WHERE RateTableID = p_RateTableId;


		IF (@v_RateApprovalProcess_ = 1 ) THEN

				CALL prc_ArchiveOldRateTableRateAA(p_RateTableId,v_TimezonesID,CONCAT(p_ModifiedBy,'|RateGenerator'));

		ELSE

			CALL prc_ArchiveOldRateTableRate(p_RateTableId,v_TimezonesID,CONCAT(p_ModifiedBy,'|RateGenerator'));

		END IF;


		IF(p_RateTableId > 0 ) THEN

			INSERT INTO tmp_JobLog_ (Message) VALUES (p_RateTableId);

		ELSE

			INSERT INTO tmp_JobLog_ (Message) VALUES ('No data found');

		END IF;


		SELECT * FROM tmp_JobLog_;

		COMMIT;


		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;



	END//
DELIMITER ;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
