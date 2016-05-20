CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getInvoiceUsage`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_GatewayID` INT, IN `p_StartDate` DATETIME, IN `p_EndDate` DATETIME, IN `p_ShowZeroCall` INT)
BEGIN
    
	DECLARE v_InvoiceCount_ INT; 
	DECLARE v_BillingTime_ INT; 
	DECLARE v_CDRType_ INT; 
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	

	
	SELECT BillingTime INTO v_BillingTime_
	FROM LocalRatemanagement.tblCompanyGateway cg
	INNER JOIN tblGatewayAccount ga ON ga.CompanyGatewayID = cg.CompanyGatewayID
	WHERE AccountID = p_AccountID AND (p_GatewayID = '' OR ga.CompanyGatewayID = p_GatewayID)
	LIMIT 1;
	
	SET v_BillingTime_ = IFNULL(v_BillingTime_,1); 
	
	CALL fnUsageDetail(p_CompanyID,p_AccountID,p_GatewayID,p_StartDate,p_EndDate,0,1,v_BillingTime_,'','','',0); 

	Select CDRType  INTO v_CDRType_ from  LocalRatemanagement.tblAccount where AccountID = p_AccountID;


            
        
            
    IF( v_CDRType_ = 2) -- Summery
    Then

        SELECT
            area_prefix AS AreaPrefix,
            max(Trunk) as Trunk,
            (SELECT 
                Country
            FROM Ratemanagement3.tblRate r
            INNER JOIN Ratemanagement3.tblCountry c
                ON c.CountryID = r.CountryID
            WHERE  r.Code = ud.area_prefix limit 1)
            AS Country,
            (SELECT Description
            FROM Ratemanagement3.tblRate r
            WHERE  r.Code = ud.area_prefix limit 1 )
            AS Description,
            COUNT(UsageDetailID) AS NoOfCalls,
            CONCAT( FLOOR(SUM(duration ) / 60), ':' , SUM(duration ) % 60) AS Duration,
            CONCAT( FLOOR(SUM(billed_duration ) / 60),':' , SUM(billed_duration ) % 60) AS BillDuration,
            SUM(cost) AS TotalCharges,
            SUM(duration ) as DurationInSec,
            SUM(billed_duration ) as BillDurationInSec

        FROM tmp_tblUsageDetails_ ud
        GROUP BY ud.area_prefix,
                 ud.AccountID;

         
    ELSE
        
        
            select
            trunk,
            area_prefix,
            cli,
            cld,
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