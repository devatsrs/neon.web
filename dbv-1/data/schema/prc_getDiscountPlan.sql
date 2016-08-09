CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getDiscountPlan`(IN `p_CompanyID` INT, IN `p_Name` VARCHAR(50), IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT)
BEGIN
	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	IF p_isExport = 0
	THEN
		SELECT   
			dp.Name,
			dgs.Name as DestinationGroupSet,
			c.Code as Currency,
			dp.UpdatedBy,
			dp.updated_at,
			dp.DiscountPlanID,
			dp.DestinationGroupSetID,
			dp.CurrencyID,
			dp.Description,
			(SELECT adp.DiscountPlanID FROM tblAccountDiscountPlan adp WHERE adp.DiscountPlanID = dp.DiscountPlanID LIMIT 1)as Applied
		FROM tblDiscountPlan dp
		INNER JOIN tblDestinationGroupSet dgs
			ON dgs.DestinationGroupSetID = dp.DestinationGroupSetID
		INNER JOIN tblCurrency c
			ON c.CurrencyId = dp.CurrencyID
		
		WHERE dp.CompanyID = p_CompanyID
			AND (p_Name ='' OR dp.Name like  CONCAT('%',p_Name,'%'))
		ORDER BY
			CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameDESC') THEN dp.Name
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameASC') THEN dp.Name
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreatedByDESC') THEN dp.CreatedBy
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreatedByASC') THEN dp.CreatedBy
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN dp.created_at
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN dp.created_at
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