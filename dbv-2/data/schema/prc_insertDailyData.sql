CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_insertDailyData`(IN `p_ProcessID` VarCHAR(200), IN `p_Offset` INT)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
 
	DROP TEMPORARY TABLE IF EXISTS tmp_DetailSummery_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_DetailSummery_(
			CompanyID int,
			CompanyGatewayID int,
			AccountID int,
			trunk varchar(50),			
			area_prefix varchar(50),			
			pincode varchar(50),
			TotalCharges float,
			TotalDuration int,
			TotalBilledDuration int,
			NoOfCalls int,
			DailyDate datetime
	);
   
    CALL fnUsageDetailbyProcessID(p_ProcessID); 
    
   INSERT INTO tmp_DetailSummery_
	SELECT
		ud.CompanyID,
		ud.CompanyGatewayID,
		ud.AccountID,
		ud.trunk,
		ud.area_prefix,
		ud.pincode,
		SUM(ud.cost) AS TotalCharges,
		SUM(duration) AS TotalDuration,
		SUM(billed_duration) AS TotalBilledDuration,
		COUNT(cld) AS NoOfCalls,
		DATE_FORMAT(connect_time, '%Y-%m-%d') AS DailyDate
	from (select CompanyID,
		CompanyGatewayID,
		AccountID,
		trunk,
		area_prefix,
		pincode,
		cost,
		duration,
		billed_duration,
		cld,
		DATE_ADD(connect_time,INTERVAL p_Offset SECOND) as connect_time
		FROM tmp_tblUsageDetailsProcess_)
		ud
	GROUP BY ud.trunk,
				area_prefix,
				pincode,
				DATE_FORMAT(connect_time, '%Y-%m-%d'),
				ud.AccountID,
				ud.CompanyID,
				ud.CompanyGatewayID;
	INSERT INTO tblUsageDaily (CompanyID,CompanyGatewayID,AccountID,Trunk,AreaPrefix,Pincode,TotalCharges,TotalDuration,TotalBilledDuration,NoOfCalls,DailyDate) 
		SELECT ds.*
		FROM tmp_DetailSummery_ ds LEFT JOIN 
		tblUsageDaily dd  ON 
		dd.CompanyID = ds.CompanyID 
		AND dd.AccountID = ds.AccountID
		AND dd.CompanyGatewayID = ds.CompanyGatewayID
		AND dd.Trunk = ds.trunk
		AND dd.AreaPrefix = ds.area_prefix
		AND dd.DailyDate = ds.DailyDate
		WHERE dd.UsageDailyID IS NULL;

	UPDATE tblUsageDaily  dd
 INNER JOIN 
	tmp_DetailSummery_ ds 
	ON 
	dd.CompanyID = ds.CompanyID 
	AND dd.AccountID = ds.AccountID
	AND dd.CompanyGatewayID = ds.CompanyGatewayID
	AND dd.Trunk = ds.trunk
	AND dd.AreaPrefix = ds.area_prefix
	AND dd.Pincode = ds.pincode
	AND dd.DailyDate = ds.DailyDate
SET dd.TotalCharges =  dd.TotalCharges + ds.TotalCharges,
	dd.TotalDuration = dd.TotalDuration + ds.TotalDuration,
	dd.TotalBilledDuration = dd.TotalBilledDuration +  ds.TotalBilledDuration,
	dd.NoOfCalls = dd.NoOfCalls + ds.NoOfCalls
	WHERE (
		ds.TotalDuration != dd.TotalDuration
	OR  ds.TotalBilledDuration != dd.TotalBilledDuration
	OR  ds.NoOfCalls != dd.NoOfCalls);


  SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
	
 
END