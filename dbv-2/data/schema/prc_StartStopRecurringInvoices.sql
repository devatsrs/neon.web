CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_StartStopRecurringInvoices`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_RecurringInvoiceStatus` INT,
	IN `p_InvoiceIDs` VARCHAR(200)



,
	IN `p_StartStop` INT

,
	IN `p_ModifiedBy` VARCHAR(50),
	IN `p_LogStatus` INT


)
BEGIN
	DECLARE v_Status_to VARCHAR(100);
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
	
	SET v_Status_to = CASE WHEN p_StartStop=1 THEN CONCAT('Recurring Invoice Start by ',p_ModifiedBy) ELSE CONCAT('Recurring Invoice Stop by ',p_ModifiedBy) END;
	
	INSERT INTO tblRecurringInvoiceLog
	SELECT null,inv.RecurringInvoiceID,v_Status_to,p_LogStatus,Now(),Now()
	FROM tblRecurringInvoice inv
	WHERE inv.CompanyID = p_CompanyID
	AND inv.`Status`!=p_StartStop
	AND (p_InvoiceIDs='' 
			AND (p_AccountID = 0 OR inv.AccountID=p_AccountID) 
			AND (p_RecurringInvoiceStatus=2 OR inv.`Status`=p_RecurringInvoiceStatus)
		 )
	OR (p_InvoiceIDs<>'' AND FIND_IN_SET(inv.RecurringInvoiceID ,p_InvoiceIDs));
	
	UPDATE tblRecurringInvoice inv SET inv.`Status` = p_StartStop
	WHERE inv.CompanyID = p_CompanyID
	AND inv.`Status`!=p_StartStop
	AND (p_InvoiceIDs='' 
			AND (p_AccountID = 0 OR inv.AccountID=p_AccountID) 
			AND (p_RecurringInvoiceStatus=2 OR inv.`Status`=p_RecurringInvoiceStatus)
		 )
	OR (p_InvoiceIDs<>'' AND FIND_IN_SET(inv.RecurringInvoiceID ,p_InvoiceIDs));
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END