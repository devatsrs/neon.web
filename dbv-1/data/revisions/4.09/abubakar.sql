USE `Ratemanagement3`;

INSERT INTO `tblresourcecategories` (`ResourceCategoryName`, `CompanyID`) VALUES ('BillingDashboardSummaryWidgets.View', '1');
INSERT INTO `tblresourcecategories` (`ResourceCategoryName`, `CompanyID`) VALUES ('BillingDashboardMissingGatewayWidget.View', '1');
INSERT INTO `tblResourceCategories` (`ResourceCategoryName`, `CompanyID`) VALUES ('BillingDashboardInvoiceExpenseWidgets.View', '1');
INSERT INTO `tblResourceCategories` (`ResourceCategoryName`, `CompanyID`) VALUES ('BillingDashboardPincodeWidget.View', '1');
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='BillingDashboardSummaryWidgets.View' limit 1) WHERE  `ResourceName`='BillingDashboard.invoice_expense_total';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='BillingDashboardSummaryWidgets.View' limit 1) WHERE  `ResourceName`='BillingDashboard.invoice_expense_total_widget';

UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='BillingDashboardMissingGatewayWidget.View' limit 1) WHERE  `ResourceName`='Dashboard.ajax_get_missing_accounts';
UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='BillingDashboardInvoiceExpenseWidgets.View' limit 1) WHERE  `ResourceName`='BillingDashboard.invoice_expense_chart';

UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='BillingDashboardPincodeWidget.View' limit 1) WHERE  `ResourceName`='BillingDashboard.ajax_top_pincode';

UPDATE `tblResource` SET `CategoryID`=(select ResourceCategoryID FROM tblResourceCategories WHERE ResourceCategoryName='BillingDashboardInvoiceExpenseWidgets.View' limit 1) WHERE  `ResourceName`='BillingDashboard.invoice_expense_total_widget';

DELETE FROM `tblCompanyConfiguration` WHERE  `Key`='FreshdeskDomain';
DELETE FROM `tblCompanyConfiguration` WHERE  `Key`='FreshdeskEmail';
DELETE FROM `tblCompanyConfiguration` WHERE  `Key`='Freshdeskkey';
DELETE FROM `tblCompanyConfiguration` WHERE  `Key`='FreshdeskPassword';
DELETE FROM `tblCompanyConfiguration` WHERE  `Key`='PAYPAL_IPN';
DELETE FROM `tblCompanyConfiguration` WHERE  `Key`='OUTLOOKCALENDAR_API';
DELETE FROM `tblCompanyConfiguration` WHERE  `Key`='EXTRA_SMTP';
DELETE FROM `tblCompanyConfiguration` WHERE  `Key`='Amazon';
DELETE FROM `tblCompanyConfiguration` WHERE  `Key`='ErrorEmail';
DELETE FROM `tblCompanyConfiguration` WHERE  `Key`='FILE_RETENTION_EMAIL';
DELETE FROM `tblCompanyConfiguration` WHERE  `Key`='SIPPY_CSVDECODER';

UPDATE `tblcompanyconfiguration` SET `Value`='UPLOAD_PATH' WHERE  `Key`='UPLOADPATH';
UPDATE `tblcompanyconfiguration` SET `Value`='NEON_API_URL' WHERE  `Key`='Neon_API_URL';
UPDATE `tblcompanyconfiguration` SET `Value`='PHP_EXE_PATH' WHERE  `Key`='PHPExePath';
UPDATE `tblcompanyconfiguration` SET `Value`='RM_ARTISAN_FILE_LOCATION' WHERE  `Key`='RMArtisanFileLocation';
UPDATE `tblcompanyconfiguration` SET `Key`='UPLOAD_PATH' WHERE  `Key`='UPLOADPATH';
UPDATE `tblcompanyconfiguration` SET `Key`='WEB_URL' WHERE  `Key`='SITE_URL';
UPDATE `tblcompanyconfiguration` SET `Key`='QUICKBOOK' WHERE `Key`='Quickbook' ;


INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'BILLING_DASHBOARD_CUSTOMER', 'BillingDashboardPincodeWidget,BillingDashboardTotalInvoiceSent,BillingDashboardTotalInvoiceReceived,BillingDashboardPaymentReceived,BillingDashboardPaymentSent,BillingDashboardUnbilledAmount');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'EMAIL_TO_CUSTOMER', '0');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'ACC_DOC_PATH','/home/neon_branches/dev/tmp');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'PAYMENT_PROOF_PATH', '/home/neon_branches/dev/tmp');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'CRM_ALLOWED_FILE_UPLOAD_EXTENSIONS', 'bmp,csv,doc,docx,gif,ini,jpg,msg,odt,pdf,png,ppt,pptx,rar,rtf,txt,xls,xlsx,zip,7z');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'SUPER_ADMIN_EMAILS', '{"registration":{"from":"","from_name":"","email":""}}');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'CACHE_EXPIRE', '60');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'MAX_UPLOAD_FILE_SIZE', '5M');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'PAGE_SIZE', '50');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'DEFAULT_PREFERENCE', '5');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'DEMO_DATA_PATH', '/home/neon_branches/dev/tmp');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'TRANSACTION_LOG_EMAIL_FREQUENCY', 'Daily');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'DEFAULT_TIMEZONE', 'GMT');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'DEFAULT_BILLING_TIMEZONE', 'Europe/London');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'DELETE_CDR_TIME', '3 month');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'DELETE_SUMMARY_TIME', '4 days');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'NEON_API_URL', '_NEON_API_URL_');
