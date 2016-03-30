CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSGenerateRateTable`(IN `p_jobId` INT
, IN `p_RateGeneratorId` INT
, IN `p_RateTableId` INT
, IN `p_rateTableName` VARCHAR(200) 
, IN `p_EffectiveDate` VARCHAR(10))
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
		  code VARCHAR(50) ,
		  rate DECIMAL(18, 6),
		  ConnectionFee DECIMAL(18, 6),
		  INDEX tmp_Rates_code (`code`) 
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Rates2_;
		CREATE TEMPORARY TABLE tmp_Rates2_  (
		  code VARCHAR(50) ,
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
        code VARCHAR(50),
        RowNo INT,
        INDEX tmp_Raterules_code (`code`),
        INDEX tmp_Raterules_rateruleid (`rateruleid`),
        INDEX tmp_Raterules_RowNo (`RowNo`)
    	);
     	DROP TEMPORARY TABLE IF EXISTS tmp_Vendorrates_;
     	CREATE TEMPORARY TABLE tmp_Vendorrates_  (
        code VARCHAR(50),
        rate DECIMAL(18, 6),
        ConnectionFee DECIMAL(18, 6),
        AccountId INT,
        RowNo INT,
        PreferenceRank INT,
        INDEX tmp_Vendorrates_code (`code`),
        INDEX tmp_Vendorrates_rate (`rate`)
    	);
     	DROP TEMPORARY TABLE IF EXISTS tmp_code_;
     	CREATE TEMPORARY TABLE tmp_code_  (
        code VARCHAR(50),
        rateid INT,
        countryid INT,
        INDEX tmp_code_code (`code`),
        INDEX tmp_code_rateid (`rateid`)
    	);
    	DROP TEMPORARY TABLE IF EXISTS tmp_tblVendorRate_;
     	CREATE TEMPORARY TABLE tmp_tblVendorRate_  (
			AccountId INT,
	      RateID INT,
	      Rate FLOAT,
	      ConnectionFee FLOAT,
	      TrunkId INT,
	      EffectiveDate DATE,
			RowID INT,
			INDEX tmp_tblVendorRate_AccountId (`AccountId`,`TrunkId`,`RateID`),
			INDEX tmp_tblVendorRate_EffectiveDate (`EffectiveDate`),
			INDEX tmp_tblVendorRate_Rate (`Rate`)
		);


    	SET  v_Use_Preference_ = (SELECT ifnull( JSON_EXTRACT(Options, '$.Use_Preference'),0)  FROM  tblJob WHERE JobID = p_jobId);
    	SELECT CurrencyID INTO v_CurrencyID_ FROM  tblRateGenerator WHERE RateGeneratorId = p_RateGeneratorId;

    	SELECT
        rateposition,
        companyid ,
        CodeDeckId,
        tblRateGenerator.TrunkID,
        tblRateGenerator.UseAverage  ,
        tblRateGenerator.RateGeneratorName INTO v_RatePosition_, v_CompanyId_, v_codedeckid_, v_trunk_, v_Average_, v_RateGeneratorName_
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
        ORDER BY rateruleid;

	     INSERT INTO tmp_Codedecks_
	        SELECT DISTINCT
	            tblVendorTrunk.CodeDeckId
	        FROM tblRateRule
	        INNER JOIN tblRateRuleSource
	            ON tblRateRule.RateRuleId = tblRateRuleSource.RateRuleId
	        INNER JOIN tblAccount
	            ON tblAccount.AccountID = tblRateRuleSource.AccountId
			JOIN tblVendorTrunk
	                ON tblAccount.AccountId = tblVendorTrunk.AccountID
	                AND  tblVendorTrunk.TrunkID = v_trunk_
	                AND tblVendorTrunk.Status = 1
	        WHERE RateGeneratorId = p_RateGeneratorId;

	    SET v_pointer_ = 1;
	    SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_Raterules_);

	    WHILE v_pointer_ <= v_rowCount_
	    DO
	        SET v_rateRuleId_ = (SELECT
	                rateruleid
	            FROM tmp_Raterules_ rr
	            WHERE rr.rowno = v_pointer_);

	        INSERT INTO tmp_code_
	            SELECT
	                tblRate.code,
	                tblRate.rateid,
	                tblRate.CountryID

	            FROM tblRate
	            JOIN tmp_Codedecks_ cd
	                ON tblRate.CodeDeckId = cd.CodeDeckId
	            JOIN tmp_Raterules_ rr
	                ON tblRate.code LIKE (REPLACE(rr.code,
	                '*', '%%'))
	            WHERE rr.rowno = v_pointer_;



				 INSERT INTO tmp_tblVendorRate_
	          SELECT AccountId,
	          RateID,
	          Rate,
	          ConnectionFee,
	          TrunkId,
	          EffectiveDate,
				 RowID
				 FROM(SELECT AccountId,
	          RateID,
	          Rate,
	          ConnectionFee,
	          TrunkId,
	          EffectiveDate,
				 @row_num := IF(@prev_RateId=tblVendorRate.RateId AND @prev_TrunkID=tblVendorRate.TrunkID AND @prev_AccountId=tblVendorRate.AccountId and @prev_EffectiveDate >= tblVendorRate.EffectiveDate ,@row_num+1,1) AS RowID,
				 @prev_RateId  := tblVendorRate.RateId,
				 @prev_TrunkID  := tblVendorRate.TrunkID,
				 @prev_AccountId  := tblVendorRate.AccountId,
				 @prev_EffectiveDate  := tblVendorRate.EffectiveDate
	          FROM tblVendorRate ,(SELECT @row_num := 1) x,(SELECT @prev_RateId := '') y,(SELECT @prev_TrunkID := '') z ,(SELECT @prev_AccountId := '') v ,(SELECT @prev_EffectiveDate := '') u
	          WHERE TrunkId = v_trunk_
				 ORDER BY tblVendorRate.AccountId , tblVendorRate.TrunkID, tblVendorRate.RateId, tblVendorRate.EffectiveDate DESC) TBLVENDOR;

	       INSERT INTO tmp_Vendorrates_   (code ,rate ,  ConnectionFee , AccountId ,RowNo ,PreferenceRank )
			  SELECT code,rate,ConnectionFee,AccountId,RowNo,PreferenceRank FROM(SELECT *,
				 @row_num := IF(@prev_Code2 = code AND  @prev_Preference >= Preference AND  @prev_Rate2 >= rate ,@row_num+1,1) AS PreferenceRank,
				 @prev_Code2  := Code,
				 @prev_Rate2  := rate,
				 @prev_Preference  := Preference
				 FROM (SELECT code,
				rate,
				ConnectionFee,
				AccountId,
				Preference,
				@row_num := IF(@prev_Code = code AND @prev_Rate >= rate ,@row_num+1,1) AS RowNo,
				@prev_Code  := Code,
				@prev_Rate  := rate
				from  (
				SELECT DISTINCT
	                c.code,
					 ( tmp_tblVendorRate_.rate / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = v_CompanyCurrencyID_ and  CompanyID = v_CompanyId_ ))
						* (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = v_CurrencyID_ and  CompanyID = v_CompanyId_ ) as  Rate,
	                tmp_tblVendorRate_.ConnectionFee,
	                tblAccount.AccountId,
					Preference

	            FROM tmp_Raterules_ rateRule
	            JOIN tblRateRuleSource
	                ON tblRateRuleSource.rateruleid = rateRule.rateruleid
	            JOIN tblAccount
	                ON tblAccount.AccountId = tblRateRuleSource.AccountId
	            JOIN tmp_tblVendorRate_
	                ON tblAccount.AccountId = tmp_tblVendorRate_.AccountId
	                AND tmp_tblVendorRate_.TrunkId = v_trunk_ and RowID = 1

	            JOIN tblVendorTrunk
	                ON tmp_tblVendorRate_.AccountId = tblVendorTrunk.AccountID
	                AND tmp_tblVendorRate_.TrunkId = tblVendorTrunk.TrunkID
	                AND tblVendorTrunk.Status = 1
	            JOIN tmp_code_ c
	                ON c.RateID = tmp_tblVendorRate_.RateId
	            LEFT JOIN tblVendorBlocking blockCountry
	                ON tblAccount.AccountId = blockCountry.AccountId
	                AND c.CountryID = blockCountry.CountryId
	                AND blockCountry.TrunkID = v_trunk_
	            LEFT JOIN tblVendorBlocking blockCode
	                ON tblAccount.AccountId = blockCode.AccountId
	                AND c.RateID = blockCode.RateId
	                AND blockCode.TrunkID = v_trunk_
	            LEFT JOIN tblVendorPreference
	                ON tblAccount.AccountId = tblVendorPreference.AccountId
	                AND c.RateID = tblVendorPreference.RateId
	                AND tblVendorPreference.TrunkID = v_trunk_
	            WHERE rateRule.rowno = v_pointer_
	            AND blockCode.VendorBlockingId IS NULL
	            AND blockCountry.VendorBlockingId IS NULL
	            AND CAST(tmp_tblVendorRate_.EffectiveDate AS DATE) <= CAST(NOW() AS DATE)
				) VNDRTBL ,(SELECT @row_num := 1) x,(SELECT @prev_Code := '') y,(SELECT @prev_Rate := '') z
				ORDER BY code , rate
				) VNDRTBL ,(SELECT @row_num := 1) x,(SELECT @prev_Code2 := '') y,(SELECT @prev_Rate2 := '') z,(SELECT @prev_Preference := '') v
			 ORDER BY code,Preference desc,rate) LASTTBL;

	       DELETE FROM tmp_tblVendorRate_;

			 INSERT INTO tmp_Rates2_ (code,rate,ConnectionFee)
			 select  code,rate,ConnectionFee from tmp_Rates_;
 
	        IF v_Average_ = 0 
	        THEN
	           
	           
	                 
	            INSERT INTO tmp_Rates_
	                SELECT
	                    code,
	                    (SELECT 
	                            CASE WHEN vRate.rate >= minrate AND
	                            vRate.rate <= maxrate THEN vRate.rate
	                                + (CASE WHEN addmargin LIKE '%p' THEN ((CAST(REPLACE(addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * vRate.rate) ELSE addmargin
	                                END) ELSE vRate.rate
	                            END
	                        FROM tblRateRuleMargin 
	                        WHERE rateruleid = v_rateRuleId_ LIMIT 1) as Rate,
	                ConnectionFee
	                FROM ( 
									 SELECT
			                    vr.code,
			                    vr.rate,
			                    vr.ConnectionFee,
			                    AccountId
			                FROM tmp_Vendorrates_ vr
			                left join tmp_Rates2_ rate on rate.code = vr.code 
			                WHERE 
			                (
			                    (v_Use_Preference_ =0 and RowNo <= v_RatePosition_ ) or 
			                    (v_Use_Preference_ =1 and PreferenceRank <= v_RatePosition_)
			                )
			                AND rate.code is null
			            --   AND IFNULL(vr.Rate, 0) > 0
						 ) vRate;
	         ELSE -- AVERAGE
	            
	           INSERT INTO tmp_Rates_
	                SELECT
	                    code,
	                     (SELECT 
	                            CASE WHEN vRate.rate >= minrate AND
	                            vRate.rate <= maxrate THEN vRate.rate
	                                + (CASE WHEN addmargin LIKE '%p' THEN ((CAST(REPLACE(addmargin, 'p', '') AS DECIMAL(18, 2)) / 100)
	                                    * vRate.rate) ELSE addmargin
	                                END) ELSE vRate.rate
	                            END
	                        FROM tblRateRuleMargin 
	                        WHERE rateruleid = v_rateRuleId_ LIMIT 1 ) as Rate,
	                    ConnectionFee
	                FROM ( 
						 	SELECT
	                    vr.code,
	                    AVG(vr.Rate) as Rate,
	                    AVG(vr.ConnectionFee) as ConnectionFee
	                FROM tmp_Vendorrates_ vr
	                left join tmp_Rates2_ rate on rate.code = vr.code 
	                WHERE 
	                (
	                    (v_Use_Preference_ =0 and RowNo <= v_RatePosition_ ) or 
	                    (v_Use_Preference_ =1 and PreferenceRank <= v_RatePosition_)
	                )
	                AND rate.code is null
	              --  AND IFNULL(vr.Rate, 0) > 0
	                GROUP BY vr.code 
						 ) vRate;
	
	        END IF;
	        DELETE FROM tmp_Vendorrates_;
	        DELETE FROM tmp_code_;
	
	        SET v_pointer_ = v_pointer_ + 1;
	    END WHILE;

		 
		 
	   -- DELETE FROM tmp_Rates_
	   -- WHERE IFNULL(Rate, 0) = 0;
		/*select * from tmp_Rates_;*/

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
	                p_RateTableId,
	                rate.Rate,
	                p_EffectiveDate,
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
    	
    	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    	

END