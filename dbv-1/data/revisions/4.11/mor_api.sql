USE `Ratemanagement3`;

INSERT INTO `tblGateway` (`GatewayID`, `Title`, `Name`, `Status`, `CreatedBy`, `created_at`, `ModifiedBy`, `updated_at`) VALUES (8, 'MOR', 'MOR', 1, 'RateManagementSystem', '2017-01-28 12:41:23', NULL, NULL);

INSERT INTO `tblGatewayConfig` (`GatewayConfigID`, `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (71, 8, 'MOR Server', 'dbserver', 1, '2015-07-09 13:56:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayConfigID`, `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (72, 8, 'MOR Username', 'username', 1, '2015-07-09 13:56:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayConfigID`, `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (73, 8, 'MOR Password', 'password', 1, '2015-07-09 13:56:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayConfigID`, `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (74, 8, 'Authentication Rule', 'NameFormat', 1, '2015-07-09 13:56:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayConfigID`, `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (75, 8, 'CDR ReRate', 'RateCDR', 1, '2015-12-21 11:19:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayConfigID`, `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (76, 8, 'Rate Format', 'RateFormat', 1, '2015-12-21 11:19:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayConfigID`, `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (77, 8, 'CLI Translation Rule', 'CLITranslationRule', 1, '2016-09-19 10:39:33', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayConfigID`, `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (78, 8, 'CLD Translation Rule', 'CLDTranslationRule', 1, '2016-09-19 10:39:33', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayConfigID`, `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (79, 8, 'Allow Account Import', 'AllowAccountImport', 1, '2016-03-18 11:19:00', 'RateManagementSystem', NULL, NULL);


INSERT INTO `tblCronJobCommand` (`CronJobCommandID`, `CompanyID`, `GatewayID`, `Title`, `Command`, `Settings`, `Status`, `created_at`, `created_by`) VALUES (83, 1, 8, 'Download MOR CDR', 'moraccountusage', '[[{"title":"MOR Max Interval","type":"text","value":"","name":"MaxInterval"},{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"}]]', 1, '2015-07-09 13:56:13', 'RateManagementSystem');

INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (63, 1, 'MOR_CRONJOB', '{"MaxInterval":"1440","CdrBehindDuration":"200","CdrBehindDurationEmail":"120","ThresholdTime":"30","SuccessEmail":"","ErrorEmail":"error@neon-soft.com","JobTime":"MINUTE","JobInterval":"1","JobDay":["SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"12:00:00 AM","CompanyGatewayID":""}');


