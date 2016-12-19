USE `NeonBillingDev`;

Update tblBillingClass SET SendInvoiceSetting='after_admin_review' where SendInvoiceSetting='never'


DROP PROCEDURE IF EXISTS `fngetDefaultCodes`;
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getDashboardinvoiceExpenseTotalOutstanding`(
	IN `p_CompanyID` INT,
	IN `p_CurrencyID` INT,
	IN `p_AccountID` INT,
	IN `p_StartDate` VARCHAR(50),
	IN `p_EndDate` VARCHAR(50)
)
BEGIN

	DECLARE v_Round_ INT;

	DECLARE v_TotalInvoiceIn_ DECIMAL(18,6);
	DECLARE v_TotalInvoiceOut_ DECIMAL(18,6);
	DECLARE v_TotalPaymentIn_ DECIMAL(18,6);
	DECLARE v_TotalPaymentOut_ DECIMAL(18,6);
	DECLARE v_TotalOutstanding_ DECIMAL(18,6);
	DECLARE v_Outstanding_ DECIMAL(18,6);

	DECLARE v_InvoiceSentTotal_ DECIMAL(18,6);
	DECLARE v_InvoiceRecvTotal_ DECIMAL(18,6);
	DECLARE v_PaymentSentTotal_ DECIMAL(18,6);
	DECLARE v_PaymentRecvTotal_ DECIMAL(18,6);

	DECLARE v_TotalUnpaidInvoices_ DECIMAL(18,6);
	DECLARE v_TotalOverdueInvoices_ DECIMAL(18,6);
	DECLARE v_TotalPaidInvoices_ DECIMAL(18,6);
	DECLARE v_TotalDispute_ DECIMAL(18,6);
	DECLARE v_TotalEstimate_ DECIMAL(18,6);

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	DROP TEMPORARY TABLE IF EXISTS tmp_Invoices_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Invoices_(
		InvoiceType TINYINT(1),
		IssueDate DATETIME,
		GrandTotal DECIMAL(18,6),
		InvoiceStatus VARCHAR(50),
		PaymentDueInDays INT,
		PendingAmount DECIMAL(18,6),
		AccountID INT
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_Payment_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Payment_(
		PaymentAmount DECIMAL(18,6),
		PaymentDate DATETIME,
		PaymentType VARCHAR(50)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_Dispute_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Dispute_(
		DisputeAmount DECIMAL(18,6)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_Estimate_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Estimate_(
		EstimateTotal DECIMAL(18,6)
	);

	/* all disputes with pending status*/
	INSERT INTO tmp_Dispute_
	SELECT
		ds.DisputeAmount
	FROM tblDispute ds
	INNER JOIN NeonRMDev.tblAccount ac
		ON ac.AccountID = ds.AccountID
	WHERE ds.CompanyID = p_CompanyID
	AND ac.CurrencyId = p_CurrencyID
	AND (p_AccountID = 0 or ac.AccountID = p_AccountID)
	AND ds.Status = 0
	AND ((p_EndDate = '0' AND fnGetMonthDifference(ds.created_at,NOW()) <= p_StartDate) OR
			(p_EndDate<>'0' AND ds.created_at between p_StartDate AND p_EndDate));

	/* all estimates with are pending to conevert invoice*/
	INSERT INTO tmp_Estimate_
	SELECT
		es.GrandTotal
	FROM tblEstimate es
	INNER JOIN NeonRMDev.tblAccount ac
		ON ac.AccountID = es.AccountID
	WHERE es.CompanyID = p_CompanyID
	AND (p_AccountID = 0 or ac.AccountID = p_AccountID)
	AND ac.CurrencyId = p_CurrencyID
	AND es.EstimateStatus NOT IN ('draft','accepted','rejected')
	AND ((p_EndDate = '0' AND fnGetMonthDifference(es.IssueDate,NOW()) <= p_StartDate) OR
			(p_EndDate<>'0' AND es.IssueDate between p_StartDate AND p_EndDate));

	/* all invoice sent and recived*/
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
	INNER JOIN NeonRMDev.tblAccount ac
		ON ac.AccountID = inv.AccountID
		AND inv.CompanyID = p_CompanyID
		AND inv.CurrencyID = p_CurrencyID
		AND (p_AccountID = 0 or ac.AccountID = p_AccountID)
		AND ( (InvoiceType = 2) OR ( InvoiceType = 1 AND InvoiceStatus NOT IN ( 'cancel' , 'draft') )  )
		AND ((p_EndDate = '0' AND fnGetMonthDifference(IssueDate,NOW()) <= p_StartDate) OR
			(p_EndDate<>'0' AND IssueDate BETWEEN p_StartDate AND p_EndDate));

	/* all payments recevied and sent*/
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
		AND (p_AccountID = 0 or ac.AccountID = p_AccountID)
		AND (
			(p_EndDate = '0' AND fnGetMonthDifference(PaymentDate,NOW()) <= p_StartDate) OR
			(p_EndDate<>'0' AND PaymentDate between p_StartDate AND p_EndDate)
			);


	/* total outstanding
	SELECT
		SUM(IF(InvoiceType=1,GrandTotal,0)),
		SUM(IF(InvoiceType=2,GrandTotal,0)) INTO v_TotalInvoiceOut_,v_TotalInvoiceIn_
	FROM tmp_Invoices_;

	SELECT
		SUM(IF(PaymentType='Payment In',PaymentAmount,0)),
		SUM(IF(PaymentType='Payment Out',PaymentAmount,0)) INTO v_TotalPaymentIn_,v_TotalPaymentOut_
	FROM tmp_Payment_;

	SELECT (IFNULL(v_TotalInvoiceOut_,0) - IFNULL(v_TotalPaymentIn_,0)) - (IFNULL(v_TotalInvoiceIn_,0) - IFNULL(v_TotalPaymentOut_,0)) INTO v_TotalOutstanding_;*/

	/* outstanding */
	SELECT
		SUM(IF(InvoiceType=1,GrandTotal,0)),
		SUM(IF(InvoiceType=2,GrandTotal,0)) INTO v_TotalInvoiceOut_,v_TotalInvoiceIn_
	FROM tmp_Invoices_;

	SELECT
		SUM(IF(PaymentType='Payment In',PaymentAmount,0)),
		SUM(IF(PaymentType='Payment Out',PaymentAmount,0)) INTO v_TotalPaymentIn_,v_TotalPaymentOut_
	FROM tmp_Payment_;

	SELECT (IFNULL(v_TotalInvoiceOut_,0) - IFNULL(v_TotalPaymentIn_,0)) - (IFNULL(v_TotalInvoiceIn_,0) - IFNULL(v_TotalPaymentOut_,0)) INTO v_Outstanding_;

	/* Invoice Sent Total */
	SELECT IFNULL(SUM(GrandTotal),0) INTO v_InvoiceSentTotal_
	FROM tmp_Invoices_
	WHERE InvoiceType = 1;

	/* Invoice Received Total*/
	SELECT IFNULL(SUM(GrandTotal),0) INTO v_InvoiceRecvTotal_
	FROM tmp_Invoices_
	WHERE  InvoiceType = 2;

	/* Payment Received */
	SELECT IFNULL(SUM(PaymentAmount),0) INTO v_PaymentRecvTotal_
	FROM tmp_Payment_ p
	WHERE p.PaymentType = 'Payment In';

	/* Payment Sent */
	SELECT IFNULL(SUM(PaymentAmount),0) INTO v_PaymentSentTotal_
	FROM tmp_Payment_ p
	WHERE p.PaymentType = 'Payment Out';

	/*Total Unpaid Invoices*/
	SELECT IFNULL(SUM(GrandTotal),0) INTO v_TotalUnpaidInvoices_
	FROM tmp_Invoices_
	WHERE InvoiceType = 1
	AND InvoiceStatus <> 'paid'
	AND PendingAmount > 0;

	/*Total Overdue Invoices*/
	SELECT IFNULL(SUM(GrandTotal),0) INTO v_TotalOverdueInvoices_
	FROM tmp_Invoices_
	WHERE ((To_days(NOW()) - To_days(IssueDate)) > PaymentDueInDays
							AND(InvoiceStatus NOT IN('awaiting'))
							AND(PendingAmount>0)
						);
	/*Total Paid Invoices*/
	SELECT IFNULL(SUM(GrandTotal),0) INTO v_TotalPaidInvoices_
	FROM tmp_Invoices_
	WHERE (InvoiceStatus IN('Paid') AND (PendingAmount=0));

	/*Total Dispute*/
	SELECT IFNULL(SUM(DisputeAmount),0) INTO v_TotalDispute_
	FROM tmp_Dispute_;

	/*Total Estimate*/
	SELECT IFNULL(SUM(EstimateTotal),0) INTO v_TotalEstimate_
	FROM tmp_Estimate_;

	SELECT
			/*ROUND(v_TotalOutstanding_,v_Round_) AS TotalOutstanding,*/
			ROUND(v_Outstanding_,v_Round_) AS Outstanding,
			ROUND(v_PaymentRecvTotal_,v_Round_) AS TotalPaymentsIn,
			ROUND(v_PaymentSentTotal_,v_Round_) AS TotalPaymentsOut,
			ROUND(v_InvoiceRecvTotal_,v_Round_) AS TotalInvoiceIn,
			ROUND(v_InvoiceSentTotal_,v_Round_) AS TotalInvoiceOut,
			ROUND(v_TotalUnpaidInvoices_,v_Round_) as TotalDueAmount,
			ROUND(v_TotalOverdueInvoices_,v_Round_) as TotalOverdueAmount,
			ROUND(v_TotalPaidInvoices_,v_Round_) as TotalPaidAmount,
			ROUND(v_TotalDispute_,v_Round_) as TotalDispute,
			ROUND(v_TotalEstimate_,v_Round_) as TotalEstimate,
			v_Round_ as `Round`;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END

DROP PROCEDURE IF EXISTS `fngetDefaultCodes`;
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getDashboardTotalOutStanding`(
	IN `p_CompanyID` INT,
	IN `p_CurrencyID` INT,
	IN `p_AccountID` INT
)
BEGIN
	DECLARE v_Round_ int;
	DECLARE v_TotalInvoice_ decimal(18,6);
	DECLARE v_TotalPayment_ decimal(18,6);

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


	SELECT cs.Value INTO v_Round_
	FROM Tech1RateManagement.tblCompanySetting cs
	WHERE cs.`Key` = 'RoundChargesAmount'
		AND cs.CompanyID = p_CompanyID;

	SELECT IFNULL(SUM(GrandTotal),0) INTO v_TotalInvoice_
	FROM tblInvoice
	WHERE
		CompanyID = p_CompanyID
		AND CurrencyID = p_CurrencyID
		AND InvoiceType = 1 -- Invoice Out
		AND InvoiceStatus NOT IN ( 'cancel' , 'draft' )
		AND (p_AccountID = 0 or AccountID = p_AccountID);

	SELECT IFNULL(SUM(p.Amount),0) INTO v_TotalPayment_
		FROM tblPayment p
	INNER JOIN Tech1RateManagement.tblAccount ac
		ON ac.AccountID = p.AccountID
	WHERE
		p.CompanyID = p_CompanyID
		AND ac.CurrencyId = p_CurrencyID
		AND p.Status = 'Approved'
		AND p.Recall=0
		AND p.PaymentType = 'Payment In'
		AND (p_AccountID = 0 or ac.AccountID = p_AccountID);

	SELECT ROUND((v_TotalInvoice_ - v_TotalPayment_),v_Round_) AS TotalOutstanding ;


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END