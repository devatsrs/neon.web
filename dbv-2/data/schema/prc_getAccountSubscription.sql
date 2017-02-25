CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getAccountSubscription`(
	IN `p_AccountID` INT,
	IN `p_ServiceID` INT
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT
		tblAccountSubscription.*
	FROM tblAccountSubscription
	INNER JOIN tblBillingSubscription
		ON tblAccountSubscription.SubscriptionID = tblBillingSubscription.SubscriptionID
	INNER JOIN NeonRMDev.tblAccountService
		ON tblAccountService.AccountID = tblAccountSubscription.AccountID AND tblAccountService.ServiceID = tblAccountSubscription.ServiceID 
	LEFT JOIN NeonRMDev.tblAccountBilling 
		ON tblAccountBilling.AccountID = tblAccountSubscription.AccountID AND tblAccountBilling.ServiceID =  tblAccountSubscription.ServiceID
	WHERE tblAccountSubscription.AccountID = p_AccountID
	AND Status = 1
	AND ( (p_ServiceID = 0 AND tblAccountBilling.ServiceID IS NULL) OR  tblAccountBilling.ServiceID = p_ServiceID)
	ORDER BY SequenceNo ASC;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END