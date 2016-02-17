CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_updateVendorPrefixTrunk`(IN `p_CompanyID` int, IN `p_CompanyGatewayID` int, IN `p_processId` varchar(200), IN `p_tbltempusagedetail_name` VARCHAR(50))
BEGIN
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

      -- update trunk with first trunk if not set UseInBilling
   set @stm1 = CONCAT(' UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` ud
    LEFT JOIN tblGatewayAccount  ga 
        ON ud.GatewayAccountID = ga.GatewayAccountID
        AND ud.CompanyGatewayID = ga.CompanyGatewayID
        AND ud.CompanyID = ga.CompanyID and ud.processId = "' , p_processId , '"
    LEFT JOIN Ratemanagement3.tblVendorTrunk ct 
        ON ct.AccountID = ga.AccountID AND ct.Status =1 
        AND UseInBilling = 0 and ud.processId = "' , p_processId , '"
    LEFT JOIN Ratemanagement3.tblTrunk t 
        ON t.TrunkID = ct.TrunkID and ud.processId = "' , p_processId , '"
	 SET ud.trunk = IFNULL(t.Trunk,"Other")
    WHERE 
    ud.processId = "' , p_processId , '"
    AND ud.CompanyID = "' , p_CompanyID , '"
    AND ud.CompanyGatewayID = "' , p_CompanyGatewayID , '"
    AND (ud.billed_duration >0 OR ud.selling_cost > 0);
    ');

    PREPARE stmt1 FROM @stm1;
    EXECUTE stmt1;
    DEALLOCATE PREPARE stmt1;
	


 -- update trunk if set UseInBilling
     set @stm2 = CONCAT(' UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` ud
    LEFT JOIN tblGatewayAccount  ga 
        ON ud.GatewayAccountID = ga.GatewayAccountID
        AND ud.CompanyGatewayID = ga.CompanyGatewayID
        AND ud.CompanyID = ga.CompanyID and ud.processId = "' , p_processId , '"
    LEFT JOIN Ratemanagement3.tblVendorTrunk ct 
        ON ct.AccountID = ga.AccountID AND ct.Status =1 and ud.processId = "' , p_processId , '"
        AND UseInBilling = 1 AND cld LIKE CONCAT(ct.Prefix , "%")
    LEFT JOIN Ratemanagement3.tblTrunk t 
        ON t.TrunkID = ct.TrunkID and ud.processId = "' , p_processId , '"
	 SET ud.trunk = IFNULL(t.Trunk,"Other")
    WHERE 
    ud.processId = "' , p_processId , '"
	 AND  ud.CompanyID = "' , p_CompanyID , '"
    AND ud.CompanyGatewayID = "' , p_CompanyGatewayID , '"
    AND (ud.billed_duration >0 OR ud.selling_cost > 0)
    AND t.Trunk IS NOT NULL;
    ');

    PREPARE stmt2 FROM @stm2;
    EXECUTE stmt2;
    DEALLOCATE PREPARE stmt2;
    
   DROP TEMPORARY TABLE IF EXISTS tmp_TempVendorCDR_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempVendorCDR_(
			TempVendorCDRID int,
			prefix varchar(50)
	);
	
	
	set @stm3 = CONCAT('
	 INSERT INTO tmp_TempVendorCDR_
    SELECT
		  TempVendorCDRID,
        MAX(r.Code) AS prefix
    FROM RMCDR3.`' , p_tbltempusagedetail_name , '` ud
    LEFT JOIN tblGatewayAccount ga 
        ON ud.GatewayAccountID = ga.GatewayAccountID
        AND ud.CompanyGatewayID = ga.CompanyGatewayID
        AND ud.CompanyID = ga.CompanyID and ud.processId = "' , p_processId , '"
    LEFT JOIN Ratemanagement3.tblVendorTrunk ct 
        ON ct.AccountID = ga.AccountID AND ct.Status =1  
        AND ((ct.UseInBilling = 1 AND cld LIKE CONCAT(ct.Prefix , "%")) OR ct.UseInBilling = 0 ) and ud.processId = "' , p_processId , '"
    LEFT JOIN Ratemanagement3.tblVendorRate cr 
        ON cr.AccountId = ga.AccountID
        AND  cr.TrunkID = ct.TrunkID and ud.processId = "' , p_processId , '"
    LEFT JOIN Ratemanagement3.tblRate r 
        ON cr.RateID = r.RateID and ud.processId = "' , p_processId , '"
    WHERE  
 	 ud.processId = "' , p_processId , '"
	 AND ud.CompanyID = "' , p_CompanyID , '"
    AND ud.CompanyGatewayID = "' , p_CompanyGatewayID , '"
    AND (ud.billed_duration >0 OR ud.selling_cost > 0)
    AND (
            (ct.UseInBilling = 1 AND ( (ct.AccountID is not null and  ct.Prefix is null and  cld LIKE CONCAT(r.Code , "%")) or (ct.Prefix is not null and  cld LIKE CONCAT(ct.Prefix,r.Code , "%"))))
            or 
            (ct.UseInBilling = 0 AND ( (ct.AccountID is not null and cld LIKE CONCAT(r.Code , "%")) or (cld LIKE CONCAT(r.Code , "%")) ) ) 
        )
    GROUP BY  TempVendorCDRID;
	');

    PREPARE stmt3 FROM @stm3;
    EXECUTE stmt3;
    DEALLOCATE PREPARE stmt3;
   
	
	set @stm4 = CONCAT('UPDATE RMCDR3.' , p_tbltempusagedetail_name , ' tbl2
    INNER JOIN tmp_TempVendorCDR_ tbl
        ON tbl2.TempVendorCDRID = tbl.TempVendorCDRID
    SET area_prefix = prefix
    WHERE tbl2.processId = "' , p_processId , '"
	 ');

    PREPARE stmt4 FROM @stm4;
    EXECUTE stmt4;
    DEALLOCATE PREPARE stmt4;
	 
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 

        
END