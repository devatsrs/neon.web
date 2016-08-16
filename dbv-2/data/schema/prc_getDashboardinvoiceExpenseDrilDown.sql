CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getDashboardinvoiceExpenseDrilDown`(IN `p_CompanyID` INT, IN `p_CurrencyID` INT, IN `p_StartDate` VARCHAR(50), IN `p_EndDate` VARCHAR(50)



, IN `p_Type` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(50)




















, IN `p_CustomerID` INT, IN `p_Export` INT



















)
BEGIN
	DECLARE v_Round_ int;
	DECLARE v_OffSet_ int;
	DECLARE v_CurrencyCode_ VARCHAR(50);
	DECLARE v_PaymentDueInDays_ int;
	DECLARE v_TotalCount int;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT cs.Value INTO v_Round_ FROM NeonRMDev.tblCompanySetting cs WHERE cs.`Key` = 'RoundChargesAmount' AND cs.CompanyID = p_CompanyID;
	SELECT cr.Symbol INTO v_CurrencyCode_ from NeonRMDev.tblCurrency cr where cr.CurrencyId =p_CurrencyID;
	SELECT cs.Value INTO v_PaymentDueInDays_ from NeonRMDev.tblCompanySetting cs where cs.`Key` = 'PaymentDueInDays' AND cs.CompanyID = p_CompanyID;
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	IF p_Type = 1  -- Payment Recived
	THEN
		DROP TEMPORARY TABLE IF EXISTS tmp_Payment_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Payment_(
			PaymentID int,
			AccountName varchar(100),
			AccountID int,
			Amount decimal(18,6),
			PaymentType varchar(10),
			CurrencyID int,
			PaymentDate datetime,
			Status varchar(50),
			CreatedBy varchar(50),
			PaymentProof varchar(150),
			InvoiceNo varchar(30),
			PaymentMethod varchar(15),
			Notes varchar(500),
			Recall TINYINT(1),
			RecallReasoan varchar(500),
			RecallBy varchar(30),
			AmountWithSymbol varchar(30)
		);
		
		INSERT INTO tmp_Payment_
		SELECT 
	      p.PaymentID,
	      ac.AccountName,
	      p.AccountID,
	      ROUND(p.Amount,v_Round_) AS Amount,
			p.PaymentType,
	      p.CurrencyID,
	      CASE WHEN inv.InvoiceID IS NOT NULL
			THEN
				inv.IssueDate
			ELSE
				p.PaymentDate
			END as PaymentDate,
	      p.Status,
	      p.CreatedBy,
	      p.PaymentProof,
	      p.InvoiceNo,
	      p.PaymentMethod,
	      p.Notes,
	      p.Recall,
	      p.RecallReasoan,
	      p.RecallBy,
	      CONCAT(IFNULL(v_CurrencyCode_,''),ROUND(p.Amount,v_Round_)) as AmountWithSymbol
		FROM tblPayment p 
		INNER JOIN NeonRMDev.tblAccount ac 
			ON ac.AccountID = p.AccountID
		LEFT JOIN tblInvoice inv on p.InvoiceID = inv.InvoiceID 
			AND p.Status = 'Approved' 
			AND p.AccountID = inv.AccountID 
			AND p.Recall=0
			AND InvoiceType = 1 
		WHERE 
			p.CompanyID = p_CompanyID
			AND ac.CurrencyId = p_CurrencyID
			AND (p_CustomerID=0 OR ac.AccountID = p_CustomerID)
			AND p.Status = 'Approved'
			AND p.Recall=0
			AND p.PaymentType = 'Payment In';
	       
		IF  p_Export = 0
		THEN
			SELECT 
		      PaymentID,
		      AccountName,
		      AccountID,
		      Amount,
			 	PaymentType,
		      CurrencyID,
		      PaymentDate,
		      Status,
		      CreatedBy,
		      PaymentProof,
		      InvoiceNo,
		      PaymentMethod,
		      Notes,
		      Recall,
		      RecallReasoan,
		      RecallBy,
		      AmountWithSymbol
			FROM tmp_Payment_
			where (PaymentDate BETWEEN p_StartDate AND p_EndDate)
			ORDER BY
					CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN AccountName
					END DESC,
					CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN AccountName
					END ASC,
					CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceNoDESC') THEN InvoiceNo
					END DESC,
					CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceNoASC') THEN InvoiceNo
					END ASC,
					CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AmountDESC') THEN Amount
					END DESC,
					CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AmountASC') THEN Amount
					END ASC,
					CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PaymentTypeDESC') THEN PaymentType
					END DESC,
					CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PaymentTypeASC') THEN PaymentType
					END ASC,
					CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PaymentDateDESC') THEN PaymentDate
					END DESC,
					CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PaymentDateASC') THEN PaymentDate
					END ASC,
					CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusDESC') THEN Status
					END DESC,
					CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusASC') THEN Status
					END ASC,
					CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreatedByDESC') THEN CreatedBy
					END DESC,
					CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreatedByASC') THEN CreatedBy
					END ASC
			LIMIT p_RowspPage OFFSET v_OffSet_;
			
			
			SELECT COUNT(PaymentID) AS totalcount,ROUND(COALESCE(SUM(Amount),0),v_Round_) as totalsum
			FROM tmp_Payment_
			where (PaymentDate BETWEEN p_StartDate AND p_EndDate);
		END IF;
		
		IF p_Export=1
		THEN
			SELECT 
            AccountName,
            Amount,
            CASE WHEN p_CustomerID > 0 THEN
              CASE WHEN PaymentType='Payment Out' THEN 'Payment In' ELSE 'Payment Out'
              END
            ELSE  PaymentType
            END as PaymentType,
            PaymentDate,
            Status,
            CreatedBy,
            InvoiceNo,
            PaymentMethod,
            Notes
				FROM tmp_Payment_
				where (PaymentDate BETWEEN p_StartDate AND p_EndDate); 
		END IF;
	END IF;
	
	IF p_Type=2 || p_Type=3 -- 2 Total Invoices
	THEN							-- 3 Total OutStanding
	
	DROP TEMPORARY TABLE IF EXISTS tmp_Invoices_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Invoices_(
		InvoiceType tinyint(1),
		AccountName varchar(100),
		InvoiceNumber varchar(100),
		IssueDate datetime,
		InvoicePeriod varchar(100),
		CurrencySymbol varchar(5),
		GrandTotal decimal(18,6),
		TotalPayment decimal(18,6),
		PendingAmount decimal(18,6),
		InvoiceStatus varchar(50),
		InvoiceID int,
		Description varchar(500),
		Attachment varchar(255),
		AccountID int,
		ItemInvoice tinyint(1),
		BillingEmail varchar(255),
		AccountNumber varchar(100),
		PaymentDueInDays int,
		PaymentDate datetime,
		SubTotal decimal(18,6),
		TotalTax decimal(18,6),
		NominalAnalysisNominalAccountNumber varchar(100)
	);
	
	INSERT INTO tmp_Invoices_
		SELECT inv.InvoiceType ,
			ac.AccountName,
			inv.FullInvoiceNumber as InvoiceNumber,
			inv.IssueDate,
			IF(invd.StartDate IS NULL ,'',CONCAT('From ',date(invd.StartDate) ,'<br> To ',date(invd.EndDate))) as InvoicePeriod,
			IFNULL(cr.Symbol,'') as CurrencySymbol,
			inv.GrandTotal as GrandTotal,		
			(select IFNULL(sum(p.Amount),0) from tblPayment p where p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0) as TotalPayment,
			(inv.GrandTotal -  (select IFNULL(sum(p.Amount),0) from tblPayment p where p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0) ) as `PendingAmount`,
			inv.InvoiceStatus,
			inv.InvoiceID,
			inv.Description,
			inv.Attachment,
			inv.AccountID,
			inv.ItemInvoice,
			IFNULL(ac.BillingEmail,'') as BillingEmail,
			ac.Number,
			IFNULL(ab.PaymentDueInDays,v_PaymentDueInDays_) as PaymentDueInDays,
			(select PaymentDate from tblPayment p where p.InvoiceID = inv.InvoiceID AND p.Status = 'Approved' AND p.Recall =0 AND p.AccountID = inv.AccountID order by PaymentID desc limit 1) AS PaymentDate,
			inv.SubTotal,
			inv.TotalTax,
			ac.NominalAnalysisNominalAccountNumber 
      FROM tblInvoice inv
      INNER JOIN NeonRMDev.tblAccount ac ON inv.AccountID = ac.AccountID
      AND (p_CustomerID=0 OR ac.AccountID = p_CustomerID)
      LEFT JOIN tblInvoiceDetail invd on invd.InvoiceID = inv.InvoiceID AND invd.ProductType = 2
		INNER JOIN NeonRMDev.tblAccountBilling ab ON ab.AccountID = ac.AccountID
		LEFT JOIN NeonRMDev.tblCurrency cr ON inv.CurrencyID   = cr.CurrencyId 
		WHERE 
		inv.CompanyID = p_CompanyID
		AND cr.CurrencyID = p_CurrencyID
		AND (IssueDate BETWEEN p_StartDate AND p_EndDate)
		AND InvoiceType = 1
		AND ((p_Type=2 AND inv.InvoiceStatus NOT IN ( 'cancel' , 'draft' )) OR (p_Type=3 AND inv.InvoiceStatus NOT IN ( 'cancel' , 'draft' , 'paid')));
	IF p_Export = 0
	THEN
      SELECT 
			AccountName,
			InvoiceNumber,
			IssueDate,
			InvoicePeriod,
			CONCAT(CurrencySymbol, ROUND(GrandTotal,v_Round_)) as GrandTotal2,
			CONCAT(CurrencySymbol,ROUND(TotalPayment,v_Round_),'/',ROUND(PendingAmount,v_Round_)) as `PendingAmount`,
			InvoiceStatus,
			InvoiceID,
			Description,
			Attachment,
			AccountID,
			PendingAmount as OutstandingAmount, 
			ItemInvoice,
			BillingEmail,
			GrandTotal
        FROM tmp_Invoices_        
        ORDER BY
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN AccountName
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN AccountName
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceTypeDESC') THEN InvoiceType
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceTypeASC') THEN InvoiceType
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceStatusDESC') THEN InvoiceStatus
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceStatusASC') THEN InvoiceStatus
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceNumberASC') THEN InvoiceNumber
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceNumberDESC') THEN InvoiceNumber
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IssueDateASC') THEN IssueDate
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IssueDateDESC') THEN IssueDate
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoicePeriodASC') THEN InvoicePeriod
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoicePeriodDESC') THEN InvoicePeriod
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GrandTotalDESC') THEN GrandTotal
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GrandTotalASC') THEN GrandTotal
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceIDDESC') THEN InvoiceID
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceIDASC') THEN InvoiceID
            END ASC
			LIMIT p_RowspPage OFFSET v_OffSet_;
        
      	SELECT COUNT(*) AS totalcount,
					ROUND(COALESCE(SUM(GrandTotal),0),v_Round_) as totalsum, 
					ROUND(COALESCE(SUM(TotalPayment),0),v_Round_) totalpaymentsum,
					ROUND(COALESCE(SUM(PendingAmount),0),v_Round_) totalpendingsum 
			FROM tmp_Invoices_;
      	
		END IF;
		IF p_Export=1
		THEN
			SELECT 
				AccountName ,
				InvoiceNumber,
				IssueDate,
				REPLACE(InvoicePeriod, '<br>', '') as InvoicePeriod,
				CONCAT(CurrencySymbol, ROUND(GrandTotal,v_Round_)) as GrandTotal,
				CONCAT(CurrencySymbol,ROUND(TotalPayment,v_Round_),'/',ROUND(PendingAmount,v_Round_)) as `Paid/OS`,
				InvoiceStatus,
				InvoiceType,
				ItemInvoice
        FROM tmp_Invoices_;
		END IF;
	END IF;
		
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END