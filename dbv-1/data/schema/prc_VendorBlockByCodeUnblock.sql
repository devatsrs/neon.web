CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_VendorBlockByCodeUnblock`(IN `p_AccountId` int , IN `p_Trunk` varchar(50) , IN `p_RateId` int)
BEGIN
   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


  
  DELETE FROM `tblVendorBlocking`
   WHERE AccountId = p_AccountId
    AND TrunkID = p_Trunk AND RateId = p_RateId;

	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	    
END