CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getHourlyReport`(IN `p_CompanyID` INT, IN `p_UserID` INT, IN `p_isAdmin` INT, IN `p_AccountID` INT)
BEGIN
	
	DECLARE v_Round_ int;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	CALL fnUsageDetail(p_CompanyID,p_AccountID,0,DATE(NOW()),CONCAT(DATE(NOW()),' 23:59:59'),p_UserID,p_isAdmin,1,'','','',0);
	
	/* total cost */
	SELECT ROUND(COALESCE(SUM(cost),0),v_Round_) as TotalCost FROM tmp_tblUsageDetails_;
	
	/* cost per hour*/
	SELECT HOUR(connect_time) as HOUR ,ROUND(COALESCE(SUM(cost),0),v_Round_) as TotalCost FROM tmp_tblUsageDetails_ GROUP BY HOUR(connect_time);
	
	/* total duration or minutes*/
	SELECT ROUND(COALESCE(SUM(billed_duration),0)/ 60,0) as TotalMinutes FROM tmp_tblUsageDetails_;
	
	/* minutes pre hour*/
	SELECT HOUR(connect_time) as HOUR ,ROUND(COALESCE(SUM(billed_duration),0) / 60,0) as TotalMinutes FROM tmp_tblUsageDetails_ GROUP BY HOUR(connect_time);
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END