use `Ratemanagement3`;


DROP PROCEDURE IF EXISTS `prc_getCustomerCodeRate`;
DELIMITER //
CREATE PROCEDURE `prc_getCustomerCodeRate`(
	IN `p_AccountID` INT,
	IN `p_trunkID` INT,
	IN `p_RateCDR` INT,
	IN `p_RateMethod` VARCHAR(50),
	IN `p_SpecifyRate` DECIMAL(18,6),
	IN `p_RateTableID` INT
)
BEGIN
	DECLARE v_codedeckid_ INT;
	DECLARE v_ratetableid_ INT;
	DECLARE v_CompanyID_ INT;

	IF p_RateTableID > 0
	THEN

		SELECT
			CodeDeckId,
			RateTableId
		INTO
			v_codedeckid_,
			v_ratetableid_
		FROM tblRateTable
		WHERE RateTableId = p_RateTableID;

	ELSE

		SELECT
			CodeDeckId,
			RateTableID
		INTO
			v_codedeckid_,
			v_ratetableid_
		FROM tblCustomerTrunk
		WHERE tblCustomerTrunk.TrunkID = p_trunkID
		AND tblCustomerTrunk.AccountID = p_AccountID
		AND tblCustomerTrunk.Status = 1;

	END IF;



	IF p_RateCDR = 0
	THEN

		DROP TEMPORARY TABLE IF EXISTS tmp_codes_;
		CREATE TEMPORARY TABLE tmp_codes_ (
			RateID INT,
			Code VARCHAR(50),
			INDEX tmp_codes_RateID (`RateID`),
			INDEX tmp_codes_Code (`Code`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_codes2_;
		CREATE TEMPORARY TABLE tmp_codes2_ (
			RateID INT,
			Code VARCHAR(50),
			INDEX tmp_codes2_RateID (`RateID`),
			INDEX tmp_codes2_Code (`Code`)
		);

		INSERT INTO tmp_codes_
		SELECT
		DISTINCT
			tblRate.RateID,
			tblRate.Code
		FROM tblRate
		INNER JOIN tblCustomerRate
		ON tblCustomerRate.RateID = tblRate.RateID
		WHERE
			 tblRate.CodeDeckId = v_codedeckid_
		AND CustomerID = p_AccountID
		AND tblCustomerRate.TrunkID = p_trunkID
		AND tblCustomerRate.EffectiveDate <= NOW();

		INSERT INTO tmp_codes2_
		SELECT * FROM tmp_codes_;

		INSERT INTO tmp_codes_
		SELECT
			DISTINCT
			tblRate.RateID,
			tblRate.Code
		FROM tblRate
		INNER JOIN tblRateTableRate
			ON tblRateTableRate.RateID = tblRate.RateID
		LEFT JOIN  tmp_codes2_ c ON c.RateID = tblRate.RateID
		WHERE
			 tblRate.CodeDeckId = v_codedeckid_
		AND RateTableID = v_ratetableid_
		AND c.RateID IS NULL
		AND tblRateTableRate.EffectiveDate <= NOW();

	END IF;

	IF p_RateCDR = 1
	THEN

		DROP TEMPORARY TABLE IF EXISTS tmp_codes_;
		CREATE TEMPORARY TABLE tmp_codes_ (
			RateID INT,
			Code VARCHAR(50),
			Rate Decimal(18,6),
			RateN Decimal(18,6),
			ConnectionFee Decimal(18,6),
			Interval1 INT,
			IntervalN INT,
			TimezonesID INT,
			INDEX tmp_codes_RateID (`RateID`),
			INDEX tmp_codes_Code (`Code`),
			INDEX tmp_codes_TimezonesID (`TimezonesID`),
			INDEX tmp_codes_Code_TimezonesID (`Code`,`TimezonesID`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_codes2_;
		CREATE TEMPORARY TABLE tmp_codes2_ (
			RateID INT,
			Code VARCHAR(50),
			Rate Decimal(18,6),
			RateN Decimal(18,6),
			ConnectionFee Decimal(18,6),
			Interval1 INT,
			IntervalN INT,
			TimezonesID INT,
			INDEX tmp_codes2_RateID (`RateID`),
			INDEX tmp_codes2_Code (`Code`),
			INDEX tmp_codes2_TimezonesID (`TimezonesID`),
			INDEX tmp_codes2_Code_TimezonesID (`Code`,`TimezonesID`)
		);

		INSERT INTO tmp_codes_
		SELECT
		DISTINCT
			tblRate.RateID,
			tblRate.Code,
			tblCustomerRate.Rate,
			tblCustomerRate.RateN,
			tblCustomerRate.ConnectionFee,
			tblCustomerRate.Interval1,
			tblCustomerRate.IntervalN,
			tblCustomerRate.TimezonesID
		FROM tblRate
		INNER JOIN tblCustomerRate
		ON tblCustomerRate.RateID = tblRate.RateID
		WHERE
			 tblRate.CodeDeckId = v_codedeckid_
		AND CustomerID = p_AccountID
		AND tblCustomerRate.TrunkID = p_trunkID
		AND tblCustomerRate.EffectiveDate <= NOW();

		INSERT INTO tmp_codes2_
		SELECT * FROM tmp_codes_;

		INSERT INTO tmp_codes_
		SELECT
			DISTINCT
			tblRate.RateID,
			tblRate.Code,
			tblRateTableRate.Rate,
			tblRateTableRate.RateN,
			tblRateTableRate.ConnectionFee,
			tblRateTableRate.Interval1,
			tblRateTableRate.IntervalN,
			tblRateTableRate.TimezonesID
		FROM tblRate
		INNER JOIN tblRateTableRate
			ON tblRateTableRate.RateID = tblRate.RateID
		LEFT JOIN  tmp_codes2_ c ON c.RateID = tblRate.RateID
		WHERE
			 tblRate.CodeDeckId = v_codedeckid_
		AND RateTableID = v_ratetableid_
		AND c.RateID IS NULL
		AND tblRateTableRate.EffectiveDate <= NOW();


		IF p_RateMethod = 'SpecifyRate'
		THEN


			IF (SELECT COUNT(*) FROM tmp_codes_) = 0
			THEN

				SET v_CompanyID_ = (SELECT CompanyId FROM tblAccount WHERE AccountID = p_AccountID);
				INSERT INTO tmp_codes_
				SELECT
					DISTINCT
					tblRate.RateID,
					tblRate.Code,
					p_SpecifyRate,
					p_SpecifyRate,
					0,
					IFNULL(tblRate.Interval1,1),
					IFNULL(tblRate.IntervalN,1),
					NULL AS TimezonesID
				FROM tblRate
				INNER JOIN tblCodeDeck
					ON tblCodeDeck.CodeDeckId = tblRate.CodeDeckId
				WHERE tblCodeDeck.CompanyId = v_CompanyID_
				AND tblCodeDeck.DefaultCodedeck = 1 ;

			END IF;

			UPDATE tmp_codes_ SET Rate=p_SpecifyRate, RateN=p_SpecifyRate;

		END IF;

	END IF;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_getCustomerInboundRate`;
DELIMITER //
CREATE PROCEDURE `prc_getCustomerInboundRate`(
	IN `p_AccountID` INT,
	IN `p_RateCDR` INT,
	IN `p_RateMethod` VARCHAR(50),
	IN `p_SpecifyRate` DECIMAL(18,6),
	IN `p_CLD` VARCHAR(500),
	IN `p_InboundTableID` INT
)
BEGIN

	DECLARE v_inboundratetableid_ INT;
	DECLARE v_CompanyID_ INT;

	IF p_CLD != ''
	THEN

		SELECT
			RateTableID INTO v_inboundratetableid_
		FROM tblCLIRateTable
		WHERE AccountID = p_AccountID AND CLI = p_CLD;

	ELSEIF p_InboundTableID > 0
	THEN

		SET v_inboundratetableid_ = p_InboundTableID;

	ELSE

		SELECT
			InboudRateTableID INTO v_inboundratetableid_
		FROM tblAccount
		WHERE AccountID = p_AccountID;

	END IF;

	IF p_RateCDR = 1
	THEN

		DROP TEMPORARY TABLE IF EXISTS tmp_inboundcodes_;
		CREATE TEMPORARY TABLE tmp_inboundcodes_ (
			RateID INT,
			Code VARCHAR(50),
			Rate Decimal(18,6),
			RateN Decimal(18,6),
			ConnectionFee Decimal(18,6),
			Interval1 INT,
			IntervalN INT,
			TimezonesID INT,
			INDEX tmp_inboundcodes_RateID (`RateID`),
			INDEX tmp_inboundcodes_Code (`Code`),
			INDEX tmp_inboundcodes_TimezonesID (`TimezonesID`),
			INDEX tmp_inboundcodes_Code_TimezonesID (`Code`,`TimezonesID`)
		);
		INSERT INTO tmp_inboundcodes_
		SELECT
			DISTINCT
			tblRate.RateID,
			tblRate.Code,
			tblRateTableRate.Rate,
			tblRateTableRate.RateN,
			tblRateTableRate.ConnectionFee,
			tblRateTableRate.Interval1,
			tblRateTableRate.IntervalN,
			tblRateTableRate.TimezonesID
		FROM tblRate
		INNER JOIN tblRateTableRate
			ON tblRateTableRate.RateID = tblRate.RateID
		WHERE RateTableID = v_inboundratetableid_
		AND tblRateTableRate.EffectiveDate <= NOW();


		IF p_RateMethod = 'SpecifyRate'
		THEN

			IF (SELECT COUNT(*) FROM tmp_inboundcodes_) = 0
			THEN

				SET v_CompanyID_ = (SELECT CompanyId FROM tblAccount WHERE AccountID = p_AccountID);
				INSERT INTO tmp_inboundcodes_
				SELECT
					DISTINCT
					tblRate.RateID,
					tblRate.Code,
					p_SpecifyRate,
					p_SpecifyRate,
					0,
					IFNULL(tblRate.Interval1,1),
					IFNULL(tblRate.IntervalN,1),
					NULL AS TimezonesID
				FROM tblRate
				INNER JOIN tblCodeDeck
					ON tblCodeDeck.CodeDeckId = tblRate.CodeDeckId
				WHERE tblCodeDeck.CompanyId = v_CompanyID_
				AND tblCodeDeck.DefaultCodedeck = 1 ;

			END IF;

			UPDATE tmp_inboundcodes_ SET Rate=p_SpecifyRate, RateN=p_SpecifyRate;

		END IF;

	END IF;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_getVendorCodeRate`;
DELIMITER //
CREATE PROCEDURE `prc_getVendorCodeRate`(
	IN `p_AccountID` INT,
	IN `p_trunkID` INT,
	IN `p_RateCDR` INT,
	IN `p_RateMethod` VARCHAR(50),
	IN `p_SpecifyRate` DECIMAL(18,6)
)
BEGIN

	DECLARE v_CompanyID_ INT;

	IF p_RateCDR = 0
	THEN

		DROP TEMPORARY TABLE IF EXISTS tmp_vcodes_;
		CREATE TEMPORARY TABLE tmp_vcodes_ (
			RateID INT,
			Code VARCHAR(50),
			INDEX tmp_vcodes_RateID (`RateID`),
			INDEX tmp_vcodes_Code (`Code`)
		);

		INSERT INTO tmp_vcodes_
		SELECT
		DISTINCT
			tblRate.RateID,
			tblRate.Code
		FROM tblRate
		INNER JOIN tblVendorRate
		ON tblVendorRate.RateID = tblRate.RateID
		WHERE
			 tblVendorRate.AccountId = p_AccountID
		AND tblVendorRate.TrunkID = p_trunkID
		AND tblVendorRate.EffectiveDate <= NOW();

	END IF;

	IF p_RateCDR = 1
	THEN

		DROP TEMPORARY TABLE IF EXISTS tmp_vcodes_;
		CREATE TEMPORARY TABLE tmp_vcodes_ (
			RateID INT,
			Code VARCHAR(50),
			Rate Decimal(18,6),
			RateN Decimal(18,6),
			ConnectionFee Decimal(18,6),
			Interval1 INT,
			IntervalN INT,
			TimezonesID INT,
			INDEX tmp_vcodes_RateID (`RateID`),
			INDEX tmp_vcodes_Code (`Code`),
			INDEX tmp_vcodes_TimezonesID (`TimezonesID`),
			INDEX tmp_vcodes_Code_TimezonesID (`Code`,`TimezonesID`)
		);

		INSERT INTO tmp_vcodes_
		SELECT
		DISTINCT
			tblRate.RateID,
			tblRate.Code,
			tblVendorRate.Rate,
			tblVendorRate.RateN,
			tblVendorRate.ConnectionFee,
			tblVendorRate.Interval1,
			tblVendorRate.IntervalN,
			tblVendorRate.TimezonesID
		FROM tblRate
		INNER JOIN tblVendorRate
		ON tblVendorRate.RateID = tblRate.RateID
		WHERE
			 tblVendorRate.AccountId = p_AccountID
		AND tblVendorRate.TrunkID = p_trunkID
		AND tblVendorRate.EffectiveDate <= NOW()
		AND (tblVendorRate.EndDate IS NULL OR tblVendorRate.EndDate >= NOW()) ;

		IF p_RateMethod = 'SpecifyRate'
		THEN
			IF (SELECT COUNT(*) FROM tmp_vcodes_) = 0
			THEN

				SET v_CompanyID_ = (SELECT CompanyId FROM tblAccount WHERE AccountID = p_AccountID);
				INSERT INTO tmp_vcodes_
				SELECT
					DISTINCT
					tblRate.RateID,
					tblRate.Code,
					p_SpecifyRate,
					p_SpecifyRate,
					0,
					IFNULL(tblRate.Interval1,1),
					IFNULL(tblRate.IntervalN,1),
					NULL AS TimezonesID
				FROM tblRate
				INNER JOIN tblCodeDeck
					ON tblCodeDeck.CodeDeckId = tblRate.CodeDeckId
				WHERE tblCodeDeck.CompanyId = v_CompanyID_
				AND tblCodeDeck.DefaultCodedeck = 1 ;

			END IF;

			UPDATE tmp_vcodes_ SET Rate=p_SpecifyRate;

		END IF;

	END IF;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_AutoUpdateAccountNumberFusionPBX`;
DELIMITER //
CREATE PROCEDURE `prc_AutoUpdateAccountNumberFusionPBX`(
	IN `p_CompanyID` INT,
	IN `p_companygatewayid` INT
)
ThisSP:BEGIN

	DECLARE v_GatewayID_ INT(11);
	DECLARE v_NameFormat_ VARCHAR(50);
	DECLARE v_rowCount_ INT;
	DECLARE v_pointer_ INT;
	DECLARE v_AccountID_ INT;
	DECLARE v_LastAccountNo_ INT;
	DECLARE v_Count_LastAccountNo_ INT;

	DROP TEMPORARY TABLE IF EXISTS tmp_accountimport_2_;
	CREATE TEMPORARY TABLE tmp_accountimport_2_ (
	  `RowID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	  `AccountID` INT(11)
	) ENGINE=InnoDB;

	SELECT
		GatewayID, IFNULL(REPLACE(JSON_EXTRACT(Settings, '$.NameFormat'),'"',''), '') INTO v_GatewayID_, v_NameFormat_
	FROM
		tblCompanyGateway
	WHERE
		CompanyGatewayID=p_companygatewayid;

	-- if Gateway is FusionPBX and Authentication is AccountName based then Auto update Account Number
	IF(v_GatewayID_ = 11 AND v_NameFormat_ = 'NAME')
	THEN

		INSERT INTO tmp_accountimport_2_
		(
			AccountID
		)
		SELECT
			a.AccountID
		FROM
			tblTempAccount ta
		LEFT JOIN
			tblAccount a ON a.AccountName = ta.AccountName
		WHERE
			a.AccountID IS NOT NULL AND
			(
				a.Number IS NULL OR
				a.Number = ''
			)
		ORDER BY
			a.AccountID ASC;

		SET v_pointer_ = 1;
		SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_accountimport_2_);

		WHILE v_pointer_ <= v_rowCount_
		DO
			SET v_AccountID_ = (SELECT AccountID FROM tmp_accountimport_2_ t WHERE t.RowID = v_pointer_);
			SET v_LastAccountNo_ = (SELECT value FROM tblCompanySetting WHERE CompanyID = p_CompanyID AND `key` = 'LastAccountNo');

			IF(v_LastAccountNo_ = 'Invalid Key')
			THEN
            SET v_LastAccountNo_ = 1;
            UPDATE tblCompanySetting SET value=v_LastAccountNo_ WHERE CompanyID = p_CompanyID AND `key`='LastAccountNo';
        	END IF;

        	SELECT COUNT(*) INTO v_Count_LastAccountNo_ FROM tblAccount WHERE Number=cast(v_LastAccountNo_ AS CHAR);

        	WHILE v_Count_LastAccountNo_ >= 1
        	DO
            SET v_LastAccountNo_ = v_LastAccountNo_ + 1;
            SET v_Count_LastAccountNo_ = (SELECT COUNT(*) FROM tblAccount WHERE Number=cast(v_LastAccountNo_ AS CHAR));
        	END WHILE;

			UPDATE tblAccount SET Number = v_LastAccountNo_ WHERE AccountID = v_AccountID_;
			UPDATE tblCompanySetting SET value=v_LastAccountNo_ WHERE CompanyID = p_CompanyID AND `key`='LastAccountNo';

			SET v_pointer_ = v_pointer_+1;

		END WHILE;

	END IF;

END//
DELIMITER ;




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


	-- if FusionPBX
	CALL prc_AutoUpdateAccountNumberFusionPBX(p_companyId,p_companygatewayid);

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
					tblAccount a ON ta.AccountName=a.AccountName
				WHERE ta.ProcessID = p_processId AND ta.CompanyID = p_companyId AND ta.IsCustomer=1 AND ta.AccountName=a.AccountName
			) aold ON aold.tblTempAccountID = tblTempAccount.tblTempAccountID;

		DELETE tblTempAccountSippy
			FROM tblTempAccountSippy
			INNER JOIN(
				SELECT
					tas.AccountSippyID
				FROM
					tblTempAccountSippy tas
				LEFT JOIN
					tblAccount a ON tas.AccountName=a.AccountName
				WHERE tas.ProcessID = p_processId AND tas.CompanyID = p_companyId AND tas.i_account!=0 AND tas.AccountName=a.AccountName
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
					asu.AccountName=tas.AccountName
				LEFT JOIN
					tblTempAccount ta
				ON
					tas.AccountName = ta.AccountName
				LEFT JOIN
					tblAccount a
				ON
					ta.AccountName=a.AccountName AND

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
					asu.AccountName=tas.AccountName
				LEFT JOIN
					tblTempAccount ta
				ON
					tas.AccountName = ta.AccountName
				LEFT JOIN
					tblAccount a
				ON
					ta.AccountName=a.AccountName AND

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




DROP PROCEDURE IF EXISTS `prc_checkDialstringAndDupliacteCode`;
DELIMITER //
CREATE PROCEDURE `prc_checkDialstringAndDupliacteCode`(
	IN `p_companyId` INT,
	IN `p_processId` VARCHAR(200) ,
	IN `p_dialStringId` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_dialcodeSeparator` VARCHAR(50)
)
ThisSP:BEGIN

	DECLARE totaldialstringcode INT(11) DEFAULT 0;
	DECLARE     v_CodeDeckId_ INT ;
	DECLARE totalduplicatecode INT(11);
	DECLARE errormessage longtext;
	DECLARE errorheader longtext;


	DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateDialString_ ;
	CREATE TEMPORARY TABLE `tmp_VendorRateDialString_` (
		`TempVendorRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Rate` decimal(18, 6) ,
		`RateN` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Forbidden` varchar(100) ,
		`DialStringPrefix` varchar(500),
		INDEX IX_code (code),
		INDEX IX_CodeDeckId (CodeDeckId),
		INDEX IX_Description (Description),
		INDEX IX_EffectiveDate (EffectiveDate),
		INDEX IX_DialStringPrefix (DialStringPrefix)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateDialString_2 ;
	CREATE TEMPORARY TABLE `tmp_VendorRateDialString_2` (
		`TempVendorRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Rate` decimal(18, 6) ,
		`RateN` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Forbidden` varchar(100) ,
		`DialStringPrefix` varchar(500),
		INDEX IX_code (code),
		INDEX IX_CodeDeckId (CodeDeckId),
		INDEX IX_Description (Description),
		INDEX IX_EffectiveDate (EffectiveDate),
		INDEX IX_DialStringPrefix (DialStringPrefix)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_VendorRateDialString_3 ;
	CREATE TEMPORARY TABLE `tmp_VendorRateDialString_3` (
		`TempVendorRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Rate` decimal(18, 6) ,
		`RateN` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Forbidden` varchar(100) ,
		`DialStringPrefix` varchar(500),
		INDEX IX_code (code),
		INDEX IX_CodeDeckId (CodeDeckId),
		INDEX IX_Description (Description),
		INDEX IX_EffectiveDate (EffectiveDate),
		INDEX IX_DialStringPrefix (DialStringPrefix)
	);

	CALL prc_SplitVendorRate(p_processId,p_dialcodeSeparator);

	IF  p_effectiveImmediately = 1
	THEN
		UPDATE tmp_split_VendorRate_
		SET EffectiveDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EffectiveDate < DATE_FORMAT (NOW(), '%Y-%m-%d');

		UPDATE tmp_split_VendorRate_
		SET EndDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EndDate < DATE_FORMAT (NOW(), '%Y-%m-%d');

	END IF;

	DROP TEMPORARY TABLE IF EXISTS tmp_split_VendorRate_2;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_split_VendorRate_2 as (SELECT * FROM tmp_split_VendorRate_);

	DELETE n1 FROM tmp_split_VendorRate_ n1
	INNER JOIN
	(
		SELECT MAX(TempVendorRateID) AS TempVendorRateID,EffectiveDate,Code,DialStringPrefix,TimezonesID,Rate
		FROM tmp_split_VendorRate_2 WHERE ProcessId = p_processId
		GROUP BY Code,EffectiveDate,DialStringPrefix,TimezonesID,Rate
		HAVING COUNT(*)>1
	)n2
	ON n1.Code = n2.Code
	AND n2.EffectiveDate = n1.EffectiveDate
	AND ((n2.DialStringPrefix IS NULL AND n1.DialStringPrefix IS NULL) OR (n2.DialStringPrefix = n1.DialStringPrefix))
	AND n2.TimezonesID = n1.TimezonesID
	AND n2.Rate = n1.Rate
	AND n1.TempVendorRateID < n2.TempVendorRateID
	WHERE n1.ProcessId = p_processId;


	INSERT INTO tmp_TempVendorRate_
	SELECT DISTINCT
		`TempVendorRateID`,
		`CodeDeckId`,
		`TimezonesID`,
		`Code`,
		`Description`,
		`Rate`,
		`RateN`,
		`EffectiveDate`,
		`EndDate`,
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
									ELSE
										1
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
										1
									END
									END;


	IF  p_effectiveImmediately = 1
	THEN
		UPDATE tmp_TempVendorRate_
			SET EffectiveDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
			WHERE EffectiveDate < DATE_FORMAT (NOW(), '%Y-%m-%d');

		UPDATE tmp_TempVendorRate_
			SET EndDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EndDate < DATE_FORMAT (NOW(), '%Y-%m-%d');

	END IF;

	SELECT count(*) INTO totalduplicatecode FROM(
	SELECT count(code) as c,code FROM tmp_TempVendorRate_  GROUP BY Code,EffectiveDate,DialStringPrefix,TimezonesID HAVING c>1) AS tbl;


	IF  totalduplicatecode > 0
	THEN


		SELECT GROUP_CONCAT(code) into errormessage FROM(
		SELECT DISTINCT code, 1 as a FROM(
		SELECT   count(code) as c,code FROM tmp_TempVendorRate_  GROUP BY Code,EffectiveDate,DialStringPrefix,TimezonesID HAVING c>1) AS tbl) as tbl2 GROUP by a;

		INSERT INTO tmp_JobLog_ (Message)
		SELECT DISTINCT
		CONCAT(code , ' DUPLICATE CODE')
		FROM(
		SELECT   count(code) as c,code FROM tmp_TempVendorRate_  GROUP BY Code,EffectiveDate,DialStringPrefix,TimezonesID HAVING c>1) AS tbl;

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

		WHERE vr.ProcessId = p_processId
		AND ds.DialStringID IS NULL
		AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

		IF totaldialstringcode > 0
		THEN



		INSERT INTO tblDialStringCode (DialStringID,DialString,ChargeCode,created_by)
		SELECT DISTINCT p_dialStringId,vr.DialStringPrefix, Code, 'RMService'
		FROM tmp_TempVendorRate_ vr
		LEFT JOIN tmp_DialString_ ds

		ON vr.DialStringPrefix = ds.DialString AND ds.DialStringID = p_dialStringId
		WHERE vr.ProcessId = p_processId
		AND ds.DialStringID IS NULL
		AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
		AND (vr.DialStringPrefix is not null AND vr.DialStringPrefix != '')
		AND (Code is not null AND Code != '');

		TRUNCATE tmp_DialString_;
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

		WHERE vr.ProcessId = p_processId
		AND ds.DialStringID IS NULL
		AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

		INSERT INTO tmp_JobLog_ (Message)
		SELECT DISTINCT CONCAT(Code ,' ', vr.DialStringPrefix , ' No PREFIX FOUND')
		FROM tmp_TempVendorRate_ vr
		LEFT JOIN tmp_DialString_ ds

		ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  ))
		WHERE vr.ProcessId = p_processId
		AND ds.DialStringID IS NULL
		AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

		END IF;

		IF totaldialstringcode = 0
		THEN

			INSERT INTO tmp_VendorRateDialString_
				SELECT DISTINCT
				`TempVendorRateID`,
				`CodeDeckId`,
				`TimezonesID`,
				`DialString`,
				CASE WHEN ds.Description IS NULL OR ds.Description = ''
				THEN
				tblTempVendorRate.Description
				ELSE
				ds.Description
				END
				AS Description,
				`Rate`,
				`RateN`,
				`EffectiveDate`,
				`EndDate`,
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

			ON ( (tblTempVendorRate.Code = ds.ChargeCode AND tblTempVendorRate.DialStringPrefix = '') OR (tblTempVendorRate.DialStringPrefix != '' AND tblTempVendorRate.DialStringPrefix =  ds.DialString AND tblTempVendorRate.Code = ds.ChargeCode  ))

			WHERE tblTempVendorRate.ProcessId = p_processId
			AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');




			INSERT INTO tmp_VendorRateDialString_2
			SELECT *  FROM tmp_VendorRateDialString_ where DialStringPrefix!='';

			Delete From tmp_VendorRateDialString_
			Where DialStringPrefix = ''
			And Code IN (Select DialStringPrefix From tmp_VendorRateDialString_2);

			INSERT INTO tmp_VendorRateDialString_3
			SELECT * FROM tmp_VendorRateDialString_;



			DELETE  FROM tmp_TempVendorRate_ WHERE  ProcessId = p_processId;

			INSERT INTO tmp_TempVendorRate_(
				`TempVendorRateID`,
				CodeDeckId,
				TimezonesID,
				Code,
				Description,
				Rate,
				RateN,
				EffectiveDate,
				EndDate,
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
				`TempVendorRateID`,
				`CodeDeckId`,
				`TimezonesID`,
				`Code`,
				`Description`,
				`Rate`,
				`RateN`,
				`EffectiveDate`,
				`EndDate`,
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

			ON ( (tblTempVendorRate.Code = ds.ChargeCode and tblTempVendorRate.DialStringPrefix = '') OR (tblTempVendorRate.DialStringPrefix != '' and tblTempVendorRate.DialStringPrefix =  ds.DialString and tblTempVendorRate.Code = ds.ChargeCode  ))
			AND tblTempVendorRate.ProcessId = p_processId
			AND ds.Forbidden = 1
			SET tblTempVendorRate.Forbidden = 'B';

			UPDATE tmp_TempVendorRate_ as  tblTempVendorRate
			JOIN tmp_DialString_ ds

			ON ( (tblTempVendorRate.Code = ds.ChargeCode and tblTempVendorRate.DialStringPrefix = '') OR (tblTempVendorRate.DialStringPrefix != '' and tblTempVendorRate.DialStringPrefix =  ds.DialString and tblTempVendorRate.Code = ds.ChargeCode  ))
			AND tblTempVendorRate.ProcessId = p_processId
			AND ds.Forbidden = 0
			SET tblTempVendorRate.Forbidden = 'UB';

			END IF;

		END IF;

	END IF;


END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_RateTableCheckDialstringAndDupliacteCode`;
DELIMITER //
CREATE PROCEDURE `prc_RateTableCheckDialstringAndDupliacteCode`(
	IN `p_companyId` INT,
	IN `p_processId` VARCHAR(200) ,
	IN `p_dialStringId` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_dialcodeSeparator` VARCHAR(50)
)
ThisSP:BEGIN

	DECLARE totaldialstringcode INT(11) DEFAULT 0;
	DECLARE v_CodeDeckId_ INT ;
	DECLARE totalduplicatecode INT(11);
	DECLARE errormessage longtext;
	DECLARE errorheader longtext;

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRateDialString_ ;
	CREATE TEMPORARY TABLE `tmp_RateTableRateDialString_` (
		`TempRateTableRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Rate` decimal(18, 6) ,
		`RateN` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Forbidden` varchar(100) ,
		`DialStringPrefix` varchar(500),
		INDEX IX_code (code),
		INDEX IX_CodeDeckId (CodeDeckId),
		INDEX IX_Description (Description),
		INDEX IX_EffectiveDate (EffectiveDate),
		INDEX IX_DialStringPrefix (DialStringPrefix)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRateDialString_2 ;
	CREATE TEMPORARY TABLE `tmp_RateTableRateDialString_2` (
		`TempRateTableRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Rate` decimal(18, 6) ,
		`RateN` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Forbidden` varchar(100) ,
		`DialStringPrefix` varchar(500),
		INDEX IX_code (code),
		INDEX IX_CodeDeckId (CodeDeckId),
		INDEX IX_Description (Description),
		INDEX IX_EffectiveDate (EffectiveDate),
		INDEX IX_DialStringPrefix (DialStringPrefix)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableRateDialString_3 ;
	CREATE TEMPORARY TABLE `tmp_RateTableRateDialString_3` (
		`TempRateTableRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Rate` decimal(18, 6) ,
		`RateN` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`Preference` varchar(100) ,
		`ConnectionFee` decimal(18, 6),
		`Interval1` int,
		`IntervalN` int,
		`Forbidden` varchar(100) ,
		`DialStringPrefix` varchar(500),
		INDEX IX_code (code),
		INDEX IX_CodeDeckId (CodeDeckId),
		INDEX IX_Description (Description),
		INDEX IX_EffectiveDate (EffectiveDate),
		INDEX IX_DialStringPrefix (DialStringPrefix)
	);

	CALL prc_SplitRateTableRate(p_processId,p_dialcodeSeparator);

	IF  p_effectiveImmediately = 1
	THEN
		UPDATE tmp_split_RateTableRate_
		SET EffectiveDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EffectiveDate < DATE_FORMAT (NOW(), '%Y-%m-%d');

		UPDATE tmp_split_RateTableRate_
		SET EndDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EndDate < DATE_FORMAT (NOW(), '%Y-%m-%d');
	END IF;

	DROP TEMPORARY TABLE IF EXISTS tmp_split_RateTableRate_2;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_split_RateTableRate_2 as (SELECT * FROM tmp_split_RateTableRate_);

	DELETE n1 FROM tmp_split_RateTableRate_ n1
	INNER JOIN
	(
		SELECT MAX(TempRateTableRateID) AS TempRateTableRateID,EffectiveDate,Code,DialStringPrefix,TimezonesID,Rate
		FROM tmp_split_RateTableRate_2 WHERE ProcessId = p_processId
		GROUP BY Code,EffectiveDate,DialStringPrefix,TimezonesID,Rate
		HAVING COUNT(*)>1
	)n2
	ON n1.Code = n2.Code
	AND n2.EffectiveDate = n1.EffectiveDate
	AND ((n2.DialStringPrefix IS NULL AND n1.DialStringPrefix IS NULL) OR (n2.DialStringPrefix = n1.DialStringPrefix))
	AND n2.TimezonesID = n1.TimezonesID
	AND n2.Rate = n1.Rate
	AND n1.TempRateTableRateID < n2.TempRateTableRateID
	WHERE n1.ProcessId = p_processId;

	INSERT INTO tmp_TempRateTableRate_
	SELECT DISTINCT
		`TempRateTableRateID`,
		`CodeDeckId`,
		`TimezonesID`,
		`Code`,
		`Description`,
		`Rate`,
		`RateN`,
		`EffectiveDate`,
		`EndDate`,
		`Change`,
		`ProcessId`,
		`Preference`,
		`ConnectionFee`,
		`Interval1`,
		`IntervalN`,
		`Forbidden`,
		`DialStringPrefix`
	FROM tmp_split_RateTableRate_
	WHERE tmp_split_RateTableRate_.ProcessId = p_processId;

	SELECT CodeDeckId INTO v_CodeDeckId_
	FROM tmp_TempRateTableRate_
	WHERE ProcessId = p_processId  LIMIT 1;

	UPDATE tmp_TempRateTableRate_ as tblTempRateTableRate
	LEFT JOIN tblRate
		ON tblRate.Code = tblTempRateTableRate.Code
		AND tblRate.CompanyID = p_companyId
		AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		AND tblRate.CodeDeckId =  v_CodeDeckId_
	SET
		tblTempRateTableRate.Interval1 = CASE WHEN tblTempRateTableRate.Interval1 is not null  and tblTempRateTableRate.Interval1 > 0
		THEN
			tblTempRateTableRate.Interval1
		ELSE
			CASE WHEN tblRate.Interval1 is not null
			THEN
				tblRate.Interval1
			ELSE
				1
			END
		END,
		tblTempRateTableRate.IntervalN = CASE WHEN tblTempRateTableRate.IntervalN is not null  and tblTempRateTableRate.IntervalN > 0
		THEN
			tblTempRateTableRate.IntervalN
		ELSE
			CASE WHEN tblRate.IntervalN is not null
			THEN
				tblRate.IntervalN
			ElSE
				1
			END
		END;

	IF  p_effectiveImmediately = 1
	THEN
		UPDATE tmp_TempRateTableRate_
		SET EffectiveDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EffectiveDate < DATE_FORMAT (NOW(), '%Y-%m-%d');

		UPDATE tmp_TempRateTableRate_
		SET EndDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EndDate < DATE_FORMAT (NOW(), '%Y-%m-%d');
	END IF;

	SELECT count(*) INTO totalduplicatecode FROM(
	SELECT count(code) as c,code FROM tmp_TempRateTableRate_  GROUP BY Code,EffectiveDate,DialStringPrefix,TimezonesID HAVING c>1) AS tbl;

	IF  totalduplicatecode > 0
	THEN

		SELECT GROUP_CONCAT(code) into errormessage FROM(
		SELECT DISTINCT code, 1 as a FROM(
		SELECT   count(code) as c,code FROM tmp_TempRateTableRate_  GROUP BY Code,EffectiveDate,DialStringPrefix,TimezonesID HAVING c>1) AS tbl) as tbl2 GROUP by a;

		INSERT INTO tmp_JobLog_ (Message)
		SELECT DISTINCT
			CONCAT(code , ' DUPLICATE CODE')
		FROM(
			SELECT   count(code) as c,code FROM tmp_TempRateTableRate_  GROUP BY Code,EffectiveDate,DialStringPrefix,TimezonesID HAVING c>1) AS tbl;
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
			FROM tmp_TempRateTableRate_ vr
			LEFT JOIN tmp_DialString_ ds
				ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  ))
			WHERE vr.ProcessId = p_processId
				AND ds.DialStringID IS NULL
				AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

			IF totaldialstringcode > 0
			THEN




				INSERT INTO tblDialStringCode (DialStringID,DialString,ChargeCode,created_by)
				  SELECT DISTINCT p_dialStringId,vr.DialStringPrefix, Code, 'RMService'
					FROM tmp_TempRateTableRate_ vr
						LEFT JOIN tmp_DialString_ ds

							ON vr.DialStringPrefix = ds.DialString AND ds.DialStringID = p_dialStringId
						WHERE vr.ProcessId = p_processId
							AND ds.DialStringID IS NULL
							AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

				TRUNCATE tmp_DialString_;
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
				FROM tmp_TempRateTableRate_ vr
					LEFT JOIN tmp_DialString_ ds
						ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  ))

					WHERE vr.ProcessId = p_processId
						AND ds.DialStringID IS NULL
						AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

				INSERT INTO tmp_JobLog_ (Message)
					  SELECT DISTINCT CONCAT(Code ,' ', vr.DialStringPrefix , ' No PREFIX FOUND')
					  	FROM tmp_TempRateTableRate_ vr
							LEFT JOIN tmp_DialString_ ds

								ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  ))
							WHERE vr.ProcessId = p_processId
								AND ds.DialStringID IS NULL
								AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

			END IF;

			IF totaldialstringcode = 0
			THEN

				INSERT INTO tmp_RateTableRateDialString_
				SELECT DISTINCT
					`TempRateTableRateID`,
					`CodeDeckId`,
					`TimezonesID`,
					`DialString`,
					CASE WHEN ds.Description IS NULL OR ds.Description = ''
					THEN
						tblTempRateTableRate.Description
					ELSE
						ds.Description
					END
					AS Description,
					`Rate`,
					`RateN`,
					`EffectiveDate`,
					`EndDate`,
					`Change`,
					`ProcessId`,
					`Preference`,
					`ConnectionFee`,
					`Interval1`,
					`IntervalN`,
					tblTempRateTableRate.Forbidden as Forbidden ,
					tblTempRateTableRate.DialStringPrefix as DialStringPrefix
				FROM tmp_TempRateTableRate_ as tblTempRateTableRate
				INNER JOIN tmp_DialString_ ds
					ON ( (tblTempRateTableRate.Code = ds.ChargeCode AND tblTempRateTableRate.DialStringPrefix = '') OR (tblTempRateTableRate.DialStringPrefix != '' AND tblTempRateTableRate.DialStringPrefix =  ds.DialString AND tblTempRateTableRate.Code = ds.ChargeCode  ))
				WHERE tblTempRateTableRate.ProcessId = p_processId
					AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');



				INSERT INTO tmp_RateTableRateDialString_2
				SELECT *  FROM tmp_RateTableRateDialString_ where DialStringPrefix!='';

				Delete From tmp_RateTableRateDialString_
				Where DialStringPrefix = ''
				And Code IN (Select DialStringPrefix From tmp_RateTableRateDialString_2);

				INSERT INTO tmp_RateTableRateDialString_3
				SELECT * FROM tmp_RateTableRateDialString_;



				DELETE  FROM tmp_TempRateTableRate_ WHERE  ProcessId = p_processId;

				INSERT INTO tmp_TempRateTableRate_(
					`TempRateTableRateID`,
					CodeDeckId,
					TimezonesID,
					Code,
					Description,
					Rate,
					RateN,
					EffectiveDate,
					EndDate,
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
					`TempRateTableRateID`,
					`CodeDeckId`,
					`TimezonesID`,
					`Code`,
					`Description`,
					`Rate`,
					`RateN`,
					`EffectiveDate`,
					`EndDate`,
					`Change`,
					`ProcessId`,
					`Preference`,
					`ConnectionFee`,
					`Interval1`,
					`IntervalN`,
					`Forbidden`,
					DialStringPrefix
				FROM tmp_RateTableRateDialString_3;

				UPDATE tmp_TempRateTableRate_ as tblTempRateTableRate
				JOIN tmp_DialString_ ds
					ON ( (tblTempRateTableRate.Code = ds.ChargeCode and tblTempRateTableRate.DialStringPrefix = '') OR (tblTempRateTableRate.DialStringPrefix != '' and tblTempRateTableRate.DialStringPrefix =  ds.DialString and tblTempRateTableRate.Code = ds.ChargeCode  ))
					AND tblTempRateTableRate.ProcessId = p_processId
					AND ds.Forbidden = 1
				SET tblTempRateTableRate.Forbidden = 'B';

				UPDATE tmp_TempRateTableRate_ as  tblTempRateTableRate
				JOIN tmp_DialString_ ds
					ON ( (tblTempRateTableRate.Code = ds.ChargeCode and tblTempRateTableRate.DialStringPrefix = '') OR (tblTempRateTableRate.DialStringPrefix != '' and tblTempRateTableRate.DialStringPrefix =  ds.DialString and tblTempRateTableRate.Code = ds.ChargeCode  ))
					AND tblTempRateTableRate.ProcessId = p_processId
					AND ds.Forbidden = 0
				SET tblTempRateTableRate.Forbidden = 'UB';

			END IF;

		END IF;

	END IF;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_WSProcessRateTableRate`;
DELIMITER //
CREATE PROCEDURE `prc_WSProcessRateTableRate`(
	IN `p_RateTableId` INT,
	IN `p_replaceAllRates` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_processId` VARCHAR(200),
	IN `p_addNewCodesToCodeDeck` INT,
	IN `p_companyId` INT,
	IN `p_forbidden` INT,
	IN `p_preference` INT,
	IN `p_dialstringid` INT,
	IN `p_dialcodeSeparator` VARCHAR(50),
	IN `p_CurrencyID` INT,
	IN `p_list_option` INT,
	IN `p_UserName` TEXT
)
ThisSP:BEGIN

	DECLARE v_AffectedRecords_ INT DEFAULT 0;
	DECLARE v_CodeDeckId_ INT ;
	DECLARE totaldialstringcode INT(11) DEFAULT 0;
	DECLARE newstringcode INT(11) DEFAULT 0;
	DECLARE totalduplicatecode INT(11);
	DECLARE errormessage longtext;
	DECLARE errorheader longtext;
	DECLARE v_RateTableCurrencyID_ INT;
	DECLARE v_CompanyCurrencyID_ INT;

	DECLARE v_pointer_ INT;
	DECLARE v_rowCount_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
	CREATE TEMPORARY TABLE tmp_JobLog_ (
		Message longtext
	);

    DROP TEMPORARY TABLE IF EXISTS tmp_TempTimezones_;
    CREATE TEMPORARY TABLE tmp_TempTimezones_ (
        TimezonesID INT
    );

	DROP TEMPORARY TABLE IF EXISTS tmp_split_RateTableRate_;
	CREATE TEMPORARY TABLE tmp_split_RateTableRate_ (
		`TempRateTableRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Rate` decimal(18, 6) ,
		`RateN` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
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

	DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableRate_;
	CREATE TEMPORARY TABLE tmp_TempRateTableRate_ (
		TempRateTableRateID int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`Rate` decimal(18, 6) ,
		`RateN` decimal(18, 6) ,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
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

	DROP TEMPORARY TABLE IF EXISTS tmp_Delete_RateTableRate;
	CREATE TEMPORARY TABLE tmp_Delete_RateTableRate (
		RateTableRateID INT,
		RateTableId INT,
		TimezonesID INT,
		RateId INT,
		Code VARCHAR(50),
		Description VARCHAR(200),
		Rate DECIMAL(18, 6),
		RateN DECIMAL(18, 6),
		EffectiveDate DATETIME,
		EndDate Datetime ,
		Interval1 INT,
		IntervalN INT,
		ConnectionFee DECIMAL(18, 6),
		deleted_at DATETIME,
		INDEX tmp_RateTableRateDiscontinued_RateTableRateID (`RateTableRateID`)
	);

	/*  1.  Check duplicate code, dial string   */
	CALL  prc_RateTableCheckDialstringAndDupliacteCode(p_companyId,p_processId,p_dialstringid,p_effectiveImmediately,p_dialcodeSeparator);

	SELECT COUNT(*) AS COUNT INTO newstringcode from tmp_JobLog_;

 	-- insert all TimezonesID against which rates will upload
 	INSERT INTO tmp_TempTimezones_
 	SELECT DISTINCT TimezonesID from tmp_TempRateTableRate_;

	-- if no error
	IF newstringcode = 0
	THEN
		/*  2.  Send Today EndDate to rates which are marked deleted in review screen  */
		/*  3.  Update interval in temp table */

		-- if review
		IF (SELECT count(*) FROM tblRateTableRateChangeLog WHERE ProcessID = p_processId ) > 0
		THEN
			-- update end date given from tblRateTableRateChangeLog for deleted rates.
			UPDATE
				tblRateTableRate vr
			INNER JOIN tblRateTableRateChangeLog  vrcl
			on vrcl.RateTableRateID = vr.RateTableRateID
			SET
				vr.EndDate = IFNULL(vrcl.EndDate,date(now()))
			WHERE vrcl.ProcessID = p_processId
				AND vrcl.`Action`  ='Deleted';

			-- update end date on temp table
			UPDATE tmp_TempRateTableRate_ tblTempRateTableRate
			JOIN tblRateTableRateChangeLog vrcl
				ON  vrcl.ProcessId = p_processId
				AND vrcl.Code = tblTempRateTableRate.Code
				-- AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
			SET
				tblTempRateTableRate.EndDate = vrcl.EndDate
			WHERE
				vrcl.`Action` = 'Deleted'
				AND vrcl.EndDate IS NOT NULL ;

			-- update intervals.
			UPDATE tmp_TempRateTableRate_ tblTempRateTableRate
			JOIN tblRateTableRateChangeLog vrcl
				ON  vrcl.ProcessId = p_processId
				AND vrcl.Code = tblTempRateTableRate.Code
				-- AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
			SET
				tblTempRateTableRate.Interval1 = vrcl.Interval1 ,
				tblTempRateTableRate.IntervalN = vrcl.IntervalN
			WHERE
				vrcl.`Action` = 'New'
				AND vrcl.Interval1 IS NOT NULL
				AND vrcl.IntervalN IS NOT NULL ;

			/*IF (FOUND_ROWS() > 0) THEN
				INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Updated End Date of Deleted Records. ' );
			END IF;
			*/

		END IF;

		/*  4.  Update EndDate to Today if Replace All existing */
		IF  p_replaceAllRates = 1
		THEN
			UPDATE tblRateTableRate
				SET tblRateTableRate.EndDate = date(now())
			WHERE RateTableId = p_RateTableId;

			/*
			IF (FOUND_ROWS() > 0) THEN
				INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' Records Removed.   ' );
			END IF;
			*/
		END IF;

		/* 5. If Complete File, remove rates not exists in file  */

		IF p_list_option = 1    -- v4.16 p_list_option = 1 : "Completed List", p_list_option = 2 : "Partial List"
		THEN
			-- v4.16 get rates which is not in file and insert it into temp table
			INSERT INTO tmp_Delete_RateTableRate(
				RateTableRateID ,
				RateTableId,
				TimezonesID,
				RateId,
				Code ,
				Description ,
				Rate ,
				RateN ,
				EffectiveDate ,
				EndDate ,
				Interval1 ,
				IntervalN ,
				ConnectionFee ,
				deleted_at
			)
			SELECT DISTINCT
				tblRateTableRate.RateTableRateID,
				p_RateTableId AS RateTableId,
				tblRateTableRate.TimezonesID,
				tblRateTableRate.RateId,
				tblRate.Code,
				tblRate.Description,
				tblRateTableRate.Rate,
				tblRateTableRate.RateN,
				tblRateTableRate.EffectiveDate,
				IFNULL(tblRateTableRate.EndDate,date(now())) ,
				tblRateTableRate.Interval1,
				tblRateTableRate.IntervalN,
				tblRateTableRate.ConnectionFee,
				now() AS deleted_at
			FROM tblRateTableRate
			JOIN tblRate
				ON tblRate.RateID = tblRateTableRate.RateId
				AND tblRate.CompanyID = p_companyId
		  	JOIN tmp_TempTimezones_
		  		ON tmp_TempTimezones_.TimezonesID = tblRateTableRate.TimezonesID
			LEFT JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
				ON tblTempRateTableRate.Code = tblRate.Code
				AND tblTempRateTableRate.TimezonesID = tblRateTableRate.TimezonesID
				AND  tblTempRateTableRate.ProcessId = p_processId
				AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
			WHERE tblRateTableRate.RateTableId = p_RateTableId
				AND tblTempRateTableRate.Code IS NULL
				AND ( tblRateTableRate.EndDate is NULL OR tblRateTableRate.EndDate <= date(now()) )
			ORDER BY RateTableRateID ASC;

			/*IF (FOUND_ROWS() > 0) THEN
			INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Records marked deleted as Not exists in File' );
			END IF;*/

			-- set end date will remove at bottom in archive proc
			UPDATE tblRateTableRate
			JOIN tmp_Delete_RateTableRate ON tblRateTableRate.RateTableRateID = tmp_Delete_RateTableRate.RateTableRateID
				SET tblRateTableRate.EndDate = date(now())
			WHERE
				tblRateTableRate.RateTableId = p_RateTableId;

		END IF;

		/* 6. Move Rates to archive which has EndDate <= now()  */
		-- move to archive if EndDate is <= now()
		IF ( (SELECT count(*) FROM tblRateTableRate WHERE  RateTableId = p_RateTableId AND EndDate <= NOW() )  > 0  ) THEN

			-- move to archive
			/*INSERT INTO tblRateTableRateArchive
			SELECT DISTINCT  null , -- Primary Key column
				`RateTableRateID`,
				`RateTableId`,
				`RateId`,
				`Rate`,
				`EffectiveDate`,
				IFNULL(`EndDate`,date(now())) as EndDate,
				`updated_at`,
				`created_at`,
				`created_by`,
				`ModifiedBy`,
				`Interval1`,
				`IntervalN`,
				`ConnectionFee`,
				concat('Ends Today rates @ ' , now() ) as `Notes`
			FROM tblRateTableRate
			WHERE  RateTableId = p_RateTableId AND EndDate <= NOW();

			delete from tblRateTableRate
			WHERE  RateTableId = p_RateTableId AND EndDate <= NOW();*/

			-- Update previous rate before archive
			call prc_RateTableRateUpdatePreviousRate(p_RateTableId,'');

			-- Archive Rates
			call prc_ArchiveOldRateTableRate(p_RateTableId, NULL,p_UserName);

		END IF;

		/* 7. Add New code in codedeck  */

		IF  p_addNewCodesToCodeDeck = 1
		THEN
			INSERT INTO tblRate (
				CompanyID,
				Code,
				Description,
				CreatedBy,
				CountryID,
				CodeDeckId,
				Interval1,
				IntervalN
			)
			SELECT DISTINCT
				p_companyId,
				vc.Code,
				vc.Description,
				'RMService',
				fnGetCountryIdByCodeAndCountry (vc.Code ,vc.Description) AS CountryID,
				CodeDeckId,
				Interval1,
				IntervalN
			FROM
			(
				SELECT DISTINCT
					tblTempRateTableRate.Code,
					tblTempRateTableRate.Description,
					tblTempRateTableRate.CodeDeckId,
					tblTempRateTableRate.Interval1,
					tblTempRateTableRate.IntervalN
				FROM tmp_TempRateTableRate_  as tblTempRateTableRate
				LEFT JOIN tblRate
					ON tblRate.Code = tblTempRateTableRate.Code
					AND tblRate.CompanyID = p_companyId
					AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
				WHERE tblRate.RateID IS NULL
					AND tblTempRateTableRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
			) vc;

			/*IF (FOUND_ROWS() > 0) THEN
					INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - New Code Inserted into Codedeck ' );
			END IF;*/

		ELSE
			SELECT GROUP_CONCAT(code) into errormessage FROM(
				SELECT DISTINCT
					c.Code as code, 1 as a
				FROM
				(
					SELECT DISTINCT
						tblTempRateTableRate.Code,
						tblTempRateTableRate.Description
					FROM tmp_TempRateTableRate_  as tblTempRateTableRate
					LEFT JOIN tblRate
						ON tblRate.Code = tblTempRateTableRate.Code
						AND tblRate.CompanyID = p_companyId
						AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
					WHERE tblRate.RateID IS NULL
						AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
				) c
			) as tbl GROUP BY a;

			IF errormessage IS NOT NULL
			THEN
				INSERT INTO tmp_JobLog_ (Message)
				SELECT DISTINCT
					CONCAT(tbl.Code , ' CODE DOES NOT EXIST IN CODE DECK')
				FROM
				(
					SELECT DISTINCT
						tblTempRateTableRate.Code,
						tblTempRateTableRate.Description
					FROM tmp_TempRateTableRate_  as tblTempRateTableRate
					LEFT JOIN tblRate
						ON tblRate.Code = tblTempRateTableRate.Code
						AND tblRate.CompanyID = p_companyId
						AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
					WHERE tblRate.RateID IS NULL
						AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
				) as tbl;
			END IF;
		END IF;

		/* 8. delete rates which will be map as deleted */

		-- delete rates which will be map as deleted
		UPDATE tblRateTableRate
		INNER JOIN tblRate
			ON tblRate.RateID = tblRateTableRate.RateId
			AND tblRate.CompanyID = p_companyId
		INNER JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND tblTempRateTableRate.TimezonesID = tblRateTableRate.TimezonesID
			AND tblTempRateTableRate.Change IN ('Delete', 'R', 'D', 'Blocked', 'Block')
		SET tblRateTableRate.EndDate = IFNULL(tblTempRateTableRate.EndDate,date(now()))
		WHERE tblRateTableRate.RateTableId = p_RateTableId;

		/*IF (FOUND_ROWS() > 0) THEN
		INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Records marked deleted as mapped in File ' );
		END IF;*/


		-- need to get ratetable rates with latest records ....
		-- and then need to use that table to insert update records in ratetable rate.


		-- ------

		/* 9. Update Interval in tblRate */

		-- Update Interval Changed for Action = "New"
		-- update Intervals which are not maching with tblTempRateTableRate
		-- so as if intervals will not mapped next time it will be same as last file.
		UPDATE tblRate
		JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
			ON 	  tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
			AND tblTempRateTableRate.Code = tblRate.Code
			AND  tblTempRateTableRate.ProcessId = p_processId
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
		SET
			tblRate.Interval1 = tblTempRateTableRate.Interval1,
			tblRate.IntervalN = tblTempRateTableRate.IntervalN
		WHERE
			tblTempRateTableRate.Interval1 IS NOT NULL
			AND tblTempRateTableRate.IntervalN IS NOT NULL
			AND
			(
				tblRate.Interval1 != tblTempRateTableRate.Interval1
				OR
				tblRate.IntervalN != tblTempRateTableRate.IntervalN
			);


		/* 10. Update INTERVAL, ConnectionFee,  */

		UPDATE tblRateTableRate
		INNER JOIN tblRate
			ON tblRateTableRate.RateId = tblRate.RateId
			AND tblRateTableRate.RateTableId = p_RateTableId
		INNER JOIN tmp_TempRateTableRate_ as tblTempRateTableRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND tblTempRateTableRate.TimezonesID = tblRateTableRate.TimezonesID
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
			AND tblRateTableRate.RateId = tblRate.RateId
		SET tblRateTableRate.ConnectionFee = tblTempRateTableRate.ConnectionFee,
			tblRateTableRate.Interval1 = tblTempRateTableRate.Interval1,
			tblRateTableRate.IntervalN = tblTempRateTableRate.IntervalN
			--  tblRateTableRate.EndDate = tblTempRateTableRate.EndDate
		WHERE tblRateTableRate.RateTableId = p_RateTableId;


		/*IF (FOUND_ROWS() > 0) THEN
		INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Updated Existing Records' );
		END IF;*/


		/* 12. Delete rates which are same in file   */

		-- delete rates which are not increase/decreased  (rates = rates)
		DELETE tblTempRateTableRate
		FROM tmp_TempRateTableRate_ as tblTempRateTableRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableRate.Code
		JOIN tblRateTableRate
			ON tblRateTableRate.RateId = tblRate.RateId
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
			AND tblRateTableRate.RateTableId = p_RateTableId
			AND tblRateTableRate.TimezonesID = tblTempRateTableRate.TimezonesID
			AND tblTempRateTableRate.Rate = tblRateTableRate.Rate
			/*AND (
				tblRateTableRate.EffectiveDate = tblTempRateTableRate.EffectiveDate
				OR
				(
					DATE_FORMAT (tblRateTableRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTableRate.EffectiveDate, '%Y-%m-%d')
				)
				OR 1 = (CASE
							WHEN tblTempRateTableRate.EffectiveDate > NOW() THEN 1
							ELSE 0
						END)
			)*/
		WHERE  tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block');

		/*IF (FOUND_ROWS() > 0) THEN
		INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Discarded no change records' );
		END IF;*/

		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

		SELECT CurrencyID into v_RateTableCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyID FROM tblRateTable WHERE RateTableId=p_RateTableId);
		SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);

		/* 13. update currency   */

		/*UPDATE tmp_TempRateTableRate_ as tblTempRateTableRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		JOIN tblRateTableRate
			ON tblRateTableRate.RateId = tblRate.RateId
			AND tblRateTableRate.RateTableId = p_RateTableId
		SET tblRateTableRate.Rate = IF (
			p_CurrencyID > 0,
			CASE WHEN p_CurrencyID = v_RateTableCurrencyID_
			THEN
				tblTempRateTableRate.Rate
			WHEN  p_CurrencyID = v_CompanyCurrencyID_
			THEN
			(
				( tblTempRateTableRate.Rate  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ and CompanyID = p_companyId ) )
			)
			ELSE
			(
				(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId )
				*
				(tblTempRateTableRate.Rate  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))
			)
			END ,
			tblTempRateTableRate.Rate
		)
		WHERE tblTempRateTableRate.Rate <> tblRateTableRate.Rate
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
			AND DATE_FORMAT (tblRateTableRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTableRate.EffectiveDate, '%Y-%m-%d');

		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();*/

		/* 13. archive same date's rate   */
		DROP TEMPORARY TABLE IF EXISTS tmp_PreviousRate;
		CREATE TEMPORARY TABLE `tmp_PreviousRate` (
			`RateId` int,
			`PreviousRate` decimal(18, 6),
			`EffectiveDate` Datetime
		);

		UPDATE tmp_TempRateTableRate_ as tblTempRateTableRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		JOIN tblRateTableRate
			ON tblRateTableRate.RateId = tblRate.RateId
			AND tblRateTableRate.RateTableId = p_RateTableId
			AND tblRateTableRate.TimezonesID = tblTempRateTableRate.TimezonesID
		SET tblRateTableRate.EndDate = NOW()
		WHERE tblTempRateTableRate.Rate <> tblRateTableRate.Rate
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
			AND DATE_FORMAT (tblRateTableRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTableRate.EffectiveDate, '%Y-%m-%d');

		INSERT INTO
			tmp_PreviousRate (RateId,PreviousRate,EffectiveDate)
		SELECT
			tblRateTableRate.RateId,tblRateTableRate.Rate,tblTempRateTableRate.EffectiveDate
		FROM
			tmp_TempRateTableRate_ as tblTempRateTableRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		JOIN tblRateTableRate
			ON tblRateTableRate.RateId = tblRate.RateId
			AND tblRateTableRate.RateTableId = p_RateTableId
			AND tblRateTableRate.TimezonesID = tblTempRateTableRate.TimezonesID
		WHERE tblTempRateTableRate.Rate <> tblRateTableRate.Rate
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
			AND DATE_FORMAT (tblRateTableRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTableRate.EffectiveDate, '%Y-%m-%d');

		-- archive rates which has EndDate <= today
		call prc_ArchiveOldRateTableRate(p_RateTableId, NULL,p_UserName);

		/* 13. insert new rates   */

		INSERT INTO tblRateTableRate (
			RateTableId,
			TimezonesID,
			RateId,
			Rate,
			RateN,
			EffectiveDate,
			EndDate,
			ConnectionFee,
			Interval1,
			IntervalN,
			PreviousRate
		)
		SELECT DISTINCT
			p_RateTableId,
			tblTempRateTableRate.TimezonesID,
			tblRate.RateID,
			IF (
				p_CurrencyID > 0,
				CASE WHEN p_CurrencyID = v_RateTableCurrencyID_
				THEN
					tblTempRateTableRate.Rate
				WHEN  p_CurrencyID = v_CompanyCurrencyID_
				THEN
				(
					( tblTempRateTableRate.Rate  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ and CompanyID = p_companyId ) )
				)
				ELSE
				(
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId )
					*
					(tblTempRateTableRate.Rate  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))
				)
				END ,
				tblTempRateTableRate.Rate
			) AS Rate,
			IF (
				p_CurrencyID > 0,
				CASE WHEN p_CurrencyID = v_RateTableCurrencyID_
				THEN
					tblTempRateTableRate.RateN
				WHEN  p_CurrencyID = v_CompanyCurrencyID_
				THEN
				(
					( tblTempRateTableRate.RateN  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ and CompanyID = p_companyId ) )
				)
				ELSE
				(
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId )
					*
					(tblTempRateTableRate.RateN  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))
				)
				END ,
				tblTempRateTableRate.Rate
			) AS RateN,
			tblTempRateTableRate.EffectiveDate,
			tblTempRateTableRate.EndDate,
			tblTempRateTableRate.ConnectionFee,
			tblTempRateTableRate.Interval1,
			tblTempRateTableRate.IntervalN,
			IFNULL(tmp_PreviousRate.PreviousRate,0) AS PreviousRate
		FROM tmp_TempRateTableRate_ as tblTempRateTableRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableRate.CodeDeckId
		LEFT JOIN tblRateTableRate
			ON tblRate.RateID = tblRateTableRate.RateId
			AND tblRateTableRate.RateTableId = p_RateTableId
			AND tblRateTableRate.TimezonesID = tblTempRateTableRate.TimezonesID
			AND tblTempRateTableRate.EffectiveDate = tblRateTableRate.EffectiveDate
		LEFT JOIN tmp_PreviousRate
			ON tblRate.RateId = tmp_PreviousRate.RateId AND tblTempRateTableRate.EffectiveDate = tmp_PreviousRate.EffectiveDate
		WHERE tblRateTableRate.RateTableRateID IS NULL
			AND tblTempRateTableRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
			AND tblTempRateTableRate.EffectiveDate >= DATE_FORMAT (NOW(), '%Y-%m-%d');

		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

		/*IF (FOUND_ROWS() > 0) THEN
		INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - New Records Inserted.' );
		END IF;
		*/

		/* 13. update enddate in old rates */

		-- loop through effective date to update end date
		DROP TEMPORARY TABLE IF EXISTS tmp_EffectiveDates_;
		CREATE TEMPORARY TABLE tmp_EffectiveDates_ (
			RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			EffectiveDate  Date
		);
		INSERT INTO tmp_EffectiveDates_ (EffectiveDate)
		SELECT distinct
			EffectiveDate
		FROM
		(
			select distinct EffectiveDate
			from 	tblRateTableRate
			WHERE
				RateTableId = p_RateTableId
			Group By EffectiveDate
			order by EffectiveDate desc
		) tmp
		,(SELECT @row_num := 0) x;


		SET v_pointer_ = 1;
		SET v_rowCount_ = ( SELECT COUNT(*) FROM tmp_EffectiveDates_ );

		IF v_rowCount_ > 0 THEN

			WHILE v_pointer_ <= v_rowCount_
			DO
				SET @EffectiveDate = ( SELECT EffectiveDate FROM tmp_EffectiveDates_ WHERE RowID = v_pointer_ );
				SET @row_num = 0;

				UPDATE  tblRateTableRate vr1
				inner join
				(
					select
						RateTableId,
						RateID,
						EffectiveDate,
						TimezonesID
					FROM tblRateTableRate
					WHERE RateTableId = p_RateTableId
						AND EffectiveDate =   @EffectiveDate
					order by EffectiveDate desc
				) tmpvr
				on
					vr1.RateTableId = tmpvr.RateTableId
					AND vr1.RateID  	=        	tmpvr.RateID
					AND vr1.TimezonesID = tmpvr.TimezonesID
					AND vr1.EffectiveDate 	< tmpvr.EffectiveDate
				SET
					vr1.EndDate = @EffectiveDate
				where
					vr1.RateTableId = p_RateTableId
					--	AND vr1.EffectiveDate < @EffectiveDate
					AND vr1.EndDate is null;


				SET v_pointer_ = v_pointer_ + 1;

			END WHILE;

		END IF;

	END IF;

	INSERT INTO tmp_JobLog_ (Message) 	 	SELECT CONCAT(v_AffectedRecords_ , ' Records Uploaded ' );

	-- Update previous rate before archive
	call prc_RateTableRateUpdatePreviousRate(p_RateTableId,'');

	-- archive rates which has EndDate <= today
	call prc_ArchiveOldRateTableRate(p_RateTableId, NULL,p_UserName);


	DELETE  FROM tblTempRateTableRate WHERE  ProcessId = p_processId;
	DELETE  FROM tblRateTableRateChangeLog WHERE ProcessID = p_processId;
	SELECT * FROM tmp_JobLog_;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_WSProcessVendorRate`;
DELIMITER //
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
	IN `p_dialcodeSeparator` VARCHAR(50),
	IN `p_CurrencyID` INT,
	IN `p_list_option` INT,
	IN `p_UserName` TEXT
)
ThisSP:BEGIN

		DECLARE v_AffectedRecords_ INT DEFAULT 0;
		DECLARE v_CodeDeckId_ INT ;
		DECLARE totaldialstringcode INT(11) DEFAULT 0;
		DECLARE newstringcode INT(11) DEFAULT 0;
		DECLARE totalduplicatecode INT(11);
		DECLARE errormessage longtext;
		DECLARE errorheader longtext;
		DECLARE v_AccountCurrencyID_ INT;
		DECLARE v_CompanyCurrencyID_ INT;


		DECLARE v_pointer_ INT;
		DECLARE v_rowCount_ INT;


	  SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
    CREATE TEMPORARY TABLE tmp_JobLog_ (
        Message longtext
    );

    DROP TEMPORARY TABLE IF EXISTS tmp_TempTimezones_;
    CREATE TEMPORARY TABLE tmp_TempTimezones_ (
        TimezonesID INT
    );

    DROP TEMPORARY TABLE IF EXISTS tmp_split_VendorRate_;
    CREATE TEMPORARY TABLE tmp_split_VendorRate_ (
    		`TempVendorRateID` int,
			  `CodeDeckId` int ,
			  `TimezonesID` INT,
			  `Code` varchar(50) ,
			  `Description` varchar(200) ,
			  `Rate` decimal(18, 6) ,
			  `RateN` decimal(18, 6) ,
			  `EffectiveDate` Datetime ,
			  `EndDate` Datetime ,
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
		    TempVendorRateID int,
			  `CodeDeckId` int ,
			  `TimezonesID` INT,
			  `Code` varchar(50) ,
			  `Description` varchar(200) ,
			  `Rate` decimal(18, 6) ,
			  `RateN` decimal(18, 6) ,
			  `EffectiveDate` Datetime ,
			  `EndDate` Datetime ,
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
		  TimezonesID INT,
        RateId INT,
        Code VARCHAR(50),
        Description VARCHAR(200),
        Rate DECIMAL(18, 6),
		  RateN DECIMAL(18, 6) ,
        EffectiveDate DATETIME,
		EndDate Datetime ,
        Interval1 INT,
        IntervalN INT,
        ConnectionFee DECIMAL(18, 6),
        deleted_at DATETIME,
        INDEX tmp_VendorRateDiscontinued_VendorRateID (`VendorRateID`)
    );


	/*  1.  Check duplicate code, dial string   */
    CALL  prc_checkDialstringAndDupliacteCode(p_companyId,p_processId,p_dialstringid,p_effectiveImmediately,p_dialcodeSeparator);

    SELECT COUNT(*) AS COUNT INTO newstringcode from tmp_JobLog_;

 -- LEAVE ThisSP;

 	-- insert all TimezonesID against which rates will upload
 	INSERT INTO tmp_TempTimezones_
 	SELECT DISTINCT TimezonesID from tmp_TempVendorRate_;


	-- if no error
    IF newstringcode = 0
    THEN


		/*  2.  Send Today EndDate to rates which are marked deleted in review screen  */
		/*  3.  Update interval in temp table */

		-- if review
		IF (SELECT count(*) FROM tblVendorRateChangeLog WHERE ProcessID = p_processId ) > 0 THEN

			-- v4.16 update end date given from tblVendorRateChangeLog for deleted rates.
			UPDATE
			tblVendorRate vr
			INNER JOIN tblVendorRateChangeLog  vrcl
                    on vrcl.VendorRateID = vr.VendorRateID
			SET
			vr.EndDate = IFNULL(vrcl.EndDate,date(now()))
			WHERE vrcl.ProcessID = p_processId
			AND vrcl.`Action`  ='Deleted';

			-- update end date on temp table
			 UPDATE tmp_TempVendorRate_ tblTempVendorRate
          JOIN tblVendorRateChangeLog vrcl
          		 ON  vrcl.ProcessId = p_processId
          		 AND vrcl.Code = tblTempVendorRate.Code
        			 -- AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
        	SET
			   tblTempVendorRate.EndDate = vrcl.EndDate
		     WHERE
		     vrcl.`Action` = 'Deleted'
        	  AND vrcl.EndDate IS NOT NULL ;


			-- update intervals.
		   UPDATE tmp_TempVendorRate_ tblTempVendorRate
          JOIN tblVendorRateChangeLog vrcl
          		 ON  vrcl.ProcessId = p_processId
          		 AND vrcl.Code = tblTempVendorRate.Code
        			 -- AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
        	SET
			   tblTempVendorRate.Interval1 = vrcl.Interval1 ,
				tblTempVendorRate.IntervalN = vrcl.IntervalN
		     WHERE
		     vrcl.`Action` = 'New'
        	  AND vrcl.Interval1 IS NOT NULL
			  AND vrcl.IntervalN IS NOT NULL ;



			/*IF (FOUND_ROWS() > 0) THEN
				INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Updated End Date of Deleted Records. ' );
			END IF;
			*/


		END IF;

		/*  4.  Update EndDate to Today if Replace All existing */

		IF  p_replaceAllRates = 1
		THEN

          /*
				DELETE FROM tblVendorRate
				WHERE AccountId = p_accountId
				AND TrunkID = p_trunkId;
          */

			UPDATE tblVendorRate
			SET tblVendorRate.EndDate = date(now())
			WHERE AccountId = p_accountId
			AND TrunkID = p_trunkId;




			/*
			IF (FOUND_ROWS() > 0) THEN
				INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' Records Removed.   ' );
			END IF;
			*/

		END IF;

		/* 5. If Complete File, remove rates not exists in file  */

		IF p_list_option = 1    -- v4.16 p_list_option = 1 : "Completed List", p_list_option = 2 : "Partial List"
		THEN


			-- v4.16 get rates which is not in file and insert it into temp table
			INSERT INTO tmp_Delete_VendorRate(
							VendorRateID ,
							AccountId,
							TrunkID ,
							TimezonesID,
							RateId,
							Code ,
							Description ,
							Rate ,
							RateN ,
							EffectiveDate ,
							EndDate ,
							Interval1 ,
							IntervalN ,
							ConnectionFee ,
							deleted_at
			)
			SELECT DISTINCT
                    tblVendorRate.VendorRateID,
                    p_accountId AS AccountId,
                    p_trunkId AS TrunkID,
                    tblVendorRate.TimezonesID,
                    tblVendorRate.RateId,
                    tblRate.Code,
                    tblRate.Description,
                    tblVendorRate.Rate,
                    tblVendorRate.RateN,
                    tblVendorRate.EffectiveDate,
                    IFNULL(tblVendorRate.EndDate,date(now())) ,
                    tblVendorRate.Interval1,
                    tblVendorRate.IntervalN,
                    tblVendorRate.ConnectionFee,
                    now() AS deleted_at
                    FROM tblVendorRate
	                    JOIN tblRate
	                   		 ON tblRate.RateID = tblVendorRate.RateId
									  	AND tblRate.CompanyID = p_companyId
							  JOIN tmp_TempTimezones_
							  		 ON tmp_TempTimezones_.TimezonesID = tblVendorRate.TimezonesID
	                    LEFT JOIN tmp_TempVendorRate_ as tblTempVendorRate
	                   		 ON tblTempVendorRate.Code = tblRate.Code
	                   		 	 AND tblTempVendorRate.TimezonesID = tblVendorRate.TimezonesID
	                   			 AND  tblTempVendorRate.ProcessId = p_processId
	                   			 AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
	                    WHERE tblVendorRate.AccountId = p_accountId
	                   		 AND tblVendorRate.TrunkId = p_trunkId
	                   		 AND tblTempVendorRate.Code IS NULL
	                   		 AND ( tblVendorRate.EndDate is NULL OR tblVendorRate.EndDate <= date(now()) )

                    ORDER BY VendorRateID ASC;


							/*IF (FOUND_ROWS() > 0) THEN
								INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Records marked deleted as Not exists in File' );
							END IF;*/

			-- set end date will remove at bottom in archive proc
			UPDATE tblVendorRate
				JOIN tmp_Delete_VendorRate ON tblVendorRate.VendorRateID = tmp_Delete_VendorRate.VendorRateID
				SET tblVendorRate.EndDate = date(now())
			WHERE
				tblVendorRate.AccountId = p_accountId
		      AND tblVendorRate.TrunkId = p_trunkId;

		-- 	CALL prc_InsertDiscontinuedVendorRate(p_accountId,p_trunkId);


		END IF;

		/* 6. Move Rates to archive which has EndDate <= now()  */
			-- move to archive if EndDate is <= now()
		IF ( (SELECT count(*) FROM tblVendorRate WHERE  AccountId = p_accountId  AND TrunkId = p_trunkId AND EndDate <= NOW() )  > 0  ) THEN

				-- move to archive
				INSERT INTO tblVendorRateArchive
				SELECT DISTINCT  null , -- Primary Key column
				`VendorRateID`,
				`AccountId`,
				`TrunkID`,
				`TimezonesID`,
				`RateId`,
				`Rate`,
				`RateN`,
				`EffectiveDate`,
				IFNULL(`EndDate`,date(now())) as EndDate,
				`updated_at`,
				`created_at`,
				`created_by`,
				`updated_by`,
				`Interval1`,
				`IntervalN`,
				`ConnectionFee`,
				`MinimumCost`,
				  concat('Ends Today rates @ ' , now() ) as `Notes`
			      FROM tblVendorRate
			      WHERE  AccountId = p_accountId  AND TrunkId = p_trunkId AND EndDate <= NOW();

			      delete from tblVendorRate
			      WHERE  AccountId = p_accountId  AND TrunkId = p_trunkId AND EndDate <= NOW();


		END IF;

		/* 7. Add New code in codedeck  */

		IF  p_addNewCodesToCodeDeck = 1
            THEN
                INSERT INTO tblRate (
                    CompanyID,
                    Code,
                    Description,
                    CreatedBy,
                    CountryID,
                    CodeDeckId,
                    Interval1,
                    IntervalN
                )
                SELECT DISTINCT
                    p_companyId,
                    vc.Code,
                    vc.Description,
                    'RMService',
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
                    AND tblTempVendorRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
                ) vc;


						/*IF (FOUND_ROWS() > 0) THEN
								INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - New Code Inserted into Codedeck ' );
						END IF;*/


						/*
               	SELECT GROUP_CONCAT(Code) into errormessage FROM(
                    SELECT DISTINCT
                        tblTempVendorRate.Code as Code, 1 as a
                    FROM tmp_TempVendorRate_  as tblTempVendorRate
                    INNER JOIN tblRate
                    ON tblRate.Code = tblTempVendorRate.Code
                    AND tblRate.CompanyID = p_companyId
                    AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
							      WHERE tblRate.CountryID IS NULL
                    AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
                ) as tbl GROUP BY a;

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
					 	    END IF; */
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
                          AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
                    ) c
                ) as tbl GROUP BY a;

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
                              AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
                        ) as tbl;
					 	    END IF;
            END IF;

			/* 8. delete rates which will be map as deleted */

				-- delete rates which will be map as deleted
            UPDATE tblVendorRate
                    INNER JOIN tblRate
                        ON tblRate.RateID = tblVendorRate.RateId
                            AND tblRate.CompanyID = p_companyId
                    INNER JOIN tmp_TempVendorRate_ as tblTempVendorRate
                        ON tblRate.Code = tblTempVendorRate.Code
                        AND tblTempVendorRate.TimezonesID = tblVendorRate.TimezonesID
                        AND tblTempVendorRate.Change IN ('Delete', 'R', 'D', 'Blocked', 'Block')
                     SET tblVendorRate.EndDate = IFNULL(tblTempVendorRate.EndDate,date(now()))
                     WHERE tblVendorRate.AccountId = p_accountId
                    AND tblVendorRate.TrunkId = p_trunkId ;


						/*IF (FOUND_ROWS() > 0) THEN
								INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Records marked deleted as mapped in File ' );
						END IF;*/


			-- need to get vendor rates with latest records ....
			-- and then need to use that table to insert update records in vendor rate.


			-- ------

			  	  -- CALL prc_InsertDiscontinuedVendorRate(p_accountId,p_trunkId);

			/* 9. Update Interval in tblRate */

			-- Update Interval Changed for Action = "New"
			-- update Intervals which are not maching with tblTempVendorRate
			-- so as if intervals will not mapped next time it will be same as last file.
    				UPDATE tblRate
                 JOIN tmp_TempVendorRate_ as tblTempVendorRate
						ON 	  tblRate.CompanyID = p_companyId
							 AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
							 AND tblTempVendorRate.Code = tblRate.Code
							AND  tblTempVendorRate.ProcessId = p_processId
							AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
	         		 SET
                    tblRate.Interval1 = tblTempVendorRate.Interval1,
                    tblRate.IntervalN = tblTempVendorRate.IntervalN
				     WHERE
                		     tblTempVendorRate.Interval1 IS NOT NULL
							 AND tblTempVendorRate.IntervalN IS NOT NULL
                		 AND
							  (
								  tblRate.Interval1 != tblTempVendorRate.Interval1
							  OR
								  tblRate.IntervalN != tblTempVendorRate.IntervalN
							  );




			/* 10. Update INTERVAL, ConnectionFee,  */

            UPDATE tblVendorRate
                INNER JOIN tblRate
                    ON tblVendorRate.RateId = tblRate.RateId
                    AND tblVendorRate.AccountId = p_accountId
                    AND tblVendorRate.TrunkId = p_trunkId
                INNER JOIN tmp_TempVendorRate_ as tblTempVendorRate
                    ON tblRate.Code = tblTempVendorRate.Code
                    AND tblTempVendorRate.TimezonesID = tblVendorRate.TimezonesID
                        AND tblRate.CompanyID = p_companyId
                        AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
                        AND tblVendorRate.RateId = tblRate.RateId
                SET tblVendorRate.ConnectionFee = tblTempVendorRate.ConnectionFee,
                    tblVendorRate.Interval1 = tblTempVendorRate.Interval1,
                    tblVendorRate.IntervalN = tblTempVendorRate.IntervalN
                  --  tblVendorRate.EndDate = tblTempVendorRate.EndDate
                WHERE tblVendorRate.AccountId = p_accountId
                    AND tblVendorRate.TrunkId = p_trunkId ;


						/*IF (FOUND_ROWS() > 0) THEN
								INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Updated Existing Records' );
						END IF;*/

			/* 11. Update VendorBlocking  */

            IF  p_forbidden = 1 OR p_dialstringid > 0
				    THEN
                INSERT INTO tblVendorBlocking
                (
                    `AccountId`,
                    `RateId`,
                    `TrunkID`,
                    `TimezonesID`,
                    `BlockedBy`
                )
                SELECT distinct
                    p_accountId as AccountId,
                    tblRate.RateID as RateId,
                    p_trunkId as TrunkID,
                    tblTempVendorRate.TimezonesID AS TimezonesID,
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
                        AND vb.TimezonesID = tblTempVendorRate.TimezonesID
                WHERE tblTempVendorRate.Forbidden IN('B')
                    AND vb.VendorBlockingId is null;

            DELETE tblVendorBlocking
                FROM tblVendorBlocking
                INNER JOIN(
                    select VendorBlockingId
                    FROM `tblVendorBlocking` tv
                    INNER JOIN(
                        SELECT
                            tblRate.RateId as RateId,
                            tblTempVendorRate.TimezonesID
                        FROM tmp_TempVendorRate_ as tblTempVendorRate
                        INNER JOIN tblRate
                            ON tblRate.Code = tblTempVendorRate.Code
                                AND tblRate.CompanyID = p_companyId
                                AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
                        WHERE tblTempVendorRate.Forbidden IN('UB')
                    )tv1 on  tv.AccountId=p_accountId
                    AND tv.TrunkID=p_trunkId
                    AND tv.TimezonesID=tv1.TimezonesID
                    AND tv.RateId = tv1.RateID
                )vb2 on vb2.VendorBlockingId = tblVendorBlocking.VendorBlockingId;
				END IF;

		/* 11. Update VendorPreference  */

		IF  p_preference = 1
		THEN
            INSERT INTO tblVendorPreference
            (
                 `AccountId`
                 ,`Preference`
                 ,`RateId`
                 ,`TrunkID`
                 ,`TimezonesID`
                 ,`CreatedBy`
                 ,`created_at`
            )
            SELECT
                 p_accountId AS AccountId,
                 tblTempVendorRate.Preference as Preference,
                 tblRate.RateID AS RateId,
                  p_trunkId AS TrunkID,
                  tblTempVendorRate.TimezonesID as TimezonesID,
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
                    AND vp.TimezonesID = tblTempVendorRate.TimezonesID
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
                    AND tblVendorPreference.TimezonesID = tblTempVendorRate.TimezonesID
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
                    	  AND tblVendorPreference.TimezonesID = tblTempVendorRate.TimezonesID
							  AND  tblTempVendorRate.Preference IS NOT NULL
							  AND  tblTempVendorRate.Preference = ''
							  AND tblVendorPreference.VendorPreferenceID IS NOT NULL;

				END IF;


		/* 12. Delete rates which are same in file   */

			-- delete rates which are not increase/decreased  (rates = rates)
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
                    AND tblVendorRate.TimezonesID = tblTempVendorRate.TimezonesID
                    AND tblTempVendorRate.Rate = tblVendorRate.Rate
                    /*AND (
                        tblVendorRate.EffectiveDate = tblTempVendorRate.EffectiveDate
                        OR
                        (
                            DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempVendorRate.EffectiveDate, '%Y-%m-%d')
                        )
                        OR 1 = (CASE
                            WHEN tblTempVendorRate.EffectiveDate > NOW() THEN 1
                            ELSE 0
                        END)
                    )*/
            WHERE  tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block');

				/*IF (FOUND_ROWS() > 0) THEN
					INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - Discarded no change records' );
				END IF;*/



            SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

            SELECT CurrencyID into v_AccountCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblAccount WHERE AccountID=p_accountId);
            SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);

		/* 13. update currency   */

            /*UPDATE tmp_TempVendorRate_ as tblTempVendorRate
            JOIN tblRate
                ON tblRate.Code = tblTempVendorRate.Code
                    AND tblRate.CompanyID = p_companyId
                    AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
            JOIN tblVendorRate
                ON tblVendorRate.RateId = tblRate.RateId
                    AND tblVendorRate.AccountId = p_accountId
                    AND tblVendorRate.TrunkId = p_trunkId
				    SET tblVendorRate.Rate = IF (
                    p_CurrencyID > 0,
                    CASE WHEN p_CurrencyID = v_AccountCurrencyID_
                    THEN
                       tblTempVendorRate.Rate
                    WHEN  p_CurrencyID = v_CompanyCurrencyID_
                    THEN
                    (
                        ( tblTempVendorRate.Rate  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ and CompanyID = p_companyId ) )
                    )
                    ELSE
                    (
                        (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ AND CompanyID = p_companyId )
                            *
                        (tblTempVendorRate.Rate  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))
                    )
                    END ,
                    tblTempVendorRate.Rate
                )
            WHERE tblTempVendorRate.Rate <> tblVendorRate.Rate
                AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
                AND DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempVendorRate.EffectiveDate, '%Y-%m-%d');

 				SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();*/

            UPDATE tmp_TempVendorRate_ as tblTempVendorRate
            JOIN tblRate
                ON tblRate.Code = tblTempVendorRate.Code
                    AND tblRate.CompanyID = p_companyId
                    AND tblRate.CodeDeckId = tblTempVendorRate.CodeDeckId
            JOIN tblVendorRate
                ON tblVendorRate.RateId = tblRate.RateId
                    AND tblVendorRate.AccountId = p_accountId
                    AND tblVendorRate.TrunkId = p_trunkId
                    AND tblVendorRate.TimezonesID = tblTempVendorRate.TimezonesID
				    SET tblVendorRate.EndDate = NOW()
            WHERE tblTempVendorRate.Rate <> tblVendorRate.Rate
                AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
                AND DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempVendorRate.EffectiveDate, '%Y-%m-%d');

				-- archive rates which has EndDate <= today
				call prc_ArchiveOldVendorRate(p_accountId,p_trunkId,NULL,p_UserName);


		/* 13. insert new rates   */

            INSERT INTO tblVendorRate (
                AccountId,
                TrunkID,
                TimezonesID,
                RateId,
                Rate,
                RateN,
                EffectiveDate,
                EndDate,
                ConnectionFee,
                Interval1,
                IntervalN
            )
            SELECT DISTINCT
                p_accountId,
                p_trunkId,
                tblTempVendorRate.TimezonesID,
                tblRate.RateID,
                IF (
                    p_CurrencyID > 0,
                    CASE WHEN p_CurrencyID = v_AccountCurrencyID_
                    THEN
                       tblTempVendorRate.Rate
                    WHEN  p_CurrencyID = v_CompanyCurrencyID_
                    THEN
                    (
                        ( tblTempVendorRate.Rate  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ and CompanyID = p_companyId ) )
                    )
                    ELSE
                    (
                        (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ AND CompanyID = p_companyId )
                            *
                        (tblTempVendorRate.Rate  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))
                    )
                    END ,
                    tblTempVendorRate.Rate
                ) AS Rate,
                IF (
                    p_CurrencyID > 0,
                    CASE WHEN p_CurrencyID = v_AccountCurrencyID_
                    THEN
                       tblTempVendorRate.RateN
                    WHEN  p_CurrencyID = v_CompanyCurrencyID_
                    THEN
                    (
                        ( tblTempVendorRate.RateN  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ and CompanyID = p_companyId ) )
                    )
                    ELSE
                    (
                        (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_AccountCurrencyID_ AND CompanyID = p_companyId )
                            *
                        (tblTempVendorRate.RateN  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))
                    )
                    END ,
                    tblTempVendorRate.RateN
                ) AS RateN,
                tblTempVendorRate.EffectiveDate,
                tblTempVendorRate.EndDate,
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
                    AND tblVendorRate.TimezonesID = tblTempVendorRate.TimezonesID
                    AND tblTempVendorRate.EffectiveDate = tblVendorRate.EffectiveDate
            WHERE tblVendorRate.VendorRateID IS NULL
                AND tblTempVendorRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
                AND tblTempVendorRate.EffectiveDate >= DATE_FORMAT (NOW(), '%Y-%m-%d');

					SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

				/*IF (FOUND_ROWS() > 0) THEN
					INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(FOUND_ROWS() , ' - New Records Inserted.' );
				END IF;
				*/

			/* 13. update enddate in old rates */


			-- loop through effective date to update end date
			DROP TEMPORARY TABLE IF EXISTS tmp_EffectiveDates_;
			CREATE TEMPORARY TABLE tmp_EffectiveDates_ (
				RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
				EffectiveDate  Date
			);
			INSERT INTO tmp_EffectiveDates_ (EffectiveDate)
				SELECT distinct
					EffectiveDate
				FROM
					(	select distinct EffectiveDate
								from 	tblVendorRate
								WHERE
								AccountId = p_accountId
								AND TrunkId = p_trunkId
								Group By EffectiveDate
								order by EffectiveDate desc
					) tmp


					,(SELECT @row_num := 0) x;


			SET v_pointer_ = 1;
			SET v_rowCount_ = ( SELECT COUNT(*) FROM tmp_EffectiveDates_ );

			IF v_rowCount_ > 0 THEN

				WHILE v_pointer_ <= v_rowCount_
				DO

					SET @EffectiveDate = ( SELECT EffectiveDate FROM tmp_EffectiveDates_ WHERE RowID = v_pointer_ );
					SET @row_num = 0;

				UPDATE  tblVendorRate vr1
	         	inner join
	         	(
						select
			         	AccountID,
			         	RateID,
			         	TrunkID,
			         	TimezonesID,
	   		      	EffectiveDate
	      	   	FROM tblVendorRate
		                    WHERE AccountId = p_accountId
		                   		 AND TrunkId = p_trunkId
		            				AND EffectiveDate =   @EffectiveDate
		         	order by EffectiveDate desc

	         	) tmpvr
	         	on
	         	vr1.AccountID = tmpvr.AccountID
	         	AND vr1.TrunkID  	=       	tmpvr.TrunkID
	         	AND vr1.TimezonesID = tmpvr.TimezonesID
	         	AND vr1.RateID  	=        	tmpvr.RateID
	         	AND vr1.EffectiveDate 	< tmpvr.EffectiveDate
	         	SET
	         	vr1.EndDate = @EffectiveDate
	         	where
	         		vr1.AccountId = p_accountId
						AND vr1.TrunkID = p_trunkId
					--	AND vr1.EffectiveDate < @EffectiveDate
						AND vr1.EndDate is null;


					SET v_pointer_ = v_pointer_ + 1;


				END WHILE;

			END IF;


		END IF;

   INSERT INTO tmp_JobLog_ (Message) 	 	SELECT CONCAT(v_AffectedRecords_ , ' Records Uploaded ' );

	-- archive rates which has EndDate <= today
	call prc_ArchiveOldVendorRate(p_accountId,p_trunkId,NULL,p_UserName);


 	 SELECT * FROM tmp_JobLog_;
    DELETE  FROM tblTempVendorRate WHERE  ProcessId = p_processId;
    DELETE  FROM tblVendorRateChangeLog WHERE ProcessID = p_processId;

	 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;