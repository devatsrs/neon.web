USE `RMBilling3`;

CREATE TABLE IF NOT EXISTS `tblClarityPBXPayment` (
  `ClarityPBXPaymentID` int(11) NOT NULL AUTO_INCREMENT,
  `PaymentID` int(11) NOT NULL,
  `CompanyID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `Amount` decimal(18,8) NOT NULL,
  `Recall` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ClarityPBXPaymentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;





DROP PROCEDURE IF EXISTS `prc_updateTempCDRTimeZones`;
DELIMITER //
CREATE PROCEDURE `prc_updateTempCDRTimeZones`(
	IN `p_tbltempusagedetail_name` TEXT
)
ThisSP:BEGIN
	
	DECLARE v_timezones_count_ int;
	DECLARE v_pointer_ int;
	DECLARE v_rowCount_ int;
	DECLARE v_TimezonesID_ int;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_timezones;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_timezones (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		TimezonesID INT(11),
		Title VARCHAR(50),
		FromTime VARCHAR(10),
		ToTime VARCHAR(10),
		DaysOfWeek VARCHAR(100),
		DaysOfMonth VARCHAR(100),
		Months VARCHAR(100),
		ApplyIF VARCHAR(100)
	);
	
	SELECT COUNT(*) INTO v_timezones_count_ FROM Ratemanagement3.tblTimezones WHERE `Status`=1;
	
	IF v_timezones_count_ > 1
	THEN
		
		INSERT INTO tmp_timezones (TimezonesID,Title,FromTime,ToTime,DaysOfWeek,DaysOfMonth,Months,ApplyIF)
		SELECT 
			TimezonesID,Title,FromTime,ToTime,DaysOfWeek,DaysOfMonth,Months,ApplyIF
		FROM
			Ratemanagement3.tblTimezones
		WHERE
			`Status`=1
		ORDER BY
			TimezonesID DESC;
			
		SET v_pointer_ = 1;
		SET v_rowCount_ = (SELECT COUNT(*) FROM tmp_timezones);
	 
		WHILE v_pointer_ <= v_rowCount_
		DO

			SET v_TimezonesID_ = (SELECT TimezonesID FROM tmp_timezones t WHERE t.RowID = v_pointer_);
			
			SET @stm = CONCAT("
				UPDATE
					RMCDR3.",p_tbltempusagedetail_name," temp
				JOIN
					tmp_timezones t ON t.TimezonesID = ",v_TimezonesID_,"
				SET
					temp.TimezonesID = ",v_TimezonesID_,"
				WHERE
				(
					(temp.TimezonesID = '' OR temp.TimezonesID IS NULL)
					AND
					(
						(t.FromTime = '' AND t.ToTime = '') 
						OR
						(
							(
								t.ApplyIF = 'start' AND 
								CAST(DATE_FORMAT(connect_time, '%H:%i:%s') AS TIME) BETWEEN CAST(CONCAT(FromTime,':00') AS TIME) AND CAST(CONCAT(ToTime,':00') AS TIME)
							)
							OR
							(
								t.ApplyIF = 'end' AND
								CAST(DATE_FORMAT(disconnect_time, '%H:%i:%s') AS TIME) BETWEEN CAST(CONCAT(FromTime,':00') AS TIME) AND CAST(CONCAT(ToTime,':00') AS TIME)
							)
							OR
							(
								t.ApplyIF = 'both' AND
								CAST(DATE_FORMAT(connect_time, '%H:%i:%s') AS TIME) BETWEEN CAST(CONCAT(FromTime,':00') AS TIME) AND CAST(CONCAT(ToTime,':00') AS TIME) AND
								CAST(DATE_FORMAT(disconnect_time, '%H:%i:%s') AS TIME) BETWEEN CAST(CONCAT(FromTime,':00') AS TIME) AND CAST(CONCAT(ToTime,':00') AS TIME)
							)
						)
					)				
					AND
					(
						t.Months = ''
						OR
						(
							(
								t.ApplyIF = 'start' AND 
								FIND_IN_SET(MONTH(connect_time), Months) != 0
							)
							OR
							(
								t.ApplyIF = 'end' AND 
								FIND_IN_SET(MONTH(disconnect_time), Months) != 0
							) 
							OR
							(
								t.ApplyIF = 'both' AND 
								FIND_IN_SET(MONTH(connect_time), Months) != 0 AND
								FIND_IN_SET(MONTH(disconnect_time), Months) != 0
							)
						)
					)				
					AND
					(
						t.DaysOfMonth = ''
						OR
						(
							(
								t.ApplyIF = 'start' AND 
								FIND_IN_SET(DAY(connect_time), DaysOfMonth) != 0
							)
							OR
							(
								t.ApplyIF = 'end' AND 
								FIND_IN_SET(DAY(disconnect_time), DaysOfMonth) != 0
							) 
							OR
							(
								t.ApplyIF = 'both' AND 
								FIND_IN_SET(DAY(connect_time), DaysOfMonth) != 0 AND
								FIND_IN_SET(DAY(disconnect_time), DaysOfMonth) != 0
							)
						)
					)				
					AND
					(
						t.DaysOfWeek = ''
						OR
						(
							(
								t.ApplyIF = 'start' AND 
								FIND_IN_SET(DAYOFWEEK(connect_time), DaysOfWeek) != 0
							)
							OR
							(
								t.ApplyIF = 'end' AND 
								FIND_IN_SET(DAYOFWEEK(disconnect_time), DaysOfWeek) != 0
							) 
							OR
							(
								t.ApplyIF = 'both' AND 
								FIND_IN_SET(DAYOFWEEK(connect_time), DaysOfWeek) != 0 AND
								FIND_IN_SET(DAYOFWEEK(disconnect_time), DaysOfWeek) != 0
							)
						)
					)
				)
			");
			
			PREPARE stmt FROM @stm;
			EXECUTE stmt;
			DEALLOCATE PREPARE stmt;
				
			SET v_pointer_ = v_pointer_ + 1;
			
		END WHILE;
		
	ELSE
	
		SET @stm = CONCAT("
			UPDATE RMCDR3.",p_tbltempusagedetail_name," SET TimezonesID=1
		");
		
		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
			
	END IF;
	
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_getClarityPBXExportPayment`;
DELIMITER //
CREATE PROCEDURE `prc_getClarityPBXExportPayment`(
	IN `p_CompanyID` INT,
	IN `p_start_date` DATETIME,
	IN `p_Recall` INT
)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	IF p_Recall=0
	THEN

		SELECT
			ac.AccountID,
			ac.AccountName,
			tblPayment.PaymentID,
			tblPayment.CompanyID,
			tblPayment.Amount,
			tblPayment.Recall
		FROM
			tblPayment
		INNER JOIN
			Ratemanagement3.tblAccount AS ac ON ac.AccountID=tblPayment.AccountID
		LEFT JOIN
			tblClarityPBXPayment AS CPP ON CPP.PaymentID = tblPayment.PaymentID
		WHERE
			CPP.PaymentID IS NULL
			AND PaymentType='Payment In'
			AND ac.CompanyId = p_CompanyID
			AND tblPayment.Status='Approved'
			AND tblPayment.Recall=p_Recall
			AND tblPayment.PaymentDate>p_start_date;

	END IF;

	IF p_Recall=1
	THEN

		SELECT
			ac.AccountID,
			ac.AccountName,
			tblPayment.PaymentID,
			tblPayment.CompanyID,
			tblPayment.Amount,
			tblPayment.Recall
		FROM
			tblPayment
		INNER JOIN
			Ratemanagement3.tblAccount AS ac ON ac.AccountID=tblPayment.AccountID
		LEFT JOIN
			tblClarityPBXPayment AS CPP ON CPP.PaymentID = tblPayment.PaymentID AND CPP.Recall = tblPayment.Recall AND CPP.Recall = p_Recall
		LEFT JOIN
			tblClarityPBXPayment AS CPP2 ON CPP2.PaymentID = tblPayment.PaymentID AND CPP2.Recall = 0
		WHERE
			CPP.PaymentID IS NULL
			AND CPP2.PaymentID IS NOT NULL
			AND PaymentType='Payment In'
			AND ac.CompanyId = p_CompanyID
			AND tblPayment.Status='Approved'
			AND tblPayment.Recall=p_Recall
			AND tblPayment.PaymentDate>p_start_date;

	END IF;


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_UpdateCDRRounding`;
DELIMITER //
CREATE PROCEDURE `prc_UpdateCDRRounding`(
	IN `p_tbltempusagedetail_name` VARCHAR(200),
	IN `p_processId` INT
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	-- trunk based
	SET @stm = CONCAT('
		UPDATE
			RMCDR3.`' , p_tbltempusagedetail_name , '` ud
		LEFT JOIN
			Ratemanagement3.`tblCustomerTrunk` ct ON ct.AccountID = ud.AccountID AND ct.TrunkID = ud.TrunkID AND ct.Status =1
		INNER JOIN
			Ratemanagement3.`tblRateTable` rt ON rt.RateTableID = ct.RateTableID
		SET
			cost = ROUND((CEIL(cost * POWER(10,IFNULL(rt.RoundChargedAmount,6)))/POWER(10,IFNULL(rt.RoundChargedAmount,6))),IFNULL(rt.RoundChargedAmount,6))
		WHERE
			ct.AccountID IS NOT NULL AND
			rt.RoundChargedAmount IS NOT NULL AND
			ud.ProcessID="' , p_processId , '";
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;

	-- service based
	SET @stm = CONCAT('
		UPDATE
			RMCDR3.`' , p_tbltempusagedetail_name , '` ud
		LEFT JOIN
			Ratemanagement3.`tblAccountTariff` t ON t.AccountID = ud.AccountID AND t.ServiceID = ud.ServiceID and ((t.`Type`=1 and ud.is_inbound=0) or (t.`Type`=2 and ud.is_inbound=1))
		INNER JOIN
			Ratemanagement3.`tblRateTable` rt ON rt.RateTableID = t.RateTableID
		SET
			cost = ROUND((CEIL(cost * POWER(10,IFNULL(rt.RoundChargedAmount,6)))/POWER(10,IFNULL(rt.RoundChargedAmount,6))),IFNULL(rt.RoundChargedAmount,6))
		WHERE
			t.AccountID IS NOT NULL AND
			rt.RoundChargedAmount IS NOT NULL AND
			ud.ProcessID="' , p_processId , '" AND
			ud.is_rerated=1;
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_InsertTempReRateCDR`;
DELIMITER //
CREATE PROCEDURE `prc_InsertTempReRateCDR`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AccountID` INT,
	IN `p_ProcessID` VARCHAR(50),
	IN `p_tbltempusagedetail_name` VARCHAR(50),
	IN `p_CDRType` VARCHAR(50),
	IN `p_CLI` VARCHAR(50),
	IN `p_CLD` VARCHAR(50),
	IN `p_zerovaluecost` INT,
	IN `p_CurrencyID` INT,
	IN `p_area_prefix` VARCHAR(50),
	IN `p_trunk` VARCHAR(50),
	IN `p_RateMethod` VARCHAR(50),
	IN `p_ResellerID` INT
)
BEGIN
	DECLARE v_BillingTime_ INT;
	DECLARE v_raccountids TEXT;
	SET v_raccountids = '';
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetBillingTime(p_CompanyGatewayID,p_AccountID) INTO v_BillingTime_;

	IF p_ResellerID > 0
	THEN
		DROP TEMPORARY TABLE IF EXISTS tmp_reselleraccounts_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_reselleraccounts_(
			AccountID int
		);

		INSERT INTO tmp_reselleraccounts_
		SELECT AccountID FROM Ratemanagement3.tblAccountDetails WHERE ResellerOwner=p_ResellerID
		UNION
		SELECT AccountID FROM Ratemanagement3.tblReseller WHERE ResellerID=p_ResellerID;

		SELECT IFNULL(GROUP_CONCAT(AccountID),'') INTO v_raccountids FROM tmp_reselleraccounts_;

	END IF;

	SET @stm1 = CONCAT('

		INSERT INTO RMCDR3.`' , p_tbltempusagedetail_name , '` (
			CompanyID,
			CompanyGatewayID,
			GatewayAccountID,
			GatewayAccountPKID,
			AccountID,
			ServiceID,
			connect_time,
			disconnect_time,
			billed_duration,
			area_prefix,
			trunk,
			pincode,
			extension,
			cli,
			cld,
			cost,
			remote_ip,
			duration,
			ProcessID,
			ID,
			is_inbound,
			billed_second,
			disposition,
			userfield,
			AccountName,
			AccountNumber,
			AccountCLI,
			AccountIP,
			cc_type,
			FileName
		)

		SELECT
		*
		FROM (
			SELECT
				Distinct
				uh.CompanyID,
				uh.CompanyGatewayID,
				uh.GatewayAccountID,
				uh.GatewayAccountPKID,
				uh.AccountID,
				uh.ServiceID,
				connect_time,
				disconnect_time,
				billed_duration,
				CASE WHEN   "' , p_RateMethod , '" = "SpecifyRate"
				THEN
					area_prefix
				ELSE
					"Other"
				END
				AS area_prefix,
				CASE WHEN   "' , p_RateMethod , '" = "SpecifyRate"
				THEN
					trunk
				ELSE
					"Other"
				END
				AS trunk,
				pincode,
				extension,
				cli,
				cld,
				cost,
				remote_ip,
				duration,
				"',p_ProcessID,'",
				ud.ID,
				is_inbound,
				billed_second,
				disposition,
				userfield,
				IFNULL(ga.AccountName,""),
				IFNULL(ga.AccountNumber,""),
				IFNULL(ga.AccountCLI,""),
				IFNULL(ga.AccountIP,""),
				dr.cc_type,
				udfl.FileName
			FROM
				RMCDR3.tblUsageDetails  ud
			INNER JOIN RMCDR3.tblUsageHeader uh
				ON uh.UsageHeaderID = ud.UsageHeaderID
			LEFT JOIN RMCDR3.tblRetailUsageDetail dr on ud.UsageDetailID = dr.UsageDetailID AND ud.ID = dr.ID
			INNER JOIN Ratemanagement3.tblAccount a
				ON uh.AccountID = a.AccountID
			LEFT JOIN tblGatewayAccount ga
				ON ga.GatewayAccountPKID = uh.GatewayAccountPKID
			LEFT JOIN RMCDR3.tblUsageDetailsFileLog udfl
				ON udfl.UsageDetailID = ud.UsageDetailID
			WHERE
				( "' , p_CDRType , '" = "" OR  ("' , p_CDRType , '" = "inbound" AND ud.is_inbound = 1) OR  ("' , p_CDRType , '" = "outbound" AND ud.is_inbound = 0))
				AND  StartDate >= DATE_ADD( "' , p_StartDate , '",INTERVAL -1 DAY)
				AND StartDate <= DATE_ADD( "' , p_EndDate , '",INTERVAL 1 DAY)
				AND uh.CompanyID =  "' , p_CompanyID , '"
				AND uh.AccountID is not null
				AND ( "' , p_AccountID , '" = 0 OR uh.AccountID = "' , p_AccountID , '")
				AND ( "' , p_CompanyGatewayID , '" = 0 OR uh.CompanyGatewayID = "' , p_CompanyGatewayID , '")
				AND ( "' , p_CurrencyID ,'" = "0" OR a.CurrencyId = "' , p_CurrencyID , '")
				AND ( "' , p_CLI , '" = "" OR cli LIKE REPLACE("' , p_CLI , '", "*", "%"))
				AND ( "' , p_CLD , '" = "" OR cld LIKE REPLACE("' , p_CLD , '", "*", "%"))
				AND ( "' , p_trunk , '" = ""  OR  trunk = "' , p_trunk , '")
				AND ( "' , p_area_prefix , '" = "" OR area_prefix LIKE REPLACE( "' , p_area_prefix , '", "*", "%"))
				AND ( "' , p_zerovaluecost , '" = 0 OR (  "' , p_zerovaluecost , '" = 1 AND COALESCE(cost,0) = 0) OR (  "' , p_zerovaluecost , '" = 2 AND cost > 0))
				AND ( "' , p_ResellerID , '" = 0 OR FIND_IN_SET(uh.AccountID, "' , v_raccountids ,'" ) != 0)

		) tbl
		WHERE
			( ( "' , v_BillingTime_ , '" =1 OR  "' , v_BillingTime_ , '" = 3 ) AND connect_time >=  "' , p_StartDate , '" AND connect_time <=  "' , p_EndDate , '")
			OR
			("' , v_BillingTime_ , '" =2 AND disconnect_time >=  "' , p_StartDate , '" AND disconnect_time <=  "' , p_EndDate , '")
			AND billed_duration > 0;
	');


	PREPARE stmt1 FROM @stm1;
	EXECUTE stmt1;
	DEALLOCATE PREPARE stmt1;


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_InsertTempReRateVendorCDR`;
DELIMITER //
CREATE PROCEDURE `prc_InsertTempReRateVendorCDR`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AccountID` INT,
	IN `p_ProcessID` VARCHAR(50),
	IN `p_tbltempvendorcdrl_name` VARCHAR(50),
	IN `p_CLI` VARCHAR(50),
	IN `p_CLD` VARCHAR(50),
	IN `p_zerovaluebuyingcost` INT,
	IN `p_CurrencyID` INT,
	IN `p_area_prefix` VARCHAR(50),
	IN `p_trunk` VARCHAR(50),
	IN `p_RateMethod` VARCHAR(50)
)
BEGIN
	DECLARE v_BillingTime_ INT;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetBillingTime(p_CompanyGatewayID,p_AccountID) INTO v_BillingTime_;

	SET @stm1 = CONCAT('

		INSERT INTO RMCDR3.`' , p_tbltempvendorcdrl_name , '` (
			CompanyID,
			CompanyGatewayID,
			GatewayAccountID,
			GatewayAccountPKID,
			AccountID,
			ServiceID,
			connect_time,
			disconnect_time,
			billed_duration,
			area_prefix,
			trunk,
			cli,
			cld,
			selling_cost,
			buying_cost,
			remote_ip,
			duration,
			ProcessID,
			ID,
			billed_second,
			AccountName,
			AccountNumber,
			AccountCLI,
			AccountIP,
			FileName
		)
		SELECT
		*
		FROM (
			SELECT
				Distinct
				uh.CompanyID,
				uh.CompanyGatewayID,
				uh.GatewayAccountID,
				uh.GatewayAccountPKID,
				uh.AccountID,
				uh.ServiceID,
				connect_time,
				disconnect_time,
				billed_duration,
				CASE WHEN   "' , p_RateMethod , '" = "SpecifyRate"
				THEN
					area_prefix
				ELSE
					"Other"
				END
				AS area_prefix,
				CASE WHEN   "' , p_RateMethod , '" = "SpecifyRate"
				THEN
					trunk
				ELSE
					"Other"
				END
				AS trunk,
				cli,
				cld,
				selling_cost,
				buying_cost,
				remote_ip,
				duration,
				"',p_ProcessID,'",
				ud.ID,
				billed_second,
				IFNULL(ga.AccountName,""),
				IFNULL(ga.AccountNumber,""),
				IFNULL(ga.AccountCLI,""),
				IFNULL(ga.AccountIP,""),
				vcfl.FileName
			FROM
				RMCDR3.tblVendorCDR  ud
			INNER JOIN RMCDR3.tblVendorCDRHeader uh
				ON uh.VendorCDRHeaderID = ud.VendorCDRHeaderID
			INNER JOIN Ratemanagement3.tblAccount a
				ON uh.AccountID = a.AccountID
			LEFT JOIN tblGatewayAccount ga
				ON ga.GatewayAccountPKID = uh.GatewayAccountPKID
			LEFT JOIN RMCDR3.tblVendorCDRFileLog vcfl
				ON vcfl.VendorCDRID = ud.VendorCDRID
			WHERE
				StartDate >= DATE_ADD( "' , p_StartDate , '",INTERVAL -1 DAY)
				AND StartDate <= DATE_ADD( "' , p_EndDate , '",INTERVAL 1 DAY)
				AND uh.CompanyID =  "' , p_CompanyID , '"
				AND uh.AccountID is not null
				AND ( "' , p_AccountID , '" = 0 OR uh.AccountID = "' , p_AccountID , '")
				AND ( "' , p_CompanyGatewayID , '" = 0 OR uh.CompanyGatewayID = "' , p_CompanyGatewayID , '")
				AND ( "' , p_CurrencyID ,'" = "0" OR a.CurrencyId = "' , p_CurrencyID , '")
				AND ( "' , p_CLI , '" = "" OR cli LIKE REPLACE("' , p_CLI , '", "*", "%"))
				AND ( "' , p_CLD , '" = "" OR cld LIKE REPLACE("' , p_CLD , '", "*", "%"))
				AND ( "' , p_trunk , '" = ""  OR  trunk = "' , p_trunk , '")
				AND ( "' , p_area_prefix , '" = "" OR area_prefix LIKE REPLACE( "' , p_area_prefix , '", "*", "%"))
				AND ( "' , p_zerovaluebuyingcost , '" = 0 OR (  "' , p_zerovaluebuyingcost , '" = 1 AND COALESCE(buying_cost,0) = 0) OR (  "' , p_zerovaluebuyingcost , '" = 2 AND buying_cost > 0))
			) tbl
		WHERE
			( ( "' , v_BillingTime_ , '" =1 OR  "' , v_BillingTime_ , '" = 3 ) AND connect_time >=  "' , p_StartDate , '" AND connect_time <=  "' , p_EndDate , '")
			OR
			("' , v_BillingTime_ , '" =2 AND disconnect_time >=  "' , p_StartDate , '" AND disconnect_time <=  "' , p_EndDate , '")
			AND billed_duration > 0;
	');


	PREPARE stmt1 FROM @stm1;
	EXECUTE stmt1;
	DEALLOCATE PREPARE stmt1;


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;