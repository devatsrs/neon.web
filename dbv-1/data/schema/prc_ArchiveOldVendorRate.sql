CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_ArchiveOldVendorRate`(
	IN `p_AccountIds` longtext
,
	IN `p_TrunkIds` longtext








,
	IN `p_DeletedBy` TEXT
)
BEGIN
 	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


	 /*1. Move Rates which EndDate <= now() */
		

	INSERT INTO tblVendorRateArchive
   SELECT DISTINCT  null , -- Primary Key column
							`VendorRateID`,
							`AccountId`,
							`TrunkID`,
							`RateId`,
							`Rate`,
							`EffectiveDate`,
							IFNULL(`EndDate`,date(now())) as EndDate,
							`updated_at`,
							now() as `created_at`,
							p_DeletedBy AS `created_by`,
							`updated_by`,
							`Interval1`,
							`IntervalN`,
							`ConnectionFee`,
							`MinimumCost`,
	  concat('Ends Today rates @ ' , now() ) as `Notes`
      FROM tblVendorRate 
      WHERE  FIND_IN_SET(AccountId,p_AccountIds) != 0 AND FIND_IN_SET(TrunkID,p_TrunkIds) != 0 AND EndDate <= NOW();


/*
     IF (FOUND_ROWS() > 0) THEN
	 	select concat(FOUND_ROWS() ," Ends Today rates" ) ;
	  END IF;
*/
 
	  
	
	DELETE  vr 
	FROM tblVendorRate vr
   inner join tblVendorRateArchive vra
   on vr.VendorRateID = vra.VendorRateID
	WHERE  FIND_IN_SET(vr.AccountId,p_AccountIds) != 0 AND FIND_IN_SET(vr.TrunkID,p_TrunkIds) != 0;
   
	
	/*  IF (FOUND_ROWS() > 0) THEN
		 select concat(FOUND_ROWS() ," sane rate " ) ;
	 END IF;
	 
	*/ 
	 
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
	
END