CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getMissingAccounts`(IN `p_CompanyID` int, IN `p_CompanyGatewayID` INT)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
	SELECT cg.Title,ga.AccountName from tblGatewayAccount ga
	inner join LocalRatemanagement.tblCompanyGateway cg on ga.CompanyGatewayID = cg.CompanyGatewayID
	where ga.GatewayAccountID is not null and ga.CompanyID =p_CompanyID and ga.AccountID is null AND cg.`Status` =1
	AND (p_CompanyGatewayID = 0 or ga.CompanyGatewayID = p_CompanyGatewayID );
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END