USE `Ratemanagement3`;
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'CUSTOMER_MOVEMENT_REPORT_DISPLAY', '1');

-- Dumping structure for procedure Ratemanagement3.prc_CustomerRatesFileImport
DROP PROCEDURE IF EXISTS `prc_CustomerRatesFileImport`;
DELIMITER |
CREATE PROCEDURE `prc_CustomerRatesFileImport`(
	IN `p_ProcessID` VARCHAR(200),
	IN `p_tbltemp_name` VARCHAR(200)
)
BEGIN

	DECLARE v_pointer_ INT;
	DECLARE v_rowCount_ INT;
	DECLARE v_TrunkID_ INT;
	DECLARE v_AccountID_ INT;
	DECLARE v_codedeckid_ INT;
	DECLARE v_companyid_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_AccountTrunk_;
	CREATE TEMPORARY TABLE tmp_AccountTrunk_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AccountID INT,
		TrunkID INT
	);
	SET @stm = CONCAT('
	INSERT INTO tmp_AccountTrunk_(AccountID,TrunkID)
	SELECT DISTINCT AccountID,TrunkID FROM `' , p_tbltemp_name , '` ud WHERE ProcessID="' , p_ProcessID , '" AND AccountID IS NOT NULL AND TrunkID IS NOT NULL;
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;

	SET v_pointer_ = 1;
	SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_AccountTrunk_);

	WHILE v_pointer_ <= v_rowCount_
	DO

		SET v_TrunkID_ = (SELECT TrunkID FROM tmp_AccountTrunk_ t WHERE t.RowID = v_pointer_); 
		SET v_AccountID_ = (SELECT AccountID FROM tmp_AccountTrunk_ t WHERE t.RowID = v_pointer_);
		SET v_codedeckid_ = (SELECT CodeDeckId FROM tblCustomerTrunk WHERE tblCustomerTrunk.TrunkID = v_TrunkID_ AND tblCustomerTrunk.AccountID = v_AccountID_ AND tblCustomerTrunk.Status = 1);

		IF v_codedeckid_ IS NOT NULL AND (SELECT COUNT(*) FROM tblCodeDeck WHERE CodeDeckId = v_codedeckid_)>0
		THEN

			SET v_companyid_ = (SELECT CompanyId FROM tblCodeDeck WHERE CodeDeckId = v_codedeckid_);			

			-- code insert and update rate id in temp table
			CALL prc_updateRateID(v_AccountID_,v_codedeckid_,p_tbltemp_name,p_ProcessID);

			-- CALL prc_GetCustomerRate(v_companyid_,v_AccountID_,v_TrunkID_,null,null,null,'All',1,0,0,0,'','',-1);
			
			-- customerrate insert,update and delete
			CALL prc_putCustomerCodeRate(v_AccountID_,v_TrunkID_,p_tbltemp_name,p_ProcessID);

		END IF;

		SET v_pointer_ = v_pointer_ + 1;

	END WHILE;

	SET @stm = CONCAT('
	DELETE FROM `' , p_tbltemp_name , '` WHERE ProcessID="' , p_ProcessID , '" ;
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm; 

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END|
DELIMITER ;

-- Dumping structure for procedure Ratemanagement3.prc_putCustomerCodeRate
DROP PROCEDURE IF EXISTS `prc_putCustomerCodeRate`;
DELIMITER |
CREATE PROCEDURE `prc_putCustomerCodeRate`(
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
	LEFT JOIN (SELECT AccountID,TrunkID,RateID,MAX(EffectiveDate) as EffectiveDate FROM `' , p_tbltemp_name , '`  WHERE ProcessID = "' , p_ProcessID , '"  GROUP BY  AccountID,TrunkID,RateID )tbl
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
	DELETE tblCustomerRate FROM tblCustomerRate
	LEFT JOIN `' , p_tbltemp_name , '` temp 
		ON tblCustomerRate.RateID = temp.RateID
		AND tblCustomerRate.TrunkID = temp.TrunkID
		AND CustomerID  = AccountID
		AND tblCustomerRate.EffectiveDate = temp.EffectiveDate
		AND ProcessID = "' , p_ProcessID , '"
	WHERE TempRatesImportID IS NULL AND CustomerID = "' , p_AccountID , '" AND tblCustomerRate.TrunkID = "' , p_TrunkID , '";
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
	INSERT INTO tblCustomerRate (RateID,CustomerID,TrunkID,LastModifiedDate,LastModifiedBy,Rate,EffectiveDate,Interval1,IntervalN,ConnectionFee)
	SELECT temp.RateID,temp.AccountID,temp.TrunkID,now(),"SYSTEM IMPORTED",temp.Rate,temp.EffectiveDate,temp.Interval1,temp.IntervalN,temp.ConnectionFee FROM `' , p_tbltemp_name , '` temp
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

END|
DELIMITER ;

-- Dumping structure for procedure Ratemanagement3.prc_putVendorCodeRate
DROP PROCEDURE IF EXISTS `prc_putVendorCodeRate`;
DELIMITER |
CREATE PROCEDURE `prc_putVendorCodeRate`(
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
	LEFT JOIN (SELECT AccountID,TrunkID,RateID,MAX(EffectiveDate) as EffectiveDate FROM `' , p_tbltemp_name , '`  WHERE ProcessID = "' , p_ProcessID , '"  GROUP BY  AccountID,TrunkID,RateID )tbl
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

END|
DELIMITER ;

-- Dumping structure for procedure Ratemanagement3.prc_putVendorPreference
DROP PROCEDURE IF EXISTS `prc_putVendorPreference`;
DELIMITER |
CREATE PROCEDURE `prc_putVendorPreference`(
	IN `p_AccountID` INT,
	IN `p_TrunkID` INT,
	IN `p_tbltemp_name` VARCHAR(200),
	IN `p_ProcessID` VARCHAR(200)
)
BEGIN

	/* delete codes which are not exist in temp table*/
	SET @stm = CONCAT('
	DELETE tblVendorPreference FROM tblVendorPreference
	LEFT JOIN `' , p_tbltemp_name , '` temp 
		ON tblVendorPreference.RateId = temp.RateID
		AND tblVendorPreference.TrunkID = temp.TrunkID
		AND tblVendorPreference. AccountId  = temp.AccountID
		AND ProcessID = "' , p_ProcessID , '"
	WHERE TempRatesImportID IS NULL AND tblVendorPreference.AccountId = "' , p_AccountID , '" AND tblVendorPreference.TrunkID = "' , p_TrunkID , '";
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;

	/* update codes which are exist in temp table*/
	SET @stm = CONCAT('
	UPDATE tblVendorPreference 
	INNER JOIN `' , p_tbltemp_name , '` temp 
		ON tblVendorPreference.RateId = temp.RateID
		AND tblVendorPreference.TrunkID = temp.TrunkID
		AND tblVendorPreference.AccountId  = temp.AccountID
		AND tblVendorPreference.EffectiveDate = temp.EffectiveDate
	SET tblVendorPreference.Preference = temp.Interval1,created_at=NOW(),CreatedBy="SYSTEM IMPORTED"
	WHERE tblVendorPreference.AccountId = "' , p_AccountID , '" AND tblVendorPreference.TrunkID = "' , p_TrunkID , '" AND ProcessID = "' , p_ProcessID , ' AND tblVendorPreference.Preference <> 0";
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;
	
	/* insert codes which are not exist in customer table*/
	SET @stm = CONCAT('
	INSERT INTO tblVendorPreference (AccountId,RateId,TrunkID,created_at,CreatedBy)
	SELECT DISTINCT temp.AccountID,temp.RateID,temp.TrunkID,now(),"SYSTEM IMPORTED" FROM `' , p_tbltemp_name , '` temp
	LEFT JOIN tblVendorPreference
		ON tblVendorPreference.RateId = temp.RateID
		AND tblVendorPreference.TrunkID = temp.TrunkID
		AND tblVendorPreference.AccountId  = temp.AccountID
		AND tblVendorPreference.EffectiveDate = temp.EffectiveDate
		AND ProcessID = "' , p_ProcessID , '"
	WHERE VendorRateID IS NULL AND temp.AccountID = "' , p_AccountID , '" AND temp.TrunkID = "' , p_TrunkID , '" AND tblVendorPreference.Preference <> 0 AND temp.RateID IS NOT NULL;
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;

END|
DELIMITER ;

-- Dumping structure for procedure Ratemanagement3.prc_updateRateID
DROP PROCEDURE IF EXISTS `prc_updateRateID`;
DELIMITER |
CREATE PROCEDURE `prc_updateRateID`(
	IN `p_AccountID` INT,
	IN `p_CodeDeckID` INT,
	IN `p_tbltemp_name` VARCHAR(200),
	IN `p_ProcessID` VARCHAR(200)
)
BEGIN

	SET @rowcount = 1;

	WHILE @rowcount  > 0 DO

		SET @stm = CONCAT('
		INSERT IGNORE INTO tblRate (CountryID,Description,CompanyID,CodeDeckId,Code,Interval1,IntervalN,CreatedBy)
		SELECT DISTINCT fnGetCountryIdByCodeAndCountry(temp.Code,temp.Description),temp.Description,temp.CompanyID,"' , p_CodeDeckID , '",temp.Code,temp.Interval1,temp.IntervalN,"SYSTEM IMPOERTED"
		FROM `' , p_tbltemp_name , '` temp 
		LEFT JOIN tblRate code ON code.CompanyID = temp.CompanyID AND code.Code = temp.Code AND code.CodeDeckId="' , p_CodeDeckID , '"
		WHERE ProcessID="' , p_ProcessID , '" AND code.RateID IS NULL
		LIMIT 1000;
		');

		PREPARE stm FROM @stm;
		EXECUTE stm;
		DEALLOCATE PREPARE stm;
		SET @stm = CONCAT('

		SELECT COUNT(DISTINCT temp.Code) INTO @rowcount
		FROM `' , p_tbltemp_name , '` temp 
		LEFT JOIN tblRate code ON code.CompanyID = temp.CompanyID AND code.Code = temp.Code AND code.CodeDeckId="' , p_CodeDeckID , '"
		WHERE ProcessID="' , p_ProcessID , '" AND code.RateID IS NULL;
		');

		PREPARE stm FROM @stm;
		EXECUTE stm;
		DEALLOCATE PREPARE stm;

	END WHILE;

	DROP TEMPORARY TABLE IF EXISTS tmp_codes_;
	CREATE TEMPORARY TABLE tmp_codes_ (
		RateID INT,
		Code VARCHAR(50),
		INDEX tmp_codes_RateID (`RateID`),
		INDEX tmp_codes_Code (`Code`)
	);
	INSERT INTO tmp_codes_
	SELECT
	DISTINCT
		tblRate.RateID,
		tblRate.Code
	FROM tblRate
	WHERE
		 tblRate.CodeDeckId = p_CodeDeckID;

	SET @stm = CONCAT('
	UPDATE `' , p_tbltemp_name , '` temp 
	INNER JOIN tmp_codes_ code ON code.Code = temp.Code
		SET temp.RateID = code.RateID
	WHERE ProcessID="' , p_ProcessID , '";
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;

END|
DELIMITER ;

-- Dumping structure for procedure Ratemanagement3.prc_VendorRatesFileImport
DROP PROCEDURE IF EXISTS `prc_VendorRatesFileImport`;
DELIMITER |
CREATE PROCEDURE `prc_VendorRatesFileImport`(
	IN `p_ProcessID` VARCHAR(200),
	IN `p_tbltemp_name` VARCHAR(200)
)
BEGIN

	DECLARE v_pointer_ INT;
	DECLARE v_rowCount_ INT;
	DECLARE v_TrunkID_ INT;
	DECLARE v_AccountID_ INT;
	DECLARE v_codedeckid_ INT;
	DECLARE v_companyid_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_AccountTrunk_;
	CREATE TEMPORARY TABLE tmp_AccountTrunk_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AccountID INT,
		TrunkID INT
	);
	SET @stm = CONCAT('
	INSERT INTO tmp_AccountTrunk_(AccountID,TrunkID)
	SELECT DISTINCT AccountID,TrunkID FROM `' , p_tbltemp_name , '` ud WHERE ProcessID="' , p_processId , '" AND AccountID IS NOT NULL AND TrunkID IS NOT NULL;
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;

	SET v_pointer_ = 1;
	SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_AccountTrunk_);

	WHILE v_pointer_ <= v_rowCount_
	DO

		SET v_TrunkID_ = (SELECT TrunkID FROM tmp_AccountTrunk_ t WHERE t.RowID = v_pointer_); 
		SET v_AccountID_ = (SELECT AccountID FROM tmp_AccountTrunk_ t WHERE t.RowID = v_pointer_);
		SET v_codedeckid_ = (SELECT CodeDeckId FROM tblVendorTrunk WHERE tblVendorTrunk.TrunkID = v_TrunkID_ AND tblVendorTrunk.AccountID = v_AccountID_ AND tblVendorTrunk.Status = 1);

		IF v_codedeckid_ IS NOT NULL AND (SELECT COUNT(*) FROM tblCodeDeck WHERE CodeDeckId = v_codedeckid_)>0
		THEN

			SET v_companyid_ = (SELECT CompanyId FROM tblCodeDeck WHERE CodeDeckId = v_codedeckid_);			

			-- code insert and update rate id in temp table
			CALL prc_updateRateID(v_AccountID_,v_codedeckid_,p_tbltemp_name,p_ProcessID);

			-- CALL prc_GetCustomerRate(v_companyid_,v_AccountID_,v_TrunkID_,null,null,null,'All',1,0,0,0,'','',-1);
			
			-- vendorrate insert,update and delete
			CALL prc_putVendorCodeRate(v_AccountID_,v_TrunkID_,p_tbltemp_name,p_ProcessID);

		END IF;

		SET v_pointer_ = v_pointer_ + 1;

	END WHILE;

	SET @stm = CONCAT('
	DELETE FROM `' , p_tbltemp_name , '` WHERE ProcessID="' , p_processId , '" ;
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm; 

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END|
DELIMITER ;