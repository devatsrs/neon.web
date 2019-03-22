USE `speakintelligentRM`;

ALTER TABLE `tblAccountService`
	ADD COLUMN `SubscriptionBillingCycleType` VARCHAR(50) NULL DEFAULT NULL AFTER `ServiceTitleShow`,
	ADD COLUMN `SubscriptionBillingCycleValue` VARCHAR(50) NULL DEFAULT NULL AFTER `SubscriptionBillingCycleType`;
	
INSERT INTO `tblCronJobCommand` (`CompanyID`, `GatewayID`, `Title`, `Command`, `Settings`, `Status`, `created_at`, `created_by`) VALUES (1, NULL, 'Account Balance Generator', 'accountbalancegenerator', '[[{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"}]]', 1, '2018-11-30 00:00:00', 'System');	

ALTER TABLE `tblAccountTariff`
	ADD COLUMN `AccountServiceID` INT(11) NULL DEFAULT '0' AFTER `ServiceID`;

ALTER TABLE `tblAccountBilling`
	ADD COLUMN `AccountServiceID` INT(11) NULL DEFAULT '0' AFTER `ServiceID`;	
	
ALTER TABLE `tblAccountAuthenticate`
	ADD COLUMN `AccountServiceID` INT(11) NULL DEFAULT '0' AFTER `ServiceID`;	
	
ALTER TABLE `tblAccountBilling`
	DROP INDEX `AccountID`,
	ADD UNIQUE INDEX `AccountID` (`ServiceID`, `AccountID`, `AccountServiceID`);	
	
ALTER TABLE `tblAccountBillingPeriod`
	ADD COLUMN `AccountServiceID` INT(11) NULL DEFAULT '0' AFTER `ServiceID`;	
	
ALTER TABLE `tblAccountNextBilling`
	ADD COLUMN `AccountServiceID` INT(11) NULL DEFAULT '0' AFTER `ServiceID`;		
	
ALTER TABLE `tblAccountNextBilling`
	DROP INDEX `AccountID`,
	ADD UNIQUE INDEX `AccountID` (`ServiceID`, `AccountID`, `AccountServiceID`);	
	
ALTER TABLE `tblAccountDiscountPlan`
	ADD COLUMN `AccountServiceID` INT(11) NULL DEFAULT '0' AFTER `ServiceID`;	
	
ALTER TABLE `tblAccountDiscountPlanHistory`
	ADD COLUMN `AccountServiceID` INT(11) NULL DEFAULT '0' AFTER `ServiceID`;	
		
ALTER TABLE `tblAccountDiscountPlan`
	DROP INDEX `AccountID`,
	ADD UNIQUE INDEX `AccountID` (`Type`, `AccountID`, `ServiceID`, `AccountServiceID`, `AccountSubscriptionID`, `AccountName`, `AccountCLI`, `SubscriptionDiscountPlanID`);

ALTER TABLE `tblCLIRateTable`
	ADD COLUMN `AccountServiceID` INT(11) NULL DEFAULT '0' AFTER `ServiceID`;	

ALTER TABLE `tblServiceBilling`
	ADD COLUMN `AccountServiceID` INT(11) NULL DEFAULT '0' AFTER `ServiceID`;

ALTER TABLE `tblAccountBalanceSubscriptionLog`
	ADD COLUMN `AccountServiceID` INT(11) NULL DEFAULT '0' AFTER `ServiceID`;

