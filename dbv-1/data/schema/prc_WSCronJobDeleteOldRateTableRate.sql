CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSCronJobDeleteOldRateTableRate`()
BEGIN
    
	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED; 

   DELETE rtr
      FROM tblRateTableRate rtr
      INNER JOIN tblRateTableRate rtr2
      ON rtr.RateTableId = rtr2.RateTableId
      AND rtr.RateID = rtr2.RateID
      WHERE rtr.EffectiveDate <= NOW()
			AND rtr2.EffectiveDate <= NOW()
         AND rtr.EffectiveDate < rtr2.EffectiveDate;
            
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;            

END