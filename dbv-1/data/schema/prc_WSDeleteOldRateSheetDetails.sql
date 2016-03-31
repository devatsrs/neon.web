CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSDeleteOldRateSheetDetails`(IN `p_LatestRateSheetID` INT , IN `p_customerID` INT , IN `p_rateSheetCategory` VARCHAR(50)
)
BEGIN
		SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
        
        DELETE  tblRateSheetDetails
        FROM    tblRateSheetDetails
                JOIN tblRateSheet ON tblRateSheet.RateSheetID = tblRateSheetDetails.RateSheetID
        WHERE   CustomerID = p_customerID
                AND Level = p_rateSheetCategory
                AND tblRateSheetDetails.RateSheetID <> p_LatestRateSheetID; 
      SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
                
END