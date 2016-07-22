CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getDiscountPlan`(IN `p_CompanyID` INT, IN `p_Name` VARCHAR(50), IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT)
BEGIN
	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	IF p_isExport = 0
	THEN
		SELECT   
			dp.Name,
			dp.UpdatedBy,
			dp.updated_at,
			dp.DiscountPlanID,
			dp.DestinationGroupSetID,
			dp.CurrencyID,
			dp.Description

		FROM tblDiscountPlan dp
		WHERE dp.CompanyID = p_CompanyID
			AND (p_Name ='' OR dp.Name like  CONCAT('%',p_Name,'%'))
		ORDER BY
			CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameDESC') THEN Name
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameASC') THEN Name
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreatedByDESC') THEN CreatedBy
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreatedByASC') THEN CreatedBy
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN created_at
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN created_at
			END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT
			COUNT(dp.DiscountPlanID) AS totalcount
		FROM tblDiscountPlan dp
		WHERE dp.CompanyID = p_CompanyID
			AND (p_Name ='' OR dp.Name like  CONCAT('%',p_Name,'%'));
	END IF;

	IF p_isExport = 1
	THEN
		
		SELECT   
			dp.Name,
			dp.UpdatedBy,
			dp.updated_at,
			dp.Description
		FROM tblDiscountPlan dp
		WHERE dp.CompanyID = p_CompanyID
			AND (p_Name ='' OR dp.Name like  CONCAT('%',p_Name,'%'));

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END