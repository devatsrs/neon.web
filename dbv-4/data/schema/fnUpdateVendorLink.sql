CREATE DEFINER=`root`@`localhost` PROCEDURE `fnUpdateVendorLink`(
	IN `p_CompanyID` INT,
	IN `p_StartDate` DATE,
	IN `p_EndDate` DATE,
	IN `p_UniqueID` VARCHAR(50)
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET @stmt = CONCAT('
	UPDATE tmp_tblVendorUsageDetailsReport_' , p_UniqueID , ' vd 
	INNER JOIN tmp_tblUsageDetailsReport_' , p_UniqueID , ' cd ON cd.CompanyGatewayID = vd.CompanyGatewayID AND cd.ID = vd.ID
		SET vd.AccountID = cd.AccountID,vd.GatewayAccountPKID = cd.GatewayAccountPKID,vd.call_status = cd.call_status;
	');

	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
	
	SET @stmt = CONCAT('
	UPDATE tmp_tblVendorUsageDetailsReport_' , p_UniqueID , ' vd 
	INNER JOIN tmp_tblUsageDetailsReport_' , p_UniqueID , ' cd ON cd.CompanyGatewayID = vd.CompanyGatewayID AND cd.ID = vd.ID
		SET vd.AccountID = cd.AccountID,vd.GatewayAccountPKID = cd.GatewayAccountPKID,vd.call_status = cd.call_status,vd.selling_cost =cd.cost
	WHERE cd.cost <> 0 AND vd.billed_duration <> 0;
	');

	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

END