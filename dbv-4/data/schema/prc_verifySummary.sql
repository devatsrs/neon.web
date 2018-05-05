CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_verifySummary`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_StartDate` DATETIME, IN `p_EndDate` DATETIME)
BEGIN

	DROP TEMPORARY TABLE IF EXISTS tmp_verify_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_verify_(
	   AccountID INT,
	   TotalCall INT,
	   TotalCharges decimal(18,6),
	   TotalSecond BIGINT,
	   Summary INT,
	   CDR INT
   );
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	CALL NeonBillingDev.fnUsageDetail(p_CompanyID,p_AccountID,0,p_StartDate,p_EndDate,0,1,1,'','','',0);
	
	INSERT INTO tmp_verify_ (AccountID,TotalCall,TotalCharges,TotalSecond,Summary,CDR)
	SELECT p_AccountID,COUNT(*) as TotalCall,SUM(cost) as TotalCost,SUM(billed_duration) as TotalSecond,0,1 FROM NeonBillingDev.tmp_tblUsageDetails_;
	
	CALL fnUsageSummary(p_CompanyID,0,p_AccountID,0,p_StartDate,p_EndDate,'','',0,0,1,1);
	
	INSERT INTO tmp_verify_ (AccountID,TotalCall,TotalCharges,TotalSecond,Summary,CDR)
	SELECT p_AccountID,SUM(NoOfCalls) as TotalCall,SUM(TotalCharges) as TotalCost,SUM(TotalBilledDuration) as TotalSecond,1,0 FROM tmp_tblUsageSummary_;
	
	SELECT * FROM tmp_verify_;

	


END