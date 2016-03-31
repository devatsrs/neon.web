CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_VendorBlockUnblockByAccount`(IN `p_CompanyId` int, IN `p_AccountId` int , IN `p_TrunkID` varchar(50) , IN `p_CountryId` longtext, IN `p_RateId` longtext, IN `p_Username` varchar(100), IN `p_status` varchar(100), IN `p_code` VARCHAR(50), IN `p_blockunblockby` varchar(100), IN `p_action` int)
BEGIN
  SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
  if(p_blockunblockby ='country')

  THEN

  IF p_action = 1
  THEN
  INSERT INTO tblVendorBlocking
      (
         `AccountId`
         ,CountryId
         ,`TrunkID`
         ,`BlockedBy`
      )
  SELECT  p_AccountId as AccountId
         ,tblCountry.CountryID as CountryId
         ,p_TrunkID as TrunkID
         ,p_Username as BlockedBy
                FROM    tblCountry
                        LEFT JOIN tblVendorBlocking ON tblVendorBlocking.CountryId = tblCountry.CountryID AND TrunkID = p_TrunkID AND AccountId = p_AccountId
                WHERE   ( p_CountryId ='' OR  FIND_IN_SET(tblCountry.CountryID, p_CountryId) )
                  AND ( p_status = 'All' or ( CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END ) = p_status)
						AND tblVendorBlocking.VendorBlockingId IS NULL;
 END IF;

 IF p_action = 2
  THEN

	delete tblVendorBlocking from tblVendorBlocking INNER JOIN(
	select vb.VendorBlockingId FROM `tblVendorBlocking` vb
					INNER JOIN (
			SELECT  p_AccountId as AccountId
			 ,tblCountry.CountryID as CountryId
			 ,p_TrunkID as TrunkID
			 ,p_Username as BlockedBy
					FROM    tblCountry
							LEFT JOIN tblVendorBlocking ON tblVendorBlocking.CountryId = tblCountry.CountryID AND TrunkID = p_TrunkID AND AccountId = p_AccountId
					WHERE   ( p_CountryId ='' OR  FIND_IN_SET(tblCountry.CountryID, p_CountryId) )
					AND ( p_status = 'All' or ( CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END ) = p_status)
					) v on vb.CountryId = v.CountryId) vb2 on vb2.VendorBlockingId=tblVendorBlocking.VendorBlockingId;			
  END IF;

  END IF;

   if(p_blockunblockby ='code')

  THEN
  IF p_action = 1
  THEN

  -- block
  INSERT INTO tblVendorBlocking
      (
         `AccountId`
         ,`RateId`
         ,`TrunkID`
         ,`BlockedBy`
      )
  SELECT   p_AccountId as AccountId, tblRate.RateID as RateId,p_TrunkID as TrunkID ,p_Username as BlockedBy

                FROM    tblRate
            INNER JOIN tblVendorRate ON tblRate.RateID = tblVendorRate.RateId AND AccountID = p_AccountId AND tblVendorRate.TrunkID = p_TrunkID   
			INNER JOIN tblVendorTrunk ON tblVendorTrunk.CodeDeckId = tblRate.CodeDeckId AND tblVendorTrunk.AccountID = p_AccountId AND tblVendorTrunk.TrunkID = p_TrunkID   
                        LEFT JOIN tblVendorBlocking ON tblVendorBlocking.RateId = tblVendorRate.RateID and tblVendorRate.TrunkID = tblVendorBlocking.TrunkID and tblVendorRate.AccountId = tblVendorBlocking.AccountId		
                        
                WHERE   (p_RateId IS NULL OR FIND_IN_SET(tblRate.RateID, p_RateId) )
						AND ( p_CountryId ='' OR tblRate.CountryID = p_CountryId)
                        AND ( tblRate.CompanyID = p_CompanyId )
                        AND ( p_code IS NULL OR Code LIKE REPLACE(p_code,'*', '%') )
                        AND ( p_status = 'All' or ( CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END ) = p_status)    
						AND tblVendorBlocking.VendorBlockingId IS NULL;
 END IF;

 IF p_action = 2
  THEN

	delete tblVendorBlocking from tblVendorBlocking INNER JOIN(
		select VendorBlockingId FROM `tblVendorBlocking` vb  INNER JOIN (
			SELECT   p_AccountId as AccountId, tblRate.RateID as RateId,p_TrunkID as TrunkID ,p_Username as BlockedBy
                FROM    `tblRate`
            INNER JOIN tblVendorRate ON tblRate.RateID = tblVendorRate.RateId AND AccountID = p_AccountId AND tblVendorRate.TrunkID = p_TrunkID
			INNER JOIN tblVendorTrunk ON tblVendorTrunk.CodeDeckId = tblRate.CodeDeckId AND tblVendorTrunk.AccountID = p_AccountId AND tblVendorTrunk.TrunkID = p_TrunkID
            LEFT JOIN tblVendorBlocking ON tblVendorBlocking.RateId = tblVendorRate.RateID and tblVendorRate.TrunkID = tblVendorBlocking.TrunkID and tblVendorRate.AccountId = tblVendorBlocking.AccountId
            WHERE   (p_RateId IS NULL OR FIND_IN_SET(tblRate.RateID, p_RateId) )
            
            AND ( p_CountryId ='' OR tblRate.CountryID = p_CountryId)
                        AND ( tblRate.CompanyID = p_CompanyId )
                        AND ( p_code IS NULL OR Code LIKE REPLACE(p_code,'*', '%') )
                        AND ( p_status = 'All' or ( CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END ) = p_status)
		)v on vb.RateID = v.RateID )vb2 on vb2.VendorBlockingId=tblVendorBlocking.VendorBlockingId;	

  END IF;

  END IF;
  
  SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END