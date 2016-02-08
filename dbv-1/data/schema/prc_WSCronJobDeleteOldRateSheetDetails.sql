CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_WSCronJobDeleteOldRateSheetDetails`()
BEGIN
     
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    DELETE tblRateSheetDetails
    FROM tblRateSheetDetails
    INNER JOIN
    (
        SELECT
            RateSheetID,
            CustomerID,
            @row_num := IF(@prev_customerid = customerid AND @prev_ratesheetid >= ratesheetid ,@row_num+1,1) AS RowID,
            @prev_customerid  := customerid,
				@prev_ratesheetid  := ratesheetid
        FROM tblRateSheet,(SELECT @row_num := 1) x,(SELECT @prev_customerid := '') y ,(SELECT @prev_ratesheetid := '') z
         ORDER BY customerid,ratesheetid DESC
    ) tbl
        ON RowID > 1
        AND tblRateSheetDetails.RateSheetID = tbl.RateSheetID;
        
   SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
 
END