CREATE DEFINER=`neon-user-bhavin`@`117.247.87.156` PROCEDURE `prc_CreateRerateLog`(
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200),
	IN `p_RateCDR` INT
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

	SET @stm = CONCAT('
	INSERT INTO tmp_tblTempRateLog_ (CompanyID,CompanyGatewayID,MessageType,Message,RateDate)
	SELECT DISTINCT ud.CompanyID,ud.CompanyGatewayID,1,  CONCAT( "Account:  " , ga.AccountName ," - Gateway: ",cg.Title," - Doesnt exist in NEON") as Message ,DATE(NOW())
	FROM NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud
	INNER JOIN tblGatewayAccount ga 
		ON  ga.AccountName = ud.AccountName
		AND ga.AccountNumber = ud.AccountNumber
		AND ga.AccountCLI = ud.AccountCLI
		AND ga.AccountIP = ud.AccountIP
		AND ga.CompanyGatewayID = ud.CompanyGatewayID
		AND ga.ServiceID = ud.ServiceID
		AND ga.CompanyID = ud.CompanyID
	INNER JOIN NeonRMDev.tblCompanyGateway cg ON cg.CompanyGatewayID = ud.CompanyGatewayID
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
			FROM  NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud
			INNER JOIN NeonRMDev.tblAccount a on  ud.AccountID = a.AccountID
			LEFT JOIN NeonRMDev.tblService s on  s.ServiceID = ud.ServiceID
			WHERE ud.ProcessID = "' , p_processid  , '" and ud.is_inbound = 0 AND ud.is_rerated = 0 AND ud.billed_second <> 0 and ud.area_prefix = "Other"');
	
			PREPARE stmt FROM @stm;
			EXECUTE stmt;
			DEALLOCATE PREPARE stmt;
		
		ELSE

			SET @stm = CONCAT('
			INSERT INTO tmp_tblTempRateLog_ (CompanyID,CompanyGatewayID,MessageType,Message,RateDate)
			SELECT DISTINCT ud.CompanyID,ud.CompanyGatewayID,2,  CONCAT( "Account:  " , a.AccountName ," - Trunk: ",ud.trunk," - Unable to Rerate number ",IFNULL(ud.cld,"")," - No Matching prefix found") as Message ,DATE(NOW())
			FROM  NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud
			INNER JOIN NeonRMDev.tblAccount a on  ud.AccountID = a.AccountID
			WHERE ud.ProcessID = "' , p_processid  , '" and ud.is_inbound = 0 AND ud.is_rerated = 0 AND ud.billed_second <> 0 and ud.area_prefix = "Other"');
	
			PREPARE stmt FROM @stm;
			EXECUTE stmt;
			DEALLOCATE PREPARE stmt;
		
		END IF;

		SET @stm = CONCAT('
		INSERT INTO tmp_tblTempRateLog_ (CompanyID,CompanyGatewayID,MessageType,Message,RateDate)
		SELECT DISTINCT ud.CompanyID,ud.CompanyGatewayID,3,  CONCAT( "Account:  " , a.AccountName ,  " - Unable to Rerate number ",IFNULL(ud.cld,"")," - No Matching prefix found") as Message ,DATE(NOW())
		FROM  NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud
		INNER JOIN NeonRMDev.tblAccount a on  ud.AccountID = a.AccountID
		WHERE ud.ProcessID = "' , p_processid  , '" and ud.is_inbound = 1 AND ud.is_rerated = 0 AND ud.billed_second <> 0 and ud.area_prefix = "Other"');

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

		SET @stm = CONCAT('
		INSERT INTO NeonRMDev.tblTempRateLog (CompanyID,CompanyGatewayID,MessageType,Message,RateDate,SentStatus,created_at)
		SELECT rt.CompanyID,rt.CompanyGatewayID,rt.MessageType,rt.Message,rt.RateDate,0 as SentStatus,NOW() as created_at FROM tmp_tblTempRateLog_ rt
		LEFT JOIN NeonRMDev.tblTempRateLog rt2 
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

END