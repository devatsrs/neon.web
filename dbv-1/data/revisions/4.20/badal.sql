Use Ratemanagement3;

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
