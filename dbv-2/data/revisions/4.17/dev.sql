USE `RMBilling3`;

/*
Sippy SetupTime updated procedures

fnUsageDetail
fnServiceUsageDetail
fnVendorUsageDetail
prc_DeleteVCDR
prc_DeleteCDR
prc_InsertTempReRateCDR
*/



DROP PROCEDURE IF EXISTS `fnUsageDetail`;
DELIMITER //
CREATE   PROCEDURE `fnUsageDetail`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_GatewayID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_billing_time` INT,
	IN `p_cdr_type` VARCHAR(50),
	IN `p_CLI` VARCHAR(50),
	IN `p_CLD` VARCHAR(50),
	IN `p_zerovaluecost` INT

)
BEGIN
	DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageDetails_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageDetails_(
		AccountID int,
		AccountName varchar(100),
		GatewayAccountID varchar(100),
		trunk varchar(50),
		area_prefix varchar(50),
		pincode VARCHAR(50),
		extension VARCHAR(50),
		UsageDetailID int,
		duration int,
		billed_duration int,
		billed_second int,
		cli varchar(500),
		cld varchar(500),
		cost decimal(18,6),
		connect_time datetime,
		disconnect_time datetime,
		is_inbound tinyint(1) default 0,
		ID BIGINT(20),
		ServiceID INT
	);
	INSERT INTO tmp_tblUsageDetails_
	SELECT
	*
	FROM (
		SELECT
			uh.AccountID,
			a.AccountName,
			uh.GatewayAccountID,
			trunk,
			area_prefix,
			pincode,
			extension,
			UsageDetailID,
			duration,
			billed_duration,
			billed_second,
			cli,
			cld,
			cost,
			connect_time,
			disconnect_time,
			ud.is_inbound,
			ud.ID,
			uh.ServiceID
		FROM RMCDR3.tblUsageDetails  ud
		INNER JOIN RMCDR3.tblUsageHeader uh
			ON uh.UsageHeaderID = ud.UsageHeaderID
		INNER JOIN Ratemanagement3.tblAccount a
			ON uh.AccountID = a.AccountID
		WHERE
		(p_cdr_type = '' OR  ud.userfield LIKE CONCAT('%',p_cdr_type,'%'))
		AND  StartDate >= DATE_ADD(p_StartDate,INTERVAL -1 DAY)
		AND StartDate <= DATE_ADD(p_EndDate,INTERVAL 1 DAY)
		AND uh.CompanyID = p_CompanyID
		AND uh.AccountID IS NOT NULL
		AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
		AND (p_GatewayID = 0 OR CompanyGatewayID = p_GatewayID)
		AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
		AND (p_CLI = '' OR cli LIKE REPLACE(p_CLI, '*', '%'))
		AND (p_CLD = '' OR cld LIKE REPLACE(p_CLD, '*', '%'))
		AND (p_zerovaluecost = 0 OR ( p_zerovaluecost = 1 AND cost = 0) OR ( p_zerovaluecost = 2 AND cost > 0))
	) tbl
	WHERE
	( (p_billing_time =1 OR p_billing_time =3) AND connect_time >= p_StartDate AND connect_time <= p_EndDate)
	OR
	(p_billing_time =2 AND disconnect_time >= p_StartDate AND disconnect_time <= p_EndDate)


	AND billed_duration > 0
	ORDER BY disconnect_time DESC;
END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `fnServiceUsageDetail`;
DELIMITER //
CREATE  PROCEDURE `fnServiceUsageDetail`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_GatewayID` INT,
	IN `p_ServiceID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_billing_time` INT


)
BEGIN
	DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageDetails_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageDetails_(
			AccountID int,
			ServiceID int,
			AccountName varchar(100),
			GatewayAccountID varchar(100),
			trunk varchar(50),
			area_prefix varchar(50),
			pincode VARCHAR(50),
			extension VARCHAR(50),
			UsageDetailID int,
			duration int,
			billed_duration int,
			billed_second int,
			cli varchar(500),
			cld varchar(500),
			cost decimal(18,6),
			connect_time datetime,
			disconnect_time datetime,
			is_inbound tinyint(1) default 0,
			ID BIGINT(20)
	);
	INSERT INTO tmp_tblUsageDetails_
	SELECT
	*
	FROM (
		SELECT
			uh.AccountID,
			uh.ServiceID,
			a.AccountName,
			uh.GatewayAccountID,
			trunk,
			area_prefix,
			pincode,
			extension,
			UsageDetailID,
			duration,
			billed_duration,
			billed_second,
			cli,
			cld,
			cost,
			connect_time,
			disconnect_time,
			ud.is_inbound,
			ud.ID
		FROM RMCDR3.tblUsageDetails  ud
		INNER JOIN RMCDR3.tblUsageHeader uh
			ON uh.UsageHeaderID = ud.UsageHeaderID
		INNER JOIN Ratemanagement3.tblAccount a
			ON uh.AccountID = a.AccountID
		LEFT JOIN Ratemanagement3.tblAccountBilling ab
			ON ab.AccountID = a.AccountID AND ab.ServiceID = uh.ServiceID
		WHERE  StartDate >= DATE_ADD(p_StartDate,INTERVAL -1 DAY)
			AND StartDate <= DATE_ADD(p_EndDate,INTERVAL 1 DAY)
			AND uh.CompanyID = p_CompanyID
			AND uh.AccountID is not null
			AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
			AND (p_GatewayID = 0 OR CompanyGatewayID = p_GatewayID)
			AND ( (p_ServiceID = 0 AND ab.ServiceID IS NULL) OR  ab.ServiceID = p_ServiceID)
	) tbl
	WHERE
	( (p_billing_time =1 OR p_billing_time =3) and connect_time >= p_StartDate AND connect_time <= p_EndDate)
	OR
	(p_billing_time =2 and disconnect_time >= p_StartDate AND disconnect_time <= p_EndDate);
