use Ratemanagement3;

-- set sql_mode='';
ALTER TABLE `tblRateTableRate`
	ADD COLUMN `EndDate` DATE NULL AFTER `EffectiveDate`;


CREATE TABLE IF NOT EXISTS `tblRateTableRateChangeLog` (
  `RateTableRateChangeLogID` int(11) NOT NULL AUTO_INCREMENT,
  `TempRateTableRateID` int(11) NOT NULL DEFAULT '0',
  `RateTableRateID` int(11) DEFAULT NULL,
  `RateTableId` int(11) DEFAULT NULL,
  `RateId` int(11) DEFAULT NULL,
  `Code` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Description` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Rate` decimal(18,6) DEFAULT NULL,
  `EffectiveDate` datetime DEFAULT NULL,
  `EndDate` datetime DEFAULT NULL,
  `Interval1` int(11) DEFAULT NULL,
  `IntervalN` int(11) DEFAULT NULL,
  `ConnectionFee` decimal(18,6) DEFAULT NULL,
  `Action` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ProcessID` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`RateTableRateChangeLogID`),
  KEY `IX_tblRateTableRateChangeLog_RateTableRateID` (`RateTableRateID`),
  KEY `IX_tblRateTableRateChangeLog_ProcessID` (`ProcessID`),
  KEY `RateId` (`RateId`),
  KEY `EffectiveDate` (`EffectiveDate`),
  KEY `Code` (`Code`),
  KEY `Action` (`Action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `tblTempRateTableRate` (
  `TempRateTableRateID` int(11) NOT NULL AUTO_INCREMENT,
  `CodeDeckId` int(11) DEFAULT NULL,
  `CountryCode` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Code` text COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Rate` decimal(18,6) NOT NULL DEFAULT '0.000000',
  `EffectiveDate` datetime NOT NULL,
  `EndDate` datetime DEFAULT NULL,
  `Change` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ProcessId` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `Preference` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ConnectionFee` decimal(18,6) DEFAULT NULL,
  `Interval1` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `IntervalN` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Forbidden` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `DialStringPrefix` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`TempRateTableRateID`),
  KEY `IX_tblTempRateTableRateProcessID` (`ProcessId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;




DROP TABLE IF EXISTS `tblRateTableRateArchive`;
CREATE TABLE IF NOT EXISTS `tblRateTableRateArchive` (
  `RateTableRateArchiveID` int(11) NOT NULL AUTO_INCREMENT,
  `RateTableRateID` int(11) DEFAULT NULL,
  `RateTableId` int(11) NOT NULL,
  `RateId` int(11) NOT NULL,
  `Rate` decimal(18,6) NOT NULL DEFAULT '0.000000',
  `EffectiveDate` datetime NOT NULL,
  `EndDate` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Interval1` int(11) DEFAULT NULL,
  `IntervalN` int(11) DEFAULT NULL,
  `ConnectionFee` decimal(18,6) DEFAULT NULL,
  `Notes` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`RateTableRateArchiveID`),
  KEY `RateTableRateID` (`RateTableRateID`),
  KEY `RateTableId` (`RateTableId`),
  KEY `RateId` (`RateId`),
  KEY `EffectiveDate` (`EffectiveDate`),
  KEY `EndDate` (`EndDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;




DROP PROCEDURE IF EXISTS `prc_WSReviewRateTableRate`;
DELIMITER //
CREATE PROCEDURE `prc_WSReviewRateTableRate`(
	IN `p_RateTableId` INT,
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

    -- @TODO: code cleanup
	DECLARE newstringcode INT(11) DEFAULT 0;
	DECLARE v_pointer_ INT;
	DECLARE v_rowCount_ INT;
	DECLARE v_RateTableCurrencyID_ INT;
	DECLARE v_CompanyCurrencyID_ INT;

    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
    CREATE TEMPORARY TABLE tmp_JobLog_ (
        Message longtext
    );

    DROP TEMPORARY TABLE IF EXISTS tmp_split_RateTableRate_;
    CREATE TEMPORARY TABLE tmp_split_RateTableRate_ (
		`TempRateTableRateID` int,
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

    DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableRate_;
    CREATE TEMPORARY TABLE tmp_TempRateTableRate_ (
		`TempRateTableRateID` int,
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

    CALL  prc_RateTableCheckDialstringAndDupliacteCode(p_companyId,p_processId,p_dialstringid,p_effectiveImmediately,p_dialcodeSeparator);

	ALTER TABLE `tmp_TempRateTableRate_`	ADD Column `NewRate` decimal(18, 6) ;

    SELECT COUNT(*) AS COUNT INTO newstringcode from tmp_JobLog_;

    SELECT CurrencyID into v_RateTableCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyID FROM tblRateTable WHERE RateTableId=p_RateTableId);
    SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);

	  -- update all rate on newrate with currency conversion.
	update tmp_TempRateTableRate_
	SET
	NewRate = IF (
                    p_CurrencyID > 0,
                    CASE WHEN p_CurrencyID = v_RateTableCurrencyID_
                    THEN
                        Rate
                    WHEN  p_CurrencyID = v_CompanyCurrencyID_
                    THEN
                    (
                        ( Rate  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ and CompanyID = p_companyId ) )
                    )
                    ELSE
                    (
                        (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId )
                            *
                        (Rate  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))
                    )
                    END ,
                    Rate
                )
    WHERE ProcessID=p_processId;

		-- if no error
    IF newstringcode = 0
    THEN
		-- if rates is not in our database (new rates from file) than insert it into ChangeLog
		INSERT INTO tblRateTableRateChangeLog(
            TempRateTableRateID,
            RateTableRateID,
            RateTableId,
            RateId,
            Code,
            Description,
            Rate,
            EffectiveDate,
            EndDate,
            Interval1,
            IntervalN,
            ConnectionFee,
            `Action`,
            ProcessID,
            created_at
		)
		SELECT
			tblTempRateTableRate.TempRateTableRateID,
			tblRateTableRate.RateTableRateID,
            p_RateTableId AS RateTableId,
            tblRate.RateId,
            tblTempRateTableRate.Code,
            tblTempRateTableRate.Description,
            tblTempRateTableRate.Rate,
			tblTempRateTableRate.EffectiveDate,
			tblTempRateTableRate.EndDate ,
			IFNULL(tblTempRateTableRate.Interval1,tblRate.Interval1 ) as Interval1,		-- take interval from file and update in tblRate if not changed in service
			IFNULL(tblTempRateTableRate.IntervalN , tblRate.IntervalN ) as IntervalN,
			tblTempRateTableRate.ConnectionFee,
			'New' AS `Action`,
			p_processId AS ProcessID,
			now() AS created_at
		FROM tmp_TempRateTableRate_ as tblTempRateTableRate
		LEFT JOIN tblRate
			ON tblTempRateTableRate.Code = tblRate.Code AND tblTempRateTableRate.CodeDeckId = tblRate.CodeDeckId  AND tblRate.CompanyID = p_companyId
		LEFT JOIN tblRateTableRate
			ON tblRate.RateID = tblRateTableRate.RateId AND tblRateTableRate.RateTableId = p_RateTableId
			AND tblRateTableRate.EffectiveDate  <= date(now())
		WHERE tblTempRateTableRate.ProcessID=p_processId AND tblRateTableRate.RateTableRateID IS NULL
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');
			-- AND tblTempRateTableRate.EffectiveDate != '0000-00-00 00:00:00';

   		  -- loop through effective date
        DROP TEMPORARY TABLE IF EXISTS tmp_EffectiveDates_;
		CREATE TEMPORARY TABLE tmp_EffectiveDates_ (
			EffectiveDate  Date,
			RowID int,
			INDEX (RowID)
		);
        INSERT INTO tmp_EffectiveDates_
        SELECT distinct
            EffectiveDate,
            @row_num := @row_num+1 AS RowID
        FROM tmp_TempRateTableRate_
            ,(SELECT @row_num := 0) x
        WHERE  ProcessID = p_processId
         -- AND EffectiveDate <> '0000-00-00 00:00:00'
        group by EffectiveDate
        order by EffectiveDate asc;

        SET v_pointer_ = 1;
        SET v_rowCount_ = ( SELECT COUNT(*) FROM tmp_EffectiveDates_ );

        IF v_rowCount_ > 0 THEN

            WHILE v_pointer_ <= v_rowCount_
            DO

                SET @EffectiveDate = ( SELECT EffectiveDate FROM tmp_EffectiveDates_ WHERE RowID = v_pointer_ );
                SET @row_num = 0;

                -- update  previous rate with all latest recent entriy of previous effective date

                INSERT INTO tblRateTableRateChangeLog(
                    TempRateTableRateID,
                    RateTableRateID,
                    RateTableId,
                    RateId,
                    Code,
                    Description,
                    Rate,
                    EffectiveDate,
                    EndDate,
                    Interval1,
                    IntervalN,
                    ConnectionFee,
                    `Action`,
                    ProcessID,
                    created_at
                )
                SELECT
                    distinct
                    tblTempRateTableRate.TempRateTableRateID,
                    RateTableRate.RateTableRateID,
                    p_RateTableId AS RateTableId,
                    RateTableRate.RateId,
                    tblRate.Code,
                    tblRate.Description,
                    tblTempRateTableRate.Rate,
                    tblTempRateTableRate.EffectiveDate,
                    tblTempRateTableRate.EndDate ,
                    tblTempRateTableRate.Interval1,
                    tblTempRateTableRate.IntervalN,
                    tblTempRateTableRate.ConnectionFee,
                    IF(tblTempRateTableRate.NewRate > RateTableRate.Rate, 'Increased', IF(tblTempRateTableRate.NewRate < RateTableRate.Rate, 'Decreased','')) AS `Action`,
                    p_processid AS ProcessID,
                    now() AS created_at
                FROM
                (
                    -- get all rates RowID = 1 to remove old to old effective date
                    select distinct tmp.* ,
                        @row_num := IF(@prev_RateId = tmp.RateID AND @prev_EffectiveDate >= tmp.EffectiveDate, (@row_num + 1), 1) AS RowID,
                        @prev_RateId := tmp.RateID,
                        @prev_EffectiveDate := tmp.EffectiveDate
                    FROM
                    (
                        select distinct vr1.*
                        from tblRateTableRate vr1
                        LEFT outer join tblRateTableRate vr2
                            on vr1.RateTableId = vr2.RateTableId
                            and vr1.RateID = vr2.RateID
                            AND vr2.EffectiveDate  = @EffectiveDate
                        where
                            vr1.RateTableId = p_RateTableId
                            and vr1.EffectiveDate <= COALESCE(vr2.EffectiveDate,@EffectiveDate) -- <= because if same day rate change need to log
                        order by vr1.RateID desc ,vr1.EffectiveDate desc
                    ) tmp ,
                    ( SELECT @row_num := 0 , @prev_RateId := 0 , @prev_EffectiveDate := '' ) x
                      order by RateID desc , EffectiveDate desc
                ) RateTableRate
                JOIN tblRate
                    ON tblRate.CompanyID = p_companyId
                    AND tblRate.RateID = RateTableRate.RateId
                JOIN tmp_TempRateTableRate_ tblTempRateTableRate
                    ON tblTempRateTableRate.Code = tblRate.Code
                    AND tblTempRateTableRate.ProcessID=p_processId
                    --	AND  tblTempRateTableRate.EffectiveDate <> '0000-00-00 00:00:00'
                    AND  RateTableRate.EffectiveDate <= tblTempRateTableRate.EffectiveDate -- <= because if same day rate change need to log
                    AND tblTempRateTableRate.EffectiveDate =  @EffectiveDate
                    AND RateTableRate.RowID = 1
                WHERE
                    RateTableRate.RateTableId = p_RateTableId
                    -- AND tblTempRateTableRate.EffectiveDate <> '0000-00-00 00:00:00'
                    AND tblTempRateTableRate.Code IS NOT NULL
                    AND tblTempRateTableRate.ProcessID=p_processId
                    AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

                SET v_pointer_ = v_pointer_ + 1;

            END WHILE;

        END IF;


        IF p_list_option = 1 -- p_list_option = 1 : "Completed List", p_list_option = 2 : "Partial List"
        THEN
            -- get rates which is not in file and insert it into ChangeLog
            INSERT INTO tblRateTableRateChangeLog(
                RateTableRateID,
                RateTableId,
                RateId,
                Code,
                Description,
                Rate,
                EffectiveDate,
                EndDate,
                Interval1,
                IntervalN,
                ConnectionFee,
                `Action`,
                ProcessID,
                created_at
            )
            SELECT DISTINCT
                tblRateTableRate.RateTableRateID,
                p_RateTableId AS RateTableId,
                tblRateTableRate.RateId,
                tblRate.Code,
                tblRate.Description,
                tblRateTableRate.Rate,
                tblRateTableRate.EffectiveDate,
                tblRateTableRate.EndDate ,
                tblRateTableRate.Interval1,
                tblRateTableRate.IntervalN,
                tblRateTableRate.ConnectionFee,
                'Deleted' AS `Action`,
                p_processId AS ProcessID,
                now() AS deleted_at
            FROM tblRateTableRate
            JOIN tblRate
                ON tblRate.RateID = tblRateTableRate.RateId AND tblRate.CompanyID = p_companyId
            LEFT JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
                ON tblTempRateTableRate.Code = tblRate.Code
                AND tblTempRateTableRate.ProcessID=p_processId
                AND (
                    -- normal condition
                    ( tblTempRateTableRate.EndDate is null AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block') )
                    OR
                    -- skip records just to avoid duplicate records in tblRateTableRateChangeLog tabke - when EndDate is given with delete
                    ( tblTempRateTableRate.EndDate is not null AND tblTempRateTableRate.Change IN ('Delete', 'R', 'D', 'Blocked','Block') )
                )
            WHERE tblRateTableRate.RateTableId = p_RateTableId
                AND ( tblRateTableRate.EndDate is null OR tblRateTableRate.EndDate <= date(now()) )
                AND tblTempRateTableRate.Code IS NULL
            ORDER BY RateTableRateID ASC;

        END IF;


        INSERT INTO tblRateTableRateChangeLog(
            RateTableRateID,
            RateTableId,
            RateId,
            Code,
            Description,
            Rate,
            EffectiveDate,
            EndDate,
            Interval1,
            IntervalN,
            ConnectionFee,
            `Action`,
            ProcessID,
            created_at
        )
        SELECT DISTINCT
            tblRateTableRate.RateTableRateID,
            p_RateTableId AS RateTableId,
            tblRateTableRate.RateId,
            tblRate.Code,
            tblRate.Description,
            tblRateTableRate.Rate,
            tblRateTableRate.EffectiveDate,
            IFNULL(tblTempRateTableRate.EndDate,tblRateTableRate.EndDate) as  EndDate ,
            tblRateTableRate.Interval1,
            tblRateTableRate.IntervalN,
            tblRateTableRate.ConnectionFee,
            'Deleted' AS `Action`,
            p_processId AS ProcessID,
            now() AS deleted_at
        FROM tblRateTableRate
        JOIN tblRate
            ON tblRate.RateID = tblRateTableRate.RateId AND tblRate.CompanyID = p_companyId
        LEFT JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
            ON tblRate.Code = tblTempRateTableRate.Code
            AND tblTempRateTableRate.Change IN ('Delete', 'R', 'D', 'Blocked','Block')
            AND tblTempRateTableRate.ProcessID=p_processId
            -- AND tblTempRateTableRate.EndDate <= date(now())
            -- AND tblTempRateTableRate.ProcessID=p_processId
        WHERE tblRateTableRate.RateTableId = p_RateTableId
            -- AND tblRateTableRate.EndDate <= date(now())
            AND tblTempRateTableRate.Code IS NOT NULL
        ORDER BY RateTableRateID ASC;


    END IF;

    SELECT * FROM tmp_JobLog_;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;





DROP PROCEDURE IF EXISTS `prc_RateTableCheckDialstringAndDupliacteCode`;
DELIMITER //
CREATE PROCEDURE `prc_RateTableCheckDialstringAndDupliacteCode`(
	IN `p_companyId` INT,
	IN `p_processId` VARCHAR(200) ,
	IN `p_dialStringId` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_dialcodeSeparator` VARCHAR(50)
)
ThisSP:BEGIN

	DECLARE totaldialstringcode INT(11) DEFAULT 0;
	DECLARE v_CodeDeckId_ INT ;
	DECLARE totalduplicatecode INT(11);
	DECLARE errormessage longtext;
	DECLARE errorheader longtext;

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRateDialString_ ;
	CREATE TEMPORARY TABLE `tmp_RateTableRateDialString_` (
		`TempRateTableRateID` int,
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

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRateDialString_2 ;
	CREATE TEMPORARY TABLE `tmp_RateTableRateDialString_2` (
		`TempRateTableRateID` int,
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

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRateDialString_3 ;
	CREATE TEMPORARY TABLE `tmp_RateTableRateDialString_3` (
		`TempRateTableRateID` int,
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

	CALL prc_SplitRateTableRate(p_processId,p_dialcodeSeparator);

	IF  p_effectiveImmediately = 1
	THEN
		UPDATE tmp_split_RateTableRate_
		SET EffectiveDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EffectiveDate < DATE_FORMAT (NOW(), '%Y-%m-%d');

		UPDATE tmp_split_RateTableRate_
		SET EndDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EndDate < DATE_FORMAT (NOW(), '%Y-%m-%d');
	END IF;

	DROP TEMPORARY TABLE IF EXISTS tmp_split_RateTableRate_2;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_split_RateTableRate_2 as (SELECT * FROM tmp_split_RateTableRate_);

	INSERT INTO tmp_TempRateTableRate_
	SELECT DISTINCT
		`TempRateTableRateID`,
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
	FROM tmp_split_RateTableRate_
	WHERE tmp_split_RateTableRate_.ProcessId = p_processId;

	SELECT CodeDeckId INTO v_CodeDeckId_
	FROM tmp_TempRateTableRate_
	WHERE ProcessId = p_processId  LIMIT 1;

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

	IF  p_effectiveImmediately = 1
	THEN
		UPDATE tmp_TempRateTableRate_
		SET EffectiveDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EffectiveDate < DATE_FORMAT (NOW(), '%Y-%m-%d');

		UPDATE tmp_TempRateTableRate_
		SET EndDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EndDate < DATE_FORMAT (NOW(), '%Y-%m-%d');
	END IF;

	SELECT count(*) INTO totalduplicatecode FROM(
	SELECT count(code) as c,code FROM tmp_TempRateTableRate_  GROUP BY Code,EffectiveDate,DialStringPrefix HAVING c>1) AS tbl;

	IF  totalduplicatecode > 0
	THEN

		SELECT GROUP_CONCAT(code) into errormessage FROM(
		SELECT DISTINCT code, 1 as a FROM(
		SELECT   count(code) as c,code FROM tmp_TempRateTableRate_  GROUP BY Code,EffectiveDate,DialStringPrefix HAVING c>1) AS tbl) as tbl2 GROUP by a;

		INSERT INTO tmp_JobLog_ (Message)
		SELECT DISTINCT
			CONCAT(code , ' DUPLICATE CODE')
		FROM(
			SELECT   count(code) as c,code FROM tmp_TempRateTableRate_  GROUP BY Code,EffectiveDate,DialStringPrefix HAVING c>1) AS tbl;
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
			FROM tmp_TempRateTableRate_ vr
			LEFT JOIN tmp_DialString_ ds
				ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  ))
			WHERE vr.ProcessId = p_processId
				AND ds.DialStringID IS NULL
				AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

			IF totaldialstringcode > 0
			THEN

				INSERT INTO tmp_JobLog_ (Message)
				SELECT DISTINCT CONCAT(Code ,' ', vr.DialStringPrefix , ' No PREFIX FOUND')
				FROM tmp_TempRateTableRate_ vr
				LEFT JOIN tmp_DialString_ ds
					ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  ))
				WHERE vr.ProcessId = p_processId
					AND ds.DialStringID IS NULL
					AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');
			END IF;

			IF totaldialstringcode = 0
			THEN

				INSERT INTO tmp_RateTableRateDialString_
				SELECT DISTINCT
					`TempRateTableRateID`,
					`CodeDeckId`,
					`DialString`,
					CASE WHEN ds.Description IS NULL OR ds.Description = ''
					THEN
						tblTempRateTableRate.Description
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
					tblTempRateTableRate.Forbidden as Forbidden ,
					tblTempRateTableRate.DialStringPrefix as DialStringPrefix
				FROM tmp_TempRateTableRate_ as tblTempRateTableRate
				INNER JOIN tmp_DialString_ ds
					ON ( (tblTempRateTableRate.Code = ds.ChargeCode AND tblTempRateTableRate.DialStringPrefix = '') OR (tblTempRateTableRate.DialStringPrefix != '' AND tblTempRateTableRate.DialStringPrefix =  ds.DialString AND tblTempRateTableRate.Code = ds.ChargeCode  ))
				WHERE tblTempRateTableRate.ProcessId = p_processId
					AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

				INSERT INTO tmp_RateTableRateDialString_2
				SELECT * FROM tmp_RateTableRateDialString_;

				INSERT INTO tmp_RateTableRateDialString_3
				SELECT vrs1.* from tmp_RateTableRateDialString_2 vrs1
				LEFT JOIN tmp_RateTableRateDialString_ vrs2 ON vrs1.Code=vrs2.Code AND vrs1.CodeDeckId=vrs2.CodeDeckId AND vrs1.Description=vrs2.Description AND vrs1.EffectiveDate=vrs2.EffectiveDate AND vrs1.DialStringPrefix != vrs2.DialStringPrefix
				WHERE ( (vrs1.DialStringPrefix ='' AND vrs2.Code IS NULL) OR (vrs1.DialStringPrefix!='' AND vrs2.Code IS NOT NULL));

				DELETE  FROM tmp_TempRateTableRate_ WHERE  ProcessId = p_processId;

				INSERT INTO tmp_TempRateTableRate_(
					`TempRateTableRateID`,
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
					`TempRateTableRateID`,
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
				FROM tmp_RateTableRateDialString_3;

				UPDATE tmp_TempRateTableRate_ as tblTempRateTableRate
				JOIN tmp_DialString_ ds
					ON ( (tblTempRateTableRate.Code = ds.ChargeCode and tblTempRateTableRate.DialStringPrefix = '') OR (tblTempRateTableRate.DialStringPrefix != '' and tblTempRateTableRate.DialStringPrefix =  ds.DialString and tblTempRateTableRate.Code = ds.ChargeCode  ))
					AND tblTempRateTableRate.ProcessId = p_processId
					AND ds.Forbidden = 1
				SET tblTempRateTableRate.Forbidden = 'B';

				UPDATE tmp_TempRateTableRate_ as  tblTempRateTableRate
				JOIN tmp_DialString_ ds
					ON ( (tblTempRateTableRate.Code = ds.ChargeCode and tblTempRateTableRate.DialStringPrefix = '') OR (tblTempRateTableRate.DialStringPrefix != '' and tblTempRateTableRate.DialStringPrefix =  ds.DialString and tblTempRateTableRate.Code = ds.ChargeCode  ))
					AND tblTempRateTableRate.ProcessId = p_processId
					AND ds.Forbidden = 0
				SET tblTempRateTableRate.Forbidden = 'UB';

			END IF;

		END IF;

	END IF;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_SplitRateTableRate`;
DELIMITER //
CREATE PROCEDURE `prc_SplitRateTableRate`(
	IN `p_processId` VARCHAR(200),
	IN `p_dialcodeSeparator` VARCHAR(50)
)
ThisSP:BEGIN

	DECLARE i INTEGER;
	DECLARE v_rowCount_ INT;
	DECLARE v_pointer_ INT;
	DECLARE v_TempRateTableRateID_ INT;
	DECLARE v_Code_ TEXT;
	DECLARE v_CountryCode_ VARCHAR(500);
	DECLARE newcodecount INT(11) DEFAULT 0;

	IF p_dialcodeSeparator !='null'
	THEN

		DROP TEMPORARY TABLE IF EXISTS `my_splits`;
		CREATE TEMPORARY TABLE `my_splits` (
			`TempRateTableRateID` INT(11) NULL DEFAULT NULL,
			`Code` Text NULL DEFAULT NULL,
			`CountryCode` Text NULL DEFAULT NULL
		);

		SET i = 1;
		REPEAT
			INSERT INTO my_splits (TempRateTableRateID, Code, CountryCode)
			SELECT TempRateTableRateID , FnStringSplit(Code, p_dialcodeSeparator, i), CountryCode  FROM tblTempRateTableRate
			WHERE FnStringSplit(Code, p_dialcodeSeparator , i) IS NOT NULL
				AND ProcessId = p_processId;

			SET i = i + 1;
			UNTIL ROW_COUNT() = 0
		END REPEAT;

		UPDATE my_splits SET Code = trim(Code);

		DROP TEMPORARY TABLE IF EXISTS tmp_newratetable_splite_;
		CREATE TEMPORARY TABLE tmp_newratetable_splite_  (
			RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			TempRateTableRateID INT(11) NULL DEFAULT NULL,
			Code VARCHAR(500) NULL DEFAULT NULL,
			CountryCode VARCHAR(500) NULL DEFAULT NULL
		);

		INSERT INTO tmp_newratetable_splite_(TempRateTableRateID,Code,CountryCode)
		SELECT
			TempRateTableRateID,
			Code,
			CountryCode
		FROM my_splits
		WHERE Code like '%-%'
			AND TempRateTableRateID IS NOT NULL;

		SET v_pointer_ = 1;
		SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_newratetable_splite_);

		WHILE v_pointer_ <= v_rowCount_
		DO
			SET v_TempRateTableRateID_ = (SELECT TempRateTableRateID FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);
			SET v_Code_ = (SELECT Code FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);
			SET v_CountryCode_ = (SELECT CountryCode FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);

			Call prc_SplitAndInsertRateTableRate(v_TempRateTableRateID_,v_Code_,v_CountryCode_);

			SET v_pointer_ = v_pointer_ + 1;
		END WHILE;

		DELETE FROM my_splits
		WHERE Code like '%-%'
			AND TempRateTableRateID IS NOT NULL;

		DELETE FROM my_splits
		WHERE Code = '' OR Code IS NULL;

		INSERT INTO tmp_split_RateTableRate_
		SELECT DISTINCT
			my_splits.TempRateTableRateID as `TempRateTableRateID`,
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
		INNER JOIN tblTempRateTableRate
			ON my_splits.TempRateTableRateID = tblTempRateTableRate.TempRateTableRateID
		WHERE	tblTempRateTableRate.ProcessId = p_processId;

	END IF;

	IF p_dialcodeSeparator = 'null'
	THEN

		INSERT INTO tmp_split_RateTableRate_
		SELECT DISTINCT
			`TempRateTableRateID`,
			`CodeDeckId`,
			CONCAT(IFNULL(tblTempRateTableRate.CountryCode,''),tblTempRateTableRate.Code) as Code,
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
		FROM tblTempRateTableRate
		WHERE ProcessId = p_processId;

	END IF;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_SplitAndInsertRateTableRate`;
DELIMITER //
CREATE PROCEDURE `prc_SplitAndInsertRateTableRate`(
	IN `TempRateTableRateID` INT,
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
		INSERT my_splits (TempRateTableRateID,Code,CountryCode) VALUES (TempRateTableRateID,v_Last_,p_countryCode);
		SET v_Last_ = v_Last_ - 1;
	END WHILE;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_getReviewRateTableRates`;
DELIMITER //
CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_getReviewRateTableRates`(
	IN `p_ProcessID` VARCHAR(50),
	IN `p_Action` VARCHAR(50),
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

	IF p_isExport = 0
	THEN
		SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

		SELECT
			distinct
			IF(p_Action='Deleted',RateTableRateID,TempRateTableRateID) AS RateTableRateID,
			`Code`,`Description`,`Rate`,`EffectiveDate`,`EndDate`,`ConnectionFee`,`Interval1`,`IntervalN`
		FROM
			tblRateTableRateChangeLog
		WHERE
			ProcessID = p_ProcessID AND Action = p_Action
			AND
			(p_Code IS NULL OR p_Code = '' OR Code LIKE REPLACE(p_Code, '*', '%'))
			AND
			(p_Description IS NULL OR p_Description = '' OR Description LIKE REPLACE(p_Description, '*', '%'))
		ORDER BY
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
			END DESC,
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
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeDESC') THEN ConnectionFee
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeASC') THEN ConnectionFee
			END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT
			COUNT(*) AS totalcount
		FROM
			tblRateTableRateChangeLog
		WHERE
			ProcessID = p_ProcessID AND Action = p_Action
			AND
			(p_Code IS NULL OR p_Code = '' OR Code LIKE REPLACE(p_Code, '*', '%'))
			AND
			(p_Description IS NULL OR p_Description = '' OR Description LIKE REPLACE(p_Description, '*', '%'));
	END IF;

	IF p_isExport = 1
	THEN
		SELECT
			distinct
			`Code`,`Description`,`Rate`,`EffectiveDate`,`EndDate`,`ConnectionFee`,`Interval1`,`IntervalN`
		FROM
			tblRateTableRateChangeLog
		WHERE
			ProcessID = p_ProcessID AND Action = p_Action
			AND
			(p_Code IS NULL OR p_Code = '' OR Code LIKE REPLACE(p_Code, '*', '%'))
			AND
			(p_Description IS NULL OR p_Description = '' OR Description LIKE REPLACE(p_Description, '*', '%'));
	END IF;


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_WSReviewRateTableRateUpdate`;
DELIMITER //
CREATE PROCEDURE `prc_WSReviewRateTableRateUpdate`(
	IN `p_RateTableID` INT,
	IN `p_RateIds` TEXT,
	IN `p_ProcessID` VARCHAR(200),
	IN `p_criteria` INT,
	IN `p_Action` VARCHAR(20),
	IN `p_Interval1` INT,
	IN `p_IntervalN` INT,
	IN `p_EndDate` DATETIME,
	IN `p_Code` VARCHAR(50),
	IN `p_Description` VARCHAR(50)
)
ThisSP:BEGIN

	DECLARE newstringcode INT(11) DEFAULT 0;
	DECLARE v_pointer_ INT;
	DECLARE v_rowCount_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
    CREATE TEMPORARY TABLE tmp_JobLog_ (
        Message longtext
    );

	SET @stm_and_code = '';
	IF p_Code != ''
	THEN
		SET @stm_and_code = CONCAT(' AND ("',p_Code,'" IS NULL OR "',p_Code,'" = "" OR tvr.Code LIKE "',REPLACE(p_Code, "*", "%"),'")');
	END IF;

	SET @stm_and_desc = '';
	IF p_Description != ''
	THEN
		SET @stm_and_desc = CONCAT(' AND ("',p_Description,'" IS NULL OR "',p_Description,'" = "" OR tvr.Description LIKE "',REPLACE(p_Description, "*", "%"),'")');
	END IF;

    CASE p_Action
		WHEN 'New' THEN
			SET @stm = '';
			IF p_Interval1 > 0
			THEN
				SET @stm = CONCAT(@stm,'tvr.Interval1 = ',p_Interval1);
			END IF;

			IF p_IntervalN > 0
			THEN
				SET @stm = CONCAT(@stm,IF(@stm != '',',',''),'tvr.IntervalN = ',p_IntervalN);
			END IF;

			IF p_criteria = 1
			THEN
				IF @stm != ''
				THEN
					SET @stm1 = CONCAT('UPDATE tblTempRateTableRate tvr LEFT JOIN tblRateTableRateChangeLog vrcl ON tvr.TempRateTableRateID=vrcl.TempRateTableRateID SET ',@stm,' WHERE tvr.TempRateTableRateID=vrcl.TempRateTableRateID AND vrcl.Action = "',p_Action,'" AND tvr.ProcessID = "',p_ProcessID,'" ',@stm_and_code,' ',@stm_and_desc,';');
					select @stm1;
					PREPARE stmt1 FROM @stm1;
					EXECUTE stmt1;
					DEALLOCATE PREPARE stmt1;

					SET @stm2 = CONCAT('UPDATE tblRateTableRateChangeLog tvr SET ',@stm,' WHERE ProcessID = "',p_ProcessID,'" AND Action = "',p_Action,'" ',@stm_and_code,' ',@stm_and_desc,';');

					PREPARE stm2 FROM @stm2;
					EXECUTE stm2;
					DEALLOCATE PREPARE stm2;
				END IF;
			ELSE
				IF @stm != ''
				THEN
					SET @stm1 = CONCAT('UPDATE tblTempRateTableRate tvr LEFT JOIN tblRateTableRateChangeLog vrcl ON tvr.TempRateTableRateID=vrcl.TempRateTableRateID SET ',@stm,' WHERE tvr.TempRateTableRateID IN (',p_RateIds,') AND tvr.TempRateTableRateID=vrcl.TempRateTableRateID AND vrcl.Action = "',p_Action,'" AND tvr.ProcessID = "',p_ProcessID,'" ',@stm_and_code,' ',@stm_and_desc,';');

					PREPARE stmt1 FROM @stm1;
					EXECUTE stmt1;
					DEALLOCATE PREPARE stmt1;

					SET @stm2 = CONCAT('UPDATE tblRateTableRateChangeLog tvr SET ',@stm,' WHERE TempRateTableRateID IN (',p_RateIds,') AND ProcessID = "',p_ProcessID,'" AND Action = "',p_Action,'" ',@stm_and_code,' ',@stm_and_desc,';');

					PREPARE stm2 FROM @stm2;
					EXECUTE stm2;
					DEALLOCATE PREPARE stm2;
				END IF;
			END IF;

		WHEN 'Deleted' THEN
			IF p_criteria = 1
			THEN
				SET @stm1 = CONCAT('UPDATE tblRateTableRateChangeLog tvr SET EndDate="',p_EndDate,'" WHERE ProcessID="',p_ProcessID,'" AND `Action`="',p_Action,'" ',@stm_and_code,' ',@stm_and_desc,';');

				PREPARE stmt1 FROM @stm1;
				EXECUTE stmt1;
				DEALLOCATE PREPARE stmt1;
			ELSE
				SET @stm1 = CONCAT('UPDATE tblRateTableRateChangeLog tvr SET EndDate="',p_EndDate,'" WHERE RateTableRateID IN (',p_RateIds,')AND Action = "',p_Action,'" AND ProcessID = "',p_ProcessID,'" ',@stm_and_code,' ',@stm_and_desc,';');

				PREPARE stmt1 FROM @stm1;
				EXECUTE stmt1;
				DEALLOCATE PREPARE stmt1;
			END IF;
	END CASE;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;





DROP PROCEDURE IF EXISTS `prc_WSProcessRateTableRate`;
DELIMITER //
CREATE PROCEDURE `prc_WSProcessRateTableRate`(
	IN `p_RateTableId` INT,
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
	IN `p_list_option` INT,
	IN `p_UserName` TEXT
)
ThisSP:BEGIN

	DECLARE v_AffectedRecords_ INT DEFAULT 0;
	DECLARE v_CodeDeckId_ INT ;
	DECLARE totaldialstringcode INT(11) DEFAULT 0;
	DECLARE newstringcode INT(11) DEFAULT 0;
	DECLARE totalduplicatecode INT(11);
	DECLARE errormessage longtext;
	DECLARE errorheader longtext;
	DECLARE v_RateTableCurrencyID_ INT;
	DECLARE v_CompanyCurrencyID_ INT;

	DECLARE v_pointer_ INT;
	DECLARE v_rowCount_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
	CREATE TEMPORARY TABLE tmp_JobLog_ (
		Message longtext
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_split_RateTableRate_;
	CREATE TEMPORARY TABLE tmp_split_RateTableRate_ (
		`TempRateTableRateID` int,
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

	DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableRate_;
	CREATE TEMPORARY TABLE tmp_TempRateTableRate_ (
		TempRateTableRateID int,
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

	DROP TEMPORARY TABLE IF EXISTS tmp_Delete_RateTableRate;
	CREATE TEMPORARY TABLE tmp_Delete_RateTableRate (
		RateTableRateID INT,
		RateTableId INT,
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
		INDEX tmp_RateTableRateDiscontinued_RateTableRateID (`RateTableRateID`)
	);

	/*  1.  Check duplicate code, dial string   */
	CALL  prc_RateTableCheckDialstringAndDupliacteCode(p_companyId,p_processId,p_dialstringid,p_effectiveImmediately,p_dialcodeSeparator);

	SELECT COUNT(*) AS COUNT INTO newstringcode from tmp_JobLog_;

	-- if no error
	IF newstringcode = 0
	THEN
		/*  2.  Send Today EndDate to rates which are marked deleted in review screen  */
		/*  3.  Update interval in temp table */

		-- if review
		IF (SELECT count(*) FROM tblRateTableRateChangeLog WHERE ProcessID = p_processId ) > 0
		THEN
			-- update end date given from tblRateTableRateChangeLog for deleted rates.
			UPDATE
				tblRateTableRate vr
			INNER JOIN tblRateTableRateChangeLog  vrcl
			on vrcl.RateTableRateID = vr.RateTableRateID
			SET
				vr.EndDate = IFNULL(vrcl.EndDate,date(now()))
			WHERE vrcl.ProcessID = p_processId
				AND vrcl.`Action`  ='Deleted';

			-- update end date on temp table
			UPDATE tmp_TempRateTableRate_ tblTempRateTableRate
			JOIN tblRateTableRateChangeLog vrcl
				ON  vrcl.ProcessId = p_processId
				AND vrcl.Code = tblTempRateTableRate.Code
				-- AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
			SET
				tblTempRateTableRate.EndDate = vrcl.EndDate
			WHERE
				vrcl.`Action` = 'Deleted'
				AND vrcl.EndDate IS NOT NULL ;

			-- update intervals.
			UPDATE tmp_TempRateTableRate_ tblTempRateTableRate
			JOIN tblRateTableRateChangeLog vrcl
				ON  vrcl.ProcessId = p_processId
				AND vrcl.Code = tblTempRateTableRate.Code
				-- AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
			SET
				tblTempRateTableRate.Interval1 = vrcl.Interval1 ,
				tblTempRateTableRate.IntervalN = vrcl.IntervalN
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
			UPDATE tblRateTableRate
				SET tblRateTableRate.EndDate = date(now())
			WHERE RateTableId = p_RateTableId;

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
			INSERT INTO tmp_Delete_RateTableRate(
				RateTableRateID ,
				RateTableId,
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
				tblRateTableRate.RateTableRateID,
				p_RateTableId AS RateTableId,
				tblRateTableRate.RateId,
				tblRate.Code,
				tblRate.Description,
				tblRateTableRate.Rate,
				tblRateTableRate.EffectiveDate,
				IFNULL(tblRateTableRate.EndDate,date(now())) ,
				tblRateTableRate.Interval1,
				tblRateTableRate.IntervalN,
				tblRateTableRate.ConnectionFee,
				now() AS deleted_at
			FROM tblRateTableRate
			JOIN tblRate
				ON tblRate.RateID = tblRateTableRate.RateId
				AND tblRate.CompanyID = p_companyId
			LEFT JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
				ON tblTempRateTableRate.Code = tblRate.Code
				AND  tblTempRateTableRate.ProcessId = p_processId
				AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
			WHERE tblRateTableRate.RateTableId = p_RateTableId
				AND tblTempRateTableRate.Code IS NULL
				AND ( tblRateTableRate.EndDate is NULL OR tblRateTableRate.EndDate <= date(now()) )
			ORDER BY RateTableRateID ASC;

			/*IF (FOUND_ROWS() > 0) THEN
			INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Records marked deleted as Not exists in File' );
			END IF;*/

			-- set end date will remove at bottom in archive proc
			UPDATE tblRateTableRate
			JOIN tmp_Delete_RateTableRate ON tblRateTableRate.RateTableRateID = tmp_Delete_RateTableRate.RateTableRateID
				SET tblRateTableRate.EndDate = date(now())
			WHERE
				tblRateTableRate.RateTableId = p_RateTableId;

		END IF;

		/* 6. Move Rates to archive which has EndDate <= now()  */
		-- move to archive if EndDate is <= now()
		IF ( (SELECT count(*) FROM tblRateTableRate WHERE  RateTableId = p_RateTableId AND EndDate <= NOW() )  > 0  ) THEN

			-- move to archive
			/*INSERT INTO tblRateTableRateArchive
			SELECT DISTINCT  null , -- Primary Key column
				`RateTableRateID`,
				`RateTableId`,
				`RateId`,
				`Rate`,
				`EffectiveDate`,
				IFNULL(`EndDate`,date(now())) as EndDate,
				`updated_at`,
				`created_at`,
				`created_by`,
				`ModifiedBy`,
				`Interval1`,
				`IntervalN`,
				`ConnectionFee`,
				concat('Ends Today rates @ ' , now() ) as `Notes`
			FROM tblRateTableRate
			WHERE  RateTableId = p_RateTableId AND EndDate <= NOW();

			delete from tblRateTableRate
			WHERE  RateTableId = p_RateTableId AND EndDate <= NOW();*/

			-- Update previous rate before archive
			call prc_RateTableRateUpdatePreviousRate(p_RateTableId,'');

			-- Archive Rates
			call prc_ArchiveOldRateTableRate(p_RateTableId,p_UserName);

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

			/*IF (FOUND_ROWS() > 0) THEN
					INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - New Code Inserted into Codedeck ' );
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
						AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
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
						tblTempRateTableRate.Code,
						tblTempRateTableRate.Description
					FROM tmp_TempRateTableRate_  as tblTempRateTableRate
					LEFT JOIN tblRate
						ON tblRate.Code = tblTempRateTableRate.Code
						AND tblRate.CompanyID = p_companyId
						AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
					WHERE tblRate.RateID IS NULL
						AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
				) as tbl;
			END IF;
		END IF;

		/* 8. delete rates which will be map as deleted */

		-- delete rates which will be map as deleted
		UPDATE tblRateTableRate
		INNER JOIN tblRate
			ON tblRate.RateID = tblRateTableRate.RateId
			AND tblRate.CompanyID = p_companyId
		INNER JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND tblTempRateTableRate.Change IN ('Delete', 'R', 'D', 'Blocked', 'Block')
		SET tblRateTableRate.EndDate = IFNULL(tblTempRateTableRate.EndDate,date(now()))
		WHERE tblRateTableRate.RateTableId = p_RateTableId;

		/*IF (FOUND_ROWS() > 0) THEN
		INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Records marked deleted as mapped in File ' );
		END IF;*/


		-- need to get ratetable rates with latest records ....
		-- and then need to use that table to insert update records in ratetable rate.


		-- ------

		/* 9. Update Interval in tblRate */

		-- Update Interval Changed for Action = "New"
		-- update Intervals which are not maching with tblTempRateTableRate
		-- so as if intervals will not mapped next time it will be same as last file.
		UPDATE tblRate
		JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
			ON 	  tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
			AND tblTempRateTableRate.Code = tblRate.Code
			AND  tblTempRateTableRate.ProcessId = p_processId
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
		SET
			tblRate.Interval1 = tblTempRateTableRate.Interval1,
			tblRate.IntervalN = tblTempRateTableRate.IntervalN
		WHERE
			tblTempRateTableRate.Interval1 IS NOT NULL
			AND tblTempRateTableRate.IntervalN IS NOT NULL
			AND
			(
				tblRate.Interval1 != tblTempRateTableRate.Interval1
				OR
				tblRate.IntervalN != tblTempRateTableRate.IntervalN
			);


		/* 10. Update INTERVAL, ConnectionFee,  */

		UPDATE tblRateTableRate
		INNER JOIN tblRate
			ON tblRateTableRate.RateId = tblRate.RateId
			AND tblRateTableRate.RateTableId = p_RateTableId
		INNER JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
			AND tblRateTableRate.RateId = tblRate.RateId
		SET tblRateTableRate.ConnectionFee = tblTempRateTableRate.ConnectionFee,
			tblRateTableRate.Interval1 = tblTempRateTableRate.Interval1,
			tblRateTableRate.IntervalN = tblTempRateTableRate.IntervalN
			--  tblRateTableRate.EndDate = tblTempRateTableRate.EndDate
		WHERE tblRateTableRate.RateTableId = p_RateTableId;


		/*IF (FOUND_ROWS() > 0) THEN
		INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Updated Existing Records' );
		END IF;*/


		/* 12. Delete rates which are same in file   */

		-- delete rates which are not increase/decreased  (rates = rates)
		DELETE tblTempRateTableRate
		FROM tmp_TempRateTableRate_ as tblTempRateTableRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableRate.Code
		JOIN tblRateTableRate
			ON tblRateTableRate.RateId = tblRate.RateId
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
			AND tblRateTableRate.RateTableId = p_RateTableId
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

		/*IF (FOUND_ROWS() > 0) THEN
		INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Discarded no change records' );
		END IF;*/

		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

		SELECT CurrencyID into v_RateTableCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyID FROM tblRateTable WHERE RateTableId=p_RateTableId);
		SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);

		/* 13. update currency   */

		/*UPDATE tmp_TempRateTableRate_ as tblTempRateTableRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		JOIN tblRateTableRate
			ON tblRateTableRate.RateId = tblRate.RateId
			AND tblRateTableRate.RateTableId = p_RateTableId
		SET tblRateTableRate.Rate = IF (
			p_CurrencyID > 0,
			CASE WHEN p_CurrencyID = v_RateTableCurrencyID_
			THEN
				tblTempRateTableRate.Rate
			WHEN  p_CurrencyID = v_CompanyCurrencyID_
			THEN
			(
				( tblTempRateTableRate.Rate  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ and CompanyID = p_companyId ) )
			)
			ELSE
			(
				(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId )
				*
				(tblTempRateTableRate.Rate  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))
			)
			END ,
			tblTempRateTableRate.Rate
		)
		WHERE tblTempRateTableRate.Rate <> tblRateTableRate.Rate
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
			AND DATE_FORMAT (tblRateTableRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTableRate.EffectiveDate, '%Y-%m-%d');

		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();*/

		/* 13. archive same date's rate   */
		DROP TEMPORARY TABLE IF EXISTS tmp_PreviousRate;
		CREATE TEMPORARY TABLE `tmp_PreviousRate` (
			`RateId` int,
			`PreviousRate` decimal(18, 6),
			`EffectiveDate` Datetime
		);

		UPDATE tmp_TempRateTableRate_ as tblTempRateTableRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		JOIN tblRateTableRate
			ON tblRateTableRate.RateId = tblRate.RateId
			AND tblRateTableRate.RateTableId = p_RateTableId
		SET tblRateTableRate.EndDate = NOW()
		WHERE tblTempRateTableRate.Rate <> tblRateTableRate.Rate
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
			AND DATE_FORMAT (tblRateTableRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTableRate.EffectiveDate, '%Y-%m-%d');

		INSERT INTO
			tmp_PreviousRate (RateId,PreviousRate,EffectiveDate)
		SELECT
			tblRateTableRate.RateId,tblRateTableRate.Rate,tblTempRateTableRate.EffectiveDate
		FROM
			tmp_TempRateTableRate_ as tblTempRateTableRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		JOIN tblRateTableRate
			ON tblRateTableRate.RateId = tblRate.RateId
			AND tblRateTableRate.RateTableId = p_RateTableId
		WHERE tblTempRateTableRate.Rate <> tblRateTableRate.Rate
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
			AND DATE_FORMAT (tblRateTableRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTableRate.EffectiveDate, '%Y-%m-%d');

		-- archive rates which has EndDate <= today
		call prc_ArchiveOldRateTableRate(p_RateTableId,p_UserName);

		/* 13. insert new rates   */

		INSERT INTO tblRateTableRate (
			RateTableId,
			RateId,
			Rate,
			EffectiveDate,
			EndDate,
			ConnectionFee,
			Interval1,
			IntervalN,
			PreviousRate
		)
		SELECT DISTINCT
			p_RateTableId,
			tblRate.RateID,
			IF (
				p_CurrencyID > 0,
				CASE WHEN p_CurrencyID = v_RateTableCurrencyID_
				THEN
					tblTempRateTableRate.Rate
				WHEN  p_CurrencyID = v_CompanyCurrencyID_
				THEN
				(
					( tblTempRateTableRate.Rate  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ and CompanyID = p_companyId ) )
				)
				ELSE
				(
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId )
					*
					(tblTempRateTableRate.Rate  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))
				)
				END ,
				tblTempRateTableRate.Rate
			) ,
			tblTempRateTableRate.EffectiveDate,
			tblTempRateTableRate.EndDate,
			tblTempRateTableRate.ConnectionFee,
			tblTempRateTableRate.Interval1,
			tblTempRateTableRate.IntervalN,
			IFNULL(tmp_PreviousRate.PreviousRate,0) AS PreviousRate
		FROM tmp_TempRateTableRate_ as tblTempRateTableRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		LEFT JOIN tblRateTableRate
			ON tblRate.RateID = tblRateTableRate.RateId
			AND tblRateTableRate.RateTableId = p_RateTableId
			AND tblTempRateTableRate.EffectiveDate = tblRateTableRate.EffectiveDate
		LEFT JOIN tmp_PreviousRate
			ON tblRate.RateId = tmp_PreviousRate.RateId AND tblTempRateTableRate.EffectiveDate = tmp_PreviousRate.EffectiveDate
		WHERE tblRateTableRate.RateTableRateID IS NULL
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
			AND tblTempRateTableRate.EffectiveDate >= DATE_FORMAT (NOW(), '%Y-%m-%d');

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
		(
			select distinct EffectiveDate
			from 	tblRateTableRate
			WHERE
				RateTableId = p_RateTableId
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

				UPDATE  tblRateTableRate vr1
				inner join
				(
					select
						RateTableId,
						RateID,
						EffectiveDate
					FROM tblRateTableRate
					WHERE RateTableId = p_RateTableId
						AND EffectiveDate =   @EffectiveDate
					order by EffectiveDate desc
				) tmpvr
				on
					vr1.RateTableId = tmpvr.RateTableId
					AND vr1.RateID  	=        	tmpvr.RateID
					AND vr1.EffectiveDate 	< tmpvr.EffectiveDate
				SET
					vr1.EndDate = @EffectiveDate
				where
					vr1.RateTableId = p_RateTableId
					--	AND vr1.EffectiveDate < @EffectiveDate
					AND vr1.EndDate is null;


				SET v_pointer_ = v_pointer_ + 1;

			END WHILE;

		END IF;

	END IF;

	INSERT INTO tmp_JobLog_ (Message) 	 	SELECT CONCAT(v_AffectedRecords_ , ' Records Uploaded ' );

	-- Update previous rate before archive
	call prc_RateTableRateUpdatePreviousRate(p_RateTableId,'');

	-- archive rates which has EndDate <= today
	call prc_ArchiveOldRateTableRate(p_RateTableId,p_UserName);


	DELETE  FROM tblTempRateTableRate WHERE  ProcessId = p_processId;
	DELETE  FROM tblRateTableRateChangeLog WHERE ProcessID = p_processId;
	SELECT * FROM tmp_JobLog_;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;







DROP PROCEDURE IF EXISTS `prc_ArchiveOldRateTableRate`;
DELIMITER //
CREATE PROCEDURE `prc_ArchiveOldRateTableRate`(
	IN `p_RateTableIds` longtext,
	IN `p_DeletedBy` TEXT
)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	/*1. Move Rates which EndDate <= now() */

	INSERT INTO tblRateTableRateArchive
	SELECT DISTINCT  null , -- Primary Key column
		`RateTableRateID`,
		`RateTableId`,
		`RateId`,
		`Rate`,
		`EffectiveDate`,
		IFNULL(`EndDate`,date(now())) as EndDate,
		`updated_at`,
		now() as `created_at`,
		p_DeletedBy AS `created_by`,
		`ModifiedBy`,
		`Interval1`,
		`IntervalN`,
		`ConnectionFee`,
		concat('Ends Today rates @ ' , now() ) as `Notes`
	FROM tblRateTableRate
	WHERE  FIND_IN_SET(RateTableId,p_RateTableIds) != 0 AND EndDate <= NOW();

	/*
	IF (FOUND_ROWS() > 0) THEN
	select concat(FOUND_ROWS() ," Ends Today rates" ) ;
	END IF;
	*/

	DELETE  vr
	FROM tblRateTableRate vr
	inner join tblRateTableRateArchive vra
		on vr.RateTableRateID = vra.RateTableRateID
	WHERE  FIND_IN_SET(vr.RateTableId,p_RateTableIds) != 0;

	/*  IF (FOUND_ROWS() > 0) THEN
	select concat(FOUND_ROWS() ," sane rate " ) ;
	END IF;
	*/

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;






DROP PROCEDURE IF EXISTS `prc_WSReviewVendorRate`;
DELIMITER //
CREATE PROCEDURE `prc_WSReviewVendorRate`(
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


    -- @TODO: code cleanup
     DECLARE newstringcode INT(11) DEFAULT 0;
     DECLARE v_pointer_ INT;
     DECLARE v_rowCount_ INT;


	  DECLARE v_AccountCurrencyID_ INT;
	  DECLARE v_CompanyCurrencyID_ INT;


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

    CALL  prc_checkDialstringAndDupliacteCode(p_companyId,p_processId,p_dialstringid,p_effectiveImmediately,p_dialcodeSeparator);


	-- archive vendor rate code
--	CALL prc_ArchiveOldVendorRate(p_AccountId,p_TrunkId);


	ALTER TABLE `tmp_TempVendorRate_`	ADD Column `NewRate` decimal(18, 6) ;



    SELECT COUNT(*) AS COUNT INTO newstringcode from tmp_JobLog_;


	   SELECT CurrencyID into v_AccountCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblAccount WHERE AccountID=p_accountId);
	   SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);


	-- update all rate on newrate with currency conversion.
	update tmp_TempVendorRate_
	SET
	NewRate = IF (
                    p_CurrencyID > 0,
                    CASE WHEN p_CurrencyID = v_AccountCurrencyID_
                    THEN
                       Rate
                    WHEN  p_CurrencyID = v_CompanyCurrencyID_
                    THEN
                    (
                        ( Rate  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ and CompanyID = p_companyId ) )
                    )
                    ELSE
                    (
                        (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ AND CompanyID = p_companyId )
                            *
                        (Rate  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))
                    )
                    END ,
                    Rate
                )
   WHERE ProcessID=p_processId;


		-- if no error
    IF newstringcode = 0
    THEN
			-- if rates is not in our database (new rates from file) than insert it into ChangeLog
			INSERT INTO tblVendorRateChangeLog(
				TempVendorRateID,
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
		   	`Action`,
		   	ProcessID,
		   	created_at
			)
			SELECT
				tblTempVendorRate.TempVendorRateID,
				tblVendorRate.VendorRateID,
			   p_accountId AS AccountId,
			   p_trunkId AS TrunkID,
			   tblRate.RateId,
			   tblTempVendorRate.Code,
			   tblTempVendorRate.Description,
			   tblTempVendorRate.Rate,
			  	tblTempVendorRate.EffectiveDate,
				tblTempVendorRate.EndDate ,
			  	IFNULL(tblTempVendorRate.Interval1,tblRate.Interval1 ) as Interval1,		-- take interval from file and update in tblRate if not changed in service
			  	IFNULL(tblTempVendorRate.IntervalN , tblRate.IntervalN ) as IntervalN,
			   tblTempVendorRate.ConnectionFee,
			   'New' AS `Action`,
			   p_processId AS ProcessID,
			   now() AS created_at
			FROM tmp_TempVendorRate_ as tblTempVendorRate
			LEFT JOIN tblRate
			   ON tblTempVendorRate.Code = tblRate.Code AND tblTempVendorRate.CodeDeckId = tblRate.CodeDeckId  AND tblRate.CompanyID = p_companyId
			LEFT JOIN tblVendorRate
				ON tblRate.RateID = tblVendorRate.RateId AND tblVendorRate.AccountId = p_accountId   AND tblVendorRate.TrunkId = p_trunkId
				AND tblVendorRate.EffectiveDate  <= date(now())
		   WHERE tblTempVendorRate.ProcessID=p_processId AND tblVendorRate.VendorRateID IS NULL
              AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');
				 -- AND tblTempVendorRate.EffectiveDate != '0000-00-00 00:00:00';


   		-- loop through effective date
      DROP TEMPORARY TABLE IF EXISTS tmp_EffectiveDates_;
			CREATE TEMPORARY TABLE tmp_EffectiveDates_ (
				EffectiveDate  Date,
				RowID int,
				INDEX (RowID)
			);
      INSERT INTO tmp_EffectiveDates_
      SELECT distinct
        EffectiveDate,
        @row_num := @row_num+1 AS RowID
      FROM tmp_TempVendorRate_
        ,(SELECT @row_num := 0) x
      WHERE  ProcessID = p_processId
     -- AND EffectiveDate <> '0000-00-00 00:00:00'
      group by EffectiveDate
      order by EffectiveDate asc;


    SET v_pointer_ = 1;
		SET v_rowCount_ = ( SELECT COUNT(*) FROM tmp_EffectiveDates_ );

		IF v_rowCount_ > 0 THEN

			WHILE v_pointer_ <= v_rowCount_
			DO

				SET @EffectiveDate = ( SELECT EffectiveDate FROM tmp_EffectiveDates_ WHERE RowID = v_pointer_ );
				SET @row_num = 0;

	         -- update  previous rate with all latest recent entriy of previous effective date

                       INSERT INTO tblVendorRateChangeLog(
                           TempVendorRateID,
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
                           `Action`,
                           ProcessID,
                           created_at
                       )
               			  SELECT
               			  distinct
                       tblTempVendorRate.TempVendorRateID,
                       VendorRate.VendorRateID,
                       p_accountId AS AccountId,
                       p_trunkId AS TrunkID,
                       VendorRate.RateId,
                       tblRate.Code,
                       tblRate.Description,
                       tblTempVendorRate.Rate,
                       tblTempVendorRate.EffectiveDate,
                       tblTempVendorRate.EndDate ,
                       tblTempVendorRate.Interval1,
                       tblTempVendorRate.IntervalN,
                       tblTempVendorRate.ConnectionFee,
                       IF(tblTempVendorRate.NewRate > VendorRate.Rate, 'Increased', IF(tblTempVendorRate.NewRate < VendorRate.Rate, 'Decreased','')) AS `Action`,
                       p_processid AS ProcessID,
                       now() AS created_at
                       FROM
                         (
                         -- get all rates RowID = 1 to remove old to old effective date

                         select distinct tmp.* ,
                         @row_num := IF(@prev_RateId = tmp.RateID AND @prev_EffectiveDate >= tmp.EffectiveDate, (@row_num + 1), 1) AS RowID,
                         @prev_RateId := tmp.RateID,
                         @prev_EffectiveDate := tmp.EffectiveDate
                         FROM
                         (


                         				select distinct vr1.*
	                         	     from tblVendorRate vr1
			                          LEFT outer join tblVendorRate vr2
												on vr1.AccountID = vr2.AccountID
												and vr1.TrunkID = vr2.TrunkID
												and vr1.RateID = vr2.RateID
												AND vr2.EffectiveDate  = @EffectiveDate
			                          where
			                          vr1.AccountID = p_accountId AND vr1.TrunkID = p_trunkId
			                          and vr1.EffectiveDate <= COALESCE(vr2.EffectiveDate,@EffectiveDate)   -- <= because if same day rate change need to log
			                          order by vr1.RateID desc ,vr1.EffectiveDate desc


                         ) tmp ,
								 ( SELECT @row_num := 0 , @prev_RateId := 0 , @prev_EffectiveDate := '' ) x
								  order by RateID desc , EffectiveDate desc


                         ) VendorRate
                      JOIN tblRate
                         ON tblRate.CompanyID = p_companyId
                         AND tblRate.RateID = VendorRate.RateId
                      JOIN tmp_TempVendorRate_ tblTempVendorRate
                         ON tblTempVendorRate.Code = tblRate.Code
								 	AND tblTempVendorRate.ProcessID=p_processId
                         --	AND  tblTempVendorRate.EffectiveDate <> '0000-00-00 00:00:00'
								 AND  VendorRate.EffectiveDate <= tblTempVendorRate.EffectiveDate -- <= because if same day rate change need to log
               				  AND tblTempVendorRate.EffectiveDate =  @EffectiveDate

               				   AND VendorRate.RowID = 1

                       WHERE
                         VendorRate.AccountId = p_accountId
                         AND VendorRate.TrunkId = p_trunkId
                         -- AND tblTempVendorRate.EffectiveDate <> '0000-00-00 00:00:00'
                         AND tblTempVendorRate.Code IS NOT NULL
                         AND tblTempVendorRate.ProcessID=p_processId
                         AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

				SET v_pointer_ = v_pointer_ + 1;


			END WHILE;

		END IF;


    		IF p_list_option = 1 -- p_list_option = 1 : "Completed List", p_list_option = 2 : "Partial List"
    		THEN

    			-- get rates which is not in file and insert it into ChangeLog
         	          INSERT INTO tblVendorRateChangeLog(
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
			   	`Action`,
			   	ProcessID,
			   	created_at
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
                    tblVendorRate.EndDate ,
                    tblVendorRate.Interval1,
                    tblVendorRate.IntervalN,
                    tblVendorRate.ConnectionFee,
                    'Deleted' AS `Action`,
			   			p_processId AS ProcessID,
                    now() AS deleted_at
                    FROM tblVendorRate
                    JOIN tblRate
                    ON tblRate.RateID = tblVendorRate.RateId AND tblRate.CompanyID = p_companyId
                    LEFT JOIN tmp_TempVendorRate_ as tblTempVendorRate
                    ON tblTempVendorRate.Code = tblRate.Code

						  AND tblTempVendorRate.ProcessID=p_processId
						  AND (
						  			-- normal condition
								  ( tblTempVendorRate.EndDate is null AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block') )
							  	OR
							  		-- skip records just to avoid duplicate records in tblVendorRateChangeLog tabke - when EndDate is given with delete
								  ( tblTempVendorRate.EndDate is not null AND tblTempVendorRate.Change IN ('Delete', 'R', 'D', 'Blocked','Block') )
							  )
                    WHERE tblVendorRate.AccountId = p_accountId
                    AND tblVendorRate.TrunkId = p_trunkId
                    AND ( tblVendorRate.EndDate is null OR tblVendorRate.EndDate <= date(now()) )
                    AND tblTempVendorRate.Code IS NULL
                    ORDER BY VendorRateID ASC;

    		END IF;


            INSERT INTO tblVendorRateChangeLog(
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
			   	`Action`,
			   	ProcessID,
			   	created_at
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
                    IFNULL(tblTempVendorRate.EndDate,tblVendorRate.EndDate) as  EndDate ,
                    tblVendorRate.Interval1,
                    tblVendorRate.IntervalN,
                    tblVendorRate.ConnectionFee,
                    'Deleted' AS `Action`,
			   			p_processId AS ProcessID,
                    now() AS deleted_at
                    FROM tblVendorRate
                    JOIN tblRate
                    ON tblRate.RateID = tblVendorRate.RateId AND tblRate.CompanyID = p_companyId
                    LEFT JOIN tmp_TempVendorRate_ as tblTempVendorRate
	                    ON tblRate.Code = tblTempVendorRate.Code
							  AND tblTempVendorRate.Change IN ('Delete', 'R', 'D', 'Blocked','Block')
							   AND tblTempVendorRate.ProcessID=p_processId
                    -- AND tblTempVendorRate.EndDate <= date(now())
         	           -- AND tblTempVendorRate.ProcessID=p_processId
                    WHERE tblVendorRate.AccountId = p_accountId
                    AND tblVendorRate.TrunkId = p_trunkId
               	     -- AND tblVendorRate.EndDate <= date(now())
            	        AND tblTempVendorRate.Code IS NOT NULL
                    ORDER BY VendorRateID ASC;



    END IF;

    SELECT * FROM tmp_JobLog_;

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
	IN `p_list_option` INT,
	IN `p_UserName` TEXT
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

            /*UPDATE tmp_TempVendorRate_ as tblTempVendorRate
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

 				SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();*/

            UPDATE tmp_TempVendorRate_ as tblTempVendorRate
            JOIN tblRate
                ON tblRate.Code = tblTempVendorRate.Code
                    AND tblRate.CompanyID = p_companyId
                    AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
            JOIN tblVendorRate
                ON tblVendorRate.RateId = tblRate.RateId
                    AND tblVendorRate.AccountId = p_accountId
                    AND tblVendorRate.TrunkId = p_trunkId
				    SET tblVendorRate.EndDate = NOW()
            WHERE tblTempVendorRate.Rate <> tblVendorRate.Rate
                AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
                AND DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempVendorRate.EffectiveDate, '%Y-%m-%d');

				-- archive rates which has EndDate <= today
				call prc_ArchiveOldVendorRate(p_accountId,p_trunkId,p_UserName);


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
	call prc_ArchiveOldVendorRate(p_accountId,p_trunkId,p_UserName);


 	 SELECT * FROM tmp_JobLog_;
   DELETE  FROM tblTempVendorRate WHERE  ProcessId = p_processId;
   DELETE  FROM tblVendorRateChangeLog WHERE ProcessID = p_processId;

	 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;










DROP PROCEDURE IF EXISTS `prc_GetRateTableRatesArchiveGrid`;
DELIMITER //
CREATE PROCEDURE `prc_GetRateTableRatesArchiveGrid`(
	IN `p_CompanyID` INT,
	IN `p_RateTableID` INT,
	IN `p_Codes` LONGTEXT,
	IN `p_View` INT
)
BEGIN

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
   CREATE TEMPORARY TABLE tmp_RateTableRate_ (
        Code VARCHAR(50),
        Description VARCHAR(200),
        Interval1 INT,
        IntervalN INT,
		  ConnectionFee VARCHAR(50),
        PreviousRate DECIMAL(18, 6),
        Rate DECIMAL(18, 6),
        EffectiveDate DATE,
        EndDate DATE,
        updated_at DATETIME,
        ModifiedBy VARCHAR(50)
   );

	IF p_View = 1
	THEN
		INSERT INTO tmp_RateTableRate_ (
			Code,
		  	Description,
		  	Interval1,
		  	IntervalN,
		  	ConnectionFee,
--		  	PreviousRate,
		  	Rate,
		  	EffectiveDate,
		  	EndDate,
		  	updated_at,
		  	ModifiedBy
		)
	   SELECT
			r.Code,
			r.Description,
			CASE WHEN vra.Interval1 IS NOT NULL THEN vra.Interval1 ELSE r.Interval1 END AS Interval1,
			CASE WHEN vra.IntervalN IS NOT NULL THEN vra.IntervalN ELSE r.IntervalN END AS IntervalN,
			IFNULL(vra.ConnectionFee,'') AS ConnectionFee,
			vra.Rate,
			vra.EffectiveDate,
			IFNULL(vra.EndDate,'') AS EndDate,
			IFNULL(vra.created_at,'') AS ModifiedDate,
			IFNULL(vra.created_by,'') AS ModifiedBy
		FROM
			tblRateTableRateArchive vra
		JOIN
			tblRate r ON r.RateID=vra.RateId
		WHERE
			r.CompanyID = p_CompanyID AND
			vra.RateTableId = p_RateTableID AND
			FIND_IN_SET (r.Code, p_Codes) != 0
		ORDER BY
			vra.EffectiveDate DESC, vra.created_at DESC;
	ELSE
		INSERT INTO tmp_RateTableRate_ (
			Code,
		  	Description,
		  	Interval1,
		  	IntervalN,
		  	ConnectionFee,
--		  	PreviousRate,
		  	Rate,
		  	EffectiveDate,
		  	EndDate,
		  	updated_at,
		  	ModifiedBy
		)
	   SELECT
			GROUP_CONCAT(r.Code),
			r.Description,
			CASE WHEN vra.Interval1 IS NOT NULL THEN vra.Interval1 ELSE r.Interval1 END AS Interval1,
			CASE WHEN vra.IntervalN IS NOT NULL THEN vra.IntervalN ELSE r.IntervalN END AS IntervalN,
			IFNULL(vra.ConnectionFee,'') AS ConnectionFee,
			vra.Rate,
			vra.EffectiveDate,
			IFNULL(vra.EndDate,'') AS EndDate,
			IFNULL(MAX(vra.created_at),'') AS ModifiedDate,
			IFNULL(MAX(vra.created_by),'') AS ModifiedBy
		FROM
			tblRateTableRateArchive vra
		JOIN
			tblRate r ON r.RateID=vra.RateId
		WHERE
			r.CompanyID = p_CompanyID AND
			vra.RateTableId = p_RateTableID AND
			FIND_IN_SET (r.Code, p_Codes) != 0
		GROUP BY
			Description, Interval1, Intervaln, ConnectionFee, Rate, EffectiveDate, EndDate
		ORDER BY
			vra.EffectiveDate DESC, MAX(vra.created_at) DESC;
	END IF;

	SELECT
		Code,
		Description,
		Interval1,
		IntervalN,
		ConnectionFee,
		Rate,
		EffectiveDate,
		EndDate,
		IFNULL(updated_at,'') AS ModifiedDate,
		IFNULL(ModifiedBy,'') AS ModifiedBy
	FROM tmp_RateTableRate_;
END//
DELIMITER ;











DROP PROCEDURE IF EXISTS `prc_GetRateTableRate`;
DELIMITER //
CREATE PROCEDURE `prc_GetRateTableRate`(
	IN `p_companyid` INT,
	IN `p_RateTableId` INT,
	IN `p_trunkID` INT,
	IN `p_contryID` INT,
	IN `p_code` VARCHAR(50),
	IN `p_description` VARCHAR(50),
	IN `p_effective` VARCHAR(50),
	IN `p_view` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
BEGIN
	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
--	SET sql_mode = '';
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
   CREATE TEMPORARY TABLE tmp_RateTableRate_ (
        ID INT,
        Code VARCHAR(50),
        Description VARCHAR(200),
        Interval1 INT,
        IntervalN INT,
		  ConnectionFee DECIMAL(18, 6),
        PreviousRate DECIMAL(18, 6),
        Rate DECIMAL(18, 6),
        EffectiveDate DATE,
        EndDate DATE,
        updated_at DATETIME,
        ModifiedBy VARCHAR(50),
        RateTableRateID INT,
        RateID INT,
        INDEX tmp_RateTableRate_RateID (`RateID`)
    );



    INSERT INTO tmp_RateTableRate_
    SELECT
        RateTableRateID AS ID,
        Code,
        Description,
        ifnull(tblRateTableRate.Interval1,1) as Interval1,
        ifnull(tblRateTableRate.IntervalN,1) as IntervalN,
		  tblRateTableRate.ConnectionFee,
        IFNULL(tblRateTableRate.PreviousRate, 0) as PreviousRate,
        IFNULL(tblRateTableRate.Rate, 0) as Rate,
        IFNULL(tblRateTableRate.EffectiveDate, NOW()) as EffectiveDate,
        tblRateTableRate.EndDate,
        tblRateTableRate.updated_at,
        tblRateTableRate.ModifiedBy,
        RateTableRateID,
        tblRate.RateID
    FROM tblRate
    LEFT JOIN tblRateTableRate
        ON tblRateTableRate.RateID = tblRate.RateID
        AND tblRateTableRate.RateTableId = p_RateTableId
    INNER JOIN tblRateTable
        ON tblRateTable.RateTableId = tblRateTableRate.RateTableId
    WHERE		(tblRate.CompanyID = p_companyid)
		AND (p_contryID is null OR CountryID = p_contryID)
		AND (p_code is null OR Code LIKE REPLACE(p_code, '*', '%'))
		AND (p_description is null OR Description LIKE REPLACE(p_description, '*', '%'))
		AND TrunkID = p_trunkID
		AND (
			p_effective = 'All'
		OR (p_effective = 'Now' AND EffectiveDate <= NOW() )
		OR (p_effective = 'Future' AND EffectiveDate > NOW())
			);

	  IF p_effective = 'Now'
		THEN
		   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate4_ as (select * from tmp_RateTableRate_);
         DELETE n1 FROM tmp_RateTableRate_ n1, tmp_RateTableRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
		   AND  n1.RateID = n2.RateID;
		END IF;

    IF p_isExport = 0
    THEN

		IF p_view = 1
		THEN
       	SELECT * FROM tmp_RateTableRate_
					ORDER BY CASE
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
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PreviousRateDESC') THEN PreviousRate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PreviousRateASC') THEN PreviousRate
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateDESC') THEN Rate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateASC') THEN Rate
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Interval1DESC') THEN Interval1
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Interval1ASC') THEN Interval1
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNDESC') THEN Interval1
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNASC') THEN Interval1
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
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byDESC') THEN ModifiedBy
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byASC') THEN ModifiedBy
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableRateIDDESC') THEN RateTableRateID
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableRateIDASC') THEN RateTableRateID
                END ASC,
				    CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeDESC') THEN ConnectionFee
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeASC') THEN ConnectionFee
                END ASC
			LIMIT p_RowspPage OFFSET v_OffSet_;

			SELECT
            COUNT(RateID) AS totalcount
        	FROM tmp_RateTableRate_;

		ELSE
			SELECT group_concat(ID) AS ID, group_concat(Code) AS Code,ANY_VALUE(Description),ANY_VALUE(Interval1),ANY_VALUE(Intervaln),ANY_VALUE(ConnectionFee),ANY_VALUE(PreviousRate),ANY_VALUE(Rate),ANY_VALUE(EffectiveDate),ANY_VALUE(EndDate),MAX(updated_at) AS updated_at,MAX(ModifiedBy) AS ModifiedBy,group_concat(ID) AS RateTableRateID,group_concat(RateID) AS RateID FROM tmp_RateTableRate_
					GROUP BY Description, Interval1, Intervaln, ConnectionFee, Rate, EffectiveDate
					ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN ANY_VALUE(Description)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN ANY_VALUE(Description)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PreviousRateDESC') THEN ANY_VALUE(PreviousRate)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PreviousRateASC') THEN ANY_VALUE(PreviousRate)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateDESC') THEN ANY_VALUE(Rate)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateASC') THEN ANY_VALUE(Rate)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Interval1DESC') THEN ANY_VALUE(Interval1)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Interval1ASC') THEN ANY_VALUE(Interval1)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNDESC') THEN ANY_VALUE(Interval1)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNASC') THEN ANY_VALUE(Interval1)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateDESC') THEN ANY_VALUE(EffectiveDate)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateASC') THEN ANY_VALUE(EffectiveDate)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateDESC') THEN ANY_VALUE(EndDate)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateASC') THEN ANY_VALUE(EndDate)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN ANY_VALUE(updated_at)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN ANY_VALUE(updated_at)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byDESC') THEN ANY_VALUE(ModifiedBy)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byASC') THEN ANY_VALUE(ModifiedBy)
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableRateIDDESC') THEN ANY_VALUE(RateTableRateID)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableRateIDASC') THEN ANY_VALUE(RateTableRateID)
                END ASC,
				    CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeDESC') THEN ANY_VALUE(ConnectionFee)
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeASC') THEN ANY_VALUE(ConnectionFee)
                END ASC
			LIMIT p_RowspPage OFFSET v_OffSet_;

			SELECT COUNT(*) AS `totalcount`
			FROM (
				SELECT
	            Description
	        	FROM tmp_RateTableRate_
					GROUP BY Description, Interval1, Intervaln, ConnectionFee, Rate, EffectiveDate
			) totalcount;


		END IF;

    END IF;

    IF p_isExport = 1
    THEN

        SELECT
            Code,
            Description,
            Interval1,
            IntervalN,
            ConnectionFee,
            PreviousRate,
            Rate,
            EffectiveDate,
            updated_at,
            ModifiedBy

        FROM   tmp_RateTableRate_;


    END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;










