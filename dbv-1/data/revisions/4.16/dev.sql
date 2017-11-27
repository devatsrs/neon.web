Use Ratemanagement3;

ALTER TABLE `tblVendorRateDiscontinued`
	DROP INDEX `UK_tblVendorRateDiscontinued`;

ALTER TABLE `tblVendorRateDiscontinued`
	ADD INDEX `AccountId` (`AccountId`),
	ADD INDEX `TrunkID` (`TrunkID`),
	ADD INDEX `RateId` (`RateId`),
	ADD INDEX `EffectiveDate` (`EffectiveDate`);


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
			`RateNew` decimal(18, 6) ,
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

		SELECT COUNT(*) AS COUNT INTO newstringcode from tmp_JobLog_;


		SELECT CurrencyID into v_AccountCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblAccount WHERE AccountID=p_accountId);
		SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);


		update tmp_TempVendorRate_
		SET
			RateNew = IF (
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
					IFNULL(tblRate.Interval1 , tblTempVendorRate.Interval1) as Interval1,
					IFNULL(tblRate.IntervalN , tblTempVendorRate.IntervalN ) as IntervalN,
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
							IF(tblTempVendorRate.Rate > VendorRate.Rate, 'Increased', IF(tblTempVendorRate.Rate < VendorRate.Rate, 'Decreased','')) AS `Action`,
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
											vr1.AccountID = p_accountId AND vr1.TrunkID = 1
											and vr1.EffectiveDate < COALESCE(vr2.EffectiveDate,@EffectiveDate)
										order by vr1.RateID desc ,vr1.EffectiveDate desc



										/*select distinct vr1.*
                    from tblVendorRate vr1
                    inner join tblVendorRate vr2
                    on vr1.AccountID = vr2.AccountID  and vr1.TrunkID = vr2.TrunkID and vr1.RateID = vr2.RateID
                    where
                    vr1.AccountID = p_accountId AND vr1.TrunkID = p_trunkId
                    and vr1.EffectiveDate < vr2.EffectiveDate   AND vr2.EffectiveDate  = @EffectiveDate
                    order by vr1.RateID desc ,vr1.EffectiveDate desc
                    */

									) tmp


							) VendorRate
							JOIN tblRate
								ON tblRate.CompanyID = p_companyId
									 AND tblRate.RateID = VendorRate.RateId
							JOIN tmp_TempVendorRate_ tblTempVendorRate
								ON tblTempVendorRate.Code = tblRate.Code
									 AND tblTempVendorRate.ProcessID=p_processId
									 --	AND  tblTempVendorRate.EffectiveDate <> '0000-00-00 00:00:00'
									 AND  VendorRate.EffectiveDate < tblTempVendorRate.EffectiveDate
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
								 AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
								 AND tblTempVendorRate.ProcessID=p_processId
					WHERE tblVendorRate.AccountId = p_accountId
								AND tblVendorRate.TrunkId = p_trunkId
								AND ( tblVendorRate.EndDate is null OR tblVendorRate.EndDate <= date(now()) )
								AND tblTempVendorRate.Code IS NULL
								AND tblTempVendorRate.ProcessID=p_processId

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
						ON tblRate.Code = tblTempVendorRate.Code
							 AND tblTempVendorRate.Change IN ('Delete', 'R', 'D', 'Blocked','Block')
							 AND tblTempVendorRate.ProcessID=p_processId
				-- AND tblTempVendorRate.EndDate <= date(now())
				WHERE tblVendorRate.AccountId = p_accountId
							AND tblVendorRate.TrunkId = p_trunkId
							-- AND tblVendorRate.EndDate <= date(now())
							AND tblTempVendorRate.Code IS NOT NULL
							AND tblTempVendorRate.ProcessID=p_processId
				ORDER BY VendorRateID ASC;



		END IF;

		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	END//
DELIMITER ;



