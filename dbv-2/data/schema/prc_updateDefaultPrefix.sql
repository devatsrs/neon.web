CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_updateDefaultPrefix`(
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200)
)
BEGIN
	DECLARE v_pointer_ INT;	
	DECLARE v_partition_limit_ INT;
	
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
	
	SET v_pointer_ = 0;
	SET v_partition_limit_ = 1000;
	SET @stm = CONCAT('
		SET @rowCount = (SELECT   COUNT(*)   FROM NeonCDRDev.' , p_tbltempusagedetail_name , '  ud LEFT JOIN tmp_Accounts_ a ON a.AccountID = ud.AccountID WHERE a.AccountID IS NULL AND ProcessID = "' , p_processId , '"  );
	');
	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
	
	WHILE v_pointer_ <= @rowCount
	DO
		
			
		DROP TEMPORARY TABLE IF EXISTS tmp_TempUsageDetailPart_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempUsageDetailPart_(
			TempUsageDetailID int,
			cld varchar(500),
			INDEX IX_TempUsageDetailID(`TempUsageDetailID`),
			INDEX IX_cld(`cld`)
		);
		SET @stm = CONCAT('
		INSERT INTO tmp_TempUsageDetailPart_
		SELECT 
			TempUsageDetailID,
			cld
		FROM NeonCDRDev.' , p_tbltempusagedetail_name , ' ud
		LEFT JOIN tmp_Accounts_ a
			ON a.AccountID = ud.AccountID
		WHERE a.AccountID IS NULL 
			AND ProcessID = "' , p_processId , '"  
			AND area_prefix = "Other"  
		LIMIT ',v_partition_limit_,' OFFSET ',v_pointer_,';
		');
		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
		
		INSERT INTO tmp_TempUsageDetail_
		SELECT
			TempUsageDetailID,
			c.code AS prefix
		FROM tmp_TempUsageDetailPart_ ud
		INNER JOIN NeonRMDev.tmp_codes_ c 
			ON  cld like  CONCAT(c.Code,"%");
			
		SET v_pointer_ = v_pointer_ + v_partition_limit_;
		
	END WHILE;
	
	


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