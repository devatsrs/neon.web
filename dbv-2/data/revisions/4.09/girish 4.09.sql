DROP PROCEDURE IF EXISTS `prc_getDashboardinvoiceExpense`;
DELIMITER //
CREATE DEFINER=`neon-user-bhavin`@`117.247.87.156` PROCEDURE `prc_getDashboardinvoiceExpense`(
	IN `p_CompanyID` INT,
	IN `p_CurrencyID` INT,
	IN `p_AccountID` INT,
	IN `p_StartDate` VARCHAR(50),
	IN `p_EndDate` VARCHAR(50),
	IN `p_ListType` VARCHAR(50)
)
BEGIN
	DECLARE v_Round_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_MonthlyTotalDue_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_MonthlyTotalDue_(
		`Year` int,
		`Month` int,
		`Week` int,
		MonthName varchar(50),
		TotalAmount float,
		CurrencyID int,
		InvoiceStatus VARCHAR(50)
	);
	DROP TEMPORARY TABLE IF EXISTS tmp_MonthlyTotalReceived_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_MonthlyTotalReceived_(
		`Year` int,
		`Month` int,
		`Week` int,
		MonthName varchar(50),
		TotalAmount float,
		OutAmount float,
		CurrencyID int
	);
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	INSERT INTO tmp_MonthlyTotalDue_
	SELECT YEAR(IssueDate) as Year
			,MONTH(IssueDate) as Month
			,WEEK(IssueDate) as Week
			,MONTHNAME(MAX(IssueDate)) as  MonthName
			,ROUND(COALESCE(SUM(GrandTotal),0),v_Round_)as TotalAmount
			,CurrencyID
			,InvoiceStatus
	FROM tblInvoice
	WHERE 
		CompanyID = p_CompanyID
		AND CurrencyID = p_CurrencyID
		AND InvoiceType = 1 -- Invoice Out
		AND InvoiceStatus NOT IN ( 'cancel' , 'draft' )
		AND (
			(p_EndDate = '0' AND fnGetMonthDifference(IssueDate,NOW()) <= p_StartDate) OR
			(p_EndDate <> '0' AND IssueDate between p_StartDate AND p_EndDate)
			)
		AND (p_AccountID = 0 or AccountID = p_AccountID)
	GROUP BY 
			YEAR(IssueDate)
			,MONTH(IssueDate)
			,Week
			,CurrencyID
			,InvoiceStatus
	ORDER BY 
			Year
			,Month
			,Week;


	DROP TEMPORARY TABLE IF EXISTS tmp_tblPayment_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblPayment_(
		PaymentDate Date,
		Amount float,
		OutAmount float,
		CurrencyID int
	);
	/* payment recevied invoice*/
	INSERT INTO tmp_tblPayment_ (PaymentDate,Amount,OutAmount,CurrencyID)
	SELECT
		PaymentDate,
		SUM(Amount),
		IF(inv.InvoiceStatus='paid' OR inv.InvoiceStatus='partially_paid' ,inv.GrandTotal - SUM(Amount),-SUM(Amount)) as OutAmount,
		TBL.CurrencyId
	FROM	
		(
		SELECT 
			CASE WHEN inv.InvoiceID IS NOT NULL
			THEN
				inv.IssueDate
			ELSE
				p.PaymentDate
			END as PaymentDate,
			p.Amount,
			inv.InvoiceID,
			ac.CurrencyId
			
		FROM tblPayment p 
		INNER JOIN NeonRMDev.tblAccount ac 
			ON ac.AccountID = p.AccountID
		LEFT JOIN tblInvoice inv ON p.AccountID = inv.AccountID
			AND p.InvoiceID = inv.InvoiceID
			AND p.Status = 'Approved' 
			AND p.AccountID = inv.AccountID 
			AND p.Recall=0
			AND InvoiceType = 1 
		WHERE 
				p.CompanyID = p_CompanyID
			AND ac.CurrencyId = p_CurrencyID
			AND (
				(p_EndDate = '0' AND ((fnGetMonthDifference(p.PaymentDate,NOW()) <= p_StartDate) OR (fnGetMonthDifference(inv.IssueDate,NOW()) <= p_StartDate))) OR
				(p_EndDate<>'0' AND ( p.PaymentDate BETWEEN p_StartDate AND p_EndDate  OR  inv.IssueDate BETWEEN p_StartDate AND p_EndDate))
				)
			AND p.Status = 'Approved'
			AND p.Recall=0
			AND p.PaymentType = 'Payment In'
			AND (p_AccountID = 0 or ac.AccountID = p_AccountID)
			)TBL
	LEFT JOIN tblInvoice inv
		ON TBL.InvoiceID = inv.InvoiceID	
	GROUP BY TBL.PaymentDate,TBL.InvoiceID;

	
		
	IF p_ListType = 'Weekly'
	THEN
	
		INSERT INTO tmp_MonthlyTotalReceived_
		SELECT YEAR(p.PaymentDate) as Year
				,MONTH(p.PaymentDate) as Month
				,WEEK(p.PaymentDate) as week
				,MONTHNAME(MAX(p.PaymentDate)) as  MonthName
				,ROUND(COALESCE(SUM(p.Amount),0),v_Round_) as TotalAmount
				,ROUND(COALESCE(SUM(p.OutAmount),0),v_Round_) as OutAmount
				,CurrencyID
		FROM tmp_tblPayment_ p 
		GROUP BY 
			YEAR(p.PaymentDate)
			,MONTH(p.PaymentDate)
			,week
			,CurrencyID		
		ORDER BY 
			Year
			,Month
			,week;
		
		SELECT 
			CONCAT(td.`Week`,'-',MAX( td.Year)) AS MonthName ,
			MAX( td.Year) AS `Year`,
			ROUND(COALESCE(SUM(td.TotalAmount),0),v_Round_) TotalInvoice ,  
			ROUND(COALESCE(MAX(tr.TotalAmount),0),v_Round_) PaymentReceived, 
			ROUND(SUM(IF(InvoiceStatus ='paid' OR InvoiceStatus='partially_paid' ,0,td.TotalAmount)) + COALESCE(MAX(tr.OutAmount),0) ,v_Round_) TotalOutstanding ,
			td.CurrencyID CurrencyID,
			'Weekly' as ftype 
		FROM  
			tmp_MonthlyTotalDue_ td
		LEFT JOIN tmp_MonthlyTotalReceived_ tr 
			ON td.Week = tr.Week 
			AND td.Year = tr.Year 
			AND td.Month = tr.Month 
			AND tr.CurrencyID = td.CurrencyID
		GROUP BY 
			td.Week,
			td.Year,
			td.CurrencyID
		ORDER BY 
			td.Year
			,td.Week;
	END IF;

	IF p_ListType = 'Monthly'
	THEN
		INSERT INTO tmp_MonthlyTotalReceived_
		SELECT YEAR(p.PaymentDate) as Year
				,MONTH(p.PaymentDate) as Month
				,1			
				,MONTHNAME(MAX(p.PaymentDate)) as  MonthName				
				,ROUND(COALESCE(SUM(p.Amount),0),v_Round_) as TotalAmount
				,ROUND(COALESCE(SUM(p.OutAmount),0),v_Round_) as OutAmount
				,CurrencyID
		FROM tmp_tblPayment_ p 
		GROUP BY 
			YEAR(p.PaymentDate)
			,MONTH(p.PaymentDate)		
			,CurrencyID		
		ORDER BY 
			Year
			,Month;
		
		
		SELECT 
			CONCAT(CONCAT(case when td.Month <10 then concat('0',td.Month) else td.Month End, '/'), td.Year) AS MonthName ,
			td.Year,
			ROUND(COALESCE(SUM(td.TotalAmount),0),v_Round_) TotalInvoice ,  
			ROUND(COALESCE(MAX(tr.TotalAmount),0),v_Round_) PaymentReceived, 
			ROUND(SUM(IF(InvoiceStatus ='paid' OR InvoiceStatus='partially_paid' ,0,td.TotalAmount)) + COALESCE(MAX(tr.OutAmount),0) ,v_Round_) TotalOutstanding ,
			td.CurrencyID CurrencyID,
			'Monthly' as ftype
		FROM  
			tmp_MonthlyTotalDue_ td
		LEFT JOIN tmp_MonthlyTotalReceived_ tr 
			ON td.Month = tr.Month 
			AND td.Year = tr.Year 
			-- AND td.Week = tr.Week 
			AND tr.CurrencyID = td.CurrencyID
		GROUP BY 
			td.Month,
			td.Year,
			td.CurrencyID
		ORDER BY 
			td.Year
			,td.Month;
	END IF;

	IF p_ListType = 'Yearly'
	THEN
			INSERT INTO tmp_MonthlyTotalReceived_
			SELECT YEAR(p.PaymentDate) as Year
					,1 -- MONTH(p.PaymentDate) as Month
					,1			
					,'Oct' as  MonthName
					,ROUND(COALESCE(SUM(p.Amount),0),v_Round_) as TotalAmount
					,ROUND(COALESCE(SUM(p.OutAmount),0),v_Round_) as OutAmount
					,CurrencyID
			FROM tmp_tblPayment_ p 
			GROUP BY 
				YEAR(p.PaymentDate)
			 	-- ,MONTH(p.PaymentDate)		
				,CurrencyID		
			ORDER BY 
				Year;
			-- 	,Month;
			
		SELECT 
			td.Year as MonthName,
			ROUND(COALESCE(SUM(td.TotalAmount),0),v_Round_) TotalInvoice ,  
			ROUND(COALESCE(MAX(tr.TotalAmount),0),v_Round_) PaymentReceived, 
			ROUND(SUM(IF(InvoiceStatus ='paid' OR InvoiceStatus='partially_paid' ,0,td.TotalAmount)) + COALESCE(MAX(tr.OutAmount),0) ,v_Round_) TotalOutstanding ,
			td.CurrencyID CurrencyID,
			'Yearly' as ftype
		FROM  
			tmp_MonthlyTotalDue_ td
		LEFT JOIN tmp_MonthlyTotalReceived_ tr 
			ON td.Year = tr.Year 
		-- 	AND td.Week = tr.Week 
		-- 	AND td.Month = tr.Month 
			AND tr.CurrencyID = td.CurrencyID
		GROUP BY 
			td.Year,
			td.CurrencyID
		ORDER BY 
			td.Year;
	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;

