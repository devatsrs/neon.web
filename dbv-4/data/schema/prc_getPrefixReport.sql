CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getPrefixReport`(IN `p_CompanyID` INT, IN `p_UserID` INT, IN `p_isAdmin` INT, IN `p_AccountID` INT)
BEGIN

	DECLARE v_Round_ int;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
		 
	CALL fnUsageSummary(p_CompanyID,0,p_AccountID,DATE(NOW()),DATE(NOW()),'','',0,p_UserID,p_isAdmin,1);
	
	/* top 10 prefix by call count */	
	SELECT AreaPrefix as ChartVal ,SUM(NoOfCalls) AS CallCount, (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)) as ACD FROM tmp_tblUsageSummary_ WHERE AreaPrefix != 'other' GROUP BY AreaPrefix ORDER BY CallCount DESC LIMIT 10;
	
	/* top 10 prefix by call cost */	
	SELECT AreaPrefix as ChartVal,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost, (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)) as ACD FROM tmp_tblUsageSummary_ WHERE AreaPrefix != 'other' GROUP BY AreaPrefix ORDER BY TotalCost DESC LIMIT 10;
	
	/* top 10 prefix by call minutes */	
	SELECT AreaPrefix as ChartVal,ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalMinutes, (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)) as ACD FROM tmp_tblUsageSummary_ WHERE AreaPrefix != 'other' GROUP BY AreaPrefix ORDER BY TotalMinutes DESC LIMIT 10;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END