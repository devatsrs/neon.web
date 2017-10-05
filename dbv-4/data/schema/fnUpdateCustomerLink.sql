CREATE DEFINER=`root`@`localhost` PROCEDURE `fnUpdateCustomerLink`(
	IN `p_CompanyID` INT,
	IN `p_StartDate` DATE,
	IN `p_EndDate` DATE,
	IN `p_UniqueID` VARCHAR(50)
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET @stmt = CONCAT('
	INSERT IGNORE INTO tblTempCallDetail_1_' , p_UniqueID , '
	SELECT cd.* FROM NeonCDRDev.tblCallDetail cd
	INNER JOIN NeonCDRDev.tblUsageHeader uh
		ON uh.UsageHeaderID = cd.UsageHeaderID
	WHERE
		uh.CompanyID = ' , p_CompanyID , '
	AND uh.AccountID IS NOT NULL
	AND uh.StartDate BETWEEN "' , p_StartDate , '" AND "' , p_EndDate , '";
	');

	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET @stmt = CONCAT('
	UPDATE tmp_tblUsageDetailsReport_' , p_UniqueID , ' ud
	INNER JOIN tblTempCallDetail_1_' , p_UniqueID , ' cd on cd.CID = ud.UsageDetailID
	SET ud.VAccountID = cd.VAccountID,ud.GatewayVAccountPKID = cd.GatewayVAccountPKID,ud.call_status_v = cd.FailCallV
	WHERE ud.CompanyID = ' , p_CompanyID , ';
	');

	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

END