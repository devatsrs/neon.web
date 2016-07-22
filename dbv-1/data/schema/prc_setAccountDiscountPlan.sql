CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_setAccountDiscountPlan`(IN `p_AccountID` INT, IN `p_DiscountPlanID` INT, IN `p_CreatedBy` VARCHAR(50))
BEGIN
	
	DECLARE v_AccountDiscountPlanID INT;
	DECLARE v_BillingDayDiff_ INT;
	DECLARE v_DayDiff_ INT;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT DATEDIFF(NextInvoiceDate,LastInvoiceDate), DATEDIFF(NextInvoiceDate,DATE(NOW())) INTO v_BillingDayDiff_,v_DayDiff_ FROM tblAccount  WHERE AccountID = p_AccountID;	
	
	IF (SELECT COUNT(*) FROM tblAccountDiscountPlan WHERE AccountID = p_AccountID AND DiscountPlanID = p_DiscountPlanID ) = 0
	THEN
		INSERT INTO tblAccountDiscountPlan (AccountID,DiscountPlanID,CreatedBy,created_at)
		VALUES (p_AccountID,p_DiscountPlanID,p_CreatedBy,now());
		
		SET v_AccountDiscountPlanID = LAST_INSERT_ID(); 
		
		INSERT INTO tblAccountDiscountScheme(AccountDiscountPlanID,DiscountID,Threshold,Discount,Unlimited)
		SELECT v_AccountDiscountPlanID,d.DiscountID,Threshold*(v_DayDiff_/v_BillingDayDiff_),Discount,Unlimited
		FROM tblDiscountPlan dp
		INNER JOIN tblDiscount d 
			ON d.DiscountPlanID = dp.DiscountPlanID
		INNER JOIN tblDiscountScheme ds
			ON ds.DiscountID = d.DiscountID
		WHERE dp.DiscountPlanID = p_DiscountPlanID;
	
	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END