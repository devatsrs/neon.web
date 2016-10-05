CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getAccountPreviousBalance`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_InvoiceID` INT)
BEGIN

	DECLARE v_PreviousBalance_ DECIMAL(18,6);
	DECLARE v_totalpaymentin_ DECIMAL(18,6);
	DECLARE v_totalpaymentout_ DECIMAL(18,6);
	DECLARE v_totalInvoiceOut_ DECIMAL(18,6);
	DECLARE v_totalInvoiceIn_ DECIMAL(18,6);
	DECLARE v_InvoiceDate_ DATE DEFAULT DATE(NOW());
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	
	IF p_InvoiceID > 0
	THEN 
		SET  v_InvoiceDate_ = (SELECT IssueDate FROM tblInvoice WHERE InvoiceID = p_InvoiceID);
	END IF;
	
	SELECT
		COALESCE(SUM(IF(PaymentType = 'Payment In',Amount,0)),0) as PaymentIn,
		COALESCE(SUM(IF(PaymentType = 'Payment Out',Amount,0)),0) as PaymentOut
	INTO 
		v_totalpaymentin_,
		v_totalpaymentout_
	FROM tblPayment
	WHERE tblPayment.AccountID = p_AccountID
	AND tblPayment.Status = 'Approved'
	AND tblPayment.Recall = 0
	AND tblPayment.PaymentDate < v_InvoiceDate_;

	SELECT
		COALESCE(SUM(IF(InvoiceType = 1,GrandTotal,0)),0) as InvoiceInTotal,
		COALESCE(SUM(IF(InvoiceType = 2,GrandTotal,0)),0) as InvoiceOutTotal
	INTO
		v_totalInvoiceOut_,
		v_totalInvoiceIn_
	FROM tblInvoice inv
	WHERE AccountID = p_AccountID 
	AND CompanyID = p_CompanyID
	AND ( (InvoiceType = 2) OR ( InvoiceType = 1 AND InvoiceStatus NOT IN ( 'cancel' , 'draft' ) )  )
	AND IssueDate < v_InvoiceDate_;

	SET v_PreviousBalance_ = (v_totalInvoiceOut_ - v_totalpaymentin_) - (v_totalInvoiceIn_ - v_totalpaymentout_);

	SELECT IFNULL(v_PreviousBalance_,0) as PreviousBalance;


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END