USE Ratemanagement3;

INSERT INTO tblGatewayConfig (GatewayConfigID, GatewayID, Title, Name, Status, Created_at, CreatedBy, updated_at, ModifiedBy) VALUES (123, 8, 'Auto Add IP', 'AutoAddIP', 1, '2017-08-29 11:45:38', 'RateManagementSystem', NULL, NULL);
INSERT INTO tblGatewayConfig (GatewayConfigID, GatewayID, Title, Name, Status, Created_at, CreatedBy, updated_at, ModifiedBy) VALUES (124, 3, 'Auto Add IP', 'AutoAddIP', 1, '2017-08-29 11:45:38', 'RateManagementSystem', NULL, NULL);


UPDATE tblCompanyConfiguration SET `Value`='{"ThresholdTime":"500","SuccessEmail":"","ErrorEmail":"","JobTime":"DAILY","JobInterval":"1","JobDay":["SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"02:00:00 AM"}' WHERE  `Key`='CUSTOMER_SUMMARYDAILY_CRONJOB';
UPDATE tblCompanyConfiguration SET `Value`='{"ThresholdTime":"500","SuccessEmail":"","ErrorEmail":"","JobTime":"DAILY","JobInterval":"1","JobDay":["SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"2:30:00 AM"}' WHERE  `Key`='VENDOR_SUMMARYDAILY_CRONJOB';