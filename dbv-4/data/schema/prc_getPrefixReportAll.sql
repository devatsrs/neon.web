CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getPrefixReportAll`(IN `p_CompanyID` INT, IN `p_CompanyGatewayID` INT, IN `p_AccountID` INT, IN `p_StartDate` DATE, IN `p_EndDate` DATE, IN `p_AreaPrefix` VARCHAR(50), IN `p_Trunk` VARCHAR(50), IN `p_CountryID` INT, IN `p_UserID` INT, IN `p_isAdmin` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT)
BEGIN

	DECLARE v_Round_ int;
	DECLARE v_OffSet_ int;
		
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	CALL fnUsageSummary(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,p_UserID,p_isAdmin,1);

	
	/* grid display*/
	IF p_isExport = 0
	THEN
	
	/* AreaPrefix by call count */	
	SELECT SQL_CALC_FOUND_ROWS AreaPrefix ,SUM(NoOfCalls) AS CallCount,COALESCE(SUM(TotalBilledDuration),0) as TotalSeconds,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)) as ACD , SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100 as ASR
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
   END ASC
	LIMIT p_RowspPage OFFSET v_OffSet_;
	
	SELECT FOUND_ROWS() as totalcount;

	
	END IF;
	
	/* export data*/
	IF p_isExport = 1
	THEN
		SELECT   AreaPrefix ,SUM(NoOfCalls) AS CallCount,COALESCE(SUM(TotalBilledDuration),0) as TotalSeconds,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)) as ACD , SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100 as ASR
		FROM tmp_tblUsageSummary_ us
		GROUP BY AreaPrefix;
	END IF;
	
	
	/* chart display*/
	IF p_isExport = 2
	THEN
	
		/* top 10 AreaPrefix by call count */	
		SELECT AreaPrefix as ChartVal ,SUM(NoOfCalls) AS CallCount,(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)) as ACD , SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100 as ASR
		FROM tmp_tblUsageSummary_ us
		WHERE us.AreaPrefix != 'Other'
		GROUP BY AreaPrefix HAVING COUNT(*) > 0 ORDER BY CallCount DESC LIMIT 10;
		
		/* top 10 AreaPrefix by call cost */	
		SELECT AreaPrefix as ChartVal,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)) as ACD , SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100 as ASR
		FROM tmp_tblUsageSummary_ us
		WHERE us.AreaPrefix != 'Other'
		GROUP BY AreaPrefix HAVING SUM(TotalCharges) > 0 ORDER BY TotalCost DESC LIMIT 10;
		
		/* top 10 AreaPrefix by call minutes */	
		SELECT AreaPrefix as ChartVal,ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalMinutes,(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)) as ACD , SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100 as ASR
		FROM tmp_tblUsageSummary_ us
		WHERE us.AreaPrefix != 'Other'
		GROUP BY AreaPrefix HAVING SUM(TotalBilledDuration) > 0  ORDER BY TotalMinutes DESC LIMIT 10;
	
 	END IF;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END