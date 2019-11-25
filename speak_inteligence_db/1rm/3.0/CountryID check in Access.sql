use speakintelligentRM;


ALTER TABLE `tblTempRateTableDIDRate`
	ADD COLUMN `CountryID` INT NOT NULL DEFAULT '0' AFTER `DialStringPrefix`;

ALTER TABLE `tblRateTableDIDRateChangeLog`
	ADD COLUMN `CountryID` INT NOT NULL DEFAULT '0' AFTER `created_at`;

ALTER TABLE `tblRate`
	DROP INDEX `IXUnique_CompanyID_Code_CodedeckID`,
	ADD UNIQUE INDEX `IXUnique_CompanyID_Code_CodedeckID` (`CompanyID`, `Code`, `CodeDeckId`, `CountryID`);




DROP PROCEDURE IF EXISTS `prc_WSMapCountryRateTableDIDRate`;
DELIMITER //
CREATE PROCEDURE `prc_WSMapCountryRateTableDIDRate`(
	IN `p_ProcessID` TEXT,
	IN `p_CountryMapping` INT,
	IN `p_OriginationCountryMapping` INT
)
ThisSP:BEGIN

	DECLARE v_Country_Error_ INT DEFAULT 0;
	DECLARE v_OCountry_Error_ INT DEFAULT 0;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
	CREATE TEMPORARY TABLE tmp_JobLog_ (
		Message longtext
	);

	IF p_CountryMapping = 1
	THEN
		SELECT
			COUNT(*) INTO v_Country_Error_
		FROM
			tblTempRateTableDIDRate temp
		LEFT JOIN
			tblCountry c ON (c.Country=temp.CountryCode OR FIND_IN_SET(temp.CountryCode,c.Keywords) != 0)
		WHERE
			temp.ProcessID = p_ProcessID AND
			temp.CountryCode IS NOT NULL AND
			temp.CountryCode != '' AND
			c.CountryID IS NULL;

		IF v_Country_Error_ = 0
		THEN
			UPDATE
				tblTempRateTableDIDRate temp
			LEFT JOIN
				tblCountry c ON (c.Country=temp.CountryCode OR FIND_IN_SET(temp.CountryCode,c.Keywords) != 0)
			SET
				temp.CountryCode = c.Prefix,
				temp.CountryID = c.CountryID
			WHERE
				temp.ProcessID = p_ProcessID AND
				temp.CountryCode IS NOT NULL AND
				temp.CountryCode != '' AND
				c.CountryID IS NOT NULL;
		ELSE
			INSERT INTO tmp_JobLog_ (Message)
			SELECT DISTINCT
				CONCAT(temp.CountryCode , ' Country NOT FOUND IN DATABASE')
			FROM
				tblTempRateTableDIDRate temp
			LEFT JOIN
				tblCountry c ON (c.Country=temp.CountryCode OR FIND_IN_SET(temp.CountryCode,c.Keywords) != 0)
			WHERE
				temp.ProcessID = p_ProcessID AND
				temp.CountryCode IS NOT NULL AND
				temp.CountryCode != '' AND
				c.CountryID IS NULL;
		END IF;
	END IF;

	IF p_OriginationCountryMapping = 1
	THEN
		SELECT
			COUNT(*) INTO v_OCountry_Error_
		FROM
			tblTempRateTableDIDRate temp
		LEFT JOIN
			tblCountry c ON (c.Country=temp.OriginationCountryCode OR FIND_IN_SET(temp.OriginationCountryCode,c.Keywords) != 0)
		WHERE
			temp.ProcessID = p_ProcessID AND
			temp.OriginationCountryCode IS NOT NULL AND
			temp.OriginationCountryCode != '' AND
			c.CountryID IS NULL;

		IF v_Country_Error_ = 0
		THEN
			UPDATE
				tblTempRateTableDIDRate temp
			LEFT JOIN
				tblCountry c ON (c.Country=temp.OriginationCountryCode OR FIND_IN_SET(temp.OriginationCountryCode,c.Keywords) != 0)
			SET
				temp.OriginationCountryCode = c.Prefix
			WHERE
				temp.ProcessID = p_ProcessID AND
				temp.OriginationCountryCode IS NOT NULL AND
				temp.OriginationCountryCode != '' AND
				c.CountryID IS NULL;
		ELSE
			INSERT INTO tmp_JobLog_ (Message)
			SELECT DISTINCT
				CONCAT(temp.OriginationCountryCode , ' Origination Country NOT FOUND IN DATABASE')
			FROM
				tblTempRateTableDIDRate temp
			LEFT JOIN
				tblCountry c ON (c.Country=temp.OriginationCountryCode OR FIND_IN_SET(temp.OriginationCountryCode,c.Keywords) != 0)
			WHERE
				temp.ProcessID = p_ProcessID AND
				temp.OriginationCountryCode IS NOT NULL AND
				temp.OriginationCountryCode != '' AND
				c.CountryID IS NOT NULL;
		END IF;
	END IF;

	SELECT * FROM tmp_JobLog_;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_WSProcessRateTableDIDRate`;
DELIMITER //
CREATE PROCEDURE `prc_WSProcessRateTableDIDRate`(
	IN `p_RateTableId` INT,
	IN `p_replaceAllRates` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_processId` VARCHAR(200),
	IN `p_addNewCodesToCodeDeck` INT,
	IN `p_companyId` INT,
	IN `p_dialstringid` INT,
	IN `p_dialcodeSeparator` VARCHAR(50),
	IN `p_seperatecolumn` INT,
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
	DECLARE v_RateApprovalProcess_ INT;
	DECLARE v_RateTableAppliedTo_ INT;

	DECLARE v_pointer_ INT;
	DECLARE v_rowCount_ INT;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SELECT Value INTO v_RateApprovalProcess_ FROM tblCompanySetting WHERE CompanyID = p_companyId AND `Key`='RateApprovalProcess';
	SELECT AppliedTo INTO v_RateTableAppliedTo_ FROM tblRateTable WHERE RateTableID = p_RateTableId;

	DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
	CREATE TEMPORARY TABLE tmp_JobLog_ (
		Message longtext
	);

    DROP TEMPORARY TABLE IF EXISTS tmp_TempTimezones_;
    CREATE TEMPORARY TABLE tmp_TempTimezones_ (
        TimezonesID INT
    );

	DROP TEMPORARY TABLE IF EXISTS tmp_split_RateTableDIDRate_;
	CREATE TEMPORARY TABLE tmp_split_RateTableDIDRate_ (
		`TempRateTableDIDRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NOT NULL DEFAULT '',
		`OneOffCost` decimal(18,6) DEFAULT NULL,
	  	`MonthlyCost` decimal(18,6) DEFAULT NULL,
	  	`CostPerCall` decimal(18,6) DEFAULT NULL,
	  	`CostPerMinute` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerCall` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerMinute` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerCall` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerMinute` decimal(18,6) DEFAULT NULL,
	  	`Surcharges` decimal(18,6) DEFAULT NULL,
	  	`Chargeback` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostAmount` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostPercentage` decimal(18,6) DEFAULT NULL,
	  	`RegistrationCostPerNumber` decimal(18,6) DEFAULT NULL,
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`DialStringPrefix` varchar(500) ,
		`CountryID` INT NOT NULL DEFAULT '0' ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_OriginationCode (`OriginationCode`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableDIDRate_;
	CREATE TEMPORARY TABLE tmp_TempRateTableDIDRate_ (
		TempRateTableDIDRateID int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NOT NULL DEFAULT '',
		`OneOffCost` decimal(18,6) DEFAULT NULL,
	  	`MonthlyCost` decimal(18,6) DEFAULT NULL,
	  	`CostPerCall` decimal(18,6) DEFAULT NULL,
	  	`CostPerMinute` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerCall` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerMinute` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerCall` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerMinute` decimal(18,6) DEFAULT NULL,
	  	`Surcharges` decimal(18,6) DEFAULT NULL,
	  	`Chargeback` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostAmount` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostPercentage` decimal(18,6) DEFAULT NULL,
	  	`RegistrationCostPerNumber` decimal(18,6) DEFAULT NULL,
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`DialStringPrefix` varchar(500) ,
		`CountryID` INT NOT NULL DEFAULT '0' ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_OriginationCode (`OriginationCode`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_Delete_RateTableDIDRate;
	CREATE TEMPORARY TABLE tmp_Delete_RateTableDIDRate (
		RateTableDIDRateID INT,
		RateTableId INT,
		TimezonesID INT,
		OriginationRateID INT,
		OriginationCode VARCHAR(50),
		OriginationDescription VARCHAR(200),
		RateId INT,
		Code VARCHAR(50),
		Description VARCHAR(200),
		City varchar(50) NOT NULL DEFAULT '',
		Tariff varchar(50) NOT NULL DEFAULT '',
		AccessType varchar(200) NOT NULL DEFAULT '',
		OneOffCost decimal(18,6) DEFAULT NULL,
	  	MonthlyCost decimal(18,6) DEFAULT NULL,
	  	CostPerCall decimal(18,6) DEFAULT NULL,
	  	CostPerMinute decimal(18,6) DEFAULT NULL,
	  	SurchargePerCall decimal(18,6) DEFAULT NULL,
	  	SurchargePerMinute decimal(18,6) DEFAULT NULL,
	  	OutpaymentPerCall decimal(18,6) DEFAULT NULL,
	  	OutpaymentPerMinute decimal(18,6) DEFAULT NULL,
	  	Surcharges decimal(18,6) DEFAULT NULL,
	  	Chargeback decimal(18,6) DEFAULT NULL,
	  	CollectionCostAmount decimal(18,6) DEFAULT NULL,
	  	CollectionCostPercentage decimal(18,6) DEFAULT NULL,
	  	RegistrationCostPerNumber decimal(18,6) DEFAULT NULL,
		OneOffCostCurrency INT(11) NULL DEFAULT NULL,
		MonthlyCostCurrency INT(11) NULL DEFAULT NULL,
		CostPerCallCurrency INT(11) NULL DEFAULT NULL,
		CostPerMinuteCurrency INT(11) NULL DEFAULT NULL,
		SurchargePerCallCurrency INT(11) NULL DEFAULT NULL,
		SurchargePerMinuteCurrency INT(11) NULL DEFAULT NULL,
		OutpaymentPerCallCurrency INT(11) NULL DEFAULT NULL,
		OutpaymentPerMinuteCurrency INT(11) NULL DEFAULT NULL,
		SurchargesCurrency INT(11) NULL DEFAULT NULL,
		ChargebackCurrency INT(11) NULL DEFAULT NULL,
		CollectionCostAmountCurrency INT(11) NULL DEFAULT NULL,
		RegistrationCostPerNumberCurrency INT(11) NULL DEFAULT NULL,
		EffectiveDate DATETIME,
		EndDate Datetime ,
		deleted_at DATETIME,
		`CountryID` INT NOT NULL DEFAULT '0' ,
		INDEX tmp_RateTableDIDRateDiscontinued_RateTableDIDRateID (`RateTableDIDRateID`)
	);

	CALL  prc_RateTableDIDRateCheckDialstringAndDupliacteCode(p_companyId,p_processId,p_dialstringid,p_effectiveImmediately,p_dialcodeSeparator,p_seperatecolumn);

	SELECT COUNT(*) AS COUNT INTO newstringcode from tmp_JobLog_;

 	INSERT INTO tmp_TempTimezones_
 	SELECT DISTINCT TimezonesID from tmp_TempRateTableDIDRate_;


	IF newstringcode = 0
	THEN

		IF (SELECT count(*) FROM tblRateTableDIDRateChangeLog WHERE ProcessID = p_processId ) > 0
		THEN

			UPDATE
				tblRateTableDIDRate vr
			INNER JOIN tblRateTableDIDRateChangeLog  vrcl
			on vrcl.RateTableDIDRateID = vr.RateTableDIDRateID
			SET
				vr.EndDate = IFNULL(vrcl.EndDate,date(now()))
			WHERE vrcl.ProcessID = p_processId
				AND vrcl.`Action`  ='Deleted';


			UPDATE tmp_TempRateTableDIDRate_ tblTempRateTableDIDRate
			JOIN tblRateTableDIDRateChangeLog vrcl
				ON  vrcl.ProcessId = p_processId
				AND vrcl.Code = tblTempRateTableDIDRate.Code
				AND vrcl.CountryID = tblTempRateTableDIDRate.CountryID
				AND vrcl.OriginationCode = tblTempRateTableDIDRate.OriginationCode
				AND vrcl.City = tblTempRateTableDIDRate.City
				AND vrcl.Tariff = tblTempRateTableDIDRate.Tariff
				AND vrcl.AccessType = tblTempRateTableDIDRate.AccessType
				AND vrcl.TimezonesID = tblTempRateTableDIDRate.TimezonesID
				AND vrcl.EffectiveDate = tblTempRateTableDIDRate.EffectiveDate
			SET
				tblTempRateTableDIDRate.EndDate = vrcl.EndDate
			WHERE
				vrcl.`Action` = 'Deleted'
				AND vrcl.EndDate IS NOT NULL ;


		END IF;


		IF  p_replaceAllRates = 1
		THEN

			UPDATE tblRateTableDIDRate
				SET tblRateTableDIDRate.EndDate = date(now())
			WHERE RateTableId = p_RateTableId;

		END IF;


		-- complete file
		IF p_list_option = 1
		THEN

			INSERT INTO tmp_Delete_RateTableDIDRate(
				RateTableDIDRateID,
				RateTableId,
				TimezonesID,
				OriginationRateID,
				OriginationCode,
				OriginationDescription,
				RateId,
				Code,
				Description,
				City,
				Tariff,
            AccessType,
				OneOffCost,
				MonthlyCost,
				CostPerCall,
				CostPerMinute,
				SurchargePerCall,
				SurchargePerMinute,
				OutpaymentPerCall,
				OutpaymentPerMinute,
				Surcharges,
				Chargeback,
				CollectionCostAmount,
				CollectionCostPercentage,
				RegistrationCostPerNumber,
				OneOffCostCurrency,
				MonthlyCostCurrency,
				CostPerCallCurrency,
				CostPerMinuteCurrency,
				SurchargePerCallCurrency,
				SurchargePerMinuteCurrency,
				OutpaymentPerCallCurrency,
				OutpaymentPerMinuteCurrency,
				SurchargesCurrency,
				ChargebackCurrency,
				CollectionCostAmountCurrency,
				RegistrationCostPerNumberCurrency,
				EffectiveDate,
				EndDate,
				deleted_at
			)
			SELECT DISTINCT
				tblRateTableDIDRate.RateTableDIDRateID,
				p_RateTableId AS RateTableId,
				tblRateTableDIDRate.TimezonesID,
				tblRateTableDIDRate.OriginationRateID,
				OriginationRate.Code AS OriginationCode,
				OriginationRate.Description AS OriginationDescription,
				tblRateTableDIDRate.RateId,
				tblRate.Code,
				tblRate.Description,
				tblRateTableDIDRate.City,
				tblRateTableDIDRate.Tariff,
            tblRateTableDIDRate.AccessType,
				tblRateTableDIDRate.OneOffCost,
				tblRateTableDIDRate.MonthlyCost,
				tblRateTableDIDRate.CostPerCall,
				tblRateTableDIDRate.CostPerMinute,
				tblRateTableDIDRate.SurchargePerCall,
				tblRateTableDIDRate.SurchargePerMinute,
				tblRateTableDIDRate.OutpaymentPerCall,
				tblRateTableDIDRate.OutpaymentPerMinute,
				tblRateTableDIDRate.Surcharges,
				tblRateTableDIDRate.Chargeback,
				tblRateTableDIDRate.CollectionCostAmount,
				tblRateTableDIDRate.CollectionCostPercentage,
				tblRateTableDIDRate.RegistrationCostPerNumber,
				tblRateTableDIDRate.OneOffCostCurrency,
				tblRateTableDIDRate.MonthlyCostCurrency,
				tblRateTableDIDRate.CostPerCallCurrency,
				tblRateTableDIDRate.CostPerMinuteCurrency,
				tblRateTableDIDRate.SurchargePerCallCurrency,
				tblRateTableDIDRate.SurchargePerMinuteCurrency,
				tblRateTableDIDRate.OutpaymentPerCallCurrency,
				tblRateTableDIDRate.OutpaymentPerMinuteCurrency,
				tblRateTableDIDRate.SurchargesCurrency,
				tblRateTableDIDRate.ChargebackCurrency,
				tblRateTableDIDRate.CollectionCostAmountCurrency,
				tblRateTableDIDRate.RegistrationCostPerNumberCurrency,
				tblRateTableDIDRate.EffectiveDate,
				IFNULL(tblRateTableDIDRate.EndDate,date(now())) ,
				now() AS deleted_at
			FROM tblRateTableDIDRate
			JOIN tblRate
				ON tblRate.RateID = tblRateTableDIDRate.RateId
				AND tblRate.CompanyID = p_companyId
			LEFT JOIN tblRate AS OriginationRate
				ON OriginationRate.RateID = tblRateTableDIDRate.OriginationRateID
				AND OriginationRate.CompanyID = p_companyId
		  	-- JOIN tmp_TempTimezones_
		  	--	ON tmp_TempTimezones_.TimezonesID = tblRateTableDIDRate.TimezonesID
			LEFT JOIN tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
				ON (tblTempRateTableDIDRate.Code = tblRate.Code AND (tblTempRateTableDIDRate.CountryID = 0 OR tblTempRateTableDIDRate.CountryID = tblRate.CountryID))
				AND ((tblTempRateTableDIDRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableDIDRate.OriginationCode = OriginationRate.Code))
				AND tblTempRateTableDIDRate.TimezonesID = tblRateTableDIDRate.TimezonesID
				AND tblTempRateTableDIDRate.City = tblRateTableDIDRate.City
				AND tblTempRateTableDIDRate.Tariff = tblRateTableDIDRate.Tariff
				AND tblTempRateTableDIDRate.AccessType = tblRateTableDIDRate.AccessType
				AND  tblTempRateTableDIDRate.ProcessId = p_processId
				AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
			WHERE tblRateTableDIDRate.RateTableId = p_RateTableId
				AND tblTempRateTableDIDRate.Code IS NULL
				AND ( tblRateTableDIDRate.EndDate is NULL OR tblRateTableDIDRate.EndDate <= date(now()) )
			ORDER BY RateTableDIDRateID ASC;


			UPDATE tblRateTableDIDRate
			JOIN tmp_Delete_RateTableDIDRate ON tblRateTableDIDRate.RateTableDIDRateID = tmp_Delete_RateTableDIDRate.RateTableDIDRateID
				SET tblRateTableDIDRate.EndDate = date(now())
			WHERE
				tblRateTableDIDRate.RateTableId = p_RateTableId;

			/*
			UPDATE tblRateTableDIDRate
			SET EndDate = date(now())
			WHERE RateTableId = p_RateTableId;
			*/
		END IF;



		IF ( (SELECT count(*) FROM tblRateTableDIDRate WHERE  RateTableId = p_RateTableId AND EndDate <= NOW() )  > 0  ) THEN

			call prc_ArchiveOldRateTableDIDRate(p_RateTableId, NULL,p_UserName);

		END IF;

		DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableDIDRate_2;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempRateTableDIDRate_2 AS (SELECT * FROM tmp_TempRateTableDIDRate_);

		IF  p_addNewCodesToCodeDeck = 1
		THEN
			-- Destination Code
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
					tblTempRateTableDIDRate.Code,
					MAX(tblTempRateTableDIDRate.Description) AS Description,
					MAX(tblTempRateTableDIDRate.CodeDeckId) AS CodeDeckId,
					1 AS Interval1,
					1 AS IntervalN
				FROM tmp_TempRateTableDIDRate_  as tblTempRateTableDIDRate
				LEFT JOIN tblRate
					ON tblRate.Code = tblTempRateTableDIDRate.Code
					AND tblRate.CompanyID = p_companyId
					AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
					AND (tblTempRateTableDIDRate.CountryID = 0 OR tblRate.CountryID = tblTempRateTableDIDRate.CountryID)
				WHERE tblRate.RateID IS NULL
					AND tblTempRateTableDIDRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
				GROUP BY
					tblTempRateTableDIDRate.Code
			) vc;

			-- Origination Code
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
					tblTempRateTableDIDRate.OriginationCode AS Code,
					MAX(tblTempRateTableDIDRate.OriginationDescription) AS Description,
					MAX(tblTempRateTableDIDRate.CodeDeckId) AS CodeDeckId,
					1 AS Interval1,
					1 AS IntervalN
				FROM tmp_TempRateTableDIDRate_  as tblTempRateTableDIDRate
				LEFT JOIN tblRate
					ON tblRate.Code = tblTempRateTableDIDRate.OriginationCode
					AND tblRate.CompanyID = p_companyId
					AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
				WHERE tblRate.RateID IS NULL
					AND tblTempRateTableDIDRate.OriginationCode IS NOT NULL AND tblTempRateTableDIDRate.OriginationCode != ''
					AND tblTempRateTableDIDRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
				GROUP BY
					tblTempRateTableDIDRate.OriginationCode
			) vc;

		ELSE
			SELECT GROUP_CONCAT(code) into errormessage FROM(
				SELECT DISTINCT
					c.Code as code, 1 as a
				FROM
				(
					SELECT DISTINCT
						temp.Code,
						MAX(temp.Description) AS Description
					FROM
					(
						SELECT DISTINCT
							tblTempRateTableDIDRate.Code,
							tblTempRateTableDIDRate.Description
						FROM tmp_TempRateTableDIDRate_  as tblTempRateTableDIDRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableDIDRate.Code
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
							AND (tblTempRateTableDIDRate.CountryID = 0 OR tblRate.CountryID = tblTempRateTableDIDRate.CountryID)
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')

						UNION ALL

						SELECT DISTINCT
							tblTempRateTableDIDRate.OriginationCode AS Code,
							tblTempRateTableDIDRate.OriginationDescription AS Description
						FROM tmp_TempRateTableDIDRate_2  as tblTempRateTableDIDRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableDIDRate.OriginationCode
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
					) temp
					GROUP BY Code
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
						temp.Code,
						MAX(temp.Description) AS Description
					FROM
					(
						SELECT DISTINCT
							tblTempRateTableDIDRate.Code,
							tblTempRateTableDIDRate.Description
						FROM tmp_TempRateTableDIDRate_  as tblTempRateTableDIDRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableDIDRate.Code
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
							AND (tblTempRateTableDIDRate.CountryID = 0 OR tblRate.CountryID = tblTempRateTableDIDRate.CountryID)
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')

						UNION ALL

						SELECT DISTINCT
							tblTempRateTableDIDRate.OriginationCode AS Code,
							tblTempRateTableDIDRate.OriginationDescription AS Description
						FROM tmp_TempRateTableDIDRate_2  as tblTempRateTableDIDRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableDIDRate.OriginationCode
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
					) temp
					GROUP BY Code
				) as tbl;
			END IF;
		END IF;


		UPDATE tblRateTableDIDRate
		INNER JOIN tblRate
			ON tblRate.RateID = tblRateTableDIDRate.RateId
			AND tblRate.CompanyID = p_companyId
		LEFT JOIN tblRate AS OriginationRate
			ON OriginationRate.RateID = tblRateTableDIDRate.OriginationRateID
			AND OriginationRate.CompanyID = p_companyId
		INNER JOIN tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
			ON tblRate.Code = tblTempRateTableDIDRate.Code AND (tblTempRateTableDIDRate.CountryID = 0 OR tblRate.CountryID = tblTempRateTableDIDRate.CountryID)
			AND ((tblTempRateTableDIDRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableDIDRate.OriginationCode = OriginationRate.Code))
			AND tblTempRateTableDIDRate.TimezonesID = tblRateTableDIDRate.TimezonesID
			AND tblTempRateTableDIDRate.City = tblRateTableDIDRate.City
			AND tblTempRateTableDIDRate.Tariff = tblRateTableDIDRate.Tariff
			AND tblTempRateTableDIDRate.AccessType = tblRateTableDIDRate.AccessType
			AND tblTempRateTableDIDRate.Change IN ('Delete', 'R', 'D', 'Blocked', 'Block')
		SET tblRateTableDIDRate.EndDate = IFNULL(tblTempRateTableDIDRate.EndDate,date(now()))
		WHERE tblRateTableDIDRate.RateTableId = p_RateTableId;


		DELETE tblTempRateTableDIDRate
		FROM tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableDIDRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
			AND (tblTempRateTableDIDRate.CountryID = 0 OR tblRate.CountryID = tblTempRateTableDIDRate.CountryID)
		LEFT JOIN tblRate AS OriginationRate
			ON OriginationRate.Code = tblTempRateTableDIDRate.OriginationCode
			AND OriginationRate.CompanyID = p_companyId
			AND OriginationRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
		JOIN tblRateTableDIDRate
			ON tblRateTableDIDRate.RateId = tblRate.RateId
			AND ((IFNULL(tblRateTableDIDRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableDIDRate.OriginationRateID = OriginationRate.RateID))
			AND tblRateTableDIDRate.RateTableId = p_RateTableId
			AND tblRateTableDIDRate.TimezonesID = tblTempRateTableDIDRate.TimezonesID
			AND tblRateTableDIDRate.City = tblTempRateTableDIDRate.City
			AND tblRateTableDIDRate.Tariff = tblTempRateTableDIDRate.Tariff
			AND tblRateTableDIDRate.AccessType = tblTempRateTableDIDRate.AccessType
			AND IFNULL(tblTempRateTableDIDRate.OneOffCost,0) = IFNULL(tblRateTableDIDRate.OneOffCost,0)
        	AND IFNULL(tblTempRateTableDIDRate.MonthlyCost,0) = IFNULL(tblRateTableDIDRate.MonthlyCost,0)
        	AND IFNULL(tblTempRateTableDIDRate.CostPerCall,0) = IFNULL(tblRateTableDIDRate.CostPerCall,0)
        	AND IFNULL(tblTempRateTableDIDRate.CostPerMinute,0) = IFNULL(tblRateTableDIDRate.CostPerMinute,0)
        	AND IFNULL(tblTempRateTableDIDRate.SurchargePerCall,0) = IFNULL(tblRateTableDIDRate.SurchargePerCall,0)
        	AND IFNULL(tblTempRateTableDIDRate.SurchargePerMinute,0) = IFNULL(tblRateTableDIDRate.SurchargePerMinute,0)
        	AND IFNULL(tblTempRateTableDIDRate.OutpaymentPerCall,0) = IFNULL(tblRateTableDIDRate.OutpaymentPerCall,0)
        	AND IFNULL(tblTempRateTableDIDRate.OutpaymentPerMinute,0) = IFNULL(tblRateTableDIDRate.OutpaymentPerMinute,0)
        	AND IFNULL(tblTempRateTableDIDRate.Surcharges,0) = IFNULL(tblRateTableDIDRate.Surcharges,0)
        	AND IFNULL(tblTempRateTableDIDRate.Chargeback,0) = IFNULL(tblRateTableDIDRate.Chargeback,0)
        	AND IFNULL(tblTempRateTableDIDRate.CollectionCostAmount,0) = IFNULL(tblRateTableDIDRate.CollectionCostAmount,0)
        	AND IFNULL(tblTempRateTableDIDRate.CollectionCostPercentage,0) = IFNULL(tblRateTableDIDRate.CollectionCostPercentage,0)
        	AND IFNULL(tblTempRateTableDIDRate.RegistrationCostPerNumber,0) = IFNULL(tblRateTableDIDRate.RegistrationCostPerNumber,0)
        	AND IFNULL(tblTempRateTableDIDRate.OneOffCostCurrency,0) = IFNULL(tblRateTableDIDRate.OneOffCostCurrency,0)
			AND IFNULL(tblTempRateTableDIDRate.MonthlyCostCurrency,0) = IFNULL(tblRateTableDIDRate.MonthlyCostCurrency,0)
			AND IFNULL(tblTempRateTableDIDRate.CostPerCallCurrency,0) = IFNULL(tblRateTableDIDRate.CostPerCallCurrency,0)
			AND IFNULL(tblTempRateTableDIDRate.CostPerMinuteCurrency,0) = IFNULL(tblRateTableDIDRate.CostPerMinuteCurrency,0)
			AND IFNULL(tblTempRateTableDIDRate.SurchargePerCallCurrency,0) = IFNULL(tblRateTableDIDRate.SurchargePerCallCurrency,0)
			AND IFNULL(tblTempRateTableDIDRate.SurchargePerMinuteCurrency,0) = IFNULL(tblRateTableDIDRate.SurchargePerMinuteCurrency,0)
			AND IFNULL(tblTempRateTableDIDRate.OutpaymentPerCallCurrency,0) = IFNULL(tblRateTableDIDRate.OutpaymentPerCallCurrency,0)
			AND IFNULL(tblTempRateTableDIDRate.OutpaymentPerMinuteCurrency,0) = IFNULL(tblRateTableDIDRate.OutpaymentPerMinuteCurrency,0)
			AND IFNULL(tblTempRateTableDIDRate.SurchargesCurrency,0) = IFNULL(tblRateTableDIDRate.SurchargesCurrency,0)
			AND IFNULL(tblTempRateTableDIDRate.ChargebackCurrency,0) = IFNULL(tblRateTableDIDRate.ChargebackCurrency,0)
			AND IFNULL(tblTempRateTableDIDRate.CollectionCostAmountCurrency,0) = IFNULL(tblRateTableDIDRate.CollectionCostAmountCurrency,0)
			AND IFNULL(tblTempRateTableDIDRate.RegistrationCostPerNumberCurrency,0) = IFNULL(tblRateTableDIDRate.RegistrationCostPerNumberCurrency,0)
		WHERE
			tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block');


		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

		SELECT CurrencyID into v_RateTableCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyID FROM tblRateTable WHERE RateTableId=p_RateTableId);
		SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);


		UPDATE tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableDIDRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
			AND (tblTempRateTableDIDRate.CountryID = 0 OR tblRate.CountryID = tblTempRateTableDIDRate.CountryID)
		LEFT JOIN tblRate AS OriginationRate
			ON OriginationRate.Code = tblTempRateTableDIDRate.OriginationCode
			AND OriginationRate.CompanyID = p_companyId
			AND OriginationRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
		JOIN tblRateTableDIDRate
			ON tblRateTableDIDRate.RateId = tblRate.RateId
			AND tblRateTableDIDRate.RateTableId = p_RateTableId
			/*
				this one condition removed at 2019-09-06 as per SI Requirement
				if upload any timezones rate with paritial file upload then all old rates on all timezones for that prefix need to deleted.
			*/
		--	AND tblRateTableDIDRate.TimezonesID = tblTempRateTableDIDRate.TimezonesID
			AND tblRateTableDIDRate.City = tblTempRateTableDIDRate.City
			AND tblRateTableDIDRate.Tariff = tblTempRateTableDIDRate.Tariff
			AND tblRateTableDIDRate.AccessType = tblTempRateTableDIDRate.AccessType
		SET tblRateTableDIDRate.EndDate = NOW()
		WHERE
			tblRateTableDIDRate.RateId = tblRate.RateId
			AND ((IFNULL(tblRateTableDIDRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableDIDRate.OriginationRateID = OriginationRate.RateID))
			AND (
				tblTempRateTableDIDRate.City <> tblRateTableDIDRate.City
				OR tblTempRateTableDIDRate.Tariff <> tblRateTableDIDRate.Tariff
				OR tblTempRateTableDIDRate.AccessType <> tblRateTableDIDRate.AccessType
				OR IFNULL(tblTempRateTableDIDRate.OneOffCost,0) <> IFNULL(tblRateTableDIDRate.OneOffCost,0)
				OR IFNULL(tblTempRateTableDIDRate.MonthlyCost,0) <> IFNULL(tblRateTableDIDRate.MonthlyCost,0)
				OR IFNULL(tblTempRateTableDIDRate.CostPerCall,0) <> IFNULL(tblRateTableDIDRate.CostPerCall,0)
				OR IFNULL(tblTempRateTableDIDRate.CostPerMinute,0) <> IFNULL(tblRateTableDIDRate.CostPerMinute,0)
				OR IFNULL(tblTempRateTableDIDRate.SurchargePerCall,0) <> IFNULL(tblRateTableDIDRate.SurchargePerCall,0)
				OR IFNULL(tblTempRateTableDIDRate.SurchargePerMinute,0) <> IFNULL(tblRateTableDIDRate.SurchargePerMinute,0)
				OR IFNULL(tblTempRateTableDIDRate.OutpaymentPerCall,0) <> IFNULL(tblRateTableDIDRate.OutpaymentPerCall,0)
				OR IFNULL(tblTempRateTableDIDRate.OutpaymentPerMinute,0) <> IFNULL(tblRateTableDIDRate.OutpaymentPerMinute,0)
				OR IFNULL(tblTempRateTableDIDRate.Surcharges,0) <> IFNULL(tblRateTableDIDRate.Surcharges,0)
				OR IFNULL(tblTempRateTableDIDRate.Chargeback,0) <> IFNULL(tblRateTableDIDRate.Chargeback,0)
				OR IFNULL(tblTempRateTableDIDRate.CollectionCostAmount,0) <> IFNULL(tblRateTableDIDRate.CollectionCostAmount,0)
				OR IFNULL(tblTempRateTableDIDRate.CollectionCostPercentage,0) <> IFNULL(tblRateTableDIDRate.CollectionCostPercentage,0)
				OR IFNULL(tblTempRateTableDIDRate.RegistrationCostPerNumber,0) <> IFNULL(tblRateTableDIDRate.RegistrationCostPerNumber,0)
				OR IFNULL(tblTempRateTableDIDRate.OneOffCostCurrency,0) <> IFNULL(tblRateTableDIDRate.OneOffCostCurrency,0)
				OR IFNULL(tblTempRateTableDIDRate.MonthlyCostCurrency,0) <> IFNULL(tblRateTableDIDRate.MonthlyCostCurrency,0)
				OR IFNULL(tblTempRateTableDIDRate.CostPerCallCurrency,0) <> IFNULL(tblRateTableDIDRate.CostPerCallCurrency,0)
				OR IFNULL(tblTempRateTableDIDRate.CostPerMinuteCurrency,0) <> IFNULL(tblRateTableDIDRate.CostPerMinuteCurrency,0)
				OR IFNULL(tblTempRateTableDIDRate.SurchargePerCallCurrency,0) <> IFNULL(tblRateTableDIDRate.SurchargePerCallCurrency,0)
				OR IFNULL(tblTempRateTableDIDRate.SurchargePerMinuteCurrency,0) <> IFNULL(tblRateTableDIDRate.SurchargePerMinuteCurrency,0)
				OR IFNULL(tblTempRateTableDIDRate.OutpaymentPerCallCurrency,0) <> IFNULL(tblRateTableDIDRate.OutpaymentPerCallCurrency,0)
				OR IFNULL(tblTempRateTableDIDRate.OutpaymentPerMinuteCurrency,0) <> IFNULL(tblRateTableDIDRate.OutpaymentPerMinuteCurrency,0)
				OR IFNULL(tblTempRateTableDIDRate.SurchargesCurrency,0) <> IFNULL(tblRateTableDIDRate.SurchargesCurrency,0)
				OR IFNULL(tblTempRateTableDIDRate.ChargebackCurrency,0) <> IFNULL(tblRateTableDIDRate.ChargebackCurrency,0)
				OR IFNULL(tblTempRateTableDIDRate.CollectionCostAmountCurrency,0) <> IFNULL(tblRateTableDIDRate.CollectionCostAmountCurrency,0)
				OR IFNULL(tblTempRateTableDIDRate.RegistrationCostPerNumberCurrency,0) <> IFNULL(tblRateTableDIDRate.RegistrationCostPerNumberCurrency,0)
			)
			AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
			AND
			-- DATE_FORMAT (tblRateTableDIDRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTableDIDRate.EffectiveDate, '%Y-%m-%d')
			(
				( -- if future rates then delete same date existing records
					DATE_FORMAT(tblTempRateTableDIDRate.EffectiveDate, '%Y-%m-%d') > CURDATE() AND
					DATE_FORMAT(tblRateTableDIDRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT(tblTempRateTableDIDRate.EffectiveDate, '%Y-%m-%d')
				)
				OR
				( -- if current rates then delete current or older records
					DATE_FORMAT(tblTempRateTableDIDRate.EffectiveDate, '%Y-%m-%d') <= CURDATE() AND
					DATE_FORMAT(tblRateTableDIDRate.EffectiveDate, '%Y-%m-%d') <= DATE_FORMAT(tblTempRateTableDIDRate.EffectiveDate, '%Y-%m-%d')
				)
			);
-- leave ThisSP;

		call prc_ArchiveOldRateTableDIDRate(p_RateTableId, NULL,p_UserName);

		SET @stm1 = CONCAT('
			INSERT INTO tblRateTableDIDRate (
				RateTableId,
				TimezonesID,
				OriginationRateID,
				RateId,
				City,
				Tariff,
				AccessType,
				OneOffCost,
				MonthlyCost,
				CostPerCall,
				CostPerMinute,
				SurchargePerCall,
				SurchargePerMinute,
				OutpaymentPerCall,
				OutpaymentPerMinute,
				Surcharges,
				Chargeback,
				CollectionCostAmount,
				CollectionCostPercentage,
				RegistrationCostPerNumber,
				OneOffCostCurrency,
				MonthlyCostCurrency,
				CostPerCallCurrency,
				CostPerMinuteCurrency,
				SurchargePerCallCurrency,
				SurchargePerMinuteCurrency,
				OutpaymentPerCallCurrency,
				OutpaymentPerMinuteCurrency,
				SurchargesCurrency,
				ChargebackCurrency,
				CollectionCostAmountCurrency,
				RegistrationCostPerNumberCurrency,
				EffectiveDate,
				EndDate,
				ApprovedStatus
			)
			SELECT DISTINCT
				',p_RateTableId,' AS RateTableId,
				tblTempRateTableDIDRate.TimezonesID,
				IFNULL(OriginationRate.RateID,0) AS OriginationRateID,
				tblRate.RateID,
				tblTempRateTableDIDRate.City,
				tblTempRateTableDIDRate.Tariff,
				tblTempRateTableDIDRate.AccessType,
		');

		SET @stm2 = '';
		IF p_CurrencyID > 0 AND p_CurrencyID != v_RateTableCurrencyID_
        THEN
			IF p_CurrencyID = v_CompanyCurrencyID_
            THEN
				SET @stm2 = CONCAT('
				    ( tblTempRateTableDIDRate.OneOffCost  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS OneOffCost,
				    ( tblTempRateTableDIDRate.MonthlyCost  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS MonthlyCost,
				    ( tblTempRateTableDIDRate.CostPerCall  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS CostPerCall,
				    ( tblTempRateTableDIDRate.CostPerMinute  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS CostPerMinute,
				    ( tblTempRateTableDIDRate.SurchargePerCall  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS SurchargePerCall,
				    ( tblTempRateTableDIDRate.SurchargePerMinute  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS SurchargePerMinute,
				    ( tblTempRateTableDIDRate.OutpaymentPerCall  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS OutpaymentPerCall,
				    ( tblTempRateTableDIDRate.OutpaymentPerMinute  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS OutpaymentPerMinute,
				    ( tblTempRateTableDIDRate.Surcharges  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS Surcharges,
				    ( tblTempRateTableDIDRate.Chargeback  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS Chargeback,
				    ( tblTempRateTableDIDRate.CollectionCostAmount  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS CollectionCostAmount,
				    ( tblTempRateTableDIDRate.CollectionCostPercentage  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS CollectionCostPercentage,
				    ( tblTempRateTableDIDRate.RegistrationCostPerNumber  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS RegistrationCostPerNumber,
				');
			ELSE
				SET @stm2 = CONCAT('
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.OneOffCost  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS OneOffCost,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.MonthlyCost  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS MonthlyCost,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.CostPerCall  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS CostPerCall,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.CostPerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS CostPerMinute,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.SurchargePerCall  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS SurchargePerCall,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.SurchargePerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS SurchargePerMinute,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.OutpaymentPerCall  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS OutpaymentPerCall,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.OutpaymentPerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS OutpaymentPerMinute,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.Surcharges  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS Surcharges,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.Chargeback  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS Chargeback,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.CollectionCostAmount  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS CollectionCostAmount,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.CollectionCostPercentage  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS CollectionCostPercentage,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.RegistrationCostPerNumber  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS RegistrationCostPerNumber,
				');
			END IF;
        ELSE
            SET @stm2 = CONCAT('
                    tblTempRateTableDIDRate.OneOffCost AS OneOffCost,
                    tblTempRateTableDIDRate.MonthlyCost AS MonthlyCost,
                    tblTempRateTableDIDRate.CostPerCall AS CostPerCall,
                    tblTempRateTableDIDRate.CostPerMinute AS CostPerMinute,
                    tblTempRateTableDIDRate.SurchargePerCall AS SurchargePerCall,
                    tblTempRateTableDIDRate.SurchargePerMinute AS SurchargePerMinute,
                    tblTempRateTableDIDRate.OutpaymentPerCall AS OutpaymentPerCall,
                    tblTempRateTableDIDRate.OutpaymentPerMinute AS OutpaymentPerMinute,
                    tblTempRateTableDIDRate.Surcharges AS Surcharges,
                    tblTempRateTableDIDRate.Chargeback AS Chargeback,
                    tblTempRateTableDIDRate.CollectionCostAmount AS CollectionCostAmount,
                    tblTempRateTableDIDRate.CollectionCostPercentage AS CollectionCostPercentage,
                    tblTempRateTableDIDRate.RegistrationCostPerNumber AS RegistrationCostPerNumber,
                ');
		END IF;

		SET @stm3 = CONCAT('
				tblTempRateTableDIDRate.OneOffCostCurrency,
				tblTempRateTableDIDRate.MonthlyCostCurrency,
				tblTempRateTableDIDRate.CostPerCallCurrency,
				tblTempRateTableDIDRate.CostPerMinuteCurrency,
				tblTempRateTableDIDRate.SurchargePerCallCurrency,
				tblTempRateTableDIDRate.SurchargePerMinuteCurrency,
				tblTempRateTableDIDRate.OutpaymentPerCallCurrency,
				tblTempRateTableDIDRate.OutpaymentPerMinuteCurrency,
				tblTempRateTableDIDRate.SurchargesCurrency,
				tblTempRateTableDIDRate.ChargebackCurrency,
				tblTempRateTableDIDRate.CollectionCostAmountCurrency,
				tblTempRateTableDIDRate.RegistrationCostPerNumberCurrency,
				tblTempRateTableDIDRate.EffectiveDate,
				tblTempRateTableDIDRate.EndDate,
				 -- if rate table is not vendor rate table and Rate Approval Process is on then rate will be upload as not approved
				IF(',v_RateTableAppliedTo_,' !=2,IF(',v_RateApprovalProcess_,'=1,0,1),1) AS ApprovedStatus
			FROM tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
			JOIN tblRate
				ON tblRate.Code = tblTempRateTableDIDRate.Code
				AND tblRate.CompanyID = ',p_companyId,'
				AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
				AND (tblTempRateTableDIDRate.CountryID = 0 OR tblRate.CountryID = tblTempRateTableDIDRate.CountryID)
			LEFT JOIN tblRate AS OriginationRate
				ON OriginationRate.Code = tblTempRateTableDIDRate.OriginationCode
				AND OriginationRate.CompanyID = ',p_companyId,'
				AND OriginationRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
			LEFT JOIN tblRateTableDIDRate
				ON tblRate.RateID = tblRateTableDIDRate.RateId
				AND ((IFNULL(tblRateTableDIDRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableDIDRate.OriginationRateID = OriginationRate.RateID))
				AND tblRateTableDIDRate.RateTableId = ',p_RateTableId,'
				AND tblRateTableDIDRate.TimezonesID = tblTempRateTableDIDRate.TimezonesID
				AND tblRateTableDIDRate.City = tblTempRateTableDIDRate.City
				AND tblRateTableDIDRate.Tariff = tblTempRateTableDIDRate.Tariff
				AND tblRateTableDIDRate.AccessType = tblTempRateTableDIDRate.AccessType
				AND tblTempRateTableDIDRate.EffectiveDate = tblRateTableDIDRate.EffectiveDate
			WHERE tblRateTableDIDRate.RateTableDIDRateID IS NULL
				AND tblTempRateTableDIDRate.Change NOT IN ("Delete", "R", "D", "Blocked","Block")
				AND tblTempRateTableDIDRate.EffectiveDate >= DATE_FORMAT(NOW(), "%Y-%m-%d");
		');

		SET @stm4 = CONCAT(@stm1,@stm2,@stm3);

		PREPARE stm4 FROM @stm4;
		EXECUTE stm4;
		DEALLOCATE PREPARE stm4;

		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();


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
			from 	tblRateTableDIDRate
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

				UPDATE  tblRateTableDIDRate vr1
				inner join
				(
					select
						RateTableId,
						OriginationRateID,
						RateID,
						EffectiveDate,
						TimezonesID,
						City,
						Tariff,
						AccessType
					FROM tblRateTableDIDRate
					WHERE RateTableId = p_RateTableId
						AND EffectiveDate =   @EffectiveDate
					order by EffectiveDate desc
				) tmpvr
				on
					vr1.RateTableId = tmpvr.RateTableId
					AND vr1.OriginationRateID = tmpvr.OriginationRateID
					AND vr1.RateID = tmpvr.RateID
					AND vr1.TimezonesID = tmpvr.TimezonesID
					AND vr1.City = tmpvr.City
					AND vr1.Tariff = tmpvr.Tariff
					AND vr1.AccessType = tmpvr.AccessType
					AND vr1.EffectiveDate < tmpvr.EffectiveDate
				SET
					vr1.EndDate = @EffectiveDate
				where
					vr1.RateTableId = p_RateTableId

					AND vr1.EndDate is null;


				SET v_pointer_ = v_pointer_ + 1;

			END WHILE;

		END IF;

	END IF;

	INSERT INTO tmp_JobLog_ (Message) 	 	SELECT CONCAT(v_AffectedRecords_ , ' Records Uploaded ' );

	call prc_ArchiveOldRateTableDIDRate(p_RateTableId, NULL,p_UserName);

	DELETE  FROM tblTempRateTableDIDRate WHERE  ProcessId = p_processId;
	DELETE  FROM tblRateTableDIDRateChangeLog WHERE ProcessID = p_processId;
	SELECT * FROM tmp_JobLog_;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_WSProcessRateTableDIDRateAA`;
DELIMITER //
CREATE PROCEDURE `prc_WSProcessRateTableDIDRateAA`(
	IN `p_RateTableId` INT,
	IN `p_replaceAllRates` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_processId` VARCHAR(200),
	IN `p_addNewCodesToCodeDeck` INT,
	IN `p_companyId` INT,
	IN `p_dialstringid` INT,
	IN `p_dialcodeSeparator` VARCHAR(50),
	IN `p_seperatecolumn` INT,
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

	DROP TEMPORARY TABLE IF EXISTS tmp_split_RateTableDIDRate_;
	CREATE TEMPORARY TABLE tmp_split_RateTableDIDRate_ (
		`TempRateTableDIDRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NOT NULL DEFAULT '',
		`OneOffCost` decimal(18,6) DEFAULT NULL,
	  	`MonthlyCost` decimal(18,6) DEFAULT NULL,
	  	`CostPerCall` decimal(18,6) DEFAULT NULL,
	  	`CostPerMinute` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerCall` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerMinute` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerCall` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerMinute` decimal(18,6) DEFAULT NULL,
	  	`Surcharges` decimal(18,6) DEFAULT NULL,
	  	`Chargeback` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostAmount` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostPercentage` decimal(18,6) DEFAULT NULL,
	  	`RegistrationCostPerNumber` decimal(18,6) DEFAULT NULL,
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`DialStringPrefix` varchar(500) ,
		`CountryID` INT NOT NULL DEFAULT '0' ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_OriginationCode (`OriginationCode`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableDIDRate_;
	CREATE TEMPORARY TABLE tmp_TempRateTableDIDRate_ (
		TempRateTableDIDRateID INT,
		RateTableDIDRateID INT DEFAULT 0,
		`CodeDeckId` INT ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NOT NULL DEFAULT '',
		`OneOffCost` decimal(18,6) DEFAULT NULL,
	  	`MonthlyCost` decimal(18,6) DEFAULT NULL,
	  	`CostPerCall` decimal(18,6) DEFAULT NULL,
	  	`CostPerMinute` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerCall` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerMinute` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerCall` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerMinute` decimal(18,6) DEFAULT NULL,
	  	`Surcharges` decimal(18,6) DEFAULT NULL,
	  	`Chargeback` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostAmount` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostPercentage` decimal(18,6) DEFAULT NULL,
	  	`RegistrationCostPerNumber` decimal(18,6) DEFAULT NULL,
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`DialStringPrefix` varchar(500) ,
		`ApprovedStatus` TINYINT(4) DEFAULT 0,
		`CountryID` INT NOT NULL DEFAULT '0' ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_OriginationCode (`OriginationCode`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_Delete_RateTableDIDRate;
	CREATE TEMPORARY TABLE tmp_Delete_RateTableDIDRate (
		TempRateTableDIDRateID INT DEFAULT 0,
		RateTableDIDRateID INT,
		`CodeDeckId` INT ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NOT NULL DEFAULT '',
		`OneOffCost` decimal(18,6) DEFAULT NULL,
	  	`MonthlyCost` decimal(18,6) DEFAULT NULL,
	  	`CostPerCall` decimal(18,6) DEFAULT NULL,
	  	`CostPerMinute` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerCall` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerMinute` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerCall` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerMinute` decimal(18,6) DEFAULT NULL,
	  	`Surcharges` decimal(18,6) DEFAULT NULL,
	  	`Chargeback` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostAmount` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostPercentage` decimal(18,6) DEFAULT NULL,
	  	`RegistrationCostPerNumber` decimal(18,6) DEFAULT NULL,
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`DialStringPrefix` varchar(500) ,
		`ApprovedStatus` TINYINT(4) DEFAULT 0,
		`CountryID` INT NOT NULL DEFAULT '0' ,
		INDEX tmp_RateTableDIDRateDiscontinued_RateTableDIDRateID (`RateTableDIDRateID`)
	);

	CALL  prc_RateTableDIDRateCheckDialstringAndDupliacteCode(p_companyId,p_processId,p_dialstringid,p_effectiveImmediately,p_dialcodeSeparator,p_seperatecolumn);

	SELECT COUNT(*) AS COUNT INTO newstringcode from tmp_JobLog_;

 	INSERT INTO tmp_TempTimezones_
 	SELECT DISTINCT TimezonesID from tmp_TempRateTableDIDRate_;

	IF newstringcode = 0
	THEN

		-- complete file
		IF p_list_option = 1
		THEN

			INSERT INTO tmp_Delete_RateTableDIDRate(
				RateTableDIDRateID,
				CodeDeckId,
				TimezonesID,
				OriginationCode,
				OriginationDescription,
				Code,
				Description,
				City,
				Tariff,
				AccessType,
				OneOffCost,
				MonthlyCost,
				CostPerCall,
				CostPerMinute,
				SurchargePerCall,
				SurchargePerMinute,
				OutpaymentPerCall,
				OutpaymentPerMinute,
				Surcharges,
				Chargeback,
				CollectionCostAmount,
				CollectionCostPercentage,
				RegistrationCostPerNumber,
				OneOffCostCurrency,
				MonthlyCostCurrency,
				CostPerCallCurrency,
				CostPerMinuteCurrency,
				SurchargePerCallCurrency,
				SurchargePerMinuteCurrency,
				OutpaymentPerCallCurrency,
				OutpaymentPerMinuteCurrency,
				SurchargesCurrency,
				ChargebackCurrency,
				CollectionCostAmountCurrency,
				RegistrationCostPerNumberCurrency,
				EffectiveDate,
				EndDate,
				`Change`,
				ProcessId,
				DialStringPrefix,
				ApprovedStatus
			)
			SELECT DISTINCT
				tblRateTableDIDRate.RateTableDIDRateID,
				tblRateTable.CodeDeckId AS CodeDeckId,
				tblRateTableDIDRate.TimezonesID,
				OriginationRate.Code AS OriginationCode,
				OriginationRate.Description AS OriginationDescription,
				tblRate.Code,
				tblRate.Description,
				tblRateTableDIDRate.City,
				tblRateTableDIDRate.Tariff,
				tblRateTableDIDRate.AccessType,
				tblRateTableDIDRate.OneOffCost,
				tblRateTableDIDRate.MonthlyCost,
				tblRateTableDIDRate.CostPerCall,
				tblRateTableDIDRate.CostPerMinute,
				tblRateTableDIDRate.SurchargePerCall,
				tblRateTableDIDRate.SurchargePerMinute,
				tblRateTableDIDRate.OutpaymentPerCall,
				tblRateTableDIDRate.OutpaymentPerMinute,
				tblRateTableDIDRate.Surcharges,
				tblRateTableDIDRate.Chargeback,
				tblRateTableDIDRate.CollectionCostAmount,
				tblRateTableDIDRate.CollectionCostPercentage,
				tblRateTableDIDRate.RegistrationCostPerNumber,
				tblRateTableDIDRate.OneOffCostCurrency,
				tblRateTableDIDRate.MonthlyCostCurrency,
				tblRateTableDIDRate.CostPerCallCurrency,
				tblRateTableDIDRate.CostPerMinuteCurrency,
				tblRateTableDIDRate.SurchargePerCallCurrency,
				tblRateTableDIDRate.SurchargePerMinuteCurrency,
				tblRateTableDIDRate.OutpaymentPerCallCurrency,
				tblRateTableDIDRate.OutpaymentPerMinuteCurrency,
				tblRateTableDIDRate.SurchargesCurrency,
				tblRateTableDIDRate.ChargebackCurrency,
				tblRateTableDIDRate.CollectionCostAmountCurrency,
				tblRateTableDIDRate.RegistrationCostPerNumberCurrency,
				tblRateTableDIDRate.EffectiveDate,
				NULL AS EndDate,
				'Delete' AS `Change`,
				p_processId AS ProcessId,
				'' AS DialStringPrefix,
				3 AS ApprovedStatus
			FROM
				tblRateTableDIDRate
			JOIN
				tblRateTable ON tblRateTable.RateTableId = tblRateTableDIDRate.RateTableId
			JOIN
				tblRate ON tblRate.RateID = tblRateTableDIDRate.RateId AND tblRate.CompanyID = p_companyId
			LEFT JOIN
				tblRate AS OriginationRate ON OriginationRate.RateID = tblRateTableDIDRate.OriginationRateID AND OriginationRate.CompanyID = p_companyId
		  	-- JOIN tmp_TempTimezones_
		  	--	ON tmp_TempTimezones_.TimezonesID = tblRateTableDIDRate.TimezonesID
			LEFT JOIN
				tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
				ON tblTempRateTableDIDRate.Code = tblRate.Code AND (tblTempRateTableDIDRate.CountryID = 0 OR tblTempRateTableDIDRate.CountryID = tblRate.CountryID)
				AND ((tblTempRateTableDIDRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableDIDRate.OriginationCode = OriginationRate.Code))
				AND tblTempRateTableDIDRate.TimezonesID = tblRateTableDIDRate.TimezonesID
				AND tblTempRateTableDIDRate.City = tblRateTableDIDRate.City
				AND tblTempRateTableDIDRate.Tariff = tblRateTableDIDRate.Tariff
				AND tblTempRateTableDIDRate.AccessType = tblRateTableDIDRate.AccessType
				AND tblTempRateTableDIDRate.ProcessId = p_processId
				AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block')
			WHERE
				tblRateTableDIDRate.RateTableId = p_RateTableId
				AND tblTempRateTableDIDRate.Code IS NULL
				AND ( tblRateTableDIDRate.EndDate is NULL OR tblRateTableDIDRate.EndDate <= date(now()) )
			ORDER BY RateTableDIDRateID ASC;

			-- these 2 queries can be merge in single query but due to time limitation this is not done as of now 2019-11-05
			-- can be updated in future

			-- records which are already exist but with different values and will be updated
			INSERT INTO tmp_Delete_RateTableDIDRate(
				RateTableDIDRateID,
				CodeDeckId,
				TimezonesID,
				OriginationCode,
				OriginationDescription,
				Code,
				Description,
				City,
				Tariff,
				AccessType,
				OneOffCost,
				MonthlyCost,
				CostPerCall,
				CostPerMinute,
				SurchargePerCall,
				SurchargePerMinute,
				OutpaymentPerCall,
				OutpaymentPerMinute,
				Surcharges,
				Chargeback,
				CollectionCostAmount,
				CollectionCostPercentage,
				RegistrationCostPerNumber,
				OneOffCostCurrency,
				MonthlyCostCurrency,
				CostPerCallCurrency,
				CostPerMinuteCurrency,
				SurchargePerCallCurrency,
				SurchargePerMinuteCurrency,
				OutpaymentPerCallCurrency,
				OutpaymentPerMinuteCurrency,
				SurchargesCurrency,
				ChargebackCurrency,
				CollectionCostAmountCurrency,
				RegistrationCostPerNumberCurrency,
				EffectiveDate,
				EndDate,
				`Change`,
				ProcessId,
				DialStringPrefix,
				ApprovedStatus
			)
			SELECT DISTINCT
				tblRateTableDIDRate.RateTableDIDRateID,
				tblRateTable.CodeDeckId AS CodeDeckId,
				tblRateTableDIDRate.TimezonesID,
				OriginationRate.Code AS OriginationCode,
				OriginationRate.Description AS OriginationDescription,
				tblRate.Code,
				tblRate.Description,
				tblRateTableDIDRate.City,
				tblRateTableDIDRate.Tariff,
				tblRateTableDIDRate.AccessType,
				tblRateTableDIDRate.OneOffCost,
				tblRateTableDIDRate.MonthlyCost,
				tblRateTableDIDRate.CostPerCall,
				tblRateTableDIDRate.CostPerMinute,
				tblRateTableDIDRate.SurchargePerCall,
				tblRateTableDIDRate.SurchargePerMinute,
				tblRateTableDIDRate.OutpaymentPerCall,
				tblRateTableDIDRate.OutpaymentPerMinute,
				tblRateTableDIDRate.Surcharges,
				tblRateTableDIDRate.Chargeback,
				tblRateTableDIDRate.CollectionCostAmount,
				tblRateTableDIDRate.CollectionCostPercentage,
				tblRateTableDIDRate.RegistrationCostPerNumber,
				tblRateTableDIDRate.OneOffCostCurrency,
				tblRateTableDIDRate.MonthlyCostCurrency,
				tblRateTableDIDRate.CostPerCallCurrency,
				tblRateTableDIDRate.CostPerMinuteCurrency,
				tblRateTableDIDRate.SurchargePerCallCurrency,
				tblRateTableDIDRate.SurchargePerMinuteCurrency,
				tblRateTableDIDRate.OutpaymentPerCallCurrency,
				tblRateTableDIDRate.OutpaymentPerMinuteCurrency,
				tblRateTableDIDRate.SurchargesCurrency,
				tblRateTableDIDRate.ChargebackCurrency,
				tblRateTableDIDRate.CollectionCostAmountCurrency,
				tblRateTableDIDRate.RegistrationCostPerNumberCurrency,
				tblRateTableDIDRate.EffectiveDate,
				NULL AS EndDate,
				'Delete' AS `Change`,
				p_processId AS ProcessId,
				'' AS DialStringPrefix,
				3 AS ApprovedStatus
			FROM
				tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
			JOIN
				tblRate ON tblRate.Code = tblTempRateTableDIDRate.Code
				AND tblRate.CompanyID = p_companyId
				AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
				AND (tblTempRateTableDIDRate.CountryID = 0 OR tblTempRateTableDIDRate.CountryID = tblRate.CountryID)
			LEFT JOIN
				tblRate AS OriginationRate ON OriginationRate.Code = tblTempRateTableDIDRate.OriginationCode
				AND OriginationRate.CompanyID = p_companyId
				AND OriginationRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
			JOIN
				tblRateTableDIDRate ON tblRateTableDIDRate.RateId = tblRate.RateId
				AND ((IFNULL(tblRateTableDIDRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableDIDRate.OriginationRateID = OriginationRate.RateID))
				AND tblRateTableDIDRate.RateTableId = p_RateTableId
				AND tblRateTableDIDRate.TimezonesID = tblTempRateTableDIDRate.TimezonesID
				AND tblRateTableDIDRate.City = tblTempRateTableDIDRate.City
				AND tblRateTableDIDRate.Tariff = tblTempRateTableDIDRate.Tariff
				AND tblRateTableDIDRate.AccessType = tblTempRateTableDIDRate.AccessType
			JOIN
				tblRateTable ON tblRateTable.RateTableId = tblRateTableDIDRate.RateTableId
			WHERE
				tblRateTableDIDRate.RateId = tblRate.RateId
				AND ((IFNULL(tblRateTableDIDRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableDIDRate.OriginationRateID = OriginationRate.RateID))
				AND (
				    tblTempRateTableDIDRate.City <> tblRateTableDIDRate.City
				    OR tblTempRateTableDIDRate.Tariff <> tblRateTableDIDRate.Tariff
				    OR tblTempRateTableDIDRate.AccessType <> tblRateTableDIDRate.AccessType
				    OR IFNULL(tblTempRateTableDIDRate.OneOffCost,0) <> IFNULL(tblRateTableDIDRate.OneOffCost,0)
				    OR IFNULL(tblTempRateTableDIDRate.MonthlyCost,0) <> IFNULL(tblRateTableDIDRate.MonthlyCost,0)
				    OR IFNULL(tblTempRateTableDIDRate.CostPerCall,0) <> IFNULL(tblRateTableDIDRate.CostPerCall,0)
				    OR IFNULL(tblTempRateTableDIDRate.CostPerMinute,0) <> IFNULL(tblRateTableDIDRate.CostPerMinute,0)
				    OR IFNULL(tblTempRateTableDIDRate.SurchargePerCall,0) <> IFNULL(tblRateTableDIDRate.SurchargePerCall,0)
				    OR IFNULL(tblTempRateTableDIDRate.SurchargePerMinute,0) <> IFNULL(tblRateTableDIDRate.SurchargePerMinute,0)
				    OR IFNULL(tblTempRateTableDIDRate.OutpaymentPerCall,0) <> IFNULL(tblRateTableDIDRate.OutpaymentPerCall,0)
				    OR IFNULL(tblTempRateTableDIDRate.OutpaymentPerMinute,0) <> IFNULL(tblRateTableDIDRate.OutpaymentPerMinute,0)
				    OR IFNULL(tblTempRateTableDIDRate.Surcharges,0) <> IFNULL(tblRateTableDIDRate.Surcharges,0)
				    OR IFNULL(tblTempRateTableDIDRate.Chargeback,0) <> IFNULL(tblRateTableDIDRate.Chargeback,0)
				    OR IFNULL(tblTempRateTableDIDRate.CollectionCostAmount,0) <> IFNULL(tblRateTableDIDRate.CollectionCostAmount,0)
				    OR IFNULL(tblTempRateTableDIDRate.CollectionCostPercentage,0) <> IFNULL(tblRateTableDIDRate.CollectionCostPercentage,0)
				    OR IFNULL(tblTempRateTableDIDRate.RegistrationCostPerNumber,0) <> IFNULL(tblRateTableDIDRate.RegistrationCostPerNumber,0)
					OR IFNULL(tblTempRateTableDIDRate.OneOffCostCurrency,0) <> IFNULL(tblRateTableDIDRate.OneOffCostCurrency,0)
					OR IFNULL(tblTempRateTableDIDRate.MonthlyCostCurrency,0) <> IFNULL(tblRateTableDIDRate.MonthlyCostCurrency,0)
					OR IFNULL(tblTempRateTableDIDRate.CostPerCallCurrency,0) <> IFNULL(tblRateTableDIDRate.CostPerCallCurrency,0)
					OR IFNULL(tblTempRateTableDIDRate.CostPerMinuteCurrency,0) <> IFNULL(tblRateTableDIDRate.CostPerMinuteCurrency,0)
					OR IFNULL(tblTempRateTableDIDRate.SurchargePerCallCurrency,0) <> IFNULL(tblRateTableDIDRate.SurchargePerCallCurrency,0)
					OR IFNULL(tblTempRateTableDIDRate.SurchargePerMinuteCurrency,0) <> IFNULL(tblRateTableDIDRate.SurchargePerMinuteCurrency,0)
					OR IFNULL(tblTempRateTableDIDRate.OutpaymentPerCallCurrency,0) <> IFNULL(tblRateTableDIDRate.OutpaymentPerCallCurrency,0)
					OR IFNULL(tblTempRateTableDIDRate.OutpaymentPerMinuteCurrency,0) <> IFNULL(tblRateTableDIDRate.OutpaymentPerMinuteCurrency,0)
					OR IFNULL(tblTempRateTableDIDRate.SurchargesCurrency,0) <> IFNULL(tblRateTableDIDRate.SurchargesCurrency,0)
					OR IFNULL(tblTempRateTableDIDRate.ChargebackCurrency,0) <> IFNULL(tblRateTableDIDRate.ChargebackCurrency,0)
					OR IFNULL(tblTempRateTableDIDRate.CollectionCostAmountCurrency,0) <> IFNULL(tblRateTableDIDRate.CollectionCostAmountCurrency,0)
					OR IFNULL(tblTempRateTableDIDRate.RegistrationCostPerNumberCurrency,0) <> IFNULL(tblRateTableDIDRate.RegistrationCostPerNumberCurrency,0)
				)
				AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
				AND
				-- DATE_FORMAT (tblRateTableDIDRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT (tblTempRateTableDIDRate.EffectiveDate, '%Y-%m-%d')
				(
					( -- if future rates then delete same date existing records
						DATE_FORMAT(tblTempRateTableDIDRate.EffectiveDate, '%Y-%m-%d') > CURDATE() AND
						DATE_FORMAT(tblRateTableDIDRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT(tblTempRateTableDIDRate.EffectiveDate, '%Y-%m-%d')
					)
					OR
					( -- if current rates then delete current or older records
						DATE_FORMAT(tblTempRateTableDIDRate.EffectiveDate, '%Y-%m-%d') <= CURDATE() AND
						DATE_FORMAT(tblRateTableDIDRate.EffectiveDate, '%Y-%m-%d') <= DATE_FORMAT(tblTempRateTableDIDRate.EffectiveDate, '%Y-%m-%d')
					)
				);

		END IF;




		DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableDIDRate_2;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_TempRateTableDIDRate_2 AS (SELECT * FROM tmp_TempRateTableDIDRate_);

		IF  p_addNewCodesToCodeDeck = 1
		THEN
			-- Destination Code
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
					tblTempRateTableDIDRate.Code,
					MAX(tblTempRateTableDIDRate.Description) AS Description,
					MAX(tblTempRateTableDIDRate.CodeDeckId) AS CodeDeckId,
					1 AS Interval1,
					1 AS IntervalN
				FROM tmp_TempRateTableDIDRate_  as tblTempRateTableDIDRate
				LEFT JOIN tblRate
					ON tblRate.Code = tblTempRateTableDIDRate.Code
					AND tblRate.CompanyID = p_companyId
					AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
					AND (tblTempRateTableDIDRate.CountryID = 0 OR tblTempRateTableDIDRate.CountryID = tblRate.CountryID)
				WHERE tblRate.RateID IS NULL
					AND tblTempRateTableDIDRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
				GROUP BY
					tblTempRateTableDIDRate.Code
			) vc;

			-- Origination Code
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
					tblTempRateTableDIDRate.OriginationCode AS Code,
					MAX(tblTempRateTableDIDRate.OriginationDescription) AS Description,
					MAX(tblTempRateTableDIDRate.CodeDeckId) AS CodeDeckId,
					1 AS Interval1,
					1 AS IntervalN
				FROM tmp_TempRateTableDIDRate_  as tblTempRateTableDIDRate
				LEFT JOIN tblRate
					ON tblRate.Code = tblTempRateTableDIDRate.OriginationCode
					AND tblRate.CompanyID = p_companyId
					AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
				WHERE tblRate.RateID IS NULL
					AND tblTempRateTableDIDRate.OriginationCode IS NOT NULL AND tblTempRateTableDIDRate.OriginationCode != ''
					AND tblTempRateTableDIDRate.`Change` NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
				GROUP BY
					tblTempRateTableDIDRate.OriginationCode
			) vc;

		ELSE
			SELECT GROUP_CONCAT(code) into errormessage FROM(
				SELECT DISTINCT
					c.Code as code, 1 as a
				FROM
				(
					SELECT DISTINCT
						temp.Code,
						MAX(temp.Description) AS Description
					FROM
					(
						SELECT DISTINCT
							tblTempRateTableDIDRate.Code,
							tblTempRateTableDIDRate.Description
						FROM tmp_TempRateTableDIDRate_  as tblTempRateTableDIDRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableDIDRate.Code
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
							AND (tblTempRateTableDIDRate.CountryID = 0 OR tblTempRateTableDIDRate.CountryID = tblRate.CountryID)
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')

						UNION ALL

						SELECT DISTINCT
							tblTempRateTableDIDRate.OriginationCode AS Code,
							tblTempRateTableDIDRate.OriginationDescription AS Description
						FROM tmp_TempRateTableDIDRate_2  as tblTempRateTableDIDRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableDIDRate.OriginationCode
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
					) temp
					GROUP BY Code
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
						temp.Code,
						MAX(temp.Description) AS Description
					FROM
					(
						SELECT DISTINCT
							tblTempRateTableDIDRate.Code,
							tblTempRateTableDIDRate.Description
						FROM tmp_TempRateTableDIDRate_  as tblTempRateTableDIDRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableDIDRate.Code
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
							AND (tblTempRateTableDIDRate.CountryID = 0 OR tblTempRateTableDIDRate.CountryID = tblRate.CountryID)
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')

						UNION ALL

						SELECT DISTINCT
							tblTempRateTableDIDRate.OriginationCode AS Code,
							tblTempRateTableDIDRate.OriginationDescription AS Description
						FROM tmp_TempRateTableDIDRate_2  as tblTempRateTableDIDRate
						LEFT JOIN tblRate
							ON tblRate.Code = tblTempRateTableDIDRate.OriginationCode
							AND tblRate.CompanyID = p_companyId
							AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
						WHERE tblRate.RateID IS NULL
							AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
					) temp
					GROUP BY Code
				) as tbl;
			END IF;
		END IF;


		DELETE tblTempRateTableDIDRate
		FROM tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableDIDRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
		LEFT JOIN tblRate AS OriginationRate
			ON OriginationRate.Code = tblTempRateTableDIDRate.OriginationCode
			AND OriginationRate.CompanyID = p_companyId
			AND OriginationRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
			AND (tblTempRateTableDIDRate.CountryID = 0 OR tblTempRateTableDIDRate.CountryID = tblRate.CountryID)
		JOIN tblRateTableDIDRateAA AS tblRateTableDIDRate
			ON tblRateTableDIDRate.RateId = tblRate.RateId
			AND ((IFNULL(tblRateTableDIDRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableDIDRate.OriginationRateID = OriginationRate.RateID))
			AND tblRateTableDIDRate.RateTableId = p_RateTableId
			AND tblRateTableDIDRate.TimezonesID = tblTempRateTableDIDRate.TimezonesID
			AND tblRateTableDIDRate.City = tblTempRateTableDIDRate.City
			AND tblRateTableDIDRate.Tariff = tblTempRateTableDIDRate.Tariff
			AND tblRateTableDIDRate.AccessType = tblTempRateTableDIDRate.AccessType
			AND IFNULL(tblTempRateTableDIDRate.OneOffCost,0) = IFNULL(tblRateTableDIDRate.OneOffCost,0)
        	AND IFNULL(tblTempRateTableDIDRate.MonthlyCost,0) = IFNULL(tblRateTableDIDRate.MonthlyCost,0)
        	AND IFNULL(tblTempRateTableDIDRate.CostPerCall,0) = IFNULL(tblRateTableDIDRate.CostPerCall,0)
        	AND IFNULL(tblTempRateTableDIDRate.CostPerMinute,0) = IFNULL(tblRateTableDIDRate.CostPerMinute,0)
        	AND IFNULL(tblTempRateTableDIDRate.SurchargePerCall,0) = IFNULL(tblRateTableDIDRate.SurchargePerCall,0)
        	AND IFNULL(tblTempRateTableDIDRate.SurchargePerMinute,0) = IFNULL(tblRateTableDIDRate.SurchargePerMinute,0)
        	AND IFNULL(tblTempRateTableDIDRate.OutpaymentPerCall,0) = IFNULL(tblRateTableDIDRate.OutpaymentPerCall,0)
        	AND IFNULL(tblTempRateTableDIDRate.OutpaymentPerMinute,0) = IFNULL(tblRateTableDIDRate.OutpaymentPerMinute,0)
        	AND IFNULL(tblTempRateTableDIDRate.Surcharges,0) = IFNULL(tblRateTableDIDRate.Surcharges,0)
        	AND IFNULL(tblTempRateTableDIDRate.Chargeback,0) = IFNULL(tblRateTableDIDRate.Chargeback,0)
        	AND IFNULL(tblTempRateTableDIDRate.CollectionCostAmount,0) = IFNULL(tblRateTableDIDRate.CollectionCostAmount,0)
        	AND IFNULL(tblTempRateTableDIDRate.CollectionCostPercentage,0) = IFNULL(tblRateTableDIDRate.CollectionCostPercentage,0)
        	AND IFNULL(tblTempRateTableDIDRate.RegistrationCostPerNumber,0) = IFNULL(tblRateTableDIDRate.RegistrationCostPerNumber,0)
        	AND IFNULL(tblTempRateTableDIDRate.OneOffCostCurrency,0) = IFNULL(tblRateTableDIDRate.OneOffCostCurrency,0)
			AND IFNULL(tblTempRateTableDIDRate.MonthlyCostCurrency,0) = IFNULL(tblRateTableDIDRate.MonthlyCostCurrency,0)
			AND IFNULL(tblTempRateTableDIDRate.CostPerCallCurrency,0) = IFNULL(tblRateTableDIDRate.CostPerCallCurrency,0)
			AND IFNULL(tblTempRateTableDIDRate.CostPerMinuteCurrency,0) = IFNULL(tblRateTableDIDRate.CostPerMinuteCurrency,0)
			AND IFNULL(tblTempRateTableDIDRate.SurchargePerCallCurrency,0) = IFNULL(tblRateTableDIDRate.SurchargePerCallCurrency,0)
			AND IFNULL(tblTempRateTableDIDRate.SurchargePerMinuteCurrency,0) = IFNULL(tblRateTableDIDRate.SurchargePerMinuteCurrency,0)
			AND IFNULL(tblTempRateTableDIDRate.OutpaymentPerCallCurrency,0) = IFNULL(tblRateTableDIDRate.OutpaymentPerCallCurrency,0)
			AND IFNULL(tblTempRateTableDIDRate.OutpaymentPerMinuteCurrency,0) = IFNULL(tblRateTableDIDRate.OutpaymentPerMinuteCurrency,0)
			AND IFNULL(tblTempRateTableDIDRate.SurchargesCurrency,0) = IFNULL(tblRateTableDIDRate.SurchargesCurrency,0)
			AND IFNULL(tblTempRateTableDIDRate.ChargebackCurrency,0) = IFNULL(tblRateTableDIDRate.ChargebackCurrency,0)
			AND IFNULL(tblTempRateTableDIDRate.CollectionCostAmountCurrency,0) = IFNULL(tblRateTableDIDRate.CollectionCostAmountCurrency,0)
			AND IFNULL(tblTempRateTableDIDRate.RegistrationCostPerNumberCurrency,0) = IFNULL(tblRateTableDIDRate.RegistrationCostPerNumberCurrency,0)
		WHERE
			tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block');


		-- SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

		SELECT CurrencyID into v_RateTableCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyID FROM tblRateTable WHERE RateTableId=p_RateTableId);
		SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);


		UPDATE tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
		JOIN tblRate
			ON tblRate.Code = tblTempRateTableDIDRate.Code
			AND tblRate.CompanyID = p_companyId
			AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
			AND (tblTempRateTableDIDRate.CountryID = 0 OR tblTempRateTableDIDRate.CountryID = tblRate.CountryID)
		LEFT JOIN tblRate AS OriginationRate
			ON OriginationRate.Code = tblTempRateTableDIDRate.OriginationCode
			AND OriginationRate.CompanyID = p_companyId
			AND OriginationRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
		JOIN tblRateTableDIDRateAA AS tblRateTableDIDRate
			ON tblRateTableDIDRate.RateId = tblRate.RateId
			AND tblRateTableDIDRate.RateTableId = p_RateTableId
			AND tblRateTableDIDRate.TimezonesID = tblTempRateTableDIDRate.TimezonesID
			AND tblRateTableDIDRate.City = tblTempRateTableDIDRate.City
			AND tblRateTableDIDRate.Tariff = tblTempRateTableDIDRate.Tariff
			AND tblRateTableDIDRate.AccessType = tblTempRateTableDIDRate.AccessType
		SET tblRateTableDIDRate.EndDate = NOW()
		WHERE
			tblRateTableDIDRate.RateId = tblRate.RateId
			AND ((IFNULL(tblRateTableDIDRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableDIDRate.OriginationRateID = OriginationRate.RateID))
			AND (
			    tblTempRateTableDIDRate.City <> tblRateTableDIDRate.City
			    OR tblTempRateTableDIDRate.Tariff <> tblRateTableDIDRate.Tariff
			    OR tblTempRateTableDIDRate.AccessType <> tblRateTableDIDRate.AccessType
			    OR IFNULL(tblTempRateTableDIDRate.OneOffCost,0) <> IFNULL(tblRateTableDIDRate.OneOffCost,0)
			    OR IFNULL(tblTempRateTableDIDRate.MonthlyCost,0) <> IFNULL(tblRateTableDIDRate.MonthlyCost,0)
			    OR IFNULL(tblTempRateTableDIDRate.CostPerCall,0) <> IFNULL(tblRateTableDIDRate.CostPerCall,0)
			    OR IFNULL(tblTempRateTableDIDRate.CostPerMinute,0) <> IFNULL(tblRateTableDIDRate.CostPerMinute,0)
			    OR IFNULL(tblTempRateTableDIDRate.SurchargePerCall,0) <> IFNULL(tblRateTableDIDRate.SurchargePerCall,0)
			    OR IFNULL(tblTempRateTableDIDRate.SurchargePerMinute,0) <> IFNULL(tblRateTableDIDRate.SurchargePerMinute,0)
			    OR IFNULL(tblTempRateTableDIDRate.OutpaymentPerCall,0) <> IFNULL(tblRateTableDIDRate.OutpaymentPerCall,0)
			    OR IFNULL(tblTempRateTableDIDRate.OutpaymentPerMinute,0) <> IFNULL(tblRateTableDIDRate.OutpaymentPerMinute,0)
			    OR IFNULL(tblTempRateTableDIDRate.Surcharges,0) <> IFNULL(tblRateTableDIDRate.Surcharges,0)
			    OR IFNULL(tblTempRateTableDIDRate.Chargeback,0) <> IFNULL(tblRateTableDIDRate.Chargeback,0)
			    OR IFNULL(tblTempRateTableDIDRate.CollectionCostAmount,0) <> IFNULL(tblRateTableDIDRate.CollectionCostAmount,0)
			    OR IFNULL(tblTempRateTableDIDRate.CollectionCostPercentage,0) <> IFNULL(tblRateTableDIDRate.CollectionCostPercentage,0)
			    OR IFNULL(tblTempRateTableDIDRate.RegistrationCostPerNumber,0) <> IFNULL(tblRateTableDIDRate.RegistrationCostPerNumber,0)
				OR IFNULL(tblTempRateTableDIDRate.OneOffCostCurrency,0) <> IFNULL(tblRateTableDIDRate.OneOffCostCurrency,0)
				OR IFNULL(tblTempRateTableDIDRate.MonthlyCostCurrency,0) <> IFNULL(tblRateTableDIDRate.MonthlyCostCurrency,0)
				OR IFNULL(tblTempRateTableDIDRate.CostPerCallCurrency,0) <> IFNULL(tblRateTableDIDRate.CostPerCallCurrency,0)
				OR IFNULL(tblTempRateTableDIDRate.CostPerMinuteCurrency,0) <> IFNULL(tblRateTableDIDRate.CostPerMinuteCurrency,0)
				OR IFNULL(tblTempRateTableDIDRate.SurchargePerCallCurrency,0) <> IFNULL(tblRateTableDIDRate.SurchargePerCallCurrency,0)
				OR IFNULL(tblTempRateTableDIDRate.SurchargePerMinuteCurrency,0) <> IFNULL(tblRateTableDIDRate.SurchargePerMinuteCurrency,0)
				OR IFNULL(tblTempRateTableDIDRate.OutpaymentPerCallCurrency,0) <> IFNULL(tblRateTableDIDRate.OutpaymentPerCallCurrency,0)
				OR IFNULL(tblTempRateTableDIDRate.OutpaymentPerMinuteCurrency,0) <> IFNULL(tblRateTableDIDRate.OutpaymentPerMinuteCurrency,0)
				OR IFNULL(tblTempRateTableDIDRate.SurchargesCurrency,0) <> IFNULL(tblRateTableDIDRate.SurchargesCurrency,0)
				OR IFNULL(tblTempRateTableDIDRate.ChargebackCurrency,0) <> IFNULL(tblRateTableDIDRate.ChargebackCurrency,0)
				OR IFNULL(tblTempRateTableDIDRate.CollectionCostAmountCurrency,0) <> IFNULL(tblRateTableDIDRate.CollectionCostAmountCurrency,0)
				OR IFNULL(tblTempRateTableDIDRate.RegistrationCostPerNumberCurrency,0) <> IFNULL(tblRateTableDIDRate.RegistrationCostPerNumberCurrency,0)
			)
			AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked', 'Block')
			AND DATE_FORMAT(tblRateTableDIDRate.EffectiveDate, '%Y-%m-%d') = DATE_FORMAT(tblTempRateTableDIDRate.EffectiveDate, '%Y-%m-%d');


		call prc_ArchiveOldRateTableDIDRateAA(p_RateTableId, NULL,p_UserName);

		SET @stm1 = CONCAT('
			INSERT INTO tblRateTableDIDRateAA (
				RateTableId,
				TimezonesID,
				OriginationRateID,
				RateId,
				City,
				Tariff,
				AccessType,
				OneOffCost,
				MonthlyCost,
				CostPerCall,
				CostPerMinute,
				SurchargePerCall,
				SurchargePerMinute,
				OutpaymentPerCall,
				OutpaymentPerMinute,
				Surcharges,
				Chargeback,
				CollectionCostAmount,
				CollectionCostPercentage,
				RegistrationCostPerNumber,
				OneOffCostCurrency,
				MonthlyCostCurrency,
				CostPerCallCurrency,
				CostPerMinuteCurrency,
				SurchargePerCallCurrency,
				SurchargePerMinuteCurrency,
				OutpaymentPerCallCurrency,
				OutpaymentPerMinuteCurrency,
				SurchargesCurrency,
				ChargebackCurrency,
				CollectionCostAmountCurrency,
				RegistrationCostPerNumberCurrency,
				EffectiveDate,
				EndDate,
				ApprovedStatus,
				RateTableDIDRateID
			)
			SELECT DISTINCT
				',p_RateTableId,' AS RateTableId,
				tblTempRateTableDIDRate.TimezonesID,
				IFNULL(OriginationRate.RateID,0) AS OriginationRateID,
				tblRate.RateID,
				tblTempRateTableDIDRate.City,
				tblTempRateTableDIDRate.Tariff,
				tblTempRateTableDIDRate.AccessType,
		');

		SET @stm2 = '';
		IF p_CurrencyID > 0 AND p_CurrencyID != v_RateTableCurrencyID_
        THEN
			IF p_CurrencyID = v_CompanyCurrencyID_
            THEN
				SET @stm2 = CONCAT('
				    ( tblTempRateTableDIDRate.OneOffCost  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS OneOffCost,
				    ( tblTempRateTableDIDRate.MonthlyCost  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS MonthlyCost,
				    ( tblTempRateTableDIDRate.CostPerCall  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS CostPerCall,
				    ( tblTempRateTableDIDRate.CostPerMinute  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS CostPerMinute,
				    ( tblTempRateTableDIDRate.SurchargePerCall  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS SurchargePerCall,
				    ( tblTempRateTableDIDRate.SurchargePerMinute  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS SurchargePerMinute,
				    ( tblTempRateTableDIDRate.OutpaymentPerCall  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS OutpaymentPerCall,
				    ( tblTempRateTableDIDRate.OutpaymentPerMinute  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS OutpaymentPerMinute,
				    ( tblTempRateTableDIDRate.Surcharges  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS Surcharges,
				    ( tblTempRateTableDIDRate.Chargeback  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS Chargeback,
				    ( tblTempRateTableDIDRate.CollectionCostAmount  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS CollectionCostAmount,
				    ( tblTempRateTableDIDRate.CollectionCostPercentage  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS CollectionCostPercentage,
				    ( tblTempRateTableDIDRate.RegistrationCostPerNumber  * (SELECT Value from tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' and CompanyID = ',p_companyId,' ) ) AS RegistrationCostPerNumber,
				');
			ELSE
				SET @stm2 = CONCAT('
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.OneOffCost  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS OneOffCost,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.MonthlyCost  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS MonthlyCost,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.CostPerCall  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS CostPerCall,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.CostPerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS CostPerMinute,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.SurchargePerCall  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS SurchargePerCall,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.SurchargePerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS SurchargePerMinute,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.OutpaymentPerCall  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS OutpaymentPerCall,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.OutpaymentPerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS OutpaymentPerMinute,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.Surcharges  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS Surcharges,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.Chargeback  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS Chargeback,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.CollectionCostAmount  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS CollectionCostAmount,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.CollectionCostPercentage  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS CollectionCostPercentage,
					(SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',v_RateTableCurrencyID_,' AND CompanyID = ',p_companyId,' ) * (tblTempRateTableDIDRate.RegistrationCostPerNumber  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = ',p_CurrencyID,' AND CompanyID = ',p_companyId,' )) AS RegistrationCostPerNumber,
				');
			END IF;
        ELSE
            SET @stm2 = CONCAT('
                    tblTempRateTableDIDRate.OneOffCost AS OneOffCost,
                    tblTempRateTableDIDRate.MonthlyCost AS MonthlyCost,
                    tblTempRateTableDIDRate.CostPerCall AS CostPerCall,
                    tblTempRateTableDIDRate.CostPerMinute AS CostPerMinute,
                    tblTempRateTableDIDRate.SurchargePerCall AS SurchargePerCall,
                    tblTempRateTableDIDRate.SurchargePerMinute AS SurchargePerMinute,
                    tblTempRateTableDIDRate.OutpaymentPerCall AS OutpaymentPerCall,
                    tblTempRateTableDIDRate.OutpaymentPerMinute AS OutpaymentPerMinute,
                    tblTempRateTableDIDRate.Surcharges AS Surcharges,
                    tblTempRateTableDIDRate.Chargeback AS Chargeback,
                    tblTempRateTableDIDRate.CollectionCostAmount AS CollectionCostAmount,
                    tblTempRateTableDIDRate.CollectionCostPercentage AS CollectionCostPercentage,
                    tblTempRateTableDIDRate.RegistrationCostPerNumber AS RegistrationCostPerNumber,
                ');
		END IF;

		SET @stm3 = CONCAT('
				tblTempRateTableDIDRate.OneOffCostCurrency,
				tblTempRateTableDIDRate.MonthlyCostCurrency,
				tblTempRateTableDIDRate.CostPerCallCurrency,
				tblTempRateTableDIDRate.CostPerMinuteCurrency,
				tblTempRateTableDIDRate.SurchargePerCallCurrency,
				tblTempRateTableDIDRate.SurchargePerMinuteCurrency,
				tblTempRateTableDIDRate.OutpaymentPerCallCurrency,
				tblTempRateTableDIDRate.OutpaymentPerMinuteCurrency,
				tblTempRateTableDIDRate.SurchargesCurrency,
				tblTempRateTableDIDRate.ChargebackCurrency,
				tblTempRateTableDIDRate.CollectionCostAmountCurrency,
				tblTempRateTableDIDRate.RegistrationCostPerNumberCurrency,
				tblTempRateTableDIDRate.EffectiveDate,
				tblTempRateTableDIDRate.EndDate,
				tblTempRateTableDIDRate.ApprovedStatus,
				tblTempRateTableDIDRate.RateTableDIDRateID
			FROM
			(
				SELECT * FROM tmp_TempRateTableDIDRate_
				WHERE tmp_TempRateTableDIDRate_.Change NOT IN ("Delete", "R", "D", "Blocked","Block")
				AND tmp_TempRateTableDIDRate_.EffectiveDate >= DATE_FORMAT(NOW(), "%Y-%m-%d")

				UNION

				SELECT * FROM tmp_Delete_RateTableDIDRate

			) as tblTempRateTableDIDRate
			JOIN tblRate
				ON tblRate.Code = tblTempRateTableDIDRate.Code
				AND tblRate.CompanyID = ',p_companyId,'
				AND tblRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId
				AND (tblTempRateTableDIDRate.CountryID = 0 OR tblTempRateTableDIDRate.CountryID = tblRate.CountryID)
			LEFT JOIN tblRate AS OriginationRate
				ON OriginationRate.Code = tblTempRateTableDIDRate.OriginationCode
				AND OriginationRate.CompanyID = ',p_companyId,'
				AND OriginationRate.CodeDeckId = tblTempRateTableDIDRate.CodeDeckId;
		');

		SET @stm4 = CONCAT(@stm1,@stm2,@stm3);

		PREPARE stm4 FROM @stm4;
		EXECUTE stm4;
		DEALLOCATE PREPARE stm4;

		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();

	END IF;

	INSERT INTO tmp_JobLog_ (Message) 	 	SELECT CONCAT(v_AffectedRecords_ , ' Records Uploaded ' );

	call prc_ArchiveOldRateTableDIDRateAA(p_RateTableId, NULL,p_UserName);

	DELETE  FROM tblTempRateTableDIDRate WHERE  ProcessId = p_processId;
	DELETE  FROM tblRateTableDIDRateChangeLog WHERE ProcessID = p_processId;
	SELECT * FROM tmp_JobLog_;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_WSReviewRateTableDIDRate`;
DELIMITER //
CREATE PROCEDURE `prc_WSReviewRateTableDIDRate`(
	IN `p_RateTableId` INT,
	IN `p_replaceAllRates` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_processId` VARCHAR(200),
	IN `p_addNewCodesToCodeDeck` INT,
	IN `p_companyId` INT,
	IN `p_dialstringid` INT,
	IN `p_dialcodeSeparator` VARCHAR(50),
	IN `p_seperatecolumn` INT,
	IN `p_CurrencyID` INT,
	IN `p_list_option` INT
)
ThisSP:BEGIN


	DECLARE newstringcode INT(11) DEFAULT 0;
	DECLARE v_pointer_ INT;
	DECLARE v_rowCount_ INT;
	DECLARE v_RateTableCurrencyID_ INT;
	DECLARE v_CompanyCurrencyID_ INT;

    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
    CREATE TEMPORARY TABLE tmp_JobLog_ (
        Message longtext
    );

    DROP TEMPORARY TABLE IF EXISTS tmp_split_RateTableDIDRate_;
    CREATE TEMPORARY TABLE tmp_split_RateTableDIDRate_ (
		`TempRateTableDIDRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NOT NULL DEFAULT '',
		`OneOffCost` decimal(18,6) DEFAULT NULL,
	  	`MonthlyCost` decimal(18,6) DEFAULT NULL,
	  	`CostPerCall` decimal(18,6) DEFAULT NULL,
	  	`CostPerMinute` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerCall` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerMinute` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerCall` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerMinute` decimal(18,6) DEFAULT NULL,
	  	`Surcharges` decimal(18,6) DEFAULT NULL,
	  	`Chargeback` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostAmount` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostPercentage` decimal(18,6) DEFAULT NULL,
	  	`RegistrationCostPerNumber` decimal(18,6) DEFAULT NULL,
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`DialStringPrefix` varchar(500) ,
		`CountryID` INT NOT NULL DEFAULT '0' ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_OriginationCode (`OriginationCode`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
    );

    DROP TEMPORARY TABLE IF EXISTS tmp_TempRateTableDIDRate_;
    CREATE TEMPORARY TABLE tmp_TempRateTableDIDRate_ (
		`TempRateTableDIDRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NOT NULL DEFAULT '',
		`OneOffCost` decimal(18,6) DEFAULT NULL,
	  	`MonthlyCost` decimal(18,6) DEFAULT NULL,
	  	`CostPerCall` decimal(18,6) DEFAULT NULL,
	  	`CostPerMinute` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerCall` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerMinute` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerCall` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerMinute` decimal(18,6) DEFAULT NULL,
	  	`Surcharges` decimal(18,6) DEFAULT NULL,
	  	`Chargeback` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostAmount` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostPercentage` decimal(18,6) DEFAULT NULL,
	  	`RegistrationCostPerNumber` decimal(18,6) DEFAULT NULL,
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`DialStringPrefix` varchar(500) ,
		`CountryID` INT NOT NULL DEFAULT '0' ,
		INDEX tmp_EffectiveDate (`EffectiveDate`),
		INDEX tmp_OriginationCode (`OriginationCode`),
		INDEX tmp_Code (`Code`),
		INDEX tmp_CC (`Code`,`Change`),
		INDEX tmp_Change (`Change`)
    );

    CALL  prc_RateTableDIDRateCheckDialstringAndDupliacteCode(p_companyId,p_processId,p_dialstringid,p_effectiveImmediately,p_dialcodeSeparator,p_seperatecolumn);

	ALTER TABLE
		`tmp_TempRateTableDIDRate_`
	ADD Column `NewOneOffCost` decimal(18, 6),
	ADD Column `NewMonthlyCost` decimal(18, 6),
	ADD Column `NewCostPerCall` decimal(18, 6),
	ADD Column `NewCostPerMinute` decimal(18, 6),
	ADD Column `NewSurchargePerCall` decimal(18, 6),
	ADD Column `NewSurchargePerMinute` decimal(18, 6),
	ADD Column `NewOutpaymentPerCall` decimal(18, 6),
	ADD Column `NewOutpaymentPerMinute` decimal(18, 6),
	ADD Column `NewSurcharges` decimal(18, 6),
	ADD Column `NewChargeback` decimal(18, 6),
	ADD Column `NewCollectionCostAmount` decimal(18, 6),
	ADD Column `NewCollectionCostPercentage` decimal(18, 6),
	ADD Column `NewRegistrationCostPerNumber` decimal(18, 6) ;

    SELECT COUNT(*) AS COUNT INTO newstringcode FROM tmp_JobLog_;

    SELECT CurrencyID into v_RateTableCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyID FROM tblRateTable WHERE RateTableId=p_RateTableId);
    SELECT CurrencyID into v_CompanyCurrencyID_ FROM tblCurrency WHERE CurrencyID=(SELECT CurrencyId FROM tblCompany WHERE CompanyID=p_companyId);

	IF p_CurrencyID > 0 AND p_CurrencyID != v_RateTableCurrencyID_
	THEN
		IF p_CurrencyID = v_CompanyCurrencyID_
		THEN
			UPDATE
				tmp_TempRateTableDIDRate_
			SET
				NewOneOffCost = ( OneOffCost  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewMonthlyCost = ( MonthlyCost  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewCostPerCall = ( CostPerCall  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewCostPerMinute = ( CostPerMinute  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewSurchargePerCall = ( SurchargePerCall  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewSurchargePerMinute = ( SurchargePerMinute  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewOutpaymentPerCall = ( OutpaymentPerCall  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewOutpaymentPerMinute = ( OutpaymentPerMinute  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewSurcharges = ( Surcharges  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewChargeback = ( Chargeback  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewCollectionCostAmount = ( CollectionCostAmount  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewCollectionCostPercentage = ( CollectionCostPercentage  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) ),
				NewRegistrationCostPerNumber = ( RegistrationCostPerNumber  * (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) )
			WHERE ProcessID=p_processId;
		ELSE
			UPDATE
				tmp_TempRateTableDIDRate_
			SET
				NewOneOffCost = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (OneOffCost  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewMonthlyCost = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (MonthlyCost  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewCostPerCall = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (CostPerCall  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewCostPerMinute = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (CostPerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewSurchargePerCall = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (SurchargePerCall  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewSurchargePerMinute = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (SurchargePerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewOutpaymentPerCall = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (OutpaymentPerCall  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewOutpaymentPerMinute = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (OutpaymentPerMinute  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewSurcharges = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (Surcharges  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewChargeback = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (Chargeback  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewCollectionCostAmount = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (CollectionCostAmount  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewCollectionCostPercentage = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (CollectionCostPercentage  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId ))),
				NewRegistrationCostPerNumber = ((SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = v_RateTableCurrencyID_ AND CompanyID = p_companyId ) * (RegistrationCostPerNumber  / (SELECT Value FROM tblCurrencyConversion WHERE tblCurrencyConversion.CurrencyId = p_CurrencyID AND CompanyID = p_companyId )))
			WHERE ProcessID=p_processId;
		END IF;
	ELSE
		UPDATE
			tmp_TempRateTableDIDRate_
		SET
			NewOneOffCost = OneOffCost,
			NewMonthlyCost = MonthlyCost,
			NewCostPerCall = CostPerCall,
			NewCostPerMinute = CostPerMinute,
			NewSurchargePerCall = SurchargePerCall,
			NewSurchargePerMinute = SurchargePerMinute,
			NewOutpaymentPerCall = OutpaymentPerCall,
			NewOutpaymentPerMinute = OutpaymentPerMinute,
			NewSurcharges = Surcharges,
			NewChargeback = Chargeback,
			NewCollectionCostAmount = CollectionCostAmount,
			NewCollectionCostPercentage = CollectionCostPercentage,
			NewRegistrationCostPerNumber = RegistrationCostPerNumber
		WHERE
			ProcessID = p_processId;
	END IF;

    IF newstringcode = 0
    THEN

		INSERT INTO tblRateTableDIDRateChangeLog(
            TempRateTableDIDRateID,
            RateTableDIDRateID,
            RateTableId,
            TimezonesID,
            OriginationRateID,
            OriginationCode,
            OriginationDescription,
            RateId,
            Code,
            Description,
            City,
				Tariff,
            AccessType,
			OneOffCost,
			MonthlyCost,
			CostPerCall,
			CostPerMinute,
			SurchargePerCall,
			SurchargePerMinute,
			OutpaymentPerCall,
			OutpaymentPerMinute,
			Surcharges,
			Chargeback,
			CollectionCostAmount,
			CollectionCostPercentage,
			RegistrationCostPerNumber,
			OneOffCostCurrency,
			MonthlyCostCurrency,
			CostPerCallCurrency,
			CostPerMinuteCurrency,
			SurchargePerCallCurrency,
			SurchargePerMinuteCurrency,
			OutpaymentPerCallCurrency,
			OutpaymentPerMinuteCurrency,
			SurchargesCurrency,
			ChargebackCurrency,
			CollectionCostAmountCurrency,
			RegistrationCostPerNumberCurrency,
            EffectiveDate,
            EndDate,
            `Action`,
            ProcessID,
            created_at
		)
		SELECT
			tblTempRateTableDIDRate.TempRateTableDIDRateID,
			tblRateTableDIDRate.RateTableDIDRateID,
			p_RateTableId AS RateTableId,
			tblTempRateTableDIDRate.TimezonesID,
			IFNULL(OriginationRate.RateID,0) AS OriginationRateID,
			tblTempRateTableDIDRate.OriginationCode,
			tblTempRateTableDIDRate.OriginationDescription,
			tblRate.RateId,
			tblTempRateTableDIDRate.Code,
			tblTempRateTableDIDRate.Description,
			tblTempRateTableDIDRate.City,
			tblTempRateTableDIDRate.Tariff,
			tblTempRateTableDIDRate.AccessType,
			tblTempRateTableDIDRate.NewOneOffCost,
			tblTempRateTableDIDRate.NewMonthlyCost,
			tblTempRateTableDIDRate.NewCostPerCall,
			tblTempRateTableDIDRate.NewCostPerMinute,
			tblTempRateTableDIDRate.NewSurchargePerCall,
			tblTempRateTableDIDRate.NewSurchargePerMinute,
			tblTempRateTableDIDRate.NewOutpaymentPerCall,
			tblTempRateTableDIDRate.NewOutpaymentPerMinute,
			tblTempRateTableDIDRate.NewSurcharges,
			tblTempRateTableDIDRate.NewChargeback,
			tblTempRateTableDIDRate.NewCollectionCostAmount,
			tblTempRateTableDIDRate.NewCollectionCostPercentage,
			tblTempRateTableDIDRate.NewRegistrationCostPerNumber,
			tblTempRateTableDIDRate.OneOffCostCurrency,
			tblTempRateTableDIDRate.MonthlyCostCurrency,
			tblTempRateTableDIDRate.CostPerCallCurrency,
			tblTempRateTableDIDRate.CostPerMinuteCurrency,
			tblTempRateTableDIDRate.SurchargePerCallCurrency,
			tblTempRateTableDIDRate.SurchargePerMinuteCurrency,
			tblTempRateTableDIDRate.OutpaymentPerCallCurrency,
			tblTempRateTableDIDRate.OutpaymentPerMinuteCurrency,
			tblTempRateTableDIDRate.SurchargesCurrency,
			tblTempRateTableDIDRate.ChargebackCurrency,
			tblTempRateTableDIDRate.CollectionCostAmountCurrency,
			tblTempRateTableDIDRate.RegistrationCostPerNumberCurrency,
			tblTempRateTableDIDRate.EffectiveDate,
			tblTempRateTableDIDRate.EndDate,
			'New' AS `Action`,
			p_processId AS ProcessID,
			now() AS created_at
		FROM tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
		LEFT JOIN tblRate
			ON tblTempRateTableDIDRate.Code = tblRate.Code AND tblTempRateTableDIDRate.CodeDeckId = tblRate.CodeDeckId AND tblRate.CompanyID = p_companyId
			AND (tblTempRateTableDIDRate.CountryID = 0 OR tblTempRateTableDIDRate.CountryID = tblRate.CountryID)
		LEFT JOIN tblRate AS OriginationRate
			ON tblTempRateTableDIDRate.OriginationCode = OriginationRate.Code AND tblTempRateTableDIDRate.CodeDeckId = OriginationRate.CodeDeckId  AND OriginationRate.CompanyID = p_companyId
		LEFT JOIN tblRateTableDIDRate
			ON tblRate.RateID = tblRateTableDIDRate.RateId AND
			((IFNULL(tblRateTableDIDRate.OriginationRateID,0) = 0 AND OriginationRate.RateID IS NULL) OR (tblRateTableDIDRate.OriginationRateID = OriginationRate.RateID)) AND
			tblRateTableDIDRate.RateTableId = p_RateTableId AND
			tblRateTableDIDRate.TimezonesID = tblTempRateTableDIDRate.TimezonesID AND
			tblRateTableDIDRate.City = tblTempRateTableDIDRate.City AND
			tblRateTableDIDRate.Tariff = tblTempRateTableDIDRate.Tariff AND
			tblRateTableDIDRate.AccessType = tblTempRateTableDIDRate.AccessType AND
			tblRateTableDIDRate.EffectiveDate  <= date(now())
		WHERE tblTempRateTableDIDRate.ProcessID=p_processId AND tblRateTableDIDRate.RateTableDIDRateID IS NULL
			AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');


        DROP TEMPORARY TABLE IF EXISTS tmp_EffectiveDates_;
		CREATE TEMPORARY TABLE tmp_EffectiveDates_ (
			EffectiveDate  Date,
			RowID int,
			INDEX (RowID)
		);
        INSERT INTO tmp_EffectiveDates_
        SELECT DISTINCT
            EffectiveDate,
            @row_num := @row_num+1 AS RowID
        FROM tmp_TempRateTableDIDRate_
            ,(SELECT @row_num := 0) x
        WHERE  ProcessID = p_processId

        group by EffectiveDate
        ORDER BY EffectiveDate asc;

        SET v_pointer_ = 1;
        SET v_rowCount_ = ( SELECT COUNT(*) FROM tmp_EffectiveDates_ );

        IF v_rowCount_ > 0 THEN

            WHILE v_pointer_ <= v_rowCount_
            DO

                SET @EffectiveDate = ( SELECT EffectiveDate FROM tmp_EffectiveDates_ WHERE RowID = v_pointer_ );
                SET @row_num = 0;

                INSERT INTO tblRateTableDIDRateChangeLog(
					TempRateTableDIDRateID,
					RateTableDIDRateID,
					RateTableId,
					TimezonesID,
					OriginationRateID,
					OriginationCode,
					OriginationDescription,
					RateId,
					Code,
					Description,
					City,
					Tariff,
					AccessType,
					OneOffCost,
					MonthlyCost,
					CostPerCall,
					CostPerMinute,
					SurchargePerCall,
					SurchargePerMinute,
					OutpaymentPerCall,
					OutpaymentPerMinute,
					Surcharges,
					Chargeback,
					CollectionCostAmount,
					CollectionCostPercentage,
					RegistrationCostPerNumber,
					OneOffCostCurrency,
					MonthlyCostCurrency,
					CostPerCallCurrency,
					CostPerMinuteCurrency,
					SurchargePerCallCurrency,
					SurchargePerMinuteCurrency,
					OutpaymentPerCallCurrency,
					OutpaymentPerMinuteCurrency,
					SurchargesCurrency,
					ChargebackCurrency,
					CollectionCostAmountCurrency,
					RegistrationCostPerNumberCurrency,
					EffectiveDate,
					EndDate,
					`Action`,
					ProcessID,
					created_at
                )
                SELECT
					DISTINCT
					tblTempRateTableDIDRate.TempRateTableDIDRateID,
					RateTableDIDRate.RateTableDIDRateID,
					p_RateTableId AS RateTableId,
					tblTempRateTableDIDRate.TimezonesID,
					IFNULL(OriginationRate.RateID,0) AS OriginationRateID,
					OriginationRate.Code AS OriginationCode,
					OriginationRate.Description AS OriginationDescription,
					tblRate.RateId,
					tblRate.Code,
					tblRate.Description,
					tblTempRateTableDIDRate.City,
					tblTempRateTableDIDRate.Tariff,
					tblTempRateTableDIDRate.AccessType,
					CONCAT(tblTempRateTableDIDRate.NewOneOffCost, IF(tblTempRateTableDIDRate.NewOneOffCost > RateTableDIDRate.OneOffCost, '<span style="color: green;" data-toggle="tooltip" data-title="OneOffCost Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewOneOffCost < RateTableDIDRate.OneOffCost, '<span style="color: red;" data-toggle="tooltip" data-title="OneOffCost Decrease" data-placement="top">&#9660;</span>',''))) AS `OneOffCost`,
					CONCAT(tblTempRateTableDIDRate.NewMonthlyCost, IF(tblTempRateTableDIDRate.NewMonthlyCost > RateTableDIDRate.MonthlyCost, '<span style="color: green;" data-toggle="tooltip" data-title="MonthlyCost Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewMonthlyCost < RateTableDIDRate.MonthlyCost, '<span style="color: red;" data-toggle="tooltip" data-title="MonthlyCost Decrease" data-placement="top">&#9660;</span>',''))) AS `MonthlyCost`,
					CONCAT(tblTempRateTableDIDRate.NewCostPerCall, IF(tblTempRateTableDIDRate.NewCostPerCall > RateTableDIDRate.CostPerCall, '<span style="color: green;" data-toggle="tooltip" data-title="CostPerCall Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewCostPerCall < RateTableDIDRate.CostPerCall, '<span style="color: red;" data-toggle="tooltip" data-title="CostPerCall Decrease" data-placement="top">&#9660;</span>',''))) AS `CostPerCall`,
					CONCAT(tblTempRateTableDIDRate.NewCostPerMinute, IF(tblTempRateTableDIDRate.NewCostPerMinute > RateTableDIDRate.CostPerMinute, '<span style="color: green;" data-toggle="tooltip" data-title="CostPerMinute Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewCostPerMinute < RateTableDIDRate.CostPerMinute, '<span style="color: red;" data-toggle="tooltip" data-title="CostPerMinute Decrease" data-placement="top">&#9660;</span>',''))) AS `CostPerMinute`,
					CONCAT(tblTempRateTableDIDRate.NewSurchargePerCall, IF(tblTempRateTableDIDRate.NewSurchargePerCall > RateTableDIDRate.SurchargePerCall, '<span style="color: green;" data-toggle="tooltip" data-title="SurchargePerCall Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewSurchargePerCall < RateTableDIDRate.SurchargePerCall, '<span style="color: red;" data-toggle="tooltip" data-title="SurchargePerCall Decrease" data-placement="top">&#9660;</span>',''))) AS `SurchargePerCall`,
					CONCAT(tblTempRateTableDIDRate.NewSurchargePerMinute, IF(tblTempRateTableDIDRate.NewSurchargePerMinute > RateTableDIDRate.SurchargePerMinute, '<span style="color: green;" data-toggle="tooltip" data-title="SurchargePerMinute Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewSurchargePerMinute < RateTableDIDRate.SurchargePerMinute, '<span style="color: red;" data-toggle="tooltip" data-title="SurchargePerMinute Decrease" data-placement="top">&#9660;</span>',''))) AS `SurchargePerMinute`,
					CONCAT(tblTempRateTableDIDRate.NewOutpaymentPerCall, IF(tblTempRateTableDIDRate.NewOutpaymentPerCall > RateTableDIDRate.OutpaymentPerCall, '<span style="color: green;" data-toggle="tooltip" data-title="OutpaymentPerCall Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewOutpaymentPerCall < RateTableDIDRate.OutpaymentPerCall, '<span style="color: red;" data-toggle="tooltip" data-title="OutpaymentPerCall Decrease" data-placement="top">&#9660;</span>',''))) AS `OutpaymentPerCall`,
					CONCAT(tblTempRateTableDIDRate.NewOutpaymentPerMinute, IF(tblTempRateTableDIDRate.NewOutpaymentPerMinute > RateTableDIDRate.OutpaymentPerMinute, '<span style="color: green;" data-toggle="tooltip" data-title="OutpaymentPerMinute Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewOutpaymentPerMinute < RateTableDIDRate.OutpaymentPerMinute, '<span style="color: red;" data-toggle="tooltip" data-title="OutpaymentPerMinute Decrease" data-placement="top">&#9660;</span>',''))) AS `OutpaymentPerMinute`,
					CONCAT(tblTempRateTableDIDRate.NewSurcharges, IF(tblTempRateTableDIDRate.NewSurcharges > RateTableDIDRate.Surcharges, '<span style="color: green;" data-toggle="tooltip" data-title="Surcharges Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewSurcharges < RateTableDIDRate.Surcharges, '<span style="color: red;" data-toggle="tooltip" data-title="Surcharges Decrease" data-placement="top">&#9660;</span>',''))) AS `Surcharges`,
					CONCAT(tblTempRateTableDIDRate.NewChargeback, IF(tblTempRateTableDIDRate.NewChargeback > RateTableDIDRate.Chargeback, '<span style="color: green;" data-toggle="tooltip" data-title="Chargeback Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewChargeback < RateTableDIDRate.Chargeback, '<span style="color: red;" data-toggle="tooltip" data-title="Chargeback Decrease" data-placement="top">&#9660;</span>',''))) AS `Chargeback`,
					CONCAT(tblTempRateTableDIDRate.NewCollectionCostAmount, IF(tblTempRateTableDIDRate.NewCollectionCostAmount > RateTableDIDRate.CollectionCostAmount, '<span style="color: green;" data-toggle="tooltip" data-title="CollectionCostAmount Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewCollectionCostAmount < RateTableDIDRate.CollectionCostAmount, '<span style="color: red;" data-toggle="tooltip" data-title="CollectionCostAmount Decrease" data-placement="top">&#9660;</span>',''))) AS `CollectionCostAmount`,
					CONCAT(tblTempRateTableDIDRate.NewCollectionCostPercentage, IF(tblTempRateTableDIDRate.NewCollectionCostPercentage > RateTableDIDRate.CollectionCostPercentage, '<span style="color: green;" data-toggle="tooltip" data-title="CollectionCostPercentage Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewCollectionCostPercentage < RateTableDIDRate.CollectionCostPercentage, '<span style="color: red;" data-toggle="tooltip" data-title="CollectionCostPercentage Decrease" data-placement="top">&#9660;</span>',''))) AS `CollectionCostPercentage`,
					CONCAT(tblTempRateTableDIDRate.NewRegistrationCostPerNumber, IF(tblTempRateTableDIDRate.NewRegistrationCostPerNumber > RateTableDIDRate.RegistrationCostPerNumber, '<span style="color: green;" data-toggle="tooltip" data-title="RegistrationCostPerNumber Increase" data-placement="top">&#9650;</span>', IF(tblTempRateTableDIDRate.NewRegistrationCostPerNumber < RateTableDIDRate.RegistrationCostPerNumber, '<span style="color: red;" data-toggle="tooltip" data-title="RegistrationCostPerNumber Decrease" data-placement="top">&#9660;</span>',''))) AS `RegistrationCostPerNumber`,
					tblTempRateTableDIDRate.OneOffCostCurrency,
					tblTempRateTableDIDRate.MonthlyCostCurrency,
					tblTempRateTableDIDRate.CostPerCallCurrency,
					tblTempRateTableDIDRate.CostPerMinuteCurrency,
					tblTempRateTableDIDRate.SurchargePerCallCurrency,
					tblTempRateTableDIDRate.SurchargePerMinuteCurrency,
					tblTempRateTableDIDRate.OutpaymentPerCallCurrency,
					tblTempRateTableDIDRate.OutpaymentPerMinuteCurrency,
					tblTempRateTableDIDRate.SurchargesCurrency,
					tblTempRateTableDIDRate.ChargebackCurrency,
					tblTempRateTableDIDRate.CollectionCostAmountCurrency,
					tblTempRateTableDIDRate.RegistrationCostPerNumberCurrency,
					tblTempRateTableDIDRate.EffectiveDate,
					tblTempRateTableDIDRate.EndDate ,
					'IncreasedDecreased' AS `Action`,
					p_processid AS ProcessID,
					now() AS created_at
                FROM
                (
                    SELECT DISTINCT tmp.* ,
                        @row_num := IF(@prev_RateId = tmp.RateID AND @prev_OriginationRateID = tmp.OriginationRateID AND @prev_TimezonesID = tmp.TimezonesID AND @prev_City = tmp.City AND @prev_Tariff = tmp.Tariff AND @prev_AccessType = tmp.AccessType AND @prev_EffectiveDate >= tmp.EffectiveDate, (@row_num + 1), 1) AS RowID,
                        @prev_RateId := tmp.RateID,
                        @prev_OriginationRateID := tmp.OriginationRateID,
                        @prev_TimezonesID := tmp.TimezonesID,
                        @prev_City := tmp.City,
                        @prev_Tariff := tmp.Tariff,
                        @prev_AccessType := tmp.AccessType,
                        @prev_EffectiveDate := tmp.EffectiveDate
                    FROM
                    (
                        SELECT DISTINCT vr1.*
                        FROM tblRateTableDIDRate vr1
                        LEFT OUTER JOIN tblRateTableDIDRate vr2
                            ON vr1.RateTableId = vr2.RateTableId
                            AND vr1.RateID = vr2.RateID
                            AND vr1.OriginationRateID = vr2.OriginationRateID
                            AND vr1.TimezonesID = vr2.TimezonesID
                            AND vr1.City = vr2.City
                            AND vr1.Tariff = vr2.Tariff
                            AND vr1.AccessType = vr2.AccessType
                            AND vr2.EffectiveDate  = @EffectiveDate
                        WHERE
                            vr1.RateTableId = p_RateTableId
                            AND vr1.EffectiveDate <= COALESCE(vr2.EffectiveDate,@EffectiveDate)
                        ORDER BY vr1.RateID DESC, vr1.OriginationRateID DESC, vr1.TimezonesID DESC, vr1.City DESC, vr1.Tariff DESC, vr1.AccessType DESC, vr1.EffectiveDate DESC
                    ) tmp ,
                    ( SELECT @row_num := 0 , @prev_RateId := 0 , @prev_OriginationRateID := 0 , @prev_TimezonesID := 0 , @prev_City := '' , @prev_Tariff := '' , @prev_AccessType := '' , @prev_EffectiveDate := '' ) x
                      ORDER BY RateID DESC, OriginationRateID DESC, TimezonesID DESC, City DESC, Tariff DESC, AccessType DESC, EffectiveDate DESC
                ) RateTableDIDRate
                JOIN tblRate
                    ON tblRate.CompanyID = p_companyId
                    AND tblRate.RateID = RateTableDIDRate.RateId
                LEFT JOIN tblRate AS OriginationRate
                    ON OriginationRate.CompanyID = p_companyId
                    AND OriginationRate.RateID = RateTableDIDRate.OriginationRateID
                JOIN tmp_TempRateTableDIDRate_ tblTempRateTableDIDRate
                    ON tblTempRateTableDIDRate.Code = tblRate.Code AND (tblTempRateTableDIDRate.CountryID = 0 OR tblTempRateTableDIDRate.CountryID = tblRate.CountryID)
                    AND ((tblTempRateTableDIDRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableDIDRate.OriginationCode = OriginationRate.Code))
                    AND tblTempRateTableDIDRate.TimezonesID = RateTableDIDRate.TimezonesID
                    AND tblTempRateTableDIDRate.City = RateTableDIDRate.City
                    AND tblTempRateTableDIDRate.Tariff = RateTableDIDRate.Tariff
                    AND tblTempRateTableDIDRate.AccessType = RateTableDIDRate.AccessType
                    AND tblTempRateTableDIDRate.ProcessID=p_processId
                    AND RateTableDIDRate.EffectiveDate <= tblTempRateTableDIDRate.EffectiveDate
                    AND tblTempRateTableDIDRate.EffectiveDate =  @EffectiveDate
                    AND RateTableDIDRate.RowID = 1
                WHERE
                    RateTableDIDRate.RateTableId = p_RateTableId
                    AND tblTempRateTableDIDRate.Code IS NOT NULL
                    AND tblTempRateTableDIDRate.ProcessID=p_processId
                    AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

                SET v_pointer_ = v_pointer_ + 1;

            END WHILE;

        END IF;


        IF p_list_option = 1
        THEN

            INSERT INTO tblRateTableDIDRateChangeLog(
				RateTableDIDRateID,
				RateTableId,
				TimezonesID,
				OriginationRateID,
				OriginationCode,
				OriginationDescription,
				RateId,
				Code,
				Description,
				City,
				Tariff,
				AccessType,
				OneOffCost,
				MonthlyCost,
				CostPerCall,
				CostPerMinute,
				SurchargePerCall,
				SurchargePerMinute,
				OutpaymentPerCall,
				OutpaymentPerMinute,
				Surcharges,
				Chargeback,
				CollectionCostAmount,
				CollectionCostPercentage,
				RegistrationCostPerNumber,
				OneOffCostCurrency,
				MonthlyCostCurrency,
				CostPerCallCurrency,
				CostPerMinuteCurrency,
				SurchargePerCallCurrency,
				SurchargePerMinuteCurrency,
				OutpaymentPerCallCurrency,
				OutpaymentPerMinuteCurrency,
				SurchargesCurrency,
				ChargebackCurrency,
				CollectionCostAmountCurrency,
				RegistrationCostPerNumberCurrency,
				EffectiveDate,
				EndDate,
				`Action`,
				ProcessID,
				created_at
            )
            SELECT DISTINCT
                tblRateTableDIDRate.RateTableDIDRateID,
                p_RateTableId AS RateTableId,
                tblRateTableDIDRate.TimezonesID,
                tblRateTableDIDRate.OriginationRateID,
                OriginationRate.Code,
                OriginationRate.Description,
                tblRateTableDIDRate.RateId,
                tblRate.Code,
                tblRate.Description,
                tblRateTableDIDRate.City,
					 tblRateTableDIDRate.Tariff,
                tblRateTableDIDRate.AccessType,
				tblRateTableDIDRate.OneOffCost,
				tblRateTableDIDRate.MonthlyCost,
				tblRateTableDIDRate.CostPerCall,
				tblRateTableDIDRate.CostPerMinute,
				tblRateTableDIDRate.SurchargePerCall,
				tblRateTableDIDRate.SurchargePerMinute,
				tblRateTableDIDRate.OutpaymentPerCall,
				tblRateTableDIDRate.OutpaymentPerMinute,
				tblRateTableDIDRate.Surcharges,
				tblRateTableDIDRate.Chargeback,
				tblRateTableDIDRate.CollectionCostAmount,
				tblRateTableDIDRate.CollectionCostPercentage,
				tblRateTableDIDRate.RegistrationCostPerNumber,
				tblRateTableDIDRate.OneOffCostCurrency,
				tblRateTableDIDRate.MonthlyCostCurrency,
				tblRateTableDIDRate.CostPerCallCurrency,
				tblRateTableDIDRate.CostPerMinuteCurrency,
				tblRateTableDIDRate.SurchargePerCallCurrency,
				tblRateTableDIDRate.SurchargePerMinuteCurrency,
				tblRateTableDIDRate.OutpaymentPerCallCurrency,
				tblRateTableDIDRate.OutpaymentPerMinuteCurrency,
				tblRateTableDIDRate.SurchargesCurrency,
				tblRateTableDIDRate.ChargebackCurrency,
				tblRateTableDIDRate.CollectionCostAmountCurrency,
				tblRateTableDIDRate.RegistrationCostPerNumberCurrency,
                tblRateTableDIDRate.EffectiveDate,
                tblRateTableDIDRate.EndDate ,
                'Deleted' AS `Action`,
                p_processId AS ProcessID,
                now() AS deleted_at
            FROM tblRateTableDIDRate
            JOIN tblRate
                ON tblRate.RateID = tblRateTableDIDRate.RateId AND tblRate.CompanyID = p_companyId
        		LEFT JOIN tblRate AS OriginationRate
             	 ON OriginationRate.RateID = tblRateTableDIDRate.OriginationRateID AND OriginationRate.CompanyID = p_companyId
            LEFT JOIN tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
                ON tblTempRateTableDIDRate.Code = tblRate.Code AND (tblTempRateTableDIDRate.CountryID = 0 OR tblTempRateTableDIDRate.CountryID = tblRate.CountryID)
                AND ((tblTempRateTableDIDRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableDIDRate.OriginationCode = OriginationRate.Code))
                AND tblTempRateTableDIDRate.TimezonesID = tblRateTableDIDRate.TimezonesID
                AND tblTempRateTableDIDRate.City = tblRateTableDIDRate.City
                AND tblTempRateTableDIDRate.Tariff = tblRateTableDIDRate.Tariff
                AND tblTempRateTableDIDRate.AccessType = tblRateTableDIDRate.AccessType
                AND tblTempRateTableDIDRate.ProcessID=p_processId
                AND (
                    ( tblTempRateTableDIDRate.EndDate is null AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block') )
                    OR
                    ( tblTempRateTableDIDRate.EndDate is not null AND tblTempRateTableDIDRate.Change IN ('Delete', 'R', 'D', 'Blocked','Block') )
                )
            WHERE tblRateTableDIDRate.RateTableId = p_RateTableId
                AND ( tblRateTableDIDRate.EndDate is null OR tblRateTableDIDRate.EndDate <= date(now()) )
                AND tblTempRateTableDIDRate.Code IS NULL
            ORDER BY RateTableDIDRateID ASC;

        END IF;


        INSERT INTO tblRateTableDIDRateChangeLog(
            RateTableDIDRateID,
            RateTableId,
            TimezonesID,
            OriginationRateID,
            OriginationCode,
            OriginationDescription,
            RateId,
            Code,
            Description,
            City,
				Tariff,
            AccessType,
			OneOffCost,
			MonthlyCost,
			CostPerCall,
			CostPerMinute,
			SurchargePerCall,
			SurchargePerMinute,
			OutpaymentPerCall,
			OutpaymentPerMinute,
			Surcharges,
			Chargeback,
			CollectionCostAmount,
			CollectionCostPercentage,
			RegistrationCostPerNumber,
			OneOffCostCurrency,
			MonthlyCostCurrency,
			CostPerCallCurrency,
			CostPerMinuteCurrency,
			SurchargePerCallCurrency,
			SurchargePerMinuteCurrency,
			OutpaymentPerCallCurrency,
			OutpaymentPerMinuteCurrency,
			SurchargesCurrency,
			ChargebackCurrency,
			CollectionCostAmountCurrency,
			RegistrationCostPerNumberCurrency,
            EffectiveDate,
            EndDate,
            `Action`,
            ProcessID,
            created_at
        )
        SELECT DISTINCT
            tblRateTableDIDRate.RateTableDIDRateID,
            p_RateTableId AS RateTableId,
            tblRateTableDIDRate.TimezonesID,
            tblRateTableDIDRate.OriginationRateID,
            OriginationRate.Code,
            OriginationRate.Description,
            tblRateTableDIDRate.RateId,
            tblRate.Code,
            tblRate.Description,
            tblRateTableDIDRate.City,
				tblRateTableDIDRate.Tariff,
            tblRateTableDIDRate.AccessType,
			tblRateTableDIDRate.OneOffCost,
			tblRateTableDIDRate.MonthlyCost,
			tblRateTableDIDRate.CostPerCall,
			tblRateTableDIDRate.CostPerMinute,
			tblRateTableDIDRate.SurchargePerCall,
			tblRateTableDIDRate.SurchargePerMinute,
			tblRateTableDIDRate.OutpaymentPerCall,
			tblRateTableDIDRate.OutpaymentPerMinute,
			tblRateTableDIDRate.Surcharges,
			tblRateTableDIDRate.Chargeback,
			tblRateTableDIDRate.CollectionCostAmount,
			tblRateTableDIDRate.CollectionCostPercentage,
			tblRateTableDIDRate.RegistrationCostPerNumber,
			tblRateTableDIDRate.OneOffCostCurrency,
			tblRateTableDIDRate.MonthlyCostCurrency,
			tblRateTableDIDRate.CostPerCallCurrency,
			tblRateTableDIDRate.CostPerMinuteCurrency,
			tblRateTableDIDRate.SurchargePerCallCurrency,
			tblRateTableDIDRate.SurchargePerMinuteCurrency,
			tblRateTableDIDRate.OutpaymentPerCallCurrency,
			tblRateTableDIDRate.OutpaymentPerMinuteCurrency,
			tblRateTableDIDRate.SurchargesCurrency,
			tblRateTableDIDRate.ChargebackCurrency,
			tblRateTableDIDRate.CollectionCostAmountCurrency,
			tblRateTableDIDRate.RegistrationCostPerNumberCurrency,
            tblRateTableDIDRate.EffectiveDate,
            IFNULL(tblTempRateTableDIDRate.EndDate,tblRateTableDIDRate.EndDate) as  EndDate ,
            'Deleted' AS `Action`,
            p_processId AS ProcessID,
            now() AS deleted_at
        FROM tblRateTableDIDRate
        JOIN tblRate
            ON tblRate.RateID = tblRateTableDIDRate.RateId AND tblRate.CompanyID = p_companyId
        LEFT JOIN tblRate AS OriginationRate
             ON OriginationRate.RateID = tblRateTableDIDRate.OriginationRateID AND OriginationRate.CompanyID = p_companyId
        LEFT JOIN tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
            ON tblRate.Code = tblTempRateTableDIDRate.Code AND (tblTempRateTableDIDRate.CountryID = 0 OR tblTempRateTableDIDRate.CountryID = tblRate.CountryID)
            AND ((tblTempRateTableDIDRate.OriginationCode IS NULL AND OriginationRate.Code IS NULL) OR (tblTempRateTableDIDRate.OriginationCode = OriginationRate.Code))
            AND tblTempRateTableDIDRate.TimezonesID = tblRateTableDIDRate.TimezonesID
            AND tblTempRateTableDIDRate.City = tblRateTableDIDRate.City
            AND tblTempRateTableDIDRate.Tariff = tblRateTableDIDRate.Tariff
            AND tblTempRateTableDIDRate.AccessType = tblRateTableDIDRate.AccessType
            AND tblTempRateTableDIDRate.Change IN ('Delete', 'R', 'D', 'Blocked','Block')
            AND tblTempRateTableDIDRate.ProcessID=p_processId
        WHERE
			tblRateTableDIDRate.RateTableId = p_RateTableId AND
			tblTempRateTableDIDRate.Code IS NOT NULL
        ORDER BY
		RateTableDIDRateID ASC;

    END IF;

    SELECT * FROM tmp_JobLog_;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_RateTableDIDRateCheckDialstringAndDupliacteCode`;
DELIMITER //
CREATE PROCEDURE `prc_RateTableDIDRateCheckDialstringAndDupliacteCode`(
	IN `p_companyId` INT,
	IN `p_processId` VARCHAR(200) ,
	IN `p_dialStringId` INT,
	IN `p_effectiveImmediately` INT,
	IN `p_dialcodeSeparator` VARCHAR(50),
	IN `p_seperatecolumn` INT
)
ThisSP:BEGIN

	DECLARE totaldialstringcode INT(11) DEFAULT 0;
	DECLARE v_CodeDeckId_ INT ;
	DECLARE totalduplicatecode INT(11);
	DECLARE errormessage longtext;
	DECLARE errorheader longtext;

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableDIDRateDialString_ ;
	CREATE TEMPORARY TABLE `tmp_RateTableDIDRateDialString_` (
		`TempRateTableDIDRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NOT NULL DEFAULT '',
		`OneOffCost` decimal(18,6) DEFAULT NULL,
	  	`MonthlyCost` decimal(18,6) DEFAULT NULL,
	  	`CostPerCall` decimal(18,6) DEFAULT NULL,
	  	`CostPerMinute` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerCall` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerMinute` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerCall` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerMinute` decimal(18,6) DEFAULT NULL,
	  	`Surcharges` decimal(18,6) DEFAULT NULL,
	  	`Chargeback` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostAmount` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostPercentage` decimal(18,6) DEFAULT NULL,
	  	`RegistrationCostPerNumber` decimal(18,6) DEFAULT NULL,
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`DialStringPrefix` varchar(500),
		`CountryID` INT NOT NULL DEFAULT '0' ,
		INDEX IX_orogination_code (OriginationCode),
		INDEX IX_origination_description (OriginationDescription),
		INDEX IX_code (code),
		INDEX IX_CodeDeckId (CodeDeckId),
		INDEX IX_Description (Description),
		INDEX IX_EffectiveDate (EffectiveDate),
		INDEX IX_DialStringPrefix (DialStringPrefix)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableDIDRateDialString_2 ;
	CREATE TEMPORARY TABLE `tmp_RateTableDIDRateDialString_2` (
		`TempRateTableDIDRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NOT NULL DEFAULT '',
		`OneOffCost` decimal(18,6) DEFAULT NULL,
	  	`MonthlyCost` decimal(18,6) DEFAULT NULL,
	  	`CostPerCall` decimal(18,6) DEFAULT NULL,
	  	`CostPerMinute` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerCall` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerMinute` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerCall` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerMinute` decimal(18,6) DEFAULT NULL,
	  	`Surcharges` decimal(18,6) DEFAULT NULL,
	  	`Chargeback` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostAmount` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostPercentage` decimal(18,6) DEFAULT NULL,
	  	`RegistrationCostPerNumber` decimal(18,6) DEFAULT NULL,
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`DialStringPrefix` varchar(500),
		`CountryID` INT NOT NULL DEFAULT '0' ,
		INDEX IX_orogination_code (OriginationCode),
		INDEX IX_origination_description (OriginationDescription),
		INDEX IX_code (code),
		INDEX IX_CodeDeckId (CodeDeckId),
		INDEX IX_Description (Description),
		INDEX IX_EffectiveDate (EffectiveDate),
		INDEX IX_DialStringPrefix (DialStringPrefix)
	);

	DROP TEMPORARY TABLE IF EXISTS tmp_RateTableDIDRateDialString_3 ;
	CREATE TEMPORARY TABLE `tmp_RateTableDIDRateDialString_3` (
		`TempRateTableDIDRateID` int,
		`CodeDeckId` int ,
		`TimezonesID` INT,
		`OriginationCode` varchar(50) NULL DEFAULT NULL,
		`OriginationDescription` varchar(200) NULL DEFAULT NULL,
		`Code` varchar(50) ,
		`Description` varchar(200) ,
		`City` varchar(50) NOT NULL DEFAULT '',
		`Tariff` varchar(50) NOT NULL DEFAULT '',
		`AccessType` varchar(200) NOT NULL DEFAULT '',
		`OneOffCost` decimal(18,6) DEFAULT NULL,
	  	`MonthlyCost` decimal(18,6) DEFAULT NULL,
	  	`CostPerCall` decimal(18,6) DEFAULT NULL,
	  	`CostPerMinute` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerCall` decimal(18,6) DEFAULT NULL,
	  	`SurchargePerMinute` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerCall` decimal(18,6) DEFAULT NULL,
	  	`OutpaymentPerMinute` decimal(18,6) DEFAULT NULL,
	  	`Surcharges` decimal(18,6) DEFAULT NULL,
	  	`Chargeback` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostAmount` decimal(18,6) DEFAULT NULL,
	  	`CollectionCostPercentage` decimal(18,6) DEFAULT NULL,
	  	`RegistrationCostPerNumber` decimal(18,6) DEFAULT NULL,
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`CostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerCallCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargePerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerCallCurrency` INT(11) NULL DEFAULT NULL,
		`OutpaymentPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`SurchargesCurrency` INT(11) NULL DEFAULT NULL,
		`ChargebackCurrency` INT(11) NULL DEFAULT NULL,
		`CollectionCostAmountCurrency` INT(11) NULL DEFAULT NULL,
		`RegistrationCostPerNumberCurrency` INT(11) NULL DEFAULT NULL,
		`EffectiveDate` Datetime ,
		`EndDate` Datetime ,
		`Change` varchar(100) ,
		`ProcessId` varchar(200) ,
		`DialStringPrefix` varchar(500),
		`CountryID` INT NOT NULL DEFAULT '0' ,
		INDEX IX_orogination_code (OriginationCode),
		INDEX IX_origination_description (OriginationDescription),
		INDEX IX_code (code),
		INDEX IX_CodeDeckId (CodeDeckId),
		INDEX IX_Description (Description),
		INDEX IX_EffectiveDate (EffectiveDate),
		INDEX IX_DialStringPrefix (DialStringPrefix)
	);

	CALL prc_SplitRateTableDIDRate(p_processId,p_dialcodeSeparator,p_seperatecolumn);

	IF  p_effectiveImmediately = 1
	THEN
		UPDATE tmp_split_RateTableDIDRate_
		SET EffectiveDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EffectiveDate < DATE_FORMAT (NOW(), '%Y-%m-%d');

		UPDATE tmp_split_RateTableDIDRate_
		SET EndDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EndDate < DATE_FORMAT (NOW(), '%Y-%m-%d');
	END IF;

	DROP TEMPORARY TABLE IF EXISTS tmp_split_RateTableDIDRate_2;
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_split_RateTableDIDRate_2 as (SELECT * FROM tmp_split_RateTableDIDRate_);

	-- delete duplicate records
	DELETE n1 FROM tmp_split_RateTableDIDRate_ n1
	INNER JOIN
	(
		SELECT MAX(TempRateTableDIDRateID) AS TempRateTableDIDRateID,EffectiveDate,OriginationCode,Code,CountryID,DialStringPrefix,TimezonesID,City,Tariff,AccessType,
			OneOffCost, MonthlyCost, CostPerCall, CostPerMinute, SurchargePerCall, SurchargePerMinute, OutpaymentPerCall,
			OutpaymentPerMinute, Surcharges, Chargeback, CollectionCostAmount, CollectionCostPercentage, RegistrationCostPerNumber
		FROM tmp_split_RateTableDIDRate_2 WHERE ProcessId = p_processId
		GROUP BY
			OriginationCode,Code,CountryID,EffectiveDate,DialStringPrefix,TimezonesID,City,Tariff,AccessType,
			OneOffCost, MonthlyCost, CostPerCall, CostPerMinute, SurchargePerCall, SurchargePerMinute, OutpaymentPerCall,
			OutpaymentPerMinute, Surcharges, Chargeback, CollectionCostAmount, CollectionCostPercentage, RegistrationCostPerNumber
		HAVING COUNT(*)>1
	)n2
	ON n1.Code = n2.Code AND n1.CountryID = n2.CountryID
		AND ((n1.OriginationCode IS NULL AND n2.OriginationCode IS NULL) OR (n1.OriginationCode = n2.OriginationCode))
		AND n2.EffectiveDate = n1.EffectiveDate
		AND ((n2.DialStringPrefix IS NULL AND n1.DialStringPrefix IS NULL) OR (n2.DialStringPrefix = n1.DialStringPrefix))
		AND n2.TimezonesID = n1.TimezonesID
		AND ((n2.City IS NULL AND n1.City IS NULL) OR n2.City = n1.City)
		AND ((n2.Tariff IS NULL AND n1.Tariff IS NULL) OR n2.Tariff = n1.Tariff)
		AND ((n2.AccessType IS NULL AND n1.AccessType IS NULL) OR n2.AccessType = n1.AccessType)
		AND ((n2.OneOffCost IS NULL AND n1.OneOffCost IS NULL) OR n2.OneOffCost = n1.OneOffCost)
		AND ((n2.MonthlyCost IS NULL AND n1.MonthlyCost IS NULL) OR n2.MonthlyCost = n1.MonthlyCost)
		AND ((n2.CostPerCall IS NULL AND n1.CostPerCall IS NULL) OR n2.CostPerCall = n1.CostPerCall)
		AND ((n2.CostPerMinute IS NULL AND n1.CostPerMinute IS NULL) OR n2.CostPerMinute = n1.CostPerMinute)
		AND ((n2.SurchargePerCall IS NULL AND n1.SurchargePerCall IS NULL) OR n2.SurchargePerCall = n1.SurchargePerCall)
		AND ((n2.SurchargePerMinute IS NULL AND n1.SurchargePerMinute IS NULL) OR n2.SurchargePerMinute = n1.SurchargePerMinute)
		AND ((n2.OutpaymentPerCall IS NULL AND n1.OutpaymentPerCall IS NULL) OR n2.OutpaymentPerCall = n1.OutpaymentPerCall)
		AND ((n2.OutpaymentPerMinute IS NULL AND n1.OutpaymentPerMinute IS NULL) OR n2.OutpaymentPerMinute = n1.OutpaymentPerMinute)
		AND ((n2.Surcharges IS NULL AND n1.Surcharges IS NULL) OR n2.Surcharges = n1.Surcharges)
		AND ((n2.Chargeback IS NULL AND n1.Chargeback IS NULL) OR n2.Chargeback = n1.Chargeback)
		AND ((n2.CollectionCostAmount IS NULL AND n1.CollectionCostAmount IS NULL) OR n2.CollectionCostAmount = n1.CollectionCostAmount)
		AND ((n2.CollectionCostPercentage IS NULL AND n1.CollectionCostPercentage IS NULL) OR n2.CollectionCostPercentage = n1.CollectionCostPercentage)
		AND ((n2.RegistrationCostPerNumber IS NULL AND n1.RegistrationCostPerNumber IS NULL) OR n2.RegistrationCostPerNumber = n1.RegistrationCostPerNumber)
		AND n1.TempRateTableDIDRateID < n2.TempRateTableDIDRateID
	WHERE
		n1.ProcessId = p_processId;

	INSERT INTO tmp_TempRateTableDIDRate_
	(
		`TempRateTableDIDRateID`,
		CodeDeckId,
		TimezonesID,
		OriginationCode,
		OriginationDescription,
		Code,
		Description,
		City,
		Tariff,
		AccessType,
		OneOffCost,
		MonthlyCost,
		CostPerCall,
		CostPerMinute,
		SurchargePerCall,
		SurchargePerMinute,
		OutpaymentPerCall,
		OutpaymentPerMinute,
		Surcharges,
		Chargeback,
		CollectionCostAmount,
		CollectionCostPercentage,
		RegistrationCostPerNumber,
		OneOffCostCurrency,
		MonthlyCostCurrency,
		CostPerCallCurrency,
		CostPerMinuteCurrency,
		SurchargePerCallCurrency,
		SurchargePerMinuteCurrency,
		OutpaymentPerCallCurrency,
		OutpaymentPerMinuteCurrency,
		SurchargesCurrency,
		ChargebackCurrency,
		CollectionCostAmountCurrency,
		RegistrationCostPerNumberCurrency,
		EffectiveDate,
		EndDate,
		`Change`,
		ProcessId,
		DialStringPrefix,
		CountryID
	)
	SELECT DISTINCT
		`TempRateTableDIDRateID`,
		`CodeDeckId`,
		`TimezonesID`,
		`OriginationCode`,
		`OriginationDescription`,
		`Code`,
		`Description`,
		`City`,
		`Tariff`,
		`AccessType`,
		`OneOffCost`,
		`MonthlyCost`,
		`CostPerCall`,
		`CostPerMinute`,
		`SurchargePerCall`,
		`SurchargePerMinute`,
		`OutpaymentPerCall`,
		`OutpaymentPerMinute`,
		`Surcharges`,
		`Chargeback`,
		`CollectionCostAmount`,
		`CollectionCostPercentage`,
		`RegistrationCostPerNumber`,
		`OneOffCostCurrency`,
		`MonthlyCostCurrency`,
		`CostPerCallCurrency`,
		`CostPerMinuteCurrency`,
		`SurchargePerCallCurrency`,
		`SurchargePerMinuteCurrency`,
		`OutpaymentPerCallCurrency`,
		`OutpaymentPerMinuteCurrency`,
		`SurchargesCurrency`,
		`ChargebackCurrency`,
		`CollectionCostAmountCurrency`,
		`RegistrationCostPerNumberCurrency`,
		`EffectiveDate`,
		`EndDate`,
		`Change`,
		`ProcessId`,
		`DialStringPrefix`,
		`CountryID`
	FROM tmp_split_RateTableDIDRate_
	WHERE tmp_split_RateTableDIDRate_.ProcessId = p_processId;

	IF  p_effectiveImmediately = 1
	THEN
		UPDATE tmp_TempRateTableDIDRate_
		SET EffectiveDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EffectiveDate < DATE_FORMAT (NOW(), '%Y-%m-%d');

		UPDATE tmp_TempRateTableDIDRate_
		SET EndDate = DATE_FORMAT (NOW(), '%Y-%m-%d')
		WHERE EndDate < DATE_FORMAT (NOW(), '%Y-%m-%d');
	END IF;

	SELECT COUNT(*) INTO totalduplicatecode FROM(
	SELECT COUNT(code) as c,code FROM tmp_TempRateTableDIDRate_  GROUP BY OriginationCode,Code,CountryID,EffectiveDate,DialStringPrefix,TimezonesID,City,Tariff,AccessType HAVING c>1) AS tbl;

	IF  totalduplicatecode > 0
	THEN

		SELECT GROUP_CONCAT(code) into errormessage FROM(
		SELECT DISTINCT OriginationCode,Code, 1 as a FROM(
		SELECT COUNT(TempRateTableDIDRateID) as c, OriginationCode, Code FROM tmp_TempRateTableDIDRate_  GROUP BY OriginationCode,Code,CountryID,EffectiveDate,DialStringPrefix,TimezonesID,City,Tariff,AccessType HAVING c>1) AS tbl) as tbl2 GROUP by a;

		INSERT INTO tmp_JobLog_ (Message)
		SELECT DISTINCT
			CONCAT(IF(OriginationCode IS NOT NULL,CONCAT(OriginationCode,'-'),''), Code, ' DUPLICATE CODE')
		FROM(
			SELECT COUNT(TempRateTableDIDRateID) as c, OriginationCode, Code FROM tmp_TempRateTableDIDRate_  GROUP BY OriginationCode,Code,CountryID,EffectiveDate,DialStringPrefix,TimezonesID,City,Tariff,AccessType HAVING c>1) AS tbl;
	END IF;

	-- this code is no longer in use as we have removed dialstring mapping from did and pkg rate upload
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

			SELECT  COUNT(*) as COUNT INTO totaldialstringcode
			FROM tmp_TempRateTableDIDRate_ vr
			LEFT JOIN tmp_DialString_ ds
				ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  ))
			WHERE vr.ProcessId = p_processId
				AND ds.DialStringID IS NULL
				AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

			IF totaldialstringcode > 0
			THEN

				INSERT INTO tblDialStringCode (DialStringID,DialString,ChargeCode,created_by)
				  SELECT DISTINCT p_dialStringId,vr.DialStringPrefix, Code, 'RMService'
					FROM tmp_TempRateTableDIDRate_ vr
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

				SELECT  COUNT(*) as COUNT INTO totaldialstringcode
				FROM tmp_TempRateTableDIDRate_ vr
					LEFT JOIN tmp_DialString_ ds
						ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  ))
					WHERE vr.ProcessId = p_processId
						AND ds.DialStringID IS NULL
						AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');

				INSERT INTO tmp_JobLog_ (Message)
					  SELECT DISTINCT CONCAT(Code ,' ', vr.DialStringPrefix , ' No PREFIX FOUND')
					  	FROM tmp_TempRateTableDIDRate_ vr
							LEFT JOIN tmp_DialString_ ds
								ON ((vr.Code = ds.ChargeCode and vr.DialStringPrefix = '') OR (vr.DialStringPrefix != '' and vr.DialStringPrefix =  ds.DialString and vr.Code = ds.ChargeCode  ))
							WHERE vr.ProcessId = p_processId
								AND ds.DialStringID IS NULL
								AND vr.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');
			END IF;

			IF totaldialstringcode = 0
			THEN

				INSERT INTO tmp_RateTableDIDRateDialString_
				SELECT DISTINCT
					`TempRateTableDIDRateID`,
					`CodeDeckId`,
					`TimezonesID`,
					`OriginationCode`,
					`OriginationDescription`,
					`DialString`,
					CASE WHEN ds.Description IS NULL OR ds.Description = ''
					THEN
						tblTempRateTableDIDRate.Description
					ELSE
						ds.Description
					END
					AS Description,
					`CityTariff`,
					`AccessType`,
					`OneOffCost`,
					`MonthlyCost`,
					`CostPerCall`,
					`CostPerMinute`,
					`SurchargePerCall`,
					`SurchargePerMinute`,
					`OutpaymentPerCall`,
					`OutpaymentPerMinute`,
					`Surcharges`,
					`Chargeback`,
					`CollectionCostAmount`,
					`CollectionCostPercentage`,
					`RegistrationCostPerNumber`,
					`OneOffCostCurrency`,
					`MonthlyCostCurrency`,
					`CostPerCallCurrency`,
					`CostPerMinuteCurrency`,
					`SurchargePerCallCurrency`,
					`SurchargePerMinuteCurrency`,
					`OutpaymentPerCallCurrency`,
					`OutpaymentPerMinuteCurrency`,
					`SurchargesCurrency`,
					`ChargebackCurrency`,
					`CollectionCostAmountCurrency`,
					`RegistrationCostPerNumberCurrency`,
					`EffectiveDate`,
					`EndDate`,
					`Change`,
					`ProcessId`,
					tblTempRateTableDIDRate.DialStringPrefix as DialStringPrefix,
					`CountryID`
				FROM tmp_TempRateTableDIDRate_ as tblTempRateTableDIDRate
				INNER JOIN tmp_DialString_ ds
					ON ( (tblTempRateTableDIDRate.Code = ds.ChargeCode AND tblTempRateTableDIDRate.DialStringPrefix = '') OR (tblTempRateTableDIDRate.DialStringPrefix != '' AND tblTempRateTableDIDRate.DialStringPrefix =  ds.DialString AND tblTempRateTableDIDRate.Code = ds.ChargeCode  ))
				WHERE tblTempRateTableDIDRate.ProcessId = p_processId
					AND tblTempRateTableDIDRate.Change NOT IN ('Delete', 'R', 'D', 'Blocked','Block');


				INSERT INTO tmp_RateTableDIDRateDialString_2
				SELECT *  FROM tmp_RateTableDIDRateDialString_ where DialStringPrefix!='';

				Delete From tmp_RateTableDIDRateDialString_
				Where DialStringPrefix = ''
				And Code IN (Select DialStringPrefix From tmp_RateTableDIDRateDialString_2);

				INSERT INTO tmp_RateTableDIDRateDialString_3
				SELECT * FROM tmp_RateTableDIDRateDialString_;


				DELETE  FROM tmp_TempRateTableDIDRate_ WHERE  ProcessId = p_processId;

				INSERT INTO tmp_TempRateTableDIDRate_(
					`TempRateTableDIDRateID`,
					CodeDeckId,
					TimezonesID,
					OriginationCode,
					OriginationDescription,
					Code,
					Description,
					CityTariff,
					AccessType,
					OneOffCost,
					MonthlyCost,
					CostPerCall,
					CostPerMinute,
					SurchargePerCall,
					SurchargePerMinute,
					OutpaymentPerCall,
					OutpaymentPerMinute,
					Surcharges,
					Chargeback,
					CollectionCostAmount,
					CollectionCostPercentage,
					RegistrationCostPerNumber,
					OneOffCostCurrency,
					MonthlyCostCurrency,
					CostPerCallCurrency,
					CostPerMinuteCurrency,
					SurchargePerCallCurrency,
					SurchargePerMinuteCurrency,
					OutpaymentPerCallCurrency,
					OutpaymentPerMinuteCurrency,
					SurchargesCurrency,
					ChargebackCurrency,
					CollectionCostAmountCurrency,
					RegistrationCostPerNumberCurrency,
					EffectiveDate,
					EndDate,
					`Change`,
					ProcessId,
					DialStringPrefix,
					CountryID
				)
				SELECT DISTINCT
					`TempRateTableDIDRateID`,
					`CodeDeckId`,
					`TimezonesID`,
					`OriginationCode`,
					`OriginationDescription`,
					`Code`,
					`Description`,
					`CityTariff`,
					`AccessType`,
					`OneOffCost`,
					`MonthlyCost`,
					`CostPerCall`,
					`CostPerMinute`,
					`SurchargePerCall`,
					`SurchargePerMinute`,
					`OutpaymentPerCall`,
					`OutpaymentPerMinute`,
					`Surcharges`,
					`Chargeback`,
					`CollectionCostAmount`,
					`CollectionCostPercentage`,
					`RegistrationCostPerNumber`,
					`OneOffCostCurrency`,
					`MonthlyCostCurrency`,
					`CostPerCallCurrency`,
					`CostPerMinuteCurrency`,
					`SurchargePerCallCurrency`,
					`SurchargePerMinuteCurrency`,
					`OutpaymentPerCallCurrency`,
					`OutpaymentPerMinuteCurrency`,
					`SurchargesCurrency`,
					`ChargebackCurrency`,
					`CollectionCostAmountCurrency`,
					`RegistrationCostPerNumberCurrency`,
					`EffectiveDate`,
					`EndDate`,
					`Change`,
					`ProcessId`,
					DialStringPrefix,
					`CountryID`
				FROM tmp_RateTableDIDRateDialString_3;

			END IF;

		END IF;

	END IF;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_SplitRateTableDIDRate`;
DELIMITER //
CREATE PROCEDURE `prc_SplitRateTableDIDRate`(
	IN `p_processId` VARCHAR(200),
	IN `p_dialcodeSeparator` VARCHAR(50),
	IN `p_seperatecolumn` INT
)
ThisSP:BEGIN

	DECLARE i INTEGER;
	DECLARE v_rowCount_ INT;
	DECLARE v_pointer_ INT;
	DECLARE v_TempRateTableDIDRateID_ INT;
	DECLARE v_OriginationCode_ TEXT;
	DECLARE v_OriginationCountryCode_ VARCHAR(500);
	DECLARE v_Code_ TEXT;
	DECLARE v_CountryCode_ VARCHAR(500);
	DECLARE newcodecount INT(11) DEFAULT 0;

	IF p_dialcodeSeparator !='null'
	THEN

		DROP TEMPORARY TABLE IF EXISTS `my_splits`;
		CREATE TEMPORARY TABLE `my_splits` (
			`TempRateTableDIDRateID` INT(11) NULL DEFAULT NULL,
			`OriginationCode` Text NULL DEFAULT NULL,
			`OriginationCountryCode` Text NULL DEFAULT NULL,
			`Code` Text NULL DEFAULT NULL,
			`CountryCode` Text NULL DEFAULT NULL
		);

		SET i = 1;
		REPEAT
			/*
				p_seperatecolumn = 1 = Origination Code
				p_seperatecolumn = 2 = Destination Code
			*/
			IF(p_seperatecolumn = 1)
			THEN
				INSERT INTO my_splits (TempRateTableDIDRateID, OriginationCode, OriginationCountryCode, Code, CountryCode)
				SELECT TempRateTableDIDRateID , FnStringSplit(OriginationCode, p_dialcodeSeparator, i), OriginationCountryCode, Code, CountryCode  FROM tblTempRateTableDIDRate
				WHERE FnStringSplit(OriginationCode, p_dialcodeSeparator , i) IS NOT NULL
					AND ProcessId = p_processId;
			ELSE
				INSERT INTO my_splits (TempRateTableDIDRateID, OriginationCode, OriginationCountryCode, Code, CountryCode)
				SELECT TempRateTableDIDRateID , OriginationCode, OriginationCountryCode, FnStringSplit(Code, p_dialcodeSeparator, i), CountryCode  FROM tblTempRateTableDIDRate
				WHERE FnStringSplit(Code, p_dialcodeSeparator , i) IS NOT NULL
					AND ProcessId = p_processId;
			END IF;

			SET i = i + 1;
			UNTIL ROW_COUNT() = 0
		END REPEAT;

		UPDATE my_splits SET OriginationCode = trim(OriginationCode), Code = trim(Code);



		INSERT INTO my_splits (TempRateTableDIDRateID, OriginationCode, OriginationCountryCode, Code, CountryCode)
		SELECT TempRateTableDIDRateID, OriginationCode, OriginationCountryCode, Code, CountryCode  FROM tblTempRateTableDIDRate
		WHERE
			(
				(p_seperatecolumn = 1 AND (OriginationCountryCode IS NOT NULL AND OriginationCountryCode <> '') AND (OriginationCode IS NULL OR OriginationCode = '')) OR
				(p_seperatecolumn = 2 AND (CountryCode IS NOT NULL AND CountryCode <> '') AND (Code IS NULL OR Code = ''))
			)
		AND ProcessId = p_processId;


		DROP TEMPORARY TABLE IF EXISTS tmp_newratetable_splite_;
		CREATE TEMPORARY TABLE tmp_newratetable_splite_  (
			RowID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			TempRateTableDIDRateID INT(11) NULL DEFAULT NULL,
			OriginationCode VARCHAR(500) NULL DEFAULT NULL,
			OriginationCountryCode VARCHAR(500) NULL DEFAULT NULL,
			Code VARCHAR(500) NULL DEFAULT NULL,
			CountryCode VARCHAR(500) NULL DEFAULT NULL
		);

		INSERT INTO tmp_newratetable_splite_(TempRateTableDIDRateID,OriginationCode,OriginationCountryCode,Code,CountryCode)
		SELECT
			TempRateTableDIDRateID,
			OriginationCode,
			OriginationCountryCode,
			Code,
			CountryCode
		FROM my_splits
		WHERE
			((p_seperatecolumn = 1 AND OriginationCode like '%-%') OR (p_seperatecolumn = 2 AND Code like '%-%'))
			AND TempRateTableDIDRateID IS NOT NULL;

		SET v_pointer_ = 1;
		SET v_rowCount_ = (SELECT COUNT(*)FROM tmp_newratetable_splite_);

		WHILE v_pointer_ <= v_rowCount_
		DO
			SET v_TempRateTableDIDRateID_ = (SELECT TempRateTableDIDRateID FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);
			SET v_OriginationCode_ = (SELECT OriginationCode FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);
			SET v_OriginationCountryCode_ = (SELECT OriginationCountryCode FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);
			SET v_Code_ = (SELECT Code FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);
			SET v_CountryCode_ = (SELECT CountryCode FROM tmp_newratetable_splite_ t WHERE t.RowID = v_pointer_);

			Call prc_SplitAndInsertRateTableDIDRate(v_TempRateTableDIDRateID_,p_seperatecolumn,v_OriginationCode_,v_OriginationCountryCode_,v_Code_,v_CountryCode_);

			SET v_pointer_ = v_pointer_ + 1;
		END WHILE;

		DELETE FROM my_splits
		WHERE
			((p_seperatecolumn = 1 AND OriginationCode like '%-%') OR (p_seperatecolumn = 2 AND Code like '%-%'))
			AND TempRateTableDIDRateID IS NOT NULL;

		DELETE FROM my_splits
		WHERE (Code = '' OR Code IS NULL) AND (CountryCode = '' OR CountryCode IS NULL);

		INSERT INTO tmp_split_RateTableDIDRate_
		SELECT DISTINCT
			my_splits.TempRateTableDIDRateID as `TempRateTableDIDRateID`,
			`CodeDeckId`,
			`TimezonesID`,
			CONCAT(IFNULL(my_splits.OriginationCountryCode,''),my_splits.OriginationCode) as OriginationCode,
			`OriginationDescription`,
			CONCAT(IFNULL(my_splits.CountryCode,''),my_splits.Code) as Code,
			`Description`,
			`City`,
			`Tariff`,
			`AccessType`,
			`OneOffCost`,
			`MonthlyCost`,
			`CostPerCall`,
			`CostPerMinute`,
			`SurchargePerCall`,
			`SurchargePerMinute`,
			`OutpaymentPerCall`,
			`OutpaymentPerMinute`,
			`Surcharges`,
			`Chargeback`,
			`CollectionCostAmount`,
			`CollectionCostPercentage`,
			`RegistrationCostPerNumber`,
			`OneOffCostCurrency`,
			`MonthlyCostCurrency`,
			`CostPerCallCurrency`,
			`CostPerMinuteCurrency`,
			`SurchargePerCallCurrency`,
			`SurchargePerMinuteCurrency`,
			`OutpaymentPerCallCurrency`,
			`OutpaymentPerMinuteCurrency`,
			`SurchargesCurrency`,
			`ChargebackCurrency`,
			`CollectionCostAmountCurrency`,
			`RegistrationCostPerNumberCurrency`,
			`EffectiveDate`,
			`EndDate`,
			`Change`,
			`ProcessId`,
			`DialStringPrefix`,
			`CountryID`
		FROM my_splits
		INNER JOIN tblTempRateTableDIDRate
			ON my_splits.TempRateTableDIDRateID = tblTempRateTableDIDRate.TempRateTableDIDRateID
		WHERE	tblTempRateTableDIDRate.ProcessId = p_processId;

	END IF;

	IF p_dialcodeSeparator = 'null'
	THEN

		INSERT INTO tmp_split_RateTableDIDRate_
		SELECT DISTINCT
			`TempRateTableDIDRateID`,
			`CodeDeckId`,
			`TimezonesID`,
			CONCAT(IFNULL(tblTempRateTableDIDRate.OriginationCountryCode,''),tblTempRateTableDIDRate.OriginationCode) as OriginationCode,
			`OriginationDescription`,
			CONCAT(IFNULL(tblTempRateTableDIDRate.CountryCode,''),tblTempRateTableDIDRate.Code) as Code,
			`Description`,
			`City`,
			`Tariff`,
			`AccessType`,
			`OneOffCost`,
			`MonthlyCost`,
			`CostPerCall`,
			`CostPerMinute`,
			`SurchargePerCall`,
			`SurchargePerMinute`,
			`OutpaymentPerCall`,
			`OutpaymentPerMinute`,
			`Surcharges`,
			`Chargeback`,
			`CollectionCostAmount`,
			`CollectionCostPercentage`,
			`RegistrationCostPerNumber`,
			`OneOffCostCurrency`,
			`MonthlyCostCurrency`,
			`CostPerCallCurrency`,
			`CostPerMinuteCurrency`,
			`SurchargePerCallCurrency`,
			`SurchargePerMinuteCurrency`,
			`OutpaymentPerCallCurrency`,
			`OutpaymentPerMinuteCurrency`,
			`SurchargesCurrency`,
			`ChargebackCurrency`,
			`CollectionCostAmountCurrency`,
			`RegistrationCostPerNumberCurrency`,
			`EffectiveDate`,
			`EndDate`,
			`Change`,
			`ProcessId`,
			`DialStringPrefix`,
			`CountryID`
		FROM tblTempRateTableDIDRate
		WHERE ProcessId = p_processId;

	END IF;

END//
DELIMITER ;