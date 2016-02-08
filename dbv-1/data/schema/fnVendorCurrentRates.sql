CREATE DEFINER=`root`@`localhost` PROCEDURE `fnVendorCurrentRates`(IN `p_companyid` INT , IN `p_codedeckID` INT, IN `p_trunkID` INT , IN `p_countryID` INT , IN `p_code` VARCHAR(50) , IN `p_accountID` INT 
    )
BEGIN
 			    
 			    
	
	-- this procedure is same as viewVendorCurrentRates  -- 29-12-2015
	
	DROP TEMPORARY TABLE IF EXISTS tmp_VendorCurrentRates_;
   CREATE TEMPORARY TABLE IF NOT EXISTS tmp_VendorCurrentRates_(
			AccountId int,
			Code varchar(50),
			Description varchar(200),
			Rate float,
			EffectiveDate date,
			TrunkID int,
			CountryID int,
			RateID int,
			INDEX tmp_VendorCurrentRates_AccountId (`AccountId`,`TrunkID`,`RateId`,`EffectiveDate`)
	);
	INSERT INTO tmp_VendorCurrentRates_ 
	  			    
      SELECT DISTINCT tblVendorRate.AccountId, tblRate.Code, tblRate.Description, tblVendorRate.Rate,
	  DATE_FORMAT (tblVendorRate.EffectiveDate, '%Y-%m-%d') AS EffectiveDate, 
	  tblVendorRate.TrunkID, tblRate.CountryID, tblRate.RateID

                
      FROM      tblVendorRate  
                INNER JOIN tblAccount   ON tblVendorRate.AccountId = tblAccount.AccountID
                INNER JOIN tblRate  ON tblVendorRate.RateId = tblRate.RateID
                LEFT OUTER JOIN tblVendorBlocking AS blockCode   ON tblVendorRate.RateId = blockCode.RateId
                                                              AND tblVendorRate.AccountId = blockCode.AccountId
                                                              AND tblVendorRate.TrunkID = blockCode.TrunkID
                LEFT OUTER JOIN tblVendorBlocking AS blockCountry    ON tblRate.CountryID = blockCountry.CountryId
                                                              AND tblVendorRate.AccountId = blockCountry.AccountId
                                                              AND tblVendorRate.TrunkID = blockCountry.TrunkID
      WHERE     ( EffectiveDate <= NOW() )
                AND tblAccount.CompanyId = p_companyid
                AND tblRate.CodeDeckId = p_codedeckID
                AND tblAccount.IsVendor = 1
                AND tblAccount.Status = 1
                AND tblVendorRate.TrunkID = p_trunkID
                AND ( CHAR_LENGTH(RTRIM(p_code)) = 0
                      OR tblRate.Code LIKE REPLACE(p_code,'*', '%')
                    )
                AND ( p_countryID = ''
                      OR tblRate.CountryID = p_countryID
                    )
                AND blockCode.RateId IS NULL
               AND blockCountry.CountryId IS NULL
                AND ( p_accountID = 0
                      OR ( p_accountID > 0
                           AND tblVendorRate.AccountId = p_accountID
                         )
                    );
END