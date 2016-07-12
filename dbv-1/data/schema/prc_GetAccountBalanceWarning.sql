CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetAccountBalanceWarning`(IN `p_CompanyID` INT, IN `p_AccountID` INT)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	CALL NeonBillingDev.prc_updateSOAOffSet(p_CompanyID);
	
	
	SELECT
		IF ( (CASE WHEN ab.BalanceThreshold LIKE '%p' THEN REPLACE(ab.BalanceThreshold, 'p', '')/ 100 * ab.PermanentCredit ELSE ab.BalanceThreshold END) < ab.BalanceAmount ,1,0) as BalanceWarning,
		ab.BalanceAmount,
		ab.UnbilledAmount,
		ab.BalanceThreshold,
		ab.PermanentCredit,
		ab.EmailToCustomer,
		a.BillingEmail,
		a.AccountName,
		a.AccountID,
		a.Owner 
	FROM tblAccountBalance ab 
	INNER JOIN tblAccount a ON a.AccountID = ab.AccountID
	WHERE BalanceThreshold IS NOT NULL
	AND  a.CompanyId = p_CompanyID
	AND (p_AccountID = 0 OR  a.AccountID = p_AccountID)
	AND a.`Status` = 1;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END