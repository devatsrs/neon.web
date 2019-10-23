USE `RMBilling3`;

DROP PROCEDURE IF EXISTS `prc_GetCDR`;
DELIMITER //
CREATE PROCEDURE `prc_GetCDR`(
	IN `p_company_id` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_start_date` DATETIME,
	IN `p_end_date` DATETIME,
	IN `p_AccountID` INT ,
	IN `p_ResellerID` INT,
	IN `p_CDRType` VARCHAR(50),
	IN `p_CLI` VARCHAR(50),
	IN `p_CLD` VARCHAR(50),
	IN `p_zerovaluecost` INT,
	IN `p_CurrencyID` INT,
	IN `p_area_prefix` VARCHAR(50),
	IN `p_trunk` VARCHAR(50),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT,
	IN `p_tag` VARCHAR(100),
	IN `p_extension` VARCHAR(50)



)
BEGIN

	DECLARE v_OffSet_ INT;
	DECLARE v_BillingTime_ INT;
	DECLARE v_Round_ INT;
	DECLARE v_raccountids TEXT;
	DECLARE v_CurrencyCode_ VARCHAR(50);
	SET v_raccountids = '';

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	SELECT fnGetRoundingPoint(p_company_id) INTO v_Round_;

	SELECT cr.Symbol INTO v_CurrencyCode_ FROM Ratemanagement3.tblCurrency cr WHERE cr.CurrencyId =p_CurrencyID;

	SELECT fnGetBillingTime(p_CompanyGatewayID,p_AccountID) INTO v_BillingTime_;

	Call fnUsageDetail(p_company_id,p_AccountID,p_CompanyGatewayID,p_start_date,p_end_date,0,1,v_BillingTime_,p_CDRType,p_CLI,p_CLD,p_zerovaluecost);
	
	IF p_ResellerID > 0
	THEN
		DROP TEMPORARY TABLE IF EXISTS tmp_reselleraccounts_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_reselleraccounts_(
			AccountID int
		);
	
		INSERT INTO tmp_reselleraccounts_
		SELECT AccountID FROM Ratemanagement3.tblAccountDetails WHERE ResellerOwner=p_ResellerID
		UNION
		SELECT AccountID FROM Ratemanagement3.tblReseller WHERE ResellerID=p_ResellerID;
	
		SELECT IFNULL(GROUP_CONCAT(AccountID),'') INTO v_raccountids FROM tmp_reselleraccounts_;
		
	END IF;

	IF p_isExport = 0
	THEN
		SELECT
			uh.UsageDetailID,
			uh.AccountName,
			uh.connect_time,
			uh.disconnect_time,
			uh.billed_duration,
			CONCAT(IFNULL(v_CurrencyCode_,''),TRIM(uh.cost)+0) AS cost,
			CONCAT(IFNULL(v_CurrencyCode_,''),TRIM(ROUND((uh.cost/uh.billed_duration)*60.0,6))+0) AS rate,
			uh.cli,
			uh.cld,
			uh.area_prefix,
			uh.trunk,
			s.ServiceName,
			uh.AccountID,
			p_CompanyGatewayID AS CompanyGatewayID,
			p_start_date AS StartDate,
			p_end_date AS EndDate,
			uh.is_inbound AS CDRType,
			uh.extension,
			tu.FileName,
			ef.trunk_group_in,
			ef.trunk_group_out,
			ef.alert_time,
			ef.connected_number,
			ef.audio_codec_type
		FROM tmp_tblUsageDetails_ uh
		INNER JOIN Ratemanagement3.tblAccount a
			ON uh.AccountID = a.AccountID
		LEFT JOIN Ratemanagement3.tblService s
			ON uh.ServiceID = s.ServiceID
		INNER JOIN RMCDR3.tblUsageDetailsFileLog tu
			ON uh.UsageDetailID = tu.UsageDetailID
		INNER JOIN RMCDR3.tblHuaweiExtraFields ef
			ON uh.UsageDetailID = ef.UsageDetailID
		WHERE  (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
			AND (p_area_prefix = '' OR area_prefix LIKE REPLACE(p_area_prefix, '*', '%'))
			AND (p_trunk = '' OR trunk = p_trunk )
			AND (p_ResellerID = 0 OR FIND_IN_SET(uh.AccountID, v_raccountids) != 0)
			AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')))
			AND (p_extension = '' OR extension = p_extension)
		LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT
			COUNT(*) AS totalcount,
			fnFormateDuration(sum(billed_duration)) AS total_duration,
			sum(cost) AS total_cost,
			v_CurrencyCode_ AS CurrencyCode
		FROM  tmp_tblUsageDetails_ uh
		INNER JOIN Ratemanagement3.tblAccount a
			ON uh.AccountID = a.AccountID
		WHERE  (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
			AND (p_area_prefix = '' OR area_prefix LIKE REPLACE(p_area_prefix, '*', '%'))
			AND (p_trunk = '' OR trunk = p_trunk )
			AND (p_ResellerID = 0 OR FIND_IN_SET(uh.AccountID, v_raccountids) != 0)
			AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')))
			AND (p_extension = '' OR extension = p_extension);

	END IF;

	IF p_isExport = 1
	THEN

		SELECT
			uh.AccountName AS 'Account Name',
			uh.connect_time AS 'Connect Time',
			uh.disconnect_time AS 'Disconnect Time',
			uh.billed_duration AS 'Billed Duration (sec)' ,
			CONCAT(IFNULL(v_CurrencyCode_,''),TRIM(uh.cost)+0) AS 'Cost',
			CONCAT(IFNULL(v_CurrencyCode_,''),TRIM(ROUND((uh.cost/uh.billed_duration)*60.0,6))+0) AS 'Avg. Rate/Min',
			uh.cli AS 'CLI',
			uh.cld AS 'CLD',
			uh.area_prefix AS 'Prefix',
			uh.trunk AS 'Trunk',
			IF(uh.is_inbound = 0 , 'OutBound','InBound') AS 'Type',
			uh.extension AS 'Extension',
			tu.FileName,
			ef.trunk_group_in,
			ef.trunk_group_out,
			ef.alert_time,
			ef.connected_number,
			ef.audio_codec_type
		FROM tmp_tblUsageDetails_ uh
		INNER JOIN Ratemanagement3.tblAccount a
			ON uh.AccountID = a.AccountID
		INNER JOIN RMCDR3.tblUsageDetailsFileLog tu
			ON uh.UsageDetailID = tu.UsageDetailID
		INNER JOIN RMCDR3.tblHuaweiExtraFields ef
			ON uh.UsageDetailID = ef.UsageDetailID
		WHERE  (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
			AND (p_area_prefix = '' OR area_prefix LIKE REPLACE(p_area_prefix, '*', '%'))
			AND (p_trunk = '' OR trunk = p_trunk )
			AND (p_ResellerID = 0 OR FIND_IN_SET(uh.AccountID, v_raccountids) != 0)
			AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')))
			AND (p_extension = '' OR extension = p_extension);
	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_GetVendorCDR`;
DELIMITER //
CREATE PROCEDURE `prc_GetVendorCDR`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_start_date` DATETIME,
	IN `p_end_date` DATETIME,
	IN `p_AccountID` INT,
	IN `p_CLI` VARCHAR(50),
	IN `p_CLD` VARCHAR(50),
	IN `p_ZeroValueBuyingCost` INT,
	IN `p_CurrencyID` INT,
	IN `p_area_prefix` VARCHAR(50),
	IN `p_trunk` VARCHAR(50),
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT,
	IN `p_tag` VARCHAR(100)


)
BEGIN 

	DECLARE v_OffSet_ int;
	DECLARE v_BillingTime_ int;
	DECLARE v_Round_ INT;
	DECLARE v_CurrencyCode_ VARCHAR(50);
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	SELECT cr.Symbol INTO v_CurrencyCode_ from Ratemanagement3.tblCurrency cr where cr.CurrencyId =p_CurrencyID;
	
	SELECT fnGetBillingTime(p_CompanyGatewayID,p_AccountID) INTO v_BillingTime_;

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
			p_end_date as EndDate,
			tu.FileName,
			ef.trunk_group_in,
			ef.trunk_group_out,
			ef.alert_time,
			ef.connected_number,
			ef.audio_codec_type  
		FROM tmp_tblVendorUsageDetails_ uh
		INNER JOIN Ratemanagement3.tblAccount a
			ON uh.AccountID = a.AccountID
		INNER JOIN RMCDR3.tblVendorCDRFileLog tu
			ON uh.VendorCDRID = tu.VendorCDRID
		INNER JOIN RMCDR3.tblHuaweiVendorExtraFields ef
			ON uh.VendorCDRID = ef.VendorCDRID
		WHERE 	(p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
			AND (p_area_prefix = '' OR area_prefix LIKE REPLACE(p_area_prefix, '*', '%'))
			AND (p_trunk = '' OR trunk = p_trunk )
			AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')))
		LIMIT p_RowspPage OFFSET v_OffSet_;
	 
		SELECT
			COUNT(*) AS totalcount,
			fnFormateDuration(SUM(uh.billed_duration)) as total_billed_duration,
			SUM(uh.buying_cost) as total_cost,
			v_CurrencyCode_ as CurrencyCode
		FROM tmp_tblVendorUsageDetails_ uh
		INNER JOIN Ratemanagement3.tblAccount a
			ON uh.AccountID = a.AccountID
		WHERE  (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
			AND (p_area_prefix = '' OR area_prefix LIKE REPLACE(p_area_prefix, '*', '%'))
			AND (p_trunk = '' OR trunk = p_trunk )
			AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')));
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
			uh.trunk,
			tu.FileName,
			ef.trunk_group_in,
			ef.trunk_group_out,
			ef.alert_time,
			ef.connected_number,
			ef.audio_codec_type  
		FROM tmp_tblVendorUsageDetails_ uh
		INNER JOIN Ratemanagement3.tblAccount a
			ON uh.AccountID = a.AccountID
		INNER JOIN RMCDR3.tblVendorCDRFileLog tu
			ON uh.VendorCDRID = tu.VendorCDRID
		INNER JOIN RMCDR3.tblHuaweiVendorExtraFields ef
			ON uh.VendorCDRID = ef.VendorCDRID
		WHERE  (p_CurrencyID = 0 OR a.CurrencyId = p_CurrencyID)
			AND (p_area_prefix = '' OR area_prefix LIKE REPLACE(p_area_prefix, '*', '%'))
			AND (p_trunk = '' OR trunk = p_trunk )
			AND (p_tag = '' OR (a.tags like Concat(p_tag,'%')));
	END IF;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;