CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetBlockUnblockAccount`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT
		DISTINCT
		a.AccountID,
		a.Number,
		a.AccountName,
		(COALESCE(ab.BalanceAmount,0) - COALESCE(ab.PermanentCredit,0)) as Balance,
		IF((COALESCE(ab.BalanceAmount,0) - COALESCE(ab.PermanentCredit,0)) > 0 AND a.`Status` = 1 ,1,0) as BlockStatus,
		a.`Status`,
		a.BillingEmail,
		a.Blocked
	FROM tblAccountBalance ab
	INNER JOIN tblAccount a
		ON a.AccountID = ab.AccountID
	INNER JOIN NeonBillingDev.tblGatewayAccount ga
		ON ga.AccountID = a.AccountID
	WHERE a.CompanyId = p_CompanyID
	AND a.AccountType = 1
	AND ( p_CompanyGatewayID = 0 OR ga.CompanyGatewayID = p_CompanyGatewayID)
	ORDER BY BlockStatus,a.AccountID;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END