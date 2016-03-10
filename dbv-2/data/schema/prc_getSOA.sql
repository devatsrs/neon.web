CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getSOA`(IN `p_CompanyID` INT, IN `p_accountID` INT, IN `p_StartDate` datetime, IN `p_EndDate` datetime, IN `p_isExport` INT )
BEGIN
	
		
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
    SET SESSION sql_mode='ONLY_FULL_GROUP_BY,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';

    DROP TEMPORARY TABLE IF EXISTS tmp_AOS_;
    CREATE TEMPORARY TABLE tmp_AOS_ (
        AccountName VARCHAR(50),
        InvoiceNo VARCHAR(50),
        PaymentType VARCHAR(15),
        PaymentDate LONGTEXT,
        PaymentMethod VARCHAR(20),
        Amount NUMERIC(18, 8),
        Currency VARCHAR(15),
        PeriodCover VARCHAR(30),
		  StartDate datetime,
  		  EndDate datetime,
        Type VARCHAR(10),
        InvoiceAmount NUMERIC(18, 8),
        CreatedDate VARCHAR(15),
        PaymentsID INT
    );
 /* Invoice & Payment with Invoice number */
    INSERT INTO tmp_AOS_
        SELECT
						DISTINCT 
            tblAccount.AccountName,
            tblInvoice.InvoiceNumber,
            tblPayment.PaymentType,
            CASE
            WHEN p_isExport = 1
            THEN
                DATE_FORMAT(tblPayment.PaymentDate,'%d/%m/%Y')
            ELSE
                DATE_FORMAT(tblPayment.PaymentDate, '%d/%m/%Y')
            END AS dates,
            tblPayment.PaymentMethod,
            tblPayment.Amount,
            tblPayment.Currency,
            CASE
            	WHEN (tblInvoice.ItemInvoice = 1 AND p_isExport = 1) THEN
	                DATE_FORMAT(tblInvoice.IssueDate,'%d/%m/%Y')
	            WHEN (tblInvoice.ItemInvoice = 1 AND p_isExport = 0) THEN
	            	 DATE_FORMAT(tblInvoice.IssueDate,'%d-%m-%Y')
					WHEN (tblInvoice.ItemInvoice IS NUll AND p_isExport = 1) THEN
	            	Concat(DATE_FORMAT(tblInvoiceDetail.StartDate,'%d/%m/%Y') ,' - ' , DATE_FORMAT(tblInvoiceDetail.EndDate,'%d/%m/%Y'))
	            WHEN (tblInvoice.ItemInvoice IS NUll AND p_isExport = 0) THEN
	            	 Concat(DATE_FORMAT(tblInvoiceDetail.StartDate,'%d-%m-%Y') , ' => ' , DATE_FORMAT(tblInvoiceDetail.EndDate,'%d-%m-%Y'))
            END AS dates,
			tblInvoiceDetail.StartDate,
			tblInvoiceDetail.EndDate,
            tblInvoice.InvoiceType,
            tblInvoice.GrandTotal,
            tblInvoice.created_at,
            tblPayment.PaymentID
        FROM tblInvoice
        LEFT JOIN tblInvoiceDetail
            ON tblInvoice.InvoiceID = tblInvoiceDetail.InvoiceID AND ( (tblInvoice.InvoiceType = 1 AND tblInvoiceDetail.ProductType = 2 ) OR  tblInvoice.InvoiceType =2 )/* ProductType =2 = INVOICE USAGE AND InvoiceType = 1 Invoice sent and InvoiceType =2 invoice recevied */
        INNER JOIN Ratemanagement3.tblAccount
            ON tblInvoice.AccountID = tblAccount.AccountID
        LEFT JOIN tblInvoiceTemplate  on tblAccount.InvoiceTemplateID = tblInvoiceTemplate.InvoiceTemplateID
        LEFT JOIN tblPayment
            ON (tblInvoice.InvoiceNumber = tblPayment.InvoiceNo OR REPLACE(tblPayment.InvoiceNo,'-','') = concat( ltrim(rtrim(REPLACE(tblInvoiceTemplate.InvoiceNumberPrefix,'-',''))) , ltrim(rtrim(tblInvoice.InvoiceNumber)) ) )
            AND tblPayment.Status = 'Approved' 
            AND tblPayment.Recall = 0
        WHERE tblInvoice.CompanyID = p_CompanyID
        AND (p_accountID = 0
        OR tblInvoice.AccountID = p_accountID)
		AND 
		(
			(p_StartDate = '0000-00-00 00:00:00' OR  p_EndDate = '0000-00-00 00:00:00') OR ((p_StartDate != '0000-00-00 00:00:00' AND p_EndDate != '0000-00-00 00:00:00') AND str_to_date(tblInvoice.IssueDate,'%Y-%m-%d') between  str_to_date(p_StartDate,'%Y-%m-%d') and str_to_date(p_EndDate,'%Y-%m-%d') )
		)
        /* Payment without Invoice number  */
			
        UNION ALL
        SELECT
						DISTINCT
            tblAccount.AccountName,
            tblPayment.InvoiceNo,
            tblPayment.PaymentType,
            CASE
            WHEN p_isExport = 1
            THEN
               DATE_FORMAT(tblPayment.PaymentDate, '%d/%m/%Y')
            ELSE
               DATE_FORMAT(tblPayment.PaymentDate, '%d/%m/%Y')
            END AS dates,
            tblPayment.PaymentMethod,
            tblPayment.Amount,
            tblPayment.Currency,
            '' AS dates,
			tblPayment.PaymentDate,
			tblPayment.PaymentDate,
            CASE
			WHEN tblPayment.PaymentType = 'Payment In'
            THEN
                1
            ELSE
                2
            END AS InvoiceType,
            0 AS GrandTotal,
            tblPayment.created_at,
            tblPayment.PaymentID
        FROM tblPayment
        INNER JOIN Ratemanagement3.tblAccount
            ON tblPayment.AccountID = tblAccount.AccountID
        WHERE tblPayment.Status = 'Approved'
        AND tblPayment.Recall = 0
        AND tblPayment.AccountID = p_accountID
        AND tblPayment.CompanyID = p_CompanyID
        AND tblPayment.InvoiceNo = ''
		AND 
		(
			(p_StartDate = '0000-00-00 00:00:00' OR  p_EndDate = '0000-00-00 00:00:00') OR ((p_StartDate != '0000-00-00 00:00:00' AND p_EndDate != '0000-00-00 00:00:00') AND str_to_date(tblPayment.PaymentDate,'%Y-%m-%d') between  str_to_date(p_StartDate,'%Y-%m-%d') and str_to_date(p_EndDate,'%Y-%m-%d') )
		);


    SELECT
				 
        IFNULL(InvoiceNo,'') as InvoiceNo,
        PeriodCover,
        InvoiceAmount,
        ' ' AS spacer,
         CASE
        WHEN p_isExport = 0
        THEN
        GROUP_CONCAT(PaymentDate SEPARATOR '<br/>')
		  ELSE
		   GROUP_CONCAT(PaymentDate SEPARATOR "\r\n") 
		  END	as PaymentDate,
        SUM(Amount) AS payment,
        NULL AS ballence,
        CASE
        WHEN p_isExport = 0
        THEN
            MAX(PaymentsID)
        END PaymentID 
    FROM tmp_AOS_
    WHERE Type = 1
    GROUP BY InvoiceNo,PeriodCover,InvoiceAmount,StartDate
    ORDER BY StartDate desc;

    SELECT
				 
        IFNULL(InvoiceNo,'') as InvoiceNo,
        PeriodCover,
        InvoiceAmount,
        ' ' AS spacer,
        PaymentDate,
        Amount AS payment,
        NULL AS ballence,
        CASE
        WHEN p_isExport = 0
        THEN
            PaymentsID
        END PaymentID
    FROM tmp_AOS_
    WHERE Type = 2
    ORDER BY StartDate desc;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END