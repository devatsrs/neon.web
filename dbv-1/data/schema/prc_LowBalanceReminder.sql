CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_LowBalanceReminder`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_BillingClassID` INT
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	CALL NeonBillingDev.prc_updateSOAOffSet(p_CompanyID,p_AccountID);
	
	
	SELECT
		DISTINCT
		IF ( (CASE WHEN ab.BalanceThreshold LIKE '%p' THEN REPLACE(ab.BalanceThreshold, 'p', '')/ 100 * ab.PermanentCredit ELSE ab.BalanceThreshold END) < ab.BalanceAmount AND ab.BalanceThreshold <> 0 ,1,0) as BalanceWarning,
		a.AccountID
	FROM tblAccountBalance ab 
	INNER JOIN tblAccount a 
		ON a.AccountID = ab.AccountID
	INNER JOIN tblAccountBilling abg 
		ON abg.AccountID  = a.AccountID
	INNER JOIN tblBillingClass b
		ON b.BillingClassID = abg.BillingClassID
	WHERE a.CompanyId = p_CompanyID
	AND (p_AccountID = 0 OR  a.AccountID = p_AccountID)
	AND (p_BillingClassID = 0 OR  b.BillingClassID = p_BillingClassID)
	AND ab.PermanentCredit IS NOT NULL
	AND ab.BalanceThreshold IS NOT NULL
	AND a.`Status` = 1;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END