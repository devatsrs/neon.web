CREATE DEFINER=`neon-user`@`%` PROCEDURE `prc_getDiscontinuedVendorRateGrid`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_TrunkID` INT,
	IN `p_CountryID` INT,
	IN `p_Code` VARCHAR(50),
	IN `p_Description` VARCHAR(50),
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
	
	DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
   CREATE TEMPORARY TABLE tmp_VendorRate_ (
        VendorRateID INT,
        Code VARCHAR(50),
        Description VARCHAR(200),
		  ConnectionFee VARCHAR(50),
        Interval1 INT,
        IntervalN INT,
        Rate DECIMAL(18, 6),
        EffectiveDate DATE,
        EndDate DATE,
        updated_at DATETIME,
        updated_by VARCHAR(50),
        INDEX tmp_VendorRate_RateID (`Code`)
   );
   
   INSERT INTO tmp_VendorRate_
		SELECT
			vra.VendorRateID,
			r.Code,
			r.Description,
			'' AS ConnectionFee,
			CASE WHEN vra.Interval1 IS NOT NULL THEN vra.Interval1 ELSE r.Interval1 END AS Interval1,
			CASE WHEN vra.IntervalN IS NOT NULL THEN vra.IntervalN ELSE r.IntervalN END AS IntervalN,
			vra.Rate,
			vra.EffectiveDate,
			vra.EndDate,
			vra.created_at AS updated_at,
			vra.created_by AS updated_by
		FROM
			tblVendorRateArchive vra
		JOIN
			tblRate r ON r.RateID=vra.RateId
		LEFT JOIN
			tblVendorRate vr ON vr.AccountId = vra.AccountId AND vr.TrunkID = vra.TrunkID AND vr.RateId = vra.RateId
		WHERE
			r.CompanyID = p_CompanyID AND
			vra.TrunkID = p_TrunkID AND
			vra.AccountId = p_AccountID AND
			(p_CountryID IS NULL OR r.CountryID = p_CountryID) AND 
			(p_code IS NULL OR r.Code LIKE REPLACE(p_code, '*', '%')) AND 
			(p_description IS NULL OR r.Description LIKE REPLACE(p_description, '*', '%')) AND
			vr.VendorRateID is NULL;
	
	IF p_isExport = 0
	THEN
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRate2_ as (select * from tmp_VendorRate_);
		DELETE 
			n1 
		FROM 
			tmp_VendorRate_ n1, tmp_VendorRate2_ n2
		WHERE
			n1.Code = n2.Code AND n1.VendorRateID < n2.VendorRateID;
	
 		SELECT
			VendorRateID,
			Code,
			Description,
			ConnectionFee,
			Interval1,
			IntervalN,
			Rate,
			EffectiveDate,
			EndDate,
			updated_at,
			updated_by
		FROM  
			tmp_VendorRate_
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
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeDESC') THEN ConnectionFee
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeASC') THEN ConnectionFee
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
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateDESC') THEN EffectiveDate
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EffectiveDateASC') THEN EffectiveDate
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateDESC') THEN EndDate
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateASC') THEN EndDate
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN updated_at
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN updated_at
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byDESC') THEN updated_by
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_byASC') THEN updated_by
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'VendorRateIDDESC') THEN VendorRateID
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'VendorRateIDASC') THEN VendorRateID
			END ASC
		LIMIT 
			p_RowspPage 
		OFFSET 
			v_OffSet_;

		SELECT
			COUNT(code) AS totalcount
		FROM tmp_VendorRate_;
	
	END IF;
	
   IF p_isExport = 1
	THEN
		SELECT
			Code,
			Description,
			Rate,
			EffectiveDate,
			EndDate,
			updated_at AS `Modified Date`,
			updated_by AS `Modified By`

		FROM tmp_VendorRate_;
	END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END