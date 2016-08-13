CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_CustomerPanel_getInvoice`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_InvoiceNumber` VARCHAR(50), IN `p_IssueDateStart` DATETIME, IN `p_IssueDateEnd` DATETIME, IN `p_InvoiceType` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT, IN `p_zerovalueinvoice` INT)
BEGIN
    DECLARE v_OffSet_ int;
    DECLARE v_Round_ int;
    SET sql_mode = 'ALLOW_INVALID_DATES';
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	        
 	 SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	 SELECT cs.Value INTO v_Round_ from NeonRMDev.tblCompanySetting cs where cs.`Key` = 'RoundChargesAmount' AND cs.CompanyID = p_CompanyID;

    IF p_isExport = 0
    THEN

        SELECT inv.InvoiceType ,
        ac.AccountName,
        CONCAT(ltrim(rtrim(IFNULL(it.InvoiceNumberPrefix,''))), ltrim(rtrim(inv.InvoiceNumber))) as InvoiceNumber,
        inv.IssueDate,
        CONCAT(IFNULL(cr.Symbol,''),ROUND(inv.GrandTotal,v_Round_)) as GrandTotal2,
		  CONCAT(IFNULL(cr.Symbol,''),format((select IFNULL(sum(p.Amount),0) from tblPayment p where REPLACE(p.InvoiceNo,'-','') = ( CONCAT(ltrim(rtrim(REPLACE(IFNULL(it.InvoiceNumberPrefix,''),'-',''))) , ltrim(rtrim(inv.InvoiceNumber)))) AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0),v_Round_),'/',format((inv.GrandTotal -  (select IFNULL(sum(p.Amount),0) from tblPayment p where REPLACE(p.InvoiceNo,'-','') = ( CONCAT(ltrim(rtrim(REPLACE(IFNULL(it.InvoiceNumberPrefix,''),'-',''))), ltrim(rtrim(inv.InvoiceNumber)))) AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0) ),v_Round_)) as `PendingAmount`,
        inv.InvoiceStatus,
        inv.InvoiceID,
        inv.Description,
        inv.Attachment,
        inv.AccountID,
        ROUND(inv.GrandTotal -  (select IFNULL(sum(p.Amount),0) from tblPayment p where REPLACE(p.InvoiceNo,'-','') = ( CONCAT(ltrim(rtrim(REPLACE(IFNULL(it.InvoiceNumberPrefix,''),'-',''))), ltrim(rtrim(inv.InvoiceNumber)))) AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0 ),v_Round_) as OutstandingAmount, 
        inv.ItemInvoice,
		  IFNULL(ac.BillingEmail,'') as BillingEmail,
		  ROUND(inv.GrandTotal,v_Round_) as GrandTotal
        FROM tblInvoice inv
        inner join NeonRMDev.tblAccount ac on ac.AccountID = inv.AccountID
        INNER JOIN NeonRMDev.tblAccountBilling ab ON ab.AccountID = ac.AccountID
        LEFT JOIN tblInvoiceTemplate it on ab.InvoiceTemplateID = it.InvoiceTemplateID
        left join NeonRMDev.tblCurrency cr ON inv.CurrencyID   = cr.CurrencyId 
        where ac.CompanyID = p_CompanyID
        AND (inv.AccountID = p_AccountID)
		AND ( (IFNULL(inv.InvoiceStatus,'') = '') OR  inv.InvoiceStatus NOT IN ( 'cancel' , 'draft' , 'awaiting'))
        AND (p_InvoiceNumber = '' OR ( p_InvoiceNumber != '' AND inv.InvoiceNumber = p_InvoiceNumber))
        AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
        AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
        AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND inv.InvoiceType = p_InvoiceType))
        AND (p_zerovalueinvoice = 0 OR ( p_zerovalueinvoice = 1 AND inv.GrandTotal > 0))
        ORDER BY
                CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN ac.AccountName
            END DESC,
                CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN ac.AccountName
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceTypeDESC') THEN inv.InvoiceType
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceTypeASC') THEN inv.InvoiceType
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceStatusDESC') THEN inv.InvoiceStatus
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceStatusASC') THEN inv.InvoiceStatus
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceNumberASC') THEN inv.InvoiceNumber
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceNumberDESC') THEN inv.InvoiceNumber
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IssueDateASC') THEN inv.IssueDate
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IssueDateDESC') THEN inv.IssueDate
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GrandTotalDESC') THEN inv.GrandTotal
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GrandTotalASC') THEN inv.GrandTotal
            END ASC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceIDDESC') THEN inv.InvoiceID
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceIDASC') THEN inv.InvoiceID
            END ASC
        
        LIMIT p_RowspPage OFFSET v_OffSet_;
        
        
        SELECT
            COUNT(*) AS totalcount,ROUND(sum(inv.GrandTotal),v_Round_) as total_grand,ROUND(sum(format((select IFNULL(sum(p.Amount),0) from tblPayment p where REPLACE(p.InvoiceNo,'-','') = ( CONCAT(ltrim(rtrim(REPLACE(IFNULL(it.InvoiceNumberPrefix,''),'-',''))) , ltrim(rtrim(inv.InvoiceNumber)))) AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0),v_Round_)),v_Round_) as `TotalPayment`,sum(ROUND(inv.GrandTotal -  (select IFNULL(sum(p.Amount),0) from tblPayment p where REPLACE(p.InvoiceNo,'-','') = ( CONCAT(ltrim(rtrim(REPLACE(IFNULL(it.InvoiceNumberPrefix,''),'-',''))), ltrim(rtrim(inv.InvoiceNumber)))) AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND p.Recall =0 ),v_Round_)) as TotalPendingAmount,cr.Symbol as currency_symbol
        FROM
        tblInvoice inv
        inner join NeonRMDev.tblAccount ac on ac.AccountID = inv.AccountID
        INNER JOIN NeonRMDev.tblAccountBilling ab ON ab.AccountID = ac.AccountID
        LEFT JOIN tblInvoiceTemplate it on ab.InvoiceTemplateID = it.InvoiceTemplateID
		left join NeonRMDev.tblCurrency cr ON inv.CurrencyID   = cr.CurrencyId
        where ac.CompanyID = p_CompanyID
        AND (inv.AccountID = p_AccountID)
		AND ( (IFNULL(inv.InvoiceStatus,'') = '') OR  inv.InvoiceStatus NOT IN ( 'cancel' , 'draft' , 'awaiting'))
        AND (p_InvoiceNumber = '' OR ( p_InvoiceNumber != '' AND inv.InvoiceNumber = p_InvoiceNumber))
        AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
        AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
        AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND inv.InvoiceType = p_InvoiceType))
        AND (p_zerovalueinvoice = 0 OR ( p_zerovalueinvoice = 1 AND inv.GrandTotal > 0));
    END IF;
    IF p_isExport = 1
    THEN

        SELECT ac.AccountName ,
        ( CONCAT(ltrim(rtrim(IFNULL(it.InvoiceNumberPrefix,''))), ltrim(rtrim(inv.InvoiceNumber)))) as InvoiceNumber,
        inv.IssueDate,
        ROUND(inv.GrandTotal,v_Round_) as GrandTotal,
        CONCAT(format((select IFNULL(sum(p.Amount),0) from tblPayment p where REPLACE(p.InvoiceNo,'-','') = ( CONCAT(ltrim(rtrim(REPLACE(IFNULL(it.InvoiceNumberPrefix,''),'-',''))), ltrim(rtrim(inv.InvoiceNumber)))) AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND (p.Recall =0)),v_Round_),'/',format((inv.GrandTotal -  (select IFNULL(sum(p.Amount),0) from tblPayment p where REPLACE(p.InvoiceNo,'-','') = ( CONCAT(ltrim(rtrim(REPLACE(IFNULL(it.InvoiceNumberPrefix,''),'-',''))), ltrim(rtrim(inv.InvoiceNumber)))) AND p.Status = 'Approved' AND p.AccountID = inv.AccountID AND (p.Recall =0)) ),v_Round_)) as `Paid/OS`,
        inv.InvoiceStatus,
        inv.InvoiceType,
        inv.ItemInvoice
        FROM tblInvoice inv
        inner join NeonRMDev.tblAccount ac on ac.AccountID = inv.AccountID
        INNER JOIN NeonRMDev.tblAccountBilling ab ON ab.AccountID = ac.AccountID
        LEFT JOIN tblInvoiceTemplate it on ab.InvoiceTemplateID = it.InvoiceTemplateID
		left join NeonRMDev.tblCurrency cr ON inv.CurrencyID   = cr.CurrencyId
        where ac.CompanyID = p_CompanyID
        AND (inv.AccountID = p_AccountID)
		AND ( (IFNULL(inv.InvoiceStatus,'') = '') OR  inv.InvoiceStatus NOT IN ( 'cancel' , 'draft' , 'awaiting'))
        AND (p_InvoiceNumber = '' OR ( p_InvoiceNumber != '' AND inv.InvoiceNumber = p_InvoiceNumber))
        AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
        AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
        AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND inv.InvoiceType = p_InvoiceType))
        AND (p_zerovalueinvoice = 0 OR ( p_zerovalueinvoice = 1 AND inv.GrandTotal > 0));
    END IF;
     

     
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END