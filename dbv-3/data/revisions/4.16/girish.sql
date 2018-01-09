USE RMCDR3;

DROP PROCEDURE IF EXISTS `prc_UniqueIDCallID`;
DELIMITER //
CREATE PROCEDURE `prc_UniqueIDCallID`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_ProcessID` VARCHAR(200),
	IN `p_tbltempusagedetail_name` VARCHAR(200)
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	SET @stm1 = CONCAT('
	INSERT INTO tblUCall (UUID)
	SELECT DISTINCT tud.UUID FROM  `' , p_tbltempusagedetail_name , '` tud
	LEFT JOIN tblUCall ON tud.UUID = tblUCall.UUID
	WHERE UID IS NULL
	AND  tud.UUID IS NOT NULL
	AND  tud.CompanyID = "' , p_CompanyID , '"
	AND  tud.CompanyGatewayID = "' , p_CompanyGatewayID , '"
	AND  tud.ProcessID = "' , p_processId , '";
	');

	PREPARE stmt1 FROM @stm1;
	EXECUTE stmt1;
	DEALLOCATE PREPARE stmt1;

	SET @stm2 = CONCAT('
	UPDATE `' , p_tbltempusagedetail_name , '` tud
	INNER JOIN tblUCall ON tud.UUID = tblUCall.UUID
	SET  tud.ID = tblUCall.UID
	WHERE tud.CompanyID = "' , p_CompanyID , '"
	AND  tud.UUID IS NOT NULL
	AND  tud.CompanyGatewayID = "' , p_CompanyGatewayID , '"
	AND  tud.ProcessID = "' , p_processId , '";
	');

	PREPARE stmt2 FROM @stm2;
	EXECUTE stmt2;
	DEALLOCATE PREPARE stmt2;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 

END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_InvoiceManagementReport`;
DELIMITER //
CREATE PROCEDURE `prc_InvoiceManagementReport`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	/* top 10 Longest Calls*/
	SELECT 
		cli as col1,
		cld as col2,
		ROUND(billed_duration/60) as col3,
		cost as col4
	FROM tblUsageDetails  ud
	INNER JOIN tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	WHERE uh.CompanyID = p_CompanyID
	AND uh.AccountID IS NOT NULL
	AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
	AND StartDate BETWEEN p_StartDate AND p_EndDate
	ORDER BY billed_duration DESC LIMIT 10;

	/* top 10 Most Expensive Calls*/
	SELECT 
		cli as col1,
		cld as col2,
		ROUND(billed_duration/60) as col3,
		cost as col4
	FROM tblUsageDetails  ud
	INNER JOIN tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	WHERE uh.CompanyID = p_CompanyID
	AND uh.AccountID IS NOT NULL
	AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
	AND StartDate BETWEEN p_StartDate AND p_EndDate
	ORDER BY cost DESC LIMIT 10;

	/* top 10 Most Dialled Number*/
	SELECT 
		cld as col1,
		count(*) AS col2,
		ROUND(SUM(billed_duration)/60) AS col3,
		SUM(cost) AS col4
	FROM tblUsageDetails  ud
	INNER JOIN tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	WHERE uh.CompanyID = p_CompanyID
	AND uh.AccountID IS NOT NULL
	AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
	AND StartDate BETWEEN p_StartDate AND p_EndDate
	GROUP BY cld
	ORDER BY col2 DESC
	LIMIT 10;

	/* Daily Summary */
	SELECT 
		DATE(StartDate) as col1,
		count(*) AS col2,
		ROUND(SUM(billed_duration)/60) AS col3,
		SUM(cost) AS col4
	FROM tblUsageDetails  ud
	INNER JOIN tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	WHERE uh.CompanyID = p_CompanyID
	AND uh.AccountID IS NOT NULL
	AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
	AND StartDate BETWEEN p_StartDate AND p_EndDate
	GROUP BY StartDate
	ORDER BY StartDate;

	/* Usage by Category */	
	SELECT
		(SELECT Description
		FROM Ratemanagement3.tblRate r
		WHERE  r.Code = ud.area_prefix limit 1 )
		AS col1,
		COUNT(UsageDetailID) AS col2,
		CONCAT( FLOOR(SUM(billed_duration ) / 60),':' , SUM(billed_duration ) % 60) AS col3,
		SUM(cost) AS col4
	FROM tblUsageDetails  ud
	INNER JOIN tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	WHERE uh.CompanyID = p_CompanyID
	AND uh.AccountID IS NOT NULL
	AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
	AND StartDate BETWEEN p_StartDate AND p_EndDate
	GROUP BY col1;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 

