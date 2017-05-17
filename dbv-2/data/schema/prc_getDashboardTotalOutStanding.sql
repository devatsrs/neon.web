CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getDashboardTotalOutStanding`(
	IN `p_CompanyID` INT,
	IN `p_CurrencyID` INT,
	IN `p_AccountID` INT
)
BEGIN
	DECLARE v_Round_ int;
	DECLARE v_TotalInvoiceOut_ decimal(18,6);
	DECLARE v_TotalPaymentIn_ decimal(18,6);
	DECLARE v_TotalInvoiceIn_ decimal(18,6);
	DECLARE v_TotalPaymentOut_ decimal(18,6);

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	SELECT 
		SUM(IF(InvoiceType=1,GrandTotal,0)),
		SUM(IF(InvoiceType=2,GrandTotal,0)) 
	INTO 
		v_TotalInvoiceOut_,
		v_TotalInvoiceIn_
	FROM tblInvoice 
	WHERE 
		CompanyID = p_CompanyID
		AND CurrencyID = p_CurrencyID		
		AND ( (InvoiceType = 2) OR ( InvoiceType = 1 AND InvoiceStatus NOT IN ( 'cancel' , 'draft') )  )
		AND (p_AccountID = 0 or AccountID = p_AccountID);
		
	SELECT 
		SUM(IF(PaymentType='Payment In',p.Amount,0)),
		SUM(IF(PaymentType='Payment Out',p.Amount,0)) 
	INTO 
		v_TotalPaymentIn_,
		v_TotalPaymentOut_
	FROM tblPayment p 
	INNER JOIN NeonRMDev.tblAccount ac 
		ON ac.AccountID = p.AccountID
	WHERE 
		p.CompanyID = p_CompanyID
		AND ac.CurrencyId = p_CurrencyID	
		AND p.Status = 'Approved'
		AND p.Recall=0
		AND (p_AccountID = 0 or ac.AccountID = p_AccountID);
	
	--	SELECT ROUND((v_TotalInvoice_ - v_TotalPayment_),v_Round_) AS TotalOutstanding ;
	SELECT 
		ROUND((IFNULL(v_TotalInvoiceOut_,0) - IFNULL(v_TotalPaymentIn_,0)) - (IFNULL(v_TotalInvoiceIn_,0) - IFNULL(v_TotalPaymentOut_,0)),v_Round_) AS TotalOutstanding,
		ROUND((IFNULL(v_TotalInvoiceOut_,0) - IFNULL(v_TotalPaymentIn_,0)),v_Round_) AS TotalPayable,
		ROUND((IFNULL(v_TotalInvoiceIn_,0) - IFNULL(v_TotalPaymentOut_,0)),v_Round_) AS TotalReceivable;
	


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END