CREATE DEFINER=`root`@`localhost` PROCEDURE `migrateService`()
BEGIN

IF ( (SELECT COUNT(*) FROM NeonBillingDev.tblAccountSubscription ) > 0 OR (SELECT COUNT(*) FROM NeonBillingDev.tblAccountOneOffCharge ) > 0  OR (SELECT COUNT(*) FROM tblCLIRateTable WHERE CompanyID =1) > 0)
THEN

INSERT INTO `tblService` (`ServiceID`, `ServiceName`, `ServiceType`, `CompanyID`, `Status`, `created_at`, `updated_at`, `CompanyGatewayID`) VALUES (1, 'Default Service', 'voice', 1, 1, '2017-05-08 13:02:01', '2017-05-09 02:00:41', 0);

INSERT INTO tblAccountService (AccountID,ServiceID,CompanyID,Status)
SELECT AccountID,1,CompanyId,1 FROM tblAccount WHERE AccountType = 1 AND Status =1 AND VerificationStatus = 2 AND CompanyId = 1;

UPDATE NeonBillingDev.tblAccountSubscription SET ServiceID =1 WHERE ServiceID = 0;
UPDATE NeonBillingDev.tblAccountOneOffCharge SET ServiceID =1 WHERE ServiceID = 0;
UPDATE tblCLIRateTable SET ServiceID =1 WHERE CompanyID =1 AND ServiceID = 0;
UPDATE tblAccountDiscountPlan SET ServiceID =1 WHERE ServiceID = 0;

END IF;

END