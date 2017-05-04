CREATE DEFINER=`root`@`localhost` PROCEDURE `fnUsageDetail`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_GatewayID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_billing_time` INT,
	IN `p_cdr_type` CHAR(1),
	IN `p_CLI` VARCHAR(50),
	IN `p_CLD` VARCHAR(50),
	IN `p_zerovaluecost` INT
)
BEGIN
	DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageDetails_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageDetails_(
		AccountID int,
		AccountName varchar(100),
		GatewayAccountID varchar(100),
		trunk varchar(50),
		area_prefix varchar(50),
		pincode VARCHAR(50),
		extension VARCHAR(50),
		UsageDetailID int,
		duration int,
		billed_duration int,
		billed_second int,
		cli varchar(500),
		cld varchar(500),
		cost decimal(18,6),
		connect_time datetime,
		disconnect_time datetime,
		is_inbound tinyint(1) default 0,
		ID INT,
		ServiceID INT
	);
	INSERT INTO tmp_tblUsageDetails_
	SELECT
	*
	FROM (
		SELECT
			uh.AccountID,
			a.AccountName,
			uh.GatewayAccountID,
			trunk,
			area_prefix,
			pincode,
			extension,
			UsageDetailID,
			duration,
			billed_duration,
			billed_second,
			cli,
			cld,
			cost,
			connect_time,
			disconnect_time,
			ud.is_inbound,
			ud.ID,
			uh.ServiceID
		FROM NeonCDRDev.tblUsageDetails  ud
		INNER JOIN NeonCDRDev.tblUsageHeader uh
			ON uh.UsageHeaderID = ud.UsageHeaderID
		INNER JOIN NeonRMDev.tblAccount a
			ON uh.AccountID = a.AccountID
		WHERE
		(p_cdr_type = '' OR  ud.is_inbound = p_cdr_type)
		AND  StartDate >= DATE_ADD(p_StartDate,INTERVAL -1 DAY)
		AND StartDate <= DATE_ADD(p_EndDate,INTERVAL 1 DAY)
		AND uh.CompanyID = p_CompanyID
		AND uh.AccountID IS NOT NULL
		AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
		AND (p_GatewayID = 0 OR CompanyGatewayID = p_GatewayID)
		AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
		AND (p_CLI = '' OR cli LIKE REPLACE(p_CLI, '*', '%'))
		AND (p_CLD = '' OR cld LIKE REPLACE(p_CLD, '*', '%'))
		AND (p_zerovaluecost = 0 OR ( p_zerovaluecost = 1 AND cost = 0) OR ( p_zerovaluecost = 2 AND cost > 0))
	) tbl
	WHERE 
	(p_billing_time =1 AND connect_time >= p_StartDate AND connect_time <= p_EndDate)
	OR 
	(p_billing_time =2 AND disconnect_time >= p_StartDate AND disconnect_time <= p_EndDate)
	AND billed_duration > 0
	ORDER BY disconnect_time DESC;
END