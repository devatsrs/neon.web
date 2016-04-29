CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getTopPrefix`(IN `p_CompanyID` INT, IN `p_UserID` INT, IN `p_isAdmin` INT, IN `p_AccountID` INT)
BEGIN

	DECLARE v_Round_ int;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
		 
	CALL fnUsageDetail(p_CompanyID,p_AccountID,0,DATE(NOW()),CONCAT(DATE(NOW()),' 23:59:59'),p_UserID,p_isAdmin,1,'','','',0);
	
	/* top 5 prefix by call count */	
	SELECT area_prefix ,COUNT(*) AS CallCount FROM tmp_tblUsageDetails_ WHERE area_prefix != 'other' GROUP BY area_prefix ORDER BY CallCount DESC LIMIT 5;
	
	/* top 5 prefix by call cost */	
	SELECT area_prefix ,ROUND(COALESCE(SUM(cost),0), v_Round_) as TotalCost FROM tmp_tblUsageDetails_ WHERE area_prefix != 'other' GROUP BY area_prefix ORDER BY TotalCost DESC LIMIT 5;
	
	/* top 5 prefix by call minutes */	
	SELECT area_prefix ,ROUND(COALESCE(SUM(billed_duration),0)/ 60,0) as TotalMinutes FROM tmp_tblUsageDetails_ WHERE area_prefix != 'other' GROUP BY area_prefix ORDER BY TotalMinutes DESC LIMIT 5;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END