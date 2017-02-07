CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetRecurringInvoiceLog`(
	IN `p_CompanyID` INT,
	IN `p_RecurringInvoiceID` INT,
	IN `p_Status` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(50),
	IN `p_isExport` INT






)
BEGIN
	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;         
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
   IF p_isExport = 0
    THEN
      SELECT
          rinvlg.Note,
          rinvlg.RecurringInvoiceLogStatus,
          rinvlg.created_at,                
          inv.RecurringInvoiceID
          
      FROM tblRecurringInvoice inv
      INNER JOIN NeonRMDev.tblAccount ac
          ON ac.AccountID = inv.AccountID
      INNER JOIN tblRecurringInvoiceLog rinvlg
          ON rinvlg.RecurringInvoiceID = inv.RecurringInvoiceID
      WHERE ac.CompanyID = p_CompanyID
      AND (inv.RecurringInvoiceID = p_RecurringInvoiceID)
      AND (p_Status=0 OR rinvlg.RecurringInvoiceLogStatus=p_Status)
       ORDER BY
          CASE
              WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RecurringInvoiceLogStatusDESC') THEN rinvlg.RecurringInvoiceLogStatus
          END DESC,
          CASE
              WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RecurringInvoiceLogStatusASC') THEN rinvlg.RecurringInvoiceLogStatus
          END ASC,
          CASE
              WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN rinvlg.created_at
          END DESC,
          CASE
              WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN rinvlg.created_at
          END ASC
			LIMIT p_RowspPage OFFSET v_OffSet_;

     SELECT
         COUNT(*) AS totalcount
     FROM tblRecurringInvoice inv
      INNER JOIN NeonRMDev.tblAccount ac
          ON ac.AccountID = inv.AccountID
      INNER JOIN tblRecurringInvoiceLog rinvlg
          ON rinvlg.RecurringInvoiceID = inv.RecurringInvoiceID
      WHERE ac.CompanyID = p_CompanyID
      AND (inv.RecurringInvoiceID = p_RecurringInvoiceID)
      AND (p_Status=0 OR rinvlg.RecurringInvoiceLogStatus=p_Status);
    END IF;
    IF p_isExport = 1
    THEN
     SELECT
         rinvlg.Note,
         rinvlg.created_at,
         rinvlg.InvoiceLogStatus,
         inv.InvoiceNumber
     FROM tblRecurringInvoice inv
      INNER JOIN NeonRMDev.tblAccount ac
          ON ac.AccountID = inv.AccountID
      INNER JOIN tblRecurringInvoiceLog rinvlg
          ON rinvlg.RecurringInvoiceID = inv.RecurringInvoiceID
      WHERE ac.CompanyID = p_CompanyID
      AND (inv.RecurringInvoiceID = p_RecurringInvoiceID)
      AND (p_Status=0 OR rinvlg.RecurringInvoiceLogStatus=p_Status);
    END IF;
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END