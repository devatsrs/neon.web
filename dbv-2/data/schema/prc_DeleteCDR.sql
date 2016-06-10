CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_DeleteCDR`(IN `p_CompanyID` INT, IN `p_GatewayID` INT, IN `p_StartDate` DATETIME, IN `p_EndDate` DATETIME, IN `p_AccountID` INT, IN `p_CDRType` CHAR(1), IN `p_CLI` VARCHAR(50), IN `p_CLD` VARCHAR(50), IN `p_zerovaluecost` INT, IN `p_CurrencyID` INT)
BEGIN

    DECLARE v_BillingTime_ int;
    SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

		SELECT BillingTime INTO v_BillingTime_
		FROM LocalRatemanagement.tblCompanyGateway cg
		INNER JOIN tblGatewayAccount ga ON ga.CompanyGatewayID = cg.CompanyGatewayID
		WHERE AccountID = p_AccountID AND (p_GatewayID = 0 OR ga.CompanyGatewayID = p_GatewayID)
		LIMIT 1;
			
		SET v_BillingTime_ = IFNULL(v_BillingTime_,1);
        
        
        CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageDetail_ AS 
        (

	        SELECT
	        UsageDetailID
	        
	        FROM (SELECT
	            uh.AccountID,
	            a.AccountName,
	            trunk,
	            area_prefix,
	            UsageDetailID,
	            duration,
	            billed_duration,
	            cli,
	            cld,
	            cost,
	            connect_time,
	            disconnect_time
	
			FROM `LocalRMCdr`.tblUsageDetails  ud 
			INNER JOIN `LocalRMCdr`.tblUsageHeader uh
				ON uh.UsageHeaderID = ud.UsageHeaderID
	        INNER JOIN LocalRatemanagement.tblAccount a
	            ON uh.AccountID = a.AccountID
	        WHERE StartDate >= DATE_ADD(p_StartDate,INTERVAL -1 DAY)
			  AND StartDate <= DATE_ADD(p_EndDate,INTERVAL 1 DAY)
	        AND uh.CompanyID = p_CompanyID
	        AND uh.AccountID is not null
	        AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
	        AND (p_GatewayID = 0 OR CompanyGatewayID = p_GatewayID)
	        AND (p_CDRType = '' OR ud.is_inbound = p_CDRType)
	        AND (p_CLI = '' OR cli LIKE REPLACE(p_CLI, '*', '%'))	
			  AND (p_CLD = '' OR cld LIKE REPLACE(p_CLD, '*', '%'))	
			  AND (p_zerovaluecost = 0 OR ( p_zerovaluecost = 1 AND cost > 0))
			  AND (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)	
	        
	        ) tbl
	        WHERE 
	    
	        (v_BillingTime_ =1 and connect_time >= p_StartDate AND connect_time <= p_EndDate)
	        OR 
	        (v_BillingTime_ =2 and disconnect_time >= p_StartDate AND disconnect_time <= p_EndDate)
	        AND billed_duration > 0
        );


		
		 delete ud.*
        From `LocalRMCdr`.tblUsageDetails ud
        inner join tmp_tblUsageDetail_ uds on ud.UsageDetailID = uds.UsageDetailID;
        
        SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END