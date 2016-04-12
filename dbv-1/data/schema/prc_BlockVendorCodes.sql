CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_BlockVendorCodes`(IN `p_companyid` INT , IN `p_AccountId` TEXT, IN `p_trunkID` INT, IN `p_CountryIDs` TEXT, IN `p_Codes` TEXT, IN `p_Username` VARCHAR(100), IN `p_action` TEXT, IN `p_isCountry` INT, IN `p_isAllCountry` INT)
BEGIN	 

		
	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;	
	 
	 
		
  	IF p_isAllCountry = 1		
	THEN	
		SELECT GROUP_CONCAT(CountryID) INTO p_CountryIDs  FROM tblCountry;
	END IF;
	IF p_isCountry = 0
	THEN
		DROP TEMPORARY TABLE IF EXISTS tmp_codes_;
	   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_codes_(
				Code varchar(20)
		);
	END IF;	
   		IF p_isAllCountry = 2 -- code with selected row
		THEN
   		insert into tmp_codes_
		   SELECT  distinct tblRate.Code 
            FROM    tblRate
          INNER JOIN tblVendorRate ON tblRate.RateID = tblVendorRate.RateId  AND tblVendorRate.TrunkID = p_trunkID   
          INNER JOIN tblVendorTrunk ON tblVendorTrunk.CodeDeckId = tblRate.CodeDeckId  AND tblVendorTrunk.TrunkID = p_trunkID   
           WHERE    tblRate.CompanyID = p_companyid  AND ( p_Codes  = '' OR FIND_IN_SET(tblRate.Code,p_Codes) != 0 );
        END IF;   

        IF p_isAllCountry = 3 -- code with critearea
		THEN
   		insert into tmp_codes_
		   SELECT  distinct tblRate.Code 
            FROM    tblRate
          INNER JOIN tblVendorRate ON tblRate.RateID = tblVendorRate.RateId  AND tblVendorRate.TrunkID = p_trunkID   
          INNER JOIN tblVendorTrunk ON tblVendorTrunk.CodeDeckId = tblRate.CodeDeckId  AND tblVendorTrunk.TrunkID = p_trunkID   
           WHERE    tblRate.CompanyID = p_companyid  AND ( p_Codes  = '' OR Code LIKE REPLACE(p_Codes,'*', '%') );
        END IF; 
	
	 
	IF p_isCountry = 0 AND p_action = 1 -- block Code
   THEN 

		INSERT INTO tblVendorBlocking (AccountId,RateId,TrunkID,BlockedBy)
			SELECT DISTINCT tblVendorRate.AccountID,tblRate.RateID,tblVendorRate.TrunkID,p_Username
			FROM tblVendorRate
			INNER JOIN tblRate ON tblRate.RateID = tblVendorRate.RateId 
				AND tblRate.CompanyID = p_companyid
			Inner join tmp_codes_ c on c.Code = tblRate.Code
			LEFT JOIN tblVendorBlocking ON tblVendorBlocking.AccountId = tblVendorRate.AccountId 
				AND tblVendorBlocking.RateId = tblRate.RateID 
				AND tblVendorBlocking.TrunkID = p_trunkID
			WHERE tblVendorBlocking.VendorBlockingId IS NULL
			 -- AND FIND_IN_SET(tblRate.Code,p_Codes) != 0 
			 AND tblVendorRate.TrunkID = p_trunkID 
			 AND FIND_IN_SET (tblVendorRate.AccountID,p_AccountId) != 0 ;
	END IF; 

	IF p_isCountry = 0 AND p_action = 0 -- unblock Code
	THEN
		DELETE  tblVendorBlocking
	  	FROM      tblVendorBlocking
	  	INNER JOIN tblVendorRate ON tblVendorRate.RateID = tblVendorBlocking.RateId
			AND tblVendorRate.TrunkID =  p_trunkID
	  	INNER JOIN tblRate ON  tblRate.RateID = tblVendorRate.RateId AND tblRate.CompanyID = p_companyid
	  	Inner join tmp_codes_ c on c.Code = tblRate.Code
		WHERE FIND_IN_SET (tblVendorRate.AccountID,p_AccountId) != 0 ;
	END IF;
	
	
	IF p_isCountry = 1 AND p_action = 1 -- block Country
	THEN
		INSERT INTO tblVendorBlocking (AccountId,CountryId,TrunkID,BlockedBy)
		SELECT DISTINCT tblVendorRate.AccountID,tblRate.CountryID,p_trunkID,p_Username
		FROM tblVendorRate  
		INNER JOIN tblRate ON tblVendorRate.RateId = tblRate.RateID 
			AND tblRate.CompanyID = p_companyid
			AND  FIND_IN_SET(tblRate.CountryID,p_CountryIDs) != 0
		LEFT JOIN tblVendorBlocking ON tblVendorBlocking.AccountId = tblVendorRate.AccountID 
			AND tblRate.CountryID = tblVendorBlocking.CountryId
			AND tblVendorBlocking.TrunkID = p_trunkID
		WHERE tblVendorBlocking.VendorBlockingId IS NULL AND FIND_IN_SET (tblVendorRate.AccountID,p_AccountId) != 0
			AND tblVendorRate.TrunkID = p_trunkID;
		 
	END IF;
	
	
	IF p_isCountry = 1 AND p_action = 0 -- unblock Count
	THEN
		DELETE FROM tblVendorBlocking
		WHERE tblVendorBlocking.TrunkID = p_trunkID AND FIND_IN_SET (tblVendorBlocking.AccountId,p_AccountId) !=0 AND FIND_IN_SET(tblVendorBlocking.CountryID,p_CountryIDs) != 0;
	END IF;
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END