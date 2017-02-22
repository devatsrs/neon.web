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
	LEFT JOIN NeonRMDev.tblAccountBilling 
		ON tblAccountBilling.AccountID = tblAccountSubscription.AccountID
	WHERE tblAccountSubscription.AccountID = p_AccountID
	AND ( p_ServiceID = 0 OR ( p_ServiceID = 1 AND tblAccountBilling.AccountID IS NOT NULL))
	ORDER BY SequenceNo ASC;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END