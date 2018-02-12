use Ratemanagement3;





DROP PROCEDURE IF EXISTS `prc_SplitAndInsertVendorRate`;
DELIMITER //
CREATE PROCEDURE `prc_SplitAndInsertVendorRate`(
	IN `TempVendorRateID` INT,
	IN `Code` VARCHAR(500),
	IN `p_countryCode` VARCHAR(50)
)
BEGIN

	DECLARE v_First_ BIGINT;
	DECLARE v_Last_ BIGINT;

	SELECT  REPLACE(SUBSTRING(SUBSTRING_INDEX(Code, '-', 1)
                 , LENGTH(SUBSTRING_INDEX(Code, '-', 0)) + 1)
                 , '-'
                 , '') INTO v_First_;

	 SELECT REPLACE(SUBSTRING(SUBSTRING_INDEX(Code, '-', 2)
                 , LENGTH(SUBSTRING_INDEX(Code, '-', 1)) + 1)
                 , '-'
                 , '') INTO v_Last_;


   WHILE v_Last_ >= v_First_
	DO
   	 INSERT my_splits (TempVendorRateID,Code,CountryCode) VALUES (TempVendorRateID,v_Last_,p_countryCode);
	    SET v_Last_ = v_Last_ - 1;
  END WHILE;

END//
DELIMITER ;











