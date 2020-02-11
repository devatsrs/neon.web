use `speakintelligentRM`;

INSERT INTO `tblCronJobCommand` (`CompanyID`, `GatewayID`, `Title`, `Command`, `Settings`, `Status`, `created_at`, `created_by`) VALUES (1, NULL, 'Exact Invoice Export', 'exactinvoiceexport', '[[{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"}]]', 1, '2019-12-03 03:35:53', 'System');
INSERT INTO `tblCronJobCommand` (`CompanyID`, `GatewayID`, `Title`, `Command`, `Settings`, `Status`, `created_at`, `created_by`) VALUES (1, NULL, 'Exact Payment Import', 'exactpaymentimport', '[[{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"}]]', 1, '2019-12-26 03:35:53', 'System');



ALTER TABLE `tblTaxRate`
	ADD COLUMN `VATCode` VARCHAR(50) NOT NULL DEFAULT '' AFTER `Status`;

ALTER TABLE `eng_tblTaxRate`
	ADD COLUMN `VATCode` VARCHAR(50) NOT NULL DEFAULT '' AFTER `Status`;




DROP TRIGGER IF EXISTS `trig_tblTaxRate_delete`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trig_tblTaxRate_delete` BEFORE DELETE ON `tblTaxRate` FOR EACH ROW BEGIN

SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DELETE FROM eng_tblTaxRate WHERE TaxRateId=OLD.TaxRateId;

	INSERT INTO eng_tblTaxRate(
		TaxRateId,
		CompanyId,
		Title,
		Amount,
		TaxType,
		FlatStatus,
		Country,
		DutchProvider,
		DutchFoundation,
		Status,
		VATCode,
		created_at,
		created_by,
		updated_at,
		updated_by,
		Action
	)
SELECT
		TaxRateId,
		CompanyId,
		Title,
		Amount,
		TaxType,
		FlatStatus,
		Country,
		DutchProvider,
		DutchFoundation,
		Status,
		VATCode,
		created_at,
		created_by,
		updated_at,
		updated_by,
		'D' AS Action
FROM tblTaxRate
WHERE TaxRateId=OLD.TaxRateId;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;




DROP TRIGGER IF EXISTS `trig_tblTaxRate_insert`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trig_tblTaxRate_insert` AFTER INSERT ON `tblTaxRate` FOR EACH ROW BEGIN

SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DELETE FROM eng_tblTaxRate WHERE TaxRateId=NEW.TaxRateId;

	INSERT INTO eng_tblTaxRate(
		TaxRateId,
		CompanyId,
		Title,
		Amount,
		TaxType,
		FlatStatus,
		Country,
		DutchProvider,
		DutchFoundation,
		Status,
		VATCode,
		created_at,
		created_by,
		updated_at,
		updated_by,
		Action
	)
SELECT
		TaxRateId,
		CompanyId,
		Title,
		Amount,
		TaxType,
		FlatStatus,
		Country,
		DutchProvider,
		DutchFoundation,
		Status,
		VATCode,
		created_at,
		created_by,
		updated_at,
		updated_by,
		'I' AS Action
FROM tblTaxRate
WHERE TaxRateId=NEW.TaxRateId;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;




