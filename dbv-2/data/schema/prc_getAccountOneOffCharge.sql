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
	LEFT JOIN NeonRMDev.tblAccountBilling 
		ON tblAccountBilling.AccountID = tblAccountOneOffCharge.AccountID
	WHERE tblAccountOneOffCharge.AccountID = p_AccountID
	AND tblAccountOneOffCharge.Date BETWEEN p_StartDate AND p_EndDate
	AND ( p_ServiceID = 0 OR ( p_ServiceID = 1 AND tblAccountBilling.AccountID IS NOT NULL))
	ORDER BY SequenceNo ASC;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END