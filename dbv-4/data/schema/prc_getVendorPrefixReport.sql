CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getVendorPrefixReport`(IN `p_CompanyID` INT, IN `p_UserID` INT, IN `p_isAdmin` INT, IN `p_AccountID` INT)
BEGIN

	DECLARE v_Round_ int;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
		 
	CALL fnUsageVendorSummary(p_CompanyID,0,p_AccountID,0,DATE(NOW()),DATE(NOW()),'','',0,p_UserID,p_isAdmin,1);
	
	/* top 10 prefix by call count */	
	SELECT AreaPrefix as ChartVal ,SUM(NoOfCalls) AS CallCount, fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR FROM tmp_tblUsageVendorSummary_ WHERE AreaPrefix != 'other' GROUP BY AreaPrefix ORDER BY CallCount DESC LIMIT 10;
	
	/* top 10 prefix by call cost */	
	SELECT AreaPrefix as ChartVal,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost, fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR FROM tmp_tblUsageVendorSummary_ WHERE AreaPrefix != 'other' GROUP BY AreaPrefix ORDER BY TotalCost DESC LIMIT 10;
	
	/* top 10 prefix by call minutes */	
	SELECT AreaPrefix as ChartVal,ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalMinutes, fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)) as ACD , ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR FROM tmp_tblUsageVendorSummary_ WHERE AreaPrefix != 'other' GROUP BY AreaPrefix ORDER BY TotalMinutes DESC LIMIT 10;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END