CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_DeleteDuplicateUniqueID2`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_ProcessID` VARCHAR(200),
	IN `p_tbltempusagedetail_name` VARCHAR(200)
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	SET @stm1 = CONCAT('
		DELETE tud FROM `' , p_tbltempusagedetail_name , '` tud
		INNER JOIN tblVendorCDR ud ON tud.ID =ud.ID
		INNER JOIN  tblVendorCDRHeader uh on uh.VendorCDRHeaderID = ud.VendorCDRHeaderID
			AND tud.CompanyID = uh.CompanyID
			AND tud.CompanyGatewayID = uh.CompanyGatewayID
		WHERE tud.CompanyID = "' , p_CompanyID , '"
		AND tud.CompanyGatewayID = "' , p_CompanyGatewayID , '"
		AND tud.ProcessID = "' , p_processId , '";
	');
	PREPARE stmt1 FROM @stm1;
	EXECUTE stmt1;
	DEALLOCATE PREPARE stmt1;

	SET @stm2 = CONCAT('
		DELETE tud FROM `' , p_tbltempusagedetail_name , '` tud
		INNER JOIN tblVendorCDRFailed ud ON tud.ID =ud.ID
		INNER JOIN  tblVendorCDRHeader uh on uh.VendorCDRHeaderID = ud.VendorCDRHeaderID
			AND tud.CompanyID = uh.CompanyID
			AND tud.CompanyGatewayID = uh.CompanyGatewayID
		WHERE tud.CompanyID = "' , p_CompanyID , '"
		AND tud.CompanyGatewayID = "' , p_CompanyGatewayID , '"
		AND tud.ProcessID = "' , p_processId , '";
	');
	PREPARE stmt2 FROM @stm2;
	EXECUTE stmt2;
	DEALLOCATE PREPARE stmt2;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END