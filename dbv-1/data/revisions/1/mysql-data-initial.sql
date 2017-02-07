USE `D2CallRM`;

INSERT INTO `tblCountry`
SELECT `CountryID`, `Prefix`, `Country`, `ISO2`, `ISO3` from Ratemanagement3.tblCountry;

INSERT INTO `tblCronJobCommand`
SELECT * from Ratemanagement3.tblCronJobCommand;

INSERT INTO `tblGateway`
SELECT * from Ratemanagement3.tblGateway;

INSERT INTO `tblGatewayConfig`
SELECT * from Ratemanagement3.tblGatewayConfig;

INSERT INTO `tblGlobalAdmin`
SELECT * from Ratemanagement3.tblGlobalAdmin;


INSERT INTO `tblGlobalSetting`
SELECT * from Ratemanagement3.tblGlobalSetting;


INSERT INTO `tblJobStatus`
SELECT * from Ratemanagement3.tblJobStatus;


INSERT INTO `tblJobType`
SELECT * from Ratemanagement3.tblJobType;


INSERT INTO `tblRateSheetFormate`
SELECT * from Ratemanagement3.tblRateSheetFormate;

INSERT INTO `tblPermission`
SELECT * from Ratemanagement3.tblPermission;

INSERT INTO `tblResource`
SELECT * from Ratemanagement3.tblResource;

INSERT INTO `tblResourceCategories`
SELECT * from Ratemanagement3.tblResourceCategories;

INSERT INTO `tblIntegration`
SELECT * from Ratemanagement3.tblIntegration;


 INSERT INTO `tblCompany` (`CompanyID`, `CompanyName`,  `CustomerAccountPrefix`, `FirstName`, `LastName`, `Email`, `Phone`,  `Status`, `TimeZone`, `created_at`, `created_by`)
 VALUES (1, 'D2Call', '22221', 'D2Call', 'D2Call', 'noc@d2call.com', '',  1, 'Etc/GMT', '2016-11-17 10:12:25', 'Dev');

 INSERT INTO `tblUser` (`UserID`, `CompanyID`, `FirstName`, `LastName`, `EmailAddress`, `password`, `AdminUser`, `AccountingUser`, `Status`, `Roles`, `remember_token`, `updated_at`, `created_at`, `created_by`, `updated_by`, `EmailFooter`, `Color`, `JobNotification`)
VALUES (1, 1, 'D2Call', 'D2Call', 'noc@d2call.com', '$2y$10$PlVXiwVLUxkuiwSyKQJyUeHAVysVkya6VDuinVOrG2GLTmPr1wk4.', 1, 1, 1, 'Admin,Billing Admin', 'mJZaptV7wrwCooghFLeaFtXfQcG3dgAYasFMPzlWGEWuUAxrZ8EqTZF8f1sA', '2016-11-17 10:26:12', '2015-02-07 07:24:02', NULL, 'Sumera Saeed', 'From ,<br><br><b>Sumera Saeed</b><br><br>', '', 1);


INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (1, 1, 'Amazon', '');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (2, 1, 'SSH', '{"host":"188.227.186.98","username":"root","password":"KatiteDo48"}');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (3, 1, 'UPLOADPATH', '/home/hostedfolders/d2call/tmp');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (4, 1, 'SITE_URL', 'http://linux1.neon-soft.com/d2call/public');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (5, 1, 'EXTRA_SMTP', '');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (6, 1, 'OUTLOOKCALENDAR_API', '');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (7, 1, 'FRONT_STORAGE_PATH', '/var/www/html/d2call/app/storage');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (8, 1, 'DELETE_STORAGE_LOG_DAYS', '365');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (9, 1, 'TEMP_PATH', '/home/hostedfolders/d2call/tmp');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (10, 1, 'DELETE_TEMP_FILES_DAYS', '365');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (11, 1, 'SIPPYFILE_LOCATION', '');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (12, 1, 'VOS_LOCATION', '');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (14, 1, 'LICENCE_KEY', '613WYHjYi0G1M5zvGAOAd6IRkDdn21TQ');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (15, 1, 'PBX_CRONJOB', '{"MaxInterval":"1440","ThresholdTime":"30","SuccessEmail":"","ErrorEmail":"","JobTime":"MINUTE","JobInterval":"1","JobDay":["SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"12:00:00 AM","CompanyGatewayID":""}');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (16, 1, 'PORTA_CRONJOB', '{"MaxInterval":"1440","ThresholdTime":"30","SuccessEmail":"","ErrorEmail":"","JobTime":"MINUTE","JobInterval":"1","JobDay":["SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"12:00:00 AM","CompanyGatewayID":""}');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (17, 1, 'SIPPYSFTP_DOWNLOAD_CRONJOB', '{"FilesDownloadLimit":"50","ThresholdTime":"120","SuccessEmail":"","ErrorEmail":"","JobTime":"MINUTE","JobInterval":"1","JobDay":["SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"12:00:00 AM","CompanyGatewayID":""}');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (18, 1, 'SIPPYSFTP_PROCESS_CRONJOB', '{"FilesMaxProccess":"3","ThresholdTime":"60","SuccessEmail":"","ErrorEmail":"","JobTime":"MINUTE","JobInterval":"1","JobDay":["SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"12:00:00 AM","CompanyGatewayID":""}');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (19, 1, 'VOS_DOWNLOAD_CRONJOB', '{"FilesDownloadLimit":"10","ThresholdTime":"120","SuccessEmail":"","ErrorEmail":"","JobTime":"MINUTE","JobInterval":"1","JobDay":["SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"12:00:00 AM","CompanyGatewayID":""}');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (20, 1, 'VOS_PROCESS_CRONJOB', '{"FilesMaxProccess":"5","ThresholdTime":"30","SuccessEmail":"","ErrorEmail":"","JobTime":"MINUTE","JobInterval":"2","JobDay":["SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"12:00:00 AM","CompanyGatewayID":""}');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (21, 1, 'CUSTOMER_SUMMARYDAILY_CRONJOB', '{"ThresholdTime":"500","SuccessEmail":"","ErrorEmail":"","JobTime":"DAILY","JobInterval":"1","JobDay":["SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"12:00:00 AM"}');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (22, 1, 'CUSTOMER_SUMMARYLIVE_CRONJOB', '{"ThresholdTime":"30","SuccessEmail":"","ErrorEmail":"","JobTime":"MINUTE","JobInterval":"1","JobDay":["SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"12:00:00 AM"}');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (23, 1, 'VENDOR_SUMMARYDAILY_CRONJOB', '{"ThresholdTime":"500","SuccessEmail":"","ErrorEmail":"","JobTime":"DAILY","JobInterval":"1","JobDay":["SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"2:00:00 AM"}');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (24, 1, 'VENDOR_SUMMARYLIVE_CRONJOB', '{"ThresholdTime":"30","SuccessEmail":"","ErrorEmail":"","JobTime":"MINUTE","JobInterval":"1","JobDay":["SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"12:00:00 AM"}');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (25, 1, 'CRM_DASHBOARD', 'CrmDashboardTasks,CrmDashboardRecentAccount,CrmDashboardSalesRevenue,CrmDashboardSalesOpportunity,CrmDashboardPipeline,CrmDashboardForecast,CrmDashboardOpportunities');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (26, 1, 'PAYPAL_IPN', '');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (27, 1, 'PHPExePath', '/usr/bin/php');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (28, 1, 'RMArtisanFileLocation', '/var/www/html/d2call.service/artisan');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (29, 1, 'CUSTOMER_NOTIFICATION_DISPLAY', '1');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (30, 1, 'CUSTOMER_COMMERCIAL_DISPLAY', '1');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (31, 1, 'BILLING_DASHBOARD', 'BillingDashboardSummaryWidgets,BillingDashboardPincodeWidget,BillingDashboardMissingGatewayWidget,BillingDashboardTotalOutstanding,BillingDashboardTotalInvoiceSent,BillingDashboardTotalInvoiceReceived,BillingDashboardDueAmount,BillingDashboardOverDueAmount,BillingDashboardPaymentReceived,BillingDashboardPaymentSent,BillingDashboardPendingDispute,BillingDashboardPendingEstimate,BillingDashboardInvoiceExpense,BillingDashboardOutstanding');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (35, 1, 'USAGE_PBX_INTERVAL', '180');
INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (36, 1, 'USAGE_INTERVAL', '100');



