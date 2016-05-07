CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_generateSummary`(IN `p_CompanyID` INT, IN `p_StartDate` DATE, IN `p_EndDate` DATE)
BEGIN
	
	DECLARE v_StartTimeId_ INT ;
	DECLARE v_EndTimeId_ INT ;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT date_id INTO v_StartTimeId_ FROM tblDimDate WHERE date = p_StartDate  LIMIT 1;
	SELECT date_id INTO v_EndTimeId_ FROM tblDimDate WHERE date = p_EndDate  LIMIT 1;
   CALL fnGetCountry(); 
	CALL fnGetUsageForSummary(p_CompanyID,p_StartDate,p_EndDate);
	
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
		`ACD` INT(11) NULL DEFAULT NULL,
		`ASR` INT(11) NULL DEFAULT NULL,
		`FinalStatus` INT(11) NULL DEFAULT '0'
	);


	INSERT INTO tmp_UsageSummary(date_id,time_id,CompanyID,CompanyGatewayID,GatewayAccountID,AccountID,Trunk,AreaPrefix,TotalCharges,TotalBilledDuration,TotalDuration,NoOfCalls,ACD)
	SELECT 
		ANY_VALUE(d.date_id),
		ANY_VALUE(t.time_id),
		ud.CompanyID,
		ud.CompanyGatewayID,
		ud.GatewayAccountID,
		ud.AccountID,
		ud.trunk,
		ud.area_prefix,
		SUM(ud.cost)  AS TotalCharges ,
		SUM(ud.billed_duration) AS TotalBilledDuration ,
		SUM(ud.duration) AS TotalDuration,
		COUNT(ud.UsageDetailID) AS  NoOfCalls,
		(SUM(ud.billed_duration)/COUNT(ud.UsageDetailID)) AS ACD
	FROM tmp_tblUsageDetails_ ud  
	INNER JOIN tblDimTime t ON t.fulltime = CONCAT(DATE_FORMAT(ud.connect_time,'%H'),':00:00')
	INNER JOIN tblDimDate d ON d.date = DATE_FORMAT(ud.connect_time,'%Y-%m-%d')
	GROUP BY YEAR(ud.connect_time),MONTH(ud.connect_time),DAY(ud.connect_time),HOUR(ud.connect_time),ud.area_prefix,ud.trunk,ud.AccountID,ud.GatewayAccountID,ud.CompanyGatewayID,ud.CompanyID;
	
	
	DELETE FROM tblUsageSummary  WHERE date_id BETWEEN v_StartTimeId_ AND v_EndTimeId_;
	
	INSERT INTO tblUsageSummary(date_id,time_id,CompanyID,CompanyGatewayID,GatewayAccountID,AccountID,Trunk,AreaPrefix,TotalCharges,TotalBilledDuration,TotalDuration,NoOfCalls,ACD,ASR,FinalStatus)
	SELECT us.* FROM tmp_UsageSummary us ;
	
	UPDATE tblUsageSummary 
	INNER JOIN  temptblCountry as tblCountry ON AreaPrefix LIKE CONCAT(Prefix , "%")
	SET tblUsageSummary.CountryID =tblCountry.CountryID
	WHERE date_id BETWEEN v_StartTimeId_ AND v_EndTimeId_;

	
	-- DELETE FROM tblUsageSummary WHERE date_id BETWEEN v_StartTimeId_ AND v_EndTimeId_ AND TotalCharges = 0 AND TotalBilledDuration =0 AND TotalDuration = 0 AND NoOfCalls = 0 ;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END