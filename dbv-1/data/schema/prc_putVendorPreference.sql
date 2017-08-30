CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_putVendorPreference`(
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
		AND ProcessID = "' , p_ProcessID , '"
	WHERE VendorRateID IS NULL AND temp.AccountID = "' , p_AccountID , '" AND temp.TrunkID = "' , p_TrunkID , '" AND tblVendorPreference.Preference <> 0 AND temp.RateID IS NOT NULL;
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;

END