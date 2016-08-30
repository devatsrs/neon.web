CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getEstimate`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_EstimateNumber` VARCHAR(50), IN `p_IssueDateStart` DATETIME, IN `p_IssueDateEnd` DATETIME, IN `p_EstimateStatus` VARCHAR(50), IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_CurrencyID` INT, IN `p_isExport` INT)
BEGIN
    
    DECLARE v_OffSet_ INT;
    DECLARE v_Round_ INT;    
    DECLARE v_CurrencyCode_ VARCHAR(50);
 	 SET sql_mode = 'ALLOW_INVALID_DATES';
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	        
 	 SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	 SELECT cs.Value INTO v_Round_ FROM NeonRMDev.tblCompanySetting cs WHERE cs.`Key` = 'RoundChargesAmount' AND cs.CompanyID = p_CompanyID;
	 SELECT cr.Symbol INTO v_CurrencyCode_ from NeonRMDev.tblCurrency cr where cr.CurrencyId =p_CurrencyID;
    IF p_isExport = 0
    THEN
        SELECT 
        ac.AccountName,
        CONCAT(LTRIM(RTRIM(IFNULL(it.EstimateNumberPrefix,''))), LTRIM(RTRIM(inv.EstimateNumber))) AS EstimateNumber,
        inv.IssueDate,
        CONCAT(IFNULL(cr.Symbol,''),ROUND(inv.GrandTotal,v_Round_)) AS GrandTotal2,		
        inv.EstimateStatus,
        inv.EstimateID,
        inv.Description,
        inv.Attachment,
        inv.AccountID,		  
		  IFNULL(ac.BillingEmail,'') AS BillingEmail,
		  ROUND(inv.GrandTotal,v_Round_) AS GrandTotal,
		  inv.converted
        FROM tblEstimate inv
        INNER JOIN NeonRMDev.tblAccount ac ON ac.AccountID = inv.AccountID
        INNER JOIN NeonRMDev.tblAccountBilling ab ON ab.AccountID = ac.AccountID
		  LEFT JOIN tblInvoiceTemplate it on ab.InvoiceTemplateID = it.InvoiceTemplateID
        LEFT JOIN NeonRMDev.tblCurrency cr ON inv.CurrencyID   = cr.CurrencyId 
        WHERE ac.CompanyID = p_CompanyID
        AND (p_AccountID = 0 OR ( p_AccountID != 0 AND inv.AccountID = p_AccountID))
        AND (p_EstimateNumber = '' OR ( p_EstimateNumber != '' AND inv.EstimateNumber = p_EstimateNumber))
        AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
        AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
        AND (p_EstimateStatus = '' OR ( p_EstimateStatus != '' AND inv.EstimateStatus = p_EstimateStatus))
		AND (p_CurrencyID = '' OR ( p_CurrencyID != '' AND inv.CurrencyID = p_CurrencyID))
        ORDER BY
                CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN ac.AccountName
            END DESC,
                CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN ac.AccountName
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EstimateStatusDESC') THEN inv.EstimateStatus
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EstimateStatusASC') THEN inv.EstimateStatus
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EstimateNumberASC') THEN inv.EstimateNumber
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EstimateNumberDESC') THEN inv.EstimateNumber
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IssueDateASC') THEN inv.IssueDate
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IssueDateDESC') THEN inv.IssueDate
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GrandTotalDESC') THEN inv.GrandTotal
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GrandTotalASC') THEN inv.GrandTotal
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EstimateIDDESC') THEN inv.EstimateID
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EstimateIDASC') THEN inv.EstimateID
            END ASC
        
        LIMIT p_RowspPage OFFSET v_OffSet_;
        
        
        SELECT
            COUNT(*) AS totalcount,  ROUND(SUM(inv.GrandTotal),v_Round_) AS total_grand,v_CurrencyCode_ as currency_symbol
        FROM
        tblEstimate inv
        INNER JOIN NeonRMDev.tblAccount ac ON ac.AccountID = inv.AccountID
        INNER JOIN NeonRMDev.tblAccountBilling ab ON ab.AccountID = ac.AccountID
		  LEFT JOIN tblInvoiceTemplate it on ab.InvoiceTemplateID = it.InvoiceTemplateID
        WHERE ac.CompanyID = p_CompanyID
        AND (p_AccountID = 0 OR ( p_AccountID != 0 AND inv.AccountID = p_AccountID))
        AND (p_EstimateNumber = '' OR ( p_EstimateNumber != '' AND inv.EstimateNumber = p_EstimateNumber))
        AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
        AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
        AND (p_EstimateStatus = '' OR ( p_EstimateStatus != '' AND inv.EstimateStatus = p_EstimateStatus))
		AND (p_CurrencyID = '' OR ( p_CurrencyID != '' AND inv.CurrencyID = p_CurrencyID));
    END IF;
    IF p_isExport = 1
    THEN
        SELECT ac.AccountName ,
        ( CONCAT(LTRIM(RTRIM(IFNULL(it.InvoiceNumberPrefix,''))), LTRIM(RTRIM(inv.EstimateNumber)))) AS EstimateNumber,
        inv.IssueDate,
        ROUND(inv.GrandTotal,v_Round_) AS GrandTotal,
        inv.EstimateStatus
        FROM tblEstimate inv
        INNER JOIN NeonRMDev.tblAccount ac ON ac.AccountID = inv.AccountID
        INNER JOIN NeonRMDev.tblAccountBilling ab ON ab.AccountID = ac.AccountID
		  LEFT JOIN tblInvoiceTemplate it on ab.InvoiceTemplateID = it.InvoiceTemplateID
        WHERE ac.CompanyID = p_CompanyID
        AND (p_AccountID = 0 OR ( p_AccountID != 0 AND inv.AccountID = p_AccountID))
        AND (p_EstimateNumber = '' OR ( p_EstimateNumber != '' AND inv.EstimateNumber = p_EstimateNumber))
        AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
        AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
        AND (p_EstimateStatus = '' OR ( p_EstimateStatus != '' AND inv.EstimateStatus = p_EstimateStatus))
		AND (p_CurrencyID = '' OR ( p_CurrencyID != '' AND inv.CurrencyID = p_CurrencyID));
    END IF;
     IF p_isExport = 2
    THEN
        SELECT ac.AccountID ,
        ac.AccountName,
        ( CONCAT(LTRIM(RTRIM(IFNULL(it.InvoiceNumberPrefix,''))), LTRIM(RTRIM(inv.EstimateNumber)))) AS EstimateNumber,
        inv.IssueDate,
		  ROUND(inv.GrandTotal,v_Round_) AS GrandTotal,
        inv.EstimateStatus,
        inv.EstimateID
        FROM tblEstimate inv
        INNER JOIN NeonRMDev.tblAccount ac ON ac.AccountID = inv.AccountID
        INNER JOIN NeonRMDev.tblAccountBilling ab ON ab.AccountID = ac.AccountID
		  LEFT JOIN tblInvoiceTemplate it on ab.InvoiceTemplateID = it.InvoiceTemplateID
        WHERE ac.CompanyID = p_CompanyID
        AND (p_AccountID = 0 OR ( p_AccountID != 0 AND inv.AccountID = p_AccountID))
        AND (p_EstimateNumber = '' OR ( p_EstimateNumber != '' AND inv.EstimateNumber = p_EstimateNumber))
        AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
        AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
        AND (p_EstimateStatus = '' OR ( p_EstimateStatus != '' AND inv.EstimateStatus = p_EstimateStatus))
		AND (p_CurrencyID = '' OR ( p_CurrencyID != '' AND inv.CurrencyID = p_CurrencyID));
    END IF; 
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    END