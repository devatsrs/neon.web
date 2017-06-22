CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_updateInboundRate`(
	IN `p_AccountID` INT,
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200),
	IN `p_CLD` VARCHAR(500),
	IN `p_ServiceID` INT,
	IN `p_RateMethod` VARCHAR(50),
	IN `p_SpecifyRate` DECIMAL(18,6)
)
BEGIN

	SET @stm = CONCAT('UPDATE   NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud SET cost = 0,is_rerated=0  WHERE ProcessID = "',p_processId,'" AND AccountID = "',p_AccountID ,'" AND ServiceID = "',p_ServiceID ,'" AND ("' , p_CLD , '" = "" OR cld = "' , p_CLD , '") AND is_inbound = 1 ') ;

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET @stm = CONCAT('
	UPDATE   NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud 
	INNER JOIN NeonRMDev.tmp_inboundcodes_ cr ON cr.Code = ud.area_prefix
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
	,duration=billed_second
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
	AND ServiceID = "',p_ServiceID ,'"
	AND ("' , p_CLD , '" = "" OR cld = "' , p_CLD , '")
	AND is_inbound = 1') ;
	
	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	IF p_RateMethod  = 'SpecifyRate'
	THEN

		SET @stm = CONCAT('
		UPDATE   NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud
		INNER JOIN NeonRMDev.tmp_inboundcodes_ cr ON cr.Code = ud.area_prefix
		SET cost =
			CASE WHEN  billed_second >= 1
			THEN
				(',p_SpecifyRate,'/60.0)*1+CEILING((billed_second-1)/1)*(',p_SpecifyRate,'/60.0)*1
			ElSE
				CASE WHEN  billed_second > 0
				THEN
					',p_SpecifyRate,'
				ELSE
					0
				END
			END
		,is_rerated=1
		,duration=billed_second
		,billed_duration =
			CASE WHEN  billed_second >= 1
			THEN
				1+CEILING((billed_second-1)/1)*1
			ElSE 
				CASE WHEN  billed_second > 0
				THEN
					1
				ELSE
					0
				END
			END 
		WHERE ProcessID = "',p_processId,'"
		AND AccountID = "',p_AccountID ,'"
		AND ServiceID = "',p_ServiceID ,'"
		AND cr.Code IS NULL
		AND ("' , p_CLD , '" = "" OR cld = "' , p_CLD , '")
		AND is_inbound = 1') ;
		
		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

	END IF;

END