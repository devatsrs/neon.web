CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_ArchiveOldCustomerRate`(IN `p_CustomerIds` longtext, IN `p_TrunkIds` longtext)
BEGIN
	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		DELETE cr
      FROM tblCustomerRate cr
      INNER JOIN tblCustomerRate cr2
      ON cr2.CustomerID = cr.CustomerID
      AND cr2.TrunkID = cr.TrunkID
      AND cr2.RateID = cr.RateID
      WHERE  FIND_IN_SET(cr.CustomerID,p_CustomerIds) != 0 AND FIND_IN_SET(cr.TrunkID,p_TrunkIds) != 0 AND cr.EffectiveDate <= NOW()
			AND FIND_IN_SET(cr2.CustomerID,p_CustomerIds) != 0 AND FIND_IN_SET(cr2.TrunkID,p_TrunkIds) != 0 AND cr2.EffectiveDate <= NOW()
         AND cr.EffectiveDate < cr2.EffectiveDate;

	DROP TEMPORARY TABLE IF EXISTS _tmp_ArchiveOldEffectiveRates;
   CREATE TEMPORARY TABLE _tmp_ArchiveOldEffectiveRates (
    	  RowID INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
     	  CustomerRateID INT,
        RateID INT,
        CustomerID INT,
        TrunkId INT,
        Rate DECIMAL(18, 6),
        EffectiveDate DATETIME,
        INDEX _tmp_ArchiveOldEffectiveRates_CustomerRateID (`CustomerRateID`,`RateID`,`TrunkId`)
	);
	DROP TEMPORARY TABLE IF EXISTS _tmp_ArchiveOldEffectiveRates2;
	CREATE TEMPORARY TABLE _tmp_ArchiveOldEffectiveRates2 (
    	  RowID INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
     	  CustomerRateID INT,
        RateID INT,
        CustomerID INT,
        TrunkId INT,
        Rate DECIMAL(18, 6),
        EffectiveDate DATETIME,
       INDEX _tmp_ArchiveOldEffectiveRates_CustomerRateID (`CustomerRateID`,`RateID`,`TrunkId`)
	);

    


    
   INSERT INTO _tmp_ArchiveOldEffectiveRates (CustomerRateID,RateID,CustomerID,TrunkID,Rate,EffectiveDate)	
	SELECT
	   cr.CustomerRateID,
	   cr.RateID,
	   cr.CustomerID,
	   cr.TrunkID,
	   cr.Rate,
	   cr.EffectiveDate
	FROM tblCustomerRate cr
	WHERE  FIND_IN_SET(cr.CustomerID,p_CustomerIds) != 0 AND FIND_IN_SET(cr.TrunkID,p_TrunkIds) != 0 
	ORDER BY cr.CustomerID ASC,cr.TrunkID ,cr.RateID ASC,EffectiveDate ASC;
	
	INSERT INTO _tmp_ArchiveOldEffectiveRates2
	SELECT * FROM _tmp_ArchiveOldEffectiveRates;

    DELETE tblCustomerRate
        FROM tblCustomerRate
        INNER JOIN(
        SELECT
            tt.CustomerRateID
        FROM _tmp_ArchiveOldEffectiveRates t
        INNER JOIN _tmp_ArchiveOldEffectiveRates2 tt
            ON tt.RowID = t.RowID + 1
            AND  t.CustomerId = tt.CustomerId
			AND  t.TrunkId = tt.TrunkId
            AND t.RateID = tt.RateID
            AND t.Rate = tt.Rate) aold on aold.CustomerRateID = tblCustomerRate.CustomerRateID;

     
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ ;
END