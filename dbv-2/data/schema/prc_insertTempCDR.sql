CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_insertTempCDR`(IN `p_processId` varchar(200))
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	INSERT INTO `RMCDR3`.tblTempUsageDetail (CompanyID, CompanyGatewayID, GatewayAccountID, AccountID, connect_time, disconnect_time, billed_duration, trunk, area_prefix, cli, cld, cost, ProcessID, ID, remote_ip, duration)
    SELECT
        CompanyID,
        CompanyGatewayID,
        GatewayAccountID,
        AccountID,
        connect_time,
        disconnect_time,
        billed_duration,
        trunk,
        area_prefix,
        cli,
        cld,
        cost,
        ProcessID,
        ID,
        remote_ip,
        duration
    FROM tblTempUsageDetail
    WHERE processid = p_processId;
    
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
END