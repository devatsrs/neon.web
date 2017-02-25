CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_getAccountOneOffCharge`(
	IN `p_AccountID` INT,
	IN `p_ServiceID` INT,
	IN `p_StartDate` DATE,
	IN `p_EndDate` DATE
)
BEGIN

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	SELECT
		tblAccountOneOffCharge.*
	FROM tblAccountOneOffCharge
	INNER JOIN NeonRMDev.tblAccountService
		ON tblAccountService.AccountID = tblAccountOneOffCharge.AccountID AND tblAccountService.ServiceID = tblAccountOneOffCharge.ServiceID 
	LEFT JOIN NeonRMDev.tblAccountBilling 
		ON tblAccountBilling.AccountID = tblAccountOneOffCharge.AccountID AND tblAccountBilling.ServiceID = tblAccountOneOffCharge.ServiceID
	WHERE tblAccountOneOffCharge.AccountID = p_AccountID
	AND tblAccountOneOffCharge.Date BETWEEN p_StartDate AND p_EndDate
	AND tblAccountService.Status = 1
	AND ( (p_ServiceID = 0 AND tblAccountBilling.ServiceID IS NULL) OR  tblAccountBilling.ServiceID = p_ServiceID);
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END