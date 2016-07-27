CREATE DEFINER=`root`@`localhost` PROCEDURE `fnGetVendorUsageForSummary`(IN `p_CompanyID` INT, IN `p_StartDate` DATE, IN `p_EndDate` DATE)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	/*DROP TEMPORARY TABLE IF EXISTS tmp_tblVendorUsageDetailsReport_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblVendorUsageDetailsReport_(
		VendorCDRID INT,
		AccountID int,
		CompanyID INT,
		CompanyGatewayID INT,
		GatewayAccountID VARCHAR(100),
		trunk varchar(50),
		area_prefix varchar(50),
		duration int,
		billed_duration int,
		buying_cost decimal(18,6),
		selling_cost decimal(18,6),
		connect_time time,
		connect_date date,
		call_status tinyint, 
		INDEX temp_connect_time (`connect_time`,`connect_date`)

	);*/
	DELETE FROM tmp_tblVendorUsageDetailsReport WHERE CompanyID = p_CompanyID;
	INSERT INTO tmp_tblVendorUsageDetailsReport  
	SELECT
		ud.VendorCDRID,
		uh.AccountID,
		uh.CompanyID,
		uh.CompanyGatewayID,
		uh.GatewayAccountID,
		trunk,
		area_prefix,
		duration,
		billed_duration,
		buying_cost,
		selling_cost,
		CONCAT(DATE_FORMAT(ud.connect_time,'%H'),':00:00'),
		DATE_FORMAT(ud.connect_time,'%Y-%m-%d'),
		1 as call_status
	FROM LocalRMCdr.tblVendorCDR  ud
	INNER JOIN LocalRMCdr.tblVendorCDRHeader uh
		ON uh.VendorCDRHeaderID = ud.VendorCDRHeaderID 
	WHERE
		uh.CompanyID = p_CompanyID
	AND uh.AccountID is not null
	AND uh.StartDate BETWEEN p_StartDate AND p_EndDate;

	INSERT INTO tmp_tblVendorUsageDetailsReport  
	SELECT
		ud.VendorCDRFailedID,
		uh.AccountID,
		uh.CompanyID,
		uh.CompanyGatewayID,
		uh.GatewayAccountID,
		trunk,
		area_prefix,
		duration,
		billed_duration,
		buying_cost,
		selling_cost,
		CONCAT(DATE_FORMAT(ud.connect_time,'%H'),':00:00'),
		DATE_FORMAT(ud.connect_time,'%Y-%m-%d'),
		2 as call_status
	FROM LocalRMCdr.tblVendorCDRFailed  ud
	INNER JOIN LocalRMCdr.tblVendorCDRHeader uh
		ON uh.VendorCDRHeaderID = ud.VendorCDRHeaderID 
	WHERE
		uh.CompanyID = p_CompanyID
	AND uh.AccountID is not null
	AND uh.StartDate BETWEEN p_StartDate AND p_EndDate;

END