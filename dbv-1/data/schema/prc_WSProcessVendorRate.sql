CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSProcessVendorRate`(IN `p_accountId` INT
, IN `p_trunkId` INT
, IN `p_replaceAllRates` INT
, IN `p_effectiveImmediately` INT
, IN `p_processId` VARCHAR(200)
, IN `p_addNewCodesToCodeDeck` INT
, IN `p_companyId` INT)
BEGIN

    DECLARE v_AffectedRecords_ INT DEFAULT 0;
	 DECLARE     v_CodeDeckId_ INT ;
    
	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;    
	 
    DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
    CREATE TEMPORARY TABLE tmp_JobLog_ (
        Message VARCHAR(200)
    );
    DROP TEMPORARY TABLE IF EXISTS tmp_TempVendorRate_;
    CREATE TEMPORARY TABLE tmp_TempVendorRate_ (
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
            INDEX tmp_CC (`Code`,`Change`),
			INDEX tmp_Change (`Change`)
    );

     DELETE n1 FROM tblTempVendorRate n1, tblTempVendorRate n2
     WHERE n1.EffectiveDate < n2.EffectiveDate
	 	AND n1.CodeDeckId = n2.CodeDeckId
		AND  n1.Code = n2.Code
		AND  n1.ProcessId = n2.ProcessId
 		AND  n1.ProcessId = p_processId and n2.ProcessId = p_processId;

		  INSERT INTO tmp_TempVendorRate_
        SELECT distinct `CodeDeckId`,`Code`,`Description`,`Rate`,`EffectiveDate`,`Change`,`ProcessId`,`Preference`,`ConnectionFee`,`Interval1`,`IntervalN` FROM tblTempVendorRate WHERE tblTempVendorRate.ProcessId = p_processId;




	 	     SELECT CodeDeckId INTO v_CodeDeckId_ FROM tmp_TempVendorRate_ WHERE ProcessId = p_processId  LIMIT 1;

            UPDATE tmp_TempVendorRate_ as tblTempVendorRate
            LEFT JOIN tblRate
                ON tblRate.Code = tblTempVendorRate.Code
                AND tblRate.CompanyID = p_companyId
                AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
                AND tblRate.CodeDeckId =  v_CodeDeckId_
            SET
                tblTempVendorRate.Interval1 = CASE WHEN tblTempVendorRate.Interval1 is not null  and tblTempVendorRate.Interval1 > 0
                                            THEN
                                                tblTempVendorRate.Interval1
                                            ELSE
                                            CASE WHEN tblRate.Interval1 is not null
                                            THEN
                                                tblRate.Interval1
                                            ELSE CASE WHEN tblTempVendorRate.Interval1 is null and (tblTempVendorRate.Description LIKE '%gambia%' OR tblTempVendorRate.Description LIKE '%mexico%')
                                                 THEN
                                                    60
                                            ELSE CASE WHEN tblTempVendorRate.Description LIKE '%USA%'
                                                 THEN
                                                    6
                                                 ELSE
                                                    1
                                                END
                                            END

                                            END
                                            END,
                tblTempVendorRate.IntervalN = CASE WHEN tblTempVendorRate.IntervalN is not null  and tblTempVendorRate.IntervalN > 0
                                            THEN
                                                tblTempVendorRate.IntervalN
                                            ELSE
                                                CASE WHEN tblRate.IntervalN is not null
                                          THEN
                                            tblRate.IntervalN
                                          ElSE
                                            CASE
                                                WHEN tblTempVendorRate.Description LIKE '%mexico%' THEN 60
                                            ELSE CASE
                                                WHEN tblTempVendorRate.Description LIKE '%USA%' THEN 6

                                            ELSE
                                            1
                                            END
                                            END
                                          END
                                          END;

          DROP TEMPORARY TABLE IF EXISTS tmp_TempVendorRate2_;
			 CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempVendorRate2_ as (select * from tmp_TempVendorRate_);



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
                            tblTempVendorRate.Code,
                            tblTempVendorRate.Description,
                            tblTempVendorRate.CodeDeckId,
                            tblTempVendorRate.Interval1,
                            tblTempVendorRate.IntervalN

                        FROM tmp_TempVendorRate_  as tblTempVendorRate
                        LEFT JOIN tblRate
					             ON tblRate.Code = tblTempVendorRate.Code
					             AND tblRate.CompanyID = p_companyId
					             AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
                        WHERE tblRate.RateID IS NULL
                        AND tblTempVendorRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')) vc
                    LEFT JOIN
                    (
                        SELECT DISTINCT
                            tblTempVendorRate2.Code,
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
                            INNER JOIN tmp_TempVendorRate2_ as tblTempVendorRate2
                                ON (tblTempVendorRate2.Code LIKE CONCAT(tblCountry.Prefix
                                , '%')
                                AND d.Prefix IS NULL
                                )
                                OR (tblTempVendorRate2.Code LIKE CONCAT(tblCountry.Prefix
                                , '%')
                                AND d.Prefix IS NOT NULL
                                AND (tblTempVendorRate2.Description LIKE Concat('%'
                                , tblCountry.Country
                                , '%')
                                )
                                )
                        WHERE tblTempVendorRate2.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')) c
                        ON vc.Code = c.Code;
                        /* AND c.CountryID IS NOT NULL*/



                INSERT INTO tmp_JobLog_ (Message)
                    SELECT DISTINCT
                        CONCAT(tblTempVendorRate.Code , ' INVALID CODE - COUNTRY NOT FOUND ')
                    FROM tmp_TempVendorRate_  as tblTempVendorRate
                    INNER JOIN tblRate
				             ON tblRate.Code = tblTempVendorRate.Code
				             AND tblRate.CompanyID = p_companyId
				             AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
						  WHERE tblRate.CountryID IS NULL
                    AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');
            ELSE
                INSERT INTO tmp_JobLog_ (Message)
                    SELECT DISTINCT
                        CONCAT(c.Code , ' CODE DOES NOT EXIST IN CODE DECK')
                    FROM
                    (
                        SELECT DISTINCT
                            tblTempVendorRate.Code,
                            tblTempVendorRate.Description
                        FROM tmp_TempVendorRate_  as tblTempVendorRate
                        LEFT JOIN tblRate
			                ON tblRate.Code = tblTempVendorRate.Code
			                AND tblRate.CompanyID = p_companyId
			                AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
								WHERE tblRate.RateID IS NULL
                        AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')) c;


            END IF;

            IF  p_replaceAllRates = 1
            THEN


                DELETE FROM tblVendorRate
                WHERE AccountId = p_accountId
                    AND TrunkID = p_trunkId;

            END IF;

            DELETE tblVendorRate
                FROM tblVendorRate
                JOIN tblRate
                    ON tblRate.RateID = tblVendorRate.RateId
                    AND tblRate.CompanyID = p_companyId
                    JOIN tmp_TempVendorRate_ as tblTempVendorRate
                        ON tblRate.Code = tblTempVendorRate.Code
            WHERE tblVendorRate.AccountId = p_accountId
                AND tblVendorRate.TrunkId = p_trunkId
                AND tblTempVendorRate.Change IN ('Delete', 'R', 'D', 'Blocked', 'Block');

            IF  p_effectiveImmediately = 1
            THEN





            	/*DELETE n1 FROM tmp_TempVendorRate_ n1, tmp_TempVendorRate2_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate AND n1.Code = n2.Code;*/





                UPDATE tmp_TempVendorRate_
                SET EffectiveDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
                WHERE EffectiveDate < DATE_FORMAT (NOW(), '%Y-%m-%d');



            END IF;

            UPDATE tblVendorRate
					INNER JOIN tblRate
						ON tblVendorRate.RateId = tblRate.RateId
						AND tblVendorRate.AccountId = p_accountId
						AND tblVendorRate.TrunkId = p_trunkId
					INNER JOIN tmp_TempVendorRate_ as tblTempVendorRate
						ON tblRate.Code = tblTempVendorRate.Code
						AND tblRate.CompanyID = p_companyId
						AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
						AND tblVendorRate.RateId = tblRate.RateId
					SET tblVendorRate.ConnectionFee = tblTempVendorRate.ConnectionFee,
						tblVendorRate.Interval1 = tblTempVendorRate.Interval1,
							tblVendorRate.IntervalN = tblTempVendorRate.IntervalN
					WHERE tblVendorRate.AccountId = p_accountId
			            AND tblVendorRate.TrunkId = p_trunkId ;

            DELETE tblTempVendorRate
                FROM tmp_TempVendorRate_ as tblTempVendorRate
                JOIN tblRate
                    ON tblRate.Code = tblTempVendorRate.Code
                    JOIN tblVendorRate
                        ON tblVendorRate.RateId = tblRate.RateId
                        AND tblRate.CompanyID = p_companyId
                        AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
                        AND tblVendorRate.AccountId = p_accountId
                        AND tblVendorRate.TrunkId = p_trunkId
                        AND tblTempVendorRate.Rate = tblVendorRate.Rate
                        AND (
                        tblVendorRate.EffectiveDate = tblTempVendorRate.EffectiveDate
                        OR
                        (
                        DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempVendorRate.EffectiveDate, '%Y-%m-%d')
                        )
                        OR 1 = (CASE
                            WHEN tblTempVendorRate.EffectiveDate > NOW() THEN 1
                            ELSE 0
                        END)
                        )
            WHERE  tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block');

            SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();


            UPDATE tmp_TempVendorRate_ as tblTempVendorRate
            JOIN tblRate
                ON tblRate.Code = tblTempVendorRate.Code
                AND tblRate.CompanyID = p_companyId
                AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
                JOIN tblVendorRate
                    ON tblVendorRate.RateId = tblRate.RateId
                    AND tblVendorRate.AccountId = p_accountId
                    AND tblVendorRate.TrunkId = p_trunkId
				SET tblVendorRate.Rate = tblTempVendorRate.Rate
            WHERE tblTempVendorRate.Rate <> tblVendorRate.Rate
            AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
            AND DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempVendorRate.EffectiveDate, '%Y-%m-%d');





            SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();


            INSERT INTO tblVendorRate (AccountId,
            TrunkID,
            RateId,
            Rate,
            EffectiveDate,
            ConnectionFee,
            Interval1,
            IntervalN
            )
                SELECT DISTINCT
                    p_accountId,
                    p_trunkId,
                    tblRate.RateID,
                    tblTempVendorRate.Rate,
                    tblTempVendorRate.EffectiveDate,
                    tblTempVendorRate.ConnectionFee,
                    tblTempVendorRate.Interval1,
                    tblTempVendorRate.IntervalN
                FROM tmp_TempVendorRate_ as tblTempVendorRate
                JOIN tblRate
                    ON tblRate.Code = tblTempVendorRate.Code
                    AND tblRate.CompanyID = p_companyId
                    AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
                    LEFT JOIN tblVendorRate
                        ON tblRate.RateID = tblVendorRate.RateId
                        AND tblVendorRate.AccountId = p_accountId
                        AND tblVendorRate.trunkid = p_trunkId
                WHERE (tblVendorRate.VendorRateID IS NULL
                OR (
                tblVendorRate.VendorRateID IS NOT NULL
                AND tblTempVendorRate.EffectiveDate >= DATE_FORMAT (NOW(), '%Y-%m-%d')
                AND tblTempVendorRate.Rate <> tblVendorRate.Rate
                AND tblTempVendorRate.EffectiveDate <> tblVendorRate.EffectiveDate
                )
                )
                AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
                AND tblTempVendorRate.EffectiveDate >= DATE_FORMAT (NOW(), '%Y-%m-%d');

            SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();



	 INSERT INTO tmp_JobLog_ (Message)
	 SELECT CONCAT(v_AffectedRecords_ , ' Records Uploaded \n\r ' );

 	 SELECT * from tmp_JobLog_;
	 DELETE  FROM tblTempVendorRate WHERE  ProcessId = p_processId;
	 
	 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END