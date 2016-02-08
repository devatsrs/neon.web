CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_checkUniqueID`(IN `p_CompanyGatewayID` int, IN `p_ID` int)
BEGIN
 	
 	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	SELECT ID  FROM tblUsageDetails inner join  tblUsageHeader on tblUsageHeader.UsageHeaderID = tblUsageDetails.UsageHeaderID where tblUsageHeader.CompanyGatewayID = p_CompanyGatewayID and tblUsageDetails.ID =  p_ID;
	
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
END