CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getDashboardinvoiceExpense`(IN `p_CompanyID` INT, IN `p_CurrencyID` INT, IN `p_AccountID` INT)
BEGIN
	DECLARE v_Round_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_MonthlyTotalDue_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_MonthlyTotalDue_(
		`Year` int,
		`Month` int,
		MonthName varchar(50),
		TotalAmount float,
		CurrencyID int
	);
	DROP TEMPORARY TABLE IF EXISTS tmp_MonthlyTotalReceived_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_MonthlyTotalReceived_(
		`Year` int,
		`Month` int,
		MonthName varchar(50),
		TotalAmount float,
		CurrencyID int
	);
	SELECT cs.Value INTO v_Round_ 
	FROM LocalRatemanagement.tblCompanySetting cs 
	WHERE cs.`Key` = 'RoundChargesAmount' 
		AND cs.CompanyID = p_CompanyID;

	INSERT INTO tmp_MonthlyTotalDue_
	SELECT YEAR(IssueDate) as Year
			,MONTH(IssueDate) as Month
			,MONTHNAME(MAX(IssueDate)) as  MonthName
			,ROUND(SUM(IFNULL(GrandTotal,0)),v_Round_) as TotalAmount
			,CurrencyID
	FROM tblInvoice
	WHERE 
		CompanyID = p_CompanyID
		AND CurrencyID = p_CurrencyID
		AND fnGetMonthDifference(IssueDate,NOW()) <= 12
		AND InvoiceType = 1 -- Invoice Out
		AND InvoiceStatus NOT IN ( 'cancel' , 'draft' )
		AND (p_AccountID = 0 or AccountID = p_AccountID)
	GROUP BY 
			YEAR(IssueDate)
			,MONTH(IssueDate)
			,CurrencyID
	ORDER BY 
			Year
			,Month;


	DROP TEMPORARY TABLE IF EXISTS tmp_tblPayment_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblPayment_(
		PaymentDate Date,
		Amount float,
		CurrencyID int
	);
	/* payment recevied invoice*/
	INSERT INTO tmp_tblPayment_ (PaymentDate,Amount,CurrencyID)
	SELECT 
		CASE WHEN inv.InvoiceID IS NOT NULL
		THEN
			inv.IssueDate
		ELSE
			p.PaymentDate
		END as PaymentDate,
		p.Amount,
		ac.CurrencyId
	FROM tblPayment p 
	INNER JOIN LocalRatemanagement.tblAccount ac 
		ON ac.AccountID = p.AccountID
	LEFT JOIN tblInvoiceTemplate it on ac.InvoiceTemplateID = it.InvoiceTemplateID
	LEFT JOIN tblInvoice inv on REPLACE(p.InvoiceNo,'-','') = CONCAT(ltrim(rtrim(REPLACE(it.InvoiceNumberPrefix,'-',''))), ltrim(rtrim(inv.InvoiceNumber))) 
		AND p.Status = 'Approved' 
		AND p.AccountID = inv.AccountID 
		AND p.Recall=0
		AND InvoiceType = 1 
	WHERE 
			p.CompanyID = p_CompanyID
		AND ac.CurrencyId = p_CurrencyID
		AND ((	fnGetMonthDifference(p.PaymentDate,NOW()) <= 12) 
				OR (	 fnGetMonthDifference(inv.IssueDate,NOW()) <= 12))
		AND p.Status = 'Approved'
		AND p.Recall=0
		AND p.PaymentType = 'Payment In'
		AND (p_AccountID = 0 or ac.AccountID = p_AccountID);


	INSERT INTO tmp_MonthlyTotalReceived_
	SELECT YEAR(p.PaymentDate) as Year
			,MONTH(p.PaymentDate) as Month
			,MONTHNAME(MAX(p.PaymentDate)) as  MonthName
			, ROUND(SUM(IFNULL(p.Amount,0)),v_Round_) as TotalAmount
			,CurrencyID
	FROM tmp_tblPayment_ p 
	GROUP BY 
		YEAR(p.PaymentDate)
		,MONTH(p.PaymentDate),CurrencyID
	ORDER BY 
		Year
		,Month;

	SELECT 
		CONCAT(CONCAT(case when td.Month <10 then concat('0',td.Month) else td.Month End, '/'), td.Year) AS MonthName ,
			td.Year,
			ROUND(IFNULL(td.TotalAmount,0),v_Round_) TotalInvoice ,  
			ROUND(IFNULL(tr.TotalAmount,0),v_Round_) PaymentReceived, 
			ROUND(IFNULL((IFNULL(td.TotalAmount,0) - IFNULL(tr.TotalAmount,0)),0),v_Round_) TotalOutstanding , 
			td.CurrencyID CurrencyID 
	FROM  
		tmp_MonthlyTotalDue_ td
	LEFT JOIN tmp_MonthlyTotalReceived_ tr 
		ON td.Month = tr.Month 
		AND td.Year = tr.Year 
		AND tr.CurrencyID = td.CurrencyID
	ORDER BY 
		td.Year
		,td.Month;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END