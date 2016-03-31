CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetInvoiceTransactionLog`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_InvoiceID` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` int)
BEGIN
    
    DECLARE v_OffSet_ int;
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;



    IF p_isExport = 0
    THEN

         
            SELECT
                `Transaction`,
                tl.Notes,
                tl.created_at,
                tl.Amount,
                tl.Status,
                inv.InvoiceID,
                inv.InvoiceNumber
            FROM tblInvoice inv
            INNER JOIN Ratemanagement3.tblAccount ac
                ON ac.AccountID = inv.AccountID
            INNER JOIN tblTransactionLog tl
                ON tl.InvoiceID = inv.InvoiceID
                AND tl.CompanyID = inv.CompanyID
            WHERE ac.CompanyID = p_CompanyID
            AND (p_AccountID = 0
            OR (p_AccountID != 0
            AND inv.AccountID = p_AccountID))
            AND (p_InvoiceID = ''
            OR (p_InvoiceID != ''
            AND inv.InvoiceID = p_InvoiceID)) 
            ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AmountDESC') THEN tl.Amount
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AmountASC') THEN tl.Amount
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusDESC') THEN tl.Status
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusASC') THEN tl.Status
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN tl.created_at
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN tl.created_at
                END ASC
        LIMIT p_RowspPage OFFSET v_OffSet_;

        SELECT
            COUNT(*) AS totalcount
        FROM tblInvoice inv
        INNER JOIN Ratemanagement3.tblAccount ac
            ON ac.AccountID = inv.AccountID
        INNER JOIN tblTransactionLog tl
            ON tl.InvoiceID = inv.InvoiceID
            AND tl.CompanyID = inv.CompanyID
        WHERE ac.CompanyID = p_CompanyID
        AND (p_AccountID = 0
        OR (p_AccountID != 0
        AND inv.AccountID = p_AccountID))
        AND (p_InvoiceID = ''
        OR (p_InvoiceID != ''
        AND inv.InvoiceID = p_InvoiceID));

    END IF;
    
    IF p_isExport = 1
    THEN

        SELECT
				`Transaction`,
            tl.Notes,
            tl.created_at,
            tl.Amount,
            tl.Status,
            inv.InvoiceID
        FROM tblInvoice inv
        INNER JOIN Ratemanagement3.tblAccount ac
            ON ac.AccountID = inv.AccountID
        INNER JOIN tblTransactionLog tl
            ON tl.InvoiceID = inv.InvoiceID
            AND tl.CompanyID = inv.CompanyID
        WHERE ac.CompanyID = p_CompanyID
        AND (p_AccountID = 0
        OR (p_AccountID != 0
        AND inv.AccountID = p_AccountID))
        AND (p_InvoiceID = ''
        OR (p_InvoiceID != ''
        AND inv.InvoiceID = p_InvoiceID));
		  
	END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END