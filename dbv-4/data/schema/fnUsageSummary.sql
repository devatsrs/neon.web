CREATE DEFINER=`root`@`localhost` PROCEDURE `fnUsageSummary`(IN `p_CompanyID` int , IN `p_CompanyGatewayID` int , IN `p_AccountID` int , IN `p_StartDate` datetime , IN `p_EndDate` datetime , IN `p_AreaPrefix` VARCHAR(50), IN `p_Trunk` VARCHAR(50), IN `p_CountryID` INT, IN `p_UserID` INT , IN `p_isAdmin` INT)
BEGIN
	
	DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageSummary_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageSummary_(
			`date_id` BIGINT(20) NOT NULL,
			`time_id` INT(11) NOT NULL,
			`CompanyID` INT(11) NOT NULL,
			`AccountID` INT(11) NOT NULL,
			`GatewayAccountID` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
			`CompanyGatewayID` INT(11) NOT NULL,
			`Trunk` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
			`AreaPrefix` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
			`CountryID` INT(11) NULL DEFAULT NULL,
			`TotalCharges` DOUBLE NULL DEFAULT NULL,
			`TotalBilledDuration` INT(11) NULL DEFAULT NULL,
			`TotalDuration` INT(11) NULL DEFAULT NULL,
			`NoOfCalls` INT(11) NULL DEFAULT NULL,
			`ACD` INT(11) NULL DEFAULT NULL,
			`ASR` INT(11) NULL DEFAULT NULL,
			`AccountName` varchar(100)
	);
	INSERT INTO tmp_tblUsageSummary_
	SELECT
		us.date_id,
		us.time_id,
		us.CompanyID,
		us.AccountID,
		us.GatewayAccountID,
		us.CompanyGatewayID,
		us.Trunk,
		us.AreaPrefix,
		us.CountryID,
		us.TotalCharges,
		us.TotalBilledDuration,
		us.TotalDuration,
		us.NoOfCalls,
		us.ACD,
		us.ASR,
		a.AccountName
	FROM tblUsageSummary  us
	INNER JOIN tblDimDate dd
		ON dd.date_id = us.date_id 
	INNER JOIN LocalRatemanagement.tblAccount a
		ON us.AccountID = a.AccountID
	WHERE dd.date BETWEEN p_StartDate AND p_EndDate
	AND us.CompanyID = p_CompanyID
	AND (p_AccountID = 0 OR us.AccountID = p_AccountID)
	AND (p_CompanyGatewayID = 0 OR CompanyGatewayID = p_CompanyGatewayID)
	AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
	AND (p_Trunk = '' OR us.Trunk LIKE REPLACE(p_Trunk, '*', '%'))
	AND (p_AreaPrefix = '' OR us.AreaPrefix LIKE REPLACE(p_AreaPrefix, '*', '%') );
	-- AND (p_CountryID = 0 OR us.CountryID = p_CountryID)
	
	IF p_EndDate = DATE(NOW())
	THEN
		CALL fnGetUsageForSummary(1,DATE(NOW()),DATE(NOW()));
		INSERT INTO tmp_tblUsageSummary_
			SELECT 
			ANY_VALUE(d.date_id),
			ANY_VALUE(t.time_id),
			ud.CompanyID,
			ud.AccountID,
			ud.CompanyGatewayID,
			ud.GatewayAccountID,
			ud.trunk,
			ud.area_prefix,
			NULL as CountryID,
			SUM(ud.cost)  AS TotalCharges ,
			SUM(ud.billed_duration) AS TotalBilledDuration ,
			SUM(ud.duration) AS TotalDuration,
			COUNT(ud.UsageDetailID) AS  NoOfCalls,
			(SUM(ud.billed_duration)/COUNT(ud.UsageDetailID)) AS ACD,
			NULL ASR,
			a.AccountName
		FROM tmp_tblUsageDetails_ ud  
		INNER JOIN tblDimTtime t ON t.fulltime = CONCAT(DATE_FORMAT(ud.connect_time,'%H'),':00:00')
		INNER JOIN tblDimDate d ON d.date = DATE_FORMAT(ud.connect_time,'%Y-%m-%d')
		INNER JOIN LocalRatemanagement.tblAccount a ON ud.AccountID = a.AccountID
		GROUP BY YEAR(ud.connect_time),MONTH(ud.connect_time),DAY(ud.connect_time),HOUR(ud.connect_time),ud.area_prefix,ud.trunk,ud.AccountID,ud.GatewayAccountID,ud.CompanyGatewayID,ud.CompanyID;
	
	
	END IF;
		
END