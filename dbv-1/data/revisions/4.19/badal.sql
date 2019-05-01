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

