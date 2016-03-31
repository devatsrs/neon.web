CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetLastRateTableRate`(IN `p_companyid` INT, IN `p_RateTableId` INT, IN `p_EffectiveDate` datetime)
BEGIN
		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
        SELECT
            Code,
            Description,
            tblRateTableRate.Interval1,
            tblRateTableRate.IntervalN,
			ConnectionFee,
            Rate,
            EffectiveDate
        FROM tblRate
        INNER JOIN tblRateTableRate
            ON tblRateTableRate.RateID = tblRate.RateID
            AND tblRateTableRate.RateTableId = p_RateTableId
        INNER JOIN tblRateTable
            ON tblRateTable.RateTableId = tblRateTableRate.RateTableId
        WHERE		(tblRate.CompanyID = p_companyid)
		AND tblRateTableRate.RateTableId = p_RateTableId
		AND EffectiveDate = p_EffectiveDate;
		
		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
		
END