CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_reseller_ProcesssCDR`(
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
	
	/* created in prc_autoAddIP */
	DROP TEMPORARY TABLE IF EXISTS tmp_tblTempRateLog_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblTempRateLog_(
		`CompanyID` INT(11) NULL DEFAULT NULL,
		`CompanyGatewayID` INT(11) NULL DEFAULT NULL,
		`MessageType` INT(11) NOT NULL,
		`Message` VARCHAR(500) NOT NULL,
		`RateDate` DATE NOT NULL
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_Customers_;
	CREATE TEMPORARY TABLE tmp_Customers_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AccountID INT,
		CompanyGatewayID INT
	);


	DROP TEMPORARY TABLE IF EXISTS tmp_Service_;
	CREATE TEMPORARY TABLE tmp_Service_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		ServiceID INT
	);
	SET @stm = CONCAT('
	INSERT INTO tmp_Service_ (ServiceID)
	SELECT DISTINCT ServiceID FROM NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud WHERE ProcessID="' , p_processId , '" AND ServiceID > 0;
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;

	SET @stm = CONCAT('
	INSERT INTO tmp_Service_ (ServiceID)
	SELECT DISTINCT tblService.ServiceID
	FROM NeonRMDev.tblService
	LEFT JOIN  NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud
	ON tblService.ServiceID = ud.ServiceID AND ProcessID="' , p_processId , '"
	WHERE tblService.ServiceID > 0 AND tblService.CompanyGatewayID > 0 AND ud.ServiceID IS NULL
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;


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
		SELECT DISTINCT AccountID FROM NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud WHERE ProcessID="' , p_processId , '" AND AccountID IS NOT NULL AND TrunkID IS NOT NULL;
		');

		PREPARE stm FROM @stm;
		EXECUTE stm;
		DEALLOCATE PREPARE stm;


		CALL NeonRMDev.prc_getDefaultCodes(p_CompanyID);


		CALL prc_updateDefaultPrefix(p_processId, p_tbltempusagedetail_name);

	END IF;


	CALL prc_RerateInboundCalls(p_CompanyID,p_processId,p_tbltempusagedetail_name,p_RateCDR,p_RateMethod,p_SpecifyRate,p_InboundTableID);


	CALL prc_CreateRerateLog(p_processId,p_tbltempusagedetail_name,p_RateCDR);

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END