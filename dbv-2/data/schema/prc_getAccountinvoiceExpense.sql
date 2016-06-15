CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getAccountinvoiceExpense`(IN `p_CompanyID` INT, IN `p_AccountID` INT)
BEGIN
	DECLARE v_Round_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	DROP TEMPORARY TABLE IF EXISTS tmp_InvoiceSent_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_InvoiceSent_(
		`Year` int,
		`Month` int,
		TotalSentAmount float,
		TotalReceivedAmount float
	);

	INSERT INTO tmp_InvoiceSent_
	SELECT 
		YEAR(IssueDate) as Year, 
		MONTH(IssueDate) as Month,
		ROUND(SUM(IF(InvoiceType=1,GrandTotal,0)),v_Round_) AS  TotalSentAmount,
		ROUND(SUM(IF(InvoiceType=2,GrandTotal,0)),v_Round_) AS  TotalReceivedAmount
	FROM tblInvoice
	WHERE 
		CompanyID = p_CompanyID
		AND 
		(
			(InvoiceType =1 AND InvoiceStatus NOT IN ( 'cancel' , 'draft' ))
			OR
			(InvoiceType =2)
		)
		AND AccountID = p_AccountID

	GROUP BY YEAR(IssueDate), MONTH(IssueDate),CurrencyID
	ORDER BY Year, Month;

	SELECT * FROM tmp_InvoiceSent_ ORDER BY YEAR;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END