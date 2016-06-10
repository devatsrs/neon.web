CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_updateVendorPrefix`(IN `p_AccountID` INT, IN `p_TrunkID` INT, IN `p_processId` INT, IN `p_tbltempusagedetail_name` VARCHAR(200))
BEGIN

	DROP TEMPORARY TABLE IF EXISTS tmp_TempUsageDetail_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempUsageDetail_(
		TempVendorCDRID int,
		prefix varchar(50),
		INDEX IX_TempVendorCDRID(`TempVendorCDRID`)
	);
	DROP TEMPORARY TABLE IF EXISTS tmp_TempUsageDetail2_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempUsageDetail2_(
		TempVendorCDRID int,
		prefix varchar(50),
		INDEX IX_TempVendorCDRID2(`TempVendorCDRID`)
	);

	/* find prefix without use in billing */
	SET @stm = CONCAT('
	INSERT INTO tmp_TempUsageDetail_
	SELECT
		TempVendorCDRID,
		c.code AS prefix
	FROM LocalRMCdr.' , p_tbltempusagedetail_name , ' ud
	INNER JOIN LocalRatemanagement.tmp_vcodes_ c 
	ON ud.ProcessID = ' , p_processId , '
		AND ud.AccountID = ' , p_AccountID , '
		AND ud.TrunkID = ' , p_TrunkID , '
		AND ud.UseInBilling = 0
		AND ud.area_prefix = "Other"
		AND cld like  CONCAT(c.Code,"%");
	');

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	/* find prefix with use in billing */
	SET @stm = CONCAT('
	INSERT INTO tmp_TempUsageDetail_
	SELECT
		TempVendorCDRID,
		c.code AS prefix
	FROM LocalRMCdr.' , p_tbltempusagedetail_name , ' ud
	INNER JOIN LocalRatemanagement.tmp_vcodes_ c 
	ON ud.ProcessID = ' , p_processId , '
		AND ud.AccountID = ' , p_AccountID , '
		AND ud.TrunkID = ' , p_TrunkID , '
		AND ud.UseInBilling = 1 
		AND ud.area_prefix = "Other"
		AND cld like  CONCAT(ud.TrunkPrefix,c.Code,"%");
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;

	SET @stm = CONCAT('INSERT INTO tmp_TempUsageDetail2_
	SELECT tbl.TempVendorCDRID,MAX(tbl.prefix)  
	FROM tmp_TempUsageDetail_ tbl
	GROUP BY tbl.TempVendorCDRID;');

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET @stm = CONCAT('UPDATE LocalRMCdr.' , p_tbltempusagedetail_name , ' tbl2
	INNER JOIN tmp_TempUsageDetail2_ tbl
		ON tbl2.TempVendorCDRID = tbl.TempVendorCDRID
	SET area_prefix = prefix
	WHERE tbl2.processId = "' , p_processId , '"
	');

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;     

END