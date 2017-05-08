CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_ProcessCDRService`(
	IN `p_CompanyID` INT,
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200)
)
BEGIN

	DECLARE v_ServiceAccountID_CLI_Count_ INT;
	DECLARE v_ServiceAccountID_IP_Count_ INT;

	SELECT COUNT(*) INTO v_ServiceAccountID_CLI_Count_ 
	FROM NeonRMDev.tblAccountAuthenticate aa
	INNER JOIN NeonRMDev.tblCLIRateTable crt ON crt.AccountID = aa.AccountID
	WHERE aa.CompanyID = p_CompanyID AND (CustomerAuthRule = 'CLI' OR VendorAuthRule = 'CLI') AND crt.ServiceID > 0 ;

	IF v_ServiceAccountID_CLI_Count_ > 0
	THEN

		/* update cdr service */
		SET @stm = CONCAT('
		UPDATE NeonCDRDev.`' , p_tbltempusagedetail_name , '` uh
		INNER JOIN NeonRMDev.tblCLIRateTable ga
			ON  ga.CompanyID = uh.CompanyID
			AND ga.CLI = uh.GatewayAccountID
		SET uh.ServiceID = ga.ServiceID
		WHERE ga.ServiceID > 0
		AND uh.CompanyID = ' ,  p_CompanyID , '
		AND uh.ProcessID = "' , p_processId , '" ;
		');
		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

	END IF;
	
	SELECT COUNT(*) INTO v_ServiceAccountID_IP_Count_ 
	FROM NeonRMDev.tblAccountAuthenticate aa
	WHERE aa.CompanyID = p_CompanyID AND (CustomerAuthRule = 'IP' OR VendorAuthRule = 'IP') AND ServiceID > 0;

	IF v_ServiceAccountID_IP_Count_ > 0
	THEN

		/* update cdr service */
		SET @stm = CONCAT('
		UPDATE NeonCDRDev.`' , p_tbltempusagedetail_name , '` uh
		INNER JOIN NeonRMDev.tblAccountAuthenticate ga
			ON  ga.CompanyID = uh.CompanyID
			AND ( FIND_IN_SET(uh.GatewayAccountID,ga.CustomerAuthValue) != 0 OR FIND_IN_SET(uh.GatewayAccountID,ga.VendorAuthValue) != 0 )
		SET uh.ServiceID = ga.ServiceID
		WHERE ga.ServiceID > 0
		AND uh.CompanyID = ' ,  p_CompanyID , '
		AND uh.ProcessID = "' , p_processId , '" ;
		');
		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

	END IF;

END