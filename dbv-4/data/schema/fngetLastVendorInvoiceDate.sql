CREATE DEFINER=`root`@`localhost` FUNCTION `fngetLastVendorInvoiceDate`(
	`p_AccountID` INT
) RETURNS DATETIME
BEGIN
	
	DECLARE v_LastInvoiceDate_ DATETIME;
	
	SELECT
		CASE WHEN EndDate IS NOT NULL AND EndDate <> '' 
		THEN 
			EndDate
		ELSE 
			CASE WHEN BillingStartDate IS NOT NULL AND BillingStartDate <> ''
			THEN
				DATE_FORMAT(BillingStartDate,'%Y-%m-%d')
			ELSE DATE_FORMAT(tblAccount.created_at,'%Y-%m-%d')
			END 
		END 
	INTO
		v_LastInvoiceDate_ 
 	FROM NeonRMDev.tblAccount
	LEFT JOIN NeonRMDev.tblAccountBilling 
		ON tblAccountBilling.AccountID = tblAccount.AccountID
	LEFT JOIN NeonBillingDev.tblInvoice 
		ON tblAccount.AccountID = tblInvoice.AccountID AND InvoiceType =2
	LEFT JOIN NeonBillingDev.tblInvoiceDetail
		ON tblInvoice.InvoiceID =  tblInvoiceDetail.InvoiceID
	WHERE tblAccount.AccountID = p_AccountID 
	ORDER BY IssueDate DESC 
	LIMIT 1;

	RETURN v_LastInvoiceDate_;

END