CREATE TABLE IF NOT EXISTS `tblAccountBalanceThreshold` (
	`AccountBalanceThresholdID` INT(11) NOT NULL AUTO_INCREMENT,
	`AccountID` INT(11) NULL DEFAULT NULL,
	`BalanceThreshold` VARCHAR(50) NULL DEFAULT NULL,
	`BalanceThresholdEmail` TEXT NULL,
	`created_at` DATETIME NULL DEFAULT NULL,
	`updated_at` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`AccountBalanceThresholdID`)
)
COLLATE='latin1_swedish_ci'
ENGINE=InnoDB
;

DROP PROCEDURE IF EXISTS `prc_GetAccounts`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetAccounts`(
	IN `p_CompanyID` INT,
	IN `p_userID` INT ,
	IN `p_IsVendor` INT ,
	IN `p_isCustomer` INT ,
	IN `p_isReseller` INT,
	IN `p_ResellerID` INT,
	IN `p_activeStatus` INT,
	IN `p_VerificationStatus` INT,
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
	DECLARE v_OffSet_ INT;
	DECLARE v_Round_ INT;
	DECLARE v_raccountids TEXT;
	DECLARE v_resellercompanyid INT;
	SET v_raccountids = '';
	SET v_resellercompanyid = 0;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	SET sql_mode=only_full_group_by;
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
			abt.BalanceThreshold,
			( CASE WHEN abt.BalanceThreshold LIKE '%p' 
			THEN REPLACE(abt.BalanceThreshold, 'p', '')/ 100 * abc.PermanentCredit 
								ELSE abt.BalanceThreshold END
						) AS BalanceThreshold1,
			0 AS BalanceWarning
	FROM tblAccount a 
		LEFT JOIN tblAccountBilling ab ON a.AccountID=ab.AccountID AND ab.ServiceID=0
		LEFT JOIN tblAccountBalance abc ON a.AccountID = abc.AccountID 
		LEFT JOIN tblAccountBalanceThreshold abt ON a.AccountID=abt.AccountID
		;		
	UPDATE tmp_AccountBalance ta
	INNER JOIN tblAccountBalanceLog bl
		 	ON ta.AccountID=bl.AccountID
	SET ta.BalanceAmount = bl.BalanceAmount,ta.UnbilledAmount = 0,ta.VendorUnbilledAmount=0,ta.SOAOffset=bl.BalanceAmount
	WHERE ta.BillingType=1;
	
	UPDATE tmp_AccountBalance SET BalanceWarning =
	 IF (BalanceThreshold1 >  BalanceAmount AND BalanceThreshold <>'0' AND BalanceThreshold <>'' ,1,0);
	
	
	
	IF p_ResellerID > 0
	THEN
		DROP TEMPORARY TABLE IF EXISTS tmp_reselleraccounts_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_reselleraccounts_(
			AccountID INT
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
			CONCAT(tblAccount.FirstName,' ',tblAccount.LastName) AS Ownername,
			tblAccount.Phone,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(MAX(abc.SOAOffset),0),v_Round_)) AS OutStandingAmount,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(MAX(abc.UnbilledAmount),0),v_Round_) - ROUND(COALESCE(MAX(abc.VendorUnbilledAmount),0),v_Round_)) AS UnbilledAmount,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(MAX(abc.PermanentCredit),0),v_Round_)) AS PermanentCredit,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(MAX(abc.BalanceAmount),0),v_Round_)) AS AccountExposure,
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
		   MAX(abc.BalanceWarning) AS BalanceWarning, 
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(MAX(abc.UnbilledAmount),0),v_Round_)) AS CUA,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(MAX(abc.VendorUnbilledAmount),0),v_Round_)) AS VUA,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(MAX(abc.BalanceAmount),0),v_Round_)) AS AE,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,IF(ROUND(COALESCE(MAX(abc.PermanentCredit),0),v_Round_) - ROUND(COALESCE(MAX(abc.BalanceAmount),0),v_Round_)<0,0,ROUND(COALESCE(MAX(abc.PermanentCredit),0),v_Round_) - ROUND(COALESCE(MAX(abc.BalanceAmount),0),v_Round_))) AS ACL,
			MAX(abc.BalanceThreshold) as BalanceThreshold,
			tblAccount.Blocked,
			tblAccount.IsReseller
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
			AND ((p_AccountName = '' OR tblAccount.AccountName LIKE CONCAT('%',p_AccountName,'%')))
			AND ((p_IPCLI = '' OR tblCLIRateTable.CLI LIKE CONCAT('%',p_IPCLI,'%') OR CONCAT(IFNULL(tblAccountAuthenticate.CustomerAuthValue,''),',',IFNULL(tblAccountAuthenticate.VendorAuthValue,'')) LIKE CONCAT('%',p_IPCLI,'%')))
			AND ((p_tags = '' OR tblAccount.tags LIKE CONCAT(p_tags,'%')))
			AND ((p_ContactName = '' OR (CONCAT(IFNULL(tblContact.FirstName,'') ,' ', IFNULL(tblContact.LastName,''))) LIKE CONCAT('%',p_ContactName,'%')))	 
			AND (p_low_balance = 0 OR ( p_low_balance = 1 AND abc.BalanceWarning=1)) 
		 GROUP BY tblAccount.AccountID
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
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutStandingAmountDESC') THEN MAX(abc.SOAOffset)
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'OutStandingAmountASC') THEN MAX(abc.SOAOffset)
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PermanentCreditDESC') THEN MAX(abc.PermanentCredit)
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'PermanentCreditASC') THEN MAX(abc.PermanentCredit)
			END ASC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'UnbilledAmountDESC') THEN (ROUND(COALESCE(MAX(abc.UnbilledAmount),0),v_Round_) - ROUND(COALESCE(MAX(abc.VendorUnbilledAmount),0),v_Round_))
			END DESC,
			CASE
				WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'UnbilledAmountASC') THEN (ROUND(COALESCE(MAX(abc.UnbilledAmount),0),v_Round_) - ROUND(COALESCE(MAX(abc.VendorUnbilledAmount),0),v_Round_))
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
			AND ((p_AccountName = '' OR tblAccount.AccountName LIKE CONCAT('%',p_AccountName,'%')))
			AND ((p_IPCLI = '' OR tblCLIRateTable.CLI LIKE CONCAT('%',p_IPCLI,'%') OR CONCAT(IFNULL(tblAccountAuthenticate.CustomerAuthValue,''),',',IFNULL(tblAccountAuthenticate.VendorAuthValue,'')) LIKE CONCAT('%',p_IPCLI,'%')))
			AND ((p_tags = '' OR tblAccount.tags LIKE CONCAT(p_tags,'%')))
			AND ((p_ContactName = '' OR (CONCAT(IFNULL(tblContact.FirstName,'') ,' ', IFNULL(tblContact.LastName,''))) LIKE CONCAT('%',p_ContactName,'%')))
			AND (p_low_balance = 0 OR ( p_low_balance = 1 AND abc.BalanceWarning=1)) ;
	END IF;
	IF p_isExport = 1
	THEN
		SELECT 
			tblAccount.Number AS NO,
			tblAccount.AccountName,
			CONCAT(tblAccount.FirstName,' ',tblAccount.LastName) AS NAME,
			tblAccount.Phone,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(MAX(abc.SOAOffset),0),v_Round_)) AS 'OutStanding',
			tblAccount.Email,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(MAX(abc.UnbilledAmount),0),v_Round_)  - ROUND(COALESCE(MAX(abc.VendorUnbilledAmount),0),v_Round_)) AS 'Unbilled Amount',
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(MAX(abc.PermanentCredit),0),v_Round_)) AS 'Credit Limit',
			CONCAT(tblUser.FirstName,' ',tblUser.LastName) AS 'Account Owner',
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(MAX(abc.BalanceAmount),0),v_Round_)) AS AccountExposure
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
			AND ((p_AccountName = '' OR tblAccount.AccountName LIKE CONCAT('%',p_AccountName,'%')))
			AND ((p_IPCLI = '' OR tblCLIRateTable.CLI LIKE CONCAT('%',p_IPCLI,'%') OR CONCAT(IFNULL(tblAccountAuthenticate.CustomerAuthValue,''),',',IFNULL(tblAccountAuthenticate.VendorAuthValue,'')) LIKE CONCAT('%',p_IPCLI,'%')))
			AND ((p_tags = '' OR tblAccount.tags LIKE CONCAT(p_tags,'%')))
			AND ((p_ContactName = '' OR (CONCAT(IFNULL(tblContact.FirstName,'') ,' ', IFNULL(tblContact.LastName,''))) LIKE CONCAT('%',p_ContactName,'%')))		
			AND (p_low_balance = 0 OR ( p_low_balance = 1 AND abc.BalanceWarning=1)) 
		 GROUP BY tblAccount.AccountID
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
			AND ((p_AccountName = '' OR tblAccount.AccountName LIKE CONCAT('%',p_AccountName,'%')))
			AND ((p_IPCLI = '' OR tblCLIRateTable.CLI LIKE CONCAT('%',p_IPCLI,'%') OR CONCAT(IFNULL(tblAccountAuthenticate.CustomerAuthValue,''),',',IFNULL(tblAccountAuthenticate.VendorAuthValue,'')) LIKE CONCAT('%',p_IPCLI,'%')))
			AND ((p_tags = '' OR tblAccount.tags LIKE CONCAT(p_tags,'%')))
			AND ((p_ContactName = '' OR (CONCAT(IFNULL(tblContact.FirstName,'') ,' ', IFNULL(tblContact.LastName,''))) LIKE CONCAT('%',p_ContactName,'%')))
			AND (p_low_balance = 0 OR ( p_low_balance = 1 AND abc.BalanceWarning=1))
		 GROUP BY tblAccount.AccountID
		;
	END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_LowBalanceReminder`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_LowBalanceReminder`(
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
		BalanceThreshold VARCHAR(50),
		BalanceThresholdEmail TEXT
	);
	
	INSERT INTO tmp_AccountBalance(AccountID,BillingType,PermanentCredit,BalanceAmount,BalanceThreshold,BalanceThresholdEmail)
	SELECT a.AccountID,
			 abg.BillingType,
			 ab.PermanentCredit,
			 ab.BalanceAmount,
			 abt.BalanceThreshold,
			 abt.BalanceThresholdEmail
	FROM tblAccountBalance ab 
		INNER JOIN tblAccount a 
			ON a.AccountID = ab.AccountID
		INNER JOIN tblAccountBilling abg 
			ON abg.AccountID  = a.AccountID  AND abg.ServiceID = 0 
		INNER JOIN tblBillingClass b
			ON b.BillingClassID = abg.BillingClassID
		LEFT JOIN tblAccountBalanceThreshold abt ON a.AccountID=abt.AccountID 
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
						) >  BalanceAmount AND BalanceThreshold <> 0 ,1,0) AS BalanceWarning,							 
		 AccountID, BalanceThresholdEmail, BalanceAmount , BalanceThreshold
		FROM tmp_AccountBalance
		Where BalanceAmount <> 0 and BalanceThreshold > BalanceAmount
		order by ABS(BalanceThreshold) limit 1 ;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;