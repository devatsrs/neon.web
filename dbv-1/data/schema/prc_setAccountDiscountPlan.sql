CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_setAccountDiscountPlan`(IN `p_AccountID` INT, IN `p_DiscountPlanID` INT, IN `p_Type` INT, IN `p_BillingDays` INT, IN `p_CreatedBy` VARCHAR(50))
BEGIN
	
	DECLARE v_AccountDiscountPlanID INT;
	DECLARE v_DayDiff_ INT;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT IF(DATEDIFF(NextInvoiceDate,DATE(NOW()))<0,0,DATEDIFF(NextInvoiceDate,DATE(NOW()))) INTO v_DayDiff_ FROM tblAccountBilling  WHERE AccountID = p_AccountID;	
	
	DELETE ads FROM tblAccountDiscountScheme ads
	INNER JOIN tblAccountDiscountPlan adp
		ON adp.AccountDiscountPlanID = ads.AccountDiscountPlanID
	WHERE AccountID = p_AccountID 
		AND Type = p_Type;
		
	DELETE FROM tblAccountDiscountPlan WHERE AccountID = p_AccountID AND Type = p_Type; 
	
	IF p_DiscountPlanID > 0
	THEN
	 
		INSERT INTO tblAccountDiscountPlan (AccountID,DiscountPlanID,Type,CreatedBy,created_at)
		VALUES (p_AccountID,p_DiscountPlanID,p_Type,p_CreatedBy,now());
		
		SET v_AccountDiscountPlanID = LAST_INSERT_ID(); 
		
		INSERT INTO tblAccountDiscountScheme(AccountDiscountPlanID,DiscountID,Threshold,Discount,Unlimited)
		SELECT v_AccountDiscountPlanID,d.DiscountID,Threshold*(v_DayDiff_/p_BillingDays),Discount,Unlimited
		FROM tblDiscountPlan dp
		INNER JOIN tblDiscount d 
			ON d.DiscountPlanID = dp.DiscountPlanID
		INNER JOIN tblDiscountScheme ds
			ON ds.DiscountID = d.DiscountID
		WHERE dp.DiscountPlanID = p_DiscountPlanID;
	
	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END