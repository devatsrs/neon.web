CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetVendorPreference`(IN `p_companyid` INT , IN `p_AccountID` INT, IN `p_trunkID` INT, IN `p_contryID` INT , IN `p_code` VARCHAR(50) , IN `p_description` VARCHAR(50) , IN `p_PageNumber` INT , IN `p_RowspPage` INT , IN `p_lSortCol` VARCHAR(50) , IN `p_SortOrder` VARCHAR(5) , IN `p_isExport` INT )
BEGIN	

		 
		DECLARE v_CodeDeckId_ int;
		DECLARE v_OffSet_ int;
		SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

		SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
		select CodeDeckId into v_CodeDeckId_  from tblVendorTrunk where AccountID = p_AccountID and TrunkID = p_trunkID;
        
         IF p_isExport = 0
			THEN

				SELECT
					tblRate.RateID,
					Code,
					Preference,
					Description,
					VendorPreferenceID
				FROM  tblVendorRate
				JOIN tblRate
					ON tblVendorRate.RateId = tblRate.RateId
				LEFT JOIN tblVendorPreference 
					ON tblVendorPreference.AccountId = tblVendorRate.AccountId
                    AND tblVendorPreference.TrunkID = tblVendorRate.TrunkID
                    AND tblVendorPreference.RateId = tblVendorRate.RateId
				WHERE (p_contryID IS NULL OR CountryID = p_contryID)
				AND (p_code IS NULL OR Code LIKE REPLACE(p_code, '*', '%'))
				AND (p_description IS NULL OR tblRate.Description LIKE REPLACE(p_description, '*', '%'))
				AND (tblRate.CompanyID = p_companyid)
				AND tblVendorRate.TrunkID = p_trunkID
				AND tblVendorRate.AccountID = p_AccountID
				AND CodeDeckId = v_CodeDeckId_
				ORDER BY CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
					END DESC,
					CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
					END ASC,					 
					CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateDESC') THEN Preference
					END DESC,
					CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'RateASC') THEN Preference
					END ASC,
					CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'VendorPreferenceIDDESC') THEN VendorPreferenceID
					END DESC,
					CASE
						WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'VendorPreferenceIDASC') THEN VendorPreferenceID
					END ASC
			LIMIT p_RowspPage OFFSET v_OffSet_;



				SELECT
					COUNT(RateID) AS totalcount
				FROM (SELECT
					tblRate.RateId,
					EffectiveDate
				FROM tblVendorRate
				JOIN tblRate
					ON tblVendorRate.RateId = tblRate.RateId
				LEFT JOIN tblVendorPreference 
					ON tblVendorPreference.AccountId = tblVendorRate.AccountId
                    AND tblVendorPreference.TrunkID = tblVendorRate.TrunkID
                    AND tblVendorPreference.RateId = tblVendorRate.RateId
				WHERE (p_contryID IS NULL
				OR CountryID = p_contryID)
				AND (p_code IS NULL
				OR Code LIKE REPLACE(p_code, '*', '%'))
				AND (p_description IS NULL
				OR tblRate.Description LIKE REPLACE(p_description, '*', '%'))
				AND (tblRate.CompanyID = p_companyid)
				AND tblVendorRate.TrunkID = p_trunkID
				AND tblVendorRate.AccountID = p_AccountID
				AND CodeDeckId = v_CodeDeckId_
				
			) AS tbl2;

		END IF;
	
		IF p_isExport = 1
		THEN

			SELECT
				Code,
				tblVendorPreference.Preference,
				Description
			FROM tblVendorRate
			JOIN tblRate
				ON tblVendorRate.RateId = tblRate.RateId
			LEFT JOIN tblVendorPreference 
					ON tblVendorPreference.AccountId = tblVendorRate.AccountId
                    AND tblVendorPreference.TrunkID = tblVendorRate.TrunkID
                    AND tblVendorPreference.RateId = tblVendorRate.RateId
			WHERE (p_contryID IS NULL OR CountryID = p_contryID)
			AND (p_code IS NULL OR Code LIKE REPLACE(p_code, '*', '%'))
			AND (p_description IS NULL OR tblRate.Description LIKE REPLACE(p_description, '*', '%'))
			AND (tblRate.CompanyID = p_companyid)
			AND tblVendorRate.TrunkID = p_trunkID
			AND tblVendorRate.AccountID = p_AccountID
			AND CodeDeckId = v_CodeDeckId_;
			
		END IF;
		IF p_isExport = 2
		THEN

			SELECT
				tblRate.RateID as RateID,
            Code,
            tblVendorPreference.Preference,
            Description
			FROM tblVendorRate
			JOIN tblRate
				ON tblVendorRate.RateId = tblRate.RateId
			LEFT JOIN tblVendorPreference 
					ON tblVendorPreference.AccountId = tblVendorRate.AccountId
                    AND tblVendorPreference.TrunkID = tblVendorRate.TrunkID
                    AND tblVendorPreference.RateId = tblVendorRate.RateId
			WHERE (p_contryID IS NULL OR CountryID = p_contryID)
			AND (p_code IS NULL OR Code LIKE REPLACE(p_code, '*', '%'))
			AND (p_description IS NULL OR tblRate.Description LIKE REPLACE(p_description, '*', '%'))
			AND (tblRate.CompanyID = p_companyid)
			AND tblVendorRate.TrunkID = p_trunkID
			AND tblVendorRate.AccountID = p_AccountID
			AND CodeDeckId = v_CodeDeckId_;
			
		END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
		
END