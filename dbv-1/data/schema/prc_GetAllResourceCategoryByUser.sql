CREATE DEFINER=`root`@`localhost` PROCEDURE `prc_GetAllResourceCategoryByUser`(IN `p_CompanyID` INT, IN `p_userid` LONGTEXT)
BEGIN

    SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
	
     select distinct
		case
		when (rolres.Checked is not null and  usrper.AddRemove ='add') or (rolres.Checked is not null and usrper.AddRemove is null ) or	(rolres.Checked is null and  usrper.AddRemove ='add')
		then rescat.ResourceCategoryID
		end as ResourceCategoryID,
		case
		when (rolres.Checked is not null and  usrper.AddRemove ='add') or (rolres.Checked is not null and usrper.AddRemove is null ) or	(rolres.Checked is null and  usrper.AddRemove ='add')
		then rescat.ResourceCategoryName
		end as ResourceCategoryName
		from tblResourceCategories rescat
		LEFT OUTER JOIN(
			select distinct rescat.ResourceCategoryID, rescat.ResourceCategoryName,usrper.AddRemove
			from tblResourceCategories rescat
			inner join tblUserPermission usrper on usrper.resourceID = rescat.ResourceCategoryID and  FIND_IN_SET(usrper.UserID,p_userid) != 0 ) usrper
			on usrper.ResourceCategoryID = rescat.ResourceCategoryID
	      LEFT OUTER JOIN(
			select distinct rescat.ResourceCategoryID, rescat.ResourceCategoryName,'true' as Checked
			from `tblResourceCategories` rescat
			inner join `tblRolePermission` rolper on rolper.resourceID = rescat.ResourceCategoryID and rolper.roleID in(SELECT RoleID FROM `tblUserRole` where FIND_IN_SET(UserID,p_userid) != 0 )) rolres
			on rolres.ResourceCategoryID = rescat.ResourceCategoryID
		where rescat.CompanyID= p_CompanyID;
		
		SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ; 
END