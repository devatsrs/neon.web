CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_updateVendorPrefixTrunk`(IN `p_CompanyID` int, IN `p_CompanyGatewayID` int, IN `p_processId` varchar(200))
BEGIN
    SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
          -- update trunk with first trunk if not set UseInBilling
    UPDATE tblTempVendorCDR ud
    LEFT JOIN tblGatewayAccount  ga 
        ON ud.GatewayAccountID = ga.GatewayAccountID
        AND ud.CompanyGatewayID = ga.CompanyGatewayID
        AND ud.CompanyID = ga.CompanyID
    LEFT JOIN Ratemanagement3.tblVendorTrunk ct 
        ON ct.AccountID = ga.AccountID AND ct.Status =1 
        AND UseInBilling = 0
    LEFT JOIN Ratemanagement3.tblTrunk t 
        ON t.TrunkID = ct.TrunkID
    SET ud.trunk = t.Trunk
    WHERE ud.CompanyID = p_CompanyID
    AND ud.CompanyGatewayID = p_CompanyGatewayID
    AND ud.processId = p_processId
    AND (ud.billed_duration >0 OR ud.selling_cost > 0);
 


    -- update trunk if set UseInBilling
    UPDATE tblTempVendorCDR ud
    LEFT JOIN tblGatewayAccount  ga 
        ON ud.GatewayAccountID = ga.GatewayAccountID
        AND ud.CompanyGatewayID = ga.CompanyGatewayID
        AND ud.CompanyID = ga.CompanyID
    LEFT JOIN Ratemanagement3.tblVendorTrunk ct 
        ON ct.AccountID = ga.AccountID AND ct.Status =1 
        AND UseInBilling = 1 AND cld LIKE CONCAT(ct.Prefix , '%')
    LEFT JOIN Ratemanagement3.tblTrunk t 
        ON t.TrunkID = ct.TrunkID
    SET ud.trunk = t.Trunk        
    WHERE ud.CompanyID = p_CompanyID
    AND ud.CompanyGatewayID = p_CompanyGatewayID
    AND ud.processId = p_processId
    AND (ud.billed_duration >0 OR ud.selling_cost > 0)
    AND t.Trunk IS NOT NULL;
    
     DROP TEMPORARY TABLE IF EXISTS tmp_TempVendorCDR_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempVendorCDR_(
			TempUsageDetailID int,
			prefix varchar(50)
	);
    INSERT INTO tmp_TempVendorCDR_
    SELECT
	    TempVendorCDRID,
        MAX(r.Code) AS prefix
    FROM tblTempVendorCDR ud
    LEFT JOIN tblGatewayAccount ga 
        ON ud.GatewayAccountID = ga.GatewayAccountID
        AND ud.CompanyGatewayID = ga.CompanyGatewayID
        AND ud.CompanyID = ga.CompanyID
    LEFT JOIN Ratemanagement3.tblVendorTrunk ct 
        ON ct.AccountID = ga.AccountID AND ct.Status =1  
        AND ((ct.UseInBilling = 1 AND cld LIKE CONCAT(ct.Prefix , '%')) OR ct.UseInBilling = 0 )
    LEFT JOIN Ratemanagement3.tblVendorRate cr 
        ON cr.AccountId = ga.AccountID 
        AND  cr.TrunkID = ct.TrunkID
    LEFT JOIN Ratemanagement3.tblRate r 
        ON cr.RateID = r.RateID
    WHERE  ud.CompanyID = p_CompanyID
    AND ud.CompanyGatewayID = p_CompanyGatewayID
    AND ud.processId = p_processId
    AND (ud.billed_duration >0 OR ud.selling_cost > 0)
    AND (
            (ct.UseInBilling = 1 AND ( (ct.AccountID is not null and  ct.Prefix is null and  cld LIKE CONCAT(r.Code , '%')) or (ct.Prefix is not null and  cld LIKE CONCAT(ct.Prefix,r.Code , '%'))))
            or 
            (ct.UseInBilling = 0 AND ( (ct.AccountID is not null and cld LIKE CONCAT(r.Code , '%')) or (cld LIKE CONCAT(r.Code , '%')) ) ) 
        )
    GROUP BY  TempVendorCDRID   ;

      -- Update Code
    UPDATE tblTempVendorCDR tbl2
    INNER JOIN tmp_TempVendorCDR_
        ON tbl2.TempVendorCDRID = TempVendorCDRID
          SET area_prefix = prefix;
   
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;


END