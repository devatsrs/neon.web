CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_CronJobGeneratePortaVendorSheet`(IN `p_AccountID` INT , IN `p_trunks` varchar(200) )
BEGIN
		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
         
        SELECT Code as `Destination`,
					Description as `Description`,
					Interval1 as `First Interval`,
					IntervalN as `Next Interval`,
					Rate as `First Price`,
					Rate as `Next Price`,
					EffectiveDate as `Effective From`,
					Preference as `Preference`,
					Forbidden as `Forbidden`, 
					CASE WHEN ConnectionFee > 0 THEN
						CONCAT('SEQ=',ConnectionFee,'&int1x1@price1&intNxN@priceN')
					ELSE
						''
					END as `Formula`  
        FROM    (
                SELECT  tblRate.Code,
                        tblRate.Description,
                        CASE WHEN tblVendorRate.Interval1 IS NOT NULL
                            THEN tblVendorRate.Interval1
                            ElSE tblRate.Interval1
                        END AS Interval1,
                        CASE WHEN tblVendorRate.IntervalN IS NOT NULL
                            THEN tblVendorRate.IntervalN
                            ElSE tblRate.IntervalN
                        END  AS IntervalN,
                        tblVendorRate.Rate,
                        tblVendorRate.ConnectionFee,
                        tblVendorRate.EffectiveDate ,
                        IFNULL(Preference,5) as Preference,
                        CASE
                            WHEN (blockCode.VendorBlockingId IS NOT NULL AND
                            	FIND_IN_SET(tblVendorRate.TrunkId,blockCode.TrunkId) != 0
                                )OR
                                (blockCountry.VendorBlockingId IS NOT NULL AND
                                FIND_IN_SET(tblVendorRate.TrunkId,blockCountry.TrunkId) != 0
                                ) THEN 1
                            ELSE 0
                        END AS Forbidden,
                        @row_num := IF(@prev_TrunkId  = tblVendorRate.TrunkId AND @prev_RateID=tblVendorRate.RateID and @prev_effectivedate >= tblVendorRate.effectivedate ,@row_num+1,1) AS RowID,
                        @prev_RateID  := tblVendorRate.RateID,
					 			@prev_effectivedate  := tblVendorRate.effectivedate,
					 			@prev_TrunkId  := tblVendorRate.TrunkId
                FROM    tblAccount
                        JOIN tblVendorRate ON tblAccount.AccountID = tblVendorRate.AccountId
                        JOIN tblRate on tblVendorRate.RateId =tblRate.RateID
                        LEFT OUTER JOIN tblVendorBlocking as blockCode
                            ON tblVendorRate.RateID = blockCode.RateId
                            AND tblAccount.AccountID = blockCode.AccountId
                            AND tblVendorRate.TrunkID = blockCode.TrunkID
                        LEFT OUTER JOIN tblVendorBlocking AS blockCountry
                            ON tblRate.CountryID = blockCountry.CountryId
                            AND tblAccount.AccountID = blockCountry.AccountId
                            AND tblVendorRate.TrunkID = blockCountry.TrunkID
								LEFT JOIN tblVendorPreference 
									ON tblVendorPreference.AccountId = tblVendorRate.AccountId
									AND tblVendorPreference.TrunkID = tblVendorRate.TrunkID
									AND tblVendorPreference.RateId = tblVendorRate.RateId
									,(SELECT @row_num := 1) x,(SELECT @prev_RateID := '') y,(SELECT @prev_effectivedate := '') z,(SELECT @prev_TrunkId := '') v
                WHERE   tblAccount.AccountID = @AccountID
                        AND tblVendorRate.Rate > 0
								AND FIND_IN_SET(tblVendorRate.TrunkId,p_trunks) != 0 
                        AND EffectiveDate <= NOW()
               ORDER BY tblVendorRate.AccountID ,tblVendorRate.TrunkId,tblVendorRate.RateID,tblVendorRate.effectivedate DESC
                ) TBL2
        where RowID = 1;
      SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END