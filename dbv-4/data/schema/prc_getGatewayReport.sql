CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getGatewayReport`(IN `p_CompanyID` INT, IN `p_UserID` INT, IN `p_isAdmin` INT, IN `p_AccountID` INT)
BEGIN

	DECLARE v_Round_ int;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
		 
	CALL fnUsageSummary(p_CompanyID,0,p_AccountID,DATE(NOW()),DATE(NOW()),'','',0,p_UserID,p_isAdmin);
	
	/* top 10 gateway by call count */	
	SELECT fnGetCompanyGatewayName(CompanyGatewayID) as ChartVal ,SUM(NoOfCalls) AS CallCount, (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)) as ACD FROM tmp_tblUsageSummary_  GROUP BY CompanyGatewayID ORDER BY CallCount DESC LIMIT 10;
	
	/* top 10 gateway by call cost */	
	SELECT fnGetCompanyGatewayName(CompanyGatewayID) as ChartVal,ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost, (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)) as ACD FROM tmp_tblUsageSummary_  GROUP BY CompanyGatewayID ORDER BY TotalCost DESC LIMIT 10;
	
	/* top 10 gateway by call minutes */	
	SELECT fnGetCompanyGatewayName(CompanyGatewayID) as ChartVal,ROUND(COALESCE(SUM(TotalBilledDuration),0)/ 60,0) as TotalMinutes, (COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)) as ACD FROM tmp_tblUsageSummary_  GROUP BY CompanyGatewayID  ORDER BY TotalMinutes DESC LIMIT 10;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END