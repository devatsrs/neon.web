USE `NeonRMDev`;

-- Dumping structure for procedure Ratemanagement3.prc_GetRateTableRate
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetRateTableRate`(
	IN `p_companyid` INT,
	IN `p_RateTableId` INT,
	IN `p_trunkID` INT,
	IN `p_contryID` INT,
	IN `p_code` VARCHAR(50),
	IN `p_description` VARCHAR(50),
	IN `p_effective` VARCHAR(50),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
BEGIN
	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRate_;
   CREATE TEMPORARY TABLE tmp_RateTableRate_ (
        ID INT,
        Code VARCHAR(50),
        Description VARCHAR(200),
        Interval1 INT,
        IntervalN INT,
		  ConnectionFee DECIMAL(18, 6),
        Rate DECIMAL(18, 6),
        EffectiveDate DATE,
        updated_at DATETIME,
        ModifiedBy VARCHAR(50),
        RateTableRateID INT,
        RateID INT,
        INDEX tmp_RateTableRate_RateID (`RateID`)
    );
    
    
    
    INSERT INTO tmp_RateTableRate_
    SELECT
        RateTableRateID AS ID,
        Code,
        Description,
        ifnull(tblRateTableRate.Interval1,1) as Interval1,
        ifnull(tblRateTableRate.IntervalN,1) as IntervalN,
		  tblRateTableRate.ConnectionFee,
        IFNULL(tblRateTableRate.Rate, 0) as Rate,
        IFNULL(tblRateTableRate.EffectiveDate, NOW()) as EffectiveDate,
        tblRateTableRate.updated_at,
        tblRateTableRate.ModifiedBy,
        RateTableRateID,
        tblRate.RateID
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
		   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_RateTableRate4_ as (select * from tmp_RateTableRate_);	        
         DELETE n1 FROM tmp_RateTableRate_ n1, tmp_RateTableRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate 
		   AND  n1.RateID = n2.RateID;
		END IF;
	 
    IF p_isExport = 0
    THEN

       SELECT * FROM tmp_RateTableRate_
					ORDER BY CASE
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
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNDESC') THEN Interval1
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IntervalNASC') THEN Interval1
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateDESC') THEN EffectiveDate
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateASC') THEN EffectiveDate
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN updated_at
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN updated_at
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byDESC') THEN ModifiedBy
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byASC') THEN ModifiedBy
                END ASC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableRateIDDESC') THEN RateTableRateID
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateTableRateIDASC') THEN RateTableRateID
                END ASC,				
				    CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeDESC') THEN ConnectionFee
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeASC') THEN ConnectionFee
                END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;



        SELECT
            COUNT(RateID) AS totalcount
        FROM tmp_RateTableRate_;

        


    END IF;

    IF p_isExport = 1
    THEN

        SELECT
            Code,
            Description,
            Interval1,
            IntervalN,
            ConnectionFee,
            Rate,
            EffectiveDate,
            updated_at,
            ModifiedBy

        FROM   tmp_RateTableRate_;
         

    END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER;
