CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetVendorBlockByCountry`(IN `p_AccountID` INT, IN `p_trunkID` INT, IN `p_contryID` INT , IN `p_status` VARCHAR(50) , IN `p_PageNumber` INT , IN `p_RowspPage` INT , IN `p_lSortCol` VARCHAR(50) , IN `p_SortOrder` VARCHAR(5) , IN `p_isExport` INT)
BEGIN
       
	DECLARE v_OffSet_ int;
	SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;      
		  
	IF p_isExport = 0
   THEN
  
		SELECT 
      	tblCountry.CountryID,
         Country,
         CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END as Status
		FROM      tblCountry
      LEFT JOIN tblVendorBlocking ON tblVendorBlocking.CountryId = tblCountry.CountryID AND TrunkID = p_trunkID AND AccountId = p_AccountID
      WHERE         ( p_contryID IS NULL OR tblCountry.CountryID = p_contryID)
      	AND ( p_status = 'All' or ( CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END ) = p_status)  
      ORDER BY 
	   	CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryDESC') THEN Country
	      END DESC ,
	      CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'CountryASC')	THEN Country
	      END ASC ,
	      CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusDESC') THEN VendorBlockingId
	      END DESC,
	      CASE WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'StatusASC') THEN VendorBlockingId
	      END ASC
		LIMIT p_RowspPage OFFSET v_OffSet_;
		
		SELECT  COUNT(tblCountry.CountryID) AS totalcount
      FROM    tblCountry
      LEFT JOIN tblVendorBlocking ON tblVendorBlocking.CountryId = tblCountry.CountryID AND TrunkID = p_trunkID AND AccountId = p_AccountID
      WHERE   ( p_contryID IS NULL OR tblCountry.CountryID = p_contryID)
      	AND ( p_status = 'All' or ( CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END ) = p_status);  
	END IF;
  
   IF p_isExport = 1
   THEN 
  
   	SELECT   Country,
      			CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END as Status
		FROM    tblCountry
      LEFT JOIN tblVendorBlocking ON tblVendorBlocking.CountryId = tblCountry.CountryID AND TrunkID = p_trunkID AND AccountId = p_AccountID
      WHERE   ( p_contryID IS NULL OR tblCountry.CountryID = p_contryID)
      	AND ( p_status = 'All' or ( CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END ) = p_status);    
	END IF;
	IF p_isExport = 2
   THEN 
  
   	SELECT   tblCountry.CountryID,
					Country,
      			CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END as Status
		FROM    tblCountry
      LEFT JOIN tblVendorBlocking ON tblVendorBlocking.CountryId = tblCountry.CountryID AND TrunkID = p_trunkID AND AccountId = p_AccountID
      WHERE   ( p_contryID IS NULL OR tblCountry.CountryID = p_contryID)
      	AND ( p_status = 'All' or ( CASE WHEN tblVendorBlocking.VendorBlockingId IS NULL THEN 'Not Blocked' ELSE 'Blocked' END ) = p_status);    
	END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END