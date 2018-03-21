USE `RMCDR3`;

DROP PROCEDURE IF EXISTS `prc_RetailMonitorCalls`;
DELIMITER //
CREATE PROCEDURE `prc_RetailMonitorCalls`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_ResellerID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_Type` VARCHAR(50)
)
BEGIN

	DECLARE v_raccountids TEXT;
	SET v_raccountids ='';
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
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
	
		
	IF p_Type = 'call_duraition'
	THEN

		SELECT 
			cli,
			cld,
			billed_duration  
		FROM tblUsageDetails  ud
		INNER JOIN tblUsageHeader uh
			ON uh.UsageHeaderID = ud.UsageHeaderID
		WHERE uh.CompanyID = p_CompanyID
		AND uh.AccountID IS NOT NULL
		AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
		AND StartDate BETWEEN p_StartDate AND p_EndDate
		AND (p_ResellerID = 0 OR FIND_IN_SET(uh.AccountID, v_raccountids) != 0)
		ORDER BY billed_duration DESC LIMIT 10;

	END IF;

		
	IF p_Type = 'call_cost'
	THEN

		SELECT 
			cli,
			cld,
			cost,
			billed_duration
		FROM tblUsageDetails  ud
		INNER JOIN tblUsageHeader uh
			ON uh.UsageHeaderID = ud.UsageHeaderID
		WHERE uh.CompanyID = p_CompanyID
		AND uh.AccountID IS NOT NULL
		AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
		AND StartDate BETWEEN p_StartDate AND p_EndDate
		AND (p_ResellerID = 0 OR FIND_IN_SET(uh.AccountID, v_raccountids) != 0)
		ORDER BY cost DESC LIMIT 10;

	END IF;
	
	
	IF p_Type = 'most_dialed'
	THEN

		SELECT 
			cld,
			count(*) AS dail_count,
			SUM(billed_duration) AS billed_duration
		FROM tblUsageDetails  ud
		INNER JOIN tblUsageHeader uh
			ON uh.UsageHeaderID = ud.UsageHeaderID
		WHERE uh.CompanyID = p_CompanyID
		AND uh.AccountID IS NOT NULL
		AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
		AND StartDate BETWEEN p_StartDate AND p_EndDate
		AND (p_ResellerID = 0 OR FIND_IN_SET(uh.AccountID, v_raccountids) != 0)
		GROUP BY cld
		ORDER BY dail_count DESC
		LIMIT 10;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 

END//
DELIMITER ;

-- new

DROP PROCEDURE IF EXISTS `prc_DeleteDuplicateUniqueID`;
DELIMITER //
CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_DeleteDuplicateUniqueID`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_ProcessID` VARCHAR(200),
	IN `p_tbltempusagedetail_name` VARCHAR(200)
)
BEGIN
 	
 	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	SET @stm1 = CONCAT('DELETE tud FROM     `' , p_tbltempusagedetail_name , '` tud
	INNER JOIN tblUsageDetails ud ON tud.ID =ud.ID
	INNER JOIN  tblUsageHeader uh on uh.UsageHeaderID = ud.UsageHeaderID
		AND tud.CompanyGatewayID = uh.CompanyGatewayID
	WHERE
		  tud.CompanyGatewayID = "' , p_CompanyGatewayID , '"
	AND  tud.ProcessID = "' , p_processId , '";
	');
	PREPARE stmt1 FROM @stm1;
   EXECUTE stmt1;
   DEALLOCATE PREPARE stmt1;
   
   SET @stm2 = CONCAT('DELETE tud FROM     `' , p_tbltempusagedetail_name , '` tud
	INNER JOIN tblUsageDetailFailedCall ud ON tud.ID =ud.ID
	INNER JOIN  tblUsageHeader uh on uh.UsageHeaderID = ud.UsageHeaderID
		AND tud.CompanyGatewayID = uh.CompanyGatewayID
	WHERE  
		  tud.CompanyGatewayID = "' , p_CompanyGatewayID , '"
	AND  tud.ProcessID = "' , p_processId , '";
	');
	PREPARE stmt2 FROM @stm2;
   EXECUTE stmt2;
   DEALLOCATE PREPARE stmt2;
	
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_InsertTemptResellerCDR`;
DELIMITER //
CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_InsertTemptResellerCDR`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_ProcessID` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200),
	IN `p_StartDate` DATE,
	IN `p_EndDate` DATE,
	IN `p_Today` INT
)
ThisSP:BEGIN
	
	DECLARE v_raccountids TEXT;
	DECLARE v_pointer_ INT;
	DECLARE v_rowCount_ INT;
	DECLARE v_ResellerID_ INT;
	DECLARE v_ResellerAccountName_ VARCHAR(100);
	SET v_raccountids = '';

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_usageheader;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_usageheader(
		UsageHeaderID INT,
		CompanyID INT,
		CompanyGatewayID INT,
		GatewayAccountID VARCHAR(100),
		AccountID INT,
		StartDate DATETIME,
		updated_at DATETIME,
		created_at DATETIME,
		ServiceID INT,
		GatewayAccountPKID INT
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_resellers;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_resellers(
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		ResellerID INT,
		CompanyID INT,
		ChildCompanyID INT,
		AccountID INT,
		TotalAccount INT
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_reselleraccounts_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_reselleraccounts_(
		AccountID int
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_allaccounts_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_allaccounts_(
		ResellerID INT,
		ResellerCompanyID INT,
		ResellerAccountID INT,
		ResellerAccountName VARCHAR(100),
		CustomerAccountID INT,
		CustomerAccountName VARCHAR(100)
	);
	
	Insert into tmp_usageheader
	select distinct uh.*
	From tblUsageHeader uh
	WHERE uh.CompanyGatewayID = p_CompanyGatewayID
	AND ((p_Today = 1 AND DATE(uh.created_at) = DATE(now())) OR p_Today =0 AND uh.StartDate BETWEEN p_StartDate AND p_EndDate );
	
	INSERT INTO tmp_resellers(ResellerID,CompanyID,ChildCompanyID,AccountID,TotalAccount)
	SELECT DISTINCT
		ResellerID,
		CompanyID,
		ChildCompanyID,
		AccountID,
		(SELECT count(*) FROM Ratemanagement3.tblAccount a WHERE a.CompanyId=ChildCompanyID AND a.IsCustomer=1 AND a.`Status`=1) as TotalAccount		
	FROM Ratemanagement3.tblReseller WHERE CompanyID =p_CompanyID 
		AND Status=1
	HAVING TotalAccount > 0;
	  
	 
 		SET v_pointer_ = 1;
		SET v_rowCount_ = (SELECT COUNT(*) FROM tmp_resellers);
	 
		WHILE v_pointer_ <= v_rowCount_
		DO

			SET v_ResellerID_ = (SELECT ResellerID FROM tmp_resellers t WHERE t.RowID = v_pointer_);
			SET v_ResellerAccountName_ = (SELECT AccountName FROM Ratemanagement3.tblAccount WHERE AccountID=(SELECT AccountID FROM tmp_resellers t WHERE t.RowID = v_pointer_));
			
			INSERT INTO tmp_allaccounts_
			SELECT 
			  tr.ResellerID,
			  tr.ChildCompanyID AS ResellerCompanyID,
			  tr.AccountID AS ResellerAccountID,
			  v_ResellerAccountName_ AS  ResellerAccountName,
			  a.AccountID AS CustomerAccountID,
			  a.AccountName CustomerAccountName
			FROM Ratemanagement3.tblAccount a
			     INNER JOIN tmp_resellers tr
			     ON a.CompanyId = tr.ChildCompanyID
			     AND tr.RowID = v_pointer_
			WHERE a.IsCustomer = 1 AND a.`Status` =1;

			SET v_pointer_ = v_pointer_ + 1;

		END WHILE;
		
		/** delete todays reseller cdrs */
		
--		Leave ThisSP;
				
		DELETE ud 
		FROM tblUsageDetailFailedCall  ud
			INNER JOIN tmp_usageheader uh
				ON uh.UsageHeaderID = ud.UsageHeaderID
			INNER JOIN tmp_allaccounts_ ta
				ON uh.AccountID = ta.ResellerAccountID	
		WHERE uh.CompanyGatewayID = p_CompanyGatewayID;
		
		DELETE ud 
		FROM tblUsageDetails  ud
			INNER JOIN tmp_usageheader uh
				ON uh.UsageHeaderID = ud.UsageHeaderID
			INNER JOIN tmp_allaccounts_ ta
				ON uh.AccountID = ta.ResellerAccountID	
		WHERE uh.CompanyGatewayID = p_CompanyGatewayID;
		
		
		/* insert reseller cdrs in temp table */
		
		SET @stm2 = CONCAT('
	    INSERT INTO `' , p_tbltempusagedetail_name , '` (CompanyID,AccountID,ProcessID,CompanyGatewayID,GatewayAccountPKID,GatewayAccountID,AccountNumber,connect_time,disconnect_time,billed_duration,billed_second,trunk,area_prefix,cli,cld,cost,ServiceID,duration,is_inbound,is_rerated,disposition,userfield)
		SELECT "' , p_CompanyID , '" as CompanyID,ta.ResellerAccountID as `AccountID`,"' ,p_ProcessID ,'" as ProcessID,uh.CompanyGatewayID,uh.GatewayAccountPKID,uh.GatewayAccountID,uh.GatewayAccountID as AccountNumber,ud.connect_time,ud.disconnect_time,ud.billed_duration,ud.billed_second,"Other" as `trunk`,"Other" as `area_prefix`,ud.cli,ud.cld,ud.cost,uh.ServiceID,ud.duration,ud.is_inbound,0 as `is_rerated`,ud.disposition,ud.userfield
		FROM tblUsageDetails  ud
			INNER JOIN tmp_usageheader uh
				ON uh.UsageHeaderID = ud.UsageHeaderID
			INNER JOIN tmp_allaccounts_ ta
				ON uh.AccountID = ta.CustomerAccountID	
		WHERE uh.CompanyGatewayID = "',p_CompanyGatewayID,'";
		');

		PREPARE stmt2 FROM @stm2;
		EXECUTE stmt2;
		DEALLOCATE PREPARE stmt2;

		SET @stm3 = CONCAT('
		INSERT INTO `' , p_tbltempusagedetail_name , '` (CompanyID,AccountID,ProcessID,CompanyGatewayID,GatewayAccountPKID,GatewayAccountID,AccountNumber,connect_time,disconnect_time,billed_duration,billed_second,trunk,area_prefix,cli,cld,cost,ServiceID,duration,is_inbound,is_rerated,disposition,userfield)		
		SELECT "' , p_CompanyID , '" as CompanyID,tb.ResellerAccountID as `AccountID`,' ,p_ProcessID ,' as ProcessID,uh.CompanyGatewayID,uh.GatewayAccountPKID,uh.GatewayAccountID,uh.GatewayAccountID as AccountNumber,ud.connect_time,ud.disconnect_time,ud.billed_duration,ud.billed_second,"Other" as `trunk`,"Other" as `area_prefix`,ud.cli,ud.cld,ud.cost,uh.ServiceID,ud.duration,ud.is_inbound,0 as `is_rerated`,ud.disposition,ud.userfield 
		FROM tblUsageDetailFailedCall  ud
			INNER JOIN tblUsageHeader uh
				ON uh.UsageHeaderID = ud.UsageHeaderID
			INNER JOIN tmp_allaccounts_ tb
				ON uh.AccountID = tb.CustomerAccountID	
		WHERE uh.CompanyGatewayID ="',p_CompanyGatewayID,'";	
		');

		PREPARE stmt3 FROM @stm3;
		EXECUTE stmt3;
		DEALLOCATE PREPARE stmt3;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_reseller_insertCDR`;
DELIMITER //
CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_reseller_insertCDR`(
	IN `p_processId` varchar(200),
	IN `p_tbltempusagedetail_name` VARCHAR(200)
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	SET SESSION innodb_lock_wait_timeout = 180;

	SET @stm2 = CONCAT('
	INSERT INTO   tblUsageHeader (CompanyID,CompanyGatewayID,GatewayAccountPKID,GatewayAccountID,AccountID,StartDate,created_at,ServiceID)
	SELECT DISTINCT d.CompanyID,d.CompanyGatewayID,d.GatewayAccountPKID,d.GatewayAccountID,d.AccountID,DATE_FORMAT(connect_time,"%Y-%m-%d"),NOW(),d.ServiceID
	FROM `' , p_tbltempusagedetail_name , '` d
	LEFT JOIN tblUsageHeader h
	ON h.GatewayAccountPKID = d.GatewayAccountPKID
		AND h.StartDate = DATE_FORMAT(connect_time,"%Y-%m-%d")
		AND h.AccountID = d.AccountID
	WHERE h.GatewayAccountID IS NULL AND processid = "' , p_processId , '";
	');

	PREPARE stmt2 FROM @stm2;
	EXECUTE stmt2;
	DEALLOCATE PREPARE stmt2;

	SET @stm3 = CONCAT('
	INSERT INTO tblUsageDetailFailedCall (UsageHeaderID,connect_time,disconnect_time,billed_duration,billed_second,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID,is_inbound,disposition,userfield)
	SELECT UsageHeaderID,connect_time,disconnect_time,billed_duration,billed_second,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID,is_inbound,disposition,userfield
	FROM  `' , p_tbltempusagedetail_name , '` d
	INNER JOIN tblUsageHeader h
	ON h.GatewayAccountPKID = d.GatewayAccountPKID
		AND h.StartDate = DATE_FORMAT(connect_time,"%Y-%m-%d")
		AND h.AccountID = d.AccountID
	WHERE   processid = "' , p_processId , '"
		AND billed_duration = 0 AND cost = 0 AND ( disposition <> "ANSWERED" OR disposition IS NULL);

	');

	PREPARE stmt3 FROM @stm3;
	EXECUTE stmt3;
	DEALLOCATE PREPARE stmt3;

	SET @stm4 = CONCAT('
	DELETE FROM `' , p_tbltempusagedetail_name , '` WHERE processid = "' , p_processId , '"  AND billed_duration = 0 AND cost = 0 AND ( disposition <> "ANSWERED" OR disposition IS NULL);
	');

	PREPARE stmt4 FROM @stm4;
	EXECUTE stmt4;
	DEALLOCATE PREPARE stmt4;

	SET @stm5 = CONCAT('
	INSERT INTO tblUsageDetails (UsageHeaderID,connect_time,disconnect_time,billed_duration,billed_second,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID,is_inbound,disposition,userfield)
	SELECT UsageHeaderID,connect_time,disconnect_time,billed_duration,billed_second,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID,is_inbound,disposition,userfield
	FROM  `' , p_tbltempusagedetail_name , '` d
	INNER JOIN tblUsageHeader h
	ON h.GatewayAccountPKID = d.GatewayAccountPKID
		AND h.StartDate = DATE_FORMAT(connect_time,"%Y-%m-%d")
		AND h.AccountID = d.AccountID
	WHERE   processid = "' , p_processId , '" ;
	');

	PREPARE stmt5 FROM @stm5;
	EXECUTE stmt5;
	DEALLOCATE PREPARE stmt5;

	SET @stm6 = CONCAT('
	DELETE FROM `' , p_tbltempusagedetail_name , '` WHERE processid = "' , p_processId , '" ;
	');

	PREPARE stmt6 FROM @stm6;
	EXECUTE stmt6;
	DEALLOCATE PREPARE stmt6;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;