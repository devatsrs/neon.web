CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_VendorBulkRateUpdate`(
	IN `p_AccountId` INT
,
	IN `p_TrunkId` INT 
,
	IN `p_code` varchar(50)
,
	IN `p_description` varchar(200)
,
	IN `p_CountryId` INT
,
	IN `p_CompanyId` INT
,
	IN `p_Rate` decimal(18,6)
,
	IN `p_EffectiveDate` DATETIME
,
	IN `p_ConnectionFee` decimal(18,6)
,
	IN `p_Interval1` INT
,
	IN `p_IntervalN` INT
,
	IN `p_ModifiedBy` varchar(50)
,
	IN `p_effective` VARCHAR(50)
,
	IN `p_action` INT



)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	IF p_action = 1
	
	THEN
		DROP TEMPORARY TABLE IF EXISTS tmp_TempVendorRate_;
	  	CREATE TEMPORARY TABLE tmp_TempVendorRate_ (
			`AccountId` int(11) NOT NULL,
			`TrunkID` int(11) NOT NULL,
			`RateId` int(11) NOT NULL,
			`Rate` decimal(18,6) NOT NULL DEFAULT '0.000000',
			`EffectiveDate` datetime NOT NULL,
			`EndDate` datetime DEFAULT NULL,
			`updated_at` datetime DEFAULT NULL,
			`created_at` datetime DEFAULT NULL,
			`created_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
			`updated_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
			`Interval1` int(11) DEFAULT NULL,
			`IntervalN` int(11) DEFAULT NULL,
			`ConnectionFee` decimal(18,6) DEFAULT NULL,
			`MinimumCost` decimal(18,6) DEFAULT NULL
		);
	/*UPDATE tblVendorRate

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
		updated_at = NOW();*/ 
		
		INSERT INTO tmp_TempVendorRate_
		SELECT 
			v.AccountId,
			v.TrunkID,
			v.RateId,
			p_Rate AS Rate,
			p_EffectiveDate AS EffectiveDate,
			v.EndDate,
			NOW() AS updated_at,
			v.created_at,
			v.created_by,
			p_ModifiedBy AS updated_by,
			p_Interval1 AS Interval1,
			p_IntervalN AS IntervalN,
			p_ConnectionFee AS ConnectionFee,
			v.MinimumCost
		FROM 
			tblVendorRate v
		INNER JOIN 
			tblRate r ON r.RateID = v.RateId
		INNER JOIN 
			tblVendorTrunk vt on vt.trunkID = p_TrunkId AND vt.AccountID = p_AccountId AND vt.CodeDeckId = r.CodeDeckId
		WHERE 
			((p_CountryId IS NULL) OR (p_CountryId IS NOT NULL AND r.CountryId = p_CountryId)) AND 
			((p_code IS NULL) OR (p_code IS NOT NULL AND r.Code like REPLACE(p_code,'*', '%'))) AND 
			((p_description IS NULL) OR (p_description IS NOT NULL AND r.Description like REPLACE(p_description,'*', '%'))) AND  
			((p_effective = 'Now' and v.EffectiveDate <= NOW() ) OR (p_effective = 'Future' and v.EffectiveDate> NOW() ) ) AND 
			v.AccountId = p_AccountId AND 
			v.TrunkID = p_TrunkId;
			
		UPDATE
			tblVendorRate v
		INNER JOIN 
			tblRate r ON r.RateID = v.RateId
		INNER JOIN 
			tblVendorTrunk vt on vt.trunkID = p_TrunkId AND vt.AccountID = p_AccountId AND vt.CodeDeckId = r.CodeDeckId
		SET
			v.EndDate = NOW()
		WHERE 
			((p_CountryId IS NULL) OR (p_CountryId IS NOT NULL AND r.CountryId = p_CountryId)) AND 
			((p_code IS NULL) OR (p_code IS NOT NULL AND r.Code like REPLACE(p_code,'*', '%'))) AND 
			((p_description IS NULL) OR (p_description IS NOT NULL AND r.Description like REPLACE(p_description,'*', '%'))) AND  
			((p_effective = 'Now' and v.EffectiveDate <= NOW() ) OR (p_effective = 'Future' and v.EffectiveDate> NOW() ) ) AND 
			v.AccountId = p_AccountId AND 
			v.TrunkID = p_TrunkId;
			
		CALL prc_ArchiveOldVendorRate(p_AccountId,p_TrunkId,p_ModifiedBy);
		
		INSERT INTO tblVendorRate (
			AccountId,
			TrunkID,
			RateId,
			Rate,
			EffectiveDate,
			EndDate,
			updated_at,
			created_at,
			created_by,
			updated_by,
			Interval1,
			IntervalN,
			ConnectionFee,
			MinimumCost
		)
		select 
			AccountId,
			TrunkID,
			RateId,
			Rate,
			EffectiveDate,
			EndDate,
			updated_at,
			created_at,
			created_by,
			updated_by,
			Interval1,
			IntervalN,
			ConnectionFee,
			MinimumCost
		from 
			tmp_TempVendorRate_;
		
 	END IF;

	-- CALL prc_ArchiveOldVendorRate(p_AccountId,p_TrunkId,p_ModifiedBy);
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END