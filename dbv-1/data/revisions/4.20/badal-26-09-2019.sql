Use Ratemanagement3;

/* add new gateway for huawei integration*/
INSERT INTO `tblGateway` (`Title`, `Name`, `Status`, `CreatedBy`, `created_at`) VALUES ('Huawei', 'Huawei', '1', 'RateManagementSystem', '2019-02-27 12:41:23');

INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (20, 'Rate Format', 'RateFormat', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (20, 'Authentication Rule', 'NameFormat', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (20, 'CDR ReRate', 'RateCDR', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (20, 'Rerate Method', 'RateMethod', 1, '2017-06-09 00:00:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (20, 'Rerate Method Value', 'SpecifyRate', 1, '2017-06-09 00:00:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (20, 'CLI Translation Rule', 'CLITranslationRule', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (20, 'Huawei Host IP', 'host', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (20, 'Protocol Type', 'protocol_type', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (20, 'Port', 'port', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (20, 'SSL', 'ssl', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (20, 'Passive Mode', 'passive_mode', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (20, 'User Name', 'username', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (20, 'Password', 'password', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (20, 'Key', 'key', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (20, 'Key Phrase', 'keyphrase', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (20, 'Huawei CDR Download Path', 'cdr_folder', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (20, 'CLD Translation Rule', 'CLDTranslationRule', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (20, 'Billing Time', 'BillingTime', 1, '2017-04-14 15:59:25', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (20, 'Prefix Translation Rule', 'PrefixTranslationRule', 1, '2017-06-09 00:00:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (20, 'File Name Rule', 'FileNameRule', 1, '2017-06-09 00:00:00', 'RateManagementSystem', NULL, NULL);


INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'HUAWEI_LOCATION', '/home/tmp/huawei_files');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'HUAWEI_CSV_LOCATION', '/home/tmp/huawei_csv_files');

INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'HUAWEI_DOWNLOAD_CRONJOB', '{"FilesDownloadLimit":"10","ThresholdTime":"120","SuccessEmail":"","ErrorEmail":"error@neon-soft.com","JobTime":"MINUTE","JobInterval":"1","JobDay":["SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"12:00:00 AM","CompanyGatewayID":""}');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'HUAWEI_PROCESS_CRONJOB', '{"FilesMaxProccess":"5","CdrBehindDuration":"200","CdrBehindDurationEmail":"180","ThresholdTime":"30","SuccessEmail":"","ErrorEmail":"error@neon-soft.com","JobTime":"MINUTE","JobInterval":"2","JobDay":["SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"12:00:00 AM","CompanyGatewayID":""}');

INSERT INTO `tblCronJobCommand` (`CompanyID`, `GatewayID`, `Title`, `Command`, `Settings`, `Status`, `created_at`, `created_by`) VALUES ('1', '20', 'Download HUAWEI CDR', 'huaweiaccountusage', '[[{"title":"Files Max Proccess","type":"text","value":"","name":"FilesMaxProccess"},{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"}]]', '1', '2015-05-29 13:05:56', 'RateManagementSystem');
INSERT INTO `tblCronJobCommand` (`CompanyID`, `GatewayID`, `Title`, `Command`, `Settings`, `Status`, `created_at`, `created_by`) VALUES ('1', '20', 'Download HUAWEI SFTP File', 'huaweidownloadcdr', '[[{"title":"Max File Download Limit","type":"text","value":"","name":"FilesDownloadLimit"},{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"}]]', '1', '2015-06-09 13:05:56', 'RateManagementSystem');

INSERT INTO `tblGateway` (`Title`, `Name`, `Status`, `CreatedBy`, `created_at`) VALUES ('Porta One', 'Porta New', '1', 'RateManagementSystem', '2015-05-01 15:38:00');

INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (21, 'API Url', 'api_url', 1, '2015-05-01 15:38:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (21, 'Username', 'username', 1, '2015-05-01 15:38:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (21, 'License Key', 'password', 1, '2015-05-01 15:38:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (21, 'Authentication Rule', 'NameFormat', 1, '2015-05-08 07:46:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (21, 'Billing Time', 'BillingTime', 1, '2015-08-13 07:58:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (21, 'CDR ReRate', 'RateCDR', 1, '2015-12-21 11:19:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (21, 'Rate Format', 'RateFormat', 1, '2015-12-21 11:19:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (21, 'Allow Account Import', 'AllowAccountImport', 1, '2016-03-18 11:19:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (21, 'CLI Translation Rule', 'CLITranslationRule', 1, '2016-09-19 10:39:33', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (21, 'CLD Translation Rule', 'CLDTranslationRule', 1, '2016-09-19 10:39:33', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (21, 'Prefix Translation Rule', 'PrefixTranslationRule', 1, '2017-06-09 00:00:00', 'RateManagementSystem', NULL, NULL);

INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'PORTAONE_CRONJOB', '{"MaxInterval":"1440","ThresholdTime":"30","SuccessEmail":"","ErrorEmail":"","JobTime":"MINUTE","JobInterval":"1","JobDay":["SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"12:00:00 AM","CompanyGatewayID":""}');

INSERT INTO `tblCronJobCommand` (`CompanyID`, `GatewayID`, `Title`, `Command`, `Settings`, `Status`, `created_at`, `created_by`) VALUES ('1', '21', 'Download PortaOne CDR', 'portaoneaccountusage', '[[{"title":"Porta Max Interval","type":"text","value":"","name":"MaxInterval"},{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"},{"title":"CDR Import Start Date","type":"text","datepicker":"","value":"","name":"CDRImportStartDate"}]]', '1', '2015-05-01 15:38:30', 'RateManagementSystem');

INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES (1, 'HUAWEI_UniqueID_1', '0');
