CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetLCRwithPrefix`(
	IN `p_companyid` INT,
	IN `p_trunkID` INT,
	IN `p_codedeckID` INT,
	IN `p_CurrencyID` INT,
	IN `p_code` VARCHAR(50),
	IN `p_Description` VARCHAR(250),
	IN `p_AccountIds` TEXT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_SortOrder` VARCHAR(50),
	IN `p_Preference` INT,
	IN `p_Position` INT,
	IN `p_isExport` INT
)
BEGIN

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
          RowCode VARCHAR(50) ,
          AccountId INT ,
          AccountName VARCHAR(100) ,
          Code VARCHAR(50) ,
          Rate DECIMAL(18,6) ,
          ConnectionFee DECIMAL(18,6) ,
          EffectiveDate DATETIME ,
          Description VARCHAR(255),
          Preference INT,
          MaxMatchRank int ,
          prev_prev_RowCode VARCHAR(50),
          prev_AccountID int
        ) 
  ;

     DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage2_;
       CREATE TEMPORARY TABLE tmp_VendorRate_stage2_ (
          RowCode VARCHAR(50) ,
          AccountId INT ,
          AccountName VARCHAR(100) ,
          Code VARCHAR(50) ,
          Rate DECIMAL(18,6) ,
          ConnectionFee DECIMAL(18,6) ,
          EffectiveDate DATETIME ,
          Description VARCHAR(255),
          Preference INT,
          MaxMatchRank int ,
          prev_prev_RowCode VARCHAR(50),
          prev_AccountID int
        ) 
  ;

  DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
  CREATE TEMPORARY TABLE tmp_VendorRate_ (
     AccountId INT ,
     AccountName VARCHAR(100) ,
     Code VARCHAR(50) ,
     Rate DECIMAL(18,6) ,
     ConnectionFee DECIMAL(18,6) ,
     EffectiveDate DATETIME ,
     Description VARCHAR(255),
     Preference INT,
     RowCode VARCHAR(50) 
   ) 
   ;
     
   DROP TEMPORARY TABLE IF EXISTS tmp_final_VendorRate_;
  CREATE TEMPORARY TABLE tmp_final_VendorRate_ (
     AccountId INT ,
     AccountName VARCHAR(100) ,
     Code VARCHAR(50) ,
     Rate DECIMAL(18,6) ,
     ConnectionFee DECIMAL(18,6) ,
     EffectiveDate DATETIME ,
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
			INDEX IX_Code (Code)
	) 
  ;

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
	) 
  ;

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
     rankname INT,
     INDEX IX_Code (Code,rankname)
   )
   ;

	SELECT CurrencyId INTO v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = p_companyid;
     
     INSERT INTO tmp_VendorCurrentRates1_ 
	  Select DISTINCT AccountId,AccountName,Code,Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference 
      FROM (
				SELECT distinct tblVendorRate.AccountId,tblAccount.AccountName, tblRate.Code, tblRate.Description, 
				 CASE WHEN  tblAccount.CurrencyId = p_CurrencyID
								THEN
									   tblVendorRate.Rate
					 WHEN  v_CompanyCurrencyID_ = p_CurrencyID  
							 THEN
								  (
								   ( tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = p_companyid ) )
								 )
							  ELSE 
							  (
										
								  (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = p_CurrencyID and  CompanyID = p_companyid )
									* (tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = p_companyid ))
							  )
							 END
								as  Rate,
								 ConnectionFee,
			  DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') AS EffectiveDate, 
			    tblVendorRate.TrunkID, tblRate.CountryID, tblRate.RateID,IFNULL(vp.Preference, 5) AS Preference 
			  FROM      tblVendorRate
					 Inner join tblVendorTrunk vt on vt.CompanyID = p_companyid AND vt.AccountID = tblVendorRate.AccountID and vt.Status =  1 and vt.TrunkID =  p_trunkID
							 
						INNER JOIN tblAccount   ON  tblAccount.CompanyID = p_companyid AND tblVendorRate.AccountId = tblAccount.AccountID 
						INNER JOIN tblRate ON tblRate.CompanyID = p_companyid   AND    tblVendorRate.RateId = tblRate.RateID
						
						
						
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
						( CHAR_LENGTH(RTRIM(p_code)) = 0 OR tblRate.Code LIKE REPLACE(p_code,'*', '%') )  
						AND (p_Description='' OR tblRate.Description LIKE REPLACE(p_Description,'*','%'))
						AND (p_AccountIds='' OR FIND_IN_SET(tblAccount.AccountID,p_AccountIds) != 0 )
						AND EffectiveDate <= NOW() 
						AND tblAccount.IsVendor = 1
						AND tblAccount.Status = 1
						AND tblAccount.CurrencyId is not NULL
						AND tblVendorRate.TrunkID = p_trunkID
						AND blockCode.RateId IS NULL
					   AND blockCountry.CountryId IS NULL
			
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

	IF p_Preference = 1 THEN
	
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
			  @preference_rank := CASE WHEN (@prev_Code     = Code AND @prev_Preference > Preference  ) THEN @preference_rank + 1 
									   WHEN (@prev_Code     = Code AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1 
									   WHEN (@prev_Code    = Code AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank 
									   ELSE 1 
									   END AS preference_rank,
			  @prev_Code := Code,
			  @prev_Preference := IFNULL(Preference, 5),
			  @prev_Rate := Rate
			FROM tmp_VendorCurrentRates_ AS preference,
				 (SELECT @preference_rank := 0 , @prev_Code := ''  , @prev_Preference := 5,  @prev_Rate := 0) x
			ORDER BY preference.Code ASC, preference.Preference DESC, preference.Rate ASC,preference.AccountId ASC
			   
			 ) tbl
		  WHERE preference_rank <= p_Position
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
			  @rank := CASE WHEN (@prev_Code    = Code AND @prev_Rate < Rate) THEN @rank + 1 
							WHEN (@prev_Code    = Code AND @prev_Rate = Rate) THEN @rank 
							ELSE 1 
							END AS RateRank,
			  @prev_Code := Code,
			  @prev_Rate := Rate
			FROM tmp_VendorCurrentRates_ AS rank,
				 (SELECT @rank := 0 , @prev_Code := '' , @prev_Rate := 0) f
			ORDER BY rank.Code ASC, rank.Rate,rank.AccountId ASC) tbl
		  WHERE RateRank <= p_Position
		  ORDER BY Code, RateRank;

	END IF;
	
          insert ignore into tmp_VendorRate_
          select
             distinct 
				 AccountId ,
				 AccountName ,
				 Code ,
				 Rate ,
                 ConnectionFee,                                                           
				 EffectiveDate ,
				 Description ,
				 Preference,
				 Code as RowCode
		   from tmp_VendorRateByRank_ 
 			  order by RowCode desc; 
                                   
	IF( p_Preference = 0 )
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
				 @rank := CASE WHEN ( @prev_RowCode     = RowCode AND @prev_Rate <  Rate ) THEN @rank+1
							   WHEN ( @prev_RowCode    = RowCode AND @prev_Rate = Rate ) THEN @rank 
					   ELSE 
					   1
					   END 
					  AS FinalRankNumber,
				@prev_RowCode  := RowCode,
				@prev_Rate  := Rate
				from tmp_VendorRate_ 
				,( SELECT @rank := 0 , @prev_RowCode := '' , @prev_Rate := 0 ) x 
				order by RowCode,Rate,AccountId ASC
			 
			) tbl1
			where 
			FinalRankNumber <= p_Position;
			 
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
						  @preference_rank := CASE WHEN (@prev_Code     = RowCode AND @prev_Preference > Preference  )   THEN @preference_rank + 1 
												   WHEN (@prev_Code     = RowCode AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1 
												   WHEN (@prev_Code    = RowCode AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank 
												   ELSE 1 END AS FinalRankNumber,
						  @prev_Code := RowCode,
						  @prev_Preference := Preference,
						  @prev_Rate := Rate
				from tmp_VendorRate_ 
					  ,(SELECT @preference_rank := 0 , @prev_Code := ''  , @prev_Preference := 5,  @prev_Rate := 0) x
				 order by RowCode ASC ,Preference DESC ,Rate ASC ,AccountId ASC
					 
			) tbl1
			where 
			FinalRankNumber <= p_Position;
	 
	 END IF;
          

    IF (p_Position = 5)
    THEN      

		IF (p_isExport = 0)
		THEN    	
			
			SELECT
	  	      CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 1`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 2`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 3`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 4`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 5`
			FROM tmp_final_VendorRate_  t
			  GROUP BY  RowCode   
			  ORDER BY RowCode ASC
			LIMIT p_RowspPage OFFSET v_OffSet_ ;
	   
			SELECT count(distinct RowCode) as totalcount from tmp_final_VendorRate_ where ( CHAR_LENGTH(RTRIM(p_code)) = 0  OR RowCode LIKE REPLACE(p_code,'*', '%') );
		   
	   ELSE 
			SELECT
			  CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 1`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 2`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 3`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 4`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 5`
			FROM tmp_final_VendorRate_  t
			  GROUP BY  RowCode   
			  ORDER BY RowCode ASC;
			
	   END IF; 
   
    END IF;
    
    IF (p_Position = 10)
    THEN       

		IF (p_isExport = 0)
		THEN    	
			
			SELECT
				CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,
				GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 1`,
				GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 2`,
				GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 3`,
				GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 4`,
				GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 5`,
				GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 6, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 6`,
				GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 7, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 7`,
				GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 8, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 8`,
				GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 9, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 9`,
				GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 10,CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 10`
			FROM tmp_final_VendorRate_  t
			  GROUP BY  RowCode   
			  ORDER BY RowCode ASC
			LIMIT p_RowspPage OFFSET v_OffSet_ ;
	   
			SELECT count(distinct RowCode) as totalcount from tmp_final_VendorRate_ where    ( CHAR_LENGTH(RTRIM(p_code)) = 0  OR RowCode LIKE REPLACE(p_code,'*', '%') );
		   
		ELSE 
			SELECT
			  CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 1`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 2`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 3`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 4`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 5`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 6, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 6`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 7, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 7`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 8, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 8`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 9, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 9`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 10,CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 10`
			FROM tmp_final_VendorRate_  t
			  GROUP BY  RowCode   
			  ORDER BY RowCode ASC;     	
		END IF; 
   
    END IF;


SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END