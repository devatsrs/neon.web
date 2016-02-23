CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_checkAccountIP`(IN `p_CompanyID` INT, IN `p_AccountIP` VARCHAR(50))
BEGIN
   
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    SELECT AccountID
    FROM tblAccountAuthenticate 
    WHERE CompanyID = p_CompanyID AND  
	 ( FIND_IN_SET(p_AccountIP,CustomerAuthValue)!= 0 OR FIND_IN_SET(p_AccountIP,VendorAuthValue)!= 0 );  
	 
	 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
END