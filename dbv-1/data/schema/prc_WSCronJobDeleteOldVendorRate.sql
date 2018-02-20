CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_WSCronJobDeleteOldVendorRate`(
	IN `p_DeletedBy` TEXT
)
BEGIN

	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	INSERT INTO tblVendorRateArchive
   SELECT DISTINCT  null , -- Primary Key column
							vr.`VendorRateID`,
							vr.`AccountId`,
							vr.`TrunkID`,
							vr.`RateId`,
							vr.`Rate`,
							vr.`EffectiveDate`,
							IFNULL(vr.`EndDate`,date(now())) as EndDate,
							vr.`updated_at`,
							now() as created_at,
							p_DeletedBy AS `created_by`,
							vr.`updated_by`,
							vr.`Interval1`,
							vr.`IntervalN`,
							vr.`ConnectionFee`,
							vr.`MinimumCost`,
	   concat('Ends Today rates @ ' , now() ) as `Notes`
      FROM tblVendorRate vr
     	INNER JOIN tblAccount a on vr.AccountId = a.AccountID
		WHERE a.Status = 1 AND vr.EndDate <= NOW();

 
	DELETE  vr 
	FROM tblVendorRate vr
   inner join tblVendorRateArchive vra
   on vr.VendorRateID = vra.VendorRateID;
  

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END