CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_ArchiveOldRateTableRate`(IN `p_RateTableId` INT)
BEGIN

 	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;  
 	
 	DELETE rtr
      FROM tblRateTableRate rtr
      INNER JOIN tblRateTableRate rtr2
      ON rtr.RateTableId = rtr2.RateTableId
      AND rtr.RateID = rtr2.RateID
      WHERE  FIND_IN_SET(rtr.RateTableId,p_RateTableId) != 0  AND rtr.EffectiveDate <= NOW()
			AND FIND_IN_SET(rtr2.RateTableId,p_RateTableId) != 0 AND rtr2.EffectiveDate <= NOW()
         AND rtr.EffectiveDate < rtr2.EffectiveDate;
    
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END