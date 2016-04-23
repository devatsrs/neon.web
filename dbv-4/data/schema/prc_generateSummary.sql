CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_generateSummary`(IN `p_CompanyID` INT, IN `p_StartDate` DATE, IN `p_EndDate` DATE)
BEGIN
	
	DECLARE v_StartTimeId_ INT ;
	DECLARE v_EndTimeId_ INT ;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT date_id INTO v_StartTimeId_ FROM tblDimDate WHERE date = p_StartDate  LIMIT 1;
	SELECT date_id INTO v_EndTimeId_ FROM tblDimDate WHERE date = p_EndDate  LIMIT 1;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_UsageSummary;
	CREATE TEMPORARY TABLE `tmp_UsageSummary` (
		`date_id` BIGINT(20) NOT NULL,
		`time_id` INT(11) NOT NULL,
		`CompanyID` INT(11) NOT NULL,
		`CompanyGatewayID` INT(11) NOT NULL,
		`GatewayAccountID` VARCHAR(100) NULL DEFAULT NULL,
		`AccountID` INT(11) NOT NULL,
		`Trunk` VARCHAR(50) NULL DEFAULT NULL ,
		`AreaPrefix` VARCHAR(100) NULL DEFAULT NULL ,
		`TotalCharges` DOUBLE NULL DEFAULT NULL,
		`TotalBilledDuration` INT(11) NULL DEFAULT NULL,
		`TotalDuration` INT(11) NULL DEFAULT NULL,
		`NoOfCalls` INT(11) NULL DEFAULT NULL,
		`FinalStatus` INT(11) NULL DEFAULT '0'
	);


	INSERT INTO tmp_UsageSummary(date_id,time_id,CompanyID,CompanyGatewayID,GatewayAccountID,AccountID,Trunk,AreaPrefix,TotalCharges,TotalBilledDuration,TotalDuration,NoOfCalls)
	SELECT 
		ANY_VALUE(d.date_id),
		ANY_VALUE(t.time_id),
		uh.CompanyID,
		uh.CompanyGatewayID,
		uh.GatewayAccountID,
		uh.AccountID,
		ud.trunk,
		ud.area_prefix,
		SUM(cost)  AS TotalCharges ,
		SUM(ud.billed_duration) AS TotalBilledDuration ,
		SUM(ud.duration) AS TotalDuration,
		COUNT(ud.UsageDetailID) AS  NoOfCalls
	FROM LocalRMCdr.tblUsageDetails ud
	INNER JOIN LocalRMCdr.tblUsageHeader uh ON uh.UsageHeaderID = ud.UsageHeaderID
	INNER JOIN tblDimTtime t ON t.fulltime = CONCAT(DATE_FORMAT(ud.connect_time,'%H'),':00:00')
	INNER JOIN tblDimDate d ON d.date = DATE_FORMAT(ud.connect_time,'%Y-%m-%d')
	WHERE uh.CompanyID = p_CompanyID AND  uh.StartDate BETWEEN p_StartDate AND p_EndDate  AND uh.AccountID IS NOT NULL
	GROUP BY YEAR(ud.connect_time),MONTH(ud.connect_time),DAY(ud.connect_time),HOUR(ud.connect_time),ud.area_prefix,ud.trunk,uh.AccountID,uh.GatewayAccountID,uh.CompanyGatewayID,uh.CompanyID;
	
	
	UPDATE tblUsageSummary set TotalCharges = 0 , TotalBilledDuration =0 , TotalDuration = 0 , NoOfCalls = 0 WHERE date_id BETWEEN v_StartTimeId_ AND v_EndTimeId_;
	
	INSERT INTO tblUsageSummary(date_id,time_id,CompanyID,CompanyGatewayID,GatewayAccountID,AccountID,Trunk,AreaPrefix,TotalCharges,TotalBilledDuration,TotalDuration,NoOfCalls,FinalStatus)
	SELECT us.*
		FROM tmp_UsageSummary us LEFT JOIN 
		tblUsageSummary usa  ON 
		    usa.CompanyID = us.CompanyID 
		AND usa.CompanyGatewayID = us.CompanyGatewayID
		AND usa.GatewayAccountID = us.GatewayAccountID
		AND usa.AccountID = us.AccountID
		AND usa.Trunk = us.Trunk
		AND usa.AreaPrefix = us.AreaPrefix
		AND usa.date_id   = us.date_id
		AND usa.time_id   = us.time_id
	WHERE usa.UsageSummaryID IS NULL;

	UPDATE tblUsageSummary  usa
	INNER JOIN 
		tmp_UsageSummary us 
	ON 
		    usa.CompanyID = us.CompanyID 
		AND usa.CompanyGatewayID = us.CompanyGatewayID
		AND usa.GatewayAccountID = us.GatewayAccountID
		AND usa.AccountID = us.AccountID
		AND usa.Trunk = us.Trunk
		AND usa.AreaPrefix = us.AreaPrefix
		AND usa.date_id   = us.date_id
		AND usa.time_id   = us.time_id
	SET usa.TotalCharges =  us.TotalCharges,
		 usa.TotalDuration = us.TotalDuration,
		 usa.TotalBilledDuration = us.TotalBilledDuration,
		 usa.NoOfCalls = us.NoOfCalls;
	
	
	DELETE FROM tblUsageSummary WHERE date_id BETWEEN v_StartTimeId_ AND v_EndTimeId_ AND TotalCharges = 0 AND TotalBilledDuration =0 AND TotalDuration = 0 AND NoOfCalls = 0 ;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END