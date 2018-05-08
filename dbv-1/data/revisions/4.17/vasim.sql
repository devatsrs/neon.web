use Ratemanagement3;

ALTER TABLE `tblBillingClass`
	ADD COLUMN `RoundChargesCDR` INT(11) NULL DEFAULT NULL AFTER `RoundChargesAmount`;



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
			`Activation Date` varchar(20),
			`Expiration Date` varchar(20),
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
			`Activation Date` varchar(20),
			`Expiration Date` varchar(20),
			AccountID int,
			TrunkID int
		);

		call vwVendorCurrentRates(p_AccountID,p_Trunks,p_Effective,p_CustomDate);

		INSERT INTO tmp_VendorSippySheet_
			SELECT
				NULL AS RateID,
				CASE WHEN EndDate IS NOT NULL THEN
					'SA'
				ELSE
				'A'
				END AS `Action [A|D|U|S|SA`,
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
				DATE_FORMAT( EffectiveDate, '%Y-%m-%d %H:%i:%s' ) AS `Activation Date`,
				DATE_FORMAT( EndDate, '%Y-%m-%d %H:%i:%s' )  AS `Expiration Date`,

				/* 'NOW' AS `Activation Date`,
				  '' AS `Expiration Date`,
				 */

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











DROP PROCEDURE IF EXISTS `prc_getDiscontinuedVendorRateGrid`;
DELIMITER //
CREATE PROCEDURE `prc_getDiscontinuedVendorRateGrid`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_TrunkID` INT,
	IN `p_CountryID` INT,
	IN `p_Code` VARCHAR(50),
	IN `p_Description` VARCHAR(50),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
BEGIN
	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
   CREATE TEMPORARY TABLE tmp_VendorRate_ (
        VendorRateID INT,
        Code VARCHAR(50),
        Description VARCHAR(200),
		  ConnectionFee VARCHAR(50),
        Interval1 INT,
        IntervalN INT,
        Rate DECIMAL(18, 6),
        EffectiveDate DATE,
        EndDate DATE,
        updated_at DATETIME,
        updated_by VARCHAR(50),
        INDEX tmp_VendorRate_RateID (`Code`)
   );

   INSERT INTO tmp_VendorRate_
		SELECT
			vra.VendorRateID,
			r.Code,
			r.Description,
			'' AS ConnectionFee,
			CASE WHEN vra.Interval1 IS NOT NULL THEN vra.Interval1 ELSE r.Interval1 END AS Interval1,
			CASE WHEN vra.IntervalN IS NOT NULL THEN vra.IntervalN ELSE r.IntervalN END AS IntervalN,
			vra.Rate,
			vra.EffectiveDate,
			vra.EndDate,
			vra.created_at AS updated_at,
			vra.created_by AS updated_by
		FROM
			tblVendorRateArchive vra
		JOIN
			tblRate r ON r.RateID=vra.RateId
		LEFT JOIN
			tblVendorRate vr ON vr.AccountId = vra.AccountId AND vr.TrunkID = vra.TrunkID AND vr.RateId = vra.RateId
		WHERE
			r.CompanyID = p_CompanyID AND
			vra.TrunkID = p_TrunkID AND
			vra.AccountId = p_AccountID AND
			(p_CountryID IS NULL OR r.CountryID = p_CountryID) AND
			(p_code IS NULL OR r.Code LIKE REPLACE(p_code, '*', '%')) AND
			(p_description IS NULL OR r.Description LIKE REPLACE(p_description, '*', '%')) AND
			vr.VendorRateID is NULL;

	IF p_isExport = 0
	THEN
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRate2_ as (select * from tmp_VendorRate_);
		DELETE
			n1
		FROM
			tmp_VendorRate_ n1, tmp_VendorRate2_ n2
		WHERE
			n1.Code = n2.Code AND n1.VendorRateID < n2.VendorRateID;

 		SELECT
			VendorRateID,
			Code,
			Description,
			ConnectionFee,
			Interval1,
			IntervalN,
			Rate,
			EffectiveDate,
			EndDate,
			updated_at,
			updated_by
		FROM
			tmp_VendorRate_
		ORDER BY
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN Description
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN Description
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateDESC') THEN Rate
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateASC') THEN Rate
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeDESC') THEN ConnectionFee
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeASC') THEN ConnectionFee
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Interval1DESC') THEN Interval1
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Interval1ASC') THEN Interval1
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNDESC') THEN IntervalN
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNASC') THEN IntervalN
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateDESC') THEN EffectiveDate
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateASC') THEN EffectiveDate
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateDESC') THEN EndDate
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateASC') THEN EndDate
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN updated_at
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN updated_at
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byDESC') THEN updated_by
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byASC') THEN updated_by
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'VendorRateIDDESC') THEN VendorRateID
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'VendorRateIDASC') THEN VendorRateID
			END ASC
		LIMIT
			p_RowspPage
		OFFSET
			v_OffSet_;

		SELECT
			COUNT(code) AS totalcount
		FROM tmp_VendorRate_;

	END IF;

   IF p_isExport = 1
	THEN
		SELECT
			Code,
			Description,
			Rate,
			EffectiveDate,
			EndDate,
			updated_at AS `Modified Date`,
			updated_by AS `Modified By`

		FROM tmp_VendorRate_;
	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;







DROP PROCEDURE IF EXISTS `prc_DeleteTicketGroup`;
DELIMITER //
CREATE PROCEDURE `prc_DeleteTicketGroup`(
	IN `p_CompanyID` INT,
	IN `p_GroupID` INT
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

-- 1
-- delete tblTicketLog

	DELETE tl FROM
		tblTicketLog tl
		inner join tblTickets t
		on tl.TicketID = t.TicketID
	where t.`Group` = p_GroupID
		AND t.CompanyID = p_CompanyID;

-- 2
-- delete tblTicketDashboardTimeline

--	DELETE FROM tblTicketDashboardTimeline where GroupID = p_GroupID AND CompanyID = p_CompanyID;

-- 3
-- delete tblTicketsDetails

	DELETE td FROM
		tblTicketsDetails td
		inner join tblTickets t
		on td.TicketID = t.TicketID
	where t.`Group` = p_GroupID
		AND t.CompanyID = p_CompanyID;


-- 4
-- delete tblTicketsDeletedLog

	DELETE FROM tblTicketsDeletedLog where tblTicketsDeletedLog.`Group` = p_GroupID AND CompanyID = p_CompanyID;

-- 5
-- delete AccountEmailLogDeletedLog

	DELETE ae FROM
		AccountEmailLogDeletedLog ae
		inner join tblTickets t
		on ae.TicketID = t.TicketID
	where t.`Group` = p_GroupID
		AND t.CompanyID = p_CompanyID;


-- 6
-- delete tblTicketsDetails
	DELETE ae FROM
		AccountEmailLog ae
		inner join tblTickets t
		on ae.TicketID = t.TicketID
	where t.`Group` = p_GroupID
		AND t.CompanyID = p_CompanyID;

-- 7
-- delete tblTickets

	DELETE FROM tblTickets where tblTickets.`Group` = p_GroupID AND CompanyID = p_CompanyID;

-- 8
-- delete tblTicketGroups

	DELETE FROM tblTicketGroups where GroupID = p_GroupID AND CompanyID = p_CompanyID;


	DELETE FROM tblTicketGroupAgents where GroupID = p_GroupID;



SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;












DROP PROCEDURE IF EXISTS `prc_ArchiveOldVendorRate`;
DELIMITER //
CREATE PROCEDURE `prc_ArchiveOldVendorRate`(
	IN `p_AccountIds` longtext,
	IN `p_TrunkIds` longtext,
	IN `p_DeletedBy` TEXT
)
BEGIN
 	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


	 /*1. Move Rates which EndDate <= now() */


	INSERT INTO tblVendorRateArchive
   SELECT DISTINCT  null , -- Primary Key column
							`VendorRateID`,
							`AccountId`,
							`TrunkID`,
							`RateId`,
							`Rate`,
							`EffectiveDate`,
							IFNULL(`EndDate`,date(now())) as EndDate,
							`updated_at`,
							now() as `created_at`,
							p_DeletedBy AS `created_by`,
							`updated_by`,
							`Interval1`,
							`IntervalN`,
							`ConnectionFee`,
							`MinimumCost`,
	  concat('Ends Today rates @ ' , now() ) as `Notes`
      FROM tblVendorRate
      WHERE  FIND_IN_SET(AccountId,p_AccountIds) != 0 AND FIND_IN_SET(TrunkID,p_TrunkIds) != 0 AND EndDate <= NOW();


/*
     IF (FOUND_ROWS() > 0) THEN
	 	select concat(FOUND_ROWS() ," Ends Today rates" ) ;
	  END IF;
*/



	DELETE  vr
	FROM tblVendorRate vr
   inner join tblVendorRateArchive vra
   on vr.VendorRateID = vra.VendorRateID
	WHERE  FIND_IN_SET(vr.AccountId,p_AccountIds) != 0 AND FIND_IN_SET(vr.TrunkID,p_TrunkIds) != 0;


	/*  IF (FOUND_ROWS() > 0) THEN
		 select concat(FOUND_ROWS() ," sane rate " ) ;
	 END IF;

	*/

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;


END//
DELIMITER ;






DROP PROCEDURE IF EXISTS `prc_VendorBulkRateDelete`;
DELIMITER //
CREATE PROCEDURE `prc_VendorBulkRateDelete`(
	IN `p_CompanyId` INT,
	IN `p_AccountId` INT,
	IN `p_TrunkId` INT ,
	IN `p_VendorRateIds` TEXT,
	IN `p_code` varchar(50),
	IN `p_description` varchar(200),
	IN `p_CountryId` INT,
	IN `p_effective` VARCHAR(50),
	IN `p_DeletedBy` TEXT,
	IN `p_action` INT
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_Delete_VendorRate;
	CREATE TEMPORARY TABLE tmp_Delete_VendorRate (
     	VendorRateID INT,
      AccountId INT,
      TrunkID INT,
      RateId INT,
      Code VARCHAR(50),
      Description VARCHAR(200),
      Rate DECIMAL(18, 6),
      EffectiveDate DATETIME,
      EndDate DATETIME,
		Interval1 INT,
		IntervalN INT,
		ConnectionFee DECIMAL(18, 6),
		deleted_at DATETIME,
        INDEX tmp_VendorRateDiscontinued_VendorRateID (`VendorRateID`)
	);

	 IF p_action = 1
	 THEN


	 INSERT INTO tmp_Delete_VendorRate(
	 	VendorRateID,
      AccountId,
      TrunkID,
      RateId,
      Code,
      Description,
      Rate,
      EffectiveDate,
      EndDate,
		Interval1,
		IntervalN,
		ConnectionFee,
		deleted_at
	 )
		SELECT
		  VendorRateID,
		  p_AccountId AS AccountId,
		  p_TrunkId AS TrunkID,
		  v.RateId,
		  r.Code,
		  r.Description,
		  Rate,
		  EffectiveDate,
		  EndDate,
		  v.Interval1,
		  v.IntervalN,
		  ConnectionFee,
		  now() AS deleted_at
		FROM tblVendorRate v
		  INNER JOIN tblRate r
		  		ON r.RateID = v.RateId
		  INNER JOIN tblVendorTrunk vt
		  		ON vt.trunkID = p_TrunkId
				   AND vt.AccountID = p_AccountId
					AND vt.CodeDeckId = r.CodeDeckId
		WHERE
			((p_CountryId IS NULL) OR (p_CountryId IS NOT NULL AND r.CountryId = p_CountryId))
			AND ((p_code IS NULL) OR (p_code IS NOT NULL AND r.Code like REPLACE(p_code,'*', '%')))
			AND ((p_description IS NULL) OR (p_description IS NOT NULL AND r.Description like REPLACE(p_description,'*', '%')))
		 	AND ((p_effective = 'Now' AND v.EffectiveDate <= NOW() ) OR (p_effective = 'Future' AND v.EffectiveDate> NOW()) OR p_effective = 'All')
		 	AND v.AccountId = p_AccountId
			AND v.TrunkID = p_TrunkId;

	 END IF;

	IF p_action = 2
	THEN




		 INSERT INTO tmp_Delete_VendorRate(
		 	VendorRateID,
	      AccountId,
	      TrunkID,
	      RateId,
	      Code,
	      Description,
	      Rate,
	      EffectiveDate,
	      EndDate,
			Interval1,
			IntervalN,
			ConnectionFee,
			deleted_at
		 )
		SELECT
		  VendorRateID,
		  p_AccountId AS AccountId,
		  p_TrunkId AS TrunkID,
		  v.RateId,
		  r.Code,
		  r.Description,
		  Rate,
		  EffectiveDate,
		  EndDate,
		  v.Interval1,
		  v.IntervalN,
		  ConnectionFee,
		  now() AS deleted_at
		FROM tblVendorRate v
		  INNER JOIN tblRate r
		  		ON r.RateID = v.RateId
		WHERE v.AccountId = p_AccountId
				AND v.TrunkID = p_TrunkId
				AND ((p_VendorRateIds IS NULL) OR FIND_IN_SET(VendorRateID,p_VendorRateIds)!=0)
				;
	END IF;


	-- set end date will remove at bottom in archive proc
	UPDATE tblVendorRate
	JOIN tmp_Delete_VendorRate ON tblVendorRate.VendorRateID = tmp_Delete_VendorRate.VendorRateID
	SET tblVendorRate.EndDate = date(now())
	WHERE
	tblVendorRate.AccountId = p_accountId
	AND tblVendorRate.TrunkId = p_trunkId;


	-- archive rates which has EndDate <= today
	call prc_ArchiveOldVendorRate(p_accountId,p_trunkId,p_DeletedBy);

	-- CALL prc_InsertDiscontinuedVendorRate(p_AccountId,p_TrunkId);


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;











DROP PROCEDURE IF EXISTS `prc_VendorBulkRateUpdate`;
DELIMITER //
CREATE PROCEDURE `prc_VendorBulkRateUpdate`(
	IN `p_AccountId` INT,
	IN `p_TrunkId` INT ,
	IN `p_code` varchar(50),
	IN `p_description` varchar(200),
	IN `p_CountryId` INT,
	IN `p_CompanyId` INT,
	IN `p_Rate` decimal(18,6),
	IN `p_EffectiveDate` DATETIME,
	IN `p_ConnectionFee` decimal(18,6),
	IN `p_Interval1` INT,
	IN `p_IntervalN` INT,
	IN `p_ModifiedBy` varchar(50),
	IN `p_effective` VARCHAR(50),
	IN `p_action` INT
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	IF p_action = 1

	THEN

	UPDATE tblVendorRate

	INNER JOIN
	(
	SELECT VendorRateID
	  FROM tblVendorRate v
	  INNER JOIN tblRate r ON r.RateID = v.RateId
	  INNER JOIN tblVendorTrunk vt on vt.trunkID = p_TrunkId AND vt.AccountID = p_AccountId AND vt.CodeDeckId = r.CodeDeckId
	 WHERE
	 ((p_CountryId IS NULL) OR (p_CountryId IS NOT NULL AND r.CountryId = p_CountryId))
	 AND ((p_code IS NULL) OR (p_code IS NOT NULL AND r.Code like REPLACE(p_code,'*', '%')))
	 AND ((p_description IS NULL) OR (p_description IS NOT NULL AND r.Description like REPLACE(p_description,'*', '%')))
	 AND  ((p_effective = 'Now' and v.EffectiveDate <= NOW() ) OR (p_effective = 'Future' and v.EffectiveDate> NOW() ) )
	 AND v.AccountId = p_AccountId AND v.TrunkID = p_TrunkId
	 ) vr
	 ON vr.VendorRateID = tblVendorRate.VendorRateID
	 	SET
		Rate = p_Rate,
		EffectiveDate = p_EffectiveDate,
		Interval1 = p_Interval1,
		IntervalN = p_IntervalN,
		updated_by = p_ModifiedBy,
		ConnectionFee = p_ConnectionFee,
		updated_at = NOW();
 	END IF;

	 CALL prc_ArchiveOldVendorRate(p_AccountId,p_TrunkId,p_ModifiedBy);

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;











DROP PROCEDURE IF EXISTS `prc_WSCronJobDeleteOldVendorRate`;
DELIMITER //
CREATE PROCEDURE `prc_WSCronJobDeleteOldVendorRate`(
	IN `p_DeletedBy` TEXT
)
BEGIN


	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	INSERT INTO tblVendorRateArchive
   SELECT DISTINCT  null , -- Primary Key column
							vr.`VendorRateID`,
							vr.`AccountId`,
							vr.`TrunkID`,
							vr.`RateId`,
							vr.`Rate`,
							vr.`EffectiveDate`,
							IFNULL(vr.`EndDate`,date(now())) as EndDate,
							vr.`updated_at`,
							now() as created_at,
							p_DeletedBy AS `created_by`,
							vr.`updated_by`,
							vr.`Interval1`,
							vr.`IntervalN`,
							vr.`ConnectionFee`,
							vr.`MinimumCost`,
	   concat('Ends Today rates @ ' , now() ) as `Notes`
      FROM tblVendorRate vr
     	INNER JOIN tblAccount a on vr.AccountId = a.AccountID
		WHERE a.Status = 1 AND vr.EndDate <= NOW();


	DELETE  vr
	FROM tblVendorRate vr
   inner join tblVendorRateArchive vra
   on vr.VendorRateID = vra.VendorRateID;


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;













DROP PROCEDURE IF EXISTS `prc_GetTicketDashboardSummary`;
DELIMITER //
CREATE PROCEDURE `prc_GetTicketDashboardSummary`(
	IN `p_CompanyID` INT,
	IN `P_Group` VARCHAR(100),
	IN `P_Agent` VARCHAR(100)
)
BEGIN

	DECLARE v_Open_ INT;
	DECLARE v_OnHold_ INT;
	DECLARE v_UnResolved_ INT;
	DECLARE v_TotalPaymentOut_ INT;
	DECLARE v_UnAssigned_ INT;
	DECLARE v_OnHoldChech_ VARCHAR(500);
	DECLARE v_UnResolvedCheck_ VARCHAR(500);

	SELECT CONCAT('Pending',',',GROUP_CONCAT(tfv.FieldValueAgent SEPARATOR ',')) as FieldValueAgent INTO v_OnHoldChech_ FROM tblTicketfieldsValues tfv
	WHERE tfv.FieldsID = (SELECT tf.TicketFieldsID FROM tblTicketfields tf WHERE tf.FieldType = 'default_status' LIMIT 1)
	AND tfv.FieldType !=0
	AND (tfv.FieldSlaTime=0)
	AND tfv.FieldValueAgent!='All UnResolved';

	DROP TEMPORARY TABLE IF EXISTS tmp_Tickets_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Tickets_(
		TicketAgentID VARCHAR(50),
		TicketStatusID int,
		TicketStatus VARCHAR(30)
	);

	INSERT INTO tmp_Tickets_
	SELECT
		T.Agent as TicketAgentID,
		TFV.ValuesID as  TicketStatusID,
		TFV.FieldValueAgent  as TicketStatus
	FROM
		tblTickets T
	LEFT JOIN tblTicketfieldsValues TFV
		ON TFV.ValuesID = T.Status
	LEFT JOIN tblTicketPriority TP
		ON TP.PriorityID = T.Priority
	LEFT JOIN tblUser TU
		ON TU.UserID = T.Agent
	LEFT JOIN tblTicketGroups TG
		ON TG.GroupID = T.`Group`
	WHERE
		T.CompanyID = p_CompanyID
		AND (P_Group = '' OR FIND_IN_SET(T.`Group`,P_Group))
		AND (P_Agent = '' OR FIND_IN_SET(T.`Agent`,P_Agent));

	SELECT COUNT(TicketStatusID) INTO v_Open_  FROM	tmp_Tickets_
	WHERE TicketStatus = 'Open';

	SELECT COUNT(TicketStatusID) INTO v_OnHold_  FROM	tmp_Tickets_
	WHERE FIND_IN_SET(TicketStatus,v_OnHoldChech_);

	SELECT COUNT(TicketStatusID) INTO v_UnResolved_  FROM	tmp_Tickets_
	WHERE !FIND_IN_SET(TicketStatus,'All UnResolved,Resolved,Closed');

	SELECT COUNT(TicketStatusID) INTO v_UnAssigned_  FROM	tmp_Tickets_
	WHERE TicketAgentID = 0 AND FIND_IN_SET(TicketStatus,'All UnResolved,Pending,Open');

	SELECT v_Open_ as `Open`, v_OnHold_ as OnHold,v_UnResolved_ as UnResolved, v_UnAssigned_ as UnAssigned;
END//
DELIMITER ;












DROP PROCEDURE IF EXISTS `prc_VendorBulkRateUpdate`;
DELIMITER //
CREATE PROCEDURE `prc_VendorBulkRateUpdate`(
	IN `p_AccountId` INT,
	IN `p_TrunkId` INT ,
	IN `p_code` varchar(50),
	IN `p_description` varchar(200),
	IN `p_CountryId` INT,
	IN `p_CompanyId` INT,
	IN `p_Rate` decimal(18,6),
	IN `p_EffectiveDate` DATETIME,
	IN `p_ConnectionFee` decimal(18,6),
	IN `p_Interval1` INT,
	IN `p_IntervalN` INT,
	IN `p_ModifiedBy` varchar(50),
	IN `p_effective` VARCHAR(50),
	IN `p_action` INT
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	IF p_action = 1

	THEN
		DROP TEMPORARY TABLE IF EXISTS tmp_TempVendorRate_;
	  	CREATE TEMPORARY TABLE tmp_TempVendorRate_ (
			`AccountId` int(11) NOT NULL,
			`TrunkID` int(11) NOT NULL,
			`RateId` int(11) NOT NULL,
			`Rate` decimal(18,6) NOT NULL DEFAULT '0.000000',
			`EffectiveDate` datetime NOT NULL,
			`EndDate` datetime DEFAULT NULL,
			`updated_at` datetime DEFAULT NULL,
			`created_at` datetime DEFAULT NULL,
			`created_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
			`updated_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
			`Interval1` int(11) DEFAULT NULL,
			`IntervalN` int(11) DEFAULT NULL,
			`ConnectionFee` decimal(18,6) DEFAULT NULL,
			`MinimumCost` decimal(18,6) DEFAULT NULL
		);
	/*UPDATE tblVendorRate

	INNER JOIN
	(
	SELECT VendorRateID
	  FROM tblVendorRate v
	  INNER JOIN tblRate r ON r.RateID = v.RateId
	  INNER JOIN tblVendorTrunk vt on vt.trunkID = p_TrunkId AND vt.AccountID = p_AccountId AND vt.CodeDeckId = r.CodeDeckId
	 WHERE
	 ((p_CountryId IS NULL) OR (p_CountryId IS NOT NULL AND r.CountryId = p_CountryId))
	 AND ((p_code IS NULL) OR (p_code IS NOT NULL AND r.Code like REPLACE(p_code,'*', '%')))
	 AND ((p_description IS NULL) OR (p_description IS NOT NULL AND r.Description like REPLACE(p_description,'*', '%')))
	 AND  ((p_effective = 'Now' and v.EffectiveDate <= NOW() ) OR (p_effective = 'Future' and v.EffectiveDate> NOW() ) )
	 AND v.AccountId = p_AccountId AND v.TrunkID = p_TrunkId
	 ) vr
	 ON vr.VendorRateID = tblVendorRate.VendorRateID
	 	SET
		Rate = p_Rate,
		EffectiveDate = p_EffectiveDate,
		Interval1 = p_Interval1,
		IntervalN = p_IntervalN,
		updated_by = p_ModifiedBy,
		ConnectionFee = p_ConnectionFee,
		updated_at = NOW();*/

		INSERT INTO tmp_TempVendorRate_
		SELECT
			v.AccountId,
			v.TrunkID,
			v.RateId,
			p_Rate AS Rate,
			p_EffectiveDate AS EffectiveDate,
			v.EndDate,
			NOW() AS updated_at,
			v.created_at,
			v.created_by,
			p_ModifiedBy AS updated_by,
			p_Interval1 AS Interval1,
			p_IntervalN AS IntervalN,
			p_ConnectionFee AS ConnectionFee,
			v.MinimumCost
		FROM
			tblVendorRate v
		INNER JOIN
			tblRate r ON r.RateID = v.RateId
		INNER JOIN
			tblVendorTrunk vt on vt.trunkID = p_TrunkId AND vt.AccountID = p_AccountId AND vt.CodeDeckId = r.CodeDeckId
		WHERE
			((p_CountryId IS NULL) OR (p_CountryId IS NOT NULL AND r.CountryId = p_CountryId)) AND
			((p_code IS NULL) OR (p_code IS NOT NULL AND r.Code like REPLACE(p_code,'*', '%'))) AND
			((p_description IS NULL) OR (p_description IS NOT NULL AND r.Description like REPLACE(p_description,'*', '%'))) AND
			((p_effective = 'Now' and v.EffectiveDate <= NOW() ) OR (p_effective = 'Future' and v.EffectiveDate> NOW() ) ) AND
			v.AccountId = p_AccountId AND
			v.TrunkID = p_TrunkId;

		UPDATE
			tblVendorRate v
		INNER JOIN
			tblRate r ON r.RateID = v.RateId
		INNER JOIN
			tblVendorTrunk vt on vt.trunkID = p_TrunkId AND vt.AccountID = p_AccountId AND vt.CodeDeckId = r.CodeDeckId
		SET
			v.EndDate = NOW()
		WHERE
			((p_CountryId IS NULL) OR (p_CountryId IS NOT NULL AND r.CountryId = p_CountryId)) AND
			((p_code IS NULL) OR (p_code IS NOT NULL AND r.Code like REPLACE(p_code,'*', '%'))) AND
			((p_description IS NULL) OR (p_description IS NOT NULL AND r.Description like REPLACE(p_description,'*', '%'))) AND
			((p_effective = 'Now' and v.EffectiveDate <= NOW() ) OR (p_effective = 'Future' and v.EffectiveDate> NOW() ) ) AND
			v.AccountId = p_AccountId AND
			v.TrunkID = p_TrunkId;

		CALL prc_ArchiveOldVendorRate(p_AccountId,p_TrunkId,p_ModifiedBy);

		INSERT INTO tblVendorRate (
			AccountId,
			TrunkID,
			RateId,
			Rate,
			EffectiveDate,
			EndDate,
			updated_at,
			created_at,
			created_by,
			updated_by,
			Interval1,
			IntervalN,
			ConnectionFee,
			MinimumCost
		)
		select
			AccountId,
			TrunkID,
			RateId,
			Rate,
			EffectiveDate,
			EndDate,
			updated_at,
			created_at,
			created_by,
			updated_by,
			Interval1,
			IntervalN,
			ConnectionFee,
			MinimumCost
		from
			tmp_TempVendorRate_;

 	END IF;

	-- CALL prc_ArchiveOldVendorRate(p_AccountId,p_TrunkId,p_ModifiedBy);

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;














DROP PROCEDURE IF EXISTS `prc_GetLCR`;
DELIMITER //
CREATE PROCEDURE `prc_GetLCR`(
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
	IN `p_vendor_block` INT,
	IN `p_groupby` VARCHAR(50),
	IN `p_SelectedEffectiveDate` DATE,
	IN `p_isExport` INT
)
ThisSP:BEGIN

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

		-- just for taking codes -

		SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage_;
		CREATE TEMPORARY TABLE tmp_VendorRate_stage_ (
			RowCode VARCHAR(50) ,
			AccountId INT ,
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
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
			BlockingId INT DEFAULT 0,
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
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
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
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
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

		-- Loop codes

		DROP TEMPORARY TABLE IF EXISTS tmp_search_code_;
		CREATE TEMPORARY TABLE tmp_search_code_ (
			Code  varchar(50),
			INDEX Index1 (Code)
		);

		-- searched codes.

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
			RowNo int,
			INDEX Index2 (Code)
		)
		;


		DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates_(
			AccountId int,
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
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
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
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
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
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

		-- Search code based on p_code

		insert into tmp_search_code_
			SELECT  DISTINCT LEFT(f.Code, x.RowNo) as loopCode FROM (
																																SELECT @RowNo  := @RowNo + 1 as RowNo
																																FROM mysql.help_category
																																	,(SELECT @RowNo := 0 ) x
																																limit 15
																															) x
				INNER JOIN tblRate AS f
					ON f.CompanyID = p_companyid  AND f.CodeDeckId = p_codedeckID
						 AND ( CHAR_LENGTH(RTRIM(p_code)) = 0  OR f.Code LIKE REPLACE(p_code,'*', '%') )
						 AND ( p_Description = ''  OR f.Description LIKE REPLACE(p_Description,'*', '%') )
						 AND x.RowNo   <= LENGTH(f.Code)
			order by loopCode   desc;

		-- distinct vendor rates

		INSERT INTO tmp_VendorCurrentRates1_
			Select DISTINCT AccountId,BlockingId,BlockingCountryId ,AccountName,Code,Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference
			FROM (
						 SELECT distinct tblVendorRate.AccountId,
						    CASE WHEN p_groupby = 'description' THEN IFNULL(blockCode.VendorBlockingId, 0) ELSE IFNULL(blockCode.VendorBlockingId, 0) END AS BlockingId,
						    IFNULL(blockCountry.CountryId, 0)  as BlockingCountryId,
							 tblAccount.AccountName, tblRate.Code, tmpselectedcd.Description,
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
									 -- Convert to base currrncy and x by RateGenerator Exhange

									 (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = p_CurrencyID and  CompanyID = p_companyid )
									 * (tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = p_companyid ))
								 )
							 END
																																		 as  Rate,
							 ConnectionFee,
							 DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') AS EffectiveDate, tblVendorRate.TrunkID, tblRate.CountryID, tblRate.RateID,IFNULL(vp.Preference, 5) AS Preference
						 FROM      tblVendorRate
							 Inner join tblVendorTrunk vt on vt.CompanyID = p_companyid AND vt.AccountID = tblVendorRate.AccountID and vt.Status =  1 and vt.TrunkID =  p_trunkID

							 INNER JOIN tblAccount   ON  tblAccount.CompanyID = p_companyid AND tblVendorRate.AccountId = tblAccount.AccountID and tblAccount.IsVendor = 1
							 INNER JOIN tblRate ON tblRate.CompanyID = p_companyid     AND    tblVendorRate.RateId = tblRate.RateID   AND vt.CodeDeckId = tblRate.CodeDeckId
							 INNER JOIN tmp_search_code_  SplitCode   on tblRate.Code = SplitCode.Code

							  INNER JOIN 	(select Code,Description from tblRate where CodeDeckId=p_codedeckID ) tmpselectedcd on tmpselectedcd.Code=tblRate.Code

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
							 ( EffectiveDate <= DATE(p_SelectedEffectiveDate) )
							 AND ( tblVendorRate.EndDate IS NULL OR  tblVendorRate.EndDate > Now() )   -- rate should not end Today
							 AND (p_AccountIds='' OR FIND_IN_SET(tblAccount.AccountID,p_AccountIds) != 0 )
							 AND tblAccount.IsVendor = 1
							 AND tblAccount.Status = 1
							 AND tblAccount.CurrencyId is not NULL
							 AND tblVendorRate.TrunkID = p_trunkID
							  AND
						        (
						           p_vendor_block = 1 OR
						          (
						             p_vendor_block = 0 AND   (
						                 blockCode.RateId IS NULL  AND blockCountry.CountryId IS NULL
						             )
						         )
						       )
							 -- AND blockCode.RateId IS NULL
							 -- AND blockCountry.CountryId IS NULL

					 ) tbl
			order by Code asc;


		-- filter by Effective Dates

		IF p_groupby = 'description' THEN

		INSERT INTO tmp_VendorCurrentRates_
			Select AccountId,max(BlockingId),max(BlockingCountryId) ,max(AccountName),max(Code),Description, MAX(Rate),max(ConnectionFee),max(EffectiveDate),max(TrunkID),max(CountryID),max(RateID),max(Preference)
			FROM (
						 SELECT * ,
							 @row_num := IF(@prev_AccountId = AccountID AND @prev_TrunkID = TrunkID AND @prev_Description = Description AND @prev_EffectiveDate >= EffectiveDate, @row_num + 1, 1) AS RowID,
							 @prev_AccountId := AccountID,
							 @prev_TrunkID := TrunkID,
							 @prev_Description := Description,
							 @prev_EffectiveDate := EffectiveDate
						 FROM tmp_VendorCurrentRates1_
							 ,(SELECT @row_num := 1,  @prev_AccountId := '',@prev_TrunkID := '', @prev_RateId := '', @prev_EffectiveDate := '') x
						 ORDER BY AccountId, TrunkID, RateId, EffectiveDate DESC
					 ) tbl
			WHERE RowID = 1
			group BY AccountId, TrunkID, Description
			order by Description asc;

		ELSE

		INSERT INTO tmp_VendorCurrentRates_
			Select AccountId,BlockingId,BlockingCountryId ,AccountName,Code,Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference
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

		END IF;
		-- Collect Codes pressent in vendor Rates from above query.
		/*
               9372     9372    1
               9372     937     2
               9372     93      3
               9372     9       4

    */


		insert into tmp_all_code_ (RowCode,Code,RowNo)
			select RowCode , loopCode,RowNo
			from (
						 select   RowCode , loopCode,
							 @RowNo := ( CASE WHEN ( @prev_Code = tbl1.RowCode  ) THEN @RowNo + 1
													 ELSE 1
													 END

							 )      as RowNo,
							 @prev_Code := tbl1.RowCode

						 from (
										SELECT distinct f.Code as RowCode, LEFT(f.Code, x.RowNo) as loopCode FROM (
																																																SELECT @RowNo  := @RowNo + 1 as RowNo
																																																FROM mysql.help_category
																																																	,(SELECT @RowNo := 0 ) x
																																																limit 15
																																															) x
											INNER JOIN tmp_search_code_ AS f
												ON  x.RowNo   <= LENGTH(f.Code)
														AND ( CHAR_LENGTH(RTRIM(p_code)) = 0  OR Code LIKE REPLACE(p_code,'*', '%') )
											INNER JOIN tblRate as tr on f.Code=tr.Code AND tr.CodeDeckId=p_codedeckID
										order by RowCode desc,  LENGTH(loopCode) DESC
									) tbl1
							 , ( Select @RowNo := 0 ) x
					 ) tbl order by RowCode desc,  LENGTH(loopCode) DESC ;



		IF (p_isExport = 0)
		THEN

			insert into tmp_code_
				select * from tmp_all_code_
				order by RowCode	LIMIT p_RowspPage OFFSET v_OffSet_ ;

		ELSE

			insert into tmp_code_
				select * from tmp_all_code_
				order by RowCode	  ;

		END IF;


		IF p_Preference = 1 THEN

			-- Sort by Preference

			INSERT IGNORE INTO tmp_VendorRateByRank_
				SELECT
					AccountID,
					BlockingId ,
					BlockingCountryId,
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
								BlockingId ,
								BlockingCountryId,
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

			-- Sort by Rate

			INSERT IGNORE INTO tmp_VendorRateByRank_
				SELECT
					AccountID,
					BlockingId ,
					BlockingCountryId,
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
								BlockingId ,
								BlockingCountryId,
								AccountName,
								Code,
								Rate,
								ConnectionFee,
								EffectiveDate,
								Description,
								Preference,
								CASE WHEN p_groupby = 'description' THEN
								@rank := CASE WHEN (@prev_Rate < Rate) THEN @rank + 1
												 WHEN (@prev_Rate = Rate) THEN @rank
												 ELSE 1
												 END
								ELSE
								@rank := CASE WHEN (@prev_Code    = Code AND @prev_Rate < Rate) THEN @rank + 1
												 WHEN (@prev_Code    = Code AND @prev_Rate = Rate) THEN @rank
												 ELSE 1
												 END
								END
									AS RateRank,
								@prev_Code := Code,
								@prev_Rate := Rate
							FROM tmp_VendorCurrentRates_ AS rank,
								(SELECT @rank := 0 , @prev_Code := '' , @prev_Rate := 0) f
							ORDER BY rank.Code ASC, rank.Rate,rank.AccountId ASC) tbl
				WHERE RateRank <= p_Position
				ORDER BY Code, RateRank;

		END IF;

		-- --------- Split Logic ----------
		/* DESC             MaxMatchRank 1  MaxMatchRank 2
    923 Pakistan :       *923 V1          92 V1
    923 Pakistan :       *92 V2            -

    now take only where  MaxMatchRank =  1
    */


		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage_1;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRate_stage_1 as (select * from tmp_VendorRate_stage_);

		insert ignore into tmp_VendorRate_stage_1 (
			RowCode,
			AccountId ,
			BlockingId,
			BlockingCountryId,
			AccountName ,
			Code ,
			Rate ,
			ConnectionFee,
			EffectiveDate ,
			Description ,
			Preference
		)
			SELECT
				distinct
				RowCode,
				v.AccountId ,
				v.BlockingId,
				v.BlockingCountryId,
				v.AccountName ,
				v.Code ,
				v.Rate ,
				v.ConnectionFee,
				v.EffectiveDate ,
				tr.Description,
				-- (select Description from tblRate where tblRate.Code =RowCode AND  tblRate.CodeDeckId=p_codedeckID ) as Description ,
				v.Preference
			FROM tmp_VendorRateByRank_ v
				left join  tmp_all_code_
									 SplitCode   on v.Code = SplitCode.Code
				inner join tblRate tr  on  RowCode = tr.Code AND  tr.CodeDeckId=p_codedeckID
			where  SplitCode.Code is not null and rankname <= p_Position
			order by AccountID,SplitCode.RowCode desc ,LENGTH(SplitCode.RowCode), v.Code desc, LENGTH(v.Code)  desc;


		insert ignore into tmp_VendorRate_stage_
			SELECT
				distinct
				RowCode,
				v.AccountId ,
				v.BlockingId,
				v.BlockingCountryId,
				v.AccountName ,
				v.Code ,
				v.Rate ,
				v.ConnectionFee,
				v.EffectiveDate ,
				v.Description ,
				v.Preference,
				@rank := ( CASE WHEN( @prev_AccountID = v.AccountId  and @prev_RowCode     = RowCode   )
					THEN  @rank + 1
									 ELSE 1
									 END
				) AS MaxMatchRank,
				@prev_RowCode := RowCode	 ,
				@prev_AccountID := v.AccountId
			FROM tmp_VendorRate_stage_1 v
				, (SELECT  @prev_RowCode := '',  @rank := 0 , @prev_Code := '' , @prev_AccountID := Null) f
			order by AccountID,RowCode desc ;



		IF p_groupby = 'description' THEN

			insert ignore into tmp_VendorRate_
			select
				distinct
				ANY_VALUE(AccountId) ,
				ANY_VALUE(BlockingId) ,
				ANY_VALUE(BlockingCountryId),
				ANY_VALUE(AccountName) ,
				ANY_VALUE(Code) ,
				ANY_VALUE(Rate) ,
				ANY_VALUE(ConnectionFee),
				ANY_VALUE(EffectiveDate) ,
				Description ,
				ANY_VALUE(Preference),
				ANY_VALUE(RowCode)
			from tmp_VendorRate_stage_
			where MaxMatchRank = 1
			group by Description
			order by Description asc;

		ELSE

			insert ignore into tmp_VendorRate_
				select
					distinct
					AccountId ,
					BlockingId ,
					BlockingCountryId,
					AccountName ,
					Code ,
					Rate ,
					ConnectionFee,
					EffectiveDate ,
					Description ,
					Preference,
					RowCode
				from tmp_VendorRate_stage_
				where MaxMatchRank = 1
				order by RowCode desc;
		END IF;






		IF( p_Preference = 0 )
		THEN

			IF p_groupby = 'description' THEN
				/* group by description when preference off */

				insert into tmp_final_VendorRate_
					SELECT
						AccountId ,
						(CASE WHEN (select COUNT(*) from tmp_VendorCurrentRates1_ where tmp_VendorCurrentRates1_.AccountId=tbl1.AccountId AND tmp_VendorCurrentRates1_.Description=tbl1.Description AND BlockingId=0) > 0 THEN 0 ELSE 1 END) AS  BlockingId,
						-- (CASE WHEN (select COUNT(*) from tmp_VendorCurrentRates1_ where tmp_VendorCurrentRates1_.AccountId=tbl1.AccountId AND tmp_VendorCurrentRates1_.Description=tbl1.Description AND BlockingId=0) > 0 THEN 0 ELSE 1 END) AS  BlockingId,
						BlockingCountryId,
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
								BlockingId,
								BlockingCountryId,
								AccountName ,
								Code ,
								Rate ,
								ConnectionFee,
								EffectiveDate ,
								Description ,
								Preference,
								RowCode,
								@rank := CASE WHEN (@prev_Description    = Description AND  @prev_Rate <  Rate ) THEN @rank+1
											 WHEN (@prev_Description    = Description AND  @prev_Rate = Rate ) THEN @rank
											 ELSE
												 1
											 END
								AS FinalRankNumber,
								@prev_Description  := Description,
								@prev_Rate  := Rate
							from tmp_VendorRate_
								,( SELECT @rank := 0 , @prev_Description := '' , @prev_Rate := 0 ) x
							order by Description,Rate,AccountId ASC

						) tbl1
					where
						FinalRankNumber <= p_Position;
			ELSE
					/* group by code when preference off */

				insert into tmp_final_VendorRate_
					SELECT
						AccountId ,
						BlockingId ,
						BlockingCountryId,
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
								BlockingId ,
								BlockingCountryId,
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

			END IF;

		ELSE

			IF p_groupby = 'description' THEN
				/* group by description when preference on */
				insert into tmp_final_VendorRate_
					SELECT
						AccountId ,
						(CASE WHEN (select COUNT(*) from tmp_VendorCurrentRates1_ where tmp_VendorCurrentRates1_.AccountId=tbl1.AccountId AND tmp_VendorCurrentRates1_.Description=tbl1.Description AND BlockingId=0) > 0 THEN 0 ELSE 1 END) AS  BlockingId,
						-- (CASE WHEN (select COUNT(*) from tmp_VendorCurrentRates1_ where tmp_VendorCurrentRates1_.AccountId=AccountId AND tmp_VendorCurrentRates1_.Description=Description AND BlockingId=0) > 0 THEN 0 ELSE 1 END) AS  BlockingId,
						BlockingCountryId,
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
								BlockingId,
								BlockingCountryId,
								AccountName ,
								Code ,
								Rate ,
								ConnectionFee,
								EffectiveDate ,
								Description ,
								Preference,
								RowCode,
								@preference_rank := CASE WHEN (@prev_Description     = Description AND @prev_Preference > Preference  )   THEN @preference_rank + 1
																		WHEN (@prev_Description     = Description AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1
																		WHEN (@prev_Description    = Description AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank
																		ELSE 1 END AS FinalRankNumber,
								@prev_Description := Description,
								@prev_Preference := Preference,
								@prev_Rate := Rate
							from tmp_VendorRate_
								,(SELECT @preference_rank := 0 , @prev_Description := ''  , @prev_Preference := 5,  @prev_Rate := 0) x
							order by Description ASC ,Preference DESC ,Rate ASC ,AccountId ASC

						) tbl1
					where
						FinalRankNumber <= p_Position;
			ELSE
					/* group by code when preference on */
				insert into tmp_final_VendorRate_
					SELECT
						AccountId ,
						BlockingId ,
						BlockingCountryId,
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
								BlockingId ,
								BlockingCountryId,
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

		END IF;


		SET @stm_columns = "";

		-- if not export then columns must be max 10
		IF p_isExport = 0 AND p_Position > 10 THEN
			SET p_Position = 10;
		END IF;

		-- if export then all columns
		IF p_isExport = 1 THEN
			SELECT COUNT(*) INTO p_Position FROM tmp_final_VendorRate_;
		END IF;

		-- columns loop 5,10,50,...
		SET v_pointer_=1;
		WHILE v_pointer_ <= p_Position
		DO

			SET @stm_columns = CONCAT(@stm_columns, "GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = ",v_pointer_,", CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION ",v_pointer_,"`,");

			SET v_pointer_ = v_pointer_ + 1;

		END WHILE;

		SET @stm_columns = TRIM(TRAILING ',' FROM @stm_columns);


		IF (p_isExport = 0)
		THEN
			IF p_groupby = 'description' THEN

				SET @stm_query = CONCAT("SELECT CONCAT(max(t.Description)) as Destination,",@stm_columns," FROM tmp_final_VendorRate_  t GROUP BY  t.Description ORDER BY t.Description ASC LIMIT ",p_RowspPage," OFFSET ",v_OffSet_," ;");
				/*SELECT
					CONCAT(max(t.Description)) as Destination,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 1`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 2`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 3`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 4`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 5`
				FROM tmp_final_VendorRate_  t
				GROUP BY  t.Description
				ORDER BY t.Description ASC
				LIMIT p_RowspPage OFFSET v_OffSet_ ;*/

			ELSE

				SET @stm_query = CONCAT("SELECT CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,", @stm_columns," FROM tmp_final_VendorRate_  t GROUP BY  RowCode ORDER BY RowCode ASC LIMIT ",p_RowspPage," OFFSET ",v_OffSet_," ;");

				/*SELECT
					CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,

					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 1`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 2`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 3`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 4`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.Code), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 5`
				FROM tmp_final_VendorRate_  t
				GROUP BY  RowCode
				ORDER BY RowCode ASC
				LIMIT p_RowspPage OFFSET v_OffSet_ ;*/

			END IF;

			SELECT count(distinct RowCode) as totalcount from tmp_final_VendorRate_
				WHERE  ( CHAR_LENGTH(RTRIM(p_code)) = 0  OR RowCode LIKE REPLACE(p_code,'*', '%') ) ;

		END IF;

		IF p_isExport = 1
		THEN
			SET @stm_query = CONCAT("SELECT CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,",@stm_columns," FROM tmp_final_VendorRate_  t GROUP BY  RowCode ORDER BY RowCode ASC;");

			/*SELECT
				CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,
				GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode)), NULL))AS `POSITION 1`,
				GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode)), NULL))AS `POSITION 2`,
				GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode)), NULL))AS `POSITION 3`,
				GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode)), NULL))AS `POSITION 4`,
				GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode)), NULL))AS `POSITION 5`
			FROM tmp_final_VendorRate_  t
			GROUP BY  RowCode
			ORDER BY RowCode ASC;*/
		END IF;

		PREPARE stm_query FROM @stm_query;
		EXECUTE stm_query;
		DEALLOCATE PREPARE stm_query;

		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

	END//
