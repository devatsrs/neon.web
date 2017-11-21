CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_autoUpdateTrunk`(
	IN `p_CompanyID` INT,
	IN `p_CompanyGatewayID` INT
)
BEGIN

	UPDATE RMCDR3.tblUsageDetails
	INNER JOIN RMCDR3.tblUsageHeader
		ON tblUsageDetails.UsageHeaderID = tblUsageHeader.UsageHeaderID
	INNER JOIN Ratemanagement3.tblCustomerTrunk
		ON tblCustomerTrunk.AccountID = tblUsageHeader.AccountID
		AND Status = 1
		AND UseInBilling =1
		AND cld LIKE CONCAT(Prefix , "%")
	INNER JOIN Ratemanagement3.tblTrunk
		ON tblCustomerTrunk.TrunkID = tblTrunk.TrunkID
		SET tblUsageDetails.trunk = tblTrunk.Trunk
	WHERE tblUsageHeader.CompanyID = p_CompanyID
		AND CompanyGatewayID = p_CompanyGatewayID
		AND tblUsageHeader.AccountID IS NOT NULL
		AND tblUsageDetails.is_inbound = 0
		AND tblUsageDetails.trunk = 'other'
		AND area_prefix <> 'other'
		AND tblUsageHeader.StartDate = DATE(now());
		
	UPDATE RMCDR3.tblVendorCDR
	INNER JOIN RMCDR3.tblVendorCDRHeader
		ON tblVendorCDR.VendorCDRHeaderID = tblVendorCDRHeader.VendorCDRHeaderID
	INNER JOIN Ratemanagement3.tblVendorTrunk
		ON tblVendorTrunk.AccountID = tblVendorCDRHeader.AccountID
		AND Status = 1
		AND UseInBilling =1
		AND cld LIKE CONCAT(Prefix , "%")
	INNER JOIN Ratemanagement3.tblTrunk
		ON tblVendorTrunk.TrunkID = tblTrunk.TrunkID
		SET tblVendorCDR.trunk = tblTrunk.Trunk
	WHERE tblVendorCDRHeader.CompanyID = p_CompanyID
		AND CompanyGatewayID = p_CompanyGatewayID
		AND tblVendorCDRHeader.AccountID IS NOT NULL
		AND tblVendorCDR.trunk = 'other'
		AND area_prefix <> 'other'
		AND tblVendorCDRHeader.StartDate = DATE(now());

END