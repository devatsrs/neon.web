CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_CronJobGeneratePortaSheet`(IN `p_CustomerID` INT , IN `p_trunks` varchar(200) )
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
            ConnectionFee DECIMAL(18,6),
            Trunk VARCHAR(50) ,
            IncludePrefix TINYINT,
            RatePrefix VARCHAR(50),
            AreaPrefix VARCHAR(50),
            INDEX tmp_RateTable_RateId (`RateId`)  
        );
        DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRate_;
        CREATE TEMPORARY TABLE tmp_CustomerRate_ (
            RateId INT ,
            Rate DECIMAL(18,6),
            EffectiveDate DATE,
            Interval1 INT,
            IntervalN INT,
            ConnectionFee DECIMAL(18,6),
            Trunk VARCHAR(50),
            IncludePrefix TINYINT,
            RatePrefix VARCHAR(50),
            AreaPrefix VARCHAR(50),
            INDEX tmp_CustomerRate_RateId (`RateId`)
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
        FIND_IN_SET(tblCustomerTrunk.TrunkID,p_trunks)!= 0  
        AND tblCustomerTrunk.AccountID = p_CustomerID
        AND tblCustomerTrunk.Status = 1
		  LIMIT 1;
        
      SELECT case when v_RateTableAssignDate_ > MAX(EffectiveDate) THEN 1 ELSE 0  END INTO v_NewA2ZAssign_  FROM (
	 	SELECT MAX(EffectiveDate) as EffectiveDate
		FROM 
		tblRateTableRate
		WHERE RateTableId = v_ratetableid_ AND EffectiveDate <= NOW() 
		ORDER BY tblRateTableRate.RateTableId,tblRateTableRate.RateID,tblRateTableRate.effectivedate DESC
	 	)tbl;
	 
    
        INSERT INTO tmp_RateTable_
             SELECT  RateId ,
                     tblRateTableRate.Rate ,
                     CASE WHEN v_NewA2ZAssign_ = 1 THEN
                         v_RateTableAssignDate_  
                     ELSE
                         tblRateTableRate.EffectiveDate
                     END  AS EffectiveDate,
                     tblRateTableRate.Interval1,
                     tblRateTableRate.IntervalN,
                     ConnectionFee ,
                     tblTrunk.Trunk ,
                     tblCustomerTrunk.IncludePrefix ,
                     tblTrunk.RatePrefix ,
                     tblTrunk.AreaPrefix
             FROM    tblAccount
                     JOIN tblCustomerTrunk ON tblCustomerTrunk.AccountId = tblAccount.AccountID
                     JOIN tblRateTable ON tblCustomerTrunk.RateTableId = tblRateTable.RateTableId
                     JOIN tblRateTableRate ON tblRateTableRate.RateTableId = tblRateTable.RateTableId
                     JOIN tblTrunk ON tblTrunk.TrunkId = tblCustomerTrunk.TrunkId
             WHERE   tblAccount.AccountID = p_CustomerID
                    -- AND tblRateTableRate.Rate > 0
                     And FIND_IN_SET(tblTrunk.TrunkID,p_trunks)!= 0
                     AND ( EffectiveDate <= now() or ( v_NewA2ZAssign_ = 1 AND EffectiveDate >= NOW()) )
            ORDER BY tblRateTableRate.RateTableId,tblRateTableRate.RateID,tblRateTableRate.effectivedate DESC;
    
    		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTable4_ as (select * from tmp_RateTable_);	        
         DELETE n1 FROM tmp_RateTable_ n1, tmp_RateTable4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate 
	 	   AND n1.Trunk = n2.Trunk
		   AND  n1.RateId = n2.RateId;
    
        INSERT INTO tmp_CustomerRate_
                SELECT  RateId ,
                        tblCustomerRate.Rate ,
                        EffectiveDate ,
                        tblCustomerRate.Interval1,
                        tblCustomerRate.IntervalN,
                        ConnectionFee ,
                        tblTrunk.Trunk ,
                        tblCustomerTrunk.IncludePrefix ,
                        CASE  WHEN t.RatePrefix is not null 
                        THEN
                            t.RatePrefix
                        ELSE
                            tblTrunk.RatePrefix
                        END AS RatePrefix,
                        CASE  WHEN t.AreaPrefix is not null
                        THEN
                            t.AreaPrefix
                        ELSE
                            tblTrunk.AreaPrefix
                        END AS AreaPrefix
                FROM    tblAccount
                        JOIN tblCustomerRate ON tblAccount.AccountID = tblCustomerRate.CustomerID
                        JOIN tblTrunk ON tblTrunk.TrunkId = tblCustomerRate.TrunkId
                        JOIN tblCustomerTrunk ON tblCustomerTrunk.AccountId = tblAccount.AccountID
                            AND tblCustomerTrunk.TrunkId = tblTrunk.TrunkId
                        LEFT JOIN tblCustomerTrunk ct ON ct.AccountId = tblCustomerRate.CustomerID and ct.TrunkID =tblCustomerRate.RoutinePlan AND ct.RoutinePlanStatus = 1
                        LEFT JOIN tblTrunk t ON t.TrunkID = tblCustomerRate.RoutinePlan 
                        
                WHERE   tblAccount.AccountID = p_CustomerID
                        AND tblCustomerRate.Rate > 0 
                        And FIND_IN_SET(tblTrunk.TrunkID,p_trunks)!= 0
                        AND EffectiveDate <= now()
               ORDER BY tblCustomerRate.CustomerId,tblCustomerRate.TrunkId,tblCustomerRate.RateID,tblCustomerRate.effectivedate DESC;
               
			CREATE TEMPORARY TABLE IF NOT EXISTS tmp_CustomerRates4_ as (select * from tmp_CustomerRate_);	        
         DELETE n1 FROM tmp_CustomerRate_ n1, tmp_CustomerRates4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate 
	 	   AND n1.Trunk = n2.Trunk
		   AND  n1.RateId = n2.RateId;
    

    		INSERT INTO tmp_tbltblRate_
			SELECT    RateID
			FROM      tmp_RateTable_
         UNION
         SELECT    RateID
         FROM      tmp_CustomerRate_; 
            
        SELECT distinct 
                Code as `Destination` ,
                Description  as `Description`,
                CASE WHEN cr.RateID IS NOT NULL 
                                    THEN
                                    cr.Interval1 
                                ELSE
                                    rt.Interval1
                                END as `First Interval`,
                CASE WHEN cr.RateID IS NOT NULL 
                                    THEN
                                    cr.IntervalN 
                                ELSE
                                    rt.IntervalN
                                END as `Next Interval`,
                CASE WHEN cr.RateID IS NOT NULL 
                                    THEN
                                    Abs(cr.Rate) 
                                ELSE
                                    Abs(rt.Rate)
                                END as `First Price` ,
                CASE WHEN cr.RateID IS NOT NULL 
                                    THEN
                                    Abs(cr.Rate) 
                                ELSE
                                    Abs(rt.Rate)
                                END as `Next Price`,
               CASE WHEN cr.RateID IS NOT NULL 
                                    THEN
                                        DATE_FORMAT(cr.EffectiveDate ,'%d/%m/%Y')
                                    ELSE
                                        DATE_FORMAT(rt.EffectiveDate ,'%d/%m/%Y')
                                    END as  `Effective From`,
         CASE WHEN ( CASE WHEN cr.RateID IS NOT NULL 
                                    THEN
                                    cr.Rate
                                ELSE
                                    rt.Rate
                                 	END ) < 0 THEN 'Y' ELSE '' 
								END  'Payback Rate' ,
			 CASE WHEN cr.RateID IS NOT NULL  and cr.ConnectionFee > 0 THEN
									CONCAT('SEQ=', cr.ConnectionFee,'&int1x1@price1&intNxN@priceN')
								ELSE
									CASE WHEN rt.RateID IS NOT NULL  and rt.ConnectionFee > 0 THEN
										CONCAT('SEQ=', rt.ConnectionFee,'&int1x1@price1&intNxN@priceN')
										ELSE
										''
									END
								END as `Formula`
			
            
        FROM    tblRate
                LEFT JOIN tmp_RateTable_ rt ON rt.RateID = tblRate.RateID
                LEFT JOIN tmp_CustomerRate_ cr ON cr.RateID = tblRate.RateID
		 WHERE tblRate.RateID in (SELECT RateID FROM tmp_tbltblRate_);
		 
		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END