CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getCustomerCliRateByAccount`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_TrunkID` INT , IN `p_processId` varchar(200), IN `p_tbltempusagedetail_name` VARCHAR(50))
BEGIN
    
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
    call prc_GetCustomerRate (p_CompanyID,p_AccountID,p_TrunkID,NULL,NULL,NULL,'All',1,0,0,0,'','',1);

    set @stm1 = CONCAT('UPDATE   RMCDR3.`' , p_tbltempusagedetail_name , '` ud 
    LEFT JOIN tmp_customerrate_ cr ON cr.Code = ud.area_prefix
    SET cost = CASE WHEN  billed_duration >= Interval1
    THEN
    (Rate/60.0)*Interval1+CEILING((billed_duration-Interval1)/IntervalN)*(Rate/60.0)*IntervalN+ifnull(ConnectionFee,0)
        ElSE 
        Rate+ifnull(ConnectionFee,0)
    END
    WHERE processid = "',p_processId,'"
    AND accountid = "',p_AccountID ,'" 
    AND trunk = (select Trunk from tblTrunk where TrunkID = "',p_TrunkID,'" limit 1)
    AND cr.rate is not null') ;
    
    PREPARE stmt1 FROM @stm1;
    EXECUTE stmt1;
    DEALLOCATE PREPARE stmt1;
    

    set @stm2 = CONCAT('UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` ud 
    LEFT JOIN tmp_customerrate_ cr ON cr.Code = ud.area_prefix
    SET cost = 0.0
    WHERE processid = "',p_processId,'"
    AND accountid = "',p_AccountID ,'"  
    AND trunk = (select  Trunk from tblTrunk where TrunkID = "',p_TrunkID,'" LIMIT 1)
    AND cr.rate is null ');
    
    PREPARE stmt2 FROM @stm2;
    EXECUTE stmt2;
    DEALLOCATE PREPARE stmt2;

    set @stm3 = CONCAT('UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` SET cost = 0.0
    WHERE processid = "',p_processId,'" AND accountid is null');
    
    PREPARE stmt3 FROM @stm3;
    EXECUTE stmt3;
    DEALLOCATE PREPARE stmt3;
    
    
    set @stm4 = CONCAT('SELECT DISTINCT ud.cld as area_prefix,ud.trunk,ud.GatewayAccountID,a.AccountName
    FROM RMCDR3.`' , p_tbltempusagedetail_name , '` ud 
    LEFT JOIN tmp_customerrate_ cr ON cr.Code = ud.area_prefix
    LEFT JOIN tblAccount a ON a.AccountID = ud.AccountID
    WHERE processid ="',p_processId,'" 
    AND ud.accountid = "',p_AccountID ,'"  
    AND trunk = (select  Trunk from tblTrunk where TrunkID = "',p_TrunkID,'" LIMIT 1)
    AND cr.rate is null');
    
    PREPARE stmt4 FROM @stm4;
    EXECUTE stmt4;
    DEALLOCATE PREPARE stmt4;
    
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    
END