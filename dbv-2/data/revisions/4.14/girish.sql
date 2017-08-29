USE `RMBilling3`;


DROP PROCEDURE IF EXISTS `prc_autoAddIP`;
DELIMITER |
CREATE PROCEDURE `prc_autoAddIP`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT
)
BEGIN
	DROP TEMPORARY TABLE IF EXISTS tmp_tblTempRateLog_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblTempRateLog_(
		`CompanyID` INT(11) NULL DEFAULT NULL,
		`CompanyGatewayID` INT(11) NULL DEFAULT NULL,
		`MessageType` INT(11) NOT NULL,
		`Message` VARCHAR(500) NOT NULL,
		`RateDate` DATE NOT NULL	
	);

	INSERT IGNORE INTO tmp_tblTempRateLog_ (
		CompanyID,
		CompanyGatewayID,
		MessageType,
		Message,
		RateDate
	)
	SELECT 
		ga.CompanyID,
		ga.CompanyGatewayID,
		4,
		CONCAT('Account: ',ga.AccountName,' - IP: ',GROUP_CONCAT(ga.AccountIP)),
		DATE(NOW())
	FROM tblGatewayAccount ga
	INNER JOIN Ratemanagement3.tblAccount a 
		ON a.AccountName = ga.AccountName
		AND a.CompanyId = p_CompanyID
		AND a.AccountType = 1
		AND a.`Status` = 1
	WHERE  ga.CompanyID = p_CompanyID 
		AND ga.CompanyGatewayID = p_CompanyGatewayID
		AND ga.AccountID IS NULL 
		AND ga.AccountName <> ''
		AND ga.AccountIP <> ''
		AND ga.IsVendor IS NULL
	GROUP BY ga.CompanyID,ga.CompanyGatewayID,ga.AccountID,ga.AccountName,ga.ServiceID;
	
	INSERT INTO Ratemanagement3.tblTempRateLog (
		CompanyID,
		CompanyGatewayID,
		MessageType,
		Message,
		RateDate,
		SentStatus,
		created_at
	)
	SELECT
		CompanyID,
		CompanyGatewayID,
		MessageType,
		Message,
		RateDate,
		0,
		NOW()
	FROM tmp_tblTempRateLog_;

	/* update customer ips */
	UPDATE Ratemanagement3.tblAccountAuthenticate aa
	INNER JOIN (
		SELECT 
			ga.CompanyID,
			a.AccountID,
			CONCAT(IFNULL(MAX(aa.CustomerAuthValue),''),IF(MAX(aa.CustomerAuthValue) IS NULL,'',','),GROUP_CONCAT(ga.AccountIP)) AS CustomerAuthValue 
		FROM tblGatewayAccount ga
		INNER JOIN Ratemanagement3.tblAccount a 
			ON a.AccountName = ga.AccountName
			AND a.CompanyId = p_CompanyID
			AND a.AccountType = 1
			AND a.`Status` = 1
		INNER JOIN Ratemanagement3.tblAccountAuthenticate aa 
			ON a.AccountID = aa.AccountID
		WHERE  ga.CompanyID = p_CompanyID 
			AND ga.CompanyGatewayID = p_CompanyGatewayID
			AND ga.AccountID IS NULL 
			AND ga.AccountName <> ''
			AND ga.AccountIP <> ''
			AND ga.IsVendor IS NULL
			AND ( 
					 ( FIND_IN_SET(ga.AccountIP,aa.CustomerAuthValue) IS NULL OR FIND_IN_SET(ga.AccountIP,aa.CustomerAuthValue) = 0)
				AND ( FIND_IN_SET(ga.AccountIP,aa.VendorAuthValue) IS NULL OR FIND_IN_SET(ga.AccountIP,aa.VendorAuthValue) = 0)
				 )
		GROUP BY ga.CompanyID,ga.CompanyGatewayID,a.AccountID,ga.AccountName,ga.ServiceID
	) TBl
	ON TBl.AccountID = aa.AccountID
	SET aa.CustomerAuthValue = TBl.CustomerAuthValue;

	/* update vendor ips */
	UPDATE Ratemanagement3.tblAccountAuthenticate aa
	INNER JOIN (
		SELECT
			ga.CompanyID,
			a.AccountID,
			CONCAT(IFNULL(MAX(aa.VendorAuthValue),''),IF(MAX(aa.VendorAuthValue) IS NULL,'',','),GROUP_CONCAT(ga.AccountIP)) AS VendorAuthValue 
		FROM tblGatewayAccount ga
		INNER JOIN Ratemanagement3.tblAccount a 
			ON a.AccountName = ga.AccountName
			AND a.CompanyId = p_CompanyID
			AND a.AccountType = 1
			AND a.`Status` = 1
		INNER JOIN Ratemanagement3.tblAccountAuthenticate aa 
			ON a.AccountID = aa.AccountID
		WHERE  ga.CompanyID = p_CompanyID 
			AND ga.CompanyGatewayID = p_CompanyGatewayID
			AND ga.AccountID IS NULL 
			AND ga.AccountName <> ''
			AND ga.AccountIP <> ''
			AND ga.IsVendor = 1
			AND ( 
					 ( FIND_IN_SET(ga.AccountIP,aa.CustomerAuthValue) IS NULL OR FIND_IN_SET(ga.AccountIP,aa.CustomerAuthValue) = 0)
				AND ( FIND_IN_SET(ga.AccountIP,aa.VendorAuthValue) IS NULL OR FIND_IN_SET(ga.AccountIP,aa.VendorAuthValue) = 0)
				 )
		GROUP BY ga.CompanyID,ga.CompanyGatewayID,a.AccountID,ga.AccountName,ga.ServiceID
	) TBl
	ON TBl.AccountID = aa.AccountID
	SET aa.VendorAuthValue = TBl.VendorAuthValue;

	/* insert customer ips */
	INSERT IGNORE INTO Ratemanagement3.tblAccountAuthenticate (
		CompanyID,
		AccountID,
		CustomerAuthRule,
		CustomerAuthValue,
		ServiceID
	)
	SELECT 
		ga.CompanyID,
		a.AccountID,
		'IP',
		GROUP_CONCAT(ga.AccountIP),
		ga.ServiceID
	FROM tblGatewayAccount ga
	INNER JOIN Ratemanagement3.tblAccount a 
		ON a.AccountName = ga.AccountName
		AND a.CompanyId = p_CompanyID
		AND a.AccountType = 1
		AND a.`Status` = 1
	LEFT JOIN Ratemanagement3.tblAccountAuthenticate aa 
		ON a.AccountID = aa.AccountID
	WHERE  ga.CompanyID = p_CompanyID 
		AND ga.CompanyGatewayID = p_CompanyGatewayID
		AND ga.AccountID IS NULL 
		AND ga.AccountName <> ''
		AND ga.AccountIP <> ''
		AND ga.IsVendor IS NULL
		AND aa.AccountID IS NULL
	GROUP BY ga.CompanyID,ga.CompanyGatewayID,a.AccountID,ga.AccountName,ga.ServiceID;

	/* insert vendor ips */
	INSERT IGNORE INTO Ratemanagement3.tblAccountAuthenticate (
		CompanyID,
		AccountID,
		VendorAuthRule,
		VendorAuthValue,
		ServiceID
	)
	SELECT 
		ga.CompanyID,
		a.AccountID,
		'IP',
		GROUP_CONCAT(ga.AccountIP),
		ga.ServiceID
	FROM tblGatewayAccount ga
	INNER JOIN Ratemanagement3.tblAccount a 
		ON a.AccountName = ga.AccountName
		AND a.CompanyId = p_CompanyID
		AND a.AccountType = 1
		AND a.`Status` = 1
	LEFT JOIN Ratemanagement3.tblAccountAuthenticate aa 
		ON a.AccountID = aa.AccountID
	WHERE  ga.CompanyID = p_CompanyID 
		AND ga.CompanyGatewayID = p_CompanyGatewayID
		AND ga.AccountID IS NULL 
		AND ga.AccountName <> ''
		AND ga.AccountIP <> ''
		AND ga.IsVendor = 1
		AND aa.AccountID IS NULL
	GROUP BY ga.CompanyID,ga.CompanyGatewayID,a.AccountID,ga.AccountName,ga.ServiceID;

