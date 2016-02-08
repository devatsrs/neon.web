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
      RateTableRateID INT
    );
	 	if(p_action=1 or p_action=2)
	THEN
		INSERT INTO tmp_criteria_
		SELECT
			RateTableRateID

        FROM (SELECT
				tblRateTableRate.RateTableRateID,
                Code,
                Description,
                tblRateTableRate.Interval1,
                tblRateTableRate.IntervalN,
                IFNULL(tblRateTableRate.Rate, 0) as Rate,
                IFNULL(tblRateTableRate.EffectiveDate, NOW()) as EffectiveDate,
                tblRateTableRate.updated_at,
                tblRateTableRate.ModifiedBy,
                @row_num := IF(@prev_RateID=tblRateTableRate.RateID and @prev_effectivedate >= tblRateTableRate.EffectiveDate ,@row_num+1,1) AS RowID,
                @prev_RateID  := tblRateTableRate.RateID,
					 @prev_effectivedate  := tblRateTableRate.EffectiveDate,
					 @prev_RateTableId := tblRateTableRate.RateTableId
            FROM tblRate
            LEFT JOIN tblRateTableRate
                ON tblRateTableRate.RateID = tblRate.RateID
                AND tblRateTableRate.RateTableId = p_RateTableId
            INNER JOIN tblRateTable
                ON tblRateTable.RateTableId = tblRateTableRate.RateTableId
         	,(SELECT @row_num := 1) x,(SELECT @prev_RateID := '') y,(SELECT @prev_effectivedate := '') z,(SELECT @prev_RateTableId := '') a
            WHERE		(tblRate.CompanyID = p_companyid)
					AND (p_contryID = '' OR CountryID = p_contryID)
					AND (p_code = '' OR Code LIKE REPLACE(p_code, '*', '%'))
					AND (p_description = '' OR Description LIKE REPLACE(p_description, '*', '%'))
					AND TrunkID = p_trunkID
					AND (			
							p_effective = 'All' 
						OR (p_effective = 'Now' AND EffectiveDate <= NOW() )
						OR (p_effective = 'Future' AND EffectiveDate > NOW())
						)
			) TBL
        WHERE	(
						p_effective = 'All' 
					OR (p_effective = 'Now' AND EffectiveDate <= NOW() AND RowID = 1) 
					OR (p_effective = 'Future' AND EffectiveDate > NOW())
				);
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