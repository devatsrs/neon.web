CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_updateRateID`(
	IN `p_AccountID` INT,
	IN `p_CodeDeckID` INT,
	IN `p_tbltemp_name` VARCHAR(200),
	IN `p_ProcessID` VARCHAR(200)
)
BEGIN

	DECLARE v_code_count INT;
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

END