DELIMITER ;















DROP PROCEDURE IF EXISTS `prc_GetLCRwithPrefix`;
DELIMITER //
CREATE PROCEDURE `prc_GetLCRwithPrefix`(
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
	IN `p_vendor_block` INT,
	IN `p_groupby` VARCHAR(50),
	IN `p_SelectedEffectiveDate` DATE,
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
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			RateID int,
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
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			RateID int,
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
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
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
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
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
			BlockingId INT DEFAULT 0,
			BlockingCountryId INT DEFAULT 0,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) ,
			Rate DECIMAL(18,6) ,
			RateID int,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			rankname INT,
			INDEX IX_Code (Code,rankname)
		)
		;

		DROP TEMPORARY TABLE IF EXISTS tmp_block0;
		CREATE TEMPORARY TABLE tmp_block0(
			AccountId INT,
			AccountName VARCHAR(200),
			des VARCHAR(200),
			RateId INT
		);

		SELECT CurrencyId INTO v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = p_companyid;



			INSERT INTO tmp_VendorCurrentRates1_

				Select DISTINCT AccountId,BlockingId,BlockingCountryId ,AccountName,Code,Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference
				FROM (

						 SELECT distinct tblVendorRate.AccountId,
						 		CASE WHEN p_groupby = 'description' THEN IFNULL(blockCode.VendorBlockingId, 0) ELSE IFNULL(blockCode.VendorBlockingId, 0) END AS BlockingId,
						 		IFNULL(blockCountry.CountryId, 0)  as BlockingCountryId,
								tblAccount.AccountName, tblRate.Code, tmpselectedcd.Description,
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
							 INNER JOIN tblRate ON tblRate.CompanyID = p_companyid   AND    tblVendorRate.RateId = tblRate.RateID  AND vt.CodeDeckId = tblRate.CodeDeckId


						    INNER JOIN 	(select Code,Description from tblRate where CodeDeckId=p_codedeckID ) tmpselectedcd on tmpselectedcd.Code=tblRate.Code

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
							-- AND EffectiveDate <= NOW()
							 AND EffectiveDate <= DATE(p_SelectedEffectiveDate)
							 AND (tblVendorRate.EndDate is NULL OR tblVendorRate.EndDate > now() )    -- rate should not end Today
							 AND tblAccount.IsVendor = 1
							 AND tblAccount.Status = 1
							 AND tblAccount.CurrencyId is not NULL
							 AND tblVendorRate.TrunkID = p_trunkID
							 AND
						        (
						           p_vendor_block = 1 OR
						          (
						             p_vendor_block = 0 AND   (
						                 blockCode.RateId IS NULL  AND blockCountry.CountryId IS NULL
						             )
						         )
						       )
							 -- AND blockCode.RateId IS NULL
							-- AND blockCountry.CountryId IS NULL
					 ) tbl
					order by Code asc;