CREATE TABLE IF NOT EXISTS `tblServiceBilling` (
	`ServiceBillingID` INT(11) NOT NULL AUTO_INCREMENT,
	`AccountID` INT(11) NOT NULL DEFAULT '0',
	`ServiceID` INT(3) NOT NULL DEFAULT '0',
	`BillingType` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
	`BillingCycleType` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`BillingCycleValue` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`LastCycleDate` DATE NULL DEFAULT NULL,
	`NextCycleDate` DATE NULL DEFAULT NULL,
	`created_at` DATETIME NULL DEFAULT NULL,
	`updated_at` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`ServiceBillingID`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;

CREATE TABLE IF NOT EXISTS `tblAccountBalanceLog` (
	`AccountBalanceLogID` INT(11) NOT NULL AUTO_INCREMENT,
	`CompanyID` INT(11) NULL DEFAULT '0',
	`AccountID` INT(11) NULL DEFAULT '0',
	`BalanceAmount` DECIMAL(18,6) NULL DEFAULT NULL,
	`created_at` DATETIME NULL DEFAULT NULL,
	`updated_at` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`AccountBalanceLogID`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;

CREATE TABLE IF NOT EXISTS `tblAccountBalanceUsageLog` (
	`AccountBalanceUsageLogID` BIGINT(20) NOT NULL AUTO_INCREMENT,
	`AccountBalanceLogID` INT(11) NULL DEFAULT '0',
	`Type` INT(11) NULL DEFAULT '0',
	`Date` DATETIME NULL DEFAULT NULL,
	`UsageAmount` DECIMAL(18,6) NULL DEFAULT NULL,
	`TotalTax` DECIMAL(18,6) NULL DEFAULT NULL,
	`TotalAmount` DECIMAL(18,6) NULL DEFAULT NULL,
	`LastDetailID` BIGINT(20) NULL DEFAULT '0',
	`created_at` DATETIME NULL DEFAULT NULL,
	`updated_at` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`AccountBalanceUsageLogID`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;

CREATE TABLE IF NOT EXISTS `tblAccountBalanceSubscriptionLog` (
	`AccountBalanceSubscriptionLogID` BIGINT(20) NOT NULL AUTO_INCREMENT,
	`AccountBalanceLogID` INT(11) NULL DEFAULT '0',
	`ServiceID` INT(11) NULL DEFAULT '0',
	`IssueDate` DATETIME NULL DEFAULT NULL,
	`ProductType` INT(11) NULL DEFAULT '0',
	`ParentID` INT(11) NULL DEFAULT '0',
	`Description` TEXT NULL COLLATE 'utf8_unicode_ci',
	`Price` DECIMAL(18,6) NULL DEFAULT NULL,
	`Qty` FLOAT NULL DEFAULT NULL,
	`StartDate` DATETIME NULL DEFAULT NULL,
	`EndDate` DATETIME NULL DEFAULT NULL,
	`LineAmount` DECIMAL(18,6) NULL DEFAULT NULL,
	`TotalTax` DECIMAL(18,6) NULL DEFAULT NULL,
	`TotalAmount` DECIMAL(18,6) NULL DEFAULT NULL,
	`DiscountAmount` DECIMAL(18,6) NULL DEFAULT '0.000000',
	`DiscountType` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`DiscountLineAmount` DECIMAL(18,6) NULL DEFAULT '0.000000',
	`created_at` DATETIME NULL DEFAULT NULL,
	`updated_at` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`AccountBalanceSubscriptionLogID`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;

CREATE TABLE IF NOT EXISTS `tblAccountBalanceTaxRateLog` (
	`AccountBalanceTaxRateLogID` BIGINT(20) NOT NULL AUTO_INCREMENT,
	`ParentLogID` BIGINT(20) NULL DEFAULT '0',
	`Type` INT(11) NOT NULL DEFAULT '0',
	`TaxRateID` INT(11) NOT NULL,
	`TaxAmount` DECIMAL(18,6) NOT NULL,
	`Title` VARCHAR(500) NOT NULL COLLATE 'utf8_unicode_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`updated_at` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`AccountBalanceTaxRateLogID`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;

DROP PROCEDURE IF EXISTS `prc_updatePrepaidAccountBalance`;
DELIMITER //
CREATE PROCEDURE `prc_updatePrepaidAccountBalance`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT
)
BEGIN
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_AccountSOA;
	CREATE TEMPORARY TABLE tmp_AccountSOA (
		AccountID INT,
		Amount NUMERIC(18, 8),
		UsageType INT,
		SubscriptionType INT,
		TopUpType INT,
		PaymentType INT
	);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_AccountSOABal;
	CREATE TEMPORARY TABLE tmp_AccountSOABal (
		AccountID INT,
		Amount NUMERIC(18, 8)
	);

	/* select usage log */   
   INSERT into tmp_AccountSOA(AccountID,Amount,UsageType)
	SELECT
		AccountID,
		TotalAmount,
		'1' as UsageType
	FROM tblAccountBalanceUsageLog ul 
			INNER JOIN  tblAccountBalanceLog bl ON bl.AccountBalanceLogID=ul.AccountBalanceLogID
	WHERE bl.CompanyID = p_CompanyID	
	AND (p_AccountID = 0 OR  bl.AccountID = p_AccountID);
	
	/* select subscription/oneoff log */
   INSERT into tmp_AccountSOA(AccountID,Amount,SubscriptionType)
	SELECT
		AccountID,
		TotalAmount,
		'1' as SubscriptionType
	FROM tblAccountBalanceSubscriptionLog sl 
			INNER JOIN  tblAccountBalanceLog bl ON bl.AccountBalanceLogID=sl.AccountBalanceLogID
	WHERE bl.CompanyID = p_CompanyID	
	AND (p_AccountID = 0 OR  bl.AccountID = p_AccountID);
	
	
	/* select topup from invoce */
	INSERT into tmp_AccountSOA(AccountID,Amount,TopUpType)
	SELECT
		i.AccountID,
		id.LineTotal as Amount,
		'1' as TopUpType
	FROM speakintelligentBilling.tblInvoiceDetail id 
			INNER JOIN speakintelligentBilling.tblInvoice i ON id.InvoiceID=i.InvoiceID					
			INNER JOIN speakintelligentBilling.tblProduct p ON id.ProductID=p.ProductID AND p.Code='topup'
			INNER JOIN tblAccountBalanceLog bl ON bl.AccountID=i.AccountID
	WHERE bl.CompanyID = p_CompanyID 
		AND ( (i.InvoiceType = 2) OR ( i.InvoiceType = 1 AND i.InvoiceStatus NOT IN ( 'cancel' , 'draft' , 'awaiting') )  )
		AND (p_AccountID = 0 OR  bl.AccountID = p_AccountID);
	
	/**taxes on topup */
	INSERT INTO tmp_AccountSOA(AccountID,Amount,TopUpType)
	SELECT
		i.AccountID,
		itr.TaxAmount as Amount,
		'1' as TopUpType
	FROM  speakintelligentBilling.tblInvoiceTaxRate itr
			INNER JOIN speakintelligentBilling.tblInvoiceDetail id ON itr.InvoiceDetailID = id.InvoiceDetailID
			INNER JOIN speakintelligentBilling.tblInvoice i ON id.InvoiceID=i.InvoiceID					
			INNER JOIN speakintelligentBilling.tblProduct p ON id.ProductID=p.ProductID AND p.Code='topup'
			INNER JOIN tblAccountBalanceLog bl ON bl.AccountID=i.AccountID
	WHERE bl.CompanyID = p_CompanyID 
		AND ( (i.InvoiceType = 2) OR ( i.InvoiceType = 1 AND i.InvoiceStatus NOT IN ( 'cancel' , 'draft' , 'awaiting') )  )
		AND (p_AccountID = 0 OR  bl.AccountID = p_AccountID);


	/* All Payment which is not aginst any invoice */
	INSERT INTO tmp_AccountSOA(AccountID,Amount,PaymentType)
	SELECT p.AccountID,
			 p.Amount,	
			 '1' as PaymentType
	FROM speakintelligentBilling.tblPayment p
		  INNER JOIN tblAccountBalanceLog bl ON bl.AccountID=p.AccountID
	WHERE bl.CompanyID = p_CompanyID
		 AND (p_AccountID = 0 OR  bl.AccountID = p_AccountID)	
		 AND p.InvoiceID=0
		 AND p.PaymentType ='Payment In'
		 AND p.Status = 'Approved'
		 AND p.Recall = 0;
		 
	/* All Payment which is have peodic invoice or not item invoice */	 
	INSERT INTO tmp_AccountSOA(AccountID,Amount,PaymentType)
	SELECT p.AccountID,
			 p.Amount,	
			 '1' as PaymentType
	FROM speakintelligentBilling.tblPayment p
		  INNER JOIN tblAccountBalanceLog bl ON bl.AccountID=p.AccountID
		  INNER JOIN speakintelligentBilling.tblInvoice i ON p.InvoiceID=i.InvoiceID
	WHERE bl.CompanyID = p_CompanyID
		 AND (p_AccountID = 0 OR  bl.AccountID = p_AccountID)
		 AND i.ItemInvoice is null
		 AND p.PaymentType ='Payment In'
		 AND p.Status = 'Approved'
		 AND p.Recall = 0;


	/* account balance = (payment + topup) - (usage + subscription )	*/
	INSERT INTO tmp_AccountSOABal
	SELECT AccountID,(SUM(IF(PaymentType=1,Amount,0)) +  SUM(IF(TopUpType=1,Amount,0))) - (SUM(IF(UsageType=1,Amount,0)) + SUM(IF(SubscriptionType=1,Amount,0))) as Amount
	FROM tmp_AccountSOA 
	GROUP BY AccountID;	
	
	UPDATE tblAccountBalanceLog
	INNER JOIN tmp_AccountSOABal 
		ON  tblAccountBalanceLog.AccountID = tmp_AccountSOABal.AccountID
	SET BalanceAmount=tmp_AccountSOABal.Amount;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_getPrepaidUnbilledReport`;
DELIMITER //
CREATE PROCEDURE `prc_getPrepaidUnbilledReport`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_StartDate` DATETIME,
	IN `p_EndDate` DATETIME,
	IN `p_Detail` INT
)
BEGIN
	
	DECLARE v_Round_ INT;
	DECLARE v_AccountBalanceLogID_ INT;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	SELECT AccountBalanceLogID INTO v_AccountBalanceLogID_ FROM tblAccountBalanceLog WHERE AccountID=p_AccountID;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_Account;
	CREATE TEMPORARY TABLE tmp_Account (
		AccountID INT,	
		Type VARCHAR(25),
		Description VARCHAR(255),
		Period VARCHAR(255),
		Amount NUMERIC(18, 8),
		IssueDate DATETIME
	);
	
	
	DROP TEMPORARY TABLE IF EXISTS tmp_TopUp;
	CREATE TEMPORARY TABLE tmp_TopUp(
		AccountID INT,
		InvoiceID INT,
		Description VARCHAR(255),
		InvoiceAmount NUMERIC(18, 8),
		TotalTax NUMERIC(18, 8),
		TotalAmount NUMERIC(18, 8),
		IssueDate DATETIME
		
	);
	
	INSERT INTO tmp_TopUp(AccountID,InvoiceID,Description,InvoiceAmount,TotalTax,TotalAmount,IssueDate)
	SELECT
		i.AccountID,
		i.InvoiceID,
		id.Description,
		id.LineTotal as InvoiceAmount,
		sum(r.TaxAmount) as TotalTax,
		(id.LineTotal + sum(r.TaxAmount)) as TotalAmount,
		i.IssueDate
	FROM speakintelligentBilling.tblInvoiceDetail id 
			INNER JOIN speakintelligentBilling.tblInvoice i ON id.InvoiceID=i.InvoiceID					
			INNER JOIN speakintelligentBilling.tblProduct p ON id.ProductID=p.ProductID AND p.Code='topup'
			LEFT JOIN speakintelligentBilling.tblInvoiceTaxRate r on id.InvoiceDetailID = r.InvoiceDetailID
	WHERE i.AccountID = p_AccountID
		AND ( (i.InvoiceType = 2) OR ( i.InvoiceType = 1 AND i.InvoiceStatus NOT IN ( 'cancel' , 'draft' , 'awaiting') )  )
		AND i.IssueDate BETWEEN p_StartDate AND p_EndDate
		GROUP BY id.InvoiceDetailID
		;
				
	/* select usage with account */
	INSERT INTO tmp_Account(AccountID,Type,Description,Period,Amount,IssueDate)
	SELECT p_AccountID AS AccountID,
			'Usage' as `Type`,
			'Usage' as Description,
			CONCAT(Date,' - ',DATE_FORMAT(Date ,"%Y-%m-%d 23:59:59")) AS Period,
			TotalAmount as Amount,
			Date as IssueDate
	FROM tblAccountBalanceUsageLog
	WHERE AccountBalanceLogID = v_AccountBalanceLogID_ 
			AND Date BETWEEN p_StartDate AND p_EndDate;		
	
	/* select subscription with account */
	INSERT INTO tmp_Account(AccountID,Type,Description,Period,Amount,IssueDate)
	SELECT p_AccountID AS AccountID,
			'Subscription' as `Type`,
			Description,
			CONCAT(StartDate,' - ',EndDate) AS Period,
			TotalAmount as Amount,
			IssueDate
	FROM tblAccountBalanceSubscriptionLog 
	WHERE AccountBalanceLogID = v_AccountBalanceLogID_ 
			AND IssueDate BETWEEN p_StartDate AND p_EndDate
			AND ProductType=3;
	
	/* select oneoffcharge with account */
	INSERT INTO tmp_Account(AccountID,Type,Description,Period,Amount,IssueDate)
	SELECT p_AccountID AS AccountID,
			'Oneofcharge' as `Type`,
			Description,
			CONCAT(StartDate,' - ',EndDate) AS Period,
			TotalAmount as Amount,
			IssueDate
	FROM tblAccountBalanceSubscriptionLog 
	WHERE AccountBalanceLogID = v_AccountBalanceLogID_ 
			AND IssueDate BETWEEN p_StartDate AND p_EndDate
			AND ProductType=4;
	INSERT INTO tmp_Account(AccountID,Type,Description,Period,Amount,IssueDate)
	SELECT p_AccountID AS AccountID,
			'TopUp' as `Type`,
			Description,
			CONCAT(IssueDate,' - ',IssueDate) AS Period,
			TotalAmount as Amount,
			IssueDate		
	FROM	tmp_TopUp;
	
	SELECT * FROM tmp_Account ORDER BY IssueDate Desc;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_GetAccounts`;
DELIMITER //
CREATE PROCEDURE `prc_GetAccounts`(
	IN `p_CompanyID` int,
	IN `p_userID` int ,
	IN `p_IsVendor` int ,
	IN `p_isCustomer` int ,
	IN `p_isReseller` INT,
	IN `p_ResellerID` INT,
	IN `p_activeStatus` int,
	IN `p_VerificationStatus` int,
	IN `p_AccountNo` VARCHAR(100),
	IN `p_ContactName` VARCHAR(50),
	IN `p_AccountName` VARCHAR(50),
	IN `p_tags` VARCHAR(50),
	IN `p_IPCLI` VARCHAR(50),
	IN `p_low_balance` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_isExport` INT
)
BEGIN
	DECLARE v_OffSet_ int;
	DECLARE v_Round_ int;
	DECLARE v_raccountids TEXT;
	DECLARE v_resellercompanyid int;
	SET v_raccountids = '';
	SET v_resellercompanyid = 0;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;
	
	
	DROP TEMPORARY TABLE IF EXISTS tmp_AccountBalance;
	CREATE TEMPORARY TABLE tmp_AccountBalance (
		AccountID INT,	
		BillingType INT,
		SOAOffset DECIMAL(18,6),
		UnbilledAmount DECIMAL(18,6),
		VendorUnbilledAmount DECIMAL(18,6),
		PermanentCredit DECIMAL(18,6),
		BalanceAmount DECIMAL(18,6),
		BalanceThreshold VARCHAR(50),
		BalanceThreshold1 VARCHAR(50),
		BalanceWarning INT
	);
	
	
	INSERT INTO tmp_AccountBalance(AccountID,BillingType,SOAOffset,UnbilledAmount,VendorUnbilledAmount,PermanentCredit,BalanceAmount,BalanceThreshold,BalanceThreshold1,BalanceWarning)
	SELECT a.AccountID,
			IFNULL(ab.BillingType,2) AS BillingType,
			IFNULL(abc.SOAOffset,0) AS SOAOffset,
			IFNULL(abc.UnbilledAmount,0) AS UnbilledAmount,
			IFNULL(abc.VendorUnbilledAmount,0) AS VendorUnbilledAmount,
			IFNULL(abc.PermanentCredit,0) AS PermanentCredit,
			IFNULL(abc.BalanceAmount,0) AS BalanceAmount,
			abc.BalanceThreshold,
			( CASE WHEN abc.BalanceThreshold LIKE '%p' 
			THEN REPLACE(abc.BalanceThreshold, 'p', '')/ 100 * abc.PermanentCredit 
								ELSE abc.BalanceThreshold END
						) as BalanceThreshold1,
			0 as BalanceWarning
	FROM tblAccount a 
		LEFT JOIN tblAccountBilling ab ON a.AccountID=ab.AccountID and ab.ServiceID=0
		LEFT JOIN tblAccountBalance abc ON a.AccountID = abc.AccountID 
		;		

	UPDATE tmp_AccountBalance ta
	INNER JOIN tblAccountBalanceLog bl
		 	ON ta.AccountID=bl.AccountID
	SET ta.BalanceAmount = bl.BalanceAmount,ta.UnbilledAmount = 0,ta.VendorUnbilledAmount=0,ta.SOAOffset=bl.BalanceAmount
	WHERE ta.BillingType=1;

	
	UPDATE tmp_AccountBalance set BalanceWarning =
	IF (BalanceThreshold1 >  BalanceAmount AND BalanceThreshold <>'0' AND BalanceThreshold <>'' ,1,0);
	

	IF p_ResellerID > 0
	THEN
		DROP TEMPORARY TABLE IF EXISTS tmp_reselleraccounts_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_reselleraccounts_(
			AccountID int
		);
	
		INSERT INTO tmp_reselleraccounts_
		SELECT AccountID FROM tblAccountDetails WHERE ResellerOwner=p_ResellerID
		UNION
		SELECT AccountID FROM tblReseller WHERE ResellerID=p_ResellerID;
		
		SELECT ChildCompanyID INTO v_resellercompanyid FROM tblReseller WHERE ResellerID=p_ResellerID;		
	
		SELECT IFNULL(GROUP_CONCAT(AccountID),'') INTO v_raccountids FROM tmp_reselleraccounts_;
		
	END IF;

	IF p_isExport = 0
	THEN

		SELECT
			tblAccount.AccountID,
			tblAccount.Number,
			tblAccount.AccountName,
			CONCAT(tblAccount.FirstName,' ',tblAccount.LastName) as Ownername,
			tblAccount.Phone,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(abc.SOAOffset,0),v_Round_)) as OutStandingAmount,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(abc.UnbilledAmount,0),v_Round_) - ROUND(COALESCE(abc.VendorUnbilledAmount,0),v_Round_)) as UnbilledAmount,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(abc.PermanentCredit,0),v_Round_)) as PermanentCredit,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(abc.BalanceAmount,0),v_Round_)) as AccountExposure,
			tblAccount.Email,
			tblAccount.IsCustomer,
			tblAccount.IsVendor,
			tblAccount.VerificationStatus,
			tblAccount.Address1,
			tblAccount.Address2,
			tblAccount.Address3,
			tblAccount.City,
			tblAccount.Country,
			tblAccount.PostCode,
			tblAccount.Picture,
		   abc.BalanceWarning, 
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(abc.UnbilledAmount,0),v_Round_)) as CUA,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(abc.VendorUnbilledAmount,0),v_Round_)) as VUA,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(abc.BalanceAmount,0),v_Round_)) as AE,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,IF(ROUND(COALESCE(abc.PermanentCredit,0),v_Round_) - ROUND(COALESCE(abc.BalanceAmount,0),v_Round_)<0,0,ROUND(COALESCE(abc.PermanentCredit,0),v_Round_) - ROUND(COALESCE(abc.BalanceAmount,0),v_Round_))) as ACL,
			abc.BalanceThreshold,
			tblAccount.Blocked
		FROM tblAccount
		LEFT JOIN tmp_AccountBalance abc
			ON abc.AccountID = tblAccount.AccountID
		LEFT JOIN tblUser
			ON tblAccount.Owner = tblUser.UserID
		LEFT JOIN tblContact
			ON tblContact.Owner=tblAccount.AccountID
		LEFT JOIN tblAccountAuthenticate
			ON tblAccountAuthenticate.AccountID = tblAccount.AccountID
		LEFT JOIN tblCLIRateTable
			ON tblCLIRateTable.AccountID = tblAccount.AccountID
		WHERE  
			 tblAccount.AccountType = 1
			AND tblAccount.Status = p_activeStatus
			AND tblAccount.VerificationStatus = p_VerificationStatus
			AND (p_userID = 0 OR tblAccount.Owner = p_userID)
			AND ((p_IsVendor = 0 OR tblAccount.IsVendor = 1))
			AND ((p_isCustomer = 0 OR tblAccount.IsCustomer = 1))
			AND ((p_isReseller = 0 OR tblAccount.IsReseller = 1))
			AND (p_ResellerID = 0 OR tblAccount.CompanyID = v_resellercompanyid)
			AND ((p_AccountNo = '' OR tblAccount.Number LIKE p_AccountNo))
			AND ((p_AccountName = '' OR tblAccount.AccountName LIKE Concat('%',p_AccountName,'%')))
			AND ((p_IPCLI = '' OR tblCLIRateTable.CLI LIKE CONCAT('%',p_IPCLI,'%') OR CONCAT(IFNULL(tblAccountAuthenticate.CustomerAuthValue,''),',',IFNULL(tblAccountAuthenticate.VendorAuthValue,'')) LIKE CONCAT('%',p_IPCLI,'%')))
			AND ((p_tags = '' OR tblAccount.tags LIKE Concat(p_tags,'%')))
			AND ((p_ContactName = '' OR (CONCAT(IFNULL(tblContact.FirstName,'') ,' ', IFNULL(tblContact.LastName,''))) LIKE CONCAT('%',p_ContactName,'%')))	 
			AND (p_low_balance = 0 OR ( p_low_balance = 1 and abc.BalanceWarning=1)) 
		-- GROUP BY tblAccount.AccountID
		ORDER BY
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN tblAccount.AccountName
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN tblAccount.AccountName
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NumberDESC') THEN tblAccount.Number
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'NumberASC') THEN tblAccount.Number
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OwnernameDESC') THEN tblUser.FirstName
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OwnernameASC') THEN tblUser.FirstName
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PhoneDESC') THEN tblAccount.Phone
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PhoneASC') THEN tblAccount.Phone
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutStandingAmountDESC') THEN abc.SOAOffset
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutStandingAmountASC') THEN abc.SOAOffset
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PermanentCreditDESC') THEN abc.PermanentCredit
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PermanentCreditASC') THEN abc.PermanentCredit
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'UnbilledAmountDESC') THEN (ROUND(COALESCE(abc.UnbilledAmount,0),v_Round_) - ROUND(COALESCE(abc.VendorUnbilledAmount,0),v_Round_))
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'UnbilledAmountASC') THEN (ROUND(COALESCE(abc.UnbilledAmount,0),v_Round_) - ROUND(COALESCE(abc.VendorUnbilledAmount,0),v_Round_))
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EmailDESC') THEN tblAccount.Email
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EmailASC') THEN tblAccount.Email
			END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT
			COUNT(DISTINCT tblAccount.AccountID) AS totalcount
		FROM tblAccount
		LEFT JOIN tmp_AccountBalance abc
			ON abc.AccountID = tblAccount.AccountID
		LEFT JOIN tblUser
			ON tblAccount.Owner = tblUser.UserID
		LEFT JOIN tblContact
			ON tblContact.Owner=tblAccount.AccountID
		LEFT JOIN tblAccountAuthenticate
			ON tblAccountAuthenticate.AccountID = tblAccount.AccountID
		LEFT JOIN tblCLIRateTable
			ON tblCLIRateTable.AccountID = tblAccount.AccountID
		WHERE 
			 tblAccount.AccountType = 1
			AND tblAccount.Status = p_activeStatus
			AND tblAccount.VerificationStatus = p_VerificationStatus
			AND (p_userID = 0 OR tblAccount.Owner = p_userID)
			AND ((p_IsVendor = 0 OR tblAccount.IsVendor = 1))
			AND ((p_isCustomer = 0 OR tblAccount.IsCustomer = 1))
			AND ((p_isReseller = 0 OR tblAccount.IsReseller = 1))
			AND (p_ResellerID = 0 OR tblAccount.CompanyID = v_resellercompanyid)
			AND ((p_AccountNo = '' OR tblAccount.Number LIKE p_AccountNo))
			AND ((p_AccountName = '' OR tblAccount.AccountName LIKE Concat('%',p_AccountName,'%')))
			AND ((p_IPCLI = '' OR tblCLIRateTable.CLI LIKE CONCAT('%',p_IPCLI,'%') OR CONCAT(IFNULL(tblAccountAuthenticate.CustomerAuthValue,''),',',IFNULL(tblAccountAuthenticate.VendorAuthValue,'')) LIKE CONCAT('%',p_IPCLI,'%')))
			AND ((p_tags = '' OR tblAccount.tags LIKE Concat(p_tags,'%')))
			AND ((p_ContactName = '' OR (CONCAT(IFNULL(tblContact.FirstName,'') ,' ', IFNULL(tblContact.LastName,''))) LIKE CONCAT('%',p_ContactName,'%')))
			AND (p_low_balance = 0 OR ( p_low_balance = 1 and abc.BalanceWarning=1)) ;

	END IF;
	IF p_isExport = 1
	THEN
		SELECT
			tblAccount.Number as NO,
			tblAccount.AccountName,
			CONCAT(tblAccount.FirstName,' ',tblAccount.LastName) as Name,
			tblAccount.Phone,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(abc.SOAOffset,0),v_Round_)) as 'OutStanding',
			tblAccount.Email,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(abc.UnbilledAmount,0),v_Round_)  - ROUND(COALESCE(abc.VendorUnbilledAmount,0),v_Round_)) as 'Unbilled Amount',
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(abc.PermanentCredit,0),v_Round_)) as 'Credit Limit',
			CONCAT(tblUser.FirstName,' ',tblUser.LastName) as 'Account Owner',
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(abc.BalanceAmount,0),v_Round_)) as AccountExposure
		FROM tblAccount
		LEFT JOIN tmp_AccountBalance abc
			ON abc.AccountID = tblAccount.AccountID
		LEFT JOIN tblUser
			ON tblAccount.Owner = tblUser.UserID
		LEFT JOIN tblContact
			ON tblContact.Owner=tblAccount.AccountID
		LEFT JOIN tblAccountAuthenticate
			ON tblAccountAuthenticate.AccountID = tblAccount.AccountID
		LEFT JOIN tblCLIRateTable
			ON tblCLIRateTable.AccountID = tblAccount.AccountID
		WHERE   tblAccount.CompanyID = p_CompanyID
			AND tblAccount.AccountType = 1
			AND tblAccount.Status = p_activeStatus
			AND tblAccount.VerificationStatus = p_VerificationStatus
			AND (p_userID = 0 OR tblAccount.Owner = p_userID)
			AND ((p_IsVendor = 0 OR tblAccount.IsVendor = 1))
			AND ((p_isCustomer = 0 OR tblAccount.IsCustomer = 1))
			AND ((p_isReseller = 0 OR tblAccount.IsReseller = 1))
			AND (p_ResellerID = 0 OR tblAccount.CompanyID = v_resellercompanyid)
			AND ((p_AccountNo = '' OR tblAccount.Number LIKE p_AccountNo))
			AND ((p_AccountName = '' OR tblAccount.AccountName LIKE Concat('%',p_AccountName,'%')))
			AND ((p_IPCLI = '' OR tblCLIRateTable.CLI LIKE CONCAT('%',p_IPCLI,'%') OR CONCAT(IFNULL(tblAccountAuthenticate.CustomerAuthValue,''),',',IFNULL(tblAccountAuthenticate.VendorAuthValue,'')) LIKE CONCAT('%',p_IPCLI,'%')))
			AND ((p_tags = '' OR tblAccount.tags LIKE Concat(p_tags,'%')))
			AND ((p_ContactName = '' OR (CONCAT(IFNULL(tblContact.FirstName,'') ,' ', IFNULL(tblContact.LastName,''))) LIKE CONCAT('%',p_ContactName,'%')))		
			AND (p_low_balance = 0 OR ( p_low_balance = 1 and abc.BalanceWarning=1)) 
		-- GROUP BY tblAccount.AccountID
		;
	END IF;
	IF p_isExport = 2
	THEN
		SELECT
			tblAccount.AccountID,
			tblAccount.AccountName
		FROM tblAccount
		LEFT JOIN tmp_AccountBalance abc
			ON abc.AccountID = tblAccount.AccountID
		LEFT JOIN tblUser
			ON tblAccount.Owner = tblUser.UserID
		LEFT JOIN tblContact
			ON tblContact.Owner=tblAccount.AccountID
		LEFT JOIN tblAccountAuthenticate
			ON tblAccountAuthenticate.AccountID = tblAccount.AccountID
		LEFT JOIN tblCLIRateTable
			ON tblCLIRateTable.AccountID = tblAccount.AccountID
		WHERE   tblAccount.CompanyID = p_CompanyID
			AND tblAccount.AccountType = 1
			AND tblAccount.Status = p_activeStatus
			AND tblAccount.VerificationStatus = p_VerificationStatus
			AND (p_userID = 0 OR tblAccount.Owner = p_userID)
			AND ((p_IsVendor = 0 OR tblAccount.IsVendor = 1))
			AND ((p_isCustomer = 0 OR tblAccount.IsCustomer = 1))
			AND ((p_isReseller = 0 OR tblAccount.IsReseller = 1))
			AND (p_ResellerID = 0 OR tblAccount.CompanyID = v_resellercompanyid)
			AND ((p_AccountNo = '' OR tblAccount.Number LIKE p_AccountNo))
			AND ((p_AccountName = '' OR tblAccount.AccountName LIKE Concat('%',p_AccountName,'%')))
			AND ((p_IPCLI = '' OR tblCLIRateTable.CLI LIKE CONCAT('%',p_IPCLI,'%') OR CONCAT(IFNULL(tblAccountAuthenticate.CustomerAuthValue,''),',',IFNULL(tblAccountAuthenticate.VendorAuthValue,'')) LIKE CONCAT('%',p_IPCLI,'%')))
			AND ((p_tags = '' OR tblAccount.tags LIKE Concat(p_tags,'%')))
			AND ((p_ContactName = '' OR (CONCAT(IFNULL(tblContact.FirstName,'') ,' ', IFNULL(tblContact.LastName,''))) LIKE CONCAT('%',p_ContactName,'%')))
			AND (p_low_balance = 0 OR ( p_low_balance = 1 and abc.BalanceWarning=1))
		-- GROUP BY tblAccount.AccountID
		;
	END IF;
	

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_LowBalanceReminder`;
DELIMITER //
CREATE PROCEDURE `prc_LowBalanceReminder`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_BillingClassID` INT
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	CALL speakintelligentBilling.prc_updateSOAOffSet(p_CompanyID,p_AccountID);
	CALL prc_updatePrepaidAccountBalance(p_CompanyID,p_AccountID);
	
	DROP TEMPORARY TABLE IF EXISTS tmp_AccountBalance;
	CREATE TEMPORARY TABLE tmp_AccountBalance (
		AccountID INT,	
		BillingType INT,
		PermanentCredit DECIMAL(18,6),
		BalanceAmount DECIMAL(18,6),
		BalanceThreshold VARCHAR(50)
	);
	
	INSERT INTO tmp_AccountBalance(AccountID,BillingType,PermanentCredit,BalanceAmount,BalanceThreshold)
	SELECT a.AccountID,
			 abg.BillingType,
			 ab.PermanentCredit,
			 ab.BalanceAmount,
			 ab.BalanceThreshold
	FROM tblAccountBalance ab 
		INNER JOIN tblAccount a 
			ON a.AccountID = ab.AccountID
		INNER JOIN tblAccountBilling abg 
			ON abg.AccountID  = a.AccountID  AND abg.ServiceID = 0 
		INNER JOIN tblBillingClass b
			ON b.BillingClassID = abg.BillingClassID
	WHERE a.CompanyId = p_CompanyID
		AND (p_AccountID = 0 OR  a.AccountID = p_AccountID)
		AND (p_BillingClassID = 0 OR  b.BillingClassID = p_BillingClassID)
		AND ab.PermanentCredit IS NOT NULL
		AND ab.BalanceThreshold IS NOT NULL
		AND a.`Status` = 1;
	
	UPDATE tmp_AccountBalance ta
	INNER JOIN tblAccountBalanceLog bl
		 	ON ta.AccountID=bl.AccountID
	SET ta.BalanceAmount = bl.BalanceAmount
	WHERE ta.BillingType=1;
		
	SELECT
		DISTINCT
		IF (( CASE WHEN BalanceThreshold LIKE '%p' 
			THEN REPLACE(BalanceThreshold, 'p', '')/ 100 * PermanentCredit 
								ELSE BalanceThreshold END
						) >  BalanceAmount AND BalanceThreshold <> 0 ,1,0) as BalanceWarning,							 
		AccountID
		FROM tmp_AccountBalance;	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_GetBlockUnblockAccount`;
DELIMITER //
CREATE PROCEDURE `prc_GetBlockUnblockAccount`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	DROP TEMPORARY TABLE IF EXISTS tmp_AccountBalance;
	CREATE TEMPORARY TABLE tmp_AccountBalance (
		AccountID INT,	
		BillingType INT,
		PermanentCredit DECIMAL(18,6),
		BalanceAmount DECIMAL(18,6)
	);
	
	INSERT INTO tmp_AccountBalance(AccountID,BillingType,PermanentCredit,BalanceAmount)
	SELECT ab.AccountID,b.BillingType,ab.PermanentCredit,ab.BalanceAmount
	FROM tblAccountBalance ab
		INNER JOIN tblAccountBilling b ON ab.AccountID=b.AccountID AND b.ServiceID=0;
		
	UPDATE tmp_AccountBalance ta
	INNER JOIN tblAccountBalanceLog bl
		 	ON ta.AccountID=bl.AccountID
	SET ta.BalanceAmount = bl.BalanceAmount
	WHERE ta.BillingType=1;	
	
	SELECT
		DISTINCT
		a.AccountID,
		a.Number,
		a.AccountName,
		(COALESCE(ab.BalanceAmount,0) - COALESCE(ab.PermanentCredit,0)) as Balance,
		IF((COALESCE(ab.BalanceAmount,0) - COALESCE(ab.PermanentCredit,0)) > 0 AND a.`Status` = 1 ,1,0) as BlockStatus,
		a.`Status`,
		a.BillingEmail,
		a.Blocked,
		IFNULL(a.IsReseller,0) AS IsReseller
	FROM tmp_AccountBalance ab 
	INNER JOIN tblAccount a 
		ON a.AccountID = ab.AccountID
	INNER JOIN speakintelligentCDR.tblUsageHeader ga
		ON ga.AccountID = a.AccountID
	WHERE a.CompanyId = p_CompanyID
	AND a.AccountType = 1
	AND ( p_CompanyGatewayID = 0 OR ga.CompanyGatewayID = p_CompanyGatewayID)
	ORDER BY BlockStatus,a.AccountID;	
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_AddAccountIPCLI`;
DELIMITER //
CREATE PROCEDURE `prc_AddAccountIPCLI`(
	IN `p_CompanyID` INT,
	IN `p_AccountID` INT,
	IN `p_CustomerVendorCheck` INT,
	IN `p_IPCLIString` LONGTEXT,
	IN `p_IPCLICheck` LONGTEXT,
	IN `p_ServiceID` INT,
	IN `p_AccountServiceID` INT
)
BEGIN

	DECLARE i int;
	DECLARE v_COUNTER int;
	DECLARE v_IPCLI LONGTEXT;
	DECLARE v_IPCLICheck VARCHAR(10);
	DECLARE v_Check int;
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	DROP TEMPORARY TABLE IF EXISTS `AccountIPCLI`; 
		CREATE TEMPORARY TABLE `AccountIPCLI` (
		  `AccountName` varchar(45) NOT NULL,
		  `IPCLI` LONGTEXT NOT NULL
		);

	DROP TEMPORARY TABLE IF EXISTS `AccountIPCLITable1`; 
		CREATE TEMPORARY TABLE `AccountIPCLITable1` (
		  `splitted_column` varchar(45) NOT NULL
		);
		
	DROP TEMPORARY TABLE IF EXISTS `AccountIPCLITable2`; 
		CREATE TEMPORARY TABLE `AccountIPCLITable2` (
		  `splitted_column` varchar(45) NOT NULL
		);
	
		
	INSERT INTO AccountIPCLI
	SELECT acc.AccountName,
	CONCAT(
	IFNULL(((CASE WHEN CustomerAuthRule = p_IPCLICheck THEN accauth.CustomerAuthValue ELSE '' END)),''),',',
	IFNULL(((CASE WHEN VendorAuthRule = p_IPCLICheck THEN accauth.VendorAuthValue ELSE '' END)),'')) as Authvalue
	FROM tblAccountAuthenticate accauth
	INNER JOIN tblAccount acc ON acc.AccountID = accauth.AccountID
	AND accauth.CompanyID = p_CompanyID
	AND ((CustomerAuthRule = p_IPCLICheck) OR (VendorAuthRule = p_IPCLICheck))
	WHERE 
		((SELECT fnFIND_IN_SET(CONCAT(IFNULL(accauth.CustomerAuthValue,''),',',IFNULL(accauth.VendorAuthValue,'')),p_IPCLIString) WHERE accauth.AccountID != p_AccountID) > 0)
		OR
		CASE(p_CustomerVendorCheck)
			WHEN 1 THEN
				(SELECT fnFIND_IN_SET(IFNULL(accauth.CustomerAuthValue,''),p_IPCLIString) WHERE accauth.AccountID = p_AccountID) > 0
			WHEN 2 THEN
				(SELECT fnFIND_IN_SET(IFNULL(accauth.VendorAuthValue,''),p_IPCLIString) WHERE accauth.AccountID = p_AccountID) > 0
		END
	;
	
	SELECT COUNT(AccountName) INTO v_COUNTER FROM AccountIPCLI;
	
	
	IF v_COUNTER > 0 THEN
		SELECT *  FROM AccountIPCLI;
		
		
		SET i = 1;
		REPEAT
			INSERT INTO AccountIPCLITable1
			SELECT FnStringSplit(p_IPCLIString, ',', i) WHERE FnStringSplit(p_IPCLIString, ',', i) IS NOT NULL LIMIT 1;
			SET i = i + 1;
			UNTIL ROW_COUNT() = 0
		END REPEAT;
		
		
		INSERT INTO AccountIPCLITable2
		SELECT AccountIPCLITable1.splitted_column FROM AccountIPCLI,AccountIPCLITable1
		WHERE FIND_IN_SET(AccountIPCLITable1.splitted_column,AccountIPCLI.IPCLI)>0
		GROUP BY AccountIPCLITable1.splitted_column;
		
		
		DELETE t1 FROM AccountIPCLITable1 t1
		INNER JOIN AccountIPCLITable2 t2 ON t1.splitted_column = t2.splitted_column
		WHERE t1.splitted_column=t2.splitted_column;
		
		SELECT GROUP_CONCAT(t.splitted_column separator ',') INTO p_IPCLIString FROM AccountIPCLITable1 t;
		
		
		DELETE t1,t2 FROM AccountIPCLITable1 t1,AccountIPCLITable2 t2;
	END IF;
	
	
	SELECT 
	accauth.AccountAuthenticateID, 
	(CASE WHEN p_CustomerVendorCheck = 1 THEN accauth.CustomerAuthValue ELSE accauth.VendorAuthValue END) as AuthValue,
	(CASE WHEN p_CustomerVendorCheck = 1 THEN accauth.CustomerAuthRule ELSE accauth.VendorAuthRule END) as AuthRule INTO v_Check,v_IPCLI,v_IPCLICheck
	FROM tblAccountAuthenticate accauth 
	WHERE accauth.CompanyID =  p_CompanyID
	AND accauth.ServiceID = p_ServiceID
	AND accauth.AccountServiceID = p_AccountServiceID
	AND accauth.AccountID = p_AccountID;
			
	IF v_Check > 0 && p_IPCLIString IS NOT NULL && p_IPCLIString!='' THEN
		IF v_IPCLICheck != p_IPCLICheck THEN
		
			IF p_CustomerVendorCheck = 1 THEN
				UPDATE tblAccountAuthenticate accauth SET accauth.CustomerAuthValue = ''
				WHERE accauth.CompanyID =  p_CompanyID
				AND accauth.ServiceID = p_ServiceID
				AND accauth.AccountServiceID = p_AccountServiceID
				AND accauth.AccountID = p_AccountID;
			ELSEIF p_CustomerVendorCheck = 2 THEN
				UPDATE tblAccountAuthenticate accauth SET accauth.VendorAuthValue = ''
				WHERE accauth.CompanyID =  p_CompanyID
				AND accauth.ServiceID = p_ServiceID
				AND accauth.AccountServiceID = p_AccountServiceID
				AND accauth.AccountID = p_AccountID;
			END IF;
			SET v_IPCLI = p_IPCLIString;
		ELSE
			
				SET i = 1;
				REPEAT
					INSERT INTO AccountIPCLITable1
					SELECT FnStringSplit(v_IPCLI, ',', i) WHERE FnStringSplit(v_IPCLI, ',', i) IS NOT NULL LIMIT 1;
					SET i = i + 1;
					UNTIL ROW_COUNT() = 0
				END REPEAT;
			
				SET i = 1;
				REPEAT
					INSERT INTO AccountIPCLITable2
					SELECT FnStringSplit(p_IPCLIString, ',', i) WHERE FnStringSplit(p_IPCLIString, ',', i) IS NOT NULL LIMIT 1;
					SET i = i + 1;
					UNTIL ROW_COUNT() = 0
				END REPEAT;
				
				
				SELECT GROUP_CONCAT(t.splitted_column separator ',') INTO v_IPCLI
				FROM
				(
				SELECT splitted_column FROM AccountIPCLITable1
				UNION
				SELECT splitted_column FROM AccountIPCLITable2
				GROUP BY splitted_column
				ORDER BY splitted_column
				) t;
		END IF;
			
			
		IF p_CustomerVendorCheck = 1 THEN
			UPDATE tblAccountAuthenticate accauth SET accauth.CustomerAuthValue = v_IPCLI, accauth.CustomerAuthRule = p_IPCLICheck
			WHERE accauth.CompanyID =  p_CompanyID
			AND accauth.ServiceID = p_ServiceID
			AND accauth.AccountServiceID = p_AccountServiceID
			AND accauth.AccountID = p_AccountID;
		ELSEIF p_CustomerVendorCheck = 2 THEN
			UPDATE tblAccountAuthenticate accauth SET accauth.VendorAuthValue = v_IPCLI, accauth.VendorAuthRule = p_IPCLICheck
			WHERE accauth.CompanyID =  p_CompanyID
			AND accauth.ServiceID = p_ServiceID
			AND accauth.AccountServiceID = p_AccountServiceID
			AND accauth.AccountID = p_AccountID;
		END IF;
	ELSEIF v_Check IS NULL && p_IPCLIString IS NOT NULL && p_IPCLIString!='' THEN
	
		IF p_CustomerVendorCheck = 1 THEN
			INSERT INTO tblAccountAuthenticate(CompanyID,AccountID,CustomerAuthRule,CustomerAuthValue,ServiceID,AccountServiceID)
			SELECT p_CompanyID,p_AccountID,p_IPCLICheck,p_IPCLIString,p_ServiceID,p_AccountServiceID;
		ELSEIF p_CustomerVendorCheck = 2 THEN
			INSERT INTO tblAccountAuthenticate(CompanyID,AccountID,VendorAuthRule,VendorAuthValue,ServiceID,AccountServiceID)
			SELECT p_CompanyID,p_AccountID,p_IPCLICheck,p_IPCLIString,p_ServiceID,p_AccountServiceID;
		END IF;
	END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ ;
	
