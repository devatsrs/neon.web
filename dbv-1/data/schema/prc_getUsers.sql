CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_getUsers`(
	IN `p_CompanyID` INT,
	IN `p_Status` INT,
	IN `p_PageNumber` INT,
	IN `p_RowspPage` INT,
	IN `p_lSortCol` VARCHAR(50),
	IN `p_SortOrder` VARCHAR(5),
	IN `p_Export` INT


)
BEGIN
     DECLARE v_OffSet_ int;
     SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


	SET v_OffSet_ = (p_PageNumber * p_RowspPage) - p_RowspPage;

	if p_Export = 0
	THEN
	
	select  
	u.Status,
	u.FirstName,
	u.LastName,
	u.EmailAddress,
	CASE WHEN u.AdminUser=1
	then 'Super Admin'
	Else group_concat(DISTINCT r.RoleName ORDER BY r.RoleName ASC SEPARATOR ' ')
	END as Roles,
	u.UserID	
	 from `tblUser` u 
	left join tblUserRole ur on u.UserID=ur.UserID
	left join tblRole r on ur.RoleID=r.RoleID 	
	where u.CompanyID=p_CompanyID and u.`Status`=p_Status
	group by u.UserID
	order by 
	CASE
                WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Statusdesc') THEN Status
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Statusasc') THEN Status
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'FirstNamedesc') THEN FirstName
                END DESC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'FirstNameasc') THEN FirstName
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'LastNamedesc') THEN LastName
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'LastNameasc') THEN LastName
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EmailAddressdesc') THEN EmailAddress
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'EmailAddressasc') THEN EmailAddress
                END ASC,
				CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Roledesc') THEN Roles
                END DESC,
                CASE
                    WHEN (CONCAT(p_lSortCol,p_SortOrder) = 'Roleasc') THEN Roles
                END ASC
            LIMIT p_RowspPage OFFSET v_OffSet_;
            
         SELECT  			
			COUNT(UserID)	 AS totalcount
		   from `tblUser`
			where CompanyID=p_CompanyID and `Status`=p_Status;

	ELSE

			select  
			u.FirstName,
			u.LastName,
			u.EmailAddress,
			CASE WHEN u.AdminUser=1
			then 'Admin'
			Else group_concat(r.RoleName)
			END as AdminUser,
			u.UserID	
			 from `tblUser` u 
			left join tblUserRole ur on u.UserID=ur.UserID
			left join tblRole r on ur.RoleID=r.RoleID 	
			where u.CompanyID=p_CompanyID and u.`Status`=p_Status
			group by u.UserID
			order by FirstName ASC;

	END IF;
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;	
END