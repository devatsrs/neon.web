CREATE DEFINER=`root`@`localhost` PROCEDURE `fnDistinctList`(
	IN `p_CompanyID` INT
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	INSERT IGNORE INTO tblRRate(Code,CountryID,CompanyID)
	SELECT DISTINCT AreaPrefix,CountryID,CompanyID FROM tmp_UsageSummary
	WHERE tmp_UsageSummary.CompanyID = p_CompanyID;

	INSERT IGNORE INTO tblRTrunk(Trunk,CompanyID)
	SELECT DISTINCT Trunk,CompanyID FROM tmp_UsageSummary
	WHERE tmp_UsageSummary.CompanyID = p_CompanyID;

	INSERT IGNORE INTO tblRRate(Code,CountryID,CompanyID)
	SELECT DISTINCT AreaPrefix,CountryID,CompanyID FROM tmp_VendorUsageSummary
	WHERE tmp_VendorUsageSummary.CompanyID = p_CompanyID;

	INSERT IGNORE INTO tblRTrunk(Trunk,CompanyID)
	SELECT DISTINCT Trunk,CompanyID FROM tmp_VendorUsageSummary
	WHERE tmp_VendorUsageSummary.CompanyID = p_CompanyID;

END