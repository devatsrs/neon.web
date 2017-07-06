CREATE DEFINER=`root`@`localhost` PROCEDURE `fnUpdateVendorLink`(
	IN `p_CompanyID` INT,
	IN `p_UniqueID` VARCHAR(50),
	IN `p_StartDate` DATE,
	IN `p_EndDate` DATE
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET @stmt = CONCAT('
	INSERT IGNORE INTO tblTempCallDetail_2_' , p_UniqueID , '
	SELECT cd.* FROM NeonCDRDev.tblCallDetail cd
	INNER JOIN NeonCDRDev.tblVendorCDRHeader uh
		ON uh.VendorCDRHeaderID = cd.VendorCDRHeaderID
	WHERE
		uh.CompanyID = ' , p_CompanyID , '
	AND uh.AccountID IS NOT NULL
	AND uh.StartDate BETWEEN "' , p_StartDate , '" AND "' , p_EndDate , '";
	');

	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET @stmt = CONCAT('
	UPDATE tmp_tblVendorUsageDetailsReport_' , p_UniqueID , ' ud
	INNER JOIN tblTempCallDetail_2_' , p_UniqueID , ' cd on cd.VCID = ud.VendorCDRID
	SET ud.AccountID = cd.AccountID,ud.GatewayAccountPKID = cd.GatewayAccountPKID,ud.call_status = cd.FailCall
	WHERE ud.CompanyID = ' , p_CompanyID , ';
	');

	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

END