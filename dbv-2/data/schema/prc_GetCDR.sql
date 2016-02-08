CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetCDR`(IN `p_company_id` INT, IN `p_CompanyGatewayID` INT, IN `p_start_date` DATETIME, IN `p_end_date` DATETIME, IN `p_AccountID` INT , IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT)
BEGIN 

	   DECLARE v_OffSet_ int;
	   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
    
    Call fnUsageDetail(p_company_id,p_AccountID,p_CompanyGatewayID,p_start_date,p_end_date,0,1,1);


   SELECT
        AccountName,        
        connect_time,
        disconnect_time,        
        duration,
        cost,
        cli,
        cld,
        AccountID,
        p_CompanyGatewayID as CompanyGatewayID,
        p_start_date as StartDate,
        p_end_date as EndDate  from(
        SELECT
        		Distinct
            uh.AccountName as AccountName,           
            uh.connect_time,
            uh.disconnect_time,
            uh.duration,
            uh.cli,
            uh.cld,
				format(uh.cost,6) as cost,
				AccountID
            
        
        FROM tmp_tblUsageDetails_ uh
         
        

    ) AS TBL  
    ORDER BY
            CASE WHEN (p_lSortCol = 'AccountName' AND p_SortOrder = 'DESC') 
                THEN AccountName
            END DESC,
            CASE WHEN (p_lSortCol = 'AccountName' AND p_SortOrder = 'ASC') 
                THEN AccountName
            END ASC, 
            CASE WHEN (p_lSortCol = 'connect_time' AND p_SortOrder = 'DESC') 
                THEN connect_time
            END DESC,
            CASE WHEN (p_lSortCol = 'connect_time' AND p_SortOrder = 'ASC') 
                THEN connect_time
            END ASC,
            CASE WHEN (p_lSortCol = 'disconnect_time' AND p_SortOrder = 'DESC') 
                THEN disconnect_time
            END DESC,
            CASE WHEN (p_lSortCol = 'disconnect_time' AND p_SortOrder = 'ASC') 
                THEN disconnect_time
            END ASC,
            CASE WHEN (p_lSortCol = 'duration' AND p_SortOrder = 'DESC') 
                THEN duration
            END DESC,
            CASE WHEN (p_lSortCol = 'duration' AND p_SortOrder = 'ASC') 
                THEN duration
            END ASC,
            CASE WHEN (p_lSortCol = 'cost' AND p_SortOrder = 'DESC') 
                THEN cost
            END DESC,
            CASE WHEN (p_lSortCol = 'cost' AND p_SortOrder = 'ASC') 
                THEN cost
            END ASC,
            CASE WHEN (p_lSortCol = 'cli' AND p_SortOrder = 'DESC') 
                THEN cli
            END DESC,
            CASE WHEN (p_lSortCol = 'cli' AND p_SortOrder = 'ASC') 
                THEN cli
            END ASC,
            CASE WHEN (p_lSortCol = 'cld' AND p_SortOrder = 'DESC') 
                THEN cld
            END DESC,
            CASE WHEN (p_lSortCol = 'cld' AND p_SortOrder = 'ASC') 
                THEN cld
            END ASC


	 LIMIT p_RowspPage OFFSET v_OffSet_;


    SELECT
        COUNT(*) AS totalcount
    FROM (
    select Distinct uh.AccountID,connect_time,disconnect_time
    FROM tmp_tblUsageDetails_ uh
    ) AS TBL2;
   
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END