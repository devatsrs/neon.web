CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_CronJobGenerateMorVendorSheet`(
	IN `p_AccountID` INT ,
	IN `p_trunks` VARCHAR(200),
	IN `p_Effective` VARCHAR(50)


)
BEGIN
		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
		
		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
    CREATE TEMPORARY TABLE tmp_VendorRate_ (
        TrunkId INT,
   	  RateId INT,
        Rate DECIMAL(18,6),
        EffectiveDate DATE,
        Interval1 INT,
        IntervalN INT,
        ConnectionFee DECIMAL(18,6),
        INDEX tmp_RateTable_RateId (`RateId`)  
    );
        INSERT INTO tmp_VendorRate_
        SELECT   `TrunkID`, `RateId`, `Rate`, `EffectiveDate`, `Interval1`, `IntervalN`, `ConnectionFee`
		  FROM tblVendorRate WHERE tblVendorRate.AccountId =  p_AccountID 
								AND FIND_IN_SET(tblVendorRate.TrunkId,p_trunks) != 0 
                        AND 
								(
					  				(p_Effective = 'Now' AND EffectiveDate <= NOW()) 
								  	OR 
								  	(p_Effective = 'Future' AND EffectiveDate > NOW())
								  	OR 
								  	(p_Effective = 'All')
								);
								
		 DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate4_;		
       CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRate4_ as (select * from tmp_VendorRate_);
		 	        
      DELETE n1 FROM tmp_VendorRate_ n1, tmp_VendorRate4_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate 
 	   AND n1.TrunkID = n2.TrunkID
	   AND  n1.RateId = n2.RateId
		AND n1.EffectiveDate <= NOW()
		AND n2.EffectiveDate <= NOW();
		
		DROP TEMPORARY TABLE IF EXISTS tmp_morrateall_;
    CREATE TEMPORARY TABLE tmp_morrateall_ (
        RateID INT,
        Country VARCHAR(155),
        CountryCode VARCHAR(50),
        Code VARCHAR(50),        
        Description VARCHAR(200),        
        Interval1 INT,
        IntervalN INT,
        ConnectionFee DECIMAL(18, 6),
        Rate DECIMAL(18, 6),
        SubCode VARCHAR(50)
    );
                        
     INSERT INTO tmp_morrateall_    
     SELECT Distinct
          tblRate.RateID as `RateID`,
			  c.Country as `Country`,
			  c.ISO3 as `CountryCode`,
			  tblRate.Code as `Code`,
               tblRate.Description as `Description` ,
               CASE WHEN tblVendorRate.Interval1 IS NOT NULL
                   THEN tblVendorRate.Interval1
                   ElSE tblRate.Interval1
               END AS `Interval1`,
               CASE WHEN tblVendorRate.IntervalN IS NOT NULL
                   THEN tblVendorRate.IntervalN
                   ElSE tblRate.IntervalN
               END  AS `IntervalN`,
               tblVendorRate.ConnectionFee as `ConnectionFee`,
               Abs(tblVendorRate.Rate) as `Rate`,
               'FIX' as `SubCode`
			   
       FROM    tmp_VendorRate_ as tblVendorRate 
               JOIN tblRate on tblVendorRate.RateId =tblRate.RateID
               LEFT JOIN tblCountry as c
                   ON tblRate.CountryID = c.CountryID;          
						
		UPDATE tmp_morrateall_ 
	  			SET SubCode='MOB' 
	  			WHERE Description LIKE '%Mobile%';

         
		SELECT DISTINCT 
	      Country as `Direction` ,
	      Description  as `Destination`,
		   Code as `Prefix`,
		   SubCode as `Subcode`,
		   CountryCode as `Country code`,
		   Rate as `Rate(EUR)`,
		   ConnectionFee as `Connection Fee(EUR)`,
		   Interval1 as `Increment`,
		   IntervalN as `Minimal Time`,
		   '0:00:00 'as `Start Time`,
		   '23:59:59' as `End Time`,
		   '' as `Week Day`
     FROM tmp_morrateall_; 
							
      SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END