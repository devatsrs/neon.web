USE `Ratemanagement3`;
CREATE TABLE `tblAuditHeader` (
	`AuditHeaderID` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`UserID` INT(11) NOT NULL,
	`CompanyID` INT(11) NOT NULL,
	`Date` DATE NULL DEFAULT NULL COMMENT 'account, product etc',
	`ParentColumnName` TEXT NULL COLLATE 'utf8_unicode_ci',
	`ParentColumnID` INT(11) NOT NULL,
	`Type` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`IP` TEXT NULL COLLATE 'utf8_unicode_ci',
	`UserType` INT(11) NULL DEFAULT '0',
	PRIMARY KEY (`AuditHeaderID`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;


CREATE TABLE `tblAuditDetails` (
	`AuditDetailID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`AuditHeaderID` INT(11) UNSIGNED NOT NULL,
	`ColumnName` VARCHAR(255) NOT NULL COLLATE 'utf8_unicode_ci',
	`OldValue` LONGTEXT NULL COLLATE 'utf8_unicode_ci',
	`NewValue` LONGTEXT NULL COLLATE 'utf8_unicode_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`created_by` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	PRIMARY KEY (`AuditDetailID`),
	INDEX `IX_AuditHeaderID` (`AuditHeaderID`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;


ALTER TABLE `tblAccountBilling`
	ADD COLUMN `AutoPaymentSetting` VARCHAR(50) NULL DEFAULT NULL;

ALTER TABLE `tblAccountService`
	ADD COLUMN `ServiceDescription` TEXT NULL DEFAULT NULL;
	
ALTER TABLE `tblAccountService`
	ADD COLUMN `ServiceTitleShow` INT NOT NULL DEFAULT '1';	

ALTER TABLE `tblAccount`
	ADD COLUMN `ShowAllPaymentMethod` INT NULL DEFAULT '1';
	

INSERT INTO `tblIntegration` (`IntegrationID`, `CompanyId`, `Title`, `Slug`, `ParentID`, `MultiOption`) VALUES (20, 1, 'Stripe ACH', 'stripeach', 4, 'N');
	
INSERT INTO `tblEmailTemplate` (`CompanyID`, `TemplateName`, `Subject`, `TemplateBody`, `created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`, `userID`, `Type`, `EmailFrom`, `StaticType`, `SystemType`, `Status`, `StatusDisabled`, `TicketTemplate`) VALUES ( 1, 'Auto Invoice Paid', '{{InvoiceNumber}} Invoice Paid', '<p>Hi</p><p>We hereby confirm that we have received payment..</p><h4>Payment Detail</h4><p>Account Name : {{AccountName}}</p><p>Paid Amount : {{PaidAmount}}</p><p>Status : {{PaidStatus}}</p><p>Payment Method : {{PaymentMethod}}</p><p>Payment Notes : {{PaymentNotes}}</p><p><br></p><h4>Best Regards</h4><p>{{CompanyName}}<br></p><p><br></p>', '2017-07-28 11:12:25', NULL, '2017-07-28 16:02:36', 'System', NULL, 10, '', 1, 'AutoInvoicePayment', 0, 0, 0);

	
DROP PROCEDURE IF EXISTS `prc_GetAccounts`;
DELIMITER |	
CREATE PROCEDURE `prc_GetAccounts`(
	IN `p_CompanyID` int,
	IN `p_userID` int ,
	IN `p_IsVendor` int ,
	IN `p_isCustomer` int ,
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

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	SELECT fnGetRoundingPoint(p_CompanyID) INTO v_Round_;

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
		WHERE   tblAccount.CompanyID = p_CompanyID
			AND tblAccount.AccountType = 1
			AND tblAccount.Status = p_activeStatus
			AND tblAccount.VerificationStatus = p_VerificationStatus
			AND (p_userID = 0 OR tblAccount.Owner = p_userID)
			AND ((p_IsVendor = 0 OR tblAccount.IsVendor = 1))
			AND ((p_isCustomer = 0 OR tblAccount.IsCustomer = 1))
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
		WHERE   tblAccount.CompanyID = p_CompanyID
			AND tblAccount.AccountType = 1
			AND tblAccount.Status = p_activeStatus
			AND tblAccount.VerificationStatus = p_VerificationStatus
			AND (p_userID = 0 OR tblAccount.Owner = p_userID)
			AND ((p_IsVendor = 0 OR tblAccount.IsVendor = 1))
			AND ((p_isCustomer = 0 OR tblAccount.IsCustomer = 1))
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
			AND ((p_AccountNo = '' OR tblAccount.Number LIKE p_AccountNo))
			AND ((p_AccountName = '' OR tblAccount.AccountName LIKE Concat('%',p_AccountName,'%')))
			AND ((p_IPCLI = '' OR tblCLIRateTable.CLI LIKE CONCAT('%',p_IPCLI,'%') OR CONCAT(IFNULL(tblAccountAuthenticate.CustomerAuthValue,''),',',IFNULL(tblAccountAuthenticate.VendorAuthValue,'')) LIKE CONCAT('%',p_IPCLI,'%')))
			AND ((p_tags = '' OR tblAccount.tags LIKE Concat(p_tags,'%')))
			AND ((p_ContactName = '' OR (CONCAT(IFNULL(tblContact.FirstName,'') ,' ', IFNULL(tblContact.LastName,''))) LIKE CONCAT('%',p_ContactName,'%')))
			AND (p_low_balance = 0 OR ( p_low_balance = 1 AND abc.BalanceThreshold <> 0 AND (CASE WHEN abc.BalanceThreshold LIKE '%p' THEN REPLACE(abc.BalanceThreshold, 'p', '')/ 100 * abc.PermanentCredit ELSE abc.BalanceThreshold END) < abc.BalanceAmount) )
		GROUP BY tblAccount.AccountID;
	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END|
DELIMITER ;
	
DROP PROCEDURE IF EXISTS `prc_GetLCRwithPrefix`;
DELIMITER |
CREATE PROCEDURE `prc_GetLCRwithPrefix`(
	IN `p_companyid` INT,
	IN `p_trunkID` INT,
	IN `p_codedeckID` INT,
	IN `p_CurrencyID` INT,
	IN `p_code` VARCHAR(50),
	IN `p_Description` VARCHAR(250),
	IN `p_AccountIds` TEXT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_SortOrder` VARCHAR(50),
	IN `p_Preference` INT,
	IN `p_Position` INT,
	IN `p_isExport` INT
)
BEGIN

DECLARE v_OffSet_ int;
DECLARE v_Code VARCHAR(50) ;
DECLARE v_pointer_ int;
DECLARE v_rowCount_ int;
DECLARE v_p_code VARCHAR(50);
DECLARE v_Codlen_ int;
DECLARE v_position int;
DECLARE v_p_code__ VARCHAR(50);
DECLARE v_has_null_position int ;
DECLARE v_next_position1 VARCHAR(200) ;
DECLARE v_CompanyCurrencyID_ INT; 	
	
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

     SET @@session.collation_connection='utf8_unicode_ci';
     SET @@session.character_set_results='utf8';

    SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
  
DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage_;
       CREATE TEMPORARY TABLE tmp_VendorRate_stage_ (
          RowCode VARCHAR(50) ,
          AccountId INT ,
          AccountName VARCHAR(100) ,
          Code VARCHAR(50) ,
          Rate DECIMAL(18,6) ,
          ConnectionFee DECIMAL(18,6) ,
          EffectiveDate DATETIME ,
          Description VARCHAR(255),
          Preference INT,
          MaxMatchRank int ,
          prev_prev_RowCode VARCHAR(50),
          prev_AccountID int
        ) 
  ;

     DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage2_;
       CREATE TEMPORARY TABLE tmp_VendorRate_stage2_ (
          RowCode VARCHAR(50) ,
          AccountId INT ,
          AccountName VARCHAR(100) ,
          Code VARCHAR(50) ,
          Rate DECIMAL(18,6) ,
          ConnectionFee DECIMAL(18,6) ,
          EffectiveDate DATETIME ,
          Description VARCHAR(255),
          Preference INT,
          MaxMatchRank int ,
          prev_prev_RowCode VARCHAR(50),
          prev_AccountID int
        ) 
  ;

  DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
  CREATE TEMPORARY TABLE tmp_VendorRate_ (
     AccountId INT ,
     AccountName VARCHAR(100) ,
     Code VARCHAR(50) ,
     Rate DECIMAL(18,6) ,
     ConnectionFee DECIMAL(18,6) ,
     EffectiveDate DATETIME ,
     Description VARCHAR(255),
     Preference INT,
     RowCode VARCHAR(50) 
   ) 
   ;
     
   DROP TEMPORARY TABLE IF EXISTS tmp_final_VendorRate_;
  CREATE TEMPORARY TABLE tmp_final_VendorRate_ (
     AccountId INT ,
     AccountName VARCHAR(100) ,
     Code VARCHAR(50) ,
     Rate DECIMAL(18,6) ,
     ConnectionFee DECIMAL(18,6) ,
     EffectiveDate DATETIME ,
     Description VARCHAR(255),
     Preference INT,
     RowCode VARCHAR(50),
     FinalRankNumber int
   ) 
  ;
   

DROP TEMPORARY TABLE IF EXISTS tmp_search_code_;
  CREATE TEMPORARY TABLE tmp_search_code_ (
     Code  varchar(50),
     INDEX Index1 (Code)
);


DROP TEMPORARY TABLE IF EXISTS tmp_code_;
  CREATE TEMPORARY TABLE tmp_code_ (
     RowCode  varchar(50),
     Code  varchar(50),
     RowNo int,
     INDEX Index1 (Code)
   );


DROP TEMPORARY TABLE IF EXISTS tmp_all_code_;
  CREATE TEMPORARY TABLE tmp_all_code_ ( 
     RowCode  varchar(50),
     Code  varchar(50),
     
     INDEX Index2 (Code)
   )
   ;

        
DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates_;
CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates_(
			AccountId int,
         AccountName varchar(200),
			Code varchar(50),
			Description varchar(200),
			Rate DECIMAL(18,6) ,
         ConnectionFee DECIMAL(18,6) , 
			EffectiveDate date,
			TrunkID int,
			CountryID int,
			RateID int,
			Preference int,
			INDEX IX_Code (Code)
	) 
  ;

     DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates1_;
CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates1_(
			AccountId int,
         AccountName varchar(200),
			Code varchar(50),
			Description varchar(200),
			Rate DECIMAL(18,6) ,
               ConnectionFee DECIMAL(18,6) , 
			EffectiveDate date,
			TrunkID int,
			CountryID int,
			RateID int,
			Preference int,
			INDEX IX_Code (Code),
			INDEX tmp_VendorCurrentRates_AccountId (`AccountId`,`TrunkID`,`RateId`,`EffectiveDate`)
	) 
  ;

 DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateByRank_;
  CREATE TEMPORARY TABLE tmp_VendorRateByRank_ (      
     AccountId INT ,
     AccountName VARCHAR(100) ,
     Code VARCHAR(50) ,
     Rate DECIMAL(18,6) ,
     ConnectionFee DECIMAL(18,6) ,
     EffectiveDate DATETIME ,
     Description VARCHAR(255),
     Preference INT,
     rankname INT,
     INDEX IX_Code (Code,rankname)
   )
   ;

	SELECT CurrencyId INTO v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = p_companyid;
     
     INSERT INTO tmp_VendorCurrentRates1_ 
	  Select DISTINCT AccountId,AccountName,Code,Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference 
      FROM (
				SELECT distinct tblVendorRate.AccountId,tblAccount.AccountName, tblRate.Code, tblRate.Description, 
				 CASE WHEN  tblAccount.CurrencyId = p_CurrencyID
								THEN
									   tblVendorRate.Rate
					 WHEN  v_CompanyCurrencyID_ = p_CurrencyID  
							 THEN
								  (
								   ( tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = p_companyid ) )
								 )
							  ELSE 
							  (
										
								  (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = p_CurrencyID and  CompanyID = p_companyid )
									* (tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = p_companyid ))
							  )
							 END
								as  Rate,
								 ConnectionFee,
			  DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') AS EffectiveDate, 
			    tblVendorRate.TrunkID, tblRate.CountryID, tblRate.RateID,IFNULL(vp.Preference, 5) AS Preference 
			  FROM      tblVendorRate
					 Inner join tblVendorTrunk vt on vt.CompanyID = p_companyid AND vt.AccountID = tblVendorRate.AccountID and vt.Status =  1 and vt.TrunkID =  p_trunkID
							 
						INNER JOIN tblAccount   ON  tblAccount.CompanyID = p_companyid AND tblVendorRate.AccountId = tblAccount.AccountID 
						INNER JOIN tblRate ON tblRate.CompanyID = p_companyid   AND    tblVendorRate.RateId = tblRate.RateID
						
						
						
						LEFT JOIN tblVendorPreference vp
										 ON vp.AccountId = tblVendorRate.AccountId
										 AND vp.TrunkID = tblVendorRate.TrunkID
										 AND vp.RateId = tblVendorRate.RateId
						LEFT OUTER JOIN tblVendorBlocking AS blockCode   ON tblVendorRate.RateId = blockCode.RateId
																	  AND tblVendorRate.AccountId = blockCode.AccountId
																	  AND tblVendorRate.TrunkID = blockCode.TrunkID
						LEFT OUTER JOIN tblVendorBlocking AS blockCountry    ON tblRate.CountryID = blockCountry.CountryId
																	  AND tblVendorRate.AccountId = blockCountry.AccountId
																	  AND tblVendorRate.TrunkID = blockCountry.TrunkID
			  WHERE      
						( CHAR_LENGTH(RTRIM(p_code)) = 0 OR tblRate.Code LIKE REPLACE(p_code,'*', '%') )  
						AND (p_Description='' OR tblRate.Description LIKE REPLACE(p_Description,'*','%'))
						AND (p_AccountIds='' OR FIND_IN_SET(tblAccount.AccountID,p_AccountIds) != 0 )
						AND EffectiveDate <= NOW() 
						AND tblAccount.IsVendor = 1
						AND tblAccount.Status = 1
						AND tblAccount.CurrencyId is not NULL
						AND tblVendorRate.TrunkID = p_trunkID
						AND blockCode.RateId IS NULL
					   AND blockCountry.CountryId IS NULL
			
		) tbl
		order by Code asc;
         
     
     INSERT INTO tmp_VendorCurrentRates_ 
	  Select AccountId,AccountName,Code,Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference 
      FROM (
			  SELECT * ,
				@row_num := IF(@prev_AccountId = AccountID AND @prev_TrunkID = TrunkID AND @prev_RateId = RateID AND @prev_EffectiveDate >= EffectiveDate, @row_num + 1, 1) AS RowID,
				@prev_AccountId := AccountID,
				@prev_TrunkID := TrunkID,
				@prev_RateId := RateID,
				@prev_EffectiveDate := EffectiveDate
			  FROM tmp_VendorCurrentRates1_
			  ,(SELECT @row_num := 1,  @prev_AccountId := '',@prev_TrunkID := '', @prev_RateId := '', @prev_EffectiveDate := '') x
           ORDER BY AccountId, TrunkID, RateId, EffectiveDate DESC	
		) tbl
		 WHERE RowID = 1 
		order by Code asc;

	IF p_Preference = 1 THEN
	
		INSERT IGNORE INTO tmp_VendorRateByRank_
		  SELECT
			AccountID,
			AccountName,
			Code,
			Rate,
			ConnectionFee,
			EffectiveDate,
			Description,
			Preference,
			preference_rank
		  FROM (SELECT
			  AccountID,
			  AccountName,
			  Code,
			  Rate,
			  ConnectionFee,
			  EffectiveDate,
			  Description,
			  Preference,
			  @preference_rank := CASE WHEN (@prev_Code     = Code AND @prev_Preference > Preference  ) THEN @preference_rank + 1 
									   WHEN (@prev_Code     = Code AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1 
									   WHEN (@prev_Code    = Code AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank 
									   ELSE 1 
									   END AS preference_rank,
			  @prev_Code := Code,
			  @prev_Preference := IFNULL(Preference, 5),
			  @prev_Rate := Rate
			FROM tmp_VendorCurrentRates_ AS preference,
				 (SELECT @preference_rank := 0 , @prev_Code := ''  , @prev_Preference := 5,  @prev_Rate := 0) x
			ORDER BY preference.Code ASC, preference.Preference DESC, preference.Rate ASC,preference.AccountId ASC
			   
			 ) tbl
		  WHERE preference_rank <= p_Position
		  ORDER BY Code, preference_rank;

	ELSE
	
		INSERT IGNORE INTO tmp_VendorRateByRank_
		  SELECT
			AccountID,
			AccountName,
			Code,
			Rate,
			ConnectionFee,
			EffectiveDate,
			Description,
			Preference,
			RateRank
		  FROM (SELECT
			  AccountID,
			  AccountName,
			  Code,
			  Rate,
			  ConnectionFee,
			  EffectiveDate,
			  Description,
			  Preference,
			  @rank := CASE WHEN (@prev_Code    = Code AND @prev_Rate < Rate) THEN @rank + 1 
							WHEN (@prev_Code    = Code AND @prev_Rate = Rate) THEN @rank 
							ELSE 1 
							END AS RateRank,
			  @prev_Code := Code,
			  @prev_Rate := Rate
			FROM tmp_VendorCurrentRates_ AS rank,
				 (SELECT @rank := 0 , @prev_Code := '' , @prev_Rate := 0) f
			ORDER BY rank.Code ASC, rank.Rate,rank.AccountId ASC) tbl
		  WHERE RateRank <= p_Position
		  ORDER BY Code, RateRank;

	END IF;
	
          insert ignore into tmp_VendorRate_
          select
             distinct 
				 AccountId ,
				 AccountName ,
				 Code ,
				 Rate ,
                 ConnectionFee,                                                           
				 EffectiveDate ,
				 Description ,
				 Preference,
				 Code as RowCode
		   from tmp_VendorRateByRank_ 
 			  order by RowCode desc; 
                                   
	IF( p_Preference = 0 )
	THEN
			
		insert into tmp_final_VendorRate_
		SELECT
			 AccountId ,
			 AccountName ,
			 Code ,
			 Rate ,
				ConnectionFee,
			 EffectiveDate ,
			 Description ,
			 Preference, 
			 RowCode,
			 FinalRankNumber
			from 
			( 				
				SELECT
				AccountId ,
				 AccountName ,
				 Code ,
				 Rate ,
					 ConnectionFee,
				 EffectiveDate ,
				 Description ,
				 Preference,
				 RowCode,
				 @rank := CASE WHEN ( @prev_RowCode     = RowCode AND @prev_Rate <  Rate ) THEN @rank+1
							   WHEN ( @prev_RowCode    = RowCode AND @prev_Rate = Rate ) THEN @rank 
					   ELSE 
					   1
					   END 
					  AS FinalRankNumber,
				@prev_RowCode  := RowCode,
				@prev_Rate  := Rate
				from tmp_VendorRate_ 
				,( SELECT @rank := 0 , @prev_RowCode := '' , @prev_Rate := 0 ) x 
				order by RowCode,Rate,AccountId ASC
			 
			) tbl1
			where 
			FinalRankNumber <= p_Position;
			 
	 ELSE 
	 
		 insert into tmp_final_VendorRate_
		SELECT
			AccountId ,
			 AccountName ,
			 Code ,
			 Rate ,
				ConnectionFee,
			 EffectiveDate ,
			 Description ,
			 Preference, 
			 RowCode,
			 FinalRankNumber
			from 
			( 				
				SELECT
					AccountId ,
					 AccountName ,
					 Code ,
					 Rate ,
						 ConnectionFee,
					 EffectiveDate ,
					 Description ,
					 Preference,
					 RowCode,
						  @preference_rank := CASE WHEN (@prev_Code     = RowCode AND @prev_Preference > Preference  )   THEN @preference_rank + 1 
												   WHEN (@prev_Code     = RowCode AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1 
												   WHEN (@prev_Code    = RowCode AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank 
												   ELSE 1 END AS FinalRankNumber,
						  @prev_Code := RowCode,
						  @prev_Preference := Preference,
						  @prev_Rate := Rate
				from tmp_VendorRate_ 
					  ,(SELECT @preference_rank := 0 , @prev_Code := ''  , @prev_Preference := 5,  @prev_Rate := 0) x
				 order by RowCode ASC ,Preference DESC ,Rate ASC ,AccountId ASC
					 
			) tbl1
			where 
			FinalRankNumber <= p_Position;
	 
	 END IF;
          

    IF (p_Position = 5)
    THEN      

		IF (p_isExport = 0)
		THEN    	
			
			SELECT
	  	      CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 1`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 2`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 3`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 4`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 5`
			FROM tmp_final_VendorRate_  t
			  GROUP BY  RowCode   
			  ORDER BY RowCode ASC
			LIMIT p_RowspPage OFFSET v_OffSet_ ;
	   
			SELECT count(distinct RowCode) as totalcount from tmp_final_VendorRate_ where ( CHAR_LENGTH(RTRIM(p_code)) = 0  OR RowCode LIKE REPLACE(p_code,'*', '%') );
		   
	   ELSE 
			SELECT
			  CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 1`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 2`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 3`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 4`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 5`
			FROM tmp_final_VendorRate_  t
			  GROUP BY  RowCode   
			  ORDER BY RowCode ASC;
			
	   END IF; 
   
    END IF;
    
    IF (p_Position = 10)
    THEN       

		IF (p_isExport = 0)
		THEN    	
			
			SELECT
				CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,
				GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 1`,
				GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 2`,
				GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 3`,
				GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 4`,
				GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 5`,
				GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 6, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 6`,
				GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 7, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 7`,
				GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 8, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 8`,
				GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 9, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 9`,
				GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 10,CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 10`
			FROM tmp_final_VendorRate_  t
			  GROUP BY  RowCode   
			  ORDER BY RowCode ASC
			LIMIT p_RowspPage OFFSET v_OffSet_ ;
	   
			SELECT count(distinct RowCode) as totalcount from tmp_final_VendorRate_ where    ( CHAR_LENGTH(RTRIM(p_code)) = 0  OR RowCode LIKE REPLACE(p_code,'*', '%') );
		   
		ELSE 
			SELECT
			  CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 1`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 2`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 3`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 4`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 5`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 6, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 6`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 7, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 7`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 8, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 8`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 9, CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 9`,
			  GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 10,CONCAT(  ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName) , '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 10`
			FROM tmp_final_VendorRate_  t
			  GROUP BY  RowCode   
			  ORDER BY RowCode ASC;     	
		END IF; 
   
    END IF;


SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END|
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_GetLCR`;
DELIMITER |
CREATE PROCEDURE `prc_GetLCR`(
	IN `p_companyid` INT,
	IN `p_trunkID` INT,
	IN `p_codedeckID` INT,
	IN `p_CurrencyID` INT,
	IN `p_code` VARCHAR(50),
	IN `p_Description` VARCHAR(250),
	IN `p_AccountIds` TEXT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_SortOrder` VARCHAR(50),
	IN `p_Preference` INT,
	IN `p_Position` INT,
	IN `p_isExport` INT
)
BEGIN

    
    DECLARE v_OffSet_ int;

    DECLARE v_Code VARCHAR(50) ;
	DECLARE v_pointer_ int;
	DECLARE v_rowCount_ int;
	DECLARE v_p_code VARCHAR(50);
	DECLARE v_Codlen_ int;
	DECLARE v_position int;
	DECLARE v_p_code__ VARCHAR(50);
	DECLARE v_has_null_position int ;
	DECLARE v_next_position1 VARCHAR(200) ;
	DECLARE v_CompanyCurrencyID_ INT; 	
	
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

     SET @@session.collation_connection='utf8_unicode_ci';
     SET @@session.character_set_results='utf8';

	-- just for taking codes -  
	 
    SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;  
  
DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage_;
       CREATE TEMPORARY TABLE tmp_VendorRate_stage_ (
          RowCode VARCHAR(50) ,
          AccountId INT ,
          AccountName VARCHAR(100) ,
          Code VARCHAR(50) ,
          Rate DECIMAL(18,6) ,
          ConnectionFee DECIMAL(18,6) ,
          EffectiveDate DATETIME ,
          Description VARCHAR(255),
          Preference INT,
          MaxMatchRank int ,
          prev_prev_RowCode VARCHAR(50),
          prev_AccountID int
        ) 
  ;

     DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage2_;
       CREATE TEMPORARY TABLE tmp_VendorRate_stage2_ (
          RowCode VARCHAR(50) ,
          AccountId INT ,
          AccountName VARCHAR(100) ,
          Code VARCHAR(50) ,
          Rate DECIMAL(18,6) ,
          ConnectionFee DECIMAL(18,6) ,
          EffectiveDate DATETIME ,
          Description VARCHAR(255),
          Preference INT,
          MaxMatchRank int ,
          prev_prev_RowCode VARCHAR(50),
          prev_AccountID int
        ) 
  ;

  DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
  CREATE TEMPORARY TABLE tmp_VendorRate_ (
     AccountId INT ,
     AccountName VARCHAR(100) ,
     Code VARCHAR(50) ,
     Rate DECIMAL(18,6) ,
     ConnectionFee DECIMAL(18,6) ,
     EffectiveDate DATETIME ,
     Description VARCHAR(255),
     Preference INT,
     RowCode VARCHAR(50) 
   ) 
   ;
     
   DROP TEMPORARY TABLE IF EXISTS tmp_final_VendorRate_;
  CREATE TEMPORARY TABLE tmp_final_VendorRate_ (
     AccountId INT ,
     AccountName VARCHAR(100) ,
     Code VARCHAR(50) ,
     Rate DECIMAL(18,6) ,
     ConnectionFee DECIMAL(18,6) ,
     EffectiveDate DATETIME ,
     Description VARCHAR(255),
     Preference INT,
     RowCode VARCHAR(50),
     FinalRankNumber int
   ) 
  ;
	
	-- Loop codes 

DROP TEMPORARY TABLE IF EXISTS tmp_search_code_;
  CREATE TEMPORARY TABLE tmp_search_code_ (
     Code  varchar(50),
     INDEX Index1 (Code)
);

	-- searched codes.

DROP TEMPORARY TABLE IF EXISTS tmp_code_;
  CREATE TEMPORARY TABLE tmp_code_ (
     RowCode  varchar(50),
     Code  varchar(50),
     RowNo int,
     INDEX Index1 (Code)
   );


DROP TEMPORARY TABLE IF EXISTS tmp_all_code_;
  CREATE TEMPORARY TABLE tmp_all_code_ ( 
     RowCode  varchar(50),
     Code  varchar(50),
     RowNo int,
     INDEX Index2 (Code)
   )
   ;

        
DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates_;
CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates_(
			AccountId int,
         AccountName varchar(200),
			Code varchar(50),
			Description varchar(200),
			Rate DECIMAL(18,6) ,
         ConnectionFee DECIMAL(18,6) , 
			EffectiveDate date,
			TrunkID int,
			CountryID int,
			RateID int,
			Preference int,
			INDEX IX_Code (Code)
	) 
  ;

     DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates1_;
CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates1_(
			AccountId int,
         AccountName varchar(200),
			Code varchar(50),
			Description varchar(200),
			Rate DECIMAL(18,6) ,
               ConnectionFee DECIMAL(18,6) , 
			EffectiveDate date,
			TrunkID int,
			CountryID int,
			RateID int,
			Preference int,
			INDEX IX_Code (Code),
			INDEX tmp_VendorCurrentRates_AccountId (`AccountId`,`TrunkID`,`RateId`,`EffectiveDate`)
	) 
  ;

 DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateByRank_;
  CREATE TEMPORARY TABLE tmp_VendorRateByRank_ (      
     AccountId INT ,
     AccountName VARCHAR(100) ,
     Code VARCHAR(50) ,
     Rate DECIMAL(18,6) ,
     ConnectionFee DECIMAL(18,6) ,
     EffectiveDate DATETIME ,
     Description VARCHAR(255),
     Preference INT,
     rankname INT,
     INDEX IX_Code (Code,rankname)
   )
   ;

	SELECT CurrencyId INTO v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = p_companyid;
	
    -- Search code based on p_code 
          
          insert into tmp_search_code_
          SELECT  DISTINCT LEFT(f.Code, x.RowNo) as loopCode FROM (
          SELECT @RowNo  := @RowNo + 1 as RowNo
          FROM mysql.help_category 
          ,(SELECT @RowNo := 0 ) x
          limit 15
          ) x
          INNER JOIN tblRate AS f
          ON f.CompanyID = p_companyid  AND f.CodeDeckId = p_codedeckID
          AND ( CHAR_LENGTH(RTRIM(p_code)) = 0  OR f.Code LIKE REPLACE(p_code,'*', '%') )
          AND ( p_Description = ''  OR f.Description LIKE REPLACE(p_Description,'*', '%') )
          AND x.RowNo   <= LENGTH(f.Code) 
          order by loopCode   desc;
		  
    -- distinct vendor rates	  
     
      INSERT INTO tmp_VendorCurrentRates1_ 
      Select DISTINCT AccountId,AccountName,Code,Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference 
      FROM (
				SELECT distinct tblVendorRate.AccountId,tblAccount.AccountName, tblRate.Code, tblRate.Description, 
				 CASE WHEN  tblAccount.CurrencyId = p_CurrencyID
								THEN
									   tblVendorRate.Rate
					 WHEN  v_CompanyCurrencyID_ = p_CurrencyID  
							 THEN
								  (
								   ( tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = p_companyid ) )
								 )
							  ELSE 
							  (
							    -- Convert to base currrncy and x by RateGenerator Exhange
										
								  (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = p_CurrencyID and  CompanyID = p_companyid )
									* (tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = p_companyid ))
							  )
							 END 
								as  Rate,
								 ConnectionFee,
                    		DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') AS EffectiveDate, tblVendorRate.TrunkID, tblRate.CountryID, tblRate.RateID,IFNULL(vp.Preference, 5) AS Preference 
                    		FROM      tblVendorRate
					     Inner join tblVendorTrunk vt on vt.CompanyID = p_companyid AND vt.AccountID = tblVendorRate.AccountID and vt.Status =  1 and vt.TrunkID =  p_trunkID
							
						INNER JOIN tblAccount   ON  tblAccount.CompanyID = p_companyid AND tblVendorRate.AccountId = tblAccount.AccountID and tblAccount.IsVendor = 1
						INNER JOIN tblRate ON tblRate.CompanyID = p_companyid     AND    tblVendorRate.RateId = tblRate.RateID  
						INNER JOIN tmp_search_code_  SplitCode   on tblRate.Code = SplitCode.Code 
						LEFT JOIN tblVendorPreference vp
										 ON vp.AccountId = tblVendorRate.AccountId
										 AND vp.TrunkID = tblVendorRate.TrunkID
										 AND vp.RateId = tblVendorRate.RateId
						LEFT OUTER JOIN tblVendorBlocking AS blockCode   ON tblVendorRate.RateId = blockCode.RateId
																	  AND tblVendorRate.AccountId = blockCode.AccountId
																	  AND tblVendorRate.TrunkID = blockCode.TrunkID
						LEFT OUTER JOIN tblVendorBlocking AS blockCountry    ON tblRate.CountryID = blockCountry.CountryId
																	  AND tblVendorRate.AccountId = blockCountry.AccountId
																	  AND tblVendorRate.TrunkID = blockCountry.TrunkID
			  WHERE      
						( EffectiveDate <= NOW() )
						AND (p_AccountIds='' OR FIND_IN_SET(tblAccount.AccountID,p_AccountIds) != 0 )
						AND tblAccount.IsVendor = 1
						AND tblAccount.Status = 1
						AND tblAccount.CurrencyId is not NULL
						AND tblVendorRate.TrunkID = p_trunkID
						AND blockCode.RateId IS NULL
					   AND blockCountry.CountryId IS NULL
			
		) tbl
		order by Code asc;
		
		
    -- filter by Effective Dates
         
     
     INSERT INTO tmp_VendorCurrentRates_ 
	  Select AccountId,AccountName,Code,Description, Rate,ConnectionFee,EffectiveDate,TrunkID,CountryID,RateID,Preference 
      FROM (
			  SELECT * ,
				@row_num := IF(@prev_AccountId = AccountID AND @prev_TrunkID = TrunkID AND @prev_RateId = RateID AND @prev_EffectiveDate >= EffectiveDate, @row_num + 1, 1) AS RowID,
				@prev_AccountId := AccountID,
				@prev_TrunkID := TrunkID,
				@prev_RateId := RateID,
				@prev_EffectiveDate := EffectiveDate
			  FROM tmp_VendorCurrentRates1_
			  ,(SELECT @row_num := 1,  @prev_AccountId := '',@prev_TrunkID := '', @prev_RateId := '', @prev_EffectiveDate := '') x
           ORDER BY AccountId, TrunkID, RateId, EffectiveDate DESC	
		) tbl
		 WHERE RowID = 1 
		order by Code asc;        

        -- Collect Codes pressent in vendor Rates from above query.
        /*
                   9372     9372    1
                   9372     937     2
                   9372     93      3
                   9372     9       4  
                                                               
        */
	
          
        insert into tmp_all_code_ (RowCode,Code,RowNo)
               select RowCode , loopCode,RowNo
               from (
                    select   RowCode , loopCode,
                    @RowNo := ( CASE WHEN ( @prev_Code = tbl1.RowCode  ) THEN @RowNo + 1
                                   ELSE 1
                                   END
                                
                              )      as RowNo,
                    @prev_Code := tbl1.RowCode

                    from (
                              SELECT distinct f.Code as RowCode, LEFT(f.Code, x.RowNo) as loopCode FROM (
                                SELECT @RowNo  := @RowNo + 1 as RowNo
                                FROM mysql.help_category 
                                ,(SELECT @RowNo := 0 ) x
                                limit 15
                              ) x
                              INNER JOIN tmp_search_code_ AS f
                              ON  x.RowNo   <= LENGTH(f.Code) 
											 AND ( CHAR_LENGTH(RTRIM(p_code)) = 0  OR Code LIKE REPLACE(p_code,'*', '%') )
                              order by RowCode desc,  LENGTH(loopCode) DESC 
                    ) tbl1
                    , ( Select @RowNo := 0 ) x
                ) tbl order by RowCode desc,  LENGTH(loopCode) DESC ;
          
    
        
        IF (p_isExport = 0)
        THEN
 			        
					insert into tmp_code_
                         select * from tmp_all_code_
                         order by RowCode	LIMIT p_RowspPage OFFSET v_OffSet_ ;
		
		ELSE

     					insert into tmp_code_
                              select * from tmp_all_code_
                              order by RowCode	  ;
			
		END IF;


IF p_Preference = 1 THEN

    -- Sort by Preference 
	
	INSERT IGNORE INTO tmp_VendorRateByRank_
	  SELECT
		AccountID,
		AccountName,
		Code,
		Rate,
		ConnectionFee,
		EffectiveDate,
		Description,
		Preference,
		preference_rank
	  FROM (SELECT
		  AccountID,
		  AccountName,
		  Code,
		  Rate,
		  ConnectionFee,
		  EffectiveDate,
		  Description,
		  Preference,
		  @preference_rank := CASE WHEN (@prev_Code     = Code AND @prev_Preference > Preference  ) THEN @preference_rank + 1 
								   WHEN (@prev_Code     = Code AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1 
								   WHEN (@prev_Code    = Code AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank 
								   ELSE 1 
								   END AS preference_rank,
		  @prev_Code := Code,
		  @prev_Preference := IFNULL(Preference, 5),
		  @prev_Rate := Rate
		FROM tmp_VendorCurrentRates_ AS preference,
			 (SELECT @preference_rank := 0 , @prev_Code := ''  , @prev_Preference := 5,  @prev_Rate := 0) x
		ORDER BY preference.Code ASC, preference.Preference DESC, preference.Rate ASC,preference.AccountId ASC
		   
		 ) tbl
	  WHERE preference_rank <= p_Position
	  ORDER BY Code, preference_rank;

	ELSE

    -- Sort by Rate 
	
	INSERT IGNORE INTO tmp_VendorRateByRank_
	  SELECT
		AccountID,
		AccountName,
		Code,
		Rate,
		ConnectionFee,
		EffectiveDate,
		Description,
		Preference,
		RateRank
	  FROM (SELECT
		  AccountID,
		  AccountName,
		  Code,
		  Rate,
		  ConnectionFee,
		  EffectiveDate,
		  Description,
		  Preference,
		  @rank := CASE WHEN (@prev_Code    = Code AND @prev_Rate < Rate) THEN @rank + 1 
						WHEN (@prev_Code    = Code AND @prev_Rate = Rate) THEN @rank 
						ELSE 1 
						END AS RateRank,
		  @prev_Code := Code,
		  @prev_Rate := Rate
		FROM tmp_VendorCurrentRates_ AS rank,
			 (SELECT @rank := 0 , @prev_Code := '' , @prev_Rate := 0) f
		ORDER BY rank.Code ASC, rank.Rate,rank.AccountId ASC) tbl
	  WHERE RateRank <= p_Position
	  ORDER BY Code, RateRank;

	END IF;

	-- --------- Split Logic ----------
	/* DESC             MaxMatchRank 1  MaxMatchRank 2
	923 Pakistan :       *923 V1          92 V1
	923 Pakistan :       *92 V2            -

	now take only where  MaxMatchRank =  1                                                        
	*/             

 
DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage_1;
 CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRate_stage_1 as (select * from tmp_VendorRate_stage_);

 insert ignore into tmp_VendorRate_stage_1 (
   	     RowCode,
     	AccountId ,
     	AccountName ,
     	Code ,
     	Rate ,
     	ConnectionFee,                                                           
     	EffectiveDate , 
     	Description ,
     	Preference
)
          SELECT  
          distinct                                
     	RowCode,
     	v.AccountId ,
     	v.AccountName ,
     	v.Code ,
     	v.Rate ,
     	v.ConnectionFee,                                                           
     	v.EffectiveDate , 
     	v.Description ,
     	v.Preference
          FROM tmp_VendorRateByRank_ v  
          left join  tmp_all_code_
			SplitCode   on v.Code = SplitCode.Code 
         where  SplitCode.Code is not null and rankname <= p_Position 
         order by AccountID,SplitCode.RowCode desc ,LENGTH(SplitCode.RowCode), v.Code desc, LENGTH(v.Code)  desc;
       

     insert ignore into tmp_VendorRate_stage_
          SELECT  
          distinct                                
     	RowCode,
     	v.AccountId ,
     	v.AccountName ,
     	v.Code ,
     	v.Rate ,
     	v.ConnectionFee,                                                           
     	v.EffectiveDate , 
     	v.Description ,
     	v.Preference, 
     	@rank := ( CASE WHEN( @prev_AccountID = v.AccountId  and @prev_RowCode     = RowCode   ) 
                                   THEN  @rank + 1 
                                   ELSE 1 
                              END 
          ) AS MaxMatchRank,
	     @prev_RowCode := RowCode	 ,															
          @prev_AccountID := v.AccountId   
          FROM tmp_VendorRate_stage_1 v  
          , (SELECT  @prev_RowCode := '',  @rank := 0 , @prev_Code := '' , @prev_AccountID := Null) f
          order by AccountID,RowCode desc ; 
         
    
    

              insert ignore into tmp_VendorRate_
              select
                    distinct 
				 AccountId ,
				 AccountName ,
				 Code ,
				 Rate ,
                    ConnectionFee,                                                           
				 EffectiveDate ,
				 Description ,
				 Preference,
				 RowCode
		   from tmp_VendorRate_stage_ 
           where MaxMatchRank = 1 
          
			  			  order by RowCode desc; 


     
                                   
		IF( p_Preference = 0 )
     	THEN
     			
			insert into tmp_final_VendorRate_
			SELECT
			     AccountId ,
			     AccountName ,
			     Code ,
			     Rate ,
                    ConnectionFee,
			     EffectiveDate ,
			     Description ,
			     Preference, 
			     RowCode,
	  		     FinalRankNumber
				from 
				( 				
					SELECT
					AccountId ,
				     AccountName ,
				     Code ,
				     Rate ,
                         ConnectionFee,
				     EffectiveDate ,
				     Description ,
				     Preference,
				     RowCode,
   			         @rank := CASE WHEN ( @prev_RowCode     = RowCode AND @prev_Rate <  Rate ) THEN @rank+1
                                   WHEN ( @prev_RowCode    = RowCode AND @prev_Rate = Rate ) THEN @rank 
						   ELSE 
						   1
						   END 
				  		  AS FinalRankNumber,
				  	@prev_RowCode  := RowCode,
					@prev_Rate  := Rate
					from tmp_VendorRate_ 
					,( SELECT @rank := 0 , @prev_RowCode := '' , @prev_Rate := 0 ) x 
				 	order by RowCode,Rate,AccountId ASC
				 
				) tbl1
				where 
				FinalRankNumber <= p_Position;
				 
		 ELSE 
		 
		 	 insert into tmp_final_VendorRate_
			SELECT
				AccountId ,
			     AccountName ,
			     Code ,
			     Rate ,
                    ConnectionFee,
			     EffectiveDate ,
			     Description ,
			     Preference, 
			     RowCode,
	  		     FinalRankNumber
				from 
				( 				
					SELECT
     					AccountId ,
     				     AccountName ,
     				     Code ,
     				     Rate ,
                             ConnectionFee,
     				     EffectiveDate ,
     				     Description ,
     				     Preference,
     				     RowCode,
                              @preference_rank := CASE WHEN (@prev_Code     = RowCode AND @prev_Preference > Preference  )   THEN @preference_rank + 1 
                                                       WHEN (@prev_Code     = RowCode AND @prev_Preference = Preference AND @prev_Rate < Rate) THEN @preference_rank + 1 
                                                       WHEN (@prev_Code    = RowCode AND @prev_Preference = Preference AND @prev_Rate = Rate) THEN @preference_rank 
                                                       ELSE 1 END AS FinalRankNumber,
                              @prev_Code := RowCode,
                              @prev_Preference := Preference,
                              @prev_Rate := Rate
					from tmp_VendorRate_ 
                          ,(SELECT @preference_rank := 0 , @prev_Code := ''  , @prev_Preference := 5,  @prev_Rate := 0) x
     			 	 order by RowCode ASC ,Preference DESC ,Rate ASC ,AccountId ASC
                         
				) tbl1
				where 
				FinalRankNumber <= p_Position;
		 
		 END IF;

   IF (p_Position = 5)
THEN
	IF (p_isExport = 0)
    THEN     
    	SELECT
          CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,
          GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 1`,
          GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 2`,
          GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 3`,
          GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 4`,
          GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 5`
        FROM tmp_final_VendorRate_  t
          GROUP BY  RowCode   
          ORDER BY RowCode ASC
     	LIMIT p_RowspPage OFFSET v_OffSet_ ;

        SELECT count(distinct RowCode) as totalcount from tmp_final_VendorRate_ 
		 	WHERE  ( CHAR_LENGTH(RTRIM(p_code)) = 0  OR RowCode LIKE REPLACE(p_code,'*', '%') ) ;
	END IF; 
   
   IF p_isExport = 1
    THEN
		SELECT
          CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,
          GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 1`,
          GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 2`,
          GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 3`,
          GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 4`,
          GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 5`
        FROM tmp_final_VendorRate_  t
          GROUP BY  RowCode   
          ORDER BY RowCode ASC;
	END IF;				
END IF;

IF (p_Position = 10)
THEN
	IF (p_isExport = 0)
    THEN     
    	SELECT
          CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,
          GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 1`,
          GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 2`,
          GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 3`,
          GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 4`,
          GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 5`,
          GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 6,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 6`,
          GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 7,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 7`,
          GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 8,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 8`,
          GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 9,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 9`,
          GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 10, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 10`
        FROM tmp_final_VendorRate_  t
          GROUP BY  RowCode   
          ORDER BY RowCode ASC
     	LIMIT p_RowspPage OFFSET v_OffSet_ ;

        SELECT count(distinct RowCode) as totalcount from tmp_final_VendorRate_ 
		 	WHERE  ( CHAR_LENGTH(RTRIM(p_code)) = 0  OR RowCode LIKE REPLACE(p_code,'*', '%') ) ;
	END IF; 
   
   IF p_isExport = 1
    THEN
		SELECT
          CONCAT(ANY_VALUE(t.RowCode) , ' : ' , ANY_VALUE(t.Description)) as Destination,
          GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 1,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 1`,
          GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 2,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 2`,
          GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 3,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 3`,
          GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 4,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 4`,
          GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 5,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 5`,
          GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 6,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 6`,
          GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 7,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 7`,
          GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 8,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 8`,
          GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 9,  CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 9`,
          GROUP_CONCAT(if(ANY_VALUE(FinalRankNumber) = 10, CONCAT(ANY_VALUE(t.Code), '<br>', ANY_VALUE(t.Rate), '<br>', ANY_VALUE(t.AccountName), '<br>', DATE_FORMAT (ANY_VALUE(t.EffectiveDate), '%d/%m/%Y'),'<br>'), NULL))AS `POSITION 10`
        FROM tmp_final_VendorRate_  t
          GROUP BY  RowCode   
          ORDER BY RowCode ASC;
	END IF;
END IF;

SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END|
DELIMITER ;


ALTER TABLE `tblTempVendorRate`
	ADD COLUMN `DialStringPrefix` VARCHAR(500) NULL DEFAULT NULL;

DROP PROCEDURE IF EXISTS `prc_WSProcessVendorRate`;
DELIMITER |
CREATE PROCEDURE `prc_WSProcessVendorRate`(
	IN `p_accountId` INT,
	IN `p_trunkId` INT,
	IN `p_replaceAllRates` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_processId` VARCHAR(200),
	IN `p_addNewCodesToCodeDeck` INT,
	IN `p_companyId` INT,
	IN `p_forbidden` INT,
	IN `p_preference` INT,
	IN `p_dialstringid` INT,
	IN `p_dialcodeSeparator` VARCHAR(50)
)
BEGIN

    DECLARE v_AffectedRecords_ INT DEFAULT 0;
	 DECLARE     v_CodeDeckId_ INT ;
    DECLARE totaldialstringcode INT(11) DEFAULT 0;	 
    DECLARE newstringcode INT(11) DEFAULT 0;
	 DECLARE totalduplicatecode INT(11);	 
	 DECLARE errormessage longtext;
	 DECLARE errorheader longtext;
    
	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;    
	 
	 
    DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
    CREATE TEMPORARY TABLE tmp_JobLog_ (
        Message longtext
    );
    
    
    
    DROP TEMPORARY TABLE IF EXISTS tmp_split_VendorRate_;
    CREATE TEMPORARY TABLE tmp_split_VendorRate_ (
    		`TempVendorRateID` int,
			`CodeDeckId` int ,
			`Code` varchar(50) ,
			`Description` varchar(200) ,
			`Rate` decimal(18, 6) ,
			`EffectiveDate` Datetime ,
			`Change` varchar(100) ,
			`ProcessId` varchar(200) ,
			`Preference` varchar(100) ,
			`ConnectionFee` decimal(18, 6),
			`Interval1` int,
			`IntervalN` int,
			`Forbidden` varchar(100) ,
			`DialStringPrefix` varchar(500) ,
			INDEX tmp_EffectiveDate (`EffectiveDate`),
			INDEX tmp_Code (`Code`),
            INDEX tmp_CC (`Code`,`Change`),
			INDEX tmp_Change (`Change`)
    );
    
    DROP TEMPORARY TABLE IF EXISTS tmp_TempVendorRate_;
    CREATE TEMPORARY TABLE tmp_TempVendorRate_ (
			`CodeDeckId` int ,
			`Code` varchar(50) ,
			`Description` varchar(200) ,
			`Rate` decimal(18, 6) ,
			`EffectiveDate` Datetime ,
			`Change` varchar(100) ,
			`ProcessId` varchar(200) ,
			`Preference` varchar(100) ,
			`ConnectionFee` decimal(18, 6),
			`Interval1` int,
			`IntervalN` int,
			`Forbidden` varchar(100) ,
			`DialStringPrefix` varchar(500) ,
			INDEX tmp_EffectiveDate (`EffectiveDate`),
			INDEX tmp_Code (`Code`),
            INDEX tmp_CC (`Code`,`Change`),
			INDEX tmp_Change (`Change`)
    );
    
    
    
    DROP TEMPORARY TABLE IF EXISTS tmp_Delete_VendorRate;
	CREATE TEMPORARY TABLE tmp_Delete_VendorRate (
     	VendorRateID INT,
      AccountId INT,
      TrunkID INT,
      RateId INT,
      Code VARCHAR(50),
      Description VARCHAR(200),
      Rate DECIMAL(18, 6),
      EffectiveDate DATETIME,
		Interval1 INT,
		IntervalN INT,
		ConnectionFee DECIMAL(18, 6),
		deleted_at DATETIME,
        INDEX tmp_VendorRateDiscontinued_VendorRateID (`VendorRateID`)
	);

    
    		CALL  prc_checkDialstringAndDupliacteCode(p_companyId,p_processId,p_dialstringid,p_effectiveImmediately,p_dialcodeSeparator);
    		
    		SELECT COUNT(*) AS COUNT INTO newstringcode from tmp_JobLog_; 
	
	
    IF newstringcode = 0
    THEN
   		
	         IF  p_addNewCodesToCodeDeck = 1
            THEN

					
                INSERT INTO tblRate (CompanyID,
                Code,
                Description,
                CreatedBy,
                CountryID,
                CodeDeckId,
                Interval1,
                IntervalN)
                    SELECT DISTINCT
                        p_companyId,
                        vc.Code,
                        vc.Description,
                        'WindowsService',
                       
                       fnGetCountryIdByCodeAndCountry (vc.Code ,vc.Description) AS CountryID,
                        CodeDeckId,
                        Interval1,
                        IntervalN
                    FROM
                    (
                        SELECT DISTINCT
                            tblTempVendorRate.Code,
                            tblTempVendorRate.Description,
                            tblTempVendorRate.CodeDeckId,
                            tblTempVendorRate.Interval1,
                            tblTempVendorRate.IntervalN

                        FROM tmp_TempVendorRate_  as tblTempVendorRate
                   	     LEFT JOIN tblRate
						             ON tblRate.Code = tblTempVendorRate.Code
						             AND tblRate.CompanyID = p_companyId
						             AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
	                        WHERE tblRate.RateID IS NULL
	                     	   AND tblTempVendorRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')) vc; 
                  						


               	SELECT GROUP_CONCAT(Code) into errormessage FROM(
                    SELECT DISTINCT
                        tblTempVendorRate.Code as Code, 1 as a
                    FROM tmp_TempVendorRate_  as tblTempVendorRate 
	                    INNER JOIN tblRate
					             ON tblRate.Code = tblTempVendorRate.Code
					             AND tblRate.CompanyID = p_companyId
					             AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
							  WHERE tblRate.CountryID IS NULL
	                 		   AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')) as tbl GROUP BY a;
                    
                  IF errormessage IS NOT NULL
                  THEN
                  
                    INSERT INTO tmp_JobLog_ (Message)
                    	 SELECT DISTINCT
                        CONCAT(tblTempVendorRate.Code , ' INVALID CODE - COUNTRY NOT FOUND')
                        FROM tmp_TempVendorRate_  as tblTempVendorRate 
                    INNER JOIN tblRate
				             ON tblRate.Code = tblTempVendorRate.Code
				             AND tblRate.CompanyID = p_companyId
				             AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
						  WHERE tblRate.CountryID IS NULL
                    AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');
                        
					 	END IF;		
					 
            ELSE
                
                SELECT GROUP_CONCAT(code) into errormessage FROM(
                    SELECT DISTINCT
                        c.Code as code, 1 as a
                    FROM
                    (
                        SELECT DISTINCT
                            tblTempVendorRate.Code,
                            tblTempVendorRate.Description
                        FROM tmp_TempVendorRate_  as tblTempVendorRate 
                      	  LEFT JOIN tblRate
				                ON tblRate.Code = tblTempVendorRate.Code
				          	      AND tblRate.CompanyID = p_companyId
				         	       AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
									WHERE tblRate.RateID IS NULL
	                  	      AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')) c) as tbl GROUP BY a;
                        
                  IF errormessage IS NOT NULL
                  THEN
                    INSERT INTO tmp_JobLog_ (Message)
                    		SELECT DISTINCT
                        CONCAT(tbl.Code , ' CODE DOES NOT EXIST IN CODE DECK')
                        FROM
                    (
                        SELECT DISTINCT
                            tblTempVendorRate.Code,
                            tblTempVendorRate.Description
                        FROM tmp_TempVendorRate_  as tblTempVendorRate 
                        LEFT JOIN tblRate
			                ON tblRate.Code = tblTempVendorRate.Code
			                AND tblRate.CompanyID = p_companyId
			                AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
								WHERE tblRate.RateID IS NULL
                        AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')) as tbl;
                        
					 	END IF;		


            END IF;

            IF  p_replaceAllRates = 1
            THEN


                DELETE FROM tblVendorRate
                WHERE AccountId = p_accountId
                    AND TrunkID = p_trunkId;

            END IF;
            				
				
       
		 
		          
            	INSERT INTO tmp_Delete_VendorRate(
			 	VendorRateID,
		      AccountId,
		      TrunkID,
		      RateId,
		      Code,
		      Description,
		      Rate,
		      EffectiveDate,
				Interval1,
				IntervalN,
				ConnectionFee,
				deleted_at
			 )
    		SELECT tblVendorRate.VendorRateID,
    			    p_accountId AS AccountId,
					 p_trunkId AS TrunkID,
					 tblVendorRate.RateId,
					 tblRate.Code,
					 tblRate.Description,
					 tblVendorRate.Rate,
					 tblVendorRate.EffectiveDate,
					 tblVendorRate.Interval1,
					 tblVendorRate.IntervalN,
					 tblVendorRate.ConnectionFee,
					 now() AS deleted_at  		
                FROM tblVendorRate
                JOIN tblRate
                    ON tblRate.RateID = tblVendorRate.RateId
                    AND tblRate.CompanyID = p_companyId
                    JOIN tmp_TempVendorRate_ as tblTempVendorRate
                        ON tblRate.Code = tblTempVendorRate.Code
            WHERE tblVendorRate.AccountId = p_accountId
                AND tblVendorRate.TrunkId = p_trunkId
                AND tblTempVendorRate.Change IN ('Delete', 'R', 'D', 'Blocked', 'Block');    
           
           
			  	CALL prc_InsertDiscontinuedVendorRate(p_accountId,p_trunkId); 

            UPDATE tblVendorRate
					INNER JOIN tblRate
						ON tblVendorRate.RateId = tblRate.RateId
						AND tblVendorRate.AccountId = p_accountId
						AND tblVendorRate.TrunkId = p_trunkId
					INNER JOIN tmp_TempVendorRate_ as tblTempVendorRate
						ON tblRate.Code = tblTempVendorRate.Code
						AND tblRate.CompanyID = p_companyId
						AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
						AND tblVendorRate.RateId = tblRate.RateId
					SET tblVendorRate.ConnectionFee = tblTempVendorRate.ConnectionFee,
						tblVendorRate.Interval1 = tblTempVendorRate.Interval1,
							tblVendorRate.IntervalN = tblTempVendorRate.IntervalN
					WHERE tblVendorRate.AccountId = p_accountId
			            AND tblVendorRate.TrunkId = p_trunkId ;
			    
				 
            IF  p_forbidden = 1 OR p_dialstringid > 0
				THEN
					
					INSERT INTO tblVendorBlocking
					(
						 `AccountId`
						 ,`RateId`
						 ,`TrunkID`
						 ,`BlockedBy`
					)
					SELECT distinct
					   p_accountId as AccountId,
					   tblRate.RateID as RateId,						
						p_trunkId as TrunkID,
						'RMService' as BlockedBy
					 FROM tmp_TempVendorRate_ as tblTempVendorRate
					 INNER JOIN tblRate 
						ON tblRate.Code = tblTempVendorRate.Code
						AND tblRate.CompanyID = p_companyId
			         AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
			       LEFT JOIN tblVendorBlocking vb 			       		
					 	ON vb.AccountId=p_accountId
						 AND vb.RateId = tblRate.RateID
						 AND vb.TrunkID = p_trunkId   
					WHERE tblTempVendorRate.Forbidden IN('B')
					 AND vb.VendorBlockingId is null;
					 
					 DELETE tblVendorBlocking 
					 FROM tblVendorBlocking 
					INNER JOIN(
						select VendorBlockingId 
						FROM `tblVendorBlocking` tv
							INNER JOIN(
							 SELECT 
							 	tblRate.RateId as RateId
							 FROM tmp_TempVendorRate_ as tblTempVendorRate
							INNER JOIN tblRate 
								ON tblRate.Code = tblTempVendorRate.Code
								AND tblRate.CompanyID = p_companyId
					         AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
							WHERE tblTempVendorRate.Forbidden IN('UB')
					     )tv1 on  tv.AccountId=p_accountId
						  	AND tv.TrunkID=p_trunkId
						  	AND tv.RateId = tv1.RateID
					 )vb2 on vb2.VendorBlockingId = tblVendorBlocking.VendorBlockingId;
	
				END IF;
				
				
				
				IF  p_preference = 1
				THEN
				
				INSERT INTO tblVendorPreference
					(
						 `AccountId`
						 ,`Preference`
						 ,`RateId`
						 ,`TrunkID`
						 ,`CreatedBy`
						 ,`created_at`
					)
				SELECT 
					   p_accountId AS AccountId,
					   tblTempVendorRate.Preference as Preference,
					   tblRate.RateID AS RateId,						
						p_trunkId AS TrunkID,
						'RMService' AS CreatedBy,
						NOW() AS created_at
					 FROM tmp_TempVendorRate_ as tblTempVendorRate
					INNER JOIN tblRate 
						ON tblRate.Code = tblTempVendorRate.Code
						AND tblRate.CompanyID = p_companyId
			         AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
					LEFT JOIN tblVendorPreference vp 
						ON vp.RateId=tblRate.RateID
						AND vp.AccountId = p_accountId 	
						AND vp.TrunkID = p_trunkId
					WHERE  tblTempVendorRate.Preference IS NOT NULL
					 AND  tblTempVendorRate.Preference > 0
					 AND  vp.VendorPreferenceID IS NULL;
					 
					 
					 UPDATE tblVendorPreference
					 	INNER JOIN tblRate 
					 		ON tblVendorPreference.RateId=tblRate.RateID
				      INNER JOIN tmp_TempVendorRate_ as tblTempVendorRate
							ON tblTempVendorRate.Code = tblRate.Code							
				         AND tblTempVendorRate.CodeDeckId = tblRate.CodeDeckId   
				         AND tblRate.CompanyID = p_companyId
				      SET tblVendorPreference.Preference = tblTempVendorRate.Preference
						WHERE tblVendorPreference.AccountId = p_accountId  
							AND tblVendorPreference.TrunkID = p_trunkId
							AND  tblTempVendorRate.Preference IS NOT NULL
							AND  tblTempVendorRate.Preference > 0
							AND tblVendorPreference.VendorPreferenceID IS NOT NULL; 
							
						DELETE tblVendorPreference
							from	tblVendorPreference
					 	INNER JOIN tblRate 
					 		ON tblVendorPreference.RateId=tblRate.RateID
				      INNER JOIN tmp_TempVendorRate_ as tblTempVendorRate
							ON tblTempVendorRate.Code = tblRate.Code							
				         AND tblTempVendorRate.CodeDeckId = tblRate.CodeDeckId   
				         AND tblRate.CompanyID = p_companyId
						WHERE tblVendorPreference.AccountId = p_accountId  
							AND tblVendorPreference.TrunkID = p_trunkId
							AND  tblTempVendorRate.Preference IS NOT NULL
							AND  tblTempVendorRate.Preference = '' 
							AND tblVendorPreference.VendorPreferenceID IS NOT NULL; 
					 
				END IF; 
				         

            DELETE tblTempVendorRate
                FROM tmp_TempVendorRate_ as tblTempVendorRate
                JOIN tblRate
                    ON tblRate.Code = tblTempVendorRate.Code
                    JOIN tblVendorRate
                        ON tblVendorRate.RateId = tblRate.RateId
                        AND tblRate.CompanyID = p_companyId
                        AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
                        AND tblVendorRate.AccountId = p_accountId
                        AND tblVendorRate.TrunkId = p_trunkId
                        AND tblTempVendorRate.Rate = tblVendorRate.Rate
                        AND (
                        tblVendorRate.EffectiveDate = tblTempVendorRate.EffectiveDate 
                        OR  
                        (
                        DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempVendorRate.EffectiveDate, '%Y-%m-%d')
                        )
                        OR 1 = (CASE
                            WHEN tblTempVendorRate.EffectiveDate > NOW() THEN 1 
                            ELSE 0
                        END)
                        )
            WHERE  tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block'); 

            SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

				
            UPDATE tmp_TempVendorRate_ as tblTempVendorRate
            JOIN tblRate
                ON tblRate.Code = tblTempVendorRate.Code
                AND tblRate.CompanyID = p_companyId
                AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
                JOIN tblVendorRate
                    ON tblVendorRate.RateId = tblRate.RateId
                    AND tblVendorRate.AccountId = p_accountId
                    AND tblVendorRate.TrunkId = p_trunkId
				SET tblVendorRate.Rate = tblTempVendorRate.Rate
            WHERE tblTempVendorRate.Rate <> tblVendorRate.Rate
            AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
            AND DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempVendorRate.EffectiveDate, '%Y-%m-%d');



            SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

				
            INSERT INTO tblVendorRate (AccountId,
            TrunkID,
            RateId,
            Rate,
            EffectiveDate,
            ConnectionFee,
            Interval1,
            IntervalN
            )
                SELECT DISTINCT
                    p_accountId,
                    p_trunkId,
                    tblRate.RateID,
                    tblTempVendorRate.Rate,
                    tblTempVendorRate.EffectiveDate,
                    tblTempVendorRate.ConnectionFee,
                    tblTempVendorRate.Interval1,
                    tblTempVendorRate.IntervalN
                FROM tmp_TempVendorRate_ as tblTempVendorRate
                JOIN tblRate
                    ON tblRate.Code = tblTempVendorRate.Code
                    AND tblRate.CompanyID = p_companyId
                    AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
                LEFT JOIN tblVendorRate
					      ON tblRate.RateID = tblVendorRate.RateId
					      AND tblVendorRate.AccountId = p_accountId
					      AND tblVendorRate.trunkid = p_trunkId
					      AND tblTempVendorRate.EffectiveDate = tblVendorRate.EffectiveDate
					WHERE tblVendorRate.VendorRateID IS NULL
                AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
                AND tblTempVendorRate.EffectiveDate >= DATE_FORMAT (NOW(), '%Y-%m-%d');

            SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS(); 

	 
 END IF;	 
	 
	 					 INSERT INTO tmp_JobLog_ (Message)
	 	SELECT CONCAT(v_AffectedRecords_ , ' Records Uploaded \n\r ' );
	 
 	 SELECT * FROM tmp_JobLog_;
     DELETE  FROM tblTempVendorRate WHERE  ProcessId = p_processId; 
	 
	 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END|
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_checkDialstringAndDupliacteCode`;
DELIMITER |
CREATE PROCEDURE `prc_checkDialstringAndDupliacteCode`(
	IN `p_companyId` INT,
	IN `p_processId` VARCHAR(200) ,
	IN `p_dialStringId` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_dialcodeSeparator` VARCHAR(50)
)
BEGIN
	
    DECLARE totaldialstringcode INT(11) DEFAULT 0;	 
    DECLARE     v_CodeDeckId_ INT ;
  	 DECLARE totalduplicatecode INT(11);	 
  	 DECLARE errormessage longtext;
	 DECLARE errorheader longtext;


DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateDialString_ ;			
	 CREATE TEMPORARY TABLE `tmp_VendorRateDialString_` (						
						`CodeDeckId` int ,
						`Code` varchar(50) ,
						`Description` varchar(200) ,
						`Rate` decimal(18, 6) ,
						`EffectiveDate` Datetime ,
						`Change` varchar(100) ,
						`ProcessId` varchar(200) ,
						`Preference` varchar(100) ,
						`ConnectionFee` decimal(18, 6),
						`Interval1` int,
						`IntervalN` int,
						`Forbidden` varchar(100) ,
						`DialStringPrefix` varchar(500)
					);
		
		
		
		CALL prc_SplitVendorRate(p_processId,p_dialcodeSeparator);							
		
		DROP TEMPORARY TABLE IF EXISTS tmp_split_VendorRate_2;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_split_VendorRate_2 as (SELECT * FROM tmp_split_VendorRate_);
                                             
	     DELETE n1 FROM tmp_split_VendorRate_ n1, tmp_split_VendorRate_2 n2 
     WHERE n1.EffectiveDate < n2.EffectiveDate 
	 	AND n1.CodeDeckId = n2.CodeDeckId
		AND  n1.Code = n2.Code
		AND  n1.ProcessId = n2.ProcessId
 		AND  n1.ProcessId = p_processId AND n2.ProcessId = p_processId;

		  INSERT INTO tmp_TempVendorRate_
	        SELECT DISTINCT `CodeDeckId`,
			  						`Code`,
									`Description`,
									`Rate`,
									`EffectiveDate`,
									`Change`,
									`ProcessId`,
									`Preference`,
									`ConnectionFee`,
									`Interval1`,
									`IntervalN`,
									`Forbidden`,
									`DialStringPrefix`
						 FROM tmp_split_VendorRate_
						 	 WHERE tmp_split_VendorRate_.ProcessId = p_processId;
			
		
	 	     SELECT CodeDeckId INTO v_CodeDeckId_ 
					FROM tmp_TempVendorRate_
						 WHERE ProcessId = p_processId  LIMIT 1;

            UPDATE tmp_TempVendorRate_ as tblTempVendorRate
            LEFT JOIN tblRate
                ON tblRate.Code = tblTempVendorRate.Code
                AND tblRate.CompanyID = p_companyId
                AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
                AND tblRate.CodeDeckId =  v_CodeDeckId_
            SET  
                tblTempVendorRate.Interval1 = CASE WHEN tblTempVendorRate.Interval1 is not null  and tblTempVendorRate.Interval1 > 0
                                            THEN    
                                                tblTempVendorRate.Interval1
                                            ELSE
                                            CASE WHEN tblRate.Interval1 is not null  
                                            THEN    
                                                tblRate.Interval1
                                            ELSE CASE WHEN tblTempVendorRate.Interval1 is null and (tblTempVendorRate.Description LIKE '%gambia%' OR tblTempVendorRate.Description LIKE '%mexico%')
                                                 THEN 
                                                    60
                                            ELSE CASE WHEN tblTempVendorRate.Description LIKE '%USA%' 
                                                 THEN 
                                                    6
                                                 ELSE 
                                                    1
                                                END
                                            END

                                            END
                                            END,
                tblTempVendorRate.IntervalN = CASE WHEN tblTempVendorRate.IntervalN is not null  and tblTempVendorRate.IntervalN > 0
                                            THEN    
                                                tblTempVendorRate.IntervalN
                                            ELSE
                                                CASE WHEN tblRate.IntervalN is not null 
                                          THEN
                                            tblRate.IntervalN
                                          ElSE
                                            CASE
                                                WHEN tblTempVendorRate.Description LIKE '%mexico%' THEN 60
                                            ELSE CASE
                                                WHEN tblTempVendorRate.Description LIKE '%USA%' THEN 6
                                                
                                            ELSE 
                                            1
                                            END
                                            END
                                          END
                                          END;

 			
			IF  p_effectiveImmediately = 1
            THEN
            
            	
            
                UPDATE tmp_TempVendorRate_
           	     SET EffectiveDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
           		     WHERE EffectiveDate < DATE_FORMAT (NOW(), '%Y-%m-%d');

            END IF;
			
			
			SELECT count(*) INTO totalduplicatecode FROM(
				SELECT count(code) as c,code FROM tmp_TempVendorRate_  GROUP BY Code,EffectiveDate,DialStringPrefix HAVING c>1) AS tbl;
				
			
			IF  totalduplicatecode > 0
			THEN	
			
			
				SELECT GROUP_CONCAT(code) into errormessage FROM(
					SELECT DISTINCT code, 1 as a FROM(
						SELECT   count(code) as c,code FROM tmp_TempVendorRate_  GROUP BY Code,EffectiveDate,DialStringPrefix HAVING c>1) AS tbl) as tbl2 GROUP by a;				
				
				INSERT INTO tmp_JobLog_ (Message)
				  SELECT DISTINCT 
				  CONCAT(code , ' DUPLICATE CODE')
				  	FROM(
						SELECT   count(code) as c,code FROM tmp_TempVendorRate_  GROUP BY Code,EffectiveDate,DialStringPrefix HAVING c>1) AS tbl;				
					
			END IF;
							
   IF	totalduplicatecode = 0
   THEN
						
	 
    IF p_dialstringid >0
    THEN
    		
    		DROP TEMPORARY TABLE IF EXISTS tmp_DialString_;
    		CREATE TEMPORARY TABLE tmp_DialString_ (
				`DialStringID` INT,
				`DialString` VARCHAR(250),
				`ChargeCode` VARCHAR(250),
				`Description` VARCHAR(250),
				`Forbidden` VARCHAR(50),
				INDEX tmp_DialStringID (`DialStringID`),			
	         INDEX tmp_DialStringID_ChargeCode (`DialStringID`,`ChargeCode`)
         );

         INSERT INTO tmp_DialString_ 
			SELECT DISTINCT
				`DialStringID`,
				`DialString`,
				`ChargeCode`,
				`Description`,
				`Forbidden`
			FROM tblDialStringCode
				WHERE DialStringID = p_dialstringid;
         
         SELECT  COUNT(*) as count INTO totaldialstringcode
			FROM tmp_TempVendorRate_ vr
				LEFT JOIN tmp_DialString_ ds
					ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  )) 
					-- ON vr.Code = ds.ChargeCode 
				WHERE vr.ProcessId = p_processId 
					AND ds.DialStringID IS NULL
					AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');
		   
         IF totaldialstringcode > 0
         THEN
         
				INSERT INTO tmp_JobLog_ (Message)
				  SELECT DISTINCT CONCAT(Code ,' ', vr.DialStringPrefix , ' No PREFIX FOUND')
				  	FROM tmp_TempVendorRate_ vr
						LEFT JOIN tmp_DialString_ ds
							-- ON vr.Code = ds.ChargeCode
							ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  )) 
						WHERE vr.ProcessId = p_processId
							AND ds.DialStringID IS NULL				
							AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');
         
         END IF;

         IF totaldialstringcode = 0
         THEN
				 
				 	INSERT INTO tmp_VendorRateDialString_ 	
					 SELECT DISTINCT
							`CodeDeckId`,
							`DialString`,
							CASE WHEN ds.Description IS NULL OR ds.Description = ''
								THEN    
									tblTempVendorRate.Description									
								ELSE
									ds.Description
							END
								AS Description,
							`Rate`,
							`EffectiveDate`,
							`Change`,
							`ProcessId`,
							`Preference`,
							`ConnectionFee`,
							`Interval1`,
							`IntervalN`,
							tblTempVendorRate.Forbidden as Forbidden ,
							tblTempVendorRate.DialStringPrefix as DialStringPrefix
					   FROM tmp_TempVendorRate_ as tblTempVendorRate
							INNER JOIN tmp_DialString_ ds
							  --	 ON tblTempVendorRate.Code = ds.ChargeCode
							  	ON ( (tblTempVendorRate.Code = ds.ChargeCode AND tblTempVendorRate.DialStringPrefix = '') OR (tblTempVendorRate.DialStringPrefix != '' AND tblTempVendorRate.DialStringPrefix =  ds.DialString AND tblTempVendorRate.Code = ds.ChargeCode  ))
							  -- on (tblTempVendorRate.DialStringPrefix != '' and tblTempVendorRate.DialStringPrefix =  ds.DialString and tblTempVendorRate.Code = ds.ChargeCode  )
						 WHERE tblTempVendorRate.ProcessId = p_processId
							AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');							
		
						
					DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateDialString_2;
					CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRateDialString_2 as (SELECT * FROM tmp_VendorRateDialString_);
					
					DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateDialString_3;
					CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRateDialString_3 as (					
					 SELECT vrs1.* from tmp_VendorRateDialString_2 vrs1
					 LEFT JOIN tmp_VendorRateDialString_ vrs2 ON vrs1.Code=vrs2.Code AND vrs1.CodeDeckId=vrs2.CodeDeckId AND vrs1.Description=vrs2.Description AND vrs1.EffectiveDate=vrs2.EffectiveDate AND vrs1.DialStringPrefix != vrs2.DialStringPrefix
					 WHERE ((vrs1.DialStringPrefix ='' AND vrs2.Code IS NULL) OR (vrs1.DialStringPrefix!='' AND vrs2.Code IS NOT NULL))						 
					);
					
					
					DELETE  FROM tmp_TempVendorRate_ WHERE  ProcessId = p_processId; 
					
					INSERT INTO tmp_TempVendorRate_(
							CodeDeckId,
							Code,
							Description,
							Rate,
							EffectiveDate,
							`Change`,
							ProcessId,
							Preference,
							ConnectionFee,
							Interval1,
							IntervalN,
							Forbidden,
							DialStringPrefix
						)
					SELECT DISTINCT 
						`CodeDeckId`,
						`Code`,
						`Description`,
						`Rate`,
						`EffectiveDate`,
						`Change`,
						`ProcessId`,
						`Preference`,
						`ConnectionFee`,
						`Interval1`,
						`IntervalN`,
						`Forbidden`,
						DialStringPrefix 
					 FROM tmp_VendorRateDialString_3;
					   
				  	UPDATE tmp_TempVendorRate_ as tblTempVendorRate
					JOIN tmp_DialString_ ds
					-- ON tblTempVendorRate.Code = ds.DialString
					  ON ( (tblTempVendorRate.Code = ds.ChargeCode and tblTempVendorRate.DialStringPrefix = '') OR (tblTempVendorRate.DialStringPrefix != '' and tblTempVendorRate.DialStringPrefix =  ds.DialString and tblTempVendorRate.Code = ds.ChargeCode  )) 
							AND tblTempVendorRate.ProcessId = p_processId
							AND ds.Forbidden = 1
					SET tblTempVendorRate.Forbidden = 'B';
					
					UPDATE tmp_TempVendorRate_ as  tblTempVendorRate
					JOIN tmp_DialString_ ds
						-- ON tblTempVendorRate.Code = ds.DialString
						ON ( (tblTempVendorRate.Code = ds.ChargeCode and tblTempVendorRate.DialStringPrefix = '') OR (tblTempVendorRate.DialStringPrefix != '' and tblTempVendorRate.DialStringPrefix =  ds.DialString and tblTempVendorRate.Code = ds.ChargeCode  ))
							AND tblTempVendorRate.ProcessId = p_processId
							AND ds.Forbidden = 0
					SET tblTempVendorRate.Forbidden = 'UB';
					        
			END IF;	  

    END IF;	
   
