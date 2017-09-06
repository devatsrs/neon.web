CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSGenerateRateTable_old-25-4-2016`(IN `p_jobId` INT
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
	
     SET @@global.collation_connection='utf8_unicode_ci';
     SET @@local.collation_connection='utf8_unicode_ci';

     SET @@local.character_set_client='utf8';
     SET @@local.character_set_client='utf8';
     
 
	
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
        code VARCHAR(50)  ,
        RowNo INT,
        INDEX tmp_Raterules_code (`code`),
        INDEX tmp_Raterules_rateruleid (`rateruleid`),
        INDEX tmp_Raterules_RowNo (`RowNo`)
    	);
     	DROP TEMPORARY TABLE IF EXISTS tmp_Vendorrates_;
     	CREATE TEMPORARY TABLE tmp_Vendorrates_  (
        code VARCHAR(50)  ,
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
		RowCode VARCHAR(50)  ,
        code VARCHAR(50)  ,
        rate DECIMAL(18, 6),
        ConnectionFee DECIMAL(18, 6),
        FinalRankNumber int,
        INDEX tmp_Vendorrates_stage2__code (`RowCode`)
    	);
     DROP TEMPORARY TABLE IF EXISTS tmp_dupVRatesstage2_;
     	CREATE TEMPORARY TABLE tmp_dupVRatesstage2_  (
		RowCode VARCHAR(50)  ,
        FinalRankNumber int,
        INDEX tmp_dupVendorrates_stage2__code (`RowCode`)
    	);
     DROP TEMPORARY TABLE IF EXISTS tmp_Vendorrates_stage3_;
     CREATE TEMPORARY TABLE tmp_Vendorrates_stage3_  (
        RowCode VARCHAR(50)  ,
        rate DECIMAL(18, 6),
        ConnectionFee DECIMAL(18, 6),
        INDEX tmp_Vendorrates_stage2__code (`RowCode`)
    	);    	
    	
     	DROP TEMPORARY TABLE IF EXISTS tmp_code_;
     	CREATE TEMPORARY TABLE tmp_code_  (
        code VARCHAR(50)  ,
        INDEX tmp_code_code (`code`)
    	);

     DROP TEMPORARY TABLE IF EXISTS tmp_all_code_;
  CREATE TEMPORARY TABLE tmp_all_code_ ( 
     RowCode  varchar(50)  ,
     Code  varchar(50)  ,
     RowNo int
     
   );

 

      DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage_;
       CREATE TEMPORARY TABLE tmp_VendorRate_stage_ (
          RowCode VARCHAR(50)  ,
          AccountId INT ,
          AccountName VARCHAR(100) ,
          Code VARCHAR(50)  ,
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
     Code VARCHAR(50)  ,
     Rate DECIMAL(18,6) ,
     ConnectionFee DECIMAL(18,6) ,
     EffectiveDate DATETIME ,
     Description VARCHAR(255),
     Preference INT,
     RowCode VARCHAR(50)   
   );
   DROP TEMPORARY TABLE IF EXISTS tmp_final_VendorRate_;
  CREATE TEMPORARY TABLE tmp_final_VendorRate_ (
     AccountId INT ,
     AccountName VARCHAR(100) ,
     Code VARCHAR(50)  ,
     Rate DECIMAL(18,6) ,
     ConnectionFee DECIMAL(18,6) ,
     EffectiveDate DATETIME ,
     Description VARCHAR(255),
     Preference INT,
     RowCode VARCHAR(50) ,
     FinalRankNumber int
   );
   
   DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates_(
			AccountId int,
            AccountName varchar(200),
			Code varchar(50) ,
			Description varchar(200) ,
			Rate DECIMAL(18,6) ,
               ConnectionFee DECIMAL(18,6) , 
			EffectiveDate date,
			TrunkID int,
			CountryID int,
			RateID int,
			Preference int,
			INDEX tmp_VendorCurrentRates_AccountId (`AccountId`,`TrunkID`,`RateId`,`EffectiveDate`)
	);
  DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateByRank_;
  CREATE TEMPORARY TABLE tmp_VendorRateByRank_ (      
     AccountId INT ,
     AccountName VARCHAR(100) ,
     Code VARCHAR(50) ,
     Rate DECIMAL(18,6) ,
     ConnectionFee DECIMAL(18,6) ,
     EffectiveDate DATETIME ,
     Description VARCHAR(255),
     Preference INT,
     rankname INT
   );

     	DROP TEMPORARY TABLE IF EXISTS tmp_SplitRowCodes_;
		CREATE TEMPORARY TABLE tmp_SplitRowCodes_  (
		  code VARCHAR(50) ,
		  INDEX tmp_Rates_code (`code`) 
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
	            ON tblAccount.AccountID = tblRateRuleSource.AccountId
			JOIN tblVendorTrunk
	                ON tblAccount.AccountId = tblVendorTrunk.AccountID
	                AND  tblVendorTrunk.TrunkID = v_trunk_
	                AND tblVendorTrunk.Status = 1
	        WHERE RateGeneratorId = p_RateGeneratorId;

	    SET v_pointer_ = 1;
	    SET v_rowCount_ = (SELECT COUNT(distinct Code ) FROM tmp_Raterules_);

		
		
		
		insert into tmp_code_
          SELECT  DISTINCT LEFT(f.Code, x.RowNo) as loopCode FROM (
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
	            JOIN tmp_Codedecks_ cd
	                ON tblRate.CodeDeckId = cd.CodeDeckId
	            JOIN tmp_Raterules_ rr
	                ON tblRate.code LIKE (REPLACE(rr.code,
	                '*', '%%'))
	          Order by tblRate.code 
          ) as f
          ON   x.RowNo   <= LENGTH(f.Code) 
          order by loopCode   desc;


          	
  
	
          
		SELECT CurrencyId INTO v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = v_CompanyId_;
		SET @IncludeAccountIds = (SELECT GROUP_CONCAT(AccountId) from tblRateRule rr inner join  tblRateRuleSource rrs on rr.RateRuleId = rrs.RateRuleId where rr.RateGeneratorId = p_RateGeneratorId ) ;
     
	
     
	 INSERT INTO tmp_VendorCurrentRates_ 
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
						INNER JOIN tblAccount   ON  tblAccount.CompanyID = v_CompanyId_ AND tblVendorRate.AccountId = tblAccount.AccountID 
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
						( EffectiveDate <= NOW() )
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
		 WHERE RowID = 1
		order by Code asc;
		
	
	
     
     
     
          
          
        insert into tmp_all_code_ (RowCode,Code,RowNo)
               select RowCode , loopCode,RowNo
               from (
                    select   RowCode , loopCode,
                    @RowNo := ( CASE WHEN (@prev_Code = tbl1.RowCode  ) THEN @RowNo + 1
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
                              INNER JOIN tmp_code_ AS f
                              ON  x.RowNo   <= LENGTH(f.Code) 
                              order by RowCode desc,  LENGTH(loopCode) DESC 
                    ) tbl1
                    , ( Select @RowNo := 0 ) x
                ) tbl order by RowCode desc,  LENGTH(loopCode) DESC ;


	IF v_Use_Preference_ = 1 THEN

	
	INSERT IGNORE INTO tmp_VendorRateByRank_
	  SELECT
		AccountID,
		AccountName,
		Code,
		Rate,
		ConnectionFee,
		EffectiveDate,
		Description,
		Preference,
		preference_rank
	  FROM (SELECT
		  AccountID,
		  AccountName,
		  Code,
		  Rate,
		  ConnectionFee,
		  EffectiveDate,
		  Description,
		  Preference,
		  @preference_rank := CASE WHEN (@prev_Code  = Code  AND @prev_Preference > Preference  ) THEN @preference_rank + 1 
								   WHEN (@prev_Code = Code  AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1 
								   WHEN (@prev_Code = Code  AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank 
								   ELSE 1 
								   END AS preference_rank,
		  @prev_Code := Code,
		  @prev_Preference := Preference,
		  @prev_Rate := Rate
		FROM tmp_VendorCurrentRates_ AS preference,
			 (SELECT @preference_rank := 0 , @prev_Code := ''  , @prev_Preference := 5,  @prev_Rate := 0) x
		ORDER BY preference.Code ASC, preference.Preference DESC, preference.Rate ASC,preference.AccountId ASC
		   
		 ) tbl
	  WHERE preference_rank <= v_RatePosition_
	  ORDER BY Code, preference_rank;

	ELSE


	
	INSERT IGNORE INTO tmp_VendorRateByRank_
	  SELECT
		AccountID,
		AccountName,
		Code,
		Rate,
		ConnectionFee,
		EffectiveDate,
		Description,
		Preference,
		RateRank
	  FROM (SELECT
		  AccountID,
		  AccountName,
		  Code,
		  Rate,
		  ConnectionFee,
		  EffectiveDate,
		  Description,
		  Preference,
		  @rank := CASE WHEN (@prev_Code = Code  AND @prev_Rate < Rate) THEN @rank + 1 
						WHEN (@prev_Code  = Code  AND @prev_Rate = Rate) THEN @rank 
						ELSE 1 
						END AS RateRank,
		  @prev_Code := Code,
		  @prev_Rate := Rate
		FROM tmp_VendorCurrentRates_ AS rank,
			 (SELECT @rank := 0 , @prev_Code := '' , @prev_Rate := 0) f
		ORDER BY rank.Code ASC, rank.Rate,rank.AccountId ASC) tbl
	  WHERE RateRank <= v_RatePosition_
	  ORDER BY Code, RateRank;

	END IF;	

     


                                 
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
                                             FROM tmp_VendorRateByRank_ v  
                                             left join  tmp_all_code_
                                   			SplitCode   on v.Code = SplitCode.Code 
                                            where  SplitCode.Code is not null and rankname <= v_RatePosition_ 
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
					AccountId ,
				     AccountName ,
				     Code ,
				     Rate ,
                         ConnectionFee,
				     EffectiveDate ,
				     Description ,
				     Preference,
				     RowCode,
   			         @rank := CASE WHEN ( @prev_RowCode = RowCode  AND @prev_Rate <  Rate ) THEN @rank+1
                                      WHEN ( @prev_RowCode = RowCode  AND @prev_Rate = Rate) THEN @rank 
						   ELSE 
						   1
						   END 
				  		  AS FinalRankNumber,
				  	@prev_RowCode  := RowCode,
					@prev_Rate  := Rate
					from tmp_VendorRate_ 
					,(SELECT @rank := 0 , @prev_RowCode := '' , @prev_Rate := 0  ) x 
				 	order by RowCode,Rate,AccountId ASC
				 
				) tbl1
				where 
				FinalRankNumber <= v_RatePosition_;
				 
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
     					AccountId ,
     				     AccountName ,
     				     Code ,
     				     Rate ,
                             ConnectionFee,
     				     EffectiveDate ,
     				     Description ,
     				     Preference,
     				     RowCode,
                              @preference_rank := CASE WHEN (@prev_Code = RowCode  AND @prev_Preference > Preference  )   THEN @preference_rank + 1 
                                                       WHEN (@prev_Code = RowCode  AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1 
                                                       WHEN (@prev_Code = RowCode  AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank 
                                                       ELSE 1 END AS FinalRankNumber,
                              @prev_Code := RowCode,
                              @prev_Preference := Preference,
                              @prev_Rate := Rate
					from tmp_VendorRate_ 
                          ,(SELECT @preference_rank := 0 , @prev_Code := ''  , @prev_Preference := 5,  @prev_Rate := 0) x
     			 	 order by RowCode ASC ,Preference DESC ,Rate ASC ,AccountId ASC
                         
				) tbl1
				where 
				FinalRankNumber <= v_RatePosition_;
		 
		 END IF;
			
		
		
	  WHILE v_pointer_ <= v_rowCount_
	    DO

             SET v_rateRuleId_ = (SELECT rateruleid FROM tmp_Raterules_ rr WHERE rr.RowNo = v_pointer_);
             SET @IncludeAccountIds = (SELECT GROUP_CONCAT(AccountId) from tblRateRuleSource where RateRuleId = v_rateRuleId_ ) ;

		INSERT INTO tmp_Rates2_ (code,rate,ConnectionFee)
		select  code,rate,ConnectionFee from tmp_Rates_;

		truncate   tmp_VRatesstage2_;
		
	               INSERT INTO tmp_VRatesstage2_         
     			SELECT
     				vr.RowCode,
     				vr.code,
     				vr.rate,
     				vr.ConnectionFee,
                         vr.FinalRankNumber
     			FROM tmp_final_VendorRate_ vr
                     JOIN tmp_Raterules_ rr ON rr.RateRuleId = v_rateRuleId_ and vr.RowCode LIKE (REPLACE(rr.code,'*', '%%'))
     			 left join tmp_Rates2_ rate on rate.Code = vr.RowCode 
     			WHERE vr.FinalRankNumber <= v_RatePosition_ 
                    AND rate.code is null
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

                    INSERT INTO tmp_Rates_
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

	           INSERT INTO tmp_Rates_
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
    	

END