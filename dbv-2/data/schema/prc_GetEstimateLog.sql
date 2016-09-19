CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetEstimateLog`(IN `p_CompanyID` INT, IN `p_EstimateID` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(50), IN `p_isExport` INT)
BEGIN

	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
    IF p_isExport = 0
    THEN

       
            SELECT
                es.EstimateNumber,
                el.Note,
                el.EstimateLogStatus,
                el.created_at,                
                es.EstimateID                
            FROM tblEstimate es
            INNER JOIN LocalRatemanagement.tblAccount ac
                ON ac.AccountID = es.AccountID
            INNER JOIN tblEstimateLog el
                ON el.EstimateID = es.EstimateID
            WHERE ac.CompanyID = p_CompanyID
            AND (p_EstimateID = '' 
            OR (p_EstimateID != ''
            AND es.EstimateID = p_EstimateID))
             ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EstimateLogStatusDESC') THEN el.EstimateLogStatus
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EstimateLogStatusASC') THEN el.EstimateLogStatus
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN el.created_at
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN el.created_at
                END ASC
					LIMIT p_RowspPage OFFSET v_OffSet_;

        SELECT
            COUNT(*) AS totalcount
        FROM tblEstimate es
        INNER JOIN LocalRatemanagement.tblAccount ac
            ON ac.AccountID = es.AccountID
        INNER JOIN tblEstimateLog el
            ON el.EstimateID = es.EstimateID
        WHERE ac.CompanyID = p_CompanyID
        AND (p_EstimateID = ''
        OR (p_EstimateID != ''
        AND es.EstimateID = p_EstimateID));

    END IF;
    IF p_isExport = 1
    THEN

        SELECT
            el.Note,
            el.created_at,
            el.EstimateLogStatus,
            es.EstimateNumber
        FROM tblEstimate es
        INNER JOIN LocalRatemanagement.tblAccount ac
            ON ac.AccountID = es.AccountID
        INNER JOIN tblEstimateLog el
            ON el.EstimateID = es.EstimateID
        WHERE ac.CompanyID = p_CompanyID
        AND (p_EstimateID = ''
        OR (p_EstimateID != ''
        AND es.EstimateID = p_EstimateID));


    END IF;
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END