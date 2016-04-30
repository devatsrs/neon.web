CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getPrefixReport`(IN `p_CompanyID` INT, IN `p_UserID` INT, IN `p_isAdmin` INT, IN `p_AccountID` INT)
BEGIN

	DECLARE v_Round_ int;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
		 
	CALL fnUsageDetail(p_CompanyID,p_AccountID,0,DATE(NOW()),CONCAT(DATE(NOW()),' 23:59:59'),p_UserID,p_isAdmin,1,'','','',0);
	
	/* top 10 prefix by call count */	
	SELECT area_prefix as ChartVal ,COUNT(*) AS CallCount, (COALESCE(SUM(billed_duration),0)/COUNT(*)) as ACD FROM tmp_tblUsageDetails_ WHERE area_prefix != 'other' GROUP BY area_prefix HAVING COUNT(*) > 0 ORDER BY CallCount DESC LIMIT 10;
	
	/* top 10 prefix by call cost */	
	SELECT area_prefix as ChartVal,ROUND(COALESCE(SUM(cost),0), v_Round_) as TotalCost, (COALESCE(SUM(billed_duration),0)/COUNT(*)) as ACD FROM tmp_tblUsageDetails_ WHERE area_prefix != 'other' GROUP BY area_prefix HAVING SUM(cost) > 0 ORDER BY TotalCost DESC LIMIT 10;
	
	/* top 10 prefix by call minutes */	
	SELECT area_prefix as ChartVal,ROUND(COALESCE(SUM(billed_duration),0)/ 60,0) as TotalMinutes, (COALESCE(SUM(billed_duration),0)/COUNT(*)) as ACD FROM tmp_tblUsageDetails_ WHERE area_prefix != 'other' GROUP BY area_prefix HAVING SUM(billed_duration)>0 ORDER BY TotalMinutes DESC LIMIT 10;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END