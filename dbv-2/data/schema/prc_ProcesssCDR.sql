CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_ProcesssCDR`(
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
	IN `p_InboundTableID` INT
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
	
	/* update service against cli or ip */
	CALL prc_ProcessCDRService(p_CompanyID,p_processId,p_tbltempusagedetail_name);

	/* check service enable at gateway*/
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
	WHERE tblService.ServiceID > 0 AND tblService.CompanyID = "' , p_CompanyID , '" AND tblService.CompanyGatewayID > 0 AND ud.ServiceID IS NULL
	');
	
	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;

	/* update account and add new accounts and apply authentication rule*/
	CALL prc_ProcessCDRAccount(p_CompanyID,p_CompanyGatewayID,p_processId,p_tbltempusagedetail_name,p_NameFormat);

	IF ( ( SELECT COUNT(*) FROM tmp_Service_ ) > 0 OR p_OutboundTableID > 0)
	THEN

		/* rerate cdr service base */
		CALL prc_RerateOutboundService(p_processId,p_tbltempusagedetail_name,p_RateCDR,p_RateFormat,p_RateMethod,p_SpecifyRate,p_OutboundTableID);

	ELSE

		/* rerate cdr trunk base */
		CALL prc_RerateOutboundTrunk(p_processId,p_tbltempusagedetail_name,p_RateCDR,p_RateFormat,p_RateMethod,p_SpecifyRate);
	
	END IF;

	/* if rerate is off and acconts and trunks not setup update prefix from default codedeck*/
	IF p_RateCDR = 0 AND p_RateFormat = 2
	THEN 
		/* temp accounts and trunks*/
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

		/* get default code */
		CALL NeonRMDev.prc_getDefaultCodes(p_CompanyID);

		/* update prefix from default codes 
		 if rate format is prefix base not charge code*/
		CALL prc_updateDefaultPrefix(p_processId, p_tbltempusagedetail_name);

	END IF;

	/* inbound rerate process*/
	CALL prc_RerateInboundCalls(p_CompanyID,p_processId,p_tbltempusagedetail_name,p_RateCDR,p_RateMethod,p_SpecifyRate,p_InboundTableID);
	
	/* generate rerate error log*/
	CALL prc_CreateRerateLog(p_processId,p_tbltempusagedetail_name,p_RateCDR);
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 

END