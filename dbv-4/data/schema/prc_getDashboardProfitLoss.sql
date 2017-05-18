CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getDashboardProfitLoss`(
	IN `p_CompanyID` INT,
	IN `p_CurrencyID` INT,
	IN `p_AccountID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_ListType` VARCHAR(50)
)
BEGIN
	DECLARE v_Round_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	DROP TEMPORARY TABLE IF EXISTS tmp_Customerbilled_;
	CREATE TEMPORARY TABLE tmp_Customerbilled_  (
		DateID INT,
		Customerbill DOUBLE
	);
	DROP TEMPORARY TABLE IF EXISTS tmp_Vendorbilled_;
	CREATE TEMPORARY TABLE tmp_Vendorbilled_  (
		DateID INT,
		Vendrorbill DOUBLE
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_FinalResult_;
	CREATE TEMPORARY TABLE tmp_FinalResult_  (
		Customerbill DOUBLE,
		Vendrorbill DOUBLE,
		date DATE
	);

	INSERT INTO tmp_Customerbilled_(DateID,Customerbill)
	SELECT 
		dd.DateID,
		SUM(h.TotalCharges)
	FROM tblDimDate dd
	INNER JOIN tblHeader h
		ON h.DateID = dd.DateID
	WHERE dd.date BETWEEN p_StartDate AND p_EndDate
	AND (p_AccountID = 0 or AccountID = p_AccountID)
	GROUP BY dd.date;

	INSERT INTO tmp_Vendorbilled_ (DateID,Vendrorbill)
	SELECT 
		dd.DateID,
		SUM(h.TotalCharges)
	FROM tblDimDate dd
	INNER JOIN tblHeaderV h
		ON h.DateID = dd.DateID
	WHERE dd.date BETWEEN p_StartDate AND p_EndDate
	AND (p_AccountID = 0 or VAccountID = p_AccountID)
	GROUP BY dd.date;

	INSERT INTO tmp_FinalResult_(Customerbill,Vendrorbill,date)
	SELECT 
		IFNULL(Customerbill,0) AS Customerbill,
		IFNULL(Vendrorbill,0) AS Vendrorbill,
		date
	FROM(
		SELECT 
			dd.date,
			Customerbill,
			Vendrorbill
		FROM tblDimDate dd 
		LEFT JOIN tmp_Customerbilled_ cu 
			ON cu.DateID = dd.DateID
		LEFT JOIN tmp_Vendorbilled_ vu
			ON vu.DateID = dd.DateID
		WHERE dd.date BETWEEN p_StartDate AND p_EndDate
		AND (cu.DateID IS NOT NULL OR vu.DateID IS NOT NULL)
		ORDER BY dd.date
	)tbl;
	
	IF p_ListType = 'Daily'
	THEN

		SELECT
			ROUND(Customerbill - Vendrorbill,v_Round_) AS PL,
			date AS Date
		FROM  tmp_FinalResult_
		ORDER BY date;

	END IF;

	IF p_ListType = 'Weekly'
	THEN

		SELECT 
			ROUND(SUM(Customerbill) - SUM(Vendrorbill),v_Round_) AS PL,
			CONCAT( YEAR(MAX(date)),' - ',WEEK(MAX(date))) AS Date
		FROM	tmp_FinalResult_
		GROUP BY 
			YEAR(date),
			WEEK(date)
		ORDER BY
			YEAR(date),
			WEEK(date);

	END IF;
	
	IF p_ListType = 'Monthly'
	THEN

		SELECT 
			ROUND(SUM(Customerbill) - SUM(Vendrorbill),v_Round_) AS PL,
			CONCAT( YEAR(MAX(date)),' - ',MONTHNAME(MAX(date))) AS Date
		FROM	tmp_FinalResult_
		GROUP BY
			YEAR(date)
			,MONTH(date)
		ORDER BY 
			YEAR(date)
			,MONTH(date);

	END IF;
	

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END