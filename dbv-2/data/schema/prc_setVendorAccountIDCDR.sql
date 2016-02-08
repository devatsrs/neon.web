CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_setVendorAccountIDCDR`(IN `p_companyid` int, IN `p_processId` varchar(200) )
BEGIN
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

 	UPDATE tblTempVendorCDR uh
	INNER JOIN tblGatewayAccount ga
		ON ga.GatewayAccountID = uh.GatewayAccountID
		AND ga.CompanyID = uh.CompanyID
		AND ga.CompanyGatewayID = uh.CompanyGatewayID
	SET uh.AccountID = ga.AccountID
	WHERE uh.AccountID IS NULL
	AND ga.AccountID is not null
	AND uh.CompanyID = p_companyid
	AND uh.ProcessID = p_processId;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

	 
END