END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `fnVendorUsageDetail`;
DELIMITER //
CREATE  PROCEDURE `fnVendorUsageDetail`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_GatewayID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_billing_time` INT,
	IN `p_CLI` VARCHAR(50),
	IN `p_CLD` VARCHAR(50),
	IN `p_ZeroValueBuyingCost` INT

)
BEGIN

	DROP TEMPORARY TABLE IF EXISTS tmp_tblVendorUsageDetails_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblVendorUsageDetails_(
		AccountID INT,
		AccountName VARCHAR(50),
		trunk VARCHAR(50),
		area_prefix VARCHAR(50),
		VendorCDRID INT,
		billed_duration INT,
		cli VARCHAR(500),
		cld VARCHAR(500),
		selling_cost DECIMAL(18,6),
		buying_cost DECIMAL(18,6),
		connect_time DATETIME,
		disconnect_time DATETIME
	);
	INSERT INTO tmp_tblVendorUsageDetails_
	SELECT
	*
	FROM (
		SELECT
			uh.AccountID,
			a.AccountName,
			trunk,
			area_prefix,
			VendorCDRID,
			billed_duration,
			cli,
			cld,
			selling_cost,
			buying_cost,
			connect_time,
			disconnect_time
		FROM RMCDR3.tblVendorCDR  ud
		INNER JOIN RMCDR3.tblVendorCDRHeader uh
			ON uh.VendorCDRHeaderID = ud.VendorCDRHeaderID
		INNER JOIN Ratemanagement3.tblAccount a
			ON uh.AccountID = a.AccountID
		WHERE StartDate >= DATE_ADD(p_StartDate,INTERVAL -1 DAY)
		AND StartDate <= DATE_ADD(p_EndDate,INTERVAL 1 DAY)
		AND uh.CompanyID = p_CompanyID
		AND uh.AccountID IS NOT NULL
		AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
		AND (p_GatewayID = 0 OR CompanyGatewayID = p_GatewayID)
		AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
		AND (p_CLI = '' OR cli LIKE REPLACE(p_CLI, '*', '%'))
		AND (p_CLD = '' OR cld LIKE REPLACE(p_CLD, '*', '%'))
		AND (p_ZeroValueBuyingCost = 0 OR ( p_ZeroValueBuyingCost = 1 AND buying_cost = 0) OR ( p_ZeroValueBuyingCost = 2 AND buying_cost > 0))
	) tbl
	WHERE 
	( ( p_billing_time =1 OR p_billing_time =3 )  AND connect_time >= p_StartDate AND connect_time <= p_EndDate)
	OR 
	(p_billing_time =2 AND disconnect_time >= p_StartDate AND disconnect_time <= p_EndDate)
	AND billed_duration > 0
	ORDER BY disconnect_time DESC;
END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_DeleteVCDR`;
DELIMITER //
CREATE  PROCEDURE `prc_DeleteVCDR`(
	IN `p_CompanyID` INT,
	IN `p_GatewayID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AccountID` INT,
	IN `p_CLI` VARCHAR(250),
	IN `p_CLD` VARCHAR(250),
	IN `p_zerovaluecost` INT,
	IN `p_CurrencyID` INT,
	IN `p_area_prefix` VARCHAR(50),
	IN `p_trunk` VARCHAR(50)

)
    COMMENT 'Delete Vendor CDR'
