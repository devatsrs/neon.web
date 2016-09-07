CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_ProcessDiscountPlan`(IN `p_processId` INT, IN `p_tbltempusagedetail_name` VARCHAR(200))
BEGIN
	
	DECLARE v_rowCount_ INT;
	DECLARE v_pointer_ INT;	
	DECLARE v_AccountID_ INT;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	/* temp accounts*/
	DROP TEMPORARY TABLE IF EXISTS tmp_Accounts_;
	CREATE TEMPORARY TABLE tmp_Accounts_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AccountID INT
	);
	SET @stm = CONCAT('
	INSERT INTO tmp_Accounts_(AccountID)
	SELECT DISTINCT ud.AccountID FROM NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud 
	INNER JOIN tblAccountDiscountPlan adp
		ON ud.AccountID = adp.AccountID AND Type = 1
	WHERE ProcessID="' , p_processId , '" AND ud.is_inbound = 0;
	');
	
	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;	
	
	SET v_pointer_ = 1;
	SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_Accounts_);

	WHILE v_pointer_ <= v_rowCount_
	DO

		SET v_AccountID_ = (SELECT AccountID FROM tmp_Accounts_ t WHERE t.RowID = v_pointer_);
		
		/* apply discount plan*/
		CALL prc_applyAccountDiscountPlan(v_AccountID_,p_tbltempusagedetail_name,p_processId,0);
		
		SET v_pointer_ = v_pointer_ + 1;
	END WHILE;
	
	/* temp accounts*/
	DROP TEMPORARY TABLE IF EXISTS tmp_Accounts_;
	CREATE TEMPORARY TABLE tmp_Accounts_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AccountID INT
	);
	SET @stm = CONCAT('
	INSERT INTO tmp_Accounts_(AccountID)
	SELECT DISTINCT ud.AccountID FROM NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud 
	INNER JOIN tblAccountDiscountPlan adp
		ON ud.AccountID = adp.AccountID AND Type = 2
	WHERE ProcessID="' , p_processId , '" AND ud.is_inbound = 1;
	');
	
	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
	
	SET v_pointer_ = 1;
	SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_Accounts_);

	WHILE v_pointer_ <= v_rowCount_
	DO

		SET v_AccountID_ = (SELECT AccountID FROM tmp_Accounts_ t WHERE t.RowID = v_pointer_);
		
		/* apply discount plan*/
		CALL prc_applyAccountDiscountPlan(v_AccountID_,p_tbltempusagedetail_name,p_processId,1);
		
		SET v_pointer_ = v_pointer_ + 1;
	END WHILE;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END