CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSCronJobDeleteOldRateSheetDetails`()
BEGIN
     
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    DELETE tblRateSheetDetails
    FROM tblRateSheetDetails
    INNER JOIN
    (
       SELECT
				DISTINCT
            rs.RateSheetID
         FROM tblRateSheet rs,tblRateSheet rs2
         WHERE rs.CustomerID = rs2.CustomerID
         AND rs.`Level` = rs2.`Level`
         AND rs.RateSheetID < rs2.RateSheetID 
    ) tbl ON tblRateSheetDetails.RateSheetID = tbl.RateSheetID;
        
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
 
END