CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getInvoiceUsage`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_GatewayID` INT, IN `p_StartDate` DATETIME, IN `p_EndDate` DATETIME, IN `p_ShowZeroCall` INT)
BEGIN
    
	DECLARE v_InvoiceCount_ INT; 
	DECLARE v_BillingTime_ INT; 
	DECLARE v_CDRType_ INT; 
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	

	
	SELECT BillingTime INTO v_BillingTime_
	FROM NeonRMDev.tblCompanyGateway cg
	INNER JOIN tblGatewayAccount ga ON ga.CompanyGatewayID = cg.CompanyGatewayID
	WHERE AccountID = p_AccountID AND (p_GatewayID = '' OR ga.CompanyGatewayID = p_GatewayID)
	LIMIT 1;
	
	SET v_BillingTime_ = IFNULL(v_BillingTime_,1); 
	
	CALL fnUsageDetail(p_CompanyID,p_AccountID,p_GatewayID,p_StartDate,p_EndDate,0,1,v_BillingTime_,'','','',0); 

	SELECT CDRType  INTO v_CDRType_ FROM NeonRMDev.tblAccountBilling ab INNER JOIN  NeonRMDev.tblBillingClass b  ON b.BillingClassID = ab.BillingClassID WHERE ab.AccountID = p_AccountID;


            
        
            
    IF( v_CDRType_ = 2) -- Summery
    Then

        SELECT
            area_prefix AS AreaPrefix,
            Trunk,
            (SELECT 
                Country
            FROM NeonRMDev.tblRate r
            INNER JOIN NeonRMDev.tblCountry c
                ON c.CountryID = r.CountryID
            WHERE  r.Code = ud.area_prefix limit 1)
            AS Country,
            (SELECT Description
            FROM NeonRMDev.tblRate r
            WHERE  r.Code = ud.area_prefix limit 1 )
            AS Description,
            COUNT(UsageDetailID) AS NoOfCalls,
            CONCAT( FLOOR(SUM(duration ) / 60), ':' , SUM(duration ) % 60) AS Duration,
            CONCAT( FLOOR(SUM(billed_duration ) / 60),':' , SUM(billed_duration ) % 60) AS BillDuration,
            SUM(cost) AS TotalCharges,
            SUM(duration ) as DurationInSec,
            SUM(billed_duration ) as BillDurationInSec

        FROM tmp_tblUsageDetails_ ud
        GROUP BY ud.area_prefix,ud.Trunk,ud.AccountID;

         
    ELSE
        
        
            select
            trunk,
            area_prefix,
            concat("'",cli) as cli,
            concat("'",cld) as cld,
            connect_time,
            disconnect_time,
            billed_duration,
            cost
            FROM tmp_tblUsageDetails_ ud
            WHERE
             ((p_ShowZeroCall =0 and ud.cost >0 )or (p_ShowZeroCall =1 and ud.cost >= 0));
            
    END IF;    
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END