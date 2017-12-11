CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_getAccountReport`(
	IN `p_CompanyID` INT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_UserID` VARCHAR(50),
	IN `p_isAdmin` INT,
	IN `p_ReportType` VARCHAR(50),
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
BEGIN

	DECLARE v_Round_ int;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageSummary_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageSummary_(
			`DateID` BIGINT(20) NOT NULL,
			`Date` DATE,
			`CompanyID` INT(11) NOT NULL,
			`AccountID` INT(11) NOT NULL,
			`TotalCharges` DOUBLE NULL DEFAULT NULL,
			`TotalCost` DOUBLE NULL DEFAULT NULL,
			`AccountName` varchar(100),
			`UserName` varchar(100),
			INDEX `tblUsageSummary_dim_date` (`DateID`)
	);
	INSERT INTO tmp_tblUsageSummary_
	SELECT
		sh.DateID,
		dd.date,
		sh.CompanyID,
		sh.AccountID,
		us.TotalCharges,
		us.TotalCost,
		a.AccountName,
		CONCAT(IFNULL(u.FirstName,''),' ',IFNULL(u.LastName,''))
	FROM tblHeader sh
	INNER JOIN tblUsageSummaryDay  us
		ON us.HeaderID = sh.HeaderID
	INNER JOIN tblDimDate dd
		ON dd.DateID = sh.DateID
	INNER JOIN NeonRMDev.tblAccount a
		ON sh.AccountID = a.AccountID
	INNER JOIN NeonRMDev.tblUser u
		ON a.Owner = u.UserID
	WHERE dd.date BETWEEN p_StartDate AND p_EndDate
	AND sh.CompanyID = p_CompanyID
	AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND FIND_IN_SET(a.Owner,p_UserID) > 0))
	AND (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID);

	INSERT INTO tmp_tblUsageSummary_
	SELECT
		sh.DateID,
		dd.date,
		sh.CompanyID,
		sh.AccountID,
		us.TotalCharges,
		us.TotalCost,
		a.AccountName,
		CONCAT(IFNULL(u.FirstName,''),' ',IFNULL(u.LastName,''))
	FROM tblHeader sh
	INNER JOIN tblUsageSummaryDayLive  us
		ON us.HeaderID = sh.HeaderID
	INNER JOIN tblDimDate dd
		ON dd.DateID = sh.DateID
	INNER JOIN NeonRMDev.tblAccount a
		ON sh.AccountID = a.AccountID
	INNER JOIN NeonRMDev.tblUser u
		ON a.Owner = u.UserID
	WHERE dd.date BETWEEN p_StartDate AND p_EndDate
	AND sh.CompanyID = p_CompanyID
	AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND FIND_IN_SET(a.Owner,p_UserID) > 0))
	AND (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID);

	/* grid display*/
	IF p_ReportType = 'Daily' AND p_isExport = 0
	THEN

		SELECT 
			
			MAX(UserName),
			AccountName,
			us.Date as TIMEVAL,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as MarginPercentage,
			CONCAT(us.Date,' ## ',us.Date) as DATERANGE
		FROM tmp_tblUsageSummary_ us
		GROUP BY us.Date,us.AccountName
		ORDER BY
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'UserNameDESC') THEN MAX(UserName)
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'UserNameASC') THEN MAX(UserName)
			END ASC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN AccountName
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN AccountName
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TIMEVALDESC') THEN us.Date
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TIMEVALASC') THEN us.Date
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostDESC') THEN COALESCE(SUM(TotalCharges),0)
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostASC') THEN COALESCE(SUM(TotalCharges),0)
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginDESC') THEN COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginASC') THEN COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageDESC') THEN (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageASC') THEN (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100
			END ASC;

		SELECT COUNT(*) AS totalcount,SUM(TotalMargin) AS TotalMargin,SUM(TotalCost) AS TotalCost
		FROM (
			SELECT
				ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,
				ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin
			FROM tmp_tblUsageSummary_ us
			GROUP BY us.Date,us.AccountName
		)tbl;

	END IF;

	IF p_ReportType = 'Weekly' AND p_isExport = 0
	THEN

		SELECT 
			MAX(UserName),
			AccountName,
			CONCAT(dd.year,'-',dd.week_of_year) as TIMEVAL,
			ROUND(COALESCE(SUM(TotalCharges),0), 2) as TotalCost,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), 2) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, 2) as MarginPercentage,
			CONCAT(MIN(us.Date),' ## ',MAX(us.Date)) as DATERANGE
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tblDimDate dd ON dd.DateID = us.DateID
		GROUP BY  dd.year,dd.week_of_year,AccountName
		ORDER BY
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'UserNameDESC') THEN MAX(UserName)
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'UserNameASC') THEN MAX(UserName)
			END ASC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN AccountName
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN AccountName
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TIMEVALDESC') THEN CONCAT(dd.year,'-',dd.week_of_year)
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TIMEVALASC') THEN CONCAT(dd.year,'-',dd.week_of_year)
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostDESC') THEN COALESCE(SUM(TotalCharges),0)
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostASC') THEN COALESCE(SUM(TotalCharges),0)
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginDESC') THEN COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginASC') THEN COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageDESC') THEN (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageASC') THEN (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100
			END ASC;

		SELECT COUNT(*) AS totalcount,SUM(TotalMargin) AS TotalMargin,SUM(TotalCost) AS TotalCost
		FROM (
			SELECT
				ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,
				ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin
			FROM tmp_tblUsageSummary_ us
			INNER JOIN tblDimDate dd ON dd.DateID = us.DateID
			GROUP BY dd.year,dd.week_of_year,AccountName
		)tbl;

	END IF;

	IF p_ReportType = 'Monthly' AND p_isExport = 0
	THEN

		SELECT 
			MAX(UserName),
			AccountName,
			CONCAT(dd.year,'-',dd.month_of_year) as TIMEVAL,
			ROUND(COALESCE(SUM(TotalCharges),0), 2) as TotalCost,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), 2) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, 2) as MarginPercentage,
			CONCAT(MIN(us.Date),' ## ',MAX(us.Date)) as DATERANGE
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tblDimDate dd ON dd.DateID = us.DateID
		GROUP BY  dd.year,dd.month_of_year,AccountName
		ORDER BY
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'UserNameDESC') THEN MAX(UserName)
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'UserNameASC') THEN MAX(UserName)
			END ASC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN AccountName
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN AccountName
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TIMEVALDESC') THEN CONCAT(dd.year,'-',dd.month_of_year)
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TIMEVALASC') THEN CONCAT(dd.year,'-',dd.month_of_year)
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostDESC') THEN COALESCE(SUM(TotalCharges),0)
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostASC') THEN COALESCE(SUM(TotalCharges),0)
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginDESC') THEN COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginASC') THEN COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageDESC') THEN (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageASC') THEN (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100
			END ASC;

		SELECT COUNT(*) AS totalcount,SUM(TotalMargin) AS TotalMargin,SUM(TotalCost) AS TotalCost
		FROM (
			SELECT
				ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,
				ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin
			FROM tmp_tblUsageSummary_ us
			INNER JOIN tblDimDate dd ON dd.DateID = us.DateID
			GROUP BY dd.year,dd.month_of_year,AccountName
		)tbl;

	END IF;

	IF p_ReportType = 'Yearly' AND p_isExport = 0
	THEN

		SELECT 
			MAX(UserName),
			AccountName,
			dd.year as TIMEVAL,
			ROUND(COALESCE(SUM(TotalCharges),0), 2) as TotalCost,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), 2) as TotalMargin,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, 2) as MarginPercentage,
			CONCAT(MIN(us.Date),' ## ',MAX(us.Date)) as DATERANGE
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tblDimDate dd ON dd.DateID = us.DateID
		GROUP BY  dd.year,AccountName
		ORDER BY
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'UserNameDESC') THEN MAX(UserName)
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'UserNameASC') THEN MAX(UserName)
			END ASC,
				CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN AccountName
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN AccountName
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TIMEVALDESC') THEN dd.year
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TIMEVALASC') THEN dd.year
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostDESC') THEN COALESCE(SUM(TotalCharges),0)
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalCostASC') THEN COALESCE(SUM(TotalCharges),0)
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginDESC') THEN COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'TotalMarginASC') THEN COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageDESC') THEN (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MarginPercentageASC') THEN (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100
			END ASC;

		SELECT COUNT(*) AS totalcount,SUM(TotalMargin) AS TotalMargin,SUM(TotalCost) AS TotalCost
		FROM (
			SELECT
				ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,
				ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as TotalMargin
			FROM tmp_tblUsageSummary_ us
			INNER JOIN tblDimDate dd ON dd.DateID = us.DateID
			GROUP BY dd.year,AccountName
		)tbl;

	END IF;

	IF p_ReportType = 'Daily' AND p_isExport = 1
	THEN

		SELECT 
			MAX(UserName) AS `Account Manager`,
			AccountName AS `Account`,
			us.Date as `Period`,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as `Revenue`,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as `Margin`,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as `Margin(%)`
		FROM tmp_tblUsageSummary_ us
		GROUP BY us.Date,us.AccountName;

	END IF;

	IF p_ReportType = 'Weekly' AND p_isExport = 1
	THEN

		SELECT 
			MAX(UserName) AS `Account Manager`,
			AccountName AS `Account`,
			CONCAT(dd.year,'-',dd.week_of_year) as `Period`,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as `Revenue`,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as `Margin`,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as `Margin(%)`
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tblDimDate dd ON dd.DateID = us.DateID
		GROUP BY  dd.year,dd.week_of_year,AccountName;

	END IF;

	IF p_ReportType = 'Monthly' AND p_isExport = 1
	THEN

		SELECT 
			MAX(UserName) AS `Account Manager`,
			AccountName AS `Account`,
			CONCAT(dd.year,'-',dd.month_of_year) as `Period`,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as `Revenue`,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as `Margin`,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as `Margin(%)`
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tblDimDate dd ON dd.DateID = us.DateID
		GROUP BY  dd.year,dd.month_of_year,AccountName;

	END IF;

	IF p_ReportType = 'Yearly' AND p_isExport = 1
	THEN

		SELECT 
			MAX(UserName) AS `Account Manager`,
			AccountName AS `Account`,
			dd.year as `Period`,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as `Revenue`,
			ROUND(COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0), v_Round_) as `Margin`,
			ROUND( (COALESCE(SUM(TotalCharges),0) - COALESCE(SUM(TotalCost),0)) / SUM(TotalCharges)*100, v_Round_) as `Margin(%)`
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tblDimDate dd ON dd.DateID = us.DateID
		GROUP BY  dd.year,AccountName;

	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END