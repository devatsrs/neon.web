CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_insertPostProcessCDR`(IN `p_ProcessID` VarCHAR(200))
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	INSERT INTO tblCDRPostProcess(CompanyID,CompanyGatewayID,GatewayAccountID,AccountID,connect_time,disconnect_time,billed_duration,billed_second,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID)
	SELECT
		CompanyID,
		CompanyGatewayID,
		GatewayAccountID,
		AccountID,
		connect_time,
		disconnect_time,
		billed_duration,
		billed_second,
		area_prefix,
		pincode,
		extension,
		cli,
		cld,
		cost,
		remote_ip,
		duration,
		trunk,
		ProcessID,
		ID
	FROM tblUsageDetails ud
	INNER JOIN tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	WHERE  uh.AccountID IS NOT NULL
		AND ud.ProcessID = p_ProcessID;
	
	INSERT INTO tblVCDRPostProcess(CompanyID,CompanyGatewayID,GatewayAccountID,AccountID,billed_duration,billed_second, ID, selling_cost, buying_cost, connect_time, disconnect_time,cli, cld,trunk,area_prefix,  remote_ip, ProcessID)
	SELECT
		CompanyID,
		CompanyGatewayID,
		GatewayAccountID,
		AccountID,
		billed_duration,
		billed_second, 
		ID, 
		selling_cost, 
		buying_cost, 
		connect_time, 
		disconnect_time,
		cli, 
		cld,
		trunk,
		area_prefix,  
		remote_ip, 
		ProcessID
	FROM tblVendorCDR ud
	INNER JOIN tblVendorCDRHeader uh
		ON uh.VendorCDRHeaderID = ud.VendorCDRHeaderID
	WHERE  uh.AccountID IS NOT NULL
		AND ud.ProcessID = p_ProcessID;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 

END