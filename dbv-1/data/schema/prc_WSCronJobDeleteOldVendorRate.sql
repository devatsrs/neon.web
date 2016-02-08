CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSCronJobDeleteOldVendorRate`()
BEGIN
     
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;	

    DELETE tblVendorRate
        FROM tblVendorRate
        INNER JOIN (SELECT
                VendorRateID
            FROM (SELECT
                    VendorRateID,
                    @row_num := IF(@prev_AccountId = vr.AccountId AND @prev_TrunkId = vr.TrunkId AND @prev_RateID=vr.RateId AND  @prev_effectivedate >= vr.effectivedate ,@row_num+1,1) AS RowID,
						  @prev_RateID  := vr.RateId,
						  @prev_effectivedate  := vr.effectivedate,
						  @prev_TrunkId := vr.TrunkId,
						  @prev_AccountId := vr.AccountId
                FROM tblVendorRate vr,(SELECT @row_num := 1) x,(SELECT @prev_RateID := '') y,(SELECT @prev_effectivedate := '') z,(SELECT @prev_AccountId := '') v,(SELECT @prev_TrunkId := '') u
                WHERE effectivedate <= NOW()
					 ORDER BY vr.AccountId,vr.TrunkId,vr.RateID, vr.effectivedate DESC
					 ) tbl
            WHERE RowID > 1) arc
            ON arc.VendorRateID = tblVendorRate.VendorRateID;

    DELETE tblVendorRate
        FROM tblVendorRate
        INNER JOIN (SELECT
                tt.VendorRateID
            FROM (SELECT
                    vr.VendorRateID,
                    vr.RateID,
                    vr.AccountId,
                    vr.TrunkID,
                    vr.Rate,
						  @row_num := IF(@prev_AccountId = vr.AccountId AND @prev_TrunkId = vr.TrunkId AND @prev_RateID=vr.RateId AND  @prev_effectivedate >= vr.effectivedate ,@row_num+1,1) AS RowID2,
						  @prev_RateID  := vr.RateId,
						  @prev_effectivedate  := vr.effectivedate,
						  @prev_TrunkId := vr.TrunkId,
						  @prev_AccountId := vr.AccountId
                FROM tblVendorRate vr,(SELECT @row_num := 1) x,(SELECT @prev_RateID := '') y,(SELECT @prev_effectivedate := '') z,(SELECT @prev_AccountId := '') v,(SELECT @prev_TrunkId := '') u
					 ORDER BY vr.AccountId,vr.TrunkId,vr.RateID, vr.effectivedate DESC
					 ) t
            INNER JOIN (SELECT
                    vr.VendorRateID,
                    vr.RateID,
                    vr.AccountId,
                    vr.TrunkID,
                    vr.Rate,
					  	  @row_num := IF(@prev_AccountId = vr.AccountId AND @prev_TrunkId = vr.TrunkId AND @prev_RateID=vr.RateId AND  @prev_effectivedate >= vr.effectivedate ,@row_num+1,1) AS RowID2,
						  @prev_RateID  := vr.RateId,
						  @prev_effectivedate  := vr.effectivedate,
						  @prev_TrunkId := vr.TrunkId,
						  @prev_AccountId := vr.AccountId
                FROM tblVendorRate vr,(SELECT @row_num := 1) x,(SELECT @prev_RateID := '') y,(SELECT @prev_effectivedate := '') z,(SELECT @prev_AccountId := '') v,(SELECT @prev_TrunkId := '') u
                ORDER BY vr.AccountId,vr.TrunkId,vr.RateID, vr.effectivedate DESC
					 ) tt
                ON tt.RowID2 = t.RowID2 + 1
                AND t.AccountId = tt.AccountId
                AND t.TrunkId = tt.TrunkId
                AND t.RateID = tt.RateID
                AND t.Rate = tt.Rate) aold
            ON aold.VendorRateID = tblVendorRate.VendorRateID;
      
      SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END