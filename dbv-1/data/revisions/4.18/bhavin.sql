Use Ratemanagement3;

INSERT INTO `tblCronJobCommand` (`CompanyID`, `GatewayID`, `Title`, `Command`, `Settings`, `Status`, `created_at`, `created_by`)
VALUES (1, NULL, 'Process Call Charges', 'processcallcharges', '[[{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"}]]', 1, NULL, NULL);

UPDATE tblCronJobCommand SET Title='PBX Account Block' WHERE Title='Mirta Account Block';

INSERT INTO `tblEmailTemplate` (`CompanyID`, `LanguageID`, `TemplateName`, `Subject`, `TemplateBody`, `created_at`, `CreatedBy`, `updated_at`, `ModifiedBy`, `userID`, `Type`, `EmailFrom`, `StaticType`, `SystemType`, `Status`, `StatusDisabled`, `TicketTemplate`) VALUES (1, 43, 'PBX Account Block Email', '{{AccountName}} - PBX Account Status Changed', '<p>Hi<br></p><p>Account&nbsp; Current Status is {{AccountBlocked}}.</p><p>Regards,</p><p>{{CompanyName}}<br></p>', '2018-05-22 16:42:31', 'System', '2018-05-28 15:38:59', 'System', NULL, 0, '', 1, 'PBXAccountBlockEmail', 1, 0, 0);
