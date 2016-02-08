CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_ArchiveOldVendorRate`(IN `p_AccountIds` longtext
, IN `p_TrunkIds` longtext)
BEGIN
 	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
  
    DROP TEMPORARY TABLE IF EXISTS _tmp_ArchiveVendorRateIds;
    CREATE TEMPORARY TABLE _tmp_ArchiveVendorRateIds (
        VendorRateID INT

    );

    DROP TEMPORARY TABLE IF EXISTS _tmp_ArchiveOldEffectiveRates;
    CREATE TEMPORARY TABLE _tmp_ArchiveOldEffectiveRates (
        VendorRateID INT,
        RateID INT,
        AccountId INT,
	     TrunkId INT,
        Rate DECIMAL(18, 6),
        RowID2 INT
    );
    DROP TEMPORARY TABLE IF EXISTS _tmp_ArchiveOldEffectiveRatesVendorRateIds;
    CREATE TEMPORARY TABLE _tmp_ArchiveOldEffectiveRatesVendorRateIds (
        VendorRateID INT
    );

    DROP TEMPORARY TABLE IF EXISTS _tmp_VendorRateIDs;
    CREATE TEMPORARY TABLE _tmp_VendorRateIDs (
        VendorRateID INT
    );
    
	 
	
    INSERT INTO _tmp_VendorRateIDs
        SELECT
            VendorRateID
        FROM (SELECT
            VendorRateID,
			@row_num := IF(@prev_TrunkId  = vr.TrunkId AND @prev_RateID=vr.RateID and @prev_AccountId >= vr.AccountId ,@row_num+1,1) AS RowID,
			@prev_RateID  := vr.RateID,
			@prev_AccountId  := vr.AccountId,
			@prev_TrunkId  := vr.TrunkId
        FROM tblVendorRate vr
		, (SELECT @row_num := 1) x,(SELECT @prev_RateID := '') y,(SELECT @prev_AccountId := '') z,(SELECT @prev_TrunkId := '') v
      
        WHERE vr.EffectiveDate <= NOW()  AND FIND_IN_SET(vr.AccountId,p_AccountIds) != 0 AND FIND_IN_SET(vr.TrunkID,p_TrunkIds) != 0 
		 ORDER BY effectivedate DESC
		) tbl
        WHERE RowID = 1;


    INSERT INTO _tmp_ArchiveVendorRateIds
        SELECT
            vr.VendorRateID
        FROM tblVendorRate vr
        LEFT JOIN _tmp_VendorRateIDs vrtr
            ON vr.VendorRateID = vrtr.VendorRateID
        WHERE vr.VendorRateID IS NULL
        AND effectivedate <= NOW() AND FIND_IN_SET(vr.AccountId,p_AccountIds) != 0 AND FIND_IN_SET(vr.TrunkID,p_TrunkIds) != 0 ;


    /*INSERT INTO tblVendorRateArchive (VendorRateID
    , RateID
    , AccountId
    , TrunkId
    , Rate
    , EffectiveDate
    , CreatedBy
    , Notes)
        SELECT
            cr.VendorRateID,
            RateID,
            AccountId,
            TrunkID,
            Rate,
            EffectiveDate,
            'RateManagementSystem',
            'Delete old Records'  
        FROM tblVendorRate cr
        INNER JOIN _tmp_ArchiveVendorRateIds AS arc
            ON arc.VendorRateID = cr.VendorRateID;*/

    DELETE vr.*
        FROM tblVendorRate vr
        INNER JOIN _tmp_ArchiveVendorRateIds AS arc
            ON arc.VendorRateID = vr.VendorRateID;


    INSERT INTO _tmp_ArchiveOldEffectiveRates
        SELECT  
			   VendorRateID,
            RateID,
            AccountId,
            TrunkID,
            Rate,
				RowID
			FROM(SELECT
            vr.VendorRateID,
            vr.RateID,
            vr.AccountId,
            vr.TrunkID,
            vr.Rate,
			@row_num := IF(@prev_TrunkId  = vr.TrunkId AND @prev_RateID=vr.RateID and @prev_AccountId >= vr.AccountId ,@row_num+1,1) AS RowID,
			@prev_RateID  := vr.RateID,
			@prev_AccountId  := vr.AccountId,
			@prev_TrunkId  := vr.TrunkId
        FROM tblVendorRate vr
			,(SELECT @row_num := 1) x,(SELECT @prev_RateID := '') y,(SELECT @prev_AccountId := '') z,(SELECT @prev_TrunkId := '') v

        WHERE FIND_IN_SET(vr.AccountId,p_AccountIds) != 0 AND FIND_IN_SET(vr.TrunkID,p_TrunkIds) != 0 
		ORDER BY vr.VendorRateID ASC,vr.TrunkID ASC, vr.RateID ASC, vr.EffectiveDate ASC) tbl2;
	
		DROP TEMPORARY TABLE IF EXISTS _tmp_ArchiveOldEffectiveRates2;
	 CREATE TEMPORARY TABLE IF NOT EXISTS _tmp_ArchiveOldEffectiveRates2 as (select * from _tmp_ArchiveOldEffectiveRates);
    INSERT INTO _tmp_ArchiveOldEffectiveRatesVendorRateIds
        SELECT
            tt.VendorRateID
        FROM _tmp_ArchiveOldEffectiveRates t
        INNER JOIN _tmp_ArchiveOldEffectiveRates2 tt
            ON tt.RowID2 = t.RowID2 + 1
            AND t.RateID = tt.RateID
            AND t.AccountId  = tt.AccountId
            AND t.TrunkId = tt.TrunkId
            AND t.Rate = tt.Rate;

    /*INSERT INTO tblVendorRateArchive (VendorRateID
    , RateID
    , AccountId
    , TrunkId
    , Rate
    , EffectiveDate
    , CreatedBy
    , Notes)
        SELECT
            c.VendorRateID,
            RateID,
            AccountId,
            TrunkID,
            Rate,
            EffectiveDate,
            'RateManagementSystem',
            'Delete not effective Records'  
        FROM tblVendorRate c
        INNER JOIN _tmp_ArchiveOldEffectiveRatesVendorRateIds aold
            ON aold.VendorRateID = c.VendorRateID;*/


    DELETE vr.*
        FROM tblVendorRate vr
        INNER JOIN _tmp_ArchiveOldEffectiveRatesVendorRateIds aold
            ON aold.VendorRateID = vr.VendorRateID;
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END