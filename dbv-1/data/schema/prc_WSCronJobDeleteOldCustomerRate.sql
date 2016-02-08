CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSCronJobDeleteOldCustomerRate`()
BEGIN
     
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    DELETE tblCustomerRate
	 FROM tblCustomerRate 
        INNER JOIN (SELECT
                CustomerRateID
            FROM (SELECT
                    CustomerRateID,
                    @row_num := IF(@prev_CustomerId = cr.CustomerId AND @prev_TrunkId = cr.TrunkId AND @prev_RateID=cr.RateId AND  @prev_effectivedate >= cr.effectivedate ,@row_num+1,1) AS RowID,
						  @prev_RateID  := cr.RateId,
						  @prev_effectivedate  := cr.effectivedate,
						  @prev_TrunkId := cr.TrunkId,
						  @prev_CustomerId := cr.CustomerId
                FROM tblCustomerRate cr ,(SELECT @row_num := 1) x,(SELECT @prev_RateID := '') y,(SELECT @prev_effectivedate := '') z,(SELECT @prev_CustomerId := '') v,(SELECT @prev_TrunkId := '') u
                WHERE effectivedate <= NOW()
					 ORDER BY cr.CustomerId,cr.TrunkId,cr.RateID, effectivedate DESC
					 ) tbl
            WHERE RowID > 1) arc
            ON arc.CustomerRateID = tblCustomerRate.CustomerRateID;

    DELETE tblCustomerRate 
	 FROM tblCustomerRate
        INNER JOIN (SELECT
                tt.CustomerRateID
            FROM (SELECT
                    cr.CustomerRateID,
                    cr.RateID,
                    cr.CustomerID,
                    cr.TrunkID,
                    cr.Rate,
                    @row_num := IF(@prev_CustomerId = cr.CustomerId AND @prev_TrunkId = cr.TrunkId AND @prev_RateID=cr.RateId AND  @prev_effectivedate >= cr.effectivedate ,@row_num+1,1) AS RowID2,
						  @prev_RateID  := cr.RateId,
						  @prev_effectivedate  := cr.effectivedate,
						  @prev_TrunkId := cr.TrunkId,
						  @prev_CustomerId := cr.CustomerId 
                FROM tblCustomerRate cr,(SELECT @row_num := 1) x,(SELECT @prev_RateID := '') y,(SELECT @prev_effectivedate := '') z,(SELECT @prev_CustomerId := '') v,(SELECT @prev_TrunkId := '') u
					  ORDER BY cr.CustomerId,cr.TrunkId,cr.RateID, effectivedate DESC
					 ) t
            INNER JOIN (SELECT
                    cr.CustomerRateID,
                    cr.RateID,
                    cr.CustomerID,
                    cr.TrunkID,
                    cr.Rate,
                    @row_num := IF(@prev_CustomerId = cr.CustomerId AND @prev_TrunkId = cr.TrunkId AND @prev_RateID=cr.RateId AND  @prev_effectivedate >= cr.effectivedate ,@row_num+1,1) AS RowID2,
						  @prev_RateID  := cr.RateId,
						  @prev_effectivedate  := cr.effectivedate,
						  @prev_TrunkId := cr.TrunkId,
						  @prev_CustomerId := cr.CustomerId 
                FROM tblCustomerRate cr,(SELECT @row_num := 1) x,(SELECT @prev_RateID := '') y,(SELECT @prev_effectivedate := '') z,(SELECT @prev_CustomerId := '') v,(SELECT @prev_TrunkId := '') u
					  ORDER BY cr.CustomerId,cr.TrunkId,cr.RateID, effectivedate DESC ) tt
                ON tt.RowID2 = t.RowID2 + 1
                AND t.CustomerId = tt.CustomerId
                AND t.TrunkId = tt.TrunkId
                AND t.RateID = tt.RateID
                AND t.Rate = tt.Rate) aold
            ON aold.CustomerRateID = tblCustomerRate.CustomerRateID;
            
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;         
	            
END