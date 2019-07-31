USE `RMCDR3`;

DROP PROCEDURE IF EXISTS `prc_DeleteDuplicateUniqueID2`;
DELIMITER //
CREATE PROCEDURE `prc_DeleteDuplicateUniqueID2`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_ProcessID` VARCHAR(200),
	IN `p_tbltempusagedetail_name` VARCHAR(200)


)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
	-- this condition is added for sippySQL (AND tud.remote_ip=ud.remote_ip)
	SET @stm1 = CONCAT('
		DELETE tud FROM `' , p_tbltempusagedetail_name , '` tud
		INNER JOIN tblVendorCDR ud ON tud.ID =ud.ID AND tud.remote_ip=ud.remote_ip
		INNER JOIN  tblVendorCDRHeader uh on uh.VendorCDRHeaderID = ud.VendorCDRHeaderID
			AND tud.CompanyID = uh.CompanyID
			AND tud.CompanyGatewayID = uh.CompanyGatewayID
		WHERE tud.CompanyID = "' , p_CompanyID , '"
		AND tud.CompanyGatewayID = "' , p_CompanyGatewayID , '"
		AND tud.ProcessID = "' , p_processId , '";
	');
	PREPARE stmt1 FROM @stm1;
	EXECUTE stmt1;
	DEALLOCATE PREPARE stmt1;

	SET @stm2 = CONCAT('
		DELETE tud FROM `' , p_tbltempusagedetail_name , '` tud
		INNER JOIN tblVendorCDRFailed ud ON tud.ID =ud.ID AND tud.remote_ip=ud.remote_ip
		INNER JOIN  tblVendorCDRHeader uh on uh.VendorCDRHeaderID = ud.VendorCDRHeaderID
			AND tud.CompanyID = uh.CompanyID
			AND tud.CompanyGatewayID = uh.CompanyGatewayID
		WHERE tud.CompanyID = "' , p_CompanyID , '"
		AND tud.CompanyGatewayID = "' , p_CompanyGatewayID , '"
		AND tud.ProcessID = "' , p_processId , '";
	');
	PREPARE stmt2 FROM @stm2;
	EXECUTE stmt2;
	DEALLOCATE PREPARE stmt2;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_InvoiceManagementReport`;
DELIMITER //
CREATE PROCEDURE `prc_InvoiceManagementReport`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME


)
BEGIN

	DECLARE v_ShowZeroCall_ INT;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


	SELECT tblInvoiceTemplate.ShowZeroCall INTO v_ShowZeroCall_
	FROM Ratemanagement3.tblAccountBilling
	INNER JOIN Ratemanagement3.tblBillingClass ON tblBillingClass.BillingClassID = tblAccountBilling.BillingClassID
	INNER JOIN RMBilling3.tblInvoiceTemplate ON tblInvoiceTemplate.InvoiceTemplateID = tblBillingClass.InvoiceTemplateID
	WHERE AccountID = p_AccountID
	LIMIT 1;

	SET v_ShowZeroCall_ = IFNULL(v_ShowZeroCall_,1);

	SELECT
		cli as col1,
		cld as col2,
		CONCAT( FLOOR(billed_duration  / 60),':' , billed_duration  % 60) AS col3,
		cost as col4,
		billed_duration AS BillDurationInSec
	FROM tblUsageDetails  ud
	INNER JOIN tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	WHERE uh.CompanyID = p_CompanyID
	AND uh.AccountID IS NOT NULL
	AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
	AND StartDate BETWEEN p_StartDate AND p_EndDate
	AND ((v_ShowZeroCall_ =0 AND ud.cost >0 ) OR (v_ShowZeroCall_ =1 AND ud.cost >= 0))
	ORDER BY billed_duration DESC LIMIT 10;


	SELECT
		cli as col1,
		cld as col2,
		CONCAT( FLOOR(billed_duration  / 60),':' , billed_duration  % 60) AS col3,
		cost as col4,
		billed_duration AS BillDurationInSec
	FROM tblUsageDetails  ud
	INNER JOIN tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	WHERE uh.CompanyID = p_CompanyID
	AND uh.AccountID IS NOT NULL
	AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
	AND StartDate BETWEEN p_StartDate AND p_EndDate
	AND ((v_ShowZeroCall_ =0 AND ud.cost >0 ) OR (v_ShowZeroCall_ =1 AND ud.cost >= 0))
	ORDER BY cost DESC LIMIT 10;


	SELECT
		cld as col1,
		count(*) AS col2,
		CONCAT( FLOOR(SUM(billed_duration ) / 60),':' , SUM(billed_duration ) % 60) AS col3,
		SUM(cost) AS col4,
		SUM(billed_duration ) AS BillDurationInSec
	FROM tblUsageDetails  ud
	INNER JOIN tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	WHERE uh.CompanyID = p_CompanyID
	AND uh.AccountID IS NOT NULL
	AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
	AND StartDate BETWEEN p_StartDate AND p_EndDate
	AND ((v_ShowZeroCall_ =0 AND ud.cost >0 ) OR (v_ShowZeroCall_ =1 AND ud.cost >= 0))
	GROUP BY cld
	ORDER BY col2 DESC
	LIMIT 10;


	SELECT
		DATE(StartDate) as col1,
		count(*) AS col2,
		CONCAT( FLOOR(SUM(billed_duration ) / 60),':' , SUM(billed_duration ) % 60) AS col3,
		SUM(cost) AS col4,
		SUM(billed_duration ) AS BillDurationInSec
	FROM tblUsageDetails  ud
	INNER JOIN tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	WHERE uh.CompanyID = p_CompanyID
	AND uh.AccountID IS NOT NULL
	AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
	AND StartDate BETWEEN p_StartDate AND p_EndDate
	AND ((v_ShowZeroCall_ =0 AND ud.cost >0 ) OR (v_ShowZeroCall_ =1 AND ud.cost >= 0))
	GROUP BY StartDate
	ORDER BY StartDate;


	SELECT
		(SELECT Description
		FROM Ratemanagement3.tblRate r
			INNER JOIN Ratemanagement3.tblCodeDeck cd
			ON r.CodeDeckID = cd.CodeDeckID
			WHERE  r.CompanyID = p_CompanyID AND r.Code = ud.area_prefix AND cd.DefaultCodeDeck=1 limit 1 )
		AS col1,
		COUNT(UsageDetailID) AS col2,
		CONCAT( FLOOR(SUM(billed_duration ) / 60),':' , SUM(billed_duration ) % 60) AS col3,
		SUM(cost) AS col4,
		SUM(billed_duration ) AS BillDurationInSec
	FROM tblUsageDetails  ud
	INNER JOIN tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	WHERE uh.CompanyID = p_CompanyID
	AND uh.AccountID IS NOT NULL
	AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
	AND StartDate BETWEEN p_StartDate AND p_EndDate
	AND ((v_ShowZeroCall_ =0 AND ud.cost >0 ) OR (v_ShowZeroCall_ =1 AND ud.cost >= 0))
	GROUP BY col1;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

