CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getDashboardinvoiceExpenseTotalOutstanding`(
	IN `p_CompanyID` INT,
	IN `p_CurrencyID` INT,
	IN `p_AccountID` INT,
	IN `p_StartDate` VARCHAR(100),
	IN `p_EndDate` VARCHAR(100)








)
BEGIN
DECLARE v_Round_ int;
	DECLARE v_TotalInvoice_ decimal(18,6);
	DECLARE v_TotalPayment_ decimal(18,6);
	DECLARE v_PaymentIn_ decimal(18,6);
	DECLARE v_PaymentOut_ decimal(18,6);
	DECLARE v_InvoiceIn_	decimal(18,6);
	DECLARE v_InvoiceOut_	decimal(18,6);
	DECLARE v_TotalUnpaidInvoices_ decimal(18,6);
	DECLARE v_TotalOverdueInvoices_ decimal(18,6);
	DECLARE v_TotalPaidInvoices_ decimal(18,6);
	DECLARE v_TotalDispute_ decimal(18,6);
	DECLARE v_TotalEstimate_ decimal(18,6);

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_Invoices_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Invoices_(
	
	InvoiceType tinyint(1),
		IssueDate datetime,
		GrandTotal decimal(18,6),
		InvoiceStatus varchar(50),
		PaymentDueInDays int,
		PendingAmount decimal(18,6),
		AccountID int
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_Payment_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Payment_(
		PaymentAmount decimal(18,6),
		PaymentDate datetime,
		PaymentType varchar(50)
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_Dispute_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Dispute_(
		DisputeAmount decimal(18,6)
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_Estimate_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Estimate_(
		EstimateTotal decimal(18,6)
	);
	
	INSERT INTO tmp_Dispute_
	SELECT ds.DisputeAmount FROM
	tblDispute ds
	INNER JOIN NeonRMDev.tblAccount ac ON ac.AccountID = ds.AccountID
	AND ac.CurrencyId = p_CurrencyID
	AND ds.CompanyID = p_CompanyID
	AND ds.InvoiceType = 0
	AND ((p_EndDate = '0' AND fnGetMonthDifference(ds.created_at,NOW()) <= p_StartDate) OR
			(p_EndDate<>'0' AND ds.created_at between p_StartDate AND p_EndDate));
			
	INSERT INTO tmp_Estimate_
	SELECT es.EstimateTotal FROM
	tblEstimate es
	INNER JOIN NeonRMDev.tblAccount ac ON ac.AccountID = es.AccountID
	AND ac.CurrencyId = p_CurrencyID
	AND es.CompanyID = p_CompanyID
	AND es.EstimateStatus NOT IN ('draft','accepted','rejected')
	AND ((p_EndDate = '0' AND fnGetMonthDifference(es.IssueDate,NOW()) <= p_StartDate) OR
			(p_EndDate<>'0' AND es.IssueDate between p_StartDate AND p_EndDate));
	
	INSERT INTO tmp_Invoices_
	SELECT 
		inv.InvoiceType,
		inv.IssueDate,
		inv.GrandTotal,
		inv.InvoiceStatus,
		(SELECT IFNULL(b.PaymentDueInDays,0) FROM NeonRMDev.tblAccountBilling ab INNER JOIN NeonRMDev.tblBillingClass b ON b.BillingClassID =ab.BillingClassID WHERE ab.AccountID = ac.AccountID) as PaymentDueInDays,
		(inv.GrandTotal -  (select IFNULL(sum(p.Amount),0) from tblPayment p where p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0) ) as `PendingAmount`,
		ac.AccountID
	FROM tblInvoice inv
	INNER JOIN NeonRMDev.tblAccount ac on ac.AccountID = inv.AccountID 
	WHERE 
		inv.CompanyID = p_CompanyID
		AND inv.CurrencyID = p_CurrencyID
		/*AND InvoiceType = 1 -- Invoice Out*/		
		AND InvoiceStatus NOT IN ( 'cancel' , 'draft' );
		
	INSERT INTO tmp_Payment_	
	SELECT 
		p.Amount,
		p.PaymentDate,
		p.PaymentType
		FROM tblPayment p 
	INNER JOIN NeonRMDev.tblAccount ac 
		ON ac.AccountID = p.AccountID
	WHERE 
		p.CompanyID = p_CompanyID
		AND ac.CurrencyId = p_CurrencyID	
		AND p.Status = 'Approved'
		AND p.Recall=0
		/*AND p.PaymentType = 'Payment In'*/
		AND (p_AccountID = 0 or ac.AccountID = p_AccountID);


	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	/*Total Invoices*/
	SELECT IFNULL(SUM(GrandTotal),0) INTO v_TotalInvoice_
	FROM tmp_Invoices_ 
	WHERE (p_AccountID = 0 or AccountID = p_AccountID)
	AND InvoiceType = 1;
	
	/*Invoice in*/
	SELECT IFNULL(SUM(GrandTotal),0) INTO v_InvoiceIn_
	FROM tmp_Invoices_ 
	WHERE (p_AccountID = 0 or AccountID = p_AccountID)
	AND InvoiceType = 2;
	
	/*Invoice Out*/
	SELECT IFNULL(SUM(GrandTotal),0) INTO v_InvoiceOut_
	FROM tmp_Invoices_ 
	WHERE (p_AccountID = 0 or AccountID = p_AccountID)
	AND InvoiceType = 1;
	
	/*Total Payments*/
	SELECT IFNULL(SUM(PaymentAmount),0) INTO v_TotalPayment_
	FROM tmp_Payment_; 
	
	/*Payments In */
	SELECT IFNULL(SUM(PaymentAmount),0) INTO v_PaymentIn_
	FROM tmp_Payment_ p
	WHERE (
	(p_EndDate = '0' AND fnGetMonthDifference(PaymentDate,NOW()) <= p_StartDate) OR
	(p_EndDate<>'0' AND PaymentDate between p_StartDate AND p_EndDate)
	)AND p.PaymentType = 'Payment In';
	
	/*Payments Out */
	SELECT IFNULL(SUM(PaymentAmount),0) INTO v_PaymentOut_
	FROM tmp_Payment_ p
	WHERE (
	(p_EndDate = '0' AND fnGetMonthDifference(PaymentDate,NOW()) <= p_StartDate) OR
	(p_EndDate<> '0' AND PaymentDate between p_StartDate AND p_EndDate)
	)AND p.PaymentType = 'Payment Out';
	
	/*Total Unpaid Invoices*/	
	SELECT IFNULL(SUM(GrandTotal),0) INTO v_TotalUnpaidInvoices_
	FROM tmp_Invoices_ 
	WHERE (InvoiceStatus Not IN('Paid') AND (PendingAmount>0))
	AND (
	(p_EndDate = '0' AND fnGetMonthDifference(IssueDate,NOW()) <= p_StartDate) OR
	(p_EndDate<>'0' AND IssueDate between p_StartDate AND p_EndDate)
	);
		
	/*Total Overdue Invoices*/	
	SELECT IFNULL(SUM(GrandTotal),0) INTO v_TotalOverdueInvoices_
	FROM tmp_Invoices_ 
	WHERE ((To_days(NOW()) - To_days(IssueDate)) > PaymentDueInDays
							AND(InvoiceStatus NOT IN('awaiting'))
							AND(PendingAmount>0)
						)
	AND (
	(p_EndDate = '0' AND fnGetMonthDifference(IssueDate,NOW()) <= p_StartDate) OR
	(p_EndDate<>'0' AND IssueDate between p_StartDate AND p_EndDate)
	);
		
	/*Total Paid Invoices*/	
	SELECT IFNULL(SUM(GrandTotal),0) INTO v_TotalPaidInvoices_
	FROM tmp_Invoices_ 
	WHERE (InvoiceStatus IN('Paid') AND (PendingAmount=0))
	AND (
	(p_EndDate = '0' AND fnGetMonthDifference(IssueDate,NOW()) <= p_StartDate) OR
	(p_EndDate<>'0' AND IssueDate between p_StartDate AND p_EndDate)
	);
	
	/*Total Dispute*/	
	
	SELECT IFNULL(SUM(DisputeAmount),0) INTO v_TotalDispute_
	FROM tmp_Dispute_;
	
	/*Total Estimate*/
	
	SELECT IFNULL(SUM(EstimateTotal),0) INTO v_TotalEstimate_
	FROM tmp_Estimate_;
	
	SELECT 
			ROUND((v_TotalInvoice_ - v_TotalPayment_),v_Round_) AS TotalOutstanding,
			ROUND(v_PaymentIn_,v_Round_) AS TotalPaymentsIn,
			ROUND(v_PaymentOut_,v_Round_) AS TotalPaymentsOut,
			ROUND(v_InvoiceIn_,v_Round_) AS TotalInvoiceIn,
			ROUND(v_InvoiceOut_,v_Round_) AS TotalInvoiceOut,
			ROUND(v_TotalUnpaidInvoices_,v_Round_) as TotalDueAmount,
			ROUND(v_TotalOverdueInvoices_,v_Round_) as TotalOverdueAmount,
			ROUND(v_TotalPaidInvoices_,v_Round_) as TotalPaidAmount,
			ROUND(v_TotalDispute_,v_Round_) as TotalDispute,
			ROUND(v_TotalEstimate_,v_Round_) as TotalEstimate,
			v_Round_ as `Round`;


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END