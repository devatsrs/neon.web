CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getBillingAccounts`(
	IN `p_CompanyID` INT,
	IN `p_Today` DATE,
	IN `p_skip_accounts` TEXT
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT 
		DISTINCT
		tblAccount.AccountID, 
		tblAccountBilling.NextInvoiceDate,
		AccountName, 
		tblAccountBilling.ServiceID
	FROM tblAccount 
	LEFT JOIN tblAccountService 
		ON tblAccountService.AccountID = tblAccount.AccountID
	LEFT JOIN tblAccountBilling 
		ON tblAccountBilling.AccountID = tblAccount.AccountID
		AND (( tblAccountBilling.ServiceID = 0  ) OR ( tblAccountService.ServiceID > 0 AND tblAccountBilling.ServiceID = tblAccountService.ServiceID AND tblAccountService.Status = 1)  ) 
	WHERE tblAccount.CompanyId = p_CompanyID 
	AND tblAccount.Status = 1 
	AND AccountType = 1 
	AND Billing = 1
	-- AND tblAccountBilling.NextInvoiceDate <>  ''
	--  AND tblAccountBilling.NextInvoiceDate <> '0000-00-00' 
	AND tblAccountBilling.NextInvoiceDate <= p_Today
	AND tblAccountBilling.BillingCycleType IS NOT NULL 
	AND FIND_IN_SET(tblAccount.AccountID,p_skip_accounts) = 0	
	ORDER BY tblAccount.AccountID ASC;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END