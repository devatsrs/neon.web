CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_RateTableRateInsertUpdate`(IN `p_CompanyID` INT, IN `p_RateTableRateID` LONGTEXT
, IN `p_RateTableId` INT 
, IN `p_Rate` DECIMAL(18, 6) 
, IN `p_EffectiveDate` DATETIME
, IN `p_ModifiedBy` VARCHAR(50)
, IN `p_Interval1` INT
, IN `p_IntervalN` INT
, IN `p_ConnectionFee` DECIMAL(18, 6)
, IN `p_Critearea` INT
, IN `p_Critearea_TrunkID` INT
, IN `p_Critearea_CountryID` INT
, IN `p_Critearea_Code` VARCHAR(50)
, IN `p_Critearea_Description` VARCHAR(50)
, IN `p_Critearea_Effective` VARCHAR(50)
, IN `p_Update_EffectiveDate` INT
, IN `p_Update_Rate` INT
, IN `p_Update_Interval1` INT
, IN `p_Update_IntervalN` INT
, IN `p_Update_ConnectionFee` INT
, IN `p_Action` INT
)
BEGIN
              
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    DROP TEMPORARY TABLE IF EXISTS tmp_Update_RateTable_;
    CREATE TEMPORARY TABLE tmp_Update_RateTable_ (
      RateID INT,
      EffectiveDate DATE,
      RateTableRateID INT
    ); 
    
    DROP TEMPORARY TABLE IF EXISTS tmp_Insert_RateTable_;
    CREATE TEMPORARY TABLE tmp_Insert_RateTable_ (
      RateID INT,
      EffectiveDate DATE,
      RateTableRateID INT
    );
    
    DROP TEMPORARY TABLE IF EXISTS tmp_all_RateId_;
    CREATE TEMPORARY TABLE tmp_all_RateId_ (
      RateID INT,
      EffectiveDate DATE,
      RateTableRateID INT
	  );
	 
    
    DROP TEMPORARY TABLE IF EXISTS tmp_RateTable_;
      CREATE TEMPORARY TABLE tmp_RateTable_ (
         RateID INT,
         EffectiveDate DATE,
         RateTableRateID INT,
			INDEX tmp_RateTable_RateId (`RateId`)  
     );
    
   IF p_Critearea = 0
	THEN
 		INSERT INTO tmp_RateTable_
			  SELECT RateID,EffectiveDate,RateTableRateID
			   FROM tblRateTableRate
					WHERE (FIND_IN_SET(RateTableRateID,p_RateTableRateID) != 0 );
	
	END IF;
	
	
	IF p_Critearea = 1
	THEN
		
		
		 INSERT INTO tmp_RateTable_
				SELECT
					 tblRateTableRate.RateID,
                IFNULL(tblRateTableRate.EffectiveDate, NOW()) as EffectiveDate,
                tblRateTableRate.RateTableRateID
            FROM tblRate
            LEFT JOIN tblRateTableRate
                ON tblRateTableRate.RateID = tblRate.RateID
                AND tblRateTableRate.RateTableId = p_RateTableId
            INNER JOIN tblRateTable
                ON tblRateTable.RateTableId = tblRateTableRate.RateTableId
            WHERE		(tblRate.CompanyID = p_CompanyID)
					AND (p_Critearea_CountryID = 0 OR CountryID = p_Critearea_CountryID)
					AND (p_Critearea_Code IS NULL OR Code LIKE REPLACE(p_Critearea_Code, '*', '%'))
					AND (p_Critearea_Description IS NULL OR Description LIKE REPLACE(p_Critearea_Description, '*', '%'))
					AND TrunkID = p_Critearea_TrunkID
					AND (			
							p_Critearea_Effective = 'All' 
						OR (p_Critearea_Effective = 'Now' AND EffectiveDate <= NOW() )
						OR (p_Critearea_Effective = 'Future' AND EffectiveDate > NOW())
						);
		 
		  IF p_Critearea_Effective = 'Now'
		  THEN 
			 CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTable4_ as (select * from tmp_RateTable_);	        
          DELETE n1 FROM tmp_RateTable_ n1, tmp_RateTable4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate 
	 	   AND  n1.RateId = n2.RateId;
		  
		  END IF;
		  		  
	END IF;
	
	IF p_action = 1
	THEN
	
	IF p_Update_EffectiveDate = 0
	THEN
	
	INSERT INTO tmp_Update_RateTable_
		SELECT RateID,EffectiveDate,RateTableRateID
			 FROM tmp_RateTable_ ;	
				
	END IF;			
	
	IF p_Update_EffectiveDate = 1
	THEN
	

		INSERT INTO tmp_Update_RateTable_
		SELECT tblRateTableRate.RateID,
				p_EffectiveDate,
				tblRateTableRate.RateTableRateID
			 FROM tblRateTableRate 
			 	INNER JOIN tmp_RateTable_ r on tblRateTableRate.RateID = r.RateID
			 	 WHERE RateTableId = p_RateTableId   
					AND tblRateTableRate.EffectiveDate = p_EffectiveDate;
	
		INSERT INTO tmp_all_RateId_
		SELECT  tblRateTableRate.RateID,
					tblRateTableRate.EffectiveDate,
					tblRateTableRate.RateTableRateID
				 FROM tblRateTableRate
				 	 INNER JOIN tmp_RateTable_ r on tblRateTableRate.RateID = r.RateID
					 	 WHERE tblRateTableRate.RateTableId = p_RateTableId
						  GROUP BY tblRateTableRate.RateID;
	
		INSERT INTO tmp_Insert_RateTable_
		SELECT r.RateID,p_EffectiveDate,r.RateTableRateID
			 FROM tmp_all_RateId_ r
			 	 LEFT JOIN tmp_Update_RateTable_ ur
					 ON r.RateID=ur.RateID
			   WHERE ur.RateID is null ;
				
		
		INSERT INTO tblRateTableRate (
			RateID,
			RateTableId,
			Rate,
			EffectiveDate,
			created_at,
			CreatedBy,
			Interval1,
			IntervalN,
			ConnectionFee
		)
	    SELECT DISTINCT  tr.RateID,
		 						RateTableId,
		 						CASE WHEN p_Update_Rate = 1
									THEN    
										p_Rate									
									ELSE
										tr.Rate
									END
								AS Rate,
								p_EffectiveDate as EffectiveDate,
								NOW() as created_at,
								p_ModifiedBy as CreatedBy,
								CASE WHEN p_Update_Interval1 = 1
									THEN    
										p_Interval1									
									ELSE
										tr.Interval1
									END
								AS Interval1,
								CASE WHEN p_Update_IntervalN = 1
									THEN    
										p_IntervalN									
									ELSE
										tr.IntervalN
									END
								AS IntervalN,
								CASE WHEN p_Update_ConnectionFee = 1
									THEN    
										p_ConnectionFee									
									ELSE
										tr.ConnectionFee
									END
								AS ConnectionFee	
			 FROM tblRateTableRate tr
		   	 INNER JOIN tmp_Insert_RateTable_ r
			 		 ON  r.RateID = tr.RateID
			 		 	AND r.RateTableRateID = tr.RateTableRateID
	   		 		AND  RateTableId = p_RateTableId; 
	
	END IF; 
	
	
	-- bulk update
	SET @stm = '';		
	
	IF p_Update_Rate = 1
	THEN
		SET @stm = CONCAT(@stm,',Rate = ',p_Rate);		
	END IF;	
	
	IF p_Update_Interval1 = 1
	THEN
		SET @stm = CONCAT(@stm,',Interval1 = ',p_Interval1);		
	END IF;	
	
	IF p_Update_IntervalN = 1
	THEN
		SET @stm = CONCAT(@stm,',IntervalN = ',p_IntervalN);		
	END IF;	
	
	IF p_Update_ConnectionFee = 1
	THEN
		SET @stm = CONCAT(@stm,',ConnectionFee = ',p_ConnectionFee);		
	END IF;	
	
	IF @stm != ''
	THEN					
			SET @stm = CONCAT('
							UPDATE tblRateTableRate tr
						    INNER JOIN tmp_Update_RateTable_ r
							 	 ON  r.RateID = tr.RateID 
							 	 	AND r.RateTableRateID = tr.RateTableRateID  
										SET updated_at=NOW(),ModifiedBy="',p_ModifiedBy,'"',@stm,'
								    WHERE  RateTableId = ',p_RateTableId,';
						');
						
			PREPARE stmt FROM @stm;
			EXECUTE stmt;
			DEALLOCATE PREPARE stmt;
							
	END IF;						
		
	CALL prc_ArchiveOldRateTableRate(p_RateTableId); 
	
	END IF;
	
	IF p_action = 2
	THEN
		DELETE tblRateTableRate
			 FROM tblRateTableRate
				INNER JOIN tmp_RateTable_ 
					ON tblRateTableRate.RateTableRateID = tmp_RateTable_.RateTableRateID;
		
		
	END IF;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	 
END