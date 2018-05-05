CREATE DEFINER=`neon-user`@`192.168.1.25` PROCEDURE `prc_CustomerBulkRateUpdate`(
	IN `p_AccountIdList` LONGTEXT ,
	IN `p_TrunkId` INT ,
	IN `p_CodeDeckId` int,
	IN `p_code` VARCHAR(50) ,
	IN `p_description` VARCHAR(200) ,
	IN `p_CountryId` INT ,
	IN `p_CompanyId` INT ,
	IN `p_Effective` VARCHAR(50),
	IN `p_CustomDate` DATE,
	IN `p_Rate` DECIMAL(18, 6) ,
	IN `p_ConnectionFee` DECIMAL(18, 6) ,
	IN `p_EffectiveDate` DATETIME ,
	IN `p_EndDate` DATETIME ,
	IN `p_Interval1` INT,
	IN `p_IntervalN` INT,
	IN `p_RoutinePlan` INT,
	IN `p_ModifiedBy` VARCHAR(50)


)
BEGIN

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
	
	-- insert rates in temp table which needs to update (based on grid filter)
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
		tblCustomerRate.CustomerRateID,
		tblCustomerRate.RateID,
		tblCustomerRate.CustomerID,
		p_Interval1 AS Interval1,
		p_IntervalN AS IntervalN,
		p_Rate AS Rate,
		tblCustomerRate.PreviousRate,
		p_ConnectionFee AS ConnectionFee,
		tblCustomerRate.EffectiveDate,
		tblCustomerRate.EndDate,
		tblCustomerRate.CreatedDate,
		tblCustomerRate.CreatedBy,
		NOW() AS LastModifiedDate,
		p_ModifiedBy AS LastModifiedBy,
		tblCustomerRate.TrunkID,
		tblCustomerRate.RoutinePlan
	FROM
		tblCustomerRate
	INNER JOIN (
		SELECT c.CustomerRateID, 
			c.EffectiveDate,
			CASE WHEN ctr.TrunkID IS NOT NULL 
			THEN p_RoutinePlan 
			ELSE NULL
			END AS RoutinePlan
		FROM   tblCustomerRate c
		INNER JOIN tblCustomerTrunk 
			ON tblCustomerTrunk.TrunkID = c.TrunkID
			AND tblCustomerTrunk.AccountID = c.CustomerID
			AND tblCustomerTrunk.Status = 1
		INNER JOIN tblRate r 
			ON c.RateID = r.RateID and r.CodeDeckId=p_CodeDeckId
		LEFT JOIN tblCustomerTrunk ctr
			ON ctr.TrunkID = c.TrunkID
			AND ctr.AccountID = c.CustomerID
			AND ctr.RoutinePlanStatus = 1
		WHERE  ( ( p_code IS NULL ) OR ( p_code IS NOT NULL AND r.Code LIKE REPLACE(p_code,'*', '%')))
			AND ( ( p_description IS NULL ) OR ( p_description IS NOT NULL AND r.Description LIKE REPLACE(p_description,'*', '%')))
			AND ( ( p_CountryId IS NULL )  OR ( p_CountryId IS NOT NULL AND r.CountryID = p_CountryId ) ) 
			AND
			(
				( p_Effective = 'Now' AND c.EffectiveDate <= NOW() )
				OR
				( p_Effective = 'Future' AND c.EffectiveDate > NOW() )
				OR
				( p_Effective = 'CustomDate' AND c.EffectiveDate <= p_CustomDate AND (c.EndDate IS NULL OR c.EndDate > p_CustomDate) )
				OR 
				p_Effective = 'All'
			)
			AND c.TrunkID = p_TrunkId
			AND FIND_IN_SET(c.CustomerID,p_AccountIdList) != 0
	) cr ON cr.CustomerRateID = tblCustomerRate.CustomerRateID; -- and cr.EffectiveDate = p_EffectiveDate;
	
	-- if custom date then remove duplicate rates of earlier date
	-- for examle custom date is 2018-05-03 today's date is 2018-05-01 and there are 2 rates available for 1 code
	-- Code	Date
	-- 1204	2018-05-01
	-- 1204	2018-05-03
	-- then it will delete 2018-05-01 from temp table and keeps only 2018-05-03 rate to update
	IF p_Effective = 'CustomDate'
	THEN
		DROP TEMPORARY TABLE IF EXISTS tmp_CustomerRates_2_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_CustomerRates_2_ AS (SELECT * FROM tmp_CustomerRates_);
		
		DELETE 
			n1 
		FROM
			tmp_CustomerRates_ n1,
			tmp_CustomerRates_2_ n2
		WHERE 
			n1.EffectiveDate < n2.EffectiveDate AND 
			n1.RateID = n2.RateID AND
			n1.CustomerID = n2.CustomerID AND
			(
				(p_Effective = 'CustomDate' AND n1.EffectiveDate <= p_CustomDate AND n2.EffectiveDate <= p_CustomDate)
				OR
				(p_Effective != 'CustomDate' AND n1.EffectiveDate <= NOW() AND n2.EffectiveDate <= NOW())
			);
	END IF;
	
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