END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_CreateRerateLog`;
DELIMITER |
CREATE PROCEDURE `prc_CreateRerateLog`(
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200),
	IN `p_RateCDR` INT
)
BEGIN

	SET @stm = CONCAT('
	INSERT INTO tmp_tblTempRateLog_ (CompanyID,CompanyGatewayID,MessageType,Message,RateDate)
	SELECT DISTINCT ud.CompanyID,ud.CompanyGatewayID,1,  CONCAT( " Account Name : ( " , ga.AccountName ," ) Number ( " , ga.AccountNumber ," ) IP  ( " , ga.AccountIP ," ) CLI  ( " , ga.AccountCLI," ) - Gateway: ",cg.Title," - Doesnt exist in NEON") as Message ,DATE(NOW())
	FROM RMCDR3.`' , p_tbltempusagedetail_name , '` ud
	INNER JOIN tblGatewayAccount ga 
		ON  ga.AccountName = ud.AccountName
		AND ga.AccountNumber = ud.AccountNumber
		AND ga.AccountCLI = ud.AccountCLI
		AND ga.AccountIP = ud.AccountIP
		AND ga.CompanyGatewayID = ud.CompanyGatewayID
		AND ga.CompanyID = ud.CompanyID
		AND ga.ServiceID = ud.ServiceID
	INNER JOIN Ratemanagement3.tblCompanyGateway cg ON cg.CompanyGatewayID = ud.CompanyGatewayID
	WHERE ud.ProcessID = "' , p_processid  , '" and ud.AccountID IS NULL');

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	IF p_RateCDR = 1
	THEN
	
		IF ( SELECT COUNT(*) FROM tmp_Service_ ) > 0
		THEN
		
			SET @stm = CONCAT('
			INSERT INTO tmp_tblTempRateLog_ (CompanyID,CompanyGatewayID,MessageType,Message,RateDate)
			SELECT DISTINCT ud.CompanyID,ud.CompanyGatewayID,2,  CONCAT( "Account:  " , a.AccountName ," - Service: ",IFNULL(s.ServiceName,"")," - Unable to Rerate number ",IFNULL(ud.cld,"")," - No Matching prefix found") as Message ,DATE(NOW())
			FROM  RMCDR3.`' , p_tbltempusagedetail_name , '` ud
			INNER JOIN Ratemanagement3.tblAccount a on  ud.AccountID = a.AccountID
			LEFT JOIN Ratemanagement3.tblService s on  s.ServiceID = ud.ServiceID
			WHERE ud.ProcessID = "' , p_processid  , '" and ud.is_inbound = 0 AND ud.is_rerated = 0 AND ud.billed_second <> 0 and ud.area_prefix = "Other"');
	
			PREPARE stmt FROM @stm;
			EXECUTE stmt;
			DEALLOCATE PREPARE stmt;
		
		ELSE

			SET @stm = CONCAT('
			INSERT INTO tmp_tblTempRateLog_ (CompanyID,CompanyGatewayID,MessageType,Message,RateDate)
			SELECT DISTINCT ud.CompanyID,ud.CompanyGatewayID,2,  CONCAT( "Account:  " , a.AccountName ," - Trunk: ",ud.trunk," - Unable to Rerate number ",IFNULL(ud.cld,"")," - No Matching prefix found") as Message ,DATE(NOW())
			FROM  RMCDR3.`' , p_tbltempusagedetail_name , '` ud
			INNER JOIN Ratemanagement3.tblAccount a on  ud.AccountID = a.AccountID
			WHERE ud.ProcessID = "' , p_processid  , '" and ud.is_inbound = 0 AND ud.is_rerated = 0 AND ud.billed_second <> 0 and ud.area_prefix = "Other"');
	
			PREPARE stmt FROM @stm;
			EXECUTE stmt;
			DEALLOCATE PREPARE stmt;
		
		END IF;

		SET @stm = CONCAT('
		INSERT INTO tmp_tblTempRateLog_ (CompanyID,CompanyGatewayID,MessageType,Message,RateDate)
		SELECT DISTINCT ud.CompanyID,ud.CompanyGatewayID,3,  CONCAT( "Account:  " , a.AccountName ,  " - Unable to Rerate number ",IFNULL(ud.cld,"")," - No Matching prefix found") as Message ,DATE(NOW())
		FROM  RMCDR3.`' , p_tbltempusagedetail_name , '` ud
		INNER JOIN Ratemanagement3.tblAccount a on  ud.AccountID = a.AccountID
		WHERE ud.ProcessID = "' , p_processid  , '" and ud.is_inbound = 1 AND ud.is_rerated = 0 AND ud.billed_second <> 0 and ud.area_prefix = "Other"');

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

		SET @stm = CONCAT('
		INSERT INTO Ratemanagement3.tblTempRateLog (CompanyID,CompanyGatewayID,MessageType,Message,RateDate,SentStatus,created_at)
		SELECT rt.CompanyID,rt.CompanyGatewayID,rt.MessageType,rt.Message,rt.RateDate,0 as SentStatus,NOW() as created_at FROM tmp_tblTempRateLog_ rt
		LEFT JOIN Ratemanagement3.tblTempRateLog rt2 
			ON rt.CompanyID = rt2.CompanyID
			AND rt.CompanyGatewayID = rt2.CompanyGatewayID
			AND rt.MessageType = rt2.MessageType
			AND rt.Message = rt2.Message
			AND rt.RateDate = rt2.RateDate
		WHERE rt2.TempRateLogID IS NULL;
		');

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

	END IF;

	SELECT DISTINCT Message FROM tmp_tblTempRateLog_;

END|
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_ProcesssCDR`;
DELIMITER |
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
	IN `p_InboundTableID` INT
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
	
	/* update service against cli or ip */
	CALL prc_autoAddIP(p_CompanyID,p_CompanyGatewayID);
	CALL prc_ProcessCDRService(p_CompanyID,p_processId,p_tbltempusagedetail_name);

	/* check service enable at gateway*/
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
		SELECT DISTINCT AccountID FROM RMCDR3.`' , p_tbltempusagedetail_name , '` ud WHERE ProcessID="' , p_processId , '" AND AccountID IS NOT NULL AND TrunkID IS NOT NULL;
		');

		PREPARE stm FROM @stm;
		EXECUTE stm;
		DEALLOCATE PREPARE stm;

		/* get default code */
		CALL Ratemanagement3.prc_getDefaultCodes(p_CompanyID);

		/* update prefix from default codes 
		 if rate format is prefix base not charge code*/
		CALL prc_updateDefaultPrefix(p_processId, p_tbltempusagedetail_name);

	END IF;

	/* inbound rerate process*/
	CALL prc_RerateInboundCalls(p_CompanyID,p_processId,p_tbltempusagedetail_name,p_RateCDR,p_RateMethod,p_SpecifyRate,p_InboundTableID);
	
	/* generate rerate error log*/
	CALL prc_CreateRerateLog(p_processId,p_tbltempusagedetail_name,p_RateCDR);
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 

END|
DELIMITER ;
