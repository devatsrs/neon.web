CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_deleteVendorCDRByRetention`(IN `p_CompanyID` INT, IN `p_DeleteDate` DATETIME)
BEGIN
 	
 	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	-- DELETE all Record of vendor cdr by date
	
	-- DELETE tblVendorCDR table
	DELETE tblVendorCDR FROM tblVendorCDR
		 INNER JOIN tblVendorCDRHeader
		 	 ON tblVendorCDR.VendorCDRHeaderID=tblVendorCDRHeader.VendorCDRHeaderID
	 WHERE StartDate=p_DeleteDate AND CompanyID = p_CompanyID;
	 
	 -- DELETE tblVendorCDRFailed table
	 
	 DELETE tblVendorCDRFailed FROM tblVendorCDRFailed
	 	 INNER JOIN tblVendorCDRHeader
		  	 ON tblVendorCDRFailed.VendorCDRHeaderID=tblVendorCDRHeader.VendorCDRHeaderID
	 WHERE StartDate=p_DeleteDate AND CompanyID = p_CompanyID;
	
	-- DELETE tblVendorCDRHeader table
	
	DELETE FROM tblVendorCDRHeader WHERE StartDate=p_DeleteDate AND CompanyID = p_CompanyID;
		
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
END