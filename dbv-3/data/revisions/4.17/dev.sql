Use RMCDR3;

/*
Convert ID INT to big INT
Do not run this query for wholesale


IF RETAIL THEN RUN THIS QUERY
    ALTER TABLE `tblVendorCDR` CHANGE COLUMN `ID` `ID` BIGINT NULL DEFAULT NULL AFTER `duration`;
    ALTER TABLE `tblUsageDetails` CHANGE COLUMN `ID` `ID` BIGINT NULL DEFAULT NULL AFTER `ProcessID`;
    ALTER TABLE `tblUsageDetailFailedCall` CHANGE COLUMN `ID` `ID` BIGINT NULL DEFAULT NULL AFTER `ProcessID`;
    ALTER TABLE `tblVendorCDRFailed` CHANGE COLUMN `ID` `ID` BIGINT NULL DEFAULT NULL AFTER `duration`;
ELSE

    D:\www\rmfeatures\ID_BigInt_Issue\req.txt

    STOP CRONJOB TO INSERT DATA INTO USAGE TABLES.
    CREATE NEW TABLE WITH ID BIGINT COLUMN
    MOVE DATA EXCEPT FAILED CALL
    UPDATE AUTO INCREMENT
    RENAME NEW TABLE TO ORIGINAL AND ORIGINAL TO OLD


END

*/
ALTER TABLE `tblVCDRPostProcess` CHANGE COLUMN `ID` `ID` BIGINT NULL DEFAULT NULL AFTER `ProcessID`;
ALTER TABLE `tblCDRPostProcess`	CHANGE COLUMN `ID` `ID` BIGINT NULL DEFAULT NULL AFTER `ProcessID`;



CREATE TABLE IF NOT EXISTS `tblRetailUsageDetail` (
  `RetailUsageDetailID` int(11) NOT NULL AUTO_INCREMENT,
  `UsageDetailID` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `cc_type` tinyint(1) NOT NULL DEFAULT '0',
  `ProcessID` bigint(20) NOT NULL,
  PRIMARY KEY (`RetailUsageDetailID`),
  KEY `IX_cc_type` (`cc_type`),
  KEY `IX_UsageDetailID` (`UsageDetailID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



DROP PROCEDURE IF EXISTS `prc_updateSippyCustomerSetupTime`;
DELIMITER //
CREATE PROCEDURE `prc_updateSippyCustomerSetupTime`(
	IN `p_ProcessID` INT,
	IN `p_customertable` VARCHAR(50),
	IN `p_vendortable` VARCHAR(50)

)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	SET SESSION innodb_lock_wait_timeout = 180;

	-- for sippy update connect_time from vendor (setup time)  cdr to customer cdr

	SET @stmt = CONCAT('
		UPDATE `'' , p_customertable , ''` cd
	INNER JOIN  `'' , p_vendortable , ''` vd ON cd.ID = vd.ID
		SET cd.connect_time = vd.connect_time
	WHERE cd.ProcessID =  "'' , p_ProcessID , ''"
		AND vd.ProcessID =  "'' , p_ProcessID , ''";
	');

	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	-- return no of rows updated
	select FOUND_ROWS() as rows_updated;

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
		AND billed_duration = 0 AND cost = 0 AND ( disposition <> "ANSWERED" OR disposition IS NULL);

	');

	PREPARE stmt3 FROM @stm3;
	EXECUTE stmt3;
	DEALLOCATE PREPARE stmt3;

	SET @stm4 = CONCAT('
	DELETE FROM `' , p_tbltempusagedetail_name , '` WHERE processid = "' , p_processId , '"  AND billed_duration = 0 AND cost = 0 AND ( disposition <> "ANSWERED" OR disposition IS NULL);
	');


	-- FOR MIRTA ONLY

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

	-- for mirta only over




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
	WHERE   processid = "' , p_processId , '" ;
	');

	PREPARE stmt5 FROM @stm5;
	EXECUTE stmt5;
	DEALLOCATE PREPARE stmt5;


	-- for retail only
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


	SET @stm6 = CONCAT('
	DELETE FROM `' , p_tbltempusagedetail_name , '` WHERE processid = "' , p_processId , '" ;
	');

	PREPARE stmt6 FROM @stm6;
	EXECUTE stmt6;
	DEALLOCATE PREPARE stmt6;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;
