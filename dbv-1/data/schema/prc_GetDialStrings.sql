CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetDialStrings`(IN `p_dialstringid` int, IN `p_dialstring` varchar(250), IN `p_chargecode` varchar(250), IN `p_description` varchar(250), IN `p_PageNumber` int, IN `p_RowspPage` int, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` int )
BEGIN

   DECLARE v_OffSet_ int;
   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	           
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
    

    IF p_isExport = 0
    THEN


        SELECT
            DialStringCodeID,
            DialString,
            ChargeCode,
            Description,
            Forbidden
        FROM tblDialStringCode
        WHERE  (DialStringID = p_dialstringid)
			AND (p_dialstring IS NULL OR DialString LIKE REPLACE(p_dialstring, '*', '%'))
            AND (p_chargecode IS NULL OR ChargeCode LIKE REPLACE(p_chargecode, '*', '%'))
            AND (p_description IS NULL OR Description LIKE REPLACE(p_description, '*', '%'))            
        ORDER BY
            CASE
                WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DialStringDESC') THEN DialString
            END DESC,
            CASE
                WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DialStringASC') THEN DialString
            END ASC,
            CASE
                WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ChargeCodeDESC') THEN ChargeCode
            END DESC,
            CASE
                WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ChargeCodeASC') THEN ChargeCode
            END ASC,
            CASE
                WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN Description
            END DESC,
            CASE
                WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN Description
            END ASC,
            CASE
                WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ForbiddenDESC') THEN Forbidden
            END DESC,
            CASE
                WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ForbiddenASC') THEN Forbidden
            END ASC
        LIMIT p_RowspPage OFFSET v_OffSet_;

        SELECT
            COUNT(DialStringCodeID) AS totalcount
        FROM tblDialStringCode
        WHERE  (DialStringID = p_dialstringid)
			AND (p_dialstring IS NULL OR DialString LIKE REPLACE(p_dialstring, '*', '%'))
            AND (p_chargecode IS NULL OR ChargeCode LIKE REPLACE(p_chargecode, '*', '%'))
            AND (p_description IS NULL OR Description LIKE REPLACE(p_description, '*', '%'));

    END IF;

    IF p_isExport = 1
    THEN

        SELECT
            DialString,
            ChargeCode,
            Description,
            Forbidden
        FROM tblDialStringCode
        WHERE  (DialStringID = p_dialstringid)
			AND (p_dialstring IS NULL OR DialString LIKE REPLACE(p_dialstring, '*', '%'))
            AND (p_chargecode IS NULL OR ChargeCode LIKE REPLACE(p_chargecode, '*', '%'))
            AND (p_description IS NULL OR Description LIKE REPLACE(p_description, '*', '%'));   

    END IF;
    IF p_isExport = 2
    THEN

        SELECT
            DialStringCodeID,
            DialString,
            ChargeCode,
            Description,
            Forbidden
        FROM tblDialStringCode
        WHERE  (DialStringID = p_dialstringid)
			AND (p_dialstring IS NULL OR DialString LIKE REPLACE(p_dialstring, '*', '%'))
            AND (p_chargecode IS NULL OR ChargeCode LIKE REPLACE(p_chargecode, '*', '%'))
            AND (p_description IS NULL OR Description LIKE REPLACE(p_description, '*', '%'));   

    END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END