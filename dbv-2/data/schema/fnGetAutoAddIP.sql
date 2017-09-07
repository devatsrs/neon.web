CREATE DEFINER=`root`@`localhost` FUNCTION `fnGetAutoAddIP`(
	`p_CompanyGatewayID` INT

) RETURNS int(11)
BEGIN

	DECLARE v_AutoAddIP_ INT;

	SELECT 
		CASE WHEN REPLACE(JSON_EXTRACT(cg.Settings, '$.AutoAddIP'),'"','') > 0
		THEN
			CAST(REPLACE(JSON_EXTRACT(cg.Settings, '$.AutoAddIP'),'"','') AS UNSIGNED INTEGER)
		ELSE
			NULL
		END
	INTO v_AutoAddIP_
	FROM NeonRMDev.tblCompanyGateway cg
	WHERE cg.CompanyGatewayID = p_CompanyGatewayID
	LIMIT 1;
	
	SET v_AutoAddIP_ = IFNULL(v_AutoAddIP_,0);

	RETURN v_AutoAddIP_;
END