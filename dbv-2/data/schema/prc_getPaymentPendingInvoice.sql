CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getPaymentPendingInvoice`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_PaymentDueInDays` INT )
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT
		MAX(i.InvoiceID) AS InvoiceID,
		(IFNULL(MAX(i.GrandTotal), 0) - IFNULL(SUM(p.Amount), 0)) AS RemaingAmount
	FROM tblInvoice i
	LEFT JOIN Ratemanagement3.tblAccount a
		ON i.AccountID = a.AccountID
	LEFT JOIN tblInvoiceTemplate it 
		ON a.InvoiceTemplateID = it.InvoiceTemplateID
	LEFT JOIN tblPayment p
		ON p.AccountID = i.AccountID
		AND REPLACE(p.InvoiceNo,'-','') = (CONCAT( ltrim(rtrim(REPLACE(it.InvoiceNumberPrefix,'-',''))), ltrim(rtrim(i.InvoiceNumber)) )) AND P.Status = 'Approved' AND p.AccountID = i.AccountID
		AND p.Status = 'Approved'
	WHERE i.CompanyID = p_CompanyID
	AND i.InvoiceStatus != 'cancel'
	AND i.AccountID = p_AccountID
	AND (p_PaymentDueInDays =0  OR (p_PaymentDueInDays =1 AND TIMESTAMPDIFF(DAY, i.IssueDate, NOW()) >= PaymentDueInDays) )

	GROUP BY i.InvoiceID,
			 p.AccountID
	HAVING (IFNULL(MAX(i.GrandTotal), 0) - IFNULL(SUM(p.Amount), 0)) > 0;	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END