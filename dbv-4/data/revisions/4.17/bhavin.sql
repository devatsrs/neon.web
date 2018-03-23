Use `StagingReport`;	

DROP PROCEDURE IF EXISTS `fnUsageSummary`;
DELIMITER //
CREATE PROCEDURE `fnUsageSummary`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_AccountID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_CDRType` VARCHAR(50),
	IN `p_UserID` INT ,
	IN `p_isAdmin` INT,
	IN `p_Detail` INT,
	IN `p_ResellerID` INT
)
BEGIN
	DECLARE v_TimeId_ INT;
	DECLARE v_raccountids TEXT;
	SET v_raccountids ='';
	
	IF p_ResellerID > 0
	THEN
		DROP TEMPORARY TABLE IF EXISTS tmp_reselleraccounts_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_reselleraccounts_(
			AccountID int
		);
	
		INSERT INTO tmp_reselleraccounts_
		SELECT AccountID FROM Ratemanagement3.tblAccountDetails WHERE ResellerOwner=p_ResellerID
		UNION
		SELECT AccountID FROM Ratemanagement3.tblReseller WHERE ResellerID=p_ResellerID;
	
		SELECT IFNULL(GROUP_CONCAT(AccountID),'') INTO v_raccountids FROM tmp_reselleraccounts_;
		
	END IF;
	

	IF DATEDIFF(p_EndDate,p_StartDate) > 31 AND p_Detail = 2
	THEN
		SET p_Detail = 1;
	END IF;

	IF p_Detail = 1 
	THEN

		DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageSummary_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageSummary_(
				`DateID` BIGINT(20) NOT NULL,
				`CompanyID` INT(11) NOT NULL,
				`AccountID` INT(11) NOT NULL,
				`CompanyGatewayID` INT(11) NOT NULL,
				`Trunk` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
				`AreaPrefix` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
				`userfield` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',				
				`CountryID` INT(11) NULL DEFAULT NULL,
				`TotalCharges` DOUBLE NULL DEFAULT NULL,
				`TotalCost` DOUBLE NULL DEFAULT NULL,
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
			us.CompanyGatewayID,
			us.Trunk,
			us.AreaPrefix,
			us.userfield,
			us.CountryID,
			us.TotalCharges,
			us.TotalCost,
			us.TotalBilledDuration,
			us.TotalDuration,
			us.NoOfCalls,
			us.NoOfFailCalls,
			a.AccountName
		FROM tblHeader sh
		INNER JOIN tblUsageSummaryDay  us
			ON us.HeaderID = sh.HeaderID
		INNER JOIN tblDimDate dd
			ON dd.DateID = sh.DateID
		INNER JOIN Ratemanagement3.tblAccount a
			ON sh.AccountID = a.AccountID
		WHERE dd.date BETWEEN p_StartDate AND p_EndDate
		AND sh.CompanyID = p_CompanyID
		AND (p_AccountID = 0 OR sh.AccountID = p_AccountID)
		AND (p_CompanyGatewayID = 0 OR us.CompanyGatewayID = p_CompanyGatewayID)
		AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
		AND (p_Trunk = '' OR us.Trunk LIKE REPLACE(p_Trunk, '*', '%'))
		AND (p_AreaPrefix = '' OR us.AreaPrefix LIKE REPLACE(p_AreaPrefix, '*', '%') )
		AND (p_CountryID = 0 OR us.CountryID = p_CountryID)
		AND (p_CDRType = '' OR us.userfield LIKE CONCAT('%',p_CDRType,'%'))
		AND (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
		AND (p_ResellerID = 0 OR FIND_IN_SET(sh.AccountID, v_raccountids) != 0);

		INSERT INTO tmp_tblUsageSummary_
		SELECT
			sh.DateID,
			sh.CompanyID,
			sh.AccountID,
			us.CompanyGatewayID,
			us.Trunk,
			us.AreaPrefix,
			us.userfield,
			us.CountryID,
			us.TotalCharges,
			us.TotalCost,
			us.TotalBilledDuration,
			us.TotalDuration,
			us.NoOfCalls,
			us.NoOfFailCalls,
			a.AccountName
		FROM tblHeader sh
		INNER JOIN tblUsageSummaryDayLive  us
			ON us.HeaderID = sh.HeaderID
		INNER JOIN tblDimDate dd
			ON dd.DateID = sh.DateID
		INNER JOIN Ratemanagement3.tblAccount a
			ON sh.AccountID = a.AccountID
		WHERE dd.date BETWEEN p_StartDate AND p_EndDate
		AND sh.CompanyID = p_CompanyID
		AND (p_AccountID = 0 OR sh.AccountID = p_AccountID)
		AND (p_CompanyGatewayID = 0 OR us.CompanyGatewayID = p_CompanyGatewayID)
		AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
		AND (p_Trunk = '' OR us.Trunk LIKE REPLACE(p_Trunk, '*', '%'))
		AND (p_AreaPrefix = '' OR us.AreaPrefix LIKE REPLACE(p_AreaPrefix, '*', '%') )
		AND (p_CountryID = 0 OR us.CountryID = p_CountryID)
		AND (p_CDRType = '' OR us.userfield LIKE CONCAT('%',p_CDRType,'%'))
		AND (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
		AND (p_ResellerID = 0 OR FIND_IN_SET(sh.AccountID, v_raccountids) != 0);

	END IF;

	IF p_Detail = 2
	THEN

		DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageSummary_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageSummary_(
				`DateID` BIGINT(20) NOT NULL,
				`TimeID` INT(11) NOT NULL,
				`CompanyID` INT(11) NOT NULL,
				`AccountID` INT(11) NOT NULL,
				`CompanyGatewayID` INT(11) NOT NULL,
				`Trunk` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
				`AreaPrefix` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
				`userfield` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',				
				`CountryID` INT(11) NULL DEFAULT NULL,
				`TotalCharges` DOUBLE NULL DEFAULT NULL,
				`TotalCost` DOUBLE NULL DEFAULT NULL,
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
			usd.CompanyGatewayID,
			usd.Trunk,
			usd.AreaPrefix,
			usd.userfield,
			usd.CountryID,
			usd.TotalCharges,
			usd.TotalCost,
			usd.TotalBilledDuration,
			usd.TotalDuration,
			usd.NoOfCalls,
			usd.NoOfFailCalls,
			a.AccountName
		FROM tblHeader sh
		INNER JOIN tblUsageSummaryHour  usd
			ON usd.HeaderID = sh.HeaderID
		INNER JOIN tblDimDate dd
			ON dd.DateID = sh.DateID
		INNER JOIN tblDimTime dt
			ON dt.TimeID = usd.TimeID
		INNER JOIN Ratemanagement3.tblAccount a
			ON sh.AccountID = a.AccountID
		WHERE dd.date BETWEEN DATE(p_StartDate) AND DATE(p_EndDate)
		AND CONCAT(dd.date,' ',dt.fulltime) BETWEEN p_StartDate AND p_EndDate
		AND sh.CompanyID = p_CompanyID
		AND (p_AccountID = 0 OR sh.AccountID = p_AccountID)
		AND (p_CompanyGatewayID = 0 OR usd.CompanyGatewayID = p_CompanyGatewayID)
		AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
		AND (p_Trunk = '' OR usd.Trunk LIKE REPLACE(p_Trunk, '*', '%'))
		AND (p_AreaPrefix = '' OR usd.AreaPrefix LIKE REPLACE(p_AreaPrefix, '*', '%') )
		AND (p_CountryID = 0 OR usd.CountryID = p_CountryID)
		AND (p_CDRType = '' OR usd.userfield LIKE CONCAT('%',p_CDRType,'%'))
		AND (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
		AND (p_ResellerID = 0 OR FIND_IN_SET(sh.AccountID, v_raccountids) != 0);

		INSERT INTO tmp_tblUsageSummary_
		SELECT
			sh.DateID,
			dt.TimeID,
			sh.CompanyID,
			sh.AccountID,
			usd.CompanyGatewayID,
			usd.Trunk,
			usd.AreaPrefix,
			usd.userfield,
			usd.CountryID,
			usd.TotalCharges,
			usd.TotalCost,
			usd.TotalBilledDuration,
			usd.TotalDuration,
			usd.NoOfCalls,
			usd.NoOfFailCalls,
			a.AccountName
		FROM tblHeader sh
		INNER JOIN tblUsageSummaryHourLive  usd
			ON usd.HeaderID = sh.HeaderID
		INNER JOIN tblDimDate dd
			ON dd.DateID = sh.DateID
		INNER JOIN tblDimTime dt
			ON dt.TimeID = usd.TimeID
		INNER JOIN Ratemanagement3.tblAccount a
			ON sh.AccountID = a.AccountID
		WHERE dd.date BETWEEN DATE(p_StartDate) AND DATE(p_EndDate)
		AND CONCAT(dd.date,' ',dt.fulltime) BETWEEN p_StartDate AND p_EndDate
		AND sh.CompanyID = p_CompanyID
		AND (p_AccountID = 0 OR sh.AccountID = p_AccountID)
		AND (p_CompanyGatewayID = 0 OR usd.CompanyGatewayID = p_CompanyGatewayID)
		AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
		AND (p_Trunk = '' OR usd.Trunk LIKE REPLACE(p_Trunk, '*', '%'))
		AND (p_AreaPrefix = '' OR usd.AreaPrefix LIKE REPLACE(p_AreaPrefix, '*', '%') )
		AND (p_CountryID = 0 OR usd.CountryID = p_CountryID)
		AND (p_CDRType = '' OR usd.userfield LIKE CONCAT('%',p_CDRType,'%'))
		AND (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
		AND (p_ResellerID = 0 OR FIND_IN_SET(sh.AccountID, v_raccountids) != 0);

	END IF;

END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_getAccountReportAll`;
DELIMITER //
CREATE PROCEDURE `prc_getAccountReportAll`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_AccountID` INT,
	IN `p_ResellerID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_CDRType` VARCHAR(50),
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
BEGIN

	DECLARE v_Round_ int;
	DECLARE v_OffSet_ int;
		
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	CALL fnUsageSummary(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,p_CDRType,p_UserID,p_isAdmin,2,p_ResellerID);

	
	/* grid display*/
	IF p_isExport = 0
	THEN
	
	/* account by call count */	
	SELECT AccountName ,FORMAT(SUM(NoOfCalls),0) AS CallCount,FORMAT(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalSeconds,FORMAT(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
		FORMAT(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
		FORMAT( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage,
	MAX(AccountID) as AccountID
	FROM tmp_tblUsageSummary_ us
	GROUP BY AccountName   
	ORDER BY
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountDESC') THEN SUM(NoOfCalls)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountASC') THEN SUM(NoOfCalls)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesDESC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesASC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN AccountName
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN AccountName
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostASC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDDESC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDASC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRDESC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRASC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginASC') THEN ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageDESC') THEN ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageASC') THEN ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_)
	END ASC
	LIMIT p_RowspPage OFFSET v_OffSet_;
	
	SELECT COUNT(*) AS totalcount,FORMAT(SUM(CallCount),0) AS TotalCall,FORMAT(SUM(TotalSeconds)/60,0) AS TotalDuration,FORMAT(SUM(TotalCost),v_Round_) AS TotalCost,
		FORMAT(SUM(TotalMargin),v_Round_) AS TotalMargin
	FROM (
		SELECT AccountName ,SUM(NoOfCalls) AS CallCount,COALESCE(SUM(TotalBilledDuration),0) as TotalSeconds,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		GROUP BY AccountName
	)tbl;

	
	END IF;
	
	/* export data*/
	IF p_isExport = 1
	THEN
		SELECT
			AccountName  as Name,
			SUM(NoOfCalls) AS `No. of Calls`,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as `Billed Duration (Min.)`,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as `Charged Amount`,
			IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as `ACD (mm:ss)` ,
			ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as `ASR (%)`,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as `Margin`,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as `Margin (%)`
		FROM tmp_tblUsageSummary_ us
		GROUP BY AccountName;
	END IF;
	
	
	/* chart display*/
	IF p_isExport = 2
	THEN
	
		/* top 10 account by call count */	
		SELECT AccountName as ChartVal ,SUM(NoOfCalls) AS CallCount,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		GROUP BY AccountName HAVING SUM(NoOfCalls) > 0 ORDER BY CallCount DESC LIMIT 10;
		
		/* top 10 account by call cost */	
		SELECT AccountName as ChartVal,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		GROUP BY AccountName HAVING SUM(TotalCharges) > 0 ORDER BY TotalCost DESC LIMIT 10;
		
		/* top 10 account by call minutes */	
		SELECT AccountName as ChartVal,ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalMinutes,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		GROUP BY AccountName HAVING SUM(TotalBilledDuration) > 0  ORDER BY TotalMinutes DESC LIMIT 10;
	
	END IF;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_getACD_ASR_Alert`;
