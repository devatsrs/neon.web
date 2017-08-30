CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_VendorRatesFileImport`(
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

END