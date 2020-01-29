use `speakintelligentRoutingEngine`; -- app server

DROP TABLE IF EXISTS `tblSyncRoutingDataLog`;
CREATE TABLE IF NOT EXISTS `tblSyncRoutingDataLog` (
  `SyncRoutingDataLogID` int(11) NOT NULL AUTO_INCREMENT,
  `eng_tblTempAccountID` int(11) NOT NULL DEFAULT 0,
  `eng_tblTempRateTableID` bigint(20) NOT NULL DEFAULT 0,
  `eng_tblTempRoutingCategoryID` int(11) NOT NULL DEFAULT 0,
  `eng_tblTempRoutingProfileID` int(11) NOT NULL DEFAULT 0,
  `eng_tblTempRoutingProfileCategoryID` int(11) NOT NULL DEFAULT 0,
  `eng_tblTempRoutingProfileToCustomerID` int(11) NOT NULL DEFAULT 0,
  `eng_tblTempVendorConnectionID` int(11) NOT NULL DEFAULT 0,
  `eng_tblTempResellerID` int(11) NOT NULL DEFAULT 0,
  `eng_tblTempCurrencyID` int(11) NOT NULL DEFAULT 0,
  `eng_tblTempCurrencyConversionID` int(11) NOT NULL DEFAULT 0,
  `eng_tblTempVendorTimezoneID` int(11) NOT NULL DEFAULT 0,
  `eng_tblTempTimezonesID` int(11) NOT NULL DEFAULT 0,
  `eng_tblTempCLIRateTableID` int(11) NOT NULL DEFAULT 0,
  `eng_tblAccountServicePackageID` int(11) NOT NULL DEFAULT 0,
  `eng_tblTaxRateID` int(11) NOT NULL DEFAULT 0,
  `eng_tblRateTableDIDRateID` bigint(20) NOT NULL DEFAULT 0,
  `eng_tblRateTablePKGRateID` bigint(20) NOT NULL DEFAULT 0,
  `eng_tblTempRateTableRateID` bigint(20) NOT NULL DEFAULT 0,
  `eng_tblTempCustomerRateTableRateID` bigint(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`SyncRoutingDataLogID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



DROP PROCEDURE IF EXISTS `prc_APIRoutingDataPerRow`;
DELIMITER //
CREATE PROCEDURE `prc_APIRoutingDataPerRow`()
ThisSP:BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET @chunk_limit = 100000;


	/************** Access/DID Rate Starts **************/

	-- get last id from log table which we have processed, so we can process all data came after that
	SELECT MAX(eng_tblRateTableDIDRateID) INTO @Last_eng_tblRateTableDIDRateID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblRateTableDIDRateID = IFNULL(@Last_eng_tblRateTableDIDRateID,0);
	-- check if new data is available
	SELECT COUNT(*) INTO @V_DID_Count FROM speakintelligentRM.eng_tblRateTableDIDRate WHERE eng_tblRateTableDIDRateID > @Last_eng_tblRateTableDIDRateID;

	-- if new data is available then process it
	IF (@V_DID_Count > 0)
	THEN
		-- get last id as checkpoint so, new data which came while this process is running can not be lost
		SELECT MAX(eng_tblRateTableDIDRateID) INTO @MAX_eng_tblRateTableDIDRateID FROM speakintelligentRM.eng_tblRateTableDIDRate;

		-- create copy table of tblRateTableDIDRate, later we will drop original and rename copy table to original
		DROP TABLE IF EXISTS `temp_tblRateTableDIDRate`;
		CREATE TABLE `temp_tblRateTableDIDRate` LIKE `tblRateTableDIDRate`;
		-- insert all records from tblRateTableDIDRate to new copy table
		INSERT `temp_tblRateTableDIDRate` SELECT * FROM tblRateTableDIDRate;
		-- if data is already exist which needs to process then delete it from new copy table
		DELETE rtd FROM temp_tblRateTableDIDRate rtd INNER JOIN speakintelligentRM.eng_tblRateTableDIDRate e ON e.RateTableDIDRateID = rtd.RateTableDIDRateID WHERE e.eng_tblRateTableDIDRateID > @Last_eng_tblRateTableDIDRateID AND e.eng_tblRateTableDIDRateID <= @MAX_eng_tblRateTableDIDRateID;

		-- if there is any insert/update data then insert it into new copy table starts
		INSERT INTO temp_tblRateTableDIDRate(
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
			now(),
			now(),
			CreatedBy,
			ModifiedBy,
			ApprovedStatus,
			ApprovedBy,
			ApprovedDate,
			VendorID,
			OriginationCode,
			DestinationCode
		FROM
			speakintelligentRM.eng_tblRateTableDIDRate
		WHERE
			(ACTION = 'I' OR ACTION = 'U') AND eng_tblRateTableDIDRateID > @Last_eng_tblRateTableDIDRateID AND eng_tblRateTableDIDRateID <= @MAX_eng_tblRateTableDIDRateID;

		-- drop original table and rename new copy table to original starts
		DROP TABLE IF EXISTS `old_tblRateTableDIDRate`;
		RENAME TABLE tblRateTableDIDRate TO old_tblRateTableDIDRate;
		RENAME TABLE temp_tblRateTableDIDRate TO tblRateTableDIDRate;
		DROP TABLE old_tblRateTableDIDRate;
		-- drop original table and rename new copy table to original ends

		-- add last id of data we have processed in log table,
		-- so next time when this procedure runs it will not process processed data again
		-- and will process data which came after currently processed data
		INSERT INTO tblSyncRoutingDataLog (eng_tblRateTableDIDRateID) VALUES (@MAX_eng_tblRateTableDIDRateID);
		-- TRUNCATE TABLE speakintelligentRM.eng_tblRateTableDIDRate;
	END IF;


	/************** Access/DID Rate Ends **************/




	/************** Package Rate Starts **************/

	-- get last id from log table which we have processed, so we can process all data came after that
	SELECT MAX(eng_tblRateTablePKGRateID) INTO @Last_eng_tblRateTablePKGRateID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblRateTablePKGRateID = IFNULL(@Last_eng_tblRateTablePKGRateID,0);
	-- check if new data is available
	SELECT COUNT(*) INTO @V_PKG_Count FROM speakintelligentRM.eng_tblRateTablePKGRate WHERE eng_tblRateTablePKGRateID > @Last_eng_tblRateTablePKGRateID;

	-- if new data is available then process it
	IF (@V_PKG_Count > 0)
	THEN
		-- get last id as checkpoint so, new data which came while this process is running can not be lost
		SELECT MAX(eng_tblRateTablePKGRateID) INTO @MAX_eng_tblRateTablePKGRateID FROM speakintelligentRM.eng_tblRateTablePKGRate;

		-- create copy table of tblRateTablePKGRate, later we will drop original and rename copy table to original
		DROP TABLE IF EXISTS `temp_tblRateTablePKGRate`;
		CREATE TABLE `temp_tblRateTablePKGRate` LIKE `tblRateTablePKGRate`;
		-- insert all records from tblRateTablePKGRate to new copy table
		INSERT `temp_tblRateTablePKGRate` SELECT * FROM tblRateTablePKGRate;
		-- if data is already exist which needs to process then delete it from new copy table
		DELETE rtd FROM temp_tblRateTablePKGRate rtd INNER JOIN speakintelligentRM.eng_tblRateTablePKGRate e ON e.RateTablePKGRateID = rtd.RateTablePKGRateID WHERE e.eng_tblRateTablePKGRateID > @Last_eng_tblRateTablePKGRateID AND e.eng_tblRateTablePKGRateID <= @MAX_eng_tblRateTablePKGRateID;

		-- if there is any insert/update data then insert it into new copy table
		INSERT INTO temp_tblRateTablePKGRate(
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
			CODE
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
			now(),
			now(),
			CreatedBy,
			ModifiedBy,
			ApprovedStatus,
			ApprovedBy,
			ApprovedDate,
			VendorID,
			CODE
		FROM
			speakintelligentRM.eng_tblRateTablePKGRate
		WHERE
			(ACTION = "I" OR ACTION = "U") AND eng_tblRateTablePKGRateID > @Last_eng_tblRateTablePKGRateID AND eng_tblRateTablePKGRateID <= @MAX_eng_tblRateTablePKGRateID;

		-- drop original table and rename new copy table to original starts
		DROP TABLE IF EXISTS `old_tblRateTablePKGRate`;
		RENAME TABLE tblRateTablePKGRate TO old_tblRateTablePKGRate;
		RENAME TABLE temp_tblRateTablePKGRate TO tblRateTablePKGRate;
		DROP TABLE old_tblRateTablePKGRate;
		-- drop original table and rename new copy table to original ends

		-- add last id of data we have processed in log table,
		-- so next time when this procedure runs it will not process processed data again
		-- and will process data which came after currently processed data
		INSERT INTO tblSyncRoutingDataLog (eng_tblRateTablePKGRateID) VALUES (@MAX_eng_tblRateTablePKGRateID);
		-- TRUNCATE TABLE speakintelligentRM.eng_tblRateTablePKGRate;

	END IF;


	/************** Package Rate Ends **************/



	/************** Termination Vendor Rate Starts **************/

	-- get last id from log table which we have processed, so we can process all data came after that
	SELECT MAX(eng_tblTempRateTableRateID) INTO @Last_eng_tblTempRateTableRateID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTempRateTableRateID = IFNULL(@Last_eng_tblTempRateTableRateID,0);
	-- check if new data is available
	SELECT COUNT(*) INTO @V_VendorRate_Count FROM speakintelligentRM.eng_tblTempRateTableRate WHERE eng_tblTempRateTableRateID > @Last_eng_tblTempRateTableRateID;

	-- if new data is available then process it
	IF (@V_VendorRate_Count > 0 )
	THEN
		-- get last id as checkpoint so, new data which came while this process is running can not be lost
		SELECT MAX(eng_tblTempRateTableRateID) INTO @MAX_eng_tblTempRateTableRateID FROM speakintelligentRM.eng_tblTempRateTableRate;

		-- create temp table to store all data which needs to process
		DROP TEMPORARY TABLE IF EXISTS `tmp_tblTempRateTableRate_data`;
		CREATE TEMPORARY TABLE IF NOT EXISTS `tmp_tblTempRateTableRate_data` (
		  `eng_tblTempRateTableRateID` bigint(20) NOT NULL AUTO_INCREMENT,
		  `RateTableRateID` bigint(20) NOT NULL,
		  `OriginationRateID` int(11) DEFAULT NULL,
		  `RateID` int(11) NOT NULL,
		  `RateTableId` bigint(20) NOT NULL,
		  `TimezonesID` int(11) NOT NULL DEFAULT 1,
		  `Rate` decimal(18,6) NOT NULL DEFAULT 0.000000,
		  `RateN` decimal(18,6) NOT NULL DEFAULT 0.000000,
		  `EffectiveDate` date NOT NULL,
		  `EndDate` date DEFAULT NULL,
		  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
		  `updated_at` datetime DEFAULT NULL,
		  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `PreviousRate` decimal(18,6) DEFAULT NULL,
		  `Interval1` int(11) DEFAULT NULL,
		  `IntervalN` int(11) DEFAULT NULL,
		  `MinimumDuration` int(11) DEFAULT NULL,
		  `ConnectionFee` decimal(18,6) DEFAULT NULL,
		  `RoutingCategoryID` int(11) DEFAULT NULL,
		  `Preference` int(11) DEFAULT NULL,
		  `Blocked` tinyint(4) NOT NULL DEFAULT 0,
		  `ApprovedStatus` tinyint(4) NOT NULL DEFAULT 1,
		  `ApprovedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `ApprovedDate` datetime DEFAULT NULL,
		  `RateCurrency` int(11) DEFAULT NULL,
		  `ConnectionFeeCurrency` int(11) DEFAULT NULL,
		  `VendorID` int(11) DEFAULT NULL,
		  `OriginationCode` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `DestinationCode` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `Action` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		  PRIMARY KEY (`RateTableRateID`),
		  KEY `IX_eng_tblTempRateTableRateID` (`eng_tblTempRateTableRateID`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

		-- insert all records to temp table which is currently in trigger table
		INSERT INTO tmp_tblTempRateTableRate_data SELECT * FROM speakintelligentRM.eng_tblTempRateTableRate WHERE eng_tblTempRateTableRateID > @Last_eng_tblTempRateTableRateID;

		-- create copy table of tblRateTableRate, later we will drop original and rename copy table to original
		DROP TABLE IF EXISTS temp_tblRateTableRate;
		CREATE TABLE temp_tblRateTableRate LIKE tblRateTableRate;
		-- insert all records from tblRateTableRate to new copy table in chunks starts
		SELECT COUNT(*) INTO @v_temprtrCount_ FROM tblRateTableRate;
		SET @i = 0;
		SET @Last_RateTableRateID = 0;
		SET @RateTableRateID = 0;
		WHILE @i < @v_temprtrCount_
		DO
			START TRANSACTION;

			SET @stm = CONCAT('
				INSERT INTO temp_tblRateTableRate
				SELECT
					*
				FROM
					tblRateTableRate rtr
				INNER JOIN
				(
					SELECT
						RateTableRateID,@RateTableRateID := RateTableRateID
					FROM
						tblRateTableRate
					WHERE
						RateTableRateID > @Last_RateTableRateID
					ORDER BY
						rtr.RateTableRateID
					LIMIT
						',@chunk_limit,'
				) tmp ON tmp.RateTableRateID = rtr.RateTableRateID;
			');
			PREPARE stmt FROM @stm; EXECUTE stmt; DEALLOCATE PREPARE stmt;

			SET @Last_RateTableRateID = @RateTableRateID;
			SET @i = @i+@chunk_limit;

			COMMIT;

		END WHILE;
		-- insert all records from tblRateTableRate to new copy table in chunks ends

		-- check if data is already exist which needs to process
		SELECT COUNT(rtd.RateTableRateID) INTO @v_tempdeletertrCount_ FROM temp_tblRateTableRate rtd INNER JOIN tmp_tblTempRateTableRate_data e ON e.RateTableRateID = rtd.RateTableRateID WHERE e.eng_tblTempRateTableRateID > @Last_eng_tblTempRateTableRateID AND e.eng_tblTempRateTableRateID <= @MAX_eng_tblTempRateTableRateID;
		SET @i = 0;
		SET @Last_RateTableRateID = 0;
		SET @RateTableRateID = 0;
		-- if data is already exist which needs to process then delete it from new copy table starts
		WHILE @i < @v_tempdeletertrCount_
		DO
			START TRANSACTION;

			SET @stm = CONCAT('
				DELETE
					rtd
				FROM
					temp_tblRateTableRate rtd
				INNER JOIN
				(
					SELECT
						rtr.RateTableRateID,@RateTableRateID := rtr.RateTableRateID
					FROM
						temp_tblRateTableRate rtr
					INNER JOIN
						tmp_tblTempRateTableRate_data e ON e.RateTableRateID = rtr.RateTableRateID
					WHERE
						e.eng_tblTempRateTableRateID > @Last_eng_tblTempRateTableRateID AND
						e.eng_tblTempRateTableRateID <= @MAX_eng_tblTempRateTableRateID AND
						rtr.RateTableRateID > @Last_RateTableRateID
					ORDER BY
						rtr.RateTableRateID
					LIMIT
						',@chunk_limit,'
				) tmp ON tmp.RateTableRateID = rtr.RateTableRateID;
			');
			PREPARE stmt FROM @stm; EXECUTE stmt; DEALLOCATE PREPARE stmt;

			SET @Last_RateTableRateID = @RateTableRateID;
			SET @i = @i+@chunk_limit;

			COMMIT;

		END WHILE;
		-- if data is already exist which needs to process then delete it from new copy table ends


		-- check if there is any insert/update data
		SELECT COUNT(RateTableRateID) INTO @v_tempinsertrtrCount_  FROM tmp_tblTempRateTableRate_data
		WHERE (ACTION = "I" OR ACTION = "U") AND eng_tblTempRateTableRateID > @Last_eng_tblTempRateTableRateID AND eng_tblTempRateTableRateID <= @MAX_eng_tblTempRateTableRateID;
		SET @i = 0;
		SET @LastID = 0;
		SET @ETID = 0;
		-- if there is any insert/update data then insert it into new copy table starts
		WHILE @i < @v_tempinsertrtrCount_
		DO
			START TRANSACTION;

			SET @stm = CONCAT('
				INSERT INTO temp_tblRateTableRate(
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
					rtr.RateTableRateID,
					OriginationRateID,
					RateID,
					RateTableId,
					TimezonesID,
					Rate,
					RateN,
					EffectiveDate,
					EndDate,
					now(),
					now(),
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
				FROM
					tmp_tblTempRateTableRate_data rtr
				INNER JOIN
				(
					SELECT
						eng_tblTempRateTableRateID, @ETID := eng_tblTempRateTableRateID
					FROM
						tmp_tblTempRateTableRate_data
					WHERE
						(ACTION = "I" OR ACTION = "U") AND eng_tblTempRateTableRateID > @Last_eng_tblTempRateTableRateID AND eng_tblTempRateTableRateID <= @MAX_eng_tblTempRateTableRateID AND
						eng_tblTempRateTableRateID > @LastID
					ORDER BY
						eng_tblTempRateTableRateID
					LIMIT
						',@chunk_limit,'
				) tmp ON tmp.eng_tblTempRateTableRateID = rtr.eng_tblTempRateTableRateID;
			');
			PREPARE stmt FROM @stm; EXECUTE stmt; DEALLOCATE PREPARE stmt;

			SET @LastID = @ETID;
			SET @i = @i+@chunk_limit;

			COMMIT;

		END WHILE;
		-- if there is any insert/update data then insert it into new copy table starts

		-- drop original table and rename new copy table to original starts
		DROP TABLE IF EXISTS `old_tblRateTableRate`;
		RENAME TABLE tblRateTableRate TO old_tblRateTableRate;
		RENAME TABLE temp_tblRateTableRate TO tblRateTableRate;
		DROP TABLE old_tblRateTableRate;
		-- drop original table and rename new copy table to original ends

		-- add last id of data we have processed in log table,
		-- so next time when this procedure runs it will not process processed data again
		-- and will process data which came after currently processed data
		INSERT INTO tblSyncRoutingDataLog (eng_tblTempRateTableRateID) VALUES (@MAX_eng_tblTempRateTableRateID);
		-- DELETE FROM speakintelligentRM.eng_tblTempRateTableRate WHERE eng_tblTempRateTableRateID <= @MAX_eng_tblTempRateTableRateID;
	END IF;


	/************** Termination Vendor Rate Ends **************/



	/************** Termination Customer Rate Starts **************/

	-- get last id from log table which we have processed, so we can process all data came after that
	SELECT MAX(eng_tblTempCustomerRateTableRateID) INTO @Last_eng_tblTempCustomerRateTableRateID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTempCustomerRateTableRateID = IFNULL(@Last_eng_tblTempCustomerRateTableRateID,0);
	-- check if new data is available
	SELECT COUNT(*) INTO @V_CustomerRate_Count FROM speakintelligentRM.eng_tblTempCustomerRateTableRate WHERE eng_tblTempCustomerRateTableRateID > @Last_eng_tblTempCustomerRateTableRateID;

	-- if new data is available then process it
	IF (@V_CustomerRate_Count > 0 )
	THEN
		-- get last id as checkpoint so, new data which came while this process is running can not be lost
		SELECT MAX(eng_tblTempCustomerRateTableRateID) INTO @MAX_eng_tblTempCustomerRateTableRateID FROM speakintelligentRM.eng_tblTempCustomerRateTableRate;

		-- create temp table to store all data which needs to process
		DROP TEMPORARY TABLE IF EXISTS `tmp_tblTempCustomerRateTableRate_data`;
		CREATE TEMPORARY TABLE IF NOT EXISTS `tmp_tblTempCustomerRateTableRate_data` (
		  `eng_tblTempCustomerRateTableRateID` bigint(20) NOT NULL AUTO_INCREMENT,
		  `RateTableRateID` bigint(20) NOT NULL,
		  `OriginationRateID` int(11) DEFAULT NULL,
		  `RateID` int(11) NOT NULL,
		  `RateTableId` bigint(20) NOT NULL,
		  `TimezonesID` int(11) NOT NULL DEFAULT 1,
		  `Rate` decimal(18,6) NOT NULL DEFAULT 0.000000,
		  `RateN` decimal(18,6) NOT NULL DEFAULT 0.000000,
		  `EffectiveDate` date NOT NULL,
		  `EndDate` date DEFAULT NULL,
		  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
		  `updated_at` datetime DEFAULT NULL,
		  `CreatedBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `ModifiedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `PreviousRate` decimal(18,6) DEFAULT NULL,
		  `Interval1` int(11) DEFAULT NULL,
		  `IntervalN` int(11) DEFAULT NULL,
		  `MinimumDuration` int(11) DEFAULT NULL,
		  `ConnectionFee` decimal(18,6) DEFAULT NULL,
		  `RoutingCategoryID` int(11) DEFAULT NULL,
		  `Preference` int(11) DEFAULT NULL,
		  `Blocked` tinyint(4) NOT NULL DEFAULT 0,
		  `ApprovedStatus` tinyint(4) NOT NULL DEFAULT 1,
		  `ApprovedBy` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `ApprovedDate` datetime DEFAULT NULL,
		  `RateCurrency` int(11) DEFAULT NULL,
		  `ConnectionFeeCurrency` int(11) DEFAULT NULL,
		  `VendorID` int(11) DEFAULT NULL,
		  `OriginationCode` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `DestinationCode` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `CountryID` int(11) DEFAULT NULL,
		  `Action` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		  PRIMARY KEY (`RateTableRateID`),
		  KEY `IX_eng_tblTempCustomerRateTableRateID` (`eng_tblTempCustomerRateTableRateID`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


		-- insert all records to temp table which is currently in trigger table
		INSERT INTO tmp_tblTempCustomerRateTableRate_data SELECT * FROM speakintelligentRM.eng_tblTempCustomerRateTableRate WHERE eng_tblTempCustomerRateTableRateID > @Last_eng_tblTempCustomerRateTableRateID;

		-- create copy table of tblCustomerRateTableRate, later we will drop original and rename copy table to original
		DROP TABLE IF EXISTS temp_tblCustomerRateTableRate;
		CREATE TABLE temp_tblCustomerRateTableRate LIKE tblCustomerRateTableRate;
		-- insert all records from tblCustomerRateTableRate to new copy table in chunks starts
		SELECT COUNT(*) INTO @v_tempcrtrCount_ FROM tblCustomerRateTableRate;
		SET @i = 0;
		SET @Last_RateTableRateID = 0;
		SET @RateTableRateID = 0;
		WHILE @i < @v_tempcrtrCount_
		DO
			START TRANSACTION;

			SET @stm = CONCAT('
				INSERT INTO temp_tblCustomerRateTableRate
				SELECT
					*
				FROM
					tblCustomerRateTableRate rtr
				INNER JOIN
				(
					SELECT
						RateTableRateID,@RateTableRateID := RateTableRateID
					FROM
						tblCustomerRateTableRate
					WHERE
						RateTableRateID > @Last_RateTableRateID
					ORDER BY
						rtr.RateTableRateID
					LIMIT
						',@chunk_limit,'
				) tmp ON tmp.RateTableRateID = rtr.RateTableRateID;
			');
			PREPARE stmt FROM @stm; EXECUTE stmt; DEALLOCATE PREPARE stmt;

			SET @Last_RateTableRateID = @RateTableRateID;
			SET @i = @i+@chunk_limit;

			COMMIT;

		END WHILE;
		-- insert all records from tblCustomerRateTableRate to new copy table in chunks ends

		-- check if data is already exist which needs to process
		SELECT COUNT(rtd.RateTableRateID) INTO @v_tempdeletecrtrCount_ FROM temp_tblCustomerRateTableRate rtd INNER JOIN tmp_tblTempCustomerRateTableRate_data e ON e.RateTableRateID = rtd.RateTableRateID WHERE e.eng_tblTempCustomerRateTableRateID > @Last_eng_tblTempCustomerRateTableRateID AND e.eng_tblTempCustomerRateTableRateID <= @MAX_eng_tblTempCustomerRateTableRateID;
		SET @i = 0;
		SET @Last_RateTableRateID = 0;
		SET @RateTableRateID = 0;
		-- if data is already exist which needs to process then delete it from new copy table starts
		WHILE @i < @v_tempdeletecrtrCount_
		DO
			START TRANSACTION;

			SET @stm = CONCAT('
				DELETE
					rtd
				FROM
					temp_tblCustomerRateTableRate rtd
				INNER JOIN
				(
					SELECT
						rtr.RateTableRateID,@RateTableRateID := rtr.RateTableRateID
					FROM
						temp_tblCustomerRateTableRate rtr
					INNER JOIN
						tmp_tblTempCustomerRateTableRate_data e ON e.RateTableRateID = rtr.RateTableRateID
					WHERE
						e.eng_tblTempCustomerRateTableRateID > @Last_eng_tblTempCustomerRateTableRateID AND
						e.eng_tblTempCustomerRateTableRateID <= @MAX_eng_tblTempCustomerRateTableRateID AND
						rtr.RateTableRateID > @Last_RateTableRateID
					ORDER BY
						rtr.RateTableRateID
					LIMIT
						',@chunk_limit,'
				) tmp ON tmp.RateTableRateID = rtr.RateTableRateID;
			');
			PREPARE stmt FROM @stm; EXECUTE stmt; DEALLOCATE PREPARE stmt;

			SET @Last_RateTableRateID = @RateTableRateID;
			SET @i = @i+@chunk_limit;

			COMMIT;

		END WHILE;
		-- if data is already exist which needs to process then delete it from new copy table ends


		-- check if there is any insert/update data
		SELECT COUNT(RateTableRateID) INTO @v_tempinsertcrtrCount_  FROM tmp_tblTempCustomerRateTableRate_data
		WHERE (ACTION = "I" OR ACTION = "U") AND eng_tblTempCustomerRateTableRateID > @Last_eng_tblTempCustomerRateTableRateID AND eng_tblTempCustomerRateTableRateID <= @MAX_eng_tblTempCustomerRateTableRateID;
		SET @i = 0;
		SET @LastID = 0;
		SET @ETID = 0;
		-- if there is any insert/update data then insert it into new copy table starts
		WHILE @i < @v_tempinsertcrtrCount_
		DO
			START TRANSACTION;

			SET @stm = CONCAT('
				INSERT INTO temp_tblCustomerRateTableRate(
					RateTableRateID,
					RateID,
					RateTableId,
					OriginationRateID,
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
					RoutingCategoryID,
					Preference,
					Blocked,
					ApprovedStatus,
					ApprovedBy,
					ApprovedDate,
					RateCurrency,
					ConnectionFeeCurrency,
					VendorID,
					OriginationCode,
					DestinationCode,
					CountryID
				)
				SELECT
					rtr.RateTableRateID,
					OriginationRateID,
					RateID,
					RateTableId,
					TimezonesID,
					Rate,
					RateN,
					EffectiveDate,
					EndDate,
					now(),
					now(),
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
				FROM
					tmp_tblTempCustomerRateTableRate_data rtr
				INNER JOIN
				(
					SELECT
						eng_tblTempCustomerRateTableRateID, @ETID := eng_tblTempCustomerRateTableRateID
					FROM
						tmp_tblTempCustomerRateTableRate_data
					WHERE
						(ACTION = "I" OR ACTION = "U") AND eng_tblTempCustomerRateTableRateID > @Last_eng_tblTempCustomerRateTableRateID AND eng_tblTempCustomerRateTableRateID <= @MAX_eng_tblTempCustomerRateTableRateID AND
						eng_tblTempCustomerRateTableRateID > @LastID
					ORDER BY
						eng_tblTempCustomerRateTableRateID
					LIMIT
						',@chunk_limit,'
				) tmp ON tmp.eng_tblTempCustomerRateTableRateID = rtr.eng_tblTempCustomerRateTableRateID;
			');
			PREPARE stmt FROM @stm; EXECUTE stmt; DEALLOCATE PREPARE stmt;

			SET @LastID = @ETID;
			SET @i = @i+@chunk_limit;

			COMMIT;

		END WHILE;
		-- if there is any insert/update data then insert it into new copy table starts

		-- drop original table and rename new copy table to original starts
		DROP TABLE IF EXISTS `old_tblCustomerRateTableRate`;
		RENAME TABLE tblCustomerRateTableRate TO old_tblCustomerRateTableRate;
		RENAME TABLE temp_tblCustomerRateTableRate TO tblCustomerRateTableRate;
		DROP TABLE old_tblCustomerRateTableRate;
		-- drop original table and rename new copy table to original ends

		-- add last id of data we have processed in log table,
		-- so next time when this procedure runs it will not process processed data again
		-- and will process data which came after currently processed data
		INSERT INTO tblSyncRoutingDataLog (eng_tblTempCustomerRateTableRateID) VALUES (@MAX_eng_tblTempCustomerRateTableRateID);
		-- DELETE FROM speakintelligentRM.eng_tblTempCustomerRateTableRate WHERE eng_tblTempCustomerRateTableRateID <= @MAX_eng_tblTempCustomerRateTableRateID;
	END IF;

	/************** Termination Customer Rate Ends **************/



	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;




DROP PROCEDURE IF EXISTS `prc_RoutingDataPerRow`;
DELIMITER //
CREATE PROCEDURE `prc_RoutingDataPerRow`()
BEGIN
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	/************** Account Section Starts **************/

	-- get last id from log table which we have processed, so we can process all data came after that
	SELECT MAX(eng_tblTempAccountID) INTO @Last_eng_tblTempAccountID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTempAccountID = IFNULL(@Last_eng_tblTempAccountID,0);
	-- check if new data is available
	SELECT COUNT(*) INTO @V_AC_Count FROM speakintelligentRM.eng_tblTempAccount WHERE eng_tblTempAccountID > @Last_eng_tblTempAccountID;
	-- if new data is available then process it
	IF (@V_AC_Count > 0)
	THEN
		-- get last id as checkpoint so, new data which came while this process is running can not be lost
		SELECT MAX(eng_tblTempAccountID) INTO @MAX_eng_tblTempAccountID FROM speakintelligentRM.eng_tblTempAccount;

		DELETE ac FROM tblAccount AS ac INNER JOIN speakintelligentRM.eng_tblTempAccount AS e ON e.AccountID = ac.AccountID WHERE e.eng_tblTempAccountID > @Last_eng_tblTempAccountID AND e.eng_tblTempAccountID <= @MAX_eng_tblTempAccountID;
		INSERT INTO tblAccount(AccountID, AccountType, CompanyId, CurrencyId, Number, AccountName, IsVendor, IsCustomer, IsReseller, STATUS, created_at, created_by, updated_at, updated_by,Country,CustomerID,TimeZone,Billing,TaxRateID)
		SELECT AccountID, AccountType, CompanyId, CurrencyId, Number, AccountName, IsVendor, IsCustomer, IsReseller, STATUS, created_at, created_by, updated_at,updated_by,Country,CustomerID,TimeZone,Billing,TaxRateID FROM speakintelligentRM.eng_tblTempAccount where eng_tblTempAccountID > @Last_eng_tblTempAccountID AND eng_tblTempAccountID <= @MAX_eng_tblTempAccountID AND `Action` IS NULL;
		DELETE FROM speakintelligentRM.eng_tblTempAccount WHERE eng_tblTempAccountID > @Last_eng_tblTempAccountID AND eng_tblTempAccountID <= @MAX_eng_tblTempAccountID;
		-- add last id of data we have processed in log table, so next time when this procedure runs it will not process processed data again and will process data which came after currently processed data
		INSERT INTO tblSyncRoutingDataLog (eng_tblTempAccountID) VALUES (@MAX_eng_tblTempAccountID);
	END IF;

	/************** Account Section Ends **************/

	/************** RateTable Section Starts **************/

	-- get last id from log table which we have processed, so we can process all data came after that
	SELECT MAX(eng_tblTempRateTableID) INTO @Last_eng_tblTempRateTableID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTempRateTableID = IFNULL(@Last_eng_tblTempRateTableID,0);
	-- check if new data is available
	SELECT COUNT(*) INTO @V_RT_Count FROM speakintelligentRM.eng_tblTempRateTable WHERE eng_tblTempRateTableID > @Last_eng_tblTempRateTableID;
	-- if new data is available then process it
	IF (@V_RT_Count > 0)
	THEN
		-- get last id as checkpoint so, new data which came while this process is running can not be lost
		SELECT MAX(eng_tblTempRateTableID) INTO @MAX_eng_tblTempRateTableID FROM speakintelligentRM.eng_tblTempRateTable;

		DELETE rt FROM tblRateTable AS rt INNER JOIN speakintelligentRM.eng_tblTempRateTable AS e ON e.RateTableId = rt.RateTableId WHERE e.eng_tblTempRateTableID > @Last_eng_tblTempRateTableID AND e.eng_tblTempRateTableID <= @MAX_eng_tblTempRateTableID;
		INSERT INTO tblRateTable(RateTableId, CompanyId, CodeDeckId, RateTableName, RateGeneratorID, TrunkID, STATUS, created_at, CreatedBy, updated_at, ModifiedBy, CurrencyID, RoundChargedAmount, DIDCategoryID, TYPE, MinimumCallCharge, AppliedTo)
		SELECT RateTableId, CompanyId, CodeDeckId, RateTableName, RateGeneratorID, TrunkID, STATUS, created_at, CreatedBy, updated_at, ModifiedBy, CurrencyID, RoundChargedAmount, DIDCategoryID, TYPE, MinimumCallCharge, AppliedTo FROM speakintelligentRM.eng_tblTempRateTable WHERE eng_tblTempRateTableID > @Last_eng_tblTempRateTableID AND eng_tblTempRateTableID <= @MAX_eng_tblTempRateTableID AND Action IS NULL;
		DELETE FROM speakintelligentRM.eng_tblTempRateTable WHERE eng_tblTempRateTableID > @Last_eng_tblTempRateTableID AND eng_tblTempRateTableID <= @MAX_eng_tblTempRateTableID;
		-- add last id of data we have processed in log table, so next time when this procedure runs it will not process processed data again and will process data which came after currently processed data
		INSERT INTO tblSyncRoutingDataLog (eng_tblTempRateTableID) VALUES (@MAX_eng_tblTempRateTableID);
	END IF;

	/************** RateTable Section Ends **************/

	/************** RoutingCategory Section Starts **************/

	-- get last id from log table which we have processed, so we can process all data came after that
	SELECT MAX(eng_tblTempRoutingCategoryID) INTO @Last_eng_tblTempRoutingCategoryID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTempRoutingCategoryID = IFNULL(@Last_eng_tblTempRoutingCategoryID,0);
	-- check if new data is available
	SELECT COUNT(*) INTO @V_RC_Count FROM speakintelligentRM.eng_tblTempRoutingCategory WHERE eng_tblTempRoutingCategoryID > @Last_eng_tblTempRoutingCategoryID;
	-- if new data is available then process it
	IF (@V_RC_Count > 0)
	THEN
		-- get last id as checkpoint so, new data which came while this process is running can not be lost
		SELECT MAX(eng_tblTempRoutingCategoryID) INTO @MAX_eng_tblTempRoutingCategoryID FROM speakintelligentRM.eng_tblTempRoutingCategory;

		DELETE rc FROM tblRoutingCategory AS rc INNER JOIN speakintelligentRM.eng_tblTempRoutingCategory AS e ON e.RoutingCategoryID = rc.RoutingCategoryID WHERE e.eng_tblTempRoutingCategoryID > @Last_eng_tblTempRoutingCategoryID AND e.eng_tblTempRoutingCategoryID <= @MAX_eng_tblTempRoutingCategoryID;
		INSERT INTO tblRoutingCategory(RoutingCategoryID, NAME, Description, CompanyID, created_at, updated_at, CreatedBy, UpdatedBy, `Order`)
		SELECT RoutingCategoryID, NAME, Description, CompanyID, created_at, updated_at, CreatedBy, UpdatedBy, `Order` FROM speakintelligentRM.eng_tblTempRoutingCategory WHERE eng_tblTempRoutingCategoryID > @Last_eng_tblTempRoutingCategoryID AND eng_tblTempRoutingCategoryID <= @MAX_eng_tblTempRoutingCategoryID AND Action IS NULL;
		DELETE FROM speakintelligentRM.eng_tblTempRoutingCategory WHERE eng_tblTempRoutingCategoryID > @Last_eng_tblTempRoutingCategoryID AND eng_tblTempRoutingCategoryID <= @MAX_eng_tblTempRoutingCategoryID;
		-- add last id of data we have processed in log table, so next time when this procedure runs it will not process processed data again and will process data which came after currently processed data
		INSERT INTO tblSyncRoutingDataLog (eng_tblTempRoutingCategoryID) VALUES (@MAX_eng_tblTempRoutingCategoryID);
	END IF;

	/************** RoutingCategory Section Ends **************/

	/************** RoutingProfile Section Starts **************/

	-- get last id from log table which we have processed, so we can process all data came after that
	SELECT MAX(eng_tblTempRoutingProfileID) INTO @Last_eng_tblTempRoutingProfileID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTempRoutingProfileID = IFNULL(@Last_eng_tblTempRoutingProfileID,0);
	-- check if new data is available
	SELECT COUNT(*) INTO @V_RP_Count FROM speakintelligentRM.eng_tblTempRoutingProfile WHERE eng_tblTempRoutingProfileID > @Last_eng_tblTempRoutingProfileID;
	-- if new data is available then process it
	IF (@V_RP_Count > 0)
	THEN
		-- get last id as checkpoint so, new data which came while this process is running can not be lost
		SELECT MAX(eng_tblTempRoutingProfileID) INTO @MAX_eng_tblTempRoutingProfileID FROM speakintelligentRM.eng_tblTempRoutingProfile;

		DELETE rp FROM tblRoutingProfile AS rp INNER JOIN speakintelligentRM.eng_tblTempRoutingProfile AS e ON e.RoutingProfileID = rp.RoutingProfileID WHERE e.eng_tblTempRoutingProfileID > @Last_eng_tblTempRoutingProfileID AND e.eng_tblTempRoutingProfileID <= @MAX_eng_tblTempRoutingProfileID;
		INSERT INTO tblRoutingProfile(RoutingProfileID, NAME, Description, SelectionCode, RoutingPolicy, CompanyID, created_at, updated_at, CreatedBy, UpdatedBy, `Status`)
		SELECT RoutingProfileID, NAME, Description, SelectionCode, RoutingPolicy, CompanyID, created_at, updated_at, CreatedBy, UpdatedBy, `Status` FROM speakintelligentRM.eng_tblTempRoutingProfile WHERE eng_tblTempRoutingProfileID > @Last_eng_tblTempRoutingProfileID AND eng_tblTempRoutingProfileID <= @MAX_eng_tblTempRoutingProfileID AND Action IS NULL;
		DELETE FROM speakintelligentRM.eng_tblTempRoutingProfile WHERE eng_tblTempRoutingProfileID > @Last_eng_tblTempRoutingProfileID AND eng_tblTempRoutingProfileID <= @MAX_eng_tblTempRoutingProfileID;
		-- add last id of data we have processed in log table, so next time when this procedure runs it will not process processed data again and will process data which came after currently processed data
		INSERT INTO tblSyncRoutingDataLog (eng_tblTempRoutingProfileID) VALUES (@MAX_eng_tblTempRoutingProfileID);
	END IF;

	/************** RoutingProfile Section Ends **************/

	/************** RoutingProfileCategory Section Starts **************/

	-- get last id from log table which we have processed, so we can process all data came after that
	SELECT MAX(eng_tblTempRoutingProfileCategoryID) INTO @Last_eng_tblTempRoutingProfileCategoryID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTempRoutingProfileCategoryID = IFNULL(@Last_eng_tblTempRoutingProfileCategoryID,0);
	-- check if new data is available
	SELECT COUNT(*) INTO @V_RPC_Count FROM speakintelligentRM.eng_tblTempRoutingProfileCategory WHERE eng_tblTempRoutingProfileCategoryID > @Last_eng_tblTempRoutingProfileCategoryID;
	-- if new data is available then process it
	IF (@V_RPC_Count > 0)
	THEN
		-- get last id as checkpoint so, new data which came while this process is running can not be lost
		SELECT MAX(eng_tblTempRoutingProfileCategoryID) INTO @MAX_eng_tblTempRoutingProfileCategoryID FROM speakintelligentRM.eng_tblTempRoutingProfileCategory;

		DELETE rpc FROM tblRoutingProfileCategory AS rpc INNER JOIN speakintelligentRM.eng_tblTempRoutingProfileCategory AS e ON e.RoutingProfileCategoryID = rpc.RoutingProfileCategoryID WHERE e.eng_tblTempRoutingProfileCategoryID > @Last_eng_tblTempRoutingProfileCategoryID AND e.eng_tblTempRoutingProfileCategoryID <= @MAX_eng_tblTempRoutingProfileCategoryID;
		INSERT INTO tblRoutingProfileCategory(RoutingProfileCategoryID, RoutingProfileID, RoutingCategoryID, `Order`, updated_at, created_at)
		SELECT RoutingProfileCategoryID, RoutingProfileID, RoutingCategoryID, `Order`, updated_at, created_at FROM speakintelligentRM.eng_tblTempRoutingProfileCategory WHERE eng_tblTempRoutingProfileCategoryID > @Last_eng_tblTempRoutingProfileCategoryID AND eng_tblTempRoutingProfileCategoryID <= @MAX_eng_tblTempRoutingProfileCategoryID AND Action IS NULL;
		DELETE FROM speakintelligentRM.eng_tblTempRoutingProfileCategory WHERE eng_tblTempRoutingProfileCategoryID > @Last_eng_tblTempRoutingProfileCategoryID AND eng_tblTempRoutingProfileCategoryID <= @MAX_eng_tblTempRoutingProfileCategoryID;
		-- add last id of data we have processed in log table, so next time when this procedure runs it will not process processed data again and will process data which came after currently processed data
		INSERT INTO tblSyncRoutingDataLog (eng_tblTempRoutingProfileCategoryID) VALUES (@MAX_eng_tblTempRoutingProfileCategoryID);
	END IF;

	/************** RoutingProfileCategory Section Ends **************/

	/************** RoutingProfileToCustomer Section Starts **************/

	-- get last id from log table which we have processed, so we can process all data came after that
	SELECT MAX(eng_tblTempRoutingProfileToCustomerID) INTO @Last_eng_tblTempRoutingProfileToCustomerID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTempRoutingProfileToCustomerID = IFNULL(@Last_eng_tblTempRoutingProfileToCustomerID,0);
	-- check if new data is available
	SELECT COUNT(*) INTO @V_RPTC_Count FROM speakintelligentRM.eng_tblTempRoutingProfileToCustomer WHERE eng_tblTempRoutingProfileToCustomerID > @Last_eng_tblTempRoutingProfileToCustomerID;
	-- if new data is available then process it
	IF (@V_RPTC_Count > 0)
	THEN
		-- get last id as checkpoint so, new data which came while this process is running can not be lost
		SELECT MAX(eng_tblTempRoutingProfileToCustomerID) INTO @MAX_eng_tblTempRoutingProfileToCustomerID FROM speakintelligentRM.eng_tblTempRoutingProfileToCustomer;

		DELETE rptc FROM tblRoutingProfileToCustomer AS rptc INNER JOIN speakintelligentRM.eng_tblTempRoutingProfileToCustomer AS e ON e.RoutingProfileToCustomerID = rptc.RoutingProfileToCustomerID WHERE e.eng_tblTempRoutingProfileToCustomerID > @Last_eng_tblTempRoutingProfileToCustomerID AND e.eng_tblTempRoutingProfileToCustomerID <= @MAX_eng_tblTempRoutingProfileToCustomerID;
		INSERT INTO tblRoutingProfileToCustomer(RoutingProfileToCustomerID, RoutingProfileID, AccountID, TrunkID, ServiceID, AccountServiceID, created_at, updated_at)
		SELECT RoutingProfileToCustomerID, RoutingProfileID, AccountID, TrunkID, ServiceID, AccountServiceID, created_at, updated_at FROM speakintelligentRM.eng_tblTempRoutingProfileToCustomer WHERE eng_tblTempRoutingProfileToCustomerID > @Last_eng_tblTempRoutingProfileToCustomerID AND eng_tblTempRoutingProfileToCustomerID <= @MAX_eng_tblTempRoutingProfileToCustomerID AND Action IS NULL;
		DELETE FROM speakintelligentRM.eng_tblTempRoutingProfileToCustomer WHERE eng_tblTempRoutingProfileToCustomerID > @Last_eng_tblTempRoutingProfileToCustomerID AND eng_tblTempRoutingProfileToCustomerID <= @MAX_eng_tblTempRoutingProfileToCustomerID;
		-- add last id of data we have processed in log table, so next time when this procedure runs it will not process processed data again and will process data which came after currently processed data
		INSERT INTO tblSyncRoutingDataLog (eng_tblTempRoutingProfileToCustomerID) VALUES (@MAX_eng_tblTempRoutingProfileToCustomerID);
	END IF;

	/************** RoutingProfileToCustomer Section Ends **************/

	/************** VendorConnection Section Starts **************/

	-- get last id from log table which we have processed, so we can process all data came after that
	SELECT MAX(eng_tblTempVendorConnectionID) INTO @Last_eng_tblTempVendorConnectionID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTempVendorConnectionID = IFNULL(@Last_eng_tblTempVendorConnectionID,0);
	-- check if new data is available
	SELECT COUNT(*) INTO @V_VC_Count FROM speakintelligentRM.eng_tblTempVendorConnection WHERE eng_tblTempVendorConnectionID > @Last_eng_tblTempVendorConnectionID;
	-- if new data is available then process it
	IF (@V_VC_Count > 0)
	THEN
		-- get last id as checkpoint so, new data which came while this process is running can not be lost
		SELECT MAX(eng_tblTempVendorConnectionID) INTO @MAX_eng_tblTempVendorConnectionID FROM speakintelligentRM.eng_tblTempVendorConnection;

		DELETE vc FROM tblVendorConnection AS vc INNER JOIN speakintelligentRM.eng_tblTempVendorConnection AS e ON e.VendorConnectionID = vc.VendorConnectionID WHERE e.eng_tblTempVendorConnectionID > @Last_eng_tblTempVendorConnectionID AND e.eng_tblTempVendorConnectionID <= @MAX_eng_tblTempVendorConnectionID;
		INSERT INTO tblVendorConnection(VendorConnectionID, AccountId, RateTypeID, CompanyID, NAME, DIDCategoryID, Active, RateTableID, TrunkID, CLIRule, CLDRule, CallPrefix, IP, PORT, Username, PASSWORD, created_at, updated_at, created_by, updated_by, PrefixCDR, SipHeader, AuthenticationMode, Location)
		SELECT VendorConnectionID, AccountId, RateTypeID, CompanyID, NAME, DIDCategoryID, Active, RateTableID, TrunkID, CLIRule, CLDRule, CallPrefix, IP, PORT, Username, PASSWORD, created_at, updated_at, created_by, updated_by, PrefixCDR, SipHeader, AuthenticationMode, Location FROM speakintelligentRM.eng_tblTempVendorConnection WHERE eng_tblTempVendorConnectionID > @Last_eng_tblTempVendorConnectionID AND eng_tblTempVendorConnectionID <= @MAX_eng_tblTempVendorConnectionID AND Action IS NULL;
		DELETE FROM speakintelligentRM.eng_tblTempVendorConnection WHERE eng_tblTempVendorConnectionID > @Last_eng_tblTempVendorConnectionID AND eng_tblTempVendorConnectionID <= @MAX_eng_tblTempVendorConnectionID;
		-- add last id of data we have processed in log table, so next time when this procedure runs it will not process processed data again and will process data which came after currently processed data
		INSERT INTO tblSyncRoutingDataLog (eng_tblTempVendorConnectionID) VALUES (@MAX_eng_tblTempVendorConnectionID);
	END IF;

	/************** VendorConnection Section Ends **************/

	/************** Reseller Section Starts **************/

	-- get last id from log table which we have processed, so we can process all data came after that
	SELECT MAX(eng_tblTempResellerID) INTO @Last_eng_tblTempResellerID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTempResellerID = IFNULL(@Last_eng_tblTempResellerID,0);
	-- check if new data is available
	SELECT COUNT(*) INTO @V_RSL_Count FROM speakintelligentRM.eng_tblTempReseller WHERE eng_tblTempResellerID > @Last_eng_tblTempResellerID;
	-- if new data is available then process it
	IF (@V_RSL_Count > 0)
	THEN
		-- get last id as checkpoint so, new data which came while this process is running can not be lost
		SELECT MAX(eng_tblTempResellerID) INTO @MAX_eng_tblTempResellerID FROM speakintelligentRM.eng_tblTempReseller;

		DELETE rsl FROM tblReseller AS rsl INNER JOIN speakintelligentRM.eng_tblTempReseller AS e ON e.ResellerID = rsl.ResellerID WHERE e.eng_tblTempResellerID > @Last_eng_tblTempResellerID AND e.eng_tblTempResellerID <= @MAX_eng_tblTempResellerID;
		INSERT INTO tblReseller(ResellerID, ResellerName, CompanyID, ChildCompanyID, AccountID, FirstName, LastName, Email,PASSWORD, STATUS, AllowWhiteLabel, created_at,created_by,updated_at,updated_by)
		SELECT ResellerID, ResellerName, CompanyID, ChildCompanyID, AccountID, FirstName, LastName, Email,PASSWORD, STATUS, AllowWhiteLabel, created_at,created_by,updated_at,updated_by FROM speakintelligentRM.eng_tblTempReseller WHERE eng_tblTempResellerID > @Last_eng_tblTempResellerID AND eng_tblTempResellerID <= @MAX_eng_tblTempResellerID AND Action IS NULL;
		DELETE FROM speakintelligentRM.eng_tblTempReseller WHERE eng_tblTempResellerID > @Last_eng_tblTempResellerID AND eng_tblTempResellerID <= @MAX_eng_tblTempResellerID;
		-- add last id of data we have processed in log table, so next time when this procedure runs it will not process processed data again and will process data which came after currently processed data
		INSERT INTO tblSyncRoutingDataLog (eng_tblTempResellerID) VALUES (@MAX_eng_tblTempResellerID);
	END IF;

	/************** Reseller Section Ends **************/

	/************** Currency Section Starts **************/

	-- get last id from log table which we have processed, so we can process all data came after that
	SELECT MAX(eng_tblTempCurrencyID) INTO @Last_eng_tblTempCurrencyID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTempCurrencyID = IFNULL(@Last_eng_tblTempCurrencyID,0);
	-- check if new data is available
	SELECT COUNT(*) INTO @V_CUR_Count FROM speakintelligentRM.eng_tblTempCurrency WHERE eng_tblTempCurrencyID > @Last_eng_tblTempCurrencyID;
	-- if new data is available then process it
	IF (@V_CUR_Count > 0)
	THEN
		-- get last id as checkpoint so, new data which came while this process is running can not be lost
		SELECT MAX(eng_tblTempCurrencyID) INTO @MAX_eng_tblTempCurrencyID FROM speakintelligentRM.eng_tblTempCurrency;

		DELETE cur FROM tblCurrency AS cur INNER JOIN speakintelligentRM.eng_tblTempCurrency AS e ON e.CurrencyId = cur.CurrencyId WHERE e.eng_tblTempCurrencyID > @Last_eng_tblTempCurrencyID AND e.eng_tblTempCurrencyID <= @MAX_eng_tblTempCurrencyID;
		INSERT INTO tblCurrency(CurrencyId, CompanyId, CODE, Symbol, Description, STATUS, created_at, CreatedBy,updated_at, ModifiedBy)
		SELECT CurrencyId, CompanyId, CODE, Symbol, Description, STATUS, created_at, CreatedBy,updated_at, ModifiedBy FROM speakintelligentRM.eng_tblTempCurrency WHERE eng_tblTempCurrencyID > @Last_eng_tblTempCurrencyID AND eng_tblTempCurrencyID <= @MAX_eng_tblTempCurrencyID AND Action IS NULL;
		DELETE FROM speakintelligentRM.eng_tblTempCurrency WHERE eng_tblTempCurrencyID > @Last_eng_tblTempCurrencyID AND eng_tblTempCurrencyID <= @MAX_eng_tblTempCurrencyID;
		-- add last id of data we have processed in log table, so next time when this procedure runs it will not process processed data again and will process data which came after currently processed data
		INSERT INTO tblSyncRoutingDataLog (eng_tblTempCurrencyID) VALUES (@MAX_eng_tblTempCurrencyID);
	END IF;

	/************** Currency Section Ends **************/

	/************** CurrencyConversion Section Starts **************/

	-- get last id from log table which we have processed, so we can process all data came after that
	SELECT MAX(eng_tblTempCurrencyConversionID) INTO @Last_eng_tblTempCurrencyConversionID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTempCurrencyConversionID = IFNULL(@Last_eng_tblTempCurrencyConversionID,0);
	-- check if new data is available
	SELECT COUNT(*) INTO @V_CC_Count FROM speakintelligentRM.eng_tblTempCurrencyConversion WHERE eng_tblTempCurrencyConversionID > @Last_eng_tblTempCurrencyConversionID;
	-- if new data is available then process it
	IF (@V_CC_Count > 0)
	THEN
		-- get last id as checkpoint so, new data which came while this process is running can not be lost
		SELECT MAX(eng_tblTempCurrencyConversionID) INTO @MAX_eng_tblTempCurrencyConversionID FROM speakintelligentRM.eng_tblTempCurrencyConversion;

		DELETE cc FROM tblCurrencyConversion AS cc INNER JOIN speakintelligentRM.eng_tblTempCurrencyConversion AS e ON e.ConversionID = cc.ConversionID WHERE e.eng_tblTempCurrencyConversionID > @Last_eng_tblTempCurrencyConversionID AND e.eng_tblTempCurrencyConversionID <= @MAX_eng_tblTempCurrencyConversionID;
		INSERT INTO tblCurrencyConversion(ConversionID, CompanyID, CurrencyID, VALUE,  created_at, CreatedBy,updated_at, ModifiedBy,EffectiveDate)
		SELECT ConversionID, CompanyID, CurrencyID, VALUE,  created_at, CreatedBy,updated_at, ModifiedBy,EffectiveDate FROM speakintelligentRM.eng_tblTempCurrencyConversion WHERE eng_tblTempCurrencyConversionID > @Last_eng_tblTempCurrencyConversionID AND eng_tblTempCurrencyConversionID <= @MAX_eng_tblTempCurrencyConversionID AND Action IS NULL;
		DELETE FROM speakintelligentRM.eng_tblTempCurrencyConversion WHERE eng_tblTempCurrencyConversionID > @Last_eng_tblTempCurrencyConversionID AND eng_tblTempCurrencyConversionID <= @MAX_eng_tblTempCurrencyConversionID;
		-- add last id of data we have processed in log table, so next time when this procedure runs it will not process processed data again and will process data which came after currently processed data
		INSERT INTO tblSyncRoutingDataLog (eng_tblTempCurrencyConversionID) VALUES (@MAX_eng_tblTempCurrencyConversionID);
	END IF;

	/************** CurrencyConversion Section Ends **************/

	/************** VendorTimezone Section Starts **************/

	-- get last id from log table which we have processed, so we can process all data came after that
	SELECT MAX(eng_tblTempVendorTimezoneID) INTO @Last_eng_tblTempVendorTimezoneID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTempVendorTimezoneID = IFNULL(@Last_eng_tblTempVendorTimezoneID,0);
	-- check if new data is available
	SELECT COUNT(*) INTO @V_VT_Count FROM speakintelligentRM.eng_tblTempVendorTimezone WHERE eng_tblTempVendorTimezoneID > @Last_eng_tblTempVendorTimezoneID;
	-- if new data is available then process it
	IF (@V_VT_Count > 0)
	THEN
		-- get last id as checkpoint so, new data which came while this process is running can not be lost
		SELECT MAX(eng_tblTempVendorTimezoneID) INTO @MAX_eng_tblTempVendorTimezoneID FROM speakintelligentRM.eng_tblTempVendorTimezone;

		DELETE vt FROM tblVendorTimezone AS vt INNER JOIN speakintelligentRM.eng_tblTempVendorTimezone AS e ON e.VendorTimezoneID = vt.VendorTimezoneID WHERE e.eng_tblTempVendorTimezoneID > @Last_eng_tblTempVendorTimezoneID AND e.eng_tblTempVendorTimezoneID <= @MAX_eng_tblTempVendorTimezoneID;
		INSERT INTO tblVendorTimezone(VendorTimezoneID, TYPE, Country, TimeZoneID, VendorID, FromTime, ToTime, DaysOfWeek,DaysOfMonth, Months, ApplyIF, STATUS, created_at, created_by,updated_at,updated_by)
		SELECT VendorTimezoneID, TYPE, Country, TimeZoneID, VendorID, FromTime, ToTime, DaysOfWeek,DaysOfMonth, Months, ApplyIF, STATUS, created_at, created_by,updated_at,updated_by FROM speakintelligentRM.eng_tblTempVendorTimezone WHERE eng_tblTempVendorTimezoneID > @Last_eng_tblTempVendorTimezoneID AND eng_tblTempVendorTimezoneID <= @MAX_eng_tblTempVendorTimezoneID AND Action IS NULL;
		DELETE FROM speakintelligentRM.eng_tblTempVendorTimezone WHERE eng_tblTempVendorTimezoneID > @Last_eng_tblTempVendorTimezoneID AND eng_tblTempVendorTimezoneID <= @MAX_eng_tblTempVendorTimezoneID;
		-- add last id of data we have processed in log table, so next time when this procedure runs it will not process processed data again and will process data which came after currently processed data
		INSERT INTO tblSyncRoutingDataLog (eng_tblTempVendorTimezoneID) VALUES (@MAX_eng_tblTempVendorTimezoneID);
	END IF;

	/************** VendorTimezone Section Ends **************/

	/************** Timezones Section Starts **************/

	-- get last id from log table which we have processed, so we can process all data came after that
	SELECT MAX(eng_tblTempTimezonesID) INTO @Last_eng_tblTempTimezonesID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTempTimezonesID = IFNULL(@Last_eng_tblTempTimezonesID,0);
	-- check if new data is available
	SELECT COUNT(*) INTO @V_TZ_Count FROM speakintelligentRM.eng_tblTempTimezones WHERE eng_tblTempTimezonesID > @Last_eng_tblTempTimezonesID;
	-- if new data is available then process it
	IF (@V_TZ_Count > 0)
	THEN
		-- get last id as checkpoint so, new data which came while this process is running can not be lost
		SELECT MAX(eng_tblTempTimezonesID) INTO @MAX_eng_tblTempTimezonesID FROM speakintelligentRM.eng_tblTempTimezones;

		DELETE tz FROM tblTimezones AS tz INNER JOIN speakintelligentRM.eng_tblTempTimezones AS e ON e.TimezonesID = tz.TimezonesID WHERE e.eng_tblTempTimezonesID > @Last_eng_tblTempTimezonesID AND e.eng_tblTempTimezonesID <= @MAX_eng_tblTempTimezonesID;
		INSERT INTO tblTimezones(TimezonesID, Title, FromTime, ToTime, DaysOfWeek, DaysOfMonth, Months, ApplyIF,STATUS, created_at, created_by,updated_at,updated_by)
		SELECT TimezonesID, Title, FromTime, ToTime, DaysOfWeek, DaysOfMonth, Months, ApplyIF,STATUS, created_at, created_by,updated_at,updated_by FROM speakintelligentRM.eng_tblTempTimezones WHERE eng_tblTempTimezonesID > @Last_eng_tblTempTimezonesID AND eng_tblTempTimezonesID <= @MAX_eng_tblTempTimezonesID AND Action IS NULL;
		DELETE FROM speakintelligentRM.eng_tblTempTimezones WHERE eng_tblTempTimezonesID > @Last_eng_tblTempTimezonesID AND eng_tblTempTimezonesID <= @MAX_eng_tblTempTimezonesID;
		-- add last id of data we have processed in log table, so next time when this procedure runs it will not process processed data again and will process data which came after currently processed data
		INSERT INTO tblSyncRoutingDataLog (eng_tblTempTimezonesID) VALUES (@MAX_eng_tblTempTimezonesID);
	END IF;

	/************** Timezones Section Ends **************/

	/*************************************************************************************************************************************************/
	/** ---------------------- This whole procedure is rewritten by @vasimseta at @2020-01-28 to fix the missing data issue. ---------------------- **/
	/** ---------- following 3 query (tblCLIRateTable,tblAccountServicePackage,tblTaxRate) has little different approach than above all. ---------- **/
	/** Above all using "NULL" value as insert/update action and below mentioned 3 uses "I" and "U" value as insert and update action respectively. **/
	/*************************************************************************************************************************************************/

	/************** CLIRateTable Section Starts **************/

	-- get last id from log table which we have processed, so we can process all data came after that
	SELECT MAX(eng_tblTempCLIRateTableID) INTO @Last_eng_tblTempCLIRateTableID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTempCLIRateTableID = IFNULL(@Last_eng_tblTempCLIRateTableID,0);
	-- check if new data is available
	SELECT COUNT(*) INTO @V_CRT_Count FROM speakintelligentRM.eng_tblTempCLIRateTable WHERE eng_tblTempCLIRateTableID > @Last_eng_tblTempCLIRateTableID;
	-- if new data is available then process it
	IF (@V_CRT_Count > 0)
	THEN
		-- get last id as checkpoint so, new data which came while this process is running can not be lost
		SELECT MAX(eng_tblTempCLIRateTableID) INTO @MAX_eng_tblTempCLIRateTableID FROM speakintelligentRM.eng_tblTempCLIRateTable;

		DELETE crt FROM tblCLIRateTable AS crt INNER JOIN speakintelligentRM.eng_tblTempCLIRateTable AS e ON e.CLIRateTableID = crt.CLIRateTableID WHERE e.eng_tblTempCLIRateTableID > @Last_eng_tblTempCLIRateTableID AND e.eng_tblTempCLIRateTableID <= @MAX_eng_tblTempCLIRateTableID;
		INSERT INTO tblCLIRateTable(CLIRateTableID, AccountServicePackageID, CompanyID, AccountID, CLI, AccessDiscountPlanID, RateTableID, TerminationRateTableID, TerminationDiscountPlanID, CountryID, NumberStartDate, NumberEndDate, ServiceID, AccountServiceID, PackageID, PackageRateTableID, STATUS, `Prefix`, PrefixWithoutCountry, ContractID, City, Tariff, DIDCategoryID, VendorID, NoType, SpecialRateTableID, SpecialTerminationRateTableID)
		SELECT CLIRateTableID, AccountServicePackageID, CompanyID, AccountID, CLI, AccessDiscountPlanID, RateTableID, TerminationRateTableID, TerminationDiscountPlanID, CountryID, NumberStartDate, NumberEndDate, ServiceID, AccountServiceID, PackageID, PackageRateTableID, STATUS, `Prefix`, PrefixWithoutCountry, ContractID, City, Tariff, DIDCategoryID, VendorID, NoType, SpecialRateTableID, SpecialTerminationRateTableID FROM speakintelligentRM.eng_tblTempCLIRateTable WHERE eng_tblTempCLIRateTableID > @Last_eng_tblTempCLIRateTableID AND eng_tblTempCLIRateTableID <= @MAX_eng_tblTempCLIRateTableID AND (ACTION = 'I' OR ACTION = 'U');
		DELETE FROM speakintelligentRM.eng_tblTempCLIRateTable WHERE eng_tblTempCLIRateTableID > @Last_eng_tblTempCLIRateTableID AND eng_tblTempCLIRateTableID <= @MAX_eng_tblTempCLIRateTableID;
		-- add last id of data we have processed in log table, so next time when this procedure runs it will not process processed data again and will process data which came after currently processed data
		INSERT INTO tblSyncRoutingDataLog (eng_tblTempCLIRateTableID) VALUES (@MAX_eng_tblTempCLIRateTableID);
	END IF;

	/************** CLIRateTable Section Ends **************/

	/************** AccountServicePackage Section Starts **************/

	-- get last id from log table which we have processed, so we can process all data came after that
	SELECT MAX(eng_tblAccountServicePackageID) INTO @Last_eng_tblAccountServicePackageID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblAccountServicePackageID = IFNULL(@Last_eng_tblAccountServicePackageID,0);
	-- check if new data is available
	SELECT COUNT(*) INTO @V_ASP_Count FROM speakintelligentRM.eng_tblAccountServicePackage WHERE eng_tblAccountServicePackageID > @Last_eng_tblAccountServicePackageID;
	-- if new data is available then process it
	IF (@V_ASP_Count > 0)
	THEN
		-- get last id as checkpoint so, new data which came while this process is running can not be lost
		SELECT MAX(eng_tblAccountServicePackageID) INTO @MAX_eng_tblAccountServicePackageID FROM speakintelligentRM.eng_tblAccountServicePackage;

		DELETE asp FROM tblAccountServicePackage AS asp INNER JOIN speakintelligentRM.eng_tblAccountServicePackage AS e ON e.AccountServicePackageID = asp.AccountServicePackageID WHERE e.eng_tblAccountServicePackageID > @Last_eng_tblAccountServicePackageID AND e.eng_tblAccountServicePackageID <= @MAX_eng_tblAccountServicePackageID;
		INSERT INTO tblAccountServicePackage(AccountServicePackageID, AccountID, AccountServiceID, CompanyID, PackageId, RateTableID, created_at, updated_at, created_by, updated_by, PackageDiscountPlanID, PackageStartDate, PackageEndDate, ContractID, STATUS, ServiceID, SpecialPackageRateTableID, VendorID, NAME)
		SELECT AccountServicePackageID, AccountID, AccountServiceID, CompanyID, PackageId, RateTableID, created_at, updated_at, created_by, updated_by, PackageDiscountPlanID, PackageStartDate, PackageEndDate, ContractID, STATUS, ServiceID, SpecialPackageRateTableID, VendorID, NAME FROM speakintelligentRM.eng_tblAccountServicePackage WHERE eng_tblAccountServicePackageID > @Last_eng_tblAccountServicePackageID AND eng_tblAccountServicePackageID <= @MAX_eng_tblAccountServicePackageID AND (ACTION = 'I' OR ACTION = 'U');
		DELETE FROM speakintelligentRM.eng_tblAccountServicePackage WHERE eng_tblAccountServicePackageID > @Last_eng_tblAccountServicePackageID AND eng_tblAccountServicePackageID <= @MAX_eng_tblAccountServicePackageID;
		-- add last id of data we have processed in log table, so next time when this procedure runs it will not process processed data again and will process data which came after currently processed data
		INSERT INTO tblSyncRoutingDataLog (eng_tblAccountServicePackageID) VALUES (@MAX_eng_tblAccountServicePackageID);
	END IF;

	/************** AccountServicePackage Section Ends **************/

	/************** TaxRate Section Starts **************/

	-- get last id from log table which we have processed, so we can process all data came after that
	SELECT MAX(eng_tblTaxRateID) INTO @Last_eng_tblTaxRateID FROM tblSyncRoutingDataLog;
	SET @Last_eng_tblTaxRateID = IFNULL(@Last_eng_tblTaxRateID,0);
	-- check if new data is available
	SELECT COUNT(*) INTO @V_TR_Count FROM speakintelligentRM.eng_tblTaxRate WHERE eng_tblTaxRateID > @Last_eng_tblTaxRateID;
	-- if new data is available then process it
	IF (@V_TR_Count > 0)
	THEN
		-- get last id as checkpoint so, new data which came while this process is running can not be lost
		SELECT MAX(eng_tblTaxRateID) INTO @MAX_eng_tblTaxRateID FROM speakintelligentRM.eng_tblTaxRate;

		DELETE tr FROM tblTaxRate AS tr INNER JOIN speakintelligentRM.eng_tblTaxRate AS e ON e.TaxRateId = tr.TaxRateId WHERE e.eng_tblTaxRateID > @Last_eng_tblTaxRateID AND e.eng_tblTaxRateID <= @MAX_eng_tblTaxRateID;
		INSERT INTO tblTaxRate(TaxRateId, CompanyId, Title, Amount, TaxType, FlatStatus, Country, DutchProvider, DutchFoundation, STATUS, VATCode, created_at, created_by, updated_at, updated_by)
		SELECT TaxRateId, CompanyId, Title, Amount, TaxType, FlatStatus, Country, DutchProvider, DutchFoundation, STATUS, VATCode, created_at, created_by, updated_at, updated_by FROM speakintelligentRM.eng_tblTaxRate WHERE eng_tblTaxRateID > @Last_eng_tblTaxRateID AND eng_tblTaxRateID <= @MAX_eng_tblTaxRateID AND (ACTION = 'I' OR ACTION = 'U');
		DELETE FROM speakintelligentRM.eng_tblTaxRate WHERE eng_tblTaxRateID > @Last_eng_tblTaxRateID AND eng_tblTaxRateID <= @MAX_eng_tblTaxRateID;
		-- add last id of data we have processed in log table, so next time when this procedure runs it will not process processed data again and will process data which came after currently processed data
		INSERT INTO tblSyncRoutingDataLog (eng_tblTaxRateID) VALUES (@MAX_eng_tblTaxRateID);
	END IF;

	/************** TaxRate Section Ends **************/

	call prc_APIRoutingDataPerRow();

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END//
DELIMITER ;















use `speakintelligentRM`; -- app server


DROP TABLE IF EXISTS `eng_tblTempAccount`;
CREATE TABLE IF NOT EXISTS `eng_tblTempAccount` engine=CONNECT table_type=MYSQL
connection='mysql://cluster-user:ieQ8b3&CGjKT@web-db.neon.colo.local/speakintelligentRM/eng_tblTempAccount';

DROP TABLE IF EXISTS `eng_tblTempRateTable`;
CREATE TABLE IF NOT EXISTS `eng_tblTempRateTable` engine=CONNECT table_type=MYSQL
connection='mysql://cluster-user:ieQ8b3&CGjKT@web-db.neon.colo.local/speakintelligentRM/eng_tblTempRateTable';

DROP TABLE IF EXISTS `eng_tblTempRoutingCategory`;
CREATE TABLE IF NOT EXISTS `eng_tblTempRoutingCategory` engine=CONNECT table_type=MYSQL
connection='mysql://cluster-user:ieQ8b3&CGjKT@web-db.neon.colo.local/speakintelligentRM/eng_tblTempRoutingCategory';

DROP TABLE IF EXISTS `eng_tblTempRoutingProfile`;
CREATE TABLE IF NOT EXISTS `eng_tblTempRoutingProfile` engine=CONNECT table_type=MYSQL
connection='mysql://cluster-user:ieQ8b3&CGjKT@web-db.neon.colo.local/speakintelligentRM/eng_tblTempRoutingProfile';

DROP TABLE IF EXISTS `eng_tblTempRoutingProfileCategory`;
CREATE TABLE IF NOT EXISTS `eng_tblTempRoutingProfileCategory` engine=CONNECT table_type=MYSQL
connection='mysql://cluster-user:ieQ8b3&CGjKT@web-db.neon.colo.local/speakintelligentRM/eng_tblTempRoutingProfileCategory';

DROP TABLE IF EXISTS `eng_tblTempRoutingProfileToCustomer`;
CREATE TABLE IF NOT EXISTS `eng_tblTempRoutingProfileToCustomer` engine=CONNECT table_type=MYSQL
connection='mysql://cluster-user:ieQ8b3&CGjKT@web-db.neon.colo.local/speakintelligentRM/eng_tblTempRoutingProfileToCustomer';

DROP TABLE IF EXISTS `eng_tblTempVendorConnection`;
CREATE TABLE IF NOT EXISTS `eng_tblTempVendorConnection` engine=CONNECT table_type=MYSQL
connection='mysql://cluster-user:ieQ8b3&CGjKT@web-db.neon.colo.local/speakintelligentRM/eng_tblTempVendorConnection';

DROP TABLE IF EXISTS `eng_tblTempReseller`;
CREATE TABLE IF NOT EXISTS `eng_tblTempReseller` engine=CONNECT table_type=MYSQL
connection='mysql://cluster-user:ieQ8b3&CGjKT@web-db.neon.colo.local/speakintelligentRM/eng_tblTempReseller';

DROP TABLE IF EXISTS `eng_tblTempCurrency`;
CREATE TABLE IF NOT EXISTS `eng_tblTempCurrency` engine=CONNECT table_type=MYSQL
connection='mysql://cluster-user:ieQ8b3&CGjKT@web-db.neon.colo.local/speakintelligentRM/eng_tblTempCurrency';

DROP TABLE IF EXISTS `eng_tblTempCurrencyConversion`;
CREATE TABLE IF NOT EXISTS `eng_tblTempCurrencyConversion` engine=CONNECT table_type=MYSQL
connection='mysql://cluster-user:ieQ8b3&CGjKT@web-db.neon.colo.local/speakintelligentRM/eng_tblTempCurrencyConversion';

DROP TABLE IF EXISTS `eng_tblTempVendorTimezone`;
CREATE TABLE IF NOT EXISTS `eng_tblTempVendorTimezone` engine=CONNECT table_type=MYSQL
connection='mysql://cluster-user:ieQ8b3&CGjKT@web-db.neon.colo.local/speakintelligentRM/eng_tblTempVendorTimezone';

DROP TABLE IF EXISTS `eng_tblTempTimezones`;
CREATE TABLE IF NOT EXISTS `eng_tblTempTimezones` engine=CONNECT table_type=MYSQL
connection='mysql://cluster-user:ieQ8b3&CGjKT@web-db.neon.colo.local/speakintelligentRM/eng_tblTempTimezones';

DROP TABLE IF EXISTS `eng_tblTempCLIRateTable`;
CREATE TABLE IF NOT EXISTS `eng_tblTempCLIRateTable` engine=CONNECT table_type=MYSQL
connection='mysql://cluster-user:ieQ8b3&CGjKT@web-db.neon.colo.local/speakintelligentRM/eng_tblTempCLIRateTable';

DROP TABLE IF EXISTS `eng_tblAccountServicePackage`;
CREATE TABLE IF NOT EXISTS `eng_tblAccountServicePackage` engine=CONNECT table_type=MYSQL
connection='mysql://cluster-user:ieQ8b3&CGjKT@web-db.neon.colo.local/speakintelligentRM/eng_tblAccountServicePackage';

DROP TABLE IF EXISTS `eng_tblTaxRate`;
CREATE TABLE IF NOT EXISTS `eng_tblTaxRate` engine=CONNECT table_type=MYSQL
connection='mysql://cluster-user:ieQ8b3&CGjKT@web-db.neon.colo.local/speakintelligentRM/eng_tblTaxRate';

DROP TABLE IF EXISTS `eng_tblRateTableDIDRate`;
CREATE TABLE IF NOT EXISTS `eng_tblRateTableDIDRate` engine=CONNECT table_type=MYSQL
connection='mysql://cluster-user:ieQ8b3&CGjKT@web-db.neon.colo.local/speakintelligentRM/eng_tblRateTableDIDRate';

DROP TABLE IF EXISTS `eng_tblRateTablePKGRate`;
CREATE TABLE IF NOT EXISTS `eng_tblRateTablePKGRate` engine=CONNECT table_type=MYSQL
connection='mysql://cluster-user:ieQ8b3&CGjKT@web-db.neon.colo.local/speakintelligentRM/eng_tblRateTablePKGRate';

DROP TABLE IF EXISTS `eng_tblTempRateTableRate`;
CREATE TABLE IF NOT EXISTS `eng_tblTempRateTableRate` engine=CONNECT table_type=MYSQL
connection='mysql://cluster-user:ieQ8b3&CGjKT@web-db.neon.colo.local/speakintelligentRM/eng_tblTempRateTableRate';

DROP TABLE IF EXISTS `eng_tblTempCustomerRateTableRate`;
CREATE TABLE IF NOT EXISTS `eng_tblTempCustomerRateTableRate` engine=CONNECT table_type=MYSQL
connection='mysql://cluster-user:ieQ8b3&CGjKT@web-db.neon.colo.local/speakintelligentRM/eng_tblTempCustomerRateTableRate';