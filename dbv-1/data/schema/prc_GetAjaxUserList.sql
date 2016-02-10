CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetAjaxUserList`(IN `p_CompanyID` INT, IN `p_ResourceCategoryID` LONGTEXT, IN `p_roleids` LONGTEXT, IN `p_action` int)
BEGIN

   SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;  
	/* get users from roleid */
	IF p_action = 1
	THEN

	select distinct `tblUser`.`UserID`, (CONCAT(tblUser.FirstName,' ',tblUser.LastName)) as UserName,case when tblUserRole.RoleID>0
	 then
		1
     else
	    null
     end As Checked 
		from tblUser left join tblUserRole on `tblUserRole`.`UserID` = `tblUser`.`UserID` 
		and `tblUser`.`CompanyID` = p_CompanyID and FIND_IN_SET(`tblUserRole`.`RoleID`,p_roleids)  != 0
		where `AdminUser` != '1' or `AdminUser` is null
		 order by
		 case when tblUserRole.RoleID>0
	 then
		1
     else
	    null
     end
	  desc, UserName asc;

	END IF;
	/* get users from ResourceCategoryID */
	IF p_action = 2
	THEN

	SELECT distinct u.UserID,(CONCAT(u.FirstName,' ',u.LastName)) as UserName,Perm.Checked, UPerm.AddRemove
		FROM tblUser u
		LEFT OUTER JOIN
		(SELECT distinct u.userid,'true' as Checked,tblResourceCategories.ResourceCategoryName as permname
		FROM tblUser u
		inner join tblUserRole ur on u.UserID = ur.UserID
		inner join tblRole r on ur.RoleID = r.RoleID
		inner join tblRolePermission rp on r.RoleID = rp.roleID
		inner join tblResourceCategories on rp.resourceID = tblResourceCategories.ResourceCategoryID
		WHERE  FIND_IN_SET(tblResourceCategories.ResourceCategoryID,p_ResourceCategoryID) != 0 ) Perm
		ON u.UserID = Perm.UserID
		LEFT OUTER JOIN
		(SELECT distinct u.userid, up.AddRemove,tblResourceCategories.ResourceCategoryName as permname
		FROM tblUser u
		inner join tblUserPermission up on u.UserID = up.UserID
		inner join tblResourceCategories on up.resourceID = tblResourceCategories.ResourceCategoryID
		WHERE FIND_IN_SET(tblResourceCategories.ResourceCategoryID ,p_ResourceCategoryID) != 0 ) UPerm
		on u.userid = UPerm.userid
		where u.CompanyID = p_CompanyID and u.AdminUser != 1 or u.AdminUser is null
		order by Perm.Checked desc, UPerm.AddRemove desc,UserName asc;

	END IF;
	
	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
END