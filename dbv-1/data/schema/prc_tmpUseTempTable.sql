CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_tmpUseTempTable`()
BEGIN

    
 
    
     Call prc_createtemptable();

	select * from tmpSplit;
	
	
END