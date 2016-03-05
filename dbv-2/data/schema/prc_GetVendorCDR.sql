CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetVendorCDR`(IN `p_CompanyID` INT, IN `p_CompanyGatewayID` INT, IN `p_start_date` DATETIME, IN `p_end_date` DATETIME, IN `p_AccountID` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT)
BEGIN 

	   DECLARE v_OffSet_ int;
	   DECLARE v_BillingTime_ int;
	   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
		SELECT BillingTime INTO v_BillingTime_
		FROM Ratemanagement3.tblCompanyGateway cg
		INNER JOIN tblGatewayAccount ga ON ga.CompanyGatewayID = cg.CompanyGatewayID
		WHERE AccountID = p_AccountID AND (p_CompanyGatewayID = '' OR ga.CompanyGatewayID = p_CompanyGatewayID)
		LIMIT 1;
		
		SET v_BillingTime_ = IFNULL(v_BillingTime_,1); 
    
    Call fnVendorUsageDetail(p_CompanyID,p_AccountID,p_CompanyGatewayID,p_start_date,p_end_date,0,1,v_BillingTime_);

	IF p_isExport = 0
	THEN 
	
   SELECT
        AccountName,        
        connect_time,
        disconnect_time,        
        billed_duration,
        selling_cost,
        buying_cost,
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
            uh.billed_duration,
            uh.cli,
            uh.cld,
				format(uh.selling_cost,6) as selling_cost,
				format(uh.buying_cost ,6) as buying_cost,
				AccountID
            
        
        FROM tmp_tblVendorUsageDetails_ uh
         
        

    ) AS TBL  
    ORDER BY
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN AccountName
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN AccountName
            END ASC,
            
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'connect_timeDESC') THEN connect_time
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'connect_timeASC') THEN connect_time
            END ASC,
            
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'disconnect_timeDESC') THEN disconnect_time
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'disconnect_timeASC') THEN disconnect_time
            END ASC,
            
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'billed_durationDESC') THEN billed_duration
            END DESC,
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'billed_durationASC') THEN billed_duration
            END ASC,
				 
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'selling_costDESC') THEN selling_cost
            END DESC,
         	CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'selling_costASC') THEN selling_cost
            END ASC,
            
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'cliDESC') THEN cli
            END DESC,
         	CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'cliASC') THEN cli
            END ASC,
            
            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'cldDESC') THEN cld
            END DESC,
         	CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'cldASC') THEN cld
            END ASC


	 LIMIT p_RowspPage OFFSET v_OffSet_;
	 
	  SELECT
        COUNT(*) AS totalcount
    FROM (
    select Distinct uh.AccountID,connect_time,disconnect_time
    FROM tmp_tblVendorUsageDetails_ uh
    ) AS TBL2;
    
    END IF;
	
	IF p_isExport = 1
		THEN
		
			SELECT
		        AccountName,        
		        connect_time,
		        disconnect_time,        
		        billed_duration,
		        selling_cost,
		        cli,
		        cld
			from(
		        SELECT
		        		Distinct
		            uh.AccountName as AccountName,           
		            uh.connect_time,
		            uh.disconnect_time,
		            uh.billed_duration,
		            uh.cli,
		            uh.cld,
						format(uh.selling_cost,6) as selling_cost,
						AccountID
		        FROM tmp_tblVendorUsageDetails_ uh
		    ) AS TBL;
		
		END IF;

   
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END