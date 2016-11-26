CREATE DEFINER=`root`@`localhost` FUNCTION `fnGetBillingTime`(
	`p_GatewayID` INT,
	`p_AccountID` INT
) RETURNS int(11)
BEGIN

	DECLARE v_BillingTime_ INT;

	SELECT 
		BillingTime 
	INTO v_BillingTime_
	FROM NeonRMDev.tblCompanyGateway cg
	INNER JOIN tblGatewayAccount ga ON ga.CompanyGatewayID = cg.CompanyGatewayID
	WHERE (p_AccountID = 0 OR AccountID = p_AccountID ) AND (p_GatewayID = 0 OR ga.CompanyGatewayID = p_GatewayID)
	LIMIT 1;
	
	SET v_BillingTime_ = IFNULL(v_BillingTime_,1);

	RETURN v_BillingTime_;
END