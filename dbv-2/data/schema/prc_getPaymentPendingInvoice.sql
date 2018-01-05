CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_getPaymentPendingInvoice`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_PaymentDueInDays` INT 


,
	IN `p_AutoPay` INT


)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	IF p_AutoPay=1
	THEN
	
	SELECT
		MAX(i.InvoiceID) AS InvoiceID,
		(IFNULL(MAX(i.GrandTotal), 0) - IFNULL(SUM(p.Amount), 0)) AS RemaingAmount
	FROM tblInvoice i
	INNER JOIN NeonRMDev.tblAccount a
		ON i.AccountID = a.AccountID
	INNER JOIN NeonRMDev.tblAccountBilling ab 
		ON ab.AccountID = a.AccountID AND ab.ServiceID = i.ServiceID
	INNER JOIN NeonRMDev.tblBillingClass b
		ON b.BillingClassID = ab.BillingClassID
	LEFT JOIN tblPayment p
		ON p.AccountID = i.AccountID
		AND p.InvoiceID = i.InvoiceID AND p.Status = 'Approved' AND p.AccountID = i.AccountID
		AND p.Status = 'Approved'
		AND p.Recall = 0
	WHERE i.CompanyID = p_CompanyID
	AND i.InvoiceStatus NOT IN ( 'awaiting','cancel' , 'draft' , 'paid','post')
	AND ( (i.ItemInvoice IS NULL) OR (i.ItemInvoice=1 AND i.RecurringInvoiceID IS NOT NULL))
	AND i.InvoiceType =1
	AND i.AccountID = p_AccountID
	AND (p_PaymentDueInDays =0  OR (p_PaymentDueInDays =1 AND TIMESTAMPDIFF(DAY, i.IssueDate, NOW()) >= IFNULL(b.PaymentDueInDays,0) ) )

	GROUP BY i.InvoiceID,
			 p.AccountID
	HAVING (IFNULL(MAX(i.GrandTotal), 0) - IFNULL(SUM(p.Amount), 0)) > 0;
	
	END IF;
	
	IF p_AutoPay =0
	THEN
	
	SELECT
		MAX(i.InvoiceID) AS InvoiceID,
		(IFNULL(MAX(i.GrandTotal), 0) - IFNULL(SUM(p.Amount), 0)) AS RemaingAmount
	FROM tblInvoice i
	LEFT JOIN tblPayment p
		ON p.AccountID = i.AccountID
		AND p.InvoiceID = i.InvoiceID AND p.Status = 'Approved' AND p.AccountID = i.AccountID
		AND p.Status = 'Approved'
		AND p.Recall = 0
	WHERE i.CompanyID = p_CompanyID
	AND i.InvoiceStatus != 'cancel'
	AND i.AccountID = p_AccountID

	GROUP BY i.InvoiceID,
			 p.AccountID;
--	HAVING (IFNULL(MAX(i.GrandTotal), 0) - IFNULL(SUM(p.Amount), 0)) > 0;
	
	END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END