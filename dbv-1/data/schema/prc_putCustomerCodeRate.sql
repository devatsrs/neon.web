CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_putCustomerCodeRate`(
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
	/* delete codes which are not exist in temp table*/
	SET @stm = CONCAT('
	DELETE cr FROM `' , p_tbltemp_name , '` cr 
	LEFT JOIN (SELECT AccountID,TrunkID,RateID,MAX(EffectiveDate) as EffectiveDate FROM `' , p_tbltemp_name , '`  WHERE ProcessID = "' , p_ProcessID , '" AND EffectiveDate <= NOW()  GROUP BY  AccountID,TrunkID,RateID )tbl
		ON tbl.AccountID = cr.AccountID  
		AND tbl.TrunkID = cr.TrunkID
		AND tbl.RateID = cr.RateID
		AND tbl.EffectiveDate = cr.EffectiveDate
	WHERE tbl.EffectiveDate IS NULL AND  cr.EffectiveDate <= NOW() AND cr.ProcessID = "' , p_ProcessID , '";');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;

	INSERT INTO tmp_tblTempLog_ (Message)
	SELECT CONCAT(AccountName,' Old Effective Dates Records Deleted ',FOUND_ROWS()) FROM tblAccount WHERE AccountID = p_AccountID;

	/* delete codes which are not exist in temp table*/
	SET @stm = CONCAT('
	DELETE tblCustomerRate FROM tblCustomerRate
	LEFT JOIN `' , p_tbltemp_name , '` temp 
		ON tblCustomerRate.RateID = temp.RateID
		AND CustomerID  = AccountID
		AND tblCustomerRate.TrunkID = temp.TrunkID
		AND tblCustomerRate.EffectiveDate = temp.EffectiveDate
		AND ProcessID = "' , p_ProcessID , '"
	WHERE TempRatesImportID IS NULL AND temp.RateID IS NOT NULL AND CustomerID = "' , p_AccountID , '" AND tblCustomerRate.TrunkID = "' , p_TrunkID , '";
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;

	INSERT INTO tmp_tblTempLog_ (Message)
	SELECT CONCAT(AccountName,' Records Deleted ',FOUND_ROWS()) FROM tblAccount WHERE AccountID = p_AccountID;

	-- 	 update codes which are exist in temp table
	SET @stm = CONCAT('
	UPDATE tblCustomerRate 
	INNER JOIN `' , p_tbltemp_name , '` temp 
		ON tblCustomerRate.RateID = temp.RateID
		AND tblCustomerRate.TrunkID = temp.TrunkID
		AND CustomerID  = AccountID
		AND tblCustomerRate.EffectiveDate = temp.EffectiveDate
	SET tblCustomerRate.PreviousRate = tblCustomerRate.Rate,tblCustomerRate.Rate = temp.Rate,tblCustomerRate.ConnectionFee = temp.ConnectionFee
	WHERE CustomerID = "' , p_AccountID , '" AND tblCustomerRate.TrunkID = "' , p_TrunkID , '" AND ProcessID = "' , p_ProcessID , '";
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;

	INSERT INTO tmp_tblTempLog_ (Message)
	SELECT CONCAT(AccountName,' Records Updated ',FOUND_ROWS()) FROM tblAccount WHERE AccountID = p_AccountID;

	-- insert codes which are not exist in customer table
	SET @stm = CONCAT('
	INSERT IGNORE INTO tblCustomerRate (RateID,CustomerID,TrunkID,LastModifiedDate,LastModifiedBy,Rate,EffectiveDate,Interval1,IntervalN,ConnectionFee)
	SELECT DISTINCT temp.RateID,temp.AccountID,temp.TrunkID,now(),"SYSTEM IMPORTED",temp.Rate,temp.EffectiveDate,temp.Interval1,temp.IntervalN,temp.ConnectionFee FROM `' , p_tbltemp_name , '` temp
	LEFT JOIN tblCustomerRate
		ON tblCustomerRate.RateID = temp.RateID
		AND tblCustomerRate.TrunkID = temp.TrunkID
		AND CustomerID  = AccountID
		AND tblCustomerRate.EffectiveDate = temp.EffectiveDate
		AND ProcessID = "' , p_ProcessID , '"
	WHERE CustomerRateID IS NULL AND AccountID = "' , p_AccountID , '" AND temp.TrunkID = "' , p_TrunkID , '" AND temp.RateID IS NOT NULL;
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;

	INSERT INTO tmp_tblTempLog_ (Message)
	SELECT CONCAT(AccountName,' Records Inserted ',FOUND_ROWS()) FROM tblAccount WHERE AccountID = p_AccountID;

	SELECT * FROM tmp_tblTempLog_;

END