CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_AccountPaymentReminder`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_BillingClassID` INT
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	CALL NeonBillingDev.prc_updateSOAOffSet(p_CompanyID,p_AccountID);
	
	
	SELECT
		DISTINCT
		a.AccountID,
		ab.SOAOffset
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
	AND ab.SOAOffset > 0
	AND a.`Status` = 1;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END