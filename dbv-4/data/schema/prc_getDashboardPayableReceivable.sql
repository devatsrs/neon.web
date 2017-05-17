CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getDashboardPayableReceivable`(
	IN `p_CompanyID` INT,
	IN `p_CurrencyID` INT,
	IN `p_AccountID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_Unbilled` INT,
	IN `p_ListType` VARCHAR(50)
)
BEGIN
	DECLARE v_Round_ INT;
	DECLARE prev_TotalInvoiceOut  DECIMAL(18,6);
	DECLARE prev_TotalInvoiceIn DECIMAL(18,6);
	DECLARE prev_TotalPaymentOut DECIMAL(18,6);
	DECLARE prev_TotalPaymentIn DECIMAL(18,6);
	DECLARE prev_CustomerUnbill DECIMAL(18,6);
	DECLARE prev_VendrorUnbill DECIMAL(18,6);

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	DROP TEMPORARY TABLE IF EXISTS tmp_CustomerUnbilled_;
	CREATE TEMPORARY TABLE tmp_CustomerUnbilled_  (
		DateID INT,
		CustomerUnbill DOUBLE
	);
	DROP TEMPORARY TABLE IF EXISTS tmp_VendorUbilled_;
	CREATE TEMPORARY TABLE tmp_VendorUbilled_  (
		DateID INT,
		VendrorUnbill DOUBLE
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_FinalResult_;
	CREATE TEMPORARY TABLE tmp_FinalResult_  (
		TotalInvoiceOut DOUBLE,
		TotalInvoiceIn DOUBLE,
		TotalPaymentOut DOUBLE,
		TotalPaymentIn DOUBLE,
		CustomerUnbill DOUBLE,
		VendrorUnbill DOUBLE,
		date DATE,
		TotalOutstanding DOUBLE,
		TotalPayable DOUBLE,
		TotalReceivable DOUBLE
	);
	
	IF p_Unbilled = 1
	THEN
		DROP TEMPORARY TABLE IF EXISTS tmp_Account_;
		CREATE TEMPORARY TABLE tmp_Account_  (
			RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			AccountID INT,
			LastInvoiceDate DATE
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Account2_;
		CREATE TEMPORARY TABLE tmp_Account2_  (
			RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			AccountID INT,
			LastInvoiceDate DATE
		);

		INSERT INTO tmp_Account_ (AccountID)
		SELECT DISTINCT tblSummaryHeader.AccountID  FROM tblSummaryHeader INNER JOIN NeonRMDev.tblAccount ON tblAccount.AccountID = tblSummaryHeader.AccountID WHERE tblSummaryHeader.CompanyID = 1;

		UPDATE tmp_Account_ SET LastInvoiceDate = fngetLastInvoiceDate(AccountID);

		INSERT INTO tmp_Account2_ (AccountID)
		SELECT DISTINCT tblSummaryVendorHeader.AccountID  FROM tblSummaryVendorHeader INNER JOIN NeonRMDev.tblAccount ON tblAccount.AccountID = tblSummaryVendorHeader.AccountID WHERE tblSummaryVendorHeader.CompanyID = p_CompanyID;

		UPDATE tmp_Account2_ SET LastInvoiceDate = fngetLastVendorInvoiceDate(AccountID);

		SELECT 
			SUM(h.TotalCharges)
		INTO
			prev_CustomerUnbill
		FROM tmp_Account_ a
		INNER JOIN tblDimDate dd
			ON dd.date >= a.LastInvoiceDate
		INNER JOIN tblHeader h
			ON h.AccountID = a.AccountID
			AND h.DateID = dd.DateID
		WHERE dd.date < p_StartDate;
		
		SELECT 
			SUM(h.TotalCharges)
		INTO 
			prev_VendrorUnbill
		FROM tmp_Account2_ a
		INNER JOIN tblDimDate dd
			ON dd.date >= a.LastInvoiceDate
		INNER JOIN tblHeaderV h
			ON h.VAccountID = a.AccountID
			AND h.DateID = dd.DateID
		WHERE dd.date < p_StartDate;

		INSERT INTO tmp_CustomerUnbilled_(DateID,CustomerUnbill)
		SELECT 
			dd.DateID,
			SUM(h.TotalCharges)
		FROM tmp_Account_ a
		INNER JOIN tblDimDate dd
			ON dd.date >= a.LastInvoiceDate
		INNER JOIN tblHeader h
			ON h.AccountID = a.AccountID
			AND h.DateID = dd.DateID
		WHERE dd.date BETWEEN p_StartDate AND p_EndDate
		GROUP BY dd.date;

		INSERT INTO tmp_VendorUbilled_ (DateID,VendrorUnbill)
		SELECT 
			dd.DateID,
			SUM(h.TotalCharges)
		FROM tmp_Account2_ a
		INNER JOIN tblDimDate dd
			ON dd.date >= a.LastInvoiceDate
		INNER JOIN tblHeaderV h
			ON h.VAccountID = a.AccountID
			AND h.DateID = dd.DateID
		WHERE dd.date BETWEEN p_StartDate AND p_EndDate
		GROUP BY dd.date;
	
	END IF;

	SELECT 
		SUM(IF(InvoiceType=1,GrandTotal,0)),
		SUM(IF(InvoiceType=2,GrandTotal,0)) 
	INTO 
		prev_TotalInvoiceOut,
		prev_TotalInvoiceIn
	FROM NeonBillingDev.tblInvoice 
	WHERE 
		CompanyID = p_CompanyID
		AND CurrencyID = p_CurrencyID
		AND ( (InvoiceType = 2) OR ( InvoiceType = 1 AND InvoiceStatus NOT IN ( 'cancel' , 'draft') )  )
		AND (p_AccountID = 0 or AccountID = p_AccountID)
	AND tblInvoice.IssueDate < p_StartDate ;

	SELECT 
		SUM(IF(PaymentType='Payment In',p.Amount,0)),
		SUM(IF(PaymentType='Payment Out',p.Amount,0)) 
	INTO 
		prev_TotalPaymentIn,
		prev_TotalPaymentOut
	FROM NeonBillingDev.tblPayment p 
	INNER JOIN NeonRMDev.tblAccount ac 
		ON ac.AccountID = p.AccountID
	WHERE 
		p.CompanyID = p_CompanyID
		AND ac.CurrencyId = p_CurrencyID
		AND p.Status = 'Approved'
		AND p.Recall=0
		AND (p_AccountID = 0 or p.AccountID = p_AccountID)
	AND p.PaymentDate < p_StartDate;
	
	SET @prev_TotalInvoiceOut := IFNULL(prev_TotalInvoiceOut,0) ;
	SET @prev_TotalInvoiceIn := IFNULL(prev_TotalInvoiceIn,0) ;
	SET @prev_TotalPaymentOut := IFNULL(prev_TotalPaymentOut,0) ;
	SET @prev_TotalPaymentIn := IFNULL(prev_TotalPaymentIn,0) ;
	SET @prev_CustomerUnbill := IFNULL(prev_CustomerUnbill,0) ;
	SET @prev_VendrorUnbill := IFNULL(prev_VendrorUnbill,0) ;
	
	INSERT INTO tmp_FinalResult_(TotalInvoiceOut,TotalInvoiceIn,TotalPaymentOut,TotalPaymentIn,CustomerUnbill,VendrorUnbill,date,TotalOutstanding,TotalPayable,TotalReceivable)
	SELECT 
		@prev_TotalInvoiceOut := @prev_TotalInvoiceOut +    IFNULL(TotalInvoiceOut,0) AS TotalInvoiceOut ,
		@prev_TotalInvoiceIn := @prev_TotalInvoiceIn +   IFNULL(TotalInvoiceIn,0) AS TotalInvoiceIn,
		@prev_TotalPaymentOut := @prev_TotalPaymentOut +   IFNULL(TotalPaymentOut,0) AS TotalPaymentOut,
		@prev_TotalPaymentIn := @prev_TotalPaymentIn +   IFNULL(TotalPaymentIn,0) AS TotalPaymentIn,
		@prev_CustomerUnbill := @prev_CustomerUnbill +   IFNULL(CustomerUnbill,0) AS CustomerUnbill,
		@prev_VendrorUnbill := @prev_VendrorUnbill +   IFNULL(VendrorUnbill,0) AS VendrorUnbill,
		date,
		ROUND( ( @prev_TotalInvoiceOut - @prev_TotalPaymentIn ) - ( @prev_TotalInvoiceIn - @prev_TotalPaymentOut ) + ( @prev_CustomerUnbill - @prev_VendrorUnbill ) , v_Round_ ) AS TotalOutstanding,
		ROUND( ( @prev_TotalInvoiceOut - @prev_TotalPaymentIn + @prev_CustomerUnbill ), v_Round_ ) AS TotalPayable,
		ROUND( ( @prev_TotalInvoiceIn - @prev_TotalPaymentOut + @prev_VendrorUnbill), v_Round_ ) AS TotalReceivable
	FROM(
		SELECT 
			dd.date,
			TotalPaymentIn,
			TotalPaymentOut,
			TotalInvoiceOut,
			TotalInvoiceIn,
			CustomerUnbill,
			VendrorUnbill
		FROM tblDimDate dd 
		LEFT JOIN(
			SELECT 
				SUM(IF(InvoiceType=1,GrandTotal,0)) AS TotalInvoiceOut,
				SUM(IF(InvoiceType=2,GrandTotal,0)) AS TotalInvoiceIn,
				DATE(tblInvoice.IssueDate) AS  IssueDate
			FROM NeonBillingDev.tblInvoice 
			WHERE 
				CompanyID = p_CompanyID
				AND CurrencyID = p_CurrencyID
				AND ( (InvoiceType = 2) OR ( InvoiceType = 1 AND InvoiceStatus NOT IN ( 'cancel' , 'draft') )  )
				AND (p_AccountID = 0 or AccountID = p_AccountID)
				AND IssueDate BETWEEN p_StartDate AND p_EndDate
			GROUP BY DATE(tblInvoice.IssueDate)
			HAVING (TotalInvoiceOut <> 0 OR TotalInvoiceIn <> 0)
		) TBL ON IssueDate = dd.date
		LEFT JOIN (
			SELECT
				SUM(IF(PaymentType='Payment In',p.Amount,0)) AS TotalPaymentIn ,
				SUM(IF(PaymentType='Payment Out',p.Amount,0)) AS TotalPaymentOut,
				DATE(p.PaymentDate) AS PaymentDate
			FROM NeonBillingDev.tblPayment p
			INNER JOIN NeonRMDev.tblAccount ac
				ON ac.AccountID = p.AccountID
			WHERE
				p.CompanyID = p_CompanyID
				AND ac.CurrencyId = p_CurrencyID
				AND p.Status = 'Approved'
				AND p.Recall=0
				AND (p_AccountID = 0 or p.AccountID = p_AccountID)
				AND PaymentDate BETWEEN p_StartDate AND p_EndDate
			GROUP BY DATE(p.PaymentDate)
			HAVING (TotalPaymentIn <> 0 OR TotalPaymentOut <> 0)
		)TBL2 ON PaymentDate = dd.date
		LEFT JOIN tmp_CustomerUnbilled_ cu 
			ON cu.DateID = dd.DateID
		LEFT JOIN tmp_VendorUbilled_ vu
			ON vu.DateID = dd.DateID
		WHERE dd.date BETWEEN p_StartDate AND p_EndDate
		AND ( PaymentDate IS NOT NULL OR IssueDate IS NOT NULL OR cu.DateID IS NOT NULL OR vu.DateID IS NOT NULL)
		ORDER BY dd.date
	)tbl;
	
	IF p_ListType = 'Daily'
	THEN

		SELECT
			TotalOutstanding,
			TotalPayable,
			TotalReceivable,
			date AS Date
		FROM  tmp_FinalResult_;

	END IF;

	IF p_ListType = 'Weekly'
	THEN

		SELECT 
			SUM(TotalOutstanding)  AS TotalOutstanding,
			SUM(TotalPayable)  AS TotalPayable,
			SUM(TotalReceivable)  AS TotalReceivable,
			CONCAT( YEAR(MAX(date)),' - ',WEEK(MAX(date))) AS Date
		FROM	tmp_FinalResult_
		GROUP BY 
			YEAR(date),
			WEEK(date)
		ORDER BY
			YEAR(date),
			WEEK(date);

	END IF;
	
	IF p_ListType = 'Monthly'
	THEN

		SELECT 
			SUM(TotalOutstanding)  AS TotalOutstanding,
			SUM(TotalPayable)  AS TotalPayable,
			SUM(TotalReceivable)  AS TotalReceivable,
			CONCAT( YEAR(MAX(date)),' - ',MONTHNAME(MAX(date))) AS Date
		FROM	tmp_FinalResult_
		GROUP BY
			YEAR(date)
			,MONTH(date)
		ORDER BY 
			YEAR(date)
			,MONTH(date);

	END IF;
	

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END