END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_unsetCDRUsageAccount`;
DELIMITER //
CREATE PROCEDURE `prc_unsetCDRUsageAccount`(
	IN `p_CompanyID` INT,
	IN `p_IPs` LONGTEXT,
	IN `p_StartDate` VARCHAR(100),
	IN `p_Confirm` INT,
	IN `p_ServiceID` INT
)
BEGIN

	DECLARE v_AccountID int;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_AccountID = 0;

	DROP TEMPORARY TABLE IF EXISTS tmp_account_;
	CREATE TEMPORARY TABLE tmp_account_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		GatewayAccountPKID INT ,
		AccountID INT ,
		UNIQUE KEY `UK` (GatewayAccountPKID)
	);

	INSERT INTO tmp_account_ (GatewayAccountPKID,AccountID)
	SELECT 
		DISTINCT GAC.GatewayAccountPKID,AccountID
	FROM RMBilling3.tblGatewayAccount GAC
	WHERE GAC.CompanyID = p_CompanyID
		AND GAC.ServiceID = p_ServiceID
		AND AccountID IS NOT NULL
		AND ( FIND_IN_SET(GAC.AccountIP, p_IPs) > 0 OR FIND_IN_SET(GAC.AccountCLI, p_IPs) > 0 );

	INSERT IGNORE INTO tmp_account_  (GatewayAccountPKID,AccountID)
	SELECT 
		DISTINCT GatewayAccountPKID,AccountID
	FROM tblUsageHeader UH
	WHERE UH.CompanyID = p_CompanyID
		AND UH.ServiceID = p_ServiceID
		AND AccountID IS NOT NULL
		AND  FIND_IN_SET(UH.GatewayAccountID, p_IPs) > 0;

	INSERT IGNORE INTO tmp_account_  (GatewayAccountPKID,AccountID)
	SELECT 
		DISTINCT GatewayAccountPKID,AccountID
	FROM tblVendorCDRHeader VH
	WHERE VH.CompanyID = p_CompanyID
		AND VH.ServiceID = p_ServiceID
		AND AccountID IS NOT NULL
		AND  FIND_IN_SET(VH.GatewayAccountID, p_IPs) > 0;

	SELECT AccountID INTO v_AccountID FROM tmp_account_ LIMIT 1;		

	IF (SELECT COUNT(*) FROM tmp_account_) > 0 AND p_Confirm = 1 THEN

			UPDATE RMBilling3.tblGatewayAccount GAC
			INNER JOIN tmp_account_ a ON a.GatewayAccountPKID = GAC.GatewayAccountPKID
				SET GAC.AccountID = NULL
			WHERE GAC.CompanyID = p_CompanyID
			AND GAC.ServiceID = p_ServiceID;

			UPDATE tblUsageHeader 
			INNER JOIN tmp_account_ a ON a.GatewayAccountPKID = tblUsageHeader.GatewayAccountPKID
				SET tblUsageHeader.AccountID = NULL
			WHERE CompanyID = p_CompanyID
			AND ServiceID = p_ServiceID
			AND StartDate >= p_StartDate;

			UPDATE tblVendorCDRHeader 
			INNER JOIN tmp_account_ a ON a.GatewayAccountPKID = tblVendorCDRHeader.GatewayAccountPKID
				SET tblVendorCDRHeader.AccountID = NULL
			WHERE CompanyID = p_CompanyID
			AND ServiceID = p_ServiceID
			AND StartDate >= p_StartDate;

			SET v_AccountID = -1;

	END IF;

	SELECT v_AccountID as `Status`;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;