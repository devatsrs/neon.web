Use Ratemanagement3;

INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1306, 'RateCompare.All', 1, 5);
INSERT INTO `tblResource` ( `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ( 'RateCompare.*', 'RateCompareController.*', 1, 'Sumera Saeed', NULL, '2017-08-31 17:16:34.000', '2017-08-31 17:16:34.000', 1306);

-- Rate Generator
ALTER TABLE `tblRateRule` ADD COLUMN `Description` VARCHAR(200) NULL AFTER `Code`;
ALTER TABLE `tblRateRule` ALTER `Code` DROP DEFAULT;
ALTER TABLE `tblRateRule` CHANGE COLUMN `Code` `Code` VARCHAR(100) NULL COLLATE 'utf8_unicode_ci' AFTER `RateGeneratorId`;

DELIMITER  //
CREATE PROCEDURE `prc_RateCompare`(
	IN `p_companyid` INT,
	IN `p_trunkID` INT,
	IN `p_codedeckID` INT,
	IN `p_currencyID` INT,
	IN `p_code` VARCHAR(50),
	IN `p_description` VARCHAR(50),
	IN `p_groupby` VARCHAR(50),
	IN `p_source_vendors` VARCHAR(100),
	IN `p_source_customers` VARCHAR(100),
	IN `p_source_rate_tables` VARCHAR(100),
	IN `p_destination_vendors` VARCHAR(100),
	IN `p_destination_customers` VARCHAR(100),
	IN `p_destination_rate_tables` VARCHAR(100),
	IN `p_Effective` VARCHAR(50),
	IN `p_SelectedEffectiveDate` DATE,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_SortOrder` VARCHAR(50),
	IN `p_isExport` INT
)
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
	SQL SECURITY DEFINER
	COMMENT ''
	BEGIN

		DECLARE v_OffSet_ int;
		DECLARE v_CompanyCurrencyID_ INT;

		DECLARE v_pointer_ INT;
		DECLARE v_rowCount_ INT;

		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		SET @@session.collation_connection='utf8_unicode_ci';
		SET @@session.character_set_results='utf8';

		SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

		SET SESSION  sql_mode = '';


		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
		CREATE TEMPORARY TABLE tmp_VendorRate_ (
			AccountId INT ,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) ,
			RateID INT,
			Description VARCHAR(200) ,
			Rate DECIMAL(18,6),
			EffectiveDate DATE ,
			TrunkID INT ,
			VendorRateID INT
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRate_;
		CREATE TEMPORARY TABLE tmp_CustomerRate_ (
			AccountId INT ,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) ,
			RateID INT,
			Description VARCHAR(200) ,
			Rate DECIMAL(18,6) ,
			EffectiveDate DATE ,
			TrunkID INT,
			CustomerRateId INT
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
		CREATE TEMPORARY TABLE tmp_RateTableRate_ (
			RateTableName VARCHAR(200) ,
			RateID INT,
			Code VARCHAR(50) ,
			Description VARCHAR(200) ,
			Rate DECIMAL(18,6) ,
			EffectiveDate DATE ,
			RateTableID INT,
			RateTableRateID INT
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_code_;
		CREATE TEMPORARY TABLE tmp_code_ (
			Code  varchar(50),
			Description  varchar(250),
			RateID int,
			INDEX Index1 (Code)
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_final_compare;
		CREATE TEMPORARY TABLE tmp_final_compare (
			Code  varchar(50),
			Description VARCHAR(200) ,
			-- 		RateID int,
			INDEX Index1 (Code)
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_vendors_;
		CREATE TEMPORARY TABLE tmp_vendors_ (
			AccountID  int,
			AccountName varchar(100),
			CurrencyID int,
			RowID int
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_vendors_;
		CREATE TEMPORARY TABLE tmp_vendors_ (
			AccountID  int,
			AccountName varchar(100),
			CurrencyID int,
			RowID int
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_customers_;
		CREATE TEMPORARY TABLE tmp_customers_ (
			AccountID  int,
			AccountName varchar(100),
			CurrencyID int,
			RowID int
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_rate_tables_;
		CREATE TEMPORARY TABLE tmp_rate_tables_ (
			RateTableID  int,
			RateTableName varchar(100),
			CurrencyID int,
			RowID int
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_dynamic_columns_;
		CREATE TEMPORARY TABLE tmp_dynamic_columns_ (
			ColumnName  varchar(200),
			ColumnType  varchar(50),
			ColumnID  INT
		);

		SELECT CurrencyId INTO v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = p_companyid;

		#vendors
		INSERT INTO tmp_vendors_
			SELECT a.AccountID,a.AccountName,a.CurrencyID,
				@row_num := @row_num+1 AS RowID
			FROM tblAccount a
				Inner join tblVendorTrunk vt on vt.CompanyID = a.CompanyId AND vt.AccountID = a.AccountID and vt.Status =  a.Status and vt.TrunkID =  p_trunkID
				,(SELECT @row_num := 0) x
			WHERE  (FIND_IN_SET(a.AccountID,p_source_vendors)!= 0 OR  FIND_IN_SET(a.AccountID,p_destination_vendors)!= 0)
						 AND a.CompanyId = p_companyid and a.Status = 1 and a.IsVendor = 1 AND a.CurrencyId is not NULL;

		#customer
		INSERT INTO tmp_customers_
			SELECT a.AccountID,a.AccountName,a.CurrencyID,
				@row_num := @row_num+1 AS RowID
			FROM tblAccount a
				Inner join tblCustomerTrunk vt on vt.CompanyID = a.CompanyId AND vt.AccountID = a.AccountID and vt.Status =  a.Status and vt.TrunkID =  p_trunkID
				,(SELECT @row_num := 0) x
			WHERE  (FIND_IN_SET(a.AccountID,p_source_customers)!= 0 OR  FIND_IN_SET(a.AccountID,p_destination_customers)!= 0)
						 AND a.CompanyId = p_companyid and a.Status = 1 and a.IsCustomer = 1 AND a.CurrencyId is not NULL;


		#rate tables
		INSERT INTO tmp_rate_tables_
			SELECT RateTableID,RateTableName,CurrencyID,
				@row_num := @row_num+1 AS RowID
			FROM tblRateTable,(SELECT @row_num := 0) x
			WHERE  (FIND_IN_SET(RateTableID,p_source_rate_tables)!= 0 OR  FIND_IN_SET(RateTableID,p_destination_rate_tables)!= 0)
						 AND CompanyID = p_companyid and TrunkID = p_trunkID /*and CodeDeckId = p_codedeckID*/ AND CurrencyId is not NULL;



		insert into tmp_code_
			select Code,Description,RateID
			from tblRate
			WHERE CompanyID = p_companyid AND CodedeckID = p_codedeckID
						AND ( CHAR_LENGTH(RTRIM(p_code)) = '' OR tblRate.Code LIKE REPLACE(p_code,'*', '%') )
						AND ( CHAR_LENGTH(RTRIM(p_description)) = '' OR tblRate.Description LIKE REPLACE(p_description,'*', '%') )
			order by `Code`;
		-- LIMIT p_RowspPage OFFSET v_OffSet_ ;



		IF p_source_vendors != '' OR p_destination_vendors != '' THEN

			INSERT INTO tmp_VendorRate_ ( AccountId ,AccountName ,		Code ,		RateID , 	Description , Rate , EffectiveDate , TrunkID , VendorRateID )
				SELECT distinct
					tblVendorRate.AccountId,
					tblAccount.AccountName,
					tblRate.Code,
					tblRate.RateID,
					tblRate.Description,
					CASE WHEN  tblAccount.CurrencyId = p_CurrencyID
						THEN tblVendorRate.Rate
					WHEN  v_CompanyCurrencyID_ = p_CurrencyID
						THEN ( tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = p_companyid ) )
					ELSE (
						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = p_CurrencyID and  CompanyID = p_companyid )
						* ( tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = p_companyid ) )
					)
					END as  Rate,
					tblVendorRate.EffectiveDate,
					tblVendorRate.TrunkID,
					tblVendorRate.VendorRateID
				FROM tblVendorRate
					INNER JOIN tmp_vendors_ as tblAccount   ON tblVendorRate.AccountId = tblAccount.AccountID
					INNER JOIN tblRate ON tblVendorRate.RateId = tblRate.RateID
					INNER JOIN tmp_code_ tc ON tc.Code = tblRate.Code
					LEFT JOIN tblVendorPreference vp
						ON vp.AccountId = tblVendorRate.AccountId
							 AND vp.TrunkID = tblVendorRate.TrunkID
							 AND vp.RateId = tblVendorRate.RateId
					LEFT OUTER JOIN tblVendorBlocking AS blockCode   ON tblVendorRate.RateId = blockCode.RateId
																															AND tblVendorRate.AccountId = blockCode.AccountId
																															AND tblVendorRate.TrunkID = blockCode.TrunkID
					LEFT OUTER JOIN tblVendorBlocking AS blockCountry    ON tblRate.CountryID = blockCountry.CountryId
																																	AND tblVendorRate.AccountId = blockCountry.AccountId
																																	AND tblVendorRate.TrunkID = blockCountry.TrunkID
				WHERE
					tblVendorRate.TrunkID = p_trunkID
					AND blockCode.RateId IS NULL
					AND blockCountry.CountryId IS NULL
					AND
					(
						( p_Effective = 'Now' AND tblVendorRate.EffectiveDate <= NOW() )
						OR
						( p_Effective = 'Future' AND tblVendorRate.EffectiveDate > NOW())
						OR (

							p_Effective = 'Selected' AND tblVendorRate.EffectiveDate <= DATE(p_SelectedEffectiveDate)
						)
					)

				ORDER BY tblRate.Code asc;


		END IF;

		IF p_source_customers != '' OR p_destination_customers != '' THEN

			INSERT INTO tmp_CustomerRate_ ( AccountId ,AccountName ,		Code ,		RateID , 	Description , Rate , EffectiveDate , TrunkID , CustomerRateID )
				SELECT distinct
					tblCustomerRate.CustomerID,
					tblAccount.AccountName,
					tblRate.Code,
					tblCustomerRate.RateID,
					tblRate.Description,
					CASE WHEN  tblAccount.CurrencyId = p_CurrencyID
						THEN tblCustomerRate.Rate
					WHEN  v_CompanyCurrencyID_ = p_CurrencyID
						THEN ( tblCustomerRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = p_companyid ) )
					ELSE (
						( Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = p_CurrencyID and  CompanyID = p_companyid )
						* ( tblCustomerRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = p_companyid ) )
					)
					END as  Rate,
					tblCustomerRate.EffectiveDate,
					tblCustomerRate.TrunkID,
					tblCustomerRate.CustomerRateId
				FROM tblCustomerRate
					INNER JOIN tmp_customers_ as tblAccount   ON tblCustomerRate.CustomerID = tblAccount.AccountID
					INNER JOIN tblRate ON tblCustomerRate.RateId = tblRate.RateID
					INNER JOIN tmp_code_ tc ON tc.Code = tblRate.Code
				WHERE
					tblCustomerRate.TrunkID = p_trunkID
					AND
					(
						( p_Effective = 'Now' AND tblCustomerRate.EffectiveDate <= NOW() )
						OR
						( p_Effective = 'Future' AND tblCustomerRate.EffectiveDate > NOW())
						OR (

							p_Effective = 'Selected' AND tblCustomerRate.EffectiveDate <= DATE(p_SelectedEffectiveDate)
						)
					)
				ORDER BY tblRate.Code asc;

		-- @TODO : skipp tmp_CustomerRate_ from rate table.
		-- dont show rate table rate in customer rate
		/*
    INSERT INTO tmp_CustomerRate_ ( AccountId ,AccountName ,		Code ,		RateID , 	Description , Rate , EffectiveDate , TrunkID , CustomerRateID )
              SELECT
              tblAccount.AccountID,
              tblAccount.AccountName,
              tblRate.Code,
              tblRateTableRate.RateID,
              tblRate.Description,
              CASE WHEN  tblAccount.CurrencyId = p_CurrencyID
                THEN tblRateTableRate.Rate
              WHEN  v_CompanyCurrencyID_ = p_CurrencyID
                THEN ( tblRateTableRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = p_companyid ) )
              ELSE (
                ( Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = p_CurrencyID and  CompanyID = p_companyid )
                * ( tblRateTableRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = p_companyid ) )
              )
              END as  Rate,
              tblRateTableRate.EffectiveDate,
              p_trunkID as TrunkID,
              NULL as CustomerRateId
            FROM tblRateTableRate
              INNER JOIN tblCustomerTrunk    ON  tblCustomerTrunk.CompanyID = p_companyid And  tblCustomerTrunk.Status= 1 And tblCustomerTrunk.TrunkID= p_trunkID  AND tblCustomerTrunk.RateTableID = tblRateTableRate.RateTableID
              INNER JOIN tmp_customers_ as tblAccount   ON tblCustomerTrunk.AccountId = tblAccount.AccountID
              INNER JOIN tblRate ON tblRateTableRate.RateId = tblRate.RateID
              INNER JOIN tmp_code_ tc ON tc.Code = tblRate.Code
            WHERE
              (
                ( p_Effective = 'Now' AND tblRateTableRate.EffectiveDate <= NOW() )
                OR
                ( p_Effective = 'Future' AND tblRateTableRate.EffectiveDate > NOW())
                OR (
                  p_Effective = 'Selected' AND tblRateTableRate.EffectiveDate <= DATE(p_SelectedEffectiveDate)
                )
              )
              ORDER BY tblRate.Code asc;
        */

		END IF;


		IF p_source_rate_tables != '' OR p_destination_rate_tables != '' THEN

			INSERT INTO tmp_RateTableRate_
				SELECT
					tblRateTable.RateTableName,
					tblRateTableRate.RateID,
					tblRate.Code,
					tblRate.Description,
					CASE WHEN  tblRateTable.CurrencyID = p_CurrencyID
						THEN tblRateTableRate.Rate
					WHEN  v_CompanyCurrencyID_ = p_CurrencyID
						THEN ( tblRateTableRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblRateTable.CurrencyID and  CompanyID = p_companyid ) )
					ELSE (
						( Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = p_CurrencyID and  CompanyID = p_companyid )
						* ( tblRateTableRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblRateTable.CurrencyID and  CompanyID = p_companyid ) )
					)
					END as  Rate,
					tblRateTableRate.EffectiveDate,
					tblRateTableRate.RateTableID,
					tblRateTableRate.RateTableRateID
				FROM tblRateTableRate
					INNER JOIN tmp_rate_tables_ as tblRateTable on tblRateTable.RateTableID =  tblRateTableRate.RateTableID
					INNER JOIN tblRate ON tblRateTableRate.RateId = tblRate.RateID
					INNER JOIN tmp_code_ tc ON tc.Code = tblRate.Code
				WHERE
					(
						( p_Effective = 'Now' AND tblRateTableRate.EffectiveDate <= NOW() )
						OR
						( p_Effective = 'Future' AND tblRateTableRate.EffectiveDate > NOW())
						OR (

							p_Effective = 'Selected' AND tblRateTableRate.EffectiveDate <= DATE(p_SelectedEffectiveDate)
						)
					)

				ORDER BY Code asc;

		-- select * from tmp_RateTableRate_;
		-- select count(*) as totalcount from tmp_RateTableRate_;

		END IF;


		#insert into tmp_final_compare
		INSERT  INTO  tmp_final_compare (Code,Description)
			SELECT 	DISTINCT 		Code,		Description
			FROM
				(
					SELECT DISTINCT
						Code,
						Description,
						RateID
					FROM tmp_VendorRate_

					UNION ALL

					SELECT DISTINCT
						Code,
						Description,
						RateID
					FROM tmp_CustomerRate_

					UNION ALL

					SELECT DISTINCT
						Code,
						Description,
						RateID
					FROM tmp_RateTableRate_
				) tmp;

		-- #########################Source##############################################################




		#source vendor insert rates
		DROP TEMPORARY TABLE IF EXISTS tmp_vendors_source;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_vendors_source as (select AccountID,AccountName,CurrencyID, @row_num := @row_num+1 AS RowID from tmp_vendors_ ,(SELECT @row_num := 0) x where FIND_IN_SET(AccountID , p_source_vendors) > 0);
		SET v_pointer_ = 1;
		SET v_rowCount_ = (SELECT COUNT(*) FROM tmp_vendors_source);
		SET @Group_sql = '';

		IF v_rowCount_ > 0 THEN

			WHILE v_pointer_ <= v_rowCount_
			DO

				SET @AccountID = (SELECT AccountID FROM tmp_vendors_source WHERE RowID = v_pointer_);
				SET @AccountName = (SELECT AccountName FROM tmp_vendors_source WHERE RowID = v_pointer_);

				-- IF ( FIND_IN_SET(@AccountID , p_source_vendors) > 0  ) THEN

				SET @ColumnName = concat('`', @AccountName ,' (VR)`' );

				SET @stm1 = CONCAT('ALTER   TABLE `tmp_final_compare` ADD COLUMN ', @ColumnName , ' VARCHAR(100) NULL DEFAULT NULL');

				PREPARE stmt1 FROM @stm1;
				EXECUTE stmt1;
				DEALLOCATE PREPARE stmt1;

				SET @stm2 = CONCAT('UPDATE `tmp_final_compare` tmp  INNER JOIN tmp_VendorRate_ vr on vr.Code = tmp.Code and vr.Description = tmp.Description set ', @ColumnName , ' =  IFNULL(concat(vr.Rate,"<br>",vr.EffectiveDate),"") WHERE vr.AccountID = ', @AccountID , ' ;');

				PREPARE stmt2 FROM @stm2;
				EXECUTE stmt2;
				DEALLOCATE PREPARE stmt2;

				SET @stm2 = CONCAT('UPDATE `tmp_final_compare`  set ', @ColumnName , ' =  "" where  ', @ColumnName , ' is null;');

				PREPARE stmt2 FROM @stm2;
				EXECUTE stmt2;
				DEALLOCATE PREPARE stmt2;

				INSERT INTO tmp_dynamic_columns_  values ( @ColumnName , '(VR)' ,  @AccountID );


				-- END IF;


				SET v_pointer_ = v_pointer_ + 1;


			END WHILE;

		END IF;

		#source customer insert rates
		DROP TEMPORARY TABLE IF EXISTS tmp_customers_source;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_customers_source as (select AccountID,AccountName,CurrencyID, @row_num := @row_num+1 AS RowID from tmp_customers_ ,(SELECT @row_num := 0) x where FIND_IN_SET(AccountID , p_source_customers) > 0);
		SET v_pointer_ = 1;
		SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_customers_source );

		IF v_rowCount_ > 0 THEN

			WHILE v_pointer_ <= v_rowCount_
			DO

				SET @AccountID = (SELECT AccountID FROM tmp_customers_source WHERE RowID = v_pointer_);
				SET @AccountName = (SELECT AccountName FROM tmp_customers_source WHERE RowID = v_pointer_);

				-- IF ( FIND_IN_SET(@AccountID , p_source_customers) > 0  ) THEN

				SET @ColumnName = concat('`', @AccountName ,' (CR)`');

				SET @stm1 = CONCAT('ALTER   TABLE `tmp_final_compare` ADD COLUMN ', @ColumnName , ' VARCHAR(100) NULL DEFAULT NULL');


				PREPARE stmt1 FROM @stm1;
				EXECUTE stmt1;
				DEALLOCATE PREPARE stmt1;

				SET @stm2 = CONCAT('UPDATE `tmp_final_compare` tmp  INNER JOIN tmp_CustomerRate_ vr on vr.Code = tmp.Code and vr.Description = tmp.Description  set ', @ColumnName , ' =  IFNULL(concat(vr.Rate,"<br>",vr.EffectiveDate),"") WHERE vr.AccountID = ', @AccountID , ' ;');

				PREPARE stmt2 FROM @stm2;
				EXECUTE stmt2;
				DEALLOCATE PREPARE stmt2;

				SET @stm2 = CONCAT('UPDATE `tmp_final_compare`  set ', @ColumnName , ' =  "" where  ', @ColumnName , ' is null;');

				PREPARE stmt2 FROM @stm2;
				EXECUTE stmt2;
				DEALLOCATE PREPARE stmt2;


				INSERT INTO tmp_dynamic_columns_  values ( @ColumnName , '(CR)' ,  @AccountID );

				-- END IF;

				SET v_pointer_ = v_pointer_ + 1;


			END WHILE;

		END IF;



		#Rate Table insert rates
		DROP TEMPORARY TABLE IF EXISTS tmp_rate_tables_source;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_rate_tables_source as (select RateTableID,RateTableName,CurrencyID, @row_num := @row_num+1 AS RowID from tmp_rate_tables_ ,(SELECT @row_num := 0) x where FIND_IN_SET(RateTableID , p_source_rate_tables) > 0);
		SET v_pointer_ = 1;
		SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_rate_tables_source );

		IF v_rowCount_ > 0 THEN

			WHILE v_pointer_ <= v_rowCount_
			DO

				SET @RateTableID = (SELECT RateTableID FROM tmp_rate_tables_source WHERE RowID = v_pointer_);
				SET @RateTableName = (SELECT TRIM(REPLACE(REPLACE(REPLACE( RateTableName,"\\"," "),"/"," "),'-'," ")) FROM tmp_rate_tables_source WHERE RowID = v_pointer_);

				-- IF ( FIND_IN_SET(@RateTableID , p_destination_rate_tables) > 0  ) THEN

				SET @ColumnName = concat('`', @RateTableName,' (RT)`');

				SET @stm1 = CONCAT('ALTER   TABLE `tmp_final_compare` ADD COLUMN ', @ColumnName , ' VARCHAR(100) NULL DEFAULT NULL');

				PREPARE stmt1 FROM @stm1;
				EXECUTE stmt1;
				DEALLOCATE PREPARE stmt1;

				SET @stm2 = CONCAT('UPDATE `tmp_final_compare` tmp  INNER JOIN tmp_RateTableRate_ vr on vr.Code = tmp.Code and vr.Description = tmp.Description  set ', @ColumnName , ' =  IFNULL(concat(vr.Rate,"<br>",vr.EffectiveDate),"") WHERE vr.RateTableID = ', @RateTableID , ' ;');

				PREPARE stmt2 FROM @stm2;
				EXECUTE stmt2;
				DEALLOCATE PREPARE stmt2;

				SET @stm2 = CONCAT('UPDATE `tmp_final_compare`  set ', @ColumnName , ' =  "" where  ', @ColumnName , ' is null;');

				PREPARE stmt2 FROM @stm2;
				EXECUTE stmt2;
				DEALLOCATE PREPARE stmt2;

				INSERT INTO tmp_dynamic_columns_  values ( @ColumnName , '(RT)' ,  @RateTableID );

				-- END IF;

				SET v_pointer_ = v_pointer_ + 1;


			END WHILE;

		END IF;

		-- ##################Destination#######################################################

		#destination vendor insert rates
		DROP TEMPORARY TABLE IF EXISTS tmp_vendors_destination;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_vendors_destination as (select AccountID,AccountName, CurrencyID, @row_num := @row_num+1 AS RowID from tmp_vendors_ ,(SELECT @row_num := 0) x where FIND_IN_SET(AccountID , p_destination_vendors) > 0);
		SET v_pointer_ = 1;
		SET v_rowCount_ = ( SELECT COUNT(*)FROM tmp_vendors_destination );

		IF v_rowCount_ > 0 THEN

			WHILE v_pointer_ <= v_rowCount_
			DO

				SET @AccountID = (SELECT AccountID FROM tmp_vendors_destination WHERE RowID = v_pointer_);
				SET @AccountName = (SELECT AccountName FROM tmp_vendors_destination WHERE RowID = v_pointer_);

				-- IF ( FIND_IN_SET(@AccountID , p_destination_vendors) > 0  ) THEN

				SET @ColumnName = concat('`', @AccountName ,' (VR)`');

				SET @stm1 = CONCAT('ALTER   TABLE `tmp_final_compare` ADD COLUMN ', @ColumnName , ' VARCHAR(100) NULL DEFAULT NULL');

				PREPARE stmt1 FROM @stm1;
				EXECUTE stmt1;
				DEALLOCATE PREPARE stmt1;

				SET @stm2 = CONCAT('UPDATE `tmp_final_compare` tmp  INNER JOIN tmp_VendorRate_ vr on vr.Code = tmp.Code and vr.Description = tmp.Description  set ', @ColumnName , ' =  IFNULL(concat(vr.Rate,"<br>",vr.EffectiveDate),"") WHERE vr.AccountID = ', @AccountID , ' ;');

				PREPARE stmt2 FROM @stm2;
				EXECUTE stmt2;
				DEALLOCATE PREPARE stmt2;

				SET @stm2 = CONCAT('UPDATE `tmp_final_compare`  set ', @ColumnName , ' =  "" where  ', @ColumnName , ' is null;');

				PREPARE stmt2 FROM @stm2;
				EXECUTE stmt2;
				DEALLOCATE PREPARE stmt2;

				INSERT INTO tmp_dynamic_columns_  values ( @ColumnName , '(VR)' ,  @AccountID );

				-- END IF;


				SET v_pointer_ = v_pointer_ + 1;


			END WHILE;

		END IF;

		#destination customer insert rates
		DROP TEMPORARY TABLE IF EXISTS tmp_customers_destination;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_customers_destination as (select AccountID,AccountName,CurrencyID, @row_num := @row_num+1 AS RowID from tmp_customers_ ,(SELECT @row_num := 0) x where FIND_IN_SET(AccountID , p_destination_customers) > 0);
		SET v_pointer_ = 1;
		SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_customers_destination);

		IF v_rowCount_ > 0 THEN

			WHILE v_pointer_ <= v_rowCount_
			DO

				SET @AccountID = (SELECT AccountID FROM tmp_customers_destination WHERE RowID = v_pointer_);
				SET @AccountName = (SELECT AccountName FROM tmp_customers_destination WHERE RowID = v_pointer_);

				-- IF ( FIND_IN_SET(@AccountID , p_destination_customers) > 0  ) THEN

				SET @ColumnName = concat('`', @AccountName ,' (CR)`');

				SET @stm1 = CONCAT('ALTER   TABLE `tmp_final_compare` ADD COLUMN ', @ColumnName , ' VARCHAR(100) NULL DEFAULT NULL');


				PREPARE stmt1 FROM @stm1;
				EXECUTE stmt1;
				DEALLOCATE PREPARE stmt1;

				SET @stm2 = CONCAT('UPDATE `tmp_final_compare` tmp  INNER JOIN tmp_CustomerRate_ vr on vr.Code = tmp.Code and vr.Description = tmp.Description  set ', @ColumnName , ' =  IFNULL(concat(vr.Rate,"<br>",vr.EffectiveDate),"") WHERE vr.AccountID = ', @AccountID , ' ;');

				PREPARE stmt2 FROM @stm2;
				EXECUTE stmt2;
				DEALLOCATE PREPARE stmt2;

				SET @stm2 = CONCAT('UPDATE `tmp_final_compare`  set ', @ColumnName , ' =  "" where  ', @ColumnName , ' is null;');

				PREPARE stmt2 FROM @stm2;
				EXECUTE stmt2;
				DEALLOCATE PREPARE stmt2;

				INSERT INTO tmp_dynamic_columns_  values ( @ColumnName , '(CR)' ,  @AccountID );

				-- END IF;

				SET v_pointer_ = v_pointer_ + 1;


			END WHILE;

		END IF;


		#Rate Table insert rates
		DROP TEMPORARY TABLE IF EXISTS tmp_rate_tables_destination;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_rate_tables_destination as (select RateTableID,RateTableName,CurrencyID, @row_num := @row_num+1 AS RowID from tmp_rate_tables_ ,(SELECT @row_num := 0) x where FIND_IN_SET(RateTableID , p_destination_rate_tables) > 0);
		SET v_pointer_ = 1;
		SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_rate_tables_destination);

		IF v_rowCount_ > 0 THEN

			WHILE v_pointer_ <= v_rowCount_
			DO

				SET @RateTableID = (SELECT RateTableID FROM tmp_rate_tables_destination WHERE RowID = v_pointer_);
				SET @RateTableName = (SELECT TRIM(REPLACE(REPLACE(REPLACE( RateTableName,"\\"," "),"/"," "),'-'," "))  FROM tmp_rate_tables_destination WHERE RowID = v_pointer_);

				-- IF ( FIND_IN_SET(@RateTableID , p_destination_rate_tables) > 0  ) THEN

				SET @ColumnName = concat('`', @RateTableName ,' (RT)`');

				SET @stm1 = CONCAT('ALTER   TABLE `tmp_final_compare` ADD COLUMN ', @ColumnName , ' VARCHAR(100) NULL DEFAULT NULL');

				PREPARE stmt1 FROM @stm1;
				EXECUTE stmt1;
				DEALLOCATE PREPARE stmt1;

				SET @stm2 = CONCAT('UPDATE `tmp_final_compare` tmp  INNER JOIN tmp_RateTableRate_ vr on vr.Code = tmp.Code and vr.Description = tmp.Description  set ', @ColumnName , ' =  IFNULL(concat(vr.Rate,"<br>",vr.EffectiveDate),"") WHERE vr.RateTableID = ', @RateTableID , ' ;');

				PREPARE stmt2 FROM @stm2;
				EXECUTE stmt2;
				DEALLOCATE PREPARE stmt2;

				SET @stm2 = CONCAT('UPDATE `tmp_final_compare`  set ', @ColumnName , ' =  "" where  ', @ColumnName , ' is null;');

				PREPARE stmt2 FROM @stm2;
				EXECUTE stmt2;
				DEALLOCATE PREPARE stmt2;

				INSERT INTO tmp_dynamic_columns_  values ( @ColumnName , '(RT)' ,  @RateTableID );

				-- END IF;

				SET v_pointer_ = v_pointer_ + 1;


			END WHILE;

		END IF;

		-- #######################################################################################

		/*select tmp.* from tmp_final_compare tmp
			left join tblRate on CompanyID = p_companyid AND CodedeckID = p_codedeckID and tmp.Code =  tblRate.Code
		WHERE tblRate.Code  is null
		order by tmp.Code;
-- LIMIT p_RowspPage OFFSET v_OffSet_ ;

		-- select count(*) as totalcount from tblRate WHERE CompanyID = p_companyid AND CodedeckID = p_codedeckID;
*/


		IF p_groupby = 'description' THEN

			select GROUP_CONCAT( concat(' max(' , ColumnName , ') as ' , ColumnName ) ) , GROUP_CONCAT(ColumnID)  INTO @maxColumnNames , @ColumnIDS from tmp_dynamic_columns_;

		ELSE

			select GROUP_CONCAT(ColumnName) , GROUP_CONCAT(ColumnID) INTO @ColumnNames ,  @ColumnIDS from tmp_dynamic_columns_;

		END IF;



		IF p_isExport = 0 THEN

			IF p_groupby = 'description' THEN

				SET @stm2 = CONCAT('select max(Description) as Destination , ',@maxColumnNames ,'  , "',@ColumnIDS ,'" as ColumnIDS   from tmp_final_compare Group by  Description  order by Description LIMIT  ', p_RowspPage , ' OFFSET ' , v_OffSet_ , '');

				PREPARE stmt2 FROM @stm2;
				EXECUTE stmt2;
				DEALLOCATE PREPARE stmt2;

				SELECT count(*) as totalcount from  (select count(Description) FROM tmp_final_compare Group by Description)tmp;

			ELSE

				SET @stm2 = CONCAT('select concat( Code , " : " , Description ) as Destination , ', @ColumnNames,' , "', @ColumnIDS ,'" as ColumnIDS from tmp_final_compare order by Code LIMIT  ', p_RowspPage , ' OFFSET ' , v_OffSet_ , '');
				PREPARE stmt2 FROM @stm2;
				EXECUTE stmt2;
				DEALLOCATE PREPARE stmt2;

				select count(*) as totalcount from tmp_final_compare;


			END IF;


		ELSE

			IF p_groupby = 'description' THEN

				SET @stm2 = CONCAT('select max(Description) as Destination , ',@maxColumnNames ,' from tmp_final_compare Group by  Description  order by Description');

				PREPARE stmt2 FROM @stm2;
				EXECUTE stmt2;
				DEALLOCATE PREPARE stmt2;

			ELSE

				SET @stm2 = CONCAT('select distinct concat( Code , " : " , Description ) as Destination , ', @ColumnNames,' from tmp_final_compare order by Code');
				PREPARE stmt2 FROM @stm2;
				EXECUTE stmt2;
				DEALLOCATE PREPARE stmt2;


			END IF;


		END IF;


		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;



	END//
