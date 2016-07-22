CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getDestinationGroup`(IN `p_CompanyID` INT, IN `p_DestinationGroupSetID` INT, IN `p_Name` VARCHAR(50), IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT)
BEGIN
	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	IF p_isExport = 0
	THEN
		SELECT   
			dg.Name,
			CONCAT(SUBSTRING_INDEX(GROUP_CONCAT(r.Code ORDER BY r.Code ASC SEPARATOR ','), ',', 10),'...') as Code,
			dg.CreatedBy,
			dg.created_at,
			dg.DestinationGroupID,
			dg.DestinationGroupSetID
		FROM tblDestinationGroup dg
		INNER JOIN tblDestinationGroupCode dgc
		ON dg.DestinationGroupID =  dgc.DestinationGroupID
		INNER JOIN tblRate r 
			ON r.RateID = dgc.RateID
		WHERE dg.DestinationGroupSetID = p_DestinationGroupSetID
			AND (p_Name ='' OR dg.Name like  CONCAT('%',p_Name,'%'))
		GROUP BY dgc.DestinationGroupID
		ORDER BY
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameDESC') THEN dg.Name
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameASC') THEN dg.Name
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreatedByDESC') THEN dg.CreatedBy
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CreatedByASC') THEN dg.CreatedBy
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atDESC') THEN dg.created_at
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'created_atASC') THEN dg.created_at
			END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT
			COUNT(dg.DestinationGroupID) AS totalcount
		FROM tblDestinationGroup dg
		INNER JOIN tblDestinationGroupCode dgc
		ON dg.DestinationGroupID =  dgc.DestinationGroupID
		INNER JOIN tblRate r 
			ON r.RateID = dgc.RateID
		WHERE dg.DestinationGroupSetID = p_DestinationGroupSetID
			AND (p_Name ='' OR dg.Name like  CONCAT('%',p_Name,'%'));
	END IF;

	IF p_isExport = 1
	THEN
		
		SELECT
			dg.Name as `Destionatio Group Name`,
			GROUP_CONCAT(r.Code ORDER BY r.Code ASC) as `Destination Codes`,
			dg.CreatedBy,
			dg.created_at
		FROM tblDestinationGroup dg
		INNER JOIN tblDestinationGroupCode dgc
			ON dg.DestinationGroupID =  dgc.DestinationGroupID
		INNER JOIN tblRate r 
			ON r.RateID = dgc.RateID
		WHERE dg.DestinationGroupSetID = p_DestinationGroupSetID
			AND (p_Name ='' OR dg.Name like  CONCAT('%',p_Name,'%'))
		GROUP BY dgc.DestinationGroupID;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END