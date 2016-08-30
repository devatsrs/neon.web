CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getInvoicePayments`(IN `p_InvoiceID` INT, IN `p_CompanyID` INT)
BEGIN

    DECLARE v_Round_ int;
     SET SESSION sql_mode='ONLY_FULL_GROUP_BY,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	        

	 SELECT cs.Value INTO v_Round_ from NeonRMDev.tblCompanySetting cs where cs.`Key` = 'RoundChargesAmount' AND cs.CompanyID = p_CompanyID;

	select      ROUND(sum(inv.GrandTotal),v_Round_) as total_grand,ROUND(sum(format((select IFNULL(sum(p.Amount),0) from tblPayment p where p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0),v_Round_)),v_Round_) as `paid_amount`,sum(ROUND(inv.GrandTotal -  (select IFNULL(sum(p.Amount),0) from tblPayment p where p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0 ),v_Round_)) as due_amount
			
from tblInvoice inv
 where InvoiceID=p_InvoiceID;

END