DROP PROCEDURE IF EXISTS `prc_WSProcessRateTableRate`;
DELIMITER //
CREATE PROCEDURE `prc_WSProcessRateTableRate`(
	IN `p_ratetableid` INT,
	IN `p_replaceAllRates` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_processId` VARCHAR(200),
	IN `p_addNewCodesToCodeDeck` INT,
	IN `p_companyId` INT
)
BEGIN
	DECLARE v_AffectedRecords_ INT DEFAULT 0;
	 DECLARE     v_CodeDeckId_ INT ;
	 DECLARE totalduplicatecode INT(11);
	 DECLARE errormessage longtext;
	 DECLARE errorheader longtext;

	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
    CREATE TEMPORARY TABLE tmp_JobLog_ (
        Message longtext
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

			 IF  p_effectiveImmediately = 1
            THEN
                UPDATE tblTempRateTableRate
                SET EffectiveDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
                WHERE EffectiveDate < DATE_FORMAT (NOW(), '%Y-%m-%d') AND ProcessId = p_processId;
          END IF;


    		-- Delete duplicates
		     DELETE n1 FROM tblTempRateTableRate n1
			  INNER JOIN
				(
				  SELECT MAX(TempRateTableRateID) AS TempRateTableRateID,EffectiveDate,Code
				  FROM tblTempRateTableRate WHERE ProcessId = p_processId
					GROUP BY Code,EffectiveDate
				HAVING COUNT(*)>1
				)n2
				ON n1.Code = n2.Code
				AND n2.EffectiveDate = n1.EffectiveDate AND n1.TempRateTableRateID < n2.TempRateTableRateID
				WHERE n1.ProcessId = p_processId;



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
                                                ELSE
                                                    1
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
                                                      1
                                          		    END
                                          	  END;

          DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableRate2_;
			 CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempRateTableRate2_ as (select * from tmp_TempRateTableRate_);

			 IF  p_effectiveImmediately = 1
            THEN
                UPDATE tmp_TempRateTableRate_
                SET EffectiveDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
                WHERE EffectiveDate < DATE_FORMAT (NOW(), '%Y-%m-%d');
          END IF;


          select count(*) INTO totalduplicatecode FROM(
				SELECT count(code) as c,code FROM tmp_TempRateTableRate_  GROUP BY Code,EffectiveDate HAVING c>1) AS tbl;


			IF  totalduplicatecode > 0
				THEN
						SELECT GROUP_CONCAT(code) into errormessage FROM(
							select distinct code, 1 as a FROM(
								SELECT   count(code) as c,code FROM tmp_TempRateTableRate_  GROUP BY Code,EffectiveDate HAVING c>1) AS tbl) as tbl2 GROUP by a;
						INSERT INTO tmp_JobLog_ (Message)
						SELECT DISTINCT
                        CONCAT(code , ' DUPLICATE CODE')
                        FROM(
								SELECT   count(code) as c,code FROM tmp_TempRateTableRate_  GROUP BY Code,EffectiveDate HAVING c>1) as tbl;

			END IF;

			IF  totalduplicatecode = 0
			THEN

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

                        fnGetCountryIdByCodeAndCountry (vc.Code ,vc.Description) AS CountryID,
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
                        AND tblTempRateTableRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
							) vc;






                SELECT GROUP_CONCAT(code) into errormessage FROM(
                    SELECT DISTINCT
                        tblTempRateTableRate.Code as Code,1 as a
                    FROM tmp_TempRateTableRate_  as tblTempRateTableRate
                    INNER JOIN tblRate
				             ON tblRate.Code = tblTempRateTableRate.Code
				             AND tblRate.CompanyID = p_companyId
				             AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
						  WHERE tblRate.CountryID IS NULL
                    AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')) as tbl GROUP BY a;

                    /*IF errormessage IS NOT NULL
	                 THEN
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

					 	 END IF;*/

            ELSE
                SELECT GROUP_CONCAT(code) into errormessage FROM(
                    SELECT DISTINCT
                        c.Code as code, 1 as a
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
                        AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')) c) as tbl GROUP BY a;

                 IF errormessage IS NOT NULL
                  THEN
                     INSERT INTO tmp_JobLog_ (Message)
	                  SELECT DISTINCT
                        CONCAT(tbl.Code , ' CODE DOES NOT EXIST IN CODE DECK')
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
                        AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')) as tbl;

					 	END IF;


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


            UPDATE tblRateTableRate
					INNER JOIN tblRate
					ON tblRateTableRate.RateId = tblRate.RateId
					AND tblRateTableRate.RateTableId = p_ratetableid
					INNER JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
					ON tblRate.Code = tblTempRateTableRate.Code
					AND tblRate.CompanyID = p_companyId
					AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
					AND tblRateTableRate.RateId = tblRate.RateId
					SET tblRateTableRate.ConnectionFee = tblTempRateTableRate.ConnectionFee,
					tblRateTableRate.Interval1 = tblTempRateTableRate.Interval1,
					tblRateTableRate.IntervalN = tblTempRateTableRate.IntervalN
					            WHERE tblRateTableRate.RateTableId = p_ratetableid;

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
						   AND tblRateTableRate.EffectiveDate =  tblTempRateTableRate.EffectiveDate
					 WHERE tblRateTableRate.RateTableRateID IS NULL
                AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
                AND tblTempRateTableRate.EffectiveDate >= DATE_FORMAT (NOW(), '%Y-%m-%d');

            SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();


         -- Update previous rate
         call prc_RateTableRateUpdatePreviousRate(p_ratetableid,'');




	END IF;

	 INSERT INTO tmp_JobLog_ (Message)
	 SELECT CONCAT(v_AffectedRecords_ , ' Records Uploaded \n\r ' );

 	 SELECT * from tmp_JobLog_;
	 DELETE  FROM tblTempRateTableRate WHERE  ProcessId = p_processId;

	 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;














DROP PROCEDURE IF EXISTS `prc_checkDialstringAndDupliacteCode`;
DELIMITER //
CREATE PROCEDURE `prc_checkDialstringAndDupliacteCode`(
	IN `p_companyId` INT,
	IN `p_processId` VARCHAR(200) ,
	IN `p_dialStringId` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_dialcodeSeparator` VARCHAR(50)
)
ThisSP:BEGIN

    DECLARE totaldialstringcode INT(11) DEFAULT 0;
    DECLARE     v_CodeDeckId_ INT ;
  	 DECLARE totalduplicatecode INT(11);
  	 DECLARE errormessage longtext;
	 DECLARE errorheader longtext;


	DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateDialString_ ;
	 CREATE TEMPORARY TABLE `tmp_VendorRateDialString_` (
    					`TempVendorRateID` int,
						`CodeDeckId` int ,
						`Code` varchar(50) ,
						`Description` varchar(200) ,
						`Rate` decimal(18, 6) ,
						`EffectiveDate` Datetime ,
						`EndDate` Datetime ,
						`Change` varchar(100) ,
						`ProcessId` varchar(200) ,
						`Preference` varchar(100) ,
						`ConnectionFee` decimal(18, 6),
						`Interval1` int,
						`IntervalN` int,
						`Forbidden` varchar(100) ,
						`DialStringPrefix` varchar(500),
						INDEX IX_code (code),
						INDEX IX_CodeDeckId (CodeDeckId),
						INDEX IX_Description (Description),
						INDEX IX_EffectiveDate (EffectiveDate),
						INDEX IX_DialStringPrefix (DialStringPrefix)
					);

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateDialString_2 ;
	 CREATE TEMPORARY TABLE `tmp_VendorRateDialString_2` (
    					`TempVendorRateID` int,
						`CodeDeckId` int ,
						`Code` varchar(50) ,
						`Description` varchar(200) ,
						`Rate` decimal(18, 6) ,
						`EffectiveDate` Datetime ,
						`EndDate` Datetime ,
						`Change` varchar(100) ,
						`ProcessId` varchar(200) ,
						`Preference` varchar(100) ,
						`ConnectionFee` decimal(18, 6),
						`Interval1` int,
						`IntervalN` int,
						`Forbidden` varchar(100) ,
						`DialStringPrefix` varchar(500),
						INDEX IX_code (code),
						INDEX IX_CodeDeckId (CodeDeckId),
						INDEX IX_Description (Description),
						INDEX IX_EffectiveDate (EffectiveDate),
						INDEX IX_DialStringPrefix (DialStringPrefix)
					);

					DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateDialString_3 ;
	 CREATE TEMPORARY TABLE `tmp_VendorRateDialString_3` (
    					`TempVendorRateID` int,
						`CodeDeckId` int ,
						`Code` varchar(50) ,
						`Description` varchar(200) ,
						`Rate` decimal(18, 6) ,
						`EffectiveDate` Datetime ,
						`EndDate` Datetime ,
						`Change` varchar(100) ,
						`ProcessId` varchar(200) ,
						`Preference` varchar(100) ,
						`ConnectionFee` decimal(18, 6),
						`Interval1` int,
						`IntervalN` int,
						`Forbidden` varchar(100) ,
						`DialStringPrefix` varchar(500),
						INDEX IX_code (code),
						INDEX IX_CodeDeckId (CodeDeckId),
						INDEX IX_Description (Description),
						INDEX IX_EffectiveDate (EffectiveDate),
						INDEX IX_DialStringPrefix (DialStringPrefix)
					);

	CALL prc_SplitVendorRate(p_processId,p_dialcodeSeparator);

		IF  p_effectiveImmediately = 1
      THEN
         UPDATE tmp_split_VendorRate_
     	     	SET EffectiveDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
     		   WHERE EffectiveDate < DATE_FORMAT (NOW(), '%Y-%m-%d');


   		UPDATE tmp_split_VendorRate_
     	   	SET EndDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
     		   WHERE EndDate < DATE_FORMAT (NOW(), '%Y-%m-%d');

      END IF;

		DROP TEMPORARY TABLE IF EXISTS tmp_split_VendorRate_2;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_split_VendorRate_2 as (SELECT * FROM tmp_split_VendorRate_);

 		DELETE n1 FROM tmp_split_VendorRate_ n1
			  INNER JOIN
			(
			  SELECT MAX(TempVendorRateID) AS TempVendorRateID,EffectiveDate,Code
			  FROM tmp_split_VendorRate_2 WHERE ProcessId = p_processId
				GROUP BY Code,EffectiveDate
			HAVING COUNT(*)>1
			)n2
			ON n1.Code = n2.Code
			AND n2.EffectiveDate = n1.EffectiveDate AND n1.TempVendorRateID < n2.TempVendorRateID
			WHERE n1.ProcessId = p_processId;

 		-- v4.16
		  INSERT INTO tmp_TempVendorRate_
	        SELECT DISTINCT
					    		 `TempVendorRateID`,
								 `CodeDeckId`,
			  						`Code`,
									`Description`,
									`Rate`,
									`EffectiveDate`,
									`EndDate`,
									`Change`,
									`ProcessId`,
									`Preference`,
									`ConnectionFee`,
									`Interval1`,
									`IntervalN`,
									`Forbidden`,
									`DialStringPrefix`
						 FROM tmp_split_VendorRate_
						 	 WHERE tmp_split_VendorRate_.ProcessId = p_processId;

		     SELECT CodeDeckId INTO v_CodeDeckId_
					FROM tmp_TempVendorRate_
						 WHERE ProcessId = p_processId  LIMIT 1;

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
	                                            	ELSE
	                                            		1
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
		                                            	1
		                                          END
                                          	END;


			IF  p_effectiveImmediately = 1
            THEN
               UPDATE tmp_TempVendorRate_
           	     	SET EffectiveDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
           		   WHERE EffectiveDate < DATE_FORMAT (NOW(), '%Y-%m-%d');


         		UPDATE tmp_TempVendorRate_
           	   	SET EndDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
           		   WHERE EndDate < DATE_FORMAT (NOW(), '%Y-%m-%d');

            END IF;


			SELECT count(*) INTO totalduplicatecode FROM(
				SELECT count(code) as c,code FROM tmp_TempVendorRate_  GROUP BY Code,EffectiveDate,DialStringPrefix HAVING c>1) AS tbl;


			IF  totalduplicatecode > 0
			THEN


				SELECT GROUP_CONCAT(code) into errormessage FROM(
					SELECT DISTINCT code, 1 as a FROM(
						SELECT   count(code) as c,code FROM tmp_TempVendorRate_  GROUP BY Code,EffectiveDate,DialStringPrefix HAVING c>1) AS tbl) as tbl2 GROUP by a;

				INSERT INTO tmp_JobLog_ (Message)
				  SELECT DISTINCT
				  CONCAT(code , ' DUPLICATE CODE')
				  	FROM(
						SELECT   count(code) as c,code FROM tmp_TempVendorRate_  GROUP BY Code,EffectiveDate,DialStringPrefix HAVING c>1) AS tbl;

			END IF;

   IF	totalduplicatecode = 0
   THEN


    IF p_dialstringid >0
    THEN

    		DROP TEMPORARY TABLE IF EXISTS tmp_DialString_;
    		CREATE TEMPORARY TABLE tmp_DialString_ (
				`DialStringID` INT,
				`DialString` VARCHAR(250),
				`ChargeCode` VARCHAR(250),
				`Description` VARCHAR(250),
				`Forbidden` VARCHAR(50),
				INDEX tmp_DialStringID (`DialStringID`),
	         INDEX tmp_DialStringID_ChargeCode (`DialStringID`,`ChargeCode`)
         );

         INSERT INTO tmp_DialString_
			SELECT DISTINCT
				`DialStringID`,
				`DialString`,
				`ChargeCode`,
				`Description`,
				`Forbidden`
			FROM tblDialStringCode
				WHERE DialStringID = p_dialstringid;

         SELECT  COUNT(*) as count INTO totaldialstringcode
			FROM tmp_TempVendorRate_ vr
				LEFT JOIN tmp_DialString_ ds
					ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  ))

				WHERE vr.ProcessId = p_processId
					AND ds.DialStringID IS NULL
					AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

         IF totaldialstringcode > 0
         THEN

				INSERT INTO tmp_JobLog_ (Message)
				  SELECT DISTINCT CONCAT(Code ,' ', vr.DialStringPrefix , ' No PREFIX FOUND')
				  	FROM tmp_TempVendorRate_ vr
						LEFT JOIN tmp_DialString_ ds

							ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  ))
						WHERE vr.ProcessId = p_processId
							AND ds.DialStringID IS NULL
							AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

         END IF;

         IF totaldialstringcode = 0
         THEN

				 	INSERT INTO tmp_VendorRateDialString_
					 SELECT DISTINCT
    						`TempVendorRateID`,
							`CodeDeckId`,
							`DialString`,
							CASE WHEN ds.Description IS NULL OR ds.Description = ''
								THEN
									tblTempVendorRate.Description
								ELSE
									ds.Description
							END
								AS Description,
							`Rate`,
							`EffectiveDate`,
							`EndDate`,
							`Change`,
							`ProcessId`,
							`Preference`,
							`ConnectionFee`,
							`Interval1`,
							`IntervalN`,
							tblTempVendorRate.Forbidden as Forbidden ,
							tblTempVendorRate.DialStringPrefix as DialStringPrefix
					   FROM tmp_TempVendorRate_ as tblTempVendorRate
							INNER JOIN tmp_DialString_ ds

							  	ON ( (tblTempVendorRate.Code = ds.ChargeCode AND tblTempVendorRate.DialStringPrefix = '') OR (tblTempVendorRate.DialStringPrefix != '' AND tblTempVendorRate.DialStringPrefix =  ds.DialString AND tblTempVendorRate.Code = ds.ChargeCode  ))

						 WHERE tblTempVendorRate.ProcessId = p_processId
							AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');


				--	DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateDialString_2;
				--	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRateDialString_2 as (SELECT * FROM tmp_VendorRateDialString_);

				INSERT INTO tmp_VendorRateDialString_2
				SELECT * FROM tmp_VendorRateDialString_;


			/*	 DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateDialString_3;
					CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRateDialString_3 as (
					 SELECT vrs1.* from tmp_VendorRateDialString_2 vrs1
					 LEFT JOIN tmp_VendorRateDialString_ vrs2 ON vrs1.Code=vrs2.Code AND vrs1.CodeDeckId=vrs2.CodeDeckId AND vrs1.Description=vrs2.Description AND vrs1.EffectiveDate=vrs2.EffectiveDate AND vrs1.DialStringPrefix != vrs2.DialStringPrefix
					 WHERE ( (vrs1.DialStringPrefix ='' AND vrs2.Code IS NULL) OR (vrs1.DialStringPrefix!='' AND vrs2.Code IS NOT NULL))
					);
			*/

			INSERT INTO tmp_VendorRateDialString_3
			SELECT vrs1.* from tmp_VendorRateDialString_2 vrs1
					 LEFT JOIN tmp_VendorRateDialString_ vrs2 ON vrs1.Code=vrs2.Code AND vrs1.CodeDeckId=vrs2.CodeDeckId AND vrs1.Description=vrs2.Description AND vrs1.EffectiveDate=vrs2.EffectiveDate AND vrs1.DialStringPrefix != vrs2.DialStringPrefix
					 WHERE ( (vrs1.DialStringPrefix ='' AND vrs2.Code IS NULL) OR (vrs1.DialStringPrefix!='' AND vrs2.Code IS NOT NULL));


					DELETE  FROM tmp_TempVendorRate_ WHERE  ProcessId = p_processId;

					INSERT INTO tmp_TempVendorRate_(
				    		`TempVendorRateID`,
							CodeDeckId,
							Code,
							Description,
							Rate,
							EffectiveDate,
							EndDate,
							`Change`,
							ProcessId,
							Preference,
							ConnectionFee,
							Interval1,
							IntervalN,
							Forbidden,
							DialStringPrefix
						)
					SELECT DISTINCT
			    		`TempVendorRateID`,
						`CodeDeckId`,
						`Code`,
						`Description`,
						`Rate`,
						`EffectiveDate`,
						`EndDate`,
						`Change`,
						`ProcessId`,
						`Preference`,
						`ConnectionFee`,
						`Interval1`,
						`IntervalN`,
						`Forbidden`,
						DialStringPrefix
					 FROM tmp_VendorRateDialString_3;

				  	UPDATE tmp_TempVendorRate_ as tblTempVendorRate
					JOIN tmp_DialString_ ds

					  ON ( (tblTempVendorRate.Code = ds.ChargeCode and tblTempVendorRate.DialStringPrefix = '') OR (tblTempVendorRate.DialStringPrefix != '' and tblTempVendorRate.DialStringPrefix =  ds.DialString and tblTempVendorRate.Code = ds.ChargeCode  ))
							AND tblTempVendorRate.ProcessId = p_processId
							AND ds.Forbidden = 1
					SET tblTempVendorRate.Forbidden = 'B';

					UPDATE tmp_TempVendorRate_ as  tblTempVendorRate
					JOIN tmp_DialString_ ds

						ON ( (tblTempVendorRate.Code = ds.ChargeCode and tblTempVendorRate.DialStringPrefix = '') OR (tblTempVendorRate.DialStringPrefix != '' and tblTempVendorRate.DialStringPrefix =  ds.DialString and tblTempVendorRate.Code = ds.ChargeCode  ))
							AND tblTempVendorRate.ProcessId = p_processId
							AND ds.Forbidden = 0
					SET tblTempVendorRate.Forbidden = 'UB';

			END IF;

    END IF;

