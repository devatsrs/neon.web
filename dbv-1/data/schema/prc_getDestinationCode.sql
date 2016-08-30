CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getDestinationCode`(IN `p_DestinationGroupSetID` INT, IN `p_DestinationGroupID` INT, IN `p_CountryID` INT, IN `p_Code` VARCHAR(50), IN `p_Selected` INT, IN `p_Description` VARCHAR(50), IN `p_PageNumber` INT, IN `p_RowspPage` INT)
BEGIN
		
	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	SELECT
		r.RateID,
		r.Code,
		r.Description,
		IF(DestinationGroupCodeID >0,1,0) as DestinationGroupCodeID
	FROM tblRate r
	INNER JOIN tblDestinationGroupSet dg
		ON dg.CodedeckID =r.CodeDeckId
	LEFT JOIN tblDestinationGroupCode dgc
		ON DestinationGroupID = p_DestinationGroupID
		AND dgc.RateID = r.RateID
	WHERE dg.DestinationGroupSetID = p_DestinationGroupSetID
	AND (p_Code = '' OR Code LIKE REPLACE(p_Code, '*', '%'))
	AND (p_Description = '' OR Description LIKE REPLACE(p_Description, '*', '%'))
	AND (p_CountryID = 0 OR r.CountryID = p_CountryID)
	AND (p_Selected = 0 OR (p_Selected = 1 AND DestinationGroupCodeID IS NOT NULL))
	LIMIT p_RowspPage OFFSET v_OffSet_;
	
	SELECT COUNT(*)  as totalcount
	FROM tblRate r
	INNER JOIN tblDestinationGroupSet dg
		ON dg.CodedeckID =r.CodeDeckId
	LEFT JOIN tblDestinationGroupCode dgc
		ON DestinationGroupID = p_DestinationGroupID
		AND dgc.RateID = r.RateID
	WHERE dg.DestinationGroupSetID = p_DestinationGroupSetID
	AND (p_Code = '' OR Code LIKE REPLACE(p_Code, '*', '%'))
	AND (p_Description = '' OR Description LIKE REPLACE(p_Description, '*', '%'))
	AND (p_CountryID = 0 OR r.CountryID = p_CountryID)
	AND (p_Selected = 0 OR (p_Selected = 1 AND DestinationGroupCodeID IS NOT NULL)); 

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END