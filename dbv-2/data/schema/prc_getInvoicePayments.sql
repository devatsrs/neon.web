CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getInvoicePayments`(IN `p_InvoiceID` INT, IN `p_CompanyID` INT)
BEGIN

	DECLARE v_Round_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	SELECT
		ROUND(SUM(inv.GrandTotal),v_Round_) as total_grand,
		ROUND((SELECT IFNULL(SUM(p.Amount),0) FROM tblPayment p WHERE p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0),v_Round_) as `paid_amount`,
		ROUND(inv.GrandTotal -  (SELECT IFNULL(SUM(p.Amount),0) FROM tblPayment p WHERE p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0 ),v_Round_) as due_amount
	FROM tblInvoice inv
	WHERE InvoiceID=p_InvoiceID;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END