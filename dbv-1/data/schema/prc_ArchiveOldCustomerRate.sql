CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_ArchiveOldCustomerRate`(IN `p_CustomerIds` longtext, IN `p_TrunkIds` longtext)
BEGIN
	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
    DROP TEMPORARY TABLE IF EXISTS _tmp_ArchiveCustomerRateIds;
    CREATE TEMPORARY TABLE _tmp_ArchiveCustomerRateIds (
        CustomerRateID INT

    );

    DROP TEMPORARY TABLE IF EXISTS _tmp_ArchiveOldEffectiveRates;
    CREATE TEMPORARY TABLE _tmp_ArchiveOldEffectiveRates (
        CustomerRateID INT,
        RateID INT,
        CustomerID INT,
        TrunkId INT,
        Rate DECIMAL(18, 6),
        RowID2 INT
    );
    DROP TEMPORARY TABLE IF EXISTS _tmp_ArchiveOldEffectiveRatesCustomerRateIds;
    CREATE TEMPORARY TABLE _tmp_ArchiveOldEffectiveRatesCustomerRateIds (
    CustomerRateID INT
    );

    DROP TEMPORARY TABLE IF EXISTS _tmp_CustomerRateIDs;
    CREATE TEMPORARY TABLE _tmp_CustomerRateIDs (
        CustomerRateID INT
    );
    
   

    INSERT INTO _tmp_CustomerRateIDs
        SELECT
            CustomerRateID
        FROM (
		  
		  		SELECT
            CustomerRateID,
				@row_num := IF(@prev_TrunkId  = cr.TrunkId AND @prev_RateID=cr.RateID and @prev_CustomerId >= cr.CustomerId ,@row_num+1,1) AS RowID,
				@prev_RateID  := cr.RateID,
				@prev_CustomerId  := cr.CustomerId,
				@prev_TrunkId  := cr.TrunkId
			
	        FROM tblCustomerRate cr ,
					(SELECT @row_num := 1) x,(SELECT @prev_RateID := '') y,(SELECT @prev_CustomerId := '') z,(SELECT @prev_TrunkId := '') v
	        WHERE effectivedate <= NOW() AND FIND_IN_SET(cr.CustomerID,p_CustomerIds) != 0 AND FIND_IN_SET(cr.TrunkID,p_TrunkIds) != 0 
		
		 ORDER BY effectivedate DESC
		) tbl
        WHERE RowID = 1;
		


    INSERT INTO _tmp_ArchiveCustomerRateIds
        SELECT
            cr.CustomerRateID
        FROM tblCustomerRate cr
         LEFT JOIN _tmp_CustomerRateIDs vrtr
            ON cr.CustomerRateID = vrtr.CustomerRateID
        WHERE vrtr.CustomerRateID IS NULL
        AND effectivedate <= NOW() AND FIND_IN_SET(cr.CustomerID,p_CustomerIds) != 0 AND FIND_IN_SET(cr.TrunkID,p_TrunkIds) != 0 ;


    /*INSERT INTO tblCustomerRateArchive (CustomerRateID
    , RateID
    , CustomerId
    , TrunkId
    , Rate
    , EffectiveDate
    , CreatedBy
    , Notes)
        SELECT
            cr.CustomerRateID,
            RateID,
            CustomerID,
            TrunkID,
            Rate,
            EffectiveDate,
            'RateManagementSystem',
            'Delete old Records' 
        FROM tblCustomerRate cr
        INNER JOIN _tmp_ArchiveCustomerRateIds AS arc
            ON arc.CustomerRateID = cr.CustomerRateID;

    DELETE tblCustomerRate
        FROM tblCustomerRate
        INNER JOIN _tmp_ArchiveCustomerRateIds AS arc
            ON arc.CustomerRateID = tblCustomerRate.CustomerRateID;*/


    INSERT INTO _tmp_ArchiveOldEffectiveRates
       SELECT 
		 	 	CustomerRateID,
            RateID,
            CustomerID,
            TrunkID,
            Rate,
            RowID
			FROM  (SELECT
            cr.CustomerRateID,
            cr.RateID,
            cr.CustomerID,
            cr.TrunkID,
            cr.Rate,
			@row_num := IF(@prev_TrunkId  = cr.TrunkId AND @prev_RateID=cr.RateID and @prev_CustomerId >= cr.CustomerId ,@row_num+1,1) AS RowID,
			@prev_RateID  := cr.RateID,
			@prev_CustomerId  := cr.CustomerId,
			@prev_TrunkId  := cr.TrunkId
        FROM tblCustomerRate cr , 
		  (SELECT @row_num := 1) x,(SELECT @prev_RateID := '') y,(SELECT @prev_CustomerId := '') z,(SELECT @prev_TrunkId := '') v
		WHERE  FIND_IN_SET(cr.CustomerID,p_CustomerIds) != 0 AND FIND_IN_SET(cr.TrunkID,p_TrunkIds) != 0 
		ORDER BY cr.CustomerID ASC,cr.TrunkID ,cr.RateID ASC,EffectiveDate ASC) tbl2;
	
	DROP TEMPORARY TABLE IF EXISTS _tmp_ArchiveOldEffectiveRates2;
	CREATE TEMPORARY TABLE IF NOT EXISTS _tmp_ArchiveOldEffectiveRates2 as (select * from _tmp_ArchiveOldEffectiveRates);

    INSERT INTO _tmp_ArchiveOldEffectiveRatesCustomerRateIds
        SELECT
            tt.CustomerRateID
        FROM _tmp_ArchiveOldEffectiveRates t
        INNER JOIN _tmp_ArchiveOldEffectiveRates2 tt
            ON tt.RowID2 = t.RowID2 + 1
            AND  t.CustomerId = tt.CustomerId
			AND  t.TrunkId = tt.TrunkId
            AND t.RateID = tt.RateID
            AND t.Rate = tt.Rate;

    /*INSERT INTO tblCustomerRateArchive (CustomerRateID
    , RateID
    , CustomerId
    , TrunkId
    , Rate
    , EffectiveDate
    , CreatedBy
    , Notes)
        SELECT
            c.CustomerRateID,
            RateID,
            CustomerID,
            TrunkID,
            Rate,
            EffectiveDate,
            'RateManagementSystem',
            'Delete not effective Records' 
        FROM tblCustomerRate c
        INNER JOIN _tmp_ArchiveOldEffectiveRatesCustomerRateIds aold
            ON aold.CustomerRateID = c.CustomerRateID;*/


    DELETE tblCustomerRate
        FROM tblCustomerRate
        INNER JOIN _tmp_ArchiveOldEffectiveRatesCustomerRateIds aold
            ON aold.CustomerRateID = tblCustomerRate.CustomerRateID;
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ ;
END