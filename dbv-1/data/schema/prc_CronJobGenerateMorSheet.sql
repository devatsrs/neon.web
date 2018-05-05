CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_CronJobGenerateMorSheet`(
	IN `p_CustomerID` INT ,
	IN `p_trunks` VARCHAR(200) ,
	IN `p_Effective` VARCHAR(50)
,
	IN `p_CustomDate` DATE
)
BEGIN
    
	-- get customer rates
	CALL vwCustomerRate(p_CustomerID,p_Trunks,p_Effective,p_CustomDate);

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
        RoutinePlanName VARCHAR(50),
        Rate DECIMAL(18, 6),
        EffectiveDate DATE,
        LastModifiedDate DATETIME,
        LastModifiedBy VARCHAR(50),
        CustomerRateId INT,
        TrunkID INT,
        RateTableRateId INT,
        IncludePrefix TINYINT,
        Prefix VARCHAR(50),
        RatePrefix VARCHAR(50),
        AreaPrefix VARCHAR(50),
        SubCode VARCHAR(50)
    );
    
    INSERT INTO tmp_morrateall_
     SELECT 
		  tc.RateID,	  			
	  	  c.Country,	  	          		      
		  c.ISO3,
		  tc.Code,
		  tc.Description,
		  tc.Interval1,
        tc.IntervalN,
        tc.ConnectionFee,
        tc.RoutinePlanName,
        tc.Rate,
        tc.EffectiveDate,
        tc.LastModifiedDate,
        tc.LastModifiedBy,
        tc.CustomerRateId,
        tc.TrunkID,
        tc.RateTableRateId,
        tc.IncludePrefix,
        tc.Prefix,
        tc.RatePrefix,
        tc.AreaPrefix,
        'FIX' as `SubCode`
	  	 FROM tmp_customerrateall_ tc
	  			 INNER JOIN tblRate r ON tc.RateID = r.RateID
				 LEFT JOIN tblCountry c ON r.CountryID = c.CountryID 		
					;
	
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