DELIMITER ;


DELIMITER  //
CREATE PROCEDURE `prc_RateCompareRateUpdate`(
	IN `p_CompanyID` INT,
	IN `p_GroupBy` VARCHAR(50),
	IN `p_Type` VARCHAR(50),
	IN `p_TypeID` INT,
	IN `p_Rate` DOUBLE,
	IN `p_Code` VARCHAR(50),
	IN `p_Description` VARCHAR(200),
	IN `p_NewDescription` VARCHAR(200),
	IN `p_EffectiveDate` VARCHAR(50),
	IN `p_TrunkID` INT,
	IN `p_Effective` VARCHAR(50),
	IN `p_SelectedEffectiveDate` DATE
)
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
  SQL SECURITY DEFINER
  COMMENT ''
	BEGIN

		DECLARE v_RateUpdate_ VARCHAR(200);
		-- DECLARE v_DesciptionUpdate_ INT;

		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;



		IF ( p_Type = 'vendor_rate') THEN

			IF ( p_GroupBy = 'description' ) THEN

				Update
						tblVendorRate v
						inner join tblRate r on r.RateID = v.RateId
				SET Rate = p_Rate
				where r.CompanyID = p_CompanyID AND
							r.Description = p_Description AND
							v.AccountId = p_TypeID AND
							v.TrunkID = p_TrunkID
							AND
							(
								( p_Effective = 'Now' AND v.EffectiveDate <= NOW() )
								OR
								( p_Effective = 'Future' AND v.EffectiveDate > NOW())
								OR
								( p_Effective = 'Selected' AND v.EffectiveDate <= DATE(p_SelectedEffectiveDate) )
							);

				SELECT concat ( ROW_COUNT() , ' Records updated' ) INTO v_RateUpdate_;

				IF ( p_Description != p_NewDescription ) THEN

					UPDATE tblRate
					SET 	Description = p_NewDescription
					WHERE  CompanyID = p_CompanyID AND
								 CodeDeckId = ( SELECT CodeDeckId from tblVendorTrunk WHERE CompanyID = p_CompanyID AND AccountID = p_TypeID AND TrunkID = p_TrunkID ) AND
								 Description = p_Description;

				END IF;


			ELSE

				Update
						tblVendorRate v
						inner join tblRate r on r.RateID = v.RateId
				SET Rate = p_Rate
				where r.CompanyID = p_CompanyID AND
							r.Code = p_Code AND
							r.Description = p_Description AND
							v.AccountId = p_TypeID AND
							v.TrunkID = p_TrunkID AND
							v.EffectiveDate = p_EffectiveDate;

				SELECT concat ( ROW_COUNT() , ' Records updated' ) INTO v_RateUpdate_;

				IF ( p_Description != p_NewDescription ) THEN

					UPDATE tblRate
					SET 	Description = p_NewDescription
					WHERE  CompanyID = p_CompanyID AND
								 CodeDeckId = ( SELECT CodeDeckId from tblVendorTrunk WHERE CompanyID = p_CompanyID AND AccountID = p_TypeID AND TrunkID = p_TrunkID ) AND
								 -- Description = p_Description AND
								 `Code` 			= p_Code ;

				END IF;


			END IF;


		END IF;


		IF ( p_Type = 'rate_table') THEN

			IF ( p_GroupBy = 'description') THEN

				update
						tblRateTableRate rtr
						inner join tblRate r on r.RateID = rtr.RateId
				SET Rate = p_Rate
				where r.CompanyID = p_CompanyID AND
							r.Description = p_Description AND
							rtr.RateTableId = p_TypeID
							AND
							(
								( p_Effective = 'Now' AND rtr.EffectiveDate <= NOW() )
								OR
								( p_Effective = 'Future' AND rtr.EffectiveDate > NOW())
								OR
								( p_Effective = 'Selected' AND rtr.EffectiveDate <= DATE(p_SelectedEffectiveDate) )
							);

					SELECT concat ( ROW_COUNT() , ' Records updated' ) INTO v_RateUpdate_;

					IF ( p_Description != p_NewDescription ) THEN

							UPDATE tblRate
							SET 	Description = p_NewDescription
							WHERE  CompanyID = p_CompanyID AND
										 CodeDeckId = ( SELECT CodeDeckId from tblRateTable WHERE  RateTableId = p_TypeID ) AND
										 Description = p_Description;

					END IF;



				ELSE

				update
						tblRateTableRate rtr
						inner join tblRate r on r.RateID = rtr.RateId
				SET Rate = p_Rate
				where r.CompanyID = p_CompanyID AND
							r.Code = p_Code AND
							r.Description = p_Description AND
							rtr.RateTableId = p_TypeID AND
							rtr.EffectiveDate = p_EffectiveDate;

				SELECT concat ( ROW_COUNT() , ' Records updated' ) INTO v_RateUpdate_;

					IF ( p_Description != p_NewDescription ) THEN

						UPDATE tblRate
						SET 	Description = p_NewDescription
						WHERE  CompanyID = p_CompanyID AND
									 CodeDeckId = ( SELECT CodeDeckId from tblRateTable WHERE  RateTableId = p_TypeID ) AND
									 -- Description = p_Description AND
									 `Code` 			= p_Code ;

					END IF;


			END IF;


		END IF;

		IF ( p_Type = 'customer_rate') THEN

			IF ( p_GroupBy = 'description') THEN

				update
						tblCustomerRate c
						inner join tblRate r on r.RateID = c.RateId
				SET Rate = p_Rate
				where r.CompanyID = p_CompanyID AND
							r.Description = p_Description AND
							c.CustomerID = p_TypeID AND
							c.TrunkID = p_TrunkID
							AND
							(
								( p_Effective = 'Now' AND c.EffectiveDate <= NOW() )
								OR
								( p_Effective = 'Future' AND c.EffectiveDate > NOW())
								OR (
									p_Effective = 'Selected' AND c.EffectiveDate <= DATE(p_SelectedEffectiveDate)
								)
							);

				SELECT concat ( ROW_COUNT() , ' Records updated' ) INTO v_RateUpdate_;

				IF ( p_Description != p_NewDescription ) THEN

					UPDATE tblRate
					SET 	Description = p_NewDescription
					WHERE  CompanyID = p_CompanyID AND
								 CodeDeckId = ( SELECT CodeDeckId from tblCustomerTrunk WHERE CompanyID = p_CompanyID AND AccountID = p_TypeID AND TrunkID = p_TrunkID ) AND
								 Description = p_Description;

				END IF;


			ELSE

				update
						tblCustomerRate c
						inner join tblRate r on r.RateID = c.RateId
				SET Rate = p_Rate
				where r.CompanyID = p_CompanyID AND
							r.Code = p_Code AND
							r.Description = p_Description AND
							c.CustomerID = p_TypeID AND
							c.TrunkID = p_TrunkID AND
							c.EffectiveDate = p_EffectiveDate;

				SELECT concat ( ROW_COUNT() , ' Records updated' ) INTO v_RateUpdate_;

				IF ( p_Description != p_NewDescription ) THEN

					UPDATE tblRate
					SET 	Description = p_NewDescription
					WHERE  CompanyID = p_CompanyID AND
								 CodeDeckId = ( SELECT CodeDeckId from tblCustomerTrunk WHERE CompanyID = p_CompanyID AND AccountID = p_TypeID AND TrunkID = p_TrunkID ) AND
								 -- Description = p_Description AND
								 `Code` 			= p_Code ;

				END IF;




			END IF;

		END IF;


		select v_RateUpdate_ as rows_update ;

		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;


	END//
