CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_setVendorAccountID`(IN `p_companyid` int)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	UPDATE RMCDR3.tblVendorCDRHeader uh
	INNER JOIN tblGatewayAccount ga
		ON ga.GatewayAccountID = uh.GatewayAccountID
		AND ga.CompanyID = uh.CompanyID
		AND ga.CompanyGatewayID = uh.CompanyGatewayID
	SET uh.AccountID = ga.AccountID
	WHERE uh.AccountID IS NULL
	AND ga.AccountID is not null
	AND uh.CompanyID = p_companyid;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END