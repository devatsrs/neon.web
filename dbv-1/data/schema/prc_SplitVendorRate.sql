CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_SplitVendorRate`(IN `p_processId` VARCHAR(200))
BEGIN

	DECLARE i INTEGER;
	DECLARE v_rowCount_ INT;
	DECLARE v_pointer_ INT;	
	DECLARE v_TempVendorRateID_ INT;
	DECLARE v_Code_ VARCHAR(500);	
	
	-- procedure is use for first data splite  with ';' into new rows.
	
	-- multiple codes replace into rows
	
	DROP TEMPORARY TABLE IF EXISTS `my_splits`;
	CREATE TEMPORARY TABLE `my_splits` (
		`TempVendorRateID` INT(11) NULL DEFAULT NULL,
		`Code` VARCHAR(500) NULL DEFAULT NULL
	);
    
  SET i = 1;
  REPEAT
    INSERT INTO my_splits (TempVendorRateID, Code)
      SELECT TempVendorRateID , FnStringSplit(Code, ';', i)  FROM tblTempVendorRate
      WHERE FnStringSplit(Code, ';', i) IS NOT NULL
			 AND ProcessId = p_processId;
    SET i = i + 1;
    UNTIL ROW_COUNT() = 0
  END REPEAT;
  
  UPDATE my_splits SET Code = trim(Code);
  
  -- after splite with ';' now we create temp table of where data with '-'
  
  DROP TEMPORARY TABLE IF EXISTS tmp_newvendor_splite_;
	CREATE TEMPORARY TABLE tmp_newvendor_splite_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		TempVendorRateID INT(11) NULL DEFAULT NULL,
		Code VARCHAR(500) NULL DEFAULT NULL
	);
	
	INSERT INTO tmp_newvendor_splite_(TempVendorRateID,Code)
	SELECT 
		TempVendorRateID,
		Code
	FROM my_splits
	WHERE Code like '%-%'
		AND TempVendorRateID IS NOT NULL;

  -- date split with '-' and data insert into my_splits with range
  
	SET v_pointer_ = 1;
	SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_newvendor_splite_);
	
	WHILE v_pointer_ <= v_rowCount_
	DO
		SET v_TempVendorRateID_ = (SELECT TempVendorRateID FROM tmp_newvendor_splite_ t WHERE t.RowID = v_pointer_); 
		SET v_Code_ = (SELECT Code FROM tmp_newvendor_splite_ t WHERE t.RowID = v_pointer_);
		
		Call prc_SplitAndInsertVendorRate(v_TempVendorRateID_,v_Code_);
		
	SET v_pointer_ = v_pointer_ + 1;
	END WHILE;
	
	-- delete '-' data after data split
	
	DELETE FROM my_splits
		WHERE Code like '%-%'
			AND TempVendorRateID IS NOT NULL;

	

	-- tmp_split_VendorRate_ table crated in prc_WSProcessVendorRate and use in prc_checkDialstringAndDupliacteCode
	
	INSERT INTO tmp_split_VendorRate_
	SELECT DISTINCT
		   my_splits.TempVendorRateID as `TempVendorRateID`,
		   `CodeDeckId`,
		   my_splits.Code as Code,
		   `Description`,
			`Rate`,
			`EffectiveDate`,
			`Change`,
			`ProcessId`,
			`Preference`,
			`ConnectionFee`,
			`Interval1`,
			`IntervalN`,
			`Forbidden`
		 FROM my_splits
		   INNER JOIN tblTempVendorRate 
				ON my_splits.TempVendorRateID = tblTempVendorRate.TempVendorRateID
		  WHERE	tblTempVendorRate.ProcessId = p_processId;	

END