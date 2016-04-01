CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_VendorBlockByCodeBlock`(IN `p_AccountId` int , IN `p_Trunk` varchar(50) , IN `p_RateId` int , IN `p_Username` varchar(100)
)
BEGIN
   
   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	IF  (SELECT COUNT(*) FROM tblVendorBlocking WHERE AccountId = p_AccountId AND TrunkID = p_Trunk AND RateId = p_RateId) = 0
	THEN 
	
	  INSERT INTO tblVendorBlocking
	      (
	         `AccountId`        
	         ,`RateId`
	         ,`TrunkID`
	         ,`BlockedBy`
	      )          
	      VALUES (
	        p_AccountId  
	        ,p_RateId
	        ,p_Trunk
	        ,p_Username 
	        );
	END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
		
END