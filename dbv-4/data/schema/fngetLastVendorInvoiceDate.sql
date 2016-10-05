CREATE DEFINER=`root`@`localhost` FUNCTION `fngetLastVendorInvoiceDate`(`p_AccountID` INT) RETURNS date
BEGIN
	
	DECLARE v_LastInvoiceDate_ DATE;
	
	SELECT 
		EndDate 
	INTO
		v_LastInvoiceDate_ 
	FROM NeonBillingDev.tblInvoice
	INNER JOIN NeonBillingDev.tblInvoiceDetail
		ON tblInvoice.InvoiceID =  tblInvoiceDetail.InvoiceID
	WHERE InvoiceType =2 
		AND AccountID = p_AccountID 
	ORDER BY IssueDate DESC 
	LIMIT 1;

	RETURN v_LastInvoiceDate_;

END