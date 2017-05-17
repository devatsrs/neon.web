USE `StagingReport`;

ALTER TABLE `tblSummaryHeader`
   ADD COLUMN `ServiceID` int(11) NULL DEFAULT '0';

ALTER TABLE `tblSummaryVendorHeader`
   ADD COLUMN `ServiceID` int(11) NULL DEFAULT '0';

ALTER TABLE `tmp_SummaryHeader`
  ADD COLUMN `ServiceID` int(11) NULL;

ALTER TABLE `tmp_SummaryHeaderLive`
  ADD COLUMN `ServiceID` int(11) NULL;

ALTER TABLE `tmp_SummaryVendorHeader`
  ADD COLUMN `ServiceID` int(11) NULL;

ALTER TABLE `tmp_SummaryVendorHeaderLive`
  ADD COLUMN `ServiceID` int(11) NULL;

ALTER TABLE `tmp_UsageSummary`
  ADD COLUMN `ServiceID` int(11) NULL;

ALTER TABLE `tmp_UsageSummaryLive`
  ADD COLUMN `ServiceID` int(11) NULL;

ALTER TABLE `tmp_VendorUsageSummary`
  ADD COLUMN `ServiceID` int(11) NULL;

ALTER TABLE `tmp_VendorUsageSummaryLive`
  ADD COLUMN `ServiceID` int(11) NULL;

ALTER TABLE `tmp_tblUsageDetailsReport`
  ADD COLUMN `ServiceID` int(11) NULL;

ALTER TABLE `tmp_tblUsageDetailsReportLive`
  ADD COLUMN `ServiceID` int(11) NULL;

ALTER TABLE `tmp_tblVendorUsageDetailsReport`
  ADD COLUMN `ServiceID` int(11) NULL;

ALTER TABLE `tmp_tblVendorUsageDetailsReportLive`
  ADD COLUMN `ServiceID` int(11) NULL;

