CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSProcessDialString`(IN `p_processId` VARCHAR(200) , IN `p_dialStringId` INT)
BEGIN

    DECLARE v_AffectedRecords_ INT DEFAULT 0;         
    DECLARE totalduplicatecode INT(11) DEFAULT 0;	 
    DECLARE errormessage longtext;
	 DECLARE errorheader longtext;
    
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
    
	 DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
    CREATE TEMPORARY TABLE tmp_JobLog_  ( 
        Message longtext   
    );
    
      
    -- delete duplicate   entry
    DELETE n1 FROM tblTempDialString n1, tblTempDialString n2 WHERE n1.TempDialStringID < n2.TempDialStringID 
	 	AND n1.DialString = n2.DialString
		AND  n1.ChargeCode = n2.ChargeCode
		AND  n1.DialStringID = n2.DialStringID
		AND  n1.ProcessId = n2.ProcessId
 		AND  n1.DialStringID = p_dialStringId and n2.DialStringID = p_dialStringId
 		AND  n1.ProcessId = p_processId and n2.ProcessId = p_processId;
 		
 	-- check duplicate code record	
			SELECT COUNT(*) INTO totalduplicatecode FROM(
				SELECT COUNT(DialString) as c,DialString 
					FROM tblTempDialString 
						WHERE DialStringID = p_dialStringId
							 AND ProcessId = p_processId
					   GROUP BY DialString HAVING c>1) AS tbl;
				
			-- for duplicate code record	
			IF  totalduplicatecode > 0
			THEN	
				
				INSERT INTO tmp_JobLog_ (Message)
				  SELECT DISTINCT 
				  CONCAT(DialString , ' DUPLICATE CODE')
				  	FROM(
						SELECT   COUNT(DialString) as c,DialString
							 FROM tblTempDialString
									WHERE DialStringID = p_dialStringId
										 AND ProcessId = p_processId
						  GROUP BY DialString HAVING c>1) AS tbl;				
					
			END IF;	

IF  totalduplicatecode = 0 	
THEN	
 
    -- check and delete from tblDialStringCode where action is delete
	IF ( SELECT COUNT(*)
                 FROM   tblTempDialString
                 WHERE  tblTempDialString.ProcessId = p_processId
						AND tblTempDialString.DialStringID = p_dialStringId
                        AND tblTempDialString.Action = 'D'
               ) > 0
            THEN
		DELETE  tblDialStringCode
            FROM    tblDialStringCode
                    INNER JOIN tblTempDialString
						ON tblDialStringCode.DialString = tblTempDialString.DialString
                        AND tblDialStringCode.ChargeCode = tblTempDialString.ChargeCode
						AND tblDialStringCode.DialStringID = tblTempDialString.DialStringID
            WHERE   tblTempDialString.Action = 'D' AND tblTempDialString.DialStringID = p_dialStringId AND tblTempDialString.ProcessId = p_processId;   
		END IF; 
		
		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();
	  
		-- update description and forbidden 
		      
		UPDATE  tblDialStringCode
		JOIN tblTempDialString ON tblDialStringCode.DialString = tblTempDialString.DialString
      		AND tblDialStringCode.ChargeCode = tblTempDialString.ChargeCode            
			AND tblDialStringCode.DialStringID = tblTempDialString.DialStringID
			AND tblTempDialString.ProcessId = p_processId
            AND tblTempDialString.DialStringID = p_dialStringId
            AND tblTempDialString.Action != 'D'
		SET   tblDialStringCode.Description = tblTempDialString.Description,
            tblDialStringCode.Forbidden = tblTempDialString.Forbidden;
  
      
		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();
      
	  
		-- insert new record
		
            INSERT  INTO tblDialStringCode
                    ( DialStringID ,
                      DialString ,
                      ChargeCode,
                      Description ,
                      Forbidden
                    )
                    SELECT  DISTINCT
				tblTempDialString.DialStringID ,
                            tblTempDialString.DialString ,
                            tblTempDialString.ChargeCode,
                            tblTempDialString.Description ,
                            tblTempDialString.Forbidden
                    FROM    tblTempDialString left join tblDialStringCode
						on(tblDialStringCode.DialStringID = tblTempDialString.DialStringID
							AND  tblDialStringCode.DialString = tblTempDialString.DialString)
							-- AND tblDialStringCode.ChargeCode = tblTempDialString.ChargeCode)
                    WHERE  tblDialStringCode.DialStringCodeID is null
                            AND tblTempDialString.ProcessId = p_processId
							AND tblTempDialString.DialStringID = p_dialStringId
                            AND tblTempDialString.Action != 'D';
  
		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();
		
 END IF;   
		INSERT INTO tmp_JobLog_ (Message)
		SELECT CONCAT(v_AffectedRecords_, ' Records Uploaded \n\r ' );
	 
	-- 	DELETE  FROM tblTempDialString WHERE   tblTempDialString.ProcessId = p_processId;
		SELECT * from tmp_JobLog_; 
	      
		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    
END