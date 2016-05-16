CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_generateSummary`(IN `p_CompanyID` INT, IN `p_StartDate` DATE, IN `p_EndDate` DATE)
BEGIN
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	CALL fnGetCountry(); 
	CALL fnGetUsageForSummary(p_CompanyID,p_StartDate,p_EndDate);
	/* used for success call summary*/
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
		
	);
	/* used for fail call summary*/
	DROP TEMPORARY TABLE IF EXISTS tmp_UsageFailSummary;
	CREATE TEMPORARY TABLE `tmp_UsageFailSummary` (
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
		
	);	 

	/* insert into success summary*/
	INSERT INTO tmp_UsageSummary(DateID,TimeID,CompanyID,CompanyGatewayID,GatewayAccountID,AccountID,Trunk,AreaPrefix,TotalCharges,TotalBilledDuration,TotalDuration,NoOfCalls)
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
		COUNT(ud.UsageDetailID) AS  NoOfCalls
	FROM tmp_tblUsageDetailsReport_ ud  
	INNER JOIN tblDimTime t ON t.fulltime = CONCAT(DATE_FORMAT(ud.connect_time,'%H'),':00:00')
	INNER JOIN tblDimDate d ON d.date = DATE_FORMAT(ud.connect_time,'%Y-%m-%d')
	GROUP BY d.DateID,t.TimeID,ud.area_prefix,ud.trunk,ud.AccountID,ud.CompanyGatewayID,ud.CompanyID;
	
	/* insert into fail summary*/	
	INSERT INTO tmp_UsageFailSummary(DateID,TimeID,CompanyID,CompanyGatewayID,GatewayAccountID,AccountID,Trunk,AreaPrefix,TotalCharges,TotalBilledDuration,TotalDuration,NoOfFailCalls)
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
		COUNT(ud.UsageDetailFailedCallID) AS  NoOfFailCalls
	FROM tmp_tblUsageDetailFail_ ud  
	INNER JOIN tblDimTime t ON t.fulltime = CONCAT(DATE_FORMAT(ud.connect_time,'%H'),':00:00')
	INNER JOIN tblDimDate d ON d.date = DATE_FORMAT(ud.connect_time,'%Y-%m-%d')
	GROUP BY d.DateID,t.TimeID,ud.area_prefix,ud.trunk,ud.AccountID,ud.CompanyGatewayID,ud.CompanyID;
	
	/* update failcalls which call are in both table */
	UPDATE tmp_UsageSummary us FORCE INDEX (Unique_key)
	INNER JOIN  tmp_UsageFailSummary ufs FORCE INDEX (Unique_key) ON 
		 us.DateID = ufs.DateID 
	AND us.CompanyID = ufs.CompanyID
	AND us.TimeID = ufs.TimeID
	AND us.AccountID = ufs.AccountID 
	AND us.CompanyGatewayID = ufs.CompanyGatewayID 
	AND us.Trunk = ufs.Trunk
	AND us.AreaPrefix = ufs.AreaPrefix
	SET us.NoOfFailCalls = ufs.NoOfFailCalls;
	
	
	DROP TEMPORARY TABLE IF EXISTS tmp_UsageSummary2;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_UsageSummary2 as (select * from tmp_UsageSummary);
	
	/* Insert failscalls which call are only in  fail table */
	INSERT INTO tmp_UsageSummary(DateID,TimeID,CompanyID,CompanyGatewayID,GatewayAccountID,AccountID,Trunk,AreaPrefix,TotalCharges,TotalBilledDuration,TotalDuration,NoOfFailCalls)
	SELECT ufs.DateID,ufs.TimeID,ufs.CompanyID,ufs.CompanyGatewayID,ufs.GatewayAccountID,ufs.AccountID,ufs.Trunk,ufs.AreaPrefix,ufs.TotalCharges,ufs.TotalBilledDuration,ufs.TotalDuration,ufs.NoOfFailCalls
	FROM tmp_UsageFailSummary ufs FORCE INDEX (Unique_key)
	LEFT JOIN  tmp_UsageSummary2 us ON 
		 us.DateID = ufs.DateID 
	AND us.CompanyID = ufs.CompanyID
	AND us.TimeID = ufs.TimeID
	AND us.AccountID = ufs.AccountID 
	AND us.CompanyGatewayID = ufs.CompanyGatewayID 
	AND us.Trunk = ufs.Trunk
	AND us.AreaPrefix = ufs.AreaPrefix
	WHERE us.AccountID IS NULL;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageDetailsReport_;
	DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageDetailFail_;
	
	UPDATE tmp_UsageSummary  FORCE INDEX (tmp_UsageSummary_AreaPrefix)
	INNER JOIN  temptblCountry as tblCountry ON AreaPrefix LIKE CONCAT(Prefix , "%")
	SET tmp_UsageSummary.CountryID =tblCountry.CountryID;
	
	INSERT INTO tblSummaryHeader (DateID,CompanyID,AccountID,GatewayAccountID,CompanyGatewayID,Trunk,AreaPrefix,CountryID,created_at)
	SELECT us.DateID,us.CompanyID,us.AccountID,ANY_VALUE(us.GatewayAccountID),us.CompanyGatewayID,us.Trunk,us.AreaPrefix,ANY_VALUE(us.CountryID),now() 
	FROM tmp_UsageSummary us
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
	
	
	DELETE us FROM tblUsageSummary us 
	INNER JOIN tblSummaryHeader sh ON us.SummaryHeaderID = sh.SummaryHeaderID
	INNER JOIN tblDimDate d ON d.DateID = sh.DateID
	WHERE date BETWEEN p_StartDate AND p_EndDate;
	
	DELETE usd FROM tblUsageSummaryDetail usd
	INNER JOIN tblSummaryHeader sh ON usd.SummaryHeaderID = sh.SummaryHeaderID
	INNER JOIN tblDimDate d ON d.DateID = sh.DateID
	WHERE date BETWEEN p_StartDate AND p_EndDate;
	
	
	INSERT INTO tblUsageSummary (SummaryHeaderID,TotalCharges,TotalBilledDuration,TotalDuration,NoOfCalls,NoOfFailCalls)
	SELECT ANY_VALUE(sh.SummaryHeaderID),SUM(us.TotalCharges),SUM(us.TotalBilledDuration),SUM(us.TotalDuration),SUM(us.NoOfCalls),SUM(us.NoOfFailCalls)
	FROM tblSummaryHeader sh
	INNER JOIN tmp_UsageSummary us FORCE INDEX (Unique_key)	 
	ON 
		 us.DateID = sh.DateID
	AND us.CompanyID = sh.CompanyID
	AND us.AccountID = sh.AccountID
	AND us.CompanyGatewayID = sh.CompanyGatewayID
	AND us.Trunk = sh.Trunk
	AND us.AreaPrefix = sh.AreaPrefix
	GROUP BY us.DateID,us.CompanyID,us.AccountID,us.CompanyGatewayID,us.Trunk,us.AreaPrefix;
	
	INSERT INTO tblUsageSummaryDetail (SummaryHeaderID,TimeID,TotalCharges,TotalBilledDuration,TotalDuration,NoOfCalls,NoOfFailCalls)
	SELECT sh.SummaryHeaderID,TimeID,us.TotalCharges,us.TotalBilledDuration,us.TotalDuration,us.NoOfCalls,us.NoOfFailCalls
	FROM tblSummaryHeader sh
	INNER JOIN tmp_UsageSummary us FORCE INDEX (Unique_key)
	ON 
		 us.DateID = sh.DateID
	AND us.CompanyID = sh.CompanyID
	AND us.AccountID = sh.AccountID
	AND us.CompanyGatewayID = sh.CompanyGatewayID
	AND us.Trunk = sh.Trunk
	AND us.AreaPrefix = sh.AreaPrefix;

	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END