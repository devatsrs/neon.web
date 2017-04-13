CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getAccountService`(
	IN `p_AccountID` INT,
	IN `p_ServiceID` INT
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT
		tblAccountService.*
	FROM tblAccountService
	LEFT JOIN tblAccountBilling 
		ON tblAccountBilling.AccountID = tblAccountService.AccountID AND tblAccountBilling.ServiceID =  tblAccountService.ServiceID
	WHERE tblAccountService.AccountID = p_AccountID
	AND Status = 1
	AND ( (p_ServiceID = 0 AND tblAccountBilling.ServiceID IS NULL) OR  tblAccountBilling.ServiceID = p_ServiceID);
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END