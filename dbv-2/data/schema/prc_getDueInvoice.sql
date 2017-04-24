CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getDueInvoice`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_BillingClassID` INT
)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT * FROM(
		SELECT
			inv.InvoiceID AS InvoiceID,
			inv.GrandTotal,
			inv.GrandTotal - (SELECT COALESCE(SUM(p.Amount),0) FROM tblPayment p WHERE p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0) AS InvoiceOutStanding,
			inv.FullInvoiceNumber as InvoiceNumber,
			DATE_ADD(inv.IssueDate,INTERVAL b.PaymentDueInDays DAY) as DueDate,
			DATEDIFF(NOW(),DATE_ADD(inv.IssueDate,INTERVAL b.PaymentDueInDays DAY)) as DueDay,
			a.AccountID,
			a.created_at as AccountCreationDate
		FROM tblInvoice inv
		INNER JOIN NeonRMDev.tblAccount a
			ON inv.AccountID = a.AccountID
		INNER JOIN NeonRMDev.tblAccountBilling ab
			ON ab.AccountID = a.AccountID	AND ab.ServiceID = inv.ServiceID
		INNER JOIN NeonRMDev.tblBillingClass b
			ON b.BillingClassID = ab.BillingClassID
		WHERE inv.CompanyID = p_CompanyID
		AND ( p_AccountID = 0 OR inv.AccountID =   p_AccountID)
		AND (p_BillingClassID = 0 OR  b.BillingClassID = p_BillingClassID)
		AND InvoiceStatus NOT IN('awaiting','draft','Cancel')
		AND inv.GrandTotal <> 0
	)tbl
	WHERE InvoiceOutStanding > 0 ;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END