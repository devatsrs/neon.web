CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_VendorBlockUnblockByAccount`(IN `p_CompanyId` int, IN `p_AccountId` int, IN `p_code` VARCHAR(50),`p_RateId` longtext,IN `p_CountryId` longtext,IN `p_TrunkID` varchar(50) ,     IN `p_Username` varchar(100),IN `p_action` varchar(100) )
BEGIN

  SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

  if(p_action ='country_block')
  THEN
		  
			  INSERT INTO tblVendorBlocking
			  (
					 `AccountId`
					 ,CountryId
					 ,`TrunkID`
					 ,`BlockedBy`
			  )
			  SELECT  
				p_AccountId as AccountId
				,tblCountry.CountryID as CountryId
				,p_TrunkID as TrunkID
				,p_Username as BlockedBy
				FROM    tblCountry
				LEFT JOIN tblVendorBlocking ON tblVendorBlocking.CountryId = tblCountry.CountryID AND TrunkID = p_TrunkID AND AccountId = p_AccountId
				WHERE  ( p_CountryId ='' OR  FIND_IN_SET(tblCountry.CountryID, p_CountryId) ) AND tblVendorBlocking.VendorBlockingId is null;
							  
  END IF;
  
  if(p_action ='country_unblock')
  THEN
  
	delete  from tblVendorBlocking 
	WHERE  AccountId = p_AccountId AND TrunkID = p_TrunkID AND ( p_CountryId ='' OR FIND_IN_SET(CountryId, p_CountryId) );
			

  END IF;

  IF(p_action ='code_block')
  THEN
		   
					IF(p_RateId = '' ) 
					THEN
                            
					  INSERT INTO tblVendorBlocking
					  (
						 `AccountId`
						 ,`RateId`
						 ,`TrunkID`
						 ,`BlockedBy`
					  )
					  SELECT tblVendorRate.AccountID, tblVendorRate.RateId ,tblVendorRate.TrunkID,p_Username as BlockedBy
					  FROM    `tblRate`
					  INNER JOIN tblVendorRate ON tblRate.RateID = tblVendorRate.RateId AND tblVendorRate.AccountID = p_AccountId AND tblVendorRate.TrunkID = p_TrunkID
					  INNER JOIN tblVendorTrunk ON tblVendorTrunk.CodeDeckId = tblRate.CodeDeckId AND tblVendorTrunk.AccountID = p_AccountId AND tblVendorTrunk.TrunkID = p_TrunkID
					  LEFT JOIN tblVendorBlocking ON tblVendorBlocking.RateId = tblVendorRate.RateID and tblVendorRate.TrunkID = tblVendorBlocking.TrunkID and tblVendorRate.AccountId = tblVendorBlocking.AccountId
					  WHERE tblVendorBlocking.VendorBlockingId is null AND ( tblRate.CompanyID = p_CompanyId ) AND ( p_code = '' OR  tblRate.Code LIKE REPLACE(p_code,'*', '%') );
						
					ELSE 
     
						  INSERT INTO tblVendorBlocking
						  (
							 `AccountId`
							 ,`RateId`
							 ,`TrunkID`
							 ,`BlockedBy`
						  )
						  SELECT p_AccountId as AccountId, RateID ,p_TrunkID as TrunkID ,p_Username as BlockedBy 
						  FROM tblRate 
						  WHERE FIND_IN_SET(RateId, p_RateId) > 0 AND CompanyID = p_CompanyId;
					 
					  
			 		END IF;				
				
END IF;
 IF(p_action ='code_unblock')
  THEN
 
			IF(p_RateId = ''  ) THEN
			
			     -- criteria base delete
				delete tblVendorBlocking from tblVendorBlocking 
					INNER JOIN(
						select VendorBlockingId 
						FROM `tblVendorBlocking` vb  
						INNER JOIN 
						(
							SELECT tblRate.RateID ,tblVendorRate.AccountID
								FROM    `tblRate`
							INNER JOIN tblVendorRate ON tblRate.RateID = tblVendorRate.RateId AND tblVendorRate.AccountID = p_AccountId AND tblVendorRate.TrunkID = p_TrunkID
							INNER JOIN tblVendorTrunk ON tblVendorTrunk.CodeDeckId = tblRate.CodeDeckId AND tblVendorTrunk.AccountID = p_AccountId AND tblVendorTrunk.TrunkID = p_TrunkID
							LEFT JOIN tblVendorBlocking ON tblVendorBlocking.RateId = tblVendorRate.RateID and tblVendorRate.TrunkID = tblVendorBlocking.TrunkID and tblVendorRate.AccountId = tblVendorBlocking.AccountId
							WHERE ( tblRate.CompanyID = p_CompanyId )
							 AND ( p_code = '' OR tblRate.Code LIKE REPLACE(p_code,'*', '%') )
						) v on v.AccountID = vb.AccountId  AND  vb.RateID = v.RateID  AND vb.TrunkID = p_TrunkID 
						
					)vb2 on vb2.VendorBlockingId=tblVendorBlocking.VendorBlockingId;	

			ELSE 

                    -- selected codes 
				delete tblVendorBlocking from tblVendorBlocking WHERE AccountId = p_AccountId AND TrunkID = p_TrunkID AND FIND_IN_SET(RateId, p_RateId) > 0;
				
				
			END IF;	
 
  END IF;
  
  SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END