USE `RMCDR3`;

ALTER TABLE `tblUsageDetailFailedCall`
	ADD COLUMN `disposition` VARCHAR(50) NULL DEFAULT NULL;
ALTER TABLE `tblUsageDetails`
	ADD COLUMN `disposition` VARCHAR(50) NULL DEFAULT NULL;

DROP PROCEDURE IF EXISTS `prc_unsetCDRUsageAccount`;
DELIMITER |
CREATE PROCEDURE `prc_unsetCDRUsageAccount`(
	IN `p_CompanyID` INT,
	IN `p_IPs` LONGTEXT,
	IN `p_StartDate` VARCHAR(50),
	IN `p_Confirm` INT
)
BEGIN

	DECLARE v_AccountID int;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_AccountID = 0;
		SELECT DISTINCT GAC.AccountID INTO v_AccountID 
		FROM RMBilling3.tblGatewayAccount GAC
		WHERE GAC.CompanyID = p_CompanyID
		AND AccountID IS NOT NULL
		AND  FIND_IN_SET(GAC.GatewayAccountID, p_IPs) > 0
		LIMIT 1;
	
	IF v_AccountID = 0
	THEN
		SELECT DISTINCT AccountID INTO v_AccountID FROM tblUsageHeader UH
			WHERE UH.CompanyID = p_CompanyID
			AND AccountID IS NOT NULL
			AND  FIND_IN_SET(UH.CompanyGatewayID, p_IPs) > 0
			LIMIT 1; 
	END IF;
	
	IF v_AccountID = 0
	THEN
		SELECT DISTINCT AccountID INTO v_AccountID FROM tblVendorCDRHeader VH
			WHERE VH.CompanyID = p_CompanyID
			AND AccountID IS NOT NULL
			AND  FIND_IN_SET(VH.GatewayAccountID, p_IPs) > 0
			LIMIT 1; 
	END IF;
	IF v_AccountID >0 AND p_Confirm = 1 THEN
			UPDATE RMBilling3.tblGatewayAccount GAC SET GAC.AccountID = NULL
			WHERE GAC.CompanyID = p_CompanyID
			AND  FIND_IN_SET(GAC.GatewayAccountID, p_IPs) > 0;
	
			Update tblUsageHeader SET AccountID = NULL
			WHERE CompanyID = p_CompanyID
			AND FIND_IN_SET(GatewayAccountID,p_IPs)>0			
			AND StartDate >= p_StartDate;
						
			Update tblVendorCDRHeader SET AccountID = NULL
			WHERE CompanyID = p_CompanyID
			AND FIND_IN_SET(GatewayAccountID,p_IPs)>0
			AND StartDate >= p_StartDate;
	SET v_AccountID = -1;
	END IF;

	SELECT v_AccountID as `Status`;

SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
END|
DELIMITER ;

DELIMITER |
CREATE PROCEDURE `prc_insertCDR`(
	IN `p_processId` varchar(200),
	IN `p_tbltempusagedetail_name` VARCHAR(200)
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	SET SESSION innodb_lock_wait_timeout = 180;
 
    
    
    
    set @stm2 = CONCAT('
	insert into   tblUsageHeader (CompanyID,CompanyGatewayID,GatewayAccountID,AccountID,StartDate,created_at)
	select distinct d.CompanyID,d.CompanyGatewayID,d.GatewayAccountID,d.AccountID,DATE_FORMAT(connect_time,"%Y-%m-%d"),NOW()  
	from `' , p_tbltempusagedetail_name , '` d
	left join tblUsageHeader h 
		on h.CompanyID = d.CompanyID
		AND h.CompanyGatewayID = d.CompanyGatewayID
		AND h.GatewayAccountID = d.GatewayAccountID
		AND h.StartDate = DATE_FORMAT(connect_time,"%Y-%m-%d")
		where h.GatewayAccountID is null and processid = "' , p_processId , '";
	');

    PREPARE stmt2 FROM @stm2;
    EXECUTE stmt2;
    DEALLOCATE PREPARE stmt2;
    

	set @stm3 = CONCAT('
	insert into tblUsageDetailFailedCall (UsageHeaderID,connect_time,disconnect_time,billed_duration,billed_second,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID,is_inbound,disposition)
	select UsageHeaderID,connect_time,disconnect_time,billed_duration,billed_second,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID,is_inbound,disposition
		 from  `' , p_tbltempusagedetail_name , '` d inner join tblUsageHeader h	 on h.CompanyID = d.CompanyID
		AND h.CompanyGatewayID = d.CompanyGatewayID
		AND h.GatewayAccountID = d.GatewayAccountID
		AND h.StartDate = DATE_FORMAT(connect_time,"%Y-%m-%d")
	where   processid = "' , p_processId , '"
	and billed_duration = 0 and cost = 0 AND ( disposition <> "ANSWERED" or disposition IS NULL);
	');

    PREPARE stmt3 FROM @stm3;
    EXECUTE stmt3;
    DEALLOCATE PREPARE stmt3;
    
    
	set @stm4 = CONCAT('    
	DELETE FROM `' , p_tbltempusagedetail_name , '` WHERE processid = "' , p_processId , '"  and billed_duration = 0 and cost = 0 AND ( disposition <> "ANSWERED" or disposition IS NULL);
	');

    PREPARE stmt4 FROM @stm4;
    EXECUTE stmt4;
    DEALLOCATE PREPARE stmt4;

	set @stm5 = CONCAT(' 
	insert into tblUsageDetails (UsageHeaderID,connect_time,disconnect_time,billed_duration,billed_second,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID,is_inbound,disposition)
	select UsageHeaderID,connect_time,disconnect_time,billed_duration,billed_second,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID,is_inbound,disposition
		 from  `' , p_tbltempusagedetail_name , '` d inner join tblUsageHeader h	 on h.CompanyID = d.CompanyID
		AND h.CompanyGatewayID = d.CompanyGatewayID
		AND h.GatewayAccountID = d.GatewayAccountID
		AND h.StartDate = DATE_FORMAT(connect_time,"%Y-%m-%d")
	where   processid = "' , p_processId , '" ;
	');
    PREPARE stmt5 FROM @stm5;
    EXECUTE stmt5;
    DEALLOCATE PREPARE stmt5;

 	set @stm6 = CONCAT(' 
	DELETE FROM `' , p_tbltempusagedetail_name , '` WHERE processid = "' , p_processId , '" ;
	');
    PREPARE stmt6 FROM @stm6;
    EXECUTE stmt6;
    DEALLOCATE PREPARE stmt6;
    

	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END|
DELIMITER ;