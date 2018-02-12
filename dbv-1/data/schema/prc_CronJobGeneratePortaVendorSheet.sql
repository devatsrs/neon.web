CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_CronJobGeneratePortaVendorSheet`(
	IN `p_AccountID` INT ,
	IN `p_trunks` VARCHAR(200),
	IN `p_Effective` VARCHAR(50)





,
	IN `p_CustomDate` DATE


)
BEGIN
		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
    CREATE TEMPORARY TABLE tmp_VendorRate_ (
        TrunkId INT,
   	  RateId INT,
        Rate DECIMAL(18,6),
        EffectiveDate DATE,
        Interval1 INT,
        IntervalN INT,
        ConnectionFee DECIMAL(18,6),
        INDEX tmp_RateTable_RateId (`RateId`)
    );
        INSERT INTO tmp_VendorRate_
        SELECT   `TrunkID`, `RateId`, `Rate`, 
		  DATE_FORMAT (`EffectiveDate`, '%Y-%m-%d') AS EffectiveDate,
		   `Interval1`, `IntervalN`, `ConnectionFee`
		  FROM tblVendorRate WHERE tblVendorRate.AccountId =  p_AccountID
								AND FIND_IN_SET(tblVendorRate.TrunkId,p_trunks) != 0
                        AND
								(
					  				(p_Effective = 'Now' AND EffectiveDate <= NOW())
								  	OR
								  	(p_Effective = 'Future' AND EffectiveDate > NOW())
								  	OR
								  	(p_Effective = 'CustomDate' AND EffectiveDate <= p_CustomDate AND (EndDate IS NULL OR EndDate > p_CustomDate))
								  	OR
								  	(p_Effective = 'All')
								);

		 DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate4_;
       CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRate4_ as (select * from tmp_VendorRate_);
      DELETE n1 FROM tmp_VendorRate_ n1, tmp_VendorRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate
 	   AND n1.TrunkID = n2.TrunkID
	   AND  n1.RateId = n2.RateId
		AND
	   (
			(p_Effective = 'CustomDate' AND n1.EffectiveDate <= p_CustomDate AND n2.EffectiveDate <= p_CustomDate)
		  	OR 
		  	(p_Effective != 'CustomDate' AND n1.EffectiveDate <= NOW() AND n2.EffectiveDate <= NOW())
		);



	DROP TEMPORARY TABLE IF EXISTS tmp_VendorArchiveCurrentRates_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorArchiveCurrentRates_(
		AccountId int,
		Code varchar(50),
		Description varchar(200),
		Rate float,
		EffectiveDate date,
		TrunkID int,
		CountryID int,
		RateID int,
		Interval1 INT,
		IntervalN varchar(100),
		ConnectionFee float,
		EndDate date
    );
    
	IF p_Effective = 'Now' || p_Effective = 'All' THEN

  	 	call vwVendorArchiveCurrentRates(p_AccountID,p_Trunks,p_Effective);
  	 	
	END IF;

       SELECT Distinct  tblRate.Code as `Destination`,
               tblRate.Description as `Description` ,
               CASE WHEN tblVendorRate.Interval1 IS NOT NULL
                   THEN tblVendorRate.Interval1
                   ElSE tblRate.Interval1
               END AS `First Interval`,
               CASE WHEN tblVendorRate.IntervalN IS NOT NULL
                   THEN tblVendorRate.IntervalN
                   ElSE tblRate.IntervalN
               END  AS `Next Interval`,
               Abs(tblVendorRate.Rate) as `First Price`,
               Abs(tblVendorRate.Rate) as `Next Price`,
               DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d')  as `Effective From` ,
               IFNULL(Preference,5) as `Preference`,
               CASE
                   WHEN (blockCode.VendorBlockingId IS NOT NULL AND
                   	FIND_IN_SET(tblVendorRate.TrunkId,blockCode.TrunkId) != 0
                       )OR
                       (blockCountry.VendorBlockingId IS NOT NULL AND
                       FIND_IN_SET(tblVendorRate.TrunkId,blockCountry.TrunkId) != 0
                       ) THEN 'Y'
                   ELSE 'N'
               END AS `Forbidden`,
               CASE WHEN tblVendorRate.Rate < 0 THEN 'Y' ELSE '' END AS 'Payback Rate' ,
               CASE WHEN ConnectionFee > 0 THEN
						CONCAT('SEQ=',ConnectionFee,'&int1x1@price1&intNxN@priceN')
					ELSE
						''
					END as `Formula`,
					'N' AS `Discontinued`
       FROM    tmp_VendorRate_ as tblVendorRate
               JOIN tblRate on tblVendorRate.RateId =tblRate.RateID
               LEFT JOIN tblVendorBlocking as blockCode
                   ON tblVendorRate.RateID = blockCode.RateId
                   AND blockCode.AccountId = p_AccountID
                   AND tblVendorRate.TrunkID = blockCode.TrunkID
               LEFT JOIN tblVendorBlocking AS blockCountry
                   ON tblRate.CountryID = blockCountry.CountryId
                   AND blockCountry.AccountId = p_AccountID
                   AND tblVendorRate.TrunkID = blockCountry.TrunkID
					LEFT JOIN tblVendorPreference
						ON tblVendorPreference.AccountId = p_AccountID
						AND tblVendorPreference.TrunkID = tblVendorRate.TrunkID
						AND tblVendorPreference.RateId = tblVendorRate.RateId
       UNION ALL



		SELECT
					Distinct
			    	tblRate.Code AS `Destination`,
			 		tblRate.Description AS `Description` ,

			 		CASE WHEN vrd.Interval1 IS NOT NULL
                   THEN vrd.Interval1
                   ElSE tblRate.Interval1
               END AS `First Interval`,
               CASE WHEN vrd.IntervalN IS NOT NULL
                   THEN vrd.IntervalN
                   ElSE tblRate.IntervalN
               END  AS `Next Interval`,

			 		Abs(vrd.Rate) AS `First Price`,
			 		Abs(vrd.Rate) AS `Next Price`,
			 		DATE_FORMAT (vrd.EffectiveDate, '%Y-%m-%d') AS `Effective From`,
			 		'' AS `Preference`,
			 		'' AS `Forbidden`,
			 		CASE WHEN vrd.Rate < 0 THEN 'Y' ELSE '' END AS 'Payback Rate' ,
			 		CASE WHEN vrd.ConnectionFee > 0 THEN
						CONCAT('SEQ=',vrd.ConnectionFee,'&int1x1@price1&intNxN@priceN')
					ELSE
						''
					END as `Formula`,
			 		'Y' AS `Discontinued`
			FROM tmp_VendorArchiveCurrentRates_ AS vrd
	 		JOIN tblRate on vrd.RateId = tblRate.RateID
			LEFT JOIN tblVendorRate vr
						ON vrd.AccountId = vr.AccountId
							AND vrd.TrunkID = vr.TrunkID
							AND vrd.RateId = vr.RateId
					WHERE FIND_IN_SET(vrd.TrunkID,p_trunks) != 0
						AND vrd.AccountId = p_AccountID
						AND vr.VendorRateID IS NULL
						AND vrd.Rate > 0;
 

			/*
		    SELECT
			 		vrd.Code AS `Destination`,
			 		vrd.Description AS `Description` ,
			 		vrd.Interval1 AS `First Interval`,
			 		vrd.IntervalN AS `Next Interval`,
			 		Abs(vrd.Rate) AS `First Price`,
			 		Abs(vrd.Rate) AS `Next Price`,
			 		vrd.EffectiveDate AS `Effective From`,
			 		'' AS `Preference`,
			 		'' AS `Forbidden`,
			 		CASE WHEN vrd.Rate < 0 THEN 'Y' ELSE '' END AS 'Payback Rate' ,
			 		CASE WHEN vrd.ConnectionFee > 0 THEN
						CONCAT('SEQ=',vrd.ConnectionFee,'&int1x1@price1&intNxN@priceN')
					ELSE
						''
					END as `Formula`,
			 		'Y' AS `Discontinued`
			  FROM tblVendorRateDiscontinued vrd
					LEFT JOIN tblVendorRate vr
						ON vrd.AccountId = vr.AccountId
							AND vrd.TrunkID = vr.TrunkID
							AND vrd.RateId = vr.RateId
					WHERE FIND_IN_SET(vrd.TrunkID,p_trunks) != 0
						AND vrd.AccountId = p_AccountID
						AND vr.VendorRateID IS NULL ;
*/


      SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END