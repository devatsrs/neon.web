CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_InsertTempReRateCDR`(IN `p_CompanyID` INT, IN `p_CompanyGatewayID` INT, IN `p_start_date` DATETIME, IN `p_end_date` DATETIME, IN `p_AccountID` INT, IN `p_ProcessID` VARCHAR(50), IN `p_tbltempusagedetail_name` VARCHAR(50), IN `p_CDRType` CHAR(1))
BEGIN
	 DECLARE v_BillingTime_ INT; 
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
    SELECT BillingTime INTO v_BillingTime_
	 FROM LocalRatemanagement.tblCompanyGateway cg
	 INNER JOIN tblGatewayAccount ga ON ga.CompanyGatewayID = cg.CompanyGatewayID
	 WHERE AccountID = p_AccountID AND (p_CompanyGatewayID = '' OR ga.CompanyGatewayID = p_CompanyGatewayID)
	 LIMIT 1;

    SET v_BillingTime_ = IFNULL(v_BillingTime_,1); 
    

    CALL fnUsageDetail(p_CompanyID,p_AccountID,p_CompanyGatewayID,p_start_date,p_end_date,0,1,v_BillingTime_,p_CDRType,'','',0); 
    
    set @stm1 = CONCAT('

    INSERT INTO LocalRMCdr.`' , p_tbltempusagedetail_name , '` (CompanyID,CompanyGatewayID,GatewayAccountID,AccountID,connect_time,disconnect_time,billed_duration,trunk,area_prefix,cli,cld,cost,ProcessID,duration,is_inbound,ID)

    SELECT "',p_CompanyID,'","',p_CompanyGatewayID,'",AccountName ,AccountID,connect_time,disconnect_time,billed_duration,trunk,area_prefix,cli,cld,cost,"',p_ProcessID,'",duration,is_inbound,ID
    FROM tmp_tblUsageDetails_');
    
    PREPARE stmt1 FROM @stm1;
    EXECUTE stmt1;
    DEALLOCATE PREPARE stmt1;
    
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
END