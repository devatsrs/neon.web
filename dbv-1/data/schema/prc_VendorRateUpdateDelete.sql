CREATE DEFINER=`neon-user`@`192.168.1.25` PROCEDURE `prc_VendorRateUpdateDelete`(
	IN `p_CompanyId` INT,
	IN `p_AccountId` INT,
	IN `p_VendorRateId` LONGTEXT,
	IN `p_EffectiveDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_Rate` decimal(18,6),
	IN `p_Interval1` INT,
	IN `p_IntervalN` INT,
	IN `p_ConnectionFee` decimal(18,6),
	IN `p_Critearea_CountryId` INT,
	IN `p_Critearea_Code` varchar(50),
	IN `p_Critearea_Description` varchar(200),
	IN `p_Critearea_Effective` VARCHAR(50),
	IN `p_TrunkId` INT,
	IN `p_ModifiedBy` varchar(50),
	IN `p_Critearea` INT,
	IN `p_action` INT




)
ThisSP:BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	--	p_action = 1 = update rates
	--	p_action = 2 = delete rates

	DROP TEMPORARY TABLE IF EXISTS tmp_TempVendorRate_;
	CREATE TEMPORARY TABLE tmp_TempVendorRate_ (
		`VendorRateId` int(11) NOT NULL,
		`RateId` int(11) NOT NULL,
		`AccountId` int(11) NOT NULL,
		`TrunkID` int(11) NOT NULL,
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

	INSERT INTO tmp_TempVendorRate_
	SELECT 
		v.VendorRateId,
		v.RateId,
		v.AccountId,
		v.TrunkID,
		IFNULL(p_Rate,v.Rate) AS Rate,
		IFNULL(p_EffectiveDate,v.EffectiveDate) AS EffectiveDate,
		IFNULL(p_EndDate,v.EndDate) AS EndDate,
		NOW() AS updated_at,
		v.created_at,
		v.created_by,
		p_ModifiedBy AS updated_by,
		IFNULL(p_Interval1,v.Interval1) AS Interval1,
		IFNULL(p_IntervalN,v.IntervalN) AS IntervalN,
		IFNULL(p_ConnectionFee,v.ConnectionFee) AS ConnectionFee,
		v.MinimumCost
	FROM 
		tblVendorRate v
	INNER JOIN 
		tblRate r ON r.RateID = v.RateId
	INNER JOIN 
		tblVendorTrunk vt on vt.trunkID = p_TrunkId AND vt.AccountID = p_AccountId AND vt.CodeDeckId = r.CodeDeckId
	WHERE		
		(
			p_EffectiveDate IS NULL OR v.RateID NOT IN (
				SELECT 
					RateID 
				FROM 
					tblVendorRate 
				WHERE 
					EffectiveDate=p_EffectiveDate AND
					((p_Critearea = 0 AND (FIND_IN_SET(VendorRateID,p_VendorRateID) = 0 )) OR p_Critearea = 1) AND 
					AccountId = p_AccountId
			)
		) 
		AND
		(
			(p_Critearea = 0 AND (FIND_IN_SET(v.VendorRateID,p_VendorRateID) != 0 )) OR
			(
				p_Critearea = 1 AND 
				(
					((p_Critearea_CountryId IS NULL) OR (p_Critearea_CountryId IS NOT NULL AND r.CountryId = p_Critearea_CountryId)) AND 
					((p_Critearea_Code IS NULL) OR (p_Critearea_Code IS NOT NULL AND r.Code LIKE REPLACE(p_Critearea_Code,'*', '%'))) AND 
					((p_Critearea_Description IS NULL) OR (p_Critearea_Description IS NOT NULL AND r.Description LIKE REPLACE(p_Critearea_Description,'*', '%'))) AND  
					(
						p_Critearea_Effective = 'All' OR
						(p_Critearea_Effective = 'Now' AND v.EffectiveDate <= NOW() ) OR 
						(p_Critearea_Effective = 'Future' AND v.EffectiveDate > NOW() ) 
					)
				)
			)
		) AND 
		v.AccountId = p_AccountId AND 
		v.TrunkID = p_TrunkId;

--	select * from tmp_TempVendorRate_;LEAVE ThisSP;

	-- if Effective Date needs to change then remove duplicate codes
	IF p_action = 1 AND p_EffectiveDate IS NOT NULL
	THEN
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempVendorRate_2 as (select * from tmp_TempVendorRate_);
		
		DELETE n1 FROM tmp_TempVendorRate_ n1, tmp_TempVendorRate_2 n2 WHERE n1.VendorRateID < n2.VendorRateID AND  n1.RateID = n2.RateID;
	END IF;
	
	-- archive and delete rates if action is 2 and also delete rates if action is 1 and rates are updating
	UPDATE
		tblVendorRate v
	INNER JOIN 
		tmp_TempVendorRate_ temp ON temp.VendorRateID = v.VendorRateID
	SET
		v.EndDate = NOW()
	WHERE 
		temp.VendorRateID = v.VendorRateID;
		
	CALL prc_ArchiveOldVendorRate(p_AccountId,p_TrunkId,p_ModifiedBy);

	IF p_action = 1
	THEN
	
		INSERT INTO tblVendorRate (
			RateId,
			AccountId,
			TrunkID,
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
			RateId,
			AccountId,
			TrunkID,
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

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END