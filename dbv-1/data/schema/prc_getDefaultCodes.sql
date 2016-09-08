CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getDefaultCodes`(IN `p_CompanyID` INT)
BEGIN

	DROP TEMPORARY TABLE IF EXISTS tmp_codes_;
	CREATE TEMPORARY TABLE tmp_codes_ (
		RateID INT,
		Code VARCHAR(50),
		INDEX tmp_codes_RateID (`RateID`),
		INDEX tmp_codes_Code (`Code`)
	);

	INSERT INTO tmp_codes_
	SELECT
	DISTINCT
		tblRate.RateID,
		tblRate.Code
	FROM tblRate
	INNER JOIN tblCodeDeck
		ON tblCodeDeck.CodeDeckId = tblRate.CodeDeckId
	WHERE tblCodeDeck.CompanyId = p_CompanyID
	AND tblCodeDeck.DefaultCodedeck = 1 ;

END