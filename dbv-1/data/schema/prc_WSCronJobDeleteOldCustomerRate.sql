CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_WSCronJobDeleteOldCustomerRate`(
	IN `p_DeletedBy` TEXT
)
BEGIN
     
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

   /*DELETE cr
   FROM tblCustomerRate cr
   INNER JOIN tblCustomerRate cr2
   ON cr2.CustomerID = cr.CustomerID
   AND cr2.TrunkID = cr.TrunkID
   AND cr2.RateID = cr.RateID
   WHERE   cr.EffectiveDate <= NOW() AND cr2.EffectiveDate <= NOW() AND cr.EffectiveDate < cr2.EffectiveDate;*/
   
   /* SET EndDate of current time to older rates */
	-- for example there are 3 rates, today's date is 2018-04-11
	-- 1. Code 	Rate 	EffectiveDate
	-- 1. 91 	0.1 	2018-04-09
	-- 2. 91 	0.2 	2018-04-10
	-- 3. 91 	0.3 	2018-04-11
	/* Then it will delete 2018-04-09 and 2018-04-10 date's rate */
	UPDATE 
		tblCustomerRate cr
	INNER JOIN tblCustomerRate cr2
		ON cr2.CustomerID = cr.CustomerID
		AND cr2.TrunkID = cr.TrunkID
		AND cr2.RateID = cr.RateID
	SET
		cr.EndDate=NOW()
	WHERE  
		cr.EffectiveDate <= NOW() AND 
		cr2.EffectiveDate <= NOW() AND 
		cr.EffectiveDate < cr2.EffectiveDate;

   INSERT INTO tblCustomerRateArchive
	SELECT DISTINCT  null , -- Primary Key column
		`CustomerRateID`,
		`CustomerID`,
		`TrunkID`,
		`RateId`,
		`Rate`,
		`EffectiveDate`,
		IFNULL(`EndDate`,date(now())) as EndDate,
		now() as `created_at`,
		p_DeletedBy AS `created_by`,
		`LastModifiedDate`,
		`LastModifiedBy`,
		`Interval1`,
		`IntervalN`,
		`ConnectionFee`,
		`RoutinePlan`,
		concat('Ends Today rates @ ' , now() ) as `Notes`
	FROM 
		tblCustomerRate 
	WHERE  
		EndDate <= NOW();


	DELETE  cr 
	FROM tblCustomerRate cr
	INNER JOIN tblCustomerRateArchive cra
	ON cr.CustomerRateID = cra.CustomerRateID;
	
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;         
	            
END