CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_RateTableRateInsertUpdate`(IN `p_RateTableRateID` LONGTEXT
, IN `p_RateTableId` int 
, IN `p_Rate` DECIMAL(18, 6) 
, IN `p_EffectiveDate` DATETIME
, IN `p_ModifiedBy` VARCHAR(50)
, IN `p_Interval1` int
, IN `p_IntervalN` int
, IN `p_ConnectionFee` DECIMAL(18, 6)
, IN `p_companyid` INT, IN `p_trunkID` INT, IN `p_contryID` INT, IN `p_code` VARCHAR(50), IN `p_description` VARCHAR(50), IN `p_effective` VARCHAR(50), IN `p_action` INT)
BEGIN
              
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    DROP TEMPORARY TABLE IF EXISTS tmp_updaterateid_;
    CREATE TEMPORARY TABLE tmp_updaterateid_ (
      RateID INT
    ); 
    
    DROP TEMPORARY TABLE IF EXISTS tmp_insertrateid_;
    CREATE TEMPORARY TABLE tmp_insertrateid_ (
      RateID INT
    );
	 
	 DROP TEMPORARY TABLE IF EXISTS tmp_criteria_;
    CREATE TEMPORARY TABLE tmp_criteria_ (
      RateTableRateID INT,
      INDEX tmp_criteria_RateTableRateID (`RateTableRateID`)
    );
	 	if(p_action=1 or p_action=2)
	THEN
	
		DROP TEMPORARY TABLE IF EXISTS tmp_RateTable_;
      CREATE TEMPORARY TABLE tmp_RateTable_ (
         RateId INT,
         EffectiveDate DATE,
         RateTableRateID INT,
			INDEX tmp_RateTable_RateId (`RateId`)  
     );
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
            WHERE		(tblRate.CompanyID = p_companyid)
					AND (p_contryID = '' OR CountryID = p_contryID)
					AND (p_code = '' OR Code LIKE REPLACE(p_code, '*', '%'))
					AND (p_description = '' OR Description LIKE REPLACE(p_description, '*', '%'))
					AND TrunkID = p_trunkID
					AND (			
							p_effective = 'All' 
						OR (p_effective = 'Now' AND EffectiveDate <= NOW() )
						OR (p_effective = 'Future' AND EffectiveDate > NOW())
						);
		 
		  IF p_effective = 'Now'
		  THEN 
			 CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTable4_ as (select * from tmp_RateTable_);	        
          DELETE n1 FROM tmp_RateTable_ n1, tmp_RateTable4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate 
	 	   AND  n1.RateId = n2.RateId;
		  
		  END IF;
		  
		  INSERT INTO tmp_criteria_
		  SELECT RateTableRateID FROM tmp_RateTable_;
		  
		  
		   
	END IF;                  
     
                    
    INSERT INTO tmp_updaterateid_
    SELECT r.RateID
    from
    (SELECT RateID FROM tblRateTableRate WHERE RateTableId = p_RateTableId   
		AND (p_RateTableRateID='' or  FIND_IN_SET(RateTableRateID,p_RateTableRateID) != 0 )
		AND (p_action=0 or RateTableRateID IN ( SELECT RateTableRateID FROM tmp_criteria_)) 
	 		
	 ) r
    LEFT OUTER JOIN (
		SELECT RateID FROM tblRateTableRate WHERE RateTableId = p_RateTableId  AND EffectiveDate = p_EffectiveDate 
    ) cr on r.RateID = cr.RateID WHERE cr.RateID IS NOT NULL;

	
    
    UPDATE tblRateTableRate tr
    LEFT JOIN tmp_updaterateid_ r ON  r.RateID = tr.RateID
    SET Rate = p_Rate ,updated_at = NOW(),ModifiedBy =   p_ModifiedBy,Interval1 = p_Interval1,IntervalN = p_IntervalN,ConnectionFee= p_ConnectionFee
    WHERE  RateTableId = p_RateTableId  AND EffectiveDate = p_EffectiveDate
    and r.RateID is NOT NULL;

    INSERT INTO tmp_insertrateid_
    SELECT r.RateID
    
    from
    (SELECT RateID FROM tblRateTableRate WHERE RateTableId = p_RateTableId AND FIND_IN_SET(RateTableRateID,p_RateTableRateID) != 0 ) r 
    
    LEFT OUTER JOIN (
     SELECT RateID FROM tblRateTableRate WHERE RateTableId = p_RateTableId AND ( Rate = p_Rate OR Rate != p_Rate) AND EffectiveDate = p_EffectiveDate 
    ) cr on r.RateID = cr.RateID WHERE cr.RateID IS NULL;
    
    
    
    
    INSERT INTO tblRateTableRate (RateID,RateTableId,Rate,EffectiveDate,created_at,CreatedBy,Interval1,IntervalN,ConnectionFee)
    SELECT DISTINCT  tr.RateID,RateTableId,p_Rate,p_EffectiveDate,NOW(),p_ModifiedBy,p_Interval1,p_IntervalN,p_ConnectionFee FROM tblRateTableRate tr
    LEFT JOIN tmp_insertrateid_ r ON  r.RateID = tr.RateID
    WHERE  RateTableId = p_RateTableId
    and r.RateID is NOT NULL;
      
   CALL prc_ArchiveOldRateTableRate(p_RateTableId);
   
   if(p_action=2)
	THEN
		DELETE tblRateTableRate FROM tblRateTableRate
		INNER JOIN tmp_criteria_ ON tblRateTableRate.RateTableRateID = tmp_criteria_.RateTableRateID;
		
		
	END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	 
END