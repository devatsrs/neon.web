-- 4.16 fixes

DROP PROCEDURE IF EXISTS `prc_WSCronJobDeleteOldVendorRate`;
DELIMITER //
CREATE PROCEDURE `prc_WSCronJobDeleteOldVendorRate`()
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
				vr.`created_by`,
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



DROP PROCEDURE IF EXISTS `vwVendorSippySheet`;
DELIMITER //
CREATE PROCEDURE `vwVendorSippySheet`(
	IN `p_AccountID` INT,
	IN `p_Trunks` LONGTEXT,
	IN `p_Effective` VARCHAR(50)


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

		call vwVendorCurrentRates(p_AccountID,p_Trunks,p_Effective);

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


DROP PROCEDURE IF EXISTS `prc_CronJobGeneratePortaVendorSheet`;
DELIMITER //
CREATE PROCEDURE `prc_CronJobGeneratePortaVendorSheet`(
	IN `p_AccountID` INT ,
	IN `p_trunks` VARCHAR(200),
	IN `p_Effective` VARCHAR(50)


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
								  	(p_Effective = 'All')
								);

		 DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate4_;
       CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRate4_ as (select * from tmp_VendorRate_);
      DELETE n1 FROM tmp_VendorRate_ n1, tmp_VendorRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
 	   AND n1.TrunkID = n2.TrunkID
	   AND  n1.RateId = n2.RateId
		AND n1.EffectiveDate <= NOW()
		AND n2.EffectiveDate <= NOW();



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



DROP PROCEDURE IF EXISTS `prc_ArchiveOldVendorRate`;
DELIMITER //
CREATE PROCEDURE `prc_ArchiveOldVendorRate`(
	IN `p_AccountIds` longtext
,
	IN `p_TrunkIds` longtext







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
				`created_by`,
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



DROP PROCEDURE IF EXISTS `prc_WSProcessVendorRate`;
DELIMITER //
CREATE PROCEDURE `prc_WSProcessVendorRate`(
	IN `p_accountId` INT,
	IN `p_trunkId` INT,
	IN `p_replaceAllRates` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_processId` VARCHAR(200),
	IN `p_addNewCodesToCodeDeck` INT,
	IN `p_companyId` INT,
	IN `p_forbidden` INT,
	IN `p_preference` INT,
	IN `p_dialstringid` INT,
	IN `p_dialcodeSeparator` VARCHAR(50),
	IN `p_CurrencyID` INT,
	IN `p_list_option` INT









































)
		ThisSP:BEGIN

		DECLARE v_AffectedRecords_ INT DEFAULT 0;
		DECLARE v_CodeDeckId_ INT ;
		DECLARE totaldialstringcode INT(11) DEFAULT 0;
		DECLARE newstringcode INT(11) DEFAULT 0;
		DECLARE totalduplicatecode INT(11);
		DECLARE errormessage longtext;
		DECLARE errorheader longtext;
		DECLARE v_AccountCurrencyID_ INT;
		DECLARE v_CompanyCurrencyID_ INT;


		DECLARE v_pointer_ INT;
		DECLARE v_rowCount_ INT;


		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
		CREATE TEMPORARY TABLE tmp_JobLog_ (
			Message longtext
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_split_VendorRate_;
		CREATE TEMPORARY TABLE tmp_split_VendorRate_ (
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
			`DialStringPrefix` varchar(500) ,
			INDEX tmp_EffectiveDate (`EffectiveDate`),
			INDEX tmp_Code (`Code`),
			INDEX tmp_CC (`Code`,`Change`),
			INDEX tmp_Change (`Change`)
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_TempVendorRate_;
		CREATE TEMPORARY TABLE tmp_TempVendorRate_ (
			TempVendorRateID int,
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
			`DialStringPrefix` varchar(500) ,
			INDEX tmp_EffectiveDate (`EffectiveDate`),
			INDEX tmp_Code (`Code`),
			INDEX tmp_CC (`Code`,`Change`),
			INDEX tmp_Change (`Change`)
		);

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
			EndDate Datetime ,
			Interval1 INT,
			IntervalN INT,
			ConnectionFee DECIMAL(18, 6),
			deleted_at DATETIME,
			INDEX tmp_VendorRateDiscontinued_VendorRateID (`VendorRateID`)
		);


		/*  1.  Check duplicate code, dial string   */
		CALL  prc_checkDialstringAndDupliacteCode(p_companyId,p_processId,p_dialstringid,p_effectiveImmediately,p_dialcodeSeparator);

		SELECT COUNT(*) AS COUNT INTO newstringcode from tmp_JobLog_;

		-- LEAVE ThisSP;


		-- if no error
		IF newstringcode = 0
		THEN


			/*  2.  Send Today EndDate to rates which are marked deleted in review screen  */
			/*  3.  Update interval in temp table */

			-- if review
			IF (SELECT count(*) FROM tblVendorRateChangeLog WHERE ProcessID = p_processId ) > 0 THEN

				-- v4.16 update end date given from tblVendorRateChangeLog for deleted rates.
				UPDATE
						tblVendorRate vr
						INNER JOIN tblVendorRateChangeLog  vrcl
							on vrcl.VendorRateID = vr.VendorRateID
				SET
					vr.EndDate = IFNULL(vrcl.EndDate,date(now()))
				WHERE vrcl.ProcessID = p_processId
							AND vrcl.`Action`  ='Deleted';

				-- update end date on temp table
				UPDATE tmp_TempVendorRate_ tblTempVendorRate
					JOIN tblVendorRateChangeLog vrcl
						ON  vrcl.ProcessId = p_processId
								AND vrcl.Code = tblTempVendorRate.Code
				-- AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
				SET
					tblTempVendorRate.EndDate = vrcl.EndDate
				WHERE
					vrcl.`Action` = 'Deleted'
					AND vrcl.EndDate IS NOT NULL ;


				-- update intervals.
				UPDATE tmp_TempVendorRate_ tblTempVendorRate
					JOIN tblVendorRateChangeLog vrcl
						ON  vrcl.ProcessId = p_processId
								AND vrcl.Code = tblTempVendorRate.Code
				-- AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
				SET
					tblTempVendorRate.Interval1 = vrcl.Interval1 ,
					tblTempVendorRate.IntervalN = vrcl.IntervalN
				WHERE
					vrcl.`Action` = 'New'
					AND vrcl.Interval1 IS NOT NULL
					AND vrcl.IntervalN IS NOT NULL ;



			/*IF (FOUND_ROWS() > 0) THEN
				INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Updated End Date of Deleted Records. ' );
			END IF;
			*/


			END IF;

			/*  4.  Update EndDate to Today if Replace All existing */

			IF  p_replaceAllRates = 1
			THEN

				/*
      DELETE FROM tblVendorRate
      WHERE AccountId = p_accountId
      AND TrunkID = p_trunkId;
        */

				UPDATE tblVendorRate
				SET tblVendorRate.EndDate = date(now())
				WHERE AccountId = p_accountId
							AND TrunkID = p_trunkId;




			/*
			IF (FOUND_ROWS() > 0) THEN
				INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' Records Removed.   ' );
			END IF;
			*/

			END IF;

			/* 5. If Complete File, remove rates not exists in file  */

			IF p_list_option = 1    -- v4.16 p_list_option = 1 : "Completed List", p_list_option = 2 : "Partial List"
			THEN


				-- v4.16 get rates which is not in file and insert it into temp table
				INSERT INTO tmp_Delete_VendorRate(
					VendorRateID ,
					AccountId,
					TrunkID ,
					RateId,
					Code ,
					Description ,
					Rate ,
					EffectiveDate ,
					EndDate ,
					Interval1 ,
					IntervalN ,
					ConnectionFee ,
					deleted_at
				)
					SELECT DISTINCT
						tblVendorRate.VendorRateID,
						p_accountId AS AccountId,
						p_trunkId AS TrunkID,
						tblVendorRate.RateId,
						tblRate.Code,
						tblRate.Description,
						tblVendorRate.Rate,
						tblVendorRate.EffectiveDate,
						IFNULL(tblVendorRate.EndDate,date(now())) ,
						tblVendorRate.Interval1,
						tblVendorRate.IntervalN,
						tblVendorRate.ConnectionFee,
						now() AS deleted_at
					FROM tblVendorRate
						JOIN tblRate
							ON tblRate.RateID = tblVendorRate.RateId
								 AND tblRate.CompanyID = p_companyId
						LEFT JOIN tmp_TempVendorRate_ as tblTempVendorRate
							ON tblTempVendorRate.Code = tblRate.Code
								 AND  tblTempVendorRate.ProcessId = p_processId
								 AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
					WHERE tblVendorRate.AccountId = p_accountId
								AND tblVendorRate.TrunkId = p_trunkId
								AND tblTempVendorRate.Code IS NULL
								AND ( tblVendorRate.EndDate is NULL OR tblVendorRate.EndDate <= date(now()) )

					ORDER BY VendorRateID ASC;


				/*IF (FOUND_ROWS() > 0) THEN
          INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Records marked deleted as Not exists in File' );
        END IF;*/

				-- set end date will remove at bottom in archive proc
				UPDATE tblVendorRate
					JOIN tmp_Delete_VendorRate ON tblVendorRate.VendorRateID = tmp_Delete_VendorRate.VendorRateID
				SET tblVendorRate.EndDate = date(now())
				WHERE
					tblVendorRate.AccountId = p_accountId
					AND tblVendorRate.TrunkId = p_trunkId;

			-- 	CALL prc_InsertDiscontinuedVendorRate(p_accountId,p_trunkId);


			END IF;

			/* 6. Move Rates to archive which has EndDate <= now()  */
			-- move to archive if EndDate is <= now()
			IF ( (SELECT count(*) FROM tblVendorRate WHERE  AccountId = p_accountId  AND TrunkId = p_trunkId AND EndDate <= NOW() )  > 0  ) THEN

				-- move to archive
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
						`created_at`,
						`created_by`,
						`updated_by`,
						`Interval1`,
						`IntervalN`,
						`ConnectionFee`,
						`MinimumCost`,
						concat('Ends Today rates @ ' , now() ) as `Notes`
					FROM tblVendorRate
					WHERE  AccountId = p_accountId  AND TrunkId = p_trunkId AND EndDate <= NOW();

				delete from tblVendorRate
				WHERE  AccountId = p_accountId  AND TrunkId = p_trunkId AND EndDate <= NOW();


			END IF;

			/* 7. Add New code in codedeck  */

			IF  p_addNewCodesToCodeDeck = 1
			THEN
				INSERT INTO tblRate (
					CompanyID,
					Code,
					Description,
					CreatedBy,
					CountryID,
					CodeDeckId,
					Interval1,
					IntervalN
				)
					SELECT DISTINCT
						p_companyId,
						vc.Code,
						vc.Description,
						'RMService',
						fnGetCountryIdByCodeAndCountry (vc.Code ,vc.Description) AS CountryID,
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
										AND tblTempVendorRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
						) vc;


			/*IF (FOUND_ROWS() > 0) THEN
          INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - New Code Inserted into Codedeck ' );
      END IF;*/


			/*
           SELECT GROUP_CONCAT(Code) into errormessage FROM(
              SELECT DISTINCT
                  tblTempVendorRate.Code as Code, 1 as a
              FROM tmp_TempVendorRate_  as tblTempVendorRate
              INNER JOIN tblRate
              ON tblRate.Code = tblTempVendorRate.Code
              AND tblRate.CompanyID = p_companyId
              AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
              WHERE tblRate.CountryID IS NULL
              AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
          ) as tbl GROUP BY a;

          IF errormessage IS NOT NULL
          THEN
              INSERT INTO tmp_JobLog_ (Message)
                  SELECT DISTINCT
                    CONCAT(tblTempVendorRate.Code , ' INVALID CODE - COUNTRY NOT FOUND')
                  FROM tmp_TempVendorRate_  as tblTempVendorRate
                  INNER JOIN tblRate
                  ON tblRate.Code = tblTempVendorRate.Code
                    AND tblRate.CompanyID = p_companyId
                    AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
                  WHERE tblRate.CountryID IS NULL
                    AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');
           END IF; */
			ELSE
				SELECT GROUP_CONCAT(code) into errormessage FROM(
																													SELECT DISTINCT
																														c.Code as code, 1 as a
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
																																		AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
																														) c
																												) as tbl GROUP BY a;

				IF errormessage IS NOT NULL
				THEN
					INSERT INTO tmp_JobLog_ (Message)
						SELECT DISTINCT
							CONCAT(tbl.Code , ' CODE DOES NOT EXIST IN CODE DECK')
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
											AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
							) as tbl;
				END IF;
			END IF;

			/* 8. delete rates which will be map as deleted */

			-- delete rates which will be map as deleted
			UPDATE tblVendorRate
				INNER JOIN tblRate
					ON tblRate.RateID = tblVendorRate.RateId
						 AND tblRate.CompanyID = p_companyId
				INNER JOIN tmp_TempVendorRate_ as tblTempVendorRate
					ON tblRate.Code = tblTempVendorRate.Code
						 AND tblTempVendorRate.Change IN ('Delete', 'R', 'D', 'Blocked', 'Block')
			SET tblVendorRate.EndDate = IFNULL(tblTempVendorRate.EndDate,date(now()))
			WHERE tblVendorRate.AccountId = p_accountId
						AND tblVendorRate.TrunkId = p_trunkId ;


			/*IF (FOUND_ROWS() > 0) THEN
          INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Records marked deleted as mapped in File ' );
      END IF;*/


			-- need to get vendor rates with latest records ....
			-- and then need to use that table to insert update records in vendor rate.


			-- ------

			-- CALL prc_InsertDiscontinuedVendorRate(p_accountId,p_trunkId);

			/* 9. Update Interval in tblRate */

			-- Update Interval Changed for Action = "New"
			-- update Intervals which are not maching with tblTempVendorRate
			-- so as if intervals will not mapped next time it will be same as last file.
			UPDATE tblRate
				JOIN tmp_TempVendorRate_ as tblTempVendorRate
					ON 	  tblRate.CompanyID = p_companyId
								AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
								AND tblTempVendorRate.Code = tblRate.Code
								AND  tblTempVendorRate.ProcessId = p_processId
								AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
			SET
				tblRate.Interval1 = tblTempVendorRate.Interval1,
				tblRate.IntervalN = tblTempVendorRate.IntervalN
			WHERE
				tblTempVendorRate.Interval1 IS NOT NULL
				AND tblTempVendorRate.IntervalN IS NOT NULL
				AND
				(
					tblRate.Interval1 != tblTempVendorRate.Interval1
					OR
					tblRate.IntervalN != tblTempVendorRate.IntervalN
				);




			/* 10. Update INTERVAL, ConnectionFee,  */

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
			--  tblVendorRate.EndDate = tblTempVendorRate.EndDate
			WHERE tblVendorRate.AccountId = p_accountId
						AND tblVendorRate.TrunkId = p_trunkId ;


			/*IF (FOUND_ROWS() > 0) THEN
          INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Updated Existing Records' );
      END IF;*/

			/* 11. Update VendorBlocking  */

			IF  p_forbidden = 1 OR p_dialstringid > 0
			THEN
				INSERT INTO tblVendorBlocking
				(
					`AccountId`,
					`RateId`,
					`TrunkID`,
					`BlockedBy`
				)
					SELECT distinct
						p_accountId as AccountId,
						tblRate.RateID as RateId,
						p_trunkId as TrunkID,
						'RMService' as BlockedBy
					FROM tmp_TempVendorRate_ as tblTempVendorRate
						INNER JOIN tblRate
							ON tblRate.Code = tblTempVendorRate.Code
								 AND tblRate.CompanyID = p_companyId
								 AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
						LEFT JOIN tblVendorBlocking vb
							ON vb.AccountId=p_accountId
								 AND vb.RateId = tblRate.RateID
								 AND vb.TrunkID = p_trunkId
					WHERE tblTempVendorRate.Forbidden IN('B')
								AND vb.VendorBlockingId is null;

				DELETE tblVendorBlocking
				FROM tblVendorBlocking
					INNER JOIN(
											select VendorBlockingId
											FROM `tblVendorBlocking` tv
												INNER JOIN(
																		SELECT
																			tblRate.RateId as RateId
																		FROM tmp_TempVendorRate_ as tblTempVendorRate
																			INNER JOIN tblRate
																				ON tblRate.Code = tblTempVendorRate.Code
																					 AND tblRate.CompanyID = p_companyId
																					 AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
																		WHERE tblTempVendorRate.Forbidden IN('UB')
																	)tv1 on  tv.AccountId=p_accountId
																					 AND tv.TrunkID=p_trunkId
																					 AND tv.RateId = tv1.RateID
										)vb2 on vb2.VendorBlockingId = tblVendorBlocking.VendorBlockingId;
			END IF;

			/* 11. Update VendorPreference  */

			IF  p_preference = 1
			THEN
				INSERT INTO tblVendorPreference
				(
					`AccountId`
					,`Preference`
					,`RateId`
					,`TrunkID`
					,`CreatedBy`
					,`created_at`
				)
					SELECT
						p_accountId AS AccountId,
						tblTempVendorRate.Preference as Preference,
						tblRate.RateID AS RateId,
						p_trunkId AS TrunkID,
						'RMService' AS CreatedBy,
						NOW() AS created_at
					FROM tmp_TempVendorRate_ as tblTempVendorRate
						INNER JOIN tblRate
							ON tblRate.Code = tblTempVendorRate.Code
								 AND tblRate.CompanyID = p_companyId
								 AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
						LEFT JOIN tblVendorPreference vp
							ON vp.RateId=tblRate.RateID
								 AND vp.AccountId = p_accountId
								 AND vp.TrunkID = p_trunkId
					WHERE  tblTempVendorRate.Preference IS NOT NULL
								 AND  tblTempVendorRate.Preference > 0
								 AND  vp.VendorPreferenceID IS NULL;

				UPDATE tblVendorPreference
					INNER JOIN tblRate
						ON tblVendorPreference.RateId=tblRate.RateID
					INNER JOIN tmp_TempVendorRate_ as tblTempVendorRate
						ON tblTempVendorRate.Code = tblRate.Code
							 AND tblTempVendorRate.CodeDeckId = tblRate.CodeDeckId
							 AND tblRate.CompanyID = p_companyId
				SET tblVendorPreference.Preference = tblTempVendorRate.Preference
				WHERE tblVendorPreference.AccountId = p_accountId
							AND tblVendorPreference.TrunkID = p_trunkId
							AND  tblTempVendorRate.Preference IS NOT NULL
							AND  tblTempVendorRate.Preference > 0
							AND tblVendorPreference.VendorPreferenceID IS NOT NULL;

				DELETE tblVendorPreference
				from	tblVendorPreference
					INNER JOIN tblRate
						ON tblVendorPreference.RateId=tblRate.RateID
					INNER JOIN tmp_TempVendorRate_ as tblTempVendorRate
						ON tblTempVendorRate.Code = tblRate.Code
							 AND tblTempVendorRate.CodeDeckId = tblRate.CodeDeckId
							 AND tblRate.CompanyID = p_companyId
				WHERE tblVendorPreference.AccountId = p_accountId
							AND tblVendorPreference.TrunkID = p_trunkId
							AND  tblTempVendorRate.Preference IS NOT NULL
							AND  tblTempVendorRate.Preference = ''
							AND tblVendorPreference.VendorPreferenceID IS NOT NULL;

			END IF;


			/* 12. Delete rates which are same in file   */

			-- delete rates which are not increase/decreased  (rates = rates)
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

			/*IF (FOUND_ROWS() > 0) THEN
        INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Discarded no change records' );
      END IF;*/



			SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

			SELECT CurrencyID into v_AccountCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblAccount WHERE AccountID=p_accountId);
			SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);

			/* 13. update currency   */

			UPDATE tmp_TempVendorRate_ as tblTempVendorRate
				JOIN tblRate
					ON tblRate.Code = tblTempVendorRate.Code
						 AND tblRate.CompanyID = p_companyId
						 AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
				JOIN tblVendorRate
					ON tblVendorRate.RateId = tblRate.RateId
						 AND tblVendorRate.AccountId = p_accountId
						 AND tblVendorRate.TrunkId = p_trunkId
			SET tblVendorRate.Rate = IF (
					p_CurrencyID > 0,
					CASE WHEN p_CurrencyID = v_AccountCurrencyID_
						THEN
							tblTempVendorRate.Rate
					WHEN  p_CurrencyID = v_CompanyCurrencyID_
						THEN
							(
								( tblTempVendorRate.Rate  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ and CompanyID = p_companyId ) )
							)
					ELSE
						(
							(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ AND CompanyID = p_companyId )
							*
							(tblTempVendorRate.Rate  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))
						)
					END ,
					tblTempVendorRate.Rate
			)
			WHERE tblTempVendorRate.Rate <> tblVendorRate.Rate
						AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
						AND DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempVendorRate.EffectiveDate, '%Y-%m-%d');

			SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

			/* 13. insert new rates   */

			INSERT INTO tblVendorRate (
				AccountId,
				TrunkID,
				RateId,
				Rate,
				EffectiveDate,
				EndDate,
				ConnectionFee,
				Interval1,
				IntervalN
			)
				SELECT DISTINCT
					p_accountId,
					p_trunkId,
					tblRate.RateID,
					IF (
							p_CurrencyID > 0,
							CASE WHEN p_CurrencyID = v_AccountCurrencyID_
								THEN
									tblTempVendorRate.Rate
							WHEN  p_CurrencyID = v_CompanyCurrencyID_
								THEN
									(
										( tblTempVendorRate.Rate  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ and CompanyID = p_companyId ) )
									)
							ELSE
								(
									(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ AND CompanyID = p_companyId )
									*
									(tblTempVendorRate.Rate  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))
								)
							END ,
							tblTempVendorRate.Rate
					) ,
					tblTempVendorRate.EffectiveDate,
					tblTempVendorRate.EndDate,
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
							 AND tblTempVendorRate.EffectiveDate = tblVendorRate.EffectiveDate
				WHERE tblVendorRate.VendorRateID IS NULL
							AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
							AND tblTempVendorRate.EffectiveDate >= DATE_FORMAT (NOW(), '%Y-%m-%d');

			SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

			/*IF (FOUND_ROWS() > 0) THEN
        INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - New Records Inserted.' );
      END IF;
      */

			/* 13. update enddate in old rates */


			-- loop through effective date to update end date
			DROP TEMPORARY TABLE IF EXISTS tmp_EffectiveDates_;
			CREATE TEMPORARY TABLE tmp_EffectiveDates_ (
				RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
				EffectiveDate  Date
			);
			INSERT INTO tmp_EffectiveDates_ (EffectiveDate)
				SELECT distinct
					EffectiveDate
				FROM
					(	select distinct EffectiveDate
						 from 	tblVendorRate
						 WHERE
							 AccountId = p_accountId
							 AND TrunkId = p_trunkId
						 Group By EffectiveDate
						 order by EffectiveDate desc
					) tmp


					,(SELECT @row_num := 0) x;


			SET v_pointer_ = 1;
			SET v_rowCount_ = ( SELECT COUNT(*) FROM tmp_EffectiveDates_ );

			IF v_rowCount_ > 0 THEN

				WHILE v_pointer_ <= v_rowCount_
				DO

					SET @EffectiveDate = ( SELECT EffectiveDate FROM tmp_EffectiveDates_ WHERE RowID = v_pointer_ );
					SET @row_num = 0;

					UPDATE  tblVendorRate vr1
						inner join
						(
							select
								AccountID,
								RateID,
								TrunkID,
								EffectiveDate
							FROM tblVendorRate
							WHERE AccountId = p_accountId
										AND TrunkId = p_trunkId
										AND EffectiveDate =   @EffectiveDate
							order by EffectiveDate desc

						) tmpvr
							on
								vr1.AccountID = tmpvr.AccountID
								AND vr1.TrunkID  	=       	tmpvr.TrunkID
								AND vr1.RateID  	=        	tmpvr.RateID
								AND vr1.EffectiveDate 	< tmpvr.EffectiveDate
					SET
						vr1.EndDate = @EffectiveDate
					where
						vr1.AccountId = p_accountId
						AND vr1.TrunkID = p_trunkId
						--	AND vr1.EffectiveDate < @EffectiveDate
						AND vr1.EndDate is null;





					SET v_pointer_ = v_pointer_ + 1;


				END WHILE;

			END IF;


		END IF;

		INSERT INTO tmp_JobLog_ (Message) 	 	SELECT CONCAT(v_AffectedRecords_ , ' Records Uploaded ' );

		-- archive rates which has EndDate <= today
		call prc_ArchiveOldVendorRate(p_accountId,p_trunkId);


		SELECT * FROM tmp_JobLog_;
		DELETE  FROM tblTempVendorRate WHERE  ProcessId = p_processId;
		DELETE  FROM tblVendorRateChangeLog WHERE ProcessID = p_processId;

		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	END//
DELIMITER ;





DROP PROCEDURE IF EXISTS `prc_WSGenerateVendorSippySheet`;
DELIMITER //
CREATE  PROCEDURE `prc_WSGenerateVendorSippySheet`(
	IN `p_VendorID` INT  ,
	IN `p_Trunks` varchar(200),
	IN `p_Effective` VARCHAR(50)

)
	BEGIN

		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		call vwVendorSippySheet(p_VendorID,p_Trunks,p_Effective);

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

DROP PROCEDURE IF EXISTS `prc_TicketCheckRepeatedEmails`;
DELIMITER //
CREATE PROCEDURE `prc_TicketCheckRepeatedEmails`(
	IN `p_CompanyID` INT,
	IN `p_Email` VARCHAR(100)


	,
	IN `p_GroupID` INT
)
	BEGIN
		DECLARE v_limit_records INT ;

		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


		SET @minutes = null;
		SET @minutes_limit = 5;

		SET v_limit_records  = 5;

		SET @isAlreadyBlocked = 0;
		SET @block = 0;

		select count(ir.TicketImportRuleID) into @isAlreadyBlocked
		from tblTicketImportRule  ir
			inner join tblTicketImportRuleCondition irc on irc.TicketImportRuleID = ir.TicketImportRuleID
			inner join tblTicketImportRuleConditionType irct on irc.TicketImportRuleConditionTypeID = irct.TicketImportRuleConditionTypeID
		where
			ir.CompanyID = p_CompanyID AND
			irct.`Condition` = 'from_email'  AND
			irc.operand = 'is' AND
			irc.Value = p_Email AND
			ir.`Status` = 1;


		IF @isAlreadyBlocked > 0 THEN

			SET @isAlreadyBlocked = 1;

		END IF;

		/*select count(AccountEmailLogID) into @hasMoreThan5Emails from AccountEmailLog
		where
			CompanyID  = p_CompanyID AND
			Emailfrom = p_Email
		order by AccountEmailLogID desc
		limit v_limit_records;*/


		select GroupReplyAddress into @GroupReplyAddress from tblTicketGroups where GroupID = p_GroupID;


		-- get open field status id
		select fv.ValuesID into @OpenStatusID from tblTicketfieldsValues fv
			inner join tblTicketfields f on fv.FieldsID = f.TicketFieldsID
		where
			f. FieldType = 'default_status' AND fv.FieldType = 0 AND fv.FieldValueAgent = 'Open';



		select count(ael.AccountEmailLogID) into @hasMoreThan5Emails from AccountEmailLog ael
			left join tblTickets t on t.TicketID=ael.TicketID
		where
			ael.CompanyID  = p_CompanyID AND
			ael.Emailfrom = p_Email AND
			(
				ael.EmailTo = @GroupReplyAddress
				OR
				ael.Cc like concat('%',  @GroupReplyAddress , '%' )
			)
			AND
			t.`Status`= @OpenStatusID;

		IF @isAlreadyBlocked = 0 AND @hasMoreThan5Emails >= v_limit_records THEN

			-- check time difference with last 5 open tickets
			SELECT
				-- min(created_at) , max(created_at) ,
				TIMESTAMPDIFF( MINUTE, min(created_at), max(created_at) )  into @minutes
			FROM
				(
					select ael.created_at from AccountEmailLog ael
						left join tblTickets t on t.TicketID=ael.TicketID
					where
						ael.CompanyID  = p_CompanyID AND
						ael.Emailfrom = p_Email AND
						(
							ael.EmailTo = @GroupReplyAddress
							OR
							ael.Cc like concat('%',  @GroupReplyAddress , '%' )
						)
						AND
						t.`Status`= @OpenStatusID

					order by ael.AccountEmailLogID desc
					limit v_limit_records
				) tmp;



		END IF;

		-- show minutes
		IF @minutes <=  @minutes_limit THEN

			SET @block = 1; -- block

		ELSE

			SET @block = 0; -- dont block

		END IF ;

		SELECT @block as block , @isAlreadyBlocked as isAlreadyBlocked;


		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;


	END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_GetSingleTicket`;
DELIMITER //
CREATE PROCEDURE `prc_GetSingleTicket`(
	IN `p_TicketID` INT
)
	BEGIN

		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		select t.* , eal.EmailTo from tblTickets t
			left join AccountEmailLog eal on t.TicketID = eal.TicketID -- and eal.EmailParent = 0
		where t.TicketID =  p_TicketID order by eal.AccountEmailLogID asc limit 1;

		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ ;

	END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_WSGenerateRateSheet`;
DELIMITER //
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
			`level`         VARCHAR(50),
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
			SELECT  tblCustomerRate.RateID,
				tblCustomerRate.Interval1,
				tblCustomerRate.IntervalN,
				tblCustomerRate.Rate,
				effectivedate,
				lastmodifieddate
			FROM   tblAccount
				JOIN tblCustomerRate
					ON tblAccount.AccountID = tblCustomerRate.CustomerID
				JOIN tblRate
					ON tblRate.RateId = tblCustomerRate.RateId
						 AND tblRate.CodeDeckId = v_codedeckid_
			WHERE  tblAccount.AccountID = p_CustomerID
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

			ORDER BY rsr.Destination,rsr.Codes DESC;
		END IF;

		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	END//
DELIMITER ;
