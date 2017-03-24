CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_generateSummaryLive`(
	IN `p_CompanyID` INT,
	IN `p_StartDate` DATE,
	IN `p_EndDate` DATE
)
BEGIN

	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		-- ERROR
		GET DIAGNOSTICS CONDITION 1
		@p2 = MESSAGE_TEXT;
	
		SELECT @p2 as Message;
		ROLLBACK;
	END;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	CALL fnGetCountry();
	CALL fngetDefaultCodes(p_CompanyID); 
	CALL fnGetUsageForSummaryLive(p_CompanyID, p_StartDate, p_EndDate);
	 
 	/* insert into success summary*/
 	DELETE FROM tmp_UsageSummaryLive WHERE CompanyID = p_CompanyID;
	INSERT INTO tmp_UsageSummaryLive(DateID,TimeID,CompanyID,CompanyGatewayID,ServiceID,GatewayAccountID,AccountID,Trunk,AreaPrefix,TotalCharges,TotalBilledDuration,TotalDuration,NoOfCalls,NoOfFailCalls)
	SELECT 
		d.DateID,
		t.TimeID,
		ud.CompanyID,
		ud.CompanyGatewayID,
		ud.ServiceID,
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
	GROUP BY d.DateID,t.TimeID,ud.area_prefix,ud.trunk,ud.AccountID,ud.CompanyGatewayID,ud.ServiceID,ud.CompanyID;

	UPDATE tmp_UsageSummaryLive 
	INNER JOIN  tmp_codes_ as code ON AreaPrefix = code.code
	SET tmp_UsageSummaryLive.CountryID =code.CountryID
	WHERE tmp_UsageSummaryLive.CompanyID = p_CompanyID AND code.CountryID > 0;

	UPDATE tmp_UsageSummaryLive
	INNER JOIN (SELECT DISTINCT AreaPrefix,tblCountry.CountryID FROM tmp_UsageSummaryLive 	INNER JOIN  temptblCountry AS tblCountry ON AreaPrefix LIKE CONCAT(Prefix , "%")) TBL
	ON tmp_UsageSummaryLive.AreaPrefix = TBL.AreaPrefix
	SET tmp_UsageSummaryLive.CountryID =TBL.CountryID 
	WHERE tmp_UsageSummaryLive.CompanyID = p_CompanyID AND tmp_UsageSummaryLive.CountryID IS NULL ;

	DELETE FROM tmp_SummaryHeaderLive WHERE CompanyID = p_CompanyID;

	INSERT INTO tmp_SummaryHeaderLive (SummaryHeaderID,DateID,CompanyID,AccountID,GatewayAccountID,CompanyGatewayID,ServiceID,Trunk,AreaPrefix,CountryID,created_at)
	SELECT 
		sh.SummaryHeaderID,
		sh.DateID,
		sh.CompanyID,
		sh.AccountID,
		sh.GatewayAccountID,
		sh.CompanyGatewayID,
		sh.ServiceID,
		sh.Trunk,
		sh.AreaPrefix,
		sh.CountryID,
		sh.created_at 
	FROM tblSummaryHeader sh
	INNER JOIN (SELECT DISTINCT DateID,CompanyID FROM tmp_UsageSummaryLive)TBL
	ON TBL.DateID = sh.DateID AND TBL.CompanyID = sh.CompanyID
	WHERE sh.CompanyID =  p_CompanyID ;

	START TRANSACTION;

	INSERT INTO tblSummaryHeader (DateID,CompanyID,AccountID,GatewayAccountID,CompanyGatewayID,ServiceID,Trunk,AreaPrefix,CountryID,created_at)
	SELECT us.DateID,us.CompanyID,us.AccountID,ANY_VALUE(us.GatewayAccountID),us.CompanyGatewayID,us.ServiceID,us.Trunk,us.AreaPrefix,ANY_VALUE(us.CountryID),now() 
	FROM tmp_UsageSummaryLive us
	LEFT JOIN tmp_SummaryHeaderLive sh	 
	ON 
		 us.DateID = sh.DateID
	AND us.CompanyID = sh.CompanyID
	AND us.AccountID = sh.AccountID
	AND us.CompanyGatewayID = sh.CompanyGatewayID
	AND us.Trunk = sh.Trunk
	AND us.AreaPrefix = sh.AreaPrefix
	AND us.ServiceID = sh.ServiceID
	WHERE sh.SummaryHeaderID IS NULL
	GROUP BY us.DateID,us.CompanyID,us.AccountID,us.CompanyGatewayID,us.ServiceID,us.Trunk,us.AreaPrefix;

	DELETE FROM tmp_SummaryHeaderLive WHERE CompanyID = p_CompanyID;

	INSERT INTO tmp_SummaryHeaderLive (SummaryHeaderID,DateID,CompanyID,AccountID,GatewayAccountID,CompanyGatewayID,ServiceID,Trunk,AreaPrefix,CountryID,created_at)
	SELECT 
		sh.SummaryHeaderID,
		sh.DateID,
		sh.CompanyID,
		sh.AccountID,
		sh.GatewayAccountID,
		sh.CompanyGatewayID,
		sh.ServiceID,
		sh.Trunk,
		sh.AreaPrefix,
		sh.CountryID,
		sh.created_at 
	FROM tblSummaryHeader sh
	INNER JOIN (SELECT DISTINCT DateID,CompanyID FROM tmp_UsageSummaryLive)TBL
	ON TBL.DateID = sh.DateID AND TBL.CompanyID = sh.CompanyID
	WHERE sh.CompanyID =  p_CompanyID ;

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
	FROM tmp_SummaryHeaderLive sh
	INNER JOIN tmp_UsageSummaryLive us FORCE INDEX (Unique_key)
	ON 
		 us.DateID = sh.DateID
	AND us.CompanyID = sh.CompanyID
	AND us.AccountID = sh.AccountID
	AND us.CompanyGatewayID = sh.CompanyGatewayID
	AND us.Trunk = sh.Trunk
	AND us.AreaPrefix = sh.AreaPrefix
	AND us.ServiceID = sh.ServiceID
	GROUP BY us.DateID,us.CompanyID,us.AccountID,us.CompanyGatewayID,us.ServiceID,us.Trunk,us.AreaPrefix; 
	
	INSERT INTO tblUsageSummaryDetailLive (SummaryHeaderID,TimeID,TotalCharges,TotalBilledDuration,TotalDuration,NoOfCalls,NoOfFailCalls)
	SELECT sh.SummaryHeaderID,TimeID,us.TotalCharges,us.TotalBilledDuration,us.TotalDuration,us.NoOfCalls,us.NoOfFailCalls
	FROM tmp_SummaryHeaderLive sh
	INNER JOIN tmp_UsageSummaryLive us FORCE INDEX (Unique_key)
	ON 
		 us.DateID = sh.DateID
	AND us.CompanyID = sh.CompanyID
	AND us.AccountID = sh.AccountID
	AND us.CompanyGatewayID = sh.CompanyGatewayID
	AND us.Trunk = sh.Trunk
	AND us.AreaPrefix = sh.AreaPrefix
	AND us.ServiceID = sh.ServiceID;	

	COMMIT;
	
END