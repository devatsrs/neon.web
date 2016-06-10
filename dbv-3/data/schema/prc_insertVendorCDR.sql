CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_insertVendorCDR`(IN `p_processId` VARCHAR(200), IN `p_tbltempusagedetail_name` VARCHAR(50))
BEGIN
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

 
	set @stm2 = CONCAT('
	insert into   tblVendorCDRHeader (CompanyID,CompanyGatewayID,GatewayAccountID,AccountID,StartDate,created_at)
	select distinct d.CompanyID,d.CompanyGatewayID,d.GatewayAccountID,d.AccountID,DATE_FORMAT(connect_time,"%Y-%m-%d"),NOW()  
	from `' , p_tbltempusagedetail_name , '` d
	left join tblVendorCDRHeader h 
		on h.CompanyID = d.CompanyID
		AND h.CompanyGatewayID = d.CompanyGatewayID
		AND h.GatewayAccountID = d.GatewayAccountID
		AND h.StartDate = DATE_FORMAT(connect_time,"%Y-%m-%d")  

		where h.GatewayAccountID is null and processid = "' , p_processId , '";
		');
		
	PREPARE stmt2 FROM @stm2;
   EXECUTE stmt2;
	DEALLOCATE PREPARE stmt2;
	
	set @stm6 = CONCAT('
	insert into tblVendorCDRFailed (VendorCDRHeaderID,billed_duration, ID, selling_cost, buying_cost, connect_time, disconnect_time,cli, cld,trunk,area_prefix,  remote_ip, ProcessID)
	select VendorCDRHeaderID,billed_duration, ID, selling_cost, buying_cost, connect_time, disconnect_time,cli, cld,trunk,area_prefix,  remote_ip, ProcessID
		 from  `' , p_tbltempusagedetail_name , '` d inner join tblVendorCDRHeader h	 on h.CompanyID = d.CompanyID
		AND h.CompanyGatewayID = d.CompanyGatewayID
		AND h.GatewayAccountID = d.GatewayAccountID
		AND h.StartDate = DATE_FORMAT(connect_time,"%Y-%m-%d")
		
	where   processid = "' , p_processId , '" AND  billed_duration = 0 and buying_cost = 0 ;
	');
	
	PREPARE stmt6 FROM @stm6;
   EXECUTE stmt6;
	DEALLOCATE PREPARE stmt6;
	
	
	set @stm3 = CONCAT('
	DELETE FROM `' , p_tbltempusagedetail_name , '` WHERE processid = "' , p_processId , '"  and billed_duration = 0 and buying_cost = 0;
	');
	
	PREPARE stmt3 FROM @stm3;
   EXECUTE stmt3;
	DEALLOCATE PREPARE stmt3;

	set @stm4 = CONCAT('
	insert into tblVendorCDR (VendorCDRHeaderID,billed_duration, ID, selling_cost, buying_cost, connect_time, disconnect_time,cli, cld,trunk,area_prefix,  remote_ip, ProcessID)
	select VendorCDRHeaderID,billed_duration, ID, selling_cost, buying_cost, connect_time, disconnect_time,cli, cld,trunk,area_prefix,  remote_ip, ProcessID
		 from  `' , p_tbltempusagedetail_name , '` d inner join tblVendorCDRHeader h	 on h.CompanyID = d.CompanyID
		AND h.CompanyGatewayID = d.CompanyGatewayID
		AND h.GatewayAccountID = d.GatewayAccountID
		AND h.StartDate = DATE_FORMAT(connect_time,"%Y-%m-%d") 
	where   processid = "' , p_processId , '" ;
	');
	
	PREPARE stmt4 FROM @stm4;
   EXECUTE stmt4;
	DEALLOCATE PREPARE stmt4;

   set @stm5 = CONCAT(' 
	DELETE FROM `' , p_tbltempusagedetail_name , '` WHERE processid = "' , p_processId , '" ;
	');
	
	PREPARE stmt5 FROM @stm5;
   EXECUTE stmt5;
	DEALLOCATE PREPARE stmt5;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END