END IF;



END//
DELIMITER ;









DROP PROCEDURE IF EXISTS `prc_WSProcessCodeDeck`;
DELIMITER //
CREATE PROCEDURE `prc_WSProcessCodeDeck`(
	IN `p_processId` VARCHAR(200),
	IN `p_companyId` INT
)
BEGIN

    DECLARE v_AffectedRecords_ INT DEFAULT 0;
    DECLARE   v_CodeDeckId_ INT;
    DECLARE errormessage longtext;
	 DECLARE errorheader longtext;
	 DECLARE countrycount INT DEFAULT 0;

    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	 DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
    CREATE TEMPORARY TABLE tmp_JobLog_  (
        Message longtext
    );

    SELECT CodeDeckId INTO v_CodeDeckId_ FROM tblTempCodeDeck WHERE ProcessId = p_processId AND CompanyId = p_companyId LIMIT 1;

    DELETE n1
	 FROM tblTempCodeDeck n1
	 INNER JOIN (
	 	SELECT MAX(TempCodeDeckRateID) as TempCodeDeckRateID,Code FROM tblTempCodeDeck WHERE ProcessId = p_processId
		GROUP BY Code
		HAVING COUNT(*)>1
	) n2
	 	ON n1.Code = n2.Code AND n1.TempCodeDeckRateID < n2.TempCodeDeckRateID
	WHERE n1.ProcessId = p_processId;


	 SELECT COUNT(*) INTO countrycount FROM tblTempCodeDeck WHERE ProcessId = p_processId AND Country !='';


    UPDATE tblTempCodeDeck
    SET
        tblTempCodeDeck.Interval1 = CASE WHEN tblTempCodeDeck.Interval1 is not null  and tblTempCodeDeck.Interval1 > 0
                                    THEN
                                        tblTempCodeDeck.Interval1
                                    ELSE
                                    	1
                                    END,
        tblTempCodeDeck.IntervalN = CASE WHEN tblTempCodeDeck.IntervalN is not null  and tblTempCodeDeck.IntervalN > 0
                                    THEN
                                        tblTempCodeDeck.IntervalN
                                    ELSE
                                        1
                                    END
    WHERE tblTempCodeDeck.ProcessId = p_processId;

    UPDATE tblTempCodeDeck t
	    SET t.CountryId = fnGetCountryIdByCodeAndCountry (t.Code ,t.Country)
	 WHERE t.ProcessId = p_processId ;

   IF countrycount > 0
   THEN
	  	UPDATE tblTempCodeDeck t
		    SET t.CountryId = fnGetCountryIdByCodeAndCountry (t.Code ,t.Description)
		 WHERE t.ProcessId = p_processId AND  t.CountryId IS NULL;
	END IF;

 IF ( SELECT COUNT(*)
                 FROM   tblTempCodeDeck
                 WHERE  tblTempCodeDeck.ProcessId = p_processId
                        AND tblTempCodeDeck.Action = 'D'
               ) > 0
            THEN
      DELETE  tblRate
            FROM    tblRate
                    INNER JOIN tblTempCodeDeck ON tblTempCodeDeck.Code = tblRate.Code
                                                  AND tblRate.CompanyID = p_companyId AND tblRate.CodeDeckId = v_CodeDeckId_
                    LEFT OUTER JOIN tblCustomerRate ON tblRate.RateID = tblCustomerRate.RateID
                    LEFT OUTER JOIN tblRateTableRate ON tblRate.RateID = tblRateTableRate.RateID
                    LEFT OUTER JOIN tblVendorRate ON tblRate.RateID = tblVendorRate.RateId
            WHERE   tblTempCodeDeck.Action = 'D'
          AND tblTempCodeDeck.CompanyID = p_companyId
          AND tblTempCodeDeck.CodeDeckId = v_CodeDeckId_
          AND tblTempCodeDeck.ProcessId = p_processId
                    AND tblCustomerRate.CustomerRateID IS NULL
                    AND tblRateTableRate.RateTableRateID IS NULL
                    AND tblVendorRate.VendorRateID IS NULL ;
		END IF;
      SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();


  		SELECT GROUP_CONCAT(Code) into errormessage FROM(
	      SELECT distinct tblRate.Code as Code,1 as a
	      FROM    tblRate
	                    INNER JOIN tblTempCodeDeck ON tblTempCodeDeck.Code = tblRate.Code
	      	    AND tblRate.CompanyID = p_companyId AND tblRate.CodeDeckId = v_CodeDeckId_
	          WHERE   tblTempCodeDeck.Action = 'D'
	          AND tblTempCodeDeck.ProcessId = p_processId
	          AND tblTempCodeDeck.CompanyID = p_companyId AND tblTempCodeDeck.CodeDeckId = v_CodeDeckId_)as tbl GROUP BY a;

	   IF errormessage IS NOT NULL
          THEN
                    INSERT INTO tmp_JobLog_ (Message)
                    SELECT distinct
						  CONCAT(tblRate.Code , ' FAILED TO DELETE - CODE IS IN USE')
					      FROM   tblRate
					              INNER JOIN tblTempCodeDeck ON tblTempCodeDeck.Code = tblRate.Code
					      	    AND tblRate.CompanyID = p_companyId AND tblRate.CodeDeckId = v_CodeDeckId_
					          WHERE   tblTempCodeDeck.Action = 'D'
					          AND tblTempCodeDeck.ProcessId = p_processId
					          AND tblTempCodeDeck.CompanyID = p_companyId AND tblTempCodeDeck.CodeDeckId = v_CodeDeckId_;
	 	END IF;

      UPDATE  tblRate
      JOIN tblTempCodeDeck ON tblRate.Code = tblTempCodeDeck.Code
            AND tblTempCodeDeck.ProcessId = p_processId
            AND tblRate.CompanyID = p_companyId
            AND tblRate.CodeDeckId = v_CodeDeckId_
            AND tblTempCodeDeck.Action != 'D'
		SET   tblRate.Description = tblTempCodeDeck.Description,
            tblRate.Interval1 = tblTempCodeDeck.Interval1,
            tblRate.IntervalN = tblTempCodeDeck.IntervalN;

  		IF countrycount > 0
  		THEN

	  		UPDATE  tblRate
	      JOIN tblTempCodeDeck ON tblRate.Code = tblTempCodeDeck.Code
	            AND tblTempCodeDeck.ProcessId = p_processId
	            AND tblRate.CompanyID = p_companyId
	            AND tblRate.CodeDeckId = v_CodeDeckId_
	            AND tblTempCodeDeck.Action != 'D'
			SET   tblRate.CountryID = tblTempCodeDeck.CountryId;

		END IF;

      SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

            INSERT  INTO tblRate
                    ( CountryID ,
                      CompanyID ,
                      CodeDeckId,
                      Code ,
                      Description,
                      Interval1,
                      IntervalN
                    )
                    SELECT  DISTINCT
              tblTempCodeDeck.CountryId ,
                            tblTempCodeDeck.CompanyId ,
                            tblTempCodeDeck.CodeDeckId,
                            tblTempCodeDeck.Code ,
                            tblTempCodeDeck.Description,
                            tblTempCodeDeck.Interval1,
                            tblTempCodeDeck.IntervalN
                    FROM    tblTempCodeDeck left join tblRate on(tblRate.CompanyID = p_companyId AND  tblRate.CodeDeckId = v_CodeDeckId_ AND tblTempCodeDeck.Code=tblRate.Code)
                    WHERE  tblRate.RateID is null
                            AND tblTempCodeDeck.ProcessId = p_processId
              AND tblTempCodeDeck.CompanyID = p_companyId
              AND tblTempCodeDeck.CodeDeckId = v_CodeDeckId_
                            AND tblTempCodeDeck.Action != 'D';

      SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

	 INSERT INTO tmp_JobLog_ (Message)
	 SELECT CONCAT(v_AffectedRecords_, ' Records Uploaded \n\r ' );

	DELETE  FROM tblTempCodeDeck WHERE   tblTempCodeDeck.ProcessId = p_processId;
 	 SELECT * from tmp_JobLog_;

    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    SELECT * from tmp_JobLog_ limit 0 , 20;
