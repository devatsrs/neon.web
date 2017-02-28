CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_updateOutboundRate`(
	IN `p_AccountID` INT,
	IN `p_TrunkID` INT,
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200)
)
BEGIN
    
    SET @stm = CONCAT('UPDATE   NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud SET cost = 0,is_rerated=0  WHERE ProcessID = "',p_processId,'" AND AccountID = "',p_AccountID ,'" AND ("',p_TrunkID ,'" = 0 OR TrunkID = "',p_TrunkID ,'") AND is_inbound = 0 ') ;

    PREPARE stmt FROM @stm;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    SET @stm = CONCAT('
    UPDATE   NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud 
    INNER JOIN NeonRMDev.tmp_codes_ cr ON cr.Code = ud.area_prefix
    SET cost = 
        CASE WHEN  billed_second >= Interval1
        THEN
            (Rate/60.0)*Interval1+CEILING((billed_second-Interval1)/IntervalN)*(Rate/60.0)*IntervalN+IFNULL(ConnectionFee,0)
        ElSE
            CASE WHEN  billed_second > 0
            THEN
                Rate+IFNULL(ConnectionFee,0)
            ELSE
                0
            END            
        END
    ,is_rerated=1
    ,duration = billed_second
    ,billed_duration =
        CASE WHEN  billed_second >= Interval1
        THEN
            Interval1+CEILING((billed_second-Interval1)/IntervalN)*IntervalN
        ElSE 
            CASE WHEN  billed_second > 0
            THEN
                Interval1
            ELSE
                0
            END
        END 
    
    WHERE ProcessID = "',p_processId,'"
    AND AccountID = "',p_AccountID ,'" 
    AND ("',p_TrunkID ,'" = 0 OR TrunkID = "',p_TrunkID ,'")
    AND is_inbound = 0') ;

    PREPARE stmt FROM @stm;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

END