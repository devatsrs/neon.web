CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_update_inbound_call_rate`(IN `p_companyid` INT, IN `p_processid` VARCHAR(100), IN `p_tbltempusagedetail_name` VARCHAR(100))
BEGIN

 	DECLARE v_Account int;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_TempUsageDetail_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempUsageDetail_(
			TempUsageDetailID int,
			prefix varchar(50),
			INDEX IX_TempUsageDetailID(`TempUsageDetailID`)
	);
	DROP TEMPORARY TABLE IF EXISTS tmp_TempUsageDetail2_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempUsageDetail2_(
			TempUsageDetailID int,
			prefix varchar(50),
			INDEX IX_TempUsageDetailID2(`TempUsageDetailID`)
	);
	DROP TEMPORARY TABLE IF EXISTS tmp_Message_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Message_(
			Message Text
	);

	/* Check if Inboud Rate Table Selected Against any account  */
    Select  count(*) into v_Account from LocalRatemanagement.tblAccount  where  CompanyId = p_companyid  and InboudRateTableID > 0 ;
	
 
	/*--- Update area_prefix */
	-- IF ( v_Account > 0 )
	-- THEN
	
		set @stm1 = CONCAT('
		 INSERT INTO tmp_TempUsageDetail_
	    SELECT
			  ud.TempUsageDetailID,
	        r.Code AS prefix
			FROM `' , p_tbltempusagedetail_name , '` ud
			inner join LocalRatemanagement.tblAccount a on  a.CompanyId = ', p_companyid ,' and a.InboudRateTableID > 0 and a.AccountID  = ud.AccountID 
			Inner join LocalRatemanagement.tblRateTable rt on rt.CompanyId = ', p_companyid ,' and  rt.RateTableId = a.InboudRateTableID
			Inner join LocalRatemanagement.tblRate r on r.CompanyId = ', p_companyid ,' and r.CodeDeckId = rt.CodeDeckId 
			Inner join LocalRatemanagement.tblRateTableRate rtr on  rtr.RateTableId =  rt.RateTableId and rtr.RateID = r.RateID  
			where ud.ProcessID = "' , p_processid  , '" and ud.is_inbound = 1 and ud.cld like concat( r.Code , "%" );
		');
	
				
	    PREPARE stmt1 FROM @stm1;
	    EXECUTE stmt1;
	    DEALLOCATE PREPARE stmt1;
	    
	    set @stm7 = CONCAT('INSERT INTO tmp_TempUsageDetail2_
		SELECT tbl.TempUsageDetailID,MAX(tbl.prefix)  
		FROM tmp_TempUsageDetail_ tbl
	   GROUP BY tbl.TempUsageDetailID;');
    
	    PREPARE stmt7 FROM @stm7;
	    EXECUTE stmt7;
	    DEALLOCATE PREPARE stmt7;
	  
	     	 
	    
	    set @stm2 = CONCAT('UPDATE ' , p_tbltempusagedetail_name , ' tbl2
	    INNER JOIN tmp_TempUsageDetail2_ tbl
	        ON tbl2.TempUsageDetailID = tbl.TempUsageDetailID
	    SET tbl2.area_prefix = tbl.prefix 
	    WHERE tbl2.processId = "' , p_processid , '" and tbl2.is_inbound = 1 ;
		 ');
	   	
	     
	    PREPARE stmt2 FROM @stm2;
	    EXECUTE stmt2;
	    DEALLOCATE PREPARE stmt2;
	    
		/* Update Inbound Rate  */
		
		set @stm3 = CONCAT('update  ' , p_tbltempusagedetail_name  , ' ud
			inner join LocalRatemanagement.tblAccount a on  a.CompanyId = ', p_companyid ,' and a.InboudRateTableID > 0 and a.AccountID  = ud.AccountID 
			Inner join LocalRatemanagement.tblRateTable rt on rt.CompanyId = ', p_companyid ,' and  rt.RateTableId = a.InboudRateTableID
			Inner join LocalRatemanagement.tblRate r on r.CompanyId = ', p_companyid ,' and r.CodeDeckId = rt.CodeDeckId and r.Code = ud.area_prefix
			Inner join LocalRatemanagement.tblRateTableRate rtr on  rtr.RateTableId =  rt.RateTableId and rtr.RateID = r.RateID  
		SET ud.cost =  CASE WHEN  ud.billed_duration >= rtr.Interval1
		THEN
			(rtr.Rate/60.0)*rtr.Interval1+CEILING((ud.billed_duration-rtr.Interval1)/rtr.IntervalN)*(rtr.Rate/60.0)*rtr.IntervalN+ifnull(rtr.ConnectionFee,0)
		ElSE 
			CASE WHEN  billed_duration > 0
		    THEN	   
            rtr.Rate+ifnull(rtr.ConnectionFee,0)
          ELSE
			    0
		    END		
		END 
		where ud.ProcessID = "' , p_processid  , '" and ud.is_inbound = 1  ;');
	
		PREPARE stmt3 FROM @stm3;
	   EXECUTE stmt3;
	   DEALLOCATE PREPARE stmt3;
	   	   
      /* set cost = 0 where prefix not matched in p_tbltempusagedetail_name table  */
	   set @stm4 = CONCAT('UPDATE ' , p_tbltempusagedetail_name , ' tbl2
	   LEFT JOIN tmp_TempUsageDetail2_ tbl
	       ON tbl2.TempUsageDetailID = tbl.TempUsageDetailID
	   SET tbl2.cost = 0
	   WHERE tbl2.processId = "' , p_processid , '" and tbl2.is_inbound = 1 and tbl.prefix is null and tbl2.AccountID is not null ;');
	     
	   PREPARE stmt4 FROM @stm4;
	   EXECUTE stmt4;
	   DEALLOCATE PREPARE stmt4;
	   
	   /* Return Error message   */
	  	/*set @stm4 = CONCAT('insert into tmp_Message_ 
		  						select  concat( "Account doesnt match for " , GatewayAccountID )
								  from  ' , p_tbltempusagedetail_name  , ' where ProcessID = "' , p_processid  , '" and is_inbound = 1 and AccountID is null ');
	
		PREPARE stmt4 FROM @stm4;
	   EXECUTE stmt4;
	   DEALLOCATE PREPARE stmt4;
		*/
	
	  	set @stm5 = CONCAT('insert into tmp_Message_ 
									select   distinct concat( "Account:  " , a.AccountName ,  " - Unable to Rerate number ",ud.cld," - No Matching prefix found")
								  from  ' , p_tbltempusagedetail_name  , ' ud
								  inner join LocalRatemanagement.tblAccount a on  a.CompanyId = ', p_companyid ,' and a.AccountID  = ud.AccountID and a.InboudRateTableID > 0 
								  where ud.ProcessID = "' , p_processid  , '" and ud.is_inbound = 1 and ud.area_prefix = "Other"  ');
	
		PREPARE stmt5 FROM @stm5;
	   EXECUTE stmt5;
	   DEALLOCATE PREPARE stmt5;

	
	-- END IF;
	
	select distinct Message from tmp_Message_;
	
		 
END