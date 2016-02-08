CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_insertTempVendorCDR`(IN `p_processId` varchar(200))
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
	
	INSERT INTO RMCDR3.tblTempVendorCDR (CompanyID, CompanyGatewayID, GatewayAccountID, AccountID, billed_duration, ID, selling_cost, buying_cost, connect_time, disconnect_time,cli, cld,trunk,area_prefix,  remote_ip, ProcessID)
    SELECT                                   CompanyID, CompanyGatewayID, GatewayAccountID, AccountID, billed_duration, ID, selling_cost, buying_cost, connect_time, disconnect_time,cli, cld,trunk,area_prefix,  remote_ip, ProcessID
    FROM tblTempVendorCDR
    WHERE processid = p_processId;
    
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END