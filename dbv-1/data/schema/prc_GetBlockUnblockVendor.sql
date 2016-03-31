CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetBlockUnblockVendor`(IN `p_companyid` INT, IN `p_UserID` int , IN `p_TrunkID` INT, IN `p_CountryIDs` VARCHAR(100), IN `p_CountryCodes` VARCHAR(100), IN `p_isCountry` int , IN `p_action` VARCHAR(10), IN `p_isAllCountry` int )
BEGIN

    SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;
		 
		 /* SET collation_server = 'utf8_unicode_ci';
		 SET collation_connection = 'utf8_unicode_ci';
		 SET collation_database = 'utf8_unicode_ci';
		 */
		 


	if p_isCountry = 0 AND p_action = 0  -- for block country codes
	Then
			SELECT DISTINCT tblVendorRate.AccountId , tblAccount.AccountName
				from tblVendorRate
				inner join tblAccount on tblVendorRate.AccountId = tblAccount.AccountID
						and tblAccount.Status = 1
						and tblAccount.CompanyID = p_companyid 
						AND tblAccount.IsVendor = 1 
						and tblAccount.AccountType = 1						
				inner join tblRate on tblVendorRate.RateId  = tblRate.RateId  
					and  FIND_IN_SET(tblRate.Code,p_CountryCodes) != 0 
					and tblVendorRate.TrunkID = p_TrunkID
				inner join tblVendorTrunk on tblVendorTrunk.CompanyID = p_companyid
					and tblVendorTrunk.AccountID =	tblVendorRate.AccountID 
					and tblVendorTrunk.Status = 1 
					and tblVendorTrunk.TrunkID = p_TrunkID 
				LEFT OUTER JOIN tblVendorBlocking
					ON tblVendorRate.AccountId = tblVendorBlocking.AccountId
						AND tblVendorTrunk.TrunkID = tblVendorBlocking.TrunkID
						AND tblRate.RateID = tblVendorBlocking.RateId											
			WHERE tblVendorBlocking.VendorBlockingId IS NULL
			ORDER BY tblAccount.AccountName;
			
	END IF;

	if p_isCountry = 0 AND p_action = 1	-- for non block country Codes

	Then
		select DISTINCT tblVendorBlocking.AccountId, tblAccount.AccountName
			from tblVendorBlocking
			inner join tblAccount on tblVendorBlocking.AccountId = tblAccount.AccountID
								and tblAccount.Status = 1
								and tblAccount.CompanyID = p_companyid 
								AND tblAccount.IsVendor = 1 
								and tblAccount.AccountType = 1
			inner join tblRate on tblVendorBlocking.RateId  = tblRate.RateId  
				and  FIND_IN_SET(tblRate.Code,p_CountryCodes) != 0  
				and tblVendorBlocking.TrunkID = p_TrunkID
			inner join tblVendorTrunk on tblVendorTrunk.CompanyID = p_companyid 
				and tblVendorTrunk.AccountID = tblVendorBlocking.AccountID 
				and tblVendorTrunk.Status = 1 
				and tblVendorTrunk.TrunkID = p_TrunkID 
			inner join tblVendorRate on tblVendorRate.RateId = tblRate.RateId 
				and tblVendorRate.AccountID = tblVendorBlocking.AccountID 								
				and tblVendorRate.TrunkID = p_TrunkID 
		ORDER BY tblAccount.AccountName;
	END IF;

	if p_isCountry = 1 AND p_action = 0 -- for block country id
	Then
	
		SELECT DISTINCT  tblVendorRate.AccountId , tblAccount.AccountName
			from tblVendorRate
			inner join tblAccount on tblVendorRate.AccountId = tblAccount.AccountID
					and tblAccount.Status = 1
					and tblAccount.CompanyID = p_companyid 
					AND tblAccount.IsVendor = 1 
					and tblAccount.AccountType = 1 										
			inner join tblRate on tblVendorRate.RateId  = tblRate.RateId  
				and  (
						(p_isAllCountry = 1 and p_isCountry = 1 and  tblRate.CountryID in  (SELECT CountryID FROM tblCountry) )
						OR
						(FIND_IN_SET(tblRate.CountryID,p_CountryIDs) != 0 )
						
					)
				
				and tblVendorRate.TrunkID = p_TrunkID
			inner join tblVendorTrunk on tblVendorTrunk.CompanyID = p_companyid
				and tblVendorTrunk.AccountID =	tblVendorRate.AccountID 
				and tblVendorTrunk.Status = 1 
				and tblVendorTrunk.TrunkID = p_TrunkID 
			LEFT OUTER JOIN tblVendorBlocking
				ON tblVendorRate.AccountId = tblVendorBlocking.AccountId
					AND tblVendorTrunk.TrunkID = tblVendorBlocking.TrunkID
					AND tblRate.CountryId = tblVendorBlocking.CountryId												
		WHERE tblVendorBlocking.VendorBlockingId IS NULL
			ORDER BY tblAccount.AccountName;											
										
	END IF;

	if p_isCountry = 1 AND p_action = 1  -- for non block code

	Then
		select DISTINCT tblVendorBlocking.AccountId , tblAccount.AccountName
			from tblVendorBlocking
				inner join tblAccount on tblVendorBlocking.AccountId = tblAccount.AccountID
								and tblAccount.Status = 1
								and tblAccount.CompanyID = p_companyid 
								AND tblAccount.IsVendor = 1 
								and tblAccount.AccountType = 1
			inner join tblRate on tblVendorBlocking.CountryId  = tblRate.CountryId  
				and  (
						(p_isAllCountry = 1 and p_isCountry = 1 and tblRate.CountryID in(SELECT CountryID FROM tblCountry) )
						OR
						( FIND_IN_SET(tblRate.CountryID,p_CountryIDs) != 0 )
						
					) 
 				and tblVendorBlocking.TrunkID = p_TrunkID
			inner join tblVendorTrunk on tblVendorTrunk.CompanyID = p_companyid 
				and tblVendorTrunk.AccountID = tblVendorBlocking.AccountID 
				and tblVendorTrunk.Status = 1 
				and tblVendorTrunk.TrunkID = p_TrunkID 
			inner join tblVendorRate on tblVendorRate.RateId = tblRate.RateId 
				and tblVendorRate.AccountID = tblVendorBlocking.AccountID 								
				and tblVendorRate.TrunkID = p_TrunkID 
			ORDER BY tblAccount.AccountName;
	END IF;
END