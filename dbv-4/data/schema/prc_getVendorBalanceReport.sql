CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getVendorBalanceReport`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` TEXT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME


)
BEGIN
	
	DECLARE v_Round_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	CALL fnUsageVendorSummaryDetail(p_CompanyID,'',p_AccountID,0,p_StartDate,p_EndDate,'','','',0,1);

	SELECT
		MAX(AccountName) as AccountName,
		IF(SUM(NoOfCalls)>0,COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls),0) as ACD , 
		ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
		AccountID,
		DATE(Time) as Date,
		HOUR(Time) as Hour,
		COALESCE(SUM(TotalCharges),0) as Cost,
		COALESCE(SUM(TotalBilledDuration),0) as Minutes,
		COALESCE(SUM(NoOfCalls),0) as Connected,
		COALESCE(SUM(NoOfCalls),0)+COALESCE(SUM(NoOfFailCalls),0) as Attempts
	FROM tmp_tblUsageVendorSummary_ us
	GROUP BY AccountID,DATE(Time),HOUR(Time);

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END