CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSGenerateRateSheet`(IN `p_CustomerID` INT, IN `p_Trunk` VARCHAR(100)
)
BEGIN 
    DECLARE v_trunkDescription_ VARCHAR(50);
    DECLARE v_lastRateSheetID_ INT ;
    DECLARE v_IncludePrefix_ TINYINT;
    DECLARE v_Prefix_ VARCHAR(50);
    DECLARE v_codedeckid_  INT;
    DECLARE v_ratetableid_ INT;
    DECLARE v_RateTableAssignDate_  DATETIME;
    DECLARE v_NewA2ZAssign_ INT;
    
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
   
    
       SELECT trunk INTO v_trunkDescription_
         FROM   tblTrunk 
         WHERE  TrunkID = p_Trunk; 
     
    DROP TEMPORARY TABLE IF EXISTS tmp_RateSheetRate_;
      CREATE TEMPORARY TABLE tmp_RateSheetRate_ 
        ( 
           RateID        INT, 
           Destination   VARCHAR(200), 
           Codes         VARCHAR(50), 
           Interval1     INT, 
           IntervalN     INT, 
           Rate          DECIMAL(18, 6), 
           `level`         VARCHAR(10), 
           `change`        VARCHAR(50), 
           EffectiveDate  DATE 
        );
    DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRates_;
    CREATE TEMPORARY TABLE tmp_CustomerRates_ (
        RateID INT,
        Interval1 INT,
        IntervalN  INT,
        Rate DECIMAL(18, 6),
        EffectiveDate DATE,
        LastModifiedDate DATETIME
    ); 
    DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
    CREATE TEMPORARY TABLE tmp_RateTableRate_ (
        RateID INT,
        Interval1 INT,
        IntervalN  INT,
        Rate DECIMAL(18, 6),
        EffectiveDate DATE,
        updated_at DATETIME
    ); 
    
      DROP TEMPORARY TABLE IF EXISTS tmp_RateSheetDetail_;
    CREATE TEMPORARY TABLE tmp_RateSheetDetail_ (
      ratesheetdetailsid int, 
        RateID int , 
         RateSheetID int, 
         Destination varchar(50), 
         Code varchar(50), 
         Rate DECIMAL(18, 6), 
         `change` varchar(50), 
         EffectiveDate Date 
    );
      SELECT RateSheetID INTO v_lastRateSheetID_
         FROM   tblRateSheet 
         WHERE  CustomerID = p_CustomerID 
                AND level = v_trunkDescription_ 
         ORDER  BY RateSheetID DESC LIMIT 1 ; 
         
      SELECT includeprefix INTO v_IncludePrefix_
         FROM   tblCustomerTrunk 
         WHERE  AccountID = p_CustomerID 
                AND TrunkID = p_Trunk; 
                
    SELECT prefix INTO v_Prefix_
         FROM   tblCustomerTrunk 
         WHERE  AccountID = p_CustomerID 
                AND TrunkID = p_Trunk; 


    SELECT
        CodeDeckId,
        RateTableID,
   RateTableAssignDate INTO v_codedeckid_, v_ratetableid_, v_RateTableAssignDate_
    FROM tblCustomerTrunk
    WHERE 
    tblCustomerTrunk.TrunkID = p_Trunk
    AND tblCustomerTrunk.AccountID = p_CustomerID
    AND tblCustomerTrunk.Status = 1;
    
    
  SELECT CASE WHEN v_RateTableAssignDate_ > MAX(EffectiveDate) THEN 1 ELSE 0  END INTO v_NewA2ZAssign_  FROM (
  SELECT  
     *,
     @row_num := IF(@prev_RateID=tblRateTableRate.RateID and @prev_effectivedate >= tblRateTableRate.EffectiveDate ,@row_num+1,1) AS RowID,
     @prev_RateID  := tblRateTableRate.RateID,
     @prev_effectivedate  := tblRateTableRate.EffectiveDate
    FROM 
    tblRateTableRate,(SELECT @row_num := 1) x,(SELECT @prev_RateID := '') y,(SELECT @prev_effectivedate := '') z
    WHERE RateTableId = v_ratetableid_ AND EffectiveDate <= NOW() 
    ORDER BY tblRateTableRate.RateTableId,tblRateTableRate.RateID,tblRateTableRate.EffectiveDate DESC
  )tbl WHERE RowID =1;

  INSERT INTO tmp_CustomerRates_
    select RateID,
       Interval1,
       IntervalN,
       Rate,
       EffectiveDate,
       LastModifiedDate
   from (
      SELECT    RateID, 
              Interval1, 
              IntervalN, 
              tblCustomerRate.Rate, 
              effectivedate, 
              lastmodifieddate,
              @row_num := IF(@prev_RateID=tblCustomerRate.RateID and @prev_effectivedate >= tblCustomerRate.EffectiveDate ,@row_num+1,1) AS RowID1,
             @prev_RateID  := tblCustomerRate.RateID,
             @prev_effectivedate  := tblCustomerRate.EffectiveDate   
      FROM   tblAccount 
         JOIN tblCustomerRate 
           ON tblAccount.AccountID = tblCustomerRate.CustomerID 
           ,(SELECT @row_num := 1) x,(SELECT @prev_RateID := '') y,(SELECT @prev_effectivedate := '') z
      WHERE  tblAccount.AccountID = p_CustomerID 
          AND tblCustomerRate.Rate > 0
         AND tblCustomerRate.TrunkID = p_Trunk 
         ORDER BY tblCustomerRate.CustomerID,tblCustomerRate.TrunkID,tblCustomerRate.RateID,tblCustomerRate.EffectiveDate DESC
      )tbl 
    where RowID1 = 1;


    INSERT INTO tmp_RateTableRate_
    select RateID,
       Interval1,
       IntervalN,
       Rate,
       EffectiveDate ,
       updated_at
   from (
      SELECT      tblRateTableRate.RateID, 
              tblRateTableRate.Interval1, 
              tblRateTableRate.IntervalN, 
              tblRateTableRate.Rate, 
              CASE WHEN v_NewA2ZAssign_ = 1   THEN
                v_RateTableAssignDate_
              ELSE 
                tblRateTableRate.EffectiveDate
              END as EffectiveDate , 
              tblRateTableRate.updated_at ,
              @row_num := IF(@prev_RateID=tblRateTableRate.RateID and @prev_effectivedate >= tblRateTableRate.EffectiveDate ,@row_num+1,1) AS RowID1,
             @prev_RateID  := tblRateTableRate.RateID,
             @prev_effectivedate  := tblRateTableRate.EffectiveDate

      FROM   tblAccount 
         JOIN tblCustomerTrunk 
           ON tblCustomerTrunk.AccountID = tblAccount.AccountID 
         JOIN tblRateTable 
           ON tblCustomerTrunk.ratetableid = tblRateTable.ratetableid 
         JOIN tblRateTableRate 
           ON tblRateTableRate.ratetableid = tblRateTable.ratetableid 
         LEFT JOIN tmp_CustomerRates_ trc1 
            ON trc1.RateID = tblRateTableRate.RateID 
         ,(SELECT @row_num := 1) x,(SELECT @prev_RateID := '') y,(SELECT @prev_effectivedate := '') z   
      WHERE  tblAccount.AccountID = p_CustomerID 
         AND tblRateTableRate.Rate > 0 
         AND tblCustomerTrunk.TrunkID = p_Trunk 
         AND (( tblRateTableRate.EffectiveDate <= Now() 
             AND ( ( trc1.RateID IS NULL )
                OR ( trc1.RateID IS NOT NULL 
                   AND tblRateTableRate.ratetablerateid IS NULL ) 
              ) ) 
          OR ( tblRateTableRate.EffectiveDate > Now() 
             AND ( ( trc1.RateID IS NULL ) 
                OR ( trc1.RateID IS NOT NULL 
                   AND tblRateTableRate.EffectiveDate < (1
                      ) ) ) ) )
      ORDER BY tblRateTableRate.RateID,tblRateTableRate.EffectiveDate desc
    )tbl
    where RowID1 = 1;

    INSERt INTO tmp_RateSheetDetail_
      SELECT ratesheetdetailsid, 
             RateID, 
             RateSheetID, 
             Destination, 
             Code, 
             Rate, 
             `change`, 
             effectivedate
      FROM   (SELECT ratesheetdetailsid, 
                     RateID, 
                     RateSheetID, 
                     Rate, 
                     Code, 
                     Destination, 
                     `change`, 
                     effectivedate,
              @row_num := IF(@prev_RateID=RateID and @prev_effectivedate >= effectivedate ,@row_num+1,1) AS RowID1,
               @prev_RateID  := RateID,
               @prev_effectivedate  := effectivedate 
              FROM   tblRateSheetDetails ,(SELECT @row_num := 1) x,(SELECT @prev_RateID := '') y,(SELECT @prev_effectivedate := '') z 
              WHERE  RateSheetID = v_lastRateSheetID_
          ORDER BY RateID,effectivedate desc
          ) AS tbl2 
      WHERE  tbl2.rowid1 = 1; 

      /* Create duplicate tmp_RateSheetDetail_*/
      DROP TABLE IF EXISTS tmp_CloneRateSheetDetail_ ; 
      CREATE TEMPORARY TABLE tmp_CloneRateSheetDetail_ LIKE tmp_RateSheetDetail_; 
      INSERT tmp_CloneRateSheetDetail_ SELECT * FROM tmp_RateSheetDetail_;


      INSERT INTO tmp_RateSheetRate_ 
      SELECT tbl.RateID, 
             description, 
             Code, 
             tbl.Interval1, 
             tbl.IntervalN, 
             tbl.Rate, 
             v_trunkDescription_, 
             'NEW' as `change`, 
             tbl.EffectiveDate 
      FROM   (SELECT tbl2.RateID, 
                     tbl2.Interval1, 
                     tbl2.IntervalN, 
                     tbl2.Rate, 
                     tbl2.EffectiveDate 
              FROM   (SELECT rt.RateID, 
                             rt.Interval1, 
                             rt.IntervalN, 
                             rt.Rate, 
                             rt.EffectiveDate,
                    @row_num := IF(@prev_RateID=rt.RateID and @prev_effectivedate >= rt.EffectiveDate ,@row_num+1,1) AS RowID1,
                     @prev_RateID  := rt.RateID,
                     @prev_effectivedate  := rt.EffectiveDate 
                      FROM   tmp_RateTableRate_ rt 
                             LEFT JOIN tblRateSheet 
                                    ON tblRateSheet.RateSheetID = 
                                       v_lastRateSheetID_ 
                             LEFT JOIN tmp_RateSheetDetail_ as  rsd 
                                    ON rsd.RateID = rt.RateID AND rsd.RateSheetID = v_lastRateSheetID_
                     ,(SELECT @row_num := 1) x,(SELECT @prev_RateID := '') y,(SELECT @prev_effectivedate := '') z 
                      WHERE  rsd.ratesheetdetailsid IS NULL  
                      ORDER BY rt.RateID,rt.EffectiveDate desc
               ) AS 
                     tbl2 
              WHERE  tbl2.rowid1 = 1 
              UNION 
              SELECT tbl3.RateID, 
                     tbl3.Interval1, 
                     tbl3.IntervalN, 
                     tbl3.Rate, 
                     tbl3.EffectiveDate 
              FROM   (SELECT trc2.RateID, 
                             trc2.Rate, 
                             trc2.Interval1, 
                             trc2.IntervalN, 
                             trc2.EffectiveDate, 
                             @row_num := IF(@prev_RateID=trc2.RateID and @prev_effectivedate >= trc2.EffectiveDate ,@row_num+1,1) AS RowID2,
                     @prev_RateID  := trc2.RateID,
                     @prev_effectivedate  := trc2.EffectiveDate
                            
                      FROM   tmp_CustomerRates_ trc2 
                             LEFT JOIN tblRateSheet 
                                    ON tblRateSheet.RateSheetID = 
                                       v_lastRateSheetID_ 
                             LEFT JOIN tmp_CloneRateSheetDetail_ as  rsd2 
                                    ON rsd2.RateID = trc2.RateID AND rsd2.RateSheetID = v_lastRateSheetID_
                      ,(SELECT @row_num := 1) x,(SELECT @prev_RateID := '') y,(SELECT @prev_effectivedate := '') z                 
                      WHERE  rsd2.ratesheetdetailsid IS NULL  
                ORDER BY trc2.RateID,trc2.EffectiveDate desc
                ) AS 
                     tbl3 
              WHERE  tbl3.rowid2 = 1) AS tbl 
             INNER JOIN tblRate 
                     ON tbl.RateID = tblRate.RateID;

      INSERT INTO tmp_RateSheetRate_ 
      SELECT tbl.RateID, 
             description, 
             Code, 
             tbl.Interval1, 
             tbl.IntervalN, 
             tbl.Rate, 
             v_trunkDescription_, 
             'NO CHANGE', 
             tbl.EffectiveDate 
      FROM   (SELECT tbl2.RateID, 
                     tbl2.Interval1, 
                     tbl2.IntervalN, 
                     tbl2.Rate, 
                     tbl2.EffectiveDate 
              FROM   (SELECT rt.RateID, 
                             rt.Interval1, 
                             rt.IntervalN, 
                             rt.Rate, 
                             rt.EffectiveDate, 
                             @row_num := IF(@prev_RateID=rt.RateID and @prev_effectivedate >= rt.EffectiveDate ,@row_num+1,1) AS RowID1,
                     @prev_RateID  := rt.RateID,
                     @prev_effectivedate  := rt.EffectiveDate 
                      FROM   tmp_RateTableRate_ rt 
                             INNER JOIN tblRateSheet 
                                     ON tblRateSheet.RateSheetID = 
                                        v_lastRateSheetID_ 
                             INNER JOIN tmp_RateSheetDetail_ as  rsd3 
                                     ON rsd3.RateID = rt.RateID AND rsd3.RateSheetID = v_lastRateSheetID_
                      ,(SELECT @row_num := 1) x,(SELECT @prev_RateID := '') y,(SELECT @prev_effectivedate := '') z                   
                      WHERE  (rt.updated_at < tblRateSheet.dategenerated 
                              OR rt.updated_at IS NULL)  
                       ORDER BY rt.RateID,rt.EffectiveDate desc
              ) AS tbl2 
              WHERE  tbl2.rowid1 = 1 
              UNION 
              SELECT tbl3.RateID, 
                     tbl3.Interval1, 
                     tbl3.IntervalN, 
                     tbl3.Rate, 
                     tbl3.EffectiveDate 
              FROM   (SELECT trc3.RateID, 
                             trc3.Interval1, 
                             trc3.IntervalN, 
                             trc3.Rate, 
                             trc3.EffectiveDate, 
                             @row_num := IF(@prev_RateID=trc3.RateID and @prev_effectivedate >= trc3.EffectiveDate ,@row_num+1,1) AS RowID2,
                     @prev_RateID := trc3.RateID,
                     @prev_effectivedate  := trc3.EffectiveDate
                            
                      FROM   tmp_CustomerRates_ trc3 
                             INNER JOIN tblRateSheet 
                                     ON tblRateSheet.RateSheetID = 
                                        v_lastRateSheetID_ 
                             INNER JOIN tmp_CloneRateSheetDetail_ as  rsd4 
                                     ON rsd4.RateID = trc3.RateID 
                                        AND rsd4.RateSheetID = v_lastRateSheetID_
                      ,(SELECT @row_num := 1) x,(SELECT @prev_RateID := '') y,(SELECT @prev_effectivedate := '') z 
                      WHERE  (trc3.LastModifiedDate < dategenerated 
                              OR trc3.LastModifiedDate IS NULL)  
                      ORDER BY trc3.RateID,trc3.EffectiveDate desc
              ) AS tbl3 
              WHERE  tbl3.rowid2 = 1) AS tbl 
             INNER JOIN tblRate 
                     ON tbl.RateID = tblRate.RateID; 

      INSERT INTO tmp_RateSheetRate_ 
      SELECT tbl.RateID, 
             description, 
             Code, 
             tbl.Interval1, 
             tbl.IntervalN, 
             tbl.Rate, 
             v_trunkDescription_, 
             tbl.`change`, 
             tbl.EffectiveDate 
      FROM   (SELECT rt.RateID, 
                     description, 
                     tblRate.Code, 
                     rt.Interval1, 
                     rt.IntervalN, 
                     rt.Rate, 
                     rsd5.Rate AS rate2, 
                     rt.EffectiveDate, 
                     CASE 
                                WHEN rsd5.Rate > rt.Rate 
                                     AND rsd5.Destination != 
                                         description THEN 
                                'NAME CHANGE & DECREASE' 
                                ELSE 
                                  CASE 
                                    WHEN rsd5.Rate > rt.Rate 
                                         AND rt.EffectiveDate <= Now() THEN 
                                    'DECREASE' 
                                    ELSE 
                                      CASE 
                                        WHEN ( rsd5.Rate > 
                                               rt.Rate 
                                               AND rt.EffectiveDate > Now() 
                                             ) 
                                      THEN 
                                        'PENDING DECREASE' 
                                        ELSE 
                                          CASE 
                                            WHEN ( rsd5.Rate < 
                                                   rt.Rate 
                                                   AND rt.EffectiveDate <= 
                                                       Now() ) 
                                          THEN 
                                            'INCREASE' 
                                            ELSE 
                                              CASE 
                                                WHEN ( rsd5.Rate 
                                                       < 
                                                       rt.Rate 
                                                       AND rt.EffectiveDate > 
                                                           Now() ) 
                                              THEN 
                                                'PENDING INCREASE' 
                                                ELSE 
                                                  CASE 
                                                    WHEN 
                                              rsd5.Destination != 
                                              description THEN 
                                                    'NAME CHANGE' 
                                                    ELSE 'NO CHANGE' 
                                                  END 
                                              END 
                                          END 
                                      END 
                                  END 
                              END as `Change` 
              FROM   tblRate 
                     INNER JOIN tmp_RateTableRate_ rt 
                             ON rt.RateID = tblRate.RateID 
                     INNER JOIN tblRateSheet 
                             ON tblRateSheet.RateSheetID = v_lastRateSheetID_ 
                     INNER JOIN tmp_RateSheetDetail_ as  rsd5 
                             ON rsd5.RateID = rt.RateID 
                                AND rsd5.RateSheetID = 
                                    v_lastRateSheetID_ 
              WHERE  rt.updated_at > tblRateSheet.dategenerated 
              UNION 
              SELECT trc4.RateID, 
                     description, 
                     tblRate.Code, 
                     trc4.Interval1, 
                     trc4.IntervalN, 
                     trc4.Rate, 
                     rsd6.Rate AS rate2, 
                     trc4.EffectiveDate, 
                     CASE 
                                WHEN rsd6.Rate > trc4.Rate 
                                     AND rsd6.Destination != 
                                         description THEN 
                                'NAME CHANGE & DECREASE' 
                                ELSE 
                                  CASE 
                                    WHEN rsd6.Rate > trc4.Rate 
                                         AND trc4.EffectiveDate <= Now() THEN 
                                    'DECREASE' 
                                    ELSE 
                                      CASE 
                                        WHEN ( rsd6.Rate > 
                                               trc4.Rate 
                                               AND trc4.EffectiveDate > Now() 
                                             ) 
                                      THEN 
                                        'PENDING DECREASE' 
                                        ELSE 
                                          CASE 
                                            WHEN ( rsd6.Rate < 
                                                   trc4.Rate 
                                                   AND trc4.EffectiveDate <= 
                                                       Now() ) 
                                          THEN 
                                            'INCREASE' 
                                            ELSE 
                                              CASE 
                                                WHEN ( rsd6.Rate 
                                                       < 
                                                       trc4.Rate 
                                                       AND trc4.EffectiveDate > 
                                                           Now() ) 
                                              THEN 
                                                'PENDING INCREASE' 
                                                ELSE 
                                                  CASE 
                                                    WHEN 
                                              rsd6.Destination != 
                                              description THEN 
                                                    'NAME CHANGE' 
                                                    ELSE 'NO CHANGE' 
                                                  END 
                                              END 
                                          END 
                                      END 
                                  END 
                              END as  `Change` 
              FROM   tblRate 
                     INNER JOIN tmp_CustomerRates_ trc4 
                             ON trc4.RateID = tblRate.RateID 
                     INNER JOIN tblRateSheet 
                             ON tblRateSheet.RateSheetID = v_lastRateSheetID_ 
                     INNER JOIN tmp_CloneRateSheetDetail_ as rsd6 
                             ON rsd6.RateID = trc4.RateID 
                                AND rsd6.RateSheetID = 
                                    v_lastRateSheetID_ 
              WHERE  trc4.LastModifiedDate > tblRateSheet.dategenerated) AS tbl ;

      INSERT INTO tmp_RateSheetRate_ 
      SELECT tblRateSheetDetails.RateID, 
             tblRateSheetDetails.Destination, 
             tblRateSheetDetails.Code, 
             tblRateSheetDetails.Interval1, 
             tblRateSheetDetails.IntervalN, 
             tblRateSheetDetails.Rate, 
             v_trunkDescription_, 
             'DELETE', 
             tblRateSheetDetails.EffectiveDate 
      FROM   tblRate 
             INNER JOIN tblRateSheetDetails 
                     ON tblRate.RateID = tblRateSheetDetails.RateID 
                        AND tblRateSheetDetails.RateSheetID = v_lastRateSheetID_ 
             LEFT JOIN (SELECT DISTINCT RateID, 
                                        effectivedate 
                        FROM   tmp_RateTableRate_ 
                        UNION 
                        SELECT DISTINCT RateID, 
                                        effectivedate 
                        FROM tmp_CustomerRates_) AS TBL 
                    ON TBL.RateID = tblRateSheetDetails.RateID 
      WHERE  `change` != 'DELETE' 
             AND TBL.RateID IS NULL ; 

      IF v_IncludePrefix_ = 1 
        THEN 
            SELECT rateid, 
                   interval1, 
                   intervaln, 
                   destination, 
                   codes, 
                   `tech prefix`, 
                   `interval`, 
                   `rate per minute (usd)`, 
                   `level`, 
                   `change`, 
                   `effective date` 
            FROM   (
      
            SELECT rsr.RateID, 
                 rsr.Interval1, 
                 rsr.IntervalN, 
                 rsr.Destination, 
                 rsr.Codes, 
                 v_Prefix_ as `Tech prefix`, 
                 CONCAT(rsr.Interval1,'/',rsr.IntervalN) as `Interval`, 
                 FORMAT(rsr.Rate,6) as `Rate Per Minute (USD)`, 
                 rsr.`level`, 
                 rsr.`change`,
                 rsr.EffectiveDate as `Effective Date`,
                @row_num := IF(@prev_RateID=rsr.RateID and @prev_effectivedate >= rsr.EffectiveDate ,@row_num+1,1) AS RowID2,
                @prev_RateID  := rsr.RateID,
                   @prev_effectivedate  := rsr.EffectiveDate 
            FROM   tmp_RateSheetRate_ rsr ,(SELECT @row_num := 1) x,(SELECT @prev_RateID := '') y,(SELECT @prev_effectivedate := '') z 
            where rsr.Rate > 0
            ORDER BY rsr.RateID,rsr.EffectiveDate desc
          ) AS tbl 
            WHERE  tbl.rowid2 = 1 
            ORDER  BY tbl.Destination, 
                      tbl.Codes ;
      ELSE 
            SELECT rateid, 
                   interval1, 
                   intervaln, 
                   destination, 
                   codes, 
                   `interval`, 
                   `rate per minute (usd)`, 
                   level, 
                   `change`,
                   `effective date` 
            FROM   (SELECT rsr.RateID, 
                           rsr.Interval1, 
                           rsr.IntervalN, 
                           rsr.Destination, 
                           rsr.Codes, 
                           CONCAT(rsr.Interval1,'/',rsr.IntervalN) as `Interval`, 
                           FORMAT(rsr.Rate, 6) as `Rate Per Minute (USD)`, 
                           rsr.level, 
                           rsr.`change`, 
                           rsr.EffectiveDate as `Effective Date`, 
                           @row_num := IF(@prev_RateID=rsr.RateID and @prev_effectivedate >= rsr.EffectiveDate ,@row_num+1,1) AS RowID2,
                  @prev_RateID  := rsr.RateID,
                     @prev_effectivedate  := rsr.EffectiveDate  
                    FROM   tmp_RateSheetRate_ rsr ,(SELECT @row_num := 1) x,(SELECT @prev_RateID := '') y,(SELECT @prev_effectivedate := '') z 
          where rsr.Rate > 0
          ORDER BY rsr.RateID,rsr.EffectiveDate desc
          ) AS tbl 
            WHERE  tbl.rowid2 = 1 
            ORDER  BY tbl.Destination, 
                      tbl.Codes ;
        END IF; 
        
        SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END