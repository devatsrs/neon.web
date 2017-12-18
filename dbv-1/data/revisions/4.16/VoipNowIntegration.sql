Use Ratemanagement3;

INSERT INTO `tblGateway` (`GatewayID`, `Title`, `Name`, `Status`, `CreatedBy`, `created_at`, `ModifiedBy`, `updated_at`) VALUES (13, 'VoipNow', 'VoipNow', 1, 'RateManagementSystem', '2017-12-15 16:25:34', NULL, NULL);

INSERT INTO `tblGatewayConfig` (`GatewayConfigID`, `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (157, 13, 'Auto Add IP', 'AutoAddIP', 1, '2017-12-15 16:36:38', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayConfigID`, `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (156, 13, 'Prefix Translation Rule', 'PrefixTranslationRule', 1, '2017-12-15 16:36:38', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayConfigID`, `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (155, 13, 'Allow Account Import', 'AllowAccountImport', 1, '2017-12-15 16:36:38', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayConfigID`, `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (154, 13, 'CLD Translation Rule', 'CLDTranslationRule', 1, '2017-12-15 16:36:38', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayConfigID`, `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (153, 13, 'CLI Translation Rule', 'CLITranslationRule', 1, '2017-12-15 16:36:38', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayConfigID`, `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (152, 13, 'Rate Format', 'RateFormat', 1, '2017-12-15 16:36:38', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayConfigID`, `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (151, 13, 'CDR ReRate', 'RateCDR', 1, '2017-12-15 16:36:38', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayConfigID`, `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (150, 13, 'Authentication Rule', 'NameFormat', 1, '2017-12-15 16:36:38', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayConfigID`, `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (149, 13, 'VoipNow Password', 'password', 1, '2017-12-15 16:36:38', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayConfigID`, `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (148, 13, 'VoipNow Username', 'username', 1, '2017-12-15 16:36:38', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayConfigID`, `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (147, 13, 'VoipNow Server', 'dbserver', 1, '2017-12-15 16:36:38', 'RateManagementSystem', NULL, NULL);

INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES (1, 'VIOPNOW_PBX_CRONJOB', '{"MaxInterval":"1440","ThresholdTime":"30","SuccessEmail":"","ErrorEmail":"","JobTime":"MINUTE","JobInterval":"1","JobDay":["SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"12:00:00 AM","CompanyGatewayID":""}');

INSERT INTO `tblCronJobCommand` (`CompanyID`, `GatewayID`, `Title`, `Command`, `Settings`, `Status`, `created_at`, `created_by`) VALUES (1, 13, 'Download VoipNow CDR', 'voipnowaccountusage', '[[{"title":"Max Interval","type":"text","value":"","name":"MaxInterval"},{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"}]]', 1, '2017-12-15 16:56:13', 'RateManagementSystem');


