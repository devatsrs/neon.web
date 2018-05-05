CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_InvoiceManagementReport`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME
)
BEGIN

	DECLARE v_ShowZeroCall_ INT;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	/* top 10 Longest Calls*/
	
	SELECT tblInvoiceTemplate.ShowZeroCall INTO v_ShowZeroCall_ 
	FROM NeonRMDev.tblAccountBilling  
	INNER JOIN NeonRMDev.tblBillingClass ON tblBillingClass.BillingClassID = tblAccountBilling.BillingClassID
	INNER JOIN NeonBillingDev.tblInvoiceTemplate ON tblInvoiceTemplate.InvoiceTemplateID = tblBillingClass.InvoiceTemplateID
	WHERE AccountID = p_AccountID
	LIMIT 1;
	
	SET v_ShowZeroCall_ = IFNULL(v_ShowZeroCall_,1);
	
	SELECT 
		cli as col1,
		cld as col2,
		ROUND(billed_duration/60) as col3,
		cost as col4
	FROM tblUsageDetails  ud
	INNER JOIN tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	WHERE uh.CompanyID = p_CompanyID
	AND uh.AccountID IS NOT NULL
	AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
	AND StartDate BETWEEN p_StartDate AND p_EndDate
	AND ((v_ShowZeroCall_ =0 AND ud.cost >0 ) OR (v_ShowZeroCall_ =1 AND ud.cost >= 0))
	ORDER BY billed_duration DESC LIMIT 10;

	/* top 10 Most Expensive Calls*/
	SELECT 
		cli as col1,
		cld as col2,
		ROUND(billed_duration/60) as col3,
		cost as col4
	FROM tblUsageDetails  ud
	INNER JOIN tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	WHERE uh.CompanyID = p_CompanyID
	AND uh.AccountID IS NOT NULL
	AND (p_AccountID = 0 OR uh.AccountID = p_AccountID)
	AND StartDate BETWEEN p_StartDate AND p_EndDate
	AND ((v_ShowZeroCall_ =0 AND ud.cost >0 ) OR (v_ShowZeroCall_ =1 AND ud.cost >= 0))
	ORDER BY cost DESC LIMIT 10;

	/* top 10 Most Dialled Number*/
	SELECT 
		cld as col1,
		count(*) AS col2,
		ROUND(SUM(billed_duration)/60) AS col3,
		SUM(cost) AS col4
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

	/* Daily Summary */
	SELECT 
		DATE(StartDate) as col1,
		count(*) AS col2,
		ROUND(SUM(billed_duration)/60) AS col3,
		SUM(cost) AS col4
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

	/* Usage by Category */	
	SELECT
		(SELECT Description
		FROM NeonRMDev.tblRate r
		WHERE  r.Code = ud.area_prefix limit 1 )
		AS col1,
		COUNT(UsageDetailID) AS col2,
		CONCAT( FLOOR(SUM(billed_duration ) / 60),':' , SUM(billed_duration ) % 60) AS col3,
		SUM(cost) AS col4
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

END