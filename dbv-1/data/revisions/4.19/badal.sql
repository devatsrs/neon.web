Use Ratemanagement3;

INSERT INTO `tblIntegration` (`CompanyId`, `Title`, `Slug`, `ParentID`) VALUES ('1', 'FastPay', 'fastpay', '4');


INSERT INTO `tblGateway` (`Title`, `Name`, `Status`, `CreatedBy`, `created_at`) VALUES ('FTP VENDOR', 'FTPVENDOR', '1', 'RateManagementSystem', '2019-01-31 12:41:23');

INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (17, 'Rate Format', 'RateFormat', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (17, 'Authentication Rule', 'NameFormat', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (17, 'CDR ReRate', 'RateCDR', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (17, 'Rerate Method', 'RateMethod', 1, '2017-06-09 00:00:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (17, 'Rerate Method Value', 'SpecifyRate', 1, '2017-06-09 00:00:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (17, 'CLI Translation Rule', 'CLITranslationRule', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (17, 'FTP Host IP', 'host', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (17, 'Protocol Type', 'protocol_type', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (17, 'Port', 'port', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (17, 'SSL', 'ssl', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (17, 'Passive Mode', 'passive_mode', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (17, 'User Name', 'username', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (17, 'Password', 'password', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (17, 'Key', 'key', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (17, 'Key Phrase', 'keyphrase', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (17, 'FTP CDR Download Path', 'cdr_folder', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (17, 'CLD Translation Rule', 'CLDTranslationRule', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (17, 'Billing Time', 'BillingTime', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (17, 'Prefix Translation Rule', 'PrefixTranslationRule', 1, '2017-06-09 00:00:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (17, 'File Name Rule', 'FileNameRule', 1, '2017-06-09 00:00:00', 'RateManagementSystem', NULL, NULL);

INSERT INTO `tblFileUploadTemplateType` (`TemplateType`, `Title`, `UploadDir`, `created_at`, `created_by`, `Status`) VALUES ('VENDORFTPCDR', 'Vendor FTP CDR', 'CDR_UPLOAD', '2018-11-30 16:28:34', NULL, 1);

INSERT INTO `tblCronJobCommand` (`CompanyID`, `GatewayID`, `Title`, `Command`, `Settings`, `Status`, `created_at`, `created_by`) VALUES (1, 17, 'Download FTP VENDOR CDR', 'ftpvendoraccountusage', '[[{"title":"Files Max Proccess","type":"text","value":"","name":"FilesMaxProccess"},{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"}]]', 1, '2017-04-17 13:45:49', 'RateManagementSystem');

INSERT INTO `tblCronJobCommand` (`CompanyID`, `GatewayID`, `Title`, `Command`, `Settings`, `Status`, `created_at`, `created_by`) VALUES (1, 17, 'Download FTP VENDOR File', 'ftpvendordownloadcdr', '[[{"title":"Max File Download Limit","type":"text","value":"","name":"FilesDownloadLimit"},{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"}]]', 1, '2017-04-17 13:45:49', 'RateManagementSystem');

/* permissions */
/* add for schedule & download*/
INSERT INTO `tblResourceCategories` (`ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES ('Report.Schedule', '1', '13');
INSERT INTO `tblResourceCategories` (`ResourceCategoryName`, `CompanyID`, `CategoryGroupID`) VALUES ('Report.Download', '1', '13');

/* add category id for view only permission*/
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Report.view', 'ReportController.edit', '1', 'Sumera Saeed', '2017-09-25 16:54:42.000', '2017-09-25 16:54:42.000', '1310');

/* add category id's for schedule permission*/
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Report.schedule_history', 'ReportController.schedule_history', 1, 'Sumera Khan', NULL, '2018-01-11 10:30:58.000', '2018-01-11 10:30:58.000', '1371');
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Report.schedule_history_datagrid', 'ReportController.schedule_history_datagrid', 1, 'Sumera Khan', NULL, '2018-01-11 10:30:58.000', '2018-01-11 10:30:58.000', '1371');
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Report.schedule', 'ReportController.schedule', 1, 'Sumera Khan', NULL, '2018-01-11 10:30:58.000', '2018-01-11 10:30:58.000', '1371');
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Report.add_schedule', 'ReportController.add_schedule', 1, 'Sumera Khan', NULL, '2018-01-11 10:30:58.000', '2018-01-11 10:30:58.000', '1371');
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Report.update_schedule', 'ReportController.update_schedule', 1, 'Sumera Khan', NULL, '2018-01-11 10:30:58.000', '2018-01-11 10:30:58.000', '1371');
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Report.schedule_delete', 'ReportController.schedule_delete', 1, 'Sumera Khan', NULL, '2018-01-11 10:30:58.000', '2018-01-11 10:30:58.000', '1371');
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Report.ajax_schedule_datagrid', 'ReportController.ajax_schedule_datagrid', 1, 'Sumera Khan', NULL, '2018-01-11 10:30:58.000', '2018-01-11 10:30:58.000', '1371');
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Report.schedule_download', 'ReportController.schedule_download', 1, 'Vishal Jagani', NULL, '2018-03-12 08:16:32.000', '2018-03-12 08:16:32.000', '1371');
INSERT INTO `tblResource` (`ResourceName`, `ResourceValue`, `CompanyID`, `CreatedBy`, `created_at`, `updated_at`, `CategoryID`) VALUES ('Report.status_update', 'ReportController.status_update', '1', 'Sumera Khan', '2018-01-11 10:30:58.000', '2018-01-11 10:30:58.000', '1371');

INSERT INTO `tblJobType` (`Code`, `Title`, `CreatedDate`, `CreatedBy`) VALUES ('SVRP', 'Sippy Vendor Rate Push', '2019-02-13 18:20:26', 'RateManagementSystem');

/*add margin for rate rule*/
ALTER TABLE `tblRateRuleMargin`
	ADD COLUMN `Type` INT(11) NULL DEFAULT NULL AFTER `FixedValue`;

DROP PROCEDURE IF EXISTS `prc_WSGenerateVendorSippySheetWithPrefix`;
DELIMITER //
CREATE PROCEDURE `prc_WSGenerateVendorSippySheetWithPrefix`(
	IN `p_VendorID` INT  ,
	IN `p_Trunks` varchar(200),
	IN `p_TimezonesID` INT,
	IN `p_Effective` VARCHAR(50),
	IN `p_CustomDate` DATE,
	IN `p_Prefix` VARCHAR(50)
)
BEGIN

		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		call vwVendorSippySheet(p_VendorID,p_Trunks,p_TimezonesID,p_Effective,p_CustomDate,p_Prefix);

		SELECT
			`Action [A|D|U|S|SA`,
			id ,
			vendorRate.Prefix,
			COUNTRY,
			Preference ,
			`Interval 1` ,
			`Interval N` ,
			`Price 1` ,
			`Price N` ,
			`1xx Timeout` ,
			`2xx Timeout` ,
			`Huntstop` ,
			Forbidden ,
			`Activation Date` ,
			`Expiration Date`
		FROM    tmp_VendorSippySheet_ vendorRate
		WHERE   vendorRate.AccountId = p_VendorID
						And  FIND_IN_SET(vendorRate.TrunkId,p_Trunks) != 0;


		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `vwVendorSippySheet`;
DELIMITER //
CREATE PROCEDURE `vwVendorSippySheet`(
	IN `p_AccountID` INT,
	IN `p_Trunks` LONGTEXT,
	IN `p_TimezonesID` INT,
	IN `p_Effective` VARCHAR(50),
	IN `p_CustomDate` DATE,
	IN `p_Prefix` VARCHAR(50)
)
BEGIN

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorSippySheet_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorSippySheet_(
			RateID int,
			`Action [A|D|U|S|SA` varchar(50),
			id varchar(10),
			Prefix varchar(50),
			COUNTRY varchar(200),
			Preference int,
			`Interval 1` int,
			`Interval N` int,
			`Price 1` float,
			`Price N` float,
			`1xx Timeout` int,
			`2xx Timeout` INT,
			Huntstop int,
			Forbidden int,
			`Activation Date` varchar(20),
			`Expiration Date` varchar(20),
			AccountID int,
			TrunkID int
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorArhiveSippySheet_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorArhiveSippySheet_(
			RateID int,
			`Action [A|D|U|S|SA` varchar(50),
			id varchar(10),
			Prefix varchar(50),
			COUNTRY varchar(200),
			Preference int,
			`Interval 1` int,
			`Interval N` int,
			`Price 1` float,
			`Price N` float,
			`1xx Timeout` int,
			`2xx Timeout` INT,
			Huntstop int,
			Forbidden int,
			`Activation Date` varchar(20),
			`Expiration Date` varchar(20),
			AccountID int,
			TrunkID int
		);

		call vwVendorCurrentRates(p_AccountID,p_Trunks,p_TimezonesID,p_Effective,p_CustomDate);

		INSERT INTO tmp_VendorSippySheet_
			SELECT
				NULL AS RateID,
				CASE WHEN EndDate IS NOT NULL THEN
					'SA'
				ELSE
					'A'
				END AS `Action [A|D|U|S|SA`,
				'' AS id,
				Concat('' , tblTrunk.Prefix ,vendorRate.Code) AS Prefix,
				vendorRate.Description AS COUNTRY,
				IFNULL(tblVendorPreference.Preference,5) as Preference,
				vendorRate.Interval1 as `Interval 1`,
				vendorRate.IntervalN as `Interval N`,
				vendorRate.Rate AS `Price 1`,
				vendorRate.RateN AS `Price N`,
				10 AS `1xx Timeout`,
				60 AS `2xx Timeout`,
				0 AS Huntstop,
				CASE
				WHEN (tblVendorBlocking.VendorBlockingId IS NOT NULL AND
							FIND_IN_SET(vendorRate.TrunkId,tblVendorBlocking.TrunkId) != 0
							OR
							(blockCountry.VendorBlockingId IS NOT NULL AND
							 FIND_IN_SET(vendorRate.TrunkId,blockCountry.TrunkId) != 0
							)
				) THEN 1
				ELSE 0
				END  AS Forbidden,
				CASE WHEN EffectiveDate < NOW()  THEN
					'NOW'
				ELSE
					DATE_FORMAT( EffectiveDate, '%Y-%m-%d %H:%i:%s' )
				END AS `Activation Date`,
				DATE_FORMAT( EndDate, '%Y-%m-%d %H:%i:%s' )  AS `Expiration Date`,

				tblAccount.AccountID,
				tblTrunk.TrunkID
			FROM tmp_VendorCurrentRates_ AS vendorRate
				INNER JOIN tblAccount
					ON vendorRate.AccountId = tblAccount.AccountID
				LEFT OUTER JOIN tblVendorBlocking
					ON vendorRate.RateID = tblVendorBlocking.RateId
						 AND tblAccount.AccountID = tblVendorBlocking.AccountId
						 AND vendorRate.TrunkID = tblVendorBlocking.TrunkID
						 AND vendorRate.TimezonesID = tblVendorBlocking.TimezonesID
				LEFT OUTER JOIN tblVendorBlocking AS blockCountry
					ON vendorRate.CountryID = blockCountry.CountryId
						 AND tblAccount.AccountID = blockCountry.AccountId
						 AND vendorRate.TrunkID = blockCountry.TrunkID
						 AND vendorRate.TimezonesID = blockCountry.TimezonesID
				LEFT JOIN tblVendorPreference
					ON tblVendorPreference.AccountId = vendorRate.AccountId
						 AND tblVendorPreference.TrunkID = vendorRate.TrunkID
						 AND tblVendorPreference.TimezonesID = vendorRate.TimezonesID
						 AND tblVendorPreference.RateId = vendorRate.RateID
				INNER JOIN tblTrunk
					ON tblTrunk.TrunkID = vendorRate.TrunkID
			WHERE (vendorRate.Rate > 0) And vendorRate.Code Like CONCAT(p_Prefix,'%');


	END//
DELIMITER ;

DROP PROCEDURE IF EXISTS `prc_WSGenerateRateTable`;
DELIMITER //
CREATE PROCEDURE `prc_WSGenerateRateTable`(
	IN `p_jobId` INT,
	IN `p_RateGeneratorId` INT,
	IN `p_RateTableId` INT,
	IN `p_TimezonesID` VARCHAR(50),
	IN `p_rateTableName` VARCHAR(200),
	IN `p_EffectiveDate` VARCHAR(10),
	IN `p_delete_exiting_rate` INT,
	IN `p_EffectiveRate` VARCHAR(50),
	IN `p_GroupBy` VARCHAR(50),
	IN `p_ModifiedBy` VARCHAR(50),
	IN `p_IsMerge` INT,
	IN `p_TakePrice` INT,
	IN `p_MergeInto` INT


)
GenerateRateTable:BEGIN


		DECLARE i INTEGER;
		DECLARE v_RTRowCount_ INT;
		DECLARE v_RatePosition_ INT;
		DECLARE v_Use_Preference_ INT;
		DECLARE v_CurrencyID_ INT;
		DECLARE v_CompanyCurrencyID_ INT;
		DECLARE v_Average_ TINYINT;
		DECLARE v_CompanyId_ INT;
		DECLARE v_codedeckid_ INT;
		DECLARE v_trunk_ INT;
		DECLARE v_rateRuleId_ INT;
		DECLARE v_RateGeneratorName_ VARCHAR(200);
		DECLARE v_pointer_ INT ;
		DECLARE v_rowCount_ INT ;

		DECLARE v_IncreaseEffectiveDate_ DATETIME ;
		DECLARE v_DecreaseEffectiveDate_ DATETIME ;





		DECLARE v_tmp_code_cnt int ;
		DECLARE v_tmp_code_pointer int;
		DECLARE v_p_code varchar(50);
		DECLARE v_Codlen_ int;
		DECLARE v_p_code__ VARCHAR(50);
		DECLARE v_Commit int;
		DECLARE v_TimezonesID int;

		DECLARE EXIT HANDLER FOR SQLEXCEPTION
		BEGIN
			SHOW WARNINGS;
			ROLLBACK;
			INSERT INTO tmp_JobLog_ (Message) VALUES ('RateTable generation failed');


		END;

		DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
		CREATE TEMPORARY TABLE tmp_JobLog_ (
			Message longtext
		);

		SET @@session.collation_connection='utf8_unicode_ci';
		SET @@session.character_set_client='utf8';
		SET SESSION group_concat_max_len = 1000000;


		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;



		SET p_EffectiveDate = CAST(p_EffectiveDate AS DATE);
		SET v_TimezonesID = IF(p_IsMerge=1,p_MergeInto,p_TimezonesID);

		IF p_rateTableName IS NOT NULL
		THEN


			SET v_RTRowCount_ = (SELECT
														 COUNT(*)
													 FROM tblRateTable
													 WHERE RateTableName = p_rateTableName
																 AND CompanyId = (SELECT
																										CompanyId
																									FROM tblRateGenerator
																									WHERE RateGeneratorID = p_RateGeneratorId));

			IF v_RTRowCount_ > 0
			THEN
				INSERT INTO tmp_JobLog_ (Message) VALUES ('RateTable Name is already exist, Please try using another RateTable Name');

				LEAVE GenerateRateTable;
			END IF;
		END IF;

		DROP TEMPORARY TABLE IF EXISTS tmp_Rates_;
		CREATE TEMPORARY TABLE tmp_Rates_  (
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			description VARCHAR(200) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			rateN DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			PreviousRate DECIMAL(18, 6),
			EffectiveDate DATE DEFAULT NULL,
			INDEX tmp_Rates_code (`code`),
			INDEX  tmp_Rates_description (`description`),
			UNIQUE KEY `unique_code` (`code`)

		);


		DROP TEMPORARY TABLE IF EXISTS tmp_Rates2_;
		CREATE TEMPORARY TABLE tmp_Rates2_  (
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			description VARCHAR(200) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			rateN DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			PreviousRate DECIMAL(18, 6),
			EffectiveDate DATE DEFAULT NULL,
			INDEX tmp_Rates2_code (`code`),
			INDEX  tmp_Rates_description (`description`)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_Rates3_;
		CREATE TEMPORARY TABLE tmp_Rates3_  (
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			description VARCHAR(200) COLLATE utf8_unicode_ci,
			UNIQUE KEY `unique_code` (`code`),
			INDEX  tmp_Rates_description (`description`)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_Codedecks_;
		CREATE TEMPORARY TABLE tmp_Codedecks_ (
			CodeDeckId INT
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Raterules_;

		CREATE TEMPORARY TABLE tmp_Raterules_  (
			rateruleid INT,
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			description VARCHAR(200) COLLATE utf8_unicode_ci,
			RowNo INT,
			`Order` INT,
			INDEX tmp_Raterules_code (`code`,`description`),
			INDEX tmp_Raterules_rateruleid (`rateruleid`),
			INDEX tmp_Raterules_RowNo (`RowNo`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Raterules_dup;

		CREATE TEMPORARY TABLE tmp_Raterules_dup  (
			rateruleid INT,
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			description VARCHAR(200) COLLATE utf8_unicode_ci,
			RowNo INT,
			`Order` INT,
			INDEX tmp_Raterules_code (`code`,`description`),
			INDEX tmp_Raterules_rateruleid (`rateruleid`),
			INDEX tmp_Raterules_RowNo (`RowNo`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Vendorrates_;
		CREATE TEMPORARY TABLE tmp_Vendorrates_  (
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			rateN DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			AccountId INT,
			RowNo INT,
			PreferenceRank INT,
			INDEX tmp_Vendorrates_code (`code`),
			INDEX tmp_Vendorrates_rate (`rate`)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_VRatesstage2_;
		CREATE TEMPORARY TABLE tmp_VRatesstage2_  (
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci,
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			description VARCHAR(200) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			rateN DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			FinalRankNumber int,
			INDEX tmp_Vendorrates_stage2__code (`RowCode`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_dupVRatesstage2_;
		CREATE TEMPORARY TABLE tmp_dupVRatesstage2_  (
			RowCode VARCHAR(50)  COLLATE utf8_unicode_ci,
			description VARCHAR(200) COLLATE utf8_unicode_ci,
			FinalRankNumber int,
			INDEX tmp_dupVendorrates_stage2__code (`RowCode`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Vendorrates_stage3_;
		CREATE TEMPORARY TABLE tmp_Vendorrates_stage3_  (
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci,
			description VARCHAR(200) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			rateN DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			INDEX tmp_Vendorrates_stage2__code (`RowCode`)
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_code_;
		CREATE TEMPORARY TABLE tmp_code_  (
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			INDEX tmp_code_code (`code`)
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_all_code_;
		CREATE TEMPORARY TABLE tmp_all_code_ (
			RowCode  varchar(50) COLLATE utf8_unicode_ci,
			Code  varchar(50) COLLATE utf8_unicode_ci,
			RowNo int,
			INDEX Index2 (Code)
		);



		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage_;
		CREATE TEMPORARY TABLE tmp_VendorRate_stage_ (
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci,
			AccountId INT ,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) COLLATE utf8_unicode_ci,
			Rate DECIMAL(18,6) ,
			RateN DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			MaxMatchRank int ,
			prev_prev_RowCode VARCHAR(50),
			prev_AccountID int
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
		CREATE TEMPORARY TABLE tmp_VendorRate_ (
			AccountId INT ,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) COLLATE utf8_unicode_ci,
			Rate DECIMAL(18,6) ,
			RateN DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_final_VendorRate_;
		CREATE TEMPORARY TABLE tmp_final_VendorRate_ (
			AccountId INT ,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) COLLATE utf8_unicode_ci,
			Rate DECIMAL(18,6) ,
			RateN DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci,
			FinalRankNumber int,
			INDEX IX_CODE (RowCode)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates_GroupBy_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates_GroupBy_(
			AccountId int,
			AccountName varchar(200),
			Code LONGTEXT,
			Description varchar(200) ,
			Rate DECIMAL(18,6) ,
			RateN DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate date,
			TrunkID int,
			TimezonesID int,
			CountryID int,
			RateID int,
			Preference int
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates_(
			AccountId int,
			AccountName varchar(200),
			Code varchar(50) COLLATE utf8_unicode_ci,
			Description varchar(200) ,
			Rate DECIMAL(18,6) ,
			RateN DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate date,
			TrunkID int,
			TimezonesID int,
			CountryID int,
			RateID int,
			Preference int,
			INDEX IX_CODE (Code)
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates1_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates1_(
			AccountId int,
			AccountName varchar(200),
			Code varchar(50),
			Description varchar(200),
			Rate DECIMAL(18,6) ,
			RateN DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate date,
			TrunkID int,
			TimezonesID int,
			CountryID int,
			RateID int,
			Preference int,
			INDEX IX_Code (Code),
			INDEX tmp_VendorCurrentRates_AccountId (`AccountId`,`TrunkID`,`RateId`,`EffectiveDate`)
		);

		SELECT CurrencyID INTO v_CurrencyID_ FROM  tblRateGenerator WHERE RateGeneratorId = p_RateGeneratorId;


		SELECT IFNULL(REPLACE(JSON_EXTRACT(Options, '$.IncreaseEffectiveDate'),'"',''), p_EffectiveDate) , IFNULL(REPLACE(JSON_EXTRACT(Options, '$.DecreaseEffectiveDate'),'"',''), p_EffectiveDate)   INTO v_IncreaseEffectiveDate_ , v_DecreaseEffectiveDate_  FROM tblJob WHERE Jobid = p_jobId;


		IF v_IncreaseEffectiveDate_ is null OR v_IncreaseEffectiveDate_ = '' THEN

			SET v_IncreaseEffectiveDate_ = p_EffectiveDate;

		END IF;

		IF v_DecreaseEffectiveDate_ is null OR v_DecreaseEffectiveDate_ = '' THEN

			SET v_DecreaseEffectiveDate_ = p_EffectiveDate;

		END IF;


		SELECT
			UsePreference,
			rateposition,
			companyid ,
			CodeDeckId,
			tblRateGenerator.TrunkID,
			tblRateGenerator.UseAverage  ,
			tblRateGenerator.RateGeneratorName INTO v_Use_Preference_, v_RatePosition_, v_CompanyId_, v_codedeckid_, v_trunk_, v_Average_, v_RateGeneratorName_
		FROM tblRateGenerator
		WHERE RateGeneratorId = p_RateGeneratorId;


		SELECT CurrencyId INTO v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = v_CompanyId_;




		INSERT INTO tmp_Raterules_
			SELECT
				rateruleid,
				tblRateRule.Code,
				tblRateRule.Description,
				@row_num := @row_num+1 AS RowID,
				tblRateRule.`Order`
			FROM tblRateRule,(SELECT @row_num := 0) x
			WHERE rategeneratorid = p_RateGeneratorId
			ORDER BY tblRateRule.`Order` ASC;


		insert into tmp_Raterules_dup (			rateruleid ,		code ,		description ,		RowNo 	,	`Order`)
		select rateruleid ,		code ,		description ,		RowNo, `Order` from tmp_Raterules_;

		INSERT INTO tmp_Codedecks_
			SELECT DISTINCT
				tblVendorTrunk.CodeDeckId
			FROM tblRateRule
				INNER JOIN tblRateRuleSource
					ON tblRateRule.RateRuleId = tblRateRuleSource.RateRuleId
				INNER JOIN tblAccount
					ON tblAccount.AccountID = tblRateRuleSource.AccountId and tblAccount.IsVendor = 1
				JOIN tblVendorTrunk
					ON tblAccount.AccountId = tblVendorTrunk.AccountID
						 AND  tblVendorTrunk.TrunkID = v_trunk_
						 AND tblVendorTrunk.Status = 1
			WHERE RateGeneratorId = p_RateGeneratorId;

		SET v_pointer_ = 1;

		SET v_rowCount_ = (SELECT COUNT(distinct concat(Code,Description) ) FROM tmp_Raterules_);




		insert into tmp_code_
			SELECT  DISTINCT LEFT(f.Code, x.RowNo) as loopCode
			FROM (
						 SELECT @RowNo  := @RowNo + 1 as RowNo
						 FROM mysql.help_category
							 ,(SELECT @RowNo := 0 ) x
						 limit 15
					 ) x
				INNER JOIN
				(SELECT
					 distinct
					 tblRate.code
				 FROM tblRate
					 JOIN tmp_Raterules_ rr
						 ON   ( rr.code = '' OR (rr.code != '' AND tblRate.Code LIKE (REPLACE(rr.code,'*', '%%')) ))
									AND
									( rr.description = '' OR ( rr.description != '' AND tblRate.Description LIKE (REPLACE(rr.description,'*', '%%')) ) )
				 where  tblRate.CodeDeckId = v_codedeckid_
				 Order by tblRate.code
				) as f
					ON   x.RowNo   <= LENGTH(f.Code)
			order by loopCode   desc;





		SELECT CurrencyId INTO v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = v_CompanyId_;
		SET @IncludeAccountIds = (SELECT GROUP_CONCAT(AccountId) from tblRateRule rr inner join  tblRateRuleSource rrs on rr.RateRuleId = rrs.RateRuleId where rr.RateGeneratorId = p_RateGeneratorId ) ;



		IF(p_IsMerge = 1)
		THEN




			INSERT INTO tmp_VendorCurrentRates1_
				Select DISTINCT AccountId,MAX(AccountName) AS AccountName,MAX(Code) AS Code,MAX(Description) AS Description, ROUND(IF(p_TakePrice=1,MAX(Rate),MIN(Rate)), 6) AS Rate, ROUND(IF(p_TakePrice=1,MAX(RateN),MIN(RateN)), 6) AS RateN,IF(p_TakePrice=1,MAX(ConnectionFee),MIN(ConnectionFee)) AS ConnectionFee,EffectiveDate,TrunkID,p_MergeInto AS TimezonesID,MAX(CountryID) AS CountryID,RateID,MAX(Preference) AS Preference
					FROM (
							 SELECT  tblVendorRate.AccountId,tblAccount.AccountName, tblRate.Code, tblRate.Description,
																																					CASE WHEN  tblAccount.CurrencyId = v_CurrencyID_
																																						THEN
																																							tblVendorRate.Rate
																																					WHEN  v_CompanyCurrencyID_ = v_CurrencyID_
																																						THEN
																																							(
																																								( tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ) )
																																							)
																																					ELSE
																																						(

																																							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = v_CurrencyID_ and  CompanyID = v_CompanyId_ )
																																							* (tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ))
																																						)
																																					END as  Rate,
																																					CASE WHEN  tblAccount.CurrencyId = v_CurrencyID_
																																						THEN
																																							tblVendorRate.RateN
																																					WHEN  v_CompanyCurrencyID_ = v_CurrencyID_
																																						THEN
																																							(
																																								( tblVendorRate.RateN  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ) )
																																							)
																																					ELSE
																																						(

																																							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = v_CurrencyID_ and  CompanyID = v_CompanyId_ )
																																							* (tblVendorRate.RateN  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ))
																																						)
																																					END as  RateN,
								 ConnectionFee,
																																					DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') AS EffectiveDate,
								 tblVendorRate.TrunkID, tblVendorRate.TimezonesID, tblRate.CountryID, tblRate.RateID,IFNULL(vp.Preference, 5) AS Preference,
																																					@row_num := IF(@prev_AccountId = tblVendorRate.AccountID AND @prev_TrunkID = tblVendorRate.TrunkID AND @prev_TimezonesID = tblVendorRate.TimezonesID AND @prev_RateId = tblVendorRate.RateID AND @prev_EffectiveDate >= tblVendorRate.EffectiveDate, @row_num + 1, 1) AS RowID,
								 @prev_AccountId := tblVendorRate.AccountID,
								 @prev_TrunkID := tblVendorRate.TrunkID,
								 @prev_TimezonesID := tblVendorRate.TimezonesID,
								 @prev_RateId := tblVendorRate.RateID,
								 @prev_EffectiveDate := tblVendorRate.EffectiveDate
							 FROM      tblVendorRate
								 Inner join tblVendorTrunk vt on vt.CompanyID = v_CompanyId_ AND vt.AccountID = tblVendorRate.AccountID and vt.Status =  1 and vt.TrunkID =  v_trunk_
								 Inner join tblTimezones t on t.TimezonesID = tblVendorRate.TimezonesID AND t.Status = 1
								 inner join tmp_Codedecks_ tcd on vt.CodeDeckId = tcd.CodeDeckId
								 INNER JOIN tblAccount   ON  tblAccount.CompanyID = v_CompanyId_ AND tblVendorRate.AccountId = tblAccount.AccountID and tblAccount.IsVendor = 1
								 INNER JOIN tblRate ON tblRate.CompanyID = v_CompanyId_  AND tblRate.CodeDeckId = vt.CodeDeckId  AND    tblVendorRate.RateId = tblRate.RateID
								 INNER JOIN tmp_code_ tcode ON tcode.Code  = tblRate.Code
								 LEFT JOIN tblVendorPreference vp
									 ON vp.AccountId = tblVendorRate.AccountId
											AND vp.TrunkID = tblVendorRate.TrunkID
											AND vp.TimezonesID = tblVendorRate.TimezonesID
											AND vp.RateId = tblVendorRate.RateId
								 LEFT OUTER JOIN tblVendorBlocking AS blockCode   ON tblVendorRate.RateId = blockCode.RateId
																																		 AND tblVendorRate.AccountId = blockCode.AccountId
																																		 AND tblVendorRate.TrunkID = blockCode.TrunkID
																																		 AND tblVendorRate.TimezonesID = blockCode.TimezonesID
								 LEFT OUTER JOIN tblVendorBlocking AS blockCountry    ON tblRate.CountryID = blockCountry.CountryId
																																				 AND tblVendorRate.AccountId = blockCountry.AccountId
																																				 AND tblVendorRate.TrunkID = blockCountry.TrunkID
																																				 AND tblVendorRate.TimezonesID = blockCountry.TimezonesID

								 ,(SELECT @row_num := 1,  @prev_AccountId := '',@prev_TrunkID := '',@prev_TimezonesID := '', @prev_RateId := '', @prev_EffectiveDate := '') x

							 WHERE
								 (
									 (p_EffectiveRate = 'now' AND EffectiveDate <= NOW())
									 OR
									 (p_EffectiveRate = 'future' AND EffectiveDate > NOW()  )
									 OR
									 (	 p_EffectiveRate = 'effective' AND EffectiveDate <= p_EffectiveDate
											 AND ( tblVendorRate.EndDate IS NULL OR (tblVendorRate.EndDate > DATE(p_EffectiveDate)) )
									 )
								 )
								 AND ( tblVendorRate.EndDate IS NULL OR tblVendorRate.EndDate > now() )
								 AND tblAccount.IsVendor = 1
								 AND tblAccount.Status = 1
								 AND tblAccount.CurrencyId is not NULL
								 AND tblVendorRate.TrunkID = v_trunk_
								 AND FIND_IN_SET(tblVendorRate.TimezonesID,p_TimezonesID) != 0
								 AND blockCode.RateId IS NULL
								 AND blockCountry.CountryId IS NULL
								 AND ( @IncludeAccountIds = NULL
											 OR ( @IncludeAccountIds IS NOT NULL
														AND FIND_IN_SET(tblVendorRate.AccountId,@IncludeAccountIds) > 0
											 )
								 )
							 ORDER BY tblVendorRate.AccountId, tblVendorRate.TrunkID, tblVendorRate.TimezonesID, tblVendorRate.RateId, tblVendorRate.EffectiveDate DESC
						 ) tbl
				GROUP BY RateID, AccountId, TrunkID, EffectiveDate
				order by Code asc;


		ELSE

				INSERT INTO tmp_VendorCurrentRates1_
				Select DISTINCT AccountId,AccountName,Code,Description, Rate, RateN,ConnectionFee,EffectiveDate,TrunkID,TimezonesID,CountryID,RateID,Preference
					FROM (
							 SELECT  tblVendorRate.AccountId,tblAccount.AccountName, tblRate.Code, tblRate.Description,
																																					CASE WHEN  tblAccount.CurrencyId = v_CurrencyID_
																																						THEN
																																							tblVendorRate.Rate
																																					WHEN  v_CompanyCurrencyID_ = v_CurrencyID_
																																						THEN
																																							(
																																								( tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ) )
																																							)
																																					ELSE
																																						(

																																							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = v_CurrencyID_ and  CompanyID = v_CompanyId_ )
																																							* (tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ))
																																						)
																																					END as  Rate,
																																					CASE WHEN  tblAccount.CurrencyId = v_CurrencyID_
																																						THEN
																																							tblVendorRate.RateN
																																					WHEN  v_CompanyCurrencyID_ = v_CurrencyID_
																																						THEN
																																							(
																																								( tblVendorRate.RateN  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ) )
																																							)
																																					ELSE
																																						(

																																							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = v_CurrencyID_ and  CompanyID = v_CompanyId_ )
																																							* (tblVendorRate.RateN  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ))
																																						)
																																					END as  RateN,
								 ConnectionFee,
																																					DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') AS EffectiveDate,
								 tblVendorRate.TrunkID, tblVendorRate.TimezonesID, tblRate.CountryID, tblRate.RateID,IFNULL(vp.Preference, 5) AS Preference,
																																					@row_num := IF(@prev_AccountId = tblVendorRate.AccountID AND @prev_TrunkID = tblVendorRate.TrunkID AND @prev_TimezonesID = tblVendorRate.TimezonesID AND @prev_RateId = tblVendorRate.RateID AND @prev_EffectiveDate >= tblVendorRate.EffectiveDate, @row_num + 1, 1) AS RowID,
								 @prev_AccountId := tblVendorRate.AccountID,
								 @prev_TrunkID := tblVendorRate.TrunkID,
								 @prev_TimezonesID := tblVendorRate.TimezonesID,
								 @prev_RateId := tblVendorRate.RateID,
								 @prev_EffectiveDate := tblVendorRate.EffectiveDate
							 FROM      tblVendorRate
								 Inner join tblVendorTrunk vt on vt.CompanyID = v_CompanyId_ AND vt.AccountID = tblVendorRate.AccountID and vt.Status =  1 and vt.TrunkID =  v_trunk_
								 Inner join tblTimezones t on t.TimezonesID = tblVendorRate.TimezonesID AND t.Status = 1
								 inner join tmp_Codedecks_ tcd on vt.CodeDeckId = tcd.CodeDeckId
								 INNER JOIN tblAccount   ON  tblAccount.CompanyID = v_CompanyId_ AND tblVendorRate.AccountId = tblAccount.AccountID and tblAccount.IsVendor = 1
								 INNER JOIN tblRate ON tblRate.CompanyID = v_CompanyId_  AND tblRate.CodeDeckId = vt.CodeDeckId  AND    tblVendorRate.RateId = tblRate.RateID
								 INNER JOIN tmp_code_ tcode ON tcode.Code  = tblRate.Code
								 LEFT JOIN tblVendorPreference vp
									 ON vp.AccountId = tblVendorRate.AccountId
											AND vp.TrunkID = tblVendorRate.TrunkID
											AND vp.TimezonesID = tblVendorRate.TimezonesID
											AND vp.RateId = tblVendorRate.RateId
								 LEFT OUTER JOIN tblVendorBlocking AS blockCode   ON tblVendorRate.RateId = blockCode.RateId
																																		 AND tblVendorRate.AccountId = blockCode.AccountId
																																		 AND tblVendorRate.TrunkID = blockCode.TrunkID
																																		 AND tblVendorRate.TimezonesID = blockCode.TimezonesID
								 LEFT OUTER JOIN tblVendorBlocking AS blockCountry    ON tblRate.CountryID = blockCountry.CountryId
																																				 AND tblVendorRate.AccountId = blockCountry.AccountId
																																				 AND tblVendorRate.TrunkID = blockCountry.TrunkID
																																				 AND tblVendorRate.TimezonesID = blockCountry.TimezonesID

								 ,(SELECT @row_num := 1,  @prev_AccountId := '',@prev_TrunkID := '',@prev_TimezonesID := '', @prev_RateId := '', @prev_EffectiveDate := '') x

							 WHERE
								 (
									 (p_EffectiveRate = 'now' AND EffectiveDate <= NOW())
									 OR
									 (p_EffectiveRate = 'future' AND EffectiveDate > NOW()  )
									 OR
									 (	 p_EffectiveRate = 'effective' AND EffectiveDate <= p_EffectiveDate
											 AND ( tblVendorRate.EndDate IS NULL OR (tblVendorRate.EndDate > DATE(p_EffectiveDate)) )
									 )
								 )
								 AND ( tblVendorRate.EndDate IS NULL OR tblVendorRate.EndDate > now() )
								 AND tblAccount.IsVendor = 1
								 AND tblAccount.Status = 1
								 AND tblAccount.CurrencyId is not NULL
								 AND tblVendorRate.TrunkID = v_trunk_
								 AND tblVendorRate.TimezonesID = v_TimezonesID
								 AND blockCode.RateId IS NULL
								 AND blockCountry.CountryId IS NULL
								 AND ( @IncludeAccountIds = NULL
											 OR ( @IncludeAccountIds IS NOT NULL
														AND FIND_IN_SET(tblVendorRate.AccountId,@IncludeAccountIds) > 0
											 )
								 )
							 ORDER BY tblVendorRate.AccountId, tblVendorRate.TrunkID, tblVendorRate.TimezonesID, tblVendorRate.RateId, tblVendorRate.EffectiveDate DESC
						 ) tbl
				order by Code asc;

		END IF;





	INSERT INTO tmp_VendorCurrentRates_
				Select AccountId,AccountName,Code,Description, Rate, RateN,ConnectionFee,EffectiveDate,TrunkID,TimezonesID,CountryID,RateID,Preference
				FROM (
							 SELECT * ,
								 @row_num := IF(@prev_AccountId = AccountID AND @prev_TrunkID = TrunkID AND @prev_TimezonesID = TimezonesID AND @prev_RateId = RateID AND @prev_EffectiveDate >= EffectiveDate, @row_num + 1, 1) AS RowID,
								 @prev_AccountId := AccountID,
								 @prev_TrunkID := TrunkID,
								 @prev_TimezonesID := TimezonesID,
								 @prev_RateId := RateID,
								 @prev_EffectiveDate := EffectiveDate
							 FROM tmp_VendorCurrentRates1_
								 ,(SELECT @row_num := 1,  @prev_AccountId := '',@prev_TrunkID := '',@prev_TimezonesID := '', @prev_RateId := '', @prev_EffectiveDate := '') x
							 ORDER BY AccountId, TrunkID, TimezonesID, RateId, EffectiveDate DESC
						 ) tbl
				WHERE RowID = 1
				order by Code asc;



		insert into tmp_all_code_ (RowCode,Code,RowNo)
			select RowCode , loopCode,RowNo
			from (
						 select   RowCode , loopCode,
							 @RowNo := ( CASE WHEN (@prev_Code  = tbl1.RowCode  ) THEN @RowNo + 1
													 ELSE 1
													 END

							 )      as RowNo,
							 @prev_Code := tbl1.RowCode

						 from (
										SELECT distinct f.Code as RowCode, LEFT(f.Code, x.RowNo) as loopCode
										FROM (
													 SELECT @RowNo  := @RowNo + 1 as RowNo
													 FROM mysql.help_category
														 ,(SELECT @RowNo := 0 ) x
													 limit 15
												 ) x
											INNER JOIN
											(
												select distinct Code from
													tmp_VendorCurrentRates_
											) AS f
												ON  x.RowNo   <= LENGTH(f.Code)
										order by RowCode desc,  LENGTH(loopCode) DESC
									) tbl1
							 , ( Select @RowNo := 0 ) x
					 ) tbl order by RowCode desc,  LENGTH(loopCode) DESC ;





		IF p_GroupBy = 'Desc'
		THEN

			INSERT INTO tmp_VendorCurrentRates_GroupBy_
				Select AccountId,max(AccountName),max(Code),Description,max(Rate),max(RateN),max(ConnectionFee),max(EffectiveDate),TrunkID,TimezonesID,max(CountryID),max(RateID),max(Preference)
				FROM
				(

					Select AccountId,AccountName,r.Code,r.Description, Rate, RateN,ConnectionFee,EffectiveDate,TrunkID,TimezonesID,r.CountryID,r.RateID,Preference
					FROM tmp_VendorCurrentRates_ v
					Inner join  tmp_all_code_ SplitCode   on v.Code = SplitCode.Code
					Inner join  tblRate r   on r.CodeDeckId = v_codedeckid_ AND r.Code = SplitCode.RowCode


				) tmp
				GROUP BY AccountId, TrunkID, TimezonesID, Description
				order by Description asc;


				truncate table tmp_VendorCurrentRates_;

				INSERT INTO tmp_VendorCurrentRates_ (AccountId,AccountName,Code,Description, Rate, RateN,ConnectionFee,EffectiveDate,TrunkID,TimezonesID,CountryID,RateID,Preference)
			  		SELECT AccountId,AccountName,Code,Description, Rate, RateN,ConnectionFee,EffectiveDate,TrunkID,TimezonesID,CountryID,RateID,Preference
					FROM tmp_VendorCurrentRates_GroupBy_;


		END IF;




		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage_1;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorRate_stage_1 as (select * from tmp_VendorRate_stage_);

		insert ignore into tmp_VendorRate_stage_1 (
			RowCode,
			AccountId ,
			AccountName ,
			Code ,
			Rate ,
			RateN ,
			ConnectionFee,
			EffectiveDate ,
			Description ,
			Preference
		)
			SELECT
				distinct
				RowCode,
				v.AccountId ,
				v.AccountName ,
				v.Code ,
				v.Rate ,
				v.RateN ,
				v.ConnectionFee,
				v.EffectiveDate ,
				v.Description ,
				v.Preference
			FROM tmp_VendorCurrentRates_ v
				Inner join  tmp_all_code_
										SplitCode   on v.Code = SplitCode.Code
			where  SplitCode.Code is not null
			order by AccountID,SplitCode.RowCode desc ,LENGTH(SplitCode.RowCode), v.Code desc, LENGTH(v.Code)  desc;



		insert into tmp_VendorRate_stage_
			SELECT
				RowCode,
				v.AccountId ,
				v.AccountName ,
				v.Code ,
				v.Rate ,
				v.RateN ,
				v.ConnectionFee,
				v.EffectiveDate ,
				v.Description ,
				v.Preference,
				@rank := ( CASE WHEN ( @prev_RowCode   = RowCode and   @prev_AccountID = v.AccountId   )
					THEN @rank + 1
									 ELSE 1  END ) AS MaxMatchRank,

				@prev_RowCode := RowCode	 as prev_RowCode,
				@prev_AccountID := v.AccountId as prev_AccountID
			FROM tmp_VendorRate_stage_1 v
				, (SELECT  @prev_RowCode := '',  @rank := 0 , @prev_Code := '' , @prev_AccountID := Null) f
			order by AccountID,RowCode desc ;


		truncate tmp_VendorRate_;
		insert into tmp_VendorRate_
			select
				AccountId ,
				AccountName ,
				Code ,
				Rate ,
				RateN ,
				ConnectionFee,
				EffectiveDate ,
				Description ,
				Preference,
				RowCode
			from tmp_VendorRate_stage_
			where MaxMatchRank = 1 order by RowCode desc;







		WHILE v_pointer_ <= v_rowCount_
		DO

			SET v_rateRuleId_ = (SELECT rateruleid FROM tmp_Raterules_ rr WHERE rr.RowNo = v_pointer_);


				INSERT INTO tmp_Rates2_ (code,description,rate,rateN,ConnectionFee)
				select  code,description,rate,rateN,ConnectionFee from tmp_Rates_;



				IF p_GroupBy = 'Desc'
				THEN


						INSERT IGNORE INTO tmp_Rates3_ (code,description)
						 select distinct r.code,r.description
						from tmp_VendorCurrentRates1_  tmpvr
						Inner join  tblRate r   on r.CodeDeckId = v_codedeckid_ AND r.Code = tmpvr.Code
						inner JOIN tmp_Raterules_ rr ON rr.RateRuleId = v_rateRuleId_ and
																 (
																	 ( rr.code != '' AND r.Code LIKE (REPLACE(rr.code,'*', '%%')) )
																	 OR
																	 ( rr.description != '' AND r.Description LIKE (REPLACE(rr.description,'*', '%%')) )
																 )
						left JOIN tmp_Raterules_dup rr2 ON rr2.Order > rr.Order and
																		 (
																			 ( rr2.code != '' AND r.Code LIKE (REPLACE(rr2.code,'*', '%%')) )
																			 OR
																			 ( rr2.description != '' AND r.Description LIKE (REPLACE(rr2.description,'*', '%%')) )
																		 )
						inner JOIN tblRateRuleSource rrs ON  rrs.RateRuleId = rr.rateruleid  and rrs.AccountId = tmpvr.AccountId
						where rr2.code is null;

				END IF;



			truncate tmp_final_VendorRate_;

			IF( v_Use_Preference_ = 0 )
			THEN

				insert into tmp_final_VendorRate_
					SELECT
						AccountId ,
						AccountName ,
						Code ,
						Rate ,
						RateN ,
						ConnectionFee,
						EffectiveDate ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								vr.AccountId ,
								vr.AccountName ,
								vr.Code ,
								vr.Rate ,
								vr.RateN ,
								vr.ConnectionFee,
								vr.EffectiveDate ,
								vr.Description ,
								vr.Preference,
								vr.RowCode,
								CASE WHEN p_GroupBy = 'Desc'  THEN
													@rank := CASE WHEN ( @prev_Description = vr.Description  AND @prev_Rate <=  vr.Rate ) THEN @rank+1
													 ELSE
														 1
													 END

								ELSE	@rank := CASE WHEN ( @prev_RowCode = vr.RowCode  AND @prev_Rate <=  vr.Rate ) THEN @rank+1

													 ELSE
														 1
													 END
								END
									AS FinalRankNumber,
								@prev_RowCode  := vr.RowCode,
								@prev_Description  := vr.Description,
								@prev_Rate  := vr.Rate
							from (
										 select distinct tmpvr.*
										 from tmp_VendorRate_  tmpvr
											 inner JOIN tmp_Raterules_ rr ON rr.RateRuleId = v_rateRuleId_ and
																											 (
																												 ( rr.code != '' AND tmpvr.RowCode LIKE (REPLACE(rr.code,'*', '%%')) )
																												 OR
																												 ( rr.description != '' AND tmpvr.Description LIKE (REPLACE(rr.description,'*', '%%')) )
																											 )
											 left JOIN tmp_Raterules_dup rr2 ON rr2.Order > rr.Order and
																													 (
																														 ( rr2.code != '' AND tmpvr.RowCode LIKE (REPLACE(rr2.code,'*', '%%')) )
																														 OR
																														 ( rr2.description != '' AND tmpvr.Description LIKE (REPLACE(rr2.description,'*', '%%')) )
																													 )
											 inner JOIN tblRateRuleSource rrs ON  rrs.RateRuleId = rr.rateruleid  and rrs.AccountId = tmpvr.AccountId
										 where rr2.code is null

									 ) vr
								,(SELECT @rank := 0 , @prev_RowCode := '' , @prev_Rate := 0 , @prev_Description := ''  ) x
							order by
								CASE WHEN p_GroupBy = 'Desc'  THEN
									vr.Description
								ELSE
									vr.RowCode
								END , vr.Rate,vr.AccountId

						) tbl1
					where FinalRankNumber <= v_RatePosition_;

			ELSE


				insert into tmp_final_VendorRate_
					SELECT
						AccountId ,
						AccountName ,
						Code ,
						Rate ,
						RateN ,
						ConnectionFee,
						EffectiveDate ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								vr.AccountId ,
								vr.AccountName ,
								vr.Code ,
								vr.Rate ,
								vr.RateN ,
								vr.ConnectionFee,
								vr.EffectiveDate ,
								vr.Description ,
								vr.Preference,
								vr.RowCode,

								CASE WHEN p_GroupBy = 'Desc'  THEN

										@preference_rank := CASE WHEN (@prev_Description  = vr.Description  AND @prev_Preference > vr.Preference  )   THEN @preference_rank + 1
																		WHEN (@prev_Description  = vr.Description  AND @prev_Preference = vr.Preference AND @prev_Rate <= vr.Rate) THEN @preference_rank + 1

																		ELSE 1 END
								ELSE
												@preference_rank := CASE WHEN (@prev_Code  = vr.RowCode  AND @prev_Preference > vr.Preference  )   THEN @preference_rank + 1
																		WHEN (@prev_Code  = vr.RowCode  AND @prev_Preference = vr.Preference AND @prev_Rate <= vr.Rate) THEN @preference_rank + 1

																		ELSE 1 END
								END

								AS FinalRankNumber,
								@prev_Code := vr.RowCode,
								@prev_Description  := vr.Description,
								@prev_Preference := vr.Preference,
								@prev_Rate := vr.Rate
							from (
										 select distinct tmpvr.*
										 from tmp_VendorRate_  tmpvr
											 inner JOIN tmp_Raterules_ rr ON rr.RateRuleId = v_rateRuleId_ and
																											 (
																												 ( rr.code != '' AND tmpvr.RowCode LIKE (REPLACE(rr.code,'*', '%%')) )
																												 OR
																												 ( rr.description != '' AND tmpvr.Description LIKE (REPLACE(rr.description,'*', '%%')) )
																											 )
											 left JOIN tmp_Raterules_dup rr2 ON rr2.Order > rr.Order and
																													 (
																														 ( rr2.code != '' AND tmpvr.RowCode LIKE (REPLACE(rr2.code,'*', '%%')) )
																														 OR
																														 ( rr2.description != '' AND tmpvr.Description LIKE (REPLACE(rr2.description,'*', '%%')) )
																													 )
											 inner JOIN tblRateRuleSource rrs ON  rrs.RateRuleId = rr.rateruleid  and rrs.AccountId = tmpvr.AccountId
										 where rr2.code is null

									 ) vr

								,(SELECT @preference_rank := 0 , @prev_Code := ''  , @prev_Preference := 5,  @prev_Rate := 0, @prev_Description := '') x
							order by
							CASE WHEN p_GroupBy = 'Desc'  THEN
									vr.Description
								ELSE
									vr.RowCode
								END , vr.Preference DESC ,vr.Rate ASC ,vr.AccountId ASC
						) tbl1
					where FinalRankNumber <= v_RatePosition_;


			END IF;



			truncate   tmp_VRatesstage2_;

			INSERT INTO tmp_VRatesstage2_
				SELECT
					vr.RowCode,
					vr.code,
					vr.description,
					vr.rate,
					vr.rateN,
					vr.ConnectionFee,
					vr.FinalRankNumber
				FROM tmp_final_VendorRate_ vr
					left join tmp_Rates2_ rate on rate.Code = vr.RowCode
				WHERE  rate.code is null
				order by vr.FinalRankNumber desc ;



			IF v_Average_ = 0
			THEN


				IF p_GroupBy = 'Desc'
				THEN

						insert into tmp_dupVRatesstage2_
						SELECT max(RowCode) , description,   MAX(FinalRankNumber) AS MaxFinalRankNumber
						FROM tmp_VRatesstage2_ GROUP BY description;

					truncate tmp_Vendorrates_stage3_;
					INSERT INTO tmp_Vendorrates_stage3_
						select  vr.RowCode as RowCode ,vr.description , vr.rate as rate , vr.rateN as rateN , vr.ConnectionFee as  ConnectionFee
						from tmp_VRatesstage2_ vr
							INNER JOIN tmp_dupVRatesstage2_ vr2
								ON (vr.description = vr2.description AND  vr.FinalRankNumber = vr2.FinalRankNumber);


				ELSE

					insert into tmp_dupVRatesstage2_
						SELECT RowCode , MAX(description),   MAX(FinalRankNumber) AS MaxFinalRankNumber
						FROM tmp_VRatesstage2_ GROUP BY RowCode;

					truncate tmp_Vendorrates_stage3_;
					INSERT INTO tmp_Vendorrates_stage3_
						select  vr.RowCode as RowCode ,vr.description , vr.rate as rate , vr.rateN as rateN , vr.ConnectionFee as  ConnectionFee
						from tmp_VRatesstage2_ vr
							INNER JOIN tmp_dupVRatesstage2_ vr2
								ON (vr.RowCode = vr2.RowCode AND  vr.FinalRankNumber = vr2.FinalRankNumber);

				END IF;


				INSERT IGNORE INTO tmp_Rates_ (code,description,rate,rateN,ConnectionFee,PreviousRate)
                SELECT RowCode,
		                description,
                    CASE WHEN rule_mgn1.RateRuleId is not null
                        THEN
                            CASE WHEN trim(IFNULL(rule_mgn1.AddMargin,"")) != '' THEN
                                vRate.rate + (CASE WHEN rule_mgn1.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn1.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * vRate.rate) ELSE rule_mgn1.addmargin END)
                            WHEN trim(IFNULL(rule_mgn1.FixedValue,"")) != '' THEN
                                rule_mgn1.FixedValue
                            ELSE
                                vRate.rate
                            END
                    ELSE
                        vRate.rate
                    END as Rate,
                    CASE WHEN rule_mgn2.RateRuleId is not null
                        THEN
                            CASE WHEN trim(IFNULL(rule_mgn2.AddMargin,"")) != '' THEN
                                vRate.rateN + (CASE WHEN rule_mgn2.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn2.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * vRate.rateN) ELSE rule_mgn2.addmargin END)
                            WHEN trim(IFNULL(rule_mgn2.FixedValue,"")) != '' THEN
                                rule_mgn2.FixedValue
                            ELSE
                                vRate.rateN
                            END
                    ELSE
                        vRate.rateN
                    END as RateN,

                    CASE WHEN rule_mgn3.RateRuleId is not null
                        THEN
                            CASE WHEN trim(IFNULL(rule_mgn3.AddMargin,"")) != '' THEN
                                IFNULL(vRate.ConnectionFee,0) + (CASE WHEN rule_mgn3.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn3.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * IFNULL(vRate.ConnectionFee,0)) ELSE rule_mgn3.addmargin END)
                            WHEN trim(IFNULL(rule_mgn3.FixedValue,"")) != '' THEN
                                rule_mgn3.FixedValue
                            ELSE
                                IFNULL(vRate.ConnectionFee,0)
                            END
                    ELSE
                        IFNULL(vRate.ConnectionFee,0)
                    END as ConnectionFee,

					null AS PreviousRate
                FROM tmp_Vendorrates_stage3_ vRate
                 LEFT join tblRateRuleMargin rule_mgn1 on  rule_mgn1.RateRuleId = v_rateRuleId_ AND rule_mgn1.`Type` = 1 and vRate.rate Between rule_mgn1.MinRate and rule_mgn1.MaxRate
                LEFT join tblRateRuleMargin rule_mgn2 on  rule_mgn2.RateRuleId = v_rateRuleId_ AND rule_mgn2.`Type` = 1 and vRate.rateN Between rule_mgn2.MinRate and rule_mgn2.MaxRate
                LEFT join tblRateRuleMargin rule_mgn3 on  rule_mgn3.RateRuleId = v_rateRuleId_ AND rule_mgn3.`Type` = 2 and vRate.ConnectionFee Between rule_mgn3.MinRate and rule_mgn3.MaxRate;

				-- Type = 1 Rate
				-- Type = 2 ConnectionFee

			ELSE

				INSERT IGNORE INTO tmp_Rates_ (code,description,rate,rateN,ConnectionFee,PreviousRate)
                SELECT RowCode,
		                description,
                    CASE WHEN rule_mgn1.RateRuleId is not null
                        THEN
                            CASE WHEN trim(IFNULL(rule_mgn1.AddMargin,"")) != '' THEN
                                vRate.rate + (CASE WHEN rule_mgn1.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn1.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * vRate.rate) ELSE rule_mgn1.addmargin END)
                            WHEN trim(IFNULL(rule_mgn1.FixedValue,"")) != '' THEN
                                rule_mgn1.FixedValue
                            ELSE
                                vRate.rate
                            END
                    ELSE
                        vRate.rate
                    END as Rate,
                    CASE WHEN rule_mgn2.RateRuleId is not null
                        THEN
                            CASE WHEN trim(IFNULL(rule_mgn2.AddMargin,"")) != '' THEN
                                vRate.rateN + (CASE WHEN rule_mgn2.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn2.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * vRate.rateN) ELSE rule_mgn2.addmargin END)
                            WHEN trim(IFNULL(rule_mgn2.FixedValue,"")) != '' THEN
                                rule_mgn2.FixedValue
                            ELSE
                                vRate.rateN
                            END
                    ELSE
                        vRate.rateN
                    END as RateN,

                    CASE WHEN rule_mgn3.RateRuleId is not null
                        THEN
                            CASE WHEN trim(IFNULL(rule_mgn3.AddMargin,"")) != '' THEN
                                IFNULL(vRate.ConnectionFee,0) + (CASE WHEN rule_mgn3.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn3.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * IFNULL(vRate.ConnectionFee,0)) ELSE rule_mgn3.addmargin END)
                            WHEN trim(IFNULL(rule_mgn3.FixedValue,"")) != '' THEN
                                rule_mgn3.FixedValue
                            ELSE
                                IFNULL(vRate.ConnectionFee,0)
                            END
                    ELSE
                        IFNULL(vRate.ConnectionFee,0)
                    END as ConnectionFee,

					null AS PreviousRate
                FROM
                (
                     select
                        max(RowCode) AS RowCode,
                        max(description) AS description,
                        AVG(Rate) as Rate,
                        AVG(RateN) as RateN,
                        AVG(ConnectionFee) as ConnectionFee
                        from tmp_VRatesstage2_
                        group by
                        CASE WHEN p_GroupBy = 'Desc' THEN
                          description
                        ELSE  RowCode
      						END

                )  vRate
                 LEFT join tblRateRuleMargin rule_mgn1 on  rule_mgn1.RateRuleId = v_rateRuleId_ AND rule_mgn1.`Type` = 1 and vRate.rate Between rule_mgn1.MinRate and rule_mgn1.MaxRate
                LEFT join tblRateRuleMargin rule_mgn2 on  rule_mgn2.RateRuleId = v_rateRuleId_ AND rule_mgn2.`Type` = 1 and vRate.rateN Between rule_mgn2.MinRate and rule_mgn2.MaxRate
                LEFT join tblRateRuleMargin rule_mgn3 on  rule_mgn3.RateRuleId = v_rateRuleId_ AND rule_mgn3.`Type` = 2 and vRate.ConnectionFee Between rule_mgn3.MinRate and rule_mgn3.MaxRate;

                -- Type = 1 Rate
					-- Type = 2 ConnectionFee
			END IF;


			SET v_pointer_ = v_pointer_ + 1;


		END WHILE;


		IF p_GroupBy = 'Desc'
		THEN

			truncate table tmp_Rates2_;
			insert into tmp_Rates2_ select * from tmp_Rates_;

				insert ignore into tmp_Rates_ (code,description,rate,rateN,ConnectionFee,PreviousRate)
				select
				distinct
					vr.Code,
					vr.Description,
					vd.rate,
					vd.rateN,
					vd.ConnectionFee,
					vd.PreviousRate
				from  tmp_Rates3_ vr
				inner JOIN tmp_Rates2_ vd on  vd.Description = vr.Description and vd.Code != vr.Code
				where vd.Rate is not null;

		END IF;


		START TRANSACTION;

		IF p_RateTableId = -1
		THEN

			INSERT INTO tblRateTable (CompanyId, RateTableName, RateGeneratorID, TrunkID, CodeDeckId,CurrencyID)
			VALUES (v_CompanyId_, p_rateTableName, p_RateGeneratorId, v_trunk_, v_codedeckid_,v_CurrencyID_);

			SET p_RateTableId = LAST_INSERT_ID();

			INSERT INTO tblRateTableRate (RateID,
																		RateTableId,
																		TimezonesID,
																		Rate,
																		RateN,
																		EffectiveDate,
																		PreviousRate,
																		Interval1,
																		IntervalN,
																		ConnectionFee
			)
				SELECT DISTINCT
					RateId,
					p_RateTableId,
					v_TimezonesID,
					Rate,
					RateN,
					p_EffectiveDate,
					Rate,
					Interval1,
					IntervalN,
					ConnectionFee
				FROM tmp_Rates_ rate
					INNER JOIN tblRate
						ON rate.code  = tblRate.Code
				WHERE tblRate.CodeDeckId = v_codedeckid_;

		ELSE

			IF p_delete_exiting_rate = 1
			THEN

				UPDATE
					tblRateTableRate
				SET
					EndDate = NOW()
				WHERE
					tblRateTableRate.RateTableId = p_RateTableId AND tblRateTableRate.TimezonesID = v_TimezonesID;


				CALL prc_ArchiveOldRateTableRate(p_RateTableId,v_TimezonesID,CONCAT(p_ModifiedBy,'|RateGenerator'));
			END IF;


			UPDATE tmp_Rates_ SET EffectiveDate = p_EffectiveDate;


			UPDATE
				tmp_Rates_ tr
			SET
				PreviousRate = (SELECT rtr.Rate FROM tblRateTableRate rtr JOIN tblRate r ON r.RateID=rtr.RateID WHERE rtr.RateTableID=p_RateTableId AND rtr.TimezonesID=v_TimezonesID AND r.Code=tr.Code AND rtr.EffectiveDate<tr.EffectiveDate ORDER BY rtr.EffectiveDate DESC,rtr.RateTableRateID DESC LIMIT 1);

			UPDATE
				tmp_Rates_ tr
			SET
				PreviousRate = (SELECT rtr.Rate FROM tblRateTableRateArchive rtr JOIN tblRate r ON r.RateID=rtr.RateID WHERE rtr.RateTableID=p_RateTableId AND rtr.TimezonesID=v_TimezonesID AND r.Code=tr.Code AND rtr.EffectiveDate<tr.EffectiveDate ORDER BY rtr.EffectiveDate DESC,rtr.RateTableRateID DESC LIMIT 1)
			WHERE
				PreviousRate is null;



			IF v_IncreaseEffectiveDate_ != v_DecreaseEffectiveDate_ THEN

				UPDATE tmp_Rates_
				SET
					tmp_Rates_.EffectiveDate =
					CASE WHEN tmp_Rates_.PreviousRate < tmp_Rates_.Rate THEN
						v_IncreaseEffectiveDate_
					WHEN tmp_Rates_.PreviousRate > tmp_Rates_.Rate THEN
						v_DecreaseEffectiveDate_
					ELSE p_EffectiveDate
					END
				;

			END IF;



			UPDATE
				tblRateTableRate
			INNER JOIN
				tblRate ON tblRate.RateId = tblRateTableRate.RateId
					AND tblRateTableRate.RateTableId = p_RateTableId

			INNER JOIN
				tmp_Rates_ as rate ON


				tblRateTableRate.EffectiveDate = p_EffectiveDate
			SET
				tblRateTableRate.EndDate = NOW()
			WHERE
				(
					(p_GroupBy != 'Desc'  AND rate.code = tblRate.Code )

					OR
					(p_GroupBy = 'Desc' AND rate.description = tblRate.description )
				)
				AND
				tblRateTableRate.TimezonesID = v_TimezonesID AND
				tblRateTableRate.RateTableId = p_RateTableId AND
				tblRate.CodeDeckId = v_codedeckid_ AND
				rate.rate != tblRateTableRate.Rate;


			CALL prc_ArchiveOldRateTableRate(p_RateTableId,v_TimezonesID,CONCAT(p_ModifiedBy,'|RateGenerator'));


			INSERT INTO tblRateTableRate (RateID,
																		RateTableId,
																		TimezonesID,
																		Rate,
																		RateN,
																		EffectiveDate,
																		PreviousRate,
																		Interval1,
																		IntervalN,
																		ConnectionFee
			)
				SELECT DISTINCT
					tblRate.RateId,
					p_RateTableId AS RateTableId,
					v_TimezonesID AS TimezonesID,
					rate.Rate,
					rate.RateN,
					rate.EffectiveDate,
					rate.PreviousRate,
					tblRate.Interval1,
					tblRate.IntervalN,
					rate.ConnectionFee
				FROM tmp_Rates_ rate
					INNER JOIN tblRate
						ON rate.code  = tblRate.Code
					LEFT JOIN tblRateTableRate tbl1
						ON tblRate.RateId = tbl1.RateId
							 AND tbl1.RateTableId = p_RateTableId
							 AND tbl1.TimezonesID = v_TimezonesID
					LEFT JOIN tblRateTableRate tbl2
						ON tblRate.RateId = tbl2.RateId
							 and tbl2.EffectiveDate = rate.EffectiveDate
							 AND tbl2.RateTableId = p_RateTableId
							 AND tbl2.TimezonesID = v_TimezonesID
					WHERE ( tbl1.RateTableRateID IS NULL
										OR
										(
											tbl2.RateTableRateID IS NULL
											AND  tbl1.EffectiveDate != rate.EffectiveDate

										)
							 )
							 AND tblRate.CodeDeckId = v_codedeckid_;


			UPDATE
				tblRateTableRate rtr
			INNER JOIN
				tblRate ON rtr.RateId  = tblRate.RateId
			LEFT JOIN
				tmp_Rates_ rate ON rate.Code=tblRate.Code
			SET
				rtr.EndDate = NOW()
			WHERE
				rate.Code is null AND rtr.RateTableId = p_RateTableId AND rtr.TimezonesID = v_TimezonesID AND rtr.EffectiveDate = rate.EffectiveDate AND tblRate.CodeDeckId = v_codedeckid_;







			UPDATE
				tblRateTableRate
			INNER JOIN
				tblRate ON tblRate.RateId = tblRateTableRate.RateId
					AND tblRateTableRate.RateTableId = p_RateTableId

			INNER JOIN
				tmp_Rates_ as rate ON


				tblRateTableRate.EffectiveDate = p_EffectiveDate
			SET
				tblRateTableRate.EndDate = NOW()
			WHERE
				(
					(p_GroupBy != 'Desc'  AND rate.code = tblRate.Code )

					OR
					(p_GroupBy = 'Desc' AND rate.description = tblRate.description )
				)
				AND
				tblRateTableRate.RateTableId = p_RateTableId AND
				tblRateTableRate.TimezonesID = v_TimezonesID AND
				tblRate.CodeDeckId = v_codedeckid_ AND
				rate.rate != tblRateTableRate.Rate;









			CALL prc_ArchiveOldRateTableRate(p_RateTableId,v_TimezonesID,CONCAT(p_ModifiedBy,'|RateGenerator'));

		END IF;


		DROP TEMPORARY TABLE IF EXISTS tmp_ALL_RateTableRate_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_ALL_RateTableRate_ AS (SELECT * FROM tblRateTableRate WHERE RateTableID=p_RateTableId AND TimezonesID=v_TimezonesID);

		UPDATE
			tmp_ALL_RateTableRate_ temp
		SET
			EndDate = (SELECT EffectiveDate FROM tblRateTableRate rtr WHERE rtr.RateTableID=p_RateTableId AND rtr.TimezonesID=v_TimezonesID AND rtr.RateID=temp.RateID AND rtr.EffectiveDate>temp.EffectiveDate ORDER BY rtr.EffectiveDate ASC,rtr.RateTableRateID ASC LIMIT 1)
		WHERE
			temp.RateTableId = p_RateTableId AND temp.TimezonesID = v_TimezonesID;

		UPDATE
			tblRateTableRate rtr
		INNER JOIN
			tmp_ALL_RateTableRate_ temp ON rtr.RateTableRateID=temp.RateTableRateID AND rtr.TimezonesID=temp.TimezonesID
		SET
			rtr.EndDate=temp.EndDate
		WHERE
			rtr.RateTableId=p_RateTableId AND
			rtr.TimezonesID=v_TimezonesID;



		CALL prc_ArchiveOldRateTableRate(p_RateTableId,v_TimezonesID,CONCAT(p_ModifiedBy,'|RateGenerator'));

		UPDATE tblRateTable
		SET RateGeneratorID = p_RateGeneratorId,
			TrunkID = v_trunk_,
			CodeDeckId = v_codedeckid_,
			updated_at = now()
		WHERE RateTableID = p_RateTableId;



		INSERT INTO tmp_JobLog_ (Message) VALUES (p_RateTableId);


		SELECT * FROM tmp_JobLog_;

		COMMIT;


		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;



	END//
DELIMITER ;


DROP PROCEDURE IF EXISTS `prc_WSGenerateRateTableWithPrefix`;
DELIMITER //
CREATE PROCEDURE `prc_WSGenerateRateTableWithPrefix`(
	IN `p_jobId` INT,
	IN `p_RateGeneratorId` INT,
	IN `p_RateTableId` INT,
	IN `p_TimezonesID` VARCHAR(50),
	IN `p_rateTableName` VARCHAR(200),
	IN `p_EffectiveDate` VARCHAR(10),
	IN `p_delete_exiting_rate` INT,
	IN `p_EffectiveRate` VARCHAR(50),
	IN `p_GroupBy` VARCHAR(50),
	IN `p_ModifiedBy` VARCHAR(50),
	IN `p_IsMerge` INT,
	IN `p_TakePrice` INT,
	IN `p_MergeInto` INT

)
GenerateRateTable:BEGIN


		DECLARE i INTEGER;
		DECLARE v_RTRowCount_ INT;
		DECLARE v_RatePosition_ INT;
		DECLARE v_Use_Preference_ INT;
		DECLARE v_CurrencyID_ INT;
		DECLARE v_CompanyCurrencyID_ INT;
		DECLARE v_Average_ TINYINT;
		DECLARE v_CompanyId_ INT;
		DECLARE v_codedeckid_ INT;
		DECLARE v_trunk_ INT;
		DECLARE v_rateRuleId_ INT;
		DECLARE v_RateGeneratorName_ VARCHAR(200);
		DECLARE v_pointer_ INT ;
		DECLARE v_rowCount_ INT ;

		DECLARE v_IncreaseEffectiveDate_ DATETIME ;
		DECLARE v_DecreaseEffectiveDate_ DATETIME ;


		DECLARE v_tmp_code_cnt int ;
		DECLARE v_tmp_code_pointer int;
		DECLARE v_p_code varchar(50);
		DECLARE v_Codlen_ int;
		DECLARE v_p_code__ VARCHAR(50);
		DECLARE v_Commit int;
		DECLARE v_TimezonesID int;

		DECLARE EXIT HANDLER FOR SQLEXCEPTION
		BEGIN
			show warnings;
			ROLLBACK;
			INSERT INTO tmp_JobLog_ (Message) VALUES ('RateTable generation failed');


		END;

		DROP TEMPORARY TABLE IF EXISTS tmp_JobLog_;
		CREATE TEMPORARY TABLE tmp_JobLog_ (
			Message longtext
		);

		SET @@session.collation_connection='utf8_unicode_ci';
		SET @@session.character_set_client='utf8';
		SET SESSION group_concat_max_len = 1000000;

		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;



		SET p_EffectiveDate = CAST(p_EffectiveDate AS DATE);
		SET v_TimezonesID = IF(p_IsMerge=1,p_MergeInto,p_TimezonesID);


		IF p_rateTableName IS NOT NULL
		THEN


			SET v_RTRowCount_ = (SELECT
														 COUNT(*)
													 FROM tblRateTable
													 WHERE RateTableName = p_rateTableName
																 AND CompanyId = (SELECT
																										CompanyId
																									FROM tblRateGenerator
																									WHERE RateGeneratorID = p_RateGeneratorId));

			IF v_RTRowCount_ > 0
			THEN
				INSERT INTO tmp_JobLog_ (Message) VALUES ('RateTable Name is already exist, Please try using another RateTable Name');

				LEAVE GenerateRateTable;
			END IF;
		END IF;

		DROP TEMPORARY TABLE IF EXISTS tmp_Rates_;
		CREATE TEMPORARY TABLE tmp_Rates_  (
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			description VARCHAR(200) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			rateN DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			PreviousRate DECIMAL(18, 6),
			EffectiveDate DATE DEFAULT NULL,
			INDEX tmp_Rates_code (`code`),
			INDEX  tmp_Rates_description (`description`),
			UNIQUE KEY `unique_code` (`code`)

		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Rates2_;
		CREATE TEMPORARY TABLE tmp_Rates2_  (
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			description VARCHAR(200) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			rateN DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			PreviousRate DECIMAL(18, 6),
			EffectiveDate DATE DEFAULT NULL,
			INDEX tmp_Rates2_code (`code`),
			INDEX  tmp_Rates_description (`description`)
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_Rates3_;
		CREATE TEMPORARY TABLE tmp_Rates3_  (
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			description VARCHAR(200) COLLATE utf8_unicode_ci,
			UNIQUE KEY `unique_code` (`code`),
			INDEX  tmp_Rates_description (`description`)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_Codedecks_;
		CREATE TEMPORARY TABLE tmp_Codedecks_ (
			CodeDeckId INT
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Raterules_;

		CREATE TEMPORARY TABLE tmp_Raterules_  (
			rateruleid INT,
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			description VARCHAR(200) COLLATE utf8_unicode_ci,
			RowNo INT,
         `Order` INT,
			INDEX tmp_Raterules_code (`code`),
			INDEX tmp_Raterules_rateruleid (`rateruleid`),
			INDEX tmp_Raterules_RowNo (`RowNo`)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_Raterules_dup;
		CREATE TEMPORARY TABLE tmp_Raterules_dup  (
			rateruleid INT,
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			description VARCHAR(200) COLLATE utf8_unicode_ci,
			RowNo INT,
         `Order` INT,
			INDEX tmp_Raterules_code (`code`,`description`),
			INDEX tmp_Raterules_rateruleid (`rateruleid`),
			INDEX tmp_Raterules_RowNo (`RowNo`)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_Vendorrates_;
		CREATE TEMPORARY TABLE tmp_Vendorrates_  (
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			rateN DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			AccountId INT,
			RowNo INT,
			PreferenceRank INT,
			INDEX tmp_Vendorrates_code (`code`),
			INDEX tmp_Vendorrates_rate (`rate`)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_VRatesstage2_;
		CREATE TEMPORARY TABLE tmp_VRatesstage2_  (
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci,
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			description VARCHAR(200) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			rateN DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			FinalRankNumber int,
			INDEX tmp_Vendorrates_stage2__code (`RowCode`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_dupVRatesstage2_;
		CREATE TEMPORARY TABLE tmp_dupVRatesstage2_  (
			RowCode VARCHAR(50)  COLLATE utf8_unicode_ci,
			description VARCHAR(200) COLLATE utf8_unicode_ci,
			FinalRankNumber int,
			INDEX tmp_dupVendorrates_stage2__code (`RowCode`)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_Vendorrates_stage3_;
		CREATE TEMPORARY TABLE tmp_Vendorrates_stage3_  (
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci,
			description VARCHAR(200) COLLATE utf8_unicode_ci,
			rate DECIMAL(18, 6),
			rateN DECIMAL(18, 6),
			ConnectionFee DECIMAL(18, 6),
			INDEX tmp_Vendorrates_stage2__code (`RowCode`)
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_code_;
		CREATE TEMPORARY TABLE tmp_code_  (
			code VARCHAR(50) COLLATE utf8_unicode_ci,
			INDEX tmp_code_code (`code`)
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_all_code_;
		CREATE TEMPORARY TABLE tmp_all_code_ (
			RowCode  varchar(50) COLLATE utf8_unicode_ci,
			Code  varchar(50) COLLATE utf8_unicode_ci,
			RowNo int,
			INDEX Index2 (Code)
		);



		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_stage_;
		CREATE TEMPORARY TABLE tmp_VendorRate_stage_ (
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci,
			AccountId INT ,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) COLLATE utf8_unicode_ci,
			Rate DECIMAL(18,6) ,
			RateN DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			MaxMatchRank int ,
			prev_prev_RowCode VARCHAR(50),
			prev_AccountID int
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorRate_;
		CREATE TEMPORARY TABLE tmp_VendorRate_ (
			AccountId INT ,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) COLLATE utf8_unicode_ci,
			Rate DECIMAL(18,6) ,
			RateN DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_final_VendorRate_;
		CREATE TEMPORARY TABLE tmp_final_VendorRate_ (
			AccountId INT ,
			AccountName VARCHAR(100) ,
			Code VARCHAR(50) COLLATE utf8_unicode_ci,
			Rate DECIMAL(18,6) ,
			RateN DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate DATETIME ,
			Description VARCHAR(255),
			Preference INT,
			RowCode VARCHAR(50) COLLATE utf8_unicode_ci,
			FinalRankNumber int,
			INDEX IX_CODE (RowCode)
		);


		DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates_GroupBy_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates_GroupBy_(
			AccountId int,
			AccountName varchar(200),
			Code LONGTEXT,
			Description varchar(200) ,
			Rate DECIMAL(18,6) ,
			RateN DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate date,
			TrunkID int,
			TimezonesID int,
			CountryID int,
			RateID int,
			Preference int
		);

		DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates_(
			AccountId int,
			AccountName varchar(200),
			Code varchar(50) COLLATE utf8_unicode_ci,
			Description varchar(200) ,
			Rate DECIMAL(18,6) ,
			RateN DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate date,
			TrunkID int,
			TimezonesID int,
			CountryID int,
			RateID int,
			Preference int,
			INDEX IX_CODE (Code)
		);
		DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates1_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates1_(
			AccountId int,
			AccountName varchar(200),
			Code varchar(50),
			Description varchar(200),
			Rate DECIMAL(18,6) ,
			RateN DECIMAL(18,6) ,
			ConnectionFee DECIMAL(18,6) ,
			EffectiveDate date,
			TrunkID int,
			TimezonesID int,
			CountryID int,
			RateID int,
			Preference int,
			INDEX IX_Code (Code),
			INDEX tmp_VendorCurrentRates_AccountId (`AccountId`,`TrunkID`,`RateId`,`EffectiveDate`)
		);

		SELECT CurrencyID INTO v_CurrencyID_ FROM  tblRateGenerator WHERE RateGeneratorId = p_RateGeneratorId;


		SELECT IFNULL(REPLACE(JSON_EXTRACT(Options, '$.IncreaseEffectiveDate'),'"',''), p_EffectiveDate) , IFNULL(REPLACE(JSON_EXTRACT(Options, '$.DecreaseEffectiveDate'),'"',''), p_EffectiveDate)   INTO v_IncreaseEffectiveDate_ , v_DecreaseEffectiveDate_  FROM tblJob WHERE Jobid = p_jobId;


		IF v_IncreaseEffectiveDate_ is null OR v_IncreaseEffectiveDate_ = '' THEN

			SET v_IncreaseEffectiveDate_ = p_EffectiveDate;

		END IF;

		IF v_DecreaseEffectiveDate_ is null OR v_DecreaseEffectiveDate_ = '' THEN

			SET v_DecreaseEffectiveDate_ = p_EffectiveDate;

		END IF;


		SELECT
			UsePreference,
			rateposition,
			companyid ,
			CodeDeckId,
			tblRateGenerator.TrunkID,
			tblRateGenerator.UseAverage  ,
			tblRateGenerator.RateGeneratorName INTO v_Use_Preference_, v_RatePosition_, v_CompanyId_, v_codedeckid_, v_trunk_, v_Average_, v_RateGeneratorName_
		FROM tblRateGenerator
		WHERE RateGeneratorId = p_RateGeneratorId;


		SELECT CurrencyId INTO v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = v_CompanyId_;




		INSERT INTO tmp_Raterules_
			SELECT
				rateruleid,
				tblRateRule.Code,
				tblRateRule.Description,
				@row_num := @row_num+1 AS RowID,
            tblRateRule.`Order`
			FROM tblRateRule,(SELECT @row_num := 0) x
			WHERE rategeneratorid = p_RateGeneratorId
			ORDER BY tblRateRule.`Order` ASC;


		insert into tmp_Raterules_dup (			rateruleid ,		code ,		description ,		RowNo 		,   `Order`)
			select rateruleid ,		code ,		description ,		RowNo, `Order` from tmp_Raterules_;


		INSERT INTO tmp_Codedecks_
			SELECT DISTINCT
				tblVendorTrunk.CodeDeckId
			FROM tblRateRule
				INNER JOIN tblRateRuleSource
					ON tblRateRule.RateRuleId = tblRateRuleSource.RateRuleId
				INNER JOIN tblAccount
					ON tblAccount.AccountID = tblRateRuleSource.AccountId and tblAccount.IsVendor = 1
				JOIN tblVendorTrunk
					ON tblAccount.AccountId = tblVendorTrunk.AccountID
						 AND  tblVendorTrunk.TrunkID = v_trunk_
						 AND tblVendorTrunk.Status = 1
			WHERE RateGeneratorId = p_RateGeneratorId;

		SET v_pointer_ = 1;
		SET v_rowCount_ = (SELECT COUNT(distinct concat(Code,Description) ) FROM tmp_Raterules_);







		insert into tmp_code_
			SELECT
				tblRate.code
			FROM tblRate
				JOIN tmp_Codedecks_ cd
					ON tblRate.CodeDeckId = cd.CodeDeckId
				JOIN tmp_Raterules_ rr
					ON ( rr.code != '' AND tblRate.Code LIKE (REPLACE(rr.code,'*', '%%')) )
						 OR
						 ( rr.description != '' AND tblRate.Description LIKE (REPLACE(rr.description,'*', '%%')) )

			Order by tblRate.code ;









		SELECT CurrencyId INTO v_CompanyCurrencyID_ FROM  tblCompany WHERE CompanyID = v_CompanyId_;
		SET @IncludeAccountIds = (SELECT GROUP_CONCAT(AccountId) from tblRateRule rr inner join  tblRateRuleSource rrs on rr.RateRuleId = rrs.RateRuleId where rr.RateGeneratorId = p_RateGeneratorId ) ;



		IF(p_IsMerge = 1)
		THEN




			INSERT INTO tmp_VendorCurrentRates1_
				Select DISTINCT AccountId,MAX(AccountName) AS AccountName,MAX(Code) AS Code,MAX(Description) AS Description, ROUND(IF(p_TakePrice=1,MAX(Rate),MIN(Rate)), 6) AS Rate, ROUND(IF(p_TakePrice=1,MAX(RateN),MIN(RateN)), 6) AS RateN,IF(p_TakePrice=1,MAX(ConnectionFee),MIN(ConnectionFee)) AS ConnectionFee,EffectiveDate,TrunkID,p_MergeInto AS TimezonesID,MAX(CountryID) AS CountryID,RateID,MAX(Preference) AS Preference
				FROM (
							 SELECT  tblVendorRate.AccountId,tblAccount.AccountName, tblRate.Code, tblRate.Description,
																																					CASE WHEN  tblAccount.CurrencyId = v_CurrencyID_
																																						THEN
																																							tblVendorRate.Rate
																																					WHEN  v_CompanyCurrencyID_ = v_CurrencyID_
																																						THEN
																																							(
																																								( tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ) )
																																							)
																																					ELSE
																																						(

																																							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = v_CurrencyID_ and  CompanyID = v_CompanyId_ )
																																							* (tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ))
																																						)
																																					END as Rate,
																																					CASE WHEN  tblAccount.CurrencyId = v_CurrencyID_
																																						THEN
																																							tblVendorRate.RateN
																																					WHEN  v_CompanyCurrencyID_ = v_CurrencyID_
																																						THEN
																																							(
																																								( tblVendorRate.rateN  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ) )
																																							)
																																					ELSE
																																						(

																																							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = v_CurrencyID_ and  CompanyID = v_CompanyId_ )
																																							* (tblVendorRate.rateN  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ))
																																						)
																																					END as RateN,
								 ConnectionFee,
																																					DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') AS EffectiveDate,
								 tblVendorRate.TrunkID, tblVendorRate.TimezonesID, tblRate.CountryID, tblRate.RateID,IFNULL(vp.Preference, 5) AS Preference,
																																					@row_num := IF(@prev_AccountId = tblVendorRate.AccountID AND @prev_TrunkID = tblVendorRate.TrunkID AND @prev_RateId = tblVendorRate.RateID AND @prev_EffectiveDate >= tblVendorRate.EffectiveDate, @row_num + 1, 1) AS RowID,
								 @prev_AccountId := tblVendorRate.AccountID,
								 @prev_TrunkID := tblVendorRate.TrunkID,
								 @prev_TimezonesID := tblVendorRate.TimezonesID,
								 @prev_RateId := tblVendorRate.RateID,
								 @prev_EffectiveDate := tblVendorRate.EffectiveDate
							 FROM      tblVendorRate

								 Inner join tblVendorTrunk vt on vt.CompanyID = v_CompanyId_ AND vt.AccountID = tblVendorRate.AccountID and

																								 vt.Status =  1 and vt.TrunkID =  v_trunk_
								 Inner join tblTimezones t on t.TimezonesID = tblVendorRate.TimezonesID AND t.Status = 1
								 inner join tmp_Codedecks_ tcd on vt.CodeDeckId = tcd.CodeDeckId
								 INNER JOIN tblAccount   ON  tblAccount.CompanyID = v_CompanyId_ AND tblVendorRate.AccountId = tblAccount.AccountID and tblAccount.IsVendor = 1
								 INNER JOIN tblRate ON tblRate.CompanyID = v_CompanyId_  AND tblRate.CodeDeckId = vt.CodeDeckId  AND    tblVendorRate.RateId = tblRate.RateID
								 INNER JOIN tmp_code_ tcode ON tcode.Code  = tblRate.Code
								 LEFT JOIN tblVendorPreference vp
									 ON vp.AccountId = tblVendorRate.AccountId
											AND vp.TrunkID = tblVendorRate.TrunkID
											AND vp.TimezonesID = tblVendorRate.TimezonesID
											AND vp.RateId = tblVendorRate.RateId
								 LEFT OUTER JOIN tblVendorBlocking AS blockCode   ON tblVendorRate.RateId = blockCode.RateId
																																		 AND tblVendorRate.AccountId = blockCode.AccountId
																																		 AND tblVendorRate.TrunkID = blockCode.TrunkID
																																		 AND tblVendorRate.TimezonesID = blockCode.TimezonesID
								 LEFT OUTER JOIN tblVendorBlocking AS blockCountry    ON tblRate.CountryID = blockCountry.CountryId
																																				 AND tblVendorRate.AccountId = blockCountry.AccountId
																																				 AND tblVendorRate.TrunkID = blockCountry.TrunkID
																																				 AND tblVendorRate.TimezonesID = blockCountry.TimezonesID

								 ,(SELECT @row_num := 1,  @prev_AccountId := '',@prev_TrunkID := '',@prev_TimezonesID := '', @prev_RateId := '', @prev_EffectiveDate := '') x

							 WHERE
								 (
									 (p_EffectiveRate = 'now' AND EffectiveDate <= NOW())
									 OR
									 (p_EffectiveRate = 'future' AND EffectiveDate > NOW()  )
									 OR
									 (	 p_EffectiveRate = 'effective' AND EffectiveDate <= p_EffectiveDate
											 AND ( tblVendorRate.EndDate IS NULL OR (tblVendorRate.EndDate > DATE(p_EffectiveDate)) )
									 )
								 )
								 AND ( tblVendorRate.EndDate IS NULL OR tblVendorRate.EndDate > now() )
								 AND tblAccount.IsVendor = 1
								 AND tblAccount.Status = 1
								 AND tblAccount.CurrencyId is not NULL
								 AND tblVendorRate.TrunkID = v_trunk_
								 AND FIND_IN_SET(tblVendorRate.TimezonesID,p_TimezonesID) != 0
								 AND blockCode.RateId IS NULL
								 AND blockCountry.CountryId IS NULL
								 AND ( @IncludeAccountIds = NULL
											 OR ( @IncludeAccountIds IS NOT NULL
														AND FIND_IN_SET(tblVendorRate.AccountId,@IncludeAccountIds) > 0
											 )
								 )
							 ORDER BY tblVendorRate.AccountId, tblVendorRate.TrunkID, tblVendorRate.TimezonesID, tblVendorRate.RateId, tblVendorRate.EffectiveDate DESC
						 ) tbl
				GROUP BY RateID, AccountId, TrunkID, EffectiveDate
				order by Code asc;

		ELSE

			INSERT INTO tmp_VendorCurrentRates1_
				Select DISTINCT AccountId,AccountName,Code,Description, Rate, RateN,ConnectionFee,EffectiveDate,TrunkID,TimezonesID,CountryID,RateID,Preference
				FROM (
							 SELECT  tblVendorRate.AccountId,tblAccount.AccountName, tblRate.Code, tblRate.Description,
																																					CASE WHEN  tblAccount.CurrencyId = v_CurrencyID_
																																						THEN
																																							tblVendorRate.Rate
																																					WHEN  v_CompanyCurrencyID_ = v_CurrencyID_
																																						THEN
																																							(
																																								( tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ) )
																																							)
																																					ELSE
																																						(

																																							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = v_CurrencyID_ and  CompanyID = v_CompanyId_ )
																																							* (tblVendorRate.rate  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ))
																																						)
																																					END as Rate,
																																					CASE WHEN  tblAccount.CurrencyId = v_CurrencyID_
																																						THEN
																																							tblVendorRate.RateN
																																					WHEN  v_CompanyCurrencyID_ = v_CurrencyID_
																																						THEN
																																							(
																																								( tblVendorRate.rateN  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ) )
																																							)
																																					ELSE
																																						(

																																							(Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = v_CurrencyID_ and  CompanyID = v_CompanyId_ )
																																							* (tblVendorRate.rateN  / (Select Value from tblCurrencyConversion where tblCurrencyConversion.CurrencyId = tblAccount.CurrencyId and  CompanyID = v_CompanyId_ ))
																																						)
																																					END as RateN,
								 ConnectionFee,
																																					DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') AS EffectiveDate,
								 tblVendorRate.TrunkID, tblVendorRate.TimezonesID, tblRate.CountryID, tblRate.RateID,IFNULL(vp.Preference, 5) AS Preference,
																																					@row_num := IF(@prev_AccountId = tblVendorRate.AccountID AND @prev_TrunkID = tblVendorRate.TrunkID AND @prev_RateId = tblVendorRate.RateID AND @prev_EffectiveDate >= tblVendorRate.EffectiveDate, @row_num + 1, 1) AS RowID,
								 @prev_AccountId := tblVendorRate.AccountID,
								 @prev_TrunkID := tblVendorRate.TrunkID,
								 @prev_TimezonesID := tblVendorRate.TimezonesID,
								 @prev_RateId := tblVendorRate.RateID,
								 @prev_EffectiveDate := tblVendorRate.EffectiveDate
							 FROM      tblVendorRate

								 Inner join tblVendorTrunk vt on vt.CompanyID = v_CompanyId_ AND vt.AccountID = tblVendorRate.AccountID and

																								 vt.Status =  1 and vt.TrunkID =  v_trunk_
								 Inner join tblTimezones t on t.TimezonesID = tblVendorRate.TimezonesID AND t.Status = 1
								 inner join tmp_Codedecks_ tcd on vt.CodeDeckId = tcd.CodeDeckId
								 INNER JOIN tblAccount   ON  tblAccount.CompanyID = v_CompanyId_ AND tblVendorRate.AccountId = tblAccount.AccountID and tblAccount.IsVendor = 1
								 INNER JOIN tblRate ON tblRate.CompanyID = v_CompanyId_  AND tblRate.CodeDeckId = vt.CodeDeckId  AND    tblVendorRate.RateId = tblRate.RateID
								 INNER JOIN tmp_code_ tcode ON tcode.Code  = tblRate.Code
								 LEFT JOIN tblVendorPreference vp
									 ON vp.AccountId = tblVendorRate.AccountId
											AND vp.TrunkID = tblVendorRate.TrunkID
											AND vp.TimezonesID = tblVendorRate.TimezonesID
											AND vp.RateId = tblVendorRate.RateId
								 LEFT OUTER JOIN tblVendorBlocking AS blockCode   ON tblVendorRate.RateId = blockCode.RateId
																																		 AND tblVendorRate.AccountId = blockCode.AccountId
																																		 AND tblVendorRate.TrunkID = blockCode.TrunkID
																																		 AND tblVendorRate.TimezonesID = blockCode.TimezonesID
								 LEFT OUTER JOIN tblVendorBlocking AS blockCountry    ON tblRate.CountryID = blockCountry.CountryId
																																				 AND tblVendorRate.AccountId = blockCountry.AccountId
																																				 AND tblVendorRate.TrunkID = blockCountry.TrunkID
																																				 AND tblVendorRate.TimezonesID = blockCountry.TimezonesID

								 ,(SELECT @row_num := 1,  @prev_AccountId := '',@prev_TrunkID := '',@prev_TimezonesID := '', @prev_RateId := '', @prev_EffectiveDate := '') x

							 WHERE
								 (
									 (p_EffectiveRate = 'now' AND EffectiveDate <= NOW())
									 OR
									 (p_EffectiveRate = 'future' AND EffectiveDate > NOW()  )
									 OR
									 (	 p_EffectiveRate = 'effective' AND EffectiveDate <= p_EffectiveDate
											 AND ( tblVendorRate.EndDate IS NULL OR (tblVendorRate.EndDate > DATE(p_EffectiveDate)) )
									 )
								 )
								 AND ( tblVendorRate.EndDate IS NULL OR tblVendorRate.EndDate > now() )
								 AND tblAccount.IsVendor = 1
								 AND tblAccount.Status = 1
								 AND tblAccount.CurrencyId is not NULL
								 AND tblVendorRate.TrunkID = v_trunk_
								 AND tblVendorRate.TimezonesID = v_TimezonesID
								 AND blockCode.RateId IS NULL
								 AND blockCountry.CountryId IS NULL
								 AND ( @IncludeAccountIds = NULL
											 OR ( @IncludeAccountIds IS NOT NULL
														AND FIND_IN_SET(tblVendorRate.AccountId,@IncludeAccountIds) > 0
											 )
								 )
							 ORDER BY tblVendorRate.AccountId, tblVendorRate.TrunkID, tblVendorRate.TimezonesID, tblVendorRate.RateId, tblVendorRate.EffectiveDate DESC
						 ) tbl
				order by Code asc;

		END IF;




		INSERT INTO tmp_VendorCurrentRates_
		Select AccountId,AccountName,Code,Description, Rate, RateN,ConnectionFee,EffectiveDate,TrunkID,TimezonesID,CountryID,RateID,Preference
		FROM (
					 SELECT * ,
						 @row_num := IF(@prev_AccountId = AccountID AND @prev_TrunkID = TrunkID AND @prev_TimezonesID = TimezonesID AND @prev_RateId = RateID AND @prev_EffectiveDate >= EffectiveDate, @row_num + 1, 1) AS RowID,
						 @prev_AccountId := AccountID,
						 @prev_TrunkID := TrunkID,
						 @prev_TimezonesID := TimezonesID,
						 @prev_RateId := RateID,
						 @prev_EffectiveDate := EffectiveDate
					 FROM tmp_VendorCurrentRates1_
						 ,(SELECT @row_num := 1,  @prev_AccountId := 0 ,@prev_TrunkID := 0 ,@prev_TimezonesID := 0, @prev_RateId := 0, @prev_EffectiveDate := '') x
					 ORDER BY AccountId, TrunkID, TimezonesID, RateId, EffectiveDate DESC
				 ) tbl
		WHERE RowID = 1
		order by Code asc;



		IF p_GroupBy = 'Desc'
		THEN





			INSERT INTO tmp_VendorCurrentRates_GroupBy_
				Select AccountId,max(AccountName),max(Code),Description,max(Rate),max(RateN),max(ConnectionFee),max(EffectiveDate),TrunkID,TimezonesID,max(CountryID),max(RateID),max(Preference)
				FROM
				(
					Select AccountId,AccountName,r.Code,r.Description, Rate, RateN,ConnectionFee,EffectiveDate,TrunkID,TimezonesID,r.CountryID,r.RateID,Preference
					FROM tmp_VendorCurrentRates_ v
					Inner join  tblRate r   on r.CodeDeckId = v_codedeckid_ AND r.Code = v.Code
				) tmp
				GROUP BY AccountId, TrunkID, TimezonesID, Description
				order by Description asc;




				truncate table tmp_VendorCurrentRates_;

				INSERT INTO tmp_VendorCurrentRates_ (AccountId,AccountName,Code,Description, Rate, RateN,ConnectionFee,EffectiveDate,TrunkID,TimezonesID,CountryID,RateID,Preference)
			  		SELECT AccountId,AccountName,Code,Description, Rate, RateN,ConnectionFee,EffectiveDate,TrunkID,TimezonesID,CountryID,RateID,Preference
					FROM tmp_VendorCurrentRates_GroupBy_;


		END IF;


		insert into tmp_VendorRate_
			select
				AccountId ,
				AccountName ,
				Code ,
				Rate ,
				RateN ,
				ConnectionFee,
				EffectiveDate ,
				Description ,
				Preference,
				Code as RowCode
			from tmp_VendorCurrentRates_;

		WHILE v_pointer_ <= v_rowCount_
		DO

			SET v_rateRuleId_ = (SELECT rateruleid FROM tmp_Raterules_ rr WHERE rr.RowNo = v_pointer_);


			INSERT INTO tmp_Rates2_ (code,description,rate,rateN,ConnectionFee)
				select  code,description,rate,rateN,ConnectionFee from tmp_Rates_;

				IF p_GroupBy = 'Desc'
				THEN


						INSERT IGNORE INTO tmp_Rates3_ (code,description)
						 select distinct r.code,r.description
						from tmp_VendorCurrentRates1_  tmpvr
						Inner join  tblRate r   on r.CodeDeckId = v_codedeckid_ AND r.Code = tmpvr.Code
						inner JOIN tmp_Raterules_ rr ON rr.RateRuleId = v_rateRuleId_ and
																 (
																	 ( rr.code != '' AND r.Code LIKE (REPLACE(rr.code,'*', '%%')) )
																	 OR
																	 ( rr.description != '' AND r.Description LIKE (REPLACE(rr.description,'*', '%%')) )
																 )
						left JOIN tmp_Raterules_dup rr2 ON rr2.Order > rr.Order and
																		 (
																			 ( rr2.code != '' AND r.Code LIKE (REPLACE(rr2.code,'*', '%%')) )
																			 OR
																			 ( rr2.description != '' AND r.Description LIKE (REPLACE(rr2.description,'*', '%%')) )
																		 )
						inner JOIN tblRateRuleSource rrs ON  rrs.RateRuleId = rr.rateruleid  and rrs.AccountId = tmpvr.AccountId
						where rr2.code is null;

				END IF;

			truncate tmp_final_VendorRate_;

			IF( v_Use_Preference_ = 0 )
			THEN

				insert into tmp_final_VendorRate_
					SELECT
						AccountId ,
						AccountName ,
						Code ,
						Rate ,
						RateN ,
						ConnectionFee,
						EffectiveDate ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								vr.AccountId ,
								vr.AccountName ,
								vr.Code ,
								vr.Rate ,
								vr.RateN ,
								vr.ConnectionFee,
								vr.EffectiveDate ,
								vr.Description ,
								vr.Preference,
								vr.RowCode,
								CASE WHEN p_GroupBy = 'Desc'  THEN
													@rank := CASE WHEN ( @prev_Description = vr.Description  AND @prev_Rate <=  vr.Rate ) THEN @rank+1
													 ELSE
														 1
													 END

								ELSE	@rank := CASE WHEN ( @prev_RowCode = vr.RowCode  AND @prev_Rate <=  vr.Rate ) THEN @rank+1

													 ELSE
														 1
													 END
								END
									AS FinalRankNumber,
								@prev_RowCode  := vr.RowCode,
								@prev_Description  := vr.Description,
								@prev_Rate  := vr.Rate
							from (
										 select distinct tmpvr.*
										 from tmp_VendorRate_  tmpvr
											 inner JOIN tmp_Raterules_ rr ON rr.RateRuleId = v_rateRuleId_ and
																											 (
																												 ( rr.code != '' AND tmpvr.RowCode LIKE (REPLACE(rr.code,'*', '%%')) )
																												 OR
																												 ( rr.description != '' AND tmpvr.Description LIKE (REPLACE(rr.description,'*', '%%')) )
																											 )
											 left JOIN tmp_Raterules_dup rr2 ON rr2.Order > rr.Order and
																													 (
																														 ( rr2.code != '' AND tmpvr.RowCode LIKE (REPLACE(rr2.code,'*', '%%')) )
																														 OR
																														 ( rr2.description != '' AND tmpvr.Description LIKE (REPLACE(rr2.description,'*', '%%')) )
																													 )
											 inner JOIN tblRateRuleSource rrs ON  rrs.RateRuleId = rr.rateruleid  and rrs.AccountId = tmpvr.AccountId
										 where rr2.code is null

									 ) vr
								,(SELECT @rank := 0 , @prev_RowCode := '' , @prev_Rate := 0 , @prev_Description := '' ) x
							order by
								CASE WHEN p_GroupBy = 'Desc'  THEN
									vr.Description
								ELSE
									vr.RowCode
								END , vr.Rate,vr.AccountId

						) tbl1
					where FinalRankNumber <= v_RatePosition_;

			ELSE

				insert into tmp_final_VendorRate_
					SELECT
						AccountId ,
						AccountName ,
						Code ,
						Rate ,
						RateN ,
						ConnectionFee,
						EffectiveDate ,
						Description ,
						Preference,
						RowCode,
						FinalRankNumber
					from
						(
							SELECT
								vr.AccountId ,
								vr.AccountName ,
								vr.Code ,
								vr.Rate ,
								vr.RateN ,
								vr.ConnectionFee,
								vr.EffectiveDate ,
								vr.Description ,
								vr.Preference,
								vr.RowCode,

								CASE WHEN p_GroupBy = 'Desc'  THEN

										@preference_rank := CASE WHEN (@prev_Description  = vr.Description  AND @prev_Preference > vr.Preference  )   THEN @preference_rank + 1
																		WHEN (@prev_Description  = vr.Description  AND @prev_Preference = vr.Preference AND @prev_Rate <= vr.Rate) THEN @preference_rank + 1

																		ELSE 1 END
								ELSE
												@preference_rank := CASE WHEN (@prev_Code  = vr.RowCode  AND @prev_Preference > vr.Preference  )   THEN @preference_rank + 1
																		WHEN (@prev_Code  = vr.RowCode  AND @prev_Preference = vr.Preference AND @prev_Rate <= vr.Rate) THEN @preference_rank + 1

																		ELSE 1 END
								END

								AS FinalRankNumber,
								@prev_Code := vr.RowCode,
								@prev_Description  := vr.Description,
								@prev_Preference := vr.Preference,
								@prev_Rate := vr.Rate
							from (
										 select distinct tmpvr.*
										 from tmp_VendorRate_  tmpvr
											 inner JOIN tmp_Raterules_ rr ON rr.RateRuleId = v_rateRuleId_ and
																											 (
																												 ( rr.code != '' AND tmpvr.RowCode LIKE (REPLACE(rr.code,'*', '%%')) )
																												 OR
																												 ( rr.description != '' AND tmpvr.Description LIKE (REPLACE(rr.description,'*', '%%')) )
																											 )
											 left JOIN tmp_Raterules_dup rr2 ON rr2.Order > rr.Order and
																													 (
																														 ( rr2.code != '' AND tmpvr.RowCode LIKE (REPLACE(rr2.code,'*', '%%')) )
																														 OR
																														 ( rr2.description != '' AND tmpvr.Description LIKE (REPLACE(rr2.description,'*', '%%')) )
																													 )
											 inner JOIN tblRateRuleSource rrs ON  rrs.RateRuleId = rr.rateruleid  and rrs.AccountId = tmpvr.AccountId
										 where rr2.code is null

									 ) vr

								,(SELECT @preference_rank := 0 , @prev_Code := ''  , @prev_Preference := 5,  @prev_Rate := 0, @prev_Description := '') x
							order by
							CASE WHEN p_GroupBy = 'Desc'  THEN
									vr.Description
								ELSE
									vr.RowCode
								END , vr.Preference DESC ,vr.Rate ASC ,vr.AccountId ASC
						) tbl1
					where FinalRankNumber <= v_RatePosition_;

			END IF;



			truncate   tmp_VRatesstage2_;

			INSERT INTO tmp_VRatesstage2_
				SELECT
					vr.RowCode,
					vr.code,
					vr.description,
					vr.rate,
					vr.rateN,
					vr.ConnectionFee,
					vr.FinalRankNumber
				FROM tmp_final_VendorRate_ vr
					left join tmp_Rates2_ rate on rate.Code = vr.RowCode
				WHERE  rate.code is null
				order by vr.FinalRankNumber desc ;



			IF v_Average_ = 0
			THEN


				IF p_GroupBy = 'Desc'
				THEN

						insert into tmp_dupVRatesstage2_
						SELECT max(RowCode) , description,   MAX(FinalRankNumber) AS MaxFinalRankNumber
						FROM tmp_VRatesstage2_ GROUP BY description;

					truncate tmp_Vendorrates_stage3_;
					INSERT INTO tmp_Vendorrates_stage3_
						select  vr.RowCode as RowCode ,vr.description , vr.rate as rate , vr.rateN as rateN , vr.ConnectionFee as  ConnectionFee
						from tmp_VRatesstage2_ vr
							INNER JOIN tmp_dupVRatesstage2_ vr2
								ON (vr.description = vr2.description AND  vr.FinalRankNumber = vr2.FinalRankNumber);


				ELSE

					insert into tmp_dupVRatesstage2_
						SELECT RowCode , MAX(description),   MAX(FinalRankNumber) AS MaxFinalRankNumber
						FROM tmp_VRatesstage2_ GROUP BY RowCode;

					truncate tmp_Vendorrates_stage3_;
					INSERT INTO tmp_Vendorrates_stage3_
						select  vr.RowCode as RowCode ,vr.description , vr.rate as rate , vr.rateN as rateN , vr.ConnectionFee as  ConnectionFee
						from tmp_VRatesstage2_ vr
							INNER JOIN tmp_dupVRatesstage2_ vr2
								ON (vr.RowCode = vr2.RowCode AND  vr.FinalRankNumber = vr2.FinalRankNumber);

				END IF;

				INSERT IGNORE INTO tmp_Rates_ (code,description,rate,rateN,ConnectionFee,PreviousRate)
                SELECT RowCode,
		                description,
                    CASE WHEN rule_mgn1.RateRuleId is not null
                        THEN
                            CASE WHEN trim(IFNULL(rule_mgn1.AddMargin,"")) != '' THEN
                                vRate.rate + (CASE WHEN rule_mgn1.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn1.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * vRate.rate) ELSE rule_mgn1.addmargin END)
                            WHEN trim(IFNULL(rule_mgn1.FixedValue,"")) != '' THEN
                                rule_mgn1.FixedValue
                            ELSE
                                vRate.rate
                            END
                    ELSE
                        vRate.rate
                    END as Rate,
                    CASE WHEN rule_mgn2.RateRuleId is not null
                        THEN
                            CASE WHEN trim(IFNULL(rule_mgn2.AddMargin,"")) != '' THEN
                                vRate.rateN + (CASE WHEN rule_mgn2.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn2.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * vRate.rateN) ELSE rule_mgn2.addmargin END)
                            WHEN trim(IFNULL(rule_mgn2.FixedValue,"")) != '' THEN
                                rule_mgn2.FixedValue
                            ELSE
                                vRate.rateN
                            END
                    ELSE
                        vRate.rateN
                    END as RateN,

                    CASE WHEN rule_mgn3.RateRuleId is not null
                        THEN
                            CASE WHEN trim(IFNULL(rule_mgn3.AddMargin,"")) != '' THEN
                                IFNULL(vRate.ConnectionFee,0) + (CASE WHEN rule_mgn3.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn3.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * IFNULL(vRate.ConnectionFee,0)) ELSE rule_mgn3.addmargin END)
                            WHEN trim(IFNULL(rule_mgn3.FixedValue,"")) != '' THEN
                                rule_mgn3.FixedValue
                            ELSE
                                IFNULL(vRate.ConnectionFee,0)
                            END
                    ELSE
                        IFNULL(vRate.ConnectionFee,0)
                    END as ConnectionFee,

					null AS PreviousRate
                FROM tmp_Vendorrates_stage3_ vRate
                LEFT join tblRateRuleMargin rule_mgn1 on  rule_mgn1.RateRuleId = v_rateRuleId_ AND rule_mgn1.`Type` = 1 and vRate.rate Between rule_mgn1.MinRate and rule_mgn1.MaxRate
                LEFT join tblRateRuleMargin rule_mgn2 on  rule_mgn2.RateRuleId = v_rateRuleId_ AND rule_mgn2.`Type` = 1 and vRate.rateN Between rule_mgn2.MinRate and rule_mgn2.MaxRate
                LEFT join tblRateRuleMargin rule_mgn3 on  rule_mgn3.RateRuleId = v_rateRuleId_ AND rule_mgn3.`Type` = 2 and vRate.ConnectionFee Between rule_mgn3.MinRate and rule_mgn3.MaxRate;

				-- Type = 1 Rate
				-- Type = 2 ConnectionFee


			ELSE

				INSERT IGNORE INTO tmp_Rates_ (code,description,rate,rateN,ConnectionFee,PreviousRate)
                SELECT RowCode,
		                description,
                    CASE WHEN rule_mgn1.RateRuleId is not null
                        THEN
                            CASE WHEN trim(IFNULL(rule_mgn1.AddMargin,"")) != '' THEN
                                vRate.rate + (CASE WHEN rule_mgn1.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn1.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * vRate.rate) ELSE rule_mgn1.addmargin END)
                            WHEN trim(IFNULL(rule_mgn1.FixedValue,"")) != '' THEN
                                rule_mgn1.FixedValue
                            ELSE
                                vRate.rate
                            END
                    ELSE
                        vRate.rate
                    END as Rate,
                    CASE WHEN rule_mgn2.RateRuleId is not null
                        THEN
                            CASE WHEN trim(IFNULL(rule_mgn2.AddMargin,"")) != '' THEN
                                vRate.rateN + (CASE WHEN rule_mgn2.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn2.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * vRate.rateN) ELSE rule_mgn2.addmargin END)
                            WHEN trim(IFNULL(rule_mgn2.FixedValue,"")) != '' THEN
                                rule_mgn2.FixedValue
                            ELSE
                                vRate.rateN
                            END
                    ELSE
                        vRate.rateN
                    END as RateN,

                   CASE WHEN rule_mgn3.RateRuleId is not null
                        THEN
                            CASE WHEN trim(IFNULL(rule_mgn3.AddMargin,"")) != '' THEN
                                IFNULL(vRate.ConnectionFee,0) + (CASE WHEN rule_mgn3.addmargin LIKE '%p' THEN ((CAST(REPLACE(rule_mgn3.addmargin, 'p', '') AS DECIMAL(18, 2)) / 100) * IFNULL(vRate.ConnectionFee,0)) ELSE rule_mgn3.addmargin END)
                            WHEN trim(IFNULL(rule_mgn3.FixedValue,"")) != '' THEN
                                rule_mgn3.FixedValue
                            ELSE
                                IFNULL(vRate.ConnectionFee,0)
                            END
                    ELSE
                        IFNULL(vRate.ConnectionFee,0)
                    END as ConnectionFee,

					null AS PreviousRate
                FROM
                    (
                        select
                        max(RowCode) AS RowCode,
                        max(description) AS description,
                        AVG(Rate) as Rate,
                        AVG(RateN) as RateN,
                        AVG(ConnectionFee) as ConnectionFee
                        from tmp_VRatesstage2_
                        group by
                        CASE WHEN p_GroupBy = 'Desc' THEN
                          description
                        ELSE  RowCode
      									END

                    )  vRate
                LEFT join tblRateRuleMargin rule_mgn1 on  rule_mgn1.RateRuleId = v_rateRuleId_ AND rule_mgn1.`Type` = 1 and vRate.rate Between rule_mgn1.MinRate and rule_mgn1.MaxRate
                LEFT join tblRateRuleMargin rule_mgn2 on  rule_mgn2.RateRuleId = v_rateRuleId_ AND rule_mgn2.`Type` = 1 and vRate.rateN Between rule_mgn2.MinRate and rule_mgn2.MaxRate
                LEFT join tblRateRuleMargin rule_mgn3 on  rule_mgn3.RateRuleId = v_rateRuleId_ AND rule_mgn3.`Type` = 2 and vRate.ConnectionFee Between rule_mgn3.MinRate and rule_mgn3.MaxRate;

				-- Type = 1 Rate
				-- Type = 2 ConnectionFee


			END IF;


			SET v_pointer_ = v_pointer_ + 1;


		END WHILE;



		IF p_GroupBy = 'Desc'
		THEN

			truncate table tmp_Rates2_;
			insert into tmp_Rates2_ select * from tmp_Rates_;

			insert ignore into tmp_Rates_ (code,description,rate,rateN,ConnectionFee,PreviousRate)
				select
				distinct
					vr.Code,
					vr.Description,
					vd.rate,
					vd.rateN,
					vd.ConnectionFee,
					vd.PreviousRate
				from  tmp_Rates3_ vr
				inner JOIN tmp_Rates2_ vd on  vd.Description = vr.Description and vd.Code != vr.Code
				where vd.Rate is not null;

		END IF;


		START TRANSACTION;

		IF p_RateTableId = -1
		THEN

			INSERT INTO tblRateTable (CompanyId, RateTableName, RateGeneratorID, TrunkID, CodeDeckId,CurrencyID)
			VALUES (v_CompanyId_, p_rateTableName, p_RateGeneratorId, v_trunk_, v_codedeckid_,v_CurrencyID_);

			SET p_RateTableId = LAST_INSERT_ID();

			INSERT INTO tblRateTableRate (RateID,
																		RateTableId,
																		TimezonesID,
																		Rate,
																		RateN,
																		EffectiveDate,
																		PreviousRate,
																		Interval1,
																		IntervalN,
																		ConnectionFee
			)
				SELECT DISTINCT
					RateId,
					p_RateTableId,
					v_TimezonesID,
					Rate,
					RateN,
					p_EffectiveDate,
					Rate,
					Interval1,
					IntervalN,
					ConnectionFee
				FROM tmp_Rates_ rate
					INNER JOIN tblRate
						ON rate.code  = tblRate.Code
				WHERE tblRate.CodeDeckId = v_codedeckid_;

		ELSE

			IF p_delete_exiting_rate = 1
			THEN

				UPDATE
					tblRateTableRate
				SET
					EndDate = NOW()
				WHERE
					tblRateTableRate.RateTableId = p_RateTableId AND tblRateTableRate.TimezonesID = v_TimezonesID;


				CALL prc_ArchiveOldRateTableRate(p_RateTableId,v_TimezonesID,CONCAT(p_ModifiedBy,'|RateGenerator'));
			END IF;


			UPDATE tmp_Rates_ SET EffectiveDate = p_EffectiveDate;


			UPDATE
				tmp_Rates_ tr
			SET
				PreviousRate = (SELECT rtr.Rate FROM tblRateTableRate rtr JOIN tblRate r ON r.RateID=rtr.RateID WHERE rtr.RateTableID=p_RateTableId AND rtr.TimezonesID=v_TimezonesID AND r.Code=tr.Code AND rtr.EffectiveDate<tr.EffectiveDate ORDER BY rtr.EffectiveDate DESC,rtr.RateTableRateID DESC LIMIT 1);

			UPDATE
				tmp_Rates_ tr
			SET
				PreviousRate = (SELECT rtr.Rate FROM tblRateTableRateArchive rtr JOIN tblRate r ON r.RateID=rtr.RateID WHERE rtr.RateTableID=p_RateTableId AND rtr.TimezonesID=v_TimezonesID AND r.Code=tr.Code AND rtr.EffectiveDate<tr.EffectiveDate ORDER BY rtr.EffectiveDate DESC,rtr.RateTableRateID DESC LIMIT 1)
			WHERE
				PreviousRate is null;



			IF v_IncreaseEffectiveDate_ != v_DecreaseEffectiveDate_ THEN

				UPDATE tmp_Rates_
				SET
					tmp_Rates_.EffectiveDate =
					CASE WHEN tmp_Rates_.PreviousRate < tmp_Rates_.Rate THEN
						v_IncreaseEffectiveDate_
					WHEN tmp_Rates_.PreviousRate > tmp_Rates_.Rate THEN
						v_DecreaseEffectiveDate_
					ELSE p_EffectiveDate
					END
				;

			END IF;



			UPDATE
				tblRateTableRate
			INNER JOIN
				tblRate ON tblRate.RateId = tblRateTableRate.RateId
					AND tblRateTableRate.RateTableId = p_RateTableId

			INNER JOIN
				tmp_Rates_ as rate ON rate.code = tblRate.Code AND tblRateTableRate.EffectiveDate = rate.EffectiveDate
			SET
				tblRateTableRate.EndDate = NOW()
			WHERE
				tblRateTableRate.TimezonesID = v_TimezonesID AND
				tblRateTableRate.RateTableId = p_RateTableId AND
				tblRate.CodeDeckId = v_codedeckid_ AND
				rate.rate != tblRateTableRate.Rate;


			CALL prc_ArchiveOldRateTableRate(p_RateTableId,v_TimezonesID,CONCAT(p_ModifiedBy,'|RateGenerator'));


			INSERT INTO tblRateTableRate (RateID,
																		RateTableId,
																		TimezonesID,
																		Rate,
																		RateN,
																		EffectiveDate,
																		PreviousRate,
																		Interval1,
																		IntervalN,
																		ConnectionFee
			)
				SELECT DISTINCT
					tblRate.RateId,
					p_RateTableId AS RateTableId,
					v_TimezonesID AS TimezonesID,
					rate.Rate,
					rate.RateN,
					rate.EffectiveDate,
					rate.PreviousRate,
					tblRate.Interval1,
					tblRate.IntervalN,
					rate.ConnectionFee
				FROM tmp_Rates_ rate
					INNER JOIN tblRate
						ON rate.code  = tblRate.Code
					LEFT JOIN tblRateTableRate tbl1
						ON tblRate.RateId = tbl1.RateId
							 AND tbl1.RateTableId = p_RateTableId
							 AND tbl1.TimezonesID = v_TimezonesID
					LEFT JOIN tblRateTableRate tbl2
						ON tblRate.RateId = tbl2.RateId
							 and tbl2.EffectiveDate = rate.EffectiveDate
							 AND tbl2.RateTableId = p_RateTableId
							 AND tbl2.TimezonesID = v_TimezonesID
				WHERE  (    tbl1.RateTableRateID IS NULL
										OR
										(
											tbl2.RateTableRateID IS NULL
											AND  tbl1.EffectiveDate != rate.EffectiveDate

										)
							 )
							 AND tblRate.CodeDeckId = v_codedeckid_;


			UPDATE
				tblRateTableRate rtr
			INNER JOIN
				tblRate ON rtr.RateId  = tblRate.RateId
			LEFT JOIN
				tmp_Rates_ rate ON rate.Code=tblRate.Code
			SET
				rtr.EndDate = NOW()
			WHERE
				rate.Code is null AND rtr.RateTableId = p_RateTableId AND rtr.TimezonesID = v_TimezonesID AND rtr.EffectiveDate = rate.EffectiveDate AND tblRate.CodeDeckId = v_codedeckid_;


			CALL prc_ArchiveOldRateTableRate(p_RateTableId,v_TimezonesID,CONCAT(p_ModifiedBy,'|RateGenerator'));

		END IF;


		DROP TEMPORARY TABLE IF EXISTS tmp_ALL_RateTableRate_;
		CREATE TEMPORARY TABLE IF NOT EXISTS tmp_ALL_RateTableRate_ AS (SELECT * FROM tblRateTableRate WHERE RateTableID=p_RateTableId AND TimezonesID=v_TimezonesID);

		UPDATE
			tmp_ALL_RateTableRate_ temp
		SET
			EndDate = (SELECT EffectiveDate FROM tblRateTableRate rtr WHERE rtr.RateTableID=p_RateTableId AND rtr.TimezonesID=v_TimezonesID AND rtr.RateID=temp.RateID AND rtr.EffectiveDate>temp.EffectiveDate ORDER BY rtr.EffectiveDate ASC,rtr.RateTableRateID ASC LIMIT 1)
		WHERE
			temp.RateTableId = p_RateTableId AND
			temp.TimezonesID = v_TimezonesID;

		UPDATE
			tblRateTableRate rtr
		INNER JOIN
			tmp_ALL_RateTableRate_ temp ON rtr.RateTableRateID=temp.RateTableRateID AND rtr.TimezonesID=temp.TimezonesID
		SET
			rtr.EndDate=temp.EndDate
		WHERE
			rtr.RateTableId=p_RateTableId AND
			rtr.TimezonesID=v_TimezonesID;



		CALL prc_ArchiveOldRateTableRate(p_RateTableId,v_TimezonesID,CONCAT(p_ModifiedBy,'|RateGenerator'));

		UPDATE tblRateTable
		SET RateGeneratorID = p_RateGeneratorId,
			TrunkID = v_trunk_,
			CodeDeckId = v_codedeckid_,
			updated_at = now()
		WHERE RateTableID = p_RateTableId;



		INSERT INTO tmp_JobLog_ (Message) VALUES (p_RateTableId);


		SELECT * FROM tmp_JobLog_;

		COMMIT;


		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;



	END//
DELIMITER ;


