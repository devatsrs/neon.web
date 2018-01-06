use Ratemanagement3;

CREATE TABLE IF NOT EXISTS `tblReseller` (
	`ResellerID` INT(11) NOT NULL AUTO_INCREMENT,
	`ResellerName` VARCHAR(155) NOT NULL COLLATE 'utf8_unicode_ci',
	`CompanyID` INT(11) NOT NULL,
	`ChildCompanyID` INT(11) NOT NULL,
	`AccountID` INT(11) NOT NULL,
	`FirstName` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`LastName` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`Email` VARCHAR(200) NOT NULL COLLATE 'utf8_unicode_ci',
	`Password` LONGTEXT NOT NULL COLLATE 'utf8_unicode_ci',
	`Status` TINYINT(1) NOT NULL DEFAULT '1',
	`AllowWhiteLabel` TINYINT(1) NOT NULL DEFAULT '0',
	`created_at` DATETIME NULL DEFAULT NULL,
	`created_by` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	`updated_by` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	PRIMARY KEY (`ResellerID`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;

ALTER TABLE `tblCompanyConfiguration`
	DROP INDEX `Key_INDEX`,
	ADD UNIQUE INDEX `Key_INDEX` (`Key`, `CompanyID`);
	
DROP PROCEDURE IF EXISTS `prc_GetAllResourceCategoryByUser`;
DELIMITER //
CREATE PROCEDURE `prc_GetAllResourceCategoryByUser`(
	IN `p_CompanyID` INT,
	IN `p_userid` LONGTEXT
)
BEGIN

    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
     select distinct
		case
		when (rolres.Checked is not null and  usrper.AddRemove ='add') or (rolres.Checked is not null and usrper.AddRemove is null ) or	(rolres.Checked is null and  usrper.AddRemove ='add')
		then rescat.ResourceCategoryID
		end as ResourceCategoryID,
		case
		when (rolres.Checked is not null and  usrper.AddRemove ='add') or (rolres.Checked is not null and usrper.AddRemove is null ) or	(rolres.Checked is null and  usrper.AddRemove ='add')
		then rescat.ResourceCategoryName
		end as ResourceCategoryName
		from tblResourceCategories rescat
		LEFT OUTER JOIN(
			select distinct rescat.ResourceCategoryID, rescat.ResourceCategoryName,usrper.AddRemove
			from tblResourceCategories rescat
			inner join tblUserPermission usrper on usrper.resourceID = rescat.ResourceCategoryID and  FIND_IN_SET(usrper.UserID,p_userid) != 0 
			where usrper.CompanyID= p_CompanyID
			) usrper
			on usrper.ResourceCategoryID = rescat.ResourceCategoryID
			
	      LEFT OUTER JOIN(
			select distinct rescat.ResourceCategoryID, rescat.ResourceCategoryName,'true' as Checked
			from `tblResourceCategories` rescat
			inner join `tblRolePermission` rolper on rolper.resourceID = rescat.ResourceCategoryID and rolper.roleID in(SELECT RoleID FROM `tblUserRole` where FIND_IN_SET(UserID,p_userid) != 0 )
			where rolper.CompanyID= p_CompanyID
			) rolres
			on rolres.ResourceCategoryID = rescat.ResourceCategoryID
		;
		
		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_WSProcessImportAccountIP`;
DELIMITER //
CREATE PROCEDURE `prc_WSProcessImportAccountIP`(
	IN `p_processId` VARCHAR(200),
	IN `p_companyId` INT
)
BEGIN
    DECLARE v_AffectedRecords_ INT DEFAULT 0;
	DECLARE totalduplicatecode INT(11);
	DECLARE errormessage longtext;
	DECLARE errorheader longtext;
	DECLARE v_accounttype INT DEFAULT 0;
	DECLARE i INT;

	SET sql_mode = '';
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
    SET SESSION sql_mode='';

	 DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
    CREATE TEMPORARY TABLE tmp_JobLog_  (
        Message longtext
    );

	DROP TEMPORARY TABLE IF EXISTS tmp_AccountAuthenticate_;
    CREATE TEMPORARY TABLE tmp_AccountAuthenticate_  (
        CompanyID INT,
        AccountID INT,
        IsCustomerOrVendor VARCHAR(20),
        IP TEXT	
    );
	
	SET i = 1;
	REPEAT
		INSERT INTO tmp_AccountAuthenticate_ (CompanyID, AccountID, IsCustomerOrVendor, IP)
		SELECT CompanyID, AccountID, 'Customer', FnStringSplit(CustomerAuthValue, ',', i)  FROM tblAccountAuthenticate
			WHERE CompanyID = p_companyId AND  CustomerAuthRule='IP' AND FnStringSplit(CustomerAuthValue, ',' , i) IS NOT NULL;
	SET i = i + 1;
	UNTIL ROW_COUNT() = 0
	END REPEAT;
	  
	SET i = 1;
	REPEAT
		INSERT INTO tmp_AccountAuthenticate_ (CompanyID, AccountID, IsCustomerOrVendor, IP)
		SELECT CompanyID, AccountID, 'Vendor', FnStringSplit(VendorAuthValue, ',', i)  FROM tblAccountAuthenticate
			WHERE CompanyID = p_companyId AND VendorAuthRule='IP' AND FnStringSplit(VendorAuthValue, ',' , i) IS NOT NULL;
	SET i = i + 1;
	UNTIL ROW_COUNT() = 0
	END REPEAT;
    

	-- delete all ips which is duplicate (IP,AccountName,CompanyID,Type,ProcessID)
	DELETE FROM 
		tblTempAccountIP
	WHERE
		tblTempAccountIPID 
	IN(
		SELECT tblTempAccountIPID FROM (
			SELECT
				n1.tblTempAccountIPID
			FROM
				tblTempAccountIP n1, 
				tblTempAccountIP n2 
			WHERE 
				n1.tblTempAccountIPID > n2.tblTempAccountIPID AND
				n1.IP = n2.IP AND
				n1.CompanyID = n2.CompanyID AND 
				n1.AccountName = n2.AccountName AND 
				n1.`Type` = n2.`Type` AND
				n1.ProcessID = n2.ProcessID AND
				n1.ProcessID = p_processId AND
				n2.ProcessID = p_processId
			GROUP BY 
				n1.tblTempAccountIPID
			ORDER BY
				n1.IP
		) t
	);

	-- Log and delete all ips which is duplicate in different account (IP,AccountName!=,CompanyID,ProcessID)
	INSERT INTO tmp_JobLog_ (Message)
		SELECT
			CONCAT(n1.IP, ' Already Exist Against Account \n\r ' )
		FROM
			tblTempAccountIP n1,
			tblTempAccountIP n2
		WHERE 
			n1.tblTempAccountIPID > n2.tblTempAccountIPID AND
			n1.IP = n2.IP AND
			n1.CompanyID = n2.CompanyID AND 
			n1.AccountName != n2.AccountName AND 
			n1.ProcessID = n2.ProcessID AND
			n1.ProcessID = p_processId AND
			n2.ProcessID = p_processId
		GROUP BY 
			n1.tblTempAccountIPID
		ORDER BY
			n1.IP;
			
	DELETE FROM 
		tblTempAccountIP
	WHERE
		tblTempAccountIPID 
		IN(
			SELECT tblTempAccountIPID FROM (
				SELECT
					n1.tblTempAccountIPID
				FROM
					tblTempAccountIP n1,
					tblTempAccountIP n2
				WHERE 
					n1.tblTempAccountIPID > n2.tblTempAccountIPID AND
					n1.IP = n2.IP AND
					n1.CompanyID = n2.CompanyID AND 
					n1.AccountName != n2.AccountName AND
					n1.ProcessID = n2.ProcessID AND
					n1.ProcessID = p_processId AND
					n2.ProcessID = p_processId
				GROUP BY 
					n1.tblTempAccountIPID
				ORDER BY
					n1.IP
			) t
		);
		

		DELETE tblTempAccountIP
			FROM tblTempAccountIP
			INNER JOIN(
				SELECT 
					ta.tblTempAccountIPID
				FROM 
					tblTempAccountIP ta 
				LEFT JOIN 
					tblAccount a ON a.AccountName = ta.AccountName
				LEFT JOIN 
					tmp_AccountAuthenticate_ aa 
				ON
					(aa.IP=ta.IP AND a.AccountID != aa.AccountID) 
					OR
					(aa.IP=ta.IP AND ta.Type = aa.IsCustomerOrVendor) 
				WHERE ta.ProcessID = p_processId AND aa.CompanyID = p_companyId
			) aold ON aold.tblTempAccountIPID = tblTempAccountIP.tblTempAccountIPID;


 		DROP TEMPORARY TABLE IF EXISTS tmp_accountipimport;
		CREATE TEMPORARY TABLE tmp_accountipimport (
						  `CompanyID` INT,
						  `AccountID` INT,
						  `AccountName` VARCHAR(100),
						  `IP` LONGTEXT,
						  `Type` VARCHAR(50),
						  `ProcessID` VARCHAR(50),
						  `ServiceID` INT,
						  `created_at` DATETIME,
						  `created_by` VARCHAR(50)
		) ENGINE=InnoDB;

		INSERT INTO tmp_accountipimport(`CompanyID`,`AccountName`,`IP`,`Type`,`ProcessID`,`ServiceID`,`created_at`,`created_by`)
		select CompanyID,AccountName,IP,Type,ProcessID,ServiceID,created_at,created_by FROM tblTempAccountIP WHERE ProcessID = p_processId;
		
		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

		UPDATE tmp_accountipimport ta LEFT JOIN tblAccount a ON ta.AccountName=a.AccountName
				SET ta.AccountID = a.AccountID
		WHERE a.AccountID IS NOT NULL AND a.AccountType=1 AND a.CompanyId=p_companyId;

		DROP TEMPORARY TABLE IF EXISTS tmp_accountcustomerip;
			CREATE TEMPORARY TABLE tmp_accountcustomerip (
							  `CompanyID` INT,
							  `AccountID` INT,
							  `CustomerAuthRule` VARCHAR(50),
							  `CustomerAuthValue` VARCHAR(8000),
							  `ServiceID` INT
			) ENGINE=InnoDB;

		DROP TEMPORARY TABLE IF EXISTS tmp_accountvendorip;
			CREATE TEMPORARY TABLE tmp_accountvendorip (
							  `CompanyID` INT,
							  `AccountID` INT,
							  `VendorAuthRule` VARCHAR(50),
							  `VendorAuthValue` VARCHAR(500),
							  `ServiceID` INT
			) ENGINE=InnoDB;
		INSERT INTO tmp_accountcustomerip(CompanyID,AccountID,CustomerAuthRule,CustomerAuthValue,ServiceID)
		select CompanyID,AccountID,'IP' as CustomerAuthRule, GROUP_CONCAT(IP) as CustomerAuthValue,ServiceID from tmp_accountipimport where Type='Customer' GROUP BY AccountID,ServiceID;

		INSERT INTO tmp_accountvendorip(CompanyID,AccountID,VendorAuthRule,VendorAuthValue,ServiceID)
		select CompanyID,AccountID,'IP' as VendorAuthRule, GROUP_CONCAT(IP) as VendorAuthValue,ServiceID from tmp_accountipimport where Type='Vendor' GROUP BY AccountID,ServiceID;

		

		INSERT INTO tblAccountAuthenticate(CompanyID,AccountID,CustomerAuthRule,CustomerAuthValue,ServiceID)
			SELECT ac.CompanyID,ac.AccountID,ac.CustomerAuthRule,'',ac.ServiceID
				FROM tmp_accountcustomerip ac LEFT JOIN tblAccountAuthenticate aa
					ON ac.AccountID=aa.AccountID AND ac.ServiceID=aa.ServiceID
			 WHERE aa.AccountID IS NULL;
			
			INSERT INTO tblAccountAuthenticate(CompanyID,AccountID,VendorAuthRule,VendorAuthValue,ServiceID)
			SELECT av.CompanyID,av.AccountID,av.VendorAuthRule,'',av.ServiceID
				FROM tmp_accountvendorip av LEFT JOIN tblAccountAuthenticate aa
					ON av.AccountID=aa.AccountID AND av.ServiceID=aa.ServiceID
			WHERE aa.AccountID IS NULL;

		UPDATE tmp_accountcustomerip ac LEFT JOIN tblAccountAuthenticate aa ON ac.AccountID=aa.AccountID AND ac.ServiceID=aa.ServiceID
				SET	aa.CustomerAuthRule='IP',aa.CustomerAuthValue =
					CASE WHEN((aa.CustomerAuthValue IS NULL) OR (aa.CustomerAuthValue='') OR (aa.CustomerAuthRule!='IP'))
								THEN
									  ac.CustomerAuthValue
								ELSE
									  CONCAT(aa.CustomerAuthValue,',',ac.CustomerAuthValue)
								END
			WHERE ac.AccountID IS NOT NULL AND aa.AccountID IS NOT NULL;

			UPDATE tmp_accountvendorip av LEFT JOIN tblAccountAuthenticate aa ON av.AccountID=aa.AccountID AND av.ServiceID=aa.ServiceID
				SET aa.VendorAuthRule='IP',aa.VendorAuthValue =
					CASE WHEN (aa.VendorAuthValue IS NULL) OR (aa.VendorAuthValue='') OR (aa.VendorAuthRule!='IP')
								THEN
									 av.VendorAuthValue
								ELSE
									CONCAT(aa.VendorAuthValue,',',av.VendorAuthValue)
								END
			 WHERE av.AccountID IS NOT NULL AND aa.AccountID IS NOT NULL;


			INSERT INTO tmp_JobLog_ (Message)
			SELECT CONCAT(v_AffectedRecords_, ' IPs Uploaded \n\r ' );

			DELETE  FROM tblTempAccountIP WHERE ProcessID = p_processId;

		SELECT * from tmp_JobLog_;

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
			AND ((p_isReseller = 0 OR tblAccount.IsReseller = 1))
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
			AND ((p_isReseller = 0 OR tblAccount.IsReseller = 1))
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

DROP PROCEDURE IF EXISTS `prc_UpdateAccountsStatus`;
DELIMITER //
CREATE PROCEDURE `prc_UpdateAccountsStatus`(
	IN `p_CompanyID` int,
	IN `p_userID` int ,
	IN `p_IsVendor` int ,
	IN `p_isCustomer` int ,
	IN `p_isReseller` INT,
	IN `p_VerificationStatus` int,
	IN `p_AccountNo` VARCHAR(100),
	IN `p_ContactName` VARCHAR(50),
	IN `p_AccountName` VARCHAR(50),
	IN `p_tags` VARCHAR(50),
	IN `p_low_balance` INT,
	IN `P_status` INT
)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	UPDATE   tblAccount ta
	LEFT JOIN tblContact tc 
		ON tc.Owner=ta.AccountID
	LEFT JOIN tblAccountBalance abc
		ON abc.AccountID = ta.AccountID
	SET ta.Status = P_status
	WHERE ta.CompanyID = p_CompanyID
		AND ta.AccountType = 1
		AND ta.VerificationStatus = p_VerificationStatus
		AND (p_userID = 0 OR ta.Owner = p_userID)
		AND ((p_IsVendor = 0 OR ta.IsVendor = 1))
		AND ((p_isCustomer = 0 OR ta.IsCustomer = 1))
		AND ((p_isReseller = 0 OR ta.IsReseller = 1))		
		AND ((p_AccountNo = '' OR ta.Number like p_AccountNo))
		AND ((p_AccountName = '' OR ta.AccountName like Concat('%',p_AccountName,'%')))
		AND ((p_tags = '' OR ta.tags like Concat(p_tags,'%')))
		AND ((p_ContactName = '' OR (CONCAT(IFNULL(tc.FirstName,'') ,' ', IFNULL(tc.LastName,''))) like Concat('%',p_ContactName,'%')))
		AND (p_low_balance = 0 OR ( p_low_balance = 1 AND abc.PermanentCredit > 0 AND (CASE WHEN abc.BalanceThreshold LIKE '%p' THEN REPLACE(abc.BalanceThreshold, 'p', '')/ 100 * abc.PermanentCredit ELSE abc.BalanceThreshold END) < abc.BalanceAmount) );

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;

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
					
							INSERT INTO	tmp_currency(ResellerCompanyID,CompanyId,Code,CurrencyID)	
							SELECT v_resellerId_ as ResellerCompanyID,p_companyid as CompanyId,Code, CurrencyId FROM `tblCurrency` WHERE CompanyId	= p_companyid;	
							
							UPDATE tmp_currency tc LEFT JOIN tblCurrency c ON tc.Code=c.Code AND tc.ResellerCompanyID = v_resellerId_ AND c.CompanyId = v_resellerId_
									set NewCurrencyID = c.CurrencyId
							WHERE c.CurrencyId IS NOT NULL;		
					
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

					IF p_is_trunk = 1
					THEN
					
					INSERT INTO tmp_Trunk(ResellerCompanyID,Trunk,CompanyId,RatePrefix,AreaPrefix,`Prefix`,Status)
							SELECT DISTINCT v_resellerId_ as ResellerCompanyID,Trunk,CompanyId,RatePrefix,AreaPrefix,`Prefix`,Status
								FROM tblTrunk
							WHERE CompanyId = p_companyid AND FIND_IN_SET(TrunkID,p_trunk);
							
					END IF;		
					
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
				SELECT DISTINCT tb.ResellerCompanyID as `CompanyID`,tb.Name,tb.Description,tb.InvoiceLineDescription,tb.ActivationFee,(SELECT NewCurrencyID FROM tmp_currency tc WHERE tc.CurrencyID= tb.CurrencyID AND tc.ResellerCompanyID = tb.ResellerCompanyID) as CurrencyID,tb.AnnuallyFee,tb.QuarterlyFee,tb.MonthlyFee,tb.WeeklyFee,tb.DailyFee,tb.Advance,Now(),Now(),'system' as ModifiedBy,'system' as CreatedBy 
					FROM tmp_BillingSubscription tb 
						LEFT JOIN RMBilling3.tblBillingSubscription b
						ON tb.ResellerCompanyID = b.CompanyID
						AND tb.Name = b.Name
				WHERE b.SubscriptionID IS NULL;
		
		END IF;
		
		IF p_is_trunk =1
		THEN

				INSERT INTO tblTrunk (Trunk,CompanyId,RatePrefix,AreaPrefix,`Prefix`,Status,created_at,updated_at)
				SELECT DISTINCT tt.Trunk, tt.ResellerCompanyID as `CompanyId`,tt.RatePrefix,tt.AreaPrefix,tt.`Prefix`,tt.Status,Now(),Now()
				FROM tmp_Trunk tt
					LEFT JOIN tblTrunk tr ON tt.ResellerCompanyID = tr.CompanyId AND tt.Trunk = tr.Trunk
				WHERE tr.TrunkID IS NULL;
		
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
	
	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		
		GET DIAGNOSTICS CONDITION 1
		@p2 = MESSAGE_TEXT;
	
		SELECT @p2 as Message;		
	END;		

	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
	
	    DROP TEMPORARY TABLE IF EXISTS tmp_currency;
				CREATE TEMPORARY TABLE tmp_currency (
					`CompanyId` INT,
					`Code` VARCHAR(50),
					`CurrencyID` INT,
					`NewCurrencyID` INT
				) ENGINE=InnoDB;	
	
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


		INSERT INTO tblCurrency (CompanyId,Code,Description,Status,created_at,updated_at,Symbol)
		SELECT DISTINCT p_childcompanyid as `CompanyId` ,Code,Description,Status,NOW(),NOW(),Symbol
		FROM tblCurrency
		WHERE CompanyId = p_companyid;
		
		IF p_is_product =1
		THEN	

		INSERT INTO RMBilling3.tblProduct (CompanyId,Name,Code,Description,Amount,Active,Note,CreatedBy,ModifiedBy,created_at,updated_at)
		SELECT DISTINCT p_childcompanyid as `CompanyId`,Name,Code,Description,Amount,Active,Note,'system' as CreatedBy,'system' as ModifiedBy,NOW(),NOW()
		FROM RMBilling3.tblProduct
		WHERE CompanyId = p_companyid AND FIND_IN_SET(ProductID,p_product);
		
		END IF;
		
		IF p_is_subscription = 1
		THEN
		
		INSERT INTO	tmp_currency(CompanyId,Code,CurrencyID)	
		SELECT p_companyid as CompanyId,Code, CurrencyId FROM `tblCurrency` WHERE CompanyId	= p_companyid;	
			
		UPDATE tmp_currency tc LEFT JOIN tblCurrency c ON tc.Code=c.Code AND c.CompanyId = p_childcompanyid
				set NewCurrencyID = c.CurrencyId
		WHERE c.CurrencyId IS NOT NULL;
		
		INSERT INTO RMBilling3.tblBillingSubscription(`CompanyID`,Name,Description,InvoiceLineDescription,ActivationFee,created_at,updated_at,ModifiedBy,CreatedBy,CurrencyID,AnnuallyFee,QuarterlyFee,MonthlyFee,WeeklyFee,DailyFee,Advance)
		SELECT DISTINCT p_childcompanyid as `CompanyID`,Name,Description,InvoiceLineDescription,ActivationFee,created_at,updated_at,ModifiedBy,CreatedBy,(SELECT NewCurrencyID FROM tmp_currency tc WHERE tc.CurrencyID= tblBillingSubscription.CurrencyID ) as CurrencyID,AnnuallyFee,QuarterlyFee,MonthlyFee,WeeklyFee,DailyFee,Advance
		FROM RMBilling3.tblBillingSubscription
		WHERE CompanyID = p_companyid AND FIND_IN_SET(SubscriptionID,p_subscription);
		
		END IF;
		
		IF p_is_trunk =1
		THEN

		INSERT INTO tblTrunk (Trunk,CompanyId,RatePrefix,AreaPrefix,`Prefix`,Status,created_at,updated_at)
		SELECT DISTINCT Trunk, p_childcompanyid as `CompanyId`,RatePrefix,AreaPrefix,`Prefix`,Status,NOW(),NOW()
		FROM tblTrunk
		WHERE CompanyId = p_companyid AND FIND_IN_SET(TrunkID,p_trunk);
		
		END IF;
		
		INSERT INTO tblReseller(ResellerName,CompanyID,ChildCompanyID,AccountID,FirstName,LastName,Email,Password,Status,AllowWhiteLabel,created_at,updated_at,created_by)
		SELECT p_accountname as ResellerName,p_companyid as CompanyID,p_childcompanyid as ChildCompanyID,p_accountid as AccountID,p_firstname as FirstName,p_lastname as LastName,p_email as Email,p_password as Password,'1' as Status,p_allowwhitelabel as AllowWhiteLabel,Now(),Now(),'system' as created_by;

	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;
