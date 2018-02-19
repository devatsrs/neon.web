CREATE DEFINER=`neon-user`@`%` PROCEDURE `prc_GetVendorRatesArchiveGrid`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_Codes` LONGTEXT


)
BEGIN
	SELECT
		vra.VendorRateArchiveID,
		vra.VendorRateID,
		vra.AccountID,
		r.Code,
		r.Description,
		CASE WHEN vra.Interval1 IS NOT NULL THEN vra.Interval1 ELSE r.Interval1 END AS Interval1,
		CASE WHEN vra.IntervalN IS NOT NULL THEN vra.IntervalN ELSE r.IntervalN END AS IntervalN,
		vra.Rate,
		vra.EffectiveDate,
		IFNULL(vra.EndDate,'') AS EndDate,
		IFNULL(vra.created_at,'') AS ModifiedDate,
		IFNULL(vra.created_by,'') AS ModifiedBy
	FROM
		tblVendorRateArchive vra
	JOIN
		tblRate r ON r.RateID=vra.RateId
	WHERE
		r.CompanyID = p_CompanyID AND
		vra.AccountId = p_AccountID AND
		FIND_IN_SET (r.Code, p_Codes) != 0
	ORDER BY
		vra.EffectiveDate DESC;
END