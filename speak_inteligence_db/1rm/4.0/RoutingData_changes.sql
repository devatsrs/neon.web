use `speakintelligentRoutingEngine`; -- web server

CREATE TABLE IF NOT EXISTS `tblSyncRoutingDataLog` engine=CONNECT table_type=MYSQL
connection='mysql://cluster-user:ieQ8b3&CGjKT@web-db.neon.colo.local/speakintelligentRoutingEngine/tblSyncRoutingDataLog';


DROP PROCEDURE IF EXISTS `prc_deleteProcessedTriggerData`;
DELIMITER //
CREATE PROCEDURE `prc_deleteProcessedTriggerData`()
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET @chunk_limit = 100000;

	/************** Account Section Starts **************/

	-- get last id from log table which we has been processed, so we can delete all processed data from trigger table
	SELECT MAX(eng_tblTempAccountID) INTO @Last_eng_tblTempAccountID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTempAccountID = IFNULL(@Last_eng_tblTempAccountID,0);
	-- delete if there is any processed data in trigger table
	DELETE FROM speakintelligentRM.eng_tblTempAccount WHERE eng_tblTempAccountID <= @Last_eng_tblTempAccountID;

	/************** Account Section Ends **************/

	/************** RateTable Section Starts **************/

	-- get last id from log table which we has been processed, so we can delete all processed data from trigger table
	SELECT MAX(eng_tblTempRateTableID) INTO @Last_eng_tblTempRateTableID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTempRateTableID = IFNULL(@Last_eng_tblTempRateTableID,0);
	-- delete if there is any processed data in trigger table
	DELETE FROM speakintelligentRM.eng_tblTempRateTable WHERE eng_tblTempRateTableID <= @Last_eng_tblTempRateTableID;

	/************** RateTable Section Ends **************/

	/************** RoutingCategory Section Starts **************/

	-- get last id from log table which we has been processed, so we can delete all processed data from trigger table
	SELECT MAX(eng_tblTempRoutingCategoryID) INTO @Last_eng_tblTempRoutingCategoryID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTempRoutingCategoryID = IFNULL(@Last_eng_tblTempRoutingCategoryID,0);
	-- delete if there is any processed data in trigger table
	DELETE FROM speakintelligentRM.eng_tblTempRoutingCategory WHERE eng_tblTempRoutingCategoryID <= @Last_eng_tblTempRoutingCategoryID;

	/************** RoutingCategory Section Ends **************/

	/************** RoutingProfile Section Starts **************/

	-- get last id from log table which we has been processed, so we can delete all processed data from trigger table
	SELECT MAX(eng_tblTempRoutingProfileID) INTO @Last_eng_tblTempRoutingProfileID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTempRoutingProfileID = IFNULL(@Last_eng_tblTempRoutingProfileID,0);
	-- delete if there is any processed data in trigger table
	DELETE FROM speakintelligentRM.eng_tblTempRoutingProfile WHERE eng_tblTempRoutingProfileID <= @Last_eng_tblTempRoutingProfileID;

	/************** RoutingProfile Section Ends **************/

	/************** RoutingProfileCategory Section Starts **************/

	-- get last id from log table which we has been processed, so we can delete all processed data from trigger table
	SELECT MAX(eng_tblTempRoutingProfileCategoryID) INTO @Last_eng_tblTempRoutingProfileCategoryID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTempRoutingProfileCategoryID = IFNULL(@Last_eng_tblTempRoutingProfileCategoryID,0);
	-- delete if there is any processed data in trigger table
	DELETE FROM speakintelligentRM.eng_tblTempRoutingProfileCategory WHERE eng_tblTempRoutingProfileCategoryID <= @Last_eng_tblTempRoutingProfileCategoryID;

	/************** RoutingProfileCategory Section Ends **************/

	/************** RoutingProfileToCustomer Section Starts **************/

	-- get last id from log table which we has been processed, so we can delete all processed data from trigger table
	SELECT MAX(eng_tblTempRoutingProfileToCustomerID) INTO @Last_eng_tblTempRoutingProfileToCustomerID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTempRoutingProfileToCustomerID = IFNULL(@Last_eng_tblTempRoutingProfileToCustomerID,0);
	-- delete if there is any processed data in trigger table
	DELETE FROM speakintelligentRM.eng_tblTempRoutingProfileToCustomer WHERE eng_tblTempRoutingProfileToCustomerID <= @Last_eng_tblTempRoutingProfileToCustomerID;

	/************** RoutingProfileToCustomer Section Ends **************/

	/************** VendorConnection Section Starts **************/

	-- get last id from log table which we has been processed, so we can delete all processed data from trigger table
	SELECT MAX(eng_tblTempVendorConnectionID) INTO @Last_eng_tblTempVendorConnectionID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTempVendorConnectionID = IFNULL(@Last_eng_tblTempVendorConnectionID,0);
	-- delete if there is any processed data in trigger table
	DELETE FROM speakintelligentRM.eng_tblTempVendorConnection WHERE eng_tblTempVendorConnectionID <= @Last_eng_tblTempVendorConnectionID;

	/************** VendorConnection Section Ends **************/

	/************** Reseller Section Starts **************/

	-- get last id from log table which we has been processed, so we can delete all processed data from trigger table
	SELECT MAX(eng_tblTempResellerID) INTO @Last_eng_tblTempResellerID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTempResellerID = IFNULL(@Last_eng_tblTempResellerID,0);
	-- delete if there is any processed data in trigger table
	DELETE FROM speakintelligentRM.eng_tblTempReseller WHERE eng_tblTempResellerID <= @Last_eng_tblTempResellerID;

	/************** Reseller Section Ends **************/

	/************** Currency Section Starts **************/

	-- get last id from log table which we has been processed, so we can delete all processed data from trigger table
	SELECT MAX(eng_tblTempCurrencyID) INTO @Last_eng_tblTempCurrencyID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTempCurrencyID = IFNULL(@Last_eng_tblTempCurrencyID,0);
	-- delete if there is any processed data in trigger table
	DELETE FROM speakintelligentRM.eng_tblTempCurrency WHERE eng_tblTempCurrencyID <= @Last_eng_tblTempCurrencyID;

	/************** Currency Section Ends **************/

	/************** CurrencyConversion Section Starts **************/

	-- get last id from log table which we has been processed, so we can delete all processed data from trigger table
	SELECT MAX(eng_tblTempCurrencyConversionID) INTO @Last_eng_tblTempCurrencyConversionID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTempCurrencyConversionID = IFNULL(@Last_eng_tblTempCurrencyConversionID,0);
	-- delete if there is any processed data in trigger table
	DELETE FROM speakintelligentRM.eng_tblTempCurrencyConversion WHERE eng_tblTempCurrencyConversionID <= @Last_eng_tblTempCurrencyConversionID;

	/************** CurrencyConversion Section Ends **************/

	/************** VendorTimezone Section Starts **************/

	-- get last id from log table which we has been processed, so we can delete all processed data from trigger table
	SELECT MAX(eng_tblTempVendorTimezoneID) INTO @Last_eng_tblTempVendorTimezoneID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTempVendorTimezoneID = IFNULL(@Last_eng_tblTempVendorTimezoneID,0);
	-- delete if there is any processed data in trigger table
	DELETE FROM speakintelligentRM.eng_tblTempVendorTimezone WHERE eng_tblTempVendorTimezoneID <= @Last_eng_tblTempVendorTimezoneID;

	/************** VendorTimezone Section Ends **************/

	/************** Timezones Section Starts **************/

	-- get last id from log table which we has been processed, so we can delete all processed data from trigger table
	SELECT MAX(eng_tblTempTimezonesID) INTO @Last_eng_tblTempTimezonesID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTempTimezonesID = IFNULL(@Last_eng_tblTempTimezonesID,0);
	-- delete if there is any processed data in trigger table
	DELETE FROM speakintelligentRM.eng_tblTempTimezones WHERE eng_tblTempTimezonesID <= @Last_eng_tblTempTimezonesID;

	/************** Timezones Section Ends **************/

	/************** CLIRateTable Section Starts **************/

	-- get last id from log table which we has been processed, so we can delete all processed data from trigger table
	SELECT MAX(eng_tblTempCLIRateTableID) INTO @Last_eng_tblTempCLIRateTableID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTempCLIRateTableID = IFNULL(@Last_eng_tblTempCLIRateTableID,0);
	-- delete if there is any processed data in trigger table
	DELETE FROM speakintelligentRM.eng_tblTempCLIRateTable WHERE eng_tblTempCLIRateTableID <= @Last_eng_tblTempCLIRateTableID;

	/************** CLIRateTable Section Ends **************/

	/************** AccountServicePackage Section Starts **************/

	-- get last id from log table which we has been processed, so we can delete all processed data from trigger table
	SELECT MAX(eng_tblAccountServicePackageID) INTO @Last_eng_tblAccountServicePackageID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblAccountServicePackageID = IFNULL(@Last_eng_tblAccountServicePackageID,0);
	-- delete if there is any processed data in trigger table
	DELETE FROM speakintelligentRM.eng_tblAccountServicePackage WHERE eng_tblAccountServicePackageID <= @Last_eng_tblAccountServicePackageID;

	/************** AccountServicePackage Section Ends **************/

	/************** TaxRate Section Starts **************/

	-- get last id from log table which we has been processed, so we can delete all processed data from trigger table
	SELECT MAX(eng_tblTaxRateID) INTO @Last_eng_tblTaxRateID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTaxRateID = IFNULL(@Last_eng_tblTaxRateID,0);
	-- delete if there is any processed data in trigger table
	DELETE FROM speakintelligentRM.eng_tblTaxRate WHERE eng_tblTaxRateID <= @Last_eng_tblTaxRateID;

	/************** TaxRate Section Ends **************/

	/************** Access/DID Rate Section Starts **************/

	-- get last id from log table which we has been processed, so we can delete all processed data from trigger table
	SELECT MAX(eng_tblRateTableDIDRateID) INTO @Last_eng_tblRateTableDIDRateID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblRateTableDIDRateID = IFNULL(@Last_eng_tblRateTableDIDRateID,0);
	-- delete if there is any processed data in trigger table
	DELETE FROM speakintelligentRM.eng_tblRateTableDIDRate WHERE eng_tblRateTableDIDRateID <= @Last_eng_tblRateTableDIDRateID;

	/************** Access/DID Rate Section Ends **************/

	/************** Package Rate Section Starts **************/

	-- get last id from log table which we has been processed, so we can delete all processed data from trigger table
	SELECT MAX(eng_tblRateTablePKGRateID) INTO @Last_eng_tblRateTablePKGRateID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblRateTablePKGRateID = IFNULL(@Last_eng_tblRateTablePKGRateID,0);
	-- delete if there is any processed data in trigger table
	DELETE FROM speakintelligentRM.eng_tblRateTablePKGRate WHERE eng_tblRateTablePKGRateID <= @Last_eng_tblRateTablePKGRateID;

	/************** Package Rate Section Ends **************/

	/************** Termination Vendor Rate Section Starts **************/

	-- get last id from log table which we has been processed, so we can delete all processed data from trigger table
	SELECT MAX(eng_tblTempRateTableRateID) INTO @Last_eng_tblTempRateTableRateID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTempRateTableRateID = IFNULL(@Last_eng_tblTempRateTableRateID,0);
	-- count if there is any processed data in trigger table
	SELECT COUNT(*) INTO @V_VendorRate_Count FROM speakintelligentRM.eng_tblTempRateTableRate_Test WHERE eng_tblTempRateTableRateID <= @Last_eng_tblTempRateTableRateID;
	-- delete in chunks if there is any processed data in trigger table
	SET @i = 0;
	SET @LastID = 0;
	SET @ETID = 0;
	WHILE @i < @V_VendorRate_Count
	DO
		START TRANSACTION;

		SET @stm = CONCAT('
			DELETE
				trg
			FROM
				speakintelligentRM.eng_tblTempRateTableRate_Test trg
			INNER JOIN
			(
				SELECT
					rtr.eng_tblTempRateTableRateID,@ETID := rtr.eng_tblTempRateTableRateID
				FROM
					speakintelligentRM.eng_tblTempRateTableRate_Test
				WHERE
					eng_tblTempRateTableRateID <= @Last_eng_tblTempRateTableRateID AND
					eng_tblTempRateTableRateID > @LastID
				ORDER BY
					eng_tblTempRateTableRateID
				LIMIT
					',@chunk_limit,'
			) tmp ON tmp.eng_tblTempRateTableRateID = trg.eng_tblTempRateTableRateID;
		');
		PREPARE stmt FROM @stm; EXECUTE stmt; DEALLOCATE PREPARE stmt;

		SET @LastID = @ETID;
		SET @i = @i+@chunk_limit;

		COMMIT;

	END WHILE;

	/************** Termination Vendor Rate Section Ends **************/

	/************** Termination Customer Rate Section Starts **************/

	-- get last id from log table which we has been processed, so we can delete all processed data from trigger table
	SELECT MAX(eng_tblTempCustomerRateTableRateID) INTO @Last_eng_tblTempCustomerRateTableRateID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTempCustomerRateTableRateID = IFNULL(@Last_eng_tblTempCustomerRateTableRateID,0);
	-- count if there is any processed data in trigger table
	SELECT COUNT(*) INTO @V_CustomerRate_Count FROM speakintelligentRM.eng_tblTempCustomerRateTableRate WHERE eng_tblTempCustomerRateTableRateID <= @Last_eng_tblTempCustomerRateTableRateID;
	-- delete in chunks if there is any processed data in trigger table
	SET @i = 0;
	SET @LastID = 0;
	SET @ETID = 0;
	WHILE @i < @V_CustomerRate_Count
	DO
		START TRANSACTION;

		SET @stm = CONCAT('
			DELETE
				trg
			FROM
				speakintelligentRM.eng_tblTempCustomerRateTableRate trg
			INNER JOIN
			(
				SELECT
					rtr.eng_tblTempCustomerRateTableRateID,@ETID := rtr.eng_tblTempCustomerRateTableRateID
				FROM
					speakintelligentRM.eng_tblTempCustomerRateTableRate
				WHERE
					eng_tblTempCustomerRateTableRateID <= @Last_eng_tblTempCustomerRateTableRateID AND
					eng_tblTempCustomerRateTableRateID > @LastID
				ORDER BY
					eng_tblTempCustomerRateTableRateID
				LIMIT
					',@chunk_limit,'
			) tmp ON tmp.eng_tblTempCustomerRateTableRateID = trg.eng_tblTempCustomerRateTableRateID;
		');
		PREPARE stmt FROM @stm; EXECUTE stmt; DEALLOCATE PREPARE stmt;

		SET @LastID = @ETID;
		SET @i = @i+@chunk_limit;

		COMMIT;

	END WHILE;

	/************** Termination Customer Rate Section Ends **************/

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;
















use `speakintelligentRM`; -- web server


INSERT INTO `tblCronJobCommand` (`CompanyID`, `GatewayID`, `Title`, `Command`, `Settings`, `Status`, `created_at`, `created_by`) VALUES (1, NULL, 'Trigger Data CleanUp', 'triggerdatacleanup', '[[{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"}]]', 1, '2020-01-29 03:35:53', 'System');


ALTER TABLE `eng_tblTempAccount`
  ALTER `AccountID` DROP DEFAULT;
ALTER TABLE `eng_tblTempAccount`
  ADD COLUMN `eng_tblTempAccountID` BIGINT(20) NOT NULL AUTO_INCREMENT FIRST,
  CHANGE COLUMN `AccountID` `AccountID` INT(11) NOT NULL AFTER `eng_tblTempAccountID`,
  ADD INDEX `IX_eng_tblTempAccountID` (`eng_tblTempAccountID`);

ALTER TABLE `eng_tblTempRateTable`
  ALTER `RateTableId` DROP DEFAULT;
ALTER TABLE `eng_tblTempRateTable`
  ADD COLUMN `eng_tblTempRateTableID` BIGINT(20) NOT NULL AUTO_INCREMENT FIRST,
  CHANGE COLUMN `RateTableId` `RateTableId` BIGINT(20) NOT NULL AFTER `eng_tblTempRateTableID`,
  ADD INDEX `IX_eng_tblTempRateTableID` (`eng_tblTempRateTableID`);

ALTER TABLE `eng_tblTempRoutingCategory`
  ALTER `RoutingCategoryID` DROP DEFAULT;
ALTER TABLE `eng_tblTempRoutingCategory`
  ADD COLUMN `eng_tblTempRoutingCategoryID` BIGINT(20) NOT NULL AUTO_INCREMENT FIRST,
  CHANGE COLUMN `RoutingCategoryID` `RoutingCategoryID` INT(11) NOT NULL AFTER `eng_tblTempRoutingCategoryID`,
  ADD INDEX `IX_eng_tblTempRoutingCategoryID` (`eng_tblTempRoutingCategoryID`);

ALTER TABLE `eng_tblTempRoutingProfile`
  ALTER `RoutingProfileID` DROP DEFAULT;
ALTER TABLE `eng_tblTempRoutingProfile`
  ADD COLUMN `eng_tblTempRoutingProfileID` BIGINT NOT NULL AUTO_INCREMENT FIRST,
  CHANGE COLUMN `RoutingProfileID` `RoutingProfileID` INT(11) NOT NULL AFTER `eng_tblTempRoutingProfileID`,
  ADD INDEX `IX_eng_tblTempRoutingProfileID` (`eng_tblTempRoutingProfileID`);

ALTER TABLE `eng_tblTempRoutingProfileCategory`
  ALTER `RoutingProfileCategoryID` DROP DEFAULT;
ALTER TABLE `eng_tblTempRoutingProfileCategory`
  ADD COLUMN `eng_tblTempRoutingProfileCategoryID` BIGINT(20) NOT NULL AUTO_INCREMENT FIRST,
  CHANGE COLUMN `RoutingProfileCategoryID` `RoutingProfileCategoryID` INT(11) NOT NULL AFTER `eng_tblTempRoutingProfileCategoryID`,
  ADD INDEX `IX_eng_tblTempRoutingProfileCategoryID` (`eng_tblTempRoutingProfileCategoryID`);

ALTER TABLE `eng_tblTempRoutingProfileToCustomer`
  ALTER `RoutingProfileToCustomerID` DROP DEFAULT;
ALTER TABLE `eng_tblTempRoutingProfileToCustomer`
  ADD COLUMN `eng_tblTempRoutingProfileToCustomerID` BIGINT(20) NOT NULL AUTO_INCREMENT FIRST,
  CHANGE COLUMN `RoutingProfileToCustomerID` `RoutingProfileToCustomerID` INT(11) NOT NULL AFTER `eng_tblTempRoutingProfileToCustomerID`,
  ADD INDEX `IX_eng_tblTempRoutingProfileToCustomerID` (`eng_tblTempRoutingProfileToCustomerID`);

ALTER TABLE `eng_tblTempVendorConnection`
  ALTER `VendorConnectionID` DROP DEFAULT;
ALTER TABLE `eng_tblTempVendorConnection`
  ADD COLUMN `eng_tblTempVendorConnectionID` BIGINT(20) NOT NULL AUTO_INCREMENT FIRST,
  CHANGE COLUMN `VendorConnectionID` `VendorConnectionID` INT(11) NOT NULL AFTER `eng_tblTempVendorConnectionID`,
  ADD INDEX `IX_eng_tblTempVendorConnectionID` (`eng_tblTempVendorConnectionID`);

ALTER TABLE `eng_tblTempReseller`
  ALTER `ResellerID` DROP DEFAULT;
ALTER TABLE `eng_tblTempReseller`
  ADD COLUMN `eng_tblTempResellerID` BIGINT(20) NOT NULL AUTO_INCREMENT FIRST,
  CHANGE COLUMN `ResellerID` `ResellerID` INT(11) NOT NULL AFTER `eng_tblTempResellerID`,
  ADD INDEX `IX_eng_tblTempResellerID` (`eng_tblTempResellerID`);

ALTER TABLE `eng_tblTempCurrency`
  ALTER `CurrencyId` DROP DEFAULT;
ALTER TABLE `eng_tblTempCurrency`
  ADD COLUMN `eng_tblTempCurrencyID` BIGINT(20) NOT NULL AUTO_INCREMENT FIRST,
  CHANGE COLUMN `CurrencyId` `CurrencyId` INT(11) NOT NULL AFTER `eng_tblTempCurrencyID`,
  ADD INDEX `IX_eng_tblTempCurrencyID` (`eng_tblTempCurrencyID`);

ALTER TABLE `eng_tblTempCurrencyConversion`
  ALTER `ConversionID` DROP DEFAULT;
ALTER TABLE `eng_tblTempCurrencyConversion`
  ADD COLUMN `eng_tblTempCurrencyConversionID` BIGINT(20) NOT NULL AUTO_INCREMENT FIRST,
  CHANGE COLUMN `ConversionID` `ConversionID` INT(11) NOT NULL AFTER `eng_tblTempCurrencyConversionID`,
  ADD INDEX `IX_eng_tblTempCurrencyConversionID` (`eng_tblTempCurrencyConversionID`);

ALTER TABLE `eng_tblTempVendorTimezone`
  ALTER `VendorTimezoneID` DROP DEFAULT;
ALTER TABLE `eng_tblTempVendorTimezone`
  ADD COLUMN `eng_tblTempVendorTimezoneID` BIGINT(20) NOT NULL AUTO_INCREMENT FIRST,
  CHANGE COLUMN `VendorTimezoneID` `VendorTimezoneID` INT(11) NOT NULL AFTER `eng_tblTempVendorTimezoneID`,
  ADD INDEX `IX_eng_tblTempVendorTimezoneID` (`eng_tblTempVendorTimezoneID`);

ALTER TABLE `eng_tblTempTimezones`
  ALTER `TimezonesID` DROP DEFAULT;
ALTER TABLE `eng_tblTempTimezones`
  ADD COLUMN `eng_tblTempTimezonesID` BIGINT(20) NOT NULL AUTO_INCREMENT FIRST,
  CHANGE COLUMN `TimezonesID` `TimezonesID` INT(11) NOT NULL AFTER `eng_tblTempTimezonesID`,
  ADD INDEX `IX_eng_tblTempTimezonesID` (`eng_tblTempTimezonesID`);

ALTER TABLE `eng_tblTempCLIRateTable`
  ALTER `CLIRateTableID` DROP DEFAULT;
ALTER TABLE `eng_tblTempCLIRateTable`
  ADD COLUMN `eng_tblTempCLIRateTableID` BIGINT(20) NOT NULL AUTO_INCREMENT FIRST,
  CHANGE COLUMN `CLIRateTableID` `CLIRateTableID` INT(11) NOT NULL AFTER `eng_tblTempCLIRateTableID`,
  ADD INDEX `IX_eng_tblTempCLIRateTableID` (`eng_tblTempCLIRateTableID`);

ALTER TABLE `eng_tblAccountServicePackage`
  ALTER `AccountServicePackageID` DROP DEFAULT;
ALTER TABLE `eng_tblAccountServicePackage`
  ADD COLUMN `eng_tblAccountServicePackageID` BIGINT(20) NOT NULL AUTO_INCREMENT FIRST,
  CHANGE COLUMN `AccountServicePackageID` `AccountServicePackageID` INT(11) NOT NULL AFTER `eng_tblAccountServicePackageID`,
  ADD INDEX `IX_eng_tblAccountServicePackageID` (`eng_tblAccountServicePackageID`);

ALTER TABLE `eng_tblTaxRate`
  ALTER `TaxRateId` DROP DEFAULT;
ALTER TABLE `eng_tblTaxRate`
  ADD COLUMN `eng_tblTaxRateID` BIGINT NOT NULL AUTO_INCREMENT FIRST,
  CHANGE COLUMN `TaxRateId` `TaxRateId` INT(11) NOT NULL AFTER `eng_tblTaxRateID`,
  ADD INDEX `IX_eng_tblTaxRateID` (`eng_tblTaxRateID`);


ALTER TABLE `eng_tblRateTableDIDRate`
  ALTER `RateTableDIDRateID` DROP DEFAULT;
ALTER TABLE `eng_tblRateTableDIDRate`
  ADD COLUMN `eng_tblRateTableDIDRateID` BIGINT(20) NOT NULL AUTO_INCREMENT FIRST,
  CHANGE COLUMN `RateTableDIDRateID` `RateTableDIDRateID` BIGINT(20) NOT NULL AFTER `eng_tblRateTableDIDRateID`,
  ADD INDEX `IX_eng_tblRateTableDIDRateID` (`eng_tblRateTableDIDRateID`);

ALTER TABLE `eng_tblRateTablePKGRate`
  ALTER `RateTablePKGRateID` DROP DEFAULT;
ALTER TABLE `eng_tblRateTablePKGRate`
  ADD COLUMN `eng_tblRateTablePKGRateID` BIGINT(20) NOT NULL AUTO_INCREMENT FIRST,
  CHANGE COLUMN `RateTablePKGRateID` `RateTablePKGRateID` BIGINT(20) NOT NULL AFTER `eng_tblRateTablePKGRateID`,
  ADD INDEX `IX_eng_tblRateTablePKGRateID` (`eng_tblRateTablePKGRateID`);

ALTER TABLE `eng_tblTempRateTableRate`
  ALTER `RateTableRateID` DROP DEFAULT;
ALTER TABLE `eng_tblTempRateTableRate`
  ADD COLUMN `eng_tblTempRateTableRateID` BIGINT(20) NOT NULL AUTO_INCREMENT FIRST,
  CHANGE COLUMN `RateTableRateID` `RateTableRateID` BIGINT(20) NOT NULL AFTER `eng_tblTempRateTableRateID`,
  ADD INDEX `IX_eng_tblTempRateTableRateID` (`eng_tblTempRateTableRateID`);

ALTER TABLE `eng_tblTempCustomerRateTableRate`
  ALTER `RateTableRateID` DROP DEFAULT;
ALTER TABLE `eng_tblTempCustomerRateTableRate`
  ADD COLUMN `eng_tblTempCustomerRateTableRateID` BIGINT(20) NOT NULL AUTO_INCREMENT FIRST,
  CHANGE COLUMN `RateTableRateID` `RateTableRateID` BIGINT(20) NOT NULL AFTER `eng_tblTempCustomerRateTableRateID`,
  ADD INDEX `IX_eng_tblTempCustomerRateTableRateID` (`eng_tblTempCustomerRateTableRateID`);





DROP TRIGGER IF EXISTS `trig_tblRateTableDIDRate_delete`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trig_tblRateTableDIDRate_delete` BEFORE DELETE ON `tblRateTableDIDRate` FOR EACH ROW BEGIN

SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DELETE FROM eng_tblRateTableDIDRate WHERE RateTableDIDRateID=OLD.RateTableDIDRateID;

	INSERT INTO eng_tblRateTableDIDRate(
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
		DestinationCode,
		Action
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
	IFNULL(origRate.Code,'NA') AS OriginationCode,
	destRate.Code  AS DestinationCode,
	'D' AS Action
FROM tblRateTableDIDRate  rtr
	INNER JOIN tblRate destRate  ON  rtr.RateID = destRate.RateId
	LEFT JOIN tblRate origRate ON rtr.OriginationRateID = origRate.RateID
WHERE RateTableDIDRateID=OLD.RateTableDIDRateID;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;





DROP TRIGGER IF EXISTS `trig_tblRateTableDIDRate_insert`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trig_tblRateTableDIDRate_insert` AFTER INSERT ON `tblRateTableDIDRate` FOR EACH ROW BEGIN

SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DELETE FROM eng_tblRateTableDIDRate WHERE RateTableDIDRateID=NEW.RateTableDIDRateID;

	INSERT INTO eng_tblRateTableDIDRate(
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
		DestinationCode,
		Action
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
	IFNULL(origRate.Code,'NA') AS OriginationCode,
	destRate.Code  AS DestinationCode,
	'I' AS Action
FROM tblRateTableDIDRate  rtr
	INNER JOIN tblRate destRate  ON  rtr.RateID = destRate.RateId
	LEFT JOIN tblRate origRate ON rtr.OriginationRateID = origRate.RateID
WHERE RateTableDIDRateID=NEW.RateTableDIDRateID;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;





DROP TRIGGER IF EXISTS `trig_tblRateTableDIDRate_update`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trig_tblRateTableDIDRate_update` AFTER UPDATE ON `tblRateTableDIDRate` FOR EACH ROW BEGIN

SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	DELETE FROM eng_tblRateTableDIDRate WHERE RateTableDIDRateID=OLD.RateTableDIDRateID;

	INSERT INTO eng_tblRateTableDIDRate(
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
		DestinationCode,
		Action
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
	IFNULL(origRate.Code,'NA') AS OriginationCode,
	destRate.Code  AS DestinationCode,
	'U' AS Action
FROM tblRateTableDIDRate  rtr
	INNER JOIN tblRate destRate  ON  rtr.RateID = destRate.RateId
	LEFT JOIN tblRate origRate ON rtr.OriginationRateID = origRate.RateID
WHERE RateTableDIDRateID=OLD.RateTableDIDRateID;


	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;





DROP TRIGGER IF EXISTS `trig_tblRateTableRate_delete`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trig_tblRateTableRate_delete` BEFORE DELETE ON `tblRateTableRate` FOR EACH ROW BEGIN
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;



	DELETE FROM eng_tblTempRateTableRate WHERE RateTableRateID=OLD.RateTableRateID;

	INSERT INTO eng_tblTempRateTableRate(
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
		Action
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
		IFNULL(origRate.Code,'NA') AS OriginationCode,
		destRate.Code AS DestinationCode,
		'D' AS Action
	FROM tblRateTableRate  rtr
		INNER JOIN tblRate destRate  ON  rtr.RateID = destRate.RateId
		INNER JOIN tblRateTable rt ON rtr.RateTableID = rt.RateTableId
		LEFT JOIN tblRate origRate ON rtr.OriginationRateID = origRate.RateID
	WHERE RateTableRateID=OLD.RateTableRateID AND rt.`Type` = 1 AND rt.AppliedTo = 2;





	DELETE FROM eng_tblTempCustomerRateTableRate WHERE RateTableRateID=OLD.RateTableRateID;

	INSERT INTO eng_tblTempCustomerRateTableRate(
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
		CountryID,
		Action
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
		IFNULL(origRate.Code,'NA') AS OriginationCode,
		destRate.Code AS DestinationCode,
		IFNULL(destRate.CountryID,0) AS CountryID,
		'D' AS Action
	FROM tblRateTableRate  rtr
		INNER JOIN tblRate destRate  ON  rtr.RateID = destRate.RateId
		INNER JOIN tblRateTable rt ON rtr.RateTableID = rt.RateTableId
		LEFT JOIN tblRate origRate ON rtr.OriginationRateID = origRate.RateID
	WHERE RateTableRateID=OLD.RateTableRateID AND rt.`Type` = 1 AND rt.AppliedTo <> 2;

SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;





DROP TRIGGER IF EXISTS `trig_tblRateTableRate_insert`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trig_tblRateTableRate_insert` AFTER INSERT ON `tblRateTableRate` FOR EACH ROW BEGIN
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	/*Vendor Rate */

	DELETE FROM eng_tblTempRateTableRate WHERE RateTableRateID=NEW.RateTableRateID;

	INSERT INTO eng_tblTempRateTableRate(
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
		Action
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
		IFNULL(origRate.Code,'NA') AS OriginationCode,
		destRate.Code AS DestinationCode,
		'I' AS Action
	FROM tblRateTableRate  rtr
		INNER JOIN tblRate destRate  ON  rtr.RateID = destRate.RateId
		INNER JOIN tblRateTable rt ON rtr.RateTableID = rt.RateTableId
		LEFT JOIN tblRate origRate ON rtr.OriginationRateID = origRate.RateID
	WHERE RateTableRateID=NEW.RateTableRateID AND rt.`Type` = 1 AND rt.AppliedTo = 2;

	/*Vendor Rate end */

	/*Customer outbound Rate */

	DELETE FROM eng_tblTempCustomerRateTableRate WHERE RateTableRateID=NEW.RateTableRateID;

	INSERT INTO eng_tblTempCustomerRateTableRate(
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
		CountryID,
		Action
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
		IFNULL(origRate.Code,'NA') AS OriginationCode,
		destRate.Code AS DestinationCode,
		IFNULL(destRate.CountryID,0) AS CountryID,
		'I' AS Action
	FROM tblRateTableRate  rtr
		INNER JOIN tblRate destRate  ON  rtr.RateID = destRate.RateId
		INNER JOIN tblRateTable rt ON rtr.RateTableID = rt.RateTableId
		LEFT JOIN tblRate origRate ON rtr.OriginationRateID = origRate.RateID
	WHERE RateTableRateID=NEW.RateTableRateID AND rt.`Type` = 1 AND rt.AppliedTo <> 2;

SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;





DROP TRIGGER IF EXISTS `trig_tblRateTableRate_update`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trig_tblRateTableRate_update` AFTER UPDATE ON `tblRateTableRate` FOR EACH ROW BEGIN
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;



	DELETE FROM eng_tblTempRateTableRate WHERE RateTableRateID=OLD.RateTableRateID;

	INSERT INTO eng_tblTempRateTableRate(
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
		Action
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
		IFNULL(origRate.Code,'NA') AS OriginationCode,
		destRate.Code AS DestinationCode,
		'U' AS Action
	FROM tblRateTableRate  rtr
		INNER JOIN tblRate destRate  ON  rtr.RateID = destRate.RateId
		INNER JOIN tblRateTable rt ON rtr.RateTableID = rt.RateTableId
		LEFT JOIN tblRate origRate ON rtr.OriginationRateID = origRate.RateID
	WHERE RateTableRateID=OLD.RateTableRateID AND rt.`Type` = 1 AND rt.AppliedTo = 2;





	DELETE FROM eng_tblTempCustomerRateTableRate WHERE RateTableRateID=OLD.RateTableRateID;

	INSERT INTO eng_tblTempCustomerRateTableRate(
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
		CountryID,
		Action
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
		IFNULL(origRate.Code,'NA') AS OriginationCode,
		destRate.Code AS DestinationCode,
		IFNULL(destRate.CountryID,0) AS CountryID,
		'U' AS Action
	FROM tblRateTableRate  rtr
		INNER JOIN tblRate destRate  ON  rtr.RateID = destRate.RateId
		INNER JOIN tblRateTable rt ON rtr.RateTableID = rt.RateTableId
		LEFT JOIN tblRate origRate ON rtr.OriginationRateID = origRate.RateID
	WHERE RateTableRateID=OLD.RateTableRateID AND rt.`Type` = 1 AND rt.AppliedTo <> 2;



SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;