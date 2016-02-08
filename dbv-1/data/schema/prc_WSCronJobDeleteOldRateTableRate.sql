CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSCronJobDeleteOldRateTableRate`()
BEGIN
    
	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED; 

    DELETE rtr
        FROM tblRateTableRate rtr
        INNER JOIN (SELECT
                RateTableRateID
            FROM (SELECT
                    RateTableRateID,
                    @row_num := IF( @prev_RateTableId = rtr.RateTableId AND @prev_RateID=rtr.RateId and @prev_effectivedate >= rtr.effectivedate ,@row_num+1,1) AS RowID,
                    @prev_RateTableId := rtr.RateTableId,
                    @prev_RateID :=rtr.RateId,
                    @prev_effectivedate := rtr.effectivedate 
                FROM tblRateTableRate rtr,(SELECT @row_num := 1) x,(SELECT @prev_RateID := '') y,(SELECT @prev_effectivedate := '') z,(SELECT @prev_RateTableId := '') v
                WHERE rtr.EffectiveDate <= NOW()
					  ORDER BY rtr.RateTableId,rtr.RateID, rtr.EffectiveDate DESC
					 ) tbl
            WHERE RowID > 1) AS artr
            ON artr.RateTableRateID = rtr.RateTableRateID;
            
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;            

END