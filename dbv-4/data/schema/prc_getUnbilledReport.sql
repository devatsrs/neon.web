CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getUnbilledReport`(IN `p_CompanyID` INT, IN `p_AccountID` INT, IN `p_LastInvoiceDate` DATETIME, IN `p_Detail` INT)
BEGIN
	
	DECLARE v_Round_ INT;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	CALL fnUsageSummary(p_CompanyID,0,p_AccountID,0,p_LastInvoiceDate,CONCAT(DATE(NOW()),' 23:59:59'),'','',0,0,1,p_Detail);
	
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
 
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END