-- Dumping structure for procedure NeonBillingDev.prc_ProcesssCDR
DROP PROCEDURE IF EXISTS `prc_ProcesssCDR`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_ProcesssCDR`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200),
	IN `p_RateCDR` INT,
	IN `p_RateFormat` INT,
	IN `p_NameFormat` VARCHAR(50),
	IN `p_RateMethod` VARCHAR(50),
	IN `p_SpecifyRate` DECIMAL(18,6)
)
BEGIN
	DECLARE v_rowCount_ INT;
	DECLARE v_pointer_ INT;
	DECLARE v_AccountID_ INT;
	DECLARE v_TrunkID_ INT;
	DECLARE v_CDRUpload_ INT;
	DECLARE v_NewAccountIDCount_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_tblTempRateLog_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tblTempRateLog_(
		`CompanyID` INT(11) NULL DEFAULT NULL,
		`CompanyGatewayID` INT(11) NULL DEFAULT NULL,
		`MessageType` INT(11) NOT NULL,
		`Message` VARCHAR(500) NOT NULL,
		`RateDate` DATE NOT NULL	
	);

	/* insert new account */
	SET @stm = CONCAT('
	INSERT INTO tblGatewayAccount (CompanyID, CompanyGatewayID, GatewayAccountID, AccountName)
	SELECT
		DISTINCT
		ud.CompanyID,
		ud.CompanyGatewayID,
		ud.GatewayAccountID,
		ud.GatewayAccountID
	FROM NeonCDRDev.' , p_tbltempusagedetail_name , ' ud
	LEFT JOIN tblGatewayAccount ga
		ON ga.GatewayAccountID = ud.GatewayAccountID
		AND ga.CompanyGatewayID = ud.CompanyGatewayID
		AND ga.CompanyID = ud.CompanyID
	WHERE ProcessID =  "' , p_processId , '"
		AND ga.GatewayAccountID IS NULL
		AND ud.GatewayAccountID IS NOT NULL;
	');

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	/* active new account */
	CALL  prc_getActiveGatewayAccount(p_CompanyID,p_CompanyGatewayID,'0','1',p_NameFormat);

	/* update cdr account */
	SET @stm = CONCAT('
	UPDATE NeonCDRDev.`' , p_tbltempusagedetail_name , '` uh
	INNER JOIN tblGatewayAccount ga
		ON  ga.CompanyID = uh.CompanyID
		AND ga.CompanyGatewayID = uh.CompanyGatewayID
		AND ga.GatewayAccountID = uh.GatewayAccountID
	SET uh.AccountID = ga.AccountID
	WHERE uh.AccountID IS NULL
	AND ga.AccountID is not null
	AND uh.CompanyID = ' ,  p_companyid , '
	AND uh.ProcessID = "' , p_processId , '" ;
	');
	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SELECT COUNT(*) INTO v_NewAccountIDCount_ 
	FROM NeonCDRDev.tblUsageHeader uh
	INNER JOIN tblGatewayAccount ga
		ON  ga.CompanyID = uh.CompanyID
		AND ga.CompanyGatewayID = uh.CompanyGatewayID
		AND ga.GatewayAccountID = uh.GatewayAccountID
	WHERE uh.AccountID IS NULL
	AND ga.AccountID is not null
	AND uh.CompanyID = p_CompanyID
	AND uh.CompanyGatewayID = p_CompanyGatewayID;

	IF v_NewAccountIDCount_ > 0
	THEN

		/* update header cdr account */
		UPDATE NeonCDRDev.tblUsageHeader uh
		INNER JOIN tblGatewayAccount ga
			ON  ga.CompanyID = uh.CompanyID
			AND ga.CompanyGatewayID = uh.CompanyGatewayID
			AND ga.GatewayAccountID = uh.GatewayAccountID
		SET uh.AccountID = ga.AccountID
		WHERE uh.AccountID IS NULL
		AND ga.AccountID is not null
		AND uh.CompanyID = p_CompanyID
		AND uh.CompanyGatewayID = p_CompanyGatewayID;

	END IF;

	/* temp accounts and trunks*/
	DROP TEMPORARY TABLE IF EXISTS tmp_AccountTrunkCdrUpload_;
	CREATE TEMPORARY TABLE tmp_AccountTrunkCdrUpload_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AccountID INT,
		TrunkID INT
	);
	SET @stm = CONCAT('
	INSERT INTO tmp_AccountTrunkCdrUpload_(AccountID,TrunkID)
	SELECT DISTINCT AccountID,TrunkID FROM NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud WHERE ProcessID="' , p_processId , '" AND AccountID IS NOT NULL AND TrunkID IS NOT NULL AND ud.is_inbound = 0;
	');

	SET v_CDRUpload_ = (SELECT COUNT(*) FROM tmp_AccountTrunkCdrUpload_);

	IF v_CDRUpload_ > 0
	THEN
		/* update UseInBilling when cdr upload*/
		SET @stm = CONCAT('
		UPDATE NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud
		INNER JOIN NeonRMDev.tblCustomerTrunk ct 
			ON ct.AccountID = ud.AccountID AND ct.TrunkID = ud.TrunkID AND ct.Status =1
		INNER JOIN NeonRMDev.tblTrunk t 
			ON t.TrunkID = ct.TrunkID  
			SET ud.UseInBilling=ct.UseInBilling,ud.TrunkPrefix = ct.Prefix
		WHERE  ud.ProcessID = "' , p_processId , '";
		');
	END IF;

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	/* if rate format is prefix base not charge code*/
	IF p_RateFormat = 2
	THEN

		/* update trunk without use in billing*/
		SET @stm = CONCAT('
		UPDATE NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud
		INNER JOIN NeonRMDev.tblCustomerTrunk ct 
			ON ct.AccountID = ud.AccountID AND ct.Status =1 
			AND ct.UseInBilling = 0 
		INNER JOIN NeonRMDev.tblTrunk t 
			ON t.TrunkID = ct.TrunkID  
			SET ud.trunk = t.Trunk,ud.TrunkID =t.TrunkID,ud.UseInBilling=ct.UseInBilling
		WHERE  ud.ProcessID = "' , p_processId , '" AND ud.is_inbound = 0 AND ud.TrunkID IS NULL;
		');

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

		/* update trunk with use in billing*/
		SET @stm = CONCAT('
		UPDATE NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud
		INNER JOIN NeonRMDev.tblCustomerTrunk ct 
			ON ct.AccountID = ud.AccountID AND ct.Status =1 
			AND ct.UseInBilling = 1 AND cld LIKE CONCAT(ct.Prefix , "%")
		INNER JOIN NeonRMDev.tblTrunk t 
			ON t.TrunkID = ct.TrunkID  
			SET ud.trunk = t.Trunk,ud.TrunkID =t.TrunkID,ud.UseInBilling=ct.UseInBilling,ud.TrunkPrefix = ct.Prefix
		WHERE  ud.ProcessID = "' , p_processId , '" AND ud.is_inbound = 0 AND ud.TrunkID IS NULL;
		');

		PREPARE stm FROM @stm;
		EXECUTE stm;
		DEALLOCATE PREPARE stm;

	END IF;

	/* if rerate on */
	IF p_RateCDR = 1
	THEN

		SET @stm = CONCAT('UPDATE   NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud SET cost = 0,is_rerated=0  WHERE ProcessID = "',p_processId,'" AND ( AccountID IS NULL OR TrunkID IS NULL ) ') ;

		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

	END IF;

	/* temp accounts and trunks*/
	DROP TEMPORARY TABLE IF EXISTS tmp_AccountTrunk_;
	CREATE TEMPORARY TABLE tmp_AccountTrunk_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AccountID INT,
		TrunkID INT
	);
	SET @stm = CONCAT('
	INSERT INTO tmp_AccountTrunk_(AccountID,TrunkID)
	SELECT DISTINCT AccountID,TrunkID FROM NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud WHERE ProcessID="' , p_processId , '" AND AccountID IS NOT NULL AND TrunkID IS NOT NULL AND ud.is_inbound = 0;
	');

	PREPARE stm FROM @stm;
	EXECUTE stm;
	DEALLOCATE PREPARE stm;

	SET v_pointer_ = 1;
	SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_AccountTrunk_);

	WHILE v_pointer_ <= v_rowCount_
	DO

		SET v_TrunkID_ = (SELECT TrunkID FROM tmp_AccountTrunk_ t WHERE t.RowID = v_pointer_); 
		SET v_AccountID_ = (SELECT AccountID FROM tmp_AccountTrunk_ t WHERE t.RowID = v_pointer_);

		/* get outbound rate process*/
		CALL NeonRMDev.prc_getCustomerCodeRate(v_AccountID_,v_TrunkID_,p_RateCDR,p_RateMethod,p_SpecifyRate);

		/* update prefix outbound process*/
		/* if rate format is prefix base not charge code*/
		IF p_RateFormat = 2
		THEN
			CALL prc_updatePrefix(v_AccountID_,v_TrunkID_, p_processId, p_tbltempusagedetail_name);
		END IF;

		/* outbound rerate process*/
		IF p_RateCDR = 1
		THEN
			CALL prc_updateOutboundRate(v_AccountID_,v_TrunkID_, p_processId, p_tbltempusagedetail_name);
		END IF;

		SET v_pointer_ = v_pointer_ + 1;
	END WHILE;

	/* if rerate is off and acconts and trunks not setup update prefix from default codedeck*/
	IF p_RateCDR = 0 AND p_RateFormat = 2
	THEN 
		/* temp accounts and trunks*/
		DROP TEMPORARY TABLE IF EXISTS tmp_Accounts_;
		CREATE TEMPORARY TABLE tmp_Accounts_  (
			RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			AccountID INT
		);
		SET @stm = CONCAT('
		INSERT INTO tmp_Accounts_(AccountID)
		SELECT DISTINCT AccountID FROM NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud WHERE ProcessID="' , p_processId , '" AND AccountID IS NOT NULL AND TrunkID IS NOT NULL;
		');

		PREPARE stm FROM @stm;
		EXECUTE stm;
		DEALLOCATE PREPARE stm;

		/* get default code */
		CALL NeonRMDev.prc_getDefaultCodes(p_CompanyID);

		/* update prefix from default codes 
		 if rate format is prefix base not charge code*/
		CALL prc_updateDefaultPrefix(p_processId, p_tbltempusagedetail_name);

	END IF;

	/* inbound rerate process*/
	CALL prc_RerateInboundCalls(p_CompanyID,p_processId,p_tbltempusagedetail_name,p_RateCDR,p_RateMethod,p_SpecifyRate);

	SET @stm = CONCAT('
	INSERT INTO tmp_tblTempRateLog_ (CompanyID,CompanyGatewayID,MessageType,Message,RateDate)
	SELECT DISTINCT ud.CompanyID,ud.CompanyGatewayID,1,  CONCAT( "Account:  " , ga.AccountName ," - Gateway: ",cg.Title," - Doesnt exist in NEON") as Message ,DATE(NOW())
	FROM NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud
	INNER JOIN tblGatewayAccount ga 
		ON ga.CompanyGatewayID = ud.CompanyGatewayID
		AND ga.CompanyID = ud.CompanyID
		AND ga.GatewayAccountID = ud.GatewayAccountID
	INNER JOIN NeonRMDev.tblCompanyGateway cg ON cg.CompanyGatewayID = ud.CompanyGatewayID
	WHERE ud.ProcessID = "' , p_processid  , '" and ud.AccountID IS NULL');
	
	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
	
	IF p_RateCDR = 1
	THEN
		SET @stm = CONCAT('
		INSERT INTO tmp_tblTempRateLog_ (CompanyID,CompanyGatewayID,MessageType,Message,RateDate)
		SELECT DISTINCT ud.CompanyID,ud.CompanyGatewayID,2,  CONCAT( "Account:  " , a.AccountName ," - Trunk: ",ud.trunk," - Unable to Rerate number ",IFNULL(ud.cld,"")," - No Matching prefix found") as Message ,DATE(NOW())
		FROM  NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud
		INNER JOIN NeonRMDev.tblAccount a on  ud.AccountID = a.AccountID
		WHERE ud.ProcessID = "' , p_processid  , '" and ud.is_inbound = 0 AND ud.is_rerated = 0 AND ud.billed_second <> 0 and ud.area_prefix = "Other"');
		
		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
		
		SET @stm = CONCAT('
		INSERT INTO tmp_tblTempRateLog_ (CompanyID,CompanyGatewayID,MessageType,Message,RateDate)
		SELECT DISTINCT ud.CompanyID,ud.CompanyGatewayID,3,  CONCAT( "Account:  " , a.AccountName ,  " - Unable to Rerate number ",IFNULL(ud.cld,"")," - No Matching prefix found") as Message ,DATE(NOW())
		FROM  NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud
		INNER JOIN NeonRMDev.tblAccount a on  ud.AccountID = a.AccountID
		WHERE ud.ProcessID = "' , p_processid  , '" and ud.is_inbound = 1 AND ud.is_rerated = 0 AND ud.billed_second <> 0 and ud.area_prefix = "Other"');
		
		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
		
		SET @stm = CONCAT('
		INSERT INTO NeonRMDev.tblTempRateLog (CompanyID,CompanyGatewayID,MessageType,Message,RateDate,SentStatus,created_at)
		SELECT rt.CompanyID,rt.CompanyGatewayID,rt.MessageType,rt.Message,rt.RateDate,0 as SentStatus,NOW() as created_at FROM tmp_tblTempRateLog_ rt
		LEFT JOIN NeonRMDev.tblTempRateLog rt2 
			ON rt.CompanyID = rt2.CompanyID
			AND rt.CompanyGatewayID = rt2.CompanyGatewayID
			AND rt.MessageType = rt2.MessageType
			AND rt.Message = rt2.Message
			AND rt.RateDate = rt2.RateDate
		WHERE rt2.TempRateLogID IS NULL;
		');
		PREPARE stmt FROM @stm;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
		
	END IF;
	
	SELECT DISTINCT Message FROM tmp_tblTempRateLog_;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 

END//
DELIMITER ;

-- Dumping structure for procedure NeonBillingDev.prc_RerateInboundCalls
DROP PROCEDURE IF EXISTS `prc_RerateInboundCalls`;
DELIMITER //
CREATE DEFINER=`neon-user-bhavin`@`117.247.87.156` PROCEDURE `prc_RerateInboundCalls`(
	IN `p_CompanyID` INT,
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200),
	IN `p_RateCDR` INT,
	IN `p_RateMethod` VARCHAR(50),
	IN `p_SpecifyRate` DECIMAL(18,6)
)
BEGIN
	
	DECLARE v_rowCount_ INT;
	DECLARE v_pointer_ INT;
	DECLARE v_AccountID_ INT;
	DECLARE v_cld_ VARCHAR(500);
	
	IF p_RateCDR = 1  
	THEN
	
		IF (SELECT COUNT(*) FROM NeonRMDev.tblCLIRateTable WHERE CompanyID = p_CompanyID AND RateTableID > 0) > 0
		THEN
		
			/* temp accounts*/
			DROP TEMPORARY TABLE IF EXISTS tmp_Account_;
			CREATE TEMPORARY TABLE tmp_Account_  (
				RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
				AccountID INT,
				cld VARCHAR(500) NULL DEFAULT NULL
			);
			SET @stm = CONCAT('
			INSERT INTO tmp_Account_(AccountID,cld)
			SELECT DISTINCT AccountID,cld FROM NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud WHERE ProcessID="' , p_processId , '" AND AccountID IS NOT NULL AND ud.is_inbound = 1;
			');
			
			PREPARE stm FROM @stm;
			EXECUTE stm;
			DEALLOCATE PREPARE stm;
		
		ELSE
			
			
			/* temp accounts*/
			DROP TEMPORARY TABLE IF EXISTS tmp_Account_;
			CREATE TEMPORARY TABLE tmp_Account_  (
				RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
				AccountID INT,
				cld VARCHAR(500) NULL DEFAULT NULL
			);
			SET @stm = CONCAT('
			INSERT INTO tmp_Account_(AccountID,cld)
			SELECT DISTINCT AccountID,"" FROM NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud WHERE ProcessID="' , p_processId , '" AND AccountID IS NOT NULL AND ud.is_inbound = 1;
			');
			
			PREPARE stm FROM @stm;
			EXECUTE stm;
			DEALLOCATE PREPARE stm;
		
		END IF;

		
		
		SET v_pointer_ = 1;
		SET v_rowCount_ = (SELECT COUNT(*) FROM tmp_Account_);

		WHILE v_pointer_ <= v_rowCount_
		DO

			SET v_AccountID_ = (SELECT AccountID FROM tmp_Account_ t WHERE t.RowID = v_pointer_);
			SET v_cld_ = (SELECT cld FROM tmp_Account_ t WHERE t.RowID = v_pointer_);
			
			/* get inbound rate process*/
			CALL NeonRMDev.prc_getCustomerInboundRate(v_AccountID_,p_RateCDR,p_RateMethod,p_SpecifyRate,v_cld_);
			
			/* update prefix inbound process*/
			CALL prc_updateInboundPrefix(v_AccountID_, p_processId, p_tbltempusagedetail_name,v_cld_);
			
			/* inbound rerate process*/
			CALL prc_updateInboundRate(v_AccountID_, p_processId, p_tbltempusagedetail_name,v_cld_);
			
			SET v_pointer_ = v_pointer_ + 1;
			
		END WHILE;

	END IF;


END//
DELIMITER ;

-- Dumping structure for procedure NeonBillingDev.prc_updateInboundPrefix
DROP PROCEDURE IF EXISTS `prc_updateInboundPrefix`;
DELIMITER //
CREATE DEFINER=`neon-user`@`localhost` PROCEDURE `prc_updateInboundPrefix`(
	IN `p_AccountID` INT,
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200),
	IN `p_CLD` VARCHAR(500)
)
BEGIN

	DROP TEMPORARY TABLE IF EXISTS tmp_TempUsageDetail_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempUsageDetail_(
		TempUsageDetailID int,
		prefix varchar(50),
		INDEX IX_TempUsageDetailID(`TempUsageDetailID`)
	);
	DROP TEMPORARY TABLE IF EXISTS tmp_TempUsageDetail2_;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempUsageDetail2_(
		TempUsageDetailID int,
		prefix varchar(50),
		INDEX IX_TempUsageDetailID2(`TempUsageDetailID`)
	);

	IF p_CLD != ''
	THEN 
		
		/* find prefix */
		SET @stm = CONCAT('
		INSERT INTO tmp_TempUsageDetail_
		SELECT
			TempUsageDetailID,
			c.code AS prefix
		FROM NeonCDRDev.' , p_tbltempusagedetail_name , ' ud 
		INNER JOIN NeonRMDev.tmp_inboundcodes_ c 
		ON ud.ProcessID = ' , p_processId , '
			AND ud.is_inbound = 1 
			AND ud.AccountID = ' , p_AccountID , '
			AND ("' , p_CLD , '" = "" OR cld = "' , p_CLD , '")
			AND ud.area_prefix = "Other"
			AND cli like  CONCAT(c.Code,"%");
		');
		
	ELSE
		
		/* find prefix */
		SET @stm = CONCAT('
		INSERT INTO tmp_TempUsageDetail_
		SELECT
			TempUsageDetailID,
			c.code AS prefix
		FROM NeonCDRDev.' , p_tbltempusagedetail_name , ' ud 
		INNER JOIN NeonRMDev.tmp_inboundcodes_ c 
		ON ud.ProcessID = ' , p_processId , '
			AND ud.is_inbound = 1 
			AND ud.AccountID = ' , p_AccountID , '
			AND ud.area_prefix = "Other"
			AND cld like  CONCAT(c.Code,"%");
		');
	
	END IF;

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET @stm = CONCAT('INSERT INTO tmp_TempUsageDetail2_
	SELECT tbl.TempUsageDetailID,MAX(tbl.prefix)  
	FROM tmp_TempUsageDetail_ tbl
	GROUP BY tbl.TempUsageDetailID;');

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET @stm = CONCAT('UPDATE NeonCDRDev.' , p_tbltempusagedetail_name , ' tbl2
	INNER JOIN tmp_TempUsageDetail2_ tbl
		ON tbl2.TempUsageDetailID = tbl.TempUsageDetailID
	SET area_prefix = prefix
	WHERE tbl2.processId = "' , p_processId , '"
	');

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;     

END//
DELIMITER ;

-- Dumping structure for procedure NeonBillingDev.prc_updateInboundRate
DROP PROCEDURE IF EXISTS `prc_updateInboundRate`;
DELIMITER //
CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_updateInboundRate`(
	IN `p_AccountID` INT,
	IN `p_processId` INT,
	IN `p_tbltempusagedetail_name` VARCHAR(200),
	IN `p_CLD` VARCHAR(500)
)
BEGIN
	
	SET @stm = CONCAT('UPDATE   NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud SET cost = 0,is_rerated=0  WHERE ProcessID = "',p_processId,'" AND AccountID = "',p_AccountID ,'" AND ("' , p_CLD , '" = "" OR cld = "' , p_CLD , '") AND is_inbound = 1 ') ;

	PREPARE stmt FROM @stm;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	SET @stm = CONCAT('
	UPDATE   NeonCDRDev.`' , p_tbltempusagedetail_name , '` ud 
	INNER JOIN NeonRMDev.tmp_inboundcodes_ cr ON cr.Code = ud.area_prefix
	SET cost = 
		CASE WHEN  billed_second >= Interval1
		THEN
			(Rate/60.0)*Interval1+CEILING((billed_second-Interval1)/IntervalN)*(Rate/60.0)*IntervalN+IFNULL(ConnectionFee,0)
		ElSE
			CASE WHEN  billed_second > 0
			THEN
				Rate+IFNULL(ConnectionFee,0)
			ELSE
				0
			END		    
		END
	,is_rerated=1
	,duration=billed_second
	,billed_duration =
		CASE WHEN  billed_second >= Interval1
		THEN
			Interval1+CEILING((billed_second-Interval1)/IntervalN)*IntervalN
		ElSE 
			CASE WHEN  billed_second > 0
			THEN
				Interval1
			ELSE
				0
			END
		END 
	WHERE ProcessID = "',p_processId,'"
	AND AccountID = "',p_AccountID ,'" 
	AND ("' , p_CLD , '" = "" OR cld = "' , p_CLD , '")
	AND is_inbound = 1') ;
	
	PREPARE stmt FROM @stm;
   EXECUTE stmt;
   DEALLOCATE PREPARE stmt;
	
END//
DELIMITER ;

-- Dumping structure for procedure NeonBillingDev.prc_updateSOAOffSet
DROP PROCEDURE IF EXISTS `prc_updateSOAOffSet`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_updateSOAOffSet`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT
)
BEGIN
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_AccountSOA;
	CREATE TEMPORARY TABLE tmp_AccountSOA (
		AccountID INT,
		Amount NUMERIC(18, 8),
		PaymentType VARCHAR(50),
		InvoiceType int
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_AccountSOABal;
	CREATE TEMPORARY TABLE tmp_AccountSOABal (
		AccountID INT,
		Amount NUMERIC(18, 8)
	);

     -- 1 Invoices
	INSERT into tmp_AccountSOA(AccountID,Amount,InvoiceType)
	SELECT
		tblInvoice.AccountID,
		tblInvoice.GrandTotal,
		tblInvoice.InvoiceType
	FROM tblInvoice
	WHERE tblInvoice.CompanyID = p_CompanyID
	AND ( (tblInvoice.InvoiceType = 2) OR ( tblInvoice.InvoiceType = 1 AND tblInvoice.InvoiceStatus NOT IN ( 'cancel' , 'draft' , 'awaiting') )  )
	AND (p_AccountID = 0 OR  tblInvoice.AccountID = p_AccountID);

     -- 2 Payments
	INSERT into tmp_AccountSOA(AccountID,Amount,PaymentType)
	SELECT
		tblPayment.AccountID,
		tblPayment.Amount,
		tblPayment.PaymentType
	FROM tblPayment
	WHERE tblPayment.CompanyID = p_CompanyID
	AND tblPayment.Status = 'Approved'
	AND tblPayment.Recall = 0
	AND (p_AccountID = 0 OR  tblPayment.AccountID = p_AccountID);
	
	INSERT INTO tmp_AccountSOABal
	SELECT AccountID,(SUM(IF(InvoiceType=1,Amount,0)) -  SUM(IF(PaymentType='Payment In',Amount,0))) - (SUM(IF(InvoiceType=2,Amount,0)) - SUM(IF(PaymentType='Payment Out',Amount,0))) as SOAOffSet 
	FROM tmp_AccountSOA 
	GROUP BY AccountID;
	
	INSERT INTO tmp_AccountSOABal
	SELECT DISTINCT tblAccount.AccountID ,0 FROM NeonRMDev.tblAccount
	LEFT JOIN tmp_AccountSOA ON tblAccount.AccountID = tmp_AccountSOA.AccountID
	WHERE tblAccount.CompanyID = p_CompanyID
	AND tmp_AccountSOA.AccountID IS NULL
	AND (p_AccountID = 0 OR  tblAccount.AccountID = p_AccountID);
	
	UPDATE NeonRMDev.tblAccountBalance
	INNER JOIN tmp_AccountSOABal 
		ON  tblAccountBalance.AccountID = tmp_AccountSOABal.AccountID
	SET SOAOffset=tmp_AccountSOABal.Amount;
	
	UPDATE NeonRMDev.tblAccountBalance SET tblAccountBalance.BalanceAmount = COALESCE(tblAccountBalance.SOAOffset,0) + COALESCE(tblAccountBalance.UnbilledAmount,0)  - COALESCE(tblAccountBalance.VendorUnbilledAmount,0);
	
	INSERT INTO NeonRMDev.tblAccountBalance (AccountID,BalanceAmount,UnbilledAmount,SOAOffset)
	SELECT tmp_AccountSOABal.AccountID,tmp_AccountSOABal.Amount,0,tmp_AccountSOABal.Amount
	FROM tmp_AccountSOABal 
	LEFT JOIN NeonRMDev.tblAccountBalance
		ON tblAccountBalance.AccountID = tmp_AccountSOABal.AccountID
	WHERE tblAccountBalance.AccountID IS NULL;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_getActiveGatewayAccount`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getActiveGatewayAccount`(
	IN `p_company_id` INT,
	IN `p_gatewayid` INT,
	IN `p_UserID` INT,
	IN `p_isAdmin` INT,
	IN `p_NameFormat` VARCHAR(50)
)
BEGIN

	DECLARE v_NameFormat_ VARCHAR(10);
	DECLARE v_RTR_ INT;
	DECLARE v_pointer_ INT ;
	DECLARE v_rowCount_ INT ;

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_ActiveAccount;
	CREATE TEMPORARY TABLE tmp_ActiveAccount (
		GatewayAccountID varchar(100),
		AccountID INT,
		AccountName varchar(100)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_AuthenticateRules_;
	CREATE TEMPORARY TABLE tmp_AuthenticateRules_ (
		RowNo INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AuthRule VARCHAR(50)
	);

	IF p_NameFormat = ''
	THEN

		INSERT INTO tmp_AuthenticateRules_  (AuthRule)
		SELECT  
			CASE WHEN Settings LIKE '%"NameFormat":"NAMENUB"%'
			THEN 'NAMENUB'
			ELSE
			CASE WHEN Settings LIKE '%"NameFormat":"NUBNAME"%'
			THEN 'NUBNAME'
			ELSE 
			CASE WHEN Settings LIKE '%"NameFormat":"NUB"%'
			THEN 'NUB'
			ELSE 
			CASE WHEN Settings LIKE '%"NameFormat":"IP"%'
			THEN 'IP'
			ELSE 
			CASE WHEN Settings LIKE '%"NameFormat":"CLI"%'
			THEN 'CLI'
			ELSE 
			CASE WHEN Settings LIKE '%"NameFormat":"NAME"%'
			THEN 'NAME'
			ELSE 'NAME' END END END END END END   AS  NameFormat 
		FROM NeonRMDev.tblCompanyGateway
		WHERE Settings LIKE '%NameFormat%' AND
		CompanyGatewayID = p_gatewayid
		LIMIT 1;

	END IF;

	IF p_NameFormat != ''
	THEN

		INSERT INTO tmp_AuthenticateRules_  (AuthRule)
		SELECT p_NameFormat;

	END IF;

	INSERT INTO tmp_AuthenticateRules_  (AuthRule)  
	SELECT DISTINCT CustomerAuthRule FROM NeonRMDev.tblAccountAuthenticate aa WHERE CustomerAuthRule IS NOT NULL
	UNION 
	SELECT DISTINCT VendorAuthRule FROM NeonRMDev.tblAccountAuthenticate aa WHERE VendorAuthRule IS NOT NULL;
	
	SET v_pointer_ = 1;
	SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_AuthenticateRules_);

	WHILE v_pointer_ <= v_rowCount_ 
	DO

		SET v_NameFormat_ = ( SELECT AuthRule FROM tmp_AuthenticateRules_  WHERE RowNo = v_pointer_ );

		IF  v_NameFormat_ = 'NAMENUB'
		THEN

			INSERT INTO tmp_ActiveAccount
			SELECT DISTINCT
				GatewayAccountID,
				a.AccountID,
				a.AccountName
			FROM NeonRMDev.tblAccount  a
			INNER JOIN tblGatewayAccount ga
				ON concat(a.AccountName , '-' , a.Number) = ga.AccountName
				AND a.Status = 1 
			WHERE GatewayAccountID IS NOT NULL
			AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
			AND a.CompanyId = p_company_id
			AND ga.CompanyGatewayID = p_gatewayid;

		END IF;

		IF v_NameFormat_ = 'NUBNAME'
		THEN

			INSERT INTO tmp_ActiveAccount
			SELECT DISTINCT
				GatewayAccountID,
				a.AccountID,
				a.AccountName
			FROM NeonRMDev.tblAccount  a
			INNER JOIN tblGatewayAccount ga
				ON concat(a.Number, '-' , a.AccountName) = ga.AccountName
				AND a.Status = 1 
			WHERE GatewayAccountID IS NOT NULL
			AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
			AND a.CompanyId = p_company_id
			AND ga.CompanyGatewayID = p_gatewayid;

		END IF;

		IF v_NameFormat_ = 'NUB'
		THEN

			INSERT INTO tmp_ActiveAccount
			SELECT DISTINCT
				GatewayAccountID,
				a.AccountID,
				a.AccountName
			FROM NeonRMDev.tblAccount  a
			INNER JOIN tblGatewayAccount ga
				ON a.Number = ga.AccountName
				AND a.Status = 1 
			WHERE GatewayAccountID IS NOT NULL
			AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
			AND a.CompanyId = p_company_id
			AND ga.CompanyGatewayID = p_gatewayid;

		END IF;

		IF v_NameFormat_ = 'IP'
		THEN

			INSERT INTO tmp_ActiveAccount
			SELECT DISTINCT
				GatewayAccountID,
				a.AccountID,
				a.AccountName
			FROM NeonRMDev.tblAccount  a
			INNER JOIN NeonRMDev.tblAccountAuthenticate aa 
				ON a.AccountID = aa.AccountID AND (aa.CustomerAuthRule = 'IP' OR aa.VendorAuthRule ='IP')
			INNER JOIN tblGatewayAccount ga
				ON   a.Status = 1 	
			WHERE GatewayAccountID IS NOT NULL
			AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
			AND a.CompanyId = p_company_id
			AND ga.CompanyGatewayID = p_gatewayid
			AND ( FIND_IN_SET(ga.AccountName,aa.CustomerAuthValue) != 0 OR FIND_IN_SET(ga.AccountName,aa.VendorAuthValue) != 0 );

		END IF;


		IF v_NameFormat_ = 'CLI'
		THEN
			/* INSERT INTO tmp_ActiveAccount
			SELECT DISTINCT
			GatewayAccountID,
			a.AccountID,
			a.AccountName
			FROM NeonRMDev.tblAccount  a
			INNER JOIN NeonRMDev.tblAccountAuthenticate aa ON 
			a.AccountID = aa.AccountID AND (aa.CustomerAuthRule = 'CLI' OR aa.VendorAuthRule ='CLI')
			INNER JOIN tblGatewayAccount ga
			ON   a.Status = 1 	
			WHERE GatewayAccountID IS NOT NULL
			AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
			AND a.CompanyId = p_company_id
			AND ga.CompanyGatewayID = p_gatewayid
			AND ( FIND_IN_SET(ga.AccountName,aa.CustomerAuthValue) != 0 OR FIND_IN_SET(ga.AccountName,aa.VendorAuthValue) != 0 );
			*/

			INSERT INTO tmp_ActiveAccount
			SELECT DISTINCT
				GatewayAccountID,
				a.AccountID,
				a.AccountName
			FROM NeonRMDev.tblAccount  a
			INNER JOIN NeonRMDev.tblCLIRateTable aa 
				ON a.AccountID = aa.AccountID 
			INNER JOIN tblGatewayAccount ga
				ON   a.Status = 1 	
			WHERE GatewayAccountID IS NOT NULL
			AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
			AND a.CompanyId = p_company_id
			AND ga.CompanyGatewayID = p_gatewayid
			AND ga.AccountName = aa.CLI;

		END IF;


		IF v_NameFormat_ = '' OR v_NameFormat_ IS NULL OR v_NameFormat_ = 'NAME'
		THEN

			INSERT INTO tmp_ActiveAccount
			SELECT DISTINCT
				GatewayAccountID,
				a.AccountID,
				a.AccountName
			FROM NeonRMDev.tblAccount  a
			LEFT JOIN NeonRMDev.tblAccountAuthenticate aa 
				ON a.AccountID = aa.AccountID AND (aa.CustomerAuthRule = 'Other' OR aa.VendorAuthRule ='Other')
			INNER JOIN tblGatewayAccount ga
				ON    a.Status = 1
			WHERE GatewayAccountID IS NOT NULL
			AND (p_isAdmin = 1 OR (p_isAdmin= 0 AND a.Owner = p_UserID))
			AND a.CompanyId = p_company_id
			AND ga.CompanyGatewayID = p_gatewayid
			AND ((aa.AccountAuthenticateID IS NOT NULL AND (aa.VendorAuthValue = ga.AccountName OR aa.CustomerAuthValue = ga.AccountName  )) OR (aa.AccountAuthenticateID IS NULL AND a.AccountName = ga.AccountName));

		END IF;

		SET v_pointer_ = v_pointer_ + 1;

	END WHILE;

	UPDATE tblGatewayAccount
	INNER JOIN tmp_ActiveAccount a
		ON a.GatewayAccountID = tblGatewayAccount.GatewayAccountID
		AND tblGatewayAccount.CompanyGatewayID = p_gatewayid
	SET tblGatewayAccount.AccountID = a.AccountID
	WHERE tblGatewayAccount.AccountID is null;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;