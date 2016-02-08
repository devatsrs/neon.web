CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getMissingAccounts`(IN `p_CompanyID` int)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
    -- Insert state... *** SQLINES FOR EVALUATION USE ONLY *** 
	SELECT cg.Title,ga.AccountName from tblGatewayAccount ga
	inner join Ratemanagement3.tblCompanyGateway cg on ga.CompanyGatewayID = cg.CompanyGatewayID
	where ga.GatewayAccountID is not null and ga.CompanyID =p_CompanyID and ga.AccountID is null;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END