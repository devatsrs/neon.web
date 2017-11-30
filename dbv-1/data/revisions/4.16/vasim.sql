use Ratemanagement3;





ALTER TABLE `tblTempAccountIP`
	ADD COLUMN `CompanyGatewayID` INT(11) NULL DEFAULT NULL AFTER `Type`,
	ADD COLUMN `i_account` INT(11) NULL DEFAULT NULL AFTER `CompanyGatewayID`,
	ADD COLUMN `i_vendor` INT(11) NULL DEFAULT NULL AFTER `i_account`,
	ADD INDEX `IX_i_account` (`i_account`),
	ADD INDEX `IX_i_vendor` (`i_vendor`),
	ADD INDEX `IX_AccountName` (`AccountName`);

	
CREATE TABLE IF NOT EXISTS `tblTempIPAccountSippy` (
	`TempIPAccountSippyID` BIGINT(20) NOT NULL AUTO_INCREMENT,
	`i_account` int(11) NOT NULL DEFAULT '0',
	`username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
	`ProcessID` varchar(255) COLLATE utf8_unicode_ci,
	PRIMARY KEY (`TempIPAccountSippyID`),
	INDEX `IX_i_account` (`i_account`),
	INDEX `IX_username` (`username`),
	INDEX `IX_ProcessID` (`ProcessID`),
);











DROP PROCEDURE IF EXISTS `prc_getMissingAccountsIPByGateway`;
DELIMITER //
CREATE PROCEDURE `prc_getMissingAccountsIPByGateway`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT,
	IN `p_ProcessID` VARCHAR(250),
	IN `p_Type` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_Export` INT
)
BEGIN
	DECLARE v_OffSet_ INT;

	SET sql_mode = '';
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	SET SESSION sql_mode='';

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	IF p_Export = 0
	THEN

		SELECT DISTINCT
			tai.tblTempAccountIPID,
			tai.AccountName AS AccountName,
			tai.IP AS IP,
			IFNULL(a.AccountID,IFNULL(tas.AccountID,'')) AS SelectedAccountID
		FROM 
			tblTempAccountIP tai
		LEFT JOIN
			tblAccount a ON tai.AccountName=a.AccountName
		LEFT JOIN
			tblAccountSippy tas 
		ON 
			tai.AccountName=tas.username AND 
			(
				(p_Type=1 AND tas.i_account=tai.i_account) OR 
				(p_Type=2 AND tas.i_vendor=tai.i_vendor)
			)
		WHERE tai.CompanyID =p_CompanyID
			AND tai.CompanyGatewayID = p_CompanyGatewayID
			AND tai.ProcessID = p_ProcessID
			AND (
				(p_Type=0) OR 
				(p_Type=1 AND tai.Type='Customer') OR 
				(p_Type=2 AND tai.Type='Vendor')
			)
		ORDER BY
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN tai.AccountName
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN tai.AccountName
		END ASC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IPDESC') THEN tai.IP
		END DESC,
		CASE
			WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'IPASC') THEN tai.IP
		END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;

		SELECT
			COUNT(tai.tblTempAccountIPID) AS totalcount
		FROM tblTempAccountIP tai
		WHERE tai.CompanyID =p_CompanyID
			AND tai.CompanyGatewayID = p_CompanyGatewayID
			AND tai.ProcessID = p_ProcessID
			AND (
				(p_Type=0) OR 
				(p_Type=1 AND tai.Type='Customer') OR 
				(p_Type=2 AND tai.Type='Vendor')
			);

	ELSE

		SELECT DISTINCT
			tai.AccountName AS AccountName,
			tai.IP AS IP
		FROM tblTempAccount tai
		WHERE tai.CompanyID =p_CompanyID
			AND tai.CompanyGatewayID = p_CompanyGatewayID
			AND tai.ProcessID = p_ProcessID
			AND (
				(p_Type=0) OR 
				(p_Type=1 AND tai.Type='Customer') OR 
				(p_Type=2 AND tai.Type='Vendor')
			);

	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;


















DROP PROCEDURE IF EXISTS `prc_WSProcessImportAccountIP`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSProcessImportAccountIP`(
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
			WHERE CustomerAuthRule='IP' AND FnStringSplit(CustomerAuthValue, ',' , i) IS NOT NULL;
	SET i = i + 1;
	UNTIL ROW_COUNT() = 0
	END REPEAT;
	  
	SET i = 1;
	REPEAT
		INSERT INTO tmp_AccountAuthenticate_ (CompanyID, AccountID, IsCustomerOrVendor, IP)
		SELECT CompanyID, AccountID, 'Vendor', FnStringSplit(VendorAuthValue, ',', i)  FROM tblAccountAuthenticate
			WHERE VendorAuthRule='IP' AND FnStringSplit(VendorAuthValue, ',' , i) IS NOT NULL;
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
DELIMITER;
