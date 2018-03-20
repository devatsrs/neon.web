CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_getACD_ASR_Alert`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` TEXT,
	IN `p_AccountID` TEXT,
	IN `p_CurrencyID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_AreaPrefix` TEXT,
	IN `p_Trunk` TEXT,
	IN `p_CountryID` TEXT
)
BEGIN
	
	DECLARE v_Round_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	CALL fnUsageSummaryDetail(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,0,1);

	IF p_AccountID = ''
	THEN
		SELECT
			IF(SUM(NoOfCalls)>0,COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls),0) as ACD , 
			ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			HOUR(ANY_VALUE(Time)) as Hour,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as Minutes,
			COALESCE(SUM(NoOfCalls),0) as Connected,
			COALESCE(SUM(NoOfCalls),0)+COALESCE(SUM(NoOfFailCalls),0) as Attempts
		FROM tmp_tblUsageSummary_ us;
		
	END IF;
	
	IF p_AccountID != ''
	THEN
		SELECT
			IF(SUM(NoOfCalls)>0,COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls),0) as ACD , 
			ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR,
			AccountID,
			HOUR(ANY_VALUE(Time)) as Hour,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as Minutes,
			COALESCE(SUM(NoOfCalls),0) as Connected,
			COALESCE(SUM(NoOfCalls),0)+COALESCE(SUM(NoOfFailCalls),0) as Attempts
		FROM tmp_tblUsageSummary_ us
		GROUP BY AccountID;
	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END