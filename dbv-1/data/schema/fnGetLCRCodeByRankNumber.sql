CREATE DEFINER=`root`@`localhost` PROCEDURE `fnGetLCRCodeByRankNumber`(IN `p_companyid` INT, IN `p_codedeckID` INT, IN `p_CurrencyID` INT, IN `p_trunkID` INT, IN `p_code` VARCHAR(50), IN `p_Preference` INT, IN `p_ranknumber` INT,  IN `p_IncludeAccountIDs` TEXT  , IN `p_ExcludeAccountIDs` TEXT)
BEGIN

-- Return All record with converted currency.
CALL fnVendorCurrentRates(p_companyid, p_codedeckID, p_trunkID, p_CurrencyID, p_code, p_IncludeAccountIDs);

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

IF ( SELECT
    COUNT(*)
  FROM tmp_VendorCurrentRates_) > 0 THEN       
       
  DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateByRank_stage_;
  CREATE TEMPORARY TABLE tmp_VendorRateByRank_stage_ (
     AccountId INT ,
     AccountName VARCHAR(100) ,
     Code VARCHAR(50) ,
     Rate DECIMAL(18,6) ,
     ConnectionFee DECIMAL(18,6) ,
     EffectiveDate DATETIME ,
     Description VARCHAR(255),
     TrunkID INT ,
     RateId INT ,
     Preference INT , 
     RowID INT
   );

-- Sort by Latest Effective Date with RowID
INSERT IGNORE INTO tmp_VendorRateByRank_stage_
  SELECT
    AccountId,
    AccountName,
    Code,
    Rate,
    ConnectionFee,  
    EffectiveDate,
    Description,
    TrunkID,
    RateId,
    Preference,
    RowID
  FROM (SELECT DISTINCT
      v.AccountId,
      v.AccountName,
      v.Code,
      v.Rate,
      v.ConnectionFee,
      v.EffectiveDate,
      v.Description,
      v.TrunkID,
      v.RateId,
      vp.Preference,
      @row_num := IF(@prev_AccountId = v.AccountId AND @prev_TrunkID = v.TrunkID AND @prev_RateId = v.RateId AND @prev_EffectiveDate >= v.effectivedate, @row_num + 1, 1) AS RowID,
      @prev_AccountId := v.AccountId,
      @prev_TrunkID := v.TrunkID,
      @prev_RateId := v.RateId,
      @prev_EffectiveDate := v.effectivedate
    FROM tmp_VendorCurrentRates_ v
           LEFT JOIN tblVendorPreference vp
             ON vp.AccountId = v.AccountId
             AND vp.TrunkID = v.TrunkID
             AND vp.RateId = v.RateId,
         (SELECT
             @row_num := 1) x,
         (SELECT
             @prev_AccountId := '') a,
         (SELECT
             @prev_TrunkID := '') b,
         (SELECT
             @prev_RateId := '') c,
         (SELECT
             @prev_EffectiveDate := '') d
    WHERE p_ExcludeAccountIDs = ''
    OR (p_ExcludeAccountIDs != ''
    AND FIND_IN_SET(v.AccountId, p_ExcludeAccountIDs) = 0)
    ORDER BY v.AccountId, v.TrunkID, v.RateId, v.EffectiveDate DESC) tbl
  WHERE RowID = 1;

 

 DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates_;

 

IF p_Preference = 1 THEN

-- Sort by Preference 
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
      IFNULL(Preference, 5) AS Preference,
      @preference_rank := CASE WHEN (@prev_Code COLLATE utf8_unicode_ci = Code AND @prev_Preference > IFNULL(Preference, 5) /*AND @prev_Rate2 <  Rate*/) THEN @preference_rank + 1 
                               WHEN (@prev_Code COLLATE utf8_unicode_ci = Code AND @prev_Preference = IFNULL(Preference, 5) AND @prev_Rate < Rate) THEN @preference_rank + 1 
                               WHEN (@prev_Code COLLATE utf8_unicode_ci = Code AND @prev_Preference = IFNULL(Preference, 5) AND @prev_Rate = Rate) THEN @preference_rank 
                               ELSE 1 
                               END AS preference_rank,
      @prev_Code := Code,
      @prev_Preference := IFNULL(Preference, 5),
      @prev_Rate := Rate
    FROM tmp_VendorRateByRank_stage_ AS preference,
         (SELECT @preference_rank := 0 , @prev_Code := ''  , @prev_Preference := 5,  @prev_Rate := 0) x
    ORDER BY preference.Code ASC, preference.Preference DESC, preference.Rate ASC,preference.AccountId ASC
       
     ) tbl
  WHERE preference_rank <= p_ranknumber
  ORDER BY Code, preference_rank;

ELSE


-- Sort by Rate 
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
      @rank := CASE WHEN (@prev_Code COLLATE utf8_unicode_ci = Code AND @prev_Rate < Rate) THEN @rank + 1 
                    WHEN (@prev_Code COLLATE utf8_unicode_ci = Code AND @prev_Rate = Rate) THEN @rank 
                    ELSE 1 
                    END AS RateRank,
      @prev_Code := Code,
      @prev_Rate := Rate
    FROM tmp_VendorRateByRank_stage_ AS rank,
         (SELECT @rank := 0 , @prev_Code := '' , @prev_Rate := 0) f
    ORDER BY rank.Code ASC, rank.Rate,rank.AccountId ASC) tbl
  WHERE RateRank <= p_ranknumber
  ORDER BY Code, RateRank;

END IF;
END IF;     -- IF (select count(*) from tmp_VendorCurrentRates_) > 0 THEN

END