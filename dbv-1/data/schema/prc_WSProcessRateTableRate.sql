CREATE DEFINER=`neon-user`@`192.168.1.25` PROCEDURE `prc_WSProcessRateTableRate`(
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


	-- DELETE  FROM tblTempRateTableRate WHERE  ProcessId = p_processId;
	-- DELETE  FROM tblRateTableRateChangeLog WHERE ProcessID = p_processId;
	SELECT * FROM tmp_JobLog_;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END