CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_PostProcessCDR`(IN `p_CompanyID` INT)
BEGIN

	DECLARE v_rowCount_ INT;
	DECLARE v_pointer_ INT;	
	DECLARE v_ProcessID_ VARCHAR(200);

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_ProcessIDS;
	CREATE TEMPORARY TABLE tmp_ProcessIDS  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		TempUsageDownloadLogID INT,
		ProcessID VARCHAR(200)
	);
	
	INSERT INTO tmp_ProcessIDS(TempUsageDownloadLogID,ProcessID)
	SELECT TempUsageDownloadLogID,ProcessID FROM  NeonBillingDev.tblTempUsageDownloadLog WHERE CompanyID = p_CompanyID AND PostProcessStatus=0 LIMIT 50;
	
	DELETE FROM tblCDRPostProcess WHERE CompanyID = p_CompanyID;
	DELETE FROM tblVCDRPostProcess WHERE CompanyID = p_CompanyID;
	
	SET v_pointer_ = 1;
	SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_ProcessIDS);

	WHILE v_pointer_ <= v_rowCount_
	DO
		SET v_ProcessID_ = (SELECT ProcessID FROM tmp_ProcessIDS t WHERE t.RowID = v_pointer_);
		
		CALL prc_insertPostProcessCDR(v_ProcessID_); 
		
	SET v_pointer_ = v_pointer_ + 1;
	END WHILE;
	
	UPDATE tblCDRPostProcess 
	INNER JOIN  NeonRMDev.tblCountry ON area_prefix LIKE CONCAT(Prefix , "%")
	SET tblCDRPostProcess.CountryID =tblCountry.CountryID
	WHERE tblCDRPostProcess.CompanyID = p_CompanyID;
	
	UPDATE tblVCDRPostProcess 
	INNER JOIN  NeonRMDev.tblCountry ON area_prefix LIKE CONCAT(Prefix , "%")
	SET tblVCDRPostProcess.CountryID =tblCountry.CountryID
	WHERE tblVCDRPostProcess.CompanyID = p_CompanyID;
	
	SELECT * FROM tmp_ProcessIDS;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 

END