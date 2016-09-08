CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_VendorBulkRateUpdate`(IN `p_AccountId` INT
, IN `p_TrunkId` INT 
, IN `p_code` varchar(50)
, IN `p_description` varchar(200)
, IN `p_CountryId` INT
, IN `p_CompanyId` INT
, IN `p_Rate` decimal(18,6)
, IN `p_EffectiveDate` DATETIME
, IN `p_ConnectionFee` decimal(18,6)
, IN `p_Interval1` INT
, IN `p_IntervalN` INT
, IN `p_ModifiedBy` varchar(50)
, IN `p_effective` VARCHAR(50)
, IN `p_action` INT)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	IF p_action = 1
	-- bulk update vendor rate
	THEN
	
	UPDATE tblVendorRate

	INNER JOIN
	( 
	SELECT VendorRateID
	  FROM tblVendorRate v
	  INNER JOIN tblRate r ON r.RateID = v.RateId
	  INNER JOIN tblVendorTrunk vt on vt.trunkID = p_TrunkId AND vt.AccountID = p_AccountId AND vt.CodeDeckId = r.CodeDeckId
	 WHERE 
	 ((p_CountryId IS NULL) OR (p_CountryId IS NOT NULL AND r.CountryId = p_CountryId))
	 AND ((p_code IS NULL) OR (p_code IS NOT NULL AND r.Code like REPLACE(p_code,'*', '%')))
	 AND ((p_description IS NULL) OR (p_description IS NOT NULL AND r.Description like REPLACE(p_description,'*', '%')))
	 AND  ((p_effective = 'Now' and v.EffectiveDate <= NOW() ) OR (p_effective = 'Future' and v.EffectiveDate> NOW() ) )
	 AND v.AccountId = p_AccountId AND v.TrunkID = p_TrunkId
	 ) vr 
	 ON vr.VendorRateID = tblVendorRate.VendorRateID
	 	SET 
		Rate = p_Rate, 
		EffectiveDate = p_EffectiveDate,
		Interval1 = p_Interval1, 
		IntervalN = p_IntervalN,
		updated_by = p_ModifiedBy,
		ConnectionFee = p_ConnectionFee,
		updated_at = NOW(); 
 	END IF;
		
	 CALL prc_ArchiveOldVendorRate(p_AccountId,p_TrunkId);

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END