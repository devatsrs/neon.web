USE `RMCDR3`;

CREATE TABLE `tblHuaweiExtraFields` (
	`HuaweiID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`UsageDetailID` INT(11) NOT NULL,
	`caller_address_nature` INT(5) NULL DEFAULT NULL,
	`called_address_nature` INT(5) NULL DEFAULT NULL,
	`alert_time` DATETIME NULL DEFAULT NULL,
	`trunk_group_in` VARCHAR(100) NULL DEFAULT NULL,
	`trunk_group_out` VARCHAR(100) NULL DEFAULT NULL,
	`caller_trunk_cic` INT(11) NULL DEFAULT NULL,
	`called_trunk_cic` INT(11) NULL DEFAULT NULL,
	`connected_number` BIGINT(20) NULL DEFAULT NULL,
	`connected_address_nature` INT(3) NULL DEFAULT NULL,
	`caller_call_id` VARCHAR(200) NULL DEFAULT NULL,
	`called_call_id` VARCHAR(200) NULL DEFAULT NULL,
	`global_call_ref` VARCHAR(50) NULL DEFAULT NULL,
	`connection_id` INT(11) NULL DEFAULT NULL,
	`audio_codec_type` INT(5) NULL DEFAULT NULL,
	PRIMARY KEY (`HuaweiID`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;

CREATE TABLE `tblHuaweiExtraFieldsFailedCall` (
	`HuaweiFailedID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`UsageDetailFailedCallID` INT(11) NOT NULL,
	`caller_address_nature` INT(5) NULL DEFAULT NULL,
	`called_address_nature` INT(5) NULL DEFAULT NULL,
	`alert_time` DATETIME NULL DEFAULT NULL,
	`trunk_group_in` VARCHAR(100) NULL DEFAULT NULL,
	`trunk_group_out` VARCHAR(100) NULL DEFAULT NULL,
	`caller_trunk_cic` INT(11) NULL DEFAULT NULL,
	`called_trunk_cic` INT(11) NULL DEFAULT NULL,
	`connected_number` BIGINT(20) NULL DEFAULT NULL,
	`connected_address_nature` INT(3) NULL DEFAULT NULL,
	`caller_call_id` VARCHAR(200) NULL DEFAULT NULL,
	`called_call_id` VARCHAR(200) NULL DEFAULT NULL,
	`global_call_ref` VARCHAR(50) NULL DEFAULT NULL,
	`connection_id` INT(11) NULL DEFAULT NULL,
	`audio_codec_type` INT(5) NULL DEFAULT NULL,
	PRIMARY KEY (`HuaweiFailedID`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;

CREATE TABLE `tblHuaweiVendorExtraFields` (
	`HuaweiVendorID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`VendorCDRID` INT(11) NOT NULL,
	`caller_address_nature` INT(5) NULL DEFAULT NULL,
	`called_address_nature` INT(5) NULL DEFAULT NULL,
	`alert_time` DATETIME NULL DEFAULT NULL,
	`trunk_group_in` VARCHAR(100) NULL DEFAULT NULL,
	`trunk_group_out` VARCHAR(100) NULL DEFAULT NULL,
	`caller_trunk_cic` INT(11) NULL DEFAULT NULL,
	`called_trunk_cic` INT(11) NULL DEFAULT NULL,
	`connected_number` BIGINT(20) NULL DEFAULT NULL,
	`connected_address_nature` INT(3) NULL DEFAULT NULL,
	`caller_call_id` VARCHAR(200) NULL DEFAULT NULL,
	`called_call_id` VARCHAR(200) NULL DEFAULT NULL,
	`global_call_ref` VARCHAR(50) NULL DEFAULT NULL,
	`connection_id` INT(11) NULL DEFAULT NULL,
	`audio_codec_type` INT(5) NULL DEFAULT NULL,
	PRIMARY KEY (`HuaweiVendorID`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;

CREATE TABLE `tblHuaweiVendorExtraFieldsFailedCall` (
	`HuaweiVendorFailedID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`VendorCDRFailedID` INT(11) NOT NULL,
	`caller_address_nature` INT(5) NULL DEFAULT NULL,
	`called_address_nature` INT(5) NULL DEFAULT NULL,
	`alert_time` DATETIME NULL DEFAULT NULL,
	`trunk_group_in` VARCHAR(100) NULL DEFAULT NULL,
	`trunk_group_out` VARCHAR(100) NULL DEFAULT NULL,
	`caller_trunk_cic` INT(11) NULL DEFAULT NULL,
	`called_trunk_cic` INT(11) NULL DEFAULT NULL,
	`connected_number` BIGINT(20) NULL DEFAULT NULL,
	`connected_address_nature` INT(3) NULL DEFAULT NULL,
	`caller_call_id` VARCHAR(200) NULL DEFAULT NULL,
	`called_call_id` VARCHAR(200) NULL DEFAULT NULL,
	`global_call_ref` VARCHAR(50) NULL DEFAULT NULL,
	`connection_id` INT(11) NULL DEFAULT NULL,
	`audio_codec_type` INT(5) NULL DEFAULT NULL,
	PRIMARY KEY (`HuaweiVendorFailedID`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;



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


	SET @gateway_name  = '';
	SET @stm1 = CONCAT('select g.Name into @gateway_name   FROM  `' , p_tbltempusagedetail_name , '` d
			INNER JOIN Ratemanagement3.tblCompanyGateway cg on d.CompanyGatewayID = cg.CompanyGatewayID
			INNER JOIN Ratemanagement3.tblGateway g on g.GatewayID = cg.GatewayID
			WHERE processid = "' , p_processId , '" AND (g.Name = "PBX" OR g.Name = "Huawei") limit 1 ');
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

	IF (@gateway_name = 'Huawei') THEN

		SET @stm8 = CONCAT('
		INSERT INTO tblHuaweiExtraFieldsFailedCall (UsageDetailFailedCallID,caller_address_nature,called_address_nature,alert_time,trunk_group_in,trunk_group_out,caller_trunk_cic,called_trunk_cic,connected_number,connected_address_nature,caller_call_id,called_call_id,global_call_ref,connection_id,audio_codec_type)
			SELECT d.UsageDetailFailedCallID,rd.caller_address_nature,rd.called_address_nature,rd.alert_time,rd.trunk_group_in,rd.trunk_group_out,rd.caller_trunk_cic,rd.called_trunk_cic,rd.connected_number,rd.connected_address_nature,rd.caller_call_id,rd.called_call_id,rd.global_call_ref,rd.connection_id,rd.audio_codec_type
		FROM  `' , p_tbltempusagedetail_name , '`rd
		INNER JOIN tblUsageDetailFailedCall d
		ON d.ProcessID = rd.ProcessID AND d.ID=rd.ID
		WHERE d.ProcessID = "' , p_processId , '" AND rd.FileName IS NOT NULL;
		');

		PREPARE stm8 FROM @stm8;
		EXECUTE stm8;
		DEALLOCATE PREPARE stm8;
	END IF;

	SET @stm4 = CONCAT('
	DELETE FROM `' , p_tbltempusagedetail_name , '` WHERE processid = "' , p_processId , '"  AND billed_duration = 0 AND cost = 0 AND ( disposition <> "ANSWERED" OR disposition IS NULL);
	');

	PREPARE stmt4 FROM @stm4;
	EXECUTE stmt4;
	DEALLOCATE PREPARE stmt4;


	 IF (@gateway_name = 'PBX') THEN

			SET @stm31 = CONCAT('
			INSERT INTO tblUsageDetailFailedCall (UsageHeaderID,connect_time,disconnect_time,billed_duration,billed_second,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID,is_inbound,disposition,userfield,ExtensionName,PincodeName)
			SELECT UsageHeaderID,connect_time,disconnect_time,billed_duration,billed_second,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID,is_inbound,disposition,userfield,ExtensionName,PincodeName
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

	SET @stm5 = CONCAT('
	INSERT INTO tblUsageDetails (UsageHeaderID,connect_time,disconnect_time,billed_duration,billed_second,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID,is_inbound,disposition,userfield,ExtensionName,PincodeName)
	SELECT UsageHeaderID,connect_time,disconnect_time,billed_duration,billed_second,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID,is_inbound,disposition,userfield,ExtensionName,PincodeName
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

	IF (@gateway_name = 'Huawei') THEN

		SET @stm9 = CONCAT('
		INSERT INTO tblHuaweiExtraFields (UsageDetailID,caller_address_nature,called_address_nature,alert_time,trunk_group_in,trunk_group_out,caller_trunk_cic,called_trunk_cic,connected_number,connected_address_nature,caller_call_id,called_call_id,global_call_ref,connection_id,audio_codec_type)
			SELECT d.UsageDetailID,rd.caller_address_nature,rd.called_address_nature,rd.alert_time,rd.trunk_group_in,rd.trunk_group_out,rd.caller_trunk_cic,rd.called_trunk_cic,rd.connected_number,rd.connected_address_nature,rd.caller_call_id,rd.called_call_id,rd.global_call_ref,rd.connection_id,rd.audio_codec_type
		FROM  `' , p_tbltempusagedetail_name , '` rd
		INNER JOIN tblUsageDetails d
		ON d.ProcessID = rd.ProcessID AND d.ID=rd.ID
		WHERE d.ProcessID = "' , p_processId , '" AND rd.FileName IS NOT NULL;
		');
		PREPARE stm9 FROM @stm9;
		EXECUTE stm9;
		DEALLOCATE PREPARE stm9;

	END IF;

	-- add file name log against failed cdr
	SET @stmFL = CONCAT('
	INSERT INTO  tblUsageDetailFailedCallFileLog (UsageDetailFailedCallID,ID,FileName,ProcessID)
	SELECT Distinct d.UsageDetailFailedCallID,rd.ID,rd.FileName,rd.ProcessID
	FROM `' , p_tbltempusagedetail_name , '` rd
	INNER JOIN tblUsageDetailFailedCall d
	ON d.ProcessID = rd.ProcessID AND d.ID=rd.ID
	WHERE d.ProcessID = "' , p_processId , '" AND rd.FileName IS NOT NULL;
	');
	PREPARE stmtFL FROM @stmFL;
	EXECUTE stmtFL;
	DEALLOCATE PREPARE stmtFL;
	-- remove old file log when rerating cdr
	SET @stmFL = CONCAT('
	DELETE udfl FROM tblUsageDetailsFileLog udfl
	JOIN `' , p_tbltempusagedetail_name , '` rd ON rd.ID=udfl.ID AND rd.FileName=udfl.FileName
	INNER JOIN tblUsageDetails d
	ON d.ProcessID = rd.ProcessID AND d.ID=rd.ID
	WHERE d.ProcessID = "' , p_processId , '" AND rd.FileName IS NOT NULL;
	');
	PREPARE stmtFL FROM @stmFL;
	EXECUTE stmtFL;
	DEALLOCATE PREPARE stmtFL;
	-- add file name log against cdr
	SET @stmFL = CONCAT('
	INSERT INTO  tblUsageDetailsFileLog (UsageDetailID,ID,FileName,ProcessID)
	SELECT Distinct d.UsageDetailID,rd.ID,rd.FileName,rd.ProcessID
	FROM `' , p_tbltempusagedetail_name , '` rd
	INNER JOIN tblUsageDetails d
	ON d.ProcessID = rd.ProcessID AND d.ID=rd.ID
	WHERE d.ProcessID = "' , p_processId , '" AND rd.FileName IS NOT NULL;
	');
	PREPARE stmtFL FROM @stmFL;
	EXECUTE stmtFL;
	DEALLOCATE PREPARE stmtFL;


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



DROP PROCEDURE IF EXISTS `prc_insertVendorCDR`;
DELIMITER //
CREATE PROCEDURE `prc_insertVendorCDR`(
	IN `p_processId` VARCHAR(200),
	IN `p_tbltempusagedetail_name` VARCHAR(50)

)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	SET SESSION innodb_lock_wait_timeout = 180;


	SET @stm1 = CONCAT('select g.Name into @gateway_name   FROM  `' , p_tbltempusagedetail_name , '` d
		INNER JOIN Ratemanagement3.tblCompanyGateway cg on d.CompanyGatewayID = cg.CompanyGatewayID
		INNER JOIN Ratemanagement3.tblGateway g on g.GatewayID = cg.GatewayID
		WHERE processid = "' , p_processId , '" AND g.Name = "Huawei" limit 1 ');
	PREPARE stmt1 FROM @stm1;
	EXECUTE stmt1;
	DEALLOCATE PREPARE stmt1;

	SET @stm2 = CONCAT('
	INSERT INTO   tblVendorCDRHeader (CompanyID,CompanyGatewayID,GatewayAccountPKID,GatewayAccountID,AccountID,StartDate,created_at,ServiceID)
	SELECT DISTINCT d.CompanyID,d.CompanyGatewayID,d.GatewayAccountPKID,d.GatewayAccountID,d.AccountID,DATE_FORMAT(connect_time,"%Y-%m-%d"),NOW(),d.ServiceID
	FROM `' , p_tbltempusagedetail_name , '` d
	LEFT JOIN tblVendorCDRHeader h
	ON h.GatewayAccountPKID = d.GatewayAccountPKID
		AND h.StartDate = DATE_FORMAT(connect_time,"%Y-%m-%d")
	WHERE h.GatewayAccountID IS NULL AND processid = "' , p_processId , '";
	');

	PREPARE stmt2 FROM @stm2;
	EXECUTE stmt2;
	DEALLOCATE PREPARE stmt2;

	SET @stm6 = CONCAT('
	INSERT INTO tblVendorCDRFailed (VendorCDRHeaderID,billed_duration,duration,billed_second, ID, selling_cost, buying_cost, connect_time, disconnect_time,cli, cld,trunk,area_prefix,  remote_ip, ProcessID)
	SELECT VendorCDRHeaderID,billed_duration,duration,billed_second, ID, selling_cost, buying_cost, connect_time, disconnect_time,cli, cld,trunk,area_prefix,  remote_ip, ProcessID
	FROM `' , p_tbltempusagedetail_name , '` d
	INNER JOIN tblVendorCDRHeader h
	ON h.GatewayAccountPKID = d.GatewayAccountPKID
		AND h.StartDate = DATE_FORMAT(connect_time,"%Y-%m-%d")
	WHERE processid = "' , p_processId , '" AND  billed_duration = 0 AND IFNULL(buying_cost,0) = 0 AND IFNULL(selling_cost,0) =  0  ;
	');

	PREPARE stmt6 FROM @stm6;
	EXECUTE stmt6;
	DEALLOCATE PREPARE stmt6;

	IF (@gateway_name = 'Huawei') THEN

		SET @stm8 = CONCAT('
		INSERT INTO tblHuaweiVendorExtraFieldsFailedCall (VendorCDRFailedID,caller_address_nature,called_address_nature,alert_time,trunk_group_in,trunk_group_out,caller_trunk_cic,called_trunk_cic,connected_number,connected_address_nature,caller_call_id,called_call_id,global_call_ref,connection_id,audio_codec_type)
		SELECT d.VendorCDRFailedID,rd.caller_address_nature,rd.called_address_nature,rd.alert_time,rd.trunk_group_in,rd.trunk_group_out,rd.caller_trunk_cic,rd.called_trunk_cic,rd.connected_number,rd.connected_address_nature,rd.caller_call_id,rd.called_call_id,rd.global_call_ref,rd.connection_id,rd.audio_codec_type
		FROM `' , p_tbltempusagedetail_name , '` rd
			INNER JOIN tblVendorCDRFailed d
		ON d.ProcessID = rd.ProcessID AND d.ID=rd.ID
		WHERE d.ProcessID = "' , p_processId , '" AND rd.FileName IS NOT NULL;
		');

		PREPARE stm8 FROM @stm8;
		EXECUTE stm8;
		DEALLOCATE PREPARE stm8;
	END IF;

	SET @stm3 = CONCAT('
	DELETE FROM `' , p_tbltempusagedetail_name , '` WHERE processid = "' , p_processId , '"  AND billed_duration = 0 AND IFNULL(buying_cost,0) = 0 AND IFNULL(selling_cost,0) =  0 ;
	');

	PREPARE stmt3 FROM @stm3;
	EXECUTE stmt3;
	DEALLOCATE PREPARE stmt3;

	SET @stm4 = CONCAT('
	INSERT INTO tblVendorCDR (VendorCDRHeaderID,billed_duration,duration,billed_second, ID, selling_cost, buying_cost, connect_time, disconnect_time,cli, cld,trunk,area_prefix,  remote_ip, ProcessID)
	SELECT VendorCDRHeaderID,billed_duration,duration,billed_second, ID, selling_cost, buying_cost, connect_time, disconnect_time,cli, cld,trunk,area_prefix,  remote_ip, ProcessID
	FROM `' , p_tbltempusagedetail_name , '` d
	INNER JOIN tblVendorCDRHeader h
	ON h.GatewayAccountPKID = d.GatewayAccountPKID
		AND h.StartDate = DATE_FORMAT(connect_time,"%Y-%m-%d")
	WHERE processid = "' , p_processId , '" ;
	');

	PREPARE stmt4 FROM @stm4;
	EXECUTE stmt4;
	DEALLOCATE PREPARE stmt4;

	IF (@gateway_name = 'Huawei') THEN

		SET @stm9 = CONCAT('
		INSERT INTO tblHuaweiVendorExtraFields (VendorCDRID,caller_address_nature,called_address_nature,alert_time,trunk_group_in,trunk_group_out,caller_trunk_cic,called_trunk_cic,connected_number,connected_address_nature,caller_call_id,called_call_id,global_call_ref,connection_id,audio_codec_type)
		SELECT d.VendorCDRID,rd.caller_address_nature,rd.called_address_nature,rd.alert_time,rd.trunk_group_in,rd.trunk_group_out,rd.caller_trunk_cic,rd.called_trunk_cic,rd.connected_number,rd.connected_address_nature,rd.caller_call_id,rd.called_call_id,rd.global_call_ref,rd.connection_id,rd.audio_codec_type
		FROM `' , p_tbltempusagedetail_name , '` rd
			INNER JOIN tblVendorCDR d
		ON d.ProcessID = rd.ProcessID AND d.ID=rd.ID
		WHERE d.ProcessID = "' , p_processId , '" AND rd.FileName IS NOT NULL;
		');
		PREPARE stm9 FROM @stm9;
		EXECUTE stm9;
		DEALLOCATE PREPARE stm9;

	END IF;

	-- add file name log against failed cdr
	SET @stmFL = CONCAT('
	INSERT INTO  tblVendorCDRFailedFileLog (VendorCDRFailedID,ID,FileName,ProcessID)
	SELECT DISTINCT d.VendorCDRFailedID,rd.ID,rd.FileName,rd.ProcessID
	FROM `' , p_tbltempusagedetail_name , '` rd
	INNER JOIN tblVendorCDRFailed d
	ON d.ProcessID = rd.ProcessID AND d.ID=rd.ID
	WHERE d.ProcessID = "' , p_processId , '" AND rd.FileName IS NOT NULL;
	');
	PREPARE stmtFL FROM @stmFL;
	EXECUTE stmtFL;
	DEALLOCATE PREPARE stmtFL;


	-- remove old file log when rerating cdr
	SET @stmFL = CONCAT('
	DELETE udfl FROM tblVendorCDRFileLog udfl
	JOIN `' , p_tbltempusagedetail_name , '` rd ON rd.ID=udfl.ID AND rd.FileName=udfl.FileName
	INNER JOIN tblVendorCDR d
	ON d.ProcessID = rd.ProcessID AND d.ID=rd.ID
	WHERE d.ProcessID = "' , p_processId , '" AND rd.FileName IS NOT NULL;
	');
	PREPARE stmtFL FROM @stmFL;
	EXECUTE stmtFL;
	DEALLOCATE PREPARE stmtFL;

	-- add file name log against cdr
	SET @stmFL = CONCAT('
	INSERT INTO  tblVendorCDRFileLog (VendorCDRID,ID,FileName,ProcessID)
	SELECT DISTINCT d.VendorCDRID,rd.ID,rd.FileName,rd.ProcessID
	FROM `' , p_tbltempusagedetail_name , '` rd
	INNER JOIN tblVendorCDR d
	ON d.ProcessID = rd.ProcessID AND d.ID=rd.ID
	WHERE d.ProcessID = "' , p_processId , '" AND rd.FileName IS NOT NULL;
	');
	PREPARE stmtFL FROM @stmFL;
	EXECUTE stmtFL;
	DEALLOCATE PREPARE stmtFL;

	SET @stm5 = CONCAT('
	DELETE FROM `' , p_tbltempusagedetail_name , '` WHERE processid = "' , p_processId , '" ;
	');

	PREPARE stmt5 FROM @stm5;
	EXECUTE stmt5;
	DEALLOCATE PREPARE stmt5;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


