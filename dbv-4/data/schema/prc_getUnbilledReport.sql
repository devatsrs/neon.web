CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getUnbilledReport`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_LastInvoiceDate` DATETIME,
	IN `p_Today` DATETIME,
	IN `p_Detail` INT
)
BEGIN
	
	DECLARE v_Round_ INT;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	
	IF p_Detail = 1
	THEN
	
		SELECT 
			dd.date,
			ROUND(COALESCE(SUM(TotalBilledDuration),0)/60,0) as TotalMinutes,
			ROUND(COALESCE(SUM(TotalCharges),0), v_Round_) as TotalCost
		FROM tblHeader us
		INNER JOIN tblDimDate dd ON dd.DateID = us.DateID
		WHERE dd.date BETWEEN p_LastInvoiceDate AND p_Today 
		AND us.CompanyID = p_CompanyID
		AND us.AccountID = p_AccountID
		GROUP BY us.DateID;	
		
	
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
		FROM tblHeader us
		INNER JOIN tblDimDate dd ON dd.DateID = us.DateID
		WHERE dd.date BETWEEN p_LastInvoiceDate AND p_Today 
		AND us.CompanyID = p_CompanyID
		AND us.AccountID = p_AccountID;
		
	END IF;
 
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END