CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetCDR`(IN `p_company_id` INT, IN `p_CompanyGatewayID` INT, IN `p_start_date` DATETIME, IN `p_end_date` DATETIME, IN `p_AccountID` INT , IN `p_CDRType` CHAR(1), IN `p_CLI` VARCHAR(50), IN `p_CLD` VARCHAR(50), IN `p_zerovaluecost` INT, IN `p_CurrencyID` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT)
BEGIN 

	   DECLARE v_OffSet_ int;
	   DECLARE v_BillingTime_ INT;
  	   DECLARE v_Round_ INT;
      DECLARE v_CurrencyCode_ VARCHAR(50);
	   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
      SELECT cs.Value INTO v_Round_ FROM LocalRatemanagement.tblCompanySetting cs WHERE cs.`Key` = 'RoundChargesAmount' AND cs.CompanyID = p_company_id;
	 	SELECT cr.Symbol INTO v_CurrencyCode_ from LocalRatemanagement.tblCurrency cr where cr.CurrencyId =p_CurrencyID;
		SELECT BillingTime INTO v_BillingTime_
		FROM LocalRatemanagement.tblCompanyGateway cg
		INNER JOIN tblGatewayAccount ga ON ga.CompanyGatewayID = cg.CompanyGatewayID
		WHERE AccountID = p_AccountID AND (p_CompanyGatewayID = '' OR ga.CompanyGatewayID = p_CompanyGatewayID)
		LIMIT 1;
		
		SET v_BillingTime_ = IFNULL(v_BillingTime_,1); 
    
    
    	Call fnUsageDetail(p_company_id,p_AccountID,p_CompanyGatewayID,p_start_date,p_end_date,0,1,v_BillingTime_,p_CDRType,p_CLI,p_CLD,p_zerovaluecost);


	IF p_isExport = 0
	THEN 
		   SELECT
		        uh.AccountName,        
		        uh.connect_time,
		        uh.disconnect_time,        
		        uh.billed_duration,
		        CONCAT(IFNULL(v_CurrencyCode_,''),format(uh.cost,6)) AS cost,
		        uh.cli,
		        uh.cld,
		        uh.AccountID,
		        p_CompanyGatewayID as CompanyGatewayID,
		        p_start_date as StartDate,
		        p_end_date as EndDate,
				uh.is_inbound as CDRType  from
		        tmp_tblUsageDetails_ uh
		        INNER JOIN LocalRatemanagement.tblAccount a
			ON uh.AccountID = a.AccountID
			where  (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)		      
		    ORDER BY
		    			CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN uh.AccountName
		            END DESC,
		            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN uh.AccountName
		            END ASC,
		            
		            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'connect_timeDESC') THEN uh.connect_time
		            END DESC,
		            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'connect_timeASC') THEN uh.connect_time
		            END ASC,
		            
		            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'disconnect_timeDESC') THEN uh.disconnect_time
		            END DESC,
		            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'disconnect_timeASC') THEN uh.disconnect_time
		            END ASC,
		            
		            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'billed_durationDESC') THEN uh.billed_duration
		            END DESC,
		            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'billed_durationASC') THEN uh.billed_duration
		            END ASC,
						 
		            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'costDESC') THEN uh.cost
		            END DESC,
		         	CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'costASC') THEN uh.cost
		            END ASC,
		            
		            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'cliDESC') THEN uh.cli
		            END DESC,
		         	CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'cliASC') THEN uh.cli
		            END ASC,
		            
		            CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'cldDESC') THEN uh.cld
		            END DESC,
		         	CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'cldASC') THEN uh.cld
		            END ASC
		
			 LIMIT p_RowspPage OFFSET v_OffSet_;
		
		
		    SELECT
		       COUNT(*) AS totalcount,ROUND(sum(billed_duration),v_Round_) as total_duration,ROUND(sum(cost),v_Round_) as total_cost,v_CurrencyCode_ as CurrencyCode
		    FROM  tmp_tblUsageDetails_ uh
		      INNER JOIN LocalRatemanagement.tblAccount a
			ON uh.AccountID = a.AccountID
			where  (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID);	    
		
		END IF;
		
		IF p_isExport = 1
		THEN
		
			SELECT
		        uh.AccountName,        
		        uh.connect_time,
		        uh.disconnect_time,        
		        uh.billed_duration as duration,
		        CONCAT(IFNULL(v_CurrencyCode_,''),format(uh.cost,6)) AS cost,
		        uh.cli,
		        uh.cld,
		        uh.is_inbound		        
		        FROM tmp_tblUsageDetails_ uh
		          INNER JOIN LocalRatemanagement.tblAccount a
			ON uh.AccountID = a.AccountID
			where  (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID);		
		END IF;
   
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END