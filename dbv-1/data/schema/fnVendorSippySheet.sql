CREATE DEFINER=`root`@`localhost` PROCEDURE `fnVendorSippySheet`(IN `p_AccountID` int, IN `p_Trunks` longtext, IN `p_Effective` VARCHAR(50))
BEGIN
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
    
        SELECT NULL AS RateID,
               'A' AS `Action [A|D|U|S|SA]`,
               '' AS id,
               Concat(tblTrunk.Prefix , vendorRate.Code)  AS PREFIX,
               vendorRate.Description AS COUNTRY,
               5 AS Preference,
               CASE
                   WHEN vendorRate.Description LIKE '%gambia%'
                        OR vendorRate.Description LIKE '%mexico%' THEN 60
                   ELSE 1
               END AS `Interval 1`,
               CASE
                   WHEN vendorRate.Description LIKE '%mexico%' THEN 60
                   ELSE 1
               END AS `Interval N`,
               vendorRate.Rate AS `Price 1`,
               vendorRate.Rate AS `Price N`,
               10 AS `1xx Timeout`,
               60 AS `2xx Timeout`,
               0 AS Huntstop,
               CASE WHEN (tblVendorBlocking.VendorBlockingId IS NOT NULL AND  FIND_IN_SET(vendorRate.TrunkId,p_Trunks) != 0) OR (blockCountry.VendorBlockingId IS NOT NULL AND FIND_IN_SET(vendorRate.TrunkId,p_Trunks) != 0 ) 
                THEN 1 
                ELSE 0 
                END AS Forbidden,
               'NOW' AS `Activation Date`,
               '' AS `Expiration Date`,
               tblAccount.AccountID,
               tblTrunk.TrunkID
        FROM tmp_VendorSippySheet_ AS vendorRate
        INNER JOIN tblAccount ON vendorRate.AccountId = tblAccount.AccountID
        LEFT OUTER JOIN tblVendorBlocking ON vendorRate.RateID = tblVendorBlocking.RateId
        AND tblAccount.AccountID = tblVendorBlocking.AccountId
        AND vendorRate.TrunkID = tblVendorBlocking.TrunkID
        LEFT OUTER JOIN tblVendorBlocking AS blockCountry ON vendorRate.CountryID = blockCountry.CountryId
        AND tblAccount.AccountID = blockCountry.AccountId
        AND vendorRate.TrunkID = blockCountry.TrunkID
        INNER JOIN tblTrunk ON tblTrunk.TrunkID = vendorRate.TrunkID
        WHERE vendorRate.AccountId = p_AccountID
          AND FIND_IN_SET(vendorRate.TrunkId,p_Trunks) != 0
          AND vendorRate.Rate > 0;


     
END