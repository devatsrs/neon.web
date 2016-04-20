-- --------------------------------------------------------
-- Host:                         188.227.186.98
-- Server version:               5.7.11 - MySQL Community Server (GPL)
-- Server OS:                    Linux
-- HeidiSQL Version:             9.1.0.4867
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping database structure for RMCDR4
CREATE DATABASE IF NOT EXISTS `RMCDR4` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `RMCDR4`;


-- Dumping structure for procedure RMCDR4.prc_checkUniqueID
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_checkUniqueID`(IN `p_CompanyGatewayID` int, IN `p_ID` int)
BEGIN
 	
 	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	SELECT ID  FROM tblUsageDetails inner join  tblUsageHeader on tblUsageHeader.UsageHeaderID = tblUsageDetails.UsageHeaderID where tblUsageHeader.CompanyGatewayID = p_CompanyGatewayID and tblUsageDetails.ID =  p_ID;
	
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
END//
DELIMITER ;


-- Dumping structure for procedure RMCDR4.prc_insertCDR
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_insertCDR`(IN `p_processId` varchar(200), IN `p_tbltempusagedetail_name` VARCHAR(200))
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	SET SESSION innodb_lock_wait_timeout = 180;
 
    
    
    
    set @stm2 = CONCAT('
	insert into   tblUsageHeader (CompanyID,CompanyGatewayID,GatewayAccountID,AccountID,StartDate,created_at)
	select distinct d.CompanyID,d.CompanyGatewayID,d.GatewayAccountID,d.AccountID,DATE_FORMAT(connect_time,"%Y-%m-%d"),NOW()  
	from `' , p_tbltempusagedetail_name , '` d
	left join tblUsageHeader h 
		on h.CompanyID = d.CompanyID
		AND h.CompanyGatewayID = d.CompanyGatewayID
		AND h.GatewayAccountID = d.GatewayAccountID
		AND h.StartDate = DATE_FORMAT(connect_time,"%Y-%m-%d")
		where h.GatewayAccountID is null and processid = "' , p_processId , '";
	');

    PREPARE stmt2 FROM @stm2;
    EXECUTE stmt2;
    DEALLOCATE PREPARE stmt2;
    

	set @stm3 = CONCAT('
	insert into tblUsageDetailFailedCall (UsageHeaderID,connect_time,disconnect_time,billed_duration,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID)
	select UsageHeaderID,connect_time,disconnect_time,billed_duration,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID
		 from  `' , p_tbltempusagedetail_name , '` d left join tblUsageHeader h	 on h.CompanyID = d.CompanyID
		AND h.CompanyGatewayID = d.CompanyGatewayID
		AND h.GatewayAccountID = d.GatewayAccountID
		AND h.StartDate = DATE_FORMAT(connect_time,"%Y-%m-%d")
	where   processid = "' , p_processId , '"
	and billed_duration = 0 and cost = 0;
	');

    PREPARE stmt3 FROM @stm3;
    EXECUTE stmt3;
    DEALLOCATE PREPARE stmt3;
    
    
	set @stm4 = CONCAT('    
	DELETE FROM `' , p_tbltempusagedetail_name , '` WHERE processid = "' , p_processId , '"  and billed_duration = 0 and cost = 0;
	');

    PREPARE stmt4 FROM @stm4;
    EXECUTE stmt4;
    DEALLOCATE PREPARE stmt4;

	set @stm5 = CONCAT(' 
	insert into tblUsageDetails (UsageHeaderID,connect_time,disconnect_time,billed_duration,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID,is_inbound)
	select UsageHeaderID,connect_time,disconnect_time,billed_duration,area_prefix,pincode,extension,cli,cld,cost,remote_ip,duration,trunk,ProcessID,ID,is_inbound
		 from  `' , p_tbltempusagedetail_name , '` d left join tblUsageHeader h	 on h.CompanyID = d.CompanyID
		AND h.CompanyGatewayID = d.CompanyGatewayID
		AND h.GatewayAccountID = d.GatewayAccountID
		AND h.StartDate = DATE_FORMAT(connect_time,"%Y-%m-%d")
	where   processid = "' , p_processId , '" ;
	');
    PREPARE stmt5 FROM @stm5;
    EXECUTE stmt5;
    DEALLOCATE PREPARE stmt5;

 	set @stm6 = CONCAT(' 
	DELETE FROM `' , p_tbltempusagedetail_name , '` WHERE processid = "' , p_processId , '" ;
	');
    PREPARE stmt6 FROM @stm6;
    EXECUTE stmt6;
    DEALLOCATE PREPARE stmt6;
   

	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


-- Dumping structure for procedure RMCDR4.prc_insertVendorCDR
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_insertVendorCDR`(IN `p_processId` VARCHAR(200), IN `p_tbltempusagedetail_name` VARCHAR(50))
BEGIN
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

 
	set @stm2 = CONCAT('
	insert into   tblVendorCDRHeader (CompanyID,CompanyGatewayID,GatewayAccountID,AccountID,StartDate,created_at)
	select distinct d.CompanyID,d.CompanyGatewayID,d.GatewayAccountID,d.AccountID,DATE_FORMAT(connect_time,"%Y-%m-%d"),NOW()  
	from `' , p_tbltempusagedetail_name , '` d
	left join tblVendorCDRHeader h 
		on h.CompanyID = d.CompanyID
		AND h.CompanyGatewayID = d.CompanyGatewayID
		AND h.GatewayAccountID = d.GatewayAccountID
		AND h.StartDate = DATE_FORMAT(connect_time,"%Y-%m-%d")  

		where h.GatewayAccountID is null and processid = "' , p_processId , '";
		');
		
	PREPARE stmt2 FROM @stm2;
   EXECUTE stmt2;
	DEALLOCATE PREPARE stmt2;
	
	
	set @stm3 = CONCAT('
	DELETE FROM `' , p_tbltempusagedetail_name , '` WHERE processid = "' , p_processId , '"  and billed_duration = 0 and buying_cost = 0;
	');
	
	PREPARE stmt3 FROM @stm3;
   EXECUTE stmt3;
	DEALLOCATE PREPARE stmt3;

	set @stm4 = CONCAT('
	insert into tblVendorCDR (VendorCDRHeaderID,billed_duration, ID, selling_cost, buying_cost, connect_time, disconnect_time,cli, cld,trunk,area_prefix,  remote_ip, ProcessID)
	select VendorCDRHeaderID,billed_duration, ID, selling_cost, buying_cost, connect_time, disconnect_time,cli, cld,trunk,area_prefix,  remote_ip, ProcessID
		 from  `' , p_tbltempusagedetail_name , '` d left join tblVendorCDRHeader h	 on h.CompanyID = d.CompanyID
		AND h.CompanyGatewayID = d.CompanyGatewayID
		AND h.GatewayAccountID = d.GatewayAccountID
		AND h.StartDate = DATE_FORMAT(connect_time,"%Y-%m-%d") 
	where   processid = "' , p_processId , '" ;
	');
	
	PREPARE stmt4 FROM @stm4;
   EXECUTE stmt4;
	DEALLOCATE PREPARE stmt4;

   set @stm5 = CONCAT(' 
	DELETE FROM `' , p_tbltempusagedetail_name , '` WHERE processid = "' , p_processId , '" ;
	');
	
	PREPARE stmt5 FROM @stm5;
   EXECUTE stmt5;
	DEALLOCATE PREPARE stmt5;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END//
DELIMITER ;


-- Dumping structure for procedure RMCDR4.prc_update_inbound_call_rate
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_update_inbound_call_rate`(IN `p_companyid` INT, IN `p_processid` VARCHAR(100), IN `p_tbltempusagedetail_name` VARCHAR(100))
BEGIN

 	DECLARE v_Account int;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_TempUsageDetail_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempUsageDetail_(
			TempUsageDetailID int,
			prefix varchar(50)
	);
	DROP TEMPORARY TABLE IF EXISTS tmp_Message_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_Message_(
			Message Text
	);

	/* Check if Inboud Rate Table Selected Against any account  */
    Select  count(*) into v_Account from RateManagement4.tblAccount  where  CompanyId = p_companyid  and InboudRateTableID > 0 ;
	
 
	/*--- Update area_prefix */
	IF ( v_Account > 0 )
	THEN
	
		set @stm1 = CONCAT('
		 INSERT INTO tmp_TempUsageDetail_
	    SELECT
			  ud.TempUsageDetailID,
	        MAX(r.Code) AS prefix
			FROM `' , p_tbltempusagedetail_name , '` ud
			inner join RateManagement4.tblAccount a on  a.CompanyId = ', p_companyid ,' and a.InboudRateTableID > 0 and a.AccountID  = ud.AccountID 
			Inner join RateManagement4.tblRateTable rt on rt.CompanyId = ', p_companyid ,' and  rt.RateTableId = a.InboudRateTableID
			Inner join RateManagement4.tblRate r on r.CompanyId = ', p_companyid ,' and r.CodeDeckId = rt.CodeDeckId 
			Inner join RateManagement4.tblRateTableRate rtr on  rtr.RateTableId =  rt.RateTableId and rtr.RateID = r.RateID  
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
			inner join RateManagement4.tblAccount a on  a.CompanyId = ', p_companyid ,' and a.InboudRateTableID > 0 and a.AccountID  = ud.AccountID 
			Inner join RateManagement4.tblRateTable rt on rt.CompanyId = ', p_companyid ,' and  rt.RateTableId = a.InboudRateTableID
			Inner join RateManagement4.tblRate r on r.CompanyId = ', p_companyid ,' and r.CodeDeckId = rt.CodeDeckId and r.Code = ud.area_prefix
			Inner join RateManagement4.tblRateTableRate rtr on  rtr.RateTableId =  rt.RateTableId and rtr.RateID = r.RateID  
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
	   
      /* set cost = 0 where prefix not matched in p_tbltempusagedetail_name table  */
	   set @stm4 = CONCAT('UPDATE ' , p_tbltempusagedetail_name , ' tbl2
	   LEFT JOIN tmp_TempUsageDetail_ tbl
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
									select   distinct concat( "Prefix not found for " , ud.GatewayAccountID )
								  from  ' , p_tbltempusagedetail_name  , ' ud
								  inner join RateManagement4.tblAccount a on  a.CompanyId = ', p_companyid ,' and a.AccountID  = ud.AccountID and a.InboudRateTableID > 0 
								  where ud.ProcessID = "' , p_processid  , '" and ud.is_inbound = 1 and ud.area_prefix = "Other"  ');
	
		PREPARE stmt5 FROM @stm5;
	   EXECUTE stmt5;
	   DEALLOCATE PREPARE stmt5;

	
	END IF;
	
	select distinct Message from tmp_Message_;
	
		 
END//
DELIMITER ;


-- Dumping structure for procedure RMCDR4.prc_VendorCDRReRateByAccount
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_VendorCDRReRateByAccount`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_TrunkID` INT, IN `p_processId` VARCHAR(200), IN `p_tbltempusagedetail_name` VARCHAR(50))
BEGIN
    
    DECLARE v_CodeDeckId_ INT;
    
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	select CodeDeckId into v_CodeDeckId_  from tblVendorTrunk where AccountID = p_AccountID and TrunkID = p_trunkID;
    DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
	   CREATE TEMPORARY TABLE tmp_VendorRate_ (
	        VendorRateID INT,
	        Code VARCHAR(50),
	        Description VARCHAR(200),
			  ConnectionFee DECIMAL(18, 6),
	        Interval1 INT,
	        IntervalN INT,
	        Rate DECIMAL(18, 6),
	        EffectiveDate DATE,
	        updated_at DATETIME,
	        updated_by VARCHAR(50)
	    );
	    
    INSERT INTO tmp_VendorRate_
	 SELECT
				VendorRateID,
				Code,
				tblRate.Description,
				tblVendorRate.ConnectionFee,
				CASE WHEN tblVendorRate.Interval1 IS NOT NULL
				THEN tblVendorRate.Interval1
				ELSE tblRate.Interval1
				END AS Interval1,
				CASE WHEN tblVendorRate.IntervalN IS NOT NULL
				THEN tblVendorRate.IntervalN
				ELSE tblRate.IntervalN
				END AS IntervalN ,
				Rate,
				EffectiveDate,
				tblVendorRate.updated_at,
				tblVendorRate.updated_by
			FROM tblVendorRate
			JOIN tblRate
				ON tblVendorRate.RateId = tblRate.RateId
			WHERE (tblRate.CompanyID = p_companyid)
			AND TrunkID = p_trunkID
			AND tblVendorRate.AccountID = p_AccountID
			AND CodeDeckId = v_CodeDeckId_
			AND EffectiveDate <= NOW();
			
	   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRate2_ as (select * from tmp_VendorRate_);	        
      DELETE n1 FROM tmp_VendorRate_ n1, tmp_VendorRate2_ n2 WHERE n1.EffectiveDate < n2.EffectiveDate 
	   AND  n1.Code = n2.Code;

    set @stm1 = CONCAT('UPDATE   RMCDR4.`' , p_tbltempusagedetail_name , '` ud 
    LEFT JOIN tmp_VendorRate_ vr ON vr.Code = ud.area_prefix
    SET selling_cost = CASE WHEN  billed_duration >= Interval1
    THEN
    (Rate/60.0)*Interval1+CEILING((billed_duration-Interval1)/IntervalN)*(Rate/60.0)*IntervalN+ifnull(ConnectionFee,0)
        ElSE 
        Rate+ifnull(ConnectionFee,0)
    END
    WHERE processid = "',p_processId,'"
    AND accountid = "',p_AccountID ,'" 
    AND trunk = (select Trunk from tblTrunk where TrunkID = "',p_TrunkID,'" limit 1)
    AND vr.rate is not null') ;
    
    PREPARE stmt1 FROM @stm1;
    EXECUTE stmt1;
    DEALLOCATE PREPARE stmt1;
    

    set @stm2 = CONCAT('UPDATE RMCDR4.`' , p_tbltempusagedetail_name , '` ud 
    LEFT JOIN tmp_VendorRate_ vr ON vr.Code = ud.area_prefix
    SET selling_cost = 0.0
    WHERE processid = "',p_processId,'"
    AND accountid = "',p_AccountID ,'"  
    AND trunk = (select  Trunk from tblTrunk where TrunkID = "',p_TrunkID,'" LIMIT 1)
    AND vr.rate is null ');
    
    PREPARE stmt2 FROM @stm2;
    EXECUTE stmt2;
    DEALLOCATE PREPARE stmt2;

    set @stm3 = CONCAT('UPDATE RMCDR4.`' , p_tbltempusagedetail_name , '` SET selling_cost = 0.0
    WHERE processid = "',p_processId,'" AND accountid is null');
    
    PREPARE stmt3 FROM @stm3;
    EXECUTE stmt3;
    DEALLOCATE PREPARE stmt3;
    
    
    set @stm4 = CONCAT('SELECT DISTINCT ud.cld as area_prefix,ud.trunk,ud.GatewayAccountID,a.AccountName
    FROM RMCDR4.`' , p_tbltempusagedetail_name , '` ud 
    LEFT JOIN tmp_VendorRate_ vr ON vr.Code = ud.area_prefix
    LEFT JOIN tblAccount a ON a.AccountID = ud.AccountID
    WHERE processid ="',p_processId,'" 
    AND ud.accountid = "',p_AccountID ,'"  
    AND trunk = (select  Trunk from tblTrunk where TrunkID = "',p_TrunkID,'" LIMIT 1)
    AND vr.rate is null');
    
    PREPARE stmt4 FROM @stm4;
    EXECUTE stmt4;
    DEALLOCATE PREPARE stmt4;
    
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    
END//
DELIMITER ;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
