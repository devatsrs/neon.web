CREATE DEFINER=`root`@`localhost` FUNCTION `fngetLastInvoiceDate`(
	`p_AccountID` INT
) RETURNS date
BEGIN
	
	DECLARE v_LastInvoiceDate_ DATE;
	
	SELECT 
		CASE WHEN tblAccountBilling.LastInvoiceDate IS NOT NULL AND tblAccountBilling.LastInvoiceDate <> '' 
		THEN 
			DATE_FORMAT(tblAccountBilling.LastInvoiceDate,'%Y-%m-%d')
		ELSE 
			CASE WHEN tblAccountBilling.BillingStartDate IS NOT NULL AND tblAccountBilling.BillingStartDate <> ''
			THEN
				DATE_FORMAT(tblAccountBilling.BillingStartDate,'%Y-%m-%d')
			ELSE DATE_FORMAT(tblAccount.created_at,'%Y-%m-%d')
			END 
		END
		INTO v_LastInvoiceDate_ 
	FROM NeonRMDev.tblAccount
	LEFT JOIN NeonRMDev.tblAccountBilling 
		ON tblAccountBilling.AccountID = tblAccount.AccountID AND tblAccountBilling.ServiceID = 0
	WHERE tblAccount.AccountID = p_AccountID
	LIMIT 1;
	
	RETURN v_LastInvoiceDate_;
	
END