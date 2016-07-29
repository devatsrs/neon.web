CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_isDiscountPlanApplied`(IN `p_Action` VARCHAR(50), IN `p_DestinationGroupSetID` INT, IN `p_DiscountPlanID` INT)
BEGIN
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	IF p_Action = 'DestinationGroupSet'
	THEN
		
		SELECT 
			dgs.DestinationGroupSetID 
		FROM tblDestinationGroupSet dgs
		INNER JOIN tblDiscountPlan dp
			ON dp.DestinationGroupSetID = dgs.DestinationGroupSetID
		INNER JOIN tblAccountDiscountPlan adp
			ON adp.DiscountPlanID = dp.DiscountPlanID
		WHERE dgs.DestinationGroupSetID = p_DestinationGroupSetID;
	
	END IF;
	
	IF p_Action = 'DiscountPlan'
	THEN
		
		SELECT 
			dp.DiscountPlanID 
		FROM tblDiscountPlan dp
		INNER JOIN tblAccountDiscountPlan adp
			ON adp.DiscountPlanID = dp.DiscountPlanID
		WHERE adp.DiscountPlanID = p_DiscountPlanID;
	
	END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END