END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_setAccountDiscountPlan`;
DELIMITER //
CREATE PROCEDURE `prc_setAccountDiscountPlan`(
	IN `p_AccountID` INT,
	IN `p_DiscountPlanID` INT,
	IN `p_Type` INT,
	IN `p_BillingDays` INT,
	IN `p_DayDiff` INT,
	IN `p_CreatedBy` VARCHAR(50),
	IN `p_Today` DATETIME,
	IN `p_ServiceID` INT,
	IN `p_AccountServiceID` INT,
	IN `p_AccountSubscriptionID` INT,
	IN `p_AccountName` VARCHAR(255),
	IN `p_AccountCLI` VARCHAR(255),
	IN `p_SubscriptionDiscountPlanID` INT

)
BEGIN
	
	DECLARE v_AccountDiscountPlanID INT;
	DECLARE v_StartDate DATE;
	DECLARE v_EndDate DATE;
	
	
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	IF (SELECT COUNT(*) FROM tblAccountBilling WHERE AccountID = p_AccountID AND AccountServiceID = p_AccountServiceID) > 0
	THEN
		SELECT StartDate,EndDate INTO v_StartDate,v_EndDate FROM tblAccountBillingPeriod WHERE AccountID = p_AccountID AND AccountServiceID = p_AccountServiceID AND StartDate <= DATE(p_Today) AND EndDate > DATE(p_Today);
	ELSE
		SELECT StartDate,EndDate INTO v_StartDate,v_EndDate FROM tblAccountBillingPeriod WHERE AccountID = p_AccountID AND ServiceID = 0 AND AccountServiceID = 0 AND StartDate <= DATE(p_Today) AND EndDate > DATE(p_Today);
	END IF;
	

	INSERT INTO tblAccountDiscountPlanHistory(AccountID,AccountDiscountPlanID,DiscountPlanID,Type,CreatedBy,Applied,Changed,StartDate,EndDate,ServiceID,AccountServiceID,AccountSubscriptionID,AccountName,AccountCLI,SubscriptionDiscountPlanID)
	SELECT AccountID,AccountDiscountPlanID,DiscountPlanID,Type,CreatedBy,created_at,p_Today,StartDate,EndDate,ServiceID,AccountServiceID,AccountSubscriptionID,AccountName,AccountCLI,SubscriptionDiscountPlanID 
		FROM tblAccountDiscountPlan
	WHERE AccountID = p_AccountID 
			AND ServiceID = p_ServiceID
			AND AccountServiceID = p_AccountServiceID
			AND Type = p_Type
			AND AccountSubscriptionID=p_AccountSubscriptionID
			AND AccountName=p_AccountName
			AND AccountCLI=p_AccountCLI
			AND SubscriptionDiscountPlanID=p_SubscriptionDiscountPlanID;
	

	INSERT INTO tblAccountDiscountSchemeHistory (AccountDiscountSchemeID,AccountDiscountPlanID,DiscountID,Threshold,Discount,Unlimited,SecondsUsed)
	SELECT ads.AccountDiscountSchemeID,ads.AccountDiscountPlanID,ads.DiscountID,ads.Threshold,ads.Discount,ads.Unlimited,ads.SecondsUsed 
	FROM tblAccountDiscountScheme ads
	INNER JOIN tblAccountDiscountPlan adp
		ON adp.AccountDiscountPlanID = ads.AccountDiscountPlanID
	WHERE AccountID = p_AccountID 
		AND adp.ServiceID = p_ServiceID
		AND adp.AccountServiceID = p_AccountServiceID
		AND Type = p_Type
		AND AccountSubscriptionID=p_AccountSubscriptionID
		AND AccountName=p_AccountName
		AND AccountCLI=p_AccountCLI
		AND SubscriptionDiscountPlanID=p_SubscriptionDiscountPlanID;
	
	DELETE ads FROM tblAccountDiscountScheme ads
	INNER JOIN tblAccountDiscountPlan adp
		ON adp.AccountDiscountPlanID = ads.AccountDiscountPlanID
	WHERE AccountID = p_AccountID 
	   AND adp.ServiceID = p_ServiceID
	   AND adp.AccountServiceID = p_AccountServiceID
		AND Type = p_Type
		AND AccountSubscriptionID=p_AccountSubscriptionID
		AND AccountName=p_AccountName
		AND AccountCLI=p_AccountCLI
		AND SubscriptionDiscountPlanID=p_SubscriptionDiscountPlanID;
		
	DELETE FROM tblAccountDiscountPlan
	WHERE AccountID = p_AccountID
			AND ServiceID = p_ServiceID
			AND AccountServiceID = p_AccountServiceID
			AND Type = p_Type
			AND AccountSubscriptionID=p_AccountSubscriptionID
			AND AccountName=p_AccountName
			AND AccountCLI=p_AccountCLI
			AND SubscriptionDiscountPlanID=p_SubscriptionDiscountPlanID; 
	
	IF p_DiscountPlanID > 0
	THEN
	 
		INSERT INTO tblAccountDiscountPlan (AccountID,DiscountPlanID,Type,CreatedBy,created_at,StartDate,EndDate,ServiceID,AccountServiceID,AccountSubscriptionID,AccountName,AccountCLI,SubscriptionDiscountPlanID)
		VALUES (p_AccountID,p_DiscountPlanID,p_Type,p_CreatedBy,p_Today,v_StartDate,v_EndDate,p_ServiceID,p_AccountServiceID,p_AccountSubscriptionID,p_AccountName,p_AccountCLI,p_SubscriptionDiscountPlanID);
		
		SET v_AccountDiscountPlanID = LAST_INSERT_ID(); 
		
		INSERT INTO tblAccountDiscountScheme(AccountDiscountPlanID,DiscountID,Threshold,Discount,Unlimited)
		SELECT v_AccountDiscountPlanID,d.DiscountID,Threshold*(p_DayDiff/p_BillingDays),Discount,Unlimited
		FROM tblDiscountPlan dp
		INNER JOIN tblDiscount d 
			ON d.DiscountPlanID = dp.DiscountPlanID
		INNER JOIN tblDiscountScheme ds
			ON ds.DiscountID = d.DiscountID
		WHERE dp.DiscountPlanID = p_DiscountPlanID;
	
	END IF;
  
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

-- date 22-03-2019
ALTER TABLE `tblAccount`	
	ADD COLUMN `TaxRateID` VARCHAR(50) NULL DEFAULT NULL AFTER `CustomerRef`;

ALTER TABLE `tblCountry`
	ADD COLUMN `EUCountry` TINYINT NULL DEFAULT '0' AFTER `Keywords`;
	
Update tblCountry set EUCountry=1 where Country IN('AUSTRIA','BELGIUM','BULGARIA','CROATIA','CYPRUS','CZECH REPUBLIC','DENMARK','ESTONIA','FINLAND','FRANCE','GERMANY','GREECE','HUNGARY','IRELAND','ITALY','LATVIA','ITALY','ITALY','LITHUANIA','LUXEMBOURG','MALTA','NETHERLANDS','POLAND','PORTUGAL','ROMANIA','SLOVAKIA','SLOVENIA','SPAIN','SWEDEN','UNITED KINGDOM');	