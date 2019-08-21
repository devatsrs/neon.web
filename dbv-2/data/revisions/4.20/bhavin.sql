USE `RMBilling3`;

DROP PROCEDURE IF EXISTS `prc_getPBXExportPayment`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getPBXExportPayment`(
	IN `p_CompanyID` INT,
	IN `p_start_date` DATETIME,
	IN `p_Recall` INT
)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	IF p_Recall=0
	THEN
		SELECT
			ac.Number,
			Concat(PaymentID,' ',Ifnull(Notes,'') ) as Notes,
			PaymentDate,
			Amount
		FROM tblPayment
			INNER JOIN Ratemanagement3.tblAccount as ac ON ac.AccountID=tblPayment.AccountID
		WHERE
			PaymentType='Payment In'
			-- AND ac.CompanyId = p_CompanyID
			AND tblPayment.Status='Approved'
			AND Recall=p_Recall
			AND PaymentDate>p_start_date
			
			UNION		 
			SELECT 
				a.Number,
				Concat(sl.AccountBalanceSubscriptionLogID,' ',sl.Description) as Notes,
				sl.IssueDate as PaymentDate,
				(sl.TotalAmount * -1) as Amount
			FROM Ratemanagement3.tblAccountBalanceSubscriptionLog sl 
				INNER JOIN Ratemanagement3.tblAccountBalanceLog bl ON bl.AccountBalanceLogID=sl.AccountBalanceLogID
				INNER JOIN Ratemanagement3.tblAccount a ON a.AccountID=bl.AccountID
			WHERE 
			-- a.CompanyId=p_CompanyID	AND 
			sl.IssueDate > p_start_date;
			
	END IF;		
	IF p_Recall=1
	THEN
	
		SELECT
			ac.Number,
			Concat(PaymentID,' ',Ifnull(Notes,'') ) as Notes,
			PaymentDate,
			Amount
		FROM tblPayment
			INNER JOIN Ratemanagement3.tblAccount as ac ON ac.AccountID=tblPayment.AccountID
		WHERE
			PaymentType='Payment In'
			-- AND ac.CompanyId = p_CompanyID
			AND tblPayment.Status='Approved'
			AND Recall=p_Recall
			AND PaymentDate>p_start_date;
	
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
	
	CALL Ratemanagement3.prc_UpdateMysqlPID(p_processId);
	
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

	CALL prc_RerateInboundCalls(p_CompanyID,p_processId,p_tbltempusagedetail_name,p_RateCDR,p_RateMethod,p_SpecifyRate,p_InboundTableID);		
	IF (  p_RateCDR = 1 )
	THEN
		
		SET @stm = CONCAT('
			UPDATE RMCDR3.`' , p_tbltempusagedetail_name , '` ud
			INNER JOIN  RMCDR3.`' , p_tbltempusagedetail_name ,'_Retail' , '` udr ON ud.TempUsageDetailID = udr.TempUsageDetailID AND ud.ProcessID = udr.ProcessID
			SET cost = 0
			WHERE ud.ProcessID="' , p_processId , '" AND 
													  ( 
														udr.cc_type = 4 
														  OR
													    CHAR_LENGTH(cld) <= 6 
													 );
		');

		PREPARE stm FROM @stm;
		EXECUTE stm;
		DEALLOCATE PREPARE stm;

	END IF;
	
	CALL prc_CreateRerateLog(p_processId,p_tbltempusagedetail_name,p_RateCDR);

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;