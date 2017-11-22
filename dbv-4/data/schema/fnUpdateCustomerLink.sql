CREATE DEFINER=`root`@`localhost` PROCEDURE `fnUpdateCustomerLink`(
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
		SET cd.VAccountID = vd.VAccountID,cd.GatewayVAccountPKID = vd.GatewayVAccountPKID,cd.call_status_v = vd.call_status_v,cd.buying_cost =vd.buying_cost
	WHERE vd.buying_cost <> 0;
	');

	PREPARE stmt FROM @stmt;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

END