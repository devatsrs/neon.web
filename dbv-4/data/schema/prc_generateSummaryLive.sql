CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_generateSummaryLive`(IN `p_CompanyID` INT, IN `p_StartDate` DATE, IN `p_EndDate` DATE)
BEGIN
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	CALL fnGetCountry(); 
	CALL fnGetUsageForSummaryLive(p_CompanyID, p_StartDate, p_EndDate);
	/* used for success call summary
	DROP TEMPORARY TABLE IF EXISTS tmp_UsageSummary;
	CREATE TEMPORARY TABLE `tmp_UsageSummary` (
		`DateID` BIGINT(20) NOT NULL,
		`TimeID` INT(11) NOT NULL,
		`CompanyID` INT(11) NOT NULL,
		`CompanyGatewayID` INT(11) NOT NULL,
		`GatewayAccountID` VARCHAR(100) NULL DEFAULT NULL,
		`AccountID` INT(11) NOT NULL,
		`Trunk` VARCHAR(50) NULL DEFAULT NULL ,
		`AreaPrefix` VARCHAR(100) NULL DEFAULT NULL ,
		`TotalCharges` DOUBLE NULL DEFAULT NULL,
		`TotalBilledDuration` INT(11) NULL DEFAULT NULL,
		`TotalDuration` INT(11) NULL DEFAULT NULL,
		`NoOfCalls` INT(11) NULL DEFAULT '0',
		`NoOfFailCalls` INT(11) NULL DEFAULT '0',
		`FinalStatus` INT(11) NULL DEFAULT '0',
		`CountryID` INT(11) NULL DEFAULT NULL,
		INDEX `tblUsageSummary_dim_date` (`DateID`),
		INDEX `tmp_UsageSummary_AreaPrefix` (`AreaPrefix`),
		INDEX `Unique_key` (`DateID`, `CompanyID`, `AccountID`, `GatewayAccountID`, `CompanyGatewayID`, `Trunk`, `AreaPrefix`)
		
	);*/
 	/* insert into success summary*/
 	DELETE FROM tmp_UsageSummaryLive WHERE CompanyID = p_CompanyID;
	INSERT INTO tmp_UsageSummaryLive(DateID,TimeID,CompanyID,CompanyGatewayID,GatewayAccountID,AccountID,Trunk,AreaPrefix,TotalCharges,TotalBilledDuration,TotalDuration,NoOfCalls,NoOfFailCalls)
	SELECT 
		d.DateID,
		t.TimeID,
		ud.CompanyID,
		ud.CompanyGatewayID,
		ANY_VALUE(ud.GatewayAccountID),
		ud.AccountID,
		ud.trunk,
		ud.area_prefix,
		COALESCE(SUM(ud.cost),0)  AS TotalCharges ,
		COALESCE(SUM(ud.billed_duration),0) AS TotalBilledDuration ,
		COALESCE(SUM(ud.duration),0) AS TotalDuration,
		SUM(IF(ud.call_status=1,1,0)) AS  NoOfCalls,
		SUM(IF(ud.call_status=2,1,0)) AS  NoOfFailCalls
	FROM tmp_tblUsageDetailsReportLive ud  
	INNER JOIN tblDimTime t ON t.fulltime = connect_time
	INNER JOIN tblDimDate d ON d.date = connect_date
	GROUP BY d.DateID,t.TimeID,ud.area_prefix,ud.trunk,ud.AccountID,ud.CompanyGatewayID,ud.CompanyID;

	-- DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageDetailsReport_;
	
	UPDATE tmp_UsageSummaryLive  FORCE INDEX (tmp_UsageSummary_AreaPrefix)
	INNER JOIN  temptblCountry as tblCountry ON AreaPrefix LIKE CONCAT(Prefix , "%")
	SET tmp_UsageSummaryLive.CountryID =tblCountry.CountryID;
	
	INSERT INTO tblSummaryHeader (DateID,CompanyID,AccountID,GatewayAccountID,CompanyGatewayID,Trunk,AreaPrefix,CountryID,created_at)
	SELECT us.DateID,us.CompanyID,us.AccountID,ANY_VALUE(us.GatewayAccountID),us.CompanyGatewayID,us.Trunk,us.AreaPrefix,ANY_VALUE(us.CountryID),now() 
	FROM tmp_UsageSummaryLive us
	LEFT JOIN tblSummaryHeader sh	 
	ON 
		 us.DateID = sh.DateID
	AND us.CompanyID = sh.CompanyID
	AND us.AccountID = sh.AccountID
	AND us.CompanyGatewayID = sh.CompanyGatewayID
	AND us.Trunk = sh.Trunk
	AND us.AreaPrefix = sh.AreaPrefix
	WHERE sh.SummaryHeaderID IS NULL
	GROUP BY us.DateID,us.CompanyID,us.AccountID,us.CompanyGatewayID,us.Trunk,us.AreaPrefix;
	
	DELETE us FROM tblUsageSummaryLive us 
	INNER JOIN tblSummaryHeader sh ON us.SummaryHeaderID = sh.SummaryHeaderID
	INNER JOIN tblDimDate d ON d.DateID = sh.DateID
	WHERE sh.CompanyID = p_CompanyID;
	
	DELETE usd FROM tblUsageSummaryDetailLive usd
	INNER JOIN tblSummaryHeader sh ON usd.SummaryHeaderID = sh.SummaryHeaderID
	INNER JOIN tblDimDate d ON d.DateID = sh.DateID
	WHERE sh.CompanyID = p_CompanyID;
	
	INSERT INTO tblUsageSummaryLive (SummaryHeaderID,TotalCharges,TotalBilledDuration,TotalDuration,NoOfCalls,NoOfFailCalls)
	SELECT ANY_VALUE(sh.SummaryHeaderID),SUM(us.TotalCharges),SUM(us.TotalBilledDuration),SUM(us.TotalDuration),SUM(us.NoOfCalls),SUM(us.NoOfFailCalls)
	FROM tblSummaryHeader sh
	INNER JOIN tmp_UsageSummaryLive us FORCE INDEX (Unique_key)	 
	ON 
		 us.DateID = sh.DateID
	AND us.CompanyID = sh.CompanyID
	AND us.AccountID = sh.AccountID
	AND us.CompanyGatewayID = sh.CompanyGatewayID
	AND us.Trunk = sh.Trunk
	AND us.AreaPrefix = sh.AreaPrefix
	GROUP BY us.DateID,us.CompanyID,us.AccountID,us.CompanyGatewayID,us.Trunk,us.AreaPrefix;
	
	INSERT INTO tblUsageSummaryDetailLive (SummaryHeaderID,TimeID,TotalCharges,TotalBilledDuration,TotalDuration,NoOfCalls,NoOfFailCalls)
	SELECT sh.SummaryHeaderID,TimeID,us.TotalCharges,us.TotalBilledDuration,us.TotalDuration,us.NoOfCalls,us.NoOfFailCalls
	FROM tblSummaryHeader sh
	INNER JOIN tmp_UsageSummaryLive us FORCE INDEX (Unique_key)
	ON 
		 us.DateID = sh.DateID
	AND us.CompanyID = sh.CompanyID
	AND us.AccountID = sh.AccountID
	AND us.CompanyGatewayID = sh.CompanyGatewayID
	AND us.Trunk = sh.Trunk
	AND us.AreaPrefix = sh.AreaPrefix;
END