CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_VendorRateForExport`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT ,
	IN `p_TrunkID` INT,
	IN `p_Account` VARCHAR(200),
	IN `p_Trunk` VARCHAR(200) ,
	IN `p_TrunkPrefix` VARCHAR(50),
	IN `p_Effective` VARCHAR(50),
	IN `p_DiscontinueRate` VARCHAR(50)
)
BEGIN

	DECLARE TrunkRatePrefix VARCHAR(50);
	DECLARE TrunkAreaPrefix VARCHAR(50);
	DECLARE TrunkPrefix VARCHAR(50);

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT 
		RatePrefix,
		AreaPrefix,
		Prefix
	INTO
		TrunkRatePrefix,
		TrunkAreaPrefix,
		TrunkPrefix
	FROM tblTrunk
	WHERE TrunkID = p_TrunkID;

	DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
	CREATE TEMPORARY TABLE tmp_VendorRate_ (
		TrunkId INT,
		RateId INT,
		Rate DECIMAL(18,6),
		EffectiveDate DATE,
		Interval1 INT,
		IntervalN INT,
		ConnectionFee DECIMAL(18,6),
		INDEX IX_tmp_VendorRate_ (`RateId`)
	);
	INSERT INTO tmp_VendorRate_
	SELECT
		TrunkID,
		RateId,
		Rate,
		EffectiveDate,
		Interval1,
		IntervalN,
		ConnectionFee
	FROM tblVendorRate
	WHERE tblVendorRate.AccountId = p_AccountID
		AND tblVendorRate.TrunkId = p_TrunkID
		AND
		(
			(p_Effective = 'Now' AND EffectiveDate <= NOW())
			OR 
			(p_Effective = 'Future' AND EffectiveDate > NOW())
			OR 
			(p_Effective = 'All')
		);

	DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate2_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRate2_ AS (SELECT * from tmp_VendorRate_);
	DELETE n1 FROM tmp_VendorRate_ n1, tmp_VendorRate2_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
	AND n1.TrunkID = n2.TrunkID
	AND  n1.RateId = n2.RateId
	AND n1.EffectiveDate <= NOW()
	AND n2.EffectiveDate <= NOW();

	IF p_DiscontinueRate = 'no'
	THEN

		SELECT DISTINCT
			p_Account AS AccountName,
			p_Trunk AS Trunk,
			p_TrunkPrefix AS VendorTrunkPrefix,
			TrunkRatePrefix,
			TrunkAreaPrefix,
			TrunkPrefix,
			tblRate.Code ,
			tblRate.Description ,
			CASE WHEN tblVendorRate.Interval1 IS NOT NULL
			THEN
				tblVendorRate.Interval1
			ElSE
				tblRate.Interval1
			END AS Interval1,
			CASE WHEN tblVendorRate.IntervalN IS NOT NULL
			THEN
				tblVendorRate.IntervalN
			ELSE
				tblRate.IntervalN
			END  AS IntervalN ,
			tblVendorRate.Rate,
			tblVendorRate.EffectiveDate,
			IFNULL(Preference,5) as `Preference`,
			CASE WHEN 
				(blockCode.VendorBlockingId IS NOT NULL AND FIND_IN_SET(tblVendorRate.TrunkId,blockCode.TrunkId) != 0) 
				OR
				(blockCountry.VendorBlockingId IS NOT NULL AND FIND_IN_SET(tblVendorRate.TrunkId,blockCountry.TrunkId) != 0 ) 
			THEN 
				'1'
			ELSE 
				'0'
			END AS `Blocked`
		FROM    tmp_VendorRate_ AS tblVendorRate
		INNER JOIN tblRate
			ON tblVendorRate.RateId =tblRate.RateID
		LEFT JOIN tblVendorBlocking AS blockCode
			ON tblVendorRate.RateID = blockCode.RateId
			AND blockCode.AccountId = p_AccountID
			AND blockCode.TrunkID = p_TrunkID
			AND tblVendorRate.TrunkID = blockCode.TrunkID
		LEFT JOIN tblVendorBlocking AS blockCountry
			ON tblRate.CountryID = blockCountry.CountryId
			AND blockCountry.AccountId = p_AccountID
			AND blockCountry.TrunkID = p_TrunkID
			AND tblVendorRate.TrunkID = blockCountry.TrunkID
		LEFT JOIN tblVendorPreference 
			ON tblVendorPreference.AccountId = p_AccountID
			AND tblVendorPreference.TrunkID = p_TrunkID
			AND tblVendorPreference.TrunkID = tblVendorRate.TrunkID
			AND tblVendorPreference.RateId = tblVendorRate.RateId;

	ELSE

		SELECT DISTINCT
			p_Account AS AccountName,
			p_Trunk AS Trunk,
			p_TrunkPrefix AS VendorTrunkPrefix,
			TrunkRatePrefix,
			TrunkAreaPrefix,
			TrunkPrefix,
			tblRate.Code,
			tblRate.Description,
			CASE WHEN tblVendorRate.Interval1 IS NOT NULL
			THEN
				tblVendorRate.Interval1
			ElSE
				tblRate.Interval1
			END AS Interval1,
			CASE WHEN tblVendorRate.IntervalN IS NOT NULL
			THEN
				tblVendorRate.IntervalN
			ElSE
				tblRate.IntervalN
			END  AS IntervalN,
			tblVendorRate.Rate,
			tblVendorRate.EffectiveDate,
			IFNULL(Preference,5) as `Preference`,
			CASE WHEN 
				(blockCode.VendorBlockingId IS NOT NULL AND FIND_IN_SET(tblVendorRate.TrunkId,blockCode.TrunkId) != 0 )
				OR
				(blockCountry.VendorBlockingId IS NOT NULL AND FIND_IN_SET(tblVendorRate.TrunkId,blockCountry.TrunkId) != 0	) 
			THEN
				'1'
			ELSE
				'0'
			END AS `Blocked`,
			'N' AS `Discontinued`
		FROM tmp_VendorRate_ AS tblVendorRate 
		INNER JOIN tblRate
			ON tblVendorRate.RateId = tblRate.RateID
		LEFT JOIN tblVendorBlocking AS blockCode
			ON tblVendorRate.RateID = blockCode.RateId
			AND blockCode.AccountId = p_AccountID
			AND blockCode.TrunkID = p_TrunkID
			AND tblVendorRate.TrunkID = blockCode.TrunkID
		LEFT JOIN tblVendorBlocking AS blockCountry
			ON tblRate.CountryID = blockCountry.CountryId
			AND blockCountry.AccountId = p_AccountID
			AND blockCountry.TrunkID = p_TrunkID
			AND tblVendorRate.TrunkID = blockCountry.TrunkID
		LEFT JOIN tblVendorPreference
			ON tblVendorPreference.AccountId = p_AccountID
			AND tblVendorPreference.TrunkID = p_TrunkID
			AND tblVendorPreference.TrunkID = tblVendorRate.TrunkID
			AND tblVendorPreference.RateId = tblVendorRate.RateId

		UNION ALL

		SELECT
			p_Account AS AccountName,
			p_Trunk AS Trunk,
			p_TrunkPrefix AS VendorTrunkPrefix,
			TrunkRatePrefix,
			TrunkAreaPrefix,
			TrunkPrefix, 
			vrd.Code,
			vrd.Description,
			vrd.Interval1,
			vrd.IntervalN,
			vrd.Rate,
			vrd.EffectiveDate,
			'' AS `Preference`,
			'' AS `Forbidden`,
			'Y' AS `Discontinued`
		FROM tblVendorRateDiscontinued vrd
		LEFT JOIN tblVendorRate vr
			ON vrd.AccountId = vr.AccountId 
			AND vrd.TrunkID = vr.TrunkID
			AND vrd.RateId = vr.RateId
		WHERE vrd.AccountId = p_AccountID
		AND vrd.TrunkID = p_TrunkID
		AND vr.VendorRateID IS NULL ;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END