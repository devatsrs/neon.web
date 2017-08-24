CREATE DEFINER=`root`@`localhost` PROCEDURE `fngetDefaultCodes`(
	IN `p_CompanyID` INT
)
BEGIN
	
	DROP TEMPORARY TABLE IF EXISTS tmp_codes_;
	CREATE TEMPORARY TABLE tmp_codes_ (
		CountryID INT,
		Code VARCHAR(50),
		Description VARCHAR(200),
		INDEX tmp_codes_CountryID (`CountryID`),
		INDEX tmp_codes_Code (`Code`)
	);

	INSERT INTO tmp_codes_
	SELECT
	DISTINCT
		tblRate.CountryID,
		tblRate.Code,
		tblRate.Description
	FROM NeonRMDev.tblRate
	INNER JOIN NeonRMDev.tblCodeDeck
		ON tblCodeDeck.CodeDeckId = tblRate.CodeDeckId
	WHERE tblCodeDeck.CompanyId = p_CompanyID
	AND tblCodeDeck.DefaultCodedeck = 1 ;
	
END