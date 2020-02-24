use `speakintelligentRM`;


INSERT INTO `tblJobType` (`Code`, `Title`, `Description`, `CreatedDate`, `CreatedBy`, `ModifiedDate`, `ModifiedBy`) VALUES ('DIFT', 'Data Import From Template File', 'Data Import From Template File', '2020-02-17 11:28:08', 'RateManagementSystem', NULL, NULL);


CREATE TABLE IF NOT EXISTS `tblTempDataImport` (
  `RowID` int(11) NOT NULL AUTO_INCREMENT,
  `numberContractId` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PackageContractId` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Number` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PackageTitle` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PricePlanTypeId` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `SalesPrice` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `code` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CurrencyName` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TariffCode` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TerminationCountryIso2` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `InOutboundType` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AccountNumber` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ProductId` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `product` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ProcessID` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ProcessStatus` tinyint(1) NOT NULL DEFAULT '0',
  `AccountID` int(11) NOT NULL DEFAULT '0',
  `CLIRateTableID` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`RowID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `tblTempDataImportCCM` (
  `RowID` int(11) NOT NULL AUTO_INCREMENT,
  `Type` tinyint(4) NOT NULL,
  `Component` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Code` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`RowID`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `tblTempDataImportCCM` (`RowID`, `Type`, `Component`, `Code`) VALUES
	(1, 1, 'Rate', 'TPC'),
	(2, 1, 'Rate', 'TPM'),
	(3, 2, 'OneOffCost', 'BWZ'),
	(4, 2, 'MonthlyCost', 'MON'),
	(5, 3, 'MonthlyCost', 'MOS'),
	(6, 2, 'CostPerCall', 'CCL'),
	(7, 2, 'CostPerMinute', 'CMN'),
	(8, 2, 'Chargeback', 'CHA'),
	(9, 2, 'OutpaymentPerCall', 'OCL'),
	(10, 2, 'OutpaymentPerMinute', 'OMN'),
	(11, 3, 'PackageCostPerMinute', 'PLM'),
	(12, 2, 'SurchargePerCall', 'TCL'),
	(13, 2, 'SurchargePerMinute', 'TMN'),
	(14, 2, 'CollectionCostAmount', 'ICL'),
	(15, 2, 'CollectionCostPercentage', 'INC'),
	(16, 2, 'OneOffCost', 'ONE'),
	(17, 2, 'OutpaymentPerCall', 'OCS'),
	(18, 2, 'SurchargePerMinute', 'T3C'),
	(19, 3, 'MonthlyCost', 'MON'),
	(20, 3, 'OneOffCost', 'ONE'),
	(21, 3, 'RecordingCostPerMinute', 'VRC'),
	(22, 2, 'SurchargePerMinute', 'TIN');



DROP PROCEDURE IF EXISTS `prc_ProcessDataImport`;
DELIMITER //
CREATE PROCEDURE `prc_ProcessDataImport`(
	IN `p_ProcessID` VARCHAR(100)
)
BEGIN

/*	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		SHOW WARNINGS;
-- 		ROLLBACK;
		INSERT INTO tmp_JobLog_ (Message) VALUES ('An error has occurred');
	END;*/

	DECLARE v_AffectedRecords_ INT DEFAULT 0;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET @Created_By = 'Data Import Job';
	SET @Today = CURDATE();

	DROP TEMPORARY TABLE IF EXISTS `tmp_JobLog_`;
	CREATE TEMPORARY TABLE IF NOT EXISTS `tmp_JobLog_` (
	  `Message` VARCHAR(255) NOT NULL
	);

	DROP TEMPORARY TABLE IF EXISTS `tmp_AccessRates_`;
	CREATE TEMPORARY TABLE IF NOT EXISTS `tmp_AccessRates_` (
		`RowID` INT(11) NOT NULL AUTO_INCREMENT,
		`CompanyID` INT(11) NOT NULL,
		`AccountID` INT(11) NOT NULL,
		`CLIRateTableID` INT(11) NOT NULL,
		`OriginationRateID` BIGINT(20) NOT NULL DEFAULT '0',
		`RateID` INT(11) NOT NULL,
		`TimezonesID` BIGINT(20) NOT NULL DEFAULT '1',
		`EffectiveDate` DATE NOT NULL,
		`City` VARCHAR(50) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
		`Tariff` VARCHAR(50) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
		`AccessType` VARCHAR(200) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
		`OneOffCost` DECIMAL(18,6) NULL DEFAULT NULL,
		`MonthlyCost` DECIMAL(18,6) NULL DEFAULT NULL,
		`CostPerCall` DECIMAL(18,6) NULL DEFAULT NULL,
		`CostPerMinute` DECIMAL(18,6) NULL DEFAULT NULL,
		`SurchargePerCall` DECIMAL(18,6) NULL DEFAULT NULL,
		`SurchargePerMinute` DECIMAL(18,6) NULL DEFAULT NULL,
		`OutpaymentPerCall` DECIMAL(18,6) NULL DEFAULT NULL,
		`OutpaymentPerMinute` DECIMAL(18,6) NULL DEFAULT NULL,
		`Surcharges` DECIMAL(18,6) NULL DEFAULT NULL,
		`Chargeback` DECIMAL(18,6) NULL DEFAULT NULL,
		`CollectionCostAmount` DECIMAL(18,6) NULL DEFAULT NULL,
		`CollectionCostPercentage` DECIMAL(18,6) NULL DEFAULT NULL,
		`RegistrationCostPerNumber` DECIMAL(18,6) NULL DEFAULT NULL,
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
		`ApprovedStatus` TINYINT(4) NOT NULL DEFAULT '1',
		`ApprovedBy` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
		`ApprovedDate` DATETIME NULL DEFAULT NULL,
		PRIMARY KEY (`RowID`)
	);

	DROP TEMPORARY TABLE IF EXISTS `tmp_AccessRates_2_`;
	CREATE TEMPORARY TABLE IF NOT EXISTS `tmp_AccessRates_2_` LIKE tmp_AccessRates_;

	DROP TEMPORARY TABLE IF EXISTS `tmp_PackageRates_`;
	CREATE TEMPORARY TABLE IF NOT EXISTS `tmp_PackageRates_` (
		`RowID` INT(11) NOT NULL AUTO_INCREMENT,
		`CompanyID` INT(11) NOT NULL,
		`AccountID` INT(11) NOT NULL,
		`CLIRateTableID` INT(11) NOT NULL,
		`PackageContractId` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
		`PackageId` INT(11) NOT NULL,
		`RateID` INT(11) NOT NULL,
		`TimezonesID` BIGINT(20) NOT NULL DEFAULT '1',
		`EffectiveDate` DATE NOT NULL,
		`OneOffCost` DECIMAL(18,6) NULL DEFAULT NULL,
		`MonthlyCost` DECIMAL(18,6) NULL DEFAULT NULL,
		`PackageCostPerMinute` DECIMAL(18,6) NULL DEFAULT NULL,
		`RecordingCostPerMinute` DECIMAL(18,6) NULL DEFAULT NULL,
		`OneOffCostCurrency` INT(11) NULL DEFAULT NULL,
		`MonthlyCostCurrency` INT(11) NULL DEFAULT NULL,
		`PackageCostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`RecordingCostPerMinuteCurrency` INT(11) NULL DEFAULT NULL,
		`ApprovedStatus` TINYINT(4) NOT NULL DEFAULT '1',
		`ApprovedBy` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
		`ApprovedDate` DATETIME NULL DEFAULT NULL,
		PRIMARY KEY (`RowID`)
	);

	DROP TEMPORARY TABLE IF EXISTS `tmp_PackageRates_2_`;
	CREATE TEMPORARY TABLE IF NOT EXISTS `tmp_PackageRates_2_` LIKE tmp_PackageRates_;

	DROP TEMPORARY TABLE IF EXISTS `tmp_TerminationRates_`;
	CREATE TEMPORARY TABLE IF NOT EXISTS `tmp_TerminationRates_` (
		`RowID` INT(11) NOT NULL AUTO_INCREMENT,
		`CompanyID` INT(11) NOT NULL,
		`AccountID` INT(11) NOT NULL,
		`CLIRateTableID` INT(11) NOT NULL,
		`TimezonesID` BIGINT(20) NOT NULL DEFAULT '1',
		`EffectiveDate` DATE NOT NULL,
		`Rate` DECIMAL(18,6) NULL DEFAULT NULL,
		`ConnectionFee` DECIMAL(18,6) NULL DEFAULT NULL,
		`RateCurrency` INT(11) NULL DEFAULT NULL,
		`ConnectionFeeCurrency` INT(11) NULL DEFAULT NULL,
		`CountryISO` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
		`InOutboundType` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
		`ApprovedStatus` TINYINT(4) NOT NULL DEFAULT '1',
		`ApprovedBy` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
		`ApprovedDate` DATETIME NULL DEFAULT NULL,
		PRIMARY KEY (`RowID`)
	);

	DROP TEMPORARY TABLE IF EXISTS `tmp_TerminationRates_2_`;
	CREATE TEMPORARY TABLE IF NOT EXISTS `tmp_TerminationRates_2_` LIKE tmp_TerminationRates_;

	-- get termination, access and package codedeckid
	SELECT CodeDeckID INTO @CodeDeckID_Termination FROM tblCodeDeck WHERE CompanyId=1 AND Type=1 LIMIT 1; -- termination
	SELECT CodeDeckID INTO @CodeDeckID_Access FROM tblCodeDeck WHERE CompanyId=1 AND Type=2 LIMIT 1; -- access
	SELECT CodeDeckID INTO @CodeDeckID_Package FROM tblCodeDeck WHERE CompanyId=1 AND Type=3 LIMIT 1; -- package

	-- get Access cost component mappiiings
	SELECT GROUP_CONCAT(Code) INTO @Code_OneOffCost FROM tblTempDataImportCCM WHERE Type=2 AND Component = 'OneOffCost';
	SELECT GROUP_CONCAT(Code) INTO @Code_MonthlyCost FROM tblTempDataImportCCM WHERE Type=2 AND Component = 'MonthlyCost';
	SELECT GROUP_CONCAT(Code) INTO @Code_CostPerCall FROM tblTempDataImportCCM WHERE Type=2 AND Component = 'CostPerCall';
	SELECT GROUP_CONCAT(Code) INTO @Code_CostPerMinute FROM tblTempDataImportCCM WHERE Type=2 AND Component = 'CostPerMinute';
	SELECT GROUP_CONCAT(Code) INTO @Code_SurchargePerCall FROM tblTempDataImportCCM WHERE Type=2 AND Component = 'SurchargePerCall';
	SELECT GROUP_CONCAT(Code) INTO @Code_SurchargePerMinute FROM tblTempDataImportCCM WHERE Type=2 AND Component = 'SurchargePerMinute';
	SELECT GROUP_CONCAT(Code) INTO @Code_OutpaymentPerCall FROM tblTempDataImportCCM WHERE Type=2 AND Component = 'OutpaymentPerCall';
	SELECT GROUP_CONCAT(Code) INTO @Code_OutpaymentPerMinute FROM tblTempDataImportCCM WHERE Type=2 AND Component = 'OutpaymentPerMinute';
	SELECT GROUP_CONCAT(Code) INTO @Code_Chargeback FROM tblTempDataImportCCM WHERE Type=2 AND Component = 'Chargeback';
	SELECT GROUP_CONCAT(Code) INTO @Code_CollectionCostAmount FROM tblTempDataImportCCM WHERE Type=2 AND Component = 'CollectionCostAmount';
	SELECT GROUP_CONCAT(Code) INTO @Code_CollectionCostPercentage FROM tblTempDataImportCCM WHERE Type=2 AND Component = 'CollectionCostPercentage';
	SELECT GROUP_CONCAT(Code) INTO @Code_RegistrationCostPerNumber FROM tblTempDataImportCCM WHERE Type=2 AND Component = 'RegistrationCostPerNumber';

	-- set Default Timezone where timezone is 'n.v.t.'
	UPDATE tblTempDataImport SET TariffCode='Default' WHERE TariffCode = 'n.v.t.';

	-- find account and number and update AccountID and CLIRateTableID in the table
	UPDATE
		tblTempDataImport tdi
	INNER JOIN
		tblAccount a ON a.Number = tdi.AccountNumber
	INNER JOIN
		tblCLIRateTable crt ON crt.AccountID = a.AccountID AND crt.ContractID = tdi.numberContractId
	INNER JOIN
		tblAccountService ac ON ac.AccountServiceID = crt.AccountServiceID
	SET
		tdi.AccountID = a.AccountID,
		tdi.CLIRateTableID = crt.CLIRateTableID
	WHERE
		a.Status = 1 AND ac.Status = 1 AND crt.Status = 1;


	SET @i = 0;
	SET @Last_RowID = 0;
	SELECT COUNT(*) INTO @TotalDataCount FROM tblTempDataImport WHERE ProcessID=p_ProcessID;

	WHILE @i < @TotalDataCount
	DO
		SELECT `RowID`, AccountID, CLIRateTableID, PricePlanTypeId INTO @RowID,@AccountID,@CLIRateTableID,@PricePlanTypeId FROM tblTempDataImport WHERE ProcessID=p_ProcessID AND RowID > @Last_RowID LIMIT 1;
		SET @AccountID = IFNULL(@AccountID,0);
		SET @CLIRateTableID = IFNULL(@CLIRateTableID,0);
		SET @PricePlanTypeId = IFNULL(@PricePlanTypeId,0);

		IF(@AccountID > 0 AND @CLIRateTableID > 0)
		THEN

			IF(@PricePlanTypeId = 1) -- Access
			THEN
				INSERT INTO tmp_AccessRates_
				(
					CompanyID,
					AccountID,
					CLIRateTableID,
					OriginationRateID,
					RateID,
					TimezonesID,
					EffectiveDate,
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
					ApprovedStatus,
					ApprovedBy,
					ApprovedDate
				)
				SELECT
					tmp.CompanyID,
					tmp.AccountID,
					tmp.CLIRateTableID,
					o_r.RateID,
					r.RateID,
					t.TimezonesID,
					@Today AS EffectiveDate,
					tmp.City,
					tmp.Tariff,
					tmp.accessType,
					tmp.OneOffCost,
					tmp.MonthlyCost,
					tmp.CostPerCall,
					tmp.CostPerMinute,
					tmp.SurchargePerCall,
					tmp.SurchargePerMinute,
					tmp.OutpaymentPerCall,
					tmp.OutpaymentPerMinute,
					tmp.Surcharges,
					tmp.Chargeback,
					tmp.CollectionCostAmount,
					tmp.CollectionCostPercentage,
					tmp.RegistrationCostPerNumber,
					c.CurrencyID AS OneOffCostCurrency,
					c.CurrencyID AS MonthlyCostCurrency,
					c.CurrencyID AS CostPerCallCurrency,
					c.CurrencyID AS CostPerMinuteCurrency,
					c.CurrencyID AS SurchargePerCallCurrency,
					c.CurrencyID AS SurchargePerMinuteCurrency,
					c.CurrencyID AS OutpaymentPerCallCurrency,
					c.CurrencyID AS OutpaymentPerMinuteCurrency,
					c.CurrencyID AS SurchargesCurrency,
					c.CurrencyID AS ChargebackCurrency,
					c.CurrencyID AS CollectionCostAmountCurrency,
					c.CurrencyID AS RegistrationCostPerNumberCurrency,
					1 AS ApprovedStatus,
					@Created_By AS ApprovedBy,
					@Today AS ApprovedDate
				FROM
				(
					SELECT
						a.CompanyID,
						tmp.AccountID,
						tmp.CLIRateTableID,
						st.City,st.Tariff,CONCAT(IFNULL(st.countryCode,''), TRIM(LEADING '0' FROM IFNULL(st.prefixName,''))) AS `Prefix`,st.accessType,tmp.InOutboundType, tmp.SalesPrice, tmp.CurrencyName, tmp.TariffCode,
						IF(FIND_IN_SET(code, @Code_OneOffCost) > 0, SalesPrice,NULL) AS `OneOffCost`,
						IF(FIND_IN_SET(code, @Code_MonthlyCost) > 0, SalesPrice,NULL) AS `MonthlyCost`,
						IF(FIND_IN_SET(code, @Code_CostPerCall) > 0, SalesPrice,NULL) AS `CostPerCall`,
						IF(FIND_IN_SET(code, @Code_CostPerMinute) > 0, SalesPrice,NULL) AS `CostPerMinute`,
						IF(FIND_IN_SET(code, @Code_SurchargePerCall) > 0, SalesPrice,NULL) AS `SurchargePerCall`,
						IF(FIND_IN_SET(code, @Code_SurchargePerMinute) > 0, SalesPrice,NULL) AS `SurchargePerMinute`,
						IF(FIND_IN_SET(code, @Code_OutpaymentPerCall) > 0, SalesPrice,NULL) AS `OutpaymentPerCall`,
						IF(FIND_IN_SET(code, @Code_OutpaymentPerMinute) > 0, SalesPrice,NULL) AS `OutpaymentPerMinute`,
						IF(FIND_IN_SET(code, @Code_Chargeback) > 0, SalesPrice,NULL) AS `Chargeback`,
						IF(FIND_IN_SET(code, @Code_CollectionCostAmount) > 0, SalesPrice,NULL) AS `CollectionCostAmount`,
						IF(FIND_IN_SET(code, @Code_CollectionCostPercentage) > 0, SalesPrice,NULL) AS `CollectionCostPercentage`,
						IF(FIND_IN_SET(code, @Code_RegistrationCostPerNumber) > 0, SalesPrice,NULL) AS `RegistrationCostPerNumber`
					FROM
						tblServiceTemplate st
					INNER JOIN
						tblDynamicFields df ON df.CompanyID=st.CompanyID AND df.`Type`='serviceTemplate' AND df.`Status`=1
					INNER JOIN
						tblDynamicFieldsValue dfv ON dfv.DynamicFieldsID=df.DynamicFieldsID AND dfv.ParentID = st.ServiceTemplateId
					INNER JOIN
						tblTempDataImport tmp ON tmp.ProductId = dfv.FieldValue
					INNER JOIN
						tblAccount a ON a.AccountID = tmp.AccountID
					INNER JOIN
						tblTempDataImportCCM ccm ON ccm.Type=2 AND ccm.Code = tmp.code -- Type=2 -> Access
					WHERE
						tmp.RowID = @RowID AND
						st.CompanyID = a.CompanyID
				) tmp
				INNER JOIN
					tblRate r ON r.Code = tmp.`Prefix` AND r.CodeDeckID = @CodeDeckID_Access
				LEFT JOIN
					tblRate o_r ON o_r.Code = tmp.InOutboundType AND r.CodeDeckID = @CodeDeckID_Access
				INNER JOIN
					tblTimezones t ON t.Title = tmp.TariffCode
				LEFT JOIN
					tblCurrency c ON c.Code = tmp.CurrencyName;


			ELSEIF(@PricePlanTypeId = 7) -- Package
			THEN

				INSERT INTO tmp_PackageRates_
				(
					CompanyID,
					AccountID,
					CLIRateTableID,
					PackageContractId,
					PackageId,
					RateID,
					TimezonesID,
					EffectiveDate,
					OneOffCost,
					MonthlyCost,
					PackageCostPerMinute,
					RecordingCostPerMinute,
					OneOffCostCurrency,
					MonthlyCostCurrency,
					PackageCostPerMinuteCurrency,
					RecordingCostPerMinuteCurrency,
					ApprovedStatus,
					ApprovedBy,
					ApprovedDate
				)
				SELECT
					tmp.CompanyID,
					tmp.AccountID,
					tmp.CLIRateTableID,
					tmp.PackageContractId,
					tmp.PackageId,
					r.RateID,
					t.TimezonesID,
					@Today AS EffectiveDate,
					tmp.OneOffCost,
					tmp.MonthlyCost,
					tmp.PackageCostPerMinute,
					tmp.RecordingCostPerMinute,
					c.CurrencyID AS OneOffCostCurrency,
					c.CurrencyID AS MonthlyCostCurrency,
					c.CurrencyID AS PackageCostPerMinuteCurrency,
					c.CurrencyID AS RecordingCostPerMinuteCurrency,
					1 AS ApprovedStatus,
					@Created_By AS ApprovedBy,
					@Today AS ApprovedDate
				FROM
				(
					SELECT
						a.CompanyID,
						tmp.AccountID,
						tmp.CLIRateTableID,
						tmp.PackageContractId,
						p.PackageId,
						p.Name AS PackageName,
						tmp.SalesPrice, tmp.CurrencyName, tmp.TariffCode,
						IF(FIND_IN_SET(code, @Code_OneOffCost) > 0, SalesPrice,NULL) AS `OneOffCost`,
						IF(FIND_IN_SET(code, @Code_MonthlyCost) > 0, SalesPrice,NULL) AS `MonthlyCost`,
						IF(FIND_IN_SET(code, @Code_PackageCostPerMinute) > 0, SalesPrice,NULL) AS `PackageCostPerMinute`,
						IF(FIND_IN_SET(code, @Code_RecordingCostPerMinutee) > 0, SalesPrice,NULL) AS `RecordingCostPerMinute`
					FROM
						tblPackage p
					INNER JOIN
						tblTempDataImport tmp ON tmp.PackageTitle = p.Name
					INNER JOIN
						tblAccount a ON a.AccountID = tmp.AccountID
					INNER JOIN
						tblTempDataImportCCM ccm ON ccm.Type=3 AND ccm.Code = tmp.code -- Type=3 -> Package
					WHERE
						tmp.RowID = @RowID AND
						p.CompanyID = a.CompanyID
				) tmp
				INNER JOIN
					tblRate r ON r.Code = tmp.PackageName AND r.CodeDeckID = @CodeDeckID_Package
				INNER JOIN
					tblTimezones t ON t.Title = tmp.TariffCode
				LEFT JOIN
					tblCurrency c ON c.Code = tmp.CurrencyName;


			ELSEIF(@PricePlanTypeId = 2) -- Termination
			THEN

				INSERT INTO tmp_TerminationRates_
				(
					CompanyID,
					AccountID,
					CLIRateTableID,
					TimezonesID,
					EffectiveDate,
					Rate,
					ConnectionFee,
					RateCurrency,
					ConnectionFeeCurrency,
					CountryISO,
					InOutboundType,
					ApprovedStatus,
					ApprovedBy,
					ApprovedDate
				)
				SELECT
					tmp.CompanyID,
					tmp.AccountID,
					tmp.CLIRateTableID,
					t.TimezonesID,
					@Today AS EffectiveDate,
					tmp.Rate,
					tmp.ConnectionFee,
					c.CurrencyID AS RateCurrency,
					c.CurrencyID AS ConnectionFeeCurrency,
					tmp.CountryISO,
					tmp.InOutboundType,
					1 AS ApprovedStatus,
					@Created_By AS ApprovedBy,
					@Today AS ApprovedDate
				FROM
				(
					SELECT
						a.CompanyID,
						tmp.AccountID,
						tmp.CLIRateTableID,
						tmp.CountryISO,
						tmp.InOutboundType,
						tmp.SalesPrice, tmp.CurrencyName, tmp.TariffCode,
						IF(FIND_IN_SET(code, @Code_Rate) > 0, SalesPrice,NULL) AS `Rate`,
						IF(FIND_IN_SET(code, @Code_ConnectionFee) > 0, SalesPrice,NULL) AS `ConnectionFee`
					FROM
						tblTempDataImport tmp
					INNER JOIN
						tblAccount a ON a.AccountID = tmp.AccountID
					INNER JOIN
						tblTempDataImportCCM ccm ON ccm.Type=1 AND ccm.Code = tmp.code -- Type=1 -> Termination
					WHERE
						tmp.RowID = @RowID AND
						p.CompanyID = a.CompanyID
				) tmp
				INNER JOIN
					tblTimezones t ON t.Title = tmp.TariffCode
				LEFT JOIN
					tblCurrency c ON c.Code = tmp.CurrencyName;

			ELSE
				INSERT INTO tmp_JobLog_ VALUES(CONCAT('Invalid PricePlanTypeId:',@PricePlanTypeId,' in RowID:',@RowID));
			END IF;

		ELSE
			INSERT INTO tmp_JobLog_ VALUES(CONCAT('Account or/and Number not found in Neon against RowID:',@RowID));
		END IF;

		SET @Last_RowID = @RowID;
		SET @i = (@i + 1);

	END WHILE;


	/*** Access Data Process Starts ***/

	-- group access records and insert it in another table
	INSERT INTO tmp_AccessRates_2_
	SELECT
		NULL, -- Primary Key (AUTO_INCREMENT)
		`CompanyID`,
		`AccountID`,
		`CLIRateTableID`,
		MAX(`OriginationRateID`) AS `OriginationRateID`,
		MAX(`RateID`) AS `RateID`,
		MAX(`TimezonesID`) AS `TimezonesID`,
		MAX(`EffectiveDate`) AS `EffectiveDate`,
		MAX(`City`) AS `City`,
		MAX(`Tariff`) AS `Tariff`,
		MAX(`AccessType`) AS `AccessType`,
		MAX(`OneOffCost`) AS `OneOffCost`,
		MAX(`MonthlyCost`) AS `MonthlyCost`,
		MAX(`CostPerCall`) AS `CostPerCall`,
		MAX(`CostPerMinute`) AS `CostPerMinute`,
		MAX(`SurchargePerCall`) AS `SurchargePerCall`,
		MAX(`SurchargePerMinute`) AS `SurchargePerMinute`,
		MAX(`OutpaymentPerCall`) AS `OutpaymentPerCall`,
		MAX(`OutpaymentPerMinute`) AS `OutpaymentPerMinute`,
		MAX(`Surcharges`) AS `Surcharges`,
		MAX(`Chargeback`) AS `Chargeback`,
		MAX(`CollectionCostAmount`) AS `CollectionCostAmount`,
		MAX(`CollectionCostPercentage`) AS `CollectionCostPercentage`,
		MAX(`RegistrationCostPerNumber`) AS `RegistrationCostPerNumber`,
		MAX(`OneOffCostCurrency`) AS `OneOffCostCurrency`,
		MAX(`MonthlyCostCurrency`) AS `MonthlyCostCurrency`,
		MAX(`CostPerCallCurrency`) AS `CostPerCallCurrency`,
		MAX(`CostPerMinuteCurrency`) AS `CostPerMinuteCurrency`,
		MAX(`SurchargePerCallCurrency`) AS `SurchargePerCallCurrency`,
		MAX(`SurchargePerMinuteCurrency`) AS `SurchargePerMinuteCurrency`,
		MAX(`OutpaymentPerCallCurrency`) AS `OutpaymentPerCallCurrency`,
		MAX(`OutpaymentPerMinuteCurrency`) AS `OutpaymentPerMinuteCurrency`,
		MAX(`SurchargesCurrency`) AS `SurchargesCurrency`,
		MAX(`ChargebackCurrency`) AS `ChargebackCurrency`,
		MAX(`CollectionCostAmountCurrency`) AS `CollectionCostAmountCurrency`,
		MAX(`RegistrationCostPerNumberCurrency`) AS `RegistrationCostPerNumberCurrency`,
		MAX(`ApprovedStatus`) AS `ApprovedStatus`,
		MAX(`ApprovedBy`) AS `ApprovedBy`,
		MAX(`ApprovedDate`) AS `ApprovedDate`
	FROM
		tmp_AccessRates_
	GROUP BY
		CompanyID,AccountID,CLIRateTableID;

	SET @i = 0;
	SET @Last_RowID = 0;
	SELECT COUNT(*) INTO @AccessDataCount FROM tmp_AccessRates_2_;

	WHILE @i < @AccessDataCount
	DO
		SELECT `RowID` INTO @RowID FROM tmp_AccessRates_2_ WHERE `RowID` > @Last_RowID LIMIT 1;

		SET @CurrentTimeMS = ROUND(UNIX_TIMESTAMP(CURTIME(4)) * 1000);
		SET @RateTableName = CONCAT('RateTable_Access_',$RowID,'_',@CurrentTimeMS);

		-- create ratetable
		INSERT INTO tblRateTable (CompanyId,CodeDeckId,RateTableName,TrunkID,CurrencyID,RoundChargedAmount,DIDCategoryID,Type,AppliedTo)
		SELECT
			CompanyID, CodeDeckID, @RateTableName, 1 AS TrunkID, MonthlyCostCurrency,6 AS RoundChargedAmount, 1 AS DIDCategoryID, 2 AS Type, 1 AS AppliedTo
		FROM
			tmp_AccessRates_2_
		WHERE
			RowID = @RowID;

		SET @RateTableID = LAST_INSERT_ID();

		-- insert rate in rate table
		INSERT INTO tblRateTableDIDRate (OriginationRateID,RateID,RateTableId,TimezonesID,EffectiveDate,City,Tariff,AccessType,OneOffCost,MonthlyCost,CostPerCall,CostPerMinute,SurchargePerCall,SurchargePerMinute,OutpaymentPerCall,OutpaymentPerMinute,Surcharges,Chargeback,CollectionCostAmount,CollectionCostPercentage,RegistrationCostPerNumber,OneOffCostCurrency,MonthlyCostCurrency,CostPerCallCurrency,CostPerMinuteCurrency,SurchargePerCallCurrency,SurchargePerMinuteCurrency,OutpaymentPerCallCurrency,OutpaymentPerMinuteCurrency,SurchargesCurrency,ChargebackCurrency,CollectionCostAmountCurrency,RegistrationCostPerNumberCurrency,created_at,CreatedBy,ApprovedStatus,ApprovedBy,ApprovedDate)
		SELECT
			OriginationRateID,RateID,@RateTableID,TimezonesID,EffectiveDate,City,Tariff,AccessType,OneOffCost,MonthlyCost,CostPerCall,CostPerMinute,SurchargePerCall,SurchargePerMinute,OutpaymentPerCall,OutpaymentPerMinute,Surcharges,Chargeback,CollectionCostAmount,CollectionCostPercentage,RegistrationCostPerNumber,OneOffCostCurrency,MonthlyCostCurrency,CostPerCallCurrency,CostPerMinuteCurrency,SurchargePerCallCurrency,SurchargePerMinuteCurrency,OutpaymentPerCallCurrency,OutpaymentPerMinuteCurrency,SurchargesCurrency,ChargebackCurrency,CollectionCostAmountCurrency,RegistrationCostPerNumberCurrency,NOW(),@Created_By,ApprovedStatus,ApprovedBy,ApprovedDate
		FROM
			tmp_AccessRates_2_
		WHERE
			RowID = @RowID;

		-- update Special Access Rate Table ID against number
		UPDATE
			tblCLIRateTable cli
		JOIN
			tmp_AccessRates_2_ tmp ON tmp.AccountID = cli.AccountID and tmp.CLIRateTableID = cli.CLIRateTableID
		SET
			SpecialRateTableID = @RateTableID
		WHERE
			tmp.RowID = @RowID;

		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();


		SET @Last_RowID = @RowID;
		SET @i = (@i + 1);

	END WHILE;

	/*** Access Data Process Ends ***/



	/*** Package Data Process Starts ***/

	-- package records insert it in another table
	INSERT INTO tmp_PackageRates_2_
	SELECT
		NULL, -- Primary Key (AUTO_INCREMENT)
		`CompanyID`,
		`AccountID`,
		`CLIRateTableID`,
		MAX(`PackageContractId`) AS `PackageContractId`,
		MAX(`RateID`) AS `RateID`,
		MAX(`TimezonesID`) AS `TimezonesID`,
		MAX(`EffectiveDate`) AS `EffectiveDate`,
		MAX(`OneOffCost`) AS `OneOffCost`,
		MAX(`MonthlyCost`) AS `MonthlyCost`,
		MAX(`PackageCostPerMinute`) AS `PackageCostPerMinute`,
		MAX(`RecordingCostPerMinute`) AS `RecordingCostPerMinute`,
		MAX(`OneOffCostCurrency`) AS `OneOffCostCurrency`,
		MAX(`MonthlyCostCurrency`) AS `MonthlyCostCurrency`,
		MAX(`PackageCostPerMinuteCurrency`) AS `PackageCostPerMinuteCurrency`,
		MAX(`RecordingCostPerMinuteCurrency`) AS `RecordingCostPerMinuteCurrency`,
		MAX(`ApprovedStatus`) AS `ApprovedStatus`,
		MAX(`ApprovedBy`) AS `ApprovedBy`,
		MAX(`ApprovedDate`) AS `ApprovedDate`
	FROM
		tmp_PackageRates_
	GROUP BY
		CompanyID,AccountID,CLIRateTableID;

	SET @i = 0;
	SET @Last_RowID = 0;
	SELECT COUNT(*) INTO @PackageDataCount FROM tmp_PackageRates_2_;

	WHILE @i < @PackageDataCount
	DO
		SELECT `RowID` INTO @RowID FROM tmp_PackageRates_2_ WHERE `RowID` > @Last_RowID LIMIT 1;

		SET @CurrentTimeMS = ROUND(UNIX_TIMESTAMP(CURTIME(4)) * 1000);
		SET @RateTableName = CONCAT('RateTable_Package_',$RowID,'_',@CurrentTimeMS);

		-- create ratetable
		INSERT INTO tblRateTable (CompanyId,CodeDeckId,RateTableName,TrunkID,CurrencyID,RoundChargedAmount,DIDCategoryID,Type,AppliedTo)
		SELECT
			CompanyID, CodeDeckID, @RateTableName, 1 AS TrunkID, MonthlyCostCurrency,6 AS RoundChargedAmount, 0 AS DIDCategoryID, 3 AS Type, 1 AS AppliedTo
		FROM
			tmp_PackageRates_2_
		WHERE
			RowID = @RowID;

		SET @RateTableID = LAST_INSERT_ID();

		-- insert rate in rate table
		INSERT INTO tblRateTablePKGRate (RateID,RateTableId,TimezonesID,EffectiveDate,OneOffCost,MonthlyCost,PackageCostPerMinute,RecordingCostPerMinute,OneOffCostCurrency,MonthlyCostCurrency,PackageCostPerMinuteCurrency,RecordingCostPerMinuteCurrency,created_at,CreatedBy,ApprovedStatus,ApprovedBy,ApprovedDate)
		SELECT
			RateID,@RateTableID,TimezonesID,EffectiveDate,OneOffCost,MonthlyCost,PackageCostPerMinute,RecordingCostPerMinute,OneOffCostCurrency,MonthlyCostCurrency,PackageCostPerMinuteCurrency,RecordingCostPerMinuteCurrency,NOW(),@Created_By,ApprovedStatus,ApprovedBy,ApprovedDate
		FROM
			tmp_PackageRates_2_
		WHERE
			RowID = @RowID;


		-- update Special Package Rate Table ID against number
		UPDATE
			tblCLIRateTable cli
		INNER JOIN
			tblAccountServicePackage asp ON asp.AccountServicePackageID = cli.AccountServicePackageID
		INNER JOIN
			tmp_AccessRates_2_ tmp ON tmp.AccountID = cli.AccountID and tmp.CLIRateTableID = cli.CLIRateTableID
		SET
			asp.SpecialPackageRateTableID = @RateTableID
		WHERE
			tmp.RowID = @RowID;

		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();


		SET @Last_RowID = @RowID;
		SET @i = (@i + 1);

	END WHILE;

	/*** Package Data Process Ends ***/



	/*** Termination Data Process Starts ***/

	-- termination records insert it in another table
	INSERT INTO tmp_TerminationRates_2_
	SELECT
		NULL, -- Primary Key (AUTO_INCREMENT)
		`CompanyID`,
		`AccountID`,
		`CLIRateTableID`,
		MAX(`TimezonesID`) AS `TimezonesID`,
		MAX(`EffectiveDate`) AS `EffectiveDate`,
		MAX(`Rate`) AS `Rate`,
		MAX(`ConnectionFee`) AS `ConnectionFee`,
		MAX(`RateCurrency`) AS `RateCurrency`,
		MAX(`ConnectionFeeCurrency`) AS `ConnectionFeeCurrency`,
		MAX(`CountryISO`) AS `CountryISO`,
		MAX(`InOutboundType`) AS `InOutboundType`,
		MAX(`ApprovedStatus`) AS `ApprovedStatus`,
		MAX(`ApprovedBy`) AS `ApprovedBy`,
		MAX(`ApprovedDate`) AS `ApprovedDate`
	FROM
		tmp_TerminationRates_
	GROUP BY
		CompanyID,AccountID,CLIRateTableID;

	SET @i = 0;
	SET @Last_RowID = 0;
	SELECT COUNT(*) INTO @TerminationDataCount FROM tmp_TerminationRates_2_;

	WHILE @i < @TerminationDataCount
	DO
		SELECT `RowID` INTO @RowID FROM tmp_TerminationRates_2_ WHERE `RowID` > @Last_RowID LIMIT 1;

		SET @CurrentTimeMS = ROUND(UNIX_TIMESTAMP(CURTIME(4)) * 1000);
		SET @RateTableName = CONCAT('RateTable_Termination_',$RowID,'_',@CurrentTimeMS);

		-- create ratetable
		INSERT INTO tblRateTable (CompanyId,CodeDeckId,RateTableName,TrunkID,CurrencyID,RoundChargedAmount,DIDCategoryID,Type,AppliedTo)
		SELECT
			CompanyID, CodeDeckID, @RateTableName, 1 AS TrunkID, MonthlyCostCurrency,6 AS RoundChargedAmount, 0 AS DIDCategoryID, 1 AS Type, 1 AS AppliedTo
		FROM
			tmp_TerminationRates_2_
		WHERE
			RowID = @RowID;

		SET @RateTableID = LAST_INSERT_ID();

		-- insert rate in rate table
		INSERT INTO tblRateTableRate (OriginationRateID,RateID,RateTableId,TimezonesID,EffectiveDate,Rate,RateN,ConnectionFee,RateCurrency,ConnectionFeeCurrency,Interval1,IntervalN,MinimumDuration,created_at,CreatedBy,ApprovedStatus,ApprovedBy,ApprovedDate)
		SELECT
			0 AS OriginationRateID,r.RateID,@RateTableID,tmp.TimezonesID,tmp.EffectiveDate,tmp.Rate,tmp.Rate AS RateN,tmp.ConnectionFee,tmp.RateCurrency,tmp.ConnectionFeeCurrency,r.Interval1,r.IntervalN,r.MinimumDuration,NOW(),@Created_By,tmp.ApprovedStatus,tmp.ApprovedBy,tmp.ApprovedDate
		FROM
			tmp_TerminationRates_2_ tmp
		INNER JOIN
			tblCountry c ON c.ISO2 = tmp.CountryISO
		INNER JOIN
			tblRate r ON r.CompanyID = tmp.CompanyID AND r.CodeDeckID = @CodeDeckID_Termination AND r.CountryID = c.CountryID AND r.Type = tmp.InOutboundType
		WHERE
			tmp.RowID = @RowID;


		-- update Special Termination Rate Table ID against number
		UPDATE
			tblCLIRateTable cli
		JOIN
			tmp_TerminationRates_2_ tmp ON tmp.AccountID = cli.AccountID and tmp.CLIRateTableID = cli.CLIRateTableID
		SET
			SpecialTerminationRateTableID = @RateTableID
		WHERE
			tmp.RowID = @RowID;

		SET v_AffectedRecords_ = v_AffectedRecords_ + FOUND_ROWS();


		SET @Last_RowID = @RowID;
		SET @i = (@i + 1);

	END WHILE;

	INSERT INTO tmp_JobLog_ (Message) SELECT CONCAT(v_AffectedRecords_ , ' Account->Numbers Uploaded.');

	/*** Termination Data Process Ends ***/

	SELECT * FROM tmp_JobLog_;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_CronJobAllPending`;
DELIMITER //
CREATE PROCEDURE `prc_CronJobAllPending`(
	IN `p_CompanyID` INT
)
BEGIN
	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

    SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'CDR'
		AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
	ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'CDR'
		AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID,
	   TBL1.JobLoggedUserID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'BI'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'BI'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;




	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'CD'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Porta"%'
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'CD'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Porta"%'
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		tblCronJobCommand.Command,
		tblCronJob.CronJobID
	FROM tblCronJob
	INNER JOIN tblCronJobCommand
		ON tblCronJobCommand.CronJobCommandID = tblCronJob.CronJobCommandID
	WHERE tblCronJob.CompanyID = p_CompanyID
	AND tblCronJob.Status = 1
	AND tblCronJob.Active = 0;






	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'BIS'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'BIS'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'VD'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Porta"%'
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'VD'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Porta"%'
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'RCC'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'RCC'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;




	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'RCV'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'RCV'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;





	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'INU'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'INU'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'CD'
			AND js.Code = 'p'
			AND j.CompanyID = p_CompanyID
			AND j.Options like '%"Format":"Rate Sheet"%'
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'CD'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
			AND j.Options like '%"Format":"Rate Sheet"%'
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'BIR'
			AND js.Code = 'p'
			AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'BIR'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'BLE'
			AND js.Code = 'p'
			AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'BLE'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'BAE'
			AND js.Code = 'p'
			AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'BAE'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'VU'
			AND js.Code = 'p'
			AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'VU'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


    SELECT
    	  "CodeDeckUpload",
        TBL1.JobID,
        TBL1.Options,
        TBL1.AccountID
    FROM
    (
        SELECT
            j.Options,
            j.AccountID,
            j.JobID,
            j.JobLoggedUserID,
            @row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			   @prev_JobLoggedUserID  := j.JobLoggedUserID,
 			   @prev_created_at  := created_at
        FROM tblJob j
        INNER JOIN tblJobType jt
            ON j.JobTypeID = jt.JobTypeID
        INNER JOIN tblJobStatus js
            ON j.JobStatusID = js.JobStatusID
         ,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
        WHERE jt.Code = 'CDU'
            AND js.Code = 'p'
            AND j.CompanyID = p_CompanyID
         ORDER BY j.JobLoggedUserID,j.created_at ASC
    ) TBL1
    LEFT JOIN
    (
        SELECT
            JobLoggedUserID
        FROM tblJob j
        INNER JOIN tblJobType jt
            ON j.JobTypeID = jt.JobTypeID
        INNER JOIN tblJobStatus js
            ON j.JobStatusID = js.JobStatusID
        WHERE jt.Code = 'CDU'
            AND js.Code = 'I'
            AND j.CompanyID = p_CompanyID
    ) TBL2
        ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
    WHERE TBL1.rowno = 1
    AND TBL2.JobLoggedUserID IS NULL;

	SELECT
    	  "ImportTranslations",
        TBL1.JobID,
        TBL1.Options,
        TBL1.AccountID
    FROM
    (
        SELECT
            j.Options,
            j.AccountID,
            j.JobID,
            j.JobLoggedUserID,
            @row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			   @prev_JobLoggedUserID  := j.JobLoggedUserID,
 			   @prev_created_at  := created_at
        FROM tblJob j
        INNER JOIN tblJobType jt
            ON j.JobTypeID = jt.JobTypeID
        INNER JOIN tblJobStatus js
            ON j.JobStatusID = js.JobStatusID
         ,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
        WHERE jt.Code = 'ILT'
            AND js.Code = 'p'
            AND j.CompanyID = p_CompanyID
         ORDER BY j.JobLoggedUserID,j.created_at ASC
    ) TBL1
    LEFT JOIN
    (
        SELECT
            JobLoggedUserID
        FROM tblJob j
        INNER JOIN tblJobType jt
            ON j.JobTypeID = jt.JobTypeID
        INNER JOIN tblJobStatus js
            ON j.JobStatusID = js.JobStatusID
        WHERE jt.Code = 'ILT'
            AND js.Code = 'I'
            AND j.CompanyID = p_CompanyID
    ) TBL2
        ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
    WHERE TBL1.rowno = 1
    AND TBL2.JobLoggedUserID IS NULL;


    SELECT
        TBL1.JobID,
        TBL1.Options,
        TBL1.AccountID
    FROM
    (
        SELECT
            j.Options,
            j.AccountID,
            j.JobID,
            j.JobLoggedUserID,
            @row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
				@prev_JobLoggedUserID  := j.JobLoggedUserID,
				@prev_created_at  := created_at
        FROM tblJob j
        INNER JOIN tblJobType jt
            ON j.JobTypeID = jt.JobTypeID
        INNER JOIN tblJobStatus js
            ON j.JobStatusID = js.JobStatusID
         ,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
        WHERE jt.Code = 'IR'
            AND js.Code = 'p'
            AND j.CompanyID = p_CompanyID
         ORDER BY j.JobLoggedUserID,j.created_at ASC
    ) TBL1
    LEFT JOIN
    (
        SELECT
            JobLoggedUserID
        FROM tblJob j
        INNER JOIN tblJobType jt
            ON j.JobTypeID = jt.JobTypeID
        INNER JOIN tblJobStatus js
            ON j.JobStatusID = js.JobStatusID
        WHERE jt.Code = 'IR'
            AND js.Code = 'I'
            AND j.CompanyID = p_CompanyID
    ) TBL2
        ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
    WHERE TBL1.rowno = 1
    AND TBL2.JobLoggedUserID IS NULL;




	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'CD'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Sippy"%'
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'CD'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Sippy"%'
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'VD'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Sippy"%'
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'VD'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Sippy"%'
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;




	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'CD'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		AND (j.Options like '%"Format":"Vos 3.2"%' OR j.Options like '%"Format":"Vos 2.0"%')
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'CD'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
		AND (j.Options like '%"Format":"Vos 3.2"%' OR j.Options like '%"Format":"Vos 2.0"%')
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'VD'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		AND (j.Options like '%"Format":"Vos 3.2"%' OR j.Options like '%"Format":"Vos 2.0"%')
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'VD'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
		AND (j.Options like '%"Format":"Vos 3.2"%' OR j.Options like '%"Format":"Vos 2.0"%')
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'GRT'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'GRT'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'RTU'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'RTU'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'DRTU'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'DRTU'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'PRTU'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'PRTU'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


    SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'VDR'
		AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
	ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'VDR'
		AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;




	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'MGA'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'MGA'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'DSU'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'DSU'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'QIP'
			AND js.Code = 'p'
			AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'QIP'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'ICU'
			AND js.Code = 'p'
			AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'ICU'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'IU'
			AND js.Code = 'p'
			AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'IU'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'CD'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Mor"%'
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'CD'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Mor"%'
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'VD'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Mor"%'
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'VD'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"Mor"%'
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'XIP'
			AND js.Code = 'p'
			AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'XIP'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'CD'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"M2"%'
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'CD'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"M2"%'
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;



	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'VD'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"M2"%'
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'VD'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
		AND j.Options like '%"Format":"M2"%'
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'QPP'
			AND js.Code = 'p'
			AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'QPP'
			AND js.Code = 'I'
			AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;

	SELECT
        TBL1.JobID,
        TBL1.Options,
        TBL1.AccountID
    FROM
    (
        SELECT
            j.Options,
            j.AccountID,
            j.JobID,
            j.JobLoggedUserID,
            @row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
				@prev_JobLoggedUserID  := j.JobLoggedUserID,
				@prev_created_at  := created_at
        FROM tblJob j
        INNER JOIN tblJobType jt
            ON j.JobTypeID = jt.JobTypeID
        INNER JOIN tblJobStatus js
            ON j.JobStatusID = js.JobStatusID
         ,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
        WHERE jt.Code = 'BDS'
            AND js.Code = 'p'
            AND j.CompanyID = p_CompanyID
         ORDER BY j.JobLoggedUserID,j.created_at ASC
    ) TBL1
    LEFT JOIN
    (
        SELECT
            JobLoggedUserID
        FROM tblJob j
        INNER JOIN tblJobType jt
            ON j.JobTypeID = jt.JobTypeID
        INNER JOIN tblJobStatus js
            ON j.JobStatusID = js.JobStatusID
        WHERE jt.Code = 'BDS'
            AND js.Code = 'I'
            AND j.CompanyID = p_CompanyID
    ) TBL2
        ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
    WHERE TBL1.rowno = 1
    AND TBL2.JobLoggedUserID IS NULL;




    SELECT
        TBL1.JobID,
        TBL1.Options,
        TBL1.AccountID
    FROM
    (
        SELECT
            j.Options,
            j.AccountID,
            j.JobID,
            j.JobLoggedUserID,
            @row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
				@prev_JobLoggedUserID  := j.JobLoggedUserID,
				@prev_created_at  := created_at
        FROM tblJob j
        INNER JOIN tblJobType jt
            ON j.JobTypeID = jt.JobTypeID
        INNER JOIN tblJobStatus js
            ON j.JobStatusID = js.JobStatusID
         ,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
        WHERE jt.Code = 'DR'
            AND js.Code = 'p'
            AND j.CompanyID = p_CompanyID
         ORDER BY j.JobLoggedUserID,j.created_at ASC
    ) TBL1
    LEFT JOIN
    (
        SELECT
            JobLoggedUserID
        FROM tblJob j
        INNER JOIN tblJobType jt
            ON j.JobTypeID = jt.JobTypeID
        INNER JOIN tblJobStatus js
            ON j.JobStatusID = js.JobStatusID
        WHERE jt.Code = 'DR'
            AND js.Code = 'I'
            AND j.CompanyID = p_CompanyID
    ) TBL2
        ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
    WHERE TBL1.rowno = 1
    AND TBL2.JobLoggedUserID IS NULL;

      SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'BCS'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.JobLoggedUserID,j.created_at ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			JobLoggedUserID
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'BCS'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL1.JobLoggedUserID = TBL2.JobLoggedUserID
	WHERE TBL1.rowno = 1
	AND TBL2.JobLoggedUserID IS NULL;


	-- Termination Rate Operation
	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'TRO'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.created_at,j.JobLoggedUserID ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			count(*) as COUNTER
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'TRO'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL2.COUNTER = 0
	WHERE TBL1.rowno = 1
	AND TBL2.COUNTER = 0
	limit 1;



	-- Termination Rate Margin
	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'TRM'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.created_at,j.JobLoggedUserID ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			count(*) as COUNTER
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'TRM'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL2.COUNTER = 0
	WHERE TBL1.rowno = 1
	AND TBL2.COUNTER = 0
	limit 1;



	-- Grid Export
	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'GE'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.created_at,j.JobLoggedUserID ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			count(*) as COUNTER
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'GE'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL2.COUNTER = 0
	WHERE TBL1.rowno = 1
	AND TBL2.COUNTER = 0
	limit 1;

		-- Account Import
	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'AI'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.created_at,j.JobLoggedUserID ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			count(*) as COUNTER
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'AI'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL2.COUNTER = 0
	WHERE TBL1.rowno = 1
	AND TBL2.COUNTER = 0
	limit 1;


		-- Service Import
	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'SI'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.created_at,j.JobLoggedUserID ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			count(*) as COUNTER
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'SI'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL2.COUNTER = 0
	WHERE TBL1.rowno = 1
	AND TBL2.COUNTER = 0
	limit 1;


	-- Data Import From Template File
	SELECT
		TBL1.JobID,
		TBL1.Options,
		TBL1.AccountID
	FROM
	(
		SELECT
			j.Options,
			j.AccountID,
			j.JobID,
			j.JobLoggedUserID,
			@row_num := IF(@prev_JobLoggedUserID=j.JobLoggedUserID and @prev_created_at <= j.created_at ,@row_num+1,1) AS rowno,
			@prev_JobLoggedUserID  := j.JobLoggedUserID,
			@prev_created_at  := created_at
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		,(SELECT @row_num := 1) x,(SELECT @prev_JobLoggedUserID := '') y,(SELECT @prev_created_at := '') z
		WHERE jt.Code = 'DIFT'
        AND js.Code = 'P'
		AND j.CompanyID = p_CompanyID
		ORDER BY j.created_at,j.JobLoggedUserID ASC
	) TBL1
	LEFT JOIN
	(
		SELECT
			count(*) as COUNTER
		FROM tblJob j
		INNER JOIN tblJobType jt
			ON j.JobTypeID = jt.JobTypeID
		INNER JOIN tblJobStatus js
			ON j.JobStatusID = js.JobStatusID
		WHERE jt.Code = 'DIFT'
        AND js.Code = 'I'
		AND j.CompanyID = p_CompanyID
	) TBL2
		ON TBL2.COUNTER = 0
	WHERE TBL1.rowno = 1
	AND TBL2.COUNTER = 0
	limit 1;



	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;