CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSGenerateSippySheet`(IN `p_CustomerID` INT , IN `p_Trunks` varchar(200) )
BEGIN
	
	DECLARE v_codedeckid_ INT;
   DECLARE v_ratetableid_ INT;
   DECLARE v_RateTableAssignDate_ DATETIME;
   DECLARE v_NewA2ZAssign_ INT;
   
   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	 
        DROP TEMPORARY TABLE IF EXISTS tmp_RateTable_;
        CREATE TEMPORARY TABLE tmp_RateTable_ (
            RateId INT,
            Rate DECIMAL(18,6),
            EffectiveDate DATE,
            Interval1 INT,
            IntervalN INT,
            Trunk VARCHAR(50),
            IncludePrefix TINYINT,
            Prefix VARCHAR(50) 
        );
        
        DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRate_;
        CREATE TEMPORARY TABLE tmp_CustomerRate_ (
            RateId INT,
            Rate  DECIMAL(18,6),
            EffectiveDate DATE,
            Interval1 INT,
            IntervalN INT,
            Trunk VARCHAR(50),
            IncludePrefix TINYINT,
            Prefix VARCHAR(50) 
        );
        DROP TEMPORARY TABLE IF EXISTS tmp_tbltblRate_;
        CREATE TEMPORARY TABLE tmp_tbltblRate_ (
            RateId INT 
        );
        
        SELECT
            CodeDeckId,
            RateTableID,
            RateTableAssignDate INTO v_codedeckid_, v_ratetableid_, v_RateTableAssignDate_
        FROM tblCustomerTrunk
        WHERE 
        FIND_IN_SET(tblCustomerTrunk.TrunkID,p_Trunks)!= 0  
        AND tblCustomerTrunk.AccountID = p_CustomerID
        AND tblCustomerTrunk.Status = 1
		  LIMIT 1;

        SELECT case when v_RateTableAssignDate_ > MAX(EffectiveDate) THEN 1 ELSE 0  END INTO v_NewA2ZAssign_  FROM (
	 	SELECT *,  
		 @row_num := IF(@prev_RateID=tblRateTableRate.RateID and @prev_effectivedate >= tblRateTableRate.effectivedate ,@row_num+1,1) AS RowID,
		 @prev_RateID  := tblRateTableRate.RateID,
		 @prev_effectivedate  := tblRateTableRate.effectivedate
		FROM 
		tblRateTableRate,(SELECT @row_num := 1) x,(SELECT @prev_RateID := '') y,(SELECT @prev_effectivedate := '') z
		WHERE RateTableId = v_ratetableid_ AND EffectiveDate <= NOW()  
		ORDER BY tblRateTableRate.RateTableId,tblRateTableRate.RateID,tblRateTableRate.effectivedate DESC
	 )tbl WHERE RowID =1;
        
        INSERT INTO tmp_RateTable_
        SELECT  RateId ,
                Rate ,
                EffectiveDate ,
                Interval1,
                IntervalN,
                Trunk ,
                IncludePrefix ,
                Prefix
                
        FROM    (               
                
                SELECT  RateId ,
                        tblRateTableRate.Rate ,
                        CASE WHEN v_NewA2ZAssign_ = 1 THEN
                            v_RateTableAssignDate_  
                        ELSE
                            tblRateTableRate.EffectiveDate
                        END  AS EffectiveDate,
                        tblRateTableRate.Interval1,
                        tblRateTableRate.IntervalN,
                        tblTrunk.Trunk ,
                        tblCustomerTrunk.IncludePrefix ,
                        tblTrunk.Prefix,
                        @row_num := IF(@prev_RateTableId  = tblRateTableRate.RateTableId AND @prev_RateID=tblRateTableRate.RateID and @prev_effectivedate >= tblRateTableRate.effectivedate ,@row_num+1,1) AS RowID,
 				 				@prev_RateID  := tblRateTableRate.RateID,
							   @prev_effectivedate  := tblRateTableRate.effectivedate,
							   @prev_RateTableId  := tblRateTableRate.RateTableId
                FROM    tblAccount
                        JOIN tblCustomerTrunk ON tblCustomerTrunk.AccountId = tblAccount.AccountID
                        JOIN tblRateTable ON tblCustomerTrunk.RateTableId = tblRateTable.RateTableId
                        JOIN tblRateTableRate ON tblRateTableRate.RateTableId = tblRateTable.RateTableId
                        JOIN tblTrunk ON tblTrunk.TrunkId = tblCustomerTrunk.TrunkId
                        ,(SELECT @row_num := 1) x,(SELECT @prev_RateID := '') y,(SELECT @prev_effectivedate := '') z,(SELECT @prev_RateTableId := '') v
                WHERE   tblAccount.AccountID = p_CustomerID
                        AND tblRateTableRate.Rate > 0
                        And FIND_IN_SET(tblTrunk.TrunkID,p_Trunks)!= 0  
                        AND ( EffectiveDate <= now() or ( v_NewA2ZAssign_ = 1 AND EffectiveDate >= NOW()) )
               ORDER BY tblRateTableRate.RateTableId,tblRateTableRate.RateID,tblRateTableRate.effectivedate DESC
                )as TBL
        WHERE RowID = 1 ;
    
        INSERT INTO tmp_CustomerRate_
        SELECT  RateId ,
                Rate ,
                EffectiveDate ,
                Interval1,
                IntervalN,
                Trunk ,
                IncludePrefix ,
                Prefix
        FROM   (                
                SELECT  RateId ,
                        tblCustomerRate.Rate ,
                        EffectiveDate ,
                        tblCustomerRate.Interval1,
                        tblCustomerRate.IntervalN,
                        tblTrunk.Trunk ,
                        tblCustomerTrunk.IncludePrefix ,
                        CASE  WHEN t.Prefix is not null
                        THEN
                            t.Prefix
                        ELSE
                            tblTrunk.Prefix
                        END AS Prefix,
                        @row_num := IF(@prev_TrunkId  = tblCustomerRate.TrunkId AND @prev_RateID=tblCustomerRate.RateID and @prev_effectivedate >= tblCustomerRate.effectivedate ,@row_num+1,1) AS RowID,
 				 				@prev_RateID  := tblCustomerRate.RateID,
							   @prev_effectivedate  := tblCustomerRate.effectivedate,
							   @prev_TrunkId  := tblCustomerRate.TrunkId
                FROM    tblAccount
                        JOIN tblCustomerRate ON tblAccount.AccountID = tblCustomerRate.CustomerID
                        JOIN tblTrunk ON tblTrunk.TrunkId = tblCustomerRate.TrunkId
                        JOIN tblCustomerTrunk ON tblCustomerTrunk.Accountid = tblAccount.AccountID
                            AND tblCustomerTrunk.TrunkId = tblTrunk.TrunkId
                        LEFT JOIN tblCustomerTrunk ct ON ct.AccountId = tblCustomerRate.CustomerID and ct.TrunkID =tblCustomerRate.RoutinePlan AND ct.RoutinePlanStatus = 1
                        LEFT JOIN tblTrunk t ON t.TrunkID = tblCustomerRate.RoutinePlan
                        ,(SELECT @row_num := 1) x,(SELECT @prev_RateID := '') y,(SELECT @prev_effectivedate := '') z,(SELECT @prev_TrunkId := '') v
                WHERE   tblAccount.AccountID = p_CustomerID
                        AND tblCustomerRate.Rate > 0
                       And FIND_IN_SET(tblTrunk.TrunkID,p_Trunks)!= 0  
                       AND EffectiveDate <= now()
               ORDER BY tblCustomerRate.CustomerId,tblCustomerRate.TrunkId,tblCustomerRate.RateID,tblCustomerRate.effectivedate DESC
                )as TBL2 
        WHERE RowID = 1;

			INSERT INTO tmp_tbltblRate_
			SELECT    RateID
			FROM      tmp_RateTable_
         UNION
         SELECT    RateID
         FROM      tmp_CustomerRate_;
    
            
        SELECT 
                'A' as `Action [A|D|U|S|SA` ,
                '' as id,
						Concat(CASE WHEN cr.RateID IS NULL
                       THEN IFNULL(rt.Prefix, '')
                       ELSE  IFNULL(cr.Prefix, '')
                   END , Code) as Prefix
                ,
                Description as COUNTRY ,
                 CASE WHEN cr.RateID IS NOT NULL 
                                    THEN
                                    cr.Interval1 
                                ELSE
                                    rt.Interval1 
                                END as `Interval 1`,
                CASE WHEN cr.RateID IS NOT NULL 
                                    THEN
                                    cr.IntervalN 
                                ELSE
                                    rt.IntervalN
                                END as `Interval N`,
                
                CASE WHEN cr.RateID IS NULL
                                 THEN CASE WHEN rt.rate < rt.Rate
                                                AND rt.EffectiveDate  > NOW()
                                           THEN rt.rate
                                           ELSE rt.Rate
                                      END
                                 ELSE CASE WHEN cr.rate < cr.Rate
                                                AND cr.EffectiveDate  > NOW() 
                                           THEN cr.rate
                                           ELSE cr.Rate
                                      END
                            END as `Price 1`,
                CASE WHEN cr.RateID IS NULL
                                 THEN CASE WHEN rt.rate < rt.Rate
                                                AND rt.EffectiveDate  > NOW()
                                           THEN rt.rate
                                           ELSE rt.Rate
                                      END
                                 ELSE CASE WHEN cr.rate < cr.Rate
                                                AND cr.EffectiveDate  > NOW() 
                                           THEN cr.rate
                                           ELSE cr.Rate
                                      END
                            END as `Price N` ,
                0  as Forbidden,
                0 as `Grace Period`,
                'NOW' as `Activation Date`,
                '' as `Expiration Date` 
        FROM    tblRate
                LEFT JOIN tmp_RateTable_ rt ON rt.RateID = tblRate.RateID
                LEFT JOIN tmp_CustomerRate_ cr ON cr.RateID = tblRate.RateID  
        WHERE tblRate.RateID in (SELECT RateID FROM tmp_tbltblRate_)	
        ORDER BY Prefix;
        
        SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END