END//
DELIMITER ;












DROP PROCEDURE IF EXISTS `fnVendorSippySheet`;
DELIMITER //
CREATE PROCEDURE `fnVendorSippySheet`(
	IN `p_AccountID` int,
	IN `p_Trunks` longtext,
	IN `p_Effective` VARCHAR(50)
)
BEGIN
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorSippySheet_(
            RateID int,
            `Action [A|D|U|S|SA` varchar(50),
            id varchar(10),
            Prefix varchar(50),
            COUNTRY varchar(200),
            Preference int,
            `Interval 1` int,
            `Interval N` int,
            `Price 1` float,
            `Price N` float,
            `1xx Timeout` int,
            `2xx Timeout` INT,
            Huntstop int,
            Forbidden int,
            `Activation Date` varchar(10),
            `Expiration Date` varchar(10),
            AccountID int,
            TrunkID int
    );

    call vwVendorCurrentRates(p_AccountID,p_Trunks,p_Effective);

        SELECT NULL AS RateID,
               'A' AS `Action [A|D|U|S|SA]`,
               '' AS id,
               Concat(tblTrunk.Prefix , vendorRate.Code)  AS PREFIX,
               vendorRate.Description AS COUNTRY,
               5 AS Preference,
               vendorRate.`Interval 1` AS `Interval 1`,
               vendorRate.`Interval N` AS `Interval N`,
               vendorRate.Rate AS `Price 1`,
               vendorRate.Rate AS `Price N`,
               10 AS `1xx Timeout`,
               60 AS `2xx Timeout`,
               0 AS Huntstop,
               CASE WHEN (tblVendorBlocking.VendorBlockingId IS NOT NULL AND  FIND_IN_SET(vendorRate.TrunkId,p_Trunks) != 0) OR (blockCountry.VendorBlockingId IS NOT NULL AND FIND_IN_SET(vendorRate.TrunkId,p_Trunks) != 0 )
                THEN 1
                ELSE 0
                END AS Forbidden,
               'NOW' AS `Activation Date`,
               '' AS `Expiration Date`,
               tblAccount.AccountID,
               tblTrunk.TrunkID
        FROM tmp_VendorSippySheet_ AS vendorRate
        INNER JOIN tblAccount ON vendorRate.AccountId = tblAccount.AccountID
        LEFT OUTER JOIN tblVendorBlocking ON vendorRate.RateID = tblVendorBlocking.RateId
        AND tblAccount.AccountID = tblVendorBlocking.AccountId
        AND vendorRate.TrunkID = tblVendorBlocking.TrunkID
        LEFT OUTER JOIN tblVendorBlocking AS blockCountry ON vendorRate.CountryID = blockCountry.CountryId
        AND tblAccount.AccountID = blockCountry.AccountId
        AND vendorRate.TrunkID = blockCountry.TrunkID
        INNER JOIN tblTrunk ON tblTrunk.TrunkID = vendorRate.TrunkID
        WHERE vendorRate.AccountId = p_AccountID
          AND FIND_IN_SET(vendorRate.TrunkId,p_Trunks) != 0
          AND vendorRate.Rate > 0;

END//
DELIMITER ;















