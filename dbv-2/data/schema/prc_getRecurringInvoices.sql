CREATE DEFINER=`neon-user-abubakar`@`122.129.78.153` PROCEDURE `prc_getRecurringInvoices`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_Status` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(50),
	IN `p_isExport` INT



















)
BEGIN
	DECLARE v_OffSet_ INT;
	DECLARE v_Round_ INT;    
	SET sql_mode = 'ALLOW_INVALID_DATES';
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	     
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	IF p_isExport = 0
	THEN
	  SELECT
	  rinv.RecurringInvoiceID,
	  rinv.Title, 
	  ac.AccountName,
	  DATE(rinv.LastInvoicedDate),
	  DATE(rinv.NextInvoiceDate),
	  CONCAT(IFNULL(cr.Symbol,''),ROUND(rinv.GrandTotal,v_Round_)) AS GrandTotal2,		
	  rinv.`Status`,
	  rinv.Occurrence,
	  (SELECT COUNT(InvoiceID) FROM tblInvoice WHERE (InvoiceStatus!='awaiting' AND InvoiceStatus!='cancel' ) AND RecurringInvoiceID = rinv.RecurringInvoiceID) as Sent,
	  rinv.BillingCycleType,
	  rinv.BillingCycleValue,
	  rinv.AccountID,
	  ROUND(rinv.GrandTotal,v_Round_) AS GrandTotal
	  FROM tblRecurringInvoice rinv
	  INNER JOIN NeonRMDev.tblAccount ac ON ac.AccountID = rinv.AccountID
	  LEFT JOIN NeonRMDev.tblCurrency cr ON rinv.CurrencyID   = cr.CurrencyId 
	  WHERE ac.CompanyID = p_CompanyID
	  AND (p_AccountID = 0 OR rinv.AccountID = p_AccountID)
	  AND (p_Status =2 OR rinv.`Status` = p_Status)
	  ORDER BY
	  		CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TitleDESC') THEN rinv.Title
	      END DESC,
	          CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TitleASC') THEN rinv.Title
	      END ASC,
	   	CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN ac.AccountName
	      END DESC,
	          CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN ac.AccountName
	      END ASC,
	      CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'LastInvoicedDateDESC') THEN rinv.LastInvoicedDate
	      END DESC,
	      CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'LastInvoicedDateASC') THEN rinv.LastInvoicedDate
	      END ASC,
	      CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NextInvoiceDateDESC') THEN rinv.NextInvoiceDate
	      END DESC,
	      CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NextInvoiceDateASC') THEN rinv.NextInvoiceDate
	      END ASC,
	      CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RecurringInvoiceStatusDESC') THEN rinv.`Status`
	      END DESC,
	      CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RecurringInvoiceStatusASC') THEN rinv.`Status`
	      END ASC,
	      CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GrandTotalDESC') THEN rinv.GrandTotal
	      END DESC,
	      CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GrandTotalASC') THEN rinv.GrandTotal
	      END ASC
	  
	  LIMIT p_RowspPage OFFSET v_OffSet_;
	  
	  
	  SELECT
	      COUNT(*) AS totalcount,  ROUND(SUM(rinv.GrandTotal),v_Round_) AS total_gran 
	  FROM tblRecurringInvoice rinv
	  INNER JOIN NeonRMDev.tblAccount ac ON ac.AccountID = rinv.AccountID
	  LEFT JOIN NeonRMDev.tblCurrency cr ON rinv.CurrencyID   = cr.CurrencyId 
	  WHERE ac.CompanyID = p_CompanyID
	  AND (p_AccountID = 0 OR rinv.AccountID = p_AccountID)
	  AND (p_Status =2 OR rinv.`Status` = p_Status);
	END IF;
	IF p_isExport = 1
	THEN
	  SELECT 
	  ac.AccountName,
	  rinv.LastInvoiceNumber,
	  rinv.LastInvoicedDate,		
	  rinv.Description,		  
	  IFNULL(ac.BillingEmail,'') AS BillingEmail,
	  ROUND(rinv.GrandTotal,v_Round_) AS GrandTotal
	  FROM tblRecurringInvoice rinv
	  INNER JOIN NeonRMDev.tblAccount ac ON ac.AccountID = rinv.AccountID
	  LEFT JOIN NeonRMDev.tblCurrency cr ON rinv.CurrencyID   = cr.CurrencyId 
	  WHERE ac.CompanyID = p_CompanyID
	  AND (p_AccountID = 0 OR rinv.AccountID = p_AccountID)
	  AND (p_Status =2 OR rinv.`Status` = p_Status);
	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END