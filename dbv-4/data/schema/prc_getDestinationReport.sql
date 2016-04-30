CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getDestinationReport`(IN `p_CompanyID` INT, IN `p_UserID` INT, IN `p_isAdmin` INT, IN `p_AccountID` INT)
BEGIN

	DECLARE v_Round_ int;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	CALL fnGetCountry();
		 
	CALL fnUsageDetail(p_CompanyID,p_AccountID,0,DATE(NOW()),CONCAT(DATE(NOW()),' 23:59:59'),p_UserID,p_isAdmin,1,'','','',0);
	
	
	/* top 10 Trunk by call count */	
	SELECT C.Country as ChartVal ,COUNT(*) AS CallCount,(COALESCE(SUM(billed_duration),0)/COUNT(*)) as ACD 
	FROM tmp_tblUsageDetails_ ud
	INNER JOIN temptblCountry c ON c.Prefix = ud.area_prefix
	GROUP BY Trunk HAVING COUNT(*) > 0 ORDER BY CallCount DESC LIMIT 10;
	
	/* top 10 Trunk by call cost */	
	SELECT C.Country as ChartVal,ROUND(COALESCE(SUM(cost),0), v_Round_) as TotalCost,(COALESCE(SUM(billed_duration),0)/COUNT(*)) as ACD 
	FROM tmp_tblUsageDetails_ ud
	INNER JOIN temptblCountry c ON c.Prefix = ud.area_prefix
	GROUP BY Trunk HAVING SUM(cost) > 0 ORDER BY TotalCost DESC LIMIT 10;
	
	/* top 10 Trunk by call minutes */	
	SELECT C.Country as ChartVal,ROUND(COALESCE(SUM(billed_duration),0)/ 60,0) as TotalMinutes,(COALESCE(SUM(billed_duration),0)/COUNT(*)) as ACD 
	FROM tmp_tblUsageDetails_ ud
	INNER JOIN temptblCountry c ON c.Prefix = ud.area_prefix
	GROUP BY Trunk HAVING SUM(billed_duration) > 0  ORDER BY TotalMinutes DESC LIMIT 10;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END