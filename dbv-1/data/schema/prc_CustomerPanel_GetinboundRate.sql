CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_CustomerPanel_GetinboundRate`(IN `p_companyid` INT, IN `p_AccountID` INT, IN `p_ratetableid` INT, IN `p_trunkID` INT, IN `p_contryID` INT, IN `p_code` VARCHAR(50), IN `p_description` VARCHAR(50), IN `p_Effective` VARCHAR(50), IN `p_effectedRates` INT, IN `p_RoutinePlan` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT )
BEGIN
   DECLARE v_codedeckid_ INT;
   DECLARE v_ratetableid_ INT;
   DECLARE v_RateTableAssignDate_ DATETIME;
   DECLARE v_NewA2ZAssign_ INT;
   DECLARE v_OffSet_ int;
   DECLARE v_IncludePrefix_ INT;
   DECLARE v_Prefix_ VARCHAR(50);
   DECLARE v_RatePrefix_ VARCHAR(50);
   DECLARE v_AreaPrefix_ VARCHAR(50);
   
   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
   SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	SELECT CodeDeckId INTO v_codedeckid_ FROM tblRateTable where RateTableId=p_ratetableid and CompanyId=p_companyid;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
    CREATE TEMPORARY TABLE tmp_RateTableRate_ (
    	  RateID INT,	
        Code VARCHAR(50),
        Description VARCHAR(200),
        Interval1 INT,
        IntervalN INT,
        ConnectionFee DECIMAL(18, 6),
        Rate DECIMAL(18, 6),
        EffectiveDate DATE,
        INDEX tmp_RateTableRate_RateID (`RateID`)
    );
    
   INSERT INTO tmp_RateTableRate_
	select 
	      r.RateID,
			r.Code,
			r.Description,			
			ifnull(rtr.Interval1,1) as Interval1,
         ifnull(rtr.IntervalN,1) as IntervalN,
			rtr.ConnectionFee,
			IFNULL(rtr.Rate, 0) as Rate,
			IFNULL(rtr.EffectiveDate, NOW()) as EffectiveDate
		 from tblRate r LEFT JOIN tblRateTableRate rtr on r.RateID=rtr.RateID
		 AND r.CodeDeckId = v_codedeckid_ AND  
						 (
						 	( p_Effective = 'Now' AND rtr.EffectiveDate <= NOW() )
						 	OR
						 	( p_Effective = 'Future' AND rtr.EffectiveDate > NOW())
						 	OR p_Effective = 'All'
						 )
		 where rtr.RateTableId = p_ratetableid AND r.CompanyID=p_companyid
		  AND	(p_contryID IS NULL OR r.CountryID = p_contryID)
        AND (p_code IS NULL OR r.Code LIKE REPLACE(p_code, '*', '%'))
        AND (p_description IS NULL OR r.Description LIKE REPLACE(p_description, '*', '%'));
        
        IF p_Effective = 'Now'
		THEN
		   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate4_ as (select * from tmp_RateTableRate_);	        
         DELETE n1 FROM tmp_RateTableRate_ n1, tmp_RateTableRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate 
		   AND  n1.RateID = n2.RateID;
		END IF;
        
        IF p_isExport = 0
    THEN


         SELECT         
				RateID,
                Code,
                Description,
                Interval1,
                IntervalN,
                ConnectionFee,
                Rate,
                EffectiveDate
            FROM tmp_RateTableRate_ 
            ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN Description
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN Description
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateDESC') THEN Rate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateASC') THEN Rate
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Interval1DESC') THEN Interval1
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Interval1ASC') THEN Interval1
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNDESC') THEN IntervalN
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNASC') THEN IntervalN
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeDESC') THEN ConnectionFee
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeASC') THEN ConnectionFee
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateDESC') THEN EffectiveDate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateASC') THEN EffectiveDate
                END ASC
            LIMIT p_RowspPage OFFSET v_OffSet_;

        SELECT
            COUNT(RateID) AS totalcount
        FROM tmp_RateTableRate_;

    END IF;
    
    IF p_isExport = 1
    THEN
			 
          select 
            RateID,
                Code,
                Description,
                Interval1,
                IntervalN,
                ConnectionFee,
                Rate,
                EffectiveDate
            FROM tmp_RateTableRate_;

    END IF;
    
 
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END