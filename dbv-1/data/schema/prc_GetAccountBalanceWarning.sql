CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetAccountBalanceWarning`(IN `p_CompanyID` INT, IN `p_AccountID` INT)
BEGIN

	SELECT
		IF ( (CASE WHEN ab.BalanceThreshold LIKE '%p' THEN REPLACE(ab.BalanceThreshold, 'p', '')/ 100 * ab.BalanceAmount ELSE ab.BalanceThreshold END) < ab.CurrentCredit ,1,0) as BalanceWarning,
		ab.BalanceAmount,
		ab.CurrentCredit,
		ab.BalanceThreshold,
		a.BillingEmail,
		a.AccountName,
		a.AccountID,
		a.Owner 
	FROM tblAccountBalance ab 
	INNER JOIN tblAccount a ON a.AccountID = ab.AccountID
	WHERE BalanceThreshold IS NOT NULL
	AND  a.CompanyId = p_CompanyID
	AND (p_AccountID = 0 OR  a.AccountID = p_AccountID);

END