CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_getReviewRateTableRates`(
	IN `p_ProcessID` VARCHAR(50),
	IN `p_Action` VARCHAR(50),
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

	IF p_isExport = 0
	THEN
		SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
		SELECT
			distinct 
			IF(p_Action='Deleted',RateTableRateID,TempRateTableRateID) AS RateTableRateID,
			`Code`,`Description`,`Rate`,`EffectiveDate`,`EndDate`,`ConnectionFee`,`Interval1`,`IntervalN`
		FROM 
			tblRateTableRateChangeLog
		WHERE   
			ProcessID = p_ProcessID AND Action = p_Action 
			AND
			(p_Code IS NULL OR p_Code = '' OR Code LIKE REPLACE(p_Code, '*', '%'))
			AND 
			(p_Description IS NULL OR p_Description = '' OR Description LIKE REPLACE(p_Description, '*', '%'))
		ORDER BY
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
			END DESC,
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
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeDESC') THEN ConnectionFee
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ConnectionFeeASC') THEN ConnectionFee
			END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;
	
		SELECT
			COUNT(*) AS totalcount
		FROM 
			tblRateTableRateChangeLog
		WHERE   
			ProcessID = p_ProcessID AND Action = p_Action 
			AND
			(p_Code IS NULL OR p_Code = '' OR Code LIKE REPLACE(p_Code, '*', '%'))
			AND 
			(p_Description IS NULL OR p_Description = '' OR Description LIKE REPLACE(p_Description, '*', '%'));
	END IF;
	
	IF p_isExport = 1
	THEN
		SELECT
			distinct 
			`Code`,`Description`,`Rate`,`EffectiveDate`,`EndDate`,`ConnectionFee`,`Interval1`,`IntervalN`
		FROM 
			tblRateTableRateChangeLog
		WHERE
			ProcessID = p_ProcessID AND Action = p_Action 
			AND
			(p_Code IS NULL OR p_Code = '' OR Code LIKE REPLACE(p_Code, '*', '%'))
			AND 
			(p_Description IS NULL OR p_Description = '' OR Description LIKE REPLACE(p_Description, '*', '%'));
	END IF;

	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END