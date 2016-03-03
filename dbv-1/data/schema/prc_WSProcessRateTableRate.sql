CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSProcessRateTableRate`(IN `p_ratetableid` INT, IN `p_replaceAllRates` INT, IN `p_effectiveImmediately` INT, IN `p_processId` VARCHAR(200), IN `p_addNewCodesToCodeDeck` INT, IN `p_companyId` INT)
BEGIN
	DECLARE v_AffectedRecords_ INT DEFAULT 0;
	 DECLARE     v_CodeDeckId_ INT ;
    
	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;    
	 
    DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
    CREATE TEMPORARY TABLE tmp_JobLog_ (
        Message VARCHAR(200)
    );
    DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableRate_;
    CREATE TEMPORARY TABLE tmp_TempRateTableRate_ (
			`CodeDeckId` int ,
			`Code` varchar(50) ,
			`Description` varchar(200) ,
			`Rate` decimal(18, 6) ,
			`EffectiveDate` Datetime ,
			`Change` varchar(100) ,
			`ProcessId` varchar(200) ,
			`Preference` int ,
			`ConnectionFee` decimal(18, 6),
			`Interval1` int,
			`IntervalN` int,
			INDEX tmp_EffectiveDate (`EffectiveDate`),
			INDEX tmp_Code (`Code`),
			INDEX tmp_Change (`Change`)
    );
    
     DELETE n1 FROM tblTempRateTableRate n1, tblTempRateTableRate n2 WHERE n1.EffectiveDate < n2.EffectiveDate 
	 	AND n1.CodeDeckId = n2.CodeDeckId
		AND  n1.Code = n2.Code
		AND  n1.ProcessId = n2.ProcessId
 		AND  n1.ProcessId = p_processId and n2.ProcessId = p_processId;

		  INSERT INTO tmp_TempRateTableRate_
        SELECT distinct `CodeDeckId`,`Code`,`Description`,`Rate`,`EffectiveDate`,`Change`,`ProcessId`,`Preference`,`ConnectionFee`,`Interval1`,`IntervalN` FROM tblTempRateTableRate WHERE tblTempRateTableRate.ProcessId = p_processId;
		 
      
		
	 	     SELECT CodeDeckId INTO v_CodeDeckId_ FROM tmp_TempRateTableRate_ WHERE ProcessId = p_processId  LIMIT 1;

            UPDATE tmp_TempRateTableRate_ as tblTempRateTableRate
            LEFT JOIN tblRate
                ON tblRate.Code = tblTempRateTableRate.Code
                AND tblRate.CompanyID = p_companyId
                AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
                AND tblRate.CodeDeckId =  v_CodeDeckId_
            SET  
                tblTempRateTableRate.Interval1 = CASE WHEN tblTempRateTableRate.Interval1 is not null  and tblTempRateTableRate.Interval1 > 0
                                            THEN    
                                                tblTempRateTableRate.Interval1
                                            ELSE
                                            CASE WHEN tblRate.Interval1 is not null  
                                            THEN    
                                                tblRate.Interval1
                                            ELSE CASE WHEN tblTempRateTableRate.Interval1 is null and (tblTempRateTableRate.Description LIKE '%gambia%' OR tblTempRateTableRate.Description LIKE '%mexico%')
                                                 THEN 
                                                    60
                                            ELSE CASE WHEN tblTempRateTableRate.Description LIKE '%USA%' 
                                                 THEN 
                                                    6
                                                 ELSE 
                                                    1
                                                END
                                            END

                                            END
                                            END,
                tblTempRateTableRate.IntervalN = CASE WHEN tblTempRateTableRate.IntervalN is not null  and tblTempRateTableRate.IntervalN > 0
                                            THEN    
                                                tblTempRateTableRate.IntervalN
                                            ELSE
                                                CASE WHEN tblRate.IntervalN is not null 
                                          THEN
                                            tblRate.IntervalN
                                          ElSE
                                            CASE
                                                WHEN tblTempRateTableRate.Description LIKE '%mexico%' THEN 60
                                            ELSE CASE
                                                WHEN tblTempRateTableRate.Description LIKE '%USA%' THEN 6
                                                
                                            ELSE 
                                            1
                                            END
                                            END
                                          END
                                          END;
                                           
          DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableRate2_;
			 CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempRateTableRate2_ as (select * from tmp_TempRateTableRate_);   
 


            IF  p_addNewCodesToCodeDeck = 1
            THEN

                INSERT INTO tblRate (CompanyID,
                Code,
                Description,
                CreatedBy,
                CountryID,
                CodeDeckId,
                Interval1,
                IntervalN)
                    SELECT DISTINCT
                        p_companyId,
                        vc.Code,
                        vc.Description,
                        'WindowsService',
                        c.CountryID,
                        CodeDeckId,
                        Interval1,
                        IntervalN
                    FROM
                    (
                        SELECT DISTINCT
                            tblTempRateTableRate.Code,
                            tblTempRateTableRate.Description,
                            tblTempRateTableRate.CodeDeckId,
                            tblTempRateTableRate.Interval1,
                            tblTempRateTableRate.IntervalN

                        FROM tmp_TempRateTableRate_  as tblTempRateTableRate
                        LEFT JOIN tblRate
					             ON tblRate.Code = tblTempRateTableRate.Code
					             AND tblRate.CompanyID = p_companyId
					             AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
                        WHERE tblRate.RateID IS NULL
                        AND tblTempRateTableRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')) vc
                    LEFT JOIN
                    (
                        SELECT DISTINCT
                            tblTempRateTableRate2.Code,
                            tblCountry.CountryID
                        FROM tblCountry
                        LEFT OUTER JOIN
                        (
                            SELECT
                                Prefix
                            FROM tblCountry
                            GROUP BY Prefix
                            HAVING COUNT(*) > 1) d
                            ON tblCountry.Prefix = d.Prefix
                            INNER JOIN tmp_TempRateTableRate2_ as tblTempRateTableRate2
                                ON (tblTempRateTableRate2.Code LIKE CONCAT(tblCountry.Prefix
                                , '%')
                                AND d.Prefix IS NULL
                                )
                                OR (tblTempRateTableRate2.Code LIKE CONCAT(tblCountry.Prefix
                                , '%')
                                AND d.Prefix IS NOT NULL
                                AND (tblTempRateTableRate2.Description LIKE Concat('%'
                                , tblCountry.Country
                                , '%')
                                )
                                )
                        WHERE tblTempRateTableRate2.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')) c
                        ON vc.Code = c.Code;
                        /* AND c.CountryID IS NOT NULL*/


                INSERT INTO tmp_JobLog_ (Message)
                    SELECT DISTINCT
                        CONCAT(tblTempRateTableRate.Code , ' INVALID CODE - COUNTRY NOT FOUND ')
                    FROM tmp_TempRateTableRate_  as tblTempRateTableRate 
                    INNER JOIN tblRate
				             ON tblRate.Code = tblTempRateTableRate.Code
				             AND tblRate.CompanyID = p_companyId
				             AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
						  WHERE tblRate.CountryID IS NULL
                    AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');
            ELSE
                INSERT INTO tmp_JobLog_ (Message)
                    SELECT DISTINCT
                        CONCAT(c.Code , ' CODE DOES NOT EXIST IN CODE DECK')
                    FROM
                    (
                        SELECT DISTINCT
                            tblTempRateTableRate.Code,
                            tblTempRateTableRate.Description
                        FROM tmp_TempRateTableRate_  as tblTempRateTableRate 
                        LEFT JOIN tblRate
			                ON tblRate.Code = tblTempRateTableRate.Code
			                AND tblRate.CompanyID = p_companyId
			                AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
								WHERE tblRate.RateID IS NULL
                        AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')) c;


            END IF;

            IF  p_replaceAllRates = 1
            THEN


                DELETE FROM tblRateTableRate
                WHERE RateTableId = p_ratetableid;

            END IF;

            DELETE tblRateTableRate
                FROM tblRateTableRate
                JOIN tblRate
                    ON tblRate.RateID = tblRateTableRate.RateId
                    AND tblRate.CompanyID = p_companyId
                    JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
                        ON tblRate.Code = tblTempRateTableRate.Code
            WHERE tblRateTableRate.RateTableId = p_ratetableid
                AND tblTempRateTableRate.Change IN ('Delete', 'R', 'D', 'Blocked', 'Block');

            IF  p_effectiveImmediately = 1
            THEN
                UPDATE tmp_TempRateTableRate_
                SET EffectiveDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
                WHERE EffectiveDate < DATE_FORMAT (NOW(), '%Y-%m-%d');
            END IF;

            UPDATE tblRateTableRate 
            LEFT JOIN tblRate 
					ON tblRateTableRate.RateId = tblRate.RateId
	            AND tblRateTableRate.RateTableId = p_ratetableid 
            LEFT JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
                ON tblRate.Code = tblTempRateTableRate.Code
                AND tblRate.CompanyID = p_companyId
                AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
                AND tblRateTableRate.RateId = tblRate.RateId
				SET tblRateTableRate.ConnectionFee = tblTempRateTableRate.ConnectionFee,
                tblRateTableRate.Interval1 = tblTempRateTableRate.Interval1,
                tblRateTableRate.IntervalN = tblTempRateTableRate.IntervalN;

            DELETE tblTempRateTableRate
                FROM tmp_TempRateTableRate_ as tblTempRateTableRate
                JOIN tblRate
                    ON tblRate.Code = tblTempRateTableRate.Code
                    JOIN tblRateTableRate
                        ON tblRateTableRate.RateId = tblRate.RateId
                        AND tblRate.CompanyID = p_companyId
                        AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
                        AND tblRateTableRate.RateTableId = p_ratetableid
                        AND tblTempRateTableRate.Rate = tblRateTableRate.Rate
                        AND (
                        tblRateTableRate.EffectiveDate = tblTempRateTableRate.EffectiveDate 
                        OR  
                        (
                        DATE_FORMAT (tblRateTableRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTableRate.EffectiveDate, '%Y-%m-%d')
                        )
                        OR 1 = (CASE
                            WHEN tblTempRateTableRate.EffectiveDate > NOW() THEN 1 
                            ELSE 0
                        END)
                        )
            WHERE  tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block');

            SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();


            UPDATE tmp_TempRateTableRate_ as tblTempRateTableRate
            JOIN tblRate
                ON tblRate.Code = tblTempRateTableRate.Code
                AND tblRate.CompanyID = p_companyId
                AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
                JOIN tblRateTableRate
                    ON tblRateTableRate.RateId = tblRate.RateId
                    AND tblRateTableRate.RateTableId = p_ratetableid
				SET tblRateTableRate.Rate = tblTempRateTableRate.Rate
            WHERE tblTempRateTableRate.Rate <> tblRateTableRate.Rate
            AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
            AND DATE_FORMAT (tblRateTableRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTableRate.EffectiveDate, '%Y-%m-%d');

            SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();


            INSERT INTO tblRateTableRate (RateTableId,
            RateId,
            Rate,
            EffectiveDate,
            ConnectionFee,
            Interval1,
            IntervalN
            )
                SELECT DISTINCT
                    p_ratetableid,
                    tblRate.RateID,
                    tblTempRateTableRate.Rate,
                    tblTempRateTableRate.EffectiveDate,
                    tblTempRateTableRate.ConnectionFee,
                    tblTempRateTableRate.Interval1,
                    tblTempRateTableRate.IntervalN
                FROM tmp_TempRateTableRate_ as tblTempRateTableRate
                JOIN tblRate
                    ON tblRate.Code = tblTempRateTableRate.Code
                    AND tblRate.CompanyID = p_companyId
                    AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
                    LEFT JOIN tblRateTableRate
                        ON tblRate.RateID = tblRateTableRate.RateId
                        AND tblRateTableRate.RateTableId = p_ratetableid
                WHERE (tblRateTableRate.RateTableRateID IS NULL
                OR (
                tblRateTableRate.RateTableRateID IS NOT NULL
                AND tblTempRateTableRate.EffectiveDate >= DATE_FORMAT (NOW(), '%Y-%m-%d')
                AND tblTempRateTableRate.Rate <> tblRateTableRate.Rate
                AND tblTempRateTableRate.EffectiveDate <> tblRateTableRate.EffectiveDate
                AND tblRateTableRate.EffectiveDate <> tblTempRateTableRate.EffectiveDate
                )
                )
                AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
                AND tblTempRateTableRate.EffectiveDate >= DATE_FORMAT (NOW(), '%Y-%m-%d');

            SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();


      
	 INSERT INTO tmp_JobLog_ (Message)
	 SELECT CONCAT(v_AffectedRecords_ , ' Records Uploaded \n\r ' );
	 
 	 SELECT * from tmp_JobLog_;
	 DELETE  FROM tmp_TempRateTableRate_ WHERE  ProcessId = p_processId;
	 
	 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END