Use RMBilling3;


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

	CALL Ratemanagement3.prc_UpdateMysqlPID(p_processId);

	CALL prc_autoAddIP(p_CompanyID,p_CompanyGatewayID);

	CALL prc_ProcessCDRService(p_CompanyID,p_processId,p_tbltempusagedetail_name);

	CALL prc_updateTempCDRTimeZones(p_tbltempusagedetail_name);


	DROP TEMPORARY TABLE IF EXISTS tmp_Customers_;
	CREATE TEMPORARY TABLE tmp_Customers_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AccountID INT,
		CompanyGatewayID INT
	);

	IF p_RerateAccounts!=0
	THEN

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




	IF ( ( SELECT COUNT(*) FROM tmp_Service_ ) > 0 OR p_OutboundTableID > 0)
	THEN


		CALL prc_RerateOutboundService(p_processId,p_tbltempusagedetail_name,p_RateCDR,p_RateFormat,p_RateMethod,p_SpecifyRate,p_OutboundTableID);

	ELSE


		CALL prc_RerateOutboundTrunk(p_processId,p_tbltempusagedetail_name,p_RateCDR,p_RateFormat,p_RateMethod,p_SpecifyRate);


		CALL prc_autoUpdateTrunk(p_CompanyID,p_CompanyGatewayID);

	END IF;





	IF ((p_RateCDR = 0 AND p_RateFormat = 2) OR (p_RerateAccounts!= 0 & p_RateCDR = 1 AND p_RateFormat = 2))
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



	IF (  p_RateCDR = 1 )
	THEN


		SET @stm = CONCAT('
	UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` ud
	INNER JOIN  RMCDR3.`' , p_tbltempusagedetail_name ,'_Retail' , '` udr ON ud.TempUsageDetailID = udr.TempUsageDetailID AND ud.ProcessID = udr.ProcessID
	SET cost = 0
  WHERE ud.ProcessID="' , p_processId , '" AND
													  (
														udr.cc_type = 4
														  OR
													    CHAR_LENGTH(cld) <= 6
													 );
	');

		PREPARE stm FROM @stm;
		EXECUTE stm;
		DEALLOCATE PREPARE stm;




	END IF;




		SET @stm = CONCAT('
						UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` ud
						INNER JOIN  RMCDR3.`' , p_tbltempusagedetail_name ,'_Retail' , '` udr ON ud.TempUsageDetailID = udr.TempUsageDetailID AND ud.ProcessID = udr.ProcessID
						SET area_prefix = "Other" , is_rerated = 1
						WHERE ud.ProcessID="' , p_processId , '" AND  CHAR_LENGTH(cld) <= 6 ;	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;


	call prc_UpdateCDRRounding(p_tbltempusagedetail_name,p_processId);


	CALL prc_CreateRerateLog(p_processId,p_tbltempusagedetail_name,p_RateCDR);

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_ProcesssVCDR`;
DELIMITER //
CREATE PROCEDURE `prc_ProcesssVCDR`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200),
	IN `p_RateCDR` INT,
	IN `p_RateFormat` INT,
	IN `p_NameFormat` VARCHAR(50),
	IN `p_RateMethod` VARCHAR(50),
	IN `p_SpecifyRate` DECIMAL(18,6),
	IN `p_RerateAccounts` INT
)
BEGIN
	DECLARE v_rowCount_ INT;
	DECLARE v_pointer_ INT;
	DECLARE v_AccountID_ INT;
	DECLARE v_CDRUpload_ INT;
	DECLARE v_TrunkID_ INT;
	DECLARE v_NewAccountIDCount_ INT;
	DECLARE v_VendorIDs_ TEXT DEFAULT '';
	DECLARE v_VendorIDs_Count_ INT DEFAULT 0;
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	CALL prc_ProcessCDRService(p_CompanyID,p_processId,p_tbltempusagedetail_name);

	CALL prc_updateTempCDRTimeZones(p_tbltempusagedetail_name);


	DROP TEMPORARY TABLE IF EXISTS tmp_Vendors_;
	CREATE TEMPORARY TABLE tmp_Vendors_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AccountID INT,
		CompanyGatewayID INT
	);

	IF p_RerateAccounts!=0
	THEN
      SET @sql1 = concat("insert into tmp_Vendors_ (AccountID) values ('", replace(( select TRIM(REPLACE(group_concat(distinct IFNULL(REPLACE(REPLACE(json_extract(Settings, '$.Accounts'), '[', ''), ']', ''),0)),'"','')) as AccountID from Ratemanagement3.tblCompanyGateway WHERE CompanyGatewayID=p_CompanyGatewayID), ",", "'),('"),"');");
      PREPARE stmt1 FROM @sql1;
      EXECUTE stmt1;
      DEALLOCATE PREPARE stmt1;
      DELETE FROM tmp_Vendors_ WHERE AccountID=0;
      UPDATE tmp_Vendors_ SET CompanyGatewayID=p_CompanyGatewayID WHERE 1;
  END IF;

	SELECT GROUP_CONCAT(AccountID) INTO v_VendorIDs_ FROM tmp_Vendors_ GROUP BY CompanyGatewayID;
	SELECT COUNT(*) INTO v_VendorIDs_Count_ FROM tmp_Vendors_;


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

	DROP TEMPORARY TABLE IF EXISTS tmp_tblTempRateLog_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblTempRateLog_(
		`CompanyID` INT(11) NULL DEFAULT NULL,
		`CompanyGatewayID` INT(11) NULL DEFAULT NULL,
		`MessageType` INT(11) NOT NULL,
		`Message` VARCHAR(500) NOT NULL,
		`RateDate` DATE NOT NULL
	);


	SET @stm = CONCAT('
	INSERT INTO tblGatewayAccount (CompanyID, CompanyGatewayID, GatewayAccountID, AccountName,AccountNumber,AccountCLI,AccountIP,ServiceID,IsVendor)
	SELECT
		DISTINCT
		ud.CompanyID,
		ud.CompanyGatewayID,
		ud.GatewayAccountID,
		ud.AccountName,
		ud.AccountNumber,
		ud.AccountCLI,
		ud.AccountIP,
		ud.ServiceID,
		1 as IsVendor
	FROM RMCDR3.' , p_tbltempusagedetail_name , ' ud
	LEFT JOIN tblGatewayAccount ga
		ON ga.AccountName = ud.AccountName
		AND ga.AccountNumber = ud.AccountNumber
		AND ga.AccountCLI = ud.AccountCLI
		AND ga.AccountIP = ud.AccountIP
		AND ga.CompanyGatewayID = ud.CompanyGatewayID
		AND ga.ServiceID = ud.ServiceID
		AND ga.CompanyID = ud.CompanyID
	WHERE ProcessID =  "' , p_processId , '"
		AND ga.GatewayAccountID IS NULL
		AND ud.GatewayAccountID IS NOT NULL;
	');

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;


	SET @stm = CONCAT('
	UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` uh
	INNER JOIN tblGatewayAccount ga
		ON  ga.CompanyID = uh.CompanyID
		AND ga.CompanyGatewayID = uh.CompanyGatewayID
		AND ga.AccountName = uh.AccountName
		AND ga.AccountNumber = uh.AccountNumber
		AND ga.AccountCLI = uh.AccountCLI
		AND ga.AccountIP = uh.AccountIP
		AND ga.ServiceID = uh.ServiceID
	SET uh.GatewayAccountPKID = ga.GatewayAccountPKID
	WHERE uh.CompanyID = ' ,  p_CompanyID , '
	AND uh.ProcessID = "' , p_processId , '" ;
	');
	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;


	CALL  prc_getActiveGatewayAccount(p_CompanyID,p_CompanyGatewayID,p_NameFormat);


	SET @stm = CONCAT('
	UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` uh
	INNER JOIN tblGatewayAccount ga
		ON  ga.CompanyID = uh.CompanyID
		AND ga.CompanyGatewayID = uh.CompanyGatewayID
		AND ga.AccountName = uh.AccountName
		AND ga.AccountNumber = uh.AccountNumber
		AND ga.AccountCLI = uh.AccountCLI
		AND ga.AccountIP = uh.AccountIP
		AND ga.ServiceID = uh.ServiceID
	SET uh.AccountID = ga.AccountID
	WHERE uh.AccountID IS NULL
	AND ga.AccountID is not null
	AND uh.CompanyID = ' ,  p_CompanyID , '
	AND uh.ProcessID = "' , p_processId , '" ;
	');
	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SELECT COUNT(*) INTO v_NewAccountIDCount_
	FROM RMCDR3.tblVendorCDRHeader uh
	INNER JOIN tblGatewayAccount ga
		ON  uh.GatewayAccountPKID = ga.GatewayAccountPKID
	WHERE uh.AccountID IS NULL
	AND ga.AccountID is not null
	AND uh.CompanyID = p_CompanyID
	AND uh.CompanyGatewayID = p_CompanyGatewayID;

	IF v_NewAccountIDCount_ > 0
	THEN

		UPDATE RMCDR3.tblVendorCDRHeader uh
		INNER JOIN tblGatewayAccount ga
			ON  uh.GatewayAccountPKID = ga.GatewayAccountPKID
		SET uh.AccountID = ga.AccountID
		WHERE uh.AccountID IS NULL
		AND ga.AccountID is not null
		AND uh.CompanyID = p_CompanyID
		AND uh.CompanyGatewayID = p_CompanyGatewayID;

	END IF;


	DROP TEMPORARY TABLE IF EXISTS tmp_AccountTrunkCdrUpload_;
	CREATE TEMPORARY TABLE tmp_AccountTrunkCdrUpload_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AccountID INT,
		TrunkID INT
	);
	SET @stm = CONCAT('
	INSERT INTO tmp_AccountTrunkCdrUpload_(AccountID,TrunkID)
	SELECT DISTINCT AccountID,TrunkID FROM RMCDR3.`' , p_tbltempusagedetail_name , '` ud WHERE ProcessID="' , p_processId , '" AND AccountID IS NOT NULL AND TrunkID IS NOT NULL;
	');

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET v_CDRUpload_ = (SELECT COUNT(*) FROM tmp_AccountTrunkCdrUpload_);

	IF v_CDRUpload_ > 0
	THEN

		SET @stm = CONCAT('
		UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` ud
		INNER JOIN Ratemanagement3.tblVendorTrunk ct
			ON ct.AccountID = ud.AccountID AND ct.TrunkID = ud.TrunkID AND ct.Status =1
		INNER JOIN Ratemanagement3.tblTrunk t
			ON t.TrunkID = ct.TrunkID
			SET ud.UseInBilling=ct.UseInBilling,ud.TrunkPrefix = ct.Prefix
		WHERE  ud.ProcessID = "' , p_processId , '";
		');

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

	END IF;




	IF p_RateFormat = 2
	THEN


		SET @stm = CONCAT('
		UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` ud
		INNER JOIN Ratemanagement3.tblVendorTrunk ct
			ON ct.AccountID = ud.AccountID AND ct.Status =1
			AND ct.UseInBilling = 1 AND (cld LIKE CONCAT(ct.Prefix , "%") OR (ct.Prefix IS NOT NULL AND ct.Prefix <> "" AND vendor_trunk_type = ct.Prefix))
		INNER JOIN Ratemanagement3.tblTrunk t
			ON t.TrunkID = ct.TrunkID
			SET ud.trunk = t.Trunk,ud.TrunkID =t.TrunkID,ud.UseInBilling=ct.UseInBilling,ud.TrunkPrefix = ct.Prefix,area_prefix = TRIM(LEADING ct.Prefix FROM area_prefix )
		WHERE  ud.ProcessID = "' , p_processId , '" AND ud.TrunkID IS NULL;
		');

		PREPARE stm FROM @stm;
		EXECUTE stm;
		DEALLOCATE PREPARE stm;


		SET @stm = CONCAT('
		UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` ud
		INNER JOIN Ratemanagement3.tblVendorTrunk ct
			ON ct.AccountID = ud.AccountID AND ct.Status =1
			AND ct.UseInBilling = 0
		INNER JOIN Ratemanagement3.tblTrunk t
			ON t.TrunkID = ct.TrunkID
			SET ud.trunk = t.Trunk,ud.TrunkID =t.TrunkID,ud.UseInBilling=ct.UseInBilling
		WHERE  ud.ProcessID = "' , p_processId , '" AND ud.TrunkID IS NULL;
		');

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

	END IF;


	IF p_RateCDR = 1 AND v_VendorIDs_Count_ = 0
	THEN

		SET @stm = CONCAT('UPDATE   RMCDR3.`' , p_tbltempusagedetail_name , '` ud SET buying_cost = 0,is_rerated=0  WHERE ProcessID = "',p_processId,'" AND ( AccountID IS NULL OR TrunkID IS NULL ) ') ;

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

	ELSEIF p_RateCDR = 1 AND v_VendorIDs_Count_ > 0
	THEN

		SET @stm = CONCAT('UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` ud SET buying_cost = 0,is_rerated=0, area_prefix="Other"  WHERE ProcessID = "',p_processId,'" AND ( FIND_IN_SET(AccountID,"',v_VendorIDs_,'")>0) ') ;

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

		SET @stm = CONCAT('UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` ud SET is_rerated=1  WHERE ProcessID = "',p_processId,'" AND ( FIND_IN_SET(AccountID,"',v_VendorIDs_,'")=0) ') ;

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

	END IF;


	DROP TEMPORARY TABLE IF EXISTS tmp_AccountTrunk_;
	CREATE TEMPORARY TABLE tmp_AccountTrunk_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AccountID INT,
		TrunkID INT
	);
	SET @stm = CONCAT('
	INSERT INTO tmp_AccountTrunk_(AccountID,TrunkID)
	SELECT DISTINCT AccountID,TrunkID FROM RMCDR3.`' , p_tbltempusagedetail_name , '` ud WHERE ProcessID="' , p_processId , '" AND AccountID IS NOT NULL AND TrunkID IS NOT NULL;
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;

	SET v_pointer_ = 1;
	SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_AccountTrunk_);

	WHILE v_pointer_ <= v_rowCount_
	DO

		SET v_TrunkID_ = (SELECT TrunkID FROM tmp_AccountTrunk_ t WHERE t.RowID = v_pointer_);
		SET v_AccountID_ = (SELECT AccountID FROM tmp_AccountTrunk_ t WHERE t.RowID = v_pointer_);


		CALL Ratemanagement3.prc_getVendorCodeRate(v_AccountID_,v_TrunkID_,p_RateCDR,p_RateMethod,p_SpecifyRate);



		IF p_RateFormat = 2
		THEN
			CALL prc_updateVendorPrefix(v_AccountID_,v_TrunkID_, p_processId, p_tbltempusagedetail_name);
		END IF;



		IF p_RateCDR = 1 AND (v_VendorIDs_Count_=0 OR (v_VendorIDs_Count_>0 AND FIND_IN_SET(v_AccountID_,v_VendorIDs_)>0))
		THEN
			CALL prc_updateVendorRate(v_AccountID_,v_TrunkID_, p_processId, p_tbltempusagedetail_name,p_RateMethod,p_SpecifyRate);
		END IF;

		SET v_pointer_ = v_pointer_ + 1;
	END WHILE;


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


		CALL prc_updateDefaultVendorPrefix(p_processId, p_tbltempusagedetail_name);

	END IF;


	SET @stm = CONCAT('
	INSERT INTO tmp_tblTempRateLog_ (CompanyID,CompanyGatewayID,MessageType,Message,RateDate)
	SELECT DISTINCT ud.CompanyID,ud.CompanyGatewayID,1,  CONCAT( " Account Name : ( " , ga.AccountName ," ) Number ( " , ga.AccountNumber ," ) IP  ( " , ga.AccountIP ," ) CLI  ( " , ga.AccountCLI," ) - Gateway: ",cg.Title," - Doesnt exist in NEON") as Message ,DATE(NOW())
	FROM RMCDR3.`' , p_tbltempusagedetail_name , '` ud
	INNER JOIN tblGatewayAccount ga
		ON ga.AccountName = ud.AccountName
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
		SET @stm = CONCAT('
		INSERT INTO tmp_tblTempRateLog_ (CompanyID,CompanyGatewayID,MessageType,Message,RateDate)
		SELECT DISTINCT ud.CompanyID,ud.CompanyGatewayID,2,  CONCAT( "Account:  " , a.AccountName ," - Trunk: ",ud.trunk," - Unable to Rerate number ",ud.cld," - No Matching prefix found") as Message ,DATE(NOW())
		FROM  RMCDR3.`' , p_tbltempusagedetail_name , '` ud
		INNER JOIN Ratemanagement3.tblAccount a on  ud.AccountID = a.AccountID
		WHERE ud.ProcessID = "' , p_processid  , '" AND ud.is_rerated = 0 AND ud.billed_second <> 0');

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

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_updateInboundRate`;
DELIMITER //
CREATE PROCEDURE `prc_updateInboundRate`(
	IN `p_AccountID` INT,
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200),
	IN `p_CLD` VARCHAR(500),
	IN `p_ServiceID` INT,
	IN `p_RateMethod` VARCHAR(50),
	IN `p_SpecifyRate` DECIMAL(18,6)
)
BEGIN

	SET @stm = CONCAT('UPDATE   RMCDR3.`' , p_tbltempusagedetail_name , '` ud SET cost = 0,is_rerated=0  WHERE ProcessID = "',p_processId,'" AND AccountID = "',p_AccountID ,'" AND ServiceID = "',p_ServiceID ,'" AND ("' , p_CLD , '" = "" OR cld = "' , p_CLD , '") AND is_inbound = 1 ') ;

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET @stm = CONCAT('
	UPDATE   RMCDR3.`' , p_tbltempusagedetail_name , '` ud
	INNER JOIN Ratemanagement3.tmp_inboundcodes_ cr ON cr.Code = ud.area_prefix AND cr.TimezonesID = ud.TimezonesID
	SET cost =
		CASE WHEN  billed_second >= Interval1
		THEN
			(Rate/60.0)*Interval1+CEILING((billed_second-Interval1)/IntervalN)*(RateN/60.0)*IntervalN+IFNULL(ConnectionFee,0)
		ElSE
			CASE WHEN  billed_second > 0
			THEN
				Rate+IFNULL(ConnectionFee,0)
			ELSE
				0
			END
		END
	,is_rerated=1
	,duration=billed_second
	,billed_duration =
		CASE WHEN  billed_second >= Interval1
		THEN
			Interval1+CEILING((billed_second-Interval1)/IntervalN)*IntervalN
		ElSE
			CASE WHEN  billed_second > 0
			THEN
				Interval1
			ELSE
				0
			END
		END
	WHERE ProcessID = "',p_processId,'"
	AND AccountID = "',p_AccountID ,'"
	AND ServiceID = "',p_ServiceID ,'"
	AND ("' , p_CLD , '" = "" OR cld = "' , p_CLD , '")
	AND is_inbound = 1') ;

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	IF p_RateMethod  = 'SpecifyRate'
	THEN

		SET @stm = CONCAT('
		UPDATE   RMCDR3.`' , p_tbltempusagedetail_name , '` ud
		LEFT JOIN Ratemanagement3.tmp_inboundcodes_ cr ON cr.Code = ud.area_prefix
		SET cost =
			CASE WHEN  billed_second >= 1
			THEN
				(',p_SpecifyRate,'/60.0)*1+CEILING((billed_second-1)/1)*(',p_SpecifyRate,'/60.0)*1
			ElSE
				CASE WHEN  billed_second > 0
				THEN
					',p_SpecifyRate,'
				ELSE
					0
				END
			END
		,is_rerated=1
		,duration=billed_second
		,billed_duration =
			CASE WHEN  billed_second >= 1
			THEN
				1+CEILING((billed_second-1)/1)*1
			ElSE
				CASE WHEN  billed_second > 0
				THEN
					1
				ELSE
					0
				END
			END
		WHERE ProcessID = "',p_processId,'"
		AND AccountID = "',p_AccountID ,'"
		AND ServiceID = "',p_ServiceID ,'"
		AND cr.Code IS NULL
		AND ("' , p_CLD , '" = "" OR cld = "' , p_CLD , '")
		AND is_inbound = 1') ;

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

	END IF;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_updateOutboundRate`;
DELIMITER //
CREATE PROCEDURE `prc_updateOutboundRate`(
	IN `p_AccountID` INT,
	IN `p_TrunkID` INT,
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200),
	IN `p_ServiceID` INT,
	IN `p_RateMethod` VARCHAR(50),
	IN `p_SpecifyRate` DECIMAL(18,6)
)
BEGIN

	SET @stm = CONCAT('UPDATE   RMCDR3.`' , p_tbltempusagedetail_name , '` ud SET cost = 0,is_rerated=0  WHERE ProcessID = "',p_processId,'" AND AccountID = "',p_AccountID ,'" AND ServiceID = "',p_ServiceID ,'" AND ("',p_TrunkID ,'" = 0 OR TrunkID = "',p_TrunkID ,'") AND is_inbound = 0 ') ;

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET @stm = CONCAT('
	UPDATE   RMCDR3.`' , p_tbltempusagedetail_name , '` ud
	INNER JOIN Ratemanagement3.tmp_codes_ cr ON cr.Code = ud.area_prefix AND cr.TimezonesID = ud.TimezonesID
	SET cost =
		CASE WHEN  billed_second >= Interval1
		THEN
			(Rate/60.0)*Interval1+CEILING((billed_second-Interval1)/IntervalN)*(RateN/60.0)*IntervalN+IFNULL(ConnectionFee,0)
		ElSE
			CASE WHEN  billed_second > 0
			THEN
				Rate+IFNULL(ConnectionFee,0)
			ELSE
				0
			END
		END
	,is_rerated=1
	,duration = billed_second
	,billed_duration =
		CASE WHEN  billed_second >= Interval1
		THEN
			Interval1+CEILING((billed_second-Interval1)/IntervalN)*IntervalN
		ElSE
			CASE WHEN  billed_second > 0
			THEN
				Interval1
			ELSE
				0
			END
		END
	WHERE ProcessID = "',p_processId,'"
	AND AccountID = "',p_AccountID ,'"
	AND ServiceID = "',p_ServiceID ,'"
	AND ("',p_TrunkID ,'" = 0 OR TrunkID = "',p_TrunkID ,'")
	AND is_inbound = 0') ;

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	IF p_RateMethod = 'SpecifyRate'
	THEN

		SET @stm = CONCAT('
		UPDATE   RMCDR3.`' , p_tbltempusagedetail_name , '` ud
		LEFT JOIN Ratemanagement3.tmp_codes_ cr ON cr.Code = ud.area_prefix
		SET cost =
			CASE WHEN  billed_second >= 1
			THEN
				(',p_SpecifyRate,'/60.0)*1+CEILING((billed_second-1)/1)*(',p_SpecifyRate,'/60.0)*1
			ElSE
				CASE WHEN  billed_second > 0
				THEN
					',p_SpecifyRate,'
				ELSE
					0
				END
			END
		,is_rerated=1
		,duration = billed_second
		,billed_duration =
			CASE WHEN  billed_second >= 1
			THEN
				1+CEILING((billed_second-1)/1)*1
			ElSE
				CASE WHEN  billed_second > 0
				THEN
					1
				ELSE
					0
				END
			END
		WHERE ProcessID = "',p_processId,'"
		AND AccountID = "',p_AccountID ,'"
		AND ServiceID = "',p_ServiceID ,'"
		AND cr.Code IS NULL
		AND ("',p_TrunkID ,'" = 0 OR TrunkID = "',p_TrunkID ,'")
		AND is_inbound = 0') ;

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

	END IF;

END//
DELIMITER ;




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
			`Status`=1;

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
								DATE_FORMAT(connect_time, '%H:%i:%s') BETWEEN CONCAT(FromTime,':00') AND CONCAT(ToTime,':00')
							)
							OR
							(
								t.ApplyIF = 'end' AND
								DATE_FORMAT(disconnect_time, '%H:%i:%s') BETWEEN CONCAT(FromTime,':00') AND CONCAT(ToTime,':00')
							)
							OR
							(
								t.ApplyIF = 'both' AND
								DATE_FORMAT(connect_time, '%H:%i:%s') BETWEEN CONCAT(FromTime,':00') AND CONCAT(ToTime,':00') AND
								DATE_FORMAT(disconnect_time, '%H:%i:%s') BETWEEN CONCAT(FromTime,':00') AND CONCAT(ToTime,':00')
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




DROP PROCEDURE IF EXISTS `prc_updateVendorRate`;
DELIMITER //
CREATE PROCEDURE `prc_updateVendorRate`(
	IN `p_AccountID` INT,
	IN `p_TrunkID` INT,
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200),
	IN `p_RateMethod` VARCHAR(50),
	IN `p_SpecifyRate` DECIMAL(18,6)
)
BEGIN

	SET @stm = CONCAT('UPDATE   RMCDR3.`' , p_tbltempusagedetail_name , '` ud SET buying_cost = 0,is_rerated=0  WHERE ProcessID = "',p_processId,'" AND AccountID = "',p_AccountID ,'" AND TrunkID = "',p_TrunkID ,'"') ;

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET @stm = CONCAT('
	UPDATE   RMCDR3.`' , p_tbltempusagedetail_name , '` ud
	INNER JOIN Ratemanagement3.tmp_vcodes_ cr ON cr.Code = ud.area_prefix AND cr.TimezonesID = ud.TimezonesID
	SET buying_cost =
		CASE WHEN  billed_second >= Interval1
		THEN
			(Rate/60.0)*Interval1+CEILING((billed_second-Interval1)/IntervalN)*(RateN/60.0)*IntervalN+IFNULL(ConnectionFee,0)
		ElSE
			CASE WHEN  billed_second > 0
			THEN
				Rate+IFNULL(ConnectionFee,0)
			ELSE
				0
			END
		END
	,is_rerated=1
	,duration=billed_second
	,billed_duration =
		CASE WHEN  billed_second >= Interval1
		THEN
			Interval1+CEILING((billed_second-Interval1)/IntervalN)*IntervalN
		ElSE
			CASE WHEN  billed_second > 0
			THEN
				Interval1
			ELSE
				0
			END
		END
	WHERE ProcessID = "',p_processId,'"
	AND AccountID = "',p_AccountID ,'"
	AND TrunkID = "',p_TrunkID ,'"') ;

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	IF p_RateMethod = 'SpecifyRate'
	THEN

		SET @stm = CONCAT('
		UPDATE   RMCDR3.`' , p_tbltempusagedetail_name , '` ud
		LEFT JOIN Ratemanagement3.tmp_vcodes_ cr ON cr.Code = ud.area_prefix
		SET buying_cost =
		CASE WHEN  billed_second >= Interval1
		THEN
			(Rate/60.0)*Interval1+CEILING((billed_second-Interval1)/IntervalN)*(Rate/60.0)*IntervalN+IFNULL(ConnectionFee,0)
		ElSE
			CASE WHEN  billed_second > 0
			THEN
				Rate+IFNULL(ConnectionFee,0)
			ELSE
				0
			END
		END
		,is_rerated=1
		,duration = billed_second
		,billed_duration =
			CASE WHEN  billed_second >= 1
			THEN
				1+CEILING((billed_second-1)/1)*1
			ElSE
				CASE WHEN  billed_second > 0
				THEN
					1
				ELSE
					0
				END
			END
		WHERE ProcessID = "',p_processId,'"
		AND AccountID = "',p_AccountID ,'"
		AND cr.Code IS NULL
		AND ("',p_TrunkID ,'" = 0 OR TrunkID = "',p_TrunkID ,'")
		') ;

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

	END IF;

END//
DELIMITER ;