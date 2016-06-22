CREATE DEFINER=`root`@`localhost` PROCEDURE `fnGetUsageForSummaryLive`(IN `p_CompanyID` INT, IN `p_StartDate` DATE, IN `p_EndDate` DATE)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	/*DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageDetailsReport_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageDetailsReport_(
		UsageDetailID INT,
		AccountID int,
		CompanyID INT,
		CompanyGatewayID INT,
		GatewayAccountID VARCHAR(100),
		trunk varchar(50),
		area_prefix varchar(50),
		duration int,
		billed_duration int,
		cost decimal(18,6),
		connect_time time,
		connect_date date,
		call_status tinyint, 
		INDEX temp_connect_time (`connect_time`,`connect_date`)

	);*/
	DELETE FROM tmp_tblUsageDetailsReportLive WHERE CompanyID = p_CompanyID;
	INSERT INTO tmp_tblUsageDetailsReportLive  
	SELECT
		ud.UsageDetailID,
		uh.AccountID,
		uh.CompanyID,
		uh.CompanyGatewayID,
		uh.GatewayAccountID,
		trunk,
		area_prefix,
		duration,
		billed_duration,
		cost,
		CONCAT(DATE_FORMAT(ud.connect_time,'%H'),':00:00'),
		DATE_FORMAT(ud.connect_time,'%Y-%m-%d'),
		1 as call_status
	FROM LocalRMCdr.tblUsageDetails  ud
	INNER JOIN LocalRMCdr.tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	WHERE
		uh.CompanyID = p_CompanyID
	AND uh.AccountID is not null
	AND uh.StartDate BETWEEN p_StartDate AND p_EndDate;

	INSERT INTO tmp_tblUsageDetailsReportLive  
	SELECT
		ud.UsageDetailFailedCallID,
		uh.AccountID,
		uh.CompanyID,
		uh.CompanyGatewayID,
		uh.GatewayAccountID,
		trunk,
		area_prefix,
		duration,
		billed_duration,
		cost,
		CONCAT(DATE_FORMAT(ud.connect_time,'%H'),':00:00'),
		DATE_FORMAT(ud.connect_time,'%Y-%m-%d'),
		2 as call_status
	FROM LocalRMCdr.tblUsageDetailFailedCall  ud
	INNER JOIN LocalRMCdr.tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	WHERE
		uh.CompanyID = p_CompanyID
	AND uh.AccountID is not null
	AND uh.StartDate BETWEEN p_StartDate AND p_EndDate;

END