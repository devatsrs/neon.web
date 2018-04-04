USE `Ratemanagement3`;

ALTER TABLE `tblAccountBilling`
	ADD COLUMN `FirstInvoiceSend` INT NULL DEFAULT '0' AFTER `AutoPaymentSetting`;

CREATE TABLE IF NOT EXISTS `tblAccountDetails` (
	`AccountDetailID` INT(11) NOT NULL AUTO_INCREMENT,
	`AccountID` INT(11) NOT NULL,
	`CustomerPaymentAdd` INT(11) NULL DEFAULT '0',
	`customerpanelpassword` LONGTEXT NULL COLLATE 'utf8_unicode_ci',
	`DisplayRates` INT(11) NULL DEFAULT '0',
	`ResellerOwner` INT(11) NULL DEFAULT '0',
	PRIMARY KEY (`AccountDetailID`),
	UNIQUE INDEX `UI_AccountID` (`AccountID`),
	INDEX `IX_AccountID` (`AccountID`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;


INSERT INTO `tblCronJobCommand` ( `CompanyID`, `GatewayID`, `Title`, `Command`, `Settings`, `Status`, `created_at`, `created_by`) VALUES (1, 4, 'Reseller PBX CDR', 'resellerpbxaccountusage', '[[{"title":"Start Date","type":"text","datepicker":"","value":"","name":"StartDate"},{"title":"End Date","type":"text","value":"","datepicker":"","name":"EndDate"},{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"}]]', 1, '2018-03-13 15:00:00', 'RateManagementSystem');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES (1, 'PBX_RESELLER_CRONJOB', '{"StartDate":"","EndDate":"","ThresholdTime":"120","SuccessEmail":"","ErrorEmail":"","JobTime":"MINUTE","JobInterval":"10","JobDay":["SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"12:00:00 AM","CompanyGatewayID":""}');

DROP PROCEDURE IF EXISTS `prc_copyResellerData`;
DELIMITER //
CREATE PROCEDURE `prc_copyResellerData`(
	IN `p_companyid` INT,
	IN `p_resellerids` TEXT,
	IN `p_is_product` INT,
	IN `p_product` TEXT,
	IN `p_is_subscription` INT,
	IN `p_subscription` TEXT,
	IN `p_is_trunk` INT,
	IN `p_trunk` TEXT
)
BEGIN
	DECLARE v_resellerId_ INT; 
	DECLARE v_pointer_ INT ;
	DECLARE v_rowCount_ INT ; 	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		
		GET DIAGNOSTICS CONDITION 1
		@p2 = MESSAGE_TEXT;
	
		SELECT @p2 as Message;
		
	END;		

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
	
		DROP TEMPORARY TABLE IF EXISTS tmp_currency;
		CREATE TEMPORARY TABLE tmp_currency (
			`ResellerCompanyID` INT,
			`CompanyId` INT,
			`Code` VARCHAR(50),
			`CurrencyID` INT,
			`NewCurrencyID` INT
		) ENGINE=InnoDB;	
				
		DROP TEMPORARY TABLE IF EXISTS tmp_product;
		CREATE TEMPORARY TABLE tmp_product (
			`ResellerCompanyID` INT,
			`CompanyId` INT,
			`Name` VARCHAR(50),
			`Code` VARCHAR(50),
			`Description` LONGTEXT,
			`Amount` DECIMAL(18,2),
			`Active` TINYINT(3) UNSIGNED,
			`Note` LONGTEXT,
			INDEX tmp_product_ResellerCompanyID (`ResellerCompanyID`),
			INDEX tmp_product_Code (`Code`)
	  	);			
				
		DROP TEMPORARY TABLE IF EXISTS tmp_BillingSubscription;
		CREATE TEMPORARY TABLE tmp_BillingSubscription (
				`ResellerCompanyID` INT,
				`CompanyID` INT(11),
				`Name` VARCHAR(50),
				`Description` LONGTEXT,
				`InvoiceLineDescription` VARCHAR(250),
				`ActivationFee` DECIMAL(18,2),						
				`CurrencyID` INT(11),
				`AnnuallyFee` DECIMAL(18,2),
				`QuarterlyFee` DECIMAL(18,2),
				`MonthlyFee` DECIMAL(18,2),
				`WeeklyFee` DECIMAL(18,2),
				`DailyFee` DECIMAL(18,2),
				`Advance` TINYINT(3) UNSIGNED,
				INDEX tmp_BillingSubscription_ResellerCompanyID (`ResellerCompanyID`),
				INDEX tmp_BillingSubscription_Name (`Name`)
		);	

		DROP TEMPORARY TABLE IF EXISTS tmp_Trunk;
		CREATE TEMPORARY TABLE tmp_Trunk (
				`ResellerCompanyID` INT,
				`Trunk` VARCHAR(50),
				`CompanyId` INT(11),
				`RatePrefix` VARCHAR(50),
				`AreaPrefix` VARCHAR(50),
				`Prefix` VARCHAR(50),
				`Status` TINYINT(1),
				INDEX tmp_Trunk_ResellerCompanyID (`ResellerCompanyID`),
				INDEX tmp_Trunk_TrunkName (`Trunk`)
			);	
			
			
		DROP TEMPORARY TABLE IF EXISTS tmp_resellers;
		CREATE TEMPORARY TABLE tmp_resellers (
			`CompanyID` INT,
			`ResellerID` INT,
			`ResellerCompanyID` INT,
			`AccountID` INT,
			`RowNo` INT,
			INDEX tmp_resellers_ResellerID (`ResellerID`),
			INDEX tmp_resellers_ResellerCompanyID (`ResellerCompanyID`),
			INDEX tmp_resellers_RowNo (`RowNo`)
		);			
				
				INSERT INTO tmp_resellers
				SELECT
					CompanyID,
					ResellerID,
					ChildCompanyID as ResellerCompanyID,
					AccountID,
					@row_num := @row_num+1 AS RowID
				FROM tblReseller,(SELECT @row_num := 0) x
				WHERE CompanyID = p_companyid
					  AND FIND_IN_SET(ResellerID,p_resellerids);
				
					
		SET v_pointer_ = 1;
		SET v_rowCount_ = (SELECT COUNT(distinct ResellerCompanyID ) FROM tmp_resellers);
					
		WHILE v_pointer_ <= v_rowCount_
		DO
					
					SET v_resellerId_ = (SELECT ResellerCompanyID FROM tmp_resellers rr WHERE rr.RowNo = v_pointer_);			
					
							/*
							INSERT INTO	tmp_currency(ResellerCompanyID,CompanyId,Code,CurrencyID)	
							SELECT v_resellerId_ as ResellerCompanyID,p_companyid as CompanyId,Code, CurrencyId FROM `tblCurrency` WHERE CompanyId	= p_companyid;	
							
							UPDATE tmp_currency tc LEFT JOIN tblCurrency c ON tc.Code=c.Code AND tc.ResellerCompanyID = v_resellerId_ AND c.CompanyId = v_resellerId_
									set NewCurrencyID = c.CurrencyId
							WHERE c.CurrencyId IS NOT NULL;		*/
					
					IF p_is_product =1
					THEN	
					
						INSERT INTO tmp_product(ResellerCompanyID,CompanyId,Name,Code,Description,Amount,Active,Note)
						SELECT DISTINCT v_resellerId_ as ResellerCompanyID,p_companyid as `CompanyId`,Name,Code,Description,Amount,Active,Note
							FROM RMBilling3.tblProduct
						WHERE CompanyId = p_companyid AND FIND_IN_SET(ProductID,p_product);
					
					END IF;
					
					IF p_is_subscription = 1
					THEN
					
						INSERT INTO tmp_BillingSubscription(`ResellerCompanyID`,`CompanyID`,Name,Description,InvoiceLineDescription,ActivationFee,CurrencyID,AnnuallyFee,QuarterlyFee,MonthlyFee,WeeklyFee,DailyFee,Advance)
						SELECT DISTINCT v_resellerId_ as ResellerCompanyID, `CompanyID`,Name,Description,InvoiceLineDescription,ActivationFee,CurrencyID,AnnuallyFee,QuarterlyFee,MonthlyFee,WeeklyFee,DailyFee,Advance
						FROM RMBilling3.tblBillingSubscription
						WHERE CompanyID = p_companyid AND FIND_IN_SET(SubscriptionID,p_subscription);
					
					END IF;
					
					/*
					IF p_is_trunk = 1
					THEN
					
					INSERT INTO tmp_Trunk(ResellerCompanyID,Trunk,CompanyId,RatePrefix,AreaPrefix,`Prefix`,Status)
							SELECT DISTINCT v_resellerId_ as ResellerCompanyID,Trunk,CompanyId,RatePrefix,AreaPrefix,`Prefix`,Status
								FROM tblTrunk
							WHERE CompanyId = p_companyid AND FIND_IN_SET(TrunkID,p_trunk);
							
					END IF;		*/
					
					 SET v_pointer_ = v_pointer_ + 1;			 
			
		END WHILE;
			
		
		IF p_is_product =1
		THEN	
					INSERT INTO RMBilling3.tblProduct (CompanyId,Name,Code,Description,Amount,Active,Note,CreatedBy,ModifiedBy,created_at,updated_at)
					SELECT DISTINCT tp.ResellerCompanyID as `CompanyId`,tp.Name,tp.Code,tp.Description,tp.Amount,tp.Active,tp.Note,'system' as CreatedBy,'system' as ModifiedBy,NOW(),NOW()
						FROM tmp_product tp 
							LEFT JOIN RMBilling3.tblProduct p
							ON tp.ResellerCompanyID = p.CompanyId
							AND tp.Code=p.Code
					WHERE p.ProductID IS NULL;		
		
		END IF;
		

		
		IF p_is_subscription = 1
		THEN
				
				INSERT INTO RMBilling3.tblBillingSubscription(`CompanyID`,Name,Description,InvoiceLineDescription,ActivationFee,CurrencyID,AnnuallyFee,QuarterlyFee,MonthlyFee,WeeklyFee,DailyFee,Advance,created_at,updated_at,ModifiedBy,CreatedBy)
				SELECT DISTINCT tb.ResellerCompanyID as `CompanyID`,tb.Name,tb.Description,tb.InvoiceLineDescription,tb.ActivationFee,CurrencyID,tb.AnnuallyFee,tb.QuarterlyFee,tb.MonthlyFee,tb.WeeklyFee,tb.DailyFee,tb.Advance,Now(),Now(),'system' as ModifiedBy,'system' as CreatedBy 
					FROM tmp_BillingSubscription tb 
						LEFT JOIN RMBilling3.tblBillingSubscription b
						ON tb.ResellerCompanyID = b.CompanyID
						AND tb.Name = b.Name
				WHERE b.SubscriptionID IS NULL;
		
		END IF;
		/*
		IF p_is_trunk =1
		THEN

				INSERT INTO tblTrunk (Trunk,CompanyId,RatePrefix,AreaPrefix,`Prefix`,Status,created_at,updated_at)
				SELECT DISTINCT tt.Trunk, tt.ResellerCompanyID as `CompanyId`,tt.RatePrefix,tt.AreaPrefix,tt.`Prefix`,tt.Status,Now(),Now()
				FROM tmp_Trunk tt
					LEFT JOIN tblTrunk tr ON tt.ResellerCompanyID = tr.CompanyId AND tt.Trunk = tr.Trunk
				WHERE tr.TrunkID IS NULL;
		
		END IF; */
	
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
			IF ( (CASE WHEN abc.BalanceThreshold LIKE '%p' THEN REPLACE(abc.BalanceThreshold, 'p', '')/ 100 * abc.PermanentCredit ELSE abc.BalanceThreshold END) < abc.BalanceAmount AND abc.BalanceThreshold <> 0 ,1,0) as BalanceWarning,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(abc.UnbilledAmount,0),v_Round_)) as CUA,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(abc.VendorUnbilledAmount,0),v_Round_)) as VUA,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,ROUND(COALESCE(abc.BalanceAmount,0),v_Round_)) as AE,
			CONCAT((SELECT Symbol FROM tblCurrency WHERE tblCurrency.CurrencyId = tblAccount.CurrencyId) ,IF(ROUND(COALESCE(abc.PermanentCredit,0),v_Round_) - ROUND(COALESCE(abc.BalanceAmount,0),v_Round_)<0,0,ROUND(COALESCE(abc.PermanentCredit,0),v_Round_) - ROUND(COALESCE(abc.BalanceAmount,0),v_Round_))) as ACL,
			abc.BalanceThreshold,
			tblAccount.Blocked
		FROM tblAccount
		LEFT JOIN tblAccountBalance abc
			ON abc.AccountID = tblAccount.AccountID
		LEFT JOIN tblUser
			ON tblAccount.Owner = tblUser.UserID
		LEFT JOIN tblContact
			ON tblContact.Owner=tblAccount.AccountID
		LEFT JOIN tblAccountAuthenticate
			ON tblAccountAuthenticate.AccountID = tblAccount.AccountID
		LEFT JOIN tblCLIRateTable
			ON tblCLIRateTable.AccountID = tblAccount.AccountID
		WHERE  -- tblAccount.CompanyID = p_CompanyID AND
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
			AND (p_low_balance = 0 OR ( p_low_balance = 1 AND abc.BalanceThreshold <> 0 AND (CASE WHEN abc.BalanceThreshold LIKE '%p' THEN REPLACE(abc.BalanceThreshold, 'p', '')/ 100 * abc.PermanentCredit ELSE abc.BalanceThreshold END) < abc.BalanceAmount) )
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
		LEFT JOIN tblAccountBalance abc
			ON abc.AccountID = tblAccount.AccountID
		LEFT JOIN tblUser
			ON tblAccount.Owner = tblUser.UserID
		LEFT JOIN tblContact
			ON tblContact.Owner=tblAccount.AccountID
		LEFT JOIN tblAccountAuthenticate
			ON tblAccountAuthenticate.AccountID = tblAccount.AccountID
		LEFT JOIN tblCLIRateTable
			ON tblCLIRateTable.AccountID = tblAccount.AccountID
		WHERE --  tblAccount.CompanyID = p_CompanyID AND
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
			AND (p_low_balance = 0 OR ( p_low_balance = 1 AND abc.BalanceThreshold <> 0 AND (CASE WHEN abc.BalanceThreshold LIKE '%p' THEN REPLACE(abc.BalanceThreshold, 'p', '')/ 100 * abc.PermanentCredit ELSE abc.BalanceThreshold END) < abc.BalanceAmount) );

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
		LEFT JOIN tblAccountBalance abc
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
			AND (p_low_balance = 0 OR ( p_low_balance = 1 AND abc.BalanceThreshold <> 0 AND (CASE WHEN abc.BalanceThreshold LIKE '%p' THEN REPLACE(abc.BalanceThreshold, 'p', '')/ 100 * abc.PermanentCredit ELSE abc.BalanceThreshold END) < abc.BalanceAmount) )
		GROUP BY tblAccount.AccountID;
	END IF;
	IF p_isExport = 2
	THEN
		SELECT
			tblAccount.AccountID,
			tblAccount.AccountName
		FROM tblAccount
		LEFT JOIN tblAccountBalance abc
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
			AND (p_low_balance = 0 OR ( p_low_balance = 1 AND abc.BalanceThreshold <> 0 AND (CASE WHEN abc.BalanceThreshold LIKE '%p' THEN REPLACE(abc.BalanceThreshold, 'p', '')/ 100 * abc.PermanentCredit ELSE abc.BalanceThreshold END) < abc.BalanceAmount) )
		GROUP BY tblAccount.AccountID;
	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_insertResellerData`;
DELIMITER //
CREATE PROCEDURE `prc_insertResellerData`(
	IN `p_companyid` INT,
	IN `p_childcompanyid` INT,
	IN `p_accountname` VARCHAR(100),
	IN `p_firstname` VARCHAR(100),
	IN `p_lastname` VARCHAR(100),
	IN `p_accountid` INT,
	IN `p_email` VARCHAR(100),
	IN `p_password` TEXT,
	IN `p_is_product` INT,
	IN `p_product` TEXT,
	IN `p_is_subscription` INT,
	IN `p_subscription` TEXT,
	IN `p_is_trunk` INT,
	IN `p_trunk` TEXT,
	IN `p_allowwhitelabel` INT
)
BEGIN

	DECLARE companycodedeckid int;
	DECLARE resellercodedeckid int;

	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		GET DIAGNOSTICS CONDITION 1
		@p2 = MESSAGE_TEXT;
		SELECT @p2 as Message;
		-- ROLLBACK;
	END;		

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_currency;
	CREATE TEMPORARY TABLE tmp_currency (
		`CompanyId` INT,
		`Code` VARCHAR(50),
		`CurrencyID` INT,
		`NewCurrencyID` INT
	) ENGINE=InnoDB;

	-- START TRANSACTION;

	INSERT INTO	tblUser(CompanyID,FirstName,LastName,EmailAddress,password,AdminUser,updated_at,created_at,created_by,Status,JobNotification)	
	SELECT p_childcompanyid as CompanyID,p_firstname as FirstName,p_lastname as LastName , p_email as EmailAddress,p_password as password, 1 as AdminUser, Now(),Now(),'system' as created_by, '1' as Status, '1' as JobNotification;

	INSERT INTO tblEmailTemplate (CompanyID,TemplateName,Subject,TemplateBody,created_at,CreatedBy,updated_at,`Type`,EmailFrom,StaticType,SystemType,Status,StatusDisabled,TicketTemplate)
	SELECT DISTINCT p_childcompanyid as `CompanyID`,TemplateName,Subject,TemplateBody,NOW(),'system' as CreatedBy,NOW(),`Type`, p_email as `EmailFrom`,StaticType,SystemType,Status,StatusDisabled,TicketTemplate	
	FROM tblEmailTemplate
	WHERE StaticType=1 AND CompanyID = p_companyid ;

	INSERT INTO tblCompanyConfiguration (`CompanyID`,`Key`,`Value`)
	SELECT DISTINCT p_childcompanyid as `CompanyID`,`Key`,`Value`	
	FROM tblCompanyConfiguration
	WHERE CompanyID = p_companyid;

	INSERT INTO tblCronJobCommand (`CompanyID`,GatewayID,Title,Command,Settings,Status,created_at,created_by)
	SELECT DISTINCT p_childcompanyid as `CompanyID`,GatewayID,Title,Command,Settings,Status,created_at,created_by	
	FROM tblCronJobCommand
	WHERE CompanyID = p_companyid;

	INSERT INTO tblTaxRate (CompanyId,Title,Amount,TaxType,FlatStatus,Status,created_at,updated_at)
	SELECT DISTINCT p_childcompanyid as `CompanyId`,Title,Amount,TaxType,FlatStatus,Status,NOW(),NOW()
	FROM tblTaxRate
	WHERE CompanyId = p_companyid;

	/*
	INSERT INTO tblCurrency (CompanyId,Code,Description,Status,created_at,updated_at,Symbol)
	SELECT DISTINCT p_childcompanyid as `CompanyId` ,Code,Description,Status,NOW(),NOW(),Symbol
	FROM tblCurrency
	WHERE CompanyId = p_companyid; */

	IF p_is_product =1
	THEN	

		INSERT INTO RMBilling3.tblProduct (CompanyId,Name,Code,Description,Amount,Active,Note,CreatedBy,ModifiedBy,created_at,updated_at)
		SELECT DISTINCT p_childcompanyid as `CompanyId`,Name,Code,Description,Amount,Active,Note,'system' as CreatedBy,'system' as ModifiedBy,NOW(),NOW()
		FROM RMBilling3.tblProduct
		WHERE CompanyId = p_companyid AND FIND_IN_SET(ProductID,p_product);

	END IF;

	IF p_is_subscription = 1
	THEN

		/*
		INSERT INTO	tmp_currency(CompanyId,Code,CurrencyID)	
		SELECT p_companyid as CompanyId,Code, CurrencyId FROM `tblCurrency` WHERE CompanyId	= p_companyid;	

		UPDATE tmp_currency tc LEFT JOIN tblCurrency c ON tc.Code=c.Code AND c.CompanyId = p_childcompanyid
		set NewCurrencyID = c.CurrencyId
		WHERE c.CurrencyId IS NOT NULL;

		*/

		INSERT INTO RMBilling3.tblBillingSubscription(`CompanyID`,Name,Description,InvoiceLineDescription,ActivationFee,created_at,updated_at,ModifiedBy,CreatedBy,CurrencyID,AnnuallyFee,QuarterlyFee,MonthlyFee,WeeklyFee,DailyFee,Advance)
		SELECT DISTINCT p_childcompanyid as `CompanyID`,Name,Description,InvoiceLineDescription,ActivationFee,created_at,updated_at,ModifiedBy,CreatedBy,CurrencyID,AnnuallyFee,QuarterlyFee,MonthlyFee,WeeklyFee,DailyFee,Advance
		FROM RMBilling3.tblBillingSubscription
		WHERE CompanyID = p_companyid AND FIND_IN_SET(SubscriptionID,p_subscription);

	END IF;

	/*
	IF p_is_trunk =1
	THEN

	INSERT INTO tblTrunk (Trunk,CompanyId,RatePrefix,AreaPrefix,`Prefix`,Status,created_at,updated_at)
	SELECT DISTINCT Trunk, p_childcompanyid as `CompanyId`,RatePrefix,AreaPrefix,`Prefix`,Status,NOW(),NOW()
	FROM tblTrunk
	WHERE CompanyId = p_companyid AND FIND_IN_SET(TrunkID,p_trunk);

	END IF;
	*/

	INSERT INTO tblReseller(ResellerName,CompanyID,ChildCompanyID,AccountID,FirstName,LastName,Email,Password,Status,AllowWhiteLabel,created_at,updated_at,created_by)
	SELECT p_accountname as ResellerName,p_companyid as CompanyID,p_childcompanyid as ChildCompanyID,p_accountid as AccountID,p_firstname as FirstName,p_lastname as LastName,p_email as Email,p_password as Password,'1' as Status,p_allowwhitelabel as AllowWhiteLabel,Now(),Now(),'system' as created_by;

	INSERT INTO tblCompanySetting(`CompanyID`,`Key`,`Value`)
	SELECT p_childcompanyid as `CompanyID`,`Key`,`Value` 
	FROM tblCompanySetting
	WHERE CompanyID = p_companyid AND `Key`='RoundChargesAmount';

	SELECT CodeDeckId INTO companycodedeckid  FROM tblCodeDeck WHERE CompanyId=p_companyid AND DefaultCodedeck=1;

	IF companycodedeckid > 0 THEN
		INSERT INTO tblCodeDeck (CompanyId, CodeDeckName, created_at, CreatedBy, updated_at, ModifiedBy, `Type`, DefaultCodedeck) 
		VALUES (p_childcompanyid, 'Default Codedeck', Now(), 'Dev', Now(), NULL, NULL, 1);

		SELECT CodeDeckId INTO resellercodedeckid  FROM tblCodeDeck WHERE CompanyId=p_childcompanyid AND DefaultCodedeck=1;

		INSERT INTO tblRate(CountryID,CompanyID,CodeDeckId,Code,Description,Interval1,IntervalN,created_at)
		SELECT CountryID,p_childcompanyid as CompanyID,resellercodedeckid as CodeDeckId,Code,Description,Interval1,IntervalN,Now() as created_at FROM tblRate where CodeDeckId=companycodedeckid;


	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;

