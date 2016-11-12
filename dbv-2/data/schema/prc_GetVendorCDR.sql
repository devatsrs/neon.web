CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetVendorCDR`(IN `p_CompanyID` INT, IN `p_CompanyGatewayID` INT, IN `p_start_date` DATETIME, IN `p_end_date` DATETIME, IN `p_AccountID` INT, IN `p_CLI` VARCHAR(50), IN `p_CLD` VARCHAR(50), IN `p_ZeroValueBuyingCost` INT, IN `p_CurrencyID` INT, IN `p_area_prefix` VARCHAR(50), IN `p_trunk` VARCHAR(50), IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_isExport` INT)
BEGIN 

	DECLARE v_OffSet_ int;
	DECLARE v_BillingTime_ int;
	DECLARE v_Round_ INT;
	DECLARE v_CurrencyCode_ VARCHAR(50);
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	SELECT cr.Symbol INTO v_CurrencyCode_ from NeonRMDev.tblCurrency cr where cr.CurrencyId =p_CurrencyID;
	
	SELECT 
		BillingTime INTO v_BillingTime_
	FROM NeonRMDev.tblCompanyGateway cg
	INNER JOIN tblGatewayAccount ga 
		ON ga.CompanyGatewayID = cg.CompanyGatewayID
	WHERE AccountID = p_AccountID 
		AND (p_CompanyGatewayID = '' OR ga.CompanyGatewayID = p_CompanyGatewayID)
	LIMIT 1;

	SET v_BillingTime_ = IFNULL(v_BillingTime_,1); 

	Call fnVendorUsageDetail(p_CompanyID,p_AccountID,p_CompanyGatewayID,p_start_date,p_end_date,0,1,v_BillingTime_,p_CLI,p_CLD,p_ZeroValueBuyingCost);

	IF p_isExport = 0
	THEN 
	
		SELECT
			uh.VendorCDRID,	
			uh.AccountName as AccountName,
			uh.connect_time,
			uh.disconnect_time,
			uh.billed_duration,
			CONCAT(IFNULL(v_CurrencyCode_,''),uh.buying_cost) AS buying_cost,
			uh.cli,
			uh.cld,
			uh.area_prefix,
			uh.trunk,
			uh.AccountID,
			p_CompanyGatewayID as CompanyGatewayID,
			p_start_date as StartDate,
			p_end_date as EndDate  
		FROM tmp_tblVendorUsageDetails_ uh
		INNER JOIN NeonRMDev.tblAccount a
			ON uh.AccountID = a.AccountID
		WHERE 	(p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
			AND (p_area_prefix = '' OR area_prefix LIKE REPLACE(p_area_prefix, '*', '%'))
			AND (p_trunk = '' OR trunk = p_trunk )
		LIMIT p_RowspPage OFFSET v_OffSet_;
	 
		SELECT
			COUNT(*) AS totalcount,
			fnFormateDuration(SUM(uh.billed_duration)) as total_billed_duration,
			SUM(uh.buying_cost) as total_cost,
			v_CurrencyCode_ as CurrencyCode
		FROM tmp_tblVendorUsageDetails_ uh
		INNER JOIN NeonRMDev.tblAccount a
			ON uh.AccountID = a.AccountID
		WHERE  (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
			AND (p_area_prefix = '' OR area_prefix LIKE REPLACE(p_area_prefix, '*', '%'))
			AND (p_trunk = '' OR trunk = p_trunk );
	END IF;
	
	IF p_isExport = 1
	THEN
	
		SELECT
			uh.AccountName,        
			uh.connect_time,
			uh.disconnect_time,        
			uh.billed_duration,
			CONCAT(IFNULL(v_CurrencyCode_,''),uh.buying_cost) AS Cost,
			uh.cli,
			uh.cld,
			uh.area_prefix,
			uh.trunk
		FROM tmp_tblVendorUsageDetails_ uh
		INNER JOIN NeonRMDev.tblAccount a
			ON uh.AccountID = a.AccountID
		WHERE  (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
			AND (p_area_prefix = '' OR area_prefix LIKE REPLACE(p_area_prefix, '*', '%'))
			AND (p_trunk = '' OR trunk = p_trunk );
	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END