CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_deleteCustomerCDRByRetention`(IN `p_CompanyID` INT, IN `p_DeleteDate` DATETIME)
BEGIN
 	
 	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	-- DELETE all Record of customer cdr by date
	
	-- DELETE tblUsageDetails table
	DELETE tblUsageDetails FROM tblUsageDetails
		 INNER JOIN tblUsageHeader
		 	 ON tblUsageDetails.UsageHeaderID=tblUsageHeader.UsageHeaderID
	 WHERE StartDate=p_DeleteDate AND CompanyID = p_CompanyID;
	 
	 -- DELETE tblUsageDetailFailedCall table
	 
	 DELETE tblUsageDetailFailedCall FROM tblUsageDetailFailedCall
	 	 INNER JOIN tblUsageHeader
		  	 ON tblUsageDetailFailedCall.UsageHeaderID=tblUsageHeader.UsageHeaderID
	 WHERE StartDate=p_DeleteDate AND CompanyID = p_CompanyID;
	
	-- DELETE tblUsageHeader table
	
	DELETE FROM tblUsageHeader WHERE StartDate=p_DeleteDate AND CompanyID = p_CompanyID;
		
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
END