DROP TRIGGER IF EXISTS `trig_tblTaxRate_update`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trig_tblTaxRate_update` AFTER UPDATE ON `tblTaxRate` FOR EACH ROW BEGIN

SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DELETE FROM eng_tblTaxRate WHERE TaxRateId=OLD.TaxRateId;

	INSERT INTO eng_tblTaxRate(
		TaxRateId,
		CompanyId,
		Title,
		Amount,
		TaxType,
		FlatStatus,
		Country,
		DutchProvider,
		DutchFoundation,
		Status,
		VATCode,
		created_at,
		created_by,
		updated_at,
		updated_by,
		Action
	)
SELECT
		TaxRateId,
		CompanyId,
		Title,
		Amount,
		TaxType,
		FlatStatus,
		Country,
		DutchProvider,
		DutchFoundation,
		Status,
		VATCode,
		created_at,
		created_by,
		updated_at,
		updated_by,
		'U' AS Action
FROM tblTaxRate
WHERE TaxRateId=OLD.TaxRateId;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;




DROP PROCEDURE IF EXISTS `prc_APIRoutingData`;
DELIMITER //
CREATE PROCEDURE `prc_APIRoutingData`()
BEGIN

SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	/* Inbound DID Rate Table */

	TRUNCATE TABLE speakintelligentRoutingEngine.tblRateTableDIDRate;

	INSERT INTO speakintelligentRoutingEngine.tblRateTableDIDRate(
			RateTableDIDRateID,
			OriginationRateID,
			RateID,
			RateTableId,
			TimezonesID,
			EffectiveDate,
			EndDate,
			City ,
			Tariff ,
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
			created_at,
			updated_at,
			CreatedBy,
			ModifiedBy,
			ApprovedStatus,
			ApprovedBy,
			ApprovedDate,
			VendorID,
			OriginationCode,
			DestinationCode
		)
	SELECT
		rtr.RateTableDIDRateID,
		rtr.OriginationRateID,
		rtr.RateID,
		rtr.RateTableId,
		rtr.TimezonesID,
		rtr.EffectiveDate,
		rtr.EndDate,
		rtr.City ,
		rtr.Tariff ,
		rtr.AccessType,
		rtr.OneOffCost,
		rtr.MonthlyCost,
		rtr.CostPerCall,
		rtr.CostPerMinute,
		rtr.SurchargePerCall,
		rtr.SurchargePerMinute,
		rtr.OutpaymentPerCall,
		rtr.OutpaymentPerMinute,
		rtr.Surcharges,
		rtr.Chargeback,
		rtr.CollectionCostAmount,
		rtr.CollectionCostPercentage,
		rtr.RegistrationCostPerNumber,
		rtr.OneOffCostCurrency,
		rtr.MonthlyCostCurrency,
		rtr.CostPerCallCurrency,
		rtr.CostPerMinuteCurrency,
		rtr.SurchargePerCallCurrency,
		rtr.SurchargePerMinuteCurrency,
		rtr.OutpaymentPerCallCurrency,
		rtr.OutpaymentPerMinuteCurrency,
		rtr.SurchargesCurrency,
		rtr.ChargebackCurrency,
		rtr.CollectionCostAmountCurrency,
		rtr.RegistrationCostPerNumberCurrency,
		rtr.created_at,
		rtr.updated_at,
		rtr.CreatedBy,
		rtr.ModifiedBy,
		rtr.ApprovedStatus,
		rtr.ApprovedBy,
		rtr.ApprovedDate,
		rtr.VendorID,
		origRate.Code AS OriginationCode,
		destRate.Code  AS DestinationCode
	FROM tblRateTableDIDRate  rtr
		INNER JOIN tblRate destRate  ON  rtr.RateID = destRate.RateId
		LEFT JOIN tblRate origRate ON rtr.OriginationRateID = origRate.RateID
	;

	/* Inbound DID Rate Table End */

	/* Package Rate Table */

	TRUNCATE TABLE speakintelligentRoutingEngine.tblRateTablePKGRate;

	INSERT INTO speakintelligentRoutingEngine.tblRateTablePKGRate(
			RateTablePKGRateID,
			RateID,
			RateTableId,
			TimezonesID,
			EffectiveDate,
			EndDate,
			OneOffCost,
			MonthlyCost,
			PackageCostPerMinute,
			RecordingCostPerMinute,
			OneOffCostCurrency,
			MonthlyCostCurrency,
			PackageCostPerMinuteCurrency,
			RecordingCostPerMinuteCurrency,
			created_at,
			updated_at,
			CreatedBy,
			ModifiedBy,
			ApprovedStatus,
			ApprovedBy,
			ApprovedDate,
			VendorID,
			Code
		)
	SELECT
		rtr.RateTablePKGRateID,
		rtr.RateID,
		rtr.RateTableId,
		rtr.TimezonesID,
		rtr.EffectiveDate,
		rtr.EndDate,
		rtr.OneOffCost,
		rtr.MonthlyCost,
		rtr.PackageCostPerMinute,
		rtr.RecordingCostPerMinute,
		rtr.OneOffCostCurrency,
		rtr.MonthlyCostCurrency,
		rtr.PackageCostPerMinuteCurrency,
		rtr.RecordingCostPerMinuteCurrency,
		rtr.created_at,
		rtr.updated_at,
		rtr.CreatedBy,
		rtr.ModifiedBy,
		rtr.ApprovedStatus,
		rtr.ApprovedBy,
		rtr.ApprovedDate,
		rtr.VendorID,
		destRate.Code AS Code
	FROM tblRateTablePKGRate  rtr
		INNER JOIN tblRate destRate  ON  rtr.RateID = destRate.RateId
	;

	/* Package Rate Table End */

	/* Vendor Rate */

	TRUNCATE TABLE speakintelligentRoutingEngine.tblRateTableRate;

	INSERT INTO speakintelligentRoutingEngine.tblRateTableRate(
		RateTableRateID,
		OriginationRateID,
		RateID,
		RateTableId,
		TimezonesID,
		Rate,
		RateN,
		EffectiveDate,
		EndDate,
		created_at,
		updated_at,
		CreatedBy,
		ModifiedBy,
		PreviousRate,
		Interval1,
		IntervalN,
		MinimumDuration,
		ConnectionFee,
		RateCurrency,
		ConnectionFeeCurrency,
		VendorID,
		RoutingCategoryID,
		Preference,
		Blocked,
		ApprovedStatus,
		ApprovedBy,
		ApprovedDate,
		OriginationCode,
		DestinationCode
	)
	SELECT
		RateTableRateID,
		OriginationRateID,
		rtr.RateID,
		rtr.RateTableId,
		TimezonesID,
		rtr.Rate,
		RateN,
		EffectiveDate,
		EndDate,
		rtr.created_at,
		rtr.updated_at,
		rtr.CreatedBy,
		rtr.ModifiedBy,
		rtr.PreviousRate,
		rtr.Interval1,
		rtr.IntervalN,
		rtr.MinimumDuration,
		rtr.ConnectionFee,
		rtr.RateCurrency,
		rtr.ConnectionFeeCurrency,
		rtr.VendorID,
		rtr.RoutingCategoryID,
		rtr.Preference,
		rtr.Blocked,
		rtr.ApprovedStatus,
		ApprovedBy,
		ApprovedDate,
		origRate.Code AS OriginationCode,
		destRate.Code AS DestinationCode
	FROM tblRateTableRate  rtr
		INNER JOIN tblRate destRate  ON  rtr.RateID = destRate.RateId
		INNER JOIN tblRateTable rt ON rtr.RateTableID = rt.RateTableId
		LEFT JOIN tblRate origRate ON rtr.OriginationRateID = origRate.RateID
	WHERE rt.`Type` = 1 AND rt.AppliedTo = 2;

	/*Vendor Rate end */

	/*Customer outbound Rate */

	TRUNCATE TABLE speakintelligentRoutingEngine.tblCustomerRateTableRate;

	INSERT INTO speakintelligentRoutingEngine.tblCustomerRateTableRate(
		RateTableRateID,
		OriginationRateID,
		RateID,
		RateTableId,
		TimezonesID,
		Rate,
		RateN,
		EffectiveDate,
		EndDate,
		created_at,
		updated_at,
		CreatedBy,
		ModifiedBy,
		PreviousRate,
		Interval1,
		IntervalN,
		MinimumDuration,
		ConnectionFee,
		RateCurrency,
		ConnectionFeeCurrency,
		VendorID,
		RoutingCategoryID,
		Preference,
		Blocked,
		ApprovedStatus,
		ApprovedBy,
		ApprovedDate,
		OriginationCode,
		DestinationCode,
		CountryID
	)
	SELECT
		RateTableRateID,
		OriginationRateID,
		rtr.RateID,
		rtr.RateTableId,
		TimezonesID,
		rtr.Rate,
		RateN,
		EffectiveDate,
		EndDate,
		rtr.created_at,
		rtr.updated_at,
		rtr.CreatedBy,
		rtr.ModifiedBy,
		rtr.PreviousRate,
		rtr.Interval1,
		rtr.IntervalN,
		rtr.MinimumDuration,
		rtr.ConnectionFee,
		rtr.RateCurrency,
		rtr.ConnectionFeeCurrency,
		rtr.VendorID,
		rtr.RoutingCategoryID,
		rtr.Preference,
		rtr.Blocked,
		rtr.ApprovedStatus,
		ApprovedBy,
		ApprovedDate,
		origRate.Code AS OriginationCode,
		destRate.Code AS DestinationCode,
		IFNULL(destRate.CountryID,0) AS CountryID
	FROM tblRateTableRate  rtr
		INNER JOIN tblRate destRate  ON  rtr.RateID = destRate.RateId
		INNER JOIN tblRateTable rt ON rtr.RateTableID = rt.RateTableId
		LEFT JOIN tblRate origRate ON rtr.OriginationRateID = origRate.RateID
	WHERE rt.`Type` = 1 AND rt.AppliedTo <> 2;

	/* Customer Rate Table end */

	/* CLI Rate Table */

	TRUNCATE TABLE speakintelligentRoutingEngine.tblCLIRateTable;
	INSERT INTO speakintelligentRoutingEngine.tblCLIRateTable(CLIRateTableID,
		AccountServicePackageID,
		CompanyID,
		AccountID,
		CLI,
		AccessDiscountPlanID,
		RateTableID,
		TerminationRateTableID,
		TerminationDiscountPlanID,
		CountryID,
		NumberStartDate,
		NumberEndDate,
		ServiceID,
		AccountServiceID,
		PackageID,
		PackageRateTableID,
		Status,
		`Prefix`,
		PrefixWithoutCountry,
		ContractID,
		City,
		Tariff,
		DIDCategoryID,
		VendorID,
		NoType,
		SpecialRateTableID,
		SpecialTerminationRateTableID
	)
	SELECT
		CLIRateTableID,
		AccountServicePackageID,
		CompanyID,
		AccountID,
		CLI,
		AccessDiscountPlanID,
		RateTableID,
		TerminationRateTableID,
		TerminationDiscountPlanID,
		CountryID,
		NumberStartDate,
		NumberEndDate,
		ServiceID,
		AccountServiceID,
		PackageID,
		PackageRateTableID,
		Status,
		Prefix,
		PrefixWithoutCountry,
		ContractID,
		City,
		Tariff,
		DIDCategoryID,
		VendorID,
		NoType,
		SpecialRateTableID,
		SpecialTerminationRateTableID
	 FROM tblCLIRateTable;

	/* CLI Rate Table end */

	/* Account Service Package */

	TRUNCATE TABLE speakintelligentRoutingEngine.tblAccountServicePackage;

	INSERT INTO speakintelligentRoutingEngine.tblAccountServicePackage(
			AccountServicePackageID,
			AccountID,
			AccountServiceID,
			CompanyID,
			PackageId,
			RateTableID,
			created_at,
			updated_at,
			created_by,
			updated_by,
			PackageDiscountPlanID,
			PackageStartDate,
			PackageEndDate,
			ContractID,
			Status,
			ServiceID,
			SpecialPackageRateTableID,
			VendorID,
			Name
			)
		SELECT
			rtr.AccountServicePackageID,
			rtr.AccountID,
			rtr.AccountServiceID,
			rtr.CompanyID,
			rtr.PackageId,
			rtr.RateTableID,
			rtr.created_at,
			rtr.updated_at,
			rtr.created_by,
			rtr.updated_by,
			rtr.PackageDiscountPlanID,
			rtr.PackageStartDate,
			rtr.PackageEndDate,
			rtr.ContractID,
			rtr.Status,
			rtr.ServiceID,
			rtr.SpecialPackageRateTableID,
			rtr.VendorID,
			p.Name AS Name
		FROM tblAccountServicePackage  rtr
			INNER JOIN tblPackage p  ON  rtr.PackageId = p.PackageId
		;

	/* Account Service Package end */

	/* Tax Rate */

	TRUNCATE TABLE speakintelligentRoutingEngine.tblTaxRate;

	INSERT INTO speakintelligentRoutingEngine.tblTaxRate(
		TaxRateId,
		CompanyId,
		Title,
		Amount,
		TaxType,
		FlatStatus,
		Country,
		DutchProvider,
		DutchFoundation,
		Status,
		VATCode,
		created_at,
		created_by,
		updated_at,
		updated_by
	)
	SELECT
		TaxRateId,
		CompanyId,
		Title,
		Amount,
		TaxType,
		FlatStatus,
		Country,
		DutchProvider,
		DutchFoundation,
		Status,
		VATCode,
		created_at,
		created_by,
		updated_at,
		updated_by
	FROM tblTaxRate
		;

	/* Tax Rate end */


SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_APIRoutingDataPerRow`;
DELIMITER //
CREATE PROCEDURE `prc_APIRoutingDataPerRow`()
BEGIN