DELIMITER ;


DELIMITER  //
CREATE PROCEDURE `prc_RateCompareRateAdd`(

	IN `p_CompanyID` INT,
	IN `p_GroupBy` VARCHAR(50),
	IN `p_Type` VARCHAR(50),
	IN `p_TypeID` INT,
	IN `p_Rate` DOUBLE,
	IN `p_Code` VARCHAR(50),
	IN `p_Description` VARCHAR(200),
	IN `p_EffectiveDate` VARCHAR(50),
	IN `Interval1` INT,
	IN `IntervalN` INT,
	IN `p_ConnectionFee` DOUBLE,
	IN `p_TrunkID` INT,
	IN `p_Effective` VARCHAR(50),
	IN `p_SelectedEffectiveDate` DATE

 )
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
  SQL SECURITY DEFINER
  COMMENT ''
  BEGIN

    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


		-- Vendor Rate
		IF ( p_Type = 'vendor_rate') THEN

			IF ( p_GroupBy = 'description') THEN


					INSERT INTO tblVendorRate (AccountID,RateID,Rate,EffectiveDate,Interval1,IntervalN,ConnectionFee)
					SELECT p_TypeID,r.RateID,p_Rate,p_EffectiveDate,p_Interval1,p_IntervalN,p_ConnectionFee
					FROM tblVendorRate v
					JOIN tblVendorTrunk vt
						ON vt.AccountID = p_TypeID
							 AND  vt.TrunkID = p_TrunkID
							 AND vt.Status = 1
					LEFT JOIN tblRate r on r.RateID = v.RateId AND r.CodedeckID = vt.CodedeckID
 					where r.CompanyID = p_CompanyID AND
							r.Description = v_Description AND
							v.AccountId = p_TypeID AND
							v.TrunkID = p_TrunkID
							AND
							(
								( p_Effective = 'Now' AND v.EffectiveDate <= NOW() )
								OR
								( p_Effective = 'Future' AND v.EffectiveDate > NOW())
								OR
								( p_Effective = 'Selected' AND v.EffectiveDate <= DATE(p_SelectedEffectiveDate) )
							);



				ELSE

						select '' ;

	      END IF;


	    END IF;

	    -- Rate Table
	    IF ( p_Type = 'rate_table') THEN

	      IF ( p_GroupBy = 'description') THEN

	        -- Rate Table group by description
	        update
	            tblRateTableRate rtr
	            inner join tblRate r on r.RateID = rtr.RateId
	        SET Rate = p_Rate
	        where r.CompanyID = p_CompanyID AND
	              r.Description = p_Description AND
	              rtr.RateTableId = p_TypeID
	              AND
	              (
	                ( p_Effective = 'Now' AND rtr.EffectiveDate <= NOW() )
	                OR
	                ( p_Effective = 'Future' AND rtr.EffectiveDate > NOW())
	                OR
	                ( p_Effective = 'Selected' AND rtr.EffectiveDate <= DATE(p_SelectedEffectiveDate) )
	              );



	      ELSE

	        -- Rate Table by code and EffectiveDate
	        update
	            tblRateTableRate rtr
	            inner join tblRate r on r.RateID = rtr.RateId
	        SET Rate = p_Rate
	        where r.CompanyID = p_CompanyID AND
	              r.Code = p_Code AND
	              r.Description = p_Description AND
	              rtr.RateTableId = p_TypeID AND
	              rtr.EffectiveDate = p_EffectiveDate;


	      END IF;


	    END IF;

	    -- Customer Rate
	    IF ( p_Type = 'customer_rate') THEN

	      IF ( p_GroupBy = 'description') THEN

	        -- Customer Rate group by description
	        update
	            tblCustomerRate c
	            inner join tblRate r on r.RateID = c.RateId
	        SET Rate = p_Rate
	        where r.CompanyID = p_CompanyID AND
	              r.Description = p_Description AND
	              c.CustomerID = p_TypeID AND
	              c.TrunkID = p_TrunkID
	              AND
	              (
	                ( p_Effective = 'Now' AND c.EffectiveDate <= NOW() )
	                OR
	                ( p_Effective = 'Future' AND c.EffectiveDate > NOW())
	                OR (
	                  p_Effective = 'Selected' AND c.EffectiveDate <= DATE(p_SelectedEffectiveDate)
	                )
	              );

	      ELSE

	        -- Customer Rate by Code and EffectiveDate
	        update
	            tblCustomerRate c
	            inner join tblRate r on r.RateID = c.RateId
	        SET Rate = p_Rate
	        where r.CompanyID = p_CompanyID AND
	              r.Code = p_Code AND
	              r.Description = p_Description AND
	              c.CustomerID = p_TypeID AND
	              c.TrunkID = p_TrunkID AND
	              c.EffectiveDate = p_EffectiveDate;

	      END IF;

	    END IF;


    select ROW_COUNT() as rows_update ;

    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;


  END//
