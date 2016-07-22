CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getAccountDiscountPlan`(IN `p_AccountID` INT)
BEGIN
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT 
		dg.Name,
		adc.Threshold,
		IF (adc.Unlimited=1,'Unlimited','') as Unlimited,
		adc.MinutesUsed
	FROM tblAccountDiscountPlan adp
	INNER JOIN tblAccountDiscountScheme adc
		ON adc.AccountDiscountPlanID = adp.AccountDiscountPlanID
	INNER JOIN tblDiscount d
		ON d.DiscountID = adc.DiscountID
	INNER JOIN tblDestinationGroup dg
		ON dg.DestinationGroupID = d.DestinationGroupID
	WHERE AccountID = p_AccountID;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END