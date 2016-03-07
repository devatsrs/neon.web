CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_update_inbound_call_rate`(IN `p_companyid` INT, IN `p_processid` VARCHAR(100), IN `p_tbltempusagedetail_name` VARCHAR(100))
BEGIN

	

	DROP TEMPORARY TABLE IF EXISTS tmp_TempUsageDetail_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempUsageDetail_(
			TempUsageDetailID int,
			prefix varchar(50)
	);
	DROP TEMPORARY TABLE IF EXISTS tmp_Message_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Message_(
   		TempUsageDetailID int,
			Message Text
	);
	
	/*--- Update area_prefix */
	
	set @stm1 = CONCAT('
	 INSERT INTO tmp_TempUsageDetail_
    SELECT
		  ud.TempUsageDetailID,
        MAX(r.Code) AS prefix
		FROM `' , p_tbltempusagedetail_name , '` ud
		inner join Ratemanagement3.tblAccount a on  a.CompanyId = ', p_companyid ,' and a.InboudRateTableID > 0 and a.AccountID  = ud.AccountID 
		Inner join Ratemanagement3.tblRateTable rt on rt.CompanyId = ', p_companyid ,' and  rt.RateTableId = a.InboudRateTableID
		Inner join Ratemanagement3.tblRate r on r.CompanyId = ', p_companyid ,' and r.CodeDeckId = rt.CodeDeckId 
		Inner join Ratemanagement3.tblRateTableRate rtr on  rtr.RateTableId =  rt.RateTableId and rtr.RateID = r.RateID  
		where ud.ProcessID = "' , p_processid  , '" and ud.is_inbound = 1 and ud.cld like concat( r.Code , "%" )
		group by ud.TempUsageDetailID ;
	');

			
    PREPARE stmt1 FROM @stm1;
    EXECUTE stmt1;
    DEALLOCATE PREPARE stmt1;
  
     	 
    
    set @stm2 = CONCAT('UPDATE ' , p_tbltempusagedetail_name , ' tbl2
    INNER JOIN tmp_TempUsageDetail_ tbl
        ON tbl2.TempUsageDetailID = tbl.TempUsageDetailID
    SET tbl2.area_prefix = tbl.prefix 
    WHERE tbl2.processId = "' , p_processid , '" and tbl2.is_inbound = 1 ;
	 ');
   	
     
    PREPARE stmt2 FROM @stm2;
    EXECUTE stmt2;
    DEALLOCATE PREPARE stmt2;
    
/* Update Inbound Rate  */
	
	set @stm3 = CONCAT('update  ' , p_tbltempusagedetail_name  , ' ud
		inner join Ratemanagement3.tblAccount a on  a.CompanyId = ', p_companyid ,' and a.InboudRateTableID > 0 and a.AccountID  = ud.AccountID 
		Inner join Ratemanagement3.tblRateTable rt on rt.CompanyId = ', p_companyid ,' and  rt.RateTableId = a.InboudRateTableID
		Inner join Ratemanagement3.tblRate r on r.CompanyId = ', p_companyid ,' and r.CodeDeckId = rt.CodeDeckId and r.Code = ud.area_prefix
		Inner join Ratemanagement3.tblRateTableRate rtr on  rtr.RateTableId =  rt.RateTableId and rtr.RateID = r.RateID  
	SET ud.cost = 
	CASE WHEN  ud.billed_duration >= rtr.Interval1
	THEN
		(rtr.Rate/60.0)*rtr.Interval1+CEILING((ud.billed_duration-rtr.Interval1)/rtr.IntervalN)*(rtr.Rate/60.0)*rtr.IntervalN+ifnull(rtr.ConnectionFee,0)
	ElSE 
		rtr.Rate+ifnull(rtr.ConnectionFee,0)
	END 
	where ud.ProcessID = "' , p_processid  , '" and ud.is_inbound = 1  ;');

	PREPARE stmt3 FROM @stm3;
   EXECUTE stmt3;
   DEALLOCATE PREPARE stmt3;
   
   /* Return Error message   */
  	/*set @stm4 = CONCAT('insert into tmp_Message_ 
	  						select  TempUsageDetailID, concat( "Account doesnt match for " , GatewayAccountID )
							  from  ' , p_tbltempusagedetail_name  , ' where ProcessID = "' , p_processid  , '" and is_inbound = 1 and AccountID is null ');

	PREPARE stmt4 FROM @stm4;
   EXECUTE stmt4;
   DEALLOCATE PREPARE stmt4;
	*/

  	set @stm5 = CONCAT('insert into tmp_Message_ 
								select   distinct ud.TempUsageDetailID , concat( "Prefix not found for " , ud.GatewayAccountID )
							  from  ' , p_tbltempusagedetail_name  , ' ud
							  inner join Ratemanagement3.tblAccount a on  a.CompanyId = ', p_companyid ,' and a.AccountID  = ud.AccountID and a.InboudRateTableID > 0 
							  where ud.ProcessID = "' , p_processid  , '" and ud.is_inbound = 1 and ud.area_prefix = "Other"  ');

	PREPARE stmt5 FROM @stm5;
   EXECUTE stmt5;
   DEALLOCATE PREPARE stmt5;

	select distinct Message from tmp_Message_;
		 
END