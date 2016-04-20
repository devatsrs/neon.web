CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getMissingAccounts`(IN `p_CompanyID` int)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
    -- Insert state... *** SQLINES FOR EVALUATION USE ONLY *** 
	SELECT cg.Title,ga.AccountName FROM tblGatewayAccount ga
	INNER JOIN Ratemanagement3.tblCompanyGateway cg ON ga.CompanyGatewayID = cg.CompanyGatewayID
	WHERE ga.GatewayAccountID IS NOT NULL AND ga.CompanyID =p_CompanyID AND ga.AccountID IS NULL AND cg.`Status` =1 ;

	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END