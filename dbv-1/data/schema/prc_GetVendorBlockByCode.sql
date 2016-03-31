CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetVendorBlockByCode`(IN `p_companyid` INT , IN `p_AccountID` INT, IN `p_trunkID` INT, IN `p_contryID` INT , IN `p_status` VARCHAR(50) , IN `p_code` VARCHAR(50), IN `p_PageNumber` INT , IN `p_RowspPage` INT , IN `p_lSortCol` VARCHAR(50) , IN `p_SortOrder` VARCHAR(5) , IN `p_isExport` INT)
BEGIN
	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
	
   IF p_isExport = 0
   THEN
   
		SELECT  
            `tblRate`.RateID
           ,`tblRate`.Code
           ,CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END as Status 
           ,`tblRate`.Description
	    FROM      `tblRate`
       INNER JOIN tblVendorRate ON tblRate.RateID = tblVendorRate.RateId AND AccountID = p_AccountID AND tblVendorRate.TrunkID = p_trunkID   
	    INNER JOIN tblVendorTrunk ON tblVendorTrunk.CodeDeckId = tblRate.CodeDeckId AND tblVendorTrunk.AccountID = p_AccountID AND tblVendorTrunk.TrunkID = p_trunkID   
       LEFT JOIN tblVendorBlocking ON tblVendorBlocking.RateId = tblVendorRate.RateID and tblVendorRate.TrunkID = tblVendorBlocking.TrunkID and tblVendorRate.AccountId = tblVendorBlocking.AccountId
		 WHERE ( p_contryID IS NULL OR tblRate.CountryID = p_contryID)
             AND ( tblRate.CompanyID = p_companyid )
             AND ( p_code IS NULL OR Code LIKE REPLACE(p_code,'*', '%') )
             AND ( p_status = 'All' or ( CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END ) = p_status)  
		 ORDER BY
		 		 	CASE WHEN ( CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
					END DESC ,
					CASE WHEN ( CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
					END ASC ,
					CASE WHEN ( CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionDESC') THEN tblRate.Description
					END DESC ,
					CASE WHEN ( CONCAT(p_lSortCol,p_SortOrder) = 'DescriptionASC') THEN tblRate.Description
					END ASC,
					CASE WHEN ( CONCAT(p_lSortCol,p_SortOrder) = 'StatusDESC') THEN VendorBlockingId
					END DESC,
					CASE WHEN ( CONCAT(p_lSortCol,p_SortOrder) = 'StatusASC') THEN VendorBlockingId
					END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;
            
      
		SELECT  COUNT(tblRate.RateID) AS totalcount
      FROM    tblRate
      INNER JOIN tblVendorRate ON tblRate.RateID = tblVendorRate.RateId AND AccountID = p_AccountID AND tblVendorRate.TrunkID = p_trunkID   
		INNER JOIN tblVendorTrunk ON tblVendorTrunk.CodeDeckId = tblRate.CodeDeckId AND tblVendorTrunk.AccountID = p_AccountID AND tblVendorTrunk.TrunkID = p_trunkID   
      LEFT JOIN tblVendorBlocking ON tblVendorBlocking.RateId = tblVendorRate.RateID and tblVendorRate.TrunkID = tblVendorBlocking.TrunkID and tblVendorRate.AccountId = tblVendorBlocking.AccountId
      WHERE   ( p_contryID IS NULL OR tblRate.CountryID = p_contryID)
      		AND ( tblRate.CompanyID = p_companyid )
            AND ( p_code IS NULL OR Code LIKE REPLACE(p_code,'*', '%') )
            AND ( p_status = 'All' or ( CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END ) = p_status);  
	END IF;
  
   IF p_isExport = 1
   THEN 
		SELECT   Code
      		,CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END as Status
            ,tblRate.Description

		FROM    tblRate
      INNER JOIN tblVendorRate ON tblRate.RateID = tblVendorRate.RateId AND AccountID = p_AccountID AND tblVendorRate.TrunkID = p_trunkID   
		INNER JOIN tblVendorTrunk ON tblVendorTrunk.CodeDeckId = tblRate.CodeDeckId AND tblVendorTrunk.AccountID = p_AccountID AND tblVendorTrunk.TrunkID = p_trunkID   
      LEFT JOIN tblVendorBlocking ON tblVendorBlocking.RateId = tblVendorRate.RateID and tblVendorRate.TrunkID = tblVendorBlocking.TrunkID and tblVendorRate.AccountId = tblVendorBlocking.AccountId		
		WHERE   ( p_contryID IS NULL OR tblRate.CountryID = p_contryID)
      		AND ( tblRate.CompanyID = p_companyid )
            AND ( p_code IS NULL OR Code LIKE REPLACE(p_code,'*', '%') )
            AND ( p_status = 'All' or ( CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END ) = p_status)    
		ORDER BY Code,Status  ;                    
   END IF;
   IF p_isExport = 2
   THEN 
		SELECT   
			    tblRate.RateID as RateID
				,Code
      		,CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END as Status
            ,tblRate.Description

		FROM    tblRate
      INNER JOIN tblVendorRate ON tblRate.RateID = tblVendorRate.RateId AND AccountID = p_AccountID AND tblVendorRate.TrunkID = p_trunkID   
		INNER JOIN tblVendorTrunk ON tblVendorTrunk.CodeDeckId = tblRate.CodeDeckId AND tblVendorTrunk.AccountID = p_AccountID AND tblVendorTrunk.TrunkID = p_trunkID   
      LEFT JOIN tblVendorBlocking ON tblVendorBlocking.RateId = tblVendorRate.RateID and tblVendorRate.TrunkID = tblVendorBlocking.TrunkID and tblVendorRate.AccountId = tblVendorBlocking.AccountId		
		WHERE   ( p_contryID IS NULL OR tblRate.CountryID = p_contryID)
      		AND ( tblRate.CompanyID = p_companyid )
            AND ( p_code IS NULL OR Code LIKE REPLACE(p_code,'*', '%') )
            AND ( p_status = 'All' or ( CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END ) = p_status)    
		ORDER BY Code,Status  ;                    
   END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	  
END