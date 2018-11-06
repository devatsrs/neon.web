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