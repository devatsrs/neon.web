USE `Ratemanagement3`;


INSERT INTO `tblGateway` VALUES (19, 'Clarity PBX', 'ClarityPBX', 1, 'RateManagementSystem', '2019-05-09 16:59:07', NULL, '2019-05-09 16:59:07');

INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (19, 'Clarity PBX Read Server', 'dbserver_read', 1, '2019-05-09 06:22:07', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (19, 'Clarity PBX Write Server', 'dbserver_write', 1, '2019-05-09 06:22:07', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (19, 'Clarity PBX Username', 'username', 1, '2019-05-09 06:22:09', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (19, 'Clarity PBX Password', 'password', 1, '2019-05-09 06:22:10', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (19, 'Authentication Rule', 'NameFormat', 1, '2019-05-09 06:22:10', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (19, 'CDR ReRate', 'RateCDR', 1, '2019-05-09 06:22:11', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (19, 'Rate Format', 'RateFormat', 1, '2019-05-09 06:22:11', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (19, 'CLI Translation Rule', 'CLITranslationRule', 1, '2019-05-09 06:22:12', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (19, 'CLD Translation Rule', 'CLDTranslationRule', 1, '2019-05-09 06:22:13', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (19, 'Prefix Translation Rule', 'PrefixTranslationRule', 1, '2019-05-09 06:22:14', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (19, 'Allow Account Import', 'AllowAccountImport', 1, '2019-05-09 06:22:15', 'RateManagementSystem', NULL, NULL);

INSERT INTO `tblCronJobCommand` (`CompanyID`, `GatewayID`, `Title`, `Command`, `Settings`, `Status`, `created_at`, `created_by`) VALUES (1, 19, 'Download Clarity PBX CDR', 'claritypbxaccountusage', '[[{"title":"Clarity PBX Max Interval","type":"text","value":"","name":"MaxInterval"},{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"},{"title":"CDR import Start Date","type":"text","datepicker":"","value":"","name":"CDRImportStartDate"}]]', 1, '2019-05-09 06:25:14', 'RateManagementSystem');

INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES (1, 'CLARITY_PBX_CRONJOB', '{"MaxInterval":"1440","ThresholdTime":"30","SuccessEmail":"","ErrorEmail":"","JobTime":"MINUTE","JobInterval":"1","JobDay":["SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"12:00:00 AM","CompanyGatewayID":""}');
