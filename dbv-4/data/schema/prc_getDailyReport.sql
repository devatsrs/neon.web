CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getDailyReport`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_StartDate` DATE,
	IN `p_EndDate` DATE,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_isExport` INT
)
BEGIN
	DECLARE v_OffSet_ int;
	DECLARE v_Round_ int;
	SET sql_mode = 'ALLOW_INVALID_DATES';
	SET @PreviosBalance := 0;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	IF p_StartDate <> '0000-00-00'
	THEN

	SET @PreviosBalance := 
	(SELECT 
		COALESCE(SUM(Amount),0)
	FROM NeonBillingDev.tblPayment 
	WHERE CompanyID = p_CompanyID
		AND AccountID = p_AccountID
		AND Status = 'Approved'
		AND Recall =0
		AND PaymentType = 'Payment In'
		AND PaymentDate < p_StartDate) - 
		
		(SELECT 
		COALESCE(SUM(TotalCharges),0)
	FROM tblHeader
	INNER JOIN tblDimDate 
		ON tblDimDate.DateID = tblHeader.DateID
	WHERE CompanyID = p_CompanyID
		AND AccountID = p_AccountID
		AND date < p_StartDate);

	END IF;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_dates_;
	CREATE TEMPORARY TABLE tmp_dates_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		Dates Date,
		UNIQUE INDEX `date` (`Dates`)
	);
	INSERT INTO tmp_dates_ (Dates)
	SELECT 
		DISTINCT DATE(PaymentDate) 
	FROM NeonBillingDev.tblPayment 
	WHERE CompanyID = p_CompanyID
		AND AccountID = p_AccountID
		AND Status = 'Approved'
		AND Recall =0
		AND PaymentType = 'Payment In'
		AND (p_StartDate = '0000-00-00' OR ( p_StartDate != '0000-00-00' AND PaymentDate >= p_StartDate) )
		AND (p_EndDate = '0000-00-00' OR ( p_EndDate != '0000-00-00' AND PaymentDate <= p_EndDate));

	INSERT IGNORE INTO tmp_dates_ (Dates)
	SELECT 
		DISTINCT date 
	FROM tblHeader
	INNER JOIN tblDimDate 
		ON tblDimDate.DateID = tblHeader.DateID
	WHERE CompanyID = p_CompanyID
		AND AccountID = p_AccountID
		AND (p_StartDate = '0000-00-00' OR ( p_StartDate != '0000-00-00' AND date >= p_StartDate) )
		AND (p_EndDate = '0000-00-00' OR ( p_EndDate != '0000-00-00' AND date <= p_EndDate));

	IF p_isExport = 0
	THEN

		SELECT 
			Dates,
			ROUND(SUM(Amount),v_Round_),
			ROUND(SUM(TotalCharges),v_Round_),
			ROUND(COALESCE(SUM(Amount),0)-COALESCE(SUM(TotalCharges),0),v_Round_),
			@PreviosBalance:= ROUND(@PreviosBalance,v_Round_)+ ROUND(COALESCE(SUM(Amount),0)-COALESCE(SUM(TotalCharges),0),v_Round_) AS Balance 
		FROM tmp_dates_
		LEFT JOIN NeonBillingDev.tblPayment 
			ON date(PaymentDate) = Dates 
			AND tblPayment.AccountID = p_AccountID
			AND Status = 'Approved'
			AND Recall =0
			AND PaymentType = 'Payment In'
		LEFT JOIN tblDimDate 
			ON date = Dates
		LEFT JOIN tblHeader 
			ON tblDimDate.DateID = tblHeader.DateID 
			AND tblHeader.AccountID = p_AccountID
		GROUP BY Dates 
		LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT
			COUNT(*) AS totalcount
		FROM tmp_dates_;

	END IF;

	IF p_isExport = 1
	THEN

		SELECT 
			Dates AS `Date`,
			ROUND(SUM(Amount),v_Round_) AS `Payments`,
			ROUND(SUM(TotalCharges),v_Round_) AS `Consumption`,
			ROUND(COALESCE(SUM(Amount),0)-COALESCE(SUM(TotalCharges),0),v_Round_) AS `Total`,
			@PreviosBalance:= ROUND(@PreviosBalance,v_Round_)+ ROUND(COALESCE(SUM(Amount),0)-COALESCE(SUM(TotalCharges),0),v_Round_) AS `Balance`
		FROM tmp_dates_
		LEFT JOIN NeonBillingDev.tblPayment 
			ON date(PaymentDate) = Dates 
			AND tblPayment.AccountID = p_AccountID
			AND Status = 'Approved'
			AND Recall =0
			AND PaymentType = 'Payment In'
		LEFT JOIN tblDimDate 
			ON date = Dates
		LEFT JOIN tblHeader 
			ON tblDimDate.DateID = tblHeader.DateID 
			AND tblHeader.AccountID = p_AccountID
		GROUP BY Dates;

	END IF;

END