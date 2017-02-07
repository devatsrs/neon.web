DROP PROCEDURE IF EXISTS `prc_unsetCDRUsageAccount`;
DELIMITER //
CREATE DEFINER=`neon-user-abubakar`@`122.129.78.153` PROCEDURE `prc_unsetCDRUsageAccount`(
	IN `p_CompanyID` INT,
	IN `p_IPs` LONGTEXT,
	IN `p_StartDate` VARCHAR(50),
	IN `p_Confirm` INT
)
BEGIN

	DECLARE v_AccountID int;

	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_AccountID = 0;
		SELECT DISTINCT GAC.AccountID INTO v_AccountID 
		FROM NeonBillingDev.tblGatewayAccount GAC
		WHERE GAC.CompanyID = p_CompanyID
		AND AccountID IS NOT NULL
		AND  FIND_IN_SET(GAC.GatewayAccountID, p_IPs) > 0
		LIMIT 1;
	
	IF v_AccountID = 0
	THEN
		SELECT DISTINCT AccountID INTO v_AccountID FROM tblUsageHeader UH
			WHERE UH.CompanyID = p_CompanyID
			AND AccountID IS NOT NULL
			AND  FIND_IN_SET(UH.CompanyGatewayID, p_IPs) > 0
			LIMIT 1; 
	END IF;
	
	IF v_AccountID = 0
	THEN
		SELECT DISTINCT AccountID INTO v_AccountID FROM tblVendorCDRHeader VH
			WHERE VH.CompanyID = p_CompanyID
			AND AccountID IS NOT NULL
			AND  FIND_IN_SET(VH.GatewayAccountID, p_IPs) > 0
			LIMIT 1; 
	END IF;
	IF v_AccountID >0 AND p_Confirm = 1 THEN
			UPDATE NeonBillingDev.tblGatewayAccount GAC SET GAC.AccountID = NULL
			WHERE GAC.CompanyID = p_CompanyID
			AND  FIND_IN_SET(GAC.GatewayAccountID, p_IPs) > 0;
	
			Update tblUsageHeader SET AccountID = NULL
			WHERE CompanyID = p_CompanyID
			AND FIND_IN_SET(GatewayAccountID,p_IPs)>0			
			AND StartDate >= p_StartDate;
						
			Update tblVendorCDRHeader SET AccountID = NULL
			WHERE CompanyID = p_CompanyID
			AND FIND_IN_SET(GatewayAccountID,p_IPs)>0
			AND StartDate >= p_StartDate;
	SET v_AccountID = -1;
	END IF;

	SELECT v_AccountID as `Status`;

SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
END//
DELIMITER ;