CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_checkCustomerCli`(
	IN `p_CompanyID` INT,
	IN `p_CustomerCLI` varchar(50)
)
BEGIN
     
	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
    
    SELECT AccountID
    FROM tblCLIRateTable 
    WHERE CompanyID = p_CompanyID AND  CLI = p_CustomerCLI;
    
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END