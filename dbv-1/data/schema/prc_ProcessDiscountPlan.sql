CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_ProcessDiscountPlan`(IN `p_tbltempusagedetail_name` VARCHAR(200), IN `p_processId` INT)
BEGIN
	
	DECLARE v_rowCount_ INT;
	DECLARE v_pointer_ INT;	
	DECLARE v_AccountID_ INT;
	DECLARE v_DiscountPlanID_ INT;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	/* temp accounts*/
	DROP TEMPORARY TABLE IF EXISTS tmp_Accounts_;
	CREATE TEMPORARY TABLE tmp_Accounts_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AccountID INT,
		TrunkID INT
	);
	SET @stm = CONCAT('
	INSERT INTO tmp_Accounts_(AccountID)
	SELECT DISTINCT AccountID,TrunkID FROM NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud WHERE ProcessID="' , p_processId , '" AND AccountID IS NOT NULL AND ud.is_inbound = 0;
	');
	
	SET v_pointer_ = 1;
	SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_Accounts_);

	WHILE v_pointer_ <= v_rowCount_
	DO

		SET v_AccountID_ = (SELECT AccountID FROM tmp_Accounts_ t WHERE t.RowID = v_pointer_);
		
		/* get discount plan id*/
		SET v_DiscountPlanID_ = (SELECT DiscountPlanID FROM tblAccountDiscountPlan WHERE AccountID = v_AccountID_);
		
		/* apply discount plan if any*/
		IF v_DiscountPlanID_ > 0
		THEN
			CALL prc_applyAccountDiscountPlan(v_AccountID_,p_tbltempusagedetail_name,p_processId);
		END IF;
		
		SET v_pointer_ = v_pointer_ + 1;
	END WHILE;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END