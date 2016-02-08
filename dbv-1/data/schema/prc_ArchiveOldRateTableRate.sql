CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_ArchiveOldRateTableRate`(IN `p_RateTableId` INT)
BEGIN

 	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;  
  
    DROP TEMPORARY TABLE IF EXISTS _tmp_ArchiveRateTableRateIds;
    CREATE TEMPORARY TABLE _tmp_ArchiveRateTableRateIds (
        RateTableRateID INT
    );

    DROP TEMPORARY TABLE IF EXISTS _tmp_ArchiveOldEffectiveRates;
    CREATE TEMPORARY TABLE _tmp_ArchiveOldEffectiveRates (
        RateTableRateID INT,
        RateID INT,
        Rate DECIMAL(18, 6),
        RowID2 INT
    );
    
    DROP TEMPORARY TABLE IF EXISTS _tmp_ArchiveOldEffectiveRatesRateTableRateIds;
    CREATE TEMPORARY TABLE _tmp_ArchiveOldEffectiveRatesRateTableRateIds (
        RateTableRateID INT
    );

    DROP TEMPORARY TABLE IF EXISTS _tmp_RateTableRateIDs;
    CREATE TEMPORARY TABLE _tmp_RateTableRateIDs (
        RateTableRateID INT
    );
    
    INSERT INTO _tmp_RateTableRateIDs
        SELECT
            RateTableRateID
        FROM (SELECT
            RateTableRateID,
			@row_num := IF(@prev_RateTableId  = rtr.RateTableId AND @prev_RateID= rtr.RateID ,@row_num+1,1) AS RowID,
			@prev_RateID  := rtr.RateID,
			@prev_RateTableId  := rtr.RateTableId
        FROM tblRateTableRate rtr 
			, (SELECT @row_num := 1) x,(SELECT @prev_RateID := '') y,(SELECT @prev_RateTableId := '') z
        WHERE rtr.EffectiveDate <= NOW() AND rtr.RateTableId = p_RateTableId
		ORDER BY rtr.EffectiveDate DESC
		) tbl
        WHERE RowID = 1;


    INSERT INTO _tmp_ArchiveRateTableRateIds
        SELECT
            rtr.RateTableRateID
        FROM tblRateTableRate rtr
        LEFT JOIN _tmp_RateTableRateIDs vrtr
            ON rtr.RateTableRateID = vrtr.RateTableRateID
        WHERE vrtr.RateTableRateID IS NULL
        AND effectivedate <= NOW() AND RateTableId = p_RateTableId ;


    -- INSERT INTO tblRateTableRateArchive (
    --  RateTableRateID
    -- , RateTableId
    -- , RateID
    -- , Rate
    -- , EffectiveDate
    -- , CreatedBy
    -- , Notes)
    --    SELECT
    --        rtr.RateTableRateID,
    --        RateTableID,
    --        RateID,
    --        Rate,
    --        EffectiveDate,
    --        'RateManagementSystem',
    --        Notes = 'Delete old Records'
    --    FROM dbo.tblRateTableRate rtr
    --    INNER JOIN _tmp_ArchiveRateTableRateIds AS artr
    --        ON artr.RateTableRateID = rtr.RateTableRateID

 


    DELETE rtr.*
        FROM tblRateTableRate rtr
        INNER JOIN _tmp_ArchiveRateTableRateIds AS artr
            ON artr.RateTableRateID = rtr.RateTableRateID;


    -- INSERT INTO _tmp_ArchiveOldEffectiveRates
    --    SELECT
    --        cr.RateTableRateID,
    --        cr.RateID,
    --        cr.Rate,
    --        ROW_NUMBER() OVER (PARTITION BY cr.RateID, cr.RateTableId ORDER BY cr.RateID ASC, EffectiveDate ASC) RowID2
    --    FROM tblRateTableRate cr
    --    WHERE RateTableId = p_RateTableId


    -- INSERT INTO _tmp_ArchiveOldEffectiveRatesRateTableRateIds
    --    SELECT
    --        tt.RateTableRateID
    --    FROM _tmp_ArchiveOldEffectiveRates t
    --    INNER JOIN _tmp_ArchiveOldEffectiveRates tt
    --        ON tt.RowID2 = t.RowID2 + 1
    --        AND t.RateID = tt.RateID
    --        AND t.Rate = tt.Rate

    -- INSERT INTO tblRateTableRateArchive (RateTableRateID
    -- , RateID
    -- , RateTableId
    -- , Rate
    -- , EffectiveDate
    -- , CreatedBy
    -- , Notes)
    --    SELECT
    --        c.RateTableRateID,
    --        RateID,
    --        RateTableID,
    --        Rate,
    --        EffectiveDate,
    --        'RateManagementSystem',
    --        Notes = 'Delete not effective Records'
    --    FROM dbo.tblRateTableRate c
    --    INNER JOIN _tmp_ArchiveOldEffectiveRatesRateTableRateIds aold
    --        ON aold.RateTableRateID = c.RateTableRateID


    --    FROM tblRateTableRate
    --    INNER JOIN _tmp_ArchiveOldEffectiveRatesRateTableRateIds aold
    --        ON aold.RateTableRateID = tblRateTableRate.RateTableRateID
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END