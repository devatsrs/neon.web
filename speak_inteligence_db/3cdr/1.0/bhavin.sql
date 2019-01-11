USE `speakintelligentCDR`;

DROP PROCEDURE IF EXISTS `prc_unsetCDRUsageAccount`;
DELIMITER //
CREATE PROCEDURE `prc_unsetCDRUsageAccount`(
	IN `p_CompanyID` INT,
	IN `p_IPs` LONGTEXT,
	IN `p_StartDate` VARCHAR(100),
	IN `p_Confirm` INT,
	IN `p_ServiceID` INT,
	IN `p_AccountServiceID` INT
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
	FROM speakintelligentBilling.tblGatewayAccount GAC
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

			UPDATE speakintelligentBilling.tblGatewayAccount GAC
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