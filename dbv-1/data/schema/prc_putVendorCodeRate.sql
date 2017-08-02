CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_putVendorCodeRate`(
	IN `p_AccountID` INT,
	IN `p_TrunkID` INT,
	IN `p_tbltemp_name` VARCHAR(200),
	IN `p_ProcessID` VARCHAR(200)
)
BEGIN

	DROP TEMPORARY TABLE IF EXISTS tmp_tblTempLog_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblTempLog_(
		`Message` VARCHAR(500) NOT NULL	
	);
	
	/* delete old dates rate */
	SET @stm = CONCAT('
	DELETE cr FROM `' , p_tbltemp_name , '` cr 
	LEFT JOIN (SELECT AccountID,TrunkID,RateID,MAX(EffectiveDate) as EffectiveDate FROM `' , p_tbltemp_name , '`  WHERE ProcessID = "' , p_ProcessID , '" AND EffectiveDate <= NOW() GROUP BY  AccountID,TrunkID,RateID )tbl
		ON tbl.AccountID = cr.AccountID  
		AND tbl.TrunkID = cr.TrunkID
		AND tbl.RateID = cr.RateID
		AND tbl.EffectiveDate = cr.EffectiveDate
	WHERE tbl.EffectiveDate IS NULL AND cr.EffectiveDate <= NOW() AND cr.ProcessID = "' , p_ProcessID , '";');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;

	INSERT INTO tmp_tblTempLog_ (Message)
	SELECT CONCAT(AccountName,' Old Effective Dates Records Deleted ',FOUND_ROWS()) FROM tblAccount WHERE AccountID = p_AccountID;

	/* delete codes which are not exist in temp table*/
	SET @stm = CONCAT('
	DELETE tblVendorRate FROM tblVendorRate
	LEFT JOIN `' , p_tbltemp_name , '` temp 
		ON tblVendorRate.RateId = temp.RateID
		AND tblVendorRate.TrunkID = temp.TrunkID
		AND tblVendorRate. AccountId  = temp.AccountID
		AND tblVendorRate.EffectiveDate = temp.EffectiveDate
		AND ProcessID = "' , p_ProcessID , '"
	WHERE TempRatesImportID IS NULL AND tblVendorRate.AccountId = "' , p_AccountID , '" AND tblVendorRate.TrunkID = "' , p_TrunkID , '";
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;

	INSERT INTO tmp_tblTempLog_ (Message)
	SELECT CONCAT(AccountName,' Records Deleted  ',FOUND_ROWS()) FROM tblAccount WHERE AccountID = p_AccountID;

	/* update codes which are exist in temp table*/
	SET @stm = CONCAT('
	UPDATE tblVendorRate 
	INNER JOIN `' , p_tbltemp_name , '` temp 
		ON tblVendorRate.RateId = temp.RateID
		AND tblVendorRate.TrunkID = temp.TrunkID
		AND tblVendorRate.AccountId  = temp.AccountID
		AND tblVendorRate.EffectiveDate = temp.EffectiveDate
	SET tblVendorRate.Interval1 = temp.Interval1,tblVendorRate.IntervalN = temp.IntervalN,tblVendorRate.Rate = temp.Rate,tblVendorRate.ConnectionFee = temp.ConnectionFee,updated_at=NOW(),updated_by="SYSTEM IMPORTED"
	WHERE tblVendorRate.AccountId = "' , p_AccountID , '" AND tblVendorRate.TrunkID = "' , p_TrunkID , '" AND ProcessID = "' , p_ProcessID , '";
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;
	
	INSERT INTO tmp_tblTempLog_ (Message)
	SELECT CONCAT(AccountName,' Records Updated ',FOUND_ROWS()) FROM tblAccount WHERE AccountID = p_AccountID;

	/* insert codes which are not exist in customer table*/
	SET @stm = CONCAT('
	INSERT INTO tblVendorRate (RateId,AccountId,TrunkID,created_at,created_by,Rate,EffectiveDate,Interval1,IntervalN,ConnectionFee)
	SELECT temp.RateID,temp.AccountID,temp.TrunkID,now(),"SYSTEM IMPORTED",temp.Rate,temp.EffectiveDate,temp.Interval1,temp.IntervalN,temp.ConnectionFee FROM `' , p_tbltemp_name , '` temp
	LEFT JOIN tblVendorRate
		ON tblVendorRate.RateId = temp.RateID
		AND tblVendorRate.TrunkID = temp.TrunkID
		AND tblVendorRate.AccountId  = temp.AccountID
		AND tblVendorRate.EffectiveDate = temp.EffectiveDate
		AND ProcessID = "' , p_ProcessID , '"
	WHERE VendorRateID IS NULL AND temp.AccountID = "' , p_AccountID , '" AND temp.TrunkID = "' , p_TrunkID , '" AND temp.RateID IS NOT NULL;
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;

	INSERT INTO tmp_tblTempLog_ (Message)
	SELECT CONCAT(AccountName,' Records Inserted ',FOUND_ROWS()) FROM tblAccount WHERE AccountID = p_AccountID;

	SELECT * FROM tmp_tblTempLog_;
	
	CALL prc_putVendorPreference(p_AccountID,p_TrunkID,p_tbltemp_name,p_ProcessID);

END