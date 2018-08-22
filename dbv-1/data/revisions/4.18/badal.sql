Use Ratemanagement3;

INSERT INTO `tblEmailTemplate` (`CompanyID`, `LanguageID`, `TemplateName`, `Subject`, `TemplateBody`, `created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`, `userID`, `Type`, `EmailFrom`, `StaticType`, `SystemType`, `Status`, `StatusDisabled`, `TicketTemplate`) VALUES (1, 43, 'AccountBalanceEmailReminder', '{{AccountName}} - Account Balance', '<p>Please find below your account balance details.</p><p>{{AccountBalance}}</p>', '2018-05-22 16:42:31', 'Vasim Seta', '2018-05-23 16:20:17', 'Vasim Seta', NULL, 0, '', 1, 'AccountBalanceEmailReminder', 1, 1, 0);

ALTER TABLE `tblBillingClass` ADD COLUMN `DeductCallChargeInAdvance` TINYINT(1) NULL DEFAULT '0' AFTER `SendInvoiceSetting`;
ALTER TABLE `tblBillingClass` ADD COLUMN `SuspendAccount` TINYINT(1) NULL DEFAULT '0' AFTER `DeductCallChargeInAdvance`;
ALTER TABLE `tblAccountBilling`	ADD COLUMN `AutoPayMethod` INT(11) NULL DEFAULT '0' AFTER `AutoPaymentSetting`;

ALTER TABLE `tblBillingClass` 
	ADD COLUMN `AutoPaymentSetting` VARCHAR(50) NULL DEFAULT NULL AFTER `SuspendAccount`,	
	ADD COLUMN `AutoPayMethod` INT(11) NULL DEFAULT '0' AFTER `AutoPaymentSetting`;

ALTER TABLE `tblBillingClass`
	ADD COLUMN `BalanceWarningStatus` TINYINT(4) NULL DEFAULT NULL AFTER `LowBalanceReminderSettings`,
	ADD COLUMN `BalanceWarningSettings` VARCHAR(5000) NULL DEFAULT NULL AFTER `BalanceWarningStatus`;
	
/* added on 29th may */

