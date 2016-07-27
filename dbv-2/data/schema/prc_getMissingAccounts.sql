CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getMissingAccounts`(IN `p_CompanyID` int, IN `p_CompanyGatewayID` INT)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	SELECT cg.Title,ga.AccountName FROM tblGatewayAccount ga
	INNER JOIN RateManagement4.tblCompanyGateway cg ON ga.CompanyGatewayID = cg.CompanyGatewayID
	WHERE ga.GatewayAccountID IS NOT NULL AND ga.CompanyID =p_CompanyID AND ga.AccountID IS NULL AND cg.`Status` =1 
	AND (p_CompanyGatewayID = 0 or ga.CompanyGatewayID = p_CompanyGatewayID )
	ORDER BY ga.CompanyGatewayID,ga.AccountName;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END