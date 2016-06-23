CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSProcessDialPlan`(IN `p_processId` VARCHAR(200) , IN `p_dialPlanId` INT)
BEGIN

    DECLARE v_AffectedRecords_ INT DEFAULT 0;         
    DECLARE errormessage longtext;
	 DECLARE errorheader longtext;
    
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
    
	 DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
    CREATE TEMPORARY TABLE tmp_JobLog_  ( 
        Message longtext   
    );
    
      
    -- delete duplicate   entry
    DELETE n1 FROM tblTempDialPlan n1, tblTempDialPlan n2 WHERE n1.TempDialPlanID < n2.TempDialPlanID 
	 	AND n1.DialString = n2.DialString
		AND  n1.ChargeCode = n2.ChargeCode
		AND  n1.DialPlanID = n2.DialPlanID
		AND  n1.ProcessId = n2.ProcessId
 		AND  n1.DialPlanID = p_dialPlanId and n2.DialPlanID = p_dialPlanId
 		AND  n1.ProcessId = p_processId and n2.ProcessId = p_processId;
 
    -- check and delete from tblDialPlanCode where action is delete
	IF ( SELECT COUNT(*)
                 FROM   tblTempDialPlan
                 WHERE  tblTempDialPlan.ProcessId = p_processId
						AND tblTempDialPlan.DialPlanID = p_dialPlanId
                        AND tblTempDialPlan.Action = 'D'
               ) > 0
            THEN
		DELETE  tblDialPlanCode
            FROM    tblDialPlanCode
                    INNER JOIN tblTempDialPlan
						ON tblDialPlanCode.DialString = tblTempDialPlan.DialString
                        AND tblDialPlanCode.ChargeCode = tblTempDialPlan.ChargeCode
						AND tblDialPlanCode.DialPlanID = tblTempDialPlan.DialPlanID
            WHERE   tblTempDialPlan.Action = 'D' AND tblTempDialPlan.DialPlanID = p_dialPlanId AND tblTempDialPlan.ProcessId = p_processId;   
		END IF; 
		
		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();
	  
		-- update description and forbidden 
		      
		UPDATE  tblDialPlanCode
		JOIN tblTempDialPlan ON tblDialPlanCode.DialString = tblTempDialPlan.DialString
      		AND tblDialPlanCode.ChargeCode = tblTempDialPlan.ChargeCode            
			AND tblDialPlanCode.DialPlanID = tblTempDialPlan.DialPlanID
			AND tblTempDialPlan.ProcessId = p_processId
            AND tblTempDialPlan.DialPlanID = p_dialPlanId
            AND tblTempDialPlan.Action != 'D'
		SET   tblDialPlanCode.Description = tblTempDialPlan.Description,
            tblDialPlanCode.Forbidden = tblTempDialPlan.Forbidden;
  
      
		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();
      
	  
		-- insert new record
		
            INSERT  INTO tblDialPlanCode
                    ( DialPlanID ,
                      DialString ,
                      ChargeCode,
                      Description ,
                      Forbidden
                    )
                    SELECT  DISTINCT
				tblTempDialPlan.DialPlanID ,
                            tblTempDialPlan.DialString ,
                            tblTempDialPlan.ChargeCode,
                            tblTempDialPlan.Description ,
                            tblTempDialPlan.Forbidden
                    FROM    tblTempDialPlan left join tblDialPlanCode
						on(tblDialPlanCode.DialPlanID = tblTempDialPlan.DialPlanID
							AND  tblDialPlanCode.DialString = tblTempDialPlan.DialString
							AND tblDialPlanCode.ChargeCode = tblTempDialPlan.ChargeCode)
                    WHERE  tblDialPlanCode.DialPlanCodeID is null
                            AND tblTempDialPlan.ProcessId = p_processId
							AND tblTempDialPlan.DialPlanID = p_dialPlanId
                            AND tblTempDialPlan.Action != 'D';
  
		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();
    
		INSERT INTO tmp_JobLog_ (Message)
		SELECT CONCAT(v_AffectedRecords_, ' Records Uploaded \n\r ' );
	 
	-- 	DELETE  FROM tblTempDialPlan WHERE   tblTempDialPlan.ProcessId = p_processId;
		SELECT * from tmp_JobLog_; 
	      
		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    
END