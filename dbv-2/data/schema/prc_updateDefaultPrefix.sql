CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_updateDefaultPrefix`(IN `p_processId` INT, IN `p_tbltempusagedetail_name` VARCHAR(200))
BEGIN

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

	/* find prefix from default codes and accounts doen't have active trunks */
	SET @stm = CONCAT('
	INSERT INTO tmp_TempUsageDetail_
	SELECT
		TempUsageDetailID,
		c.code AS prefix
	FROM NeonCDRDev.' , p_tbltempusagedetail_name , ' ud
	INNER JOIN NeonRMDev.tmp_codes_ c 
		ON ud.ProcessID = ' , p_processId , '
		AND ud.area_prefix = "Other"
		AND cld like  CONCAT(c.Code,"%")
	LEFT JOIN tmp_Accounts_ a
		ON a.AccountID = ud.AccountID
	WHERE a.AccountID IS NULL;
	');

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;


	INSERT INTO tmp_TempUsageDetail2_
	SELECT tbl.TempUsageDetailID,MAX(tbl.prefix)  
	FROM tmp_TempUsageDetail_ tbl
	GROUP BY tbl.TempUsageDetailID;

	SET @stm = CONCAT('UPDATE NeonCDRDev.' , p_tbltempusagedetail_name , ' tbl2
	INNER JOIN tmp_TempUsageDetail2_ tbl
		ON tbl2.TempUsageDetailID = tbl.TempUsageDetailID
	SET area_prefix = prefix
	WHERE tbl2.processId = "' , p_processId , '"
	');

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;     

END