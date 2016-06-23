CREATE DEFINER=`root`@`localhost` PROCEDURE `vwVendorSippySheet`(IN `p_AccountID` INT, IN `p_Trunks` LONGTEXT, IN `p_Effective` VARCHAR(50))
BEGIN

DROP TEMPORARY TABLE IF EXISTS tmp_VendorSippySheet_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorSippySheet_(
			RateID int,
			`Action [A|D|U|S|SA` varchar(50),
			id varchar(10),
			Prefix varchar(50),
			COUNTRY varchar(200),
			Preference int,
			`Interval 1` int,
			`Interval N` int,
			`Price 1` float,
			`Price N` float,
			`1xx Timeout` int,
			`2xx Timeout` INT,
			Huntstop int,
			Forbidden int,
			`Activation Date` varchar(10),
			`Expiration Date` varchar(10),
			AccountID int,
			TrunkID int
	);
	

call vwVendorCurrentRates(p_AccountID,p_Trunks,p_Effective); 

INSERT INTO tmp_VendorSippySheet_ 
SELECT
    NULL AS RateID,
    'A' AS `Action [A|D|U|S|SA`,
    '' AS id,
    Concat('' , tblTrunk.Prefix ,vendorRate.Code) AS Prefix,
    vendorRate.Description AS COUNTRY,
    IFNULL(tblVendorPreference.Preference,5) as Preference,
    vendorRate.Interval1 as `Interval 1`,
    vendorRate.IntervalN as `Interval N`,
    vendorRate.Rate AS `Price 1`,
    vendorRate.Rate AS `Price N`,
    10 AS `1xx Timeout`,
    60 AS `2xx Timeout`,
    0 AS Huntstop,
    CASE
        WHEN (tblVendorBlocking.VendorBlockingId IS NOT NULL AND
        	FIND_IN_SET(vendorRate.TrunkId,tblVendorBlocking.TrunkId) != 0
			OR
            (blockCountry.VendorBlockingId IS NOT NULL AND
            FIND_IN_SET(vendorRate.TrunkId,blockCountry.TrunkId) != 0
            )
            ) THEN 1
        ELSE 0
    END  AS Forbidden,
    'NOW' AS `Activation Date`,
    '' AS `Expiration Date`,
    tblAccount.AccountID,
    tblTrunk.TrunkID
FROM tmp_VendorCurrentRates_ AS vendorRate
INNER JOIN tblAccount
    ON vendorRate.AccountId = tblAccount.AccountID
LEFT OUTER JOIN tblVendorBlocking
    ON vendorRate.RateID = tblVendorBlocking.RateId
    AND tblAccount.AccountID = tblVendorBlocking.AccountId
    AND vendorRate.TrunkID = tblVendorBlocking.TrunkID
LEFT OUTER JOIN tblVendorBlocking AS blockCountry
    ON vendorRate.CountryID = blockCountry.CountryId
    AND tblAccount.AccountID = blockCountry.AccountId
    AND vendorRate.TrunkID = blockCountry.TrunkID
LEFT JOIN tblVendorPreference 
  ON tblVendorPreference.AccountId = vendorRate.AccountId
  AND tblVendorPreference.TrunkID = vendorRate.TrunkID
  AND tblVendorPreference.RateId = vendorRate.RateID
INNER JOIN tblTrunk
    ON tblTrunk.TrunkID = vendorRate.TrunkID
WHERE (vendorRate.Rate > 0);

END