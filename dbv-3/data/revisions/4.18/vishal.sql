USE `RMCDR3`;

-- Dumping structure for procedure NeonCDRDev.prc_deleteVendorCDRByRetention
DROP PROCEDURE IF EXISTS `prc_deleteVendorCDRByRetention`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_deleteVendorCDRByRetention`(
	IN `p_CompanyID` INT,
	IN `p_DeleteDate` DATETIME,
	IN `p_ActionName` VARCHAR(200)
)
BEGIN
 	
 	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	IF p_ActionName = 'CDR'
	THEN
	
		DELETE tblVendorCDR
		 FROM tblVendorCDR
			 INNER JOIN tblVendorCDRHeader
			 	 ON tblVendorCDR.VendorCDRHeaderID = tblVendorCDRHeader.VendorCDRHeaderID
		 WHERE StartDate = p_DeleteDate
		  AND CompanyID = p_CompanyID;
		 
		 
		 
		 DELETE tblVendorCDRFailed
		  FROM tblVendorCDRFailed
		 	 INNER JOIN tblVendorCDRHeader
			  	 ON tblVendorCDRFailed.VendorCDRHeaderID = tblVendorCDRHeader.VendorCDRHeaderID
		 WHERE StartDate = p_DeleteDate
		  AND CompanyID = p_CompanyID;
		
		
		
		DELETE FROM tblVendorCDRHeader
		 WHERE StartDate = p_DeleteDate
		  AND CompanyID = p_CompanyID;

	END IF;
	
	IF (p_ActionName = 'CDRFailedCalls')
	THEN

		DELETE tblVendorCDRFailed
		  FROM tblVendorCDRFailed
		 	 INNER JOIN tblVendorCDRHeader
			  	 ON tblVendorCDRFailed.VendorCDRHeaderID = tblVendorCDRHeader.VendorCDRHeaderID
		 WHERE StartDate = p_DeleteDate
		  AND CompanyID = p_CompanyID;
	
	END IF;	
		
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
END//
DELIMITER ;


-- Dumping structure for procedure NeonCDRDev.prc_deleteCustomerCDRByRetention
DROP PROCEDURE IF EXISTS `prc_deleteCustomerCDRByRetention`;
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_deleteCustomerCDRByRetention`(
	IN `p_CompanyID` INT,
	IN `p_DeleteDate` DATETIME,
	IN `p_ActionName` VARCHAR(200)	
)
BEGIN
 	
 	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
	IF p_ActionName = 'CDR'
	THEN

		DELETE tblUsageDetails
		 FROM tblUsageDetails
			 INNER JOIN tblUsageHeader
			 	 ON tblUsageDetails.UsageHeaderID = tblUsageHeader.UsageHeaderID
		 WHERE StartDate = p_DeleteDate
		  AND CompanyID = p_CompanyID;
		 
		 DELETE tblUsageDetailFailedCall
		  FROM tblUsageDetailFailedCall
		 	 INNER JOIN tblUsageHeader
			  	 ON tblUsageDetailFailedCall.UsageHeaderID = tblUsageHeader.UsageHeaderID
		 WHERE StartDate = p_DeleteDate
		  AND CompanyID = p_CompanyID;
		  
		  DELETE FROM tblUsageHeader
		 WHERE StartDate = p_DeleteDate
		  AND CompanyID = p_CompanyID;

	END IF;
		
	IF (p_ActionName = 'CDRFailedCalls')
	THEN

		DELETE tblUsageDetailFailedCall
		  FROM tblUsageDetailFailedCall
		 	 INNER JOIN tblUsageHeader
			  	 ON tblUsageDetailFailedCall.UsageHeaderID = tblUsageHeader.UsageHeaderID
		 WHERE StartDate = p_DeleteDate
		  AND CompanyID = p_CompanyID;
	
	END IF;	
		
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
END//
DELIMITER ;