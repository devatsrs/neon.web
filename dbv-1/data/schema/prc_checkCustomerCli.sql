CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_checkCustomerCli`(IN `p_CompanyID` INT, IN `p_CustomerCLI` varchar(50))
BEGIN
     
	 SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
    SELECT AccountID
    FROM tblAccount a
    WHERE a.CompanyId =p_CompanyID AND FIND_IN_SET(p_CustomerCLI,CustomerCLI)!= 0;
    
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END