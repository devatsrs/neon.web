CREATE DEFINER=`root`@`localhost` PROCEDURE `fnGetUsageForSummary`(IN `p_CompanyID` INT, IN `p_StartDate` DATE, IN `p_EndDate` DATE)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageDetailsReport_;
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
		connect_time datetime,
		disconnect_time datetime,
		INDEX temp_connect_time (`connect_time`)
	);
	
	INSERT INTO tmp_tblUsageDetailsReport_  
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
		connect_time,
		disconnect_time
	FROM LocalRMCdr.tblUsageDetails  ud
	INNER JOIN LocalRMCdr.tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	WHERE
		uh.CompanyID = p_CompanyID
	AND uh.AccountID is not null
	AND uh.StartDate BETWEEN p_StartDate AND p_EndDate;

	DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageDetailFail_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageDetailFail_(
		UsageDetailFailedCallID INT,
		AccountID int,
		CompanyID INT,
		CompanyGatewayID INT,
		GatewayAccountID VARCHAR(100),
		trunk varchar(50),
		area_prefix varchar(50),
		duration int,
		billed_duration int,
		cost decimal(18,6),
		connect_time datetime,
		disconnect_time datetime,
		INDEX temp_connect_time_2 (`connect_time`)
	);
	 
	INSERT INTO tmp_tblUsageDetailFail_  
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
		connect_time,
		disconnect_time
	FROM LocalRMCdr.tblUsageDetailFailedCall  ud
	INNER JOIN LocalRMCdr.tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	WHERE
		uh.CompanyID = p_CompanyID
	AND uh.AccountID is not null
	AND uh.StartDate BETWEEN p_StartDate AND p_EndDate;

END