CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_checkAccountIP`(IN `p_CompanyID` INT, IN `p_AccountIP` varchar(50))
BEGIN
   
    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

    SELECT AccountID
    FROM tblAccount a
    WHERE a.CompanyId =p_CompanyID AND  FIND_IN_SET(p_AccountIP,AccountIP)!= 0;  
	 
	 SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
END