CREATE DEFINER=`neon-user`@`192.168.1.25` PROCEDURE `prc_WSReviewRateTableRate`(
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
END