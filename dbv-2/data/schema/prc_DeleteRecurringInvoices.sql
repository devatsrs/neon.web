CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_DeleteRecurringInvoices`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_RecurringInvoiceStatus` INT,
	IN `p_InvoiceIDs` VARCHAR(200)











)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	DELETE invd FROM tblRecurringInvoiceDetail invd
	INNER JOIN tblRecurringInvoice inv ON invd.RecurringInvoiceID = inv.RecurringInvoiceID
	WHERE inv.CompanyID = p_CompanyID
	AND ((p_InvoiceIDs='' 
			AND (p_AccountID = 0 OR inv.AccountID=p_AccountID) 
			AND (p_RecurringInvoiceStatus=2 OR inv.`Status`=p_RecurringInvoiceStatus))
			OR (p_InvoiceIDs<>'' AND FIND_IN_SET(inv.RecurringInvoiceID ,p_InvoiceIDs))
		 );
	
	DELETE invlg FROM tblRecurringInvoiceLog invlg
	INNER JOIN tblRecurringInvoice inv ON invlg.RecurringInvoiceID = inv.RecurringInvoiceID
	WHERE inv.CompanyID = p_CompanyID
	AND ((p_InvoiceIDs='' 
			AND (p_AccountID = 0 OR inv.AccountID=p_AccountID) 
			AND (p_RecurringInvoiceStatus=2 OR inv.`Status`=p_RecurringInvoiceStatus))
			OR (p_InvoiceIDs<>'' AND FIND_IN_SET(inv.RecurringInvoiceID ,p_InvoiceIDs))
		 );
	
	DELETE invtr FROM tblRecurringInvoiceTaxRate invtr
	INNER JOIN tblRecurringInvoice inv ON invtr.RecurringInvoiceID = inv.RecurringInvoiceID
	WHERE inv.CompanyID = p_CompanyID
	AND ((p_InvoiceIDs='' 
			AND (p_AccountID = 0 OR inv.AccountID=p_AccountID) 
			AND (p_RecurringInvoiceStatus=2 OR inv.`Status`=p_RecurringInvoiceStatus))
			OR (p_InvoiceIDs<>'' AND FIND_IN_SET(inv.RecurringInvoiceID ,p_InvoiceIDs))
		 );
		 
	UPDATE tblInvoice inv
	INNER JOIN tblRecurringInvoice rinv ON inv.RecurringInvoiceID = rinv.RecurringInvoiceID
	AND rinv.CompanyID = p_CompanyID
	AND ((p_InvoiceIDs='' 
			AND (p_AccountID = 0 OR rinv.AccountID=p_AccountID) 
			AND (p_RecurringInvoiceStatus=2 OR rinv.`Status`=p_RecurringInvoiceStatus))
			OR (p_InvoiceIDs<>'' AND FIND_IN_SET(rinv.RecurringInvoiceID ,p_InvoiceIDs))
		 )
	SET inv.RecurringInvoiceID=0;
	
	DELETE inv FROM tblRecurringInvoice inv
	WHERE inv.CompanyID = p_CompanyID
	AND ((p_InvoiceIDs='' 
			AND (p_AccountID = 0 OR inv.AccountID=p_AccountID) 
			AND (p_RecurringInvoiceStatus=2 OR inv.`Status`=p_RecurringInvoiceStatus))
			OR (p_InvoiceIDs<>'' AND FIND_IN_SET(inv.RecurringInvoiceID ,p_InvoiceIDs))
		 );
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END