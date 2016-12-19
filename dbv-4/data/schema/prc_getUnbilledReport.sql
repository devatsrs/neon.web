CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getUnbilledReport`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_LastInvoiceDate` DATETIME,
	IN `p_Today` DATETIME,
	IN `p_Detail` INT
)
BEGIN
	
	DECLARE v_Round_ INT;
	DECLARE v_Detail_ INT;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	IF p_Detail = 3
	THEN 
		SET v_Detail_ = 1;
	ELSE 
		SET v_Detail_ = p_Detail;
	END IF;
	
	
	CALL fnUsageSummary(p_CompanyID,0,p_AccountID,0,p_LastInvoiceDate,p_Today,'','',0,0,1,v_Detail_);
	
	IF p_Detail = 1
	THEN
	
		SELECT 
			dd.date,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tblDimDate dd on dd.DateID = us.DateID
		GROUP BY us.DateID;
	
	END IF;
	
	IF p_Detail = 2
	THEN
	
		SELECT 
			dd.date,
			dt.fulltime,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tmp_tblUsageSummary_ us
		INNER JOIN tblDimDate dd on dd.DateID = us.DateID
		INNER JOIN tblDimTime dt on dt.TimeID = us.TimeID
		GROUP BY us.DateID,us.TimeID;
	
	END IF;
	
	IF p_Detail = 3
	THEN
	
		DROP TEMPORARY TABLE IF EXISTS tmp_FinalAmount_;
		CREATE TEMPORARY TABLE tmp_FinalAmount_  (
			FinalAmount DOUBLE
		);
		INSERT INTO tmp_FinalAmount_
		SELECT 
		ROUND(COALESCE(SUM(TotalCharges),0), v_Round_)
		FROM tmp_tblUsageSummary_ us;
	
	END IF;
 
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END