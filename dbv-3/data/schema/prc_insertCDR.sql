CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_insertCDR`(IN `p_processId` varchar(200), IN `p_tbltempusagedetail_name` VARCHAR(200))
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET SESSION innodb_lock_wait_timeout = 180;
	/* Update DailySummaryStatus to 1 to trigger Summery Creation Job. this will keep on and off as cdr downloads for the day  
	set @stm1 = CONCAT('
	update tblUsageHeader h
	inner join  `' , p_tbltempusagedetail_name , '` d 
		on h.CompanyID = d.CompanyID
		AND h.CompanyGatewayID = d.CompanyGatewayID
		AND h.GatewayAccountID = d.GatewayAccountID
		AND h.StartDate = DATE_FORMAT(connect_time,"%Y-%m-%d")  
		set h.DailySummaryStatus =1 ,h.updated_at = NOW()
		where processid = "' , p_processId , '";
');

    PREPARE stmt1 FROM @stm1;
    /* EXECUTE stmt1; 
    DEALLOCATE PREPARE stmt1;*/
    
    
    
    
    set @stm2 = CONCAT('
	insert into   tblUsageHeader (CompanyID,CompanyGatewayID,GatewayAccountID,AccountID,StartDate,DailySummaryStatus,created_at)
	select distinct d.CompanyID,d.CompanyGatewayID,d.GatewayAccountID,d.AccountID,DATE_FORMAT(connect_time,"%Y-%m-%d"),1,NOW()  
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
	insert into tblUsageDetailFailedCall (UsageHeaderID,connect_time,disconnect_time,billed_duration,area_prefix,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID)
	select UsageHeaderID,connect_time,disconnect_time,billed_duration,area_prefix,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID
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
	insert into tblUsageDetails (UsageHeaderID,connect_time,disconnect_time,billed_duration,area_prefix,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID)
	select UsageHeaderID,connect_time,disconnect_time,billed_duration,area_prefix,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID
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