CREATE TABLE IF NOT EXISTS `tblHeader` (
  `HeaderID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `DateID` bigint(20) NOT NULL,
  `CompanyID` int(11) DEFAULT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `TotalCharges` double DEFAULT NULL,
  `TotalBilledDuration` int(11) DEFAULT NULL,
  `TotalDuration` int(11) DEFAULT NULL,
  `NoOfCalls` int(11) DEFAULT NULL,
  `NoOfFailCalls` int(11) DEFAULT NULL,
  PRIMARY KEY (`HeaderID`),
  UNIQUE KEY `Unique_key` (`DateID`,`AccountID`),
  KEY `FK_tblSummaryHeaderNew_dim_date` (`DateID`),
  KEY `IX_CompanyID` (`CompanyID`),
  CONSTRAINT `tblHeader_ibfk_1` FOREIGN KEY (`DateID`) REFERENCES `tblDimDate` (`DateID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `tblHeaderV` (
  `HeaderVID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `DateID` bigint(20) NOT NULL,
  `CompanyID` int(11) DEFAULT NULL,
  `VAccountID` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `TotalCharges` double DEFAULT NULL,
  `TotalSales` double DEFAULT NULL,
  `TotalBilledDuration` int(11) DEFAULT NULL,
  `TotalDuration` int(11) DEFAULT NULL,
  `NoOfCalls` int(11) DEFAULT NULL,
  `NoOfFailCalls` int(11) DEFAULT NULL,
  PRIMARY KEY (`HeaderVID`),
  UNIQUE KEY `Unique_key` (`DateID`,`VAccountID`),
  KEY `FK_tblHeaderV_dim_date` (`DateID`),
  KEY `IX_CompanyID` (`CompanyID`),
  CONSTRAINT `tblHeader_ibfk_2` FOREIGN KEY (`DateID`) REFERENCES `tblDimDate` (`DateID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;  

INSERT INTO tblHeader (DateID,CompanyID,AccountID,TotalCharges,TotalBilledDuration,TotalDuration,NoOfCalls,NoOfFailCalls)
SELECT DateID,CompanyID,AccountID,SUM(TotalCharges),SUM(TotalBilledDuration),SUM(TotalDuration),SUM(NoOfCalls),SUM(NoOfFailCalls) FROM tblUsageSummary INNER JOIN 
tblSummaryHeader ON tblSummaryHeader.SummaryHeaderID = tblUsageSummary.SummaryHeaderID
WHERE CompanyID =1
GROUP BY DateID,AccountID;

INSERT INTO tblHeaderV (DateID,CompanyID,VAccountID,TotalCharges,TotalBilledDuration,TotalDuration,NoOfCalls,NoOfFailCalls)
SELECT DateID,CompanyID,AccountID,SUM(TotalCharges),SUM(TotalBilledDuration),SUM(TotalDuration),SUM(NoOfCalls),SUM(NoOfFailCalls) FROM tblSummaryVendorHeader INNER JOIN 
tblUsageVendorSummary ON tblSummaryVendorHeader.SummaryVendorHeaderID = tblUsageVendorSummary.SummaryVendorHeaderID
WHERE CompanyID =1
GROUP BY DateID,AccountID;
  
DROP FUNCTION IF EXISTS `fngetLastInvoiceDate`;

DELIMITER |
CREATE FUNCTION `fngetLastInvoiceDate`(
	`p_AccountID` INT

) RETURNS date
BEGIN
	
	DECLARE v_LastInvoiceDate_ DATE;
	
	SELECT 
		CASE WHEN tblAccountBilling.LastInvoiceDate IS NOT NULL AND tblAccountBilling.LastInvoiceDate <> '' 
		THEN 
			DATE_FORMAT(tblAccountBilling.LastInvoiceDate,'%Y-%m-%d')
		ELSE 
			CASE WHEN tblAccountBilling.BillingStartDate IS NOT NULL AND tblAccountBilling.BillingStartDate <> ''
			THEN
				DATE_FORMAT(tblAccountBilling.BillingStartDate,'%Y-%m-%d')
			ELSE DATE_FORMAT(tblAccount.created_at,'%Y-%m-%d')
			END 
		END
		INTO v_LastInvoiceDate_ 
	FROM Ratemanagement3.tblAccount
	LEFT JOIN Ratemanagement3.tblAccountBilling 
		ON tblAccountBilling.AccountID = tblAccount.AccountID AND tblAccountBilling.ServiceID = 0
	WHERE tblAccount.AccountID = p_AccountID
	LIMIT 1;
	
	RETURN v_LastInvoiceDate_;
	
END|
DELIMITER ;

DROP FUNCTION IF EXISTS `fngetLastVendorInvoiceDate`;

DELIMITER |
CREATE FUNCTION `fngetLastVendorInvoiceDate`(
	`p_AccountID` INT
) RETURNS datetime
BEGIN
	
	DECLARE v_LastInvoiceDate_ DATETIME;
	
	SELECT
		CASE WHEN EndDate IS NOT NULL AND EndDate <> '' AND EndDate <> '0000-00-00 00:00:00'
		THEN 
			EndDate
		ELSE 
			CASE WHEN BillingStartDate IS NOT NULL AND BillingStartDate <> ''
			THEN
				DATE_FORMAT(BillingStartDate,'%Y-%m-%d')
			ELSE DATE_FORMAT(tblAccount.created_at,'%Y-%m-%d')
			END 
		END  INTO v_LastInvoiceDate_
 	FROM Ratemanagement3.tblAccount
	LEFT JOIN Ratemanagement3.tblAccountBilling 
		ON tblAccountBilling.AccountID = tblAccount.AccountID AND tblAccountBilling.ServiceID = 0
	LEFT JOIN RMBilling3.tblInvoice 
		ON tblAccount.AccountID = tblInvoice.AccountID AND InvoiceType =2
	LEFT JOIN RMBilling3.tblInvoiceDetail
		ON tblInvoice.InvoiceID =  tblInvoiceDetail.InvoiceID
	WHERE tblAccount.AccountID = p_AccountID 
	ORDER BY IssueDate DESC 
	LIMIT 1;

	RETURN v_LastInvoiceDate_;

END|
DELIMITER ;

DROP FUNCTION IF EXISTS `fnGetRoundingPoint`;
DELIMITER |
CREATE FUNCTION `fnGetRoundingPoint`(
	`p_CompanyID` INT
) RETURNS int(11)
BEGIN

DECLARE v_Round_ int;

SELECT cs.Value INTO v_Round_ from Ratemanagement3.tblCompanySetting cs where cs.`Key` = 'RoundChargesAmount' AND cs.CompanyID = p_CompanyID AND cs.Value <> '';

SET v_Round_ = IFNULL(v_Round_,2);

RETURN v_Round_;
END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `fnGetUsageForSummary`;

DELIMITER |
CREATE PROCEDURE `fnGetUsageForSummary`(
	IN `p_CompanyID` INT,
	IN `p_StartDate` DATE,
	IN `p_EndDate` DATE
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DELETE FROM tmp_tblUsageDetailsReport WHERE CompanyID = p_CompanyID;

	INSERT INTO tmp_tblUsageDetailsReport (UsageDetailID,AccountID,CompanyID,CompanyGatewayID,ServiceID,GatewayAccountID,trunk,area_prefix,duration,billed_duration,cost,connect_time,connect_date,call_status)
	SELECT
		ud.UsageDetailID,
		uh.AccountID,
		uh.CompanyID,
		uh.CompanyGatewayID,
		uh.ServiceID,
		uh.GatewayAccountID,
		trunk,
		area_prefix,
		duration,
		billed_duration,
		cost,
		CONCAT(DATE_FORMAT(ud.connect_time,'%H'),':',IF(MINUTE(ud.connect_time)<30,'00','30'),':00'),
		DATE_FORMAT(ud.connect_time,'%Y-%m-%d'),
		1 as call_status
	FROM RMCDR3.tblUsageDetails  ud
	INNER JOIN RMCDR3.tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	WHERE
		uh.CompanyID = p_CompanyID
	AND uh.AccountID is not null
	AND uh.StartDate BETWEEN p_StartDate AND p_EndDate;

	INSERT INTO tmp_tblUsageDetailsReport (UsageDetailID,AccountID,CompanyID,CompanyGatewayID,ServiceID,GatewayAccountID,trunk,area_prefix,duration,billed_duration,cost,connect_time,connect_date,call_status)
	SELECT
		ud.UsageDetailFailedCallID,
		uh.AccountID,
		uh.CompanyID,
		uh.CompanyGatewayID,
		uh.ServiceID,
		uh.GatewayAccountID,
		trunk,
		area_prefix,
		duration,
		billed_duration,
		cost,
		CONCAT(DATE_FORMAT(ud.connect_time,'%H'),':',IF(MINUTE(ud.connect_time)<30,'00','30'),':00'),
		DATE_FORMAT(ud.connect_time,'%Y-%m-%d'),
		2 as call_status
	FROM RMCDR3.tblUsageDetailFailedCall  ud
	INNER JOIN RMCDR3.tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	WHERE
		uh.CompanyID = p_CompanyID
	AND uh.AccountID is not null
	AND uh.StartDate BETWEEN p_StartDate AND p_EndDate;

END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `fnGetUsageForSummaryLive`;

DELIMITER |
CREATE PROCEDURE `fnGetUsageForSummaryLive`(
	IN `p_CompanyID` INT,
	IN `p_StartDate` DATE,
	IN `p_EndDate` DATE
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	DELETE FROM tmp_tblUsageDetailsReportLive WHERE CompanyID = p_CompanyID;
	
	INSERT INTO tmp_tblUsageDetailsReportLive (UsageDetailID,AccountID,CompanyID,CompanyGatewayID,ServiceID,GatewayAccountID,trunk,area_prefix,duration,billed_duration,cost,connect_time,connect_date,call_status)
	SELECT
		ud.UsageDetailID,
		uh.AccountID,
		uh.CompanyID,
		uh.CompanyGatewayID,
		uh.ServiceID,
		uh.GatewayAccountID,
		trunk,
		area_prefix,
		duration,
		billed_duration,
		cost,
		CONCAT(DATE_FORMAT(ud.connect_time,'%H'),':',IF(MINUTE(ud.connect_time)<30,'00','30'),':00'),
		DATE_FORMAT(ud.connect_time,'%Y-%m-%d'),
		1 AS call_status
	FROM RMCDR3.tblUsageDetails  ud
	INNER JOIN RMCDR3.tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	WHERE
		uh.CompanyID = p_CompanyID
	AND uh.AccountID IS NOT NULL
	AND uh.StartDate BETWEEN p_StartDate AND p_EndDate;

	INSERT INTO tmp_tblUsageDetailsReportLive (UsageDetailID,AccountID,CompanyID,CompanyGatewayID,ServiceID,GatewayAccountID,trunk,area_prefix,duration,billed_duration,cost,connect_time,connect_date,call_status)
	SELECT
		ud.UsageDetailFailedCallID,
		uh.AccountID,
		uh.CompanyID,
		uh.CompanyGatewayID,
		uh.ServiceID,
		uh.GatewayAccountID,
		trunk,
		area_prefix,
		duration,
		billed_duration,
		cost,
		CONCAT(DATE_FORMAT(ud.connect_time,'%H'),':',IF(MINUTE(ud.connect_time)<30,'00','30'),':00'),
		DATE_FORMAT(ud.connect_time,'%Y-%m-%d'),
		2 AS call_status
	FROM RMCDR3.tblUsageDetailFailedCall  ud
	INNER JOIN RMCDR3.tblUsageHeader uh
		ON uh.UsageHeaderID = ud.UsageHeaderID
	WHERE
		uh.CompanyID = p_CompanyID
	AND uh.AccountID IS NOT NULL
	AND uh.StartDate BETWEEN p_StartDate AND p_EndDate;

END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `fnGetVendorUsageForSummary`;

DELIMITER |
CREATE PROCEDURE `fnGetVendorUsageForSummary`(
	IN `p_CompanyID` INT,
	IN `p_StartDate` DATE,
	IN `p_EndDate` DATE
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DELETE FROM tmp_tblVendorUsageDetailsReport WHERE CompanyID = p_CompanyID;
	INSERT INTO tmp_tblVendorUsageDetailsReport (VendorCDRID,AccountID,CompanyID,CompanyGatewayID,ServiceID,GatewayAccountID,trunk,area_prefix,duration,billed_duration,buying_cost,selling_cost,connect_time,connect_date,call_status)
	SELECT
		ud.VendorCDRID,
		uh.AccountID,
		uh.CompanyID,
		uh.CompanyGatewayID,
		uh.ServiceID,
		uh.GatewayAccountID,
		trunk,
		area_prefix,
		duration,
		billed_duration,
		buying_cost,
		selling_cost,
		CONCAT(DATE_FORMAT(ud.connect_time,'%H'),':',IF(MINUTE(ud.connect_time)<30,'00','30'),':00'),
		DATE_FORMAT(ud.connect_time,'%Y-%m-%d'),
		1 AS call_status
	FROM RMCDR3.tblVendorCDR  ud
	INNER JOIN RMCDR3.tblVendorCDRHeader uh
		ON uh.VendorCDRHeaderID = ud.VendorCDRHeaderID 
	WHERE
		uh.CompanyID = p_CompanyID
	AND uh.AccountID IS NOT NULL
	AND uh.StartDate BETWEEN p_StartDate AND p_EndDate;

	INSERT INTO tmp_tblVendorUsageDetailsReport (VendorCDRID,AccountID,CompanyID,CompanyGatewayID,ServiceID,GatewayAccountID,trunk,area_prefix,duration,billed_duration,buying_cost,selling_cost,connect_time,connect_date,call_status)
	SELECT
		ud.VendorCDRFailedID,
		uh.AccountID,
		uh.CompanyID,
		uh.CompanyGatewayID,
		uh.ServiceID,
		uh.GatewayAccountID,
		trunk,
		area_prefix,
		duration,
		billed_duration,
		buying_cost,
		selling_cost,
		CONCAT(DATE_FORMAT(ud.connect_time,'%H'),':',IF(MINUTE(ud.connect_time)<30,'00','30'),':00'),
		DATE_FORMAT(ud.connect_time,'%Y-%m-%d'),
		2 AS call_status
	FROM RMCDR3.tblVendorCDRFailed  ud
	INNER JOIN RMCDR3.tblVendorCDRHeader uh
		ON uh.VendorCDRHeaderID = ud.VendorCDRHeaderID 
	WHERE
		uh.CompanyID = p_CompanyID
	AND uh.AccountID IS NOT NULL
	AND uh.StartDate BETWEEN p_StartDate AND p_EndDate;

END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `fnGetVendorUsageForSummaryLive`;

DELIMITER |
CREATE PROCEDURE `fnGetVendorUsageForSummaryLive`(
	IN `p_CompanyID` INT,
	IN `p_StartDate` DATE,
	IN `p_EndDate` DATE
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DELETE FROM tmp_tblVendorUsageDetailsReportLive WHERE CompanyID = p_CompanyID;
	
	INSERT INTO tmp_tblVendorUsageDetailsReportLive (VendorCDRID,AccountID,CompanyID,CompanyGatewayID,ServiceID,GatewayAccountID,trunk,area_prefix,duration,billed_duration,buying_cost,selling_cost,connect_time,connect_date,call_status)
	SELECT
		ud.VendorCDRID,
		uh.AccountID,
		uh.CompanyID,
		uh.CompanyGatewayID,
		uh.ServiceID,
		uh.GatewayAccountID,
		trunk,
		area_prefix,
		duration,
		billed_duration,
		buying_cost,
		selling_cost,
		CONCAT(DATE_FORMAT(ud.connect_time,'%H'),':',IF(MINUTE(ud.connect_time)<30,'00','30'),':00'),
		DATE_FORMAT(ud.connect_time,'%Y-%m-%d'),
		1 AS call_status
	FROM RMCDR3.tblVendorCDR  ud
	INNER JOIN RMCDR3.tblVendorCDRHeader uh
		ON uh.VendorCDRHeaderID = ud.VendorCDRHeaderID 
	WHERE
		uh.CompanyID = p_CompanyID
	AND uh.AccountID IS NOT NULL
	AND uh.StartDate BETWEEN p_StartDate AND p_EndDate;

	INSERT INTO tmp_tblVendorUsageDetailsReportLive (VendorCDRID,AccountID,CompanyID,CompanyGatewayID,ServiceID,GatewayAccountID,trunk,area_prefix,duration,billed_duration,buying_cost,selling_cost,connect_time,connect_date,call_status)
	SELECT
		ud.VendorCDRFailedID,
		uh.AccountID,
		uh.CompanyID,
		uh.CompanyGatewayID,
		uh.ServiceID,
		uh.GatewayAccountID,
		trunk,
		area_prefix,
		duration,
		billed_duration,
		buying_cost,
		selling_cost,
		CONCAT(DATE_FORMAT(ud.connect_time,'%H'),':',IF(MINUTE(ud.connect_time)<30,'00','30'),':00'),
		DATE_FORMAT(ud.connect_time,'%Y-%m-%d'),
		2 AS call_status
	FROM RMCDR3.tblVendorCDRFailed  ud
	INNER JOIN RMCDR3.tblVendorCDRHeader uh
		ON uh.VendorCDRHeaderID = ud.VendorCDRHeaderID 
	WHERE
		uh.CompanyID = p_CompanyID
	AND uh.AccountID IS NOT NULL
	AND uh.StartDate BETWEEN p_StartDate AND p_EndDate;

END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_generateSummary`;

DELIMITER |
CREATE PROCEDURE `prc_generateSummary`(
	IN `p_CompanyID` INT,
	IN `p_StartDate` DATE,
	IN `p_EndDate` DATE
)
BEGIN
	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		
		GET DIAGNOSTICS CONDITION 1
		@p2 = MESSAGE_TEXT;
	
		SELECT @p2 as Message;
		ROLLBACK;
	END;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	CALL fngetDefaultCodes(p_CompanyID); 
	CALL fnGetUsageForSummary(p_CompanyID,p_StartDate,p_EndDate);
 
 	
 	DELETE FROM tmp_UsageSummary WHERE CompanyID = p_CompanyID;
	INSERT INTO tmp_UsageSummary(DateID,TimeID,CompanyID,CompanyGatewayID,ServiceID,GatewayAccountID,AccountID,Trunk,AreaPrefix,TotalCharges,TotalBilledDuration,TotalDuration,NoOfCalls,NoOfFailCalls)
	SELECT 
		d.DateID,
		t.TimeID,
		ud.CompanyID,
		ud.CompanyGatewayID,
		ud.ServiceID,
		ANY_VALUE(ud.GatewayAccountID),
		ud.AccountID,
		ud.trunk,
		ud.area_prefix,
		COALESCE(SUM(ud.cost),0)  AS TotalCharges ,
		COALESCE(SUM(ud.billed_duration),0) AS TotalBilledDuration ,
		COALESCE(SUM(ud.duration),0) AS TotalDuration,
		SUM(IF(ud.call_status=1,1,0)) AS  NoOfCalls,
		SUM(IF(ud.call_status=2,1,0)) AS  NoOfFailCalls
	FROM tmp_tblUsageDetailsReport ud  
	INNER JOIN tblDimTime t ON t.fulltime = connect_time
	INNER JOIN tblDimDate d ON d.date = connect_date
	GROUP BY d.DateID,t.TimeID,ud.area_prefix,ud.trunk,ud.AccountID,ud.CompanyGatewayID,ud.ServiceID,ud.CompanyID;

	UPDATE tmp_UsageSummary 
	INNER JOIN  tmp_codes_ as code ON AreaPrefix = code.code
	SET tmp_UsageSummary.CountryID =code.CountryID
	WHERE tmp_UsageSummary.CompanyID = p_CompanyID AND code.CountryID > 0;

	DELETE FROM tmp_SummaryHeader WHERE CompanyID = p_CompanyID;
	INSERT INTO tmp_SummaryHeader (SummaryHeaderID,DateID,CompanyID,AccountID,GatewayAccountID,CompanyGatewayID,ServiceID,Trunk,AreaPrefix,CountryID,created_at)
	SELECT 
		sh.SummaryHeaderID,
		sh.DateID,
		sh.CompanyID,
		sh.AccountID,
		sh.GatewayAccountID,
		sh.CompanyGatewayID,
		sh.ServiceID,
		sh.Trunk,
		sh.AreaPrefix,
		sh.CountryID,
		sh.created_at 
	FROM tblSummaryHeader sh
	INNER JOIN (SELECT DISTINCT DateID,CompanyID FROM tmp_UsageSummary)TBL
	ON TBL.DateID = sh.DateID AND TBL.CompanyID = sh.CompanyID
	WHERE sh.CompanyID =  p_CompanyID ;
	
	START TRANSACTION;
	
	INSERT INTO tblSummaryHeader (DateID,CompanyID,AccountID,GatewayAccountID,CompanyGatewayID,ServiceID,Trunk,AreaPrefix,CountryID,created_at)
	SELECT us.DateID,us.CompanyID,us.AccountID,ANY_VALUE(us.GatewayAccountID),us.CompanyGatewayID,us.ServiceID,us.Trunk,us.AreaPrefix,ANY_VALUE(us.CountryID),now() 
	FROM tmp_UsageSummary us
	LEFT JOIN tmp_SummaryHeader sh	 
	ON 
		 us.DateID = sh.DateID
	AND us.CompanyID = sh.CompanyID
	AND us.AccountID = sh.AccountID
	AND us.CompanyGatewayID = sh.CompanyGatewayID
	AND us.Trunk = sh.Trunk
	AND us.AreaPrefix = sh.AreaPrefix
	AND us.ServiceID = sh.ServiceID
	WHERE sh.SummaryHeaderID IS NULL
	GROUP BY us.DateID,us.CompanyID,us.AccountID,us.CompanyGatewayID,us.ServiceID,us.Trunk,us.AreaPrefix;
	
	DELETE FROM tmp_SummaryHeader WHERE CompanyID = p_CompanyID;
	INSERT INTO tmp_SummaryHeader (SummaryHeaderID,DateID,CompanyID,AccountID,GatewayAccountID,CompanyGatewayID,ServiceID,Trunk,AreaPrefix,CountryID,created_at)
	SELECT 
		sh.SummaryHeaderID,
		sh.DateID,
		sh.CompanyID,
		sh.AccountID,
		sh.GatewayAccountID,
		sh.CompanyGatewayID,
		sh.ServiceID,
		sh.Trunk,
		sh.AreaPrefix,
		sh.CountryID,
		sh.created_at 
	FROM tblSummaryHeader sh
	INNER JOIN (SELECT DISTINCT DateID,CompanyID FROM tmp_UsageSummary)TBL
	ON TBL.DateID = sh.DateID AND TBL.CompanyID = sh.CompanyID
	WHERE sh.CompanyID =  p_CompanyID ;

	DELETE us FROM tblUsageSummary us 
	INNER JOIN tblSummaryHeader sh ON us.SummaryHeaderID = sh.SummaryHeaderID
	INNER JOIN tblDimDate d ON d.DateID = sh.DateID
	WHERE date BETWEEN p_StartDate AND p_EndDate AND sh.CompanyID = p_CompanyID;
	
	DELETE usd FROM tblUsageSummaryDetail usd
	INNER JOIN tblSummaryHeader sh ON usd.SummaryHeaderID = sh.SummaryHeaderID
	INNER JOIN tblDimDate d ON d.DateID = sh.DateID
	WHERE date BETWEEN p_StartDate AND p_EndDate AND sh.CompanyID = p_CompanyID;
	
	INSERT INTO tblUsageSummary (SummaryHeaderID,TotalCharges,TotalBilledDuration,TotalDuration,NoOfCalls,NoOfFailCalls)
	SELECT ANY_VALUE(sh.SummaryHeaderID),SUM(us.TotalCharges),SUM(us.TotalBilledDuration),SUM(us.TotalDuration),SUM(us.NoOfCalls),SUM(us.NoOfFailCalls)
	FROM tmp_SummaryHeader sh
	INNER JOIN tmp_UsageSummary us FORCE INDEX (Unique_key)	 
	ON 
		 us.DateID = sh.DateID
	AND us.CompanyID = sh.CompanyID
	AND us.AccountID = sh.AccountID
	AND us.CompanyGatewayID = sh.CompanyGatewayID
	AND us.Trunk = sh.Trunk
	AND us.AreaPrefix = sh.AreaPrefix
	AND us.ServiceID = sh.ServiceID
	GROUP BY us.DateID,us.CompanyID,us.AccountID,us.CompanyGatewayID,us.ServiceID,us.Trunk,us.AreaPrefix;
	
	INSERT INTO tblUsageSummaryDetail (SummaryHeaderID,TimeID,TotalCharges,TotalBilledDuration,TotalDuration,NoOfCalls,NoOfFailCalls)
	SELECT sh.SummaryHeaderID,TimeID,us.TotalCharges,us.TotalBilledDuration,us.TotalDuration,us.NoOfCalls,us.NoOfFailCalls
	FROM tmp_SummaryHeader sh
	INNER JOIN tmp_UsageSummary us FORCE INDEX (Unique_key)
	ON 
		 us.DateID = sh.DateID
	AND us.CompanyID = sh.CompanyID
	AND us.AccountID = sh.AccountID
	AND us.CompanyGatewayID = sh.CompanyGatewayID
	AND us.Trunk = sh.Trunk
	AND us.AreaPrefix = sh.AreaPrefix
	AND us.ServiceID = sh.ServiceID;
	
	DELETE h FROM tblHeader h 
	INNER JOIN tmp_UsageSummary u 
		ON h.DateID = u.DateID 
		AND h.CompanyID = u.CompanyID
	WHERE u.CompanyID = p_CompanyID;
	
	INSERT INTO tblHeader(DateID,CompanyID,AccountID,TotalCharges,TotalBilledDuration,TotalDuration,NoOfCalls,NoOfFailCalls)
	SELECT 
		u.DateID,
		u.CompanyID,
		u.AccountID,
		SUM(u.TotalCharges) as TotalCharges,
		SUM(u.TotalBilledDuration) as TotalBilledDuration,
		SUM(u.TotalDuration) as TotalDuration,
		SUM(u.NoOfCalls) as NoOfCalls,
		SUM(u.NoOfFailCalls) as NoOfFailCalls
	FROM tmp_UsageSummary u 
	WHERE u.CompanyID = p_CompanyID
	GROUP BY u.DateID,u.AccountID,u.CompanyID;

	COMMIT;
	
END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_generateSummaryLive`;

DELIMITER |
CREATE PROCEDURE `prc_generateSummaryLive`(
	IN `p_CompanyID` INT,
	IN `p_StartDate` DATE,
	IN `p_EndDate` DATE
)
BEGIN

	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		
		GET DIAGNOSTICS CONDITION 1
		@p2 = MESSAGE_TEXT;
	
		SELECT @p2 as Message;
		ROLLBACK;
	END;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	CALL fngetDefaultCodes(p_CompanyID); 
	CALL fnGetUsageForSummaryLive(p_CompanyID, p_StartDate, p_EndDate);
	 
 	
 	DELETE FROM tmp_UsageSummaryLive WHERE CompanyID = p_CompanyID;
	INSERT INTO tmp_UsageSummaryLive(DateID,TimeID,CompanyID,CompanyGatewayID,ServiceID,GatewayAccountID,AccountID,Trunk,AreaPrefix,TotalCharges,TotalBilledDuration,TotalDuration,NoOfCalls,NoOfFailCalls)
	SELECT 
		d.DateID,
		t.TimeID,
		ud.CompanyID,
		ud.CompanyGatewayID,
		ud.ServiceID,
		ANY_VALUE(ud.GatewayAccountID),
		ud.AccountID,
		ud.trunk,
		ud.area_prefix,
		COALESCE(SUM(ud.cost),0)  AS TotalCharges ,
		COALESCE(SUM(ud.billed_duration),0) AS TotalBilledDuration ,
		COALESCE(SUM(ud.duration),0) AS TotalDuration,
		SUM(IF(ud.call_status=1,1,0)) AS  NoOfCalls,
		SUM(IF(ud.call_status=2,1,0)) AS  NoOfFailCalls
	FROM tmp_tblUsageDetailsReportLive ud  
	INNER JOIN tblDimTime t ON t.fulltime = connect_time
	INNER JOIN tblDimDate d ON d.date = connect_date
	GROUP BY d.DateID,t.TimeID,ud.area_prefix,ud.trunk,ud.AccountID,ud.CompanyGatewayID,ud.ServiceID,ud.CompanyID;

	UPDATE tmp_UsageSummaryLive 
	INNER JOIN  tmp_codes_ as code ON AreaPrefix = code.code
	SET tmp_UsageSummaryLive.CountryID =code.CountryID
	WHERE tmp_UsageSummaryLive.CompanyID = p_CompanyID AND code.CountryID > 0;

	DELETE FROM tmp_SummaryHeaderLive WHERE CompanyID = p_CompanyID;
	INSERT INTO tmp_SummaryHeaderLive (SummaryHeaderID,DateID,CompanyID,AccountID,GatewayAccountID,CompanyGatewayID,ServiceID,Trunk,AreaPrefix,CountryID,created_at)
	SELECT 
		sh.SummaryHeaderID,
		sh.DateID,
		sh.CompanyID,
		sh.AccountID,
		sh.GatewayAccountID,
		sh.CompanyGatewayID,
		sh.ServiceID,
		sh.Trunk,
		sh.AreaPrefix,
		sh.CountryID,
		sh.created_at 
	FROM tblSummaryHeader sh
	INNER JOIN (SELECT DISTINCT DateID,CompanyID FROM tmp_UsageSummaryLive)TBL
	ON TBL.DateID = sh.DateID AND TBL.CompanyID = sh.CompanyID
	WHERE sh.CompanyID =  p_CompanyID ;

	START TRANSACTION;

	INSERT INTO tblSummaryHeader (DateID,CompanyID,AccountID,GatewayAccountID,CompanyGatewayID,ServiceID,Trunk,AreaPrefix,CountryID,created_at)
	SELECT us.DateID,us.CompanyID,us.AccountID,ANY_VALUE(us.GatewayAccountID),us.CompanyGatewayID,us.ServiceID,us.Trunk,us.AreaPrefix,ANY_VALUE(us.CountryID),now() 
	FROM tmp_UsageSummaryLive us
	LEFT JOIN tmp_SummaryHeaderLive sh	 
	ON 
		 us.DateID = sh.DateID
	AND us.CompanyID = sh.CompanyID
	AND us.AccountID = sh.AccountID
	AND us.CompanyGatewayID = sh.CompanyGatewayID
	AND us.Trunk = sh.Trunk
	AND us.AreaPrefix = sh.AreaPrefix
	AND us.ServiceID = sh.ServiceID
	WHERE sh.SummaryHeaderID IS NULL
	GROUP BY us.DateID,us.CompanyID,us.AccountID,us.CompanyGatewayID,us.ServiceID,us.Trunk,us.AreaPrefix;

	DELETE FROM tmp_SummaryHeaderLive WHERE CompanyID = p_CompanyID;
	INSERT INTO tmp_SummaryHeaderLive (SummaryHeaderID,DateID,CompanyID,AccountID,GatewayAccountID,CompanyGatewayID,ServiceID,Trunk,AreaPrefix,CountryID,created_at)
	SELECT 
		sh.SummaryHeaderID,
		sh.DateID,
		sh.CompanyID,
		sh.AccountID,
		sh.GatewayAccountID,
		sh.CompanyGatewayID,
		sh.ServiceID,
		sh.Trunk,
		sh.AreaPrefix,
		sh.CountryID,
		sh.created_at 
	FROM tblSummaryHeader sh
	INNER JOIN (SELECT DISTINCT DateID,CompanyID FROM tmp_UsageSummaryLive)TBL
	ON TBL.DateID = sh.DateID AND TBL.CompanyID = sh.CompanyID
	WHERE sh.CompanyID =  p_CompanyID ;

	DELETE us FROM tblUsageSummaryLive us 
	INNER JOIN tblSummaryHeader sh ON us.SummaryHeaderID = sh.SummaryHeaderID
	INNER JOIN tblDimDate d ON d.DateID = sh.DateID
	WHERE sh.CompanyID = p_CompanyID; 

	DELETE usd FROM tblUsageSummaryDetailLive usd
	INNER JOIN tblSummaryHeader sh ON usd.SummaryHeaderID = sh.SummaryHeaderID
	INNER JOIN tblDimDate d ON d.DateID = sh.DateID
	WHERE sh.CompanyID = p_CompanyID;

	INSERT INTO tblUsageSummaryLive (SummaryHeaderID,TotalCharges,TotalBilledDuration,TotalDuration,NoOfCalls,NoOfFailCalls)
	SELECT ANY_VALUE(sh.SummaryHeaderID),SUM(us.TotalCharges),SUM(us.TotalBilledDuration),SUM(us.TotalDuration),SUM(us.NoOfCalls),SUM(us.NoOfFailCalls)
	FROM tmp_SummaryHeaderLive sh
	INNER JOIN tmp_UsageSummaryLive us FORCE INDEX (Unique_key)
	ON 
		 us.DateID = sh.DateID
	AND us.CompanyID = sh.CompanyID
	AND us.AccountID = sh.AccountID
	AND us.CompanyGatewayID = sh.CompanyGatewayID
	AND us.Trunk = sh.Trunk
	AND us.AreaPrefix = sh.AreaPrefix
	AND us.ServiceID = sh.ServiceID
	GROUP BY us.DateID,us.CompanyID,us.AccountID,us.CompanyGatewayID,us.ServiceID,us.Trunk,us.AreaPrefix; 
	
	INSERT INTO tblUsageSummaryDetailLive (SummaryHeaderID,TimeID,TotalCharges,TotalBilledDuration,TotalDuration,NoOfCalls,NoOfFailCalls)
	SELECT sh.SummaryHeaderID,TimeID,us.TotalCharges,us.TotalBilledDuration,us.TotalDuration,us.NoOfCalls,us.NoOfFailCalls
	FROM tmp_SummaryHeaderLive sh
	INNER JOIN tmp_UsageSummaryLive us FORCE INDEX (Unique_key)
	ON 
		 us.DateID = sh.DateID
	AND us.CompanyID = sh.CompanyID
	AND us.AccountID = sh.AccountID
	AND us.CompanyGatewayID = sh.CompanyGatewayID
	AND us.Trunk = sh.Trunk
	AND us.AreaPrefix = sh.AreaPrefix
	AND us.ServiceID = sh.ServiceID;

	DELETE h FROM tblHeader h 
	INNER JOIN tmp_UsageSummaryLive u 
		ON h.DateID = u.DateID 
		AND h.CompanyID = u.CompanyID
	WHERE u.CompanyID = p_CompanyID;
	
	INSERT INTO tblHeader(DateID,CompanyID,AccountID,TotalCharges,TotalBilledDuration,TotalDuration,NoOfCalls,NoOfFailCalls)
	SELECT 
		u.DateID,
		u.CompanyID,
		u.AccountID,
		SUM(u.TotalCharges) as TotalCharges,
		SUM(u.TotalBilledDuration) as TotalBilledDuration,
		SUM(u.TotalDuration) as TotalDuration,
		SUM(u.NoOfCalls) as NoOfCalls,
		SUM(u.NoOfFailCalls) as NoOfFailCalls
	FROM tmp_UsageSummaryLive u 
	WHERE u.CompanyID = p_CompanyID
	GROUP BY u.DateID,u.AccountID,u.CompanyID;
	COMMIT;
	
END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_generateVendorSummary`;

DELIMITER |
CREATE PROCEDURE `prc_generateVendorSummary`(
	IN `p_CompanyID` INT,
	IN `p_StartDate` DATE,
	IN `p_EndDate` DATE
)
BEGIN
	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		-- ERROR
		GET DIAGNOSTICS CONDITION 1
		@p2 = MESSAGE_TEXT;
	
		SELECT @p2 as Message;
		ROLLBACK;
	END;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	CALL fngetDefaultCodes(p_CompanyID); 
	CALL fnGetVendorUsageForSummary(p_CompanyID,p_StartDate,p_EndDate);

 	/* insert into success summary*/
 	DELETE FROM tmp_VendorUsageSummary WHERE CompanyID = p_CompanyID;
	INSERT INTO tmp_VendorUsageSummary(DateID,TimeID,CompanyID,CompanyGatewayID,ServiceID,GatewayAccountID,AccountID,Trunk,AreaPrefix,TotalCharges,TotalSales,TotalBilledDuration,TotalDuration,NoOfCalls,NoOfFailCalls)
	SELECT 
		d.DateID,
		t.TimeID,
		ud.CompanyID,
		ud.CompanyGatewayID,
		ud.ServiceID,
		ANY_VALUE(ud.GatewayAccountID),
		ud.AccountID,
		ud.trunk,
		ud.area_prefix,
		COALESCE(SUM(ud.buying_cost),0)  AS TotalCharges ,
		COALESCE(SUM(ud.selling_cost),0)  AS TotalSales ,
		COALESCE(SUM(ud.billed_duration),0) AS TotalBilledDuration ,
		COALESCE(SUM(ud.duration),0) AS TotalDuration,
		SUM(IF(ud.call_status=1,1,0)) AS  NoOfCalls,
		SUM(IF(ud.call_status=2,1,0)) AS  NoOfFailCalls
	FROM tmp_tblVendorUsageDetailsReport ud  
	INNER JOIN tblDimTime t ON t.fulltime = connect_time
	INNER JOIN tblDimDate d ON d.date = connect_date
	GROUP BY d.DateID,t.TimeID,ud.area_prefix,ud.trunk,ud.AccountID,ud.CompanyGatewayID,ud.ServiceID,ud.CompanyID;

	UPDATE tmp_VendorUsageSummary 
	INNER JOIN  tmp_codes_ as code ON AreaPrefix = code.code
	SET tmp_VendorUsageSummary.CountryID =code.CountryID
	WHERE tmp_VendorUsageSummary.CompanyID = p_CompanyID AND code.CountryID > 0;

	DELETE FROM tmp_SummaryVendorHeader WHERE CompanyID = p_CompanyID;

	INSERT INTO tmp_SummaryVendorHeader (SummaryVendorHeaderID,DateID,CompanyID,AccountID,GatewayAccountID,CompanyGatewayID,ServiceID,Trunk,AreaPrefix,CountryID,created_at)
	SELECT 
		sh.SummaryVendorHeaderID,
		sh.DateID,
		sh.CompanyID,
		sh.AccountID,
		sh.GatewayAccountID,
		sh.CompanyGatewayID,
		sh.ServiceID,
		sh.Trunk,
		sh.AreaPrefix,
		sh.CountryID,
		sh.created_at 
	FROM tblSummaryVendorHeader sh
	INNER JOIN (SELECT DISTINCT DateID,CompanyID FROM tmp_VendorUsageSummary)TBL
	ON TBL.DateID = sh.DateID AND TBL.CompanyID = sh.CompanyID
	WHERE sh.CompanyID =  p_CompanyID ;

	START TRANSACTION;

	INSERT INTO tblSummaryVendorHeader (DateID,CompanyID,AccountID,GatewayAccountID,CompanyGatewayID,ServiceID,Trunk,AreaPrefix,CountryID,created_at)
	SELECT us.DateID,us.CompanyID,us.AccountID,ANY_VALUE(us.GatewayAccountID),us.CompanyGatewayID,us.ServiceID,us.Trunk,us.AreaPrefix,ANY_VALUE(us.CountryID),now() 
	FROM tmp_VendorUsageSummary us
	LEFT JOIN tmp_SummaryVendorHeader sh	 
	ON 
		 us.DateID = sh.DateID
	AND us.CompanyID = sh.CompanyID
	AND us.AccountID = sh.AccountID
	AND us.CompanyGatewayID = sh.CompanyGatewayID
	AND us.Trunk = sh.Trunk
	AND us.AreaPrefix = sh.AreaPrefix
	AND us.ServiceID = sh.ServiceID
	WHERE sh.SummaryVendorHeaderID IS NULL
	GROUP BY us.DateID,us.CompanyID,us.AccountID,us.CompanyGatewayID,us.ServiceID,us.Trunk,us.AreaPrefix;

	DELETE FROM tmp_SummaryVendorHeader WHERE CompanyID = p_CompanyID;

	INSERT INTO tmp_SummaryVendorHeader (SummaryVendorHeaderID,DateID,CompanyID,AccountID,GatewayAccountID,CompanyGatewayID,ServiceID,Trunk,AreaPrefix,CountryID,created_at)
	SELECT 
		sh.SummaryVendorHeaderID,
		sh.DateID,
		sh.CompanyID,
		sh.AccountID,
		sh.GatewayAccountID,
		sh.CompanyGatewayID,
		sh.ServiceID,
		sh.Trunk,
		sh.AreaPrefix,
		sh.CountryID,
		sh.created_at 
	FROM tblSummaryVendorHeader sh
	INNER JOIN (SELECT DISTINCT DateID,CompanyID FROM tmp_VendorUsageSummary)TBL
	ON TBL.DateID = sh.DateID AND TBL.CompanyID = sh.CompanyID
	WHERE sh.CompanyID =  p_CompanyID ;

	DELETE us FROM tblUsageVendorSummary us 
	INNER JOIN tblSummaryVendorHeader sh ON us.SummaryVendorHeaderID = sh.SummaryVendorHeaderID
	INNER JOIN tblDimDate d ON d.DateID = sh.DateID
	WHERE date BETWEEN p_StartDate AND p_EndDate AND sh.CompanyID = p_CompanyID;

	DELETE usd FROM tblUsageVendorSummaryDetail usd
	INNER JOIN tblSummaryVendorHeader sh ON usd.SummaryVendorHeaderID = sh.SummaryVendorHeaderID
	INNER JOIN tblDimDate d ON d.DateID = sh.DateID
	WHERE date BETWEEN p_StartDate AND p_EndDate AND sh.CompanyID = p_CompanyID;

	INSERT INTO tblUsageVendorSummary (SummaryVendorHeaderID,TotalCharges,TotalSales,TotalBilledDuration,TotalDuration,NoOfCalls,NoOfFailCalls)
	SELECT ANY_VALUE(sh.SummaryVendorHeaderID),SUM(us.TotalCharges),SUM(us.TotalSales),SUM(us.TotalBilledDuration),SUM(us.TotalDuration),SUM(us.NoOfCalls),SUM(us.NoOfFailCalls)
	FROM tmp_SummaryVendorHeader sh
	INNER JOIN tmp_VendorUsageSummary us FORCE INDEX (Unique_key)	 
	ON 
		 sh.DateID = us.DateID
	AND sh.CompanyID = us.CompanyID
	AND sh.AccountID = us.AccountID
	AND sh.CompanyGatewayID = us.CompanyGatewayID
	AND sh.Trunk = us.Trunk
	AND sh.AreaPrefix = us.AreaPrefix
	AND sh.ServiceID = us.ServiceID
	GROUP BY us.DateID,us.CompanyID,us.AccountID,us.CompanyGatewayID,us.ServiceID,us.Trunk,us.AreaPrefix;

	INSERT INTO tblUsageVendorSummaryDetail (SummaryVendorHeaderID,TimeID,TotalCharges,TotalSales,TotalBilledDuration,TotalDuration,NoOfCalls,NoOfFailCalls)
	SELECT sh.SummaryVendorHeaderID,TimeID,us.TotalCharges,us.TotalSales,us.TotalBilledDuration,us.TotalDuration,us.NoOfCalls,us.NoOfFailCalls
	FROM tmp_SummaryVendorHeader sh
	INNER JOIN tmp_VendorUsageSummary us FORCE INDEX (Unique_key)
	ON 
		sh.DateID = us.DateID
	AND sh.CompanyID = us.CompanyID
	AND sh.AccountID = us.AccountID
	AND sh.CompanyGatewayID = us.CompanyGatewayID
	AND sh.Trunk = us.Trunk
	AND sh.AreaPrefix = us.AreaPrefix
	AND sh.ServiceID = us.ServiceID;
	
	DELETE h FROM tblHeaderV h 
	INNER JOIN tmp_VendorUsageSummary u 
		ON h.DateID = u.DateID 
		AND h.CompanyID = u.CompanyID
	WHERE u.CompanyID = p_CompanyID;
	
	INSERT INTO tblHeaderV(DateID,CompanyID,VAccountID,TotalCharges,TotalSales,TotalBilledDuration,TotalDuration,NoOfCalls,NoOfFailCalls)
	SELECT 
		u.DateID,
		u.CompanyID,
		u.AccountID,
		SUM(u.TotalCharges) as TotalCharges,
		SUM(u.TotalSales) as TotalSales,
		SUM(u.TotalBilledDuration) as TotalBilledDuration,
		SUM(u.TotalDuration) as TotalDuration,
		SUM(u.NoOfCalls) as NoOfCalls,
		SUM(u.NoOfFailCalls) as NoOfFailCalls
	FROM tmp_VendorUsageSummary u 
	WHERE u.CompanyID = p_CompanyID
	GROUP BY u.DateID,u.AccountID,u.CompanyID;
	
	COMMIT;

END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_generateVendorSummaryLive`;

DELIMITER |
CREATE PROCEDURE `prc_generateVendorSummaryLive`(
	IN `p_CompanyID` INT,
	IN `p_StartDate` DATE,
	IN `p_EndDate` DATE
)
BEGIN
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		-- ERROR
		GET DIAGNOSTICS CONDITION 1
		@p2 = MESSAGE_TEXT;
	
		SELECT @p2 as Message;
		ROLLBACK;
	END;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	CALL fngetDefaultCodes(p_CompanyID); 
	CALL fnGetVendorUsageForSummaryLive(p_CompanyID, p_StartDate, p_EndDate);

 	/* insert into success summary*/
 	DELETE FROM tmp_VendorUsageSummaryLive WHERE CompanyID = p_CompanyID;
	INSERT INTO tmp_VendorUsageSummaryLive(DateID,TimeID,CompanyID,CompanyGatewayID,ServiceID,GatewayAccountID,AccountID,Trunk,AreaPrefix,TotalCharges,TotalSales,TotalBilledDuration,TotalDuration,NoOfCalls,NoOfFailCalls)
	SELECT 
		d.DateID,
		t.TimeID,
		ud.CompanyID,
		ud.CompanyGatewayID,
		ud.ServiceID,
		ANY_VALUE(ud.GatewayAccountID),
		ud.AccountID,
		ud.trunk,
		ud.area_prefix,
		COALESCE(SUM(ud.buying_cost),0)  AS TotalCharges ,
		COALESCE(SUM(ud.selling_cost),0)  AS TotalSales ,
		COALESCE(SUM(ud.billed_duration),0) AS TotalBilledDuration ,
		COALESCE(SUM(ud.duration),0) AS TotalDuration,
		SUM(IF(ud.call_status=1,1,0)) AS  NoOfCalls,
		SUM(IF(ud.call_status=2,1,0)) AS  NoOfFailCalls
	FROM tmp_tblVendorUsageDetailsReportLive ud  
	INNER JOIN tblDimTime t ON t.fulltime = connect_time
	INNER JOIN tblDimDate d ON d.date = connect_date
	GROUP BY d.DateID,t.TimeID,ud.area_prefix,ud.trunk,ud.AccountID,ud.CompanyGatewayID,ud.ServiceID,ud.CompanyID;

	UPDATE tmp_VendorUsageSummaryLive 
	INNER JOIN  tmp_codes_ as code ON AreaPrefix = code.code
	SET tmp_VendorUsageSummaryLive.CountryID =code.CountryID
	WHERE tmp_VendorUsageSummaryLive.CompanyID = p_CompanyID AND code.CountryID > 0;

	DELETE FROM tmp_SummaryVendorHeaderLive WHERE CompanyID = p_CompanyID;

	INSERT INTO tmp_SummaryVendorHeaderLive (SummaryVendorHeaderID,DateID,CompanyID,AccountID,GatewayAccountID,CompanyGatewayID,ServiceID,Trunk,AreaPrefix,CountryID,created_at)
	SELECT 
		sh.SummaryVendorHeaderID,
		sh.DateID,
		sh.CompanyID,
		sh.AccountID,
		sh.GatewayAccountID,
		sh.CompanyGatewayID,
		sh.ServiceID,
		sh.Trunk,
		sh.AreaPrefix,
		sh.CountryID,
		sh.created_at 
	FROM tblSummaryVendorHeader sh
	INNER JOIN (SELECT DISTINCT DateID,CompanyID FROM tmp_VendorUsageSummaryLive)TBL
	ON TBL.DateID = sh.DateID AND TBL.CompanyID = sh.CompanyID
	WHERE sh.CompanyID =  p_CompanyID ;

	START TRANSACTION;

	INSERT INTO tblSummaryVendorHeader (DateID,CompanyID,AccountID,GatewayAccountID,CompanyGatewayID,ServiceID,Trunk,AreaPrefix,CountryID,created_at)
	SELECT us.DateID,us.CompanyID,us.AccountID,ANY_VALUE(us.GatewayAccountID),us.CompanyGatewayID,us.ServiceID,us.Trunk,us.AreaPrefix,ANY_VALUE(us.CountryID),now() 
	FROM tmp_VendorUsageSummaryLive us
	LEFT JOIN tmp_SummaryVendorHeaderLive sh	 
	ON 
		 us.DateID = sh.DateID
	AND us.CompanyID = sh.CompanyID
	AND us.AccountID = sh.AccountID
	AND us.CompanyGatewayID = sh.CompanyGatewayID
	AND us.Trunk = sh.Trunk
	AND us.AreaPrefix = sh.AreaPrefix
	AND us.ServiceID = sh.ServiceID
	WHERE sh.SummaryVendorHeaderID IS NULL
	GROUP BY us.DateID,us.CompanyID,us.AccountID,us.CompanyGatewayID,us.ServiceID,us.Trunk,us.AreaPrefix;

	DELETE FROM tmp_SummaryVendorHeaderLive WHERE CompanyID = p_CompanyID;

	INSERT INTO tmp_SummaryVendorHeaderLive (SummaryVendorHeaderID,DateID,CompanyID,AccountID,GatewayAccountID,CompanyGatewayID,ServiceID,Trunk,AreaPrefix,CountryID,created_at)
	SELECT 
		sh.SummaryVendorHeaderID,
		sh.DateID,
		sh.CompanyID,
		sh.AccountID,
		sh.GatewayAccountID,
		sh.CompanyGatewayID,
		sh.ServiceID,
		sh.Trunk,
		sh.AreaPrefix,
		sh.CountryID,
		sh.created_at 
	FROM tblSummaryVendorHeader sh
	INNER JOIN (SELECT DISTINCT DateID,CompanyID FROM tmp_VendorUsageSummaryLive)TBL
	ON TBL.DateID = sh.DateID AND TBL.CompanyID = sh.CompanyID
	WHERE sh.CompanyID =  p_CompanyID ;

	DELETE us FROM tblUsageVendorSummaryLive us 
	INNER JOIN tblSummaryVendorHeader sh ON us.SummaryVendorHeaderID = sh.SummaryVendorHeaderID
	INNER JOIN tblDimDate d ON d.DateID = sh.DateID
	WHERE sh.CompanyID = p_CompanyID;

	DELETE usd FROM tblUsageVendorSummaryDetailLive usd
	INNER JOIN tblSummaryVendorHeader sh ON usd.SummaryVendorHeaderID = sh.SummaryVendorHeaderID
	INNER JOIN tblDimDate d ON d.DateID = sh.DateID
	WHERE sh.CompanyID = p_CompanyID;

	INSERT INTO tblUsageVendorSummaryLive (SummaryVendorHeaderID,TotalCharges,TotalSales,TotalBilledDuration,TotalDuration,NoOfCalls,NoOfFailCalls)
	SELECT ANY_VALUE(sh.SummaryVendorHeaderID),SUM(us.TotalCharges),SUM(us.TotalSales),SUM(us.TotalBilledDuration),SUM(us.TotalDuration),SUM(us.NoOfCalls),SUM(us.NoOfFailCalls)
	FROM tmp_SummaryVendorHeaderLive sh
	INNER JOIN tmp_VendorUsageSummaryLive us FORCE INDEX (Unique_key)	 
	ON 
		 sh.DateID = us.DateID
	AND sh.CompanyID = us.CompanyID
	AND sh.AccountID = us.AccountID
	AND sh.CompanyGatewayID = us.CompanyGatewayID
	AND sh.Trunk = us.Trunk
	AND sh.AreaPrefix = us.AreaPrefix
	AND sh.ServiceID = us.ServiceID
	GROUP BY us.DateID,us.CompanyID,us.AccountID,us.CompanyGatewayID,us.ServiceID,us.Trunk,us.AreaPrefix;

	INSERT INTO tblUsageVendorSummaryDetailLive (SummaryVendorHeaderID,TimeID,TotalCharges,TotalSales,TotalBilledDuration,TotalDuration,NoOfCalls,NoOfFailCalls)
	SELECT sh.SummaryVendorHeaderID,TimeID,us.TotalCharges,us.TotalSales,us.TotalBilledDuration,us.TotalDuration,us.NoOfCalls,us.NoOfFailCalls
	FROM tmp_SummaryVendorHeaderLive sh
	INNER JOIN tmp_VendorUsageSummaryLive us FORCE INDEX (Unique_key)
	ON 
		 sh.DateID = us.DateID
	AND sh.CompanyID = us.CompanyID
	AND sh.AccountID = us.AccountID
	AND sh.CompanyGatewayID = us.CompanyGatewayID
	AND sh.Trunk = us.Trunk
	AND sh.AreaPrefix = us.AreaPrefix
	AND sh.ServiceID = us.ServiceID;

	DELETE h FROM tblHeaderV h 
	INNER JOIN tmp_VendorUsageSummaryLive u 
		ON h.DateID = u.DateID 
		AND h.CompanyID = u.CompanyID
	WHERE u.CompanyID = p_CompanyID;
	
	INSERT INTO tblHeaderV(DateID,CompanyID,VAccountID,TotalCharges,TotalSales,TotalBilledDuration,TotalDuration,NoOfCalls,NoOfFailCalls)
	SELECT 
		u.DateID,
		u.CompanyID,
		u.AccountID,
		SUM(u.TotalCharges) as TotalCharges,
		SUM(u.TotalSales) as TotalSales,
		SUM(u.TotalBilledDuration) as TotalBilledDuration,
		SUM(u.TotalDuration) as TotalDuration,
		SUM(u.NoOfCalls) as NoOfCalls,
		SUM(u.NoOfFailCalls) as NoOfFailCalls
	FROM tmp_VendorUsageSummaryLive u 
	WHERE u.CompanyID = p_CompanyID
	GROUP BY u.DateID,u.AccountID,u.CompanyID;	

	COMMIT;
	
END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_getDashboardPayableReceivable`;
DELIMITER |
CREATE PROCEDURE `prc_getDashboardPayableReceivable`(
	IN `p_CompanyID` INT,
	IN `p_CurrencyID` INT,
	IN `p_AccountID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_Unbilled` INT,
	IN `p_ListType` VARCHAR(50)
)
BEGIN
	DECLARE v_Round_ INT;
	DECLARE prev_TotalInvoiceOut  DECIMAL(18,6);
	DECLARE prev_TotalInvoiceIn DECIMAL(18,6);
	DECLARE prev_TotalPaymentOut DECIMAL(18,6);
	DECLARE prev_TotalPaymentIn DECIMAL(18,6);
	DECLARE prev_CustomerUnbill DECIMAL(18,6);
	DECLARE prev_VendrorUnbill DECIMAL(18,6);

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	DROP TEMPORARY TABLE IF EXISTS tmp_CustomerUnbilled_;
	CREATE TEMPORARY TABLE tmp_CustomerUnbilled_  (
		DateID INT,
		CustomerUnbill DOUBLE
	);
	DROP TEMPORARY TABLE IF EXISTS tmp_VendorUbilled_;
	CREATE TEMPORARY TABLE tmp_VendorUbilled_  (
		DateID INT,
		VendrorUnbill DOUBLE
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_FinalResult_;
	CREATE TEMPORARY TABLE tmp_FinalResult_  (
		TotalInvoiceOut DOUBLE,
		TotalInvoiceIn DOUBLE,
		TotalPaymentOut DOUBLE,
		TotalPaymentIn DOUBLE,
		CustomerUnbill DOUBLE,
		VendrorUnbill DOUBLE,
		date DATE,
		TotalOutstanding DOUBLE,
		TotalPayable DOUBLE,
		TotalReceivable DOUBLE
	);
	
	IF p_Unbilled = 1
	THEN
		DROP TEMPORARY TABLE IF EXISTS tmp_Account_;
		CREATE TEMPORARY TABLE tmp_Account_  (
			RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			AccountID INT,
			LastInvoiceDate DATE
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Account2_;
		CREATE TEMPORARY TABLE tmp_Account2_  (
			RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			AccountID INT,
			LastInvoiceDate DATE
		);

		INSERT INTO tmp_Account_ (AccountID)
		SELECT DISTINCT tblSummaryHeader.AccountID  FROM tblSummaryHeader INNER JOIN Ratemanagement3.tblAccount ON tblAccount.AccountID = tblSummaryHeader.AccountID WHERE tblSummaryHeader.CompanyID = 1;

		UPDATE tmp_Account_ SET LastInvoiceDate = fngetLastInvoiceDate(AccountID);

		INSERT INTO tmp_Account2_ (AccountID)
		SELECT DISTINCT tblSummaryVendorHeader.AccountID  FROM tblSummaryVendorHeader INNER JOIN Ratemanagement3.tblAccount ON tblAccount.AccountID = tblSummaryVendorHeader.AccountID WHERE tblSummaryVendorHeader.CompanyID = p_CompanyID;

		UPDATE tmp_Account2_ SET LastInvoiceDate = fngetLastVendorInvoiceDate(AccountID);

		SELECT 
			SUM(h.TotalCharges)
		INTO
			prev_CustomerUnbill
		FROM tmp_Account_ a
		INNER JOIN tblDimDate dd
			ON dd.date >= a.LastInvoiceDate
		INNER JOIN tblHeader h
			ON h.AccountID = a.AccountID
			AND h.DateID = dd.DateID
		WHERE dd.date < p_StartDate;
		
		SELECT 
			SUM(h.TotalCharges)
		INTO 
			prev_VendrorUnbill
		FROM tmp_Account2_ a
		INNER JOIN tblDimDate dd
			ON dd.date >= a.LastInvoiceDate
		INNER JOIN tblHeaderV h
			ON h.VAccountID = a.AccountID
			AND h.DateID = dd.DateID
		WHERE dd.date < p_StartDate;

		INSERT INTO tmp_CustomerUnbilled_(DateID,CustomerUnbill)
		SELECT 
			dd.DateID,
			SUM(h.TotalCharges)
		FROM tmp_Account_ a
		INNER JOIN tblDimDate dd
			ON dd.date >= a.LastInvoiceDate
		INNER JOIN tblHeader h
			ON h.AccountID = a.AccountID
			AND h.DateID = dd.DateID
		WHERE dd.date BETWEEN p_StartDate AND p_EndDate
		GROUP BY dd.date;

		INSERT INTO tmp_VendorUbilled_ (DateID,VendrorUnbill)
		SELECT 
			dd.DateID,
			SUM(h.TotalCharges)
		FROM tmp_Account2_ a
		INNER JOIN tblDimDate dd
			ON dd.date >= a.LastInvoiceDate
		INNER JOIN tblHeaderV h
			ON h.VAccountID = a.AccountID
			AND h.DateID = dd.DateID
		WHERE dd.date BETWEEN p_StartDate AND p_EndDate
		GROUP BY dd.date;
	
	END IF;

	SELECT 
		SUM(IF(InvoiceType=1,GrandTotal,0)),
		SUM(IF(InvoiceType=2,GrandTotal,0)) 
	INTO 
		prev_TotalInvoiceOut,
		prev_TotalInvoiceIn
	FROM RMBilling3.tblInvoice 
	WHERE 
		CompanyID = p_CompanyID
		AND CurrencyID = p_CurrencyID
		AND ( (InvoiceType = 2) OR ( InvoiceType = 1 AND InvoiceStatus NOT IN ( 'cancel' , 'draft') )  )
		AND (p_AccountID = 0 or AccountID = p_AccountID)
	AND tblInvoice.IssueDate < p_StartDate ;

	SELECT 
		SUM(IF(PaymentType='Payment In',p.Amount,0)),
		SUM(IF(PaymentType='Payment Out',p.Amount,0)) 
	INTO 
		prev_TotalPaymentIn,
		prev_TotalPaymentOut
	FROM RMBilling3.tblPayment p 
	INNER JOIN Ratemanagement3.tblAccount ac 
		ON ac.AccountID = p.AccountID
	WHERE 
		p.CompanyID = p_CompanyID
		AND ac.CurrencyId = p_CurrencyID
		AND p.Status = 'Approved'
		AND p.Recall=0
		AND (p_AccountID = 0 or p.AccountID = p_AccountID)
	AND p.PaymentDate < p_StartDate;
	
	SET @prev_TotalInvoiceOut := IFNULL(prev_TotalInvoiceOut,0) ;
	SET @prev_TotalInvoiceIn := IFNULL(prev_TotalInvoiceIn,0) ;
	SET @prev_TotalPaymentOut := IFNULL(prev_TotalPaymentOut,0) ;
	SET @prev_TotalPaymentIn := IFNULL(prev_TotalPaymentIn,0) ;
	SET @prev_CustomerUnbill := IFNULL(prev_CustomerUnbill,0) ;
	SET @prev_VendrorUnbill := IFNULL(prev_VendrorUnbill,0) ;
	
	INSERT INTO tmp_FinalResult_(TotalInvoiceOut,TotalInvoiceIn,TotalPaymentOut,TotalPaymentIn,CustomerUnbill,VendrorUnbill,date,TotalOutstanding,TotalPayable,TotalReceivable)
	SELECT 
		@prev_TotalInvoiceOut := @prev_TotalInvoiceOut +    IFNULL(TotalInvoiceOut,0) AS TotalInvoiceOut ,
		@prev_TotalInvoiceIn := @prev_TotalInvoiceIn +   IFNULL(TotalInvoiceIn,0) AS TotalInvoiceIn,
		@prev_TotalPaymentOut := @prev_TotalPaymentOut +   IFNULL(TotalPaymentOut,0) AS TotalPaymentOut,
		@prev_TotalPaymentIn := @prev_TotalPaymentIn +   IFNULL(TotalPaymentIn,0) AS TotalPaymentIn,
		@prev_CustomerUnbill := @prev_CustomerUnbill +   IFNULL(CustomerUnbill,0) AS CustomerUnbill,
		@prev_VendrorUnbill := @prev_VendrorUnbill +   IFNULL(VendrorUnbill,0) AS VendrorUnbill,
		date,
		ROUND( ( @prev_TotalInvoiceOut - @prev_TotalPaymentIn ) - ( @prev_TotalInvoiceIn - @prev_TotalPaymentOut ) + ( @prev_CustomerUnbill - @prev_VendrorUnbill ) , v_Round_ ) AS TotalOutstanding,
		ROUND( ( @prev_TotalInvoiceOut - @prev_TotalPaymentIn + @prev_CustomerUnbill ), v_Round_ ) AS TotalPayable,
		ROUND( ( @prev_TotalInvoiceIn - @prev_TotalPaymentOut + @prev_VendrorUnbill), v_Round_ ) AS TotalReceivable
	FROM(
		SELECT 
			dd.date,
			TotalPaymentIn,
			TotalPaymentOut,
			TotalInvoiceOut,
			TotalInvoiceIn,
			CustomerUnbill,
			VendrorUnbill
		FROM tblDimDate dd 
		LEFT JOIN(
			SELECT 
				SUM(IF(InvoiceType=1,GrandTotal,0)) AS TotalInvoiceOut,
				SUM(IF(InvoiceType=2,GrandTotal,0)) AS TotalInvoiceIn,
				DATE(tblInvoice.IssueDate) AS  IssueDate 
			FROM RMBilling3.tblInvoice 
			WHERE 
				CompanyID = p_CompanyID
				AND CurrencyID = p_CurrencyID
				AND ( (InvoiceType = 2) OR ( InvoiceType = 1 AND InvoiceStatus NOT IN ( 'cancel' , 'draft') )  )
				AND (p_AccountID = 0 or AccountID = p_AccountID)
				AND IssueDate BETWEEN p_StartDate AND p_EndDate
			GROUP BY DATE(tblInvoice.IssueDate)
			HAVING (TotalInvoiceOut <> 0 OR TotalInvoiceIn <> 0)
		) TBL ON IssueDate = dd.date
		LEFT JOIN (
			SELECT
				SUM(IF(PaymentType='Payment In',p.Amount,0)) AS TotalPaymentIn ,
				SUM(IF(PaymentType='Payment Out',p.Amount,0)) AS TotalPaymentOut,
				DATE(p.PaymentDate) AS PaymentDate
			FROM RMBilling3.tblPayment p
			INNER JOIN Ratemanagement3.tblAccount ac
				ON ac.AccountID = p.AccountID
			WHERE
				p.CompanyID = p_CompanyID
				AND ac.CurrencyId = p_CurrencyID
				AND p.Status = 'Approved'
				AND p.Recall=0
				AND (p_AccountID = 0 or p.AccountID = p_AccountID)
				AND PaymentDate BETWEEN p_StartDate AND p_EndDate
			GROUP BY DATE(p.PaymentDate)
			HAVING (TotalPaymentIn <> 0 OR TotalPaymentOut <> 0)
		)TBL2 ON PaymentDate = dd.date
		LEFT JOIN tmp_CustomerUnbilled_ cu 
			ON cu.DateID = dd.DateID
		LEFT JOIN tmp_VendorUbilled_ vu
			ON vu.DateID = dd.DateID
		WHERE dd.date BETWEEN p_StartDate AND p_EndDate
		AND ( PaymentDate IS NOT NULL OR IssueDate IS NOT NULL OR cu.DateID IS NOT NULL OR vu.DateID IS NOT NULL)
		ORDER BY dd.date
	)tbl;
	
	IF p_ListType = 'Daily'
	THEN

		SELECT
			TotalOutstanding,
			TotalPayable,
			TotalReceivable,
			date AS Date
		FROM  tmp_FinalResult_;

	END IF;

	IF p_ListType = 'Weekly'
	THEN

		SELECT 
			SUM(TotalOutstanding)  AS TotalOutstanding,
			SUM(TotalPayable)  AS TotalPayable,
			SUM(TotalReceivable)  AS TotalReceivable,
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
			SUM(TotalOutstanding)  AS TotalOutstanding,
			SUM(TotalPayable)  AS TotalPayable,
			SUM(TotalReceivable)  AS TotalReceivable,
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
END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_getUnbilledReport`;

DELIMITER |
CREATE PROCEDURE `prc_getUnbilledReport`(
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
		INNER JOIN tblDimDate dd on dd.DateID = us.DateID
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
		INNER JOIN tblDimDate dd on dd.DateID = us.DateID
		WHERE dd.date BETWEEN p_LastInvoiceDate AND p_Today 
		AND us.CompanyID = p_CompanyID
		AND us.AccountID = p_AccountID;
		
	END IF;
 
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END|
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_getVendorUnbilledReport`;

DELIMITER |
CREATE PROCEDURE `prc_getVendorUnbilledReport`(
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
		FROM tblHeaderV us
		INNER JOIN tblDimDate dd on dd.DateID = us.DateID
		WHERE dd.date BETWEEN p_LastInvoiceDate AND p_Today 
		AND us.CompanyID = p_CompanyID
		AND us.VAccountID = p_AccountID
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
		FROM tblHeaderV us
		INNER JOIN tblDimDate dd on dd.DateID = us.DateID
		WHERE dd.date BETWEEN p_LastInvoiceDate AND p_Today 
		AND us.CompanyID = p_CompanyID
		AND us.VAccountID = p_AccountID;

	END IF;
 
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_getDashboardProfitLoss`;
DELIMITER |
CREATE PROCEDURE `prc_getDashboardProfitLoss`(
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
			(Customerbill - Vendrorbill) AS PL,
			date AS Date
		FROM  tmp_FinalResult_
		ORDER BY date;

	END IF;

	IF p_ListType = 'Weekly'
	THEN

		SELECT 
			(SUM(Customerbill) - SUM(Vendrorbill)) AS PL,
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
			(SUM(Customerbill) - SUM(Vendrorbill)) AS PL,
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
END|
DELIMITER ;