-- --------------------------------------------------------
-- Host:                         192.168.1.106
-- Server version:               5.7.20-log - MySQL Community Server (GPL)
-- Server OS:                    Linux
-- HeidiSQL Version:             9.4.0.5125
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for NeonRMDev
CREATE DATABASE IF NOT EXISTS `NeonRMDev` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `NeonRMDev`;

-- Dumping structure for procedure NeonRMDev.prc_checkDialstringAndDupliacteCode
DROP PROCEDURE IF EXISTS `prc_checkDialstringAndDupliacteCode`;
DELIMITER //
CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_checkDialstringAndDupliacteCode`(
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
						`DialStringPrefix` varchar(500)
					);



		CALL prc_SplitVendorRate(p_processId,p_dialcodeSeparator);

		DROP TEMPORARY TABLE IF EXISTS tmp_split_VendorRate_2;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_split_VendorRate_2 as (SELECT * FROM tmp_split_VendorRate_);


  -- updated in v4.16
	  DELETE n1 FROM tmp_split_VendorRate_ n1, tmp_split_VendorRate_2 n2
    WHERE n1.EffectiveDate <= now() AND n1.EffectiveDate < n2.EffectiveDate
	 	AND n1.CodeDeckId = n2.CodeDeckId
		AND  n1.Code = n2.Code
		AND  n1.ProcessId = n2.ProcessId
 		AND  n1.ProcessId = p_processId;

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


					DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateDialString_2;
					CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRateDialString_2 as (SELECT * FROM tmp_VendorRateDialString_);

					DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateDialString_3;
					CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRateDialString_3 as (
					 SELECT vrs1.* from tmp_VendorRateDialString_2 vrs1
					 LEFT JOIN tmp_VendorRateDialString_ vrs2 ON vrs1.Code=vrs2.Code AND vrs1.CodeDeckId=vrs2.CodeDeckId AND vrs1.Description=vrs2.Description AND vrs1.EffectiveDate=vrs2.EffectiveDate AND vrs1.DialStringPrefix != vrs2.DialStringPrefix
					 WHERE ((vrs1.DialStringPrefix ='' AND vrs2.Code IS NULL) OR (vrs1.DialStringPrefix!='' AND vrs2.Code IS NOT NULL))
					);


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

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;



-- --------------------------------------------------------
-- Host:                         192.168.1.106
-- Server version:               5.7.20-log - MySQL Community Server (GPL)
-- Server OS:                    Linux
-- HeidiSQL Version:             9.4.0.5125
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for NeonRMDev
CREATE DATABASE IF NOT EXISTS `NeonRMDev` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `NeonRMDev`;

-- Dumping structure for procedure NeonRMDev.prc_InsertDiscontinuedVendorRate
DROP PROCEDURE IF EXISTS `prc_InsertDiscontinuedVendorRate`;
DELIMITER //
CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_InsertDiscontinuedVendorRate`(
	IN `p_AccountId` INT,
	IN `p_TrunkId` INT


)
BEGIN
	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;






	 	INSERT INTO tblVendorRateDiscontinued(
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
		SELECT DISTINCT
			   	VendorRateID,
			   	AccountId,
			   	TrunkID,
					RateId,
			   	Code,
			   	Description,
			   	Rate,
			   	EffectiveDate,
			   	IFNULL(EndDate,NOW()),
			   	Interval1,
			   	IntervalN,
			   	ConnectionFee,
			   	deleted_at
		FROM tmp_Delete_VendorRate
			ORDER BY VendorRateID ASC
			;



		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateDiscontinued_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRateDiscontinued_ (PRIMARY KEY (DiscontinuedID), INDEX tmp_UK_tblVendorRateDiscontinued (AccountId, RateId)) as (select * from tblVendorRateDiscontinued);

		-- delete from duplicate discontinue rates
		  DELETE n1 FROM tblVendorRateDiscontinued n1,
		  	tmp_VendorRateDiscontinued_ n2 WHERE n1.DiscontinuedID < n2.DiscontinuedID
			AND  n1.AccountId = p_AccountId
			AND  n1.TrunkID = n2.TrunkID
			AND  n1.RateId = n2.RateId
			AND  n1.EffectiveDate = n2.EffectiveDate;



		-- v4.16 delete only when EndDate is <= now()


		DELETE tblVendorRate
			FROM tblVendorRate
				INNER JOIN(	SELECT dv.VendorRateID FROM tmp_Delete_VendorRate dv WHERE (EndDate is not null AND EndDate <= date(now()) ) ) tmdv
					ON tmdv.VendorRateID = tblVendorRate.VendorRateID
			WHERE tblVendorRate.AccountId = p_AccountId;


		CALL prc_ArchiveOldVendorRate(p_AccountId,p_TrunkId);

   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ ;
END//
DELIMITER ;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;


-- --------------------------------------------------------
-- Host:                         192.168.1.106
-- Server version:               5.7.20-log - MySQL Community Server (GPL)
-- Server OS:                    Linux
-- HeidiSQL Version:             9.4.0.5125
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for NeonRMDev
CREATE DATABASE IF NOT EXISTS `NeonRMDev` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `NeonRMDev`;

-- Dumping structure for procedure NeonRMDev.prc_WSProcessVendorRate
DROP PROCEDURE IF EXISTS `prc_WSProcessVendorRate`;
DELIMITER //
CREATE DEFINER=`neon-user`@`%` PROCEDURE `prc_WSProcessVendorRate`(
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

		CALL  prc_checkDialstringAndDupliacteCode(p_companyId,p_processId,p_dialstringid,p_effectiveImmediately,p_dialcodeSeparator);

		SELECT COUNT(*) AS COUNT INTO newstringcode from tmp_JobLog_;

		-- LEAVE ThisSP;

		IF newstringcode = 0
		THEN


			IF  p_replaceAllRates = 1
			THEN

				/*
     DELETE FROM tblVendorRate
        WHERE AccountId = p_accountId
            AND TrunkID = p_trunkId;
        */
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
						VendorRateID,
						AccountID,
						TrunkID,
						tblVendorRate.RateId,
						tblRate.Code,
						tblRate.Description,
						tblVendorRate.Rate,
						tblVendorRate.EffectiveDate,
						date(now())  as EndDate ,
						tblVendorRate.Interval1,
						tblVendorRate.IntervalN,
						tblVendorRate.ConnectionFee,
						now() AS deleted_at

					FROM tblVendorRate
						JOIN tblRate  ON tblRate.RateID = tblVendorRate.RateId AND tblRate.CompanyID = p_companyId
					WHERE AccountId = p_accountId
								AND TrunkID = p_trunkId;

				INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' Records Removed.   \n\r ' );

			END IF;


			IF p_list_option = 1    -- v4.16 p_list_option = 1 : "Completed List", p_list_option = 2 : "Partial List"
			THEN

				-- if review
				IF (SELECT count(*) FROM tblVendorRateChangeLog WHERE ProcessID = p_processId ) > 0 THEN

					-- v4.16 update end date given from tblVendorRateChangeLog for deleted rates.
					UPDATE
							tblVendorRate vr
							INNER JOIN tblVendorRateChangeLog  vrcl
								on vrcl.VendorRateID = vr.VendorRateID
					SET vr.EndDate = IFNULL(vrcl.EndDate,date(now()))
					WHERE vrcl.ProcessID = p_processId
								AND vrcl.`Action`  ='Deleted';

					INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Updated End Date of Deleted Records. \n\r ' );

				END IF;


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
					WHERE tblVendorRate.AccountId = p_accountId
								AND tblVendorRate.TrunkId = p_trunkId
								AND tblTempVendorRate.Code IS NULL
								AND ( tblVendorRate.EndDate is NULL OR tblVendorRate.EndDate <= date(now()) )
								AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
								AND  tblTempVendorRate.ProcessId = p_processId
					ORDER BY VendorRateID ASC;

				INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Records marked deleted as Not exists in File\n\r ' );



				CALL prc_InsertDiscontinuedVendorRate(p_accountId,p_trunkId);

			END IF;


			-- v4.16
			IF  p_replaceAllRates = 0
			THEN
				-- insert all future end date to discontinue
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
						VendorRateID,
						AccountID,
						TrunkID,
						tblVendorRate.RateId,
						tblRate.Code,
						tblRate.Description,
						tblVendorRate.Rate,
						tblVendorRate.EffectiveDate,
						tblVendorRate.EndDate ,
						tblVendorRate.Interval1,
						tblVendorRate.IntervalN,
						tblVendorRate.ConnectionFee,
						tblVendorRate.EndDate AS deleted_at
					FROM tblVendorRate
						JOIN tblRate  ON tblRate.RateID = tblVendorRate.RateId AND tblRate.CompanyID = p_companyId
					WHERE AccountId = p_accountId
								AND TrunkID = p_trunkId
								AND EndDate > date(now());

				INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Future End Date Records\n\r ' );

			END IF;

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

				INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - New Code Inserted into Codedeck \n\r ' );
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


			-- delete rates which will be map as deleted
			INSERT INTO tmp_Delete_VendorRate(
				VendorRateID,
				AccountId,
				TrunkID,
				RateId,
				Code,
				Description,
				Rate,
				EffectiveDate,
				EndDate ,
				Interval1,
				IntervalN,
				ConnectionFee,
				deleted_at
			)
				SELECT tblVendorRate.VendorRateID,
					p_accountId AS AccountId,
					p_trunkId AS TrunkID,
					tblVendorRate.RateId,
					tblRate.Code,
					tblRate.Description,
					tblVendorRate.Rate,
					tblVendorRate.EffectiveDate,
					IFNULL(tblTempVendorRate.EndDate,date(now())) ,
					tblVendorRate.Interval1,
					tblVendorRate.IntervalN,
					tblVendorRate.ConnectionFee,
					now() AS deleted_at
				FROM tblVendorRate
					JOIN tblRate
						ON tblRate.RateID = tblVendorRate.RateId
							 AND tblRate.CompanyID = p_companyId
					JOIN tmp_TempVendorRate_ as tblTempVendorRate
						ON tblRate.Code = tblTempVendorRate.Code
				WHERE tblVendorRate.AccountId = p_accountId
							AND tblVendorRate.TrunkId = p_trunkId
							AND tblTempVendorRate.Change IN ('Delete', 'R', 'D', 'Blocked', 'Block');
			-- AND IFNULL(tblTempVendorRate.EndDate,date(now())) <= date(now()) ;
			-- SS delete if end date is today otherwise just update end date in tblVendorRate

			INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Records marked deleted as mapped in File \n\r ' );

			CALL prc_InsertDiscontinuedVendorRate(p_accountId,p_trunkId);

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
				tblVendorRate.IntervalN = tblTempVendorRate.IntervalN,
				tblVendorRate.EndDate = tblTempVendorRate.EndDate
			WHERE tblVendorRate.AccountId = p_accountId
						AND tblVendorRate.TrunkId = p_trunkId ;

			INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Updated Existing Records\n\r ' );

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

			INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Discarded no change records\n\r ' );

			-- SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

			SELECT CurrencyID into v_AccountCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblAccount WHERE AccountID=p_accountId);
			SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);

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

			INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - New Records Inserted.\n\r ' );


		END IF;

		-- INSERT INTO tmp_JobLog_ (Message) 	 	SELECT CONCAT(v_AffectedRecords_ , ' Records Uploaded \n\r ' );

		SELECT * FROM tmp_JobLog_;
		DELETE  FROM tblTempVendorRate WHERE  ProcessId = p_processId;

		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	END//
DELIMITER ;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
