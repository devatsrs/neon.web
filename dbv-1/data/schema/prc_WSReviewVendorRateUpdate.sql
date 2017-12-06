CREATE DEFINER=`neon-user`@`%` PROCEDURE `prc_WSReviewVendorRateUpdate`(
	IN `p_AccountId` INT,
	IN `p_TrunkID` INT,
	IN `p_RateIds` TEXT,
	IN `p_ProcessID` VARCHAR(200),
	IN `p_criteria` INT,
	IN `p_Action` VARCHAR(20),
	IN `p_Interval1` INT,
	IN `p_IntervalN` INT,
	IN `p_EndDate` DATETIME



,
	IN `p_Code` VARCHAR(50),
	IN `p_Description` VARCHAR(50)
)
ThisSP:BEGIN

	DECLARE newstringcode INT(11) DEFAULT 0;
	DECLARE v_pointer_ INT;
	DECLARE v_rowCount_ INT;     

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
    CREATE TEMPORARY TABLE tmp_JobLog_ (
        Message longtext
    );

	SET @stm_and_code = '';	
	IF p_Code != ''
	THEN
		SET @stm_and_code = CONCAT(' AND ("',p_Code,'" IS NULL OR "',p_Code,'" = "" OR tvr.Code LIKE "',REPLACE(p_Code, "*", "%"),'")');
	END IF;
	
	SET @stm_and_desc = '';	
	IF p_Description != ''
	THEN
		SET @stm_and_desc = CONCAT(' AND ("',p_Description,'" IS NULL OR "',p_Description,'" = "" OR tvr.Description LIKE "',REPLACE(p_Description, "*", "%"),'")');
	END IF;

    CASE p_Action
		WHEN 'New' THEN
			SET @stm = '';		
			IF p_Interval1 > 0
			THEN
				SET @stm = CONCAT(@stm,'tvr.Interval1 = ',p_Interval1);
			END IF;	
			
			IF p_IntervalN > 0
			THEN
				SET @stm = CONCAT(@stm,IF(@stm != '',',',''),'tvr.IntervalN = ',p_IntervalN);		
			END IF;	

			IF p_criteria = 1
			THEN
				IF @stm != ''
				THEN
					SET @stm1 = CONCAT('UPDATE tblTempVendorRate tvr LEFT JOIN tblVendorRateChangeLog vrcl ON tvr.TempVendorRateID=vrcl.TempVendorRateID SET ',@stm,' WHERE tvr.TempVendorRateID=vrcl.TempVendorRateID AND vrcl.Action = "',p_Action,'" AND tvr.ProcessID = "',p_ProcessID,'" ',@stm_and_code,' ',@stm_and_desc,';');
					select @stm1;			
					PREPARE stmt1 FROM @stm1;
					EXECUTE stmt1;
					DEALLOCATE PREPARE stmt1;
					
					SET @stm2 = CONCAT('UPDATE tblVendorRateChangeLog tvr SET ',@stm,' WHERE ProcessID = "',p_ProcessID,'" AND Action = "',p_Action,'" ',@stm_and_code,' ',@stm_and_desc,';');
								
					PREPARE stm2 FROM @stm2;
					EXECUTE stm2;
					DEALLOCATE PREPARE stm2;
				END IF;
			ELSE
				IF @stm != ''
				THEN					
					SET @stm1 = CONCAT('UPDATE tblTempVendorRate tvr LEFT JOIN tblVendorRateChangeLog vrcl ON tvr.TempVendorRateID=vrcl.TempVendorRateID SET ',@stm,' WHERE tvr.TempVendorRateID IN (',p_RateIds,') AND tvr.TempVendorRateID=vrcl.TempVendorRateID AND vrcl.Action = "',p_Action,'" AND tvr.ProcessID = "',p_ProcessID,'" ',@stm_and_code,' ',@stm_and_desc,';');
								
					PREPARE stmt1 FROM @stm1;
					EXECUTE stmt1;
					DEALLOCATE PREPARE stmt1;
					
					SET @stm2 = CONCAT('UPDATE tblVendorRateChangeLog tvr SET ',@stm,' WHERE TempVendorRateID IN (',p_RateIds,') AND ProcessID = "',p_ProcessID,'" AND Action = "',p_Action,'" ',@stm_and_code,' ',@stm_and_desc,';');
								
					PREPARE stm2 FROM @stm2;
					EXECUTE stm2;
					DEALLOCATE PREPARE stm2;
				END IF;
			END IF;
						
		WHEN 'Deleted' THEN
			IF p_criteria = 1
			THEN
				-- UPDATE tblVendorRate vr LEFT JOIN tblVendorRateChangeLog vrcl ON vr.VendorRateID=vrcl.VendorRateID SET vr.EndDate=p_EndDate WHERE vr.VendorRateID=vrcl.VendorRateID AND vr.AccountId=p_AccountId AND vr.TrunkID=p_TrunkID AND vrcl.ProcessID=p_ProcessID;
				SET @stm1 = CONCAT('UPDATE tblVendorRateChangeLog tvr SET EndDate="',p_EndDate,'" WHERE ProcessID="',p_ProcessID,'" AND `Action`="',p_Action,'" ',@stm_and_code,' ',@stm_and_desc,';');
							
				PREPARE stmt1 FROM @stm1;
				EXECUTE stmt1;
				DEALLOCATE PREPARE stmt1;
			ELSE
				-- UPDATE tblVendorRate vr LEFT JOIN tblVendorRateChangeLog vrcl ON vr.VendorRateID=vrcl.VendorRateID SET vr.EndDate=p_EndDate WHERE vr.VendorRateID IN (p_RateIds) AND vr.VendorRateID=vrcl.VendorRateID AND vr.AccountId=p_AccountId AND vr.TrunkID=p_TrunkID AND vrcl.ProcessID=p_ProcessID;

				SET @stm1 = CONCAT('UPDATE tblVendorRateChangeLog tvr SET EndDate="',p_EndDate,'" WHERE VendorRateID IN (',p_RateIds,')AND Action = "',p_Action,'" AND ProcessID = "',p_ProcessID,'" ',@stm_and_code,' ',@stm_and_desc,';');
							
				PREPARE stmt1 FROM @stm1;
				EXECUTE stmt1;
				DEALLOCATE PREPARE stmt1;
				-- UPDATE tblVendorRateChangeLog SET EndDate=p_EndDate WHERE VendorRateID IN (p_RateIds) AND ProcessID=p_ProcessID AND `Action`=p_Action;
			END IF;
	END CASE;
 
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END