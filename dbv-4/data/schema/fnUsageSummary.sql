CREATE DEFINER=`root`@`localhost` PROCEDURE `fnUsageSummary`(IN `p_CompanyID` INT, IN `p_CompanyGatewayID` INT, IN `p_AccountID` INT, IN `p_CurrencyID` INT, IN `p_StartDate` DATETIME, IN `p_EndDate` DATETIME, IN `p_AreaPrefix` VARCHAR(50), IN `p_Trunk` VARCHAR(50), IN `p_CountryID` INT, IN `p_UserID` INT , IN `p_isAdmin` INT, IN `p_Detail` INT)
BEGIN
	DECLARE v_TimeId_ INT;
	
	IF p_Detail = 1 
	THEN
	
		DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageSummary_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageSummary_(
				`DateID` BIGINT(20) NOT NULL,
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
		INSERT INTO tmp_tblUsageSummary_
		SELECT
			sh.DateID,
			sh.CompanyID,
			sh.AccountID,
			sh.GatewayAccountID,
			sh.CompanyGatewayID,
			sh.Trunk,
			sh.AreaPrefix,
			sh.CountryID,
			us.TotalCharges,
			us.TotalBilledDuration,
			us.TotalDuration,
			us.NoOfCalls,
			us.NoOfFailCalls,
			a.AccountName
		FROM tblSummaryHeader sh
		INNER JOIN tblUsageSummary us
			ON us.SummaryHeaderID = sh.SummaryHeaderID 
		INNER JOIN tblDimDate dd
			ON dd.DateID = sh.DateID
		INNER JOIN NeonRMDev.tblAccount a
			ON sh.AccountID = a.AccountID
		WHERE dd.date BETWEEN p_StartDate AND p_EndDate
		AND sh.CompanyID = p_CompanyID
		AND (p_AccountID = 0 OR sh.AccountID = p_AccountID)
		AND (p_CompanyGatewayID = 0 OR sh.CompanyGatewayID = p_CompanyGatewayID)
		AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
		AND (p_Trunk = '' OR sh.Trunk LIKE REPLACE(p_Trunk, '*', '%'))
		AND (p_AreaPrefix = '' OR sh.AreaPrefix LIKE REPLACE(p_AreaPrefix, '*', '%') )
		AND (p_CountryID = 0 OR sh.CountryID = p_CountryID)
		AND (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID);
		
		INSERT INTO tmp_tblUsageSummary_
		SELECT
			sh.DateID,
			sh.CompanyID,
			sh.AccountID,
			sh.GatewayAccountID,
			sh.CompanyGatewayID,
			sh.Trunk,
			sh.AreaPrefix,
			sh.CountryID,
			us.TotalCharges,
			us.TotalBilledDuration,
			us.TotalDuration,
			us.NoOfCalls,
			us.NoOfFailCalls,
			a.AccountName
		FROM tblSummaryHeader sh
		INNER JOIN tblUsageSummaryLive us
			ON us.SummaryHeaderID = sh.SummaryHeaderID 
		INNER JOIN tblDimDate dd
			ON dd.DateID = sh.DateID
		INNER JOIN NeonRMDev.tblAccount a
			ON sh.AccountID = a.AccountID
		WHERE dd.date BETWEEN p_StartDate AND p_EndDate
		AND sh.CompanyID = p_CompanyID
		AND (p_AccountID = 0 OR sh.AccountID = p_AccountID)
		AND (p_CompanyGatewayID = 0 OR sh.CompanyGatewayID = p_CompanyGatewayID)
		AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
		AND (p_Trunk = '' OR sh.Trunk LIKE REPLACE(p_Trunk, '*', '%'))
		AND (p_AreaPrefix = '' OR sh.AreaPrefix LIKE REPLACE(p_AreaPrefix, '*', '%') )
		AND (p_CountryID = 0 OR sh.CountryID = p_CountryID)
		AND (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID);
		
		
		
	END IF;
	
	IF p_Detail = 2 
	THEN
	
		DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageSummary_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageSummary_(
				`DateID` BIGINT(20) NOT NULL,
				`TimeID` INT(11) NOT NULL,
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
		
		INSERT INTO tmp_tblUsageSummary_
		SELECT
			sh.DateID,
			dt.TimeID,
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
		WHERE dd.date BETWEEN DATE(p_StartDate) AND DATE(p_EndDate)
		AND ( DATEDIFF(p_StartDate,p_EndDate) !=  0 OR ( DATEDIFF(p_StartDate,p_EndDate) =  0 AND CONCAT(dd.date,' ',dt.fulltime) BETWEEN p_StartDate AND p_EndDate))
		AND sh.CompanyID = p_CompanyID
		AND (p_AccountID = 0 OR sh.AccountID = p_AccountID)
		AND (p_CompanyGatewayID = 0 OR sh.CompanyGatewayID = p_CompanyGatewayID)
		AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
		AND (p_Trunk = '' OR sh.Trunk LIKE REPLACE(p_Trunk, '*', '%'))
		AND (p_AreaPrefix = '' OR sh.AreaPrefix LIKE REPLACE(p_AreaPrefix, '*', '%') )
		AND (p_CountryID = 0 OR sh.CountryID = p_CountryID)
		AND (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID);
		
		INSERT INTO tmp_tblUsageSummary_
		SELECT
			sh.DateID,
			dt.TimeID,
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
		WHERE dd.date BETWEEN DATE(p_StartDate) AND DATE(p_EndDate)
		AND ( DATEDIFF(p_StartDate,p_EndDate) !=  0 OR ( DATEDIFF(p_StartDate,p_EndDate) =  0 AND CONCAT(dd.date,' ',dt.fulltime) BETWEEN p_StartDate AND p_EndDate))
		AND sh.CompanyID = p_CompanyID
		AND (p_AccountID = 0 OR sh.AccountID = p_AccountID)
		AND (p_CompanyGatewayID = 0 OR sh.CompanyGatewayID = p_CompanyGatewayID)
		AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
		AND (p_Trunk = '' OR sh.Trunk LIKE REPLACE(p_Trunk, '*', '%'))
		AND (p_AreaPrefix = '' OR sh.AreaPrefix LIKE REPLACE(p_AreaPrefix, '*', '%') )
		AND (p_CountryID = 0 OR sh.CountryID = p_CountryID)
		AND (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID);			
		
	END IF;
END