CREATE DEFINER=`root`@`localhost` PROCEDURE `fnDistinctList`(
	IN `p_CompanyID` INT
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	INSERT INTO tblRRate(Code,CompanyID,CountryID)
	SELECT tbl.AreaPrefix,tbl.CompanyID,tbl.CountryID FROM (SELECT DISTINCT AreaPrefix,CountryID,CompanyID FROM tmp_UsageSummary)tbl
	LEFT JOIN tblRRate
		ON	tbl.AreaPrefix = tblRRate.Code
		AND tbl.CompanyID = tblRRate.CompanyID
	WHERE tblRRate.CompanyID = p_CompanyID
	AND tbl.AreaPrefix IS NULL;
	
	INSERT INTO tblRTrunk(Trunk,CompanyID)
	SELECT tbl.Trunk,tbl.CompanyID FROM (SELECT DISTINCT Trunk,CompanyID FROM tmp_UsageSummary)tbl
	LEFT JOIN tblRTrunk
		ON	tbl.Trunk = tblRTrunk.Trunk
		AND tbl.CompanyID = tblRTrunk.CompanyID
	WHERE tblRTrunk.CompanyID = p_CompanyID
	AND tbl.Trunk IS NULL;
	
	INSERT INTO tblRRate(Code,CompanyID,CountryID)
	SELECT tbl.AreaPrefix,tbl.CompanyID,tbl.CountryID FROM (SELECT DISTINCT AreaPrefix,CountryID,CompanyID FROM tmp_VendorUsageSummary)tbl
	LEFT JOIN tblRRate
		ON	tbl.AreaPrefix = tblRRate.Code
		AND tbl.CompanyID = tblRRate.CompanyID
	WHERE tblRRate.CompanyID = p_CompanyID
	AND tbl.AreaPrefix IS NULL;
	
	INSERT INTO tblRTrunk(Trunk,CompanyID)
	SELECT tbl.Trunk,tbl.CompanyID FROM (SELECT DISTINCT Trunk,CompanyID FROM tmp_VendorUsageSummary)tbl
	LEFT JOIN tblRTrunk
		ON	tbl.Trunk = tblRTrunk.Trunk
		AND tbl.CompanyID = tblRTrunk.CompanyID
	WHERE tblRTrunk.CompanyID = p_CompanyID
	AND tbl.Trunk IS NULL;

END