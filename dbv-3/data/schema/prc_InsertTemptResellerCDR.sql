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
	From NeonCDRDev.tblUsageHeader uh
	WHERE uh.CompanyGatewayID = p_CompanyGatewayID
	AND ((p_Today = 1 AND uh.StartDate BETWEEN DATE_FORMAT(SUBDATE(Now(),INTERVAL 2 hour) ,"%Y-%m-%d") AND DATE_FORMAT(Now() ,"%Y-%m-%d") ) OR (p_Today =0 AND uh.StartDate BETWEEN p_StartDate AND p_EndDate ));
	
	INSERT INTO tmp_resellers(ResellerID,CompanyID,ChildCompanyID,AccountID,TotalAccount)
	SELECT DISTINCT
		ResellerID,
		CompanyID,
		ChildCompanyID,
		AccountID,
		(SELECT count(*) FROM NeonRMDev.tblAccount a WHERE a.CompanyId=ChildCompanyID AND a.IsCustomer=1 AND a.`Status`=1) as TotalAccount		
	FROM NeonRMDev.tblReseller WHERE CompanyID =p_CompanyID 
		AND Status=1
	HAVING TotalAccount > 0;
	  
	 
 		SET v_pointer_ = 1;
		SET v_rowCount_ = (SELECT COUNT(*) FROM tmp_resellers);
	 
		WHILE v_pointer_ <= v_rowCount_
		DO

			SET v_ResellerID_ = (SELECT ResellerID FROM tmp_resellers t WHERE t.RowID = v_pointer_);
			SET v_ResellerAccountName_ = (SELECT AccountName FROM NeonRMDev.tblAccount WHERE AccountID=(SELECT AccountID FROM tmp_resellers t WHERE t.RowID = v_pointer_));
			
			INSERT INTO tmp_allaccounts_
			SELECT 
			  tr.ResellerID,
			  tr.ChildCompanyID AS ResellerCompanyID,
			  tr.AccountID AS ResellerAccountID,
			  v_ResellerAccountName_ AS  ResellerAccountName,
			  a.AccountID AS CustomerAccountID,
			  a.AccountName CustomerAccountName
			FROM NeonRMDev.tblAccount a
			     INNER JOIN tmp_resellers tr
			     ON a.CompanyId = tr.ChildCompanyID
			     AND tr.RowID = v_pointer_
			WHERE a.IsCustomer = 1 AND a.`Status` =1;

			SET v_pointer_ = v_pointer_ + 1;

		END WHILE;
		
		/** delete todays reseller cdrs */
		
--		Leave ThisSP;
				
		DELETE ud 
		FROM NeonCDRDev.tblUsageDetailFailedCall  ud
			INNER JOIN tmp_usageheader uh
				ON uh.UsageHeaderID = ud.UsageHeaderID
			INNER JOIN tmp_allaccounts_ ta
				ON uh.AccountID = ta.ResellerAccountID	
		WHERE uh.CompanyGatewayID = p_CompanyGatewayID;
		
		DELETE ud 
		FROM NeonCDRDev.tblUsageDetails  ud
			INNER JOIN tmp_usageheader uh
				ON uh.UsageHeaderID = ud.UsageHeaderID
			INNER JOIN tmp_allaccounts_ ta
				ON uh.AccountID = ta.ResellerAccountID	
		WHERE uh.CompanyGatewayID = p_CompanyGatewayID;
		
		
		/* insert reseller cdrs in temp table */
		
		SET @stm2 = CONCAT('
	    INSERT INTO `' , p_tbltempusagedetail_name , '` (CompanyID,AccountID,ProcessID,CompanyGatewayID,GatewayAccountPKID,GatewayAccountID,AccountNumber,connect_time,disconnect_time,billed_duration,billed_second,trunk,area_prefix,cli,cld,cost,ServiceID,duration,is_inbound,is_rerated,disposition,userfield)
		SELECT "' , p_CompanyID , '" as CompanyID,ta.ResellerAccountID as `AccountID`,"' ,p_ProcessID ,'" as ProcessID,uh.CompanyGatewayID,uh.GatewayAccountPKID,uh.GatewayAccountID,uh.GatewayAccountID as AccountNumber,ud.connect_time,ud.disconnect_time,ud.billed_duration,ud.billed_second,"Other" as `trunk`,"Other" as `area_prefix`,ud.cli,ud.cld,ud.cost,uh.ServiceID,ud.duration,ud.is_inbound,0 as `is_rerated`,ud.disposition,ud.userfield
		FROM NeonCDRDev.tblUsageDetails  ud
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
		FROM NeonCDRDev.tblUsageDetailFailedCall  ud
			INNER JOIN NeonCDRDev.tblUsageHeader uh
				ON uh.UsageHeaderID = ud.UsageHeaderID
			INNER JOIN tmp_allaccounts_ tb
				ON uh.AccountID = tb.CustomerAccountID	
		WHERE uh.CompanyGatewayID ="',p_CompanyGatewayID,'";	
		');

		PREPARE stmt3 FROM @stm3;
		EXECUTE stmt3;
		DEALLOCATE PREPARE stmt3;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END