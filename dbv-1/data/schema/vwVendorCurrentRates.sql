CREATE DEFINER=`root`@`localhost` PROCEDURE `vwVendorCurrentRates`(IN `p_AccountID` INT, IN `p_Trunks` LONGTEXT)
BEGIN	

	DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates_(
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
		ConnectionFee float
    );

    INSERT INTO tmp_VendorCurrentRates_ 
    SELECT DISTINCT
    v_1.AccountId,
    r.Code,
    r.Description,
    v_1.Rate,
    DATE_FORMAT (v_1.EffectiveDate, '%Y-%m-%d') AS EffectiveDate,
    v_1.TrunkID,
    r.CountryID,
    r.RateID,
   	CASE WHEN v_1.Interval1 is not null
   		THEN v_1.Interval1
    	ELSE r.Interval1
    END as  Interval1,
    CASE WHEN v_1.IntervalN is not null
    	THEN v_1.IntervalN
        ELSE r.IntervalN
    END IntervalN,
    v_1.ConnectionFee
    FROM
	(
		SELECT DISTINCT
			v.AccountId,
	        v.TrunkID,
	        v.RateId,
	        e.EffectiveDate,
	        v.Rate,
	        v.Interval1,
	        v.IntervalN,
	        v.ConnectionFee
	    FROM tblVendorRate AS v
	    INNER JOIN
	    (
	        SELECT
	            AccountId,
	            TrunkID,
	            RateId,
	            MAX(EffectiveDate) AS EffectiveDate
	        FROM tblVendorRate
	        WHERE (EffectiveDate <= NOW())
	        AND tblVendorRate.AccountId = p_AccountID
	        AND FIND_IN_SET(tblVendorRate.TrunkID,p_Trunks)
	        GROUP BY AccountId,
	                 TrunkID,
	                 RateId
	    ) AS e
	        ON v.AccountId = e.AccountId
	        AND v.TrunkID = e.TrunkID
	        AND v.RateId = e.RateId
	        AND v.EffectiveDate = e.EffectiveDate
	) AS v_1
	INNER JOIN tblRate AS r
    	ON r.RateID = v_1.RateId;	 

END