CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `vwVendorVersion3VosSheet`(
	IN `p_AccountID` INT,
	IN `p_Trunks` LONGTEXT,
	IN `p_Effective` VARCHAR(50)







,
	IN `p_CustomDate` DATE
)
BEGIN



	DROP TEMPORARY TABLE IF EXISTS tmp_VendorVersion3VosSheet_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorVersion3VosSheet_(
			RateID int,
			`Rate Prefix` varchar(50),
			`Area Prefix` varchar(50),
			`Rate Type` varchar(50),
			`Area Name` varchar(200),
			`Billing Rate` float,
			`Billing Cycle` int,
			`Minute Cost` float,
			`Lock Type` varchar(50),
			`Section Rate` varchar(50),
			`Billing Rate for Calling Card Prompt` float,
			`Billing Cycle for Calling Card Prompt` INT,
			AccountID int,
			TrunkID int,
			EffectiveDate date,
			EndDate date
	);
	

	DROP TEMPORARY TABLE IF EXISTS tmp_VendorArhiveVersion3VosSheet_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorArhiveVersion3VosSheet_(
			RateID int,
			`Rate Prefix` varchar(50),
			`Area Prefix` varchar(50),
			`Rate Type` varchar(50),
			`Area Name` varchar(200),
			`Billing Rate` float,
			`Billing Cycle` int,
			`Minute Cost` float,
			`Lock Type` varchar(50),
			`Section Rate` varchar(50),
			`Billing Rate for Calling Card Prompt` float,
			`Billing Cycle for Calling Card Prompt` INT,
			AccountID int,
			TrunkID int,
			EffectiveDate date,
			EndDate date
	);

	
	 Call vwVendorCurrentRates(p_AccountID,p_Trunks,p_Effective,p_CustomDate);	

	 
INSERT INTO tmp_VendorVersion3VosSheet_	 
SELECT


    NULL AS RateID,
    IFNULL(tblTrunk.RatePrefix, '') AS `Rate Prefix`,
    Concat('' , IFNULL(tblTrunk.AreaPrefix, '') , vendorRate.Code) AS `Area Prefix`,
    'International' AS `Rate Type`,
    vendorRate.Description AS `Area Name`,
    vendorRate.Rate / 60 AS `Billing Rate`,
    vendorRate.IntervalN AS `Billing Cycle`,
    CAST(vendorRate.Rate AS DECIMAL(18, 5)) AS `Minute Cost`,
    CASE
        WHEN (tblVendorBlocking.VendorBlockingId IS NOT NULL AND
        FIND_IN_SET(vendorRate.TrunkId,tblVendorBlocking.TrunkId) != 0
             OR
            (blockCountry.VendorBlockingId IS NOT NULL AND
             FIND_IN_SET(vendorRate.TrunkId,blockCountry.TrunkId) != 0
            )) THEN 'No Lock'
        ELSE 'No Lock'
    END
    AS `Lock Type`,
        CASE WHEN vendorRate.Interval1 != vendorRate.IntervalN 
                                      THEN 
                    Concat('0,', vendorRate.Rate, ',',vendorRate.Interval1)
                                      ELSE ''
                                 END as `Section Rate`,
    0 AS `Billing Rate for Calling Card Prompt`,
    0 AS `Billing Cycle for Calling Card Prompt`,
    tblAccount.AccountID,
    vendorRate.TrunkId,
    vendorRate.EffectiveDate,
    vendorRate.EndDate
FROM tmp_VendorCurrentRates_ AS vendorRate
INNER JOIN tblAccount 
    ON vendorRate.AccountId = tblAccount.AccountID
LEFT OUTER JOIN tblVendorBlocking
    ON vendorRate.TrunkId = tblVendorBlocking.TrunkID
    AND vendorRate.RateID = tblVendorBlocking.RateId
    AND tblAccount.AccountID = tblVendorBlocking.AccountId
LEFT OUTER JOIN tblVendorBlocking AS blockCountry
    ON vendorRate.TrunkId = blockCountry.TrunkID
    AND vendorRate.CountryID = blockCountry.CountryId
    AND tblAccount.AccountID = blockCountry.AccountId
INNER JOIN tblTrunk
    ON tblTrunk.TrunkID = vendorRate.TrunkId
WHERE (vendorRate.Rate > 0);

	 
	 -- for archive rates 
	 IF p_Effective != 'Now' THEN
	 
		 	call vwVendorArchiveCurrentRates(p_AccountID,p_Trunks,p_Effective);	
		 
			INSERT INTO tmp_VendorArhiveVersion3VosSheet_	 
			SELECT
			
			
			    NULL AS RateID,
			    IFNULL(tblTrunk.RatePrefix, '') AS `Rate Prefix`,
			    Concat('' , IFNULL(tblTrunk.AreaPrefix, '') , vendorArchiveRate.Code) AS `Area Prefix`,
			    'International' AS `Rate Type`,
			    vendorArchiveRate.Description AS `Area Name`,
			    vendorArchiveRate.Rate / 60 AS `Billing Rate`,
			    vendorArchiveRate.IntervalN AS `Billing Cycle`,
			    CAST(vendorArchiveRate.Rate AS DECIMAL(18, 5)) AS `Minute Cost`,
			    'No Lock'   AS `Lock Type`,
			     CASE WHEN vendorArchiveRate.Interval1 != vendorArchiveRate.IntervalN THEN 
				           Concat('0,', vendorArchiveRate.Rate, ',',vendorArchiveRate.Interval1)
			   	ELSE ''
			    END as `Section Rate`,
			    0 AS `Billing Rate for Calling Card Prompt`,
			    0 AS `Billing Cycle for Calling Card Prompt`,
			    tblAccount.AccountID,
			    vendorArchiveRate.TrunkId,
			    vendorArchiveRate.EffectiveDate,
			    vendorArchiveRate.EndDate
			FROM tmp_VendorArchiveCurrentRates_ AS vendorArchiveRate
			Left join tmp_VendorVersion3VosSheet_ vendorRate
				 ON vendorArchiveRate.AccountId = vendorRate.AccountID
				 AND vendorArchiveRate.AccountId = vendorRate.TrunkID
 				 AND vendorArchiveRate.RateID = vendorRate.RateID
 				 
			INNER JOIN tblAccount 
			    ON vendorArchiveRate.AccountId = tblAccount.AccountID
			INNER JOIN tblTrunk
			    ON tblTrunk.TrunkID = vendorArchiveRate.TrunkId
			WHERE vendorRate.RateID is Null AND -- remove all archive rates which are exists in VendorRate
			(vendorArchiveRate.Rate > 0);

	 END IF;

END