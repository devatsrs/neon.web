CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_generateVendorSummaryLive`(IN `p_CompanyID` INT, IN `p_StartDate` DATE, IN `p_EndDate` DATE)
BEGIN
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	CALL fnGetCountry(); 
	CALL fnGetVendorUsageForSummary(p_CompanyID,p_StartDate,p_EndDate);
	/* used for success call summary*/
	DROP TEMPORARY TABLE IF EXISTS tmp_VendorUsageSummary;
	CREATE TEMPORARY TABLE `tmp_VendorUsageSummary` (
		`DateID` BIGINT(20) NOT NULL,
		`TimeID` INT(11) NOT NULL,
		`CompanyID` INT(11) NOT NULL,
		`CompanyGatewayID` INT(11) NOT NULL,
		`GatewayAccountID` VARCHAR(100) NULL DEFAULT NULL,
		`AccountID` INT(11) NOT NULL,
		`Trunk` VARCHAR(50) NULL DEFAULT NULL ,
		`AreaPrefix` VARCHAR(100) NULL DEFAULT NULL ,
		`TotalCharges` DOUBLE NULL DEFAULT NULL,
		`TotalSales` DOUBLE NULL DEFAULT NULL,
		`TotalBilledDuration` INT(11) NULL DEFAULT NULL,
		`TotalDuration` INT(11) NULL DEFAULT NULL,
		`NoOfCalls` INT(11) NULL DEFAULT '0',
		`NoOfFailCalls` INT(11) NULL DEFAULT '0',
		`FinalStatus` INT(11) NULL DEFAULT '0',
		`CountryID` INT(11) NULL DEFAULT NULL,
		INDEX `tmp_VendorUsageSummary_dim_date` (`DateID`),
		INDEX `tmp_VendorUsageSummary_AreaPrefix` (`AreaPrefix`),
		INDEX `Unique_key` (`DateID`, `CompanyID`, `AccountID`, `GatewayAccountID`, `CompanyGatewayID`, `Trunk`, `AreaPrefix`)
		
	);
 	/* insert into success summary*/
	INSERT INTO tmp_VendorUsageSummary(DateID,TimeID,CompanyID,CompanyGatewayID,GatewayAccountID,AccountID,Trunk,AreaPrefix,TotalCharges,TotalSales,TotalBilledDuration,TotalDuration,NoOfCalls,NoOfFailCalls)
	SELECT 
		d.DateID,
		t.TimeID,
		ud.CompanyID,
		ud.CompanyGatewayID,
		ANY_VALUE(ud.GatewayAccountID),
		ud.AccountID,
		ud.trunk,
		ud.area_prefix,
		COALESCE(SUM(ud.buying_cost),0)  AS TotalCharges ,
		COALESCE(SUM(ud.selling_cost),0)  AS TotalSales ,
		COALESCE(SUM(ud.billed_duration),0) AS TotalBilledDuration ,
		COALESCE(SUM(ud.duration),0) AS TotalDuration,
		SUM(IF(ud.call_status=1,1,0)) AS  NoOfCalls,
		SUM(IF(ud.call_status=2,1,0)) AS  NoOfFailCalls
	FROM tmp_tblVendorUsageDetailsReport_ ud  
	INNER JOIN tblDimTime t ON t.fulltime = connect_time
	INNER JOIN tblDimDate d ON d.date = connect_date
	GROUP BY d.DateID,t.TimeID,ud.area_prefix,ud.trunk,ud.AccountID,ud.CompanyGatewayID,ud.CompanyID;

	DROP TEMPORARY TABLE IF EXISTS tmp_tblVendorUsageDetailsReport_;
	
	UPDATE tmp_VendorUsageSummary  FORCE INDEX (tmp_VendorUsageSummary_AreaPrefix)
	INNER JOIN  temptblCountry as tblCountry ON AreaPrefix LIKE CONCAT(Prefix , "%")
	SET tmp_VendorUsageSummary.CountryID =tblCountry.CountryID;
	
	INSERT INTO tblSummaryVendorHeader (DateID,CompanyID,AccountID,GatewayAccountID,CompanyGatewayID,Trunk,AreaPrefix,CountryID,created_at)
	SELECT us.DateID,us.CompanyID,us.AccountID,ANY_VALUE(us.GatewayAccountID),us.CompanyGatewayID,us.Trunk,us.AreaPrefix,ANY_VALUE(us.CountryID),now() 
	FROM tmp_VendorUsageSummary us
	LEFT JOIN tblSummaryVendorHeader sh	 
	ON 
		 us.DateID = sh.DateID
	AND us.CompanyID = sh.CompanyID
	AND us.AccountID = sh.AccountID
	AND us.CompanyGatewayID = sh.CompanyGatewayID
	AND us.Trunk = sh.Trunk
	AND us.AreaPrefix = sh.AreaPrefix
	WHERE sh.SummaryVendorHeaderID IS NULL
	GROUP BY us.DateID,us.CompanyID,us.AccountID,us.CompanyGatewayID,us.Trunk,us.AreaPrefix;
	
	
	DELETE us FROM tblUsageVendorSummary us 
	INNER JOIN tblSummaryVendorHeader sh ON us.SummaryVendorHeaderID = sh.SummaryVendorHeaderID
	INNER JOIN tblDimDate d ON d.DateID = sh.DateID
	WHERE date BETWEEN p_StartDate AND p_EndDate;
	
	DELETE FROM tblUsageVendorSummaryLive;
	DELETE FROM tblUsageVendorSummaryDetailLive;
	
	INSERT INTO tblUsageVendorSummaryLive (SummaryVendorHeaderID,TotalCharges,TotalSales,TotalBilledDuration,TotalDuration,NoOfCalls,NoOfFailCalls)
	SELECT ANY_VALUE(sh.SummaryVendorHeaderID),SUM(us.TotalCharges),SUM(us.TotalSales),SUM(us.TotalBilledDuration),SUM(us.TotalDuration),SUM(us.NoOfCalls),SUM(us.NoOfFailCalls)
	FROM tblSummaryVendorHeader sh
	INNER JOIN tmp_VendorUsageSummary us FORCE INDEX (Unique_key)	 
	ON 
		 us.DateID = sh.DateID
	AND us.CompanyID = sh.CompanyID
	AND us.AccountID = sh.AccountID
	AND us.CompanyGatewayID = sh.CompanyGatewayID
	AND us.Trunk = sh.Trunk
	AND us.AreaPrefix = sh.AreaPrefix
	GROUP BY us.DateID,us.CompanyID,us.AccountID,us.CompanyGatewayID,us.Trunk,us.AreaPrefix;
	
	INSERT INTO tblUsageVendorSummaryDetailLive (SummaryVendorHeaderID,TimeID,TotalCharges,TotalSales,TotalBilledDuration,TotalDuration,NoOfCalls,NoOfFailCalls)
	SELECT sh.SummaryVendorHeaderID,TimeID,us.TotalCharges,us.TotalSales,us.TotalBilledDuration,us.TotalDuration,us.NoOfCalls,us.NoOfFailCalls
	FROM tblSummaryVendorHeader sh
	INNER JOIN tmp_VendorUsageSummary us FORCE INDEX (Unique_key)
	ON 
		 us.DateID = sh.DateID
	AND us.CompanyID = sh.CompanyID
	AND us.AccountID = sh.AccountID
	AND us.CompanyGatewayID = sh.CompanyGatewayID
	AND us.Trunk = sh.Trunk
	AND us.AreaPrefix = sh.AreaPrefix;

	
END