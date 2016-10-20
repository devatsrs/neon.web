CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getACD_ASR_Alert`(IN `p_CompanyID` INT, IN `p_CompanyGatewayID` INT, IN `p_AccountID` INT, IN `p_CurrencyID` INT, IN `p_StartDate` DATE, IN `p_EndDate` DATE, IN `p_AreaPrefix` VARCHAR(50), IN `p_Trunk` VARCHAR(50), IN `p_CountryID` INT)
BEGIN
	
	DECLARE v_Round_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	CALL fnUsageSummary(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,0,1,1);
	
	SELECT
		IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , 
		ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR
	FROM tmp_tblUsageSummary_ us;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END