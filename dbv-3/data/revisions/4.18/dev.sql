USE `RMCDR3`;


DROP PROCEDURE IF EXISTS `prc_insertCDR`;
DELIMITER //
CREATE PROCEDURE `prc_insertCDR`(
	IN `p_processId` varchar(200),
	IN `p_tbltempusagedetail_name` VARCHAR(200)

)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	SET SESSION innodb_lock_wait_timeout = 180;


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
