CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getVendorACD_ASR_Alert`(IN `p_CompanyID` INT, IN `p_CompanyGatewayID` TEXT, IN `p_AccountID` TEXT, IN `p_CurrencyID` INT, IN `p_StartDate` DATETIME, IN `p_EndDate` DATETIME, IN `p_AreaPrefix` TEXT, IN `p_Trunk` TEXT, IN `p_CountryID` TEXT)
BEGIN
	
	DECLARE v_Round_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	CALL fnUsageVendorSummaryDetail(p_CompanyID,p_CompanyGatewayID,p_AccountID,p_CurrencyID,p_StartDate,p_EndDate,p_AreaPrefix,p_Trunk,p_CountryID,0,1);
	
	SELECT
		IF(SUM(NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(TotalBilledDuration),0)/SUM(NoOfCalls)),0) as ACD , 
		ROUND(SUM(NoOfCalls)/(SUM(NoOfCalls)+SUM(NoOfFailCalls))*100,v_Round_) as ASR
	FROM tmp_tblUsageVendorSummary_ us;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END