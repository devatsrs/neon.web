CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSProcessCodeDeck`(IN `p_processId` VARCHAR(200) , IN `p_companyId` INT)
BEGIN

    DECLARE v_AffectedRecords_ INT DEFAULT 0;     
    DECLARE   v_CodeDeckId_ INT;
    
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
    
	 DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
    CREATE TEMPORARY TABLE tmp_JobLog_  ( 
        Message VARCHAR(200)     
    );

    SELECT CodeDeckId INTO v_CodeDeckId_ FROM tblTempCodeDeck WHERE ProcessId = p_processId AND CompanyId = p_companyId LIMIT 1;
    
    DELETE n1 FROM tblTempCodeDeck n1, tblTempCodeDeck n2 WHERE n1.TempCodeDeckRateID < n2.TempCodeDeckRateID 
	 	AND n1.CodeDeckId = n2.CodeDeckId
		AND  n1.CompanyId = n2.CompanyId
		AND  n1.Code = n2.Code
		AND  n1.ProcessId = n2.ProcessId
 		AND  n1.ProcessId = p_processId and n2.ProcessId = p_processId;
 
    UPDATE tblTempCodeDeck
    SET  
        tblTempCodeDeck.Interval1 = CASE WHEN tblTempCodeDeck.Interval1 is not null  and tblTempCodeDeck.Interval1 > 0
                                    THEN    
                                        tblTempCodeDeck.Interval1
                                    ELSE
                                    CASE WHEN tblTempCodeDeck.Interval1 is null and (tblTempCodeDeck.Description LIKE '%gambia%' OR tblTempCodeDeck.Description LIKE '%mexico%')
                                            THEN 
                                            60
                                    ELSE CASE WHEN tblTempCodeDeck.Description LIKE '%USA%' 
                                            THEN 
                                            6
                                            ELSE 
                                            1
                                        END
                                    END
                                    END,
        tblTempCodeDeck.IntervalN = CASE WHEN tblTempCodeDeck.IntervalN is not null  and tblTempCodeDeck.IntervalN > 0
                                    THEN    
                                        tblTempCodeDeck.IntervalN
                                    ELSE
                                        CASE WHEN tblTempCodeDeck.Description LIKE '%mexico%' THEN 
                                        60
                                    ELSE CASE
                                        WHEN tblTempCodeDeck.Description LIKE '%USA%' THEN 
                                        6
                                    ELSE 
                                        1
                                    END
                                    END
                                    END
    WHERE tblTempCodeDeck.ProcessId = p_processId;
  
    UPDATE tblTempCodeDeck t
    INNER JOIN (
        SELECT DISTINCT
            tblTempCodeDeck.Code,
            tblCountry.CountryID
        FROM tblCountry
        LEFT OUTER JOIN (
            SELECT
                Prefix
            FROM tblCountry
            GROUP BY Prefix
            HAVING COUNT(*) > 1) d
            ON tblCountry.Prefix = d.Prefix
        INNER JOIN tblTempCodeDeck
            ON 
                tblTempCodeDeck.Country IS NOT NULL
            AND (tblTempCodeDeck.Code LIKE CONCAT(tblCountry.Prefix , '%') AND d.Prefix IS NULL)
                OR (tblTempCodeDeck.Code LIKE CONCAT(tblCountry.Prefix , '%') AND d.Prefix IS NOT NULL AND 
                (tblTempCodeDeck.Country LIKE Concat('%' , tblCountry.Country , '%') OR tblTempCodeDeck.Description LIKE Concat('%' , tblCountry.Country , '%')) )
        WHERE tblTempCodeDeck.ProcessId = p_processId) c
        ON t.Code = c.Code
        AND t.ProcessId = p_processId
    SET t.CountryId = c.CountryID;
  
          IF ( SELECT COUNT(*)
                 FROM   tblTempCodeDeck
                 WHERE  tblTempCodeDeck.ProcessId = p_processId
                        AND CountryId IS NULL
               ) > 0
            THEN
                
        INSERT INTO tmp_JobLog_  (Message)
        SELECT DISTINCT CONCAT(CODE , ' INVALID CODE - COUNTRY NOT FOUND ')
        FROM tblTempCodeDeck 
        WHERE tblTempCodeDeck.ProcessId = p_processId 
        AND CountryId IS NULL AND Country IS NOT NULL;
        
        
            
            END IF;   


 IF ( SELECT COUNT(*)
                 FROM   tblTempCodeDeck
                 WHERE  tblTempCodeDeck.ProcessId = p_processId
                        AND tblTempCodeDeck.Action = 'D'
               ) > 0
            THEN
      DELETE  tblRate
            FROM    tblRate
                    INNER JOIN tblTempCodeDeck ON tblTempCodeDeck.Code = tblRate.Code
                                                  AND tblRate.CompanyID = p_companyId AND tblRate.CodeDeckId = v_CodeDeckId_
                    LEFT OUTER JOIN tblCustomerRate ON tblRate.RateID = tblCustomerRate.RateID
                    LEFT OUTER JOIN tblRateTableRate ON tblRate.RateID = tblRateTableRate.RateID
                    LEFT OUTER JOIN tblVendorRate ON tblRate.RateID = tblVendorRate.RateId
            WHERE   tblTempCodeDeck.Action = 'D'
          AND tblTempCodeDeck.CompanyID = p_companyId
          AND tblTempCodeDeck.CodeDeckId = v_CodeDeckId_
          AND tblTempCodeDeck.ProcessId = p_processId                    
                    AND tblCustomerRate.CustomerRateID IS NULL
                    AND tblRateTableRate.RateTableRateID IS NULL
                    AND tblVendorRate.VendorRateID IS NULL ;   
		END IF; 
      SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();
  
  
      INSERT INTO tmp_JobLog_ (Message)
      SELECT CONCAT(tblRate.Code , 'FAILED TO DELETE - CODE IS IN USE')
      FROM    tblRate
                    INNER JOIN tblTempCodeDeck ON tblTempCodeDeck.Code = tblRate.Code
          AND tblRate.CompanyID = p_companyId AND tblRate.CodeDeckId = v_CodeDeckId_
            WHERE   tblTempCodeDeck.Action = 'D'
          AND tblTempCodeDeck.ProcessId = p_processId
          AND tblTempCodeDeck.CompanyID = p_companyId AND tblTempCodeDeck.CodeDeckId = v_CodeDeckId_;
      
      UPDATE  tblRate
      JOIN tblTempCodeDeck ON tblRate.Code = tblTempCodeDeck.Code
      		AND tblRate.CountryID = tblTempCodeDeck.CountryId
            AND tblTempCodeDeck.ProcessId = p_processId
            AND tblRate.CompanyID = p_companyId
            AND tblRate.CodeDeckId = v_CodeDeckId_
            AND tblTempCodeDeck.Action != 'D'
		SET   tblRate.Description = tblTempCodeDeck.Description,
            tblRate.Interval1 = tblTempCodeDeck.Interval1,
            tblRate.IntervalN = tblTempCodeDeck.IntervalN;
  
      
      SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();
      
            INSERT  INTO tblRate
                    ( CountryID ,
                      CompanyID ,
                      CodeDeckId,
                      Code ,
                      Description,
                      Interval1,
                      IntervalN
                    )
                    SELECT  DISTINCT
              tblTempCodeDeck.CountryId ,
                            tblTempCodeDeck.CompanyId ,
                            tblTempCodeDeck.CodeDeckId,
                            tblTempCodeDeck.Code ,
                            tblTempCodeDeck.Description,
                            tblTempCodeDeck.Interval1,
                            tblTempCodeDeck.IntervalN
                    FROM    tblTempCodeDeck left join tblRate on(tblRate.CompanyID = p_companyId AND  tblRate.CodeDeckId = v_CodeDeckId_ AND tblTempCodeDeck.Code=tblRate.Code)
                    WHERE  tblRate.RateID is null
                            AND tblTempCodeDeck.ProcessId = p_processId
              AND tblTempCodeDeck.CompanyID = p_companyId
              AND tblTempCodeDeck.CodeDeckId = v_CodeDeckId_
                            AND tblTempCodeDeck.Action != 'D';
  
      SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();
    
	 INSERT INTO tmp_JobLog_ (Message)
	 SELECT CONCAT(v_AffectedRecords_, ' Records Uploaded \n\r ' );
	 
    DELETE  FROM tblTempCodeDeck WHERE   tblTempCodeDeck.ProcessId = p_processId;
 	 SELECT * from tmp_JobLog_;
	      
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END