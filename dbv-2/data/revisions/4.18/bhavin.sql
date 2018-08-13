Use `RMBilling3`;

ALTER TABLE `tblInvoiceTemplate`
	ADD COLUMN `DefaultTemplate` INT NULL DEFAULT '0' AFTER `ShowPaymentWidgetInvoice`;
ALTER TABLE `tblInvoiceTemplate`	
	ADD COLUMN `FooterDisplayOnlyFirstPage` INT NULL DEFAULT '0' AFTER `DefaultTemplate`;

CREATE TABLE IF NOT EXISTS `tblProcessCallChargesLog` (
  `LogID` bigint(20) NOT NULL AUTO_INCREMENT,
  `AccountID` int(11) NOT NULL,
  `ServiceID` int(11) NOT NULL DEFAULT '0',
  `InvoiceDate` date NOT NULL,
  `Description` text COLLATE utf8_unicode_ci,
  `Amount` decimal(18,6) DEFAULT NULL,
  `PaymentStatus` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`LogID`),
  UNIQUE KEY `Unique_IX_AccountID_ServiceID_InvoiceDate` (`AccountID`,`ServiceID`,`InvoiceDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP PROCEDURE IF EXISTS `prc_GetAccountSubscriptions`;
DELIMITER //
CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_GetAccountSubscriptions`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_ServiceID` VARCHAR(50),
	IN `p_SubscriptionName` VARCHAR(50),
	IN `p_Status` INT,
	IN `p_Date` DATE,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
BEGIN 
	DECLARE v_OffSet_ INT;
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
	IF p_isExport = 0
	THEN 
		SELECT
			sa.AccountSubscriptionID as AID,
			sa.SequenceNo,
			a.AccountName,
			s.ServiceName,
			sb.Name,
			sa.InvoiceDescription,
			sa.Qty,
			sa.StartDate,
			IF(sa.EndDate = '0000-00-00','',sa.EndDate) as EndDate,
			sa.ActivationFee,
			sa.DailyFee,
			sa.WeeklyFee,
			sa.MonthlyFee,
			sa.QuarterlyFee,
			sa.AnnuallyFee,
			sa.AccountSubscriptionID,
			sa.SubscriptionID,	
			sa.ExemptTax,
			a.AccountID,
			s.ServiceID,
			sa.`Status`
		FROM tblAccountSubscription sa
			INNER JOIN tblBillingSubscription sb
				ON sb.SubscriptionID = sa.SubscriptionID
			INNER JOIN Ratemanagement3.tblAccount a
				ON sa.AccountID = a.AccountID
			INNER JOIN Ratemanagement3.tblService s
				ON sa.ServiceID = s.ServiceID
		WHERE 	(p_AccountID = 0 OR a.AccountID = p_AccountID)
			AND (p_SubscriptionName is null OR sb.Name LIKE concat('%',p_SubscriptionName,'%'))
			AND (p_Status = sa.`Status`)
			AND (p_ServiceID = 0 OR s.ServiceID = p_ServiceID)
		ORDER BY
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SequenceNoASC') THEN sa.SequenceNo
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'SequenceNoDESC') THEN sa.SequenceNo
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN a.AccountName
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN a.AccountName
			END DESC,			
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ServiceNameASC') THEN s.ServiceName
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ServiceNameDESC') THEN s.ServiceName
			END DESC,			
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameASC') THEN sb.Name
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NameDESC') THEN sb.Name
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'QtyASC') THEN sa.Qty
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'QtyDESC') THEN sa.Qty
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StartDateASC') THEN sa.StartDate
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StartDateDESC') THEN sa.StartDate
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateASC') THEN sa.EndDate
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EndDateDESC') THEN sa.EndDate
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ActivationFeeASC') THEN sa.ActivationFee
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'ActivationFeeDESC') THEN sa.ActivationFee
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DailyFeeASC') THEN sa.DailyFee
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'DailyFeeDESC') THEN sa.DailyFee
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'WeeklyFeeASC') THEN sa.WeeklyFee
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'WeeklyFeeDESC') THEN sa.WeeklyFee
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MonthlyFeeASC') THEN sa.MonthlyFee
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'MonthlyFeeDESC') THEN sa.MonthlyFee
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'QuarterlyFeeASC') THEN sa.QuarterlyFee
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'QuarterlyFeeDESC') THEN sa.QuarterlyFee
			END DESC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AnnuallyFeeASC') THEN sa.AnnuallyFee
			END ASC,
			CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AnnuallyFeeDESC') THEN sa.AnnuallyFee
			END DESC
		LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT
			COUNT(*) AS totalcount
		FROM tblAccountSubscription sa
			INNER JOIN tblBillingSubscription sb
				ON sb.SubscriptionID = sa.SubscriptionID
			INNER JOIN Ratemanagement3.tblAccount a
				ON sa.AccountID = a.AccountID
			INNER JOIN Ratemanagement3.tblService s
				ON sa.ServiceID = s.ServiceID
		WHERE 	(p_AccountID = 0 OR a.AccountID = p_AccountID)
			AND (p_SubscriptionName is null OR sb.Name LIKE concat('%',p_SubscriptionName,'%'))
			AND (p_Status = sa.`Status`)
			AND (p_ServiceID = 0 OR s.ServiceID = p_ServiceID);
	END IF;

	IF p_isExport = 1
	THEN
		SELECT
			sa.SequenceNo,
			a.AccountName,
			s.ServiceName,
			sb.Name,
			sa.InvoiceDescription,
			sa.Qty,
			sa.StartDate,
			IF(sa.EndDate = '0000-00-00','',sa.EndDate) as EndDate,
			sa.ActivationFee,
			sa.DailyFee,
			sa.WeeklyFee,
			sa.MonthlyFee,
			sa.QuarterlyFee,
			sa.AnnuallyFee,
			sa.AccountSubscriptionID,
			sa.SubscriptionID,	
			sa.ExemptTax,
			sa.`Status`
		FROM tblAccountSubscription sa
			INNER JOIN tblBillingSubscription sb
				ON sb.SubscriptionID = sa.SubscriptionID
			INNER JOIN Ratemanagement3.tblAccount a
				ON sa.AccountID = a.AccountID
			INNER JOIN Ratemanagement3.tblService s
				ON sa.ServiceID = s.ServiceID
		WHERE 	(p_AccountID = 0 OR a.AccountID = p_AccountID)
			AND (p_SubscriptionName is null OR sb.Name LIKE concat('%',p_SubscriptionName,'%'))
			AND (p_Status = sa.`Status`)
			AND (p_ServiceID =0 OR s.ServiceID = p_ServiceID);
	END IF;

SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_reseller_ProcesssCDR`;
DELIMITER //
CREATE PROCEDURE `prc_reseller_ProcesssCDR`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200),
	IN `p_RateCDR` INT,
	IN `p_RateFormat` INT,
	IN `p_NameFormat` VARCHAR(50),
	IN `p_RateMethod` VARCHAR(50),
	IN `p_SpecifyRate` DECIMAL(18,6),
	IN `p_OutboundTableID` INT,
	IN `p_InboundTableID` INT,
	IN `p_RerateAccounts` INT
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
	
	
	DROP TEMPORARY TABLE IF EXISTS tmp_tblTempRateLog_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblTempRateLog_(
		`CompanyID` INT(11) NULL DEFAULT NULL,
		`CompanyGatewayID` INT(11) NULL DEFAULT NULL,
		`MessageType` INT(11) NOT NULL,
		`Message` VARCHAR(500) NOT NULL,
		`RateDate` DATE NOT NULL
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_Customers_;
	CREATE TEMPORARY TABLE tmp_Customers_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AccountID INT,
		CompanyGatewayID INT
	);


	DROP TEMPORARY TABLE IF EXISTS tmp_Service_;
	CREATE TEMPORARY TABLE tmp_Service_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		ServiceID INT
	);
	SET @stm = CONCAT('
	INSERT INTO tmp_Service_ (ServiceID)
	SELECT DISTINCT ServiceID FROM RMCDR3.`' , p_tbltempusagedetail_name , '` ud WHERE ProcessID="' , p_processId , '" AND ServiceID > 0;
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;

	SET @stm = CONCAT('
	INSERT INTO tmp_Service_ (ServiceID)
	SELECT DISTINCT tblService.ServiceID
	FROM Ratemanagement3.tblService
	LEFT JOIN  RMCDR3.`' , p_tbltempusagedetail_name , '` ud
	ON tblService.ServiceID = ud.ServiceID AND ProcessID="' , p_processId , '"
	WHERE tblService.ServiceID > 0 AND tblService.CompanyGatewayID > 0 AND ud.ServiceID IS NULL
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;
	
	IF ( ( SELECT COUNT(*) FROM tmp_Service_ ) > 0 OR p_OutboundTableID > 0)
	THEN
		CALL prc_RerateOutboundService(p_processId,p_tbltempusagedetail_name,p_RateCDR,p_RateFormat,p_RateMethod,p_SpecifyRate,p_OutboundTableID);
	ELSE
		CALL prc_RerateOutboundTrunk(p_processId,p_tbltempusagedetail_name,p_RateCDR,p_RateFormat,p_RateMethod,p_SpecifyRate);
		CALL prc_autoUpdateTrunk(p_CompanyID,p_CompanyGatewayID);
	END IF;	 

	IF p_RateCDR = 0 AND p_RateFormat = 2
	THEN

		DROP TEMPORARY TABLE IF EXISTS tmp_Accounts_;
		CREATE TEMPORARY TABLE tmp_Accounts_  (
			RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			AccountID INT
		);
		SET @stm = CONCAT('
		INSERT INTO tmp_Accounts_(AccountID)
		SELECT DISTINCT AccountID FROM RMCDR3.`' , p_tbltempusagedetail_name , '` ud WHERE ProcessID="' , p_processId , '" AND AccountID IS NOT NULL AND TrunkID IS NOT NULL;
		');

		PREPARE stm FROM @stm;
		EXECUTE stm;
		DEALLOCATE PREPARE stm;
		
		CALL Ratemanagement3.prc_getDefaultCodes(p_CompanyID);
		CALL prc_updateDefaultPrefix(p_processId, p_tbltempusagedetail_name);

	END IF;

	CALL prc_RerateInboundCalls(p_CompanyID,p_processId,p_tbltempusagedetail_name,p_RateCDR,p_RateMethod,p_SpecifyRate,p_InboundTableID);		-- for mirta only	
	IF (  p_RateCDR = 1 )
	THEN
		
		SET @stm = CONCAT('
			UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` ud
			INNER JOIN  RMCDR3.`' , p_tbltempusagedetail_name ,'_Retail' , '` udr ON ud.TempUsageDetailID = udr.TempUsageDetailID AND ud.ProcessID = udr.ProcessID
			SET cost = 0
			WHERE ud.ProcessID="' , p_processId , '" AND udr.cc_type = 4 ;
		');

		PREPARE stm FROM @stm;
		EXECUTE stm;
		DEALLOCATE PREPARE stm;

	END IF;
	
	CALL prc_CreateRerateLog(p_processId,p_tbltempusagedetail_name,p_RateCDR);

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;