DECLARE V_DID_Count INT;
DECLARE V_PKG_Count INT;
DECLARE V_VendorRate_Count INT;
DECLARE V_CustomerRate_Count INT;
DECLARE V_CLIRate_Count INT;
DECLARE V_AccountSerPKG_Count INT;
DECLARE V_TaxRate_Count INT;

SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	/* Inbound DID Rate Table */


	SELECT COUNT(*) INTO V_DID_Count FROM eng_tblRateTableDIDRate;

	IF (V_DID_Count > 0 )
	THEN

	 DROP TABLE IF EXISTS `speakintelligentRoutingEngine`.`temp_tblRateTableDIDRate`;

	 CREATE TABLE `speakintelligentRoutingEngine`.`temp_tblRateTableDIDRate` LIKE `speakintelligentRoutingEngine`.`tblRateTableDIDRate`;
	 INSERT `speakintelligentRoutingEngine`.`temp_tblRateTableDIDRate` SELECT * FROM `speakintelligentRoutingEngine`.tblRateTableDIDRate;

     DELETE rtd FROM speakintelligentRoutingEngine.temp_tblRateTableDIDRate rtd INNER JOIN eng_tblRateTableDIDRate e ON e.RateTableDIDRateID = rtd.RateTableDIDRateID WHERE e.Action = 'D' OR e.Action = 'U';

		INSERT INTO speakintelligentRoutingEngine.temp_tblRateTableDIDRate(
		RateTableDIDRateID,
		OriginationRateID,
		RateID,
		RateTableId,
		TimezonesID,
		EffectiveDate,
		EndDate,
		City ,
		Tariff ,
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
		created_at,
		updated_at,
		CreatedBy,
		ModifiedBy,
		ApprovedStatus,
		ApprovedBy,
		ApprovedDate,
		VendorID,
		OriginationCode,
		DestinationCode
	)
	SELECT
		RateTableDIDRateID,
		OriginationRateID,
		RateID,
		RateTableId,
		TimezonesID,
		EffectiveDate,
		EndDate,
		City ,
		Tariff ,
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
		created_at,
		updated_at,
		CreatedBy,
		ModifiedBy,
		ApprovedStatus,
		ApprovedBy,
		ApprovedDate,
		VendorID,
		OriginationCode,
		DestinationCode
	FROM eng_tblRateTableDIDRate
	WHERE Action = 'I' OR Action = 'U';


	DROP TABLE IF EXISTS `speakintelligentRoutingEngine.old_tblRateTableDIDRate`;
	RENAME TABLE speakintelligentRoutingEngine.tblRateTableDIDRate TO speakintelligentRoutingEngine.old_tblRateTableDIDRate;
	RENAME TABLE speakintelligentRoutingEngine.temp_tblRateTableDIDRate TO speakintelligentRoutingEngine.tblRateTableDIDRate;

	DROP TABLE speakintelligentRoutingEngine.old_tblRateTableDIDRate;

   -- DELETE eg eng_tblRateTableDIDRate eg INNER JOIN speakintelligentRoutingEngine.tblRateTableDIDRate rtd ON rtd.RateTableDIDRateID = eg.RateTableDIDRateID;
   -- DELETE FROM eng_tblRateTableDIDRate;
	TRUNCATE TABLE eng_tblRateTableDIDRate;

 END IF;
    /* Inbound DID Rate Table end */

    /* Package Rate Table */

    SELECT COUNT(*) INTO V_PKG_Count FROM eng_tblRateTablePKGRate;

	IF (V_PKG_Count > 0 )
	THEN

  	 DROP TABLE IF EXISTS `speakintelligentRoutingEngine`.`temp_tblRateTablePKGRate`;

  	 CREATE TABLE `speakintelligentRoutingEngine`.`temp_tblRateTablePKGRate` LIKE `speakintelligentRoutingEngine`.`tblRateTablePKGRate`;
	 INSERT `speakintelligentRoutingEngine`.`temp_tblRateTablePKGRate` SELECT * FROM `speakintelligentRoutingEngine`.tblRateTablePKGRate;

     DELETE rtd FROM speakintelligentRoutingEngine.temp_tblRateTablePKGRate rtd INNER JOIN eng_tblRateTablePKGRate e ON e.RateTablePKGRateID = rtd.RateTablePKGRateID WHERE e.Action = 'D' OR e.Action = 'U';

		INSERT INTO speakintelligentRoutingEngine.temp_tblRateTablePKGRate(
		RateTablePKGRateID,
		RateID,
		RateTableId,
		TimezonesID,
		EffectiveDate,
		EndDate,
		OneOffCost,
		MonthlyCost,
		PackageCostPerMinute,
		RecordingCostPerMinute,
		OneOffCostCurrency,
		MonthlyCostCurrency,
		PackageCostPerMinuteCurrency,
		RecordingCostPerMinuteCurrency,
		created_at,
		updated_at,
		CreatedBy,
		ModifiedBy,
		ApprovedStatus,
		ApprovedBy,
		ApprovedDate,
		VendorID,
		Code
	)
	SELECT
		RateTablePKGRateID,
		RateID,
		RateTableId,
		TimezonesID,
		EffectiveDate,
		EndDate,
		OneOffCost,
		MonthlyCost,
		PackageCostPerMinute,
		RecordingCostPerMinute,
		OneOffCostCurrency,
		MonthlyCostCurrency,
		PackageCostPerMinuteCurrency,
		RecordingCostPerMinuteCurrency,
		created_at,
		updated_at,
		CreatedBy,
		ModifiedBy,
		ApprovedStatus,
		ApprovedBy,
		ApprovedDate,
		VendorID,
		Code
	FROM eng_tblRateTablePKGRate
	WHERE Action = 'I' OR Action = 'U';


	DROP TABLE IF EXISTS `speakintelligentRoutingEngine.old_tblRateTablePKGRate`;
	RENAME TABLE speakintelligentRoutingEngine.tblRateTablePKGRate TO speakintelligentRoutingEngine.old_tblRateTablePKGRate;
	RENAME TABLE speakintelligentRoutingEngine.temp_tblRateTablePKGRate TO speakintelligentRoutingEngine.tblRateTablePKGRate;

	DROP TABLE speakintelligentRoutingEngine.old_tblRateTablePKGRate;

	TRUNCATE TABLE eng_tblRateTablePKGRate;

 END IF;

    /* Package Rate Table End */

    /* Vendor Rate Table */

    SELECT COUNT(*) INTO V_VendorRate_Count FROM eng_tblTempRateTableRate;

    IF (V_VendorRate_Count > 0 )
	THEN

	 DROP TABLE IF EXISTS `speakintelligentRoutingEngine`.`temp_tblRateTableRate`;

    CREATE TABLE `speakintelligentRoutingEngine`.`temp_tblRateTableRate` AS SELECT * FROM `speakintelligentRoutingEngine`.tblRateTableRate;

     DELETE rtd FROM speakintelligentRoutingEngine.temp_tblRateTableRate rtd INNER JOIN eng_tblTempRateTableRate e ON e.RateTableRateID = rtd.RateTableRateID WHERE e.Action = 'D' OR e.Action = 'U';

		INSERT INTO speakintelligentRoutingEngine.temp_tblRateTableRate(
		RateTableRateID,
		OriginationRateID,
		RateID,
		RateTableId,
		TimezonesID,
		Rate,
		RateN,
		EffectiveDate,
		EndDate,
		created_at,
		updated_at,
		CreatedBy,
		ModifiedBy,
		PreviousRate,
		Interval1,
		IntervalN,
		MinimumDuration,
		ConnectionFee,
		RateCurrency,
		ConnectionFeeCurrency,
		VendorID,
		RoutingCategoryID,
		Preference,
		Blocked,
		ApprovedStatus,
		ApprovedBy,
		ApprovedDate,
		OriginationCode,
		DestinationCode
	)
	SELECT
		RateTableRateID,
		OriginationRateID,
		RateID,
		RateTableId,
		TimezonesID,
		Rate,
		RateN,
		EffectiveDate,
		EndDate,
		created_at,
		updated_at,
		CreatedBy,
		ModifiedBy,
		PreviousRate,
		Interval1,
		IntervalN,
		MinimumDuration,
		ConnectionFee,
		RateCurrency,
		ConnectionFeeCurrency,
		VendorID,
		RoutingCategoryID,
		Preference,
		Blocked,
		ApprovedStatus,
		ApprovedBy,
		ApprovedDate,
		OriginationCode,
		DestinationCode
	FROM eng_tblTempRateTableRate
	WHERE Action = 'I' OR Action = 'U';


	DROP TABLE IF EXISTS `speakintelligentRoutingEngine.old_tblRateTableRate`;
	RENAME TABLE speakintelligentRoutingEngine.tblRateTableRate TO speakintelligentRoutingEngine.old_tblRateTableRate;
	RENAME TABLE speakintelligentRoutingEngine.temp_tblRateTableRate TO speakintelligentRoutingEngine.tblRateTableRate;

	DROP TABLE speakintelligentRoutingEngine.old_tblRateTableRate;

	TRUNCATE TABLE eng_tblTempRateTableRate;

   END IF;

    /* Vendor Rate Table end */

    /* Customer Rate Table */

    SELECT COUNT(*) INTO V_CustomerRate_Count FROM eng_tblTempCustomerRateTableRate;
    IF (V_CustomerRate_Count > 0 )
	 THEN

	 DROP TABLE IF EXISTS `speakintelligentRoutingEngine`.`temp_tblCustomerRateTableRate`;

	 CREATE TABLE `speakintelligentRoutingEngine`.`temp_tblCustomerRateTableRate` LIKE `speakintelligentRoutingEngine`.`tblCustomerRateTableRate`;
	 INSERT `speakintelligentRoutingEngine`.`temp_tblCustomerRateTableRate` SELECT * FROM `speakintelligentRoutingEngine`.tblCustomerRateTableRate;

     DELETE rtd FROM speakintelligentRoutingEngine.temp_tblCustomerRateTableRate rtd INNER JOIN eng_tblTempCustomerRateTableRate e ON e.RateTableRateID = rtd.RateTableRateID WHERE e.Action = 'D' OR e.Action = 'U';

		INSERT INTO speakintelligentRoutingEngine.temp_tblCustomerRateTableRate(
		RateTableRateID,
		OriginationRateID,
		RateID,
		RateTableId,
		TimezonesID,
		Rate,
		RateN,
		EffectiveDate,
		EndDate,
		created_at,
		updated_at,
		CreatedBy,
		ModifiedBy,
		PreviousRate,
		Interval1,
		IntervalN,
		MinimumDuration,
		ConnectionFee,
		RateCurrency,
		ConnectionFeeCurrency,
		VendorID,
		RoutingCategoryID,
		Preference,
		Blocked,
		ApprovedStatus,
		ApprovedBy,
		ApprovedDate,
		OriginationCode,
		DestinationCode,
		CountryID
	)
	SELECT
		RateTableRateID,
		OriginationRateID,
		RateID,
		RateTableId,
		TimezonesID,
		Rate,
		RateN,
		EffectiveDate,
		EndDate,
		created_at,
		updated_at,
		CreatedBy,
		ModifiedBy,
		PreviousRate,
		Interval1,
		IntervalN,
		MinimumDuration,
		ConnectionFee,
		RateCurrency,
		ConnectionFeeCurrency,
		VendorID,
		RoutingCategoryID,
		Preference,
		Blocked,
		ApprovedStatus,
		ApprovedBy,
		ApprovedDate,
		OriginationCode,
		DestinationCode,
		CountryID
	FROM eng_tblTempCustomerRateTableRate
	WHERE Action = 'I' OR Action = 'U';


	DROP TABLE IF EXISTS `speakintelligentRoutingEngine.old_tblCustomerRateTableRate`;
	RENAME TABLE speakintelligentRoutingEngine.tblCustomerRateTableRate TO speakintelligentRoutingEngine.old_tblCustomerRateTableRate;
	RENAME TABLE speakintelligentRoutingEngine.temp_tblCustomerRateTableRate TO speakintelligentRoutingEngine.tblCustomerRateTableRate;

	DROP TABLE speakintelligentRoutingEngine.old_tblCustomerRateTableRate;

	TRUNCATE TABLE eng_tblTempCustomerRateTableRate;

   END IF;

    /* Customer Rate Table end */

    /* CLI Rate Table */

    SELECT COUNT(*) INTO V_CLIRate_Count FROM eng_tblTempCLIRateTable;
    IF (V_CLIRate_Count > 0 )
	THEN

    DROP TABLE IF EXISTS `speakintelligentRoutingEngine`.`temp_tblCLIRateTable`;

		CREATE TABLE `speakintelligentRoutingEngine`.`temp_tblCLIRateTable` AS SELECT * FROM `speakintelligentRoutingEngine`.tblCLIRateTable;

		DELETE rtd FROM speakintelligentRoutingEngine.temp_tblCLIRateTable rtd INNER JOIN eng_tblTempCLIRateTable e ON e.CLIRateTableID = rtd.CLIRateTableID WHERE e.Action = 'D' OR e.Action = 'U';

		INSERT INTO speakintelligentRoutingEngine.temp_tblCLIRateTable(
			CLIRateTableID,
			AccountServicePackageID,
			CompanyID,
			AccountID,
			CLI,
			AccessDiscountPlanID,
			RateTableID,
			TerminationRateTableID,
			TerminationDiscountPlanID,
			CountryID,
			NumberStartDate,
			NumberEndDate,
			ServiceID,
			AccountServiceID,
			PackageID,
			PackageRateTableID,
			Status,
			`Prefix`,
			PrefixWithoutCountry,
			ContractID,
			City,
			Tariff,
			DIDCategoryID,
			VendorID,
			NoType,
			SpecialRateTableID,
			SpecialTerminationRateTableID
		)
		SELECT
			CLIRateTableID,
			AccountServicePackageID,
			CompanyID,
			AccountID,
			CLI,
			AccessDiscountPlanID,
			RateTableID,
			TerminationRateTableID,
			TerminationDiscountPlanID,
			CountryID,
			NumberStartDate,
			NumberEndDate,
			ServiceID,
			AccountServiceID,
			PackageID,
			PackageRateTableID,
			Status,
			`Prefix`,
			PrefixWithoutCountry,
			ContractID,
			City,
			Tariff,
			DIDCategoryID,
			VendorID,
			NoType,
			SpecialRateTableID,
			SpecialTerminationRateTableID
		FROM eng_tblTempCLIRateTable
		WHERE Action = 'I' OR Action = 'U';


		DROP TABLE IF EXISTS `speakintelligentRoutingEngine.old_tblCLIRateTable`;
		RENAME TABLE speakintelligentRoutingEngine.tblCLIRateTable TO speakintelligentRoutingEngine.old_tblCLIRateTable;
		RENAME TABLE speakintelligentRoutingEngine.temp_tblCLIRateTable TO speakintelligentRoutingEngine.tblCLIRateTable;

		DROP TABLE speakintelligentRoutingEngine.old_tblCLIRateTable;

		TRUNCATE TABLE eng_tblTempCLIRateTable;

    END IF;
    /* CLI Rate Table End */

    /* Account Service Package */


	SELECT COUNT(*) INTO V_AccountSerPKG_Count FROM eng_tblAccountServicePackage;

	IF (V_AccountSerPKG_Count > 0 )
	THEN

	 DROP TABLE IF EXISTS `speakintelligentRoutingEngine`.`temp_tblAccountServicePackage`;

    CREATE TABLE `speakintelligentRoutingEngine`.`temp_tblAccountServicePackage` AS SELECT * FROM `speakintelligentRoutingEngine`.tblAccountServicePackage;

     DELETE rtd FROM speakintelligentRoutingEngine.temp_tblAccountServicePackage rtd INNER JOIN eng_tblAccountServicePackage e ON e.AccountServicePackageID = rtd.AccountServicePackageID WHERE e.Action = 'D' OR e.Action = 'U';

		INSERT INTO speakintelligentRoutingEngine.temp_tblAccountServicePackage(
		AccountServicePackageID,
		AccountID,
		AccountServiceID,
		CompanyID,
		PackageId,
		RateTableID,
		created_at,
		updated_at,
		created_by,
		updated_by,
		PackageDiscountPlanID,
		PackageStartDate,
		PackageEndDate,
		ContractID,
		Status,
		ServiceID,
		SpecialPackageRateTableID,
		VendorID,
		Name
	)
	SELECT
		AccountServicePackageID,
		AccountID,
		AccountServiceID,
		CompanyID,
		PackageId,
		RateTableID,
		created_at,
		updated_at,
		created_by,
		updated_by,
		PackageDiscountPlanID,
		PackageStartDate,
		PackageEndDate,
		ContractID,
		Status,
		ServiceID,
		SpecialPackageRateTableID,
		VendorID,
		Name
	FROM eng_tblAccountServicePackage
	WHERE Action = 'I' OR Action = 'U';


	DROP TABLE IF EXISTS `speakintelligentRoutingEngine.old_tblAccountServicePackage`;
	RENAME TABLE speakintelligentRoutingEngine.tblAccountServicePackage TO speakintelligentRoutingEngine.old_tblAccountServicePackage;
	RENAME TABLE speakintelligentRoutingEngine.temp_tblAccountServicePackage TO speakintelligentRoutingEngine.tblAccountServicePackage;

	DROP TABLE speakintelligentRoutingEngine.old_tblAccountServicePackage;

	TRUNCATE TABLE eng_tblAccountServicePackage;

 END IF;
    /* Account Service Package end */

	/* Tax Rate */

	SELECT COUNT(*) INTO V_TaxRate_Count FROM eng_tblTaxRate;

	IF (V_TaxRate_Count > 0 )
	THEN

		DROP TABLE IF EXISTS `speakintelligentRoutingEngine`.`temp_tblTaxRate`;

		CREATE TABLE `speakintelligentRoutingEngine`.`temp_tblTaxRate` AS SELECT * FROM `speakintelligentRoutingEngine`.tblTaxRate;

		DELETE rtd FROM speakintelligentRoutingEngine.temp_tblTaxRate rtd INNER JOIN eng_tblTaxRate e ON e.TaxRateId = rtd.TaxRateId WHERE e.Action = 'D' OR e.Action = 'U';

		INSERT INTO speakintelligentRoutingEngine.temp_tblTaxRate(
		TaxRateId,
		CompanyId,
		Title,
		Amount,
		TaxType,
		FlatStatus,
		Country,
		DutchProvider,
		DutchFoundation,
		Status,
		VATCode,
		created_at,
		created_by,
		updated_at,
		updated_by
		)
		SELECT
		TaxRateId,
		CompanyId,
		Title,
		Amount,
		TaxType,
		FlatStatus,
		Country,
		DutchProvider,
		DutchFoundation,
		Status,
		VATCode,
		created_at,
		created_by,
		updated_at,
		updated_by
		FROM eng_tblTaxRate
		WHERE Action = 'I' OR Action = 'U';


		DROP TABLE IF EXISTS speakintelligentRoutingEngine.old_tblTaxRate;
		RENAME TABLE speakintelligentRoutingEngine.tblTaxRate TO speakintelligentRoutingEngine.old_tblTaxRate;
		RENAME TABLE speakintelligentRoutingEngine.temp_tblTaxRate TO speakintelligentRoutingEngine.tblTaxRate;

		DROP TABLE speakintelligentRoutingEngine.old_tblTaxRate;

		TRUNCATE TABLE eng_tblTaxRate;
	END IF;
    /* Tax Rate end */


   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;