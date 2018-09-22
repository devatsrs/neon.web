USE `Ratemanagement3`;



RENAME TABLE `tblRateSheetDetailsArchive` TO `tblRateSheetDetailsArchive__not_in_use`;
RENAME TABLE `tblRateSheetArchive` TO `tblRateSheetArchive__not_in_use`;

-- Ticket: Account import filter #3746

-- Dumping structure for procedure NeonRMDev.prc_WSProcessImportAccount
DROP PROCEDURE IF EXISTS `prc_WSProcessImportAccount`;
DELIMITER //
CREATE PROCEDURE `prc_WSProcessImportAccount`(
	IN `p_processId` VARCHAR(200) ,
	IN `p_companyId` INT,
	IN `p_companygatewayid` INT,
	IN `p_tempaccountid` TEXT,
	IN `p_option` INT,
	IN `p_importdate` DATETIME,
	IN `p_gateway` VARCHAR(50)


)
ThisSP:BEGIN
    DECLARE v_AffectedRecords_ INT DEFAULT 0;
	DECLARE totalduplicatecode INT(11);
	DECLARE errormessage longtext;
	DECLARE errorheader longtext;
	DECLARE v_accounttype INT DEFAULT 0;

	SET sql_mode = '';
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
    SET SESSION sql_mode='';

	 DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
    CREATE TEMPORARY TABLE tmp_JobLog_  (
        Message longtext
    );

    DROP TEMPORARY TABLE IF EXISTS tmp_accountimport;
				CREATE TEMPORARY TABLE tmp_accountimport (
				  `AccountType` tinyint(3) default 0,
				  `CompanyId` INT,
				  `Title` VARCHAR(100),
				  `Owner` INT,
				  `Number` VARCHAR(50),
				  `AccountName` VARCHAR(100),
				  `NamePrefix` VARCHAR(50),
				  `FirstName` VARCHAR(50),
				  `LastName` VARCHAR(50),
				  `LeadSource` VARCHAR(50),
				  `Email` VARCHAR(100),
				  `Phone` VARCHAR(50),
				  `Address1` VARCHAR(100),
				  `Address2` VARCHAR(100),
				  `Address3` VARCHAR(100),
				  `City` VARCHAR(50),
				  `PostCode` VARCHAR(50),
				  `Country` VARCHAR(50),
				  `Status` INT,
				  `tags` VARCHAR(250),
				  `Website` VARCHAR(100),
				  `Mobile` VARCHAR(50),
				  `Fax` VARCHAR(50),
				  `Skype` VARCHAR(50),
				  `Twitter` VARCHAR(50),
				  `Employee` VARCHAR(50),
				  `Description` longtext,
				  `BillingEmail` VARCHAR(200),
				  `CurrencyId` INT,
				  `VatNumber` VARCHAR(50),
				  `created_at` datetime,
				  `created_by` VARCHAR(100),
				  `VerificationStatus` tinyint(3) default 0,
				   IsVendor INT,
				   IsCustomer INT
				) ENGINE=InnoDB;

   IF p_option = 0
   THEN

   SELECT DISTINCT(AccountType) INTO v_accounttype from tblTempAccount WHERE ProcessID=p_processId;

	DELETE n1 FROM tblTempAccount n1, tblTempAccount n2 WHERE n1.tblTempAccountID < n2.tblTempAccountID
		AND  n1.CompanyId = n2.CompanyId
		AND  n1.AccountName = n2.AccountName
		AND  n1.ProcessID = n2.ProcessID
 		AND  n1.ProcessID = p_processId and n2.ProcessID = p_processId;

		 select count(*) INTO totalduplicatecode FROM(
				SELECT count(`Number`) as n,`Number` FROM tblTempAccount where ProcessID = p_processId  GROUP BY `Number` HAVING n>1) AS tbl;


		 IF  totalduplicatecode > 0
				THEN
						SELECT GROUP_CONCAT(Number) into errormessage FROM(
							select distinct Number, 1 as a FROM(
								SELECT count(`Number`) as n,`Number` FROM tblTempAccount where ProcessID = p_processId  GROUP BY `Number` HAVING n>1) AS tbl) as tbl2 GROUP by a;

						SELECT 'DUPLICATE AccountNumber : \n\r' INTO errorheader;

						INSERT INTO tmp_JobLog_ (Message)
							 SELECT CONCAT(errorheader ,errormessage);

							 delete FROM tblTempAccount WHERE Number IN (
								  SELECT Number from(
								  	SELECT count(`Number`) as n,`Number` FROM tblTempAccount where ProcessID = p_processId  GROUP BY `Number` HAVING n>1
									  ) as tbl
								);

			END IF;

			INSERT  INTO tblAccount
				  (	AccountType ,
					CompanyId ,
					Title,
					Owner ,
					`Number`,
					AccountName,
					NamePrefix,
					FirstName,
					LastName,
					LeadStatus,
					LeadSource,
					Email,
					Phone,
					Address1,
					Address2,
					Address3,
					City,
					PostCode,
					Country,
					Status,
					tags,
					Website,
					Mobile,
					Fax,
					Skype,
					Twitter,
					Employee,
					Description,
					BillingEmail,
					CurrencyId,
					VatNumber,
					created_at,
					created_by,
					VerificationStatus,
					IsVendor,
				   IsCustomer
                   )

				   SELECT  DISTINCT
					ta.AccountType,
					ta.CompanyId,
					ta.Title,
					ta.Owner,
					ta.Number as Number,
					ta.AccountName,
					ta.NamePrefix,
					ta.FirstName,
					ta.LastName,
					ta.LeadStatus,
					ta.LeadSource,
					ta.Email,
					ta.Phone,
					ta.Address1,
					ta.Address2,
					ta.Address3,
					ta.City,
					ta.PostCode,
					ta.Country,
					ta.Status,
					ta.tags,
					ta.Website,
					ta.Mobile,
					ta.Fax,
					ta.Skype,
					ta.Twitter,
					ta.Employee,
					ta.Description,
					ta.BillingEmail,
					ta.Currency as CurrencyId,
					ta.VatNumber,
					p_importdate AS created_at,
					ta.created_by,
					2 as VerificationStatus,
					ta.IsVendor,
				   ta.IsCustomer
					from tblTempAccount ta
						left join tblAccount a on ta.AccountName = a.AccountName
						 	-- AND ta.CompanyId = a.CompanyId
							AND ta.AccountType = a.AccountType
						where ta.ProcessID = p_processId
						   AND ta.AccountType = v_accounttype
							AND a.AccountID is null
							AND ta.CompanyID = p_companyId
							;


      SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

	 INSERT INTO tmp_JobLog_ (Message)
	 SELECT CONCAT(v_AffectedRecords_, ' Records Uploaded \n\r ' );


	END IF;


	IF p_option = 1
   THEN
		DELETE tblTempAccount
			FROM tblTempAccount
			INNER JOIN(
				SELECT
					ta.tblTempAccountID
				FROM
					tblTempAccount ta
				LEFT JOIN
					tblAccount a ON ta.AccountName=a.AccountName -- AND ta.CompanyID=a.CompanyID
				WHERE ta.ProcessID = p_processId AND ta.CompanyID = p_companyId AND ta.IsCustomer=1 AND ta.AccountName=a.AccountName -- AND ta.CompanyID=a.CompanyID
			) aold ON aold.tblTempAccountID = tblTempAccount.tblTempAccountID;

		DELETE tblTempAccountSippy
			FROM tblTempAccountSippy
			INNER JOIN(
				SELECT
					tas.AccountSippyID
				FROM
					tblTempAccountSippy tas
				LEFT JOIN
					tblAccount a ON tas.AccountName=a.AccountName -- AND tas.CompanyID=a.CompanyID
				WHERE tas.ProcessID = p_processId AND tas.CompanyID = p_companyId AND tas.i_account!=0 AND tas.AccountName=a.AccountName -- AND tas.CompanyID=a.CompanyID
			) aold ON aold.AccountSippyID = tblTempAccountSippy.AccountSippyID;

   		INSERT INTO tmp_accountimport
   		(	AccountType ,
					CompanyId ,
					Title,
					Owner ,
					`Number`,
					AccountName,
					NamePrefix,
					FirstName,
					LastName,
					LeadSource,
					Email,
					Phone,
					Address1,
					Address2,
					Address3,
					City,
					PostCode,
					Country,
					Status,
					tags,
					Website,
					Mobile,
					Fax,
					Skype,
					Twitter,
					Employee,
					Description,
					BillingEmail,
					CurrencyId,
					VatNumber,
					created_at,
					created_by,
					VerificationStatus,
					IsCustomer,
					IsVendor
                   )
				select ta.AccountType ,
					ta.CompanyId ,
					ta.Title,
					ta.Owner ,
					ta.Number,
					ta.AccountName,
					ta.NamePrefix,
					ta.FirstName,
					ta.LastName,
					ta.LeadSource,
					ta.Email,
					ta.Phone,
					ta.Address1,
					ta.Address2,
					ta.Address3,
					ta.City,
					ta.PostCode,
					ta.Country,
					ta.Status,
					ta.tags,
					ta.Website,
					ta.Mobile,
					ta.Fax,
					ta.Skype,
					ta.Twitter,
					ta.Employee,
					ta.Description,
					ta.BillingEmail,
					ta.Currency as CurrencyId,
					ta.VatNumber,
					p_importdate AS created_at,
					ta.created_by,
					2 as VerificationStatus,
					ta.IsCustomer,
					ta.IsVendor
				 FROM tblTempAccount ta
				left join tblAccount a on ta.AccountName=a.AccountName
					-- AND ta.CompanyId = a.CompanyId
					AND ta.AccountType = a.AccountType
				where ta.CompanyID = p_companyId
				AND ta.ProcessID = p_processId
				AND ta.AccountType = 1
				AND a.AccountID is null
				AND (p_tempaccountid = '' OR ( p_tempaccountid != '' AND FIND_IN_SET(ta.tblTempAccountID,p_tempaccountid) ))
				AND (p_companygatewayid = 0 OR ( ta.CompanyGatewayID = p_companygatewayid))
				AND (p_gateway!='SippySFTP' OR (p_gateway='SippySFTP' AND ta.IsCustomer=1))
				group by ta.AccountName;


			SELECT GROUP_CONCAT(Number) into errormessage FROM(
			SELECT distinct ta.Number as Number,1 as an  from tblTempAccount ta
				left join tblAccount a on ta.Number = a.Number
					-- AND ta.CompanyId = a.CompanyId
					AND ta.AccountType = a.AccountType
				where ta.CompanyID = p_companyId
				AND ta.ProcessID = p_processId
				AND ta.AccountType = 1
				AND a.AccountID is not null
				AND (p_tempaccountid = '' OR ( p_tempaccountid != '' AND FIND_IN_SET(ta.tblTempAccountID,p_tempaccountid) ))
				AND (p_companygatewayid = 0 OR ( ta.CompanyGatewayID = p_companygatewayid)))tbl GROUP by an;

		  IF errormessage is not null
		  THEN

		  		SELECT 'AccountNumber Already EXISTS : \n\r' INTO errorheader;
						INSERT INTO tmp_JobLog_ (Message)
							 SELECT CONCAT(errorheader ,errormessage);

				delete FROM tmp_accountimport WHERE Number IN (
								  SELECT Number from(
								  	SELECT distinct ta.Number as Number,1 as an  from tblTempAccount ta
										left join tblAccount a on ta.Number = a.Number
											-- AND ta.CompanyId = a.CompanyId
											AND ta.AccountType = a.AccountType
										where ta.CompanyID = p_companyId
										AND ta.ProcessID = p_processId
										AND ta.AccountType = 1
										AND a.AccountID is not null
										AND (p_tempaccountid = '' OR ( p_tempaccountid != '' AND FIND_IN_SET(ta.tblTempAccountID,p_tempaccountid) ))
										AND (p_companygatewayid = 0 OR ( ta.CompanyGatewayID = p_companygatewayid))
									  ) as tbl
								);

		  END IF;

		INSERT  INTO tblAccount
				  (	AccountType ,
					CompanyId ,
					Title,
					Owner ,
					`Number`,
					AccountName,
					NamePrefix,
					FirstName,
					LastName,
					LeadSource,
					Email,
					Phone,
					Address1,
					Address2,
					Address3,
					City,
					PostCode,
					Country,
					Status,
					tags,
					Website,
					Mobile,
					Fax,
					Skype,
					Twitter,
					Employee,
					Description,
					BillingEmail,
				    CurrencyId,
					VatNumber,
					created_at,
					created_by,
					VerificationStatus,
					IsCustomer,
					IsVendor
                   )
			SELECT
					AccountType ,
					CompanyId ,
					Title,
					Owner ,
					`Number`,
					AccountName,
					NamePrefix,
					FirstName,
					LastName,
					LeadSource,
					Email,
					Phone,
					Address1,
					Address2,
					Address3,
					City,
					PostCode,
					Country,
					Status,
					tags,
					Website,
					Mobile,
					Fax,
					Skype,
					Twitter,
					Employee,
					Description,
					BillingEmail,
				    CurrencyId,
					VatNumber,
					p_importdate AS created_at,
					created_by,
					VerificationStatus,
					IsCustomer,
					IsVendor
				from tmp_accountimport;


			SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

			INSERT INTO
				tblAccountSippy
				(
					CompanyID,
					AccountID,
					i_account,
					i_vendor,
					AccountName,
					username,
					CompanyGatewayID,
					created_at,
					updated_at
				)
			SELECT
				tas.CompanyID,
				a.AccountID,
				tas.i_account,
				tas.i_vendor,
				tas.AccountName,
				tas.username,
				tas.CompanyGatewayID,
				NOW(),
				NOW()
			FROM
				tblTempAccountSippy tas
			LEFT JOIN
				tmp_accountimport tai
			ON
				tas.AccountName = tai.AccountName
			LEFT JOIN
				tblAccount a
			ON
				tas.AccountName = a.AccountName
			WHERE
				a.AccountName is null AND tas.ProcessID = p_processId;


			IF p_gateway = "SippySFTP"
			THEN

				UPDATE
					tblAccountSippy asu
				LEFT JOIN
					tblTempAccountSippy tas
				ON
					asu.AccountName=tas.AccountName -- AND asu.CompanyID=tas.CompanyID
				LEFT JOIN
					tblTempAccount ta
				ON
					tas.AccountName = ta.AccountName
				LEFT JOIN
					tblAccount a
				ON
					ta.AccountName=a.AccountName AND
					-- ta.CompanyId = a.CompanyId AND
					ta.AccountType = a.AccountType
				SET
					asu.i_vendor=tas.i_vendor,
					asu.updated_at=NOW()
				WHERE
					tas.AccountName = ta.AccountName AND tas.AccountName = a.AccountName
					AND ta.CompanyID = p_companyId
					AND ta.ProcessID = p_processId
					AND ta.AccountType = 1
					AND a.AccountID is not null
					AND (p_tempaccountid = '' OR ( p_tempaccountid != '' AND FIND_IN_SET(ta.tblTempAccountID,p_tempaccountid) ))
					AND (p_companygatewayid = 0 OR ( ta.CompanyGatewayID = p_companygatewayid))
					AND ta.IsVendor=1
					AND (a.IsVendor=0 OR a.IsVendor is null)
					AND asu.AccountName=tas.AccountName
					-- AND asu.CompanyID=tas.CompanyID
					AND tas.i_vendor > 0;

				INSERT INTO
					tblAccountSippy
					(
						CompanyID,
						AccountID,
						i_account,
						i_vendor,
						AccountName,
						username,
						CompanyGatewayID,
						created_at,
						updated_at
					)
				SELECT
					tas.CompanyID,
					a.AccountID,
					tas.i_account,
					tas.i_vendor,
					tas.AccountName,
					tas.username,
					tas.CompanyGatewayID,
					NOW(),
					NOW()
				FROM
					tblTempAccountSippy tas
				LEFT JOIN
					tblAccountSippy asu
				ON
					asu.AccountName=tas.AccountName -- AND asu.CompanyID=tas.CompanyID
				LEFT JOIN
					tblTempAccount ta
				ON
					tas.AccountName = ta.AccountName
				LEFT JOIN
					tblAccount a
				ON
					ta.AccountName=a.AccountName AND
					-- ta.CompanyId = a.CompanyId AND
					ta.AccountType = a.AccountType
				WHERE
					tas.AccountName = ta.AccountName AND tas.AccountName = a.AccountName
					AND ta.CompanyID = p_companyId
					AND ta.ProcessID = p_processId
					AND ta.AccountType = 1
					AND a.AccountID is not null
					AND (p_tempaccountid = '' OR ( p_tempaccountid != '' AND FIND_IN_SET(ta.tblTempAccountID,p_tempaccountid) ))
					AND (p_companygatewayid = 0 OR ( ta.CompanyGatewayID = p_companygatewayid))
					AND ta.IsVendor=1
					AND (a.IsVendor=0 OR a.IsVendor is null)
					AND asu.AccountSippyID is null
				group by ta.AccountName;


				UPDATE
					tblAccount a
				LEFT JOIN
					tblTempAccount ta
				ON
					ta.AccountName=a.AccountName AND
					-- ta.CompanyId = a.CompanyId AND
					ta.AccountType = a.AccountType
				SET
					a.IsVendor=1
				WHERE ta.CompanyID = p_companyId
					AND ta.ProcessID = p_processId
					AND ta.AccountType = 1
					AND a.AccountID is not null
					AND (p_tempaccountid = '' OR ( p_tempaccountid != '' AND FIND_IN_SET(ta.tblTempAccountID,p_tempaccountid) ))
					AND (p_companygatewayid = 0 OR ( ta.CompanyGatewayID = p_companygatewayid))
					AND ta.IsVendor=1
					AND (a.IsVendor=0 OR a.IsVendor is null);

				SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();


				truncate tmp_accountimport;
				INSERT INTO tmp_accountimport
				(	AccountType ,
						CompanyId ,
						Title,
						Owner ,
						`Number`,
						AccountName,
						NamePrefix,
						FirstName,
						LastName,
						LeadSource,
						Email,
						Phone,
						Address1,
						Address2,
						Address3,
						City,
						PostCode,
						Country,
						Status,
						tags,
						Website,
						Mobile,
						Fax,
						Skype,
						Twitter,
						Employee,
						Description,
						BillingEmail,
						CurrencyId,
						VatNumber,
						created_at,
						created_by,
						VerificationStatus,
						IsCustomer,
						IsVendor
	                   )
					select ta.AccountType ,
						ta.CompanyId ,
						ta.Title,
						ta.Owner ,
						ta.Number,
						ta.AccountName,
						ta.NamePrefix,
						ta.FirstName,
						ta.LastName,
						ta.LeadSource,
						ta.Email,
						ta.Phone,
						ta.Address1,
						ta.Address2,
						ta.Address3,
						ta.City,
						ta.PostCode,
						ta.Country,
						ta.Status,
						ta.tags,
						ta.Website,
						ta.Mobile,
						ta.Fax,
						ta.Skype,
						ta.Twitter,
						ta.Employee,
						ta.Description,
						ta.BillingEmail,
						ta.Currency as CurrencyId,
						ta.VatNumber,
						p_importdate AS created_at,
						ta.created_by,
						2 as VerificationStatus,
						ta.IsCustomer,
						ta.IsVendor
					 FROM tblTempAccount ta
					left join tblAccount a on ta.AccountName=a.AccountName
						-- AND ta.CompanyId = a.CompanyId
						AND ta.AccountType = a.AccountType
					where ta.CompanyID = p_companyId
					AND ta.ProcessID = p_processId
					AND ta.AccountType = 1
					AND a.AccountID is null
					AND (p_tempaccountid = '' OR ( p_tempaccountid != '' AND FIND_IN_SET(ta.tblTempAccountID,p_tempaccountid) ))
					AND (p_companygatewayid = 0 OR ( ta.CompanyGatewayID = p_companygatewayid))
					AND ta.IsVendor=1
					group by ta.AccountName;

				INSERT  INTO tblAccount
					  (	AccountType ,
						CompanyId ,
						Title,
						Owner ,
						`Number`,
						AccountName,
						NamePrefix,
						FirstName,
						LastName,
						LeadSource,
						Email,
						Phone,
						Address1,
						Address2,
						Address3,
						City,
						PostCode,
						Country,
						Status,
						tags,
						Website,
						Mobile,
						Fax,
						Skype,
						Twitter,
						Employee,
						Description,
						BillingEmail,
					    CurrencyId,
						VatNumber,
						created_at,
						created_by,
						VerificationStatus,
						IsCustomer,
						IsVendor
	                   )
				SELECT
						AccountType ,
						CompanyId ,
						Title,
						Owner ,
						`Number`,
						AccountName,
						NamePrefix,
						FirstName,
						LastName,
						LeadSource,
						Email,
						Phone,
						Address1,
						Address2,
						Address3,
						City,
						PostCode,
						Country,
						Status,
						tags,
						Website,
						Mobile,
						Fax,
						Skype,
						Twitter,
						Employee,
						Description,
						BillingEmail,
					    CurrencyId,
						VatNumber,
						p_importdate AS created_at,
						created_by,
						VerificationStatus,
						IsCustomer,
						IsVendor
					from tmp_accountimport;

				SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

				INSERT INTO
					tblAccountSippy
					(
						CompanyID,
						AccountID,
						i_account,
						i_vendor,
						AccountName,
						username,
						CompanyGatewayID,
						created_at,
						updated_at
					)
				SELECT
					tas.CompanyID,
					a.AccountID,
					tas.i_account,
					tas.i_vendor,
					tas.AccountName,
					tas.username,
					tas.CompanyGatewayID,
					NOW(),
					NOW()
				FROM
					tblTempAccountSippy tas
				LEFT JOIN
					tmp_accountimport tai
				ON
					tas.AccountName = tai.AccountName
				LEFT JOIN
					tblAccount a
				ON
					tas.AccountName = a.AccountName
				WHERE
					tas.AccountName = tai.AccountName AND tas.AccountName = a.AccountName AND tas.ProcessID = p_processId;

			END IF;



			INSERT INTO tmp_JobLog_ (Message)
			SELECT CONCAT(v_AffectedRecords_, ' Records Uploaded \n\r ' );


			-- insert all records into account sippy which are not present in account sippy.
				INSERT INTO
					tblAccountSippy
					(
						CompanyID,
						AccountID,
						i_account,
						i_vendor,
						AccountName,
						username,
						CompanyGatewayID,
						created_at,
						updated_at
					)
				SELECT
					tas.CompanyID,
					a.AccountID,
					tas.i_account,
					tas.i_vendor,
					tas.AccountName,
					tas.username,
					tas.CompanyGatewayID,
					NOW(),
					NOW()
				FROM
					tblTempAccountSippy tas
				LEFT JOIN
					tblAccountSippy ats
									ON
					ats.i_account = tas.i_account  AND ats.i_vendor = tas.i_vendor
				LEFT JOIN
					tblAccount a
				ON
					a.AccountName = tas.AccountName
				WHERE
					ats.AccountSippyID is null AND a.AccountID is not null AND tas.ProcessID = p_processId;





	END IF;

	DELETE  FROM tblTempAccount WHERE ProcessID = p_processId;
	DELETE  FROM tblTempAccountSippy WHERE ProcessID = p_processId;
 	SELECT * from tmp_JobLog_;

    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;
