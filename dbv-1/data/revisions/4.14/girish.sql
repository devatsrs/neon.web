USE Ratemanagement3;

INSERT INTO tblGatewayConfig (GatewayConfigID, GatewayID, Title, Name, Status, Created_at, CreatedBy, updated_at, ModifiedBy) VALUES (123, 8, 'Auto Add IP', 'AutoAddIP', 1, '2017-08-29 11:45:38', 'RateManagementSystem', NULL, NULL);
INSERT INTO tblGatewayConfig (GatewayConfigID, GatewayID, Title, Name, Status, Created_at, CreatedBy, updated_at, ModifiedBy) VALUES (124, 3, 'Auto Add IP', 'AutoAddIP', 1, '2017-08-29 11:45:38', 'RateManagementSystem', NULL, NULL);


UPDATE tblCompanyConfiguration SET `Value`='{"ThresholdTime":"500","SuccessEmail":"","ErrorEmail":"","JobTime":"DAILY","JobInterval":"1","JobDay":["SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"02:00:00 AM"}' WHERE  `Key`='CUSTOMER_SUMMARYDAILY_CRONJOB';
UPDATE tblCompanyConfiguration SET `Value`='{"ThresholdTime":"500","SuccessEmail":"","ErrorEmail":"","JobTime":"DAILY","JobInterval":"1","JobDay":["SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"2:30:00 AM"}' WHERE  `Key`='VENDOR_SUMMARYDAILY_CRONJOB';

CREATE TABLE IF NOT EXISTS `tblGatewayCustomerRate` (
  `CustomerRateID` int(11) NOT NULL AUTO_INCREMENT,
  `Code` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `Rate` decimal(18,6) NOT NULL DEFAULT '0.000000',
  `EffectiveDate` date DEFAULT NULL,
  `Interval1` int(11) DEFAULT NULL,
  `IntervalN` int(11) DEFAULT NULL,
  `ConnectionFee` decimal(18,6) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  PRIMARY KEY (`CustomerRateID`),
  KEY `IX_CustomerID` (`CustomerID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES ( 1, 'CUSTOMER_DASHBOARD_DISPLAY', '1'),
( 1, 'CUSTOMER_NOTICEBOARD_DISPLAY', '1'),
( 1, 'CUSTOMER_TICKET_DISPLAY', '1'),
( 1, 'CUSTOMER_BILLING_DISPLAY', '1'),
( 1, 'CUSTOMER_BANALYSIS_DISPLAY', '1'),
( 1, 'CUSTOMER_INVOICE_DISPLAY', '1'),
( 1, 'CUSTOMER_PAYMENT_DISPLAY', '1'),
( 1, 'CUSTOMER_STATEMENT_DISPLAY', '1'),
( 1, 'CUSTOMER_PAYMENT_PROFILE_DISPLAY', '1'),
( 1, 'CUSTOMER_CDR_DISPLAY', '1'),
( 1, 'CUSTOMER_ANALYSIS_DISPLAY', '1'),
( 1, 'CUSTOMER_PROFILE_DISPLAY', '1');

INSERT INTO `tblCompanyConfiguration` (`CompanyID`, `Key`, `Value`) VALUES (1, 'CUSTOMER_RATE_DISPLAY', '1');


ALTER TABLE `tblAccount`
	ADD COLUMN `DisplayRates` INT NULL ;