CREATE DEFINER=`neon-user`@`192.168.1.25` PROCEDURE `prc_ArchiveOldRateTableRate`(
	IN `p_RateTableIds` longtext,
	IN `p_DeletedBy` TEXT


)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	/* SET EndDate of current time to older rates */
	-- for example there are 3 rates, today's date is 2018-04-11
	-- 1. Code 	Rate 	EffectiveDate
	-- 1. 91 	0.1 	2018-04-09
	-- 2. 91 	0.2 	2018-04-10
	-- 3. 91 	0.3 	2018-04-11
	/* Then it will delete 2018-04-09 and 2018-04-10 date's rate */
	UPDATE 
		tblRateTableRate rtr
	INNER JOIN tblRateTableRate rtr2
		ON rtr2.RateTableId = rtr.RateTableId
		AND rtr2.RateID = rtr.RateID
	SET
		rtr.EndDate=NOW()
	WHERE  
		FIND_IN_SET(rtr.RateTableId,p_RateTableIds) != 0 AND rtr.EffectiveDate <= NOW() AND 
		FIND_IN_SET(rtr2.RateTableId,p_RateTableIds) != 0 AND rtr2.EffectiveDate <= NOW() AND 
		rtr.EffectiveDate < rtr2.EffectiveDate AND rtr.RateTableRateID != rtr2.RateTableRateID;
		
	/*1. Move Rates which EndDate <= now() */

	INSERT INTO tblRateTableRateArchive
	SELECT DISTINCT  null , -- Primary Key column
		`RateTableRateID`,
		`RateTableId`,
		`RateId`,
		`Rate`,
		`EffectiveDate`,
		IFNULL(`EndDate`,date(now())) as EndDate,
		`updated_at`,
		now() as `created_at`,
		p_DeletedBy AS `created_by`,
		`ModifiedBy`,
		`Interval1`,
		`IntervalN`,
		`ConnectionFee`,
		concat('Ends Today rates @ ' , now() ) as `Notes`
	FROM tblRateTableRate 
	WHERE  FIND_IN_SET(RateTableId,p_RateTableIds) != 0 AND EndDate <= NOW();

	/*
	IF (FOUND_ROWS() > 0) THEN
	select concat(FOUND_ROWS() ," Ends Today rates" ) ;
	END IF;
	*/

	DELETE  vr 
	FROM tblRateTableRate vr
	inner join tblRateTableRateArchive vra
		on vr.RateTableRateID = vra.RateTableRateID
	WHERE  FIND_IN_SET(vr.RateTableId,p_RateTableIds) != 0;

	/*  IF (FOUND_ROWS() > 0) THEN
	select concat(FOUND_ROWS() ," sane rate " ) ;
	END IF;
	*/ 

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END