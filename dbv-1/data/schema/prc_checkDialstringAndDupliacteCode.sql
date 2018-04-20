CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_checkDialstringAndDupliacteCode`(
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

	/*DELETE n1 FROM tmp_split_VendorRate_ n1
	INNER JOIN
	(
	SELECT MAX(TempVendorRateID) AS TempVendorRateID,EffectiveDate,Code
	FROM tmp_split_VendorRate_2 WHERE ProcessId = p_processId
	GROUP BY Code,EffectiveDate
	HAVING COUNT(*)>1
	)n2
	ON n1.Code = n2.Code
	AND n2.EffectiveDate = n1.EffectiveDate AND n1.TempVendorRateID < n2.TempVendorRateID
	WHERE n1.ProcessId = p_processId;*/

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

		/*INSERT INTO tmp_JobLog_ (Message)
		SELECT DISTINCT CONCAT(Code ,' ', vr.DialStringPrefix , ' No PREFIX FOUND')
		FROM tmp_TempVendorRate_ vr
		LEFT JOIN tmp_DialString_ ds

		ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  ))
		WHERE vr.ProcessId = p_processId
		AND ds.DialStringID IS NULL
		AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');*/

		INSERT INTO tblDialStringCode (DialStringID,DialString,ChargeCode,created_by)
		SELECT DISTINCT p_dialStringId,vr.DialStringPrefix, Code, 'RMService'
		FROM tmp_TempVendorRate_ vr
		LEFT JOIN tmp_DialString_ ds

		ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  ))
		WHERE vr.ProcessId = p_processId
		AND ds.DialStringID IS NULL
		AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
		AND (vr.DialStringPrefix is not null AND vr.DialStringPrefix != '')
		AND (Code is not null AND Code != '');

		TRUNCATE tmp_DialString_;
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


			/*				INSERT INTO tmp_VendorRateDialString_2
			SELECT * FROM tmp_VendorRateDialString_; */

			INSERT INTO tmp_VendorRateDialString_2
			SELECT *  FROM tmp_VendorRateDialString_ where DialStringPrefix!='';

			Delete From tmp_VendorRateDialString_
			Where DialStringPrefix = ''
			And Code IN (Select DialStringPrefix From tmp_VendorRateDialString_2);

			INSERT INTO tmp_VendorRateDialString_3
			SELECT * FROM tmp_VendorRateDialString_;

			/*	

			INSERT INTO tmp_VendorRateDialString_3
			SELECT vrs1.* from tmp_VendorRateDialString_2 vrs1
			LEFT JOIN tmp_VendorRateDialString_ vrs2 ON vrs1.Code=vrs2.Code AND vrs1.CodeDeckId=vrs2.CodeDeckId 
			AND vrs1.EffectiveDate=vrs2.EffectiveDate 
			AND vrs1.DialStringPrefix != vrs2.DialStringPrefix
			WHERE ( (vrs1.DialStringPrefix ='' AND vrs2.Code IS NULL) OR (vrs1.DialStringPrefix!='' AND vrs2.Code IS NOT NULL)); */

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


END