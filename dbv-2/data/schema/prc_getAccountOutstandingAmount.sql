CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getAccountOutstandingAmount`(IN `p_company_id` INT, IN `p_AccountID` int 
)
BEGIN

	  Declare v_TotalDue_ decimal(18,6);
    Declare v_TotalPaid_ decimal(18,6);
    
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    
	  
      

   
    

    -- Sum of Invoice Created - Sum of Payment = Outstanidng
    Select   ifnull(sum(GrandTotal),0) INTO v_TotalDue_
    from tblInvoice
    where AccountID = p_AccountID
    and CompanyID = p_company_id
    AND InvoiceStatus != 'cancel';   -- cancel invoice

    -- print concat('Total Due ', v_TotalDue_)

    Select  ifnull(sum(Amount),0) INTO v_TotalPaid_ 
    from tblPayment
    where AccountID = p_AccountID
    and CompanyID = p_company_id
    and Status = 'Approved'
    and Recall = 0;

    -- print concat('Total Paid ',v_TotalPaid_)

    Select ifnull((v_TotalDue_ - v_TotalPaid_ ),0) as Outstanding;
                
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END