CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getAccountExpense`(IN `p_CompanyID` INT, IN `p_AccountID` INT)
BEGIN
	DECLARE v_Round_ int;
	DECLARE v_DateID_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	SELECT MIN(DateID) INTO v_DateID_ FROM tblDimDate WHERE fnGetMonthDifference(date,NOW()) <= 12;

	DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageSummary_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageSummary_(
		`DateID` BIGINT(20) NOT NULL,
		`CompanyID` INT(11) NOT NULL,
		`AccountID` INT(11) NOT NULL,
		`AreaPrefix` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
		`TotalCharges` DOUBLE NULL DEFAULT NULL,
		`CustomerVendor` INT,
		INDEX `tmp_tblUsageSummary_DateID` (`DateID`)
	);
	DROP TEMPORARY TABLE IF EXISTS tmp_tblUsageSummary2_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblUsageSummary2_(
		`DateID` BIGINT(20) NOT NULL,
		`CompanyID` INT(11) NOT NULL,
		`AccountID` INT(11) NOT NULL,
		`AreaPrefix` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
		`TotalCharges` DOUBLE NULL DEFAULT NULL,
		`CustomerVendor` INT,
		INDEX `tmp_tblUsageSummary_DateID` (`DateID`)
	);
	DROP TEMPORARY TABLE IF EXISTS tmp_tblCustomerPrefix_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblCustomerPrefix_(
		`AreaPrefix` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
		`CustomerTotal` DOUBLE NULL DEFAULT NULL,
		`FinalTotal` DOUBLE NULL DEFAULT NULL,
		`YearMonth` VARCHAR(50) NOT NULL
	);
	DROP TEMPORARY TABLE IF EXISTS tmp_tblVendorPrefix_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblVendorPrefix_(
		`AreaPrefix` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
		`VendorTotal` DOUBLE NULL DEFAULT NULL,
		`FinalTotal` DOUBLE NULL DEFAULT NULL,
		`YearMonth` VARCHAR(50) NOT NULL
	);
	
	/* insert customer summary */
	INSERT INTO tmp_tblUsageSummary_
	SELECT
		sh.DateID,
		sh.CompanyID,
		sh.AccountID,
		sh.AreaPrefix,
		us.TotalCharges,
		1 as Customer
	FROM tblSummaryHeader sh
	INNER JOIN tblUsageSummary us
		ON us.SummaryHeaderID = sh.SummaryHeaderID 
	WHERE  sh.CompanyID = p_CompanyID
	AND sh.AccountID = p_AccountID;
	
	/* insert vendor summary */
	INSERT INTO tmp_tblUsageSummary_
	SELECT
		sh.DateID,
		sh.CompanyID,
		sh.AccountID,
		sh.AreaPrefix,
		us.TotalCharges,
		2 as Vendor
	FROM tblSummaryVendorHeader sh
	INNER JOIN tblUsageVendorSummary us
		ON us.SummaryVendorHeaderID = sh.SummaryVendorHeaderID 
	WHERE  sh.CompanyID = p_CompanyID
	AND sh.AccountID = p_AccountID;
	
	INSERT INTO tmp_tblUsageSummary2_
	SELECT * FROM tmp_tblUsageSummary_;
	
	/* customer and vendor chart by month and year */
	SELECT 
		ROUND(SUM(IF(CustomerVendor=1,TotalCharges,0)),v_Round_) AS  CustomerTotal,
		ROUND(SUM(IF(CustomerVendor=2,TotalCharges,0)),v_Round_) AS  VendorTotal,
		dd.year as Year,
		dd.month_of_year as Month
	FROM tmp_tblUsageSummary_ us 
	INNER JOIN tblDimDate dd ON dd.DateID = us.DateID
	GROUP BY dd.year,dd.month_of_year;
	
	/* top 5 customer destination month and year */
	INSERT INTO tmp_tblCustomerPrefix_
	SELECT 
		us.AreaPrefix,
		ROUND(SUM(TotalCharges),2) AS  CustomerTotal,
		FinalTotal,
		CONCAT(dd.year,'-',dd.month_of_year) as YearMonth
	FROM tmp_tblUsageSummary_ us 
	INNER JOIN 
	(SELECT SUM(TotalCharges) as FinalTotal,AreaPrefix FROM tmp_tblUsageSummary2_ WHERE CustomerVendor = 1 AND AreaPrefix != 'other' AND DateID >= v_DateID_ GROUP BY AreaPrefix ORDER BY FinalTotal DESC LIMIT 5 ) tbl
	ON tbl.AreaPrefix = us.AreaPrefix
	INNER JOIN tblDimDate dd ON dd.DateID = us.DateID
	WHERE 
			 us.CustomerVendor = 1 
		AND us.AreaPrefix != 'other'
		AND dd.DateID >= v_DateID_
	GROUP BY dd.year,dd.month_of_year,us.AreaPrefix
	ORDER BY FinalTotal DESC ,dd.year,dd.month_of_year;
	
	/* convert into pivot table*/
	
	IF (SELECT COUNT(*) FROM tmp_tblCustomerPrefix_) > 0
	THEN
		SET @sql = NULL;
		
		SELECT
			GROUP_CONCAT( DISTINCT CONCAT('MAX(IF(YearMonth = ''',YearMonth,''', CustomerTotal, 0)) AS ''',YearMonth,'''') ) INTO @sql
		FROM tmp_tblCustomerPrefix_;
	
		SET @sql = CONCAT('
							SELECT AreaPrefix , ', @sql, ' 
							FROM tmp_tblCustomerPrefix_ 
							GROUP BY AreaPrefix
							ORDER BY FinalTotal DESC
						');
		
		PREPARE stmt FROM @sql;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
	ELSE
		SELECT 0;
	END IF;

	/* top 5 vendor destination month and year */
	INSERT INTO tmp_tblVendorPrefix_
	SELECT 
		us.AreaPrefix,
		ROUND(SUM(TotalCharges),2) AS  VendorTotal,
		FinalTotal,
		CONCAT(dd.year,'-',dd.month_of_year) as YearMonth
	FROM tmp_tblUsageSummary_ us 
	INNER JOIN 
	(SELECT SUM(TotalCharges) as FinalTotal,AreaPrefix FROM tmp_tblUsageSummary2_ WHERE CustomerVendor = 2 AND AreaPrefix != 'other' AND DateID >= v_DateID_ GROUP BY AreaPrefix ORDER BY FinalTotal DESC LIMIT 5 ) tbl
	ON tbl.AreaPrefix = us.AreaPrefix
	INNER JOIN tblDimDate dd ON dd.DateID = us.DateID
	WHERE 
			 us.CustomerVendor = 2 
		AND us.AreaPrefix != 'other'
		AND dd.DateID >= v_DateID_
	GROUP BY dd.year,dd.month_of_year,us.AreaPrefix
	ORDER BY FinalTotal DESC ,dd.year,dd.month_of_year;

	/* convert into pivot table*/
	
	IF (SELECT COUNT(*) FROM tmp_tblVendorPrefix_) > 0
	THEN
	
		SET @stm = NULL;
		SELECT
			GROUP_CONCAT( DISTINCT CONCAT('MAX(IF(YearMonth = ''',YearMonth,''', CustomerTotal, 0)) AS ''',YearMonth,'''') ) INTO @stm
		FROM tmp_tblVendorPrefix_;

		SET @stm = CONCAT('
							SELECT AreaPrefix , ', @stm, ' 
							FROM tmp_tblVendorPrefix_ 
							GROUP BY AreaPrefix
							ORDER BY FinalTotal DESC
						');
		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
	ELSE
		SELECT 0;	
	END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END