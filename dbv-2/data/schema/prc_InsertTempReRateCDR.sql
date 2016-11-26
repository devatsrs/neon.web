CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_InsertTempReRateCDR`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_start_date` DATETIME,
	IN `p_end_date` DATETIME,
	IN `p_AccountID` INT,
	IN `p_ProcessID` VARCHAR(50),
	IN `p_tbltempusagedetail_name` VARCHAR(50),
	IN `p_CDRType` CHAR(1),
	IN `p_CLI` VARCHAR(50),
	IN `p_CLD` VARCHAR(50),
	IN `p_zerovaluecost` INT,
	IN `p_CurrencyID` INT,
	IN `p_area_prefix` VARCHAR(50),
	IN `p_trunk` VARCHAR(50)
)
BEGIN
	DECLARE v_BillingTime_ INT;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetBillingTime(p_CompanyGatewayID,p_AccountID) INTO v_BillingTime_;

	Call fnUsageDetail(p_CompanyID,p_AccountID,p_CompanyGatewayID,p_start_date,p_end_date,0,1,v_BillingTime_,p_CDRType,p_CLI,p_CLD,p_zerovaluecost);

	set @stm1 = CONCAT('

	INSERT INTO NeonCDRDev.`' , p_tbltempusagedetail_name , '` (CompanyID,CompanyGatewayID,GatewayAccountID,AccountID,connect_time,disconnect_time,billed_duration,billed_second,trunk,area_prefix,cli,cld,cost,ProcessID,duration,is_inbound,ID)

	SELECT "',p_CompanyID,'","',p_CompanyGatewayID,'",ud.GatewayAccountID ,ud.AccountID,ud.connect_time,ud.disconnect_time,ud.billed_duration,ud.billed_second,"Other" as trunk,"Other" as area_prefix,ud.cli,ud.cld,ud.cost,"',p_ProcessID,'",ud.duration,ud.is_inbound,ud.ID
	FROM tmp_tblUsageDetails_ ud
	INNER JOIN NeonRMDev.tblAccount a
		ON ud.AccountID = a.AccountID
	WHERE (' , p_CurrencyID , ' = 0 OR a.CurrencyId = ' , p_CurrencyID , ')
		AND ( "' , p_area_prefix , '" = "" OR area_prefix LIKE REPLACE( "' , p_area_prefix , '", "*", "%"))
		AND ( "' , p_trunk , '" = ""  OR  trunk = "' , p_trunk , '")
	
	');

	PREPARE stmt1 FROM @stm1;
	EXECUTE stmt1;
	DEALLOCATE PREPARE stmt1;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END