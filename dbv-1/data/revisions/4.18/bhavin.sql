Use Ratemanagement3;

INSERT INTO `tblCronJobCommand` (`CompanyID`, `GatewayID`, `Title`, `Command`, `Settings`, `Status`, `created_at`, `created_by`)
VALUES (1, NULL, 'Process Call Charges', 'processcallcharges', '[[{"title":"Threshold Time (Minute)","type":"text","value":"","name":"ThresholdTime"},{"title":"Success Email","type":"text","value":"","name":"SuccessEmail"},{"title":"Error Email","type":"text","value":"","name":"ErrorEmail"}]]', 1, NULL, NULL);