CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getDestinationReport`(IN `p_CompanyID` INT, IN `p_UserID` INT, IN `p_isAdmin` INT, IN `p_AccountID` INT)
BEGIN

	DECLARE v_Round_ int;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	CALL fnGetCountry();
		 
	CALL fnUsageSummary(p_CompanyID,0,p_AccountID,DATE(NOW()),DATE(NOW()),'','',0,p_UserID,p_isAdmin);
	
	
	/* top 10 country by call count */	
	SELECT Country as ChartVal ,COUNT(*) AS CallCount,(COALESCE(SUM(TotalBilledDuration),0)/COUNT(*)) as ACD 
	FROM tmp_tblUsageSummary_ us
	INNER JOIN temptblCountry c ON c.CountryID = us.CountryID
	GROUP BY Country HAVING COUNT(*) > 0 ORDER BY CallCount DESC LIMIT 10;
	
	/* top 10 country by call cost */	
	SELECT Country as ChartVal,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost,(COALESCE(SUM(TotalBilledDuration),0)/COUNT(*)) as ACD 
	FROM tmp_tblUsageSummary_ us
	INNER JOIN temptblCountry c ON c.CountryID = us.CountryID
	GROUP BY Country HAVING SUM(TotalCharges) > 0 ORDER BY TotalCost DESC LIMIT 10;
	
	/* top 10 country by call minutes */	
	SELECT Country as ChartVal,ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalMinutes,(COALESCE(SUM(TotalBilledDuration),0)/COUNT(*)) as ACD 
	FROM tmp_tblUsageSummary_ us
	INNER JOIN temptblCountry c ON c.CountryID = us.CountryID
	GROUP BY Country HAVING SUM(TotalBilledDuration) > 0  ORDER BY TotalMinutes DESC LIMIT 10;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END