CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getDistinctList`(
	IN `p_CompanyID` INT,
	IN `p_ColName` VARCHAR(50),
	IN `p_Search` VARCHAR(50),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT
)
BEGIN
	DECLARE v_OffSet_ int;
	DECLARE v_Round_ int;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	IF p_ColName = 'CompanyGatewayID'
	THEN

		SELECT 
			CompanyGatewayID,
			Title 
		FROM NeonRMDev.tblCompanyGateway 
		WHERE CompanyID = p_CompanyID
		AND Title LIKE CONCAT(p_Search,'%')
		LIMIT p_RowspPage OFFSET v_OffSet_;
		
		SELECT
			COUNT(*) AS totalcount
		FROM NeonRMDev.tblCompanyGateway 
		WHERE CompanyID = p_CompanyID
		AND Title LIKE CONCAT(p_Search,'%');

	END IF;
	
	IF p_ColName = 'CountryID'
	THEN

		SELECT 
			CountryID,
			Country 
		FROM NeonRMDev.tblCountry
		WHERE Country LIKE CONCAT(p_Search,'%')
		LIMIT p_RowspPage OFFSET v_OffSet_;
		
		SELECT
			COUNT(*) AS totalcount
		FROM NeonRMDev.tblCountry
		WHERE Country LIKE CONCAT(p_Search,'%');

	END IF;
	
	IF p_ColName = 'AccountID' OR p_ColName = 'VAccountID'
	THEN

		SELECT 
			AccountID,
			AccountName 
		FROM NeonRMDev.tblAccount
		WHERE CompanyID = p_CompanyID
		AND AccountName LIKE CONCAT(p_Search,'%')
		LIMIT p_RowspPage OFFSET v_OffSet_;
		
		SELECT
			COUNT(*) AS totalcount
		FROM NeonRMDev.tblAccount
		WHERE CompanyID = p_CompanyID
		AND AccountName LIKE CONCAT(p_Search,'%');

	END IF;
	
	IF p_ColName = 'ServiceID'
	THEN

		SELECT 
			ServiceID,
			ServiceName 
		FROM NeonRMDev.tblService 
		WHERE CompanyID = p_CompanyID
		AND ServiceName LIKE CONCAT(p_Search,'%')
		LIMIT p_RowspPage OFFSET v_OffSet_;
		
		SELECT
			COUNT(*) AS totalcount
		FROM NeonRMDev.tblService 
		WHERE CompanyID = p_CompanyID
		AND ServiceName LIKE CONCAT(p_Search,'%');

	END IF;
	
	
	IF p_ColName = 'Trunk'
	THEN

		SELECT 
			DISTINCT
			Trunk as Trunk1,
			Trunk
		FROM tblRTrunk
		WHERE CompanyID = p_CompanyID
		AND Trunk LIKE CONCAT(p_Search,'%')
		LIMIT p_RowspPage OFFSET v_OffSet_;
		
		SELECT
			COUNT(*) AS totalcount
		FROM tblRTrunk
		WHERE CompanyID = p_CompanyID
		AND Trunk LIKE CONCAT(p_Search,'%');

	END IF;
	
	IF p_ColName = 'CurrencyID'
	THEN

		SELECT 
			DISTINCT
			CurrencyId as CurrencyID,
			Code
		FROM NeonRMDev.tblCurrency
		WHERE CompanyID = p_CompanyID
		AND Code LIKE CONCAT(p_Search,'%')
		LIMIT p_RowspPage OFFSET v_OffSet_;
		
		SELECT
			COUNT(*) AS totalcount
		FROM NeonRMDev.tblCurrency
		WHERE CompanyID = p_CompanyID
		AND Code LIKE CONCAT(p_Search,'%');

	END IF;
	
	IF p_ColName = 'TaxRateID'
	THEN

		SELECT 
			DISTINCT
			TaxRateId as CurrencyID,
			Title
		FROM NeonRMDev.tblTaxRate
		WHERE CompanyID = p_CompanyID
		AND Title LIKE CONCAT(p_Search,'%')
		LIMIT p_RowspPage OFFSET v_OffSet_;
		
		SELECT
			COUNT(*) AS totalcount
		FROM NeonRMDev.tblTaxRate
		WHERE CompanyID = p_CompanyID
		AND Title LIKE CONCAT(p_Search,'%');

	END IF;
	
	IF p_ColName = 'ProductID'
	THEN

		SELECT 
			DISTINCT
			ProductID as ProductID,
			Name
		FROM NeonBillingDev.tblProduct
		WHERE CompanyID = p_CompanyID
		AND Name LIKE CONCAT(p_Search,'%')
		LIMIT p_RowspPage OFFSET v_OffSet_;
		
		SELECT
			COUNT(*) AS totalcount
		FROM NeonBillingDev.tblProduct
		WHERE CompanyID = p_CompanyID
		AND Name LIKE CONCAT(p_Search,'%');

	END IF;
	
	IF p_ColName = 'Code'
	THEN

		SELECT 
			DISTINCT
			ProductID as ProductID,
			Code
		FROM NeonBillingDev.tblProduct
		WHERE CompanyID = p_CompanyID
		AND Code LIKE CONCAT(p_Search,'%')
		LIMIT p_RowspPage OFFSET v_OffSet_;
		
		SELECT
			COUNT(*) AS totalcount
		FROM NeonBillingDev.tblProduct
		WHERE CompanyID = p_CompanyID
		AND Code LIKE CONCAT(p_Search,'%');

	END IF;
	
	IF p_ColName = 'AreaPrefix'
	THEN

		SELECT 
			DISTINCT
			Code as AreaPrefix1,
			Code
		FROM tblRRate
		WHERE CompanyID = p_CompanyID
		AND Code LIKE CONCAT(p_Search,'%')
		LIMIT p_RowspPage OFFSET v_OffSet_;
		
		SELECT
			COUNT(*) AS totalcount
		FROM tblRRate
		WHERE CompanyID = p_CompanyID
		AND Code LIKE CONCAT(p_Search,'%');

	END IF;
	
	IF p_ColName = 'GatewayAccountPKID' OR p_ColName = 'GatewayVAccountPKID'
	THEN

		SELECT
			DISTINCT 
			CASE WHEN AccountIP <> ''
			THEN 
				AccountIP
			ELSE
				AccountCLI
			END as AccountIP,
			CASE WHEN AccountIP <> ''
			THEN 
				AccountIP
			ELSE
				AccountCLI
			END as AccountIP1 
		FROM NeonBillingDev.tblGatewayAccount 
		WHERE CompanyID = p_CompanyID
		AND (AccountIP <> '' OR AccountCLI <> '')
		AND ( AccountIP LIKE CONCAT(p_Search,'%') OR AccountCLI LIKE CONCAT(p_Search,'%'))
		LIMIT p_RowspPage OFFSET v_OffSet_;
		
		SELECT
			COUNT(
			DISTINCT
			CASE WHEN AccountIP <> ''
			THEN 
				AccountIP
			ELSE
				AccountCLI
			END) AS totalcount
		FROM NeonBillingDev.tblGatewayAccount 
		WHERE CompanyID = p_CompanyID
		AND (AccountIP <> '' OR AccountCLI <> '')
		AND ( AccountIP LIKE CONCAT(p_Search,'%') OR AccountCLI LIKE CONCAT(p_Search,'%'));

	END IF;
	
	IF p_ColName = 'week_of_year'
	THEN

		SELECT 
			DISTINCT
			tblDimDate.week_of_year as week_of_year1,
			tblDimDate.week_of_year
		FROM tblHeader
		INNER JOIN tblDimDate
			ON tblDimDate.DateID = tblHeader.DateID
		WHERE CompanyID = p_CompanyID
		ORDER BY tblDimDate.week_of_year
		LIMIT p_RowspPage OFFSET v_OffSet_;
		
		SELECT
			COUNT(DISTINCT tblDimDate.week_of_year) AS totalcount
		FROM tblHeader
		INNER JOIN tblDimDate
			ON tblDimDate.DateID = tblHeader.DateID
		WHERE CompanyID = p_CompanyID;

	END IF;
	
	IF p_ColName = 'month'
	THEN

		SELECT 
			DISTINCT
			tblDimDate.month_of_year as month1,
			tblDimDate.month
		FROM tblHeader
		INNER JOIN tblDimDate
			ON tblDimDate.DateID = tblHeader.DateID
		WHERE CompanyID = p_CompanyID
		ORDER BY tblDimDate.month_of_year
		LIMIT p_RowspPage OFFSET v_OffSet_;
		
		SELECT
			COUNT(DISTINCT tblDimDate.month) AS totalcount
		FROM tblHeader
		INNER JOIN tblDimDate
			ON tblDimDate.DateID = tblHeader.DateID
		WHERE CompanyID = p_CompanyID;

	END IF;
	
	IF p_ColName = 'quarter_of_year'
	THEN

		SELECT 
			DISTINCT
			tblDimDate.quarter_of_year as month1,
			tblDimDate.quarter_of_year
		FROM tblHeader
		INNER JOIN tblDimDate
			ON tblDimDate.DateID = tblHeader.DateID
		WHERE CompanyID = p_CompanyID
		ORDER BY tblDimDate.quarter_of_year
		LIMIT p_RowspPage OFFSET v_OffSet_;
		
		SELECT
			COUNT(DISTINCT tblDimDate.quarter_of_year) AS totalcount
		FROM tblHeader
		INNER JOIN tblDimDate
			ON tblDimDate.DateID = tblHeader.DateID
		WHERE CompanyID = p_CompanyID;

	END IF;
	
	IF p_ColName = 'year'
	THEN

		SELECT 
			DISTINCT
			tblDimDate.year as month1,
			tblDimDate.year
		FROM tblHeader
		INNER JOIN tblDimDate
			ON tblDimDate.DateID = tblHeader.DateID
		WHERE CompanyID = p_CompanyID
		ORDER BY tblDimDate.year
		LIMIT p_RowspPage OFFSET v_OffSet_;
		
		SELECT
			COUNT(DISTINCT tblDimDate.year) AS totalcount
		FROM tblHeader
		INNER JOIN tblDimDate
			ON tblDimDate.DateID = tblHeader.DateID
		WHERE CompanyID = p_CompanyID;

	END IF;

END