CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getDestinationGroupSet`(IN `p_CompanyID` INT, IN `p_Name` VARCHAR(50), IN `p_CodedeckID` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT)
BEGIN
	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	IF p_isExport = 0
	THEN
		SELECT   
			dgs.Name,
			cd.CodeDeckName,
			dgs.CreatedBy,
			dgs.created_at,
			dgs.DestinationGroupSetID,
			dgs.CodedeckID,
			dgs.CompanyID,
			(SELECT adp.DiscountPlanID FROM tblDiscountPlan dp  LEFT JOIN tblAccountDiscountPlan adp ON adp.DiscountPlanID = dp.DiscountPlanID WHERE dp.DestinationGroupSetID = dgs.DestinationGroupSetID LIMIT 1) as Applied
		FROM tblDestinationGroupSet dgs
		INNER JOIN tblCodeDeck cd 
			ON cd.CodeDeckId = dgs.CodedeckID
		WHERE dgs.CompanyID = p_CompanyID
			AND (p_Name ='' OR dgs.Name like  CONCAT('%',p_Name,'%'))
			AND (p_CodedeckID = 0 OR dgs.CodedeckID = p_CodedeckID)
		ORDER BY
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameDESC') THEN dgs.Name
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameASC') THEN dgs.Name
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreatedByDESC') THEN dgs.CreatedBy
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreatedByASC') THEN dgs.CreatedBy
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN dgs.created_at
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN dgs.created_at
			END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT
			COUNT(dgs.DestinationGroupSetID) AS totalcount
		FROM tblDestinationGroupSet dgs
		INNER JOIN tblCodeDeck cd 
			ON cd.CodeDeckId = dgs.CodedeckID
		WHERE dgs.CompanyID = p_CompanyID
			AND (p_Name ='' OR dgs.Name like  CONCAT('%',p_Name,'%'))
			AND (p_CodedeckID = 0 OR dgs.CodedeckID = p_CodedeckID);
	END IF;

	IF p_isExport = 1
	THEN
		
		SELECT   
			dgs.Name,
			dgs.CreatedBy,
			dgs.created_at
		FROM tblDestinationGroupSet dgs
		INNER JOIN tblCodeDeck cd 
			ON cd.CodeDeckId = dgs.CodedeckID
		WHERE dgs.CompanyID = p_CompanyID
			AND (p_Name ='' OR dgs.Name like  CONCAT('%',p_Name,'%'))
			AND (p_CodedeckID = 0 OR dgs.CodedeckID = p_CodedeckID);

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END