/* for grooup by description  			*/

			IF p_groupby = 'description' THEN

				INSERT INTO tmp_VendorCurrentRates_
				Select AccountId,max(BlockingId),max(BlockingCountryId) ,max(AccountName),max(Code),Description, MAX(Rate),max(ConnectionFee),max(EffectiveDate),max(TrunkID),max(CountryID),max(RateID),max(Preference)
				FROM (

							 SELECT * ,
								 @row_num := IF(@prev_AccountId = AccountID AND @prev_TrunkID = TrunkID AND @prev_Description = Description AND @prev_EffectiveDate >= EffectiveDate, @row_num + 1, 1) AS RowID,
								 @prev_AccountId := AccountID,
								 @prev_TrunkID := TrunkID,
								 @prev_Description := Description,
								 @prev_EffectiveDate := EffectiveDate
							 FROM tmp_VendorCurrentRates1_
								 ,(SELECT @row_num := 1,  @prev_AccountId := '',@prev_TrunkID := '', @prev_RateId := '', @prev_EffectiveDate := '') x

							 ORDER BY AccountId, TrunkID, RateId, EffectiveDate DESC
						 ) tbl
				WHERE RowID = 1
				group BY AccountId, TrunkID, Description
				order by Description asc;



			Else

/* for grooup by code  */

		INSERT INTO tmp_VendorCurrentRates_
				Select AccountId,BlockingId,BlockingCountryId ,AccountName,Code,Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference
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

      END IF;



		IF p_Preference = 1 THEN

			INSERT IGNORE INTO tmp_VendorRateByRank_
				SELECT
					AccountID,
					BlockingId ,
					BlockingCountryId,
					AccountName,
					Code,
					Rate,
					RateID,
					ConnectionFee,
					EffectiveDate,
					Description,
					Preference,
					preference_rank
				FROM (SELECT
								AccountID,
								BlockingId ,
								BlockingCountryId,
								AccountName,
								Code,
								Rate,
								RateID,
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
					BlockingId ,
					BlockingCountryId,
					AccountName,
					Code,
					Rate,
					RateID,
					ConnectionFee,
					EffectiveDate,
					Description,
					Preference,
					RateRank
				FROM (SELECT
								AccountID,
								BlockingId ,
								BlockingCountryId,
								AccountName,
								Code,
								Rate,
								RateID,
								ConnectionFee,
								EffectiveDate,
								Description,
								Preference,
								CASE WHEN p_groupby = 'description' THEN
								@rank := CASE WHEN ( @prev_Rate < Rate) THEN @rank + 1
												 WHEN ( @prev_Rate = Rate) THEN @rank
												 ELSE 1
												 END
								ELSE
								@rank := CASE WHEN (@prev_Code    = Code AND @prev_Rate < Rate) THEN @rank + 1
												 WHEN (@prev_Code    = Code AND @prev_Rate = Rate) THEN @rank
												 ELSE 1
												 END
								END
									AS RateRank,
								@prev_Code := Code,
								@prev_Rate := Rate,
								@prev_Description := Description
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
				BlockingId ,
				BlockingCountryId,
				AccountName ,
				Code ,
				Rate ,
				RateID,
				ConnectionFee,
				EffectiveDate ,
				Description ,
				Preference,
				Code as RowCode
			from tmp_VendorRateByRank_
			order by RowCode desc;

		IF( p_Preference = 0 )
		THEN

			 /* if group by description preference off */
			IF p_groupby = 'description' THEN


				insert into tmp_final_VendorRate_
					SELECT
						AccountId ,
						BlockingId ,
						BlockingCountryId,
						AccountName ,
						Code ,
						Rate ,
						RateID,
						ConnectionFee,
						EffectiveDate ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								AccountId,
								-- (CASE WHEN (select COUNT(*) from tmp_VendorCurrentRates1_ where AccountId=tmp_VendorRate_.AccountId AND Description=tmp_VendorRate_.Description AND BlockingId=0) > 0 THEN 0 ELSE 1 END) AS  BlockingId,
								(CASE WHEN (select COUNT(*) from tmp_VendorCurrentRates1_ where tmp_VendorCurrentRates1_.AccountId=AccountId AND tmp_VendorCurrentRates1_.Description=Description AND BlockingId=0) > 0 THEN 0 ELSE 1 END) AS  BlockingId,
								BlockingCountryId,
						      AccountName ,
								Code ,
								Rate ,
								RateID,
								ConnectionFee,
								EffectiveDate ,
								Description ,
								Preference,
								RowCode,
								@rank := CASE WHEN (@prev_Description    = Description AND  @prev_Rate <  Rate ) THEN @rank+1
												 WHEN (@prev_Description    = Description AND  @prev_Rate = Rate ) THEN @rank
												 ELSE
													 1
												 END
								AS FinalRankNumber,
								@prev_Rate  := Rate,
								@prev_Description := Description,
								@prev_RateID  := RateID

							from tmp_VendorRate_
								,( SELECT @rank := 0 , @prev_Description := '' , @prev_Rate := 0 ) x
							order by tmp_VendorRate_.Description,Rate,AccountId ASC

						) tbl1
					where
						FinalRankNumber <= p_Position;

			ELSE
					/* if group by code start preference off */
					insert into tmp_final_VendorRate_
					SELECT
						AccountId ,
						BlockingId ,
						BlockingCountryId,
						AccountName ,
						Code ,
						Rate ,
						RateID,
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
								BlockingId,
								BlockingCountryId,
								AccountName ,
								Code ,
								Rate ,
								RateID,
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
					/* if group by code end  preference off*/
			END IF;

		ELSE

			IF p_groupby = 'description' THEN
				/* group by descrion when preference on */
				insert into tmp_final_VendorRate_
					SELECT
						AccountId ,
						(CASE WHEN (select COUNT(*) from tmp_VendorCurrentRates1_ where tmp_VendorCurrentRates1_.AccountId=tbl1.AccountId AND tmp_VendorCurrentRates1_.Description=tbl1.Description AND BlockingId=0) > 0 THEN 0 ELSE 1 END) AS  BlockingId,
						-- (CASE WHEN (select COUNT(*) from tmp_VendorCurrentRates1_ where tmp_VendorCurrentRates1_.AccountId = AccountId  AND tmp_VendorCurrentRates1_.Description=Description AND BlockingId=0) > 0 THEN 0 ELSE 1 END) AS  BlockingId,
						BlockingCountryId,
						AccountName ,
						Code ,
						Rate ,
						RateID,
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
								BlockingId ,
								BlockingCountryId,
								AccountName ,
								Code ,
								Rate ,
								RateID,
								ConnectionFee,
								EffectiveDate ,
								Description ,
								Preference,
								RowCode,
								@preference_rank := CASE WHEN (@prev_Description    = Description AND @prev_Preference > Preference  )   THEN @preference_rank + 1
											WHEN (@prev_Description    = Description AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1
											WHEN (@prev_Description    = Description AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank
											ELSE 1 END
									AS FinalRankNumber,
								@prev_Preference := Preference,
								@prev_Description := Description,
								@prev_Rate := Rate
							from tmp_VendorRate_
								,(SELECT @preference_rank := 0 , @prev_Preference := 5, @prev_Description := '',  @prev_Rate := 0) x
							order by Description ASC ,Preference DESC ,Rate ASC ,AccountId ASC

						) tbl1
					where
						FinalRankNumber <= p_Position;
				ELSE

						/* group by code when preference on start*/
						insert into tmp_final_VendorRate_
							SELECT
								AccountId ,
								BlockingId ,
								BlockingCountryId,
								AccountName ,
								Code ,
								Rate ,
								RateID,
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
										BlockingId ,
										BlockingCountryId,
										AccountName ,
										Code ,
										Rate ,
										RateID,
										ConnectionFee,
										EffectiveDate ,
										Description ,
										Preference,
										RowCode,
										@preference_rank := CASE WHEN (@prev_Code     = RowCode AND @prev_Preference > Preference  )   THEN @preference_rank + 1
													WHEN (@prev_Code     = RowCode AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1
													WHEN (@prev_Code    = RowCode AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank
													ELSE 1 END
										AS FinalRankNumber,
										@prev_Code := RowCode,
										@prev_Preference := Preference,
										@prev_Rate := Rate
									from tmp_VendorRate_
										,(SELECT @preference_rank := 0 , @prev_Code := ''  , @prev_Preference := 5,  @prev_Rate := 0) x
									order by RowCode ASC ,Preference DESC ,Rate ASC ,AccountId ASC

								) tbl1
							where
								FinalRankNumber <= p_Position;
						/* group by code when preference on end */

				END IF;
		END IF;


		SET @stm_columns = "";

		-- if not export then columns must be max 10
		IF p_isExport = 0 AND p_Position > 10 THEN
			SET p_Position = 10;
		END IF;

		-- if export then all columns
		IF p_isExport = 1 THEN
			SELECT COUNT(*) INTO p_Position FROM tmp_final_VendorRate_;
		END IF;

		-- columns loop 5,10,50,...
		SET v_pointer_=1;
		WHILE v_pointer_ <= p_Position
		DO

			SET @stm_columns = CONCAT(@stm_columns, "GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = ",v_pointer_,", CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION ",v_pointer_,"`,");

			SET v_pointer_ = v_pointer_ + 1;

		END WHILE;

		SET @stm_columns = TRIM(TRAILING ',' FROM @stm_columns);

		IF (p_isExport = 0)
		THEN

		   IF p_groupby = 'description' THEN

				SET @stm_query = CONCAT("SELECT	CONCAT(max(t.Description)) as Destination,",@stm_columns," FROM tmp_final_VendorRate_  t GROUP BY  t.Description ORDER BY t.Description ASC LIMIT ",p_RowspPage," OFFSET ",v_OffSet_," ;");

					/*SELECT
					   CONCAT(max(t.Description)) as Destination,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 1`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 2`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 3`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 4`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 5`
					FROM tmp_final_VendorRate_  t
					GROUP BY  t.Description
					ORDER BY t.Description ASC
					LIMIT p_RowspPage OFFSET v_OffSet_ ;*/

			ELSE

				SET @stm_query = CONCAT("SELECT	CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,",@stm_columns," FROM tmp_final_VendorRate_  t GROUP BY  RowCode ORDER BY RowCode ASC LIMIT ",p_RowspPage," OFFSET ",v_OffSet_," ;");

					/*SELECT
					   CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 1`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 2`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 3`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 4`,
						GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'', '=', ANY_VALUE(t.BlockingId), '-', ANY_VALUE(t.AccountId), '-', ANY_VALUE(t.RowCode), '-', ANY_VALUE(t.BlockingCountryId) ), NULL))AS `POSITION 5`
					FROM tmp_final_VendorRate_  t
					GROUP BY  RowCode
					ORDER BY RowCode ASC
					LIMIT p_RowspPage OFFSET v_OffSet_ ;*/

			END IF;


			SELECT count(distinct RowCode) as totalcount from tmp_final_VendorRate_ where ( CHAR_LENGTH(RTRIM(p_code)) = 0  OR RowCode LIKE REPLACE(p_code,'*', '%') );

		ELSE

			IF p_groupby = 'description' THEN

				SET @stm_query = CONCAT("SELECT CONCAT(max(t.Description)) as Destination,",@stm_columns," FROM tmp_final_VendorRate_  t GROUP BY  t.Description ORDER BY t.Description ASC;");

				/*SELECT
					CONCAT(max(t.Description)) as Destination,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),''), NULL))AS `POSITION 1`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),''), NULL))AS `POSITION 2`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),''), NULL))AS `POSITION 3`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),''), NULL))AS `POSITION 4`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),''), NULL))AS `POSITION 5`
				FROM tmp_final_VendorRate_  t
				GROUP BY  t.Description
				ORDER BY t.Description ASC;*/

			ELSE

				SET @stm_query = CONCAT("SELECT CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,",@stm_columns," FROM tmp_final_VendorRate_  t GROUP BY  RowCode ORDER BY RowCode ASC;");

				/*SELECT
					CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),''), NULL))AS `POSITION 1`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),''), NULL))AS `POSITION 2`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),''), NULL))AS `POSITION 3`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),''), NULL))AS `POSITION 4`,
					GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),''), NULL))AS `POSITION 5`
				FROM tmp_final_VendorRate_  t
				GROUP BY  RowCode
				ORDER BY RowCode ASC;*/

			END IF;

		END IF;

		PREPARE stm_query FROM @stm_query;
		EXECUTE stm_query;
		DEALLOCATE PREPARE stm_query;


		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

	END//
DELIMITER ;











DROP PROCEDURE IF EXISTS `prc_SplitVendorRate`;
DELIMITER //
CREATE PROCEDURE `prc_SplitVendorRate`(
	IN `p_processId` VARCHAR(200),
	IN `p_dialcodeSeparator` VARCHAR(50)
)
ThisSP:BEGIN

	DECLARE i INTEGER;
	DECLARE v_rowCount_ INT;
	DECLARE v_pointer_ INT;
	DECLARE v_TempVendorRateID_ INT;
	DECLARE v_Code_ TEXT;
	DECLARE v_CountryCode_ VARCHAR(500);
	DECLARE newcodecount INT(11) DEFAULT 0;

	IF p_dialcodeSeparator !='null'
	THEN





	DROP TEMPORARY TABLE IF EXISTS `my_splits`;
	CREATE TEMPORARY TABLE `my_splits` (
		`TempVendorRateID` INT(11) NULL DEFAULT NULL,
		`Code` Text NULL DEFAULT NULL,
		`CountryCode` Text NULL DEFAULT NULL
	);

  SET i = 1;
  REPEAT
    INSERT INTO my_splits (TempVendorRateID, Code, CountryCode)
      SELECT TempVendorRateID , FnStringSplit(Code, p_dialcodeSeparator, i), CountryCode  FROM tblTempVendorRate
      WHERE FnStringSplit(Code, p_dialcodeSeparator , i) IS NOT NULL
			 AND ProcessId = p_processId;
    SET i = i + 1;
    UNTIL ROW_COUNT() = 0
  END REPEAT;

  UPDATE my_splits SET Code = trim(Code);

	INSERT INTO my_splits (TempVendorRateID, Code, CountryCode)
	SELECT TempVendorRateID , Code, CountryCode  FROM tblTempVendorRate
	WHERE (CountryCode IS NOT NULL AND CountryCode <> '') AND (Code IS NULL OR Code = '')
	AND ProcessId = p_processId;



  DROP TEMPORARY TABLE IF EXISTS tmp_newvendor_splite_;
	CREATE TEMPORARY TABLE tmp_newvendor_splite_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		TempVendorRateID INT(11) NULL DEFAULT NULL,
		Code VARCHAR(500) NULL DEFAULT NULL,
		CountryCode VARCHAR(500) NULL DEFAULT NULL
	);

	INSERT INTO tmp_newvendor_splite_(TempVendorRateID,Code,CountryCode)
	SELECT
		TempVendorRateID,
		Code,
		CountryCode
	FROM my_splits
	WHERE Code like '%-%'
		AND TempVendorRateID IS NOT NULL;



	SET v_pointer_ = 1;
	SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_newvendor_splite_);

	WHILE v_pointer_ <= v_rowCount_
	DO
		SET v_TempVendorRateID_ = (SELECT TempVendorRateID FROM tmp_newvendor_splite_ t WHERE t.RowID = v_pointer_);
		SET v_Code_ = (SELECT Code FROM tmp_newvendor_splite_ t WHERE t.RowID = v_pointer_);
		SET v_CountryCode_ = (SELECT CountryCode FROM tmp_newvendor_splite_ t WHERE t.RowID = v_pointer_);

		Call prc_SplitAndInsertVendorRate(v_TempVendorRateID_,v_Code_,v_CountryCode_);

	SET v_pointer_ = v_pointer_ + 1;
	END WHILE;


	DELETE FROM my_splits
		WHERE Code like '%-%'
			AND TempVendorRateID IS NOT NULL;

	DELETE FROM my_splits
		WHERE (Code = '' OR Code IS NULL) AND (CountryCode = '' OR CountryCode IS NULL);

	 INSERT INTO tmp_split_VendorRate_
	SELECT DISTINCT
		   my_splits.TempVendorRateID as `TempVendorRateID`,
		   `CodeDeckId`,
		   CONCAT(IFNULL(my_splits.CountryCode,''),my_splits.Code) as Code,
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
		 FROM my_splits
		   INNER JOIN tblTempVendorRate
				ON my_splits.TempVendorRateID = tblTempVendorRate.TempVendorRateID
		  WHERE	tblTempVendorRate.ProcessId = p_processId;

	END IF;




	IF p_dialcodeSeparator = 'null'
	THEN

		INSERT INTO tmp_split_VendorRate_
		SELECT DISTINCT
			  `TempVendorRateID`,
			  `CodeDeckId`,
			   CONCAT(IFNULL(tblTempVendorRate.CountryCode,''),tblTempVendorRate.Code) as Code,
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
			 FROM tblTempVendorRate
			  WHERE ProcessId = p_processId;

	END IF;

END//
DELIMITER ;















DROP PROCEDURE IF EXISTS `prc_WSGenerateRateTable`;
DELIMITER //
CREATE PROCEDURE `prc_WSGenerateRateTable`(
	IN `p_jobId` INT,
	IN `p_RateGeneratorId` INT,
	IN `p_RateTableId` INT,
	IN `p_rateTableName` VARCHAR(200),
	IN `p_EffectiveDate` VARCHAR(10),
	IN `p_delete_exiting_rate` INT,
	IN `p_EffectiveRate` VARCHAR(50),
	IN `p_GroupBy` VARCHAR(50),
	IN `p_ModifiedBy` VARCHAR(50)
)
GenerateRateTable:BEGIN


		DECLARE i INTEGER;
		DECLARE v_RTRowCount_ INT;
		DECLARE v_RatePosition_ INT;
		DECLARE v_Use_Preference_ INT;
		DECLARE v_CurrencyID_ INT;
		DECLARE v_CompanyCurrencyID_ INT;
		DECLARE v_Average_ TINYINT;
		DECLARE v_CompanyId_ INT;
		DECLARE v_codedeckid_ INT;
		DECLARE v_trunk_ INT;
		DECLARE v_rateRuleId_ INT;
		DECLARE v_RateGeneratorName_ VARCHAR(200);
		DECLARE v_pointer_ INT ;
		DECLARE v_rowCount_ INT ;

		DECLARE v_IncreaseEffectiveDate_ DATETIME ;
		DECLARE v_DecreaseEffectiveDate_ DATETIME ;





		DECLARE v_tmp_code_cnt int ;
		DECLARE v_tmp_code_pointer int;
		DECLARE v_p_code varchar(50);
		DECLARE v_Codlen_ int;
		DECLARE v_p_code__ VARCHAR(50);
		DECLARE v_Commit int;
		DECLARE EXIT HANDLER FOR SQLEXCEPTION
		BEGIN
			SHOW WARNINGS;
			ROLLBACK;
			CALL prc_WSJobStatusUpdate(p_jobId, 'F', 'RateTable generation failed', '');

		END;

		SET @@session.collation_connection='utf8_unicode_ci';
		SET @@session.character_set_client='utf8';
		SET SESSION group_concat_max_len = 1000000; -- change group_concat limit for group by


		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;



		SET p_EffectiveDate = CAST(p_EffectiveDate AS DATE);


		IF p_rateTableName IS NOT NULL
		THEN


			SET v_RTRowCount_ = (SELECT
														 COUNT(*)
													 FROM tblRateTable
													 WHERE RateTableName = p_rateTableName
																 AND CompanyId = (SELECT
																										CompanyId
																									FROM tblRateGenerator
																									WHERE RateGeneratorID = p_RateGeneratorId));

			IF v_RTRowCount_ > 0
			THEN
				CALL prc_WSJobStatusUpdate  (p_jobId, 'F', 'RateTable Name is already exist, Please try using another RateTable Name', '');
				LEAVE GenerateRateTable;
			END IF;
		END IF;

		DROP TEMPORARY TABLE IF EXISTS tmp_Rates_;
		CREATE TEMPORARY TABLE tmp_Rates_  (
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			PreviousRate DECIMAL(18, 6),
			EffectiveDate DATE DEFAULT NULL,
			INDEX tmp_Rates_code (`code`) ,
			UNIQUE KEY `unique_code` (`code`)

		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Rates2_;
		CREATE TEMPORARY TABLE tmp_Rates2_  (
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			PreviousRate DECIMAL(18, 6),
			EffectiveDate DATE DEFAULT NULL,
			INDEX tmp_Rates2_code (`code`)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_Codedecks_;
		CREATE TEMPORARY TABLE tmp_Codedecks_ (
			CodeDeckId INT
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Raterules_;

		CREATE TEMPORARY TABLE tmp_Raterules_  (
			rateruleid INT,
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			description VARCHAR(200) COLLATE utf8_unicode_ci,
			RowNo INT,
			`Order` INT,
			INDEX tmp_Raterules_code (`code`,`description`),
			INDEX tmp_Raterules_rateruleid (`rateruleid`),
			INDEX tmp_Raterules_RowNo (`RowNo`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Raterules_dup;

		CREATE TEMPORARY TABLE tmp_Raterules_dup  (
			rateruleid INT,
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			description VARCHAR(200) COLLATE utf8_unicode_ci,
			RowNo INT,
			`Order` INT,
			INDEX tmp_Raterules_code (`code`,`description`),
			INDEX tmp_Raterules_rateruleid (`rateruleid`),
			INDEX tmp_Raterules_RowNo (`RowNo`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Vendorrates_;
		CREATE TEMPORARY TABLE tmp_Vendorrates_  (
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			AccountId INT,
			RowNo INT,
			PreferenceRank INT,
			INDEX tmp_Vendorrates_code (`code`),
			INDEX tmp_Vendorrates_rate (`rate`)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_VRatesstage2_;
		CREATE TEMPORARY TABLE tmp_VRatesstage2_  (
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci,
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			FinalRankNumber int,
			INDEX tmp_Vendorrates_stage2__code (`RowCode`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_dupVRatesstage2_;
		CREATE TEMPORARY TABLE tmp_dupVRatesstage2_  (
			RowCode VARCHAR(50)  COLLATE utf8_unicode_ci,
			FinalRankNumber int,
			INDEX tmp_dupVendorrates_stage2__code (`RowCode`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Vendorrates_stage3_;
		CREATE TEMPORARY TABLE tmp_Vendorrates_stage3_  (
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			INDEX tmp_Vendorrates_stage2__code (`RowCode`)
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_code_;
		CREATE TEMPORARY TABLE tmp_code_  (
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			INDEX tmp_code_code (`code`)
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_all_code_;
		CREATE TEMPORARY TABLE tmp_all_code_ (
			RowCode  varchar(50) COLLATE utf8_unicode_ci,
			Code  varchar(50) COLLATE utf8_unicode_ci,
			RowNo int,
			INDEX Index2 (Code)
		);



		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage_;
		CREATE TEMPORARY TABLE tmp_VendorRate_stage_ (
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci,
			AccountId INT ,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) COLLATE utf8_unicode_ci,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			MaxMatchRank int ,
			prev_prev_RowCode VARCHAR(50),
			prev_AccountID int
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
		CREATE TEMPORARY TABLE tmp_VendorRate_ (
			AccountId INT ,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) COLLATE utf8_unicode_ci,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_final_VendorRate_;
		CREATE TEMPORARY TABLE tmp_final_VendorRate_ (
			AccountId INT ,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) COLLATE utf8_unicode_ci,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci,
			FinalRankNumber int,
			INDEX IX_CODE (RowCode)
		);

		-- when group by description this table use to insert comma seperated codes
		DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates_GroupBy_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates_GroupBy_(
			AccountId int,
			AccountName varchar(200),
			Code LONGTEXT,
			Description varchar(200) ,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate date,
			TrunkID int,
			CountryID int,
			RateID int,
			Preference int/*,
			INDEX IX_CODE (Code)*/
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates_(
			AccountId int,
			AccountName varchar(200),
			Code varchar(50) COLLATE utf8_unicode_ci,
			Description varchar(200) ,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate date,
			TrunkID int,
			CountryID int,
			RateID int,
			Preference int,
			INDEX IX_CODE (Code)
		);

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
		);

		SELECT CurrencyID INTO v_CurrencyID_ FROM  tblRateGenerator WHERE RateGeneratorId = p_RateGeneratorId;

		-- get Increase Decrease date from Job
		SELECT IFNULL(REPLACE(JSON_EXTRACT(Options, '$.IncreaseEffectiveDate'),'"',''), p_EffectiveDate) , IFNULL(REPLACE(JSON_EXTRACT(Options, '$.DecreaseEffectiveDate'),'"',''), p_EffectiveDate)   INTO v_IncreaseEffectiveDate_ , v_DecreaseEffectiveDate_  FROM tblJob WHERE Jobid = p_jobId;


		IF v_IncreaseEffectiveDate_ is null OR v_IncreaseEffectiveDate_ = '' THEN

			SET v_IncreaseEffectiveDate_ = p_EffectiveDate;

		END IF;

		IF v_DecreaseEffectiveDate_ is null OR v_DecreaseEffectiveDate_ = '' THEN

			SET v_DecreaseEffectiveDate_ = p_EffectiveDate;

		END IF;


		SELECT
			UsePreference,
			rateposition,
			companyid ,
			CodeDeckId,
			tblRateGenerator.TrunkID,
			tblRateGenerator.UseAverage  ,
			tblRateGenerator.RateGeneratorName INTO v_Use_Preference_, v_RatePosition_, v_CompanyId_, v_codedeckid_, v_trunk_, v_Average_, v_RateGeneratorName_
		FROM tblRateGenerator
		WHERE RateGeneratorId = p_RateGeneratorId;


		SELECT CurrencyId INTO v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = v_CompanyId_;




		INSERT INTO tmp_Raterules_
			SELECT
				rateruleid,
				tblRateRule.Code,
				tblRateRule.Description,
				@row_num := @row_num+1 AS RowID,
				tblRateRule.`Order`
			FROM tblRateRule,(SELECT @row_num := 0) x
			WHERE rategeneratorid = p_RateGeneratorId
			ORDER BY tblRateRule.`Order` ASC;  -- <== order of rule is important

		-- v 4.17 fix process rules in order  -- NEON-1292 		Otto Rate Generator issue
		insert into tmp_Raterules_dup (			rateruleid ,		code ,		description ,		RowNo 	,	`Order`)
		select rateruleid ,		code ,		description ,		RowNo, `Order` from tmp_Raterules_;

		INSERT INTO tmp_Codedecks_
			SELECT DISTINCT
				tblVendorTrunk.CodeDeckId
			FROM tblRateRule
				INNER JOIN tblRateRuleSource
					ON tblRateRule.RateRuleId = tblRateRuleSource.RateRuleId
				INNER JOIN tblAccount
					ON tblAccount.AccountID = tblRateRuleSource.AccountId and tblAccount.IsVendor = 1
				JOIN tblVendorTrunk
					ON tblAccount.AccountId = tblVendorTrunk.AccountID
						 AND  tblVendorTrunk.TrunkID = v_trunk_
						 AND tblVendorTrunk.Status = 1
			WHERE RateGeneratorId = p_RateGeneratorId;

		SET v_pointer_ = 1;
		-- SET v_rowCount_ = (SELECT COUNT(distinct Code ) FROM tmp_Raterules_);
		SET v_rowCount_ = (SELECT COUNT(distinct concat(Code,Description) ) FROM tmp_Raterules_);




		insert into tmp_code_
			SELECT  DISTINCT LEFT(f.Code, x.RowNo) as loopCode
			FROM (
						 SELECT @RowNo  := @RowNo + 1 as RowNo
						 FROM mysql.help_category
							 ,(SELECT @RowNo := 0 ) x
						 limit 15
					 ) x
				INNER JOIN
				(SELECT
					 distinct
					 tblRate.code
				 FROM tblRate
					 JOIN tmp_Raterules_ rr
						 ON   ( rr.code = '' OR (rr.code != '' AND tblRate.Code LIKE (REPLACE(rr.code,'*', '%%')) ))
									AND
									( rr.description = '' OR ( rr.description != '' AND tblRate.Description LIKE (REPLACE(rr.description,'*', '%%')) ) )
				 where  tblRate.CodeDeckId = v_codedeckid_
				 Order by tblRate.code
				) as f
					ON   x.RowNo   <= LENGTH(f.Code)
			order by loopCode   desc;





		SELECT CurrencyId INTO v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = v_CompanyId_;
		SET @IncludeAccountIds = (SELECT GROUP_CONCAT(AccountId) from tblRateRule rr inner join  tblRateRuleSource rrs on rr.RateRuleId = rrs.RateRuleId where rr.RateGeneratorId = p_RateGeneratorId ) ;




		INSERT INTO tmp_VendorCurrentRates1_
			Select DISTINCT AccountId,AccountName,Code,Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference
			FROM (
						 SELECT  tblVendorRate.AccountId,tblAccount.AccountName, tblRate.Code, tblRate.Description,
																																				CASE WHEN  tblAccount.CurrencyId = v_CurrencyID_
																																					THEN
																																						tblVendorRate.Rate
																																				WHEN  v_CompanyCurrencyID_ = v_CurrencyID_
																																					THEN
																																						(
																																							( tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ) )
																																						)
																																				ELSE
																																					(

																																						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = v_CurrencyID_ and  CompanyID = v_CompanyId_ )
																																						* (tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ))
																																					)
																																				END
																																																																																																																																														as  Rate,
							 ConnectionFee,
																																				DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') AS EffectiveDate,
							 tblVendorRate.TrunkID, tblRate.CountryID, tblRate.RateID,IFNULL(vp.Preference, 5) AS Preference,
																																				@row_num := IF(@prev_AccountId = tblVendorRate.AccountID AND @prev_TrunkID = tblVendorRate.TrunkID AND @prev_RateId = tblVendorRate.RateID AND @prev_EffectiveDate >= tblVendorRate.EffectiveDate, @row_num + 1, 1) AS RowID,
							 @prev_AccountId := tblVendorRate.AccountID,
							 @prev_TrunkID := tblVendorRate.TrunkID,
							 @prev_RateId := tblVendorRate.RateID,
							 @prev_EffectiveDate := tblVendorRate.EffectiveDate
						 FROM      tblVendorRate
							 Inner join tblVendorTrunk vt on vt.CompanyID = v_CompanyId_ AND vt.AccountID = tblVendorRate.AccountID and vt.Status =  1 and vt.TrunkID =  v_trunk_
							 inner join tmp_Codedecks_ tcd on vt.CodeDeckId = tcd.CodeDeckId
							 INNER JOIN tblAccount   ON  tblAccount.CompanyID = v_CompanyId_ AND tblVendorRate.AccountId = tblAccount.AccountID and tblAccount.IsVendor = 1
							 INNER JOIN tblRate ON tblRate.CompanyID = v_CompanyId_  AND tblRate.CodeDeckId = vt.CodeDeckId  AND    tblVendorRate.RateId = tblRate.RateID
							 INNER JOIN tmp_code_ tcode ON tcode.Code  = tblRate.Code
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

							 ,(SELECT @row_num := 1,  @prev_AccountId := '',@prev_TrunkID := '', @prev_RateId := '', @prev_EffectiveDate := '') x

						 WHERE
							 (
								 (p_EffectiveRate = 'now' AND EffectiveDate <= NOW())
								 OR
								 (p_EffectiveRate = 'future' AND EffectiveDate > NOW()  )
								 OR
								 (	 p_EffectiveRate = 'effective' AND EffectiveDate <= p_EffectiveDate
										 AND ( tblVendorRate.EndDate IS NULL OR (tblVendorRate.EndDate > DATE(p_EffectiveDate)) )
								 )  -- rate should not end on selected effective date
							 )
							 AND ( tblVendorRate.EndDate IS NULL OR tblVendorRate.EndDate > now() )  -- rate should not end Today
							 AND tblAccount.IsVendor = 1
							 AND tblAccount.Status = 1
							 AND tblAccount.CurrencyId is not NULL
							 AND tblVendorRate.TrunkID = v_trunk_
							 AND blockCode.RateId IS NULL
							 AND blockCountry.CountryId IS NULL
							 AND ( @IncludeAccountIds = NULL
										 OR ( @IncludeAccountIds IS NOT NULL
													AND FIND_IN_SET(tblVendorRate.AccountId,@IncludeAccountIds) > 0
										 )
							 )
						 ORDER BY tblVendorRate.AccountId, tblVendorRate.TrunkID, tblVendorRate.RateId, tblVendorRate.EffectiveDate DESC
					 ) tbl
			order by Code asc;

		IF p_GroupBy = 'Desc' -- Group By Description
		THEN
			-- insert all rates and if code is multiple then insert it as comma seperated values
			INSERT INTO tmp_VendorCurrentRates_GroupBy_
				Select AccountId,max(AccountName),GROUP_CONCAT(Code),Description,max(Rate),max(ConnectionFee),max(EffectiveDate),TrunkID,max(CountryID),max(RateID),max(Preference)
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
				GROUP BY AccountId, TrunkID, Description
				order by Description asc;

				-- split and insert comma seperated codes
				SET i = 1;
				REPEAT
					INSERT INTO tmp_VendorCurrentRates_ (AccountId,AccountName,Code,Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference)
				  	SELECT
					  	AccountId,AccountName,FnStringSplit(Code, ',', i),Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference
					FROM
						tmp_VendorCurrentRates_GroupBy_
				  	WHERE
					  	FnStringSplit(Code, ',' , i) IS NOT NULL;

					SET i = i + 1;
				  	UNTIL ROW_COUNT() = 0
				END REPEAT;

		ELSE

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

		END IF;










		/* convert 9131 to all possible codes
			9131
			913
			91
		 */
		insert into tmp_all_code_ (RowCode,Code,RowNo)
			select RowCode , loopCode,RowNo
			from (
						 select   RowCode , loopCode,
							 @RowNo := ( CASE WHEN (@prev_Code  = tbl1.RowCode  ) THEN @RowNo + 1
													 ELSE 1
													 END

							 )      as RowNo,
							 @prev_Code := tbl1.RowCode

						 from (
										SELECT distinct f.Code as RowCode, LEFT(f.Code, x.RowNo) as loopCode
										FROM (
													 SELECT @RowNo  := @RowNo + 1 as RowNo
													 FROM mysql.help_category
														 ,(SELECT @RowNo := 0 ) x
													 limit 15
												 ) x
											INNER JOIN
											(
												select distinct Code from
													tmp_VendorCurrentRates_
											) AS f
												ON  x.RowNo   <= LENGTH(f.Code)
										order by RowCode desc,  LENGTH(loopCode) DESC
									) tbl1
							 , ( Select @RowNo := 0 ) x
					 ) tbl order by RowCode desc,  LENGTH(loopCode) DESC ;








		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage_1;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRate_stage_1 as (select * from tmp_VendorRate_stage_);

		insert ignore into tmp_VendorRate_stage_1 (
			RowCode,
			AccountId ,
			AccountName ,
			Code ,
			Rate ,
			ConnectionFee,
			EffectiveDate ,
			Description ,
			Preference
		)
			SELECT
				distinct
				RowCode,
				v.AccountId ,
				v.AccountName ,
				v.Code ,
				v.Rate ,
				v.ConnectionFee,
				v.EffectiveDate ,
				v.Description ,
				v.Preference
			FROM tmp_VendorCurrentRates_ v
				Inner join  tmp_all_code_
										SplitCode   on v.Code = SplitCode.Code
			where  SplitCode.Code is not null
			order by AccountID,SplitCode.RowCode desc ,LENGTH(SplitCode.RowCode), v.Code desc, LENGTH(v.Code)  desc;



		insert into tmp_VendorRate_stage_
			SELECT
				RowCode,
				v.AccountId ,
				v.AccountName ,
				v.Code ,
				v.Rate ,
				v.ConnectionFee,
				v.EffectiveDate ,
				v.Description ,
				v.Preference,
				@rank := ( CASE WHEN ( @prev_RowCode   = RowCode and   @prev_AccountID = v.AccountId   )
					THEN @rank + 1
									 ELSE 1  END ) AS MaxMatchRank,

				@prev_RowCode := RowCode	 as prev_RowCode,
				@prev_AccountID := v.AccountId as prev_AccountID
			FROM tmp_VendorRate_stage_1 v
				, (SELECT  @prev_RowCode := '',  @rank := 0 , @prev_Code := '' , @prev_AccountID := Null) f
			order by AccountID,RowCode desc ;


		truncate tmp_VendorRate_;
		insert into tmp_VendorRate_
			select
				AccountId ,
				AccountName ,
				Code ,
				Rate ,
				ConnectionFee,
				EffectiveDate ,
				Description ,
				Preference,
				RowCode
			from tmp_VendorRate_stage_
			where MaxMatchRank = 1 order by RowCode desc;







		WHILE v_pointer_ <= v_rowCount_
		DO

			SET v_rateRuleId_ = (SELECT rateruleid FROM tmp_Raterules_ rr WHERE rr.RowNo = v_pointer_);


			INSERT INTO tmp_Rates2_ (code,rate,ConnectionFee)
				select  code,rate,ConnectionFee from tmp_Rates_;



			truncate tmp_final_VendorRate_;

			IF( v_Use_Preference_ = 0 )
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
								vr.AccountId ,
								vr.AccountName ,
								vr.Code ,
								vr.Rate ,
								vr.ConnectionFee,
								vr.EffectiveDate ,
								vr.Description ,
								vr.Preference,
								vr.RowCode,
								@rank := CASE WHEN ( @prev_RowCode = vr.RowCode  AND @prev_Rate <  vr.Rate ) THEN @rank+1
												 WHEN ( @prev_RowCode  = vr.RowCode  AND @prev_Rate = vr.Rate) THEN @rank
												 ELSE
													 1
												 END
									AS FinalRankNumber,
								@prev_RowCode  := vr.RowCode,
								@prev_Rate  := vr.Rate
							from (
										 select distinct tmpvr.*
										 from tmp_VendorRate_  tmpvr
											 inner JOIN tmp_Raterules_ rr ON rr.RateRuleId = v_rateRuleId_ and
																											 (
																												 ( rr.code != '' AND tmpvr.RowCode LIKE (REPLACE(rr.code,'*', '%%')) )
																												 OR
																												 ( rr.description != '' AND tmpvr.Description LIKE (REPLACE(rr.description,'*', '%%')) )
																											 )
											 left JOIN tmp_Raterules_dup rr2 ON rr2.Order > rr.Order and
																											 (
																												 ( rr2.code != '' AND tmpvr.RowCode LIKE (REPLACE(rr2.code,'*', '%%')) )
																												 OR
																												 ( rr2.description != '' AND tmpvr.Description LIKE (REPLACE(rr2.description,'*', '%%')) )
																											 )
											 inner JOIN tblRateRuleSource rrs ON  rrs.RateRuleId = rr.rateruleid  and rrs.AccountId = tmpvr.AccountId
										 	 where rr2.code is null

									 ) vr
								,(SELECT @rank := 0 , @prev_RowCode := '' , @prev_Rate := 0  ) x
							order by vr.RowCode,vr.Rate,vr.AccountId ASC

						) tbl1
					where FinalRankNumber <= v_RatePosition_;

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
								vr.AccountId ,
								vr.AccountName ,
								vr.Code ,
								vr.Rate ,
								vr.ConnectionFee,
								vr.EffectiveDate ,
								vr.Description ,
								vr.Preference,
								vr.RowCode,
								@preference_rank := CASE WHEN (@prev_Code  = vr.RowCode  AND @prev_Preference > vr.Preference  )   THEN @preference_rank + 1
																		WHEN (@prev_Code  = vr.RowCode  AND @prev_Preference = vr.Preference AND @prev_Rate < vr.Rate) THEN @preference_rank + 1
																		WHEN (@prev_Code  = vr.RowCode  AND @prev_Preference = vr.Preference AND @prev_Rate = vr.Rate) THEN @preference_rank
																		ELSE 1 END AS FinalRankNumber,
								@prev_Code := vr.RowCode,
								@prev_Preference := vr.Preference,
								@prev_Rate := vr.Rate
							from (

								select distinct tmpvr.*
								from tmp_VendorRate_  tmpvr
									inner JOIN tmp_Raterules_ rr ON rr.RateRuleId = v_rateRuleId_ and
																									(
																										( rr.code != '' AND tmpvr.RowCode LIKE (REPLACE(rr.code,'*', '%%')) )
																										OR
																										( rr.description != '' AND tmpvr.Description LIKE (REPLACE(rr.description,'*', '%%')) )
																									)
									left JOIN tmp_Raterules_dup rr2 ON rr2.Order > rr.Order and
																											(
																												( rr2.code != '' AND tmpvr.RowCode LIKE (REPLACE(rr2.code,'*', '%%')) )
																												OR
																												( rr2.description != '' AND tmpvr.Description LIKE (REPLACE(rr2.description,'*', '%%')) )
																											)
									inner JOIN tblRateRuleSource rrs ON  rrs.RateRuleId = rr.rateruleid  and rrs.AccountId = tmpvr.AccountId
									where rr2.code is null

									 ) vr

								,(SELECT @preference_rank := 0 , @prev_Code := ''  , @prev_Preference := 5,  @prev_Rate := 0) x
							order by vr.RowCode ASC ,vr.Preference DESC ,vr.Rate ASC ,vr.AccountId ASC

						) tbl1
					where 				FinalRankNumber <= v_RatePosition_;

			END IF;



			truncate   tmp_VRatesstage2_;

			INSERT INTO tmp_VRatesstage2_
				SELECT
					vr.RowCode,
					vr.code,
					vr.rate,
					vr.ConnectionFee,
					vr.FinalRankNumber
				FROM tmp_final_VendorRate_ vr
					left join tmp_Rates2_ rate on rate.Code = vr.RowCode
				WHERE  rate.code is null
				order by vr.FinalRankNumber desc ;



			IF v_Average_ = 0
			THEN
				insert into tmp_dupVRatesstage2_
					SELECT RowCode , MAX(FinalRankNumber) AS MaxFinalRankNumber
					FROM tmp_VRatesstage2_ GROUP BY RowCode;

				truncate tmp_Vendorrates_stage3_;
				INSERT INTO tmp_Vendorrates_stage3_
					select  vr.RowCode as RowCode , vr.rate as rate , vr.ConnectionFee as  ConnectionFee
					from tmp_VRatesstage2_ vr
						INNER JOIN tmp_dupVRatesstage2_ vr2
							ON (vr.RowCode = vr2.RowCode AND  vr.FinalRankNumber = vr2.FinalRankNumber);

				INSERT IGNORE INTO tmp_Rates_ (code,rate,ConnectionFee,PreviousRate)
                SELECT RowCode,
                    CASE WHEN rule_mgn.RateRuleId is not null
                        THEN
                            CASE WHEN trim(IFNULL(AddMargin,"")) != '' THEN
                                vRate.rate + (CASE WHEN addmargin LIKE '%p' THEN ((CAST(REPLACE(addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * vRate.rate) ELSE addmargin END)
                            WHEN trim(IFNULL(FixedValue,"")) != '' THEN
                                FixedValue
                            ELSE
                                vRate.rate
                            END
                    ELSE
                        vRate.rate
                    END as Rate,
                    ConnectionFee,
					null AS PreviousRate
                FROM tmp_Vendorrates_stage3_ vRate
                LEFT join tblRateRuleMargin rule_mgn on  rule_mgn.RateRuleId = v_rateRuleId_ and vRate.rate Between rule_mgn.MinRate and rule_mgn.MaxRate;




			ELSE

				INSERT IGNORE INTO tmp_Rates_ (code,rate,ConnectionFee,PreviousRate)
                SELECT RowCode,
                    CASE WHEN rule_mgn.RateRuleId is not null
                        THEN
                            CASE WHEN trim(IFNULL(AddMargin,"")) != '' THEN
                                vRate.rate + (CASE WHEN addmargin LIKE '%p' THEN ((CAST(REPLACE(addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * vRate.rate) ELSE addmargin END)
                            WHEN trim(IFNULL(FixedValue,"")) != '' THEN
                                FixedValue
                            ELSE
                                vRate.rate
                            END
                    ELSE
                        vRate.rate
                    END as Rate,
                    ConnectionFee,
					null AS PreviousRate
                FROM
                (
                    select RowCode,
                    AVG(Rate) as Rate,
                    AVG(ConnectionFee) as ConnectionFee
                    from tmp_VRatesstage2_
                    group by RowCode
                )  vRate
                LEFT join tblRateRuleMargin rule_mgn on  rule_mgn.RateRuleId = v_rateRuleId_ and vRate.rate Between rule_mgn.MinRate and rule_mgn.MaxRate;

			END IF;


			SET v_pointer_ = v_pointer_ + 1;


		END WHILE;



		START TRANSACTION;

		IF p_RateTableId = -1
		THEN

			INSERT INTO tblRateTable (CompanyId, RateTableName, RateGeneratorID, TrunkID, CodeDeckId,CurrencyID)
			VALUES (v_CompanyId_, p_rateTableName, p_RateGeneratorId, v_trunk_, v_codedeckid_,v_CurrencyID_);

			SET p_RateTableId = LAST_INSERT_ID();

			INSERT INTO tblRateTableRate (RateID,
																		RateTableId,
																		Rate,
																		EffectiveDate,
																		PreviousRate,
																		Interval1,
																		IntervalN,
																		ConnectionFee
			)
				SELECT DISTINCT
					RateId,
					p_RateTableId,
					Rate,
					p_EffectiveDate,
					Rate,
					Interval1,
					IntervalN,
					ConnectionFee
				FROM tmp_Rates_ rate
					INNER JOIN tblRate
						ON rate.code  = tblRate.Code
				WHERE tblRate.CodeDeckId = v_codedeckid_;

		ELSE

			IF p_delete_exiting_rate = 1
			THEN
				-- delete all rate table rates of this rate table
				UPDATE
					tblRateTableRate
				SET
					EndDate = NOW()
				WHERE
					tblRateTableRate.RateTableId = p_RateTableId;

				-- delete and archive rates which rate's EndDate <= NOW()
				CALL prc_ArchiveOldRateTableRate(p_RateTableId,CONCAT(p_ModifiedBy,'|RateGenerator')); -- p_ModifiedBy
			END IF;

			-- set EffectiveDate for which date rates need to generate
			UPDATE tmp_Rates_ SET EffectiveDate = p_EffectiveDate;

			-- update Previous Rates By Vasim Seta Start
			UPDATE
				tmp_Rates_ tr
			SET
				PreviousRate = (SELECT rtr.Rate FROM tblRateTableRate rtr JOIN tblRate r ON r.RateID=rtr.RateID WHERE rtr.RateTableID=p_RateTableId AND r.Code=tr.Code AND rtr.EffectiveDate<tr.EffectiveDate ORDER BY rtr.EffectiveDate DESC,rtr.RateTableRateID DESC LIMIT 1);

			UPDATE
				tmp_Rates_ tr
			SET
				PreviousRate = (SELECT rtr.Rate FROM tblRateTableRateArchive rtr JOIN tblRate r ON r.RateID=rtr.RateID WHERE rtr.RateTableID=p_RateTableId AND r.Code=tr.Code AND rtr.EffectiveDate<tr.EffectiveDate ORDER BY rtr.EffectiveDate DESC,rtr.RateTableRateID DESC LIMIT 1)
			WHERE
				PreviousRate is null;
			-- update Previous Rates By Vasim Seta End

			-- update increase decrease effective date starts
			IF v_IncreaseEffectiveDate_ != v_DecreaseEffectiveDate_ THEN

				UPDATE tmp_Rates_
				SET
					tmp_Rates_.EffectiveDate =
					CASE WHEN tmp_Rates_.PreviousRate < tmp_Rates_.Rate THEN
						v_IncreaseEffectiveDate_
					WHEN tmp_Rates_.PreviousRate > tmp_Rates_.Rate THEN
						v_DecreaseEffectiveDate_
					ELSE p_EffectiveDate
					END
				;

			END IF;
			-- update increase decrease effective date ends

			-- delete rates which needs to insert and with same EffectiveDate and rate is not same
			UPDATE
				tblRateTableRate
			INNER JOIN
				tblRate ON tblRate.RateId = tblRateTableRate.RateId
					AND tblRateTableRate.RateTableId = p_RateTableId
				--	AND tblRateTableRate.EffectiveDate = p_EffectiveDate
			INNER JOIN
				tmp_Rates_ as rate ON rate.code = tblRate.Code AND tblRateTableRate.EffectiveDate = rate.EffectiveDate
			SET
				tblRateTableRate.EndDate = NOW()
			WHERE
				tblRateTableRate.RateTableId = p_RateTableId AND
				tblRate.CodeDeckId = v_codedeckid_ AND
				rate.rate != tblRateTableRate.Rate;

			-- delete and archive rates which rate's EndDate <= NOW()
			CALL prc_ArchiveOldRateTableRate(p_RateTableId,CONCAT(p_ModifiedBy,'|RateGenerator')); -- p_ModifiedBy

			-- insert rates
			INSERT INTO tblRateTableRate (RateID,
																		RateTableId,
																		Rate,
																		EffectiveDate,
																		PreviousRate,
																		Interval1,
																		IntervalN,
																		ConnectionFee
			)
				SELECT DISTINCT
					tblRate.RateId,
					p_RateTableId RateTableId,
					rate.Rate,
					rate.EffectiveDate,
					rate.PreviousRate,
					tblRate.Interval1,
					tblRate.IntervalN,
					rate.ConnectionFee
				FROM tmp_Rates_ rate
					INNER JOIN tblRate
						ON rate.code  = tblRate.Code
					LEFT JOIN tblRateTableRate tbl1
						ON tblRate.RateId = tbl1.RateId
							 AND tbl1.RateTableId = p_RateTableId
					LEFT JOIN tblRateTableRate tbl2
						ON tblRate.RateId = tbl2.RateId
							 and tbl2.EffectiveDate = rate.EffectiveDate
							 AND tbl2.RateTableId = p_RateTableId
				WHERE  (    tbl1.RateTableRateID IS NULL
										OR
										(
											tbl2.RateTableRateID IS NULL
											AND  tbl1.EffectiveDate != rate.EffectiveDate

										)
							 )
							 AND tblRate.CodeDeckId = v_codedeckid_;

			-- delete same date's rates which are not in tmp_Rates_
			UPDATE
				tblRateTableRate rtr
			INNER JOIN
				tblRate ON rtr.RateId  = tblRate.RateId
			LEFT JOIN
				tmp_Rates_ rate ON rate.Code=tblRate.Code
			SET
				rtr.EndDate = NOW()
			WHERE
				rate.Code is null AND rtr.RateTableId = p_RateTableId AND rtr.EffectiveDate = rate.EffectiveDate AND tblRate.CodeDeckId = v_codedeckid_;

			-- delete and archive rates which rate's EndDate <= NOW()
			CALL prc_ArchiveOldRateTableRate(p_RateTableId,CONCAT(p_ModifiedBy,'|RateGenerator')); -- p_ModifiedBy

		END IF;

		-- update EndDate of all Rates of this RateTable By Vasim Seta Starts
		DROP TEMPORARY TABLE IF EXISTS tmp_ALL_RateTableRate_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_ALL_RateTableRate_ AS (SELECT * FROM tblRateTableRate WHERE RateTableID=p_RateTableId);

		UPDATE
			tmp_ALL_RateTableRate_ temp
		SET
			EndDate = (SELECT EffectiveDate FROM tblRateTableRate rtr WHERE rtr.RateTableID=p_RateTableId AND rtr.RateID=temp.RateID AND rtr.EffectiveDate>temp.EffectiveDate ORDER BY rtr.EffectiveDate ASC,rtr.RateTableRateID ASC LIMIT 1)
		WHERE
			temp.RateTableId = p_RateTableId;

		UPDATE
			tblRateTableRate rtr
		INNER JOIN
			tmp_ALL_RateTableRate_ temp ON rtr.RateTableRateID=temp.RateTableRateID
		SET
			rtr.EndDate=temp.EndDate
		WHERE
			rtr.RateTableId=p_RateTableId;
		-- update EndDate of all Rates of this RateTable By Vasim Seta Ends

		-- delete and archive rates which rate's EndDate <= NOW()
		CALL prc_ArchiveOldRateTableRate(p_RateTableId,CONCAT(p_ModifiedBy,'|RateGenerator')); -- p_ModifiedBy

		UPDATE tblRateTable
		SET RateGeneratorID = p_RateGeneratorId,
			TrunkID = v_trunk_,
			CodeDeckId = v_codedeckid_,
			updated_at = now()
		WHERE RateTableID = p_RateTableId;

		SELECT p_RateTableId as RateTableID;

		CALL prc_WSJobStatusUpdate(p_jobId, 'S', 'RateTable Created Successfully', '');

		COMMIT;


		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;



	END//
DELIMITER ;













DROP PROCEDURE IF EXISTS `prc_WSGenerateRateTableWithPrefix`;
DELIMITER //
CREATE PROCEDURE `prc_WSGenerateRateTableWithPrefix`(
	IN `p_jobId` INT,
	IN `p_RateGeneratorId` INT,
	IN `p_RateTableId` INT,
	IN `p_rateTableName` VARCHAR(200),
	IN `p_EffectiveDate` VARCHAR(10),
	IN `p_delete_exiting_rate` INT,
	IN `p_EffectiveRate` VARCHAR(50),
	IN `p_GroupBy` VARCHAR(50),
	IN `p_ModifiedBy` VARCHAR(50)
)
GenerateRateTable:BEGIN


		DECLARE i INTEGER;
		DECLARE v_RTRowCount_ INT;
		DECLARE v_RatePosition_ INT;
		DECLARE v_Use_Preference_ INT;
		DECLARE v_CurrencyID_ INT;
		DECLARE v_CompanyCurrencyID_ INT;
		DECLARE v_Average_ TINYINT;
		DECLARE v_CompanyId_ INT;
		DECLARE v_codedeckid_ INT;
		DECLARE v_trunk_ INT;
		DECLARE v_rateRuleId_ INT;
		DECLARE v_RateGeneratorName_ VARCHAR(200);
		DECLARE v_pointer_ INT ;
		DECLARE v_rowCount_ INT ;

		DECLARE v_IncreaseEffectiveDate_ DATETIME ;
		DECLARE v_DecreaseEffectiveDate_ DATETIME ;


		DECLARE v_tmp_code_cnt int ;
		DECLARE v_tmp_code_pointer int;
		DECLARE v_p_code varchar(50);
		DECLARE v_Codlen_ int;
		DECLARE v_p_code__ VARCHAR(50);
		DECLARE v_Commit int;
		DECLARE EXIT HANDLER FOR SQLEXCEPTION
		BEGIN
			show warnings;
			ROLLBACK;
			CALL prc_WSJobStatusUpdate(p_jobId, 'F', 'RateTable generation failed', '');

		END;

		SET @@session.collation_connection='utf8_unicode_ci';
		SET @@session.character_set_client='utf8';
		SET SESSION group_concat_max_len = 1000000; -- change group_concat limit for group by

		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;



		SET p_EffectiveDate = CAST(p_EffectiveDate AS DATE);


		IF p_rateTableName IS NOT NULL
		THEN


			SET v_RTRowCount_ = (SELECT
														 COUNT(*)
													 FROM tblRateTable
													 WHERE RateTableName = p_rateTableName
																 AND CompanyId = (SELECT
																										CompanyId
																									FROM tblRateGenerator
																									WHERE RateGeneratorID = p_RateGeneratorId));

			IF v_RTRowCount_ > 0
			THEN
				CALL prc_WSJobStatusUpdate  (p_jobId, 'F', 'RateTable Name is already exist, Please try using another RateTable Name', '');
				LEAVE GenerateRateTable;
			END IF;
		END IF;

		DROP TEMPORARY TABLE IF EXISTS tmp_Rates_;
		CREATE TEMPORARY TABLE tmp_Rates_  (
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			PreviousRate DECIMAL(18, 6),
			EffectiveDate DATE DEFAULT NULL,
			INDEX tmp_Rates_code (`code`),
			UNIQUE KEY `unique_code` (`code`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Rates2_;
		CREATE TEMPORARY TABLE tmp_Rates2_  (
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			PreviousRate DECIMAL(18, 6),
			EffectiveDate DATE DEFAULT NULL,
			INDEX tmp_Rates2_code (`code`)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_Codedecks_;
		CREATE TEMPORARY TABLE tmp_Codedecks_ (
			CodeDeckId INT
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Raterules_;

		CREATE TEMPORARY TABLE tmp_Raterules_  (
			rateruleid INT,
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			description VARCHAR(200) COLLATE utf8_unicode_ci,
			RowNo INT,
         `Order` INT,
			INDEX tmp_Raterules_code (`code`),
			INDEX tmp_Raterules_rateruleid (`rateruleid`),
			INDEX tmp_Raterules_RowNo (`RowNo`)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_Raterules_dup;
		CREATE TEMPORARY TABLE tmp_Raterules_dup  (
			rateruleid INT,
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			description VARCHAR(200) COLLATE utf8_unicode_ci,
			RowNo INT,
         `Order` INT,
			INDEX tmp_Raterules_code (`code`,`description`),
			INDEX tmp_Raterules_rateruleid (`rateruleid`),
			INDEX tmp_Raterules_RowNo (`RowNo`)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_Vendorrates_;
		CREATE TEMPORARY TABLE tmp_Vendorrates_  (
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			AccountId INT,
			RowNo INT,
			PreferenceRank INT,
			INDEX tmp_Vendorrates_code (`code`),
			INDEX tmp_Vendorrates_rate (`rate`)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_VRatesstage2_;
		CREATE TEMPORARY TABLE tmp_VRatesstage2_  (
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci,
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			FinalRankNumber int,
			INDEX tmp_Vendorrates_stage2__code (`RowCode`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_dupVRatesstage2_;
		CREATE TEMPORARY TABLE tmp_dupVRatesstage2_  (
			RowCode VARCHAR(50)  COLLATE utf8_unicode_ci,
			FinalRankNumber int,
			INDEX tmp_dupVendorrates_stage2__code (`RowCode`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Vendorrates_stage3_;
		CREATE TEMPORARY TABLE tmp_Vendorrates_stage3_  (
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			INDEX tmp_Vendorrates_stage2__code (`RowCode`)
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_code_;
		CREATE TEMPORARY TABLE tmp_code_  (
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			INDEX tmp_code_code (`code`)
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_all_code_;
		CREATE TEMPORARY TABLE tmp_all_code_ (
			RowCode  varchar(50) COLLATE utf8_unicode_ci,
			Code  varchar(50) COLLATE utf8_unicode_ci,
			RowNo int,
			INDEX Index2 (Code)
		);



		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage_;
		CREATE TEMPORARY TABLE tmp_VendorRate_stage_ (
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci,
			AccountId INT ,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) COLLATE utf8_unicode_ci,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			MaxMatchRank int ,
			prev_prev_RowCode VARCHAR(50),
			prev_AccountID int
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
		CREATE TEMPORARY TABLE tmp_VendorRate_ (
			AccountId INT ,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) COLLATE utf8_unicode_ci,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_final_VendorRate_;
		CREATE TEMPORARY TABLE tmp_final_VendorRate_ (
			AccountId INT ,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) COLLATE utf8_unicode_ci,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci,
			FinalRankNumber int,
			INDEX IX_CODE (RowCode)
		);

		-- when group by description this table use to insert comma seperated codes
		DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates_GroupBy_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates_GroupBy_(
			AccountId int,
			AccountName varchar(200),
			Code LONGTEXT,
			Description varchar(200) ,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate date,
			TrunkID int,
			CountryID int,
			RateID int,
			Preference int/*,
			INDEX IX_CODE (Code)*/
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates_(
			AccountId int,
			AccountName varchar(200),
			Code varchar(50) COLLATE utf8_unicode_ci,
			Description varchar(200) ,
			Rate DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate date,
			TrunkID int,
			CountryID int,
			RateID int,
			Preference int,
			INDEX IX_CODE (Code)
		);
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
		);

		SELECT CurrencyID INTO v_CurrencyID_ FROM  tblRateGenerator WHERE RateGeneratorId = p_RateGeneratorId;

		-- get Increase Decrease date from Job
		SELECT IFNULL(REPLACE(JSON_EXTRACT(Options, '$.IncreaseEffectiveDate'),'"',''), p_EffectiveDate) , IFNULL(REPLACE(JSON_EXTRACT(Options, '$.DecreaseEffectiveDate'),'"',''), p_EffectiveDate)   INTO v_IncreaseEffectiveDate_ , v_DecreaseEffectiveDate_  FROM tblJob WHERE Jobid = p_jobId;


		IF v_IncreaseEffectiveDate_ is null OR v_IncreaseEffectiveDate_ = '' THEN

			SET v_IncreaseEffectiveDate_ = p_EffectiveDate;

		END IF;

		IF v_DecreaseEffectiveDate_ is null OR v_DecreaseEffectiveDate_ = '' THEN

			SET v_DecreaseEffectiveDate_ = p_EffectiveDate;

		END IF;


		SELECT
			UsePreference,
			rateposition,
			companyid ,
			CodeDeckId,
			tblRateGenerator.TrunkID,
			tblRateGenerator.UseAverage  ,
			tblRateGenerator.RateGeneratorName INTO v_Use_Preference_, v_RatePosition_, v_CompanyId_, v_codedeckid_, v_trunk_, v_Average_, v_RateGeneratorName_
		FROM tblRateGenerator
		WHERE RateGeneratorId = p_RateGeneratorId;


		SELECT CurrencyId INTO v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = v_CompanyId_;



		-- order is important
		INSERT INTO tmp_Raterules_
			SELECT
				rateruleid,
				tblRateRule.Code,
				tblRateRule.Description,
				@row_num := @row_num+1 AS RowID,
            tblRateRule.`Order`
			FROM tblRateRule,(SELECT @row_num := 0) x
			WHERE rategeneratorid = p_RateGeneratorId
			ORDER BY tblRateRule.`Order` ASC;  -- <== order of rule is important

		-- v 4.17 fix process rules in order  -- NEON-1292 		Otto Rate Generator issue
		insert into tmp_Raterules_dup (			rateruleid ,		code ,		description ,		RowNo 		,   `Order`)
			select rateruleid ,		code ,		description ,		RowNo, `Order` from tmp_Raterules_;


		INSERT INTO tmp_Codedecks_
			SELECT DISTINCT
				tblVendorTrunk.CodeDeckId
			FROM tblRateRule
				INNER JOIN tblRateRuleSource
					ON tblRateRule.RateRuleId = tblRateRuleSource.RateRuleId
				INNER JOIN tblAccount
					ON tblAccount.AccountID = tblRateRuleSource.AccountId and tblAccount.IsVendor = 1
				JOIN tblVendorTrunk
					ON tblAccount.AccountId = tblVendorTrunk.AccountID
						 AND  tblVendorTrunk.TrunkID = v_trunk_
						 AND tblVendorTrunk.Status = 1
			WHERE RateGeneratorId = p_RateGeneratorId;

		SET v_pointer_ = 1;
		SET v_rowCount_ = (SELECT COUNT(distinct concat(Code,Description) ) FROM tmp_Raterules_);







		insert into tmp_code_
			SELECT
				tblRate.code
			FROM tblRate
				JOIN tmp_Codedecks_ cd
					ON tblRate.CodeDeckId = cd.CodeDeckId
				JOIN tmp_Raterules_ rr
					ON ( rr.code != '' AND tblRate.Code LIKE (REPLACE(rr.code,'*', '%%')) )
						 OR
						 ( rr.description != '' AND tblRate.Description LIKE (REPLACE(rr.description,'*', '%%')) )

			Order by tblRate.code ;









		SELECT CurrencyId INTO v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = v_CompanyId_;
		SET @IncludeAccountIds = (SELECT GROUP_CONCAT(AccountId) from tblRateRule rr inner join  tblRateRuleSource rrs on rr.RateRuleId = rrs.RateRuleId where rr.RateGeneratorId = p_RateGeneratorId ) ;



		INSERT INTO tmp_VendorCurrentRates1_
			Select DISTINCT AccountId,AccountName,Code,Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference
			FROM (
						 SELECT  tblVendorRate.AccountId,tblAccount.AccountName, tblRate.Code, tblRate.Description,
																																				CASE WHEN  tblAccount.CurrencyId = v_CurrencyID_
																																					THEN
																																						tblVendorRate.Rate
																																				WHEN  v_CompanyCurrencyID_ = v_CurrencyID_
																																					THEN
																																						(
																																							( tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ) )
																																						)
																																				ELSE
																																					(

																																						(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = v_CurrencyID_ and  CompanyID = v_CompanyId_ )
																																						* (tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ))
																																					)
																																				END
																																																																																																																																														as  Rate,
							 ConnectionFee,
																																				DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') AS EffectiveDate,
							 tblVendorRate.TrunkID, tblRate.CountryID, tblRate.RateID,IFNULL(vp.Preference, 5) AS Preference,
																																				@row_num := IF(@prev_AccountId = tblVendorRate.AccountID AND @prev_TrunkID = tblVendorRate.TrunkID AND @prev_RateId = tblVendorRate.RateID AND @prev_EffectiveDate >= tblVendorRate.EffectiveDate, @row_num + 1, 1) AS RowID,
							 @prev_AccountId := tblVendorRate.AccountID,
							 @prev_TrunkID := tblVendorRate.TrunkID,
							 @prev_RateId := tblVendorRate.RateID,
							 @prev_EffectiveDate := tblVendorRate.EffectiveDate
						 FROM      tblVendorRate

							 Inner join tblVendorTrunk vt on vt.CompanyID = v_CompanyId_ AND vt.AccountID = tblVendorRate.AccountID and

																							 vt.Status =  1 and vt.TrunkID =  v_trunk_
							 inner join tmp_Codedecks_ tcd on vt.CodeDeckId = tcd.CodeDeckId
							 INNER JOIN tblAccount   ON  tblAccount.CompanyID = v_CompanyId_ AND tblVendorRate.AccountId = tblAccount.AccountID and tblAccount.IsVendor = 1
							 INNER JOIN tblRate ON tblRate.CompanyID = v_CompanyId_  AND tblRate.CodeDeckId = vt.CodeDeckId  AND    tblVendorRate.RateId = tblRate.RateID
							 INNER JOIN tmp_code_ tcode ON tcode.Code  = tblRate.Code
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

							 ,(SELECT @row_num := 1,  @prev_AccountId := '',@prev_TrunkID := '', @prev_RateId := '', @prev_EffectiveDate := '') x

						 WHERE
							 (
								 (p_EffectiveRate = 'now' AND EffectiveDate <= NOW())
								 OR
								 (p_EffectiveRate = 'future' AND EffectiveDate > NOW()  )
								 OR
								 (	 p_EffectiveRate = 'effective' AND EffectiveDate <= p_EffectiveDate
										 AND ( tblVendorRate.EndDate IS NULL OR (tblVendorRate.EndDate > DATE(p_EffectiveDate)) )
								 )  -- rate should not end on selected effective date
							 )
							 AND ( tblVendorRate.EndDate IS NULL OR tblVendorRate.EndDate > now() )  -- rate should not end Today
							 AND tblAccount.IsVendor = 1
							 AND tblAccount.Status = 1
							 AND tblAccount.CurrencyId is not NULL
							 AND tblVendorRate.TrunkID = v_trunk_
							 AND blockCode.RateId IS NULL
							 AND blockCountry.CountryId IS NULL
							 AND ( @IncludeAccountIds = NULL
										 OR ( @IncludeAccountIds IS NOT NULL
													AND FIND_IN_SET(tblVendorRate.AccountId,@IncludeAccountIds) > 0
										 )
							 )
						 ORDER BY tblVendorRate.AccountId, tblVendorRate.TrunkID, tblVendorRate.RateId, tblVendorRate.EffectiveDate DESC
					 ) tbl
			order by Code asc;

		IF p_GroupBy = 'Desc' -- Group By Description
		THEN

			INSERT INTO tmp_VendorCurrentRates_GroupBy_ -- tmp_VendorCurrentRates_
				Select AccountId,max(AccountName),GROUP_CONCAT(Code),Description,max(Rate),max(ConnectionFee),max(EffectiveDate),TrunkID,max(CountryID),max(RateID),max(Preference)
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
				GROUP BY AccountId, TrunkID, Description
				order by Description asc;

				-- split and insert comma seperated codes
				SET i = 1;
				REPEAT
					INSERT INTO tmp_VendorCurrentRates_ (AccountId,AccountName,Code,Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference)
				  	SELECT
					  	AccountId,AccountName,FnStringSplit(Code, ',', i),Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference
					FROM
						tmp_VendorCurrentRates_GroupBy_
				  	WHERE
					  	FnStringSplit(Code, ',' , i) IS NOT NULL;

					SET i = i + 1;
				  	UNTIL ROW_COUNT() = 0
				END REPEAT;

		ELSE

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

		END IF;





			/* convert 9131 to all possible codes
            9131
            913
            91
         */





		insert into tmp_all_code_ (RowCode,Code,RowNo)
			select RowCode , loopCode,RowNo
			from (
						 select   RowCode , loopCode,
							 @RowNo := ( CASE WHEN (@prev_Code  = tbl1.RowCode  ) THEN @RowNo + 1
													 ELSE 1
													 END

							 )      as RowNo,
							 @prev_Code := tbl1.RowCode
						 from (
										select distinct Code as RowCode, Code as  loopCode from
											tmp_VendorCurrentRates_
									) tbl1
							 , ( Select @RowNo := 0 ) x
					 ) tbl order by RowCode desc,  LENGTH(loopCode) DESC ;















		insert into tmp_VendorRate_
			select
				AccountId ,
				AccountName ,
				Code ,
				Rate ,
				ConnectionFee,
				EffectiveDate ,
				Description ,
				Preference,
				Code as RowCode
			from tmp_VendorCurrentRates_;






		WHILE v_pointer_ <= v_rowCount_
		DO

			SET v_rateRuleId_ = (SELECT rateruleid FROM tmp_Raterules_ rr WHERE rr.RowNo = v_pointer_);


			INSERT INTO tmp_Rates2_ (code,rate,ConnectionFee)
				select  code,rate,ConnectionFee from tmp_Rates_;



			truncate tmp_final_VendorRate_;

			IF( v_Use_Preference_ = 0 )
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
								vr.AccountId ,
								vr.AccountName ,
								vr.Code ,
								vr.Rate ,
								vr.ConnectionFee,
								vr.EffectiveDate ,
								vr.Description ,
								vr.Preference,
								vr.RowCode,
								@rank := CASE WHEN ( @prev_RowCode = vr.RowCode  AND @prev_Rate <=  vr.Rate ) THEN @rank+1

												 ELSE
													 1
												 END
									AS FinalRankNumber,
								@prev_RowCode  := vr.RowCode,
								@prev_Rate  := vr.Rate
							from (
										 select distinct tmpvr.*
										 from tmp_VendorRate_  tmpvr
											 inner JOIN tmp_Raterules_ rr ON rr.RateRuleId = v_rateRuleId_ and
																											 (
																												 ( rr.code != '' AND tmpvr.RowCode LIKE (REPLACE(rr.code,'*', '%%')) )
																												 OR
																												 ( rr.description != '' AND tmpvr.Description LIKE (REPLACE(rr.description,'*', '%%')) )
																											 )
											 left JOIN tmp_Raterules_dup rr2 ON rr2.Order > rr.Order and
																													 (
																														 ( rr2.code != '' AND tmpvr.RowCode LIKE (REPLACE(rr2.code,'*', '%%')) )
																														 OR
																														 ( rr2.description != '' AND tmpvr.Description LIKE (REPLACE(rr2.description,'*', '%%')) )
																													 )
											 inner JOIN tblRateRuleSource rrs ON  rrs.RateRuleId = rr.rateruleid  and rrs.AccountId = tmpvr.AccountId
										 where rr2.code is null

									 ) vr
								,(SELECT @rank := 0 , @prev_RowCode := '' , @prev_Rate := 0  ) x
							order by vr.RowCode,vr.Rate,vr.AccountId ASC

						) tbl1
					where FinalRankNumber <= v_RatePosition_;

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
								vr.AccountId ,
								vr.AccountName ,
								vr.Code ,
								vr.Rate ,
								vr.ConnectionFee,
								vr.EffectiveDate ,
								vr.Description ,
								vr.Preference,
								vr.RowCode,
								@preference_rank := CASE WHEN (@prev_Code  = vr.RowCode  AND @prev_Preference > vr.Preference  )   THEN @preference_rank + 1
																		WHEN (@prev_Code  = vr.RowCode  AND @prev_Preference = vr.Preference AND @prev_Rate <= vr.Rate) THEN @preference_rank + 1

																		ELSE 1 END AS FinalRankNumber,
								@prev_Code := vr.RowCode,
								@prev_Preference := vr.Preference,
								@prev_Rate := vr.Rate
							from (
										 select distinct tmpvr.*
										 from tmp_VendorRate_  tmpvr
											 inner JOIN tmp_Raterules_ rr ON rr.RateRuleId = v_rateRuleId_ and
																											 (
																												 ( rr.code != '' AND tmpvr.RowCode LIKE (REPLACE(rr.code,'*', '%%')) )
																												 OR
																												 ( rr.description != '' AND tmpvr.Description LIKE (REPLACE(rr.description,'*', '%%')) )
																											 )
											 left JOIN tmp_Raterules_dup rr2 ON rr2.Order > rr.Order and
																													 (
																														 ( rr2.code != '' AND tmpvr.RowCode LIKE (REPLACE(rr2.code,'*', '%%')) )
																														 OR
																														 ( rr2.description != '' AND tmpvr.Description LIKE (REPLACE(rr2.description,'*', '%%')) )
																													 )
											 inner JOIN tblRateRuleSource rrs ON  rrs.RateRuleId = rr.rateruleid  and rrs.AccountId = tmpvr.AccountId
										 where rr2.code is null

									 ) vr

								,(SELECT @preference_rank := 0 , @prev_Code := ''  , @prev_Preference := 5,  @prev_Rate := 0) x
							order by vr.RowCode ASC ,vr.Preference DESC ,vr.Rate ASC ,vr.AccountId ASC

						) tbl1
					where FinalRankNumber <= v_RatePosition_;

			END IF;



			truncate   tmp_VRatesstage2_;

			INSERT INTO tmp_VRatesstage2_
				SELECT
					vr.RowCode,
					vr.code,
					vr.rate,
					vr.ConnectionFee,
					vr.FinalRankNumber
				FROM tmp_final_VendorRate_ vr
					left join tmp_Rates2_ rate on rate.Code = vr.RowCode
				WHERE  rate.code is null
				order by vr.FinalRankNumber desc ;



			IF v_Average_ = 0
			THEN
				insert into tmp_dupVRatesstage2_
					SELECT RowCode , MAX(FinalRankNumber) AS MaxFinalRankNumber
					FROM tmp_VRatesstage2_ GROUP BY RowCode;

				truncate tmp_Vendorrates_stage3_;
				INSERT INTO tmp_Vendorrates_stage3_
					select  vr.RowCode as RowCode , vr.rate as rate , vr.ConnectionFee as  ConnectionFee
					from tmp_VRatesstage2_ vr
						INNER JOIN tmp_dupVRatesstage2_ vr2
							ON (vr.RowCode = vr2.RowCode AND  vr.FinalRankNumber = vr2.FinalRankNumber);

				INSERT IGNORE INTO tmp_Rates_ (code,rate,ConnectionFee,PreviousRate)
                SELECT RowCode,
                    CASE WHEN rule_mgn.RateRuleId is not null
                        THEN
                            CASE WHEN trim(IFNULL(AddMargin,"")) != '' THEN
                                vRate.rate + (CASE WHEN addmargin LIKE '%p' THEN ((CAST(REPLACE(addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * vRate.rate) ELSE addmargin END)
                            WHEN trim(IFNULL(FixedValue,"")) != '' THEN
                                FixedValue
                            ELSE
                                vRate.rate
                            END
                    ELSE
                        vRate.rate
                    END as Rate,
                    ConnectionFee,
					null AS PreviousRate
                FROM tmp_Vendorrates_stage3_ vRate
                LEFT join tblRateRuleMargin rule_mgn on  rule_mgn.RateRuleId = v_rateRuleId_ and vRate.rate Between rule_mgn.MinRate and rule_mgn.MaxRate;



			ELSE

				INSERT IGNORE INTO tmp_Rates_ (code,rate,ConnectionFee,PreviousRate)
                SELECT RowCode,
                    CASE WHEN rule_mgn.RateRuleId is not null
                        THEN
                            CASE WHEN trim(IFNULL(AddMargin,"")) != '' THEN
                                vRate.rate + (CASE WHEN addmargin LIKE '%p' THEN ((CAST(REPLACE(addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * vRate.rate) ELSE addmargin END)
                            WHEN trim(IFNULL(FixedValue,"")) != '' THEN
                                FixedValue
                            ELSE
                                vRate.rate
                            END
                    ELSE
                        vRate.rate
                    END as Rate,
                    ConnectionFee,
					null AS PreviousRate
                FROM
                    (
                        select RowCode,
                        AVG(Rate) as Rate,
                        AVG(ConnectionFee) as ConnectionFee
                        from tmp_VRatesstage2_
                        group by RowCode
                    )  vRate
                LEFT join tblRateRuleMargin rule_mgn on  rule_mgn.RateRuleId = v_rateRuleId_ and vRate.rate Between rule_mgn.MinRate and rule_mgn.MaxRate;





			END IF;


			SET v_pointer_ = v_pointer_ + 1;


		END WHILE;


		START TRANSACTION;

		IF p_RateTableId = -1
		THEN

			INSERT INTO tblRateTable (CompanyId, RateTableName, RateGeneratorID, TrunkID, CodeDeckId,CurrencyID)
			VALUES (v_CompanyId_, p_rateTableName, p_RateGeneratorId, v_trunk_, v_codedeckid_,v_CurrencyID_);

			SET p_RateTableId = LAST_INSERT_ID();

			INSERT INTO tblRateTableRate (RateID,
																		RateTableId,
																		Rate,
																		EffectiveDate,
																		PreviousRate,
																		Interval1,
																		IntervalN,
																		ConnectionFee
			)
				SELECT DISTINCT
					RateId,
					p_RateTableId,
					Rate,
					p_EffectiveDate,
					Rate,
					Interval1,
					IntervalN,
					ConnectionFee
				FROM tmp_Rates_ rate
					INNER JOIN tblRate
						ON rate.code  = tblRate.Code
				WHERE tblRate.CodeDeckId = v_codedeckid_;

		ELSE

			IF p_delete_exiting_rate = 1
			THEN
				-- delete all rate table rates of this rate table
				UPDATE
					tblRateTableRate
				SET
					EndDate = NOW()
				WHERE
					tblRateTableRate.RateTableId = p_RateTableId;

				-- delete and archive rates which rate's EndDate <= NOW()
				CALL prc_ArchiveOldRateTableRate(p_RateTableId,CONCAT(p_ModifiedBy,'|RateGenerator')); -- p_ModifiedBy
			END IF;

			-- set EffectiveDate for which date rates need to generate
			UPDATE tmp_Rates_ SET EffectiveDate = p_EffectiveDate;

			-- update Previous Rates By Vasim Seta Start
			UPDATE
				tmp_Rates_ tr
			SET
				PreviousRate = (SELECT rtr.Rate FROM tblRateTableRate rtr JOIN tblRate r ON r.RateID=rtr.RateID WHERE rtr.RateTableID=p_RateTableId AND r.Code=tr.Code AND rtr.EffectiveDate<tr.EffectiveDate ORDER BY rtr.EffectiveDate DESC,rtr.RateTableRateID DESC LIMIT 1);

			UPDATE
				tmp_Rates_ tr
			SET
				PreviousRate = (SELECT rtr.Rate FROM tblRateTableRateArchive rtr JOIN tblRate r ON r.RateID=rtr.RateID WHERE rtr.RateTableID=p_RateTableId AND r.Code=tr.Code AND rtr.EffectiveDate<tr.EffectiveDate ORDER BY rtr.EffectiveDate DESC,rtr.RateTableRateID DESC LIMIT 1)
			WHERE
				PreviousRate is null;
			-- update Previous Rates By Vasim Seta End

			-- update increase decrease effective date starts
			IF v_IncreaseEffectiveDate_ != v_DecreaseEffectiveDate_ THEN

				UPDATE tmp_Rates_
				SET
					tmp_Rates_.EffectiveDate =
					CASE WHEN tmp_Rates_.PreviousRate < tmp_Rates_.Rate THEN
						v_IncreaseEffectiveDate_
					WHEN tmp_Rates_.PreviousRate > tmp_Rates_.Rate THEN
						v_DecreaseEffectiveDate_
					ELSE p_EffectiveDate
					END
				;

			END IF;
			-- update increase decrease effective date ends

			-- delete rates which needs to insert and with same EffectiveDate and rate is not same
			UPDATE
				tblRateTableRate
			INNER JOIN
				tblRate ON tblRate.RateId = tblRateTableRate.RateId
					AND tblRateTableRate.RateTableId = p_RateTableId
				--	AND tblRateTableRate.EffectiveDate = p_EffectiveDate
			INNER JOIN
				tmp_Rates_ as rate ON rate.code = tblRate.Code AND tblRateTableRate.EffectiveDate = rate.EffectiveDate
			SET
				tblRateTableRate.EndDate = NOW()
			WHERE
				tblRateTableRate.RateTableId = p_RateTableId AND
				tblRate.CodeDeckId = v_codedeckid_ AND
				rate.rate != tblRateTableRate.Rate;

			-- delete and archive rates which rate's EndDate <= NOW()
			CALL prc_ArchiveOldRateTableRate(p_RateTableId,CONCAT(p_ModifiedBy,'|RateGenerator')); -- p_ModifiedBy

			-- insert rates
			INSERT INTO tblRateTableRate (RateID,
																		RateTableId,
																		Rate,
																		EffectiveDate,
																		PreviousRate,
																		Interval1,
																		IntervalN,
																		ConnectionFee
			)
				SELECT DISTINCT
					tblRate.RateId,
					p_RateTableId RateTableId,
					rate.Rate,
					rate.EffectiveDate,
					rate.PreviousRate,
					tblRate.Interval1,
					tblRate.IntervalN,
					rate.ConnectionFee
				FROM tmp_Rates_ rate
					INNER JOIN tblRate
						ON rate.code  = tblRate.Code
					LEFT JOIN tblRateTableRate tbl1
						ON tblRate.RateId = tbl1.RateId
							 AND tbl1.RateTableId = p_RateTableId
					LEFT JOIN tblRateTableRate tbl2
						ON tblRate.RateId = tbl2.RateId
							 and tbl2.EffectiveDate = rate.EffectiveDate
							 AND tbl2.RateTableId = p_RateTableId
				WHERE  (    tbl1.RateTableRateID IS NULL
										OR
										(
											tbl2.RateTableRateID IS NULL
											AND  tbl1.EffectiveDate != rate.EffectiveDate

										)
							 )
							 AND tblRate.CodeDeckId = v_codedeckid_;

			-- delete same date's rates which are not in tmp_Rates_
			UPDATE
				tblRateTableRate rtr
			INNER JOIN
				tblRate ON rtr.RateId  = tblRate.RateId
			LEFT JOIN
				tmp_Rates_ rate ON rate.Code=tblRate.Code
			SET
				rtr.EndDate = NOW()
			WHERE
				rate.Code is null AND rtr.RateTableId = p_RateTableId AND rtr.EffectiveDate = rate.EffectiveDate AND tblRate.CodeDeckId = v_codedeckid_;

			-- delete and archive rates which rate's EndDate <= NOW()
			CALL prc_ArchiveOldRateTableRate(p_RateTableId,CONCAT(p_ModifiedBy,'|RateGenerator')); -- p_ModifiedBy

		END IF;

		-- update EndDate of all Rates of this RateTable By Vasim Seta Starts
		DROP TEMPORARY TABLE IF EXISTS tmp_ALL_RateTableRate_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_ALL_RateTableRate_ AS (SELECT * FROM tblRateTableRate WHERE RateTableID=p_RateTableId);

		UPDATE
			tmp_ALL_RateTableRate_ temp
		SET
			EndDate = (SELECT EffectiveDate FROM tblRateTableRate rtr WHERE rtr.RateTableID=p_RateTableId AND rtr.RateID=temp.RateID AND rtr.EffectiveDate>temp.EffectiveDate ORDER BY rtr.EffectiveDate ASC,rtr.RateTableRateID ASC LIMIT 1)
		WHERE
			temp.RateTableId = p_RateTableId;

		UPDATE
			tblRateTableRate rtr
		INNER JOIN
			tmp_ALL_RateTableRate_ temp ON rtr.RateTableRateID=temp.RateTableRateID
		SET
			rtr.EndDate=temp.EndDate
		WHERE
			rtr.RateTableId=p_RateTableId;
		-- update EndDate of all Rates of this RateTable By Vasim Seta Ends

		-- delete and archive rates which rate's EndDate <= NOW()
		CALL prc_ArchiveOldRateTableRate(p_RateTableId,CONCAT(p_ModifiedBy,'|RateGenerator')); -- p_ModifiedBy

		UPDATE tblRateTable
		SET RateGeneratorID = p_RateGeneratorId,
			TrunkID = v_trunk_,
			CodeDeckId = v_codedeckid_,
			updated_at = now()
		WHERE RateTableID = p_RateTableId;

		SELECT p_RateTableId as RateTableID;

		CALL prc_WSJobStatusUpdate(p_jobId, 'S', 'RateTable Created Successfully', '');

		COMMIT;


		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;



	END//
DELIMITER ;