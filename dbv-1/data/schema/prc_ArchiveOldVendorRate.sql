CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_ArchiveOldVendorRate`(IN `p_AccountIds` longtext
, IN `p_TrunkIds` longtext)
BEGIN
 	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
  
   DELETE vr
      FROM tblVendorRate vr
      INNER JOIN tblVendorRate vr2
      ON vr2.AccountId = vr.AccountId
      AND vr2.TrunkID = vr.TrunkID
      AND vr2.RateID = vr.RateID
      WHERE  FIND_IN_SET(vr.AccountId,p_AccountIds) != 0 AND FIND_IN_SET(vr.TrunkID,p_TrunkIds) != 0 AND vr.EffectiveDate <= NOW()
			AND FIND_IN_SET(vr2.AccountId,p_AccountIds) != 0 AND FIND_IN_SET(vr2.TrunkID,p_TrunkIds) != 0 AND vr2.EffectiveDate <= NOW()
         AND vr.EffectiveDate < vr2.EffectiveDate;
	
   DROP TEMPORARY TABLE IF EXISTS _tmp_ArchiveOldEffectiveRates;
   CREATE TEMPORARY TABLE _tmp_ArchiveOldEffectiveRates (
    	  RowID INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
     	  VendorRateID INT,
        RateID INT,
        CustomerID INT,
        TrunkId INT,
        Rate DECIMAL(18, 6),
        EffectiveDate DATETIME,
        INDEX _tmp_ArchiveOldEffectiveRates_VendorRateID (`VendorRateID`,`RateID`,`TrunkId`,`CustomerID`)
	);
	DROP TEMPORARY TABLE IF EXISTS _tmp_ArchiveOldEffectiveRates2;
	CREATE TEMPORARY TABLE _tmp_ArchiveOldEffectiveRates2 (
    	  RowID INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
     	  VendorRateID INT,
        RateID INT,
        CustomerID INT,
        TrunkId INT,
        Rate DECIMAL(18, 6),
        EffectiveDate DATETIME,
       INDEX _tmp_ArchiveOldEffectiveRates_VendorRateID (`VendorRateID`,`RateID`,`TrunkId`,`CustomerID`)
	);

    


    
   INSERT INTO _tmp_ArchiveOldEffectiveRates (VendorRateID,RateID,CustomerID,TrunkID,Rate,EffectiveDate)	
	SELECT
	   VendorRateID,
	   RateID,
	   AccountId,
	   TrunkID,
	   Rate,
	   EffectiveDate
	FROM tblVendorRate 
	WHERE  FIND_IN_SET(AccountId,p_AccountIds) != 0 AND FIND_IN_SET(TrunkID,p_TrunkIds) != 0 
	ORDER BY AccountId ASC,TrunkID ,RateID ASC,EffectiveDate ASC;
	
	INSERT INTO _tmp_ArchiveOldEffectiveRates2
	SELECT * FROM _tmp_ArchiveOldEffectiveRates;

    DELETE tblVendorRate
        FROM tblVendorRate
        INNER JOIN(
        SELECT
            tt.VendorRateID
        FROM _tmp_ArchiveOldEffectiveRates t
        INNER JOIN _tmp_ArchiveOldEffectiveRates2 tt
            ON tt.RowID = t.RowID + 1
            AND  t.CustomerID = tt.CustomerID
			   AND  t.TrunkId = tt.TrunkId
            AND t.RateID = tt.RateID
            AND t.Rate = tt.Rate) aold on aold.VendorRateID = tblVendorRate.VendorRateID;
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END