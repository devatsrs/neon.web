CREATE DEFINER=`neon-user`@`192.168.1.25` PROCEDURE `prc_ChangeCodeDeckRateTable`(
	IN `p_AccountID` INT,
	IN `p_TrunkID` INT,
	IN `p_RateTableID` INT,
	IN `p_DeletedBy` VARCHAR(50),
	IN `p_Action` INT

)
ThisSP:BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	-- p_Action = 1 = change codedeck = when change codedeck then archive both customer and ratetable rates
	-- p_Action = 2 = change ratetable = when change ratetable then archive only ratetable rates
	
	IF p_Action = 1
	THEN
		-- set ratetableid = 0, no rate table assign to trunk
		UPDATE
			tblCustomerTrunk
		SET
			RateTableID = 0
		WHERE
			AccountID = p_AccountID AND TrunkID = p_TrunkID;
		
		-- archive all customer rate against this account and trunk
		UPDATE
			tblCustomerRate
		SET
			EndDate = DATE(NOW())
		WHERE
			CustomerID = p_AccountID AND TrunkID = p_TrunkID;
		
		-- archive Customer Rates
		call prc_ArchiveOldCustomerRate (p_AccountID,p_TrunkID,p_DeletedBy);
	END IF;
	
	-- archive RateTable Rates
	INSERT INTO tblCustomerRateArchive
	(
		`AccountId`,
		`TrunkID`,
		`RateId`,
		`Rate`,
		`EffectiveDate`,
		`EndDate`,
		`created_at`,
		`created_by`,
		`updated_at`,
		`updated_by`,
		`Interval1`,
		`IntervalN`,
		`ConnectionFee`,
		`Notes`
	)
	SELECT
		p_AccountID AS `AccountId`,
		p_TrunkID AS `TrunkID`,
		`RateID`,
		`Rate`,
		`EffectiveDate`,
		DATE(NOW()) AS `EndDate`,
		DATE(NOW()) AS `created_at`,
		p_DeletedBy AS `created_by`,
		`updated_at`,
		`ModifiedBy`,
		`Interval1`,
		`IntervalN`,
		`ConnectionFee`,
		concat('Ends Today rates @ ' , now() ) as `Notes`
	FROM
		tblRateTableRate
	WHERE
		RateTableID = p_RateTableID;	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ ;
END