END IF; 

 	

END|
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_SplitVendorRate`;
DELIMITER |
CREATE PROCEDURE `prc_SplitVendorRate`(
	IN `p_processId` VARCHAR(200),
	IN `p_dialcodeSeparator` VARCHAR(50)
)
BEGIN

	DECLARE i INTEGER;
	DECLARE v_rowCount_ INT;
	DECLARE v_pointer_ INT;	
	DECLARE v_TempVendorRateID_ INT;
	DECLARE v_Code_ VARCHAR(500);	
	DECLARE newcodecount INT(11) DEFAULT 0;
	
	IF p_dialcodeSeparator !='null'
	THEN
	
	
	
	
	
	DROP TEMPORARY TABLE IF EXISTS `my_splits`;
	CREATE TEMPORARY TABLE `my_splits` (
		`TempVendorRateID` INT(11) NULL DEFAULT NULL,
		`Code` Text NULL DEFAULT NULL
	);
    
  SET i = 1;
  REPEAT
    INSERT INTO my_splits (TempVendorRateID, Code)
      SELECT TempVendorRateID , FnStringSplit(Code, p_dialcodeSeparator, i)  FROM tblTempVendorRate
      WHERE FnStringSplit(Code, p_dialcodeSeparator , i) IS NOT NULL
			 AND ProcessId = p_processId;
    SET i = i + 1;
    UNTIL ROW_COUNT() = 0
  END REPEAT;
  
  UPDATE my_splits SET Code = trim(Code);
  
	
  
  
  DROP TEMPORARY TABLE IF EXISTS tmp_newvendor_splite_;
	CREATE TEMPORARY TABLE tmp_newvendor_splite_  (
		RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		TempVendorRateID INT(11) NULL DEFAULT NULL,
		Code VARCHAR(500) NULL DEFAULT NULL
	);
	
	INSERT INTO tmp_newvendor_splite_(TempVendorRateID,Code)
	SELECT 
		TempVendorRateID,
		Code
	FROM my_splits
	WHERE Code like '%-%'
		AND TempVendorRateID IS NOT NULL;

  
  
	SET v_pointer_ = 1;
	SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_newvendor_splite_);
	
	WHILE v_pointer_ <= v_rowCount_
	DO
		SET v_TempVendorRateID_ = (SELECT TempVendorRateID FROM tmp_newvendor_splite_ t WHERE t.RowID = v_pointer_); 
		SET v_Code_ = (SELECT Code FROM tmp_newvendor_splite_ t WHERE t.RowID = v_pointer_);
		
		Call prc_SplitAndInsertVendorRate(v_TempVendorRateID_,v_Code_);
		
	SET v_pointer_ = v_pointer_ + 1;
	END WHILE;
	
	
	
	DELETE FROM my_splits
		WHERE Code like '%-%'
			AND TempVendorRateID IS NOT NULL;

	

	
	
	 INSERT INTO tmp_split_VendorRate_
	SELECT DISTINCT
		   my_splits.TempVendorRateID as `TempVendorRateID`,
		   `CodeDeckId`,
		   my_splits.Code as Code,
		   `Description`,
			`Rate`,
			`EffectiveDate`,
			`Change`,
			`ProcessId`,
			`Preference`,
			`ConnectionFee`,
			`Interval1`,
			`IntervalN`,
			`Forbidden`,
			`DialStringPrefix`
		 FROM my_splits
		   INNER JOIN tblTempVendorRate 
				ON my_splits.TempVendorRateID = tblTempVendorRate.TempVendorRateID
		  WHERE	tblTempVendorRate.ProcessId = p_processId;	
		  
	END IF;
	
	IF p_dialcodeSeparator = 'null'
	THEN
	
		INSERT INTO tmp_split_VendorRate_
		SELECT DISTINCT
			  `TempVendorRateID`,
			  `CodeDeckId`,
			   `Code`,
			   `Description`,
				`Rate`,
				`EffectiveDate`,
				`Change`,
				`ProcessId`,
				`Preference`,
				`ConnectionFee`,
				`Interval1`,
				`IntervalN`,
				`Forbidden`,
				`DialStringPrefix`
			 FROM tblTempVendorRate
			  WHERE ProcessId = p_processId;	
	
	END IF;	
		
END|
DELIMITER ;	