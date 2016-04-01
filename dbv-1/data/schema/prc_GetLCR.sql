CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetLCR`(IN `p_companyid` INT, IN `p_trunkID` INT, IN `p_codedeckID` INT, IN `p_contryID` INT, IN `p_code` VARCHAR(50), IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_SortOrder` VARCHAR(50), IN `p_Preference` INT, IN `p_isExport` INT)
BEGIN
    DECLARE v_OffSet_ int;
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
                
    SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
    
  Call fnVendorCurrentRates(p_companyid,p_codedeckID,p_trunkID,p_contryID, p_code,'0');
  DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
  CREATE TEMPORARY TABLE tmp_VendorRate_ (      
     AccountId INT ,
     AccountName VARCHAR(100) ,
     Code VARCHAR(50) ,
     Rate DECIMAL(18,6) ,
     EffectiveDate DATETIME ,
     Description VARCHAR(255),
     rankname INT
   );
        
               
       
    INSERT  INTO tmp_VendorRate_
                SELECT  vr.AccountID ,
                        vr.AccountName ,
                        vr.Code ,
                        vr.Rate ,
                        vr.EffectiveDate ,
                        vr.Description,
                        vr.rankname
                FROM    ( 

                        SELECT 
                                    Description ,
                                    AccountID ,
                                    AccountName ,
                                    Code ,
                                    Rate ,
                                    EffectiveDate ,
                                    case when p_Preference = 1
                                    then preference_rank
                                    else
                                        rankname
                                    end as rankname
                                    
                                FROM (
                                                SELECT 
                                                Description ,
                                                AccountID ,
                                                AccountName ,
                                                Code ,
                                                Preference,
                                                Rate,
                                                EffectiveDate,
                                                preference_rank,
                                                @rank := CASE WHEN (@prev_Code2  = Code  AND @prev_Rate2 <  Rate) THEN @rank+1
                                                	   		  WHEN (@prev_Code2  = Code  AND @prev_Rate2 =  Rate) THEN @rank
                                                				  ELSE
																					1
																				  END
																				  AS rankname,
                                                @prev_Code2  := Code,
                                                @prev_Rate2  := Rate
                                                FROM    (SELECT 
                                                    Description ,
                                                    AccountID ,
                                                    AccountName ,
                                                    Code ,
                                                    Preference,
                                                    Rate,
                                                    EffectiveDate,
                                                    @preference_rank := CASE WHEN (@prev_Code  = Code AND @prev_Preference = Preference AND @prev_Rate2 <  Rate) THEN @preference_rank+1
					                                                	   		  WHEN (@prev_Code  = Code  AND @prev_Preference = Preference AND @prev_Rate2 =  Rate) THEN @preference_rank
               					                                 				  ELSE
																										1
																									  END as preference_rank,
                                                    @prev_Code  := Code,
                                                    @prev_Preference  := Preference,
                                                    @prev_Rate  := Rate
                                                    FROM 
                                                    (SELECT DISTINCT
                                                                r.Description ,
                                                                v.AccountId,v.TrunkID,v.RateId ,
                                                                a.AccountName ,
                                                                r.Code ,
                                                                vp.Preference,
                                                                v.Rate,/*(v.Rate * ifnull(v.Rate,1)) as Rate*/
                                                                v.EffectiveDate ,
                                                                @row_num := IF(@prev_AccountId=v.AccountId AND @prev_TrunkID=v.TrunkID AND @prev_RateId=v.RateId and @prev_EffectiveDate >= v.effectivedate ,@row_num+1,1) AS RowID,
                                                                @prev_AccountId  := v.AccountId,
                                                                @prev_TrunkID  := v.TrunkID,
                                                                @prev_RateId  := v.RateId,
                                                                @prev_EffectiveDate  := v.effectivedate
                                                      FROM      tblVendorRate AS v
                                                                INNER JOIN tblAccount a ON v.AccountId = a.AccountID
                                                                INNER JOIN tblRate r ON v.RateId = r.RateID and r.CodeDeckId = p_codedeckID
                                                                INNER JOIN  tmp_VendorCurrentRates_ AS e ON 
                                                                    v.AccountId = e.AccountID 
                                                                    AND v.TrunkID = e.TrunkID
                                                                    AND     v.RateId = e.RateId
                                                                    AND v.EffectiveDate = e.EffectiveDate
                                                                    AND v.Rate > 0
                                                             
                                                                LEFT JOIN tblVendorPreference vp 
                                                                    ON vp.AccountId = v.AccountId
                                                                    AND vp.TrunkID = v.TrunkID
                                                                    AND vp.RateId = v.RateId
                                                            ,(SELECT @row_num := 1) x,(SELECT @prev_AccountId := '') a,(SELECT @prev_TrunkID := '') b,(SELECT @prev_RateId := '') c,(SELECT @prev_EffectiveDate := '') d

                                                    ORDER BY v.AccountId,v.TrunkID,v.RateId,v.EffectiveDate DESC
                                                    
                                            )as preference,(SELECT @preference_rank := 1) x,(SELECT @prev_Code := '') a,(SELECT @prev_Preference := '') b,(SELECT @prev_Rate := '') c
                                            where preference.RowID = 1
                                            ORDER BY preference.Code ASC, preference.Preference desc, preference.Rate ASC
                                            
                                        )as rank ,(SELECT @rank := 1) x,(SELECT @prev_Code2 := '') d,(SELECT @prev_Rate2 := '') f
                                        ORDER BY rank.Code ASC, rank.Rate ASC
    
                                    ) tbl
                           
                        ) vr
                WHERE   vr.rankname <= 5 
               ORDER BY rankname; 

         IF p_isExport = 0
        THEN
        
          SELECT
            
        	 CONCAT(t.Code , ' : ' , ANY_VALUE(t.Description)) as Destination,
          GROUP_CONCAT(if(ANY_VALUE(rankname) = 1, CONCAT(ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `Position 1`,
          GROUP_CONCAT(if(ANY_VALUE(rankname) = 2, CONCAT(ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `Position 2`,
          GROUP_CONCAT(if(ANY_VALUE(rankname) = 3, CONCAT(ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `Position 3`,
          GROUP_CONCAT(if(ANY_VALUE(rankname) = 4, CONCAT(ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `Position 4`,
          GROUP_CONCAT(if(ANY_VALUE(rankname) = 5, CONCAT(ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `Position 5`
        FROM      tmp_VendorRate_ t
          GROUP BY  t.Code
          ORDER BY  CASE WHEN (p_SortOrder = 'DESC') THEN `Destination` END DESC,
                       CASE WHEN (p_SortOrder = 'ASC') THEN `Destination` END ASC
                        
          LIMIT p_RowspPage OFFSET v_OffSet_;     

         select count(*)as totalcount FROM(SELECT code
         from tmp_VendorRate_ 
         GROUP by Code)tbl;
    
    END IF;
    
    
    IF p_isExport = 1
    THEN

        SELECT  
        	 CONCAT(t.Code , ' : ' , ANY_VALUE(t.Description)) as Destination,
          GROUP_CONCAT(if(ANY_VALUE(rankname) = 1, CONCAT(ANY_VALUE(t.Rate), "\r\n", ANY_VALUE(t.AccountName) , "\r\n", DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'), "\r\n"), NULL))AS `Position 1`,
          GROUP_CONCAT(if(ANY_VALUE(rankname) = 2, CONCAT(ANY_VALUE(t.Rate), "\r\n", ANY_VALUE(t.AccountName), "\r\n", DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),  "\r\n"), NULL))AS `Position 2`,
          GROUP_CONCAT(if(ANY_VALUE(rankname) = 3, CONCAT(ANY_VALUE(t.Rate), "\r\n", ANY_VALUE(t.AccountName) , "\r\n" , DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),"\r\n"), NULL))AS `Position 3`,
          GROUP_CONCAT(if(ANY_VALUE(rankname) = 4, CONCAT(ANY_VALUE(t.Rate), "\r\n", ANY_VALUE(t.AccountName) , "\r\n" , DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),"\r\n"), NULL))AS `Position 4`,
          GROUP_CONCAT(if(ANY_VALUE(rankname) = 5, CONCAT(ANY_VALUE(t.Rate), "\r\n", ANY_VALUE(t.AccountName) , "\r\n" , DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),"\r\n"), NULL))AS `Position 5`
        FROM tmp_VendorRate_  t
          GROUP BY  t.Code
          ORDER BY `Destination` ASC;
        
    END IF;
    
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END