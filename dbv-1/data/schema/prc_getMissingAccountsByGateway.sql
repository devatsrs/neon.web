CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getMissingAccountsByGateway`(IN `p_CompanyID` INT, IN `p_CompanyGatewayID` INT, IN `p_PageNumber` INT, IN `p_RowspPage` INT, IN `p_lSortCol` VARCHAR(50), IN `p_SortOrder` VARCHAR(5), IN `p_Export` INT)
BEGIN
     DECLARE v_OffSet_ int;
     SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

	      
	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;


	if p_Export = 0
	THEN

		SELECT DISTINCT
				ta.tblTempAccountID,				
				ta.AccountName as AccountName,
				ta.FirstName as FirstName,
				ta.LastName as LastName,
				ta.Email as Email
				from tblTempAccount ta
				left join tblAccount a on ta.AccountName=a.AccountName AND ta.CompanyID = a.CompanyId
				where ta.CompanyID =p_CompanyID 								
				AND ta.AccountType=1
				AND ta.CompanyGatewayID = p_CompanyGatewayID
				AND a.AccountID is null
				group by ta.AccountName
				ORDER BY				
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameDESC') THEN ta.AccountName
                END DESC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'AccountNameASC') THEN ta.AccountName
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'FirstNameDESC') THEN ta.FirstName
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'FirstNameASC') THEN ta.FirstName
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'LastNameDESC') THEN ta.LastName
                END DESC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'LastNameASC') THEN ta.LastName
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EmailDESC') THEN ta.Email
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EmailASC') THEN ta.Email
                END ASC
				LIMIT p_RowspPage OFFSET v_OffSet_;				
			
			select count(*) as totalcount from(
			SELECT 
				COUNT(ta.tblTempAccountID) as totalcount				
				from tblTempAccount ta
				left join tblAccount a on ta.AccountName=a.AccountName AND ta.CompanyID = a.CompanyId
				where ta.CompanyID =p_CompanyID 
				AND ta.AccountType=1
				AND ta.CompanyGatewayID = p_CompanyGatewayID
				AND a.AccountID is null
				group by ta.AccountName)tbl;

	ELSE

			SELECT DISTINCT				
				ta.AccountName as AccountName,
				ta.FirstName as FirstName,
				ta.LastName as LastName,
				ta.Email as Email
				from tblTempAccount ta
				left join tblAccount a on ta.AccountName=a.AccountName AND ta.CompanyID = a.CompanyId
				where ta.CompanyID =p_CompanyID 
				AND ta.AccountType=1
				AND ta.CompanyGatewayID = p_CompanyGatewayID
				AND a.AccountID is null
				group by ta.AccountName;

	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
	
END