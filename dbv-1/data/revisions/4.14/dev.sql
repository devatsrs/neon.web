Use Ratemanagement3;


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
      RateID int,
      INDEX Index1 (Code)
    );

    DROP TEMPORARY TABLE IF EXISTS tmp_final_compare;
    CREATE TEMPORARY TABLE tmp_final_compare (
      Code  varchar(50),
      Description VARCHAR(200) ,
      RateID int,
      INDEX Index1 (Code)
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
      ColumnName  varchar(200)
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
      select Code,RateID
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
    INSERT  INTO  tmp_final_compare (Code,Description,RateID)
      SELECT 	DISTINCT 		Code,		Description,		RateID
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
    SET v_pointer_ = 1;
    SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_vendors_ WHERE  FIND_IN_SET(AccountID , p_source_vendors) > 0);
    SET @Group_sql = '';

    IF v_rowCount_ > 0 THEN

      WHILE v_pointer_ <= v_rowCount_
      DO

        SET @AccountID = (SELECT AccountID FROM tmp_vendors_ WHERE RowID = v_pointer_);
        SET @AccountName = (SELECT AccountName FROM tmp_vendors_ WHERE RowID = v_pointer_);

        IF ( FIND_IN_SET(@AccountID , p_source_vendors) > 0  ) THEN

          SET @ColumnName = concat('`Source:VendorRate <br>', @AccountName ,'`' );

          SET @stm1 = CONCAT('ALTER   TABLE `tmp_final_compare` ADD COLUMN ', @ColumnName , ' VARCHAR(100) NULL DEFAULT NULL');

          PREPARE stmt1 FROM @stm1;
          EXECUTE stmt1;
          DEALLOCATE PREPARE stmt1;

          SET @stm2 = CONCAT('UPDATE `tmp_final_compare` tmp  INNER JOIN tmp_VendorRate_ vr on vr.Code = tmp.Code set ', @ColumnName , ' =  IFNULL(concat(vr.Rate,"<br>",vr.EffectiveDate),"") WHERE vr.AccountID = ', @AccountID , ' ;');

          PREPARE stmt2 FROM @stm2;
          EXECUTE stmt2;
          DEALLOCATE PREPARE stmt2;

          SET @stm2 = CONCAT('UPDATE `tmp_final_compare`  set ', @ColumnName , ' =  "" where  ', @ColumnName , ' is null;');

          PREPARE stmt2 FROM @stm2;
          EXECUTE stmt2;
          DEALLOCATE PREPARE stmt2;

          INSERT INTO tmp_dynamic_columns_  values ( @ColumnName );


        END IF;


        SET v_pointer_ = v_pointer_ + 1;


      END WHILE;

    END IF;

    #source customer insert rates
    SET v_pointer_ = 1;
    SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_customers_ WHERE  FIND_IN_SET(AccountID , p_source_customers) > 0);

    IF v_rowCount_ > 0 THEN

      WHILE v_pointer_ <= v_rowCount_
      DO

        SET @AccountID = (SELECT AccountID FROM tmp_customers_ WHERE RowID = v_pointer_);
        SET @AccountName = (SELECT AccountName FROM tmp_customers_ WHERE RowID = v_pointer_);

        IF ( FIND_IN_SET(@AccountID , p_source_customers) > 0  ) THEN

          SET @ColumnName = concat('`Source:CustomerRate <br>', @AccountName ,'`');

          SET @stm1 = CONCAT('ALTER   TABLE `tmp_final_compare` ADD COLUMN ', @ColumnName , ' VARCHAR(100) NULL DEFAULT NULL');


          PREPARE stmt1 FROM @stm1;
          EXECUTE stmt1;
          DEALLOCATE PREPARE stmt1;

          SET @stm2 = CONCAT('UPDATE `tmp_final_compare` tmp  INNER JOIN tmp_CustomerRate_ vr on vr.Code = tmp.Code set ', @ColumnName , ' =  IFNULL(concat(vr.Rate,"<br>",vr.EffectiveDate),"") WHERE vr.AccountID = ', @AccountID , ' ;');

          PREPARE stmt2 FROM @stm2;
          EXECUTE stmt2;
          DEALLOCATE PREPARE stmt2;

          SET @stm2 = CONCAT('UPDATE `tmp_final_compare`  set ', @ColumnName , ' =  "" where  ', @ColumnName , ' is null;');

          PREPARE stmt2 FROM @stm2;
          EXECUTE stmt2;
          DEALLOCATE PREPARE stmt2;


          INSERT INTO tmp_dynamic_columns_  values ( @ColumnName );

        END IF;

        SET v_pointer_ = v_pointer_ + 1;


      END WHILE;

    END IF;


    #Rate Table insert rates
    SET v_pointer_ = 1;
    SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_rate_tables_ WHERE FIND_IN_SET(RateTableID , p_source_rate_tables) > 0);

    IF v_rowCount_ > 0 THEN

      WHILE v_pointer_ <= v_rowCount_
      DO

        SET @RateTableID = (SELECT RateTableID FROM tmp_rate_tables_ WHERE RowID = v_pointer_);
        SET @RateTableName = (SELECT RateTableName FROM tmp_rate_tables_ WHERE RowID = v_pointer_);

        IF ( FIND_IN_SET(@RateTableID , p_destination_rate_tables) > 0  ) THEN

          SET @ColumnName = concat('`Source:RateTable <br>', @RateTableName ,'`');

          SET @stm1 = CONCAT('ALTER   TABLE `tmp_final_compare` ADD COLUMN ', @ColumnName , ' VARCHAR(100) NULL DEFAULT NULL');

          PREPARE stmt1 FROM @stm1;
          EXECUTE stmt1;
          DEALLOCATE PREPARE stmt1;

          SET @stm2 = CONCAT('UPDATE `tmp_final_compare` tmp  INNER JOIN tmp_RateTableRate_ vr on vr.Code = tmp.Code set ', @ColumnName , ' =  IFNULL(concat(vr.Rate,"<br>",vr.EffectiveDate),"") WHERE vr.RateTableID = ', @RateTableID , ' ;');

          PREPARE stmt2 FROM @stm2;
          EXECUTE stmt2;
          DEALLOCATE PREPARE stmt2;

          SET @stm2 = CONCAT('UPDATE `tmp_final_compare`  set ', @ColumnName , ' =  "" where  ', @ColumnName , ' is null;');

          PREPARE stmt2 FROM @stm2;
          EXECUTE stmt2;
          DEALLOCATE PREPARE stmt2;

          INSERT INTO tmp_dynamic_columns_  values ( @ColumnName );

        END IF;

        SET v_pointer_ = v_pointer_ + 1;


      END WHILE;

    END IF;

    -- ##################Destination#######################################################

    #destination vendor insert rates
    SET v_pointer_ = 1;
    SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_vendors_ WHERE  FIND_IN_SET(AccountID , p_destination_vendors) > 0);

    IF v_rowCount_ > 0 THEN

      WHILE v_pointer_ <= v_rowCount_
      DO

        SET @AccountID = (SELECT AccountID FROM tmp_vendors_ WHERE RowID = v_pointer_);
        SET @AccountName = (SELECT AccountName FROM tmp_vendors_ WHERE RowID = v_pointer_);

        IF ( FIND_IN_SET(@AccountID , p_destination_vendors) > 0  ) THEN

          SET @ColumnName = concat('`Destination:VendorRate <br>', @AccountName ,'`');

          SET @stm1 = CONCAT('ALTER   TABLE `tmp_final_compare` ADD COLUMN ', @ColumnName , ' VARCHAR(100) NULL DEFAULT NULL');

          PREPARE stmt1 FROM @stm1;
          EXECUTE stmt1;
          DEALLOCATE PREPARE stmt1;

          SET @stm2 = CONCAT('UPDATE `tmp_final_compare` tmp  INNER JOIN tmp_VendorRate_ vr on vr.Code = tmp.Code set ', @ColumnName , ' =  IFNULL(concat(vr.Rate,"<br>",vr.EffectiveDate),"") WHERE vr.AccountID = ', @AccountID , ' ;');

          PREPARE stmt2 FROM @stm2;
          EXECUTE stmt2;
          DEALLOCATE PREPARE stmt2;

          SET @stm2 = CONCAT('UPDATE `tmp_final_compare`  set ', @ColumnName , ' =  "" where  ', @ColumnName , ' is null;');

          PREPARE stmt2 FROM @stm2;
          EXECUTE stmt2;
          DEALLOCATE PREPARE stmt2;

          INSERT INTO tmp_dynamic_columns_  values ( @ColumnName );

        END IF;


        SET v_pointer_ = v_pointer_ + 1;


      END WHILE;

    END IF;

    #destination customer insert rates
    SET v_pointer_ = 1;
    SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_customers_ WHERE  FIND_IN_SET(AccountID , p_destination_customers) > 0);

    IF v_rowCount_ > 0 THEN

      WHILE v_pointer_ <= v_rowCount_
      DO

        SET @AccountID = (SELECT AccountID FROM tmp_customers_ WHERE RowID = v_pointer_);
        SET @AccountName = (SELECT AccountName FROM tmp_customers_ WHERE RowID = v_pointer_);

        IF ( FIND_IN_SET(@AccountID , p_destination_customers) > 0  ) THEN

          SET @ColumnName = concat('`Destination:CustomerRate <br>', @AccountName ,'`');

          SET @stm1 = CONCAT('ALTER   TABLE `tmp_final_compare` ADD COLUMN ', @ColumnName , ' VARCHAR(100) NULL DEFAULT NULL');


          PREPARE stmt1 FROM @stm1;
          EXECUTE stmt1;
          DEALLOCATE PREPARE stmt1;

          SET @stm2 = CONCAT('UPDATE `tmp_final_compare` tmp  INNER JOIN tmp_CustomerRate_ vr on vr.Code = tmp.Code set ', @ColumnName , ' =  IFNULL(concat(vr.Rate,"<br>",vr.EffectiveDate),"") WHERE vr.AccountID = ', @AccountID , ' ;');

          PREPARE stmt2 FROM @stm2;
          EXECUTE stmt2;
          DEALLOCATE PREPARE stmt2;

          SET @stm2 = CONCAT('UPDATE `tmp_final_compare`  set ', @ColumnName , ' =  "" where  ', @ColumnName , ' is null;');

          PREPARE stmt2 FROM @stm2;
          EXECUTE stmt2;
          DEALLOCATE PREPARE stmt2;

          INSERT INTO tmp_dynamic_columns_  values ( @ColumnName );

        END IF;

        SET v_pointer_ = v_pointer_ + 1;


      END WHILE;

    END IF;


    #Rate Table insert rates
    SET v_pointer_ = 1;
    SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_rate_tables_ WHERE FIND_IN_SET(RateTableID , p_destination_rate_tables) > 0);

    IF v_rowCount_ > 0 THEN

      WHILE v_pointer_ <= v_rowCount_
      DO

        SET @RateTableID = (SELECT RateTableID FROM tmp_rate_tables_ WHERE RowID = v_pointer_);
        SET @RateTableName = (SELECT RateTableName FROM tmp_rate_tables_ WHERE RowID = v_pointer_);

        IF ( FIND_IN_SET(@RateTableID , p_destination_rate_tables) > 0  ) THEN

          SET @ColumnName = concat('`Destination:RateTable <br>', @RateTableName ,'`');

          SET @stm1 = CONCAT('ALTER   TABLE `tmp_final_compare` ADD COLUMN ', @ColumnName , ' VARCHAR(100) NULL DEFAULT NULL');

          PREPARE stmt1 FROM @stm1;
          EXECUTE stmt1;
          DEALLOCATE PREPARE stmt1;

          SET @stm2 = CONCAT('UPDATE `tmp_final_compare` tmp  INNER JOIN tmp_RateTableRate_ vr on vr.Code = tmp.Code set ', @ColumnName , ' =  IFNULL(concat(vr.Rate,"<br>",vr.EffectiveDate),"") WHERE vr.RateTableID = ', @RateTableID , ' ;');

          PREPARE stmt2 FROM @stm2;
          EXECUTE stmt2;
          DEALLOCATE PREPARE stmt2;

          SET @stm2 = CONCAT('UPDATE `tmp_final_compare`  set ', @ColumnName , ' =  "" where  ', @ColumnName , ' is null;');

          PREPARE stmt2 FROM @stm2;
          EXECUTE stmt2;
          DEALLOCATE PREPARE stmt2;

          INSERT INTO tmp_dynamic_columns_  values ( @ColumnName );

        END IF;

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

      select GROUP_CONCAT( concat(' max(' , ColumnName , ') as ' , ColumnName ) ) INTO @maxColumnNames from tmp_dynamic_columns_;

    ELSE

      select GROUP_CONCAT(ColumnName) INTO @ColumnNames from tmp_dynamic_columns_;

    END IF;


    IF p_isExport = 0 THEN

      IF p_groupby = 'description' THEN

        SET @stm2 = CONCAT('select max(Description) as Destination , ',@maxColumnNames ,' from tmp_final_compare Group by  Description  order by Description LIMIT  ', p_RowspPage , ' OFFSET ' , v_OffSet_ , '');

        PREPARE stmt2 FROM @stm2;
        EXECUTE stmt2;
        DEALLOCATE PREPARE stmt2;

        SELECT count(*) as totalcount from  (select count(Description) FROM tmp_final_compare Group by Description)tmp;

      ELSE

        SET @stm2 = CONCAT('select concat( Code , " : " , Description ) as Destination , ', @ColumnNames,' from tmp_final_compare order by Code LIMIT  ', p_RowspPage , ' OFFSET ' , v_OffSet_ , '');
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

        SET @stm2 = CONCAT('select concat( Code , " : " , Description ) as Destination , ', @ColumnNames,' from tmp_final_compare order by Code');
        PREPARE stmt2 FROM @stm2;
        EXECUTE stmt2;
        DEALLOCATE PREPARE stmt2;


      END IF;


    END IF;


    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;



  END