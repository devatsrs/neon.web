CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_CronJobGenerateM2Sheet`(
	IN `p_CustomerID` INT ,
	IN `p_trunks` VARCHAR(200) ,
	IN `p_Effective` VARCHAR(50)

,
	IN `p_CustomDate` DATE
)
BEGIN
    
	-- get customer rates
	CALL vwCustomerRate(p_CustomerID,p_Trunks,p_Effective,p_CustomDate);
  
		SELECT DISTINCT 	      
	       Description  as `Destination`,
		   Code as `Prefix`,
		   Rate as `Rate(USD)`,
		   ConnectionFee as `Connection Fee(USD)`,
		   Interval1 as `Increment`,
		   IntervalN as `Minimal Time`,
		   '0:00:00 'as `Start Time`,
		   '23:59:59' as `End Time`,
		   '' as `Week Day`,
		   EffectiveDate  as `Effective from`,
			RoutinePlanName as `Routing through`	
     FROM tmp_customerrateall_ ; 
	 
		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END