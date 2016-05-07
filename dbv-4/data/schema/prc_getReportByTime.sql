CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getReportByTime`(IN `p_CompanyID` INT, IN `p_CompanyGatewayID` INT, IN `p_AccountID` INT, IN `p_StartDate` DATE, IN `p_EndDate` DATE, IN `p_AreaPrefix` VARCHAR(50), IN `p_Trunk` VARCHAR(50), IN `p_CountryID` INT, IN `p_UserID` INT, IN `p_isAdmin` INT, IN `p_ReportType` INT)
BEGIN
	
	DECLARE v_Round_ int;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	CALL fnUsageSummary(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,p_UserID,p_isAdmin);
	
	/* hourly report */
	IF p_ReportType =1
	THEN
	
		/* report by hour*/
		SELECT 
			CONCAT(dt.hour,' Hour') as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tblDimTime dt on dt.time_id = us.time_id
		GROUP BY  us.time_id,us.date_id;
		
	END IF;
	
	/* daily report */
	IF p_ReportType =2
	THEN
	
		/* report by daily*/
		SELECT 
			 dd.date as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tblDimDate dd on dd.date_id = us.date_id
		GROUP BY  us.date_id;
		
	END IF;
	/* weekly report */
	IF p_ReportType =3
	THEN
	
		/* report by weekly*/
		SELECT 
			 dd.week_year_name as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tblDimDate dd on dd.date_id = us.date_id
		GROUP BY  dd.week_of_year;
		
	END IF;
	/* monthly report */
	IF p_ReportType =4
	THEN
	
		/* report by monthly*/
		SELECT 
			 dd.month_year_name as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tblDimDate dd on dd.date_id = us.date_id
		GROUP BY  dd.month_of_year;
		
	END IF;
	
	/* queterly report */
	IF p_ReportType =5
	THEN
	
		/* report by monthly*/
		SELECT 
			 dd.quarter_year_name as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tblDimDate dd on dd.date_id = us.date_id
		GROUP BY  dd.quarter_of_year;
		
	END IF;
	
	/* yearly report */
	IF p_ReportType =6
	THEN
	
		/* report by monthly*/
		SELECT 
			 dd.year as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tblDimDate dd on dd.date_id = us.date_id
		GROUP BY  dd.year;
		
	END IF;
	
	
	/* monthly report */
	IF p_ReportType =7
	THEN
	
		/* report by monthly*/
		SELECT 
			 dd.month_of_year as category,
			SUM(NoOfCalls) AS CallCount,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tblDimDate dd on dd.date_id = us.date_id
		GROUP BY  dd.month_of_year;
		
	END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END