DROP PROCEDURE IF EXISTS `prc_CronJobGenerateM2VendorSheet`;
DELIMITER //
CREATE PROCEDURE `prc_CronJobGenerateM2VendorSheet`(
	IN `p_AccountID` INT ,
	IN `p_trunks` VARCHAR(200),
	IN `p_Effective` VARCHAR(50),
	IN `p_CustomDate` DATE
)
BEGIN
		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
    CREATE TEMPORARY TABLE tmp_VendorRate_ (
        TrunkId INT,
   	  RateId INT,
        Rate DECIMAL(18,6),
        EffectiveDate DATE,
        Interval1 INT,
        IntervalN INT,
        ConnectionFee DECIMAL(18,6),
        INDEX tmp_RateTable_RateId (`RateId`)
    );
        INSERT INTO tmp_VendorRate_
        SELECT   `TrunkID`, `RateId`, `Rate`, `EffectiveDate`, `Interval1`, `IntervalN`, `ConnectionFee`
		  FROM tblVendorRate WHERE tblVendorRate.AccountId =  p_AccountID
								AND FIND_IN_SET(tblVendorRate.TrunkId,p_trunks) != 0
                        AND
								(
					  				(p_Effective = 'Now' AND EffectiveDate <= NOW())
								  	OR
								  	(p_Effective = 'Future' AND EffectiveDate > NOW())
								  	OR
								  	(p_Effective = 'CustomDate' AND EffectiveDate <= p_CustomDate AND (EndDate IS NULL OR EndDate > p_CustomDate))
								  	OR
								  	(p_Effective = 'All')
								);

		 DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate4_;
       CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRate4_ as (select * from tmp_VendorRate_);

      DELETE n1 FROM tmp_VendorRate_ n1, tmp_VendorRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
 	   AND n1.TrunkID = n2.TrunkID
	   AND  n1.RateId = n2.RateId
	   AND
	   (
			(p_Effective = 'CustomDate' AND n1.EffectiveDate <= p_CustomDate AND n2.EffectiveDate <= p_CustomDate)
		  	OR
		  	(p_Effective != 'CustomDate' AND n1.EffectiveDate <= NOW() AND n2.EffectiveDate <= NOW())
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_m2rateall_;
    CREATE TEMPORARY TABLE tmp_m2rateall_ (
        RateID INT,
        Code VARCHAR(50),
        Description VARCHAR(200),
        Interval1 INT,
        IntervalN INT,
        ConnectionFee DECIMAL(18, 6),
        Rate DECIMAL(18, 6),
        EffectiveDate DATE
    );

     INSERT INTO tmp_m2rateall_
     SELECT Distinct
			tblRate.RateID as `RateID`,
			tblRate.Code as `Code`,
			tblRate.Description as `Description` ,
			CASE WHEN tblVendorRate.Interval1 IS NOT NULL
			   THEN tblVendorRate.Interval1
			   ElSE tblRate.Interval1
			END AS `Interval1`,
			CASE WHEN tblVendorRate.IntervalN IS NOT NULL
			   THEN tblVendorRate.IntervalN
			   ElSE tblRate.IntervalN
			END  AS `IntervalN`,
			tblVendorRate.ConnectionFee as `ConnectionFee`,
			Abs(tblVendorRate.Rate) as `Rate`,
			tblVendorRate.EffectiveDate as `EffectiveDate`
        FROM    tmp_VendorRate_ as tblVendorRate
            JOIN tblRate on tblVendorRate.RateId =tblRate.RateID;

		SELECT DISTINCT
			Description  as `Destination`,
			Code as `Prefix`,
			Rate as `Rate(USD)`,
			ConnectionFee as `Connection Fee(USD)`,
			Interval1 as `Increment`,
			IntervalN as `Minimal Time`,
			'0:00:00 'as `Start Time`,
			'23:59:59' as `End Time`,
			'' as `Week Day`,
			EffectiveDate  as `Effective from`
		FROM tmp_m2rateall_;

      SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;









DROP PROCEDURE IF EXISTS `prc_CronJobGenerateMorVendorSheet`;
DELIMITER //
CREATE PROCEDURE `prc_CronJobGenerateMorVendorSheet`(
	IN `p_AccountID` INT ,
	IN `p_trunks` VARCHAR(200),
	IN `p_Effective` VARCHAR(50),
	IN `p_CustomDate` DATE
)
BEGIN
		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
    CREATE TEMPORARY TABLE tmp_VendorRate_ (
        TrunkId INT,
   	  RateId INT,
        Rate DECIMAL(18,6),
        EffectiveDate DATE,
        Interval1 INT,
        IntervalN INT,
        ConnectionFee DECIMAL(18,6),
        INDEX tmp_RateTable_RateId (`RateId`)
    );
        INSERT INTO tmp_VendorRate_
        SELECT   `TrunkID`, `RateId`, `Rate`, `EffectiveDate`, `Interval1`, `IntervalN`, `ConnectionFee`
		  FROM tblVendorRate WHERE tblVendorRate.AccountId =  p_AccountID
								AND FIND_IN_SET(tblVendorRate.TrunkId,p_trunks) != 0
                        AND
								(
					  				(p_Effective = 'Now' AND EffectiveDate <= NOW())
								  	OR
								  	(p_Effective = 'Future' AND EffectiveDate > NOW())
								  	OR
								  	(p_Effective = 'CustomDate' AND EffectiveDate <= p_CustomDate AND (EndDate IS NULL OR EndDate > p_CustomDate))
								  	OR
								  	(p_Effective = 'All')
								);

		 DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate4_;
       CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRate4_ as (select * from tmp_VendorRate_);

      DELETE n1 FROM tmp_VendorRate_ n1, tmp_VendorRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
 	   AND n1.TrunkID = n2.TrunkID
	   AND  n1.RateId = n2.RateId
		AND
	   (
			(p_Effective = 'CustomDate' AND n1.EffectiveDate <= p_CustomDate AND n2.EffectiveDate <= p_CustomDate)
		  	OR
		  	(p_Effective != 'CustomDate' AND n1.EffectiveDate <= NOW() AND n2.EffectiveDate <= NOW())
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_morrateall_;
    CREATE TEMPORARY TABLE tmp_morrateall_ (
        RateID INT,
        Country VARCHAR(155),
        CountryCode VARCHAR(50),
        Code VARCHAR(50),
        Description VARCHAR(200),
        Interval1 INT,
        IntervalN INT,
        ConnectionFee DECIMAL(18, 6),
        Rate DECIMAL(18, 6),
        SubCode VARCHAR(50)
    );

     INSERT INTO tmp_morrateall_
     SELECT Distinct
          tblRate.RateID as `RateID`,
			  c.Country as `Country`,
			  c.ISO3 as `CountryCode`,
			  tblRate.Code as `Code`,
               tblRate.Description as `Description` ,
               CASE WHEN tblVendorRate.Interval1 IS NOT NULL
                   THEN tblVendorRate.Interval1
                   ElSE tblRate.Interval1
               END AS `Interval1`,
               CASE WHEN tblVendorRate.IntervalN IS NOT NULL
                   THEN tblVendorRate.IntervalN
                   ElSE tblRate.IntervalN
               END  AS `IntervalN`,
               tblVendorRate.ConnectionFee as `ConnectionFee`,
               Abs(tblVendorRate.Rate) as `Rate`,
               'FIX' as `SubCode`

       FROM    tmp_VendorRate_ as tblVendorRate
               JOIN tblRate on tblVendorRate.RateId =tblRate.RateID
               LEFT JOIN tblCountry as c
                   ON tblRate.CountryID = c.CountryID;

		UPDATE tmp_morrateall_
	  			SET SubCode='MOB'
	  			WHERE Description LIKE '%Mobile%';


		SELECT DISTINCT
	      Country as `Direction` ,
	      Description  as `Destination`,
		   Code as `Prefix`,
		   SubCode as `Subcode`,
		   CountryCode as `Country code`,
		   Rate as `Rate(EUR)`,
		   ConnectionFee as `Connection Fee(EUR)`,
		   Interval1 as `Increment`,
		   IntervalN as `Minimal Time`,
		   '0:00:00 'as `Start Time`,
		   '23:59:59' as `End Time`,
		   '' as `Week Day`
     FROM tmp_morrateall_;

      SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;






DROP PROCEDURE IF EXISTS `prc_CronJobGeneratePortaVendorSheet`;
DELIMITER //
CREATE PROCEDURE `prc_CronJobGeneratePortaVendorSheet`(
	IN `p_AccountID` INT ,
	IN `p_trunks` VARCHAR(200),
	IN `p_Effective` VARCHAR(50),
	IN `p_CustomDate` DATE
)
BEGIN
		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
    CREATE TEMPORARY TABLE tmp_VendorRate_ (
        TrunkId INT,
   	  RateId INT,
        Rate DECIMAL(18,6),
        EffectiveDate DATE,
        Interval1 INT,
        IntervalN INT,
        ConnectionFee DECIMAL(18,6),
        INDEX tmp_RateTable_RateId (`RateId`)
    );
        INSERT INTO tmp_VendorRate_
        SELECT   `TrunkID`, `RateId`, `Rate`,
		  DATE_FORMAT (`EffectiveDate`, '%Y-%m-%d') AS EffectiveDate,
		   `Interval1`, `IntervalN`, `ConnectionFee`
		  FROM tblVendorRate WHERE tblVendorRate.AccountId =  p_AccountID
								AND FIND_IN_SET(tblVendorRate.TrunkId,p_trunks) != 0
                        AND
								(
					  				(p_Effective = 'Now' AND EffectiveDate <= NOW())
								  	OR
								  	(p_Effective = 'Future' AND EffectiveDate > NOW())
								  	OR
								  	(p_Effective = 'CustomDate' AND EffectiveDate <= p_CustomDate AND (EndDate IS NULL OR EndDate > p_CustomDate))
								  	OR
								  	(p_Effective = 'All')
								);

		 DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate4_;
       CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRate4_ as (select * from tmp_VendorRate_);
      DELETE n1 FROM tmp_VendorRate_ n1, tmp_VendorRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
 	   AND n1.TrunkID = n2.TrunkID
	   AND  n1.RateId = n2.RateId
		AND
	   (
			(p_Effective = 'CustomDate' AND n1.EffectiveDate <= p_CustomDate AND n2.EffectiveDate <= p_CustomDate)
		  	OR
		  	(p_Effective != 'CustomDate' AND n1.EffectiveDate <= NOW() AND n2.EffectiveDate <= NOW())
		);



	DROP TEMPORARY TABLE IF EXISTS tmp_VendorArchiveCurrentRates_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorArchiveCurrentRates_(
		AccountId int,
		Code varchar(50),
		Description varchar(200),
		Rate float,
		EffectiveDate date,
		TrunkID int,
		CountryID int,
		RateID int,
		Interval1 INT,
		IntervalN varchar(100),
		ConnectionFee float,
		EndDate date
    );

	IF p_Effective = 'Now' || p_Effective = 'All' THEN

  	 	call vwVendorArchiveCurrentRates(p_AccountID,p_Trunks,p_Effective);

	END IF;

       SELECT Distinct  tblRate.Code as `Destination`,
               tblRate.Description as `Description` ,
               CASE WHEN tblVendorRate.Interval1 IS NOT NULL
                   THEN tblVendorRate.Interval1
                   ElSE tblRate.Interval1
               END AS `First Interval`,
               CASE WHEN tblVendorRate.IntervalN IS NOT NULL
                   THEN tblVendorRate.IntervalN
                   ElSE tblRate.IntervalN
               END  AS `Next Interval`,
               Abs(tblVendorRate.Rate) as `First Price`,
               Abs(tblVendorRate.Rate) as `Next Price`,
               DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d')  as `Effective From` ,
               IFNULL(Preference,5) as `Preference`,
               CASE
                   WHEN (blockCode.VendorBlockingId IS NOT NULL AND
                   	FIND_IN_SET(tblVendorRate.TrunkId,blockCode.TrunkId) != 0
                       )OR
                       (blockCountry.VendorBlockingId IS NOT NULL AND
                       FIND_IN_SET(tblVendorRate.TrunkId,blockCountry.TrunkId) != 0
                       ) THEN 'Y'
                   ELSE 'N'
               END AS `Forbidden`,
               CASE WHEN tblVendorRate.Rate < 0 THEN 'Y' ELSE '' END AS 'Payback Rate' ,
               CASE WHEN ConnectionFee > 0 THEN
						CONCAT('SEQ=',ConnectionFee,'&int1x1@price1&intNxN@priceN')
					ELSE
						''
					END as `Formula`,
					'N' AS `Discontinued`
       FROM    tmp_VendorRate_ as tblVendorRate
               JOIN tblRate on tblVendorRate.RateId =tblRate.RateID
               LEFT JOIN tblVendorBlocking as blockCode
                   ON tblVendorRate.RateID = blockCode.RateId
                   AND blockCode.AccountId = p_AccountID
                   AND tblVendorRate.TrunkID = blockCode.TrunkID
               LEFT JOIN tblVendorBlocking AS blockCountry
                   ON tblRate.CountryID = blockCountry.CountryId
                   AND blockCountry.AccountId = p_AccountID
                   AND tblVendorRate.TrunkID = blockCountry.TrunkID
					LEFT JOIN tblVendorPreference
						ON tblVendorPreference.AccountId = p_AccountID
						AND tblVendorPreference.TrunkID = tblVendorRate.TrunkID
						AND tblVendorPreference.RateId = tblVendorRate.RateId
       UNION ALL



		SELECT
					Distinct
			    	tblRate.Code AS `Destination`,
			 		tblRate.Description AS `Description` ,

			 		CASE WHEN vrd.Interval1 IS NOT NULL
                   THEN vrd.Interval1
                   ElSE tblRate.Interval1
               END AS `First Interval`,
               CASE WHEN vrd.IntervalN IS NOT NULL
                   THEN vrd.IntervalN
                   ElSE tblRate.IntervalN
               END  AS `Next Interval`,

			 		Abs(vrd.Rate) AS `First Price`,
			 		Abs(vrd.Rate) AS `Next Price`,
			 		DATE_FORMAT (vrd.EffectiveDate, '%Y-%m-%d') AS `Effective From`,
			 		'' AS `Preference`,
			 		'' AS `Forbidden`,
			 		CASE WHEN vrd.Rate < 0 THEN 'Y' ELSE '' END AS 'Payback Rate' ,
			 		CASE WHEN vrd.ConnectionFee > 0 THEN
						CONCAT('SEQ=',vrd.ConnectionFee,'&int1x1@price1&intNxN@priceN')
					ELSE
						''
					END as `Formula`,
			 		'Y' AS `Discontinued`
			FROM tmp_VendorArchiveCurrentRates_ AS vrd
	 		JOIN tblRate on vrd.RateId = tblRate.RateID
			LEFT JOIN tblVendorRate vr
						ON vrd.AccountId = vr.AccountId
							AND vrd.TrunkID = vr.TrunkID
							AND vrd.RateId = vr.RateId
					WHERE FIND_IN_SET(vrd.TrunkID,p_trunks) != 0
						AND vrd.AccountId = p_AccountID
						AND vr.VendorRateID IS NULL
						AND vrd.Rate > 0;


			/*
		    SELECT
			 		vrd.Code AS `Destination`,
			 		vrd.Description AS `Description` ,
			 		vrd.Interval1 AS `First Interval`,
			 		vrd.IntervalN AS `Next Interval`,
			 		Abs(vrd.Rate) AS `First Price`,
			 		Abs(vrd.Rate) AS `Next Price`,
			 		vrd.EffectiveDate AS `Effective From`,
			 		'' AS `Preference`,
			 		'' AS `Forbidden`,
			 		CASE WHEN vrd.Rate < 0 THEN 'Y' ELSE '' END AS 'Payback Rate' ,
			 		CASE WHEN vrd.ConnectionFee > 0 THEN
						CONCAT('SEQ=',vrd.ConnectionFee,'&int1x1@price1&intNxN@priceN')
					ELSE
						''
					END as `Formula`,
			 		'Y' AS `Discontinued`
			  FROM tblVendorRateDiscontinued vrd
					LEFT JOIN tblVendorRate vr
						ON vrd.AccountId = vr.AccountId
							AND vrd.TrunkID = vr.TrunkID
							AND vrd.RateId = vr.RateId
					WHERE FIND_IN_SET(vrd.TrunkID,p_trunks) != 0
						AND vrd.AccountId = p_AccountID
						AND vr.VendorRateID IS NULL ;
*/


      SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;







DROP PROCEDURE IF EXISTS `vwVendorCurrentRates`;
DELIMITER //
CREATE PROCEDURE `vwVendorCurrentRates`(
	IN `p_AccountID` INT,
	IN `p_Trunks` LONGTEXT,
	IN `p_Effective` VARCHAR(50),
	IN `p_CustomDate` DATE
)
BEGIN

	DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates_(
		AccountId int,
		Code varchar(50),
		Description varchar(200),
		Rate float,
		EffectiveDate date,
		TrunkID int,
		CountryID int,
		RateID int,
		Interval1 INT,
		IntervalN varchar(100),
		ConnectionFee float,
		EndDate date
    );

    DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
    CREATE TEMPORARY TABLE tmp_VendorRate_ (
        TrunkId INT,
	 	  RateId INT,
        Rate DECIMAL(18,6),
        EffectiveDate DATE,
        Interval1 INT,
        IntervalN INT,
        ConnectionFee DECIMAL(18,6),
        EndDate date,
        INDEX tmp_RateTable_RateId (`RateId`)
    );
        INSERT INTO tmp_VendorRate_
        SELECT   `TrunkID`, `RateId`, `Rate`, `EffectiveDate`, `Interval1`, `IntervalN`, `ConnectionFee` , tblVendorRate.EndDate
		  FROM tblVendorRate WHERE tblVendorRate.AccountId =  p_AccountID
								AND FIND_IN_SET(tblVendorRate.TrunkId,p_Trunks) != 0
                        AND
								(
					  				(p_Effective = 'Now' AND EffectiveDate <= NOW() AND (EndDate IS NULL OR EndDate > NOW() ))
								  	OR
								  	(p_Effective = 'Future' AND EffectiveDate > NOW() AND ( EndDate IS NULL OR EndDate > NOW() ))
								  	OR
								  	(p_Effective = 'CustomDate' AND EffectiveDate <= p_CustomDate AND (EndDate IS NULL OR EndDate > p_CustomDate))
								  	OR
								  	(p_Effective = 'All'  )
								);

    DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate4_;
       CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRate4_ as (select * from tmp_VendorRate_);
      DELETE n1 FROM tmp_VendorRate_ n1, tmp_VendorRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
 	   AND n1.TrunkID = n2.TrunkID
	   AND  n1.RateId = n2.RateId
		AND
	   (
			(p_Effective = 'CustomDate' AND n1.EffectiveDate <= p_CustomDate AND n2.EffectiveDate <= p_CustomDate)
		  	OR
		  	(p_Effective != 'CustomDate' AND n1.EffectiveDate <= NOW() AND n2.EffectiveDate <= NOW())
		);


    INSERT INTO tmp_VendorCurrentRates_
    SELECT DISTINCT
    p_AccountID,
    r.Code,
    r.Description,
    v_1.Rate,
    DATE_FORMAT (v_1.EffectiveDate, '%Y-%m-%d') AS EffectiveDate,
    v_1.TrunkID,
    r.CountryID,
    r.RateID,
   	CASE WHEN v_1.Interval1 is not null
   		THEN v_1.Interval1
    	ELSE r.Interval1
    END as  Interval1,
    CASE WHEN v_1.IntervalN is not null
    	THEN v_1.IntervalN
        ELSE r.IntervalN
    END IntervalN,
    v_1.ConnectionFee,
    v_1.EndDate
    FROM tmp_VendorRate_ AS v_1
	INNER JOIN tblRate AS r
    	ON r.RateID = v_1.RateId;

END//
DELIMITER ;







DROP PROCEDURE IF EXISTS `vwVendorVersion3VosSheet`;
DELIMITER //
CREATE PROCEDURE `vwVendorVersion3VosSheet`(
	IN `p_AccountID` INT,
	IN `p_Trunks` LONGTEXT,
	IN `p_Effective` VARCHAR(50),
	IN `p_CustomDate` DATE
)
BEGIN



	DROP TEMPORARY TABLE IF EXISTS tmp_VendorVersion3VosSheet_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorVersion3VosSheet_(
			RateID int,
			`Rate Prefix` varchar(50),
			`Area Prefix` varchar(50),
			`Rate Type` varchar(50),
			`Area Name` varchar(200),
			`Billing Rate` float,
			`Billing Cycle` int,
			`Minute Cost` float,
			`Lock Type` varchar(50),
			`Section Rate` varchar(50),
			`Billing Rate for Calling Card Prompt` float,
			`Billing Cycle for Calling Card Prompt` INT,
			AccountID int,
			TrunkID int,
			EffectiveDate date,
			EndDate date
	);


	DROP TEMPORARY TABLE IF EXISTS tmp_VendorArhiveVersion3VosSheet_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorArhiveVersion3VosSheet_(
			RateID int,
			`Rate Prefix` varchar(50),
			`Area Prefix` varchar(50),
			`Rate Type` varchar(50),
			`Area Name` varchar(200),
			`Billing Rate` float,
			`Billing Cycle` int,
			`Minute Cost` float,
			`Lock Type` varchar(50),
			`Section Rate` varchar(50),
			`Billing Rate for Calling Card Prompt` float,
			`Billing Cycle for Calling Card Prompt` INT,
			AccountID int,
			TrunkID int,
			EffectiveDate date,
			EndDate date
	);


	 Call vwVendorCurrentRates(p_AccountID,p_Trunks,p_Effective,p_CustomDate);


INSERT INTO tmp_VendorVersion3VosSheet_
SELECT


    NULL AS RateID,
    IFNULL(tblTrunk.RatePrefix, '') AS `Rate Prefix`,
    Concat('' , IFNULL(tblTrunk.AreaPrefix, '') , vendorRate.Code) AS `Area Prefix`,
    'International' AS `Rate Type`,
    vendorRate.Description AS `Area Name`,
    vendorRate.Rate / 60 AS `Billing Rate`,
    vendorRate.IntervalN AS `Billing Cycle`,
    CAST(vendorRate.Rate AS DECIMAL(18, 5)) AS `Minute Cost`,
    CASE
        WHEN (tblVendorBlocking.VendorBlockingId IS NOT NULL AND
        FIND_IN_SET(vendorRate.TrunkId,tblVendorBlocking.TrunkId) != 0
             OR
            (blockCountry.VendorBlockingId IS NOT NULL AND
             FIND_IN_SET(vendorRate.TrunkId,blockCountry.TrunkId) != 0
            )) THEN 'No Lock'
        ELSE 'No Lock'
    END
    AS `Lock Type`,
        CASE WHEN vendorRate.Interval1 != vendorRate.IntervalN
                                      THEN
                    Concat('0,', vendorRate.Rate, ',',vendorRate.Interval1)
                                      ELSE ''
                                 END as `Section Rate`,
    0 AS `Billing Rate for Calling Card Prompt`,
    0 AS `Billing Cycle for Calling Card Prompt`,
    tblAccount.AccountID,
    vendorRate.TrunkId,
    vendorRate.EffectiveDate,
    vendorRate.EndDate
FROM tmp_VendorCurrentRates_ AS vendorRate
INNER JOIN tblAccount
    ON vendorRate.AccountId = tblAccount.AccountID
LEFT OUTER JOIN tblVendorBlocking
    ON vendorRate.TrunkId = tblVendorBlocking.TrunkID
    AND vendorRate.RateID = tblVendorBlocking.RateId
    AND tblAccount.AccountID = tblVendorBlocking.AccountId
LEFT OUTER JOIN tblVendorBlocking AS blockCountry
    ON vendorRate.TrunkId = blockCountry.TrunkID
    AND vendorRate.CountryID = blockCountry.CountryId
    AND tblAccount.AccountID = blockCountry.AccountId
INNER JOIN tblTrunk
    ON tblTrunk.TrunkID = vendorRate.TrunkId
WHERE (vendorRate.Rate > 0);


	 -- for archive rates
	 IF p_Effective != 'Now' THEN

		 	call vwVendorArchiveCurrentRates(p_AccountID,p_Trunks,p_Effective);

			INSERT INTO tmp_VendorArhiveVersion3VosSheet_
			SELECT


			    NULL AS RateID,
			    IFNULL(tblTrunk.RatePrefix, '') AS `Rate Prefix`,
			    Concat('' , IFNULL(tblTrunk.AreaPrefix, '') , vendorArchiveRate.Code) AS `Area Prefix`,
			    'International' AS `Rate Type`,
			    vendorArchiveRate.Description AS `Area Name`,
			    vendorArchiveRate.Rate / 60 AS `Billing Rate`,
			    vendorArchiveRate.IntervalN AS `Billing Cycle`,
			    CAST(vendorArchiveRate.Rate AS DECIMAL(18, 5)) AS `Minute Cost`,
			    'No Lock'   AS `Lock Type`,
			     CASE WHEN vendorArchiveRate.Interval1 != vendorArchiveRate.IntervalN THEN
				           Concat('0,', vendorArchiveRate.Rate, ',',vendorArchiveRate.Interval1)
			   	ELSE ''
			    END as `Section Rate`,
			    0 AS `Billing Rate for Calling Card Prompt`,
			    0 AS `Billing Cycle for Calling Card Prompt`,
			    tblAccount.AccountID,
			    vendorArchiveRate.TrunkId,
			    vendorArchiveRate.EffectiveDate,
			    vendorArchiveRate.EndDate
			FROM tmp_VendorArchiveCurrentRates_ AS vendorArchiveRate
			Left join tmp_VendorVersion3VosSheet_ vendorRate
				 ON vendorArchiveRate.AccountId = vendorRate.AccountID
				 AND vendorArchiveRate.AccountId = vendorRate.TrunkID
 				 AND vendorArchiveRate.RateID = vendorRate.RateID

			INNER JOIN tblAccount
			    ON vendorArchiveRate.AccountId = tblAccount.AccountID
			INNER JOIN tblTrunk
			    ON tblTrunk.TrunkID = vendorArchiveRate.TrunkId
			WHERE vendorRate.RateID is Null AND -- remove all archive rates which are exists in VendorRate
			(vendorArchiveRate.Rate > 0);

	 END IF;

END//
DELIMITER ;







DROP PROCEDURE IF EXISTS `prc_WSGenerateVendorVersion3VosSheet`;
DELIMITER //
CREATE PROCEDURE `prc_WSGenerateVendorVersion3VosSheet`(
	IN `p_VendorID` INT ,
	IN `p_Trunks` varchar(200) ,
	IN `p_Effective` VARCHAR(50),
	IN `p_Format` VARCHAR(50),
	IN `p_CustomDate` DATE
)
BEGIN
         SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

        call vwVendorVersion3VosSheet(p_VendorID,p_Trunks,p_Effective,p_CustomDate);

        IF p_Effective = 'Now' OR p_Format = 'Vos 2.0'
		  THEN

	        SELECT  `Rate Prefix` ,
	                `Area Prefix` ,
	                `Rate Type` ,
	                `Area Name` ,
	                `Billing Rate` ,
	                `Billing Cycle`,
	                `Minute Cost` ,
	                `Lock Type` ,
	                `Section Rate` ,
	                `Billing Rate for Calling Card Prompt` ,
	                `Billing Cycle for Calling Card Prompt`
	        FROM    tmp_VendorVersion3VosSheet_
	       -- WHERE   AccountID = p_VendorID
	       -- AND  FIND_IN_SET(TrunkId,p_Trunks)!= 0
	        ORDER BY `Rate Prefix`;

        END IF;

        IF ( (p_Effective = 'Future' OR p_Effective = 'All' OR p_Effective = 'CustomDate') AND p_Format = 'Vos 3.2'  )
		  THEN

				DROP TEMPORARY TABLE IF EXISTS tmp_VendorVersion3VosSheet2_ ;
				CREATE TEMPORARY TABLE tmp_VendorVersion3VosSheet2_ SELECT * FROM tmp_VendorVersion3VosSheet_;

				SELECT
					 	 `Time of timing replace`,
						 `Mode of timing replace`,
			  			 `Rate Prefix` ,
	                `Area Prefix` ,
	                `Rate Type` ,
	                `Area Name` ,
	                `Billing Rate` ,
	                `Billing Cycle`,
	                `Minute Cost` ,
	                `Lock Type` ,
	                `Section Rate` ,
	                `Billing Rate for Calling Card Prompt` ,
	                `Billing Cycle for Calling Card Prompt`
	        FROM (
					  SELECT  CONCAT(EffectiveDate,' 00:00') as `Time of timing replace`,
								 'Append replace' as `Mode of timing replace`,
					  			 `Rate Prefix` ,
			                `Area Prefix` ,
			                `Rate Type` ,
			                `Area Name` ,
			                `Billing Rate` ,
			                `Billing Cycle`,
			                `Minute Cost` ,
			                `Lock Type` ,
			                `Section Rate` ,
			                `Billing Rate for Calling Card Prompt` ,
			                `Billing Cycle for Calling Card Prompt`
			        FROM    tmp_VendorVersion3VosSheet2_
			        -- WHERE   AccountID = p_VendorID
			       -- AND  FIND_IN_SET(TrunkId,p_Trunks)!= 0
			       -- ORDER BY `Rate Prefix`

			   	UNION ALL

			        SELECT

					  	  CONCAT(EndDate,' 00:00') as `Time of timing replace`,
						 	'Delete' as `Mode of timing replace`,
					  		`Rate Prefix` ,
			                `Area Prefix` ,
			                `Rate Type` ,
			                `Area Name` ,
			                `Billing Rate` ,
			                `Billing Cycle`,
			                `Minute Cost` ,
			                `Lock Type` ,
			                `Section Rate` ,
			                `Billing Rate for Calling Card Prompt` ,
			                `Billing Cycle for Calling Card Prompt`
			        FROM    tmp_VendorVersion3VosSheet_
			        WHERE  EndDate is not null
					  -- AccountID = p_VendorID
			      --  AND  FIND_IN_SET(TrunkId,p_Trunks) != 0
			      --  ORDER BY `Rate Prefix`;

			   	UNION ALL

			        -- archive records
			        SELECT
			        		distinct
					  	  CONCAT(EndDate,' 00:00') as `Time of timing replace`,
						 	'Delete' as `Mode of timing replace`,
					  		`Rate Prefix` ,
			                `Area Prefix` ,
			                `Rate Type` ,
			                `Area Name` ,
			                `Billing Rate` ,
			                `Billing Cycle`,
			                `Minute Cost` ,
			                `Lock Type` ,
			                `Section Rate` ,
			                `Billing Rate for Calling Card Prompt` ,
			                `Billing Cycle for Calling Card Prompt`
			        FROM    tmp_VendorArhiveVersion3VosSheet_

					  /*WHERE
					     AccountID = p_VendorID
			        AND  FIND_IN_SET(TrunkId,p_Trunks) != 0
			        AND EndDate is not null
			        */
			      --  ORDER BY `Rate Prefix`;


	      ) tmp
	      ORDER BY `Rate Prefix`;



     END IF;


/*
query replaced on above condition

        IF p_Effective = 'All' AND p_Format = 'Vos 3.2'
		  THEN

	        SELECT  CONCAT(tmp_VendorVersion3VosSheet_.EffectiveDate,' 00:00') as `Time of timing replace`,
						 'Append replace' as `Mode of timing replace`,
			  			 `Rate Prefix` ,
	                `Area Prefix` ,
	                `Rate Type` ,
	                `Area Name` ,
	                `Billing Rate` ,
	                `Billing Cycle`,
	                `Minute Cost` ,
	                `Lock Type` ,
	                `Section Rate` ,
	                `Billing Rate for Calling Card Prompt` ,
	                `Billing Cycle for Calling Card Prompt`
	        FROM    tmp_VendorVersion3VosSheet_
	        WHERE   AccountID = p_VendorID
	        AND  FIND_IN_SET(TrunkId,p_Trunks)!= 0
	        ORDER BY `Rate Prefix`;

        END IF;
*/


        SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;








DROP PROCEDURE IF EXISTS `prc_WSGenerateVendorSippySheet`;
DELIMITER //
CREATE PROCEDURE `prc_WSGenerateVendorSippySheet`(
	IN `p_VendorID` INT  ,
	IN `p_Trunks` varchar(200),
	IN `p_Effective` VARCHAR(50),
	IN `p_CustomDate` DATE
)
BEGIN

		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		call vwVendorSippySheet(p_VendorID,p_Trunks,p_Effective,p_CustomDate);

		SELECT
			`Action [A|D|U|S|SA`,
			id ,
			vendorRate.Prefix,
			COUNTRY,
			Preference ,
			`Interval 1` ,
			`Interval N` ,
			`Price 1` ,
			`Price N` ,
			`1xx Timeout` ,
			`2xx Timeout` ,
			`Huntstop` ,
			Forbidden ,
			`Activation Date` ,
			`Expiration Date`
		FROM    tmp_VendorSippySheet_ vendorRate
		WHERE   vendorRate.AccountId = p_VendorID
						And  FIND_IN_SET(vendorRate.TrunkId,p_Trunks) != 0;

		/*

    SELECT
     `Action [A|D|U|S|SA`,
            id ,
            Prefix,
            COUNTRY,
            Preference ,
            `Interval 1` ,
            `Interval N` ,
            `Price 1` ,
            `Price N` ,
            `1xx Timeout` ,
            `2xx Timeout` ,
            `Huntstop` ,
            Forbidden ,
            `Activation Date` ,
            `Expiration Date`
    FROM
    (
      SELECT
       `Action [A|D|U|S|SA`,
              id ,
              vendorRate.Prefix,
              COUNTRY,
              Preference ,
              `Interval 1` ,
              `Interval N` ,
              `Price 1` ,
              `Price N` ,
              `1xx Timeout` ,
              `2xx Timeout` ,
              `Huntstop` ,
              Forbidden ,
              `Activation Date` ,
              `Expiration Date`
      FROM    tmp_VendorSippySheet_ vendorRate

      UNION ALL

      SELECT
       `Action [A|D|U|S|SA`,
              id ,
              vendorRate.Prefix,
              COUNTRY,
              Preference ,
              `Interval 1` ,
              `Interval N` ,
              `Price 1` ,
              `Price N` ,
              `1xx Timeout` ,
              `2xx Timeout` ,
              `Huntstop` ,
              Forbidden ,
              `Activation Date` ,
              `Expiration Date`
      FROM    tmp_VendorArhiveSippySheet_ vendorRate
  ) tmp;

    -- WHERE   vendorRate.AccountId = p_VendorID
    -- And  FIND_IN_SET(vendorRate.TrunkId,p_Trunks) != 0;

    */

		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	END//
DELIMITER ;








DROP PROCEDURE IF EXISTS `vwVendorSippySheet`;
DELIMITER //
CREATE PROCEDURE `vwVendorSippySheet`(
	IN `p_AccountID` INT,
	IN `p_Trunks` LONGTEXT,
	IN `p_Effective` VARCHAR(50),
	IN `p_CustomDate` DATE
)
BEGIN

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorSippySheet_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorSippySheet_(
			RateID int,
			`Action [A|D|U|S|SA` varchar(50),
			id varchar(10),
			Prefix varchar(50),
			COUNTRY varchar(200),
			Preference int,
			`Interval 1` int,
			`Interval N` int,
			`Price 1` float,
			`Price N` float,
			`1xx Timeout` int,
			`2xx Timeout` INT,
			Huntstop int,
			Forbidden int,
			`Activation Date` varchar(10),
			`Expiration Date` varchar(10),
			AccountID int,
			TrunkID int
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorArhiveSippySheet_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorArhiveSippySheet_(
			RateID int,
			`Action [A|D|U|S|SA` varchar(50),
			id varchar(10),
			Prefix varchar(50),
			COUNTRY varchar(200),
			Preference int,
			`Interval 1` int,
			`Interval N` int,
			`Price 1` float,
			`Price N` float,
			`1xx Timeout` int,
			`2xx Timeout` INT,
			Huntstop int,
			Forbidden int,
			`Activation Date` varchar(10),
			`Expiration Date` varchar(10),
			AccountID int,
			TrunkID int
		);

		call vwVendorCurrentRates(p_AccountID,p_Trunks,p_Effective,p_CustomDate);

		INSERT INTO tmp_VendorSippySheet_
			SELECT
				NULL AS RateID,
				'A' AS `Action [A|D|U|S|SA`,
				'' AS id,
				Concat('' , tblTrunk.Prefix ,vendorRate.Code) AS Prefix,
				vendorRate.Description AS COUNTRY,
				IFNULL(tblVendorPreference.Preference,5) as Preference,
				vendorRate.Interval1 as `Interval 1`,
				vendorRate.IntervalN as `Interval N`,
				vendorRate.Rate AS `Price 1`,
				vendorRate.Rate AS `Price N`,
				10 AS `1xx Timeout`,
				60 AS `2xx Timeout`,
				0 AS Huntstop,
				CASE
				WHEN (tblVendorBlocking.VendorBlockingId IS NOT NULL AND
							FIND_IN_SET(vendorRate.TrunkId,tblVendorBlocking.TrunkId) != 0
							OR
							(blockCountry.VendorBlockingId IS NOT NULL AND
							 FIND_IN_SET(vendorRate.TrunkId,blockCountry.TrunkId) != 0
							)
				) THEN 1
				ELSE 0
				END  AS Forbidden,
				'NOW' AS `Activation Date`,
				'' AS `Expiration Date`,
				-- EndDate AS `Expiration Date`,
				tblAccount.AccountID,
				tblTrunk.TrunkID
			FROM tmp_VendorCurrentRates_ AS vendorRate
				INNER JOIN tblAccount
					ON vendorRate.AccountId = tblAccount.AccountID
				LEFT OUTER JOIN tblVendorBlocking
					ON vendorRate.RateID = tblVendorBlocking.RateId
						 AND tblAccount.AccountID = tblVendorBlocking.AccountId
						 AND vendorRate.TrunkID = tblVendorBlocking.TrunkID
				LEFT OUTER JOIN tblVendorBlocking AS blockCountry
					ON vendorRate.CountryID = blockCountry.CountryId
						 AND tblAccount.AccountID = blockCountry.AccountId
						 AND vendorRate.TrunkID = blockCountry.TrunkID
				LEFT JOIN tblVendorPreference
					ON tblVendorPreference.AccountId = vendorRate.AccountId
						 AND tblVendorPreference.TrunkID = vendorRate.TrunkID
						 AND tblVendorPreference.RateId = vendorRate.RateID
				INNER JOIN tblTrunk
					ON tblTrunk.TrunkID = vendorRate.TrunkID
			WHERE (vendorRate.Rate > 0);

		/*	IF p_Effective = 'Now' || p_Effective = 'All' THEN

        -- for archive rates
        call vwVendorArchiveCurrentRates(p_AccountID,p_Trunks,p_Effective);

        INSERT INTO tmp_VendorArhiveSippySheet_
        SELECT
          NULL AS RateID,
            'A' AS `Action [A|D|U|S|SA`,
            '' AS id,
            Concat('' , tblTrunk.Prefix ,vendorArchiveRate.Code) AS Prefix,
            vendorArchiveRate.Description AS COUNTRY,
            IFNULL(tblVendorPreference.Preference,5) as Preference,
            vendorArchiveRate.Interval1 as `Interval 1`,
            vendorArchiveRate.IntervalN as `Interval N`,
            vendorArchiveRate.Rate AS `Price 1`,
            vendorArchiveRate.Rate AS `Price N`,
            10 AS `1xx Timeout`,
            60 AS `2xx Timeout`,
            0 AS Huntstop,
            CASE
                WHEN (tblVendorBlocking.VendorBlockingId IS NOT NULL AND
                  FIND_IN_SET(vendorArchiveRate.TrunkId,tblVendorBlocking.TrunkId) != 0
              OR
                    (blockCountry.VendorBlockingId IS NOT NULL AND
                    FIND_IN_SET(vendorArchiveRate.TrunkId,blockCountry.TrunkId) != 0
                    )
                    ) THEN 1
                ELSE 0
            END  AS Forbidden,
            'NOW' AS `Activation Date`,
            EndDate AS `Expiration Date`,
            tblAccount.AccountID,
            tblTrunk.TrunkID
        FROM tmp_VendorArchiveCurrentRates_ AS vendorArchiveRate
        Left join tmp_VendorSippySheet_ vendorRate
           ON vendorArchiveRate.AccountId = vendorRate.AccountID
           AND vendorArchiveRate.AccountId = vendorRate.TrunkID
           AND vendorArchiveRate.RateID = vendorRate.RateID

        INNER JOIN tblAccount
            ON vendorArchiveRate.AccountId = tblAccount.AccountID
        LEFT OUTER JOIN tblVendorBlocking
            ON vendorArchiveRate.RateID = tblVendorBlocking.RateId
            AND tblAccount.AccountID = tblVendorBlocking.AccountId
            AND vendorArchiveRate.TrunkID = tblVendorBlocking.TrunkID
        LEFT OUTER JOIN tblVendorBlocking AS blockCountry
            ON vendorArchiveRate.CountryID = blockCountry.CountryId
            AND tblAccount.AccountID = blockCountry.AccountId
            AND vendorArchiveRate.TrunkID = blockCountry.TrunkID
        LEFT JOIN tblVendorPreference
          ON tblVendorPreference.AccountId = vendorArchiveRate.AccountId
          AND tblVendorPreference.TrunkID = vendorArchiveRate.TrunkID
          AND tblVendorPreference.RateId = vendorArchiveRate.RateID
        INNER JOIN tblTrunk
            ON tblTrunk.TrunkID = vendorArchiveRate.TrunkID
        WHERE vendorRate.RateID is Null AND -- remove all archive rates which are exists in VendorRate
        (vendorArchiveRate.Rate > 0);

      END IF;

    */
END//
DELIMITER ;