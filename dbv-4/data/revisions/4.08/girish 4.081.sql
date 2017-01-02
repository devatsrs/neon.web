USE `NeonReportDev`;

-- Dumping structure for procedure NeonReportDev.fnUsageSummaryDetail
DROP PROCEDURE IF EXISTS `fnUsageSummaryDetail`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `fnUsageSummaryDetail`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` TEXT,
	IN `p_AccountID` TEXT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` TEXT,
	IN `p_Trunk` TEXT,
	IN `p_CountryID` TEXT,
	IN `p_UserID` INT ,
	IN `p_isAdmin` INT


)
BEGIN
	
	DECLARE i INTEGER;

	DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageSummary_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageSummary_(
			`DateID` BIGINT(20) NOT NULL,
			`TimeID` INT(11) NOT NULL,
			`Time` VARCHAR(50) NOT NULL,
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
			`NoOfFailCalls` INT(11) NULL DEFAULT NULL,
			`AccountName` varchar(100),
			INDEX `tblUsageSummary_dim_date` (`DateID`)
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_AreaPrefix_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_AreaPrefix_ (
		`Code` Text NULL DEFAULT NULL
	);
    
	SET i = 1;
	REPEAT
		INSERT INTO tmp_AreaPrefix_ ( Code)
		SELECT NeonRMDev.FnStringSplit(p_AreaPrefix, ',', i) FROM tblDimDate WHERE NeonRMDev.FnStringSplit(p_AreaPrefix, ',', i) IS NOT NULL LIMIT 1;
		SET i = i + 1;
		UNTIL ROW_COUNT() = 0
	END REPEAT;
		
	INSERT INTO tmp_tblUsageSummary_
	SELECT
		sh.DateID,
		dt.TimeID,
		CONCAT(dd.date,' ',dt.fulltime),
		sh.CompanyID,
		sh.AccountID,
		sh.GatewayAccountID,
		sh.CompanyGatewayID,
		sh.Trunk,
		sh.AreaPrefix,
		sh.CountryID,
		usd.TotalCharges,
		usd.TotalBilledDuration,
		usd.TotalDuration,
		usd.NoOfCalls,
		usd.NoOfFailCalls,
		a.AccountName
	FROM tblSummaryHeader sh
	INNER JOIN tblUsageSummaryDetail usd
		ON usd.SummaryHeaderID = sh.SummaryHeaderID 
	INNER JOIN tblDimDate dd
		ON dd.DateID = sh.DateID
	INNER JOIN tblDimTime dt
		ON dt.TimeID = usd.TimeID
	INNER JOIN NeonRMDev.tblAccount a
		ON sh.AccountID = a.AccountID
	LEFT JOIN NeonRMDev.tblTrunk t
		ON t.Trunk = sh.Trunk
	LEFT JOIN tmp_AreaPrefix_ ap 
		ON sh.AreaPrefix LIKE REPLACE(ap.Code, '*', '%')
	WHERE CONCAT(dd.date,' ',dt.fulltime) BETWEEN p_StartDate AND p_EndDate
	AND sh.CompanyID = p_CompanyID
	AND (p_AccountID = '' OR FIND_IN_SET(sh.AccountID,p_AccountID))
	AND (p_CompanyGatewayID = '' OR FIND_IN_SET(sh.CompanyGatewayID,p_CompanyGatewayID))
	AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
	AND (p_Trunk = '' OR FIND_IN_SET(t.TrunkID,p_Trunk))
	AND (p_CountryID = '' OR FIND_IN_SET(sh.CountryID,p_CountryID))
	AND (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
	AND (p_AreaPrefix ='' OR ap.Code IS NOT NULL);
		
	INSERT INTO tmp_tblUsageSummary_
	SELECT
		sh.DateID,
		dt.TimeID,
		dt.fulltime,
		sh.CompanyID,
		sh.AccountID,
		sh.GatewayAccountID,
		sh.CompanyGatewayID,
		sh.Trunk,
		sh.AreaPrefix,
		sh.CountryID,
		usd.TotalCharges,
		usd.TotalBilledDuration,
		usd.TotalDuration,
		usd.NoOfCalls,
		usd.NoOfFailCalls,
		a.AccountName
	FROM tblSummaryHeader sh
	INNER JOIN tblUsageSummaryDetailLive usd
		ON usd.SummaryHeaderID = sh.SummaryHeaderID 
	INNER JOIN tblDimDate dd
		ON dd.DateID = sh.DateID
	INNER JOIN tblDimTime dt
		ON dt.TimeID = usd.TimeID
	INNER JOIN NeonRMDev.tblAccount a
		ON sh.AccountID = a.AccountID
	LEFT JOIN NeonRMDev.tblTrunk t
		ON t.Trunk = sh.Trunk
	LEFT JOIN tmp_AreaPrefix_ ap 
		ON (p_AreaPrefix = '' OR sh.AreaPrefix LIKE REPLACE(ap.Code, '*', '%') )
	WHERE dd.date BETWEEN DATE(p_StartDate) AND DATE(p_EndDate)
	AND CONCAT(dd.date,' ',dt.fulltime) BETWEEN p_StartDate AND p_EndDate
	AND sh.CompanyID = p_CompanyID
	AND (p_AccountID = '' OR FIND_IN_SET(sh.AccountID,p_AccountID))
	AND (p_CompanyGatewayID = '' OR FIND_IN_SET(sh.CompanyGatewayID,p_CompanyGatewayID))
	AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
	AND (p_Trunk = '' OR FIND_IN_SET(t.TrunkID,p_Trunk))
	AND (p_CountryID = '' OR FIND_IN_SET(sh.CountryID,p_CountryID))
	AND (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
	AND (p_AreaPrefix ='' OR ap.Code IS NOT NULL);

END//
DELIMITER ;

-- Dumping structure for procedure NeonReportDev.fnUsageVendorSummaryDetail
DROP PROCEDURE IF EXISTS `fnUsageVendorSummaryDetail`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `fnUsageVendorSummaryDetail`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` TEXT,
	IN `p_AccountID` TEXT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` TEXT,
	IN `p_Trunk` TEXT,
	IN `p_CountryID` TEXT,
	IN `p_UserID` INT,
	IN `p_isAdmin` INT


)
BEGIN
	
	DECLARE i INTEGER;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageVendorSummary_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageVendorSummary_(
			`DateID` BIGINT(20) NOT NULL,
			`TimeID` INT(11) NOT NULL,
			`Time` VARCHAR(50) NOT NULL,
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
			`NoOfFailCalls` INT(11) NULL DEFAULT NULL,
			`AccountName` varchar(100),
			INDEX `tblUsageSummary_dim_date` (`DateID`)
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_AreaPrefix_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_AreaPrefix_ (
		`Code` Text NULL DEFAULT NULL
	);
    
	SET i = 1;
	REPEAT
		INSERT INTO tmp_AreaPrefix_ ( Code)
		SELECT NeonRMDev.FnStringSplit(p_AreaPrefix, ',', i) FROM tblDimDate WHERE NeonRMDev.FnStringSplit(p_AreaPrefix, ',', i) IS NOT NULL LIMIT 1;
		SET i = i + 1;
		UNTIL ROW_COUNT() = 0
	END REPEAT;
	
	INSERT INTO tmp_tblUsageVendorSummary_
	SELECT
		sh.DateID,
		dt.TimeID,
		CONCAT(dd.date,' ',dt.fulltime),
		sh.CompanyID,
		sh.AccountID,
		sh.GatewayAccountID,
		sh.CompanyGatewayID,
		sh.Trunk,
		sh.AreaPrefix,
		sh.CountryID,
		usd.TotalCharges,
		usd.TotalBilledDuration,
		usd.TotalDuration,
		usd.NoOfCalls,
		usd.NoOfFailCalls,
		a.AccountName
	FROM tblSummaryVendorHeader sh
	INNER JOIN tblUsageVendorSummaryDetail usd
		ON usd.SummaryVendorHeaderID = sh.SummaryVendorHeaderID 
	INNER JOIN tblDimDate dd
		ON dd.DateID = sh.DateID
	INNER JOIN tblDimTime dt
		ON dt.TimeID = usd.TimeID
	INNER JOIN NeonRMDev.tblAccount a
		ON sh.AccountID = a.AccountID
	LEFT JOIN NeonRMDev.tblTrunk t
		ON t.Trunk = sh.Trunk
	LEFT JOIN tmp_AreaPrefix_ ap 
		ON sh.AreaPrefix LIKE REPLACE(ap.Code, '*', '%')
	WHERE CONCAT(dd.date,' ',dt.fulltime) BETWEEN p_StartDate AND p_EndDate
	AND sh.CompanyID = p_CompanyID
	AND (p_AccountID = '' OR FIND_IN_SET(sh.AccountID,p_AccountID))
	AND (p_CompanyGatewayID = '' OR FIND_IN_SET(sh.CompanyGatewayID,p_CompanyGatewayID))
	AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
	AND (p_Trunk = '' OR FIND_IN_SET(t.TrunkID,p_Trunk))
	AND (p_CountryID = '' OR FIND_IN_SET(sh.CountryID,p_CountryID))
	AND (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
	AND (p_AreaPrefix ='' OR ap.Code IS NOT NULL);
	
	INSERT INTO tmp_tblUsageVendorSummary_
	SELECT
		sh.DateID,
		dt.TimeID,
		dt.fulltime,
		sh.CompanyID,
		sh.AccountID,
		sh.GatewayAccountID,
		sh.CompanyGatewayID,
		sh.Trunk,
		sh.AreaPrefix,
		sh.CountryID,
		usd.TotalCharges,
		usd.TotalBilledDuration,
		usd.TotalDuration,
		usd.NoOfCalls,
		usd.NoOfFailCalls,
		a.AccountName
	FROM tblSummaryVendorHeader sh
	INNER JOIN tblUsageVendorSummaryDetailLive usd
		ON usd.SummaryVendorHeaderID = sh.SummaryVendorHeaderID 
	INNER JOIN tblDimDate dd
		ON dd.DateID = sh.DateID
	INNER JOIN tblDimTime dt
		ON dt.TimeID = usd.TimeID
	INNER JOIN NeonRMDev.tblAccount a
		ON sh.AccountID = a.AccountID
	LEFT JOIN NeonRMDev.tblTrunk t
		ON t.Trunk = sh.Trunk
	LEFT JOIN tmp_AreaPrefix_ ap 
		ON sh.AreaPrefix LIKE REPLACE(ap.Code, '*', '%')
	WHERE dd.date BETWEEN DATE(p_StartDate) AND DATE(p_EndDate)
	AND CONCAT(dd.date,' ',dt.fulltime) BETWEEN p_StartDate AND p_EndDate
	AND sh.CompanyID = p_CompanyID
	AND (p_AccountID = '' OR FIND_IN_SET(sh.AccountID,p_AccountID))
	AND (p_CompanyGatewayID = '' OR FIND_IN_SET(sh.CompanyGatewayID,p_CompanyGatewayID))
	AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
	AND (p_Trunk = '' OR FIND_IN_SET(t.TrunkID,p_Trunk))
	AND (p_CountryID = '' OR FIND_IN_SET(sh.CountryID,p_CountryID))
	AND (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
	AND (p_AreaPrefix ='' OR ap.Code IS NOT NULL);

END//
DELIMITER ;

-- Dumping structure for procedure NeonReportDev.prc_getReportByTime
DROP PROCEDURE IF EXISTS `prc_getReportByTime`;
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getReportByTime`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_AccountID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_ReportType` INT
)
BEGIN

	DECLARE v_Round_ INT;
	DECLARE V_Detail INT;

	SET V_Detail = 2;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	CALL fnUsageSummary(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,p_UserID,p_isAdmin,V_Detail);

	/* hourly report */
	IF p_ReportType =1
	THEN
	
		SELECT 
			CONCAT(dt.hour,' Hour') as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tblDimTime dt on dt.TimeID = us.TimeID
		GROUP BY  us.DateID,dt.hour;

	END IF;

	/* daily report */
	IF p_ReportType =2
	THEN

		SELECT 
			dd.date as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tblDimDate dd on dd.DateID = us.DateID
		GROUP BY  us.DateID;
	END IF;

	/* weekly report */
	IF p_ReportType =3
	THEN

		SELECT 
			ANY_VALUE(dd.week_year_name) as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tblDimDate dd on dd.DateID = us.DateID
		GROUP BY  dd.week_of_year;

	END IF;

	/* monthly report */
	IF p_ReportType =4
	THEN

		SELECT 
			ANY_VALUE(dd.month_year_name) as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tblDimDate dd on dd.DateID = us.DateID
		GROUP BY  dd.month_of_year;

	END IF;

	/* queterly report */
	IF p_ReportType =5
	THEN

		SELECT 
			CONCAT('Q-',ANY_VALUE(dd.quarter_year_name)) as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tblDimDate dd on dd.DateID = us.DateID
		GROUP BY  dd.quarter_of_year;

	END IF;

	/* yearly report */
	IF p_ReportType =6
	THEN

		SELECT 
			dd.year as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tblDimDate dd on dd.DateID = us.DateID
		GROUP BY  dd.year;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

-- Dumping structure for procedure NeonReportDev.prc_getVendorBalanceReport
DROP PROCEDURE IF EXISTS `prc_getVendorBalanceReport`;
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getVendorBalanceReport`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` TEXT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME


)
BEGIN
	
	DECLARE v_Round_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	CALL fnUsageVendorSummaryDetail(p_CompanyID,'',p_AccountID,0,p_StartDate,p_EndDate,'','','',0,1);

	SELECT
		MAX(AccountName) as AccountName,
		IF(SUM(NoOfCalls)>0,COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls),0) as ACD , 
		ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
		AccountID,
		DATE(Time) as Date,
		HOUR(Time) as Hour,
		COALESCE(SUM(TotalCharges),0) as Cost,
		COALESCE(SUM(TotalBilledDuration),0) as Minutes,
		COALESCE(SUM(NoOfCalls),0) as Connected,
		COALESCE(SUM(NoOfCalls),0)+COALESCE(SUM(NoOfFailCalls),0) as Attempts
	FROM tmp_tblUsageVendorSummary_ us
	GROUP BY AccountID,DATE(Time),HOUR(Time);

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

-- Dumping structure for procedure NeonReportDev.prc_getVendorReportByTime
DROP PROCEDURE IF EXISTS `prc_getVendorReportByTime`;
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getVendorReportByTime`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_AccountID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_ReportType` INT
)
BEGIN

	DECLARE v_Round_ INT;
	DECLARE V_Detail INT;

	SET V_Detail = 2;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	CALL fnUsageVendorSummary(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,p_UserID,p_isAdmin,V_Detail);

	/* hourly report */
	IF p_ReportType =1
	THEN

		SELECT 
			CONCAT(dt.hour,' Hour') as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageVendorSummary_ us
		INNER JOIN tblDimTime dt on dt.TimeID = us.TimeID
		GROUP BY  us.DateID,dt.hour;

	END IF;

	/* daily report */
	IF p_ReportType =2
	THEN

		SELECT 
			dd.date as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageVendorSummary_ us
		INNER JOIN tblDimDate dd on dd.DateID = us.DateID
		GROUP BY  us.DateID;

	END IF;

	/* weekly report */
	IF p_ReportType =3
	THEN

		SELECT 
			ANY_VALUE(dd.week_year_name) as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageVendorSummary_ us
		INNER JOIN tblDimDate dd on dd.DateID = us.DateID
		GROUP BY  dd.week_of_year;

	END IF;

	/* monthly report */
	IF p_ReportType =4
	THEN

		SELECT 
			ANY_VALUE(dd.month_year_name) as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageVendorSummary_ us
		INNER JOIN tblDimDate dd on dd.DateID = us.DateID
		GROUP BY  dd.month_of_year;

	END IF;

	/* queterly report */
	IF p_ReportType =5
	THEN

		SELECT 
			CONCAT('Q-',ANY_VALUE(dd.quarter_year_name)) as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageVendorSummary_ us
		INNER JOIN tblDimDate dd on dd.DateID = us.DateID
		GROUP BY  dd.quarter_of_year;

	END IF;

	/* yearly report */
	IF p_ReportType =6
	THEN

		SELECT 
			dd.year as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageVendorSummary_ us
		INNER JOIN tblDimDate dd on dd.DateID = us.DateID
		GROUP BY  dd.year;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;