CREATE TABLE `tblSubscriptionDiscountPlan` (
	`SubscriptionDiscountPlanID` INT(11) NOT NULL AUTO_INCREMENT,
	`AccountID` INT(11) NULL DEFAULT '0',
	`ServiceID` INT(11) NULL DEFAULT '0',
	`AccountSubscriptionID` INT(11) NULL DEFAULT '0',
	`AccountName` VARCHAR(200) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`AccountCLI` VARCHAR(300) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`InboundDiscountPlans` INT(11) NULL DEFAULT '0',
	`OutboundDiscountPlans` INT(11) NULL DEFAULT '0',
	`created_at` DATETIME NULL DEFAULT NULL,
	`updated_at` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`SubscriptionDiscountPlanID`),
	UNIQUE INDEX `IX_UNIQUE_ACCOUNTCLI` (`AccountCLI`),
	UNIQUE INDEX `IX_UNIQUE_ACCOUNTNAME` (`AccountName`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;

/* add payment gateway*/ 
INSERT INTO `tblIntegration` (`IntegrationID`, `CompanyId`, `Title`, `Slug`, `ParentID`) VALUES ('25', '1', 'MerchantWarrior', 'merchantwarrior', '4');

/* add billing gateway*/ 
INSERT INTO `tblGateway` (`GatewayID`, `Title`, `Name`, `Status`, `CreatedBy`, `created_at`, `updated_at`) VALUES ('15', 'Sippy SQL', 'SippySQL', '1', 'RateManagementSystem', '2018-06-07 16:59:07', '2018-06-07 16:59:10');

/* add billing gateway configurations*/ 
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (15, 'Database Server', 'dbserver', 1, '2018-05-29 13:06:00', NULL, NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (15, 'Database Name', 'dbname', 1, '2018-06-15 13:06:00', NULL, NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (15, 'Database User Name', 'username', 1, '2018-05-29 13:06:00', NULL, NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (15, 'Database Password', 'password', 1, '2018-05-29 13:06:00', NULL, NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (15, 'Authentication Rule', 'NameFormat', 1, '2018-05-29 13:06:00', NULL, NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (15, 'Billing Time', 'BillingTime', 1, '2018-08-13 07:58:00', NULL, NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (15, 'CDR ReRate', 'RateCDR', 1, '2018-12-21 11:19:00', NULL, NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (15, 'Rate Format', 'RateFormat', 1, '2018-12-21 11:19:00', NULL, NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (15, 'CLI Translation Rule', 'CLITranslationRule', 1, '2018-05-29 10:39:33', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (15, 'CLD Translation Rule', 'CLDTranslationRule', 1, '2018-05-29 10:39:33', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (15, 'Prefix Translation Rule', 'PrefixTranslationRule', 1, '2018-05-29 00:00:00', 'RateManagementSystem', NULL, NULL);
INSERT INTO `tblGatewayConfig` (`GatewayID`, `Title`, `Name`, `Status`, `Created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`) VALUES (15, 'Allow Account Import', 'AllowAccountImport', 1, '2018-05-29 11:19:00', 'RateManagementSystem', NULL, NULL);

/* Add Cron command For Billing Gateway*/ 
INSERT INTO `tblCronJobCommand` (`CompanyID`, `GatewayID`, `Title`, `Command`, `Settings`, `Status`, `created_at`, `created_by`) VALUES (1, 15, 'Download SippySQL CDR', 'sippysqlaccountusage', '[[{"title":"SippySQL Max Interval","type":"text","value":"","name":"MaxInterval"},{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"}]]', 1, '2018-06-08 06:25:14', 'RateManagementSystem');

/* insert quickbook payment post job type */
INSERT INTO `tblJobType` (`Code`, `Title`, `CreatedDate`, `CreatedBy`) VALUES ('QPP', 'QuickBook Payment Post', '2018-07-06 18:20:26', 'RateManagementSystem');

INSERT INTO `tblIntegration` (`CompanyId`, `Title`, `Slug`, `ParentID`) VALUES ('1', 'Quickbook Desktop', 'quickbookdesktop', '15');

/*insert tblcreditnotes template for email (insert in database manually for each company id)*/

INSERT INTO `tblEmailTemplate` (`CompanyID`, `LanguageID`, `TemplateName`, `Subject`, `TemplateBody`, `created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`, `userID`, `Type`, `EmailFrom`, `StaticType`, `SystemType`, `Status`, `StatusDisabled`, `TicketTemplate`) VALUES (1, 43, 'CreditNotes Send', 'New creditnotes {{CreditNotesNumber}} from {{CompanyName}} ', 'Hi {{AccountName}},<br><br>\r\n\r\n\r\n{{CompanyName}} has sent you an creditnotes of {{CreditnotesGrandTotal}} {{Currency}}, \r\nto download copy of your creditnotes please click the below link. <br><br>\r\n\r\n\r\n<div>\r\n<!--[if mso]>\r\n<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{CreditNotesLink}}" style="height:30px;v-text-anchor:middle;width:100px;" arcsize="10%" strokecolor="#ff9600" fillcolor="#ff9600">\r\n <w:anchorlock/>\r\n <center style="color:#ffffff;font-family:sans-serif;font-size:13px;font-weight:bold;">View CreditNotes</center>\r\n </v:roundrect>\r\n<![endif]--> \r\n<!--[if !mso]><!-- ><![endif]--> \r\n\r\n<a href="{{CreditNotesLink}}" style="background-color:#ff9600;border:2px solid #ff9600;border-radius:4px;color:#ffffff;display:inline-block;font-family:sans-serif;font-size:13px;font-weight:bold;line-height:30px;text-align:center;text-decoration:none;width:100px;-webkit-text-size-adjust:none;mso-hide:all;" title="Link: {{CreditNotesLink}}">View CreditNotes</a>\r\n</div>\r\n<br><br>\r\n\r\n\r\n\r\nBest Regards,<br><br>\r\n\r\n\r\n{{CompanyName}}\r\n<br>', '2017-02-13 10:28:29', 'Sumera Saeed', '2017-04-17 11:15:19', 'Sumera Saeed', NULL, 5, 'sumera.staging@code-desk.com', 1, 'CreditNotesSingleSend', 1, 1, 0);


