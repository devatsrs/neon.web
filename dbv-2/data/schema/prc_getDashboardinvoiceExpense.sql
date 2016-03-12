CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getDashboardinvoiceExpense`(IN `p_CompanyID` INT, IN `p_CurrencyID` INT, IN `p_AccountID` INT)
BEGIN
    DECLARE v_Round_ int;
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    
     CREATE TEMPORARY TABLE IF NOT EXISTS tmp_MonthlyTotalDue_(
			`Year` int,
			`Month` int,
			MonthName varchar(50),
			TotalAmount float,
			CurrencyID int
			
		);
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_MonthlyTotalReceived_(
			`Year` int,
			`Month` int,
			MonthName varchar(50),
			TotalAmount float,
			CurrencyID int
			
		);

		SELECT cs.Value INTO v_Round_ from Ratemanagement3.tblCompanySetting cs where cs.`Key` = 'RoundChargesAmount' AND cs.CompanyID = p_CompanyID;
	 INSERT INTO tmp_MonthlyTotalDue_
    SELECT YEAR(created_at) as Year, MONTH(created_at) as Month,MONTHNAME(MAX(created_at)) as  MonthName, ROUND(SUM(IFNULL(GrandTotal,0)),v_Round_) as TotalAmount,CurrencyID
    from tblInvoice
    where 
        CompanyID = p_CompanyID
        and CurrencyID = p_CurrencyID
        and created_at >= DATE_ADD(NOW(),INTERVAL -6 MONTH)
        and InvoiceType = 1 -- Invoice Out
        and InvoiceStatus != 'cancel'
        and (p_AccountID = 0 or AccountID = p_AccountID)

    GROUP BY YEAR(created_at), MONTH(created_at),CurrencyID
    ORDER BY Year, Month;

	 INSERT INTO tmp_MonthlyTotalReceived_
	 
	 SELECT YEAR(p.PaymentDate) as Year, MONTH(p.PaymentDate) as Month,MONTHNAME(MAX(p.PaymentDate)) as  MonthName, ROUND(SUM(IFNULL(p.Amount,0)),v_Round_) as TotalAmount,ac.CurrencyID
	 FROM tblPayment p 
	 INNER JOIN Ratemanagement3.tblAccount ac 
	 	ON ac.AccountID = p.AccountID
	 WHERE 
        		p.CompanyID = p_CompanyID
        and ac.CurrencyID = p_CurrencyID
        and p.PaymentDate >= DATE_ADD(NOW(),INTERVAL -6 MONTH)
        AND p.Status = 'Approved'
        AND p.Recall=0
        AND p.PaymentType = 'Payment In'
        and (p_AccountID = 0 or ac.AccountID = p_AccountID)
    GROUP BY YEAR(p.PaymentDate), MONTH(p.PaymentDate),ac.CurrencyID
    ORDER BY Year, Month;
	 
    /*SELECT YEAR(inv.created_at) as Year, MONTH(inv.created_at) as Month,MONTHNAME(MAX(inv.created_at)) as  MonthName, ROUND(SUM(IFNULL(p.Amount,0)),v_Round_) as TotalAmount,inv.CurrencyID
    from tblInvoice inv
    inner join Ratemanagement3.tblAccount ac on ac.AccountID = inv.AccountID
    left join tblInvoiceTemplate it on ac.InvoiceTemplateID = it.InvoiceTemplateID
    left join tblPayment p on REPLACE(p.InvoiceNo,'-','') = CONCAT(ltrim(rtrim(REPLACE(it.InvoiceNumberPrefix,'-',''))), ltrim(rtrim(inv.InvoiceNumber))) AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall=0
    where 
        inv.CompanyID = p_CompanyID
        and inv.CurrencyID = p_CurrencyID
        and inv.created_at >= DATE_ADD(NOW(),INTERVAL -6 MONTH)
        and inv.InvoiceType = 1 
        and (p_AccountID = 0 or inv.AccountID = p_AccountID)
        and (
            inv.InvoiceStatus = 'paid' -- Paid Invoice
            OR inv.InvoiceStatus = 'partially_paid' 
            )
     
    GROUP BY YEAR(inv.created_at), MONTH(inv.created_at),inv.CurrencyID
    ORDER BY Year, Month;*/
 

    
    SELECT CONCAT(CONCAT(case when td.Month <10 then concat('0',td.Month) else td.Month End, '/'), td.Year) AS MonthName ,
	 td.Year,ROUND(IFNULL(td.TotalAmount,0),v_Round_) TotalInvoice ,  ROUND(IFNULL(tr.TotalAmount,0),v_Round_) PaymentReceived, ROUND(IFNULL((IFNULL(td.TotalAmount,0) - IFNULL(tr.TotalAmount,0)),0),v_Round_) TotalOutstanding , td.CurrencyID CurrencyID from 
        tmp_MonthlyTotalDue_ td
        left join tmp_MonthlyTotalReceived_ tr on td.Month = tr.Month and td.Year = tr.Year and tr.CurrencyID = td.CurrencyID
   ORDER BY td.Year,td.Month;
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END