DROP PROCEDURE IF EXISTS `prc_getDiscontinuedRateTableRateGrid`;
DELIMITER //
CREATE PROCEDURE `prc_getDiscontinuedRateTableRateGrid`(
	IN `p_CompanyID` INT,
	IN `p_RateTableID` INT,
	IN `p_CountryID` INT,
	IN `p_Code` VARCHAR(50),
	IN `p_Description` VARCHAR(50),
	IN `p_View` INT,
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

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
	CREATE TEMPORARY TABLE tmp_RateTableRate_ (
		RateTableRateID INT,
		Code VARCHAR(50),
		Description VARCHAR(200),
		Interval1 INT,
		IntervalN INT,
		ConnectionFee VARCHAR(50),
		PreviousRate DECIMAL(18, 6),
		Rate DECIMAL(18, 6),
		EffectiveDate DATE,
		EndDate DATE,
		updated_at DATETIME,
		updated_by VARCHAR(50),
		INDEX tmp_RateTableRate_RateID (`Code`)
	);

	INSERT INTO tmp_RateTableRate_
	SELECT
		vra.RateTableRateID,
		r.Code,
		r.Description,
		CASE WHEN vra.Interval1 IS NOT NULL THEN vra.Interval1 ELSE r.Interval1 END AS Interval1,
		CASE WHEN vra.IntervalN IS NOT NULL THEN vra.IntervalN ELSE r.IntervalN END AS IntervalN,
		'' AS ConnectionFee,
		null AS PreviousRate,
		vra.Rate,
		vra.EffectiveDate,
		vra.EndDate,
		vra.created_at AS updated_at,
		vra.created_by AS updated_by
	FROM
		tblRateTableRateArchive vra
	JOIN
		tblRate r ON r.RateID=vra.RateId
	LEFT JOIN
		tblRateTableRate vr ON vr.RateTableId = vra.RateTableId AND vr.RateId = vra.RateId
	WHERE
		r.CompanyID = p_CompanyID AND
		vra.RateTableId = p_RateTableID AND
		(p_CountryID IS NULL OR r.CountryID = p_CountryID) AND
		(p_code IS NULL OR r.Code LIKE REPLACE(p_code, '*', '%')) AND
		(p_description IS NULL OR r.Description LIKE REPLACE(p_description, '*', '%')) AND
		vr.RateTableRateID is NULL;

	IF p_isExport = 0
	THEN
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate2_ as (select * from tmp_RateTableRate_);
		DELETE
			n1
		FROM
			tmp_RateTableRate_ n1, tmp_RateTableRate2_ n2
		WHERE
			n1.Code = n2.Code AND n1.RateTableRateID < n2.RateTableRateID;

		IF p_view = 1
		THEN
			SELECT
				RateTableRateID,
				Code,
				Description,
				Interval1,
				IntervalN,
				ConnectionFee,
				PreviousRate,
				Rate,
				EffectiveDate,
				EndDate,
				updated_at,
				updated_by
			FROM
				tmp_RateTableRate_
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
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableRateIDDESC') THEN RateTableRateID
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableRateIDASC') THEN RateTableRateID
				END ASC
			LIMIT
				p_RowspPage
			OFFSET
				v_OffSet_;

			SELECT
				COUNT(code) AS totalcount
			FROM tmp_RateTableRate_;

		ELSE

			SELECT
				group_concat(RateTableRateID) AS RateTableRateID,
				group_concat(Code),
				Description,
				ConnectionFee,
				Interval1,
				IntervalN,
				ANY_VALUE(PreviousRate),
				Rate,
				EffectiveDate,
				EndDate,
				MAX(updated_at),
				MAX(updated_by)
			FROM
				tmp_RateTableRate_
			GROUP BY
				Description, Interval1, Intervaln, ConnectionFee, Rate, EffectiveDate, EndDate
			ORDER BY
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN ANY_VALUE(Description)
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN ANY_VALUE(Description)
				END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateDESC') THEN ANY_VALUE(Rate)
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateASC') THEN ANY_VALUE(Rate)
				END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeDESC') THEN ANY_VALUE(ConnectionFee)
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeASC') THEN ANY_VALUE(ConnectionFee)
				END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Interval1DESC') THEN ANY_VALUE(Interval1)
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Interval1ASC') THEN ANY_VALUE(Interval1)
				END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNDESC') THEN ANY_VALUE(IntervalN)
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNASC') THEN ANY_VALUE(IntervalN)
				END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateDESC') THEN ANY_VALUE(EffectiveDate)
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateASC') THEN ANY_VALUE(EffectiveDate)
				END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateDESC') THEN ANY_VALUE(EndDate)
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateASC') THEN ANY_VALUE(EndDate)
				END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN ANY_VALUE(updated_at)
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN ANY_VALUE(updated_at)
				END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byDESC') THEN ANY_VALUE(updated_by)
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byASC') THEN ANY_VALUE(updated_by)
				END ASC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableRateIDDESC') THEN ANY_VALUE(RateTableRateID)
				END DESC,
				CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableRateIDASC') THEN ANY_VALUE(RateTableRateID)
				END ASC
			LIMIT
				p_RowspPage
			OFFSET
				v_OffSet_;


			SELECT COUNT(*) AS `totalcount`
			FROM (
				SELECT
	            Description
	        	FROM tmp_RateTableRate_
					GROUP BY Description, Interval1, Intervaln, ConnectionFee, Rate, EffectiveDate, EndDate
			) totalcount;

		END IF;

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
		FROM tmp_RateTableRate_;
	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;










DROP PROCEDURE IF EXISTS `prc_GetVendorRatesArchiveGrid`;
DELIMITER //
CREATE PROCEDURE `prc_GetVendorRatesArchiveGrid`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_Codes` LONGTEXT
)
BEGIN
	SELECT
	--	vra.VendorRateArchiveID,
	--	vra.VendorRateID,
	--	vra.AccountID,
		r.Code,
		r.Description,
		IFNULL(vra.ConnectionFee,'') AS ConnectionFee,
		CASE WHEN vra.Interval1 IS NOT NULL THEN vra.Interval1 ELSE r.Interval1 END AS Interval1,
		CASE WHEN vra.IntervalN IS NOT NULL THEN vra.IntervalN ELSE r.IntervalN END AS IntervalN,
		vra.Rate,
		vra.EffectiveDate,
		IFNULL(vra.EndDate,'') AS EndDate,
		IFNULL(vra.created_at,'') AS ModifiedDate,
		IFNULL(vra.created_by,'') AS ModifiedBy
	FROM
		tblVendorRateArchive vra
	JOIN
		tblRate r ON r.RateID=vra.RateId
	WHERE
		r.CompanyID = p_CompanyID AND
		vra.AccountId = p_AccountID AND
		FIND_IN_SET (r.Code, p_Codes) != 0
	ORDER BY
		vra.EffectiveDate DESC, vra.created_at DESC;
END//
DELIMITER ;