CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getBillingClass`(IN `p_CompanyID` INT, IN `p_Name` VARCHAR(50), IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT)
BEGIN

	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	IF p_isExport = 0
	THEN

		SELECT
			Name,
			UpdatedBy,
			updated_at,
			BillingClassID,
			(SELECT COUNT(*) FROM tblAccountBilling a WHERE a.BillingClassID =  tblBillingClass.BillingClassID) as Applied
		FROM tblBillingClass
		WHERE  CompanyID = p_CompanyID 
			AND (p_Name = '' OR Name LIKE REPLACE(p_Name, '*', '%'))
		ORDER BY
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameDESC') THEN Name
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameASC') THEN Name
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'UpdatedByDESC') THEN UpdatedBy
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'UpdatedByASC') THEN UpdatedBy
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atDESC') THEN updated_at
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'updated_atASC') THEN updated_at
			END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT
			COUNT(BillingClassID) AS totalcount
		FROM tblBillingClass
		WHERE  CompanyID = p_CompanyID 
			AND (p_Name = '' OR Name LIKE REPLACE(p_Name, '*', '%'));

	END IF;

	IF p_isExport = 1
	THEN
	
		SELECT
			Name,
			UpdatedBy,
			updated_at
		FROM tblBillingClass
		WHERE  CompanyID = p_CompanyID 
			AND (p_Name = '' OR Name LIKE REPLACE(p_Name, '*', '%'));
	
	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END