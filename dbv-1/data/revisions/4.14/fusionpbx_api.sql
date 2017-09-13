USE `Ratemanagement3`;

INSERT INTO `tblGateway` (`GatewayID`, `Title`, `Name`, `Status`, `CreatedBy`, `created_at`, `ModifiedBy`, `updated_at`) VALUES (11, 'Fusion PBX', 'FusionPBX', 1, 'RateManagementSystem', '2017-08-23 10:47:22', NULL, NULL);


INSERT INTO `tblGatewayConfig` (`GatewayConfigID`, `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (113, 11, 'Fusion PBX Server', 'dbserver', 1, '2017-08-23 06:22:07', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayConfigID`, `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (114, 11, 'Fusion PBX Username', 'username', 1, '2017-08-23 06:22:09', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayConfigID`, `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (115, 11, 'Fusion PBX Password', 'password', 1, '2017-08-23 06:22:10', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayConfigID`, `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (116, 11, 'Authentication Rule', 'NameFormat', 1, '2017-08-23 06:22:10', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayConfigID`, `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (117, 11, 'CDR ReRate', 'RateCDR', 1, '2017-08-23 06:22:11', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayConfigID`, `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (118, 11, 'Rate Format', 'RateFormat', 1, '2017-08-23 06:22:11', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayConfigID`, `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (119, 11, 'CLI Translation Rule', 'CLITranslationRule', 1, '2017-08-23 06:22:12', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayConfigID`, `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (120, 11, 'CLD Translation Rule', 'CLDTranslationRule', 1, '2017-08-23 06:22:13', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayConfigID`, `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (121, 11, 'Prefix Translation Rule', 'PrefixTranslationRule', 1, '2017-08-23 06:22:14', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayConfigID`, `GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (122, 11, 'Allow Account Import', 'AllowAccountImport', 1, '2017-08-23 06:22:15', 'RateManagementSystem', NULL, NULL);

INSERT INTO `tblCronJobCommand` ( `CompanyID`, `GatewayID`, `Title`, `Command`, `Settings`, `Status`, `created_at`, `created_by`) VALUES ( 1, 11, 'Download Fusion PBX CDR', 'fusionpbxaccountusage', '[[{"title":"Fusion PBX Max Interval","type":"text","value":"","name":"MaxInterval"},{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"}]]', 1, '2017-08-23 06:25:14', 'RateManagementSystem');

INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ( 1, 'FUSION_PBX_CRONJOB', '{"MaxInterval":"1440","ThresholdTime":"30","SuccessEmail":"","ErrorEmail":"","JobTime":"MINUTE","JobInterval":"1","JobDay":["SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"12:00:00 AM","CompanyGatewayID":""}');

