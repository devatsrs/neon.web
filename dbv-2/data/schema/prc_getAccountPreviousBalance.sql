CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getAccountPreviousBalance`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_InvoiceID` INT)
BEGIN

	

	Declare v_PreviousBalance_ decimal(18,6);
	Declare v_totalpaymentin_ decimal(18,6);
	Declare v_totalInvoiceOut_ decimal(18,6);
	Declare v_Cancel_ VARCHAR(50) Default 'cancel';
	Declare v_InvoiceOut_ int Default 1;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	 
	 
		SELECT  ifnull(SUM(Amount),0) INTO v_totalpaymentin_ 
		FROM tblPayment
        INNER JOIN Ratemanagement3.tblAccount
            ON tblPayment.AccountID = tblAccount.AccountID
        WHERE tblPayment.AccountID = p_AccountID 
		AND tblPayment.Status = 'Approved' 
		AND tblPayment.Recall = 0
		AND tblPayment.PaymentType = 'Payment In';

		
		-- Commited Invoice in total
		
		SELECT  ifnull(SUM(GrandTotal),0) INTO v_totalInvoiceOut_ 
		FROM tblInvoice inv
		where InvoiceType = v_InvoiceOut_
			and AccountID = p_AccountID 
			and CompanyID = p_CompanyID 
			and InvoiceStatus != v_Cancel_ 
			and InvoiceID !=  p_InvoiceID;
		

		set v_PreviousBalance_ = v_totalInvoiceOut_ - v_totalpaymentin_  ;

	
		select ifnull(v_PreviousBalance_,0) as PreviousBalance;


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END