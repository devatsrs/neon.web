CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getDashboardinvoiceExpenseTotalOutstanding`(IN `p_CompanyID` INT, IN `p_CurrencyID` INT, IN `p_AccountID` INT)
BEGIN
DECLARE v_Round_ int;
	DECLARE v_TotalInvoice_ decimal(18,6);
	DECLARE v_TotalPayment_ decimal(18,6);

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


	SELECT cs.Value INTO v_Round_ 
	FROM LocalRatemanagement.tblCompanySetting cs 
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
	INNER JOIN LocalRatemanagement.tblAccount ac 
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