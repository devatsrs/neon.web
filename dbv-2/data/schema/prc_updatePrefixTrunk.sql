CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_updatePrefixTrunk`(IN `p_CompanyID` int, IN `p_CompanyGatewayID` int, IN `p_processId` varchar(200), IN `p_tbltempusagedetail_name` VARCHAR(200))
BEGIN
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
	
	set @stm6 = CONCAT(' UPDATE LocalRMCdr.`' , p_tbltempusagedetail_name , '` ud
	 SET ud.trunk = "Other",ud.area_prefix = "Other"
    WHERE 
    	  ud.processId = "' , p_processId , '" 
			AND ud.is_inbound = 0;
    ');

    PREPARE stmt6 FROM @stm6;
    EXECUTE stmt6;
    DEALLOCATE PREPARE stmt6;

    -- update trunk with first trunk if not set UseInBilling
    SET @stm1 = CONCAT(' UPDATE LocalRMCdr.`' , p_tbltempusagedetail_name , '` ud
    LEFT JOIN LocalRatemanagement.tblCustomerTrunk ct 
        ON ct.AccountID = ud.AccountID AND ct.Status =1 
        AND UseInBilling = 0 and ud.processId = "' , p_processId , '"
    LEFT JOIN LocalRatemanagement.tblTrunk t 
        ON t.TrunkID = ct.TrunkID and ud.processId = "' , p_processId , '"
	   SET ud.trunk = IFNULL(t.Trunk,"Other")
    WHERE 
        ud.CompanyID = "' , p_CompanyID , '"
    AND ud.CompanyGatewayID = "' , p_CompanyGatewayID , '"
    AND ud.processId = "' , p_processId , '"
    AND (ud.billed_duration >0 OR ud.cost > 0)
	AND ud.is_inbound = 0;

    ');

    PREPARE stmt1 FROM @stm1;
    EXECUTE stmt1;
    DEALLOCATE PREPARE stmt1;
	


    -- update trunk if set UseInBilling
    SET @stm2 = CONCAT(' UPDATE LocalRMCdr.`' , p_tbltempusagedetail_name , '` ud
    LEFT JOIN LocalRatemanagement.tblCustomerTrunk ct 
        ON ct.AccountID = ud.AccountID AND ct.Status =1 and ud.processId = "' , p_processId , '"
        AND UseInBilling = 1 AND cld LIKE CONCAT(ct.Prefix , "%")
    LEFT JOIN LocalRatemanagement.tblTrunk t 
        ON t.TrunkID = ct.TrunkID and ud.processId = "' , p_processId , '"
	   SET ud.trunk = IFNULL(t.Trunk,"Other")
    WHERE 
	     ud.CompanyID = "' , p_CompanyID , '"
    AND ud.CompanyGatewayID = "' , p_CompanyGatewayID , '"
    AND ud.processId = "' , p_processId , '"
    AND (ud.billed_duration >0 OR ud.cost > 0)
    AND ud.is_inbound = 0
    AND t.Trunk IS NOT NULL;
    ');

    PREPARE stmt2 FROM @stm2;
    EXECUTE stmt2;
    DEALLOCATE PREPARE stmt2;
    
    DROP TEMPORARY TABLE IF EXISTS tmp_TempUsageDetail_;
    CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempUsageDetail_(
		    TempUsageDetailID int,
			prefix varchar(50),
			INDEX IX_TempUsageDetailID(`TempUsageDetailID`)
	);
	DROP TEMPORARY TABLE IF EXISTS tmp_TempUsageDetail2_;
    CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempUsageDetail2_(
			TempUsageDetailID int,
			prefix varchar(50),
			INDEX IX_TempUsageDetailID2(`TempUsageDetailID`)
	);
	
	
	SET @stm3 = CONCAT('
	INSERT INTO tmp_TempUsageDetail_
        SELECT
    		  TempUsageDetailID,
            r.Code AS prefix
        FROM LocalRMCdr.`' , p_tbltempusagedetail_name , '` ud FORCE INDEX (IX_' , p_tbltempusagedetail_name , '_CID_CGID_PID)
        INNER JOIN LocalRatemanagement.tblCustomerTrunk ct FORCE INDEX (IX_AccountIDTrunkID_Unique)
            ON ct.AccountID = ud.AccountID AND ct.Status =1  
            AND ((ct.UseInBilling = 1 AND cld LIKE CONCAT(ct.Prefix , "%")) OR ct.UseInBilling = 0 ) and ud.processId = "' , p_processId , '"
        INNER JOIN LocalRatemanagement.tblCustomerRate cr FORCE INDEX (IX_tblCustomerRate_CustomerID_TrunkID_effectivedate)
            ON cr.CustomerID = ud.AccountID
            AND  cr.TrunkID = ct.TrunkID and ud.processId = "' , p_processId , '"
            AND cr.EffectiveDate <= Now()
        INNER JOIN LocalRatemanagement.tblRate r 
            ON cr.RateID = r.RateID AND  ct.CodeDeckId = r.CodeDeckId and ud.processId = "' , p_processId , '"
        WHERE  
    	     ud.CompanyID = "' , p_CompanyID , '"
        AND ud.CompanyGatewayID = "' , p_CompanyGatewayID , '"
        AND ud.processId = "' , p_processId , '"
        AND (ud.billed_duration >0 OR ud.cost > 0)
        AND ud.is_inbound = 0
        AND (
                (ct.UseInBilling = 1 AND ( (ct.AccountID is not null and  ct.Prefix is null and  cld LIKE CONCAT(r.Code , "%")) or (ct.Prefix is not null and  cld LIKE CONCAT(ct.Prefix,r.Code , "%"))))
                or 
                (ct.UseInBilling = 0 AND ( (ct.AccountID is not null and cld LIKE CONCAT(r.Code , "%")) or (cld LIKE CONCAT(r.Code , "%")) ) ) 
            );
	');

    PREPARE stmt3 FROM @stm3;
    EXECUTE stmt3;
    DEALLOCATE PREPARE stmt3;
    
    SET @stm8 = CONCAT('
	INSERT INTO tmp_TempUsageDetail_
        SELECT
    		  TempUsageDetailID,
            r.Code AS prefix
        FROM LocalRMCdr.`' , p_tbltempusagedetail_name , '` ud FORCE INDEX (IX_' , p_tbltempusagedetail_name , '_CID_CGID_PID)
        INNER JOIN LocalRatemanagement.tblCustomerTrunk ct FORCE INDEX (IX_AccountIDTrunkID_Unique)
            ON ct.AccountID = ud.AccountID AND ct.Status =1  
            AND ((ct.UseInBilling = 1 AND cld LIKE CONCAT(ct.Prefix , "%")) OR ct.UseInBilling = 0 ) and ud.processId = "' , p_processId , '"
        INNER JOIN LocalRatemanagement.tblRateTableRate rtr FORCE INDEX (IX_RateTableId_RateID_EffectiveDate)
            ON rtr.RateTableId = ct.RateTableID and ud.processId = "' , p_processId , '"
            AND rtr.EffectiveDate <= Now()
        INNER JOIN LocalRatemanagement.tblRate r 
            ON  rtr.RateID = r.RateID  AND  ct.CodeDeckId = r.CodeDeckId and ud.processId = "' , p_processId , '"
        WHERE  
    	     ud.CompanyID = "' , p_CompanyID , '"
        AND ud.CompanyGatewayID = "' , p_CompanyGatewayID , '"
        AND ud.processId = "' , p_processId , '"
        AND (ud.billed_duration >0 OR ud.cost > 0)
        AND ud.is_inbound = 0
        AND (
                (ct.UseInBilling = 1 AND ( (ct.AccountID is not null and  ct.Prefix is null and  cld LIKE CONCAT(r.Code , "%")) or (ct.Prefix is not null and  cld LIKE CONCAT(ct.Prefix,r.Code , "%"))))
                or 
                (ct.UseInBilling = 0 AND ( (ct.AccountID is not null and cld LIKE CONCAT(r.Code , "%")) or (cld LIKE CONCAT(r.Code , "%")) ) ) 
            );
	');

    PREPARE stmt8 FROM @stm8;
    EXECUTE stmt8;
    DEALLOCATE PREPARE stmt8;
    
    SET @stm7 = CONCAT('INSERT INTO tmp_TempUsageDetail2_
	SELECT tbl.TempUsageDetailID,MAX(tbl.prefix)  
	FROM tmp_TempUsageDetail_ tbl
	GROUP BY tbl.TempUsageDetailID;');
    
    PREPARE stmt7 FROM @stm7;
    EXECUTE stmt7;
    DEALLOCATE PREPARE stmt7;
   
	
	SET @stm4 = CONCAT('UPDATE LocalRMCdr.' , p_tbltempusagedetail_name , ' tbl2
    INNER JOIN tmp_TempUsageDetail2_ tbl
        ON tbl2.TempUsageDetailID = tbl.TempUsageDetailID
    SET area_prefix = prefix
    WHERE tbl2.processId = "' , p_processId , '"
	 ');

    PREPARE stmt4 FROM @stm4;
    EXECUTE stmt4;
    DEALLOCATE PREPARE stmt4;
	 
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
        
END