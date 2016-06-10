CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getVendorCodeRate`(IN `p_AccountID` INT, IN `p_trunkID` INT, IN `p_RateCDR` INT)
BEGIN
 
	IF p_RateCDR = 0
	THEN 

		DROP TEMPORARY TABLE IF EXISTS tmp_vcodes_;
		CREATE TEMPORARY TABLE tmp_vcodes_ (
			RateID INT,
			Code VARCHAR(50),
			INDEX tmp_vcodes_RateID (`RateID`),
			INDEX tmp_vcodes_Code (`Code`)
		);

		INSERT INTO tmp_vcodes_
		SELECT
		DISTINCT
			tblRate.RateID,
			tblRate.Code
		FROM tblRate
		INNER JOIN tblVendorRate
		ON tblVendorRate.RateID = tblRate.RateID
		WHERE 
			 tblVendorRate.AccountId = p_AccountID
		AND tblVendorRate.TrunkID = p_trunkID
		AND tblVendorRate.EffectiveDate <= NOW();

	END IF;
	
	IF p_RateCDR = 1
	THEN 

		DROP TEMPORARY TABLE IF EXISTS tmp_vcodes_;
		CREATE TEMPORARY TABLE tmp_vcodes_ (
			RateID INT,
			Code VARCHAR(50),
			Rate Decimal(18,6),
			ConnectionFee Decimal(18,6),
			Interval1 INT,
			IntervalN INT,
			INDEX tmp_vcodes_RateID (`RateID`),
			INDEX tmp_vcodes_Code (`Code`)
		);
		
		INSERT INTO tmp_vcodes_
		SELECT
		DISTINCT
			tblRate.RateID,
			tblRate.Code,
			tblVendorRate.Rate,
			tblVendorRate.ConnectionFee,
			tblVendorRate.Interval1,
			tblVendorRate.IntervalN
		FROM tblRate
		INNER JOIN tblVendorRate
		ON tblVendorRate.RateID = tblRate.RateID
		WHERE 
			 tblVendorRate.AccountId = p_AccountID
		AND tblVendorRate.TrunkID = p_trunkID
		AND tblVendorRate.EffectiveDate <= NOW();
	
	END IF;
END