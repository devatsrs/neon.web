USE `RMCDR3`;

ALTER TABLE `tblRetailUsageDetail`
	ALTER `UsageDetailID` DROP DEFAULT;
ALTER TABLE `tblRetailUsageDetail`
	CHANGE COLUMN `UsageDetailID` `UsageDetailID` BIGINT NOT NULL AFTER `RetailUsageDetailID`,
	CHANGE COLUMN `ID` `ID` BIGINT(20) NULL DEFAULT NULL AFTER `UsageDetailID`;

DROP PROCEDURE IF EXISTS `prc_InsertTemptResellerCDR`;
DELIMITER //
CREATE PROCEDURE `prc_InsertTemptResellerCDR`(
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
	
	INSERT INTO tmp_usageheader
	SELECT DISTINCT uh.*
	From tblUsageHeader uh
	WHERE uh.CompanyGatewayID = p_CompanyGatewayID
	AND ((p_Today = 1 AND uh.StartDate BETWEEN DATE_FORMAT(SUBDATE(Now(),INTERVAL 2 hour) ,"%Y-%m-%d") AND DATE_FORMAT(Now() ,"%Y-%m-%d") ) OR (p_Today =0 AND uh.StartDate BETWEEN p_StartDate AND p_EndDate ));
	
	INSERT INTO tmp_resellers(ResellerID,CompanyID,ChildCompanyID,AccountID,TotalAccount)
	SELECT DISTINCT
		ResellerID,
		CompanyID,
		ChildCompanyID,
		AccountID,
		(SELECT count(*) FROM Ratemanagement3.tblAccount a WHERE a.CompanyId=ChildCompanyID) as TotalAccount		
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
			     AND tr.RowID = v_pointer_;
			-- WHERE a.`Status` =1;

			SET v_pointer_ = v_pointer_ + 1;

		END WHILE;
		
		
		DELETE urd 
		FROM tblRetailUsageDetail urd
			INNER JOIN tblUsageDetails  ud
			   ON ud.UsageDetailID = urd.UsageDetailID
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
		
		
		
		
		SET @stm2 = CONCAT('
			    INSERT INTO `' , p_tbltempusagedetail_name , '` (CompanyID,AccountID,ProcessID,CompanyGatewayID,GatewayAccountPKID,GatewayAccountID,AccountNumber,connect_time,disconnect_time,billed_duration,billed_second,trunk,area_prefix,cli,cld,cost,ServiceID,duration,is_inbound,is_rerated,disposition,userfield,cc_type,ID,extension,pincode)
				SELECT "' , p_CompanyID , '" as CompanyID,ta.ResellerAccountID as `AccountID`,"' ,p_ProcessID ,'" as ProcessID,uh.CompanyGatewayID,uh.GatewayAccountPKID,uh.GatewayAccountID,uh.GatewayAccountID as AccountNumber,ud.connect_time,ud.disconnect_time,ud.billed_duration,ud.billed_second,"Other" as `trunk`,"Other" as `area_prefix`,ud.cli,ud.cld,ud.cost,uh.ServiceID,ud.duration,ud.is_inbound,0 as `is_rerated`,ud.disposition,ud.userfield,cc_type,ud.ID,ud.extension,ud.pincode
				FROM tblUsageDetails  ud
					INNER JOIN tmp_usageheader uh
						ON uh.UsageHeaderID = ud.UsageHeaderID
					INNER JOIN tmp_allaccounts_ ta
						ON uh.AccountID = ta.CustomerAccountID	
					LEFT JOIN tblRetailUsageDetail rd
						ON rd.UsageDetailID = ud.UsageDetailID	
				WHERE uh.CompanyGatewayID = "',p_CompanyGatewayID,'";
		');

		PREPARE stmt2 FROM @stm2;
		EXECUTE stmt2;
		DEALLOCATE PREPARE stmt2;

		SET @stm4 = CONCAT('
			    INSERT INTO `' , p_tbltempusagedetail_name ,'_Retail' , '` (TempUsageDetailID,ID,cc_type,ProcessID)
				SELECT TempUsageDetailID,ID,IFNULL(cc_type,0),ProcessID
				FROM `' , p_tbltempusagedetail_name , '`  
				WHERE ProcessID = "',p_ProcessID,'";
		');

		PREPARE stmt4 FROM @stm4;
		EXECUTE stmt4;
		DEALLOCATE PREPARE stmt4;
		/*
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
		DEALLOCATE PREPARE stmt3; */
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_reseller_insertCDR`;
DELIMITER //
CREATE PROCEDURE `prc_reseller_insertCDR`(
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

	-- for Mirta retail only
	
	SET @stm51 = CONCAT('
	INSERT INTO  tblRetailUsageDetail (UsageDetailID,ID,cc_type,ProcessID)
	SELECT Distinct d.UsageDetailID,rd.ID,rd.cc_type,rd.ProcessID
	FROM   `' , p_tbltempusagedetail_name , '_Retail` rd
	INNER JOIN tblUsageDetails d
	ON d.ProcessID = rd.ProcessID AND rd.ID=d.ID
	WHERE   d.ProcessID = "' , p_processId , '" ;
	');
	PREPARE stmt51 FROM @stm51;
	EXECUTE stmt51;
	DEALLOCATE PREPARE stmt51;


	SET @stm52 = CONCAT('
	DELETE FROM `' , p_tbltempusagedetail_name , '_Retail` WHERE processid = "' , p_processId , '" ;
	');

	PREPARE stm52 FROM @stm52;
	EXECUTE stm52;
	DEALLOCATE PREPARE stm52;

	SET @stm6 = CONCAT('
	DELETE FROM `' , p_tbltempusagedetail_name , '` WHERE processid = "' , p_processId , '" ;
	');

	PREPARE stmt6 FROM @stm6;
	EXECUTE stmt6;
	DEALLOCATE PREPARE stmt6;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_insertCDR`;
DELIMITER //
CREATE PROCEDURE `prc_insertCDR`(
	IN `p_processId` varchar(200),
	IN `p_tbltempusagedetail_name` VARCHAR(200)
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	SET SESSION innodb_lock_wait_timeout = 180;

	CALL Ratemanagement3.prc_UpdateMysqlPID(p_processId);
	
	-- Find Gateway Name for Mirta only
	SET @gateway_name  = '';
	SET @stm1 = CONCAT('select g.Name into @gateway_name   FROM  `' , p_tbltempusagedetail_name , '` d
			INNER JOIN Ratemanagement3.tblCompanyGateway cg on d.CompanyGatewayID = cg.CompanyGatewayID
			INNER JOIN Ratemanagement3.tblGateway g on g.GatewayID = cg.GatewayID
			WHERE processid = "' , p_processId , '" AND g.Name = "PBX" limit 1 ');
	PREPARE stmt1 FROM @stm1;
	EXECUTE stmt1;
	DEALLOCATE PREPARE stmt1;
 


	SET @stm2 = CONCAT('
	INSERT INTO   tblUsageHeader (CompanyID,CompanyGatewayID,GatewayAccountPKID,GatewayAccountID,AccountID,StartDate,created_at,ServiceID)
	SELECT DISTINCT d.CompanyID,d.CompanyGatewayID,d.GatewayAccountPKID,d.GatewayAccountID,d.AccountID,DATE_FORMAT(connect_time,"%Y-%m-%d"),NOW(),d.ServiceID
	FROM `' , p_tbltempusagedetail_name , '` d
	LEFT JOIN tblUsageHeader h
	ON h.GatewayAccountPKID = d.GatewayAccountPKID
		AND h.StartDate = DATE_FORMAT(connect_time,"%Y-%m-%d")
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
		AND (h.AccountID = d.AccountID OR (d.AccountID is null AND h.AccountID is null))
	WHERE   processid = "' , p_processId , '"
		AND billed_duration = 0 AND cost = 0 AND ( disposition <> "ANSWERED" OR disposition IS NULL );

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


	-- FOR MIRTA ONLY 

	 IF (@gateway_name = 'PBX') THEN
   	
			SET @stm31 = CONCAT('
			INSERT INTO tblUsageDetailFailedCall (UsageHeaderID,connect_time,disconnect_time,billed_duration,billed_second,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID,is_inbound,disposition,userfield)
			SELECT UsageHeaderID,connect_time,disconnect_time,billed_duration,billed_second,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID,is_inbound,disposition,userfield
			FROM  `' , p_tbltempusagedetail_name , '` d
			INNER JOIN tblUsageHeader h
			ON h.GatewayAccountPKID = d.GatewayAccountPKID
				AND h.StartDate = DATE_FORMAT(connect_time,"%Y-%m-%d")
				AND (h.AccountID = d.AccountID OR (d.AccountID is null AND h.AccountID is null))
			WHERE   processid = "' , p_processId , '"
				AND disposition IS NOT NULL AND disposition <> "ANSWERED"; 
		
			');
		
			PREPARE stmt31 FROM @stm31;
			EXECUTE stmt31;
			DEALLOCATE PREPARE stmt31;
		
			SET @stm41 = CONCAT('
			DELETE FROM `' , p_tbltempusagedetail_name , '` WHERE processid = "' , p_processId , '"  AND disposition IS NOT NULL AND disposition <> "ANSWERED";
			'); 
		
			PREPARE stmt41 FROM @stm41;
			EXECUTE stmt41;
			DEALLOCATE PREPARE stmt41; 
	END IF;
	-- for mirta only over
	 
	


	SET @stm5 = CONCAT('
	INSERT INTO tblUsageDetails (UsageHeaderID,connect_time,disconnect_time,billed_duration,billed_second,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID,is_inbound,disposition,userfield)
	SELECT UsageHeaderID,connect_time,disconnect_time,billed_duration,billed_second,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID,is_inbound,disposition,userfield
	FROM  `' , p_tbltempusagedetail_name , '` d
	INNER JOIN tblUsageHeader h
	ON h.GatewayAccountPKID = d.GatewayAccountPKID
		AND h.StartDate = DATE_FORMAT(connect_time,"%Y-%m-%d")
		AND (h.AccountID = d.AccountID OR (d.AccountID is null AND h.AccountID is null))
	WHERE   processid = "' , p_processId , '" ;
	');

	PREPARE stmt5 FROM @stm5;
	EXECUTE stmt5;
	DEALLOCATE PREPARE stmt5;
	
	
	-- for Mirta retail only
   IF (@gateway_name = 'PBX') THEN
   
		SET @stm51 = CONCAT('
		INSERT INTO  tblRetailUsageDetail (UsageDetailID,ID,cc_type,ProcessID)
		SELECT Distinct d.UsageDetailID,rd.ID,rd.cc_type,rd.ProcessID
		FROM   `' , p_tbltempusagedetail_name , '_Retail` rd
		INNER JOIN tblUsageDetails d
		ON d.ProcessID = rd.ProcessID  AND d.ID = rd.ID 
		WHERE   d.ProcessID = "' , p_processId , '" ;
		');
		PREPARE stmt51 FROM @stm51;
		EXECUTE stmt51;
		DEALLOCATE PREPARE stmt51;

   END IF;

	SET @stm6 = CONCAT('
	DELETE FROM `' , p_tbltempusagedetail_name , '` WHERE processid = "' , p_processId , '" ;
	');

	PREPARE stmt6 FROM @stm6;
	EXECUTE stmt6;
	DEALLOCATE PREPARE stmt6;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;