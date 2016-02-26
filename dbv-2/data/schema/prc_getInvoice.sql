CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getInvoice`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_InvoiceNumber` VARCHAR(50), IN `p_IssueDateStart` DATETIME, IN `p_IssueDateEnd` DATETIME, IN `p_InvoiceType` INT, IN `p_InvoiceStatus` VARCHAR(50), IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT, IN `p_sageExport` INT, IN `p_zerovalueinvoice` INT, IN `p_InvoiceID` LONGTEXT)
BEGIN
    DECLARE v_OffSet_ int;
    DECLARE v_Round_ int;
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    SELECT cs.Value INTO v_Round_ from Ratemanagement3.tblCompanySetting cs where cs.`Key` = 'RoundChargesAmount' AND cs.CompanyID = p_CompanyID;
	        
 	 SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;



    IF p_isExport = 0 and p_sageExport = 0
    THEN

        SELECT inv.InvoiceType ,
        ac.AccountName,
        ( CONCAT(ltrim(rtrim(it.InvoiceNumberPrefix)), ltrim(rtrim(inv.InvoiceNumber)))) as InvoiceNumber,
        inv.IssueDate,
        ROUND(inv.GrandTotal,v_Round_) as GrandTotal,
		  CONCAT(ROUND(format((select IFNULL(sum(p.Amount),0) from tblPayment p where REPLACE(p.InvoiceNo,'-','') = ( CONCAT(ltrim(rtrim(REPLACE(it.InvoiceNumberPrefix,'-',''))) , ltrim(rtrim(inv.InvoiceNumber)))) AND p.Status = 'Approved' AND p.AccountID = inv.AccountID),6),v_Round_),'/',ROUND(format((inv.GrandTotal -  (select IFNULL(sum(p.Amount),0) from tblPayment p where REPLACE(p.InvoiceNo,'-','') = ( CONCAT(ltrim(rtrim(REPLACE(it.InvoiceNumberPrefix,'-',''))), ltrim(rtrim(inv.InvoiceNumber)))) AND p.Status = 'Approved'  AND p.AccountID = inv.AccountID AND (p.Recall =0)) ),6),v_Round_)) as `PendingAmount`,
        inv.InvoiceStatus,
        inv.InvoiceID,
        inv.Description,
        inv.Attachment,
        inv.AccountID,
        ROUND(inv.GrandTotal -  (select IFNULL(sum(p.Amount),0) from tblPayment p where REPLACE(p.InvoiceNo,'-','') = ( CONCAT(ltrim(rtrim(REPLACE(it.InvoiceNumberPrefix,'-',''))), ltrim(rtrim(inv.InvoiceNumber)))) AND p.Status = 'Approved' AND p.AccountID = inv.AccountID),v_Round_) as OutstandingAmount, 
        inv.ItemInvoice,
		  IFNULL(ac.BillingEmail,'') as BillingEmail
        FROM tblInvoice inv
        inner join Ratemanagement3.tblAccount ac on ac.AccountID = inv.AccountID
        left join tblInvoiceTemplate it on ac.InvoiceTemplateID = it.InvoiceTemplateID
        where ac.CompanyID = p_CompanyID
        AND (p_AccountID = 0 OR ( p_AccountID != 0 AND inv.AccountID = p_AccountID))
        AND (p_InvoiceNumber = '' OR ( p_InvoiceNumber != '' AND inv.InvoiceNumber = p_InvoiceNumber))
        AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
        AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
        AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND inv.InvoiceType = p_InvoiceType))
        AND (p_InvoiceStatus = '' OR ( p_InvoiceStatus != '' AND inv.InvoiceStatus = p_InvoiceStatus))
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
            COUNT(*) AS totalcount
        FROM
        tblInvoice inv
        inner join Ratemanagement3.tblAccount ac on ac.AccountID = inv.AccountID
        left join tblInvoiceTemplate it on ac.InvoiceTemplateID = it.InvoiceTemplateID
        where ac.CompanyID = p_CompanyID
        AND (p_AccountID = 0 OR ( p_AccountID != 0 AND inv.AccountID = p_AccountID))
        AND (p_InvoiceNumber = '' OR ( p_InvoiceNumber != '' AND inv.InvoiceNumber = p_InvoiceNumber))
        AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
        AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
        AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND inv.InvoiceType = p_InvoiceType))
        AND (p_InvoiceStatus = '' OR ( p_InvoiceStatus != '' AND inv.InvoiceStatus = p_InvoiceStatus))
        AND (p_zerovalueinvoice = 0 OR ( p_zerovalueinvoice = 1 AND inv.GrandTotal > 0));
    END IF;
    IF p_isExport = 1
    THEN

        SELECT ac.AccountName ,
        ( CONCAT(ltrim(rtrim(it.InvoiceNumberPrefix)), ltrim(rtrim(inv.InvoiceNumber)))) as InvoiceNumber,
        inv.IssueDate,
        ROUND(inv.GrandTotal,v_Round_) as GrandTotal,
        CONCAT(ROUND(format((select ROUND(IFNULL(sum(p.Amount),0),v_Round_) from tblPayment p where REPLACE(p.InvoiceNo,'-','') = ( CONCAT(ltrim(rtrim(REPLACE(it.InvoiceNumberPrefix,'-',''))), ltrim(rtrim(inv.InvoiceNumber)))) AND p.Status = 'Approved' AND p.AccountID = inv.AccountID),6),v_Round_),'/',ROUND(format((inv.GrandTotal -  (select IFNULL(sum(p.Amount),0) from tblPayment p where REPLACE(p.InvoiceNo,'-','') = ( CONCAT(ltrim(rtrim(REPLACE(it.InvoiceNumberPrefix,'-',''))), ltrim(rtrim(inv.InvoiceNumber)))) AND p.Status = 'Approved' AND p.AccountID = inv.AccountID) ),6),v_Round_)) as `Paid/OS`,
        inv.InvoiceStatus,
        inv.InvoiceType,
        inv.ItemInvoice
        FROM tblInvoice inv
        inner join Ratemanagement3.tblAccount ac on ac.AccountID = inv.AccountID
        left join tblInvoiceTemplate it on ac.InvoiceTemplateID = it.InvoiceTemplateID
        where ac.CompanyID = p_CompanyID
        AND (p_AccountID = 0 OR ( p_AccountID != 0 AND inv.AccountID = p_AccountID))
        AND (p_InvoiceNumber = '' OR ( p_InvoiceNumber != '' AND inv.InvoiceNumber = p_InvoiceNumber))
        AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
        AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
        AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND inv.InvoiceType = p_InvoiceType))
        AND (p_InvoiceStatus = '' OR ( p_InvoiceStatus != '' AND inv.InvoiceStatus = p_InvoiceStatus))
        AND (p_zerovalueinvoice = 0 OR ( p_zerovalueinvoice = 1 AND inv.GrandTotal > 0));

    END IF;
     IF p_isExport = 2
    THEN

        SELECT ac.AccountID ,
        ac.AccountName,
        ( CONCAT(ltrim(rtrim(it.InvoiceNumberPrefix)), ltrim(rtrim(inv.InvoiceNumber)))) as InvoiceNumber,
        inv.IssueDate,
        inv.GrandTotal,
		  CONCAT(ROUND(format((select IFNULL(sum(p.Amount),0) from tblPayment p where REPLACE(p.InvoiceNo,'-','') = ( CONCAT(ltrim(rtrim(REPLACE(it.InvoiceNumberPrefix,'-',''))), ltrim(rtrim(inv.InvoiceNumber)))) AND p.Status = 'Approved' AND p.AccountID = inv.AccountID),6),v_Round_),'/',format((inv.GrandTotal -  (select IFNULL(sum(p.Amount),0) from tblPayment p where REPLACE(p.InvoiceNo,'-','') = ( CONCAT(ltrim(rtrim(REPLACE(it.InvoiceNumberPrefix,'-',''))), ltrim(rtrim(inv.InvoiceNumber)))) AND p.Status = 'Approved' AND p.AccountID = inv.AccountID) ),6)) as `Paid/OS`,
        inv.InvoiceStatus,
        inv.InvoiceType,
        inv.ItemInvoice,
        inv.InvoiceID
        FROM tblInvoice inv
        inner join Ratemanagement3.tblAccount ac on ac.AccountID = inv.AccountID
        left join tblInvoiceTemplate it on ac.InvoiceTemplateID = it.InvoiceTemplateID
        where ac.CompanyID = p_CompanyID
        AND (p_AccountID = 0 OR ( p_AccountID != 0 AND inv.AccountID = p_AccountID))
        AND (p_InvoiceNumber = '' OR ( p_InvoiceNumber != '' AND inv.InvoiceNumber = p_InvoiceNumber))
        AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
        AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
        AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND inv.InvoiceType = p_InvoiceType))
        AND (p_InvoiceStatus = '' OR ( p_InvoiceStatus != '' AND inv.InvoiceStatus = p_InvoiceStatus))
        AND (p_zerovalueinvoice = 0 OR ( p_zerovalueinvoice = 1 AND inv.GrandTotal > 0));
    END IF;

    IF p_sageExport =1 OR p_sageExport =2
    THEN
    		 -- mark as paid invoice that are sage export
        IF p_sageExport = 2
        THEN 
        UPDATE tblInvoice  inv
        INNER JOIN Ratemanagement3.tblAccount ac
          ON ac.AccountID = inv.AccountID
        INNER JOIN Ratemanagement3.tblCurrency c
          ON c.CurrencyId = ac.CurrencyId
        SET InvoiceStatus = 'paid' 
        WHERE ac.CompanyID = p_CompanyID
                AND (p_AccountID = 0 OR ( p_AccountID != 0 AND inv.AccountID = p_AccountID))
                AND (p_InvoiceNumber = '' OR ( p_InvoiceNumber != '' AND inv.InvoiceNumber = p_InvoiceNumber))
                AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
			       AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
                AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND inv.InvoiceType = p_InvoiceType))
                AND (p_InvoiceStatus = '' OR ( p_InvoiceStatus != '' AND inv.InvoiceStatus = p_InvoiceStatus))
                AND (p_zerovalueinvoice = 0 OR ( p_zerovalueinvoice = 1 AND inv.GrandTotal > 0))
                AND (p_InvoiceID = '' OR (p_InvoiceID !='' AND FIND_IN_SET (inv.InvoiceID,p_InvoiceID)!= 0 ));
        END IF; 
        SELECT
          Number AS AccountNumber,
          DATE_FORMAT(DATE_ADD(inv.IssueDate,INTERVAL ac.PaymentDueInDays DAY), '%Y-%m-%d') AS DueDate,
          ROUND(GrandTotal,v_Round_) AS GoodsValueInAccountCurrency,
          ROUND(GrandTotal,v_Round_) AS SalControlValueInBaseCurrency,
          1 AS DocumentToBaseCurrencyRate,
          1 AS DocumentToAccountCurrencyRate,
          DATE_FORMAT(IssueDate, '%Y-%m-%d') AS PostedDate,
          inv.InvoiceNumber AS TransactionReference,
          '' AS SecondReference,
          '' AS Source,
          4 AS SYSTraderTranType, -- 4 - Sales invoice (SI)
          DATE_FORMAT((select PaymentDate from tblPayment p where REPLACE(p.InvoiceNo,'-','') = ( CONCAT(ltrim(rtrim(REPLACE(it.InvoiceNumberPrefix,'-',''))), ltrim(rtrim(inv.InvoiceNumber)))) AND p.Status = 'Approved' AND p.AccountID = inv.AccountID order by PaymentID desc limit 1),'%Y-%m-%d') AS TransactionDate,
          TotalTax AS TaxValue,
          SubTotal AS `NominalAnalysisTransactionValue/1`,
          ac.NominalAnalysisNominalAccountNumber AS `NominalAnalysisNominalAccountNumber/1`,
          'NEON' AS `NominalAnalysisNominalAnalysisNarrative/1`,
          '' AS `NominalAnalysisTransactionAnalysisCode/1`,
          1 AS `TaxAnalysisTaxRate/1`,
          SubTotal AS `TaxAnalysisGoodsValueBeforeDiscount/1`,
          TotalTax as   `TaxAnalysisTaxOnGoodsValue/1`
        FROM tblInvoice inv
        INNER JOIN Ratemanagement3.tblAccount ac
          ON ac.AccountID = inv.AccountID
        INNER JOIN Ratemanagement3.tblCurrency c
          ON c.CurrencyId = ac.CurrencyId
        LEFT JOIN tblInvoiceTemplate it 
          ON ac.InvoiceTemplateID = it.InvoiceTemplateID        

        WHERE ac.CompanyID = p_CompanyID
                AND (p_AccountID = 0 OR ( p_AccountID != 0 AND inv.AccountID = p_AccountID))
                AND (p_InvoiceNumber = '' OR ( p_InvoiceNumber != '' AND inv.InvoiceNumber = p_InvoiceNumber))
                AND (p_IssueDateStart = '0000-00-00 00:00:00' OR ( p_IssueDateStart != '0000-00-00 00:00:00' AND inv.IssueDate >= p_IssueDateStart))
			       AND (p_IssueDateEnd = '0000-00-00 00:00:00' OR ( p_IssueDateEnd != '0000-00-00 00:00:00' AND inv.IssueDate <= p_IssueDateEnd))
                AND (p_InvoiceType = 0 OR ( p_InvoiceType != 0 AND inv.InvoiceType = p_InvoiceType))
                AND (p_InvoiceStatus = '' OR ( p_InvoiceStatus != '' AND inv.InvoiceStatus = p_InvoiceStatus))
                AND (p_zerovalueinvoice = 0 OR ( p_zerovalueinvoice = 1 AND inv.GrandTotal > 0))
                AND (p_InvoiceID = '' OR (p_InvoiceID !='' AND FIND_IN_SET (inv.InvoiceID,p_InvoiceID)!= 0 ));
    END IF;

 
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END