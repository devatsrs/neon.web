CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetInvoiceLog`(IN `p_CompanyID` INT, IN `p_InvoiceID` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(50), IN `p_isExport` INT)
BEGIN


	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	            
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;


    IF p_isExport = 0
    THEN

       
            SELECT
                inv.InvoiceNumber,
                tl.Note,
                tl.InvoiceLogStatus,
                tl.created_at,                
                inv.InvoiceID
                
            FROM tblInvoice inv
            INNER JOIN Ratemanagement3.tblAccount ac
                ON ac.AccountID = inv.AccountID
            INNER JOIN tblInvoiceLog tl
                ON tl.InvoiceID = inv.InvoiceID
            WHERE ac.CompanyID = p_CompanyID
            AND (p_InvoiceID = '' 
            OR (p_InvoiceID != ''
            AND inv.InvoiceID = p_InvoiceID))
             ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceLogStatusDESC') THEN tl.InvoiceLogStatus
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'InvoiceLogStatusASC') THEN tl.InvoiceLogStatus
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
        INNER JOIN tblInvoiceLog tl
            ON tl.InvoiceID = inv.InvoiceID
        WHERE ac.CompanyID = p_CompanyID
        AND (p_InvoiceID = ''
        OR (p_InvoiceID != ''
        AND inv.InvoiceID = p_InvoiceID));

    END IF;
    IF p_isExport = 1
    THEN

        SELECT
            tl.Note,
            tl.created_at,
            tl.InvoiceLogStatus,
            inv.InvoiceNumber
        FROM tblInvoice inv
        INNER JOIN Ratemanagement3.tblAccount ac
            ON ac.AccountID = inv.AccountID
        INNER JOIN tblInvoiceLog tl
            ON tl.InvoiceID = inv.InvoiceID
        WHERE ac.CompanyID = p_CompanyID
        AND (p_InvoiceID = ''
        OR (p_InvoiceID != ''
        AND inv.InvoiceID = p_InvoiceID));


    END IF;
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END