CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_VendorBlockByCountryUnblock`(IN `p_AccountId` int , IN `p_Trunk` varchar(50) , IN `p_CountryId` int)
BEGIN 
  
  SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
  DELETE FROM `tblVendorBlocking`
   WHERE AccountId = p_AccountId
    AND TrunkID = p_Trunk AND CountryId = p_CountryId;
    
    SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
  
  
END