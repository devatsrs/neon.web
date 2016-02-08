CREATE DEFINER=`neon-user`@`117.247.87.156` PROCEDURE `prc_GetVendorCodes`(IN `p_companyid` INT , IN `p_trunkID` INT, IN `p_contryID` VARCHAR(50) , IN `p_code` VARCHAR(50), IN `p_PageNumber` INT , IN `p_RowspPage` INT , IN `p_lSortCol` NVARCHAR(50) , IN `p_SortOrder` NVARCHAR(5) , IN `p_isExport` INT)
BEGIN

	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;
        
        IF p_isExport = 0
		  THEN
  
				SELECT  RateID,Code FROM(
                                SELECT distinct tblRate.Code as RateID
                                    ,tblRate.Code
                                FROM      tblRate
                                INNER JOIN tblVendorRate ON tblRate.RateID = tblVendorRate.RateId AND tblVendorRate.TrunkID = p_trunkID   
                                INNER JOIN tblVendorTrunk ON tblVendorTrunk.CodeDeckId = tblRate.CodeDeckId  AND tblVendorTrunk.TrunkID = p_trunkID   
                                WHERE ( p_contryID  = '' OR  FIND_IN_SET(tblRate.CountryID,p_contryID) != 0  )
                                AND ( tblRate.CompanyID = p_companyid )
                                AND ( p_code = '' OR Code LIKE REPLACE(p_code,'*', '%') )
                            ) AS TBL2
            ORDER BY
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeDESC') THEN Code
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CodeASC') THEN Code
                END ASC
				LIMIT p_RowspPage OFFSET v_OffSet_;
            
                    
                    
                    
      
            SELECT  COUNT(distinct tblRate.Code) AS totalcount
            FROM    tblRate
          INNER JOIN tblVendorRate ON tblRate.RateID = tblVendorRate.RateId  AND tblVendorRate.TrunkID = p_trunkID   
          INNER JOIN tblVendorTrunk ON tblVendorTrunk.CodeDeckId = tblRate.CodeDeckId  AND tblVendorTrunk.TrunkID = p_trunkID   
                    
            WHERE   ( p_contryID  = '' OR  FIND_IN_SET(tblRate.CountryID,p_contryID) != 0  )
                    AND ( tblRate.CompanyID = p_companyid )
                    AND ( p_code  = '' OR Code LIKE REPLACE(p_code,'*', '%') );
                    
    END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
       
END