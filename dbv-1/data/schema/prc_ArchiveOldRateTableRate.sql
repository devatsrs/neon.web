CREATE DEFINER=`neon-user`@`192.168.1.25` PROCEDURE `prc_ArchiveOldRateTableRate`(
	IN `p_RateTableIds` longtext,
	IN `p_DeletedBy` TEXT

)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

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