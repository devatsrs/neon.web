CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetTicketSLA`(
	IN `p_CompanyID` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(30),
	IN `p_SortOrder` VARCHAR(10),
	IN `p_isExport` INT
)
BEGIN

DECLARE v_OffSet_ int;

   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
 
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	 
	
	IF p_isExport = 0
	THEN
		SELECT Name,Description,TicketSlaID,IsDefault 
		FROM tblTicketSla  
		WHERE (CompanyID = p_CompanyID) 			
		ORDER BY
				CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameASC') THEN Name
            END ASC,
				CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameDESC') THEN Name
            END DESC,
           CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN Description
            END DESC,
				CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN Description
            END ASC				
							                    
			LIMIT p_RowspPage OFFSET v_OffSet_;
				
		SELECT COUNT(*) AS totalcount
		FROM tblTicketSla
		WHERE (CompanyID = p_CompanyID);
	END IF;
	
	IF p_isExport = 1
	THEN
	SELECT Name,Description,IsDefault as DefaultData
		FROM tblTicketSla  
		WHERE (CompanyID = p_CompanyID);
	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END