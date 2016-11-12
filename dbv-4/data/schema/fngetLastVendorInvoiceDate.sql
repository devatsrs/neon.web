CREATE DEFINER=`root`@`localhost` FUNCTION `fngetLastVendorInvoiceDate`(`p_AccountID` INT) RETURNS date
BEGIN
	
	DECLARE v_LastInvoiceDate_ DATE;
	
	SELECT
		CASE WHEN EndDate IS NOT NULL AND EndDate <> '' 
		THEN 
			DATE_FORMAT(EndDate,'%Y-%m-%d')
		ELSE 
			CASE WHEN BillingStartDate IS NOT NULL AND BillingStartDate <> ''
			THEN
				DATE_FORMAT(BillingStartDate,'%Y-%m-%d')
			ELSE DATE_FORMAT(tblAccount.created_at,'%Y-%m-%d')
			END 
		END 
	INTO
		v_LastInvoiceDate_ 
	FROM NeonBillingDev.tblInvoice
	INNER JOIN NeonBillingDev.tblInvoiceDetail
		ON tblInvoice.InvoiceID =  tblInvoiceDetail.InvoiceID
	INNER JOIN NeonRMDev.tblAccount
		ON tblAccount.AccountID = tblInvoice.AccountID
	LEFT JOIN NeonRMDev.tblAccountBilling 
		ON tblAccountBilling.AccountID = tblInvoice.AccountID
	WHERE InvoiceType =2 
		AND tblInvoice.AccountID = p_AccountID 
	ORDER BY IssueDate DESC 
	LIMIT 1;

	RETURN v_LastInvoiceDate_;

END