USE `NeonRMDev`;

INSERT INTO `tblCompanyConfiguration` (`CompanyConfigurationID`, `CompanyID`, `Key`, `Value`) VALUES (40, 1, 'BILLING_DASHBOARD_CUSTOMER', 'BillingDashboardPincodeWidget,BillingDashboardTotalInvoiceSent,BillingDashboardTotalInvoiceReceived,BillingDashboardPaymentReceived,BillingDashboardPaymentSent,BillingDashboardUnbilledAmount');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'EMAIL_TO_CUSTOMER', '0');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'Neon_API_URL', 'http://linux1.neon-soft.com/api.neon/api.abubakar/api.neon-crm/public/api/');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'ACC_DOC_PATH','/home/neon_branches/dev/tmp');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'PAYMENT_PROOF_PATH', '/home/neon_branches/dev/tmp');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'CRM_ALLOWED_FILE_UPLOAD_EXTENSIONS', 'bmp,csv,doc,docx,gif,ini,jpg,msg,odt,pdf,png,ppt,pptx,rar,rtf,txt,xls,xlsx,zip,7z');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'SUPER_ADMIN_EMAILS', '{"registration":{"from":"","from_name":"","email":""}}');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'CACHE_EXPIRE', '60');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'MAX_UPLOAD_FILE_SIZE', '5M');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'PAGE_SIZE', '50');
INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ('1', 'DEFAULT_PREFERENCE', '5');
