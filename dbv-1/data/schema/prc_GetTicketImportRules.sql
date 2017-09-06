CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetTicketImportRules`(
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
		SELECT `Title`,`Status`,`TicketImportRuleID` 
		FROM tblTicketImportRule  
		WHERE (CompanyID = p_CompanyID) 			
		ORDER BY
			CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TitleASC') THEN Title
            END ASC,
			CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TitleDESC') THEN Title
            END DESC,
           CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusDESC') THEN Status
            END DESC,
				CASE
               WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusASC') THEN Status
            END ASC				
							                    
			LIMIT p_RowspPage OFFSET v_OffSet_;
				
		SELECT COUNT(*) AS totalcount
		FROM tblTicketImportRule
		WHERE (CompanyID = p_CompanyID);
	END IF;
	
	IF p_isExport = 1
	THEN
	SELECT Title,Description,Status
		FROM tblTicketImportRule  
		WHERE (CompanyID = p_CompanyID);
	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END