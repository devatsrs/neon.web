CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_checkCustomerCli`(
	IN `p_CompanyID` INT,
	IN `p_CustomerCLI` varchar(50)
)
BEGIN
     
	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
    
    SELECT AccountID
    FROM tblAccountAuthenticate 
    WHERE CompanyID = p_CompanyID AND  
	 ( FIND_IN_SET(p_CustomerCLI,CustomerAuthValue)!= 0 OR FIND_IN_SET(p_CustomerCLI,VendorAuthValue)!= 0 );  
    
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END