INSERT INTO tblCRMBoards (CompanyID, BoardName, `Status`, BoardType, CreatedBy, created_at)
SELECT * FROM (SELECT 1 as CompanyID,'TaskBoard', 1 as Status, 2 as BoardType, 'System' as CreatedBy, Now() as created_at) AS tmp
WHERE NOT EXISTS (
SELECT BoardName FROM tblCRMBoards
WHERE tblCRMBoards.BoardName = 'TaskBoard'
and tblCRMBoards.BoardType = 2
and tblCRMBoards.CompanyID = 1
) LIMIT 1;


SELECT br.BoardID into @taskBoardID FROM tblCRMBoards br
WHERE br.BoardName = 'TaskBoard'
and br.BoardType = 2
and br.CompanyID = 1
limit 1;

INSERT INTO `tblCRMBoardColumn` ( `BoardID`, `CompanyID`, `BoardColumnName`, `Height`, `Width`, `Order`, `SetCompleted`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`) VALUES ( @taskBoardID, 1, 'Not Started', '100%', '300px', 0, 0, 'System', 'System', NOW(), NOW());
INSERT INTO `tblCRMBoardColumn` ( `BoardID`, `CompanyID`, `BoardColumnName`, `Height`, `Width`, `Order`, `SetCompleted`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`) VALUES ( @taskBoardID, 1, 'In Progress', '100%', '300px', 1, 0, 'System', 'System', NOW(), NOW());
INSERT INTO `tblCRMBoardColumn` ( `BoardID`, `CompanyID`, `BoardColumnName`, `Height`, `Width`, `Order`, `SetCompleted`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`) VALUES ( @taskBoardID, 1, 'Waiting', '100%', '300px', 2, 0, 'System', 'System', NOW(), NOW());
INSERT INTO `tblCRMBoardColumn` ( `BoardID`, `CompanyID`, `BoardColumnName`, `Height`, `Width`, `Order`, `SetCompleted`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`) VALUES ( @taskBoardID, 1, 'Completed', '100%', '300px', 3, 0, 'System', 'System', NOW(), NOW());
INSERT INTO `tblCRMBoardColumn` ( `BoardID`, `CompanyID`, `BoardColumnName`, `Height`, `Width`, `Order`, `SetCompleted`, `CreatedBy`, `ModifiedBy`, `created_at`, `updated_at`) VALUES ( @taskBoardID, 1, 'Deferred', '100%', '300px', 4, 0, 'System', 'System', NOW(), NOW());



-- # One time set up for dim tables


INSERT INTO `tblCodeDeck` (`CodeDeckId`, `CompanyId`, `CodeDeckName`, `created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`, `Type`, `DefaultCodedeck`) VALUES (1, 1, 'Default Codedeck', '2016-11-18 09:17:21', 'Dev', '2016-11-18 09:36:18', NULL, NULL, 1);

insert into tblRate
SELECT * from RateManagement4.tblRate where CodeDeckId = 1 ;


USE `D2CallReport`;

call prc_datedimbuild('2016-01-01','2026-01-01');
call prc_timedimbuild();

