CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_WSCronJobDeleteOldRateTableRate`(
	IN `p_DeletedBy` TEXT

)
ThisSP:BEGIN
    
/*	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED; 

   DELETE rtr
      FROM tblRateTableRate rtr
      INNER JOIN tblRateTableRate rtr2
      ON rtr.RateTableId = rtr2.RateTableId
      AND rtr.RateID = rtr2.RateID
      WHERE rtr.EffectiveDate <= NOW()
			AND rtr2.EffectiveDate <= NOW()
         AND rtr.EffectiveDate < rtr2.EffectiveDate;
            
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;      */
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	-- UPDATE tblRateTableRate SET EndDate=NULL where EndDate='0000-00-00';
	
	UPDATE 
		tblRateTableRate rtr
	INNER JOIN tblRateTableRate rtr2
		ON rtr2.RateTableId = rtr.RateTableId
		AND rtr2.RateID = rtr.RateID
	SET
		rtr.EndDate=NOW()
	WHERE  
		rtr.EffectiveDate <= NOW() AND 
		rtr2.EffectiveDate <= NOW() AND 
		rtr.EffectiveDate < rtr2.EffectiveDate;
         
	INSERT INTO tblRateTableRateArchive
   SELECT DISTINCT  null , -- Primary Key column
							vr.`RateTableRateID`,
							vr.`RateTableId`,
							vr.`RateId`,
							vr.`Rate`,
							vr.`EffectiveDate`,
							IFNULL(vr.`EndDate`,date(now())) as EndDate,
							vr.`updated_at`,
							now() as created_at,
							p_DeletedBy AS `created_by`,
							vr.`ModifiedBy`,
							vr.`Interval1`,
							vr.`IntervalN`,
							vr.`ConnectionFee`,
	   					concat('Ends Today rates @ ' , now() ) as `Notes`
      FROM 
			tblRateTableRate vr
		WHERE 
			vr.EndDate <= NOW();

 
	DELETE  vr 
	FROM tblRateTableRate vr
   inner join tblRateTableRateArchive vra
   on vr.RateTableRateID = vra.RateTableRateID;
  

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;      

END