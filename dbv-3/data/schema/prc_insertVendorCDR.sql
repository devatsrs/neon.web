CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_insertVendorCDR`(IN `p_processId` varchar(200))
BEGIN
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	update tblVendorCDRHeader  h
	inner join  RMBilling3.tblTempVendorCDR d 
		on h.CompanyID = d.CompanyID
		AND h.CompanyGatewayID = d.CompanyGatewayID
		AND h.GatewayAccountID = d.GatewayAccountID
		AND h.StartDate = DATE_FORMAT(connect_time,'%Y-%m-%d')  
	set h.updated_at = NOW()
		where processid = p_processId;

	insert into   tblVendorCDRHeader (CompanyID,CompanyGatewayID,GatewayAccountID,AccountID,StartDate,created_at)
	select distinct d.CompanyID,d.CompanyGatewayID,d.GatewayAccountID,d.AccountID,DATE_FORMAT(connect_time,'%Y-%m-%d'),NOW()  
	from RMBilling3.tblTempVendorCDR d
	left join tblVendorCDRHeader h 
		on h.CompanyID = d.CompanyID
		AND h.CompanyGatewayID = d.CompanyGatewayID
		AND h.GatewayAccountID = d.GatewayAccountID
		AND h.StartDate = DATE_FORMAT(connect_time,'%Y-%m-%d')  

		where h.GatewayAccountID is null and processid = p_processId;
	
	DELETE FROM RMBilling3.tblTempVendorCDR WHERE processid = p_processId  and billed_duration = 0 and selling_cost = 0;

	insert into tblVendorCDR (VendorCDRHeaderID,billed_duration, ID, selling_cost, buying_cost, connect_time, disconnect_time,cli, cld,trunk,area_prefix,  remote_ip, ProcessID)
	select VendorCDRHeaderID,billed_duration, ID, selling_cost, buying_cost, connect_time, disconnect_time,cli, cld,trunk,area_prefix,  remote_ip, ProcessID
		 from  RMBilling3.tblTempVendorCDR d left join tblVendorCDRHeader h	 on h.CompanyID = d.CompanyID
		AND h.CompanyGatewayID = d.CompanyGatewayID
		AND h.GatewayAccountID = d.GatewayAccountID
		AND h.StartDate = DATE_FORMAT(connect_time,'%Y-%m-%d') 
	where   processid = p_processId ;

    
	DELETE FROM RMBilling3.tblTempVendorCDR WHERE processid = p_processId ;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END