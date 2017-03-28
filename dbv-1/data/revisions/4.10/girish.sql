USE `Ratemanagement3`;

DROP PROCEDURE IF EXISTS `prc_WSGenerateRateSheet`;
DELIMITER |
CREATE PROCEDURE `prc_WSGenerateRateSheet`(
	IN `p_CustomerID` INT,
	IN `p_Trunk` VARCHAR(100)
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
    CREATE TEMPORARY TABLE tmp_RateSheetRate_(
        RateID        INT,
        Destination   VARCHAR(200),
        Codes         VARCHAR(50),
        Interval1     INT,
        IntervalN     INT,
        Rate          DECIMAL(18, 6),
        `level`         VARCHAR(10),
        `change`        VARCHAR(50),
        EffectiveDate  DATE,
			  INDEX tmp_RateSheetRate_RateID (`RateID`)
    );
    DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRates_;
    CREATE TEMPORARY TABLE tmp_CustomerRates_ (
        RateID INT,
        Interval1 INT,
        IntervalN  INT,
        Rate DECIMAL(18, 6),
        EffectiveDate DATE,
        LastModifiedDate DATETIME,
        INDEX tmp_CustomerRates_RateId (`RateID`)
    );
    DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
    CREATE TEMPORARY TABLE tmp_RateTableRate_ (
        RateID INT,
        Interval1 INT,
        IntervalN  INT,
        Rate DECIMAL(18, 6),
        EffectiveDate DATE,
        updated_at DATETIME,
        INDEX tmp_RateTableRate_RateId (`RateID`)
    );

    DROP TEMPORARY TABLE IF EXISTS tmp_RateSheetDetail_;
    CREATE TEMPORARY TABLE tmp_RateSheetDetail_ (
        ratesheetdetailsid int,
        RateID int ,
        RateSheetID int,
        Destination varchar(200),
        Code varchar(50),
        Rate DECIMAL(18, 6),
        `change` varchar(50),
        EffectiveDate Date,
			  INDEX tmp_RateSheetDetail_RateId (`RateID`,`RateSheetID`)
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
        RateTableAssignDate
        INTO v_codedeckid_, v_ratetableid_, v_RateTableAssignDate_
    FROM tblCustomerTrunk
    WHERE tblCustomerTrunk.TrunkID = p_Trunk
    AND tblCustomerTrunk.AccountID = p_CustomerID
    AND tblCustomerTrunk.Status = 1;

  INSERT INTO tmp_CustomerRates_
      SELECT  RateID,
              Interval1,
              IntervalN,
              tblCustomerRate.Rate,
              effectivedate,
              lastmodifieddate
      FROM   tblAccount
         JOIN tblCustomerRate
           ON tblAccount.AccountID = tblCustomerRate.CustomerID
      WHERE  tblAccount.AccountID = p_CustomerID
          -- AND tblCustomerRate.Rate > 0
         AND tblCustomerRate.TrunkID = p_Trunk
         ORDER BY tblCustomerRate.CustomerID,tblCustomerRate.TrunkID,tblCustomerRate.RateID,tblCustomerRate.EffectiveDate DESC;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRates4_;
	DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRates2_;
	
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_CustomerRates4_ as (select * from tmp_CustomerRates_);
   DELETE n1 FROM tmp_CustomerRates_ n1, tmp_CustomerRates4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
   AND  n1.RateId = n2.RateId;

   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_CustomerRates2_ as (select * from tmp_CustomerRates_);

	INSERT INTO tmp_RateTableRate_
		SELECT
			tblRateTableRate.RateID,
         tblRateTableRate.Interval1,
         tblRateTableRate.IntervalN,
         tblRateTableRate.Rate,         
         tblRateTableRate.EffectiveDate,
         tblRateTableRate.updated_at
      FROM tblAccount
      JOIN tblCustomerTrunk
           ON tblCustomerTrunk.AccountID = tblAccount.AccountID
      JOIN tblRateTable
           ON tblCustomerTrunk.ratetableid = tblRateTable.ratetableid
      JOIN tblRateTableRate
           ON tblRateTableRate.ratetableid = tblRateTable.ratetableid
      LEFT JOIN tmp_CustomerRates_ trc1
            ON trc1.RateID = tblRateTableRate.RateID
      WHERE  tblAccount.AccountID = p_CustomerID
         -- AND tblRateTableRate.Rate > 0
         AND tblCustomerTrunk.TrunkID = p_Trunk
         AND (( tblRateTableRate.EffectiveDate <= Now()
             AND ( ( trc1.RateID IS NULL )
                OR ( trc1.RateID IS NOT NULL
                   AND tblRateTableRate.ratetablerateid IS NULL )
              ) )
          OR ( tblRateTableRate.EffectiveDate > Now()
             AND ( ( trc1.RateID IS NULL )
                OR ( trc1.RateID IS NOT NULL
                   AND tblRateTableRate.EffectiveDate < (SELECT
									   IFNULL(MIN(crr.EffectiveDate),
									   tblRateTableRate.EffectiveDate)
										  FROM   tmp_CustomerRates2_ crr
										  WHERE  crr.RateID =
					   tblRateTableRate.RateID
                      ) ) ) ) )
      ORDER BY tblRateTableRate.RateID,tblRateTableRate.EffectiveDate desc;

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate4_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate4_ as (select * from tmp_RateTableRate_);
   DELETE n1 FROM tmp_RateTableRate_ n1, tmp_RateTableRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
   AND  n1.RateId = n2.RateId;

    INSERt INTO tmp_RateSheetDetail_
      SELECT ratesheetdetailsid,
                     RateID,
                     RateSheetID,
                     Destination,
                     Code,
                     Rate,
                     `change`,
                     effectivedate
              FROM   tblRateSheetDetails
              WHERE  RateSheetID = v_lastRateSheetID_
          ORDER BY RateID,effectivedate desc;

	DROP TEMPORARY TABLE IF EXISTS tmp_RateSheetDetail4_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateSheetDetail4_ as (select * from tmp_RateSheetDetail_);
   DELETE n1 FROM tmp_RateSheetDetail_ n1, tmp_RateSheetDetail4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
   AND  n1.RateId = n2.RateId;

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
      FROM   (
			SELECT
				rt.RateID,				
				rt.Interval1,
				rt.IntervalN,
				rt.Rate,
				rt.EffectiveDate
         FROM   tmp_RateTableRate_ rt
         LEFT JOIN tblRateSheet
         	ON tblRateSheet.RateSheetID =
            	v_lastRateSheetID_
         LEFT JOIN tmp_RateSheetDetail_ as  rsd
         	ON rsd.RateID = rt.RateID AND rsd.RateSheetID = v_lastRateSheetID_
			WHERE  rsd.ratesheetdetailsid IS NULL

			UNION

			SELECT
				trc2.RateID,            
            trc2.Interval1,
            trc2.IntervalN,
            trc2.Rate,
            trc2.EffectiveDate
         FROM   tmp_CustomerRates_ trc2
         LEFT JOIN tblRateSheet
         	ON tblRateSheet.RateSheetID =
         		v_lastRateSheetID_
         LEFT JOIN tmp_CloneRateSheetDetail_ as  rsd2
         	ON rsd2.RateID = trc2.RateID AND rsd2.RateSheetID = v_lastRateSheetID_
			WHERE  rsd2.ratesheetdetailsid IS NULL

			) AS tbl
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
				  ) AS tbl ;

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
	
	DROP TEMPORARY TABLE IF EXISTS tmp_RateSheetRate4_;	
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateSheetRate4_ as (select * from tmp_RateSheetRate_);
	DELETE n1 FROM tmp_RateSheetRate_ n1, tmp_RateSheetRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
	AND  n1.RateId = n2.RateId;

      IF v_IncludePrefix_ = 1
        THEN
            SELECT rsr.RateID AS rateid,
                 rsr.Interval1 AS interval1,
                 rsr.IntervalN AS intervaln,
                 rsr.Destination AS destination,
                 rsr.Codes AS codes,
                 v_Prefix_ AS `tech prefix`,
                 CONCAT(rsr.Interval1,'/',rsr.IntervalN) AS `interval`,
                 FORMAT(rsr.Rate,6) AS `rate per minute (usd)`,
                 rsr.`level`,
                 rsr.`change`,
                 rsr.EffectiveDate AS `effective date`
            FROM   tmp_RateSheetRate_ rsr
            -- WHERE rsr.Rate > 0
            ORDER BY rsr.Destination,rsr.Codes desc;
      ELSE
            SELECT rsr.RateID AS rateid ,
                           rsr.Interval1 AS interval1,
                           rsr.IntervalN AS intervaln,
                           rsr.Destination AS destination,
                           rsr.Codes AS codes,
                           CONCAT(rsr.Interval1,'/',rsr.IntervalN) AS  `interval`,
                           FORMAT(rsr.Rate, 6) AS `rate per minute (usd)`,
                           rsr.`level`,
                           rsr.`change`,
                           rsr.EffectiveDate AS `effective date`
            FROM   tmp_RateSheetRate_ rsr
            -- WHERE rsr.Rate > 0
          	ORDER BY rsr.Destination,rsr.Codes DESC;
        END IF; 
        
        SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END|
DELIMITER ;