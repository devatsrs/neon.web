CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_InsertTempReRateCDR`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AccountID` INT,
	IN `p_ProcessID` VARCHAR(50),
	IN `p_tbltempusagedetail_name` VARCHAR(50),
	IN `p_CDRType` VARCHAR(50),
	IN `p_CLI` VARCHAR(50),
	IN `p_CLD` VARCHAR(50),
	IN `p_zerovaluecost` INT,
	IN `p_CurrencyID` INT,
	IN `p_area_prefix` VARCHAR(50),
	IN `p_trunk` VARCHAR(50),
	IN `p_RateMethod` VARCHAR(50)
)
BEGIN
	DECLARE v_BillingTime_ INT;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetBillingTime(p_CompanyGatewayID,p_AccountID) INTO v_BillingTime_;

	

	set @stm1 = CONCAT('

	INSERT INTO NeonCDRDev.`' , p_tbltempusagedetail_name , '` (
		CompanyID,
		CompanyGatewayID,
		GatewayAccountID,
		GatewayAccountPKID,
		AccountID,
		ServiceID,
		connect_time,
		disconnect_time,
		billed_duration,
		area_prefix,
		trunk,
		pincode,
		extension,
		cli,
		cld,
		cost,
		remote_ip,
		duration,
		ProcessID,
		ID,
		is_inbound,
		billed_second,
		disposition,
		userfield,
		AccountName,
		AccountNumber,
		AccountCLI,
		AccountIP
	)

	SELECT
	*
	FROM (SELECT
		uh.CompanyID,
		uh.CompanyGatewayID,
		uh.GatewayAccountID,
		uh.GatewayAccountPKID,
		uh.AccountID,
		uh.ServiceID,
		connect_time,
		disconnect_time,
		billed_duration,
		CASE WHEN   "' , p_RateMethod , '" = "SpecifyRate"
		THEN 
			area_prefix
		ELSE
			"Other" 
		END
		AS area_prefix,
		CASE WHEN   "' , p_RateMethod , '" = "SpecifyRate"
		THEN 
			trunk
		ELSE
			"Other" 
		END
		AS trunk,		
		pincode,
		extension,
		cli,
		cld,
		cost,
		remote_ip,
		duration,
		"',p_ProcessID,'",
		ID,
		is_inbound,
		billed_second,
		disposition,
		userfield,
		IFNULL(ga.AccountName,""),
		IFNULL(ga.AccountNumber,""),
		IFNULL(ga.AccountCLI,""),
		IFNULL(ga.AccountIP,"")
	FROM NeonCDRDev.tblUsageDetails  ud
	INNER JOIN NeonCDRDev.tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	INNER JOIN NeonRMDev.tblAccount a
		ON uh.AccountID = a.AccountID
	LEFT JOIN tblGatewayAccount ga
		ON ga.GatewayAccountPKID = uh.GatewayAccountPKID
	WHERE
	( "' , p_CDRType , '" = "" OR  ud.userfield LIKE  CONCAT("%","' , p_CDRType , '","%"))
	AND  StartDate >= DATE_ADD( "' , p_StartDate , '",INTERVAL -1 DAY)
	AND StartDate <= DATE_ADD( "' , p_EndDate , '",INTERVAL 1 DAY)
	AND uh.CompanyID =  "' , p_CompanyID , '"
	AND uh.AccountID is not null
	AND ( "' , p_AccountID , '" = 0 OR uh.AccountID = "' , p_AccountID , '")
	AND ( "' , p_CompanyGatewayID , '" = 0 OR uh.CompanyGatewayID = "' , p_CompanyGatewayID , '")
	AND ( "' , p_CurrencyID ,'" = "0" OR a.CurrencyId = "' , p_CurrencyID , '")
	AND ( "' , p_CLI , '" = "" OR cli LIKE REPLACE("' , p_CLI , '", "*", "%"))	
	AND ( "' , p_CLD , '" = "" OR cld LIKE REPLACE("' , p_CLD , '", "*", "%"))
	AND ( "' , p_trunk , '" = ""  OR  trunk = "' , p_trunk , '")
	AND ( "' , p_area_prefix , '" = "" OR area_prefix LIKE REPLACE( "' , p_area_prefix , '", "*", "%"))	
	AND ( "' , p_zerovaluecost , '" = 0 OR (  "' , p_zerovaluecost , '" = 1 AND cost = 0) OR (  "' , p_zerovaluecost , '" = 2 AND cost > 0))	
	) tbl
	WHERE 
	("' , v_BillingTime_ , '" =1 and connect_time >=  "' , p_StartDate , '" AND connect_time <=  "' , p_EndDate , '")
	OR 
	("' , v_BillingTime_ , '" =2 and disconnect_time >=  "' , p_StartDate , '" AND disconnect_time <=  "' , p_EndDate , '")
	AND billed_duration > 0;
	');

	
	PREPARE stmt1 FROM @stm1;
	EXECUTE stmt1;
	DEALLOCATE PREPARE stmt1;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END