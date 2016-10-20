CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getDiscount`(IN `p_CompanyID` INT, IN `p_DiscountPlanID` INT, IN `p_Name` VARCHAR(50), IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT)
BEGIN
	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	IF p_isExport = 0
	THEN
		SELECT   
			dg.Name,
			ROUND(ds.Threshold/60,0) as Threshold,
			ds.Discount,
			IF(ds.Unlimited =1 ,'Unlimited','') as UnlimitedText,
			dp.UpdatedBy,
			dp.updated_at,
			dp.DiscountID,
			dp.DiscountPlanID,
			dp.DestinationGroupID,
			ds.DiscountSchemeID,
			dp.Service,
			ds.Unlimited
		FROM tblDiscount dp
		INNER JOIN tblDestinationGroup dg 
			ON dg.DestinationGroupID = dp.DestinationGroupID
		INNER JOIN tblDiscountScheme ds 
			ON ds.DiscountID = dp.DiscountID
		WHERE dp.DiscountPlanID = p_DiscountPlanID
			AND (p_Name ='' OR dg.Name like  CONCAT('%',p_Name,'%'))
		ORDER BY
			CASE
					WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameDESC') THEN dg.Name
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameASC') THEN dg.Name
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
			COUNT(dp.DiscountID) AS totalcount
		FROM tblDiscount dp
		INNER JOIN tblDestinationGroup dg ON dg.DestinationGroupID = dp.DestinationGroupID
		INNER JOIN tblDiscountScheme ds ON ds.DiscountID = dp.DiscountID
		WHERE dp.DiscountPlanID = p_DiscountPlanID
			AND (p_Name ='' OR dg.Name like  CONCAT('%',p_Name,'%'));
	END IF;

	IF p_isExport = 1
	THEN
		
		SELECT   
			dg.Name,
			dp.Service,
			ds.Threshold,
			ds.Discount,
			IF(ds.Unlimited =1 ,'Unlimited','') as Unlimited,
			dp.UpdatedBy,
			dp.updated_at
		FROM tblDiscount dp
		INNER JOIN tblDestinationGroup dg ON dg.DestinationGroupID = dp.DestinationGroupID
		INNER JOIN tblDiscountScheme ds ON ds.DiscountID = dp.DiscountID
		WHERE dp.DiscountPlanID = p_DiscountPlanID
			AND (p_Name ='' OR dg.Name like  CONCAT('%',p_Name,'%'));

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END