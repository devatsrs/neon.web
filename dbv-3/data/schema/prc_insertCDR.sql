CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_insertCDR`(IN `p_processId` varchar(200), IN `p_tbltempusagedetail_name` VARCHAR(200))
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
	insert into tblUsageDetailFailedCall (UsageHeaderID,connect_time,disconnect_time,billed_duration,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID)
	select UsageHeaderID,connect_time,disconnect_time,billed_duration,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID
		 from  `' , p_tbltempusagedetail_name , '` d left join tblUsageHeader h	 on h.CompanyID = d.CompanyID
		AND h.CompanyGatewayID = d.CompanyGatewayID
		AND h.GatewayAccountID = d.GatewayAccountID
		AND h.StartDate = DATE_FORMAT(connect_time,"%Y-%m-%d")
	where   processid = "' , p_processId , '"
	and billed_duration = 0 and cost = 0;
	');

    PREPARE stmt3 FROM @stm3;
    EXECUTE stmt3;
    DEALLOCATE PREPARE stmt3;
    
    
	set @stm4 = CONCAT('    
	DELETE FROM `' , p_tbltempusagedetail_name , '` WHERE processid = "' , p_processId , '"  and billed_duration = 0 and cost = 0;
	');

    PREPARE stmt4 FROM @stm4;
    EXECUTE stmt4;
    DEALLOCATE PREPARE stmt4;

	set @stm5 = CONCAT(' 
	insert into tblUsageDetails (UsageHeaderID,connect_time,disconnect_time,billed_duration,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID,is_inbound)
	select UsageHeaderID,connect_time,disconnect_time,billed_duration,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID,is_inbound
		 from  `' , p_tbltempusagedetail_name , '` d left join tblUsageHeader h	 on h.CompanyID = d.CompanyID
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
END