DELIMITER //
CREATE PROCEDURE `prc_getACD_ASR_Alert`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` TEXT,
	IN `p_AccountID` TEXT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` TEXT,
	IN `p_Trunk` TEXT,
	IN `p_CountryID` TEXT
)
BEGIN
	
	DECLARE v_Round_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	CALL fnUsageSummaryDetail(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,0,1);

	IF p_AccountID = ''
	THEN
		SELECT
			IF(SUM(NoOfCalls)>0,COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls),0) as ACD , 
			ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			HOUR(ANY_VALUE(Time)) as Hour,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as Minutes,
			COALESCE(SUM(NoOfCalls),0) as Connected,
			COALESCE(SUM(NoOfCalls),0)+COALESCE(SUM(NoOfFailCalls),0) as Attempts
		FROM tmp_tblUsageSummary_ us;
		
	END IF;
	
	IF p_AccountID != ''
	THEN
		SELECT
			IF(SUM(NoOfCalls)>0,COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls),0) as ACD , 
			ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			AccountID,
			HOUR(ANY_VALUE(Time)) as Hour,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as Minutes,
			COALESCE(SUM(NoOfCalls),0) as Connected,
			COALESCE(SUM(NoOfCalls),0)+COALESCE(SUM(NoOfFailCalls),0) as Attempts
		FROM tmp_tblUsageSummary_ us
		GROUP BY AccountID;
	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_getDescReportAll`;
DELIMITER //
CREATE PROCEDURE `prc_getDescReportAll`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_AccountID` INT,
	IN `p_ResellerID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_CDRType` VARCHAR(50),
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
BEGIN

	DECLARE v_Round_ INT;
	DECLARE v_OffSet_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	CALL fngetDefaultCodes(p_CompanyID);

	CALL fnUsageSummary(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,p_CDRType,p_UserID,p_isAdmin,2,p_ResellerID);

	/* grid display*/
	IF p_isExport = 0
	THEN

		/* Description by call count */	
			
		SELECT IFNULL(Description,'Other') AS Description ,FORMAT(SUM(NoOfCalls),0) AS CallCount,FORMAT(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) AS TotalSeconds,FORMAT(COALESCE(SUM(TotalCharges),0), v_Round_) AS TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) AS ACD , IF(SUM(NoOfCalls)>0,ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_),0) AS ASR,
			FORMAT(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			FORMAT( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		LEFT JOIN tmp_codes_ c ON c.Code = us.AreaPrefix
		GROUP BY c.Description
		ORDER BY
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountDESC') THEN SUM(NoOfCalls)
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountASC') THEN SUM(NoOfCalls)
		END ASC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesDESC') THEN COALESCE(SUM(TotalBilledDuration),0)
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesASC') THEN COALESCE(SUM(TotalBilledDuration),0)
		END ASC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN Description
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN Description
		END ASC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostASC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
		END ASC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDDESC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDASC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
		END ASC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRDESC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRASC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
		END ASC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_)
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginASC') THEN ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_)
		END ASC,
		CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageDESC') THEN ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_)
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageASC') THEN ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_)
		END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT COUNT(*) AS totalcount,FORMAT(SUM(CallCount),0) AS TotalCall,FORMAT(SUM(TotalSeconds)/60,0) AS TotalDuration,FORMAT(SUM(TotalCost),v_Round_) AS TotalCost,FORMAT(SUM(TotalMargin),v_Round_) AS TotalMargin FROM(
			SELECT IFNULL(Description,'Other') AS Description ,SUM(NoOfCalls) AS CallCount,COALESCE(SUM(TotalBilledDuration),0) AS TotalSeconds,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) AS TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) AS ACD , IF(SUM(NoOfCalls)>0,ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_),0) AS ASR,
				ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
				ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
			FROM tmp_tblUsageSummary_ us
			LEFT JOIN tmp_codes_ c ON c.Code = us.AreaPrefix
			GROUP BY c.Description
		)tbl;

	END IF;

	/* export data*/
	IF p_isExport = 1
	THEN

		SELECT
			SQL_CALC_FOUND_ROWS IFNULL(Description,'Other') AS Description,
			SUM(NoOfCalls) AS `No. of Calls`,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as `Billed Duration (Min.)`,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as `Charged Amount`,
			IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as `ACD (mm:ss)` ,
			ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as `ASR (%)`,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as `Margin`,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as `Margin (%)`
		FROM tmp_tblUsageSummary_ us
		LEFT JOIN tmp_codes_ c ON c.Code = us.AreaPrefix
		GROUP BY Description;

	END IF;

	/* chart display*/
	IF p_isExport = 2
	THEN

		/* top 10 Description by call count */
		
		SELECT Description AS ChartVal ,SUM(NoOfCalls) AS CallCount,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) AS ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) AS ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tmp_codes_ c ON c.Code = us.AreaPrefix
		GROUP BY Description HAVING SUM(NoOfCalls) > 0 ORDER BY CallCount DESC LIMIT 10;

		/* top 10 Description by call cost */
		
		SELECT Description AS ChartVal,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) AS TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) AS ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) AS ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tmp_codes_ c ON c.Code = us.AreaPrefix
		GROUP BY Description HAVING SUM(TotalCharges) > 0 ORDER BY TotalCost DESC LIMIT 10;

		/* top 10 Description by call minutes */
		
		SELECT Description AS ChartVal,ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) AS TotalMinutes,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) AS ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) AS ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tmp_codes_ c ON c.Code = us.AreaPrefix
		GROUP BY Description HAVING SUM(TotalBilledDuration) > 0  ORDER BY TotalMinutes DESC LIMIT 10;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_getDestinationReportAll`;
DELIMITER //
CREATE PROCEDURE `prc_getDestinationReportAll`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_AccountID` INT,
	IN `p_ResellerID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_CDRType` VARCHAR(50),
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
BEGIN

	DECLARE v_Round_ int;
	DECLARE v_OffSet_ int;
		
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	CALL fnGetCountry();
		 
	
	CALL fnUsageSummary(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,p_CDRType,p_UserID,p_isAdmin,2,p_ResellerID);

	
	/* grid display*/
	IF p_isExport = 0
	THEN
	
	/* country by call count */	
		
	SELECT IFNULL(Country,'Other') as Country ,FORMAT(SUM(NoOfCalls),0) AS CallCount,FORMAT(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalSeconds,FORMAT(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , IF(SUM(NoOfCalls)>0,ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_),0) as ASR,
		FORMAT(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
		FORMAT( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
	FROM tmp_tblUsageSummary_ us
	LEFT JOIN temptblCountry c ON c.CountryID = us.CountryID
	WHERE (p_CountryID = 0 OR c.CountryID = p_CountryID)
	GROUP BY c.Country   
	ORDER BY
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountDESC') THEN SUM(NoOfCalls)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountASC') THEN SUM(NoOfCalls)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesDESC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesASC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryDESC') THEN Country
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryASC') THEN Country
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostASC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDDESC') THEN IF(SUM(NoOfCalls)>0,(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDASC') THEN IF(SUM(NoOfCalls)>0,(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRDESC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRASC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginASC') THEN ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageDESC') THEN ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageASC') THEN ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_)
	END ASC
	LIMIT p_RowspPage OFFSET v_OffSet_;
	

	SELECT COUNT(*) AS totalcount,FORMAT(SUM(CallCount),0) AS TotalCall,FORMAT(SUM(TotalSeconds)/60,0) AS TotalDuration,FORMAT(SUM(TotalCost),v_Round_) AS TotalCost,FORMAT(SUM(TotalMargin),v_Round_) AS TotalMargin FROM(
		SELECT IFNULL(Country,'Other') as Country ,SUM(NoOfCalls) AS CallCount,COALESCE(SUM(TotalBilledDuration),0) as TotalSeconds,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , IF(SUM(NoOfCalls)>0,ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_),0) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		LEFT JOIN temptblCountry c ON c.CountryID = us.CountryID
		WHERE (p_CountryID = 0 OR c.CountryID = p_CountryID)
		GROUP BY c.Country
	)tbl;

	
	END IF;
	
	/* export data*/
	IF p_isExport = 1
	THEN
		SELECT
			SQL_CALC_FOUND_ROWS IFNULL(Country,'Other') as  Country,
			SUM(NoOfCalls) AS `No. of Calls`,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as `Billed Duration (Min.)`,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as `Charged Amount`,
			IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as `ACD (mm:ss)` ,
			ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as `ASR (%)`,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as `Margin`,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as `Margin (%)`
		FROM tmp_tblUsageSummary_ us
		LEFT JOIN temptblCountry c ON c.CountryID = us.CountryID
		WHERE (p_CountryID = 0 OR c.CountryID = p_CountryID)
		GROUP BY Country;
	END IF;
	
	
	/* chart display*/
	IF p_isExport = 2
	THEN
	
		/* top 10 country by call count */	
			
		SELECT Country as ChartVal ,SUM(NoOfCalls) AS CallCount,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		INNER JOIN temptblCountry c ON c.CountryID = us.CountryID
		WHERE (p_CountryID = 0 OR c.CountryID = p_CountryID)
		GROUP BY Country HAVING SUM(NoOfCalls) > 0 ORDER BY CallCount DESC LIMIT 10;
		
		/* top 10 country by call cost */	
			
		SELECT Country as ChartVal,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		INNER JOIN temptblCountry c ON c.CountryID = us.CountryID
		WHERE (p_CountryID = 0 OR c.CountryID = p_CountryID)
		GROUP BY Country HAVING SUM(TotalCharges) > 0 ORDER BY TotalCost DESC LIMIT 10;
		
		/* top 10 country by call minutes */	
			
		SELECT Country as ChartVal,ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalMinutes,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		INNER JOIN temptblCountry c ON c.CountryID = us.CountryID
		WHERE (p_CountryID = 0 OR c.CountryID = p_CountryID)
		GROUP BY Country HAVING SUM(TotalBilledDuration) > 0  ORDER BY TotalMinutes DESC LIMIT 10;
	
	END IF;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_getGatewayReportAll`;
DELIMITER //
CREATE PROCEDURE `prc_getGatewayReportAll`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_AccountID` INT,
	IN `p_ResellerID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_CDRType` VARCHAR(50),
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
BEGIN

	DECLARE v_Round_ int;
	DECLARE v_OffSet_ int;
		
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	CALL fnUsageSummary(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,p_CDRType,p_UserID,p_isAdmin,2,p_ResellerID);

	
	/* grid display*/
	IF p_isExport = 0
	THEN
	
	/* CompanyGatewayID by call count */	
		
	SELECT fnGetCompanyGatewayName(CompanyGatewayID) ,FORMAT(SUM(NoOfCalls),0) AS CallCount,FORMAT(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalSeconds,FORMAT(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
		FORMAT(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
		FORMAT( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage,
		CompanyGatewayID
	FROM tmp_tblUsageSummary_ us
	GROUP BY CompanyGatewayID   
	ORDER BY
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountDESC') THEN SUM(NoOfCalls)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountASC') THEN SUM(NoOfCalls)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesDESC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesASC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GatewayDESC') THEN fnGetCompanyGatewayName(CompanyGatewayID)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'GatewayASC') THEN fnGetCompanyGatewayName(CompanyGatewayID)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostASC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDDESC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDASC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRDESC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRASC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginASC') THEN ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageDESC') THEN ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageASC') THEN ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_)
	END ASC
	LIMIT p_RowspPage OFFSET v_OffSet_;
	
	SELECT COUNT(*) AS totalcount,FORMAT(SUM(CallCount),0) AS TotalCall,FORMAT(SUM(TotalSeconds)/60,0) AS TotalDuration,FORMAT(SUM(TotalCost),v_Round_) AS TotalCost,FORMAT(SUM(TotalMargin),v_Round_) AS TotalMargin FROM (
		SELECT fnGetCompanyGatewayName(CompanyGatewayID) ,SUM(NoOfCalls) AS CallCount,COALESCE(SUM(TotalBilledDuration),0) as TotalSeconds,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		GROUP BY CompanyGatewayID
	)tbl;

	
	END IF;
	
	/* export data*/
	IF p_isExport = 1
	THEN
		SELECT
			fnGetCompanyGatewayName(CompanyGatewayID)  as Name,
			SUM(NoOfCalls) AS `No. of Calls`,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as `Billed Duration (Min.)`,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as `Charged Amount`,
			IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as `ACD (mm:ss)`,
			ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as `ASR (%)`,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as `Margin`,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as `Margin (%)`
		FROM tmp_tblUsageSummary_ us
		GROUP BY CompanyGatewayID;
	END IF;
	
	
	/* chart display*/
	IF p_isExport = 2
	THEN
	
		/* top 10 CompanyGatewayID by call count */	
			
		SELECT fnGetCompanyGatewayName(CompanyGatewayID) as ChartVal ,SUM(NoOfCalls) AS CallCount,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		WHERE us.CompanyGatewayID != 'Other'
		GROUP BY CompanyGatewayID HAVING SUM(NoOfCalls) > 0 ORDER BY CallCount DESC LIMIT 10;
		
		/* top 10 CompanyGatewayID by call cost */	
			
		SELECT fnGetCompanyGatewayName(CompanyGatewayID) as ChartVal,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		WHERE us.CompanyGatewayID != 'Other'
		GROUP BY CompanyGatewayID HAVING SUM(TotalCharges) > 0 ORDER BY TotalCost DESC LIMIT 10;
		
		/* top 10 CompanyGatewayID by call minutes */	
			
		SELECT fnGetCompanyGatewayName(CompanyGatewayID) as ChartVal,ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalMinutes,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		WHERE us.CompanyGatewayID != 'Other'
		GROUP BY CompanyGatewayID HAVING SUM(TotalBilledDuration) > 0  ORDER BY TotalMinutes DESC LIMIT 10;
	
	END IF;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_getHourlyReport`;
DELIMITER //
CREATE PROCEDURE `prc_getHourlyReport`(
	IN `p_CompanyID` INT,
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_AccountID` INT,
	IN `p_ResellerID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_CDRType` VARCHAR(50)
)
BEGIN
	
	DECLARE v_Round_ int;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	CALL fnUsageSummary(p_CompanyID,0,p_AccountID,0,p_StartDate,p_EndDate,'','',0,p_CDRType,p_UserID,p_isAdmin,2,p_ResellerID);
	
	/* total cost */
	SELECT ROUND(COALESCE(SUM(TotalCharges),0),v_Round_) as TotalCost FROM tmp_tblUsageSummary_;
	
	/* cost per hour*/
	SELECT dt.hour as HOUR ,ROUND(COALESCE(SUM(TotalCharges),0),v_Round_) as TotalCost FROM tmp_tblUsageSummary_ us INNER JOIN tblDimTime dt on us.TimeID =  dt.TimeID GROUP BY dt.hour;
	
	/* total duration or minutes*/
	SELECT ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalMinutes FROM tmp_tblUsageSummary_;
	
	/* minutes pre hour*/
	SELECT dt.hour as HOUR ,ROUND(COALESCE(SUM(TotalBilledDuration),0) / 60,0) as TotalMinutes FROM tmp_tblUsageSummary_ us INNER JOIN tblDimTime dt on us.TimeID =  dt.TimeID GROUP BY dt.hour;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_getPrefixReportAll`;
DELIMITER //
CREATE PROCEDURE `prc_getPrefixReportAll`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_AccountID` INT,
	IN `p_ResellerID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_CDRType` VARCHAR(50),
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
BEGIN

	DECLARE v_Round_ int;
	DECLARE v_OffSet_ int;
		
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	CALL fnUsageSummary(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,p_CDRType,p_UserID,p_isAdmin,2,p_ResellerID);

	
	/* grid display*/
	IF p_isExport = 0
	THEN
	
	/* AreaPrefix by call count */	
		
	SELECT AreaPrefix ,FORMAT(SUM(NoOfCalls),0) AS CallCount,FORMAT(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalSeconds,FORMAT(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
		FORMAT(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
		FORMAT( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
	FROM tmp_tblUsageSummary_ us
	GROUP BY AreaPrefix   
	ORDER BY
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountDESC') THEN SUM(NoOfCalls)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountASC') THEN SUM(NoOfCalls)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesDESC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesASC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AreaPrefixDESC') THEN AreaPrefix
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AreaPrefixASC') THEN AreaPrefix
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostASC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDDESC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDASC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRDESC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRASC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginASC') THEN ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageDESC') THEN ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageASC') THEN ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_)
	END ASC
	LIMIT p_RowspPage OFFSET v_OffSet_;
	
	SELECT COUNT(*) AS totalcount,FORMAT(SUM(CallCount),0) AS TotalCall,FORMAT(SUM(TotalSeconds)/60,0) AS TotalDuration,FORMAT(SUM(TotalCost),v_Round_) AS TotalCost,FORMAT(SUM(TotalMargin),v_Round_) AS TotalMargin FROM (
		SELECT AreaPrefix ,SUM(NoOfCalls) AS CallCount,COALESCE(SUM(TotalBilledDuration),0) as TotalSeconds,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		GROUP BY AreaPrefix
	)tbl;

	
	END IF;
	
	/* export data*/
	IF p_isExport = 1
	THEN
		SELECT
			AreaPrefix,
			SUM(NoOfCalls) AS `No. of Calls`,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as `Billed Duration (Min.)`,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as `Charged Amount`,
			IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as `ACD (mm:ss)` ,
			ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as `ASR (%)`,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as `Margin`,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as `Margin (%)`
		FROM tmp_tblUsageSummary_ us
		GROUP BY AreaPrefix;
	END IF;
	
	
	/* chart display*/
	IF p_isExport = 2
	THEN
	
		/* top 10 AreaPrefix by call count */	
			
		SELECT AreaPrefix as ChartVal ,SUM(NoOfCalls) AS CallCount,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		WHERE us.AreaPrefix != 'Other'
		GROUP BY AreaPrefix HAVING SUM(NoOfCalls) > 0 ORDER BY CallCount DESC LIMIT 10;
		
		/* top 10 AreaPrefix by call cost */	
			
		SELECT AreaPrefix as ChartVal,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		WHERE us.AreaPrefix != 'Other'
		GROUP BY AreaPrefix HAVING SUM(TotalCharges) > 0 ORDER BY TotalCost DESC LIMIT 10;
		
		/* top 10 AreaPrefix by call minutes */	
			
		SELECT AreaPrefix as ChartVal,ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalMinutes,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		WHERE us.AreaPrefix != 'Other'
		GROUP BY AreaPrefix HAVING SUM(TotalBilledDuration) > 0  ORDER BY TotalMinutes DESC LIMIT 10;
	
	END IF;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_getReportByTime`;
DELIMITER //
CREATE PROCEDURE `prc_getReportByTime`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_AccountID` INT,
	IN `p_ResellerID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_CDRType` VARCHAR(50),
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

	CALL fnUsageSummary(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,p_CDRType,p_UserID,p_isAdmin,V_Detail,p_ResellerID);

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
		GROUP BY  dd.year,dd.week_of_year
		ORDER BY dd.year,dd.week_of_year;

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
		GROUP BY  dd.year,dd.month_of_year
		ORDER BY dd.year,dd.month_of_year;

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
		GROUP BY  dd.year,dd.quarter_of_year
		ORDER BY dd.year,dd.quarter_of_year;

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


DROP PROCEDURE IF EXISTS `prc_getTrunkReportAll`;
DELIMITER //
CREATE PROCEDURE `prc_getTrunkReportAll`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_AccountID` INT,
	IN `p_ResellerID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_CDRType` VARCHAR(50),
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
BEGIN

	DECLARE v_Round_ int;
	DECLARE v_OffSet_ int;
		
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	CALL fnUsageSummary(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,p_CDRType,p_UserID,p_isAdmin,2,p_ResellerID);

	
	/* grid display*/
	IF p_isExport = 0
	THEN
	
	/* Trunk by call count */	
		
	SELECT Trunk ,FORMAT(SUM(NoOfCalls),0) AS CallCount,FORMAT(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalSeconds,FORMAT(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,	
		FORMAT(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
		FORMAT( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
	FROM tmp_tblUsageSummary_ us
	GROUP BY Trunk   
	ORDER BY
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountDESC') THEN SUM(NoOfCalls)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CallCountASC') THEN SUM(NoOfCalls)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesDESC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMinutesASC') THEN COALESCE(SUM(TotalBilledDuration),0)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TrunkDESC') THEN Trunk
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TrunkASC') THEN Trunk
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostASC') THEN ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDDESC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ACDASC') THEN (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls))
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRDESC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ASRASC') THEN SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginDESC') THEN ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginASC') THEN ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_)
	END ASC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageDESC') THEN ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_)
	END DESC,
	CASE
		WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageASC') THEN ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_)
	END ASC
	LIMIT p_RowspPage OFFSET v_OffSet_;
	
	SELECT COUNT(*) AS totalcount,FORMAT(SUM(CallCount),0) AS TotalCall,FORMAT(SUM(TotalSeconds)/60,0) AS TotalDuration,FORMAT(SUM(TotalCost),v_Round_) AS TotalCost,FORMAT(SUM(TotalMargin),v_Round_) AS TotalMargin FROM (
		SELECT Trunk ,SUM(NoOfCalls) AS CallCount,COALESCE(SUM(TotalBilledDuration),0) as TotalSeconds,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		GROUP BY Trunk
	)tbl;

	
	END IF;
	
	/* export data*/
	IF p_isExport = 1
	THEN
		SELECT
			Trunk,
			SUM(NoOfCalls) AS `No. of Calls`,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as `Billed Duration (Min.)`,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as `Charged Amount`,
			IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as `ACD (mm:ss)` ,
			ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as `ASR (%)`,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as `Margin`,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as `Margin (%)`
		FROM tmp_tblUsageSummary_ us
		GROUP BY Trunk;
	END IF;
	
	
	/* chart display*/
	IF p_isExport = 2
	THEN
	
		/* top 10 Trunk by call count */	
			
		SELECT Trunk as ChartVal ,SUM(NoOfCalls) AS CallCount,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		WHERE us.Trunk != 'Other'
		GROUP BY Trunk HAVING SUM(NoOfCalls) > 0 ORDER BY CallCount DESC LIMIT 10;
		
		/* top 10 Trunk by call cost */	
			
		SELECT Trunk as ChartVal,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		WHERE us.Trunk != 'Other'
		GROUP BY Trunk HAVING SUM(TotalCharges) > 0 ORDER BY TotalCost DESC LIMIT 10;
		
		/* top 10 Trunk by call minutes */	
			
		SELECT Trunk as ChartVal,ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalMinutes,IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage
		FROM tmp_tblUsageSummary_ us
		WHERE us.Trunk != 'Other'
		GROUP BY Trunk HAVING SUM(TotalBilledDuration) > 0  ORDER BY TotalMinutes DESC LIMIT 10;
	
	END IF;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_getWorldMap`;
DELIMITER //
CREATE PROCEDURE `prc_getWorldMap`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_AccountID` INT,
	IN `p_ResellerID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` VARCHAR(50),
	IN `p_Trunk` VARCHAR(50),
	IN `p_CountryID` INT,
	IN `p_CDRType` VARCHAR(50),
	IN `p_UserID` INT,
	IN `p_isAdmin` INT
)
BEGIN

	DECLARE v_Round_ int;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	CALL fnGetCountry();

	CALL fnUsageSummary(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,p_CDRType,p_UserID,p_isAdmin,2,p_ResellerID);

	/* get all country call counts*/
	SELECT 
		Country,
		SUM(NoOfCalls) AS CallCount,
		ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,
		ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalMinutes,
		IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD,
		ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
		ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
		ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage,
		MAX(ISO2) AS ISO_Code,
		tblCountry.CountryID
	FROM tmp_tblUsageSummary_
	INNER JOIN temptblCountry AS tblCountry 
		ON tblCountry.CountryID = tmp_tblUsageSummary_.CountryID
	GROUP BY Country,tblCountry.CountryID 
	HAVING SUM(NoOfCalls) > 0
	ORDER BY CallCount DESC;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_verifySummary`;
DELIMITER //
CREATE PROCEDURE `prc_verifySummary`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_StartDate` DATETIME, IN `p_EndDate` DATETIME)
BEGIN

	DROP TEMPORARY TABLE IF EXISTS tmp_verify_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_verify_(
	   AccountID INT,
	   TotalCall INT,
	   TotalCharges decimal(18,6),
	   TotalSecond BIGINT,
	   Summary INT,
	   CDR INT
   );
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	CALL RMBilling3.fnUsageDetail(p_CompanyID,p_AccountID,0,p_StartDate,p_EndDate,0,1,1,'','','',0);
	
	INSERT INTO tmp_verify_ (AccountID,TotalCall,TotalCharges,TotalSecond,Summary,CDR)
	SELECT p_AccountID,COUNT(*) as TotalCall,SUM(cost) as TotalCost,SUM(billed_duration) as TotalSecond,0,1 FROM RMBilling3.tmp_tblUsageDetails_;
	
	CALL fnUsageSummary(p_CompanyID,0,p_AccountID,0,p_StartDate,p_EndDate,'','',0,0,1,1);
	
	INSERT INTO tmp_verify_ (AccountID,TotalCall,TotalCharges,TotalSecond,Summary,CDR)
	SELECT p_AccountID,SUM(NoOfCalls) as TotalCall,SUM(TotalCharges) as TotalCost,SUM(TotalBilledDuration) as TotalSecond,1,0 FROM tmp_tblUsageSummary_;
	
	SELECT * FROM tmp_verify_;

	


END//
DELIMITER ;
