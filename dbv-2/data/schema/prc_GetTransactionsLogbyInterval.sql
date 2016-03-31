CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetTransactionsLogbyInterval`(IN `p_CompanyID` int, IN `p_Interval` varchar(50) )
BEGIN
   
   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
 
        SELECT
			ac.AccountName,
			inv.InvoiceNumber,
            tl.Transaction,
            tl.Notes,
            tl.created_at,
            tl.Amount,
            Case WHEN tl.Status = 1 then 'Success' ELSE 'Failed'  
			END as Status
		FROM   tblTransactionLog tl
		INNER JOIN tblInvoice inv
            ON tl.CompanyID = inv.CompanyID 
            AND tl.InvoiceID = inv.InvoiceID
        INNER JOIN Ratemanagement3.tblAccount ac
            ON ac.AccountID = inv.AccountID
        
        WHERE ac.CompanyID = p_CompanyID 
        AND (
			( p_Interval = 'Daily' AND tl.created_at >= DATE_FORMAT(DATE_ADD(NOW(),INTERVAL -1 DAY),'%Y-%m-%d') )
			OR
			( p_Interval = 'Weekly' AND tl.created_at >= DATE_FORMAT(DATE_ADD(NOW(),INTERVAL -1 WEEK),'%Y-%m-%d') )
			OR
			( p_Interval = 'Monthly' AND tl.created_at >= DATE_FORMAT(DATE_ADD(NOW(),INTERVAL -1 MONTH),'%Y-%m-%d') )
		);
		
		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END