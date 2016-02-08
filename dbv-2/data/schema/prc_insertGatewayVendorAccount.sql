CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_insertGatewayVendorAccount`(IN `p_processId` varchar(200), IN `TempTableName` VARCHAR(200))
BEGIN
    
	INSERT INTO tblGatewayAccount (CompanyID, CompanyGatewayID, GatewayAccountID, AccountName,IsVendor)
		SELECT
			distinct
			ud.CompanyID,
			ud.CompanyGatewayID,
			ud.GatewayAccountID,
			ud.GatewayAccountID,
			1 as IsVendor

		FROM tblTempVendorCDR ud
		LEFT JOIN tblGatewayAccount ga
			ON ga.GatewayAccountID = ud.GatewayAccountID
			AND ga.CompanyGatewayID = ud.CompanyGatewayID
			AND ga.CompanyID = ud.CompanyID
		WHERE processid = p_processId
		AND ga.GatewayAccountID IS NULL
		AND ud.GatewayAccountID IS NOT NULL;
		
 
END