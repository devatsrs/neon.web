Use Ratemanagement3;

INSERT INTO `tblResourceCategories` (`ResourceCategoryID`, `ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES (1306, 'RateCompare.All', 1, 5);
INSERT INTO `tblResource` ( `ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ( 'RateCompare.*', 'RateCompareController.*', 1, 'Sumera Saeed', NULL, '2017-08-31 17:16:34.000', '2017-08-31 17:16:34.000', 1306);

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

    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	    -- Vendor Rate
	    IF ( p_Type = 'vendor_rate') THEN

	      IF ( p_GroupBy = 'description') THEN

	        -- Vendor Rate group by description
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


	      ELSE
	        -- Vendor Rate by code and EffectiveDate
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
