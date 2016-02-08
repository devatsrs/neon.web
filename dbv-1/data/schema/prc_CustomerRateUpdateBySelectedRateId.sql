CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_CustomerRateUpdateBySelectedRateId`(IN `p_CompanyId` INT, IN `p_AccountIdList` LONGTEXT , IN `p_RateIDList` LONGTEXT , IN `p_TrunkId` VARCHAR(100) , IN `p_Rate` DECIMAL(18, 6) , IN `p_ConnectionFee` DECIMAL(18, 6) , IN `p_EffectiveDate` DATETIME , IN `p_Interval1` INT, IN `p_IntervalN` INT, IN `p_RoutinePlan` INT, IN `p_ModifiedBy` VARCHAR(50))
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	UPDATE  tblCustomerRate
	INNER JOIN ( 
		SELECT c.CustomerRateID,c.EffectiveDate,
			CASE WHEN ctr.TrunkID IS NOT NULL 
				THEN p_RoutinePlan 
			ELSE 0
				END AS RoutinePlan
      FROM   tblCustomerRate c
			
         INNER JOIN tblCustomerTrunk ON tblCustomerTrunk.TrunkID = c.TrunkID
         	AND tblCustomerTrunk.AccountID = c.CustomerID
            AND tblCustomerTrunk.Status = 1
            AND c.EffectiveDate = p_EffectiveDate
			LEFT JOIN tblCustomerTrunk ctr
				ON ctr.TrunkID = c.TrunkID
				AND ctr.AccountID = c.CustomerID
				AND ctr.RoutinePlanStatus = 1
            ) cr ON cr.CustomerRateID = tblCustomerRate.CustomerRateID and cr.EffectiveDate = p_EffectiveDate
		SET     
			tblCustomerRate.PreviousRate = Rate ,
         tblCustomerRate.Rate = p_Rate ,
			tblCustomerRate.ConnectionFee = p_ConnectionFee,
         tblCustomerRate.EffectiveDate = p_EffectiveDate ,
         tblCustomerRate.Interval1 = p_Interval1, 
			tblCustomerRate.IntervalN = p_IntervalN,
         tblCustomerRate.LastModifiedBy = p_ModifiedBy ,
			tblCustomerRate.RoutinePlan = cr.RoutinePlan,
         tblCustomerRate.LastModifiedDate = NOW()
			WHERE tblCustomerRate.TrunkID = p_TrunkId
         AND FIND_IN_SET(tblCustomerRate.RateID,p_RateIDList) != 0
         AND FIND_IN_SET(tblCustomerRate.CustomerID,p_AccountIdList) != 0;
                           
                           
        INSERT  INTO tblCustomerRate
                ( RateID ,
                  CustomerID ,
                  TrunkID ,
                  Rate ,
					   ConnectionFee,
                  EffectiveDate ,
                  Interval1, 
				  		IntervalN ,
				  		RoutinePlan,
                  CreatedDate ,
                  LastModifiedBy ,
                  LastModifiedDate
                )
                SELECT  r.RateID,
                        r.AccountId ,
                        p_TrunkId ,
                        p_Rate ,
								p_ConnectionFee,
                        p_EffectiveDate ,
                        p_Interval1, 
					    		p_IntervalN,
								RoutinePlan,
                        NOW() ,
                        p_ModifiedBy ,
                        NOW()
                FROM    ( SELECT    tblRate.RateID ,
                                    a.AccountId,
                                    tblRate.CompanyID,
									RoutinePlan
                          FROM      tblRate ,
                                    ( SELECT  a.AccountId,
													CASE WHEN ctr.TrunkID IS NOT NULL 
														THEN p_RoutinePlan 
														ELSE 0
													END AS RoutinePlan
                                      FROM tblAccount a 
												  INNER JOIN tblCustomerTrunk ON TrunkID = p_TrunkId
                                      		AND a.AccountId = tblCustomerTrunk.AccountID
                                          AND tblCustomerTrunk.Status = 1
												LEFT JOIN tblCustomerTrunk ctr
													ON ctr.TrunkID = p_TrunkId
													AND ctr.AccountID = a.AccountID
													AND ctr.RoutinePlanStatus = 1
												WHERE  FIND_IN_SET(a.AccountID,p_AccountIdList) != 0 
                                    ) a
                          WHERE FIND_IN_SET(tblRate.RateID,p_RateIDList) != 0     
                        ) r
                        LEFT JOIN ( SELECT DISTINCT
                                                    c.RateID,
                                                    c.CustomerID as AccountId ,
                                                    c.TrunkID,
                                                    c.EffectiveDate
                                          FROM      tblCustomerRate c
                                                    INNER JOIN tblCustomerTrunk ON tblCustomerTrunk.TrunkID = c.TrunkID
                                                              AND tblCustomerTrunk.AccountID = c.CustomerID
                                                              AND tblCustomerTrunk.Status = 1
                                          WHERE FIND_IN_SET(c.CustomerID,p_AccountIdList) != 0 AND c.TrunkID = p_TrunkId
                                        ) cr ON r.RateID = cr.RateID
                                                AND r.AccountId = cr.AccountId
                                                AND r.CompanyID = p_CompanyId   
                                                and cr.EffectiveDate = p_EffectiveDate 
                WHERE  
                 r.CompanyID = p_CompanyId       
                 and cr.RateID is NULL;
  
       CALL prc_ArchiveOldCustomerRate(p_AccountIdList, p_TrunkId);
       SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;                            
END