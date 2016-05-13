CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetVendorCDR`(IN `p_CompanyID` INT, IN `p_CompanyGatewayID` INT, IN `p_start_date` DATETIME, IN `p_end_date` DATETIME, IN `p_AccountID` INT, IN `p_CLI` VARCHAR(50), IN `p_CLD` VARCHAR(50), IN `p_ZeroValueBuyingCost` INT, IN `p_CurrencyID` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT)
BEGIN 

   	DECLARE v_OffSet_ int;
   	DECLARE v_BillingTime_ int;
   	DECLARE v_Round_ INT;
   	DECLARE v_CurrencyCode_ VARCHAR(50);
   	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	SELECT cs.Value INTO v_Round_ FROM LocalRatemanagement.tblCompanySetting cs WHERE cs.`Key` = 'RoundChargesAmount' AND cs.CompanyID = p_CompanyID;
	SELECT cr.Symbol INTO v_CurrencyCode_ from LocalRatemanagement.tblCurrency cr where cr.CurrencyId =p_CurrencyID;
	SELECT BillingTime INTO v_BillingTime_
	FROM LocalRatemanagement.tblCompanyGateway cg
	INNER JOIN tblGatewayAccount ga ON ga.CompanyGatewayID = cg.CompanyGatewayID
	WHERE AccountID = p_AccountID AND (p_CompanyGatewayID = '' OR ga.CompanyGatewayID = p_CompanyGatewayID)
	LIMIT 1;
	
	SET v_BillingTime_ = IFNULL(v_BillingTime_,1); 
    
    Call fnVendorUsageDetail(p_CompanyID,p_AccountID,p_CompanyGatewayID,p_start_date,p_end_date,0,1,v_BillingTime_,p_CLI,p_CLD,p_ZeroValueBuyingCost,p_CurrencyID);

	IF p_isExport = 0
	THEN 
	
   SELECT
        AccountName,        
        connect_time,
        disconnect_time,        
        billed_duration,
        buying_cost,
        cli,
        cld,
        AccountID,
        p_CompanyGatewayID as CompanyGatewayID,
        p_start_date as StartDate,
        p_end_date as EndDate,
		v_CurrencyCode_ as CurrencyCode		from(
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
        COUNT(*) AS totalcount,ROUND(sum(billed_duration),v_Round_) as total_billed_duration,concat(IFNULL(v_CurrencyCode_,''),ROUND(sum(buying_cost),v_Round_)) as total_cost
    FROM (
    select Distinct uh.AccountID,connect_time,disconnect_time,uh.billed_duration,uh.buying_cost
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
						concat(IFNULL(v_CurrencyCode_,''),format(uh.selling_cost,6)) as selling_cost,
						AccountID
		        FROM tmp_tblVendorUsageDetails_ uh
		    ) AS TBL;
		
		END IF;
   
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END