CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `fnVendorUsageDetail`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_GatewayID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_billing_time` INT,
	IN `p_CLI` VARCHAR(50),
	IN `p_CLD` VARCHAR(50),
	IN `p_ZeroValueBuyingCost` INT
)
BEGIN

	DROP TEMPORARY TABLE IF EXISTS tmp_tblVendorUsageDetails_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblVendorUsageDetails_(
		AccountID INT,
		AccountName VARCHAR(50),
		trunk VARCHAR(50),
		area_prefix VARCHAR(50),
		VendorCDRID INT,
		billed_duration INT,
		cli VARCHAR(500),
		cld VARCHAR(500),
		selling_cost DECIMAL(18,6),
		buying_cost DECIMAL(18,6),
		connect_time DATETIME,
		disconnect_time DATETIME
	);
	INSERT INTO tmp_tblVendorUsageDetails_
	SELECT
	*
	FROM (
		SELECT
			uh.AccountID,
			a.AccountName,
			trunk,
			area_prefix,
			VendorCDRID,
			billed_duration,
			cli,
			cld,
			selling_cost,
			buying_cost,
			connect_time,
			disconnect_time
		FROM NeonCDRDev.tblVendorCDR  ud
		INNER JOIN NeonCDRDev.tblVendorCDRHeader uh
			ON uh.VendorCDRHeaderID = ud.VendorCDRHeaderID
		INNER JOIN NeonRMDev.tblAccount a
			ON uh.AccountID = a.AccountID
		WHERE StartDate >= DATE_ADD(p_StartDate,INTERVAL -1 DAY)
		AND StartDate <= DATE_ADD(p_EndDate,INTERVAL 1 DAY)
		AND uh.CompanyID = p_CompanyID
		AND uh.AccountID IS NOT NULL
		AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
		AND (p_GatewayID = 0 OR CompanyGatewayID = p_GatewayID)
		AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
		AND (p_CLI = '' OR cli LIKE REPLACE(p_CLI, '*', '%'))
		AND (p_CLD = '' OR cld LIKE REPLACE(p_CLD, '*', '%'))
		AND (p_ZeroValueBuyingCost = 0 OR ( p_ZeroValueBuyingCost = 1 AND buying_cost = 0) OR ( p_ZeroValueBuyingCost = 2 AND buying_cost > 0))
	) tbl
	WHERE 
	(p_billing_time =1 AND connect_time >= p_StartDate AND connect_time <= p_EndDate)
	OR 
	(p_billing_time =2 AND disconnect_time >= p_StartDate AND disconnect_time <= p_EndDate)
	AND billed_duration > 0
	ORDER BY disconnect_time DESC;
END