DELIMITER ;



-- Rate geneator

DELIMITER  //
CREATE PROCEDURE `prc_WSGenerateRateTable`(
	IN `p_jobId` INT,
	IN `p_RateGeneratorId` INT,
	IN `p_RateTableId` INT,
	IN `p_rateTableName` VARCHAR(200),
	IN `p_EffectiveDate` VARCHAR(10),
	IN `p_delete_exiting_rate` INT,
	IN `p_EffectiveRate` VARCHAR(50)
)
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
	SQL SECURITY DEFINER
	COMMENT ''
		GenerateRateTable:BEGIN


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



		DECLARE v_tmp_code_cnt int ;
		DECLARE v_tmp_code_pointer int;
		DECLARE v_p_code varchar(50);
		DECLARE v_Codlen_ int;
		DECLARE v_p_code__ VARCHAR(50);
		DECLARE v_Commit int;
		DECLARE EXIT HANDLER FOR SQLEXCEPTION
		BEGIN
			ROLLBACK;
			CALL prc_WSJobStatusUpdate(p_jobId, 'F', 'RateTable generation failed', '');

		END;

		SET @@session.collation_connection='utf8_unicode_ci';
		SET @@session.character_set_client='utf8';

		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;



		SET p_EffectiveDate = CAST(p_EffectiveDate AS DATE);


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
				CALL prc_WSJobStatusUpdate  (p_jobId, 'F', 'RateTable Name is already exist, Please try using another RateTable Name', '');
				LEAVE GenerateRateTable;
			END IF;
		END IF;

		DROP TEMPORARY TABLE IF EXISTS tmp_Rates_;
		CREATE TEMPORARY TABLE tmp_Rates_  (
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			INDEX tmp_Rates_code (`code`) ,
			UNIQUE KEY `unique_code` (`code`)

		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Rates2_;
		CREATE TEMPORARY TABLE tmp_Rates2_  (
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			INDEX tmp_Rates2_code (`code`)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_Codedecks_;
		CREATE TEMPORARY TABLE tmp_Codedecks_ (
			CodeDeckId INT
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Raterules_;

		CREATE TEMPORARY TABLE tmp_Raterules_  (
			rateruleid INT,
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			description VARCHAR(200) COLLATE utf8_unicode_ci,
			RowNo INT,
			INDEX tmp_Raterules_code (`code`,`description`),
			INDEX tmp_Raterules_rateruleid (`rateruleid`),
			INDEX tmp_Raterules_RowNo (`RowNo`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Vendorrates_;
		CREATE TEMPORARY TABLE tmp_Vendorrates_  (
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			AccountId INT,
			RowNo INT,
			PreferenceRank INT,
			INDEX tmp_Vendorrates_code (`code`),
			INDEX tmp_Vendorrates_rate (`rate`)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_VRatesstage2_;
		CREATE TEMPORARY TABLE tmp_VRatesstage2_  (
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci,
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			FinalRankNumber int,
			INDEX tmp_Vendorrates_stage2__code (`RowCode`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_dupVRatesstage2_;
		CREATE TEMPORARY TABLE tmp_dupVRatesstage2_  (
			RowCode VARCHAR(50)  COLLATE utf8_unicode_ci,
			FinalRankNumber int,
			INDEX tmp_dupVendorrates_stage2__code (`RowCode`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Vendorrates_stage3_;
		CREATE TEMPORARY TABLE tmp_Vendorrates_stage3_  (
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			INDEX tmp_Vendorrates_stage2__code (`RowCode`)
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_code_;
		CREATE TEMPORARY TABLE tmp_code_  (
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
			Code VARCHAR(50) COLLATE utf8_unicode_ci,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			MaxMatchRank int ,
			prev_prev_RowCode VARCHAR(50),
			prev_AccountID int
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
		CREATE TEMPORARY TABLE tmp_VendorRate_ (
			AccountId INT ,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) COLLATE utf8_unicode_ci,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_final_VendorRate_;
		CREATE TEMPORARY TABLE tmp_final_VendorRate_ (
			AccountId INT ,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) COLLATE utf8_unicode_ci,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci,
			FinalRankNumber int,
			INDEX IX_CODE (RowCode)
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates_(
			AccountId int,
			AccountName varchar(200),
			Code varchar(50) COLLATE utf8_unicode_ci,
			Description varchar(200) ,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate date,
			TrunkID int,
			CountryID int,
			RateID int,
			Preference int,
			INDEX IX_CODE (Code)
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates1_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates1_(
			AccountId int,
			AccountName varchar(200),
			Code varchar(50),
			Description varchar(200),
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate date,
			TrunkID int,
			CountryID int,
			RateID int,
			Preference int,
			INDEX IX_Code (Code),
			INDEX tmp_VendorCurrentRates_AccountId (`AccountId`,`TrunkID`,`RateId`,`EffectiveDate`)
		);

		SELECT CurrencyID INTO v_CurrencyID_ FROM  tblRateGenerator WHERE RateGeneratorId = p_RateGeneratorId;

		SELECT
			UsePreference,
			rateposition,
			companyid ,
			CodeDeckId,
			tblRateGenerator.TrunkID,
			tblRateGenerator.UseAverage  ,
			tblRateGenerator.RateGeneratorName INTO v_Use_Preference_, v_RatePosition_, v_CompanyId_, v_codedeckid_, v_trunk_, v_Average_, v_RateGeneratorName_
		FROM tblRateGenerator
		WHERE RateGeneratorId = p_RateGeneratorId;


		SELECT CurrencyId INTO v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = v_CompanyId_;




		INSERT INTO tmp_Raterules_
			SELECT
				rateruleid,
				tblRateRule.code,
				tblRateRule.description,
				@row_num := @row_num+1 AS RowID
			FROM tblRateRule,(SELECT @row_num := 0) x
			WHERE rategeneratorid = p_RateGeneratorId
			ORDER BY tblRateRule.Code DESC;

		INSERT INTO tmp_Codedecks_
			SELECT DISTINCT
				tblVendorTrunk.CodeDeckId
			FROM tblRateRule
				INNER JOIN tblRateRuleSource
					ON tblRateRule.RateRuleId = tblRateRuleSource.RateRuleId
				INNER JOIN tblAccount
					ON tblAccount.AccountID = tblRateRuleSource.AccountId and tblAccount.IsVendor = 1
				JOIN tblVendorTrunk
					ON tblAccount.AccountId = tblVendorTrunk.AccountID
						 AND  tblVendorTrunk.TrunkID = v_trunk_
						 AND tblVendorTrunk.Status = 1
			WHERE RateGeneratorId = p_RateGeneratorId;

		SET v_pointer_ = 1;
		-- SET v_rowCount_ = (SELECT COUNT(distinct Code ) FROM tmp_Raterules_);
		SET v_rowCount_ = (SELECT COUNT(*) FROM tmp_Raterules_);




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
						 ON ( rr.code != '' AND tblRate.Code LIKE (REPLACE(rr.code,'*', '%%')) )
								OR
								( rr.description != '' AND tblRate.Description LIKE (REPLACE(rr.description,'*', '%%')) )
				 where  tblRate.CodeDeckId = v_codedeckid_
				 Order by tblRate.code
				) as f
					ON   x.RowNo   <= LENGTH(f.Code)
			order by loopCode   desc;





		SELECT CurrencyId INTO v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = v_CompanyId_;
		SET @IncludeAccountIds = (SELECT GROUP_CONCAT(AccountId) from tblRateRule rr inner join  tblRateRuleSource rrs on rr.RateRuleId = rrs.RateRuleId where rr.RateGeneratorId = p_RateGeneratorId ) ;




		INSERT INTO tmp_VendorCurrentRates1_
			Select DISTINCT AccountId,AccountName,Code,Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference
			FROM (
						 SELECT  tblVendorRate.AccountId,tblAccount.AccountName, tblRate.Code, tblRate.Description,
							CASE WHEN  tblAccount.CurrencyId = v_CurrencyID_
								THEN
									tblVendorRate.Rate
							WHEN  v_CompanyCurrencyID_ = v_CurrencyID_
								THEN
									(
										( tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ) )
									)
							ELSE
								(

									(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = v_CurrencyID_ and  CompanyID = v_CompanyId_ )
									* (tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ))
								)
							END
							as  Rate,
							 ConnectionFee,
							DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') AS EffectiveDate,
							 tblVendorRate.TrunkID, tblRate.CountryID, tblRate.RateID,IFNULL(vp.Preference, 5) AS Preference,
							@row_num := IF(@prev_AccountId = tblVendorRate.AccountID AND @prev_TrunkID = tblVendorRate.TrunkID AND @prev_RateId = tblVendorRate.RateID AND @prev_EffectiveDate >= tblVendorRate.EffectiveDate, @row_num + 1, 1) AS RowID,
							 @prev_AccountId := tblVendorRate.AccountID,
							 @prev_TrunkID := tblVendorRate.TrunkID,
							 @prev_RateId := tblVendorRate.RateID,
							 @prev_EffectiveDate := tblVendorRate.EffectiveDate
						 FROM      tblVendorRate
							 Inner join tblVendorTrunk vt on vt.CompanyID = v_CompanyId_ AND vt.AccountID = tblVendorRate.AccountID and vt.Status =  1 and vt.TrunkID =  v_trunk_
							 inner join tmp_Codedecks_ tcd on vt.CodeDeckId = tcd.CodeDeckId
							 INNER JOIN tblAccount   ON  tblAccount.CompanyID = v_CompanyId_ AND tblVendorRate.AccountId = tblAccount.AccountID and tblAccount.IsVendor = 1
							 INNER JOIN tblRate ON tblRate.CompanyID = v_CompanyId_  AND tblRate.CodeDeckId = vt.CodeDeckId  AND    tblVendorRate.RateId = tblRate.RateID
							 INNER JOIN tmp_code_ tcode ON tcode.Code  = tblRate.Code
							 LEFT JOIN tblVendorPreference vp
								 ON vp.AccountId = tblVendorRate.AccountId
										AND vp.TrunkID = tblVendorRate.TrunkID
										AND vp.RateId = tblVendorRate.RateId
							 LEFT OUTER JOIN tblVendorBlocking AS blockCode   ON tblVendorRate.RateId = blockCode.RateId
																																	 AND tblVendorRate.AccountId = blockCode.AccountId
																																	 AND tblVendorRate.TrunkID = blockCode.TrunkID
							 LEFT OUTER JOIN tblVendorBlocking AS blockCountry    ON tblRate.CountryID = blockCountry.CountryId
																																			 AND tblVendorRate.AccountId = blockCountry.AccountId
																																			 AND tblVendorRate.TrunkID = blockCountry.TrunkID

							 ,(SELECT @row_num := 1,  @prev_AccountId := '',@prev_TrunkID := '', @prev_RateId := '', @prev_EffectiveDate := '') x

						 WHERE
							 (
								 (p_EffectiveRate = 'now' AND EffectiveDate <= NOW())
								 OR
								 (p_EffectiveRate = 'future' AND EffectiveDate > NOW())
								 OR
								 (p_EffectiveRate = 'effective' AND EffectiveDate <= p_EffectiveDate)
							 )
							 AND tblAccount.IsVendor = 1
							 AND tblAccount.Status = 1
							 AND tblAccount.CurrencyId is not NULL
							 AND tblVendorRate.TrunkID = v_trunk_
							 AND blockCode.RateId IS NULL
							 AND blockCountry.CountryId IS NULL
							 AND ( @IncludeAccountIds = NULL
										 OR ( @IncludeAccountIds IS NOT NULL
													AND FIND_IN_SET(tblVendorRate.AccountId,@IncludeAccountIds) > 0
										 )
							 )
						 ORDER BY tblVendorRate.AccountId, tblVendorRate.TrunkID, tblVendorRate.RateId, tblVendorRate.EffectiveDate DESC
					 ) tbl
			order by Code asc;

		INSERT INTO tmp_VendorCurrentRates_
			Select AccountId,AccountName,Code,Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference
			FROM (
						 SELECT * ,
							 @row_num := IF(@prev_AccountId = AccountID AND @prev_TrunkID = TrunkID AND @prev_RateId = RateID AND @prev_EffectiveDate >= EffectiveDate, @row_num + 1, 1) AS RowID,
							 @prev_AccountId := AccountID,
							 @prev_TrunkID := TrunkID,
							 @prev_RateId := RateID,
							 @prev_EffectiveDate := EffectiveDate
						 FROM tmp_VendorCurrentRates1_
							 ,(SELECT @row_num := 1,  @prev_AccountId := '',@prev_TrunkID := '', @prev_RateId := '', @prev_EffectiveDate := '') x
						 ORDER BY AccountId, TrunkID, RateId, EffectiveDate DESC
					 ) tbl
			WHERE RowID = 1
			order by Code asc;










		/* convert 9131 to all possible codes
			9131
			913
			91
		 */
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








		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage_1;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRate_stage_1 as (select * from tmp_VendorRate_stage_);

		insert ignore into tmp_VendorRate_stage_1 (
			RowCode,
			AccountId ,
			AccountName ,
			Code ,
			Rate ,
			ConnectionFee,
			EffectiveDate ,
			Description ,
			Preference
		)
			SELECT
				distinct
				RowCode,
				v.AccountId ,
				v.AccountName ,
				v.Code ,
				v.Rate ,
				v.ConnectionFee,
				v.EffectiveDate ,
				v.Description ,
				v.Preference
			FROM tmp_VendorCurrentRates_ v
				Inner join  tmp_all_code_
										SplitCode   on v.Code = SplitCode.Code
			where  SplitCode.Code is not null
			order by AccountID,SplitCode.RowCode desc ,LENGTH(SplitCode.RowCode), v.Code desc, LENGTH(v.Code)  desc;



		insert into tmp_VendorRate_stage_
			SELECT
				RowCode,
				v.AccountId ,
				v.AccountName ,
				v.Code ,
				v.Rate ,
				v.ConnectionFee,
				v.EffectiveDate ,
				v.Description ,
				v.Preference,
				@rank := ( CASE WHEN ( @prev_RowCode   = RowCode and   @prev_AccountID = v.AccountId   )
					THEN @rank + 1
									 ELSE 1  END ) AS MaxMatchRank,

				@prev_RowCode := RowCode	 as prev_RowCode,
				@prev_AccountID := v.AccountId as prev_AccountID
			FROM tmp_VendorRate_stage_1 v
				, (SELECT  @prev_RowCode := '',  @rank := 0 , @prev_Code := '' , @prev_AccountID := Null) f
			order by AccountID,RowCode desc ;


		truncate tmp_VendorRate_;
		insert into tmp_VendorRate_
			select
				AccountId ,
				AccountName ,
				Code ,
				Rate ,
				ConnectionFee,
				EffectiveDate ,
				Description ,
				Preference,
				RowCode
			from tmp_VendorRate_stage_
			where MaxMatchRank = 1 order by RowCode desc;







		WHILE v_pointer_ <= v_rowCount_
		DO

			SET v_rateRuleId_ = (SELECT rateruleid FROM tmp_Raterules_ rr WHERE rr.RowNo = v_pointer_);


			INSERT INTO tmp_Rates2_ (code,rate,ConnectionFee)
				select  code,rate,ConnectionFee from tmp_Rates_;



			truncate tmp_final_VendorRate_;

			IF( v_Use_Preference_ = 0 )
			THEN

				insert into tmp_final_VendorRate_
					SELECT
						AccountId ,
						AccountName ,
						Code ,
						Rate ,
						ConnectionFee,
						EffectiveDate ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								vr.AccountId ,
								vr.AccountName ,
								vr.Code ,
								vr.Rate ,
								vr.ConnectionFee,
								vr.EffectiveDate ,
								vr.Description ,
								vr.Preference,
								vr.RowCode,
								@rank := CASE WHEN ( @prev_RowCode = vr.RowCode  AND @prev_Rate <  vr.Rate ) THEN @rank+1
												 WHEN ( @prev_RowCode  = vr.RowCode  AND @prev_Rate = vr.Rate) THEN @rank
												 ELSE
													 1
												 END
									AS FinalRankNumber,
								@prev_RowCode  := vr.RowCode,
								@prev_Rate  := vr.Rate
							from (
										 select tmpvr.*
										 from tmp_VendorRate_  tmpvr
											 inner JOIN tmp_Raterules_ rr ON rr.RateRuleId = v_rateRuleId_ and
																											 -- tmpvr.RowCode LIKE (REPLACE(rr.code,'*', '%%'))
																												( rr.code != '' AND tmpvr.RowCode LIKE (REPLACE(rr.code,'*', '%%')) )
																												OR
																												( rr.description != '' AND tmpvr.Description LIKE (REPLACE(rr.description,'*', '%%')) )
											 inner JOIN tblRateRuleSource rrs ON  rrs.RateRuleId = rr.rateruleid  and rrs.AccountId = tmpvr.AccountId
									 ) vr
								,(SELECT @rank := 0 , @prev_RowCode := '' , @prev_Rate := 0  ) x
							order by vr.RowCode,vr.Rate,vr.AccountId ASC

						) tbl1
					where FinalRankNumber <= v_RatePosition_;

			ELSE

				insert into tmp_final_VendorRate_
					SELECT
						AccountId ,
						AccountName ,
						Code ,
						Rate ,
						ConnectionFee,
						EffectiveDate ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								vr.AccountId ,
								vr.AccountName ,
								vr.Code ,
								vr.Rate ,
								vr.ConnectionFee,
								vr.EffectiveDate ,
								vr.Description ,
								vr.Preference,
								vr.RowCode,
								@preference_rank := CASE WHEN (@prev_Code  = vr.RowCode  AND @prev_Preference > vr.Preference  )   THEN @preference_rank + 1
																		WHEN (@prev_Code  = vr.RowCode  AND @prev_Preference = vr.Preference AND @prev_Rate < vr.Rate) THEN @preference_rank + 1
																		WHEN (@prev_Code  = vr.RowCode  AND @prev_Preference = vr.Preference AND @prev_Rate = vr.Rate) THEN @preference_rank
																		ELSE 1 END AS FinalRankNumber,
								@prev_Code := vr.RowCode,
								@prev_Preference := vr.Preference,
								@prev_Rate := vr.Rate
							from (
										 select tmpvr.*
										 from tmp_VendorRate_  tmpvr
											 inner JOIN tmp_Raterules_ rr ON rr.RateRuleId = v_rateRuleId_ and
																											 -- tmpvr.RowCode LIKE (REPLACE(rr.code,'*', '%%'))
																											 ( rr.code != '' AND tmpvr.RowCode LIKE (REPLACE(rr.code,'*', '%%')) )
																											 OR
																											 ( rr.description != '' AND tmpvr.Description LIKE (REPLACE(rr.description,'*', '%%')) )
											 inner JOIN tblRateRuleSource rrs ON  rrs.RateRuleId = rr.rateruleid  and rrs.AccountId = tmpvr.AccountId
									 ) vr

								,(SELECT @preference_rank := 0 , @prev_Code := ''  , @prev_Preference := 5,  @prev_Rate := 0) x
							order by vr.RowCode ASC ,vr.Preference DESC ,vr.Rate ASC ,vr.AccountId ASC

						) tbl1
					where 				FinalRankNumber <= v_RatePosition_;

			END IF;



			truncate   tmp_VRatesstage2_;

			INSERT INTO tmp_VRatesstage2_
				SELECT
					vr.RowCode,
					vr.code,
					vr.rate,
					vr.ConnectionFee,
					vr.FinalRankNumber
				FROM tmp_final_VendorRate_ vr
					left join tmp_Rates2_ rate on rate.Code = vr.RowCode
				WHERE  rate.code is null
				order by vr.FinalRankNumber desc ;



			IF v_Average_ = 0
			THEN
				insert into tmp_dupVRatesstage2_
					SELECT RowCode , MAX(FinalRankNumber) AS MaxFinalRankNumber
					FROM tmp_VRatesstage2_ GROUP BY RowCode;

				truncate tmp_Vendorrates_stage3_;
				INSERT INTO tmp_Vendorrates_stage3_
					select  vr.RowCode as RowCode , vr.rate as rate , vr.ConnectionFee as  ConnectionFee
					from tmp_VRatesstage2_ vr
						INNER JOIN tmp_dupVRatesstage2_ vr2
							ON (vr.RowCode = vr2.RowCode AND  vr.FinalRankNumber = vr2.FinalRankNumber);

				INSERT IGNORE INTO tmp_Rates_
					SELECT RowCode,
						CASE WHEN rule_mgn.RateRuleId is not null
							THEN
								CASE WHEN AddMargin LIKE '%p'
									THEN ( vRate.rate + (CAST(REPLACE(AddMargin, 'p', '') AS DECIMAL(18, 2)) / 100) * vRate.rate)
								ELSE  vRate.rate + AddMargin
								END
						ELSE
							vRate.rate
						END as Rate,
						ConnectionFee
					FROM tmp_Vendorrates_stage3_ vRate
						left join tblRateRuleMargin rule_mgn on  rule_mgn.RateRuleId = v_rateRuleId_ and vRate.rate Between rule_mgn.MinRate and rule_mgn.MaxRate;



			ELSE

				INSERT IGNORE INTO tmp_Rates_
					SELECT RowCode,
						CASE WHEN rule_mgn.AddMargin is not null
							THEN
								CASE WHEN AddMargin LIKE '%p'
									THEN ( vRate.rate + (CAST(REPLACE(AddMargin, 'p', '') AS DECIMAL(18, 2)) / 100) * vRate.rate)
								ELSE  vRate.rate + AddMargin
								END
						ELSE
							vRate.rate
						END as Rate,
						ConnectionFee
					FROM (
								 select RowCode,
									 AVG(Rate) as Rate,
									 AVG(ConnectionFee) as ConnectionFee
								 from tmp_VRatesstage2_
								 group by RowCode
							 )  vRate
						left join tblRateRuleMargin rule_mgn on  rule_mgn.RateRuleId = v_rateRuleId_ and vRate.rate Between rule_mgn.MinRate and rule_mgn.MaxRate;
			END IF;


			SET v_pointer_ = v_pointer_ + 1;


		END WHILE;



		START TRANSACTION;

		IF p_RateTableId = -1
		THEN

			INSERT INTO tblRateTable (CompanyId, RateTableName, RateGeneratorID, TrunkID, CodeDeckId,CurrencyID)
			VALUES (v_CompanyId_, p_rateTableName, p_RateGeneratorId, v_trunk_, v_codedeckid_,v_CurrencyID_);

			SET p_RateTableId = LAST_INSERT_ID();

			INSERT INTO tblRateTableRate (RateID,
																		RateTableId,
																		Rate,
																		EffectiveDate,
																		PreviousRate,
																		Interval1,
																		IntervalN,
																		ConnectionFee
			)
				SELECT DISTINCT
					RateId,
					p_RateTableId,
					Rate,
					p_EffectiveDate,
					Rate,
					Interval1,
					IntervalN,
					ConnectionFee
				FROM tmp_Rates_ rate
					INNER JOIN tblRate
						ON rate.code  = tblRate.Code
				WHERE tblRate.CodeDeckId = v_codedeckid_;

		ELSE

			IF p_delete_exiting_rate = 1
			THEN
				DELETE tblRateTableRate
				FROM tblRateTableRate
				WHERE tblRateTableRate.RateTableId = p_RateTableId;
			END IF;

			INSERT INTO tblRateTableRate (RateID,
																		RateTableId,
																		Rate,
																		EffectiveDate,
																		PreviousRate,
																		Interval1,
																		IntervalN,
																		ConnectionFee
			)
				SELECT DISTINCT
					tblRate.RateId,
					p_RateTableId RateTableId,
					rate.Rate,
					p_EffectiveDate EffectiveDate,
					rate.Rate,
					tblRate.Interval1,
					tblRate.IntervalN,
					rate.ConnectionFee
				FROM tmp_Rates_ rate
					INNER JOIN tblRate
						ON rate.code  = tblRate.Code
					LEFT JOIN tblRateTableRate tbl1
						ON tblRate.RateId = tbl1.RateId
							 AND tbl1.RateTableId = p_RateTableId
					LEFT JOIN tblRateTableRate tbl2
						ON tblRate.RateId = tbl2.RateId
							 and tbl2.EffectiveDate = p_EffectiveDate
							 AND tbl2.RateTableId = p_RateTableId
				WHERE  (    tbl1.RateTableRateID IS NULL
										OR
										(
											tbl2.RateTableRateID IS NULL
											AND  tbl1.EffectiveDate != p_EffectiveDate

										)
							 )
							 AND tblRate.CodeDeckId = v_codedeckid_;

			UPDATE tblRateTableRate
				INNER JOIN tblRate
					ON tblRate.RateId = tblRateTableRate.RateId
						 AND tblRateTableRate.RateTableId = p_RateTableId
						 AND tblRateTableRate.EffectiveDate = p_EffectiveDate
				INNER JOIN tmp_Rates_ as rate
					ON  rate.code  = tblRate.Code
			SET tblRateTableRate.PreviousRate = tblRateTableRate.Rate,
				tblRateTableRate.EffectiveDate = p_EffectiveDate,
				tblRateTableRate.Rate = rate.Rate,
				tblRateTableRate.ConnectionFee = rate.ConnectionFee,
				tblRateTableRate.updated_at = NOW(),
				tblRateTableRate.ModifiedBy = 'RateManagementService',
				tblRateTableRate.Interval1 = tblRate.Interval1,
				tblRateTableRate.IntervalN = tblRate.IntervalN
			WHERE tblRate.CodeDeckId = v_codedeckid_
						AND rate.rate != tblRateTableRate.Rate;

			DELETE tblRateTableRate
			FROM tblRateTableRate
			WHERE tblRateTableRate.RateTableId = p_RateTableId
						AND RateId NOT IN (SELECT DISTINCT
																 RateId
															 FROM tmp_Rates_ rate
																 INNER JOIN tblRate
																	 ON rate.code  = tblRate.Code
															 WHERE tblRate.CodeDeckId = v_codedeckid_)
						AND tblRateTableRate.EffectiveDate = p_EffectiveDate;


		END IF;


		UPDATE tblRateTable
		SET RateGeneratorID = p_RateGeneratorId,
			TrunkID = v_trunk_,
			CodeDeckId = v_codedeckid_,
			updated_at = now()
		WHERE RateTableID = p_RateTableId;

		SELECT p_RateTableId as RateTableID;

		CALL prc_WSJobStatusUpdate(p_jobId, 'S', 'RateTable Created Successfully', '');

		COMMIT;


		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;



	END//
DELIMITER ;




DELIMITER  //
CREATE PROCEDURE `prc_WSGenerateRateTableWithPrefix`(
	IN `p_jobId` INT,
	IN `p_RateGeneratorId` INT,
	IN `p_RateTableId` INT,
	IN `p_rateTableName` VARCHAR(200),
	IN `p_EffectiveDate` VARCHAR(10),
	IN `p_delete_exiting_rate` INT,
	IN `p_EffectiveRate` VARCHAR(50)



)
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
	SQL SECURITY DEFINER
	COMMENT ''
		GenerateRateTable:BEGIN


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



		DECLARE v_tmp_code_cnt int ;
		DECLARE v_tmp_code_pointer int;
		DECLARE v_p_code varchar(50);
		DECLARE v_Codlen_ int;
		DECLARE v_p_code__ VARCHAR(50);
		DECLARE v_Commit int;
		DECLARE EXIT HANDLER FOR SQLEXCEPTION
		BEGIN
			show warnings;
			ROLLBACK;
			CALL prc_WSJobStatusUpdate(p_jobId, 'F', 'RateTable generation failed', '');

		END;

		SET @@session.collation_connection='utf8_unicode_ci';
		SET @@session.character_set_client='utf8';

		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;



		SET p_EffectiveDate = CAST(p_EffectiveDate AS DATE);


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
				CALL prc_WSJobStatusUpdate  (p_jobId, 'F', 'RateTable Name is already exist, Please try using another RateTable Name', '');
				LEAVE GenerateRateTable;
			END IF;
		END IF;

		DROP TEMPORARY TABLE IF EXISTS tmp_Rates_;
		CREATE TEMPORARY TABLE tmp_Rates_  (
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			INDEX tmp_Rates_code (`code`),
			UNIQUE KEY `unique_code` (`code`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Rates2_;
		CREATE TEMPORARY TABLE tmp_Rates2_  (
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			INDEX tmp_Rates2_code (`code`)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_Codedecks_;
		CREATE TEMPORARY TABLE tmp_Codedecks_ (
			CodeDeckId INT
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Raterules_;

		CREATE TEMPORARY TABLE tmp_Raterules_  (
			rateruleid INT,
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			RowNo INT,
			INDEX tmp_Raterules_code (`code`),
			INDEX tmp_Raterules_rateruleid (`rateruleid`),
			INDEX tmp_Raterules_RowNo (`RowNo`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Vendorrates_;
		CREATE TEMPORARY TABLE tmp_Vendorrates_  (
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			AccountId INT,
			RowNo INT,
			PreferenceRank INT,
			INDEX tmp_Vendorrates_code (`code`),
			INDEX tmp_Vendorrates_rate (`rate`)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_VRatesstage2_;
		CREATE TEMPORARY TABLE tmp_VRatesstage2_  (
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci,
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			FinalRankNumber int,
			INDEX tmp_Vendorrates_stage2__code (`RowCode`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_dupVRatesstage2_;
		CREATE TEMPORARY TABLE tmp_dupVRatesstage2_  (
			RowCode VARCHAR(50)  COLLATE utf8_unicode_ci,
			FinalRankNumber int,
			INDEX tmp_dupVendorrates_stage2__code (`RowCode`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Vendorrates_stage3_;
		CREATE TEMPORARY TABLE tmp_Vendorrates_stage3_  (
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			INDEX tmp_Vendorrates_stage2__code (`RowCode`)
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_code_;
		CREATE TEMPORARY TABLE tmp_code_  (
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
			Code VARCHAR(50) COLLATE utf8_unicode_ci,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			MaxMatchRank int ,
			prev_prev_RowCode VARCHAR(50),
			prev_AccountID int
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
		CREATE TEMPORARY TABLE tmp_VendorRate_ (
			AccountId INT ,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) COLLATE utf8_unicode_ci,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_final_VendorRate_;
		CREATE TEMPORARY TABLE tmp_final_VendorRate_ (
			AccountId INT ,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) COLLATE utf8_unicode_ci,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci,
			FinalRankNumber int,
			INDEX IX_CODE (RowCode)
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates_(
			AccountId int,
			AccountName varchar(200),
			Code varchar(50) COLLATE utf8_unicode_ci,
			Description varchar(200) ,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate date,
			TrunkID int,
			CountryID int,
			RateID int,
			Preference int,
			INDEX IX_CODE (Code)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates1_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates1_(
			AccountId int,
			AccountName varchar(200),
			Code varchar(50),
			Description varchar(200),
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate date,
			TrunkID int,
			CountryID int,
			RateID int,
			Preference int,
			INDEX IX_Code (Code),
			INDEX tmp_VendorCurrentRates_AccountId (`AccountId`,`TrunkID`,`RateId`,`EffectiveDate`)
		);

		SELECT CurrencyID INTO v_CurrencyID_ FROM  tblRateGenerator WHERE RateGeneratorId = p_RateGeneratorId;

		SELECT
			UsePreference,
			rateposition,
			companyid ,
			CodeDeckId,
			tblRateGenerator.TrunkID,
			tblRateGenerator.UseAverage  ,
			tblRateGenerator.RateGeneratorName INTO v_Use_Preference_, v_RatePosition_, v_CompanyId_, v_codedeckid_, v_trunk_, v_Average_, v_RateGeneratorName_
		FROM tblRateGenerator
		WHERE RateGeneratorId = p_RateGeneratorId;


		SELECT CurrencyId INTO v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = v_CompanyId_;




		INSERT INTO tmp_Raterules_
			SELECT
				rateruleid,
				tblRateRule.code,
				@row_num := @row_num+1 AS RowID
			FROM tblRateRule,(SELECT @row_num := 0) x
			WHERE rategeneratorid = p_RateGeneratorId
			ORDER BY tblRateRule.Code DESC;

		INSERT INTO tmp_Codedecks_
			SELECT DISTINCT
				tblVendorTrunk.CodeDeckId
			FROM tblRateRule
				INNER JOIN tblRateRuleSource
					ON tblRateRule.RateRuleId = tblRateRuleSource.RateRuleId
				INNER JOIN tblAccount
					ON tblAccount.AccountID = tblRateRuleSource.AccountId and tblAccount.IsVendor = 1
				JOIN tblVendorTrunk
					ON tblAccount.AccountId = tblVendorTrunk.AccountID
						 AND  tblVendorTrunk.TrunkID = v_trunk_
						 AND tblVendorTrunk.Status = 1
			WHERE RateGeneratorId = p_RateGeneratorId;

		SET v_pointer_ = 1;
		SET v_rowCount_ = (SELECT COUNT(distinct Code ) FROM tmp_Raterules_);







		insert into tmp_code_
			SELECT
				tblRate.code
			FROM tblRate
				JOIN tmp_Codedecks_ cd
					ON tblRate.CodeDeckId = cd.CodeDeckId
				JOIN tmp_Raterules_ rr
					ON ( rr.code != '' AND tblRate.Code LIKE (REPLACE(rr.code,'*', '%%')) )
						 OR
						 ( rr.description != '' AND tblRate.Description LIKE (REPLACE(rr.description,'*', '%%')) )
			Order by tblRate.code ;









		SELECT CurrencyId INTO v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = v_CompanyId_;
		SET @IncludeAccountIds = (SELECT GROUP_CONCAT(AccountId) from tblRateRule rr inner join  tblRateRuleSource rrs on rr.RateRuleId = rrs.RateRuleId where rr.RateGeneratorId = p_RateGeneratorId ) ;



		INSERT INTO tmp_VendorCurrentRates1_
			Select DISTINCT AccountId,AccountName,Code,Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference
			FROM (
						 SELECT  tblVendorRate.AccountId,tblAccount.AccountName, tblRate.Code, tblRate.Description,
								CASE WHEN  tblAccount.CurrencyId = v_CurrencyID_
									THEN
										tblVendorRate.Rate
								WHEN  v_CompanyCurrencyID_ = v_CurrencyID_
									THEN
										(
											( tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ) )
										)
								ELSE
									(

										(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = v_CurrencyID_ and  CompanyID = v_CompanyId_ )
										* (tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ))
									)
								END
								as  Rate,
							 ConnectionFee,
																																				DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') AS EffectiveDate,
							 tblVendorRate.TrunkID, tblRate.CountryID, tblRate.RateID,IFNULL(vp.Preference, 5) AS Preference,
																																				@row_num := IF(@prev_AccountId = tblVendorRate.AccountID AND @prev_TrunkID = tblVendorRate.TrunkID AND @prev_RateId = tblVendorRate.RateID AND @prev_EffectiveDate >= tblVendorRate.EffectiveDate, @row_num + 1, 1) AS RowID,
							 @prev_AccountId := tblVendorRate.AccountID,
							 @prev_TrunkID := tblVendorRate.TrunkID,
							 @prev_RateId := tblVendorRate.RateID,
							 @prev_EffectiveDate := tblVendorRate.EffectiveDate
						 FROM      tblVendorRate

							 Inner join tblVendorTrunk vt on vt.CompanyID = v_CompanyId_ AND vt.AccountID = tblVendorRate.AccountID and

																							 vt.Status =  1 and vt.TrunkID =  v_trunk_
							 inner join tmp_Codedecks_ tcd on vt.CodeDeckId = tcd.CodeDeckId
							 INNER JOIN tblAccount   ON  tblAccount.CompanyID = v_CompanyId_ AND tblVendorRate.AccountId = tblAccount.AccountID and tblAccount.IsVendor = 1
							 INNER JOIN tblRate ON tblRate.CompanyID = v_CompanyId_  AND tblRate.CodeDeckId = vt.CodeDeckId  AND    tblVendorRate.RateId = tblRate.RateID
							 INNER JOIN tmp_code_ tcode ON tcode.Code  = tblRate.Code
							 LEFT JOIN tblVendorPreference vp
								 ON vp.AccountId = tblVendorRate.AccountId
										AND vp.TrunkID = tblVendorRate.TrunkID
										AND vp.RateId = tblVendorRate.RateId
							 LEFT OUTER JOIN tblVendorBlocking AS blockCode   ON tblVendorRate.RateId = blockCode.RateId
																																	 AND tblVendorRate.AccountId = blockCode.AccountId
																																	 AND tblVendorRate.TrunkID = blockCode.TrunkID
							 LEFT OUTER JOIN tblVendorBlocking AS blockCountry    ON tblRate.CountryID = blockCountry.CountryId
																																			 AND tblVendorRate.AccountId = blockCountry.AccountId
																																			 AND tblVendorRate.TrunkID = blockCountry.TrunkID

							 ,(SELECT @row_num := 1,  @prev_AccountId := '',@prev_TrunkID := '', @prev_RateId := '', @prev_EffectiveDate := '') x

						 WHERE
							 (
								 (p_EffectiveRate = 'now' AND EffectiveDate <= NOW())
								 OR
								 (p_EffectiveRate = 'future' AND EffectiveDate > NOW())
								 OR
								 (p_EffectiveRate = 'effective' AND EffectiveDate <= p_EffectiveDate)
							 )
							 AND tblAccount.IsVendor = 1
							 AND tblAccount.Status = 1
							 AND tblAccount.CurrencyId is not NULL
							 AND tblVendorRate.TrunkID = v_trunk_
							 AND blockCode.RateId IS NULL
							 AND blockCountry.CountryId IS NULL
							 AND ( @IncludeAccountIds = NULL
										 OR ( @IncludeAccountIds IS NOT NULL
													AND FIND_IN_SET(tblVendorRate.AccountId,@IncludeAccountIds) > 0
										 )
							 )
						 ORDER BY tblVendorRate.AccountId, tblVendorRate.TrunkID, tblVendorRate.RateId, tblVendorRate.EffectiveDate DESC
					 ) tbl
			order by Code asc;

		INSERT INTO tmp_VendorCurrentRates_
			Select AccountId,AccountName,Code,Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference
			FROM (
						 SELECT * ,
							 @row_num := IF(@prev_AccountId = AccountID AND @prev_TrunkID = TrunkID AND @prev_RateId = RateID AND @prev_EffectiveDate >= EffectiveDate, @row_num + 1, 1) AS RowID,
							 @prev_AccountId := AccountID,
							 @prev_TrunkID := TrunkID,
							 @prev_RateId := RateID,
							 @prev_EffectiveDate := EffectiveDate
						 FROM tmp_VendorCurrentRates1_
							 ,(SELECT @row_num := 1,  @prev_AccountId := '',@prev_TrunkID := '', @prev_RateId := '', @prev_EffectiveDate := '') x
						 ORDER BY AccountId, TrunkID, RateId, EffectiveDate DESC
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
										select distinct Code as RowCode, Code as  loopCode from
											tmp_VendorCurrentRates_
									) tbl1
							 , ( Select @RowNo := 0 ) x
					 ) tbl order by RowCode desc,  LENGTH(loopCode) DESC ;















		insert into tmp_VendorRate_
			select
				AccountId ,
				AccountName ,
				Code ,
				Rate ,
				ConnectionFee,
				EffectiveDate ,
				Description ,
				Preference,
				Code as RowCode
			from tmp_VendorCurrentRates_;






		WHILE v_pointer_ <= v_rowCount_
		DO

			SET v_rateRuleId_ = (SELECT rateruleid FROM tmp_Raterules_ rr WHERE rr.RowNo = v_pointer_);


			INSERT INTO tmp_Rates2_ (code,rate,ConnectionFee)
				select  code,rate,ConnectionFee from tmp_Rates_;



			truncate tmp_final_VendorRate_;

			IF( v_Use_Preference_ = 0 )
			THEN

				insert into tmp_final_VendorRate_
					SELECT
						AccountId ,
						AccountName ,
						Code ,
						Rate ,
						ConnectionFee,
						EffectiveDate ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								vr.AccountId ,
								vr.AccountName ,
								vr.Code ,
								vr.Rate ,
								vr.ConnectionFee,
								vr.EffectiveDate ,
								vr.Description ,
								vr.Preference,
								vr.RowCode,
								@rank := CASE WHEN ( @prev_RowCode = vr.RowCode  AND @prev_Rate <=  vr.Rate ) THEN @rank+1

												 ELSE
													 1
												 END
									AS FinalRankNumber,
								@prev_RowCode  := vr.RowCode,
								@prev_Rate  := vr.Rate
							from (
										 select tmpvr.*
										 from tmp_VendorRate_  tmpvr
											 inner JOIN tmp_Raterules_ rr ON rr.RateRuleId = v_rateRuleId_ and
																											 -- tmpvr.RowCode LIKE (REPLACE(rr.code,'*', '%%'))
																											 ( rr.code != '' AND tmpvr.RowCode LIKE (REPLACE(rr.code,'*', '%%')) )
																											 OR
																											 ( rr.description != '' AND tmpvr.Description LIKE (REPLACE(rr.description,'*', '%%')) )
											 inner JOIN tblRateRuleSource rrs ON  rrs.RateRuleId = rr.rateruleid  and rrs.AccountId = tmpvr.AccountId
									 ) vr
								,(SELECT @rank := 0 , @prev_RowCode := '' , @prev_Rate := 0  ) x
							order by vr.RowCode,vr.Rate,vr.AccountId ASC

						) tbl1
					where FinalRankNumber <= v_RatePosition_;

			ELSE

				insert into tmp_final_VendorRate_
					SELECT
						AccountId ,
						AccountName ,
						Code ,
						Rate ,
						ConnectionFee,
						EffectiveDate ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								vr.AccountId ,
								vr.AccountName ,
								vr.Code ,
								vr.Rate ,
								vr.ConnectionFee,
								vr.EffectiveDate ,
								vr.Description ,
								vr.Preference,
								vr.RowCode,
								@preference_rank := CASE WHEN (@prev_Code  = vr.RowCode  AND @prev_Preference > vr.Preference  )   THEN @preference_rank + 1
																		WHEN (@prev_Code  = vr.RowCode  AND @prev_Preference = vr.Preference AND @prev_Rate <= vr.Rate) THEN @preference_rank + 1

																		ELSE 1 END AS FinalRankNumber,
								@prev_Code := vr.RowCode,
								@prev_Preference := vr.Preference,
								@prev_Rate := vr.Rate
							from (
										 select tmpvr.*
										 from tmp_VendorRate_  tmpvr
											 inner JOIN tmp_Raterules_ rr ON rr.RateRuleId = v_rateRuleId_ and
																											 -- tmpvr.RowCode LIKE (REPLACE(rr.code,'*', '%%'))
																											 ( rr.code != '' AND tmpvr.RowCode LIKE (REPLACE(rr.code,'*', '%%')) )
																											 OR
																											 ( rr.description != '' AND tmpvr.Description LIKE (REPLACE(rr.description,'*', '%%')) )
											 inner JOIN tblRateRuleSource rrs ON  rrs.RateRuleId = rr.rateruleid  and rrs.AccountId = tmpvr.AccountId
									 ) vr

								,(SELECT @preference_rank := 0 , @prev_Code := ''  , @prev_Preference := 5,  @prev_Rate := 0) x
							order by vr.RowCode ASC ,vr.Preference DESC ,vr.Rate ASC ,vr.AccountId ASC

						) tbl1
					where FinalRankNumber <= v_RatePosition_;

			END IF;



			truncate   tmp_VRatesstage2_;

			INSERT INTO tmp_VRatesstage2_
				SELECT
					vr.RowCode,
					vr.code,
					vr.rate,
					vr.ConnectionFee,
					vr.FinalRankNumber
				FROM tmp_final_VendorRate_ vr
					left join tmp_Rates2_ rate on rate.Code = vr.RowCode
				WHERE  rate.code is null
				order by vr.FinalRankNumber desc ;



			IF v_Average_ = 0
			THEN
				insert into tmp_dupVRatesstage2_
					SELECT RowCode , MAX(FinalRankNumber) AS MaxFinalRankNumber
					FROM tmp_VRatesstage2_ GROUP BY RowCode;

				truncate tmp_Vendorrates_stage3_;
				INSERT INTO tmp_Vendorrates_stage3_
					select  vr.RowCode as RowCode , vr.rate as rate , vr.ConnectionFee as  ConnectionFee
					from tmp_VRatesstage2_ vr
						INNER JOIN tmp_dupVRatesstage2_ vr2
							ON (vr.RowCode = vr2.RowCode AND  vr.FinalRankNumber = vr2.FinalRankNumber);

				INSERT IGNORE INTO tmp_Rates_
					SELECT
						RowCode,
						IFNULL((SELECT
						CASE WHEN vRate.rate  BETWEEN minrate AND maxrate THEN vRate.rate
																																	 + (CASE WHEN addmargin LIKE '%p' THEN ((CAST(REPLACE(addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * vRate.rate) ELSE addmargin
																																			END) ELSE vRate.rate
						END
										FROM tblRateRuleMargin
										WHERE rateruleid = v_rateRuleId_
													and vRate.rate  BETWEEN minrate AND maxrate LIMIT 1
									 ),vRate.Rate) as Rate,
						ConnectionFee
					FROM tmp_Vendorrates_stage3_ vRate;



			ELSE

				INSERT IGNORE INTO tmp_Rates_
					SELECT
						RowCode,
						IFNULL((SELECT
						CASE WHEN vRate.rate  BETWEEN minrate AND maxrate THEN vRate.rate
																																	 + (CASE WHEN addmargin LIKE '%p' THEN ((CAST(REPLACE(addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * vRate.rate) ELSE addmargin
																																			END) ELSE vRate.rate
						END
										FROM tblRateRuleMargin
										WHERE rateruleid = v_rateRuleId_
													and vRate.rate  BETWEEN minrate AND maxrate LIMIT 1
									 ),vRate.Rate) as Rate,
						ConnectionFee
					FROM (
								 select RowCode,
									 AVG(Rate) as Rate,
									 AVG(ConnectionFee) as ConnectionFee
								 from tmp_VRatesstage2_
								 group by RowCode
							 )  vRate;




			END IF;


			SET v_pointer_ = v_pointer_ + 1;


		END WHILE;


		START TRANSACTION;

		IF p_RateTableId = -1
		THEN

			INSERT INTO tblRateTable (CompanyId, RateTableName, RateGeneratorID, TrunkID, CodeDeckId,CurrencyID)
			VALUES (v_CompanyId_, p_rateTableName, p_RateGeneratorId, v_trunk_, v_codedeckid_,v_CurrencyID_);

			SET p_RateTableId = LAST_INSERT_ID();

			INSERT INTO tblRateTableRate (RateID,
																		RateTableId,
																		Rate,
																		EffectiveDate,
																		PreviousRate,
																		Interval1,
																		IntervalN,
																		ConnectionFee
			)
				SELECT DISTINCT
					RateId,
					p_RateTableId,
					Rate,
					p_EffectiveDate,
					Rate,
					Interval1,
					IntervalN,
					ConnectionFee
				FROM tmp_Rates_ rate
					INNER JOIN tblRate
						ON rate.code  = tblRate.Code
				WHERE tblRate.CodeDeckId = v_codedeckid_;

		ELSE

			IF p_delete_exiting_rate = 1
			THEN
				DELETE tblRateTableRate
				FROM tblRateTableRate
				WHERE tblRateTableRate.RateTableId = p_RateTableId;
			END IF;

			INSERT INTO tblRateTableRate (RateID,
																		RateTableId,
																		Rate,
																		EffectiveDate,
																		PreviousRate,
																		Interval1,
																		IntervalN,
																		ConnectionFee
			)
				SELECT DISTINCT
					tblRate.RateId,
					p_RateTableId RateTableId,
					rate.Rate,
					p_EffectiveDate EffectiveDate,
					rate.Rate,
					tblRate.Interval1,
					tblRate.IntervalN,
					rate.ConnectionFee
				FROM tmp_Rates_ rate
					INNER JOIN tblRate
						ON rate.code  = tblRate.Code
					LEFT JOIN tblRateTableRate tbl1
						ON tblRate.RateId = tbl1.RateId
							 AND tbl1.RateTableId = p_RateTableId
					LEFT JOIN tblRateTableRate tbl2
						ON tblRate.RateId = tbl2.RateId
							 and tbl2.EffectiveDate = p_EffectiveDate
							 AND tbl2.RateTableId = p_RateTableId
				WHERE  (    tbl1.RateTableRateID IS NULL
										OR
										(
											tbl2.RateTableRateID IS NULL
											AND  tbl1.EffectiveDate != p_EffectiveDate

										)
							 )
							 AND tblRate.CodeDeckId = v_codedeckid_;

			UPDATE tblRateTableRate
				INNER JOIN tblRate
					ON tblRate.RateId = tblRateTableRate.RateId
						 AND tblRateTableRate.RateTableId = p_RateTableId
						 AND tblRateTableRate.EffectiveDate = p_EffectiveDate
				INNER JOIN tmp_Rates_ as rate
					ON  rate.code  = tblRate.Code
			SET tblRateTableRate.PreviousRate = tblRateTableRate.Rate,
				tblRateTableRate.EffectiveDate = p_EffectiveDate,
				tblRateTableRate.Rate = rate.Rate,
				tblRateTableRate.ConnectionFee = rate.ConnectionFee,
				tblRateTableRate.updated_at = NOW(),
				tblRateTableRate.ModifiedBy = 'RateManagementService',
				tblRateTableRate.Interval1 = tblRate.Interval1,
				tblRateTableRate.IntervalN = tblRate.IntervalN
			WHERE tblRate.CodeDeckId = v_codedeckid_
						AND rate.rate != tblRateTableRate.Rate;

			DELETE tblRateTableRate
			FROM tblRateTableRate
			WHERE tblRateTableRate.RateTableId = p_RateTableId
						AND RateId NOT IN (SELECT DISTINCT
																 RateId
															 FROM tmp_Rates_ rate
																 INNER JOIN tblRate
																	 ON rate.code  = tblRate.Code
															 WHERE tblRate.CodeDeckId = v_codedeckid_)
						AND tblRateTableRate.EffectiveDate = p_EffectiveDate;


		END IF;


		UPDATE tblRateTable
		SET RateGeneratorID = p_RateGeneratorId,
			TrunkID = v_trunk_,
			CodeDeckId = v_codedeckid_,
			updated_at = now()
		WHERE RateTableID = p_RateTableId;

		SELECT p_RateTableId as RateTableID;

		CALL prc_WSJobStatusUpdate(p_jobId, 'S', 'RateTable Created Successfully', '');

		COMMIT;


		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;



	END//
DELIMITER ;