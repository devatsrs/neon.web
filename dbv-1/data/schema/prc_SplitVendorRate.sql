CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_SplitVendorRate`(IN `p_processId` VARCHAR(200))
BEGIN

	DECLARE i INTEGER;
	
	-- procedure is use for first if exit '-' then replace into ';' after that that data splite  with ';' into new rows.
	
	-- multiple codes replace into rows
	
	DROP TEMPORARY TABLE IF EXISTS `my_splits`;
	CREATE TEMPORARY TABLE `my_splits` (
		`id` INT(11) NULL DEFAULT NULL,
		`name` VARCHAR(500) NULL DEFAULT NULL
	);
    
  SET i = 1;
  REPEAT
    INSERT INTO my_splits (id, name)
      SELECT TempVendorRateID , FnStringSplit(REPLACE(Code,'-',';'), ';', i)  FROM tblTempVendorRate
      WHERE FnStringSplit(REPLACE(Code,'-',';'), ';', i) IS NOT NULL
			 AND ProcessId = p_processId;
    SET i = i + 1;
    UNTIL ROW_COUNT() = 0
  END REPEAT;
  
  UPDATE my_splits SET name = trim(name);

	-- tmp_split_VendorRate_ table crated in prc_WSProcessVendorRate and use in prc_checkDialstringAndDupliacteCode
	
	INSERT INTO tmp_split_VendorRate_
	SELECT DISTINCT
		   id as `TempVendorRateID`,
		   `CodeDeckId`,
		   name as Code,
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
				ON my_splits.id = tblTempVendorRate.TempVendorRateID
		  WHERE	tblTempVendorRate.ProcessId = p_processId;	

END