BEGIN

    DECLARE v_BillingTime_ int;
    SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

		SELECT fnGetBillingTime(p_GatewayID,p_AccountID) INTO v_BillingTime_;


        CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageDetail_ AS
        (

	        SELECT
	        VendorCDRID

	        FROM (SELECT
	            ud.VendorCDRID,
	            billed_duration,
	            connect_time,
	            disconnect_time,
	            cli,
	            cld,
	            buying_cost,
	            CompanyGatewayID,
	            uh.AccountID

			FROM `RMCDR3`.tblVendorCDR  ud
			INNER JOIN `RMCDR3`.tblVendorCDRHeader uh
				ON uh.VendorCDRHeaderID = ud.VendorCDRHeaderID
	        LEFT JOIN Ratemanagement3.tblAccount a
	            ON uh.AccountID = a.AccountID
	        WHERE StartDate >= DATE_ADD(p_StartDate,INTERVAL -1 DAY)
			  AND StartDate <= DATE_ADD(p_EndDate,INTERVAL 1 DAY)
	        AND uh.CompanyID = p_CompanyID
	        AND uh.AccountID is not null
	        AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
	        AND (p_GatewayID = 0 OR CompanyGatewayID = p_GatewayID)
	        AND (p_CLI = '' OR cli LIKE REPLACE(p_CLI, '*', '%'))
			  AND (p_CLD = '' OR cld LIKE REPLACE(p_CLD, '*', '%'))
			  AND (p_area_prefix = '' OR area_prefix LIKE REPLACE(p_area_prefix, '*', '%'))
			  AND (p_trunk = '' OR trunk = p_trunk )
				AND (p_zerovaluecost = 0 OR ( p_zerovaluecost = 1 AND buying_cost = 0) OR ( p_zerovaluecost = 2 AND buying_cost > 0))
			  AND (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
	        ) tbl
	        WHERE

	        ( (v_BillingTime_ =1 OR v_BillingTime_ =3)  and connect_time >= p_StartDate AND connect_time <= p_EndDate)
	        OR
	        (v_BillingTime_ =2 and disconnect_time >= p_StartDate AND disconnect_time <= p_EndDate)
	        AND billed_duration > 0
        );



		 delete ud.*
        From `RMCDR3`.tblVendorCDR ud
        inner join tmp_tblUsageDetail_ uds on ud.VendorCDRID = uds.VendorCDRID;

        SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_DeleteCDR`;
DELIMITER //
CREATE PROCEDURE `prc_DeleteCDR`(
	IN `p_CompanyID` INT,
	IN `p_GatewayID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AccountID` INT,
	IN `p_CDRType` VARCHAR(50),
	IN `p_CLI` VARCHAR(50),
	IN `p_CLD` VARCHAR(50),
	IN `p_zerovaluecost` INT,
	IN `p_CurrencyID` INT,
	IN `p_area_prefix` VARCHAR(50),
	IN `p_trunk` VARCHAR(50)

)
BEGIN

	DECLARE v_BillingTime_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	SELECT fnGetBillingTime(p_GatewayID,p_AccountID) INTO v_BillingTime_;

	DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageDetail_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageDetail_ AS (

		SELECT
		UsageDetailID
		FROM
		(
			SELECT
				uh.AccountID,
				a.AccountName,
				trunk,
				area_prefix,
				UsageDetailID,
				duration,
				billed_duration,
				cli,
				cld,
				cost,
				connect_time,
				disconnect_time
			FROM `RMCDR3`.tblUsageDetails  ud
			INNER JOIN `RMCDR3`.tblUsageHeader uh
				ON uh.UsageHeaderID = ud.UsageHeaderID
			INNER JOIN Ratemanagement3.tblAccount a
				ON uh.AccountID = a.AccountID
			WHERE StartDate >= DATE_ADD(p_StartDate,INTERVAL -1 DAY)
				AND StartDate <= DATE_ADD(p_EndDate,INTERVAL 1 DAY)
				AND uh.CompanyID = p_CompanyID
				AND uh.AccountID is not null
				AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
				AND (p_GatewayID = 0 OR CompanyGatewayID = p_GatewayID)
				AND (p_CDRType = '' OR ud.userfield LIKE CONCAT('%',p_CDRType,'%') )
				AND (p_CLI = '' OR cli LIKE REPLACE(p_CLI, '*', '%'))
				AND (p_CLD = '' OR cld LIKE REPLACE(p_CLD, '*', '%'))
				AND (p_zerovaluecost = 0 OR ( p_zerovaluecost = 1 AND cost = 0) OR ( p_zerovaluecost = 2 AND cost > 0))
				AND (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
				AND (p_area_prefix = '' OR area_prefix LIKE REPLACE(p_area_prefix, '*', '%'))
				AND (p_trunk = '' OR trunk = p_trunk )

		) tbl
		WHERE

			((v_BillingTime_ =1 OR v_BillingTime_ =3)  and connect_time >= p_StartDate AND connect_time <= p_EndDate)
			OR
			(v_BillingTime_ =2 and disconnect_time >= p_StartDate AND disconnect_time <= p_EndDate)
			AND billed_duration > 0
	);


	/* For Mirta only */
	DELETE ud.*
	FROM `RMCDR3`.tblRetailUsageDetail ud
	INNER JOIN tmp_tblUsageDetail_ uds
		ON ud.UsageDetailID = uds.UsageDetailID;

	DELETE ud.*
	FROM `RMCDR3`.tblUsageDetails ud
	INNER JOIN tmp_tblUsageDetail_ uds
		ON ud.UsageDetailID = uds.UsageDetailID;

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
	IN `p_RateMethod` VARCHAR(50)

)
BEGIN
	DECLARE v_BillingTime_ INT;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetBillingTime(p_CompanyGatewayID,p_AccountID) INTO v_BillingTime_;

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
		AccountIP
	)

	SELECT
	*
	FROM (SELECT
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
		ID,
		is_inbound,
		billed_second,
		disposition,
		userfield,
		IFNULL(ga.AccountName,""),
		IFNULL(ga.AccountNumber,""),
		IFNULL(ga.AccountCLI,""),
		IFNULL(ga.AccountIP,"")
	FROM RMCDR3.tblUsageDetails  ud
	INNER JOIN RMCDR3.tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	INNER JOIN Ratemanagement3.tblAccount a
		ON uh.AccountID = a.AccountID
	LEFT JOIN tblGatewayAccount ga
		ON ga.GatewayAccountPKID = uh.GatewayAccountPKID
	WHERE
	( "' , p_CDRType , '" = "" OR  ud.userfield LIKE  CONCAT("%","' , p_CDRType , '","%"))
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
	AND ( "' , p_zerovaluecost , '" = 0 OR (  "' , p_zerovaluecost , '" = 1 AND cost = 0) OR (  "' , p_zerovaluecost , '" = 2 AND cost > 0))
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

DROP PROCEDURE IF EXISTS `prc_ProcessCDRService`;
DELIMITER //
CREATE PROCEDURE `prc_ProcessCDRService`(
	IN `p_CompanyID` INT,
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200)



)
BEGIN

	DECLARE v_ServiceAccountID_CLI_Count_ INT;
	DECLARE v_ServiceAccountID_IP_Count_ INT;

	SELECT COUNT(*) INTO v_ServiceAccountID_CLI_Count_
	FROM Ratemanagement3.tblAccountAuthenticate aa
	INNER JOIN Ratemanagement3.tblCLIRateTable crt ON crt.AccountID = aa.AccountID
	WHERE aa.CompanyID = p_CompanyID AND (CustomerAuthRule = 'CLI' OR VendorAuthRule = 'CLI') AND crt.ServiceID > 0 ;

	IF v_ServiceAccountID_CLI_Count_ > 0
	THEN


		SET @stm = CONCAT('
		UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` uh
		INNER JOIN Ratemanagement3.tblCLIRateTable ga
			ON  ga.CompanyID = uh.CompanyID
			AND ga.CLI = uh.cli

		SET uh.ServiceID = ga.ServiceID
		WHERE ga.ServiceID > 0
		AND uh.CompanyID = ' ,  p_CompanyID , '
		AND uh.ProcessID = "' , p_processId , '" ;
		');
		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

	END IF;

	SELECT COUNT(*) INTO v_ServiceAccountID_IP_Count_
	FROM Ratemanagement3.tblAccountAuthenticate aa
	WHERE aa.CompanyID = p_CompanyID AND (CustomerAuthRule = 'IP' OR VendorAuthRule = 'IP') AND ServiceID > 0;

	IF v_ServiceAccountID_IP_Count_ > 0
	THEN


		SET @stm = CONCAT('
		UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` uh
		INNER JOIN Ratemanagement3.tblAccountAuthenticate ga
			ON  ga.CompanyID = uh.CompanyID
			AND ( FIND_IN_SET(uh.GatewayAccountID,ga.CustomerAuthValue) != 0 OR FIND_IN_SET(uh.GatewayAccountID,ga.VendorAuthValue) != 0 )
		SET uh.ServiceID = ga.ServiceID
		WHERE ga.ServiceID > 0
		AND uh.CompanyID = ' ,  p_CompanyID , '
		AND uh.ProcessID = "' , p_processId , '" ;
		');
		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

	END IF;

END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_ApplyAuthRule`;
DELIMITER //
CREATE PROCEDURE `prc_ApplyAuthRule`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_ServiceID` INT




)
	BEGIN
		DECLARE p_NameFormat VARCHAR(10);
		DECLARE v_pointer_ INT ;
		DECLARE v_rowCount_ INT ;

		SET v_pointer_ = 1;
		SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_AuthenticateRules_);

		WHILE v_pointer_ <= v_rowCount_
		DO

			SET p_NameFormat = ( SELECT AuthRule FROM tmp_AuthenticateRules_  WHERE RowNo = v_pointer_ );

			IF  p_NameFormat = 'NAMENUB'
			THEN

				INSERT INTO tmp_ActiveAccount
					SELECT DISTINCT
						ga.AccountName,
						ga.AccountNumber,
						ga.AccountCLI,
						ga.AccountIP,
						a.AccountID,
						ga.ServiceID
					FROM Ratemanagement3.tblAccount  a
						INNER JOIN tblGatewayAccount ga
							ON ga.CompanyID = a.CompanyId
								 AND CONCAT(a.AccountName , '-' , a.Number) = ga.AccountName
						LEFT JOIN Ratemanagement3.tblAccountAuthenticate aa
							ON a.AccountID = aa.AccountID
								 AND aa.ServiceID = ga.ServiceID
					WHERE GatewayAccountID IS NOT NULL
								AND ga.AccountID IS NULL
								AND a.CompanyId = p_CompanyID
								AND a.Status = 1
								AND ga.CompanyGatewayID = p_CompanyGatewayID
								AND ga.ServiceID = p_ServiceID
								AND ( ( aa.AccountID IS NOT NULL AND (aa.CustomerAuthRule = 'NAMENUB' OR aa.VendorAuthRule ='NAMENUB' )) OR
											aa.AccountID IS NULL
								);

			END IF;

			IF p_NameFormat = 'NUBNAME'
			THEN

				INSERT INTO tmp_ActiveAccount
					SELECT DISTINCT
						ga.AccountName,
						ga.AccountNumber,
						ga.AccountCLI,
						ga.AccountIP,
						a.AccountID,
						ga.ServiceID
					FROM Ratemanagement3.tblAccount  a
						INNER JOIN tblGatewayAccount ga
							ON ga.CompanyID = a.CompanyId
								 AND CONCAT(a.Number, '-' , a.AccountName) = ga.AccountName
						LEFT JOIN Ratemanagement3.tblAccountAuthenticate aa
							ON a.AccountID = aa.AccountID
								 AND aa.ServiceID = ga.ServiceID
					WHERE GatewayAccountID IS NOT NULL
								AND ga.AccountID IS NULL
								AND a.CompanyId = p_CompanyID
								AND a.Status = 1
								AND ga.CompanyGatewayID = p_CompanyGatewayID
								AND ga.ServiceID = p_ServiceID
								AND ( ( aa.AccountID IS NOT NULL AND (aa.CustomerAuthRule = 'NUBNAME' OR aa.VendorAuthRule ='NUBNAME' )) OR
											aa.AccountID IS NULL
								);

			END IF;

			IF p_NameFormat = 'NUB'
			THEN

				INSERT INTO tmp_ActiveAccount
					SELECT DISTINCT
						ga.AccountName,
						ga.AccountNumber,
						ga.AccountCLI,
						ga.AccountIP,
						a.AccountID,
						ga.ServiceID
					FROM Ratemanagement3.tblAccount  a
						INNER JOIN tblGatewayAccount ga
							ON ga.CompanyID = a.CompanyId
								 AND a.Number = ga.AccountNumber
						LEFT JOIN Ratemanagement3.tblAccountAuthenticate aa
							ON a.AccountID = aa.AccountID
								 AND aa.ServiceID = ga.ServiceID
					WHERE GatewayAccountID IS NOT NULL
								AND ga.AccountID IS NULL
								AND a.CompanyId = p_CompanyID
								AND a.Status = 1
								AND ga.CompanyGatewayID = p_CompanyGatewayID
								AND ga.ServiceID = p_ServiceID
								AND ( ( aa.AccountID IS NOT NULL AND (aa.CustomerAuthRule = 'NUB' OR aa.VendorAuthRule ='NUB' )) OR
											aa.AccountID IS NULL
								);

			END IF;

			IF p_NameFormat = 'IP'
			THEN

				INSERT INTO tmp_ActiveAccount
					SELECT DISTINCT
						ga.AccountName,
						ga.AccountNumber,
						ga.AccountCLI,
						ga.AccountIP,
						a.AccountID,
						aa.ServiceID
					FROM Ratemanagement3.tblAccount  a
						INNER JOIN Ratemanagement3.tblAccountAuthenticate aa
							ON a.AccountID = aa.AccountID AND (aa.CustomerAuthRule = 'IP' OR aa.VendorAuthRule ='IP')
						INNER JOIN tblGatewayAccount ga
							ON ga.CompanyID = a.CompanyId
								 AND ga.ServiceID = p_ServiceID AND aa.ServiceID = ga.ServiceID
								 AND ( (aa.CustomerAuthRule = 'IP' AND FIND_IN_SET(ga.AccountIP,aa.CustomerAuthValue) != 0) OR (aa.VendorAuthRule ='IP' AND FIND_IN_SET(ga.AccountIP,aa.VendorAuthValue) != 0) )
					WHERE a.CompanyId = p_CompanyID
								AND a.`Status` = 1
								AND GatewayAccountID IS NOT NULL
								AND ga.AccountID IS NULL
								AND ga.CompanyGatewayID = p_CompanyGatewayID;

			END IF;

			IF p_NameFormat = 'CLI'
			THEN

				INSERT INTO tmp_ActiveAccount
					SELECT DISTINCT
						ga.AccountName,
						ga.AccountNumber,
						ga.AccountCLI,
						ga.AccountIP,
						a.AccountID,
						aa.ServiceID
					FROM Ratemanagement3.tblAccount  a
						INNER JOIN tblGatewayAccount ga
							ON ga.CompanyID = a.CompanyId
						INNER JOIN Ratemanagement3.tblCLIRateTable aa
							ON a.AccountID = aa.AccountID
								 AND ga.ServiceID = p_ServiceID AND aa.ServiceID = ga.ServiceID
								 AND ga.AccountCLI = aa.CLI
					WHERE a.CompanyId = p_CompanyID
								AND a.`Status` = 1
								AND GatewayAccountID IS NOT NULL
								AND ga.AccountID IS NULL
								AND ga.CompanyGatewayID = p_CompanyGatewayID;

			END IF;

			IF p_NameFormat = '' OR p_NameFormat IS NULL OR p_NameFormat = 'NAME'
			THEN

				-- IF sippy add sippy gateway too
				select count(*) into @IsSippy from Ratemanagement3.tblGateway g inner join Ratemanagement3.tblCompanyGateway cg
						on cg.GatewayID = g.GatewayID
							 AND cg.`Status` = 1
							 AND cg.CompanyGatewayID = p_CompanyGatewayID
							 AND g.Name = 'SippySFTP';

				IF (@IsSippy > 0 ) THEN


					INSERT INTO tmp_ActiveAccount
						SELECT DISTINCT
							ga.AccountName,
							ga.AccountNumber,
							ga.AccountCLI,
							ga.AccountIP,
							sa.AccountID,
							ga.ServiceID
						FROM Ratemanagement3.tblAccount  a
							LEFT JOIN Ratemanagement3.tblAccountAuthenticate aa
								ON a.AccountID = aa.AccountID
							INNER JOIN tblGatewayAccount ga
								ON ga.CompanyID = a.CompanyId
									 AND aa.ServiceID = ga.ServiceID
							--	AND a.AccountName = ga.AccountName
							INNER JOIN Ratemanagement3.tblAccountSippy sa
								ON sa.CompanyID = a.CompanyId
									 AND 	( (a.IsCustomer = 1	AND ga.AccountNumber = sa.i_account)	OR	( a.IsVendor = 1	AND ga.AccountNumber = sa.i_vendor ) )
						WHERE a.CompanyId = p_CompanyID
									AND a.`Status` = 1
									AND ga.ServiceID = p_ServiceID
									AND GatewayAccountID IS NOT NULL
									AND ga.AccountID IS NULL
									AND ga.CompanyGatewayID = p_CompanyGatewayID
									AND ( ( aa.AccountID IS NOT NULL AND (aa.CustomerAuthRule = 'NAME' OR aa.VendorAuthRule ='NAME' )) OR
												aa.AccountID IS NULL
									);

				ELSE


					INSERT INTO tmp_ActiveAccount
						SELECT DISTINCT
							ga.AccountName,
							ga.AccountNumber,
							ga.AccountCLI,
							ga.AccountIP,
							a.AccountID,
							ga.ServiceID
						FROM Ratemanagement3.tblAccount  a
							INNER JOIN tblGatewayAccount ga
								ON ga.CompanyID = a.CompanyId
									 AND a.AccountName = ga.AccountName
							LEFT JOIN Ratemanagement3.tblAccountAuthenticate aa
								ON a.AccountID = aa.AccountID
									 AND aa.ServiceID = ga.ServiceID
						WHERE a.CompanyId = p_CompanyID
									AND a.`Status` = 1
									AND ga.ServiceID = p_ServiceID
									AND GatewayAccountID IS NOT NULL
									AND ga.AccountID IS NULL
									AND ga.CompanyGatewayID = p_CompanyGatewayID
									AND ( ( aa.AccountID IS NOT NULL AND (aa.CustomerAuthRule = 'NAME' OR aa.VendorAuthRule ='NAME' )) OR
												aa.AccountID IS NULL
									);


				END IF;


			END IF;

			IF p_NameFormat = 'Other'
			THEN

				INSERT INTO tmp_ActiveAccount
					SELECT DISTINCT
						ga.AccountName,
						ga.AccountNumber,
						ga.AccountCLI,
						ga.AccountIP,
						a.AccountID,
						aa.ServiceID
					FROM Ratemanagement3.tblAccount  a
						INNER JOIN Ratemanagement3.tblAccountAuthenticate aa
							ON a.AccountID = aa.AccountID AND (aa.CustomerAuthRule = 'Other' OR aa.VendorAuthRule ='Other')
						INNER JOIN tblGatewayAccount ga
							ON ga.CompanyID = a.CompanyId
								 AND ga.ServiceID = aa.ServiceID
								 AND ( (aa.VendorAuthRule ='Other' AND aa.VendorAuthValue = ga.AccountName) OR (aa.CustomerAuthRule = 'Other' AND aa.CustomerAuthValue = ga.AccountName) )
					WHERE a.CompanyId = p_CompanyID
								AND a.`Status` = 1
								AND GatewayAccountID IS NOT NULL
								AND ga.AccountID IS NULL
								AND ga.CompanyGatewayID = p_CompanyGatewayID
								AND ga.ServiceID = p_ServiceID;

			END IF;

			SET v_pointer_ = v_pointer_ + 1;

		END WHILE;

	END//
DELIMITER ;






DROP PROCEDURE IF EXISTS `prc_ProcesssCDR`;
DELIMITER //
CREATE PROCEDURE `prc_ProcesssCDR`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200),
	IN `p_RateCDR` INT,
	IN `p_RateFormat` INT,
	IN `p_NameFormat` VARCHAR(50),
	IN `p_RateMethod` VARCHAR(50),
	IN `p_SpecifyRate` DECIMAL(18,6),
	IN `p_OutboundTableID` INT,
	IN `p_InboundTableID` INT,
	IN `p_RerateAccounts` INT











)
	BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;


	CALL prc_autoAddIP(p_CompanyID,p_CompanyGatewayID); -- only if AutoAddIP is on

	CALL prc_ProcessCDRService(p_CompanyID,p_processId,p_tbltempusagedetail_name); -- update service ID based on IP or cli


	DROP TEMPORARY TABLE IF EXISTS tmp_Customers_;
	CREATE TEMPORARY TABLE tmp_Customers_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AccountID INT,
		CompanyGatewayID INT
	);

	IF p_RerateAccounts!=0
	THEN
		-- selected customer
      SET @sql1 = concat("insert into tmp_Customers_ (AccountID) values ('", replace(( select TRIM(REPLACE(group_concat(distinct IFNULL(REPLACE(REPLACE(json_extract(Settings, '$.Accounts'), '[', ''), ']', ''),0)),'"','')) as AccountID from Ratemanagement3.tblCompanyGateway), ",", "'),('"),"');");
      PREPARE stmt1 FROM @sql1;
      EXECUTE stmt1;
      DEALLOCATE PREPARE stmt1;
      DELETE FROM tmp_Customers_ WHERE AccountID=0;
      UPDATE tmp_Customers_ SET CompanyGatewayID=p_CompanyGatewayID WHERE 1;
  END IF;

	DROP TEMPORARY TABLE IF EXISTS tmp_Service_;
	CREATE TEMPORARY TABLE tmp_Service_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		ServiceID INT
	);
	SET @stm = CONCAT('
	INSERT INTO tmp_Service_ (ServiceID)
	SELECT DISTINCT ServiceID FROM RMCDR3.`' , p_tbltempusagedetail_name , '` ud WHERE ProcessID="' , p_processId , '" AND ServiceID > 0;
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;

	SET @stm = CONCAT('
	INSERT INTO tmp_Service_ (ServiceID)
	SELECT DISTINCT tblService.ServiceID
	FROM Ratemanagement3.tblService
	LEFT JOIN  RMCDR3.`' , p_tbltempusagedetail_name , '` ud
	ON tblService.ServiceID = ud.ServiceID AND ProcessID="' , p_processId , '"
	WHERE tblService.ServiceID > 0 AND tblService.CompanyGatewayID > 0 AND ud.ServiceID IS NULL
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;



	CALL prc_ProcessCDRAccount(p_CompanyID,p_CompanyGatewayID,p_processId,p_tbltempusagedetail_name,p_NameFormat);



	-- p_OutboundTableID is for cdr upload
	IF ( ( SELECT COUNT(*) FROM tmp_Service_ ) > 0 OR p_OutboundTableID > 0)
	THEN


		CALL prc_RerateOutboundService(p_processId,p_tbltempusagedetail_name,p_RateCDR,p_RateFormat,p_RateMethod,p_SpecifyRate,p_OutboundTableID);

	ELSE


		CALL prc_RerateOutboundTrunk(p_processId,p_tbltempusagedetail_name,p_RateCDR,p_RateFormat,p_RateMethod,p_SpecifyRate);


		CALL prc_autoUpdateTrunk(p_CompanyID,p_CompanyGatewayID);

	END IF;

	 -- no rerate and prefix format

	IF p_RateCDR = 0 AND p_RateFormat = 2
	THEN

		DROP TEMPORARY TABLE IF EXISTS tmp_Accounts_;
		CREATE TEMPORARY TABLE tmp_Accounts_  (
			RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			AccountID INT
		);
		SET @stm = CONCAT('
		INSERT INTO tmp_Accounts_(AccountID)
		SELECT DISTINCT AccountID FROM RMCDR3.`' , p_tbltempusagedetail_name , '` ud WHERE ProcessID="' , p_processId , '" AND AccountID IS NOT NULL AND TrunkID IS NOT NULL;
		');

		PREPARE stm FROM @stm;
		EXECUTE stm;
		DEALLOCATE PREPARE stm;


		CALL Ratemanagement3.prc_getDefaultCodes(p_CompanyID);


		CALL prc_updateDefaultPrefix(p_processId, p_tbltempusagedetail_name);

	END IF;


	CALL prc_RerateInboundCalls(p_CompanyID,p_processId,p_tbltempusagedetail_name,p_RateCDR,p_RateMethod,p_SpecifyRate,p_InboundTableID);


	-- for mirta only
	IF (  p_RateCDR = 1 )
	THEN
		-- update cost = 0 where cc_type = 4 (OUTNOCHARGE)
		SET @stm = CONCAT('
	UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` ud
	INNER JOIN  RMCDR3.`' , p_tbltempusagedetail_name ,'_Retail' , '` udr ON ud.TempUsageDetailID = udr.TempUsageDetailID AND ud.ProcessID = udr.ProcessID
	SET cost = 0
  WHERE ud.ProcessID="' , p_processId , '" AND udr.cc_type = 4 ;
	');

		PREPARE stm FROM @stm;
		EXECUTE stm;
		DEALLOCATE PREPARE stm;

	END IF;



	CALL prc_CreateRerateLog(p_processId,p_tbltempusagedetail_name,p_RateCDR);

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_getSOA`;
DELIMITER //
CREATE PROCEDURE `prc_getSOA`(
	IN `p_CompanyID` INT,
	IN `p_accountID` INT,
	IN `p_StartDate` datetime,
	IN `p_EndDate` datetime,
	IN `p_isExport` INT 





)
BEGIN
  
  
    	
    DECLARE v_start_date DATETIME ;
	DECLARE v_bf_InvoiceOutAmountTotal NUMERIC(18, 8);
	DECLARE v_bf_PaymentInAmountTotal NUMERIC(18, 8);
	DECLARE v_bf_InvoiceInAmountTotal NUMERIC(18, 8);
	DECLARE v_bf_PaymentOutAmountTotal NUMERIC(18, 8);


    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
     SET SESSION sql_mode='';


   DROP TEMPORARY TABLE IF EXISTS tmp_Invoices;
    CREATE TEMPORARY TABLE tmp_Invoices (
        InvoiceNo VARCHAR(50),
        PeriodCover VARCHAR(50),
        IssueDate Datetime,
        Amount NUMERIC(18, 8),
        InvoiceType int,
        AccountID int,
        InvoiceID INT
    );
    
    DROP TEMPORARY TABLE IF EXISTS tmp_Invoices_broghtf;
    CREATE TEMPORARY TABLE tmp_Invoices_broghtf (
        Amount NUMERIC(18, 8),
        InvoiceType int
    );

    
    DROP TEMPORARY TABLE IF EXISTS tmp_Payments;
    CREATE TEMPORARY TABLE tmp_Payments (
        InvoiceNo VARCHAR(50),
        PaymentDate VARCHAR(50),
        IssueDate datetime,
        Amount NUMERIC(18, 8),
        PaymentID INT,
        PaymentType VARCHAR(50),
        InvoiceID INT
    );
    
    DROP TEMPORARY TABLE IF EXISTS tmp_Payments_broghtf;
    CREATE TEMPORARY TABLE tmp_Payments_broghtf (
        Amount NUMERIC(18, 8),
        PaymentType VARCHAR(50)
    );

    
    DROP TEMPORARY TABLE IF EXISTS tmp_Disputes;
    CREATE TEMPORARY TABLE tmp_Disputes (
        InvoiceNo VARCHAR(50),
        created_at datetime,
        DisputeAmount NUMERIC(18, 8),
        InvoiceType VARCHAR(50),
        DisputeID INT,
        AccountID INT,
        InvoiceID INT

    );
  
  DROP TEMPORARY TABLE IF EXISTS tmp_InvoiceOutWithDisputes;
   CREATE TEMPORARY TABLE tmp_InvoiceOutWithDisputes (
        InvoiceOut_InvoiceNo VARCHAR(50),
        InvoiceOut_PeriodCover VARCHAR(50),
        InvoiceOut_IssueDate datetime,
        InvoiceOut_Amount NUMERIC(18, 8),
        InvoiceOut_DisputeAmount NUMERIC(18, 8),
        InvoiceOut_DisputeID INT,
       InvoiceOut_AccountID INT,
       InvoiceOut_InvoiceID INT
    );
    
  DROP TEMPORARY TABLE IF EXISTS tmp_InvoiceInWithDisputes;
   CREATE TEMPORARY TABLE tmp_InvoiceInWithDisputes (
        InvoiceIn_InvoiceNo VARCHAR(50),
        InvoiceIn_PeriodCover VARCHAR(50),
        InvoiceIn_IssueDate datetime,
        InvoiceIn_Amount NUMERIC(18, 8),
        InvoiceIn_DisputeAmount NUMERIC(18, 8),
        InvoiceIn_DisputeID INT,
        InvoiceIn_AccountID INT,
        InvoiceIn_InvoiceID INT
    );
  
  


    
     
    INSERT into tmp_Invoices
    SELECT
      DISTINCT 
         tblInvoice.InvoiceNumber,
         CASE
          WHEN (tblInvoice.ItemInvoice = 1 ) THEN
                DATE_FORMAT(tblInvoice.IssueDate,'%d/%m/%Y')
        WHEN (tblInvoice.ItemInvoice IS NUll AND p_isExport = 0 ) THEN
          Concat(DATE_FORMAT(tblInvoiceDetail.StartDate,'%d/%m/%Y') ,'<br>' , DATE_FORMAT(tblInvoiceDetail.EndDate,'%d/%m/%Y'))
        WHEN (tblInvoice.ItemInvoice IS NUll AND p_isExport = 1) THEN
          (select Concat(DATE_FORMAT(tblInvoiceDetail.StartDate,'%d/%m/%Y') ,'-' , DATE_FORMAT(tblInvoiceDetail.EndDate,'%d/%m/%Y')) from tblInvoiceDetail where tblInvoiceDetail.InvoiceID= tblInvoice.InvoiceID order by InvoiceDetailID  limit 1)  
       END AS PeriodCover,
         tblInvoice.IssueDate,
         tblInvoice.GrandTotal,
         tblInvoice.InvoiceType,
        tblInvoice.AccountID,
        tblInvoice.InvoiceID
        FROM tblInvoice
        LEFT JOIN tblInvoiceDetail         
		  ON tblInvoice.InvoiceID = tblInvoiceDetail.InvoiceID AND ( (tblInvoice.InvoiceType = 1 AND tblInvoiceDetail.ProductType = 2 ) OR  tblInvoice.InvoiceType =2 )
        WHERE tblInvoice.CompanyID = p_CompanyID
        AND tblInvoice.AccountID = p_accountID
        AND ( (tblInvoice.InvoiceType = 2) OR ( tblInvoice.InvoiceType = 1 AND tblInvoice.InvoiceStatus NOT IN ( 'cancel' , 'draft' , 'awaiting') )  )
        AND (p_StartDate = '0000-00-00' OR  (p_StartDate != '0000-00-00' AND  DATE_FORMAT(tblInvoice.IssueDate,'%Y-%m-%d') >= p_StartDate ) )
      AND (p_EndDate   = '0000-00-00' OR  (p_EndDate   != '0000-00-00' AND  DATE_FORMAT(tblInvoice.IssueDate,'%Y-%m-%d') <= p_EndDate ) )
      AND tblInvoice.GrandTotal != 0
      Order by tblInvoice.IssueDate asc;
    
	IF ( p_StartDate = '0000-00-00' ) THEN
		  SELECT min(IssueDate) into  v_start_date from tmp_Invoices;
	END IF;
	
	
        INSERT into tmp_Invoices_broghtf
      SELECT 
		GrandTotal ,
		InvoiceType
		FROM (
		
		SELECT
		 DISTINCT 
         tblInvoice.InvoiceNumber,
         CASE
          WHEN (tblInvoice.ItemInvoice = 1 ) THEN
                DATE_FORMAT(tblInvoice.IssueDate,'%d/%m/%Y')
        WHEN (tblInvoice.ItemInvoice IS NUll AND p_isExport = 0 ) THEN
          Concat(DATE_FORMAT(tblInvoiceDetail.StartDate,'%d/%m/%Y') ,'<br>' , DATE_FORMAT(tblInvoiceDetail.EndDate,'%d/%m/%Y'))
        WHEN (tblInvoice.ItemInvoice IS NUll AND p_isExport = 1) THEN
          (select Concat(DATE_FORMAT(tblInvoiceDetail.StartDate,'%d/%m/%Y') ,'-' , DATE_FORMAT(tblInvoiceDetail.EndDate,'%d/%m/%Y')) from tblInvoiceDetail where tblInvoiceDetail.InvoiceID= tblInvoice.InvoiceID order by InvoiceDetailID  limit 1)  
       END AS PeriodCover,
         tblInvoice.IssueDate,
         tblInvoice.GrandTotal,
         tblInvoice.InvoiceType,
        tblInvoice.AccountID,
        tblInvoice.InvoiceID
        FROM tblInvoice
        LEFT JOIN tblInvoiceDetail         
		  ON tblInvoice.InvoiceID = tblInvoiceDetail.InvoiceID AND ( (tblInvoice.InvoiceType = 1 AND tblInvoiceDetail.ProductType = 2 ) OR  tblInvoice.InvoiceType =2 )
        WHERE tblInvoice.CompanyID = p_CompanyID
        AND tblInvoice.AccountID = p_accountID
        AND ( (tblInvoice.InvoiceType = 2) OR ( tblInvoice.InvoiceType = 1 AND tblInvoice.InvoiceStatus NOT IN ( 'cancel' , 'draft' , 'awaiting') )  )
        AND ( (p_StartDate = '0000-00-00' AND  DATE_FORMAT(tblInvoice.IssueDate,'%Y-%m-%d') < v_start_date ) OR  (p_StartDate != '0000-00-00' AND  DATE_FORMAT(tblInvoice.IssueDate,'%Y-%m-%d') < p_StartDate ) )
        AND tblInvoice.GrandTotal != 0
      Order by tblInvoice.IssueDate asc
		) t;

	
     
     INSERT into tmp_Payments
       SELECT
        DISTINCT
            tblPayment.InvoiceNo,
            DATE_FORMAT(tblPayment.PaymentDate, '%d/%m/%Y') AS PaymentDate,
            tblPayment.PaymentDate as IssueDate,
            tblPayment.Amount,
            tblPayment.PaymentID,
            tblPayment.PaymentType,
            tblPayment.InvoiceID
        FROM tblPayment
        WHERE 
            tblPayment.CompanyID = p_CompanyID
      AND tblPayment.AccountID = p_accountID
      AND tblPayment.Status = 'Approved'
        AND tblPayment.Recall = 0
      AND (p_StartDate = '0000-00-00' OR  (p_StartDate != '0000-00-00' AND  DATE_FORMAT(tblPayment.PaymentDate,'%Y-%m-%d') >= p_StartDate ) )
      AND (p_EndDate   = '0000-00-00' OR  (p_EndDate   != '0000-00-00' AND  DATE_FORMAT(tblPayment.PaymentDate,'%Y-%m-%d') <= p_EndDate ) )
    Order by tblPayment.PaymentDate desc;

	
	
    INSERT into tmp_Payments_broghtf
    	SELECT 
    	Amount,
    	PaymentType
    	FROM (
       SELECT
         DISTINCT
           tblPayment.InvoiceNo,
            DATE_FORMAT(tblPayment.PaymentDate, '%d/%m/%Y') AS PaymentDate,
            tblPayment.PaymentDate as IssueDate,
            tblPayment.Amount,
            tblPayment.PaymentID,
            tblPayment.PaymentType,
            tblPayment.InvoiceID
        FROM tblPayment
        WHERE 
            tblPayment.CompanyID = p_CompanyID
      AND tblPayment.AccountID = p_accountID
      AND tblPayment.Status = 'Approved'
        AND tblPayment.Recall = 0
      AND ( ( p_StartDate = '0000-00-00' AND DATE_FORMAT(tblPayment.PaymentDate,'%Y-%m-%d') < v_start_date )  OR  (p_StartDate != '0000-00-00' AND  DATE_FORMAT(tblPayment.PaymentDate,'%Y-%m-%d') < p_StartDate ) )
    Order by tblPayment.PaymentDate desc
	 ) t;
        
     
     INSERT INTO tmp_Disputes
       SELECT
            InvoiceNo,
        created_at,
            DisputeAmount,
            InvoiceType,
            DisputeID,
            AccountID,
            0 as InvoiceID
      FROM tblDispute 
        WHERE 
            CompanyID = p_CompanyID
      AND AccountID = p_accountID
      AND Status = 0 
      AND (p_StartDate = '0000-00-00' OR  (p_StartDate != '0000-00-00' AND  DATE_FORMAT(created_at,'%Y-%m-%d') >= p_StartDate ) )
      AND (p_EndDate   = '0000-00-00' OR  (p_EndDate   != '0000-00-00' AND  DATE_FORMAT(created_at,'%Y-%m-%d') <= p_EndDate ) )
      order by created_at;

        DROP TEMPORARY TABLE IF EXISTS tmp_Invoices_dup;
        DROP TEMPORARY TABLE IF EXISTS tmp_Disputes_dup;
        DROP TEMPORARY TABLE IF EXISTS tmp_Payments_dup;        

          
     CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Invoices_dup AS (SELECT * FROM tmp_Invoices);
     CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Disputes_dup AS (SELECT * FROM tmp_Disputes);
     CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Payments_dup AS (SELECT * FROM tmp_Payments);
              
     
    INSERT INTO tmp_InvoiceOutWithDisputes
      SELECT  
    InvoiceNo as InvoiceOut_InvoiceNo,
    PeriodCover as InvoiceOut_PeriodCover,
    IssueDate as InvoiceOut_IssueDate,
    Amount as InvoiceOut_Amount,
    ifnull(DisputeAmount,0) as InvoiceOut_DisputeAmount,
    DisputeID as InvoiceOut_DisputeID,
    AccountID as InvoiceOut_AccountID,
    InvoiceID as InvoiceOut_InvoiceID
    
    FROM
     (
      SELECT
      		DISTINCT
            iv.InvoiceNo,
            iv.PeriodCover,
            iv.IssueDate,
            iv.Amount,
            ds.DisputeAmount,
            ds.DisputeID,
       	   iv.AccountID,
       	   iv.InvoiceID
            
        FROM tmp_Invoices iv
      LEFT JOIN tmp_Disputes ds on ds.InvoiceNo = iv.InvoiceNo AND ds.InvoiceType = iv.InvoiceType  AND ds.InvoiceNo is not null
        WHERE 
        iv.InvoiceType = 1          
       
    
     UNION ALL
    
     SELECT
        DISTINCT
            ds.InvoiceNo,
            DATE_FORMAT(ds.created_at, '%d/%m/%Y') as PeriodCover,
            ds.created_at as IssueDate,
            0 as Amount,
            ds.DisputeAmount,
            ds.DisputeID,
        ds.AccountID,
        iv.InvoiceID
      FROM tmp_Disputes_dup ds 
      LEFT JOIN  tmp_Invoices_dup iv on ds.InvoiceNo = iv.InvoiceNo and iv.InvoiceType = ds.InvoiceType 
        WHERE 
        ds.InvoiceType = 1  
        AND iv.InvoiceNo is null
        
   
    ) tbl  ;
    
    
    
    
    SELECT 
      InvoiceOut_InvoiceNo,
      InvoiceOut_PeriodCover,
      ifnull(InvoiceOut_Amount,0) as InvoiceOut_Amount,
      InvoiceOut_DisputeAmount,
      InvoiceOut_DisputeID,
      ifnull(PaymentIn_PeriodCover,'') as PaymentIn_PeriodCover,
      PaymentIn_Amount,
      PaymentIn_PaymentID
    FROM
    (
      SELECT 
      DISTINCT
      InvoiceOut_InvoiceNo,
      InvoiceOut_PeriodCover,
      InvoiceOut_IssueDate,
      InvoiceOut_Amount,
      InvoiceOut_DisputeAmount,
      InvoiceOut_DisputeID,
      tmp_Payments.PaymentDate as PaymentIn_PeriodCover,
      tmp_Payments.Amount as PaymentIn_Amount,
      tmp_Payments.PaymentID as PaymentIn_PaymentID
      FROM tmp_InvoiceOutWithDisputes 
      INNER JOIN Ratemanagement3.tblAccount on tblAccount.AccountID = p_accountID
      INNER JOIN Ratemanagement3.tblAccountBilling ab ON ab.AccountID = tblAccount.AccountID
      
      LEFT JOIN tmp_Payments
      ON tmp_Payments.PaymentType = 'Payment In' AND  tmp_Payments.InvoiceNo != '' AND 
      tmp_Payments.InvoiceID = tmp_InvoiceOutWithDisputes.InvoiceOut_InvoiceID
		
      
      UNION ALL

      select 
      DISTINCT
      '' as InvoiceOut_InvoiceNo,
      '' as InvoiceOut_PeriodCover,
      tmp_Payments.IssueDate as InvoiceOut_IssueDate,
      0  as InvoiceOut_Amount,
      0 as InvoiceOut_DisputeAmount,
      0 as InvoiceOut_DisputeID,
      tmp_Payments.PaymentDate as PaymentIn_PeriodCover,
      tmp_Payments.Amount as PaymentIn_Amount,
      tmp_Payments.PaymentID as PaymentIn_PaymentID
      from tmp_Payments_dup as tmp_Payments
      where PaymentType = 'Payment In' 
      
		AND  tmp_Payments.InvoiceID = 0
  
    ) tbl
    order by InvoiceOut_IssueDate desc,InvoiceOut_InvoiceNo desc;
     
    
    


    
    INSERT INTO tmp_InvoiceInWithDisputes
    SELECT 
    InvoiceNo as InvoiceIn_InvoiceNo,
    PeriodCover as InvoiceIn_PeriodCover,
    IssueDate as InvoiceIn_IssueDate,
    Amount as InvoiceIn_Amount,
    ifnull(DisputeAmount,0) as InvoiceIn_DisputeAmount,
    DisputeID as InvoiceIn_DisputeID,
    AccountID as InvoiceIn_AccountID,
    InvoiceID as InvoiceIn_InvoiceID
    FROM
     (  SELECT
        DISTINCT
            iv.InvoiceNo,
            iv.PeriodCover,
            iv.IssueDate,
            iv.Amount,
            ds.DisputeAmount,
            ds.DisputeID,
            iv.AccountID,
            iv.InvoiceID
        FROM tmp_Invoices iv
      LEFT JOIN tmp_Disputes ds  on  ds.InvoiceNo = iv.InvoiceNo   AND ds.InvoiceType = iv.InvoiceType AND ds.InvoiceNo is not null
        WHERE 
        iv.InvoiceType = 2          
        
    
     UNION ALL
    
     SELECT
        DISTINCT
            ds.InvoiceNo,
            DATE_FORMAT(ds.created_at, '%d/%m/%Y') as PeriodCover,
            ds.created_at as IssueDate,
            0 as Amount,
            ds.DisputeAmount,
            ds.DisputeID,
            ds.AccountID,
            ds.InvoiceID
      From tmp_Disputes_dup ds 
        LEFT JOIN tmp_Invoices_dup iv on ds.InvoiceNo = iv.InvoiceNo AND iv.InvoiceType = ds.InvoiceType 
        WHERE  ds.InvoiceType = 2  
        AND iv.InvoiceNo is null
        
    )tbl;
      
      
     
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_InvoiceInWithDisputes_dup AS (SELECT * FROM tmp_InvoiceInWithDisputes);
 
     
    SELECT 
      InvoiceIn_InvoiceNo,
      InvoiceIn_PeriodCover,
      ifnull(InvoiceIn_Amount,0) as InvoiceIn_Amount,
      InvoiceIn_DisputeAmount,
      InvoiceIn_DisputeID,
      ifnull(PaymentOut_PeriodCover,'') as PaymentOut_PeriodCover,
      ifnull(PaymentOut_Amount,0) as PaymentOut_Amount,
      PaymentOut_PaymentID
    FROM
    (
      SELECT 
      DISTINCT
      InvoiceIn_InvoiceNo,
      InvoiceIn_PeriodCover,
      InvoiceIn_IssueDate,
      InvoiceIn_Amount,
      InvoiceIn_DisputeAmount,
      InvoiceIn_DisputeID,
      tmp_Payments.PaymentDate as PaymentOut_PeriodCover,
      tmp_Payments.Amount as PaymentOut_Amount,
      tmp_Payments.PaymentID as PaymentOut_PaymentID
      FROM tmp_InvoiceInWithDisputes 
      INNER JOIN Ratemanagement3.tblAccount on tblAccount.AccountID = p_accountID
      INNER JOIN Ratemanagement3.tblAccountBilling ab ON ab.AccountID = tblAccount.AccountID
      
      LEFT JOIN tmp_Payments ON tmp_Payments.PaymentType = 'Payment Out' AND tmp_InvoiceInWithDisputes.InvoiceIn_InvoiceNo = tmp_Payments.InvoiceNo 
      
      UNION ALL

      select 
      DISTINCT
      '' as InvoiceIn_InvoiceNo,
      '' as InvoiceIn_PeriodCover,
      tmp_Payments.IssueDate  as InvoiceIn_IssueDate,
      0 as InvoiceIn_Amount,
      0 as InvoiceIn_DisputeAmount,
      0 as InvoiceIn_DisputeID,
      tmp_Payments.PaymentDate as PaymentOut_PeriodCover,
      tmp_Payments.Amount as PaymentOut_Amount,
      tmp_Payments.PaymentID as PaymentOut_PaymentID
      from tmp_Payments_dup as tmp_Payments
       where PaymentType = 'Payment Out' AND 
      (
			tmp_Payments.InvoiceNo = ''		
			OR 
			tmp_Payments.InvoiceNo NOT in  ( select InvoiceIn_InvoiceNo from  tmp_InvoiceInWithDisputes_dup )
		)
  
    ) tbl
    order by InvoiceIn_IssueDate desc;

   
	
    select sum(Amount) as InvoiceOutAmountTotal 
    from tmp_Invoices where InvoiceType = 1 ;
    

    
    select sum(DisputeAmount) as InvoiceOutDisputeAmountTotal 
    from tmp_Disputes where InvoiceType = 1; 

    
    select sum(Amount) as PaymentInAmountTotal 
    from tmp_Payments where PaymentType = 'Payment In' ; 
    

    
    select sum(Amount) as InvoiceInAmountTotal 
    from tmp_Invoices where InvoiceType = 2 ;
    
    
    
    select sum(DisputeAmount) as InvoiceInDisputeAmountTotal
    from tmp_Disputes where InvoiceType = 2; 


    
    select sum(Amount) as PaymentOutAmountTotal 
    from tmp_Payments where PaymentType = 'Payment Out' ; 
    
	
	
	
	select sum(ifnull(Amount,0)) as InvoiceOutAmountTotal  into v_bf_InvoiceOutAmountTotal 
    from tmp_Invoices_broghtf where InvoiceType = 1 ;
    
    select sum(ifnull(Amount,0)) as PaymentInAmountTotal into v_bf_PaymentInAmountTotal
    from tmp_Payments_broghtf where PaymentType = 'Payment In' ; 
    
    select sum(ifnull(Amount,0)) as InvoiceInAmountTotal into v_bf_InvoiceInAmountTotal
    from tmp_Invoices_broghtf where InvoiceType = 2 ;
    
    select sum(ifnull(Amount,0)) as PaymentOutAmountTotal into v_bf_PaymentOutAmountTotal
    from tmp_Payments_broghtf where PaymentType = 'Payment Out' ; 

	SELECT (ifnull(v_bf_InvoiceOutAmountTotal,0) - ifnull(v_bf_PaymentInAmountTotal,0)) - ( ifnull(v_bf_InvoiceInAmountTotal,0) - ifnull(v_bf_PaymentOutAmountTotal,0)) as BroughtForwardOffset;
	
	


  SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
  
END//
DELIMITER ;
