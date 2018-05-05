CREATE DEFINER=`neon-user`@`192.168.1.25` PROCEDURE `prc_CustomerRateUpdate`(
	IN `p_AccountIdList` LONGTEXT ,
	IN `p_TrunkId` VARCHAR(100) ,
	IN `p_CustomerRateIDList` LONGTEXT,
	IN `p_Rate` DECIMAL(18, 6) ,
	IN `p_ConnectionFee` DECIMAL(18, 6) ,
	IN `p_EffectiveDate` DATETIME ,
	IN `p_Interval1` INT,
	IN `p_IntervalN` INT,
	IN `p_RoutinePlan` INT,
	IN `p_ModifiedBy` VARCHAR(50)





)
ThisSP:BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRates_;
    CREATE TEMPORARY TABLE tmp_CustomerRates_ (
        CustomerRateID INT,
        RateID INT,
        CustomerID INT,
        Interval1 INT,
        IntervalN  INT,
        Rate DECIMAL(18, 6),
        PreviousRate DECIMAL(18, 6),
        ConnectionFee DECIMAL(18, 6),
        EffectiveDate DATE,
        EndDate DATE,
        CreatedDate DATETIME,
        CreatedBy VARCHAR(50),
        LastModifiedDate DATETIME,
        LastModifiedBy VARCHAR(50),
        TrunkID INT,
        RoutinePlan INT,
        INDEX tmp_CustomerRates__CustomerRateID (`CustomerRateID`)/*,
        INDEX tmp_CustomerRates__RateID (`RateID`),
        INDEX tmp_CustomerRates__TrunkID (`TrunkID`),
        INDEX tmp_CustomerRates__EffectiveDate (`EffectiveDate`)*/
    );
	
	-- if p_EffectiveDate null means multiple rates update
	-- and if multiple rates update then we don't allow to change EffectiveDate
	-- we only allow EffectiveDate change when single edit
	
	-- insert rates in temp table which needs to update
	INSERT INTO tmp_CustomerRates_
	(
		CustomerRateID,
		RateID,
		CustomerID,
		Interval1,
		IntervalN,
		Rate,
		PreviousRate,
		ConnectionFee,
		EffectiveDate,
		EndDate,
		CreatedDate,
		CreatedBy,
		LastModifiedDate,
		LastModifiedBy,
		TrunkID,
		RoutinePlan
	)
	SELECT 
		cr.CustomerRateID,
		cr.RateID,
		cr.CustomerID,
		p_Interval1 AS Interval1,
		p_IntervalN AS IntervalN,
		p_Rate AS Rate,
		cr.PreviousRate,
		p_ConnectionFee AS ConnectionFee,
		IFNULL(p_EffectiveDate,cr.EffectiveDate) AS EffectiveDate, -- if p_EffectiveDate null take exiting EffectiveDate
		cr.EndDate,
		cr.CreatedDate,
		cr.CreatedBy,
		NOW() AS LastModifiedDate,
		p_ModifiedBy AS LastModifiedBy,
		cr.TrunkID,
		CASE WHEN ctr.TrunkID IS NOT NULL 
		THEN p_RoutinePlan 
		ELSE NULL
		END AS RoutinePlan
	FROM
		tblCustomerRate cr
	LEFT JOIN tblCustomerTrunk ctr 
		ON ctr.TrunkID = cr.TrunkID
		AND ctr.AccountID = cr.CustomerID
		AND ctr.RoutinePlanStatus = 1
	LEFT JOIN
	(
		SELECT 
			RateID,CustomerID 
		FROM 
			tblCustomerRate 
		WHERE 
			EffectiveDate = p_EffectiveDate AND 
			FIND_IN_SET(tblCustomerRate.CustomerRateID,p_CustomerRateIDList) = 0 
	) crc ON crc.RateID=cr.RateID AND crc.CustomerID=cr.CustomerID
	WHERE 
		FIND_IN_SET(cr.CustomerRateID,p_CustomerRateIDList) != 0 AND crc.RateID IS NULL;
	
	-- update EndDate to Archive rates which needs to update
	UPDATE
		tblCustomerRate cr
	JOIN
		tmp_CustomerRates_ temp ON cr.CustomerRateID=temp.CustomerRateID
	SET
		cr.EndDate = NOW();
		
	-- archive rates which rates' EndDate < NOW()
	CALL prc_ArchiveOldCustomerRate(p_AccountIdList, p_TrunkId, p_ModifiedBy);

	-- insert rates in tblCustomerRate with updated values
	INSERT INTO tblCustomerRate
	(
		CustomerRateID,
		RateID,
		CustomerID,
		Interval1,
		IntervalN,
		Rate,
		PreviousRate,
		ConnectionFee,
		EffectiveDate,
		EndDate,
		CreatedDate,
		CreatedBy,
		LastModifiedDate,
		LastModifiedBy,
		TrunkID,
		RoutinePlan
	)
	SELECT
		NULL, -- primary key
		RateID,
		CustomerID,
		Interval1,
		IntervalN,
		Rate,
		PreviousRate,
		ConnectionFee,
		EffectiveDate,
		EndDate,
		CreatedDate,
		CreatedBy,
		LastModifiedDate,
		LastModifiedBy,
		TrunkID,
		RoutinePlan
	FROM
		tmp_CustomerRates_;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;                            
END