CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_updateVendorPrefixTrunk`(IN `p_CompanyID` int, IN `p_CompanyGatewayID` int, IN `p_processId` varchar(200), IN `p_tbltempusagedetail_name` VARCHAR(50))
BEGIN
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

    -- update trunk with first trunk if not set UseInBilling
    SET @stm1 = CONCAT(' UPDATE LocalRMCdr.`' , p_tbltempusagedetail_name , '` ud
    LEFT JOIN LocalRatemanagement.tblVendorTrunk ct 
        ON ct.AccountID = ud.AccountID AND ct.Status =1 
        AND UseInBilling = 0 and ud.processId = "' , p_processId , '"
    LEFT JOIN LocalRatemanagement.tblTrunk t 
        ON t.TrunkID = ct.TrunkID and ud.processId = "' , p_processId , '"
	   SET ud.trunk = IFNULL(t.Trunk,"Other")
    WHERE 
        ud.CompanyID = "' , p_CompanyID , '"
    AND ud.CompanyGatewayID = "' , p_CompanyGatewayID , '"
    AND ud.processId = "' , p_processId , '"
    ;
    ');

    PREPARE stmt1 FROM @stm1;
    EXECUTE stmt1;
    DEALLOCATE PREPARE stmt1;
	


    -- update trunk if set UseInBilling
    SET @stm2 = CONCAT(' UPDATE LocalRMCdr.`' , p_tbltempusagedetail_name , '` ud
    LEFT JOIN LocalRatemanagement.tblVendorTrunk ct 
        ON ct.AccountID = ud.AccountID AND ct.Status =1 and ud.processId = "' , p_processId , '"
        AND UseInBilling = 1 AND cld LIKE CONCAT(ct.Prefix , "%")
    LEFT JOIN LocalRatemanagement.tblTrunk t 
        ON t.TrunkID = ct.TrunkID and ud.processId = "' , p_processId , '"
	   SET ud.trunk = IFNULL(t.Trunk,"Other")
    WHERE 
	     ud.CompanyID = "' , p_CompanyID , '"
    AND ud.CompanyGatewayID = "' , p_CompanyGatewayID , '"
    AND ud.processId = "' , p_processId , '"
    AND t.Trunk IS NOT NULL;
    ');

    PREPARE stmt2 FROM @stm2;
    EXECUTE stmt2;
    DEALLOCATE PREPARE stmt2;
    
    DROP TEMPORARY TABLE IF EXISTS tmp_TempVendorCDR_;
    CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempVendorCDR_(
			TempVendorCDRID int,
			prefix varchar(50),
			INDEX IX_TempUsageDetailID(`TempVendorCDRID`)
	);
	DROP TEMPORARY TABLE IF EXISTS tmp_TempVendorCDR2_;
    CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempVendorCDR2_(
			TempVendorCDRID int,
			prefix varchar(50),
			INDEX IX_TempUsageDetailID2(`TempVendorCDRID`)
	);
    
    
	
	SET @stm3 = CONCAT('
	INSERT INTO tmp_TempVendorCDR_
        SELECT
    		  TempVendorCDRID,
            r.Code AS prefix
        FROM LocalRMCdr.`' , p_tbltempusagedetail_name , '` ud FORCE INDEX (IX_' , p_tbltempusagedetail_name , '_CID_CGID_PID)
        INNER JOIN LocalRatemanagement.tblVendorTrunk ct FORCE INDEX (IX_AccountID_TrunkID_Status)
            ON ct.AccountID = ud.AccountID AND ct.Status =1  
            AND ((ct.UseInBilling = 1 AND cld LIKE CONCAT(ct.Prefix , "%")) OR ct.UseInBilling = 0 ) and ud.processId = "' , p_processId , '"
        INNER JOIN LocalRatemanagement.tblVendorRate cr FORCE INDEX (IX_tblVendorRate_RateId_TrunkID_EffectiveDate)
            ON cr.AccountId = ud.AccountID
            AND  cr.TrunkID = ct.TrunkID and ud.processId = "' , p_processId , '"
        INNER JOIN LocalRatemanagement.tblRate r 
            ON cr.RateID = r.RateID and ud.processId = "' , p_processId , '"
        WHERE  
    	  	  ud.CompanyID = "' , p_CompanyID , '"
        AND ud.CompanyGatewayID = "' , p_CompanyGatewayID , '"
        AND ud.processId = "' , p_processId , '"
        AND	ud.area_prefix = "Other"
        AND (
                (ct.UseInBilling = 1 AND ( (ct.AccountID is not null and  ct.Prefix is null and  cld LIKE CONCAT(r.Code , "%")) or (ct.Prefix is not null and  cld LIKE CONCAT(ct.Prefix,r.Code , "%"))))
                or 
                (ct.UseInBilling = 0 AND ( (ct.AccountID is not null and cld LIKE CONCAT(r.Code , "%")) or (cld LIKE CONCAT(r.Code , "%")) ) ) 
            );
	');

    PREPARE stmt3 FROM @stm3;
    EXECUTE stmt3;
    DEALLOCATE PREPARE stmt3;
    
    SET @stm7 = CONCAT('INSERT INTO tmp_TempVendorCDR2_
    SELECT tbl.TempVendorCDRID,MAX(tbl.prefix)  
	FROM tmp_TempVendorCDR_ tbl
	GROUP BY tbl.TempVendorCDRID;');
    
    PREPARE stmt7 FROM @stm7;
    EXECUTE stmt7;
    DEALLOCATE PREPARE stmt7;
   
	
	SET @stm4 = CONCAT('UPDATE LocalRMCdr.' , p_tbltempusagedetail_name , ' tbl2
    INNER JOIN tmp_TempVendorCDR2_ tbl
        ON tbl2.TempVendorCDRID = tbl.TempVendorCDRID
    SET area_prefix = prefix
    WHERE tbl2.processId = "' , p_processId , '"
	 ');

    PREPARE stmt4 FROM @stm4;
    EXECUTE stmt4;
    DEALLOCATE PREPARE stmt4;
	 
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 

        
END