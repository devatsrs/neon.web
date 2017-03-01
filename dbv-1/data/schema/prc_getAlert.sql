CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getAlert`(
	IN `p_CompanyID` INT,
	IN `p_AlertGroup` VARCHAR(50),
	IN `p_AlertType` VARCHAR(50),
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

	IF p_isExport = 0
	THEN

		SELECT
			Name,
			AlertType,
			Status,
			LowValue,
			HighValue,
			updated_at,
			UpdatedBy,
			AlertID,
			Settings
		FROM tblAlert
		WHERE  CompanyID = p_CompanyID
			AND CreatedByCustomer = 0 
			AND (p_AlertGroup = '' OR AlertGroup = p_AlertGroup)
			AND (p_AlertType = '' OR AlertType = p_AlertType)
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
			COUNT(AlertID) AS totalcount
		FROM tblAlert
		WHERE  CompanyID = p_CompanyID
			AND CreatedByCustomer = 0 
			AND (p_AlertGroup = '' OR AlertGroup = p_AlertGroup)
			AND (p_AlertType = '' OR AlertType = p_AlertType);

	END IF;

	IF p_isExport = 1
	THEN
	
		SELECT
			Name,
			UpdatedBy,
			updated_at
		FROM tblAlert
		WHERE  CompanyID = p_CompanyID
			AND CreatedByCustomer = 0 
			AND (p_AlertGroup = '' OR AlertGroup = p_AlertGroup)
			AND (p_AlertType = '' OR AlertType = p_AlertType);
	
	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END